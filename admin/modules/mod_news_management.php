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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('news')){	

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';	

	$objNews = News::Instance();
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objNews->AddRecord()){
			if(ModulesSettings::Get('news', 'news_rss') == 'yes'){
				$rss_result = RSSFeed::UpdateFeeds();			
			}
			$msg .= draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			if(!empty($rss_result)) $msg .= draw_important_message($rss_result, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objNews->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objNews->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objNews->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objNews->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objNews->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_NEWS=>'',_NEWS_MANAGEMENT=>'',ucfirst($action)=>'')));

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objNews->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objNews->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objNews->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objNews->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

