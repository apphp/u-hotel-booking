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

	if(!$objLogin->IsLoggedIn()){
		redirect_to('index.php?customer=login', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?customer=login">here</a>.</p>');
	}
	$current_customer_id = $objLogin->GetLoggedID();

	$car_id			 	 = isset($_POST['car_id']) ? prepare_input($_POST['car_id']) : '';
	$car_picking_up 	 = isset($_POST['picking_up']) ? prepare_input($_POST['picking_up']) : '';
	$car_dropping_off	 = isset($_POST['dropping_off']) ? prepare_input($_POST['dropping_off']) : '';
	$car_pick_up_date    = isset($_POST['pick_up_date_time']) ? prepare_input($_POST['pick_up_date_time']) : '';
	$car_drop_off_date	 = isset($_POST['drop_off_date_time']) ? prepare_input($_POST['drop_off_date_time']) : '';
	$additional_info	 = '';
	$discount_value		 = '';
	$vat_cost			 = '';
	$is_prepayment		 = false;
	$pre_payment_type    = isset($_POST['pre_payment_type']) ? prepare_input($_POST['pre_payment_type']) : '';
	$pre_payment_value   = ModulesSettings::Get('car_rental', 'pre_payment_value');
	$currency_rate		 = Application::Get('currency_code') != '' ? Application::Get('currency_rate') : 1;
	$vat_included_price  = ModulesSettings::Get('car_rental', 'vat_included_in_price');
	$get_currency_format = get_currency_format();
	$booking_mode		 = ModulesSettings::Get('car_rental', 'mode');
	$nl = "\n";


	if($vat_included_price == 'no'){
		$sql='SELECT
				cl.*,
				cd.name as country_name,
				c.vat_value
			FROM '.TABLE_CUSTOMERS.' cl
				LEFT OUTER JOIN '.TABLE_COUNTRIES.' count ON cl.b_country = c.abbrv AND c.is_active = 1
				LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.Application::Get('lang').'\'
			WHERE cl.id = '.(int)$current_customer_id;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$vat_percent = isset($result[0]['vat_value']) ? $result[0]['vat_value'] : '0';
		}else{
			$vat_percent = ModulesSettings::Get('car_rental', 'vat_value');
		}			
	}else{
		$vat_percent = '0';
	}		


	if(empty($car_id)){
		redirect_to('index.php?page=check_cars_availability', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=check_cars_availability">here</a>.</p>');
	}


	$car_where_clause = 'av.id = '.(int)$car_id.' AND ';

	$sql = 'SELECT
			av.id, 
			av.agency_id, 
			av.make, 
			av.model, 
			av.price_per_day, 
			av.price_per_hour, 
			av.default_distance, 
			av.distance_extra_price, 
			av.transmission,
			vt.image, 
			vt.image_thumb, 
			vt.passengers, 
			vt.luggages, 
			vt.doors
		FROM '.TABLE_CAR_AGENCY_VEHICLES.' av
			INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON av.vehicle_type_id = vt.id
		WHERE
			'.$car_where_clause.'
			av.is_active = 1 AND
			vt.is_active = 1
		LIMIT 1';
	$cars = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	if(is_array($cars) && $cars[1] > 0){
		$cars_count = $cars[1];
	}else{
		$cars_count = 0;
	}

	$car = $cars[0][0];

	if(empty($cars_count)){
		redirect_to('index.php?page=check_cars_availability', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=check_cars_availability">here</a>.</p>');
	}

	$pick_up_time  = !empty($car_pick_up_date)  ? strtotime($car_pick_up_date)  : '';
	$drop_off_time = !empty($car_drop_off_date) ? strtotime($car_drop_off_date) : '';

	$diff_time = $drop_off_time - $pick_up_time;
	// 1800 = 30 min (30 * 60)
	if($diff_time < 1800){
		redirect_to('index.php?page=check_cars_availability', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=check_cars_availability">here</a>.</p>');
	}

	$hours_to_seconds = $diff_time % (24 * 60 * 60);
	$days = ($diff_time - $hours_to_seconds) / (24 * 60 * 60);
	$hours = $hours_to_seconds / 3600;
	//$price = isset($_POST['price']) ? prepare_input($_POST['price']) : '';
	$price_per_day		 = $car['price_per_day'];
	$price_per_hour		 = $car['price_per_hour'];
	$price_total		 = $days * $price_per_day + $hours * $price_per_hour;

	if($pre_payment_type == 'percentage' && $pre_payment_value > '0' && $pre_payment_value < '100'){
		// sub price(%)
		$is_prepayment = true;			
		$cart_total = ($price_total * $pre_payment_value) / 100;
		$prepayment_text = $pre_payment_value.'%';
	}elseif($pre_payment_type == 'fixed sum' && $pre_payment_value > '0'){
		// sub fix price
		$is_prepayment = true;			
		$cart_total = round($pre_payment_value * $currency_rate, 2);
		$prepayment_text = _FIXED_SUM;  
	}else{
		$is_prepayment = false;
		$cart_total = $price_total;
		$prepayment_text = '';
	}					

	$custom			  = base64_encode($current_customer_id.'|'.$car_id.'|'.$car_picking_up.'|'.$car_dropping_off.'|'.$car_pick_up_date.'|'.$car_drop_off_date.'|'.$price_total.'|'.$cart_total);
	$extras_sub_total = 0;

	$booking_payment_output  = '<table align="center" border="0" width="97%">';
	if(!empty($car_picking_up) || !empty($car_dropping_off)){
		$lang = Application::Get('lang');
		$sql='SELECT * FROM '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' WHERE language_id = \''.$lang.'\' AND agency_location_id IN ('.(!empty($car_picking_up) ? (int)$car_picking_up.(!empty($car_dropping_off) ? ',' : '') : '').(!empty($car_dropping_off) ? (int)$car_dropping_off : '').')';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

		if($result[1] > 0){
			$all_locations = array();
			foreach($result[0] as $location){
				$all_locations[$location['agency_location_id']] = $location['name'];
			}

			if($all_locations[$car_picking_up]){
				$booking_payment_output .= '<tr><td width="20%">'._PICKING_UP.'</td><td width="2%"></td><td>'.$all_locations[$car_picking_up].'</td></tr>';
			}
			if($all_locations[$car_dropping_off]){
				$booking_payment_output .= '<tr><td width="20%">'._DROPPING_OFF.'</td><td width="2%"></td><td>'.$all_locations[$car_dropping_off].'</td></tr>';
			}
		}
	}

	$pick_up_month = date('m', $pick_up_time);
	$drop_off_month = date('m', $drop_off_time);
	if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
		$pick_up_date_format = get_month_local($pick_up_month).date(' d, Y (H:i)', $pick_up_time);
		$drop_off_date_format = get_month_local($drop_off_month).date(' d, Y (H:i)', $drop_off_time);
	}else{
		$pick_up_date_format = date('H:i d ', $pick_up_time).get_month_local($pick_up_month).date(' Y'.$pick_up_time);
		$drop_off_date_format = date('H:i d ', $drop_off_time).get_month_local($drop_off_month).date(' Y'.$drop_off_time);
	}

	$booking_payment_output .= '<tr><td width="20%">'._PICK_UP_DATE.'</td><td width="2%"></td><td>'.$pick_up_date_format.'</td></tr>';
	$booking_payment_output .= '<tr><td width="20%">'._DROP_OFF_DATE.'</td><td width="2%"></td><td>'.$drop_off_date_format.'</td></tr>';
	if($vat_included_price == 'no'){
		$booking_payment_output .= '<tr><td>'._VAT.' ('.Currencies::PriceFormat($vat_percent, '%', 'after', $get_currency_format, (substr($vat_percent, -1) == '0' ? 2 : 3)).') </td><td> : </td><td> '.Currencies::PriceFormat($vat_cost, '', '', $get_currency_format).'</td></tr>';
	}
	if($is_prepayment){
		$booking_payment_output .= '<tr><td>'._PAYMENT_SUM.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($price_total + $extras_sub_total + $vat_cost, '', '', $get_currency_format).'</b></td></tr>';
		$booking_payment_output .= '<tr><td>'._PRE_PAYMENT.'</td><td> : </td> <td>'.Currencies::PriceFormat($cart_total, '', '', $get_currency_format).' ('.$prepayment_text.')</td></tr>';
	}else{
		$booking_payment_output .= '<tr><td>'._PAYMENT_SUM.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($price_total + $extras_sub_total + $vat_cost, '', '', $get_currency_format).'</b></td></tr>';
		///echo '<tr><td>'._PRE_PAYMENT.'</td><td> : </td> <td>'._FULL_PRICE.'</td></tr>';
	}

	// prepare customers info 
	$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE id = '.(int)$current_customer_id;
	$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

	$customer_info = array();
	$customer_info['first_name'] = isset($result[0]['first_name']) ? $result[0]['first_name'] : '';
	$customer_info['last_name']  = isset($result[0]['last_name']) ? $result[0]['last_name'] : '';
	$customer_info['address1']   = isset($result[0]['b_address']) ? $result[0]['b_address'] : '';
	$customer_info['address2']   = isset($result[0]['b_address2']) ? $result[0]['b_address2'] : '';
	$customer_info['city']       = isset($result[0]['b_city']) ? $result[0]['b_city'] : '';
	$customer_info['state']      = isset($result[0]['b_state']) ? $result[0]['b_state'] : '';
	$customer_info['zip']        = isset($result[0]['b_zipcode']) ? $result[0]['b_zipcode'] : '';
	$customer_info['country']    = isset($result[0]['b_country']) ? $result[0]['b_country'] : '';
	$customer_info['email']      = isset($result[0]['email']) ? $result[0]['email'] : '';
	$customer_info['company']    = isset($result[0]['company']) ? $result[0]['company'] : '';
	$customer_info['phone']      = isset($result[0]['phone']) ? $result[0]['phone'] : '';
	$customer_info['fax']        = isset($result[0]['fax']) ? $result[0]['fax'] : '';
	$customer_info['balance']    = isset($result[0]['balance']) ? $result[0]['balance'] : 0;

	$paypal_email              = ModulesSettings::Get('car_rental', 'paypal_email');
	//$credit_card_required      = ModulesSettings::Get('car_rental', 'online_credit_card_required');
	//$two_checkout_vendor       = ModulesSettings::Get('car_rental', 'two_checkout_vendor');
	//$authorize_login_id        = ModulesSettings::Get('car_rental', 'authorize_login_id');
	//$authorize_transaction_key = ModulesSettings::Get('car_rental', 'authorize_transaction_key');
	$bank_transfer_info        = ModulesSettings::Get('car_rental', 'bank_transfer_info');
	$mode                      = ModulesSettings::Get('car_rental', 'mode');

	$pp_params = array(
		'api_login'       	=> '',
		'transaction_key' 	=> '',
		'payment_info'    	=> $bank_transfer_info,
		
		'booking_number'    => $custom,
		
		'address1'      	=> $customer_info['address1'],
		'address2'      	=> $customer_info['address2'],
		'city'          	=> $customer_info['city'],
		'zip'           	=> $customer_info['zip'],
		'country'       	=> $customer_info['country'],
		'state'         	=> $customer_info['state'],
		'first_name'    	=> $customer_info['first_name'],
		'last_name'     	=> $customer_info['last_name'],
		'email'         	=> $customer_info['email'],
		'company'       	=> $customer_info['company'],
		'phone'         	=> $customer_info['phone'],
		'fax'           	=> $customer_info['fax'],
		
		'notify'        	=> '',
		'return'        	=> 'index.php?page=cars_return',
		//'cancel_return' 	=> 'index.php?page=booking_cancel',
		'cancel_return' 	=> 'index.php?page=check_cars_availability',
		'cancel_button'		=> 'page=check_cars_availability',
					
		'paypal_form_type'   	   => '',
		'paypal_form_fields' 	   => '',
		'paypal_form_fields_count' => '',
		
		'credit_card_required' => '',
		'cc_type'              => '',
		'cc_holder_name'       => '',
		'cc_number'            => '',
		'cc_cvv_code'          => '',
		'cc_expires_month'     => '',
		'cc_expires_year'      => '',
		
		'currency_code'      => Application::Get('currency_code'),
		'additional_info'    => $additional_info,
		'discount_value'     => $discount_value,
		'extras_sub_total'   => $extras_sub_total,
		'vat_cost'           => $vat_cost,
		'cart_total' 		 => number_format((float)$cart_total, (int)Application::Get('currency_decimals'), '.', ','),
		'is_prepayment'      => $is_prepayment,
		'pre_payment_type'   => $pre_payment_type,
		'pre_payment_value'  => $pre_payment_value,				
		'account_balance'  	 => $customer_info['balance']
	);
	$pp_params['paypal_form_fields_count'] = 1;
	$pp_params['api_login']                = $paypal_email;
	$pp_params['notify']        		   = 'index.php?page=cars_notify_paypal';
	$pp_params['paypal_form_type']   	   = '';
	$pp_params['paypal_form_fields'] 	   = '';
	$pp_params['paypal_form_fields_count'] = 0;

	$online_credit_card_required = ModulesSettings::Get('car_rental', 'online_credit_card_required');
	$booking_mode = ModulesSettings::Get('car_rental', 'mode');

	$booking_payment_output .= '<tr><td colspan="3">'.PaymentIPN::DrawPaymentForm('paypal', $pp_params, (($booking_mode == 'TEST MODE') ? 'test' : 'real'), false).'</td></tr>';
	$booking_payment_output .= '</table>';
}
