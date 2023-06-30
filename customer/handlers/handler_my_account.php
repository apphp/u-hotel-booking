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

if(!$objLogin->IsLoggedInAsCustomer()){
	$objSession->SetMessage('notice', _MUST_BE_LOGGED);
    redirect_to('index.php?customer=login');
}else{
	
	$task = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
	$password_one = isset($_POST['password_one']) ? prepare_input($_POST['password_one']) : '';
	$password_two = isset($_POST['password_two']) ? prepare_input($_POST['password_two']) : '';
	$msg = '';
	
	$objCustomers = new Customers($objLogin->GetCustomerType(), 'me');
	
	if($task == 'change_password'){
		$msg = Customers::ChangePassword($password_one, $password_two);		
	}
}
