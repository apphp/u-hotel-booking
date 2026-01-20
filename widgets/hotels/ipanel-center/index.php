<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

define('APPHP_EXEC', 'access allowed');
define('IS_WIDGET', 1);
define('CSRF_VALIDATION', false);

$basedir = '../../../';

require_once($basedir.'include/base.inc.php');
require_once($basedir.'include/shared.inc.php');
require_once($basedir.'include/settings.inc.php');
require_once($basedir.'include/functions.common.inc.php');
require_once($basedir.'include/functions.database.'.(DB_TYPE == 'PDO' ? 'pdo.' : 'mysqli.').'inc.php');
require_once($basedir.'include/functions.html.inc.php');
require_once($basedir.'include/functions.validation.inc.php');
require_once($basedir.'modules/calendar/calendar.class.php');
require_once('functions.inc.php');

// Start profiler
if(SITE_MODE == 'development'){
	profiler_start();
}


$key = isset($_GET['key']) ? prepare_input(base64_decode($_GET['key']), true) : '';
if($key != INSTALLATION_KEY) exit(0);

$host = isset($_GET['host']) && validation_url(urldecode(base64_decode($_GET['host']))) ? urldecode(base64_decode($_GET['host'])) : '';
$host = rtrim($host, '/').'/';
$homeurl = $host.'widgets/hotels/ipanel-center/';

// Prepare hotel IDs
$input_hids = isset($_GET['hids']) ? str_ireplace('-', ',', base64_decode($_GET['hids'])) : '';
// Check input param
$arr_hids = explode(',', $input_hids);
$arr_check_hids = array();
foreach($arr_hids as $hotel_id){
	$hotel_id = (int)$hotel_id;
	if($hotel_id != 0){
		$arr_check_hids[] = $hotel_id;
	}
}
$hids = implode(',', $arr_check_hids);

// Prepare input params
$layout_full      = isset($_GET['full']) && base64_decode($_GET['full'])  == 'false'?  false : true;
$layout_type      = isset($_GET['layout']) && base64_decode($_GET['layout'])  == 'layout-2'?  'layout-2' : 'layout-1';
$type_search_form = isset($_GET['type']) && base64_decode($_GET['type']) == 'horizontal' ? 'horizontal' : 'vertical';
$background_name  = isset($_GET['bkg']) ? urldecode(base64_decode($_GET['bkg'])) : '';
$css_name         = isset($_GET['css']) ? urldecode(base64_decode($_GET['css'])) : '';
$default_lang	  = isset($_GET['d_lang']) ? prepare_input(base64_decode($_GET['d_lang']), true) : '';
$default_lang	  = strlen($default_lang) == 2 ? $default_lang : '';
$lang			  = isset($_GET['lang']) ? prepare_input($_GET['lang'], true) : '';

$background_full_path = '';
$css_full_file = '';

// Check backgrond
if(!empty($background_name) && is_string($background_name) && check_exist_file($background_name, __DIR__.'/template/images/background/', 'image')){
	$background_full_path = $homeurl.'template/images/background/'.$background_name;
}else{
	$background_name = improve_url($background_name);
   	if(!empty($background_name) && validation_url($background_name)){
		$background_full_path = $background_name;
	}
}

// Check css
if(!empty($css_name) && is_string($css_name) && check_exist_file($css_name, __DIR__.'/template/css/')){
	$css_full_file = $homeurl.'template/css/'.$css_name;
}else{
	$css_name = improve_url($css_name);
   	if(!empty($css_name) && validation_url($css_name)){
		$css_full_file = $css_name;
	}
}
	
define('WIDGET_ONE_HOTEL', count($arr_check_hids) == 1 ? true : false);
define('WIDGET_HOTEL_IDS', $hids);
define('WIDGET_HOME_URL', $homeurl);
define('WIDGET_HOST', $host);
define('WIDGET_KEY', $key);
define('WIDGET_HASH', 'host='.base64_encode($host).'&key='.base64_encode($key).'&hids='.base64_encode($hids).'&layout='.base64_encode($layout_type).'&type='.base64_encode($type_search_form).'&bkg='.base64_encode($background_full_path).'&css='.base64_encode($css_full_file).'&d_lang='.base64_encode($default_lang).'&lang='.base64_encode($lang));
define('WIDGET_LAYOUT_FULL', $layout_full);
define('WIDGET_LAYOUT', $layout_type);
define('WIDGET_TYPE_FORM', $type_search_form);
define('WIDGET_FILE_BG', $background_full_path);
define('WIDGET_FILE_CSS', $css_full_file);
define('CALENDAR_DIR', $basedir.'modules/calendar/');
define('CALENDAR_URL', $host.'modules/calendar/');


define('APPHP_BASE', get_base_url());

$objSession 		= new Session();
$objLogin 			= new Login();
$objSettings 		= new Settings();
$objSiteDescription = new SiteDescription();
Settings::SetTimeZone();	
Modules::Init();
ModulesSettings::Init();
Application::Init();
Languages::Init();

// Set Default Language
$default_lang = Languages::LanguageExists($default_lang) ? $default_lang : Languages::GetDefaultLang();
if(empty($_GET['lang'])){
	Application::Set('lang', $default_lang);
}

// Set Language
if(Session::Get('lang') == ''){
    Session::Set('lang', Application::Get('lang') != '' ? Application::Get('lang') : 'en');
}
// Set Hotel IDs
if(Session::Get('widget_hotel_ids') != $hids){
	Session::Set('widget_hotel_ids', $hids);
}

// Include language file
$lang = Application::Get('lang') != '' ? Application::Get('lang') : $default_lang;
$lang = Languages::LanguageExists($lang) ? $lang : 'en';
if(is_file($basedir.'include/messages.'.$lang.'.inc.php')){
	require_once($basedir.'include/messages.'.$lang.'.inc.php');
}else{
	require_once($basedir.'include/messages.inc.php');
}

$objReservation = new Reservation();

if((Application::Get('page') != '') && file_exists('page/handlers/handler_'.Application::Get('page').'.php')){
	include_once('page/handlers/handler_'.Application::Get('page').'.php');
}

require_once('template/default.php');

echo "\n".'<!-- This page was generated by uHotelBooking v'.CURRENT_VERSION.' (http://hotel-booking-script.com/) -->';

// Finish profiler
if(SITE_MODE == 'development'){
	profiler_finish();
}
