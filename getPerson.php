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

// Container for the person being looked up
	$Person = new Person();
	$Person->setID(trim($_GET['id']));

// Lists of which database fields the user can see
	$AuthorizedLDAPFields = array();
	$AuthorizedMySQLFields = '';

// Temporary storage for info on the person being looked up
// ingested into the Person object during the import phase
	$LDAPRecord = array();
	$MySQLRecord = array();
	$SupportHistory = array();
	$EligibleForSoftwareCheckout = false;
	$username = '';
	$criticalUsers = array();

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
		->getUsername($Person->id, $username)
		->getUser($Person->id, $AuthorizedMySQLFields, $MySQLRecord)
		->getHistory($Person->id, $SupportHistory)
		->checkMSEligibility($Person->id, $EligibleForSoftwareCheckout)
		->canResetPassword($AuthorizedUsername, $PasswordResetAllowed)
		->getCriticalUsers($criticalUsers)
		->getAttributes($PersonalAttributes)
		->disconnect();


	$LDAP
		->connect($ldap_user, $ldap_pass)
		->getUser($username, $AuthorizedLDAPFields, $LDAPRecord)
		->disconnect();

	

// ----------------------------------------
//  Import raw data into the Person object
// ----------------------------------------

	$Person
		->importCategories($PersonalAttributes)
		->importLdapData($LDAPRecord)
		->importMysqlData($MySQLRecord);



// ----------------------------------------
//  Display data on a webpage
// ----------------------------------------

	$Person->draw();
	
	if ($AuthorizationLevel === 'library')
		$Person->DisplayMSSoftwareEligibility($EligibleForSoftwareCheckout);
	elseif ($Person->isFullUser())
		$Person->drawHistory($SupportHistory, $HistoryItemsShown);
	if ($PasswordResetAllowed == true && $Person->isFullUser() && !in_array($username, $criticalUsers)) {
		$Person->drawPasswordReset($username);
		if (!$Person->hasGraceLogins())
			$Person->drawAddGraceLogins($username);
	}
		
?>
