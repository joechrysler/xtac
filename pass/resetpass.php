<?php
// This script is designed to allow authorized Support Center users to reset a user's password
// in the case that the given user has forgotten their password and the answers to their challenge
// response questions.
//
// All changes made via this script are logged for future reference.  The Support Center cannot choose
// the password assigned to the user - it is random.  This script will also reset the grace login count
// for the given user.

include 'config.php';
include '/srv/www/live/webapps/include/sanitize.php';

//$usercn = 'teststu';
$usercn = sanitize_ldap_string($_POST['usercn']);
$success = false;

// Perform ldapsearch to find user's current dn...
if($_POST['confirm'] != "1")
{
	echo 'You did not confirm this operation!!!<br /><br />',
		'<a href="https://www.svsu.edu/netserv/xtac/">Back to XTAC</a>';
	die;
}

if ($connection = @ldap_connect($ldap_url))
{
	if ($bind = @ldap_bind($connection, $pw_user, base64_decode($pw_pass)))
	{
		$tempfilter = "cn=$usercn";
		$results = ldap_search($connection, "o=svsu", $tempfilter);

		$info = ldap_get_entries($connection, $results);

		if($info["count"] == 1)
		{
			$userdn = $info[0]["dn"];

			// Get a random password to use:
			$newpass = generatePassword();

			// Prepare updates:
			$update['userPassword'] = $newpass;
			$update1['loginGraceLimit'] = '20';
			$update2['loginGraceRemaining'] = '5';
			$update3['passwordExpirationTime'] = '20080223132322Z';

			// Execute updates:
			if(@ldap_modify($connection, $userdn, $update))
				$success = true;

			@ldap_modify($connection, $userdn, $update1);
			@ldap_modify($connection, $userdn, $update2);
			@ldap_modify($connection, $userdn, $update3);


			ldap_close($connection);
		}
	}
}		

if($success)
{
	$fh = fopen($passwordResetLogfile, 'a');
	fwrite($fh, date('r') . " - " . $_SERVER['PHP_AUTH_USER'] . " reset the password on " . $userdn . "\n");	
	fclose($fh);

	echo 'Password reset requested by <b>' , $_SERVER['PHP_AUTH_USER'] , '</b> was successful!<br /><br />'
		, 'This action was logged.<br /><br />'
		, 'Resetting password for: <b>',$usercn,'</b><br />'
		, 'New password: <b>' , $newpass , '</b><br /><br />'
		, 'Grace limit set to: 20 <br />'
		, 'Grace logins remaining set to: 5 <br /><br />'
		, '<b>*** NEW PASSWORD HAS BEEN EXPIRED ***</b><br /><br />'
		, 'Please instruct the user to change their password and reset their challenge questions!<br /><br />'
		, '<a href="https://www.svsu.edu/netserv/xtac/">Back to XTAC</a>'


} else {
	echo 'FAILED!';
}

function generatePassword($length = 8)
{

	// start with a blank password
	$password = "";

	// define possible characters
	$possible = '0123456789bcdfghjkmnpqrstvwxyz'; 

	// set up a counter
	$i = 0; 

	// add random characters to $password until $length is reached
	while ($i < $length) { 

		// pick a random character from the possible ones
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

		// we don't want this character if it's already in the password
		if (!strstr($password, $char)) { 
			$password .= $char;
			$i++;
		}

	}

	// done!
	return $password;

}

?>
