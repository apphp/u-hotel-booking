<?php

/**
 * 	Class ChannelManager (for uHotelBooking ONLY)
 *  -------------- 
 *  Description : 
 *  Updated	    : 10.12.2016
 * 	Written by  : ApPHP
 * 	
 * 	PUBLIC:					STATIC:							PRIVATE (static):
 *  -----------				-----------						-----------
 *  __construct				GetAllChannels					PrepareArrRoomPrices
 *  __destruct              ChangeRoomAvailability			GetArrRoomPricesForDefaultValues
 *                          GetArrRoomPrices				PrepareArrRoomPricesFromOneRecord
 *                          GetFormattedRoomPrices      	PrepareArrRoomPricesForDefaultValues
 *															GetMaxUnixDateForRoomPrices
 *															PrepareArrRoomPricesForOnePeriod
 *                                                 	 		GetRoomPrices
 *                                                 	 		IsDefaultOrInvalidDateRoomPrice
 *                                                 	 		IsDefaultRoomPrice
 *                                                 	 		IsInvalidDateInRecord
 *                                                 	 		GetRoomPricesByByPeriodId
 *                                                 	 		
 *                                                 	 		
 *  
 * */
class ChannelManager extends MicroGrid {

    protected $debug = false;

	const MAXIMUM_PERIOD_FOR_ROOM_PRICE_IN_YEAR = 1;

    //==========================================================================
    // Class Constructor
    //==========================================================================
    function __construct() {
        parent::__construct();

        //global $objSettings;
        //global $objLogin;

        $this->params = array();
        if (isset($_POST['name']))
            $this->params['name'] = prepare_input($_POST['name']);
        if (isset($_POST['description']))
            $this->params['description'] = prepare_input($_POST['description']);

        $this->primaryKey = 'id';
        $this->tableName = DB_PREFIX . 'channels';
        $this->dataSet = array();
        $this->error = '';
        ///$this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

        $this->actions = array('add' => true, 'edit' => true, 'details' => true, 'delete' => true);
        $this->actionIcons = true;
        $this->allowRefresh = true;
        $this->allowPrint = true;
        $this->allowTopButtons = false;

        $this->allowLanguages = false;
        $this->WHERE_CLAUSE = '';
        $this->ORDER_CLAUSE = 'ORDER BY id DESC';

        $this->isAlterColorsAllowed = true;

        $this->isPagingAllowed = true;
        $this->pageSize = 20;

        $this->isSortingAllowed = true;

        //$total_countries = Countries::GetAllCountries();
        //$arr_countries = array();
        //foreach($total_countries[0] as $key => $val){
        //	$arr_countries[$val['abbrv']] = $val['name'];
        //}
        //
		//$total_groups = CustomerGroups::GetAllGroups();
        //
		//// Get url_parameter for affiliate link
        //$url_parameter = ModulesSettings::Get('affiliates', 'url_parameter');
        //
		//$this->arr_order_count = array();
        //$sql = 'SELECT COUNT('.TABLE_BOOKINGS.'.affiliate_id) as cnt,
        //			'.TABLE_AFFILIATES.'.affiliate_id
        //		FROM '.TABLE_BOOKINGS.' 
        //			RIGHT JOIN '.TABLE_AFFILIATES.' ON '.TABLE_AFFILIATES.'.affiliate_id = '.TABLE_BOOKINGS.'.affiliate_id
        //		WHERE 1
        //		GROUP BY '.TABLE_AFFILIATES.'.affiliate_id';
        //$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        //if($result[1] > 0){
        //	foreach($result[0] as $order_info){
        //		if($order_info['cnt'] == 0){
        //			$this->arr_order_count[$order_info['affiliate_id']] = '<label class="mgrid_label">'._ORDERS.' (0)'.'</label>';
        //		}else{
        //			$this->arr_order_count[$order_info['affiliate_id']] = '<label class="mgrid_label"><a href="'.APPHP_BASE.'index.php?admin=mod_booking_bookings&mg_action=view&mg_operation=filtering&mg_search_status=active&filter_by_'.TABLE_BOOKINGS.'affiliate_id='.$order_info['affiliate_id'].'&mg_search_status=active">'._ORDERS.' </a>'.' ('.$order_info['cnt'].')</label>';
        //		}
        //	}
        //}
        //
		//$this->generationString = '[ <a href="javascript:generateRandomAlphaNumeric(\'affiliate_id\', 10)">'._GENERATE.'</a> ]';

        $this->isFilteringAllowed = true;
        $this->arrFilteringFields = array(
            _CHANNEL => array('table' => $this->tableName, 'field' => 'name', 'type' => 'text', 'sign' => '%like%', 'width' => '90px'),
        );


        //$datetime_format = get_datetime_format();		
        //$date_format_view = get_date_format('view');
        //$date_format_edit = get_date_format('edit');
        //
		//$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
        //
		//if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
        //	$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
        //	$this->sqlFieldDateFormat = '%b %d, %Y';
        //}else{
        //	$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
        //	$this->sqlFieldDateFormat = '%d %b, %Y';
        //}
        //$this->SetLocale(Application::Get('lc_time_name'));
        //---------------------------------------------------------------------- 
        // VIEW MODE
        //---------------------------------------------------------------------- 
        $this->VIEW_MODE_SQL = 'SELECT
		                            ' . $this->tableName . '.*
								FROM ' . $this->tableName;
        // define view mode fields
        $this->arrViewModeFields = array(
            'name' => array('title' => _CHANNEL_NAME, 'type' => 'label', 'align' => 'left', 'width' => '210px', 'maxlength' => '25'),
            'description' => array('title' => _DESCRIPTION, 'type' => 'label', 'align' => 'left', 'width' => '', 'maxlength' => '100'),
            'id' => array('title' => 'ID', 'type' => 'label', 'align' => 'center', 'width' => '80px'),
        );

        //---------------------------------------------------------------------- 
        // ADD MODE
        //---------------------------------------------------------------------- 
        // define add mode fields
        $this->arrAddModeFields = array(
            'name' => array('title' => _CHANNEL_NAME, 'type' => 'textbox', 'width' => '210px', 'maxlength' => '255', 'required' => true, 'unique' => true, 'validation_type' => 'text'),
            'description' => array('title' => _DESCRIPTION, 'type' => 'textarea', 'width' => '310px', 'required' => false, 'height' => '90px', 'editor_type' => 'simple', 'readonly' => false, 'default' => '', 'maxlength' => '2048', 'validation_type' => '', 'validation_maxlength' => '2048', 'unique' => false),
        );

        //---------------------------------------------------------------------- 
        // EDIT MODE
        // * password field must be written directly in SQL!!!
        //---------------------------------------------------------------------- 
        $this->EDIT_MODE_SQL = 'SELECT
		                            ' . $this->tableName . '.*
								FROM ' . $this->tableName . '
								WHERE ' . $this->tableName . '.' . $this->primaryKey . ' = _RID_';

        // define edit mode fields
        $this->arrEditModeFields = array(
            'name' => array('title' => _CHANNEL_NAME, 'type' => 'textbox', 'width' => '210px', 'maxlength' => '255', 'required' => true, 'unique' => true, 'validation_type' => 'text'),
            'description' => array('title' => _DESCRIPTION, 'type' => 'textarea', 'width' => '310px', 'required' => false, 'height' => '90px', 'editor_type' => 'simple', 'readonly' => false, 'default' => '', 'maxlength' => '2048', 'validation_type' => '', 'validation_maxlength' => '2048', 'unique' => false),
        );

        //---------------------------------------------------------------------- 
        // DETAILS MODE
        // format: strip_tags, nl2br, readonly_text
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
        $this->arrDetailsModeFields = array(
            'name' => array('title' => _CHANNEL_NAME, 'type' => 'label', 'format' => '', 'format_parameter' => '', 'visible' => true),
            'description' => array('title' => _DESCRIPTION, 'type' => 'label', 'format' => '', 'format_parameter' => '', 'visible' => true),
        );
    }

    //==========================================================================
    // Class Destructor
    //==========================================================================
    function __destruct() {
        // echo 'this object has been destroyed';
    }

    //==========================================================================
    // Static Methods
    //==========================================================================	
    /**
     * 	Get all countries array
     * 	@param $where - where clause
     */
    public static function GetAllChannels($where = '') {
        // Build WHERE clause
        $where_clause = !empty($where) ? 'WHERE ' . $where : '';
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'channels ' . $where_clause;

        return database_query($sql, DATA_AND_ROWS);
    }

    /**
     * 	Changes room availability after booking is made
     * 	Called form Bookings class
     * 	@param $room_id
     * 	@param $booking_number
     * 	@param $info
     * 	@param $availability	increase|decrease
     * 	@param $action_name
     * 	@param $channel_id
     * 	@return void
     */
    public static function ChangeRoomAvailability($room_id, $booking_number = '', $info = array(), $availability = '', $action_name = '', $channel_id = '') {
        // Get hotel room data
        $hotel_result = ChannelHotelRooms::GetHotelRoomData($room_id, $channel_id);
		
		//echo "$room_id, $booking_number, $info, $availability, $action_name";
		////dbug($info);
		//dbug($hotel_result);

        if (!empty($hotel_result[1]) && $hotel_result[0]['cm_enabled'] == '1' && $hotel_result[0]['cm_auth_key'] != '' && $hotel_result[0]['cm_api_key'] != '' && $hotel_result[0]['cm_hotel_id'] != '' && $hotel_result[0]['cm_room_id'] != '') {
            $cm_auth_key = $hotel_result[0]['cm_auth_key'];
            $cm_api_key = $hotel_result[0]['cm_api_key'];
            $cm_hotel_id = $hotel_result[0]['cm_hotel_id'];
            $cm_room_id = $hotel_result[0]['cm_room_id'];

            /*
             * All we have to do here is create a POST HTPP Request to "https://cmapp.hotelook.gr/api/rooms/decrease_availability" if decrease OR
             * "https://cmapp.hotelook.gr/api/rooms/increase_availability" if increase for a specific room for specific from - to.
             * from - to must be in YYYY-mm-dd format.
             * decrease_availability and increase_availability function decreases and increases the availability by 1. 
             * For 2 rooms, call it twice
             * For 3 rooms, call it three times etc..
             */
            for ($i = 1; $i <= $info['rooms']; $i++) {
                $data = array(
                    "auth_key" => $cm_auth_key,
                    "api_key" => $cm_api_key,
                    "hotel_id" => $cm_hotel_id,
                    "room_id" => $cm_room_id,
                    "from" => $info['checkin'],
                    "to" => $info['checkout'],
                );
				
				$sent_status = true;
                $ch = curl_init();
                if ($availability == 'decrease') {
                    curl_setopt($ch, CURLOPT_URL, "https://cmapp.hotelook.gr/api/rooms/decrease_availability");
                } else {
                    curl_setopt($ch, CURLOPT_URL, "https://cmapp.hotelook.gr/api/rooms/increase_availability");
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $output = curl_exec($ch);
				$log_error = curl_error($ch);

				// Add log record
				// -------------------------------------------
				$log_subject = 'Room reservation.' . (!empty($action_name) ? '<br>(' . $action_name . ')' : '') . '<br>Booking #: ' . $booking_number;
				$log_error = curl_error($ch);
				if(!empty($log_error)){
					$sent_status = false;
					$log_status_description = 'Error sending data to the server: '.$log_error;
				}
	
				if($sent_status){
					$result_to_xml = new SimpleXMLElement($output);
					$result_status = (string)$result_to_xml->status;
					$result_message = (string)$result_to_xml->message;
					$result_error = (string)$result_to_xml->error;
	
					if($result_status == 'true'){
						$log_status_description = 'Operation completed successfully';
					}else{
						$error_message = $result_message != '' ? $result_message : $result_error;
						$sent_status = false;
						$log_status_description = $error_message;
					}
				}
				$log_content = 'Data sent: ';
				$log_content .= '<pre style=min-width:800px;width:100%;>';
				$log_content .= 'Hotel ID - '.$cm_hotel_id."\r\n";
				$log_content .= 'Room ID - '.$cm_room_id."\r\n";
				$log_content .= 'Operation: - '.($availability == 'decrease' ? 'Decrease' : 'Increase')."  Room Availability\r\n";
				$log_content .= str_replace(' ', '', print_r($data, 1));			
				$log_content .= '</pre>';
				
				if($sent_status && $result_status == 'true'){
					//draw_success_message('Room reservation have been successfully updated in Channel Manager');	
				}else{
					//draw_important_message('An error occurred while updating room reservation in Channel Manager. See <a href="index.php?admin=mod_channel_manager_log" target="_blank" rel="noopener noreferrer">Channel Manager Log</a> for more details.');
				}
				
				ChannelManagerLog::LogAddRecord($hotel_result[0]['hotel_id'], $hotel_result[0]['room_id'], $log_subject, $sent_status == true ? 'OK' : 'ERROR', $log_status_description, $log_content);
            }
        }
    }

	/**
	 * Get array prices for room
	 * @param int $hotel_id
	 * @param int $room_id
	 * @return array
	 */
	public static function GetArrRoomPrices($hotel_id, $room_id)
	{
		global $objSession;
		
		$arr_room_prices = array();		
		$task = isset($_REQUEST['task']) ? prepare_input($_REQUEST['task']) : '';
		$room_id = isset($_POST['rid']) ? prepare_input($_POST['rid']) : 0;
		$room_price_id = isset($_POST['rpid']) ? prepare_input($_POST['rpid']) : 0;
		
		// Add new period of time
		if($task == 'add_new'){			
			$data = self::GetRoomPricesByByPeriodId($room_id, $room_price_id);
			if(!empty($data)){
				$unix_date_from = strtotime($data['date_from']);
				$unix_date_to   = strtotime($data['date_to']);
				unset($data['date_from'], $data['date_to']);
				$arr_room_prices = self::PrepareArrRoomPricesForOnePeriod($data, $unix_date_from, $unix_date_to);
			}
		
		// Add default periods
		}elseif($task == 'add_default_periods'){

			// Get hotel all default periods 
			$sql = 'SELECT * FROM '.TABLE_HOTEL_PERIODS.' WHERE hotel_id = '.(int)$hotel_id;
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			$sql_middle = array();
			if($result[1] > 0){
				for($i=0; $i<$result[1]; $i++){
					$sql_middle[] = " ( date_from = '".$result[0][$i]['start_date']."' AND date_to = '".$result[0][$i]['finish_date']."') ";
				}

				// Get room prices for default periods
				$sql = 'SELECT * FROM '.TABLE_ROOMS_PRICES.' WHERE room_id = '.(int)$room_id.' AND is_default = 0 AND ( '.implode(' OR ', $sql_middle).' ) ';
				$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

				// Collect prices for different periods
				for($i=0; $i<$result[1]; $i++){
					$arr_room_def_period_prices = array();
					$data = self::GetRoomPricesByByPeriodId($room_id, $result[0][$i]['id']);
					if(!empty($data)){
						$unix_date_from = strtotime($data['date_from']);
						$unix_date_to   = strtotime($data['date_to']);
						unset($data['date_from'], $data['date_to']);
						$arr_room_def_period_prices = self::PrepareArrRoomPricesForOnePeriod($data, $unix_date_from, $unix_date_to);
						$arr_room_prices = array_merge($arr_room_prices, $arr_room_def_period_prices);
					}
				}
			}
			
			
		// Delete period of time
		}elseif($task == 'delete'){		
			$room_prices_deleted_period = $objSession->GetMessage('room_prices_deleted_period');			
			$data = self::GetRoomPricesByByPeriodId($room_id, 0, true);
			if(!empty($room_prices_deleted_period)){
				$unix_date_from = strtotime($room_prices_deleted_period['date_from']);
				$unix_date_to   = strtotime($room_prices_deleted_period['date_to']);
				$objSession->SetMessage('room_prices_deleted_period', NULL);
				unset($data['date_from'], $data['date_to']);
				$arr_room_prices = self::PrepareArrRoomPricesForOnePeriod($data, $unix_date_from, $unix_date_to);
			}	

		// General update of prices
		}elseif($task == 'update'){			
            $arr_room_prices = self::GetFormattedRoomPrices($room_id);
		}

		return $arr_room_prices;
	}
	
	/**
	 * Prepare array room prices by period id
	 * @param int $room_id
	 * @param int $room_price_id
	 * @param bool $is_default
	 * @return array
	 */
	private static function GetRoomPricesByByPeriodId($room_id, $room_price_id, $is_default = false)
	{
		$prices = array();
		if(!empty($room_price_id)){
			$sql = 'SELECT * FROM '.TABLE_ROOMS_PRICES.' WHERE room_id = '.(int)$room_id.' AND id = '.(int)$room_price_id;
		}else{
			$sql = 'SELECT * FROM '.TABLE_ROOMS_PRICES.' WHERE room_id = '.(int)$room_id.' '.($is_default ? ' AND is_default = 1 ' : '').' ORDER BY id DESC';	
		}
		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){

			$date_from	= isset($result[0]['date_from']) ? prepare_input($result[0]['date_from']) : '';
			$date_to 	= isset($result[0]['date_to']) ? prepare_input($result[0]['date_to']) : '';
			$default_price = 0;
			
			$prices = array(
				'mon' => isset($result[0]['mon']) ? number_format((float)$result[0]['mon'], 2, '.', '') : $default_price,
				'tue' => isset($result[0]['tue']) ? number_format((float)$result[0]['tue'], 2, '.', '') : $default_price,
				'wed' => isset($result[0]['wed']) ? number_format((float)$result[0]['wed'], 2, '.', '') : $default_price,
				'thu' => isset($result[0]['thu']) ? number_format((float)$result[0]['thu'], 2, '.', '') : $default_price,
				'fri' => isset($result[0]['fri']) ? number_format((float)$result[0]['fri'], 2, '.', '') : $default_price,
				'sat' => isset($result[0]['sat']) ? number_format((float)$result[0]['sat'], 2, '.', '') : $default_price,
				'sun' => isset($result[0]['sun']) ? number_format((float)$result[0]['sun'], 2, '.', '') : $default_price,
			);
			
			$prices['date_from'] = $date_from;
			$prices['date_to'] = $date_to;
		}
		
		return $prices;
	}	

    public static function GetFormattedRoomPrices($room_id)
    {
        $arr_room_prices = array();

        $room_prices = self::GetRoomPrices($room_id);
        // Get all periods
        if(!empty($room_prices)){
            $unix_max_date = self::GetMaxUnixDateForRoomPrices($room_prices);
            $arr_room_prices = self::PrepareArrRoomPrices($room_prices, $unix_max_date);
        }

        return $arr_room_prices;
    }

	/**
	 * Prepare array room prices to records
	 * @param array $room_prices
	 * @param int $unix_max_date
	 * @return array
	 */
	private static function PrepareArrRoomPrices($records, $unix_max_date)
	{
		$arr_room_prices = self::GetArrRoomPricesForDefaultValues($records, $unix_max_date);
		foreach($records as $record){
			$arr_room_price = self::PrepareArrRoomPricesFromOneRecord($record);
			$arr_room_prices = array_merge($arr_room_prices, $arr_room_price);
		}

		return $arr_room_prices;
	}

	/**
	 * Get array room prices with default price for records
	 * @param array $room_prices
	 * @param int $unix_max_date
	 * @return array
	 */
	private static function GetArrRoomPricesForDefaultValues($records, $unix_max_date)
	{
		$arr_room_prices = array();

		foreach($records as $record){
			if(self::IsDefaultRoomPrice($record)){
				$arr_room_prices = self::PrepareArrRoomPricesForDefaultValues($record, $unix_max_date);
				break;
			}
		}

		return $arr_room_prices;
	}

	/**
	 * Prepare array room prices for one record
	 * @param array $record
	 * @return array
	 */
    private static function PrepareArrRoomPricesFromOneRecord($record)
    {
        if(self::IsDefaultOrInvalidDateRoomPrice($record)){
            return array();
        }
        
        $unix_date_from = strtotime($record['date_from']);
        $unix_date_to   = strtotime($record['date_to']);
        $arr_room_prices = self::PrepareArrRoomPricesForOnePeriod($record, $unix_date_from, $unix_date_to);

        return $arr_room_prices;
    }

	/**
	 * Prepare array room prices with default price for default record
	 * @param array $default_record
	 * @param int $unix_end_date
	 * @return array
	 */
	private static function PrepareArrRoomPricesForDefaultValues($default_record, $unix_end_date)
	{
		$unix_start_date = strtotime(date('Y-m-d'));
        $arr_room_prices = self::PrepareArrRoomPricesForOnePeriod($default_record, $unix_start_date, $unix_end_date);

        return $arr_room_prices;
	}

	/**
	 * Find and get max date for room prices by records
	 * @param array $records
	 * @return int
	 */
	private static function GetMaxUnixDateForRoomPrices($records)
	{
		$unix_max_date = time() + self::MAXIMUM_PERIOD_FOR_ROOM_PRICE_IN_YEAR * 365 * 24 * 60 * 60;

		return $unix_max_date;
	}

	/**
	 * Prepare array room prices for one record
	 * @param array $record
	 * @param int $unix_start_date
	 * @param int $unix_end_date
	 * @return array
	 */
    private static function PrepareArrRoomPricesForOnePeriod($record, $unix_start_date, $unix_end_date){
        $arr_room_prices = array();

		$one_day_to_unix = 24 * 60 * 60;

		for($unix_current_date = $unix_start_date; $unix_current_date <= $unix_end_date; $unix_current_date += $one_day_to_unix){
			$current_date   = date('Y-m-d', $unix_current_date);
			$week_day       = date('D', $unix_current_date);
			$lower_week_day = strtolower($week_day);
			$price          = $record[$lower_week_day];

			$arr_room_prices[$current_date] = $price;	
        }

        return $arr_room_prices;
    }

	/**
	 * Get room prices info
	 * @param int $room_id
	 * @return array
	 */
	private static function GetRoomPrices($room_id)
	{
		$room_prices = array();
		$sql = 'SELECT * FROM '.TABLE_ROOMS_PRICES.' rp WHERE rp.room_id = '.(int)$room_id.' ORDER BY rp.is_default DESC';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			$room_prices = $result[0];
		}

		return $room_prices;
	}

	/*
	 * Check is default record or record with invalid date
	 * @param array $record
	 * @return boolean
	 */
    private static function IsDefaultOrInvalidDateRoomPrice($record)
    {
        return (self::IsDefaultRoomPrice($record) || self::IsInvalidDateInRecord($record)) ? true : false;
    }

	/*
	 * Check is default record
	 * @param array $record
	 * @return boolean
	 */
	private static function IsDefaultRoomPrice($record)
	{
		return $record['is_default'] == 1 ? true : false;
	}

	/*
	 * Check is record with invalid date
	 * @param array $record
	 * @return boolean
	 */
	private static function IsInvalidDateInRecord($record)
	{
		return (empty($record['date_from']) || empty($record['date_to'])) ? true : false;
	}

}
