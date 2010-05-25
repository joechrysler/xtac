<?php
require_once 'php/DataBase.class.php';
require_once 'config.php';

$MySQLDatabase = new DataBase($db_host, $db_name);
$MySQLDatabase
	->connect($db_user, $db_pass)
	->addComment($_GET['id'], $_SERVER['PHP_AUTH_USER'], $_GET['comment'])
	->disconnect();

?>
