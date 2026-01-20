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

if(Modules::IsModuleInstalled('conferences')){
	$conference_id = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
	if(!empty($conference_id)){
		$conference = Conferences::GetConference($conference_id);
		if(!empty($conference) && is_array($conference)){
			$action_message = '';
			if(Session::Get('conference_alert') != '' && Session::Get('conference_alert_type') != ''){
				$alert = Session::Get('conference_alert');
				$alert_type = Session::Get('conference_alert_type');
				Session::Set('conference_alert', '');
				Session::Set('conference_alert_type', '');
				if(!empty($alert_type) && !empty($alert)){
					if($alert_type == 'success'){
						$action_message = draw_success_message($alert, false);
					}elseif($alert_type == 'warning'){
						$action_message = draw_message($alert, false);
					}elseif($alert_type == 'error'){
						$action_message = draw_important_message($alert, false);
					}else{
						$action_message = draw_default_message($alert, false);
					}
				}
			}

			draw_content_start();
			echo '<div class="vpadding20">';

			echo '<h2>'.$conference['conference_type_name'].'</h2>';
			echo $action_message;

			echo '<br>';
			echo '<a class="form_button_middle next-button green" href="index.php?page=conference&cid='.$conference_id.'">'._CONFERENCE_REGESTRATION_LINK.'</a>';
			echo '<hr>';

			// Location Description
			if(!empty($conference['location_description'])){
				echo '<h3>'._LOCATION_DESCRIPTION.'</h3>';
				echo '<div id="location_description">'.$conference['location_description'].'</div>';
				echo '<hr>';
			}
			
			// Hotel Description
			if(!empty($conference['hotel_description'])){
				echo '<h3>'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT_DESCRIPTION : _HOTEL_DESCRIPTION).'</h3>';
				echo '<div id="hotel_description">'.$conference['hotel_description'].'</div>';
				echo '<hr>';
			}

			// Target Audience
			if(!empty($conference['target_audience'])){
				echo '<h3>'._TARGET_AUDIENCE.'</h3>';
				echo '<div id="target_audience">'.$conference['target_audience'].'</div>';
				echo '<hr>';
			}
			
			// Program Purpose
			if(!empty($conference['program_purpose'])){
				echo '<h3>'._PROGRAM_PURPOSE.'</h3>';
				echo '<div id="program_purpose">'.$conference['program_purpose'].'</div>';
				echo '<hr>';
			}
			
			// Objectives
			if(!empty($conference['objectives'])){
				echo '<h3>'._OBJECTIVES.'</h3>';
				echo '<div id="objectives">'.$conference['objectives'].'</div>';
				echo '<hr>';
			}
			
			// Program Schedule
			if(!empty($conference['program_schedule'])){
				echo '<h3>'._PROGRAM_SCHEDULE.'</h3>';
				echo '<div id="program_schedule">'.$conference['program_schedule'].'</div>';
				echo '<hr>';
			}
			
			// Faculty
			if(!empty($conference['faculty'])){
				echo '<h3>'._FACULTY.'</h3>';
				echo '<div id="faculty">'.$conference['faculty'].'</div>';
				echo '<hr>';
			}
			
			// Accreditation
			if(!empty($conference['accreditation'])){
				echo '<h3>'._ACCREDITATION.'</h3>';
				echo '<div id="accreditation">'.$conference['accreditation'].'</div>';
				echo '<hr>';
			}
			
			// Cancellation Fee
			if(!empty($conference['course_cancellation_fee'])){
				echo '<h3>'._COURSE_CANCELLATION_FEE.'</h3>';
				echo '<div id="course_cancellation_fee">'.$conference['course_cancellation_fee'].'</div>';
				echo '<hr>';
			}
			
			// FAP
			if(!empty($conference['fap'])){
				echo '<h3>'._FAP.'</h3>';
				echo '<div id="fap">'.$conference['fap'].'</div>';
				echo '<hr>';
			}
			
			echo '<a class="form_button_middle next-button green" href="index.php?page=conference&cid='.$conference_id.'">'._CONFERENCE_REGESTRATION_LINK.'</a>';
			echo '<br><br>';
			echo '</div>';
			draw_content_end();
		}
	}
}
