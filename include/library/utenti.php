<?php

/**
 * @class Utente
 *
 * @brief Classe Utente.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * degli utenti.
 *
 * @author Francesco Lorenzon
 *
 * @version 0.1
 *
 * @date 1 set 2011, 11.15.21
 *
 * @copyright GNU Public License.
 *
 * Contact: grepcesco@gmail.com
 *
 */

class Utente {
	/**
	 * Restituisce la password inserita dall'utente 
	 * aggiungendo un valore numero "salt" casuale di 16 bit.
	 *
	 * @param $password
	 *   La stringa contenente la password cifrata
	 * @return 
	 *   L'hash della password con il "sale"
	 */
	public static function hash_salt_password($password) { //controllo password esistente??
		 return self::hash_password($password) . dechex(rand(1, 65535));
	}
	
	/**
	 * Restituisce la password inserita dall'utente con un hashing SHA a 512 bit
	 *
	 * @param $password
	 *   La stringa contenente la password in chiaro
	 * @return 
	 *   L'hash della password
	 */
	public static function hash_password($password) { //controllo password esistente??
		 return hash('sha512', $password);
	}

	/**
	 * Verifica che l'utente abbia avuto accesso al sito restituendo un valore booleano 
	 * come risposta.
	 *
	 * @param None.
	 * @return 
	 *   Vero se l'utente ha effettuato l'accesso, falso altrimenti.
	 */

	public static function is_user_logged_in() {
		return isset($_SESSION['Username']);
	}

	/**
	 * Verifica se l'utente è un amministratore o meno.
	 *
	 * @param $username
	 *   Il nome dell'utente.
	 * @return
	 *   Vero se l'utente è un amministratore, falso altrimenti.
	 */
	
	public static function is_user_admin($username) {
		$db_handle = new Database();
		$db_handle->connect();
		$query = "SELECT COUNT(*) from AppartenenzaUtenteAGruppo
				  WHERE Utente = '$username' AND Gruppo = 'Amministratori';";
		$risultato = $db_handle->query($query);
		$return_value = mysql_result($risultato, 0);
		$db_handle->disconnect();
		return $return_value;
	}

	/**
	 * Restituisce tutti i dati relativi all'utente sotto forma di array.
	 * 
	 *
	 * @param $username
	 *   Il nome dell'utente.
	 * @return 
	 *   I dati dell'utente come array.
	 */

	public static function get_user_data($username) {
		$db_handle = new Database();
		$db_handle->connect();
		$get_user_data_query = "SELECT * FROM Utenti WHERE NomeUtente = '$username'";
		$risultato = $db_handle->query($get_user_data_query);
		return mysql_fetch_array($risultato);
	}
	
	/**
	 * Restituisce tutti i dati relativi a tutti gli utenti.
	 * 
	 * @param None.
	 * @return 
	 *   I dati dell'utente come array.
	 */

	public static function get_users_data() {
		$db_handle = new Database();
		$db_handle->connect();
		$get_users_data_query = "SELECT * FROM Utenti";
		$risultato = $db_handle->query($get_users_data_query);
		while($temp = mysql_fetch_array($risultato)) $array_risultato[] = $temp;
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Restituisce tutti i dati relativi alla statistiche di un utente sotto forma di array.
	 * 
	 * @param $username
	 *   Il nome dell'utente.
	 * @return 
	 *   I dati delle statistiche di un utente come array.
	 */

	public static function get_statistics($username) {
		$db_handle = new Database();
		$db_handle->connect();
		$get_user_data_query = "SELECT * FROM Statistiche WHERE Utente = '$username'";
		$risultato = $db_handle->query($get_user_data_query);
		return mysql_fetch_array($risultato);
	}

	/**
	 * Modifica il valore di un campo dell'utente
	 * 
	 * @param $username
	 *   Il nome dell'utente
	 * @param $value
 	 *   Il valore del campo da modificare
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public static function edit_user_field($username, $value, $field) {
		$db_handle = new Database();
		$db_handle->connect();
		$disable_query = "UPDATE Utenti SET `$field` = '$value' WHERE NomeUtente = '$username';";
		$risultato = $db_handle->query($disable_query);
		return $risultato;
	}

	/**
	 * Disabilita un utente
	 * 
	 * @param $username
	 *   Il nome dell'utente da disabilitare
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public static function disable_user($username) {
		return self::edit_user_field($username, 0, "Attivo");
	}

	/**
	 * Abilita un utente
	 * 
	 * @param $username
	 *   Il nome dell'utente da abilitare
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public static function enable_user($username) {
		return self::edit_user_field($username, 1, "Attivo");
	}


}
