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

    if(Modules::IsModuleInstalled('tours_and_attractions')){

        $action 		= MicroGrid::GetParameter('action');
        $rid    		= MicroGrid::GetParameter('rid');
        $mode   		= 'view';
        $msg 			= '';
        $view_allowed 	= true;
        $customers_cancel_reservation = ModulesSettings::Get('tours_and_attractions', 'customers_cancel_reservation');

        $objToursOrders = new ToursOrders($objLogin->GetLoggedID());

        // Customer access validation
        if(!empty($rid)){
            $reservation = $objToursOrders->GetInfoByID($rid);
            if(!isset($reservation['customer_id']) || $reservation['customer_id'] != $objLogin->GetLoggedID()){
                $view_allowed = false;
            }
        }

        if($view_allowed){
            if($action=='add'){
                $mode = 'add';
            }elseif($action=='create'){
                if($objToursOrders->AddRecord()){
                    $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
                    $mode = 'view';
                }else{
                    $msg = draw_important_message($objToursOrders->error, false);
                    $mode = 'add';
                }
            }elseif($action=='edit'){
                $mode = 'edit';
            }elseif($action=='update'){
                if($objToursOrders->UpdateRecord($rid)){
                    $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
                    $mode = 'view';
                }else{
                    $msg = draw_important_message($objToursOrders->error, false);
                    $mode = 'edit';
                }
                //}elseif($action=='delete' && $customers_cancel_reservation > '0'){
                //	if($objToursOrders->DeleteRecord($rid)){
                //		$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
                //	}else{
                //		$msg = draw_important_message($objToursOrders->error, false);
                //	}
                //	$mode = 'view';
            }elseif($action=='cancel' && $customers_cancel_reservation > '0'){
                if($objToursOrders->CancelRecord($rid)){
                    $msg  = draw_success_message(str_replace('_BOOKING_', '', _BOOKING_CANCELED_SUCCESS), false);
                    // send email to customer about reservation cancelation
                    $objToursReservations = new ToursReservations();
                    if($objToursReservations->SendCancelOrderEmail($rid)){
                        $msg .= draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
                    }else{
                        $msg .= draw_important_message($objToursReservations->error, false);
                    }
                }else{
                    $msg = draw_important_message($objToursOrders->error, false);
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
                prepare_breadcrumbs(array(_TOUR_RESERVATIONS=>'',_RESERVATIONS=>'',ucfirst($action)=>'')),
                (($mode == 'invoice' || $mode == 'description') ? '<a href="javascript:appPreview(\''.$mode.'\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>' : '')
            );

            //if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
            echo $msg;

            //draw_content_start();
            echo '<div id="divMyToursOrders">';
            if($mode == 'view'){
                echo '<script type="text/javascript">
					function __mgMyDoPostBack(tbl, type, key){
						if(confirm("'._ALERT_CANCEL_BOOKING.'")){
							__mgDoPostBack(tbl, type, key);
						}					
					}
				  </script>';
                $objToursOrders->DrawViewMode();
            }elseif($mode == 'add'){
                $objToursOrders->DrawAddMode();
            }elseif($mode == 'edit'){
                $objToursOrders->DrawEditMode($rid);
            }elseif($mode == 'details'){
                //$objToursOrders->DrawDetailsMode($rid);
            }elseif($mode == 'description'){
                $objToursOrders->DrawReservationDescription($rid);
            }elseif($mode == 'invoice'){
                $objToursOrders->DrawToursOrdersInvoice($rid);
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
