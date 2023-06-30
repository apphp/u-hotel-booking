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

if($objLogin->IsLoggedInAs('owner','mainadmin','admin')){
	
	draw_title_bar(prepare_breadcrumbs(array(_MASS_MAIL_AND_TEMPLATES=>'',_MAIL_LOG=>'')));
	
	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';

	$objMailLog = new MailLogs();

	if($action=='details'){		
		$mode = 'details';
	}elseif($action=='delete'){
		if($objMailLog->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objMailLog->error, false);
		}
		$mode = 'view';
	}elseif($action=='delete_all'){
		if($objMailLog->DeleteAllRecord()){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objMailLog->error, false);
		}
		$mode = 'view';
	}

	echo $msg;

	draw_content_start();	
	if($mode == 'view'){
		echo prepare_permanent_link('javascript:void(\'delete_all\')', '[ '._DELETE_ALL.' ]', '', '', '', 'onclick="if(confirm(\''._DELETE_ALL_ALERT.'\')) appGoToPage(\'index.php?admin=mail_log\', \'&mg_action=delete_all&token='.Application::Get('token').'\', \'post\');"');
		$objMailLog->DrawViewMode();	
	}elseif($mode == 'details'){		
		$objMailLog->DrawDetailsMode($rid);		
	}
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
