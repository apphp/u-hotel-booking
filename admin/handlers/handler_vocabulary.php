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

if($objLogin->IsLoggedInAsAdmin()){

	$act 			= (!empty($_REQUEST['act'])) ? prepare_input($_REQUEST['act']) : '';
	$key 			= (!empty($_REQUEST['key'])) ? prepare_input($_REQUEST['key']) : '';
	$submition_type = (!empty($_POST['submition_type'])) ? prepare_input($_POST['submition_type']) : '';
	$txt_key        = (!empty($_POST['txt_key'])) ? prepare_input($_POST['txt_key'], false, 'low') : '';
	$txt_key_value  = (!empty($_POST['txt_key_value'])) ? prepare_input($_POST['txt_key_value'], false, 'low') : '';
	$language_id    = (!empty($_POST['language_id'])) ? prepare_input($_POST['language_id']) : '';
	$all_languages  = (isset($_POST['all_languages'])) ? true : false;
	$msg            = '';
	
	$objVocabulary = new Vocabulary();
	
	if($submition_type == '1'){
		if($objVocabulary->UpdateKey($txt_key, $txt_key_value)){			
			$objVocabulary->RewriteVocabularyFile(false);
			$objSession->SetMessage('notice', draw_success_message(_VOC_KEY_UPDATED, false));
			redirect_to('index.php?admin=vocabulary&key='.$key.$objVocabulary->GetFilterURL().$objVocabulary->GetLanguageURL());			
		}
	}

} 
