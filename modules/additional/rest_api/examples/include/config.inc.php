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

// Path to your site root directory (must include "/" at the end)
// Example:
//	http://localhost/
//	http://localhost/hotel/
$site_url = 'http://hotel/';

// API key 
$api_key = 'eobpdbddv4jrzi20b0wv';

// API secret
$api_secret = '3eaa4acb7ba69e54236b46ab334c7d75';


// Token
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/(http|https|ftp):\/\//i', '', $_SERVER['HTTP_HOST']) : '';
$host = 'http://'.$host;
if($site_url != $host){
	$host = $site_url;
}
$api_token = md5($host.'#'.$api_secret);

if(empty($api_key) || empty($site_url) || empty($api_secret)){
	include('error.php');
	die();
}
