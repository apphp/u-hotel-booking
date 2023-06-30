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

if($objLogin->IsLoggedInAs('owner') && Modules::IsModuleInstalled('backup')){
	
	$submition_type = isset($_POST['submition_type']) ? prepare_input($_POST['submition_type']) : '';
	$backup_file 	= isset($_POST['backup_file']) ? str_replace(array("'", '"', "\\", "/", " "), '', prepare_input($_POST['backup_file'])) : '';
	$st 			= isset($_GET['st']) ? prepare_input($_GET['st']) : '';
	$fname 		    = isset($_GET['fname']) ? prepare_input($_GET['fname']) : '';
	$msg            = '';		
	
	$objBackup = new Backup();
	
	if($submition_type == '1'){		
		// save backup
		if($objBackup->ExecuteBackup($backup_file)){
			$msg = draw_success_message(str_replace('_FILE_NAME_', $fname, _BACKUP_WAS_CREATED), false);	
		}else{
			$msg = draw_important_message($objBackup->error, false);
		}		
	}elseif($st == 'delete'){
		// delete previouse backup		
		if($objBackup->DeleteBackup($fname)){
			$msg = draw_success_message(str_replace('_FILE_NAME_', $fname, _BACKUP_WAS_DELETED), false);	
		}else{
			$msg = draw_important_message($objBackup->error, false);
		}
	}

	// draw title bar and message
	draw_title_bar(
		prepare_breadcrumbs(array(_MODULES=>'',_BACKUP=>'',_BACKUP_INSTALLATION=>'')),
		prepare_permanent_link('index.php?admin=mod_backup_restore', _BACKUP_RESTORE)
	);
	echo $msg;	

	draw_content_start();
	$objBackup->DrawInstallationForm();
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

