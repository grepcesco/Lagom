<?php

/**
 * @file items.php
 * @brief File con classi Items, Richieste e Offerte
 *
 * È uno tra i file più importanti del progetto, in quanto contiene tutte le classi
 *   con metodi per lavorare con i beni (Richieste e Offerte).
 *
 */

/**
 * @class Items
 *
 * @brief Classe Items
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * dei beni in generale.
 *
 */

class Items {
	/**
	 * Restituisce un'array contenente i dati selezionati riguardanti un bene.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $property
	 *   Specifica il tipo di colonna specifica da estrarre.
	 * @param $keyvalue
	 *   Il valore di ricerca (può essere l'ID del bene o il suo titolo)
	 * @param $key
	 *   Il tipo di chiave per la ricerca (può essere l'ID del bene o il titolo)
	 * @return 
	 *   I dati selezionati riguardanti un bene.
	 * @todo 
	 *   Inserire gestione errori.
	 */

	protected static function get_item($type, $property, $keyvalue, $key) {
		$db_handle = Database::open_db();
		$query_to_execute = "SELECT $property FROM $type WHERE $key = '$keyvalue'";
		$risultato = $db_handle->query($query_to_execute);
		$array_risultato = mysql_fetch_array($risultato);
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Restituisce il valore di una proprietà specifica di un bene.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $property
	 *   Specifica il tipo di valore specifico da estrarre.
	 * @param $keyvalue
	 *   Il valore di ricerca (può essere l'ID del bene o il suo titolo)
	 * @param $key
	 *   Il tipo di chiave per la ricerca (può essere l'ID del bene o il titolo)
	 * @return 
	 *   Il valore specifico che si voleva ottenere.
	 */

	protected static function get_property_by_item($type, $property, $keyvalue, $key) {
		$risultato = self::get_item($type, $property, $keyvalue, $key);
		return $risultato["$property"];
	}

	/**
	 * Restituisce i beni che corrispondono a determinate caratteristiche.
	 *
	 * Restituisce i beni che corrispondono a determinate caratteristiche. La
	 *   funzione prende elementi casualmente attraverso l'uso di RAND(). Tale
	 *   approccio è poco performante, ma per tabelle di piccole-medie dimensioni
	 *	 costituisce la soluzione più semplice.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $keyvalue
	 *   Il valore di ricerca
	 * @param $filter
	 *   Il tipo di chiave per la ricerca
	 * @param $limit
	 *   Il limite come numero di record da restituire.
	 *
	 * @todo 
	 *   spiegare comportamento con categorie
	 * @todo
	 *   selezionare solo beni attivi!!
	 */
	protected static function get_items($type, $keyvalue, $filter, $limit) { 
		$type_of_operator = $type=="Richieste" ? "Richiedente" : "Offerente" ;
		$type_of_operation = $type=="Richieste" ? "Richiesta" : "Offerta" ;
		// per le categorie andrebbe fatta una funzione a parte todo
		if($filter == 'category') { 
			if(Categorie::is_parent_category($keyvalue)) {
				$query_to_execute = "SELECT BeneID, Titolo, Data, Descrizione, ImmaginePercorsoURL, 
									 Quantita, Disponibile, $type_of_operator 
									 FROM Appartenenza{$type}ACategorie AS AC, $type as Bene 
									 WHERE AC.$type_of_operation = Bene.BeneID 
									 AND Bene.Disponibile = '1'
									 AND AC.Categoria IN 
										(SELECT Titolo FROM Categorie WHERE ParentID = '$keyvalue') 
									 UNION 
									 SELECT BeneID, Titolo, Data, Descrizione, ImmaginePercorsoURL, 
									 Quantita, Disponibile, $type_of_operator 
									 FROM Appartenenza{$type}ACategorie AS AC, $type AS Bene 
									 WHERE AC.$type_of_operation = Bene.BeneID AND AC.Categoria = '$keyvalue'
									 AND Bene.Disponibile = '1'
									 ORDER BY RAND()";
			} else {
				$query_to_execute = "SELECT BeneID, Titolo, Data, Descrizione, ImmaginePercorsoURL,
									 Quantita, Disponibile, $type_of_operator 
									 FROM Appartenenza".$type."ACategorie AS AC, $type as Off 
									 WHERE AC.$type_of_operation = Off.BeneID 
									 AND Categoria = '$keyvalue'
									 AND Off.Disponibile = '1'
									 ORDER BY RAND()";
			}
		}
		elseif($filter != '') { 
			$query_to_execute = "SELECT * FROM $type 
								 WHERE $filter = '$keyvalue' AND Disponibile = '1'
								 ORDER BY RAND()"; 
		} 
		else { $query_to_execute = "SELECT * FROM $type WHERE Disponibile = '1' ORDER BY RAND()"; }
	
		if($limit > 0) 
			$query_to_execute .= " LIMIT $limit;";
		else
			$query_to_execute .= ";";
		
		$db_handle = new Database();
		$db_handle->connect();
		$risultato = $db_handle->query($query_to_execute);
		while($temp = mysql_fetch_array($risultato)) $array_risultato[] = $temp;
		$db_handle->disconnect();
		return $array_risultato;
	}

	/**
	 * Inserisce un bene nel database.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $title
	 *   Il titolo del bene da inserire
	 * @param $description
	 *   La descrizione del bene da inserire
	 * @param $imageURL
	 *   L'indirizzo dell'immagine del bene da inserire
	 * @param $quantity
	 *   La quantità numerica del bene da inserire
	 * @param $category
	 *   La categoria del bene da inserire
	 * @param $operator
	 *   L'utente che ha inserito il bene
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * @todo
	 *   Sarebbe carino realizzare una funzione che pulisca i valori in input, senza
	 *   doverli vedere tutti brutti dentro $query_to_execute
	 */

	protected static function insert_item($type, $title, $description, $imageURL, $quantity, $category, $operator) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_of_operator = $type=="Richieste" ? "Richiedente" : "Offerente" ;
		$query_to_execute = "INSERT INTO ".$type." (`Titolo`, `Descrizione`, `ImmaginePercorsoURL`, `Quantita`, `Disponibile`, `$type_of_operator`) VALUES ('".mysql_real_escape_string($title)."', '".mysql_real_escape_string($description)."', '".mysql_real_escape_string($imageURL)."', '".mysql_real_escape_string($quantity)."', '1', '".mysql_real_escape_string($operator)."');";
		$esito_inserimento = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		$esito_associazione = self::associate_item_to_category($type, $title, $category);
		return $esito_inserimento and $esito_associazione;
	}

	/**
	 * Associa un'elemento ad una categoria.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $title
	 *   Il titolo del bene da inserire
	 * @param $category
	 *   La categoria a cui va associato il bene
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	protected static function associate_item_to_category($type, $title, $category) {
		$beneid = self::get_property_by_item($type, "BeneID", $title, "Titolo");
		$db_handle = new Database();
		$db_handle->connect();
		$query_to_execute = "INSERT INTO Appartenenza".$type."ACategorie VALUES ('$category', '$beneid');";
		$esito_associazione = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $esito_associazione;
	}

	/**
	 * Cancella un bene dalla base di dati.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $item_id
	 *   L'ID del bene da cancellare
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	protected static function delete_item($type, $item_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$type == "Richieste" ? $type_of_item = "Richiesta" : $type_of_item = "Offerta";
		// rimuovi anche immagine..
		$query_to_execute1 = "DELETE FROM Appartenenza".$type."ACategorie WHERE $type_of_item = '$item_id';";
		$query_to_execute2 = "DELETE FROM $type WHERE BeneID = '$item_id';";
		$esito_cancellazione = $db_handle->query($query_to_execute1) and $db_handle->query($query_to_execute2);
		$db_handle->disconnect();
		// todo: bisogna mettere on cascade delete...
		//return $esito_cancellazione;
		return $esito_cancellazione;
	}

	/**
	 * Modifica un bene della base di dati.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $item_id
	 *   L'ID del bene da cancellare
	 * @param $keyvalue
	 *   Il valore del campo da moficare
	 * @param $key
	 *   Il campo da modificare
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	protected static function edit_item($type, $id_item, $keyvalue, $key) {
		$db_handle = new Database();
		$db_handle->connect();
		$query_to_execute = "UPDATE $type SET $key = '$keyvalue' WHERE BeneID = '$id_item';";
		$esito = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $esito;
	}

	/**
	 * Diminuisce di un valore il numero di elementi disponibili per un bene.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $item_id
	 *   L'ID del bene da decrementare la quantità
	 * @return 
	 *   Il numero di elementi disponibili aggiornati
	 *   
	 */

	protected static function decrease_quantity($type, $item_id) {
		$item_number = get_property_by_item($type, "Quantita", $keyvalue, $key);
		if($item_number > 0) {
			$db_handle = Database::open_db();
			$query_to_execute = "UPDATE $type SET `Quantita` = `Quantita` - 1 WHERE BeneID = '$item_id'";
			$db_handle->query($query_to_execute);
			$item_number--;
			if($item_number == 0)
				$query_to_execute = "UPDATE $type SET `Disponibile` = 0 WHERE BeneID = '$item_id'";
				$db_handle->query($query_to_execute);
		}
		$db_handle->disconnect();
		// non sarebbe meglio con un valore booleano di successo ? todo
		return $item_number;
	}

	/**
	 * Verifica se un bene è disponibile o meno
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $keyvalue
	 *   Il valore del campo usato come filtro
	 * @param $key
	 *   Il campo usato come filtro
	 * @return 
	 *   Vero se l'elemento è disponibile, falso altrimenti.
	 *   
	 */

	protected static function is_item_available($type, $keyvalue, $key) { 
		return (self::get_property_by_item($type, "Disponibile", $keyvalue, $key));
	}

	/**
	 * Restituisce la categoria di un bene.
	 *
	 * @param $type
	 *   La tipologia di bene (Richiesta o Offerta)
	 * @param $id_item
	 *   L'ID del bene	 
	 * @return 
	 *   La categoria del bene indicato
	 *   
	 */

	public static function get_category_of_item($type, $id_item) {
		$db_handle = new Database();
		$db_handle->connect();
		$type_of_operation = $type=="Richieste" ? "Richiesta" : "Offerta" ;
		$query_to_execute = "SELECT Categoria FROM Appartenenza".$type."ACategorie AS AC, $type AS Item WHERE AC.$type_of_operation = '$id_item' AND Item.BeneID = '$id_item'";
		$categoria = mysql_fetch_assoc($db_handle->query($query_to_execute));
		$db_handle->disconnect();
		return $categoria['Categoria'];
	}
	
	/**
	 * Rende non più disponibile un bene. È stata dunque portata a successo l'operazione.
	 *   
	 * @param $type
	 *   La tipologia di bene
	 * @param $item_id
	 *   L'ID del bene
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 * @todo 
	 *   Sistemare il valore di ritorno
	 */

	protected static function disable_item($type, $item_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$query_to_execute = "UPDATE $type SET Disponibile = '0' WHERE BeneID = '$item_id';";
		$return_value = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $return_value;
	}

	/**
	 * Incrementa il numero di visite di un bene.
	 *   
	 * @param $type
	 *   La tipologia di bene
	 * @param $item_id
	 *   L'ID del bene
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	protected static function increment_views($type, $item_id) {
		$db_handle = new Database();
		$db_handle->connect();
		$query_to_execute = "UPDATE $type SET ViewsNumber = ViewsNumber + 1 WHERE BeneID = '$item_id';";
		$return_value = $db_handle->query($query_to_execute);
		$db_handle->disconnect();
		return $query_to_execute;
	}
}

/**
 * @class Richieste
 *
 * @brief Classe Richieste
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * delle richieste in generale.
 *
 */

class Richieste extends Items { 

	/**
	 * Restituisce un'array contenente i dati selezionati riguardanti una richiesta.
	 *
	 * @param $keyvalue
	 *   Il valore di ricerca (può essere l'ID del bene - BeneID - o il suo titolo). 
	 * @param $key
	 *   Il tipo di chiave per la ricerca (può essere l'ID del bene o il titolo).
	 *   Parametro facoltativo. Il valore predefinito è il "Titolo".
	 * @return 
	 *   I dati selezionati riguardanti una richiesta.
	 */

	public static function get_request($keyvalue, $key = "Titolo") {
		return parent::get_item("Richieste", "*", $keyvalue, $key);
	}

	/**
	 * Restituisce un'array contenente tutte le richieste. Le richieste possono essere
	 *   filtrate in diversi modi (categoria, utente ecc). È possibile indicare anche
	 *   un limite al numero di risultati tornati.
	 *
	 * @param $keyvalue
	 *   Il valore del campo usato come filtro
	 * @param $filter
	 *   Il tipo di chiave per la ricerca 	 
	 * @param $limit
	 *   Numero che limita il numero di risultati tornati dalla funzione. Se impostato
	 *   a zero non viene imposto alcun limite.
	 * @return 
	 *   Le richieste che corrispondono alla ricerca.
	 */

	public static function get_requests($keyvalue, $filter, $limit) {
		return parent::get_items("Richieste", "$keyvalue", $filter, $limit);
	}

	/**
	 * Restituisce il valore di una proprietà specifica di una richiesta.
	 *
	 * @param $property
	 *   Specifica il tipo di valore specifico da estrarre.
	 * @param $keyvalue
	 *   Il valore di ricerca (può essere l'ID del bene o il suo titolo)
	 * @param $key
	 *   Il tipo di chiave per la ricerca (può essere l'ID del bene o il titolo)
	 * @return 
	 *   Il valore specifico che si voleva ottenere.
	 */

	public static function get_property_by_item($property, $keyvalue, $key) { 
		return parent::get_property_by_item("Richieste", $property, $keyvalue, $key);
	}

	/**
	 * Inserisce una richiesta.
	 *
	 * @param $title
	 *   Il titolo della richiesta
	 * @param $description
	 *   La descrizione della richiesta
	 * @param $imageURL
	 *   Il nome dell'immagine che rappresenta il bene
	 * @param $quantity  
	 *	 La quantità di elementi richiesti per la richiesta
	 * @param $category
	 *   La categoria di inserimento
	 * @param $richiedente
	 *   L'esecutore della richiesta
	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function insert_request($title, $description, $imageURL, $quantity, $category, $richiedente) {
		return parent::insert_item("Richieste", $title, $description, $imageURL, $quantity, $category, $richiedente);
	}

	/**
	 * Cancella una richiesta
	 *
	 * @param $id_request
	 *   L'ID della richiesta
	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function delete_request($id_request) {
		return parent::delete_item("Richieste", $id_request);
	}
	
	/**
	 * Modifica il campo di una richiesta
	 *
	 * @param $id_request
	 *   L'ID della richiesta
	 * @param $keyvalue
	 *   Il valore del campo da modificare
	 * @param $key
	 *   Il campo da modificare
	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function edit_request($id_request, $keyvalue, $key) {
		return parent::edit_item("Richieste", $id_request, $keyvalue, $key);
	}

 	/**
	 * Decrementa il numero di elementi disponibili per una richiesta selezionata
	 *   in base ad alcuni parametri
	 *
	 * @param $request_id
	 *   L'ID della richiesta
 	 * @return
	 *   Il numero di elementi disponibili aggiornati
	 */   

	public static function decrease_quantity($id_request) {
		return parent::decrease_quantity("Richieste", $keyvalue, $key);
	}

	/**
	 * Verifica che una richiesta sia disponibile
 	 *
	 * @param $keyvalue
	 *   Il valore del campo di ricerca
 	 * @param $key
	 *   Il campo utilizzato come filtro
 	 * @return
	 *   Vero se la richiesta è disponibile, falso altrimenti.
	 */

	public static function is_request_available($keyvalue, $key) { 
		return parent::is_item_available("Richieste", $keyvalue, $key);
	}

	/**
	 * Restituisce la categoria di una richiesta
	 *
	 * @param $id_item
	 *   L'ID della richiesta
	 * @return
	 *   La categoria a cui appartiene la richiesta
	 */
	
	public static function get_category_of_request($id_item) {
		return parent::get_category_of_item("Richieste", $id_item);
	}

	/**
	 * Disabilita una richiesta, segnandola come inattiva.
	 *   
	 * @param $item_id
	 *   L'ID della richiesta
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	public static function disable_request($id_item) {
		return parent::disable_item("Richieste", $id_item);
	}

	/**
	 * Incrementa il numero di visite di una richiesta.
	 *   
	 * @param $item_id
	 *   L'ID del bene
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function increment_views($item_id) {
		return parent::increment_views("Richieste", $item_id);
	}

}

/**
 * @class Offerte
 *
 * @brief Classe Offerte
 *
 * Questa classe fornisce tutta una serie di metodi di supporto alla gestione
 * delle offerte in generale.
 *
 */

class Offerte extends Items { 

	/**
	 * Restituisce un'array contenente i dati selezionati riguardanti una offerta.
	 *
	 * @param $keyvalue
	 *   Il valore di ricerca (può essere l'ID del bene - BeneID - o il suo titolo). 
	 * @param $key
	 *   Il tipo di chiave per la ricerca (può essere l'ID del bene o il titolo).
	 *   Parametro facoltativo. Il valore predefinito è il "Titolo".
	 * @return 
	 *   I dati selezionati riguardanti una offerta.
	 */

	public static function get_offer($keyvalue, $key = "Titolo") {
		return parent::get_item("Offerte", "*", $keyvalue, $key);
	}

	/**
	 * Restituisce un'array contenente tutte le offerte. Le offerte possono essere
	 *   filtrate in diversi modi (categoria, utente ecc). È possibile indicare anche
	 *   un limite al numero di risultati tornati.
	 *
	 * @param $keyvalue
	 *   Il valore del campo usato come filtro
	 * @param $filter
	 *   Il tipo di chiave per la ricerca 	 
	 * @param $limit
	 *   Numero che limita il numero di risultati tornati dalla funzione. Se impostato
	 *   a zero non viene imposto alcun limite.
	 * @return 
	 *   Le offerte che corrispondono alla ricerca.
	 */

	public static function get_offers($keyvalue, $filter, $limit) { 
		return parent::get_items("Offerte", $keyvalue, $filter, $limit);
	}


	/**
	 * Restituisce il valore di una proprietà specifica di una offerta.
	 *
	 * @param $property
	 *   Specifica il tipo di valore specifico da estrarre.
	 * @param $keyvalue
	 *   Il valore di ricerca (può essere l'ID del bene o il suo titolo)
	 * @param $key
	 *   Il tipo di chiave per la ricerca (può essere l'ID del bene o il titolo)
	 * @return 
	 *   Il valore specifico che si voleva ottenere.
	 */

	public static function get_property_by_item($property, $keyvalue, $key) { 
		return parent::get_property_by_item("Offerte", $property, $keyvalue, $key);
	}


	/**
	 * Inserisce una offerta.
	 *
	 * @param $title
	 *   Il titolo della offerta
	 * @param $description
	 *   La descrizione della offerta
	 * @param $imageURL
	 *   Il nome dell'immagine che rappresenta il bene
	 * @param $quantity  
	 *	 La quantità di elementi richiesti per la offerta
	 * @param $category
	 *   La categoria di inserimento
	 * @param $richiedente
	 *   L'esecutore della offerta
	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function insert_offer($title, $description, $imageURL, $quantity, $category, $offerente) {
		return parent::insert_item("Offerte", $title, $description, $imageURL, $quantity, $category, $offerente);
	}


	/**
	 * Cancella una offerta
	 *
	 * @param $id_request
	 *   L'ID della offerta
	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function delete_offer($offer_id) {
		return parent::delete_item("Offerte", $offer_id);
	}

	/**
	 * Modifica il campo di una offerta
	 *
	 * @param $id_request
	 *   L'ID della offerta
	 * @param $keyvalue
	 *   Il valore del campo da modificare
	 * @param $key
	 *   Il campo da modificare
	 * @return
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function edit_offer($id_offer, $keyvalue, $key) {
		return parent::edit_item("Offerte", $id_offer, $keyvalue, $key);
	}

 	/**
	 * Decrementa il numero di elementi disponibili per una offerta selezionata
	 *   in base ad alcuni parametri
	 *
	 * @param $request_id
	 *   L'ID della offerta
 	 * @return
	 *   Il numero di elementi disponibili aggiornati
	 */ 

	public static function decrease_quantity($keyvalue, $key) {
		return parent::decrease_quantity("Offerte", $keyvalue, $key);
	}

	/**
	 * Verifica che una offerta sia disponibile
 	 *
	 * @param $keyvalue
	 *   Il valore del campo di ricerca
 	 * @param $key
	 *   Il campo utilizzato come filtro
 	 * @return
	 *   Vero se la offerta è disponibile, falso altrimenti.
	 */

	public static function is_offer_available($keyvalue, $key) { 
		return parent::is_item_available("Offerte", $keyvalue, $key);
	}

	/**
	 * Restituisce la categoria di una offerta
	 *
	 * @param $id_item
	 *   L'ID della offerta
	 * @return
	 *   La categoria a cui appartiene la offerta
	 */

	public static function get_category_of_offer($id_item) {
		return parent::get_category_of_item("Offerte", $id_item);
	}

	/**
	 * Disabilita una offerta, segnandola come inattiva.
	 *   
	 * @param $item_id
	 *   L'ID della offerta
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *   
	 */

	public static function disable_offer($id_item) {
		return parent::disable_item("Offerte", $id_item);
	}
	
	/**
	 * Incrementa il numero di visite di una offerta.
	 *   
	 * @param $item_id
	 *   L'ID del bene
	 * @return 
	 *   Vero se l'operazione ha avuto successo, falso altrimenti.
	 *
	 */

	public static function increment_views($item_id) {
		return parent::increment_views("Offerte", $item_id);
	}


}
