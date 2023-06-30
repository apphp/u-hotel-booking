<?php

/**
 *	Class PaymentIPN
 *  -------------- 
 *  Description : encapsulates Payment IPN properties
 *	Written by  : ApPHP
 *	Version     : 1.0.2
 *  Updated	    : 03.02.2016
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             DrawPaymentForm         
 *	__destruct              DrawOnlineForm
 *  GetPaymentStatus        DrawPayPalPaymentForm  
 *  GetParameter            Draw2COPaymentForm
 *                          DrawAuthorizeNetPaymentForm
 *                          DrawBankTransferPaymentForm
 *                          DrawBalancePaymentForm
 *                          
 *  1.0.2
 *  	- added 2co type
 *  	- added online type
 *  	- added DrawBankTransferPaymentForm
 *  	-
 *  	-
 **/

class PaymentIPN {
	
	private $post_vars;
	private $response;
	private $timeout;

	private $error_email;
	private $pp_type;
	
	//==========================================================================
    // Class Constructor
	// 		@param $post_vars
	//==========================================================================
	function __construct($post_vars, $pp_type)
	{
		$this->post_vars = $post_vars;
		$this->pp_type = $pp_type;
		$this->timeout = 120;
	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }
	
	/**
	 *	Returns payment status
	 */
	public function GetPaymentStatus()
	{
		$payment_status = '';
		
		if($this->pp_type == 'paypal'){
			$payment_status = isset($this->post_vars['payment_status']) ? $this->post_vars['payment_status'] : '';
		}elseif($this->pp_type == '2co'){
			$payment_status = isset($this->post_vars['invoice_status']) ? $this->post_vars['invoice_status'] : '';
		}elseif($this->pp_type == 'authorize.net'){
			$payment_status = isset($this->post_vars['x_response_code']) ? $this->post_vars['x_response_code'] : '';
		}
		return $payment_status;
	}

	/**
	 *	Returns parameter
	 * @param $param
	 */
	public function GetParameter($param)
	{
		$param_value = '';
		
		if($this->pp_type == 'paypal'){
			$param_value = isset($this->post_vars[$param]) ? $this->post_vars[$param] : '';
		}elseif($this->pp_type == '2co'){
			$param_value = isset($this->post_vars[$param]) ? $this->post_vars[$param] : '';
		}elseif($this->pp_type == 'authorize.net'){
			$param_value = isset($this->post_vars[$param]) ? $this->post_vars[$param] : '';
		}		
		return $param_value;
	}

	////////////////////////////////////////////////////////////////////////////
	/**
	 * Draws Payment Form
	 * @param $pp_type
	 * @param $pp_params
	 * @param $mode
	 * @param $draw
	 */
	public static function DrawPaymentForm($pp_type, $pp_params = array(), $mode = 'real', $draw = true)
	{
		$output = '';
		if($pp_type == 'poa'){
			$output = self::DrawPoaForm($pp_params, $mode);
		}elseif($pp_type == 'online'){
			$output = self::DrawOnlineForm($pp_params, $mode);
		}elseif($pp_type == 'paypal'){
			$output = self::DrawPayPalPaymentForm($pp_params, $mode);
		}elseif($pp_type == '2co'){
			$output = self::Draw2COPaymentForm($pp_params, $mode);
		}elseif($pp_type == 'authorize.net'){
			$output = self::DrawAuthorizeNetPaymentForm($pp_params, $mode);
		}elseif($pp_type == 'bank.transfer'){
			$output = self::DrawBankTransferPaymentForm($pp_params, $mode);
		}elseif($pp_type == 'account.balance'){
			$output = self::DrawBalancePaymentForm($pp_params, $mode);
		}
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws Payment Form for Cach/Check on Arrival (POA)
	 * @param $pp_params
	 * @param $mode
	 */
	public static function DrawPoaForm($pp_params, $mode = 'real')
	{
		$nl = "\n";		
        if(defined('IS_WIDGET') && IS_WIDGET){
            $action = !empty($pp_params['notify']) ? WIDGET_HOST.$pp_params['notify'] : APPHP_BASE.'index.php?page=booking_payment&'.WIDGET_HASH;
            $url_cancel = (!empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details').'&'.WIDGET_HASH;
        }else{
            $action = !empty($pp_params['notify']) ? $pp_params['notify'] : 'index.php?page=booking_payment';
            $url_cancel = !empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details';
        }
		$output  = $nl.'<form action="'.$action.'" method="post">';
		$output .= $nl.draw_hidden_field('task', 'place_order', false);
		$output .= $nl.draw_hidden_field('payment_type', 'poa', false);
		$output .= $nl.draw_hidden_field('additional_info', $pp_params['additional_info'], false);
		$output .= $nl.draw_hidden_field('pre_payment_type', $pp_params['pre_payment_type'], false);
		$output .= $nl.draw_hidden_field('pre_payment_value', $pp_params['pre_payment_value'], false);
		$output .= $nl.draw_token_field(false);

		$output .= $nl.'<tr><td colspan="3" nowrap height="10px"></td></tr>';
		$output .= $nl.'<tr>';
		$output .= $nl.'<td colspan="3">';
		$output .= $nl.'<input type="submit" class="form_button" value="'._PLACE_ORDER.'" name="btnSubmit" />';
		$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
		$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_CANCEL.'</a>';
		$output .= $nl.'</td>';
		$output .= $nl.'</tr>';					
		$output .= $nl.'</form>';
		
		return $output;		
	}

	/**
	 * Draws Payment Form for Online
	 * @param $pp_params
	 * @param $mode
	 */
	public static function DrawOnlineForm($pp_params, $mode = 'real')
	{
		$nl = "\n";		
        if(defined('IS_WIDGET') && IS_WIDGET){
            $action = !empty($pp_params['notify']) ? WIDGET_HOST.$pp_params['notify'] : APPHP_BASE.'index.php?page=booking_payment&'.WIDGET_HASH;
            $url_cancel = (!empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details').'&'.WIDGET_HASH;
        }else{
            $action = !empty($pp_params['notify']) ? $pp_params['notify'] : 'index.php?page=booking_payment';
            $url_cancel = !empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details';
        }

		$output  = $nl.'<form action="'.$action.'" method="post" id="frmCrediCard">';
		$output .= $nl.draw_hidden_field('task', 'place_order', false);
		$output .= $nl.draw_hidden_field('payment_type', 'online', false);
		$output .= $nl.draw_hidden_field('additional_info', $pp_params['additional_info'], false);		
		$output .= $nl.draw_hidden_field('pre_payment_type', $pp_params['pre_payment_type'], false);
		$output .= $nl.draw_hidden_field('pre_payment_value', $pp_params['pre_payment_value'], false);
		$output .= $nl.draw_token_field(false);
		$output .= $nl.$pp_params['extras_param'];

		$req_mark = ($pp_params['credit_card_required'] == 'yes') ? '<span class="required">*</span>' : '';
		
		$cc_type        = $pp_params['cc_type'];
		$cc_holder_name = $pp_params['cc_holder_name'];
		$cc_number      = $pp_params['cc_number'];
		$cc_cvv_code    = $pp_params['cc_cvv_code'];
		$cc_expires_month = $pp_params['cc_expires_month'];
		$cc_expires_year = $pp_params['cc_expires_year'];

		$output .= $nl.'<tr><td colspan="3" nowrap height="10px"></td></tr>';
		$output .= $nl.'<tr><td colspan="3"><h4>'.str_replace("'", "\'", _CREDIT_CARD).'</h4></td></tr>';
		$output .= $nl.'<tr>';
		$output .= $nl.'	<td width="20%">'._CREDIT_CARD_TYPE.' '.$req_mark.'</td>';
		$output .= $nl.'	<td width="2%"> : </td>';
		$output .= $nl.'	<td>';
		$output .= $nl.'		<select class="my-form-control" name="cc_type">';
		$output .= $nl.'		<option value="Visa" '.(($cc_type == 'Visa') ? 'selected="selected"' : '').'>Visa</option>';
		$output .= $nl.'		<option value="MasterCard" '.(($cc_type == 'MasterCard') ? 'selected="selected"' : '').'>MasterCard</option>';
		$output .= $nl.'		<option value="American Express" '.(($cc_type == 'American Express') ? 'selected="selected"' : '').'>American Express</option>';
		$output .= $nl.'		<option value="Discover" '.(($cc_type == 'Discover') ? 'selected="selected"' : '').'>Discover</option>';
		$output .= $nl.'		</select>';
		$output .= $nl.'	</td>';
		$output .= $nl.'</tr>';
		$output .= $nl.'<tr>';
		$output .= $nl.'	<td>'._CREDIT_CARD_HOLDER_NAME.' '.$req_mark.'</td>';
		$output .= $nl.'	<td> : </td>';
		$output .= $nl.'	<td><input type="text" class="cc_holder_name" name="cc_holder_name" size="20" maxlength="50" value="'.$cc_holder_name.'" autocomplete="off" /></td>';
		$output .= $nl.'</tr>';
		$output .= $nl.'<tr>';
		$output .= $nl.'	<td>'._CREDIT_CARD_NUMBER.' '.$req_mark.'</td>';
		$output .= $nl.'	<td> : </td>';
		$output .= $nl.'	<td><input type="text" class="cc_number" name="cc_number" size="20" maxlength="20" value="'.$cc_number.'" autocomplete="off" /></td>';
		$output .= $nl.'</tr>';
		$output .= $nl.'<tr>';
		$output .= $nl.'	<td>'._CREDIT_CARD_EXPIRES.' '.$req_mark.'</td>';
		$output .= $nl.'	<td> : </td>';
		$output .= $nl.'	<td>';
		$output .= $nl.draw_months_select_box('cc_expires_month', $cc_expires_month, 'my-form-control cc_month', false, false);
		$output .= $nl.draw_years_select_box('cc_expires_year', $cc_expires_year, 'my-form-control cc_year', false);
		$output .= $nl.'	</td>';
		$output .= $nl.'</tr>';
		$output .= $nl.'<tr><td>'._CVV_CODE.' '.$req_mark.'</td>';
		$output .= $nl.'	<td> : </td>';
		$output .= $nl.'	<td><input type="text" class="cc_cvv_code" name="cc_cvv_code" size="4" maxlength="4" value="'.$cc_cvv_code.'" autocomplete="off" /> &nbsp;<a href="javascript:void(0)" onclick="javascript:appOpenPopup(\'html/cvv_description.html\')">'._WHAT_IS_CVV.' [?]</a></td>';
		$output .= $nl.'</tr>';
		$output .= $nl.'<tr><td colspan="3" nowrap height="10px"></td></tr>';

		$output .= $nl.'<tr>';
		$output .= $nl.'<td colspan="3">';
		$output .= $nl.'<input type="submit" class="form_button" value="'._PLACE_ORDER.'" name="btnSubmit" />';
		$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
		$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_CANCEL.'</a>';
		$output .= $nl.'</td>';
		$output .= $nl.'</tr>';
		$output .= $nl.'</form>';
		
		return $output;		
	}	
	
	/**
	 * Draws Payment Form for PayPal
	 * @param $pp_params
	 * @param $mode
	 */
	public static function DrawPayPalPaymentForm($pp_params, $mode = 'real')
	{
		global $objLogin;
		
		$nl = "\n";
		$output = '';
		
		//if($mode == 'test'){
		//	$output .= $nl.'<tr><td colspan="3" nowrap height="10px">';
		//	$output .= $nl.'<form action="index.php?page=booking_notify_paypal" method="post" name="payform">';
		//	$output .= $nl.draw_hidden_field('txn_id', 'TEST_'.get_random_string(8), false);
		//	$output .= $nl.draw_hidden_field('payer_status', 'verified', false);
		//	$output .= $nl.draw_hidden_field('mc_gross', round($pp_params['cart_total'], 2), false);
		//	$output .= $nl.draw_hidden_field('custom', $pp_params['booking_number'], false);
		//	$output .= $nl.draw_token_field(false);
		//	
		//	$output .= $nl.'<table width="99%" border="0">';
		//	$output .= $nl.'<tr>';
		//	$output .= $nl.'<td align="left">';
		//	$output .= $nl.'<br />'._PAYPAL_NOTICE.'<br /><br />';
		//	$output .= $nl.'<input type="image" style="border:0px" src="images/ppc_icons/btn_pp_paynow.gif" title="'._BOOK_NOW.'" value="Go To Payment" name="btnSubmit" />';
		//	$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
		//	$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\'page=booking_details\')">'._BUTTON_CANCEL.'</a>';
		//	$output .= $nl.'</td>';
		//	$output .= $nl.'</tr>';
		//	$output .= $nl.'</table>';
		//	$output .= $nl.'</form>';
		//	$output .= $nl.'</td></tr>';
		//}else{
		///}
		
		$output .= $nl.'<tr><td colspan="3" nowrap height="10px">';
		if($mode == 'test' || $mode == 'editable'){
			$output .= $nl.'<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" name="payform">';
		}
		else{
			$output .= $nl.'<form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="payform">';
		}			 
		$output .= $nl.draw_hidden_field('business', $pp_params['api_login'], false);

		if($pp_params['paypal_form_type'] == 'multiple' && !$pp_params['is_prepayment'] == 'multiple' && !$pp_params['discount_value']){
			$output .= $nl.draw_hidden_field('cmd', '_cart', false);
			$output .= $nl.draw_hidden_field('upload', '1', false);
			$output .= $pp_params['paypal_form_fields'];

			if($pp_params['extras_sub_total'] > 0){
				$pp_params['paypal_form_fields_count']++;					
				$output .= $nl.draw_hidden_field('item_name_'.$pp_params['paypal_form_fields_count'], _EXTRAS, false);
				$output .= $nl.draw_hidden_field('quantity_'.$pp_params['paypal_form_fields_count'], '1', false);
				$output .= $nl.draw_hidden_field('amount_'.$pp_params['paypal_form_fields_count'], number_format($pp_params['extras_sub_total'], '2', '.', ''), false);
			}									
			if($pp_params['vat_cost'] > 0){
				$pp_params['paypal_form_fields_count']++;
				$output .= $nl.draw_hidden_field('item_name_'.$pp_params['paypal_form_fields_count'], _VAT, false);
				$output .= $nl.draw_hidden_field('quantity_'.$pp_params['paypal_form_fields_count'], '1', false);
				$output .= $nl.draw_hidden_field('amount_'.$pp_params['paypal_form_fields_count'], number_format($pp_params['vat_cost'], '2', '.', ''), false);
			}									
		}else{
			$output .= $nl.draw_hidden_field('cmd', '_xclick', false);
			$output .= $nl.draw_hidden_field('item_name', !empty($pp_params['item_name']) ? $pp_params['item_name'] : 'Rooms Reservation', false);
			$output .= $nl.draw_hidden_field('item_number', !empty($pp_params['item_number']) ? $pp_params['item_number'] : '002', false);
			$output .= $nl.draw_hidden_field('amount', round($pp_params['cart_total'], 2), false);
			
			if($mode == 'real-editable' || 'test-editable'){
				$output .= '</td></tr>';
				$output .= '<tr id="row-change" style="display:none;"><td>'._BUTTON_CHANGE.': </td><td width="2%">:</td><td><input type="text" style="width:100px" maxlength="10" name="amount" value="'.round($pp_params['cart_total'], 2).'"></td></tr>';
				$output .= $nl.'<tr><td colspan="3" nowrap height="10px">';
				// $nl.draw_hidden_field('amount', round($pp_params['cart_total'], 2), false);
			}
		}
		$output .= $nl.draw_hidden_field('custom', $pp_params['booking_number'], false);
		$output .= $nl.draw_hidden_field('lc', 'US', false);
		$output .= $nl.draw_hidden_field('currency_code', $pp_params['currency_code'], false);
		$output .= $nl.draw_hidden_field('cn', '', false);
		$output .= $nl.draw_hidden_field('no_shipping', '1', false);
		$output .= $nl.draw_hidden_field('rm', '1', false);

		$output .= $nl.draw_hidden_field('address_override', '0', false);
		if(isset($pp_params['address1'])) $output .= $nl.draw_hidden_field('address1', $pp_params['address1'], false);
		if(isset($pp_params['address2'])) $output .= $nl.draw_hidden_field('address2', $pp_params['address2'], false);
		if(isset($pp_params['city'])) $output .= $nl.draw_hidden_field('city', $pp_params['city'], false);
		if(isset($pp_params['zip'])) $output .= $nl.draw_hidden_field('zip', $pp_params['zip'], false);
		if(isset($pp_params['country'])) $output .= $nl.draw_hidden_field('country', $pp_params['country'], false);			
		if(isset($pp_params['state'])) $output .= $nl.draw_hidden_field('state', $pp_params['state'], false);
		if(isset($pp_params['first_name'])) $output .= $nl.draw_hidden_field('first_name', $pp_params['first_name'], false);
		if(isset($pp_params['last_name'])) $output .= $nl.draw_hidden_field('last_name', $pp_params['last_name'], false);
		if(isset($pp_params['email'])) $output .= $nl.draw_hidden_field('email', $pp_params['email'], false);

		if(isset($pp_params['phone'])){
			$phone_parts = explode('-', $pp_params['phone']);
			if(isset($phone_parts[0])) $output .= $nl.draw_hidden_field('night_phone_a', $phone_parts[0], false);
			if(isset($phone_parts[1])) $output .= $nl.draw_hidden_field('night_phone_b', $phone_parts[1], false);
			if(isset($phone_parts[2])) $output .= $nl.draw_hidden_field('night_phone_c', $phone_parts[2], false);
		}

        if(defined('IS_WIDGET') && IS_WIDGET){
            $pp_params['cancel_return'] .= '&'.WIDGET_HASH;
            $output .= $nl.draw_hidden_field('notify', WIDGET_HOST.$pp_params['notify'], false);
            $output .= $nl.draw_hidden_field('return', APPHP_BASE.$pp_params['return'].'&'.WIDGET_HASH, false);
        }else{
            $output .= $nl.draw_hidden_field('notify', APPHP_BASE.$pp_params['notify'], false);
            $output .= $nl.draw_hidden_field('return', APPHP_BASE.$pp_params['return'], false);
        }
        $output .= $nl.draw_hidden_field('cancel_return', APPHP_BASE.$pp_params['cancel_return'], false);
		$output .= $nl.draw_hidden_field('bn', 'PP-BuyNowBF', false);
		
		$output .= $nl.'<br />'._PAYPAL_NOTICE.'<br /><br />';
		$output .= $nl.'<input type="image" style="border:0px" src="images/ppc_icons/btn_pp_paynow.gif" title="'._BOOK_NOW.'" value="Go To Payment" name="btnSubmit" />';
		$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
		if($mode == 'real-editable' || $mode == 'test-editable'){
			$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.(!empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : ($objLogin->IsLoggedInAsAdmin() ? 'admin=mod_booking_bookings' : 'customer=my_bookings')).'\')">'._BUTTON_CANCEL.'</a>';					
		}else{
			$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.(!empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details').'\')">'._BUTTON_CANCEL.'</a>';	
		}
		$output .= $nl.'</form>';
		$output .= $nl.'</td></tr>';

		return $output;
	}
	
	/**
	 * Draws Payment Form for 2CO
	 * @param $pp_params
	 * @param $mode
	 */
	public static function Draw2COPaymentForm($pp_params, $mode = 'real')
	{
		$nl = "\n";
        if(defined('IS_WIDGET') && IS_WIDGET){
            $action = !empty($pp_params['notify']) ? WIDGET_HOST.$pp_params['notify'] : APPHP_BASE.'index.php?page=booking_notify_2co&'.WIDGET_HASH;
            $url_cancel = (!empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details').'&'.WIDGET_HASH;
        }else{
            $action = !empty($pp_params['notify']) ? $pp_params['notify'] : 'index.php?page=booking_notify_2co';
            $url_cancel = !empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details';
        }

		$output = '';
		
		if($mode == 'test'){
			$output .= $nl.'<tr><td colspan="3" nowrap height="10px">';
			$output .= $nl.'<form action="'.$action.'" method="post">';
			$output .= $nl.draw_hidden_field('order_number', 'TEST_'.get_random_string(8), false);
			$output .= $nl.draw_hidden_field('pay_method', '2CO', false);
			$output .= $nl.draw_hidden_field('total', round($pp_params['cart_total'], 2), false);
			$output .= $nl.draw_hidden_field('custom', $pp_params['booking_number'], false);
			$output .= $nl.draw_token_field(false);
			$output .= $nl.'<table width="99%" border="0">';
			$output .= $nl.'<tr>';
			$output .= $nl.'<td>';
			$output .= $nl.'<br />'._2CO_NOTICE.'<br /><br />';
			$output .= $nl.'<input type="image" style="border:0px" src="images/ppc_icons/btn_2co_buynow.jpg" title="'._BOOK_NOW.'" value="Go To Payment" name="btnSubmit" />';
			$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
			$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_CANCEL.'</a>';
			$output .= $nl.'</td>';
			$output .= $nl.'</tr>';
			$output .= $nl.'</table>';
			$output .= $nl.'</form>';
			$output .= $nl.'</td></tr>';
		}else{
			$output .= $nl.'<tr><td colspan="3" nowrap height="10px">';
			$output .= $nl.'<form action="https://www.2checkout.com/checkout/purchase" method="post">';
			$output .= $nl.draw_hidden_field('sid', $pp_params['api_login'], false);
			$output .= $nl.draw_hidden_field('cart_order_id', 'Rooms Reservation', false);
			$output .= $nl.draw_hidden_field('total', (round($pp_params['cart_total'] * Application::Get('currency_rate'), 2)), false);
            if(defined('IS_WIDGET') && IS_WIDGET){
                $output .= $nl.draw_hidden_field('x_Receipt_Link_URL', WIDGET_HOST.$pp_params['notify'], false);
                $output .= $nl.draw_hidden_field('return_url', APPHP_BASE.$pp_params['return'].'&'.WIDGET_HASH, false);
            }else{
                $output .= $nl.draw_hidden_field('x_Receipt_Link_URL', APPHP_BASE.$pp_params['notify'], false);
                $output .= $nl.draw_hidden_field('return_url', APPHP_BASE.$pp_params['return'], false);
            }
			$output .= $nl.draw_hidden_field('tco_currency', Application::Get('currency_code'), false);
			$output .= $nl.draw_hidden_field('custom', $pp_params['booking_number'], false);
			$output .= $nl.'<br />'._2CO_NOTICE.'<br /><br />';
			$output .= $nl.'<input type="image" style="border:0px" src="images/ppc_icons/btn_2co_buynow.jpg" title="'._BOOK_NOW.'" value="Go To Payment" name="btnSubmit" />';
			$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
			$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_CANCEL.'</a>';
			$output .= $nl.'</form>';
			$output .= $nl.'</td></tr>';
		}		
		return $output;
	}
	
	/**
	 * Draws Payment Form for Authorize.Net
	 * @param $pp_params
	 * @param $mode
	 */
	public static function DrawAuthorizeNetPaymentForm($pp_params, $mode = 'real')
	{
		$nl = "\n";
        if(defined('IS_WIDGET') && IS_WIDGET){
            $action = !empty($pp_params['notify']) ? WIDGET_HOST.$pp_params['notify'] : APPHP_BASE.'index.php?page=booking_notify_autorize_net&'.WIDGET_HASH;
            $url_cancel = (!empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details').'&'.WIDGET_HASH;
        }else{
            $action = !empty($pp_params['notify']) ? $pp_params['notify'] : 'index.php?page=booking_payment';
            $url_cancel = !empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details';
        }

		$output = '';
		
		if($mode == 'test'){
			$output .= $nl.'<tr><td colspan="3" nowrap height="10px">';
			$output .= $nl.'<form action="'.$action.'" method="post">';
			$output .= $nl.draw_hidden_field('x_trans_id', 'TEST_'.get_random_string(8), false);
			$output .= $nl.draw_hidden_field('x_method', '1', false);
			$output .= $nl.draw_hidden_field('x_amount', round($pp_params['cart_total'], 2), false);
			$output .= $nl.draw_hidden_field('custom', $pp_params['booking_number'], false);
			$output .= $nl.draw_token_field(false);
			$output .= $nl.'<table width="99%" border="0">';
			$output .= $nl.'<tr>';
			$output .= $nl.'<td>';
			$output .= $nl.'<br />'._AUTHORIZE_NET_NOTICE.'<br /><br />';
			$output .= $nl.'<input type="image" style="border:0px" src="images/ppc_icons/btn_authorize_buynow.gif" title="'._SUBMIT_PAYMENT.'" value="Go To Payment" name="btnSubmit" />';
			$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
			$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_CANCEL.'</a>';
			$output .= $nl.'</td>';
			$output .= $nl.'</tr>';
			$output .= $nl.'</table>';
			$output .= $nl.'</form>';
			$output .= $nl.'</td></tr>';
		}else{
			// <!-- Create the HTML form containing necessary SIM post values -->
			// <!--  Additional fields can be added here as outlined in the SIM integration guide at: http://developer.authorize.net -->
			//$url = 'https://test.authorize.net/gateway/transact.dll';
			$url = "https://secure.authorize.net/gateway/transact.dll";			
			
			$testMode	= 'false';
			// an invoice is generated using the date and time
			$invoice	= date('YmdHis');
			// a sequence number is randomly generated
			$sequence	= rand(1, 1000);
			// a timestamp is generated
			$timeStamp	= time();
			// The following lines generate the SIM fingerprint.  PHP versions 5.1.2 and
			// newer have the necessary hmac function built in.  For older versions, it
			// will try to use the mhash library.
			if( phpversion() >= '5.1.2' ){
				$fingerprint = hash_hmac('md5', $pp_params['api_login'].'^'.$sequence.'^'.$timeStamp.'^'.round($pp_params['cart_total'], 2) . '^', $pp_params['transaction_key']);
			}else{
				$fingerprint = bin2hex(mhash(MHASH_MD5, $pp_params['api_login'].'^'.$sequence.'^'.$timeStamp.'^'.round($pp_params['cart_total'], 2).'^', $pp_params['transaction_key']));
			}

			$output .= $nl.'<tr><td colspan="3" nowrap height="10px">';
			$output .= $nl.'<form method="post" action="'.$url.'">';
			$output .= $nl.draw_hidden_field('x_login', $pp_params['api_login'], false);
			$output .= $nl.draw_hidden_field('x_amount', round($pp_params['cart_total'], 2), false);
			$output .= $nl.draw_hidden_field('x_description', 'Rooms Reservation', false);
			$output .= $nl.draw_hidden_field('x_invoice_num', $invoice, false);
			$output .= $nl.draw_hidden_field('x_fp_sequence', $sequence, false);
			$output .= $nl.draw_hidden_field('x_fp_timestamp', $timeStamp, false);
			$output .= $nl.draw_hidden_field('x_fp_hash', $fingerprint, false);
			$output .= $nl.draw_hidden_field('x_test_request', $testMode, false);
			$output .= $nl.draw_hidden_field('x_relay_response', 'TRUE', false);
            if(defined('IS_WIDGET') && IS_WIDGET){
                $output .= $nl.draw_hidden_field('x_relay_url', WIDGET_HOST.$pp_params['notify'], false);
            }else{
                $output .= $nl.draw_hidden_field('x_relay_url', APPHP_BASE.$pp_params['notify'], false);
            }
			$output .= $nl.draw_hidden_field('x_show_form', 'PAYMENT_FORM', false);
			$output .= $nl.draw_hidden_field('custom', $pp_params['booking_number'], false);
			
			$output .= $nl.draw_hidden_field('x_first_name', $pp_params['first_name'], false);
			$output .= $nl.draw_hidden_field('x_last_name', $pp_params['last_name'], false);
			$output .= $nl.draw_hidden_field('x_company', $pp_params['company'], false);
			$output .= $nl.draw_hidden_field('x_phone', $pp_params['phone'], false);
			$output .= $nl.draw_hidden_field('x_fax', $pp_params['fax'], false);
			$output .= $nl.draw_hidden_field('x_email', $pp_params['email'], false);
			$output .= $nl.draw_hidden_field('x_address', $pp_params['address1'].' '.$pp_params['address2'], false);
			$output .= $nl.draw_hidden_field('x_city', $pp_params['city'], false);
			$output .= $nl.draw_hidden_field('x_zip', $pp_params['zip'], false);
			$output .= $nl.draw_hidden_field('x_country', $pp_params['country'], false);
			$output .= $nl.draw_hidden_field('x_state', $pp_params['state'], false);
		
			$output .= $nl.'<br />'._AUTHORIZE_NET_NOTICE.'<br /><br />';
			$output .= $nl.'<input type="image" style="border:0px" src="images/ppc_icons/btn_authorize_buynow.gif" title="'._SUBMIT_PAYMENT.'" value="Go To Payment" name="btnSubmit" />';
			$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
			$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_CANCEL.'</a>';
			$output .= $nl.'</form>';
			$output .= $nl.'</td></tr>';			
		}
		return $output;
	}
	
	/**
	 * Draws Payment Form for Bank Transfer
	 * @param $pp_params
	 * @param $mode
	 */
	public static function DrawBankTransferPaymentForm($pp_params, $mode = 'real')
	{
		$nl = "\n";
        if(defined('IS_WIDGET') && IS_WIDGET){
            $action = !empty($pp_params['notify']) ? WIDGET_HOST.$pp_params['notify'] : APPHP_BASE.'index.php?page=booking_payment&'.WIDGET_HASH;
            $url_cancel = (!empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details').'&'.WIDGET_HASH;
        }else{
            $action = !empty($pp_params['notify']) ? $pp_params['notify'] : 'index.php?page=booking_payment';
            $url_cancel = !empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details';
        }

		$output = '';
		
		$bank_transfer_info = isset($pp_params['payment_info']) ? $pp_params['payment_info'] : '';
		if(empty($pp_params['payment_info'])){
			$bank_transfer_info = ModulesSettings::Get($pp_params['module'], 'bank_transfer_info');		
		}
	
		$output  = $nl.'<form action="'.$action.'" method="post">';
		$output .= $nl.draw_hidden_field('task', 'place_order', false);
		$output .= $nl.draw_hidden_field('payment_type', 'bank.transfer', false);
		$output .= $nl.draw_hidden_field('additional_info', $pp_params['additional_info'], false);		
		$output .= $nl.draw_hidden_field('pre_payment_type', $pp_params['pre_payment_type'], false);
		$output .= $nl.draw_hidden_field('pre_payment_value', $pp_params['pre_payment_value'], false);
		$output .= $nl.draw_token_field(false);
		$output .= $nl.$pp_params['extras_param'];

		$output .= $nl.'<tr><td colspan="3" nowrap height="20px"></td></tr>';
		$output .= $nl.'<tr><td colspan="3"><h4>'._BANK_PAYMENT_INFO.'</h4></td></tr>';
		$output .= $nl.'<tr><td colspan="3" nowrap height="5px"></td></tr>';
		$output .= $nl.'<tr><td colspan="3" wrap height="10px">'.nl2br($bank_transfer_info).'</td></tr>';
		$output .= $nl.'<tr><td colspan="3" nowrap height="10px"></td></tr>';

		$output .= $nl.'<tr>';
		$output .= $nl.'<td colspan="3">';
		$output .= $nl.'<input type="submit" class="form_button" value="'._PLACE_ORDER.'" name="btnSubmit" />';
		$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
		$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_CANCEL.'</a>';
		$output .= $nl.'</td>';
		$output .= $nl.'</tr>';					
		$output .= $nl.'</form>';
		
		return $output;		
		
	}	

	/**
	 * Draws Payment Form by account balance
	 * @param $pp_params
	 * @param $mode
	 */
	public static function DrawBalancePaymentForm($pp_params, $mode = 'real')
	{
		$nl = "\n";
        if(defined('IS_WIDGET') && IS_WIDGET){
            $action = !empty($pp_params['notify']) ? WIDGET_HOST.$pp_params['notify'] : APPHP_BASE.'index.php?page=booking_payment&'.WIDGET_HASH;
            $url_cancel = (!empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details').'&'.WIDGET_HASH;
        }else{
            $action = !empty($pp_params['notify']) ? $pp_params['notify'] : 'index.php?page=booking_payment';
            $url_cancel = !empty($pp_params['cancel_button']) ? $pp_params['cancel_button'] : 'page=booking_details';
        }

		$output = '';
		
		// Check if enough money on account balance
		if(($pp_params['account_balance'] * Application::Get('currency_rate')) < $pp_params['cart_total']){			
			$output .= $nl.'<tr><td colspan="3" nowrap height="10px"></td></tr>';
			$output .= $nl.'<tr>';
			$output .= $nl.'<td colspan="3">';
			$output .= $nl.draw_important_message(_NOT_ENOUGH_MONEY_ALERT.' '.Currencies::GetDefaultCurrency().$pp_params['account_balance'], false);
			$output .= $nl.'</td>';
			$output .= $nl.'</tr>';					
			$output .= $nl.'<tr>';
			$output .= $nl.'<td colspan="3">';
			$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_BACK.'</a>';
			$output .= $nl.'</td>';
			$output .= $nl.'</tr>';
		}else{
			$output .= $nl.'<form action="'.$action.'" method="post">';
			$output .= $nl.draw_hidden_field('task', 'place_order', false);
			$output .= $nl.draw_hidden_field('payment_type', 'account.balance', false);
			$output .= $nl.draw_hidden_field('additional_info', $pp_params['additional_info'], false);
			$output .= $nl.draw_hidden_field('pre_payment_type', $pp_params['pre_payment_type'], false);
			$output .= $nl.draw_hidden_field('pre_payment_value', $pp_params['pre_payment_value'], false);
			$output .= $nl.draw_token_field(false);
	
			$output .= $nl.'<tr><td colspan="3" nowrap height="10px"></td></tr>';
			$output .= $nl.'<tr>';
			$output .= $nl.'<td colspan="3">';
			$output .= $nl.'<input type="submit" class="form_button" value="'._PLACE_ORDER.'" name="btnSubmit" />';
			$output .= $nl.'&nbsp; - '._OR.' - &nbsp;';
			$output .= $nl.'<a href="javascript:void(0);" onclick="javascript:appGoTo(\''.$url_cancel.'\')">'._BUTTON_CANCEL.'</a>';
			$output .= $nl.'</td>';
			$output .= $nl.'</tr>';					
			$output .= $nl.'</form>';			
		}
		
		return $output;		
	}
	
}

