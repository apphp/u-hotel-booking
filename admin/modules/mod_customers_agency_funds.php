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
	
if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('customers') && ModulesSettings::Get('customers', 'allow_agencies') == 'yes'){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$agency_id	= (int)MicroGrid::GetParameter('aid', false);
	$mode   	= 'view';
	$msg 		= '';
	
	$objCustomers = new Customers();
	$agency_info = $objCustomers->GetCustomerInfo($agency_id);
	
	if(!empty($agency_id) && count($agency_info) > 0){	
		$objCustomerFunds = new CustomerFunds($agency_id);
		
		if($action=='add'){		
			$mode = 'add';
		}elseif($action=='create'){
			if($objCustomerFunds->addRecord()){
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objCustomerFunds->error, false);
				$mode = 'add';
			}
		}elseif($action=='edit'){
			$mode = 'edit';
		}elseif($action=='update'){
			if($objCustomerFunds->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objCustomerFunds->error, false);
				$mode = 'edit';
			}		
		}elseif($action=='delete'){
			if($objCustomerFunds->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objCustomerFunds->error, false);
			}
			$mode = 'view';
		}elseif($action=='details'){		
			$mode = 'details';		
		}elseif($action=='cancel_add'){		
			$mode = 'view';		
		}elseif($action=='cancel_edit'){				
			$mode = 'view';
		}elseif($action=='remove'){
			$comments = isset($_POST['removed_comments']) ? prepare_input($_POST['removed_comments'], true) : '';
			if($objCustomerFunds->RemoveRecord($rid, $comments)){
				$msg = draw_success_message(_FUND_REMOVE_SUCCESS, false);
			}else{
				$msg = draw_important_message($objCustomerFunds->error, false);
			}			
			$mode = 'view';
		}
		
		$agency_name = isset($agency_info['company']) ? $agency_info['company'] : '';

		// Start main content
		draw_title_bar(
			prepare_breadcrumbs(array(
				_ACCOUNTS				=> '',
				_CUSTOMERS_MANAGEMENT	=> 'index.php?admin=mod_customers_management',
				_AGENCIES				=> 'index.php?admin=mod_customers_agencies',
				$agency_name			=> '',
				_BALANCE				=> '',
				ucfirst($action)=>''
			)),
			prepare_permanent_link('index.php?admin=mod_customers_agencies', _BUTTON_BACK)
		);
		
		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		echo $msg;
	
		draw_content_start();	
		if($mode == 'view'){
			$objCustomerFunds->DrawViewMode();
			echo '<script type="text/javascript">
					function __mgMyDoPostBack(tbl, type, key){
						if(confirm("'._ALERT_REMOVE_FUND.'")){
							__mgDoPostBack(tbl, type, key);
						}					
					}
				  </script>';
			echo '<script type="text/javascript">
					var dialog = null, table, type, key;

					jQuery(document).ready(function(){
						dialog = jQuery( "#dialog-form" ).dialog({
						  autoOpen: false,
						  height: 200,
						  width: 350,
						  modal: true,
						  buttons: {
							"'.htmlspecialchars(_YES).'": myFoundRemoveRecord,
							"'.htmlspecialchars(_NO).'": function() {
							  dialog.dialog("close");
							}
						  },
						});
					});
					function customerFoundRemove(table, id){
						type = \'remove\';
						tbl = table;

						if(dialog === null){
							return false;
						}

						jQuery("#form-customer-found-remove input[name=mg_rid]").val(id);
						
						 dialog.dialog("open");
						//if(confirm("'._ALERT_CANCEL_BOOKING.'")){
							//__mgDoPostBack(tbl, type, key);
						//}					
					}
					function myFoundRemoveRecord(){
						dialog.dialog("close");
						jQuery("#form-customer-found-remove").submit();
					}
				  </script>
				  <div id="dialog-form" title="'._REMOVED.'">
					  <span class="ui-icon ui-icon-alert" style="float:left; margin:0 12px 0 0;"></span>
					  <p class="validateTips">'._ALERT_CANCEL_BOOKING.'</p>
					  <form id="form-customer-found-remove" method="post">
						<input type="hidden" name="mg_action" value="remove"/>
						<input type="hidden" name="mg_rid" value=""/>
						'.draw_hidden_field('mg_operation_code', MicroGrid::GetRandomString(20), false).'
						'.draw_token_field(false).'
						<fieldset>
						  <label for="removed_comments">'._COMMENTS.':</label><br/>
						  <textarea name="removed_comments" id="removed_comments" class="text ui-widget-content" style="height:50px;width:320px;"></textarea>
					 
						  <!-- Allow form submission with keyboard without duplicating the dialog button -->
						  <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
						</fieldset>
					  </form>
					</div>';
		}elseif($mode == 'add'){		
			$objCustomerFunds->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objCustomerFunds->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objCustomerFunds->DrawDetailsMode($rid);		
		}
		
		draw_content_end();
	}else{
		draw_title_bar(_ADMIN);
		draw_important_message(_WRONG_PARAMETER_PASSED);		
	}
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
