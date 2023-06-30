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

$type = isset($_GET['type']) ? prepare_input($_GET['type']) : '';

if($objLogin->IsLoggedInAsAdmin() &&
	($objLogin->HasPrivileges('add_pages') || $objLogin->HasPrivileges('edit_pages') || $objLogin->HasPrivileges('delete_pages'))   
){

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$language_id = (MicroGrid::GetParameter('language_id') != '') ? MicroGrid::GetParameter('language_id') : Languages::GetDefaultLang();
	$act    	= MicroGrid::GetParameter('act', false);
	$pid    	= MicroGrid::GetParameter('pid', false);
	$po    		= MicroGrid::GetParameter('po', false);
	$dir    	= MicroGrid::GetParameter('dir', false);
	
	$mode   = 'view';
	$msg 	= '';
	
	$objPages = new PagesGrid(
		Application::Get('type'),
		array(
			'add'     => $objLogin->HasPrivileges('add_pages'),
			'edit'    => $objLogin->HasPrivileges('edit_pages'),
			'details' => true,
			'delete'  => $objLogin->HasPrivileges('delete_pages')
		)
	);

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objPages->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objPages->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objPages->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objPages->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		$objPage = new Pages($rid);
		if($objPage->MoveToTrash()){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objPage->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
	}
	
	// start main content
	draw_title_bar(prepare_breadcrumbs(array(_MENUS_AND_PAGES=>'',_PAGE_MANAGEMENT=>'',((Application::Get('type') == 'system') ? _PAGE_EDIT_SYS_PAGES : _PAGE_EDIT_PAGES)=>'',ucfirst($action)=>'')));
	
	if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	
	echo '<script type="text/javascript">
		<!--
			function confirmRemoving(pid){
				if(!confirm("'._PAGE_REMOVE_WARNING.'")){
					false;
				}else{
					appGoTo("admin=pages&mg_action=delete&mg_language_id='.$language_id.'&mg_rid="+pid);
				}				
			}
		//-->
		</script>';

	if($mode == 'view'){		
		$objPages->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objPages->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objPages->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objPages->DrawDetailsMode($rid);		
	}
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
