<?php
	session_start();
	require_once('php/DataBase.class.php');
	require_once('config.php');
?>
<html>
	<head>
		<title>
		SVSU Administrator Tools
		</title>

		<!-- Style Sheets -->
			<link href="css/master.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
			<link href="css/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
			<!--[if IE 6]>
			<link href="/css/screen-ie6.css" type="text/css" rel="stylesheet" media="screen" /><![endif]--> 
			<!--[if IE 7]>
			<link href="/css/screen-ie7.css" type="text/css" rel="stylesheet" media="screen" /><![endif]--> 
			<!--[if IE 8]>
			<link href="/css/screen-ie8.css" type="text/css" rel="stylesheet" media="screen" /><![endif]--> 

		<!-- Scripts -->
			<script src="js/jquery.min.js" type="text/javascript"></script>
			<script src="js/jquery.autocomplete.min.js" type="text/javascript"></script>
			<script src="js/autocolumn.min.js" type="text/javascript"></script>
			<script src="js/main.js"></script>
			<script src="js/jquery.slidingLabels.min.js"></script>
	</head>

	<body>
		<h1>Admin Tools</h1>

		<?php require_once('forms/withAutoComplete.html');?>

		<div id="result"></div>
	</body>
</html>
