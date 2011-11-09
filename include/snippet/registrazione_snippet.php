<div id="register_fieldset"> 
<?php
if(isset($_POST['submit_reg'])) {
	$error = array();
	// controllo campo per campo, da mettere
	$name = isset($_POST['name']) ? trim($_POST['name']) : '';
	$surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
	$datanascita = isset($_POST['datanascita']) ? trim($_POST['datanascita']) : '';
	$indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
	$city = isset($_POST['city']) ? trim($_POST['city']) : '';
	$nazione = isset($_POST['nazione']) ? trim($_POST['nazione']) : '';
	$cap = isset($_POST['cap']) ? trim($_POST['cap']) : '';
	$username = isset($_POST['username']) ? trim($_POST['username']) : '';
	$email = $_POST['email'] ? trim($_POST['email']) : '';
	$confirm_email = $_POST['confirm_email'] ? trim($_POST['confirm_email']) : '';
	$password = $_POST['password'] ? trim($_POST['password']) : '';
	$password_confirm = $_POST['password_confirm'] ? trim($_POST['password_confirm']) : '';
	$cp_ans = $_POST['cp_ans'] ? trim($_POST['cp_ans']) : '';

	/* se il campo nascosto non è vuoto significa che si tratta di uno spammer */
	if(!empty($cp_ans)) $error["SpamDetected"] = "A bot may not injure (or simply annoy) a human being or, through inaction, allow a human being to come to harm";

	/* se uno dei campi è vuoto si rimanda alla pagina iniziale */
	empty($name) ? $error["MissingName"] = "Il campo nome è incompleto" : '';
	empty($surname) ? $error["MissingSurname"] = "Il campo cognome è incompleto" : '';
	empty($datanascita) ? $error["MissingBirthdate"] = "Il campo data di nascita è incompleto" : '';
	empty($indirizzo) ? $error["MissingAddress"] = "Il campo indirizzo è incompleto" : '';
	empty($city) ? $error["MissingCity"] = "Il campo città è incompleto" : ''; 
	empty($nazione) ? $error["MissingNation"] = "Il campo nazione è incompleto" : ''; 
	empty($cap) ? $error["MissingPostalCode"] = "Il campo CAP è incompleto" : ''; 
	empty($username) ? $error["MissingUsername"] = "Il campo nome utente è incompleto" : '';
	empty($email) ? $error["MissingEmail"] = "Il campo e-mail è incompleto" : '';
	empty($confirm_email) ? $error["MissingConfirmEmail"] = "Il campo di conferma per l'email è incompleto" : ''; 
	empty($password) ? $error["MissingPassword"] = "Il campo password è incompleto" : ''; 
	empty($password_confirm) ? $error["MissingConfirmPassword"] = "Il campo di conferma per la password è incompleto" : '';
	
	// controllo correttezza della data
	if(strlen($datanascita) == 10 and substr_count($datanascita,"/") == 2) {
		list($giorno, $mese, $anno) = split('/', $datanascita);
		$datanascita = $anno . "-" . $mese . "-" . $giorno;
		if(!checkdate($mese, $giorno, $anno)) 
			$error["InvalidData"] = "La data non è valida";
	} else $error["InvalidData"] = "La data non è valida";

	// controllo correttezza e-mail
	if(!filter_var($email,FILTER_VALIDATE_EMAIL)) 
		$error["EmailNotValid"] = "L'indirizzo e-mail non è corretto";
	// controllo stessa e-mail
	if($email !== $confirm_email)
		$error["EmailDontMatch"] = "Gli indirizzi e-mail non corrispondono";
	// controllo stessa password
	if($password !== $password_confirm)
		$error["PasswordsDontMatch"] = "Le password non corrispondono";
	$db_handle = new Database();
	try {
		$db_handle->connect();
		/* non funziona!! */
		$check_user_query = "SELECT * FROM Utenti WHERE NomeUtente = '". $username ."'";
		$check_email_query = "SELECT * FROM Utenti WHERE IndirizzoPostaElettronica = '". $email ."'";
		$risultato_utente = $db_handle->query($check_user_query);
		$risultato_email = $db_handle->query($check_email_query);
		if (mysql_num_rows($risultato_utente) > 0)
			$error["ExistingUsername"] = "Il nome utente esiste già";
		if (mysql_num_rows($risultato_email) > 0)
			$error["ExistingEmail"] = "Esiste già un'utente registrato con questa password";
		if(empty($error)) {
			// con hashing e salt TODO ne facciamo funzione?
			$hashed_password = Utente::hash_salt_password($password);
			$insert_user_query = "INSERT INTO Utenti (`Nome`, `Cognome`, `NomeUtente`, `DataNascita`, `Citta`, `Nazione`, `CAP`, `Indirizzo`, `PasswordSHA`, `IndirizzoPostaElettronica`) 
								  VALUES 
								  ('".mysql_real_escape_string($name)."', '".mysql_real_escape_string($surname)."', '".mysql_real_escape_string($username)."', '".mysql_real_escape_string($datanascita)."', '".mysql_real_escape_string($city)."', '".mysql_real_escape_string($nazione)."', '".mysql_real_escape_string($cap)."', '".mysql_real_escape_string($indirizzo)."', '".mysql_real_escape_string($hashed_password)."', '".mysql_real_escape_string($email)."');";
			// se è il primo utente allora lo facciamo divenire in automatico amministratore
			$check_number_users = "SELECT * From Utenti";
			$risultato_number_users = $db_handle->query($check_number_users);
			if(mysql_num_rows($risultato_number_users) == 0)
				$insert_in_group_query = "INSERT INTO `AppartenenzaUtenteAGruppo` VALUES ('".$username."', 'Amministratori')";
			else $insert_in_group_query = "INSERT INTO `AppartenenzaUtenteAGruppo` VALUES ('".$username."', 'Utenti')";
			$insert_statistics_query = "INSERT INTO `Statistiche` VALUES ('".$username."', '0', '0', '0', '0')";
			// se primo utente, allora automaticamente amministratore? altrimenti utente normale
			$db_handle->query($insert_user_query);
			$db_handle->query($insert_in_group_query);
			$db_handle->query($insert_statistics_query);
			echo "Utente registrato con successo";
			// tutto ok? invio e-mail? conferma? TODO
		} 
	} catch(Exception $e) {
		echo $e->getMessage(), "\n"; 
	} $db_handle->disconnect();
} if(!isset($_POST['submit_reg']) or !empty($error)) { ?>
	<!-- mettere che non cancelli tutto... -->
	<form id="register_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<fieldset>
		<legend> Generalità </legend>
			<p><label>Nome:<span class="didascalia">Aggiungi il tuo nome</span></label><input type="text" class="textbox required" id="name" name="name" value="" /><?php if(array_key_exists("MissingName", $error)) { echo '<label for="name" class="error">'.$error["MissingName"].'</label><br/></p>'; } ?>
<br/></p>
			<p><label>Cognome:<span class="didascalia">Aggiungi il tuo cognome</span></label><input type="text" class="textbox required" id="surname" name="surname" value="" /><?php if(array_key_exists("MissingSurname", $error)) { echo '<label for="name" class="error">'.$error["MissingSurname"].'</label><br/></p>'; } ?><br/></p>
			<p><label>Data di nascita:<span class="didascalia">La tua data di nascita (gg/mm/aaaa)</span></label><input type="text" class="textbox required date" id="datanascita" name="datanascita" value="" /><?php if(array_key_exists("MissingBirthdate", $error)) { echo '<label for="name" class="error">'.$error["MissingBirthdate"].'</label><br/></p>'; } if(array_key_exists("InvalidData", $error)) { echo '<label for="name" class="error">'.$error["InvalidData"].'</label>'; } ?><br/></p>
			<p><label>Indirizzo:<span class="didascalia">Inserisci il tuo indirizzo</span></label><input type="text" class="textbox required" id="indirizzo" name="indirizzo" value="" /><?php if(array_key_exists("MissingAddress", $error)) { echo '<label for="name" class="error">'.$error["MissingAddress"].'</label><br/></p>'; } ?><br/></p>
			<p><label>Città:<span class="didascalia">Inserisci il nome della tua città</span></label><input type="text" class="textbox required" id="city" name="city" value="" /><?php if(array_key_exists("MissingCity", $error)) { echo '<label for="name" class="error">'.$error["MissingCity"].'</label><br/></p>'; } ?><br/></p>
			<p><label>Nazione:<span class="didascalia">Inserisci la tua nazione</span></label><input type="text" class="textbox required" id="nazione" name="nazione" value="" /><?php if(array_key_exists("MissingNation", $error)) { echo '<label for="name" class="error">'.$error["MissingNation"].'</label><br/></p>'; } ?><br/></p>
			<p><label>CAP:<span class="didascalia">Inserisci il CAP</span></label><input type="text" class="textbox required" id="cap" name="cap" value="" /><?php if(array_key_exists("MissingPostalCode", $error)) { echo '<label for="name" class="error">'.$error["MissingPostalCode"].'</label><br/></p>'; } ?><br/></p>
		</fieldset>
		<fieldset>
		<legend> Dati per l'accesso </legend>
			<!-- ajax per controllo email e nome utente?? -->
			<p><label>Nome utente:<span class="didascalia">Scegli il tuo nome utente</span></label><input type="text" class="textbox required" id="username" name="username" value="" /><?php if(array_key_exists("MissingUsername", $error)) { echo '<label for="name" class="error">'.$error["MissingUsername"].'</label>'; } ?><?php if(array_key_exists("ExistingUsername", $error)) { echo '<label for="name" class="error">'.$error["ExistingUsername"].'</label>'; } ?><br/></p>
			<p><label>E-mail:<span class="didascalia">Scrivi la tua e-mail</span></label><input type="text" class="textbox required email" id="email" name="email" value="" /><?php if(array_key_exists("MissingEmail", $error)) { echo '<label for="name" class="error">'.$error["MissingEmail"].'</label><br/></p>'; } ?><?php if(array_key_exists("ExistingEmail", $error)) { echo '<label for="name" class="error">'.$error["ExistingEmail"].'</label>'; } ?><br/></p>
			<p><label>Conferma E-mail:<span class="didascalia">Riscrivila per sicurezza</span></label><input type="text" class="textbox required email" id="confirm_email" name="confirm_email" value="" /><?php if(array_key_exists("MissingConfirmEmail", $error)) { echo '<label for="name" class="error">'.$error["MissingConfirmEmail"].'</label><br/></p>'; } ?><br/></p>
			<p><label>Scelta Password:<span class="didascalia">Scegli la password (a-z, 0-9, simboli)</span></label><input type="password" class="textbox required" id="password" name="password" value="" /><?php if(array_key_exists("MissingPassword", $error)) { echo '<label for="name" class="error">'.$error["MissingPassword"].'</label><br/></p>'; } ?><br/></p>
			<!-- mettere robustezza password -->
			<p><label>Ripeti Password:<span class="didascalia">Riscrivila per sicurezza</span></label><input type="password" class="textbox required" id="password_confirm" name="password_confirm" value="" /><?php if(array_key_exists("MissingConfirmPassword", $error)) { echo '<label for="name" class="error">'.$error["MissingConfirmPassword"].'</label><br/></p>'; } ?><br/></p>
		</fieldset>
			<p><input type="submit" id="submit_reg" name="submit_reg" value="Conferma" /></p>
			<!-- controllo antispam -->
			<input type="text" class="textbox" id="cp_ans" name="cp_ans" value="" style="display:none;"/>
	</form>
<?php } ?>
</div>
