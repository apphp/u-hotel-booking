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

	if(Modules::IsModuleInstalled('reviews')){
		
		$action 		= MicroGrid::GetParameter('action');
		$rid    		= MicroGrid::GetParameter('rid');
		$mode   		= 'view';
		$msg 			= '';
		
		$objReviews = new Reviews($objLogin->GetLoggedID());
		
		if($action != 'add' || !$objReviews->error){
			if($action=='add'){		
				$mode = 'add';
			}elseif($action=='create'){
				if($objReviews->AddRecord()){
					$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
					$mode = 'view';
				}else{
					$msg = draw_important_message($objReviews->error, false);
					$mode = 'add';
				}
			}elseif($action=='edit'){
				$mode = 'edit';
			}elseif($action=='update'){
				if($objReviews->UpdateRecord($rid)){
					$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
					$mode = 'view';
				}else{
					$msg = draw_important_message($objReviews->error, false);
					$mode = 'edit';
				}		
			}elseif($action=='delete'){
				if($objReviews->DeleteRecord($rid)){
					$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
				}else{
					$msg = draw_important_message($objReviews->error, false);
				}
				$mode = 'view';
			}elseif($action=='details'){		
				$mode = 'details';		
			}elseif($action=='cancel_add'){		
				$mode = 'view';		
			}elseif($action=='cancel_edit'){				
				$mode = 'view';
			}
		}else{
			$msg = draw_important_message($objReviews->error, false);
		}
			
		// Start main content
		draw_title_bar(
			prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_REVIEWS=>'',ucfirst($action)=>''))
		);
			
		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		echo $msg;
		
		//draw_content_start();
		echo '<div id="divMyReviews">';
		if($mode == 'view'){			
			$objReviews->DrawViewMode(true);
		}elseif($mode == 'add'){		
			$objReviews->DrawAddMode(array('cancel'=>true), $swipe = false);		
		}elseif($mode == 'edit'){		
			$objReviews->DrawEditMode($rid, array('reset'=>false, 'cancel'=>true), true);
		}elseif($mode == 'details'){		
			$objReviews->DrawDetailsMode($rid);		
		}	
		//draw_content_end();		
		echo '</div><br><br>';
	}else{
		draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
		draw_important_message(_NOT_AUTHORIZED);
	}
}elseif($objLogin->IsLoggedIn()){
	draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
	draw_important_message(_NOT_AUTHORIZED);
}else{
	draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
	draw_important_message(_MUST_BE_LOGGED);
}
