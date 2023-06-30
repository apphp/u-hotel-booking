<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

define('APPHP_EXEC', 'access allowed');
define('APPHP_CONNECT', 'direct');
require_once('../include/base.inc.php');
require_once('../include/connection.php');

$template = isset($_POST['template']) ? prepare_input($_POST['template']) : '';
$check_key = isset($_POST['check_key']) ? prepare_input($_POST['check_key']) : '';
$token = isset($_POST['token']) ? prepare_input($_POST['token']) : '';
$session_token = isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr = array();

if($objLogin->IsLoggedInAsAdmin() && $check_key == 'apphphs'  && ($token == $session_token) && $template != ''){
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Pragma: no-cache'); // HTTP/1.0
	header('Content-Type: application/json');
	
	$arr[] = '"status": "1"';

	$template_name = '';
	$template_icon = '';
	$template_direction = '';
	$template_description = '';
	$template_license = '';
	$template_version = '';
	$template_layout = '';
    $template_author = '';
	$template_menus = '';
	
	if(@file_exists('../templates/'.$template.'/info.xml')) {
		$xml = simplexml_load_file('../templates/'.$template.'/info.xml');		 
		$template_name = $xml->name;
		$template_icon = $xml->icon;
		$template_direction = $xml->direction;
        $template_author = $xml->author;
		$template_description = $xml->description;
		$template_license = $xml->license;
		$template_version = $xml->version;
		$template_layout = $xml->layout;
		if(isset($xml->menus->menu)){
			foreach($xml->menus->menu as $menu){
				if($template_menus != '') $template_menus .= ',';
				$template_menus .= $menu;
			}				
		}
	}

	$arr[] = '"template_name": "'.$template_name.'"';
	$arr[] = '"template_icon": "'.$template_icon.'"';
	$arr[] = '"template_direction": "'.$template_direction.'"';
	$arr[] = '"template_description": "'.$template_description.'"';
    $arr[] = '"template_author": "'.$template_author.'"';
	$arr[] = '"template_license": "'.$template_license.'"';
	$arr[] = '"template_version": "'.$template_version.'"';
	$arr[] = '"template_layout": "'.$template_layout.'"';
	$arr[] = '"template_menus": "'.$template_menus.'"';
	
	echo '{';
	echo implode(',', $arr);
	echo '}';
}else{
	// wrong parameters passed!
	$arr[] = '"status": "0"';
	echo '{';
	echo implode(',', $arr);
	echo '}';
}    

