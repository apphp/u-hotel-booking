<?php

/***
 *	Class Accounts
 *  -------------- 
 *  Description : encapsulates account properties
 *	Written by  : ApPHP
 *	Version     : 1.0.2
 *  Updated	    : 15.08.2016
 *  Usage       : Core Class
 *	Differences : no
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct
 *	__destruct
 *	GetParameter
 *	ChangePassword
 *	ChangeEmail
 *	ChangeLang
 *	SendPassword
 *	SavePersonalInfo
 *	SaveApiInfo
 *
 *	ChangeLog:
 *  1.0.2
 *      - added error fields SavePersonalInfo
 *      - my sql_real_escape_string() replaced with encode_text()
 *      - added possibility to upload profile photo
 *      -
 *      -
 *  1.0.1
 *  	- added check for unique email for admins on updating
 *  	- changes in send_email()
 *  	- added first/last name for GetParameter()
 *  	- added SavePersonalInfo()
 *  	- added sending "forgotten password" email in a preferred language
 *	
 **/

class Accounts {

	public $account_name;	
	public $error;

	protected $account_id;
	
	private $account_password;
	private $account_email;
	private $first_name;
	private $last_name;
	private $profile_photo;
	private $profile_photo_thumb;
	private $preferred_language;
	private $account_type;
	
	//==========================================================================
    // Class Constructor
	// 		@param $account
	//==========================================================================
	function __construct($account = 0)
	{		
		$this->account_id = $account;
		$this->error      = '';
		
		// Get account information only if the class was created with some valid account_id
		if($this->account_id != '0'){
			$sql = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE id = '.(int)$this->account_id;
			$temp = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			if(is_array($temp)){
				$this->account_email = isset($temp['email']) ? $temp['email'] : '';
				$this->first_name = isset($temp['first_name']) ? $temp['first_name'] : '';
				$this->last_name = isset($temp['last_name']) ? $temp['last_name'] : '';
				$this->profile_photo = isset($temp['profile_photo']) ? $temp['profile_photo'] : '';
				$this->profile_photo_thumb = isset($temp['profile_photo_thumb']) ? $temp['profile_photo_thumb'] : '';
				$this->account_name = isset($temp['user_name']) ? $temp['user_name'] : '';
				$this->account_password = isset($temp['password']) ? $temp['password'] : '';
				$this->preferred_language = isset($temp['preferred_language']) ? $temp['preferred_language'] : '';
				$this->account_type = isset($temp['account_type']) ? $temp['account_type'] : '';
				$this->room_relocation = isset($temp['room_relocation']) ? $temp['room_relocation'] : '0';
				$this->hotel_relocation = isset($temp['hotel_relocation']) ? $temp['hotel_relocation'] : '0';
				$this->oa_consumer_domain = isset($temp['oa_consumer_domain']) ? $temp['oa_consumer_domain'] : '';
				$this->oa_consumer_key = isset($temp['oa_consumer_key']) ? $temp['oa_consumer_key'] : '';
				$this->oa_consumer_secret = isset($temp['oa_consumer_secret']) ? $temp['oa_consumer_secret'] : '';
				$this->oa_date_created = isset($temp['oa_date_created']) ? $temp['oa_date_created'] : '';
			}			
		}
	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/***
	 * Get Parameter
	 * @param $param
	 **/
	public function GetParameter($param = '')
	{
		if($param == 'email'){
			return $this->account_email;
		}elseif($param == 'preferred_language'){
			return $this->preferred_language;
		}elseif($param == 'account_type'){
			return $this->account_type;
		}elseif($param == 'first_name'){
			return $this->first_name;
		}elseif($param == 'last_name'){
			return $this->last_name;
		}elseif($param == 'profile_photo'){
			return $this->profile_photo;
		}elseif($param == 'profile_photo_thumb'){
			return $this->profile_photo_thumb;
		}elseif($param == 'oa_consumer_domain'){
			return $this->oa_consumer_domain;
		}elseif($param == 'oa_consumer_key'){
			return $this->oa_consumer_key;
		}elseif($param == 'oa_consumer_secret'){
			return $this->oa_consumer_secret;
		}elseif($param == 'oa_date_created'){
			return $this->oa_date_created;
		}elseif($param == 'room_relocation'){
			return $this->room_relocation;
		}elseif($param == 'hotel_relocation'){
			return $this->hotel_relocation;
		}
		return '';
	}

	/***
	 * Change Password
	 * @param $password
	 * @param $confirmation - confirm password
	 * @param &$error_field
	 */
	public function ChangePassword($password, $confirmation, &$error_field)
	{
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			return draw_important_message(_OPERATION_BLOCKED, false);
		}
				
		if(!empty($password) && !empty($confirmation) && strlen($password) >= 6) {
			if($password == $confirmation){
				if(!PASSWORDS_ENCRYPTION){
					$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET password = '.quote_text(encode_text($password)).' WHERE id = '.(int)$this->account_id;
				}else{
					if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
						$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET password = AES_ENCRYPT('.quote_text($password).', '.quote_text(PASSWORDS_ENCRYPT_KEY).') WHERE id = '.(int)$this->account_id;
					}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
						$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET password = '.quote_text(md5($password)).' WHERE id = '.(int)$this->account_id;
					}else{
						$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET password = AES_ENCRYPT('.quote_text($password).', '.quote_text(PASSWORDS_ENCRYPT_KEY).') WHERE id = '.(int)$this->account_id;
					}
				}
				if(database_void_query($sql)){
					return draw_success_message(_PASSWORD_CHANGED, false);
				}else{
					$error_field = 'password_one';
					return draw_important_message(_PASSWORD_NOT_CHANGED, false);
				}								
			}else{
				$error_field = 'password_one';
				return draw_important_message(_PASSWORD_DO_NOT_MATCH, false);
			}
		}else{
			$error_field = 'password_one';
			return draw_important_message(_PASSWORD_IS_EMPTY, false);
		}
	}

	/**
	 * Change Email
	 * @param $email
	 */
	public function ChangeEmail($email)
	{
		global $objLogin;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			return draw_important_message(_OPERATION_BLOCKED, false);
		}
				
		if(!empty($email)){
			if(check_email_address($email)){

				$sql = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE email = '.quote_text(encode_text($email)).' AND id != '.(int)$this->account_id;
				$temp = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($temp[1] > 0){
					return draw_important_message(_ADMIN_EMAIL_EXISTS_ALERT, false);
				}			

				$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET email = '.quote_text(encode_text($email)).' WHERE id = '.(int)$this->account_id;
				if(database_void_query($sql)){
					$this->account_email = $email;
					$objLogin->UpdateLoggedEmail($email);
					return draw_success_message(_CHANGES_SAVED, false);
				}else{
					return draw_important_message(_TRY_LATER, false);
				}
			}else return draw_important_message(_EMAIL_IS_WRONG, false);
		}else return draw_important_message(_EMAIL_EMPTY_ALERT, false);
	}

	/**
	 * Change personal info 
	 * @param $email
	 * @param $first_name
	 * @param $last_name
	 * @param $room_relocation
	 * @param $hotel_relocation
	 * @param &$error_field
	 */
	public function SavePersonalInfo($email, $first_name, $last_name, $room_relocation = false, $hotel_relocation = false, &$error_field)
	{
		global $objLogin;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			return draw_important_message(_OPERATION_BLOCKED, false);
		}
		
		$profile_photo = $profile_photo_thumb = '';
				
		if(empty($first_name)){
			$error_field = 'first_name';
			return draw_important_message(str_replace('_FIELD_', _FIRST_NAME, _FIELD_CANNOT_BE_EMPTY), false);
		}elseif(empty($last_name)){
			$error_field = 'last_name';
			return draw_important_message(str_replace('_FIELD_', _LAST_NAME, _FIELD_CANNOT_BE_EMPTY), false);
		}elseif(!empty($email)){
			if(check_email_address($email)){
				$sql = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE email = '.quote_text(encode_text($email)).' AND id != '.(int)$this->account_id;
				$temp = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($temp[1] > 0){
					$error_field = 'admin_email';
					return draw_important_message(_ADMIN_EMAIL_EXISTS_ALERT, false);
				}
			}else{
				$error_field = 'admin_email';
				return draw_important_message(_EMAIL_IS_WRONG, false);
			}
		}else{
			$error_field = 'admin_email';
			return draw_important_message(_EMAIL_EMPTY_ALERT, false);
		}
		
		$update_profile_photo = false;
		if(!empty($_FILES['profile_photo'])){
			if(!empty($_FILES['profile_photo']['name'])){
				$target_dir = 'images/admins/';

				$target_file_name = basename($_FILES['profile_photo']['name']);
				$ext = substr(strrchr($target_file_name, '.'), 1);
				$target_file = get_random_string(20).'.'.$ext;
				$target_full_path = $target_dir.$target_file;
				$file_maxsize = 1024 * 200;
				$image_file_type = strtolower(pathinfo($target_full_path, PATHINFO_EXTENSION));
				
				// Check file size > 200 Kb
				if($_FILES['profile_photo']['size'] > $file_maxsize){
					$error = str_replace('_FILE_SIZE_', number_format(($_FILES['profile_photo']['size']/1024), 2, '.', ',').' Kb', _INVALID_FILE_SIZE);
					return draw_important_message(str_replace('_MAX_ALLOWED_', number_format(($file_maxsize/1024), 2, '.', ',').' Kb', $error), false);
				}
				
				// Allow certain file formats
				if($image_file_type != 'jpg' && $image_file_type != 'jpeg' && $image_file_type != 'png' && $image_file_type != 'gif' ){
					return draw_important_message(_INVALID_IMAGE_FILE_TYPE, false);
				}				

				// Check if image file is a actual image or fake image				
				$check = getimagesize($_FILES['profile_photo']['tmp_name']);
				if(!$check) {
					return draw_important_message(_INVALID_IMAGE_FILE_TYPE, false);
				}
				
				// If everything is ok, try to move upload file
				if(move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_full_path)){
					
					// create thumbnail
					$thumb_file_ext = substr(strrchr($target_file, '.'), 1);
					$thumb_file_name = str_replace('.'.$ext, '', $target_file);
					$thumb_file_fullname = $thumb_file_name.'_thumb.'.$thumb_file_ext;
					@copy($target_full_path, $target_dir.$thumb_file_fullname);								
					$thumb_file_thumb_fullname = resize_image($target_dir, $thumb_file_fullname, '90', '90');

					$profile_photo = $target_file;
					$profile_photo_thumb = $thumb_file_thumb_fullname;

					$update_profile_photo = true;

				}else{
					return draw_important_message(_TRY_LATER, false);
				}
			}
		}

		$sql = 'UPDATE '.TABLE_ACCOUNTS.'
				SET
					email = '.quote_text(encode_text($email)).',
					first_name = '.quote_text(encode_text($first_name)).',					
					last_name = '.quote_text(encode_text($last_name)).',
					room_relocation = '.quote_text(encode_text($room_relocation)).',
					hotel_relocation = '.quote_text(encode_text($hotel_relocation)).'
					'.($update_profile_photo ? ',profile_photo = '.quote_text(encode_text($profile_photo)) : '').'
					'.($update_profile_photo ? ',profile_photo_thumb = '.quote_text(encode_text($profile_photo_thumb)) : '').'
				WHERE id = '.(int)$this->account_id;
		if(database_void_query($sql)){
			$this->account_email = $email;
			$this->first_name = $first_name;
			$this->last_name = $last_name;
            $this->room_relocation = $room_relocation;
            $this->hotel_relocation = $hotel_relocation;
			if($update_profile_photo){
				$this->profile_photo = $profile_photo;
				$this->profile_photo_thumb = $profile_photo_thumb;
				$objLogin->UpdateLoggedPhoto($profile_photo_thumb);
			}
			$objLogin->UpdateLoggedEmail($email);			
			return draw_success_message(_CHANGES_SAVED, false);
		}else{
			return draw_important_message(_TRY_LATER, false);
		}
	}
	
	/**
	 * Change API info
	 * @param $consumer_domain
	 * @param &$error_field
	 * @param $recreate
	 */
	public function SaveApiInfo($consumer_domain, &$error_field, $recreate = false)
	{
		global $objLogin;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			return draw_important_message(_OPERATION_BLOCKED, false);
		}
		
		$oa_consumer_key = '';
		$oa_consumer_secret = '';
		
		if(!empty($consumer_domain) && substr($consumer_domain, -1) != '/'){
			$consumer_domain .= '/';
		}

		if(!empty($consumer_domain)){
			if(!is_url($consumer_domain)){
				$error_field = 'oa_consumer_domain';
				return draw_important_message(str_replace('_FIELD_', '<b>'._API_DOMAIN.'</b>', _FIELD_MUST_BE_URL), false);
			}else{
				$sql = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE id = '.(int)$this->account_id;
				$account = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($account[1] > 0 && ($consumer_domain != $account[0]['oa_consumer_domain'] || $recreate == true)){
					$oa_consumer_key = get_random_string(20);
					$oa_consumer_secret = md5($consumer_domain.$oa_consumer_key);		
				}else{
					$error_field = 'oa_consumer_domain';
					return draw_success_message(_CHANGES_SAVED, false);
				}
			}
		}

		$oa_date_created = !empty($oa_consumer_key) ? date('Y-m-d H:i:s') : null;
		$sql = 'UPDATE '.TABLE_ACCOUNTS.'
				SET
					oa_consumer_domain = '.quote_text($consumer_domain).',
					oa_consumer_key = '.quote_text($oa_consumer_key).',
					oa_consumer_secret = '.quote_text($oa_consumer_secret).',
					oa_date_created = '.(!empty($oa_date_created) ? "'".$oa_date_created."'" : null).'
				WHERE id = '.(int)$this->account_id;
		if(database_void_query($sql)){
			$this->oa_consumer_domain = $consumer_domain;
			$this->oa_consumer_key = $oa_consumer_key;
			$this->oa_consumer_secret = $oa_consumer_secret;
			$this->oa_date_created = $oa_date_created;
			return draw_success_message(_CHANGES_SAVED, false);
		}else{
			return draw_important_message(_TRY_LATER, false);
		}
	}	
	
	/**
	 * Delete account photo
	 */
	public function DeletePhoto()
	{
		global $objLogin;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			return draw_important_message(_OPERATION_BLOCKED, false);
		}
		
		$profile_photo = $this->GetParameter('profile_photo');
		$profile_photo_thumb = $this->GetParameter('profile_photo_thumb');		

		$sql = "UPDATE ".TABLE_ACCOUNTS."
				SET profile_photo = '', profile_photo_thumb = ''
				WHERE id = ".(int)$this->account_id;				
		if(database_void_query($sql)){
			$target_dir = 'images/admins/';

			if(file($target_dir.$profile_photo)) unlink($target_dir.$profile_photo);
			if(file($target_dir.$profile_photo_thumb)) unlink($target_dir.$profile_photo_thumb);

			$this->profile_photo = '';
			$this->profile_photo_thumb = '';
			$objLogin->UpdateLoggedPhoto('');

			return draw_success_message(_CHANGES_SAVED, false);
		}else{
			return draw_important_message(_TRY_LATER, false);
		}
	}	

	/**
	 * Change Parameter
	 * @param $param_val
	 */
	public function ChangeLang($param_val)
	{
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			return draw_important_message(_OPERATION_BLOCKED, false);
		}
		
		global $objLogin;
				
		if(!empty($param_val)){
			$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET preferred_language = '.quote_text(encode_text($param_val)).' WHERE id = '.(int)$this->account_id;
			if(database_void_query($sql)){
				$this->preferred_language = $param_val;
				$objLogin->SetPreferredLang($param_val);
				return draw_success_message(_SETTINGS_SAVED, false);
			}else{
				return draw_important_message(_TRY_LATER, false);
			}
		}else return draw_important_message(str_replace('_FIELD_', _PREFERRED_LANGUAGE, _FIELD_CANNOT_BE_EMPTY), false);
	}

	/**
	 * Send forgotten password
	 * @param $email
	 */
	public function SendPassword($email)
	{
		global $objSettings;
		
		$lang = Application::Get('lang');
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}
				
		if(!empty($email)) {
			if(check_email_address($email)){   

				if(!PASSWORDS_ENCRYPTION){
					$sql = 'SELECT id, first_name, last_name, user_name, password, preferred_language FROM '.TABLE_ACCOUNTS.' WHERE email = '.quote_text(encode_text($email)).' AND is_active = 1';
				}else{
					if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
						$sql = 'SELECT id, first_name, last_name, user_name, AES_DECRYPT(password, '.quote_text(PASSWORDS_ENCRYPT_KEY).') as password, preferred_language FROM '.TABLE_ACCOUNTS.' WHERE email = '.quote_text(encode_text($email)).' AND is_active = 1';
					}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
						$sql = 'SELECT id, first_name, last_name, user_name, \'\' as password, preferred_language FROM '.TABLE_ACCOUNTS.' WHERE email = '.quote_text($email).' AND is_active = 1';
					}				
				}
				
				$temp = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
				if(is_array($temp) && count($temp) > 0){

					//////////////////////////////////////////////////////////////////
					if(!PASSWORDS_ENCRYPTION){
						$password = $temp['password'];
					}else{
						if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
							$password = $temp['password'];
						}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
							$password = get_random_string(8);
							$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET password = '.quote_text(md5($password)).' WHERE id = '.(int)$temp['id'];
							database_void_query($sql);
						}				
					}
					
					send_email(
						$email,
						$objSettings->GetParameter('admin_email'),
						'password_forgotten',
						array(
							'{FIRST NAME}'    => $temp['first_name'],
							'{LAST NAME}'     => $temp['last_name'],
							'{USER NAME}'     => $temp['user_name'],
							'{USER PASSWORD}' => $password,
							'{BASE URL}'      => APPHP_BASE,
							'{WEB SITE}'      => $_SERVER['SERVER_NAME'],
							'{YEAR}'       	  => date('Y')
						),
						$temp['preferred_language']
					);
					//////////////////////////////////////////////////////////////////
					
					return true;					
				}else{
					$this->error = _EMAIL_NOT_EXISTS;
					return false;
				}				
			}else{
				$this->error = _EMAIL_IS_WRONG;
				return false;								
			}
		}else{
			$this->error = _EMAIL_EMPTY_ALERT;
			return false;
		}
		return true;
	}
	
}
