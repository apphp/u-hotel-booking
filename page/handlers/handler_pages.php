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

$curr_page_id   = (Application::Get('system_page') == '') ? Application::Get('page_id') : Application::Get('system_page');
$field_name     = (Application::Get('system_page') == '') ? 'id' : 'system_page';
$mg_language_id = isset($_REQUEST['mg_language_id']) ? prepare_input($_REQUEST['mg_language_id']) : Application::Get('lang');

$new_page_id    = Pages::GetPageId($curr_page_id, $mg_language_id, $field_name);    
$field_from     = (Application::Get('system_page') == '') ? 'pid='.$curr_page_id : 'system_page='.$curr_page_id;
$field_to       = (Application::Get('system_page') == '') ? 'pid='.$new_page_id : 'system_page='.$new_page_id;
$seo_field_from = (Application::Get('system_page') == '') ? '/'.$curr_page_id.'/' : '/'.$curr_page_id.'.';
$seo_field_to   = (Application::Get('system_page') == '') ? '/'.$new_page_id.'/' : '/'.$new_page_id.'.';

if(!empty($new_page_id) && $curr_page_id != $new_page_id){
    $url = get_page_url(false);

    if($objSettings->GetParameter('seo_urls') == '1'){
        $url = str_replace($seo_field_from, $seo_field_to, $url);						
    }else{
        $url = str_replace($field_from, $field_to, $url);        
    }    
    
    if(Application::Get('preview') != 'yes'){
        redirect_to($url);
    }
}elseif(empty($new_page_id)){ 
    $objSession->SetMessage('notice', draw_important_message(_PAGE_UNKNOWN, false));    
}

