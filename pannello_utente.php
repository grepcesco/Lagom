<?php

/**
 * @file
 * @brief Pannello generale dell'utente
 * @todo inserire i messaggi di errore nell'inserimento di offerte e richieste
 * @todo fare funzione per pulire le variabili in ingresso (trim)
 * @todo nella modifica delle offerte e richieste, implementare il cambio
 *   dell'immagine
 * @todo Sistemare aspetto grafico tabelle (low priority)
 * @todo accesso autorizzato solo se loggati
 * @todo invio messaggio... selezionando utenti possibili tramite ajax
 * @todo in view_offer: impostare richiesta di conferma JS
 * @todo aggiungere feed back visivo a rifiuto di un'operazione
 * @todo se l'utente ha già fatto un offerta o richiesta su un bene, dovrebbe
 *   essere reindirizzato su tale pagina (però forse è meglio farlo sull'index)
 *
 * Tramite questa pagina è possibile per l'utente:
 *   - Visualizzare e modificare il proprio profilo;
 *   - Visualizzare e inviare messaggi;
 *   - Visualizzare e modificare le proprie offerte;
 *   - Visualizzare e modificare le proprie richieste.
 *
 */

require('include/library/database.php');
require('include/library/utenti.php');
require('include/library/messaggi.php');
require('include/library/categorie.php');
require('include/library/items.php');
require('include/library/operations.php');
require('include/library/template.php');
require('include/library/images.php');
require('include/library/sec_login.php');
include('include/templates/header.inc.php');

?>
<aside>
	<ul id="menu">
		<li><div>Il mio profilo</div>
		<ul>
			<li><a href="pannello_utente.php?q=user_info">Anteprima</a></li>
			<li><a href="pannello_utente.php?q=edit_user">Modifica dati</a></li>
		</ul>
		</li> 
		<li><div>Messaggi <?php 
				$new_messages = Messages::get_n_unread_messages($_SESSION['Username']); 
				echo $new_messages == 0 ? "" : "($new_messages)";
			?></div>
			<ul>
				<li><a href="pannello_utente.php?q=msgbox">In arrivo<?php 
					echo $new_messages == 0 ? "" : " ($new_messages)"; 
				?> </a></li>
				<li><a href="pannello_utente.php?q=sentbox">Inviati</a></li>
			</ul> 
		</li>
		<li><div>Richieste<?php 
				$new_requests = Request_Operations::get_n_unread_operations($_SESSION['Username']); 
				echo $new_requests == 0 ? "" : "($new_requests)";
			?></div>
		<ul>
			<li><a href="pannello_utente.php?q=richieste">Visualizza le tue richieste</a></li>
			<li><a href="pannello_utente.php?q=ans_richieste">Visualizza le richieste a cui hai dato una risposta<?php 
					echo $new_requests == 0 ? "" : " ($new_requests)"; 
				?></a></li>
			<li><a href="pannello_utente.php?q=add_request">Aggiungi una nuova richiesta</a></li>
		</ul>
		</li> 
		<li><div>Offerte<?php 
				$new_offers = Offer_Operations::get_n_unread_operations($_SESSION['Username']); 
				echo $new_offers == 0 ? "" : "($new_offers)";
			?></div>
		<ul>
			<li><a href="pannello_utente.php?q=offers">Visualizza le tue offerte</a></li>
			<li><a href="pannello_utente.php?q=ans_offers">Visualizza le offerte che hai richiesto<?php 
					echo $new_offers == 0 ? "" : " ($new_offers)"; 
				?></a></li>
			<li><a href="pannello_utente.php?q=add_offer">Aggiungi una nuova offerta</a></li>
		</ul>
		</li> 
		<?php if(Utente::is_user_admin($_SESSION['Username'])) { ?>
		<li><div>Amministra</div>
		<ul>
			<li><a href="pannello_utente.php?q=admin_users">Amministra utenti</a></li>
			<li><a href="pannello_utente.php?q=admin_categories">Amministra categorie</a></li>
			<li><a href="pannello_utente.php?q=admin_offers">Amministra offerte</a></li>
			<li><a href="pannello_utente.php?q=admin_requests">Amministra richieste</a></li>
		</ul>
		</li>
		<?php } ?>
		<li><div></div></li>
	</ul>
</aside>
<div id="main-content">
<?php 
switch($_GET['q']) {

	/****************************************
	*		  CASE MSGBOX					*
	****************************************/

	case "msgbox":
		echo Messages_View::get_messages_table($_SESSION['Username']);
		break;
	case "sentbox":
		echo '<h2>Messaggi inviati</h2>';
		$messaggi = Messages::get_sent_messages($_SESSION['Username']);
		if(empty($messaggi)) echo '<p>Non ci sono messaggi</p>';
		else {
			echo '<table class="tabella"><tr><th>Data</th><th>Destinatario</th><th colspan="2">Titolo</th><th>Azioni</th></tr>';
			foreach($messaggi as $msg) {
				$link = '<a href="pannello_utente?q=view_msg&msg_id=' . 
							$msg['MsgID'].'&destinatario='.$msg['Destinatario'].'" >';
				$delete_action_tag = "<a class=\"remove\" id=\"msg-".$msg['MsgID']."\" href=\"#\" title=\"Cancella messaggio\"><img src=\"images/delete.png\" /></a>";
				echo '<tr>';
				echo '<td>'.$msg['DataInvio'].'</td>';
				echo '<td>'.$msg['Destinatario'].'</td>';
				echo '<td>'.$link.$msg['Titolo'].'</a></td>';
				echo '<td>'.$msg['LEFT(Testo, 45)'].'... </td>';
				echo '<td>'.$delete_action_tag.' </td>';
				// se ci sono beni associati...
				// echo '<td>'.$messaggi[''].'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		break;
	case "view_msg":
	case "send_msg":
		if(isset($_POST['send_msg_request'])) {
			$msg_title = isset($_POST['msg_title']) ? trim($_POST['msg_title']) : '';
			$msg_body = isset($_POST['msg_body']) ? trim($_POST['msg_body']) : '';
			$receiver = isset($_POST['receiver']) ? trim($_POST['receiver']) : '';
			$parent_message = isset($_POST['parent_message']) ? trim($_POST['parent_message']) : '';
			if(!empty($cp_ans)) $error["SpamDetected"] = "A bot may not injure (or simply annoy) a human being or, through inaction, allow a human being to come to harm";
			empty($msg_title) ? $error["MissingMsgTitle"] = "Il titolo del messaggio è incompleto" : '';
			empty($msg_body) ? $error["MissingMsgBody"] = "Manca il corpo del messaggio" : ''; 
			empty($receiver) ? $error["MissingReceiver"] = "Manca il destinatario" : ''; 

			if(empty($error)) {
				// da sistemare la scoperta degli errori nei messaggi
				if(empty($parent_message)) {
					$id_msg = Messages::send_message($_SESSION['Username'], 
														$receiver, 
														$msg_title, 
														$msg_body);
					$messaggio = '<p> Messaggio inviato correttamente. </p>';
				}
				else {
					$id_msg = Messages::send_message($_SESSION['Username'], 
														$receiver, 
														$msg_title, 
														$msg_body, 
														$parent_message);
					$messaggio = '<p> Messaggio inviato correttamente. </p>';
				}
				if(isset($_POST['offer_id'])) {
					if(Offer_Operations::answer_offer($_POST['offer_id'], $id_msg, 1)) {
						$messaggio = '<p> Risposta ad offerta inviata correttamente. </p>';
					}
				}

				elseif(isset($_POST['request_id'])) {
					if(Request_Operations::answer_request($_POST['request_id'], $id_msg, 1)) {
						$messaggio = '<p> Risposta a richiesta inviata correttamente. </p>';
					}
				}
				echo $messaggio;
			} 
		} else if (!isset($_POST['send_msg_request']) or !empty($error)) {
			if(isset($_GET['request_id'])) {
				$richiesta = Richieste::get_request($_GET['request_id'], "BeneID");
				$destinatario = $richiesta['Richiedente'];
				$legend = "Segnala disponibilit&agrave; a rispondere ad una richiesta";
				$titolo_risposta = 'Re: '.$richiesta['Titolo'];
				echo '<h2>'.$richiesta['Titolo'].'</h2>';
				echo '<p>'.$richiesta['Descrizione'].'</p>';
			}
			elseif(isset($_GET['offer_id'])) {
				$offerta = Offerte::get_offer($_GET['offer_id'], "BeneID");
				$destinatario = $offerta['Offerente'];
				$legend = "Segnala disponibilit&agrave; ad accettare una offerta";
				$titolo_risposta = 'Re: '.$offerta['Titolo'];
				echo '<h2>'.$offerta['Titolo'].'</h2>';
				echo '<img style="float:left; padding-right: 10px" src="'.$offerta['ImmaginePercorsoURL'].'" />';
				echo '<p style="clear:both">'.$offerta['Descrizione'].'</p>';
			} elseif(isset($_GET['offer_operation_id'])) { 
				$legend = "Rispondi";
				$operazione = Offer_Operations::get_offer_operation($_GET['offer_operation_id']);
				Offer_Operations::set_read_answer($_GET['offer_operation_id']);

				// stampa dell'offerta
				$offerta = Offerte::get_offer($operazione['BeneAssociato'], "BeneID");
				if(strpos($operazione['StatusOperationCreator'], 'active') !== false)
					echo '<h2>Proposta per '.$offerta['Titolo'].'</h2>';
				elseif(strpos($operazione['StatusOperationCreator'], 'disabled') !== false)
					echo '<h2>Proposta per '.$offerta['Titolo'].' rifiutata</h2>';
				else 
					echo '<h2>Vincitore della proposta per '.$offerta['Titolo'].'</h2>';
					
				echo '<img style="float:left; padding-right: 10px" src="'.$offerta['ImmaginePercorsoURL'].'" />';
				echo '<p style="clear:both">'.$offerta['Descrizione'].'</p>';

				// stampa del thread 
				$thread = Messages::get_thread($operazione['MessaggioAssociato']);
				$thread_head = $thread[0]['MsgID'];
				Messages::set_read_thread($thread_head, $_SESSION['Username']);
				$destinatario = $_SESSION['Username'] != $thread[0]['Mittente'] ? $thread[0]['Mittente'] : 
					$thread[0]['Destinatario'];
				$utente_mittente = Utente::get_user_data($thread[0]['Mittente']);
				$utente_destinatario = Utente::get_user_data($thread[0]['Destinatario']);
				foreach($thread as $post) { // polish and save style settings onto style sheet todo
					echo '<div style="margin-bottom:30px;">';
					echo '<img src="data/upload/avatars/small_avatars/small_' 
							. (strcasecmp($post['Mittente'], $utente_mittente['NomeUtente']) == 0 ? $utente_mittente['ImmaginePercorsoURL'] : $utente_destinatario['ImmaginePercorsoURL'] ) . '" style="float:left; margin-right: 10px; padding-right: 10px; padding-bottom: 10px;"/>';
					echo '<div class="inner-message" style="margin-left:65px; border-left: 2px dotted #999;"><p style="margin-top:5px;">'.$post['Testo'].'</p>';
					echo '<div class="didascalia">'.$post['Mittente']. ' scrisse in data ' . $post['DataInvio'] .'</div></div>';
					echo '</div>';
				}
				$titolo_risposta = 'Re: '.$thread[0]['Titolo'];
			
				// azioni possibili per l'offerta (tutto in AJAX:)
				if(strpos($operazione['StatusOperationCreator'], 'active') !== false 
					AND $thread['0']['Mittente'] != $_SESSION['Username']) { 
					echo '<p id="buttons"> 
						<input class="accept_operation" type="button" value="Accetta la proposta per l\'offerta" id="accept_offer-' . $operazione['OperazioneID'] . '" />
						<input class="remove" type="button" value="Rifiuta la proposta per l\'offerta" id="refuse_offer-' .
						$operazione['OperazioneID'] . '" />
					  </p>';
				}
			} elseif(isset($_GET['request_operation_id'])) { 
				$legend = "Rispondi";
				$operazione = Request_Operations::get_request_operation($_GET['request_operation_id']);
				Request_Operations::set_read_answer($_GET['request_operation_id']);

				// stampa dell'richiesta
				$richiesta = Richieste::get_request($operazione['BeneAssociato'], "BeneID");
				if(strpos($operazione['StatusOperationCreator'], 'active') !== false)
					echo '<h2>Richiesta per '.$richiesta['Titolo'].'</h2>';
				elseif(strpos($operazione['StatusOperationCreator'], 'disabled') !== false)
					echo '<h2>Richiesta per '.$richiesta['Titolo'].' rifiutata </h2>';
				else 
					echo '<h2>Vincitore della richiesta per '.$richiesta['Titolo'].'</h2>';
				echo '<img style="float:left; padding-right: 10px" src="'.$richiesta['ImmaginePercorsoURL'].'" />';
				echo '<p style="clear:both">'.$richiesta['Descrizione'].'</p>';

				// stampa del thread 
				$thread = Messages::get_thread($operazione['MessaggioAssociato']);
				$thread_head = $thread[0]['MsgID'];
				Messages::set_read_thread($thread_head, $_SESSION['Username']);
				// e se fosse il mittente a rispondere?? bella domanda
				$destinatario = $_SESSION['Username'] != $thread[0]['Mittente'] ? $thread[0]['Mittente'] : 
					$thread[0]['Destinatario'];
				$utente_mittente = Utente::get_user_data($thread[0]['Mittente']);
				$utente_destinatario = Utente::get_user_data($thread[0]['Destinatario']);
				foreach($thread as $post) { // polish and save style settings onto style sheet todo
					echo '<div style="margin-bottom:30px;">';
					echo '<img src="data/upload/avatars/small_avatars/small_' 
							. (strcasecmp($post['Mittente'], $utente_mittente['NomeUtente']) == 0 ? $utente_mittente['ImmaginePercorsoURL'] : $utente_destinatario['ImmaginePercorsoURL'] ) . '" style="float:left; margin-right: 10px; padding-right: 10px; padding-bottom: 10px;"/>';
					echo '<div class="inner-message" style="margin-left:65px; border-left: 2px dotted #999;"><p style="margin-top:5px;">'.$post['Testo'].'</p>';
					echo '<div class="didascalia">'.$post['Mittente']. ' scrisse in data ' . $post['DataInvio'] .'</div></div>';
					echo '</div>';
				}
				$titolo_risposta = 'Re: '.$thread[0]['Titolo'];
			
				// azioni possibili per la richiesta (tutto in AJAX:)
				if(strpos($operazione['StatusOperationCreator'], 'active') !== false 
					AND $thread['0']['Mittente'] != $_SESSION['Username']) { 
						echo '<p id="buttons"> 
						<input class="accept_operation" type="button" value="Accetta la proposta per la richiesta" id="accept_request-' . $operazione['OperazioneID'] . '" />
						<input class="remove" type="button" value="Rifiuta la proposta per la richiesta" id="refuse_request-' .	$operazione['OperazioneID'] . '" />
					  </p>';
				}
			} elseif(isset($_GET['msg_id'])) { // render funzione..
				$legend = "Rispondi a messaggio";
				$destinatario = $_GET['destinatario'];
				$thread_head = Messages::get_parent_id($_GET['msg_id']);
				$thread = Messages::get_thread($_GET['msg_id']);
				Messages::set_read_thread($thread_head, $_SESSION['Username']);
				$utente_mittente = Utente::get_user_data($thread[0]['Mittente']);
				$utente_destinatario = Utente::get_user_data($thread[0]['Destinatario']);
				echo '<h2>'.$thread[0]['Titolo'].'</h2>';
				foreach($thread as $post) { // polish and save style settings onto style sheet todo
					echo '<div style="margin-bottom:30px;">';
					echo '<img src="data/upload/avatars/small_avatars/small_' 
							. (strcasecmp($post['Mittente'], $utente_mittente['NomeUtente']) == 0 ? $utente_mittente['ImmaginePercorsoURL'] : $utente_destinatario['ImmaginePercorsoURL'] ) . '" style="float:left; margin-right: 10px; padding-right: 10px; padding-bottom: 10px;"/>';
					echo '<div class="inner-message" style="margin-left:65px; border-left: 2px dotted #999;"><p style="margin-top:5px;">'.$post['Testo'].'</p>';
					echo '<div class="didascalia">'.$post['Mittente']. ' scrisse in data ' . $post['DataInvio'] .'</div></div>';
					echo '</div>';
				}
				$titolo_risposta = 'Re: '.$thread[0]['Titolo'];
			}else {
				$legend = "Invia messaggio";
			}
			// se è presente view_msg... mostra l'intero thread. Se è presente un bene, stampa prima questo...
		?>
		<!-- check this piece of code -->
		<form id="send_msg" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?q=send_msg">
		<fieldset>
			<!-- 
				Mettere quantità ed errori todo.. 
				Inserire destinatario... tramite ajax autocompletamento!!
				mettere re: titolo in automatico!
				in una risposta, sempre lo stesso parentid!!!!
			-->
			<legend> <?php echo $legend; ?> </legend>
			<p><label>Destinatario:<span class="didascalia">Il destinatario del messaggio</span></label><input type="text" class="textbox required <?php echo (isset($destinatario)) ? "disabled" : ""; ?>" id="receiver" name="receiver" value="<?php echo (isset($destinatario)) ? $destinatario."\" readonly=\"readonly\"" : "\""; ?> /></p> <!-- "  TODO: remove me!! -->
			<p><label>Titolo messaggio:<span class="didascalia">Inserisci il titolo del messaggio</span></label><input type="text" class="textbox required" id="msg_title" name="msg_title" value="<?php echo (isset($titolo_risposta)) ? $titolo_risposta : ""; ?>" /></p>
			<p><label>Corpo:<span class="didascalia">Inserisci il testo del messaggio </span></label>
			<textarea rows="5" cols="58" name="msg_body" id="msg_body"></textarea>
			</p>
		</fieldset>
			<p><input type="submit" id="send_msg_request" name="send_msg_request" value="Conferma" /></p>
			<!-- controllo antispam -->
			<input type="text" class="textbox" id="cp_ans" name="cp_ans" value="" style="display:none;"/>
			<?php if(isset($_GET['msg_id']) 
					or isset($_GET['request_operation_id'])
					or isset($_GET['offer_operation_id'])) { ?>
			<input type="text" class="textbox" id="parent_message" name="parent_message" value="<?php echo $thread_head ?>" style="display:none;"/>
			<?php } 
				if(isset($_GET['offer_id'])) { 
					echo '<input type="text" class="textbox" id="offer_id" name="offer_id" value="'.$_GET['offer_id'].'" style="display:none;"/>';
				}
				if(isset($_GET['request_id'])) { 
					echo '<input type="text" class="textbox" id="request_id" name="request_id" value="'.$_GET['request_id'].'" style="display:none;"/>';
				}
			?>
		</form> 
		<?php } //mettere, se request_id, se offer_id. Input nascosto...
		break;

	case "richieste":
		?><h2>Le tue richieste</h2>
		<?php
		// prende ultime 5 richieste
		// get_richieste(5); 
		$array_pakkoso = Richieste::get_requests($_SESSION['Username'], "Richiedente", 0);
		// mettere bel codicillo per stamparlo?
		if(empty($array_pakkoso)) { 
			echo '<p>Non ci sono richieste</p>'; 
		} else {
			echo '<table class="tabella"><tr><th>Titolo</th><th>Data</th><th>Descrizione</th><th style="width:63px">Stato</th><th>Azioni</th></tr>';
			foreach($array_pakkoso as $richiesta) {
					$has_requests = Request_Operations::get_n_operations_by_request($richiesta['BeneID']);
					echo "<tr><td>".$richiesta['Titolo'] . 
					"</td><td>".date("d/m/y",strtotime($richiesta['Data']))."</td><td>" . 
					substr($richiesta['Descrizione'],0,100)."...</td><td>";
					if($richiesta['Disponibile']) { 
						echo 'Aperta';
						$title_a = "Ci sono risposte alla richiesta.";
					} else { 
						$title_a = "C'è un vincitore della richiesta.";
						echo 'Chiusa';
					}
					if($has_requests) {
						echo '<a class="number_answers" href="pannello_utente.php?q=view_request&request_id=' . 
						$richiesta['BeneID'].'" title="'.$title_a.'">'.$has_requests.'</a>';
					}
					echo "</td><td class=\"azioni\">"."<a href=\"pannello_utente.php?q=edit_request&request_id=" .
					$richiesta['BeneID']."\" title=\"Modifica richiesta\"><img src=\"images/edit.png\" /><span class=\"hide\">Modifica</span></a><a href=\"#\" class=\"remove\" id=\"request-" .
					$richiesta['BeneID']."\"title=\"Rimuovi offerta\"><img src=\"images/delete.png\" /><span class=\"hide\">Modifica</span></a>"."</td></tr>";
			}
			echo '</table>';
		}
		break;

	case "ans_richieste":
		?><h2>Le tue richieste</h2>
		<?php
		// prende ultime 5 richieste
		// get_richieste(5); 
		$array_pakkoso = Request_Operations::get_request_operations($_SESSION['Username']);
		// mettere bel codicillo per stamparlo?
		if(empty($array_pakkoso)) { 
			echo '<p>Non ci sono richieste</p>'; 
		} else {
			echo '<table class="tabella"><tr><th>Titolo</th><th>Data</th><th>Descrizione</th><th>Stato</th><th>Azioni</th></tr>';
			foreach($array_pakkoso as $richiesta) { ?>
					<tr>
						<td><?=$richiesta['Titolo']?></td>
						<td><?=date("d/m/y",strtotime($richiesta['Data']))?></td>
						<td><?=substr($richiesta['Testo'],0,100)?>..</td>
						<td><?php
							if(strpos($richiesta['StatusOperationCreator'], "disabled") !== false) 
								echo "Rifiutata";
							elseif(strpos($richiesta['StatusOperationCreator'], "winner") !== false)
								echo "Vinta";
							else
								echo "Aperta";
							?></td>
						<td class=\"azioni\"><a href="pannello_utente.php?q=view_msg&request_operation_id=<?=$richiesta['OperazioneID']?>" title="Visualizza richiesta">Visualizza</a></td></tr>
			<?php
			}
			echo '</table>';
		}
		break;

	case "view_request":
		// todo: vanno settati le richieste come lette, farne metodo
		$request = Richieste::get_request($_GET['request_id'], "BeneID");
		echo '<h2>Proposte per '.$request['Titolo'].'</h2>';
		echo '<p>'.$request['Descrizione'].'</p>';
		if($request['Disponibile'] == 1)
			echo '<h2> Risposte </h2>';
		else 
			echo '<h2> Risposta vincente </h2>';
		$array_operazioni = Request_Operations::get_operations_by_request($_GET['request_id']);
		Request_Operations::set_all_operations_read($_GET['request_id']);
		foreach($array_operazioni as $operazione) {
			$messaggio = Messages::get_message($operazione['MessaggioAssociato']);
			$utente = Utente::get_user_data($messaggio['Mittente']);
			echo '<div style="min-height: 80px">';
			echo '<img src="data/upload/avatars/small_avatars/small_' .
				  $utente['ImmaginePercorsoURL'] . '" style="float:left; margin-right: 10px; padding-right: 10px; padding-bottom: 10px;"/>';
			echo '<div style="float:right">
					<!-- da fare in ajax -->
					<form id="view_request_operation" method="get" action="pannello_utente.php" >
						<input type="hidden" name="q" value="view_msg">
						<input type="hidden" name="request_operation_id" value="' . $operazione['OperazioneID'] .'">
						<input type="submit" value="Rispondi"></input>
					</form>
				  </div>';
			echo '<p>' . $messaggio['Testo'] . '</p>';
			echo '<span class="didascalia">' . $messaggio['Mittente'] . ' scrisse in data ' . $messaggio['DataInvio'] . '</span>';
			echo '</div>';
		}
		break;
	case "add_request": ?> 
		<h2>Fai una tua richiesta</h2>
		<?php // request_name, categoria_richiesta, indirizzo_foto, text_request
		if(isset($_POST['submit_request'])) {
			$request_name = isset($_POST['request_name']) ? trim($_POST['request_name']) : '';
			$categoria_richiesta = isset($_POST['categoria_richiesta']) ? trim($_POST['categoria_richiesta']) : '';
			$text_request = isset($_POST['text_request']) ? trim($_POST['text_request']) : '';
			if(!empty($cp_ans)) $error["SpamDetected"] = "A bot may not injure (or simply annoy) a human being or, through inaction, allow a human being to come to harm";
			empty($request_name) ? $error["MissingRequestName"] = "Il titolo della richiesta è incompleto" : '';
			empty($categoria_richiesta) ? $error["MissingCategoria"] = "Selezionare una categoria" : ''; 
			empty($text_request) ? $error["MissingTextRequest"] = "Scrivere una descrizione della richiesta" : ''; 

			if(empty($error)) {
				echo Richieste::insert_request($request_name, $text_request, "", 1, $categoria_richiesta, $_SESSION['Username']);
			} 
		} else if (!isset($_POST['submit_request']) or !empty($error)) {  ?>
		<form id="insert_request" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?q=add_request">
		<fieldset>
			<!-- mettere quantità ed errori todo.. -->
			<legend> Inserisci richiesta </legend>
			<p><label>Nome richiesta:<span class="didascalia">Inserisci il nome della richiesta</span></label><input type="text" class="textbox required" id="request_name" name="request_name" value="" maxlength="40" /></p>
			<p><label>Categoria:<span class="didascalia">Seleziona la categoria </span></label>
				<!-- TODO: mettere seconda selezione.. -->
				<select name="categoria_richiesta" id="categoria_richiesta">
				<?php $categorie = Categorie::get_categories('');
					foreach($categorie as $categoria) {
						echo "<option>".$categoria['Titolo']."</option>\n";
						$sottocategorie = Categorie::get_categories($categoria['Titolo']);
						if(!empty($sottocategorie)) {
							foreach($sottocategorie as $sottocategoria) 
								echo "<option>".$sottocategoria['Titolo']."</option>\n";
						}
					}
				?>
				</select>
			</p>
			<!-- <p><label>Caricamento foto:<span class="didascalia">To do</span></label>Pakkoso</p> -->
			<p><label>Descrizione:<span class="didascalia">Inserisci una descrizione della tua richiesta</span></label>
			<textarea rows="5" cols="58" name="text_request" id="text_request"></textarea>
			</p>
		</fieldset>
			<p><input type="submit" id="submit_request" name="submit_request" value="Conferma" /></p>
			<!-- controllo antispam -->
			<input type="text" class="textbox" id="cp_ans" name="cp_ans" value="" style="display:none;"/>
		</form><?php 
		}
		break;
	case "edit_request":
		if(isset($_POST['submit_edit_request'])) {
			$request_name = isset($_POST['request_name']) ? trim($_POST['request_name']) : '';
			$categoria_richiesta = isset($_POST['categoria_richiesta']) ? trim($_POST['categoria_richiesta']) : '';
			$text_request = isset($_POST['text_request']) ? trim($_POST['text_request']) : '';
			$image_name = isset($_FILES['preview_request']['name']) ? trim($_FILES['preview_request']['name']) : '';

			// todo: trim nome immagine.. e farne funzione!!
			if(!empty($cp_ans)) $error["SpamDetected"] = "A bot may not injure (or simply annoy) a human being or, through inaction, allow a human being to come to harm";

			if(empty($request_name) and empty($categoria_richiesta) and empty($categoria_richiesta) 
				and empty($text_request) and empty($image_name)) {
				$error["MissingData"] = "Manca un dato da modificare";
			}

			// inserisci immagine richiesta e realizza anteprima (return url image)
			if(empty($error)) {
				if(!empty($request_name))
					if(Richieste::edit_request($_POST['request_id'], $request_name, "Titolo")) echo "Titolo aggiornato con successo!";
				
				if(!empty($categoria_richiesta)) 
					if(Richieste::edit_request($_POST['request_id'], $categoria_richiesta, "Category")) echo "Categoria aggiornata con successo!";

				
				if(!empty($text_request)) 
					if(Richieste::edit_request($_POST['request_id'], $text_request, "Descrizione")) echo "Descrizione aggiornata con successo!";
				
				// qui bisogna caricarla di nuovo... todo attenzione! cambia todos!
				if(!empty($image_name)) 
					echo Richieste::edit_request($_POST['request_id'], $image_name, "ImmaginePercorsoURL");
				
			}
		} else if (!isset($_POST['submit_edit_request']) or !empty($error)) {
			// ottieni tutti i dati, mettili di default, mettere controlli,
			// aggiungere richiesta di conferma...
			$request_to_edit = Richieste::get_request($_GET['request_id'], "BeneID");
			$request_category = Richieste::get_category_of_request($_GET['request_id']);
			?>
			<form id="edit_request_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?q=edit_request" enctype="multipart/form-data">
		<fieldset>
			<!-- mettere quantità ed errori todo.. 
				 con input hidden evitare di ricambiare sempre i valori vecchi?
			-->
			<legend> Modifica Richiesta </legend>
			<p><label>Nome richiesta:<span class="didascalia">Cambia il nome della richiesta</span></label><input type="text" class="textbox required" id="request_name" name="request_name" value="<?php echo $request_to_edit['Titolo'] ?>" maxlength="40" /></p>
			<p><label>Categoria:<span class="didascalia">Cambia la categoria </span></label>
				<!-- TODO: mettere seconda selezione.. -->
				<select name="categoria_richiesta" id="categoria_richiesta">
				<?php $categorie = Categorie::get_categories('');
					foreach($categorie as $categoria) {
						$is_selected = $categoria['Titolo'] == $request_category ? "selected" : "";
						echo "<option ".$is_selected.">".$categoria['Titolo']."</option>\n";
						$sottocategorie = Categorie::get_categories($categoria['Titolo']);
						if(!empty($sottocategorie)) {
							foreach($sottocategorie as $sottocategoria) {
								$is_selected = $sottocategoria['Titolo'] == $request_category ? "selected" : "";
								echo "<option ".$is_selected.">".$sottocategoria['Titolo']."</option>\n";
							}
						}
					}
				?>
				</select>
			</p>
			<p><label>Caricamento foto:<span class="didascalia">Cambia l'immagine</span></label><input type="file" name="preview_request" id="preview_request"></p>
			<p><label>Descrizione:<span class="didascalia">Cambia la descrizione della tua richiesta</span></label>
			<textarea rows="5" cols="58" name="text_request" id="text_request"><?php echo $request_to_edit['Descrizione']; ?></textarea>
			</p>
		</fieldset>
			<p><input type="submit" id="submit_edit_request" name="submit_edit_request" value="Conferma" /></p>
			<!-- controllo antispam -->
			<input type="text" class="textbox" id="cp_ans" name="cp_ans" value="" style="display:none;"/>
			<input type="text" class="textbox" id="request_id" name="request_id" value="<?php echo $_GET['request_id']; ?>" style="display:none;"/>
		</form>
		<?php 
		}
		break;
	case "offers":
 		?><h2>Le tue Offerte</h2><?php
		$array_offerte = Offerte::get_offers($_SESSION['Username'], "Offerente", 0);
		// mettere bel codicillo per stamparlo?
		if(empty($array_offerte)) { 
			echo '<p>Non ci sono offerte</p>'; 
		} else {
			echo '<table class="tabella"><tr><th>Titolo</th><th>Data</th><th>Descrizione</th><th style="width:63px">Stato</th><th>Azioni</th></tr>';
			foreach($array_offerte as $offerta) {
				$has_offers = Offer_Operations::get_n_operations_by_offer($offerta['BeneID']);
				echo "<tr><td>".$offerta['Titolo'] . 
				"</td><td>".date("d/m/y",strtotime($offerta['Data']))."</td><td>" . 
				substr($offerta['Descrizione'],0,100)."...</td><td>";
				if($offerta['Disponibile']) { 
					echo 'Aperta';
					$title_a = "Ci sono risposte alla offerta.";
				} else { 
					$title_a = "C'è un vincitore della offerta.";
					echo 'Chiusa';
				}
				if($has_offers) {
					echo '<a class="number_answers" href="pannello_utente.php?q=view_offer&offer_id=' . 
					$offerta['BeneID'].'" title="'.$title_a.'">'.$has_offers.'</a>';
				}
				echo "</td><td class=\"azioni\">"." <a href=\"pannello_utente.php?q=edit_offer&offer_id=".$offerta['BeneID']."\" title=\"Modifica offerta\"><img src=\"images/edit.png\" /><span class=\"hide\">Modifica</span></a><a href=\"#\" class=\"remove\" id=\"offer-" . 
				$offerta['BeneID']."\"title=\"Rimuovi offerta\"><img src=\"images/delete.png\" /><span class=\"hide\">Modifica</span></a>"."</td></tr>";
					// clean this piece of code todo!!
					if($has_requests) {
						echo ', <a href="pannello_utente.php?q=view_request&request_id=' . 
						$richiesta['BeneID'].'" title="Ci sono risposte alla richiesta"> *'.$has_requests.'</a>';
					}

			}
			echo '</table>';
		}
		break;
	case "view_offer":
		$offer = Offerte::get_offer($_GET['offer_id'], "BeneID");
		echo '<h2>Proposte per '.$offer['Titolo'].'</h2>';
		echo '<img style="float:left; padding-right: 10px" src="'.$offer['ImmaginePercorsoURL'].'" />';
		echo '<p>'.$offer['Descrizione'].'</p>';
		if($offer['Disponibile'] == 1)
			echo '<h2 style="clear:left"> Risposte </h2>';
		else
			echo '<h2 style="clear:left"> Risposta vincente </h2>';
		$array_operazioni = Offer_Operations::get_operations_by_offer($_GET['offer_id']);
		Offer_Operations::set_all_operations_read($_GET['offer_id']);
		foreach($array_operazioni as $operazione) {
			$messaggio = Messages::get_message($operazione['MessaggioAssociato']);
			$utente = Utente::get_user_data($messaggio['Mittente']);
			echo '<div style="min-height: 80px">';
			echo '<img src="data/upload/avatars/small_avatars/small_' .
				  $utente['ImmaginePercorsoURL'] . '" style="float:left; margin-right: 10px; padding-right: 10px; padding-bottom: 10px;"/>';

			echo '<div style="float:right">
					<!-- da fare in ajax -->
					<form id="view_offer_operation" method="get" action="pannello_utente.php" >
						<input type="hidden" name="q" value="view_msg">
						<input type="hidden" name="offer_operation_id" value="' . $operazione['OperazioneID'] .'">
						<input type="submit" value="Rispondi"></input>
					</form>
				  </div>';
			echo '<p>' . $messaggio['Testo'] . '</p>';
			echo '<span class="didascalia">' . $messaggio['Mittente'] . ' scrisse in data ' . $messaggio['DataInvio'] . '</span>';
			echo '</div>';
		}

		break;
	case "add_offer": 
		echo '<h2>Fai una tua offerta</h2>';
		// offer_name, categoria_offerta, indirizzo_foto, text_offer
		if(isset($_POST['submit_offer'])) {
			$offer_name = isset($_POST['offer_name']) ? trim($_POST['offer_name']) : '';
			$categoria_offerta = isset($_POST['categoria_offerta']) ? trim($_POST['categoria_offerta']) : '';
			$text_offer = isset($_POST['text_offer']) ? trim($_POST['text_offer']) : '';
			$image_name = isset($_FILES['preview_offer']['name']) ? trim($_FILES['preview_offer']['name']) : '';

			// todo: trim nome immagine.. e farne funzione!!
			if(!empty($cp_ans)) $error["SpamDetected"] = "A bot may not injure (or simply annoy) a human being or, through inaction, allow a human being to come to harm";
			empty($offer_name) ? $error["MissingOfferName"] = "Il titolo della offerta è incompleto" : '';
			empty($categoria_offerta) ? $error["MissingCategoria"] = "Selezionare una categoria" : ''; 
			empty($text_offer) ? $error["MissingTextOffer"] = "Scrivere una descrizione della offerta" : ''; 

			// inserisci immagine offerta e realizza anteprima (return url image)

			if(empty($error)) {
				if($image_name != '') {
					$stato_inserimento_immagine = Images::insert_image($_FILES['preview_offer']["tmp_name"], $image_name, $_FILES['preview_offer']["size"], $_FILES['preview_offer']["type"]);
					$image_url = $image_name;
				} // attento
				if($stato_inserimento_immagine == true) {
					echo Offerte::insert_offer($offer_name, $text_offer, $image_url, 1, $categoria_offerta, $_SESSION['Username']);
				} else {
					echo 'Errore nell\'inserimento dell\'immagine!!';
				}
			} 
		} else if (!isset($_POST['submit_offer']) or !empty($error)) {  ?>
		<form id="insert_offer" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?q=add_offer" enctype="multipart/form-data">
		<fieldset>
			<!-- mettere quantità ed errori todo.. -->
			<legend> Inserisci offerta </legend>
			<p><label>Nome offerta:<span class="didascalia">Inserisci il nome della offerta</span></label><input type="text" class="textbox required" id="offer_name" name="offer_name" value="" maxlength="40" /></p>
			<p><label>Categoria:<span class="didascalia">Seleziona la categoria </span></label>
				<!-- TODO: mettere seconda selezione.. -->
				<select name="categoria_offerta" id="categoria_offerta">
				<?php $categorie = Categorie::get_categories('');
					foreach($categorie as $categoria) {
						echo "<option>".$categoria['Titolo']."</option>\n";
						$sottocategorie = Categorie::get_categories($categoria['Titolo']);
						if(!empty($sottocategorie)) {
							foreach($sottocategorie as $sottocategoria) 
								echo "<option>".$sottocategoria['Titolo']."</option>\n";
						}
					}
				?>
				</select>
			</p>
			<p><label>Caricamento foto:<span class="didascalia">Carica un'immagine</span></label><input type="file" name="preview_offer" id="preview_offer"></p>
			<p><label>Descrizione:<span class="didascalia">Inserisci una descrizione della tua offerta</span></label>
			<textarea rows="5" cols="58" name="text_offer" id="text_offer"></textarea>
			</p>
		</fieldset>
			<p><input type="submit" id="submit_offer" name="submit_offer" value="Conferma" /></p>
			<!-- controllo antispam -->
			<input type="text" class="textbox" id="cp_ans" name="cp_ans" value="" style="display:none;"/>
		</form><?php 
		}
		break;
	case "ans_offers":
	?><h2>Le offerte che hai richiesto </h2>
		<?php
		$array_pakkoso = Offer_Operations::get_offers_operations($_SESSION['Username']);
		// mettere bel codicillo per stamparlo?
		if(empty($array_pakkoso)) { 
			echo '<p>Non ci sono offerte</p>'; 
		} else {
			echo '<table class="tabella"><tr><th>Titolo</th><th>Data</th><th>Descrizione</th><th>Stato</th><th>Azioni</th></tr>';
			foreach($array_pakkoso as $offerta) { // careful check
					echo "<tr><td>".$offerta['Titolo']."</td><td>".date("d/m/y",strtotime($offerta['Data']))."</td><td>".substr($offerta['Testo'],0,100)."...</td><td>";
					if(strpos($offerta['StatusOperationCreator'], "disabled") !== false) 
						echo "Rifiutata";
					elseif(strpos($offerta['StatusOperationCreator'], "winner") !== false)
						echo "Vinta";
					else
						echo "Aperta";
					echo "</td><td class=\"azioni\">"."<a href=\"pannello_utente.php?q=view_msg&offer_operation_id=".$offerta['OperazioneID']."\" title=\"Visualizza proposta\">Visualizza</td></tr>";
			}
			echo '</table>';
		}

		break;
	case "edit_offer":
		if(isset($_POST['submit_edit_offer'])) {
			$offer_name = isset($_POST['offer_name']) ? trim($_POST['offer_name']) : '';
			$categoria_offerta = isset($_POST['categoria_offerta']) ? trim($_POST['categoria_offerta']) : '';
			$text_offer = isset($_POST['text_offer']) ? trim($_POST['text_offer']) : '';
			$image_name = isset($_FILES['preview_offer']['name']) ? trim($_FILES['preview_offer']['name']) : '';

			// todo: trim nome immagine.. e farne funzione!!
			if(!empty($cp_ans)) $error["SpamDetected"] = "A bot may not injure (or simply annoy) a human being or, through inaction, allow a human being to come to harm";

			if(empty($offer_name) and empty($categoria_offerta) and empty($categoria_offerta) 
				and empty($text_offer) and empty($image_name)) {
				$error["MissingData"] = "Manca un dato da modificare";
			}

			// inserisci immagine offerta e realizza anteprima (return url image)
			if(empty($error)) {
				if(!empty($offer_name)) 
					echo Offerte::edit_offer($_POST['offer_id'], $offer_name, "Titolo");
				
				if(!empty($categoria_offerta)) 
					echo Offerte::edit_offer($_POST['offer_id'], $categoria_offerta, "Category");
				
				if(!empty($text_offer)) 
					echo Offerte::edit_offer($_POST['offer_id'], $text_offer, "Descrizione");
				
				// todo: testa funzione
				if(!empty($image_name)) {
					// carica nuova immagine
					$stato_inserimento_immagine = Images::insert_image($_FILES['preview_offer']["tmp_name"], $image_name, $_FILES['preview_offer']["size"], $_FILES['preview_offer']["type"]);
					$new_image_url = $image_name;
					// prendi campo vecchio
					$old_image_url = Offerte::get_property_by_item("ImmaginePercorsoURL", $_POST['offer_id'], "BeneID");
					// cambia campo nell' offerta
					echo Offerte::edit_offer($_POST['offer_id'], $new_image_url, "ImmaginePercorsoURL");
					// cancella immagine
					Images::delete_image($old_image_url);
				}
				
			}
		} else if (!isset($_POST['submit_edit_offer']) or !empty($error)) {
			// ottieni tutti i dati, mettili di default, mettere controlli,
			// aggiungere richiesta di conferma...
			$offer_to_edit = Offerte::get_offer($_GET['offer_id'], "BeneID");
			$offer_category = Offerte::get_category_of_offer($_GET['offer_id']);
			?>
			<form id="edit_offer_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?q=edit_offer" enctype="multipart/form-data">
		<fieldset>
			<!-- mettere quantità ed errori todo.. -->
			<legend> Modifica offerta <?php echo $offer_category ?></legend>
			<p><label>Nome offerta:<span class="didascalia">Cambia il nome della offerta</span></label><input type="text" class="textbox required" id="offer_name" name="offer_name" value="<?php echo $offer_to_edit['Titolo'] ?>" maxlength="40" /></p>
			<p><label>Categoria:<span class="didascalia">Cambia la categoria </span></label>
				<!-- TODO: mettere seconda selezione.. -->
				<select name="categoria_offerta" id="categoria_offerta">
				<?php $categorie = Categorie::get_categories('');
					foreach($categorie as $categoria) {
						$is_selected = $categoria['Titolo'] == $offer_category ? "selected" : "";
						echo "<option ".$is_selected.">".$categoria['Titolo']."</option>\n";
						$sottocategorie = Categorie::get_categories($categoria['Titolo']);
						if(!empty($sottocategorie)) {
							foreach($sottocategorie as $sottocategoria) {
								$is_selected = $sottocategoria['Titolo'] == $offer_category ? "selected" : "";
								echo "<option ".$is_selected.">".$sottocategoria['Titolo']."</option>\n";
							}
						}
					}
				?>
				</select>
			</p>
			<p><label>Caricamento foto:<span class="didascalia">Cambia l'immagine</span></label><input type="file" name="preview_offer" id="preview_offer"></p>
			<p><label>Descrizione:<span class="didascalia">Cambia la descrizione della tua offerta</span></label>
			<textarea rows="5" cols="58" name="text_offer" id="text_offer"><?php echo $offer_to_edit['Descrizione']; ?></textarea>
			</p>
		</fieldset>
			<p><input type="submit" id="submit_edit_offer" name="submit_edit_offer" value="Conferma" /></p>
			<!-- controllo antispam -->
			<input type="text" class="textbox" id="cp_ans" name="cp_ans" value="" style="display:none;"/>
			<input type="text" class="textbox" id="offer_id" name="offer_id" value="<?php echo $_GET['offer_id']; ?>" style="display:none;"/>
		</form>
			<?php
			}
			break;
		case "admin_users":
			// controllo admin...
			echo User_View::get_users_table();
			break;
		case "admin_categories":
			$categorie = Categorie::get_categories('');
			echo "<h2> Categorie </h2>";
			echo "<table class=\"tabella\" id=\"table_categories\" style=\"margin-bottom:10px\"><tbody>";
			echo '<tr><th>Nome categoria</th><th style="width:140px;">Genitore</th><th style="width:50px">Azioni</th></tr>';
			if(empty($categorie)) {
				?><tr id="no_category"><td colspan="3">Non ci sono categorie.</td></tr><?php
			} else {
				foreach($categorie as $categoria) {
					empty($categoria['ParentID']) ? $parent_cell = '-' : $parent_cell = $categoria['ParentID'];
					echo "\t\t\t\t\t\t<tr>
							<td> {$categoria['Titolo']} </td>
							<td> $parent_cell </td>
							<td><a href=\"#\" class=\"remove_category\" id=\"category-{$categoria['Titolo']}\" title=\"Rimuovi categoria\"><img src=\"images/delete.png\" /></td>
						</tr>";
					$sottocategorie = Categorie::get_categories($categoria['Titolo']);
					if(!empty($sottocategorie)) {
						foreach($sottocategorie as $sottocategoria) 
							echo "\t\t\t\t\t\t<tr>
									<td> {$sottocategoria['Titolo']} </td>
									<td> {$categoria['Titolo']} </td>
									<td><a href=\"#\" class=\"remove_category\" id=\"category-{$categoria['Titolo']}\" title=\"Rimuovi categoria\"><img src=\"images/delete.png\" /></td>
								</tr>";
					}
				}
			}
			// todo: stampare le categorie... rendere possibile il cambio del genitore.
			echo "</tbody></table>";
			?>
			<!-- mettere quantità ed errori todo.. -->
			<fieldset>
			<legend>Inserisci nuova categoria</legend>
			<button style="float:right;" id="add_category" name="add_category" value="" >Aggiungi</button>
			<label>Nome:</label><input style="margin: 0 3px" type="text" id="category_name" name="category_name" value="" />
			<p style="padding-top:8px"><label>Categoria padre:</label>
			<select name="category_parent" id="category_parent">
			<?php 
				echo "<option selected> Nessuno </option>\n";
				foreach($categorie as $categoria) 
					echo "<option>".$categoria['Titolo']."</option>\n";
			?>
			</select>
			</p>
			</fieldset>
			<input type="button" id="save_changes" name="Salva" value="Salva modifiche" />
			<?php
			break;
		case "admin_offers":
		?><h2>Le Offerte nel sito</h2><?php
		$array_offerte = Offerte::get_offers('', '', 0);
		// mettere bel codicillo per stamparlo?
		if(empty($array_offerte)) { 
			echo '<p>Non ci sono offerte</p>'; 
		} else {
			echo '<table class="tabella"><tr><th>Titolo</th><th>Data</th><th>Descrizione</th><th>Stato</th><th>Azioni</th></tr>';
			foreach($array_offerte as $offerta) {
				echo "<tr><td>".$offerta['Titolo'] . 
				"</td><td>".date("d/m/y",strtotime($offerta['Data']))."</td><td>" . 
				substr($offerta['Descrizione'],0,100)."...</td><td>" . 
					($offerta['Disponibile'] ? 'Aperta' : "Chiusa") .
				"</td><td class=\"azioni\">"." <a href=\"pannello_utente.php?q=edit_offer&offer_id=".$offerta['BeneID']."\" title=\"Modifica offerta\"><img src=\"images/edit.png\" /><span class=\"hide\">Modifica</span></a><a href=\"#\" class=\"remove\" id=\"offer-" . 
				$offerta['BeneID']."\"title=\"Rimuovi offerta\"><img src=\"images/delete.png\" /><span class=\"hide\">Modifica</span></a>"."</td></tr>";
			}
			echo '</table>';
		}
		break;

		case "admin_requests":
			?><h2>Le richieste nel sito</h2>
			<?php
			// prende ultime 5 richieste
			// get_richieste(5); 
			$array_pakkoso = Richieste::get_requests('', '', 0);
			if(empty($array_pakkoso)) { 
				echo '<p>Non ci sono richieste</p>'; 
			} else {
				echo '<table class="tabella"><tr><th>Titolo</th><th>Data</th><th>Descrizione</th><th>Stato</th><th>Azioni</th></tr>';
				foreach($array_pakkoso as $richiesta) { // careful check
					if($richiesta['Disponibile']) { 
						$stato = 'Aperta';
					} else { 
						$stato = 'Chiusa';
					}

					echo "<tr><td>".$richiesta['Titolo']."</td><td>".date("d/m/y",strtotime($richiesta['Data']))."</td><td>".substr($richiesta['Descrizione'],0,100)."...</td><td> $stato </td><td class=\"azioni\">"."<a href=\"pannello_utente.php?q=edit_request&request_id=".$richiesta['BeneAssociato']."\" title=\"Modifica richiesta\"><img src=\"images/edit.png\" /><span class=\"hide\">Modifica</span></a><a href=\"#\" class=\"remove\" id=\"request-".$richiesta['BeneID']."\"title=\"Rimuovi offerta\"><img src=\"images/delete.png\" /><span class=\"hide\">Modifica</span></a>"."</td></tr>";
				}
				echo '</table>';
			}
			break;

		case "disable_user":
			echo '<h2> Operazione di disabilitazione utente in corso </h2>';
			if(isset($_GET['username'])) {
				if(Utente::disable_user($_GET['username'])) {
					echo "<p>Utente disabilitato con successo\n<p>Redirezione alla tabella utenti in corso...</p></p>";
					header('Refresh: 5; URL=pannello_utente.php?q=admin_users');
				} else {
					echo "<p> Errore nella disabilitazione dell'utente </p>";
				}
			} else {
				echo "<p> Errore: non è presente il nome dell'utente </p>";
			}
			break;
		case "enable_user":
			echo '<h2> Operazione di abilitazione utente in corso </h2>';
			if(isset($_GET['username'])) {
				if(Utente::enable_user($_GET['username'])) {
					echo "<p>Utente abilitato con successo</p>\n<p>Redirezione alla tabella utenti in corso...</p>";
					header('Refresh: 5; URL=pannello_utente.php?q=admin_users');
				} else {
					echo "<p> Errore nell'abilitazione dell'utente </p>";
				}
			} else {
				echo "<p> Errore: non è presente il nome dell'utente </p>";
			}
			break;

		case "edit_user":
			if(isset($_POST['submit_edit_user'])) {
				$error = array();
				$name = isset($_POST['name']) ? trim($_POST['name']) : '';
				$surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
				$datanascita = isset($_POST['datanascita']) ? trim($_POST['datanascita']) : '';
				$indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
				$city = isset($_POST['city']) ? trim($_POST['city']) : '';
				$nazione = isset($_POST['nazione']) ? trim($_POST['nazione']) : '';
				$cap = isset($_POST['cap']) ? trim($_POST['cap']) : '';
				$email = $_POST['email'] ? trim($_POST['email']) : '';
				$confirm_email = $_POST['confirm_email'] ? trim($_POST['confirm_email']) : '';
				$password = $_POST['password'] ? trim($_POST['password']) : '';
				$password_confirm = $_POST['password_confirm'] ? trim($_POST['password_confirm']) : '';
				$cp_ans = $_POST['cp_ans'] ? trim($_POST['cp_ans']) : '';
				$image_name = isset($_FILES['avatar_user']['name']) ? 
								trim($_FILES['avatar_user']['name']) : '';

				if(strlen($datanascita) == 10 and substr_count($datanascita,"/") == 2) {
					list($giorno, $mese, $anno) = split('/', $datanascita);
					$datanascita = $anno . "-" . $mese . "-" . $giorno;
					if(!checkdate($mese, $giorno, $anno)) 
						$error["InvalidData"] = "La data non è valida";
				} else $error["InvalidData"] = "La data non è valida";

				// todo: trim nome immagine.. e farne funzione!!
				if(!empty($cp_ans)) $error["SpamDetected"] = "A bot may not injure (or simply annoy) a human being or, through inaction, allow a human being to come to harm";

				if(empty($name) and empty($surname) and empty($datanascita) 
					and empty($indirizzo) and empty($city) and empty($nazione) and empty($cap)
					and empty($email) and empty($password) and empty($image_name)) {
					$error["MissingData"] = "Manca un dato da modificare";
				}

				//  da ricontrollare tutto, mettere conferme... se non identici
				$user_data = Utente::get_user_data($_SESSION['Username']);
				if(empty($error)) {
					$edit = false;
					if($user_data['Nome'] != $name) {
						if(Utente::edit_user_field($_SESSION['Username'], $name, "Nome"))
							echo "Nome aggiornato con successo </br>";
						$edit = true;
					}
					if($user_data['Cognome'] != $surname) {
						if(Utente::edit_user_field($_SESSION['Username'], $surname, "Cognome"))
							echo "Cognome aggiornato con successo </br>";
						$edit = true;
					}
					if(date("Y-m-d", strtotime($user_data['DataNascita'])) != $datanascita) {
						if(Utente::edit_user_field($_SESSION['Username'], $datanascita, "DataNascita"))
							echo "Data di nascita aggiornato con successo </br>";
						$edit = true;
					}
					if($user_data['Indirizzo'] != $indirizzo) {
						if(Utente::edit_user_field($_SESSION['Username'], $indirizzo, "Indirizzo"))
							echo "Indirizzo aggiornato con successo </br>";
						$edit = true;
					}
					if($user_data['Citta'] != $city) {
						if(Utente::edit_user_field($_SESSION['Username'], $city, "Citta"))
							echo "Città aggiornata con successo </br>";
						$edit = true;
					}
					if($user_data['Nazione'] != $nazione) {
						if(Utente::edit_user_field($_SESSION['Username'], $nazione, "Nazione"))
							echo "Nazione aggiornata con successo </br>";
						$edit = true;
					}
					if($user_data['CAP'] != $cap) {
						if(Utente::edit_user_field($_SESSION['Username'], $cap, "CAP"))
							echo "CAP aggiornato con successo </br>";
						$edit = true;
					}
					if($user_data['IndirizzoPostaElettronica'] != $email) {
						if(Utente::edit_user_field($_SESSION['Username'], $email, "IndirizzoPostaElettronica"))
							echo "Email aggiornata con successo </br>";
						$edit = true;
					}
					if(!empty($image_name)) {
						// carica nuova immagine
						$stato_inserimento_immagine = Images::insert_avatar($_FILES['avatar_user']["tmp_name"], $image_name, $_FILES['avatar_user']["size"], $_FILES['avatar_user']["type"]);
						$new_image_url = $image_name;
						// prendi campo vecchio
						$old_image_url = $user_data['ImmaginePercorsoURL'];
						// tocode : Images::delete_avatar($old_image_url);

						// gestione inserimento immagine
						if(Utente::edit_user_field($_SESSION['Username'], $image_name, "ImmaginePercorsoURL"))
							echo "Immagine aggiornato con successo </br>";
						$edit = true;
					}

					if($edit == false) 
						echo "Non è stata effettuata alcuna modifica";
				}
			} elseif (!isset($_POST['submit_edit_user']) or !empty($error)) {
			// ottieni tutti i dati, mettili di default, mettere controlli,
			// aggiungere richiesta di conferma...
			$user_data = Utente::get_user_data($_SESSION['Username']);
			if(!empty($error)) var_dump($error);
			?>
			<h2> Cambia i tuoi dati </h2>
			<form id="edit_user_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?q=edit_user" enctype="multipart/form-data">
		<fieldset>
			<!-- mettere quantità ed errori todo.. 
				 con input hidden evitare di ricambiare sempre i valori vecchi?
			-->
			<legend> Modifica Utente </legend>

			<p><label>Nome:<span class="didascalia">Cambia il tuo nome</span></label><input type="text" class="textbox required" id="name" name="name" value="<?=$user_data['Nome']?>" /> 
			<br/></p>
			<p><label>Cognome:<span class="didascalia">Cambia il tuo cognome</span></label><input type="text" class="textbox required" id="surname" name="surname" value="<?=$user_data['Cognome']?>" /><br/></p>
			<p><label>Data di nascita:<span class="didascalia">La tua data di nascita (gg/mm/aaaa)</span></label><input type="text" class="textbox required date" id="datanascita" name="datanascita" value="<?=date("d/m/Y",strtotime($user_data['DataNascita']))?>" /><br/></p>
			<p><label>Indirizzo:<span class="didascalia">Cambia il tuo indirizzo</span></label><input type="text" class="textbox required" id="indirizzo" name="indirizzo" value="<?=$user_data['Indirizzo']?>" /><br/></p>
			<p><label>Città:<span class="didascalia">Cambia il nome della tua città</span></label><input type="text" class="textbox required" id="city" name="city" value="<?=$user_data['Citta']?>" /><br/></p>
			<p><label>Nazione:<span class="didascalia">Cambia la tua nazione</span></label><input type="text" class="textbox required" id="nazione" name="nazione" value="<?=$user_data['Nazione']?>" /><br/></p>
			<p><label>CAP:<span class="didascalia">Cambia il CAP</span></label><input type="text" class="textbox required" id="cap" name="cap" value="<?=$user_data['CAP']?>" /><br/></p>
			<p><label>E-mail:<span class="didascalia">Cambia la tua e-mail</span></label><input type="text" class="textbox required email" id="email" name="email" value="<?=$user_data['IndirizzoPostaElettronica']?>" /><br/></p>
			<p><label>Conferma E-mail:<span class="didascalia">Riscrivila per sicurezza</span></label><input type="text" class="textbox required email" id="confirm_email" name="confirm_email" value="" /><br/></p>
			<!--
			<p><label>Scelta Password:<span class="didascalia">Cambia la password (a-z, 0-9, simboli)</span></label><input type="password" class="textbox required" id="password" name="password" value="" /><br/></p>
			<p><label>Ripeti Password:<span class="didascalia">Riscrivila per sicurezza</span></label><input type="password" class="textbox required" id="password_confirm" name="password_confirm" value="" /><br/></p>
			-->
			<p><label>Caricamento foto:<span class="didascalia">Cambia l'immagine di profilo</span></label><input type="file" name="avatar_user" id="avatar_user"></p>
		</fieldset>
			<p><input type="submit" id="submit_edit_user" name="submit_edit_user" value="Conferma" /></p>
			<!-- controllo antispam -->
			<input type="text" class="textbox" id="cp_ans" name="cp_ans" value="" style="display:none;"/>
		</form>
		<?php
			}
			break;

		case "user_info":
		default:
		$dati_utente = Utente::get_user_data($_SESSION['Username']);

		?>
		<h2> Benvenuto <?php echo $dati_utente['NomeUtente']; ?> </h2> <?php
		echo "<img style=\"float:left; padding-right:10px; padding-bottom:10px;\" src=\"data/upload/avatars/medium_avatars/medium_".$dati_utente['ImmaginePercorsoURL']."\" />";
		echo "Il mio nome: ".$dati_utente['Nome']."<br/>";
 		echo "Il mio cognome: ".$dati_utente['Cognome']."<br/>";
 		echo "Il mio nome utente: ".$dati_utente['NomeUtente']."<br/>";
		echo "La mia data di nascita: ".$dati_utente['DataNascita']."<br/>";
		echo "Il mio indirizzo: ".$dati_utente['Indirizzo']."<br/>";
		echo "La mia città: ".$dati_utente['Citta']."<br/>";
		echo "La mia nazione: ".$dati_utente['Nazione']."<br/>";
		echo "Il mio CAP: ".$dati_utente['CAP']."<br/>";
		echo "La mia e-mail: ".$dati_utente['IndirizzoPostaElettronica']."<br/>";
		echo "La data della mia registrazione: ".$dati_utente['DataRegistrazione']."<br/>";
		?> <h3 style="clear:left">  Statistiche </h3> <?php
		$array_statistiche = Utente::get_statistics($_SESSION['Username']);
		echo 'Numero di accessi: '.$array_statistiche['numeroAccessi'].'</br>';
		echo 'Numero di richieste: '.$array_statistiche['numeroRichieste'].'</br>';
		echo 'Numero di offerte: '.$array_statistiche['numeroOfferte'].'</br>';
		echo 'Numero di messaggi: '.$array_statistiche['numeroMessaggi'].'</br>';
}

include('include/templates/footer.inc.php'); ?>

<script type="text/javascript">
jQuery(document).ready(function(){
	$("#menu ul").hide();
	$("#menu > li > div").click(function(){
	    if(false == $(this).next().is(':visible')) {
	        $('#menu ul').slideUp(300);
	    }
	    $(this).next().slideToggle(300);
	});

	$('#menu ul:eq(<?php 
		switch($_GET['q']) {
			case "msgbox":
			case "sentbox":
			case "view_msg":
			case "send_msg":
				echo "1";
				break;
			case "richieste":
			case "add_request":
			case "ans_richieste":
			case "view_request":
				echo "2";
				break;
			case "offers":
			case "add_offer":
			case "ans_offers":
			case "view_offer":
				echo "3";
				break;
			case "admin_users":
			case "admin_offers":
			case "admin_requests":
			case "admin_categories":
				echo "4";
				break;
			case "user_info":
			default:
				echo "0"; 
		}?>)').show();

	$('.remove').click(function(e) { // test di regressione su offerte e richieste
   		e.preventDefault();
		var item_to_remove = $(this).attr('id').split('-');
		if(item_to_remove[0] == "request" || item_to_remove[0] == 'offer' || item_to_remove[0] == "msg" ) {
			var elementContainer = $(this).closest("tr");
		} 
    	$.ajax({
	      type: 'post',
    	  url: 'controller.php',
	      data: { 'delete_type': item_to_remove[0], 
				  'delete': item_to_remove[1],
				  'Username': "<?=$_SESSION['Username']?>"
				},
		  cache: false,
    	  beforeSend: function() { // aggiungere feedback per button refuse operation
			if(item_to_remove[0] == "request" || item_to_remove[0] == 'offer'|| item_to_remove[0] == "msg" ) {
          		elementContainer.animate({'backgroundColor':'#fb6c6c'}, 300);
			} 
	      },
    	  success: function(pakkoso) {// aggiungere feedback per button refuse operation
			if(item_to_remove[0] == "request" || item_to_remove[0] == 'offer' || item_to_remove[0] == "msg" ) {
        		elementContainer.slideUp(300,function() {
		          elementContainer.remove();
    		    });
			}
			if(item_to_remove[0] == "refuse_request" || item_to_remove[0] == "refuse_offer") {
				if(pakkoso == 1) {
					alert("Rifiuto avvenuto con successo");
					$('#buttons').hide();
				} 
			}
			if(item_to_remove[0] == "msg") {
				if(pakkoso == 1) {
					alert("Messaggio cancellato con successo");
				}
				$('#buttons').hide();
			}
		  }
    	});
	  });

	/* Per la pagina admin_categories */

	$('#add_category').click(function(e) {
   		e.preventDefault();
		var nomeCategoria = $('#category_name').val();
		var nomePadre = $('#category_parent:first').val();
		var stringaTesto = '<tr class="new_row"><td>' + nomeCategoria +
							'</td><td>' + nomePadre + '</td> <td> <a href="#" class="remove_category" id="category-' + nomeCategoria + '" title="Rimuovi categoria"><img src="images/delete.png" /></td></tr>';

		if ($('#no_category').length) {
			$('#no_category').remove()
		}

		// se è una sottocategoria dovrebbe andare ad inserirsi sotto il padre...
		// non dovrebbe essere possibile inserire valori già inseriti.
		// fare nella prossima versione todo.

		$('#table_categories > tbody:last').append(stringaTesto);
        $('#table_categories tr:last').animate({'backgroundColor':'#70d44d'}, 300);
        $('#table_categories tr:last').animate({'backgroundColor':'#fff'}, 500);

		if(nomePadre == 'Nessuno') {
			$('#category_parent').append('<option>'+nomeCategoria+'</option>');
		}
	});

	$('.remove_category').live('click', function(e) {
   		e.preventDefault();
		$(this).closest('tr').addClass("remove_confirmed");
       	$(this).closest('tr').animate({'backgroundColor':'#fb6c6c'}, 300);
		$(this).closest('tr').hide(300);
		// aggiungere animazione...
	});

	/* * SAVE NEXT TO DO * * * * * * /
	 *	obbliga un refresh della pagina
	 * * * * * * * * * * * * * * * */

	$('#save_changes').click(function(e) {
		e.preventDefault();
		var array_changes = new Array();
		
		$('#table_categories > tbody').children().each(function() {
			if($(this).hasClass('new_row') && !$(this).hasClass('remove_confirmed')) {
				array_changes.push(new Array("add_category", 
									$(this).children().eq(0).html(), $(this).children().eq(1).html()));
			} 
			if(!$(this).hasClass('new_row') && $(this).hasClass('remove_confirmed')) {
				array_changes.push(new Array("remove_category", 
									$(this).children().eq(0).html()));
			} 
		
		});
		
		$.ajax({
	      type: 'post',
    	  url: 'controller.php',
	      data: { 'operation_type': "categories_changes", 'array_changes': $.toJSON(array_changes)},
		  cache: false,
    	  beforeSend: function() { // aggiungere feedback 
		  },
    	  success: function(pakkoso) {// aggiungere feedback 
				alert(pakkoso);
		  }
    	});

		// invia tutto
		
	});

	/* fine codice per la pagina admin_categories */

	$('.accept_operation').click(function(e) { // test di regressione su offerte e richieste
   		e.preventDefault();
		var operation_to_accept = $(this).attr('id').split('-');

    	$.ajax({
	      type: 'post',
    	  url: 'controller.php',
	      data: { 'operation_type': operation_to_accept[0], 'operation_id': operation_to_accept[1] },
		  cache: false,
    	  success: function(pakkoso) {// aggiungere feedback 
			if(operation_to_accept[0] == "accept_request" || operation_to_accept[0] == "accept_offer") {
				if(pakkoso == 1) {
					alert("Accettazione avvenuto con successo");
					$('#buttons').hide();
				}
			}
		  },
		  error: function() {
				alert("Error!");
		  }
    	});
	  });

});
</script>
