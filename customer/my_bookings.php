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

	if(Modules::IsModuleInstalled('booking') &&
	   in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'front-end'))
	){
		
		$action 		= MicroGrid::GetParameter('action');
		$rid    		= MicroGrid::GetParameter('rid');
		$mode   		= 'view';
		$msg 			= '';
		$view_allowed 	= true;
		$cancel_reservation_days = ModulesSettings::Get('booking', 'customers_cancel_reservation');	
		$customers_cancel_reservation = $cancel_reservation_days > 0 ? true : false;
		
		$objBookings = new Bookings($objLogin->GetLoggedID());
		
		// Customer access validation
		if(!empty($rid)){
			$reservation = $objBookings->GetInfoByID($rid);
			if(!empty($reservation)){
				if(!isset($reservation['customer_id']) || $reservation['customer_id'] != $objLogin->GetLoggedID()){
					$view_allowed = false;
				}

				$time_canceled_date = strtotime($reservation['cancel_payment_date']);
				if($time_canceled_date !== false){
					$customers_cancel_reservation = ($time_canceled_date - mktime(0, 0, 0, date('m'), date('d'), date('Y'))) > 0 ? true : false;
				}
			}
		}


		if($view_allowed){
			if($action=='add'){		
				$mode = 'add';
			}elseif($action=='create'){
				if($objBookings->AddRecord()){
					$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
					$mode = 'view';
				}else{
					$msg = draw_important_message($objBookings->error, false);
					$mode = 'add';
				}
			}elseif($action=='edit'){
				$mode = 'edit';
			}elseif($action=='update'){
				if($objBookings->UpdateRecord($rid)){
					$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
					$mode = 'view';
				}else{
					$msg = draw_important_message($objBookings->error, false);
					$mode = 'edit';
				}		
			}elseif($action=='delete' && $customers_cancel_reservation){
				if($objBookings->DeleteRecord($rid)){
					$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
				}else{
					$msg = draw_important_message($objBookings->error, false);
				}
				$mode = 'view';
			}elseif($action=='cancel'){
				if($customers_cancel_reservation){
					if($objBookings->CancelRecord($rid)){
						$msg  = draw_success_message(str_replace('_BOOKING_', '', _BOOKING_CANCELED_SUCCESS), false);
						// send email to customer about reservation cancelation
						$objReservation = new Reservation();
						if($objReservation->SendCancelOrderEmail($rid)){
							$msg .= draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
						}else{
							$msg .= draw_important_message($objReservation->error, false);
						}
					}else{
						$msg = draw_important_message($objBookings->error, false);
					}
				}else{
					$msg = draw_important_message(_YOU_CANNOT_CANCELED, false);
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
				prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_BOOKINGS_MANAGEMENT=>'',ucfirst($action)=>'')),
				(($mode == 'invoice' || $mode == 'description') ? '<a href="javascript:appPreview(\''.$mode.'\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>' : '')
			);
				
			//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
			echo $msg;
		
			//draw_content_start();
			echo '<div id="divMyBookings">';
			if($mode == 'view'){			
				echo '<script type="text/javascript">
					function __mgMyDoPostBack(tbl, type, key){
						if(confirm("'._ALERT_CANCEL_BOOKING.'")){
							__mgDoPostBack(tbl, type, key);
						}					
					}
				  </script>';
                if(file_exists('templates/'.Application::Get('template').'/lib/my_bookings.html_row_template.php')){
                    $template = include('templates/'.Application::Get('template').'/lib/my_bookings.html_row_template.php');
                    if(!empty($template)){
                        $objBookings->SetRowTemplate($template);
                    }
                }
				$objBookings->DrawViewMode(true);
			}elseif($mode == 'add'){		
				$objBookings->DrawAddMode();		
			}elseif($mode == 'edit'){		
				$objBookings->DrawEditMode($rid);		
			}elseif($mode == 'details'){		
				$objBookings->DrawDetailsMode($rid);		
			}elseif($mode == 'description'){		
				$objBookings->DrawBookingDescription($rid, '', true, true);		
			}elseif($mode == 'invoice'){		
				$objBookings->DrawBookingInvoice($rid);		
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
