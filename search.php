<?php
 
require_once 'php/DataBase.class.php';
require_once 'config.php';

$MySQLDatabase = new DataBase($db_host, $db_name);
$role = '';
$results = array();

// Check for missing parameters
if (!@$_GET['q']){
	echo 'this must be called with a get variable \'q\'';
	return;
}

$MySQLDatabase
	->connect($db_user, $db_pass)
	->getRole($_SERVER['PHP_AUTH_USER'], $role)
	->searchUsers($_GET['q'], $results);

echo json_encode($results);
?>
