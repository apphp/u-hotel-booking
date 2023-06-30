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

$allow_payment_with_balance = ModulesSettings::Get('booking', 'allow_payment_with_balance') == 'yes' ? true : false;

if($objLogin->IsLoggedInAsCustomer() && $objLogin->GetCustomerType() == 1 && $allow_payment_with_balance){

	$agency_id = $objLogin->GetLoggedID();
		
	$objCustomers = new Customers();
	$agency_info = $objCustomers->GetCustomerInfo($agency_id);

	draw_content_start();
	
	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_FUNDS_INFORMATION=>''))
	);
		

	if(count($agency_info) > 0){
		$objCustomerFunds = new CustomerFunds($agency_id, 'my_funds');
		$objCustomerFunds->DrawViewMode();
	}	
	
	draw_content_end();

}else{
	draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'')));
	draw_important_message(_NOT_AUTHORIZED);
}
