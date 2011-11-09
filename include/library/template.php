<?php

/**
 * @class Generic_View
 *
 * @brief Classe Generic_View.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla presentazione
 *   e alla formattazione dei dati sulle pagine web del sito.
 *
 */

class Generic_View {

	/**
	 * Genera una tabella generica.
	 *
	 * Genera una tabella generica in base a delle informazioni fornite, come
	 * le intestazioni e i valori. Le intestazioni sono date nell'array 
	 * $array_table_info, mentre i valori sono inseriti in un array nidificato
	 * $array_values.
	 *
	 * @param $array_table_info
	 *   Contiene il nome delle intestazioni
	 * @param $array_values
	 *   Contiene dei sottovettori contenenti ciascuno una riga della tabella
	 * @return
	 *   Una tabella generica html sotto forma di stringa
	 *
	 */
	 
	protected static function generate_generic_table($array_table_info, $array_values) { 
		$table_string = '<table class="tabella"><tr>';
		foreach($array_table_info as $heading)
			$table_string .= "<th>$heading</th>";
		$table_string .= "</tr>";

		foreach($array_values as $array_item) {
				$table_string .= '<tr>';
				foreach($array_item as $array_item_value)
					$table_string .= "<td>$array_item_value</td>";
				$table_string .= '</tr>';
			}
		$table_string .= "</table>";
		return $table_string;
	}
}

/**
 * @class User_View
 *
 * @brief Classe User_View.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla presentazione
 *   e alla formattazione dei dati sugli utenti.
 *
 */

class User_View extends Generic_View {

	/**
	 * Genera una tabella degli utenti.
	 *
	 * @param None
	 * @return
	 *   Una tabella con tutti gli utenti.
	 *
	 */

	public static function get_users_table() {
		$array_headings = array("Nome Utente", "Nome", "Cognome", "E-mail", 
								"Data Registrazione", "Azioni");
		$utenti = Utente::get_users_data();

		foreach($utenti as $utente) { 
			$azione = $utente['Attivo'] ? 
						self::get_disable_user_tag($utente['NomeUtente']) :
						self::get_enable_user_tag($utente['NomeUtente']);
			$array_values[] = array("{$utente['Nome']}", 
									"{$utente['Cognome']}", 
									"{$utente['NomeUtente']}", 
									"{$utente['IndirizzoPostaElettronica']}", 
									"{$utente['DataRegistrazione']}", 
									$azione );
		}

		return "<h2> Lista utenti </h2> \n ".parent::generate_generic_table($array_headings, $array_values);
	}

	/**
	 * Restituisce il pezzo di codice per disabilitare un utente
	 *
	 * @param $username
	 *   L'utente da disabilitare
	 * @return
	 *   La stringa di codice per disabilitare l'utente
	 *
	 */

	private static function get_disable_user_tag($username) {
		return "<a href=\"pannello_utente.php?q=disable_user&username=$username\" title=\"Disabilita l'utente $username\"> Disabilita </a>";
	}
	
	/**
	 * Restituisce il pezzo di codice per abilitare un utente
	 *
	 * @param $username
	 *   L'utente da abilitare
	 * @return
	 *   La stringa di codice per abilitare l'utente
	 *
	 */
	
	private static function get_enable_user_tag($username) {
		return "<a href=\"pannello_utente.php?q=enable_user&username=$username\" title=\"Abilita l'utente $username\"> Abilita </a>";
	}
	
}

class Items_View {
}

/**
 * @class Messages_View
 *
 * @brief Classe Messages_View.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla presentazione
 *   e alla formattazione dei dati sui messaggi.
 *
 */

class Messages_View extends Generic_View {

	/**
	 * Genera una tabella dei messaggi.
	 *
	 * @param $username
	 *   L'utente legato ai messaggi
	 * @return
	 *   Una tabella con tutti i messaggi di un utente
	 *
	 */

	public static function get_messages_table($username) {
		$return_string = "<h2>Messaggi ricevuti</h2>\n
						  <a href=\"{$_SERVER['PHP_SELF']}?q=send_msg\">+ Componi un nuovo messaggio</a>";
		$messaggi = Messages::get_messages($username);
		if(empty($messaggi)) $return_string .= '<p>Non ci sono messaggi</p>';
		else {
			$array_headings = array("Data", "Mittente", "Titolo", "Corpo", "Info", "Azioni");
			foreach($messaggi as $msg) {
				$link = '<a href="pannello_utente?q=view_msg&msg_id=' . 
							$msg['MsgID'].'&destinatario='.$msg['Mittente'].'" >';

				$delete_action_tag = "<a class=\"remove\" id=\"msg-".$msg['MsgID']."\" href=\"#\" title=\"Cancella messaggio\"><img src=\"images/delete.png\" /></a>";
				$stato = $msg['StatusReceiver'] == "read" ? "Letto" : "Non letto";

				$array_values[] = array("{$msg['DataInvio']}", 
									"{$msg['Mittente']}", 
									"$link{$msg['Titolo']}", 
									"{$msg['LEFT(Testo, 45)']}", 
									$stato,
									"$delete_action_tag");
			}
			$return_string .= parent::generate_generic_table($array_headings, $array_values);
		}
			
		return $return_string;
	}

}

class Offers_View {
}

class Requests_View {
}

/**
 * @class Images_View
 *
 * @brief Classe Images_View.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla presentazione
 *   e alla formattazione dei dati sui messaggi.
 *
 */

class Images_View {

	/**
	 * Restituisce il pezzo di codice per mostrare l'immagine (piccola) di un bene
	 *
	 * @param $file_name
	 *   Il nome dell'immagine
	 * @return
	 *   La stringa di codice per visualizzare l'immagine piccola
	 *
	 */

	public static function get_small_thumb_img_tag($file_name) {
		return '<img src="' . Images::make_small_thumb_address($file_name) . '" />';
	}

	/**
	 * Restituisce il pezzo di codice per mostrare l'immagine (media) di un bene
	 *
	 * @param $file_name
	 *   Il nome dell'immagine
	 * @return
	 *   La stringa di codice per visualizzare l'immagine media
	 *
	 */

 	public static function get_medium_thumb_img_tag($file_name) {
		return '<img class="item_image" src="' . Images::make_medium_thumb_address($file_name) . '" />';
	}

	/**
	 * Restituisce il pezzo di codice per mostrare l'immagine (piccola) di un utente
	 *
	 * @param $file_name
	 *   Il nome dell'immagine
	 * @return
	 *   La stringa di codice per visualizzare l'immagine piccola
	 *
	 */

	public static function get_avatar_small_thumb_img_tag($file_name) {
		return "";// '<img src="' . Images::make_medium_thumb_address($file_name) . '" />';
	}

	/**
	 * Restituisce il pezzo di codice per mostrare l'immagine (media) di un utente
	 *
	 * @param $file_name
	 *   Il nome dell'immagine
	 * @return
	 *   La stringa di codice per visualizzare l'immagine media
	 *
	 */

	public static function get_avatar_medium_thumb_img_tag($file_name) {
		return ""; //'<img src="' . Images::make_medium_thumb_address($file_name) . '" />';
	}
}

?>
