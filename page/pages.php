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

$mg_language_id = isset($_REQUEST['mg_language_id']) ? prepare_input($_REQUEST['mg_language_id']) : Application::Get('lang');

if($objLogin->IsLoggedInAsAdmin() && Application::Get('preview') != 'yes'){
	$objPage = new Pages(Application::Get('page_id'), false, $mg_language_id);	
}else{
	$objPage = new Pages(((Application::Get('system_page') != '') ? Application::Get('system_page') : Application::Get('page_id')), true, $mg_language_id);			
}
$button_text = '';

// check if there is a page 
if($objSession->IsMessage('notice')){ 
	draw_title_bar(_PAGE);
	echo $objSession->GetMessage('notice');
}elseif($objPage->CheckAccessRights($objLogin->IsLoggedIn())){
	// check if there is a page 
	if($objPage->GetId() != ''){
		if($objLogin->IsLoggedInAsAdmin() && Application::Get('preview') != 'yes'){
			$button_text = prepare_permanent_link('index.php?admin=pages'.((Application::Get('type') == 'system') ? '&type=system' : '').'&mg_language_id='.$mg_language_id, _BUTTON_BACK);
		}
		$objPage->DrawTitle($button_text);
		$objPage->DrawText();
	}else{
		draw_title_bar(_PAGES);
		draw_important_message(_PAGE_UNKNOWN);		
	}
}else{
	draw_title_bar(_PAGE);
	draw_important_message(_MUST_BE_LOGGED);
}

