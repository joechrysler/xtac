<?php
	session_start();
?>
<html>
	<head>
		<title>
		SVSU Administrator Tools
		</title>

		<!-- Style Sheets -->
			<link href="css/master.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
			<link href="css/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
			<link href="css/blitzer/jquery-ui.css" rel="stylesheet" type="text/css" />
			<!--[if IE 6]>
			<link href="/css/screen-ie6.css" type="text/css" rel="stylesheet" media="screen" /><![endif]--> 
			<!--[if IE 7]>
			<link href="/css/screen-ie7.css" type="text/css" rel="stylesheet" media="screen" /><![endif]--> 
			<!--[if IE 8]>
			<link href="/css/screen-ie8.css" type="text/css" rel="stylesheet" media="screen" /><![endif]--> 

		<!-- Scripts -->
			<script src="js/jquery.min.js" type="text/javascript"></script>
			<script src="js/jquery-ui.min.js" type="text/javascript"></script>
			<script src="js/jquery.autocomplete.min.js" type="text/javascript"></script>
			<script src="js/autocolumn.min.js" type="text/javascript"></script>
			<script src="js/main.js"></script>
	</head>

	<body>
		<h1>Admin Tools</h1>
			<form autocomplete="off">
				 <input type="text" id="searchField" name="searchField" />
			</form>
		<div id="result"></div>
		<div id="dialog" title="Confirm Password Reset">
			<p>
				<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 0 0;"></span>
				You are about to reset the password for:
			</p>
			<p id="dialog-username"></p>
		</div>
	</body>
</html>
