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

if($objLogin->IsLoggedInAs('owner','mainadmin','admin','hotelowner','regionalmanager') && Modules::IsModuleInstalled('booking')){

	$action 		 = MicroGrid::GetParameter('action');
	$title_action    = $action;
	$rid    		 = MicroGrid::GetParameter('rid');	

	$booking_status  = MicroGrid::GetParameter('status', false);
	$booking_number  = MicroGrid::GetParameter('booking_number', false);
	$customer_id     = MicroGrid::GetParameter('customer_id', false);
	$room_numbers    = MicroGrid::GetParameter('room_numbers', false);
	$comment         = MicroGrid::GetParameter('regional_menager_comment', false);
	$drid    	     = MicroGrid::GetParameter('rdid', false);
	$sel_extras      = MicroGrid::GetParameter('sel_extras', false);
	$extras_amount   = MicroGrid::GetParameter('extras_amount', false);

	$mode = 'view';
	$msg = '';
	$links = '';
	
	$objBookings = new Bookings();
	
	if($objLogin->IsLoggedInAs('owner','mainadmin','admin') ||
	   ($objLogin->IsLoggedInAs('hotelowner','regionalmanager') && ($rid == 0 || $objBookings->CheckRecordAssigned($rid)))
	){
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
				$objReservation = new Reservation();
				$additional_info_email = isset($_POST['additional_info_email']) ? prepare_input($_POST['additional_info_email']) : '';
				if($booking_status == '2'){
					// 2 - ORDER RESERVED - send email to customer
					$objReservation->SendOrderEmail($booking_number, 'reserved', $customer_id, $additional_info_email);
				}elseif($booking_status == '3'){
					// 3 - ORDER COMPLETED - send email to customer
					$objBookings->UpdatePaymentDate($rid);
					$objReservation->SendOrderEmail($booking_number, 'completed', $customer_id, $additional_info_email);
				}elseif($booking_status == '4'){
					// 4 - REFUND - return rooms to search
					$objReservation->SendOrderEmail($booking_number, 'refunded', $customer_id, $additional_info_email);
				}
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objBookings->error, false);
				$mode = 'edit';
			}
		}elseif($action=='delete'){
			$allow_deleting = !$objLogin->HasPrivileges('delete_bookings') ? false : true;
			if($allow_deleting){
				if($objBookings->DeleteRecord($rid)){
					$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
				}else{
					$msg = draw_important_message($objBookings->error, false);
				}
			}
			$mode = 'view';		
		}elseif($action=='cancel'){
            $allow_cancelation = !$objLogin->HasPrivileges('cancel_bookings') ? false : true;
            $booking_status = $objBookings->GetBookingStatus($rid);
			if($allow_cancelation && !in_array($booking_status, array(4,5,6))){
				if($objBookings->CancelRecord($rid)){			
					$msg = draw_success_message(str_replace('_BOOKING_', '', _BOOKING_CANCELED_SUCCESS), false);
					// send email to customer about reservation cancelation
					$objReservation = new Reservation();			
					if($objReservation->SendCancelOrderEmail($rid)){
						$msg .= draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
					}else{
						$msg .= ($objReservation->error != '') ? draw_important_message($objReservation->error, false) : '';
					}			
				}else{
					$msg = draw_important_message($objBookings->error, false);
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
		}elseif($action=='comment'){
			$mode =  $objLogin->IsLoggedInAs('regionalmanager') ? 'comment' : 'view';
		}elseif($action=='invoice'){				
			$mode = 'invoice';
		}elseif($action=='download_invoice'){
			if(strtolower(SITE_MODE) == "demo"){
				$msg = draw_important_message(_OPERATION_BLOCKED, false);
			}else{		
				if($objBookings->PrepareInvoiceDownload($rid)){
					$msg = draw_success_message($objBookings->message, false);			
				}else{
					$msg = draw_important_message($objBookings->error, false);
				}
			}
			$mode = 'view';
			$title_action = _DOWNLOAD_INVOICE;		
		}elseif($action=='send_invoice'){
			if($objBookings->SendInvoice($rid)){
				$msg = draw_success_message(_INVOICE_SENT_SUCCESS, false);
			}else{
				$msg = draw_important_message($objBookings->error, false);
			}
			$mode = 'view';
			$title_action = _SEND_INVOICE;
		}elseif($action=='clean_credit_card'){				
			if($objBookings->CleanUpCreditCardInfo($rid)){
				$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
			}else{
				$msg = draw_important_message($objBookings->error, false);
			}
			$mode = 'view';
			$title_action = 'Clean';
		}elseif($action=='cleanup_bookings'){				
			if($objBookings->CleanUpBookings($rid)){
				$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
			}else{
				$msg = draw_important_message($objBookings->error, false);
			}
			$mode = 'view';
			$title_action = _CLEANUP;
		}elseif($action=='update_room_numbers'){
			if($objBookings->UpdateRoomNumbers($drid, $room_numbers)){
				$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
			}else{
				$msg = draw_important_message($objBookings->error, false);
			}
			$mode = 'description';
			$title_action = _BUTTON_UPDATE;		
		}elseif($action=='update_comment' && $objLogin->IsLoggedInAs('regionalmanager')){
			if($objBookings->UpdateComment($rid, $comment)){
				$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
                $mode = 'view';
			}else{
				$msg = draw_important_message($objBookings->error, false);
                $mode = 'comment';
			}
			$title_action = _BUTTON_UPDATE;		
		}elseif($action=='add_extras'){
			if($objBookings->RecalculateExtras($rid, $sel_extras, $extras_amount, 'add')){
				$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
			}else{
				$msg = draw_important_message($objBookings->error, false);
			}
			$mode = 'description';
			$title_action = _BUTTON_UPDATE;
		}elseif($action=='remove_extras'){
			if($objBookings->RecalculateExtras($rid, $sel_extras, 0, 'remove')){
				$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
			}else{
				$msg = draw_important_message($objBookings->error, false);
			}
			$mode = 'description';
			$title_action = _BUTTON_UPDATE;
		}
	}else{
		$mode = 'view';	
	}	
	
	// Start main content
	if($mode == 'invoice'){
		$links .= '<a href="javascript:void(\'invoice|send\')" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_booking_bookings\', \'&mg_action=send_invoice&mg_rid='.$rid.'&token='.Application::Get('token').'\', \'post\');"><img src="images/mail.png" alt="" /> '._SEND_INVOICE.'</a> &nbsp;|&nbsp; ';
		$links .= '<a href="javascript:void(\'invoice|download\')" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_booking_bookings\', \'&mg_action=download_invoice&mg_rid='.$rid.'&token='.Application::Get('token').'\', \'post\');"><img src="images/pdf.png" alt="" /> '._DOWNLOAD_INVOICE.'</a> &nbsp;|&nbsp; ';
		$links .= '<a href="javascript:void(\'invoice|preview\')" onclick="javascript:appPreview(\'invoice\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>';
	}elseif($mode == 'description'){
		$links .= '<a href="javascript:void(\'description|preview\')" onclick="javascript:appPreview(\'description\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>';
    }
	draw_title_bar(
		prepare_breadcrumbs(array(_BOOKINGS=>'',_BOOKINGS_MANAGEMENT=>'',_BOOKINGS=>'',ucfirst($title_action)=>'')),
		$links		
	);
    	
	if($objSession->IsMessage('notice')){
		$msg = $objSession->GetMessage('notice');
		$objSession->SetMessage('notice', '');
	}
	echo $msg;

	draw_content_start();	

	// Check if hotel owner is not assigned to any hotel
	$allow_viewing = true;
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotels_list = implode(',', $objLogin->AssignedToHotels());
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
		}
	}elseif($objLogin->IsLoggedInAs('regionalmanager')){
		$hotels_list = implode(',', AccountLocations::GetHotels($objLogin->GetLoggedID()));
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(_REGIONALMANGER_NOT_ASSIGNED, false);
		}
	}
	
	if($allow_viewing){
		if($mode == 'view'){
			$objBookings->DrawOperationLinks(prepare_permanent_link('javascript:void(\'cleaup\')', '[ '._CLEANUP.' ]', '', '', '', 'onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_booking_bookings\', \'&mg_action=cleanup_bookings&token='.Application::Get('token').'\', \'post\');"').' <img class="help" src="images/question_mark.png" title="'._CLEANUP_TOOLTIP.'" />');		
			$objBookings->DrawViewMode();
			echo '<script type="text/javascript">
					function __mgMyDoPostBack(tbl, type, key){
						if(confirm("'._ALERT_CANCEL_BOOKING.'")){
							__mgDoPostBack(tbl, type, key);
						}					
					}
					function decodeEntities(encodedString) {
						var textArea = document.createElement("textarea");
						jQuery(textArea).html(encodedString);
						return textArea.value;
					}
					jQuery(document).ready(function(){
						// Add field customer name
						jQuery("input[name=filter_by_uhb_customersid]").hide();
						jQuery(\'<input type="text" class="mgrid_text" name="filter_by_uhb_customersname" value="'.htmlentities(isset($_POST['filter_by_uhb_customersname']) && !empty($_POST['filter_by_uhb_customersid']) ? $_POST['filter_by_uhb_customersname'] : '').'" style="width:140px" maxlength="125">\').insertAfter("input[name=filter_by_uhb_customersid]");
						// Add autocomplete for customer name
						jQuery("input[name=filter_by_uhb_customersname]").autocomplete({
							source: function(request, response){
								var token = "'.htmlentities(Application::Get('token')).'";
								jQuery.ajax({
									url: "ajax/customers.ajax.php",
									global: false,
									type: "POST",
									data: ({
										token: token,
										act: "send",
										lang: "'.htmlentities(Application::Get('lang')).'",
										search : jQuery("input[name=filter_by_uhb_customersname]").val(),
									}),
									dataType: "json",
									async: true,
									error: function(html){
										console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
									},
									success: function(data){
										if(data.length == 0){
											var label = decodeEntities("'.htmlentities(_NO_MATCHES_FOUND).'")
											response({ label: label });
										}else{
											jQuery("input[name=filter_by_uhb_customersid]").val("");
											response(jQuery.map(data, function(item){
												var label = decodeEntities(item.label)
												return{ id: item.id, label: label }
											}));
										}
									}
								});
							},
							minLength: 2,
							select: function(event, ui) {					
								jQuery("input[name=filter_by_uhb_customersid]").val(ui.item.id);
							}
						});
					});
					</script>';
			
			echo '<fieldset class="instructions" style="margin-top:10px;">
					<legend>Legend: </legend>
					<div style="padding:10px;">
						<span style="color:#222222">'._PREBOOKING.'</span> - '._LEGEND_PREBOOKING.'<br>
						<span style="color:#0000a3">'._PENDING.'</span> - '._LEGEND_PENDING.'<br>
						<span style="color:#a3a300">'._RESERVED.'</span> - '._LEGEND_RESERVED.'<br>
						<span style="color:#00a300">'._COMPLETED.'</span> - '._LEGEND_COMPLETED.'<br>
						<span style="color:#660000">'._REFUNDED.'</span> - '._LEGEND_REFUNDED.'<br>
						<span style="color:#a30000">'._PAYMENT_ERROR.'</span> - '._LEGEND_PAYMENT_ERROR.'<br>
						<span style="color:#939393">'._CANCELED.'</span> - '._LEGEND_CANCELED.'<br>
					</div>
				</fieldset>';
			
		}elseif($mode == 'add'){		
			$objBookings->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objBookings->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objBookings->DrawDetailsMode($rid);		
		}elseif($mode == 'description'){
			echo '<script type="text/javascript">
					function __RemoveExtras(rid, sel_extras){
						if(confirm("'._DELETE_WARNING.'")){
							appGoToPage("index.php?admin=mod_booking_bookings", "&mg_action=remove_extras&mg_rid="+rid+"&sel_extras="+sel_extras+"&token='.Application::Get('token').'", "post");
						}					
					}
				  </script>';
			$objBookings->DrawBookingDescription($rid);		
        }elseif($mode == 'comment'){
            $objBookings->DrawBookingComment($rid);
		}elseif($mode == 'invoice'){		
			$objBookings->DrawBookingInvoice($rid);		
		}	
	}
	
	draw_content_end();	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

