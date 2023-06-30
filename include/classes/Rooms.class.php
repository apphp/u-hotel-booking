<?php

/**
 *	Rooms Class (for HotelSite ONLY)
 *  --------------
 *	Written by  : ApPHP
 *  Updated	    : 24.02.2016
 *	Written by  : ApPHP
 *
 *	PUBLIC:						STATIC:							PRIVATE:
 *  -----------					-----------						-----------
 *  __construct					GetRoomAvalibilityForWeek		GetMonthMaxDay
 *  __destruct                  GetRoomAvalibilityForMonth      CheckAvailabilityForPeriod
 *  DrawRoomAvailabilitiesForm  GetRoomInfo 					DrawPaginationLinks
 *  DrawRoomPricesForm          GetRoomTypes                    DrawHotelInfoBlock
 *  DeleteRoomAvailability      GetMonthLastDay                 DrawExtraBedsDDL
 *  DeleteRoomPrices 		    DrawSearchAvailabilityBlock     DrawWidgetRoomItem
 *  AddRoomAvailability         DrawSearchAvailabilityFooter    GetBestPriceRooms
 *  AddRoomPrices               DrawRoomsInfo
 *  UpdateRoomAvailability      ConvertToDecimal (private)
 *  UpdateRoomPrices            GetPriceForDate
 *  AfterInsertRecord           GetRoomPricesTable
 *  BeforeUpdateRecord          DrawRoomDescription
 *	AfterUpdateRecord           DrawRoomsInHotel
 *	AfterDeleteRecord           GetAllActive
 *	SearchFor                   GetAllRooms
 *	DrawSearchResult            GetRoomPrice
 *	GetAvailableRooms           GetRoomDefaultPrice
 *	DrawWidgetSearchResult      GetRoomWeekDefaultPrice
 *	GetBestPriceValue           GetRoomExtraBedsPrice
 *	GetArrBestRoomAdults        GetRoomPriceWithStayDiscount
 *	GetArrBestRoomCount         PrepareHotelNameField
 *	GetArrBestRooms             DrawRoomPricesNightDiscounts
 *	GetHotelIdBestPrice         DrawRoomPricesGuestsDiscounts
 *	GetArrAvailableRooms        GetRoomPriceIncludeNightsDiscount
 *	GetRoomPricesForYear        GetRoomPriceIncludeRoomsDiscount
 *	SwitchYearsRoomAvailability GetRoomRefundMoney
 *	                            NumberViewsIteration
 *	                            DrawWidgetJS
 **/

class Rooms extends MicroGrid {

	protected $debug = false;

	//-------------------------
	private $arrAvailableRooms;
	private $arrBeds;
	private $arrFloor;
	private $arrBathrooms;
	private $currencyFormat;
	private $roomsCount;
	private $defaultPrice;
	private $extraBedCharge;
	private $hotelsList;
	private $regionalManager = false;
	private $hotelOwner = false;
	private $relocationType = 'room';
	private $hotelIdBestPrice = 0;
	private $bestPriceValue = 0;
	private $arrBestPrices = array();
	private $arrBestRooms = array();
	private $arrBestRoomCount = array();
	private $arrBestRoomAdults = array();

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		parent::__construct();

		global $objLogin;
		$this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager');
		$this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner');

		$this->params = array();
		$this->arrAvailableRooms = array();

		## for standard fields
		if(isset($_POST['room_type']))                $this->params['room_type']                = prepare_input($_POST['room_type']);
		if(isset($_POST['room_short_description']))   $this->params['room_short_description']   = prepare_input($_POST['room_short_description']);
		if(isset($_POST['room_long_description']))    $this->params['room_long_description']    = prepare_input($_POST['room_long_description']);
		if(isset($_POST['max_adults']))               $this->params['max_adults']               = prepare_input($_POST['max_adults']);
		if(isset($_POST['max_children']))             $this->params['max_children']             = prepare_input($_POST['max_children']);
		if(isset($_POST['max_extra_beds']))           $this->params['max_extra_beds']           = prepare_input($_POST['max_extra_beds']);
		if(isset($_POST['room_count']))               $this->params['room_count']               = prepare_input($_POST['room_count']);
		if(isset($_POST['default_price']))            $this->params['default_price']            = prepare_input($_POST['default_price']);
		if(isset($_POST['extra_bed_charge']))         $this->params['extra_bed_charge']         = prepare_input($_POST['extra_bed_charge']);
		if(isset($_POST['discount_night_type']))      $this->params['discount_night_type']      = prepare_input($_POST['discount_night_type']);
		if(isset($_POST['discount_night_3']))         $this->params['discount_night_3']         = prepare_input($_POST['discount_night_3']);
		if(isset($_POST['discount_night_4']))         $this->params['discount_night_4']         = prepare_input($_POST['discount_night_4']);
		if(isset($_POST['discount_night_5']))         $this->params['discount_night_5']         = prepare_input($_POST['discount_night_5']);
		if(isset($_POST['discount_guests_type']))     $this->params['discount_guests_type']     = prepare_input($_POST['discount_guests_type']);
		if(isset($_POST['discount_guests_3']))        $this->params['discount_guests_3']        = prepare_input($_POST['discount_guests_3']);
		if(isset($_POST['discount_guests_4']))        $this->params['discount_guests_4']        = prepare_input($_POST['discount_guests_4']);
		if(isset($_POST['discount_guests_5']))        $this->params['discount_guests_5']        = prepare_input($_POST['discount_guests_5']);
		if(isset($_POST['refund_money_type']))        $this->params['refund_money_type']        = prepare_input($_POST['refund_money_type']);
		if(isset($_POST['refund_money_value']))       $this->params['refund_money_value']       = prepare_input($_POST['refund_money_value']);
		if(isset($_POST['priority_order']))           $this->params['priority_order']           = prepare_input($_POST['priority_order']);
		if(isset($_POST['beds']))                     $this->params['beds']                     = prepare_input($_POST['beds']);
		if(isset($_POST['single_beds']))              $this->params['single_beds']              = prepare_input($_POST['single_beds']);
		if(isset($_POST['double_beds']))              $this->params['double_beds']              = prepare_input($_POST['double_beds']);
		if(isset($_POST['folding_sofa_single_beds'])) $this->params['folding_sofa_single_beds'] = prepare_input($_POST['folding_sofa_single_beds']);
		if(isset($_POST['folding_sofa_double_beds'])) $this->params['folding_sofa_double_beds'] = prepare_input($_POST['folding_sofa_double_beds']);
		if(isset($_POST['bathrooms']))                $this->params['bathrooms']                = prepare_input($_POST['bathrooms']);
		if(isset($_POST['room_area']))                $this->params['room_area']                = prepare_input($_POST['room_area']);
		if(isset($_POST['facilities']))               $this->params['facilities']               = prepare_input($_POST['facilities']);
		if(isset($_POST['hotel_id']))                 $this->params['hotel_id']                 = prepare_input($_POST['hotel_id']);
		if(isset($_POST['room_floor']))               $this->params['room_floor']               = prepare_input($_POST['room_floor']);
		$image_prefix = (isset($_POST['hotel_id'])) ? prepare_input($_POST['hotel_id']).'_' : '';

		## for checkboxes
        $this->params['suitable_for_kids'] = isset($_POST['suitable_for_kids']) ? (int)$_POST['suitable_for_kids'] : '0';
        $this->params['is_active'] = !$this->regionalManager && isset($_POST['is_active']) ? (int)$_POST['is_active'] : '0';

		## for images
		if(isset($_POST['room_icon'])) {
			$this->params['room_icon'] = prepare_input($_POST['room_icon']);
		}elseif(isset($_FILES['room_icon']['name']) && $_FILES['room_icon']['name'] != ''){
			// nothing
		}elseif (self::GetParameter('action') == 'create'){
			$this->params['room_icon'] = '';
		}

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
        $watermark = (ModulesSettings::Get('rooms', 'watermark') == 'yes') ? true : false;
        $watermark_text = ModulesSettings::Get('rooms', 'watermark_text');

		// Detect hotel ID to pass it to add mode, when hotel is already selected
		$operation = MicroGrid::GetParameter('operation');
		$selected_hotel_id = (in_array($operation, array('filtering', 'sorting')) ? MicroGrid::GetParameter('filter_by_uhb_roomshotel_id', false) : '');

        $editHotelRooms     = ($this->hotelOwner && !$objLogin->HasPrivileges('edit_hotel_rooms')) ? false : true;
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_ROOMS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_rooms_management'.(!empty($selected_hotel_id) ? '&filter_by_uhb_roomshotel_id='.(int)$selected_hotel_id : '');
		$this->actions      = array(
                            'add'       => $editHotelRooms,
                            'edit'      => $editHotelRooms,
                            'details'   => true,
                            'delete'    => $editHotelRooms
        );
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;

		$this->allowLanguages = false;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();

		$this->WHERE_CLAUSE = '';
		$this->hotelsList = '';
		if($objLogin->IsLoggedInAs('hotelowner')){
			$this->hotelsList = implode(',', $objLogin->AssignedToHotels());
			$this->WHERE_CLAUSE .= 'WHERE '.(!empty($this->hotelsList) ? $this->tableName.'.hotel_id IN ('.$this->hotelsList.')' : '1 = 0');
		}elseif($this->regionalManager){
			$this->hotelsList = implode(',', AccountLocations::GetHotels($objLogin->GetLoggedID()));
			$this->WHERE_CLAUSE .= 'WHERE '.(!empty($this->hotelsList) ? $this->tableName.'.hotel_id IN ('.$this->hotelsList.')' : '1 = 0');
		}
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.hotel_id ASC, '.$this->tableName.'.priority_order ASC';

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		// Prepare hotels array
		$where_clause = '';
        if($this->regionalManager || $this->hotelOwner){
            $where_clause = !empty($this->hotelsList) ? TABLE_HOTELS.'.id IN ('.$this->hotelsList.')' : '1 = 0';
        }
        $total_hotels = Hotels::GetAllHotels($where_clause);
		$arr_hotels = array();
		$arr_hotels_filter = array();
		foreach($total_hotels[0] as $key => $val){
			$arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
			$arr_hotels_filter[$val['id']] = $val['name'].(!empty($val['location_name']) ? ' ('.$val['location_name'].') ' : '');
		}

		// prepare facilities array
		$total_facilities = RoomFacilities::GetAllActive();
		$arr_facilities = array();
		foreach($total_facilities[0] as $key => $val){
			$arr_facilities[$val['id']] = $val['name'];
		}

		// prepare discount types
		$arr_discount_types = array('0'=>_FIXED_PRICE, '1'=>_PERCENTAGE);
		// prepare refund money types
		$arr_refund_types = array('0'=>_FIRST_NIGHT, '1'=>_FIXED_PRICE, '2'=>_PERCENTAGE);

        $tooltip_image_size = str_replace('_SIZE_', '760 x 470 px', _RECOMMENDED_IMAGE_SIZE);

		// Prepare rooms ratings
		$sql = 'SELECT * FROM '.TABLE_RATINGS_ITEMS.' WHERE item LIKE \'rt_room_%\'';
		$ratings = database_query($sql, DATA_AND_ROWS);
		$rated_room_ids = array();
		if(!empty($ratings[1])){
			foreach($ratings[0] as $rating){
				$room_id = str_replace('rt_room_', '', $rating['item']);
				$rated_room_ids[$room_id] = $rating['totalrate'].' / '.$rating['nrrates'];
			}
		}

		$this->isFilteringAllowed = true;

        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		// define filtering fields
		$this->arrFilteringFields = array(
			$hotel_name => array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels_filter, 'sign'=>'=', 'width'=>'250px', 'visible'=>true),
		);

		$this->isAggregateAllowed = true;
		// define aggregate fields for View Mode
		$this->arrAggregateFields = array(
			'room_count' => array('function'=>'SUM', 'decimal_place'=>0),
			///'field2' => array('function'=>'AVG'),
		);

		// prepare languages array
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		$this->currencyFormat = get_currency_format();

		$this->arrBeds = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
		$this->arrFloor = $this->GetFloors();
		$this->arrBathrooms = array(0, 1, 2, 3);
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		$default_currency = Currencies::GetDefaultCurrency();

		$random_name = true;
		$booking_active = (Modules::IsModuleInstalled('booking')) ? ModulesSettings::Get('booking', 'is_active') : false;
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');

		//----------------------------------------------------------------------
		// VIEW MODE
		//----------------------------------------------------------------------
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.hotel_id,
									'.$this->tableName.'.max_adults,
									'.$this->tableName.'.suitable_for_kids,
									'.$this->tableName.'.max_children,
									'.$this->tableName.'.max_extra_beds,
									'.$this->tableName.'.room_count,
									'.$this->tableName.'.default_price,
									'.$this->tableName.'.extra_bed_charge,
									'.$this->tableName.'.room_icon,
									'.$this->tableName.'.room_icon_thumb,
									'.$this->tableName.'.priority_order,
									'.$this->tableName.'.is_active,
									CONCAT("<a href=\"index.php?admin=mod_room_prices&rid=", '.$this->tableName.'.'.$this->primaryKey.', "\" title=\"'._CLICK_TO_MANAGE.'\">", "[ '._PRICES.' ]", "</a>") as link_prices,
									CONCAT("<a href=\"index.php?admin=mod_room_availability&rid=", '.$this->tableName.'.'.$this->primaryKey.', "\" title=\"'._CLICK_TO_MANAGE.'\">", "[ '._AVAILABILITY.' ]", "</a>") as link_room_availability,
									CONCAT("<a href=\"index.php?admin=mod_booking_rooms_occupancy&sel_room_types=", '.$this->tableName.'.'.$this->primaryKey.', "\" title=\"'._CLICK_TO_MANAGE.'\">", "[ '._OCCUPANCY.' ]", "</a>") as link_room_occupancy,
									CONCAT("<a href=\"index.php?admin=mod_room_description&room_id=", '.$this->tableName.'.'.$this->primaryKey.', "\" title=\"'._CLICK_TO_MANAGE.'\">[ ", "'._DESCRIPTION.'", " ]</a>") as link_room_description,
									CONCAT(rd.room_type, "<a href=\"index.php?page=rooms&room_id=", '.$this->tableName.'.id, "&preview=yes\" target=\"_blank\" title=\"Open in new window\"> <img src=\"images/external_link.gif\" alt=\"\" /></a>") as room_name,
									rd.room_type,
									rd.room_short_description,
									rd.room_long_description
								FROM '.$this->tableName.'
									INNER JOIN '.TABLE_HOTELS.' ON '.$this->tableName.'.hotel_id = '.TABLE_HOTELS.'.id
									LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON '.$this->tableName.'.'.$this->primaryKey.' = rd.room_id AND rd.language_id = \''.$this->languageId.'\' ';
		// define view mode fields
		$this->arrViewModeFields = array(

			'room_icon_thumb'           => array('title'=>_ICON_IMAGE, 'type'=>'image', 'align'=>'center', 'width'=>'80px', 'image_width'=>'60px', 'image_height'=>'30px', 'target'=>'images/rooms/', 'no_image'=>'no_image.png'),
			//'room_type'                 => array('title'=>_TYPE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'32'),
			'room_name'                 => array('title'=>_TYPE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>''),
			'hotel_id'                  => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
			'room_count'                => array('title'=>_COUNT, 'type'=>'label', 'align'=>'center', 'width'=>'49px', 'maxlength'=>''),
			'max_adults'                => array('title'=>_ADULTS, 'type'=>'label', 'align'=>'center', 'width'=>'49px', 'maxlength'=>''),
            'suitable_for_kids'         => array('title'=>_SUITABLE_FOR_KIDS, 'type'=>'enum',  'align'=>'center', 'width'=>'49px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>(($allow_children == 'yes') ? true : false), 'source'=>$arr_is_active),
			'max_children'              => array('title'=>_CHILD, 'type'=>'label', 'align'=>'center', 'width'=>'49px', 'maxlength'=>'', 'visible'=>(($allow_children == 'yes' && !$this->hotelOwner) ? true : false)),
			'max_extra_beds'            => array('title'=>_EXTRA_BEDS, 'type'=>'label', 'align'=>'center', 'width'=>'49px', 'maxlength'=>'', 'visible'=>(($allow_extra_beds == 'yes') ? true : false)),
			'id'                        => array('title'=>_RATINGS, 'type'=>'enum',  'header_tooltip'=>htmlentities(_RATE.' / '._VOTES), 'align'=>'center', 'width'=>'85px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$rated_room_ids),
			'is_active'                 => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'49px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			'priority_order'            => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'60px', 'maxlength'=>'', 'movable'=>true, 'visible'=>(($this->hotelOwner) ? false : true)),
			'link_room_description'     => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'105px', 'maxlength'=>'', 'nowrap'=>'nowrap'),
			'link_prices'               => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'65px', 'maxlength'=>'', 'nowrap'=>'nowrap'),
			'link_room_availability'    => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'105px', 'maxlength'=>'', 'nowrap'=>'nowrap'),
			'link_room_occupancy'       => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'maxlength'=>'', 'nowrap'=>'nowrap', 'visible'=>(($booking_active == 'global') ? true : false)),
			'_empty_'                   => array('title'=>'', 'type'=>'label', 'align'=>'left', 'width'=>'15px')
		);

		//----------------------------------------------------------------------
		// ADD MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//----------------------------------------------------------------------
		// define add mode fields
		$this->arrAddModeFields = array(
			'separator_1'   =>array(
				'separator_info' => array('legend'=>_ROOM_DETAILS),
				'hotel_id'                  => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'width'=>'',   'required'=>true, 'readonly'=>false, 'default'=>((count($arr_hotels) == 1) ? key($arr_hotels) : $selected_hotel_id), 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'room_type'                 => array('title'=>_TYPE, 'type'=>'textbox',  'width'=>'270px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>'text'),
                'room_floor'                => array('title'=>_FLOOR, 'type'=>'enum', 'width'=>'150px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrFloor, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'room_short_description'    => array('title'=>_SHORT_DESCRIPTION, 'type'=>'textarea', 'editor_type'=>'wysiwyg', 'width'=>'410px', 'height'=>'40px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'validation_maxlength'=>'512'),
				'room_long_description'     => array('title'=>_LONG_DESCRIPTION, 'type'=>'textarea', 'editor_type'=>'wysiwyg', 'width'=>'410px', 'height'=>'70px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'validation_maxlength'=>'4096'),
				'max_adults'                => array('title'=>_MAX_ADULTS, 'type'=>'textbox', 'header_tooltip'=>_MAX_ADULTS_ACCOMMODATE, 'width'=>'40px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'1', 'validation_type'=>'numeric|positive'),
                'suitable_for_kids'         => array('title'=>_SUITABLE_FOR_KIDS, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>true),
				'max_children'              => array('title'=>_MAX_CHILDREN, 'type'=>'textbox', 'header_tooltip'=>_MAX_CHILDREN_ACCOMMODATE, 'width'=>'40px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'visible'=>(($allow_children == 'yes' && !$this->hotelOwner) ? true : false)),
				'max_extra_beds'            => array('title'=>_MAX_EXTRA_BEDS, 'type'=>'textbox',  'width'=>'30px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'1', 'default'=>'0', 'validation_type'=>'numeric|positive', 'visible'=>(($allow_extra_beds == 'yes') ? true : false)),
				'room_count'                => array('title'=>_ROOMS_COUNT, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'1', 'validation_type'=>'numeric|positive'),
				'bathrooms'                 => array('title'=>_BATHROOMS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBathrooms, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'room_area'                 => array('title'=>_ROOM_AREA, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'999', 'post_html'=>' m<sup>2</sup>'),
				'priority_order'            => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'35px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'visible'=>(($this->hotelOwner) ? false : true)),
				'is_active'                 => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>($this->regionalManager ? '0' : '1'), 'true_value'=>'1', 'false_value'=>'0', 'visible'=>($this->regionalManager ? false : true)),
			),
            'separator_8'   =>array(
                'separator_info' => array('legend'=>_BEDS),
                'beds'                      => array('title'=>_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'single_beds'               => array('title'=>_SINGLE_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'double_beds'               => array('title'=>_DOUBLE_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'folding_sofa_single_beds'  => array('title'=>_FOLDING_SOFA_SINGLE_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'folding_sofa_double_beds'  => array('title'=>_FOLDING_SOFA_DOUBLE_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            ),
			'separator_2'   =>array(
				'separator_info' => array('legend'=>_PRICES),
				'default_price'    => array('title'=>_DEFAULT_PRICE, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
				'extra_bed_charge' => array('title'=>_EXTRA_BED_CHARGE, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0.00', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
			),
		);

		if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
			$this->arrAddModeFields['separator_3'] = array(
				'separator_info' => array('legend'=>_LONG_TERM_STAY_DISCOUNT),
				'discount_night_type' => array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_discount_types, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'discount_night_3' => array('title'=>_DISCOUNT.': '._NIGHT_3, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-night-percent"></span>'),
				'discount_night_4' => array('title'=>_DISCOUNT.': '._NIGHT_4, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-night-percent"></span>'),
				'discount_night_5' => array('title'=>_DISCOUNT.': '._NIGHT_5.' +', 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-night-percent"></span>'),
			);
		}
		if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
			$this->arrAddModeFields['separator_4'] = array(
				'separator_info' => array('legend'=>(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT)),
				'discount_guests_type' => array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_discount_types, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'discount_guests_3' => array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_3 : _ROOMS_3), 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-guests-percent"></span>'),
				'discount_guests_4' => array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_4 : _ROOMS_4), 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-guests-percent"></span>'),
				'discount_guests_5' => array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_5 : _ROOMS_5).' +', 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-guests-percent"></span>'),
			);
		}
		if(ModulesSettings::Get('rooms', 'refund_money') == 'yes'){
			$this->arrAddModeFields['separator_5'] = array(
				'separator_info'     => array('legend'=>_CANCELLATION_POLICY),
				'refund_money_type'  => array('title'=>_TYPE_REFUND_MONEY, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_refund_types, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'refund_money_value' => array('title'=>_SIZE_REFUND_MONEY, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="refund-money-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="refund-money-percent"></span>'),
			);
		}

		$this->arrAddModeFields['separator_6'] = array(
			'separator_info' => array('legend'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_FACILITIES : _ROOM_FACILITIES),
			'facilities'     => array('title'=>_FACILITIES, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_facilities, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true),
		);
		$this->arrAddModeFields['separator_7'] = array(
			'separator_info' => array('legend'=>_IMAGES, 'columns'=>'2'),
			'room_icon'      => array('title'=>_ICON_IMAGE, 'type'=>'image', 'header_tooltip'=>$tooltip_image_size, 'width'=>'210px', 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'icon_', 'unique'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'room_icon_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_1' => array('title'=>_IMAGE.' 1', 'type'=>'image', 'header_tooltip'=>$tooltip_image_size, 'width'=>'210px', 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view1_', 'unique'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_1_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_2' => array('title'=>_IMAGE.' 2', 'type'=>'image', 'header_tooltip'=>$tooltip_image_size, 'width'=>'210px', 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view2_', 'unique'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_2_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_3' => array('title'=>_IMAGE.' 3', 'type'=>'image', 'header_tooltip'=>$tooltip_image_size, 'width'=>'210px', 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view3_', 'unique'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_3_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_4' => array('title'=>_IMAGE.' 4', 'type'=>'image', 'header_tooltip'=>$tooltip_image_size, 'width'=>'210px', 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view4_', 'unique'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_4_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_5' => array('title'=>_IMAGE.' 5', 'type'=>'image', 'header_tooltip'=>$tooltip_image_size, 'width'=>'210px', 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view5_', 'unique'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_5_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
		);

		//----------------------------------------------------------------------
		// EDIT MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//----------------------------------------------------------------------
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.hotel_id,
								'.$this->tableName.'.room_type,
								'.$this->tableName.'.room_floor,
								'.$this->tableName.'.room_short_description,
								'.$this->tableName.'.room_long_description,
								'.$this->tableName.'.max_adults,
								'.$this->tableName.'.suitable_for_kids,
								'.$this->tableName.'.max_children,
								'.$this->tableName.'.max_extra_beds,
								'.$this->tableName.'.room_count,
								'.$this->tableName.'.default_price,
								'.$this->tableName.'.extra_bed_charge,
								'.$this->tableName.'.discount_night_type,
								'.$this->tableName.'.discount_night_3,
								'.$this->tableName.'.discount_night_4,
								'.$this->tableName.'.discount_night_5,
								'.$this->tableName.'.discount_guests_type,
								'.$this->tableName.'.discount_guests_3,
								'.$this->tableName.'.discount_guests_4,
								'.$this->tableName.'.discount_guests_5,
								'.$this->tableName.'.refund_money_type,
								'.$this->tableName.'.refund_money_value,
								'.$this->tableName.'.beds,
								'.$this->tableName.'.single_beds,
								'.$this->tableName.'.double_beds,
								'.$this->tableName.'.folding_sofa_single_beds,
								'.$this->tableName.'.folding_sofa_double_beds,
								'.$this->tableName.'.bathrooms,
								'.$this->tableName.'.room_area,
								'.$this->tableName.'.facilities,
								'.$this->tableName.'.room_icon,
								'.$this->tableName.'.room_icon_thumb,
								'.$this->tableName.'.room_picture_1,
								'.$this->tableName.'.room_picture_1_thumb,
								'.$this->tableName.'.room_picture_2,
								'.$this->tableName.'.room_picture_2_thumb,
								'.$this->tableName.'.room_picture_3,
								'.$this->tableName.'.room_picture_3_thumb,
								'.$this->tableName.'.room_picture_4,
								'.$this->tableName.'.room_picture_4_thumb,
								'.$this->tableName.'.room_picture_5,
								'.$this->tableName.'.room_picture_5_thumb,
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.is_active,
								rd.room_type as m_room_type,
								'.TABLE_HOTELS.'.cancel_reservation_day
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON '.$this->tableName.'.'.$this->primaryKey.' = rd.room_id
								INNER JOIN '.TABLE_HOTELS.' ON '.$this->tableName.'.hotel_id = '.TABLE_HOTELS.'.id
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_1'   =>array(
				'separator_info' => array('legend'=>_ROOM_DETAILS),
				'hotel_id'                  => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'width'=>'',   'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'm_room_type'               => array('title'=>_ROOM_TYPE, 'type'=>'label'),
                'room_floor'                => array('title'=>_FLOOR, 'type'=>'enum', 'width'=>'150px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrFloor, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'max_adults'                => array('title'=>_MAX_ADULTS, 'type'=>'textbox', 'header_tooltip'=>_MAX_ADULTS_ACCOMMODATE, 'width'=>'40px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'', 'validation_type'=>'numeric|positive'),
                'suitable_for_kids'         => array('title'=>_SUITABLE_FOR_KIDS, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>true),
                'max_children'              => array('title'=>_MAX_CHILDREN, 'type'=>'textbox', 'header_tooltip'=>_MAX_CHILDREN_ACCOMMODATE, 'width'=>'40px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'', 'validation_type'=>'numeric|positive', 'visible'=>(($allow_children == 'yes' && !$this->hotelOwner) ? true : false)),
				'max_extra_beds'            => array('title'=>_MAX_EXTRA_BEDS, 'type'=>'textbox',  'width'=>'30px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'1', 'default'=>'0', 'validation_type'=>'numeric|positive', 'visible'=>(($allow_extra_beds == 'yes') ? true : false)),
				'room_count'                => array('title'=>_ROOMS_COUNT, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive'),
                'bathrooms'                 => array('title'=>_BATHROOMS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBathrooms, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'room_area'                 => array('title'=>_ROOM_AREA, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'999', 'post_html'=>' m<sup>2</sup>'),
				'priority_order'            => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'35px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'visible'=>(($this->hotelOwner) ? false : true)),
				'is_active'                 => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>($this->regionalManager ? false : true)),
			),
            'separator_8'   =>array(
                'separator_info' => array('legend'=>_BEDS),
                'beds'                      => array('title'=>_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'single_beds'               => array('title'=>_SINGLE_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'double_beds'               => array('title'=>_DOUBLE_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'folding_sofa_single_beds'  => array('title'=>_FOLDING_SOFA_SINGLE_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'folding_sofa_double_beds'  => array('title'=>_FOLDING_SOFA_DOUBLE_BEDS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrBeds, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            ),
			'separator_2' => array(
				'separator_info' => array('legend'=>_PRICES),
				'default_price'    => array('title'=>_DEFAULT_PRICE, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
				'extra_bed_charge' => array('title'=>_EXTRA_BED_CHARGE, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
			),
		);
		if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
			$this->arrEditModeFields['separator_3'] = array(
				'separator_info' => array('legend'=>_LONG_TERM_STAY_DISCOUNT),
				'discount_night_type' => array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_discount_types, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'discount_night_3' => array('title'=>_DISCOUNT.': '._NIGHT_3, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-night-percent"></span>'),
				'discount_night_4' => array('title'=>_DISCOUNT.': '._NIGHT_4, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-night-percent"></span>'),
				'discount_night_5' => array('title'=>_DISCOUNT.': '._NIGHT_5.' +', 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-night-percent"></span>'),
			);
		}
		if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
			$this->arrEditModeFields['separator_4'] = array(
				'separator_info' => array('legend'=>(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT)),
				'discount_guests_type' 	=> array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_discount_types, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'discount_guests_3' 	=> array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_3 : _ROOMS_3), 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-guests-percent"></span>'),
				'discount_guests_4' 	=> array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_4 : _ROOMS_4), 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-guests-percent"></span>'),
				'discount_guests_5' 	=> array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_5 : _ROOMS_5).' +', 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="discount-guests-percent"></span>'),
			);
		}
		if(ModulesSettings::Get('rooms', 'refund_money') == 'yes'){
			$this->arrEditModeFields['separator_5'] = array(
				'separator_info' => array('legend'=>_CANCELLATION_POLICY),
				'cancel_reservation_day' => array('title'=>_DAYS_TO_CANCEL, 'type'=>'label', 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'post_html'=>' '._DAYS),
				'refund_money_type'  	 => array('title'=>_TYPE_REFUND_MONEY, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_refund_types, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'refund_money_value' 	 => array('title'=>_SIZE_REFUND_MONEY, 'type'=>'textbox',  'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>'<span class="refund-money-price" data-currency="'.$default_currency.'"></span> ', 'post_html'=>' <span class="refund-money-percent"></span>'),
			);
		}

		$this->arrEditModeFields['separator_6'] = array(
			'separator_info' => array('legend'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_FACILITIES : _ROOM_FACILITIES),
			'facilities'     => array('title'=>_FACILITIES, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_facilities, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true),
		);
		$this->arrEditModeFields['separator_7'] = array(
			'separator_info' => array('legend'=>_IMAGES, 'columns'=>'2'),
			'room_icon'      => array('title'=>_ICON_IMAGE, 'type'=>'image', 'width'=>'210px', 'header_tooltip'=>$tooltip_image_size, 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'icon_', 'thumbnail_create'=>true, 'thumbnail_field'=>'room_icon_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_1' => array('title'=>_IMAGE.' 1', 'type'=>'image', 'width'=>'210px', 'header_tooltip'=>$tooltip_image_size, 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view1_', 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_1_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_2' => array('title'=>_IMAGE.' 2', 'type'=>'image', 'width'=>'210px', 'header_tooltip'=>$tooltip_image_size, 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view2_', 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_2_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_3' => array('title'=>_IMAGE.' 3', 'type'=>'image', 'width'=>'210px', 'header_tooltip'=>$tooltip_image_size, 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view3_', 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_3_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_4' => array('title'=>_IMAGE.' 4', 'type'=>'image', 'width'=>'210px', 'header_tooltip'=>$tooltip_image_size, 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view4_', 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_4_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
			'room_picture_5' => array('title'=>_IMAGE.' 5', 'type'=>'image', 'width'=>'210px', 'header_tooltip'=>$tooltip_image_size, 'required'=>false, 'target'=>'images/rooms/', 'no_image'=>'', 'random_name'=>$random_name, 'image_name_pefix'=>$image_prefix.'view5_', 'thumbnail_create'=>true, 'thumbnail_field'=>'room_picture_5_thumb', 'thumbnail_width'=>'190px', 'thumbnail_height'=>'', 'file_maxsize'=>'900k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
		);

		//----------------------------------------------------------------------
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_1'   =>array(
				'separator_info'            => array('legend'=>_ROOM_DETAILS),
				'hotel_id'                  => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'source'=>$arr_hotels),
				'room_type'                 => array('title'=>_TYPE, 'type'=>'label'),
				'room_floor'                => array('title'=>_FLOOR, 'type'=>'label'),
				'max_adults'                => array('title'=>_MAX_ADULTS, 'type'=>'label', 'header_tooltip'=>_MAX_ADULTS_ACCOMMODATE, ),
                'suitable_for_kids'         => array('title'=>_SUITABLE_FOR_KIDS, 'type'=>'enum', 'source'=>$arr_is_active),
                'max_children'              => array('title'=>_MAX_CHILDREN, 'type'=>'label', 'header_tooltip'=>_MAX_CHILDREN_ACCOMMODATE, 'visible'=>(($allow_children == 'yes'  && !$this->hotelOwner) ? true : false)),
				'max_extra_beds'            => array('title'=>_MAX_EXTRA_BEDS, 'type'=>'label', 'visible'=>(($allow_extra_beds == 'yes') ? true : false)),
				'room_count'                => array('title'=>_ROOMS_COUNT, 'type'=>'label'),
				'beds'                      => array('title'=>_BEDS, 'type'=>'label'),
				'single_beds'               => array('title'=>_SINGLE_BEDS, 'type'=>'label'),
				'double_beds'               => array('title'=>_DOUBLE_BEDS, 'type'=>'label'),
				'folding_sofa_single_beds'  => array('title'=>_FOLDING_SOFA_SINGLE_BEDS, 'type'=>'label'),
				'folding_sofa_double_beds'  => array('title'=>_FOLDING_SOFA_DOUBLE_BEDS, 'type'=>'label'),
				'bathrooms'                 => array('title'=>_BATHROOMS, 'type'=>'label'),
				'room_area'                 => array('title'=>_ROOM_AREA, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'post_html'=>' m<sup>2</sup>'),
				'id'	                    => array('title'=>_RATINGS.' ('.htmlentities(_RATE.' / '._VOTES).')', 'type'=>'enum', 'source'=>$rated_room_ids),
				'priority_order'            => array('title'=>_ORDER, 'type'=>'label', 'visible'=>((!$this->hotelOwner) ? false : true)),
				'is_active'                 => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
			),
			'separator_2'   =>array(
				'separator_info' => array('legend'=>_PRICES),
				'default_price'  	=> array('title'=>_DEFAULT_PRICE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>$default_currency),
				'extra_bed_charge'  => array('title'=>_EXTRA_BED_CHARGE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>$default_currency),
			),
		);
		if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
			$this->arrDetailsModeFields['separator_3'] = array(
				'separator_info' => array('legend'=>_LONG_TERM_STAY_DISCOUNT),
				'discount_night_type'   => array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'source'=>$arr_discount_types),
				'discount_night_3' 		=> array('title'=>_DISCOUNT.': '._NIGHT_3, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span>', 'post_html'=>'<span class="discount-night-percent"></span>'),
				'discount_night_4' 		=> array('title'=>_DISCOUNT.': '._NIGHT_4, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span>', 'post_html'=>'<span class="discount-night-percent"></span>'),
				'discount_night_5' 		=> array('title'=>_DISCOUNT.': '._NIGHT_5.' +', 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>'<span class="discount-night-price" data-currency="'.$default_currency.'"></span>', 'post_html'=>'<span class="discount-night-percent"></span>'),
			);
		}
		if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
			$this->arrDetailsModeFields['separator_4'] = array(
				'separator_info' => array('legend'=>(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT)),
				'discount_guests_type'  => array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'source'=>$arr_discount_types),
				'discount_guests_3' 	=> array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_3 : _ROOMS_3), 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span>', 'post_html'=>'<span class="discount-guests-percent"></span>'),
				'discount_guests_4' 	=> array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_4 : _ROOMS_4), 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span>', 'post_html'=>'<span class="discount-guests-percent"></span>'),
				'discount_guests_5' 	=> array('title'=>_DISCOUNT.': '.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_5 : _ROOMS_5).' +', 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>'<span class="discount-guests-price" data-currency="'.$default_currency.'"></span>', 'post_html'=>'<span class="discount-guests-percent"></span>'),
			);
		}
		if(ModulesSettings::Get('rooms', 'refund_money') == 'yes'){
			$this->arrDetailsModeFields['separator_5'] = array(
				'separator_info' => array('legend'=>_CANCELLATION_POLICY),
				'cancel_reservation_day' => array('title'=>_DAYS_TO_CANCEL, 'type'=>'label', 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'post_html'=>' '._DAYS),
				'refund_money_type'  	=> array('title'=>_TYPE_REFUND_MONEY, 'type'=>'enum', 'source'=>$arr_refund_types),
				'refund_money_value' 	=> array('title'=>_SIZE_REFUND_MONEY, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>'<span class="refund-money-price" data-currency="'.$default_currency.'"></span>', 'post_html'=>'<span class="refund-money-percent"></span>'),
			);
		}

		$this->arrDetailsModeFields['separator_6'] = array(
			'separator_info' => array('legend'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_FACILITIES : _ROOM_FACILITIES),
			'facilities'     => array('title'=>_FACILITIES, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_facilities, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true),
		);
		$this->arrDetailsModeFields['separator_7'] = array(
			'separator_info' => array('legend'=>_IMAGES, 'columns'=>'2'),
			'room_icon'      => array('title'=>_ICON_IMAGE, 'type'=>'image', 'target'=>'images/rooms/', 'no_image'=>'no_image.png'),
			'room_picture_1' => array('title'=>_IMAGE.' 1', 'type'=>'image', 'target'=>'images/rooms/', 'no_image'=>'no_image.png'),
			'room_picture_2' => array('title'=>_IMAGE.' 2', 'type'=>'image', 'target'=>'images/rooms/', 'no_image'=>'no_image.png'),
			'room_picture_3' => array('title'=>_IMAGE.' 3', 'type'=>'image', 'target'=>'images/rooms/', 'no_image'=>'no_image.png'),
			'room_picture_4' => array('title'=>_IMAGE.' 4', 'type'=>'image', 'target'=>'images/rooms/', 'no_image'=>'no_image.png'),
			'room_picture_5' => array('title'=>_IMAGE.' 5', 'type'=>'image', 'target'=>'images/rooms/', 'no_image'=>'no_image.png'),
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
	 * Draws room availabilities form
	 * @param $rid
	 */
	public function DrawRoomAvailabilitiesForm($rid)
	{
		global $objSettings;

		$nl = "\n";

		$sql = 'SELECT *
				FROM '.TABLE_ROOMS.'
				WHERE id = '.(int)$rid.'
				'.(!empty($this->hotelsList) ? ' AND '.TABLE_ROOMS.'.hotel_id IN ('.$this->hotelsList.')' : '');
		$room = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($room[1] == 0){
			draw_important_message(_WRONG_PARAMETER_PASSED);
			return false;
		}

		$default_currency_info = Currencies::GetDefaultCurrencyInfo();
		if($default_currency_info['symbol_placement'] == 'before'){
			$currency_l_sign = $default_currency_info['symbol'];
			$currency_r_sign = '';
		}else{
			$currency_l_sign = '';
			$currency_r_sign = $default_currency_info['symbol'];
		}

		$lang['weeks'][0] = (defined('_SU')) ? _SU : 'Su';
		$lang['weeks'][1] = (defined('_MO')) ? _MO : 'Mo';
		$lang['weeks'][2] = (defined('_TU')) ? _TU : 'Tu';
		$lang['weeks'][3] = (defined('_WE')) ? _WE : 'We';
		$lang['weeks'][4] = (defined('_TH')) ? _TH : 'Th';
		$lang['weeks'][5] = (defined('_FR')) ? _FR : 'Fr';
		$lang['weeks'][6] = (defined('_SA')) ? _SA : 'Sa';

		$lang['months'][1] = (defined('_JANUARY')) ? _JANUARY : 'January';
		$lang['months'][2] = (defined('_FEBRUARY')) ? _FEBRUARY : 'February';
		$lang['months'][3] = (defined('_MARCH')) ? _MARCH : 'March';
		$lang['months'][4] = (defined('_APRIL')) ? _APRIL : 'April';
		$lang['months'][5] = (defined('_MAY')) ? _MAY : 'May';
		$lang['months'][6] = (defined('_JUNE')) ? _JUNE : 'June';
		$lang['months'][7] = (defined('_JULY')) ? _JULY : 'July';
		$lang['months'][8] = (defined('_AUGUST')) ? _AUGUST : 'August';
		$lang['months'][9] = (defined('_SEPTEMBER')) ? _SEPTEMBER : 'September';
		$lang['months'][10] = (defined('_OCTOBER')) ? _OCTOBER : 'October';
		$lang['months'][11] = (defined('_NOVEMBER')) ? _NOVEMBER : 'November';
		$lang['months'][12] = (defined('_DECEMBER')) ? _DECEMBER : 'December';

		$room_type 	   = isset($_REQUEST['room_type']) ? prepare_input($_REQUEST['room_type']) : '';
		$from_new 	   = isset($_POST['from_new']) ? prepare_input($_POST['from_new']) : '';
		$to_new 	   = isset($_POST['to_new']) ? prepare_input($_POST['to_new']) : '';
		$year 	       = isset($_REQUEST['year']) ? prepare_input($_REQUEST['year']) : 'current';
		$ids_list 	   = '';
		$max_days 	   = 0;
		$output        = '';
		$output_week_days = '';
		$current_month = date('m');
		$current_year  = date('Y');
		$selected_year  = ($year == 'next') ? $current_year+1 : $current_year;

		$room_info = $this->GetInfoByID($rid);
		$room_count = isset($room_info['room_count']) ? $room_info['room_count'] : '0';

		// Prepare room prices for current year
		$room_prices = $this->GetRoomPricesForYear($rid, $selected_year);

		$output .= '<script type="text/javascript">
			function submitAvailabilityForm(task){
				if(task == "refresh"){
					document.getElementById("task").value = task;
					document.getElementById("frmRoomAvailability").submit();				
				}
				else if(task == "delete"){
					if(confirm("'._DELETE_WARNING_COMMON.'")){
						document.getElementById("task").value = task;
						document.getElementById("frmRoomAvailability").submit();
					}				
				}
				else if(task == "update" || task == "add_new"){
					document.getElementById("task").value = task;
					document.getElementById("frmRoomAvailability").submit();
				}
				else if(task == "switch_years"){
					if(confirm("'._SWITCH_YEARS_CONFIRM.'")){
						document.getElementById("task").value = task;
						document.getElementById("frmRoomAvailability").submit();
					}
				}
			}
			function toggleAvailability(selection_type, rid){				
				var selection_type = (selection_type == 1) ? true : false;
				var room_count = "'.$room_count.'";
				for(i=1; i<=31; i++){
					if(document.getElementById("aval_"+rid+"_"+i))
					   document.getElementById("aval_"+rid+"_"+i).value = (selection_type) ? room_count : "0";
				}
			}
		</script>'.$nl;

		$output .= '<div class="table-responsive">';
		$output .= '<form action="index.php?admin=mod_room_availability" id="frmRoomAvailability" method="post">';
		$output .= draw_hidden_field('task', 'update', false, 'task');
		$output .= draw_hidden_field('rid', $rid, false, 'rid');
		$output .= draw_hidden_field('year', $year, false, 'year');
		$output .= draw_hidden_field('room_type', $room_type, false, 'room_type');
		$output .= draw_token_field(false);

		$output .= '<table width="100%">';
		$output .= '<tr>';
		$output .= '<td align="left" colspan="22">
						<span class="gray">'.str_replace('_MAX_', $room_count, _AVAILABILITY_ROOMS_NOTE).'</span>						
					</td>
					<td align="right" colspan="5">
						<button type="button" class="form_button" style="width:120px" onclick="javascript:submitAvailabilityForm(\'switch_years\')">
							<span class="glyphicon glyphicon-calendar"></span> '._SWITCH_YEARS.'
						</button>
					</td>
					<td align="right" colspan="5">
						<button type="button" class="form_button" style="width:100px" onclick="javascript:submitAvailabilityForm(\'refresh\')">
							<span class="glyphicon glyphicon-refresh"></span> '._REFRESH.'
						</button>
					</td>
					<td align="right" colspan="6">
						<input type="button" class="form_button" style="width:130px" onclick="javascript:submitAvailabilityForm(\'update\')" value="'._BUTTON_SAVE_CHANGES.'">
					</td>';
		$output .= '</tr>';
		$output .= '<tr><td colspan="39">&nbsp;</td></tr>';

		$count = 0;
		$week_day = date('w', mktime('0', '0', '0', '1', '1', $selected_year));
		// fill empty cells from the beginning of month line
		while($count < $week_day){
			$td_class = (($count == 0 || $count == 6) ? 'day_td_w' : '');	// 0 - 'Sun', 6 - 'Sat'
			$output_week_days .= '<td class="'.$td_class.'">'.$lang['weeks'][$count].'</td>';
			$count++;
		}
		// fill cells at the middle
		for($day = 1; $day <= 31; $day ++){
			$week_day = date('w', mktime('0', '0', '0', '1', $day, $selected_year));
			$td_class = (($week_day == 0 || $week_day == 6) ? 'day_td_w' : '');	// 0 - 'Sun', 6 - 'Sat'
			$output_week_days .= '<td class="'.$td_class.'">'.$lang['weeks'][$week_day].'</td>';
		}
		$max_days = $count + 31;
		// fill empty cells at the end of month line
		if($max_days < 37){
			$count=0;
			while($count < (37-$max_days)){
				$week_day++;
				$count++;
				$week_day_mod = $week_day % 7;
				$td_class = (($week_day_mod == 0 || $week_day_mod == 6) ? 'day_td_w' : '');	// 0 - 'Sun', 6 - 'Sat'
				$output_week_days .= '<td class="'.$td_class.'">'.$lang['weeks'][$week_day_mod].'</td>';
			}
			$max_days += $count;
		}

		// draw week days
		$output .= '<tr style="text-align:center;background-color:#cccccc;">';
		$output .= '<td style="text-align:left;background-color:#ffffff;">';
		$output .= '<select class="_form-control mgrid_select" style="width:90px;margin-bottom:10px;" name="selYear" onchange="javascript:appGoTo(\'admin=mod_room_availability\',\'&rid='.$rid.'&year=\'+this.value)">';
		$output .= '<option value="current" '.(($year == 'current') ? 'selected="selected"' : '').'>'.$current_year.'</option>';
		$output .= '<option value="next" '.(($year == 'next') ? 'selected="selected"' : '').'>'.($current_year+1).'</option>';
		$output .= '</select>';
		$output .= '</td>';
		$output .= '<td align="center" style="padding:0px 2px;background-color:#ffffff;"><img src="images/check_all.gif" alt="check all" /></td>';
		$output .= $output_week_days;
		$output .= '</tr>';

		$sql = 'SELECT * FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE room_id = '.(int)$rid.' AND y = '.(($selected_year == $current_year) ? '0' : '1').' ORDER BY m ASC';
		$room = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        for($i=0; $i < $room[1]; $i++){
            $is_current_month = false;
			$is_current_year = ($selected_year == $current_year) ? true : false;
            $selected_month = $room[0][$i]['m'];
            $current_day = date('d');
            if($selected_month == $current_month){
                $tr_class = 'm_current';
                $is_current_month = true;
            }
			else{
				$tr_class = (($i%2==0) ? 'm_odd' : 'm_even');
			}
			$output .= '<tr align="center" class="'.$tr_class.'">';
			$output .= '<td align="left">&nbsp;<b>'.$lang['months'][$selected_month].'</b></td>';
			$output .= '<td><div class="checkbox" style="margin-left:6px;"><input type="checkbox" class="form_checkbox" onclick="toggleAvailability(this.checked,\''.$room[0][$i]['id'].'\')" /><label></label></div></td>';
			$max_day = $this->GetMonthMaxDay($selected_year, $selected_month);

			// Fill empty cells from the beginning of month line
			$count = date('w', mktime('0', '0', '0', $selected_month, 1, $selected_year));
			$max_days -= $count; /* subtract days that were missed from the beginning of the month */
			while($count--) $output .= '<td></td>';
			// Fill cells at the middle
			for($day = 1; $day <= $max_day; $day ++){
				if($room[0][$i]['d'.$day] == 0) {
					$day_color = 'dc_none';
				}elseif($room[0][$i]['d'.$day] >= $room_count){
					$day_color = 'dc_all';
				}elseif($room[0][$i]['d'.$day] > 0 && $room[0][$i]['d'.$day] < $room_count){
					$day_color = 'dc_part';
				}else{
					$day_color = 'dc_none';
				}
				$week_day = date('w', mktime('0', '0', '0', $selected_month, $day, $selected_year));
				$td_class = (($week_day == 0 || $week_day == 6) ? 'day_td_w' : 'day_td'); // 0 - 'Sun', 6 - 'Sat'
                if($is_current_year && $is_current_month && $current_day == $day){
                    $td_class .= ' day_td_current';
                }

				if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){
					$current_date = $selected_year.'-'.$this->ConvertToDecimal($selected_month).'-'.$this->ConvertToDecimal($day);
					$td_title = htmlentities(_ROOMS_COUNT.': '.$room_count."\n"._AVAILABLE_ROOMS.': '.$room[0][$i]['d'.$day]."\n"._RESERVED_ROOMS.': '.($room_count - ($room[0][$i]['a'.$day] + $room[0][$i]['d'.$day]))."\n"._CLOSED_ROOMS.': '.$room[0][$i]['a'.$day].(isset($room_prices[$current_date]) ? "\n--------\n"._PRICE.': '.$currency_l_sign.$room_prices[$current_date].$currency_r_sign : ''));
					$output .= '<td class="'.$td_class.'" title="'.$td_title.'"><label class="l_day">'.$day.'</label><br><input class="day_a '.$day_color.'" maxlength="3" name="aval_'.$room[0][$i]['id'].'_'.$day.'" id="aval_'.$room[0][$i]['id'].'_'.$day.'" value="'.$room[0][$i]['d'.$day].'" />';
					$output .= '<input type="hidden" name="old_aval_'.$room[0][$i]['id'].'_'.$day.'" value="'.$room[0][$i]['d'.$day].'" />';
				}else{
					$output .= '<td class="'.$td_class.'"><label class="l_day">'.$day.'</label><br><input class="day_a '.$day_color.'" maxlength="3" name="aval_'.$room[0][$i]['id'].'_'.$day.'" id="aval_'.$room[0][$i]['id'].'_'.$day.'" value="'.$room[0][$i]['d'.$day].'" />';
				}
                $output .= '</td>';
			}
			// fill empty cells at the end of the month line
			while($day <= $max_days){
				$output .= '<td></td>';
				$day++;
			}
			$output .= '</tr>';
			if($ids_list != '') $ids_list .= ','.$room[0][$i]['id'];
			else $ids_list = $room[0][$i]['id'];
		}

		$output .= '<tr><td colspan="39">&nbsp;</td></tr>';
		$output .= '<tr><td align="'.Application::Get('defined_right').'" colspan="39"><input type="button" class="form_button" style="width:130px" onclick="javascript:submitAvailabilityForm(\'update\')" value="'._BUTTON_SAVE_CHANGES.'"></td></tr>';
		$output .= '<tr><td colspan="39"><b>'._LEGEND.':</b> </td></tr>';
		$output .= '<tr><td colspan="39" nowrap="nowrap" height="5px"></td></tr>';
		$output .= '<tr><td colspan="39"><div class="dc_all" style="width:16px;height:15px;float:'.Application::Get('defined_left').';margin:1px;"></div> &nbsp;- '._ALL_AVAILABLE.'</td></tr>';
		$output .= '<tr><td colspan="39"><div class="dc_part" style="width:16px;height:15px;float:'.Application::Get('defined_left').';margin:1px;"></div> &nbsp;- '._PARTIALLY_AVAILABLE.'</td></tr>';
		$output .= '<tr><td colspan="39"><div class="dc_none" style="width:16px;height:15px;float:'.Application::Get('defined_left').';margin:1px;"></div> &nbsp;- '._NO_AVAILABLE.'</td></tr>';
		$output .= '</table>';
		$output .= draw_hidden_field('ids_list', $ids_list, false);
		$output .= '</form>';
		$output .= '</div>';

		echo $output;
	}

	/**
	 * Draws room prices form
	 * @param $rid
	 */
	public function DrawRoomPricesForm($rid)
	{
		global $objSettings, $objLogin;;

        $nl = "\n";
		$default_price = '0';
		$output = '';

		$default_currency_info = Currencies::GetDefaultCurrencyInfo();
		if($default_currency_info['symbol_placement'] == 'before'){
			$currency_l_sign = $default_currency_info['symbol'];
			$currency_r_sign = '';
		}else{
			$currency_l_sign = '';
			$currency_r_sign = $default_currency_info['symbol'];
		}
		$decimals = $default_currency_info['decimals'];

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$calendar_date_format = '%m-%d-%Y';
			$field_date_format = 'M d, Y';
		}else{
			$calendar_date_format = '%d-%m-%Y';
			$field_date_format = 'd M, Y';
		}

		$sql = 'SELECT *
				FROM '.TABLE_ROOMS.'
				WHERE id = '.(int)$rid.'
				'.(!empty($this->hotelsList) ? ' AND '.TABLE_ROOMS.'.hotel_id IN ('.$this->hotelsList.')' : '');
		$room = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($room[1] > 0){
			$default_price = number_format((float)$room[0]['default_price'], $decimals, '.', '');
			$max_adults = $room[0]['max_adults'];
			$max_children = $room[0]['max_children'];
			$max_extra_beds = $room[0]['max_extra_beds'];
			$extra_bed_charge = $room[0]['extra_bed_charge'];
			$hotel_id = $room[0]['hotel_id'];
		}else{
			draw_important_message(_WRONG_PARAMETER_PASSED);
			return false;
		}

		$room_type 	   = isset($_REQUEST['room_type']) ? prepare_input($_REQUEST['room_type']) : '';
		$from_new 	   = isset($_POST['from_new']) ? prepare_input($_POST['from_new']) : '';
		$to_new 	   = isset($_POST['to_new']) ? prepare_input($_POST['to_new']) : '';
		$adults_new    = isset($_POST['adults_new']) ? prepare_input($_POST['adults_new']) : $max_adults;
		$children_new  = isset($_POST['children_new']) ? prepare_input($_POST['children_new']) : $max_children;
		$extra_bed_charge_new = isset($_POST['extra_bed_charge_new']) ? number_format((float)$_POST['extra_bed_charge_new'], $decimals, '.', '') : $extra_bed_charge;
		$price_new_mon = isset($_POST['price_new_mon']) ? number_format((float)$_POST['price_new_mon'], $decimals, '.', '') : $default_price;
		$price_new_tue = isset($_POST['price_new_tue']) ? number_format((float)$_POST['price_new_tue'], $decimals, '.', '') : $default_price;
		$price_new_wed = isset($_POST['price_new_wed']) ? number_format((float)$_POST['price_new_wed'], $decimals, '.', '') : $default_price;
		$price_new_thu = isset($_POST['price_new_thu']) ? number_format((float)$_POST['price_new_thu'], $decimals, '.', '') : $default_price;
		$price_new_fri = isset($_POST['price_new_fri']) ? number_format((float)$_POST['price_new_fri'], $decimals, '.', '') : $default_price;
		$price_new_sat = isset($_POST['price_new_sat']) ? number_format((float)$_POST['price_new_sat'], $decimals, '.', '') : $default_price;
		$price_new_sun = isset($_POST['price_new_sun']) ? number_format((float)$_POST['price_new_sun'], $decimals, '.', '') : $default_price;
		$ids_list 	   = '';
		$width         = '60px';
		$text_align    = (Application::Get('defined_alignment') == 'left') ? 'right' : 'left';
		$allow_default_periods = ModulesSettings::Get('rooms', 'allow_default_periods');

		$output .= '<link type="text/css" rel="stylesheet" href="modules/jscalendar/skins/aqua/theme.css" />'.$nl;
		$output .= '<script type="text/javascript">
			function submitPriceForm(task, rpid){
				if(task == "refresh" || task == "add_default_periods"){
					document.getElementById("task").value = task;
					document.getElementById("frmRoomPrices").submit();				
				}
				else if(task == "delete"){
					if(confirm("'._DELETE_WARNING_COMMON.'")){
						document.getElementById("task").value = task;
						document.getElementById("rpid").value = rpid;
						document.getElementById("frmRoomPrices").submit();
					}				
				}
				else if(task == "update" || task == "add_new"){
					document.getElementById("task").value = task;
					document.getElementById("frmRoomPrices").submit();
				}				
			}
			function copy_room_prices(room_name_id){
				var frm = jQuery("#frmRoomPrices");	
				var default_price = jQuery("#frmRoomPrices input[name="+room_name_id+"_mon]").val();
				if(frm){
					jQuery("#frmRoomPrices input[name="+room_name_id+"_tue]").val(default_price);
					jQuery("#frmRoomPrices input[name="+room_name_id+"_wed]").val(default_price); 
					jQuery("#frmRoomPrices input[name="+room_name_id+"_thu]").val(default_price); 
					jQuery("#frmRoomPrices input[name="+room_name_id+"_fri]").val(default_price);
					jQuery("#frmRoomPrices input[name="+room_name_id+"_sat]").val(default_price);
					jQuery("#frmRoomPrices input[name="+room_name_id+"_sun]").val(default_price);
				}
			}
		</script>'.$nl;
		$output .= '<script type="text/javascript" src="modules/jscalendar/calendar.js"></script>'.$nl;
		$output .= '<script type="text/javascript" src="modules/jscalendar/lang/calendar-'.((file_exists('modules/jscalendar/lang/calendar-'.Application::Get('lang').'.js')) ? Application::Get('lang') : 'en').'.js"></script>'.$nl;
		$output .= '<script type="text/javascript" src="modules/jscalendar/calendar-setup.js"></script>'.$nl;

		$output .= '<div class="table-responsive">';
		$output .= '<form action="index.php?admin=mod_room_prices" id="frmRoomPrices" method="post">';
		$output .= draw_hidden_field('task', 'update', false, 'task');
		$output .= draw_hidden_field('rid', $rid, false, 'rid');
		$output .= draw_hidden_field('rpid', '', false, 'rpid');
        $output .= draw_hidden_field('room_type', $room_type, false, 'room_type');
		$output .= draw_token_field(false);

		$output .= '<table width="99%" border="0" cellpadding="1" cellspacing="0">';
		$output .= '<tr style="text-align:center;font-weight:bold;">';
		$output .= '  <td></td>';
		$output .= '  <td colspan="4" align="left">';
		$output .= '  <input type="button" class="form_button" style="width:80px" onclick="javascript:submitPriceForm(\'refresh\')" value="'._REFRESH.'">';
		if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner') && $allow_default_periods){
			$output .= ' &nbsp;<input type="button" class="form_button" style="width:150px" onclick="javascript:submitPriceForm(\'add_default_periods\')" value="'._ADD_DEFAULT_PERIODS.'">';
			$output .= ' &nbsp;<a href="index.php?admin=hotel_default_periods&hid='.$hotel_id.'">[ '._SET_PERIODS.' ]</a>';
		}
		$output .= '  </td>';
		$output .= '  <td colspan="9"></td>';
		$output .= '</tr>';
		$output .= '<tr><td colspan="14" nowrap height="5px"></td></tr>';
		$output .= '<tr style="text-align:center;font-weight:bold;">';
		$output .= '  <td width="5px"></td>';
		$output .= '  <td colspan="3"></td>';
		$output .= '  <td width="">'._ADULTS.' '._CHILDREN.' '._EXTRA_BED.'</td>';
		$output .= '  <td width="10px"></td>';
		$output .= '  <td>'._MON.'</td>';
		$output .= '  <td>'._TUE.'</td>';
		$output .= '  <td>'._WED.'</td>';
		$output .= '  <td>'._THU.'</td>';
		$output .= '  <td>'._FRI.'</td>';
		$output .= '  <td style="background-color:#ffcc33;">'._SAT.'</td>';
		$output .= '  <td style="background-color:#ffcc33;">'._SUN.'</td>';
		$output .= '  <td></td>';
		$output .= '</tr>';

		$sql = 'SELECT *
				FROM '.TABLE_ROOMS_PRICES.'
				WHERE room_id = '.(int)$rid.'
				ORDER BY is_default DESC, date_from ASC';
		$room = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		for($i=0; $i < $room[1]; $i++){
			$output .= '<tr align="center" style="'.(($i%2==0) ? '' : 'background-color:#f1f2f3;').'">';

			$output .= '<td></td>';
			if($i == 0){
				$output .= '<td align="left" nowrap="nowrap" colspan="3"><b>'._STANDARD_PRICE.'</b></td>';
				$output .= '<td>';
				$output .= '  &nbsp;'.draw_numbers_select_field('adults_'.$room[0][$i]['id'], $max_adults, 1, $max_adults, 1, 'mgrid_select', 'disabled', false);
				$output .= '  &nbsp;'.draw_numbers_select_field('children_'.$room[0][$i]['id'], $max_children, 0, $max_children, 1, 'mgrid_select', 'disabled', false);
				$output .= '  &nbsp;'.$currency_l_sign.' <input type="text" maxlength="7" '.($max_extra_beds == 0 ? 'readonly="readonly" class="readonly mgrid_text"' : 'class="mgrid_text"').' name="extra_bed_charge_'.$room[0][$i]['id'].'" value="'.(isset($_POST['extra_bed_charge_'.$room[0][$i]['id']]) ? number_format((float)$_POST['extra_bed_charge_'.$room[0][$i]['id']], 2, '.', '') : $room[0][$i]['extra_bed_charge']).'" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'"> '.$currency_r_sign;
				$output .= '</td>';
			}else{
				$output .= '<td align="left" nowrap="nowrap"><input type="text" readonly="readonly" class="mgrid_text" name="date_from_'.$room[0][$i]['id'].'" style="width:100px;border:0px;'.(($i%2==0) ? '' : 'background-color:#f1f2f3;').'" value="'.format_datetime($room[0][$i]['date_from'], $field_date_format).'" /></td>';
				$output .= '<td align="left" nowrap="nowrap" width="20px">-</td>';
				$output .= '<td align="left" nowrap="nowrap"><input type="text" readonly="readonly" class="mgrid_text" name="date_to_'.$room[0][$i]['id'].'" style="width:100px;border:0px;'.(($i%2==0) ? '' : 'background-color:#f1f2f3;').'" value="'.format_datetime($room[0][$i]['date_to'], $field_date_format).'" /></td>';
				$output .= '<td>';
				$output .= '  &nbsp;'.draw_numbers_select_field('adults_'.$room[0][$i]['id'], (isset($_POST['adults_'.$room[0][$i]['id']]) ? $_POST['adults_'.$room[0][$i]['id']] : $room[0][$i]['adults']), 1, $max_adults, 1, 'mgrid_select', 'disabled', false);
				$output .= '  &nbsp;'.draw_numbers_select_field('children_'.$room[0][$i]['id'], (isset($_POST['children_'.$room[0][$i]['id']]) ? $_POST['children_'.$room[0][$i]['id']] : $room[0][$i]['children']), 0, $max_children, 1, 'mgrid_select', 'disabled', false);
				$output .= '  &nbsp;'.$currency_l_sign.' <input type="text" maxlength="7" '.($max_extra_beds == 0 ? 'readonly="readonly" class="readonly"' : 'class="mgrid_text"').' name="extra_bed_charge_'.$room[0][$i]['id'].'" value="'.(isset($_POST['extra_bed_charge_'.$room[0][$i]['id']]) ? number_format((float)$_POST['extra_bed_charge_'.$room[0][$i]['id']], 2, '.', '') : $room[0][$i]['extra_bed_charge']).'" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'"> '.$currency_r_sign;
				$output .= '</td>';
			}
			$output .= '<td></td>';
			$output .= '<td nowrap="nowrap">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_'.$room[0][$i]['id'].'_mon" value="'.number_format((float)(isset($_POST['price_'.$room[0][$i]['id'].'_mon']) ? $_POST['price_'.$room[0][$i]['id'].'_mon'] : $room[0][$i]['mon']), $decimals, '.', '').'" maxlength="7" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> <a href="javascript:void(\'copy-price\')" onclick="copy_room_prices(\'price_'.$room[0][$i]['id'].'\')" style="font-size:15px;" title="'._COPY_TO_OTHERS.'">&raquo;</a> '.$currency_r_sign.'</td>';
			$output .= '<td nowrap="nowrap">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_'.$room[0][$i]['id'].'_tue" value="'.number_format((float)(isset($_POST['price_'.$room[0][$i]['id'].'_tue']) ? $_POST['price_'.$room[0][$i]['id'].'_tue'] : $room[0][$i]['tue']), $decimals, '.', '').'" maxlength="7" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
			$output .= '<td nowrap="nowrap">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_'.$room[0][$i]['id'].'_wed" value="'.number_format((float)(isset($_POST['price_'.$room[0][$i]['id'].'_wed']) ? $_POST['price_'.$room[0][$i]['id'].'_wed'] : $room[0][$i]['wed']), $decimals, '.', '').'" maxlength="7" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
			$output .= '<td nowrap="nowrap">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_'.$room[0][$i]['id'].'_thu" value="'.number_format((float)(isset($_POST['price_'.$room[0][$i]['id'].'_thu']) ? $_POST['price_'.$room[0][$i]['id'].'_thu'] : $room[0][$i]['thu']), $decimals, '.', '').'" maxlength="7" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
			$output .= '<td nowrap="nowrap">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_'.$room[0][$i]['id'].'_fri" value="'.number_format((float)(isset($_POST['price_'.$room[0][$i]['id'].'_fri']) ? $_POST['price_'.$room[0][$i]['id'].'_fri'] : $room[0][$i]['fri']), $decimals, '.', '').'" maxlength="7" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
			$output .= '<td style="background-color:#ffcc33;">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_'.$room[0][$i]['id'].'_sat" value="'.number_format((float)(isset($_POST['price_'.$room[0][$i]['id'].'_sat']) ? $_POST['price_'.$room[0][$i]['id'].'_sat'] : $room[0][$i]['sat']), $decimals, '.', '').'" maxlength="7" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
			$output .= '<td style="background-color:#ffcc33;">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_'.$room[0][$i]['id'].'_sun" value="'.number_format((float)(isset($_POST['price_'.$room[0][$i]['id'].'_sun']) ? $_POST['price_'.$room[0][$i]['id'].'_sun'] : $room[0][$i]['sun']), $decimals, '.', '').'" maxlength="7" style="padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
			$output .= '<td width="30px" align="center">'.(($i > 0) ? '<img src="images/delete.gif" alt="'._DELETE_WORD.'" title="'._DELETE_WORD.'" style="cursor:pointer;" onclick="javascript:submitPriceForm(\'delete\',\''.$room[0][$i]['id'].'\')" />' : '').'</td>';
			$output .= '</tr>';
			if($ids_list != '') $ids_list .= ','.$room[0][$i]['id'];
			else $ids_list = $room[0][$i]['id'];
		}
		$output .= '<tr><td colspan="11"></td><td colspan="2" style="height:5px;background-color:#ffcc33;"></td><td></td></tr>';
		$output .= '<tr><td colspan="14">&nbsp;</td></tr>';
		$output .= '<tr>';
		$output .= '  <td colspan="9"></td>';
		$output .= '  <td align="center" colspan="2"></td>';
		$output .= '  <td align="center" colspan="2"><input type="button" class="form_button" style="width:130px" onclick="javascript:submitPriceForm(\'update\')" value="'._BUTTON_SAVE_CHANGES.'"></td>';
		$output .= '  <td></td>';
		$output .= '</tr>';
		$output .= '<tr><td colspan="14">&nbsp;</td></tr>';
		$output .= '<tr align="center">';
		$output .= '  <td></td>';
		$output .= '  <td colspan="3" align="right">'._FROM.': <input type="text" class="mgrid_text" id="from_new" name="from_new" style="color:#808080;width:90px" readonly="readonly" value="'.$from_new.'" /><img id="from_new_cal" src="images/cal.gif" alt="calendar" title="'._SET_DATE.'" style="margin-left:5px;margin-right:5px;cursor:pointer;" /><br />'._TO.': <input type="text" class="mgrid_text" id="to_new" name="to_new" style="color:#808080;width:90px" readonly="readonly" value="'.$to_new.'" /><img id="to_new_cal" src="images/cal.gif" alt="calendar" title="'._SET_DATE.'" style="margin-left:5px;margin-right:5px;cursor:pointer;" /></td>';
		$output .= '  <td>';
		$output .= '  &nbsp;'.draw_numbers_select_field('adults_new', $adults_new, 1, $max_adults, 1, 'mgrid_select', '', false);
		$output .= '  &nbsp;'.draw_numbers_select_field('children_new', $children_new, 0, $max_children, 1, 'mgrid_select', '', false);
		$output .= '  &nbsp;'.$currency_l_sign.' <input type="text" class="mgrid_text" maxlength="7" '.($max_extra_beds == 0 ? 'readonly="readonly" class="readonly"' : '').' name="extra_bed_charge_new" value="'.$extra_bed_charge_new.'" style="color:#808080;padding:0 2px;text-align:'.$text_align.';width:'.$width.'"> '.$currency_r_sign;
		$output .= '  </td>';
		$output .= '  <td></td>';
		$output .= '  <td>'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_new_mon" value="'.$price_new_mon.'" maxlength="7" style="color:#808080;padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> <a href="javascript:void(\'copy-price\')" onclick="copy_room_prices(\'price_new\')" style="font-size:15px;" title="'._COPY_TO_OTHERS.'">&raquo;</a> '.$currency_r_sign.'</td>';
		$output .= '  <td>'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_new_tue" value="'.$price_new_tue.'" maxlength="7" style="color:#808080;padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
		$output .= '  <td>'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_new_wed" value="'.$price_new_wed.'" maxlength="7" style="color:#808080;padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
		$output .= '  <td>'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_new_thu" value="'.$price_new_thu.'" maxlength="7" style="color:#808080;padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
		$output .= '  <td>'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_new_fri" value="'.$price_new_fri.'" maxlength="7" style="color:#808080;padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
		$output .= '  <td style="background-color:#ffcc33;">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_new_sat" value="'.$price_new_sat.'" maxlength="7" style="color:#808080;padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
		$output .= '  <td style="background-color:#ffcc33;">'.$currency_l_sign.' <input type="text" class="mgrid_text" name="price_new_sun" value="'.$price_new_sun.'" maxlength="7" style="color:#808080;padding:0 2px;text-align:'.$text_align.';width:'.$width.'" /> '.$currency_r_sign.'</td>';
		$output .= '  <td></td>';
		$output .= '</tr>';

		$output .= '<tr><td colspan="14">&nbsp;</td></tr>';
		$output .= '<tr>';
		$output .= '  <td colspan="11"></td>';
		$output .= '  <td align="center" colspan="2"><input type="button" class="form_button" style="width:130px" onclick="javascript:submitPriceForm(\'add_new\')" value="'._ADD_NEW.'"></td>';
		$output .= '  <td></td>';
		$output .= '</tr>';
		$output .= '</table>';
		$output .= draw_hidden_field('ids_list', $ids_list, false);
		$output .= '</form>';
		$output .= '</div>';

		$output .= '<script type="text/javascript"> 
		Calendar.setup({firstDay : '.($objSettings->GetParameter('week_start_day')-1).', inputField : "from_new", ifFormat : "'.$calendar_date_format.'", showsTime : false, button : "from_new_cal"});
		Calendar.setup({firstDay : '.($objSettings->GetParameter('week_start_day')-1).', inputField : "to_new", ifFormat : "'.$calendar_date_format.'", showsTime : false, button : "to_new_cal"});
		</script>';

		echo $output;
	}

	/**
	 * Returns a table with prices for certain room
	 * @param $rid
	 * @param $draw_swipe
	 */
	public static function GetRoomPricesTable($rid, $draw_swipe = true)
	{
		global $objSettings, $objLogin;

		$currency_rate = ($objLogin->IsLoggedInAsAdmin()) ? '1' : Application::Get('currency_rate');
        if(empty($currency_rate)){
            $currency_info = Currencies::GetDefaultCurrencyInfo();
            $currency_rate = $currency_info['rate'];
        }

		$currency_format = get_currency_format();
		$show_default_prices = ModulesSettings::Get('rooms', 'show_default_prices');

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$calendar_date_format = '%m-%d-%Y';
			$field_date_format = 'M d, Y';
		}else{
			$calendar_date_format = '%d-%m-%Y';
			$field_date_format = 'd M, Y';
		}

		$sql = 'SELECT * FROM '.TABLE_ROOMS.' WHERE id = '.(int)$rid;
		$room = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($room[1] > 0){
			$default_price = $room[0]['default_price'];
		}else{
			$default_price = '0';
		}

		$output = '';

		if($draw_swipe){
			$output .= '<div class="swipe-table swipe-room-prices table-responsive">';
			$output .= draw_swipe_icon();
		}

		$output .= '<table class="room_prices">';
		$output .= '<tr class="header">';
		$output .= '  <th colspan="3">&nbsp;</th>';
		$output .= '  <th width="10px">&nbsp;'._ADULTS.'&nbsp;</th>';
		$output .= '  <th width="10px">&nbsp;'._CHILDREN.'&nbsp;</th>';
		$output .= '  <th width="10px">&nbsp;'._EXTRA_BED.'&nbsp;</th>';
		$output .= '  <th>'._MON.'</th>';
		$output .= '  <th>'._TUE.'</th>';
		$output .= '  <th>'._WED.'</th>';
		$output .= '  <th>'._THU.'</th>';
		$output .= '  <th>'._FRI.'</th>';
		$output .= '  <th>'._SAT.'</th>';
		$output .= '  <th>'._SUN.'</th>';
		$output .= '</tr>';

		$sql = 'SELECT * FROM '.TABLE_ROOMS_PRICES.'
				WHERE
					room_id = '.(int)$rid.' AND
					(
						is_default = 1 OR 
						(is_default = 0 AND date_from >= \''.date('Y').'-01-01\') OR
						(is_default = 0 AND date_to >= \''.date('Y').'-01-01\')
					)
				ORDER BY is_default DESC, date_from ASC';
		$room = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		$output .= '<tr><td colspan="15" nowrap="nowrap" height="5px"></td></tr>';
		for($i=0; $i < $room[1]; $i++){

			if($show_default_prices != 'yes' && $room[0][$i]['is_default'] == 1 && $room[1] > 1) continue;

			$output .= '<tr align="'.Application::Get('defined_right').'">';
			//$output .= '  <td></td>';
			if($i == 0 && $room[0][$i]['is_default'] == 1){
				$output .= '  <td align="left" nowrap="nowrap" colspan="3">&nbsp;<b>'._STANDARD_PRICE.'</b></td>';
			}else{
				$output .= '  <td align="left" nowrap="nowrap">&nbsp;'.format_datetime($room[0][$i]['date_from'], $field_date_format).'</td>';
				$output .= '  <td align="left" nowrap="nowrap" width="20px">-</td>';
				$output .= '  <td align="left" nowrap="nowrap">'.format_datetime($room[0][$i]['date_to'], $field_date_format).'</td>';
			}
			$curr_rate = !$objLogin->IsLoggedInAsAdmin() ? $currency_rate : 1;

			$output .= '  <td align="center">'.$room[0][$i]['adults'].'</td>';
			$output .= '  <td align="center">'.$room[0][$i]['children'].'</td>';
			$output .= '  <td><span>'.Currencies::PriceFormat($room[0][$i]['extra_bed_charge'] * $curr_rate, '', '', $currency_format).'</span></td>';
			//$output .= '  <td></td>';
			$output .= '  <td><span>'.Currencies::PriceFormat($room[0][$i]['mon'] * $curr_rate, '', '', $currency_format).'</span></td>';
			$output .= '  <td><span>'.Currencies::PriceFormat($room[0][$i]['tue'] * $curr_rate, '', '', $currency_format).'</span></td>';
			$output .= '  <td><span>'.Currencies::PriceFormat($room[0][$i]['wed'] * $curr_rate, '', '', $currency_format).'</span></td>';
			$output .= '  <td><span>'.Currencies::PriceFormat($room[0][$i]['thu'] * $curr_rate, '', '', $currency_format).'</span></td>';
			$output .= '  <td><span>'.Currencies::PriceFormat($room[0][$i]['fri'] * $curr_rate, '', '', $currency_format).'</span></td>';
			$output .= '  <td><span>'.Currencies::PriceFormat($room[0][$i]['sat'] * $curr_rate, '', '', $currency_format).'</span></td>';
			$output .= '  <td><span>'.Currencies::PriceFormat($room[0][$i]['sun'] * $curr_rate, '', '', $currency_format).'</span>&nbsp;</td>';
			$output .= '</tr>';
		}
		$output .= '<tr><td colspan="15" nowrap="nowrap" height="5px"></td></tr>';
		$output .= '</table>';
		if($draw_swipe){
			$output .= '</div>';
		}

		return $output;
	}

    /**
	 * Deletes room availability
	 * @param $rid
	 */
	public function DeleteRoomAvailability($rpid)
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'DELETE FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE id = '.(int)$rpid;
		if(!database_void_query($sql)){
			$this->error = _TRY_LATER;
			return false;
		}
		return true;
	}

    /**
	 * Deletes room availability
	 * @param int $rid
     * @return bool
	 */
	public function SwitchYearsRoomAvailability($rid = 0)
	{
        // Block operation in demo mode
        if(strtolower(SITE_MODE) == 'demo'){
            $this->error = _OPERATION_BLOCKED;
            return false;
        }

        $success              = false;
        $success_current_year = false;
        $success_next_year    = false;
        $current_year         = array();
        $next_year            = array();

        $room_info            = $this->GetInfoByID($rid);

        if(!empty($room_info)){
            $sql = 'SELECT id, room_id, y FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE room_id = '.(int)$rid.' ORDER BY id ASC';
            $rooms_availabilities = database_query($sql, DATA_ONLY, ALL_ROWS);

            if(!empty($rooms_availabilities)){
                foreach($rooms_availabilities as $room_availability){
                    if($room_availability['y'] == 0){
                        $current_year[] = $room_availability['id'];
                    }elseif($room_availability['y'] == 1){
                        $next_year[] = $room_availability['id'];
                    }
                }
            }
            // switch years in the room availability
            if(!empty($current_year) && count($current_year) == 12 && !empty($next_year) && count($next_year) == 12){
                // Update current year in the room availability
                $sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET y = 1  WHERE id IN ('.implode(',',$current_year).') AND room_id = '.(int)$rid;
                $success_current_year = database_void_query($sql);

                // Update next year in the room availability
                $sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET y = 0  WHERE id IN ('.implode(',',$next_year).') AND room_id = '.(int)$rid;
                $success_next_year = database_void_query($sql);

                if($success_current_year && $success_next_year){
                    $success = true;
                }
            }else{
                $success = false;
            }
        }

        if($success){
            return true;
        }else{
            $this->error = _TRY_LATER;
            return false;
        }
	}

    /**
	 * Deletes room prices
	 * @param $rpid
	 */
	public function DeleteRoomPrices($rpid)
	{
		global $objSession;

		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		// Save deleted period of time
		if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){
			$sql = 'SELECT * FROM '.TABLE_ROOMS_PRICES.' WHERE id = '.(int)$rpid;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$data = array('date_from' => $result[0]['date_from'], 'date_to' => $result[0]['date_to']);
				$objSession->SetMessage('room_prices_deleted_period', $data);
			}
		}

		$sql = 'DELETE FROM '.TABLE_ROOMS_PRICES.' WHERE id = '.(int)$rpid;
		if(!database_void_query($sql)){
			$this->error = _TRY_LATER;
			return false;
		}
		return true;
	}

    /**
	 * Adds room availability
	 * @param $rid
	 */
	public function AddRoomAvailability($rid)
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		global $objSettings;

		$task 	  = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
		$from_new = isset($_POST['from_new']) ? prepare_input($_POST['from_new']) : '';
		$to_new   = isset($_POST['to_new']) ? prepare_input( $_POST['to_new']) : '';
		$aval_mon = isset($_POST['aval_new_mon']) ? '1' : '0';
		$aval_tue = isset($_POST['aval_new_tue']) ? '1' : '0';
		$aval_wed = isset($_POST['aval_new_wed']) ? '1' : '0';
		$aval_thu = isset($_POST['aval_new_thu']) ? '1' : '0';
		$aval_fri = isset($_POST['aval_new_fri']) ? '1' : '0';
		$aval_sat = isset($_POST['aval_new_sat']) ? '1' : '0';
		$aval_sun = isset($_POST['aval_new_sun']) ? '1' : '0';


		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$from_new = substr($from_new, 6, 4).'-'.substr($from_new, 0, 2).'-'.substr($from_new, 3, 2);
			$to_new = substr($to_new, 6, 4).'-'.substr($to_new, 0, 2).'-'.substr($to_new, 3, 2);
		}else{
			// dd/mm/yyyy
			$from_new = substr($from_new, 6, 4).'-'.substr($from_new, 3, 2).'-'.substr($from_new, 0, 2);
			$to_new = substr($to_new, 6, 4).'-'.substr($to_new, 3, 2).'-'.substr($to_new, 0, 2);
		}

		if($from_new == '--' || $to_new == '--'){
			$this->error = _DATE_EMPTY_ALERT;
			return false;
		}elseif($from_new > $to_new){
			$this->error = _FROM_TO_DATE_ALERT;
			return false;
		}else{
			$sql = 'SELECT * FROM '.TABLE_ROOMS_AVAILABILITIES.'
					WHERE
						room_id = '.(int)$rid.' AND
						is_default = 0 AND 
						(((\''.$from_new.'\' >= date_from) AND (\''.$from_new.'\' <= date_to)) OR
						((\''.$to_new.'\' >= date_from) AND (\''.$to_new.'\' <= date_to))) ';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$this->error = _TIME_PERIOD_OVERLAPPING_ALERT;
				return false;
			}
		}

		if($from_new != '' && $to_new != ''){
			$sql = 'INSERT INTO '.TABLE_ROOMS_AVAILABILITIES.' (id, room_id, date_from, date_to, mon, tue, wed, thu, fri, sat, sun, is_default)
					VALUES (NULL, '.(int)$rid.', \''.$from_new.'\', \''.$to_new.'\', '.$aval_mon.', '.$aval_tue.', '.$aval_wed.', '.$aval_thu.', '.$aval_fri.', '.$aval_sat.', '.$aval_sun.', 0)';
			if(database_void_query($sql)){
				return true;
			}else{
				$this->error = _TRY_LATER;
				return false;
			}
		}
	}

    /**
	 * Adds room prices
	 * @param $rid
	 */
	public function AddRoomPrices($rid)
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		global $objSettings;

		$task 	       = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
		$from_new 	   = isset($_POST['from_new']) ? prepare_input($_POST['from_new']) : '';
		$to_new 	   = isset($_POST['to_new']) ? prepare_input($_POST['to_new']) : '';
		$adults_new    = isset($_POST['adults_new']) ? prepare_input($_POST['adults_new']) : '1';
		$children_new  = isset($_POST['children_new']) ? prepare_input($_POST['children_new']) : '0';
		$extra_bed_charge_new = isset($_POST['extra_bed_charge_new']) ? prepare_input($_POST['extra_bed_charge_new']) : '0';
		$price_new_mon = isset($_POST['price_new_mon']) ? prepare_input($_POST['price_new_mon']) : '';
		$price_new_tue = isset($_POST['price_new_tue']) ? prepare_input($_POST['price_new_tue']) : '';
		$price_new_wed = isset($_POST['price_new_wed']) ? prepare_input($_POST['price_new_wed']) : '';
		$price_new_thu = isset($_POST['price_new_thu']) ? prepare_input($_POST['price_new_thu']) : '';
		$price_new_fri = isset($_POST['price_new_fri']) ? prepare_input($_POST['price_new_fri']) : '';
		$price_new_sat = isset($_POST['price_new_sat']) ? prepare_input($_POST['price_new_sat']) : '';
		$price_new_sun = isset($_POST['price_new_sun']) ? prepare_input($_POST['price_new_sun']) : '';

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$from_new = substr($from_new, 6, 4).'-'.substr($from_new, 0, 2).'-'.substr($from_new, 3, 2);
			$to_new = substr($to_new, 6, 4).'-'.substr($to_new, 0, 2).'-'.substr($to_new, 3, 2);
		}else{
			// dd/mm/yyyy
			$from_new = substr($from_new, 6, 4).'-'.substr($from_new, 3, 2).'-'.substr($from_new, 0, 2);
			$to_new = substr($to_new, 6, 4).'-'.substr($to_new, 3, 2).'-'.substr($to_new, 0, 2);
		}

		if($from_new == '--' || $to_new == '--'){
			$this->error = _DATE_EMPTY_ALERT;
			return false;
		}elseif($from_new > $to_new){
			$this->error = _FROM_TO_DATE_ALERT;
			return false;
		}elseif(!$this->IsFloat($extra_bed_charge_new) || $extra_bed_charge_new < 0){
			$this->error = str_replace('_FIELD_', '<b>'._EXTRA_BED_CHARGE.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			return false;
		}elseif($price_new_mon == '' || $price_new_tue == '' || $price_new_wed == '' || $price_new_thu == '' || $price_new_fri == '' || $price_new_sat == '' || $price_new_sun == ''){
			$this->error = _PRICE_EMPTY_ALERT;
			return false;
		}elseif(!$this->IsFloat($price_new_mon) || $price_new_mon < 0){
			$this->error = str_replace('_FIELD_', '<b>'._MON.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			return false;
		}elseif(!$this->IsFloat($price_new_tue) || $price_new_tue < 0){
			$this->error = str_replace('_FIELD_', '<b>'._TUE.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			return false;
		}elseif(!$this->IsFloat($price_new_wed) || $price_new_wed < 0){
			$this->error = str_replace('_FIELD_', '<b>'._WED.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			return false;
		}elseif(!$this->IsFloat($price_new_thu) || $price_new_thu < 0){
			$this->error = str_replace('_FIELD_', '<b>'._THU.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			return false;
		}elseif(!$this->IsFloat($price_new_fri) || $price_new_fri < 0){
			$this->error = str_replace('_FIELD_', '<b>'._FRI.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			return false;
		}elseif(!$this->IsFloat($price_new_sat) || $price_new_sat < 0){
			$this->error = str_replace('_FIELD_', '<b>'._SAT.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			return false;
		}elseif(!$this->IsFloat($price_new_sun) || $price_new_sun < 0){
			$this->error = str_replace('_FIELD_', '<b>'._SUN.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			return false;
		}else{
			$sql = 'SELECT * FROM '.TABLE_ROOMS_PRICES.'
					WHERE
						room_id = '.(int)$rid.' AND
						adults = '.(int)$adults_new.' AND
						children = '.(int)$children_new.' AND
						is_default = 0 AND 
						(((\''.$from_new.'\' >= date_from) AND (\''.$from_new.'\' <= date_to)) OR
						((\''.$to_new.'\' >= date_from) AND (\''.$to_new.'\' <= date_to))) ';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$this->error = _TIME_PERIOD_OVERLAPPING_ALERT;
				return false;
			}
		}

		if($from_new != '' && $to_new != ''){
			$sql = 'INSERT INTO '.TABLE_ROOMS_PRICES.' (id, room_id, date_from, date_to, adults, children, extra_bed_charge, mon, tue, wed, thu, fri, sat, sun, is_default)
					VALUES (NULL, '.(int)$rid.', \''.$from_new.'\', \''.$to_new.'\', \''.$adults_new.'\', \''.$children_new.'\', \''.$extra_bed_charge_new.'\', '.$price_new_mon.', '.$price_new_tue.', '.$price_new_wed.', '.$price_new_thu.', '.$price_new_fri.', '.$price_new_sat.', '.$price_new_sun.', 0)';
			if(database_void_query($sql)){
				return true;
			}else{
				$this->error = _TRY_LATER;
				return false;
			}
		}
	}

    /**
	 * Adds default periods
	 * @param $rid
	 */
	public function AddDefaultPeriods($rid)
	{
		global $objSession;
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'SELECT * FROM '.TABLE_ROOMS.' WHERE id = '.(int)$rid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $arr_new_room_price_periods = array();
			$adults = isset($result[0]['max_adults']) ? $result[0]['max_adults'] : '';
			$children = isset($result[0]['max_children']) ? $result[0]['max_children'] : '';
			$extra_bed_charge = isset($result[0]['extra_bed_charge']) ? $result[0]['extra_bed_charge'] : '';
			$price = isset($result[0]['default_price']) ? $result[0]['default_price'] : '';
			$hotel_id = isset($result[0]['hotel_id']) ? $result[0]['hotel_id'] : 0;

			$sql = 'SELECT * FROM '.TABLE_HOTEL_PERIODS.' WHERE hotel_id = '.(int)$hotel_id;
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] > 0){
				for($i=0; $i<$result[1]; $i++){
					$sql = 'SELECT room_id FROM '.TABLE_ROOMS_PRICES.'
							WHERE room_id = '.(int)$rid.' AND date_from = \''.$result[0][$i]['start_date'].'\' AND date_to = \''.$result[0][$i]['finish_date'].'\'';
					$result_check = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
                    if(!$result_check[1]){
						$sql = 'INSERT INTO '.TABLE_ROOMS_PRICES.' (id, room_id, date_from, date_to, adults, children, extra_bed_charge, mon, tue, wed, thu, fri, sat, sun, is_default)
								VALUES (NULL, '.(int)$rid.', \''.$result[0][$i]['start_date'].'\', \''.$result[0][$i]['finish_date'].'\', \''.$adults.'\', \''.$children.'\', \''.$extra_bed_charge.'\', '.$price.', '.$price.', '.$price.', '.$price.', '.$price.', '.$price.', '.$price.', 0) ';
                        if(database_void_query($sql)){
                            $arr_new_room_price_periods[] = array('start_date'=>$result[0][$i]['start_date'], 'finish_date'=>$result[0][$i]['finish_date']);
                        }
					}
                }

				$objSession->SetMessage('room_prices_add_default_periods', $arr_new_room_price_periods);
				return true;
			}else{
				$this->error = str_ireplace('_HREF_', 'index.php?admin=hotel_default_periods&hid='.(int)$hotel_id, _NO_DEFAULT_PERIODS);
				return false;
			}
		}

		return false;
	}

    /**
	 * Updates room availability
	 * @param $rid
	 */
	public function UpdateRoomAvailability($rid)
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$ids_list = isset($_POST['ids_list']) ? prepare_input($_POST['ids_list']) : '';
		$ids_list_array = explode(',', $ids_list);

		$room_info = $this->GetInfoByID($rid);
		$room_count = isset($room_info['room_count']) ? $room_info['room_count'] : '0';

        if($room_count > 0){
			if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){
                $room_avail_ids = array();
                foreach($ids_list_array as $key){
                    $room_avail_ids[] = (int)$key;
                }
                $sql = 'SELECT * FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE id IN ('.implode(',',$room_avail_ids).') AND room_id = '.(int)$rid;
                $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
                if(!empty($result) && !empty($result[1])){
                    foreach($result[0] as $room_avail){
                        $key = $room_avail['id'];
                        $sql = '';
                        for($day = 1; $day <= 31; $day++){
                            $new_aval_day = isset($_POST['aval_'.$key.'_'.$day]) ? (int)$_POST['aval_'.$key.'_'.$day] : '0';
                            $old_aval_day = isset($_POST['old_aval_'.$key.'_'.$day]) ? (int)$_POST['old_aval_'.$key.'_'.$day] : '0';

                            if($new_aval_day != $old_aval_day){
                                $diff = $new_aval_day - $old_aval_day;
                                $d_n = $room_avail['d'.$day];
                                $a_n = $room_avail['a'.$day];
                                $save_day = $d_n + $diff;
                                $save_close = $a_n - $diff;
                                if($save_day > $room_count){
                                    $this->error = str_replace('_FIELD_', '\'<b>Day '.$day.'</b>\'', _ROOM_VALUE_EXCEEDED);
                                    $this->error = str_replace('_MAX_', $room_count, $this->error);
                                    return false;
                                }elseif($save_day < 0){
                                    $this->error = str_replace('_FIELD_', '\'<b>Day '.$day.'</b>\'', _FIELD_MUST_BE_NUMERIC_POSITIVE);
                                    $this->error = str_replace('_MIN_', 0, $this->error);
                                    return false;
                                }elseif($save_close < 0){
                                    $this->error = str_replace('_DAY_', '\'<b>Day '.$day.'</b>\'', _FIELD_DAYS_INCORRECT_VALUE);
                                    return false;
                                }
                                if(!empty($sql)){
                                    $sql .= ', ';
                                }
                                $sql .= 'd'.$day.' = '.(int)$save_day.', a'.$day.' = '.(int)$save_close;
                            }
                        }
                        if(!empty($sql)){
                            $sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET '.$sql.' WHERE id = '.$room_avail['id'].' AND room_id = '.(int)$rid;
                            if(!database_void_query($sql)){
                                $this->error = _TRY_LATER;
                                return false;
                            }
                        }
                    }
                }
            }else{
                // update availability
                foreach($ids_list_array as $key){

                    $sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET ';
                    for($day = 1; $day <= 31; $day ++){
                        // input validation
                        $aval_day = isset($_POST['aval_'.$key.'_'.$day]) ? $_POST['aval_'.$key.'_'.$day] : '0';
                        if(!$this->IsInteger($aval_day) || $aval_day < 0){
                            $this->error = str_replace('_FIELD_', '\'<b>Day '.$day.'</b>\'', _FIELD_MUST_BE_NUMERIC_POSITIVE);
                            return false;
                        }elseif($aval_day > $room_count){
                            $this->error = str_replace('_FIELD_', '\'<b>Day '.$day.'</b>\'', _ROOM_VALUE_EXCEEDED);
                            $this->error = str_replace('_MAX_', $room_count, $this->error);
                            return false;
                        }

                        if($day > 1) $sql .= ', ';
                        $sql .= 'd'.$day.' = '.(int)$aval_day;
                    }
                    $sql .= ' WHERE id = '.(int)$key.' AND room_id = '.(int)$rid;
                    if(!database_void_query($sql)){
                        $this->error = _TRY_LATER;
                        return false;
                    }
                }
            }
        }
		return true;
	}

    /**
	 * Updates room prices
	 * @param $rid
	 */
	public function UpdateRoomPrices($rid)
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$ids_list = isset($_POST['ids_list']) ? prepare_input($_POST['ids_list']) : '';
		$ids_list_array = explode(',', $ids_list);

		// Input validation
		$arrPrices = array();
		$count = 0;
		foreach($ids_list_array as $key){

			$adults    = (isset($_POST['adults_'.$key]) ? prepare_input($_POST['adults_'.$key]) : '1');
			$children  = (isset($_POST['children_'.$key]) ? prepare_input($_POST['children_'.$key]) : '0');
			$extra_bed_charge = (isset($_POST['extra_bed_charge_'.$key]) ? prepare_input($_POST['extra_bed_charge_'.$key]) : '0');
			$price_mon = (isset($_POST['price_'.$key.'_mon']) ? prepare_input($_POST['price_'.$key.'_mon']) : '0');
			$price_tue = (isset($_POST['price_'.$key.'_tue']) ? prepare_input($_POST['price_'.$key.'_tue']) : '0');
			$price_wed = (isset($_POST['price_'.$key.'_wed']) ? prepare_input($_POST['price_'.$key.'_wed']) : '0');
			$price_thu = (isset($_POST['price_'.$key.'_thu']) ? prepare_input($_POST['price_'.$key.'_thu']) : '0');
			$price_fri = (isset($_POST['price_'.$key.'_fri']) ? prepare_input($_POST['price_'.$key.'_fri']) : '0');
			$price_sat = (isset($_POST['price_'.$key.'_sat']) ? prepare_input($_POST['price_'.$key.'_sat']) : '0');
			$price_sun = (isset($_POST['price_'.$key.'_sun']) ? prepare_input($_POST['price_'.$key.'_sun']) : '0');

			if(!$this->IsFloat($extra_bed_charge) || $extra_bed_charge < 0){
				$this->error = str_replace('_FIELD_', '<b>'._EXTRA_BED_CHARGE.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
				return false;
			}elseif(!$this->IsFloat($price_mon) || $price_mon < 0){
				$this->error = str_replace('_FIELD_', '<b>'._MON.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
				return false;
			}elseif(!$this->IsFloat($price_tue) || $price_tue < 0){
				$this->error = str_replace('_FIELD_', '<b>'._TUE.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
				return false;
			}elseif(!$this->IsFloat($price_wed) || $price_wed < 0){
				$this->error = str_replace('_FIELD_', '<b>'._WED.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
				return false;
			}elseif(!$this->IsFloat($price_thu) || $price_thu < 0){
				$this->error = str_replace('_FIELD_', '<b>'._THU.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
				return false;
			}elseif(!$this->IsFloat($price_fri) || $price_fri < 0){
				$this->error = str_replace('_FIELD_', '<b>'._FRI.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
				return false;
			}elseif(!$this->IsFloat($price_sat) || $price_sat < 0){
				$this->error = str_replace('_FIELD_', '<b>'._SAT.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
				return false;
			}elseif(!$this->IsFloat($price_sun) || $price_sun < 0){
				$this->error = str_replace('_FIELD_', '<b>'._SUN.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
				return false;
			}

			$sql = 'UPDATE '.TABLE_ROOMS_PRICES.'
					SET
						'.(($count == 0) ? 'date_from = NULL,' : '').'
						'.(($count == 0) ? 'date_to = NULL,' : '').'
						'.(isset($_POST['adults_'.$key]) ? 'adults = '.(int)$adults.',' : '').'
						'.(isset($_POST['children_'.$key]) ? 'children = '.(int)$children.',' : '').'
						extra_bed_charge = \''.$extra_bed_charge.'\',
						mon = \''.$price_mon.'\',
						tue = \''.$price_tue.'\',
						wed = \''.$price_wed.'\',
						thu = \''.$price_thu.'\',
						fri = \''.$price_fri.'\',
						sat = \''.$price_sat.'\',
						sun = \''.$price_sun.'\',
						is_default = '.(($count == 0) ? '1' : '0').'
					WHERE id = '.$key.' AND room_id = '.(int)$rid;
			if(!database_void_query($sql)){
				$this->error = _TRY_LATER;
				return false;
			}
			$count++;
		}
		return true;
	}

    /**
	 * After-Insert operation
	 */
	public function AfterInsertRecord()
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$default_price 			= isset($_POST['default_price']) ? prepare_input($_POST['default_price']) : '0';
		$room_type 			    = isset($_POST['room_type']) ? prepare_input($_POST['room_type']) : '';
		$room_count 			= isset($_POST['room_count']) ? (int)$_POST['room_count'] : '0';
		$room_short_description = isset($_POST['room_short_description']) ? prepare_input($_POST['room_short_description']) : '';
		$room_long_description  = isset($_POST['room_long_description']) ? prepare_input($_POST['room_long_description']) : '';
		$max_adults             = isset($_POST['max_adults']) ? prepare_input($_POST['max_adults']) : '';
		$max_children           = isset($_POST['max_children']) ? prepare_input($_POST['max_children']) : '';
		$extra_bed_charge       = isset($_POST['extra_bed_charge']) ? prepare_input($_POST['extra_bed_charge']) : '';
		$hotel_id               = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '0';

		// Add room prices
		// ---------------------------------------------------------------------
		$sql = 'SELECT * FROM '.TABLE_ROOMS_PRICES.' WHERE room_id = '.$this->lastInsertId;
		$room = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($room[1] > 0){
			$sql = 'UPDATE '.TABLE_ROOMS_PRICES.'
					SET
						date_from = NULL,
						date_to   = NULL,
						mon = \''.$default_price.'\',
						tue = \''.$default_price.'\',
						wed = \''.$default_price.'\',
						thu = \''.$default_price.'\',
						fri = \''.$default_price.'\',
						sat = \''.$default_price.'\',
						sun = \''.$default_price.'\',
						is_default = 1
					WHERE room_id = '.$this->lastInsertId;
			$result = database_void_query($sql);
		}else{
			$sql = 'INSERT INTO '.TABLE_ROOMS_PRICES.' (id, room_id, date_from, date_to, adults, children, extra_bed_charge, mon, tue, wed, thu, fri, sat, sun, is_default)
					VALUES (NULL, '.$this->lastInsertId.', NULL, NULL, '.(int)$max_adults.', '.(int)$max_children.', '.$extra_bed_charge.', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', 1)';
			$result = database_void_query($sql);

			// Add prices for default periods (if specified)
			$sql = 'SELECT id, hotel_id, period_description, start_date, finish_date FROM '.TABLE_HOTEL_PERIODS.' WHERE hotel_id = '.(int)$hotel_id;
			$periods = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			for($i = 0; $i < $periods[1]; $i++){
				$sql = 'INSERT INTO '.TABLE_ROOMS_PRICES.' (id, room_id, date_from, date_to, adults, children, extra_bed_charge, mon, tue, wed, thu, fri, sat, sun, is_default)
						VALUES (NULL, '.$this->lastInsertId.', \''.$periods[0][$i]['start_date'].'\', \''.$periods[0][$i]['finish_date'].'\', '.(int)$max_adults.', '.(int)$max_children.', '.$extra_bed_charge.', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', \''.$default_price.'\', 0)';
				$result = database_void_query($sql);
			}
		}

		// Add room availability
		// ---------------------------------------------------------------------
		$sql = 'SELECT * FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE room_id = '.$this->lastInsertId;
		$room = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($room[1] <= 0){
			for($y = 0; $y <= 1; $y++){ // 0 - current, 1 - next year
				$sql_temp = 'INSERT INTO '.TABLE_ROOMS_AVAILABILITIES.' (id, room_id, y, m ';
				$sql_temp_values = '';
				for($i=1; $i<=31; $i++){
					$sql_temp .= ', d'.$i;
					$sql_temp_values .= ', '.$room_count;
				}
				$sql_temp .= ')';
				$sql_temp .= 'VALUES (NULL, '.$this->lastInsertId.', '.$y.', _MONTH_'.$sql_temp_values.');';

				for($i = 1; $i <= 12; $i++){
					$sql = str_replace('_MONTH_', $i, $sql_temp);
					$result = database_void_query($sql);
				}
			}
		}

		// Languages array
		// ---------------------------------------------------------------------
		$total_languages = Languages::GetAllActive();
		foreach($total_languages[0] as $key => $val){
			$sql = 'INSERT INTO '.TABLE_ROOMS_DESCRIPTION.'(
						id, room_id, language_id, room_type, room_short_description, room_long_description
					)VALUES(
						NULL, '.$this->lastInsertId.', \''.$val['abbreviation'].'\', \''.encode_text($room_type).'\', \''.encode_text($room_short_description).'\', \''.encode_text($room_long_description).'\'
					)';
			database_void_query($sql);
		}
	}

	public function BeforeUpdateRecord()
	{
		$record_info = $this->GetInfoByID($this->curRecordId);
		$this->roomsCount = isset($record_info['room_count']) ? $record_info['room_count'] : '';
		$this->defaultPrice = isset($record_info['default_price']) ? $record_info['default_price'] : '';
		$this->extraBedCharge = isset($record_info['extra_bed_charge']) ? $record_info['extra_bed_charge'] : '';
		$this->maxAdults = isset($record_info['max_adults']) ? $record_info['max_adults'] : '';
		$this->maxChildren = isset($record_info['max_children']) ? $record_info['max_children'] : '';
		return true;
	}

	public function AfterUpdateRecord()
	{
		$new_room_count = MicroGrid::GetParameter('room_count', false);
		$default_price = MicroGrid::GetParameter('default_price', false);
        $extra_bed_charge = MicroGrid::GetParameter('extra_bed_charge', false);
		$max_adults = MicroGrid::GetParameter('max_adults', false);
		$max_children = MicroGrid::GetParameter('max_children', false);

		if($new_room_count != $this->roomsCount){
			$diff = $new_room_count - $this->roomsCount;
			$sql = 'UPDATE '.TABLE_ROOMS_AVAILABILITIES.' SET ';
			for($day = 1; $day <= 31; $day ++){
				if($day > 1) $sql .= ', ';
				if($new_room_count > $this->roomsCount) {
					$sql .= 'd'.$day.' = (CASE WHEN d'.$day.' < '.$this->roomsCount.' THEN (d'.$day.' + '.$diff.') ELSE '.$new_room_count.' END)';
				}else{
					$sql .= 'd'.$day.' = (CASE WHEN d'.$day.' = 0 THEN 0 WHEN d'.$day.' < '.$this->roomsCount.' AND d'.$day.' < '.$new_room_count.' THEN d'.$day.' ELSE '.$new_room_count.' END)';
				}
			}
			$sql .= ' WHERE room_id = '.(int)$this->curRecordId;
			database_void_query($sql);
		}

		if($max_adults != $this->maxAdults){
			$sql = 'UPDATE '.TABLE_ROOMS_PRICES.' SET adults='.(int)$max_adults;
			$sql .= ' WHERE room_id = '.(int)$this->curRecordId.' AND is_default = 1';
			database_void_query($sql);
		}

		if($max_children != $this->maxChildren){
		   $sql = 'UPDATE '.TABLE_ROOMS_PRICES.' SET children='.(int)$max_children;
		   $sql .= ' WHERE room_id = '.(int)$this->curRecordId.' AND is_default = 1';
		   database_void_query($sql);
		}

		if($default_price != $this->defaultPrice){
			$sql = 'UPDATE '.TABLE_ROOMS_PRICES.' SET mon='.$default_price.', tue='.$default_price.', wed='.$default_price.', thu='.$default_price.', fri='.$default_price.', sat='.$default_price.', sun='.$default_price;
			$sql .= ' WHERE room_id = '.(int)$this->curRecordId.' AND is_default = 1';
			database_void_query($sql);
		}

		if($extra_bed_charge != $this->extraBedCharge){
			$sql = 'UPDATE '.TABLE_ROOMS_PRICES.' SET extra_bed_charge = '.$extra_bed_charge;
			$sql .= ' WHERE room_id = '.(int)$this->curRecordId.' AND is_default = 1';
			database_void_query($sql);
		}
	}

    /**
	 * After-Delete operation
	 */
	public function AfterDeleteRecord()
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$rid = self::GetParameter('rid');

		$sql = 'DELETE FROM '.TABLE_ROOMS_PRICES.' WHERE room_id = '.(int)$rid;
		database_void_query($sql);
		$sql = 'DELETE FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE room_id = '.(int)$rid;
		database_void_query($sql);
		$sql = 'DELETE FROM '.TABLE_ROOMS_DESCRIPTION.' WHERE room_id = '.(int)$rid;
		database_void_query($sql);

		// *** Channel manager
		// -----------------------------------------------------------------------------
		if(Modules::IsModuleInstalled('channel_manager')){
			ChannelHotelRooms::DeleteRoom($rid);
		}
	}

    /**
	 * Search available rooms
	 * @param array $params
     * @param array $additional_info
     * @param bool $search_suggestion
	 */
	public function SearchFor($params = array(), $additional_info = array(), $search_suggestion = false)
	{
		global $objLogin;

		$lang                     = Application::Get('lang');
		$checkin_date             = $params['from_year'].'-'.$params['from_month'].'-'.$params['from_day'];
		$checkout_date            = $params['to_year'].'-'.$params['to_month'].'-'.$params['to_day'];
		$max_adults               = isset($params['max_adults']) ? $params['max_adults'] : '';
		$max_children             = isset($params['max_children']) ? $params['max_children'] : '';
		$room_id                  = isset($params['room_id']) ? $params['room_id'] : '';
		$hotel_sel_id             = isset($params['hotel_sel_id']) ? $params['hotel_sel_id'] : '';
		$hotel_sel_loc_id         = isset($params['hotel_sel_loc_id']) ? $params['hotel_sel_loc_id'] : '';
		$property_type_id         = isset($params['property_type_id']) ? (int)$params['property_type_id'] : '';
		$sort_by                  = isset($params['sort_by']) ? $params['sort_by'] : '';
		$nights                   = isset($params['nights']) ? $params['nights'] : '';
		$arr_serialize_facilities = isset($params['arr_serialize_facilities']) ? $params['arr_serialize_facilities'] : array();
		$currency_rate			  = Application::Get('currency_rate');
        if(empty($currency_rate)){
            $currency_info = Currencies::GetDefaultCurrencyInfo();
            $currency_rate = $currency_info['rate'];
        }

        $arr_filter_rating        = isset($params['arr_filter_rating']) ? $params['arr_filter_rating'] : array();
		$filter_start_price       = isset($params['filter_start_price']) ? ($params['filter_start_price'] / $currency_rate) : 0;
		$filter_end_price         = isset($params['filter_end_price']) ? ($params['filter_end_price'] / $currency_rate) : MAX_PRICE_FILTER;
		$filter_start_distance    = isset($params['filter_start_distance']) ? $params['filter_start_distance'] : 0;
		$filter_end_distance      = isset($params['filter_end_distance']) ? $params['filter_end_distance'] : 1000;
		$sort_rating              = isset($params['sort_rating']) ? (int)$params['sort_rating'] : null;
		$sort_price               = isset($params['sort_price']) && in_array($params['sort_price'], array('asc', 'desc')) ? $params['sort_price'] : '';
		$minimum_beds             = isset($params['minimum_beds']) ? $params['minimum_beds'] : '';
		$min_max_hotels			  = isset($params['min_max_hotels']) ? $params['min_max_hotels'] : array();

		$beds_sample_type		  = isset($additional_info['beds_sample_type']) ? $additional_info['beds_sample_type'] : 'hotel'; // hotel|room

		$order_by_clause = '';
		$hotel_where_clause = '';
		$type_sort_rating = '';

		// prepare sort by clause
		switch($sort_by){
			case 'stars-1-5':
				$order_by_clause .= 'h.stars ASC'; break;
			case 'stars-5-1':
				$order_by_clause .= 'h.stars DESC'; break;
			case 'name-a-z':
				$order_by_clause .= 'hd.name ASC'; break;
			case 'name-z-a':
				$order_by_clause .= 'hd.name DESC'; break;
			case 'distance-asc':
				$order_by_clause .= 'h.distance_center ASC'; break;
			case 'distance-desc':
				$order_by_clause .= 'h.distance_center DESC'; break;
			case 'price-l-h':
				//$order_by_clause .= 'r.default_price ASC'; break;
				$order_by_clause .= 'r.priority_order ASC';
				$sort_price = 'asc'; break;
			case 'price-h-l':
				//$order_by_clause .= 'r.default_price DESC'; break;
				$order_by_clause .= 'r.priority_order ASC';
				$sort_price = 'desc'; break;
			case 'review-asc':
				$order_by_clause .= 'r.priority_order ASC';
				$type_sort_rating = 'asc'; break;
			case 'review-desc':
				$order_by_clause .= 'r.priority_order ASC';
				$type_sort_rating = 'desc'; break;
			default:
				$order_by_clause .= 'r.priority_order ASC'; break;
		}

        if(!empty($hotel_sel_id)){
            if(is_string($hotel_sel_id)){
                $hotel_where_clause .= 'h.id = '.(int)$hotel_sel_id.' AND ';
            }elseif(is_array($hotel_sel_id)){
                $hotel_val_sel_ids = array();
                foreach($hotel_sel_id as $h_id){
                    $hotel_val_sel_ids[] = (int)$h_id;
                }
                $hotel_where_clause .= 'h.id IN ('.implode(',',$hotel_val_sel_ids).') AND ';
            }
        }
		$hotel_where_clause .= (!empty($hotel_sel_loc_id)) ? 'h.hotel_location_id = '.(int)$hotel_sel_loc_id.' AND ' : '';
		$hotel_where_clause .= (!empty($property_type_id)) ? 'h.property_type_id = '.(int)$property_type_id.' AND ' : '';
		$hotel_where_clause .= (!empty($arr_serialize_facilities)) ? '(h.facilities LIKE \'%'.implode('%\' AND h.facilities LIKE \'%', $arr_serialize_facilities).'%\') AND ' : '';
		$hotel_where_clause .= (!empty($arr_filter_rating)) ? 'h.stars IN ('.implode(',', $arr_filter_rating).') AND ' : '';
		$hotel_where_clause .= (!empty($filter_start_distance)) ? '(h.distance_center = \'\' OR h.distance_center >= '.(int)$filter_start_distance.') AND ' : '';
		$hotel_where_clause .= (!empty($filter_end_distance)) ? '(h.distance_center = \'\' OR h.distance_center <= '.(int)$filter_end_distance.') AND ' : '';

		$arr_output = array('rooms' => 0, 'hotels' => 0, 'min_price' => 0);
		$arr_hotels = array();
		$min_price_per_hotel = 0;
		$arr_min_price = array();
		$show_fully_booked_rooms = ModulesSettings::Get('booking', 'show_fully_booked_rooms');
		$allow_minimum_beds = ModulesSettings::Get('rooms', 'allow_minimum_beds');

		$packages = Packages::GetAllActiveByDate($checkin_date, $checkout_date);

		//echo '<pre>';
		//print_r($packages);
		//echo '</pre>';

    	$sql = 'SELECT
					r.*, h.facilities
			    FROM '.TABLE_ROOMS.' r
				    INNER JOIN '.TABLE_HOTELS.' h ON r.hotel_id = h.id
					'.(($sort_by == 'name-z-a' || $sort_by == 'name-a-z') ? ' INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON h.id = hd.hotel_id AND hd.language_id = \''.$lang.'\'' : '').'
				WHERE 1=1 AND 
					'.$hotel_where_clause.'
					'.(ADMIN_CAN_SEE_INACTIVE_PROPERTIES && $objLogin->IsLoggedInAs('owner','mainadmin') ? '' : 'h.is_active = 1 AND').'
					r.is_active = 1					
					'.(($room_id != '') ? ' AND r.id='.(int)$room_id : '').'
					'.(($max_adults != '') ? ' AND r.max_adults >= '.(int)$max_adults : '').'
					'.(($max_children != '') ? ' AND r.max_children >= '.(int)$max_children : '').'
				ORDER BY '.$order_by_clause;

		$rooms = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

		// Add lowest price to each hotel
		for($i=0; $i < $rooms[1]; $i++){
			$rooms[0][$i]['lowest_price_per_night'] = self::GetRoomLowestPrice($rooms[0][$i], $params);
		}

		// Prepare array with indexes by price
		$sort_array = array();
		for($i=0; $i < $rooms[1]; $i++){
            if($rooms[0][$i]['lowest_price_per_night'] >= $filter_start_price && $rooms[0][$i]['lowest_price_per_night'] <= $filter_end_price){
				$sort_array[$rooms[0][$i]['lowest_price_per_night'].'_'.$i] = $rooms[0][$i];
			}
		}

		// Sort rooms by price
        if(!empty($sort_price)){
			if($sort_price == 'asc'){
				ksort($sort_array, SORT_NUMERIC);
			}else{
				krsort($sort_array, SORT_NUMERIC);
			}
		}

		// Assign sorted array to SQL result
		$rooms[0] = array();
		$i = 0;
		foreach($sort_array as $key => $sort_array_item){
			$rooms[0][$i++] = $sort_array_item;
		}

		$rooms[1] = count($rooms[0]);

		// Loop by rooms
		for($i=0; $i < $rooms[1]; $i++){
			//echo '<br />RID:'.$rooms[0][$i]['id'].' HOTID: '.$rooms[0][$i]['hotel_id'].' = '.$rooms[0][$i]['room_count'];

			// check if package min/max stays is available for this hotel
			$hotel_data = isset($packages[$rooms[0][$i]['hotel_id']]) ? $packages[$rooms[0][$i]['hotel_id']] : NULL;
			if(!empty($hotel_data)){
				// skip this hotel
				if($nights < $hotel_data['minimum_nights'] || $nights > $hotel_data['maximum_nights']){
					continue;
				}
			}

			// maximum available rooms in hotel for one day
			$maximal_rooms = (int)$rooms[0][$i]['room_count'];
			if(!empty($hotel_data)){
				// skip this hotel
				if($maximal_rooms > $hotel_data['maximum_rooms']){
					$maximal_rooms = $hotel_data['maximum_rooms'];
				}
			}

			if(!Modules::IsModuleInstalled('channel_manager') || (ModulesSettings::Get('channel_manager', 'is_active') == 'no')){
                $max_booked_rooms = '0';
                $sql = 'SELECT
                            MAX('.TABLE_BOOKINGS_ROOMS.'.rooms) as max_booked_rooms
                        FROM '.TABLE_BOOKINGS.'
                            INNER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
                        WHERE
                            ('.TABLE_BOOKINGS.'.status = 2 OR '.TABLE_BOOKINGS.'.status = 3) AND
                            '.TABLE_BOOKINGS_ROOMS.'.room_id = '.(int)$rooms[0][$i]['id'].' AND
                            (
                                (\''.$checkin_date.'\' <= checkin AND \''.$checkout_date.'\' > checkin) 
                                OR
                                (\''.$checkin_date.'\' < checkout AND \''.$checkout_date.'\' >= checkout)
                                OR
                                (\''.$checkin_date.'\' >= checkin  AND \''.$checkout_date.'\' < checkout)
                            )';

                $rooms_booked = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

                if($rooms_booked[1] > 0){
                    $max_booked_rooms = (int)$rooms_booked[0]['max_booked_rooms'];
                }

                // this is only a simple check if there is at least one room wirh available num > booked rooms
                $available_rooms = (int)($rooms[0][$i]['room_count'] - $max_booked_rooms);
                // echo '<br> Room ID: '.$rooms[0][$i]['id'].' Max: '.$maximal_rooms.' Booked: '.$max_booked_rooms.' Av:'.$available_rooms;
            }else{
                $available_rooms = (int)$rooms[0][$i]['room_count'];
            }

			// this is advanced check that takes in account max availability for each specific day is selected period of time
			$fully_booked_rooms = true;

			if($available_rooms > 0){

                if(!$search_suggestion || ($search_suggestion && $arr_output['rooms'] == 0)){
                    $available_rooms_updated = self::CheckAvailabilityForPeriod($rooms[0][$i]['id'], $checkin_date, $checkout_date, $available_rooms);
                    if($available_rooms_updated){
                        $arr_output['rooms']++;
                        if(!isset($arr_hotels[$rooms[0][$i]['hotel_id']])){
                            $arr_hotels[$rooms[0][$i]['hotel_id']] = 1;
                        }
                        if(empty($min_price_per_hotel) || $rooms[0][$i]['lowest_price_per_night'] < $min_price_per_hotel){
                            $min_price_per_hotel = $rooms[0][$i]['lowest_price_per_night'];
                        }
                        if(!isset($arr_min_price[$rooms[0][$i]['hotel_id']]) || $rooms[0][$i]['lowest_price_per_night'] < $arr_min_price[$rooms[0][$i]['hotel_id']]){
                            $arr_min_price[$rooms[0][$i]['hotel_id']] = $rooms[0][$i]['lowest_price_per_night'];
                        }

                        if ($search_suggestion) {
                            $check_hotel_owner_relocation = self::CheckHotelOwnerRelocation($rooms[0][$i]['hotel_id'], $this->relocationType);
                            if (is_array($check_hotel_owner_relocation) && (($check_hotel_owner_relocation['hotel_owner_find'] && $check_hotel_owner_relocation['relocation']) || !$check_hotel_owner_relocation['hotel_owner_find'])) {
                                $this->arrAvailableRooms[$rooms[0][$i]['hotel_id']][] = array('id'=>$rooms[0][$i]['id'], 'available_rooms'=>$available_rooms_updated, 'facilities'=>$rooms[0][$i]['facilities'], 'lowest_price_per_night'=>$rooms[0][$i]['lowest_price_per_night'], 'max_adults'=>$rooms[0][$i]['max_adults']);
                                $fully_booked_rooms = false;
                            }
                        } else {
                            $this->arrAvailableRooms[$rooms[0][$i]['hotel_id']][] = array('id'=>$rooms[0][$i]['id'], 'available_rooms'=>$available_rooms_updated, 'facilities'=>$rooms[0][$i]['facilities'], 'lowest_price_per_night'=>$rooms[0][$i]['lowest_price_per_night'], 'max_adults'=>$rooms[0][$i]['max_adults']);
                            $fully_booked_rooms = false;
                        }
                    }
                }
			}

			if($show_fully_booked_rooms == 'yes' && $fully_booked_rooms){
				$arr_output['rooms']++;
				if(!isset($arr_hotels[$rooms[0][$i]['hotel_id']])){
					$arr_hotels[$rooms[0][$i]['hotel_id']] = 1;
				}
				if(empty($min_price_per_hotel) || $rooms[0][$i]['lowest_price_per_night'] < $min_price_per_hotel){
					$min_price_per_hotel = $rooms[0][$i]['lowest_price_per_night'];
				}
				if(!isset($arr_min_price[$rooms[0][$i]['hotel_id']]) || $rooms[0][$i]['lowest_price_per_night'] < $arr_min_price[$rooms[0][$i]['hotel_id']]){
					$arr_min_price[$rooms[0][$i]['hotel_id']] = $rooms[0][$i]['lowest_price_per_night'];
				}
				$this->arrAvailableRooms[$rooms[0][$i]['hotel_id']][] = array('id'=>$rooms[0][$i]['id'], 'available_rooms'=>'0', 'facilities'=>$rooms[0][$i]['facilities'], 'lowest_price_per_night'=>$rooms[0][$i]['lowest_price_per_night'], 'max_adults'=>$rooms[0][$i]['max_adults']);
			}
		}

        if($allow_minimum_beds == 'yes' && $rooms[1] > 0){
			$hotel_beds = array();
			$hotel_remove = array();
			$room_adults = array();
			for($i = 0; $i < $rooms[1]; $i++){
				$room_adults[$rooms[0][$i]['id']] = $rooms[0][$i]['max_adults'];
			}
			if($beds_sample_type == 'room'){
				$new_availabile_rooms = array();
				$room_remove = array();
				foreach($this->arrAvailableRooms as $hotel_id => $arr_info){
					$new_availabile_rooms[$hotel_id] = $arr_info;
					for($i = 0, $max_count = count($arr_info); $i < $max_count; $i++){
						$id = $arr_info[$i]['id'];
						$beds = $arr_info[$i]['available_rooms'] * $room_adults[$id];
						if($beds < $minimum_beds){
							unset($new_availabile_rooms[$hotel_id][$i]);
							$arr_output['rooms']--;
							if(empty($new_availabile_rooms[$hotel_id])){
								unset($new_availabile_rooms[$hotel_id]);
								unset($arr_hotels[$hotel_id]);
								unset($arr_min_price[$hotel_id]);
							}
						}
					}
				}
				$this->arrAvailableRooms = $new_availabile_rooms;
			}else{
				foreach($this->arrAvailableRooms as $hotel_id => $arr_info){
					for($i = 0, $max_count = count($arr_info); $i < $max_count; $i++){
						$id = $arr_info[$i]['id'];
						$beds = $arr_info[$i]['available_rooms'] * $room_adults[$id];
						if(!isset($hotel_beds[$hotel_id])){
							$hotel_beds[$hotel_id] = $beds;
						}else{
							$hotel_beds[$hotel_id] += $beds;
						}
					}
				}
				if(!empty($hotel_beds)){
					// Remove the hotels where the default is less room than you need
					foreach($hotel_beds as $hotel_id => $beds){
						if($beds < $minimum_beds){
							$hotel_remove[] = $hotel_id;
						}
					}

					if(!empty($hotel_remove)){
						foreach($hotel_remove as $one_hotel){
							if(isset($this->arrAvailableRooms[$one_hotel])){
								$arr_output['rooms'] -= count($this->arrAvailableRooms[$one_hotel]);
								unset($this->arrAvailableRooms[$one_hotel]);
								unset($arr_hotels[$one_hotel]);
								unset($arr_min_price[$one_hotel]);
							}
						}
						if(!empty($arr_min_price)){
							$min_price_per_hotel = min($arr_min_price);
						}else{
							$min_price_per_hotel = 0;
						}
					}
				}
			}
		}

		$filter_info = $this->FilterAvailabilityRoomsForMinMaxRooms($arr_output['rooms'], $arr_hotels, $arr_min_price, $min_max_hotels);
		$arr_output['rooms']  = $filter_info['rooms_count'];
		$arr_hotels           = $filter_info['arr_hotels'];
		$arr_min_price        = $filter_info['arr_min_price'];
		$min_price_per_hotel  = $filter_info['min_price_per_hotel'];

		if($sort_rating !== null || !empty($type_sort_rating)){

            $arr_hotel_ids = array_keys($this->arrAvailableRooms);
			$sql = 'SELECT r.hotel_id, r.evaluation
					FROM '.TABLE_REVIEWS.' r
					WHERE r.is_active = 1 AND r.hotel_id IN ('.implode(',', $arr_hotel_ids).')
					ORDER BY r.hotel_id ASC';
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] > 0){
				$arr_reviews = array();
				$arr_sum_reviews = array();
				$tmp_arr_available_rooms = array();
				$arr_hotels = array();
				$min_price_per_hotel = 0;
				$arr_output['rooms'] = 0;
				foreach($result[0] as $review){
					$hotel_id = $review['hotel_id'];
					if(isset($arr_sum_reviews[$hotel_id])){
						$arr_sum_reviews[$hotel_id]['sum'] += $review['evaluation'];
						$arr_sum_reviews[$hotel_id]['num']++;
					}else{
						$arr_sum_reviews[$hotel_id] = array('sum'=>$review['evaluation'], 'num'=>'1');
					}
				}
				foreach($arr_sum_reviews as $key => $review){
					$middle_evalution = $review['sum'] / $review['num'];
					if(in_array($type_sort_rating, array('asc','desc')) || round($middle_evalution) == $sort_rating){
						$arr_reviews[$key] = $middle_evalution;
					}
				}
				if(in_array($type_sort_rating, array('asc', 'desc')) || !empty($arr_reviews)){
					if(empty($sort_rating)){
						$hotel_keys = array_keys($this->arrAvailableRooms);
						$hotel_review_keys = array_keys($arr_reviews);
						$diff_array = array_diff($hotel_keys, $hotel_review_keys);
						foreach($diff_array as $hotel_id){
							$arr_reviews[$hotel_id] = 0;
						}
					}
					if($type_sort_rating == 'desc'){
						arsort($arr_reviews, SORT_NUMERIC);
					}else{
						asort($arr_reviews, SORT_NUMERIC);
					}

					// Create a new array sorted in the correct order
					foreach($arr_reviews as $key => $value){
						$tmp_arr_available_rooms[$key] = $this->arrAvailableRooms[$key];
						if(!isset($arr_hotels[$key])){
							$arr_hotels[$key] = 1;
						}
						$arr_output['rooms'] += count($this->arrAvailableRooms[$key]);
						for($i = 0; $i < count($this->arrAvailableRooms[$key]); $i++){
							if(!$min_price_per_hotel || $this->arrAvailableRooms[$key][$i]['lowest_price_per_night'] < $min_price_per_hotel){
								$min_price_per_hotel = $this->arrAvailableRooms[$key][$i]['lowest_price_per_night'];
							}
						}
					}
				}

				$this->arrAvailableRooms = $tmp_arr_available_rooms;
			}
		}

		if(SHOW_BEST_PRICE_ROOMS){
			if(!empty($minimum_beds) && is_array($this->arrAvailableRooms)){
				$arr_best_price = array();
				$arr_select_rooms = array();
				$arr_count_rooms = array();
				$arr_adults = array();
				$arr_empty_price_hotel_ids = array();
				foreach($this->arrAvailableRooms as $hotel_id => $rooms){
					$result = $this->GetBestPriceRooms($rooms, $minimum_beds);
					if(!empty($result)){
						$arr_best_price[$hotel_id] = $result['best_price'];
						$arr_select_rooms[$hotel_id] = $result['select_rooms'];
						$arr_count_rooms[$hotel_id] = $result['count_rooms'];
						$arr_adults[$hotel_id] = $result['adults'];
					}else{
						$arr_empty_price_hotel_ids[] = $hotel_id;
					}
				}
				asort($arr_best_price);

				if(!empty($arr_best_price)){
					$this->arrBestPrices = $arr_best_price;
					$this->arrBestRooms = $arr_select_rooms;
					$this->arrBestRoomCount = $arr_count_rooms;
					$this->arrBestRoomAdults = $arr_adults;
					// Get first key
					reset($arr_best_price);
					$this->hotelIdBestPrice = key($arr_best_price);
					$this->bestPriceValue = current($arr_best_price);
					if($sort_price == 'desc'){
						arsort($arr_best_price);
					}
					if(empty($sort_by) || !empty($sort_price)){
						// Sort arrAvailableRooms
						$hotel_ids = array_keys($arr_best_price);
						$hotel_ids = array_merge($hotel_ids, $arr_empty_price_hotel_ids);
						$tmp_arr_available_rooms = array();
						foreach($hotel_ids as $hotel_id){
							$tmp_arr_available_rooms[$hotel_id] = $this->arrAvailableRooms[$hotel_id];
						}
						$this->arrAvailableRooms = $tmp_arr_available_rooms;
					}
				}
			}
		}


        $arr_output['hotels'] = count($arr_hotels);
		$arr_output['min_price_per_hotel'] = $min_price_per_hotel;

		return $arr_output;
	}
	
	/**
	 * Returns availabel rooms
	 * @return array
	 */
	public function GetArrAvailableRooms(){
		return $this->arrAvailableRooms;
	}
	
	public function SetArrAvailableRooms($array = array()){
		$this->arrAvailableRooms = $array;
	}

	public function SetRelocationType($relocation_type = 'room'){
		$this->relocationType = $relocation_type;
	}

    /**
	 * Draws search result
	 * @param $params
	 * @param $rooms_total
	 * @param $search_suggestion
	 * @param $draw
	 */
	public function DrawSearchResult($params, $rooms_total = 0, $draw = true, $search_suggestion = false)
	{
		if(function_exists('Template_DrawSearchResult')){
            $result = Template_DrawSearchResult($this, $params, $rooms_total, $draw);
            return $result;
        }

		global $objLogin;

		$nl = "\n";
		$output = '';
		$currency_rate = Application::Get('currency_rate');
		if($search_suggestion && empty($currency_rate)){
            $currency_info = Currencies::GetDefaultCurrencyInfo();
            $currency_rate = $currency_info['rate'];
        }
		$currency_format = get_currency_format();
		$lang 		   = Application::Get('lang');
		$rooms_count   = 0;
		$hotels_count  = 0;
		$total_hotels  = Hotels::HotelsCount();
		$search_page_size = (int)ModulesSettings::Get('rooms', 'search_availability_page_size');
		$show_room_types_in_search = ModulesSettings::Get('rooms', 'show_room_types_in_search');
		if($search_page_size <= 0) $search_page_size = '1';
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
		$min_max_hotels = isset($params['min_max_hotels']) ? $params['min_max_hotels'] : array();
		$sort_facilities = isset($params['sort_facilities']) ? $params['sort_facilities'] : '';
		$sort_rating = isset($params['sort_rating']) ? $params['sort_rating'] : '';
		$sort_price = isset($params['sort_price']) ? $params['sort_price'] : '';
		$minimum_beds = isset($params['minimum_beds']) ? $params['minimum_beds'] : '';
		$best_select_rooms = array();
		$best_count_rooms = array();
		$best_adults = array();

		$allow_booking = false;
		if(Modules::IsModuleInstalled('booking')){
			if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
			   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
			  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())
			){
				$allow_booking = true;
			}
		}

		$sql = 'SELECT
					r.id,
					r.room_type,
					r.room_count,
					r.room_icon,
					r.refund_money_type,
					r.refund_money_value,
					IF(r.room_icon_thumb != \'\', r.room_icon_thumb, \'no_image.png\') as room_icon_thumb,
					r.room_picture_1,
					r.room_picture_2,
					r.room_picture_3,
					r.room_picture_4,
					r.room_picture_5,
					CASE
						WHEN r.room_picture_1 != \'\' THEN r.room_picture_1
						WHEN r.room_picture_2 != \'\' THEN r.room_picture_2
						WHEN r.room_picture_3 != \'\' THEN r.room_picture_3
						WHEN r.room_picture_4 != \'\' THEN r.room_picture_4
						WHEN r.room_picture_5 != \'\' THEN r.room_picture_5
						ELSE \'\'
					END as first_room_image,
					r.max_adults,
					r.max_children,
					r.max_extra_beds,
					r.discount_night_type,
					r.discount_night_3,
					r.discount_night_4,
					r.discount_night_5,
					r.discount_guests_type,
					r.discount_guests_3,
					r.discount_guests_4,
					r.discount_guests_5,
					r.default_price as price,
					rd.room_type as loc_room_type,
					rd.room_short_description as loc_room_short_description
				FROM '.TABLE_ROOMS.' r
					INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id
				WHERE
					r.id = _KEY_ AND
					rd.language_id = \''.$lang.'\'';

		if(count($this->arrAvailableRooms) == 1){

			// -------- pagination
			$current_page = isset($_REQUEST['p']) ? (int)$_REQUEST['p'] : '1';
			$total_pages = (int)($rooms_total / $search_page_size);
			if($current_page > ($total_pages+1)) $current_page = 1;
			if(($rooms_total % $search_page_size) != 0) $total_pages++;
			if(!is_numeric($current_page) || (int)$current_page <= 0) $current_page = 1;
			// --------

			if($rooms_total > 0){
				// get a first key of the array
				reset($this->arrAvailableRooms);
				$hotel_id = key($this->arrAvailableRooms);
                $hotel_info = Hotels::GetHotelFullInfo($hotel_id, (!empty($lang) ? $lang : Application::Get('lang')));

				if(SHOW_BEST_PRICE_ROOMS){
					if(empty($minimum_beds)){
						$room_min_price = 0;
						$room_id_best_price = 0;
						foreach($this->arrAvailableRooms[$hotel_id] as $room){
							if($room_min_price == 0 || $room['lowest_price_per_night'] < $room_min_price){
								$room_min_price = $room['lowest_price_per_night'];
								$room_id_best_price = $room['id'];
							}
						}
						if($room_id_best_price != 0){
							$best_select_rooms = array($room_id_best_price);
							$best_count_rooms = array($room_id_best_price => 1);
						}
					}else{
						$best_select_rooms = !empty($this->arrBestRooms[$hotel_id]) ? $this->arrBestRooms[$hotel_id] : array();
						$best_count_rooms = !empty($this->arrBestRoomCount[$hotel_id]) ? $this->arrBestRoomCount[$hotel_id] : array();
						$best_adults = !empty($this->arrBestRoomAdults[$hotel_id]) ? $this->arrBestRoomAdults[$hotel_id] : array();
					}
				}

				if($total_hotels > 1){
					$output .= $this->DrawHotelInfoBlock($hotel_id, $hotel_info, $lang, false, false, $params);
				}

				$meal_plans = MealPlans::GetAllMealPlans($hotel_id);

				// This hotel requires special min/max nights order
				if(isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['alert'])){
					$output .= '<tr>';
					$output .= '  <td align="left" colspan="8" style="padding:2px 10px">'.$min_max_hotels[$hotel_id]['alert'].'</td>';
					$output .= '<tr>';
				}else{
					$min_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['minimum_rooms']) ? $min_max_hotels[$hotel_id]['minimum_rooms'] : 0;
					$max_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['maximum_rooms']) ? $min_max_hotels[$hotel_id]['maximum_rooms'] : 0;
					if(TYPE_FILTER_TO_NUMBER_ROOMS == 'hotel'){
						$message = '';
						if($min_rooms > 1 && $max_rooms > 0){
							$message = str_replace(array('_HOTEL_NAME_', '_MIN_ROOMS_', '_MAX_ROOMS_'), array($hotel_info['name'], $min_rooms, $max_rooms), _HOTEL_RESTRICTIONS_MIN_MAX_ROOMS);
						}elseif($min_rooms > 1){
							$message = str_replace(array('_HOTEL_NAME_', '_MIN_ROOMS_'), array($hotel_info['name'], $min_rooms), _HOTEL_RESTRICTIONS_MIN_ROOMS);
						}elseif($max_rooms > 0){
							$message = str_replace(array('_HOTEL_NAME_', '_MAX_ROOMS_'), array($hotel_info['name'], $max_rooms), _HOTEL_RESTRICTIONS_MAX_ROOMS);
						}
						if(!empty($message)){
							$output .= '<br>'.draw_default_message($message, false);
						}
						$min_rooms = 0;
					}
					foreach($this->arrAvailableRooms[$hotel_id] as $key){

						if($show_room_types_in_search != 'all' && $key['available_rooms'] < 1) continue;
						$rooms_count++;

						if($rooms_count <= ($search_page_size * ($current_page - 1))){
							continue;
						}elseif($rooms_count > ($search_page_size * ($current_page - 1)) + $search_page_size){
							break;
						}

						$room = database_query(str_replace('_KEY_', $key['id'], $sql), DATA_AND_ROWS, FIRST_ROW_ONLY);
						if($room[1] > 0){
							$room_adults = $params['max_adults'];
							if(!empty($minimum_beds)){
								$room_adults = $room[0]['max_adults'] < $minimum_beds ? $room[0]['max_adults'] : $minimum_beds;
							}
							if(SHOW_BEST_PRICE_ROOMS){
								if(!empty($best_adults[$room[0]['id']])){
									$room_adults = $best_adults[$room[0]['id']];
								}
							}
							$output .= '<br />';
							$output .= '<form action="index.php?page=booking" method="post">'.$nl;
							$output .= draw_hidden_field('act', 'add', false).$nl;
							$output .= draw_hidden_field('hotel_id', $hotel_id, false).$nl;
							$output .= draw_hidden_field('room_id', $room[0]['id'], false).$nl;

							if($search_suggestion && isset($params['from_date']) && is_array($params['from_date'])){
                                $output .= draw_hidden_field('from_date', $params['from_date'][$rooms_count], false).$nl;
                            }elseif(isset($params['from_date'])){
                               $output .= draw_hidden_field('from_date', $params['from_date'], false).$nl;
                            }

                            if($search_suggestion && isset($params['to_date']) && is_array($params['to_date'])){
                                $output .= draw_hidden_field('to_date', $params['to_date'][$rooms_count], false).$nl;
                            }elseif(isset($params['to_date'])){
                                $output .= draw_hidden_field('to_date', $params['to_date'], false).$nl;
                            }

                            if($search_suggestion && isset($params['nights']) && is_array($params['nights'])){
                                $output .= draw_hidden_field('nights', $params['nights'][$rooms_count], false).$nl;
                            }elseif(isset($params['nights'])){
                                $output .= draw_hidden_field('nights', $params['nights'], false).$nl;
                            }

							if(isset($params['max_adults']) && !ALLOW_ADULTS_SELECTING) $output .= draw_hidden_field('adults', $params['max_adults'], false).$nl;
							if(isset($params['max_children']) && !ALLOW_CHILDREN_SELECTING) $output .= draw_hidden_field('children', $params['max_children'], false).$nl;
							if(isset($params['hotel_sel_id']))        $output .= draw_hidden_field('hotel_sel_id', $params['hotel_sel_id'], false).$nl;
							if(isset($params['hotel_sel_loc_id']))    $output .= draw_hidden_field('hotel_sel_loc_id', $params['hotel_sel_loc_id'], false).$nl;
							if(isset($params['property_type_id']))    $output .= draw_hidden_field('property_type_id', $params['property_type_id'], false).$nl;
							if(isset($params['checkin_year_month']))  $output .= draw_hidden_field('checkin_year_month', $params['from_year'].'-'.(int)$params['from_month'], false).$nl;
							if(isset($params['checkin_monthday']))    $output .= draw_hidden_field('checkin_monthday', $params['from_day'], false).$nl;
							if(isset($params['checkout_year_month'])) $output .= draw_hidden_field('checkout_year_month', $params['to_year'].'-'.(int)$params['to_month'], false).$nl;
							if(isset($params['checkout_monthday']))   $output .= draw_hidden_field('checkout_monthday', $params['to_day'], false).$nl;
							$output .= draw_hidden_field('lang', Application::Get('lang'), false).$nl;
							$output .= draw_token_field(false).$nl;

							// zzz
							$output .= '<table class="single-hotel-description" width="100%">'.$nl;
							$output .= '<tr valign="top">';
								$output .= '<td>';
									$output .= '<table width="100%">';

									//$room_price_result = self::GetRoomPrice($room[0]['id'], $hotel_id, $params, 'array');
									$room_price_result = self::GetRoomPrice($room[0]['id'], $hotel_id, $params, 'array', $search_suggestion, $rooms_count);
									$room_total_price = $room_price_result['total_price'];
									$room_original_price = $room_price_result['original_price'];

                                    if($search_suggestion && is_array($params['nights'])){
                                        $room_price = (self::GetRoomPriceIncludeNightsDiscount($room_total_price / $params['nights'][$rooms_count], $params['nights'][$rooms_count], $room[0]['id'], $room) * $params['nights'][$rooms_count]);
                                    }else{
                                        $room_price = (self::GetRoomPriceIncludeNightsDiscount($room_total_price / $params['nights'], $params['nights'], $room[0]['id'], $room) * $params['nights']);
                                    }

									if(empty($key['available_rooms'])) $rooms_descr = '<span class="gray">('._FULLY_BOOKED.')</span>';
									else if($room[0]['room_count'] > '1' && $key['available_rooms'] == '1') $rooms_descr = '<span class="red">('._ROOMS_LAST.')</span>';
									else if($room[0]['room_count'] > '1' && $key['available_rooms'] <= '5') $rooms_descr = '<span class="red">('.$key['available_rooms'].' '._ROOMS_LEFT.')</span>';
									else $rooms_descr = '<span class="green"> ('._AVAILABLE.')</span>';

									//$output .= '<tr><td colspan="2"><h4>'.prepare_link('rooms', 'room_id', $room[0]['id'], $room[0]['loc_room_type'], $room[0]['loc_room_type'], '', _CLICK_TO_VIEW).' '.$rooms_descr.'</h4></td></tr>';
                                    $output .= '<tr><td colspan="2"><h4><a title="'._CLICK_TO_VIEW.'" href="index.php?page=rooms&room_id='.$room[0]['id'].'">'.$room[0]['loc_room_type'].'</a>'.$rooms_descr.'</h4></td></tr>';
									$output .= '<tr>';
										$output .= '<td colspan="2">';
											$output .= '<div class="right textcenter room-images-wrapper">';
												if($room[0]['first_room_image'] != '') $output .= '<a href="images/rooms/'.$room[0]['first_room_image'].'" rel="lyteshow_'.$room[0]['id'].'" title="'._IMAGE.' 1">';
												$output .= '<img class="room_icon" src="images/rooms/'.$room[0]['room_icon_thumb'].'" width="165px" alt="icon" />';
												if($room[0]['first_room_image'] != '') $output .= '</a>';
												if($room[0]['room_picture_1'] != '') $output .= '  <a href="images/rooms/'.$room[0]['room_picture_1'].'" rel="lyteshow_'.$room[0]['id'].'" title="'._IMAGE.' 1"></a>';
												if($room[0]['room_picture_2'] != '') $output .= '  <a href="images/rooms/'.$room[0]['room_picture_2'].'" rel="lyteshow_'.$room[0]['id'].'" title="'._IMAGE.' 2"></a>';
												if($room[0]['room_picture_3'] != '') $output .= '  <a href="images/rooms/'.$room[0]['room_picture_3'].'" rel="lyteshow_'.$room[0]['id'].'" title="'._IMAGE.' 3"></a>';
												if($room[0]['room_picture_4'] != '') $output .= '  <a href="images/rooms/'.$room[0]['room_picture_4'].'" rel="lyteshow_'.$room[0]['id'].'" title="'._IMAGE.' 4"></a>';
												if($room[0]['room_picture_5'] != '') $output .= '  <a href="images/rooms/'.$room[0]['room_picture_5'].'" rel="lyteshow_'.$room[0]['id'].'" title="'._IMAGE.' 5"></a>';
												$output .= '<br>';
												if($allow_booking && $key['available_rooms'] && !$search_suggestion){
													if(Reservation::RoomIsReserved($room[0]['id'])){
														$output .= '<input type="submit" '.(HOTEL_BUTTON_RESERVE_AJAX ? 'data-room-id="'.$room[0]['id'].'" data-type="remove" value="'._REMOVE.'!" ' : 'value="'._BOOK_NOW.'" ').'class="form_button_middle red" style="margin:3px;" />';
													}else{
														$output .= '<input type="submit" '.(HOTEL_BUTTON_RESERVE_AJAX ? 'data-room-id="'.$room[0]['id'].'" data-type="reserve" value="'._RESERVE.'!" ' : 'value="'._BOOK_NOW.'" ').'class="form_button_middle green" style="margin:3px;" />';
													}
												}
											$output .= '</div>';

											// Descriprion
											$output .= '<div>';
											$output .= $room[0]['loc_room_short_description'];
											$output .= '</div>';

											// Cancallation policy
											$arr_refund_types = array('0'=>_FIRST_NIGHT, '1'=>_FIXED_PRICE, '2'=>_PERCENTAGE);
											$currency_info = Currencies::GetDefaultCurrencyInfo();
											$default_currency = $currency_info['symbol'];
											$currency_decimals = $currency_info['decimals'];

											$refund_type = isset($room[0]['refund_money_type']) ? $room[0]['refund_money_type'] : '';
											$refund_money_type = isset($room[0]['refund_money_type']) ? $arr_refund_types[$room[0]['refund_money_type']] : '';
											$refund_money_value = isset($room[0]['refund_money_value']) ? $room[0]['refund_money_value'] : '';
											$output .= '<div class="room-cancellation-descr"><span class="grey2">'._CANCELLATION_POLICY_FOR_ROOM.':<br/>';
											$cancel_reservation_day = isset($hotel_info['cancel_reservation_day']) ? $hotel_info['cancel_reservation_day'] : ModulesSettings::Get('booking', 'customers_cancel_reservation');
											$output .= (!empty($cancel_reservation_day) ? _DAYS_TO_CANCEL.' - '.$cancel_reservation_day.' '._DAYS_LC."<br/>" : '');
											if($refund_type != 0){
												$output .= _SIZE_REFUND_BOOKING_ROOMS.' - '.($refund_type == '1' ? $default_currency.number_format((float)$refund_money_value, $currency_decimals, '.', ',') : number_format((float)$refund_money_value, 0).'%');
											}else{
												$output .= _REFUND_AMOUNT_FIRST_NIGHT;
											}
											$output .= '</span></div>';
										$output .= '</td>';
									$output .= '</tr>';
									$output .= '<tr><td colspan="2">'._MAX_ADULTS.': '.$room[0]['max_adults'].(($allow_children == 'yes') ? ', '._MAX_CHILDREN.': '.$room[0]['max_children'] : '').'</td></tr>';

									if($key['available_rooms']){
										//if($params['nights'] > 1){
                                        $output .= '<tr><td>'._RATE_PER_NIGHT.':</td>';
                                        $output .= '<td>';
                                        if($room_price != $room_original_price){
                                            if($search_suggestion && is_array($params['nights'])){
                                                $output .= '<span class="old-price">'.Currencies::PriceFormat(($room_original_price * $currency_rate) / $params['nights'][$rooms_count], '', '', $currency_format).'</span> ';
                                            }else{
                                                $output .= '<span class="old-price">'.Currencies::PriceFormat(($room_original_price * $currency_rate) / $params['nights'], '', '', $currency_format).'</span> ';
                                            }
                                            $output .= '<span class="new-price">';
                                        }

                                        if($search_suggestion && is_array($params['nights'])){
                                            $output .= Currencies::PriceFormat(($room_price * $currency_rate) / $params['nights'][$rooms_count], '', '', $currency_format);
                                        }else{
                                            $output .= Currencies::PriceFormat(($room_price * $currency_rate) / $params['nights'], '', '', $currency_format);
                                        }
                                        if($room_price != $room_original_price){
                                            $output .= '</span>';
                                        }
                                        $output .= '</td>';
                                        $output .= '</tr>';
										//}

										if(!empty($room_adults) && ALLOW_ADULTS_SELECTING){
						  					//$output .= draw_hidden_field('adults', $room_adults, false).$nl;
											$output .= '<tr>';
												$output .= '<td>'._ADULTS.': </td>';
												$output .= '<td>';
												$output .= '<select name="adults" class="form-control adults_ddl">';
												for($i = 1; $i <= $room[0]['max_adults']; $i++){
													$output .= '<option value="'.$i.'"'.($i == $room_adults ? ' selected="selected"' : '').'>'.$i.'</option>';
												}
												$output .= '</select>';
												$output .= '</td>';
											$output .= '</tr>';
										}

										if(!empty($room[0]['max_children']) && ALLOW_CHILDREN_SELECTING){
											//$output .= draw_hidden_field('children', $params['max_children'], false).$nl;
											$room_children = $params['max_children'];
											$output .= '<tr>';
												$output .= '<td>'._CHILDREN.': </td>';
												$output .= '<td>';
												$output .= '<select name="children" class="form-control children_ddl">';
												for($i = 0; $i <= $room[0]['max_children']; $i++){
													$output .= '<option value="'.$i.'"'.($i == $room_children ? ' selected="selected"' : '').'>'.(empty($i) ? '- ' . strtolower(_NO) . ' -' : $i).'</option>';
												}
												$output .= '</td>';
											$output .= '</tr>';
										}

										$output .= '<tr><td>'._ROOMS.':</td>';
										$output .= '<td>';
											if($key['available_rooms'] == 1 || $max_rooms == 1){
												$output .= '<input type="hidden" name="available_rooms" value="1-'.$room_total_price.'" />';
												$output .= '1 ('.Currencies::PriceFormat($room_price * $currency_rate, '', '', $currency_format).')';
											}else{
												$this_room_is_best = in_array($room[0]['id'], $best_select_rooms) ? true : false;
												$options = '<select name="available_rooms" class="form-control available_rooms_ddl'.($this_room_is_best ? ' select-room' : '').'" '.($allow_booking ? '' : 'disabled="disabled"').'>';
												$start_count_rooms = !empty($min_rooms) ? $min_rooms : 1;
												$end_count_rooms = !empty($max_rooms) && $max_rooms < $key['available_rooms'] && $max_rooms != '-1' ? $max_rooms : $key['available_rooms'];
												for($i = $start_count_rooms; $i <= $end_count_rooms; $i++){
													$room_price_i = $room_total_price * $i;
													$room_price_i_formatted = Currencies::PriceFormat(($room_price * $i) * $currency_rate, '', '', $currency_format);
													$options .= '<option value="'.$i.'-'.$room_price_i.'" ';
													if($this_room_is_best){
														$options .= ($i == $best_count_rooms[$room[0]['id']]) ? 'selected="selected" ' : '';
													}else{
														$options .= ($i == '0') ? 'selected="selected" ' : '';
													}
													$options .= '>'.$i.(($i != 0) ? ' ('.$room_price_i_formatted.')' : '').'</option>';
												}
												$output .= $options.'</select>';
											}
										$output .= '</td>';
										$output .= '</tr>';

										if($meal_plans[1] > 0){
											$output .= '<tr>';
												$output .= '<td>'._MEAL_PLANS.': <span class="meal_plans_description"> <span class="red">*</span> '._PERSON_PER_NIGHT.'</span></td>';
												$output .= '<td>';
												$output .= MealPlans::DrawMealPlansDDL($meal_plans, $currency_rate, $currency_format, $allow_booking, false);
												$output .= '</td>';
											$output .= '</tr>';
										}
										if($allow_extra_beds == 'yes' && $room[0]['max_extra_beds'] > 0){
											$output .= '<tr>';
												$output .= '<td>'._EXTRA_BEDS.': <span class="extra_beds_description"> <span class="red">*</span> '._PER_NIGHT.'</span></td>';
												$output .= '<td>';
												$output .= $this->DrawExtraBedsDDL($room[0]['id'], $room[0]['max_extra_beds'], $params, $currency_rate, $currency_format, $allow_booking, false);
												$output .= '</td>';
											$output .= '</tr>';
										}

										$message_discount = '';
                                        if($search_suggestion && is_array($params['nights'])){
                                            $new_price = $room_price / $params['nights'][$rooms_count];
                                        }else{
                                            $new_price = $room_price / $params['nights'];
                                        }
                                        if($room[0]['discount_guests_3'] > 0.00 || $room[0]['discount_guests_4'] > 0.00 || $room[0]['discount_guests_5'] > 0.00){
											$price_3_guest = $new_price - ($room[0]['discount_guests_type'] == 1 ? 0.01 * $new_price * $room[0]['discount_guests_3'] : $room[0]['discount_guests_3']);
											$price_4_guest = $new_price - ($room[0]['discount_guests_type'] == 1 ? 0.01 * $new_price * $room[0]['discount_guests_4'] : $room[0]['discount_guests_4']);
											$price_5_guest = $new_price - ($room[0]['discount_guests_type'] == 1 ? 0.01 * $new_price * $room[0]['discount_guests_5'] : $room[0]['discount_guests_5']);
                                            $price_3_guest = Currencies::PriceFormat($price_3_guest > 0 ? $price_3_guest * $currency_rate : 0, '', '');
                                            $price_4_guest = Currencies::PriceFormat($price_4_guest > 0 ? $price_4_guest * $currency_rate : 0, '', '');
                                            $price_5_guest = Currencies::PriceFormat($price_5_guest > 0 ? $price_5_guest * $currency_rate : 0, '', '');

                                            $message_discount .= str_replace(array('_PRICE_3_PEOPLE_', '_PRICE_4_PEOPLE_', '_PRICE_5_PEOPLE_'), array('<span class="new-price">'.$price_3_guest.'</span>', '<span class="new-price">'.$price_4_guest.'</span>', '<span class="new-price">'.$price_5_guest.'</span>'), (TYPE_DISCOUNT_GUEST == 'guests' ? _MESSAGE_DISCOUNTS_GUESTS : _MESSAGE_DISCOUNTS_ROOMS));
                                        }
                                        if($room[0]['discount_night_3'] > 0.00 || $room[0]['discount_night_4'] > 0.00 || $room[0]['discount_night_5'] > 0.00){
                                            if($search_suggestion && is_array($params['nights'])){
                                                $original_price = $room_original_price / $params['nights'][$rooms_count];
                                            }else{
                                                $original_price = $room_original_price / $params['nights'];
                                            }

											$price_3_night = $original_price - ($room[0]['discount_night_type'] == 1 ? 0.01 * $original_price * $room[0]['discount_night_3'] : $room[0]['discount_night_3']);
											$price_4_night = $original_price - ($room[0]['discount_night_type'] == 1 ? 0.01 * $original_price * $room[0]['discount_night_4'] : $room[0]['discount_night_4']);
											$price_5_night = $original_price - ($room[0]['discount_night_type'] == 1 ? 0.01 * $original_price * $room[0]['discount_night_5'] : $room[0]['discount_night_5']);
                                            $price_3_night = Currencies::PriceFormat($price_3_night > 0 ? $price_3_night * $currency_rate : 0, '', '');
                                            $price_4_night = Currencies::PriceFormat($price_4_night > 0 ? $price_4_night * $currency_rate : 0, '', '');
                                            $price_5_night = Currencies::PriceFormat($price_5_night > 0 ? $price_5_night * $currency_rate : 0, '', '');

                                            $message_discount .= (!empty($message_discount) ? '<br/><br/>' : '').str_replace(array('_PRICE_3_NIGHT_', '_PRICE_4_NIGHT_', '_PRICE_5_NIGHT_'), array('<span class="new-price">'.$price_3_night.'</span>', '<span class="new-price">'.$price_4_night.'</span>', '<span class="new-price">'.$price_5_night.'</span>'), _MESSAGE_DISCOUNTS_NIGHT);
                                        }
										if(!empty($message_discount)){
											$output .= '<tr><td colspan="2" class="message-discount">'.$message_discount.'</td>';
										}
									}

									$output .= '<tr><td colspan="2"><a class="price_link" href="javascript:void(0);" onclick="javascript:appToggleElement(\'row_prices_'.$room[0]['id'].'\')" title="'._CLICK_TO_SEE_PRICES.'">'._PRICES.' (+)</a></td></tr>';
									$output .= '</table>';
								$output .= '</td>';
							$output .= '</tr>';
							$output .= '<tr><td colspan="2"><span id="row_prices_'.$room[0]['id'].'" style="margin:5px 5px 10px 5px;display:none;">'.self::GetRoomPricesTable($room[0]['id']).'</span></td></tr>';
							if($rooms_count <= ($rooms_total - 1)) $output .= '<tr><td colspan="2"><div class="line-hor"></div><td></tr>';
							else $output .= '<tr><td colspan="2"><br /><td></tr>';
							$output .= '</table>'.$nl;
							$output .= '</form>'.$nl;
						}
					}
					if($search_suggestion && !empty($this->arrAvailableRooms)){
                        $output .= '<div class="col-md-8 col-md-offset-4">';
                        $output .= '<input type="button" id="search_suggestion_reserve" data-type="reserve" value="'._RESERVE.'!" class="form_button_middle green" style="margin:3px;padding:20px 30px;font-size:20px;text-align:center;" />';
                        $output .= '</div>';
                    }
				}
			}

			$output .= $this->DrawPaginationLinks($total_pages, $current_page, $params, false);

		}else{
			// multi hotels found
			// -------- pagination
			$hotels_total = count($this->arrAvailableRooms);
			$current_page = isset($_REQUEST['p']) ? (int)$_REQUEST['p'] : '1';
			$total_pages = (int)($hotels_total / $search_page_size);
			if($current_page > ($total_pages+1)) $current_page = 1;
			if(($hotels_total % $search_page_size) != 0) $total_pages++;
			if(!is_numeric($current_page) || (int)$current_page <= 0) $current_page = 1;
			// --------

            if($rooms_total > 0){
				$arr_hotel_ids = array();
				foreach($this->arrAvailableRooms as $key => $val){
					$hotels_count++;
					if($hotels_count <= ($search_page_size * ($current_page - 1))){
						continue;
					}elseif($hotels_count > ($search_page_size * ($current_page - 1)) + $search_page_size){
						break;
					}
					$arr_hotel_ids[] = $key;
				}

				$hotel_last_booking = array();
				if(!empty($arr_hotel_ids)){
					$last_bookings = Bookings::GetLastBookingForHotels($arr_hotel_ids);

					if($last_bookings[1] > 0){
						// hotel_id set as a key
						foreach($last_bookings[0] as $last_booking){
							$hotel_last_booking[$last_booking['hotel_id']] = $last_booking;
						}
					}
				}

				$hotels_count  = 0;
				foreach($this->arrAvailableRooms as $hotel_id => $val){
					$meal_plans = MealPlans::GetAllMealPlans($hotel_id);
					$hotels_count++;
					$hotel_info = Hotels::GetHotelFullInfo($hotel_id, (!empty($lang) ? $lang : Application::Get('lang')));

					if($hotels_count <= ($search_page_size * ($current_page - 1))){
						continue;
					}elseif($hotels_count > ($search_page_size * ($current_page - 1)) + $search_page_size){
						break;
					}

					if(SHOW_BEST_PRICE_ROOMS){
						if(empty($minimum_beds)){
							$room_min_price = 0;
							$room_id_best_price = 0;
							foreach($val as $room){
								if($room_min_price == 0 || $room['lowest_price_per_night'] < $room_min_price){
									$room_min_price = $room['lowest_price_per_night'];
									$room_id_best_price = $room['id'];
								}
							}
							if($room_id_best_price != 0){
								$best_select_rooms = array($room_id_best_price);
								$best_count_rooms = array($room_id_best_price => 1);
								$best_hotel = false;
							}
						}else{
							if($hotel_id == $this->hotelIdBestPrice){
								$best_hotel = true;
							}else{
								$best_hotel = false;
							}
							$best_select_rooms = !empty($this->arrBestRooms[$hotel_id]) ? $this->arrBestRooms[$hotel_id] : array();
							$best_count_rooms = !empty($this->arrBestRoomCount[$hotel_id]) ? $this->arrBestRoomCount[$hotel_id] : array();
							$best_adults = !empty($this->arrBestRoomAdults[$hotel_id]) ? $this->arrBestRoomAdults[$hotel_id] : array();
						}
					}
					//if($hotels_count > 1) $output .= '<br><div class="line-hor"></div>';

					$output .= '<div class="one-hotel'.($best_hotel ? ' best-hotel' : '').'">';
					$output .= $this->DrawHotelInfoBlock($hotel_id, $hotel_info,$lang, true, false, $params);

					// Swipe icon for rooms table
                    $output .= '<div id="book_block_'.$hotel_id.'" class="swipe-table table-responsive div-room-prices offset-1">';
					$output .= draw_swipe_icon();

					$output .= '<table class="room_prices">';
					$output .= '<tr class="header">';
					$output .= '  <th align="left">&nbsp;'._ROOM_TYPE.'</th>';
					$output .= '  <th align="center" colspan="3" width="80px">'._MAX_OCCUPANCY.'</th>';
					$output .= '  <th align="center">'._ROOMS.'</th>';
					$output .= '  <th align="center" width="80px">'._RATE.'</th>';
					if($meal_plans[1] > 0) $output .= '<th align="center">'._MEAL_PLANS.'</th>';
					$output .= '  <th align="center">&nbsp;</th>';
					$output .= '</tr>';

					$output .= '<tr class="header" style="font-size:10px;background-color:transparent;">';
					$output .= '  <th align="left">&nbsp;</th>';
					$output .= '  <th align="center">'._ADULTS.'</th>';
					$output .= '  '.(($allow_children == 'yes') ? '<th align="center" width="75px">'._CHILDREN.'</th>' : '<th></th>');
					$output .= '  '.(($allow_extra_beds == 'yes') ? '<th align="center" width="95px">'._EXTRA_BED.' <span class="help" title="'._PER_NIGHT.'">[?]</span></th>' : '<th></th>');
					$output .= '  <th align="center">&nbsp;</th>';

                    if($search_suggestion && is_array($params['nights'])){
                        $output .= '  <th align="center">'.(($params['nights'][$rooms_count] > 1) ? _RATE_PER_NIGHT_AVG : _RATE_PER_NIGHT).'</th>';
                    }else{
                        $output .= '  <th align="center">'.(($params['nights'] > 1) ? _RATE_PER_NIGHT_AVG : _RATE_PER_NIGHT).'</th>';
                    }

					if($meal_plans[1] > 0) $output .= '  <th align="center">'._PERSON_PER_NIGHT.'</th>';
					$output .= '  <th align="center" width="120px">&nbsp;</th>';
					$output .= '</tr>';

					// This hotel requires special min/max nights order
					if(isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['alert'])){
						$output .= '<tr>';
						$output .= '  <td align="left" colspan="8" style="padding:10px 10px 0px 10px">'.$min_max_hotels[$hotel_id]['alert'].'</td>';
						$output .= '<tr>';
					}else{
                        $min_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['minimum_rooms']) ? $min_max_hotels[$hotel_id]['minimum_rooms'] : 0;
                        $max_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['maximum_rooms']) ? $min_max_hotels[$hotel_id]['maximum_rooms'] : 0;
                        $r_count = (!$search_suggestion && $hotels_total == 1) ? 0 : $hotels_count;
                        foreach($val as $k_key => $v_val){
                            if(!$search_suggestion && $hotels_total == 1){$r_count++;}
							if($show_room_types_in_search != 'all' && $v_val['available_rooms'] < 1) continue;

							$room = database_query(str_replace('_KEY_', $v_val['id'], $sql), DATA_AND_ROWS, FIRST_ROW_ONLY);
							if($room[1] > 0){

								$room_price_result = self::GetRoomPrice($room[0]['id'], $hotel_id, $params, 'array', $search_suggestion, $r_count);
								$room_total_price = $room_price_result['total_price'];
								$room_original_price = $room_price_result['original_price'];
                                if($search_suggestion && is_array($params['nights'])){
                                    $room_price = (self::GetRoomPriceIncludeNightsDiscount($room_total_price / $params['nights'][$r_count], $params['nights'][$r_count], $room[0]['id'], $room) * $params['nights'][$r_count]);
                                }else{
                                    $room_price = (self::GetRoomPriceIncludeNightsDiscount($room_total_price / $params['nights'], $params['nights'], $room[0]['id'], $room) * $params['nights']);
                                }


								if(empty($v_val['available_rooms'])) $rooms_descr = '<span class="gray">('._FULLY_BOOKED.')</span>';
								else if($room[0]['room_count'] > '1' && $v_val['available_rooms'] == '1') $rooms_descr = '<span class="red">('._ROOMS_LAST.')</span>';
								else if($room[0]['room_count'] > '1' && $v_val['available_rooms'] <= '5') $rooms_descr = '<span class="red">('.$v_val['available_rooms'].' '._ROOMS_LEFT.')</span>';
								else $rooms_descr = '<span class="green">('._AVAILABLE.')</span>';

								$output .= '<tr>';
								$output .= '<form action="index.php?page=booking" method="post">'.$nl;
								$output .= draw_hidden_field('act', 'add', false).$nl;
								$output .= draw_hidden_field('hotel_id', $hotel_id, false).$nl;
								$output .= draw_hidden_field('room_id', $room[0]['id'], false).$nl;

                                if($search_suggestion && isset($params['from_date']) && is_array($params['from_date'])){
                                    $output .= draw_hidden_field('from_date', $params['from_date'][$r_count], false).$nl;
                                }elseif(isset($params['from_date'])){
                                    $output .= draw_hidden_field('from_date', $params['from_date'], false).$nl;
                                }

                                if($search_suggestion && isset($params['to_date']) && is_array($params['to_date'])){
                                    $output .= draw_hidden_field('to_date', $params['to_date'][$r_count], false).$nl;
                                }elseif(isset($params['to_date'])){
                                    $output .= draw_hidden_field('to_date', $params['to_date'], false).$nl;
                                }

                                if($search_suggestion && isset($params['nights']) && is_array($params['nights'])){
                                    $output .= draw_hidden_field('nights', $params['nights'][$r_count], false).$nl;
                                }elseif(isset($params['nights'])){
                                    $output .= draw_hidden_field('nights', $params['nights'], false).$nl;
                                }

								$room_adults = $params['max_adults'];
								if(!empty($minimum_beds)){
									$room_adults = $room[0]['max_adults'] < $minimum_beds ? $room[0]['max_adults'] : $minimum_beds;
								}
								if(SHOW_BEST_PRICE_ROOMS){
									if(!empty($best_adults[$room[0]['id']])){
										$room_adults = $best_adults[$room[0]['id']];
									}
								}
								$output .= (!ALLOW_ADULTS_SELECTING ? draw_hidden_field('adults', $room_adults, false).$nl : '');
								$output .= (!ALLOW_CHILDREN_SELECTING ? draw_hidden_field('children', $params['max_children'], false).$nl : '');
								$output .= draw_hidden_field('hotel_sel_id', $params['hotel_sel_id'], false).$nl;
								$output .= draw_hidden_field('hotel_sel_loc_id', $params['hotel_sel_loc_id'], false).$nl;
								$output .= draw_hidden_field('property_type_id', $params['property_type_id'], false).$nl;
								$output .= draw_hidden_field('sort_by', $params['sort_by'], false).$nl;
                                if($search_suggestion && is_array($params['from_year']) && is_array($params['from_month'])){
                                    $output .= draw_hidden_field('checkin_year_month', $params['from_year'][$r_count].'-'.(int)$params['from_month'][$r_count], false).$nl;
                                }else{
                                    $output .= draw_hidden_field('checkin_year_month', $params['from_year'].'-'.(int)$params['from_month'], false).$nl;
                                }
                                if($search_suggestion && is_array($params['from_day'])){
                                    $output .= draw_hidden_field('checkin_monthday', $params['from_day'][$r_count], false).$nl;
                                }else{
                                    $output .= draw_hidden_field('checkin_monthday', $params['from_day'], false).$nl;
                                }
                                if($search_suggestion && is_array($params['to_year']) && is_array($params['to_month'])){
                                    $output .= draw_hidden_field('checkout_year_month', $params['to_year'][$r_count].'-'.(int)$params['to_month'][$r_count], false).$nl;
                                }else{
                                    $output .= draw_hidden_field('checkout_year_month', $params['to_year'].'-'.(int)$params['to_month'], false).$nl;
                                }
                                if($search_suggestion && is_array($params['to_day'])){
                                    $output .= draw_hidden_field('checkout_monthday', $params['to_day'][$r_count], false).$nl;
                                }else{
                                    $output .= draw_hidden_field('checkout_monthday', $params['to_day'], false).$nl;
                                }
                                $output .= draw_hidden_field('lang', Application::Get('lang'), false).$nl;
								$output .= draw_token_field(false).$nl;

								$arr_refund_types = array('0'=>_FIRST_NIGHT, '1'=>_FIXED_PRICE, '2'=>_PERCENTAGE);

								$currency_info = Currencies::GetDefaultCurrencyInfo();
								$default_currency = $currency_info['symbol'];
								$currency_decimals = $currency_info['decimals'];

								$refund_type = isset($room[0]['refund_money_type']) ? $room[0]['refund_money_type'] : '';
								$refund_money_type = isset($room[0]['refund_money_type']) ? $arr_refund_types[$room[0]['refund_money_type']] : '';
								$refund_money_value = isset($room[0]['refund_money_value']) ? $room[0]['refund_money_value'] : '';
                                $cancel_reservation_day = isset($hotel_info['cancel_reservation_day']) ? $hotel_info['cancel_reservation_day'] : ModulesSettings::Get('booking', 'customers_cancel_reservation');
								$canceled_info = _CANCELLATION_POLICY_FOR_ROOM.":\n";
                                $canceled_info .= (!empty($cancel_reservation_day) ? _DAYS_TO_CANCEL.' - '.$cancel_reservation_day.' '._DAYS_LC."\n" : '');
								if($refund_type != 0){
									$canceled_info .= _SIZE_REFUND_BOOKING_ROOMS.' - '.($refund_type == '1' ? $default_currency.number_format((float)$refund_money_value, $currency_decimals, '.', ',') : number_format((float)$refund_money_value, 0).'%');
								}else{
									$canceled_info .= _REFUND_AMOUNT_FIRST_NIGHT;
								}

								//$output .= '<td align="left">&nbsp;'.prepare_link('rooms', 'room_id', $room[0]['id'], $room[0]['loc_room_type'], $room[0]['loc_room_type'], '', _CLICK_TO_VIEW).'&nbsp;<img src="images/info.png" class="help" title="'.htmlspecialchars($canceled_info).'" style="height:14px;" /><br> &nbsp;<small>'.$rooms_descr.'</small></td>';
								$output .= '<td align="left">&nbsp;<a title="'._CLICK_TO_VIEW.'" href="index.php?page=rooms&room_id='.$room[0]['id'].'">'.$room[0]['loc_room_type'].'</a>&nbsp;<img src="images/info.png" class="help" title="'.htmlspecialchars($canceled_info).'" style="height:14px;" /><br> &nbsp;<small>'.$rooms_descr.'</small></td>';

								// Draw adults DDL
								$output .= '<td align="center">';
									if(!empty($room[0]['max_adults']) && ALLOW_ADULTS_SELECTING){
										$room_adults = $params['max_adults'];
										$output .= '<select name="adults" class="form-control adults_ddl">';
										for($i = 1; $i <= $room[0]['max_adults']; $i++){
											$output .= '<option value="'.$i.'"'.($i == $room_adults ? ' selected="selected"' : '').'>'.$i.'</option>';
										}
									}else{
										$output .= $room[0]['max_adults'];
									}
								$output .= '</td>';

								// Draw children DDL
								if($allow_children == 'yes'){
									$output .= '<td align="center">';
									if(!empty($room[0]['max_children']) && ALLOW_CHILDREN_SELECTING){
										$room_children = $params['max_children'];
										$output .= '<select name="children" class="form-control children_ddl">';
										for($i = 0; $i <= $room[0]['max_children']; $i++){
											$output .= '<option value="'.$i.'"'.($i == $room_children ? ' selected="selected"' : '').'>'.(empty($i) ? '- ' . strtolower(_NO) . ' -' : $i).'</option>';
										}
									}else{
										$output .= (!empty($room[0]['max_children']) ? $room[0]['max_children'] : '--');
									}
									$output .= '</td>';
								}

								$output .= '<td align="center">';
										if(!empty($v_val['available_rooms']) && $allow_extra_beds == 'yes' && $room[0]['max_extra_beds'] > 0){
											$output .= $this->DrawExtraBedsDDL($room[0]['id'], $room[0]['max_extra_beds'], $params, $currency_rate, $currency_format, $allow_booking, false);
										}else{
											$output .= '--';
										}
								$output .= '</td>';
								$output .= '<td align="center">';
									if(!empty($v_val['available_rooms'])){
										$this_room_is_best = in_array($room[0]['id'], $best_select_rooms) ? true : false;
										$output .= '<select name="available_rooms" class="form-control available_rooms_ddl'.($this_room_is_best ? ' select-room' : '').'" '.($allow_booking ? '' : 'disabled="disabled"').'>';
										$options = '';
                                        $start_count_rooms = !empty($min_rooms) ? $min_rooms : 1;
                                        $end_count_rooms = !empty($max_rooms) && $max_rooms < $v_val['available_rooms'] && $max_rooms != '-1' ? $max_rooms : $v_val['available_rooms'];
										for($i = $start_count_rooms; $i <= $end_count_rooms; $i++){
											$room_price_i = $room_total_price * $i;
											$room_price_i_formatted = Currencies::PriceFormat(($room_price * $i) * $currency_rate, '', '', $currency_format);
											$options .= '<option value="'.$i.'-'.$room_price_i.'" ';
											if($this_room_is_best){
												$options .= ($i == $best_count_rooms[$room[0]['id']]) ? 'selected="selected" ' : '';
											}else{
												$options .= ($i == '0') ? 'selected="selected" ' : '';
											}
											$options .= '>'.$i.(($i != 0) ? ' ('.$room_price_i_formatted.')' : '').'</option>';
										}
										$output .= $options.'</select>';
									}
								$output .= '</td>';

								$output .= '<td align="center">';
								if($room_price != $room_original_price){
                                    if($search_suggestion && is_array($params['nights'])){
                                        $output .= '<span class="old-price">'.Currencies::PriceFormat(($room_original_price * $currency_rate) / $params['nights'][$r_count], '', '', $currency_format).'</span><br>';
                                    }else{
                                        $output .= '<span class="old-price">'.Currencies::PriceFormat(($room_original_price * $currency_rate) / $params['nights'], '', '', $currency_format).'</span><br>';
                                    }
									$output .= '<span class="new-price">';
								}

								$message_discount = '';
                                if($search_suggestion && is_array($params['nights'])){
                                    $new_price = $room_price / $params['nights'][$r_count];
                                }else{
                                    $new_price = $room_price / $params['nights'];
                                }
								if($room[0]['discount_guests_3'] > 0.00 || $room[0]['discount_guests_4'] > 0.00 || $room[0]['discount_guests_5'] > 0.00){
									$price_3_guest = $new_price - ($room[0]['discount_guests_type'] == 1 ? 0.01 * $new_price * $room[0]['discount_guests_3'] : $room[0]['discount_guests_3']);
									$price_4_guest = $new_price - ($room[0]['discount_guests_type'] == 1 ? 0.01 * $new_price * $room[0]['discount_guests_4'] : $room[0]['discount_guests_4']);
									$price_5_guest = $new_price - ($room[0]['discount_guests_type'] == 1 ? 0.01 * $new_price * $room[0]['discount_guests_5'] : $room[0]['discount_guests_5']);
                                    $price_3_guest = Currencies::PriceFormat($price_3_guest > 0 ? $price_3_guest * $currency_rate : 0, '', '');
                                    $price_4_guest = Currencies::PriceFormat($price_4_guest > 0 ? $price_4_guest * $currency_rate : 0, '', '');
                                    $price_5_guest = Currencies::PriceFormat($price_5_guest > 0 ? $price_5_guest * $currency_rate : 0, '', '');

                                    $message_discount .= str_replace(array('_PRICE_3_PEOPLE_', '_PRICE_4_PEOPLE_', '_PRICE_5_PEOPLE_'), array($price_3_guest, $price_4_guest, $price_5_guest), (TYPE_DISCOUNT_GUEST == 'guests' ? _MESSAGE_DISCOUNTS_GUESTS : _MESSAGE_DISCOUNTS_ROOMS));
								}
								if($room[0]['discount_night_3'] > 0.00 || $room[0]['discount_night_4'] > 0.00 || $room[0]['discount_night_5'] > 0.00){
                                    if($search_suggestion && is_array($params['nights'])){
                                        $original_price = $room_original_price / $params['nights'][$r_count];
                                    }else{
                                        $original_price = $room_original_price / $params['nights'];
                                    }
									$price_3_night = $original_price - ($room[0]['discount_night_type'] == 1 ? 0.01 * $original_price * $room[0]['discount_night_3'] : $room[0]['discount_night_3']);
									$price_4_night = $original_price - ($room[0]['discount_night_type'] == 1 ? 0.01 * $original_price * $room[0]['discount_night_4'] : $room[0]['discount_night_4']);
									$price_5_night = $original_price - ($room[0]['discount_night_type'] == 1 ? 0.01 * $original_price * $room[0]['discount_night_5'] : $room[0]['discount_night_5']);
									$price_3_night = Currencies::PriceFormat($price_3_night > 0 ? $price_3_night * $currency_rate : 0, '', '');
									$price_4_night = Currencies::PriceFormat($price_4_night > 0 ? $price_4_night * $currency_rate : 0, '', '');
									$price_5_night = Currencies::PriceFormat($price_5_night > 0 ? $price_5_night * $currency_rate : 0, '', '');

                                    $message_discount .= (!empty($message_discount) ? '<br/><br/>' : '').str_replace(array('_PRICE_3_NIGHT_', '_PRICE_4_NIGHT_', '_PRICE_5_NIGHT_'), array($price_3_night, $price_4_night, $price_5_night), _MESSAGE_DISCOUNTS_NIGHT);
								}
								if(!empty($message_discount)){
                                    $output .= '<img src="images/info.png" class="help" title="'.htmlentities(str_replace(array('<br/>', '<br>'), "\r\n", $message_discount)).'" alt="tooltip" style="height:14px;vertical-align:baseline;">&nbsp;';
								}

                                if($search_suggestion && is_array($params['nights'])){
                                    if($params['nights'][$r_count] > 1){
                                        $output .= Currencies::PriceFormat(($room_price * $currency_rate) / $params['nights'][$r_count], '', '', $currency_format);
                                    }else{
                                        $output .= Currencies::PriceFormat($room_price * $currency_rate, '', '', $currency_format);
                                    }
                                }else{
                                    if($params['nights'] > 1){
                                        $output .= Currencies::PriceFormat(($room_price * $currency_rate) / $params['nights'], '', '', $currency_format);
                                    }else{
                                        $output .= Currencies::PriceFormat($room_price * $currency_rate, '', '', $currency_format);
                                    }
                                }

								if($room_price != $room_original_price){
									$output .= '</span>';
								}
								$output .= '</td>';

								if($meal_plans[1] > 0){
									$output .= '<td align="center">';
									if(!empty($v_val['available_rooms'])){
										$output .= MealPlans::DrawMealPlansDDL($meal_plans, $currency_rate, $currency_format, $allow_booking, false);
									}
									$output .= '</td>';
								}
								$output .= '<td align="right">';
								if($allow_booking && $v_val['available_rooms'] && !$search_suggestion){
									if(Reservation::RoomIsReserved($room[0]['id'])){
										$output .= '<input type="submit" '.(HOTEL_BUTTON_RESERVE_AJAX ? 'data-room-id="'.$room[0]['id'].'" data-type="remove" value="'._REMOVE.'!" ' : 'value="'._BOOK_NOW.'" ').'class="form_button_middle red" style="margin:3px;" />';
									}else{
										$output .= '<input type="submit" '.(HOTEL_BUTTON_RESERVE_AJAX ? 'data-room-id="'.$room[0]['id'].'" data-type="reserve" value="'._RESERVE.'!" ' : 'value="'._BOOK_NOW.'" ').'class="form_button_middle green" style="margin:3px;" />';
									}
								}elseif($search_suggestion){
                                    $output .= '<button class="bookbtn mr20" type="button" onclick="jQuery(location).attr(\'href\',\'index.php?page=rooms&room_id='.$room[0]['id'].'\')">'._DETAILS.'</button>';
                                }
								$output .= '</td>';
								$output .= '</form>'.$nl;
								$output .= '</tr>';
							}
						}
					}

					$output .= '</table>';
                    $output .= '</div>';
					$output .= '</div>';
                    $output .= '<div class="offset-2"><hr class="featurette-divider3"></div>';
					$output .= '<div class="clearfix"></div>';
				}
                if($search_suggestion && !empty($this->arrAvailableRooms)){
                    $output .= '<div class="col-md-8 col-md-offset-4">';
                    $output .= '<input type="button" id="search_suggestion_reserve" data-type="reserve" value="'._RESERVE.'!" class="form_button_middle green" style="margin:3px;padding:20px 30px;font-size:20px;text-align:center;" />';
                    $output .= '</div>';
                }
			}

			$output .= $this->DrawPaginationLinks($total_pages, $current_page, $params, false);
		}

		if(!$search_suggestion){
            $output .= $this->PrepareJsForSearch();
        }else{
            $output .= $this->PrepareJsForSearchSuggestion();
        }

		$output .= '<br>';

		if($draw) echo $output;
		else return $output;
	}

    /**
	 * Draws search result
	 * @param $params
	 * @param $rooms_total
	 * @param $draw
	 */
	public function DrawWidgetSearchResult($params, $rooms_total = 0, $host = '', $draw = true)
	{
		global $objLogin;

        $host = rtrim($host, '/');
		$nl = "\n";
		$output = '';
		$lang 		   = Application::Get('lang');
		$rooms_count   = 0;
		$hotels_count  = 0;
		$total_hotels  = Hotels::HotelsCount();

		$search_page_size = (int)ModulesSettings::Get('rooms', 'search_availability_page_size');
		$show_room_types_in_search = ModulesSettings::Get('rooms', 'show_room_types_in_search');
		if($search_page_size <= 0) $search_page_size = '1';
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
		$min_max_hotels = isset($params['min_max_hotels']) ? $params['min_max_hotels'] : array();
		$sort_facilities = isset($params['sort_facilities']) ? $params['sort_facilities'] : '';
		$sort_rating = isset($params['sort_rating']) ? $params['sort_rating'] : '';
		$sort_price = isset($params['sort_price']) ? $params['sort_price'] : '';

		$allow_booking = false;
		if(Modules::IsModuleInstalled('booking')){
			if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
			   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
			  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())
			){
				$allow_booking = true;
			}
		}

		$sql = 'SELECT
                    r.id,
                    r.hotel_id,
					r.room_type,
					r.room_count,
					r.room_icon,
					IF(r.room_icon_thumb != \'\', r.room_icon_thumb, \'no_image.png\') as room_icon_thumb,
					r.room_picture_1,
					r.room_picture_2,
					r.room_picture_3,
					r.room_picture_4,
					r.room_picture_5,
					r.room_picture_1_thumb,
					r.room_picture_2_thumb,
					r.room_picture_3_thumb,
					r.room_picture_4_thumb,
					r.room_picture_5_thumb,
					CASE
						WHEN r.room_picture_1 != \'\' THEN r.room_picture_1
						WHEN r.room_picture_2 != \'\' THEN r.room_picture_2
						WHEN r.room_picture_3 != \'\' THEN r.room_picture_3
						WHEN r.room_picture_4 != \'\' THEN r.room_picture_4
						WHEN r.room_picture_5 != \'\' THEN r.room_picture_5
						ELSE \'\'
					END as first_room_image,
					r.max_adults,
					r.max_children,
					r.max_extra_beds,
					r.discount_night_type,
					r.discount_night_3,
					r.discount_night_4,
					r.discount_night_5,
					r.discount_guests_type,
					r.discount_guests_3,
					r.discount_guests_4,
					r.discount_guests_5,
					r.default_price as price,
					r.room_area,
					r.facilities,
					rd.room_type as loc_room_type,
                    rd.room_short_description as loc_room_short_description,
                    rd.room_long_description as loc_room_long_description,
                    h.stars as hotel_stars,
                    h.property_type_id as hotel_property_type_id,
                    hd.name as hotel_name
				FROM '.TABLE_ROOMS.' r
					INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id
					INNER JOIN '.TABLE_HOTELS.' h ON r.hotel_id = h.id AND h.is_active = 1
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id

				WHERE
					r.id = _KEY_ AND
                    rd.language_id = \''.$lang.'\' AND
                    hd.language_id = \''.$lang.'\'';

			// multi hotels found
			// -------- pagination
			$hotels_total = count($this->arrAvailableRooms);
			$real_rooms_total = 0;
			$current_page = isset($_REQUEST['p']) ? (int)$_REQUEST['p'] : '1';
			$total_pages = (int)($rooms_total / $search_page_size);
			if($current_page > ($total_pages+1)) $current_page = 1;
			if(($rooms_total % $search_page_size) != 0) $total_pages++;
			if(!is_numeric($current_page) || (int)$current_page <= 0) $current_page = 1;
			// --------

			// prepare properties array
			$property_types = HotelsPropertyTypes::GetHotelsPropertyTypes();
			foreach($property_types as $key => $val){
				$arr_property_types[$val['id']] = $val['name'];
			}

			if($rooms_total > 0){
				$arr_hotel_ids = array();
				$rooms_end_this_page = 0;
				foreach($this->arrAvailableRooms as $key => $val){
					$rooms_start_this_page = $rooms_end_this_page + 1;
					$rooms_end_this_page += count($val);
					if($rooms_end_this_page <= ($search_page_size * ($current_page - 1))){
						continue;
					}elseif($rooms_start_this_page > ($search_page_size * ($current_page - 1)) + $search_page_size){
						break;
					}
					$arr_hotel_ids[] = $key;
				}

				$hotel_last_booking = array();
				if(!empty($arr_hotel_ids)){
					$last_bookings = Bookings::GetLastBookingForHotels($arr_hotel_ids);

					if($last_bookings[1] > 0){
						// hotel_id set as a key
						foreach($last_bookings[0] as $last_booking){
							$hotel_last_booking[$last_booking['hotel_id']] = $last_booking;
						}
					}
				}

				$rooms_end_this_page  = 0;
				foreach($this->arrAvailableRooms as $hotel_id => $val){
					$h_key = 'h_'.$hotel_id;
					$meal_plans = MealPlans::GetAllMealPlans($hotel_id);
					$rooms_start_this_page = $rooms_end_this_page + 1;
					$rooms_end_this_page += count($val);

					if($rooms_end_this_page <= ($search_page_size * ($current_page - 1))){
						continue;
					}elseif($rooms_start_this_page > ($search_page_size * ($current_page - 1)) + $search_page_size){
						break;
					}

					//if($hotels_count > 1) $output .= '<br><div class="line-hor"></div>';
					// This hotel requires special min/max nights order
					if(isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['alert'])){
						$output .= $min_max_hotels[$hotel_id]['alert'];
					}else{
                        $min_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['minimum_rooms']) ? $min_max_hotels[$hotel_id]['minimum_rooms'] : 0;
						$max_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['maximum_rooms']) ? $min_max_hotels[$hotel_id]['maximum_rooms'] : 0;
						$rooms_count = $rooms_start_this_page - 1;
						foreach($val as $k_key => $v_val){
							$rooms_count++;
							if($rooms_count <= ($search_page_size * ($current_page - 1))){
								continue;
							}elseif($rooms_count > ($search_page_size * ($current_page - 1)) + $search_page_size){
								break;
							}
							if($show_room_types_in_search != 'all' && $v_val['available_rooms'] < 1) continue;

                            if($v_val['available_rooms'] < $min_rooms){
                                continue;
                            }

							$room_id = $v_val['id'];

							$room = database_query(str_replace('_KEY_', $room_id, $sql), DATA_AND_ROWS, FIRST_ROW_ONLY);
							if($room[1] > 0){
								$output .= self::DrawWidgetRoomItem($room[0], $v_val['available_rooms'], $hotel_id, $params, $meal_plans, $host, $allow_booking, $arr_property_types);
							}
						}
					}

					$hotel_info = Hotels::GetHotelFullInfo($hotel_id);
					$hotel_images = Hotels::GetHotelsImages($hotel_id);

					// --- MODAL HOTEL --- //
					$h_arr_images = array();
					$h_arr_thumb_images = array();
					if(!empty($hotel_images[0][0]['hotel_image'])){
						$h_arr_images[] = $hotel_images[0][0]['hotel_image'];
						$h_arr_thumb_images[] = $hotel_images[0][0]['hotel_image_thumb'];
					}
					for($i = 1; $i < $hotel_images[1]; $i++){
						if(!empty($hotel_images[0][$i]['item_file'])){
							$h_arr_images[] = $hotel_images[0][$i]['item_file'];
							$h_arr_thumb_images[] = $hotel_images[0][$i]['item_file_thumb'];
						}
					}
					if(empty($h_arr_images)){
						$h_arr_images[] = 'no_image.png';
						$h_arr_thumb_images[] = 'no_image.png';
					}

					$output .= '<div id="myModal-h_'.$hotel_id.'" class="w3-modal modal-two-columns" style="display:none;" onclick="closeModal(\'h_'.$hotel_id.'\')" >'.$nl;
					$output .= '<div class="w3-modal-content" onclick="event.stopPropagation()">'.$nl;
					$output .= '<div class="w3-content" style="max-width:1240px;padding:0;">'.$nl;
					$output .= '<div class="w3-center w3-row w3-black">'.$nl;

					$output .= '<div class="w3-col l8 w3-black">'.$nl;
					foreach($h_arr_images as $image){
						$output .= '<img class="mySlides mySlides-h_'.$hotel_id.'" src="'.$host.'/images/hotels/'.htmlspecialchars($image).'" style="width:100%;" onclick="plusDivs(1,\'h_'.$hotel_id.'\')" alt="'.htmlspecialchars($hotel_info['name']).'">'.$nl;
					}
					if(count($h_arr_images) > 1){
						$output .= '<div class="w3-container w3-display-container">'.$nl;
						$output .= '<p id="caption-h_'.$hotel_id.'"></p>'.$nl;
						$output .= '<span class="w3-display-left w3-btn-floating" onclick="plusDivs(-1,\'h_'.$hotel_id.'\')">&#10094;</span>'.$nl;
						$output .= '<span class="w3-display-right w3-btn-floating" onclick="plusDivs(1,\'h_'.$hotel_id.'\')">&#10095;</span>'.$nl;
						$output .= '</div>'.$nl;
					}
					if(count($h_arr_thumb_images) > 1){
						$output .= '<div style="width:100%;text-align:center;padding-bottom:32px;">'.$nl;
						foreach($h_arr_thumb_images as $i_key => $image){
							$output .= '<div class="w3-col" style="width:50px;height:35px;float:none;display:inline-block;">'.$nl;
							$output .= '<img class="demo demo-h_'.$hotel_id.' w3-opacity w3-hover-opacity-off" src="'.$host.'/images/hotels/'.htmlspecialchars($image).'" style="width:96%;height:96%" onclick="currentDiv('.($i_key + 1).', \'h_'.$hotel_id.'\')" alt="'.htmlspecialchars($hotel_info['name']).'">'.$nl;
							$output .= '</div>'.$nl;
						}
						$output .= '</div>'.$nl;
					}
					$output .= '</div>'.$nl;

					$output .= '<div class="w3-col l4 w3-white">'.$nl;
					$output .= '<span class="w3-text-black w3-xxlarge w3-hover-text-grey w3-container w3-display-topright" onclick="closeModal(\''.$h_key.'\')" style="cursor:pointer;z-index:3;">&times;</span>'.$nl;
					$output .= '<div class="collapse w3-left-align">'.$nl;

					$output .= '<div class="tab-pane fadeempty  w3-left-align">'.$nl;
					$output .= '<div class="clearfix"></div>'.$nl;
					$output .= '<div class="hpadding20">'.$nl;
					$campaning_banner = Campaigns::DrawCampaignSmallBanner($hotel_id, false);
					if(!empty($campaning_banner)){
						$output .= '<div style="margin-right:20px;margin-bottom:20px;">'.$campaning_banner.'</div>';
					}
					$output .= '<h4>'._DESCRIPTION.'</h3>';
					$output .= '</div>'.$nl;
					$output .= '<div class="hpadding20">'.$nl;
					$output .= $hotel_info['description'];
					$output .= '</div>'.$nl;
					$output .= '<div class="clearfix"></div>'.$nl;

					if($hotel_info['distance_center'] > 0 && $hotel_info['name_center_point'] != ''){
						$distance_string = $hotel_info['distance_center'].' '._KILOMETERS_SHORTENED;

						$output .= '<div class="hpadding20">'.$nl;
						$output .= '<h4>'._LOCATION.'</h4>'.$nl;
						$output .= '</div>'.$nl;
						$output .= '<div class="hpadding20">'.$nl;
						$output .= str_replace(array('{name_center_point}', '{distance_center_point}'), array($hotel_info['name_center_point'],$distance_string), FLATS_INSTEAD_OF_HOTELS ? _DISTANCE_OF_FLAT_FROM_CENTER_POINT : _DISTANCE_OF_HOTEL_FROM_CENTER_POINT);
						$output .= '</div>'.$nl;
						$output .= '<div class="clearfix"></div>'.$nl;
					}
					$output .= '<div class="hpadding20">'.$nl;
					$output .= '<h4>'._ROOMS.'</h3>'.$nl;
					$output .= '</div>'.$nl;
					$output .= '<div class="hpadding20">'.$nl;
					$output .= Rooms::DrawRoomsInHotel($hotel_id, false, false, false);
					$output .= '</div>'.$nl;
					$output .= '</div>'.$nl;
					$output .= '</div>'.$nl;

					$output .= '</div>'.$nl;
					$output .= '</div>'.$nl;
					$output .= '</div>'.$nl;
					$output .= '</div>'.$nl;
					$output .= '</div>'.$nl;
					// --- END MODAL HOTEL --- //
				}
				echo self::DrawWidgetJS();
			}

			$output .= $this->DrawPaginationLinks($total_pages, $current_page, $params, false);

            $output .= '<script>
                var dialog = null, table, type, key;

                jQuery(document).ready(function(){
                    var $ = jQuery;

                    $(".form_button_middle").click(function(){
                        var objButton = this;
                        var token = "'.htmlentities(Application::Get('token')).'";
                        var room_id = $(this).data("room-id");
                        var type = $(this).data("type");
                        
                        if($(this).parents("form").length > 0){
                            var arr_input = $(this).parents("form").find("input").toArray();
                            arr_input = arr_input.concat($(this).parents("form").find("select").toArray());
                        }else{
                            var arr_input = $(this).parents("tr").find("input").toArray();
                            arr_input = arr_input.concat($(this).parents("tr").find("select").toArray());
                        }
                        var data = {};
                        for(i = 0, max_count = arr_input.length; i < max_count; i++){
                            val = $(arr_input[i]).val();
                            key = $(arr_input[i]).attr("name");
                            if(key != undefined){
                                data[key] = val;
                            }
                        }

                        data.act = "send";
                        data.type = type;
                        data.is_widget = 1;

						if($(objButton).hasClass("ajax-working")){
							return false;
						}				
						$(objButton).addClass("ajax-working");

                        jQuery.ajax({
                            url: "'.$host.'/ajax/reserve_room.ajax.php",
                            global: false,
                            type: "POST",
                            data: (data),
                            dataType: "json",
                            async: true,
                            error: function(html){
                                console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
                            },
                            success: function(data){
                            	$(objButton).removeClass("ajax-working");
                                var error = data.error !== undefined ? data.error : false;
                                var message = data.message;
                                if(data.widget_html != undefined){
                                    $(".reservationcartdiv").replaceWith(data.widget_html);
                                    $(".reservationcartdiv").addClass("margin-right-zero");
                                }
                                if(message.length > 0){
                                    // dialog
                                    if(error == false){
                                        if(type == "reserve"){
                                            $(objButton).removeClass("green");
                                            $(objButton).addClass("red");
                                            $(objButton).data("type", "remove");
                                            $(objButton).val("'._REMOVE.'");
                                        }else{
                                            $(objButton).removeClass("red");
                                            $(objButton).addClass("green");
                                            $(objButton).data("type", "reserve");
                                            $(objButton).val("'._RESERVE.'");
                                        }
                                    }
                                    if(typeof toastr != "undefined"){
                                        toastr.remove();
                                        if(data.message != ""){
                                            // Init
                                            toastr.options = {
                                              "closeButton": true,
                                              "debug": false,
                                              "newestOnTop": false,
                                              "progressBar": false,
                                              "positionClass": "toast-top-right",
                                              "preventDuplicates": false,
                                              "onclick": null,
                                              "showDuration": "300",
                                              "hideDuration": "1000",
                                              "timeOut": "3000",
                                              "extendedTimeOut": "0",
                                              "showEasing": "swing",
                                              "hideEasing": "linear",
                                              "showMethod": "fadeIn",
                                              "hideMethod": "fadeOut",
                                              "rtl": '.(Application::Get('lang_dir') == 'rtl' ? 'true' : 'false').'
                                            }

                                            if(error){
                                                toastr.error(data.message);
                                            }else{
                                                type == "remove" ? toastr.warning(data.message) : toastr.success(data.message);
                                            }
                                        }
                                    }else{
                                        $("#dialog-message").html(message);
                                        dialog = jQuery( "#dialog-alert" ).dialog({
                                          autoOpen: true,
                                          width: 500,
                                          buttons: {
                                            "'.str_replace('"', '\\"', _BOOK_NOW).'": function(){
                                                window.location.href = "index.php?page=booking";
                                            },
                                            "Ok": function() {
                                              dialog.dialog("close");
                                            }
                                          },
                                          open: function() {
                                            $(".ui-dialog-buttonpane").find("button:contains(\"Ok\")").css("float", "right");
                                            $(".ui-dialog-buttonset").css({"float": "left", "margin-left": "5px", "width":"100%"});
                                          }
                                        });
                                    }
                                }
                            }
                        });
                        return false;
                    });
                });
			</script>
			<div id="dialog-alert" title="'._RESERVATION.'">
				<p><span id="dialog-alert-image" class="ui-icon ui-icon-alert" style="display:none;float:left; margin:2px 5px 0px 0;"></span><span id="dialog-message" style="font-size:14px;"></span></p>
			</div>';

		$output .= '<br>';

		if($draw) echo $output;
		else return $output;
	}

    /**
	 * Draws room item
	 * @param array $room
	 * @param int $available_rooms
	 * @param int $hotel_id
	 * @param array $params
	 * @param array $meal_plans
	 * @param string $host
	 * @param bool $allow_booking
	 * @param $arr_property_types
	 * @return html
	 */
	private static function DrawWidgetRoomItem($room, $available_rooms, $hotel_id, $params, $meal_plans, $host = '', $allow_booking = false, $arr_property_types = array())
	{
		$output = '';
		$nl = "\n";
		$currency_rate = Application::Get('currency_rate');
		$currency_format = get_currency_format();
		$room_id = $room['id'];

		// Key for room modal
		$gal_key = $hotel_id.'_'.$room_id;

		$room_price_result = self::GetRoomPrice($room['id'], $hotel_id, $params, 'array');
		$room_total_price = $room_price_result['total_price'];
		$room_original_price = $room_price_result['original_price'];

		$room_price = (self::GetRoomPriceIncludeNightsDiscount($room_total_price / $params['nights'], $params['nights'], $room_id, $room) * $params['nights']);

		// Prepare array images
		$arr_images = array();
		$arr_thumb_images = array();
		if(!empty($room['room_icon'])){
			$arr_images[] = $room['room_icon'];
			$arr_thumb_images[] = $room['room_icon_thumb'];
		}
		for($i=0; $i < 5; $i++){
			$ind = 'room_picture_'.($i + 1);
			if(isset($room[$ind]) && $room[$ind] != ''){
				$arr_images[] = $room[$ind];
				$arr_thumb_images[] = $room[$ind.'_thumb'];
			}
		}
		if(empty($arr_images)){
			$arr_images[] = 'no_image.png';
			$arr_thumb_images[] = 'no_image.png';
		}

		// prepare facilities array
		$total_facilities = RoomFacilities::GetAllActive();
		$arr_facilities = array();
		foreach($total_facilities[0] as $f_val){
			$arr_facilities[$f_val['id']] = array('code'=>$f_val['code'], 'name'=>$f_val['name'], 'icon'=>$f_val['icon_image']);
		}
		$facilities = isset($room['facilities']) ? unserialize($room['facilities']) : array();

		if(empty($available_rooms)) $rooms_descr = '<span class="gray">('._FULLY_BOOKED.')</span>';
		else if($room['room_count'] > '1' && $available_rooms == '1') $rooms_descr = '<span class="red">('._ROOMS_LAST.')</span>';
		else if($room['room_count'] > '1' && $available_rooms <= '5') $rooms_descr = '<span class="red">('.$available_rooms.' '._ROOMS_LEFT.')</span>';
		else $rooms_descr = '<span class="green">('._AVAILABLE.')</span>';

		$output .= '<div class="eachroomsearchresults" id="book_block_'.$hotel_id.'" >'.$nl;

			/* * * * * DRAW FORM * * * * */
		$output .= '<form action="index.php?page=booking" method="post">'.$nl;
		$output .= draw_hidden_field('lang', Application::Get('lang'), false).$nl;
		$output .= draw_hidden_field('hotel_id', $hotel_id, false).$nl;
		$output .= draw_hidden_field('room_id', $room['id'], false).$nl;
		$output .= draw_hidden_field('from_date', $params['from_date'], false).$nl;
		$output .= draw_hidden_field('to_date', $params['to_date'], false).$nl;
		$output .= draw_hidden_field('nights', $params['nights'], false).$nl;
		$output .= draw_hidden_field('adults', $params['max_adults'], false).$nl;
		$output .= draw_hidden_field('children', $params['max_children'], false).$nl;
		$output .= draw_hidden_field('checkin_year_month', $params['from_year'].'-'.(int)$params['from_month'], false).$nl;
		$output .= draw_hidden_field('checkin_monthday', $params['from_day'], false).$nl;
		$output .= draw_hidden_field('checkout_year_month', $params['to_year'].'-'.(int)$params['to_month'], false).$nl;
		$output .= draw_hidden_field('checkout_monthday', $params['to_day'], false).$nl;
        $output .= draw_hidden_field('lang', Application::Get('lang'), false).$nl;
		$output .= draw_token_field(false).$nl;

		$output .= '<div class="eachroomdiv">'.$nl;

		$output .= '<div class="toplayerwithfloats">'.$nl;

		$output .= '<div class="eachroomtoplayer" style="border-image: initial;">'.$nl;

		// LEFT BLOCK
		// Top line
		$output .= '<div class="toplayertitle">'.$nl;
		$output .= '<div class="roomtitle" title="'.$room['loc_room_type'].'" onclick="openModal(\''.$gal_key.'\');currentDiv(1, \''.$gal_key.'\')">'.$room['room_type'].'</div>'.$nl;
		$output .= '<div class="available-rooms">'.$nl;
		if(empty($available_rooms)) $output .= '<span class="gray">('._FULLY_BOOKED.')</span>';
		else if($room['room_count'] > '1' && $available_rooms == '1') $output .= '<span class="red">('._ROOMS_LAST.')</span>';
		else if($room['room_count'] > '1' && $available_rooms <= '5') $output .= '<span class="red">('.$available_rooms.' '._ROOMS_LEFT.')</span>';
		else $output .= '<span class="green">('._AVAILABLE.')</span>';
		$output .= '</div>'.$nl;
		$output .= '<div class="check-availability" onclick="openModal(\'cal_r'.$room_id.'_h'.$hotel_id.'\');" title="'.htmlentities(_DISPLAYS_AVAILABILITY_CALENDAR).'">'._AVAILABLE_CALENDAR.'</div>'.$nl;
		$output .= '<div class="clearfix"></div>'.$nl;
		$output .= '<div class="roombordertop">'.$nl;
		// Description
		$room_descr = strip_tags($room['loc_room_short_description']);
		$room_descr = mb_strimwidth($room_descr, 0, 350, '...');
		$output .= $room_descr.' [&nbsp;<a href="javascript:void(0);" onclick="openModal(\''.$gal_key.'\');currentDiv(1, \''.$gal_key.'\');">'._MORE.'</a>&nbsp;]'.$nl;
		$output .= '</div>'.$nl; // end roombordertop
		$output .= '</div>'.$nl; // end toplayertitle

		// Image
		$output .= '<div class="toplayeroverflow">'.$nl;
		$output .= '<div class="toplayerthumb">'.$nl;
		$output .= '<img src="'.$host.'/images/rooms/'.$room['room_icon_thumb'].'" onclick="openModal(\''.$gal_key.'\');currentDiv(1, \''.$gal_key.'\');" class="w3-hover-shadow">'.$nl;
		$output .= '</div>'.$nl;

		$output .= '<div class="toplayercontent">'.$nl;

		$output .= '<div class="bottomlayercontent">'.$nl;

		// Hotels
		if(defined('WIDGET_ONE_HOTEL') && WIDGET_ONE_HOTEL){
			//$output .= '<div class="meal_plans_description">[&nbsp;<a href="javascript:void(0);" onclick="openModal(\'h_'.$hotel_id.'\');currentDiv(1, \'h_'.$hotel_id.'\');"  title="'.htmlentities($room['hotel_name']).'">'._MORE.'</a>&nbsp;]</div>'.$nl;
		}else{
			$output .= '<div class="earchroomselectdivs">'.$nl;
			$output .= '<div class="float30">'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : (!empty($arr_property_types[$room['hotel_property_type_id']]) ? $arr_property_types[$room['hotel_property_type_id']] : _HOTEL)).':&nbsp;</div>'.$nl;
			//
			// zzz
			$output .= '<div class="float70">'.$nl;
			$output .= '<a href="javascript:void(0);" class="__meal_plans_description" onclick="openModal(\'h_'.$hotel_id.'\');currentDiv(1, \'h_'.$hotel_id.'\');"  title="'.htmlentities($room['hotel_name']).'">'.$room['hotel_name'].'</a> &nbsp; <img src="'.WIDGET_HOME_URL.'template/images/bigrating-'.(int)$room['hotel_stars'].'.png" class="rating" alt="stars rating" width="60" />'.$nl;
			$output .= '</div>'.$nl;
			$output .= '</div>'.$nl; // end earchroomselectdivs
		}

		// Rooms
		$output .= '<div class="earchroomselectdivs" id="numberofrooms">'.$nl;
		$output .= '<div class="float30">'._ROOMS.':&nbsp;</div>'.$nl;
		$output .= '<div class="float70">';
		$output .= '<div class="select-wrapper">';
		if(!empty($available_rooms)){
			$output .= '<select name="available_rooms" class="available_rooms_ddl" '.($allow_booking ? '' : 'disabled="disabled"').'>';
			$options = '';
			$start_count_rooms = !empty($min_rooms) ? $min_rooms : 1;
			$end_count_rooms = !empty($max_rooms) && $max_rooms < $available_rooms && $max_rooms != '-1' ? $max_rooms : $available_rooms;
			for($i = $start_count_rooms; $i <= $end_count_rooms; $i++){
				$room_price_i = $room_total_price * $i;
				$room_price_i_formatted = Currencies::PriceFormat(($room_price * $i) * $currency_rate, '', '', $currency_format);
				$options .= '<option value="'.$i.'-'.$room_price_i.'" ';
				$options .= ($i == '0') ? 'selected="selected" ' : '';
				$options .= '>'.$i.(($i != 0) ? ' ('.$room_price_i_formatted.')' : '').'</option>';
			}
			$output .= $options.'</select>';
		}
		$output .= '</div>'.$nl; // select-wrapper
		$output .= '</div>'.$nl; // end float70

		$output .= '</div>'.$nl; // end earchroomselectdivs

		// Meal Plans
		$output .= '<div class="earchroomselectdivs" id="mealplans">'.$nl;
		$output .= '<div class="float30">'._MEAL_PLANS.':&nbsp;</div>'.$nl;
		$output .= '<div class="float70">'.$nl;
		$output .= '<div class="select-wrapper">'.$nl;
		if($meal_plans[1] > 0){
			$output .= MealPlans::DrawMealPlansDDL($meal_plans, $currency_rate, $currency_format, $allow_booking, false);
		}
		$output .= '</div>'.$nl;
		$output .= '<span class="meal_plans_description" title="'.htmlentities(_MEAL_DESCRIPTION).'"> <span class="red">*</span>'._PERSON_PER_NIGHT.'</span>'.$nl;
		$output .= '</div>'.$nl; // end float70
		$output .= '</div>'.$nl; // end earchroomselectdivs

		$output .= '</div>'.$nl; // end bottomlayercontent

		$output .= '</div>'.$nl; // end toplayercontent
		$output .= '</div>'.$nl; // end toplayeroverflow

		$output .= '<br>'.$nl;

		// Details price
		$output .= '<div class="check-availability details-prices" onclick="openModal(\'d_p_r'.$room_id.'_h'.$hotel_id.'\');">'._PRICE_DETAILS.'</div>'.$nl;

		// Facilities
		$output .= '<ul class="hotelpreferences2 left">';
			if(is_array($facilities)){
				foreach($facilities as $f_val){
					//if(isset($arr_facilities[$val])) $output .= '<li style="float:left;margin-right:5px;" class="icohp-'.$arr_facilities[$val]['code'].'" title="'.$arr_facilities[$val]['name'].'"></li>';
					if(isset($arr_facilities[$f_val])) $output .= '<li style="margin-right:5px;"'.(empty($arr_facilities[$f_val]['icon']) ? ' class="icohp-'.$arr_facilities[$f_val]['code'].'"' : '').' title="'.htmlentities($arr_facilities[$f_val]['name']).'">'.(!empty($arr_facilities[$f_val]['icon']) ? '<img src="'.$host.'/images/facilities/'.$arr_facilities[$f_val]['icon'].'"/>' : '').'</li>';
				}
			}
		$output .= '</ul>';

		$output .= '</div>'.$nl; // end eachroomtoplayer

		// RIGHT BLOCK
		$output .= '<div class="booknowdiv">'.$nl;
		if(defined('WIDGET_LAYOUT') && WIDGET_LAYOUT == 'layout-1'){
			 if(Reservation::RoomIsReserved($room['id'])){
				 $output .= '<input class="booknowdivbtn form_button_middle red" type="button" data-room-id="'.$room['id'].'" data-type="remove" value="'._REMOVE.'" />';
			 }else{
				 $output .= '<input class="booknowdivbtn form_button_middle green" type="button" data-room-id="'.$room['id'].'" data-type="reserve" value="'._RESERVE.'" />';
			 }
		}else{
			$output .= '<div class="total-price">';
			$output .= '<div class="title">'._TOTAL_PRICE.'</div>';
			if(abs($room_original_price) > 0.01 && $room_price != $room_original_price){
				$output .= '<div class="old-price">'.Currencies::PriceFormat($room_original_price * $currency_rate, '', '', $currency_format).'</div>';
				$output .= '<div class="new-price">'.Currencies::PriceFormat($room_price * $currency_rate, '', '', $currency_format).'</div>';
			}else{
				$output .= '<div class="price">'.Currencies::PriceFormat($room_price * $currency_rate, '', '', $currency_format).'</div>';
			}
			$output .= '<div class="nights">('.$params['nights'].' '.($params['nights'] > 1 ? _NIGHTS : _NIGHT).')</div>';
			$output .= '</div>';
			if(Reservation::RoomIsReserved($room['id'])){
				$output .= '<input class="form_button_middle red" type="submit" data-room-id="'.$room['id'].'" data-value-remove="'._REMOVE.'" data-value-reserve="'._RESERVE.'" data-type="remove" value="'._REMOVE.'" />';
			}else{
				$output .= '<input class="form_button_middle green" type="submit" data-room-id="'.$room['id'].'" data-value-remove="'._REMOVE.'" data-value-reserve="'._RESERVE.'" data-type="reserve" value="'._RESERVE.'" />';
			}
		}
		$output .= '</div>'.$nl; // end booknowdiv

		$output .= '</div>'.$nl; // end toplayerwithfloats

		$output .= '</div>'.$nl; // end eachroomdiv

		$output .= '</form>'.$nl;
		$output .= '</div>';
		$output .= '<div class="offset-2"><hr class="featurette-divider3"></div>';
		$output .= '<div class="clearfix"></div>';

		// --- MODAL ROOMS --- //
		$output .= '<div id="myModal-'.$gal_key.'" class="w3-modal modal-two-columns" style="display:none;" onclick="closeModal(\''.$gal_key.'\')" >'.$nl;
		$output .= '<div class="w3-modal-content" onclick="event.stopPropagation()">'.$nl;
		$output .= '<div class="w3-content" style="max-width:1240px;padding:0;">'.$nl;
		$output .= '<div class="w3-center w3-row w3-black">'.$nl;
		$output .= '<div class="w3-col l8 w3-black">'.$nl;
		foreach($arr_images as $image){
			$output .= '<img class="mySlides mySlides-'.$gal_key.' room-image-slider" src="'.$host.'/images/rooms/'.htmlspecialchars($image).'" style="width:100%;margin:0;" onclick="plusDivs(1,\''.$gal_key.'\')" alt="'.htmlspecialchars($room['room_type']).'">'.$nl;
		}
		$output .= '<div class="w3-container w3-display-container">'.$nl;
		$output .= '<p id="caption-'.$gal_key.'" style="display:none;"></p>'.$nl;
		if(count($arr_images) > 1){
			$output .= '<span class="w3-display-left w3-btn-floating" onclick="plusDivs(-1,\''.$gal_key.'\')">&#10094;</span>'.$nl;
			$output .= '<span class="w3-display-right w3-btn-floating" onclick="plusDivs(1,\''.$gal_key.'\')">&#10095;</span>'.$nl;
		}
		$output .= '</div>'.$nl;
		$output .= '<div style="width:100%;text-align:center;padding-bottom:32px;">'.$nl;
		foreach($arr_thumb_images as $i_key => $image){
			$output .= '<div class="w3-col" style="width:50px;height:35px;float:none;display:inline-block;">'.$nl;
			$output .= '<img class="demo demo-'.$gal_key.' w3-opacity w3-hover-opacity-off" src="'.$host.'/images/rooms/'.htmlspecialchars($image).'" style="width:96%;height:96%" onclick="currentDiv('.($i_key + 1).', \''.$gal_key.'\')" alt="'.htmlspecialchars($room['room_type']).'">'.$nl;
			$output .= '</div>'.$nl;
		}
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;

		$output .= '<div class="w3-col l4 w3-white">'.$nl;
		$output .= '<span class="w3-text-black w3-xxlarge w3-hover-text-grey w3-container w3-display-topright" onclick="closeModal(\''.$gal_key.'\')" style="cursor:pointer;z-index:2;">&times;</span>'.$nl;
		$output .= '<div class="collapse w3-left-align">'.$nl;
		$output .= '<div class="clearfix"></div>'.$nl;
		$output .= '<div class="hpadding20">'.$nl;
		$output .= '<h4>'.$room['room_type'].'</h4>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '<div class="hpadding20">'.$nl;

		$is_active = (isset($room['is_active']) && $room['is_active'] == 1) ? _AVAILABLE : _NOT_AVAILABLE;
		$output .= '<div class="w3-row long-description">'.$room['loc_room_long_description'].'</div>'.$nl;
		$output .= '<div class="clearfix"></div>'.$nl;
		$output .= '<div class="w3-row room-addition-info">'.$nl;
		$output .= '<div class="row"><div class="name">'._COUNT.':</div> <div class="value">'.$room['room_count'].'</div></div>';
		$output .= '<div class="row"><div class="name">'._MAX_ADULTS.':</div> <div class="value">'.$room['max_adults'].'</div></div>';
		$output .= '<div class="row"><div class="name">'._MAX_CHILDREN.':</div> <div class="value">'.$room['max_children'].'</div></div>';
		$output .= '<div class="row"><div class="name">'._MAX_EXTRA_BEDS.':</div> <div class="value">'.$room['max_extra_beds'].'</div></div>';
		$output .= '<div class="row"><div class="name">'._AVAILABILITY.':</div> <div class="value">'.$is_active.'</div></div>';
		if(!empty($room['beds']) || !empty($room['bathrooms']) || $room['room_area'] > 0.1){
			$output .= '<br>';
			$output .= !empty($room['beds']) ? '<div class="row"><div class="name">'._BEDS.':</div> <div class="value">'.$room['beds'].'</div></div>' : '';
			$output .= !empty($room['bathrooms']) ? '<div class="row"><div class="name">'._BATHROOMS.':</div> <div class="value">'.$room['bathrooms'].'</div></div>' : '';
			$output .= $room['room_area'] > 0.1 ? '<div class="row"><div class="name">'._ROOM_AREA.':</div> <div class="value">'.$room['room_area'].' m<sup>2</sup></div></div>' : '';
		}
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '<div class="hpadding20">'.$nl;
		$output .= '<ul class="hotelpreferences2 left">';
			if(is_array($facilities)){
				foreach($facilities as $f_val){
					//if(isset($arr_facilities[$val])) $output .= '<li style="float:left;margin-right:5px;" class="icohp-'.$arr_facilities[$val]['code'].'" title="'.$arr_facilities[$val]['name'].'"></li>';
					if(isset($arr_facilities[$f_val])) $output .= '<li style="margin-right:5px;"'.(empty($arr_facilities[$f_val]['icon']) ? ' class="icohp-'.$arr_facilities[$f_val]['code'].'"' : '').' title="'.htmlentities($arr_facilities[$f_val]['name']).'">'.(!empty($arr_facilities[$f_val]['icon']) ? '<img src="'.$host.'/images/facilities/'.$arr_facilities[$f_val]['icon'].'"/>' : '').'</li>';
				}
			}
		$output .= '</ul>';
		$output .= '</div>'.$nl;
		if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
			$discount_night =  Rooms::DrawRoomPricesNightDiscounts($room);
			if(!empty($discount_night)){
				$output .= '<div class="hpadding20">';
				$output .= $discount_night;
				$output .= '</div>';
			}
		}
		if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
			$discount_guests = Rooms::DrawRoomPricesGuestsDiscounts($room);
			if(!empty($discount_guests)){
				$output .= '<div class="hpadding20">';
				$output .= $discount_guests;
				$output .= '</div>';
			}
		}
		if(ModulesSettings::Get('rooms', 'refund_money') == 'yes'){
			$refund_money = Rooms::GetRoomRefundMoney($room);
			if(!empty($refund_money)){
				$output .= '<div class="hpadding20">';
				$output .= $refund_money;
				$output .= '</div>';
			}
		}
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;
		// --- END MODAL ROOMS --- //

		// --- MODAL CALENDAR --- //
		$output .= self::DrawWidgetOneColumnModal('cal_r'.$room_id.'_h'.$hotel_id, Rooms::DrawOccupancyCalendar($room['id'], false));

		// --- MODAL DETAIL PRICES --- //
		$detail_prices = '';
		$detail_prices .= '<div class="room-prices-table">'.$nl;

		$diff_from_to_date_in_days = round((strtotime($params['to_date']) - strtotime($params['from_date'])) / 86400); // 86400 sec == 1 day
		for($i = 0; $i < $diff_from_to_date_in_days; $i++){
			$detail_prices .= '<div class="pricesdiv" title="'.htmlentities(_RATE_PER_NIGHT).'">'.$nl;
			$detail_prices .= '<div class="dateprice">'.date('d/m',strtotime($params['from_date']) + ($i * 86400)).'</div>'.$nl;
			$date_current_day = date('Y-m-d', strtotime($params['from_date']) + (($i + 1) * 86400));
			$price_per_day =  $room_price_result[$date_current_day] * $currency_rate;
			$detail_prices .= '<div class="originalprice"><div class="pricevertical">'.Currencies::PriceFormat($price_per_day, '', '', $currency_format).'</div></div>'.$nl;
			$detail_prices .= '</div>'.$nl;
		}

		$detail_prices .= '</div>'.$nl;

		$output .= self::DrawWidgetOneColumnModal('d_p_r'.$room_id.'_h'.$hotel_id, $detail_prices);

		return $output;
	}

    /**
	 * Get js for widget
	 * @param int $m_key
	 * @param string $content
	 * @return html
	 */
	public static function DrawWidgetJS()
	{
		$output = '<script>
			var slideIndex = 1;
			var activeModal = "";

			function openModal(i) {
			  activeModal = i;
			  document.getElementById("myModal-" + i).style.display = "block";
			}

			function closeModal(i) {
			  document.getElementById("myModal-" + i).style.display = "none";
			  activeModal = "";
			  slideIndex = 1;
			}

			//showDivs(slideIndex);

			function plusDivs(n, i) {
			  showDivs(slideIndex += n, i);
			}

			function currentDiv(n, i) {
			  showDivs(slideIndex = n, i);
			}

			function showDivs(n, i) {
			  var j;
			  var x = document.getElementsByClassName("mySlides-" + i);
			  var dots = document.getElementsByClassName("demo-" + i);
			  var captionText = document.getElementById("caption-" + i);
			  if (n > x.length) {slideIndex = 1}
			  if (n < 1) {slideIndex = x.length}
			  for (j = 0; j < x.length; j++) {
				 x[j].style.display = "none";
			  }
			  for (j = 0; j < dots.length; j++) {
				 dots[j].className = dots[j].className.replace(" w3-opacity-off active", "");

			  }
			  x[slideIndex-1].style.display = "block";
			  if(typeof dots[slideIndex-1] != "undefined"){
				  dots[slideIndex-1].className += " w3-opacity-off active";
				  captionText.innerHTML = dots[slideIndex-1].alt;
			  }
			}
			//document.onkeypress = function(evt) {
			//  evt = evt || window.event;
			//  if (evt.keyCode == 27 && activeModal != "") {
			//    closeModal(i);
			//  }
			//};
			</script>';
		return $output;
	}

    /**
	 * Get one column modal
	 *	@param int $m_key
	 *	@param string $content
	 * @return html
	 */
	public static function DrawWidgetOneColumnModal($m_key, $content = '')
	{
		$nl = "\n";
		$output  = '<div id="myModal-'.$m_key.'" class="w3-modal" style="display:none;" onclick="closeModal(\''.$m_key.'\')" >'.$nl;
		$output .= '<div class="w3-modal-content" onclick="event.stopPropagation()">'.$nl;
		$output .= '<div class="w3-content" style="max-width:1240px">'.$nl;
		$output .= '<span class="w3-text-black w3-xxlarge w3-hover-text-grey w3-container w3-display-topright" onclick="closeModal(\''.$m_key.'\')" style="cursor:pointer">&times;</span>'.$nl;
		$output .= '<div class="hpadding20">'.$nl;
		$output .=  $content;
		$output .= '<div class="clearfix"></div>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;
		$output .= '</div>'.$nl;

		return $output;
	}

    /**
	 * Get variable arrAvailableRooms
	 * @return array
	 */
	public function GetAvailableRooms()
	{
		return $this->arrAvailableRooms;
	}

    /**
	 * Draws room description
	 * @param $room_id
	 * @param $back_button
	 */
	public static function DrawRoomDescription($room_id, $back_button = true)
	{
		global $objLogin;

		$lang = Application::Get('lang');
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$hotels_count = Hotels::HotelsCount();
		$output = '';

		$sql = 'SELECT
					r.id,
					r.room_type,
					r.hotel_id,
					r.room_count,
					r.max_adults,
					r.max_children,
					r.beds,
					r.bathrooms,
					r.room_area,
					r.default_price,
					r.facilities,
					r.room_icon,
					r.room_icon_thumb,
					r.room_picture_1,
					r.room_picture_1_thumb,
					r.room_picture_2,
					r.room_picture_2_thumb,
					r.room_picture_3,
					r.room_picture_3_thumb,
					r.room_picture_4,
					r.room_picture_4_thumb,
					r.room_picture_5,
					r.room_picture_5_thumb,
					r.is_active,
					rd.room_type as loc_room_type,
					rd.room_long_description as loc_room_long_description,
					hd.name as hotel_name,
                    h.property_type_id
				FROM '.TABLE_ROOMS.' r
					INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id
					INNER JOIN '.TABLE_HOTELS.' h ON r.hotel_id = h.id
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id
				WHERE
					'.(ADMIN_CAN_SEE_INACTIVE_PROPERTIES && $objLogin->IsLoggedInAs('owner','mainadmin') ? '' : 'h.is_active = 1 AND').'
					r.id = '.(int)$room_id.' AND
					hd.language_id = \''.$lang.'\' AND
					rd.language_id = \''.$lang.'\'';

		$room_info = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);

		$room_type 		  = isset($room_info['loc_room_type']) ? $room_info['loc_room_type'] : '';
		$room_long_description = isset($room_info['loc_room_long_description']) ? $room_info['loc_room_long_description'] : '';
		$facilities       = isset($room_info['facilities']) ? unserialize($room_info['facilities']) : array();
		$room_count       = isset($room_info['room_count']) ? $room_info['room_count'] : '';
		$max_adults       = isset($room_info['max_adults']) ? $room_info['max_adults'] : '';
		$max_children  	  = isset($room_info['max_children']) ? $room_info['max_children'] : '';
		$room_area  	  = isset($room_info['room_area']) ? $room_info['room_area'] : '';
		$beds             = isset($room_info['beds']) ? $room_info['beds'] : '';
		$bathrooms        = isset($room_info['bathrooms']) ? $room_info['bathrooms'] : '';
		$default_price    = isset($room_info['default_price']) ? $room_info['default_price'] : '';
		$room_icon        = isset($room_info['room_icon']) ? $room_info['room_icon'] : '';
		$room_picture_1	  = isset($room_info['room_picture_1']) ? $room_info['room_picture_1'] : '';
		$room_picture_2	  = isset($room_info['room_picture_2']) ? $room_info['room_picture_2'] : '';
		$room_picture_3	  = isset($room_info['room_picture_3']) ? $room_info['room_picture_3'] : '';
		$room_picture_4	  = isset($room_info['room_picture_4']) ? $room_info['room_picture_4'] : '';
		$room_picture_5	  = isset($room_info['room_picture_5']) ? $room_info['room_picture_5'] : '';
		$room_picture_1_thumb = isset($room_info['room_picture_1_thumb']) ? $room_info['room_picture_1_thumb'] : '';
		$room_picture_2_thumb = isset($room_info['room_picture_2_thumb']) ? $room_info['room_picture_2_thumb'] : '';
		$room_picture_3_thumb = isset($room_info['room_picture_3_thumb']) ? $room_info['room_picture_3_thumb'] : '';
		$room_picture_4_thumb = isset($room_info['room_picture_4_thumb']) ? $room_info['room_picture_4_thumb'] : '';
		$room_picture_5_thumb = isset($room_info['room_picture_5_thumb']) ? $room_info['room_picture_5_thumb'] : '';
		$hotel_name       = isset($room_info['hotel_name']) ? $room_info['hotel_name'] : '';
		$is_active		  = (isset($room_info['is_active']) && $room_info['is_active'] == 1) ? '<span class="green">'._AVAILABLE.'</span>' : '<span class="red">'._NOT_AVAILABLE.'</span>';
        $property_type_id = isset($room_info['property_type_id']) ? $room_info['property_type_id'] : '1';

		if(count($room_info) > 0){

			// prepare facilities array
			$total_facilities = RoomFacilities::GetAllActive();
			$arr_facilities = array();
			foreach($total_facilities[0] as $key => $val){
				$arr_facilities[$val['id']] = $val['name'];
			}

			$output .= '<table border="0" class="room_description">';
			$output .= '<tr valign="top">';
			$output .= '<td width="200px">';
			///$output .= '  <img class="room_icon" src="images/rooms/'.$room_icon.'" width="165px" alt="icon" />';
			if($room_picture_1 == '' && $room_picture_2 == '' && $room_picture_3 == '' && $room_picture_4 == '' && $room_picture_5 == ''){
				$output .= '<img class="room_icon" src="images/rooms/no_image.png" width="165px" alt="icon" />';
			}
			if($room_picture_1 != '') $output .= ' <a href="'.APPHP_BASE.'images/rooms/'.$room_picture_1.'" rel="lyteshow" title="'._IMAGE.' 1"><img class="room_icon" src="'.APPHP_BASE.'images/rooms/'.$room_picture_1_thumb.'" width="165px" height="140px" alt="icon" /></a><br />';
			if($room_picture_2 != '') $output .= ' <a href="'.APPHP_BASE.'images/rooms/'.$room_picture_2.'" rel="lyteshow" title="'._IMAGE.' 2"><img class="room_icon" src="'.APPHP_BASE.'images/rooms/'.$room_picture_2_thumb.'" width="165px" height="140px" alt="icon" /></a><br />';
			if($room_picture_3 != '') $output .= ' <a href="'.APPHP_BASE.'images/rooms/'.$room_picture_3.'" rel="lyteshow" title="'._IMAGE.' 3"><img class="room_icon" src="'.APPHP_BASE.'images/rooms/'.$room_picture_3_thumb.'" width="165px" height="140px" alt="icon" /></a><br />';
			if($room_picture_4 != '') $output .= ' <a href="'.APPHP_BASE.'images/rooms/'.$room_picture_4.'" rel="lyteshow" title="'._IMAGE.' 4"><img class="room_icon" src="'.APPHP_BASE.'images/rooms/'.$room_picture_4_thumb.'" width="79px" height="67px" alt="icon" /></a>';
			if($room_picture_5 != '') $output .= ' <a href="'.APPHP_BASE.'images/rooms/'.$room_picture_5.'" rel="lyteshow" title="'._IMAGE.' 5"><img class="room_icon" src="'.APPHP_BASE.'images/rooms/'.$room_picture_5_thumb.'" width="79px" height="67px" alt="icon" /></a><br />';
			$output .= '</td>';
			$output .= '<td>';
				$output .= '<table class="room_description_inner">';
				$output .= '<tr><td>';
					$output .= '<h4>'.$room_type.'&nbsp;';
					if($hotels_count > 1) $output .= ' ('.prepare_link((FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels'), (FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid'), $room_info['hotel_id'], $hotel_name, $hotel_name, '', _CLICK_TO_VIEW).')';
					$output .= '</h4>';
				$output .= '</td></tr>';

				$output .= '<tr><td>'.$room_long_description.'</td></tr>';

				$output .= '<tr><td><b>'._FACILITIES.':</b></td></tr>';
				$output .= '<tr><td>';
				$output .= '<ul class="facilities">';
				if(is_array($facilities)){
					foreach($facilities as $key => $val){
						if(isset($arr_facilities[$val])) $output .= '<li>'.$arr_facilities[$val].'</li>';
					}
				}
				$output .= '</ul>';
				$output .= '</td></tr>';

				$output .= '<tr><td>&nbsp;</td></tr>';
				$output .= '<tr><td><b>'._COUNT.':</b> '.$room_count.'</td></tr>';
				$output .= '<tr><td><b>'._ROOM_AREA.':</b> '.number_format($room_area, 1, '.', '').' m<sup>2</sup></td></tr>';
				$output .= '<tr><td><b>'._MAX_ADULTS.':</b> '.$max_adults.'</td></tr>';
				if(!empty($beds)) $output .= '<tr><td><b>'._BEDS.':</b> '.$beds.'</td></tr>';
				if(!empty($bathrooms)) $output .= '<tr><td><b>'._BATHROOMS.':</b> '.$bathrooms.'</td></tr>';

				$output .= '<tr><td><b>'._AVAILABILITY.':</b> '.$is_active.'</td></tr>';
				$output .= '</tr>';
				$output .= '</table>';
			$output .= '</td>';
			$output .= '</tr>';

			// draw prices table
			$output .= '<tr><td colspan="2" nowrap="nowrap" height="5px"><td></tr>';
			$output .= '<tr><td colspan="2"><h4>'._PRICES.'</h4><td></tr>';
			$output .= '<tr><td colspan="2">'.self::GetRoomPricesTable($room_id).'<td></tr>';
			$output .= '<tr><td colspan="2" nowrap="nowrap" height="10px"><td></tr>';

			if($back_button){
				if(!$objLogin->IsLoggedInAsAdmin()){
					if(Modules::IsModuleInstalled('booking')){
						if(ModulesSettings::Get('booking', 'show_reservation_form') == 'yes'){
							$output .= '<tr><td colspan="2"><h4>'._RESERVATION.'</h4><td></tr>';
							$output .= '<tr><td colspan="2">'.self::DrawSearchAvailabilityBlock(false, $room_id, '', $max_adults, $max_children, 'room-inline', '', '', false, true, true, $property_type_id).'<td></tr>';
						}
					}
				}
			}
			$output .= '</table>';
			$output .= '<br>';

		}else{
			$output .= draw_important_message(_WRONG_PARAMETER_PASSED, false);
		}

		echo $output;
	}

	/**
	 * Get room price for a certain period of time
	 * @param $room_id
	 * @param $hotel_id
	 * @param $params
	 * @param $return_type
	 * @param $search_suggestion
	 * @param $room_count
	 */
	public static function GetRoomPrice($room_id, $hotel_id = '', $params = array(), $return_type = 'scalar', $search_suggestion = false, $room_count = 0)
	{
		// improve: how to make it takes defult price if not found another ?
		// make check periods for 2, 3 days?
		$debug = false;

		$rooms_count = isset($params['rooms']) ? $params['rooms'] : 1;

		if($search_suggestion){
            $date_from = $params['from_year'][$room_count].'-'.self::ConvertToDecimal($params['from_month'][$room_count]).'-'.self::ConvertToDecimal($params['from_day'][$room_count]);
            $date_to = ($params['to_year'][$room_count]).'-'.self::ConvertToDecimal($params['to_month'][$room_count]).'-'.self::ConvertToDecimal($params['to_day'][$room_count]);
        }else{
            $date_from = $params['from_year'].'-'.self::ConvertToDecimal($params['from_month']).'-'.self::ConvertToDecimal($params['from_day']);
            $date_to = $params['to_year'].'-'.self::ConvertToDecimal($params['to_month']).'-'.self::ConvertToDecimal($params['to_day']);
        }

		$room_default_price = self::GetRoomDefaultPrice($room_id);
		$arr_week_default_price = self::GetRoomWeekDefaultPrice($room_id);
		$nights = nights_diff($date_from, $date_to);

		// Calculate available discounts for specific period of time
		$arr_global_discounts = array();
		$arr_standard_discounts = Campaigns::GetCampaignInfo('', $date_from, $date_to, 'standard');
		///dbug($arr_standard_discounts);

		$total_price = '0';
		$total_price_array = array('total_price' => 0, 'original_price' => 0);
		$offset = 0;
		while($date_from < $date_to){
			$curr_date_from = $date_from;

			$offset++;
            if($search_suggestion){
                $current = getdate(mktime(0,0,0,$params['from_month'][$room_count],$params['from_day'][$room_count]+$offset,$params['from_year'][$room_count]));
            }else{
                $current = getdate(mktime(0,0,0,$params['from_month'],$params['from_day']+$offset,$params['from_year']));
            }
			$date_from = $current['year'].'-'.self::ConvertToDecimal($current['mon']).'-'.self::ConvertToDecimal($current['mday']);

			$curr_date_to = $date_from;
			if($debug) echo '<br> '.$hotel_id.' / '.$room_id.' ('.$curr_date_from.' ... '.$curr_date_to.') ';

			$sql = 'SELECT
						r.id,
						r.default_price,
						r.discount_night_type,
						r.discount_night_3,
						r.discount_night_4,
						r.discount_night_5,
						r.discount_guests_3,
						r.discount_guests_4,
						r.discount_guests_5,
						rp.adults,
						rp.children,
						rp.mon,
						rp.tue,
						rp.wed,
						rp.thu,
						rp.fri,
						rp.sat,
						rp.sun,
						rp.sun,
						rp.is_default
					FROM '.TABLE_ROOMS.' r
						INNER JOIN '.TABLE_ROOMS_PRICES.' rp ON r.id = rp.room_id
					WHERE
						r.id = '.(int)$room_id.' AND
						rp.adults >= '.(int)$params['max_adults'].' AND
						rp.children >= '.(int)$params['max_children'].' AND 
						(
							(rp.date_from <= \''.$curr_date_from.'\' AND rp.date_to = \''.$curr_date_from.'\') OR
							(rp.date_from <= \''.$curr_date_from.'\' AND rp.date_to >= \''.$curr_date_to.'\')
						) AND
						rp.is_default = 0
					ORDER BY rp.adults ASC, rp.children ASC';

			$room_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($room_info[1] > 0){
				$arr_week_price = $room_info[0];

				// calculate total sum, according to week day prices
				$start = $current_date = strtotime($curr_date_from);
				$end = strtotime($curr_date_to);
				while($current_date < $end) {
					// take default weekday price if weekday price is empty
					if(empty($arr_week_price[strtolower(date('D', $current_date))])){
						if($debug) echo '-'.$arr_week_default_price[strtolower(date('D', $current_date))];
						$room_price = $arr_week_default_price[strtolower(date('D', $current_date))];
					}else{
						if($debug) echo '='.$arr_week_price[strtolower(date('D', $current_date))];
						$room_price = $arr_week_price[strtolower(date('D', $current_date))];
					}
					$room_price_original = $room_price;

					// Check if there is available discount
					// Discount for group of guests (apply by room)
					$hotel_ind_id = null;
					if(isset($arr_standard_discounts[$curr_date_from][$hotel_id]) && $arr_standard_discounts[$curr_date_from][$hotel_id]['apply_per'] == '0'){
						if($params['max_adults'] >= $arr_standard_discounts[$curr_date_from][$hotel_id]['minimum_adults'] && $params['max_adults'] <= $arr_standard_discounts[$curr_date_from][$hotel_id]['maximum_adults']){
							$hotel_ind_id = $hotel_id;
						}
					}elseif(isset($arr_standard_discounts[$curr_date_from][0]) && $arr_standard_discounts[$curr_date_from][0]['apply_per'] == '0'){
						if($params['max_adults'] >= $arr_standard_discounts[$curr_date_from][0]['minimum_adults'] && $params['max_adults'] <= $arr_standard_discounts[$curr_date_from][0]['maximum_adults']){
							$hotel_ind_id = 0;
						}
					}

					// Discount calculation
					if($hotel_ind_id !== null){
						$discount_type = isset($arr_standard_discounts[$curr_date_from][$hotel_ind_id]['discount_type'])
							? $arr_standard_discounts[$curr_date_from][$hotel_ind_id]['discount_type']
							: '1';
						$discount_value = isset($arr_standard_discounts[$curr_date_from][$hotel_ind_id]['discount_value'])
							? $arr_standard_discounts[$curr_date_from][$hotel_ind_id]['discount_value']
							: (isset($arr_standard_discounts[$curr_date_from][0]['discount_value']) ? $arr_standard_discounts[$curr_date_from][0]['discount_value'] : '');

						if($discount_value != ''){
							$room_price = ($discount_type == '1' ? ($room_price * (1 - ($discount_value / 100))) : ($room_price - $discount_value));
							if($debug) echo ' after discount '.($discount_type == '1' ? $discount_value.'%= ' : $discount_value.'= ').$room_price;
						}
					}

					$total_price += $room_price;
					$total_price_array['total_price'] += $room_price;
					$total_price_array['original_price'] += $room_price_original;

					$current_date = strtotime('+1 day', $current_date);
				}
			}else{
				// Add default (standard) price
				if($debug) echo ' => '.$arr_week_default_price[strtolower(date('D', strtotime($curr_date_from)))];
				$t_price = isset($arr_week_default_price[strtolower(date('D', strtotime($curr_date_from)))]) ? $arr_week_default_price[strtolower(date('D', strtotime($curr_date_from)))] : 0;
				if(!empty($t_price)) $room_price = $t_price;
				else $room_price = $room_default_price;

				$room_price_original = $room_price;

				// Check if there is available discount
				// Discount for group of guests (apply by room)
				$hotel_ind_id = null;
				if(isset($arr_standard_discounts[$curr_date_from][$hotel_id]) && $arr_standard_discounts[$curr_date_from][$hotel_id]['apply_per'] == '0'){
					if($params['max_adults'] >= $arr_standard_discounts[$curr_date_from][$hotel_id]['minimum_adults'] && $params['max_adults'] <= $arr_standard_discounts[$curr_date_from][$hotel_id]['maximum_adults']){
						$hotel_ind_id = $hotel_id;
					}
				}elseif(isset($arr_standard_discounts[$curr_date_from][0]) && $arr_standard_discounts[$curr_date_from][0]['apply_per'] == '0'){
					if($params['max_adults'] >= $arr_standard_discounts[$curr_date_from][0]['minimum_adults'] && $params['max_adults'] <= $arr_standard_discounts[$curr_date_from][0]['maximum_adults']){
						$hotel_ind_id = 0;
					}
				}

				// Discount calculation
				if($hotel_ind_id !== null){
					$discount_type = isset($arr_standard_discounts[$curr_date_from][$hotel_ind_id]['discount_type'])
						? $arr_standard_discounts[$curr_date_from][$hotel_ind_id]['discount_type']
						: '1';
					$discount_value = isset($arr_standard_discounts[$curr_date_from][$hotel_ind_id]['discount_value'])
						? $arr_standard_discounts[$curr_date_from][$hotel_ind_id]['discount_value']
						: (isset($arr_standard_discounts[$curr_date_from][0]['discount_value']) ? $arr_standard_discounts[$curr_date_from][0]['discount_value'] : '');

					if($discount_value != ''){
						$room_price = ($discount_type == '1' ? ($room_price * (1 - ($discount_value / 100))) : ($room_price - $discount_value));
						if($debug) echo ' after discount '.($discount_type == '1' ? $discount_value.'%= ' : $discount_value.'= ').$room_price;
					}
				}

				$total_price += $room_price;
				$total_price_array['total_price'] += $room_price;
				$total_price_array['original_price'] += $room_price_original;
			}
			$total_price_array[date('Y-m-d', strtotime($date_from))] = $room_price;
		}

		return ($return_type == 'array') ? $total_price_array : $total_price;
	}

	/**
	 * Get room extra beds price for a certain period of time
	 * @param $room_id
	 * @param $params
	 */
	public static function GetRoomExtraBedsPrice($room_id, $params)
	{
		global $SQL_CASHE;

		if(OPTIMIZE_SQL){
			if(!empty($SQL_CASHE['room_extra_bed_price'][$room_id])){
				return $SQL_CASHE['room_extra_bed_price'][$room_id];
			}
		}

		$extra_bed_price = '0';

		$sql = 'SELECT
					r.id,
					r.id,
					rp.extra_bed_charge
				FROM '.TABLE_ROOMS.' r
					INNER JOIN '.TABLE_ROOMS_PRICES.' rp ON r.id = rp.room_id
				WHERE
					r.id = '.(int)$room_id.' AND
					(
						(
							rp.is_default = 0 AND 
							rp.adults >= '.(int)$params['max_adults'].' AND
							rp.children >= '.(int)$params['max_children'].' AND 
							( (rp.date_from <= \''.$params['from_date'].'\' AND rp.date_to = \''.$params['from_date'].'\') OR
							  (rp.date_from <= \''.$params['from_date'].'\' AND rp.date_to >= \''.$params['to_date'].'\')
							) 						
						)
						OR
						(
							rp.is_default = 1
						)
					)
				ORDER BY rp.adults ASC, rp.children ASC, rp.is_default ASC';
		$room_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($room_info[1] > 0){
			$extra_bed_price = $room_info[0]['extra_bed_charge'];
		}

		if(OPTIMIZE_SQL){
			$SQL_CASHE['room_extra_bed_price'][$room_id] = $extra_bed_price;
		}

		return $extra_bed_price;
	}

	/**
	 * Returns room default price
	 * @param $room_id
	 */
	private static function GetRoomDefaultPrice($room_id)
	{
		global $SQL_CASHE;

		if(OPTIMIZE_SQL){
			if(!empty($SQL_CASHE['room_default_price'][$room_id])){
				return $SQL_CASHE['room_default_price'][$room_id];
			}
		}

		$sql = 'SELECT
					r.id,
					r.default_price,
					rp.mon,
					rp.tue,
					rp.wed,
					rp.thu,
					rp.fri,
					rp.sat,
					rp.sun,
					rp.sun,
					rp.is_default
				FROM '.TABLE_ROOMS.' r
					INNER JOIN '.TABLE_ROOMS_PRICES.' rp ON r.id = rp.room_id
				WHERE
					r.id = '.(int)$room_id.' AND
					rp.is_default = 1';

		$room_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		$result = 0;
		if($room_info[1] > 0){
			$result = isset($room_info[0]['mon']) ? $room_info[0]['mon'] : 0;
		}else{
			$result = isset($room_info[0]['default_price']) ? $room_info[0]['default_price'] : 0;
		}

		if(OPTIMIZE_SQL){
			$SQL_CASHE['room_default_price'][$room_id] = $result;
		}

		return $result;
	}

	/**
	 * Returns room default price
     * @param $room
     * @param $params
	 */
	private static function GetRoomLowestPrice($room = array(), $params = array())
	{
		$lower_price = 0;

		$room_id = ! empty($room['id']) ? (int)$room['id'] : 0;

		if(OPTIMIZE_SQL){
			if(!empty($SQL_CASHE['room_lowest_price'][$room_id])){
				return $SQL_CASHE['room_lowest_price'][$room_id];
			}
		}

        $sql = 'SELECT 
                COUNT(*) as cnt,
                MIN(mon) as min_mon,
                MIN(tue) as min_tue,
                MIN(wed) as min_wed,
                MIN(thu) as min_thu,
                MIN(fri) as min_fri,
                MIN(sat) as min_sat,
                MIN(sun) as min_sun
            FROM '.TABLE_ROOMS_PRICES.' 
            WHERE '.TABLE_ROOMS_PRICES.'.room_id = '.$room_id.' AND
                '.TABLE_ROOMS_PRICES.'.date_from <= \''.date('Y-m-d').'\' AND '.TABLE_ROOMS_PRICES.'.date_to >= \''.date('Y-m-d').'\'';
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

        if($result[1] == 0 || $result[0]['cnt'] == 0){
            $sql = 'SELECT
                    MIN(mon) as min_mon,
                    MIN(tue) as min_tue,
                    MIN(wed) as min_wed,
                    MIN(thu) as min_thu,
                    MIN(fri) as min_fri,
                    MIN(sat) as min_sat,
                    MIN(sun) as min_sun
                FROM '.TABLE_ROOMS_PRICES.' 
                WHERE '.TABLE_ROOMS_PRICES.'.room_id = '.$room_id.' AND
                    ('.TABLE_ROOMS_PRICES.'.is_default = 1)';
            $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        }

        if($result[1] > 0){
            $lower_price = $result[0]['min_mon'];
            $arr_week = array('tue','wed','thu','fri','sat','sun');
            $select_day = 'mon';
            foreach($arr_week as $day){
                if($lower_price > $result[0]['min_'.$day]){
                    $lower_price = $result[0]['min_'.$day];
                    $select_day = $day;
                }
            }
            if(!empty($params)){
                $hotel_id = self::GetRoomInfo($room_id, 'hotel_id');
                $unix_from_time = mktime(0,0,0,$params['from_month'],$params['from_day'] + 1,$params['from_year']);
                $params['to_day'] = date('d', $unix_from_time);
                $params['to_month'] = date('m', $unix_from_time);
                $params['to_year'] = date('Y', $unix_from_time);
                $room_price_result = self::GetRoomPrice($room_id, $hotel_id, $params, 'array');
				$room_price_result['total_price'] = self::GetRoomPriceIncludeNightsDiscount($room_price_result['total_price'], $params['nights'], $room_id, $room);

				$lower_price = $room_price_result['total_price'];
			}
        }

		if(OPTIMIZE_SQL){
			$SQL_CASHE['room_lowest_price'][$room_id] = $lower_price;
		}

		return $lower_price;
	}

	/**
	 * Returns room week default price
	 * @param $room_id
	 */
	private static function GetRoomWeekDefaultPrice($room_id)
	{
		global $SQL_CASHE;

		if(OPTIMIZE_SQL){
			if(!empty($SQL_CASHE['room_week_default_price'][$room_id])){
				return $SQL_CASHE['room_week_default_price'][$room_id];
			}
		}

		$sql = 'SELECT
					r.id,
					r.default_price,
					rp.mon,
					rp.tue,
					rp.wed,
					rp.thu,
					rp.fri,
					rp.sat,
					rp.sun,
					rp.sun,
					rp.is_default
				FROM '.TABLE_ROOMS.' r
					INNER JOIN '.TABLE_ROOMS_PRICES.' rp ON r.id = rp.room_id
				WHERE
					r.id = '.(int)$room_id.' AND
					rp.is_default = 1';
		$room_default_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

		$result = array();
		if($room_default_info[1] > 0){
			$result = $room_default_info[0];
		}

		if(OPTIMIZE_SQL){
			$SQL_CASHE['room_week_default_price'][$room_id] = $result;
		}

		return $result;
	}

	/**
	 * Returns room availability for month
	 * @param $arr_rooms
	 * @param $year
	 * @param $month
	 * @param $day
	 */
	public static function GetRoomAvalibilityForWeek($arr_rooms, $year, $month, $day)
	{
		//echo '$year, $month, $day';
		$end_date = date('Y-m-d', strtotime('+7 day', strtotime($year.'-'.$month.'-'.$day)));
		$end_date = explode('-', $end_date);
		$year_end = $end_date['0'];
		$month_end = $end_date['1'];
		$day_end = $end_date['2'];

		$today = date('Ymd');
		$today_month = date('Ym');

		for($i=0; $i<count($arr_rooms); $i++){
			$arr_rooms[$i]['availability'] = array('01'=>0, '02'=>0, '03'=>0, '04'=>0, '05'=>0, '06'=>0, '07'=>0, '08'=>0, '09'=>0, '10'=>0, '11'=>0, '12'=>0, '13'=>0, '14'=>0, '15'=>0,
										           '16'=>0, '17'=>0, '18'=>0, '19'=>0, '20'=>0, '21'=>0, '22'=>0, '23'=>0, '24'=>0, '25'=>0, '26'=>0, '27'=>0, '28'=>0, '29'=>0, '30'=>0, '31'=>0);
			// exit if we in the past
			if($today_month > $year.$month) continue;

			// fill array with rooms availability
			// ------------------------------------
			$sql = 'SELECT * FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE room_id = '.(int)$arr_rooms[$i]['id'].' AND m = '.(int)$month;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				for($d = (int)$day; (($d <= (int)$day+7) && ($d <= 31)); $d ++){
					$arr_rooms[$i]['availability'][self::ConvertToDecimal($d)] = (int)$result[0]['d'.$d];
				}
			}

			// fill array with rooms availability
			// ------------------------------------
			if($month_end != $month){
				$sql = 'SELECT * FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE room_id = '.(int)$arr_rooms[$i]['id'].' AND m = '.(int)$month_end;
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					for($d = 1; ($d <= (int)$day_end); $d ++){
						$arr_rooms[$i]['availability'][self::ConvertToDecimal($d)] = (int)$result[0]['d'.$d];
					}
				}
			}
		}

		///echo '<pre>';
		///print_r($arr_rooms[0]);
		///echo '</pre>';

		return $arr_rooms;
	}

	/**
	 * Returns room availability for month
	 * @param $arr_rooms
	 * @param $year
	 * @param $month
	 */
	public static function GetRoomAvalibilityForMonth($arr_rooms, $year, $month)
	{
		global $SQL_CASHE;

		if(OPTIMIZE_SQL){
			if(!empty($SQL_CASHE['rooms_availability_for_month'][$year.'-'.$month])){
				return $SQL_CASHE['rooms_availability_for_month'][$year.'-'.$month];
			}
		}

		$today = date('Ymd');
		$today_year_month = date('Ym');
		$today_year = date('Y');

		for($i=0; $i<count($arr_rooms); $i++){
			$arr_rooms[$i]['availability'] = array('01'=>0, '02'=>0, '03'=>0, '04'=>0, '05'=>0, '06'=>0, '07'=>0, '08'=>0, '09'=>0, '10'=>0, '11'=>0, '12'=>0, '13'=>0, '14'=>0, '15'=>0,
										           '16'=>0, '17'=>0, '18'=>0, '19'=>0, '20'=>0, '21'=>0, '22'=>0, '23'=>0, '24'=>0, '25'=>0, '26'=>0, '27'=>0, '28'=>0, '29'=>0, '30'=>0, '31'=>0);
			// exit if we in the past
			if($today_year_month > $year.$month) continue;

			// fill array with rooms availability
			// ------------------------------------
			if(isset($arr_rooms[$i]['id'])){
				$sql = 'SELECT *
						FROM '.TABLE_ROOMS_AVAILABILITIES.'
				        WHERE room_id = '.(int)$arr_rooms[$i]['id'].' AND
							  y = '.(($today_year == $year) ? '0' : '1').' AND	
						      m = '.(int)$month;
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					for($day = 1; $day <= 31; $day ++){
						$arr_rooms[$i]['availability'][self::ConvertToDecimal($day)] = (int)$result[0]['d'.$day];
					}
				}
			}
		}

		if(OPTIMIZE_SQL){
			$SQL_CASHE['rooms_availability_for_month'][$year.'-'.$month] = $arr_rooms;
		}

		//echo '<pre>';
		//print_r($arr_rooms);
		//echo '</pre>';

		return $arr_rooms;
	}

	/**
	 * Returns room default availability
	 * Looks for the minimum availabel rooms in selected period
	 * @param $room_id
	 * @param $checkin_date
	 * @param $checkout_date
	 * @param $avail_rooms
	 * @return int
	 */
	public static function CheckAvailabilityForPeriod($room_id, $checkin_date, $checkout_date, $avail_rooms = 0)
	{
		$available_rooms = $avail_rooms;
		$available_until_approval = ModulesSettings::Get('booking', 'available_until_approval');

		// Calculate total sum, according to week day prices
		$current_date = strtotime($checkin_date);
		$current_year = date('Y');
		$next_year = date('Y')+1;
		$end = strtotime($checkout_date);
		$m_old = '';

		/// [16.09.2018] IN TEST - seems it reduces booked rooms from the rest of rooms (already available)!!!!
		/// So it make double reduce !!!
//		if(OPTIMIZE_SQL){
//			// Prepare array with booked rooms in selected period
//			$sql = 'SELECT '.TABLE_BOOKINGS_ROOMS.'.rooms, checkin, checkout
//					FROM '.TABLE_BOOKINGS.'
//						INNER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
//					WHERE
//						('.(($available_until_approval == 'yes') ? '' : TABLE_BOOKINGS.'.status = 2 OR ').' '.TABLE_BOOKINGS.'.status = 3) AND
//						'.TABLE_BOOKINGS_ROOMS.'.room_id = '.(int)$room_id.' AND
//						checkin >= "'.strtotime($checkin_date).'"';
//			$result_reservations = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
//			$reservations_arr = array();
//			for($i=0; $i<$result_reservations[1];$i++) {
//				$period = new DatePeriod(new DateTime($result_reservations[0][$i]['checkin']), new DateInterval('P1D'), new DateTime($result_reservations[0][$i]['checkout']));
//				foreach ($period as $key => $value) {
//					//echo '<br>== '.$value->format('Y-m-d');
//					if(isset($reservations_arr[$value->format('Y-m-d')])){
//						$reservations_arr[$value->format('Y-m-d')] += $result_reservations[0][$i]['rooms'];
//					}else{
//						$reservations_arr[$value->format('Y-m-d')] = $result_reservations[0][$i]['rooms'];
//					}
//				}
//			}
//		}

		while($current_date < $end) {
			$y = date('Y', $current_date);
			$m = date('m', $current_date);
			$d = date('d', $current_date);

			$search_suggestion = true;
			if($m_old != $m){
				$sql = 'SELECT * 
					FROM '.TABLE_ROOMS_AVAILABILITIES.' ra
					WHERE ra.room_id = '.(int)$room_id.' AND
						  ra.y = '.(($y == $current_year) ? '0' : '1').' AND
						  ra.m = '.(int)$m;
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			}
			if($result[1] > 0){
				///echo '<br />'.$result[1].' Room ID: '.$room_id.' Day: '.$d.' Avail: '.$result[0]['d'.(int)$d];
				if($result[0]['d'.(int)$d] <= 0){
					return 0;
				}else{
					if($result[0]['d'.(int)$d] < $available_rooms){
						$available_rooms = $result[0]['d'.(int)$d];
					}

					/// [16.09.2018] IN TEST - seems it reduces booked rooms from the rest of rooms (already available)!!!!
					/// So it make double reduce !!!

//					if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){
//						if($result[0]['d'.(int)$d] < $available_rooms){
//							$available_rooms = $result[0]['d'.(int)$d];
//						}
//					}else{
//						$current_date_formated = date('Y-m-d', $current_date);
//
//						if(OPTIMIZE_SQL) {
//							$total_booked_rooms = !empty($reservations_arr[$current_date_formated]) ? $reservations_arr[$current_date_formated] : 0;
//							if ($total_booked_rooms >= $result[0]['d' . (int)$d]) {
//								return 0;
//							} else {
//								$available_diff = $result[0]['d' . (int)$d] - $total_booked_rooms;
//								if ($available_diff < $available_rooms) {
//									$available_rooms = $available_diff;
//								}
//							}
//						}else{
//							// Check maximal booked rooms for this day!!!
//							$sql = 'SELECT SUM('.TABLE_BOOKINGS_ROOMS.'.rooms) as total_booked_rooms
//								FROM '.TABLE_BOOKINGS.'
//									INNER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
//								WHERE
//									('.(($available_until_approval == 'yes') ? '' : TABLE_BOOKINGS.'.status = 2 OR ').' '.TABLE_BOOKINGS.'.status = 3) AND
//									'.TABLE_BOOKINGS_ROOMS.'.room_id = '.(int)$room_id.' AND
//									(
//										(\''.$current_date_formated.'\' >= checkin AND \''.$current_date_formated.'\' < checkout)
//										OR
//										(\''.$current_date_formated.'\' = checkin AND \''.$current_date_formated.'\' = checkout)
//									)';
//							$result1 = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
//							if($result1[1] > 0){
//								echo '<br>T: '.$result[0]['d'.(int)$d].' | Avail: '.$available_rooms.' | Total Reserved/B: '.$result1[0]['total_booked_rooms'];
//								if($result1[0]['total_booked_rooms'] >= $result[0]['d'.(int)$d]){
//									return 0;
//								}else{
//									$available_diff = $result[0]['d'.(int)$d] - $result1[0]['total_booked_rooms'];
//									if($available_diff < $available_rooms){
//										$available_rooms = $available_diff;
//									}
//								}
//							}
//						}
//					}
				}
			}else{
				return 0;
			}

			$m_old = $m;
			$current_date = strtotime('+1 day', $current_date);
		}


		return $available_rooms;
	}

	/**
	 * Convert to decimal number with leading zero
	 * @param $number
	 */
	private static function ConvertToDecimal($number)
	{
		return (($number < 0) ? '-' : '').((abs($number) < 10) ? '0' : '').abs($number);
	}

	/**
	 * Get price for specific date (1 night)
	 * @param $day
	 */
	public static function GetPriceForDate($rid, $day)
	{
		// get a week day of $day
		$week_day = strtolower(date('D', strtotime($day)));

		$sql = 'SELECT '.$week_day.' as price
				FROM '.TABLE_ROOMS_PRICES.'
				WHERE
					(
						is_default = 1 OR
						(is_default = 0 AND date_from <= \''.$day.'\' AND \''.$day.'\' <= date_to)
					) AND 
					room_id = '.(int)$rid.'
				ORDER BY is_default ASC
				LIMIT 0, 1';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return $result[0]['price'];
		}else{
			return '0';
		}
	}

	/**
	 * Get room info
	 * @param $room_id
	 * @param $param
	 */
	public static function GetRoomInfo($room_id, $param = '')
	{
		$lang = Application::Get('lang');
		$output = '';

		$sql = 'SELECT
					r.id,
					r.hotel_id,
					r.room_count,
					r.max_adults,
					r.max_children,
					r.beds,
					r.bathrooms,
					r.default_price,
					r.room_icon,					
					r.room_picture_1,
					r.room_picture_2,
					r.room_picture_3,
					r.room_picture_4,
					r.room_picture_5,
					r.is_active,
					rd.room_type,
					rd.room_short_description,
					rd.room_long_description,
					hd.name as hotel_name
				FROM '.TABLE_ROOMS.' r
					INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id AND rd.language_id = \''.$lang.'\'
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id AND hd.language_id = \''.$lang.'\'
				WHERE
					r.id = '.(int)$room_id.'';

		$room_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($room_info[1] > 0){
			if($param != ''){
				$output = isset($room_info[0][$param]) ? $room_info[0][$param] : '';
			}else{
				$output = isset($room_info[0]) ? $room_info[0] : array();
			}
		}
		return $output;
	}


	/**
	 * Get room full info
	 * @param $room_id
	 */
	public static function GetRoomFullInfo($room_id)
	{
		global $objLogin;

		$lang = Application::Get('lang');

		$sql = 'SELECT
                r.id,
                r.room_type,
                r.room_floor,
                r.hotel_id,
                r.room_count,
                r.max_adults,
                r.suitable_for_kids,
                r.max_children,
                r.beds,
                r.single_beds,
                r.double_beds,
                r.folding_sofa_single_beds,
                r.folding_sofa_double_beds,
                r.bathrooms,
                r.room_area,
                r.default_price,
				r.discount_night_type,
				r.discount_night_3,
				r.discount_night_4,
				r.discount_night_5,
				r.discount_guests_type,
				r.discount_guests_3,
				r.discount_guests_4,
				r.discount_guests_5,
				r.refund_money_type,
				r.refund_money_value,
                r.facilities,
                r.room_icon,
                r.room_icon_thumb,
                r.room_picture_1,
                r.room_picture_1_thumb,
                r.room_picture_2,
                r.room_picture_2_thumb,
                r.room_picture_3,
                r.room_picture_3_thumb,
                r.room_picture_4,
                r.room_picture_4_thumb,
                r.room_picture_5,
                r.room_picture_5_thumb,
                r.number_of_views,
                r.is_active,
                rd.room_type as loc_room_type,
                rd.room_long_description as loc_room_long_description,
                hd.name as hotel_name,
                h.property_type_id,
				h.cancel_reservation_day as cancel_reservation_day
            FROM '.TABLE_ROOMS.' r
                INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id
                INNER JOIN '.TABLE_HOTELS.' h ON r.hotel_id = h.id
                INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id
            WHERE
				'.(ADMIN_CAN_SEE_INACTIVE_PROPERTIES && $objLogin->IsLoggedInAs('owner','mainadmin') ? '' : 'h.is_active = 1 AND').'
                r.id = '.(int)$room_id.' AND
                hd.language_id = \''.$lang.'\' AND
                rd.language_id = \''.$lang.'\'';

		$room_info = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
        return $room_info;
    }

	/**
	 * Returns room types default price
	 * @param 4where
	 */
	public static function GetRoomTypes($where = '')
	{
		global $objLogin;

		$lang = Application::Get('lang');
		$output = '';
		$where_clause = '';

		if($objLogin->IsLoggedInAs('hotelowner')){
			$hotels_list = implode(',', $objLogin->AssignedToHotels());
			if(!empty($hotels_list)) $where_clause .= ' AND r.hotel_id IN ('.$hotels_list.')';
		}

		if(!empty($where)) $where_clause .= ' AND r.hotel_id = '.(int)$where;

		$sql = 'SELECT
					r.id,
					r.hotel_id,
					r.room_count,
					rd.room_type,
					\'\' as availability,
					hd.name as hotel_name					
				FROM '.TABLE_ROOMS.' r 
					INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id AND rd.language_id = \''.$lang.'\'
					INNER JOIN '.TABLE_HOTELS.' h ON r.hotel_id = h.id AND h.is_active = 1
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id AND hd.language_id = \''.$lang.'\'
				WHERE 1 = 1
				    '.$where_clause.'
				ORDER BY r.hotel_id ASC, r.priority_order ASC';

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

		if($result[1] > 0){
			return $result[0];
		}else{
			return array();
		}
	}

	/**
	 * Returns last day of month
	 * @param $month
	 * @param $year
	 */
	public static function GetMonthLastDay($month, $year)
	{
		if(empty($month)) {
		   $month = date('m');
		}
		if(empty($year)) {
		   $year = date('Y');
		}
		$result = strtotime("{$year}-{$month}-01");
		$result = strtotime('-1 second', strtotime('+1 month', $result));
		return date('d', $result);
	}


	/**
	 * Returns min and max count rooms
	 * @param $checkin_date
	 * @param $checkout_date
     * @param $room_id
	 */
    public static function GetMinMaxCountRooms($checkin_date = '', $checkout_date = '', $room_id = '')
    {
        $arr_output = array('min_rooms'=>0, 'max_rooms'=>0);
        $room_info = self::GetRoomInfo($room_id);
        if(empty($room_info)){
            return false;
        }

        $hotel_id = $room_info['hotel_id'];
        $min_max_hotels = array();

		// -----------------------------------------------------
		// Find general min night via all packages
		// -----------------------------------------------------
		$min_nights_packages = Packages::GetMinimumNights($checkin_date, $checkout_date, $hotel_id, true);
		if(!empty($min_nights_packages) && is_array($min_nights_packages)){
			$packages_min_nights = '';
			foreach($min_nights_packages as $key => $package){
				if(!empty($package['hotel_id'])){
					if($package['minimum_nights'] < $packages_min_nights || $packages_min_nights === ''){
						$packages_min_nights = (int)$package['minimum_nights'];
					}
					$min_max_hotels[$package['hotel_id']] = array(
						'package_name' 		=> $package['package_name'],
						'minimum_nights' 	=> $package['minimum_nights'],
						'maximum_nights' 	=> '',
						'minimum_rooms' 	=> '',
						'maximum_rooms' 	=> '',
						'partial_nights'	=> '',
						'start_date'		=> $package['start_date'],
						'finish_date'		=> $package['finish_date'],
						'hotel_name' 		=> $package['hotel_name'],
					);
				}
			}
		}

		// -----------------------------------------------------
		// Find general max night via all packages
		// -----------------------------------------------------
		$max_nights_packages = Packages::GetMaximumNights($checkin_date, $checkout_date, $hotel_id, true);
		if(!empty($max_nights_packages) && is_array($max_nights_packages)){
			$packages_max_nights = '';
			foreach($max_nights_packages as $key => $package){
				if(!empty($package['hotel_id'])){
					if($package['maximum_nights'] > $packages_max_nights || $packages_max_nights === ''){
						$packages_max_nights = (int)$package['maximum_nights'];
					}

					if(!isset($min_max_hotels[$package['hotel_id']])){
						$min_max_hotels[$package['hotel_id']] = array(
							'package_name' 		=> $package['package_name'],
							'minimum_nights' 	=> '',
							'maximum_nights' 	=> $package['maximum_nights'],
							'minimum_rooms' 	=> '',
							'maximum_rooms' 	=> '',
							'partial_nights'	=> '',
							'start_date'		=> $package['start_date'],
							'finish_date'		=> $package['finish_date'],
							'hotel_name' 		=> $package['hotel_name'],
						);
					}else{
						$min_max_hotels[$package['hotel_id']]['maximum_nights'] = $package['maximum_nights'];
					}
				}
			}
		}

		// -----------------------------------------------------
		// Find general min rooms via all packages
		// -----------------------------------------------------
		$min_rooms_packages = Packages::GetMinimumRooms($checkin_date, $checkout_date, $hotel_id, true);
		if(!empty($min_rooms_packages) && is_array($min_rooms_packages)){
			$packages_min_rooms = '';
			foreach($min_rooms_packages as $key => $package){
				if(!empty($package['hotel_id'])){
					if($package['minimum_rooms'] < $packages_min_rooms || $packages_min_rooms === ''){
						$packages_min_rooms = (int)$package['minimum_rooms'];
					}
					if(!isset($min_max_hotels[$package['hotel_id']])){
						$min_max_hotels[$package['hotel_id']] = array(
							'package_name' 		=> $package['package_name'],
							'minimum_nights' 	=> '',
							'maximum_nights' 	=> '',
							'minimum_rooms' 	=> $package['minimum_rooms'],
							'maximum_rooms' 	=> '',
							'start_date'		=> $package['start_date'],
							'finish_date'		=> $package['finish_date'],
							'hotel_name' 		=> $package['hotel_name'],
						);
					}else{
						$min_max_hotels[$package['hotel_id']]['minimum_rooms'] = $package['minimum_rooms'];
					}
				}
			}
		}

		// -----------------------------------------------------
		// Find general max rooms via all packages
		// -----------------------------------------------------
		$max_rooms_packages = Packages::GetMaximumRooms($checkin_date, $checkout_date, $hotel_id, true);
		if(!empty($max_rooms_packages) && is_array($max_rooms_packages)){
			$packages_max_rooms = '';
			foreach($max_rooms_packages as $key => $package){
				if(!empty($package['hotel_id'])){
					if($package['maximum_rooms'] > $packages_max_rooms || $packages_max_rooms == '-1'){
						$packages_max_rooms = (int)$package['maximum_rooms'];
					}

					if(!isset($min_max_hotels[$package['hotel_id']])){
						$min_max_hotels[$package['hotel_id']] = array(
							'package_name' 		=> $package['package_name'],
							'minimum_nights' 	=> '',
							'maximum_nights' 	=> '',
							'minimum_rooms' 	=> '',
							'maximum_rooms' 	=> $package['maximum_rooms'],
							'start_date'		=> $package['start_date'],
							'finish_date'		=> $package['finish_date'],
							'hotel_name' 		=> $package['hotel_name'],
						);
					}else{
						$min_max_hotels[$package['hotel_id']]['maximum_rooms'] = $package['maximum_rooms'];
					}
				}
			}
		}

        $min_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['minimum_rooms']) ? $min_max_hotels[$hotel_id]['minimum_rooms'] : 1;
        $max_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['maximum_rooms']) ? $min_max_hotels[$hotel_id]['maximum_rooms'] : 0;

        if(!Modules::IsModuleInstalled('channel_manager') || (ModulesSettings::Get('channel_manager', 'is_active') == 'no')){
            $max_booked_rooms = '0';
            $sql = 'SELECT
                        MAX('.TABLE_BOOKINGS_ROOMS.'.rooms) as max_booked_rooms
                    FROM '.TABLE_BOOKINGS.'
                        INNER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
                    WHERE
                        ('.TABLE_BOOKINGS.'.status = 2 OR '.TABLE_BOOKINGS.'.status = 3) AND
                        '.TABLE_BOOKINGS_ROOMS.'.room_id = '.(int)$room_id.' AND
                        (
                            (\''.$checkin_date.'\' <= checkin AND \''.$checkout_date.'\' > checkin) 
                            OR
                            (\''.$checkin_date.'\' < checkout AND \''.$checkout_date.'\' >= checkout)
                            OR
                            (\''.$checkin_date.'\' >= checkin  AND \''.$checkout_date.'\' < checkout)
                        )';
            $rooms_booked = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
            if($rooms_booked[1] > 0){
                $max_booked_rooms = (int)$rooms_booked[0]['max_booked_rooms'];
            }

            // this is only a simple check if there is at least one room wirh available num > booked rooms
            $available_rooms = (int)($room_info['room_count'] - $max_booked_rooms);
            // echo '<br> Room ID: '.$rooms[0][$i]['id'].' Max: '.$maximal_rooms.' Booked: '.$max_booked_rooms.' Av:'.$available_rooms;
        }else{
            $available_rooms = (int)$room_info['room_count'];
        }

        $available_rooms_updated = self::CheckAvailabilityForPeriod($room_id, $checkin_date, $checkout_date, $available_rooms);
        $max_rooms = !empty($max_rooms) && $max_rooms < $available_rooms_updated && $max_rooms != '-1' ? $max_rooms : $available_rooms_updated;

        $arr_output['max_rooms'] = $max_rooms;
        $arr_output['min_rooms'] = $min_rooms;

        return $arr_output;
    }

	/**
	 * Draws quick reservations block
	 * @param $hotel_ids
	 * @param $m_adults
	 * @param $m_children
	 * @param $type values: 'main-vertical', 'room-inline', 'main-inline'
	 * @param $draw
	 */
	public static function DrawQuickReservationsBlock($draw = true)
	{
		global $objLogin, $objRooms, $objSettings;

		$destination_validation_attr = (DESTINATION_VALIDATION) ? ' data-required="required" data-required-message="'.htmlentities(_FIELD_CANNOT_BE_EMPTY_DESTINATION).'"' : '';

		$current_day = date('d');
		$action_url = APPHP_BASE.'index.php?admin=mod_quick_reservations';
		$nl = "\r\n";

		$output = '<link rel="stylesheet" type="text/css" href="'.APPHP_BASE.'templates/'.Application::Get('template').'/css/calendar.css" />'.$nl;
		$output .= '<form action="'.$action_url.'" id="quick-reservation-form" name="quick-reservation-form" method="post">
		'.draw_hidden_field('act', 'send', false).$nl.'
		'.draw_token_field(false).$nl;


        $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;
		$hotel_sel_id = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
		$room_sel_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : '';
        $count_rooms = isset($_POST['count_rooms']) ? (int)$_POST['count_rooms'] : '';

        $checkin_date		 	= isset($_POST['checkin_date']) ? prepare_input($_POST['checkin_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y');
        $checkin_parts			= explode('/', $checkin_date);
        $checkin_month 			= isset($checkin_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 0 : 1]) : '';
        $checkin_day 			= isset($checkin_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkin_parts[$american_format ? 1 : 0]) : '';
        $checkin_year 			= isset($checkin_parts[2]) ? (int)$checkin_parts[2] : '';

        $checkout_date			= isset($_POST['checkout_date']) ? prepare_input($_POST['checkout_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + (24 * 60 * 60));
        $checkout_parts			= explode('/', $checkout_date);
        $checkout_month 		= isset($checkout_parts[$american_format ? 0 : 1]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 0 : 1]) : '';
        $checkout_day			= isset($checkout_parts[$american_format ? 1 : 0]) ? convert_to_decimal((int)$checkout_parts[$american_format ? 1 : 0]) : '';
        $checkout_year 			= isset($checkout_parts[2]) ? (int)$checkout_parts[2] : '';

		// Retrieve all active hotels according (for owner - related only, for selected location - related only)
		$hotels_list = ($objLogin->IsLoggedInAs('hotelowner')) ? implode(',', $objLogin->AssignedToHotels()) : '';
		$total_hotels = Hotels::GetAllHotels(
			(!empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : ($objLogin->IsLoggedInAs('hotelowner') ? '1=0' : '1=1'))
		);
		$hotels_total_number = $total_hotels[1];

        $output_hotels = '';
        $output_rooms = '';
        $total_rooms = array();
        $output_hotels .= '<select class="my-form-control mgrid_select select_hotel chosen_select" id="hotel_sel_id" name="hotel_id">'.$nl;
        $output_hotels .= '<option value="">-- '._SELECT.' --</option>'.$nl;
        foreach($total_hotels[0] as $key => $val){
            $output_hotels .= '<option'.(($hotel_sel_id == $val['id']) ? ' selected="selected"' : '').' value="'.$val['id'].'">'.$val['name'].'</option>'.$nl;
        }
        $output_hotels .= '</select>'.$nl;

        if(!empty($hotel_sel_id)){
            $total_rooms = Rooms::GetAllActive('r.hotel_id = '.(int)$hotel_sel_id);
        }

        $output_rooms .= '<select class="my-form-control mgrid_select select_rooms" id="room_sel_id" name="room_id">'.$nl;
        $output_rooms .= '<option value="">-- '._SELECT.' --</option>'.$nl;
        if(!empty($total_rooms)){
            foreach($total_rooms[0] as $key => $val){
                $output_rooms .= '<option'.(($room_sel_id == $val['id']) ? ' selected="selected"' : '').' value="'.$val['id'].'">'.$val['room_type'].'</option>'.$nl;
            }
        }
        $output_rooms .= '</select>'.$nl;


        $min_nights = ModulesSettings::Get('booking', 'minimum_nights');
        $search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');

        $selected_date1 = !empty($_POST['checkin_date']) ? prepare_input($_POST['checkin_date']) : date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y');
        $output1 = '<input class="form-control mySelectCalendar checkin_date" name="checkin_date" id="checkin_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$selected_date1.'">';
        $selected_date2 = !empty($_POST['checkout_date']) ? prepare_input($_POST['checkout_date']) : date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', time() + ((int)$min_nights * 24 * 60 * 60));
        $output2 = '<input class="form-control mySelectCalendar checkout_date" name="checkout_date" id="checkout_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$selected_date2.'">';

        $max_count_rooms = 0;
        $min_count_rooms = 0;
        $output_room_count = '';
        $arr_min_max_count_rooms = self::GetMinMaxCountRooms($checkin_year.'-'.$checkin_month.'-'.$checkin_day, $checkout_year.'-'.$checkout_month.'-'.$checkout_day, $room_sel_id);
        if(!empty($arr_min_max_count_rooms)){
            $min_count_rooms = $arr_min_max_count_rooms['min_rooms'];
            $max_count_rooms = $arr_min_max_count_rooms['max_rooms'];
        }
        $output_room_count .= '<select class="my-form-control mgrid_select select_count_rooms" id="count_rooms" name="count_rooms">'.$nl;
        $output_room_count .= '<option value="">-- '._SELECT.' --</option>'.$nl;
        if(!empty($max_count_rooms)){
            for($i = 1; $i <= $max_count_rooms; $i++){
                $output_room_count .= '<option'.(($count_rooms == $i) ? ' selected="selected"' : '').' value="'.$i.'">'.$i.'</option>'.$nl;
            }
        }
        $output_room_count .= '</select>'.$nl;

        $output_script = '<script>
        jQuery(document).ready(function(){
            function getCountRooms(){
                var room_select = jQuery("#room_sel_id").val();
                var checkin_val = jQuery("#checkin_date").val();
                var checkout_val = jQuery("#checkout_date").val();
                jQuery(".error_message").hide();
                jQuery(".msg.fail:not(.error_message .msg.fail)").hide();
                jQuery("#count_rooms").empty();
                jQuery("#count_rooms").replaceWith(\'<select class="form-control mgrid_select selectpicker" disabled="disabled" id="count_rooms" name="count_rooms"></select>\');
                jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_LOADING).'..."}).appendTo("#count_rooms");
                jQuery.ajax({
                    url: "'.APPHP_BASE.'ajax/count_rooms.ajax.php",
                    global: false,
                    type: "POST",
                    data: ({
                        room_id: room_select,
                        checkin_date: checkin_val,
                        checkout_date: checkout_val
                    }),
                    dataType: "json",
                    async: true,
                    error: function(html){
                        jQuery("#count_rooms").empty();
                        jQuery("#count_rooms").replaceWith(\'<select class="form-control mgrid_select selectpicker" id="count_rooms" name="count_rooms"></select>\');
                        jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_NONE).'"}).appendTo("#count_rooms");
                        console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
                    },
                    success: function(data){
                        jQuery("#count_rooms").empty();
                        if(data.min_rooms >= 0 && data.max_rooms > 0){
                            jQuery("#count_rooms").replaceWith(\'<select class="form-control mgrid_select selectpicker" id="count_rooms" name="count_rooms"></select>\');
                            jQuery("<option />", {val: "", selected: true, text: "-- '.htmlspecialchars(_SELECT).' --"}).appendTo("#count_rooms");
                            // add empty option
                            for(var i = data.min_rooms; i <= data.max_rooms; i++){
                                jQuery("<option />", {val: i, text: i}).appendTo("#count_rooms");
                            }
                        }else{
                            jQuery("#count_rooms").replaceWith(\'<select class="form-control mgrid_select selectpicker" disabled="disabled" id="count_rooms" name="count_rooms"></select>\');
                            jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_NONE).'"}).appendTo("#count_rooms");
                            jQuery("#error_none_count_rooms").show();
                        }
                    }
                });
            }
            jQuery(function() {
                var minDate = new Date();
                var maxDate = new Date();
                // min date + 1 day
                minDate.setTime(Date.parse("'.$selected_date1.'") + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
                '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                    ? 'minDate.setFullYear(minDate.getFullYear() - 1);'
                    : '').'
                jQuery("#checkin_date").datepicker({
                    '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                        ? 'defaultDate: "-1y",minDate: "-1y",'
                        : 'minDate: 0,').'
                    maxDate: "+'.(int)$search_availability_period.'y",
                    numberOfMonths: 2,
                    dateFormat: "'.($american_format ? 'mm/dd/yy' : 'dd/mm/yy').'"
                });
                jQuery("#checkout_date").datepicker({
                    '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                        ? 'defaultDate: "-1y",'
                        : '').'
                    minDate: minDate,
                    maxDate: "+'.(int)$search_availability_period.'y +'.((int)$min_nights > 0 ? (int)$min_nights : '1').'d",
                    numberOfMonths: 2,
                    dateFormat: "'.($american_format ? 'mm/dd/yy' : 'dd/mm/yy').'"
                });
            });
            jQuery("#checkin_date").change(function(){
                var checkin_val = jQuery("#checkin_date").val();
                '.(!$american_format
                    // make american format
                    ? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")'
                    : '').'
                var set_date = new Date();

                // checkin_val + 1d
                set_date.setTime(Date.parse(checkin_val) + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
                jQuery("#checkout_date").datepicker("option", "minDate", set_date);

                getCountRooms();
            });
            jQuery("#checkout_date").change(function(){
                var checkout_val = jQuery("#checkout_date").val();
                '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'
                    // make american format
                    ? 'checkout_val = checkout_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")'
                    : '').'
                var set_date = new Date();

                // checkout_val - 1d
                set_date.setTime(Date.parse(checkout_val) - ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
                jQuery("#checkin_date").datepicker("option", "maxDate", set_date);

                getCountRooms();
            });
            jQuery("#hotel_sel_id").change(function(){
                var hotel_select = jQuery(this).val();
                jQuery(".error_message").hide();
                jQuery(".msg.fail:not(.error_message .msg.fail)").hide();
                jQuery("#room_sel_id").empty();
                jQuery("#room_sel_id").replaceWith(\'<select class="form-control mgrid_select selectpicker" disabled="disabled" id="room_sel_id" name="room_id"></select>\');
                jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_LOADING).'..."}).appendTo("#room_sel_id");
                jQuery.ajax({
                    url: "'.APPHP_BASE.'ajax/rooms.ajax.php",
                    global: false,
                    type: "POST",
                    data: ({
                        hotel_id: hotel_select
                    }),
                    dataType: "json",
                    async: true,
                    error: function(html){
                        console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
                    },
                    success: function(data){
                        if(data.length > 0){
                            jQuery("#room_sel_id").replaceWith(\'<select class="form-control mgrid_select selectpicker" id="room_sel_id" name="room_id"></select>\');
                            jQuery("<option />", {val: "", selected: true, text: "--'.htmlspecialchars(_SELECT).'--"}).appendTo("#room_sel_id");
                            // add empty option
                            for(var i = 0; i < data.length; i++){
                                jQuery("<option />", {val: data[i].id, html: data[i].name}).appendTo("#room_sel_id");
                            }
                        }else{
                            jQuery("#room_sel_id").replaceWith(\'<select class="form-control mgrid_select selectpicker" disabled="disabled" id="room_sel_id" name="room_id"></select>\');
                            jQuery("<option />", {val: "", selected: true, text: "'.htmlspecialchars(_NONE).'"}).appendTo("#room_sel_id");
                            jQuery("#error_none_hotel_rooms").show();
                        }
                    }
                });
            });
            jQuery("#quick-reservation-form").on("change", "#room_sel_id", function(){
                getCountRooms();
            });
            jQuery(".chosen_select").chosen();
        });
        </script>';

        $output .= '<div class="error_message" id="error_none_hotel_rooms" style="display:none;">'.draw_important_message(_THIS_HOTEL_NO_ROOMS, false).'</div>';
        $output .= '<div class="error_message" id="error_none_count_rooms" style="display:none;">'.draw_important_message(_NO_AVAILABLE_ROOMS, false).'</div>';
        $output .= '<table cellspacing="2" border="0">';

        $output .= '<tr><td><label>'.(FLATS_INSTEAD_OF_HOTELS ? _SELECT_FLAT : _SELECT_HOTEL).':</label></td></tr>';
        $output .= '<tr><td nowrap="nowrap">'.$output_hotels.'</td></tr>';
        $output .= '<tr><td><label>'._EXISTING_ROOM_TYPES.'</label></td></tr>';
        $output .= '<tr><td>'.$output_rooms.'</td></tr>';
        $output .= '<tr><td><label>'._CHECK_IN.'</label></td></tr>';
        $output .= '<tr><td>'.$output1.'</td></tr>';
        $output .= '<tr><td><label>'._CHECK_OUT.'</label></td></tr>';
        $output .= '<tr><td>'.$output2.'</td></tr>';
        $output .= '<tr><td><label>'._ROOMS_COUNT.'</label></td></tr>';
        $output .= '<tr><td>'.$output_room_count.'</td></tr>';

        $output .= '<tr><td style="height:15px"></td></tr>';
        $output .= '<tr><td><input class="button-availability" type="submit" value="'._RESERVATION.'" /></td></tr>';
        $output .= '</table>';
        $output .= $output_script;

		$output .= '</form>';
		$output .= '<div id="calendar"></div>';

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws search availability block
	 * @param $show_calendar
	 * @param $room_id
	 * @param $hotel_ids
	 * @param $m_adults
	 * @param $m_children
	 * @param $type values: 'main-vertical', 'room-inline', 'main-inline'
	 * @param $action_url
	 * @param $target
	 * @param $draw
	 * @param $show_sort_by
	 * @param $show_hotels_ddl
	 * @param $property_type_id
	 * @param $show_property_types_ddl
	 * @param $month_number
	 * @param $show_filter
	 */
	public static function DrawSearchAvailabilityBlock($show_calendar = true, $room_id = '', $hotel_ids = '', $m_adults = 8, $m_children = 3, $type = 'main-vertical', $action_url = '', $target = '', $draw = true, $show_sort_by = true, $show_hotels_ddl = true, $property_type_id = '', $show_property_types_ddl = false, $month_number = SHOW_QUANTITY_MONTHS_CALENDAR, $show_filter = true)
	{
		global $objLogin, $objRooms, $objSettings;

		// If page check_availability then show additional filters
		$extended = false;
		if(Application::Get('page') == 'check_availability' && $show_filter){
			$extended = true;
		}

		$destination_validation_attr = (DESTINATION_VALIDATION) ? ' data-required="required" data-required-message="'.htmlentities(_FIELD_CANNOT_BE_EMPTY_DESTINATION).'"' : '';

		$current_day = date('d');
		$maximum_adults = ($type == 'room-inline') ? $m_adults : ModulesSettings::Get('rooms', 'max_adults');
		$maximum_children = ($type == 'room-inline') ? $m_children : ModulesSettings::Get('rooms', 'max_children');
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_minimum_beds = ModulesSettings::Get('rooms', 'allow_minimum_beds');
		$action_url = ($action_url != '') ? $action_url : APPHP_BASE;
		$target = (!empty($target)) ? $target : '';
		$nl = "\n";


		if(empty($property_type_id)){
			$property_type_id = isset($_REQUEST['property_type_id']) ? (int)$_REQUEST['property_type_id'] : '';
		}

		// Find default property
		if(empty($property_type_id)){
			$property_types = Application::Get('property_types');
			//$property_type_id = isset($property_types[0]['id']) ? $property_types[0]['id'] : 0;
		}

        $output = '';
		$output .= '<form'.(!empty($target) ? ' target="'.$target.'"' : '').' action="'.$action_url.'index.php?page=check_availability" id="reservation-form" name="reservation-form" method="post">
		'.draw_hidden_field('room_id', $room_id, false).'
		'.draw_hidden_field('p', '1', false, 'page_number').'
        '.(!$show_property_types_ddl ? draw_hidden_field('property_type_id', $property_type_id, false, 'property_type_id') : '').'
		'.draw_token_field(false).$nl;


		$output_properties = '';
		$output_hotels = '';
		$output_locations = '';
		$output_sort_by = '';

        $hotel_sel_loc = isset($_POST['hotel_sel_loc']) ? htmlspecialchars($_POST['hotel_sel_loc']) : '';
		$hotel_sel_loc_id = (HOTEL_SELECT_LOCATION == 'dropdownlist' || !empty($hotel_sel_loc)) && isset($_POST['hotel_sel_loc_id']) ? (int)$_POST['hotel_sel_loc_id'] : '';
		$hotel_sel_id = isset($_POST['hotel_sel_id']) ? (int)$_POST['hotel_sel_id'] : '';

        $total_hotels_locations = HotelsLocations::GetAllLocations();
        if($total_hotels_locations[1] == 1 && empty($hotel_sel_loc)){
            $hotel_sel_loc_id = $total_hotels_locations[0][0]['id'];
            $hotel_sel_loc    = $total_hotels_locations[0][0]['name'];
        }

		if($extended){
            $current_currency = isset($_POST['current_currency']) ? prepare_input($_POST['current_currency'], true) : Application::Get('currency_code');
			$sort_facilities = isset($_POST['sort_facilities']) ? (int)$_POST['sort_facilities'] : '';
			$sort_rating     = isset($_POST['sort_rating']) && is_numeric($_POST['sort_rating']) ? (int)$_POST['sort_rating'] : '';
			$sort_price      = isset($_POST['sort_price']) && in_array(strtolower($_POST['sort_price']), array('asc', 'desc')) ? strtolower($_POST['sort_price']) : '';
			$additional_sort_by = isset($_POST['additional_sort_by']) ? $_POST['additional_sort_by'] : '';
			$output .= draw_hidden_field('sort_rating', $sort_rating, false, 'sort_rating').$nl;
			$output .= draw_hidden_field('additional_sort_by', $additional_sort_by, false, 'additional_sort_by').$nl;
			$output .= draw_hidden_field('sort_price', $sort_price, false, 'sort_price').$nl;
			$output .= draw_hidden_field('sort_facilities', $sort_facilities, false, 'sort_facilities').$nl;
		    $output .= draw_hidden_field('current_currency', Application::Get('currency_code'), false, 'current_currency').$nl;
		}

		// Retrieve all active hotels according (for owner - related only, for selected location - related only)
		$hotels_list = ($objLogin->IsLoggedInAs('hotelowner')) ? implode(',', $objLogin->AssignedToHotels()) : '';
        if(!$objLogin->IsLoggedInAs('hotelowner','mainadmin','admin') && $hotel_ids != '') $hotels_list = prepare_input($hotel_ids);

		if(ADMIN_CAN_SEE_INACTIVE_PROPERTIES && $objLogin->IsLoggedInAs('owner','mainadmin')){
			$hotels_function = 'GetAllHotels';
		}else{
			$hotels_function = 'GetAllActive';
		}

		$total_hotels = Hotels::$hotels_function(
			(!empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : ($objLogin->IsLoggedInAs('hotelowner') ? '1=0' : '1=1')).
			($property_type_id != '' ? ' AND '.TABLE_HOTELS.'.property_type_id = '.(int)$property_type_id : '')
		);
		$hotels_total_number = $total_hotels[1];

		// Draw hidden field for widgets or hotel description page (if only one hotel is defined)
		if($hotels_total_number == 1 && $hotels_list != ''){
			if(HOTEL_SELECT_LOCATION == 'autocomplete'){
				$output .= draw_hidden_field('hotel_sel_id', (int)$hotels_list, false, 'hotel_sel_id').$nl;
			}else{
				$output .= draw_hidden_field('hotel_sel_id', (int)$hotels_list, false).$nl;
			}
		}

		// Draw property types
		if($show_property_types_ddl){
			$properties = HotelsPropertyTypes::GetHotelsPropertyTypes();
			if(!empty($properties)){
				$output_properties .= '<select class="my-form-control select_location" id="property_type_id" name="property_type_id" onchange="appChangeProperties()">';
				foreach($properties as $key => $val){
					$output_properties .= '<option value="'.$val['id'].'">'.$val['name'].'</option>';
				}
				$output_properties .= '</select>';
			}
		}

		// draw locations if no widget with predefined hotel IDs
		if(HOTEL_SELECT_LOCATION == 'dropdownlist'){
		   if(empty($hotel_ids) || $hotels_total_number > 1){
				if($total_hotels_locations[1] > 1){
					$onchange = ($show_hotels_ddl) ? 'onchange="appReloadHotels(this.value,jQuery(\'#property_type_id\').val(),\'hotel_sel_id\',\''.Application::Get('token').'\',\''.$action_url.'\',\''.Application::Get('lang').'\', \'-- '._ALL.' --\')"' : '';
					$output_locations .= '<select class="my-form-control select_location" id="hotel_sel_loc_id" name="hotel_sel_loc_id" '.$onchange.'>'.$nl;
					$output_locations .= '<option value="">-- '._ALL.' --</option>'.$nl;
					foreach($total_hotels_locations[0] as $key => $val){
						$output_locations .= '<option'.(($hotel_sel_loc_id == $val['id']) ? ' selected="selected"' : '').' value="'.$val['id'].'">'.$val['name'].'</option>'.$nl;
					}
					$output_locations .= '</select>'.$nl;
				}
			}

			// draw list of hotels
			$output_hotels .= '<select class="my-form-control select_hotel" id="hotel_sel_id" name="hotel_sel_id">'.$nl;
			if(!$objLogin->IsLoggedInAs('hotelowner')) $output_hotels .= '<option value="">-- '._ALL.' --</option>'.$nl;
			foreach($total_hotels[0] as $key => $val){
				$output_hotels .= '<option'.(($hotel_sel_id == $val['id']) ? ' selected="selected"' : '').' value="'.$val['id'].'">'.$val['name'].'</option>'.$nl;
			}
			$output_hotels .= '</select>'.$nl;
		}

		$selected_sort_by = isset($_POST['sort_by']) ? prepare_input($_POST['sort_by']) : '';
		$output_sort_by .= '<select class="my-form-control star_rating" name="sort_by" id="sort_by">
			<option'.($selected_sort_by == '' ? ' selected="selected"' : '').' value="">'._DEFAULT.'</option>';
		if($hotels_total_number > 1){
			$output_sort_by .= '<option'.(($selected_sort_by == 'stars-5-1') ? ' selected="selected"' : '').' value="stars-5-1">'._STARS_5_1.'</option>
			<option'.(($selected_sort_by == 'stars-1-5') ? ' selected="selected"' : '').' value="stars-1-5">'._STARS_1_5.'</option>
			<option'.(($selected_sort_by == 'name-a-z') ? ' selected="selected"' : '').' value="name-a-z">'._NAME_A_Z.'</option>
			<option'.(($selected_sort_by == 'name-z-a') ? ' selected="selected"' : '').' value="name-z-a">'._NAME_Z_A.'</option>';
		}
		$output_sort_by .= '<option'.(($selected_sort_by == 'price-l-h') ? ' selected="selected"' : '').' value="price-l-h">'._PRICE_L_H.'</option>
			<option'.(($selected_sort_by == 'price-h-l') ? ' selected="selected"' : '').' value="price-h-l">'._PRICE_H_L.'</option>
		</select>&nbsp;';

		if(CALENDAR_HOTEL == 'old'){
            $output1 = '<select id="checkin_day" name="checkin_monthday" class="my-form-control checkin_day" style="width:65px" onchange="cCheckDateOrder(this,\'checkin_monthday\',\'checkin_year_month\',\'checkout_monthday\',\'checkout_year_month\');cUpdateDaySelect(this);">
                            <option class="day prompt" value="0">'._DAY.'</option>';
                            $selected_day = isset($_POST['checkin_monthday']) ? prepare_input($_POST['checkin_monthday']) : date('d');
                            for($i=1; $i<=31; $i++){
                                $output1  .= '<option value="'.$i.'" '.(($selected_day == $i) ? 'selected="selected"' : '').'>'.$i.'</option>';
                            }
                        $output1 .= '</select>
                        <select id="checkin_year_month" name="checkin_year_month" class="my-form-control checkin_year_month" onchange="cCheckDateOrder(this,\'checkin_monthday\',\'checkin_year_month\',\'checkout_monthday\',\'checkout_year_month\');cUpdateDaySelect(this);">
                            <option class="month prompt" value="0">'._MONTH.'</option>';
                            $selected_year_month = isset($_POST['checkin_year_month']) ? prepare_input($_POST['checkin_year_month']) : date('Y-n');
                            for($i=0; $i<23; $i++){
                                $cur_time = mktime(0, 0, 0, date('m')+$i, '1', date('Y'));
                                $val = date('Y', $cur_time).'-'.(int)date('m', $cur_time);
                                $output1 .= '<option value="'.$val.'" '.(($selected_year_month == $val) ? 'selected="selected"' : '').'>'.get_month_local(date('n', $cur_time)).' \''.date('y', $cur_time).'</option>';
                            }
                        $output1 .= '</select>';
                        if($show_calendar) $output1 .= '<a class="calendar" onclick="cShowCalendar(this,\'calendar\',\'checkin\');" href="javascript:void(0);"><img title="'._PICK_DATE.'" alt="calendar" src="'.$action_url.'templates/'.Application::Get('template').'/images/button-calendar.png" /></a>';

            $output2 = '<select id="checkout_monthday" name="checkout_monthday" class="my-form-control checkout_day" style="width:65px;" onchange="cCheckDateOrder(this,\'checkout_monthday\',\'checkout_year_month\');cUpdateDaySelect(this);">
                            <option class="day prompt" value="0">'._DAY.'</option>';
                            $checkout_selected_day = isset($_POST['checkout_monthday']) ? prepare_input($_POST['checkout_monthday']) : date('d');
                            for($i=1; $i<=31; $i++){
                                $output2 .= '<option value="'.$i.'" '.(($checkout_selected_day == $i) ? 'selected="selected"' : '').'>'.$i.'</option>';
                            }
                        $output2 .= '</select>
                        <select id="checkout_year_month" name="checkout_year_month" class="my-form-control checkout_year_month" onchange="cCheckDateOrder(this,\'checkout_monthday\',\'checkout_year_month\');cUpdateDaySelect(this);">
                            <option class="month prompt" value="0">'._MONTH.'</option>';
                            $checkout_selected_year_month = isset($_POST['checkout_year_month']) ? prepare_input($_POST['checkout_year_month']) : date('Y-n');
                            for($i=0; $i<23; $i++){
                                $cur_time = mktime(0, 0, 0, date('m')+$i, '1', date('Y'));
                                $val = date('Y', $cur_time).'-'.(int)date('m', $cur_time);
                                $output2 .= '<option value="'.$val.'" '.(($checkout_selected_year_month == $val) ? 'selected="selected"' : '').'>'.get_month_local(date('n', $cur_time)).' \''.date('y', $cur_time).'</option>';
                            }
                        $output2 .= '</select>';
                        if($show_calendar) $output2 .= '<a class="calendar" onclick="cShowCalendar(this,\'calendar\',\'checkout\');" href="javascript:void(0);"><img title="'._PICK_DATE.'" alt="calendar" src="'.$action_url.'templates/'.Application::Get('template').'/images/button-calendar.png" /></a>';
		}else{
			$min_nights = ModulesSettings::Get('booking', 'minimum_nights');
			$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');

			$selected_date1 = !empty($_POST['checkin_date']) ? prepare_input($_POST['checkin_date']) : date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y');
			$output1 = '<input class="form-control mySelectCalendar checkin_date" name="checkin_date" id="checkin_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$selected_date1.'">';
			$selected_date2 = !empty($_POST['checkout_date']) ? prepare_input($_POST['checkout_date']) : date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', time() + ((int)$min_nights * 24 * 60 * 60));
			$output2 = '<input class="form-control mySelectCalendar checkout_date" name="checkout_date" id="checkout_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$selected_date2.'">';

			$output2 .= '<script>
			jQuery(function() {
				var minDate = new Date();
				var maxDate = new Date();
				// min date + 1 day
				minDate.setTime(Date.parse("'.$selected_date1.'") + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
                '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                    ? 'minDate.setFullYear(minDate.getFullYear() - 1);'
                    : '').'
				jQuery("#checkin_date").datepicker({
                    '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                        ? 'defaultDate: "-1y",minDate: "-1m",'
                        : 'minDate: '.(ModulesSettings::Get('booking', 'customer_booking_in_past') == 'yes' ? '"-1d"' : '0').',').'
					maxDate: "+'.(int)$search_availability_period.'y",
					numberOfMonths: '.(int)$month_number.',
                    dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
				});
				jQuery("#checkout_date").datepicker({
                    '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                        ? 'defaultDate: "-1m",'
                        : '').'
					minDate: minDate,
					maxDate: "+'.(int)$search_availability_period.'y +'.((int)$min_nights > 0 ? (int)$min_nights : '1').'d",
					numberOfMonths: '.(int)$month_number.',
                    dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
				});
			});
			jQuery("#checkin_date").change(function(){
				var checkin_val = jQuery("#checkin_date").val();
                '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'
                    // make american format
                    ? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")'
                    : '').'
				var set_date = new Date();

				// checkin_val + 1d
				set_date.setTime(Date.parse(checkin_val) + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
				jQuery("#checkout_date").datepicker("option", "minDate", set_date);
			});
			jQuery("#checkout_date").change(function(){
				var checkout_val = jQuery("#checkout_date").val();
                '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'
                    // make american format
                    ? 'checkout_val = checkout_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")'
                    : '').'
				var set_date = new Date();

				// checkout_val - 1d
				set_date.setTime(Date.parse(checkout_val) - ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
				jQuery("#checkin_date").datepicker("option", "maxDate", set_date);
			});
			</script>';
		}
		if($allow_minimum_beds == 'yes' && MIN_BEDS_USE_FOR_ADULTS){
			$minimum_beds = !empty($_POST['minimum_beds']) ? (int)$_POST['minimum_beds'] : 1;
			$output3  = '<input type="hidden" name="max_adults" value="1" />';
			$output3 .= '<input class="form-control minimum_beds" name="minimum_beds" id="minimum_beds" placeholder="" type="text" value="'.$minimum_beds.'">';
		}else{
			$output3 = '<select class="my-form-control max_occupation" style="width:55px" name="max_adults" id="max_adults">';
            $max_adults = isset($_POST['max_adults']) ? (int)$_POST['max_adults'] : '1';
            for($i=1; $i<=$maximum_adults; $i++){
                $output3 .= '<option value="'.$i.'" '.(($max_adults == $i) ? 'selected="selected"' : '').'>'.$i.'&nbsp;</option>';
            }
			$output3 .= '</select>';
		}

		$output4 = '';
		if($allow_children == 'yes'){
			$output4 .= '<select class="my-form-control max_occupation" style="width:55px" name="max_children" id="max_children">';
				$max_children = isset($_POST['max_children']) ? (int)$_POST['max_children'] : '0';
				for($i=0; $i<=$maximum_children; $i++){
					$output4 .= '<option value="'.$i.'" '.(($max_children == $i) ? 'selected="selected"' : '').'>'.$i.'&nbsp;</option>';
				}
			$output4 .= '</select>';
		}
		$output5 = '';
		if($allow_minimum_beds == 'yes' && !MIN_BEDS_USE_FOR_ADULTS){
			$minimum_beds = !empty($_POST['minimum_beds']) ? (int)$_POST['minimum_beds'] : '';
			$output5 .= '<input class="form-control minimum_beds" name="minimum_beds" id="minimum_beds" placeholder="" type="text" value="'.$minimum_beds.'">';
		}

		if($type == 'room-inline'){
			// We're in room description page
			if(HOTEL_SELECT_LOCATION == 'autocomplete'){
				$output .= self::PrepareHotelNameField(isset($total_hotels[0][0]) ? $total_hotels[0][0] : '', 'hotel_sel_loc');
			}

			$output0 = '';
			$output6 = '';
			if($objLogin->IsLoggedInAs('hotelowner') && Application::Get('template') == 'admin'){
				$output0 .= FLATS_INSTEAD_OF_HOTELS ? _SELECT_FLAT : _SELECT_HOTEL;
				$output6 .= $output_hotels;
			}else{
				if($hotels_total_number > 1 && (HOTEL_SELECT_LOCATION == 'autocomplete' || !empty($output_locations))){
					$output0 .= _SELECT_LOCATION;
					//$output .= $output_locations;
					$output6 .= draw_hidden_field('hotel_sel_loc_id', $hotel_sel_loc_id, false, 'hotel_sel_loc_id').$nl;
					$output6 .= '<input class="form-control hotel_sel_loc" '.$destination_validation_attr.' type="textbox" name="hotel_sel_loc" id="hotel_sel_loc" placeholder="'.htmlentities(FLATS_INSTEAD_OF_HOTELS ? _EX_FLAT_OR_LOCATION : _EX_HOTEL_OR_LOCATION).'" type="text" value="'.htmlspecialchars($hotel_sel_loc).'">'.$nl;
				}
				if(HOTEL_SELECT_LOCATION == 'dropdownlist'){
					if($hotels_total_number > 1){
						$output0 .= FLATS_INSTEAD_OF_HOTELS ? _SELECT_FLAT : _SELECT_HOTEL;
						$output6 .= $output_hotels;
					}
				}
			}

			$output .= '<table cellspacing="2" border="0" class="responsive-table">
				<tr class="responsive-hidden">
					'.(!empty($output0) ? '<td class="td-header"><label>'.$output0.'</label></td>' : '').'
					<td class="td-header"><label>'._CHECK_IN.':</label></td>
					<td class="td-header"><label>'._CHECK_OUT.':</label></td>
					<td class="td-header"><label>'._ADULTS.'</label></td>
					<td class="td-header"><label>'._CHILDREN.'</label></td>
					<td class="td-header">'.($allow_minimum_beds == 'yes' && !MIN_BEDS_USE_FOR_ADULTS ? '<label>'._MIN_BEDS.'</label>' : '').'</td>
					<td class="td-header"></td>
				</tr>
				<tr>
					'.(!empty($output6) ? '<td nowrap="nowrap" data-th="'.htmlentities($output0).'">'.$output6.'</td>' : '').'
					<td nowrap="nowrap" data-th="'.htmlentities(_CHECK_IN).'">'.$output1.'</td>
					<td nowrap="nowrap" data-th="'.htmlentities(_CHECK_OUT).'">'.$output2.'</td>
					<td nowrap="nowrap" data-th="'.htmlentities(_ADULTS).'">'.$output3.(defined('IS_WIDGET') && IS_WIDGET ? '<br/><small style="display:block;width:55px;text-align:center;">'._AGES.' 18+</small>' : '').'</td>
					<td nowrap="nowrap" data-th="'.htmlentities(_CHILDREN).'">'.$output4.(defined('IS_WIDGET') && IS_WIDGET ? '<br/><small style="display:block;width:55px;text-align:center;">0-3</small>' : '').'</td>
					<td nowrap="nowrap" data-th="'.htmlentities(_MINIMUM_BEDS).'">'.($allow_minimum_beds == 'yes' ? ''.$output5 : '').'</td>
					<td'.(empty($output6) && $allow_minimum_beds != 'yes' ? ' style="width:45%;"' : '').'><input class="form_button" type="submit" value="'._CHECK_AVAILABILITY.'" /></td>
				</tr>				
				</table>';
		}elseif($type == 'main-inline'){
			$output .= '<table width="100%" cellspacing="2" border="0">
			<tr>
				<td>'.$nl;

					if($objLogin->IsLoggedInAs('hotelowner') && Application::Get('template') == 'admin'){
						$output .= '<label>'.(FLATS_INSTEAD_OF_HOTELS ? _SELECT_FLAT : _SELECT_HOTEL).':</label>';
						$output .= $output_hotels;
					}else{
						if($hotels_total_number > 1 && (HOTEL_SELECT_LOCATION == 'autocomplete' || !empty($output_locations))){
							$output .= '<label>'._SELECT_LOCATION.':</label>';
							//$output .= $output_locations;
							$output .= draw_hidden_field('hotel_sel_loc_id', $hotel_sel_loc_id, false, 'hotel_sel_loc_id').$nl;
							$output .= '<input class="form-control hotel_sel_loc" '.$destination_validation_attr.' type="textbox" name="hotel_sel_loc" id="hotel_sel_loc" placeholder="'.htmlentities(FLATS_INSTEAD_OF_HOTELS ? _EX_FLAT_OR_LOCATION : _EX_HOTEL_OR_LOCATION).'" type="text" value="'.htmlspecialchars($hotel_sel_loc).'">'.$nl;
						}
						if(HOTEL_SELECT_LOCATION == 'dropdownlist'){
							$output .= '</td><td>';
							if($hotels_total_number > 1){
								$output .= '<label>'.(FLATS_INSTEAD_OF_HOTELS ? _SELECT_FLAT : _SELECT_HOTEL).':</label>';
								$output .= $output_hotels;
							}
						}
					}
				$output .= '</td>
				<td colspan="2"><label>'._SORT_BY.':</label>'.$output_sort_by.'</td>
				<td><input class="form_button" type="submit" value="'._CHECK_AVAILABILITY.'" /></td>
			<tr>
			<tr>
				<td><label>'._CHECK_IN.':</label>'.$output1.'</td>
				<td><label>'._CHECK_OUT.':</label>'.$output2.'</td>
				<td><label>'._ADULTS.'</label>'.$output3.'</td>
				<td><label>'._CHILDREN.'</label>'.$output4.'</td>				
				'.($allow_minimum_beds == 'yes' && !MIN_BEDS_USE_FOR_ADULTS ? '<td><label>'._MINIMUM_BEDS.'</label>'.$output5.'</td>' : '').'
			</tr>
			</table>';
		}else{
			$output .= '<table cellspacing="2" border="0">';

			if($objLogin->IsLoggedInAs('hotelowner') && Application::Get('template') == 'admin'){
				// Draw fields for hotel owner - leave only hotel ID select

				$hotels_list = implode(',', $objLogin->AssignedToHotels());
				$total_hotels = Hotels::GetAllHotels(
					(!empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : '0=1')
				);

				// draw list of hotels
				$output_hotels .= '<select class="mgrid_select" id="_hotel_sel_id" name="hotel_sel_id">'.$nl;
				foreach($total_hotels[0] as $key => $val){
					$output_hotels .= '<option'.(($hotel_sel_id == $val['id']) ? ' selected="selected"' : '').' value="'.$val['id'].'">'.$val['name'].'</option>'.$nl;
				}
				$output_hotels .= '</select>'.$nl;

				if($hotels_total_number > 1){
					$output .= '<tr><td><label>'.(FLATS_INSTEAD_OF_HOTELS ? _SELECT_FLAT : _SELECT_HOTEL).':</label></td></tr>';
					$output .= '<tr><td nowrap="nowrap">'.$output_hotels.'</td></tr>';
				}

			}else{

				if(!empty($output_properties)){
					$output .= '<tr><td><label>'._PROPERTY_TYPES.':</label></td></tr>';
					$output .= '<tr><td nowrap="nowrap">'.$output_properties.'</td></tr>';
				}

				if($hotels_total_number == 1 && $hotels_list != ''){
					// We're in hotel description page - draw nothing
					$output .= '<tr><td nowrap="nowrap">'.$nl;
					$output .= self::PrepareHotelNameField(isset($total_hotels[0][0]) ? $total_hotels[0][0] : '', 'hotel_sel_loc');
					$output .= '</td></tr>';
				}else{
                    if(HOTEL_SELECT_LOCATION == 'autocomplete'){
                        if($hotels_total_number > 1){
    						$output .= '<tr><td><label>'.(FLATS_INSTEAD_OF_HOTELS ? _SELECT_DESTINATION_OR_FLAT : _SELECT_DESTINATION_OR_HOTEL).':</label></td></tr>';
	    					$output .= '<tr><td nowrap="nowrap">'.$nl;
		    				$output .= draw_hidden_field('hotel_sel_loc_id', $hotel_sel_loc_id, false, 'hotel_sel_loc_id').$nl;
			    			$output .= draw_hidden_field('hotel_sel_id', $hotel_sel_id, false, 'hotel_sel_id').$nl;
				    		$output .= '<input class="form-control hotel_sel_loc" '.$destination_validation_attr.' type="textbox" name="hotel_sel_loc" id="hotel_sel_loc" placeholder="'.htmlentities(FLATS_INSTEAD_OF_HOTELS ? _EX_FLAT_OR_LOCATION : _EX_HOTEL_OR_LOCATION).'" value="'.htmlspecialchars($hotel_sel_loc).'">'.$nl;
					    	$output .= '</td></tr>';
                        }else{
	    					$output .= '<tr><td nowrap="nowrap">'.$nl;
		    				$output .= draw_hidden_field('hotel_sel_loc_id', '', false, 'hotel_sel_loc_id').$nl;
			    			$output .= draw_hidden_field('hotel_sel_id', (!empty($total_hotels[0][0]['id']) ? $total_hotels[0][0]['id'] : 0), false, 'hotel_sel_id').$nl;
					    	$output .= '</td></tr>';
                        }
					}else{
						if($show_hotels_ddl && $hotels_total_number > 1){
							if(!empty($output_locations)){
								$output .= '<tr><td><label>'._SELECT_LOCATION.':</label></td></tr>';
								$output .= '<tr><td>'.$output_locations.'</td></tr>';
								$output .= '</td></tr>';
							}
							$output .= '<tr><td><label>'.(FLATS_INSTEAD_OF_HOTELS ? _SELECT_FLAT : _SELECT_HOTEL).':</label></td></tr>';
							$output .= '<tr><td nowrap="nowrap">'.$output_hotels.'</td></tr>';
						}
					}
				}
			}

			$output .= '<tr><td><label>'._CHECK_IN.':</label></td></tr>
						<tr><td nowrap="nowrap">'.$output1.'</td></tr>
						<tr><td><label>'._CHECK_OUT.':</label></td></tr>
						<tr><td nowrap="nowrap">'.$output2.'</td></tr>
						<tr><td style="height:5px"></td></tr>
						<tr>
                        <td nowrap="nowrap">
							<table>
							<tr>
								<td><label>'._ADULTS.'</label>:</td>
								<td><label>'._CHILDREN.'</label>:</td>
								<td><label>'._SORT_BY.'</label>:</td>
							</tr>
							<tr>
								<td>'.$output3.'</td>
								<td>'.$output4.'</td>
								<td>'.($show_sort_by ? $output_sort_by : '').'</td>
							</tr>
							<tr>
								<td class="textleft"><small>'._AGES.' 18+</small></td>
								<td class="textleft">&nbsp;<small>0-3</small></td>
								<td></td>
							</tr>
							</table>
                        </td>
                        </tr>';
			if($allow_minimum_beds == 'yes' && !MIN_BEDS_USE_FOR_ADULTS){
				$output .= '<tr><td style="height:5px"></td></tr>
							<tr><td><label style="margin-top:6px;">'._MINIMUM_BEDS_SHORT.':</label>'.$output5.'</td></tr>
							<tr><td style="height:5px"></td></tr>';
			}

            $output .= '<tr><td style="height:5px"></td></tr>';
            $output .= '<tr><td><input class="button-availability" type="submit" value="'._CHECK_AVAILABILITY.'" /></td></tr>';
			$output .= '</table>';
		}
		// Show additional filters
		if($extended){
			$arr_filter_rating = isset($_POST['filter_rating_star']) && is_array($_POST['filter_rating_star']) ? $_POST['filter_rating_star'] : array();
			$arr_filter_price = isset($_POST['filter_price']) && strpos($_POST['filter_price'], ';') ? explode(';', $_POST['filter_price']) : array();
			$arr_filter_distance = isset($_POST['filter_distance']) && strpos($_POST['filter_distance'], ';') ? explode(';', $_POST['filter_distance']) : array();
			$arr_filter_facilities = isset($_POST['filter_facilities']) && is_array($_POST['filter_facilities']) ? $_POST['filter_facilities'] : array();

            $currency_rate = Application::Get('currency_rate');
			if(!empty($arr_filter_price) && count($arr_filter_price) == 2){
				$filter_start_price = $arr_filter_price[0];
				$filter_end_price = $arr_filter_price[1];

                $currency_symbol = Application::Get('currency_symbol');
                $currency_code = Application::Get('currency_code');
                if($currency_code != $current_currency){
                    $current_currency_info = Currencies::GetCurrencyInfo($current_currency);
                    if($current_currency_info['code'] != $currency_code){
                        if($filter_start_price > 1){
                            $filter_start_price = floor($filter_start_price * ($currency_rate / $current_currency_info['rate']));
                        }
                        $filter_end_price = ceil($filter_end_price * ($currency_rate / $current_currency_info['rate']));
                    }
                }
			}else{
				$filter_start_price = 0;
                $filter_end_price = MAX_PRICE_FILTER * $currency_rate;
			}

			if(!empty($arr_filter_distance) && count($arr_filter_distance) == 2){
				$filter_start_distance = $arr_filter_distance[0];
				$filter_end_distance = $arr_filter_distance[1];
			}else{
				$filter_start_distance = 0;
				$filter_end_distance = 1000;
			}

			// prepare facilities array
			$total_facilities = RoomFacilities::GetAllActive();
			$arr_facilities = array();
			if($total_facilities[1] > 0){
				foreach($total_facilities[0] as $key => $val){
					$arr_facilities[$val['id']] = array('name'=>$val['name'], 'count'=>0);
				}
			}

			if(!empty($objRooms)){
				foreach($objRooms->GetAvailableRooms() as $key => $val){
					$hotel_facilities = !empty($val[0]['facilities']) ? @unserialize($val[0]['facilities']) : array();
					if(!empty($hotel_facilities) && is_array($hotel_facilities)){
						foreach($hotel_facilities as $facilities){
							if(isset($arr_facilities[$facilities])){
								$arr_facilities[$facilities]['count']++;
							}
						}
					}
				}
			}
			// Head
			$output .= '
				<div class="line2"></div>
				<h3 class="opensans dark">'._FILTER_BY.'</h3>
				<div class="line2"></div>';

			// Star rating
			$output .= '
				<!-- Star ratings -->	
				<button type="button" class="collapsebtn" data-toggle="collapse" data-target="#collapse1_1">
				  '._RATINGS.' <span class="collapsearrow"></span>
				</button>
				<div id="collapse1_1" class="collapse in">
					<div class="hpadding20">
					';
			for($i = 5; $i > 0; $i--){
				$output .= '<div class="checkbox">
							<label>
							  <input type="checkbox"'.(in_array($i, $arr_filter_rating) ? ' checked="checked"' : '').' value="'.$i.'" name="filter_rating_star[]"><img src="templates/'.Application::Get('template').'/images/filter-rating-'.$i.'.png" class="imgpos1" alt=""/> '.$i.' '.($i > 1 ? _STARS : _STAR).'
							</label>
						</div>';
			}

			$output .= '</div>
					<div class="clearfix"></div>
				</div>
				<!-- End of Star ratings -->';

			// Price range
            $currency_symbol = Application::Get('currency_symbol');
            $priceTo = ceil(MAX_PRICE_FILTER * Application::Get('currency_rate') / 10) * 10;
			$output .= '<div class="line2"></div>
				<!-- Price range -->					
				<button type="button" class="collapsebtn" data-toggle="collapse" data-target="#collapse1_2">
				  '._PRICE_RANGE.' <span class="collapsearrow"></span>
				</button>
					
				<div id="collapse1_2" class="collapse in">
					<div class="padding20">
						<div class="layout-slider wh100percent">
						<span class="cstyle09"><input id="Slider2" type="slider" name="filter_price" value="'.$filter_start_price.';'.$filter_end_price.'" /></span>
						</div>
						<script type="text/javascript" >
						  jQuery("#Slider2").slider({ from: 1, to: '.(int)$priceTo.', step: 1, heterogeneity: ["15/'.((int)$priceTo/100).'", "50/'.((int)$priceTo/10).'", "75/'.((int)$priceTo/2).'"], smooth: true, round: 0, dimension: "&nbsp;'.$currency_symbol .'", skin: "round" });
						</script>
					</div>
				</div>
				<!-- End of Price range -->';

            // Distance
            if(SHOW_FILTER_DISTANCE_TO_CENTER_POINT){
                $output .= '<div class="line2"></div>
                    <!-- Discance -->		
                    <button type="button" class="collapsebtn" data-toggle="collapse" data-target="#collapse1_3">
                      '._DISTANCE_TO_CENTER_POINT.' <span class="collapsearrow"></span>
                    </button>				
                    
                    <div id="collapse1_3" class="collapse in">
                        <div class="padding20">
                            <div class="layout-slider wh100percent">
                            <span class="cstyle09"><input id="Slider3" type="slider" name="filter_distance" value="'.$filter_start_distance.';'.$filter_end_distance.'" /></span>
                            </div>
                            <script type="text/javascript" >
                              jQuery("#Slider3").slider({ from: 0, to: 1000, step: 0.1, heterogeneity: ["12/1", "25/10", "50/100", "75/500"], smooth: true, round: 1, dimension: "&nbsp;'._KILOMETERS_SHORTENED.'", skin: "round", format: {format: "#,##0.0", locale: "us"}});
                            </script>
                        </div>
                    </div>
                    <!-- End of Acomodations -->';
            }

			$output .= '<div class="line2"></div>
				<!-- Hotel Preferences -->
				<button type="button" class="collapsebtn last" data-toggle="collapse" data-target="#collapse1_4">
				  '._PREFERENCES.' <span class="collapsearrow"></span>
				</button>	
				<div id="collapse1_4" class="collapse in">
					<div class="hpadding20">';

			foreach($arr_facilities as $key => $facilities){
				if($facilities['count'] > 0){
					$output .= '<div class="checkbox">
							<label>
							  <input type="checkbox"'.(in_array($key, $arr_filter_facilities) ? ' checked="checked"' : '').' value="'.$key.'" name="filter_facilities[]">'.$facilities['name'].' ('.$facilities['count'].')
							</label>
						</div>';
				}
			}

			$output .= '</div>
					<div class="clearfix"></div>						
				</div>	
				<!-- End of Hotel Preferences -->';

            $output .= '<div class="line2"></div>';
            $output .= '<input class="button-availability" type="submit" value="'._APPLY_FILTERS.'" />';
		}

		$output .= '</form>';
		$output .= '<div id="calendar"></div>';
		$output .= '<script>
		jQuery(document).ready(function(){
			jQuery("#hotel_sel_loc").autocomplete({
				source: function(request, response){
					var token = "'.htmlentities(Application::Get('token')).'";
					jQuery.ajax({
						url: "'.$action_url.'ajax/hotel_location.ajax.php",
						global: false,
						type: "POST",
						data: ({
							token: token,
							act: "send",
							lang: "'.htmlentities(Application::Get('lang')).'",
							property_type : jQuery("#property_type_id").val(),
							search : jQuery("#hotel_sel_loc").val(),
						}),
						dataType: "json",
						async: true,
						error: function(html){
							console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
						},
						success: function(data){
							if(data.length == 0){
								response({ label: "'.htmlentities(_NO_MATCHES_FOUND).'" });
							}else{
								jQuery("#hotel_sel_loc_id").val("");
								response(jQuery.map(data, function(item){
									return{ location_id: item.location_id, hotel_id: item.hotel_id, label: item.label }
								}));
							}
						}
					});
				},
				minLength: 2,
				select: function(event, ui) {					
					jQuery("#hotel_sel_loc_id").val(ui.item.location_id);
					jQuery("#hotel_sel_id").val(ui.item.hotel_id);
					if(typeof(ui.item.location_id) == "undefined"){
						jQuery("#hotel_sel_loc").val("");
						return false;
					}
				}
			});
		});
		</script>';

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws search availability footer scripts
	 * @param $dir
	 * @param $action_url
	 */
	public static function DrawSearchAvailabilityFooter($dir = '', $action_url = '')
	{
		global $objSettings;

		$nl = "\n";
		$output = '';
		if(Modules::IsModuleInstalled('booking')){
			if(CALENDAR_HOTEL == 'old'){
				$min_nights = ModulesSettings::Get('booking', 'minimum_nights');
				$min_nights_packages = Packages::GetMinimumNights(date('Y-m-01'), date('Y-m-28'));
				if(isset($min_nights_packages['minimum_nights']) && !empty($min_nights_packages['minimum_nights'])) $min_nights = $min_nights_packages['minimum_nights'];
				$action_url = ($action_url != '') ? $action_url : APPHP_BASE;

				$output  = '<script type="text/javascript" src="'.$action_url.'templates/'.Application::Get('template').'/js/calendar'.$dir.(Application::Get('lang_dir') == 'rtl' ? '.rtl' : '').'.js"></script>'.$nl;
				$output .= '<script type="text/javascript">'.$nl;
				$output .= 'var calendar = new Object();';
				$output .= 'var trCal = new Object();';
				$output .= 'trCal.nextMonth = "'._NEXT.'";';
				$output .= 'trCal.prevMonth = "'._PREVIOUS.'";';
				$output .= 'trCal.closeCalendar = "'._CLOSE.'";';
				$output .= 'trCal.icons = "templates/'.Application::Get('template').'/images/";';
				$output .= 'trCal.iconPrevMonth2 = "'.((Application::Get('defined_alignment') == 'left') ? 'butPrevMonth2.gif' : 'butNextMonth2.gif').'";';
				$output .= 'trCal.iconPrevMonth = "'.((Application::Get('defined_alignment') == 'left') ? 'butPrevMonth.gif' : 'butNextMonth.gif').'";';
				$output .= 'trCal.iconNextMonth2 = "'.((Application::Get('defined_alignment') == 'left') ? 'butNextMonth2.gif' : 'butPrevMonth2.gif').'";';
				$output .= 'trCal.iconNextMonth = "'.((Application::Get('defined_alignment') == 'left') ? 'butNextMonth.gif' : 'butPrevMonth.gif').'";';
				if(ModulesSettings::Get('booking', 'customer_booking_in_past') == 'yes'){
					$output .= 'trCal.currentDay = "'.date('d', mktime(0,0,0,date('m'), date('d') - 1, date('Y'))).'";';
				}else{
					$output .= 'trCal.currentDay = "'.date('d').'";';
				}
				$output .= 'trCal.currentYearMonth = "'.date('Y-n').'";';
				$output .= 'var minimum_nights = "'.(int)$min_nights.'";';
				$output .= 'var months = ["'._JANUARY.'","'._FEBRUARY.'","'._MARCH.'","'._APRIL.'","'._MAY.'","'._JUNE.'","'._JULY.'","'._AUGUST.'","'._SEPTEMBER.'","'._OCTOBER.'","'._NOVEMBER.'","'._DECEMBER.'"];';
				$output .= 'var days = ["'._MON.'","'._TUE.'","'._WED.'","'._THU.'","'._FRI.'","'._SAT.'","'._SUN.'"];'.$nl;
				if(!isset($_POST['checkin_monthday']) && !isset($_POST['checkin_year_month'])){
					$output .= 'cCheckDateOrder(document.getElementById("checkin_day"),"checkin_monthday","checkin_year_month","checkout_monthday","checkout_year_month");';
				}
				$output .= '</script>';
			}elseif(CALENDAR_HOTEL == 'new'){
				$output .= '<script type=\'text/javascript\'>';
                $output .= 'if(jQuery.datepicker != undefined){';
				$output .= 'jQuery.datepicker.regional[\''.Application::Get('lang').'\'] = {';
                    $output .= 'firstDay: "'.($objSettings->GetParameter('week_start_day')-1).'",';
					$output .= 'closeText: "'.htmlspecialchars(_CLOSE).'",';
					$output .= 'prevText: "&#x3C; '.htmlspecialchars(_PREVIEW).'",';
					$output .= 'nextText: "'.htmlspecialchars(_NEXT).' &#x3E;",';
					$output .= 'currentText: "'.htmlspecialchars(_TODAY).'",';
					$output .= 'monthNames: [ "'.htmlspecialchars(_JANUARY).'", "'.htmlspecialchars(_FEBRUARY).'", "'.htmlspecialchars(_MARCH).'", "'.htmlspecialchars(_APRIL).'", "'.htmlspecialchars(_MAY).'", "'.htmlspecialchars(_JUNE).'",';
					$output .= '"'.htmlspecialchars(_JULY).'", "'.htmlspecialchars(_AUGUST).'", "'.htmlspecialchars(_SEPTEMBER).'", "'.htmlspecialchars(_OCTOBER).'", "'.htmlspecialchars(_NOVEMBER).'", "'.htmlspecialchars(_DECEMBER).'" ],';
					$output .= 'monthNamesShort: [ "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12" ],';
					$output .= 'dayNames: [ "'.htmlspecialchars(_SUNDAY).'", "'.htmlspecialchars(_MONDAY).'", "'.htmlspecialchars(_TUESDAY).'", "'.htmlspecialchars(_WEDNESDAY).'", "'.htmlspecialchars(_THURSDAY).'", "'.htmlspecialchars(_FRIDAY).'", "'.htmlspecialchars(_SATURDAY).'" ],';
					$output .= 'dayNamesShort: [ "'.htmlspecialchars(_SUN).'", "'.htmlspecialchars(_MON).'", "'.htmlspecialchars(_TUE).'", "'.htmlspecialchars(_WED).'", "'.htmlspecialchars(_THU).'", "'.htmlspecialchars(_FRI).'", "'.htmlspecialchars(_SAT).'" ],';
					$output .= 'dayNamesMin: [ "'.htmlspecialchars(_SU).'", "'.htmlspecialchars(_MO).'", "'.htmlspecialchars(_TU).'", "'.htmlspecialchars(_WE).'", "'.htmlspecialchars(_TH).'", "'.htmlspecialchars(_FR).'", "'.htmlspecialchars(_SA).'" ],';
					$output .= 'isRTL: '.(Application::Get('lang_dir') == 'rtl' ? 'true' : 'false');
				$output .= '};';
                $output .= 'jQuery.datepicker.setDefaults( jQuery.datepicker.regional[\''.Application::Get('lang').'\'] );';
				$output .= '};';

				$output .= '</script>';
			}
		}
		echo $output;
	}

	/**
	 * Draw information about rooms and services
	 * @param $draw
	 */
	public static function DrawRoomsInfo($draw = true)
	{
		$lang = Application::Get('lang');
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
		$hotel_id = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
		$total_hotels = Hotels::GetAllActive();
		$output = '';

		if($total_hotels[1] == 1){
			$hotel_id = isset($total_hotels[0][0]['id']) ? (int)$total_hotels[0][0]['id'] : '';
		}elseif($total_hotels[1] > 1){
			$output .= '<form action="'.prepare_link('pages', 'system_page', 'rooms', 'index', '', '', '', true).'" method="post">';
            $output .= draw_token_field(false);
            //<label>'._HOTEL.':</label>
			$output .= '<div class="hotel_selector"><select class="form-control" name="hotel_id">';
			//$output .= '<option value="0">-- '._HOTELS.' ('._ALL.') --</option>';
			$output .= '<option value="">-- '.(FLATS_INSTEAD_OF_HOTELS ? _SELECT_FLAT : _SELECT_HOTEL).' --</option>';
			$total_hotels = Hotels::GetAllActive();
			foreach($total_hotels[0] as $key => $val){
				$output .= '<option value="'.$val['id'].'" '.(($hotel_id == $val['id']) ? ' selected="selected"' : '').'>'.$val['name'].'</option>';
			}
			$output .= '</select> ';
			$output .= '<input type="submit" class="form_button" value="'._SHOW.'" />';
			$output .= '</div>';
			$output .= '</form>';
			$output .= '<div class="line-hor"></div>';
		}

		if(!empty($hotel_id)){
			$sql = 'SELECT
					r.id,
					r.max_adults,
					r.max_children,
					r.max_extra_beds,
					r.room_count,
					r.default_price,
					r.room_icon,
					IF(r.room_icon_thumb != "", r.room_icon_thumb, "no_image.png") as room_icon_thumb,
					r.priority_order,
					r.is_active,
					CONCAT("<a href=\"index.php?admin=mod_room_prices&rid=", r.id, "\" title=\"'._CLICK_TO_MANAGE.'\">", "[ '._PRICES.' ]", "</a>") as link_prices,
					CONCAT("<a href=\"index.php?admin=mod_room_availability&rid=", r.id, "\" title=\"'._CLICK_TO_MANAGE.'\">", "[ '._AVAILABILITY.' ]", "</a>") as link_room_availability,
					IF(r.is_active = 1, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as my_is_active,
					CONCAT("<a href=\"index.php?admin=mod_room_description&room_id=", r.id, "\" title=\"'._CLICK_TO_MANAGE.'\">[ ", "'._DESCRIPTION.'", " ]</a>") as link_room_description,
					rd.room_type,
					rd.room_short_description,
					rd.room_long_description,
					h.id as hotel_id,
					hd.name as hotel_name
				FROM '.TABLE_ROOMS.' r
					INNER JOIN '.TABLE_HOTELS.' h ON r.hotel_id = h.id
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id
					INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id
				WHERE
					'.(!empty($hotel_id) ? ' h.id = '.(int)$hotel_id.' AND ' : '').'
					h.is_active = 1 AND 
					r.is_active = 1 AND
					hd.language_id = \''.$lang.'\' AND
					rd.language_id = \''.$lang.'\'
				ORDER BY
					r.hotel_id ASC, 
					r.priority_order ASC';

			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			for($i=0; $i<$result[1]; $i++){
				$is_active = (isset($result[0][$i]['is_active']) && $result[0][$i]['is_active'] == 1) ? _AVAILABLE : _NOT_AVAILABLE;
				$href = prepare_link('rooms', 'room_id', $result[0][$i]['id'], $result[0][$i]['hotel_name'].'/'.$result[0][$i]['room_type'], $result[0][$i]['room_type'], '', '', true);

				if($i > 0) $output .= '<div class="line-hor"></div>';
				$output .= '<table width="100%" border="0">';
				$output .= '<tr valign="top">
							<td><h4><a href="'.$href.'" title="'._CLICK_TO_VIEW.'">'.$result[0][$i]['room_type'].'</a></h4></td>
							<td width="175px" align="center" rowspan="6">
								<a href="'.$href.'" title="'._CLICK_FOR_MORE_INFO.'"><img class="room_icon" src="images/rooms/'.$result[0][$i]['room_icon_thumb'].'" width="165px" alt="icon" /></a>
								'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).': '.prepare_link((FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels'), (FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid'), $result[0][$i]['hotel_id'], $result[0][$i]['hotel_name'], $result[0][$i]['hotel_name'], '', _CLICK_TO_VIEW).'
							</td>
							</tr>';
				$output .= '<tr><td>'.$result[0][$i]['room_short_description'].'</td></tr>';
				$output .= '<tr><td><b>'._COUNT.':</b> '.$result[0][$i]['room_count'].'</td></tr>';
				$output .= '<tr><td><b>'._MAX_ADULTS.':</b> '.$result[0][$i]['max_adults'].'</td></tr>';
				if($allow_children == 'yes') $output .= '<tr><td><b>'._MAX_CHILDREN.':</b> '.$result[0][$i]['max_children'].'</td></tr>';
				if($allow_extra_beds == 'yes' && !empty($result[0][$i]['max_extra_beds'])) $output .= '<tr><td><b>'._MAX_EXTRA_BEDS.':</b> '.$result[0][$i]['max_extra_beds'].'</td></tr>';
				//$output .= '<tr><td><b>'._DEFAULT_PRICE.':</b> '.Currencies::PriceFormat($default_price).'</td></tr>';
				$output .= '<tr><td><b>'._AVAILABILITY.':</b> '.$is_active.'</td></tr>';
				$output .= '</tr>';
				$output .= '</table>';
			}
		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Get max day for month
	 * @param $year
	 * @param $month
	 */
	private function GetMonthMaxDay($year, $month)
	{
		if(empty($month)) $month = date('m');
		if(empty($year)) $year = date('Y');
		$result = strtotime("{$year}-{$month}-01");
		$result = strtotime('-1 second', strtotime('+1 month', $result));
		return date('d', $result);
	}

    /**
     * Prepare room prices for current year
     * @param $rid
     * @param $selected_year
     * @return array
     */
    public function GetRoomPricesForYear($rid, $selected_year)
    {
		$room_prices = array();
		$one_day_to_unix = 24 * 60 * 60;

		$sql = 'SELECT *
				FROM '.TABLE_ROOMS_PRICES.'
				WHERE room_id = '.(int)$rid.' AND (is_default = 1 OR date_from >= '.$selected_year.'-01-01)
				ORDER BY is_default DESC, date_from ASC';

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i=0; $i<$result[1]; $i++){

				if($result[0][$i]['is_default'] == '1'){
					$unix_start_date = strtotime(date($selected_year.'-01-01'));
					$unix_end_date = strtotime(date($selected_year.'-12-31'));
				}else{
					$unix_start_date = strtotime($result[0][$i]['date_from']);
					$unix_end_date = strtotime($result[0][$i]['date_to']);
				}

				for($unix_current_date = $unix_start_date; $unix_current_date <= $unix_end_date; $unix_current_date += $one_day_to_unix){
					$current_date   = date('Y-m-d', $unix_current_date);
					$week_day       = date('D', $unix_current_date);
					$lower_week_day = strtolower($week_day);
					$price          = $result[0][$i][$lower_week_day];
					$room_prices[$current_date] = $price;
				}
			}
		}

		return $room_prices;
    }

    /**
     * Get hotel_id with best price
     */
    public function GetHotelIdBestPrice()
    {
        return $this->hotelIdBestPrice;
    }

    /**
     * Get best rooms
     */
    public function GetArrBestRooms()
    {
        return $this->arrBestRooms;
    }

    /**
     * Get best room count
     */
    public function GetArrBestRoomCount()
    {
        return $this->arrBestRoomCount;
    }

    /**
     * Get best room adults
     */
    public function GetArrBestRoomAdults()
    {
        return $this->arrBestRoomAdults;
    }

    /**
     * Get best price value
     */
    public function GetBestPriceValue()
    {
        return $this->bestPriceValue;
    }

    /**
     * Get best price value
     */
    public function GetArrBestPrices()
    {
        return $this->arrBestPrices;
    }

	/**
	 * Draws system suggestion form
	 * @param $room_id
	 * @param $checkin_day
	 * @param $checkin_year_month
	 * @param $checkout_day
	 * @param $checkout_year_month
	 * @param $max_adults
	 * @param $max_children
	 * @param $draw
	 */
	public static function DrawTrySystemSuggestionForm($room_id, $checkin_day, $checkin_year_month, $checkout_day, $checkout_year_month, $max_adults, $max_children, $draw = true)
	{
		$output = '';
		if($max_adults > 1){
			$allow_minimum_beds = ModulesSettings::Get('rooms', 'allow_minimum_beds');

			$output .= '<br>';
			$output .= '<form target="_parent" action="index.php?page=check_availability" method="post">';
			$output .= draw_hidden_field('room_id', $room_id, false);
			$output .= draw_hidden_field('p', '1', false, 'page_number');
			$output .= draw_token_field(false);
			if(CALENDAR_HOTEL != 'old') $output .= draw_hidden_field('hotel_sel_loc', isset($_POST['hotel_sel_loc']) ? $_POST['hotel_sel_loc'] : '', false);
			$output .= draw_hidden_field('checkin_monthday', $checkin_day, false);
			$output .= draw_hidden_field('checkin_year_month', $checkin_year_month, false);
			$output .= draw_hidden_field('checkout_monthday', $checkout_day, false);
			$output .= draw_hidden_field('checkout_year_month', $checkout_year_month, false);
			$output .= draw_hidden_field('max_adults', (int)($max_adults / 2), false);
			$output .= draw_hidden_field('max_children', (int)($max_children / 2), false);

			$output .= _TRY_SYSTEM_SUGGESTION.':<br>';
			$output .= '<input class="form_button" type="submit" value="'._CHECK_NOW.'" />';
			$output .= '</form>';
		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws rooms in specific hotel
	 * @param $hotel_id
	 * @param $draw
	 * @param $draw_header
	 */
	public static function DrawRoomsSideInHotel($hotel_id, $draw = true, $draw_header = true)
	{
		$output = '';

		$sql = 'SELECT
					r.id,
					r.room_count,
					rd.room_type 
				FROM '.TABLE_ROOMS.' r 
					LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id AND rd.language_id = \''.Application::Get('lang').'\'
				WHERE r.is_active = 1 AND hotel_id = '.(int)$hotel_id.'
				ORDER BY r.priority_order ASC ';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i=0; $i<$result[1]; $i++){
                if($i > 0) $output .= '<div class="line5"></div>';
                $output .= '<div class="cpadding1 ">';
                $output .= prepare_link('rooms', 'room_id', $result[0][$i]['id'], $result[0][$i]['room_type'], $result[0][$i]['room_type'], '', _CLICK_TO_VIEW);
                $output .= '</div>';
            }
		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws rooms in specific hotel
	 * @param $hotel_id
	 * @param $draw
	 * @param $draw_header
	 * @param $show_link
	 */
	public static function DrawRoomsInHotel($hotel_id, $draw = true, $draw_header = true, $show_link = true)
	{
		$output = '';

		$sql = 'SELECT
					r.id,
					r.room_count,
					rd.room_type 
				FROM '.TABLE_ROOMS.' r 
					LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id AND rd.language_id = \''.Application::Get('lang').'\'
				WHERE r.is_active = 1 AND hotel_id = '.(int)$hotel_id.'
				ORDER BY r.priority_order ASC ';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			if($draw_header) $output .= '<b>'._ROOMS.'</b>:<br>';
			$output .= '<ul>';
			for($i=0; $i<$result[1]; $i++){
				$output .= '<li> '.($show_link ? prepare_link('rooms', 'room_id', $result[0][$i]['id'], $result[0][$i]['room_type'], $result[0][$i]['room_type'], '', _CLICK_TO_VIEW).' - '.$result[0][$i]['room_count'] : $result[0][$i]['room_type'].' - '.$result[0][$i]['room_count']).' </li>';
			}
			$output .= '</ul>';
		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Returns room id in property
	 * @param $hotel_id
	 */
	public static function GetRoomInProperty($hotel_id)
	{
		$sql = 'SELECT
					r.id,
					r.room_count,
					rd.room_type 
				FROM '.TABLE_ROOMS.' r 
					LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id AND rd.language_id = \''.Application::Get('lang').'\'
				WHERE r.is_active = 1 AND hotel_id = '.(int)$hotel_id.'
				ORDER BY r.priority_order ASC
                LIMIT 0, 1';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
            return $result[0]['id'];
		}

		return 0;
	}

	/**
	 * Prepares hotel name field to fake autocomplete action
	 * @param array $record
	 * @param string $field_name
	 * @return HTML
	*/
	public static function PrepareHotelNameField($record = array(), $field_name = 'hotel_sel_loc')
	{
		$hotel_sel_loc = isset($record['name']) ? $record['name'] : '';
		$hotel_sel_loc .= isset($record['location_name']) ? (!empty($hotel_sel_loc) ? ', ' : '').$record['location_name'] : '';
		return  draw_hidden_field('hotel_sel_loc', $hotel_sel_loc, false);
	}

	/**
	 * Returns info about room price discounts
	 * @param array $room
	 * @return HTML
	*/
	public static function DrawRoomPricesNightDiscounts($room = array())
	{
		$arr_discount_types = array('0'=>_FIXED_PRICE, '1'=>_PERCENTAGE);

		$currency_info = Currencies::GetDefaultCurrencyInfo();
		$default_currency = $currency_info['symbol'];
		$currency_decimals = $currency_info['decimals'];

		$discount_type = isset($room['discount_night_type']) ? $room['discount_night_type'] : '';
		$discount_type_name = isset($room['discount_night_type']) ? $arr_discount_types[$room['discount_night_type']] : '';
		$discount_night_3 = isset($room['discount_night_3']) ? $room['discount_night_3'] : '';
		$discount_night_4 = isset($room['discount_night_4']) ? $room['discount_night_4'] : '';
		$discount_night_5 = isset($room['discount_night_5']) ? $room['discount_night_5'] : '';
		$output = '';

		if(!is_empty_number($discount_night_3) || !is_empty_number($discount_night_4) || !is_empty_number($discount_night_5)){
			$output = '<h3>'._LONG_TERM_STAY_DISCOUNT.'</h3>
				'._LONG_TERM_IF_YOU_BOOK_ROOM.':<br>
				<div class="hpadding20">
					<ul class="checklist">
					'.(!is_empty_number($discount_night_3) ? '<li>'._DISCOUNT_FOR_3RD_NIGHT.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_night_3, $currency_decimals, '.', ',') : number_format((float)$discount_night_3, 0).'%').'</li>' : '').'
					'.(!is_empty_number($discount_night_4) ? '<li>'._DISCOUNT_FOR_4TH_NIGHT.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_night_4, $currency_decimals, '.', ',') : number_format((float)$discount_night_4, 0).'%').'</li>' : '').'
					'.(!is_empty_number($discount_night_5) ? '<li>'._DISCOUNT_FOR_5TH_OR_MORE_NIGHTS.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_night_5, $currency_decimals, '.', ',') : number_format((float)$discount_night_5, 0).'%').'</li>' : '').'
					</ul>
				</div>
				<br>';
		}

		return $output;
	}

	/**
	 * Returns info about room price discounts for guests
	 * @param array $room
	 * @return HTML
	*/
	public static function DrawRoomPricesGuestsDiscounts($room = array())
	{
		$arr_discount_types = array('0'=>_FIXED_PRICE, '1'=>_PERCENTAGE);

		$currency_info = Currencies::GetDefaultCurrencyInfo();
		$default_currency = $currency_info['symbol'];
		$currency_decimals = $currency_info['decimals'];

		$discount_type = isset($room['discount_guests_type']) ? $room['discount_guests_type'] : '';
		$discount_type_name = isset($room['discount_guests_type']) ? $arr_discount_types[$room['discount_guests_type']] : '';
		$discount_guests_3 = isset($room['discount_guests_3']) ? $room['discount_guests_3'] : '';
		$discount_guests_4 = isset($room['discount_guests_4']) ? $room['discount_guests_4'] : '';
		$discount_guests_5 = isset($room['discount_guests_5']) ? $room['discount_guests_5'] : '';
		$output = '';

		if(!is_empty_number($discount_guests_3) || !is_empty_number($discount_guests_4) || !is_empty_number($discount_guests_5)){
			$output = '<h3>'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).'</h3>
				'._GUESTS_DISCOUNT_IF_YOU_BOOK_ROOM.':<br>
				<div class="hpadding20">
					<ul class="checklist">';
			if(TYPE_DISCOUNT_GUEST == 'guests'){
				$output .= (!is_empty_number($discount_guests_3) ? '<li>'._DISCOUNT_FOR_THREE_GUESTS.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_guests_3, $currency_decimals, '.', ',') : number_format((float)$discount_guests_3, 0).'%').'</li>' : '');
				$output .= (!is_empty_number($discount_guests_4) ? '<li>'._DISCOUNT_FOR_FOUR_GUESTS.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_guests_4, $currency_decimals, '.', ',') : number_format((float)$discount_guests_4, 0).'%').'</li>' : '');
				$output .= (!is_empty_number($discount_guests_5) ? '<li>'._DISCOUNT_FOR_FIVE_OR_MORE_GUESTS.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_guests_5, $currency_decimals, '.', ',') : number_format((float)$discount_guests_5, 0).'%').'</li>' : '');
			}else{
				$output .= (!is_empty_number($discount_guests_3) ? '<li>'._DISCOUNT_FOR_THREE_ROOMS.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_guests_3, $currency_decimals, '.', ',') : number_format((float)$discount_guests_3, 0).'%').'</li>' : '');
				$output .= (!is_empty_number($discount_guests_4) ? '<li>'._DISCOUNT_FOR_FOUR_ROOMS.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_guests_4, $currency_decimals, '.', ',') : number_format((float)$discount_guests_4, 0).'%').'</li>' : '');
				$output .= (!is_empty_number($discount_guests_5) ? '<li>'._DISCOUNT_FOR_FIVE_OR_MORE_ROOMS.' - '.($discount_type == '0' ? $default_currency.number_format((float)$discount_guests_5, $currency_decimals, '.', ',') : number_format((float)$discount_guests_5, 0).'%').'</li>' : '');
			}
			$output .= '
					</ul>
				</div>
				<br>';
		}

		return $output;
	}

	/**
	 * Returns info about room price discounts for guests
	 * @param array $room
	 * @return HTML
	*/
	public static function GetRoomRefundMoney($room = array())
	{
		$arr_refund_types = array('0'=>_FIRST_NIGHT, '1'=>_FIXED_PRICE, '2'=>_PERCENTAGE);

		$currency_info = Currencies::GetDefaultCurrencyInfo();
		$default_currency = $currency_info['symbol'];
		$currency_decimals = $currency_info['decimals'];

		$refund_type = isset($room['refund_money_type']) ? $room['refund_money_type'] : '';
		$refund_money_type = isset($room['refund_money_type']) ? $arr_refund_types[$room['refund_money_type']] : '';
		$refund_money_value = isset($room['refund_money_value']) ? $room['refund_money_value'] : '';
		$cancel_reservation_day = isset($room['cancel_reservation_day']) ? $room['cancel_reservation_day'] : ModulesSettings::Get('booking', 'customers_cancel_reservation');
		$output = '';

		if(!is_empty_number($refund_money_value) || $refund_type == 0){
			$output = '<h4>'._CANCELLATION_POLICY.'</h4>';
			$output .= '<div class="hpadding20">
			<ul class="checklist">';
				$output .= (!empty($cancel_reservation_day) ? '<li>'._DAYS_TO_CANCEL.' - '.$cancel_reservation_day.' '._DAYS_LC.'</li>' : '');

			///_REFUND_AMOUNT.':<br>
			if($refund_type != 0){
				$output .= '<li>'._SIZE_REFUND_BOOKING_ROOMS.' - '.($refund_type == '1' ? $default_currency.number_format((float)$refund_money_value, $currency_decimals, '.', ',') : number_format((float)$refund_money_value, 0).'%').'</li>';
			}else{
				$output .= '<li>'._REFUND_AMOUNT_FIRST_NIGHT.'</li>';
			}

			$output .= '</ul>
			</div>';
		}

		return $output;
	}

	/**
	 * Iteration counter for room
	 * @param array $room
	 * @return void
	*/
	public static function NumberViewsIteration($rid = 0)
	{
		if(!empty($rid) && is_numeric($rid)){
			$sql = 'UPDATE '.TABLE_ROOMS.' SET number_of_views = number_of_views + 1 WHERE id = '.(int)$rid;
			return database_void_query($sql);
		}
		return false;
	}

	/**
	 * Returns all array of all active rooms
	 * @param $where_clause
	 */
	public static function GetAllActive($where_clause = '')
	{
		$lang = Application::Get('lang');

		return self::GetAllRooms('r.is_active = 1'.(!empty($where_clause) ? ' AND '.$where_clause : ''));
	}

	/**
	 * Returns all array of all rooms
	 * @param $where_clause
	 * @param $join_hotels
	 */
	public static function GetAllRooms($where_clause = '', $join_hotels = true)
	{
		$lang = Application::Get('lang');

		$sql = 'SELECT
					r.*,
					rd.room_type,
					rd.room_short_description
				FROM '.TABLE_ROOMS.' r 
					INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id AND rd.language_id = \''.$lang.'\'
					'.($join_hotels ? 'INNER JOIN '.TABLE_HOTELS.' h ON r.hotel_id = h.id AND h.is_active = 1' : '').'
				WHERE 1 = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'	
				ORDER BY r.priority_order ASC';
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 * Recalculates room price according to stay perriod
	 * @param $room_price
	 * @param $day
	 * @param $room_id
	 * @param $room_info
	 * @return float
	*/
	public static function GetRoomPriceIncludeNightsDiscount($room_price, $day, $room_id, $room_info = array())
	{
		$is_debug = false;

		// Optimize SQL
		if(OPTIMIZE_SQL){
			if(!empty($room_info)) $room_default_info = $room_info;
		}else{
			$sql = 'SELECT * FROM '.TABLE_ROOMS.' WHERE id = '.(int)$room_id;
			$room_default_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		}

		// 0 - fixed price, 1 - percentage
		$discount_type = isset($room_default_info[0]['discount_night_type']) ? $room_default_info[0]['discount_night_type'] : 0;
		$discount_night_3 = isset($room_default_info[0]['discount_night_3']) ? $room_default_info[0]['discount_night_3'] : 0;
		$discount_night_4 = isset($room_default_info[0]['discount_night_4']) ? $room_default_info[0]['discount_night_4'] : 0;
		$discount_night_5 = isset($room_default_info[0]['discount_night_5']) ? $room_default_info[0]['discount_night_5'] : 0;

		if($is_debug){
			echo '<br>Day: '.$day.' | Room ID: '.$room_id.' | ';
			echo isset($room_default_info[0]['discount_night_'.$day]) ? 'Discount night: '.$room_default_info[0]['discount_night_'.$day].' | ' : '';
			echo ' Price:'.$room_price;
		}

		if($day == 3 && !empty($discount_night_3)){
			$room_price = $room_price - ($discount_type == 0 ? $discount_night_3 : $discount_night_3 * $room_price / 100);
		}elseif($day == 4 && !empty($room_default_info[0]['discount_night_4'])){
			$room_price = $room_price - ($discount_type == 0 ? $discount_night_4 : $discount_night_4 * $room_price / 100);
		}elseif($day >=5 && !empty($room_default_info[0]['discount_night_5'])){
			$room_price = $room_price - ($discount_type == 0 ? $discount_night_5 : $discount_night_5 * $room_price / 100);
		}

		$room_price = $room_price > 0 ? $room_price : 0;

		if($is_debug){
			echo ' Final: '.$room_price;
		}

		return $room_price;
	}

	/**
	 * Draws pagination links
	 * @param $total_pages
	 * @param $current_page
	 * @param $params
	 * @param $draw
	 */
	private function DrawPaginationLinks($total_pages, $current_page, $params, $draw = true)
	{
		global $objLogin;

		$output = '';

		// draw pagination links
		if($total_pages > 1){
			if($objLogin->IsLoggedInAsAdmin()){
				$output .= '<form action="index.php?page=check_availability" id="reservation-form" name="reservation-form" method="post">
				'.draw_hidden_field('p', '1', false, 'page_number').'
				'.draw_token_field(false).'
				'.draw_hidden_field('checkin_monthday', $params['from_day'], false, 'checkin_monthday').'
				'.draw_hidden_field('checkin_year_month', $params['from_year'].'-'.(int)$params['from_month'], false, 'checkin_year_month').'
				'.draw_hidden_field('checkout_monthday', $params['to_day'], false, 'checkout_monthday').'
				'.draw_hidden_field('checkout_year_month', $params['to_year'].'-'.(int)$params['to_month'], false, 'checkout_year_month');
			}

			$output .= '<div class="paging">';
			for($page_ind = 1; $page_ind <= $total_pages; $page_ind++){
				$output .= '<a class="paging_link" href="javascript:void(\'page|'.$page_ind.'\');" onclick="javascript:appFormSubmit(\'reservation-form\',\'page_number='.$page_ind.'\')">'.(($page_ind == $current_page) ? '<b>['.$page_ind.']</b>' : $page_ind).'</a> ';
			}
			$output .= '</div>';
			if($objLogin->IsLoggedInAsAdmin()) $output .= '<form>';
		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draw Hotel Info block
	 * @param $hotel_id
	 * @param $hotel_info
	 * @param $lang
	 * @param $detail_button
	 * @param $draw
	 * @param $params
	 */
	private function DrawHotelInfoBlock($hotel_id, $hotel_info = array(), $lang = '', $detail_button = true, $draw = true, $params = array())
	{
		global $objSettings, $objLogin;

		$currency_rate = Application::Get('currency_rate');
        if(empty($currency_rate)){
            $currency_info = Currencies::GetDefaultCurrencyInfo();
            $currency_rate = $currency_info['rate'];
        }
		$currency_format = get_currency_format();
		$template = $objSettings->GetTemplate() != '' ? $objSettings->GetTemplate() : Application::Get('template');
		$customer_id = $objLogin->GetLoggedID();
		$max_adults = isset($params['max_adults']) && (int)$params['max_adults'] > 1 ? (int)$params['max_adults'] : 1;

		$output = '';
		// 14.03.2018 - removed as unneeded - we pass it via function params
		// $hotel_info = Hotels::GetHotelFullInfo($hotel_id, (!empty($lang) ? $lang : Application::Get('lang')));
		if(!empty($hotel_info)){

			// Get info about wishlist
			$in_wishlist = Wishlist::GetHotelInfo($hotel_id, 'hotel_id', $customer_id);
            //$hotel_img = ($hotel_info['hotel_image'] != '' && file_exists($root_path.'images/hotels/'.$hotel_info['hotel_image'])) ? 'images/hotels/'.$hotel_info['hotel_image'] : 'images/hotels/no_image.png';
            $hotel_img = ($hotel_info['hotel_image'] != '') ? 'images/hotels/'.$hotel_info['hotel_image'] : 'images/hotels/no_image.png';
            //$hotel_name = prepare_link((FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels'), (FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid'), $hotel_info['id'], $hotel_info['name'], $hotel_info['name'], '', _CLICK_TO_SEE_DESCR);
            $hotel_name = '<a title="'._CLICK_TO_SEE_DESCR.'" href="index.php?page='.(FLATS_INSTEAD_OF_HOTELS ? 'flats&fid=' : 'hotels&hid=').$hotel_info['id'].'">'.$hotel_info['name'].'</a>';
            $hotel_stars = (($hotel_info['stars'] > 0) ? (int)$hotel_info['stars'] : '0');
            $hotel_viewed = (($hotel_info['number_of_views'] > 1) ? str_replace('{number}', $hotel_info['number_of_views'], _VIEWED_TIMES) : _VIEWED_TIME);
            // Select the minimum price in arrAvailableRooms since GetHotelFullInfo
            // returns the minimum price for selecting all available rooms, excluding filter
            $hotel_lowest_price = 0;
            for($i = 0, $max_count = count($this->arrAvailableRooms[$hotel_id]); $i < $max_count; $i++){
                if(empty($hotel_lowest_price) || $hotel_lowest_price > $this->arrAvailableRooms[$hotel_id][$i]['lowest_price_per_night']){
                    $hotel_lowest_price = $this->arrAvailableRooms[$hotel_id][$i]['lowest_price_per_night'];
                }
            }
            $hotel_descr = substr_by_word(str_replace(array('<br>', '<br />', '<p>', '</p>'), ' ', $hotel_info['description']), 300, true);
            $hotel_facilities  = isset($hotel_info['facilities']) ? unserialize($hotel_info['facilities']) : array();

			// prepare facilities array
			$total_facilities = RoomFacilities::GetAllActive();
			$arr_facilities = array();
			foreach($total_facilities[0] as $key => $val){
				$arr_facilities[$val['id']] = array('name'=>$val['name'], 'code'=>$val['code'], 'icon'=>$val['icon_image']);
			}

			// prepare reviews
			$reviews = Reviews::GetReviews($hotel_id);
			$total_reviews = $reviews[1];
			$average_rating = 0;
			for($i=0; $i<$total_reviews; $i++){
				$average_rating += $reviews[0][$i]['rating_cleanliness'] + $reviews[0][$i]['rating_room_comfort'] + $reviews[0][$i]['rating_location'] + $reviews[0][$i]['rating_service'] + $reviews[0][$i]['rating_sleep_quality'] + $reviews[0][$i]['rating_price'];
			}
			if($total_reviews > 0){
				$average_rating /= $total_reviews * 6;
			}

            $rating = '<img src="templates/'.$template.'/images/user-rating-'.round($average_rating).'.png" alt="user rating" />
				<br><span class="size11 grey">'.$total_reviews.' '.strtolower($total_reviews == 1 ? _REVIEW : _REVIEWS).'</span>';

			$campaing_small_banner = Campaigns::DrawCampaignSmallBanner($hotel_id, false);
            $output .= '<div class="offset-1">
				<div class="itemlabel3">';
					if(!empty($campaing_small_banner)){
						$output .= '<div class="campaing-msg">'.$campaing_small_banner.'</div>';
					}
					$output .= '<div class="hotel-info-block">';
					$output .= '<div class="col-md-4">';

					// Determine the best hotel
					if(SHOW_BEST_PRICE_ROOMS){
						$best_hotel = ($hotel_id == $this->hotelIdBestPrice) ? true : false;
						if($best_hotel){
							$output .= '<div class="label-tab success best-price-tab">'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT_WITH_BEST_PRICE : _HOTEL_WITH_BEST_PRICE).' <span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($this->bestPriceValue) * $currency_rate, '', '', $currency_format).'</b></span></div>';
						}elseif(!empty($this->arrBestPrices[$hotel_id])){
							$output .= '<div class="label-tab success best-price-tab"><span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($this->arrBestPrices[$hotel_id]) * $currency_rate, '', '', $currency_format).'</b></span></div>';
						}
					}
					if(empty($this->arrBestPrices) && $max_adults > 1){
						$output .= '<div class="label-tab success best-price-tab">'._BEST_PRICE.' <span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($hotel_lowest_price) * $currency_rate, '', '', $currency_format).' ('.$max_adults.' '._ADULTS.')</b></span></div>';
					}

					$output .= '<div class="listitem2">
							<a href="'.$hotel_img.'" data-footer="" data-title="" data-gallery="multiimages" data-toggle="lightbox"><img src="'.$hotel_img.'" alt="" /></a>
							<br />
							<div class="liover"></div>';

							// Draw wishlist icon
							if($in_wishlist){
								$output .= Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'remove', 'fav-icon', '&nbsp;');
							}else{
								$output .= Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'add', 'fav-icon', '&nbsp;');
							}

							$output .= '<a class="book-icon" href="index.php?page='.(FLATS_INSTEAD_OF_HOTELS ? 'flats&fid=' : 'hotels&hid=').$hotel_id.'"></a>
							</div>
						</div>
						<div class="col-md-8 pl0r15">
							<div class="labelright center ml10r0">
								<img src="templates/'.$template.'/images/filter-rating-'.$hotel_stars.'.png" alt="stars rating" width="60" />
								<br/><br/>
								'.$rating.'
								<br><br>
								<center>
								'.($hotel_lowest_price ? '<span class="green size18"><b>'.Currencies::PriceFormat($hotel_lowest_price * $currency_rate, '', '', $currency_format).'</b></span>' : '').'
								<br>
								'.($hotel_lowest_price ? '<span class="size11 grey">'._FROM_PER_NIGHT.'</span>' : '').'
								</center>
								<br/><br/>
								'.($detail_button ? '&nbsp;&nbsp;&nbsp;<button class="bookbtn mt1" type="button" onclick="jQuery(location).attr(\'href\',\'index.php?page='.(FLATS_INSTEAD_OF_HOTELS ? 'flats&fid=' : 'hotels&hid=').$hotel_id.'\')">'._DETAILS.'</button>' : '').'
							</div>
							<div class="labelleft2">';

								// Draw wishlist icon
								if($in_wishlist){
									$output .= '<span class="pull-right">'.Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'remove').'</span>';
								}

								$cut_chars = is_mobile() ? 120 : (count($hotel_facilities) > 12 ? 215 : 300);

								$output .= '<p><b class="hn_new">'.$hotel_name.'</b>
								'.$hotel_info['location_name'].' &nbsp;('.$hotel_viewed.')
								'.(!$hotel_info['is_active'] ? ' &nbsp;<label class="badge-red">'.htmlspecialchars(_INACTIVE).'</label>' : '').'
								</p>
								
								<p class="grey2">'.substr_by_word($hotel_descr, $cut_chars, true).'</p>
								<ul class="hotelpreferences'.(count($hotel_facilities) > 12 ? ' hp_new_many' : ' hp_new').'">';
									if(is_array($hotel_facilities)){
										foreach($hotel_facilities as $key => $val){
											if(isset($arr_facilities[$val])) $output .= '<li'.(empty($arr_facilities[$val]['icon']) ? ' class="icohp-'.$arr_facilities[$val]['code'].'"' : '').' title="'.htmlentities($arr_facilities[$val]['name']).'">'.(!empty($arr_facilities[$val]['icon']) ? '<img src="images/facilities/'.$arr_facilities[$val]['icon'].'"/>' : '').'</li>';
										}
									}
								$output .= '</ul>                                
							</div>
                        </div>
					</div>';
					$output .= '<div class="clearfix"></div>';
					$output .= '<div class="col-md-12">';

						$output .= '<div class="hotel-messages">';

						$hotel_last_booking = array();
						$last_bookings = Bookings::GetLastBookingForHotels(array($hotel_id));

						if($last_bookings[1] > 0){
							$output .= '<div class="hotel-last-booking"><div class="msg notice">';
							$unix_time_created = strtotime($last_bookings[0][0]['created_date']);
							$count_hours = (int)((time() - $unix_time_created) / 3600);
							if($count_hours < 24){
								$type_booking = $count_hours < 1 ? _JUST_BOOKED : ($count_hours > 1 ? $count_hours.' '._HOURS : _HOUR).' '._AGO;
							}else{
								$count_days = (int)($count_hours / 24);
								if($count_days < 366){
									$type_booking = strtolower(($count_days == 1 ? '1 '._DAY : $count_days.' '._DAYS).' '._AGO);
								}else{
									$type_booking = _MORE_YEAR_AGO;
								}
							}
							$message = str_replace('{type_booking}', $type_booking, (FLATS_INSTEAD_OF_HOTELS ? _LAST_BOOKING_FLAT_WAS : _LAST_BOOKING_HOTEL_WAS));
							$output .= '<p>'.$message.'</p>';
							$output .= '</div></div>';
						}
						$canceled_days = Hotels::GetCanceledDays($hotel_id, $params['from_date'], $params['to_date']);
						$output .= '<div class="hotel-canceled-days"><div class="msg notice">';
						$output .= '<p>'._DAYS_TO_CANCEL.' - '.$canceled_days.' '.($canceled_days > 1 ? _DAYS : _DAY).'</p>';
						$output .= '</div></div>';
						$output .= '</div>
					</div>
                </div>
            </div>';

		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Get best price for rooms (recursive function)
	 * @param array $rooms
	 * @param number $number_beds
	 * @return float|false
	 */
	private function GetBestPriceRooms($rooms, $number_beds)
	{
		if(!empty($number_beds) && !empty($rooms) && is_array($rooms)){
            $best_price = 0.00;
            // Beds are left free after you check in this step count
            $free_beds = 0;
            // The beds are reserved at this stage of calculating
            $reserve_beds = 0;
            // Flag determines whether to reserve a room in this step is necessary
            $reserve_room = true;
			$arr_select_room_ids = array();
			$arr_price_bed_rooms = array();
			$arr_count_rooms = array();
			$arr_rooms = array();
			$arr_adults = array();
			// We find the price per person
			foreach($rooms as $room){
				if($number_beds > $room['max_adults']){
					$arr_price_bed_rooms[$room['id']] = ($room['lowest_price_per_night'] * ceil($number_beds / $room['max_adults'])) / $number_beds;
				}else{
					$arr_price_bed_rooms[$room['id']] = $room['lowest_price_per_night'] / $number_beds;
				}
				$arr_rooms[$room['id']] = $room;
			}
			asort($arr_price_bed_rooms);

			reset($arr_price_bed_rooms);
			$best_room_id        = key($arr_price_bed_rooms);
			$max_adults_for_room = $arr_rooms[$best_room_id]['max_adults'];
			$available_rooms     = $arr_rooms[$best_room_id]['available_rooms'];
			$price_for_room      = $arr_rooms[$best_room_id]['lowest_price_per_night'];
            $price               = $price_for_room / $max_adults_for_room;

			if($max_adults_for_room <= $number_beds){
				$arr_adults[$best_room_id] = $max_adults_for_room;
			}else{
				$arr_adults[$best_room_id] = $number_beds;
			}

            if($number_beds > $max_adults_for_room){
                $number_beds = $number_beds - $max_adults_for_room;
                $reserve_beds = $max_adults_for_room;
            }else{
                $free_beds = $max_adults_for_room - $number_beds;
                $number_beds = 0;
                $reserve_beds = $number_beds;
            }
            if($available_rooms == 1){
                unset($arr_rooms[$best_room_id]);
            }else{
                $arr_rooms[$best_room_id]['available_rooms'] = $available_rooms - 1;
            }

			if(!empty($arr_rooms)){
				if($number_beds > 0){
					$result = $this->GetBestPriceRooms($arr_rooms, $number_beds);
					if(!empty($result)){
                        $best_price += !empty($result['best_price']) ? $result['best_price'] : 0.00;
                        // Check the number of available beds, and if possible, accommodating guests with this step
                        if($result['free_beds'] >= $reserve_beds){
                            $reserve_room = false;
                        }else{
                            $free_beds += $result['free_beds'];
                        }
						if(!empty($result['select_rooms'])){
							$arr_select_room_ids = array_merge($arr_select_room_ids, $result['select_rooms']);
						}
						if(!empty($result['count_rooms'])){
                            $arr_count_rooms = $result['count_rooms'];
						}
						if(!empty($result['adults'])){
                            if(!isset($result['adults'][$best_room_id]) || $result['adults'][$best_room_id] < $arr_adults[$best_room_id]){
                                $result['adults'][$best_room_id] = $arr_adults[$best_room_id];
                            }
                            $arr_adults = $result['adults'];
						}
					}else{
						// If the hotel is not possible to calculate the price - return false
						return false;
					}
				}
			}elseif($number_beds > 0){
				// If the hotel is not possible to calculate the price - return false
				return false;
            }
            if($reserve_room){
                $best_price += $price_for_room;
                if(!in_array($best_room_id, $arr_select_room_ids)){
                    $arr_select_room_ids[] = $best_room_id;
                }
                if(!empty($arr_count_rooms[$best_room_id])){
                    $arr_count_rooms[$best_room_id]++;
                }else{
                    $arr_count_rooms[$best_room_id] = 1;
                }
            }

            $result = array('best_price'=>$best_price, 'select_rooms'=>$arr_select_room_ids, 'count_rooms'=>$arr_count_rooms, 'adults'=>$arr_adults, 'free_beds'=>$free_beds);

            return $result;
		}

		return false;
	}

	/**
	 * Draw extra beds dropdownlist
	 * @param $room_id
	 * @param $max_extra_beds
	 * @param $params
	 * @param $currency_rate
	 * @param $currency_format
	 * @param $enabled
	 * @param $draw
	 */
	private function DrawExtraBedsDDL($room_id, $max_extra_beds, $params, $currency_rate, $currency_format, $enabled = true, $draw = true)
	{
		$extra_bed_price = self::GetRoomExtraBedsPrice($room_id, $params);
		$output = '<select class="form-control available_extra_beds_ddl" name="available_extra_beds" '.($enabled ? '' : 'disabled="disabled"').'>';
		$output .= '<option value="0">- ' . strtolower(_NO) . ' -</option>';
		for($i=0; $i<$max_extra_beds; $i++){
			$extra_beds_count = ($i+1);
			$extra_bed_charge_per_night = ($extra_beds_count * $extra_bed_price);
			$extra_bed_charge_per_night_format = Currencies::PriceFormat($extra_bed_charge_per_night * $currency_rate, '', '', $currency_format);
			$output .= '<option value="'.$extra_beds_count.'-'.$extra_bed_charge_per_night.'">'.$extra_beds_count.' ('.$extra_bed_charge_per_night_format.')</option>';
		}
		$output .= '</select>';

		if($draw) echo $output;
		else return $output;
	}

    /**
     * Draw occupancy calendar for specific room
     * @param $room_id
     * @param $draw
     */
	static public function DrawOccupancyCalendar($room_id = 0, $draw = true)
    {
        global $objSettings;

        ##  *** define a relative (virtual) path to calendar.class.php file
		if(!defined('CALENDAR_URL')){
	        define('CALENDAR_URL', get_base_url().'modules/calendar/');
		}
		if(!defined('CALENDAR_DIR')){
	        define('CALENDAR_DIR', 'modules/calendar/');
			require_once(CALENDAR_DIR.'calendar.class.php');
		}
        ## *** create calendar object
        $objCalendar = new Calendar(array(
            'used_on'		=> 'room',
            'room_types'	=> $room_id,
            'show_as'		=> 'reserved_and_completed',
			'month_number'	=> ModulesSettings::Get('rooms', 'show_rooms_occupancy_months')
        ));
		## *** show debug info - false|true
        $objCalendar->Debug(false);
		## *** show left rooms
		$objCalendar->shownRoomsType = 'left';
		## *** save http request variables between  calendar's sessions
        $objCalendar->SaveHttpRequestVars(array('page', 'hid'));
        ## *** set interface language (default - English)
        switch(Application::Get('lang')){
            case 'de':
            case 'es':
            case 'fr':
            case 'it':
            case 'pt':
                $objCalendar->SetInterfaceLang(Application::Get('lang')); break;
            default:
                $objCalendar->SetInterfaceLang('en');
                break;
        }
        ## *** set start day of the week: from 1 (Sunday) to 7 (Saturday)
        $objCalendar->SetWeekStartedDay($objSettings->GetParameter('week_start_day'));
        ## *** define showing a week number of the year
        $objCalendar->ShowWeekNumberOfYear(true);
        ## +-- Categories Actions & Operations ------------------------------------
        $objCalendar->SetCategoriesOperations(array());
        ##  *** allow multiple occurrences for events in the same time slot: false|true - default
        $objCalendar->SetEventsMultipleOccurrences(false);
        ##  *** set (allow) calendar events operations
        $objCalendar->SetEventsOperations(array());
        // set time zone according to time zone of hotel
        /// $time_zone = 'America/Los_Angeles';
        /// $objCalendar->SetTimeZone($time_zone);
        ## *** set (allow) calendar Views
        $objCalendar->SetCalendarViews(array('monthly_triple'=>true));
        ## *** set default calendar view - 'daily'|'weekly'|'monthly'|'yearly'|'list_view'|'monthly_small'
        $objCalendar->SetDefaultView('monthly_triple');
        ## +-- Calendar Actions -----------------------------------------------------
        ##  *** set (allow) calendar actions
        $objCalendar->SetCalendarActions(array('statistics'=>false,'printing'=>false));
        ## *** set CSS style: 'green'|'brown'|'blue' - default
        $objCalendar->SetCssStyle('blue');
        ## *** set calendar width and height
        $objCalendar->SetCalendarDimensions('100%', '290px');
        ## *** set type of displaying for events
        ## *** possible values for weekly  - 'inline'|'tooltip'
        ## *** possible values for monthly - 'inline'|'list'|'tooltip'
        $objCalendar->SetEventsDisplayType(array('weekly'=>'inline', 'monthly'=>'inline', 'yearly'=>'tooltip'));
        ## *** set Sunday color - true|false
        $objCalendar->SetSundayColor(true);

        ## *** drawing calendar
        ob_start();
        $objCalendar->Show();
        // save the contents of output buffer to the string
        $calendar_output = ob_get_contents();
        ob_end_clean();

        if($draw) echo $calendar_output;
        else return $calendar_output;
    }

	/**
	 * Filtering availability rooms by min/max number of rooms
	 * @param array $arr_availability
	 */
	private function FilterAvailabilityRoomsForMinMaxRooms($rooms_count, $arr_hotels, $arr_min_price, $min_max_hotels)
	{
		// Check for a minimum number of rooms
        $tmp_arr_available_rooms = array();
        foreach($this->arrAvailableRooms as $hotel_id => $info_hotel){
            $hotel_is_empty = false;
			$min_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['minimum_rooms']) ? $min_max_hotels[$hotel_id]['minimum_rooms'] : 0;
			$tmp_arr_available_rooms[$hotel_id] = $this->arrAvailableRooms[$hotel_id];

            if(TYPE_FILTER_TO_NUMBER_ROOMS == 'hotel'){
                $number_type_rooms_for_hotel = count($tmp_arr_available_rooms[$hotel_id]);
                $count_rooms_for_hotel = 0;

                foreach($this->arrAvailableRooms[$hotel_id] as $key => $val){
                    $count_rooms_for_hotel += $val['available_rooms'];
                }
                if(RESIDUE_ROOMS_IS_AVAILABILITY){
                    if($count_rooms_for_hotel == 0){
                        $rooms_count -= $number_type_rooms_for_hotel;
                        $hotel_is_empty = true;
                    }
                }else{
                    if($count_rooms_for_hotel < $min_rooms){
                        $rooms_count -= $number_type_rooms_for_hotel;
                        $hotel_is_empty = true;
                    }
                }
            }else{
                foreach($this->arrAvailableRooms[$hotel_id] as $key => $val){
                    if(RESIDUE_ROOMS_IS_AVAILABILITY){
                        if($val['available_rooms'] == 0){
                            unset($tmp_arr_available_rooms[$hotel_id][$key]);
                            $rooms_count--;
                        }
                    }else{
                        if($val['available_rooms'] < $min_rooms){
                            unset($tmp_arr_available_rooms[$hotel_id][$key]);
                            $rooms_count--;
                        }
                    }
                }
                if(count($tmp_arr_available_rooms[$hotel_id]) == 0){
                    $hotel_is_empty = true;
                }
            }

            if($hotel_is_empty){
                unset($tmp_arr_available_rooms[$hotel_id]);
                unset($arr_hotels[$hotel_id]);
                unset($arr_min_price[$hotel_id]);
			}else{
				// reset keys
				$tmp_arr_available_rooms[$hotel_id] = array_values($tmp_arr_available_rooms[$hotel_id]);
			}
        }
        if(!empty($arr_min_price)){
            $min_price_per_hotel = min($arr_min_price);
        }else{
            $min_price_per_hotel = 0;
        }
		$this->arrAvailableRooms = $tmp_arr_available_rooms;

		return array('rooms_count'=>$rooms_count, 'arr_hotels'=>$arr_hotels, 'arr_min_price'=>$arr_min_price, 'min_price_per_hotel'=>$min_price_per_hotel);
	}

    public function GetCountAvailabilityRoomsById($room_id, $checkin_date, $checkout_date)
    {
        $available_rooms = '0';
        $sql = 'SELECT
                    MAX('.TABLE_BOOKINGS_ROOMS.'.rooms) as max_booked_rooms,
                    '.TABLE_ROOMS.'.room_count
                FROM '.TABLE_BOOKINGS.'
                    INNER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
                    INNER JOIN '.TABLE_ROOMS.' ON '.TABLE_ROOMS.'.id = '.TABLE_BOOKINGS_ROOMS.'.room_id
                WHERE
                    ('.TABLE_BOOKINGS.'.status = 2 OR '.TABLE_BOOKINGS.'.status = 3) AND
                    '.TABLE_BOOKINGS_ROOMS.'.room_id = '.(int)$room_id.' AND
                    (
                        (\''.$checkin_date.'\' <= checkin AND \''.$checkout_date.'\' > checkin) 
                        OR
                        (\''.$checkin_date.'\' < checkout AND \''.$checkout_date.'\' >= checkout)
                        OR
                        (\''.$checkin_date.'\' >= checkin  AND \''.$checkout_date.'\' < checkout)
                    )';
        $rooms_booked = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($rooms_booked[1] > 0){
            $available_rooms = (int)$rooms_booked[0]['room_count'] - (int)$rooms_booked[0]['max_booked_rooms'];
        }

        return $available_rooms;
    }

    public static function GetFloors()
    {
        $floors = array(
            0 => _GROUND,
            1 => '1'._ST_FLOOR,
            2 => '2'._ST_FLOOR,
            3 => '3'._ST_FLOOR,
            4 => '4'._ST_FLOOR,
            5 => '5'._ST_FLOOR,
            6 => '6'._ST_FLOOR,
            7 => '7'._ST_FLOOR,
            8 => '8'._ST_FLOOR,
            9 => '9'._ST_FLOOR,
            10 => '10'._ST_FLOOR,
            11 => '11'._ST_FLOOR,
            12 => '12'._ST_FLOOR,
            13 => '13'._ST_FLOOR,
            14 => '14'._ST_FLOOR,
            15 => '15'._ST_FLOOR,
            16 => '16'._ST_FLOOR,
            17 => '17'._ST_FLOOR,
            18 => '18'._ST_FLOOR,
            19 => '19'._ST_FLOOR,
            20 => '20'._ST_FLOOR,
            21 => '21'._ST_FLOOR,
            22 => '22'._ST_FLOOR,
            23 => '23'._ST_FLOOR,
            24 => '24'._ST_FLOOR,
            25 => '25'._ST_FLOOR,
            26 => '26'._ST_FLOOR,
            27 => '27'._ST_FLOOR,
            28 => '28'._ST_FLOOR,
            29 => '29'._ST_FLOOR,
            30 => '30'._ST_FLOOR,
            31 => '31'._ST_FLOOR,
            32 => '32'._ST_FLOOR,
            33 => '33'._ST_FLOOR,
            34 => '34'._ST_FLOOR,
            35 => '35'._ST_FLOOR,
            36 => '36'._ST_FLOOR,
            37 => '37'._ST_FLOOR,
            38 => '38'._ST_FLOOR,
            39 => '39'._ST_FLOOR,
            40 => '40'._ST_FLOOR,
            41 => '41'._ST_FLOOR,
            42 => '42'._ST_FLOOR,
            43 => '43'._ST_FLOOR,
            44 => '44'._ST_FLOOR,
            45 => '45'._ST_FLOOR,
            46 => '46'._ST_FLOOR,
            47 => '47'._ST_FLOOR,
            48 => '48'._ST_FLOOR,
            49 => '49'._ST_FLOOR,
            50 => '50'._ST_FLOOR,
        );

        return $floors;
    }
    
	public function PrepareJsForSearch(){
		$output = '';
		if(HOTEL_BUTTON_RESERVE_AJAX){
			$output = '<script>
                var dialog = null, table, type, key;

                jQuery(document).ready(function(){
                    var $ = jQuery;

                    $(".form_button_middle").click(function(){
                        var objButton = this;
                        var token = "'.htmlentities(Application::Get('token')).'";
                        var room_id = $(this).data("room-id");
                        var type = $(this).data("type");
                        
                        if($(objButton).hasClass("ajax-working")){
                        	return false;
                        }
                        
                        if($(this).parents("form").length > 0){
                            var arr_input = $(this).parents("form").find("input").toArray();
                            arr_input = arr_input.concat($(this).parents("form").find("select").toArray());
                        }else{
                            var arr_input = $(this).parents("tr").find("input").toArray();
                            arr_input = arr_input.concat($(this).parents("tr").find("select").toArray());
                        }
                       
                        var data = {};
                        for(i = 0, max_count = arr_input.length; i < max_count; i++){
                            val = $(arr_input[i]).val();
                            key = $(arr_input[i]).attr("name");
                            if(key != undefined){
                                data[key] = val;
                            }
                        }
                        
                        data.act = "send";
                        data.type = type;

						if($(objButton).hasClass("ajax-working")){
							return false;
						}
						$(objButton).addClass("ajax-working");
						
                        jQuery.ajax({
                            url: "ajax/reserve_room.ajax.php",
                            global: false,
                            type: "POST",
                            data: (data),
                            dataType: "json",
                            async: true,
							beforeSend: function(){ $(objButton).addClass("ajax-working"); },
							complete: function(){ $(objButton).removeClass("ajax-working"); },
                            error: function(html){ console.log("AJAX: cannot connect to the server or server response error! Please try again later."); },
                            success: function(data){
                            	$(objButton).removeClass("ajax-working");
                                var error = data.error !== undefined ? data.error : false;
                                var message = data.message;
                                if(data.toastr_message != undefined){
                                    if(typeof toastr != "undefined"){
                                        toastr.remove();
                                    }
                                    if(data.toastr_message != ""){
                                        jQuery(document).ready(function(){
                                            if(typeof toastr != "undefined"){
                                                // Init
                                                toastr.options = {
                                                  "closeButton": true,
                                                  "debug": false,
                                                  "newestOnTop": false,
                                                  "progressBar": false,
                                                  "positionClass": "toast-bottom-left",
                                                  "preventDuplicates": false,
                                                  "onclick": null,
                                                  "showDuration": "300",
                                                  "hideDuration": "1000",
                                                  "timeOut": "0",
                                                  "extendedTimeOut": "0",
                                                  "showEasing": "swing",
                                                  "hideEasing": "linear",
                                                  "showMethod": "fadeIn",
                                                  "hideMethod": "fadeOut",
                                                  "rtl": '.(Application::Get('lang_dir') == 'rtl' ? 'true' : 'false').'
                                                }
                                
                                                toastr.success(data.toastr_message);
                                            }
                                        });
                                    }
                                }
                                if(message.length > 0){
                                    // dialog
                                    if(error == false){
                                        if(type == "reserve"){
                                            $(objButton).removeClass("green");
                                            $(objButton).addClass("red");
                                            $(objButton).data("type", "remove");
                                            $(objButton).val("'.htmlspecialchars(_REMOVE).'!");
                                        }else{
                                            $(objButton).removeClass("red");
                                            $(objButton).addClass("green");
                                            $(objButton).data("type", "reserve");
                                            $(objButton).val("'.htmlspecialchars(_RESERVE).'!");
                                        }
                                    }
                                    $("#dialog-message").html(message);
                                    dialog = jQuery( "#dialog-alert" ).dialog({
                                      autoOpen: true,
                                      width: 500,
                                      buttons: {
                                        "'.str_replace('"', '\\"', htmlspecialchars(_CONTINUE_RESERVATION)).'": function(){
                                            window.location.href = "index.php?page=booking";
                                        },
                                        "Ok": function() {
                                          dialog.dialog("close");
                                          if(type == "reserve" && typeof toastr != "undefined"){
											if(data.toastr_message != undefined){
                                              // Init
                                              toastr.options = {
                                                "closeButton": true,
                                                "debug": false,
                                                "newestOnTop": false,
                                                "progressBar": false,
                                                "positionClass": "toast-bottom-right",
                                                "preventDuplicates": false,
                                                "onclick": null,
                                                "showDuration": "300",
                                                "hideDuration": "1000",
                                                "timeOut": "0",
                                                "extendedTimeOut": "0",
                                                "showEasing": "swing",
                                                "hideEasing": "linear",
                                                "showMethod": "fadeIn",
                                                "hideMethod": "fadeOut",
                                                "rtl": '.(Application::Get('lang_dir') == 'rtl' ? 'true' : 'false').'
                                              }
											  toastr.success("'.str_replace('"', '\\"', _MESSAGE_INFO_LINK_BOOKING).'");
											}
                                          }
                                        }
                                      },
                                      open: function() {
                                        $(".ui-dialog-buttonpane").find("button:contains(\"Ok\")").css("float", "right");
                                        $(".ui-dialog-buttonset").css({"float": "left", "margin-left": "5px", "width":"100%"});
                                      }
                                    });
                                }
                            }
                        });
                        return false;
                    });
                });
              </script>
              <div id="dialog-alert" title="'.htmlspecialchars(_RESERVATION).'">
                  <p><span id="dialog-alert-image" class="ui-icon ui-icon-alert" style="display:none;float:left; margin:2px 5px 0px 0;"></span><span id="dialog-message" style="font-size:14px;"></span></p>
              </div>';
		}
		return $output;
		
	}


	public function PrepareJsForSearchSuggestion(){

	    $output = '
          <script>
              jQuery(document).ready(function(){
                    jQuery("#search_suggestion_reserve").click(function(){
                        var token = "'.htmlentities(Application::Get('token')).'";
                        var rtl = "'.(Application::Get('lang_dir') == 'rtl' ? 'true' : 'false').'";
                        checkAvailabilitySearchSuggestion(this, token, rtl);
                    });
              });
          </script>
          <div id="dialog-alert" title="'.htmlspecialchars(_RESERVATION).'">
              <p><span id="dialog-alert-image" class="ui-icon ui-icon-alert" style="display:none;float:left; margin:2px 5px 0px 0;"></span><span id="dialog-message" style="font-size:14px;"></span></p>
          </div>';

		return $output;

	}

    /**
     * @param int $count_nights
     * @param string $checkin_date
     * @param string $checkout_date
     * @param array $params
     * @return array
     */
	public function SearchSuggestion($count_nights = 0, $checkin_date = '', $checkout_date = '', $params = array()){

        $result = array();

        $checkin_year   = date('Y', strtotime($checkin_date));
        $checkin_month  = date('m', strtotime($checkin_date));
        $checkin_day    = date('d', strtotime($checkin_date));


        $checkout_year  = date('Y', strtotime($checkout_date));
        $checkout_month = date('m', strtotime($checkout_date));
        $checkout_day   = date('d', strtotime($checkout_date));

        $params['from_date']   = $checkin_date;
        $params['to_date']     = $checkout_date;
        $params['nights']      = $count_nights;
        $params['from_year']   = $checkin_year;
        $params['from_month']  = $checkin_month;
        $params['from_day']    = $checkin_day;
        $params['to_year']     = $checkout_year;
        $params['to_month']    = $checkout_month;
        $params['to_day']      = $checkout_day;

        $rooms_count = $this->SearchFor($params, array(), true);

        if(is_array($rooms_count) && $rooms_count['rooms'] > 0){
            $result                    = $rooms_count;
            $result['checkin_date']    = $checkin_date;
            $result['checkout_date']   = $checkout_date;
            $result['count_nights']    = $count_nights;
            $result['checkin_year']    = $checkin_year;
            $result['checkin_month']   = $checkin_month;
            $result['checkin_day']     = $checkin_day;
            $result['checkout_year']   = $checkout_year;
            $result['checkout_month']  = $checkout_month;
            $result['checkout_day']    = $checkout_day;
        }

        return $result;
	}

	/**
     *
     * @param string $hotel_id
     * @param string $relocation_type
     * @param array $params
     * @return array
     */
	public function CheckHotelOwnerRelocation($hotel_id = 0, $relocation_type = 'room'){

        $result = array();

        if (!empty($hotel_id) && !empty($relocation_type)) {
            $sql = "SELECT * FROM ".TABLE_ACCOUNTS." WHERE ".TABLE_ACCOUNTS.".account_type = 'hotelowner'";
            $hotel_owners = database_query($sql, DATA_ONLY, ALL_ROWS);

            if (!empty($hotel_owners) && is_array($hotel_owners)) {
                foreach ($hotel_owners as $hotel_owner) {
                    $hotel_ids = @explode(',', $hotel_owner['companies']);
                    if (!empty($hotel_ids) && is_array($hotel_ids)) {
                        foreach ($hotel_ids as $arr_hotel_id) {
                            if ($arr_hotel_id == $hotel_id) {
                                $result['hotel_owner_find'] = true;
                                if ($relocation_type == 'room' && $hotel_owner['room_relocation']) {
                                    $result['relocation'] = true;
                                } elseif ($relocation_type == 'hotel' && $hotel_owner['hotel_relocation']) {
                                    $result['relocation'] = true;
                                }
                                break(2);
                            }
                        }
                    }
                }
            }

        }

        return $result;
	}


 
}
