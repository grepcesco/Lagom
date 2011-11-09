<?php

/**
 * @file registrazione.php
 * @brief Script di registrazione di un utente al sito web
 *
 * Tramite questa pagina è possibile per un utente registrarsi al sito.
 *
 */


require('include/library/database.php');
require('include/library/utenti.php');
require('include/library/categorie.php');
include('include/templates/header.inc.php');
?><div id="main_content_without_sidebar"><?php
include('include/snippet/registrazione_snippet.php');
include('include/templates/footer.inc.php'); 

?>
