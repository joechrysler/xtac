<?php
require_once 'php/DataBase.class.php';
require_once 'config.php';

$MySQLDatabase = new DataBase($db_host, $db_name);
$MySQLDatabase
	->connect($db_user, $dbpass)
	->addField($_POST['newMysqlField'], $_POST['newLdapField']=null, $_SERVER['PHP_AUTH_USER'])
	->disconnect();
?>
