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

if($objLogin->IsLoggedInAsAdmin()){
	
	$task = isset($_GET['task']) ? prepare_input($_GET['task']) : '';
	$alert_state = Session::Get('alert_state');

	if($task == 'close_alert'){
		$alert_state = 'hidden';
	    Session::Set('alert_state', 'hidden');
	}elseif($task == 'open_alert'){
		$alert_state = '';
		Session::Set('alert_state', '');
	}

	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(
			_GENERAL	=> 'index.php?admin=home',
			_HOME		=> ''
		)
	));
    
    
    // draw important messages 
	// ---------------------------------------------------------------------
    if(count($actions_msg) > 0){
		if($alert_state == ''){
			$msg = '<div id="divAlertMessages">
				<img src="images/close.png" alt="" style="cursor:pointer;float:'.Application::Get('defined_right').';margin-right:-3px;" title="'._HIDE.'" onclick="javascript:appGoTo(\'admin=home\',\'&task=close_alert\')" />
				<img src="images/action_required.png" alt="" style="margin-bottom:-3px;" /> &nbsp;&nbsp;<b>'._ACTION_REQUIRED.'</b>: 
				<ul>';
				foreach($actions_msg as $single_msg){
					$msg .= '<li>'.$single_msg.'</li>';
				}
			$msg .= '</ul></div>';
			draw_important_message($msg, true, false);        			
		}else{
			echo '<div id="divAlertRequired"><a href="javascript:void(0);" onclick="javascript:appGoTo(\'admin=home\',\'&task=open_alert\')">'._OPEN_ALERT_WINDOW.'</a></div>';
		}
    }

	# Draw Check-In/Check-Out information for all admins excluding car agency owners
	if(!$objLogin->IsLoggedInAs('agencyowner')){
		$msg = '<div style="padding:9px;">
			<p>'._TODAY.': <b>'.format_datetime(date('Y-m-d H:i:s'), '', '', true).'</b></p>			
			'.Bookings::DrawCheckList('checkin').'
			'.Bookings::DrawCheckList('checkout').'
		</div>';
		draw_message($msg, true, false);
	}
	$msg = '<div style="padding:9px;">
	<div class="site_version">'._VERSION.': '.CURRENT_VERSION.'</div>
	<p>'._LAST_LOGIN.': <b>'.format_datetime($objLogin->GetLastLoginTime(), '', _NEVER, true).'</b></p>';
	if($objLogin->IsLoggedInAs('agencyowner')){
		$msg .= '<br>'._AGENCYOWNER_WELCOME_TEXT;
	}elseif ($objLogin->IsLoggedInAs('hotelowner')){
		$msg .= '<br>'.(FLATS_INSTEAD_OF_HOTELS ? _FLATS_HOTELOWNER_WELCOME_TEXT : _HOTELOWNER_WELCOME_TEXT);
	}elseif ($objLogin->IsLoggedInAs('regionalmanager')){
		$msg .= '<br>'._REGIONALMANAGER_WELCOME_TEXT;
	}else{
		$msg .= FLATS_INSTEAD_OF_HOTELS == true ? _FLATS_ADMIN_WELCOME_TEXT : _ADMIN_WELCOME_TEXT;
	}	
	
	$msg .= '</div>';
	draw_default_message($msg, true, false);

    // Draw dashboard modules
	$objModules = new Modules();
	echo '<div style="padding:2px 2px 40px 2px;">';
		$objModules->DrawModulesOnDashboard();
		echo '<div style="clear:both;"></div>';
	echo '</div>';

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
