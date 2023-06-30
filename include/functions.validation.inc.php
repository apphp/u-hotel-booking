<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// VALIDATION FUNCTIONS 24.11.2010

/**
 * Check email address
 * @param $email
 */
function check_email_address($email) {
	$strict = false;
	$regex = $strict ? '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' :  '/^([*+!.&#$�\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,7})$/i';
	if (preg_match($regex, trim($email))) {
	   return true;
	} else {
	   return false;
	}    
}

/**
 * Check date address
 * @param $date
 */
function check_date($date, $allow_empty_value = true)
{
	if($allow_empty_value && is_empty_date($date)) return true;
	$year  = (int)substr($date, 0, 4);
	$month = (int)substr($date, 5, 2);
	$day   = (int)substr($date, 8, 2);	
	if(checkdate($month, $day, $year)){		
	   return true;
	}else{
	   return false;
	}    
}

/**
 * Integer Validation
 */
function check_integer($field = '')
{
	if(is_numeric($field) === true){
		if((int)$field == $field){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

/**
 * Check creadit card (last update 30.04.2012)
 */
function check_credit_card($cc_params)
{	
	$cards = array(
		array('name' => 'Visa', 'length' => '13,16', 'prefixes' => '4', 'checkdigit' => true, 'test' => '4111111111111111'),
		array('name' => 'MasterCard', 'length' => '16', 'prefixes' => '51,52,53,54,55', 'checkdigit' => true, 'test' => '5555555555554444'),
		array('name' => 'American Express', 'length' => '15', 'prefixes' => '34,37', 'checkdigit' => true, 'test' => '371449635398431'),
		array('name' => 'Discover', 'length' => '16', 'prefixes' => '6011,622,64,65', 'checkdigit' => true, 'test' => '6011111111111117')
	);
	
    $ccErrors[0] = '';   // No errors
    $ccErrors[1] = _CC_UNKNOWN_CARD_TYPE; 
    $ccErrors[2] = _CC_NO_CARD_NUMBER_PROVIDED;
    $ccErrors[3] = _CC_CARD_INVALID_FORMAT;
    $ccErrors[4] = _CC_CARD_INVALID_NUMBER;
    $ccErrors[5] = _CC_CARD_WRONG_LENGTH; 
	$ccErrors[6] = _CC_CARD_NO_CVV_NUMBER; 
	$ccErrors[7] = _CC_CARD_WRONG_EXPIRE_DATE;
	$ccErrors[8] = _CC_CARD_HOLDER_NAME_EMPTY;
	
	// check card holder's name
	if(trim($cc_params['cc_holder_name']) == '') return $ccErrors[8]; 
              
    // define card type
    $ccType = -1;
    for($i=0; $i<sizeof($cards); $i++){
		if(strtolower($cc_params['cc_type']) == strtolower($cards[$i]['name'])){
			$ccType = $i;
			break;
		}
    }  
	if($ccType == -1) return $ccErrors[1];  
	if(strlen($cc_params['cc_number']) == 0) return $ccErrors[2]; 
	$ccNumber = str_replace(' ', '', $cc_params['cc_number']);  
	
	// Check that the number is numeric and of the right sort of length.
	if(!preg_match('/^[0-9]{13,19}$/i',$ccNumber)){
		return $ccErrors[3]; 
	}
	
	// Check that the number is not a test number
	if(SITE_MODE != 'development' && ($cards[$ccType]['test'] == $ccNumber)){
		return  $ccErrors[4]; 
	}
	
	// check the modulus 10 check digit - if required
	if($cards[$ccType]['checkdigit']){
		$checksum = 0;     // checksum total
		$j = 1;
		
		// handle each digit starting from the right
		for($i = strlen($ccNumber) - 1; $i >= 0; $i--){
			$calc = $ccNumber[$i] * $j;
			// if the result is in two digits add 1 to the checksum total
			if($calc > 9){
				$checksum = $checksum + 1;
				$calc = $calc - 10;
			}
			$checksum = $checksum + $calc;
			// switch j
			if($j ==1) {$j = 2;} else {$j = 1;};
		} 
		
		// if checksum is divisible by 10, it is a valid modulus 10 oe error occured
		if($checksum % 10 != 0) return $ccErrors[4]; 
	}  
  
	// prepare array with the valid prefixes for this card
	$prefix = explode(',',$cards[$ccType]['prefixes']);
		
	// check if any of them match what we have in the card number  
	$is_prefix_valid = false; 
	for ($i=0; $i<sizeof($prefix); $i++) {
		$exp = '^'.$prefix[$i];
		if(preg_match('/'.$exp.'/i',$ccNumber)) {
			$is_prefix_valid = true;
			break;
		}
	}
		
	// if there is no valid prefix the length is wrong
	if(!$is_prefix_valid){
		return $ccErrors[5];
	}
	  
	// check if the length is valid
	$is_length_valid = false;
	$lengths = explode(',',$cards[$ccType]['length']);
	for($j=0; $j<sizeof($lengths); $j++){
		if(strlen($ccNumber) == $lengths[$j]){
			$is_length_valid = true;
			break;
		}
	}
	
	if(!$is_length_valid){
		return $ccErrors[5];
	}

	// check expire date
	if($cc_params['cc_expires_year'].$cc_params['cc_expires_month'] < date('Ym')){
		return $ccErrors[7];
	}
	
	// check cvv number
	if($cc_params['cc_cvv_code'] == ''){
		return $ccErrors[6];
	}	

	// The credit card is in the required format.
	return $ccErrors[0]; 
}

/**
 * Check whether property type is valid
 * @param $property_type_id 
*/
function is_valid_property_type($property_type_id = '')
{
	$is_valid = false;
	$property_types = Application::Get('property_types');
	foreach($property_types as $key => $val){
		if($property_type_id == $val['id']){
			$is_valid = true;
			break;
		}
	}
	
	return $is_valid;
}

/**
 * Checks if a given parameter is an integer value
 * @param mixed $value
 * @return boolean
 */
function is_integer_value($value)
{
	return is_numeric($value) ? intval($value) == $value : false;
}

/**
 * Checks if a given parameter is a date value
 * @param mixed $value
 * @return boolean
 */
function is_date($value)
{
	$year = (int)substr($value, 0, 4);
	$month = (int)substr($value, 5, 2);
	$day = (int)substr($value, 8, 2);
	if(strtotime($value) == strtotime($year.'-'.$month.'-'.$day)){
		return checkdate($month, $day, $year);
	}else{
		$date = strtotime($value);        
		return (!empty($date) && is_integer_value($date));            
	}
}

/**
 * Checks if a given parameter is a valid URL
 * @param string $url
 * @return boolean
 */
function is_url($url = '')
{
	return (preg_match('/(http:\/\/|https:\/\/|ftp:\/\/)/i', $url) && filter_var($url, FILTER_VALIDATE_URL)) ? true : false;
}

/**
 * Checks if a given parameter is a valid alpha dashed value
 * @param string $val
 * @return boolean
 */
function is_alpha_dashed($val = '')
{
	if(preg_match('/[^a-zA-z_\-]/',$val)){
		return false;
	}else{
		return true;
	}
}

/**
 * Checks if a given parameter is a valid alphanumeric
 * @param string $val
 * @return boolean
 */
function is_alpha_numeric($val = '')
{
    if(function_exists('ctype_alnum') && ctype_alnum($val)){
        return true;
    }elseif(preg_match('/[^a-zA-z0-9_\-]/',$val)){
        return false;
    }else{
        return true;
    }
}

/**
 * Checks if a given datetime has empty value
 * @param string $datetime
 * @return boolean
 */
function is_empty_date($date)
{
	return (empty($date) || $date == '0000-00-00') ? true : false;
}

/**
 * Checks if a given datetime has empty value
 * @param string $datetime
 * @return boolean
 */
function is_empty_datetime($dateTime)
{
	return (empty($dateTime) || $dateTime == '0000-00-00 00:00:00') ? true : false;
}

/**
 * Checks if we're on mobile device
 * @return boolean
 */
function is_mobile(){
    if(isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])){
		$user_ag = $_SERVER['HTTP_USER_AGENT'];
		if(preg_match('/(Mobile|Android|Tablet|GoBrowser|[0-9]x[0-9]*|uZardWeb\/|Mini|Doris\/|Skyfire\/|iPhone|Fennec\/|Maemo|Iris\/|CLDC\-|Mobi\/)/uis',$user_ag)){
			return true;
		}else{
			return false;
		}
    }else{
		return false;    
    }
}

/**
 * Returns whether there is an AJAX (XMLHttpRequest) request
 * @return boolean
 */
function is_ajax()
{
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
