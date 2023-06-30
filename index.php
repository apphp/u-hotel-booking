<?php
################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 #
## --------------------------------------------------------------------------- #
##  uHotelBooking  version 2.8.9                                               #
##  Developed by:  ApPHP <info@apphp.com>                                      #
##  License:       ApPHP uHotelBooking license v.1                             #
##  Site:          https://www.hotel-booking-script.com                        #
##  Copyright:     uHotelBooking (c) 2014 - 2017. All rights reserved.         #
##                                                                             #
##  Additional modules (embedded):                                             #
##  -- ApPHP EasyInstaller v2.0.5 (installation module)      https://apphp.com #
##  -- ApPHP Tabs v2.0.3 (tabs menu control)        		 https://apphp.com #
##  -- TinyMCE (WYSIWYG editor)                   http://tinymce.moxiecode.com #
##  -- Crystal Project Icons (icons set)               http://www.everaldo.com #
##  -- Securimage v3.6.4 (captcha script)            http://www.phpcaptcha.org #
##  -- jQuery 1.4.2 (New Wave Javascript)             		 http://jquery.com #
##  -- Google AJAX Libraries API                  http://code.google.com/apis/ #
##  -- Lytebox v3.22                                       http://lytebox.com/ #
##  -- JsCalendar v1.0 (DHTML/JavaScript Calendar)      http://www.dynarch.com #
##  -- CrossSlide jQuery plugin v0.6.2 	                     by Tobia Conforto #
##  -- PHPMailer v5.2 https://code.google.com/a/apache-extras.org/p/phpmailer/ #
##  -- tFPDF v1.24 (PDF files generator (FPDF http://fpdf.org))    by Ian Back #
##  -- Ajax-PHP Rating Stars Script                     http://coursesweb.net/ #
##                                                                             #
################################################################################

// *** Check if database connection parameters file exists
if(!file_exists('include/base.inc.php')){
	header('location: install.php');
	exit;
}

## uncomment, if your want to prevent 'Web Page exired' message when use $submission_method = 'post';
//session_cache_limiter('private, must-revalidate');    

// *** Set flag that this is a parent file
define('APPHP_EXEC', 'access allowed');
// *** Set CSRF validation
defined('CSRF_VALIDATION') or define('CSRF_VALIDATION', true); 
// *** Set CSRF validation exclusions
// Ex.: 'key' => array('val1', 'val2')
// Ex.: array('admin' => array('login', 'my_account'), 'page' => array('faq', 'cms'))
// $CSRF_VALIDATION_EXCLUDE = array();

require_once('include/base.inc.php');

try{

	require_once('include/connection.php');

	// *** Call handler if exists
	// -----------------------------------------------------------------------------
	if(Application::Get('page') != '' && file_exists('page/handlers/handler_'.Application::Get('page').'.php')){
		include_once('page/handlers/handler_'.Application::Get('page').'.php');
	}elseif(preg_match('/check\_/i', Application::Get('page')) && !file_exists('page/handlers/handler_'.Application::Get('page').'.php')){
		$page_types = explode('_', Application::Get('page'));
		$page_type = isset($page_types[1]) ? $page_types[1] : '';
		if(in_sub_array('property_code', $page_type, Application::Get('property_types'))){
			include_once('page/handlers/handler_properties.php');
		}
	}elseif((Application::Get('customer') != '') && file_exists('customer/handlers/handler_'.Application::Get('customer').'.php')){
		if(Modules::IsModuleInstalled('customers')){	
			include_once('customer/handlers/handler_'.Application::Get('customer').'.php');
		}
	}elseif((Application::Get('admin') != '') && file_exists('admin/handlers/handler_'.Application::Get('admin').'.php')){
		include_once('admin/handlers/handler_'.Application::Get('admin').'.php');
	}elseif((Application::Get('admin') == 'export') && file_exists('admin/downloads/export.php')){
		include_once('admin/downloads/export.php');
	}elseif((Application::Get('mobile') != '') && file_exists('customer/handlers/handler_'.Application::Get('mobile').'.php')){
		include_once('mobile/handlers/handler_'.Application::Get('mobile').'.php');
	}
	
	// *** Channel manager route
	// -----------------------------------------------------------------------------
	if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){
		require_once('modules/additional/channel_manager/listener.php');	
	}
	
	// *** Get site content
	// -----------------------------------------------------------------------------
	if(!preg_match('/(booking|cars)\_notify\_/i', str_replace('_', '\_', Application::Get('page')))){
		$cachefile = '';
		if($objSettings->GetParameter('caching_allowed') && !$objLogin->IsLoggedIn()){
			$c_page        = Application::Get('page');
			$c_page_id     = Application::Get('page_id');
			$c_system_page = Application::Get('system_page');
			$c_album_code  = Application::Get('album_code');
			$c_news_id     = Application::Get('news_id');
			$c_customer    = Application::Get('customer');
			$c_mobile      = Application::Get('mobile');
			$c_admin       = Application::Get('admin');
	
			if(($c_page == '' && $c_mobile == ''  && $c_customer == '' && $c_admin == '') ||
			   ($c_page == 'pages' && $c_page_id != '') || 
			   ($c_page == 'news' && $c_news_id != '') ||
			   ($c_page == 'gallery' && $c_album_code != '')
			   )
			{	
				$cachefile = md5($c_page.'-'.
								 $c_page_id.'-'.
								 $c_system_page.'-'.
								 $c_album_code.'-'.
								 $c_news_id.'-'.
								 Application::Get('lang').'-'.
								 Application::Get('currency_code')).'.cch';	
				if($c_page == 'news' && $c_news_id != ''){
					if(!News::CacheAllowed($c_news_id)) $cachefile = '';
				}else{
					$objTempPage = new Pages((($c_system_page != '') ? $c_system_page : $c_page_id));
					if(!$objTempPage->CacheAllowed()) $cachefile = '';			
				}			
				if(start_caching($cachefile)) exit;
			}
		}
		
		require_once('templates/'.Application::Get('template').'/default.php');
		if($objSettings->GetParameter('caching_allowed') && !$objLogin->IsLoggedIn()) finish_caching($cachefile);
	}
	
	Application::DrawPreview();
	
	echo "\n".'<!-- This page was generated by uHotelBooking v'.CURRENT_VERSION.' (https://www.hotel-booking-script.com) -->';

	require_once('include/disconnection.php');

}catch(Exception $e){
	echo backtrace($e->getMessage(), $e->getCode(), $e->getTrace());
}
