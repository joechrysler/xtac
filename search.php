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
	->searchUsers($_GET['q'], $results)
	->disconnect();

//echo json_encode($results),'<br/><br/>';
// Replacement for json_encode.  Remove after netserv upgrades to php 5.3
$firstItem = true;
$firstResult = true;
echo '[';
foreach ($results as $matchingUsers) {
	if (!$firstResult)
		echo ',';

	echo '{';
	foreach ($matchingUsers as $attribute => $value) {
		if (!$firstItem)
			echo ',';

		echo '"',$attribute,'":"',$value,'"';
		$firstItem = false;
	}
	$firstItem = true;
	echo '}';
	$firstResult = false;
}
echo ']';
?>
