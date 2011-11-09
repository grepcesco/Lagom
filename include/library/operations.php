<?php

/**
 * @class Operations
 *
 * @brief Classe Operations.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * delle operazioni.
 *
 * Contact: grepcesco@gmail.com
 * 
 * @todo togliere tutti i suffissi offer e request dai nomi dei metodi
 *
 */

class Operations {

	/**
	 * Restituisce la versione singolare delle parole "Richieste" o "Offerte.
	 *
	 * Restituisce la versione singolare delle parole "Richieste" o "Offerte.
	 *   Viene utilizzato per effettuare operazioni nel DB.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @return
	 *   La stringa "Richiesta" se il tipo era "Richieste", "Offerta" se era "Offerte".
	 */

	private static function singularize($type) {
		return ($type == "Richieste" ? "Richiesta" : "Offerta");
	}

	/**
	 * Inserisce un'operazione nella base di dati.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $item_id
	 *   L'id del bene
	 * @param $msg_id
	 *   L'id del messaggio
	 * @param $quantity
	 *   La quantità di beni richiesti
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * @todo 
	 *   un utente non può effettuare più di un'inserimento di richiesta di un bene
	 * @todo
	 *   aggiungere controllo sulla quantità dei beni (non può eccedere la disponibilità)
	 */

	protected static function answer_operation($type, $item_id, $msg_id, $quantity) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);
		$query_to_execute = "INSERT INTO Operazione".$type_singular." (`Quantita`, `BeneAssociato`, `MessaggioAssociato`, `StatusOperationCreator`, `StatusOperationReceiver`) 
							 VALUES ('".mysql_real_escape_string($quantity)."', '".mysql_real_escape_string($item_id)."', '".mysql_real_escape_string($msg_id)."', 'active', 'unread');";
		$esito_inserimento = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $esito_inserimento;
	}

	/**
	 * Prende tutti le operazioni effettuate da un'utente, che possono essere o risposte a richieste
	 *   o risposte ad offerte. 
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $username
	 *   Il nome utente.
	 * @return 
	 *   Le operazioni effettuate da un utente
	 * @todo 
	 *   Aggiungere mysql_real_escape_string()
	 */

	protected static function get_operations($type, $username) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);
		$query_to_execute = "SELECT Operazione$type_singular.*, Messaggi.Titolo, Messaggi.Testo 
							 FROM Operazione$type_singular, Messaggi 
							 WHERE MessaggioAssociato = Messaggi.MsgID AND Messaggi.Mittente = '$username';";
		$risultato = $db_handle->query($query_to_execute);
		while($temp = mysql_fetch_array($risultato)) $array_risultato[] = $temp;
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Restituisce una operazione singola.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $operation_id
	 *   L'id dell'operazione
	 * @return 
	 *   L'operazione effettuata da un utente
	 * @todo 
	 *   Aggiungere mysql_real_escape_string()
	 */

	protected static function get_operation($type, $operation_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = $type=="Richieste" ? "Richiesta" : "Offerta" ;
		$query_to_execute = "SELECT * from Operazione$type_singular where OperazioneID = '$operation_id';";
		$risultato = $db_handle->query($query_to_execute);
		$array_risultato = mysql_fetch_array($risultato);
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Restituisce le operazioni associate ad un bene specifico.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $item_id
	 *   Il bene in questione.
	 * @return 
	 *   L'operazione effettuata da un utente
	 */
	protected static function get_operations_by_item($type, $item_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = $type=="Richieste" ? "Richiesta" : "Offerta" ;
		$query_to_execute = "SELECT * FROM Operazione$type_singular 
							 WHERE BeneAssociato = '$item_id' 
							 AND (StatusOperationCreator = 'active' 
							 OR StatusOperationCreator = 'unread_winner_operation'
							 OR StatusOperationCreator = 'read_winner_operation');";
		$risultato = $db_handle->query($query_to_execute);
		while($temp = mysql_fetch_array($risultato)) $array_risultato[] = $temp;
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Restituisce il numero di operazioni associate ad un bene specifico.
	 *   
	 * @param $type
	 *   La tipologia di bene
	 * @param $item_id
	 *   L'ID del bene
	 * @return 
	 *   Il numero di operazioni associati ad un bene specifico.
	 * @todo 
	 *   Aggiungere mysql_real_escape_string()
	 */

	protected static function get_n_operations_by_item($type, $item_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = $type=="Richieste" ? "Richiesta" : "Offerta" ;
		$query_to_execute = "SELECT COUNT(*) 
							 FROM Operazione$type_singular 
							 WHERE BeneAssociato = '$item_id' 
							 AND (StatusOperationCreator = 'active' OR StatusOperationCreator LIKE '%winner%');";
		$return_value = $db_handle->query($query_to_execute);
		$result = mysql_result($return_value, 0);
		$db_handle->disconnect();
		return $result;
	}
	
	/**
	 * Aggiudica un bene ad una operazione specifica.
	 *   
	 * @param $type
	 *   La tipologia di bene
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */

	protected static function set_winner_operation($type, $operation_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);
		$query_to_execute = "UPDATE Operazione$type_singular 
							 SET StatusOperationCreator = 'unread_winner_operation'
							 WHERE OperazioneID = '$operation_id';";
		$return_value = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $return_value;
	}
	
	/**
	 * Rimuove lo stato di non letto ad una operazione rifiutata o vinta
	 *   da un utente.
	 *   
	 * @param $type
	 *   La tipologia di bene
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */

	protected static function set_read_answer($type, $operation_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);
		$query_to_execute = "UPDATE Operazione$type_singular
							 SET `StatusOperationCreator` = REPLACE(`StatusOperationCreator`, 'unread_', '') 
							 WHERE OperazioneID = '$operation_id';";
		$return_value = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $return_value;
	}


	/**
	 * Disabilita una operazione, segnandola come inattiva.
	 *   
	 * @param $type
	 *   La tipologia di bene
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */

	protected static function disable_operation($type, $operation_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);
		$query_to_execute = "UPDATE Operazione$type_singular 
							 SET StatusOperationCreator = 'unread_disabled_operation'
							 WHERE OperazioneID = '$operation_id';";
		$return_value = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $return_value;
	}

	/**
	 * Disabilita tutte le operazioni legate ad un bene, eccetto una specificata.
	 *   
	 * @param $type
	 *   La tipologia di bene
	 * @param $operation_id
	 *   L'ID dell'operazione da non rifiutare.
	 * @param $item_id
	 *   L'ID del bene
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */

	protected static function disable_operations_except($type, $operation_id, $item_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);
		$query_to_execute = "UPDATE Operazione$type_singular 
							 SET StatusOperationCreator = 'unread_disabled_operation' 
							 WHERE OperazioneID != '$operation_id' 
							 AND BeneAssociato = '$item_id';";
		$return_value = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $return_value;
	}

	/**
	 * Restituisce il numero di operazioni con uno stato specifico
	 *   effettuate da un utente.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni con uno stato specifico.
	 *
	 */ 
	
	protected static function get_n_withstatus_operations($type, $username, $status, $status_type = "Creator") {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);

		if($status_type == "Creator") $ruolo_messaggio = "Mittente";
		else $ruolo_messaggio = "Destinatario";

		$query_to_execute = "SELECT COUNT(*)
							 FROM Operazione$type_singular, Messaggi 
							 WHERE StatusOperation$status_type LIKE '$status' 
							 AND Messaggi.MsgID = MessaggioAssociato 
							 AND Messaggi.$ruolo_messaggio = '$username'";
		$return_value = $db_handle->query($query_to_execute);
		$result = mysql_result($return_value, 0);
		$db_handle->disconnect();
		return $result;
	}
	
	/**
	 * Restituisce il numero di operazioni vincenti ottenute da un utente non
	 *   ancora lette.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni vincenti ottenute da un utente non ancora lette.
	 *
	 */ 
	
	protected static function get_n_unread_winner_operations($type, $username) {
		return self::get_n_withstatus_operations($type, $username, "unread_winner_operation");
	}
	
	/**
	 * Restituisce il numero di operazioni "perdenti" ottenute da un utente non
	 *   ancora lette.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni vincenti ottenute da un utente non ancora lette.
	 *
	 */ 
	
	protected static function get_n_unread_disabled_operations($type, $username) {
		return self::get_n_withstatus_operations($type, $username, "unread_disabled_operation");
	}

	/**
	 * Restituisce il numero di operazioni la cui risposta da un utente non
	 *   ancora lette.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni da un utente non ancora lette.
	 *
	 */ 
	
	protected static function get_n_unread_operations($type, $username) {
		return self::get_n_withstatus_operations($type, $username, "unread%");
	}
	
	/**
	 * Restituisce il numero di operazioni su un bene inserito dall'utente
	 *   non ancora lette.
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni da un utente non ancora lette.
	 *
	 */ 
	
	protected static function get_n_unread_operations_receiver($type, $username) {
		return self::get_n_withstatus_operations($type, $username, "unread", "Receiver");
	}

	/**
	 * Restituisce il numero di operazioni su un bene effettuate dall'utente
	 *
	 * @param $type
	 *   La tipologia di bene
	 * @param $username
	 *   Il nome dell'utente
	 * @param $item_id
 	 *   L'ID del bene
	 * @return
	 *   Il numero di operazioni da un utente effettuate su un bene
	 *
	 */ 

	protected static function get_operation_by_user_on_item($type, $username, $item_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);

		$query_to_execute = "SELECT Operazione$type_singular.*
							 FROM Operazione$type_singular, Messaggi 
							 WHERE BeneAssociato = '$item_id'
							 AND Messaggi.MsgID = MessaggioAssociato 
							 AND Messaggi.Mittente = '$username'";

		$risultato = $db_handle->query($query_to_execute);
		$result = mysql_fetch_array($risultato);
		$db_handle->disconnect();
		return $result;
	}

	/**
 	 * Imposta tutte le operazioni su un bene come lette da colui che
 	 *   ha immesso il bene (fatto la richiesta o offerto un bene)
 	 * 
	 * @param $type
 	 *   La tipologia di bene
	 * @param $item_id
 	 *   L'ID del bene
 	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	protected static function set_all_operations_read($type, $item_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_singular = self::singularize($type);

		$query_to_execute = "UPDATE Operazione$type_singular
							 SET StatusOperationReceiver = 'read'
							 WHERE BeneAssociato = '$item_id';";

		$risultato = $db_handle->query($query_to_execute);
		return $risultato;
	}

}

/**
 * @class Offer_Operations
 *
 * @brief Classe Offer_Operations.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * delle operazioni di offerta.
 *
 * @author Francesco Lorenzon
 *
 * Contact: grepcesco@gmail.com
 *
 */

class Offer_Operations extends Operations {
	
	/**
	 * Inserisce una offerta nella base di dati.
	 *
	 * @param $item_id
	 *   L'id del bene
	 * @param $msg_id
	 *   L'id del messaggio
	 * @param $quantity
	 *   La quantità di beni richiesti
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function answer_offer($item_id, $msg_id, $quantity) {
		return parent::answer_operation("Offerte", $item_id, $msg_id, $quantity);
	}
	
	/**
	 * Restituisce una operazione di offerta singola.
	 *
	 * @param $operation_id
	 *   L'id dell'operazione
	 * @return 
	 *   L'operazione effettuata da un utente
	 *
	 */

	public static function get_offer_operation($id_operation) {
		return parent::get_operation("Offerte", $id_operation);
	}
	
	/**
	 * Prende tutti le operazioni di offerta effettuate da un'utente.
	 *
	 * @param $username
	 *   Il nome utente.
	 * @return 
	 *   Le operazioni effettuate da un utente
	 *   
	 */

	public static function get_offers_operations($username) {
		return parent::get_operations("Offerte", $username);
	}

	/**
	 * Restituisce il numero di operazioni associate ad una offerta specifica.
	 *   
	 * @param $item_id
	 *   L'ID del bene
	 * @return 
	 *   Il numero di operazioni associati ad un bene specifico.
	 * 
	 */

	public static function get_n_operations_by_offer($item_id) {
		return parent::get_n_operations_by_item("Offerte", $item_id);
	}
	
	/**
	 * Restituisce le operazioni associate ad una offerta specifica.
	 *
	 * @param $item_id
	 *   Il bene in questione.
	 * @return 
	 *   L'operazione effettuata da un utente
	 */

	public static function get_operations_by_offer($item_id) {
		return parent::get_operations_by_item("Offerte", $item_id);
	}

	/**
	 * Aggiudica una offerta ad un operazione specifica.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	public static function set_winner_offer_operation($operation_id) {
		return parent::set_winner_operation("Offerte", $operation_id);
	}

	/**
	 * Rimuove lo stato di non letto ad una operazione su un offerta rifiutata 
	 *   o vinta da un utente.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */

	public static function set_read_answer($operation_id) {
		return parent::set_read_answer("Offerte", $operation_id);
	}


	/**
	 * Disabilita una operazione su una offerta, segnandola come inattiva.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	public static function disable_offer_operation($operation_id) {
		return parent::disable_operation("Offerte", $operation_id);
	}

	/**
	 * Disabilita tutte le operazioni legate ad una offerta, eccetto una specificata.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione da non rifiutare.
	 * @param $item_id
	 *   L'ID del bene in offerta
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */

	public static function disable_offer_operations_except($operation_id, $item_id) {
		return parent::disable_operations_except("Offerte", $operation_id, $item_id);
	}

	/**
	 * Assegna l'offerta all'operazione indicata in modo permanente. 
	 * 
	 * Scrive l'operazione che si è aggiudicata l'offerta. La funzione inoltre
	 *   segna tutte le altre operazioni come rifiutate, segna l'offerta in
	 *   questione come non più disponibile e imposta l'operazione indicata 
	 *   come scelta nei confronti dell'offerta.
	 *   
	 * @param $operation_id
	 *   L'ID del bene in offerta
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */

	public static function set_choosed_offer_operation($operation_id) {
		$operazione = self::get_offer_operation($operation_id);
		return Offerte::disable_offer($operazione['BeneAssociato']) 
			&& self::set_winner_offer_operation($operation_id)
			&& self::disable_offer_operations_except($operation_id, $operazione['BeneAssociato']);
	}

	/**
	 * Restituisce il numero di operazioni su offerte vincenti ottenute da un utente non
	 *   ancora lette.
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni su offerte vincenti ottenute da un utente non ancora lette.
	 *
	 */ 
	
	public static function get_n_unread_operations($username) {
		return parent::get_n_unread_operations("Offerte", $username);
	}
	
	/**
	 * Restituisce il numero di operazioni su offerte "perdenti" ottenute da un 
	 *   utente non ancora lette.
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni su offerte perdenti ottenute da un utente non 
	 *	 ancora lette.
	 *
	 */ 
	
	public static function get_n_unread_disabled_operations($username) {
		return parent::get_n_unread_disabled_operations("Offerte", $username);
	}

	/**
	 * Restituisce il numero di operazioni su offerte vincenti da un utente non
	 *   ancora lette.
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni su offerte vincenti da un utente non ancora lette.
	 *
	 */ 
	
	public static function get_n_unread_winner_operations($username) {
		return parent::get_n_unread_winner_operations("Offerte", $username);
	}
	
	/**
	 * Restituisce il numero di operazioni su un'offerta inserita dall'utente
	 *   non ancora lette.
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni da un utente non ancora lette.
	 *
	 */ 
	
	public static function get_n_unread_answers_by_user($username) {
		return parent::get_n_unread_operations_receiver("Offerte", $username);
	}

	/**
	 * Restituisce il numero di operazioni su una offerta effettuate dall'utente
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @param $item_id
 	 *   L'ID del bene
	 * @return
	 *   Il numero di operazioni da un utente effettuate su una offerta
	 *
	 */ 

	public static function get_operation_by_user_on_offer($username, $item_id) {
		return parent::get_operation_by_user_on_item("Offerte", $username, $item_id);
	}

	/**
 	 * Imposta tutte le operazioni su una offerta come lette da colui che
 	 *   ha fatto l'offerta
	 *
	 * @param $offer_id
 	 *   L'ID dell'offerta
 	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public static function set_all_operations_read($offer_id) {
		return parent::set_all_operations_read("Offerte", $offer_id);
	}

}

/**
 * @class Request_Operations
 *
 * @brief Classe Request_Operations.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * delle operazioni di richiesta.
 *
 * @author Francesco Lorenzon
 *
 * Contact: grepcesco@gmail.com
 *
 */

class Request_Operations extends Operations {
	
	/**
	 * Inserisce una richiesta nella base di dati.
	 *
	 * @param $item_id
	 *   L'id del bene
	 * @param $msg_id
	 *   L'id del messaggio
	 * @param $quantity
	 *   La quantità di beni richiesti
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function answer_request($item_id, $msg_id, $quantity) {
		return parent::answer_operation("Richieste", $item_id, $msg_id, $quantity);
	}

	/**
	 * Restituisce una operazione di offerta singola.
	 *
	 * @param $operation_id
	 *   L'id dell'operazione
	 * @return 
	 *   L'operazione effettuata da un utente
	 *
	 */

	public static function get_request_operation($id_operation) {
		return parent::get_operation("Richieste", $id_operation);
	}
	
	/**
	 * Prende tutti le operazioni di richiesta effettuate da un'utente.
	 *
	 * @param $username
	 *   Il nome utente.
	 * @return 
	 *   Le operazioni effettuate da un utente
	 *   
	 */

	public static function get_request_operations($username) {
		return parent::get_operations("Richieste", $username);
	}
	
	/**
	 * Restituisce il numero di operazioni associate ad una richiesta specifica.
	 *   
	 * @param $item_id
	 *   L'ID del bene
	 * @return 
	 *   Il numero di operazioni associati ad un bene specifico.
	 * 
	 */

	public static function get_n_operations_by_request($item_id) {
		return parent::get_n_operations_by_item("Richieste", $item_id);
	}
	
	/**
	 * Restituisce le operazioni associate ad una richiesta specifica.
	 *
	 * @param $item_id
	 *   Il bene in questione.
	 * @return 
	 *   L'operazione effettuata da un utente
	 */

	public static function get_operations_by_request($item_id) {
		return parent::get_operations_by_item("Richieste", $item_id);
	}

	/**
	 * Aggiudica una richiesta ad un proposta specifica.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	public static function set_winner_request_operation($operation_id) {
		return parent::set_winner_operation("Richieste", $operation_id);
	}

	/**
	 * Rimuove lo stato di non letto ad una operazione su una richiesta rifiutata 
	 *   o vinta da un utente.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * 
	 */

	public static function set_read_answer($operation_id) {
		return parent::set_read_answer("Richieste", $operation_id);
	}


	/**
	 * Disabilita una operazione su una richiesta, segnandola come inattiva.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	public static function disable_request_operation($operation_id) {
		return parent::disable_operation("Richieste", $operation_id);
	}

	/**
	 * Disabilita tutte le operazioni legate ad una richiesta eccetto una specificata.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione da non rifiutare.
	 * @param $item_id
	 *   L'ID del bene in offerta
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	public static function disable_request_operations_except($operation_id, $item_id) {
		return parent::disable_operations_except("Richieste", $operation_id, $item_id);
	}

	/**
	 * Assegna la richiesta all'operazione indicata in modo permanente. 
	 * 
	 * Scrive l'operazione che si è aggiudicata la richiesta. La funzione inoltre
	 *   segna tutte le altre operazioni come rifiutate, segna la richiesta in
	 *   questione come non più disponibile e imposta l'operazione indicata 
	 *   come scelta nei confronti della richiesta.
	 *   
	 * @param $operation_id
	 *   L'ID dell'operazione
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	public static function set_choosed_request_operation($operation_id) {
		$operazione = self::get_request_operation($operation_id);
		return Richieste::disable_request($operazione['BeneAssociato']) 
			&& self::set_winner_request_operation($operation_id)
			&& self::disable_request_operations_except($operation_id, $operazione['BeneAssociato']); 
	}

	/**
	 * Restituisce il numero di operazioni su richieste vincenti ottenute da un utente non
	 *   ancora lette.
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni su richieste vincenti ottenute da un utente non ancora lette.
	 *
	 */ 
	
	public static function get_n_unread_operations($username) {
		return parent::get_n_unread_operations("Richieste", $username);
	}
	
	/**
	 * Restituisce il numero di operazioni su richieste "perdenti" ottenute da un 
	 *   utente non ancora lette.
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni su richieste perdenti ottenute da un utente non 
	 *	 ancora lette.
	 *
	 */ 
	
	public static function get_n_unread_disabled_operations($username) {
		return parent::get_n_unread_disabled_operations("Richieste", $username);
	}

	/**
	 * Restituisce il numero di operazioni su richieste vincenti da un utente non
	 *   ancora lette.
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni su richieste vincenti da un utente non ancora lette.
	 *
	 */ 
	
	public static function get_n_unread_winner_operations($username) {
		return parent::get_n_unread_winner_operations("Richieste", $username);
	}
	
	/**
	 * Restituisce il numero di operazioni su una richiesta inserita dall'utente
	 *   non ancora lette.
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @return
	 *   Il numero di operazioni da un utente non ancora lette.
	 *
	 */ 
	
	public static function get_n_unread_answers_by_user($username) {
		return parent::get_n_unread_operations_receiver("Richieste", $username);
	}

	/**
	 * Restituisce il numero di operazioni su una richiesta effettuate dall'utente
	 *
	 * @param $username
	 *   Il nome dell'utente
	 * @param $item_id
 	 *   L'ID del bene
	 * @return
	 *   Il numero di operazioni da un utente effettuate su una richiesta
	 *
	 */ 

	public static function get_operation_by_user_on_request($username, $item_id) {
		return parent::get_operation_by_user_on_item("Richieste", $username, $item_id);
	}

	/**
 	 * Imposta tutte le operazioni su una richiesta come lette da colui che
 	 *   ha fatto l'offerta
	 *
	 * @param $request_id
 	 *   L'ID della richiesta
 	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public static function set_all_operations_read($request_id) {
		return parent::set_all_operations_read("Richieste", $request_id);
	}

}

?>
