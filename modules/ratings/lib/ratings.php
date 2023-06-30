<?php
// Star Rating Script - http://coursesweb.net/php-mysql/

//////////////////////////////////////////////////////////////////////////////////
// [#001 apphp 27.12.2012] - prevents direct access
if(isset($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')){
  echo 'The direct access to this page is prohibited!';
  exit;
}
//////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////
// [#002 apphp 28.12.2012] - includes all needed files
$basedir = "../../../";
define('APPHP_EXEC', 'access allowed');
define('APPHP_CONNECT', 'direct');
require_once($basedir.'include/base.inc.php');
require_once($basedir.'include/connection.php');

$is_demo = (defined('SITE_MODE') && SITE_MODE == 'demo') ? true : false;
////////////////////////////////////////////////////////////////////////////////////

define('SVRATING', 'mysql');        // change 'txt' with 'mysql' if you want to save rating data in MySQL

// HERE define data for connecting to MySQL database (MySQL server, user, password, database name)
//define('DATABASE_HOST', 'localhost');
//define('DATABASE_USERNAME', 'root');
//define('DATABASE_PASSWORD', '');
//define('DATABASE_NAME', 'business_directory');

// if NRRTG is 0, the user can rate multiple items in a day, if it is 1, the user can rate only one item in a day
if(ModulesSettings::Get('ratings', 'multiple_items_per_day') == 'yes'){
  define('NRRTG', 0);
}else{
  define('NRRTG', 1);
}

// If you want than only the logged users to can rate the element(s) on page, sets USRRATE to 0
// And sets $_SESSION['username'] with the session that your script uses to keep logged users
if(ModulesSettings::Get('ratings', 'user_type') == 'registered'){
  define('USRRATE', 0);
}else{
  define('USRRATE', 1);  
}

if(USRRATE !== 1) {
  //if(!isset($_SESSION)) session_start();
  //if(isset($_SESSION['username'])) define('RATER', $_SESSION['username']);
  if($objLogin->IsLoggedIn()) define('RATER', $objLogin->GetLoggedName());
}

/* From Here no need to modify */
if(!headers_sent()) header('Content-type: text/html; charset=utf-8');      // header for utf-8

include('class.rating.php');        // Include Rating class
$obRtg = new Rating();

// if data from POST 'elm' and 'rate'
if(isset($_POST['elm']) && isset($_POST['rate'])) {
	
	// Field 'elem' validation
	if(!is_array($_POST['elm'])){
		$_POST['elm'] = array();
	}else{
		foreach($_POST['elm'] as $k => $v){
			if(strlen($v) <= 2 || strlen($v) > 20){
				$_POST['elm'] = array();
				break;
			}
		}
	}
	
	// removes tags and external whitespaces from 'elm'
	if($_POST['elm'])
	$_POST['elm'] = array_map('strip_tags', $_POST['elm']);
	$_POST['elm'] = array_map('trim', $_POST['elm']);
	$_POST['elm'] = array_map('prepare_input_alphanumeric', $_POST['elm']);
	if(!empty($_POST['rate'])) $_POST['rate'] = intval($_POST['rate']);
  
	echo $obRtg->getRating($_POST['elm'], $_POST['rate']);
}