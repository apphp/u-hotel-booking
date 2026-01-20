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

// *** check if database connection parameters file exists
if(!file_exists('include/base.inc.php')){
	header('location: install.php');
	exit;
}

// *** set flag that this is a parent file
define('APPHP_EXEC', 'access allowed');

require_once('include/base.inc.php');
require_once('include/connection.php');
require_once('modules/additional/rest_api/api.class.php');

$api = new PHP_CRUD_API();
$api->executeCommand();

