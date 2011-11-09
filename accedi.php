<?php

/**
 * @file accedi.php
 * @brief Schermata di login
 * @author  Francesco Lorenzon <grepcesco@gmail.com>
 * 
 * Tramite questa pagina è possibile accedere e autenticarsi al sito.
 *
 */

require('include/library/database.php');
require('include/library/utenti.php');
require('include/library/messaggi.php');
require('include/library/operations.php');
require('include/library/sec_login.php');
include('include/templates/header.inc.php');

if(Utente::is_user_logged_in()) header('location:index.php');

?>
<div id="main_content_without_sidebar">
<div id="register_fieldset"> 
<?php
if(isset($_POST['submit_login'])) {
	// TODO: va aumentato il campo numero accessi
	$username = isset($_POST['username']) ? trim($_POST['username']) : '';
	$password = isset($_POST['password']) ? trim($_POST['password']) : '';
	if(!empty($cp_ans)) $error["SpamDetected"] = "A bot may not injure (or simply annoy) a human being or, through inaction, allow a human being to come to harm";
	empty($username) ? $error["MissingUsername"] = "Il campo nome utente è incompleto" : '';
	empty($password) ? $error["MissingPassword"] = "Il campo password è incompleto" : ''; 

	$db_handle = new Database();
	try {
		$db_handle->connect();
		if(empty($error)) {
			$check_user_query = "SELECT * FROM Utenti WHERE NomeUtente = '". $username ."'";
			$risultato_utente = $db_handle->query($check_user_query);
			$utente = mysql_fetch_array($risultato_utente);
			if($utente['NomeUtente'] == $username) {
				// cambiare hash password todo...
				$db_password = substr($utente['PasswordSHA'], 0, -4); 
				if($db_password === Utente::hash_password($password)) {
					// password corretta, può entrare
					$_SESSION['Username']= $username;
					//mettere! $_SESSION['Usergroup']=$rslt_login->Fields('group');
					$update_login_query = "UPDATE Utenti SET LastLoginDate = NOW() WHERE Nome = '$username'";
					$increment_n_login = "UPDATE Statistiche SET numeroAccessi = numeroAccessi + 1 WHERE Utente = '$username';";
					$db_handle->query($update_login_query);
					$db_handle->query($increment_n_login);
					header('location:index.php');
				} else {
					$error['WrongPassword'] = "La password è errata";
				}
			} else $error['WrongUsername'] = "Non esiste un nome utente con questo nome";
		}
	} catch(Exception $e) {
		echo $e->getMessage(), "\n"; 
	}
}  
if(!isset($_POST['submit_login']) or !empty($error)) { ?>
	<form id="register_form" method="post" action="accedi.php">
	<fieldset>
		<legend> Accedi al sito </legend>
			<!-- ajax per controllo email e nome utente?? -->
			<p><label>Nome utente:<span class="didascalia">Inserisci il tuo nome utente</span></label><input type="text" class="textbox required" id="username" name="username" value="" /><?php if(array_key_exists("MissingUsername", $error)) { echo '<label for="name" class="error">'.$error["MissingUsername"].'</label>'; } ?><?php if(array_key_exists("WrongUsername", $error)) { echo '<label for="name" class="error">'.$error["WrongUsername"].'</label>'; } ?><br/></p>
			<p><label>Scelta Password:<span class="didascalia">Inserisci la tua password </span></label><input type="password" class="textbox required" id="password" name="password" value="" /><?php if(array_key_exists("MissingPassword", $error)) { echo '<label for="name" class="error">'.$error["MissingPassword"].'</label>'; } ?><?php if(array_key_exists("WrongPassword", $error)) { echo '<label for="name" class="error">'.$error["WrongPassword"].'</label>'; } ?><br/></p>
	</fieldset>
			<p><input type="submit" id="submit_login" name="submit_login" value="Conferma" /></p>
			<!-- controllo antispam -->
			<input type="text" class="textbox" id="cp_ans" name="cp_ans" value="" style="display:none;"/>
	</form>
<?php } ?>
</div>
<?php include('include/templates/footer.inc.php'); ?>
