<?php

/**
 *	Code Template for Packages Class
 *  -------------- 
 *	Written by  : ApPHP
 *	Usage       : HotelBooking ONLY
 *  Updated	    : 06.03.2015
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct             GetPackageInfo          CheckStartFinishDate
 *	__destruct              UpdateStatus            CheckDateOverlapping
 *	BeforeInsertRecord      GetMinimumNights        CheckMinMaxNights 
 *	BeforeUpdateRecord      GetMaximumNights		CheckRecordAssigned
 *	BeforeEditRecord		GetAllActiveByDate		CheckMinMaxRooms
 *	BeforeDetailsRecord		GetMinimumNights
 *	BeforeDeleteRecord      GetMaximumNights
 *							GetCanceledDays	
 *	
 **/


class Packages extends MicroGrid {
	
	protected $debug = false;
	
	protected $assigned_to_hotels = '';
    private $hotelOwner = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();

        global $objLogin;

		$this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner') ? true : false;

		$this->params = array();
		
		## for standard fields
		if(isset($_POST['hotel_id']))				$this->params['hotel_id'] = (int)$_POST['hotel_id'];
		if(isset($_POST['package_name']))   		$this->params['package_name'] = prepare_input($_POST['package_name']);
		if(isset($_POST['start_date']))     		$this->params['start_date'] = prepare_input($_POST['start_date']);
		if(isset($_POST['finish_date']))    		$this->params['finish_date'] = prepare_input($_POST['finish_date']);
		if(isset($_POST['minimum_nights'])) 		$this->params['minimum_nights'] = prepare_input($_POST['minimum_nights']);
		if(isset($_POST['maximum_nights'])) 		$this->params['maximum_nights'] = prepare_input($_POST['maximum_nights']);
		if(isset($_POST['minimum_rooms']))  		$this->params['minimum_rooms'] = (int)$_POST['minimum_rooms'];
		if(isset($_POST['maximum_rooms']))  		$this->params['maximum_rooms'] = (int)$_POST['maximum_rooms'];
		if(isset($_POST['cancel_reservation_day'])) $this->params['cancel_reservation_day'] = (int)$_POST['cancel_reservation_day'];
		
		## for checkboxes 
		$this->params['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : '0';

		## for images
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = prepare_input($_POST['icon']);
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}elseif (self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		## for files:
		// define nothing

		//$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_PACKAGES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_booking_packages';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	= '';

		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		$hotels_list = '';
		if($objLogin->IsLoggedInAs('hotelowner')){
			$hotels = $objLogin->AssignedToHotels();
			$hotels_list = implode(',', $hotels);
			$this->assigned_to_hotels = ' AND '.(!empty($hotels_list) ? $this->tableName.'.hotel_id IN ('.$hotels_list.') ' : '1 = 0');
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_hotels;

		// prepare hotels array		
        $where_clause = '';
        if($this->hotelOwner){
            $where_clause = (!empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : '1 = 0');
        }
		$total_hotels = Hotels::GetAllHotels($where_clause);
		$arr_hotels = array();
		foreach($total_hotels[0] as $key => $val){
			$arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
		}		

		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.hotel_id ASC, '.$this->tableName.'.start_date ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;

        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		// define filtering fields
		$this->arrFilteringFields = array(
			$hotel_name  => array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
		);

		$default_minimum_nights = ModulesSettings::Get('booking', 'minimum_nights');
		$default_maximum_nights = ModulesSettings::Get('booking', 'maximum_nights');
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		//$arr_nights = array('1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9',
		//					'10'=>'10','14'=>'14','21'=>'21','28'=>'28','30'=>'30','45'=>'45','60'=>'60','90'=>'90');
		//$arr_max_nights = array('1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9',
		//					'10'=>'10','14'=>'14','21'=>'21','28'=>'28','30'=>'30','45'=>'45','60'=>'60','90'=>'90',
		//					'120'=>'120', '150'=>'150', '180'=>'180', '240'=>'240', '360'=>'360');
		//$arr_rooms = array('1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9',
		//					'10'=>'10','12'=>'12','15'=>'15','20'=>'20','25'=>'25');
		//$arr_max_rooms = array('1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9',
		//					'10'=>'10','12'=>'12','15'=>'15','20'=>'20','25'=>'25','30'=>'30','45'=>'45','0'=>_UNLIMITED);

		//$date_format = get_date_format('view');
		$date_format_edit = get_date_format('edit');
		
		global $objSettings;
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$sqlFieldDateFormat = '%b %d, %Y';
		}else{
			$sqlFieldDateFormat = '%d %b, %Y';
		}

        // set locale time names
		$this->SetLocale(Application::Get('lc_time_name'));

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A' + IF(date_created IS NULL, '', date_created) as date_created,
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									hotel_id,
									package_name,
									DATE_FORMAT(start_date, \''.$sqlFieldDateFormat.'\') as start_date,
									DATE_FORMAT(finish_date, \''.$sqlFieldDateFormat.'\') as finish_date,
									minimum_nights,
									maximum_nights,
									minimum_rooms,
									IF(maximum_rooms >=0, maximum_rooms, "'.htmlentities(_UNLIMITED).'") as maximum_rooms,
									is_active
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'package_name'    => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'hotel_id'        => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'align'=>'center', 'width'=>'140px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
			'start_date'  	  => array('title'=>_START_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'140px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'finish_date'  	  => array('title'=>_FINISH_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'140px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'minimum_nights'  => array('title'=>_MIN.' '._NIGHTS, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'maximum_nights'  => array('title'=>_MAX.' '._NIGHTS, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'minimum_rooms'   => array('title'=>_MIN.' '._ROOMS, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'maximum_rooms'   => array('title'=>_MAX.' '._ROOMS, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'is_active'       => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255 ....
		//   Ex.: 'validation_maxlength'=>'255'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'hotel_id'               => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'required'=>($objLogin->IsLoggedInAs('hotelowner') ? true : false), 'width'=>'210px', 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
			'package_name'           => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'Package #_ '.@date('M Y'), 'validation_type'=>'text', 'unique'=>false, 'visible'=>true),
			'start_date'             => array('title'=>_START_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10'),
			'finish_date'            => array('title'=>_FINISH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10'),
			'minimum_nights'         => array('title'=>_MINIMUM_NIGHTS, 'type'=>'textbox', 'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>$default_minimum_nights, 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'validation_maximum'=>'100', 'unique'=>false, 'visible'=>true),
			'maximum_nights'         => array('title'=>_MAXIMUM_NIGHTS, 'type'=>'textbox', 'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>$default_minimum_nights, 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'validation_maximum'=>'100', 'unique'=>false, 'visible'=>true),
			'minimum_rooms'          => array('title'=>_MINIMUM_ROOMS, 'type'=>'textbox', 'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>$default_minimum_nights, 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'validation_maximum'=>'100', 'unique'=>false, 'visible'=>true),
			'maximum_rooms'          => array('title'=>_MAXIMUM_ROOMS, 'type'=>'textbox', 'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>$default_minimum_nights, 'validation_type'=>'numeric', 'validation_minimum'=>'-1', 'validation_maximum'=>'100', 'unique'=>false, 'visible'=>true, 'post_html'=>' ('.str_replace('_VAL_', '-1', _TO_SET_UNLIMITED_TOOLTIP).')'),
			'cancel_reservation_day' => array('title'=>_CANCELLATION_POLICY, 'type'=>'textbox',  'width'=>'40px', 'required'=>false, 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric|positive', 'post_html'=>' '._DAYS),
			'is_active'              => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255 ....
		//   Ex.: 'validation_maxlength'=>'255'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.hotel_id,
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.package_name,
								'.$this->tableName.'.start_date,
								'.$this->tableName.'.finish_date,
								'.$this->tableName.'.minimum_nights,
								'.$this->tableName.'.maximum_nights,
								'.$this->tableName.'.minimum_rooms,
								'.$this->tableName.'.maximum_rooms,
								'.$this->tableName.'.cancel_reservation_day,
								DATE_FORMAT('.$this->tableName.'.start_date, \''.$sqlFieldDateFormat.'\') as mod_start_date,
								DATE_FORMAT('.$this->tableName.'.finish_date, \''.$sqlFieldDateFormat.'\') as mod_finish_date,								
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'hotel_id'               => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'required'=>($objLogin->IsLoggedInAs('hotelowner') ? true : false), 'width'=>'210px', 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
			'package_name'           => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'Package #_ '.@date('M Y'), 'validation_type'=>'text', 'unique'=>false, 'visible'=>true),
			'start_date'             => array('title'=>_START_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'50', 'max_year'=>'10'),
			'finish_date'            => array('title'=>_FINISH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'50', 'max_year'=>'10'),
			'minimum_nights'         => array('title'=>_MINIMUM_NIGHTS, 'type'=>'textbox', 'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>$default_minimum_nights, 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'validation_maximum'=>'100', 'unique'=>false, 'visible'=>true),
			'maximum_nights'         => array('title'=>_MAXIMUM_NIGHTS, 'type'=>'textbox', 'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>$default_minimum_nights, 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'validation_maximum'=>'100', 'unique'=>false, 'visible'=>true),
			'minimum_rooms'          => array('title'=>_MINIMUM_ROOMS, 'type'=>'textbox', 'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>$default_minimum_nights, 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'validation_maximum'=>'100', 'unique'=>false, 'visible'=>true),
			'maximum_rooms'          => array('title'=>_MAXIMUM_ROOMS, 'type'=>'textbox', 'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>$default_minimum_nights, 'validation_type'=>'numeric', 'validation_minimum'=>'-1', 'validation_maximum'=>'100',  'unique'=>false, 'visible'=>true, 'post_html'=>' ('.str_replace('_VAL_', '-1', _TO_SET_UNLIMITED_TOOLTIP).')'),
			'cancel_reservation_day' => array('title'=>_CANCELLATION_POLICY, 'type'=>'textbox',  'width'=>'40px', 'required'=>false, 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric|positive', 'post_html'=>' '._DAYS),
			'is_active'              => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'hotel_id'               => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'source'=>$arr_hotels),
			'package_name'           => array('title'=>_NAME, 'type'=>'label'),
			'mod_start_date'         => array('title'=>_START_DATE, 'type'=>'label'),
			'mod_finish_date'        => array('title'=>_FINISH_DATE, 'type'=>'label'),
			'minimum_nights'         => array('title'=>_MINIMUM_NIGHTS, 'type'=>'label'),
			'maximum_nights'         => array('title'=>_MAXIMUM_NIGHTS, 'type'=>'label'),
			'minimum_rooms'          => array('title'=>_MINIMUM_ROOMS, 'type'=>'label'),
			'maximum_rooms'          => array('title'=>_MAXIMUM_ROOMS, 'type'=>'label'),
			'cancel_reservation_day' => array('title'=>_CANCELLATION_POLICY, 'type'=>'label'),
			'is_active'              => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
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
	 * Return package info
	 * @param $hotel_id
	 */
	public static function GetPackageInfo($hotel_id = 0)
	{
		$output = array('id'=>'', 'minimum_nights'=>'', 'maximum_nights'=>'');
		$sql = 'SELECT
					id,
					hotel_id,
					minimum_nights,
					maximum_nights,
					minimum_rooms,
					maximum_rooms,
					DATE_FORMAT(start_date, \'%M %d\') as start_date,
					DATE_FORMAT(finish_date, \'%M %d, %Y\') as finish_date,
					DATE_FORMAT(finish_date, \'%m/%d/%Y\') as formated_finish_date,
					DATE_FORMAT(finish_date, \'%Y\') as fd_y,
					DATE_FORMAT(finish_date, \'%m\') as fd_m,
					DATE_FORMAT(finish_date, \'%d\') as fd_d,
					hd.name as hotel_name
				FROM '.TABLE_PACKAGES.'
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON t.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.(!empty($hotel_id) ? ' hotel_id = '.(int)$hotel_id.' AND ' : '').'
                    \''.@date('Y-m-d').'\' >= start_date AND
                    \''.@date('Y-m-d').'\' <= finish_date AND
                    is_active = 1
				ORDER BY start_date DESC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output['id'] = $result[0]['id']; 
			$output['minimum_nights'] = $result[0]['minimum_nights'];
			$output['maximum_nights'] = $result[0]['maximum_nights']; 
			$output['minimum_rooms'] = $result[0]['minimum_rooms'];
			$output['maximum_rooms'] = $result[0]['maximum_rooms']; 
		}
		return $output;		
	}
	
	/**
	 * Return all packages info
	 * @param string $checkin_date
	 * @param string $checkout_date
	 */
	public static function GetAllActiveByDate($checkin_date = '', $checkout_date = '')
	{
		$output = array();
		
		if(empty($checkin_date) && empty($checkout_date)){
			return $output;
		}
		
		$sql = 'SELECT
					p.id,
					p.hotel_id,
					p.minimum_nights,
					p.maximum_nights,
					p.minimum_rooms,
					p.maximum_rooms,
					DATE_FORMAT(p.start_date, \'%M %d\') as start_date,
					DATE_FORMAT(p.finish_date, \'%M %d, %Y\') as finish_date,
					DATE_FORMAT(p.finish_date, \'%m/%d/%Y\') as formated_finish_date,
					DATE_FORMAT(p.finish_date, \'%Y\') as fd_y,
					DATE_FORMAT(p.finish_date, \'%m\') as fd_m,
					DATE_FORMAT(p.finish_date, \'%d\') as fd_d,
					hd.name as hotel_name
				FROM '.TABLE_PACKAGES.' p
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON p.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE
                    \''.$checkin_date.'\' >= p.start_date AND 
                    \''.$checkout_date.'\' <= p.finish_date AND
                    p.is_active = 1
				ORDER BY p.start_date DESC';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		for($i=0; $i<$result[1]; $i++){			
			$output[$result[0][$i]['hotel_id']] = array(
				'id' => $result[0][$i]['id'],
				'hotel_id' => $result[0][$i]['hotel_id'],
				'hotel_name' => $result[0][$i]['hotel_name'],
				'minimum_nights' => $result[0][$i]['minimum_nights'],
				'maximum_nights' => $result[0][$i]['maximum_nights'],
				'minimum_rooms' => $result[0][$i]['minimum_rooms'],
				'maximum_rooms' => $result[0][$i]['maximum_rooms'] 				
			);
		}
		
		return $output;		
	}
	
	
	/**
	 *	Before-Insertion record
	 */
	public function BeforeInsertRecord()
	{
		if(!$this->CheckStartFinishDate()) return false;
		if(!$this->CheckDateOverlapping()) return false;
		if(!$this->CheckMinMaxNights()) return false;
		if(!$this->CheckMinMaxRooms()) return false;
		return true;
	}

	/**
	 *	Before-updating record
	 */
	public function BeforeUpdateRecord()
	{
		if(!$this->CheckStartFinishDate()) return false;
		if(!$this->CheckDateOverlapping()) return false;
		if(!$this->CheckMinMaxNights()) return false;
		if(!$this->CheckMinMaxRooms()) return false;
		return true;
	}
	
	/**
	 *	Before-editing record
	 */
	public function BeforeEditRecord()
	{ 
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			redirect_to($this->formActionURL);
		}
		
		return true;
	}

	/**
	 *	Before-details record
	 */
	public function BeforeDetailsRecord()
	{
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			redirect_to($this->formActionURL);
		}
		
		return true;
	}

	/**
	 *	Before-deleting record
	 */
	public function BeforeDeleteRecord()
	{
		return $this->CheckRecordAssigned($this->curRecordId);
	}

	/**
	 * Check if start date is greater than finish date
	 */
	private function CheckStartFinishDate()
	{
		$start_date = MicroGrid::GetParameter('start_date', false);
		$finish_date = MicroGrid::GetParameter('finish_date', false);
		
		if($start_date > $finish_date){
			$this->error = _START_FINISH_DATE_ERROR;
			return false;
		}	
		return true;		
	}
	
	/**
	 * Check if there is a date overlapping
	 */
	private function CheckDateOverlapping()
	{
		$rid = MicroGrid::GetParameter('rid');
		$start_date = MicroGrid::GetParameter('start_date', false);
		$finish_date = MicroGrid::GetParameter('finish_date', false);
		$hotel_id = MicroGrid::GetParameter('hotel_id', false);

		$sql = 'SELECT * FROM '.TABLE_PACKAGES.'
				WHERE
					id != '.(int)$rid.' AND
					hotel_id = '.(int)$hotel_id.' AND
					is_active = 1 AND 
					(((\''.$start_date.'\' >= start_date) AND (\''.$start_date.'\' <= finish_date)) OR
					((\''.$finish_date.'\' >= start_date) AND (\''.$finish_date.'\' <= finish_date))) ';	
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$this->error = _TIME_PERIOD_OVERLAPPING_ALERT;
			return false;
		}
		return true;
	}
	
	/**
	 * Check if there is a min/max nights overlapping
	 */
	private function CheckMinMaxNights()
	{
		$rid = MicroGrid::GetParameter('rid');
		$min_nights = MicroGrid::GetParameter('minimum_nights', false);
		$max_nights = MicroGrid::GetParameter('maximum_nights', false);

		if($max_nights < $min_nights){
			$this->error = str_replace(array('_FIELD_', '_MIN_'), array('<b>'._MAXIMUM_NIGHTS.'</b>', $min_nights), _FIELD_VALUE_MINIMUM);
			return false;
		}
		return true;
	}

	/**
	 * Check if there is a min/max number rooms overlapping
	 */
	private function CheckMinMaxRooms()
	{
		$rid = MicroGrid::GetParameter('rid');
		$min_rooms = MicroGrid::GetParameter('minimum_rooms', false);
		$max_rooms = MicroGrid::GetParameter('maximum_rooms', false);

		if($max_rooms != 0 && $max_rooms != -1 && $max_rooms < $min_rooms){
			$this->error = str_replace(array('_FIELD_', '_MIN_'), array('<b>'._MAXIMUM_ROOMS.'</b>', $min_rooms), _FIELD_VALUE_MINIMUM);
			return false;
		}
		return true;
	}

	/**
	 * Check if specific record is assigned to given owner
	 * @param int $curRecordId
	 */
	private function CheckRecordAssigned($curRecordId = 0)
	{
		global $objSession;
		
		$sql = 'SELECT * 
				FROM '.$this->tableName.' 
				WHERE '.$this->primaryKey.' = '.(int)$curRecordId . $this->assigned_to_hotels;
				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] <= 0){
			$objSession->SetMessage('notice', draw_important_message(_WRONG_PARAMETER_PASSED, false));
			return false;
		}
		
		return true;		
	}

	/**
	 * Update package status
	 */
	public static function UpdateStatus()
	{
		$sql = 'UPDATE '.TABLE_PACKAGES.'
				SET is_active = 0
				WHERE finish_date < \''.@date('Y-m-d').'\' AND is_active = 1';    
		database_void_query($sql);
	}
	
	/**
	 * Get minimum nights for certain period
	 * 	@param $check_in
	 * 	@param $check_out
	 * 	@param $hotel_id
	 * 	@param $return_all
	 * 	@return mixed
	 */
	public static function GetMinimumNights($check_in, $check_out, $hotel_id = 0, $return_all = false)
	{
		$output = array('package_name'=>'', 'minimum_nights'=>'', 'start_date'=>'', 'finish_date'=>'', 'hotel_id'=>'', 'hotel_name'=>'');
		
		$sql = 'SELECT
					p.package_name,
					p.minimum_nights,
					p.start_date,
					p.finish_date,
					p.hotel_id,
					hd.name as hotel_name
				FROM '.TABLE_PACKAGES.' p
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON p.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.(!empty($hotel_id) ? ' p.hotel_id = '.(int)$hotel_id.' AND ' : '').'
					p.is_active = 1 AND 
					(((\''.$check_in.'\' >= p.start_date) AND (\''.$check_in.'\' <= p.finish_date)) OR
					((\''.$check_out.'\' >= p.start_date) AND (\''.$check_out.'\' <= p.finish_date))) ';	
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			if($return_all){
				$output = $result[0];
			}else{
				$output['package_name'] 	= $result[0][0]['package_name'];
				$output['minimum_nights'] 	= $result[0][0]['minimum_nights'];
				$output['start_date']     	= $result[0][0]['start_date'];
				$output['finish_date']    	= $result[0][0]['finish_date'];
				$output['hotel_id']       	= $result[0][0]['hotel_id'];
				$output['hotel_name']       = $result[0][0]['hotel_name'];				
			}
		}
		
	    return $output;
	}

	/**
	 * Get maximum nights for certain period
	 * 	@param $check_in
	 * 	@param $check_out
	 * 	@param $hotel_id
	 * 	@param $return_all
	 * 	@return mixed
	 */
	public static function GetMaximumNights($check_in, $check_out, $hotel_id = 0, $return_all = false)
	{
		$output = array('package_name'=>'', 'maximum_nights'=>'', 'start_date'=>'', 'finish_date'=>'', 'hotel_id'=>'', 'hotel_name'=>'');

		$sql = 'SELECT
					p.package_name,
					p.maximum_nights,
					p.start_date,
					p.finish_date,
					p.hotel_id,
					hd.name as hotel_name
				FROM '.TABLE_PACKAGES.' p
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON p.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.(!empty($hotel_id) ? ' p.hotel_id = '.(int)$hotel_id.' AND ' : '').'
					p.is_active = 1 AND 
					(((\''.$check_in.'\' >= p.start_date) AND (\''.$check_in.'\' <= p.finish_date)) OR
					((\''.$check_out.'\' >= p.start_date) AND (\''.$check_out.'\' <= p.finish_date))) ';	
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){			
			if($return_all){
				$output = $result[0];
			}else{
				$output['package_name'] 	= $result[0][0]['package_name'];
				$output['maximum_nights'] 	= $result[0][0]['maximum_nights'];
				$output['start_date']     	= $result[0][0]['start_date'];
				$output['finish_date']    	= $result[0][0]['finish_date'];
				$output['hotel_id']       	= $result[0][0]['hotel_id'];
				$output['hotel_name']       = $result[0][0]['hotel_name'];				
			}
		}
		
	    return $output;
	}
	
	/**
	 * Get minimum rooms for certain period
	 * 	@param $check_in
	 * 	@param $check_out
	 * 	@param $hotel_id
	 * 	@param $return_all
	 * 	@return mixed
	 */
	public static function GetMinimumRooms($check_in, $check_out, $hotel_id = 0, $return_all = false)
	{
		$output = array('package_name'=>'', 'minimum_rooms'=>'', 'start_date'=>'', 'finish_date'=>'', 'hotel_id'=>'', 'hotel_name'=>'');
		
		$sql = 'SELECT
					p.package_name,
					p.minimum_rooms,
					p.start_date,
					p.finish_date,
					p.hotel_id,
					hd.name as hotel_name
				FROM '.TABLE_PACKAGES.' p
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON p.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.(!empty($hotel_id) ? ' p.hotel_id = '.(int)$hotel_id.' AND ' : '').'
					p.is_active = 1 AND 
					(((\''.$check_in.'\' >= p.start_date) AND (\''.$check_in.'\' <= p.finish_date)) OR
					((\''.$check_out.'\' >= p.start_date) AND (\''.$check_out.'\' <= p.finish_date))) ';	
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			if($return_all){
				$output = $result[0];
			}else{
				$output['package_name'] 	= $result[0][0]['package_name'];
				$output['minimum_rooms'] 	= $result[0][0]['minimum_rooms'];
				$output['start_date']     	= $result[0][0]['start_date'];
				$output['finish_date']    	= $result[0][0]['finish_date'];
				$output['hotel_id']       	= $result[0][0]['hotel_id'];
				$output['hotel_name']       = $result[0][0]['hotel_name'];				
			}
		}
		
	    return $output;
	}

	/**
	 * Get maximum rooms for certain period
	 * 	@param $check_in
	 * 	@param $check_out
	 * 	@param $hotel_id
	 * 	@param $return_all
	 * 	@return mixed
	 */
	public static function GetMaximumRooms($check_in, $check_out, $hotel_id = 0, $return_all = false)
	{
		$output = array('package_name'=>'', 'maximum_rooms'=>'', 'start_date'=>'', 'finish_date'=>'', 'hotel_id'=>'', 'hotel_name'=>'');

		$sql = 'SELECT
					p.package_name,
					p.maximum_rooms,
					p.start_date,
					p.finish_date,
					p.hotel_id,
					hd.name as hotel_name
				FROM '.TABLE_PACKAGES.' p
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON p.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.(!empty($hotel_id) ? ' p.hotel_id = '.(int)$hotel_id.' AND ' : '').'
					p.is_active = 1 AND 
					(((\''.$check_in.'\' >= p.start_date) AND (\''.$check_in.'\' <= p.finish_date)) OR
					((\''.$check_out.'\' >= p.start_date) AND (\''.$check_out.'\' <= p.finish_date))) ';	
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){			
			if($return_all){
				$output = $result[0];
			}else{
				$output['package_name'] 	= $result[0][0]['package_name'];
				$output['maximum_rooms'] 	= $result[0][0]['maximum_rooms'];
				$output['start_date']     	= $result[0][0]['start_date'];
				$output['finish_date']    	= $result[0][0]['finish_date'];
				$output['hotel_id']       	= $result[0][0]['hotel_id'];
				$output['hotel_name']       = $result[0][0]['hotel_name'];				
			}
		}
		
	    return $output;
	}

	/*
	 * @param array $hotel_ids
	 * @param checkin
	 * @param checkout
	 * @return false|array(0 => result, 1 => count_rows)
	 * */
	public static function GetCanceledDays($hotel_ids = array(), $checkin = '', $checkout = '')
	{
		if(!is_array($hotel_ids) || empty($hotel_ids)){
			return false;
		}

		// Check Hotel IDs
		$count_hotels = 0;
		$hotel_ids_in_string = '';
		foreach($hotel_ids as $hotel_id){
			$hotel_id = (int)$hotel_id;
			if($hotel_id != 0){
				$count_hotels++;
				if(!empty($hotel_ids_in_string)){
					$hotel_ids_in_string .= ', ';
				}
				$hotel_ids_in_string .= $hotel_id;
			}
		}

		// Check and correction CheckIn and CheckOut
		if(!empty($checkin) && !empty($checkout)){
			// Date --> unix time --> the date in the correct format
			$checkin = date('Y-m-d', strtotime($checkin));
			$checkout = date('Y-m-d', strtotime($checkout));
		}

		if(!empty($hotel_ids_in_string) && !empty($checkin) && !empty($checkout)){
			$sql = 'SELECT
						p.hotel_id,
						p.cancel_reservation_day
					FROM '.TABLE_PACKAGES.' p
					WHERE
						p.hotel_id '.($count_hotels > 1 ? 'IN ('.$hotel_ids_in_string.')' : '= '.$hotel_ids_in_string).' AND 
						p.is_active = 1 AND 
						p.cancel_reservation_day != 0 AND
						(((\''.$checkin.'\' >= p.start_date) AND (\''.$checkin.'\' <= p.finish_date)) OR
						((\''.$checkout.'\' >= p.start_date) AND (\''.$checkout.'\' <= p.finish_date)))
					ORDER BY '.($count_hotels > 1 ? 'p.hotel_id DESC, ' : '').'p.cancel_reservation_day ASC';
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

			return $result;
		}
		return false;
	}
}

