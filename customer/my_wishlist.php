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

if($objLogin->IsLoggedInAsCustomer()){

	$action 		= MicroGrid::GetParameter('action');
	$rid    		= MicroGrid::GetParameter('rid');
	$mode   		= 'view';
	$msg 			= '';
	
	$objWishlist = new Wishlist($objLogin->GetLoggedID());
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objWishlist->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objWishlist->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objWishlist->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objWishlist->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objWishlist->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objWishlist->error, false);
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
		prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_WISHLIST=>'',ucfirst($action)=>''))
	);
		
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;
	
	//draw_content_start();
	echo '<div id="divMyWishlist">';
	if($mode == 'view'){			
		$objWishlist->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objWishlist->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objWishlist->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objWishlist->DrawDetailsMode($rid);		
	}	
	//draw_content_end();		
	echo '</div><br><br>';

}elseif($objLogin->IsLoggedIn()){
	draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
	draw_important_message(_NOT_AUTHORIZED);
}else{
	draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
	draw_important_message(_MUST_BE_LOGGED);
}
