<?php

require_once 'config.php';	// Configuration script
require_once '/srv/www/live/webapps/include/sanitize.php';	// Provides sanitize_ldap_string()

$ldc = new LDAP($ldap_url);

$usercn = $_GET['cn'];

$ldc
	->connect($ldap_user, $ldap_pass)
	->resetPassword($usercn)
	->disconnect();

?>
