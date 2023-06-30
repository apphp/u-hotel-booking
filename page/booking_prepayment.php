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

if(Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
	   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
	  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
	){
        
        $booking_number = isset($_GET['bn']) ? prepare_input($_GET['bn']) : '';

		draw_title_bar(prepare_breadcrumbs(array(_BOOKINGS=>'')));
		
		draw_content_start();
		draw_reservation_bar('payment');
        
        if(empty($booking_number)){
            draw_important_message(_WRONG_BOOKING_NUMBER);
        }else{
            
            $sql = 'SELECT
				IF((('.TABLE_BOOKINGS.'.order_price - '.TABLE_BOOKINGS.'.discount_fee - '.TABLE_BOOKINGS.'.guests_discount) + '.TABLE_BOOKINGS.'.initial_fee + '.TABLE_BOOKINGS.'.guest_tax + '.TABLE_BOOKINGS.'.extras_fee + '.TABLE_BOOKINGS.'.vat_fee - ('.TABLE_BOOKINGS.'.payment_sum + '.TABLE_BOOKINGS.'.additional_payment) > 0),
					(('.TABLE_BOOKINGS.'.order_price - '.TABLE_BOOKINGS.'.discount_fee - '.TABLE_BOOKINGS.'.guests_discount) + '.TABLE_BOOKINGS.'.initial_fee + '.TABLE_BOOKINGS.'.guest_tax + '.TABLE_BOOKINGS.'.extras_fee + '.TABLE_BOOKINGS.'.vat_fee - ('.TABLE_BOOKINGS.'.payment_sum + '.TABLE_BOOKINGS.'.additional_payment)),
					0
                ) as mod_have_to_pay
			FROM '.TABLE_BOOKINGS.'
				LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.TABLE_BOOKINGS.'.currency = '.TABLE_CURRENCIES.'.code
			WHERE '.TABLE_BOOKINGS.'.booking_number = \''.$booking_number.'\'';

            $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
            $total_prepayment = ($result[1] > 0) ? $result[0]['mod_have_to_pay'] : '0';    

            $paypal_email = ModulesSettings::Get('booking', 'paypal_email');
            $currencyFormat = get_currency_format();		  
            $fieldDateFormat = ($objSettings->GetParameter('date_format') == 'mm/dd/yyyy') ? 'M d, Y' : 'd M, Y';
            
			$pp_params = array(
				'api_login'       => '',
				'transaction_key' => '',
				'booking_number'  => $booking_number,			
				
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
				'return'        => 'index.php?page=booking_return',
				'cancel_return' => 'index.php?page=booking_cancel',
							
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
            $pp_params['notify']        		   = 'index.php?page=booking_notify_paypal';
            //$pp_params['paypal_form_type']   	   = $this->paypal_form_type;
            //$pp_params['paypal_form_fields'] 	   = $this->paypal_form_fields;
            //$pp_params['paypal_form_fields_count'] = $this->paypal_form_fields_count;
            
			echo '<table border="0" width="97%" align="center">
				<tr><td width="20%">'._BOOKING_DATE.' </td><td width="2%"> : </td><td> '.format_date(date('Y-m-d H:i:s'), $fieldDateFormat, '', true).'</td></tr>						
				<tr><td>'._BOOKING_PRICE.' </td><td width="2%"> : </td><td> '.Currencies::PriceFormat($total_prepayment, '', '', $currencyFormat).' [<a href="javascript:void(0);" onclick="javascript:$(\'#row-change\').toggle();"> '._BUTTON_CHANGE.' </a>]</td></tr>';

            echo PaymentIPN::DrawPaymentForm('paypal', $pp_params, 'real-editable', false);
            
            echo '</table><br />';

        }

		draw_content_end();
		
	}else{
		draw_title_bar(_BOOKINGS);
		draw_important_message(_NOT_AUTHORIZED);
	}	
}else{
	draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);
}

