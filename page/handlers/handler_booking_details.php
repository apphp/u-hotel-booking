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

		$m = isset($_REQUEST['m']) ? prepare_input($_REQUEST['m']) : '';
		$act = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
		
		if(!$objLogin->IsLoggedIn()){
			$current_customer_id = (int)Session::Get('current_customer_id'); 
            Session::Set('last_visited', 'index.php?page=booking_details');
		}else{
			$current_customer_id = $objLogin->GetLoggedID();
		}
		
		$objReservation = new Reservation(); 
		//--------------------------------------------------------------------------
		// *** redirect if reservation cart is empty or not correct reserved number of rooms
		if($objReservation->IsCartEmpty() || !$objReservation->IsCorrectReservedNumberRooms()){
			redirect_to('index.php?page=booking', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=booking">here</a>.</p>');
		}
			
		//--------------------------------------------------------------------------
		// *** redirect if account is created (if not from checkout shipping)
		if($current_customer_id != '' && $m != 'edit'){
			redirect_to('index.php?page=booking_checkout&m=3', '', '<p>if your browser doesn\'t support redirection please click <a href="index.php?page=booking_checkout&m=3">here</a>.</p>');
		}
		
		$allow_booking_without_account = ModulesSettings::Get('booking', 'allow_booking_without_account');
		$image_verification_allow = ModulesSettings::Get('customers', 'image_verification_allow');
		
		include_once('modules/captcha/securimage.php');
		$objImg = new Securimage();
		
		$send_updates = isset($_POST['send_updates']) ? (int)$_POST['send_updates'] : '1';
		$first_name  = isset($_POST['first_name']) ? prepare_input($_POST['first_name']) : '';
		$last_name   = isset($_POST['last_name']) ? prepare_input($_POST['last_name']) : '';
		
		$birth_date_year = !empty($_POST['birth_date__nc_year']) ? prepare_input($_POST['birth_date__nc_year']) : '';
		$birth_date_month = !empty($_POST['birth_date__nc_month']) ? prepare_input($_POST['birth_date__nc_month']) : '';
		$birth_date_day = !empty($_POST['birth_date__nc_day']) ? prepare_input($_POST['birth_date__nc_day']) : '';
		$birth_date = $birth_date_year.'-'.$birth_date_month.'-'.$birth_date_day;
		$birth_date_in_sql = ($birth_date == '--') ? 'NULL' : "'".$birth_date."'";
		
		$company     = isset($_POST['company']) ? prepare_input($_POST['company']) : '';
		$b_address   = isset($_POST['b_address']) ? prepare_input($_POST['b_address']) : '';
		$b_address_2 = isset($_POST['b_address_2']) ? prepare_input($_POST['b_address_2']) : '';
		$b_city      = isset($_POST['b_city']) ? prepare_input($_POST['b_city']) : '';
		$b_zipcode   = isset($_POST['b_zipcode']) ? prepare_input($_POST['b_zipcode']) : '';
		$b_country   = isset($_POST['b_country']) ? prepare_input($_POST['b_country']) : '';
		$b_state     = isset($_POST['b_state']) ? prepare_input($_POST['b_state']) : '';
		$phone       = isset($_POST['phone']) ? prepare_input($_POST['phone']) : '';
		$fax         = isset($_POST['fax']) ? prepare_input($_POST['fax']) : '';
		$email       = isset($_POST['email']) ? prepare_input($_POST['email']) : '';
		$user_name   = isset($_POST['user_name']) ? prepare_input($_POST['user_name']) : '';
		$user_password1 = isset($_POST['user_password1']) ? prepare_input($_POST['user_password1']) : '';
		$user_password2 = isset($_POST['user_password2']) ? prepare_input($_POST['user_password2']) : '';
		$agree       = isset($_POST['agree']) ? (int)$_POST['agree'] : '';	
		$captcha_code = isset($_POST['captcha_code']) ? prepare_input($_POST['captcha_code']) : '';
		$focus_field  = 'first_name';
		
		$msg_default = draw_message(_ACCOUNT_CREATE_MSG, false);
		$msg = '';
		
		$account_created = false;
		
		if($act == 'create' || $act == 'update')
		{
			if($first_name == ''){
				$msg = draw_important_message(_FIRST_NAME_EMPTY_ALERT, false);
				$focus_field = 'first_name';
			}elseif($last_name == ''){
				$msg = draw_important_message(_LAST_NAME_EMPTY_ALERT, false);
				$focus_field = 'last_name';
			}elseif($birth_date != '--' && !check_date($birth_date)){
				$msg = draw_important_message(_BIRTH_DATE_VALID_ALERT, false);
				$focus_field = 'birth_date';
			}elseif($b_address == ''){
				$msg = draw_important_message(_ADDRESS_EMPTY_ALERT, false);
				$focus_field = 'b_address';
			}elseif($b_city == ''){
				$msg = draw_important_message(_CITY_EMPTY_ALERT, false);
				$focus_field = 'b_city';
//			}elseif($b_zipcode == ''){
//				$msg = draw_important_message(_ZIPCODE_EMPTY_ALERT, false);
//				$focus_field = 'b_zipcode';
			}elseif($b_country == ''){
				$msg = draw_important_message(_COUNTRY_EMPTY_ALERT, false);
				$focus_field = 'b_country';
			}elseif($phone == ''){
				$msg = draw_important_message(_PHONE_EMPTY_ALERT, false);
				$focus_field = 'phone';
			}elseif($email == ''){
				$msg = draw_important_message(_EMAIL_EMPTY_ALERT, false);
				$focus_field = 'email';
			}elseif(($email != '') && (!check_email_address($email))){
				$msg = draw_important_message(_EMAIL_VALID_ALERT, false);
				$focus_field = 'email';
			}else{
				if($allow_booking_without_account == 'no' && $m != 'edit'){
					if($user_name == ''){
						$msg = draw_important_message(_USERNAME_EMPTY_ALERT, false);
						$focus_field = 'user_name';
					}elseif(($user_name != '') && (strlen($user_name) < 6)){
						$msg = draw_important_message(_USERNAME_LENGTH_ALERT, false);
						$focus_field = 'user_name';
					}elseif($user_password1 == ''){
						$msg = draw_important_message(_PASSWORD_IS_EMPTY, false);					
						$user_password1 = $user_password2 = '';
						$focus_field = 'user_password1';
					}elseif(($user_password1 != '') && (strlen($user_password1) < 6)){
						$msg = draw_important_message(_PASSWORD_IS_EMPTY, false);
						$user_password1 = $user_password2 = '';
						$focus_field = 'user_password1';
					}elseif(($user_password1 != '') && ($user_password2 == '')){
						$msg = draw_important_message(_CONF_PASSWORD_IS_EMPTY, false);
						$user_password1 = $user_password2 = '';
						$focus_field = 'user_password2';
					}elseif(($user_password1 != '') && ($user_password2 != '') && ($user_password1 != $user_password2)){
						$msg = draw_important_message(_CONF_PASSWORD_MATCH, false);
						$user_password1 = $user_password2 = '';
						$focus_field = 'user_password2';
					}				
				}			
				if($msg == '' && $m != 'edit'){
					if($agree == ''){
						$msg = draw_important_message(_CONFIRM_TERMS_CONDITIONS, false);
					}elseif($image_verification_allow == 'yes' && !$objImg->check($captcha_code)){				
						$msg = draw_important_message(_WRONG_CODE_ALERT, false);
						$focus_field = 'captcha_code';
					}
				}
			}		
		}
		
		if($act == 'create'){ 
			if($allow_booking_without_account == 'no'){
				if($msg == ''){
					// check if email already exists                    
					$sql = "SELECT * FROM ".TABLE_CUSTOMERS." WHERE email = '".$email."'";
					$result = database_query($sql, DATA_AND_ROWS);
					if($result[1] > 0){
						$msg = draw_important_message(_USER_EMAIL_EXISTS_ALERT, false);
						$focus_field = 'email';
					}			
				}
		
				if($msg == ''){
					// check if such user already exists                    
					$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE user_name != \'\' AND user_name = \''.$user_name.'\'';
					$result = database_query($sql, DATA_AND_ROWS);
					if($result[1] > 0){
						$msg = draw_important_message(_USER_EXISTS_ALERT, false);
						$focus_field = 'user_name';
					}
				}			
			}else{
				if($msg == ''){
					// check if such user already exists
					$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\' AND user_name != \'\'';
					$result = database_query($sql, DATA_AND_ROWS);
					if($result[1] > 0){
						$msg = draw_important_message(_USER_EMAIL_EXISTS_ALERT, false);
						$focus_field = 'email';
					}			
				}
			}
		
			// deny all operations in demo version
			if(strtolower(SITE_MODE) == 'demo'){
				$msg = draw_important_message(_OPERATION_BLOCKED, false);
			}
			
			if($msg == ''){			
				$user_ip = get_current_ip();
				
				$registration_code = '';
		
				if($allow_booking_without_account == 'yes'){
					$user_password = '\'\'';
				}else{
					if(!PASSWORDS_ENCRYPTION){
						$user_password = '\''.$user_password1.'\'';
					}else{
						if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){					
							$user_password = 'AES_ENCRYPT(\''.$user_password1.'\', \''.PASSWORDS_ENCRYPT_KEY.'\')';
						}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
							$user_password = 'MD5(\''.$user_password1.'\')';
						}				
					}
				}
				
				// check if such customer (without account) already exists
				$sql = 'SELECT *
						FROM '.TABLE_CUSTOMERS.'
						WHERE email = \''.$email.'\' AND user_name = \'\' AND user_password = \'\'';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					$registration_type = 'exist';
					// update user
					$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET 
							first_name = \''.$first_name.'\',
							last_name = \''.$last_name.'\',
							company	= \''.$company.'\',
							b_address = \''.$b_address.'\',
							b_address_2 = \''.$b_address_2.'\',
							b_city = \''.$b_city.'\',
							b_zipcode = \''.$b_zipcode.'\',
							b_country = \''.$b_country.'\',
							b_state = \''.$b_state.'\',
							phone = \''.$phone.'\',
							fax = \''.$fax.'\',
							email = \''.$email.'\',
							last_logged_ip =\''.$user_ip.'\',
							email_notifications = \''.$send_updates.'\',
							notification_status_changed = \''.date('Y-m-d H:i:s').'\'
						WHERE id = '.(int)$result[0]['id'];
				}else{
					$registration_type = 'new';
					// insert new user
					$sql = 'INSERT INTO '.TABLE_CUSTOMERS.'(
						first_name, last_name, birth_date, company,
						b_address, b_address_2, b_city, b_zipcode, b_country, b_state,
						phone, fax, email, url,
						user_name, user_password, preferred_language,
						date_created, registered_from_ip, last_logged_ip,
						email_notifications,
						comments,
						is_active,
						is_removed,
						registration_code)
					VALUES(
						\''.$first_name.'\', \''.$last_name.'\', '.$birth_date_in_sql.', \''.$company.'\',
						\''.$b_address.'\', \''.$b_address_2.'\', \''.$b_city.'\', \''.$b_zipcode.'\', \''.$b_country.'\', \''.$b_state.'\',
						\''.$phone.'\', \''.$fax.'\', \''.$email.'\', \'\',
						\''.$user_name.'\', '.$user_password.', \''.Application::Get('lang').'\',
						\''.date('Y-m-d H:i:s').'\', \''.$user_ip.'\', \'\',
						\''.$send_updates.'\',
						\'\',
						1,
						0,
						\''.$registration_code.'\')';
				}
			
				if(database_void_query($sql) > 0){
					$account_created = true;
					if($registration_type == 'new'){
						Session::Set('current_customer_id', database_insert_id());	
					}else{
						Session::Set('current_customer_id', $result[0]['id']);	
					}
					
					if($allow_booking_without_account == 'yes'){
						$email_template = 'new_account_created_without';
					}else{
						$email_template = 'new_account_created';
					}
					/////////////////////////////////////////////////////////////
					send_email(
						$email,
						$objSettings->GetParameter('admin_email'),
						$email_template,
						array(
							'{FIRST NAME}'    => $first_name,
							'{LAST NAME}'     => $last_name,
							'{USER NAME}'     => $user_name,
							'{USER PASSWORD}' => $user_password1,
							'{WEB SITE}'      => $_SERVER['SERVER_NAME'],
							'{REGISTRATION CODE}' => '',
							'{BASE URL}'      => APPHP_BASE,
							'{YEAR}' 	      => date('Y')
						)
					);
					/////////////////////////////////////////////////////////////
					
					redirect_to('index.php?page=booking_checkout&m=1');
				}else{
					$msg = draw_important_message(_CREATING_ACCOUNT_ERROR, false);
					///$msg .= database_error();
				}                    		
			}		
		}elseif($act == 'update'){
		
			// check if email already exists                    
			$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\' AND id != '.$current_customer_id;
			$result = database_query($sql, DATA_ONLY);
			if($result[0]['cnt'] > 0){
				$msg = draw_important_message(_USER_EMAIL_EXISTS_ALERT, false);
				$focus_field = 'email';
			}			
			
			// deny all operations in demo version
			if(strtolower(SITE_MODE) == 'demo'){
				$msg = draw_important_message(_OPERATION_BLOCKED, false);
			}				
		
			if($msg == ''){			
				// insert new user
				$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET
							first_name = \''.$first_name.'\',
							last_name = \''.$last_name.'\',
							company = \''.$company.'\',
							b_address = \''.$b_address.'\',
							b_address_2 = \''.$b_address_2.'\',
							b_city = \''.$b_city.'\',
							b_state = \''.$b_state.'\',
							b_country = \''.$b_country.'\',
							b_zipcode = \''.$b_zipcode.'\',
							phone = \''.$phone.'\',
							fax = \''.$fax.'\',
							email = \''.$email.'\'
						WHERE id = '.$current_customer_id;
				if(database_void_query($sql) > 0){		
					$account_created = true;
					redirect_to('index.php?page=booking_checkout&m=2');
				}else{
					$msg = draw_important_message(_UPDATING_ACCOUNT_ERROR, false);
				}
			}
		}
		
		
		if($current_customer_id != '' && $m == 'edit'){		
			$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE id = '.(int)$current_customer_id;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$send_updates = $result[0]['email_notifications'];
				$first_name  = $result[0]['first_name'];
				$last_name   = $result[0]['last_name'];
				$birth_date  = $result[0]['birth_date'];
				$company     = $result[0]['company'];
				$b_address   = $result[0]['b_address'];
				$b_address_2 = $result[0]['b_address_2'];
				$b_city      = $result[0]['b_city'];
				$b_zipcode   = $result[0]['b_zipcode'];
				$b_country   = $result[0]['b_country'];
				$b_state     = $result[0]['b_state'];
				$phone       = $result[0]['phone'];
				$fax         = $result[0]['fax'];
				$email       = $result[0]['email'];
				$user_name   = $result[0]['user_name'];
				$user_password1 = $result[0]['user_password'];
				$user_password2 = $result[0]['user_password'];
			}
		}		
	}
}

