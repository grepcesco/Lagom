<?php

/**
 * @file index.php
 * @brief Pagina principale del sito web
 * @todo Importante. Il sito va riscritto, fondendo il pannello_utente con la 
 *   pagina principale. Va creato un template per ogni pagina. Il programma 
 *   caricherà tutte le librerie usate con autoload. Quando prenderà il tipo
 *   di richiesta caricherà il template con i dati necessari alla visualizzazione.
 *   Per migliorare l'estetica andrà usato l'url rewriting di apache. Implementare
 *   il metodo load.
 * @todo aumentare le statistiche sui beni quando visualizzati (nella funzione?)
 *
 * È la pagina principale del sito. Tramite questa pagina è possibile per 
 *   l'utente visualizzare i beni filtrandoli per tipologia (richieste o offerte)
 *   e categorie.
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
include('include/templates/sidebar.inc.php');

// todo: inserire breadcrumb

if(isset($_GET['request_id'])) {
	// @todo sanitization??
	$request = Richieste::get_request($_GET['request_id'], "BeneID");
	echo '<h2>'.$request['Titolo'].'</h2>';
	echo '<p>'.$request['Descrizione'].'</p>';

	if(Utente::is_user_logged_in()) {
		$proposta_precedente = Request_Operations::get_operation_by_user_on_request($_SESSION['Username'], $_GET['request_id']);
		if (strcasecmp($request['Richiedente'], $_SESSION['Username']) == 0) {
			echo '<p><a href="pannello_utente.php?q=view_request&request_id='.$_GET['request_id'].'"> Visualizza la tua richiesta e relative proposte.</a> </p>';
		} elseif(!empty($proposta_precedente)) {
			echo '<p><a href="pannello_utente.php?q=view_msg&request_operation_id='.$proposta_precedente['OperazioneID'].'">Visualizza la tua proposta ed eventualmente continua la conversazione di mediazione.</a></p>';
		}
		else
			echo '<p><a href="pannello_utente.php?q=send_msg&request_id='.$_GET['request_id'].'">Contatta il richiedente per segnalare la tua disponibilità a fornire il bene</a></p>';
	}

	else
		echo '<p><a href="accedi.php">Accedi al sito</a> o <a href="registrazione.php">registrati</a> per poter contattare il richiedente per segnalare la tua disponibilità a fornire il bene</p>';

	echo '<p class="didascalia">Richiesta effettuata il '.strftime("%d %B",strtotime($request['Data'])).' da '.$request['Richiedente'].' sotto la categoria '.Richieste::get_category_of_request($request['BeneID']).'</p>';

} 

elseif(isset($_GET['offer_id'])) {
	// @todo sanitization??
	// todo: sistemare immagine...
	$offer = Offerte::get_offer($_GET['offer_id'], "BeneID");
	echo '<h2>'.$offer['Titolo'].'</h2>';
	echo Images_View::get_medium_thumb_img_tag($offer['ImmaginePercorsoURL']);
	echo '<p>'.$offer['Descrizione'].'</p>';
	if(Utente::is_user_logged_in()) {
		$proposta_precedente = Offer_Operations::get_operation_by_user_on_offer($_SESSION['Username'], $_GET['offer_id']);

	if (strcasecmp($offer['Offerente'], $_SESSION['Username']) == 0) {
		echo '<p><a href="pannello_utente.php?q=view_offer&offer_id='.$_GET['offer_id'].'"> Visualizza la tua offerta e relative proposte.</a> </p>';
	} elseif(!empty($proposta_precedente)) {
		echo '<p><a href="pannello_utente.php?q=view_msg&offer_operation_id='.$proposta_precedente['OperazioneID'].'">Visualizza la tua proposta ed eventualmente continua la conversazione di mediazione.</a></p>';
	} else {
		echo '<p><a href="pannello_utente.php?q=send_msg&offer_id='.$_GET['offer_id'].'">Contatta l\'offerente per richiedere il bene</a></p>';
	}

	echo '<p class="didascalia">Offerta proposta il '.strftime("%d %B",strtotime($offer['Data'])).' da '.$offer['Offerente'].' sotto la categoria '.Offerte::get_category_of_offer($offer['BeneID']).'</p>';
	}

} else {
	// todo#1cat implementare la rimozione dei filtri per categorie
	// todo#2cat migliorare l'aspetto grafico, creazione classi

	/* Se c'è una categoria filtrata o se c'è una sottocategoria filtrata (ma 
	 * non insieme contemporaneamente) allora imposta il link a X tornare alla 
	 * homepage 
	 */
	if(isset($_GET['cat']) XOR isset($_GET['subcat'])) {
		// e se ci fosse un filtro??
		echo '<div style="float:left; margin-right: 10px; background-color: #eee;">' 
				. (isset($_GET['cat']) ? $_GET['cat'] : $_GET['subcat']) .' <a href="'
				.(isset($_GET['cat']) ? $url_without_cat : $url_without_subcat).'" title="Rimuovi categoria"> X </a>
			  </div>';
	} elseif(isset($_GET['cat']) AND isset($_GET['subcat'])) {
		echo '<div style="float:left; margin-right: 10px; background-color: #eee;">' 
				. $_GET['cat'].' <a href="'. $url_without_cat .'" title="Rimuovi categoria"> X </a>
			  </div>';
		echo '<div style="float:left; margin-right: 10px; background-color: #eee;">' 
				. $_GET['subcat'].' <a href="'. $url_without_subcat .'" title="Rimuovi categoria"> X </a>

			  </div>';
	}

	// todo: hmm sporco
	if(!isset($_GET['filter_by']) or
		(isset($_GET['filter_by']) and $_GET['filter_by'] != 'offers')) {
		if(isset($_GET['subcat'])) // to sistemar
			$request_array = Richieste::get_requests($_GET['subcat'], "category", 8);
		elseif(isset($_GET['cat']))
			$request_array = Richieste::get_requests($_GET['cat'], "category", 8);
		else 
			$request_array = Richieste::get_requests("", "", 8);
	
		// mettere bel codicillo per stamparlo? se disponibile??
		echo '<h2>Richieste</h2>';
		if(empty($request_array)) echo '<p>Non ci sono richieste</p>';
		foreach($request_array as $richiesta) {
			echo '<div class="item_box request_box">
					<a href="'.htmlentities($_SERVER['PHP_SELF']).'?request_id='.$richiesta['BeneID'].'" title="'.$richiesta['Descrizione'].'">';
			// codice per prendere data: date("d/m/y",strtotime($richiesta['Data']))
			echo $richiesta['Titolo']."</a>	<br/>";
			echo "<p>".substr($richiesta['Descrizione'], 0, 80)."...</p>";
			echo "<div class=\"didascalia\">(Inserito il ".strftime("%d %B",strtotime($richiesta['Data']))." da ".$richiesta['Richiedente'].")</div></div>";
		}
		echo '<div class="clear"></div>';
	}
	
	// todo: hmm sporco
	if(!isset($_GET['filter_by']) or
		(isset($_GET['filter_by']) and $_GET['filter_by'] != 'requests')) {
		if(isset($_GET['subcat'])) // to sistemar
			$array_pakkoso = Offerte::get_offers($_GET['subcat'], "category", 8);
		elseif(isset($_GET['cat'])) {
			$array_pakkoso = Offerte::get_offers($_GET['cat'], "category", 8);
		}
		else 
			$array_pakkoso = Offerte::get_offers("", "", 8);

		// mettere bel codicillo per stamparlo? se disponibile??
		echo '<h2>Offerte</h2>'; // cambiare offerta...
		if(empty($array_pakkoso)) echo '<p>Non ci sono offerte</p>';
		foreach($array_pakkoso as $offerta) {
			echo '<div class="item_box">
					<a href="'.htmlentities($_SERVER['PHP_SELF']).'?offer_id='.$offerta['BeneID'].
					'" title="'.$offerta['Descrizione'].'">
					'.$offerta['Titolo'].'<div class="image_container">'
					. Images_View::get_small_thumb_img_tag($offerta['ImmaginePercorsoURL'])."</div></a>";
			echo "<br/><div class=\"didascalia\">(Inserito il ".strftime("%d %B",strtotime($offerta['Data']))." da ".$offerta['Offerente'].")</div></div>";
		}
		echo '<div class="clear"></div>';
		}
	}
?>

<?php include('include/templates/footer.inc.php'); ?>

<script type="text/javascript">
jQuery(document).ready(function(){
	$("#menu ul").hide();
	$("#menu > li > div").click(function(){
	    if(false == $(this).next().is(':visible')) {
	        $('#menu ul').slideUp(300);
	    }
	    $(this).next().slideToggle(300);
	});
	});
</script>
