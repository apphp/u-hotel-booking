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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('conferences')){

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$conference_id = MicroGrid::GetParameter('cid', false);
	$mode   = 'view';
	$msg    = '';
	
	$objConferenceParticipants = new ConferenceParticipants($conference_id);
	
//	$objConferences = new Conferences();
//	$conference_info = $objConferences->GetConference($conference_id);

	if($action=='delete'){
		if($objConferenceParticipants->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objConferenceParticipants->error, false);
		}
		$mode = 'view';
	}elseif($action=='clean_credit_card'){				
		if($objConferenceParticipants->CleanUpCreditCardInfo($rid)){
			$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
		}else{
			$msg = draw_important_message($objBookings->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}else{
		$action = '';
	}

	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(_MODULES=>'index.php?admin=modules',_CONFERENCES=>'index.php?admin=mod_conference_types',_CONFERENCES_MANAGEMENT=>'index.php?admin=mod_conferences_management',_CONFERENCE_PARTICIPANTS=>($mode == 'view' ? '' : 'index.php?admin=mod_conference_participants'.(!empty($conference_id) ? '&cid='.$conference_id : '')))),
		prepare_permanent_link(($mode == 'view' ? 'index.php?admin=mod_conferences_management' : 'index.php?admin=mod_conference_participants'.(!empty($conference_id) ? '&cid='.$conference_id : '')), _BUTTON_BACK)
	);

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objConferenceParticipants->DrawViewMode();	
	}elseif($mode == 'details'){		
		$objConferenceParticipants->DrawDetailsMode($rid);		
	}
	draw_content_end();		
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
