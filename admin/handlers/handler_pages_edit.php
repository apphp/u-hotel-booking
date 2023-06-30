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

if($objLogin->IsLoggedInAsAdmin() && $objLogin->HasPrivileges('edit_pages')){

	$submit   = isset($_POST['subSavePage']) ? prepare_input($_POST['subSavePage']) : '';
	$act      = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
	$msg 	  = '';
	$language_id = isset($_REQUEST['language_id']) ? prepare_input($_REQUEST['language_id']) : Languages::GetDefaultLang();
	$meta_tags_status = isset($_POST['meta_tags_status']) ? prepare_input($_POST['meta_tags_status']) : 'closed';

	$objPage = new Pages(Application::Get('page_id'), false, $language_id);
	if(Application::Get('page_id') != 'home') {
		$wysiwyg_dir = Languages::GetLanguageDirection($objPage->GetParameter('language_id'));
	}else{
		$wysiwyg_dir = Languages::GetLanguageDirection($language_id);
	}	
	
	if($act == 'edit'){
		$params = array();

		if(isset($_POST['content_type'])) 	  $params['content_type'] = prepare_input($_POST['content_type']);
		if(isset($_POST['link_url']))     	  $params['link_url'] = prepare_input($_POST['link_url'], false, 'medium');
		if(isset($_POST['link_target']))      $params['link_target'] = prepare_input($_POST['link_target']);
		if(isset($_POST['is_published']))     $params['is_published'] = prepare_input($_POST['is_published']);
		if(isset($_POST['system_page']) && Application::Get('type') == 'system')  $params['system_page'] = prepare_input($_POST['system_page']);

		if(isset($_POST['tag_title']))   	  $params['tag_title'] = prepare_input($_POST['tag_title']);
		if(isset($_POST['tag_keywords']))     $params['tag_keywords'] = prepare_input($_POST['tag_keywords']);
		if(isset($_POST['tag_description']))  $params['tag_description'] = prepare_input($_POST['tag_description']);
		if(isset($_POST['comments_allowed'])) $params['comments_allowed'] = (int)$_POST['comments_allowed'];
		if(isset($_POST['show_in_search']))   $params['show_in_search'] = (int)$_POST['show_in_search'];
		if(isset($_POST['priority_order']))   $params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['access_level']))     $params['access_level'] = prepare_input($_POST['access_level']);

		if(isset($_POST['page_title'])) 	  $params['page_title'] = prepare_input($_POST['page_title']);
		if(isset($_POST['page_text']))  	  $params['page_text']  = prepare_input($_POST['page_text'], false, 'low');
		if(isset($_POST['menu_link']))  	  $params['menu_link']  = prepare_input($_POST['menu_link']);
		$params['menu_id']           		  = (isset($_POST['menu_id'])) ? prepare_input($_POST['menu_id']) : '0';
		$params['page_key'] 				  = create_seo_url(prepare_input($_POST['page_title']));
		$params['language_id']          	  = (isset($_POST['language_id'])) ? prepare_input($_POST['language_id']) : '';
		$params['finish_publishing'] 		  = (isset($_POST['finish_publishing']) && check_date($_POST['finish_publishing'])) ? prepare_input($_POST['finish_publishing']) : null;
		
		if($objPage->PageUpdate($params)){
			$msg = draw_success_message(_PAGE_SAVED, false);
			//if(Application::Get('page_id') != 'home'){
			//$objSession->SetMessage('notice', $msg);				
			///redirect_to('index.php?admin=pages'.((Application::Get('type') != '') ? '&type='.Application::Get('type'): '').'&mg_language_id='.$params['language_id']);
			///exit;
			//}
		}else{			
			$msg = draw_important_message($objPage->error, false);
		}
	}

	if($msg == ''){
		$msg = draw_message(_ALERT_REQUIRED_FILEDS, false);
	}

} 
