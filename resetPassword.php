<?php

require_once 'config.php';	// Configuration script
require_once 'php/LDAP.class.php';

$LDAP = new LDAP($ldap_url);

$usercn = $_GET['cn'];

$LDAP
	->connect($pw_user, base64_decode($pw_pass))
	->resetPassword($usercn)
	->disconnect();

?>
