<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

@session_start();

// Important includes
//------------------------------------------------------------------------------
require_once('shared.inc.php');
require_once('settings.inc.php');
require_once('functions.validation.inc.php');
require_once('functions.common.inc.php');
require_once('functions.html.inc.php');

// Start profiler
profiler_start();

require_once('functions.database.'.(DB_TYPE == 'PDO' ? 'pdo.' : 'mysqli.').'inc.php');

// Set base URL
define('APPHP_BASE', get_base_url());

if(defined('APPHP_CONNECT') && APPHP_CONNECT == 'direct'){	
	// Set time zone
	//------------------------------------------------------------------------------
	@date_default_timezone_set(TIME_ZONE);
	
	Modules::Init();
	ModulesSettings::Init();

	// Create main objects
	//------------------------------------------------------------------------------
	$objSession  = new Session();
	$objLogin    = new Login();
	$objSettings = new Settings();
	
    $lang_file = $objSession->GetSessionVariable('lang');
    if(empty($lang_file)){
        // use messages file according to preferences
        $lang_file = $objLogin->GetPreferredLang();
        if(empty($lang_file)){
            $lang_file = Languages::GetDefaultLang();
        }
    }
    include_once('messages'.($lang_file != '' ? '.'.$lang_file : '').'.inc.php');
	
}else{
	// Set timezone
	//------------------------------------------------------------------------------
	Settings::SetTimeZone();	
	Modules::Init();
	ModulesSettings::Init();

	// Create main objects
	//------------------------------------------------------------------------------
	$objSession 		= new Session();
	$objLogin 			= new Login();
	$objSettings 		= new Settings();
	$objSiteDescription = new SiteDescription();
	Application::Init();
	Languages::Init();
	
	// Force SSL mode if defined
	//------------------------------------------------------------------------------
	$ssl_mode = $objSettings->GetParameter('ssl_mode');
	$ssl_enabled = false; 
	if($ssl_mode == '1'){
		$ssl_enabled = true; 
	}elseif($ssl_mode == '2' && $objLogin->IsLoggedInAsAdmin()){
		$ssl_enabled = true; 
	}elseif($ssl_mode == '3' && $objLogin->IsLoggedInAsCustomer()){
		$ssl_enabled = true; 
	}
	if($ssl_enabled && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') && isset($_SERVER['HTTP_HOST'])){ 
		redirect_to('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); 
	}
	
	// Include files for administrator use only
	//------------------------------------------------------------------------------
	if($objLogin->IsLoggedInAsAdmin()){
		include_once('functions.admin.inc.php');
	}
	
	// Include language file
	//------------------------------------------------------------------------------
	if(!defined('APPHP_LANG_INCLUDED')){
		if(get_os_name() == 'windows'){
			$lang_file_path = str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']).'include/messages.'.Application::Get('lang').'.inc.php';
		}else{
			$lang_file_path = 'include/messages.'.Application::Get('lang').'.inc.php';
		}
		if(file_exists($lang_file_path)){
			include_once($lang_file_path);
		}elseif(file_exists('include/messages.inc.php')){
			include_once('include/messages.inc.php');
		}
	}
	
	// Include files for custom template
	//------------------------------------------------------------------------------
	if(file_exists('templates/'.Application::Get('template').'/lib/functions.template.php')){
		include_once('templates/'.Application::Get('template').'/lib/functions.template.php');
	}
	
	// *** Run cron jobs file
    // -----------------------------------------------------------------------------
    if($objSettings->GetParameter('cron_type') == 'non-batch'){
        if(file_exists('cron.php')) include_once('cron.php');		
    }
}
