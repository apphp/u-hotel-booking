<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if($objLogin->IsLoggedInAsCustomer()){

	if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
		
		$action 		= MicroGrid::GetParameter('action');
		$rid    		= MicroGrid::GetParameter('rid');
		$mode   		= 'view';
		$msg 			= '';
		$view_allowed 	= true;
		$customers_cancel_reservation = ModulesSettings::Get('car_rental', 'customers_cancel_reservation');	
		
		$objCarRental = new CarRental($objLogin->GetLoggedID());
		
		// Customer access validation
		if(!empty($rid)){
			$reservation = $objCarRental->GetInfoByID($rid);
			if(!isset($reservation['customer_id']) || $reservation['customer_id'] != $objLogin->GetLoggedID()){
				$view_allowed = false;
			}
		}
		
		if($view_allowed){
			if($action=='add'){		
				$mode = 'add';
			}elseif($action=='create'){
				if($objCarRental->AddRecord()){
					$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
					$mode = 'view';
				}else{
					$msg = draw_important_message($objCarRental->error, false);
					$mode = 'add';
				}
			}elseif($action=='edit'){
				$mode = 'edit';
			}elseif($action=='update'){
				if($objCarRental->UpdateRecord($rid)){
					$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
					$mode = 'view';
				}else{
					$msg = draw_important_message($objCarRental->error, false);
					$mode = 'edit';
				}		
			//}elseif($action=='delete' && $customers_cancel_reservation > '0'){
			//	if($objCarRental->DeleteRecord($rid)){
			//		$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			//	}else{
			//		$msg = draw_important_message($objCarRental->error, false);
			//	}
			//	$mode = 'view';
			}elseif($action=='cancel' && $customers_cancel_reservation > '0'){
				if($objCarRental->CancelRecord($rid)){
					$msg  = draw_success_message(str_replace('_BOOKING_', '', _BOOKING_CANCELED_SUCCESS), false);
					// send email to customer about reservation cancelation
					$objCarReservations = new CarReservations();
					if($objCarReservations->SendCancelOrderEmail($rid)){
						$msg .= draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
					}else{
						$msg .= draw_important_message($objCarReservations->error, false);
					}			
				}else{
					$msg = draw_important_message($objCarRental->error, false);
				}
				$mode = 'view';
			}elseif($action=='details'){		
				$mode = 'details';		
			}elseif($action=='cancel_add'){		
				$mode = 'view';		
			}elseif($action=='cancel_edit'){				
				$mode = 'view';
			}elseif($action=='description'){				
				$mode = 'description';
			}elseif($action=='invoice'){				
				$mode = 'invoice';
			}
			
			// Start main content
			draw_title_bar(
				prepare_breadcrumbs(array(_CAR_RESERVATIONS=>'',_RESERVATIONS=>'',ucfirst($action)=>'')),
				(($mode == 'invoice' || $mode == 'description') ? '<a href="javascript:appPreview(\''.$mode.'\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>' : '')
			);
				
			//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
			echo $msg;
		
			//draw_content_start();
			echo '<div id="divMyCarRental">';
			if($mode == 'view'){			
				echo '<script type="text/javascript">
					function __mgMyDoPostBack(tbl, type, key){
						if(confirm("'._ALERT_CANCEL_BOOKING.'")){
							__mgDoPostBack(tbl, type, key);
						}					
					}
				  </script>';
				$objCarRental->DrawViewMode();	
			}elseif($mode == 'add'){		
				$objCarRental->DrawAddMode();		
			}elseif($mode == 'edit'){		
				$objCarRental->DrawEditMode($rid);		
			}elseif($mode == 'details'){		
				//$objCarRental->DrawDetailsMode($rid);		
			}elseif($mode == 'description'){		
				$objCarRental->DrawBookingDescription($rid);		
			}elseif($mode == 'invoice'){		
				$objCarRental->DrawCarRentalInvoice($rid);		
			}	
			//draw_content_end();		
			echo '</div>';
		}else{
			draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
			draw_important_message(_NOT_AUTHORIZED);
		}
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
