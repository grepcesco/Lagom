<?php

/**
 * @file messaggi.php
 * @brief File con classe sui messaggi
 * 
 * Rappresenta il file con classe e metodi per lavorare sui messaggi.
 * Come stato di invio ("StatusSender") il messaggio può avere come valori:
 *   - sent;
 *   - deleted.
 *
 * Come stato di ricezione ("StatusReceiver") il messaggio può avere come valori:
 *   - unread;
 *   - read;
 *   - deleted.
 *
 * Il messaggio viene effettivamente cancellato dal sistema quando sia il destinatario
 * che il mittente lo contrassegnano come cancellato.
 *
 */

/**
 * @class Messages
 *
 * @brief Classe Messages
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * dei messaggi in generale.
 *
 */

class Messages {

	/**
	 * Restituisce un array contenente i dati di un messaggio
	 *
	 * @param $MsgID
	 *   Id del messaggio da recuperare
	 * @return 
	 *   Un array contenente i dati di un messaggio
	 *
	 */

	public static function get_message($MsgID) {
		$db_handle = Database::open_db();
		$query_to_execute = "SELECT * FROM Messaggi WHERE MsgID = '$MsgID'";
		$risultato = $db_handle->query($query_to_execute);
		$array_risultato = mysql_fetch_array($risultato);
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Restituisce un array nidificato contenente i dati di una serie di 
	 *   messaggi intercorrelati tra loro.
	 *
	 * Restituisce un array con tutti i messaggi aventi come primo messaggio
	 *   il messaggio con MsgID passato come primo argomento. Come metodo di
	 *   ordinamento, i messaggi sono ordinati per data, in ordine crescente.
	 *
	 * @param $Parent_MsgID
	 *   Id del messaggio che inizia il thread.
	 * @return 
	 *   I messaggi (come array di array) ordinati per data.
	 * 
	 */

	public static function get_thread($Parent_MsgID) {
		$thread_head = self::get_parent_id($Parent_MsgID);
		$db_handle = Database::open_db();
		$query_to_execute = "SELECT * FROM Messaggi 
							 WHERE MsgID = '$thread_head' 
							 OR ParentID = '$thread_head' 
							 ORDER BY DataInvio;";
		$risultato = $db_handle->query($query_to_execute);
		while($temp = mysql_fetch_array($risultato)) $array_risultato[] = $temp;
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 *  Imposta i messaggi di un thread come letti dal destinatario.
	 *   
	 * @param $Parent_MsgID
	 *   Id del messaggio che inizia il thread.
	 * @param $username
	 *   Il nome utente destinatario dei messaggi.
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */
	public static function set_read_thread($Parent_MsgID, $username) {
		$thread_head = self::get_parent_id($Parent_MsgID);
		$db_handle = Database::open_db();
		$query_to_execute = "UPDATE Messaggi SET StatusReceiver = 'read' 
							 WHERE Destinatario = '$username' 
							 AND MsgID = '$thread_head' 
							 OR ParentID = '$thread_head';";
		$risultato = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $risultato;

	}

	/**
	 * Restituisce il numero di messaggi non letti.
	 *
	 * @param $Username
	 *   L'utente legato ai messaggi.
	 * @return 
	 *	 Il numero di messaggi non letti dell'utente.
	 *
	 */

	public static function get_n_unread_messages($Username) {
		$db_handle = Database::open_db();
		$query_to_execute = "SELECT * FROM Messaggi 
							 WHERE Destinatario = '$Username' 
							 AND StatusReceiver = 'unread' 
							 AND MsgID NOT IN 
								(SELECT MessaggioAssociato FROM OperazioneOfferta 
								UNION 
								SELECT MessaggioAssociato FROM OperazioneRichiesta) ;";
		$risultato = $db_handle->query($query_to_execute);
		$n_unread = mysql_num_rows($risultato);
		$db_handle->disconnect();
		return $n_unread;
	}

	/**
	 * Restituisce tutti i messaggi ricevuti da un utente
	 *
	 * @param $Username
	 *   L'utente legato ai messaggi.
	 * @return 
	 *   Un array contenente tutti i messaggi dell'utente
	 *
	 */

	public static function get_messages($Username) {
		$db_handle = Database::open_db();
		$query_to_execute = "SELECT MsgID, Mittente, Destinatario, DataInvio, Titolo, 
							 LEFT(Testo, 45), ParentID, StatusReceiver 
							 FROM Messaggi WHERE Destinatario = '$Username' 
							 AND StatusReceiver != 'deleted'
							 AND MsgID NOT IN 
								(SELECT MessaggioAssociato FROM OperazioneOfferta 
								UNION 
								SELECT MessaggioAssociato FROM OperazioneRichiesta) 
							 AND (ParentID is NULL OR ParentID NOT IN 
								(SELECT MessaggioAssociato FROM OperazioneOfferta 
								UNION 
								SELECT MessaggioAssociato FROM OperazioneRichiesta)) 
							 ORDER BY DataInvio DESC;";
		$risultato = $db_handle->query($query_to_execute);
		while($temp = mysql_fetch_array($risultato)) $array_risultato[] = $temp;
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Restituisce tutti i messaggi inviati da un utente
	 *
	 * @param $Username
	 *   L'utente legato ai messaggi.
	 * @return 
	 *   Un array contenente tutti i messaggi dell'utente
	 * 
	 */

	public static function get_sent_messages($Username) {
		$db_handle = Database::open_db();
		$query_to_execute = "SELECT MsgID, Mittente, Destinatario, DataInvio, Titolo, 
							 LEFT(Testo, 45), ParentID, StatusReceiver 
							 FROM Messaggi WHERE Mittente = '$Username' 
							 AND StatusSender != 'deleted'
							 AND MsgID NOT IN 
								(SELECT MessaggioAssociato FROM OperazioneOfferta 
								UNION 
								SELECT MessaggioAssociato FROM OperazioneRichiesta) 
							 AND (ParentID is NULL OR ParentID NOT IN 
								(SELECT MessaggioAssociato FROM OperazioneOfferta 
								UNION 
								SELECT MessaggioAssociato FROM OperazioneRichiesta)) 
							 ORDER BY DataInvio DESC;";
		$risultato = $db_handle->query($query_to_execute);
		while($temp = mysql_fetch_array($risultato)) $array_risultato[] = $temp;
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Spedisce un messaggio.
	 *
	 * @param $Sender
	 *   Indica il mittente del messaggio.
	 * @param $Receiver
	 *   Indica il destinatario del messaggio.
	 * @param $Title
	 *   Indica il titolo del messaggio.
	 * @param $Text
	 *   Indica il testo del messaggio.
	 * @param $Parent_MsgID
	 *   Se esiste, indica il messaggio che ha iniziato il thread.
	 *   È un parametro facoltativo. Se non viene passato nulla,
	 *   Si assume che il messaggio non abbia figli.
	 * @return 
	 *   Ritorna il numero id del messaggio in caso l'operazione ha avuto successo, zero se non è stato inserito il valore
	 * @todo aggiungere sanitization
	 *
	 */

	public static function send_message($Sender, $Receiver, $Title, $Text, $Parent_MsgID = 'NULL') {
		// non è possibile inviare un messaggio a se stessi
		if($Sender == $Receiver) return 0;
		$db_handle = new Database();
		$db_handle->connect();
		$query_to_execute = "INSERT INTO Messaggi (`ParentID`, `Mittente`, `Destinatario`, `StatusSender`, `StatusReceiver`, `Titolo`, `Testo`) VALUES ($Parent_MsgID, '$Sender', '$Receiver', 'sent', 'unread', '".mysql_real_escape_string($Title)."', '".mysql_real_escape_string($Text)."');";
		// se il parent id non è null, va impostato il valore statusreceiver a unread
		$esito_inserimento = $db_handle->query($query_to_execute);
		$msg_id = mysql_insert_id();
		$db_handle->disconnect();
		return $msg_id;
	}

	/**
	 * Cancella un messaggio. 
	 *
	 * Cancella un messaggio. La cancellazione effettiva avviene quando entrambi 
	 *   gli utenti lo segnalano come cancellato. Questo ha luogo quando lo stato del
	 *   messaggio del ricevente (StatusReceiver) è segnato come
	 *   cancellato (deleted) e l'utente che desidera cancellare il
	 *   messaggio ne è il mittente, oppure parimenti quando lo stato del
	 *   messaggio del mittente (StatusSender) è segnato come
	 *   cancellato (deleted) e l'utente che desidera cancellare il
	 *   messaggio ne è il destinatario.
	 *
	 * @param $MsgID id del messaggio da cancellare
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * @todo il metodo va testato con cura.
	 *
	 */

	public static function delete_message($MsgID, $Username) {
		$messaggio = self::get_message($MsgID);
		$db_handle = new Database();
		$db_handle->connect();
		if($messaggio['Mittente'] == $Username) {
			/* if($messaggio['StatusReceiver'] == "deleted")
				$query_to_execute = "DELETE FROM Messaggi WHERE MsgID = '$MsgID' OR ParentID = '$MsgID';";
			else */
			$query_to_execute = "UPDATE Messaggi SET StatusSender = 'deleted' WHERE MsgID = '$MsgID';";
		} elseif($messaggio['Destinatario'] == $Username) {
			/* if($messaggio['StatusSender'] == "deleted")
				$query_to_execute = "DELETE FROM Messaggi WHERE MsgID = '$MsgID';";
			else */
			$query_to_execute = "UPDATE Messaggi SET StatusReceiver = 'deleted' WHERE MsgID = '$MsgID';";

		}
		$esito_inserimento = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $esito_inserimento;

	}
	/**
	 * Restituisce il genitore del messaggio (se esiste).
	 *
	 * @param $MsgID 
	 *   id del messaggio
	 * @return 
	 *   L'ID del genitore del messaggio. Nel caso in cui non esiste,
	 *	 viene restituito l'ID del messaggio stesso.
	 *
	 */

	public static function get_parent_id($MsgID) {
		$messaggio = self::get_message($MsgID);
		if($messaggio['ParentID'] != NULL) 
			return $messaggio['ParentID'];
		else
			return $MsgID;
	}
	
}


?>
