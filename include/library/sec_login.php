<?php
/* includere controlli. Se è già loggato non serve... */
session_start();
setlocale(LC_TIME, 'ita', 'it_IT.utf8');

if(basename($_SERVER['PHP_SELF']) != "accedi.php" and !Utente::is_user_logged_in()) {
	header('location:accedi.php');
}

?>
