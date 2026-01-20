<?php

/**
 *	Class ToursOrders
 *  --------------
 *  Description : encapsulates bookings properties
 *	Usage       : HotelBooking ONLY
 *  Updated	    : 11.02.2016
 *	Written by  : ApPHP
 *
 *	PUBLIC:						STATIC:						PRIVATE:                    PROTECTED
 *  -----------					-----------					-----------                 -------------------
 * __construct					RemoveExpired											OnItemCreated_ViewMode
 * __destruct
 * AfterDeleteRecord
 * BeforeDeleteRecord
 * CancelRecord
 * CleanUpCreditCardInfo
 * CleanUpReservation
 * DrawReservationDescription
 * DrawToursOrdersInvoice
 * GetTourReservationList
 * GetVatPercentDecimalPoints
 * PrepareInvoiceDownload
 * ReturnMoneyToBalance
 * SendInvoice
 * UpdatePaymentDate
 *
 **/


class ToursOrders extends MicroGrid {

    protected $debug = false;

    //------------------------------
    public $message;

    //------------------------------
    private $user_id;
    //private $rooms_amount;
    //private $reservation_number;
    private $reservation_status;
    //private $booking_customer_id;
    //private $revert_sum;
    private $allow_payment_with_balance;
    //private $fieldDateFormat;
    private $vat_included_in_price;
    private $online_credit_card_required;
    private $customers_cancel_reservation;
    private $default_currency_info;
    private $currencyFormat;
    private $arr_payment_types;
    private $arr_agencies_locations;
    private $arr_tours;
    private $arr_tours_types;
    private $arr_payment_methods;
    private $statuses_vm;
    private $sqlFieldDatetimeFormat = '';

    //==========================================================================
    // Class Constructor
    //==========================================================================
    function __construct($user_id = '') {

        parent::__construct();

        global $objSettings, $objLogin;

        $this->params = array();
        if(isset($_POST['status']))               $this->params['status'] = prepare_input($_POST['status']);
        if(isset($_POST['additional_info']))      $this->params['additional_info'] = prepare_input($_POST['additional_info']);
        if(isset($_POST['additional_payment']))   $this->params['additional_payment'] = prepare_input($_POST['additional_payment']);

        $this->vat_included_in_price      	= ModulesSettings::Get('tours_and_attractions', 'vat_included_in_price');
        $this->online_credit_card_required 	= ModulesSettings::Get('tours_and_attractions', 'online_credit_card_required');
        $this->customers_cancel_reservation = ModulesSettings::Get('tours_and_attractions', 'customers_cancel_reservation');

        $this->params['language_id'] = MicroGrid::GetParameter('language_id');
        $rid = self::GetParameter('rid');

        $this->primaryKey 	= 'id';
        $this->tableName 	= DB_PREFIX.'tours_reservations';

        //////////////////// TIL HERE

        $this->dataSet 		= array();
        $this->error 		= '';
        $this->message      = '';
        $this->reservation_number = '';
        $this->reservation_status = '';
        $this->booking_customer_id = '';
        $this->revert_sum = 0;
        $this->allow_payment_with_balance = ModulesSettings::Get('tours_and_attractions', 'allow_payment_with_balance') == 'yes' ? true : false;

        $arr_statuses = array('0'=>_PREORDERING, '1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED);
        $this->statuses_vm = array('0' => '<span style=color:#222222>'._PREORDERING.'</span>',
                                   '1' => '<span style=color:#0000a3>'._PENDING.'</span>',
                                   '2' => '<span style=color:#a3a300>'._RESERVED.'</span>',
                                   '3' => '<span style=color:#00a300>'._COMPLETED.'</span>',
                                   '4' => '<span style=color:#660000>'._REFUNDED.'</span>',
                                   '5' => '<span style=color:#a30000>'._PAYMENT_ERROR.'!</span>',
                                   '6' => '<span style=color:#939393>'._CANCELED.'</span>',
                                   '-1' => _UNKNOWN);

        $this->arr_payment_types = array('0'=>_PAY_ON_ARRIVAL, '1'=>_ONLINE, '2'=>_PAYPAL, '3'=>'2CO', '4'=>'Authorize.Net', '5'=>_BANK_TRANSFER, '6'=>_BALANCE, '7'=>_UNKNOWN);
        $this->arr_payment_methods = array('0'=>_PAYMENT_COMPANY_ACCOUNT, '1'=>_CREDIT_CARD, '2'=>_ECHECK, '3'=>_UNKNOWN);

        if($user_id != ''){
            $this->user_id = $user_id;
            $this->page = 'customer=my_tour_reservations';
            $this->actions = array('add'=>false, 'edit'=>false, 'details'=>false, 'delete'=>false);
            $arr_statuses_filter = array('1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED);
            $arr_statuses_edit = array('1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED);
            $arr_statuses_edit_completed = array('3'=>_COMPLETED, '4'=>_REFUNDED);
        }else{
            $this->user_id = '';
            $this->page = 'admin=mod_tours_and_attractions_orders_management';

            $this->actions = array('add'=>false, 'edit'=>true, 'details'=>true, 'delete'=>true);
            $arr_statuses_filter = array('0'=>_PREORDERING, '1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED, '5'=>_PAYMENT_ERROR, '6'=>_CANCELED);
            $arr_statuses_edit = array('1'=>_PENDING, '2'=>_RESERVED, '3'=>_COMPLETED, '4'=>_REFUNDED);
            $arr_statuses_edit_completed = array('3'=>_COMPLETED, '4'=>_REFUNDED);
        }
        $this->actionIcons  = true;
        $this->allowRefresh = true;
        $this->formActionURL = 'index.php?'.$this->page;

        $this->allowLanguages = false;
        $this->languageId  	= ''; // ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();

        $this->WHERE_CLAUSE = '';
        $agencies_list = '';
        if($this->user_id != ''){
            //'.$this->tableName.'.is_admin_reservation = 0 AND
            $this->WHERE_CLAUSE = 'WHERE '.$this->tableName.'.status <> 0 AND
			                             '.$this->tableName.'.customer_id = '.(int)$this->user_id;
        }
        $this->GROUP_BY_CLAUSE = '';//'GROUP BY '.$this->tableName.'.reservation_number';
        $this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.created_date DESC'; // ORDER BY date_created DESC

        $this->isAlterColorsAllowed = true;
        $this->isPagingAllowed = true;
        $this->pageSize = 30;
        $this->isSortingAllowed = true;
        $this->isExportingAllowed = ($user_id != '') ? false : true;
        $this->arrExportingTypes = array('csv'=>true);

        $date_format_settings = get_date_format('view', true);

        // prepare tours array
        $this->arr_tours = [];
        $total_tours = Tours::GetAllActive();
        foreach($total_tours[0] as $key => $val){
            $this->arr_tours[$val['id']] = $val['name'];
        }

        // prepare tours types array
        $this->arr_tours_types = [];
        $total_tours_types = ToursTypes::GetAllTypes();
        foreach($total_tours_types[0] as $key => $val){
            $this->arr_tours_types[$val['id']] = $val['name'];
        }


        // prepare countries array
        $total_countries = Countries::GetAllCountries();
        $arr_countries = array();
        foreach($total_countries[0] as $key => $val){
            $arr_countries[$val['abbrv']] = $val['name'];
        }

        // prepare locations array
        $tours_locations = ToursLocations::GetAllLocations();
        foreach ($tours_locations[0] as $key => $val) {
            $this->arr_agencies_locations[$val['id']] = $val['name'];
        }


        $this->default_currency_info = Currencies::GetDefaultCurrencyInfo();
        if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
            $this->fieldDateFormat = 'M d, Y';
            $this->sqlFieldDateFormat = '%b %d, %Y';
            $this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
        }else{
            $this->fieldDateFormat = 'd M, Y';
            $this->sqlFieldDateFormat = '%d %b, %Y';
            $this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
        }
        $this->currencyFormat = get_currency_format();

        $this->isFilteringAllowed = true;
        $this->maxFilteringColumns = 6;
        // define filtering fields
        $this->arrFilteringFields = array(
            '#' => array('table'=>$this->tableName, 'field'=>'reservation_number', 'type'=>'text', 'sign'=>'like%', 'width'=>'90px'),
        );

        if(empty($this->user_id)) $this->arrFilteringFields[_CUSTOMER] = array('table'=>TABLE_CUSTOMERS, 'field'=>'last_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'90px');
        if(empty($this->user_id)) $this->arrFilteringFields[_STATUS] = array('table'=>$this->tableName, 'field'=>'status', 'type'=>'dropdownlist', 'source'=>$arr_statuses_filter, 'sign'=>'=', 'width'=>'120px');
        if(empty($this->user_id)) $this->arrFilteringFields[_TOUR] = array('table'=>$this->tableName, 'field'=>'tour_type_id', 'type'=>'dropdownlist', 'source'=>$this->arr_tours, 'sign'=>'=', 'width'=>'120px');

        $this->arrFilteringFields[_FROM] = array('table'=>$this->tableName, 'field'=>'date_from', 'type'=>'calendar', 'date_format'=>$date_format_settings, 'sign'=>'>=', 'width'=>'100px', 'visible'=>true);
        $this->arrFilteringFields[_TO] = array('table'=>$this->tableName, 'field'=>'date_to', 'type'=>'calendar', 'date_format'=>$date_format_settings, 'sign'=>'<=', 'width'=>'100px', 'visible'=>true);

        $this->isAggregateAllowed = true;
        // define aggregate fields for View Mode
        $this->arrAggregateFields = array(
            'tp_w_currency' => array('function'=>'SUM', 'align'=>'center', 'aggregate_by'=>'tp_wo_default_currency', 'decimal_place'=>2, 'sign'=>$this->default_currency_info['symbol']),
            //'tpaid_w_currency' => array('function'=>'SUM', 'align'=>'center', 'aggregate_by'=>'tp_wo_default_currency', 'decimal_place'=>2, 'sign'=>$this->default_currency_info['symbol']),
        );

        //----------------------------------------------------------------------
        // VIEW MODE
        //----------------------------------------------------------------------

        // set locale time names
        $this->SetLocale(Application::Get('lc_time_name'));

        $this->VIEW_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.reservation_number,
								'.$this->tableName.'.location_id,
								DATE_FORMAT('.$this->tableName.'.date_from, "'.$this->sqlFieldDateFormat.'") date_from,
								DATE_FORMAT('.$this->tableName.'.date_to, "'.$this->sqlFieldDateFormat.'") date_to,
								CONCAT(DATE_FORMAT('.$this->tableName.'.date_from, "'.$this->sqlFieldDateFormat.'"), " ", DATE_FORMAT('.$this->tableName.'.time_from, "%H:%i")) date_time_from,
								CONCAT(DATE_FORMAT('.$this->tableName.'.date_to, "'.$this->sqlFieldDateFormat.'"), " ", DATE_FORMAT('.$this->tableName.'.time_to, "%H:%i")) date_time_to,
								CONCAT(DATE_FORMAT('.$this->tableName.'.date_from, "'.$this->sqlFieldDateFormat.'"), "<br>", DATE_FORMAT('.$this->tableName.'.date_to, "'.$this->sqlFieldDateFormat.'")) date_from_to,
								'.$this->tableName.'.tour_type_id,
								'.$this->tableName.'.tour_id,
								DATE_FORMAT('.$this->tableName.'.created_date, "'.$this->sqlFieldDateFormat.'") as created_date_formated,
								'.$this->tableName.'.customer_id,
								IF('.$this->tableName.'.is_admin_reservation = 0, CONCAT("<a href=\"javascript:void(\'customer|view\')\" onclick=\"open_popup(\'popup.ajax.php\',\'customer\',\'",'.$this->tableName.'.customer_id,"\',\'",'.TABLE_CUSTOMERS.'.customer_type,"\',\''.Application::Get('token').'\')\">", CONCAT('.TABLE_CUSTOMERS.'.last_name, " ", '.TABLE_CUSTOMERS.'.first_name, " (", '.TABLE_CUSTOMERS.'.b_country, ")"), "</a>"), "{administrator}") as customer_name,
								'.$this->tableName.'.payment_type,
								'.$this->tableName.'.payment_method,
								IF('.$this->tableName.'.status > 6, -1, '.$this->tableName.'.status) as status,
								'.TABLE_CURRENCIES.'.symbol,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = \'before\' THEN
										CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.reservation_total_price) 
					                ELSE
										CONCAT('.$this->tableName.'.reservation_total_price, '.TABLE_CURRENCIES.'.symbol)
								END as tp_w_currency,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = \'before\' THEN
										CONCAT('.TABLE_CURRENCIES.'.symbol, ('.$this->tableName.'.reservation_paid + '.$this->tableName.'.additional_payment)) 
					                ELSE
										CONCAT(('.$this->tableName.'.reservation_paid + '.$this->tableName.'.additional_payment), '.TABLE_CURRENCIES.'.symbol)
								END as tpaid_w_currency,
								CONCAT("<nobr><a href=\"javascript:void(\'description\')\" title=\"'._DESCRIPTION.'\" onclick=\"javascript:__mgDoPostBack(\''.$this->tableName.'\', \'description\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\')\">[ ", "'._DESCRIPTION.'", " ]</a></nobr>") as link_order_description,
								('.$this->tableName.'.reservation_total_price) as tp_wo_currency,
								(('.$this->tableName.'.reservation_total_price) / '.TABLE_CURRENCIES.'.rate) as tp_wo_default_currency,
								IF(
									'.$this->tableName.'.status = 2 AND ((SELECT DATEDIFF(cr.date_from, \''.@date('Y-m-d').'\') FROM '.$this->tableName.' cr WHERE cr.date_from > \''.@date('Y-m-d').'\' AND cr.reservation_number = '.$this->tableName.'.reservation_number LIMIT 0, 1) >= '.$this->customers_cancel_reservation.'),
									CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'._BUTTON_CANCEL.'\" onclick=\"javascript:__mgMyDoPostBack(\''.$this->tableName.'\', \'cancel\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\">[ '._BUTTON_CANCEL.' ]</a></nobr>"),
									"<span class=lightgray>'._BUTTON_CANCEL.'</span>"
								) as link_cust_order_cancel,
								IF('.$this->tableName.'.status != 6, CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'._BUTTON_CANCEL.'\" onclick=\"javascript:__mgMyDoPostBack(\''.$this->tableName.'\', \'cancel\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\">[ '._BUTTON_CANCEL.' ]</a></nobr>"), "<span class=lightgray>'._BUTTON_CANCEL.'</span>") as link_admin_order_cancel,
								IF(('.$this->tableName.'.status = 2 OR '.$this->tableName.'.status = 3), CONCAT("<nobr><a href=\"javascript:void(\'invoice\')\" title=\"'._INVOICE.'\" onclick=\"javascript:__mgDoPostBack(\''.$this->tableName.'\', \'invoice\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\')\">[ ", "'._INVOICE.'", " ]</a></nobr>"), "<span class=lightgray>'._INVOICE.'</span>") as link_order_invoice
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
								LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
							';

        // define view mode fields
        if($this->user_id != ''){
            $this->arrViewModeFields = array(
                //'created_date_formated'   => array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'height'=>'', 'nowrap'=>'nowrap', 'maxlength'=>''),
                'reservation_number'   	  	=> array('title'=>'#', 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'height'=>'', 'maxlength'=>''),
                'location_id'   		    => array('title'=>_LOCATION, 'type'=>'enum',  'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_agencies_locations),
                //'date_from'    			=> array('title'=>_DATE.'/'._TIME, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'height'=>'', 'maxlength'=>''),
                //'date_to'    				=> array('title'=>_DATE.'/'._TIME, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'height'=>'', 'maxlength'=>''),
                'date_from_to'    			=> array('title'=>_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'height'=>'', 'maxlength'=>''),
                'tour_id'   			    => array('title'=>_TOUR, 'type'=>'enum',  'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_tours),
                //'tour_type_id'   	=> array('title'=>_TOUR_TYPE, 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_tours_types),
                //'customer_name'   		=> array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>''),
                //'payment_type'           	=> array('title'=>_METHOD, 'type'=>'enum',  'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'source'=>$this->arr_payment_types),
                'tp_w_currency'   		  	=> array('title'=>_PAYMENT, 'type'=>'label', 'align'=>'right', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'tp_wo_currency', 'sort_type'=>'numeric', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
                'tpaid_w_currency'   		=> array('title'=>_PAID_SUM, 'type'=>'label', 'align'=>'right', 'width'=>'70px', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'tp_wo_currency', 'sort_type'=>'numeric', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
                'status'                 	=> array('title'=>_STATUS, 'type'=>'enum',  'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->statuses_vm),
                'link_order_description'    => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'nowrap'=>'nowrap'),
                'link_order_invoice'     	=> array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'75px', 'height'=>'', 'maxlength'=>'', 'nowrap'=>'nowrap'),
                'link_cust_order_cancel'    => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'nowrap'=>'nowrap', 'visible'=>(($this->customers_cancel_reservation > '0') ? true : false)),
            );
        }else{
            $this->arrViewModeFields = array(
                //'created_date_formated'   	=> array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'height'=>'', 'nowrap'=>'nowrap', 'maxlength'=>''),
                'reservation_number'   	  	=> array('title'=>_RESERVATION, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'height'=>'', 'maxlength'=>''),
                'tour_id'   			    => array('title'=>_TOUR, 'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_tours, 'pre_html'=>' &nbsp; '),
                //'location_id'   		    => array('title'=>_LOCATION, 'type'=>'enum',  'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_agencies_locations),
                'date_from_to'    			=> array('title'=>_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'140px', 'height'=>'', 'maxlength'=>''),
                //'tour_type_id'   	=> array('title'=>_TOUR_TYPE, 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_tours_types),
                //'payment_type'            	=> array('title'=>_METHOD, 'type'=>'enum',  'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'source'=>$this->arr_payment_types),
                'customer_name'   		  	=> array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>''),
                'tp_w_currency'   		  	=> array('title'=>_PAYMENT, 'type'=>'label', 'align'=>'right', 'width'=>'70px', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'tp_wo_currency', 'sort_type'=>'numeric', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
                'tpaid_w_currency'   		=> array('title'=>_PAID_SUM, 'type'=>'label', 'align'=>'right', 'width'=>'70px', 'height'=>'', 'maxlength'=>'', 'sort_by'=>'tp_wo_currency', 'sort_type'=>'numeric', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
                'status'                  	=> array('title'=>_STATUS, 'type'=>'enum',  'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->statuses_vm),
                'link_order_description'    => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>''),
                'link_order_invoice'      	=> array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>''),
                'link_admin_order_cancel'   => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>'', 'visible'=>true),
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
								'.$this->tableName.'.reservation_number,
								'.$this->tableName.'.reservation_number as reservation_number_label,
								'.$this->tableName.'.reservation_description,
								'.$this->tableName.'.additional_info,
								'.$this->tableName.'.reservation_total_price,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = "before" THEN CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.reservation_paid)
					                ELSE CONCAT('.$this->tableName.'.reservation_paid, '.TABLE_CURRENCIES.'.symbol)
								END as reservation_paid,
								CONCAT(
									'.TABLE_CURRENCIES.'.symbol,
									IF(
										('.$this->tableName.'.reservation_total_price - ('.$this->tableName.'.reservation_paid + '.$this->tableName.'.additional_payment) > 0),
										('.$this->tableName.'.reservation_total_price - ('.$this->tableName.'.reservation_paid + '.$this->tableName.'.additional_payment)),
										0
									)									
								) as mod_have_to_pay,
								'.$this->tableName.'.additional_payment,
								'.$this->tableName.'.vat_fee,
								'.$this->tableName.'.vat_percent,
								CASE
									WHEN '.TABLE_CURRENCIES.'.symbol_placement = "before" THEN CONCAT('.TABLE_CURRENCIES.'.symbol, '.$this->tableName.'.vat_fee)
					                ELSE CONCAT('.$this->tableName.'.vat_fee, '.TABLE_CURRENCIES.'.symbol)
								END as mod_vat_fee,
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
								DATE_FORMAT('.$this->tableName.'.created_date, "'.$this->sqlFieldDatetimeFormat.'") as created_date_formated,
								DATE_FORMAT('.$this->tableName.'.payment_date, "'.$this->sqlFieldDatetimeFormat.'") as payment_date_formated,
								'.$this->tableName.'.transaction_number,
								'.$this->tableName.'.payment_date,
								'.$this->tableName.'.payment_type,
								'.$this->tableName.'.payment_method,
								'.$this->tableName.'.status,
								'.$this->tableName.'.status_description,
								
								"" as hold
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
								LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
							';

        if($this->user_id != ''){
            //'.$this->tableName.'.is_admin_reservation = 0 AND
            $WHERE_CLAUSE = 'WHERE '.$this->tableName.'.customer_id = '.$this->user_id.' AND
			                       '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
            $this->EDIT_MODE_SQL = $this->EDIT_MODE_SQL.' WHERE TRUE = FALSE';
        }else{
            $WHERE_CLAUSE = 'WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
            $this->EDIT_MODE_SQL = $this->EDIT_MODE_SQL.$WHERE_CLAUSE;
        }

        // prepare trigger
        $sql = 'SELECT
					vat_percent,
					status,
					payment_type,
					IF(TRIM(cc_number) = "" OR LENGTH(AES_DECRYPT(cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")) <= 4, "hide", "show") as cc_number_trigger
				FROM '.$this->tableName.' WHERE id = '.(int)$rid;
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $cc_number_trigger = $result[0]['cc_number_trigger'];
            $status_trigger = $result[0]['status'];
            $payment_type = $result[0]['payment_type'];
            $vat_percent = ' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).')';
        }else{
            $cc_number_trigger = 'hide';
            $status_trigger = '0';
            $payment_type = '0';
            $vat_percent = '';
        }

        // define edit mode fields
        $this->arrEditModeFields = array(
            'reservation_number_label' 	=> array('title'=>_RESERVATION_NUMBER, 'type'=>'label'),
            'reservation_number'     	=> array('title'=>'', 'type'=>'hidden', 'required'=>false, 'default'=>''),
            'customer_id'        		=> array('title'=>'', 'type'=>'hidden', 'required'=>false, 'default'=>''),

            'created_date_formated' 	=> array('title'=>_DATE_CREATED, 'type'=>'label'),
            'payment_date_formated' 	=> array('title'=>_DATE_PAYMENT, 'type'=>'label'),
            'payment_type'       		=> array('title'=>_PAYMENT_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>true, 'default'=>'', 'source'=>$this->arr_payment_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
            'payment_method'     		=> array('title'=>_PAYMENT_METHOD, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>true, 'default'=>'', 'source'=>$this->arr_payment_methods, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
            'mod_vat_fee'        		=> array('title'=>_VAT, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'post_html'=>$vat_percent),
            'reservation_paid'    		=> array('title'=>_PAID_SUM, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
            'additional_payment' 		=> array('title'=>_ADDITIONAL_PAYMENT, 'header_tooltip'=>_ADDITIONAL_PAYMENT_TOOLTIP, 'type'=>'textbox',  'width'=>'100px', 'required'=>false, 'readonly'=>(($status_trigger == '2' || $status_trigger == '3') ? false : true), 'maxlength'=>'10', 'default'=>'', 'validation_type'=>'float', 'validation_maximum'=>'10000000', 'unique'=>false, 'visible'=>true, 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
            'mod_have_to_pay'    		=> array('title'=>_PAYMENT_REQUIRED, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2'),
            'status'  			 		=> array('title'=>_STATUS, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>(($status_trigger >= '3') ? true : false), 'source'=>$arr_statuses_edit),
        );
        if($this->user_id != ''){
            $this->arrEditModeFields['status'] = array('title'=>_STATUS, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>(($status_trigger >= '3') ? true : false), 'source'=>$arr_statuses_edit);
        }else{
            $this->arrEditModeFields['status'] = array('title'=>_STATUS, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'source'=>(($status_trigger >= '3') ? $arr_statuses_edit_completed : $arr_statuses_edit));
            $this->arrEditModeFields['status_description'] = array('title'=>_STATUS.' ('._DESCRIPTION.')', 'type'=>'label');

            if($payment_type == '1'){ // on-line orders only!
                $this->arrEditModeFields['cc_type'] 		= array('title'=>_CREDIT_CARD_TYPE, 'type'=>'label');
                $this->arrEditModeFields['cc_holder_name'] 	= array('title'=>_CREDIT_CARD_HOLDER_NAME, 'type'=>'label');
                $this->arrEditModeFields['m_cc_number'] 	= array('title'=>_CREDIT_CARD_NUMBER, 'type'=>'label', 'post_html'=>(($cc_number_trigger == 'show') ? '&nbsp;[ <a href="javascript:void(0);" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) __mgDoPostBack(\''.$this->tableName.'\', \'clean_credit_card\', \''.$rid.'\')">'._REMOVE.'</a> ]' : ''));
                $this->arrEditModeFields['m_cc_expires_date'] = array('title'=>_CREDIT_CARD_EXPIRES, 'type'=>'label');
                $this->arrEditModeFields['m_cc_cvv_code'] 	= array('title'=>_CVV_CODE, 'type'=>'label');
            }
            $this->arrEditModeFields['additional_info'] = array('title'=>_ADDITIONAL_INFO, 'type'=>'textarea', 'width'=>'390px', 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'validation_maxlength'=>'1024', 'unique'=>false);
        }

        //----------------------------------------------------------------------
        // DETAILS MODE
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = $this->VIEW_MODE_SQL.$WHERE_CLAUSE;

        $this->arrDetailsModeFields = array(

            'reservation_number'  	  => array('title'=>_RESERVATION_NUMBER, 'type'=>'label'),
            'reservation_description' => array('title'=>_DESCRIPTION, 'type'=>'label'),
            'reservation_total_price' => array('title'=>_ORDER_PRICE, 'type'=>'label'),
            'vat_fee'  		     => array('title'=>_VAT, 'type'=>'label'),
            'vat_percent'  		 => array('title'=>_VAT_PERCENT, 'type'=>'label'),
            'reservation_paid'  		 => array('title'=>_PAYMENT_SUM, 'type'=>'label'),
            'currency'  		 => array('title'=>_CURRENCY, 'type'=>'label'),
            'customer_name'      => array('title'=>_CUSTOMER, 'type'=>'html'),
            'transaction_number' => array('title'=>_TRANSACTION, 'type'=>'label'),
            'payment_date_formated' => array('title'=>_DATE, 'type'=>'label'),
            'payment_type'       => array('title'=>_PAYMENT_TYPE, 'type'=>'enum', 'source'=>$this->arr_payment_types),
            'payment_method'     => array('title'=>_PAYMENT_METHOD, 'type'=>'enum', 'source'=>$this->arr_payment_methods),
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
     *	Cancel record
     */
    public function CancelRecord($rid)
    {
        global $objLogin;

        if(strtolower(SITE_MODE) == 'demo'){
            $this->error = _OPERATION_BLOCKED;
            return false;
        }

        $sql = 'UPDATE '.$this->tableName.'
				SET
					status = 6,
					status_changed = \''.date('Y-m-d H:i:s').'\',
					status_description = \''.encode_text(($objLogin->IsLoggedInAsAdmin() ? _CANCELED_BY_ADMIN : _CANCELED_BY_CUSTOMER)).'\'
				WHERE '.$this->primaryKey.' = '.(int)$rid;
        if(!database_void_query($sql)) return false;

        // Check if this is "by balance" payment and return money back to account balance
        $this->ReturnMoneyToBalance($rid);

        return true;
    }

    /**
     *	Draws order invoice
     */
    public function DrawToursOrdersInvoice($rid, $text_only = false, $type = 'html', $draw = true)
    {
        global $objSiteDescription, $objSettings, $objLogin;

        $output = '';
        $output_pdf = array();
        $oid = isset($rid) ? (int)$rid : '0';
        $language_id = Languages::GetDefaultLang();

        $sql = 'SELECT
				'.$this->tableName.'.'.$this->primaryKey.',
				'.$this->tableName.'.reservation_number,
				'.$this->tableName.'.reservation_description,
				'.$this->tableName.'.additional_info,
				'.$this->tableName.'.reservation_total_price,
				'.$this->tableName.'.vat_fee,
				'.$this->tableName.'.vat_percent,
				'.$this->tableName.'.reservation_paid,
				'.$this->tableName.'.pre_payment_type,
				'.$this->tableName.'.pre_payment_value,
				'.$this->tableName.'.status,
				IF(
					('.$this->tableName.'.reservation_total_price - ('.$this->tableName.'.reservation_paid + '.$this->tableName.'.additional_payment) > 0),
					('.$this->tableName.'.reservation_total_price - ('.$this->tableName.'.reservation_paid + '.$this->tableName.'.additional_payment)),
					0
				) as mod_have_to_pay,								
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
				'.TABLE_COUNTRIES_DESCRIPTION.'.name as country_name
			FROM '.$this->tableName.'
				LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
				LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
				LEFT OUTER JOIN '.TABLE_COUNTRIES.' ON '.TABLE_CUSTOMERS.'.b_country = '.TABLE_COUNTRIES.'.abbrv AND '.TABLE_COUNTRIES.'.is_active = 1
				LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' ON '.TABLE_COUNTRIES.'.id = '.TABLE_COUNTRIES_DESCRIPTION.'.country_id AND '.TABLE_COUNTRIES_DESCRIPTION.'.language_id = \''.$language_id.'\'
			WHERE
				'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$oid;

        if($this->user_id != ''){
            $sql .= ' AND '.$this->tableName.'.is_admin_reservation = 0 AND '.$this->tableName.'.customer_id = '.(int)$this->user_id;
        }

        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            // for pdf export
            if($type == 'pdf'){
                $output_pdf['is_admin_reservation'] = ($result[0]['is_admin_reservation'] == '1') ? '1' : '0';
                $output_pdf['first_name'] = ($result[0]['is_admin_reservation'] == '0') ? _FIRST_NAME.': '.$result[0]['first_name'] : '';
                $output_pdf['last_name'] = ($result[0]['is_admin_reservation'] == '0') ? _LAST_NAME.': '.$result[0]['last_name'] : '';
                $output_pdf['email'] = ($result[0]['is_admin_reservation'] == '0') ? _EMAIL_ADDRESS.': '.$result[0]['customer_email'] : '';
                $output_pdf['company'] = ($result[0]['is_admin_reservation'] == '0') ? _COMPANY.': '.$result[0]['customer_company'] : '';
                $output_pdf['phones'] = '';
                if($result[0]['phone'] != '' || $result[0]['fax'] != ''){
                    if($result[0]['phone'] != '') $output_pdf['phones'] .= _PHONE.': '.$result[0]['phone'];
                    if($result[0]['phone'] != '' && $result[0]['fax'] != '') $output_pdf['phones'] .= ', ';
                    if($result[0]['fax'] != '') $output_pdf['phones'] .= _FAX.': '.$result[0]['fax'];
                }
                $output_pdf['address'] = ($result[0]['is_admin_reservation'] == '0') ? _ADDRESS.': '.$result[0]['b_address'].' '.$result[0]['b_address_2'] : '';
                $output_pdf['city'] = ($result[0]['is_admin_reservation'] == '0') ? $result[0]['b_city'].' '.$result[0]['b_state'] : '';
                $output_pdf['country'] = ($result[0]['is_admin_reservation'] == '0') ? $result[0]['country_name'].' '.$result[0]['b_zipcode'] : '';
                $output_pdf['created_date'] = $result[0]['created_date_formated'];
                $output_pdf['reservation_status'] = strip_tags($this->statuses_vm[$result[0]['status']]);
                $output_pdf['reservation_number'] = $result[0]['reservation_number'];
                $output_pdf['reservation_description'] = $result[0]['reservation_description'];
                $output_pdf['transaction_number'] = $result[0]['transaction_number'];
                $output_pdf['payment_date'] = $result[0]['payment_date_formated'];
                $output_pdf['payment_type'] = $this->arr_payment_types[$result[0]['payment_type']];
                $output_pdf['payment_method'] = $this->arr_payment_methods[$result[0]['payment_method']];
                $output_pdf['booking_price'] = Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
                $output_pdf['booking_currency'] = $result[0]['symbol'];

                $output_pdf['booking_subtotal'] = Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
                //$output_pdf['extras_subtotal'] = (!empty($result[0]['extras'])) ? Currencies::PriceFormat($result[0]['extras_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat) : '';
                $output_pdf['vat_fee'] = ($this->vat_included_in_price == 'no') ? (Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).') ') : '';

                if($result[0]['pre_payment_type'] == 'percentage' && $result[0]['pre_payment_value'] > 0 && $result[0]['pre_payment_value'] < 100){
                    $output_pdf['pre_payment'] = Currencies::PriceFormat($result[0]['reservation_paid'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.$result[0]['pre_payment_value'].'%)';
                }elseif($result[0]['pre_payment_type'] == 'fixed sum' && $result[0]['pre_payment_value'] > 0){
                    $output_pdf['pre_payment'] = Currencies::PriceFormat($result[0]['reservation_paid'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['pre_payment_value'], $result[0]['symbol'], $result[0]['symbol_placement']).')';
                }else{
                    $output_pdf['pre_payment'] = _FULL_PRICE;
                }
                $output_pdf['additional_payment'] = Currencies::PriceFormat($result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
                $output_pdf['total'] = Currencies::PriceFormat($result[0]['reservation_paid'] + $result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
                $output_pdf['payment_required'] = ($result[0]['mod_have_to_pay'] != 0) ? Currencies::PriceFormat($result[0]['mod_have_to_pay'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat) : '';
            }


            $part = '<table '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').' width="100%" border="0" cellspacing="0" cellpadding="0">';
            if($text_only && ModulesSettings::Get('tours_and_attractions', 'mode') == 'TEST MODE'){
                $part .= '<tr><td colspan="2"><div style="text-align:center;padding:10px;color:#a60000;border:1px dashed #a60000;width:100px">TEST MODE!</div></td></tr>';
            }

            $part .= '<tr><td colspan="2">'._DATE_CREATED.': '.$result[0]['created_date_formated'].'</td></tr>
				      <tr><td colspan="2" nowrap="nowrap" height="10px"></td></tr>';
            $part .= '<tr>
					<td valign="top">						
						<h3>'._CUSTOMER_DETAILS.':</h3>';
            if($result[0]['is_admin_reservation'] == '1'){
                $part .= _ADMIN_RESERVATION.'<br />';
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
                $part .= _ADDRESS.': '.$result[0]['b_address'].' '.$result[0]['b_address_2'].'<br />';
                $part .= $result[0]['b_city'].' '.$result[0]['b_state'].'<br />';
                $part .= $result[0]['country_name'].' '.$result[0]['b_zipcode'];
            }
            $part .= '</td>
					<td valign="top" align="'.Application::Get('defined_right').'">';

//            $agency_info = [];
//            $part .= '<h3>'.$agency_info['name'].'</h3>';
//            $part .= _ADDRESS.': '.$agency_info['address'].'<br />';
//            $part .= _PHONE.': '.$agency_info['phone'].'<br />';
//            $part .= _FAX.': '.$agency_info['fax'].'<br />';
//            $part .= _EMAIL_ADDRESS.': '.$agency_info['email'].'<br />';

            $part .= '</td>
				</tr>
				<tr><td colspan="2" nowrap="nowrap" height="10px"></td></tr>
				<tr>
					<td colspan="2">';
            $part .= '<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border:1px solid #d1d2d3">';
            $part .= '<tr style="background-color:#e1e2e3;font-weight:bold;font-size:13px;"><th align="left" colspan="2">&nbsp;<b>'._BOOKING_DETAILS.'</b></th></tr>';
            $part .= '<tr><td width="25%">&nbsp;'._STATUS.': </td><td><b>'.strip_tags($this->statuses_vm[$result[0]['status']]).'</b></td></tr>';
            $part .= '<tr><td width="25%">&nbsp;'._RESERVATION_NUMBER.': </td><td>'.$result[0]['reservation_number'].'</td></tr>';
            $part .= '<tr><td>&nbsp;'._DESCRIPTION.': </td><td>'.$result[0]['reservation_description'].'</td></tr>';
            $part .= '</table><br />';

            $part .= '<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border:1px solid #d1d2d3">';
            $part .= '<tr style="background-color:#e1e2e3;font-weight:bold;font-size:13px;"><th align="left" colspan="2">&nbsp;<b>'._PAYMENT_DETAILS.'</b></th></tr>';
            $part .= '<tr><td width="25%">&nbsp;'._TRANSACTION.': </td><td>'.$result[0]['transaction_number'].'</td></tr>';
            $part .= '<tr><td>&nbsp;'._DATE_PAYMENT.': </td><td>'.$result[0]['payment_date_formated'].'</td></tr>';
            $part .= '<tr><td>&nbsp;'._PAYMENT_TYPE.': </td><td>'.$this->arr_payment_types[$result[0]['payment_type']].'</td></tr>';
            $part .= '<tr><td>&nbsp;'._PAYMENT_METHOD.': </td><td>'.$this->arr_payment_methods[$result[0]['payment_method']].'</td></tr>';
            $part .= '<tr><td>&nbsp;'._PRICE.': </td><td>'.Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
            //$part .= '<tr><td>&nbsp;'._SUBTOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
            //if(!empty($result[0]['extras'])) $part .= '<tr><td>&nbsp;'._EXTRAS_SUBTOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['extras_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
            if($this->vat_included_in_price == 'no') $part .= '<tr><td>&nbsp;'._VAT.': </td><td>'.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).')</td></tr>';
            $part .= '<tr><td>&nbsp;'._PAYMENT_SUM.': </td><td>'.Currencies::PriceFormat($result[0]['reservation_paid'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';

            if($result[0]['pre_payment_type'] == 'percentage' && $result[0]['pre_payment_value'] > 0 && $result[0]['pre_payment_value'] < 100){
                $part .= '<tr><td>&nbsp;'._PRE_PAYMENT.'</td><td>'.Currencies::PriceFormat($result[0]['reservation_paid'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.$result[0]['pre_payment_value'].'%)</td></tr>';
            }elseif($result[0]['pre_payment_type'] == 'fixed sum' && $result[0]['pre_payment_value'] > 0){
                $part .= '<tr><td>&nbsp;'._PRE_PAYMENT.'</td><td>'.Currencies::PriceFormat($result[0]['reservation_paid'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['pre_payment_value'], $result[0]['symbol'], $result[0]['symbol_placement']).')</td></tr>';
            }else{
                $part .= '<tr><td>&nbsp;'._PRE_PAYMENT.'</td><td>'._FULL_PRICE.'</td></tr>';
            }
            $part .= '<tr><td>&nbsp;'._ADDITIONAL_PAYMENT.': </td><td>'.Currencies::PriceFormat($result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
            $part .= '<tr><td>&nbsp;'._TOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['reservation_paid'] + $result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
            if($result[0]['mod_have_to_pay'] != 0){
                $part .= '<tr><td style="color:#960000">&nbsp;'._PAYMENT_REQUIRED.': </td><td style="color:#960000">'.Currencies::PriceFormat($result[0]['mod_have_to_pay'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
            }
            $part .= '</table><br />';
            $part .= '</td>';
            $part .= '</tr>';
            $part .= '</table>';

            $content = @file_get_contents('html/templates/invoice_tour.tpl');
            if($content){
                $content = str_replace('_TOP_PART_', $part, $content);
                $content = str_replace('_TOUR_DETAILS_', $this->GetTourReservationList($oid, $language_id, 'invoice'), $content);
                $content = str_replace('_YOUR_COMPANY_NAME_', $objSiteDescription->GetParameter('header_text'), $content);
                $content = str_replace('_ADMIN_EMAIL_', $objSettings->GetParameter('admin_email'), $content);
                $content = str_replace('_ALL_RIGHTS_RESERVED_', '', $content);
                $content = str_replace('_INVOICE_DISCLAIMER_LINE_', '', $content);
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
            return ($type == 'pdf') ? $output_pdf : $output;
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
					tr.reservation_number,
					IF(is_admin_reservation = 1, a.email, c.email) as email,
					IF(is_admin_reservation = 1, a.preferred_language, c.preferred_language) as preferred_language
				FROM '.$this->tableName.' tr
					LEFT OUTER JOIN '.TABLE_CUSTOMERS.' c ON tr.customer_id = c.id
					LEFT OUTER JOIN '.TABLE_ACCOUNTS.' a ON tr.customer_id = a.id
				WHERE tr.id = '.(int)$rid;
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){

            $recipient = $result[0]['email'];
            $sender    = $objSettings->GetParameter('admin_email');
            $subject   = _INVOICE.' ('._FOR_BOOKING.$result[0]['reservation_number'].')';
            $body      = $this->DrawToursOrdersInvoice($rid, true, 'html', false);
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
     * Draws Resrvation Description
     * @param $rid
     * @param $mode
     * @param $draw
     */
    public function DrawReservationDescription($rid, $mode = '', $draw = true)
    {
        global $objLogin;

        $output = '';
        $content = '';
        $oid = isset($rid) ? (int)$rid : '0';
        $language_id = Application::Get('lang');

        $sql = 'SELECT
				'.$this->tableName.'.'.$this->primaryKey.',
				'.$this->tableName.'.reservation_number,
				'.$this->tableName.'.reservation_description,
				'.$this->tableName.'.additional_info,
				'.$this->tableName.'.reservation_price,
				'.$this->tableName.'.reservation_total_price,
				'.$this->tableName.'.vat_fee,
				'.$this->tableName.'.vat_percent,
				'.$this->tableName.'.reservation_paid,
				'.$this->tableName.'.pre_payment_type,
				'.$this->tableName.'.pre_payment_value,
				'.$this->tableName.'.additional_payment,
				'.$this->tableName.'.extras,
				'.$this->tableName.'.extras_fee,
				'.$this->tableName.'.cc_type,
				'.$this->tableName.'.cc_holder_name,
				IF((('.$this->tableName.'.reservation_total_price) + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.reservation_paid + '.$this->tableName.'.additional_payment) > 0),
					(('.$this->tableName.'.reservation_total_price) + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.reservation_paid + '.$this->tableName.'.additional_payment)),
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
				IF('.$this->tableName.'.status > 6, -1, '.$this->tableName.'.status) as status,
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
				CONCAT("<a href=\"index.php?'.$this->page.'&mg_action=description&oid=", '.$this->tableName.'.'.$this->primaryKey.', "\">", "'._DESCRIPTION.'", "</a>") as link_order_description
			FROM '.$this->tableName.'
				LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
				LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
			WHERE
				'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$oid;

        if($this->user_id != ''){
            $sql .= ' AND '.$this->tableName.'.is_admin_reservation = 0 AND '.$this->tableName.'.customer_id = '.(int)$this->user_id;
        }

        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $content .= '<table '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').' width="100%" border="0">';
            $content .= '<tr><td width="210px">'._RESERVATION_NUMBER.': </td><td>'.$result[0]['reservation_number'].'</td></tr>';
            $content .= '<tr><td>'._DESCRIPTION.': </td><td>'.$result[0]['reservation_description'].'</td></tr>';
            $content .= '<tr><td>'._TRANSACTION.': </td><td>'.$result[0]['transaction_number'].'</td></tr>';
            $content .= '<tr><td>'._DATE_CREATED.': </td><td>'.$result[0]['created_date_formated'].'</td></tr>';
            $content .= '<tr><td>'._DATE_PAYMENT.': </td><td>'.($result[0]['payment_date_formated'] ? $result[0]['payment_date_formated'] : '--').'</td></tr>';
            if($this->user_id == ''){
                $content .= '<tr><td>'._CUSTOMER.': </td><td>';
                if($result[0]['customer_name'] == 'without_account'){
                    $content .= $result[0]['customer_last_name'].' '.$result[0]['customer_first_name'].' ('._WITHOUT_ACCOUNT.')';
                }elseif($result[0]['customer_name'] == 'admin'){
                    $content .= _ADMIN;
                }else{
                    $content .= $result[0]['customer_name'];
                }
                $content .= '</td></tr>';
            }
            $content .= '<tr><td>'._PAYMENT_TYPE.': </td><td>'.$this->arr_payment_types[$result[0]['payment_type']].'</td></tr>';
            $content .= '<tr><td>'._PAYMENT_METHOD.': </td><td>'.$this->arr_payment_methods[$result[0]['payment_method']].'</td></tr>';

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

            $content .= '<tr><td>'._BOOKING_PRICE.': </td><td>'.Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';

            $content .= '<tr><td>'._BOOKING_SUBTOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';

            if(!empty($result[0]['extras'])) $content .= '<tr><td>'._EXTRAS_SUBTOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['extras_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';
            if($this->vat_included_in_price == 'no') $content .= '<tr><td>'._VAT.': </td><td>'.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'right', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).')</td></tr>';

            $reservation_price_plus_vat = Currencies::PriceFormat($result[0]['reservation_total_price'] + $result[0]['extras_fee'] + $result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
            $reservation_paid = Currencies::PriceFormat($result[0]['reservation_paid'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
            $reservation_paid_plus_additional = Currencies::PriceFormat($result[0]['reservation_paid'] + $result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
            $have_to_pay = Currencies::PriceFormat($result[0]['reservation_total_price'] + $result[0]['extras_fee'] + $result[0]['vat_fee'] - ($result[0]['reservation_paid'] + $result[0]['additional_payment']), $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
            $additional_payment = Currencies::PriceFormat($result[0]['additional_payment'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat);
            $link_to_payment = '&nbsp;&nbsp;&nbsp; <a href="index.php?page=tours_prepayment&rn='.$result[0]['reservation_number'].'" style="text-decoration:none;">'._CLICK_TO_PAY.' <img src="images/ppc_icons/logo_paypal.gif" alt="paypal" style="margin-top:-2px;margin-left:5px;"></a>';

            if($result[0]['pre_payment_type'] == 'percentage' && $result[0]['pre_payment_value'] > 0 && $result[0]['pre_payment_value'] < 100){
                $content .= '<tr><td>'._PAYMENT_SUM.': </td><td>'.$reservation_price_plus_vat.'</td></tr>';
                $content .= '<tr><td>'._PRE_PAYMENT.': </td><td>'.$reservation_paid.' ('._PARTIAL_PRICE.' - '.$result[0]['pre_payment_value'].'%)</td></tr>';
                if($result[0]['additional_payment'] != 0) $content .= '<tr><td>'._ADDITIONAL_PAYMENT.': </td><td>'.$additional_payment.'</td></tr>';
                if($have_to_pay > 0) $content .= '<tr><td style="color:#a60000">'._PAYMENT_REQUIRED.': </td><td style="color:#a60000">'.$have_to_pay.' '.$link_to_payment.'</td></tr>';
            }elseif($result[0]['pre_payment_type'] == 'fixed sum' && $result[0]['pre_payment_value'] > 0){
                $content .= '<tr><td>'._PAYMENT_SUM.': </td><td>'.$reservation_price_plus_vat.'</td></tr>';
                $content .= '<tr><td>'._PRE_PAYMENT.': </td><td>'.$reservation_paid.' ('._PARTIAL_PRICE.' - '.Currencies::PriceFormat($result[0]['pre_payment_value'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).')</td></tr>';
                if($result[0]['additional_payment'] != 0) $content .= '<tr><td>'._ADDITIONAL_PAYMENT.': </td><td>'.$additional_payment.'</td></tr>';
                if($have_to_pay > 0) $content .= '<tr><td style="color:#a60000">'._PAYMENT_REQUIRED.': </td><td style="color:#a60000">'.$have_to_pay.' '.$link_to_payment.'</td></tr>';
            }else{
                $content .= '<tr><td>'._PAYMENT_SUM.': </td><td>'.$reservation_paid.'</td></tr>';
                $content .= '<tr><td>'._PRE_PAYMENT.': </td><td>'._FULL_PRICE.'</td></tr>';
                if($result[0]['additional_payment'] != 0) $content .= '<tr><td>'._ADDITIONAL_PAYMENT.': </td><td>'.$additional_payment.'</td></tr>';
            }
            $content .= '<tr><td>'._TOTAL.': </td><td>'.$reservation_paid_plus_additional.'</td></tr>';
            //$content .= '<tr><td>'._TOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'</td></tr>';

            // Allow to pay required/additional sum if order is not canceled
            if(!$objLogin->IsLoggedInAsAdmin() && $result[0]['mod_have_to_pay'] != 0 && $result[0]['status'] != 6){
                $content .= '<tr><td style="color:#960000">'._PAYMENT_REQUIRED.': </td><td style="color:#960000">'.Currencies::PriceFormat($result[0]['mod_have_to_pay'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' '.$link_to_payment.'</td></tr>';
            }

            $content .= '<tr><td>'._STATUS.': </td><td>'.$this->statuses_vm[$result[0]['status']].'</td></tr>';
            if($result[0]['additional_info'] != '') $content .= '<tr><td>'._ADDITIONAL_INFO.': </td><td>'.$result[0]['additional_info'].'</td></tr>';
            $content .= '<tr><td colspan="2">&nbsp;</tr>';
            $content .= '</table>';
        }else{
            $content .= draw_important_message(_WRONG_PARAMETER_PASSED, false);
        }

        $content .= $this->GetTourReservationList($oid, $language_id, (($objLogin->IsLoggedInAsAdmin()) ? 'edit' : 'details'));

        $output .= '<div id="divDescriptionContent">'.$content.'</div>';
        if(empty($mode)){
            $output .= '<div>';
            $output .= '<br /><input type="button" class="mgrid_button" name="btnBack" value="'._BUTTON_BACK.'" onclick="javascript:appGoTo(\''.$this->page.'\');">';
            $output .= '</div>';
        }

        if($draw) echo $output;
        else return $output;
    }

    /**
     * Before-Delete record
     */
    public function BeforeDeleteRecord()
    {
        $oid = MicroGrid::GetParameter('rid');
        $sql = 'SELECT
					reservation_number,
					customer_id,
					is_admin_reservation,
					payment_type,
					status,
					(reservation_paid + additional_payment) as revert_sum
				FROM '.$this->tableName.'
				WHERE id = '.(int)$oid;
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){

            $this->reservation_number = $result[0]['reservation_number'];
            $this->reservation_status = $result[0]['status'];
            $this->booking_customer_id = $result[0]['customer_id'];

            // If paid by balance ('payment_type' == '6') and order is not canceled ('status' != '6')
            if($result[0]['payment_type'] == '6' && $result[0]['status'] != '6'){
                $this->revert_sum = $result[0]['revert_sum'];
            }

            return true;
        }

        return false;
    }

    /**
     *	After-Delete record
     */
    public function AfterDeleteRecord()
    {
        global $objLogin;

        // Check if this is "by balance" payment and return money back to account balance
        if($this->allow_payment_with_balance && !empty($this->revert_sum)){
            $customer_type = Customers::GetCustomerInfo($this->booking_customer_id, 'customer_type');
            // Check if this is "agency payment"
            if($customer_type == 1){
                $sql = 'UPDATE '.TABLE_CUSTOMERS.' SET balance = balance + '.$this->revert_sum.' WHERE id = '.(int)$this->booking_customer_id;
                database_void_query($sql);
            }
        }
    }

    /**
     * Trigger method - allows to work with View Mode items
     */
    protected function OnItemCreated_ViewMode($field_name, &$field_value)
    {
        if($field_name == 'customer_name' && $field_value == '{administrator}'){
            $field_value = _ADMIN;
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
					(payment_date = \'\' OR payment_date IS NULL)';
        database_void_query($sql);
    }

    /**
     *	Cleans pending reservations
     */
    public function CleanUpReservation()
    {
        if(strtolower(SITE_MODE) == 'demo'){
            $this->error = _OPERATION_BLOCKED;
            return false;
        }

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
     * Returns list for reservation
     * @param $oid
     * @param $language_id
     * @param $mode
     */
    public function GetTourReservationList($oid, $language_id, $mode = 'details')
    {
        $output = '';
        $data = array();

        // display list of cars in order
        $sql = 'SELECT
					'.$this->tableName. '.*,
					DATE_FORMAT('.$this->tableName.'.date_from, "'.$this->sqlFieldDateFormat.'") date_from,
					DATE_FORMAT('.$this->tableName.'.date_to, "'.$this->sqlFieldDateFormat.'") date_to
				FROM '.$this->tableName.'
				WHERE '.$this->tableName.'.id = '.(int)$oid;

        //CONCAT(DATE_FORMAT('.$this->tableName.'.date_to, "'.$this->sqlFieldDateFormat.'"), " ", DATE_FORMAT('.$this->tableName.'.time_to, "%H:%i")) date_to

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

        if($result[1] > 0){

            $output .= '<h3>'._RESERVATION_DETAILS.'</h3>';
            $output .= '<table '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').' style="border:1px solid #d1d2d3" width="100%" border="0" cellspacing="0" cellpadding="3" class="tblReservationDetails">';
            $output .= '<thead><tr style="background-color:#e1e2e3;font-weight:bold;font-size:13px;">';
            if($mode == 'invoice'){
                $output .= '<th align="center" width="40px" style="text-align:center">&nbsp;# </th>';
                $output .= '<th align="left">'._TOUR.'</th>';
                $output .= '<th align="left" width="165px">'._TOUR_TYPE.'</th>';
                $output .= '<th align="left" width="100px">'._LOCATION.'</th>';
                $output .= '<th align="left" width="90px">'._FROM.'</th>';
                $output .= '<th align="left" width="90px">'._TO.'</th>';
                $output .= '<th align="right" width="90px" style="text-align:right">'._PRICE.'&nbsp;</th>';
                $output .= '</tr></thead>';
            }else{
                $output .= '<th align="center" width="40px" style="text-align:center">&nbsp;# </th>';
                $output .= '<th align="left">'._TOUR.'</th>';
                $output .= '<th align="left" width="220px">'._TOUR_TYPE.'</th>';
                $output .= '<th align="left" width="170px">'._LOCATION.'</th>';
                $output .= '<th align="left" width="120px">'._FROM.'</th>';
                $output .= '<th align="left" width="120px">'._TO.'</th>';
                $output .= '<th align="right" width="100px" style="text-align:right">'._PRICE.'&nbsp;</th>';
                $output .= '</tr></thead>';
            }

            for($i=0; $i < $result[1]; $i++){
                $tour = isset($this->arr_tours[$result[0][$i]['tour_id']]) ? $this->arr_tours[$result[0][$i]['tour_id']] : '';
                $tour_type = isset($this->arr_tours_types[$result[0][$i]['tour_type_id']]) ? $this->arr_tours_types[$result[0][$i]['tour_type_id']] : '';
                $location = isset($this->arr_agencies_locations[$result[0][$i]['location_id']]) ? $this->arr_agencies_locations[$result[0][$i]['location_id']] : '';
                $date_from = isset($result[0][$i]['date_from']) ? $result[0][$i]['date_from'] : '';
                $date_to = isset($result[0][$i]['date_to']) ? $result[0][$i]['date_to'] : '';
                $price = isset($result[0][$i]['reservation_total_price']) ? $result[0][$i]['reservation_total_price'] : '';

                if($mode == 'invoice'){
                    $output .= '<tr>';
                    $output .= '<td width="40px" align="center">'.($i+1).'.</td>';
                    $output .= '<td align="left">'.$tour.'</td>';
                    $output .= '<td width="165px" align="left">'.$tour_type.'</td>';
                    $output .= '<td width="100px" align="left">'.$location.'</td>';
                    $output .= '<td width="90px" align="left">'.$date_from.'</td>';
                    $output .= '<td width="90px" align="left">'.$date_to.'</td>';
                    $output .= '<td width="90px" align="right">'.$price.'&nbsp;</td>';
                    $output .= '</tr>';
                } else {
                    $output .= '<tr>';
                    $output .= '<td align="center">'.($i+1).'.</td>';
                    $output .= '<td align="left">'.$tour.'</td>';
                    $output .= '<td align="left">'.$tour_type.'</td>';
                    $output .= '<td align="left">'.$location.'</td>';
                    $output .= '<td align="left">'.$date_from.'</td>';
                    $output .= '<td align="left">'.$date_to.'</td>';
                    $output .= '<td align="right">'.$price.'&nbsp;</td>';
                    $output .= '</tr>';
                }
            }
            $output .= '</table>';
        }

        //if($mode == 'invoice') return $data;
        //else return $output;
        return $output;
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

        $tableName = DB_PREFIX.'tours_reservations';

        $prebooking_orders_timeout = (int)ModulesSettings::Get('tours_and_attractions', 'prebooking_orders_timeout');
        $sender = $objSettings->GetParameter('admin_email');
        // preapre datetime format
        if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
            $fieldDateFormat = 'M d, Y';
        }else{
            $fieldDateFormat = 'd M, Y';
        }
        $currencyFormat = get_currency_format();
        $language_id = Languages::GetDefaultLang();

        if($prebooking_orders_timeout > 0){

            $sql_delete = 'DELETE FROM '.$tableName.' WHERE status = 0 AND TIMESTAMPDIFF(HOUR, created_date, \''.date('Y-m-d H:i:s').'\') >= '.(int)$prebooking_orders_timeout;

            if(ModulesSettings::Get('tours_and_attractions', 'reservation_expired_alert') == 'yes'){
                $sql = 'SELECT
							'.$tableName.'.customer_id,
							'.TABLE_CUSTOMERS.'.first_name,
							'.TABLE_CUSTOMERS.'.last_name,
							'.TABLE_CUSTOMERS.'.preferred_language,
							'.TABLE_CUSTOMERS.'.email,
							'.$tableName.'.reservation_number,
							'.$tableName.'.created_date,
							'.$tableName.'.reservation_description,
							'.$tableName.'.reservation_total_price,
							'.$tableName.'.currency
						FROM '.$tableName.'
							LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
						WHERE '.$tableName.'.status = 0 AND
							TIMESTAMPDIFF(HOUR, '.$tableName.'.created_date, \''.date('Y-m-d H:i:s').'\') >= '.(int)$prebooking_orders_timeout;

                $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
                if($result[1] > 0){

                    for($i=0; $i < $result[1]; $i++){
                        $booking_details  = _RESERVATION_DESCRIPTION.': '.$result[0][$i]['reservation_description'].'<br />';
                        $booking_details .= _CREATED_DATE.': '.format_datetime($result[0][$i]['created_date'], $fieldDateFormat.' H:i:s', '', true).'<br />';
                        $booking_details .= _BOOKING_PRICE.': '.Currencies::PriceFormat($result[0][$i]['reservation_total_price'], $result[0][$i]['currency'], 'left', $currencyFormat).'<br />';

                        $recipient = $result[0][$i]['email'];
                        $preferred_language = $result[0][$i]['preferred_language'];
                        send_email(
                            $recipient,
                            $sender,
                            'reservation_expired',
                            array(
                                '{FIRST NAME}' => $result[0][$i]['first_name'],
                                '{LAST NAME}'  => $result[0][$i]['last_name'],
                                '{BOOKING DETAILS}' => $booking_details,
                                '{HOTEL INFO}' => ''
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
     * @return bool
     */
    private function ReturnMoneyToBalance($rid = '0')
    {
        if(!$this->allow_payment_with_balance){
            return false;
        }

        // Check if this is "by balance" payment and return money back to account balance
        $sql = 'SELECT
					customer_id,
					(reservation_paid + additional_payment) as revert_sum
				FROM '.$this->tableName.'
				WHERE id = '.(int)$rid.' AND payment_type = 6';

        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $customer_id = $result[0]['customer_id'];
            $revert_sum = $result[0]['revert_sum'];
            $customer_type = Customers::GetCustomerInfo($customer_id, 'customer_type');

            // Check if this is "agency payment"
            if($customer_type == 1){
                $sql = 'UPDATE '.TABLE_CUSTOMERS.' SET balance = balance + '.$revert_sum.' WHERE id = '.(int)$customer_id;
                database_void_query($sql);
            }
        }

        return true;
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
					'.$this->tableName.'.reservation_number
				FROM '.$this->tableName.'
				WHERE
					'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$rid;

        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

        $reservation_number = isset($result[0]['reservation_number']) ? $result[0]['reservation_number'] : '';

        Pdf::config(array(
                        'page_orientation'  => 'P',             // [P=portrait, L=landscape]
                        'unit'              => 'mm',            // [pt=point, mm=millimeter, cm=centimeter, in=inch]
                        'page_format'       => 'A4',
                        'unicode'           => true,
                        'encoding'          => 'UTF-8',
                        'creator'           => 'uHotelBooking',
                        'author'            => 'ApPHP',
                        'title'             => _INVOICE.' ('._FOR_BOOKING.$reservation_number.')',
                        'subject'           => _INVOICE.' ('._FOR_BOOKING.$reservation_number.')',
                        'keywords'          => '',
                        //'header_logo'     => '../../../templates/reports/images/logo.png',
                        'header_logo_width' => '45',
                        'header_title'      => 'Tour Reservation #'.$reservation_number,
                        'header_enable'     => false,
                        'text_shadow'       => false,
                        'margin_top'        => '1',
                        'margin_left'       => '5',
                        'direction'         => Application::Get('lang_dir')
                    ));

        $data = $this->DrawToursOrdersInvoice($rid, true, 'html', false);

        $base_path = $_SERVER['DOCUMENT_ROOT'].get_base_path();
        Pdf::createDocument($data, $base_path.'tmp/export/invoice', 'F'); // 'I' - inline, 'D' - download, 'F' - file,

        $this->message = _DOWNLOAD_INVOICE.' (PDF): <a href="javascript:void(\'pdf\')" onclick="javascript:appGoToPage(\'index.php?admin=export&file=invoice.pdf\');window.setTimeout(function(){location.reload()},2000);">'._FOR_BOOKING.$reservation_number.'</a>';

        return true;
    }

}
