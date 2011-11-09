<?php

/**
 * @file install.php
 * @brief Script di installazione del sito web
 * @todo aggiungere vincoli di cancellazione automatica
 *   di alcune tabelle alla cancellazione di altre alla quale sono vincolate.
 * @todo redirizionare a questa pagina se non è ancora stato installato il db.
 * @todo forse i messaggi andrebbero ridisegnati come impostazione.
 * @todo prossima reinstallazione db rimuovere... avatar url
 * @todo prossima reinstallazione db rimuovere... beni url qui difficile
 *
 * Tramite questa pagina è possibile per l'utente l'installazione preliminare 
 *   del sito, con popolamento della base di dati. Durante la procedura, viene
 *   creato il primo utente, che ne rappresenta l'amministratore.
 *
 */

require('include/library/database.php');
require('include/library/utenti.php');
include('include/templates/header.inc.php');

$sql_init_db = array( 
	"utenti" => 'CREATE TABLE IF NOT EXISTS `Utenti` ( 
			`Nome` varchar(30) NOT NULL,
			`Cognome` varchar(30) NOT NULL,
			`NomeUtente` varchar(30) NOT NULL,
			`DataNascita` date NOT NULL,
			`Citta` varchar(30) NOT NULL,
			`Nazione` varchar(30) NOT NULL,
			`CAP` int(5) NOT NULL,
			`Indirizzo` varchar(30) NOT NULL,
			`PasswordSHA` varchar(132) NOT NULL,
			`IndirizzoPostaElettronica` varchar(30) NOT NULL,
			`ImmaginePercorsoURL` varchar(255),
			`DataRegistrazione` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`LastLoginDate` TIMESTAMP DEFAULT 0,
			`Attivo` tinyint(1) NOT NULL DEFAULT "1",
			PRIMARY KEY (`NomeUtente`),
			UNIQUE (`IndirizzoPostaElettronica`)
			) ENGINE=InnoDB;',

	"Gruppi" => 'CREATE TABLE IF NOT EXISTS `Gruppi` (
			`Nome` varchar(30) NOT NULL,
			`PermessoRichieste` tinyint(1) NOT NULL,
			`PermessoOfferte` tinyint(1) NOT NULL, 
			`PermessoAmministrazione` tinyint(1) NOT NULL, 
			PRIMARY KEY (`Nome`)
			) ENGINE=InnoDB;',

	"AppartenenzaUtenteAGruppo" => 'CREATE TABLE IF NOT EXISTS `AppartenenzaUtenteAGruppo` ( 
			`Utente` varchar(30) NOT NULL,
			`Gruppo` varchar(30) NOT NULL,
			FOREIGN KEY `Utente` (`Utente`) REFERENCES `Utenti` (`NomeUtente`) 
			ON DELETE CASCADE ON UPDATE CASCADE,
			FOREIGN KEY `Gruppo` (`Gruppo`) REFERENCES `Gruppi` (`Nome`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',

	// andrebbero potenziate di brutto...
	"Statistiche" => 'CREATE TABLE IF NOT EXISTS `Statistiche` ( 
			`Utente` varchar(30) NOT NULL,
			`numeroAccessi` int(12) NOT NULL,
			`numeroRichieste` int(12) NOT NULL,
			`numeroOfferte` int(12) NOT NULL,
			`numeroMessaggi` int(12) NOT NULL,
			PRIMARY KEY (`Utente`),
			FOREIGN KEY (`Utente`) REFERENCES `Utenti` (`NomeUtente`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',

	// TODO: letto?? i vari costraint con on cascade eccetera eccetera?
	"Messaggi" => 'CREATE TABLE IF NOT EXISTS `Messaggi` ( 
			`MsgID` int(16) NOT NULL AUTO_INCREMENT,
			`ParentID` int(16),
			`Mittente` varchar(30) NOT NULL,
			`Destinatario` varchar(30) NOT NULL,
			`DataInvio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`StatusReceiver` varchar(30) NOT NULL,
			`StatusSender` varchar(30) NOT NULL,
			`Titolo` varchar(30) NOT NULL,
			`Testo` text NOT NULL,
			PRIMARY KEY (`MsgID`),
			FOREIGN KEY (`Mittente`) REFERENCES `Utenti` (`NomeUtente`),
			FOREIGN KEY (`Destinatario`) REFERENCES `Utenti` (`NomeUtente`),
			FOREIGN KEY (`ParentID`) REFERENCES `Messaggi` (`MsgID`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',
	// cambiare disponibile in stato.. titolo univoco!
	"Offerte" => 'CREATE TABLE IF NOT EXISTS `Offerte` ( 
			`BeneID` int(12) NOT NULL AUTO_INCREMENT,
			`Titolo` varchar(40) NOT NULL,
			`Data` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`Descrizione` text NOT NULL,    
			`ImmaginePercorsoURL` varchar(255),
			`Quantita` int(6) NOT NULL,
			`ViewsNumber` int(12) NOT NULL,
			`Disponibile` tinyint(1) NOT NULL,
			`Offerente` varchar(30) NOT NULL,
			PRIMARY KEY (`BeneID`),
			FOREIGN KEY (`Offerente`) REFERENCES `Utenti` (`NomeUtente`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',

	"Richieste" => 'CREATE TABLE IF NOT EXISTS `Richieste` ( 
			`BeneID` int(12) NOT NULL AUTO_INCREMENT,
			`Titolo` varchar(40) NOT NULL,
			`Data` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`Descrizione` varchar(4096) NOT NULL,
			`ImmaginePercorsoURL` varchar(255),
			`Quantita` int(6) NOT NULL,
			`ViewsNumber` int(12) NOT NULL,
			`Disponibile` tinyint(1) NOT NULL,
			`Richiedente` varchar(30) NOT NULL,
			PRIMARY KEY (`BeneID`),
			KEY `Titolo` (`Titolo`),
			FOREIGN KEY (`Richiedente`) REFERENCES `Utenti` (`NomeUtente`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',

	"OperazioneOfferta" => 'CREATE TABLE IF NOT EXISTS `OperazioneOfferta` (
			`OperazioneID` int(12) NOT NULL AUTO_INCREMENT,
			`Data` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`Quantita` int(6) NOT NULL,
			`StatusOperationCreator` varchar(30) NOT NULL,
			`StatusOperationReceiver` varchar(30) NOT NULL,
			`BeneAssociato` int(12) NOT NULL,
			`MessaggioAssociato` int(16) NOT NULL,
			PRIMARY KEY (`OperazioneID`),
			FOREIGN KEY (`MessaggioAssociato`) REFERENCES `Messaggi` (`MsgID`),
			FOREIGN KEY (`BeneAssociato`) REFERENCES `Offerte` (`BeneID`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',

	"OperazioneRichiesta" => 'CREATE TABLE IF NOT EXISTS `OperazioneRichiesta` (
			`OperazioneID` int(12) NOT NULL AUTO_INCREMENT,
			`Data` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`Quantita` int(6) NOT NULL,
			`StatusOperationCreator` varchar(30) NOT NULL,
			`StatusOperationReceiver` varchar(30) NOT NULL,
			`BeneAssociato` int(12) NOT NULL,
			`MessaggioAssociato` int(16) NOT NULL,
			PRIMARY KEY (`OperazioneID`),
			FOREIGN KEY (`MessaggioAssociato`) REFERENCES `Messaggi` (`MsgID`),
			FOREIGN KEY (`BeneAssociato`) REFERENCES `Richieste` (`BeneID`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',

	// rimossa: `ImmaginePercorsoURL` varchar(255) NOT NULL,
	// todo: attenzione, va aggiornato codice con parent
	"Categorie" => 'CREATE TABLE IF NOT EXISTS `Categorie` ( 
			`Titolo` varchar(30) NOT NULL,
			`ParentID` varchar(30),
			PRIMARY KEY (`Titolo`),
			FOREIGN KEY (`ParentID`) REFERENCES `Categorie` (`Titolo`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',

	"AppartenenzaRichiesteACategorie" => 'CREATE TABLE IF NOT EXISTS `AppartenenzaRichiesteACategorie` ( 
			`Categoria` varchar(30) NOT NULL,
			`Richiesta` int(12) NOT NULL,
			FOREIGN KEY (`Categoria`) REFERENCES `Categorie` (`Titolo`),
			FOREIGN KEY (`Richiesta`) REFERENCES `Richieste` (`BeneID`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB; ',

	"AppartenenzaOfferteACategorie" => 'CREATE TABLE IF NOT EXISTS `AppartenenzaOfferteACategorie` ( 
			`Categoria` varchar(30) NOT NULL,
			`Offerta` int(12) NOT NULL,
			FOREIGN KEY (`Categoria`) REFERENCES `Categorie` (`Titolo`),
			FOREIGN KEY (`Offerta`) REFERENCES `Offerte` (`BeneID`)
			ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;',

	"incrementaContatoreMessaggi_trigger" => 'CREATE TRIGGER incrementaContatoreMessaggi
			AFTER INSERT ON Messaggi
			FOR EACH ROW
			UPDATE Statistiche SET numeroMessaggi = numeroMessaggi + 1 WHERE NEW.Mittente = utente;',

	"incrementaContatoreOfferte_trigger" => 'CREATE TRIGGER incrementaContatoreOfferte
			AFTER INSERT ON Offerte
			FOR EACH ROW
			UPDATE Statistiche SET numeroOfferte = numeroOfferte + 1 WHERE NEW.Offerente = utente;',

	"incrementaContatoreRichieste_trigger" => 'CREATE TRIGGER incrementaContatoreRichieste
			AFTER INSERT ON Richieste
			FOR EACH ROW
			UPDATE Statistiche SET numeroRichieste = numeroRichieste + 1 WHERE NEW.Richiedente = utente;',
	"CreaGruppoAmministratori" => "INSERT INTO `Gruppi` VALUES ('Amministratori', '1', '1', '1');",
	"CreaGruppoUtenti" => "INSERT INTO `Gruppi` VALUES ('Utenti', '1', '1', '0');"
	);
	
/* se non è stato installato impedire l'esecuzione degli altri script.. todo */

$db_handle = new Database();

try {
	$db_handle->connect();
	foreach($sql_init_db as $db_query)
		$db_handle->query($db_query);
	?>
	<aside>
	Registrazione primo utente del sito
	</aside>
	<div id="main-content"> 
	<h2> Installazione - registrazione utente amministratore </h2>
	<?php include('include/snippet/registrazione_snippet.php');
	// se transazione ha buon esito, associa utente a gruppo
} catch(Exception $e) {
	echo $e->getMessage(), "\n"; }
include('include/templates/footer.inc.php'); ?>
