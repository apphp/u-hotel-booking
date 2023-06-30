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

draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'', _REGISTRATION_CONFIRMATION=>'')));

if(!$objLogin->IsLoggedIn() && (ModulesSettings::Get('customers', 'allow_registration') == 'yes')){
    
	echo $msg;
	
	echo '<div class="pages_contents">';
	if(!$confirmed){
		echo '<br />
		<form action="index.php?customer=confirm_registration" method="post" name="frmConfirmCode" id="frmConfirmCode">
			'.draw_token_field(false).'
			'.draw_hidden_field('task', 'post_submission', false).'
			
			'._ENTER_CONFIRMATION_CODE.':			
			<input type="text" name="c" id="c" value="" size="27" maxlength="25" /><br /><br />
			<input class="form_button" type="submit" name="btnSubmit" id="btnSubmit" value="Submit">			
		</form>
		<script type="text/javascript">appSetFocus("c")</script>';
	}
	echo '</div>';

}else{
    draw_important_message(_NOT_AUTHORIZED);
}

