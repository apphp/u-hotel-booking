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
	$is_prepayment		 = 'multiple';
	$pre_payment_type	 = '';
	$pre_payment_value	 = '';
	$order_price		 = 0;
	$vat_percent		 = 0;

	$lang = Application::Get('lang');

	$class_left	 = Application::Get('defined_left');
	$class_right = Application::Get('defined_right');
	
	$pre_payment_value      = ModulesSettings::Get('car_rental', 'pre_payment_value');
	$payment_type_paypal    = ModulesSettings::Get('car_rental', 'payment_type_paypal');
	$vat_included_in_price  = ModulesSettings::Get('car_rental', 'vat_included_in_price');
	$pre_payment_type       = ModulesSettings::Get('car_rental', 'pre_payment_type');
	$pre_payment_type_post  = isset($_POST['pre_payment_type']) ? prepare_input($_POST['pre_payment_type']) : $pre_payment_type;

	$nl = "\n";

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
	$cars = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
	if(!is_array($cars) || $cars[1] == 0){
		redirect_to('index.php?page=check_cars_availability', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=check_cars_availability">here</a>.</p>');
	}

	$car = $cars[0];

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
	$price				 = $days * $price_per_day + $hours * $price_per_hour;

	$payment_type_paypal = ($payment_type_paypal == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Frontend Only')) ? 'yes' : 'no';
	$payment_type_cnt = ($payment_type_paypal === 'yes');
	$payment_types_defined  = true;
	
	if($objLogin->IsLoggedInAsAdmin() && $this->selectedUser == 'admin'){
		$table_name = TABLE_ACCOUNTS;
		$sql = 'SELECT '.$table_name.'.*
				FROM '.$table_name.'
				WHERE '.$table_name.'.id = '.(int)$current_customer_id;
	}else{
		$table_name = TABLE_CUSTOMERS;
		$sql='SELECT
				'.$table_name.'.*,
				cd.name as country_name,
				c.vat_value,
				IF(st.name IS NOT NULL, st.name, '.$table_name.'.b_state) as b_state
			FROM '.$table_name.'
				LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.$table_name.'.b_country = c.abbrv AND c.is_active = 1
				LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
				LEFT OUTER JOIN '.TABLE_STATES.' st ON '.$table_name.'.b_state = st.abbrv AND st.country_id = c.id AND st.is_active = 1
			WHERE '.$table_name.'.id = '.(int)$current_customer_id;				  
	}
	$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
	if($result[1] <= 0){
		draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
		return false;
	}

		echo '<form id="checkout-form" action="index.php?page=cars_payment" method="post">
		'.draw_token_field(false);
		echo '<input type="hidden" name="car_id" value="'.$car_id.'" />';
		echo '<input type="hidden" name="picking_up" value="'.$car_picking_up.'" />';
		echo '<input type="hidden" name="dropping_off" value="'.$car_dropping_off.'" />';
		echo '<input type="hidden" name="pick_up_date_time" value="'.$car_pick_up_date.'" />';
		echo '<input type="hidden" name="drop_off_date_time" value="'.$car_drop_off_date.'" />';
			
		echo '<table class="reservation_cart" border="0" width="99%" align="center" cellspacing="0" cellpadding="5">
		<tr>
			<td colspan="2"><h4>'._BILLING_DETAILS.'</h4></td>
		</tr>
		<tr>
			<td style="padding-left:10px;">
				'._FIRST_NAME.': '.$result[0]['first_name'].'<br />
				'._LAST_NAME.': '.$result[0]['last_name'].'<br />';				
				if(!$objLogin->IsLoggedInAsAdmin()){					
					echo _ADDRESS.': '.$result[0]['b_address'].'<br />';
					echo _ADDRESS_2.': '.$result[0]['b_address_2'].'<br />';
					echo _CITY.': '.$result[0]['b_city'].'<br />';
					echo _ZIP_CODE.': '.$result[0]['b_zipcode'].'<br />';
					echo _COUNTRY.': '.$result[0]['country_name'].'<br />';
					echo _STATE.': '.$result[0]['b_state'].'<br />';
				}				
			echo '</td>
			<td></td>
		</tr>
		</table><br />';

		echo '<table class="reservation_cart" border="0" width="99%" align="center" cellspacing="0" cellpadding="5">
		<tr><td colspan="5"><h4>'._RESERVATION_DETAILS.'</h4></td></tr>
		<tr class="header">
			<th class="'.$class_left.'" width="40px">&nbsp;</th>
			<th align="'.$class_left.'">'._CAR_TYPE.'</th>
			<th align="center">'._FROM.'</th>
			<th align="center">'._TO.'</th>
			<th class="center" width="80px" style="text-align:right;">'._PRICE.'</th>
		</tr>
		<tr><td colspan="5" nowrap="nowrap" height="5px"></td></tr>';

		$car_icon_thumb = ($car['image_thumb'] != '') ? $car['image_thumb'] : 'no_image.png';
		echo '<tr>
				<td><img src="images/vehicle_types/'.$car_icon_thumb.'" alt="icon" width="32px" height="32px" /></td>							
				<td>
					<b>'.prepare_link('car', 'car_id', $car['id'], $car['make'].$car['model'], $car['make'].$car['model'], '', _CLICK_TO_VIEW).'</b><br>
				</td>							
				<td align="'.$class_left.'">'.get_month_local(date('m', strtotime($car_pick_up_date))).date(' d, Y (H:i)', strtotime($car_pick_up_date)).'</td>
				<td align="'.$class_left.'">'.get_month_local(date('m', strtotime($car_drop_off_date))).date(' d, Y (H:i)', strtotime($car_drop_off_date)).'</td>							
				<td align="'.$class_right.'">'.Currencies::PriceFormat($price * Application::Get('currency_rate'), '', '', get_currency_format()).'&nbsp;</td>
			</tr>
			<tr><td colspan="5" nowrap height="3px"></td></tr>';
		$order_price += ($price * Application::Get('currency_rate'));
	
	
	// calculate percent
	$vat_cost = (($order_price) * ($vat_percent / 100));
	$cart_total = ($order_price) + $vat_cost;

	if($vat_included_in_price == 'no'){
		echo '<tr>
				<td colspan="3"></td>
				<td class="td" colspan="1" align="'.$class_left.'"><b>'._VAT.': ('.Currencies::PriceFormat($vat_percent, '%', 'after', get_currency_format(), (substr($vat_percent, -1) == 0 ? 2 : 3)).')</b></td>
				<td class="td" align="'.$class_right.'">
					<b><label id="reservation_vat">'.Currencies::PriceFormat($vat_cost, '', '', get_currency_format()).'</label></b>
				</td>
			 </tr>';
	}
	echo '<tr><td colspan="5" nowrap height="5px"></td></tr>
		 <tr class="footer">
			<td colspan="3"></td>
			<td class="td" colspan="1" align="'.$class_left.'"><b>'._TOTAL.':</b></td>
			<td class="td" align="'.$class_right.'">
				<b><label id="reservation_total">'.Currencies::PriceFormat($cart_total, '', '', get_currency_format()).'</label></b>
			</td>
		 </tr>';

	// PAYMENT DETAILS
	// ------------------------------------------------------------
	echo '<tr><td colspan="5" nowrap height="12px"></td></tr>';
	echo '<tr><td colspan="5"><h4>'._PAYMENT_DETAILS.'</h4></td></tr>';
	echo '<tr><td colspan="5">';
	echo '<table border="0" width="100%">';
	echo '<tr><td width="130px" nowrap>'._PAYMENT_TYPE.': &nbsp;</td><td> 
	<select name="payment_type" class="form-control payment_type" id="payment_type">';
		echo '<option value="paypal" '.(($payment_type == 'paypal') ? 'selected="selected"' : '').'>'._PAYPAL.'</option>';
	echo '</select>';
	echo '</td></tr>';
	echo '<tr>';

	if($pre_payment_type == 'percentage' && $pre_payment_value > '0' && $pre_payment_value < '100'){
		echo '<td>'._PAYMENT_METHOD.': </td>';
		echo '<td>';
		echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked" /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
		echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="percentage" /> <label for="pre_payment_partially">'._PRE_PAYMENT.' ('.Currencies::PriceFormat($pre_payment_value, '%', 'after', get_currency_format()).')</label>';
		echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
		echo '</td>';
	}elseif($pre_payment_type == 'fixed sum' && $pre_payment_value > '0'){
		echo '<td>'._PAYMENT_METHOD.': </td>';
		echo '<td>';
		echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked" /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
		echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="fixed sum" /> <label for="pre_payment_partially">'._PRE_PAYMENT.' ('.Currencies::PriceFormat($pre_payment_value * $this->currencyRate, '', '', $this->currencyFormat).')</label>';
		echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
		echo '</td>';
	}else{
		echo '<td colspan="2">';
		// full price payment
		if($payment_type_cnt <= 1 && $payment_types_defined) echo _FULL_PRICE;
		echo draw_hidden_field('pre_payment_type', 'full price', false, 'pre_payment_fully');
		echo draw_hidden_field('pre_payment_value', '100', false, 'pre_payment_full');
		echo '</td>';
	}					
	echo '<tr><td colspan="5" nowrap height="15px"></td></tr>
		  <tr valign="middle">
			<td colspan="3" align="'.$class_right.'"></td>
			<td align="'.$class_right.'" colspan="2">
				'.(($payment_types_defined) ? '<input class="form_button" type="submit" value="'._SUBMIT_BOOKING.'" />' : '').' 
			</td>
		</tr>';
	echo '</table>';
	echo '<input type="hidden" name="hid_vat_percent" value="'.$vat_percent.'" />';
	echo '<input type="hidden" name="hid_order_price" value="'.$order_price.'" />';
	echo '<input type="hidden" name="hid_currency_symbol" value="'.Application::Get('currency_symbol').'" />';
	echo '<input type="hidden" name="hid_currency_format" value="'.get_currency_format().'" />';
	echo '</form><br>';

	echo '</tr>';			
	echo '</table></td></tr>';
}
