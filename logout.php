<?php

/**
 * @file logout.php
 * @brief Script di disconnessione dal sito web
 *
 * Tramite questa pagina viene effettuato il logout
 *
 */

require('include/library/database.php');
require('include/library/utenti.php');
require('include/library/messaggi.php');
require('include/library/operations.php');
require('include/library/sec_login.php');
include('include/templates/header.inc.php');

if(Utente::is_user_logged_in()) {  
	session_destroy();  
	session_regenerate_id();  
	header('location:index.php');
}  

?>
