<?php

/**
 * @file chisiamo.php
 * @brief Pagina di presentazione del progetto
 * 
 * Questa pagina presenta agli utenti informazioni sul progetto
 *
 */

require('include/library/database.php');
require('include/library/categorie.php');
require('include/library/utenti.php');
require('include/library/messaggi.php');
require('include/library/items.php');
require('include/library/operations.php');
require('include/library/images.php');
require('include/library/template.php');
require('include/library/sec_login.php');
include('include/templates/header.inc.php');

?>

<div id="main_content_without_sidebar">

<h2> Chi siamo </h2>
<img src="images/viking-drinking.jpg" style="float:left; margin-right: 10px; margin-bottom: 20px" />
<p> Questo sito è stato creato per facilitare la condivisione dei beni tra le persone, sia nell'offrire che nel richiedere.</p>
<p> Il nome di questo sito è "lagom", concetto di antica tradizione svedese che si concilia parecchio con lo stile di questo progetto. Si narra che al tempo dei vichinghi —nelle pause, dopo il duro lavoro— riempissero un enorme boccale di birra mettendolo su un tavolo. Ogni forte vichingo a turno prendeva un bel sorso dal boccale rimettendolo poi al suo posto.</p>
<p>Il concetto di lagom è che ognuno doveva poter bere un po' di birra dal boccale comune almeno per un turno. Se la birra non fosse stata sufficiente, avrebbe significato che un vichingo si era fatto prendere un po' la mano. Per cui tutti dovevano bere il giusto, o come avrebbero detto loro, il lagom (per essere tutti felici:). </p>
<p> Questo sito prende ispirazione da questi assenati e saggi vichingi, che usufruivano dei beni moderatamente, in misura dei bisogni degli altri. Se hai qualche "bene" (il tuo boccale di birra) di cui potresti fare a meno, puoi offrirlo (offrire un sorso di birra) ad un tuo compagno che potrebbe averne bisogno! </p>

<?php 
include('include/templates/footer.inc.php');

?>
