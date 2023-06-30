<?php

/**
 *	Hotels Class
 *  --------------
 *  Description : encapsulates Hotels class properties
 *  Updated	    : 28.02.2016
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllActive            ValidateTranslationFields
 *  __destruct              DrawAboutUs				CheckRecordAssigned
 *  BeforeInsertRecord      GetHotelInfo            UpdateNeedAssistance
 *  AfterInsertRecord       GetHotelFullInfo
 *  BeforeUpdateRecord      DrawLocalTime
 *  AfterUpdateRecord       DrawPhones
 *  BeforeDeleteRecord      HotelsCount
 *  AfterDeleteRecord       DrawHotelDescription
 *	BeforeEditRecord		DrawHotelsInfo
 *	BeforeDetailsRecord     DrawNeedAssistanceForm
 *                          DrawTopHotels
 *                          DrawMap
 *	                        GetSupportInfo
 *	                        GetSupportSideInfo
 *	                        GetCanceledDays
 *	                        DrawHotelsByGroup
 *	                        DrawHotelsBestRated
 *	                        GetHotelLowestPrice
 *	                        NumberViewsIteration
 *                          SendEmailNeedAssistance
 *
 **/


class Hotels extends MicroGrid {

	protected $debug = false;
	protected $assigned_to_hotels = '';

	private $arrTranslations = '';
	private $hotelOwner = false;
	private $regionalManager = false;
    private $currencyFormat;

	private static $arr_stars_vm = array(
		'0'=>_NONE,
		'1'=>'<img src="images/smallstar-1.png" alt="1" title="1-star hotel" />',
		'2'=>'<img src="images/smallstar-2.png" alt="2" title="2-stars hotel" />',
		'3'=>'<img src="images/smallstar-3.png" alt="3" title="3-stars hotel" />',
		'4'=>'<img src="images/smallstar-4.png" alt="4" title="4-stars hotel" />',
		'5'=>'<img src="images/smallstar-5.png" alt="5" title="5-stars hotel" />');

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		parent::__construct();

		global $objLogin;

		$this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner');
		$this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager');
		$allow_default_periods = ModulesSettings::Get('rooms', 'allow_default_periods');

		$this->params = array();

		## for standard fields

		if(isset($_POST['hotel_location_id']))   $this->params['hotel_location_id'] = prepare_input($_POST['hotel_location_id']);
		if(isset($_POST['property_type_id']))   $this->params['property_type_id'] = (int)$_POST['property_type_id'];
        if(isset($_POST['hotel_group_id']))   $this->params['hotel_group_id'] = (int)$_POST['hotel_group_id'];

		if(isset($_POST['phone']))                  $this->params['phone']                  = prepare_input($_POST['phone']);
		if(isset($_POST['fax']))                    $this->params['fax']                    = prepare_input($_POST['fax']);
		if(isset($_POST['email']))                  $this->params['email']                  = prepare_input($_POST['email']);
		if(isset($_POST['map_code']))               $this->params['map_code']               = prepare_input($_POST['map_code'], false, 'low');
		if(isset($_POST['latitude']))               $this->params['latitude']               = prepare_input($_POST['latitude']);
		if(isset($_POST['longitude']))              $this->params['longitude']              = prepare_input($_POST['longitude']);
		if(isset($_POST['latitude_center']))        $this->params['latitude_center']        = prepare_input($_POST['latitude_center']);
		if(isset($_POST['longitude_center']))       $this->params['longitude_center']       = prepare_input($_POST['longitude_center']);
		if(isset($_POST['distance_center']))        $this->params['distance_center']        = prepare_input($_POST['distance_center']);
		if(isset($_POST['time_zone']))              $this->params['time_zone']              = prepare_input($_POST['time_zone']);
		if(isset($_POST['priority_order']))         $this->params['priority_order']         = prepare_input($_POST['priority_order']);
		if(isset($_POST['number_of_views']))        $this->params['number_of_views']        = prepare_input($_POST['number_of_views']);
		if(isset($_POST['is_active']))              $this->params['is_active']              = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		if(isset($_POST['is_default']))             $this->params['is_default']             = (int)$_POST['is_default'];
		if(isset($_POST['stars']))                  $this->params['stars']                  = prepare_input($_POST['stars']);
		if(isset($_POST['agent_commision']))        $this->params['agent_commision']        = prepare_input($_POST['agent_commision']);
        if(isset($_POST['facilities']))             $this->params['facilities']             = prepare_input($_POST['facilities']);
        if(isset($_POST['cancel_reservation_day'])) $this->params['cancel_reservation_day'] = (int)$_POST['cancel_reservation_day'];
        if(isset($_POST['video_link']))             $this->params['video_link']             = prepare_input($_POST['video_link']);
        if(isset($_POST['room_relocation']))        $this->params['room_relocation']        = (int)$_POST['room_relocation']; else $this->params['room_relocation'] = '0';
        if(isset($_POST['hotel_relocation']))       $this->params['hotel_relocation']       = (int)$_POST['hotel_relocation']; else $this->params['hotel_relocation'] = '0';
        if(isset($_POST['registration_number']))    $this->params['registration_number']    = prepare_input($_POST['registration_number']);

        $allow_relocation_to_other_hotel = (ModulesSettings::Get('rooms', 'allow_relocation_to_other_hotel') == 'yes') ? true : false;
        $allow_relocation_to_other_room = (ModulesSettings::Get('rooms', 'allow_relocation_to_other_room') == 'yes') ? true : false;

        if ($this->hotelOwner) {
            $getLoggedID = $objLogin->GetLoggedID();
            $sql = "SELECT * FROM " . TABLE_ACCOUNTS . " WHERE " . TABLE_ACCOUNTS . ".id = " . $getLoggedID . " AND " . TABLE_ACCOUNTS . ".account_type = 'hotelowner'";
            $hotel_owner = database_query($sql, DATA_ONLY, ALL_ROWS);

            if (!empty($hotel_owner) && is_array($hotel_owner)) {

                if ($allow_relocation_to_other_hotel) {
                    $allow_relocation_to_other_hotel = $hotel_owner[0]['hotel_relocation'];
                }

                if ($allow_relocation_to_other_room) {
                    $allow_relocation_to_other_room = $hotel_owner[0]['room_relocation'];
                }
            }
        }

		## for checkboxes
		//$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

		## for images (not necessary)
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = prepare_input($_POST['icon']);
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing
		//}elseif (self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		## for files:
		// define nothing

		///$this->params['language_id'] = MicroGrid::GetParameter('language_id');

		//$this->uPrefix 		= 'prefix_';

		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_HOTELS; //
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=hotels_info';
		$this->actions      = array(
								'add'		=> ((($this->hotelOwner && !$objLogin->HasPrivileges('add_hotel_info')) || $this->regionalManager) ? false : true),
								'edit'		=> ($this->hotelOwner ? $objLogin->HasPrivileges('edit_hotel_info') : true),
								'details'	=> true,
								'delete'	=> (($this->hotelOwner && $objLogin->GetRegistrationType() == 'advanced') || ($this->hotelOwner && !$objLogin->HasPrivileges('edit_hotel_info')) || $this->regionalManager ? false : true));
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId = $objLogin->GetPreferredLang();
        $watermark = (ModulesSettings::Get('rooms', 'watermark') == 'yes') ? true : false;
        $watermark_text = ModulesSettings::Get('rooms', 'watermark_text');

		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		if($this->hotelOwner){
			$hotels = $objLogin->AssignedToHotels();
			$hotels_list = implode(',', $hotels);
			$this->assigned_to_hotels = !empty($hotels_list) ? ' AND '.$this->tableName.'.'.$this->primaryKey.' IN ('.$hotels_list.')' : ' AND 1 = 0';
		}elseif($this->regionalManager){
			$hotel_locations = AccountLocations::GetHotelLocations($objLogin->GetLoggedId());
			$hotel_locations_list = implode(',', $hotel_locations);
			$this->assigned_to_hotels = !empty($hotel_locations_list) ? ' AND '.$this->tableName.'.hotel_location_id IN ('.$hotel_locations_list.')' : ' AND 1 = 0';
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_hotels;

		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.priority_order ASC';

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = true;
		$this->arrExportingTypes = array('csv'=>true);

		///$this->isAggregateAllowed = false;
		///// define aggregate fields for View Mode
		///$this->arrAggregateFields = array(
		///	'field1' => array('function'=>'SUM', 'align'=>'center'),
		///	'field2' => array('function'=>'AVG', 'align'=>'center'),
		///);

		///$date_format = get_date_format('view');
		///$date_format_settings = get_date_format('view', true); /* to get pure settings format */
		///$date_format_edit = get_date_format('edit');
		///$datetime_format = get_datetime_format();
		///$time_format = get_time_format(); /* by default 1st param - shows seconds */
		///$currency_format = get_currency_format();
		$default_currency = Currencies::GetDefaultCurrency();
        $this->currencyFormat = get_currency_format();
		$this->hotelGetDistance = '[ <a href="javascript:Hotel_GetDistantionCenterPoint()">'._GET_DISTANCE.'</a> ]';

		// prepare locations array
		$total_hotels_locations = HotelsLocations::GetAllLocations();
		$arr_hotels_locations = array();
		$arr_hotels_locations_wm = array();

		foreach($total_hotels_locations[0] as $key => $val){
			// For regional managers we need only approved locations
			if($this->regionalManager && !in_array($val['id'], $hotel_locations)){
				continue;
			}
			$arr_hotels_locations[$val['country_name']][$val['id']] = $val['name'];
			$arr_hotels_locations_wm[$val['id']] = $val['name'].'<br><small>'.$val['country_name'].'</small>';
		}

		// prepare facilities array
		$total_facilities = RoomFacilities::GetAllActive();
		$arr_facilities = array();
		foreach($total_facilities[0] as $key => $val){
			$arr_facilities[$val['id']] = $val['name'];
		}

		// prepare properties array
        $property_types = HotelsPropertyTypes::GetHotelsPropertyTypes();
		foreach($property_types as $key => $val){
			$arr_property_types[$val['id']] = $val['name'];
		}

		// Prepare hotel reviews
		$sql = 'SELECT hotel_id, SUM(evaluation) as evaluation, COUNT(*) as cnt FROM '.TABLE_REVIEWS.' GROUP BY hotel_id';
		$reviews = database_query($sql, DATA_AND_ROWS);
		$reviewed_hotel_ids = array();
		foreach($reviews[0] as $review){
			$reviewed_hotel_ids[$review['hotel_id']] = round($review['evaluation'] / $review['cnt'], 1).' / '.$review['cnt'];
		}


        $arr_hotel_groups = array('1'=>_TOP, '2'=>_LAST_MINUTE, '3'=>_EARLY_BOOKING, '4'=>_HOT_DEALS, '5'=>_TODAY_TOP_DEALS, '6'=>_FEATURED_OFFERS);

		$arr_time_zones = get_timezones_array();

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types = array('0'=>_NO, '1'=>_YES);
		$arr_stars = array('0'=>_NONE, '1'=>'&lowast; (1)</span>', '2'=>'&lowast;&lowast; (2)', '3'=>'&lowast;&lowast;&lowast; (3)', '4'=>'&lowast;&lowast;&lowast;&lowast; (4)', '5'=>'&lowast;&lowast;&lowast;&lowast;&lowast; (5)');

		$arr_agent_commisions = array();
		for($i = 0; $i<=200; $i++){
			$ind = $i * 0.5;
			$arr_agent_commisions[(string)$ind] = $ind;
		}

		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_LOCATIONS  => array('table'=>$this->tableName, 'field'=>'hotel_location_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels_locations, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
			_PROPERTY_TYPES  => array('table'=>$this->tableName, 'field'=>'property_type_id', 'type'=>'dropdownlist', 'source'=>$arr_property_types, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
			_GROUP  => array('table'=>$this->tableName, 'field'=>'hotel_group_id', 'type'=>'dropdownlist', 'source'=>$arr_hotel_groups, 'sign'=>'=', 'width'=>'130px'),
		);

		// *** Channel manager
		// -----------------------------------------------------------------------------
		$channel_manager_enabled = false;
		if(Modules::IsModuleInstalled('channel_manager') && (ModulesSettings::Get('channel_manager', 'is_active') != 'no')){
			$channel_manager_enabled = true;
		}

		// *** Property Management
		// -----------------------------------------------------------------------------
		$property_management_enabled = false;
		if(Modules::IsModuleInstalled('property_management') && (ModulesSettings::Get('property_management', 'is_active') != 'no')){
			$property_management_enabled = true;
		}

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'name_center_point', 'address', 'description', 'preferences')
		);
		///////////////////////////////////////////////////////////////////////////////

		///////////////////////////////////////////////////////////////////////////////
		// #003. prepare translations array for add/edit/detail modes
		/// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		/// $sql_translation_description = $this->PrepareTranslateSql(
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_HOTELS_DESCRIPTION,
			'hotel_id',
			array('name', 'name_center_point', 'address', 'description', 'preferences')
		);
		///////////////////////////////////////////////////////////////////////////////


		//----------------------------------------------------------------------
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//----------------------------------------------------------------------
		$this->VIEW_MODE_SQL = 'SELECT
				'.$this->tableName.'.'.$this->primaryKey.',
				'.$this->tableName.'.'.$this->primaryKey.' as hotel_id,
				'.$this->tableName.'.hotel_location_id,
				'.$this->tableName.'.property_type_id,
				'.$this->tableName.'.hotel_group_id,
				'.$this->tableName.'.phone,
				'.$this->tableName.'.fax,
				'.$this->tableName.'.email,
				'.$this->tableName.'.time_zone,
				'.$this->tableName.'.map_code,
				'.$this->tableName.'.latitude,
				'.$this->tableName.'.longitude,
				'.$this->tableName.'.latitude_center,
				'.$this->tableName.'.longitude_center,
				'.$this->tableName.'.hotel_image_thumb,
				'.$this->tableName.'.stars,
				'.$this->tableName.'.is_default,
				'.$this->tableName.'.is_active,
				'.$this->tableName.'.priority_order,
				'.$this->tableName.'.number_of_views,
				'.$this->tableName.'.cancel_reservation_day,
				CONCAT("<a href=index.php?admin=hotel_default_periods&hid=", '.$this->tableName.'.'.$this->primaryKey.', ">[ '._DEFINE.' ]</a>") as link_default_periods,
				CONCAT("<a href=\"javascript:void();\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_rooms_management\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_'.DB_PREFIX.'roomshotel_id=", '.$this->tableName.'.'.$this->primaryKey.', "\',\'post\')\"",">[ '._ROOMS.' ]</a>  (", (SELECT COUNT(*) as cnt FROM '.TABLE_ROOMS.' r WHERE r.hotel_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_rooms,
				CONCAT("<a href=index.php?admin=hotel_upload_images&hid=", '.$this->tableName.'.'.$this->primaryKey.', ">[ '._UPLOAD.' ]</a> (", (SELECT COUNT(*) as cnt FROM '.TABLE_HOTEL_IMAGES.' hi WHERE hi.hotel_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_upload_images,
				'.($channel_manager_enabled ? 'CONCAT("<a href=index.php?admin=mod_channel_manager_hotels&hid=", '.$this->tableName.'.'.$this->primaryKey.', ">[ '._SETTINGS.' ]</a>") as link_channel_manager,' : '').'
				'.($property_management_enabled ? 'CONCAT("<a href=\"javascript:void();\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_property_management_inventory\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_'.DB_PREFIX.'property_inventoryhotel_id=", '.$this->tableName.'.'.$this->primaryKey.', "\',\'post\')\"",">[ '._MANAGE.' ]</a>") as link_property_management,' : '').'
				CONCAT('.TABLE_HOTELS_DESCRIPTION.'.name, "<a href=\"index.php?page=hotels&hid=", '.$this->tableName.'.id, "&preview=yes\" target=\"_blank\" title=\"Open in new window\"> <img src=\"images/external_link.gif\" alt=\"\" /></a>") as hotel_name,
				'.TABLE_HOTELS_DESCRIPTION.'.name,
				'.TABLE_HOTELS_DESCRIPTION.'.name_center_point
			FROM ('.$this->tableName.'
				LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.$this->tableName.'.id = '.TABLE_HOTELS_DESCRIPTION.'.hotel_id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$this->languageId.'\')
			';

		// define view mode fields
		$this->arrViewModeFields = array(
			'hotel_image_thumb' 	=> array('title'=>_IMAGE, 'type'=>'image', 'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'image_width'=>'50px', 'image_height'=>'30px', 'target'=>'images/hotels/', 'no_image'=>'no_image.png'),
			'hotel_name'    		=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			//'name'  			=> array('title'=>_NAME, 'type'=>'link',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'header_tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'href'=>'index.php?page=hotels&hid={id}&preview=yes', 'target'=>'_blank'),
			'hotel_location_id' 	=> array('title'=>_LOCATION, 'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels_locations_wm),
			'property_type_id' 		=> array('title'=>_TYPE, 'type'=>'enum',  'align'=>'left', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_property_types),
			'hotel_group_id' 		=> array('title'=>_GROUP, 'type'=>'enum',  'align'=>'left', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>($this->hotelOwner ? false : true), 'source'=>$arr_hotel_groups),
			//'phone'   			=> array('title'=>_PHONE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			//'fax'     			=> array('title'=>_FAX, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'stars'					=> array('title'=>_STARS, 'type'=>'enum',  'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>($this->hotelOwner ? false : true), 'source'=>self::$arr_stars_vm),
			'hotel_id'				=> array('title'=>_RATINGS, 'type'=>'enum',  'header_tooltip'=>htmlentities(_RATE.' / '._REVIEWS), 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$reviewed_hotel_ids),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'55px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
			'is_default'     		=> array('title'=>_DEFAULT, 'type'=>'enum',  'align'=>'center', 'width'=>'55px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>($this->hotelOwner ? false : true), 'source'=>$arr_default_types_vm),
			'priority_order' 		=> array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'55px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>($this->hotelOwner ? false : true), 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),
			'number_of_views' 		=> array('title'=>_VIEWS,  'type'=>'label', 'align'=>'center', 'width'=>'55px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'movable'=>false),
			'link_rooms' 			=> array('title'=>_ROOMS, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>false, 'visible'=>true),
			'link_upload_images'  	=> array('title'=>_IMAGES, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>false),
			'link_default_periods' 	=> array('title'=>_PERIODS, 'type'=>'label', 'align'=>'center', 'width'=>'70px', 'sortable'=>false, 'visible'=>(($allow_default_periods == 'yes' && ! $this->regionalManager) ? true : false)),
		);

		if($channel_manager_enabled){
			$this->arrViewModeFields['link_channel_manager'] = array('title'=>_CHANNEL_MANAGER, 'type'=>'label', 'align'=>'center', 'width'=>'70px', 'sortable'=>false, 'visible'=>($channel_manager_enabled ? true : false));
		}
		if($property_management_enabled){
			$this->arrViewModeFields['link_property_management'] = array('title'=>_PROPERTY_MANAGEMENT, 'type'=>'label', 'align'=>'center', 'width'=>'70px', 'sortable'=>false, 'visible'=>($property_management_enabled ? true : false));
		}
		$this->arrViewModeFields['id'] = array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'30px');


		//----------------------------------------------------------------------
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//----------------------------------------------------------------------
		// define add mode fields
		$this->arrAddModeFields = array(

			'separator_1'   =>array(
				'separator_info'            => array('legend'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_INFO : _HOTEL_INFO, 'columns'=>'0'),
				'phone'                     => array('title'=>_PHONE, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
				'fax'                       => array('title'=>_FAX, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
				'email'                     => array('title'=>_EMAIL_ADDRESS,'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'header_tooltip'=>_HOTEL_EMAIL_ALERT, 'maxlength'=>'70', 'validation_type'=>'email', 'unique'=>true, 'post_html'=>' !'),
                'property_type_id'          => array('title'=>_PROPERTY_TYPE, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'0',  'source'=>$arr_property_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'hotel_group_id'            => array('title'=>_GROUP, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'0',  'source'=>$arr_hotel_groups, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false, 'visible'=>($this->hotelOwner ? false : true)),
                'registration_number'       => array('title'=>_HOTEL_REGISTRATION_NUMBER, 'type'=>'textbox', 'width'=>'210px', 'required'=>false,  'maxlength'=>'50', 'validation_maxlength'=>'50', 'validation_type'=>'text', 'unique'=>false),
                'stars'                     => array('title'=>_STARS, 'type'=>'enum',     'width'=>'',      'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_stars, 'default_option'=>'0', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false, 'visible'=>($this->hotelOwner ? false : true)),
				'time_zone'                 => array('title'=>_TIME_ZONE, 'type'=>'enum',  'required'=>true, 'width'=>'480px', 'readonly'=>false, 'source'=>$arr_time_zones),
				'hotel_image'               => array('title'=>_IMAGE, 'type'=>'image',    'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/hotels/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'hotel_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>true, 'thumbnail_field'=>'hotel_image_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
                'agent_commision'           => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLATS_AGENT_COMMISION : _AGENT_COMMISION, 'type'=>'enum', 'required'=>false, 'width'=>'80px', 'readonly'=>false, 'source'=>$arr_agent_commisions, 'default_option'=>false, 'post_html'=>' %', 'visible'=>($this->hotelOwner ? false : true)),
                'cancel_reservation_day'    => array('title'=>_CANCELLATION_POLICY, 'type'=>'textbox',  'width'=>'40px', 'required'=>false, 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric|positive', 'post_html'=>' '._DAYS),
				'priority_order'            => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>($this->hotelOwner ? false : true)),
				'views'                     => array('title'=>_VIEWS, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>($this->hotelOwner ? false : true)),
				'video_link'                => array('title'=>_VIDEO_LINK, 'type'=>'textbox', 'required'=>false, 'header_tooltip'=>_VIDEO_LINK_TOOLTIP, 'width'=>'500px', 'readonly'=>false, 'maxlength'=>'125', 'default'=>'', 'validation_type'=>'text', 'unique'=>false, 'visible'=>true),
                'room_relocation'           => array('title'=>_MS_ROOM_RELOCATION.' <img src="images/microgrid_icons/question.png" class="help" title="'._MS_ALLOW_RELOCATION_TO_OTHER_ROOM.'" alt="tooltip">',	'type'=>($allow_relocation_to_other_hotel ? 'checkbox' : 'hidden'), 'readonly'=>false, 'default'=>($allow_relocation_to_other_hotel ? '1' : '0'), 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
                'hotel_relocation'          => array('title'=>_MS_HOTEL_RELOCATION.' <img src="images/microgrid_icons/question.png" class="help" title="'._MS_ALLOW_RELOCATION_TO_OTHER_HOTEL.'" alt="tooltip">',	'type'=>($allow_relocation_to_other_room ? 'checkbox' : 'hidden'), 'readonly'=>false, 'default'=>($allow_relocation_to_other_room ? '1' : '0'), 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
				'is_default'                => array('title'=>_IS_DEFAULT, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>($this->regionalManager || $this->hotelOwner ? false : true)),
				'is_active'                 => array('title'=>_ACTIVE, 'type'=>($this->hotelOwner ? 'hidden' : 'checkbox'), 'readonly'=>false, 'default'=>($this->hotelOwner ? '0' : '1'), 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>($this->regionalManager ? false : true)),
			),

            'separator_4'   =>array(
                'separator_info' => array('legend'=>_LOCATION_AND_MAP),
                'hotel_location_id' => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels_locations, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'latitude'          => array('title'=>_LATITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
                'longitude'         => array('title'=>_LONGITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
                'map_code'          => array('title'=>_MAP_CODE, 'type'=>'textarea', 'header_tooltip'=>_MAP_CODE_TOOLTIP, 'required'=>false, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
            ),
			'separator_2'   =>array(
				'separator_info'    => array('legend'=>_COORDINATES_CENTER_POINT),
				'latitude_center'   => array('title'=>_CENTRAL_POINT_LATITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
				'longitude_center'  => array('title'=>_CENTRAL_POINT_LONGITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
				'distance_center'   => array('title'=>_DISTANCE_TO_CENTER_POINT, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false, 'javascript_event'=>'', 'post_html'=>' '._KILOMETERS_SHORTENED.' '.$this->hotelGetDistance),
			),
			'separator_3'   =>array(
				'separator_info' => array('legend'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_FACILITIES : _HOTEL_FACILITIES),
				'facilities'     => array('title'=>_FACILITIES, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_facilities, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true),
			),
		);

		//----------------------------------------------------------------------
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		// - for editable passwords they must be defined directly in SQL : '.$this->tableName.'.user_password,
		//----------------------------------------------------------------------
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.'.$this->primaryKey.' as hotel_id,
								'.$this->tableName.'.stars,
								'.$this->tableName.'.hotel_location_id,
                                '.$this->tableName.'.property_type_id,
                                '.$this->tableName.'.hotel_group_id,
                                '.$this->tableName.'.registration_number,
								'.$this->tableName.'.phone,
								'.$this->tableName.'.fax,
								'.$this->tableName.'.email,
								'.$this->tableName.'.time_zone,
								'.$this->tableName.'.hotel_image,
								'.$this->tableName.'.hotel_image_thumb,
								'.$this->tableName.'.map_code,
								'.$this->tableName.'.latitude,
								'.$this->tableName.'.longitude,
								'.$this->tableName.'.latitude_center,
								'.$this->tableName.'.longitude_center,
								'.$this->tableName.'.distance_center,
								'.$this->tableName.'.stars,
                                '.$this->tableName.'.facilities,
								'.$this->tableName.'.cancel_reservation_day,
								'.$this->tableName.'.agent_commision,
								'.$sql_translation_description.'
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.number_of_views,
								'.$this->tableName.'.video_link,
								'.$this->tableName.'.room_relocation,
								'.$this->tableName.'.hotel_relocation,
								'.$this->tableName.'.is_active,
								'.$this->tableName.'.is_default
							FROM '.$this->tableName.'
							WHERE
								'.$this->tableName.'.'.$this->primaryKey.' = _RID_';

		// prepare trigger
		$sql = 'SELECT is_default FROM '.$this->tableName.' WHERE id = '.(int)self::GetParameter('rid');
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		$is_default = '0';
		if($result[1] > 0){
			$is_default = (isset($result[0]['is_default'])) ? $result[0]['is_default'] : '0';
		}

		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_1'   => array(
				'separator_info'            => array('legend'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_INFO : _HOTEL_INFO, 'columns'=>'0'),
				'phone'                     => array('title'=>_PHONE, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
				'fax'                       => array('title'=>_FAX, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
				'email'                     => array('title'=>_EMAIL_ADDRESS,'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'header_tooltip'=>_HOTEL_EMAIL_ALERT, 'maxlength'=>'70', 'validation_type'=>'email', 'unique'=>true, 'post_html'=>' !'),
                'property_type_id'          => array('title'=>_PROPERTY_TYPE, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'0',  'source'=>$arr_property_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'hotel_group_id'            => array('title'=>_GROUP, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'',  'source'=>$arr_hotel_groups, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false, 'visible'=>($this->hotelOwner ? false : true)),
                'registration_number'       => array('title'=>_HOTEL_REGISTRATION_NUMBER, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'50', 'validation_maxlength'=>'50', 'validation_type'=>'text', 'unique'=>false),
                'stars'                     => array('title'=>_STARS, 'type'=>'enum', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_stars, 'default_option'=>'0', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'visible'=>($this->hotelOwner ? false : true)),
				'time_zone'                 => array('title'=>_TIME_ZONE, 'type'=>'enum', 'required'=>true, 'width'=>'480px', 'readonly'=>false, 'source'=>$arr_time_zones),
				'hotel_image'               => array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/hotels/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>true, 'image_name_pefix'=>'hotel_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>true, 'thumbnail_field'=>'hotel_image_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text),
                'agent_commision'           => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLATS_AGENT_COMMISION : _AGENT_COMMISION, 'type'=>'enum', 'required'=>false, 'width'=>'80px', 'readonly'=>false, 'source'=>$arr_agent_commisions, 'default_option'=>false, 'post_html'=>' %', 'visible'=>($this->hotelOwner ? false : true)),
                'cancel_reservation_day'    => array('title'=>_CANCELLATION_POLICY, 'type'=>'textbox',  'width'=>'40px', 'required'=>false, 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric|positive', 'post_html'=>' '._DAYS),
				'number_of_views'           => array('title'=>_VIEWS, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>($this->hotelOwner ? false : true)),
				'priority_order'            => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>($this->hotelOwner ? false : true)),
                'video_link'                => array('title'=>_VIDEO_LINK, 'type'=>'textbox', 'required'=>false, 'header_tooltip'=>_VIDEO_LINK_TOOLTIP, 'width'=>'500px', 'readonly'=>false, 'maxlength'=>'125', 'default'=>'', 'validation_type'=>'text', 'unique'=>false, 'visible'=>true),
                'room_relocation'           => array('title'=>_MS_ROOM_RELOCATION.' <img src="images/microgrid_icons/question.png" class="help" title="'._MS_ALLOW_RELOCATION_TO_OTHER_ROOM.'" alt="tooltip">', 'type'=>($allow_relocation_to_other_hotel ? 'checkbox' : 'hidden'), 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
                'hotel_relocation'          => array('title'=>_MS_HOTEL_RELOCATION.' <img src="images/microgrid_icons/question.png" class="help" title="'._MS_ALLOW_RELOCATION_TO_OTHER_HOTEL.'" alt="tooltip">', 'type'=>($allow_relocation_to_other_room ? 'checkbox' : 'hidden'), 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
                'is_default'                => array('title'=>_IS_DEFAULT, 'type'=>'checkbox', 'readonly'=>(($is_default) ? true : false), 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>(($this->hotelOwner || $this->regionalManager) ? false : true)),
				'is_active'                 => array('title'=>_ACTIVE, 'type'=>($this->hotelOwner ? 'hidden' : 'checkbox'), 'readonly'=>(($is_default) ? true : false), 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>($this->regionalManager ? false : true)),
			),
            'separator_4'   =>array(
                'separator_info' => array('legend'=>_LOCATION_AND_MAP),
                'hotel_location_id'         => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels_locations, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'latitude'                  => array('title'=>_LATITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
                'longitude'                 => array('title'=>_LONGITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
                'map_code'                  => array('title'=>_MAP_CODE, 'type'=>'textarea', 'required'=>false, 'header_tooltip'=>_MAP_CODE_TOOLTIP, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
            ),
			'separator_2'   =>array(
				'separator_info' => array('legend'=>_COORDINATES_CENTER_POINT),
				'latitude_center'  => array('title'=>_CENTRAL_POINT_LATITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
				'longitude_center' => array('title'=>_CENTRAL_POINT_LONGITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
				'distance_center' => array('title'=>_DISTANCE_TO_CENTER_POINT, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false, 'javascript_event'=>'', 'post_html'=>' '._KILOMETERS_SHORTENED.' '.$this->hotelGetDistance),
			),
			'separator_3'   =>array(
				'separator_info' => array('legend'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_FACILITIES : _HOTEL_FACILITIES),
				'facilities'     => array('title'=>_FACILITIES, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_facilities, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true),
			),
		);

		//----------------------------------------------------------------------
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_1'   =>array(
				'separator_info'            => array('legend'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_INFO : _HOTEL_INFO, 'columns'=>'0'),
				'phone'               		=> array('title'=>_PHONE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
				'fax'                 		=> array('title'=>_FAX, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
				'email'                     => array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
				'hotel_location_id'         => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'source'=>$arr_hotels_locations),
                'property_type_id'          => array('title'=>_PROPERTY_TYPE, 'type'=>'enum', 'source'=>$arr_property_types),
                'hotel_group_id'            => array('title'=>_GROUP, 'type'=>'enum', 'source'=>$arr_hotel_groups, 'visible'=>($this->hotelOwner ? false : true)),
                'registration_number'       => array('title'=>_HOTEL_REGISTRATION_NUMBER, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'stars'                     => array('title'=>_STARS, 'type'=>'enum', 'source'=>self::$arr_stars_vm, 'visible'=>($this->hotelOwner ? false : true)),
				'hotel_id'                  => array('title'=>_RATINGS.' ('.htmlentities(_RATE.' / '._REVIEWS).')', 'type'=>'enum', 'source'=>$reviewed_hotel_ids),
				'time_zone'                 => array('title'=>_TIME_ZONE, 'type'=>'enum', 'source'=>$arr_time_zones),
				'hotel_image'               => array('title'=>_IMAGE, 'type'=>'image', 'target'=>'images/hotels/', 'no_image'=>'no_image.png', 'image_width'=>'120px', 'image_height'=>'90px', 'visible'=>true),
				'map_code'                  => array('title'=>_MAP_CODE, 'type'=>'html', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
				'latitude'                  => array('title'=>_LATITUDE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
				'longitude'                 => array('title'=>_LONGITUDE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'agent_commision'           => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLATS_AGENT_COMMISION : _AGENT_COMMISION, 'type'=>'label', 'post_html'=>' %', 'visible'=>($this->hotelOwner ? false : true)),
				'cancel_reservation_day'    => array('title'=>_CANCELLATION_POLICY, 'type'=>'label', 'header_tooltip'=>_DAYS_TO_CANCEL_TOOLTIP, 'post_html'=>' '._DAYS),
				'number_of_views'           => array('title'=>_VIEWS, 'type'=>'label'),
				'priority_order'            => array('title'=>_ORDER, 'type'=>'label', 'visible'=>($this->hotelOwner ? false : true)),
				'video_link'                => array('title'=>_VIDEO_LINK, 'type'=>'label'),
				'room_relocation'           => array('title'=>_MS_ROOM_RELOCATION, 'type'=>'enum', 'source'=>$arr_default_types_vm, 'visible'=>$allow_relocation_to_other_hotel),
				'hotel_relocation'          => array('title'=>_MS_HOTEL_RELOCATION, 'type'=>'enum', 'source'=>$arr_default_types_vm, 'visible'=>$allow_relocation_to_other_room),
				'is_default'                => array('title'=>_DEFAULT, 'type'=>'enum', 'source'=>$arr_default_types_vm, 'visible'=>($this->hotelOwner ? false : true)),
				'is_active'                 => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
			),
			'separator_2'   =>array(
				'separator_info'   => array('legend'=>_COORDINATES_CENTER_POINT),
				'latitude_center'  => array('title'=>_CENTRAL_POINT_LATITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
				'longitude_center' => array('title'=>_CENTRAL_POINT_LONGITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
				'distance_center'  => array('title'=>_DISTANCE_TO_CENTER_POINT, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false, 'post_html'=>' '._KILOMETERS_SHORTENED),
			),
			'separator_3'   =>array(
				'separator_info' => array('legend'=>_ROOM_FACILITIES),
				'facilities'     => array('title'=>_FACILITIES, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_facilities, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true),
			),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
				array('name'              => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
			          'name_center_point' => array('title'=>_NAME_CENTER_POINT, 'type'=>'textbox', 'width'=>'410px', 'required'=>false, 'maxlength'=>'125', 'readonly'=>false),
			          'address'           => array('title'=>_ADDRESS, 'type'=>'textarea', 'width'=>'410px', 'height'=>'55px', 'required'=>false, 'maxlength'=>'225', 'validation_maxlength'=>'225', 'readonly'=>false),
			          'preferences'       => array('title'=>_PREFERENCES, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'readonly'=>false, 'editor_type'=>'simple'),
			          'description'       => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'2048', 'validation_maxlength'=>'2048', 'readonly'=>false, 'editor_type'=>'wysiwyg')
			)
		);
		///////////////////////////////////////////////////////////////////////////////
	}


	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Returns all array of all active hotels
	 * @param $where_clause
	 * @param $lang
	 *		@return array
	 */
	public static function GetAllActive($where_clause = '', $lang = '')
	{
		$where_clause = TABLE_HOTELS.'.is_active = 1'.(!empty($where_clause) ? ' AND '.$where_clause : '');
		return self::GetAllHotels($where_clause, $lang);
	}

	/**
	 * Returns all array of all active hotels
	 * @param $where_clause
	 * @param $lang
	 * @return array
	 */
	public static function GetAllHotels($where_clause = '', $lang = '')
	{
		$language = !empty($lang) ? $lang : Application::Get('lang');

		$sql = 'SELECT
					'.TABLE_HOTELS.'.*,
					'.TABLE_HOTELS_DESCRIPTION.'.name,
					'.TABLE_HOTELS_DESCRIPTION.'.name_center_point,
					'.TABLE_HOTELS_DESCRIPTION.'.address,
					'.TABLE_HOTELS_DESCRIPTION.'.description,
                    '.TABLE_HOTELS_DESCRIPTION.'.preferences,
					IF('.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.name IS NOT NULL, '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.name, "") as location_name 
				FROM '.TABLE_HOTELS.'
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_HOTELS.'.id = '.TABLE_HOTELS_DESCRIPTION.'.hotel_id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$language.'\'
                    LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' ON '.TABLE_HOTELS.'.hotel_location_id = '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.hotel_location_id AND '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.language_id = \''.$language.'\'
				WHERE 1 = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.TABLE_HOTELS.'.is_active DESC, '.TABLE_HOTELS.'.priority_order ASC ';
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 * Draws About Us block
	 * @param $draw
	 */
	public static function DrawAboutUs($draw = true)
	{
		$lang = Application::Get('lang');
		$output = '';

		$sql = 'SELECT
					h.phone,
					h.fax,
					h.stars,
					h.map_code,
					h.latitude,
					h.longitude,	
					hd.name,									
					hd.address,
					hd.description,
                    hd.preferences
				FROM '.TABLE_HOTELS.' h
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON h.id = hd.hotel_id
				WHERE h.is_default = 1 AND hd.language_id = \''.$lang.'\'';

		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output .= '<h3>'.$result[0]['name'].'</h3>';
			if($result[0]['stars'] > 0) $output .= '<p>'._STARS.': '.self::$arr_stars_vm[$result[0]['stars']].'</p>';
			$output .= '<p>'.$result[0]['description'].'</p>';
			$output .= '<p>'._ADDRESS.': '.$result[0]['address'].'</p>';
			$output .= '<p>'._PHONE.': '.$result[0]['phone'].'<br />'._FAX.': '.$result[0]['fax'].'</p>';
			$output .= self::DrawMap($result[0], array('width'=>90), false);
		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Returns hotel info
	 * @param $hotel_id
	 */
	public static function GetHotelInfo($hotel_id = '')
	{
		$output = array();
		$sql = 'SELECT *
				FROM '.TABLE_HOTELS.'
				WHERE '.(!empty($hotel_id) ? ' id ='.(int)$hotel_id : ' is_default = 1');
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output = $result[0];
		}
		return $output;
	}

	/**
	 * Returns hotel full info
	 * @param $hotel_id
	 * @param $lang
	 */
	public static function GetHotelFullInfo($hotel_id = '', $lang = '')
	{
		$output = array();
		if(empty($lang)){
			$lang = Application::Get('lang');
		}
		$sql = 'SELECT
					h.*,
					hd.name,
					hd.name_center_point,
					hd.address,
					hd.description,
                    hd.preferences,
					hld.name as location_name
				FROM '.TABLE_HOTELS.' h
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON h.id = hd.hotel_id AND hd.language_id = \''.$lang.'\'            
					LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' hld ON h.hotel_location_id = hld.hotel_location_id AND hld.language_id = \''.$lang.'\'
				WHERE 1=1
				'.(!empty($hotel_id) ? ' AND h.id = \''.(int)$hotel_id.'\' ' : ' AND h.is_default = 1').'
				LIMIT 0, 1';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output = $result[0];
			$output['lowest_price'] = self::GetHotelLowestPrice($result[0]['id']);
		}
		return $output;
	}

	/**
	 * Get hotel's lowest price
	 * @param $hotel_id
	 */
	public static function GetHotelLowestPrice($hotel_id = '', $params = array())
	{
		$lower_price = 0;

		$hotel_id = (int)$hotel_id;

        $sql = 'SELECT 
                COUNT(*) as cnt,
                MIN(mon) as min_mon,
                MIN(tue) as min_tue,
                MIN(wed) as min_wed,
                MIN(thu) as min_thu,
                MIN(fri) as min_fri,
                MIN(sat) as min_sat,
                MIN(sun) as min_sun
            FROM '.TABLE_ROOMS.' 
                LEFT OUTER JOIN '.TABLE_ROOMS_PRICES.' ON '.TABLE_ROOMS_PRICES.'.room_id = '.TABLE_ROOMS.'.id AND '.TABLE_ROOMS.'.is_active = 1
            WHERE '.TABLE_ROOMS.'.hotel_id = '.$hotel_id.' AND
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
                FROM '.TABLE_ROOMS.' 
                    LEFT OUTER JOIN '.TABLE_ROOMS_PRICES.' ON '.TABLE_ROOMS_PRICES.'.room_id = '.TABLE_ROOMS.'.id AND '.TABLE_ROOMS.'.is_active = 1
                WHERE '.TABLE_ROOMS.'.hotel_id = '.$hotel_id.' AND
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
                $sql = 'SELECT
                        '.TABLE_ROOMS.'.id
						FROM '.TABLE_ROOMS.' 
							LEFT OUTER JOIN '.TABLE_ROOMS_PRICES.' ON '.TABLE_ROOMS_PRICES.'.room_id = '.TABLE_ROOMS.'.id
						WHERE '.TABLE_ROOMS.'.hotel_id = '.$hotel_id.' AND
							('.TABLE_ROOMS_PRICES.'.is_default = 1 OR ('.TABLE_ROOMS_PRICES.'.date_from <= \''.date('Y-m-d').'\' AND '.TABLE_ROOMS_PRICES.'.date_to >= \''.date('Y-m-d').'\'))
							AND '.$select_day.' = \''.$lower_price.'\'';
					$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
					if($result[1] > 0){
						$room_price_result = Rooms::GetRoomPrice($result[0]['id'], $hotel_id, $params, 'array');
						$lower_price = $room_price_result['total_price'];
					}
				}
			}

		return $lower_price;
	}

	/**
	 * Returns hotels full info
	 * @param $hotel_id
	 * @param $lang
	 */
	public static function GetHotelsFullInfo($hotel_ids = array(), $lang = '')
	{
		$output = array();
		if(empty($lang)){
			$lang = Application::Get('lang');
		}

		if(!empty($hotel_ids) && is_array($hotel_ids)){
			$sql = 'SELECT
						h.*,
						hd.name,
						hd.name_center_point,
						hd.address,
						hd.description,
						hd.preferences,
						hld.name as location_name
					FROM '.TABLE_HOTELS.' h
						INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON h.id = hd.hotel_id AND hd.language_id = \''.$lang.'\'            
						LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' hld ON h.hotel_location_id = hld.hotel_location_id AND hld.language_id = \''.$lang.'\'
					WHERE h.id IN (\''.implode('\',\'', $hotel_ids).'\')';
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] > 0){
				$output = $result[0];
			}
		}
		return $output;
	}


	/**
	 * Draws Local Time block
	 * @param $hotel_id
	 * @param $draw
	 */
	public static function DrawLocalTime($hotel_id = '', $draw = true)
	{
		global $objSettings;

		// set timezone
		//----------------------------------------------------------------------
		$hotelInfo = Hotels::GetHotelInfo($hotel_id);
		$time_offset_hotel = (isset($hotelInfo['time_zone'])) ? $hotelInfo['time_zone'] : 0;
		$time_offset_site = $objSettings->GetParameter('time_zone');
		$time_zome_diff = $time_offset_hotel - $time_offset_site;
		$time_with_offset = time() + $time_zome_diff * 3600;

		if(Application::Get('lang') != 'en'){
			$dmy_string = ($objSettings->GetParameter('date_format') == 'mm/dd/yyyy') ? '%B %d, %Y' : '%d %B, %Y';
			$output1 = strftime(str_replace('%B', get_month_local(@strftime('%m', $time_with_offset)), $dmy_string), $time_with_offset);
			$output2 = strftime(str_replace('%A', get_weekday_local(@strftime('%w', $time_with_offset)+1), '%A %H:%M'), $time_with_offset);
		}else{
			$dmy_string = ($objSettings->GetParameter('date_format') == 'mm/dd/yyyy') ? 'F dS, Y' : 'dS \of F Y';
			$output1 = @date($dmy_string, $time_with_offset);
			$output2 = @date('l g:i A', $time_with_offset);
		}
		$output = $output1.'<br />'.$output2;

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws hotel phones
	 * 	@param $draw
	 * 	@param $separator
	 */
	public static function DrawPhones($draw = true, $separator = '<br>')
	{
		$hotel_id = Application::Get('hotel_id');
		$room_id = Application::Get('room_id');
		$hotel_info = array();
		$output = '';

		// Get hotel ID from room ID
		if(!empty($room_id)){
			$hotel_id = Rooms::GetRoomInfo($room_id, 'hotel_id');
		}

		// Get hotel info
		if(!empty($hotel_id)){
			$hotel_info = Hotels::GetHotelInfo($hotel_id);
		}else{
			$hotel_info = Hotels::GetAllActive('is_default = 1');
			$hotel_info = isset($hotel_info[0][0]) ? $hotel_info[0][0] : array();
		}

		if(!empty($hotel_info)){
			$output = $hotel_info[0][0]['phone'].$separator.$hotel_info[0][0]['fax'];
		}

		if($draw) echo $output;
		else return $output;
	}



	/**
	 * Draws Need Assistance Form
	 * @param string $email_hotels
	 * @return string $output
	 */
	public static function DrawNeedAssistanceForm($email_hotels = '')
	{
		$output = '';
		if(!empty($email_hotels)){
			$align_left = Application::Get('defined_left');
			$output .= '
            <div class="msg fail" id="message_need_assistance_error" style="display: none;">
                <p></p>
            </div>
            <div class="msg success" id="message_need_assistance_success" style="display: none;">
                <p>'._EMAIL_SUCCESSFULLY_SENT.'</p>
            </div>
            <form method="post" name="frmNeedAssistance" id="frm_need_assistance">
                '.draw_hidden_field('task', 'needAssistance', false).'
                '.draw_hidden_field('email_hotels', $email_hotels, false, 'email_hotels').'
                '.draw_token_field(false).'
                
                <table class="tblContactUs" border="0" width="99%">
                <tbody>
                <tr>
                    <td width="25%" align="'.$align_left.'">'._NAME.': <span class="mandatory_star">*</span></td>
                </tr>
                <tr>
                    <td nowrap="nowrap" align="'.$align_left.'"><input type="text" id="name" name="name" size="34" maxlength="40" value="" autocomplete="off" /></td>
                </tr>
                <tr valign="top">
                    <td align="'.$align_left.'">'._MESSAGE.': <span class="mandatory_star">*</span></td>
                </tr>
                <tr valign="top">
                    <td nowrap="nowrap" align="'.$align_left.'">
                        <textarea id="message" name="message" maxlength="1024" rows="8"></textarea>                
                    </td>
                </tr>
                <tr><td height="25" nowrap colspan="3"></td></tr>            
                <tr>
                    <td align="'.$align_left.'">
                        <input type="button" class="form_button" name="btn_need_assistance" id="btn_need_assistance" value="'._SEND.'" />
                    </td>
                </tr>
                <tr><td height="25" nowrap></td></tr>            
                </table>
            </form>
        ';
		}

		return $output;
	}

	/**
	 * Send Email Need Assistance
	 * @param string $email_hotels
	 * @param string $email_customer
	 * @param string $body
	 * @return bool $result
	 */
	public static function SendEmailNeedAssistance($email_hotels = '', $email_customer = '', $body = '')
	{
		$result     = false;

        $subject    = str_replace('{SITE}', $_SERVER['SERVER_NAME'], _SOMEONE_FROM_SITE_CONTACTED_YOU);
        $language   = Application::Get('lang');

        $send_email = send_email_wo_template(
            $email_hotels,
            $email_customer,
            $subject,
            $body,
            $language
        );

        if($send_email){
			$result = self::UpdateNeedAssistance();
        }

		return $result;
	}

	/**
	 * Update need assistance count and last need assistance
	 * @return bool $result
	 */
	public static function UpdateNeedAssistance()
	{
		$result = false;

        $objLogin 				= new Login();
        $rid 					= $objLogin->GetLoggedID();
		$current_date 			= date('Y-m-d');
		$need_assistance_count	= 0;

		$sql_select = 'SELECT '.TABLE_CUSTOMERS.'.*
				  FROM '.TABLE_CUSTOMERS.'
				  WHERE '.TABLE_CUSTOMERS.'.id = '.(int)$rid;
		$customer = database_query($sql_select, DATA_ONLY, FIRST_ROW_ONLY);
		if(!empty($customer)){
			if($customer['last_need_assistance'] == $current_date){
				$need_assistance_count = $customer['need_assistance_count'] + 1;
			}else{
				$need_assistance_count = 1;
			}
		}
		$last_need_assistance = date('Y-m-d');

        $sql = 'UPDATE '.TABLE_CUSTOMERS.' SET
                    '.TABLE_CUSTOMERS.'.need_assistance_count = '.(int)$need_assistance_count.',
                    '.TABLE_CUSTOMERS.'.last_need_assistance = "'.$last_need_assistance.'"
                WHERE '.TABLE_CUSTOMERS.'.id = '.(int)$rid;

		$result = database_void_query($sql);

		return $result;
	}

	/**
	 * Gets support info
	 * @return array
	 */
	public static function GetSupportInfo()
	{
		$hotel_id = Application::Get('hotel_id');
		$room_id = Application::Get('room_id');
		$hotel_info = array();

		if(ModulesSettings::Get('rooms', 'show_hotel_in_search_result') == 'yes'){
			$hotel_info['phone'] = ModulesSettings::Get('rooms', 'default_contant_phone');
			$hotel_info['email'] = ModulesSettings::Get('rooms', 'default_contant_email');
		}else{
			// Get hotel ID from room ID
			if(!empty($room_id)){
				$hotel_id = Rooms::GetRoomInfo($room_id, 'hotel_id');
			}

			// Get hotel info
			if(!empty($hotel_id)){
				$hotel_info = Hotels::GetHotelInfo($hotel_id);
			}else{
				$hotel_info = Hotels::GetAllActive('is_default = 1');
				$hotel_info = isset($hotel_info[0][0]) ? $hotel_info[0][0] : array();
			}
		}

		return $hotel_info;
	}

	/**
	 * Draws side support block
	 * @return array
	 */
	public static function GetSupportSideInfo()
	{
		$hotel_id = Application::Get('hotel_id');
		$room_id = Application::Get('room_id');
		$hotel_info = array();
		$output = '';

		// Get hotel ID from room ID
		if(!empty($room_id)){
			$hotel_id = Rooms::GetRoomInfo($room_id, 'hotel_id');
		}

		// Get hotel info
		if(!empty($hotel_id)){
			$hotel_info = Hotels::GetHotelInfo($hotel_id);
		}else{
			$hotel_info = Hotels::GetAllActive('is_default = 1');
			$hotel_info = isset($hotel_info[0][0]) ? $hotel_info[0][0] : array();
		}

		return $hotel_info;
	}

	/*
	 * @param int $hotel_id
	 * @param string $checkin
	 * @param string $checkout
	 * @return int
	 * */
	public static function GetCanceledDays($hotel_id, $checkin = '', $checkout = '')
	{
		if(empty($hotel_id)){
			return 0;
		}

		// Get the "canceled days" of settings
		$cancel_reservation_days = ModulesSettings::Get('booking', 'customers_cancel_reservation');

		// Get the "canceled days" at the hotel
		$sql = 'SELECT 
			'.TABLE_HOTELS.'.cancel_reservation_day
		FROM '.TABLE_HOTELS.'
		WHERE '.TABLE_HOTELS.'.id = '.(int)$hotel_id;

		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		if(!empty($result)){
			if($result['cancel_reservation_day'] > 0){
				$cancel_reservation_days = $result['cancel_reservation_day'];
			}
		}

		// Get the "canceled days" of the packages
		if(!empty($checkin) && !empty($checkout)){
			$result = Packages::GetCanceledDays(array($hotel_id), $checkin, $checkout);
			if(!empty($result) && $result[1] > 0){
				$cancel_reservation_days = $result[0][0]['cancel_reservation_day'];
			}
		}

		if($cancel_reservation_days > 0){
			return $cancel_reservation_days;
		}

		return false;
	}

	/**
	 * Returns hotels count
	*/
	public static function HotelsCount()
	{
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_HOTELS.' WHERE is_active = 1';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return (int)$result[0]['cnt'];
		}
		return 0;
	}


	/**
	 * Returns hotels images
	 * @param $hotel_id
	*/
	public static function GetHotelsImages($hotel_id)
	{
        // hotel images
        $sql = 'SELECT
                    h.hotel_image,
                    h.hotel_image_thumb,
                    hi.item_file,
                    hi.item_file_thumb,
                    hi.image_title,
                    hi.priority_order,
                    hi.is_active
                FROM '.TABLE_HOTELS.' h 
                    LEFT OUTER JOIN '.TABLE_HOTEL_IMAGES.' hi ON h.id = hi.hotel_id AND hi.is_active = 1
                WHERE
                    h.is_active = 1 AND
                    h.id = '.(int)$hotel_id.'
                ORDER BY
                    hi.priority_order ASC';
        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        return $result;
    }

	/**
	 * Returns hotels description
	 * @param $hotel_id
	 * @param $draw
	*/
	public static function DrawHotelDescription($hotel_id, $draw = true)
	{
		$output = '';

		$sql = 'SELECT
					h.id,
					h.hotel_location_id,
					h.phone,
					h.fax,
					h.time_zone,
					h.map_code,
					h.latitude,
					h.longitude,
					h.hotel_image_thumb,
					h.stars,
					h.is_default,
					h.is_active,
					h.priority_order,
					hd.name as hotel_name,
					hd.name_center_point as hotel_name_center_point,
					hd.address as hotel_address,
					hd.description as hotel_description,
                    hd.preferences as hotel_preferences
				FROM '.TABLE_HOTELS.' h
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON h.id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE h.is_active = 1 AND h.id = '.(int)$hotel_id;

		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output .= draw_title_bar($result[0]['hotel_name'].' &nbsp;'.(($result[0]['stars'] > 0) ? self::$arr_stars_vm[$result[0]['stars']] : ''), false);

			$output .= '<table class="tblHotelDescription" width="100%">';
			$output .= '<tr>';
			$output .= ' <td colspan="3"><b>'._ADDRESS.'</b>: '.$result[0]['hotel_address'].'</td>';
			$output .= ' <td rowspan="2" width="140px"><img src="images/hotels/'.$result[0]['hotel_image_thumb'].'" style="float:'.Application::Get('defined_right').';margin:0 5px;" alt="" /></td>';
			$output .= '</tr>';
			$output .= '<tr>';
			$output .= ' <td valign="top" width="170px"><b>'._LOCAL_TIME.'</b>:<br>'.Hotels::DrawLocalTime($result[0]['id'], false).'</td>';
			$output .= ' <td valign="top" width="170px">';
			if($result[0]['phone'] || $result[0]['fax']){
				if($result[0]['phone']) $output .= '<b>'._PHONE.'</b>: '.$result[0]['phone'].'<br />';
				if($result[0]['fax']) $output .= '<b>'._FAX.'</b>: '.$result[0]['fax'];
			}
			$output .= '</td>';
			$output .= '<td valign="top" height="80px"></td>';
			$output .= '</tr>';

			// hotel images
			$sql = 'SELECT id, hotel_id, item_file, item_file_thumb, image_title, priority_order, is_active
					FROM '.TABLE_HOTEL_IMAGES.'
					WHERE is_active = 1 AND hotel_id = '.(int)$hotel_id.'
					ORDER BY priority_order ASC';
			$result_img = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result_img[1] > 0){
				$output .= '<tr align="'.Application::Get('defined_right').'">';
				$output .= '<td colspan="4">';
					for($i=0; $i < $result_img[1]; $i++){
						$output .= '<a href="images/hotels/'.$result_img[0][$i]['item_file'].'" title="'.$result_img[0][$i]['image_title'].'" rel="lyteshow"><img src="images/hotels/'.$result_img[0][$i]['item_file_thumb'].'" style="margin:0 2px;" alt="" width="42px" /></a>';
					}
				$output .= '</td>';
				$output .= '</tr>';
			}

			$output .= '<tr><td colspan="4">'.$result[0]['hotel_description'].'</td></tr>';
			$output .= '<tr><td colspan="4">'.Rooms::DrawRoomsInHotel($hotel_id, false).'</td></tr>';
			$output .= '</table>';

			if($result[0]['map_code']) $output .= '<p><b>'._LOCATION.'</b>:<br /> '.$result[0]['map_code'].'</p>';

		}else{
			$output = draw_important_message(_WRONG_PARAMETER_PASSED, false);
		}

		if($draw) echo $output;
		else return $output;
	}


	/**
	 * Draw information about rooms and services
	 * @param $draw
	 */
	public static function DrawHotelsInfo($draw = true)
	{
		$lang = Application::Get('lang');
		$output = '';

		$sql = 'SELECT
				h.*,
				hd.name,
				hd.description,
                hd.preferences,
				hd.address
			FROM '.TABLE_HOTELS.' h
				LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON h.id = hd.hotel_id
			WHERE
				h.is_active = 1 AND
				hd.language_id = \''.$lang.'\'';

		$result = database_query($sql, DATA_AND_ROWS);
		for($i=0; $i<$result[1]; $i++){
			if($i > 0) $output .= '<div class="line-hor"></div>';
			$output .= '<h3>'.prepare_link((FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels'), (FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid'), $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], '', _CLICK_TO_VIEW).'</h3>';
			if($result[0][$i]['stars'] > 0) $output .= self::$arr_stars_vm[$result[0][$i]['stars']].'<br>';
			$output .= strip_tags($result[0][$i]['description'], '<b><u><i><br>').'<br>';
			$output .= _ADDRESS.': '.$result[0][$i]['address'].'<br>';
			$output .= (($result[0][$i]['phone'] != '') ? _PHONE.': '.$result[0][$i]['phone'].'<br>' : '');
			$output .= (($result[0][$i]['fax'] != '') ? _FAX.': '.$result[0][$i]['fax'].'<br>' : '');
		}
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draw hotel map
	 * @param $data
	 * @param $params
	 * @param $draw
	 */
	public static function DrawMap($data = array(), $params = array(), $draw = true)
	{
        global $objSettings;

		$width = isset($params['width']) ? (int)$params['width'] : 99;

		// Show map
		if(empty($data['latitude']) && empty($data['longitude'])){
			$output = $data['map_code'];
		}else{
			// Use Gmap3 if latitude and longitude are known
			$output = '<script src="//maps.googleapis.com/maps/api/js?key='.$objSettings->GetParameter('google_api').'" type="text/javascript"></script>
				<script type="text/javascript" src="'.APPHP_BASE.'templates/'.Application::Get('template').'/js/gmap3.js"></script>					
				<div id="map" class="gmap3"></div>
				'.htmlentities($data['name'].', '.str_replace("\r\n", ' ', $data['address'])).'
				<script type="text/javascript">
				$(document).ready(function() {
					$("#map").width("'.$width.'%").height("390px").gmap3({
						map:{options:{center:['.$data['latitude'].','.$data['longitude'].'],zoom: 14}},
						marker:{
							values:[{latLng:['.$data['latitude'].','.$data['longitude'].'], data:"'.htmlentities($data['name']).', <br />'.htmlentities(str_replace("\r\n", ' ', $data['address'])).'"},],
							options:{draggable: false},
							events:{
								mouseover: function(marker, event, context){
								  var map = $(this).gmap3("get"),
									infowindow = $(this).gmap3({get:{name:"infowindow"}});
									if(infowindow){
										infowindow.open(map, marker);
										infowindow.setContent(context.data);
									}else{
										$(this).gmap3({infowindow:{anchor:marker, options:{content: context.data}}});
									}
								},
								mouseout: function(){
									var infowindow = $(this).gmap3({get:{name:"infowindow"}});
									if (infowindow){infowindow.close();}
								}
							}
						}
					});								
				});
				</script>';
		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draw hotels by rating
	 * @param $draw
	 */
	public static function DrawHotelsBestRated($draw = true)
	{
		$lang = Application::Get('lang');
		$output = '';

		// Get 5 best rated hotels
		$sql = 'SELECT hotel_id, SUM(evaluation) as evaluation, COUNT(*) as cnt FROM '.TABLE_REVIEWS.' GROUP BY hotel_id ORDER BY evaluation DESC';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		$rated_hotel_ids = array(1,2);
		for($i=0; $i<$result[1]; $i++){
			$rated_hotel_ids[$result[0][$i]['hotel_id']] = array('rate'=>$result[0][$i]['evaluation'], 'votes'=>$result[0][$i]['cnt']);
		}

		// Get info about these hotels
		if(!empty($rated_hotel_ids)){
			$sql = 'SELECT
					h.*,
					hd.name,
					hd.description,
					hd.preferences,
					hd.address,
					hld.name as location_name
				FROM '.TABLE_HOTELS.' h
					INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON h.id = hd.hotel_id AND hd.language_id = \''.$lang.'\'            
					LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' hld ON h.hotel_location_id = hld.hotel_location_id AND hld.language_id = \''.$lang.'\'            
				WHERE
					h.is_active = 1 AND
					h.id IN ('.implode(',', array_keys($rated_hotel_ids)).')
				ORDER BY FIELD(h.id, '.implode(',', array_keys($rated_hotel_ids)).')';
			$result = database_query($sql, DATA_AND_ROWS);

			for($i=0; $i<$result[1]; $i++){
				$votes = $rated_hotel_ids[$result[0][$i]['id']]['votes'];
				$rate = $rated_hotel_ids[$result[0][$i]['id']]['rate'] / (!empty($votes) ? $votes : 1);
				$output .=  '<div class="best-hotels-title">
                        <p><a href="index.php?page='.(FLATS_INSTEAD_OF_HOTELS ? 'flats&amp;fid=' : 'hotels&amp;hid=').$result[0][$i]['id'].'" class="dark">'.$result[0][$i]['name'].'</a></p>
                        <img src="templates/'.Application::Get('template').'/images/smallrating-'.(int)$rate.'.png" alt="" class="mt-10">
						<span class="size13 grey mt-9">'.$votes.' '.($votes > 1 ? _VOTES : _VOTE).'</span>
                    </div>';
			}
		}

        if($draw) echo $output;
		else return $output;
	}

	/**
	 * Iteration counter for room
	 * @param array $room
	 * @return void
	*/
	public static function NumberViewsIteration($hid = 0)
	{
		if(!empty($hid) && is_numeric($hid)){
			$sql = 'UPDATE '.TABLE_HOTELS.' SET number_of_views = number_of_views + 1
			WHERE id = '.(int)$hid;

			return database_void_query($sql);
		}
		return false;
	}

	/**
	 * Draw top hotels by group
	 * @param $group
	 * @param $image
	 * @param $draw
	 * @param $params
	 */
	public static function DrawHotelsByGroup($group = 0, $image = false, $draw = true, $params = array())
	{
		$lang = Application::Get('lang');
        $currencyRate = Application::Get('currency_rate');
        $currencyFormat = get_currency_format();
		$output = '';

		$cols = isset($params['col-md']) ? $params['col-md'] : 'col-md-4';

		$sql = 'SELECT
				h.*,
				hd.name,
				hd.description,
                hd.preferences,
				hd.address,
				hld.name as location_name
			FROM '.TABLE_HOTELS.' h
				INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON h.id = hd.hotel_id AND hd.language_id = \''.$lang.'\'            
				LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' hld ON h.hotel_location_id = hld.hotel_location_id AND hld.language_id = \''.$lang.'\'            
			WHERE
				h.is_active = 1 AND
                h.hotel_group_id = '.(int)$group.'				
            ORDER BY RAND()
            LIMIT 0, 10';
		$result = database_query($sql, DATA_AND_ROWS);

        if($group == 1){
            for($i=0; $i<$result[1] && $i<2; $i++){
				$hotel_lowest_price = Hotels::GetHotelLowestPrice($result[0][$i]['id']);
                $href = prepare_link((FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels'), (FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid'), $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], '', _CLICK_TO_VIEW, true);
                $output .= '<div class="'.$cols.'">
                    <div class="shadow cstyle05">
                        <div class="fwi one"><img style="height:355px;" src="'.APPHP_BASE.'images/hotels/'.(!empty($result[0][$i]['hotel_image']) ? $result[0][$i]['hotel_image'] : 'no_image.png').'" alt="'.htmlentities($result[0][$i]['name']).'" /><div class="mhover none"><span class="icon"><a href="'.$href.'"><img src="'.APPHP_BASE.'templates/'.Application::Get('template').'/images/spacer.png" alt=""/></a></span></div></div>
                        <div class="ctitle cpointer" onclick="javascript:appGoToPage(\''.$href.'\')">'.$result[0][$i]['name'].'<a href="'.$href.'"><img src="'.APPHP_BASE.'templates/'.Application::Get('template').'/images/spacer.png" alt="cutton icon" /></a>
							'.(!empty($hotel_lowest_price) ? '<span class="cpointer">'.Currencies::PriceFormat(($hotel_lowest_price * $currencyRate), '', '', $currencyFormat).'</span>' : '').'                            
                        </div>
                    </div>
                </div>';
            }
        }elseif(($group == 2 || $group == 3 || $group == 4) && $result[1]){
            $output .= '<div class="col-md-4">';

            if($image){
                if($group == 2){
                    $output .= '<div class="lbl">
                        <img src="templates/'.Application::Get('template').'/images/egypt-thumb.jpg" alt="'._LAST_MINUTE.'" class="fwimg"/>
                        <div class="smallblacklabel">'._LAST_MINUTE.'</div></div>';
                }elseif($group == 3){
                    $output .= '<div class="lbl">
                        <img src="templates/'.Application::Get('template').'/images/rome-thumb.jpg" alt="'._EARLY_BOOKING.'" class="fwimg"/>
                        <div class="smallblacklabel">'._EARLY_BOOKING.'</div></div>';
                }elseif($group == 4){
                    $output .= '<div class="lbl">
                        <img src="templates/'.Application::Get('template').'/images/surfer-thumb.jpg" alt="'._HOT_DEALS.'" class="fwimg"/>
                        <div class="smallblacklabel">'._HOT_DEALS.'</div></div>';
                }
            }else{
                if($group == 2) $output .= '<span class="dtitle">'._LAST_MINUTE.'</span>';
                else if($group == 3) $output .= '<span class="dtitle">'._EARLY_BOOKING.'</span>';
                else if($group == 4) $output .= '<span class="dtitle">'._HOT_DEALS.'</span>';
            }

            for($i=0; $i<$result[1] && $i<3; $i++){
				$hotel_lowest_price = Hotels::GetHotelLowestPrice($result[0][$i]['id']);
                $href = prepare_link((FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels'), (FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid'), $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], '', _CLICK_TO_VIEW, true);
                $output .= '<div class="deal">
                    <a href="'.$href.'"><img src="'.APPHP_BASE.'images/hotels/'.(!empty($result[0][$i]['hotel_image_thumb']) ? $result[0][$i]['hotel_image_thumb'] : 'no_image.png').'" alt="'.htmlentities($result[0][$i]['name']).'" class="dealthumb"/></a>
                    <div class="dealtitle">
                        <p><a href="'.$href.'" class="dark">'.$result[0][$i]['name'].'</a></p>
                        <img src="templates/'.Application::Get('template').'/images/smallrating-'.(int)$result[0][$i]['stars'].'.png" alt="" class="mt-10"/><span class="size13 grey mt-9">'.$result[0][$i]['location_name'].'</span>
                    </div>
                    <div class="dealprice">
						'.(!empty($hotel_lowest_price) ? '<p class="size12 grey lh2" style="text-transform:lowercase">'._FROM.'<span class="price" style="margin:0 0 0 5px">'.Currencies::PriceFormat(($hotel_lowest_price * $currencyRate), '', '', $currencyFormat).'</span><br/>'._PER_NIGHT.'</p>' : '').'
                    </div>					
                </div><div class="clear"></div>';
            }
            $output .= '</div>';
        }elseif(($group == 5 || $group == 6) && $result[1]){

            $output .= '<div class="col-md-3">';
            if($group == 5){
                $output .= '<h2>'._TODAY_TOP_DEALS_WITH_BR.'</h2><br/>';
                $output .= _TODAY_TOP_DEALS_TEXT;
            }elseif($group == 6){
                $output .= '<h2>'._FEATURED_OFFERS_WITH_BR.'</h2><br/>';
                $output .= _FEATURED_OFFERS_TEXT;
            }
            $output .= '</div>';
            $output .= '<div class="col-md-9">';

            $output .= '<!-- Carousel -->';
            $output .= '<div class="wrapper">';
            $output .= '<div class="list_carousel">';
            if($group == 5){
                $output .= '<ul id="foo">';
            }elseif($group == 6){
                $output .= '<ul id="foo2">';
            }

			// [05.02.2015] - Bugfix for slider with 3 images
			if($result[1] == 3){
				$result[0][3] = $result[0][0];
				$result[0][4] = $result[0][1];
				$result[0][5] = $result[0][2];
				$result[1] = 6;
			}

            for($i=0; $i<$result[1]; $i++){
				$hotel_lowest_price = Hotels::GetHotelLowestPrice($result[0][$i]['id']);
                $href = prepare_link((FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels'), (FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid'), $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], '', _CLICK_TO_VIEW, true);
                $output .= '<li>
                    <a href="'.$href.'"><img width="255px" height="179" src="'.APPHP_BASE.'images/hotels/'.$result[0][$i]['hotel_image'].'" alt="'.htmlentities($result[0][$i]['name']).'" /></a>
                    <div class="m1">
                        <h6 class="lh1 dark"><b>'.$result[0][$i]['name'].', '.$result[0][$i]['location_name'].'</b></h6>
                        <h6 class="lh1 green">'._STARTING_FROM.' '.Currencies::PriceFormat(($hotel_lowest_price * $currencyRate), '', '', $currencyFormat).'</h6>							
                    </div>
                </li>';
            }

            $output .= '</ul>';

            $output .= '<div class="clearfix"></div>';
            $output .= '<a id="prev_btn'.($group == 6 ? '2' : '').'" class="prev" href="javascript:void(0);"><img src="templates/'.Application::Get('template').'/images/spacer.png" alt=""/></a>';
            $output .= '<a id="next_btn'.($group == 6 ? '2' : '').'" class="next" href="javascript:void(0);"><img src="templates/'.Application::Get('template').'/images/spacer.png" alt=""/></a>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
        }

        if($draw) echo $output;
		else return $output;
    }


	//==========================================================================
    // MicroGrid Methods
	//==========================================================================
	/**
	 * Validate translation fields
	 */
	private function ValidateTranslationFields()
	{
		foreach($this->arrTranslations as $key => $val){
			if(trim($val['name']) == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;
			}
		}
		return true;
	}

	/**
	 * Before-Insertion
	 */
	public function BeforeInsertRecord()
	{
		return $this->ValidateTranslationFields();
	}

	/**
	 * After-Insertion - add banner descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		global $objLogin;

		$sql = 'INSERT INTO '.TABLE_HOTELS_DESCRIPTION.'(id, hotel_id, language_id, name, name_center_point, address, description, preferences) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['name_center_point'])).'\', \''.encode_text(prepare_input($val['address'])).'\', \''.encode_text(prepare_input($val['description'], false, 'medium')).'\', \''.encode_text(prepare_input($val['preferences'], false, 'medium')).'\')';
			$count++;
		}

		if(database_void_query($sql)){
			// set default  = 0 for other languages
			if(self::GetParameter('is_default', false) == '1'){
				$sql = 'UPDATE '.TABLE_HOTELS.'
						SET is_active = IF(id = '.(int)$this->lastInsertId.', 1, is_active),
						    is_default = IF(id = '.(int)$this->lastInsertId.', 1, 0)';
				database_void_query($sql);
			}

			$sql = "INSERT INTO ".TABLE_HOTEL_PAYMENT_GATEWAYS." (id, hotel_id, payment_type, payment_type_name, api_login, api_key, payment_info) VALUES
				(NULL, ".(int)$this->lastInsertId.", 'poa', 'Pay on Arrival', '', '', ''),
				(NULL, ".(int)$this->lastInsertId.", 'online', 'On-line Order', '', '', ''),
				(NULL, ".(int)$this->lastInsertId.", 'paypal', 'PayPal', '', '', ''),
				(NULL, ".(int)$this->lastInsertId.", '2co', '2CO (2 checkout)', '', '', ''),
				(NULL, ".(int)$this->lastInsertId.", 'authorize.net', 'Authorize.Net', '', '', ''),
				(NULL, ".(int)$this->lastInsertId.", 'bank.transfer', 'Bank Transfer', '', '', 'Bank name: {BANK NAME HERE}\r\nSwift code: {CODE HERE}\r\nRouting in Transit# or ABA#: {ROUTING HERE}\r\nAccount number *: {ACCOUNT NUMBER HERE}\r\n\r\n*The account number must be in the IBAN format which may be obtained from the branch handling the customer''s account or may be seen at the top the customer''s bank statement\r\n'),
				(NULL, ".(int)$this->lastInsertId.", 'account.balance', 'Pay with Balance', '', '', '')";
			database_void_query($sql);

			// Update list of hotels for current hotel owner
			if($this->hotelOwner && $objLogin->HasPrivileges('add_hotel_info')){
				$hotel_ids = $objLogin->AssignedToHotels();
				if(is_array($hotel_ids)){
					$hotel_ids[] = $this->lastInsertId;
					$sql = "UPDATE ".TABLE_ACCOUNTS."
							SET companies = '".implode(',', $hotel_ids)."'
							WHERE id = ".$objLogin->GetLoggedId();
					database_void_query($sql);

					// Update session variables
					$session_user_privileges = Session::Get('session_user_privileges');
					$session_user_privileges['hotel_ids'] = $hotel_ids;
					Session::Set('session_user_privileges', $session_user_privileges);
				}
			}

			return true;
		}else{
			return false;
		}
	}

	/**
	 * Before-Updating operations
	 */
	public function BeforeUpdateRecord()
	{
		return $this->ValidateTranslationFields();
	}

	/**
	 * After-Updating - update hotels item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		// set always default hotel to be active
		$sql = 'SELECT * FROM '.TABLE_HOTELS.' WHERE id = '.(int)$this->curRecordId;
		if($language = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			// set default  = 0 for other languages
			if(self::GetParameter('is_default', false) == '1'){
				$sql = 'UPDATE '.TABLE_HOTELS.'
						SET is_active = IF(id = '.(int)$this->curRecordId.', 1, is_active),
						    is_default = IF(id = '.(int)$this->curRecordId.', 1, 0)';
				database_void_query($sql);
			}
		}

		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_HOTELS_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						name_center_point = \''.encode_text(prepare_input($val['name_center_point'])).'\',
						address = \''.encode_text(prepare_input($val['address'])).'\',
						description = \''.encode_text(prepare_input($val['description'], false, 'medium')).'\',
                        preferences = \''.encode_text(prepare_input($val['preferences'], false, 'medium')).'\'
					WHERE hotel_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
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
	 * Before-Deleting Record
	 */
	public function BeforeDeleteRecord()
	{
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			return false;
		}

		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_HOTELS.' WHERE is_active = 1';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);

		if((int)$result['cnt'] > 1){
			$sql = 'SELECT is_default FROM '.TABLE_HOTELS.' WHERE id = '.(int)MicroGrid::GetParameter('rid');
			$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			if($result['is_default'] == '1'){
				$this->error = FLATS_INSTEAD_OF_HOTELS ? _DEFAULT_FLAT_DELETE_ALERT : _DEFAULT_HOTEL_DELETE_ALERT;
				return false;
			}
			return true;
		}else{
			$sql = 'SELECT is_active FROM '.TABLE_HOTELS.' WHERE id = '.(int)$this->curRecordId;
			$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			if($result['is_active'] == '1'){
				$this->error = FLATS_INSTEAD_OF_HOTELS ? _LAST_FLAT_ALERT : _LAST_HOTEL_ALERT;
				return false;
			}
			return true;
		}

		return false;
	}

    /**
	 * After-Deleting Record
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'SELECT id, is_active FROM '.TABLE_HOTELS;
		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			if((int)$result[1] == 1){
				// make last hotel always default and active
				$sql = 'UPDATE '.TABLE_HOTELS.' SET is_default = \'1\', is_active = \'1\' WHERE id= '.(int)$result[0][0]['id'];
				database_void_query($sql);
			}
		}

		// *** Channel manager
		// -----------------------------------------------------------------------------
		if(Modules::IsModuleInstalled('channel_manager')){
			$rooms = Rooms::GetAllRooms('r.hotel_id = '.(int)$this->curRecordId, false);
			$ids = array();
			for($i=0; $i<$rooms[1]; $i++){
				$ids[] = $rooms[0][$i]['id'];
			}
			$ids_string = implode(',', $ids);
			ChannelHotelRooms::DeleteRooms($ids_string);
		}

        // Delete info from hotel description table and other hotel related info
		$sql = 'DELETE FROM '.TABLE_HOTELS_DESCRIPTION.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_HOTEL_PERIODS.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_ROOMS.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_ROOMS_AVAILABILITIES.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_ROOMS_DESCRIPTION.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_ROOMS_PRICES.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'SELECT id FROM '.TABLE_MEAL_PLANS.' WHERE hotel_id = '.$this->curRecordId;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		for($i=0; $i<$result[1]; $i++){
			$sql = 'DELETE FROM '.TABLE_MEAL_PLANS_DESCRIPTION.' WHERE meal_plan_id = '.$result[0][$i]['id'];
			database_void_query($sql);
		}
		$sql = 'DELETE FROM '.TABLE_MEAL_PLANS.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_HOTEL_IMAGES.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_HOTEL_PAYMENT_GATEWAYS.' WHERE hotel_id = '.$this->curRecordId;
		database_void_query($sql);

		$sql = 'SELECT id, companies
			FROM '.TABLE_ACCOUNTS.'
			WHERE companies FIND_IN_SET('.$this->curRecordId.', companies) AND account_type = \'hotelowner\'';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$hotels = @explode(',', $result[0]['companies']);
			$account_id = $result[0]['id'];
			if(!empty($hotels) && in_array($this->curRecordId, $hotels)){
				$hotels = array_diff($hotels, array($this->curRecordId));
				$companies = implode(',', $hotels);

				$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET companies = \''.$companies.'\'
				WHERE id = '.(int)$account_id;
				database_void_query($sql);
			}
		}
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
     * Generates an video tag
     * @param string $src
     * @param array $options
     * Ex.: array('width'=>'560', 'height'=>'350', 'autoplay'=>true, 'allowfullscreen'=>true)
     * @return string
     */
    public static function video($src, $options = array())
    {
        $videoHtml = '';

        $srcParts = explode('/', $src);
        $videoId = array_pop($srcParts);
        $width = !empty($options['width']) ? $options['width'] : '560';
        $height = !empty($options['height']) ? $options['height'] : '315';
        $autoplay = !empty($options['autoplay']) ? ' autoplay' : '';

        if(preg_match('/(youtube\.|youtu\.)/i', $src)){
            $videoId = str_replace('watch?v=', '', $videoId);
            $videoHtml = '<iframe width="'.$width.'" height="'.$height.'" frameborder="0" allow="encrypted-media'.(!empty($options['autoplay']) ? ' autoplay' : '').'" allowfullscreen="'.(!empty($options['allowfullscreen']) ? 'allowfullscreen' : '').'" src="https://www.youtube.com/embed/'.$videoId.(!empty($autoplay) ? '?autoplay=1' : '').'"></iframe>';
        }elseif(preg_match('/vimeo\./i', $src)){
            $videoHtml = '<iframe width="'.$width.'" height="'.$height.'" webkitallowfullscreen="'.(!empty($options['webkitallowfullscreen']) ? 'webkitallowfullscreen' : '').'" mozallowfullscreen="'.(!empty($options['mozallowfullscreen']) ? 'mozallowfullscreen' : '').'" src="https://player.vimeo.com/video/'.$videoId.(!empty($autoplay) ? '?autoplay=1' : '').'"></iframe>';
        }

        return $videoHtml;
    }
}
