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
		->getRole($AuthorizedUsername, $AuthorizationLevel)     // What role does this admin fill?  (admin, library staff, intern, etc.)
		->getAuthorizedFields($AuthorizedUsername, $AuthorizedMySQLFields, $AuthorizedLDAPFields)     // Which fields is this admin allowed to see?
		->getUsername($Person->id, $username)     // What is the user's username?
		->getUser($Person->id, $AuthorizedMySQLFields, $MySQLRecord)     // Load the user's data into the MYSQLRecord array.
		->getHistory($Person->id, $SupportHistory)     // Fetch the user's support history.
		->checkMSEligibility($Person->id, $EligibleForSoftwareCheckout)     // Is the user allowed to check out Microsoft Software from the Library?
		->canResetPassword($AuthorizedUsername, $PasswordResetAllowed)     // Is the current admin allowed to reset user passwords?
		->getCriticalUsers($criticalUsers)     // Fetch the list of users whose passwords must NOT be reset by this script.
		->getAttributes($PersonalAttributes)     // Fetch a list of all data fields that SVSU stores about each user.
		->disconnect();


	$LDAP
		->connect($ldap_user, $ldap_pass)
		->getUser($username, $AuthorizedLDAPFields, $LDAPRecord)     // Load the user's data into the LDAPRecord array.
		->disconnect();

	

// ----------------------------------------
//  Import raw data into the Person object
// ----------------------------------------

	$Person
		->importCategories($PersonalAttributes)		// Populate the Person object with an updated list of data that SVSU stores about its users.
		->importLdapData($LDAPRecord)     // Load data from LDAP into the Person object.
		->importMysqlData($MySQLRecord);     // Load data from MYSQL into the Person object.



// ----------------------------------------
//  Display data on a webpage
// ----------------------------------------

	$Person->draw();     // Display the user's data onscreen, indicating any inconsistencies between LDAP and MYSQL.
	
	if ($AuthorizationLevel === 'library')     // The current admin is a library staffer and should be notified of the user's eligibility to check out MS software
		$Person->DisplayMSSoftwareEligibility($EligibleForSoftwareCheckout);
	elseif ($Person->isFullUser())     // The user is a real person, and therefore might have support history
		$Person->drawHistory($SupportHistory, $HistoryItemsShown);
	if ($PasswordResetAllowed == true && $Person->isFullUser() && !in_array($username, $criticalUsers)) {     // The user is a real person, their password can be reset and the current admin is allowed to reset passwords.
		$Person->drawPasswordReset($username);
		if (!$Person->hasGraceLogins())     // The user has no grace logins remaining
			$Person->drawAddGraceLogins($username);
	}
		
?>
