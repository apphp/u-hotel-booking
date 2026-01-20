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

if(Modules::IsModuleInstalled('car_rental') &&
	($objLogin->IsLoggedInAs('owner','mainadmin') ||
	($objLogin->IsLoggedInAs('agencyowner') && ModulesSettings::Get('car_rental', 'is_active') != 'no'))
){	

	$action 		 = MicroGrid::GetParameter('action');
	$title_action    = $action;
	$rid    		 = MicroGrid::GetParameter('rid');	

	$reservation_status  = MicroGrid::GetParameter('status', false);
	$reservation_number  = MicroGrid::GetParameter('reservation_number', false);
	$customer_id     = MicroGrid::GetParameter('customer_id', false);

	$mode = 'view';
	$msg = '';
	$links = '';
	
	$objCarRental = new CarRental();
	
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
			$objCarReservations = new CarReservations();
			if($reservation_status == '2'){
				// 2 - ORDER RESERVED - send email to customer
				$objCarReservations->SendOrderEmail($reservation_number, 'reserved', $customer_id);
			}elseif($reservation_status == '3'){
				// 3 - ORDER COMPLETED - send email to customer
				$objCarRental->UpdatePaymentDate($rid);
				$objCarReservations->SendOrderEmail($reservation_number, 'completed', $customer_id);
			}elseif($reservation_status == '4'){
				// 4 - REFUND - return rooms to search
				$objCarReservations->SendOrderEmail($reservation_number, 'refunded', $customer_id);
			}
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objCarRental->error, false);
			$mode = 'edit';
		}
	}elseif($action=='delete'){
		$allow_deleting = !$objLogin->HasPrivileges('delete_car_reservations') ? false : true;
		if($allow_deleting){
			if($objCarRental->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objCarRental->error, false);
			}
		}
		$mode = 'view';		
	}elseif($action=='cancel'){
		$allow_cancelation = !$objLogin->HasPrivileges('cancel_car_reservations') ? false : true;
		if($allow_cancelation){
			if($objCarRental->CancelRecord($rid)){
				$msg = draw_success_message(str_replace('_BOOKING_', '', _BOOKING_CANCELED_SUCCESS), false);
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
	}elseif($action=='download_invoice'){
		if(strtolower(SITE_MODE) == "demo"){
			$msg = draw_important_message(_OPERATION_BLOCKED, false);
		}else{		
			if($objCarRental->PrepareInvoiceDownload($rid)){
				$msg = draw_success_message($objCarRental->message, false);			
			}else{
				$msg = draw_important_message($objCarRental->error, false);
			}
		}
		$mode = 'view';
		$title_action = _DOWNLOAD_INVOICE;		
	}elseif($action=='send_invoice'){
		if($objCarRental->SendInvoice($rid)){
			$msg = draw_success_message(_INVOICE_SENT_SUCCESS, false);
		}else{
			$msg = draw_important_message($objCarRental->error, false);
		}
		$mode = 'view';
		$title_action = _SEND_INVOICE;
	}elseif($action=='clean_credit_card'){				
		if($objCarRental->CleanUpCreditCardInfo($rid)){
			$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
		}else{
			$msg = draw_important_message($objCarRental->error, false);
		}
		$mode = 'view';
		$title_action = 'Clean';
	}elseif($action=='cleanup_reservation'){				
		if($objCarRental->CleanUpReservation($rid)){
			$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
		}else{
			$msg = draw_important_message($objCarRental->error, false);
		}
		$mode = 'view';
		$title_action = _CLEANUP;
	}
	
	// Start main content
	if($mode == 'invoice'){
		$links .= '<a href="javascript:void(\'invoice|send\')" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_car_rental_reservations\', \'&mg_action=send_invoice&mg_rid='.$rid.'&token='.Application::Get('token').'\', \'post\');"><img src="images/mail.png" alt="" /> '._SEND_INVOICE.'</a> &nbsp;|&nbsp; ';
		$links .= '<a href="javascript:void(\'invoice|download\')" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_car_rental_reservations\', \'&mg_action=download_invoice&mg_rid='.$rid.'&token='.Application::Get('token').'\', \'post\');"><img src="images/pdf.png" alt="" /> '._DOWNLOAD_INVOICE.'</a> &nbsp;|&nbsp; ';
		$links .= '<a href="javascript:void(\'invoice|preview\')" onclick="javascript:appPreview(\'invoice\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>';
	}elseif($mode == 'description'){
		$links .= '<a href="javascript:void(\'description|preview\')" onclick="javascript:appPreview(\'description\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>';
	}
	draw_title_bar(
		prepare_breadcrumbs(array(_CAR_RENTAL=>'',_CAR_RESERVATIONS=>'',_RESERVATIONS=>'',ucfirst($title_action)=>'')),
		$links		
	);
    	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	$allow_viewing = true;
	if($objLogin->IsLoggedInAs('agencyowner')){
		$hotels_list = implode(',', $objLogin->AssignedToCarAgencies());
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(__CAR_OWNER_NOT_ASSIGNED, false);
		}
	}
	
	if($allow_viewing){
		if($mode == 'view'){
			$objCarRental->DrawOperationLinks(prepare_permanent_link('javascript:void(\'cleaup\')', '[ '._CLEANUP.' ]', '', '', '', 'onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_car_rental_reservations\', \'&mg_action=cleanup_reservation&token='.Application::Get('token').'\', \'post\');"').' <img class="help" src="images/question_mark.png" title="'._CLEANUP_TOOLTIP.'" />');		
			$objCarRental->DrawViewMode();
			echo '<script type="text/javascript">
					function __mgMyDoPostBack(tbl, type, key){
						if(confirm("'._ALERT_CANCEL_BOOKING.'")){
							__mgDoPostBack(tbl, type, key);
						}					
					}
				  </script>';
			
			echo '<fieldset class="instructions" style="margin-top:10px;">
					<legend>Legend: </legend>
					<div style="padding:10px;">
						<span style="color:#222222">'._PREORDERING.'</span> - '._LEGEND_PREORDERING.'<br>
						<span style="color:#0000a3">'._PENDING.'</span> - '._LEGEND_CAR_PENDING.'<br>
						<span style="color:#a3a300">'._RESERVED.'</span> - '._LEGEND_CAR_RESERVED.'<br>
						<span style="color:#00a300">'._COMPLETED.'</span> - '._LEGEND_COMPLETED.'<br>
						<span style="color:#660000">'._REFUNDED.'</span> - '._LEGEND_CAR_REFUNDED.'<br>
						<span style="color:#a30000">'._PAYMENT_ERROR.'</span> - '._LEGEND_PAYMENT_ERROR.'<br>
						<span style="color:#939393">'._CANCELED.'</span> - '._LEGEND_CAR_CANCELED.'<br>
					</div>
				</fieldset>';
			
		}elseif($mode == 'add'){		
			$objCarRental->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objCarRental->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objCarRental->DrawDetailsMode($rid);		
		}elseif($mode == 'description'){
			$objCarRental->DrawBookingDescription($rid);		
		}elseif($mode == 'invoice'){		
			$objCarRental->DrawCarRentalInvoice($rid);		
		}	
	}
	
	draw_content_end();	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

