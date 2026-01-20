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
			draw_content_start();
			Conferences::DrawRegestrationProgress($step > 1 ? $step - 1 : 1, $activeStep > 1 ? $activeStep - 1 : 1);
			echo '<h2 class="center">'.$conference['conference_type_name'].'</h2>';
			echo '<h3 class="center">'.$conference['hotel_location_name'].'</h3>';
			echo '<h4 class="center">'.date(get_date_format(), strtotime($conference['start_date'])).' - '.date(get_date_format(), strtotime($conference['finish_date'])).'</h4>';
			echo '<form action="index.php?page=conference_registration&step='.($activeStep + 1).'&cid='.$conference_id.'" id="frmConferenceSpecialty" method="post">';
			echo draw_token_field(false);
			echo draw_hidden_field('act', 'send', false);
			echo $action_message;
			echo $fields;
			if($activeStep > 1) echo '<a class="form_button_middle previous" href="index.php?page=conference_registration&step='.(int)($activeStep - 1).'&cid='.$conference_id.'">'.$text_button_previous.'</a>';
			echo '<input class="form_button_middle next-button green" type="submit" value="'.$text_button_next.'"/>';
			echo '</form>';
			draw_content_end();
		}
	}
}
