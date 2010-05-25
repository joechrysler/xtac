<?php
require_once 'php/Person.class.php';
require_once 'php/DataBase.class.php';


// ----------------
//  Transient Data
// ----------------

   $MySQL = new DataBase($db_host, $db_name);
   //$AuthorizedUsername = $_SERVER['PHP_AUTH_USER'];
   $AuthorizationLevel = '';
   $AuthorizedUsername = 'bob';


   $MySQL
      ->connect($db_user, $db_pass)
      ->getRole($AuthorizedUsername, $AuthorizationLevel)
      ->disconnect();

	echo $AuthorizationLevel;

?>
