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
	
if($objLogin->IsLoggedInAs('owner','mainadmin')){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
    $country_id = MicroGrid::GetParameter('cid', false);
	$mode   = 'view';
	$msg 	= '';
	
	$country_info = Countries::GetCountryInfo($country_id);
	
	if($country_id > 0 && count($country_info) > 0){

        $objStates = new States($country_id);
    
        if($action=='add'){		
            $mode = 'add';
        }elseif($action=='create'){
            if($objStates->AddRecord()){
                $msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objStates->error, false);
                $mode = 'add';
            }
        }elseif($action=='edit'){
            $mode = 'edit';
        }elseif($action=='update'){
            if($objStates->UpdateRecord($rid)){
                $msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
                $mode = 'view';
            }else{
                $msg = draw_important_message($objStates->error, false);
                $mode = 'edit';
            }		
        }elseif($action=='delete'){
            if($objStates->DeleteRecord($rid)){
                $msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
            }else{
                $msg = draw_important_message($objStates->error, false);
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
		draw_title_bar(
			prepare_breadcrumbs(array(
				_GENERAL				=> 'index.php?admin=home',
				_COUNTRIES				=> 'index.php?admin=countries_management',
				$country_info['name']	=> (!empty($action) ? 'index.php?admin=states_management&cid='.(int)$country_id : ''),
				_STATES					=> '',
				ucfirst($action)		=> ''
			),
			prepare_permanent_link('index.php?admin=countries_management', _BUTTON_BACK)								
		));

        //if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
        echo $msg;
    
        draw_content_start();	
        if($mode == 'view'){		
            $objStates->DrawViewMode();	
        }elseif($mode == 'add'){		
            $objStates->DrawAddMode();		
        }elseif($mode == 'edit'){		
            $objStates->DrawEditMode($rid);		
        }elseif($mode == 'details'){		
            $objStates->DrawDetailsMode($rid);		
        }
        draw_content_end();
	}else{
		draw_title_bar(
			prepare_breadcrumbs(array(_GENERAL=>'',_COUNTRIES=>'',_STATES=>'')),
			prepare_permanent_link('index.php?admin=countries_management', _BUTTON_BACK)
		);
		draw_important_message(_WRONG_PARAMETER_PASSED);
	}
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

