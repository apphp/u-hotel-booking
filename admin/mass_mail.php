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
	
	draw_title_bar(prepare_breadcrumbs(array(_MASS_MAIL_AND_TEMPLATES=>'',_MASS_MAIL=>'')));
	
	$objMassMail = new EmailTemplates();

	$task = (isset($_POST['task'])) ? prepare_input($_POST['task']) : '';
	
	if($task == 'send'){
		$objMassMail->SendMassMail();
	}
	
	$objMassMail->DrawMassMailForm();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
