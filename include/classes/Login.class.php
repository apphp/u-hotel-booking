<?php

/**
 *	Class Login (for uHotelSite ONLY)
 *  -------------- 
 *  Description : encapsulates login properties
 *  Updated     : 11.02.2016
 *	Written by  : ApPHP
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *  __construct                                     DoLogin
 *  __destruct                                      UpdateAccountInfo 
 *  IsWrongLogin                                    GetAccountInformation
 *  IsIpAddressBlocked                              SetSessionVariables
 *  IsEmailBlocked                                  GetUniqueUrl
 *  DoLogout                                        PrepareLink 
 *  GetLastLoginTime                                Encrypt
 *  IsLoggedIn                                      Decrypt
 *  IsLoggedInAs                                    
 *  IsLoggedInAsAdmin
 *  IsLoggedInAsCustomer
 *  GetLoggedType
 *  GetLoggedEmail
 *  GetLoggedPhoto
 *  GetCustomerType
 *  GetAgencyLogo
 *  UpdateAgencyLogo
 *  GetLoggedGroupID
 *  UpdateLoggedEmail
 *  UpdateLoggedPhoto
 *  GetLoggedName
 *  GetLoggedFirstName
 *  GetLoggedLastName
 *  UpdateLoggedFirstName
 *  UpdateLoggedLastName
 *  GetLoggedID
 *  GetPreferredLang
 *  SetPreferredLang
 *  GetActiveMenuCount
 *  DrawLoginLinks
 *  IpAddressBlocked
 *  EmailBlocked
 *  RemoveAccount
 *  GetLoginError
 *  HasPrivileges
 *  AssignedToHotel
 *  AssignedToHotels
 *  AssignedToCarAgency
 *  AssignedToCarAgencies
 *  
 **/

class Login {

	private $wrongLogin;
	private $ipAddressBlocked;
	private $emailBlocked;
	private $activeMenuCount;
	private $accountType;
	private $loginError;

	private $cookieName;
	private $cookieTime;
	private $passwordKey = 'phphs_customer_area';
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		$this->ipAddressBlocked = false;
		$this->emailBlocked = false;
		$this->loginError = '';

		$this->cookieName = 'site_auth'.INSTALLATION_KEY;
		$this->cookieTime = (3600 * 24 * 14); // 14 days

		$submit_login  = isset($_POST['submit_login']) ? prepare_input($_POST['submit_login']) : '';
		$submit_logout = isset($_POST['submit_logout']) ? prepare_input($_POST['submit_logout']) : '';
		$user_name 	   = isset($_POST['user_name']) ? prepare_input($_POST['user_name'], true) : '';
		$password      = isset($_POST['password']) ? prepare_input($_POST['password'], true) : '';
		$this->accountType = isset($_POST['type']) ? prepare_input($_POST['type']) : 'customer';
		$remember_me   = isset($_POST['remember_me']) ? prepare_input($_POST['remember_me']) : '';		

		$this->wrongLogin = false;		
		if(!$this->IsLoggedIn()){
			if($submit_login == 'login'){
				if(empty($user_name) || empty($password)){
					if(isset($_POST['user_name']) && empty($user_name)){
						$this->loginError = '_USERNAME_EMPTY_ALERT';						
					}elseif(isset($_POST['password']) && empty($password)){
						$this->loginError = '_WRONG_LOGIN';
					}
					$this->wrongLogin = true;							
				}else{
					$this->DoLogin($user_name, $password, $remember_me);
				}
			}elseif(isset($_GET['provider']) && isset($_GET['customer']) && ModulesSettings::Get('customers', 'allow_social_login') == 'yes'){
				if(SITE_MODE == 'demo'){
					$this->loginError = '_OPERATION_BLOCKED';
					$this->wrongLogin = true;
				}else{
					//-- try to gibrid login here
					$provider_name = !empty($_REQUEST['provider']) ? $_REQUEST['provider'] : '';

					try{
						// include HybridAuth library
						$config_file   = dirname(__FILE__) . '/../../modules/hybridauth/config.php';
						require_once( dirname(__FILE__) . '/../../modules/hybridauth/Hybrid/Auth.php');

						// initialize Hybrid_Auth class with the config file
						$hybridauth = new Hybrid_Auth($config_file);

						// try to authenticate with the selected provider
						$adapter = $hybridauth->authenticate($provider_name);
						// then grab the user profile
						$user_profile = $adapter->getUserProfile();
					}
					// something went wrong?
					catch(Exception $e){
						if(SITE_MODE == 'development'){
							var_dump($e);
							die;
						}else{
							header('location: '.APPHP_BASE);
						}
					}

					// Check if the current user already have authenticated using this provider before
					$user_exist = $this->get_user_by_provider_and_id($provider_name, $user_profile->identifier);

					// If the used didn't authenticate using the selected provider before
					// we create a new entry on database.users for him
					if(!$user_exist){
						$aLogin_details = $this->create_new_hybridauth_user(
							$user_profile->email,
							$user_profile->firstName,
							$user_profile->lastName,
							$provider_name,
							$user_profile->identifier
						);

						$this->DoLogin($aLogin_details['user_name'], $aLogin_details['pwd'], '', true, true);
					}else{
						$this->DoLogin($user_exist['user_name'], $user_exist['pwd'], '', true, true);
					}
					//--end try to gybrid login here
				}
            }else{
				if(isset($_COOKIE[$this->cookieName])){
					parse_str($_COOKIE[$this->cookieName]);
					if(!empty($usr) && !empty($hash)){
						$user_name = $usr;
						$password = $this->Decrypt($hash, $this->passwordKey);					
						$this->DoLogin($user_name, $password, '2');
					}
				}				
			}
		}elseif($submit_logout == 'logout'){
			$this->DoLogout();
		}
		$this->activeMenuCount = 0;
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Do login
	 * @param $user_name - system name of user
	 * @param $password - password of user
	 * @param $remember_me
	 * @param $do_redirect - prepare redirect or not
	 * @param $do_direct_login
	 */
	private function DoLogin($user_name, $password, $remember_me = '', $do_redirect = true, $do_direct_login = false)
	{
		global $objSession;
		
		$ip_address = get_current_ip();

		if($account_information = $this->GetAccountInformation($user_name, $password, $do_direct_login)){
			if($account_information['is_active'] == '0'){
				if(!empty($account_information['registration_code']) != ''){
					$this->loginError = '_REGISTRATION_NOT_COMPLETED';	
				}else{
					$this->loginError = '_WRONG_LOGIN';
				}				
				$this->wrongLogin = true;
				return false;
			}
			
			ob_start();
			$this->SetSessionVariables($account_information);

			if($this->IsLoggedInAsCustomer(false)){
				if($this->IpAddressBlocked($ip_address)){
					$this->DoLogout();
					$this->ipAddressBlocked = true;
					$do_redirect = false;
				}elseif($this->EmailBlocked($this->GetLoggedEmail(false))){
					$this->DoLogout();
					$this->emailBlocked = true;
					$do_redirect = false;			
				}
			}
			
			// Check if module is installed or block car agency owners access
			if($this->GetLoggedType(false) == 'agencyowner' && !Modules::IsModuleInstalled('car_rental')){
				$this->DoLogout();
				$do_redirect = false;
				$this->loginError = '_WRONG_LOGIN';
				$this->wrongLogin = true;
			}

			$this->UpdateAccountInfo($account_information);
			if($do_redirect){
				$objSession->SetFingerInfo();

				if($remember_me == '2'){
					// ignore and do nothing - allow cookies to expire in $this->cookieTime sec.
				}elseif($remember_me == '1'){
					$password_hash = $this->Encrypt($password, $this->passwordKey);
					setcookie($this->cookieName, 'usr='.$user_name.'&hash='.$password_hash, time() + $this->cookieTime);
				}else{
					setcookie($this->cookieName, '', time() - 3600);
				}

				$redirect_page = 'index.php';
				if($this->IsLoggedInAsCustomer()){
					$redirect_page  = (Session::Get('last_visited') != '') ? Session::Get('last_visited') : 'index.php?customer=home';
					$redirect_page .= (preg_match('/\?/', $redirect_page) ? '&' : '?').'lang='.$this->GetPreferredLang();
				}
				
				if(defined('WIDGET_HASH')){
					// Redirect for widget
					redirect_to(WIDGET_HOME_URL.'index.php?page=booking_details&'.WIDGET_HASH);
				}else{
					redirect_to($redirect_page);
				}
				
				ob_end_flush();
				exit;
			}
		}else{
			$this->loginError = '_WRONG_LOGIN';
			$this->wrongLogin = true;
		}
	}
	
	/**
	 * 	Checks if login was wrong
	 */
	public function IsWrongLogin()
	{
		return ($this->wrongLogin == true) ? true : false;	
	}

	/**
	 * 	Checks if IP address was blocked
	 */
	public function IsIpAddressBlocked()
	{
		return ($this->ipAddressBlocked == true) ? true : false;	
	}

	/**
	 * 	Checks if IP address was blocked
	 * 	
	 */
	public function IsEmailBlocked()
	{
		return ($this->emailBlocked == true) ? true : false;	
	}

	/**
	 * 	Destroys the session and returns to the default page
	 */
	public function DoLogout()
	{
		global $objSession;
		
		$redirect = ($this->IsLoggedInAsAdmin()) ? 'index.php?admin='.ADMIN_LOGIN : '';
		$objSession->EndSession();
		setcookie($this->cookieName, '', time() - 3600);
		
		if($redirect != ''){
			redirect_to($redirect);
		}
	}

	/**
	 * Checks Ip Address
	 * @param $ip_address
	 */
	public function IpAddressBlocked($ip_address)
	{
		$sql = 'SELECT ban_item
				FROM '.TABLE_BANLIST.'
				WHERE ban_item = \''.$ip_address.'\' AND ban_item_type = \'IP\'';
		return database_query($sql, ROWS_ONLY);		
	}

	/**
	 * Checks Email Address
	 * @param $email
	 */
	public function EmailBlocked($email)
	{
		$sql = 'SELECT ban_item
				FROM '.TABLE_BANLIST.'
				WHERE ban_item = \''.$email.'\' AND ban_item_type = \'Email\'';
		return database_query($sql, ROWS_ONLY);		
	}

	/**
	 * Returns the account information
	 * @param $user_name - system name of user
	 * @param $password - password of user
	 * @param $do_direct_login
	 */
	private function GetAccountInformation($user_name, $password, $do_direct_login = false)
	{
		if(PASSWORDS_ENCRYPTION && !$do_direct_login){
			if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
				$password = 'AES_ENCRYPT(\''.$password.'\', \''.PASSWORDS_ENCRYPT_KEY.'\')';
			}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
				$password = 'MD5(\''.$password.'\')';
			}	
		}else{
			$password = '\''.$password.'\'';
		}
		
		if($this->accountType == 'admin'){
			$sql = 'SELECT '.TABLE_ACCOUNTS.'.*, user_name AS account_name
					FROM '.TABLE_ACCOUNTS.'
					WHERE
						user_name = \''.$user_name.'\' AND 
						password = '.$password;			
		}else{
			$sql = 'SELECT '.TABLE_CUSTOMERS.'.*, user_name AS account_name
					FROM '.TABLE_CUSTOMERS.'
					WHERE
						user_name = \''.$user_name.'\' AND 
						user_password = '.$password.' AND
						is_removed = 0';
		}
		///$sql .= ' AND is_active = 1';
		return database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
	}

	/**
	 * 	Checks if the user is logged in
	 */
	public function IsLoggedIn()
	{
		global $objSession;
		
		$logged = Session::Get('session_account_logged');
		$id = Session::Get('session_account_id');
	    if($logged == str_replace(array('modules/tinymce/plugins/imageupload/', 'ajax/', 'modules/ratings/lib/'), '', $this->GetUniqueUrl()).$id){
			if(!$objSession->AnalyseFingerInfo()){
				return false;
			}
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 	Returns last login time
	 */
	public function GetLastLoginTime()
	{
		$last_login = Session::Get('session_last_login');

		if(!is_empty_date($last_login)){
			return $last_login;
		}else{
			return '--';
		}
	}

	/**
	 * Checks if the user is logged in as a specific account type
	 * @return true if the user is logged in as specified account type, false otherwise
	 */
	public function IsLoggedInAs()
	{
		if(!$this->IsLoggedIn()) return false;
		$account_type = Session::Get('session_account_type');

		$types = func_get_args();
		foreach($types as $type){
			$type_parts = explode(',', $type);
			foreach($type_parts as $type_part){
				if($account_type == $type_part) return true;	
			}			
		}
		return false;
	}

	/**
	 * Checks if the user is logged in as a Admin
	 * @return true if the user is logged in as specified account type false otherwise
	 */
	public function IsLoggedInAsAdmin()
	{
		if (!$this->IsLoggedIn()) return false;
		$account_type = Session::Get('session_account_type');
		if(in_array($account_type, array('owner', 'mainadmin', 'admin', 'hotelowner', 'agencyowner', 'regionalmanager', 'hotelmanager'))) return true;
		return false;
	}

	/**
	 * Checks to see if the customer is logged in as a specific account type
	 * @param $check
	 */
	public function IsLoggedInAsCustomer($check = true)
	{
		if(!$this->IsLoggedIn() && $check) return false;
		$account_type = Session::Get('session_account_type');
		if($account_type == 'customer') return true;
		return false;
	}
	
	/**
	 * 	Returns logged user type
	 */
	public function GetLoggedType($check = true)
	{
		if(!$this->IsLoggedIn() && $check) return false;				
		return Session::Get('session_account_type');
	}

	/**
	 * 	Returns logged user email
	 */
	public function GetLoggedEmail($check = true)
	{
		if(!$this->IsLoggedIn() && $check) return false;
		return Session::Get('session_user_email');
	}

	/**
	 * 	Returns logged user profile photo
	 */
	public function GetLoggedPhoto($check = true)
	{
		if(!$this->IsLoggedIn() && $check) return false;
		return Session::Get('session_user_profile_photo');
	}

	/**
	 * 	Returns logged customer type
	 */
	public function GetCustomerType($check = true)
	{
		if(!$this->IsLoggedIn() && $check) return false;
		return Session::Get('session_customer_type');
	}

	/**
	 * 	Returns logged customer type
	 */
	public function GetAgencyLogo($check = true)
	{
		if(!$this->IsLoggedIn() && $check) return false;
		return Session::Get('session_agency_logo');
	}

	/**
	 * 	Sets new logo 
	 */
	public function UpdateAgencyLogo($new_logo)
	{
		if(!$this->IsLoggedIn()) return false;
		Session::Set('session_agency_logo', $new_logo);
	}

	/**
	 * 	Returns logged user group ID
	 */
	public function GetLoggedGroupID()
	{
		return ($this->IsLoggedIn()) ? Session::Get('session_account_group_id') : '0';
	}

	/**
	 * 	Sets the email of logged user 
	 */
	public function UpdateLoggedEmail($new_email = '')
	{
		if(!$this->IsLoggedIn()) return false;
		Session::Set('session_user_email', $new_email);
	}
	
	/**
	 * 	Sets the email of logged user photo 
	 */
	public function UpdateLoggedPhoto($new_photo = '')
	{
		if(!$this->IsLoggedIn()) return false;
		Session::Set('session_user_profile_photo', $new_photo);
	}
	
	/**
	 * 	Returns logged user name
	 */
	public function GetLoggedName()
	{
		return Session::Get('session_user_name');
	}
	
	/**
	 * 	Returns the first name of logged user 
	 */
	public function GetLoggedFirstName()
	{
		return Session::Get('session_user_first_name');
	}
	
	/**
	 * 	Returns the last name of logged user 
	 */
	public function GetLoggedLastName()
	{
		return Session::Get('session_user_last_name');
	}

	/**
	 * 	Update first name of logged user 
	 */
	public function UpdateLoggedFirstName($first_name)
	{
		return Session::Set('session_user_first_name', $first_name);
	}

	/**
	 * 	Update last name of logged user 
	 */
	public function UpdateLoggedLastName($last_name)
	{
		return Session::Set('session_user_last_name', $last_name);
	}

	/**
	 * 	Returns logged user ID
	 */
	public function GetLoggedID()
	{
		return Session::Get('session_account_id');
	}

	/**
	 * 	Returns preferred language
	 */
	public function GetPreferredLang()
	{
		return Session::Get('session_preferred_language');
	}	

	/**
	 * Sets preferred language
	 * @param $lang
	 */
	public function SetPreferredLang($lang)
	{
		Session::Set('session_preferred_language', $lang);
	}

    /**
     * 	Returns logged user ID
     */
    public function GetRegistrationType()
    {
        return Session::Get('session_registration_type');
    }

    /**
	 * Sets the session variables and performs the login
	 * @param $account_information - array
	 */
	private function SetSessionVariables($account_information)
	{		
		Session::Set('session_account_logged', (($account_information['id']) ? $this->GetUniqueUrl().$account_information['id'] : false));			
		Session::Set('session_account_id', $account_information['id']);
		Session::Set('session_user_name', $account_information['user_name']);
		Session::Set('session_user_first_name', $account_information['first_name']);
		Session::Set('session_user_last_name', $account_information['last_name']);		
		Session::Set('session_user_email', $account_information['email']);
		Session::Set('session_user_profile_photo', $account_information['profile_photo_thumb']);
		Session::Set('session_account_type', (($this->accountType == 'admin') ? $account_information['account_type'] : 'customer'));
		Session::Set('session_account_group_id', (($this->accountType == 'admin') ? '0' : $account_information['group_id']));
		Session::Set('session_last_login', $account_information['date_lastlogin']);
		Session::Set('session_customer_type', isset($account_information['customer_type']) ? $account_information['customer_type'] : '');
		Session::Set('session_agency_logo', isset($account_information['logo']) ? $account_information['logo'] : '');
		Session::Set('session_agency_logo', isset($account_information['logo']) ? $account_information['logo'] : '');
		Session::Set('session_registration_type', isset($account_information['registration_type']) ? $account_information['registration_type'] : '');

		// check if predefined lang still exists, if not set default language		
		if(isset($account_information['preferred_language']) && Languages::LanguageActive($account_information['preferred_language'])){
			$preferred_language = $account_information['preferred_language'];
		}else{
			$preferred_language = Languages::GetDefaultLang();
		}
		Session::Set('session_preferred_language', $preferred_language);

		// Prepare role privileges
		$result = Roles::GetPrivileges(Session::Get('session_account_type'));
		$privileges_info = array();
		for($i = 0; $i < $result[1]; $i++){
			$privileges_info[$result[0][$i]['code']] = ($result[0][$i]['is_active'] == '1') ? true : false;
		}
		
		// Set hotels
		$hotels = @explode(',', $account_information['companies']);
		if(is_array($hotels)){
			$privileges_info['hotel_ids'] = array();
			foreach($hotels as $key => $val){
				if(!empty($val)){
					$privileges_info['hotel_ids'][] = $val;
				}
			}
		}

		// Set transportation agencies 
		$agencies = @explode(',', $account_information['companies']);
		if(is_array($agencies)){
			$privileges_info['agency_ids'] = array();
			foreach($agencies as $key => $val){
				if(!empty($val)) {
					$privileges_info['agency_ids'][] = $val;
				}
			}
		}
		
		Session::Set('session_user_privileges', $privileges_info);

		// clean some session variables
		Session::Set('preview', '');
	}

	/**
	 *  Returns unique URL 
	 */
	private function GetUniqueUrl()
	{
		$port = '';
		$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80'){
			if(!strpos($http_host, ':')){
				$port = ':'.$_SERVER['SERVER_PORT'];
			}
		}	
		$folder = get_foolder();	
		return $http_host.$port.$folder;
	}
	
	/**
	 * Returns count of active menus
	 * @return number on menus
	 */
	public function GetActiveMenuCount()
	{
		return $this->activeMenuCount;
	}	
	
	/**
	 * Updates Account Info	
	 * @param $account_information - array
	 */
	private function UpdateAccountInfo($account_information)
	{
		if($this->accountType == 'admin'){
			$sql = 'UPDATE '.TABLE_ACCOUNTS.'
					SET date_lastlogin = \''.@date('Y-m-d H:i:s').'\'
					WHERE id = '.(int)$account_information['id'];
		}else{
			$sql = 'UPDATE '.TABLE_CUSTOMERS.'
					SET
						date_lastlogin = \''.@date('Y-m-d H:i:s').'\',
						last_logged_ip = \''.get_current_ip().'\'
					WHERE id = '.(int)$account_information['id'];			
		}
		return database_void_query($sql);
	}	

	/**
	 * 	Removes user account
	 */	
	public function RemoveAccount()
	{
		$sql = 'UPDATE '.TABLE_CUSTOMERS.'
				SET is_removed = 1, is_active = 0, comments = CONCAT(comments, "\r\n'.@date('Y-m-d H:i:s').' - account was removed by customer.") 
				WHERE id = '.(int)$this->GetLoggedID();
		return (database_void_query($sql) > 0 ? true : false);
	}

	/**
	 * 	Get Login Error
	 */	
	public function GetLoginError()
	{
		return defined($this->loginError) ? constant($this->loginError) : '';
	}

	/**
	 * Check if user has privilege
	 * @param $code
	 * @param $val
	 */
	public function HasPrivileges($code = '', $val = '')
	{
		$privileges_info = Session::Get('session_user_privileges');
		if(!empty($val)){
			if(isset($privileges_info[$code]) && is_array($privileges_info[$code])){
				return in_array($val, $privileges_info[$code]);
			}else{
				return ($privileges_info[$code] == $val);
			}			
		}else{
			return (isset($privileges_info[$code]) && $privileges_info[$code] == true) ? true : false;	
		}		
	}
	
	/**
	 * Checks if logged admin is assigned to given hotel
	 * @param int $hotel_id
	 * return bool
	 */
	public function AssignedToHotel($hotel_id = 0)
	{
		$return = false;

		if($this->IsLoggedInAs('owner','mainadmin')){
			$return = true;
		}elseif($this->IsLoggedInAs('hotelowner')){
			$privileges_info = Session::Get('session_user_privileges');
			$return = in_array($hotel_id, $privileges_info['hotel_ids']) ? true : false;
		}elseif($this->IsLoggedInAs('regionalmanager')){
			$hotel_ids = AccountLocations::GetHotels($this->GetLoggedID());
			$return = in_array($hotel_id, $hotel_ids) ? true : false;
		}
		
		return $return;		
	}	

	/**
	 * Return list of assigned hotels
	 */
	public function AssignedToHotels()
	{
		$privileges_info = Session::Get('session_user_privileges');
		return (isset($privileges_info['hotel_ids'])) ? $privileges_info['hotel_ids'] : array();
	}	

	/**
	 * Checks if logged admin is assigned to given agency
	 * @param int $agency_id
	 * return bool
	 */
	public function AssignedToCarAgency($agency_id = 0)
	{
		$return = false;

		if($this->IsLoggedInAs('owner','mainadmin')){
			$return = true;
		}elseif($this->IsLoggedInAs('agencyowner')){
			$privileges_info = Session::Get('session_user_privileges');
			$return = in_array($agency_id, $privileges_info['agency_ids']) ? true : false;
		}
		
		return $return;		
	}	

	/**
	 * Return list of assigned agencies
	 */
	public function AssignedToCarAgencies()
	{
		$privileges_info = Session::Get('session_user_privileges');
		return (isset($privileges_info['agency_ids'])) ? $privileges_info['agency_ids'] : array();
	}	

	/**
	 * 	Draws the login links and logout form
	 * 	@param $draw
	 */
	public function DrawLoginLinks($draw = true, $cascading_menu = false)
	{	
		global $objSettings;
		
		if(Application::Get('preview') == 'yes') return '';
		
		$menu_index = '0';
		$text_align = (Application::Get('lang_dir') == 'ltr') ? 'text-align: left;' : 'text-align: right; padding-right:15px;';
		$output = '';
		
		// ---------------------------------------------------------------------
		// MAIN ADMIN LINKS
		if($this->IsLoggedInAsAdmin()){
			
			$output .= '<ul>';
		
			$output .= '<li class="has_sub">';
				$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-view-dashboard"></i> <span> '._GENERAL.' </span> <span class="menu-arrow"></span></a>';
				$output .= '<ul class="list-unstyled">';

				$output .= $this->PrepareLink('home', _HOME);
				if($this->IsLoggedInAs('owner','mainadmin')) $output .= $this->PrepareLink('settings', _SETTINGS);
				if($this->IsLoggedInAs('owner','mainadmin','admin')) $output .= $this->PrepareLink('ban_list', _BAN_LIST);
				if($this->IsLoggedInAs('owner','mainadmin')) $output .= $this->PrepareLink('countries_management', _COUNTRIES, '', '', array('states_management'));
				if(!$this->IsLoggedInAs('agencyowner', 'regionalmanager')) $output .= '<li><a href="index.php?preview=yes">'._PREVIEW.' <img src="images/external_link.gif" alt="" /></a></li>';

				$output .= '</ul>';
			$output .= '</li>';

			$output .= '<li class="has_sub">';
				$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-accounts-list zmdi-hc-fw"></i> <span> '._ACCOUNTS_MANAGEMENT.' </span> <span class="menu-arrow"></span></a>';
				$output .= '<ul class="list-unstyled">';

				$output .= $this->PrepareLink('my_account', _MY_ACCOUNT);
				if(Modules::IsModuleInstalled('customers') && $this->IsLoggedInAs('owner','mainadmin')) $output .= $this->PrepareLink('accounts_statistics', _STATISTICS);
				if($this->IsLoggedInAs('owner')) $output .= $this->PrepareLink('roles_management', _ROLES_AND_PRIVILEGES, '', '', array('role_privileges_management'));
				if($this->IsLoggedInAs('owner','mainadmin')) $output .= $this->PrepareLink('mod_accounts_settings', _ACCOUNTS_SETTINGS);

				if($this->IsLoggedInAs('owner','mainadmin')){
					if($cascading_menu){
						$output .= '<li class="has_sub">';
						$output .= '<a href="javascript:void(0);">'._ADMINS_MANAGEMENT.'</a>';
						$output .= '<ul class="list-unstyled">';
					}else{
						$output .= '<li class="text-muted menu-title">'._ADMINS_MANAGEMENT.'</li>';
					}
					$output .= $this->PrepareLink('admins_management', _ADMINS);
					$output .= $this->PrepareLink('hotel_owners_management', FLATS_INSTEAD_OF_HOTELS ? _FLAT_OWNERS : _HOTEL_OWNERS);
					$output .= $this->PrepareLink('regional_managers_management', _REGIONAL_MANAGERS);
					if(Modules::IsModuleInstalled('car_rental') && $this->IsLoggedInAs('owner','mainadmin')){
						$output .= $this->PrepareLink('mod_car_rental_owners_management', lang('_CAR_AGENCY_OWNERS'));
					}
					if($cascading_menu){
						$output .= '</li>';
						$output .= '</ul>';
					}
				}
				
				if(Modules::IsModuleInstalled('customers') && $this->IsLoggedInAs('owner','mainadmin','regionalmanager','hotelowner')){
					if(!$this->IsLoggedInAs('regionalmanager', 'hotelowner')){
						if($cascading_menu){
							$output .= '<li class="has_sub">';
							$output .= '<a href="javascript:void(0);">'._CUSTOMERS_MANAGEMENT.'</a>';
							$output .= '<ul class="list-unstyled">';
						}else{
							$output .= '<li class="text-muted menu-title">'._CUSTOMERS_MANAGEMENT.'</li>';
						}
						$output .= $this->PrepareLink('mod_customers_groups', _CUSTOMER_GROUPS);
						$output .= $this->PrepareLink('mod_customers_management', _CUSTOMERS);
						if(ModulesSettings::Get('customers', 'allow_agencies') == 'yes'){
							$output .= $this->PrepareLink('mod_customers_agencies', _TRAVEL_AGENCIES);
						}
						if($cascading_menu){
							$output .= '</li>';
							$output .= '</ul>';
						}
					}elseif($this->IsLoggedInAs('regionalmanager')){
						$output .= $this->PrepareLink('mod_customers_agencies', _TRAVEL_AGENCIES);
					}elseif($this->IsLoggedInAs('hotelowner')){
						$output .= $this->PrepareLink('mod_customers_management', _MY_CUSTOMERS);
					}
				}

				$output .= '</ul>';
			$output .= '</li>';

			if($this->IsLoggedInAs('owner','mainadmin','hotelowner','regionalmanager')){
				$output .= '<li class="has_sub">';
					$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-hotel zmdi-hc-fw"></i> <span> '.(FLATS_INSTEAD_OF_HOTELS ? _FLATS_MANAGEMENT : _HOTELS_MANAGEMENT).' </span> <span class="menu-arrow"></span></a>';
					$output .= '<ul class="list-unstyled">';					
					
					if($cascading_menu){
						$output .= '<li class="has_sub">';
						$output .= '<a href="javascript:void(0);">'.(FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS_AND_ROOMS).'</a>';
						$output .= '<ul class="list-unstyled">';
					}else{
						$output .= '<li class="text-muted menu-title">'.(FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS_AND_ROOMS).'</li>';
					}
					$output .= $this->PrepareLink('hotels_info', FLATS_INSTEAD_OF_HOTELS ? _FLATS_INFO : _HOTELS_INFO, '', '', array('hotel_upload_images', 'hotel_default_periods'));
					if($this->IsLoggedInAs('owner','mainadmin', 'regionalmanager') || ($this->IsLoggedInAs('hotelowner') && $this->HasPrivileges('edit_hotel_rooms'))) $output .= $this->PrepareLink('mod_rooms_management', _ROOMS_MANAGEMENT);
					if($this->IsLoggedInAs('owner','mainadmin') || ($this->IsLoggedInAs('hotelowner') && $this->HasPrivileges('edit_hotel_info'))) $output .= $this->PrepareLink('mod_booking_meal_plans', _MEAL_PLANS);
					if($this->IsLoggedInAs('owner','mainadmin')) $output .= $this->PrepareLink('hotels_property_types', _PROPERTY_TYPES);
					if($cascading_menu){
						$output .= '</li>';
						$output .= '</ul>';
					}
					
					if($cascading_menu){
						$output .= '<li class="has_sub">';
						$output .= '<a href="javascript:void(0);">'._SETTINGS.'</a>';
						$output .= '<ul class="list-unstyled">';
					}else{
						$output .= '<li class="text-muted menu-title">'._SETTINGS.'</li>';
					}
					if($this->IsLoggedInAs('owner','mainadmin')){
						$output .= $this->PrepareLink('mod_rooms_settings', _ROOMS_SETTINGS);
						$output .= $this->PrepareLink('hotels_locations', _LOCATIONS);
						$output .= $this->PrepareLink('facilities_management', _FACILITIES);
						$output .= $this->PrepareLink('mod_rooms_integration', _INTEGRATION);
					}elseif($this->IsLoggedInAs('hotelowner')){
						$output .= $this->PrepareLink('mod_rooms_integration', _INTEGRATION);
					}elseif($this->IsLoggedInAs('regionalmanager')){
						$output .= $this->PrepareLink('hotels_locations', _LOCATIONS);
					}
					
					if($cascading_menu){
						$output .= '</li>';
						$output .= '</ul>';
					}					
					
					$output .= '</ul>';
				$output .= '</li>';
			}
			
			if(Modules::IsModuleInstalled('booking') && $this->IsLoggedInAs('owner','mainadmin','admin','hotelowner','regionalmanager')){
				$output .= '<li class="has_sub">';
					$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-money-box zmdi-hc-fw"></i> <span> '._BOOKINGS.' </span> <span class="menu-arrow"></span></a>';
					$output .= '<ul class="list-unstyled">';
					
					if($cascading_menu){
						$output .= '<li class="has_sub">';
						$output .= '<a href="javascript:void(0);">'._BOOKINGS_MANAGEMENT.'</a>';
						$output .= '<ul class="list-unstyled">';
					}else{
						$output .= '<li class="text-muted menu-title">'._BOOKINGS_MANAGEMENT.'</li>';
					}
					if(in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'back-end')) && $this->HasPrivileges('add_bookings')){
						$output .= $this->PrepareLink('mod_booking_reserve_room', _MAKE_RESERVATION);
						$output .= $this->PrepareLink('mod_quick_reservations', _QUICK_RESERVATIONS);
					}
					$output .= $this->PrepareLink('mod_booking_bookings', _BOOKINGS);

					if($cascading_menu){
						$output .= '</li>';
						$output .= '</ul>';
					}

					if($this->IsLoggedInAs('owner','mainadmin','hotelowner')){
						if($cascading_menu){
							$output .= '<li class="has_sub">';
							$output .= '<a href="javascript:void(0);">'._SETTINGS.'</a>';
							$output .= '<ul class="list-unstyled">';
						}else{
							$output .= '<li class="text-muted menu-title">'._SETTINGS.'</li>';
						}
						if($this->IsLoggedInAs('owner','mainadmin')){
							$output .= $this->PrepareLink('mod_booking_currencies', _CURRENCIES);
						}
						
						$output .= $this->PrepareLink('mod_booking_packages', _PACKAGES);
						$output .= $this->PrepareLink('mod_booking_extras', _EXTRAS);
						if($this->IsLoggedInAs('hotelowner') && ModulesSettings::Get('booking', 'allow_separate_gateways') == 'yes' && $this->HasPrivileges('view_hotel_payments')){
                            $output .= $this->PrepareLink('mod_booking_hotel_payment_gateways', FLATS_INSTEAD_OF_HOTELS ? _FLAT_PAYMENT_GATEWAYS : _HOTEL_PAYMENT_GATEWAYS);
                        }
					

						if($cascading_menu){
							$output .= '</li>';
								$output .= '</ul>';
						}
					}

					if($this->IsLoggedInAs('owner','mainadmin','regionalmanager') || $this->IsLoggedInAs('hotelowner')){
						if($cascading_menu){
							$output .= '<li class="has_sub">';
							$output .= '<a href="javascript:void(0);">'._PROMO_AND_DISCOUNTS.'</a>';
							$output .= '<ul class="list-unstyled">';
						}else{
							$output .= '<li class="text-muted menu-title">'._PROMO_AND_DISCOUNTS;
						}
						
						$output .= $this->PrepareLink('mod_booking_campaigns', _CAMPAIGNS);
						$output .= $this->PrepareLink('mod_booking_coupons', _COUPONS);

						if($cascading_menu){
							$output .= '</li>';
							$output .= '</ul>';
						}
					}

					if($this->IsLoggedInAs('owner','mainadmin','admin') || ($this->IsLoggedInAs('hotelowner') && $this->HasPrivileges('view_hotel_reports'))){
						if(Modules::IsModuleInstalled('booking')){				
							if(ModulesSettings::Get('booking', 'is_active') != 'no'){
								if($cascading_menu){
									$output .= '<li class="has_sub">';
									$output .= '<a href="javascript:void(0);">'._INFO_AND_STATISTICS.'</a>';
									$output .= '<ul class="list-unstyled">';
								}else{
									$output .= '<li class="text-muted menu-title">'._INFO_AND_STATISTICS.'</li>';
								}

								$output .= $this->PrepareLink('mod_booking_rooms_occupancy', _ROOMS_OCCUPANCY);
								$output .= $this->PrepareLink('mod_booking_reports', _REPORTS);
								$output .= $this->PrepareLink('mod_booking_statistics', _STATISTICS);

								if($cascading_menu){
									$output .= '</li>';
									$output .= '</ul>';
								}
							}
						}
					}

					$output .= '</ul>';
				$output .= '</li>';
			}

			if(Modules::IsModuleInstalled('car_rental') && $this->IsLoggedInAs('owner','mainadmin','agencyowner')){
				if($this->IsLoggedInAs('owner','mainadmin') ||
				  ($this->IsLoggedInAs('agencyowner') && ModulesSettings::Get('car_rental', 'is_active') != 'no')
				){
					$output .= '<li class="has_sub">';
						$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-car zmdi-hc-fw"></i> <span> '.lang('_CAR_RENTAL').' </span> <span class="menu-arrow"></span></a>';
						$output .= '<ul class="list-unstyled">';
		
						if($cascading_menu){
							$output .= '<li class="has_sub">';
							$output .= '<a href="javascript:void(0);">'._SETTINGS.'</a>';
							$output .= '<ul class="list-unstyled">';
						}else{
							$output .= '<li class="text-muted menu-title">'._SETTINGS.'</li>';
						}
						$output .= $this->PrepareLink('mod_car_rental_integration', _INTEGRATION);
		
						if($this->IsLoggedInAs('owner','mainadmin')){
							$output .= $this->PrepareLink('mod_car_rental_settings', lang('_CAR_RENTAL_SETTINGS'));
							$output .= $this->PrepareLink('mod_car_rental_locations', _LOCATIONS);
							$output .= $this->PrepareLink('mod_car_rental_makes', lang('_MAKES'));
							$output .= $this->PrepareLink('mod_car_rental_vehicle_categories', lang('_VEHICLE_CATEGORIES'));
						}
						if($cascading_menu){
							$output .= '</li>';
							$output .= '</ul>';

							$output .= '<li class="has_sub">';
							$output .= '<a href="javascript:void(0);">'.lang('_TRANSPORTATION_AGENCIES').'</a>';
							$output .= '<ul class="list-unstyled">';
						}else{
							$output .= '<li class="text-muted menu-title">'.lang('_TRANSPORTATION_AGENCIES').'</li>';
						}
						$output .= $this->PrepareLink('mod_car_rental_agencies', lang('_AGENCIES_INFO'));					
						$output .= $this->PrepareLink('mod_car_rental_vehicle_types', lang('_AGENCY_CAR_TYPES'));
						$output .= $this->PrepareLink('mod_car_rental_vehicles', lang('_CAR_INVENTORY'));
						if($cascading_menu){
							$output .= '</li>';
							$output .= '</ul>';

							$output .= '<li class="has_sub">';
							$output .= '<a href="javascript:void(0);">'.lang('_TRANSPORTATION_AGENCIES').'</a>';
							$output .= '<ul class="list-unstyled">';
						}else{
							$output .= '<li class="text-muted menu-title">'.lang('_CAR_RESERVATIONS').'</li>';
						}
						$output .= $this->PrepareLink('mod_car_rental_reservations', _RESERVATIONS);
						$output .= $this->PrepareLink('mod_car_rental_reports', _REPORTS);

						if($cascading_menu){
							$output .= '</li>';
							$output .= '</ul>';
						}
		
						if($this->IsLoggedInAs('agencyowner') && ModulesSettings::Get('car_rental', 'allow_separate_gateways') == 'yes'){
							if($cascading_menu){
								$output .= '<li class="has_sub">';
								$output .= '<a href="javascript:void(0);">'._SETTINGS.'</a>';
								$output .= '<ul class="list-unstyled">';
							}else{
								$output .= '<li class="text-muted menu-title">'._SETTINGS.'</li>';
							}
							
							$output .= $this->PrepareLink('mod_car_rental_payment_gateways', lang('_CAR_AGENCY_PAYMENT_GATEWAYS'));

							if($cascading_menu){
								$output .= '</li>';
								$output .= '</ul>';
							}
						}
		
						$output .= '</ul>';
					$output .= '</li>';
				}
			}

			if($this->HasPrivileges('add_menus') || $this->HasPrivileges('edit_menus') || $this->HasPrivileges('add_pages') || $this->HasPrivileges('edit_pages')){				
				$output .= '<li class="has_sub">';
					$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-collection-item"></i> <span> '._MENUS_AND_PAGES.' </span> <span class="menu-arrow"></span></a>';
					$output .= '<ul class="list-unstyled">';

					if($this->HasPrivileges('add_menus') || $this->HasPrivileges('edit_menus')){
						if($cascading_menu){
							$output .= '<li class="has_sub">';
							$output .= '<a href="javascript:void(0);">'._MENU_MANAGEMENT.'</a>';
							$output .= '<ul class="list-unstyled">';
						}else{
							$output .= '<li class="text-muted menu-title">'._MENU_MANAGEMENT.'</li>';
						}
						if($this->HasPrivileges('add_menus')) $output .= $this->PrepareLink('menus_add', _ADD_NEW_MENU);
						$output .= $this->PrepareLink('menus', _EDIT_MENUS, '', '', array('menus_edit'));
						if($cascading_menu){
							$output .= '</li>';
							$output .= '</ul>';
						}
					}

					if($this->HasPrivileges('add_pages') || $this->HasPrivileges('edit_pages')){
						if($cascading_menu){
							$output .= '<li class="has_sub">';
							$output .= '<a href="javascript:void(0);">'._PAGE_MANAGEMENT.'</a>';
							$output .= '<ul class="list-unstyled">';
						}else{
							$output .= '<li class="text-muted menu-title">'._PAGE_MANAGEMENT.'</li>';
						}
						if($this->HasPrivileges('add_pages')) $output .= $this->PrepareLink('pages_add', _PAGE_ADD_NEW);
						if($this->HasPrivileges('edit_pages')) $output .= $this->PrepareLink('pages_edit', _PAGE_EDIT_HOME, 'type=home');
						$output .= $this->PrepareLink('pages', _PAGE_EDIT_PAGES, 'type=general');
						if($this->HasPrivileges('edit_pages')) $output .= $this->PrepareLink('pages', _PAGE_EDIT_SYS_PAGES, 'type=system');				
						if($this->HasPrivileges('edit_pages')) $output .= $this->PrepareLink('pages_trash', _TRASH);				
						if($cascading_menu){
							$output .= '</li>';
							$output .= '</ul>';
						}
					}

					$output .= '</ul>';
				$output .= '</li>';
			}

			if($this->IsLoggedInAs('owner','mainadmin','admin')){
				$output .= '<li class="has_sub">';
					$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-settings zmdi-hc-fw"></i> <span> '.($objSettings->GetParameter('type_menu') == 'horizontal' ? _LANGUAGES : _LANGUAGES_SETTINGS).' </span> <span class="menu-arrow"></span></a>';
					$output .= '<ul class="list-unstyled">';

					if($this->IsLoggedInAs('owner','mainadmin')) $output .= $this->PrepareLink('languages', _LANGUAGES, '', '', array('languages_add','languages_edit'));
					if($this->IsLoggedInAs('owner','mainadmin','admin')) $output .= $this->PrepareLink('vocabulary', _VOCABULARY, 'filter_by=A');

					$output .= '</ul>';
				$output .= '</li>';
			} 

			if($this->IsLoggedInAs('owner','mainadmin')){
				$output .= '<li class="has_sub">';
					$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-email zmdi-hc-fw"></i> <span> '.($objSettings->GetParameter('type_menu') == 'horizontal' ? _MASS_MAIL : _MASS_MAIL_AND_TEMPLATES).' </span> <span class="menu-arrow"></span></a>';
					$output .= '<ul class="list-unstyled">';

					if($this->IsLoggedInAs('owner','mainadmin')) $output .= $this->PrepareLink('email_templates', _EMAIL_TEMPLATES);
					if($this->IsLoggedInAs('owner','mainadmin')) $output .= $this->PrepareLink('mass_mail', _MASS_MAIL);
					if($this->IsLoggedInAs('owner')) $output .= $this->PrepareLink('mail_log', _MAIL_LOG);

					$output .= '</ul>';
				$output .= '</li>';
			}
			
			// MODULES: application or additional
			$sql = 'SELECT * FROM '.TABLE_MODULES.' WHERE is_installed = 1 AND module_type IN(1,2) ORDER BY priority_order ASC';
			$modules = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			
			$modules_output = '';
			for($i=0; $i < $modules[1]; $i++){
				$inner_output = '';
				if($modules[0][$i]['settings_access_by'] == '' || ($modules[0][$i]['settings_access_by'] != '' && $this->IsLoggedInAs($modules[0][$i]['settings_access_by']))){
					if($modules[0][$i]['settings_const'] != '') $inner_output .= $this->PrepareLink($modules[0][$i]['settings_page'], (defined($modules[0][$i]['settings_const']) ? constant($modules[0][$i]['settings_const']) : humanize($modules[0][$i]['settings_const'])));
				}
				if($modules[0][$i]['management_access_by'] == '' || ($modules[0][$i]['management_access_by'] != '' && $this->IsLoggedInAs($modules[0][$i]['management_access_by']))){
					$management_pages = explode(',', $modules[0][$i]['management_page']);
					$management_consts = explode(',', $modules[0][$i]['management_const']);
					$management_pages_total = count($management_pages);
					for($j=0; $j < $management_pages_total; $j++){
						if(isset($management_pages[$j]) && isset($management_consts[$j]) && $management_consts[$j] != ''){
							$inner_output .= $this->PrepareLink($management_pages[$j], (defined($management_consts[$j]) ? constant($management_consts[$j]) : humanize($management_consts[$j])));
						}
					}							
				}
				if($inner_output){
					if($cascading_menu){
						$modules_output .= '<li class="has_sub">';
						$modules_output .= '<a href="javascript:void(0);">'.(defined($modules[0][$i]['name_const']) ? constant($modules[0][$i]['name_const']) : humanize($modules[0][$i]['name_const'])).'</a>';
						$modules_output .= '<ul class="list-unstyled">';
					}else{
						$modules_output .= '<li class="text-muted menu-title">'.(defined($modules[0][$i]['name_const']) ? constant($modules[0][$i]['name_const']) : humanize($modules[0][$i]['name_const'])).'</li>';
					}
					$modules_output .= $inner_output;
					if($cascading_menu){
						$modules_output .= '</li>';
						$modules_output .= '</ul>';
					}
				}
			}				

			if(!empty($modules_output)){
				$output .= '<li class="has_sub">';
					$output .= '<a href="javascript:void(0);" class="waves-effect"><i class="zmdi zmdi-view-module zmdi-hc-fw"></i> <span> '._MODULES.' </span> <span class="menu-arrow"></span></a>';
					$output .= '<ul class="list-unstyled">';

					if($this->IsLoggedInAs('owner','mainadmin')){
						$output .= $this->PrepareLink('modules', _MODULES_MANAGEMENT);				
					}						
					$output .= $modules_output;	

					$output .= '</ul>';
				$output .= '</li>';
			}
			
			$output .= '</ul>';
		}
	
		// ---------------------------------------------------------------------
		// CUSTOMERS LINKS
		if($this->IsLoggedInAsCustomer()){
			// Logged in as travel agency
			if($this->GetCustomerType() == 1){
				$allow_payment_with_balance = ModulesSettings::Get('booking', 'allow_payment_with_balance') == 'yes' ? true : false;
				$balance = Customers::GetCustomerInfo($this->GetLoggedID(), 'balance');
				$coupon_code = Coupons::GetCouponByCustomerId($this->GetLoggedID(), 'coupon_code');
				$default_currency = Currencies::GetDefaultCurrency();
				$output .= draw_block_top(_AGENCY, -2, 'maximized', '', false);
					$output .= '<ul>';
					if($allow_payment_with_balance) $output .= '<li><a href="index.php?customer=my_funds">'._FUNDS_INFORMATION.'</a></li>';
					if($allow_payment_with_balance) $output .= '<li>'._ACCOUNT_BALANCE.': <br>'.($balance < 100 ? '<span class="darkred">'.$default_currency.$balance.' ('._LOW_BALANCE.')</span>' : $default_currency.$balance).'</li>';
					if(!empty($coupon_code))  $output .= '<li>'._COUPON_CODE.': <br>'.$coupon_code.'</li>';
					$output .= '</ul>';
				$output .= draw_block_bottom(false);
			}

			$output .= draw_block_top(_MY_ACCOUNT, -1, 'maximized', '', false);
				$output .= '<ul>';
				$output .= $this->PrepareLink('home', _DASHBOARD);
				$output .= $this->PrepareLink('my_account', _EDIT_MY_ACCOUNT);
				if(Modules::IsModuleInstalled('booking')){				
					if(in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'front-end'))){
						$output .= $this->PrepareLink('my_bookings', _MY_BOOKINGS);
					}
				}
				if(Modules::IsModuleInstalled('reviews')){				
					$output .= $this->PrepareLink('my_reviews', _MY_REVIEWS);
				}
				$output .= $this->PrepareLink('my_wishlist', _WISHLIST);
				if(Modules::IsModuleInstalled('car_rental')){
					if(ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
						$output .= $this->PrepareLink('my_car_rentals', _MY_CAR_RENTALS);
					}
				}
				$output .= '</ul>';
				// logout
				$output .= '<form action="index.php?customer='.($this->GetCustomerType() == 1 ? TRAVEL_AGENCY_LOGIN : 'login').'" style="margin-bottom:7px;" method="post">
					  '.draw_hidden_field('submit_logout', 'logout', false).'
					  &nbsp;&nbsp;
					  <input class="form_button" type="submit" name="btnLogout" value="'._BUTTON_LOGOUT.'" />&nbsp;&nbsp;
					  </form>';
			$output .= draw_block_bottom(false);
		}		
		
		$this->activeMenuCount = $menu_index;

		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 * Prepare admin panel link
	 * @param $href
	 * @param $link
	 * @param $params
	 * @param $class
	 * @param $href_array
	 * @param $li_wrapper
	 */
	private function PrepareLink($href, $link, $params='', $class='', $href_array=array(), $li_wrapper = true)
	{
		$output = '';
		$css_class = (($class != '') ? $class : '');
		$logged_as = ($this->IsLoggedInAsCustomer()) ? 'customer' : 'admin';
		
		if(Application::Get($logged_as) == $href || in_array(Application::Get($logged_as), $href_array)){
			$is_active = true;
			if(!empty($params)){
				$params_parts = explode('=', $params);
				$f_param  = (isset($params_parts[0]) && isset($_GET[$params_parts[0]])) ? $_GET[$params_parts[0]] : '';
				$s_param = isset($params_parts[1]) ? $params_parts[1] : '';
				if($f_param != $s_param) $is_active = false; 
			}
		}else{
			$is_active = false;
		}
		
		if(!empty($css_class)){
			$css_class = ($is_active ? $css_class.' active' : '');	
		}else{
			$css_class = ($is_active ? 'active' : '');	
		}
	
		$output = prepare_permanent_link('index.php?'.$logged_as.'='.$href.((!empty($params)) ? '&'.$params : $params), $link, '', $css_class);	
		if($li_wrapper){
			$output = '<li'.($is_active ? ' class="active"' : '').'>'.$output.'</li>';
		}
		
		return $output;
	}
	
	/**
	 * Encrypt
	 * @param $value
	 * @param $secret_key
	 */
	private function Encrypt($value, $secret_key)
    {
		if(version_compare(phpversion(), '7.0.0', '<')){
			$return = trim(strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secret_key, $value, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))), '+/=', '-_,'));
		}else{
			// Generate an initialization vector
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
			// Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector
			$encrypted = openssl_encrypt($value, 'aes-256-cbc', $secret_key, 0, $iv);
			// The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (self::$_separator)
			$return = base64_encode($encrypted . '::' . base64_encode($iv));
		}
		
		return $return;
    }
	
	/**
	 * Decrypt
	 * @param $value
	 * @param $secret_key
	 */
	private function Decrypt($value, $secret_key)
	{
		if(version_compare(phpversion(), '7.0.0', '<')){
			$return = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secret_key, base64_decode(strtr($value, '-_,', '+/=')), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
		}else{
			// To decrypt, split the encrypted data from our IV - our unique separator used was '::'
			list($encrypted_data, $iv) = array_pad(explode('::', base64_decode($value), 2), 2, '');
			// Validate IV value
			$iv = base64_decode($iv);
			if ( strlen($iv) == 16) {
				$return = openssl_decrypt($encrypted_data, 'aes-256-cbc', $secret_key, 0, $iv);
			} else {
				$return = '';
			}
		}
		
		return $return;
	}
	
	
	/**
	 * Check if the current user already have authenticated using this provider before
	 * @param $provider_name
	 * @param $user_identifier
	 */
	function get_user_by_provider_and_id($provider_name, $user_identifier)
	{
		$sql = "SELECT ban_item
				FROM ".TABLE_CUSTOMERS."
				WHERE registered_via_provider = '".$provider_name."' AND provider_identifier = '".$user_identifier."'";
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		if ($result) {
			return array('user_name' => $result[0]['user_name'], 'pwd' => $result[0]['user_password']);
		}
	
		return false;
	}
	
	/**
	 * 	If the used didn't authenticate using the selected provider before we create a new entry on database.users for him
	 * @param $email
	 * @param $firstName
	 * @param $lastName
	 * @param $provider_name
	 * @param $provider_identifier
	 */
	function create_new_hybridauth_user($email, $firstName, $lastName, $provider_name, $provider_identifier)
	{
		$password = get_random_string(20);
		$sql = "INSERT INTO ".TABLE_CUSTOMERS."
				(id, email, user_name, user_password, first_name, last_name, registered_via_provider, provider_identifier, b_address, b_address_2, b_city, b_state, b_country, b_zipcode, phone, registered_from_ip, comments, registration_code, is_active)
				VALUES
				(NULL, '".$email."', '".$email."', '".$password."', '".$firstName."', '".$lastName."', '".$provider_name."', '".$provider_identifier."', '', '', '', '', '', '', '', '', '', '', 1)";
		$result = database_void_query($sql);
		if($result){
			return array('user_name' => $email, 'pwd' => $password);
		}else{
			return false;
		}
	}
	
}

