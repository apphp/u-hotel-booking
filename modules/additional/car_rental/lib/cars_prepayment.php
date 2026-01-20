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

if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
        
	$reservation_number = isset($_GET['rn']) ? prepare_input($_GET['rn']) : '';

	draw_title_bar(prepare_breadcrumbs(array(_CARS=>'')));
	
	draw_content_start();
	draw_reservation_bar('payment');

	// test mode alert
	if(ModulesSettings::Get('car_rental', 'mode') == 'TEST MODE'){
		echo draw_message(_TEST_MODE_ALERT_SHORT, false, true);
	}        
	
	if(empty($reservation_number)){
		draw_important_message(_WRONG_BOOKING_NUMBER);
	}else{

		$sql = 'SELECT
			IF(('.TABLE_CAR_AGENCY_RESERVATIONS.'.reservation_total_price + '.TABLE_CAR_AGENCY_RESERVATIONS.'.extras_fee + '.TABLE_CAR_AGENCY_RESERVATIONS.'.vat_fee - ('.TABLE_CAR_AGENCY_RESERVATIONS.'.reservation_paid + '.TABLE_CAR_AGENCY_RESERVATIONS.'.additional_payment) > 0),
				('.TABLE_CAR_AGENCY_RESERVATIONS.'.reservation_total_price + '.TABLE_CAR_AGENCY_RESERVATIONS.'.extras_fee + '.TABLE_CAR_AGENCY_RESERVATIONS.'.vat_fee - ('.TABLE_CAR_AGENCY_RESERVATIONS.'.reservation_paid + '.TABLE_CAR_AGENCY_RESERVATIONS.'.additional_payment)),
				0
			) as mod_have_to_pay
		FROM '.TABLE_CAR_AGENCY_RESERVATIONS.'
			LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.TABLE_CAR_AGENCY_RESERVATIONS.'.currency = '.TABLE_CURRENCIES.'.code
		WHERE '.TABLE_CAR_AGENCY_RESERVATIONS.'.reservation_number = \''.$reservation_number.'\'';

		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		$total_prepayment = ($result[1] > 0) ? $result[0]['mod_have_to_pay'] : '0';    

		$paypal_email = ModulesSettings::Get('booking', 'paypal_email');
		$currencyFormat = get_currency_format();		  
		$fieldDateFormat = ($objSettings->GetParameter('date_format') == 'mm/dd/yyyy') ? 'M d, Y' : 'd M, Y';
		$mode = ModulesSettings::Get('car_rental', 'mode');
		
		$pp_params = array(
			'api_login'       => '',
			'transaction_key' => '',
			'booking_number'  => $reservation_number,			
			
			//'address1'      => $customer_info['address1'],
			//'address2'      => $customer_info['address2'],
			//'city'          => $customer_info['city'],
			//'zip'           => $customer_info['zip'],
			//'country'       => $customer_info['country'],
			//'state'         => $customer_info['state'],
			//'first_name'    => $customer_info['first_name'],
			//'last_name'     => $customer_info['last_name'],
			//'email'         => $customer_info['email'],
			//'company'       => $customer_info['company'],
			//'phone'         => $customer_info['phone'],
			//'fax'           => $customer_info['fax'],
			
			'notify'        => '',
			'return'        => 'index.php?page=booking_car_return',
			'cancel_return' => 'index.php?page=booking_car_cancel',
						
			'paypal_form_type'   	   => '',
			'paypal_form_fields' 	   => '',
			'paypal_form_fields_count' => '',
			
			'credit_card_required' => '',
			'cc_type'             => '',
			'cc_holder_name'      => '',
			'cc_number'           => '',
			'cc_cvv_code'         => '',
			'cc_expires_month'    => '',
			'cc_expires_year'     => '',
			
			'currency_code'      => Application::Get('currency_code'),
			//'additional_info'    => $additional_info,
			//'discount_value'     => $discount_value,
			//'extras_param'       => $extras_param,
			//'extras_sub_total'   => $extras_sub_total,
			//'vat_cost'           => $vat_cost,
			'cart_total' 		 => number_format((float)$total_prepayment, (int)Application::Get('currency_decimals'), '.', ','),
			//'is_prepayment'      => $is_prepayment,
			//'pre_payment_type'   => $pre_payment_type,
			//'pre_payment_value'  => $pre_payment_value,				
		);

		$pp_params['api_login']                = $paypal_email;
		$pp_params['notify']        		   = 'index.php?page=booking_car_notify_paypal';
		//$pp_params['paypal_form_type']   	   = $this->paypal_form_type;
		//$pp_params['paypal_form_fields'] 	   = $this->paypal_form_fields;
		//$pp_params['paypal_form_fields_count'] = $this->paypal_form_fields_count;
		$pp_params['cancel_button'] = $objLogin->IsLoggedInAsAdmin() ? 'admin=mod_car_rental_reservations' : 'customer=my_car_rentals';
		
		echo '<table border="0" width="97%" align="center">
			<tr><td width="20%">'._BOOKING_DATE.' </td><td width="2%"> : </td><td> '.format_date(date('Y-m-d H:i:s'), $fieldDateFormat, '', true).'</td></tr>						
			<tr><td>'._BOOKING_PRICE.' </td><td width="2%"> : </td><td> '.Currencies::PriceFormat($total_prepayment, '', '', $currencyFormat).' [<a href="javascript:void(0);" onclick="javascript:$(\'#row-change\').toggle();"> '._BUTTON_CHANGE.' </a>]</td></tr>';

		echo PaymentIPN::DrawPaymentForm('paypal', $pp_params, $mode == "TEST MODE" ? 'test-editable' : 'real-editable', false);
		
		echo '</table><br />';

	}

	draw_content_end();
	
}else{
	draw_title_bar(_CARS);
	draw_important_message(_NOT_AUTHORIZED);
}	

