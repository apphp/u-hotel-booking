<?php
/**
 * REST API module for uHotelBooking script
 *
 * @project uHotelBooking
 * @copyright (c) 2019 ApPHP
 * @author ApPHP <info@apphp.com>
 * @site http://www.hotel-booking-script.com
 * @license http://hotel-booking-script.com/license.php
 */	  

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="">
	<meta name="author" content="ApPHP">

	<title>Configuration - uHotelBooking REST API</title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>

	<!-- Navigation -->
	<?php
		$page = 'configuration.php';
		include('include/nav.php');
	?>

	<!-- Begin page content -->
	<div class="container">
		<div class="page-header">
			<h2>API Configuration</h2>
		</div>

		<p>
			To start testing of these exampels do following:<br>
			<ol>
				<li>Generate API Key and API Secret in your account</li>
				<li>Copy generated API Key and API Secret codes</li>
				<li>Open <code>examples/include/config.inc.php</code> file</li>
				<li>Enter copied API Key and API Secret in approprite variables in configuration file</li>
				<li>Enter your website base URL into <code>$site_url</code> in configuration file</li>
			</ol>
		</p>
		<br>
		
	</div>
  
</body>
</html>
