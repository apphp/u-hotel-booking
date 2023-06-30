<?php

/**
 *	Class Bookings
 * -------------- 
 * Description : encapsulates bookings properties
 * Usage       : HotelSite ONLY
 * Updated	    : 11.02.2016
 * Written by  : ApPHP
 *
 *	PUBLIC:						STATIC:						PRIVATE:                    PROTECTED
 * -----------					-----------					-----------                 -------------------
 * __construct             		RemoveExpired           	CalculateFirstNightPrice	OnItemCreated_ViewMode
 * __destruct                  	DrawBookingStatusBlock		GetVatPercentDecimalPoints
 * DrawBookingDescription		DrawCheckList				ReturnMoneyToBalance
 * BeforeEditRecord				GetTotalPayments				
 * BeforeDeleteRecord			NotifyCustomerAfterStayed
 * BeforeDetailsRecord         	CalculateCancellationFee
 * AfterDeleteRecord           	CountAllByCoupon
 * CleanUpBookings
 * CleanUpCreditCardInfo
 * UpdateRoomNumbers
 * UpdateComment
 * RecalculateExtras
 * UpdatePaymentDate
 * DrawBookingInvoice
 * DrawBookingComment
 * SendInvoice
 * CancelRecord
 * DrawBookingStatus
 * GetBookingHotelsList
 * GetBookingRoomsList
 * GetBookingExtrasList
 * PrepareInvoiceDownload
 * CheckRecordAssigned
 * BeforeUpdateRecord
 * SetViewModeSql
 * 
 **/


class Bookings extends MicroGrid {
	
	protected $debug = false;
	protected $assigned_to_hotels = '';
	
	//------------------------------
	public $message;
	
	//------------------------------
	private $page;
	private $user_id;
	private $rooms_amount;
	private $booking_number;
	private $booking_status;
	private $booking_customer_id;
	private $booking_is_admin_reserv;
	private $revert_sum;
	private $allow_payment_with_balance;
	private $fieldDateFormat;
	private $vat_included_in_price;
	private $online_credit_card_required;
	private $customers_cancel_reservation;
	private $default_currency_info;
	private $currencyFormat;
	private $arr_payment_types;
	private $arr_payment_methods;
	private $additional_info_email;
	private $statuses;
	private $statuses_vm;
	private $status_changed;
	private $payment_type;
	private $cancel_payment_date;
	private $cancellation_fee;
	private $where_booking_rooms = '';
	private $objBookingSettings = null;
	private $sqlFieldDatetimeFormat = '';
	private $all_hotels = array();
	private $arr_hotels = array();
	private $arr_room_types = array();
    private $arr_affiliates = array();

    private $hotelOwner = false;
    private $regionalManager = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($user_id = '') {
		
		parent::__construct();
		
		global $objSettings, $objLogin;

		$this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner');
		$this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager');

		$this->params = array();		
		if(isset($_POST['status']))                $this->params['status'] = prepare_input($_POST['status']);
		if(isset($_POST['hotel_reservation_id']))  $this->params['hotel_reservation_id'] = prepare_input($_POST['hotel_reservation_id']);
		if(isset($_POST['additional_payment']))    $this->params['additional_payment'] = prepare_input($_POST['additional_payment']);
		if(isset($_POST['additional_info']))       $this->params['additional_info'] = prepare_input($_POST['additional_info']);
		if(isset($_POST['additional_info_email'])) $this->params['additional_info_email'] = prepare_input($_POST['additional_info_email']);

		$this->additional_info_email = isset($this->params['additional_info_email']) ? prepare_input($_POST['additional_info_email']) : '';

		$this->vat_included_in_price      	= ModulesSettings::Get('booking', 'vat_included_in_price');
		$this->online_credit_card_required 	= ModulesSettings::Get('booking', 'online_credit_card_required');
		$this->customers_cancel_reservation = ModulesSettings::Get('booking', 'customers_cancel_reservation');

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
		$rid = self::GetParameter('rid');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_BOOKINGS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->message      = '';
		$this->booking_number = '';
		$this->rooms_amount = '';
		$this->booking_status = '';
		$this->booking_customer_id = '';
		$this->booking_is_admin_reserv = '0';
		$this->revert_sum = 0;
		$this->allow_payment_with_balance = ModulesSettings::Get('booking', 'allow_payment_with_balance') == 'yes' ? true : false;
		
		$arr_statuses = array('0'=>_PREBOOKING, '1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED);
		$this->statuses = array('0'=>_PREBOOKING, '1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED, '5'=>_PAYMENT_ERROR);
		$this->statuses_vm = array('0' => '<span style=color:#222222>'._PREBOOKING.'</span>',
								 '1' => '<span style=color:#0000a3>'._PENDING.'</span>',
								 '2' => '<span style=color:#a3a300>'._RESERVED.'</span>',
								 '3' => '<span style=color:#00a300>'._COMPLETED.'</span>',
								 '4' => '<span style=color:#660000>'._REFUNDED.'</span>',
								 '5' => '<span style=color:#a30000>'._PAYMENT_ERROR.'!</span>',
								 '6' => '<span style=color:#939393>'._CANCELED.'</span>',
								 '-1' => _UNKNOWN);
        if($this->regionalManager){
    		$arr_customer_types = array('0'=>_CUSTOMER, '1'=>_AGENCY, '99'=>_MY_AGENCIES, '2'=>_ADMIN);
        }else{
            $arr_customer_types = array('0'=>_CUSTOMER, '1'=>_AGENCY, '2'=>_ADMIN);
        }

		$this->arr_payment_types = array('0'=>_PAY_ON_ARRIVAL, '1'=>_ONLINE, '2'=>_PAYPAL, '3'=>'2CO', '4'=>'Authorize.Net', '5'=>_BANK_TRANSFER, '6'=>_BALANCE, '7'=>_UNKNOWN);
		$this->arr_payment_methods = array('0'=>_PAYMENT_COMPANY_ACCOUNT, '1'=>_CREDIT_CARD, '2'=>_ECHECK, '3'=>_UNKNOWN);
		$allow_editing = $allow_deleting = $allow_cancelation = true;

		if($user_id != ''){
			$this->user_id = $user_id;
			$this->page = 'customer=my_bookings';
			$this->actions   = array('add'=>false, 'edit'=>false, 'details'=>false, 'delete'=>false);
			$arr_statuses_filter = array('1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED, '5'=>_PAYMENT_ERROR, '6'=>_CANCELED);
			$arr_statuses_edit = array('1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED);
			$arr_statuses_edit_completed = array('3'=>_COMPLETED, '4'=>_REFUNDED);
		}else{
			$this->user_id = '';
			$this->page = 'admin=mod_booking_bookings';
			
			$allow_editing = !$objLogin->HasPrivileges('edit_bookings') ? false : true;
			$allow_deleting = !$objLogin->HasPrivileges('delete_bookings') ? false : true;
			$allow_cancelation = !$objLogin->HasPrivileges('cancel_bookings') ? false : true; 
			
			$this->actions = array('add'=>false, 'edit'=>$allow_editing, 'details'=>false, 'delete'=>$allow_deleting);
			$arr_statuses_filter = array('0'=>_PREBOOKING, '1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED, '5'=>_PAYMENT_ERROR, '6'=>_CANCELED);
			$arr_statuses_edit = array('1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED);
			$arr_statuses_edit_completed = array('3'=>_COMPLETED, '4'=>_REFUNDED);
		}
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowPrint   = true;
		$this->formActionURL = 'index.php?'.$this->page;		

		$this->allowLanguages = false;
		$this->languageId  	= ''; // ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();

		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		$hotels_list = '';
        $hotels = array();
        $hotels_location = array();
		$travel_agency_list = '';

		// Filters
		if(SHOW_CREATION_DATE_FROM_TO){
			$american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;

			$created_date_min = isset($_POST['filter_by_created_date_min']) ? prepare_input($_POST['filter_by_created_date_min'], true) : '';
			$created_date_max = isset($_POST['filter_by_created_date_max']) ? prepare_input($_POST['filter_by_created_date_max'], true) : '';
			$date_min_arr = explode('-', $created_date_min);
            $count_date_min = count($date_min_arr);
			
			$date_min_day   = '01';
			$date_min_mouth = '01';
			$date_min_year  = '';

			if($count_date_min == 3){
				$date_min_day   = $date_min_arr[$american_format ? 1 : 0];
				$date_min_mouth = $date_min_arr[$american_format ? 0 : 1];
				$date_min_year  = $date_min_arr[2];
			}elseif($count_date_min == 2){
				$date_min_mouth = $date_min_arr[0];
				$date_min_year  = $date_min_arr[1];
			}elseif($count_date_min == 1){
				$date_min_year  = $date_min_arr[0];
			}
            if(!empty($date_min_year)){
                $date_min = $date_min_year.'-'.$date_min_mouth.'-'.$date_min_day.' 00:00:00';
				$this->WHERE_CLAUSE .= ' AND '.$this->tableName.'.created_date >= \''.$date_min.'\'';
			}

			$date_max_arr = explode('-', $created_date_max);
            $count_date_max = count($date_max_arr);
			
			$date_max_day   = '31';
			$date_max_mouth = '12';
			$date_max_year  = '';

			if($count_date_max == 3){
				$date_max_day   = $date_max_arr[$american_format ? 1 : 0];
				$date_max_mouth = $date_max_arr[$american_format ? 0 : 1];
				$date_max_year  = $date_max_arr[2];
			}elseif($count_date_max == 2){
				$date_max_mouth = $date_max_arr[0];
				$date_max_year  = $date_max_arr[1];
			}elseif($count_date_max == 1){
				$date_max_year  = $date_max_arr[0];
			}
			if(!empty($date_max_year)){
                $date_max = $date_max_year.'-'.$date_max_mouth.'-'.$date_max_day.' 23:59:59';
				$this->WHERE_CLAUSE .= ' AND '.$this->tableName.'.created_date <= \''.$date_max.'\'';
			}
		}

		if($this->user_id != ''){
			$this->WHERE_CLAUSE .= ' AND '.$this->tableName.'.is_admin_reservation = 0 AND
				                         '.$this->tableName.'.status <> 0 AND
			                             '.$this->tableName.'.customer_id = '.(int)$this->user_id;
		}elseif($this->hotelOwner){
			$hotels = $objLogin->AssignedToHotels();
			$hotels_list = implode(',', $hotels);
		    $this->assigned_to_hotels = ' AND '.(!empty($hotels_list) ? 'br.hotel_id in ('.$hotels_list.') AND '.$this->tableName.'.status <> 0 ' : '1 = 0');
            $this->where_booking_rooms = TABLE_BOOKINGS_ROOMS.'.hotel_id IN ('.$hotels_list.')';
		}elseif($this->regionalManager){
			$hotels = AccountLocations::GetHotels($objLogin->GetLoggedID());
			$travel_agency_ids = AccountLocations::GetTravelAgencies($objLogin->GetLoggedID());
			$hotels_list = !empty($hotels) ? implode(',', $hotels) : '-1';
			$travel_agency_list = !empty($travel_agency_ids) ? implode(',', $travel_agency_ids) : '-1';
			$this->assigned_to_hotels = ' AND (br.hotel_id IN ('.$hotels_list.') OR '.TABLE_BOOKINGS.'.customer_id IN ('.$travel_agency_list.')) AND '.$this->tableName.'.status <> 0';
            $this->where_booking_rooms = TABLE_BOOKINGS_ROOMS.'.hotel_id IN ('.$hotels_list.')';
            // prepare hotels location
            $locations = AccountLocations::GetHotelLocations($objLogin->GetLoggedID());
            if(!empty($locations) && is_array($locations)){
                $arr_locations = HotelsLocations::GetAllLocations('hl.id IN ('.implode(',', $locations).')');
                if(!empty($arr_locations) && $arr_locations[1] > 0){
                    foreach($arr_locations[0] as $key => $val){
                        $hotels_location[$val['id']] = $val['name'];
                    }
                }
            }
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_hotels;
		
		//$this->GROUP_BY_CLAUSE = 'GROUP BY '.$this->tableName.'.booking_number';
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.created_date DESC'; // ORDER BY date_created DESC
		
		$this->isAlterColorsAllowed = true;
		$this->isPagingAllowed = true;
		$this->pageSize = 30;
		$this->isSortingAllowed = true;		
		$this->isExportingAllowed = ($user_id != '') ? false : true;
		$this->arrExportingTypes = array('csv'=>true);
		
		$date_format_settings = get_date_format('view', true);

        // prepare hotels array
        $where_clause = '';
        if($this->regionalManager || $this->hotelOwner){
            $where_clause = !empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : '1 = 0';
        }
        $all_hotels = Hotels::GetAllHotels();
        $this->all_hotels = array();
        foreach($all_hotels[0] as $key => $val){
            $this->all_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
        }

        $this->arr_hotels = array();
        if($objLogin->IsLoggedInAs('owner', 'mainadmin', 'admin')){
            foreach($this->all_hotels as $key => $val){
                $this->arr_hotels[$key] = $val;
            }
        }elseif(!empty($hotels) || !($this->hotelOwner || $this->regionalManager)){
            foreach($this->all_hotels as $key => $val){
                if(in_array($key, $hotels)){
                    $this->arr_hotels[$key] = $val;
                }
            }
        }

		// prepare room types
		$total_room_types = Rooms::GetRoomTypes();
		foreach($total_room_types as $key => $val){
			$this->arr_room_types[$val['id']] = $val['room_type'];
		}

		// prepare countries array		
		$total_countries = Countries::GetAllCountries();
		$arr_countries = array();
		foreach($total_countries[0] as $key => $val){
			$arr_countries[$val['abbrv']] = $val['name'];
		}


		// prepare affiliates array		
		if(Modules::IsModuleInstalled('affiliates')){
			$total_affiliates = Affiliates::GetAllAffiliates();
			foreach($total_affiliates[0] as $key => $val){
				$this->arr_affiliates[$val['affiliate_id']] = $val['first_name'].' '.$val['last_name'].' ('.$val['affiliate_id'].')';
			}
		}
		
		$this->default_currency_info = Currencies::GetDefaultCurrencyInfo();
		$default_currency_rate = isset($this->default_currency_info['rate']) ? $this->default_currency_info['rate'] : 1;
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->fieldDateFormat = 'M d, Y';
			$this->sqlFieldDateFormat = '%b %d, %Y';
			$this->sqlFieldDateFormatFrom = '%b %d';
			$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
		}else{
			$this->fieldDateFormat = 'd M, Y';
			$this->sqlFieldDateFormat = '%d %b, %Y';
			$this->sqlFieldDateFormatFrom = '%d %b';
			$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
		}
		$this->currencyFormat = get_currency_format();

		$this->isFilteringAllowed = true;
        $this->maxFilteringColumns = 5; 
        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		// define filtering fields
		$this->arrFilteringFields = array(
			_BOOKING.' #' => array('table'=>$this->tableName, 'field'=>'booking_number', 'type'=>'text', 'sign'=>'like%', 'width'=>'90px'),
			$hotel_name   => array('table'=>'', 'field'=>'b_r_hotel_id', 'type'=>'dropdownlist', 'source'=>$this->arr_hotels, 'sign'=>'=', 'width'=>'', 'visible'=>(($this->user_id != '') ? false : true)),
        );
        // For admins
		if(empty($this->user_id)) $this->arrFilteringFields[_STATUS] = array('table'=>$this->tableName, 'field'=>'status', 'type'=>'dropdownlist', 'source'=>$arr_statuses_filter, 'sign'=>'=', 'width'=>'120px');
		$this->arrFilteringFields[_CHECK_IN] = array('table'=>'', 'field'=>'checkin', 'type'=>'calendar', 'date_format'=>$date_format_settings, 'sign'=>'like%', 'width'=>'100px', 'visible'=>true);
		$this->arrFilteringFields[_CHECK_OUT] = array('table'=>'', 'field'=>'checkout', 'type'=>'calendar', 'date_format'=>$date_format_settings, 'sign'=>'like%', 'width'=>'100px', 'visible'=>true);
		if(SHOW_CREATION_DATE_FROM_TO){
			$this->arrFilteringFields[_CREATED_DATE.' ('._FROM.')'] = array('table'=>'', 'field'=>'created_date_min', 'type'=>'calendar', 'date_format'=>$date_format_settings, 'width'=>'100px', 'visible'=>true, 'custom_handler'=>true);
	        $this->arrFilteringFields[_CREATED_DATE.' ('._TO.')'] = array('table'=>'', 'field'=>'created_date_max', 'type'=>'calendar', 'date_format'=>$date_format_settings, 'width'=>'100px', 'visible'=>true, 'custom_handler'=>true);
		}else{
			$this->arrFilteringFields[_CREATED_DATE] = array('table'=>TABLE_BOOKINGS, 'field'=>'created_date', 'type'=>'calendar', 'date_format'=>$date_format_settings, 'sign'=>'like%', 'width'=>'100px', 'visible'=>true);
		}
        // For customers
		if(!empty($this->user_id)) $this->arrFilteringFields[_STATUS] = array('table'=>$this->tableName, 'field'=>'status', 'type'=>'dropdownlist', 'source'=>$arr_statuses_filter, 'sign'=>'=', 'width'=>'120px');
		
		if($objLogin->IsLoggedInAs('hotelowner','regionalmanager')){
			// prepare rooms array		
			$total_rooms = Rooms::GetAllActive((!empty($hotels_list) ? 'h.id IN ('.$hotels_list.')' : ''));
			$arr_rooms = array();
			foreach($total_rooms[0] as $key => $val){
				$arr_rooms[$val['id']] = $val['room_type'];
			}
			$this->arrFilteringFields[_ROOMS] = array('table'=>'br', 'field'=>'room_id', 'type'=>'dropdownlist', 'source'=>$arr_rooms, 'sign'=>'=', 'width'=>'', 'visible'=>(($this->user_id != '') ? false : true));
		}
		
		if(empty($this->user_id)){
			$this->arrFilteringFields[_CUSTOMER] = array('table'=>TABLE_CUSTOMERS, 'field'=>'id', 'type'=>'text', 'sign'=>'=', 'width'=>'140px');
			$this->arrFilteringFields[_ACCOUNT_TYPE] = array('table'=>TABLE_CUSTOMERS, 'field'=>'customer_type', 'type'=>'dropdownlist', 'source'=>$arr_customer_types, 'sign'=>'=', 'default'=>'', 'width'=>'120px');
			// Trick to show admin bookings - we use it because we mess firlds from different tables
			// and have to use different where clause for each type
			$customer_type = isset($_POST['filter_by_'.TABLE_CUSTOMERS.'customer_type']) ? $_POST['filter_by_'.TABLE_CUSTOMERS.'customer_type'] : '';
			$customer_name_filter_table = TABLE_CUSTOMERS;
			if($customer_type === '0' || $customer_type == '1'){
				$this->WHERE_CLAUSE .= !empty($this->WHERE_CLAUSE) ? ' AND '.$this->tableName.'.is_admin_reservation = 0' : '';
			}elseif($customer_type == '2' && self::GetParameter('operation') != 'reset_filtering'){
				unset($_REQUEST['filter_by_'.TABLE_CUSTOMERS.'customer_type']);
				$this->WHERE_CLAUSE .= !empty($this->WHERE_CLAUSE) ? ' AND '.$this->tableName.'.is_admin_reservation = 1' : '';
				$customer_name_filter_table = TABLE_ACCOUNTS;
				$this->arrFilteringFields[_ACCOUNT_TYPE]['default'] = 2;
            }elseif($customer_type == '99' && $this->regionalManager){
                // My Agencies
				$this->WHERE_CLAUSE .= (!empty($this->WHERE_CLAUSE) ? ' AND ' : '').(!empty($travel_agency_list) ? $this->tableName.'.customer_id IN ('.$travel_agency_list.')' : '1 = 0');
				$this->arrFilteringFields[_ACCOUNT_TYPE]['sign'] = '<=';
			}
		}

		if(empty($this->user_id)) $this->arrFilteringFields[_LAST_NAME] = array('table'=>$customer_name_filter_table, 'field'=>'last_name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'90px');
		if(empty($this->user_id)) $this->arrFilteringFields[_FIRST_NAME] = array('table'=>$customer_name_filter_table, 'field'=>'first_name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'90px');

		if(Modules::IsModuleInstalled('affiliates') && $objLogin->IsLoggedInAs('owner','mainadmin','admin')){
			$this->arrFilteringFields[_AFFILIATES] = array('table'=>TABLE_BOOKINGS, 'field'=>'affiliate_id', 'type'=>'dropdownlist', 'source'=>$this->arr_affiliates, 'sign'=>'=', 'width'=>'130px', 'visible'=>true);
        }

        if($this->regionalManager){
            $this->arrFilteringFields[FLATS_INSTEAD_OF_HOTELS ? _FLATS_LOCATION : _HOTELS_LOCATION] = array('table'=>TABLE_HOTELS, 'field'=>'hotel_location_id', 'type'=>'dropdownlist', 'source'=>$hotels_location, 'sign'=>'=', 'width'=>'130px', 'visible'=>true);
            // crutch to create the right sorting for comments
            $this->arrFilteringFields[_COMMENTS] = array('table'=>TABLE_BOOKINGS, 'field'=>'id', 'type'=>'dropdownlist', 'source'=>array('0'=>_NO, '1'=>_YES), 'sign'=>'>=', 'width'=>'130px', 'visible'=>true);
			$comment_type = (isset($_POST['filter_by_'.TABLE_BOOKINGS.'id']) && $_POST['filter_by_'.TABLE_BOOKINGS.'id'] !== '') ? ($_POST['filter_by_'.TABLE_BOOKINGS.'id'] == 0 ? 'new' : 'update') : 'all';
			if($comment_type == 'new'){
				$this->WHERE_CLAUSE .= ' AND '.$this->tableName.'.regional_menager_comment = \'\'';
			}elseif($comment_type == 'update'){
				$this->WHERE_CLAUSE .= ' AND '.$this->tableName.'.regional_menager_comment != \'\'';
			}
        }

		$this->isAggregateAllowed = true;
		// define aggregate fields for View Mode
		$this->arrAggregateFields = array(
			'tp_w_currency' => array('function'=>'SUM', 'align'=>'center', 'aggregate_by'=>'tp_wo_default_currency', 'decimal_place'=>2, 'sign'=>$this->default_currency_info['symbol']),
		);

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//----------------------------------------------------------------------

        // set locale time names
		$this->SetLocale(Application::Get('lc_time_name'));
		
		$this->VIEW_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.booking_number,
								'.$this->tableName.'.hotel_reservation_id,
								'.$this->tableName.'.booking_description,
								'.$this->tableName.'.additional_info,
								'.$this->tableName.'.order_price,
								'.$this->tableName.'.vat_fee,
								'.$this->tableName.'.vat_percent,
								'.$this->tableName.'.payment_sum,
								'.$this->tableName.'.currency,
								'.$this->tableName.'.customer_id,
								'.$this->tableName.'.transaction_number,
								(SELECT GROUP_CONCAT(DATE_FORMAT(checkin, "'.$this->sqlFieldDateFormatFrom.'"), " - " ,DATE_FORMAT(checkout, "'.$this->sqlFieldDateFormat.'") SEPARATOR "<br>") FROM '.TABLE_BOOKINGS_ROOMS.' br WHERE br.booking_number = '.$this->tableName.'.booking_number) as mod_checkin_checkout,
								(SELECT GROUP_CONCAT(IF(br.room_numbers != "", br.room_numbers, "") SEPARATOR "<br>") FROM '.TABLE_BOOKINGS_ROOMS.' br WHERE br.booking_number = '.$this->tableName.'.booking_number) as mod_room_numbers,
								DATE_FORMAT('.$this->tableName.'.created_date, "%b %d, %Y <br/> %H:%i:%s") as created_date_formated,
								DATE_FORMAT('.$this->tableName.'.payment_date, "'.$this->sqlFieldDateFormat.'") as payment_date_formated,
								'.$this->tableName.'.payment_type,
								'.$this->tableName.'.payment_method,
								IF('.$this->tableName.'.status > 6, -1, '.$this->tableName.'.status) as status,
								IF(
									'.$this->tableName.'.is_admin_reservation = 0,
									CONCAT("<a href=\"javascript:void(\'customer|view\')\" onclick=\"open_popup(\'popup.ajax.php\',\'customer\',\'",'.$this->tableName.'.customer_id,"\',\'",'.TABLE_CUSTOMERS.'.customer_type,"\',\''.Application::Get('token').'\')\">", CONCAT('.TABLE_CUSTOMERS.'.last_name, " ", '.TABLE_CUSTOMERS.'.first_name, " ", IF('.TABLE_CUSTOMERS.'.customer_type = 1, " ('.htmlentities(_AGENCY).')", "")), "</a>"),
									CONCAT("<a href=\"javascript:void(\'admin|view\')\" onclick=\"open_popup(\'popup.ajax.php\',\'admin\',\'",'.$this->tableName.'.customer_id,"\',\'",0,"\',\''.Application::Get('token').'\')\">", CONCAT('.TABLE_ACCOUNTS.'.last_name, " ", '.TABLE_ACCOUNTS.'.first_name, " (", '.TABLE_ACCOUNTS.'.account_type), ")</a>")
								) as customer_name,
								'.TABLE_CURRENCIES.'.symbol,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = \'before\' THEN
										CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment) 
					                ELSE
										CONCAT('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment, '.TABLE_CURRENCIES.'.symbol)
								END as tp_w_currency,
								('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment) as tp_wo_currency,
								((('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment) / '.TABLE_CURRENCIES.'.rate) * '.$default_currency_rate.') as tp_wo_default_currency,
								IF(('.$this->tableName.'.status = 2 OR '.$this->tableName.'.status = 3), CONCAT("<nobr><a href=\"javascript:void(\'invoice\')\" title=\"'._INVOICE.'\" onclick=\"javascript:__mgDoPostBack(\''.$this->tableName.'\', \'invoice\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\')\">[ ", "'._INVOICE.'", " ]</a></nobr>"), "<span class=lightgray>'._INVOICE.'</span>") as link_order_invoice,
								CONCAT("<nobr><a href=\"javascript:void(\'description\')\" title=\"'._DESCRIPTION.'\" onclick=\"javascript:__mgDoPostBack(\''.$this->tableName.'\', \'description\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\')\">[ ", "'._DESCRIPTION.'", " ]</a></nobr>") as link_order_description,
								IF(
									'.$this->tableName.'.regional_menager_comment = \'\',
									CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'.encode_text(htmlspecialchars(_REGIONAL_MANAGER_COMMENT_ADD)).'\" onclick=\"javascript:__mgDoPostBack(\''.TABLE_BOOKINGS.'\', \'comment\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\"><img src=\"'.APPHP_BASE.'images/microgrid_icons/comment_add.png\" /></a></nobr>"),
									CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'.encode_text(htmlspecialchars(_REGIONAL_MANAGER_COMMENT_EDIT)).'\" onclick=\"javascript:__mgDoPostBack(\''.TABLE_BOOKINGS.'\', \'comment\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\"><img src=\"'.APPHP_BASE.'images/microgrid_icons/comment_update.png\" /></a></nobr>")
								) as link_regional_menager_comment,
								IF(
									(('.$this->tableName.'.status IN(2,3) AND DATEDIFF('.$this->tableName.'.cancel_payment_date, \''.@date('Y-m-d').'\') >= 0) OR '.$this->tableName.'.status = 1),
									CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'._BUTTON_CANCEL.'\" onclick=\"javascript:__mgMyDoPostBack(\''.TABLE_BOOKINGS.'\', \'cancel\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\">[ '._BUTTON_CANCEL.' ]</a></nobr>"),
									IF('.$this->tableName.'.status = 6, "--", "<span class=lightgray>'._BUTTON_CANCEL.'</span>")
								) as link_cust_order_cancel,
								IF(
									'.$this->tableName.'.status NOT IN (4,5,6), CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'._BUTTON_CANCEL.'\" onclick=\"javascript:__mgMyDoPostBack(\''.TABLE_BOOKINGS.'\', \'cancel\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\">[ '._BUTTON_CANCEL.' ]</a></nobr>"), "<span class=lightgray>--</span>"
								) as link_admin_order_cancel,
								br.hotel_id,
								br.room_id,
								(SELECT GROUP_CONCAT(IF(br.hotel_id != "", br.hotel_id, "") SEPARATOR "<br>") FROM '.TABLE_BOOKINGS_ROOMS.' br WHERE br.booking_number = '.$this->tableName.'.booking_number) as mod_hotel_types,
								(SELECT GROUP_CONCAT(IF(br.room_id != "", br.room_id, "") SEPARATOR "<br>") FROM '.TABLE_BOOKINGS_ROOMS.' br WHERE br.booking_number = '.$this->tableName.'.booking_number) as mod_room_types,
								'.TABLE_CUSTOMERS.'.b_country
							FROM '.$this->tableName.'
								INNER JOIN (SELECT '.TABLE_BOOKINGS_ROOMS.'.booking_number, '.TABLE_BOOKINGS_ROOMS.'.hotel_id, '.TABLE_BOOKINGS_ROOMS.'.room_id, '.TABLE_BOOKINGS_ROOMS.'.checkin, '.TABLE_BOOKINGS_ROOMS.'.checkout, '.TABLE_BOOKINGS_ROOMS.'.hotel_id as b_r_hotel_id FROM '.TABLE_BOOKINGS_ROOMS.(!empty($this->where_booking_rooms) ? (' WHERE '.$this->where_booking_rooms) : '').' GROUP BY '.TABLE_BOOKINGS_ROOMS.'.booking_number) br ON '.$this->tableName.'.booking_number = br.booking_number
								'.($this->regionalManager ? 'INNER JOIN '.TABLE_HOTELS.' ON '.TABLE_BOOKINGS_ROOMS.'.hotel_id = '.TABLE_HOTELS.'.id' : '').'
								LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
								LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
								LEFT OUTER JOIN '.TABLE_ACCOUNTS.' ON '.$this->tableName.'.customer_id = '.TABLE_ACCOUNTS.'.id AND '.$this->tableName.'.is_admin_reservation = 1
							';		

							//'.$this->tableName.'.coupon_code,
							//'.$this->tableName.'.discount_campaign_id,
							//, " (", '.TABLE_CUSTOMERS.'.b_country, ")"

		// define view mode fields
		if($this->user_id != ''){
			$this->arrViewModeFields = array(
				'created_date_formated'  => array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'created_date'),
				'booking_number'    	 => array('title'=>_BOOKING_NUMBER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
				'mod_checkin_checkout'   => array('title'=>_CHECK_IN.' - '._CHECK_OUT, 'type'=>'label', 'align'=>'left', 'width'=>'150px', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'checkin'),
				//'hotel_id'        		 => array('title'=>_HOTEL, 'type'=>'enum',  'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->all_hotels),
				//'room_id'        		 => array('title'=>_ROOMS, 'type'=>'enum',  'align'=>'left', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_room_types),
				'mod_hotel_types'        => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS, 'type'=>'label', 'align'=>'left', 'width'=>'160px', 'height'=>'', 'maxlength'=>'', 'sortable'=>false),
				'mod_room_types'    	 => array('title'=>_ROOMS, 'visible'=>(FLATS_INSTEAD_OF_HOTELS ? false : true), 'type'=>'label', 'align'=>'left', 'width'=>'160px', 'height'=>'', 'maxlength'=>'', 'sortable'=>false, 'sort_by'=>''),
				//'payment_type'           => array('title'=>_METHOD, 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'source'=>$this->arr_payment_types),
				'tp_w_currency'   		 => array('title'=>_PAYMENT_SUM, 'type'=>'label', 'align'=>'right', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'tp_wo_currency', 'sort_type'=>'numeric', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
				'status'                 => array('title'=>_STATUS, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->statuses_vm),
				'link_order_description' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'nowrap'=>'nowrap'),
				'link_order_invoice'     => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'75px', 'height'=>'', 'maxlength'=>'', 'nowrap'=>'nowrap'),
				'link_cust_order_cancel' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'nowrap'=>'nowrap', 'visible'=>(($this->customers_cancel_reservation > '0') ? true : false)),
			);			
		}else{
			$this->arrViewModeFields = array(
				'created_date_formated'   => array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'nowrap'=>'nowrap', 'maxlength'=>'', 'sort_by'=>'created_date'),
				'booking_number'    	  => array('title'=>_BOOKING_NUMBER, 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>''),
				'mod_checkin_checkout'    => array('title'=>_CHECK_IN.' - '._CHECK_OUT, 'type'=>'label', 'align'=>'center', 'width'=>'180px', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'checkin'),
				//'hotel_id'        		  => array('title'=>_HOTEL, 'type'=>'enum',  'align'=>'left', 'width'=>'130px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_hotels),
				//'room_id'        		  => array('title'=>_ROOMS, 'type'=>'enum',  'align'=>'left', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_room_types),
				'mod_hotel_types'         => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS, 'type'=>'label', 'align'=>'left', 'width'=>'160px', 'height'=>'', 'maxlength'=>'', 'sortable'=>false),
				'mod_room_types'    	  => array('title'=>_ROOMS, 'visible'=>(FLATS_INSTEAD_OF_HOTELS ? false : true), 'type'=>'label', 'align'=>'left', 'width'=>'160px', 'height'=>'', 'maxlength'=>'', 'sortable'=>false, 'sort_by'=>''),
				'mod_room_numbers'        => array('title'=>'#', 'type'=>'label', 'align'=>'center', 'width'=>'50px', 'height'=>'', 'maxlength'=>''),
				//'payment_type'            => array('title'=>_METHOD, 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'source'=>$this->arr_payment_types),
				'customer_name'   		  => array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
				//'b_country'               => array('title'=>_COUNTRY, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_countries),
				'tp_w_currency'   		  => array('title'=>_PAYMENT, 'type'=>'label', 'align'=>'right', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'tp_wo_currency', 'sort_type'=>'numeric', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
				'status'                  => array('title'=>_STATUS, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->statuses_vm),
				'link_order_description'  => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>''),
				'link_order_invoice'      => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>''),
				'link_admin_order_cancel' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'visible'=>$allow_cancelation),
                'link_regional_menager_comment' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=> '', 'visible'=>$this->regionalManager)
			);						
		}
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.booking_number,
								'.$this->tableName.'.hotel_reservation_id,
								'.$this->tableName.'.booking_number as booking_number_label,
								'.$this->tableName.'.booking_description,
								'.$this->tableName.'.additional_info,
								'.$this->tableName.'.order_price,
								CASE
									WHEN '.$this->tableName.'.pre_payment_type = "first night" THEN CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.pre_payment_value)
									WHEN '.$this->tableName.'.pre_payment_type = "fixed sum" THEN CONCAT("'.$this->default_currency_info['symbol'].'", '.$this->tableName.'.pre_payment_value)
									WHEN '.$this->tableName.'.pre_payment_type = "percentage" THEN CONCAT('.$this->tableName.'.pre_payment_value, "%")
									ELSE ""
								END as mod_pre_payment,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = "before" THEN CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.payment_sum)
					                ELSE CONCAT('.$this->tableName.'.payment_sum, '.TABLE_CURRENCIES.'.symbol)
								END as mod_payment_sum,
								CONCAT(
									'.TABLE_CURRENCIES.'.symbol,
									IF((('.$this->tableName.'.order_price - '.$this->tableName.'.discount_fee - '.$this->tableName.'.guests_discount - '.$this->tableName.'.nights_discount) + '.$this->tableName.'.initial_fee + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment) > 0),
								   (('.$this->tableName.'.order_price - '.$this->tableName.'.discount_fee - '.$this->tableName.'.guests_discount - '.$this->tableName.'.nights_discount) + '.$this->tableName.'.initial_fee + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment)),
									0)									
								) as mod_have_to_pay,
								'.$this->tableName.'.coupon_code,
								'.$this->tableName.'.additional_payment,
								'.$this->tableName.'.vat_fee,
								'.$this->tableName.'.vat_percent,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = "before" THEN CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.vat_fee)
					                ELSE CONCAT('.$this->tableName.'.vat_fee, '.TABLE_CURRENCIES.'.symbol)
								END as mod_vat_fee,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = "before" THEN CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.initial_fee)
					                ELSE CONCAT('.$this->tableName.'.initial_fee, '.TABLE_CURRENCIES.'.symbol)
								END as mod_initial_fee,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = "before" THEN CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.guest_tax)
					                ELSE CONCAT('.$this->tableName.'.guest_tax, '.TABLE_CURRENCIES.'.symbol)
								END as mod_guest_tax,
								'.$this->tableName.'.payment_sum,
								'.$this->tableName.'.currency,
								'.$this->tableName.'.customer_id,								
								'.$this->tableName.'.cc_type,
								'.$this->tableName.'.cc_holder_name,
								IF(
									LENGTH(AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")) = 4,
									CONCAT("...", AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'"), " ('._CLEANED.')"),
									AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")
								) as m_cc_number,								
								AES_DECRYPT('.$this->tableName.'.cc_cvv_code, "'.PASSWORDS_ENCRYPT_KEY.'") as m_cc_cvv_code,
								'.$this->tableName.'.cc_expires_month,
								'.$this->tableName.'.cc_expires_year,
								IF('.$this->tableName.'.cc_expires_month != "", CONCAT('.$this->tableName.'.cc_expires_month, "/", '.$this->tableName.'.cc_expires_year), "") as m_cc_expires_date,
								'.$this->tableName.'.transaction_number,
								'.$this->tableName.'.payment_date,
								DATE_FORMAT('.$this->tableName.'.created_date, "'.$this->sqlFieldDatetimeFormat.'") as created_date_formated,
								DATE_FORMAT('.$this->tableName.'.payment_date, "'.$this->sqlFieldDatetimeFormat.'") as payment_date_formated,
								'.$this->tableName.'.payment_type,
								'.$this->tableName.'.payment_method,
								'.$this->tableName.'.cancel_payment_date,
								DATE_FORMAT('.$this->tableName.'.cancel_payment_date, \''.(($this->fieldDateFormat == 'M d, Y') ? '%b %d, %Y %h:%i %p' : '%d %b %Y %h:%i %p').'\') as cancel_payment_date_formated_till,
								DATE_FORMAT('.$this->tableName.'.cancel_payment_date, \''.(($this->fieldDateFormat == 'M d, Y') ? '%b %d, %Y %h:%i %p' : '%d %b %Y %h:%i %p').'\') as cancel_payment_date_formated_after,
								'.$this->tableName.'.status,
								DATE_FORMAT('.$this->tableName.'.status_changed, "'.$this->sqlFieldDatetimeFormat.'") as status_changed_formated,
								'.$this->tableName.'.status_description,
								IF(
									'.$this->tableName.'.is_admin_reservation = 0,
									CONCAT("<a href=javascript:void(\'customer|view\') onclick=open_popup(\'popup.ajax.php\',\'customer\',\'",'.$this->tableName.'.customer_id,"\',\'",'.TABLE_CUSTOMERS.'.customer_type,"\',\''.Application::Get('token').'\')>", CONCAT('.TABLE_CUSTOMERS.'.last_name, " ", '.TABLE_CUSTOMERS.'.first_name, IF('.TABLE_CUSTOMERS.'.customer_type = 1, CONCAT(" <br>(", '.TABLE_CUSTOMERS.'.company, ")"), "")), "</a>"),
									CONCAT("<a href=javascript:void(\'admin|view\') onclick=open_popup(\'popup.ajax.php\',\'admin\',\'",'.$this->tableName.'.customer_id,"\',\'",0,"\',\''.Application::Get('token').'\')>", CONCAT('.TABLE_ACCOUNTS.'.last_name, " ", '.TABLE_ACCOUNTS.'.first_name, " (", '.TABLE_ACCOUNTS.'.account_type), ")</a>")
								) as customer_name								,
								CONCAT('.TABLE_AFFILIATES.'.first_name, " ", '.TABLE_AFFILIATES.'.last_name) as affiliate_name
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
								LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
								LEFT OUTER JOIN '.TABLE_ACCOUNTS.' ON '.$this->tableName.'.customer_id = '.TABLE_ACCOUNTS.'.id AND '.$this->tableName.'.is_admin_reservation = 1
								LEFT OUTER JOIN '.TABLE_AFFILIATES.' ON '.$this->tableName.'.affiliate_id = '.TABLE_AFFILIATES.'.id
							';
		if($this->user_id != ''){
			$WHERE_CLAUSE = 'WHERE '.$this->tableName.'.is_admin_reservation = 0 AND
								   '.$this->tableName.'.customer_id = '.$this->user_id.' AND
			                       '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
			$this->EDIT_MODE_SQL = $this->EDIT_MODE_SQL.' WHERE TRUE = FALSE';					   
		}else{
			$WHERE_CLAUSE = 'WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
			$this->EDIT_MODE_SQL = $this->EDIT_MODE_SQL.$WHERE_CLAUSE;
		}		

		// prepare trigger
		$sql = 'SELECT
					booking_number,
					vat_percent,
					status,
					payment_type,
					IF(TRIM(cc_number) = "" OR LENGTH(AES_DECRYPT(cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")) <= 4, "hide", "show") as cc_number_trigger
				FROM '.$this->tableName.' WHERE id = '.(int)$rid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$booking_number = $result[0]['booking_number'];
			$cc_number_trigger = $result[0]['cc_number_trigger'];
			$status_trigger = $result[0]['status'];
			$payment_type = $result[0]['payment_type'];
			$vat_percent = ' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).')';			
		}else{
			$cc_number_trigger = 'hide';
			$status_trigger = '0';
			$payment_type = '0';
			$vat_percent = '';
			$booking_number = '';
		}
		
		// Cancellation fee
		$cancellation_fee = '';
		if(self::GetParameter('mg_action', false) == 'edit'){
			$cancellation_fee = self::CalculateCancellationFee($booking_number);	
		}		

		// define edit mode fields
		// BOOKING_DESCRIPTION
		$this->arrEditModeFields['separator_0'] = array(
			'separator_info' => array('legend'=>_BOOKING_DESCRIPTION),
			'booking_number_label' => array('title'=>_BOOKING_NUMBER, 'type'=>'label'),			
			'hotel_reservation_id' => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_RESERVATION_ID : _HOTEL_RESERVATION_ID, 'header_tooltip'=>_INTERNAL_USE_TOOLTIP, 'type'=>'textbox', 'required'=>false, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>(($this->user_id != '') ? false : true)),
			'booking_number'     => array('title'=>'', 'type'=>'hidden', 'required'=>false, 'default'=>''),
			'created_date_formated' => array('title'=>_DATE_CREATED, 'type'=>'label'),
			//'status'  			 => array('title'=>_STATUS, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>(($status_trigger >= '3') ? true : false), 'source'=>$arr_statuses_edit),
		);

		// CUSTOMER_DETAILS
		$this->arrEditModeFields['separator_1'] = array(
			'separator_info' => array('legend'=>_CUSTOMER_DETAILS),
			'customer_id'        => array('title'=>'', 'type'=>'hidden', 'required'=>false, 'default'=>''),
			'customer_name'   	 => array('title'=>_CUSTOMER, 'type'=>'label'),			
		);

		// PAYMENT_INFO
		$this->arrEditModeFields['separator_2'] = array(
			'separator_info' => array('legend'=>_PAYMENT_INFO),
			'payment_date_formated' => array('title'=>_DATE_PAYMENT, 'type'=>'label'),
			'payment_type'       => array('title'=>_PAYMENT_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>true, 'default'=>'', 'source'=>$this->arr_payment_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
			'payment_method'     => array('title'=>_PAYMENT_METHOD, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>true, 'default'=>'', 'source'=>$this->arr_payment_methods, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
			'transaction_number' => array('title'=>_TRANSACTION.' #', 'type'=>'label'),
		);
		
		if(empty($this->user_id)){
			if($payment_type == '1'){ // on-line orders only!
				$this->arrEditModeFields['separator_2']['cc_type'] 			= array('title'=>_CREDIT_CARD_TYPE, 'type'=>'label');
				$this->arrEditModeFields['separator_2']['cc_holder_name'] 	= array('title'=>_CREDIT_CARD_HOLDER_NAME, 'type'=>'label');
				$this->arrEditModeFields['separator_2']['m_cc_number'] 		= array('title'=>_CREDIT_CARD_NUMBER, 'type'=>'label', 'post_html'=>(($cc_number_trigger == 'show') ? '&nbsp;[ <a href="javascript:void(0);" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) __mgDoPostBack(\''.$this->tableName.'\', \'clean_credit_card\', \''.$rid.'\')">'._REMOVE.'</a> ]' : ''));
				$this->arrEditModeFields['separator_2']['m_cc_expires_date'] = array('title'=>_CREDIT_CARD_EXPIRES, 'type'=>'label');
				$this->arrEditModeFields['separator_2']['m_cc_cvv_code'] 	= array('title'=>_CVV_CODE, 'type'=>'label');
			}
		};
		
		$this->arrEditModeFields['separator_2']['mod_initial_fee']    = array('title'=>_INITIAL_FEE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2');
		$this->arrEditModeFields['separator_2']['mod_guest_tax']      = array('title'=>_GUEST_TAX, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2');
		$this->arrEditModeFields['separator_2']['mod_vat_fee']        = array('title'=>_VAT, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'post_html'=>$vat_percent);
		$this->arrEditModeFields['separator_2']['mod_payment_sum']    = array('title'=>_PAYMENT_SUM, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2');
		$this->arrEditModeFields['separator_2']['mod_pre_payment']    = array('title'=>_PRE_PAYMENT, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2');
		$this->arrEditModeFields['separator_2']['additional_payment'] = array('title'=>_ADDITIONAL_PAYMENT, 'header_tooltip'=>(in_array($status_trigger, array('1','2','3')) ? _ADDITIONAL_PAYMENT_TOOLTIP : ''), 'type'=>(in_array($status_trigger, array('1','2','3')) ? 'textbox' : 'label'),  'width'=>'100px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'', 'validation_type'=>'float', 'validation_maximum'=>'10000000', 'unique'=>false, 'visible'=>true, 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2');
		$this->arrEditModeFields['separator_2']['mod_have_to_pay']    = array('title'=>_PAYMENT_REQUIRED, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2');
		$this->arrEditModeFields['separator_2']['coupon_code']        = array('title'=>_COUPON_CODE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'');
		
		
		// ADDITIONAL_INFO
		$this->arrEditModeFields['separator_3'] = array(
			'separator_info' => array('legend'=>_ADDITIONAL_INFO),
		);
		
		if($this->user_id != ''){
			$this->arrEditModeFields['separator_3']['status'] = array('title'=>_STATUS, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>(($status_trigger >= '3') ? true : false), 'source'=>$arr_statuses_edit);
			$this->arrEditModeFields['separator_3']['status_changed_formated'] = array('title'=>_STATUS_CHANGED, 'type'=>'label');
			$this->arrEditModeFields['separator_3']['cancel_payment_date_formated_till'] = array('title'=>_CANCELLATION_POLICY, 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'type'=>'label', 'pre_html'=>_TILL.' ', 'post_html'=>' - <strong>'._FREE_OF_CHARGE.'</strong>');
			$this->arrEditModeFields['separator_3']['cancel_payment_date_formated_after'] = array('title'=>'', 'header_tooltip'=>'', 'type'=>'label', 'pre_html'=>_AFTER.' ', 'post_html'=>' - <strong>'.Currencies::PriceFormat($cancellation_fee).'</strong>');
		}else{			
			$this->arrEditModeFields['separator_3']['status'] = array('title'=>_STATUS, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'source'=>(($status_trigger >= '3') ? $arr_statuses_edit_completed : $arr_statuses_edit));
			$this->arrEditModeFields['separator_3']['status_changed_formated'] = array('title'=>_STATUS_CHANGED, 'type'=>'label');
			$this->arrEditModeFields['separator_3']['status_description'] = array('title'=>_STATUS.' ('._DESCRIPTION.')', 'type'=>'label');
			$this->arrEditModeFields['separator_3']['cancel_payment_date_formated_till'] = array('title'=>_CANCELLATION_POLICY, 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'type'=>'label', 'pre_html'=>_TILL.' ', 'post_html'=>' - ' . _FREE_OF_CHARGE);
			$this->arrEditModeFields['separator_3']['cancel_payment_date_formated_after'] = array('title'=>'', 'header_tooltip'=>'', 'type'=>'label', 'pre_html'=>_AFTER.' ', 'post_html'=>' - <strong>'.Currencies::PriceFormat($cancellation_fee).'</strong>');
			$this->arrEditModeFields['separator_3']['affiliate_name'] = array('title'=>_AFFILIATE, 'type'=>'label');
			$this->arrEditModeFields['separator_3']['additional_info'] = array('title'=>_ADDITIONAL_INFO, 'type'=>'textarea', 'width'=>'390px', 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'validation_maxlength'=>'1024', 'unique'=>false);
			$this->arrEditModeFields['separator_3']['additional_info_email'] = array('title'=>_ADDITIONAL_INFO_EMAIL, 'header_tooltip'=>_ADDITIONAL_INFO_EMAIL_TOOLTIP, 'type'=>'textarea', 'width'=>'390px', 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'validation_maxlength'=>'1024', 'unique'=>false);
		}

		
		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------		
		$this->DETAILS_MODE_SQL = $this->VIEW_MODE_SQL.$WHERE_CLAUSE;

		$this->arrDetailsModeFields = array(

			'booking_number'  	 => array('title'=>_BOOKING_NUMBER, 'type'=>'label'),
			'booking_description'  => array('title'=>_DESCRIPTION, 'type'=>'label'),
			'order_price'  		 => array('title'=>_ORDER_PRICE, 'type'=>'label'),
			'vat_fee'  		     => array('title'=>_VAT, 'type'=>'label'),
			'vat_percent'  		 => array('title'=>_VAT_PERCENT, 'type'=>'label'),
			'payment_sum'  		 => array('title'=>_PAYMENT_SUM, 'type'=>'label'),
			'currency'  		 => array('title'=>_CURRENCY, 'type'=>'label'),
			'customer_name'      => array('title'=>_CUSTOMER, 'type'=>'label'),
			'transaction_number' => array('title'=>_TRANSACTION, 'type'=>'label'),
			'payment_date_formated' => array('title'=>_DATE, 'type'=>'label'),
			'payment_type'       => array('title'=>_PAYMENT_TYPE, 'type'=>'enum', 'source'=>$this->arr_payment_types),
			'payment_method'     => array('title'=>_PAYMENT_METHOD, 'type'=>'enum', 'source'=>$this->arr_payment_methods),
			'coupon_code'  	 	 => array('title'=>'', 'type'=>'label'),
			//'discount_campaign_id' => array('title'=>'', 'type'=>'label'),
			'status'  	         => array('title'=>_STATUS, 'type'=>'label'),
			'additional_info'    => array('title'=>_ADDITIONAL_INFO, 'type'=>'label'),
		);
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }
	
	/**
	 * Cancel record
	 */
	public function CancelRecord($rid)
	{
		global $objLogin;
		
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		if($objLogin->IsLoggedInAsAdmin() && !$objLogin->HasPrivileges('cancel_bookings')){
			return false;
		}
		
		$sql = 'SELECT b.id, b.booking_number
				FROM '.TABLE_BOOKINGS.' b
				WHERE b.'.$this->primaryKey.' = \''.$rid.'\'';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		if(!empty($result['booking_number'])){
			self::UpdateRoomsAvailability($result['booking_number'], 'increase', 'Canceled');
		}
		
		$sql = 'UPDATE '.$this->tableName.'
				SET
					status = 6,
					status_changed = \''.date('Y-m-d H:i:s').'\',
					status_description = \''.encode_text(($objLogin->IsLoggedInAsAdmin() ? _CANCELED_BY_ADMIN : _CANCELED_BY_CUSTOMER)).'\'
				WHERE '.$this->primaryKey.' = '.(int)$rid;
		if(!database_void_query($sql)) return false;
		
		// Check if this is "by balance" payment and return money back to account balance 
		$this->ReturnMoneyToBalance($rid, true, true);
		
		return true; 
	}	
	
	/**
	 * Draws order invoice
	 */
	public function DrawBookingInvoice($rid, $text_only = false, $type = 'html', $draw = true)
	{
		global $objSiteDescription, $objSettings, $objLogin;
		
		$output = '';
		$oid = isset($rid) ? (int)$rid : '0';
		$language_id = Languages::GetDefaultLang();
		
		$sql = 'SELECT
					'.$this->tableName.'.'.$this->primaryKey.',
					'.$this->tableName.'.booking_number,
					'.$this->tableName.'.hotel_reservation_id,
					'.$this->tableName.'.booking_description,
					'.$this->tableName.'.additional_info,
					'.$this->tableName.'.discount_fee,
					'.$this->tableName.'.discount_type,
					'.$this->tableName.'.discount_value,
					'.$this->tableName.'.guests_discount,
					'.$this->tableName.'.nights_discount,
					'.$this->tableName.'.coupon_code, 
					'.$this->tableName.'.order_price,
					'.$this->tableName.'.vat_fee,
					'.$this->tableName.'.vat_percent,
					'.$this->tableName.'.initial_fee,
					'.$this->tableName.'.guest_tax,
					'.$this->tableName.'.payment_sum,
					'.$this->tableName.'.pre_payment_type,
					'.$this->tableName.'.pre_payment_value,
					'.$this->tableName.'.status,
					IF((('.$this->tableName.'.order_price - '.$this->tableName.'.discount_fee - '.$this->tableName.'.guests_discount - '.$this->tableName.'.nights_discount) + '.$this->tableName.'.initial_fee + '.$this->tableName.'.guest_tax + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment) > 0),
						(('.$this->tableName.'.order_price - '.$this->tableName.'.discount_fee - '.$this->tableName.'.guests_discount - '.$this->tableName.'.nights_discount) + '.$this->tableName.'.initial_fee + '.$this->tableName.'.guest_tax + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment)),
						0
					) as mod_have_to_pay,								
					CASE
						WHEN '.$this->tableName.'.pre_payment_type = \'first night\' THEN CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.pre_payment_value)
						WHEN '.$this->tableName.'.pre_payment_type = \'fixed sum\' THEN CONCAT("'.$this->default_currency_info['symbol'].'", '.$this->tableName.'.pre_payment_value)
						WHEN '.$this->tableName.'.pre_payment_type = \'percentage\' THEN CONCAT('.$this->tableName.'.pre_payment_value, "%")
						ELSE \'\'
					END as mod_pre_payment,					
					'.$this->tableName.'.additional_payment,
					'.$this->tableName.'.extras,
					'.$this->tableName.'.extras_fee,
					'.$this->tableName.'.cc_type,
					'.$this->tableName.'.cc_holder_name,
					'.$this->tableName.'.cc_expires_month,
					'.$this->tableName.'.cc_expires_year,
					'.$this->tableName.'.cc_cvv_code, 					
					'.$this->tableName.'.currency,
					'.$this->tableName.'.customer_id,
					'.$this->tableName.'.transaction_number,
					'.$this->tableName.'.is_admin_reservation,
					DATE_FORMAT('.$this->tableName.'.created_date, \''.$this->sqlFieldDatetimeFormat.'\') as created_date_formated,					
					DATE_FORMAT('.$this->tableName.'.payment_date, \''.$this->sqlFieldDatetimeFormat.'\') as payment_date_formated,					
					'.$this->tableName.'.payment_type,
					'.$this->tableName.'.payment_method,
					'.TABLE_CURRENCIES.'.symbol,
					'.TABLE_CURRENCIES.'.symbol_placement,
					CONCAT("<a href=\"index.php?'.$this->page.'&mg_action=description&oid=", '.$this->tableName.'.'.$this->primaryKey.', "\">", "'._DESCRIPTION.'", "</a>") as link_order_description,
					'.TABLE_CUSTOMERS.'.first_name,
					'.TABLE_CUSTOMERS.'.last_name,					
					'.TABLE_CUSTOMERS.'.email as customer_email,
					'.TABLE_CUSTOMERS.'.company as customer_company,
					'.TABLE_CUSTOMERS.'.b_address,
					'.TABLE_CUSTOMERS.'.b_address_2,
					'.TABLE_CUSTOMERS.'.b_city,
					'.TABLE_CUSTOMERS.'.b_state,
					'.TABLE_CUSTOMERS.'.b_zipcode,
					'.TABLE_CUSTOMERS.'.phone,
					'.TABLE_CUSTOMERS.'.fax, 
					'.TABLE_COUNTRIES_DESCRIPTION.'.name as country_name,
					'.TABLE_CAMPAIGNS.'.campaign_name,
					DATE_FORMAT('.$this->tableName.'.cancel_payment_date, \''.(($this->fieldDateFormat == 'M d, Y') ? '%b %d, %Y %h:%i %p' : '%d %b %Y %h:%i %p').'\') as cancel_payment_date_formated
				FROM '.$this->tableName.'
					LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
					LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' ON '.TABLE_CUSTOMERS.'.b_country = '.TABLE_COUNTRIES.'.abbrv AND '.TABLE_COUNTRIES.'.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' ON '.TABLE_COUNTRIES.'.id = '.TABLE_COUNTRIES_DESCRIPTION.'.country_id AND '.TABLE_COUNTRIES_DESCRIPTION.'.language_id = \''.$language_id.'\'
					LEFT OUTER JOIN '.TABLE_CAMPAIGNS.' ON '.$this->tableName.'.discount_campaign_id = '.TABLE_CAMPAIGNS.'.id
				WHERE
					'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$oid;

				if($this->user_id != ''){
					$sql .= ' AND '.$this->tableName.'.is_admin_reservation = 0 AND '.$this->tableName.'.customer_id = '.(int)$this->user_id;
				}
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
            $part =  '';
            if($type == 'pdf'){
                $part .= '<h1 style="text-align:center;width:100%;">'._INVOICE.'</h1>';
            }
			$part .= '<table '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').' width="100%" border="0" cellspacing="0" cellpadding="0">';
			if($text_only && ModulesSettings::Get('booking', 'mode') == 'TEST MODE'){
				$part .= '<tr><td colspan="2"><div style="text-align:center;padding:10px;color:#a60000;border:1px dashed #a60000;width:100px">TEST MODE!</div></td></tr>';
			}

            if($type == 'pdf'){
                $part .= '<tr><td>'._FOR_BOOKING.$result[0]['booking_number'].'</td><td style="text-align:right;">'._DATE_CREATED.': '.$result[0]['created_date_formated'].'</td></tr>
                          <tr><td colspan="2" nowrap="nowrap" height="10px"></td></tr>';
            }else{
                $part .= '<tr><td colspan="2">'._DATE_CREATED.': '.$result[0]['created_date_formated'].'</td></tr>
                          <tr><td colspan="2" nowrap="nowrap" height="10px"></td></tr>';
            }
			$part .= '<tr>
					<td valign="top">						
						<h3>'._CUSTOMER_DETAILS.':</h3>';
						if($result[0]['is_admin_reservation'] == '1'){							
							$objAdmin = new Accounts((int)$result[0]['customer_id']);
							$part .= _ADMIN_RESERVATION.'<br />';
							$part .= '('.$objAdmin->GetParameter('account_type').') - '.$objAdmin->GetParameter('last_name').' '.$objAdmin->GetParameter('first_name');
						}else{
							$part .= _FIRST_NAME.': '.$result[0]['first_name'].'<br />';         
							$part .= _LAST_NAME.': '.$result[0]['last_name'].'<br />';           
							$part .= _EMAIL_ADDRESS.': '.$result[0]['customer_email'].'<br />';  
							if($result[0]['customer_company'] != '') $part .= _COMPANY.': '.$result[0]['customer_company'].'<br />';
							if($result[0]['phone'] != '' || $result[0]['fax'] != ''){
								if($result[0]['phone'] != '') $part .= _PHONE.': '.$result[0]['phone'];
								if($result[0]['phone'] != '' && $result[0]['fax'] != '') $part .= ', ';
								if($result[0]['fax'] != '') $part .= _FAX.': '.$result[0]['fax'];
								$part .= '<br />';	
							} 
							if(!empty($result[0]['b_address']) || !empty($result[0]['b_address_2']))  $part .= _ADDRESS.': '.$result[0]['b_address'].' '.$result[0]['b_address_2'].'<br />';
							if(!empty($result[0]['b_city']) || !empty($result[0]['b_state']))         $part .= $result[0]['b_city'].' '.$result[0]['b_state'].'<br />';
							if(!empty($result[0]['country_name']) || !empty($result[0]['b_zipcode'])) $part .= $result[0]['country_name'].' '.$result[0]['b_zipcode'];
						}
			$part .= '</td>
					<td valign="top" align="'.Application::Get('defined_right').'">';
					
					$hotels_list = $this->GetBookingHotelsList($result[0]['booking_number']);
					if(count($hotels_list) == 1){
						$hotel_info = Hotels::GetHotelFullInfo($hotels_list[0], $language_id);
						$part .= '<h3>'.$hotel_info['name'].'</h3>';
						if(!empty($hotel_info['address'])) $part .= _ADDRESS.': '.$hotel_info['address'].'<br />';
						if(!empty($hotel_info['phone']))   $part .= _PHONE.': '.$hotel_info['phone'].'<br />';
						if(!empty($hotel_info['fax']))     $part .= _FAX.': '.$hotel_info['fax'].'<br />';
						if(!empty($hotel_info['email']))   $part .= _EMAIL_ADDRESS.': '.$hotel_info['email'].'<br />';
					}
					$part .= '</td>
				</tr>
				<tr><td colspan="2" nowrap="nowrap" height="10px"></td></tr>
				<tr>
					<td colspan="2">';						
						$part .= '<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border:1px solid #d1d2d3;margin-bottom:20px;">';
						$part .= '<tr style="background-color:#e1e2e3;font-weight:bold;font-size:13px;"><th align="left" colspan="2">&nbsp;<b>'._BOOKING_DETAILS.'</b></th></tr>';
						$part .= '<tr><td width="25%">&nbsp;'._STATUS.': </td><td><b>'.strip_tags($this->statuses_vm[$result[0]['status']]).'</b></td></tr>';
						$part .= '<tr><td width="25%">&nbsp;'._BOOKING_NUMBER.': </td><td>'.$result[0]['booking_number'].'</td></tr>';
						if($objLogin->IsLoggedInAsAdmin() && $result[0]['hotel_reservation_id'] != '') $part .= '<tr><td>&nbsp;'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT_RESERVATION_ID : _HOTEL_RESERVATION_ID).': </td><td>'.$result[0]['hotel_reservation_id'].'</td></tr>';
						$part .= '<tr><td>&nbsp;'._DESCRIPTION.': </td><td>'.$result[0]['booking_description'].'</td></tr>';
						$part .= '</table><br />';									
						
						$part .= '<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border:1px solid #d1d2d3;margin-bottom:20px;">';
						$part .= '<tr style="background-color:#e1e2e3;font-weight:bold;font-size:13px;"><th align="left" colspan="2">&nbsp;<b>'._PAYMENT_DETAILS.'</b></th></tr>';
						$part .= '<tr><td width="25%">&nbsp;'._DATE_PAYMENT.': </td><td>'.$result[0]['payment_date_formated'].'</td></tr>';
						$part .= '<tr><td>&nbsp;'._PAYMENT_TYPE.': </td><td>'.$this->arr_payment_types[$result[0]['payment_type']].'</td></tr>';
						$part .= '<tr><td>&nbsp;'._PAYMENT_METHOD.': </td><td>';
							if($result[0]['payment_type'] == 1 && $this->online_credit_card_required == 'yes'){
								$part .= _CREDIT_CARD;
							}else{
								$part .= $this->arr_payment_methods[$result[0]['payment_method']];
							}
						$part .= '</td></tr>';
						$part .= '<tr><td>&nbsp;'._TRANSACTION.' #: </td><td>'.$result[0]['transaction_number'].'</td></tr>';
						$part .= '<tr><td>&nbsp;'._BOOKING_PRICE.': </td><td>'.Currencies::PriceFormat($result[0]['order_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						$part .= (($result[0]['guests_discount'] > 0) ? '<tr><td>&nbsp;'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).'</td><td>- '.Currencies::PriceFormat($result[0]['guests_discount'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>' : '');
						$part .= (($result[0]['nights_discount'] > 0) ? '<tr><td>&nbsp;'._LONG_TERM_STAY_DISCOUNT.'</td><td>- '.Currencies::PriceFormat($result[0]['nights_discount'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>' : '');
						if($result[0]['campaign_name'] != '') $part .= '<tr><td>&nbsp;'._DISCOUNT.': </td><td>- '.Currencies::PriceFormat($result[0]['discount_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.($result[0]['discount_type'] == '1' ? Currencies::PriceFormat($result[0]['discount_value'], '%', 'after', $this->currencyFormat) : Currencies::PriceFormat($result[0]['discount_value'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat)).' - '.$result[0]['campaign_name'].')</td></tr>';
						else if($result[0]['coupon_code'] != '') $part .= '<tr><td>&nbsp;'._COUPON_DISCOUNT.': </td><td>- '.Currencies::PriceFormat($result[0]['discount_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.($result[0]['discount_type'] == '1' ? Currencies::PriceFormat($result[0]['discount_value'], '%', 'after', $this->currencyFormat) : Currencies::PriceFormat($result[0]['discount_value'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat)).' - '._COUPON_CODE.': '.$result[0]['coupon_code'].')</td></tr>';
						$part .= '<tr><td>&nbsp;'._BOOKING_SUBTOTAL.(($result[0]['campaign_name'] != '') ? ' ('._AFTER_DISCOUNT.')' : '').': </td><td>'.Currencies::PriceFormat($result[0]['order_price']-$result[0]['discount_fee']-$result[0]['guests_discount']-$result[0]['nights_discount'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						if(!empty($result[0]['extras'])) $part .= '<tr><td>&nbsp;'._EXTRAS_SUBTOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['extras_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						if(!empty($result[0]['initial_fee'])) $part .= '<tr><td>&nbsp;'._INITIAL_FEE.': </td><td>'.Currencies::PriceFormat($result[0]['initial_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						if(!empty($result[0]['guest_tax'])) $part .= '<tr><td>&nbsp;'._GUEST_TAX.': </td><td>'.Currencies::PriceFormat($result[0]['guest_tax'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						if($this->vat_included_in_price == 'no') $part .= '<tr><td>&nbsp;'._VAT.': </td><td>'.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).')</td></tr>';
						$part .= '<tr><td>&nbsp;'._PAYMENT_SUM.': </td><td>'.Currencies::PriceFormat($result[0]['payment_sum'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						if($result[0]['pre_payment_type'] == 'first night'){
							$part .= '<tr><td>&nbsp;'._PRE_PAYMENT.'</td><td>'.Currencies::PriceFormat($result[0]['payment_sum'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('._FIRST_NIGHT.')</td></tr>';
						}elseif($result[0]['pre_payment_type'] == 'percentage' && $result[0]['pre_payment_value'] > 0 && $result[0]['pre_payment_value'] < 100){
							$part .= '<tr><td>&nbsp;'._PRE_PAYMENT.'</td><td>'.Currencies::PriceFormat($result[0]['payment_sum'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.$result[0]['pre_payment_value'].'%)</td></tr>';
						}elseif($result[0]['pre_payment_type'] == 'fixed sum' && $result[0]['pre_payment_value'] > 0){
							$part .= '<tr><td>&nbsp;'._PRE_PAYMENT.'</td><td>'.Currencies::PriceFormat($result[0]['payment_sum'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['pre_payment_value'], $result[0]['symbol'], $result[0]['symbol_placement']).')</td></tr>';
						}else{
							$part .= '<tr><td>&nbsp;'._PRE_PAYMENT.'</td><td>'._FULL_PRICE.'</td></tr>';
						}
						$part .= '<tr><td>&nbsp;'._ADDITIONAL_PAYMENT.': </td><td>'.Currencies::PriceFormat($result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						$part .= '<tr><td>&nbsp;'._TOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['payment_sum'] + $result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						if($result[0]['mod_have_to_pay'] != 0){
							$part .= '<tr><td style="color:#960000">&nbsp;'._PAYMENT_REQUIRED.': </td><td style="color:#960000">'.Currencies::PriceFormat($result[0]['mod_have_to_pay'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
						}			
						$part .= '</table><br />';
                        if(!empty($result[0]['additional_info'])){
                            $part .= '<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border:1px solid #d1d2d3;margin-bottom:20px;">';
                            $part .= '<tr style="background-color:#e1e2e3;font-weight:bold;font-size:13px;"><th align="left">&nbsp;<b>'._ADDITIONAL_INFO.'</b></th></tr>';
                            $part .= '<tr><td>&nbsp;'.$result[0]['additional_info'].'</td></tr>';
                            $part .= '</table><br />';									
                        }
						
				$part .= '</td>';
				$part .= '</tr>';
				$part .= '</table>';
				
				$cancellation_fee = self::CalculateCancellationFee($result[0]['booking_number']);
				$cancellation_date = $result[0]['cancel_payment_date_formated'];
				$cancellation_policy = '<br /><h4 style="color:#960000">'._CANCELLATION_POLICY.':</h4>
					'._TILL.' '.$cancellation_date.' - <strong>'._FREE_OF_CHARGE.'</strong><br>
					'.(!empty($cancellation_fee) ? _AFTER.' '.$cancellation_date.' - <strong>'.Currencies::PriceFormat($cancellation_fee).'</strong>' : '').'
				<br />';
						
			$content = @file_get_contents('html/templates/invoice.tpl');
			if($content){
				$content = str_replace('_TOP_PART_', $part, $content);
				$content = str_replace('_EXTRAS_LIST_', Extras::GetExtrasList(unserialize($result[0]['extras']), $result[0]['currency']), $content);
				$content = str_replace('_ROOMS_LIST_', $this->GetBookingRoomsList($oid, $language_id, 'details', false), $content);
				$content = str_replace('_CANCELLATION_POLICY_', $cancellation_policy, $content);
				$content = str_replace('_INVOICE_THANK_YOU_LINE_', _INVOICE_THANK_YOU_LINE, $content);
				$content = str_replace('_INVOICE_HAVE_QUESTIONS_LINE_', _INVOICE_HAVE_QUESTIONS_LINE, $content);
				$content = str_replace('_INVOICE_DISCLAIMER_LINE_', _INVOICE_DISCLAIMER_LINE, $content);
				$content = str_replace('_YOUR_COMPANY_NAME_', $objSiteDescription->GetParameter('header_text'), $content);
				$content = str_replace('_ALL_RIGHTS_RESERVED_', _ALL_RIGHTS_RESERVED, $content);
				$content = str_replace('_ADMIN_EMAIL_', $objSettings->GetParameter('admin_email'), $content);
			}
			$output .= '<div id="divInvoiceContent">'.$content.'</div>';
		}
		
		if(!$text_only){
			$output .= '<table width="100%" border="0">';
			$output .= '<tr><td colspan="2">&nbsp;</tr>';
			$output .= '<tr>';
			$output .= '  <td colspan="2" align="left"><input type="button" class="mgrid_button" name="btnBack" value="'._BUTTON_BACK.'" onclick="javascript:appGoTo(\''.$this->page.'\');"></td>';
			$output .= '</tr>';			
			$output .= '</table>';
		}
		
		if($draw){
			echo $output;
		}else{
			return $output;
		}
	}
	
	/**
	 * Send invoice to customer
	 * @param $rid
	 */
	public function SendInvoice($rid)
	{
		global $objSettings;
		
		if(strtolower(SITE_MODE) == "demo"){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}
		
		$sql = 'SELECT
					b.booking_number,
					IF(is_admin_reservation = 1, a.email, c.email) as email,
					IF(is_admin_reservation = 1, a.preferred_language, c.preferred_language) as preferred_language
				FROM '.TABLE_BOOKINGS.' b
					LEFT OUTER JOIN '.TABLE_CUSTOMERS.' c ON b.customer_id = c.id
					LEFT OUTER JOIN '.TABLE_ACCOUNTS.' a ON b.customer_id = a.id
				WHERE b.id = '.(int)$rid;		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			
			$recipient = $result[0]['email'];
			$sender    = $objSettings->GetParameter('admin_email');
			$subject   = _INVOICE.' ('._FOR_BOOKING.$result[0]['booking_number'].')';
			$body      = $this->DrawBookingInvoice($rid, true, 'html', false);
			$preferred_language = $result[0]['preferred_language'];
			//$body      = str_replace('<br />', '', $body);
			
			send_email_wo_template(
				$recipient,
				$sender,
				$subject,
				$body,
				$preferred_language
			);
			
			return true;
		}
		
		$this->error = _EMAILS_SENT_ERROR;
		return false;		
	}

	/**
	 * Draws Booking Description
	 * @param $rid
	 * @param $mode
	 * @param $draw
	 * @param $draw_swipe
	 */
	public function DrawBookingDescription($rid, $mode = '', $draw = true, $draw_swipe = false)
	{
		global $objLogin;
		
		$output = '';
		$content = '';
		$oid = isset($rid) ? (int)$rid : '0';
		$language_id = Application::Get('lang');

		$sql = 'SELECT
				'.$this->tableName.'.'.$this->primaryKey.',
				'.$this->tableName.'.booking_number,
				'.$this->tableName.'.hotel_reservation_id,
				'.$this->tableName.'.booking_description,
				'.$this->tableName.'.additional_info,
				'.$this->tableName.'.discount_fee,
				'.$this->tableName.'.discount_type,
				'.$this->tableName.'.discount_value,
				'.$this->tableName.'.guests_discount,
				'.$this->tableName.'.nights_discount,
				'.$this->tableName.'.order_price,
				'.$this->tableName.'.vat_fee,
				'.$this->tableName.'.vat_percent,
				'.$this->tableName.'.initial_fee,
				'.$this->tableName.'.guest_tax,
				'.$this->tableName.'.payment_sum,
				'.$this->tableName.'.pre_payment_type,
				'.$this->tableName.'.pre_payment_value,
				'.$this->tableName.'.additional_payment,
				'.$this->tableName.'.extras,
				'.$this->tableName.'.extras_fee,
				'.$this->tableName.'.coupon_code,
				'.$this->tableName.'.currency,
				'.$this->tableName.'.cc_type,
				'.$this->tableName.'.cc_holder_name,
				IF((('.$this->tableName.'.order_price - '.$this->tableName.'.discount_fee - '.$this->tableName.'.guests_discount - '.$this->tableName.'.nights_discount) + '.$this->tableName.'.initial_fee + '.$this->tableName.'.guest_tax + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment) > 0),
					(('.$this->tableName.'.order_price - '.$this->tableName.'.discount_fee - '.$this->tableName.'.guests_discount - '.$this->tableName.'.nights_discount) + '.$this->tableName.'.initial_fee + '.$this->tableName.'.guest_tax + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.payment_sum + '.$this->tableName.'.additional_payment)),
					0
				) as mod_have_to_pay,								
				CASE
					WHEN LENGTH(AES_DECRYPT('.$this->tableName.'.cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\')) = 4
						THEN CONCAT(\'...\', AES_DECRYPT('.$this->tableName.'.cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\'))
					ELSE AES_DECRYPT('.$this->tableName.'.cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\')
				END as cc_number,
				CONCAT(\'...\', SUBSTRING(AES_DECRYPT(cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\'), -4)) as cc_number_for_customer,
				CASE
					WHEN LENGTH(AES_DECRYPT('.$this->tableName.'.cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\')) = 4
						THEN \' ('._CLEANED.')\'
					ELSE \'\'
				END as cc_number_cleaned,								
				'.$this->tableName.'.cc_expires_month,
				'.$this->tableName.'.cc_expires_year,
				AES_DECRYPT(cc_cvv_code, \''.PASSWORDS_ENCRYPT_KEY.'\') as cc_cvv_code, 
				'.$this->tableName.'.currency,
				'.$this->tableName.'.customer_id,
				'.$this->tableName.'.transaction_number,
				'.$this->tableName.'.payment_date, 
				DATE_FORMAT('.$this->tableName.'.created_date, \''.(($this->fieldDateFormat == 'M d, Y') ? '%b %d, %Y %h:%i %p' : '%d %b %Y %h:%i %p').'\') as created_date_formated,
				DATE_FORMAT('.$this->tableName.'.payment_date, \''.(($this->fieldDateFormat == 'M d, Y') ? '%b %d, %Y %h:%i %p' : '%d %b %Y %h:%i %p').'\') as payment_date_formated,
				'.$this->tableName.'.payment_type,
				'.$this->tableName.'.payment_method,
				'.$this->tableName.'.cancel_payment_date,
				DATE_FORMAT('.$this->tableName.'.cancel_payment_date, \''.(($this->fieldDateFormat == 'M d, Y') ? '%b %d, %Y %h:%i %p' : '%d %b %Y %h:%i %p').'\') as cancel_payment_date_formated,
				IF('.$this->tableName.'.status > 6, -1, '.$this->tableName.'.status) as status,
				DATE_FORMAT('.$this->tableName.'.status_changed, "'.$this->sqlFieldDatetimeFormat.'") as status_changed_formated,
				CASE
					WHEN '.$this->tableName.'.is_admin_reservation = 0 THEN
						CASE							
							WHEN '.TABLE_CUSTOMERS.'.user_name != \'\' THEN CONCAT(IF('.TABLE_CUSTOMERS.'.customer_type = 1, '.TABLE_CUSTOMERS.'.company, '.TABLE_CUSTOMERS.'.user_name), " (", '.TABLE_CUSTOMERS.'.last_name, " ", '.TABLE_CUSTOMERS.'.first_name, ")")
							ELSE \'without_account\'
						END
					ELSE \'admin\'
				END as customer_name,
                '.TABLE_CUSTOMERS.'.last_name as customer_last_name,
                '.TABLE_CUSTOMERS.'.first_name as customer_first_name,
				'.TABLE_CURRENCIES.'.symbol,
				'.TABLE_CURRENCIES.'.symbol_placement,
				CONCAT("<a href=\"index.php?'.$this->page.'&mg_action=description&oid=", '.$this->tableName.'.'.$this->primaryKey.', "\">", "'._DESCRIPTION.'", "</a>") as link_order_description,
				'.TABLE_CAMPAIGNS.'.campaign_name
			FROM '.$this->tableName.'
				LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
				LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
				LEFT OUTER JOIN '.TABLE_CAMPAIGNS.' ON '.$this->tableName.'.discount_campaign_id = '.TABLE_CAMPAIGNS.'.id
			WHERE
				'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$oid;
				
			if($this->user_id != ''){
				$sql .= ' AND '.$this->tableName.'.is_admin_reservation = 0 AND '.$this->tableName.'.customer_id = '.(int)$this->user_id;
			}
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$content .= '<table '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').' width="100%" border="0">';
			$content .= '<tr><td width="210px">'._BOOKING_NUMBER.': </td><td>'.$result[0]['booking_number'].'</td></tr>';
			if($objLogin->IsLoggedInAsAdmin()) $content .= '<tr><td>'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT_RESERVATION_ID : _HOTEL_RESERVATION_ID).': </td><td>'.$result[0]['hotel_reservation_id'].'</td></tr>';
			$content .= '<tr><td>'._DESCRIPTION.': </td><td>'.$result[0]['booking_description'].'</td></tr>';
			$content .= '<tr><td>'._DATE_CREATED.': </td><td>'.$result[0]['created_date_formated'].'</td></tr>';
			$content .= '<tr><td>'._DATE_PAYMENT.': </td><td>'.($result[0]['payment_date_formated'] ? $result[0]['payment_date_formated'] : '--').'</td></tr>';
			if($this->user_id == ''){
				$content .= '<tr><td>'._CUSTOMER.': </td><td>';
				if($result[0]['customer_name'] == 'without_account'){
					$content .= $result[0]['customer_last_name'].' '.$result[0]['customer_first_name'].' ('._WITHOUT_ACCOUNT.')';
				}elseif($result[0]['customer_name'] == 'admin'){
					$objAdmin = new Accounts((int)$result[0]['customer_id']);
					$content .= _ADMIN.' ('.$objAdmin->GetParameter('account_type').') - '.$objAdmin->GetParameter('last_name').' '.$objAdmin->GetParameter('first_name');
				}else{
					$content .= $result[0]['customer_name'];
				}
				$content .= '</td></tr>';
			}
			$content .= '<tr><td>'._PAYMENT_TYPE.': </td><td>'.$this->arr_payment_types[$result[0]['payment_type']].'</td></tr>';
			$content .= '<tr><td>'._PAYMENT_METHOD.': </td><td>';
				if($result[0]['payment_type'] == 1 && $this->online_credit_card_required == 'yes'){
					$content .= _CREDIT_CARD;
				}else{
					$content .= $this->arr_payment_methods[$result[0]['payment_method']];
				}				
			$content .= '</td></tr>';
			$content .= '<tr><td>'._TRANSACTION.' #: </td><td>'.$result[0]['transaction_number'].'</td></tr>';
			
			if($result[0]['payment_type'] == '1' && empty($mode)){
				// always show cc info, even if collecting is not requieed
				// $this->collect_credit_card == 'yes'
				$content .= '<tr><td>'._CREDIT_CARD_TYPE.': </td><td>'.$result[0]['cc_type'].'</td></tr>';
				$content .= '<tr><td>'._CREDIT_CARD_HOLDER_NAME.': </td><td>'.$result[0]['cc_holder_name'].'</td></tr>';
				if($this->user_id == ''){
					$content .= '<tr><td>'._CREDIT_CARD_NUMBER.': </td><td>'.$result[0]['cc_number'].$result[0]['cc_number_cleaned'].'</td></tr>';
					$content .= '<tr><td>'._CREDIT_CARD_EXPIRES.': </td><td>'.(($result[0]['cc_expires_month'] != '') ? $result[0]['cc_expires_month'].'/'.$result[0]['cc_expires_year'] : '').'</td></tr>';
					$content .= '<tr><td>'._CVV_CODE.': </td><td>'.$result[0]['cc_cvv_code'].'</td></tr>';				
				}else{
					$content .= '<tr><td>'._CREDIT_CARD_NUMBER.': </td><td>'.$result[0]['cc_number_for_customer'].'</td></tr>';
				}
			}

			$content .= '<tr><td>'._BOOKING_PRICE.': </td><td>'.Currencies::PriceFormat($result[0]['order_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
			
			$content .= (($result[0]['nights_discount'] > 0) ? '<tr><td>'._LONG_TERM_STAY_DISCOUNT.'</td><td>- '.Currencies::PriceFormat($result[0]['nights_discount'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>' : '');
			$content .= (($result[0]['guests_discount'] > 0) ? '<tr><td>'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).'</td><td>- '.Currencies::PriceFormat($result[0]['guests_discount'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>' : '');
			
			if($result[0]['campaign_name'] != '') $content .= '<tr><td>'._DISCOUNT.': </td><td>- '.Currencies::PriceFormat($result[0]['discount_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.($result[0]['discount_type'] == '1' ? Currencies::PriceFormat($result[0]['discount_value'], '%', 'right', $this->currencyFormat) : Currencies::PriceFormat($result[0]['discount_value'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat)).' - '.$result[0]['campaign_name'].')</td></tr>';
			else if($result[0]['coupon_code'] != '') $content .= '<tr><td>'._COUPON_DISCOUNT.': </td><td>- '.Currencies::PriceFormat($result[0]['discount_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.($result[0]['discount_type'] == '1' ? Currencies::PriceFormat($result[0]['discount_value'], '%', 'right', $this->currencyFormat) : Currencies::PriceFormat($result[0]['discount_value'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat)).' - '._COUPON_CODE.': '.$result[0]['coupon_code'].')</td></tr>';

			$content .= '<tr><td>'._BOOKING_SUBTOTAL.(($result[0]['campaign_name'] != '') ? ' ('._AFTER_DISCOUNT.')' : '').': </td><td>'.Currencies::PriceFormat($result[0]['order_price']-$result[0]['discount_fee']-$result[0]['guests_discount']-$result[0]['nights_discount'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';	

			if(!empty($result[0]['extras'])) $content .= '<tr><td>'._EXTRAS_SUBTOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['extras_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';			
			if(!empty($result[0]['initial_fee'])) $content .= '<tr><td>'._INITIAL_FEE.': </td><td>'.Currencies::PriceFormat($result[0]['initial_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
			if(!empty($result[0]['guest_tax'])) $content .= '<tr><td>'._GUEST_TAX.': </td><td>'.Currencies::PriceFormat($result[0]['guest_tax'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
			if($this->vat_included_in_price == 'no') $content .= '<tr><td>'._VAT.': </td><td>'.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'right', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).')</td></tr>';

			$order_price_plus_vat = Currencies::PriceFormat($result[0]['order_price'] - $result[0]['discount_fee'] - $result[0]['guests_discount'] - $result[0]['nights_discount']+ $result[0]['extras_fee'] + $result[0]['initial_fee'] + $result[0]['guest_tax'] + $result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
			$payment_sum = Currencies::PriceFormat($result[0]['payment_sum'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
			$payment_sum_plus_additional = Currencies::PriceFormat($result[0]['payment_sum'] + $result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
			$have_to_pay = Currencies::PriceFormat($result[0]['order_price'] - $result[0]['discount_fee'] - $result[0]['guests_discount'] - $result[0]['nights_discount'] + $result[0]['extras_fee'] + $result[0]['initial_fee'] + $result[0]['guest_tax'] + $result[0]['vat_fee'] - ($result[0]['payment_sum'] + $result[0]['additional_payment']), $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
			$additional_payment = Currencies::PriceFormat($result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
            $link_to_payment = '&nbsp;&nbsp;&nbsp; <a href="index.php?page=booking_prepayment&bn='.$result[0]['booking_number'].'" style="text-decoration:none;">'._CLICK_TO_PAY.' <img src="images/ppc_icons/logo_paypal.gif" alt="paypal" style="margin-top:-2px;margin-left:5px;"></a>';

			if($result[0]['pre_payment_type'] == 'first night'){
				$content .= '<tr><td>'._PAYMENT_SUM.': </td><td>'.$order_price_plus_vat.'</td></tr>';
				$content .= '<tr><td>'._PRE_PAYMENT.': </td><td>'.$payment_sum.' ('._PARTIAL_PRICE.' - '._FIRST_NIGHT.')</td></tr>';
				if($result[0]['additional_payment'] != 0) $content .= '<tr><td>'._ADDITIONAL_PAYMENT.': </td><td>'.$additional_payment.'</td></tr>';
				if($have_to_pay > 0) $content .= '<tr><td style="color:#a60000">'._PAYMENT_REQUIRED.': </td><td style="color:#a60000">'.$have_to_pay.' '.$link_to_payment.'</td></tr>';						
			}elseif($result[0]['pre_payment_type'] == 'percentage' && $result[0]['pre_payment_value'] > 0 && $result[0]['pre_payment_value'] < 100){
				$content .= '<tr><td>'._PAYMENT_SUM.': </td><td>'.$order_price_plus_vat.'</td></tr>';
				$content .= '<tr><td>'._PRE_PAYMENT.': </td><td>'.$payment_sum.' ('._PARTIAL_PRICE.' - '.$result[0]['pre_payment_value'].'%)</td></tr>';
				if($result[0]['additional_payment'] != 0) $content .= '<tr><td>'._ADDITIONAL_PAYMENT.': </td><td>'.$additional_payment.'</td></tr>';
				if($have_to_pay > 0) $content .= '<tr><td style="color:#a60000">'._PAYMENT_REQUIRED.': </td><td style="color:#a60000">'.$have_to_pay.' '.$link_to_payment.'</td></tr>';
				$content .= '<tr><td>'._TOTAL.': </td><td>'.$payment_sum_plus_additional.'</td></tr>';
			}elseif($result[0]['pre_payment_type'] == 'fixed sum' && $result[0]['pre_payment_value'] > 0){
				$content .= '<tr><td>'._PAYMENT_SUM.': </td><td>'.$order_price_plus_vat.'</td></tr>';
				$content .= '<tr><td>'._PRE_PAYMENT.': </td><td>'.$payment_sum.' ('._PARTIAL_PRICE.' - '.Currencies::PriceFormat($result[0]['pre_payment_value'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).')</td></tr>';
				if($result[0]['additional_payment'] != 0) $content .= '<tr><td>'._ADDITIONAL_PAYMENT.': </td><td>'.$additional_payment.'</td></tr>';
				if($have_to_pay > 0) $content .= '<tr><td style="color:#a60000">'._PAYMENT_REQUIRED.': </td><td style="color:#a60000">'.$have_to_pay.' '.$link_to_payment.'</td></tr>';
				$content .= '<tr><td>'._TOTAL.': </td><td>'.$payment_sum_plus_additional.'</td></tr>';
			}else{
				$content .= '<tr><td>'._PAYMENT_SUM.': </td><td>'.$payment_sum.'</td></tr>';
				$content .= '<tr><td>'._PRE_PAYMENT.': </td><td>'._FULL_PRICE.'</td></tr>';				
				if($result[0]['additional_payment'] != 0) $content .= '<tr><td>'._ADDITIONAL_PAYMENT.': </td><td>'.$additional_payment.'</td></tr>';
				$content .= '<tr><td>'._TOTAL.': </td><td>'.$payment_sum_plus_additional.'</td></tr>';
			}
			
			// Allow to pay required/additional sum if order is not canceled
			if($result[0]['mod_have_to_pay'] != 0 && $result[0]['status'] != 6){
				$content .= '<tr><td style="color:#960000">'._PAYMENT_REQUIRED.': </td><td style="color:#960000">'.Currencies::PriceFormat($result[0]['mod_have_to_pay'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' '.$link_to_payment.'</td></tr>';
			}			

			$content .= '<tr><td>'._STATUS.': </td><td>'.$this->statuses_vm[$result[0]['status']].'</td></tr>';
			$content .= '<tr><td>'._STATUS_CHANGED.': </td><td>'.$result[0]['status_changed_formated'].'</td></tr>';
			if($result[0]['additional_info'] != '') $content .= '<tr><td>'._ADDITIONAL_INFO.': </td><td>'.$result[0]['additional_info'].'</td></tr>';

			// Cancellation policy
			$cancellation_date_unix = strtotime($result[0]['cancel_payment_date']);
			if($cancellation_date_unix !== false){
				$cancellation_fee = self::CalculateCancellationFee($result[0]['booking_number']);
				$cancellation_date = date('M d, Y h:i A', $cancellation_date_unix);
				$currency_info = Currencies::GetCurrencyInfo($result[0]['currency']);
				$content .= '<tr>
					<td style="color:#960000">'._CANCELLATION_POLICY.': <img src="images/microgrid_icons/question.png" class="help" title="'._DAYS_TO_CANCEL_TOOLTIP.'" alt="tooltip"></td>
					<td tyle="color:#960000">
						'._TILL.' '.$cancellation_date.' - '._FREE_OF_CHARGE.'<br>
						'.(!empty($cancellation_fee) ? _AFTER.' '.$cancellation_date.' - <strong>'.Currencies::PriceFormat($cancellation_fee, $currency_info['symbol'], $currency_info['symbol_placement']).'</strong>' : '').'
					</td>
				</tr>';
			}

			$content .= '<tr><td colspan="2">&nbsp;</tr>';
			$content .= '</table>';
			
			// Find out the number of rooms belong to this order
			$hotel_ids = array();
			$sql = 'SELECT
					'.TABLE_BOOKINGS_ROOMS.'.hotel_id
				FROM '.TABLE_BOOKINGS_ROOMS.'
				WHERE
					'.TABLE_BOOKINGS_ROOMS.'.booking_number = \''.$result[0]['booking_number'].'\'
				GROUP BY '.TABLE_BOOKINGS_ROOMS.'.hotel_id';

			$hotel_count = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($hotel_count[1] != 1){
			}else{
				$hotel_ids = array(0, $hotel_count[0][0]['hotel_id']);
			}
			if($objLogin->IsLoggedInAs('owner','mainadmin','admin')){
				$content .= Extras::GetExtrasList(unserialize($result[0]['extras']), $result[0]['currency'], '', $hotel_ids, (($objLogin->IsLoggedInAsAdmin()) ? 'edit' : 'details'), $oid);
			}
		}else{
			$content .= draw_important_message(_WRONG_PARAMETER_PASSED, false);
		}

		$content .= $this->GetBookingRoomsList($oid, $language_id, ($objLogin->IsLoggedInAs('owner', 'mainadmin', 'admin') ? 'edit' : 'details'), $draw_swipe);

		$output .= '<div id="divDescriptionContent">'.$content.'</div>';
		if(empty($mode)){
			$output .= '<div>';
			$output .= '<br /><input type="button" class="mgrid_button mgrid_button_cancel" name="btnBack" value="'._BUTTON_BACK.'" onclick="javascript:appGoTo(\''.$this->page.'\');">';
			$output .= '</div>';			
		}
		
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws Booking Comment
	 * @param $rid
	 * @param $mode
	 * @param $draw
	 */
	public function DrawBookingComment($rid, $draw = true)
	{
		global $objLogin;
		
		$output = '';
		$content = '';
		$oid = isset($rid) ? (int)$rid : '0';
        $comment = MicroGrid::GetParameter('regional_menager_comment', false);
		$language_id = Application::Get('lang');

		$sql = 'SELECT
                '.$this->tableName.'.'.$this->primaryKey.',
                '.$this->tableName.'.booking_number,
				'.$this->tableName.'.regional_menager_comment
			FROM '.$this->tableName.'
				LEFT OUTER JOIN '.TABLE_BOOKINGS_ROOMS.' br ON '.$this->tableName.'.booking_number = br.booking_number
			WHERE
				'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$oid.$this->assigned_to_hotels;
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
            $output .= '<form name="frmBookingDescription" action="index.php?admin=mod_booking_bookings" method="post">';
            $output .= draw_hidden_field('mg_action', 'update_comment', false);
            $output .= draw_hidden_field('mg_rid', $oid, false);
            $output .= draw_token_field(false);
			$output .= '<table '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').' width="100%" border="0">';
            $output .= '<tr id="mg_row_booking_number_label"  style="color: rgb(0, 0, 0);">
                        <td width="25%" align="left" style="border-right: 1px dotted rgb(209, 209, 209);">
                            <label for="booking_number_label">'._BOOKING_NUMBER.'</label>:
                        </td>
                        <td style="text-align:left;padding-left:6px;">
                            <label class="mgrid_label mgrid_wrapword" title="">'.$result[0]['booking_number'].'</label>
                        </td>
                    </tr>';
            $output .= '<tr id="mg_row_additional_info" style="color: rgb(34, 34, 34);">
                        <td width="25%" align="left" style="border-right: 0px dotted rgb(204, 204, 204);">
                            <label for="additional_info">'._COMMENT_TEXT.'</label>:<br>'.str_replace('_MAX_CHARS_', '2048', _MAX_CHARS).'
                        </td>
                        <td style="text-align:left;padding-left:6px;">
                            <textarea class="mgrid_textarea" name="regional_menager_comment" id="regional_menager_comment" style="width:390px;height:90px;" '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').'" maxlength="2048">'.(!empty($comment) ? $comment : $result[0]['regional_menager_comment']).'</textarea>
                        </td>
                    </tr>';
            $output .= '<tr>
                        <td colspan="2" align="left">
                            <input class="mgrid_button" type="submit" name="subUpdateRecord" value="'._BUTTON_UPDATE.'">&nbsp;
                            <input class="mgrid_button mgrid_button_cancel" type="button" name="btnCancel" value="'._BUTTON_CANCEL.'" onclick="javascript:appGoTo(\'admin=mod_booking_bookings\');">
                        </td>
                    </tr>';
            $output .= '</table>';

        }	
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Before-editing record
	 */
	public function BeforeUpdateRecord()
	{ 
		if(isset($this->params['additional_info_email'])){
			unset($this->params['additional_info_email']);
		}

        if(RESTRICT_ADD_PAYMENT == true && !empty($this->params['additional_payment'])){
            $sql = 'SELECT
				IF((('.TABLE_BOOKINGS.'.order_price - '.TABLE_BOOKINGS.'.discount_fee - '.TABLE_BOOKINGS.'.guests_discount - '.TABLE_BOOKINGS.'.nights_discount) + '.TABLE_BOOKINGS.'.initial_fee + '.TABLE_BOOKINGS.'.guest_tax + '.TABLE_BOOKINGS.'.extras_fee + '.TABLE_BOOKINGS.'.vat_fee - ('.TABLE_BOOKINGS.'.payment_sum) > 0),
					(('.TABLE_BOOKINGS.'.order_price - '.TABLE_BOOKINGS.'.discount_fee - '.TABLE_BOOKINGS.'.guests_discount - '.TABLE_BOOKINGS.'.nights_discount) + '.TABLE_BOOKINGS.'.initial_fee + '.TABLE_BOOKINGS.'.guest_tax + '.TABLE_BOOKINGS.'.extras_fee + '.TABLE_BOOKINGS.'.vat_fee - ('.TABLE_BOOKINGS.'.payment_sum)),
					0
                ) as mod_have_to_pay
			FROM '.TABLE_BOOKINGS.'
			WHERE '.TABLE_BOOKINGS.'.id = '.(int)$this->curRecordId.$this->assigned_to_hotels;

            $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
            $total_prepayment = ($result[1] > 0) ? $result[0]['mod_have_to_pay'] : '0';
            if(($this->params['additional_payment'] - $total_prepayment) > 0.01){
                $this->error = str_replace(array('_FIELD_', '_SUM_'), array(_ADDITIONAL_PAYMENT, $total_prepayment), _MSG_MAXIMUM_PRICE);
                return false;
            }
        }
		
		$oid = MicroGrid::GetParameter('rid');
		$sql = 'SELECT
					booking_number,
					payment_type,
					status,
					status_changed,
					cancel_payment_date
				FROM '.TABLE_BOOKINGS.'
				WHERE id = '.(int)$oid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		
		if($result[1] > 0){
			$this->booking_number = $result[0]['booking_number'];
			$this->booking_status = $result[0]['status'];
			$this->status_changed = $result[0]['status_changed'];
			$this->payment_type = $result[0]['payment_type'];
		}

		return true;
	}

	/**
	 * After-Update record
	 */	
	public function AfterUpdateRecord()
	{
		$oid = MicroGrid::GetParameter('rid');
		$sql = 'SELECT
					booking_number,
					payment_type,
					status,
					cancel_payment_date
				FROM '.TABLE_BOOKINGS.'
				WHERE id = '.(int)$oid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		
		if($result[1] > 0){
			if($this->payment_type == '6' && !in_array($this->booking_status, array('4','5','6')) && $this->params['status'] >= 4){
                // Check if this is "by balance" payment and return money back to account balance 
                $this->ReturnMoneyToBalance($this->curRecordId, true, true);
			}
            if($this->payment_type == '6' && in_array($this->booking_status, array('4','5','6')) && $this->params['status'] < 4){
                // Check if this is "by balance" payment then take money from account balance 
				$status_changed_date_unix = strtotime($this->status_changed);
				$cancel_payment_date_unix = strtotime($result[0]['cancel_payment_date']);
				// Check the cancellation fee was refunded, and if according to write off the full amount,
				// or the amount excluding payment for cancellation
				if($status_changed_date_unix < $cancel_payment_date_unix){
					$this->ReturnMoneyToBalance($this->curRecordId, false, false);
				}else{
					$this->ReturnMoneyToBalance($this->curRecordId, false, true);
				}
			}
		}
		
		// Status changed from reserved or completed to something else
		$status = MicroGrid::GetParameter('status', false);
		if(in_array($this->booking_status, array('0','1','4','5','6')) && in_array($status, array('2', '3'))){
			self::UpdateRoomsAvailability($this->booking_number, 'decrease', 'Status Changed - '.$this->statuses[$status]);
		}elseif(in_array($this->booking_status, array('2', '3')) && in_array($status, array('0','1','4','5','6'))){
			self::UpdateRoomsAvailability($this->booking_number, 'increase', 'Status Changed - '.$this->statuses[$status]);
		}
	}

	/**
	 * Before-editing record
	 */
	public function BeforeEditRecord()
	{ 
        if(RESTRICT_ADD_PAYMENT == true){
			$sql = 'SELECT
						'.$this->tableName.'.* 
					FROM '.$this->tableName.'
					WHERE '.$this->tableName.'.'.$this->primaryKey.' = '.(int)$this->curRecordId.$this->assigned_to_hotels;
					
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0 && $result[0]['pre_payment_value'] < 0.01){
                $this->arrEditModeFields['separator_2']['additional_payment'] = array('title'=>_ADDITIONAL_PAYMENT, 'header_tooltip'=>'', 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2');
			}
        }
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			redirect_to($this->formActionURL);
		}
		
		return true;
	}

	/**
	 * Before-details record
	 */
	public function BeforeDetailsRecord()
	{
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			redirect_to($this->formActionURL);
		}
		
		return true;
	}

	/**
	 * Before-Delete record
	 */
    public function BeforeDeleteRecord()
	{
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			return false;
		}

		$oid = MicroGrid::GetParameter('rid');
		$sql = 'SELECT
					booking_number,
					rooms_amount,
					customer_id,
					is_admin_reservation,
					payment_type,
					status,
					cancel_payment_date,
					(payment_sum + additional_payment) as revert_sum					
				FROM '.TABLE_BOOKINGS.'
				WHERE id = '.(int)$oid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		
		if($result[1] > 0){
			$this->booking_number = $result[0]['booking_number'];
			$this->rooms_amount = $result[0]['rooms_amount'];
			$this->booking_status = $result[0]['status'];
			$this->booking_customer_id = $result[0]['customer_id'];
			$this->booking_is_admin_reserv = (int)$result[0]['is_admin_reservation'];
			
			$this->payment_type = $result[0]['payment_type'];
			$this->cancel_payment_date = $result[0]['cancel_payment_date'];
			$this->revert_sum = $result[0]['revert_sum'];
			$this->customer_id = $result[0]['customer_id'];
			if($this->payment_type == '6'){
				$this->cancellation_fee = self::CalculateCancellationFee($this->booking_number);
			}
			return true;
		}
		
		return false;
	}

	/**
	 * After-Delete record
	 */	
	public function AfterDeleteRecord()
	{
		global $objLogin;

		if(($this->booking_status == 2 || $this->booking_status == 3)){
			self::UpdateRoomsAvailability($this->booking_number, 'increase', 'After Deleting');	
		}

		$sql = 'DELETE FROM '.TABLE_BOOKINGS_ROOMS.' WHERE booking_number = \''.encode_text($this->booking_number).'\'';
		if($this->user_id != ''){
			$sql .= ' AND '.$this->tableName.'.is_admin_reservation = 0 AND '.$this->tableName.'.customer_id = '.(int)$this->user_id;
		}		
		if(!database_void_query($sql)){ /* echo 'error!'; */ }	 

		// update customer orders/rooms amount
		if($objLogin->IsLoggedIn() && ($this->booking_status > 0) && ($this->booking_is_admin_reserv == '0')){
			$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET
						orders_count = IF(orders_count > 0, orders_count - 1, orders_count),
						rooms_count = IF(rooms_count > 0, rooms_count - '.(int)$this->rooms_amount.', rooms_count)
					WHERE id = '.(int)$this->booking_customer_id;
			database_void_query($sql);
		}

		// Check if this is "by balance" payment and return money back to account balance
		if($this->payment_type == 6 && in_array($this->booking_status, array(2,3))){
			$cancellation_date_unix = strtotime($this->cancel_payment_date);
			if($cancellation_date_unix <= time()){
				$this->revert_sum -= $this->cancellation_fee;
			}
			$customer_type = Customers::GetCustomerInfo($this->customer_id, 'customer_type');
			
			// Check if this is "agency payment"
			$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET balance = balance + '.$this->revert_sum.' WHERE id = '.(int)$this->customer_id;
			database_void_query($sql);
		}
        
	}

	/**
	 * Trigger method - allows to work with View Mode items
	 */
	protected function OnItemCreated_ViewMode($field_name, &$field_value)
	{
		if($field_name == 'customer_name' && $field_value == '{administrator}'){
			$field_value = _ADMIN;
		}elseif($field_name == 'mod_hotel_types'){
			$arr_rooms = explode('<br>', $field_value);
			$arr_rooms_temp = array();
			$arr_rooms_temp2 = array();

			foreach($arr_rooms as $val){
				$arr_rooms_temp[$val] = array(
					'type'	=> isset($this->all_hotels[$val]) ? $this->all_hotels[$val] : _UNKNOWN,
					'count'	=> isset($arr_rooms_temp[$val]) ? $arr_rooms_temp[$val]['count']++ : 0
				);
			}
			
			foreach($arr_rooms_temp as $val){
				$arr_rooms_temp2[] = $val['type'].($val['count'] > 1 ? ' ('.$val['count'].')' : '');
			}
			$field_value = implode('<br>', $arr_rooms_temp2);
		}elseif($field_name == 'mod_room_types'){
			$arr_rooms = explode('<br>', $field_value);
			$arr_rooms_temp = array();
			$arr_rooms_temp2 = array();

			foreach($arr_rooms as $val){
				$arr_rooms_temp[$val] = array(
					'type'	=> isset($this->arr_room_types[$val]) ? $this->arr_room_types[$val] : _UNKNOWN,
					'count'	=> isset($arr_rooms_temp[$val]) ? $arr_rooms_temp[$val]['count']++ : 0
				);
			}
			
			foreach($arr_rooms_temp as $val){
				$arr_rooms_temp2[] = $val['type'].($val['count'] > 1 ? ' ('.$val['count'].')' : '');
			}
			$field_value = implode('<br>', $arr_rooms_temp2);
		}
    }
	
	/**
	 * Update Payment Date
	 * @param $rid
	 */
	public function UpdatePaymentDate($rid)
	{
		$sql = 'UPDATE '.$this->tableName.'
				SET payment_date = \''.date('Y-m-d H:i:s').'\'
				WHERE
					'.$this->primaryKey.' = '.(int)$rid.' AND 
					status = 3 AND
					(payment_date IS NULL OR payment_date)';
		database_void_query($sql);		
	}
	
	/**
	 * Cleans pending reservations
	 */
	public function CleanUpBookings()
	{
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		// delete 'tail' records in booking_rooms table
		$sql = 'DELETE
				FROM '.TABLE_BOOKINGS_ROOMS.'					
				WHERE booking_number NOT IN (SELECT booking_number FROM '.TABLE_BOOKINGS.')';
		database_void_query($sql);

		if($this->RemoveExpired()){
			return true;
		}else{
			$this->error = _NO_RECORDS_PROCESSED;
		}
	}
	
		
	/**
	 * Cleans credit card info
	 * @param $rid
	 */
	public function CleanUpCreditCardInfo($rid)
	{
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'UPDATE '.$this->tableName.'
				SET
					cc_number = AES_ENCRYPT(SUBSTRING(AES_DECRYPT(cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\'), -4), \''.PASSWORDS_ENCRYPT_KEY.'\'),
					cc_cvv_code = \'\',
					cc_expires_month = \'\',
					cc_expires_year = \'\'
				WHERE '.$this->primaryKey.' = '.(int)$rid;
		if(database_void_query($sql)){
			return true;
		}else{
			$this->error = _TRY_LATER;
		}		
	}
	
	/**
	 * Update room numbers for booking
	 * @param $rid
	 * @param $room_numbers
	 */
	public function UpdateRoomNumbers($rid, $room_numbers)
	{
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}
		
		$sql = 'UPDATE '.TABLE_BOOKINGS_ROOMS.'
				SET room_numbers = \''.encode_text($room_numbers).'\'
				WHERE '.$this->primaryKey.' = '.(int)$rid;
		return database_void_query($sql);		
	}
	
	/**
	 * Update room numbers for booking
	 * @param $rid
	 * @param $room_numbers
	 */
	public function UpdateComment($rid, $comment)
	{
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}elseif(strlen($comment) > 2048){
			$msg_text = str_replace('_FIELD_', '<b>'._OFFLINE_MESSAGE.'</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '2048', $msg_text);
            $this->error = $msg_text;
            return false;
        }
		
		$sql = 'UPDATE '.TABLE_BOOKINGS.'
				SET regional_menager_comment = \''.encode_text($comment).'\'
				WHERE '.$this->primaryKey.' = '.(int)$rid;
		return database_void_query($sql);		
	}
	
	/**
	 * Add extras for booking
	 * @param $rid
	 * @param $sel_extras
	 * @param $extras_amount
	 * @param $act
	 */
	public function RecalculateExtras($rid, $sel_extras, $extras_amount, $act = 'add')
	{
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'SELECT booking_number, extras, order_price,
		               vat_percent, vat_fee, pre_payment_type, pre_payment_value,
					   payment_sum, discount_campaign_id, discount_type, discount_value, discount_fee,
					   guests_discount, nights_discount, extras_fee, initial_fee, guest_tax, currency
		        FROM '.TABLE_BOOKINGS.' WHERE id = '.(int)$rid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$booking_number = $result[0]['booking_number'];			
			$pre_payment_type = $result[0]['pre_payment_type'];
			$pre_payment_value = $result[0]['pre_payment_value'];			
			$vat_percent = $result[0]['vat_percent'];
			$payment_sum = $result[0]['payment_sum'];
			$discount_fee = $result[0]['discount_fee'];
			$guests_discount= $result[0]['guests_discount'];
			$nights_discount= $result[0]['nights_discount'];
			$extras_fee = $result[0]['extras_fee'];
			$initial_fee = $result[0]['initial_fee'];
			$guest_tax = $result[0]['guest_tax'];
			$vat_fee = $result[0]['vat_fee'];
			$currency = $result[0]['currency'];
			$order_sub_total = 0;
			
			//// Calculate discount
			//$discount_type = $result[0]['discount_type'];
			//$discount_value = $result[0]['discount_value'];

			// Calculate total rooms price
			// [#001 - 2013.06.13] fixed bug - calculating order_price - old version was SUM(price) as order_price
			$sql = 'SELECT SUM(price + extra_beds_charge + meal_plan_price) as order_price FROM '.TABLE_BOOKINGS_ROOMS.' WHERE booking_number = \''.$booking_number.'\'';
			$result1 = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			$order_price = ($result1[1] > 0) ? $result1[0]['order_price'] : 0;
			
			//calculate extras ammount
			$temp_array = unserialize($result[0]['extras']);			
			if($act == 'add'){
				if(isset($temp_array[$sel_extras])){
					if(($temp_array[$sel_extras] + $extras_amount) > 100) $temp_array[$sel_extras] = 100;
					else $temp_array[$sel_extras] += $extras_amount;
				}else{
					$temp_array[$sel_extras] = $extras_amount;
				}
			}else{
				unset($temp_array[$sel_extras]);
			}
			$sql = 'SELECT (price * '.$extras_amount.') as extras_price FROM '.TABLE_EXTRAS.' WHERE id = '.(int)$sel_extras;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$extras_fee = Extras::GetExtrasSum($temp_array, $currency);
			}
			
			// formula: ((order_price - discount) + initial fee + guest tax + extras) * VAT
			$order_sub_total = (($order_price - $discount_fee - $guests_discount - $nights_discount) + $initial_fee + $guest_tax + $extras_fee);
			$vat_fee = $order_sub_total * ($vat_percent / 100);
			
			if($pre_payment_type == 'full price'){								
				$payment_sum = $order_sub_total + $vat_fee;			
			}elseif($pre_payment_type == 'first night'){
				$payment_sum = $this->CalculateFirstNightPrice($booking_number, $vat_percent);
			}elseif($pre_payment_type == 'fixed sum'){
				$payment_sum = $pre_payment_value;
			}elseif($pre_payment_type == 'percentage'){				
				$payment_sum = ($order_sub_total + $vat_fee) * ($pre_payment_value / 100);
			}

			// update bookings table
			$sql = 'UPDATE '.TABLE_BOOKINGS.' SET
						extras = \''.serialize($temp_array).'\',
						extras_fee = '.$extras_fee.',
						vat_fee = \''.$vat_fee.'\',						
						payment_sum = \''.$payment_sum.'\',
						pre_payment_value = \''.$pre_payment_value.'\'
					WHERE id = '.(int)$rid;
			database_void_query($sql);
			return true;
		}
		return false;				
	}	

	/**
	 * Returns Extras list for booking
	 * @param $oid
	 */
	public function GetBookingExtrasList($oid)
	{
		$output = array();
		$sql = 'SELECT currency, extras FROM '.TABLE_BOOKINGS.' WHERE id = '.(int)$oid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$arr_extras = unserialize($result[0]['extras']);
			$currency_info = Currencies::GetCurrencyInfo($result[0]['currency']);
			$symbol = isset($currency_info['symbol']) ? $currency_info['symbol'] : '$';
			foreach($arr_extras as $key => $val){
				$extra = Extras::GetExtrasInfo($key);
				$output[] = array('name'=>$extra['name'], 'unit_price'=>$symbol.$extra['price'], 'units'=>$val, 'price'=>$symbol.($extra['price']*$val), 'price_wo_currency'=>($extra['price']*$val));
			}
		}
		return $output;
	}

	/**
	 * Returns Rooms list for booking
	 * @param $booking_number
	 */
	public function GetBookingHotelsList($booking_number = '')
	{
        $return = array();
		$sql = "SELECT
					".TABLE_BOOKINGS_ROOMS.".id,
					".TABLE_BOOKINGS_ROOMS.".hotel_id
				FROM ".TABLE_BOOKINGS_ROOMS."
				WHERE
					".TABLE_BOOKINGS_ROOMS.".booking_number = '".$booking_number."'";
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i=0; $i<$result[1]; $i++){
				$return[] = $result[0][$i]['hotel_id'];
			}
		}
		
		return $return;
	}

	/**
	 * Returns booking status
     * @param $rid
     * @return int
	 */
	public function GetBookingStatus($rid = '')
	{
        $return = 0;
		$sql = "SELECT
					".TABLE_BOOKINGS.".status
				FROM ".TABLE_BOOKINGS."
				WHERE
					".TABLE_BOOKINGS.".id = '".$rid."'";
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
            $return = (int)$result[0]['status'];
		}
		
		return $return;
	}
	
	/**
	 * Returns Rooms list for booking
	 * @param $oid
	 * @param $language_id
	 * @param $mode
	 * @param $draw_swipe
	 */
	public function GetBookingRoomsList($oid, $language_id, $mode = 'details', $draw_swipe = true)
	{
		$output = '';
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
		$meal_plans_count = MealPlans::MealPlansCount();
		$hotels_count = Hotels::HotelsCount();
		$data = array();

        // display list of rooms in order		
		$sql = 'SELECT
					'.TABLE_BOOKINGS_ROOMS.'.id,
					'.TABLE_BOOKINGS_ROOMS.'.booking_number,
					'.TABLE_BOOKINGS_ROOMS.'.rooms,
					'.TABLE_BOOKINGS_ROOMS.'.room_numbers,
					'.TABLE_BOOKINGS_ROOMS.'.adults,
					'.TABLE_BOOKINGS_ROOMS.'.children,
					DATE_FORMAT('.TABLE_BOOKINGS_ROOMS.'.checkin, \''.$this->sqlFieldDateFormat.'\') as checkin,
					DATE_FORMAT('.TABLE_BOOKINGS_ROOMS.'.checkout, \''.$this->sqlFieldDateFormat.'\') as checkout,
					'.TABLE_BOOKINGS_ROOMS.'.rooms,
					'.TABLE_BOOKINGS_ROOMS.'.price,
					'.TABLE_BOOKINGS_ROOMS.'.extra_beds,
					'.TABLE_BOOKINGS_ROOMS.'.extra_beds_charge,
					'.TABLE_BOOKINGS_ROOMS.'.meal_plan_id,
					'.TABLE_BOOKINGS_ROOMS.'.meal_plan_price,
					'.TABLE_CURRENCIES.'.symbol,
					'.TABLE_CURRENCIES.'.symbol_placement,					
					'.TABLE_ROOMS_DESCRIPTION.'.room_type,
					'.TABLE_MEAL_PLANS_DESCRIPTION.'.name as meal_plan_name,
					'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name			
				FROM '.TABLE_BOOKINGS_ROOMS.'
					INNER JOIN '.$this->tableName.' ON '.TABLE_BOOKINGS_ROOMS.'.booking_number = '.$this->tableName.'.booking_number
					LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.TABLE_BOOKINGS_ROOMS.'.room_id = '.TABLE_ROOMS_DESCRIPTION.'.room_id AND '.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.encode_text($language_id).'\' 
					LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
					LEFT OUTER JOIN '.TABLE_MEAL_PLANS_DESCRIPTION.' ON '.TABLE_BOOKINGS_ROOMS.'.meal_plan_id = '.TABLE_MEAL_PLANS_DESCRIPTION.'.meal_plan_id AND '.TABLE_MEAL_PLANS_DESCRIPTION.'.language_id = \''.encode_text($language_id).'\'
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_BOOKINGS_ROOMS.'.hotel_id = '.TABLE_HOTELS_DESCRIPTION.'.hotel_id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.encode_text($language_id).'\'
				WHERE
					'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$oid.' ';
				if($this->user_id != ''){
					$sql .= ' AND '.$this->tableName.'.is_admin_reservation = 0 AND '.$this->tableName.'.customer_id = '.(int)$this->user_id;
				}

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			$reservations_total = 0;

			$output .= '<h4>'._RESERVATION_DETAILS.'</h4>';

			if($draw_swipe){
				$output .= '<div class="swipe-table swipe-room-prices table-responsive">';
				$output .= draw_swipe_icon();
			}
			$output .= '<table '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').' style="border:1px solid #d1d2d3" width="100%" border="0" cellspacing="0" cellpadding="3" class="tblReservationDetails">';
			$output .= '<thead><tr style="background-color:#e1e2e3;font-weight:bold;font-size:13px;">';
			$output .= '<th class="center"> # </th>';
			$output .= '<th>'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _ROOM_TYPE).'</th>';
			$output .= (($hotels_count > 1 && !FLATS_INSTEAD_OF_HOTELS) ? '<th>'._HOTEL.'</th>' : '<th></th>');
 			$output .= '<th align="center">'._CHECK_IN.'</th>';
			$output .= '<th align="center">'._CHECK_OUT.'</th>';
			$output .= FLATS_INSTEAD_OF_HOTELS ? '' : '<th align="center">'._ROOMS.'</th>';
			$output .= FLATS_INSTEAD_OF_HOTELS ? '' : '<th align="center">'._ROOM_NUMBERS.'</th>';
			$output .= '<th align="center">'._ADULTS.'</th>';
			$output .= (($allow_children == 'yes') ? '<th align="center">'._CHILDREN.'</th>' : '<th></th>');
			$output .= (($allow_extra_beds == 'yes') ? '<th align="center">'._EXTRA_BEDS.'</th>' : '<th></th>');
			$output .= (($meal_plans_count) ? '<th align="center">'._MEAL_PLANS.'</th>' : '<th></th>');
			$output .= '<th align="right">'._PRICE.'</th>';
			$output .= '<th width="5px" nowrap="nowrap"></th>';
			$output .= '</tr></thead><tbody>';
			
			for($i=0; $i < $result[1]; $i++){			
				if($mode == 'invoice'){
					$data[$i]['room_type'] = $result[0][$i]['room_type'];
					$data[$i]['checkin'] = $result[0][$i]['checkin'];
					$data[$i]['checkout'] = $result[0][$i]['checkout'];
					$data[$i]['rooms'] = FLATS_INSTEAD_OF_HOTELS ? '' : $result[0][$i]['rooms'];
					$data[$i]['room_numbers'] = FLATS_INSTEAD_OF_HOTELS ? '' : decode_text($result[0][$i]['room_numbers']);
					$data[$i]['adults'] = decode_text($result[0][$i]['adults']);
					$data[$i]['children'] = ($allow_children == 'yes') ? $result[0][$i]['children'] : '';
					$data[$i]['extra_beds'] = ($allow_extra_beds == 'yes') ? $result[0][$i]['extra_beds'] : '';
					$data[$i]['extra_beds_charge'] = ($allow_extra_beds == 'yes') ? $result[0][$i]['extra_beds_charge'] : '';
					$data[$i]['price'] = Currencies::PriceFormat($result[0][$i]['price'], $result[0][$i]['symbol'], $result[0][$i]['symbol_placement'], $this->currencyFormat);
					$data[$i]['price_wo_currency'] = $result[0][$i]['price'];
					$data[$i]['meal_plan_name'] = ($meal_plans_count) ? $result[0][$i]['meal_plan_name'] : '';
					$data[$i]['meal_plan_price'] = ($meal_plans_count) ? $result[0][$i]['meal_plan_price'] : 0;
					$data[$i]['hotel_name'] = ($hotels_count > 1) ? $result[0][$i]['hotel_name'] : '';
				}			

				$output .= '<tr style="font-size:13px;">';
				$output .= '<td align="center" width="40px">'.($i+1).'.</td>';
				$output .= '<td>'.$result[0][$i]['room_type'].'</td>';
				$output .= ($hotels_count > 1) ? '<td>'.$result[0][$i]['hotel_name'].'</td>' : '<td></td>';				
				$output .= '<td>'.$result[0][$i]['checkin'].'</td>';
				$output .= '<td>'.$result[0][$i]['checkout'].'</td>';
				$output .= FLATS_INSTEAD_OF_HOTELS ? '' : '<td align="center">'.$result[0][$i]['rooms'].'</td>';
				if(!FLATS_INSTEAD_OF_HOTELS && $mode == 'edit'){
					$output .= '<td>';
					$output .= '<form name="frmBookingDescription" action="index.php?admin=mod_booking_bookings" method="post">';
					$output .= draw_hidden_field('mg_action', 'update_room_numbers', false);
					$output .= draw_hidden_field('mg_rid', $oid, false);
					$output .= draw_hidden_field('rdid', $result[0][$i]['id'], false);
					$output .= draw_token_field(false);
					$output .= '<input type="textbox" class="mgrid_text" name="room_numbers" size="8" maxlength="50" value="'.decode_text($result[0][$i]['room_numbers']).'" />&nbsp;';
					$output .= '<label style="display:none;">'.decode_text($result[0][$i]['room_numbers']).'</label>';
					$output .= '<input type="submit" class="mgrid_button" name="btnSubmit" value="'._BUTTON_UPDATE.'" />';
					$output .= '</form>';
					$output .= '</td>';	
				}else{
					$output .= FLATS_INSTEAD_OF_HOTELS ? '' : '<td align="center">'.(int)$result[0][$i]['room_numbers'].'</td>';	
				}				
				$output .= '<td align="center">'.$result[0][$i]['adults'].'</td>';
				$output .= ($allow_children == 'yes') ? '<td align="center">'.$result[0][$i]['children'].'</td>' : ' <td></td>';
				$output .= ($allow_extra_beds == 'yes') ? '<td align="center">'.$result[0][$i]['extra_beds'].(!empty($result[0][$i]['extra_beds']) ? ' ('.Currencies::PriceFormat($result[0][$i]['extra_beds_charge'], $result[0][0]['symbol'], $result[0][0]['symbol_placement'], $this->currencyFormat).')' : '').'</td>' : ' <td></td>';
				$output .= ($meal_plans_count) ? '<td>'.(!empty($result[0][$i]['meal_plan_name']) ? $result[0][$i]['meal_plan_name'].' ('.Currencies::PriceFormat($result[0][$i]['meal_plan_price'], $result[0][$i]['symbol'], $result[0][$i]['symbol_placement'], $this->currencyFormat).')' : '').'</td>' : '<td></td>';
				$output .= '<td align="right">'.Currencies::PriceFormat($result[0][$i]['price'], $result[0][$i]['symbol'], $result[0][$i]['symbol_placement'], $this->currencyFormat).'</td>';
				$output .= '<td></td>';
				$output .= '</tr>';
				$reservations_total += ($result[0][$i]['price'] + $result[0][$i]['meal_plan_price'] + $result[0][$i]['extra_beds_charge']);
			}
			if($reservations_total > 0){
				$output .= '<tr style="font-size:13px;">';
				$output .= '<td colspan="'.(FLATS_INSTEAD_OF_HOTELS ? '7' : '9').'"></td>';
				$output .= '<td colspan="3" align="right"><span>&nbsp;<b>'._TOTAL.': &nbsp;&nbsp;&nbsp;'.Currencies::PriceFormat($reservations_total, $result[0][0]['symbol'], $result[0][0]['symbol_placement'], $this->currencyFormat).'</b>&nbsp;</span></td>';
				$output .= '<td></td>';
				$output .= '</tr>';					
			}
			$output .= '</tbody></table>';
			
			if($draw_swipe){
				$output .= '</div>';
			}
		}		
		
		if($mode == 'invoice') return $data;
		else return $output;
	}
	
	/**
	 * Calculates cancellation fee
	 * @param $booking_number
	 */
	public static function CalculateCancellationFee($booking_number = '')
	{
		$total_cancellation_fee = 0;

        // Display list of rooms in order		
		$sql = "SELECT
					".TABLE_BOOKINGS_ROOMS.".id,
					".TABLE_BOOKINGS_ROOMS.".booking_number,
					".TABLE_BOOKINGS_ROOMS.".checkin,
					".TABLE_BOOKINGS_ROOMS.".checkout,
					".TABLE_BOOKINGS_ROOMS.".price,
					".TABLE_BOOKINGS_ROOMS.".discount,
					".TABLE_BOOKINGS_ROOMS.".meal_plan_price,
					".TABLE_BOOKINGS_ROOMS.".extra_beds_charge,
					".TABLE_ROOMS.".refund_money_type,
					".TABLE_ROOMS.".refund_money_value
				FROM ".TABLE_BOOKINGS_ROOMS."
					LEFT OUTER JOIN ".TABLE_ROOMS." ON ".TABLE_BOOKINGS_ROOMS.".room_id = ".TABLE_ROOMS.".id 
				WHERE
					".TABLE_BOOKINGS_ROOMS.".booking_number = '".$booking_number."'";

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		for($i=0; $i < $result[1]; $i++){
			
			$refund_type = isset($result[0][$i]['refund_money_type']) ? $result[0][$i]['refund_money_type'] : 0;
			$refund_value = isset($result[0][$i]['refund_money_value']) ? $result[0][$i]['refund_money_value'] : 0;
			
			$rooms_price = ($result[0][$i]['price'] + $result[0][$i]['meal_plan_price'] + $result[0][$i]['extra_beds_charge'] - $result[0][$i]['discount']);
			
			// Types: '0 - first night, 1 - fixed price, 2 - percentage'						
			if($refund_type == 2){
				// Percentage
                $rooms_cancellation = $rooms_price * ($refund_value / 100);
			}elseif($refund_type == 1){
				// Fixed price
				$rooms_cancellation = $refund_value;
			}else{
				// Frist night
				$nights = nights_diff($result[0][$i]['checkin'], $result[0][$i]['checkout']);
				$rooms_cancellation = !empty($nights) ? $rooms_price / $nights : 0;
			}
            $rooms_cancellation = $rooms_cancellation > 0 ? $rooms_cancellation : 0;
			
			$total_cancellation_fee += $rooms_cancellation;
		}
		
		return $total_cancellation_fee;
	}

	/**
	 * Returns count of bookings with given couponnumber
	 * @param $where_clause
	 */
	public static function CountAllByCoupon($coupon = '')
	{
		$sql = "SELECT COUNT(*) as cnt FROM ".TABLE_BOOKINGS." WHERE status IN(1,2,3) AND coupon_code = '".$coupon."'";
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		return isset($result[0]['cnt']) ? (int)$result[0]['cnt'] : 0;
	}

	//==========================================================================
    // Static Methods
	//==========================================================================
	/**
	 * Remove expired 'Prebooking' bookings
	 */
	public static function RemoveExpired()
	{
		global $objSettings;
		
		$prebooking_orders_timeout = (int)ModulesSettings::Get('booking', 'prebooking_orders_timeout');
		$sender = $objSettings->GetParameter('admin_email');
		// preapre datetime format
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$fieldDateFormat = 'M d, Y';
		}else{
			$fieldDateFormat = 'd M, Y';
		}
		$currencyFormat = get_currency_format();
		$language_id = Languages::GetDefaultLang();
		$hotels_count = Hotels::HotelsCount();

		if($prebooking_orders_timeout > 0){
			
			$sql_delete = 'DELETE FROM '.TABLE_BOOKINGS.' WHERE status = 0 AND TIMESTAMPDIFF(HOUR, created_date, \''.date('Y-m-d H:i:s').'\') >= '.(int)$prebooking_orders_timeout;
			
			if(ModulesSettings::Get('booking', 'reservation_expired_alert') == 'yes'){
				$sql = 'SELECT
							'.TABLE_BOOKINGS.'.customer_id,
							'.TABLE_CUSTOMERS.'.first_name,
							'.TABLE_CUSTOMERS.'.last_name,
							'.TABLE_CUSTOMERS.'.preferred_language,
							'.TABLE_CUSTOMERS.'.email,
							'.TABLE_BOOKINGS.'.booking_number,
							'.TABLE_BOOKINGS.'.created_date,
							'.TABLE_BOOKINGS.'.booking_description,
							'.TABLE_BOOKINGS.'.rooms_amount,
							'.TABLE_BOOKINGS.'.order_price,
							'.TABLE_BOOKINGS.'.currency
						FROM '.TABLE_BOOKINGS.'
							LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.TABLE_BOOKINGS.'.customer_id = '.TABLE_CUSTOMERS.'.id
						WHERE '.TABLE_BOOKINGS.'.status = 0 AND
							TIMESTAMPDIFF(HOUR, '.TABLE_BOOKINGS.'.created_date, \''.date('Y-m-d H:i:s').'\') >= '.(int)$prebooking_orders_timeout;
				
				$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
				if($result[1] > 0){
					$arr_trans_val = array(
						'_PERSONAL_INFORMATION','_FIRST_NAME','_LAST_NAME','_EMAIL_ADDRESS',
						'_BILLING_DETAILS','_ADDRESS','_CITY','_STATE','_COUNTRY','_ZIP_CODE','_PHONE','_FAX',
						'_BOOKING_DESCRIPTION','_CREATED_DATE','_NOT_PAID_YET','_PAYMENT_DATE','_PAYMENT_TYPE','_PAYMENT_METHOD','_CURRENCY','_ROOMS','_BOOKING_PRICE',
						'_DISCOUNT','_COUPON_DISCOUNT','_GUESTS_DISCOUNT','_ROOMS_DISCOUNT','_BOOKING_SUBTOTAL','_AFTER_DISCOUNT','_EXTRAS_SUBTOTAL',
						'_INITIAL_FEE','_GUEST_TAX','_VAT','_PAYMENT_SUM','_PAYMENT_REQUIRED','_ADDITIONAL_INFO','_RESERVATION_DETAILS',
						'_ROOM_TYPE','_CHECK_IN','_CHECK_OUT','_CHILD','_NIGHTS','_ADULT','_EXTRA_BEDS','_MEAL_PLANS','_PER_NIGHT','_PRICE','_BANK_PAYMENT_INFO',
						'_CANCELLATION_POLICY','_FREE_OF_CHARGE','_AFTER','_UNKNOWN','_COUPON_CODE','_STATUS','_PAY_ON_ARRIVAL','_ONLINE_ORDER','_PAYPAL',
						'_BANK_TRANSFER','_ACCOUNT_BALANCE','_PREBOOKING','_PREBOOKING','_PENDING','_RESERVED','_COMPLETED','_REFUNDED','_PAYMENT_REQUIRED',
						'_CANCELED','_PAYMENT_ERROR','_HOTEL', '_FLAT',
					);

					$sql = 'SELECT language_id, key_value, key_text FROM '.TABLE_VOCABULARY.' WHERE key_value IN (\''.implode('\',\'', $arr_trans_val).'\')';
					$translations = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
					if($translations[1] > 0){
						foreach($translations[0] as $trans_info){
							if(!isset($_t[$trans_info['language_id']])){
								$_t[$trans_info['language_id']] = array();
							}
							$_t[$trans_info['language_id']][$trans_info['key_value']] = $trans_info['key_text'];
						}
					}else{
						$arr_lang = array();
					}
					$hotel_description = array();
					if($hotels_count == 1){
						foreach($_t as $key => $val){
							$this_lang = $key;
							$hotel_info = Hotels::GetHotelFullInfo(0, $language_id);
							$hotel_description[$this_lang] .= $hotel_info['name'].'<br>';
							$hotel_description[$this_lang] .= $hotel_info['address'].'<br>';
							$hotel_description[$this_lang] .= (isset($val['_PHONE']) ? $val['_PHONE'] : _PHONE).':'.$hotel_info['phone'];
							if($hotel_info['fax'] != '') $hotel_description[$this_lang] .= ', '.(isset($val['_FAX']) ? $val['_FAX'] : _FAX).':'.$hotel_info['fax'];
						}
					}
	
					for($i=0; $i < $result[1]; $i++){	
						$booking_details = array();
						foreach($_t as $key => $val){
							$this_lang = $key;
							$booking_details[$this_lang]  = (isset($val['_BOOKING_DESCRIPTION']) ? $val['_BOOKING_DESCRIPTION'] : _BOOKING_DESCRIPTION).': '.$result[0][$i]['booking_description'].'<br />';
							$booking_details[$this_lang] .= (isset($val['_CREATED_DATE']) ? $val['_CREATED_DATE'] : _CREATED_DATE).': '.format_datetime($result[0][$i]['created_date'], $fieldDateFormat.' H:i:s', '', true).'<br />';
							$booking_details[$this_lang] .= (isset($val['_ROOMS']) ? $val['_ROOMS'] : _ROOMS).': '.$result[0][$i]['rooms_amount'].'<br />';
							$booking_details[$this_lang] .= (isset($val['_BOOKING_PRICE']) ? $val['_BOOKING_PRICE'] : _BOOKING_PRICE).': '.Currencies::PriceFormat($result[0][$i]['order_price'], $result[0][$i]['currency'], 'left', $currencyFormat).'<br />';
						}
						
						$recipient = $result[0][$i]['email'];
						$preferred_language = $result[0][$i]['preferred_language'];
						send_email(
							$recipient,
							$sender,
							'reservation_expired',
							array(
								'{FIRST NAME}' => $result[0][$i]['first_name'],
								'{LAST NAME}'  => $result[0][$i]['last_name'],
								'{BOOKING DETAILS}' => $booking_details[$preferred_language],
								'{HOTEL INFO}' => ((!empty($hotel_description[$preferred_language])) ? '<br>-----<br>'.$hotel_description[$preferred_language] : ''),
							),
							$preferred_language
						);
					}	
					return database_void_query($sql_delete);
				}							
			}else{
				return database_void_query($sql_delete);
			}			
		}
		return false;
	}
	
	/**
	 * Draw booking status
	 * @param $booking_number
	 * @param $draw
	 */
	public function DrawBookingStatus($booking_number = '', $draw = true)
	{			
		global $objSettings;
		$output = '';
		
		$sql = 'SELECT b.id
				FROM '.TABLE_BOOKINGS.' b
				WHERE
					 b.status != 0 AND
					 b.booking_number = \''.$booking_number.'\'';
						
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output .= '<div style="float:'.Application::Get('defined_right').'"><a style="text-decoration:none;" href="javascript:void(\'booking|preview\')" onclick="javascript:appPreview(\'booking\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a></div>';
			$output .= $this->DrawBookingDescription($result[0]['id'], 'check booking', false);
		}else{
			$this->error = draw_important_message(_NO_BOOKING_FOUND, false);
			$output .= $this->error;
		}
		
		if($draw) echo $output;
		else return $output;
	}	
	
	/**
	 * Draws booking status block
	 * @param $draw
	 */
	public static function DrawBookingStatusBlock($draw = true)
	{
		$output = '<form action="index.php?page=check_status" id="frmCheckBooking" name="frmCheckBooking" method="post">
			'.draw_hidden_field('task', 'check_status', false, 'task').'
			'.draw_token_field(false).'
			<table cellspacing="2" border="0">
			<tr><td>'._ENTER_BOOKING_NUMBER.':</td></tr>
			<tr><td><input type="text" name="booking_number" id="frmCheckBooking_booking_number" maxlength="20" autocomplete="off" value="" /></td></tr>
			<tr><td style="height:3px"></td></tr>
			<tr><td><input class="form_button" type="submit" value="'._CHECK_STATUS.'" /></td></tr>
			</table>
		</form>';
	
		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 * Draws booking status block
	 * @param $columns
	 * @param $items_in_coulmn
	 * @param $draw
	 */
	public static function DrawLastBookingBlock($columns = 2, $items_in_coulmn = 3, $draw = true)
	{
		$lang = Application::Get('lang');
		$output = '';
		
		$columns = $columns > 12 ? 12 : abs((int)$columns);
		$items_in_coulmn = $items_in_coulmn > 10 ? 10 : abs((int)$items_in_coulmn);
		$col_md = (12 / $columns);
		
		// I do a sample by counting the number of rooms booked for each hotel.
		// STRAIGHT_JOIN is necessary to don't change the order of MySQL for JOIN
		$sql = 'SELECT STRAIGHT_JOIN
			SUM('.TABLE_BOOKINGS_ROOMS.'.rooms) as rooms_count,
				'.TABLE_BOOKINGS.'.created_date,
				'.TABLE_COUNTRIES_DESCRIPTION.'.name as user_location,
				'.TABLE_HOTELS.'.hotel_image_thumb,
				'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name,
				'.TABLE_BOOKINGS_ROOMS.'.hotel_id as hotel_id,
				'.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.name as location_name
			FROM '.TABLE_BOOKINGS.'
				LEFT OUTER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS_ROOMS.'.booking_number = '.TABLE_BOOKINGS.'.booking_number
				LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.TABLE_CUSTOMERS.'.id = '.TABLE_BOOKINGS.'.customer_id
				LEFT OUTER JOIN '.TABLE_HOTELS.' ON '.TABLE_HOTELS.'.id = '.TABLE_BOOKINGS_ROOMS.'.hotel_id
				LEFT OUTER JOIN '.TABLE_COUNTRIES.' ON '.TABLE_COUNTRIES.'.abbrv = '.TABLE_CUSTOMERS.'.b_country
				LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' ON '.TABLE_COUNTRIES.'.id = '.TABLE_COUNTRIES_DESCRIPTION.'.country_id AND '.TABLE_COUNTRIES_DESCRIPTION.'.language_id = \''.$lang.'\'
				LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_HOTELS_DESCRIPTION.'.hotel_id = '.TABLE_HOTELS.'.id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$lang.'\'
				LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' ON '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.hotel_location_id = '.TABLE_HOTELS.'.hotel_location_id AND '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.language_id = \''.$lang.'\'
			WHERE 1
			GROUP BY '.TABLE_BOOKINGS_ROOMS.'.booking_number, '.TABLE_BOOKINGS_ROOMS.'.hotel_id
			ORDER BY '.TABLE_BOOKINGS_ROOMS.'.id DESC
			LIMIT 6';

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if(!empty($result[1])){
			// Create a block "Last Bookings"
			$output = '<div class="col-md-'.$col_md.'"><span class="dtitle">'._LAST_BOOKINGS.'</span>';
			for($i = 0; $i < $result[1]; $i++){
				if($i > 0 && $i % 3 == 0){
					$output .= '</div><div class="col-md-'.$col_md.'"><span class="dtitle">'._LAST_BOOKINGS.'</span>';
				}
				$unix_time_created = strtotime($result[0][$i]['created_date']);
				$href = prepare_link('hotels', 'hid', $result[0][$i]['hotel_id'], $result[0][$i]['hotel_name'], $result[0][$i]['hotel_name'], '', _CLICK_TO_VIEW, true);
				$count_hours = (int)((time() - $unix_time_created) / 3600);
				if($count_hours < 24){
					$type_booking = $count_hours < 1 ? _JUST_BOOKED : _BOOKED.' '.($count_hours > 1 ? $count_hours.' '._HOURS : _HOUR).' '._AGO;
				}else{
					$count_days = (int)($count_hours / 24);
					if($count_days < 366){
						$type_booking = strtolower(($count_days == 1 ? '1 '._DAY : $count_days.' '._DAYS).' '._AGO);
					}else{
						$type_booking = _MORE_YEAR_AGO;
					}
					$type_booking = _BOOKED.' '.$type_booking;
				}
				$count_rooms = $result[0][$i]['rooms_count'] > 1 ? $result[0][$i]['rooms_count'].' '._ROOMS : _ROOM;
				$hotel_name = '</span><a href="'.$href.'" class="dark">'.$result[0][$i]['hotel_name'].'</a><span class="grey">';
				$message = str_replace(array('{user_location}', '{type_booking}', '{count_rooms}', '{hotel_name}', '{hotel_location}'), array($result[0][$i]['user_location'], $type_booking, $count_rooms, $hotel_name, $result[0][$i]['location_name']), _LAST_BOOKINGS_MESSAGE);
				$output .= '<div class="deal">
					<a href="'.$href.'"><img src="'.APPHP_BASE.'images/hotels/'.(!empty($result[0][$i]['hotel_image_thumb']) ? $result[0][$i]['hotel_image_thumb'] : 'no_image.png').'" alt="'.htmlentities($result[0][$i]['hotel_name']).'" class="dealthumb"/></a>
					<div class="dealtitle">
						<p><span class="grey">'.$message.'</span></p>
					</div>
				</div>';
			}

			$output .= '</div>';
		}
		
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Calculate first night price for booking
	 * @param $booking_number
	 * @param $vat_percent
	 */
	private function CalculateFirstNightPrice($booking_number, $vat_percent)
	{
		$first_night_price = 0;
		$first_night_calculating_type = ModulesSettings::Get('booking', 'first_night_calculating_type');
		
		$sql = 'SELECT checkin, checkout, price, room_id 
				FROM '.TABLE_BOOKINGS_ROOMS.'
				WHERE booking_number = \''.$booking_number.'\'';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i=0; $i < $result[1]; $i++){
				if($first_night_calculating_type == 'average'){
					// formula: total_sum / number of nights			
					$first_night_price += $result[0][$i]['price'] / nights_diff($result[0][$i]['checkin'], $result[0][$i]['checkout']);
				}else{
					// formula: real price for first day
					$temp = Rooms::GetPriceForDate($result[0][$i]['room_id'], $result[0][$i]['checkin']);
					$first_night_price += $temp * (1 + $vat_percent / 100);
				}				
			}	
		}		
		
		return $first_night_price;
	}
	
	/**
	 * Get Vat Percent decimal points
	 * @param $vat_percent
	 */
	private function GetVatPercentDecimalPoints($vat_percent = '0')
	{
		return (substr($vat_percent, -1) == '0') ? 2 : 3;
	}
	
	/**
	 * Returns money to balance after canceled or deleted order
	 * @param int $rid		Order ID
     * @param bool $return_money
     * @param bool $include_cancelled_fee
	 * @return bool
	*/
	private function ReturnMoneyToBalance($rid = '0', $return_money = true, $include_cancelled_fee = true)
	{
		if(!$this->allow_payment_with_balance){
			return false;
		}
		
		// Check if this is "by balance" payment and return money back to account balance 
		$sql = 'SELECT
					customer_id,
					(payment_sum + additional_payment) as revert_sum,
					cancel_payment_date,
					booking_number,
					currency
				FROM '.TABLE_BOOKINGS.'
				WHERE id = '.(int)$rid.' AND payment_type = 6';		
		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);		
		if($result[1] > 0){
			$customer_id = $result[0]['customer_id'];
			$revert_sum = $result[0]['revert_sum'];
			if($include_cancelled_fee){
				$cancellation_date_unix = strtotime($result[0]['cancel_payment_date']);
				if($cancellation_date_unix <= time()){
					$cancellation_fee = self::CalculateCancellationFee($result[0]['booking_number']);
					$revert_sum -= $cancellation_fee;
				}
			}
			$currency_info = Currencies::GetCurrencyInfo($result[0]['currency']);
			$revert_sum = $revert_sum / $currency_info['rate'];
			$customer_type = Customers::GetCustomerInfo($customer_id, 'customer_type');
			
			// Check if this is "agency payment"
			if($customer_type == 1){
				$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET balance = balance '.($return_money === false ? '-' : '+').' '.$revert_sum.' WHERE id = '.(int)$customer_id;
				database_void_query($sql);
			}
		}
		
		return true;
	}	
	
	/**
	 * Check if specific record is assigned to given owner
	 * @param int $curRecordId
	 */
	public function CheckRecordAssigned($curRecordId = 0)
	{
		global $objSession, $objLogin;
		
		if($objLogin->IsLoggedInAs('hotelowner','regionalmanager')){
			$sql = 'SELECT
						'.$this->tableName.'.* 
					FROM '.$this->tableName.'
						INNER JOIN '.TABLE_BOOKINGS_ROOMS.' br ON '.$this->tableName.'.booking_number = br.booking_number
					WHERE '.$this->tableName.'.'.$this->primaryKey.' = '.(int)$curRecordId . $this->assigned_to_hotels;
					
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] <= 0){
				$objSession->SetMessage('notice', draw_important_message(_OWNER_NOT_ASSIGNED_TO_HOTEL, false));
				return false;
			}
		}
		
		return true;		
	}

	/**
	 * Draws checkin/checkout list
	 * @param string $type
	 * @param string $date
	 */
	public static function DrawCheckList($type = 'checkin', $date = '')
	{
		global $objLogin;
		
		$output = '';
		$date_sql = empty($date) ? date('Y-m-d') : '';
		$where_clause = '';
		$language_id = Application::Get('lang');
		$hotels_count = Hotels::HotelsCount();
		$show_checklist = true;
		
		if($objLogin->IsLoggedInAs('hotelowner')){
			$hotels = $objLogin->AssignedToHotels();
			$hotels_list = implode(',', $hotels);
			if(!empty($hotels_list)){
				$where_clause .= ' AND br.hotel_id IN ('.$hotels_list.') ';
			}
			else{
				$show_checklist = false;
			}
		}elseif($objLogin->IsLoggedInAs('regionalmanager')){
			$hotels = AccountLocations::GetHotels($objLogin->GetLoggedID());
			$travel_agency_ids = AccountLocations::GetTravelAgencies($objLogin->GetLoggedID());
			$hotels_list = !empty($hotels) ? implode(',', $hotels) : '-1';
			$travel_agency_list = !empty($travel_agency_ids) ? implode(',', $travel_agency_ids) : '-1';			
			if(!empty($hotels_list) || !empty($travel_agency_list)){
				$where_clause .= ' AND (br.hotel_id in ('.$hotels_list.') OR b.customer_id IN ('.$travel_agency_list.')) ';
			}else{
				$show_checklist = false;
			}
		}
		
		if($show_checklist){
			$sql = "SELECT
						b.booking_number,
						b.created_date,
						b.additional_info,
						IF(
							b.is_admin_reservation = 1,
							CONCAT(a.first_name, ' ', a.last_name, ' <br>(', a.account_type, ')'),
							CONCAT(c.first_name, ' ', c.last_name, IF(c.customer_type = 1, CONCAT('<br>(', c.company, ')'), ''))
						) as full_name,
						c.id as customer_id,
						c.customer_type,
						b.is_admin_reservation,
						br.checkin,
						br.checkout,
						br.rooms as room_numbers,
						r.room_type,
						hd.name as hotel_name
					FROM ".TABLE_BOOKINGS." b
						INNER JOIN ".TABLE_BOOKINGS_ROOMS." br ON b.booking_number = br.booking_number
						INNER JOIN ".TABLE_ROOMS." r ON br.room_id = r.id
						LEFT OUTER JOIN ".TABLE_CUSTOMERS." c ON b.customer_id = c.id
						LEFT OUTER JOIN ".TABLE_ACCOUNTS." a ON b.customer_id = a.id AND b.is_admin_reservation = 1
						LEFT OUTER JOIN ".TABLE_HOTELS_DESCRIPTION." hd ON r.hotel_id = hd.hotel_id AND hd.language_id = '".encode_text($language_id)."'
					WHERE
						b.status IN (2,3)
						".$where_clause."
						".($type == 'checkout' ? " AND br.checkout = '".$date_sql."'" : " AND br.checkin = '".$date_sql."'");
	
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		}else{
			$result = array('0' => array(), '1' => 0);
		}

		$output .= '<div'.($result[1] > 0 ? ' class="table-responsive"' : '').'>';
		$output .= '<table class="tbl-dashboard-'.$type.'">';
		$output .= '<caption>'.($type == 'checkout' ? _TODAY_CHECKOUT : _TODAY_CHECKIN).'</caption>';
		$output .= '<thead>
			<tr>
				<th class="align-left" width="170px">'._VISITOR.'</th>
				<th class="align-left" width="120px">'._BOOKING_NUMBER.'</th>
				<th class="align-left" width="95px">'._CHECK_IN.'</th>
				<th class="align-left" width="95px">'._CHECK_OUT.'</th>
				'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<th class="align-left" width="140px">'._ROOM_TYPE.'</th>').'
				<th width="40px">&nbsp;</th>				
				'.($hotels_count > 1 ? '<th class="align-left">'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).'</th>' : '').'
				<th class="align-left" width="120px">'._DATE_CREATED.'</th>
				<th class="align-left">'._INFO.'</th>
			</tr>
			</thead>';
		$output .= '<tbody>';
		if($result[1] > 0){ 
			for($i = 0; $i < $result[1]; $i++){
				$output .= '<tr>
						<td>'.(!$result[0][$i]['is_admin_reservation'] ? '<a href="javascript:void(\'customer|view\')" onclick="open_popup(\'popup.ajax.php\',\'customer\',\''.$result[0][$i]['customer_id'].'\',\''.$result[0][$i]['customer_type'].'\',\''.Application::Get('token').'\')">'.str_replace('&lt;br&gt;', '<br>', htmlspecialchars($result[0][$i]['full_name'])).'</a>' : str_replace('&lt;br&gt;', '<br>', htmlspecialchars($result[0][$i]['full_name']))).'</td>
						<td class="align-left"><a href="javascript:void();" onclick="javascript:appGoToPage(\'index.php?admin=mod_booking_bookings&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;filter_by_'.TABLE_BOOKINGS.'booking_number='.htmlspecialchars($result[0][$i]['booking_number']).'\')">'.$result[0][$i]['booking_number'].'</a></td>
						<td class="align-left">'.htmlspecialchars($result[0][$i]['checkin']).'</td>
						<td class="align-left">'.htmlspecialchars($result[0][$i]['checkout']).'</td>
						'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<td class="align-left">'.htmlspecialchars($result[0][$i]['room_type']).'</td>').'
						<td class="align-left">'.htmlspecialchars($result[0][$i]['room_numbers']).'</td>
						'.($hotels_count > 1 ? '<td align="left">'.htmlspecialchars($result[0][$i]['hotel_name']).'</td>' : '').'
						<td class="align-left">'.htmlspecialchars($result[0][$i]['created_date']).'</td>
						<td class="align-left">'.htmlspecialchars($result[0][$i]['additional_info']).'</td>
					</tr>';
			}
		}else{
			$output .= '<tr><td colspan="8">'._NO_RECORDS_FOUND.'</td></tr>';
		}
		$output .= '</tbody>';
		$output .= '</table>';
		$output .= '</div>';
        if($result[1] > 0){
            $output .= '<script>';
            $output .= '$(document).ready(function(){$(".tbl-dashboard-'.$type.'").DataTable({"paging":false,"searching":false,"info":false});});';
            $output .= '</script>';
        }
		
		return $output;		
	}	
	
	/**
	 * get last booking
	 * @param $columns
	 * @param $items_in_coulmn
	 * @param $draw
	 */
	public static function GetLastBooking($items = 1, $where_clause = '')
	{
		$lang = Application::Get('lang');
		$output = '';
		
		// I do a sample by counting the number of rooms booked for each hotel.
		// STRAIGHT_JOIN is necessary to don't change the order of MySQL for JOIN
		$sql = 'SELECT STRAIGHT_JOIN
			SUM('.TABLE_BOOKINGS_ROOMS.'.rooms) as rooms_count,
				'.TABLE_BOOKINGS.'.created_date,
				'.TABLE_COUNTRIES_DESCRIPTION.'.name as user_location,
				'.TABLE_HOTELS.'.hotel_image_thumb,
				'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name,
				'.TABLE_BOOKINGS_ROOMS.'.hotel_id as hotel_id,
				'.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.name as location_name
			FROM '.TABLE_BOOKINGS.'
				LEFT OUTER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS_ROOMS.'.booking_number = '.TABLE_BOOKINGS.'.booking_number
				INNER JOIN '.TABLE_CUSTOMERS.' ON '.TABLE_CUSTOMERS.'.id = '.TABLE_BOOKINGS.'.customer_id
				INNER JOIN '.TABLE_HOTELS.' ON '.TABLE_HOTELS.'.id = '.TABLE_BOOKINGS_ROOMS.'.hotel_id
				INNER JOIN '.TABLE_COUNTRIES.' ON '.TABLE_COUNTRIES.'.abbrv = '.TABLE_CUSTOMERS.'.b_country
				LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' ON '.TABLE_COUNTRIES.'.id = '.TABLE_COUNTRIES_DESCRIPTION.'.country_id AND '.TABLE_COUNTRIES_DESCRIPTION.'.language_id = \''.$lang.'\'
				LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_HOTELS_DESCRIPTION.'.hotel_id = '.TABLE_HOTELS.'.id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$lang.'\'
				LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' ON '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.hotel_location_id = '.TABLE_HOTELS.'.hotel_location_id AND '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.language_id = \''.$lang.'\'
			WHERE '.(!empty($where_clause) ? $where_clause : '1').'
			GROUP BY '.TABLE_BOOKINGS_ROOMS.'.booking_number, '.TABLE_BOOKINGS_ROOMS.'.hotel_id
			ORDER BY '.TABLE_BOOKINGS_ROOMS.'.id DESC
			LIMIT '.(int)$items;

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result;
	}
	
	/**
	 * Get last booking
	 * @param $columns
	 * @param $items_in_coulmn
	 * @param $draw
	 */
	public static function GetLastBookingForHotels($hotel_ids = array())
	{
		$lang = Application::Get('lang');
		$result = array();

		if(!empty($hotel_ids) || !is_array($hotel_ids)){
			$sql = 'SELECT
					b.created_date,
					br.hotel_id as hotel_id
				FROM '.TABLE_BOOKINGS.' as b
					LEFT OUTER JOIN '.TABLE_BOOKINGS_ROOMS.' as br ON br.booking_number = b.booking_number
				WHERE br.hotel_id IN ('.implode(',', $hotel_ids).')
				   	AND br.id = (SELECT MAX(sbr.id) FROM '.TABLE_BOOKINGS_ROOMS.' as sbr WHERE sbr.hotel_id = br.hotel_id)';

			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		}
		return $result;
	}

	/**
	 * Get value of total payments of customers 
	 */
	public static function GetTotalPayments($customer_ids = array(), $where = '')
	{
		$sql = 'SELECT customer_id, SUM(payment_sum) as total_payments
				FROM '.TABLE_BOOKINGS.'
				'.(!empty($customer_ids) ? 'WHERE customer_id IN('.implode(',', $customer_ids).')' : '').'				
				'.(!empty($where) ? ' AND '.$where : '').'
				GROUP BY customer_id';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result;
	}		

	/*
	 * @param int $rid
	 * @return false|Date (in Unix format)
	 * */
	public static function GetCanceledDate($booking_number = '')
	{
		if(empty($booking_number)){
			return false;
		}

		$cancel_reservation_days = ModulesSettings::Get('booking', 'customers_cancel_reservation');	

		$sql = 'SELECT 
			'.TABLE_BOOKINGS_ROOMS.'.hotel_id,
			'.TABLE_BOOKINGS_ROOMS.'.checkin,
			'.TABLE_BOOKINGS_ROOMS.'.checkout,
			'.TABLE_HOTELS.'.cancel_reservation_day
		FROM '.TABLE_BOOKINGS_ROOMS.'
			LEFT OUTER JOIN '.TABLE_HOTELS.' ON '.TABLE_HOTELS.'.id = '.TABLE_BOOKINGS_ROOMS.'.hotel_id
		WHERE '.TABLE_BOOKINGS_ROOMS.'.booking_number = \''.$booking_number.'\'
		ORDER BY '.TABLE_HOTELS.'.cancel_reservation_day ASC';
		
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		$hotel_ids = array();
		$min_check_in = '';
		$max_check_out = '';
		if($result[1] > 0){
			$hotel_canvel_reservation = 0;
			$min_check_in = $result[0][0]['checkin'];
			$max_check_out = $result[0][0]['checkout'];
			for($i = 0; $i < $result[1]; $i++){
				if($min_check_in > $result[0][$i]['checkin']){
					$min_check_in = $result[0][$i]['checkin'];
				}
				if($max_check_out < $result[0][$i]['checkout']){
					$max_check_out = $result[0][$i]['checkout'];
				}
				if(!in_array($result[0][$i]['hotel_id'], $hotel_ids)){
					$hotel_ids[] = $result[0][$i]['hotel_id'];
				}
				if($hotel_canvel_reservation < $result[0][$i]['cancel_reservation_day']){
					$hotel_canvel_reservation = $result[0][$i]['cancel_reservation_day'];
				}
			}
			if($hotel_canvel_reservation > 0){
				$cancel_reservation_days = $hotel_canvel_reservation;
			}
		}

		if(!empty($hotel_ids) && !empty($min_check_in) && !empty($max_check_out)){
			$result = Packages::GetCanceledDays($hotel_ids, $min_check_in, $max_check_out);
			if(!empty($result) && $result[1] > 0){
				if(count($hotel_ids) == 1){
					$cancel_reservation_days = $result[0][0]['cancel_reservation_day'];
				}else{
					$hotel_cancel_reservation = 0;
					$hotel_default_cancel_reservation = 0;
					$hotels_not_found = array_combine($hotel_ids, $hotel_ids);
					for($i = 0; $i < $result[1]; $i++){
						// We find packages for all hotels (hotels == all) and learn the days to cancel
						if($result[0][$i]['hotel_id'] == 0 && $hotel_default_cancel_reservation < $result[0][$i]['cancel_reservation_day']){
							$hotel_default_cancel_reservation = $result[0][$i]['cancel_reservation_day'];
						}
						// We find packages for specific accommodations (hotels == "Hotel Name") and learn the days to cancel
						if($hotel_cancel_reservation < $result[0][$i]['cancel_reservation_day']){
							$hotel_cancel_reservation = $result[0][$i]['cancel_reservation_day'];
						}
						// If accommodations remove a hotel from the list. It is necessary to make sure that cancel_reservation_day defined for all hotels in the order
						if(isset($hotels_not_found[$result[0][$i]['hotel_id']])){
							unset($hotels_not_found[$result[0][$i]['hotel_id']]);
						}
					}
					if(empty($hotels_not_found) && $hotel_cancel_reservation != 0){
						// If cancel_reservation_day defined for all hotels in the order, then choose the highest number of days
						$cancel_reservation_days = $hotel_cancel_reservation;
					}elseif($hotel_default_cancel_reservation > 0){
						// If cancel_reservation_day defined not for all hotels in the order, then choose packages for all hotels (if one exists)
						$cancel_reservation_days = $hotel_default_cancel_reservation;
					}elseif($cancel_reservation_days < $hotel_cancel_reservation){
						// If at least for some of the hotel is defined Package and number of days to cancel it more than the default, then select it
						$cancel_reservation_days = $hotel_cancel_reservation;
					}
				}
			}
		}

		if($cancel_reservation_days > 0 && !empty($min_check_in)){
			$time_check_in = strtotime($min_check_in);
			return mktime(0, 0, 0, date('m', $time_check_in), date('d', $time_check_in) - $cancel_reservation_days, date('Y', $time_check_in));
		}

		return false;
	}

	/* Update room availability 
     * @param int $booking_number
     * @param $availability	increase|decrease
     * @param string $action_name
	 * @return void|HTML (error message)
	 * */
    public static function UpdateRoomsAvailability($booking_number = '', $availability = '', $action_name = '')
    {
		$availability = in_array($availability, array('increase', 'decrease')) ? $availability : 'increase';
	
		$sql = 'SELECT br.id, br.room_id, br.rooms, br.checkin, br.checkout
                FROM '.TABLE_BOOKINGS_ROOMS.' br
                WHERE br.booking_number = \''.$booking_number.'\'
                ORDER BY br.room_id DESC';
        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

        if(!empty($result) && $result[1] > 0){
            $arr_room_ids = array();
            $arr_room_info = array();
            foreach($result[0] as $booking_room){
                $arr_room_ids[] = $booking_room['room_id'];
                $info = array();
                $info['rooms'] = $booking_room['rooms'];
                $info['room_id'] = $booking_room['room_id'];
                $info['checkin'] = $booking_room['checkin'];
                $info['checkout_one_day_less'] = date('Y-m-d', (strtotime($booking_room['checkout']) - 24 * 60 * 60));
				$info['checkout'] = date('Y-m-d', (strtotime($booking_room['checkout'])));
                $arr_room_info[$booking_room['room_id']] = $info;
			}
			if(!empty($arr_room_ids)){
				// Get max room count for the all rooms
				$arr_room_count = array();
				$sql = 'SELECT id, room_count FROM '.TABLE_ROOMS.' WHERE id IN ('.implode(',',$arr_room_ids).')';
				$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
				if($result[1] > 0){
					foreach($result[0] as $room_info){
						$arr_room_count[$room_info['id']] = $room_info['room_count'];
					}
				}

                $sql = 'SELECT * FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE room_id IN ('.implode(',', $arr_room_ids).') ORDER BY room_id';
                $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
                if($result[1] > 0){
                    foreach($result[0] as $room_avail){
                        $room_id = $room_avail['room_id'];
                        $info = $arr_room_info[$room_id];
                        $from_year = date('Y', strtotime($info['checkin'])) - date('Y');
                        $to_year = date('Y', strtotime($info['checkout_one_day_less'])) - date('Y');
                        $from_month = date('m', strtotime($info['checkin']));
                        $to_month = date('m', strtotime($info['checkout_one_day_less']));
                        $from_day = date('d', strtotime($info['checkin']));
                        $to_day = date('d', strtotime($info['checkout_one_day_less']));

                        if($from_year == $to_year && $room_avail['y'] != $from_year){
                            continue;
                        }

                        if(($room_avail['y'] == $from_year && $room_avail['m'] < $from_month) || ($room_avail['y'] == $to_year && $room_avail['m'] > $to_month)){
                            continue;
                        }

                        if($room_avail['m'] == $from_month){
                            $start_day = (int)$from_day;
                        }else{
                            $start_day = 1;
                        }

                        if($room_avail['m'] == $to_month){
                            $end_day = $to_day;
                        }else{
                            $end_day = cal_days_in_month(CAL_GREGORIAN, $room_avail['m'], date('Y') + $room_avail['y']);
                        }
                        $sql = '';
                        for($i = $start_day; $i <= $end_day; $i++){
                            if($availability == 'increase'){
								$rooms_availabilities = $room_avail['d'.$i];
								// Limiting the upper limit for rooms
								$max_rooms = isset($arr_room_count[$room_id]) ? $arr_room_count[$room_id] : 0;
								$set_rooms = $rooms_availabilities + $info['rooms'] > $max_rooms ? $max_rooms : $rooms_availabilities + $info['rooms'];
                                if(!empty($sql)){
                                    $sql .= ', ';
                                }
                                $sql .= 'd'.$i.' = '.$set_rooms;
                            }else{
                                $rooms_availabilities = $room_avail['d'.$i];
								// Limiting the lower limit for rooms
								$set_rooms = $rooms_availabilities - $info['rooms'] < 0 ? 0 : $rooms_availabilities - $info['rooms'];
                                if(!empty($sql)){
                                    $sql .= ', ';
                                }
                                $sql .= 'd'.$i.' = '.($rooms_availabilities - $info['rooms']);
                            }
                        }
                        $sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET '.$sql.' WHERE id = '.$room_avail['id'];
						database_void_query($sql);
						
						// *** Channel manager is enabled
						// -----------------------------------------------------------------------------
						if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){
							ChannelManager::ChangeRoomAvailability($room_id, $booking_number, $info, $availability, $action_name);
						}
                    }
                }
            }
        }
    }

	/**
	 * Notification of customer after they stayed at the hotel
	 */
	public static function NotifyCustomerAfterStayed()
	{
		global $objSettings;
		$sql = 'SELECT 
				'.TABLE_BOOKINGS.'.customer_id,
				'.TABLE_CUSTOMERS.'.first_name,
				'.TABLE_CUSTOMERS.'.last_name,
				'.TABLE_CUSTOMERS.'.email,
				'.TABLE_CUSTOMERS.'.preferred_language,
				'.TABLE_BOOKINGS.'.booking_number,
				'.TABLE_BOOKINGS_ROOMS.'.id as booking_room_id,
				'.TABLE_BOOKINGS_ROOMS.'.hotel_id
			FROM '.TABLE_BOOKINGS.'
				INNER JOIN '.TABLE_CUSTOMERS.' ON '.TABLE_CUSTOMERS.'.id = '.TABLE_BOOKINGS.'.customer_id AND '.TABLE_CUSTOMERS.'.is_active = 1 AND '.TABLE_CUSTOMERS.'.is_removed = 0
				LEFT OUTER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS_ROOMS.'.booking_number = '.TABLE_BOOKINGS.'.booking_number  
			WHERE 
				'.TABLE_CUSTOMERS.'.email_notifications = 1
				AND '.TABLE_BOOKINGS.'.status = 3
				AND '.TABLE_BOOKINGS_ROOMS.'.email_notify = 0
				AND '.TABLE_BOOKINGS_ROOMS.'.checkout <= \''.date('Y-m-d').'\' 
				AND '.TABLE_BOOKINGS_ROOMS.'.checkout >= \''.date('Y-m-d', strtotime('-1 months')).'\'';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

		if($result[1] > 0){
			$customer_send_email = array();
			$customer_info = array();
			for($i = 0; $i < $result[1]; $i++){
				$customer_id = $result[0][$i]['customer_id'];
				$hotel_id = $result[0][$i]['hotel_id'];
				$b_room_id = $result[0][$i]['booking_room_id'];
				if(!isset($customer_send_email[$customer_id])){
					$customer_send_email[$customer_id] = array($hotel_id=>$b_room_id);
					// save email
					$customer_info[$customer_id] = array('first_name'=>$result[0][$i]['first_name'], 'last_name'=>$result[0][$i]['last_name'], 'email'=>$result[0][$i]['email'], 'language'=>$result[0][$i]['preferred_language']);
				}else{
					$customer_send_email[$customer_id][$hotel_id] = $b_room_id;
				}
			}
			$all_customers = array_keys($customer_send_email);

			// We are looking customers for all comments
			$sql = 'SELECT
					'.TABLE_REVIEWS.'.hotel_id,
					'.TABLE_REVIEWS.'.customer_id
				FROM '.TABLE_REVIEWS.'
				WHERE '.TABLE_REVIEWS.'.customer_id IN ('.implode(',', $all_customers).')';
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

			if($result[1] > 0){
				// Remove a hotel from the list for which customer replied
				for($i = 0; $i < $result[1]; $i++){
					$customer_id = $result[0][$i]['customer_id'];
					$hotel_id = $result[0][$i]['hotel_id'];
					if(isset($customer_send_email[$customer_id][$hotel_id])){
						unset($customer_send_email[$customer_id][$hotel_id]);
						if(empty($customer_send_email[$customer_id])){
							unset($customer_send_email[$customer_id]);
						}
					}
				}
			}

			if(!empty($customer_send_email)){
				$sender = $objSettings->GetParameter('admin_email');
				foreach($customer_send_email as $customer_id => $arr_hotel){
					reset($arr_hotel);
					$hotel_id = key($arr_hotel);
					$b_room_id = current($arr_hotel);
					if(send_email(
						$customer_info[$customer_id]['email'],
						$sender,
						'notice_leave_review',
						array(
							'{FIRST NAME}' => $customer_info[$customer_id]['first_name'],
							'{LAST NAME}' => $customer_info[$customer_id]['last_name'],
							'{LINK REVIEW}' => APPHP_BASE.'index.php?customer=my_reviews&mg_action=add&hid='.$hotel_id
						),
						$customer_info[$customer_id]['language']
					)){
						// Set the flag to not send notification twice per hotel
						$sql = 'UPDATE '.TABLE_BOOKINGS_ROOMS.'
								SET
									email_notify = 1
								WHERE id = '.(int)$b_room_id;
						database_void_query($sql);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Prepare Invoice Download
	 * @param $rid
	 */
	public function PrepareInvoiceDownload($rid = 0)
	{
		if(strtolower(SITE_MODE) == "demo"){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		global $objSettings;

		$sql = 'SELECT
					'.$this->tableName.'.'.$this->primaryKey.',
					'.$this->tableName.'.booking_number
				FROM '.$this->tableName.'
				WHERE
					'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$rid;
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		
        Pdf::config(array(
            'page_orientation'  => 'P',             // [P=portrait, L=landscape]
            'unit'              => 'mm',            // [pt=point, mm=millimeter, cm=centimeter, in=inch]
            'page_format'       => 'A4',
            'unicode'           => true,
            'encoding'          => 'UTF-8',
            'creator'           => 'uHotelBooking',
            'author'            => 'ApPHP',
            'title'             => _INVOICE.' ('._FOR_BOOKING.$result[0]['booking_number'].')',
            'subject'           => _INVOICE.' ('._FOR_BOOKING.$result[0]['booking_number'].')',
            'keywords'          => '',
            //'header_logo'     => '../../../templates/reports/images/logo.png',
            'header_logo_width' => '45',
            'header_title'      => 'Bookings #'.$result[0]['booking_number'],
            'header_enable'     => false,
            'text_shadow'       => false,
            'margin_top'        => '1',
            'margin_left'       => '5',
            'direction'         => Application::Get('lang_dir') 
        ));
        
		$data = $this->DrawBookingInvoice($rid, true, 'pdf', false);

		$base_path = $_SERVER['DOCUMENT_ROOT'].get_base_path();
        Pdf::createDocument($data, $base_path.'tmp/export/invoice', 'F'); // 'I' - inline, 'D' - download, 'F' - file,

        $this->message = _DOWNLOAD_INVOICE.' (PDF): <a href="javascript:void(\'pdf\')" onclick="javascript:appGoToPage(\'index.php?admin=export&file=invoice.pdf\');window.setTimeout(function(){location.reload()},2000);">'._FOR_BOOKING.$result[0]['booking_number'].'</a>';
		
		return true;
	}	

	public function SetViewModeSql($sql = '')
	{
		$this->VIEW_MODE_SQL = $sql;
	}
}
