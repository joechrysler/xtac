<?php

require_once 'config.php';	// Configuration script
require_once 'php/LDAP.class.php';

$ldc = new LDAP($ldap_url);

$usercn = $_GET['cn'];

$ldc
	->connect($pw_user, base64_decode($pw_pass))
	->resetPassword($usercn)
	->disconnect();

?>
