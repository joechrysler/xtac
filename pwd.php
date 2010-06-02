<?php
require_once 'config.php';
echo ($pw_pass === hash('sha256', $_GET['p']))?
	'correct':
	'incorrect';
?>
