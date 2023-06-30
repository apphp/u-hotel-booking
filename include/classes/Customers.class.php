<?php

/**
 *	Class Customers (for ApPHP HotelSite ONLY)
 *  -------------- 
 *  Description : encapsulates Customers operations & properties
 *  Updated	    : 10.02.2016
 *	Written by  : ApPHP
 *	
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				SendPassword						
 *  __destruct              GetStaticError
 *  AfterAddRecord          DrawLoginFormBlock
 *  BeforeEditRecord        ResetAccount 
 *  AfterEditRecord         Reactivate  
 *  BeforeUpdateRecord      AwaitingAprovalCount
 *  AfterUpdateRecord       GetCustomerInfo
 *  BeforeInsertRecord		
 *  AfterInsertRecord       
 *  GetAllCustomers
 *  ChangePassword
 *  AssignedToHotelOwner
 *  
 **/

class Customers extends MicroGrid {
	
	protected $debug = false;
	
    //------------------------------
	private $email_notifications;
	private $user_password;
	private $allow_changing_password;
	private $reg_confirmation;
	private $sqlFieldDatetimeFormat = '';
	private $sqlFieldDateFormat = '';
	private $customer_type = '';
	private $regionalManager = false;
	private $hotelOwner = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($customer_type = '', $account_type = '')
	{
		parent::__construct();

		global $objSettings;
		global $objLogin;
		$this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager');
		$this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner');
		
		$this->params = array();
		if(isset($_POST['group_id']))   	 	$this->params['group_id']    	= (int)prepare_input($_POST['group_id']);
		if(isset($_POST['customer_type']))   	$this->params['customer_type'] = (int)prepare_input($_POST['customer_type']);
		//if(isset($_POST['balance']))   	 	$this->params['balance']     	= prepare_input($_POST['balance']);
		if(isset($_POST['company']))   		 	$this->params['company']     	= prepare_input($_POST['company']);
		if(isset($_POST['url'])) 			 	$this->params['url'] 		 	= prepare_input($_POST['url'], false, 'medium');
		if(isset($_POST['first_name'])) 	 	$this->params['first_name']  	= prepare_input($_POST['first_name']);
		if(isset($_POST['last_name']))		 	$this->params['last_name']   	= prepare_input($_POST['last_name']);
		if(isset($_POST['birth_date']) && ($_POST['birth_date'] != ''))  $this->params['birth_date'] = prepare_input($_POST['birth_date']); else $this->params['birth_date'] = null;
		if(isset($_POST['b_address']))  	 	$this->params['b_address']   	= prepare_input($_POST['b_address']);
		if(isset($_POST['b_address_2']))	 	$this->params['b_address_2'] 	= prepare_input($_POST['b_address_2']);
		if(isset($_POST['b_city']))   		 	$this->params['b_city']      	= prepare_input($_POST['b_city']);
		if(isset($_POST['b_zipcode']))		 	$this->params['b_zipcode']   	= prepare_input($_POST['b_zipcode']);
		if(isset($_POST['b_country']))		 	$this->params['b_country']   	= prepare_input($_POST['b_country']);
		if(isset($_POST['b_state']))   		 	$this->params['b_state']     	= prepare_input($_POST['b_state']);
		if(isset($_POST['phone'])) 			 	$this->params['phone'] 		= prepare_input($_POST['phone']);
		if(isset($_POST['fax'])) 			 	$this->params['fax'] 		 	= prepare_input($_POST['fax']);
		if(isset($_POST['email'])) 			 	$this->params['email'] 		= prepare_input($_POST['email']);
		if(isset($_POST['user_name']))  	 	$this->params['user_name']   	= prepare_input($_POST['user_name']);
		if(isset($_POST['user_password']))  	$this->params['user_password']  = prepare_input($_POST['user_password']);
		if(isset($_POST['preferred_language'])) $this->params['preferred_language'] = prepare_input($_POST['preferred_language']);
		if(isset($_POST['date_created']))  		$this->params['date_created']   = prepare_input($_POST['date_created']);
		if(isset($_POST['date_lastlogin']))  	$this->params['date_lastlogin'] = prepare_input($_POST['date_lastlogin']);
		if(isset($_POST['registered_from_ip'])) $this->params['registered_from_ip'] = prepare_input($_POST['registered_from_ip']);
		if(isset($_POST['last_logged_ip'])) 	$this->params['last_logged_ip'] 	= prepare_input($_POST['last_logged_ip']);
		if(isset($_POST['email_notifications'])) 		 $this->params['email_notifications'] 		  = (int)$_POST['email_notifications']; else $this->params['email_notifications'] = '0';
		if(isset($_POST['notification_status_changed'])) $this->params['notification_status_changed'] = prepare_input($_POST['notification_status_changed']);
		if(isset($_POST['orders_count'])) 		$this->params['orders_count'] 		= (int)$_POST['orders_count'];
		if(isset($_POST['rooms_count'])) 	    $this->params['rooms_count'] 		= (int)$_POST['rooms_count'];
		if(isset($_POST['reviews_count'])) 	    $this->params['reviews_count'] 		= (int)$_POST['reviews_count'];
		if(isset($_POST['award_points'])) 	    $this->params['award_points'] 		= (int)$_POST['award_points'];
		if(isset($_POST['comments'])) 			$this->params['comments'] 		 	= prepare_input($_POST['comments']);
		if(isset($_POST['registration_code'])) 	$this->params['registration_code'] 	= prepare_input($_POST['registration_code']);
		
		## for checkboxes 
		if($account_type == ''){
			if(isset($_POST['is_active'])) $this->params['is_active'] = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		}
		if($account_type == ''){
			if(isset($_POST['is_removed'])) $this->params['is_removed'] = (int)$_POST['is_removed']; else $this->params['is_removed'] = '0';
		}

		$this->customer_type = ($customer_type == 'agencies' || $customer_type == '1') ? 1 : 0;
		$this->email_notifications = '';
		$this->user_password = '';
		$this->allow_changing_password = ModulesSettings::Get('customers', 'password_changing_by_admin');
		$this->reg_confirmation = ModulesSettings::Get('customers', 'reg_confirmation');
		$this->allow_payment_with_balance = ModulesSettings::Get('booking', 'allow_payment_with_balance') == 'yes' ? true : false;
		$this->allow_award_points = (bool)Modules::IsModuleInstalled('loyalty');
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CUSTOMERS;
		$this->dataSet 		= array();
		$this->error 		= '';
		///$this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

		if($account_type == 'me'){
			$this->formActionURL = 'index.php?customer=my_account';
			$allow_adding = false;
			$allow_deleting = true;		
			$allow_editing = true;
			$is_active_visible = false;
			$is_removed_visible = false;
		}else{
			$this->formActionURL = 'index.php?admin='.($this->customer_type == 1 ? 'mod_customers_agencies' : 'mod_customers_management');
			$is_active_visible = true;
			$is_removed_visible = false;

			$allow_adding_by_admin = ModulesSettings::Get('customers', 'allow_adding_by_admin');
			$allow_adding = ($allow_adding_by_admin == 'yes') ? true : false;
			$allow_deleting = true;		
			$allow_editing = true;
			if($this->regionalManager){
				$allow_adding = $allow_editing = $allow_deleting = false;
			}elseif($this->hotelOwner){
				$allow_deleting = false;
			}
		}

		$this->actions      = array('add'=>$allow_adding, 'edit'=>$allow_editing, 'details'=>true, 'delete'=>$allow_deleting);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowPrint   = true;
		$this->allowTopButtons = true;

		$this->allowLanguages = false;
		if($this->regionalManager){
			$account_id = $objLogin->GetLoggedID();
			$travel_agencies = AccountLocations::GetAllTravelAgencies($account_id);
			$this->WHERE_CLAUSE = 'WHERE customer_type = 1 AND c.id IN (-1,'.implode(',',$travel_agencies).')';
		}elseif($this->hotelOwner){
			$assigned_to_hotels = '';
			$this->hotelsList = implode(',', $objLogin->AssignedToHotels());
			if(!empty($this->hotelsList)) $assigned_to_hotels = TABLE_BOOKINGS_ROOMS.'.hotel_id IN ('.$this->hotelsList.')';
			$this->WHERE_CLAUSE = 'WHERE customer_type = '.$this->customer_type.' AND ('.(!empty($assigned_to_hotels) ? $assigned_to_hotels.' OR ' : '').' c.created_by_admin_id = '.(int)$objLogin->GetLoggedID().')';
			$this->GROUP_BY_CLAUSE = 'GROUP BY c.'.$this->primaryKey;
		}else{
			$this->WHERE_CLAUSE = 'WHERE customer_type = '.$this->customer_type;
		}
		$this->ORDER_CLAUSE = 'ORDER BY id DESC';

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$currency_format = get_currency_format();		
		$default_currency = Currencies::GetDefaultCurrency();

		// prepare countries array		
		$arr_countries = array();
		if($this->regionalManager){
			$arr_countries = AccountLocations::GetCountries($objLogin->GetLoggedID());
		}else{
			$total_countries = Countries::GetAllCountries();
			foreach($total_countries[0] as $key => $val){
				$arr_countries[$val['abbrv']] = $val['name'];
			}
		}

		$total_groups = CustomerGroups::GetAllGroups();
		$arr_groups = array();
		foreach($total_groups[0] as $key => $val){
			$arr_groups[$val['id']] = $val['name'];
		}
		
		$arr_total_funds = array();
		if($this->customer_type == 1){
			$total_funds = CustomerFunds::GetTotalFunds();
			foreach($total_funds[0] as $key => $val){
				$arr_total_funds[$val['customer_id']] = $val['total_funds'];	
				//if($currency_format == 'american'){
					//$arr_total_funds[$val['customer_id']] = $default_currency.number_format($val['total_funds'], 2, '.', ',');	
				//}else{
					//$arr_total_funds[$val['customer_id']] = $default_currency.number_format($val['total_funds'], 2, ',', '.');	
				//}				
			}
		}
		
		// Prapare total payments
		$arr_total_payments_agencies = array();
		$arr_total_payments_customers = array();
		if($this->customer_type == 0){			
			$customer_ids = array();
			$customers = self::GetAllCustomers('AND customer_type = 0');
			foreach($customers[0] as $key => $val){
				$customer_ids[] = $val['id'];
			}
			$total_payments = Bookings::GetTotalPayments($customer_ids, 'status IN (1,2,3)');
			foreach($total_payments[0] as $key => $val){
				if($currency_format == 'american'){
					$arr_total_payments_customers[$val['customer_id']] = $default_currency.number_format($val['total_payments'], 2, '.', ',');	
				}else{
					$arr_total_payments_customers[$val['customer_id']] = $default_currency.number_format($val['total_payments'], 2, ',', '.');	
				}				
			}
		}
		
		if($this->customer_type == 1){			
			$agency_ids = array();
			$agencies = self::GetAllCustomers('AND customer_type = 1');
			foreach($agencies[0] as $key => $val){
				$agency_ids[] = $val['id'];
			}
			$total_payments = Bookings::GetTotalPayments($agency_ids, 'status IN (1,2,3)');
			foreach($total_payments[0] as $key => $val){
				$arr_total_payments_agencies[$val['customer_id']] = $val['total_payments'];	
				//if($currency_format == 'american'){
					//$arr_total_payments_agencies[$val['customer_id']] = $default_currency.number_format($val['total_payments'], 2, '.', ',');	
				//}else{
					//$arr_total_payments_agencies[$val['customer_id']] = $default_currency.number_format($val['total_payments'], 2, ',', '.');	
				//}				
			}
		}

		$this->isFilteringAllowed = true;
		// define filtering fields for Agency
		if($this->customer_type == 1){
			$this->arrFilteringFields = array(
				_AGENCY    	=> array('table'=>'c', 'field'=>'company', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),			
				_USERNAME   => array('table'=>'c', 'field'=>'user_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),			
				_LAST_NAME  => array('table'=>'c', 'field'=>'last_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
				_EMAIL      => array('table'=>'c', 'field'=>'email', 'type'=>'text', 'sign'=>'like%', 'width'=>'100px'),
				_COUNTRY    => array('table'=>'c', 'field'=>'b_country', 'type'=>'dropdownlist', 'source'=>$arr_countries, 'sign'=>'=', 'width'=>'100px'),
			);
			
			$rid = MicroGrid::GetParameter('rid');
			$coupon_code = Coupons::GetCouponByCustomerId($rid, 'coupon_code');
			$this->isAggregateAllowed = true;
			$this->arrAggregateFields = array(
				'balance' => array('function'=>'SUM', 'align'=>'right', 'aggregate_by'=>'', 'decimal_place'=>2),
				'link_total_funds' => array('function'=>'SUM', 'align'=>'right', 'aggregate_by'=>'', 'decimal_place'=>2),
				'link_total_payments' => array('function'=>'SUM', 'align'=>'right', 'aggregate_by'=>'', 'decimal_place'=>2)
			);
		// define filtering fields for simple Customer
		}else{			
			$this->arrFilteringFields = array(
				_USERNAME   => array('table'=>'c', 'field'=>'user_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),			
				_LAST_NAME  => array('table'=>'c', 'field'=>'last_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
				_EMAIL      => array('table'=>'c', 'field'=>'email', 'type'=>'text', 'sign'=>'like%', 'width'=>'100px'),
				_GROUP      => array('table'=>'c', 'field'=>'group_id', 'type'=>'dropdownlist', 'source'=>$arr_groups, 'sign'=>'=', 'width'=>'85px'),
			);
		}

		$user_ip = get_current_ip();		
		$datetime_format = get_datetime_format();		
		$date_format_view = get_date_format('view');
		$date_format_edit = get_date_format('edit');
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_is_removed = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_email_notification = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		// prepare languages array		
		$total_languages = Languages::GetAllActive();
		$arr_languages = array();
		foreach($total_languages[0] as $key => $val){
			$arr_languages[$val['abbreviation']] = $val['lang_name'];
		}

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
			$this->sqlFieldDateFormat = '%b %d, %Y';
		}else{
			$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
			$this->sqlFieldDateFormat = '%d %b, %Y';
		}
		$this->SetLocale(Application::Get('lc_time_name'));

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									c.'.$this->primaryKey.',
		                            c.*,
									CONCAT(c.first_name, " ", c.last_name) as full_name,
									IF(c.user_name != "", c.user_name, "<span class=gray>- without account -</span>") as mod_user_name,
									c.is_active,
									cg.name as group_name,
									IF(
										orders_count > 0,
										CONCAT("<a href=\"javascript:void();\" title=\"'.htmlentities(_CLICK_TO_VIEW).'\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_booking_bookings&mg_action=view&mg_operation=filtering&mg_search_status=active&filter_by_'.DB_PREFIX.'customersid=", c.id, "\')\">", orders_count, "</a>"),
										"0"
									) as link_orders_count,
									IF(
										reviews_count > 0,
										CONCAT("<a href=\"javascript:void();\" title=\"'.htmlentities(_CLICK_TO_VIEW).'\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_reviews_management&mg_action=view&mg_operation=filtering&mg_search_status=active&filter_by_cremail=", c.email, "\')\">", reviews_count, "</a>"),
										"0"
									) as link_reviews_count,
									IF(
										c.is_active = 0,
										CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'._CLICK_TO_CHANGE_STATUS.'\" onclick=\"javascript:__mgDoPostBack(\''.TABLE_CUSTOMERS.'\', \'set_status\', \'", c.'.$this->primaryKey.', "\');\"><span class=\"no\">[ '._NO.' ]</span></a></nobr>"),
										CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'._CLICK_TO_CHANGE_STATUS.'\" onclick=\"javascript:__mgDoPostBack(\''.TABLE_CUSTOMERS.'\', \'reset_status\', \'", c.'.$this->primaryKey.', "\');\"><span class=\"yes\">[ '._YES.' ]</span></a></nobr>")
									) as view_active,
									CONCAT("<a href=\"index.php?admin=mod_customers_agency_funds&aid=", c.id, "\">[ '.htmlspecialchars(_FUNDS).' ]</a>") as link_manage_balance,
									c.id as link_total_funds,
									c.id as link_total_payments,
									CONCAT("<a href=\"index.php?admin=mod_customers_award_points&cid=", c.id, "\">", c.award_points, "</a>") as link_manage_award_points
									'.($this->customer_type == 1 ? ', CONCAT('.TABLE_COUPONS.'.coupon_code, "<br><small>(discount ", '.TABLE_COUPONS.'.discount_value, IF('.TABLE_COUPONS.'.discount_type = 1, "%", "'.htmlspecialchars($default_currency).'"), ")</small>") as coupon_code' : '').'
								FROM '.$this->tableName.' c
									'.($this->customer_type == 1 ? 'LEFT OUTER JOIN '.TABLE_COUPONS.' ON c.id = '.TABLE_COUPONS.'.customer_id' : '').'
									'.($this->hotelOwner ? 'LEFT OUTER JOIN '.TABLE_BOOKINGS.' ON c.id = '.TABLE_BOOKINGS.'.customer_id' : '').'
									'.($this->hotelOwner ? 'LEFT OUTER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number' : '').'
									LEFT OUTER JOIN '.TABLE_CUSTOMER_GROUPS.' cg ON c.group_id = cg.id ';
		// Define view mode fields
		if($this->customer_type == 1){
			$this->arrViewModeFields = array(
				'id'           			=> array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'50px'),
				'company'    			=> array('title'=>_AGENCY, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'25'),
				'full_name'    			=> array('title'=>_CUSTOMER_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'25'),
				//'mod_user_name'		=> array('title'=>_USERNAME, 'type'=>'label', 'align'=>'left', 'width'=>''),
				'email' 	   			=> array('title'=>_EMAIL_ADDRESS, 'type'=>'link', 'href'=>'mailto:{email}', 'align'=>'left', 'maxlength'=>'28', 'width'=>''),
				//'b_country'  			=> array('title'=>_COUNTRY, 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_countries),
				'coupon_code' 			=> array('title'=>_COUPON_CODE, 'type'=>'label', 'align'=>'right', 'width'=>'160px'),
				'balance' 				=> array('title'=>_BALANCE, 'type'=>'label', 'align'=>'right', 'width'=>'95px', 'pre_html'=>$default_currency, 'format'=>'currency', 'format_parameter'=>$currency_format.'|2', 'visible'=>$this->allow_payment_with_balance),
				'link_manage_balance' 	=> array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'75px', 'maxlength'=>'', 'visible'=>($this->allow_payment_with_balance && !$this->regionalManager ? true : false)),
				'link_total_funds'  	=> array('title'=>_TOTAL_FUNDS, 'type'=>'enum', 'align'=>'center', 'width'=>'75px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_total_funds, 'pre_html'=>$default_currency, 'format'=>'currency', 'format_parameter'=>$currency_format.'|2'),
				'link_total_payments' 	=> array('title'=>_TOTAL_PAYMENTS, 'type'=>'enum', 'align'=>'center', 'width'=>'75px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_total_payments_agencies, 'pre_html'=>$default_currency, 'format'=>'currency', 'format_parameter'=>$currency_format.'|2'),
				'link_orders_count' 	=> array('title'=>_BOOKINGS, 'type'=>'label', 'align'=>'center', 'width'=>'80px'),
				'is_active'    			=> array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>(!$objLogin->IsLoggedInAs('owner', 'mainadmin', 'regionalmanager') ? true : false), 'source'=>$arr_is_active),
				'view_active'			=> array('title'=>_ACTIVE, 'type'=>'label', 'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'visible'=>($objLogin->IsLoggedInAs('owner', 'mainadmin', 'regionalmanager') ? true : false)),
			);
		}else{
			$this->arrViewModeFields = array(
				'profile_photo_thumb'   => array('title'=>_PHOTO, 'type'=>'image', 'align'=>'left', 'width'=>'50px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'image_width'=>'32px', 'image_height'=>'28px', 'target'=>'images/customers/', 'no_image'=>'no_image.png'),
				'full_name'    			=> array('title'=>_CUSTOMER_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'25'),
				'mod_user_name'			=> array('title'=>_USERNAME, 'type'=>'label', 'align'=>'left', 'width'=>''),
				'email' 	   			=> array('title'=>_EMAIL_ADDRESS, 'type'=>'link', 'href'=>'mailto:{email}', 'align'=>'left', 'maxlength'=>'28', 'width'=>''),
				'b_country'  			=> array('title'=>_COUNTRY, 'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_countries),
				'link_total_payments' 	=> array('title'=>_TOTAL_PAYMENTS, 'type'=>'enum', 'align'=>'center', 'width'=>'75px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_total_payments_customers),
				'link_orders_count' 	=> array('title'=>_BOOKINGS, 'type'=>'label', 'align'=>'center', 'width'=>'80px'),
				'link_reviews_count' 	=> array('title'=>_REVIEWS, 'type'=>'label', 'align'=>'center', 'width'=>'60px'),
				'link_manage_award_points' => array('title'=>_AWARD_POINTS, 'type'=>'label', 'align'=>'center', 'width'=>'65px', 'visible'=>($this->allow_award_points ? true : false)),
				//'group_name'   		=> array('title'=>_GROUP, 'type'=>'label', 'align'=>'center', 'width'=>'80px'),
				'is_active'    			=> array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'50px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
				'id'           			=> array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'50px'),
			);			
		}

		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array();

		if($this->customer_type == 1){
			$this->arrAddModeFields['separator_0'] = array(
				'separator_info' => array('legend'=>_AGENCY_DETAILS),
				'company' 		=> array('title'=>_NAME, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'255', 'required'=>true, 'validation_type'=>'text'),
				'url' 			=> array('title'=>_URL,	'type'=>'textbox', 'width'=>'270px', 'maxlength'=>'128', 'required'=>false, 'validation_type'=>'text'),
				'logo'   		=> array('title'=>_LOGO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/travel_agencies/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'agency_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
				'balance'  		=> array('title'=>'', 'type'=>'hidden',  'width'=>'210px', 'required'=>false, 'default'=>0),
			);			
		}
		    
		$this->arrAddModeFields['separator_1'] = array(
			'separator_info' => array('legend'=>_PERSONAL_DETAILS),
			'first_name'  	 => array('title'=>_FIRST_NAME,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
			'last_name'    	 => array('title'=>_LAST_NAME, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
			'birth_date'     => array('title'=>_BIRTH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'default'=>null, 'validation_type'=>'date', 'unique'=>false, 'visible'=>true, 'min_year'=>'90', 'max_year'=>'0', 'format'=>'date', 'format_parameter'=>$date_format_edit),
			'profile_photo'  => array('title'=>_PHOTO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/customers/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'profile_photo_thumb', 'thumbnail_width'=>'90px', 'thumbnail_height'=>'90px', 'file_maxsize'=>'900k', 'watermark'=>false, 'watermark_text'=>''),
		);
		$this->arrAddModeFields['separator_2'] = array(
			'separator_info' => array('legend'=>_CONTACT_INFORMATION),
			'phone' 		 => array('title'=>_PHONE,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>false, 'validation_type'=>'text'),
			'fax' 		     => array('title'=>_FAX,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>false, 'validation_type'=>'text'),
			'email' 		 => array('title'=>_EMAIL_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'100', 'required'=>true, 'validation_type'=>'email', 'unique'=>true, 'autocomplete'=>'off'),
		);
		$this->arrAddModeFields['separator_3'] = array(
			'separator_info' => array('legend'=>_BILLING_ADDRESS),
			'b_address' 	=> array('title'=>_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'64', 'required'=>true, 'validation_type'=>'text'),
			'b_address_2' 	=> array('title'=>_ADDRESS_2,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'64', 'required'=>false, 'validation_type'=>'text'),
			'b_city' 		=> array('title'=>_CITY,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'64', 'required'=>true, 'validation_type'=>'text'),
			'b_zipcode' 	=> array('title'=>_ZIP_CODE, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>false, 'validation_type'=>'text'),
			'b_country' 	=> array('title'=>_COUNTRY,	 'type'=>'enum',     'width'=>'210px', 'source'=>$arr_countries, 'required'=>true, 'javascript_event'=>'onchange="appChangeCountry(this.value,\'b_state\',\'\',\''.Application::Get('token').'\')"'),
			'b_state' 		=> array('title'=>_STATE,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'64', 'required'=>false, 'validation_type'=>'text'),
		);
		$this->arrAddModeFields['separator_4'] = array(
			'separator_info' 		=> array('legend'=>_ACCOUNT_DETAILS),
			'user_name' 	 		=> array('title'=>_USERNAME,  'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text', 'validation_minlength'=>'4', 'readonly'=>false, 'unique'=>true, 'username_generator'=>true),
			'user_password'  		=> array('title'=>_PASSWORD, 'type'=>'password', 'width'=>'210px', 'maxlength'=>'30', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'password_generator'=>true),
			'group_id'       		=> array('title'=>_CUSTOMER_GROUP, 'type'=>'enum', 'required'=>false, 'readonly'=>false, 'width'=>'', 'source'=>$arr_groups, 'visible'=>($this->customer_type == 1 ? false: true)),
			'preferred_language' 	=> array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'120px', 'default'=>Application::Get('lang'), 'source'=>$arr_languages),
		);
		$this->arrAddModeFields['separator_5'] = array(
			'separator_info' 		=> array('legend'=>_OTHER),
			'orders_count'        	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>'0'),
			'rooms_count'         	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>'0'),
			'reviews_count'       	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>'0'),
			'award_points'        	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>'0'),
			'date_created'        	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>date('Y-m-d H:i:s')),
			'registered_from_ip'  	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>$user_ip),
			'last_logged_ip'      	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>''),
			'is_removed'          	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>'0'),
			'registration_code'   	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>''),
			'email_notifications' 	=> array('title'=>_EMAIL_NOTIFICATIONS,	'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0'),
			'is_active'           	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			'comments'            	=> array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'420px', 'height'=>'70px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'validation_maxlength'=>'1024'),
			'customer_type'       	=> array('title'=>'', 'type'=>'hidden', 'width'=>'', 'required'=>false, 'default'=>$this->customer_type),
			'created_by_admin_id' 	=> array('visible'=>$this->hotelOwner ? true : false, 'title'=>'', 'type'=>'hidden', 'width'=>'', 'required'=>false, 'default'=>$objLogin->GetLoggedID()),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// * password field must be written directly in SQL!!!
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
		                            '.$this->tableName.'.*,
									'.$this->tableName.'.user_password,
									'.$this->tableName.'.id as link_total_funds,
									'.$this->tableName.'.id as link_total_payments,
									IF('.$this->tableName.'.user_name != "", '.$this->tableName.'.user_name, "- without account -") as mod_user_name,
									DATE_FORMAT('.$this->tableName.'.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as date_created,
									DATE_FORMAT('.$this->tableName.'.date_lastlogin, \''.$this->sqlFieldDatetimeFormat.'\') as date_lastlogin,
									DATE_FORMAT('.$this->tableName.'.notification_status_changed, \''.$this->sqlFieldDatetimeFormat.'\') as notification_status_changed
									'.($this->customer_type == 1 ? ', CONCAT('.TABLE_COUPONS.'.coupon_code, " (discount ", '.TABLE_COUPONS.'.discount_value, IF('.TABLE_COUPONS.'.discount_type = 1, "%", "'.htmlspecialchars($default_currency).'"), ")") as coupon_code' : '').'
								FROM '.$this->tableName.'
									'.($this->customer_type == 1 ? 'LEFT OUTER JOIN '.TABLE_COUPONS.' ON '.$this->tableName.'.id = '.TABLE_COUPONS.'.customer_id' : '').'
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';

		// define edit mode fields
		if($this->customer_type == 1){
			$this->arrEditModeFields['separator_0'] = array(
				'separator_info' => array('legend'=>_AGENCY_DETAILS),
				'company' 		=> array('title'=>_NAME, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'255', 'required'=>true, 'validation_type'=>'text'),
				'url' 			=> array('title'=>_URL,		 'type'=>'textbox', 'width'=>'270px', 'maxlength'=>'128', 'required'=>false, 'validation_type'=>'text'),
				'logo'   		=> array('title'=>_LOGO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/travel_agencies/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'agency_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
				'coupon_code' 	=> array('title'=>_COUPON_CODE,	'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true, 'post_html'=>($account_type == '' ? ' &nbsp;<a href="javascript:void();" onclick="javascript:appGoToPage(\'index.php?admin=mod_booking_coupons\', \'&mg_action=view&mg_operation=filtering&mg_search_status=active&token='.Application::Get('token').'&filter_by_'.TABLE_COUPONS.'coupon_code='.htmlspecialchars($coupon_code).'\', \'post\')">[ '._CLICK_TO_MANAGE.' ]</a>' : '')),
				'balance'  		=> array('title'=>_BALANCE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$currency_format.'|2', 'pre_html'=>$default_currency, 'visible'=>$this->allow_payment_with_balance),
			);			
		}

		$this->arrEditModeFields['separator_1'] = array(
			'separator_info' 	=> array('legend'=>_PERSONAL_DETAILS),
			'first_name'  	 	=> array('title'=>_FIRST_NAME,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
			'last_name'    	 	=> array('title'=>_LAST_NAME, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
			'birth_date'     	=> array('title'=>_BIRTH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'default'=>null, 'validation_type'=>'date', 'unique'=>false, 'visible'=>true, 'min_year'=>'90', 'max_year'=>'0', 'format'=>'date', 'format_parameter'=>$date_format_edit),
			'profile_photo'  	=> array('title'=>_PHOTO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/customers/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'profile_photo_thumb', 'thumbnail_width'=>'90px', 'thumbnail_height'=>'90px', 'file_maxsize'=>'900k', 'watermark'=>false, 'watermark_text'=>''),
		);
		$this->arrEditModeFields['separator_2'] = array(
			'separator_info' 	=> array('legend'=>_CONTACT_INFORMATION),
			'phone' 		 	=> array('title'=>_PHONE,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>false, 'validation_type'=>'text'),
			'fax' 		     	=> array('title'=>_FAX,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>false, 'validation_type'=>'text'),
			'email' 		 	=> array('title'=>_EMAIL_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'100', 'required'=>true, 'readonly'=>false, 'validation_type'=>'email', 'unique'=>true, 'autocomplete'=>'off'),
		);
		$this->arrEditModeFields['separator_3'] = array(
			'separator_info' 		=> array('legend'=>_BILLING_ADDRESS),
			'b_address' 			=> array('title'=>_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'64', 'required'=>true, 'validation_type'=>'text'),
			'b_address_2' 			=> array('title'=>_ADDRESS_2,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'64', 'required'=>false, 'validation_type'=>'text'),
			'b_city' 				=> array('title'=>_CITY,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'64', 'required'=>true, 'validation_type'=>'text'),
			'b_zipcode' 			=> array('title'=>_ZIP_CODE, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>false, 'validation_type'=>'text'),
			'b_country' 			=> array('title'=>_COUNTRY,	 'type'=>'enum',     'width'=>'210px', 'source'=>$arr_countries, 'required'=>true, 'javascript_event'=>'onchange="appChangeCountry(this.value,\'b_state\',\'\',\''.Application::Get('token').'\')"'),
			'b_state' 				=> array('title'=>_STATE,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'64', 'required'=>false, 'validation_type'=>'text'),
		);
		$this->arrEditModeFields['separator_4'] = array(
			'separator_info' 		=> array('legend'=>_ACCOUNT_DETAILS),
			'mod_user_name'  		=> array('title'=>_USERNAME, 'type'=>'label'),
			'user_password'  		=> array('title'=>_PASSWORD, 'type'=>'password', 'width'=>'210px', 'maxlength'=>'20', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'visible'=>(($this->allow_changing_password == 'yes' && $account_type == '') ? true : false)),
			'group_id'       		=> array('title'=>_CUSTOMER_GROUP, 'type'=>'enum', 'required'=>false, 'readonly'=>false, 'width'=>'', 'source'=>$arr_groups, 'visible'=>($this->customer_type == 1 || $account_type == 'me' ? false: true)),
			'preferred_language' 	=> array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'120px', 'source'=>$arr_languages),
		);
		$this->arrEditModeFields['separator_5'] = array(
			'separator_info' 		=> array('legend'=>_RESERVATIONS_AND_PAYMENTS),
			'orders_count'			=> array('title'=>_BOOKINGS,  'type'=>'label', 'visible'=>($account_type == 'me' ? false : true)),
			'rooms_count'			=> array('title'=>_ROOMS, 'type'=>'label', 'visible'=>($account_type == 'me' ? false : true)),
			'reviews_count'			=> array('title'=>_REVIEWS, 'type'=>'label', 'visible'=>($account_type == 'me' ? false : true)),
			'link_total_funds'  	=> array('title'=>_TOTAL_FUNDS, 'type'=>'enum', 'required'=>false, 'readonly'=>false, 'width'=>'', 'source'=>$arr_total_funds, 'view_type'=>'label', 'visible'=>($this->customer_type == 1 ? true : false)),
			'link_total_payments' 	=> array('title'=>_TOTAL_PAYMENTS, 'type'=>'enum', 'required'=>false, 'readonly'=>false, 'width'=>'', 'source'=>($this->customer_type == 1 ? $arr_total_payments_agencies : $arr_total_payments_customers), 'view_type'=>'label'),
			'award_points'			=> array('title'=>_AWARD_POINTS, 'type'=>'label', 'visible'=>($this->allow_award_points ? true : false)),
		);
		$this->arrEditModeFields['separator_6'] = array(
			'separator_info' 		=> array('legend'=>_OTHER),
			'date_created'			=> array('title'=>_DATE_CREATED, 'type'=>'label'),
			'date_lastlogin'		=> array('title'=>_LAST_LOGIN, 'type'=>'label'),
			'registered_from_ip'	=> array('title'=>_REGISTERED_FROM_IP, 'type'=>'label'),
			'last_logged_ip'		=> array('title'=>_LAST_LOGGED_IP,	 'type'=>'label'),
			'email_notifications' 	=> array('title'=>_EMAIL_NOTIFICATIONS,	'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0'),
			'notification_status_changed' => array('title'=>_NOTIFICATION_STATUS_CHANGED, 'type'=>'label', 'format'=>'date'),
			'is_active'				=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>$is_active_visible),
			'is_removed'			=> array('title'=>_REMOVED,	'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>$is_removed_visible),
			'comments'				=> array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'420px', 'height'=>'70px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'validation_maxlength'=>'1024', 'visible'=>($account_type == 'me' ? false : true)),
			'registration_code'		=> array('title'=>_REGISTRATION_CODE, 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>''),
			'customer_type'			=> array('title'=>'', 'type'=>'hidden', 'width'=>'', 'required'=>false, 'default'=>$this->customer_type),
			'need_assistance_count'	=> array('title'=>_MSN_MAX_EMAIL_NEED_ASSISTANCE, 'type'=>'label'),
			'last_need_assistance'	=> array('title'=>_MSN_MAX_EMAIL_NEED_ASSISTANCE.' ('._LAST_UPDATE.')', 'type'=>'label', 'format'=>'date', 'format_parameter'=>$date_format_view),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = 'SELECT
									c.'.$this->primaryKey.',
		                            c.*,
									c.id as link_total_funds,
									c.id as link_total_payments,
									IF(c.user_name != "", c.user_name, "<span class=darkred>- without account -</span>") as mod_user_name,
									DATE_FORMAT(c.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as date_created,
									DATE_FORMAT(c.date_lastlogin, \''.$this->sqlFieldDatetimeFormat.'\') as date_lastlogin,
									DATE_FORMAT(c.birth_date, \''.$this->sqlFieldDateFormat.'\') as birth_date,
									DATE_FORMAT(c.notification_status_changed, \''.$this->sqlFieldDatetimeFormat.'\') as notification_status_changed,
									c.email_notifications,
									c.is_active,
									c.is_removed,
									cg.name as group_name,
									IF(st.name IS NOT NULL, st.name, c.b_state) as state_name
									'.($this->customer_type == 1 ? ', CONCAT('.TABLE_COUPONS.'.coupon_code, " (discount ", '.TABLE_COUPONS.'.discount_value, IF('.TABLE_COUPONS.'.discount_type = 1, "%", "'.htmlspecialchars($default_currency).'"), ")") as coupon_code' : '').'
								FROM '.$this->tableName.' c
									'.($this->customer_type == 1 ? 'LEFT OUTER JOIN '.TABLE_COUPONS.' ON c.id = '.TABLE_COUPONS.'.customer_id' : '').'
									LEFT OUTER JOIN '.TABLE_COUNTRIES.' cnt ON c.b_country = cnt.abbrv AND cnt.is_active = 1
									LEFT OUTER JOIN '.TABLE_CUSTOMER_GROUPS.' cg ON c.group_id = cg.id
									LEFT OUTER JOIN '.TABLE_STATES.' st ON c.b_state = st.abbrv AND st.country_id = cnt.id AND st.is_active = 1
								WHERE c.'.$this->primaryKey.' = _RID_';

		// define edit mode fields
		if($this->customer_type == 1){
			$this->arrDetailsModeFields['separator_0'] = array(
				'separator_info' => array('legend'=>_AGENCY_DETAILS),
				'company' 		=> array('title'=>_NAME, 'type'=>'label'),
				'url' 			=> array('title'=>_URL,	'type'=>'label'),
				'logo'   		=> array('title'=>_LOGO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/travel_agencies/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'agency_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
				'coupon_code' 	=> array('title'=>_COUPON_CODE,	'type'=>'label'),
				'balance'  		=> array('title'=>_BALANCE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$currency_format.'|2', 'pre_html'=>$default_currency, 'visible'=>$this->allow_payment_with_balance),
			);			
		}
		
		$this->arrDetailsModeFields['separator_1'] = array(
			'separator_info' 		=> array('legend'=>_PERSONAL_DETAILS),
			'first_name'  			=> array('title'=>_FIRST_NAME, 'type'=>'label'),
			'last_name'    			=> array('title'=>_LAST_NAME,  'type'=>'label'),
			'birth_date'    		=> array('title'=>_BIRTH_DATE,  'type'=>'label'),
			'profile_photo' 		=> array('title'=>_PHOTO, 'type'=>'image', 'target'=>'images/customers/', 'no_image'=>'no_image.png', 'image_width'=>'90px', 'image_height'=>'90px'),
		);
		$this->arrDetailsModeFields['separator_2'] = array(
			'separator_info' 		=> array('legend'=>_CONTACT_INFORMATION),
			'phone' 				=> array('title'=>_PHONE,	 'type'=>'label'),
			'fax' 		     		=> array('title'=>_FAX,	 'type'=>'label'),
			'email' 				=> array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
		);
		$this->arrDetailsModeFields['separator_3'] = array(
			'separator_info' 		=> array('legend'=>_BILLING_ADDRESS),
			'b_address' 			=> array('title'=>_ADDRESS,	 'type'=>'label'),
			'b_address_2' 			=> array('title'=>_ADDRESS_2,'type'=>'label'),
			'b_city' 				=> array('title'=>_CITY,	 'type'=>'label'),
			'b_zipcode' 			=> array('title'=>_ZIP_CODE, 'type'=>'label'),
			'country_name' 			=> array('title'=>_COUNTRY,	 'type'=>'label'),
			'state_name'    		=> array('title'=>_STATE,	 'type'=>'label'),
		);
		$this->arrDetailsModeFields['separator_4'] = array(
			'separator_info' 		=> array('legend'=>_ACCOUNT_DETAILS),
			'mod_user_name'  		=> array('title'=>_USERNAME,	 'type'=>'label'),
			'group_name'     		=> array('title'=>_CUSTOMER_GROUP, 'type'=>'label', 'visible'=>($this->customer_type == 1 ? false: true)),
			'preferred_language' 	=> array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'source'=>$arr_languages),
		);
		$this->arrDetailsModeFields['separator_5'] = array(
			'separator_info' 		=> array('legend'=>_RESERVATIONS_AND_PAYMENTS),
			'orders_count'			=> array('title'=>_BOOKINGS, 'type'=>'label'),
			'rooms_count'			=> array('title'=>_ROOMS, 'type'=>'label'),
			'reviews_count'			=> array('title'=>_REVIEWS, 'type'=>'label'),
			'link_total_funds'  	=> array('title'=>_TOTAL_FUNDS, 'type'=>'enum', 'source'=>$arr_total_funds, 'view_type'=>'label', 'visible'=>($this->customer_type == 1 ? true : false)),
			'link_total_payments' 	=> array('title'=>_TOTAL_PAYMENTS, 'type'=>'enum', 'source'=>($this->customer_type == 1 ? $arr_total_payments_agencies : $arr_total_payments_customers), 'view_type'=>'label'),
			'award_points'			=> array('title'=>_AWARD_POINTS, 'type'=>'label', 'visible'=>($this->allow_award_points ? true : false)),
		);
		$this->arrDetailsModeFields['separator_6'] = array(
			'separator_info' 		=> array('legend'=>_OTHER),
			'date_created'			=> array('title'=>_DATE_CREATED, 'type'=>'label'),
			'date_lastlogin'		=> array('title'=>_LAST_LOGIN,	 'type'=>'label'),
			'registered_from_ip' 	=> array('title'=>_REGISTERED_FROM_IP, 'type'=>'label'),
			'last_logged_ip'	 	=> array('title'=>_LAST_LOGGED_IP,	 'type'=>'label'),
			'email_notifications' 	=> array('title'=>_EMAIL_NOTIFICATIONS,	'type'=>'label', 'type'=>'enum', 'source'=>$arr_email_notification),
			'notification_status_changed' => array('title'=>_NOTIFICATION_STATUS_CHANGED, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
			'is_active'	        	=> array('title'=>_ACTIVE,	 'type'=>'label', 'type'=>'enum', 'source'=>$arr_is_active),
			'is_removed'	    	=> array('title'=>_REMOVED,  'type'=>'label', 'type'=>'enum', 'source'=>$arr_is_removed),
			'comments'				=> array('title'=>_COMMENTS,  'type'=>'label', 'format'=>'nl2br'),
			'need_assistance_count'	=> array('title'=>_MSN_MAX_EMAIL_NEED_ASSISTANCE, 'type'=>'label'),
			'last_need_assistance'	=> array('title'=>_MSN_MAX_EMAIL_NEED_ASSISTANCE.' ('._LAST_UPDATE.')', 'type'=>'label', 'format'=>'date', 'format_parameter'=>$date_format_view),
		);

	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	//==========================================================================
    // PUBLIC METHODS
	//==========================================================================
	/**
	 * Draws login form in Front-End
	 * @param $draw
	 */
	public static function DrawLoginFormBlock($draw = true, $menu_ind = '')
	{
		global $objLogin;		

		$username = '';
		$password = '';

		$output = draw_block_top(_AUTHENTICATION, $menu_ind, 'maximized', 'hidden-xs', false);
		$output .= '<form class="authentication-form" action="index.php?customer=login" method="post">
			<table border="0" cellspacing="2" cellpadding="1">
			<tr>
				<td>
					'.draw_hidden_field('submit_login', 'login', false).'
					'.draw_hidden_field('type', 'customer', false).'
					'.draw_token_field(false).'				
			    </td>
			</tr>
			<tr><td>'._USERNAME.':</td></tr>
			<tr><td><input type="text" name="user_name" id="user_name" maxlength="50" autocomplete="off" value="'.$username.'" /></td></tr>
			<tr><td>'._PASSWORD.':</td></tr>
			<tr><td><input type="password" name="password" id="password" maxlength="20" autocomplete="off" value="'.$password.'" /></td></tr>
			<tr><td style="height:5px"></td></tr>
			<tr><td>';
			
		$output .= '<input class="form_button" type="submit" name="submit" value="'._BUTTON_LOGIN.'" /> ';
		if(ModulesSettings::Get('customers', 'remember_me_allow') == 'yes'){
			$output .= '<input type="checkbox" class="form_checkbox" name="remember_me" id="chk_remember_me" value="1" /> <label for="chk_remember_me">'._REMEMBER_ME.'</label><br>';				
		}				
			
		$output .= '</td></tr>
			<tr><td></td></tr>';
			if(ModulesSettings::Get('customers', 'allow_registration') == 'yes') $output .= '<tr><td><a class="form_link" href="index.php?customer=create_account">'._CREATE_ACCOUNT.'</a></td></tr>';
			if(ModulesSettings::Get('customers', 'allow_reset_passwords') == 'yes') $output .= '<tr><td><a class="form_link" href="index.php?customer=password_forgotten">'._FORGOT_PASSWORD.'</a></td></tr>';
			$output .= '</table>		
		</form>';
		$output .= draw_block_bottom(false);
		
		if($draw) echo $output;
		else return $output;				
	}	

	/**
	 * Before-Update operation
	 */
	public function BeforeUpdateRecord()
	{
		$sql = 'SELECT email_notifications, user_password FROM '.$this->tableName.' WHERE '.$this->primaryKey.' = '.(int)$this->curRecordId;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
        if(isset($result['email_notifications'])) $this->email_notifications = $result['email_notifications'];
		if(isset($result['user_password'])) $this->user_password = $result['user_password'];
		return true;
	}

	/**
	 * After-Update operation
	 */
	public function AfterUpdateRecord()
	{
		global $objSettings;
		
		$registration_code = self::GetParameter('registration_code', false);
		$is_active         = self::GetParameter('is_active', false);
		$removed_update_clause = ((self::GetParameter('is_removed', false) == '1') ? ', is_active = 0' : '');
		$confirm_update_clause = '';
		
		$sql = 'SELECT user_name, user_password, preferred_language FROM '.$this->tableName.' WHERE '.$this->primaryKey.' = '.$this->curRecordId;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		$preferred_language = isset($result['preferred_language']) ? $result['preferred_language'] : '';
		$user_password = isset($result['user_password']) ? $result['user_password'] : '';

		if(!empty($registration_code) && $is_active && $this->reg_confirmation == 'by admin'){
			$confirm_update_clause = ', registration_code=\'\'';	
			////////////////////////////////////////////////////////////
			send_email(
				self::GetParameter('email', false),
				$objSettings->GetParameter('admin_email'),
				'registration_approved_by_admin',
				array(
					'{FIRST NAME}' => self::GetParameter('first_name', false),
					'{LAST NAME}'  => self::GetParameter('last_name', false),
					'{USER NAME}'  => self::GetParameter('user_name', false),
					'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
					'{BASE URL}'   => APPHP_BASE,
					'{YEAR}' 	   => date('Y')
				),
				$preferred_language
			);
			////////////////////////////////////////////////////////////
		}		

		$sql = 'UPDATE '.$this->tableName.'
				SET notification_status_changed = IF(email_notifications <> \''.$this->email_notifications.'\', \''.date('Y-m-d H:i:s').'\', notification_status_changed)
				    '.$removed_update_clause.'
					'.$confirm_update_clause.'
				WHERE '.$this->primaryKey.' = '.$this->curRecordId;		
		database_void_query($sql);

        // send email, if password was changed
		if($user_password != $this->user_password){
			////////////////////////////////////////////////////////////
			send_email(
				self::GetParameter('email', false),
				$objSettings->GetParameter('admin_email'),
				'password_changed_by_admin',
				array(
					'{FIRST NAME}'    => self::GetParameter('first_name', false),
					'{LAST NAME}'     => self::GetParameter('last_name', false),
					'{USER NAME}'     => $result['user_name'],
					'{USER PASSWORD}' => self::GetParameter('user_password', false),
					'{WEB SITE}'      => $_SERVER['SERVER_NAME']
				),
				$preferred_language
			);
			////////////////////////////////////////////////////////////			
		}

		return true;
	}

	/**
	 * Before-Addition operation
	 */
	public function BeforeInsertRecord(){
		global $objLogin;

		if($this->hotelOwner){
			$this->params['created_by_admin_id'] = $objLogin->GetLoggedID();
		}
		return true;
	}

	/**
	 * After-Addition operation
	 */
	public function AfterInsertRecord()
	{
		global $objSettings, $objSiteDescription;
		
		// This is agency - generate coupon number 
		if($this->params['customer_type'] == 1){
			$new_coupon_code = strtoupper(get_random_string(4).'-'.get_random_string(4).'-'.get_random_string(4).'-'.get_random_string(4));
			$sql = "INSERT INTO ".TABLE_COUPONS." (id, hotel_id, customer_id, coupon_code, max_amount, times_used, date_started, date_finished, discount_type, discount_value, comments, is_active)
					VALUES (NULL, 0, ".(int)$this->lastInsertId.", '".$new_coupon_code."', -1, 0, '".date('Y-m-d')."', '".date('Y-m-d', strtotime('+365 days'))."', '1', '0', 'Coupon for agency: ".$this->params['company']."', 1)";
			database_void_query($sql);
		}

		////////////////////////////////////////////////////////////
		if(!empty($this->params['email'])){			
			send_email(
				$this->params['email'],
				$objSettings->GetParameter('admin_email'),
				'new_account_created_by_admin',
				array(
					'{FIRST NAME}' => $this->params['first_name'],
					'{LAST NAME}'  => $this->params['last_name'],
					'{USER NAME}'  => $this->params['user_name'],
					'{USER PASSWORD}' => $this->params['user_password'],
					'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
					'{BASE URL}'   => APPHP_BASE,
					'{YEAR}' 	   => date('Y')
				),
				$this->params['preferred_language']
			);		
		}
		////////////////////////////////////////////////////////////
	}

	/**
	 * Send activation email
	 * @param $email
	 */
	public static function Reactivate($email)
	{		
		global $objSettings;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			self::$static_error = _OPERATION_BLOCKED;
			return false;
		}
		
		if(!empty($email)){
			if(check_email_address($email)){
				$sql = 'SELECT id, first_name, last_name, user_name, registration_code, preferred_language, is_active ';
				if(!PASSWORDS_ENCRYPTION){
					$sql .= ', user_password ';
				}else{
					if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
						$sql .= ', AES_DECRYPT(user_password, \''.PASSWORDS_ENCRYPT_KEY.'\') as user_password ';
					}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
						$sql .= ', \'\' as user_password ';
					}				
				}
				$sql .= 'FROM '.TABLE_CUSTOMERS.' WHERE email = \''.encode_text($email).'\'';				
				$temp = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
				if(is_array($temp) && count($temp) > 0){
					if($temp['registration_code'] != '' && $temp['is_active'] == '0'){
						////////////////////////////////////////////////////////		
						if(!PASSWORDS_ENCRYPTION){
							$user_password = $temp['user_password'];
						}else{
							if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
								$user_password = $temp['user_password'];
							}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
								$user_password = get_random_string(8);
								$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET user_password = \''.md5($user_password).'\' WHERE id = '.(int)$temp['id'];
								database_void_query($sql);
							}				
						}
						
						send_email(
							$email,
							$objSettings->GetParameter('admin_email'),
							'new_account_created_confirm_by_email',
							array(
								'{FIRST NAME}' => $temp['first_name'],
								'{LAST NAME}'  => $temp['last_name'],
								'{USER NAME}'  => $temp['user_name'],
								'{USER PASSWORD}' => $user_password,
								'{REGISTRATION CODE}' => $temp['registration_code'],
								'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
								'{BASE URL}'   => APPHP_BASE,
								'{YEAR}' 	   => date('Y')
							),
							$temp['preferred_language']
						);
						////////////////////////////////////////////////////////
						return true;					
					}else{
						self::$static_error = _EMAILS_SENT_ERROR;
						return false;						
					}
				}else{
					self::$static_error = _EMAIL_NOT_EXISTS;
					return false;
				}				
			}else{
				self::$static_error = _EMAIL_IS_WRONG;
				return false;								
			}
		}else{
			self::$static_error = _EMAIL_EMPTY_ALERT;
			return false;
		}
		return true;
	}

	/**
	 * Before Edit Record
	 */
	public function BeforeEditRecord()
	{
		$user_name = isset($this->result[0][0]['user_name']) ? $this->result[0][0]['user_name'] : '';		
		$registration_code = isset($this->result[0][0]['registration_code']) ? $this->result[0][0]['registration_code'] : '';
		$is_active = isset($this->result[0][0]['is_active']) ? $this->result[0][0]['is_active'] : '';
		$reactivation_html = '';
		
        if($registration_code != '' && !$is_active && $this->reg_confirmation == 'by email'){
			$reactivation_html = ' &nbsp;<a href="javascript:void(\'email|reactivate\')" onclick="javascript:if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\'))__mgDoPostBack(\''.TABLE_CUSTOMERS.'\', \'reactivate\');">[ '._REACTIVATION_EMAIL.' ]</a>';
		}
		$this->arrEditModeFields['separator_2']['email']['post_html'] = $reactivation_html;
		
		// hide password fields for "without account" customers
		if(empty($user_name)){
			$this->arrEditModeFields['separator_4']['user_password']['visible'] = false;
		}
		
		return true;
	}

	/**
	 * After Add Record
	 */
	public function AfterAddRecord()
	{ 
		echo '<script type="text/javascript">appChangeCountry(jQuery("#b_country").val(), "b_state", "'.self::GetParameter('b_state', false).'", "'.Application::Get('token').'");</script>';
	}
	
	/**
	 * After Edit Record
	 */
	public function AfterEditRecord()
	{
		$state_value = (self::GetParameter('b_state', false) != '') ? self::GetParameter('b_state', false) : $this->result[0][0]['b_state'];
		echo '<script type="text/javascript">appChangeCountry(jQuery("#b_country").val(), "b_state", "'.$state_value.'", "'.Application::Get('token').'");</script>';
	}

	/**
	 * Returns DataSet array
	 * @param $where_clause
	 * @param $order_clause
	 * @param $limit_clause
	 */
	public function GetAllCustomers($where_clause = '', $order_clause = '', $limit_clause = '')
	{
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE is_active = 1 '.$where_clause.' '.$order_clause.' '.$limit_clause;
		if($this->debug) $this->arrSQLs['select_get_all'] = $sql;					
		return database_query($sql, DATA_AND_ROWS);
	}

	//==========================================================================
    // STATIC METHODS
	//==========================================================================
	/**
	 * Send forgotten password
	 * @param $email
	 */
	public static function SendPassword($email)
	{		
		global $objSettings;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			self::$static_error = _OPERATION_BLOCKED;
			return false;
		}
		
		if(!empty($email)) {
			if(check_email_address($email)){   
				if(!PASSWORDS_ENCRYPTION){
					$sql = 'SELECT id, first_name, last_name, user_name, user_password, preferred_language FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\' AND is_active = 1';
				}else{
					if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
						$sql = 'SELECT id, first_name, last_name, user_name, AES_DECRYPT(user_password, \''.PASSWORDS_ENCRYPT_KEY.'\') as user_password, preferred_language FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\' AND is_active = 1';
					}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
						$sql = 'SELECT id, first_name, last_name, user_name, \'\' as user_password, preferred_language FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\' AND is_active = 1';
					}				
				}
				
				$temp = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
				if(is_array($temp) && count($temp) > 0){
					$sender = $objSettings->GetParameter('admin_email');
					$recipiant = $email;

					if(!PASSWORDS_ENCRYPTION){
						$user_password = $temp['user_password'];
					}else{
						if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
							$user_password = $temp['user_password'];
						}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
							$user_password = get_random_string(8);
							$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET user_password = \''.md5($user_password).'\' WHERE id = '.$temp['id'];
							database_void_query($sql);
						}				
					}

					////////////////////////////////////////////////////////////
					send_email(
						$recipiant,
						$sender,
						'password_forgotten',
						array(
							'{FIRST NAME}' => $temp['first_name'],
							'{LAST NAME}'  => $temp['last_name'],
							'{USER NAME}'  => $temp['user_name'],
							'{USER PASSWORD}' => $user_password,
							'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
							'{BASE URL}'   => APPHP_BASE,
							'{YEAR}' 	   => date('Y')
						),
						$temp['preferred_language']
					);
					////////////////////////////////////////////////////////////
					
					return true;					
				}else{
					self::$static_error = _EMAIL_NOT_EXISTS;
					return false;
				}				
			}else{
				self::$static_error = _EMAIL_IS_WRONG;
				return false;								
			}
		}else{
			self::$static_error = _EMAIL_IS_EMPTY;
			return false;
		}
		return true;
	}
	
	/**
	 * Returns static error description
	 */
	public static function GetStaticError()
	{
		return self::$static_error;
	}	
	
	/**
	 * Reset customer account
	 * @param $email
	 */
	public static function ResetAccount($email)
	{
		global $objSettings;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			self::$static_error = _OPERATION_BLOCKED;
			return false;
		}
		
		if(!empty($email)) {
			if(check_email_address($email)){
			
				$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE user_name = \'\' AND email = \''.encode_text($email).'\'';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					
					$user_name = $email;
					$new_password = get_random_string(8);
					if(!PASSWORDS_ENCRYPTION){
						$user_password = encode_text($new_password);
					}else{
						if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){					
							$user_password = 'AES_ENCRYPT(\''.encode_text($new_password).'\', \''.PASSWORDS_ENCRYPT_KEY.'\')';
						}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
							$user_password = 'MD5(\''.encode_text($new_password).'\')';
						}
					}
					
					$sql = 'UPDATE '.TABLE_CUSTOMERS.'
							SET user_name = \''.encode_text($user_name).'\', user_password = '.$user_password.'
							WHERE email = \''.encode_text($email).'\'';
					database_void_query($sql);

					////////////////////////////////////////////////////////////
					send_email(
						$email,
						$objSettings->GetParameter('admin_email'),
						'new_account_created',
						array(
							'{FIRST NAME}' => $result[0]['first_name'],
							'{LAST NAME}'  => $result[0]['last_name'],
							'{USER NAME}'  => $user_name,
							'{USER PASSWORD}' => $new_password,
							'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
							'{BASE URL}'   => APPHP_BASE
						),
						$result[0]['preferred_language']
					);
					////////////////////////////////////////////////////////////					
					return true;
				}else{
					self::$static_error = _WRONG_PARAMETER_PASSED;
					return false;
				}
			}else{
				self::$static_error = _EMAIL_IS_WRONG;
				return false;								
			}
		}else{
			self::$static_error = _EMAIL_IS_EMPTY;
			return false;
		}
	}
	
	/**
	 *	Get number of customers awaiting aproval
	 */
	public static function AwaitingAprovalCount()
	{
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_CUSTOMERS.' WHERE is_active = 0 AND registration_code != \'\'';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return $result[0]['cnt'];
		}
		return '0';
	}
	
	/**
	 *	Returns customer info
	 *	@param int $customer_id
	 *	@param string $field
	 */
	public static function GetCustomerInfo($customer_id = 0, $field = '')
	{
		$lang = Application::Get('lang');
		$output = array();

		$sql = 'SELECT
				cust.*,
				CONCAT(cust.first_name, " ", cust.last_name) as full_name,
				cust.is_active,
				cd.name as country_name,
				cg.name as group_name									
			FROM '.TABLE_CUSTOMERS.' cust
				LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON cust.b_country = c.abbrv AND c.is_active = 1
				LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
				LEFT OUTER JOIN '.TABLE_CUSTOMER_GROUPS.' cg ON cust.group_id = cg.id
			WHERE
				cust.id = '.(int)$customer_id;		
		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			if(!empty($field)){
				$output = isset($result[0][$field]) ? $result[0][$field]: '';
			}else{
				$output = $result[0];	
			}			
		}
		
		return $output;
	}


	/**
	 * Change Password
	 * @param $password
	 * @param $confirmation - confirm password
	 */
	public static function ChangePassword($password, $confirmation)
	{
		global $objLogin;
		
		$msg = '';
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$msg = draw_important_message(_OPERATION_BLOCKED, false);
			return $msg;
		}
				
		if(!empty($password) && !empty($confirmation) && strlen($password) >= 6) {
			if($password == $confirmation){
				if(!PASSWORDS_ENCRYPTION){
					$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET user_password = '.quote_text(encode_text($password)).' WHERE id = '.(int)$objLogin->GetLoggedID();
				}else{
					if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
						$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET user_password = AES_ENCRYPT('.quote_text($password).', '.quote_text(PASSWORDS_ENCRYPT_KEY).') WHERE id = '.(int)$objLogin->GetLoggedID();
					}elseif(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
						$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET user_password = '.quote_text(md5($password)).' WHERE id = '.(int)$objLogin->GetLoggedID();
					}else{
						$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET user_password = AES_ENCRYPT('.quote_text($password).', '.quote_text(PASSWORDS_ENCRYPT_KEY).') WHERE id = '.(int)$objLogin->GetLoggedID();
					}
				}
				if(database_void_query($sql)){
					$msg .= draw_success_message(_PASSWORD_CHANGED, false);
				}else{
					$msg .= draw_important_message(_PASSWORD_NOT_CHANGED, false);
				}								
			}else $msg .= draw_important_message(_PASSWORD_DO_NOT_MATCH, false);
		}else $msg .= draw_important_message(_PASSWORD_IS_EMPTY, false);

		return $msg;		
	}

	/**
	 * Checks if customer assigned to hotels
	 * @param int $customer_id
	 * @param int $owner_id
	 * @return bool
	 */
	public function AssignedToHotelOwner($customer_id = 0, $owner_id = 0){
		global $objLogin;

		$assigned_to_hotels = '';
		$this->hotelsList = implode(',', $objLogin->AssignedToHotels());
		if(!empty($this->hotelsList)) $assigned_to_hotels = TABLE_BOOKINGS_ROOMS.'.hotel_id IN ('.$this->hotelsList.')';

		$sql = 'SELECT c.*
				FROM '.$this->tableName.' c
					LEFT OUTER JOIN '.TABLE_BOOKINGS.' ON c.id = '.TABLE_BOOKINGS.'.customer_id
					LEFT OUTER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
				WHERE customer_type = 0 AND c.id = '.(int)$customer_id.' AND ('.(!empty($assigned_to_hotels) ? $assigned_to_hotels.' OR ' : '').' c.created_by_admin_id = '.(int)$owner_id.')
				GROUP BY c.id';
		$result = database_query($sql, ROWS_ONLY);
		return $result ? true : false;
	}

	/**
	 * Change Status
	 * @param int $status
	 * @return string
	 */
	public static function ChangeStatus($rid, $status)
	{
		global $objLogin;
		
		$msg = '';
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$msg = draw_important_message(_OPERATION_BLOCKED, false);
			return $msg;
		}
				
		if(!empty($rid)) {
			$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET is_active = '.(int)$status.' WHERE id = '.(int)$rid;
			if(database_void_query($sql)){
				$msg .= draw_success_message(_STATUS_CHANGED, false);
			}else{
				$msg .= draw_important_message(_STATUS_NOT_CHANGED, false);
			}								
		}else $msg .= draw_important_message(_RECORD_CANNOT_EMPTY, false);

		return $msg;		
	}
	

}
