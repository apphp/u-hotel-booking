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

			$tuition = ConferenceTuitions::GetConferenceTuitions($conference_id);
			$currency_rate = Application::Get('currency_rate');
			$currency_format = get_currency_format();

			draw_content_start();
			echo '<h2>'.$conference['conference_type_name'].'</h2>';
			echo $action_message;
			if(!empty($tuition[1])){
				echo '<h3>'._COURSE_REGISTRATION.'</h3>';
				echo '<table width="100%">';
				echo '<thead>';
				echo '<th width="40%">'._TITLE.'</th>';
				echo '<th width="20%">'._CONFERENCE_TYPE_1.'</th>';
				echo '<th width="20%">'._CONFERENCE_TYPE_2.'</th>';
				echo '<th width="20%">'._CONFERENCE_TYPE_3.'</th>';
				echo '</thead>';
				for($i = 0; $i < $tuition[1]; $i++){
					echo '<tr>';
					echo '<td>'.$tuition[0][$i]['name'].'</td>';
					echo '<td>'.Currencies::PriceFormat(($tuition[0][$i]['price_type_1'] * $currency_rate), '', '', $currency_format).'</td>';
					echo '<td>'.Currencies::PriceFormat(($tuition[0][$i]['price_type_2'] * $currency_rate), '', '', $currency_format).'</td>';
					echo '<td>'.Currencies::PriceFormat(($tuition[0][$i]['price_type_3'] * $currency_rate), '', '', $currency_format).'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}
			
			if(!empty($conference['course_cancellation_fee'])){
				echo '<h3>'._COURSE_CANCELLATION_FEE.'</h3>';
				echo '<p>'.$conference['course_cancellation_fee'].'</p>';
			}
			
			echo '<hr>';
			echo '<a class="form_button_middle next-button green" href="index.php?page=conference_registration&step=1&cid='.$conference_id.'">'._CONFERENCE_REGESTRATION_LINK.'</a>';
			draw_content_end();
		}
	}
}
