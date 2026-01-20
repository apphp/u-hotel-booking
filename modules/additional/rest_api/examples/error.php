<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="">
	<meta name="author" content="">

	<title>uHotelBooking API Examples</title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!-- Include the jQuery library -->
	<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>

	<script src="include/main.js"></script>
</head>
<body>

	<!-- Navigation -->
	<?php
		$page = 'examples-index.php';
		include('include/nav.php');
	?>

	<!-- Begin page content -->
	<div class="container">
		<div class="page-header"><h1>Error</h1></div>
		<h4>Error Message:</h4>
		<pre>Misconfiguration or wrong credentials have been specified! File - <?= __DIR__.'/include/config.inc.php'; ?></pre>
	</div>
</body>
</html>
