<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

// *** check access to check property pages
// -----------------------------------------------------------------------------
$access_allowed = false;

$page_types = explode('_', Application::Get('page'));
$page_type = isset($page_types[1]) ? $page_types[1] : '';
if(in_sub_array('property_code', $page_type, Application::Get('property_types'))){
	$access_allowed = true;
}

if(!$access_allowed){
	redirect_to('index.php');
}
