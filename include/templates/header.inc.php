<!-- header include -->
<!doctype html>
<head>
	<title>Sito web di condivisione</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<link href="css/stile.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="include/js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="include/js/jquery.color.min.js"></script>
	<script type="text/javascript" src="include/js/jquery.validate.min.js"></script> <!-- solo su regisrazione? -->
	<script type="text/javascript" src="include/js/jquery.json-2.3.min.js"></script>
	<script type="text/javascript" src="include/js/common.js"></script>
	<script>
		$(document).ready(function(){
	    	$("#register_form").validate({
  				rules: {
				    email: "required",
				    confirm_email: {
				    	equalTo: "#email"
				    },
					password: "required",
				    password_confirm: {
				    	equalTo: "#password"
				    }
				}
				/*messages: {
					firstname: "Please enter your firstname",
				}*/
		})});
	</script>
</head>
<body>
<div id="wrapper">
<header>
	<h1>
		<a href="index.php"><span id="orange_text">Lagom</span><sub style="font-size:11px">Beta</sub></a>
	</h1>
	<img src="images/logo_temp.png" style="position:absolute; right:30px; bottom: -31px"/>
</header>
<?php
echo '<nav><ul>';
if(Utente::is_user_logged_in()) { ?>
	<li><a href="index.php" title="Vai alla pagina principale"> Home </a> </li>
	<li><a href="pannello_utente.php" title="Entra nella tua pagina personale">Il mio spazio</a></li>
	<li><a href="chisiamo.php">Chi siamo</a></li>
	<li><a href="logout.php">Esci</a></li>
	<div id="notify_box">
		<?php
		// notificare: nuovi messaggi, nuove operazioni, nuove risposte a proprie operazioni
		$unread_messages = Messages::get_n_unread_messages($_SESSION['Username']);
		$unread_received_answers_requests = Request_Operations::get_n_unread_answers_by_user($_SESSION['Username']);
		$unread_received_answers_offers = Offer_Operations::get_n_unread_answers_by_user($_SESSION['Username']);
		$unread_winner_requests = Request_Operations::get_n_unread_winner_operations($_SESSION['Username']);
		$unread_disabled_requests = Request_Operations::get_n_unread_disabled_operations($_SESSION['Username']);
		$unread_winner_offers = Offer_Operations::get_n_unread_winner_operations($_SESSION['Username']);
		$unread_disabled_offers = Offer_Operations::get_n_unread_disabled_operations($_SESSION['Username']);
		$unread_total = $unread_messages + $unread_received_answers_requests
						+ $unread_received_answers_offers + $unread_winner_requests 
						+ $unread_disabled_requests	+ $unread_winner_offers 
						+ $unread_disabled_offers;
		// realizzare funzione template per formazione notifica
		echo "<h3 id=\"notify_box_title\"> Notifiche " . 
				($unread_total == 0 ? "" : "($unread_total)") . "</h3>";

		if($unread_messages) {
			echo "<a class=\"notification_box\" href=\"pannello_utente.php?q=msgbox\">
					Ci sono $unread_messages messaggi nuovi.
				 </a>";
		}
		
		if($unread_received_answers_requests) {
			echo "<a class=\"notification_box\" href=\"pannello_utente.php?q=richieste\">
					Ci sono $unread_received_answers_requests proposte a richieste che non hai ancora letto.
				 </a>";
		}

		if($unread_received_answers_offers) {
			echo "<a class=\"notification_box\" href=\"pannello_utente.php?q=offers\">
					Ci sono $unread_received_answers_offers richieste a offerte che non hai ancora letto.
				 </a>";
		}

		if($unread_winner_requests) {
			echo "<a class=\"notification_box\" href=\"pannello_utente.php?q=ans_richieste\">
					Ci sono $unread_winner_requests richieste che hai vinto che non hai ancora letto.
				 </a>";
		}

		if($unread_disabled_requests) {
			echo "<a class=\"notification_box\" href=\"pannello_utente.php?q=ans_richieste\">
					Ci sono $unread_disabled_requests richieste che sono state rifiutate che non hai ancora letto.
				 </a>";
		}

		if($unread_winner_offers) {
			echo "<a class=\"notification_box\" href=\"pannello_utente.php?q=ans_offers\">
				Ci sono $unread_winner_offers offerte che hai vinto che non hai ancora letto.
			 </a>";
		}

		if($unread_disabled_offers) {
			echo "<a class=\"notification_box\" href=\"pannello_utente.php?q=ans_offers\">
					Ci sono $unread_disabled_offers offerte che sono state rifiutate che non hai ancora letto.
			 </a>";
		}

	?>
</div>

<?php } else { ?>
	<li><a href="index.php" title="Vai alla pagina principale"> Home </a> </li>
	<li><a href="accedi.php" title="Entra nel sito">Accedi</a></li>
	<li><a href="registrazione.php" title="Registrati!">Registrati</a></li>
	<li><a href="chisiamo.php">Chi siamo</a></li> 
<?php } 
	/* farla in AJAX. Sì siamo fighi
	echo '<input type="text" id="ricerca_form" value="Ricerca nel sito..." style="float:right; margin-right: 20px; margin-top:16px; height: 20px"></ul>'; */ ?>
</nav>

