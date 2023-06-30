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

if(ModulesSettings::Get('accounts', 'hotel_owner_allow_registration') == 'yes' && !$objLogin->IsLoggedIn() && (ModulesSettings::Get('customers', 'allow_registration') == 'yes')){

    $registrationType   = ModulesSettings::Get('accounts', 'hotel_owner_registration_type');
    $code               = isset($_REQUEST['c']) ? prepare_input($_REQUEST['c']) : '';
	$task               = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
    $msg                = '';
    $user_name          = '';
    $user_password1     = '';
    $user_password2     = '';
    $first_name         = '';
    $last_name          = '';
	$confirmed          = false;
	$draw_form_reg      = false;
    $user_ip            = get_current_ip();
    $focus_field        = '';

//    if($registrationType == 'advanced'){
//
//    }elseif($registrationType == 'standard'){
//
//    }

    if($code != ''){
        // Search account
        $sql_account = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE registration_code = \''.encode_text($code).'\' AND is_active = 0';
		$account = database_query($sql_account, DATA_ONLY, DATA_AND_ROWS);
        if($registrationType == 'advanced'){
            if(!empty($account) && $task != 'update_account'){
                //If account exists and $task != 'update_account' open the registration form
                $msg = draw_success_message(_HOTEL_OWNERS_CONFIRMED_UPDATE_MSG, false);
                $confirmed = true;
                $draw_form_reg = true;
                $focus_field = 'frmReg_user_name';
            }elseif(!empty($account) && $task == 'update_account'){

                //If account exists and $task = 'update_account' check the form and update the account
                $user_name      = isset($_POST['user_name']) ? prepare_input($_POST['user_name']) : '';
                $user_password1 = isset($_POST['user_password1']) ? prepare_input($_POST['user_password1']) : '';
                $user_password2 = isset($_POST['user_password2']) ? prepare_input($_POST['user_password2']) : '';
                $first_name     = isset($_POST['first_name']) ? prepare_input($_POST['first_name']) : '';
                $last_name      = isset($_POST['last_name']) ? prepare_input($_POST['last_name']) : '';

                if($user_name == ''){
                    $msg = draw_important_message(_USERNAME_EMPTY_ALERT, false);
                    $focus_field = 'frmReg_user_name';
                    $draw_form_reg = true;
                }elseif(($user_name != '') && (strlen($user_name) < 4)){
                    $msg = draw_important_message(_USERNAME_LENGTH_ALERT, false);
                    $focus_field = 'frmReg_user_name';
                    $draw_form_reg = true;
                }elseif($user_password1 == ''){
                    $msg = draw_important_message(_PASSWORD_IS_EMPTY, false);
                    $user_password1 = $user_password2 = '';
                    $focus_field = 'frmReg_user_password1';
                    $draw_form_reg = true;
                }elseif(($user_password1 != '') && (strlen($user_password1) < 6)){
                    $msg = draw_important_message(_PASSWORD_IS_EMPTY, false);
                    $user_password1 = $user_password2 = '';
                    $focus_field = 'frmReg_user_password1';
                    $draw_form_reg = true;
                }elseif(($user_password1 != '') && ($user_password2 == '')){
                    $msg = draw_important_message(_CONF_PASSWORD_IS_EMPTY, false);
                    $user_password1 = $user_password2 = '';
                    $focus_field = 'frmReg_user_password1';
                    $draw_form_reg = true;
                }elseif(($user_password1 != '') && ($user_password2 != '') && ($user_password1 != $user_password2)){
                    $msg = draw_important_message(_CONF_PASSWORD_MATCH, false);
                    $user_password1 = $user_password2 = '';
                    $focus_field = 'frmReg_user_password1';
                    $draw_form_reg = true;
                }

                // deny all operations in demo version
                if(strtolower(SITE_MODE) == 'demo'){
                    $msg = draw_important_message(_OPERATION_BLOCKED, false);
                }

                // check if user IP or email don't blocked
                if($msg == ''){
                    if($objLogin->IpAddressBlocked($user_ip)) $msg = draw_important_message(_IP_ADDRESS_BLOCKED, false);
                    else if($objLogin->EmailBlocked($account['email'])) $msg = draw_important_message(_EMAIL_BLOCKED, false);
                }

                if($msg == ''){
                    // check if user already exists
                    $sql_exists_account = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE user_name = \''.encode_text($user_name).'\' AND is_active = 1';
                    $exists_account = database_query($sql_exists_account, DATA_ONLY, DATA_AND_ROWS);
                    if(!empty($exists_account)){
                        $msg = draw_important_message(_USER_EXISTS_ALERT, false);
                        $draw_form_reg = true;
                        $focus_field = 'frmReg_user_name';
                        $user_name = '';
                    }
                }

                if($msg == ''){
                    if(!PASSWORDS_ENCRYPTION){
                        $user_password = '\''.encode_text($user_password1).'\'';
                    }else{
                        if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
                            $user_password = 'AES_ENCRYPT(\''.encode_text($user_password1).'\', \''.PASSWORDS_ENCRYPT_KEY.'\')';
                        }elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
                            $user_password = 'MD5(\''.encode_text($user_password1).'\')';
                        }
                    }
                    //Update account
                    $sql_update_account = 'UPDATE '.TABLE_ACCOUNTS.'
                        SET 
                        '.TABLE_ACCOUNTS.'.user_name = "'.encode_text($user_name).'", 
                        '.TABLE_ACCOUNTS.'.password = '.$user_password.', 
                        '.TABLE_ACCOUNTS.'.first_name = "'.encode_text($first_name).'", 
                        '.TABLE_ACCOUNTS.'.last_name = "'.encode_text($last_name).'", 
                        '.TABLE_ACCOUNTS.'.is_active = 1, 
                        '.TABLE_ACCOUNTS.'.registration_type = "advanced",
                        '.TABLE_ACCOUNTS.'.registration_code = ""
                        WHERE registration_code = "'.encode_text($code).'" AND is_active = 0';
                    if(database_void_query($sql_update_account) > 0){
                        $_POST['submit_login'] = 'login';
                        $_POST['user_name'] = $user_name;
                        $_POST['password'] = $user_password1;
                        $_POST['type'] = 'admin';
                        $login = new Login();
                    }else{
                        $msg = draw_important_message(_CREATING_ACCOUNT_ERROR, false);
                    }
                }

            }else{
                if(strlen($code) == 20){
                    $confirmed = true;
                    $draw_form_reg = false;
                    $msg = draw_message(_HOTEL_OWNERS_CONFIRMED_ALREADY_MSG, false);
                }else{
                    $msg = draw_important_message(_WRONG_CONFIRMATION_CODE, false);
                }
            }
        }elseif($registrationType == 'standard'){
            if(!empty($account)){
                $confirmed = true;
                //Update account
                $sql_update_account = 'UPDATE '.TABLE_ACCOUNTS.'
                        SET 
                        '.TABLE_ACCOUNTS.'.is_active = 1, 
                        '.TABLE_ACCOUNTS.'.registration_type = "standard",
                        '.TABLE_ACCOUNTS.'.registration_code = ""
                        WHERE registration_code = "'.encode_text($code).'" AND is_active = 0';
                if(database_void_query($sql_update_account) > 0){
                    $msg = draw_success_message(_HOTEL_OWNERS_CONFIRMED_SUCCESS_MSG, false);
                }else{
                    $msg = draw_important_message(_CREATING_ACCOUNT_ERROR, false);
                }
            }else{
                if(strlen($code) == 20){
                    $confirmed = true;
                    $draw_form_reg = false;
                    $msg = draw_message(_HOTEL_OWNERS_CONFIRMED_ALREADY_MSG, false);
                }else{
                    $msg = draw_important_message(_WRONG_CONFIRMATION_CODE, false);
                }
            }
        }
    }else{
		if($task == 'post_submission') $msg = draw_important_message(str_replace('_FIELD_', _CONFIRMATION_CODE, _FIELD_CANNOT_BE_EMPTY), false);
    }    
}

