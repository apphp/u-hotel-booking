<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

//--------------------------------------------------------------------------
// *** Remote file inclusion, check for strange characters in $_GET keys
// *** All keys with "/", "\", ":" or "%-0-0" are blocked, so it becomes virtually impossible to inject other pages or websites
foreach($_GET as $get_key => $get_value){
    if(is_string($get_value) && (preg_match("/\//", $get_value) || preg_match("/\[\\\]/", $get_value) || preg_match("/:/", $get_value) || preg_match("/%00/", $get_value))){
        if(isset($_GET[$get_key])) unset($_GET[$get_key]);
        die("A hacking attempt has been detected. For security reasons, we're blocking any code execution.");
    }
}

// *** Check token for POST requests (CSRF validation)
$CSRF_VALIDATION = defined('CSRF_VALIDATION') ? CSRF_VALIDATION : false;
if($CSRF_VALIDATION && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
	
	$token_post = isset($_POST['token']) ? $_POST['token'] : 'post';
	$token_session = isset($_SESSION[INSTALLATION_KEY]['token']) ? $_SESSION[INSTALLATION_KEY]['token'] : 'session';
	
	// Check if there is exclusion and if exists - cancel CSRF validation
	if(isset($CSRF_VALIDATION_EXCLUDE) && is_array($CSRF_VALIDATION_EXCLUDE)){
		foreach($CSRF_VALIDATION_EXCLUDE as $key => $val){
			if(is_array($val)){
				foreach($val as $ikey => $ival){
					if(isset($_GET[$key]) && $_GET[$key] === $ival){
						$token_session = $token_post = '';
						break 2;
					}
				}
			}else{
				if(isset($_GET[$key]) && $_GET[$key] === $val){
					$token_session = $token_post = '';
					break;
				}				
			}
		}
	}

	if($token_session != $token_post){
		
		unset($_POST['submition_type']); // for settings page
										 //     vocabulary
										 //     backup
        unset($_POST['submit_type']);    // for Admin my_account page
										 //     backup installation
		unset($_REQUEST['mg_action']);   // for MicroGrid pages
		unset($_POST['task']);           // for room prices,
										 //	    room availability 
										 //     booking_payment
										 //     mass_mail
										 //     customer/confirm_registration
										 //     customer/my_account page
		unset($_POST['act']);            // for booking_details page
		                                 // 	menus
										 // 	pages
										 // 	languages
										 //     vocabulary
										 //     customer/create_account
										 //     customer/password_forgotten
        //unset($_POST['tabid']);        // for Tabs operations
	    //unset($_POST['submit_login']); // for login page
		//unset($_POST['sel_search_in']);// for search operations
	}
}

