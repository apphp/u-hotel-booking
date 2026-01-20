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
	<meta name="description" content="uHotelBooking REST API">
	<meta name="author" content="ApPHP">

	<title>uHotelBooking REST API</title>
	
	<link rel="SHORTCUT ICON" href="appph.ico" />    

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
		$page = 'index.php';
		include('include/nav.php');
	?>

	<!-- Begin page content -->
	<div class="container">
		<div class="page-header">
			<h1>uHotelBooking API Examples</h1>
		</div>
		
		<p class="lead">
			Examples below allow you to test uHotelBooking API.<br>
			Generally only site admins or hotel owners allowed to access protected site data.
		</p>

		<p>
			<h4>Features</h4>
			<ul>
				<li>Single PHP file, easy to deploy.</li>
				<li>Very little code, easy to adapt and maintain</li>
				<li>Streaming data, low memory footprint</li>
				<li>Supports POST variables as input</li>
				<li>Supports a JSON object as input</li>
				<li>Condensed JSON ouput: first row contains field names</li>
				<li>Sanitize and validate input using callbacks</li>
				<li>Permission system for databases, tables, columns and records</li>
				<li>Multi-tenant database layouts are supported</li>
				<li>Both JSONP and CORS support for cross-domain requests</li>
				<li>Combined requests with support for multiple table names</li>
				<li>Search support on multiple criteria</li>
				<li>Pagination, sorting and column selection</li>
				<li>Relation detection and filtering on foreign keys</li>
				<li>Relation "transforms" for PHP and JavaScript</li>
				<li>Binary fields supported with base64 encoding</li>
				<li>Generate API documentation using Swagger tools</li>
			</ul>
		</p>
		<br>

		<p>
			<h4>Limitations</h4>
			<ul>
				<li>Authentication is not included</li>
				<li>Composite primary or foreign keys are not supported</li>
				<li>Complex filters (with both "and" & "or") are not supported</li>
				<li>Complex writes (transactions) are not supported</li>
				<li>Complex queries calling functions (like "concat" or "sum") are not supported</li>
				<li>Batch operations for insert, update and delete are not supported</li>
			</ul>
		</p>
		<br>
		
		<p>
			<h4>Requirements</h4>
			<ul>
				<li>PHP 5.3 or higher with MySQLi</li>
				<li>uHotelBooking script v2.4.0 or above</li>
			</ul>
		</p>

	</div>
	
	<p><br></p>
	<p><br></p>
	
	<footer class="footer">
      <div class="container">
		<p class="text-muted">Based on <a href="https://github.com/mevdschee/php-crud-ui">PHP-CRID-API</a></p>			
      </div>
    </footer>
  
</body>
</html>
