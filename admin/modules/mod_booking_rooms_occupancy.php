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

if(Modules::IsModuleInstalled('booking') &&
  (ModulesSettings::Get('booking', 'is_active') != 'no') && 
  ($objLogin->IsLoggedInAs('owner','mainadmin','admin','regionalmanager') || ($objLogin->IsLoggedInAs('hotelowner') && $objLogin->HasPrivileges('view_hotel_reports')))
){

	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(
			_BOOKING => 'index.php?admin=mod_booking_bookings',
			_INFO_AND_STATISTICS => '',
			_ROOMS_OCCUPANCY => ''
		)),
		_LOCAL_TIME.': '.format_datetime(date('Y-m-d H:i:s'), '', '', true)
	);

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	//echo $msg;

	draw_content_start();	

	$allow_viewing = true;
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotels_list = implode(',', $objLogin->AssignedToHotels());
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
		}
	}elseif($objLogin->IsLoggedInAs('regionalmanager')){
		$hotels_list = implode(',', AccountLocations::GetHotels($objLogin->GetLoggedID()));
		if(empty($hotels_list)){
			$allow_viewing = false;
			echo draw_important_message(_OWNER_NOT_ASSIGNED, false);
		}
	}
	
	if($allow_viewing){
		## +-----------------------------------------------------------------------+
		## | 1. Creating & Calling:                                                | 
		## +-----------------------------------------------------------------------+
		##  *** define a relative (virtual) path to calendar.class.php file  
		##  *** and other files (relatively to the current file)
		##  *** RELATIVE PATH ONLY *** Ex.: '', 'calendar/' or '../calendar/'
		define ('CALENDAR_DIR', 'modules/calendar/');                     
		///require_once(CALENDAR_DIR.'inc/connection.inc.php');
		require_once(CALENDAR_DIR.'calendar.class.php');
		
		## *** create calendar object
		$objCalendar = new Calendar();
		
		## +-----------------------------------------------------------------------+
		## | 2. General Settings:                                                  |
		## +-----------------------------------------------------------------------+
		## +-- Submission Settings & Debug Mode -----------------------------------
		## *** set PostBack method: 'get' or 'post'
		/// $objCalendar->SetPostBackMethod('post');
		## *** show debug info - false|true
		$objCalendar->Debug(false);
	
		## +-- Users Settings -----------------------------------------------------
		## *** set user ID (must be numeric value)
		/// $user_id = 0;
		/// $objCalendar->SetUserID($user_id);    
	
		## +-- Passing Parameters -------------------------------------------------
		## *** save http request variables between  calendar's sessions
		$http_request_vars = array('admin');
		$objCalendar->SaveHttpRequestVars($http_request_vars);
	
		## +-- Cache Settings -----------------------------------------------------
		## *** define caching parameters:
		## *** 1st - allow caching or not, 2nd - caching lifetime in minutes
		$objCalendar->SetCachingParameters(false, 15);
		## *** define all caching pages
		/// $objCalendar->DeleteCache();
		
		## +-- Languages ----------------------------------------------------------
		## *** set interface language (default - English)
		## *** (en) - English  (es) - Spanish  (de) - German
		switch(Application::Get('lang')){
			case 'de':
			case 'es':
			case 'fr':
			case 'it':
			case 'pt':
				$objCalendar->SetInterfaceLang(Application::Get('lang')); break;
			default:
				$objCalendar->SetInterfaceLang('en');    
				break;		
		}    
	
		## +-- Week Settings ------------------------------------------------------
		## *** set week day name length - 'short' or 'long'
		$objCalendar->SetWeekDayNameLength('long');
		## *** set start day of the week: from 1 (Sunday) to 7 (Saturday)
		$objCalendar->SetWeekStartedDay($objSettings->GetParameter('week_start_day'));
		## *** disable certain day of the week: from 1 (Sunday) to 7 (Saturday)
		/// $objCalendar->SetDisabledDay('7');
		## *** define showing a week number of the year
		$objCalendar->ShowWeekNumberOfYear(true);
	
		## +-----------------------------------------------------------------------+
		## | 3. Events & Categories Settings:                                      |
		## +-----------------------------------------------------------------------+
		## +-- Categories Actions & Operations ------------------------------------
		##  *** set (allow) calendar categories operations
		$cat_operations = array(
			'add'=>false, 
			'edit'=>false,
			'details'=>false,
			'delete'=>false,
			'manage'=>false,
			'allow_colors'=>false
		);
		$objCalendar->SetCategoriesOperations($cat_operations);
	
		## +-- Events Actions & Operations ----------------------------------------
		##  *** allow multiple occurrences for events in the same time slot: false|true - default
		 $objCalendar->SetEventsMultipleOccurrences(false);
		##  *** allow editing events in past
		/// $objCalendar->EditingEventsInPast(false);
		##  *** block deleting events before certain period of time (in hours)
		 //$objCalendar->BlockEventsDeletingBefore(24);
		##  *** set (allow) calendar events operations
		$events_operations = array(
			'add'=>false,
			'edit'=>false,
			'details'=>false,
			'delete'=>false,
			'delete_by_range'=>false,
			'manage'=>false,
		);
		$objCalendar->SetEventsOperations($events_operations);
	
	
		## +-----------------------------------------------------------------------+
		## | 4. Time Settings and Formatting:                                      | 
		## +-----------------------------------------------------------------------+
		## +-- TimeZone Settings --------------------------------------------------
		## *** set timezone
		## *** (list of supported Timezones - http://us3.php.net/manual/en/timezones.php)
	
		// set time zone according to time zone of hotel
		/// $time_zone = 'America/Los_Angeles';
		/// $objCalendar->SetTimeZone($time_zone);
		## *** get current timezone
		/// $objCalendar->GetCurrentTimeZone();
	
		## +-- Time Format & Settings ----------------------------------------------
		## *** define time format - 24|AM/PM
		$objCalendar->SetTimeFormat('24');
		## *** define allowed hours frame (from, to). Possible values: 0...24
		$objCalendar->SetAllowedHours(0, 19);
		## *** define time slot - 15|30|45|60 minutes
		$objCalendar->SetTimeSlot('60');
		## *** set showing times in Daily, Weekly and List views
		$objCalendar->ShowTimes('true');
		
	
		## +-----------------------------------------------------------------------+
		## | 5. Visual Settings:                                                   | 
		## +-----------------------------------------------------------------------+
		## +-- Calendar Views -----------------------------------------------------
		## *** set (allow) calendar Views
		$views = array('daily'=>false, 
					   'weekly'=>true,
					   'monthly'=>true,
					   'monthly_small'=>false,
					   'yearly'=>true,
					   'list_view'=>true);                        
		$objCalendar->SetCalendarViews($views);
		## *** set default calendar view - 'daily'|'weekly'|'monthly'|'yearly'|'list_view'|'monthly_small'
		$objCalendar->SetDefaultView('monthly');    
		## *** Set action link for monthly small view - file2.php or ../file3.php etc.
		/// $objCalendar->SetMonthlySmallLinks('');    
		## *** set CSS style: 'green'|'brown'|'blue' - default
	
		## +-- Calendar Actions -----------------------------------------------------
		##  *** set (allow) calendar actions
		$calendar_actions = array(
			'statistics'=>false,
			'printing'=>true
		);
		$objCalendar->SetCalendarActions($calendar_actions);
		
		$objCalendar->SetCssStyle('blue');
		## *** set Add Event form type: 'floating'|'popup' - default
		$objCalendar->SetAddEventFormType('floating');
		## *** set calendar width and height
		$objCalendar->SetCalendarDimensions('90%', '550px');
		## *** set type of displaying for events
		## *** possible values for weekly  - 'inline'|'tooltip'
		## *** possible values for monthly - 'inline'|'list'|'tooltip'
		$events_display_type = array('weekly'=>'inline', 'monthly'=>'inline', 'yearly'=>'tooltip');
		$objCalendar->SetEventsDisplayType($events_display_type);
		## *** set Sunday color - true|false
		$objCalendar->SetSundayColor(true);    
		## *** set calendar caption
		//$objCalendar->SetCaption();
	
	
		## +-----------------------------------------------------------------------+
		## | 6. Draw Calendar:                                                     | 
		## +-----------------------------------------------------------------------+
		## *** drawing calendar
		echo '<div class="table-responsive">';
		$objCalendar->Show();
		echo '</div>';
	}

	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

