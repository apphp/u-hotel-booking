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

if(!$objLogin->IsLoggedIn()){

	$account_email = Session::Get('reset_account_email');
	$account_reset = (bool)Session::Get('account_reset');
	$msg = '';
	
	if($account_email != ''){
		if(Customers::ResetAccount($account_email)){
			$msg = draw_success_message(_ACCOUNT_SUCCESSFULLY_RESET, false);			
			Session::Set('account_reset', true);
		}else{
			$msg = draw_important_message(Customers::GetError(), false);					
		}
		Session::Set('reset_account_email', '');
	}else{
		if(!$account_reset){
			$msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);			
		}else{
			$msg = draw_message(_ACCOUNT_ALREADY_RESET, false);			
		}	
	}
   
	draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'',_RESET_ACCOUNT=>'')));
	echo $msg;	

}elseif($objLogin->IsLoggedIn()){
    draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
    draw_important_message(_NOT_AUTHORIZED);
}
