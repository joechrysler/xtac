<?php
require_once 'php/DataBase.class.php';
require_once 'config.php';

$MySQLDatabase = new DataBase($db_host, $db_name);
$MySQLDatabase
	->connect($db_user, $db_pass)
	->addField(
		$_POST['canonicalName'],
		$_POST['readableName'],
		$_POST['category'],
		$_POST['mysqlField'],
		$_POST['ldapField'],
		$_POST['authorizationLevelRequired'])
	->disconnect();
?>
