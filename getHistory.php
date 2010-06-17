<?php
require_once 'php/Person.class.php';
require_once 'php/DataBase.class.php';
require_once 'config.php';

$MySQL = new DataBase($db_host, $db_name);
$Person = new Person();
$Person->setID(trim($_GET['id']));
$SupportHistory = array();
$AuthorizedUsername = $_SERVER['PHP_AUTH_USER'];
$AuthorizationLevel = '';

$MySQL
	->connect($db_user, $db_pass)
	->getRole($AuthorizedUsername, $AuthorizationLevel)
	->getHistory($Person->id, $SupportHistory)
	->disconnect();

if ($AuthorizationLevel !== 'library')
	$Person->drawHistoryItems($SupportHistory, $HistoryItemsShown);
?>
