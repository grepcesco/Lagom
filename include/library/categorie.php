<?php

/**
 * @class Categorie.
 *
 * @brief Classe per la gestione delle categorie.
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * delle categorie.
 *
 */

class Categorie {
	const DEFAULT_CATEGORY_THUMB = "";

	/**
	 * Restituisce tutte le categorie presenti nel sito.
	 *
	 * @param $with_image
	 *   Se falso (valore predefinito) torna solo i titoli delle categorie.
	 *   Altrimenti ritorna anche le immagini di anteprima.
	 * @return 
	 *   Tutte le categorie presenti nel sito come array.
	 */

	public static function get_categories($genitore, $with_image = False) { // andrebbe migliorata l'estetica...
		$db_handle = Database::open_db();
		if($genitore == '') {
			$query = "SELECT `Titolo`".($with_image?", `ImmaginePercorsoURL`":"")." FROM Categorie WHERE `ParentID` IS NULL;";
		} else {
			$query = "SELECT `Titolo`".($with_image?", `ImmaginePercorsoURL`":"")." FROM Categorie WHERE `ParentID` = '".$genitore."';";
		}
		$risultato = $db_handle->query($query);
		while($temp = mysql_fetch_array($risultato)) $array_risultato[] = $temp;
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Indica se la categoria è una categoria senza genitore.
	 * 
	 * @param $category
	 *   La categoria da verificare
	 * @return
	 *   Vero se la categoria non ha genitori, falso altrimenti (è una sottocategoria).
	 */

	public static function is_parent_category($category) {
		$db_handle = Database::open_db();
		$query = "SELECT COUNT(*) FROM Categorie WHERE `Titolo` = '$category' AND `ParentID` IS NULL;";
		$risultato = $db_handle->query($query);
		$return_value = mysql_result($risultato, 0);
		$db_handle->disconnect();
		return $return_value;
	}

	/**
	 * Inserisce una categoria nel sito.
	 *
	 * @param $title
	 *   La stringa contenente il titolo della categoria da inserire.
	 * @param $parent
	 *   // todo
	 * @param $image
	 *   L'indirizzo all'immagine che rappresenta la categoria.
	 *   Se non viene fornito alcun indirizzo viene inserito un'immagine
	 *   predefinita.
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public static function insert_category($title, $parent, $image = self::DEFAULT_CATEGORY_THUMB) { 
		$db_handle = new Database();
		$db_handle->connect();
		if($parent != "Nessuno") 
			$query_to_execute = "INSERT INTO Categorie VALUES ('".mysql_real_escape_string($title)."', '".mysql_real_escape_string($parent)."');";
		else
			$query_to_execute = "INSERT INTO Categorie VALUES ('".mysql_real_escape_string($title)."', NULL);";
		$esito_inserimento = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $esito_inserimento;
	}
	
	/**
	 * Rimuove una categoria nel sito.
	 *
	 * @param $title
	 *   La stringa contenente il titolo della categoria da rimuovere.
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 */

	public static function remove_category($title) { 
		$db_handle = new Database();
		$db_handle->connect();
		$query_to_execute = "DELETE FROM Categorie WHERE Titolo = '$title';";
		$esito_inserimento = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $esito_inserimento;
	}

}

?>
