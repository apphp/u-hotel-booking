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

if($objLogin->IsLoggedInAsAdmin() && $objLogin->HasPrivileges('add_menus')){

	$act   = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
	$mid   = isset($_POST['mid']) ? (int)$_POST['mid'] : '';
	$language_id = (isset($_POST['language_id'])) ? prepare_input($_POST['language_id']) : Languages::GetDefaultLang();
	$menu_placement = (isset($_POST['menu_placement'])) ? prepare_input($_POST['menu_placement']) : '';
	$menu  = new Menu($mid);
	$msg   = '';
	
	// add new menu catagory
	if ($act == 'add'){
		$params = array();		
		$params['name']           = (isset($_POST['name'])) ? prepare_input($_POST['name']) : '';
		$params['menu_placement'] = (isset($_POST['menu_placement'])) ? prepare_input($_POST['menu_placement']) : '';
		$params['order'] 		  = (isset($_POST['order'])) ? (int)$_POST['order'] : '';
		$params['language_id']    = (isset($_POST['language_id'])) ? prepare_input($_POST['language_id']) : '';
		$params['access_level']   = (isset($_POST['access_level'])) ? prepare_input($_POST['access_level']) : '';
		
		if($menu->MenuCreate($params)) {
			$msg = draw_success_message(_MENU_CREATED, false);
			$objSession->SetMessage('notice', $msg);
			redirect_to('index.php?admin=menus');
		}else{
			$msg = draw_important_message($menu->error, false);
		}
	}

	if($msg == ''){
		$msg = draw_message(_ALERT_REQUIRED_FILEDS.'<br>'._MENUS_DISABLED_ALERT, false);
	}

}

