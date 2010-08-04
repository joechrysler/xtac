<?php
require_once 'php/Person.class.php';
require_once 'php/DataBase.class.php';
require_once 'php/LDAP.class.php';
require_once 'php/print_nice.php';
require_once 'config.php';

date_default_timezone_set('America/New_York');

// ----------------
//  Transient Data
// ----------------

// Database Connections
	$MySQL = new DataBase($db_host, $db_name);
	$LDAP = new LDAP($ldap_url);

// Lists of which database fields the user can see
	$AuthorizedLDAPFields = array();
	$AuthorizedMySQLFields = '';

// Temporary storage for info on the person being looked up
// ingested into the Person object during the import phase
	$LDAPRecord = array();
	$MySQLRecord = array();
	$SupportHistory = array();
	$criticalUsers = array();
	$EligibleForSoftwareCheckout = false;
	$username = '';

// Information about the user that is currently accessing xtac
// The role which the active user fulfills (library, admin, intern, etc.)
// fetched from the MySQL database
	$AuthorizedUsername = $_SERVER['PHP_AUTH_USER'];
	$AuthorizationLevel = '';
	$PasswordResetAllowed = false;

// A list of all the facts that the university should know about someone
// used to build the Person object during the import phase.
	$PersonalAttributes = array();




// ----------------------------------
//  Get raw data from both Databases
// ----------------------------------

	$MySQL
		->connect($db_user, $db_pass)
		->getRole($AuthorizedUsername, $AuthorizationLevel)
		->getAuthorizedFields($AuthorizedUsername, $AuthorizedMySQLFields, $AuthorizedLDAPFields)
		->getCriticalUsers($criticalUsers)
		->disconnect();

	print_nice($criticalUsers);
	if (!in_array('admin', $criticalUsers))
		echo 'Joe is not in here';
	else
		echo 'Joe is in here';


?>
