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

if(ModulesSettings::Get('accounts', 'hotel_owner_allow_registration') == 'yes' && !$objLogin->IsLoggedIn()){

    $registrationType   = ModulesSettings::Get('accounts', 'hotel_owner_registration_type');
    $act 		        = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
    $hotel_id           = isset($_POST['hotel_id']) ? prepare_input($_POST['hotel_id']) : '';
    $focus_field        = 'first_name';
    $user_ip            = get_current_ip();
    $msg                = '';
    $account_created    = false;
    $account_exists     = false;
    $room_relocation    = true;
    $hotel_relocation   = true;
    $username           = '';
    $password           = '';
    $confirm_password   = '';
    $first_name         = '';
    $last_name          = '';
    $email              = '';
    $companies          = '';
    $user_password      = '""';

    if($act == 'create'){
        if($registrationType == 'advanced'){
            if($hotel_id == ''){
                $msg = draw_important_message(_HOTEL_EMPTY_ALERT, false);
            }

            // deny all operations in demo version
            if(strtolower(SITE_MODE) == 'demo'){
                $msg = draw_important_message(_OPERATION_BLOCKED, false);
            }

            // check if user IP or email don't blocked
            if($msg == ''){
                if($objLogin->IpAddressBlocked($user_ip)) $msg = draw_important_message(_IP_ADDRESS_BLOCKED, false);
            }

            if($msg == ''){
                // check if hotel already exists
                $sql_hotel              = 'SELECT * FROM '.TABLE_HOTELS.' WHERE id = \''.encode_text($hotel_id).'\'';
                $hotel                  = database_query($sql_hotel, DATA_ONLY, DATA_AND_ROWS);
                $sql_hotel_description  = 'SELECT * FROM '.TABLE_HOTELS_DESCRIPTION.' WHERE hotel_id = \''.encode_text($hotel['id']).'\'';
                $hotel_description      = database_query($sql_hotel_description, DATA_ONLY, DATA_AND_ROWS);

                if(empty($hotel) || empty($hotel_description)){
                    $msg = draw_important_message(_HOTEL_NOT_EXISTS, false);
                }else{
                    $sql_account = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE '.TABLE_ACCOUNTS.'.email = \''.encode_text($hotel['email']).'\'';
                    $account = database_query($sql_account, DATA_ONLY, DATA_AND_ROWS);
                    if(!empty($account)){
                        $msg = draw_important_message(_USER_EMAIL_EXISTS_ALERT, false);
                        $account_exists = true;
                    }
                }
            }
        }elseif($registrationType == 'standard'){
            //If account exists and $task = 'update_account' check the form and update the account
            $username           = isset($_POST['username']) ? prepare_input($_POST['username']) : '';
            $password           = isset($_POST['password']) ? prepare_input($_POST['password']) : '';
            $confirm_password   = isset($_POST['confirm_password']) ? prepare_input($_POST['confirm_password']) : '';
            $first_name         = isset($_POST['first_name']) ? prepare_input($_POST['first_name']) : '';
            $last_name          = isset($_POST['last_name']) ? prepare_input($_POST['last_name']) : '';
            $email              = isset($_POST['email']) ? prepare_input($_POST['email']) : '';
            $room_relocation    = isset($_POST['room_relocation']) ? prepare_input($_POST['room_relocation']) : false;
            $hotel_relocation   = isset($_POST['hotel_relocation']) ? prepare_input($_POST['hotel_relocation']) : false;

            if($first_name == ''){
                $msg = draw_important_message(_FIRST_NAME_EMPTY_ALERT, false);
                $focus_field = 'first_name';
            }elseif($last_name == '') {
                $msg = draw_important_message(_LAST_NAME_EMPTY_ALERT, false);
                $focus_field = 'last_name';
            }elseif($email == ''){
                $msg = draw_important_message(_EMAIL_EMPTY_ALERT, false);
                $focus_field = 'email';
            }elseif(($email != '') && (!check_email_address($email))){
                $msg = draw_important_message(_EMAIL_VALID_ALERT, false);
                $focus_field = 'email';
            }elseif($username == ''){
                $msg = draw_important_message(_USERNAME_EMPTY_ALERT, false);
                $focus_field = 'username';
            }elseif(($username != '') && (strlen($username) < 4)){
                $msg = draw_important_message(_USERNAME_LENGTH_ALERT, false);
                $focus_field = 'username';
            }elseif($password == ''){
                $msg = draw_important_message(_PASSWORD_IS_EMPTY, false);
                $password = $confirm_password = '';
                $focus_field = 'password';
            }elseif(($password != '') && (strlen($password) < 6)){
                $msg = draw_important_message(_PASSWORD_IS_EMPTY, false);
                $password = $confirm_password = '';
                $focus_field = 'password';
            }elseif(($password != '') && ($confirm_password == '')){
                $msg = draw_important_message(_CONF_PASSWORD_IS_EMPTY, false);
                $password = $confirm_password = '';
                $focus_field = 'password';
            }elseif(($password != '') && ($confirm_password != '') && ($password != $confirm_password)){
                $msg = draw_important_message(_CONF_PASSWORD_MATCH, false);
                $password = $confirm_password = '';
                $focus_field = 'password';
            }

            // deny all operations in demo version
            if(strtolower(SITE_MODE) == 'demo'){
                $msg = draw_important_message(_OPERATION_BLOCKED, false);
            }

            if($msg == ''){
                $sql_exists_account = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE email = \''.encode_text($email).'\' AND is_active = 1';
                if(!empty($exists_account)){
                    $msg = draw_important_message(_USER_EMAIL_EXISTS_ALERT, false);
                    $draw_form_reg = true;
                    $focus_field = 'email';
                    $username = '';
                }
            }

            if($msg == ''){
                // check if user already exists
                $sql_exists_account = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE user_name = \''.encode_text($username).'\' AND is_active = 1';
                $exists_account = database_query($sql_exists_account, DATA_ONLY, DATA_AND_ROWS);
                if(!empty($exists_account)){
                    $msg = draw_important_message(_USER_EXISTS_ALERT, false);
                    $focus_field = 'username';
                    $username = '';
                    $password = '';
                    $confirm_password = '';
                }
            }

            // check if user IP or email don't blocked
            if($msg == ''){
                if($objLogin->IpAddressBlocked($user_ip)) $msg = draw_important_message(_IP_ADDRESS_BLOCKED, false);
                else if($objLogin->EmailBlocked($email)) $msg = draw_important_message(_EMAIL_BLOCKED, false);
            }
        }

        if($msg == ''){
            if($registrationType == 'advanced'){
                $email = $hotel['email'];
                $companies = serialize(array(1=>$hotel['id']));
            }elseif($registrationType == 'standard'){
                if(!PASSWORDS_ENCRYPTION){
                    $user_password = '\''.encode_text($password).'\'';
                }else{
                    if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
                        $user_password = 'AES_ENCRYPT(\''.encode_text($password).'\', \''.PASSWORDS_ENCRYPT_KEY.'\')';
                    }elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
                        $user_password = 'MD5(\''.encode_text($password).'\')';
                    }
                }
            }


            $account_type = 'hotelowner';
            $preferred_language = Application::Get('lang');
            $date_created = date('Y-m-d H:i:s');
            $is_active = 0;
            $registration_code = strtoupper(get_random_string(20));
            // insert new user
            $sql_create_account = 'INSERT INTO '.TABLE_ACCOUNTS.'
                        (
                            '.TABLE_ACCOUNTS.'.first_name, 
                            '.TABLE_ACCOUNTS.'.last_name, 
                            '.TABLE_ACCOUNTS.'.email, 
                            '.TABLE_ACCOUNTS.'.user_name, 
                            '.TABLE_ACCOUNTS.'.password, 
                            '.TABLE_ACCOUNTS.'.account_type, 
                            '.TABLE_ACCOUNTS.'.companies, 
                            '.TABLE_ACCOUNTS.'.preferred_language, 
                            '.TABLE_ACCOUNTS.'.date_created, 
                            '.TABLE_ACCOUNTS.'.is_active, 
                            '.TABLE_ACCOUNTS.'.registration_code,
                            '.TABLE_ACCOUNTS.'.room_relocation,
                            '.TABLE_ACCOUNTS.'.hotel_relocation
						)
					VALUES(
						"'.encode_text($first_name).'",
						"'.encode_text($last_name).'",
						"'.encode_text($email).'",
						"'.encode_text($username).'",
						'.$user_password.',
						"'.encode_text($account_type).'",
						"'.encode_text($companies).'",
						"'.encode_text($preferred_language).'",
						"'.encode_text($date_created).'",
						"'.encode_text($is_active).'",
						"'.encode_text($registration_code).'",
						"'.$room_relocation.'",
						"'.$hotel_relocation.'"
					)';
            if(database_void_query($sql_create_account) > 0){
                if($registrationType == 'advanced'){
                    $template = 'new_hotel_owner_account_created_confirm';
                    $description = $hotel_description['name'];
                }elseif($registrationType == 'standard'){
                    $template = 'new_hotel_owner_created_standard_type';
                    $description = '';
                }
                ////////////////////////////////////////////////////////////
                send_email(
                    $email,
                    $objSettings->GetParameter('admin_email'),
                    $template,
                    array(
                        '{HOTEL_NAME}' => $description,
                        '{REGISTRATION_CODE}' => $registration_code,
                        '{BASE_URL}'   => APPHP_BASE,
                        '{WEB SITE}'   => $_SERVER['SERVER_NAME'],
                        '{YEAR}' 	   => date('Y')
                    )
                );
                ////////////////////////////////////////////////////////////

                $msg = draw_success_message(_ACCOUNT_CREATED_CONF_BY_EMAIL_MSG, false);
                $msg .= '<br />'.draw_message(_HOTEL_OWNERS_ACCOUNT_CREATED_CONF_LINK, false);
                $account_created = true;

            }else{
                $msg = draw_important_message(_CREATING_ACCOUNT_ERROR, false);
            }
        }

    }
}

