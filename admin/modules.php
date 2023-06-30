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

if($objLogin->IsLoggedInAs('owner','mainadmin')){
	
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_MODULES_MANAGEMENT=>'',ucfirst($action)=>'')));
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	if($objModules->modulesCount <= 0){
		$msg = draw_important_message(_MODULES_NOT_FOUND, false);
	}
	
	if($objSession->IsMessage('notice')){
		echo $objSession->GetMessage('notice');
	}else{
		echo $msg;	
	}	

	$action = MicroGrid::GetParameter('action', false);
	$module = MicroGrid::GetParameter('module', false);
	
	draw_content_start();	

	if(!empty($module)){
		if($action == 'install'){
			$objModules->InstallAdditionalModule($module);	
		}elseif($action == 'uninstall'){
			$objModules->UninstallAdditionalModule($module);	
		}		
	}
	
	if($mode == 'view'){		
		$objModules->DrawModules();
	}elseif($mode == 'add'){		
		$objModules->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objModules->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objModules->DrawDetailsMode($rid);		
	}
	
	draw_content_end();
	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

