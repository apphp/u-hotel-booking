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

if(!$objLogin->IsLoggedIn() && ModulesSettings::Get('customers', 'allow_registration') == 'yes'){

	include_once('modules/captcha/securimage.php');
	$objImg = new Securimage();
	
	$act 		  = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
	$send_updates = isset($_POST['send_updates']) ? prepare_input($_POST['send_updates']) : '1';
	$first_name   = isset($_POST['first_name']) ? prepare_input($_POST['first_name']) : '';
	$last_name    = isset($_POST['last_name']) ? prepare_input($_POST['last_name']) : '';

	$birth_date_year = isset($_POST['birth_date__nc_year']) ? prepare_input($_POST['birth_date__nc_year']) : '';
	$birth_date_month = isset($_POST['birth_date__nc_month']) ? prepare_input($_POST['birth_date__nc_month']) : '';
	$birth_date_day = isset($_POST['birth_date__nc_day']) ? prepare_input($_POST['birth_date__nc_day']) : '';
	$birth_date = $birth_date_year.'-'.$birth_date_month.'-'.$birth_date_day;
	$birth_date = $birth_date != '--' ? $birth_date : '';
	$birth_date_in_sql = ($birth_date == '') ? 'NULL' : "'".$birth_date."'";
	
	$company     = isset($_POST['company']) ? prepare_input($_POST['company']) : '';
	$b_address   = isset($_POST['b_address']) ? prepare_input($_POST['b_address']) : '';
	$b_address_2 = isset($_POST['b_address_2']) ? prepare_input($_POST['b_address_2']) : '';
	$b_city      = isset($_POST['b_city']) ? prepare_input($_POST['b_city']) : '';
	$b_zipcode   = isset($_POST['b_zipcode']) ? prepare_input($_POST['b_zipcode']) : '';
	$b_country   = isset($_POST['b_country']) ? prepare_input($_POST['b_country']) : '';
	$b_state     = isset($_POST['b_state']) ? prepare_input($_POST['b_state'], false, 'extra') : '';
	$phone       = isset($_POST['phone']) ? prepare_input($_POST['phone']) : '';
	$fax         = isset($_POST['fax']) ? prepare_input($_POST['fax']) : '';
	$email       = isset($_POST['email']) ? prepare_input($_POST['email']) : '';
	$user_name   = isset($_POST['user_name']) ? prepare_input($_POST['user_name']) : '';
	$user_password1 = isset($_POST['user_password1']) ? prepare_input($_POST['user_password1']) : '';
	$user_password2 = isset($_POST['user_password2']) ? prepare_input($_POST['user_password2']) : '';
	$agree       = isset($_POST['agree']) ? prepare_input($_POST['agree']) : '';
	$user_ip     = get_current_ip();			
	$focus_field = 'first_name';

	$reg_confirmation = ModulesSettings::Get('customers', 'reg_confirmation');
	$image_verification_allow = ModulesSettings::Get('customers', 'image_verification_allow');
	$admin_alert_new_registration = ModulesSettings::Get('customers', 'admin_alert_new_registration');

	$msg_default = draw_message(_ACCOUNT_CREATE_MSG, false);
	$msg = '';

	$account_created = false;
	
	if($act == 'create'){
		$captcha_code = isset($_POST['captcha_code']) ? prepare_input($_POST['captcha_code']) : '';
		
		if($first_name == ''){
			$msg = draw_important_message(_FIRST_NAME_EMPTY_ALERT, false);
			$focus_field = 'first_name';
		}elseif($last_name == ''){
			$msg = draw_important_message(_LAST_NAME_EMPTY_ALERT, false);
			$focus_field = 'last_name';
		}elseif($birth_date != '' && !check_date($birth_date)){
			$msg = draw_important_message(_BIRTH_DATE_VALID_ALERT, false);
		}elseif($b_address == ''){
			$msg = draw_important_message(_ADDRESS_EMPTY_ALERT, false);
			$focus_field = 'b_address';
		}elseif($b_city == ''){
			$msg = draw_important_message(_CITY_EMPTY_ALERT, false);
			$focus_field = 'b_city';
		//}elseif($b_zipcode == ''){
		//	$msg = draw_important_message(_ZIPCODE_EMPTY_ALERT, false);
		//	$focus_field = 'b_zipcode';
		}elseif($b_country == ''){
			$msg = draw_important_message(_COUNTRY_EMPTY_ALERT, false);
			$focus_field = 'b_country';
		}elseif($phone == ''){
			$msg = draw_important_message(_PHONE_EMPTY_ALERT, false);
			$focus_field = 'phone';
		}elseif($email == ''){
			$msg = draw_important_message(_EMAIL_EMPTY_ALERT, false);
			$focus_field = 'email';
		}elseif($email != '' && !check_email_address($email)){
			$msg = draw_important_message(_EMAIL_VALID_ALERT, false);
			$focus_field = 'email';
		}elseif($user_name == ''){
			$msg = draw_important_message(_USERNAME_EMPTY_ALERT, false);
			$focus_field = 'frmReg_user_name';
		}elseif(($user_name != '') && (strlen($user_name) < 4)){
			$msg = draw_important_message(_USERNAME_LENGTH_ALERT, false);
			$focus_field = 'frmReg_user_name';
		}elseif($user_password1 == ''){
			$msg = draw_important_message(_PASSWORD_IS_EMPTY, false);
			$user_password1 = $user_password2 = '';			
			$focus_field = 'frmReg_user_password1';
		}elseif(($user_password1 != '') && (strlen($user_password1) < 6)){
			$msg = draw_important_message(_PASSWORD_IS_EMPTY, false);
			$user_password1 = $user_password2 = '';			
			$focus_field = 'frmReg_user_password1';
		}elseif(($user_password1 != '') && ($user_password2 == '')){			
			$msg = draw_important_message(_CONF_PASSWORD_IS_EMPTY, false);
			$user_password1 = $user_password2 = '';			
			$focus_field = 'frmReg_user_password1';
		}elseif(($user_password1 != '') && ($user_password2 != '') && ($user_password1 != $user_password2)){			
			$msg = draw_important_message(_CONF_PASSWORD_MATCH, false);
			$user_password1 = $user_password2 = '';			
			$focus_field = 'frmReg_user_password1';
		}elseif($agree == ''){
			$msg = draw_important_message(_CONFIRM_TERMS_CONDITIONS, false);
		}elseif($image_verification_allow == 'yes' && !$objImg->check($captcha_code)){			
			$msg = draw_important_message(_WRONG_CODE_ALERT, false);			
			$focus_field = 'frmReg_captcha_code';
		}

		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$msg = draw_important_message(_OPERATION_BLOCKED, false);
		}				

		// check if user IP or email don't blocked
		if($msg == ''){
			if($objLogin->IpAddressBlocked($user_ip)) $msg = draw_important_message(_IP_ADDRESS_BLOCKED, false);
			else if($objLogin->EmailBlocked($email)) $msg = draw_important_message(_EMAIL_BLOCKED, false);			
		}

		if($msg == ''){
			// check if email already exists                    
			$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE email = \''.encode_text($email).'\'';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				if($result[0]['user_name'] != ''){
					$msg = draw_important_message(_USER_EMAIL_EXISTS_ALERT, false);	
				}else{
					Session::Set('reset_account_email', $result[0]['email']);
					$msg = draw_important_message(_NO_USER_EMAIL_EXISTS_ALERT, false);						
				}					
			}else{
				// check if user already exists
				$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE user_name != \'\' AND user_name = \''.encode_text($user_name).'\'';
				$result = database_query($sql, DATA_AND_ROWS);
				if($result[1] > 0){
					$msg = draw_important_message(_USER_EXISTS_ALERT, false);
				}					
			}
		}
		
		if($msg == ''){			
			if($reg_confirmation == 'by email'){
				$registration_code = strtoupper(get_random_string(19));
				$is_active = '0';
			}elseif($reg_confirmation == 'by admin'){
				$registration_code = strtoupper(get_random_string(19));
				$is_active = '0';
			}else{
				$registration_code = '';
				$is_active = '1';
			}
			
			if(!PASSWORDS_ENCRYPTION){
				$user_password = '\''.encode_text($user_password1).'\'';
			}else{
				if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){					
					$user_password = 'AES_ENCRYPT(\''.encode_text($user_password1).'\', \''.PASSWORDS_ENCRYPT_KEY.'\')';
				}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
					$user_password = 'MD5(\''.encode_text($user_password1).'\')';
				}
			}
			
			// insert new user
			$sql = 'INSERT INTO '.TABLE_CUSTOMERS.'(
						first_name, last_name, birth_date, company,
						b_address, b_address_2, b_city,  b_zipcode, b_country, b_state,
						phone, fax, email, url,
						user_name, user_password, preferred_language,
						date_created, registered_from_ip, last_logged_ip,
						email_notifications, is_active, is_removed, comments, registration_code)
					VALUES(
						\''.encode_text($first_name).'\', \''.encode_text($last_name).'\', '.$birth_date_in_sql.', \''.encode_text($company).'\',
						\''.encode_text($b_address).'\', \''.encode_text($b_address_2).'\', \''.encode_text($b_city).'\', \''.encode_text($b_zipcode).'\', \''.encode_text($b_country).'\', \''.encode_text($b_state).'\',
						\''.encode_text($phone).'\', \''.encode_text($fax).'\', \''.encode_text($email).'\', \'\',
						\''.encode_text($user_name).'\', '.$user_password.', \''.Application::Get('lang').'\',
						\''.date('Y-m-d H:i:s').'\', \''.encode_text($user_ip).'\', \'\',
						\''.(int)$send_updates.'\', \''.(int)$is_active.'\', 0, \'\', \''.encode_text($registration_code).'\')';
			if(database_void_query($sql) > 0){
		
				////////////////////////////////////////////////////////////
				if($reg_confirmation == 'by email'){
					$email_template = 'new_account_created_confirm_by_email';					
				}elseif($reg_confirmation == 'by admin'){
					$email_template = 'new_account_created_confirm_by_admin';
				}else{
					$email_template = 'new_account_created';
				}
				send_email(
					$email,
					$objSettings->GetParameter('admin_email'),
					$email_template,
					array(
						'{FIRST NAME}' => $first_name,
						'{LAST NAME}'  => $last_name,
						'{USER NAME}'  => $user_name,
						'{USER PASSWORD}' => $user_password1,
						'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
						'{REGISTRATION CODE}' => $registration_code,
						'{BASE URL}'   => APPHP_BASE,
						'{YEAR}' 	   => date('Y')
					)
				);

				if($admin_alert_new_registration == 'yes'){
					send_email(
						$objSettings->GetParameter('admin_email'),
						$objSettings->GetParameter('admin_email'),
						'new_account_created_notify_admin',
						array(
							'{FIRST NAME}' => $first_name,
							'{LAST NAME}'  => $last_name,
							'{USER NAME}'  => $user_name,
							'{USER EMAIL}' => $email,							
							'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
							'{BASE URL}'   => APPHP_BASE,
							'{YEAR}' 	   => date('Y')
						)
					);
				}
				////////////////////////////////////////////////////////////
		
				if($reg_confirmation == 'by email'){
					$msg = draw_success_message(_ACCOUNT_CREATED_CONF_BY_EMAIL_MSG, false);
					$msg .= '<br />'.draw_message(_ACCOUT_CREATED_CONF_LINK, false);				
				}elseif($reg_confirmation == 'by admin'){
					$msg = draw_success_message(_ACCOUNT_CREATED_CONF_BY_ADMIN_MSG, false);
					$msg .= '<br />'.draw_message(_ACCOUT_CREATED_CONF_LINK, false);
				}else{
					$msg = draw_success_message(_ACCOUNT_CREATED_NON_CONFIRM_MSG, false);
					$msg .= '<br />'.draw_message(_ACCOUNT_CREATED_NON_CONFIRM_LINK, false);
				}

				$account_created = true;
				
			}else{
				$msg = draw_important_message(_CREATING_ACCOUNT_ERROR, false);
			}                    		
		}		
	}
}

