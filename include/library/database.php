<?php

error_reporting(0);

/**
 * @class Database
 *
 * @brief Classe per la gestione dell'accesso alla base di dati.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * della base di dati.
 *
 */

class Database {
	/* come farglielo prendere dal config.php? */
	private $db_host = 'localhost';
	private $db_user = 'utenteprogetto';
	private $db_pass = 'paka';
	private $db_name = 'dbprogetto2';

	private $attiva = false;

	/**
	 * Inizializza la connessione al db.
	 *
	 * @param None
	 * 
	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public function connect() { 
		if(!$this->attiva) {
			$connessione = mysql_connect($this->db_host,$this->db_user,$this->db_pass);
			if($connessione) {
				$selezione_db = mysql_select_db($this->db_name, $connessione);
                if($selezione_db) {
                    $this->attiva = true;
                    return true;
                } else return false; // la selezione del db è fallita
            } else return false; // la connessione è fallita
        } else return true; // la connessione era già attiva
	}

	/**
	 * Chiude la connessione al db.
	 *
	 * @param None
	 * 
	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public function disconnect() { 
		if($this->attiva) {
			if(mysql_close()) {
				$this->attiva = false;
				return true;
			} else return false; // problemi nel chiudere la connessione
		} else return true;
	}

	/**
	 * Esegue una interrogazione e ne restituisce l'output.
	 *
	 * @param $sql
	 *   L'interrogazione desiderata
	 * 
	 * @return
	 *   L'esito dell'interrogazione
	 */

	public function query($sql) { 
		return mysql_query($sql);
	}

	/**
	 * Apre una connessione al db e restituisce un oggetto database
	 *
	 * @param None
	 * 
	 * @return
	 *   Il riferimento all'oggetto database
	 */

	public static function open_db() {
		$db_handle = new Database();
		$db_handle->connect();
		return $db_handle;
	}

}
