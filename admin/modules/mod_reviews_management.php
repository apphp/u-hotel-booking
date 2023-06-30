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
	
if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner') && Modules::IsModuleInstalled('reviews')){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$email		= MicroGrid::GetParameter('email', false);
	$mode   	= 'view';
	$msg 		= '';
	
	$objReviews = new Reviews();

	// Check hotel owner has permissions to edit this hotel's info
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotel_id = null;
		if(in_array($action, array('create', 'update'))){
			$hotel_id = MicroGrid::GetParameter('hotel_id', false);	
		}elseif(in_array($action, array('edit', 'details', 'delete'))){
			$info = $objReviews->GetInfoByID($rid);
			$hotel_id = isset($info['hotel_id']) ? $info['hotel_id'] : '';
			if(empty($hotel_id)){
				$hotel_id = '-99';
			}
		}
		
		if(!empty($hotel_id) && !in_array($hotel_id, $objLogin->AssignedToHotels())){
            $msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
            $action = '';
			$mode = 'view';
		}
	}

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
	
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_REVIEWS=>'',_REVIEWS_MANAGEMENT=>'',ucfirst($action)=>'')));
	
	if($objSession->IsMessage('notice')) $msg = $objSession->GetMessage('notice');
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
	}
	
	if($allow_viewing){
		if($mode == 'view'){
			$objReviews->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objReviews->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objReviews->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objReviews->DrawDetailsMode($rid);		
		}
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

