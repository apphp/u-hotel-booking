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

    $action 	= MicroGrid::GetParameter('action');
    $rid    	= MicroGrid::GetParameter('rid');
    $email		= MicroGrid::GetParameter('email', false);
    $mode   	= 'view';
    $msg 		= '';

    $objToursReviews = new ToursReviews();

    if($action=='add'){
        $mode = 'add';
    }elseif($action=='create'){
        if($objToursReviews->AddRecord()){
            $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objToursReviews->error, false);
            $mode = 'add';
        }
    }elseif($action=='edit'){
        $mode = 'edit';
    }elseif($action=='update'){
        if($objToursReviews->UpdateRecord($rid)){
            $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
            $mode = 'view';
        }else{
            $msg = draw_important_message($objToursReviews->error, false);
            $mode = 'edit';
        }
    }elseif($action=='delete'){
        if($objToursReviews->DeleteRecord($rid)){
            $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
        }else{
            $msg = draw_important_message($objToursReviews->error, false);
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
    draw_title_bar(prepare_breadcrumbs(array(_TOURS_AND_ATTRACTIONS=>'',_REVIEWS_MANAGEMENT=>'',ucfirst($action)=>'')));

    if($objSession->IsMessage('notice')) $msg = $objSession->GetMessage('notice');
    echo $msg;

    draw_content_start();

    if($mode == 'view'){
        $objToursReviews->DrawOperationLinks(
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_management', '[ ' . _TOURS_MANAGEMENT . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_agencies_management', '[ ' . _TOUR_AGENCIES . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_types_management', '[ ' . _TOUR_TYPES . ' ]') . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_locations_management', '[ ' . _TOUR_LOCATIONS . ' ]') . ' &nbsp; ' .
            '[ <b>' . _REVIEWS . '</b> ]' . ' &nbsp; ' .
            prepare_permanent_link('index.php?admin=mod_tours_and_attractions_orders_management', '[ ' . _RESERVATIONS . ' ]')
        );

        $objToursReviews->DrawViewMode();
    }elseif($mode == 'add'){
        $objToursReviews->DrawAddMode();
    }elseif($mode == 'edit'){
        $objToursReviews->DrawEditMode($rid);
    }elseif($mode == 'details'){
        $objToursReviews->DrawDetailsMode($rid);
    }
    draw_content_end();

    if($mode == 'add'){
        echo '<script type="text/javascript">
			function decodeEntities(encodedString) {
				var textArea = document.createElement("textarea");
				jQuery(textArea).html(encodedString);
				return textArea.value;
			}				
			jQuery(document).ready(function(){
				// Add field customer name
				jQuery("input[name=customer_id]").hide();
				jQuery(\'<input type="text" class="mgrid_text" name="customer_name" value="" style="width:140px" maxlength="125">\').insertAfter("input[name=customer_id]");
				// Add autocomplete for customer name
				jQuery("input[name=customer_name]").autocomplete({
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
								search : jQuery("input[name=customer_name]").val(),
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
									jQuery("input[name=customer_id]").val("");
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
						jQuery("input[name=customer_id]").val(ui.item.id);
					}
				});
			});
			</script>';
    }

}else{
    draw_title_bar(_ADMIN);
    draw_important_message(_NOT_AUTHORIZED);
}

