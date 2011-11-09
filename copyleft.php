<?php

/**
 * @file privacy.php
 * @brief Pagina informativa privacy
 * @author  Francesco Lorenzon <grepcesco@gmail.com>
 * @todo va riempita
 *
 * Una classica pagina statica.
 *
 */


require('include/library/messaggi.php');
require('include/library/operations.php');
require('include/library/images.php');
require('include/library/template.php');
require('include/library/database.php');
require('include/library/categorie.php');
require('include/library/utenti.php');
require('include/library/items.php');
require('include/library/sec_login.php');
include('include/templates/header.inc.php');

?>

<div id="main_content_without_sidebar">

<h2> Licenza del sito </h2>
<img src="images/Gpl-v3-logo.png" style="float:left; margin-right: 10px; margin-bottom: 30px" />
<p> The license under which this website is released is the GPLv3 from the Free Software Foundation. A copy of the license is included with every copy of Lagom, but you can also read the text of the license here. </p>

<?php 
include('include/templates/footer.inc.php');
?>
