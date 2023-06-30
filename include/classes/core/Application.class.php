<?php

/***
 * Class Application
 * -------------- 
 * Description : encapsulates application properties and methods
 * Written by  : ApPHP
 * Version     : 1.0.6
 * Updated	    : 03.08.2016
 * Usage       : Core Class
 * Differences : $PROJECT
 *
 * PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *                         Init
 *                         Get
 *                         Set
 *                         SetLibraries
 *                         DrawPreview
 *
 * ChangeLog:
 * ----------
 * 1.0.6
 * 	- added new method GetSystemAlerts
 * 	- added new param affiliate_id
 * 	-
 * 	-
 * 	-
 * 1.0.5
 *     - added rtl to lytebox
 *     - added 'property_types'
 *     - fixed error in self::$params['page']
 *     - added additional check for 'lang'
 *     - added ADMIN_LOGIN
 * 1.0.4
 *     - templates section moved after META tags section
 *     - bug fixed for wrong META tags for BusinessDirectory
 *     - DoctorsSpecialities renamed into DoctorSpecialities
 *     - changes for MedicalAppointment
 *     - changes for MedicalAppointment according to selected currency
 * 1.0.3
 *     - added changes for self::$params['search_in']
 *     - added 'currency_decimals'
 *     - added if(file_exists('cron.php')) include_once('cron.php');		
 *     - added drawing hotel and rooms META tags
 *     - added specia lMETA tags for customer & user pages	    
 * 1.0.2
 *     - added Title = Listing | Category for BusinessDirectory
 *     - added changed - doctor for MedicalAppointment
 *     - fixed bug for Categories::GetCategoryInfo()
 *     - added template for offline message
 *     - issues fixed for creating titel for Listings
 **/

class Application {
	
	//------------------------------
	private static $PROJECT = PROJECT_NAME;

	private static $params = array(
		'template'        	=> 'default',
		'tag_title'       	=> '',
		'tag_description' 	=> '',
		'tag_keywords'    	=> '',
		
		'defined_left' 	  	=> 'left',
		'defined_right'   	=> 'right',
		'defined_alignment' => 'left',
		'preview'           => '',
		
		'admin'             => '',
		'customer'          => '',
		'mobile'          	=> '',
		'system_page'       => '',
		'type'              => '',
		'page'              => '',
		'page_id'           => '',
		'news_id'        	=> '',		
		'album_code'        => '',
		'search_in'         => '',		
		
		'lang'              => 'en',
		'lang_dir'          => 'ltr',
		'lang_name'         => 'English',
		'lang_name_en'      => 'English',
		'lc_time_name'      => 'en_US',

		'currency'          => '',
		'currency_code'     => '',
		'currency_symbol'   => '',
		'currency_rate'     => '',
        'currency_decimals' => '',
		'currency_symbol_place' => '',

		'token'             => '',
		'render'            => '',

		'js_included'       => '',
		
		'allow_last_visited' => false,

        'hotel_id'          => '',
        'room_id'           => '',
        'doctor_id'         => '',
		'property_types'    => '',
		'hotel_owner_id'    => '',
	);


	//==========================================================================
    // Class Initialization
	//==========================================================================
	public static function Init()
	{
		global $objLogin, $objSettings, $objSiteDescription;

		self::$params['page'] = isset($_GET['page']) ? prepare_input_alphanumeric($_GET['page']) : 'home';
		self::$params['page_id'] = isset($_REQUEST['pid']) ? prepare_input_alphanumeric($_REQUEST['pid']) : 'home';
		self::$params['system_page'] = isset($_GET['system_page']) ? prepare_input($_GET['system_page']) : '';
		self::$params['type'] = isset($_GET['type']) ? prepare_input($_GET['type']) : '';
		self::$params['admin'] = isset($_GET['admin']) ? prepare_input($_GET['admin']) : '';
		self::$params['customer'] = isset($_GET['customer']) ? prepare_input($_GET['customer']) : '';
		self::$params['mobile'] = isset($_GET['mobile']) ? prepare_input($_GET['mobile']) : null;
		self::$params['news_id'] = isset($_GET['nid']) ? (int)$_GET['nid'] : '';
		self::$params['album_code'] = isset($_GET['acode']) ? strip_tags(prepare_input_alphanumeric($_GET['acode'])) : '';
		self::$params['search_in'] = isset($_POST['search_in']) ? prepare_input($_POST['search_in']) : '';
		self::$params['lang'] = (isset($_GET['lang']) && strlen($_GET['lang']) == 2) ? prepare_input_alphanumeric($_GET['lang']) : '';
		self::$params['currency'] = isset($_GET['currency']) ? prepare_input($_GET['currency']) : '';
		self::$params['token'] = isset($_GET['token']) ? prepare_input($_GET['token']) : '';
		self::$params['hotel_id'] = isset($_GET['hid']) ? (int)$_GET['hid'] : '';
		self::$params['room_id'] = isset($_GET['room_id']) ? (int)$_GET['room_id'] : '';
		self::$params['hotel_owner_id'] = isset($_GET['hotel_owner_id']) ? (int)$_GET['hotel_owner_id'] : '';
		self::$params['render'] = isset($_GET['render']) && $_GET['render'] === 'partial' ? 'partial' : '';
		if (self::$params['search_in'] == '') {
			self::$params['search_in'] = 'rooms';
		}

		// Prepare affiliate parameter
		$affiliate_param = ModulesSettings::Get('affiliates', 'url_parameter');
		self::$params['affiliate_id'] = isset($_GET[$affiliate_param]) ? prepare_input_alphanumeric($_GET[$affiliate_param]) : '';
		$req_preview = isset($_GET['preview']) ? prepare_input($_GET['preview']) : '';

		//------------------------------------------------------------------------------
		// check and set token
		$token = md5(uniqid(rand(), true));
		self::$params['token'] = $token;
		Session::Set('token', $token);

		//------------------------------------------------------------------------------
		// save last visited page
		if (self::$params['allow_last_visited'] && !$objLogin->IsLoggedIn()) {
			$condition = (!empty(self::$params['page']) && self::$params['page'] != 'home');
			if (in_array(self::$PROJECT, array('HotelSite', 'HotelBooking'))) {
				$condition = (self::$params['page'] == 'booking' || self::$params['page'] == 'booking_details');
			}
			if ($condition) {
				Session::Set('last_visited', 'index.php?page=' . self::$params['page']);
				if (self::$params['page'] == 'pages' && !empty(self::$params['page_id']) && self::$params['page_id'] != 'home') {
					Session::Set('last_visited', Session::Get('last_visited') . '&pid=' . self::$params['page_id']);
				} elseif (self::$params['page'] == 'news' && !empty(self::$params['news_id'])) {
					Session::Set('last_visited', Session::Get('last_visited') . '&nid=' . self::$params['news_id']);
				}
			}
		}

		//------------------------------------------------------------------------------
		// set language
		if ($objLogin->IsLoggedInAsAdmin()) {
			$pref_lang = $objLogin->GetPreferredLang();
			self::$params['lang'] = (Languages::LanguageExists($pref_lang, false)) ? $pref_lang : Languages::GetDefaultLang();
			$language_info = Languages::GetLanguageInfo(self::$params['lang']);
			self::$params['lang_dir'] = $language_info['lang_dir'];
			self::$params['lang_name'] = $language_info['lang_name'];
			self::$params['lang_name_en'] = $language_info['lang_name_en'];
			self::$params['lc_time_name'] = $language_info['lc_time_name'];
		} else {
			if (!$objLogin->IsLoggedIn() && (self::$params['admin'] == ADMIN_LOGIN || self::$params['admin'] == 'password_forgotten')) {
				self::$params['lang'] = Languages::GetDefaultLang();
				$language_info = Languages::GetLanguageInfo(self::$params['lang']);
				self::$params['lang_dir'] = $language_info['lang_dir'];
				self::$params['lang_name'] = $language_info['lang_name'];
				self::$params['lang_name_en'] = $language_info['lang_name_en'];
				self::$params['lc_time_name'] = $language_info['lc_time_name'];
			} elseif (!empty(self::$params['lang']) && Languages::LanguageExists(self::$params['lang'])) {
				//self::$params['lang']         = self::$params['lang'];
				$language_info = Languages::GetLanguageInfo(self::$params['lang']);
				Session::Set('lang', self::$params['lang']);
				Session::Set('lang_dir', self::$params['lang_dir'] = $language_info['lang_dir']);
				Session::Set('lang_name', self::$params['lang_name'] = $language_info['lang_name']);
				Session::Set('lang_name_en', self::$params['lang_name_en'] = $language_info['lang_name_en']);
				Session::Set('lc_time_name', self::$params['lc_time_name'] = $language_info['lc_time_name']);
			} elseif (Session::Get('lang') != '' && Session::Get('lang_dir') != '' && Session::Get('lang_name') != '' && Session::Get('lang_name_en') != '') {
				self::$params['lang'] = Session::Get('lang');
				self::$params['lang_dir'] = Session::Get('lang_dir');
				self::$params['lang_name'] = Session::Get('lang_name');
				self::$params['lang_name_en'] = Session::Get('lang_name_en');
				self::$params['lc_time_name'] = Session::Get('lc_time_name');
			} else {
				self::$params['lang'] = Languages::GetDefaultLang();
				$language_info = Languages::GetLanguageInfo(self::$params['lang']);
				self::$params['lang_dir'] = isset($language_info['lang_dir']) ? $language_info['lang_dir'] : '';
				self::$params['lang_name'] = isset($language_info['lang_name']) ? $language_info['lang_name'] : '';
				self::$params['lang_name_en'] = isset($language_info['lang_name_en']) ? $language_info['lang_name_en'] : '';
				self::$params['lc_time_name'] = isset($language_info['lc_time_name']) ? $language_info['lc_time_name'] : '';
			}
		}

		//------------------------------------------------------------------------------
		// set currency
		if(!empty(self::$params['currency']) && Currencies::CurrencyExists(self::$params['currency'])){
			self::$params['currency_code'] = self::$params['currency'];
			$currency_info = Currencies::GetCurrencyInfo(self::$params['currency_code']);
			self::$params['currency_symbol'] = $currency_info['symbol'];
			self::$params['currency_rate'] = $currency_info['rate'];
			self::$params['currency_decimals'] = $currency_info['decimals'];
			self::$params['currency_symbol_place'] = $currency_info['symbol_placement'];
			Session::Set('currency_code', self::$params['currency']);
			Session::Set('currency_symbol', $currency_info['symbol']);
			Session::Set('currency_rate', $currency_info['rate']);
			Session::Set('currency_decimals', $currency_info['decimals']);
			Session::Set('symbol_placement', $currency_info['symbol_placement']);
		}else{
			if(Session::Get('currency_code') != '' && Session::Get('currency_symbol') != '' && Session::Get('currency_rate') != '' && Session::Get('currency_decimals') != '' && Session::Get('symbol_placement') != '' && Currencies::CurrencyExists(Session::Get('currency_code'))){
				self::$params['currency_code'] = Session::Get('currency_code');
				self::$params['currency_symbol'] = Session::Get('currency_symbol');
				self::$params['currency_rate'] = Session::Get('currency_rate');
				self::$params['currency_decimals'] = Session::Get('currency_decimals');
				self::$params['currency_symbol_place'] = Session::Get('symbol_placement');
			}else{
				$currency_info = Currencies::GetDefaultCurrencyInfo();
				self::$params['currency_code'] = $currency_info['code'];
				self::$params['currency_symbol'] = $currency_info['symbol'];
				self::$params['currency_rate'] = $currency_info['rate'];
				self::$params['currency_decimals'] = $currency_info['decimals'];
				self::$params['currency_symbol_place'] = $currency_info['symbol_placement'];
			}
		}

		// preview allowed only for admins
		// -----------------------------------------------------------------------------
		if($objLogin->IsLoggedInAsAdmin()){
			if($req_preview == 'yes' || $req_preview == 'no'){
				self::$params['preview'] = $req_preview;
				Session::Set('preview', self::$params['preview']);
			}elseif((self::$params['admin'] == '') && (Session::Get('preview') == 'yes' || Session::Get('preview') == 'no')){
				self::$params['preview'] = Session::Get('preview');
			}else{
				self::$params['preview'] = 'no';
				Session::Set('preview', self::$params['preview']);
			}
		}
				
		// *** get site description
		// -----------------------------------------------------------------------------
		$objSiteDescription->LoadData(self::$params['lang']);
	
		// *** draw offline message
		// -----------------------------------------------------------------------------
		if($objSettings->GetParameter('is_offline')){
			if(!$objLogin->IsLoggedIn() && self::$params['admin'] != ADMIN_LOGIN){
                
                $offline_content = @file_get_contents('html/site_offline.html');
                if(!empty($offline_content)){
                    $offline_content = str_ireplace(
                        array('{HEADER_TEXT}', '{SLOGAN_TEXT}', '{OFFLINE_MESSAGE}', '{FOOTER}'),
                        array(
                            $objSiteDescription->GetParameter('header_text'),
                            $objSiteDescription->GetParameter('slogan_text'),
                            $objSettings->GetParameter('offline_message'),
                            $objSiteDescription->DrawFooter(false)
                        ),
                        $offline_content
                    );                    
                }else{
                    $offline_content = $objSettings->GetParameter('offline_message');
                }
                echo $offline_content;
				exit;
			}
		}
		
		// *** draw offline message
		// -----------------------------------------------------------------------------
		if($objSettings->GetParameter('is_offline')){
			if(!$objLogin->IsLoggedIn() && self::$params['admin'] != ADMIN_LOGIN){
				echo '<html>';
				echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
				echo '<body>'.$objSettings->GetParameter('offline_message').'</body>';
				echo '</html>';
				exit;
			}
		}
		
		// *** default user page
		// -----------------------------------------------------------------------------
		if($objLogin->IsLoggedInAsCustomer()) if(self::$params['customer'] == '' && self::$params['page'] == '') self::$params['customer'] = 'home';
		
		// *** use direction of selected language
		// -----------------------------------------------------------------------------
		self::$params['defined_left']  = (self::$params['lang_dir'] == 'ltr') ? 'left' : 'right';
		self::$params['defined_right'] = (self::$params['lang_dir'] == 'ltr') ? 'right' : 'left';
		self::$params['defined_alignment'] = (self::$params['lang_dir'] == 'ltr') ? 'left' : 'right';
		
		// *** prepare META tags
		// -----------------------------------------------------------------------------
		if(self::$params['page'] == 'news' && self::$params['news_id'] != ''){
			$news_info = News::GetNewsInfo(self::$params['news_id'], self::$params['lang']);
			self::$params['tag_title'] = isset($news_info['header_text']) ? $news_info['header_text'] : $objSiteDescription->GetParameter('tag_title');
			self::$params['tag_keywords'] = isset($news_info['header_text']) ? str_replace(' ', ',', $news_info['header_text']) : $objSiteDescription->GetParameter('tag_keywords');
			self::$params['tag_description'] = isset($news_info['header_text']) ? $news_info['header_text'] : $objSiteDescription->GetParameter('tag_description');
        }elseif(in_array(self::$PROJECT, array('HotelSite', 'HotelBooking')) && self::$params['page'] == 'hotels'){
            $hotel_info = Hotels::GetHotelFullInfo(self::$params['hotel_id'], self::$params['lang']);
            self::$params['tag_title'] = isset($hotel_info['name']) ? strip_tags($hotel_info['name']) : '';
            self::$params['tag_keywords'] = isset($hotel_info['name']) ? strip_tags($hotel_info['name']) : '';
            self::$params['tag_description'] = isset($hotel_info['description']) ? strip_tags($hotel_info['description']) : ''; 
        }elseif(in_array(self::$PROJECT, array('HotelSite', 'HotelBooking')) && self::$params['page'] == 'rooms'){
            $room_info = Rooms::GetRoomInfo(self::$params['room_id']);
            self::$params['tag_title'] = isset($room_info['room_type']) ? strip_tags($room_info['room_type'].' | '.$room_info['hotel_name']) : '';
            self::$params['tag_keywords'] = isset($room_info['room_type']) ? strip_tags($room_info['room_type']) : '';
            self::$params['tag_description'] = isset($room_info['room_short_description']) ? strip_tags($room_info['room_short_description']) : ''; 
		}elseif(in_array(self::$PROJECT, array('HotelSite', 'HotelBooking')) && self::$params['customer'] != ''){
			self::$params['tag_title'] = file_exists('customer/'.self::$params['customer'].'.php') && !(self::$params['customer'] == 'login' && (DEFAULT_CUSTOMER_LINK != 'login' || TRAVEL_AGENCY_LOGIN != 'login')) ? ucwords(str_replace('_', ' ', self::$params['customer'])) : 'Error 404';
			self::$params['tag_title'] = in_array(self::$params['customer'], array(DEFAULT_CUSTOMER_LINK, TRAVEL_AGENCY_LOGIN)) ? 'Login' : self::$params['tag_title'];
			self::$params['tag_keywords'] = $objSiteDescription->GetParameter('tag_keywords');
			self::$params['tag_description'] = $objSiteDescription->GetParameter('tag_description');            
		}else{
			if(self::$params['system_page'] != ''){
				$objPage = new Pages(self::$params['system_page'], true);			
			}else{
				$objPage = new Pages(self::$params['page_id'], true);
			}
			self::$params['tag_title'] = ($objPage->GetParameter('tag_title') != '') ? $objPage->GetParameter('tag_title') : $objSiteDescription->GetParameter('tag_title');
			self::$params['tag_keywords'] = ($objPage->GetParameter('tag_keywords') != '') ? $objPage->GetParameter('tag_keywords') : $objSiteDescription->GetParameter('tag_keywords');
			self::$params['tag_description'] = ($objPage->GetParameter('tag_description') != '') ? $objPage->GetParameter('tag_description') : $objSiteDescription->GetParameter('tag_description');
		}
		
		// *** get site template
		// -----------------------------------------------------------------------------
		self::$params['template'] = ($objSettings->GetTemplate() != '') ? $objSettings->GetTemplate() : DEFAULT_TEMPLATE;
		if($objLogin->IsLoggedInAsAdmin() && (self::$params['preview'] != 'yes' || self::$params['admin'] != '')) self::$params['template'] = 'admin';
		elseif(!$objLogin->IsLoggedIn() && (self::$params['admin'] == ADMIN_LOGIN || self::$params['admin'] == 'password_forgotten' || self::$params['admin'] == 'hotel_owners_create_account' || self::$params['admin'] == 'hotel_owners_confirm_registration')) self::$params['template'] = 'admin';
		elseif(!is_null(self::$params['mobile'])) self::$params['template'] = 'mobile';
		
		// *** included js libraries
		// -----------------------------------------------------------------------------
		self::$params['js_included'] = array();

		// *** get special data for separate projects
		// -----------------------------------------------------------------------------		
		if(self::$PROJECT == 'HotelBooking'){
			// *** get property types
			self::$params['property_types'] = HotelsPropertyTypes::GetHotelsPropertyTypes();		
		}
	}
	
	/**
	 * Include style and javascript files
	 */
	public static function SetLibraries()
	{
		$nl = "\n";

		$output = '<script type="text/javascript" src="'.APPHP_BASE.'js/jquery-1.6.3.min.js"></script>'.$nl;
		$output .= GalleryAlbums::SetLibraries();		
		
		if(!self::Get('js_included', 'lytebox')){
			$output .= '<link rel="stylesheet" href="'.APPHP_BASE.'modules/lytebox/css/lytebox.css" type="text/css" media="screen" />'.$nl;
            if(self::Get('lang_dir') == 'rtl') $output .= '<link rel="stylesheet" href="'.APPHP_BASE.'modules/lytebox/css/lytebox.rtl.css" type="text/css" media="screen" />'.$nl;
			$output .= '<script type="text/javascript" src="'.APPHP_BASE.'modules/lytebox/js/lytebox.js"></script>'.$nl;
			Application::Set('js_included', 'lytebox');
		}

		return $output;
	}

	/**
	 * Returns parameter
	 * @param $param
	 * @param $val
	 */
	public static function Get($param = '', $val = '')
	{
		if(isset(self::$params[$param])){
			if(is_array(self::$params[$param])){
				if(!empty($val)){
					return isset(self::$params[$param][$val]) ? self::$params[$param][$val] : '';
				}else{
					return self::$params[$param];	
				}				
			}else{
				return isset(self::$params[$param]) ? self::$params[$param] : '';
			}
		}else{
			return '';
		}
	}

	/**
	 * Set parameter value
	 * @param $param
	 * @param $val
	 */
	public static function Set($param = '', $val = '')
	{
		if(isset(self::$params[$param])){
			if(is_array(self::$params[$param])){
				self::$params[$param][$val] = true;	
			}else{
				self::$params[$param] = $val;	
			}
		}
	}

	/**
	 * Draw Preview mode 
	 */
	public static function DrawPreview()
	{
		$preview = isset($_GET['preview']) ? prepare_input($_GET['preview']) : '';
		$preview_type = isset($_GET['preview_type']) ? prepare_input($_GET['preview_type']) : '';
		$page    = isset($_GET['page']) ? prepare_input($_GET['page']) : 'home';
		$page_id = isset($_GET['pid']) ? prepare_input($_GET['pid']) : 'home';
		$output = '';
		
		if($preview = 'yes' && $preview_type == 'single' && $page == 'pages' && $page_id != ''){
			$output .= '<div style="display:block; position:absolute; top:0%; left:0%; width:100%; height:1900px; background-color:black; z-index:2001; -moz-opacity:0.05; opacity:.05; filter:alpha(opacity=5);"></div>';
			$output .= '<div style="display:block; position: absolute; top: 75px; left: -225px; width: 600px; padding: 10px; font-size: 24px; text-align: center; color: rgb(255, 255, 255); font-family: \'trebuchet ms\',verdana,arial,sans-serif; -o-transform: rotate(-45deg); -moz-transform: rotate(-45deg); -webkit-transform: rotate(-45deg); transform: rotate(-45deg); background-color: rgb(0, 0, 0); border: 1px solid rgb(170, 170, 170); z-index: 2002; opacity: 0.5;">PREVIEW</div>';
		}
		echo $output;
	}

	/**
	 * Draw System Alerts
	 */
	public static function GetSystemAlerts()
	{
		global $objLogin, $objSettings;
		
		$actions_msg = array();
		if($objLogin->IsLoggedInAs('owner', 'mainadmin') && (file_exists('install.php') || file_exists('install/'))){
			$actions_msg[] = '<span class="darkred">'._INSTALL_PHP_EXISTS.'</span>';
		}
	
		if($objLogin->IsLoggedInAs('owner', 'mainadmin')){
			if(SITE_MODE == 'development') $actions_msg[] = '<span class="darkred">'._SITE_DEVELOPMENT_MODE_ALERT.'</span>';		
		}
	
		// test mode alert
		if(Modules::IsModuleInstalled('booking') && $objLogin->IsLoggedInAs('owner', 'mainadmin', 'admin', 'hotelowner')){
			if(ModulesSettings::Get('booking', 'mode') == 'TEST MODE'){
				$actions_msg[] = '<span class="darkred">'._TEST_MODE_ALERT.'</span>';
			}        
		}
	
		if($objLogin->IsLoggedInAs('owner', 'mainadmin')){
			$arr_folders = array('images/upload/', 'images/flags/', 'images/hotels/', 'images/rooms/', 'images/banners/', 'images/gallery/', 'tmp/backup/', 'tmp/export/', 'tmp/cache/', 'tmp/logs/', 'feeds/');
			$arr_folders_not_writable = '';
			foreach($arr_folders as $folder){
				if(!is_writable($folder)){
					if($arr_folders_not_writable != '') $arr_folders_not_writable .= ', ';
					$arr_folders_not_writable .= $folder;
				}
			}
			if($arr_folders_not_writable != ''){
				$actions_msg[] = _NO_WRITE_ACCESS_ALERT.' <b>'.$arr_folders_not_writable.'</b>';
			}
		
			$admin_email = $objSettings->GetParameter('admin_email');    
			if($objLogin->IsLoggedInAs('owner', 'mainadmin') && ($admin_email == '' || preg_match('/yourdomain/i', $admin_email))){
				$actions_msg[] = _DEFAULT_EMAIL_ALERT;
			}
		}
		
		$own_email = $objLogin->GetLoggedEmail();    
		if($own_email == '' || preg_match('/yourdomain/i', $own_email)){
			$actions_msg[] = _DEFAULT_OWN_EMAIL_ALERT;
		}
		
		if($objLogin->IsLoggedInAs('owner', 'mainadmin') && Modules::IsModuleInstalled('contact_us')){
			$admin_email_to = ModulesSettings::Get('contact_us', 'email');
			if($admin_email_to == '' || preg_match('/yourdomain/i', $admin_email_to)){
				$actions_msg[] = _CONTACTUS_DEFAULT_EMAIL_ALERT;
			}
		}    
		
		if($objLogin->IsLoggedInAs('owner', 'mainadmin', 'admin') && Modules::IsModuleInstalled('comments')){
			$comments_allow	= ModulesSettings::Get('comments', 'comments_allow');
			$comments_count = Comments::AwaitingModerationCount();
			if($comments_allow == 'yes' && $comments_count > 0){
				$actions_msg[] = str_replace('_COUNT_', $comments_count, _COMMENTS_AWAITING_MODERATION_ALERT);			
			}
		}
		
		if($objLogin->IsLoggedInAs('owner', 'mainadmin') && ModulesSettings::Get('customers', 'reg_confirmation') == 'by admin'){
			$customers_count = Customers::AwaitingAprovalCount();
			if($customers_count > 0){
				$actions_msg[] = str_replace('_COUNT_', $customers_count, _CUSTOMERS_AWAITING_MODERATION_ALERT);			
			}
		}
		
		return $actions_msg;
	}
}
