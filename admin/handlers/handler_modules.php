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

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$mode   	= 'view';
	$msg 		= '';
	
	$objModules = new Modules();

	if($action=='add'){		
		$mode = 'view';
	}elseif($action=='create'){
		$mode = 'view';
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objModules->UpdateRecord($rid)){
			$mst_text = ($objModules->error != '') ? $objModules->error : _UPDATING_OPERATION_COMPLETED;
			$msg = draw_success_message($mst_text, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objModules->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'view';
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
	}

}

