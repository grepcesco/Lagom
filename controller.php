<?php

/**
 * @file controller.php
 * @brief Pagina di cancellazione dei beni.
 * @todo realizzare sanitization variabili
 * @todo aggiornare documentazione
 * @todo andrebbe rifattorizzato
 * 
 * Questa pagina permette la cancellazione dei beni, dal pannello_utente.php;
 * lavora asincronamente con AJAX.
 * 
 * @see pannello_utente.php
 *
 */

require('include/library/database.php');
require('include/library/utenti.php');
require('include/library/categorie.php');
require('include/library/items.php');
require('include/library/operations.php');
require('include/library/messaggi.php');
require('include/library/images.php');
require('include/library/sec_login.php');

if(isset($_POST['delete']) and isset($_POST['delete_type'])) {
	switch($_POST['delete_type']) {
		case "offer":
			$result = Offerte::delete_offer($_POST['delete']);
			break;
		case "request":
			$result = Richieste::delete_request($_POST['delete']);
			break;
		case "refuse_offer":
			$result = Offer_Operations::disable_offer_operation($_POST['delete']);
			echo $result;
			break;
		case "refuse_request":
			$result = Request_Operations::disable_request_operation($_POST['delete']);
			echo $result;
			break;
		case "msg":
			$result = Messages::delete_message($_POST['delete'], $_POST['Username']);
			echo $result;
			break;
	}
}
// mettere come else if todo
if(isset($_POST['operation_type'])) {
	switch($_POST['operation_type']) {
		case "accept_offer":
			$result = Offer_Operations::set_choosed_offer_operation($_POST['operation_id']);
			echo $result;
			break;
		case "accept_request":
			$result = Request_Operations::set_choosed_request_operation($_POST['operation_id']);
			echo $result;
			break;
		case "categories_changes":
			/* 
			 * Array bidimensionale nella forma:
			 *   [0] => { [0] => "Tipo di cambiamento", [1] => "Nome categoria", [ [2] => "Padre della categoria" ] }
			 */
			$array_cambiamenti = json_decode(stripslashes($_POST['array_changes']));
			foreach($array_cambiamenti as $change) {
				switch($change[0]) {
					case "add_category":
						echo Categorie::insert_category($change[1], $change[2]);
						break;
					case "remove_category":
						echo Categorie::remove_category(trim($change[1]));
						break;
				}
			}
	}
}

?>
