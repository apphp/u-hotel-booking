<?php

/***
 * Campaigns Class (has differences)
 * -------------- 
 * Description : encapsulates comments properties
 * Written by  : ApPHP
 * Version     : 1.0.6
 * Updated	    : 05.04.2018
 *
 * PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 * __construct             DrawCampaignBanner      	CheckStartFinishDate
 * __destruct              FindCampaigns         	CheckDateOverlapping 
 * BeforeInsertRecord      GetCampaignInfo			CheckRecordAssigned
 * BeforeUpdateRecord	   UpdateStatus				CheckAdultsMinMax
 * BeforeEditRecord									ValidateDiscountType
 * BeforeDetailsRecord
 * BeforeDeleteRecord
 *
 *  1.0.6
 *      - added _THIS_HOTEL and _OUR_HOTELS
 *      - added minimum_adults, maximum_adults and discount_type
 *      - added discount_type, discount_value
 *      -
 *      -
 *  1.0.5
 *      - improved GetCampaignInfo()
 *      - added definition of campaign types according to project
 *      - discount step redone to +1
 *      - fixed issue with wrong calculating discount for "standard" campaign in last day
 *      - added changes in campaign names
 *  1.0.4
 *      - added campaign_type column to View Mode
 *      - added SetLocale
 *      - added campaign type for some methods
 *      - 'current' campaign changed with global'
 *      - added _DISCOUNT_STD_CAMPAIGN_TEXT
 *  1.0.3
 *      - added date localization in discount campaign banner
 *      - for HotelSite clients changed with customers
 *      - " replaced with '
 *      - all is_active fields redone with "enum" type
 *      - added current/standard types for campaigns
 *  1.0.2
 *      - added campaign_type field
 *      - added _CAMPAIGNS_TOOLTIP
 *      - fixed error in GetCampaignInfo() and DrawCampaignBanner()
 *      - bug fixed in check dates overlapping
 *      - bug fixed for date displaying on year passing
 *  1.0.1
 *  	- added  to select differences
 *  	- added group_id as target group
 *  	- changes in GetCampaignInfo
 *  	- added group_id to GetCampaignInfo() and DrawCampaignBanner()
 *  	- added filtering
 **/


class Campaigns extends MicroGrid {
	
	protected $debug = false;
	protected $assigned_to_hotels = '';

    private $regionalManager = false;
    private $hotelOwner = false;
	
	//------------------------------
	private static $PROJECT = PROJECT_NAME; // HotelSite, HotelBooking
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
        
        global $objLogin, $objSettings;

        $this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager') ? true : false;
        $this->hotelOwner      = $objLogin->IsLoggedInAs('hotelowner') ? true : false;

		$this->params = array();		

		## for standard fields
		if(isset($_POST['campaign_name'])) 		$this->params['campaign_name'] = prepare_input($_POST['campaign_name']);
		if(isset($_POST['group_id']))      		$this->params['group_id'] = (int)$_POST['group_id'];
        if(isset($_POST['hotel_id']))      		$this->params['hotel_id'] = (int)$_POST['hotel_id'];
		if(isset($_POST['start_date']))    		$this->params['start_date'] = prepare_input($_POST['start_date']);
		if(isset($_POST['finish_date']))   		$this->params['finish_date'] = prepare_input($_POST['finish_date']);
		if(isset($_POST['discount_type']))  	$this->params['discount_type'] = prepare_input($_POST['discount_type']);
		if(isset($_POST['discount_value']))  	$this->params['discount_value'] = prepare_input($_POST['discount_value']);
        if(isset($_POST['apply_per']))      	$this->params['apply_per'] = (int)$_POST['apply_per'];
		if(isset($_POST['minimum_adults']))  	$this->params['minimum_adults'] = prepare_input($_POST['minimum_adults']);
		if(isset($_POST['maximum_adults']))  	$this->params['maximum_adults'] = prepare_input($_POST['maximum_adults']);		
		

		$this->params['campaign_type'] = (GLOBAL_CAMPAIGNS == 'enabled' ? (isset($_POST['campaign_type']) ? prepare_input($_POST['campaign_type']) : '') : 'standard');
		
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
		$this->tableName 	= TABLE_CAMPAIGNS;
		$this->dataSet 		= array();
		$this->error 		= '';
		
		$this->formActionURL = 'index.php?admin='.((self::$PROJECT == 'HotelSite' || self::$PROJECT == 'HotelBooking') ? 'mod_booking_campaigns' : 'mod_shop_campaigns');
		$group_table = TABLE_CUSTOMER_GROUPS;	
		
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();

		$this->WHERE_CLAUSE = 'WHERE 1 = 1'.(GLOBAL_CAMPAIGNS != 'enabled' ? ' AND '.$this->tableName.".campaign_type = 'standard'" : '');
		$hotels_list = '';
		if($this->hotelOwner){
			$hotels = $objLogin->AssignedToHotels();
			$hotels_list = implode(',', $hotels);
			$this->assigned_to_hotels = ' AND '.(!empty($hotels_list) ? $this->tableName.'.hotel_id IN ('.$hotels_list.') ' : '1 = 0');
		}elseif($this->regionalManager){
			$hotels = AccountLocations::GetHotels($objLogin->GetLoggedID());
			if(empty($hotels)){
				$this->actions['add'] = false;
			}
			$hotels_list = implode(',', $hotels);
			$this->assigned_to_hotels = ' AND '.(!empty($hotels_list) ? $this->tableName.'.hotel_id IN ('.$hotels_list.')' : '1 = 0 ');
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_hotels;

        // prepare hotels array
        $where_clause = '';
        if($this->hotelOwner || $this->regionalManager){
            $where_clause = !empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : '1 = 0';
        }

        $total_hotels = Hotels::GetAllHotels($where_clause);
        $arr_hotels = array();
        foreach($total_hotels[0] as $key => $val){
            $arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
        }

		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.'.$this->primaryKey.' DESC';
		
		$this->isAlterColorsAllowed = true;
		$this->isPagingAllowed = true;
		$this->pageSize = 20;
		$this->isSortingAllowed = true;

		// prepare target groups array		
		$total_groups = CustomerGroups::GetAllGroups();
		$arr_groups = array();
		foreach($total_groups[0] as $key => $val){
		 	$arr_groups[$val['id']] = $val['name'];
		}
        
        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		// define filtering fields
		$this->isFilteringAllowed = true;
		$this->arrFilteringFields = array(			
			_GROUP => array('table'=>$this->tableName, 'field'=>'group_id', 'type'=>'dropdownlist', 'source'=>$arr_groups, 'sign'=>'=', 'width'=>'130px'),
            $hotel_name => array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels, 'sign'=>'=', 'width'=>'130px'),
		);

		$arr_active = array('0'=>_NO, '1'=>_YES);
		$arr_active_html = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_discount = array();
		for($i=0; $i<100; $i++){
			$arr_discount[$i] = $i;
		}
		
		$arr_discount_types = array('0'=>_FIXED_PRICE, '1'=>_PERCENTAGE);
		
		$arr_adults = array('0'=>_NOT_DEFINED);
		for($i=1; $i<=100; $i++){
			$arr_adults[$i] = $i;
		}

		$arr_campaign_types = array('global'=>_GLOBAL, 'standard'=>_STANDARD);
		$arr_campaign_types_full = array('global'=>_GLOBAL.' ('._REAL_TIME_CAMPAIGN.')', 'standard'=>_STANDARD.' ('._SCHEDULED_CAMPAIGN.')');

		$arr_campaign_apply_types = array('0'=>ucfirst(_ROOM), '1'=>ucfirst(_HOTEL));
		
		$date_format = get_date_format('view');
		$date_format_edit = get_date_format('edit');				
		$currency_format = get_currency_format();
		$currency_symbol = Application::Get('currency_symbol');		
		
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
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.campaign_name,
									'.(GLOBAL_CAMPAIGNS == 'enabled' ? $this->tableName.'.campaign_type, ' : '').'
									DATE_FORMAT('.$this->tableName.'.start_date, "'.$sqlFieldDateFormat.'") as start_date,
									DATE_FORMAT('.$this->tableName.'.finish_date, "'.$sqlFieldDateFormat.'") as finish_date,
									'.$this->tableName.'.discount_type,
									'.$this->tableName.'.discount_value,
									'.$this->tableName.'.apply_per,
									CONCAT(IF('.$this->tableName.'.minimum_adults > 0, '.$this->tableName.'.minimum_adults, ""), " - ", IF('.$this->tableName.'.maximum_adults > 0, '.$this->tableName.'.maximum_adults, "")) min_max_adults,
									CONCAT(IF('.$this->tableName.'.discount_type = 0, "'.htmlentities($currency_symbol).'", ""), '.$this->tableName.'.discount_value, IF('.$this->tableName.'.discount_type = 1, "%", "")) as discount_formatted,
									'.$this->tableName.'.is_active,
									IF('.$this->tableName.'.group_id = 0, "'._ALL.'", cg.name) as group_name,
                                    IF('.$this->tableName.'.hotel_id = 0, "'._ALL.'", hd.name) as hotel_name
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.$group_table.' cg ON '.$this->tableName.'.group_id = cg.id
                                    LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON '.$this->tableName.'.hotel_id = hd.hotel_id AND hd.language_id = \''.$this->languageId.'\'
								';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'campaign_name'  	=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'210px', 'sortable'=>true, 'nowrap'=>'nowrap', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'campaign_type'     => array('visible'=>(GLOBAL_CAMPAIGNS == 'enabled' ? true : false), 'title'=>_TYPE, 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'source'=>$arr_campaign_types),
			'group_name'  	    => array('title'=>_TARGET_GROUP, 'type'=>'label', 'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'hotel_name'  	    => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _TARGET_FLAT : _TARGET_HOTEL, 'type'=>'label', 'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'apply_per'     	=> array('title'=>_APPLY_PER, 'type'=>'enum',  'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'source'=>$arr_campaign_apply_types),
			'start_date'  		=> array('title'=>_START_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'finish_date'  		=> array('title'=>_FINISH_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'min_max_adults'	=> array('title'=>_ADULTS, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'discount_formatted'=> array('title'=>_DISCOUNT, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'is_active'         => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_html),
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
			'campaign_name'  	=> array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'Campaign #_ '.@date('M Y'), 'validation_type'=>'text', 'unique'=>false, 'visible'=>true),
			'campaign_type'     => array('visible'=>GLOBAL_CAMPAIGNS == 'enabled' ? true : false, 'title'=>_TYPE, 'type'=>'enum', 'header_tooltip'=>_CAMPAIGNS_TOOLTIP, 'required'=>true, 'readonly'=>false, 'width'=>'', 'source'=>$arr_campaign_types_full, 'default'=>'global', 'unique'=>false, 'javascript_event'=>''),
			'group_id'  	    => array('title'=>_TARGET_GROUP, 'type'=>'enum', 'required'=>false, 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_groups, 'unique'=>false, 'javascript_event'=>'', 'default_option'=>_ALL),
			'hotel_id'  	    => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _TARGET_FLAT : _TARGET_HOTEL, 'type'=>'enum', 'required'=>($objLogin->IsLoggedInAs('hotelowner') ? true : false), 'validation_type'=>'numeric', 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'unique'=>false, 'javascript_event'=>'', 'default_option'=>(!$objLogin->IsLoggedInAs('hotelowner', 'regionalmanager') ? _ALL : false)),
			'apply_per'  		=> array('title'=>_APPLY_PER, 'type'=>'enum', 'header_tooltip'=>htmlspecialchars(_APPLY_PER_DESCRIPTION), 'required'=>true, 'readonly'=>false, 'width'=>'140px', 'default'=>'1', 'source'=>$arr_campaign_apply_types, 'unique'=>false, 'javascript_event'=>''),
			'start_date'  		=> array('title'=>_START_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10'),
			'finish_date'  		=> array('title'=>_FINISH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10'),
			'minimum_adults'  	=> array('title'=>_MIN_ADULTS, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'100px', 'source'=>$arr_adults, 'unique'=>false, 'javascript_event'=>'', 'default'=>'0'),
			'maximum_adults'  	=> array('title'=>_MAX_ADULTS, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'100px', 'source'=>$arr_adults, 'unique'=>false, 'javascript_event'=>'', 'default'=>'0'),
			'discount_type'  	=> array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'140px', 'default'=>'1', 'source'=>$arr_discount_types, 'unique'=>false, 'javascript_event'=>''),
			'discount_value'  	=> array('title'=>_DISCOUNT, 'type'=>'textbox', 'header_tooltip'=>'', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'pre_html'=>'<span class="discount_currency">'.$currency_symbol.'</span> ', 'post_html'=>' <span class="discount_percent">%</span>'),
			'is_active'         => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
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
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.group_id,
                                '.$this->tableName.'.hotel_id,
								'.(GLOBAL_CAMPAIGNS =='enabled' ? $this->tableName.'.campaign_type, ' : '').'
								'.$this->tableName.'.campaign_name,
								'.$this->tableName.'.start_date,
								'.$this->tableName.'.finish_date,
								DATE_FORMAT('.$this->tableName.'.start_date, "'.$sqlFieldDateFormat.'") as mod_start_date,
								DATE_FORMAT('.$this->tableName.'.finish_date, "'.$sqlFieldDateFormat.'") as mod_finish_date,
								'.$this->tableName.'.minimum_adults,
								'.$this->tableName.'.maximum_adults,
								'.$this->tableName.'.discount_type,
								'.$this->tableName.'.discount_value,
								'.$this->tableName.'.apply_per,
								CONCAT(IF('.$this->tableName.'.discount_type = 0, "'.htmlentities($currency_symbol).'", ""), '.$this->tableName.'.discount_value, IF('.$this->tableName.'.discount_type = 1, "%", "")) as discount_formatted,
								'.$this->tableName.'.is_active,
								IF('.$this->tableName.'.group_id = 0, "'._ALL.'", cg.name) as group_name,
                                IF('.$this->tableName.'.hotel_id = 0, "'._ALL.'", hd.name) as hotel_name
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.$group_table.' cg ON '.$this->tableName.'.group_id = cg.id
                                LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON '.$this->tableName.'.hotel_id = hd.hotel_id AND hd.language_id = \''.$this->languageId.'\'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'campaign_name'  	=> array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'Campaign #_ '.@date('M Y'), 'validation_type'=>'text', 'unique'=>false, 'visible'=>true),
			'campaign_type'     => array('visible'=>GLOBAL_CAMPAIGNS == 'enabled' ? true : false, 'title'=>_TYPE, 'type'=>'enum', 'header_tooltip'=>_CAMPAIGNS_TOOLTIP, 'required'=>true, 'readonly'=>true, 'width'=>'', 'source'=>$arr_campaign_types_full, 'default'=>'', 'unique'=>false, 'javascript_event'=>''),
			'group_id'  	    => array('title'=>_TARGET_GROUP, 'type'=>'enum', 'required'=>false, 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_groups, 'unique'=>false, 'javascript_event'=>'', 'default_option'=>_ALL),
			'hotel_id'  	    => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _TARGET_FLAT : _TARGET_HOTEL, 'type'=>'enum', 'required'=>($objLogin->IsLoggedInAs('hotelowner') ? true : false), 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'unique'=>false, 'javascript_event'=>'', 'default_option'=>(!$objLogin->IsLoggedInAs('hotelowner', 'regionalmanager') ? _ALL : false)),
			'apply_per'  		=> array('title'=>_APPLY_PER, 'type'=>'enum', 'header_tooltip'=>htmlspecialchars(_APPLY_PER_DESCRIPTION), 'required'=>true, 'readonly'=>false, 'width'=>'140px', 'default'=>'1', 'source'=>$arr_campaign_apply_types, 'unique'=>false, 'javascript_event'=>''),
			'start_date'  		=> array('title'=>_START_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'50', 'max_year'=>'10'),
			'finish_date'  		=> array('title'=>_FINISH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'50', 'max_year'=>'10'),
			'minimum_adults'  	=> array('title'=>_MIN_ADULTS, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'100px', 'source'=>$arr_adults, 'unique'=>false, 'javascript_event'=>'', 'default'=>'0'),
			'maximum_adults'  	=> array('title'=>_MAX_ADULTS, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'100px', 'source'=>$arr_adults, 'unique'=>false, 'javascript_event'=>'', 'default'=>'0'),
			'discount_type'  	=> array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'140px', 'default'=>'1', 'source'=>$arr_discount_types, 'unique'=>false, 'javascript_event'=>''),
			'discount_value'  	=> array('title'=>_DISCOUNT, 'type'=>'textbox', 'header_tooltip'=>'', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'pre_html'=>'<span class="discount_currency">'.$currency_symbol.'</span> ', 'post_html'=>' <span class="discount_percent">%</span>'),
			'is_active'         => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'campaign_name'  	=> array('title'=>_NAME, 'type'=>'label'),
			'campaign_type'  	=> array('visible'=>GLOBAL_CAMPAIGNS == 'enabled' ? true : false, 'title'=>_TYPE, 'type'=>'enum', 'source'=>$arr_campaign_types_full),
			'group_name'  	    => array('title'=>_TARGET_GROUP, 'type'=>'label'),
            'hotel_name'  	    => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _TARGET_FLAT : _TARGET_HOTEL, 'type'=>'label'),
			'apply_per'  		=> array('title'=>_APPLY_PER, 'type'=>'enum', 'source'=>$arr_campaign_apply_types),
			'mod_start_date'  	=> array('title'=>_START_DATE, 'type'=>'label'),
			'mod_finish_date'   => array('title'=>_FINISH_DATE, 'type'=>'label'),
			'minimum_adults'	=> array('title'=>_MIN_ADULTS, 'type'=>'label'),
			'maximum_adults'	=> array('title'=>_MAX_ADULTS, 'type'=>'label'),
			'discount_formatted'=> array('title'=>_DISCOUNT, 'type'=>'label'),
			'is_active'         => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_html),
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
	 * Finds campaign info
	 * @param $from_date
	 * @param $to_date
	 * @param $campaign_type
	 * @param $where
	 * @param $order_by
	 * @return array
	 */
	public static function FindCampaigns($from_date = '', $to_date = '', $campaign_type = '', $where = '', $order_by = '')
	{
		global $objLogin;
		
		$sql = 'SELECT
					id,
					hotel_id,
					discount_type,
					discount_value,
					apply_per,
					minimum_adults,
					maximum_adults,
					start_date,
					finish_date
				FROM '.TABLE_CAMPAIGNS.'
				WHERE
					group_id = IF(group_id > 0, '.(int)$objLogin->GetLoggedGroupID().', 0) AND
					(
						(\''.$from_date.'\' <= start_date AND \''.$to_date.'\' > start_date) OR
						(\''.$from_date.'\' < finish_date AND \''.$to_date.'\' >= finish_date) OR
						(\''.$from_date.'\' >= start_date AND \''.$to_date.'\' < finish_date)
					) AND 
					is_active = 1 AND
					campaign_type = \''.$campaign_type.'\'
					'.(!empty($where) ? ' AND '.$where : '').'
				'.(!empty($order_by) ? $order_by : ' ORDER BY start_date DESC');
		
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);		
		return $result;
	}

	/**
	 * Return campaign info
	 * @param $campaign_id
	 * @param $from_date
	 * @param $to_date
	 * @param $campaign_type
	 * @param $where
	 */
	public static function GetCampaignInfo($campaign_id = '', $from_date = '', $to_date = '', $campaign_type = '', $where = '')
	{
		global $objLogin;

		if($campaign_type == 'standard'){
			$output = array();
			$result = self::FindCampaigns($from_date, $to_date, $campaign_type, $where);			
			if($result[1] > 0){
				for($i=0; $i<$result[1]; $i++){
					$cdate_from = ($from_date >= $result[0][$i]['start_date']) ? strtotime($from_date) : strtotime($result[0][$i]['start_date']);
					$cdate_to = ($to_date < $result[0][$i]['finish_date']) ? strtotime($to_date) : strtotime($result[0][$i]['finish_date']);
					while($cdate_from <= $cdate_to){
						if(!isset($output[date('Y-m-d', $cdate_from)])){
							$output[date('Y-m-d', $cdate_from)] = array();
						}
						$output[date('Y-m-d', $cdate_from)][$result[0][$i]['hotel_id']] = array(
							'id' => $result[0][$i]['id'],
                            'hotel_id' => $result[0][$i]['hotel_id'],
							'apply_per' => $result[0][$i]['apply_per'],
							'discount_type' => $result[0][$i]['discount_type'],
                            'discount_value' => $result[0][$i]['discount_value'],
							'minimum_adults' => $result[0][$i]['minimum_adults'],
							'maximum_adults' => $result[0][$i]['maximum_adults']
                        );
						$cdate_from = strtotime('+1 day', $cdate_from);
					}
				}
			}
		}else{			
			$output = array('id'=>'', 'discount_type'=>'', 'discount_value'=>'', 'minimum_adults', 'maximum_adults');
			$from_date = (!empty($from_date)) ? $from_date : @date('Y-m-d');
			$to_date   = (!empty($to_date)) ? $to_date : @date('Y-m-d');
			
			$sql = 'SELECT
						id,
                        hotel_id,
						discount_type,
						discount_value,
						apply_per,
						minimum_adults,
						maximum_adults,
						DATE_FORMAT(start_date, \'%M %d\') as start_date,
						DATE_FORMAT(finish_date, \'%M %d, %Y\') as finish_date,
						DATE_FORMAT(finish_date, \'%m/%d/%Y\') as formated_finish_date,
						DATE_FORMAT(finish_date, \'%Y\') as fd_y,
						DATE_FORMAT(finish_date, \'%m\') as fd_m,
						DATE_FORMAT(finish_date, \'%d\') as fd_d
					FROM '.TABLE_CAMPAIGNS.'
					WHERE
						group_id = IF(group_id > 0, '.(int)$objLogin->GetLoggedGroupID().', 0) AND
						\''.$from_date.'\' >= start_date AND
						\''.$to_date.'\' <= finish_date AND
						is_active = 1 
						'.(($campaign_type != '') ? ' AND campaign_type = \''.$campaign_type.'\'' : '').'
						'.(($campaign_id != '') ? ' AND id='.(int)$campaign_id : '').'
						'.(!empty($where) ? ' AND '.$where : '').'
					ORDER BY start_date DESC';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$output['id'] = $result[0]['id'];
                $output['hotel_id'] = $result[0]['hotel_id'];
				$output['apply_per'] = $result[0]['apply_per'];
				$output['discount_type'] = $result[0]['discount_type']; 
				$output['discount_value'] = $result[0]['discount_value']; 
				$output['minimum_adults'] = $result[0]['minimum_adults']; 
				$output['maximum_adults'] = $result[0]['maximum_adults']; 
			}
		}
		
		return $output;		
	}
    
	/**
	 * Draws campaign small banner
	 * @param $hotel_id
	 * @param $draw
	 */
	public static function DrawCampaignSmallBanner($hotel_id ='', $draw = true)
	{
        if(!$hotel_id) return '';
        
		global $objLogin, $objSettings;
		$output = '';
		
		$sql = 'SELECT
					c.id,
                    c.hotel_id,
					c.discount_type,
					c.discount_value,
					c.apply_per,
					c.minimum_adults,
					c.maximum_adults,
					c.start_date,
					c.finish_date,
                    c.hotel_id,
                    IF(c.hotel_id = 0, "'._ALL.'", hd.name) as hotel_name,
					DATE_FORMAT(c.start_date, \'%Y\') as sd_y,
					DATE_FORMAT(c.start_date, \'%m\') as sd_m,
					DATE_FORMAT(c.start_date, \'%d\') as sd_d,
					DATE_FORMAT(c.finish_date, \'%Y\') as fd_y,
					DATE_FORMAT(c.finish_date, \'%m\') as fd_m,
					DATE_FORMAT(c.finish_date, \'%d\') as fd_d
				FROM '.TABLE_CAMPAIGNS.' c
                    LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON c.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE
					c.group_id = IF(c.group_id > 0, '.(int)$objLogin->GetLoggedGroupID().', 0) AND
                    c.hotel_id = IF(c.hotel_id > 0, '.(int)$hotel_id.', 0) AND
                    c.is_active = 1 AND
                    c.campaign_type = \'standard\'
                ORDER BY c.start_date DESC';

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        if($result[1] > 0){			
            // replace name for hotel
            $discount_campaign_text = _DISCOUNT_STD_CAMPAIGN_TEXT;
            $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _THIS_FLAT : _THIS_HOTEL;
            if($result[0][0]['hotel_id'] > 0){
                $hotel_name = $result[0][0]['hotel_name'];
            }
            $discount_campaign_text = str_replace('_HOTEL_', $hotel_name, $discount_campaign_text);

            $msg = '<div class="row">
				<div class="col-md-10">
                    '.$discount_campaign_text;
					
                    for($i=0; $i<$result[1]; $i++){
                        $s_d = $result[0][$i]['sd_d'];
                        $s_m = get_month_local($result[0][$i]['sd_m']);
                        $s_y = $result[0][$i]['sd_y'];
                        if($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'){
                            $start_date_short = $s_d.' '.$s_m;
                            $start_date = $s_d.' '.$s_m.', '.$s_y;
                        }else{
                            $start_date_short = $s_m.' '.$s_d;
                            $start_date = $s_m.' '.$s_d.', '.$s_y;
                        }
            
                        $f_d = $result[0][$i]['fd_d'];
                        $f_m = get_month_local($result[0][$i]['fd_m']);
                        $f_y = $result[0][$i]['fd_y'];
                        if($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'){
                            $finish_date = $f_d.' '.$f_m.', '.$f_y;
                        }else{
                            $finish_date = $f_m.' '.$f_d.', '.$f_y;
                        }
                        $msg .= '<br>'._FROM.' <b><i>'.$start_date.'</i></b> '._TO.' <b><i>'.$finish_date.'</i></b> - <span style="font-size:15px;color:#a13a3a;"><b>';
						if($result[0][$i]['discount_type'] == '1'){
							$msg .= number_format($result[0][$i]['discount_value'], 0).'%';	
						}else{
							$msg .= Application::Get('currency_symbol').number_format($result[0][$i]['discount_value'], 0);	
						}
						$msg .= '</b></span>';
						if(!empty($result[0][$i]['minimum_adults']) || !empty($result[0][$i]['maximum_adults'])){
							$hyphen = '';
							if(empty($result[0][$i]['minimum_adults'])){
								$result[0][$i]['minimum_adults'] = '';
							}elseif(empty($result[0][$i]['maximum_adults'])){
								$result[0][$i]['maximum_adults'] = '';
							}elseif($result[0][$i]['minimum_adults'] == $result[0][$i]['maximum_adults']){
								$result[0][$i]['minimum_adults'] = '';								
							}else{
								$hyphen = ' - ';
							}
							$msg .= ' <br>('.str_ireplace(array('_MIN_', '_MAX_'), array($result[0][$i]['minimum_adults'].$hyphen, $result[0][$i]['maximum_adults']), _DISCOUNT_STD_CAMPAIGN_TEXT_EXT). ')';
						}
                    }
                $msg .= '</div>
                <div class="col-md-2 hidden-xs">
					<img src="images/discount_tag.gif" alt="discount" class="right" />
				</div>
            </div>';

			$output .= draw_message($msg, false);			
		}
        
		if($draw) echo $output;
		else return $output;
    }
    

	/**
	 * Draws campaign banner
	 * @param $campaign_type
	 * @param $draw
	 */
	public static function DrawCampaignBanner($campaign_type = 'global', $draw = true)
	{
		global $objLogin, $objSettings;
		$output = '';
		
		$sql = 'SELECT
					c.id,
					c.discount_type,
					c.discount_value,
					c.apply_per,
					c.start_date,
					c.finish_date,
					c.minimum_adults,
					c.maximum_adults,
                    c.hotel_id,
                    IF(c.hotel_id = 0, "'._ALL.'", hd.name) as hotel_name,
					DATE_FORMAT(c.start_date, \'%Y\') as sd_y,
					DATE_FORMAT(c.start_date, \'%m\') as sd_m,
					DATE_FORMAT(c.start_date, \'%d\') as sd_d,
					DATE_FORMAT(c.finish_date, \'%Y\') as fd_y,
					DATE_FORMAT(c.finish_date, \'%m\') as fd_m,
					DATE_FORMAT(c.finish_date, \'%d\') as fd_d
				FROM '.TABLE_CAMPAIGNS.' c
                    LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON c.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
				WHERE
					c.group_id = IF(c.group_id > 0, '.(int)$objLogin->GetLoggedGroupID().', 0) AND
                    c.is_active = 1 ';
		if($campaign_type == 'standard'){
			$sql .= ' AND c.campaign_type = \'standard\'';				
		}else{
			$sql .= ' AND \''.@date('Y-m-d').'\' >= c.start_date
					  AND \''.@date('Y-m-d').'\' <= c.finish_date
					  AND c.campaign_type = \'global\'';
		}				
		$sql .= 'ORDER BY c.start_date DESC';

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        if($result[1] > 0){			
			if($campaign_type == 'standard'){

                // replace name for hotel
				$discount_campaign_text = _DISCOUNT_STD_CAMPAIGN_TEXT;
                $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _OUR_FLATS : _OUR_HOTELS;
                if($result[0][0]['hotel_id'] > 0){
                    $hotel_name = $result[0][0]['hotel_name'];
                }
                $discount_campaign_text = str_replace('_HOTEL_', $hotel_name, $discount_campaign_text);                    

				$msg = '<div class="row">
					<div class="col-md-10">
						'.$discount_campaign_text.'
						<br>';
						for($i=0; $i<$result[1]; $i++){
							$s_d = $result[0][$i]['sd_d'];
							$s_m = get_month_local($result[0][$i]['sd_m']);
							$s_y = $result[0][$i]['sd_y'];
                            if($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'){
                                $start_date_short = $s_d.' '.$s_m;
                                $start_date = $s_d.' '.$s_m.', '.$s_y;
                            }else{
                                $start_date_short = $s_m.' '.$s_d;
                                $start_date = $s_m.' '.$s_d.', '.$s_y;
                            }
				
							$f_d = $result[0][$i]['fd_d'];
							$f_m = get_month_local($result[0][$i]['fd_m']);
							$f_y = $result[0][$i]['fd_y'];
                            if($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'){
                                $finish_date = $f_d.' '.$f_m.', '.$f_y;
                            }else{
                                $finish_date = $f_m.' '.$f_d.', '.$f_y;
                            }
							$msg .= _FROM.' <b><i>'.$start_date.'</i></b> '._TO.' <b><i>'.$finish_date.'</i></b> - <span style="font-size:15px;color:#a13a3a;"><b>';
							if($result[0][$i]['discount_type'] == '1'){
								$msg .= number_format($result[0][$i]['discount_value'], 0).'%';	
							}else{
								$msg .= Application::Get('currency_symbol').number_format($result[0][$i]['discount_value'], 0);	
							}
							$msg .= '</b></span>';
							if(!empty($result[0][$i]['minimum_adults']) || !empty($result[0][$i]['maximum_adults'])){
								$hyphen = '';
								if(empty($result[0][$i]['minimum_adults'])){
									$result[0][$i]['minimum_adults'] = '';
								}elseif(empty($result[0][$i]['maximum_adults'])){
									$result[0][$i]['maximum_adults'] = '';
								}elseif($result[0][$i]['minimum_adults'] == $result[0][$i]['maximum_adults']){
									$result[0][$i]['minimum_adults'] = '';								
								}else{
									$hyphen = ' - ';
								}
								$msg .= ' <br>('.str_ireplace(array('_MIN_', '_MAX_'), array($result[0][$i]['minimum_adults'].$hyphen, $result[0][$i]['maximum_adults']), _DISCOUNT_STD_CAMPAIGN_TEXT_EXT). ')';
							}
						}
					$msg .= '</div>
					<div class="col-md-2 hidden-xs">
						<img src="images/discount_tag.gif" alt="discount" class="right" />
					</div>
				</div>';
			}else{			
				$s_d = $result[0][0]['sd_d'];
				$s_m = get_month_local($result[0][0]['sd_m']);
				$s_y = $result[0][0]['sd_y'];
                if($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'){
                    $start_date_short = $s_d.' '.$s_m;
                    $start_date = $s_d.' '.$s_m.', '.$s_y;
                }else{
                    $start_date_short = $s_m.' '.$s_d;
                    $start_date = $s_m.' '.$s_d.', '.$s_y;
                }
	
				$f_d = $result[0][0]['fd_d'];
				$f_m = get_month_local($result[0][0]['fd_m']);
				$f_y = $result[0][0]['fd_y'];
                if($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'){
                    $finish_date = $f_d.' '.$f_m.', '.$f_y;
                }else{
                    $finish_date = $f_m.' '.$f_d.', '.$f_y;
                }
				
                // replace name for hotel
				$discount_campaign_text = FLATS_INSTEAD_OF_HOTELS ? _FLATS_DISCOUNT_CAMPAIGN_TEXT : _DISCOUNT_CAMPAIGN_TEXT;
                $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
                if($result[0][0]['hotel_id'] > 0){
                    $hotel_name = $result[0][0]['hotel_name'];
                }
                $discount_campaign_text = str_replace('_HOTEL_', $hotel_name, $discount_campaign_text);                    
				
				if($result[0][0]['start_date'] != $result[0][0]['finish_date']){
					$discount_campaign_text = str_replace('_FROM_', _FROM.' <b>'.(($result[0][0]['sd_y'] == $result[0][0]['fd_y']) ? $start_date_short : $start_date).'</b>', $discount_campaign_text);
					$discount_campaign_text = str_replace('_TO_', _TO.' <b>'.$finish_date.'</b>', $discount_campaign_text);
					if($result[0][0]['discount_type'] == '1'){
						$discount_value = number_format($result[0][0]['discount_value'], 0).'%';	
					}else{
						$discount_value = Application::Get('currency_symbol').number_format($result[0][0]['discount_value'], 0);	
					}
					$discount_campaign_text = str_replace('_PERCENT_', '<span style="color:#a13a3a;font-size:15px;"><b>'.$discount_value.'</b></span>', $discount_campaign_text);
				}else{
					$discount_campaign_text = str_replace('_FROM_', '', $discount_campaign_text);
					$discount_campaign_text = str_replace('_TO_', '<b>'.$finish_date.'</b> '._ONLY, $discount_campaign_text);
					if($result[0][0]['discount_type'] == '1'){
						$discount_value = number_format($result[0][0]['discount_value'], 0).'%';	
					}else{
						$discount_value = Application::Get('currency_symbol').number_format($result[0][0]['discount_value'], 0);	
					}
					$discount_campaign_text = str_replace('_PERCENT_', '<span style="color:#a13a3a;font-size:15px;"><b>'.number_format($discount_value, 0).'</b></span>', $discount_campaign_text);
				}
				$msg = '<div class="row">
					<div class="col-md-10">
						'.$discount_campaign_text.'
					</td>
					<div class="col-md-2 hidden-xs">
						<img src="images/discount_tag.gif" alt="discount" class="right" />
						<span style="color:#a13a3a;font-weight:bold;font-size:18px;"><b>';
						if($result[0][0]['discount_type'] == '1'){
							$msg .= number_format($result[0][0]['discount_value'], 0).'%';	
						}else{
							$msg .= Application::Get('currency_symbol').number_format($result[0][0]['discount_value'], 0);	
						}
						$msg .= '</b></span>
					</div>
				</div>';								
			}
			$output .= draw_message($msg, false);			
		}
		if($draw) echo $output;
		else return $output;
	}

	/**
	 *	Before-Insertion record
	 */
	public function BeforeInsertRecord()
	{
		if(!$this->CheckAdultsMinMax()) return false;
		if(!$this->ValidateDiscountType()) return false;
		if(!$this->CheckStartFinishDate()) return false;
		if(!$this->CheckDateOverlapping()) return false;		
		return true;
	}

	/**
	 *	Before-updating record
	 */
	public function BeforeUpdateRecord()
	{
		if(!$this->CheckAdultsMinMax()) return false;
		if(!$this->ValidateDiscountType()) return false;
		if(!$this->CheckStartFinishDate()) return false;
		if(!$this->CheckDateOverlapping()) return false;		
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
	 * Validate adults values
	 */
	private function CheckAdultsMinMax()
	{
		$minimum_adults = MicroGrid::GetParameter('minimum_adults', false);
		$maximum_adults = MicroGrid::GetParameter('maximum_adults', false);
		
		if($minimum_adults > $maximum_adults){
			$this->error = str_replace('_FIELD_', '<b>'._MAX_ADULTS.'</b>', _FIELD_VALUE_MINIMUM);
			$this->error = str_replace('_MIN_', $minimum_adults, $this->error);
			return false;
		}	
		return true;		
	}
	
	/**
	 * Validate discount type
	 */
	private function ValidateDiscountType()
	{
		$discount_type = MicroGrid::GetParameter('discount_type', false);
		$discount_value = MicroGrid::GetParameter('discount_value', false);
			
		if($discount_value <= 0){
			$this->error = str_replace('_FIELD_', '<b>'._DISCOUNT.'</b>', _FIELD_VALUE_MINIMUM);
			$this->error = str_replace('_MIN_', '1', $this->error);
			return false;
		}
		
		if($discount_type == '1'){
			if($discount_value > 100){
				$this->error = str_replace('_FIELD_', '<b>'._DISCOUNT.'</b>', _FIELD_VALUE_EXCEEDED);
				$this->error = str_replace('_MAX_', number_format(100), $this->error);
				return false;
			}			
		}else{
			if($discount_value > 1000000){
				$this->error = str_replace('_FIELD_', '<b>'._DISCOUNT.'</b>', _FIELD_VALUE_EXCEEDED);
				$this->error = str_replace('_MAX_', number_format(1000000), $this->error);
				return false;
			}
		}
		
		return true;
	}	

	/**
	 * Check if there is a date overlapping
	 */
	private function CheckDateOverlapping()
	{
		$rid = MicroGrid::GetParameter('rid');
		$hotel_id = MicroGrid::GetParameter('hotel_id', false);
		$group_id = MicroGrid::GetParameter('group_id', false);
		$start_date = MicroGrid::GetParameter('start_date', false);
		$finish_date = MicroGrid::GetParameter('finish_date', false);
		$minimum_adults = MicroGrid::GetParameter('minimum_adults', false);
		$maximum_adults = MicroGrid::GetParameter('maximum_adults', false);

		$sql = "SELECT * FROM ".TABLE_CAMPAIGNS."
				WHERE
					id != ".(int)$rid." AND
					hotel_id = ".(int)$hotel_id." AND
					group_id = ".(int)$group_id." AND
					is_active = 1 AND
					(
						('".$minimum_adults."' >= minimum_adults) AND ('".$minimum_adults."' <= maximum_adults) OR
						('".$maximum_adults."' >= minimum_adults) AND ('".$maximum_adults."' <= maximum_adults) OR
						('".$minimum_adults."' <= minimum_adults) AND ('".$maximum_adults."' >= maximum_adults)
					) AND
					(
						(('".$start_date."' > start_date) AND ('".$start_date."' < finish_date)) OR
						(('".$finish_date."' > start_date) AND ('".$finish_date."' < finish_date)) OR
						(('".$start_date."' <= start_date) AND ('".$finish_date."' >= finish_date))
					)";	
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$this->error = _GROUP_TIME_OVERLAPPING_ALERT;
			return false;
		}
		return true;
	}	

	/**
	 * Updates campaign status
	 */
	public static function UpdateStatus()
	{
		$sql = 'UPDATE '.TABLE_CAMPAIGNS.'
				SET is_active = 0
				WHERE finish_date < \''.@date('Y-m-d').'\' AND is_active = 1';    
		database_void_query($sql);
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

}
