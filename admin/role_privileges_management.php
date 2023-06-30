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

if($objLogin->IsLoggedInAs('owner')){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$role_id  	= MicroGrid::GetParameter('role_id', false);
	$mode   	= 'view';
	$msg 		= '';
	
	$objRolePrivileges = new RolePrivileges($role_id);

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objRolePrivileges->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objRolePrivileges->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objRolePrivileges->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objRolePrivileges->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objRolePrivileges->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objRolePrivileges->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
	}
	
	$objRoles = new Roles();
	$role_info = $objRoles->GetInfoByID($role_id);
	$role_info_name = isset($role_info['name']) ? $role_info['name'] : '';

	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(
			_ACCOUNTS			=> '',
			_ROLES_MANAGEMENT	=> 'index.php?admin=roles_management',
			$role_info_name		=> (!empty($action) ? 'index.php?admin=role_privileges_management&role_id='.(int)$role_id : ''),
			ucfirst($action)	=> ''
		)),
		prepare_permanent_link(
			(!empty($action) ? 'index.php?admin=role_privileges_management&role_id='.(int)$role_id : 'index.php?admin=roles_management'),
			_BUTTON_BACK
		)
	);

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){		
		$objRolePrivileges->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objRolePrivileges->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objRolePrivileges->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objRolePrivileges->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
