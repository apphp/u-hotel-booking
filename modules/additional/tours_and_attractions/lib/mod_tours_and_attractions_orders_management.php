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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('tours_and_attractions')){

    $action 		 = MicroGrid::GetParameter('action');
    $title_action    = $action;
    $rid    		 = MicroGrid::GetParameter('rid');

    $reservation_status  = MicroGrid::GetParameter('status', false);
    $reservation_number  = MicroGrid::GetParameter('reservation_number', false);
    $customer_id     = MicroGrid::GetParameter('customer_id', false);

    $mode = 'view';
    $msg = '';
    $links = '';

    $objToursOrders = new ToursOrders();

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
            $objToursReservations = new ToursReservations();
            if($reservation_status == '2'){
                // 2 - ORDER RESERVED - send email to customer
                $objToursReservations->SendOrderEmail($reservation_number, 'reserved', $customer_id);
            }elseif($reservation_status == '3'){
                // 3 - ORDER COMPLETED - send email to customer
                $objToursOrders->UpdatePaymentDate($rid);
                $objToursReservations->SendOrderEmail($reservation_number, 'completed', $customer_id);
            }elseif($reservation_status == '4'){
                // 4 - REFUND - return rooms to search
                $objToursReservations->SendOrderEmail($reservation_number, 'refunded', $customer_id);
            }
            $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objToursOrders->error, false);
            $mode = 'edit';
        }
    }elseif($action=='delete'){
        if($objToursOrders->DeleteRecord($rid)){
            $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
        }else{
            $msg = draw_important_message($objToursOrders->error, false);
        }
        $mode = 'view';
    }elseif($action=='cancel'){
        if($objToursOrders->CancelRecord($rid)){
            $msg = draw_success_message(str_replace('_BOOKING_', '', _BOOKING_CANCELED_SUCCESS), false);
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
    }elseif($action=='download_invoice'){
        if(strtolower(SITE_MODE) == "demo"){
            $msg = draw_important_message(_OPERATION_BLOCKED, false);
        }else{
            if($objToursOrders->PrepareInvoiceDownload($rid)){
                $msg = draw_success_message($objToursOrders->message, false);
            }else{
                $msg = draw_important_message($objToursOrders->error, false);
            }
        }
        $mode = 'view';
        $title_action = _DOWNLOAD_INVOICE;
    }elseif($action=='send_invoice'){
        if($objToursOrders->SendInvoice($rid)){
            $msg = draw_success_message(_INVOICE_SENT_SUCCESS, false);
        }else{
            $msg = draw_important_message($objToursOrders->error, false);
        }
        $mode = 'view';
        $title_action = _SEND_INVOICE;
    }elseif($action=='clean_credit_card'){
        if($objToursOrders->CleanUpCreditCardInfo($rid)){
            $msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
        }else{
            $msg = draw_important_message($objToursOrders->error, false);
        }
        $mode = 'view';
        $title_action = 'Clean';
    }elseif($action=='cleanup_reservation'){
        if($objToursOrders->CleanUpReservation($rid)){
            $msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
        }else{
            $msg = draw_important_message($objToursOrders->error, false);
        }
        $mode = 'view';
        $title_action = _CLEANUP;
    }

    // Start main content
    if($mode == 'invoice'){
        $links .= '<a href="javascript:void(\'invoice|send\')" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_tours_and_attractions_orders_management\', \'&mg_action=send_invoice&mg_rid='.$rid.'&token='.Application::Get('token').'\', \'post\');"><img src="images/mail.png" alt="" /> '._SEND_INVOICE.'</a> &nbsp;|&nbsp; ';
        $links .= '<a href="javascript:void(\'invoice|download\')" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_tours_and_attractions_orders_management\', \'&mg_action=download_invoice&mg_rid='.$rid.'&token='.Application::Get('token').'\', \'post\');"><img src="images/pdf.png" alt="" /> '._DOWNLOAD_INVOICE.'</a> &nbsp;|&nbsp; ';
        $links .= '<a href="javascript:void(\'invoice|preview\')" onclick="javascript:appPreview(\'invoice\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>';
    }elseif($mode == 'description'){
        $links .= '<a href="javascript:void(\'description|preview\')" onclick="javascript:appPreview(\'description\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>';
    }

    draw_title_bar(
        prepare_breadcrumbs(array(_TOURS_AND_ATTRACTIONS=>'',_RESERVATIONS=>'',ucfirst($action)=>'')),
        $links
    );

    if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
    echo $msg;

    draw_content_start();

    if($mode == 'view'){
        $objToursOrders->DrawOperationLinks(
            $links .
            prepare_permanent_link('javascript:void(\'cleaup\')', '[ '._CLEANUP.' ]', '', '', '', 'onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) appGoToPage(\'index.php?admin=mod_tours_and_attractions_orders_management\', \'&mg_action=cleanup_reservation&token='.Application::Get('token').'\', \'post\');"').' <img class="help" src="images/question_mark.png" title="'._CLEANUP_TOOLTIP.'" />'
            . ' &nbsp;&nbsp; | &nbsp;&nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_management', '[ ' . _TOURS_MANAGEMENT . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_agencies_management', '[ ' . _TOUR_AGENCIES . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_types_management', '[ ' . _TOUR_TYPES . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_locations_management', '[ ' . _TOUR_LOCATIONS . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_reviews_management', '[ ' . _REVIEWS . ' ]') . ' &nbsp; ' .
            '[ <b>' . _RESERVATIONS . '</b> ]'
        );

        $objToursOrders->DrawViewMode();
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
                    <span style="color:#0000a3">'._PENDING.'</span> - '._LEGEND_TOUR_PENDING.'<br>
                    <span style="color:#a3a300">'._RESERVED.'</span> - '._LEGEND_TOUR_RESERVED.'<br>
                    <span style="color:#00a300">'._COMPLETED.'</span> - '._LEGEND_COMPLETED.'<br>
                    <span style="color:#660000">'._REFUNDED.'</span> - '._LEGEND_TOUR_REFUNDED.'<br>
                    <span style="color:#a30000">'._PAYMENT_ERROR.'</span> - '._LEGEND_PAYMENT_ERROR.'<br>
                    <span style="color:#939393">'._CANCELED.'</span> - '._LEGEND_TOUR_CANCELED.'<br>
                </div>
            </fieldset>';

    }elseif($mode == 'add'){
        $objToursOrders->DrawAddMode();
    }elseif($mode == 'edit'){
        $objToursOrders->DrawEditMode($rid);
    }elseif($mode == 'details'){
        $objToursOrders->DrawDetailsMode($rid);
    }elseif($mode == 'description'){
        $objToursOrders->DrawReservationDescription($rid);
    }elseif($mode == 'invoice'){
        $objToursOrders->DrawToursOrdersInvoice($rid);
    }

    draw_content_end();
}else{
    draw_title_bar(_ADMIN);
    draw_important_message(_NOT_AUTHORIZED);
}

