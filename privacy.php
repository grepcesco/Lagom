<?php

/**
 * @file privacy.php
 * @brief Pagina informativa privacy
 *
 * Una classica pagina statica.
 *
 */

require('include/library/database.php');
require('include/library/categorie.php');
require('include/library/utenti.php');
require('include/library/items.php');
require('include/library/sec_login.php');
include('include/templates/header.inc.php');

?>

<div id="main_content_without_sidebar">

<h2> Copyleft </h2>
<img src="http://blogs.suntimes.com/cornerkicks/Snoopy--Joe-Cool--Maxi-Posters-331290.jpg" style="float:left; margin-right: 10px; margin-bottom: 20px" />
<p> Vogliamo conquistare il mondo. Chiunque si metterà in mezzo sarà annientato. </p>

<?php 
include('include/templates/footer.inc.php');
?>
