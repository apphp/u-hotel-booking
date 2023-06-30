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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('faq')){	

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$fcid   = MicroGrid::GetParameter('fcid' ,false);
	$mode   = 'view';
	$msg    = '';
	
	$objFaqCategories = new FaqCategories();
	$faq_info = $objFaqCategories->GetInfoByID($fcid, true);
	
	if(!empty($fcid) && count($faq_info) > 0){
		$objFaqCategoryItems = new FaqCategoryItems($fcid);
		
		if($action=='add'){		
			$mode = 'add';
		}elseif($action=='create'){
			if($objFaqCategoryItems->AddRecord()){
				$msg .= draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objFaqCategoryItems->error, false);
				$mode = 'add';
			}
		}elseif($action=='edit'){
			$mode = 'edit';
		}elseif($action=='update'){
			if($objFaqCategoryItems->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objFaqCategoryItems->error, false);
				$mode = 'edit';
			}		
		}elseif($action=='delete'){
			if($objFaqCategoryItems->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objFaqCategoryItems->error, false);
			}
			$mode = 'view';
		}elseif($action=='details'){		
			$mode = 'details';		
		}elseif($action=='cancel_add'){		
			$mode = 'view';		
		}elseif($action=='cancel_edit'){				
			$mode = 'view';
		}
		
		// Start main content
		draw_title_bar(
			prepare_breadcrumbs(array(_MODULES=>'',_FAQ=>'',_FAQ_MANAGEMENT=>'',$faq_info['name']=>'',ucfirst($action)=>'')),
			prepare_permanent_link('index.php?admin=mod_faq_management', _BUTTON_BACK)
		);
	
		echo $msg;
	
		draw_content_start();
		if($mode == 'view'){		
			$objFaqCategoryItems->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objFaqCategoryItems->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objFaqCategoryItems->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objFaqCategoryItems->DrawDetailsMode($rid);		
		}
		draw_content_end();		
	}else{
		draw_title_bar(
			prepare_breadcrumbs(array(_MODULES=>'',_FAQ_MANAGEMENT=>'',_QUESTIONS=>'')),
			prepare_permanent_link('index.php?admin=mod_faq_management', _BUTTON_BACK)
		);
		draw_important_message(_WRONG_PARAMETER_PASSED);
	}
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
