<?php

/**
 *	Coupons Class
 *  --------------
 *	Description : encapsulates methods and properties for Coupons
 *	Written by  : ApPHP
 *	Version     : 1.0.1
 *  Updated	    : 07.04.2018
 *  Usage       : HotelSite, HotelBooking
 *	Differences : no
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct             GetCouponInfo           CheckStartFinishDate
 *	__destruct              GetCouponByCustomerId	CheckRecordAssigned
 *	BeforeInsertRecord								ValidateDiscountType	
 *	BeforeUpdateRecord
 *	BeforeEditRecord
 *	BeforeDetailsRecord
 *	BeforeDeleteRecord
 *
 *  1.0.2
 *      - added discount_type, discount_value
 *  	-
 *  	-
 *  	-
 *  	-
 *  1.0.1
 *      - added 'enum' types instead of SQL CASEs
 *      - replaced " with '
 *      - added SetLocale
 *      - maximum value for discount 100%
 *      - added filtering by coupon_code
 *	
 **/


class Coupons extends MicroGrid {
	
	protected $debug = false;
	protected $assigned_to_hotels = '';

    private $regionalManager = false;
    private $hotelOwner = false;
	
	// #001 private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
        
        global $objLogin;

        $this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager') ? true : false;
        $this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner') ? true : false;

		$this->params = array();
		
		## for standard fields
		if(isset($_POST['coupon_code'])) 	$this->params['coupon_code'] = prepare_input($_POST['coupon_code']);
        if(isset($_POST['hotel_id'])) 		$this->params['hotel_id'] = (int)$_POST['hotel_id'];
		if(isset($_POST['date_started'])) 	$this->params['date_started'] = prepare_input($_POST['date_started']);
		if(isset($_POST['date_finished'])) 	$this->params['date_finished'] = prepare_input($_POST['date_finished']);
        if(isset($_POST['max_amount'])) 	$this->params['max_amount'] = (int)$_POST['max_amount'];
		if(isset($_POST['comments'])) 		$this->params['comments'] = prepare_input($_POST['comments']);
		if(isset($_POST['discount_type']))  	$this->params['discount_type'] = prepare_input($_POST['discount_type']);
		if(isset($_POST['discount_value']))  	$this->params['discount_value'] = prepare_input($_POST['discount_value']);
		
		## for checkboxes 
		$this->params['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : '0';

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

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_COUPONS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_booking_coupons';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();

		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		$hotels_list = '';
		if($this->hotelOwner){
			$hotels = $objLogin->AssignedToHotels();
			$hotels_list = implode(',', $hotels);
			$this->assigned_to_hotels = ' AND '.(!empty($hotels_list) ? $this->tableName.'.hotel_id IN ('.$hotels_list.') ' : '1 = 0 ');
		}elseif($this->regionalManager){
			$hotels = AccountLocations::GetHotels($objLogin->GetLoggedID());
			if(empty($hotels)){
				$this->actions['add'] = false;
			}
			$travel_agency_ids = AccountLocations::GetTravelAgencies($objLogin->GetLoggedID());
			$hotels_list = !empty($hotels) ? implode(',', $hotels) : '-1';
			$travel_agency_list = !empty($travel_agency_ids) ? implode(',', $travel_agency_ids) : '-1';
			$this->assigned_to_hotels = ' AND ('.$this->tableName.'.hotel_id IN ('.$hotels_list.') OR '.$this->tableName.'.customer_id IN ('.$travel_agency_list.')) ';
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_hotels;

        // prepare hotels array
        $where_clause = '';
        if($this->regionalManager || $this->hotelOwner){
            $where_clause = !empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : '1 = 0';
        }
        $total_hotels = Hotels::GetAllHotels($where_clause);
		$arr_hotels = array();
		foreach($total_hotels[0] as $key => $val){
			$arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
		}
		
		$currency_symbol = Application::Get('currency_symbol');

		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.id DESC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);
		
        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
            $hotel_name		=> array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels, 'sign'=>'=', 'width'=>'130px'),
			_COUPON_CODE  	=> array('table'=>$this->tableName, 'field'=>'coupon_code', 'type'=>'text', 'sign'=>'like%', 'width'=>'170px', 'visible'=>true),
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
		);
		
		$arr_active = array('0'=>_NO, '1'=>_YES);
		$arr_discount = array();
		for($i=0; $i<=100; $i+=5){
			$arr_discount[$i] = $i;
			if($i == 30) $arr_discount[33] = 33;
			else if($i == 60) $arr_discount[66] = 66;
		}
		
		$arr_discount_types = array('0'=>_FIXED_PRICE, '1'=>_PERCENTAGE);
		
		$new_coupon_code = strtoupper(get_random_string(4).'-'.get_random_string(4).'-'.get_random_string(4).'-'.get_random_string(4));
		$date_format = get_date_format('view');
		$date_format_edit = get_date_format('edit');				
		$currency_format = get_currency_format();

		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		
		global $objSettings;
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$sqlFieldDateFormat = '%b %d, %Y';
		}else{
			$sqlFieldDateFormat = '%d %b, %Y';
		}

        // set locale time names
		$this->SetLocale(Application::Get('lc_time_name'));
		
		// Set settings about agencies
		$agencies_allowed = (ModulesSettings::Get('customers', 'allow_agencies') == 'yes') ? true : false;

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		/// $this->arrTranslations = $this->PrepareTranslateFields(
		///	array('field1', 'field2')
		/// );
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		/// $sql_translation_description = $this->PrepareTranslateSql(
		///	TABLE_XXX_DESCRIPTION,
		///	'gallery_album_id',
		///	array('field1', 'field2')
		/// );
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.coupon_code,
									'.$this->tableName.'.hotel_id,
									DATE_FORMAT('.$this->tableName.'.date_started, "'.$sqlFieldDateFormat.'") as date_started,
									DATE_FORMAT('.$this->tableName.'.date_finished, "'.$sqlFieldDateFormat.'") as date_finished,
									'.$this->tableName.'.discount_type,
									'.$this->tableName.'.discount_value,
									CONCAT(IF('.$this->tableName.'.discount_type = 0, "'.htmlentities($currency_symbol).'", ""), '.$this->tableName.'.discount_value, IF('.$this->tableName.'.discount_type = 1, "%", "")) as discount_formatted,
									IF('.$this->tableName.'.max_amount >=0, '.$this->tableName.'.max_amount, "'.htmlentities(_UNLIMITED).'") as max_amount,
									(SELECT COUNT(*) cnt FROM '.TABLE_BOOKINGS.' WHERE coupon_code = '.$this->tableName.'.coupon_code) as times_used,
									'.$this->tableName.'.comments,
									'.$this->tableName.'.is_active,
									'.($agencies_allowed ? 'CONCAT("<a href=\"javascript:void(\'customer|view\')\" onclick=\"open_popup(\'popup.ajax.php\',\'customer\',\'",'.$this->tableName.'.customer_id,"\', \'",cust.customer_type, "\', \''.Application::Get('token').'\')\">", CONCAT(cust.last_name, " ", cust.first_name), "</a>") as customer_name, ' : '').'
									'.$this->tableName.'.customer_id
								FROM '.$this->tableName.'
									'.($agencies_allowed ? 'LEFT OUTER JOIN '.TABLE_CUSTOMERS.' cust ON '.$this->tableName.'.customer_id = cust.id ' : '').'
                                ';		
		// define view mode fields
		$this->arrViewModeFields = array();
		$this->arrViewModeFields['coupon_code']      = array('title'=>_COUPON_CODE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'');
		if($agencies_allowed){
			$this->arrViewModeFields['customer_name'] = array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'left', 'width'=>'170px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'');
		}
        $this->arrViewModeFields['hotel_id']       	 = array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT :_HOTEL, 'type'=>'enum', 'align'=>'left', 'width'=>'140px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'source'=>$arr_hotels);
		$this->arrViewModeFields['date_started']     = array('title'=>_START_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'');
		$this->arrViewModeFields['date_finished']    = array('title'=>_FINISH_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'');
		$this->arrViewModeFields['discount_formatted'] = array('title'=>_DISCOUNT, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'');
		$this->arrViewModeFields['max_amount'] 		 = array('title'=>_MAX_NUMBER_OF_USES, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'post_html'=>'');
		$this->arrViewModeFields['times_used'] 		 = array('title'=>_TIMES_USED, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'post_html'=>'');
		$this->arrViewModeFields['is_active']        = array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active);
		
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
			'hotel_id'  	   => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _TARGET_FLAT:_TARGET_HOTEL, 'type'=>'enum', 'required'=>($objLogin->IsLoggedInAs('hotelowner','regionalmanager') ? true : false), 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'unique'=>false, 'javascript_event'=>'', 'default_option'=>(!$objLogin->IsLoggedInAs('hotelowner', 'regionalmanager') ? _ALL : false)),
			'coupon_code'      => array('title'=>_COUPON_CODE, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'19', 'default'=>$new_coupon_code, 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			'date_started'     => array('title'=>_START_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10'),
			'date_finished'    => array('title'=>_FINISH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10'),
			'discount_type'    => array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'140px', 'default'=>'1', 'source'=>$arr_discount_types, 'unique'=>false, 'javascript_event'=>''),
			//'discount_value'   => array('title'=>_DISCOUNT, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'65px', 'source'=>$arr_discount, 'unique'=>false, 'javascript_event'=>'', 'validation_minimum'=>'1', 'post_html'=>' %'),
			'discount_value'   => array('title'=>_DISCOUNT, 'type'=>'textbox', 'header_tooltip'=>'', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'pre_html'=>'<span class="discount_currency">'.$currency_symbol.'</span> ', 'post_html'=>' <span class="discount_percent">%</span>'),
			'max_amount'       => array('title'=>_MAX_NUMBER_OF_USES, 'type'=>'textbox', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>100, 'validation_type'=>'numeric', 'validation_minimum'=>'-1', 'validation_maximum'=>'10000', 'unique'=>false, 'visible'=>true, 'post_html'=>' ('.str_replace('_VAL_', '-1', _TO_SET_UNLIMITED_TOOLTIP).')'),
			'comments'         => array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'validation_maxlength'=>'512', 'unique'=>false),
			'is_active'        => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
                                '.$this->tableName.'.hotel_id,
								'.$this->tableName.'.coupon_code,
								'.$this->tableName.'.date_started,
								'.$this->tableName.'.date_finished,
								DATE_FORMAT('.$this->tableName.'.date_started, "'.$sqlFieldDateFormat.'") as mod_date_started,
								DATE_FORMAT('.$this->tableName.'.date_finished, "'.$sqlFieldDateFormat.'") as mod_date_finished,
								'.$this->tableName.'.discount_type,
								'.$this->tableName.'.discount_value,
								CONCAT(IF('.$this->tableName.'.discount_type = 0, "'.htmlentities($currency_symbol).'", ""), '.$this->tableName.'.discount_value, IF('.$this->tableName.'.discount_type = 1, "%", "")) as discount_formatted,
								'.$this->tableName.'.max_amount,
								(SELECT COUNT(*) cnt FROM '.TABLE_BOOKINGS.' WHERE coupon_code = '.$this->tableName.'.coupon_code) as times_used,
								'.$this->tableName.'.comments,
								'.$this->tableName.'.is_active,
								'.($agencies_allowed ? 'CONCAT(cust.last_name, " ", cust.first_name, " ID:", cust.id) as customer_name, ' : '').'
                                IF('.$this->tableName.'.hotel_id = 0, "'._ALL.'", hd.name) as hotel_name
							FROM '.$this->tableName.'
                                LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON '.$this->tableName.'.hotel_id = hd.hotel_id AND hd.language_id = \''.$this->languageId.'\'
								'.($agencies_allowed ? 'LEFT OUTER JOIN '.TABLE_CUSTOMERS.' cust ON '.$this->tableName.'.customer_id = cust.id ' : '').'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array();
		if($agencies_allowed){
			$this->arrEditModeFields['customer_name'] = array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'left', 'width'=>'170px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'');
		} 
		$this->arrEditModeFields['hotel_id']  	     = array('title'=>FLATS_INSTEAD_OF_HOTELS ? _TARGET_FLAT : _TARGET_HOTEL, 'type'=>'enum', 'required'=>($objLogin->IsLoggedInAs('hotelowner') ? true : false), 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'unique'=>false, 'javascript_event'=>'', 'default_option'=>(!$objLogin->IsLoggedInAs('hotelowner', 'regionalmanager') ? _ALL : false));
		$this->arrEditModeFields['coupon_code']      = array('title'=>_COUPON_CODE, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'19', 'default'=>$new_coupon_code, 'validation_type'=>'', 'unique'=>false, 'visible'=>true);
		$this->arrEditModeFields['date_started']     = array('title'=>_START_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10');
		$this->arrEditModeFields['date_finished']    = array('title'=>_FINISH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10');
		$this->arrEditModeFields['discount_type']  	 = array('title'=>_DISCOUNT_TYPE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'140px', 'default'=>'1', 'source'=>$arr_discount_types, 'unique'=>false, 'javascript_event'=>'');
		///$this->arrEditModeFields['discount_value']   = array('title'=>_DISCOUNT, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'65px', 'source'=>$arr_discount, 'unique'=>false, 'javascript_event'=>'', 'validation_minimum'=>'1', 'post_html'=>' %');
		$this->arrEditModeFields['discount_value']   = array('title'=>_DISCOUNT, 'type'=>'textbox', 'header_tooltip'=>'', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_minimum'=>'1', 'pre_html'=>'<span class="discount_currency">'.$currency_symbol.'</span> ', 'post_html'=>' <span class="discount_percent">%</span>');
		$this->arrEditModeFields['comments']         = array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'validation_maxlength'=>'512', 'unique'=>false);
		$this->arrEditModeFields['max_amount']       = array('title'=>_MAX_NUMBER_OF_USES, 'type'=>'textbox', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>100, 'validation_type'=>'numeric', 'validation_minimum'=>'-1', 'validation_maximum'=>'10000', 'unique'=>false, 'visible'=>true, 'post_html'=>' ('.str_replace('_VAL_', '-1', _TO_SET_UNLIMITED_TOOLTIP).')');
		$this->arrEditModeFields['times_used']       = array('title'=>_TIMES_USED, 'type'=>'label');
		$this->arrEditModeFields['is_active']        = array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array();
		if($agencies_allowed){
			$this->arrDetailsModeFields['customer_name'] = array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'left', 'width'=>'170px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'');
		}
        $this->arrDetailsModeFields['hotel_name']  	    	= array('title'=>FLATS_INSTEAD_OF_HOTELS ? _TARGET_FLAT : _TARGET_HOTEL, 'type'=>'label');
		$this->arrDetailsModeFields['coupon_code']    		= array('title'=>_COUPON_CODE, 'type'=>'label');
		$this->arrDetailsModeFields['mod_date_started']  	= array('title'=>_START_DATE, 'type'=>'label');
		$this->arrDetailsModeFields['mod_date_finished'] 	= array('title'=>_FINISH_DATE, 'type'=>'label');
		$this->arrDetailsModeFields['discount_formatted']   = array('title'=>_DISCOUNT, 'type'=>'label');
		$this->arrDetailsModeFields['comments']         	= array('title'=>_COMMENTS, 'type'=>'label');
		$this->arrDetailsModeFields['max_amount']       	= array('title'=>_MAX_NUMBER_OF_USES, 'type'=>'label');
		$this->arrDetailsModeFields['times_used']       	= array('title'=>_TIMES_USED, 'type'=>'label');
		$this->arrDetailsModeFields['is_active']        	= array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		/// $this->AddTranslateToModes(
		/// $this->arrTranslations,
		/// array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'', 'readonly'=>false),
		/// 	  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'readonly'=>false)
		/// )
		/// );
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
	 * Before-Insertion record
	 */
	public function BeforeInsertRecord()
	{
		if(!$this->ValidateDiscountType()) return false;
		if(!$this->CheckStartFinishDate()) return false;
		return true;
	}
	
	/**
	 * Before-updating record
	 */
	public function BeforeUpdateRecord()
	{
		if(!$this->ValidateDiscountType()) return false;
		if(!$this->CheckStartFinishDate()) return false;
		return true;
	}
	
	/**
	 * Before-editing record
	 */
	public function BeforeEditRecord()
	{ 
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
	 * Before-deleting record
	 */
	public function BeforeDeleteRecord()
	{
		return $this->CheckRecordAssigned($this->curRecordId);
	}
	
	/**
	 * Get coupon info
	 */
	public static function GetCouponInfo($coupon_code = '')
	{
		global $objLogin;
		
		if(empty($coupon_code)) return false;
		
		$output = array();
		$is_agency = $objLogin->GetCustomerType() == 1 ? true : false;
		
		$current_date = @date('Y-m-d');
		$sql = 'SELECT * FROM '.TABLE_COUPONS.' as cp
					'.($is_agency ? 'LEFT OUTER JOIN '.TABLE_CUSTOMERS.' as cm ON cm.id = cp.customer_id' : '').'
				WHERE
					cp.coupon_code = \''.$coupon_code.'\' AND
					'.($is_agency ? 'cp.customer_id = '.$objLogin->GetLoggedID().' AND cm.customer_type = 1 AND ' : 'cp.customer_id = 0 AND ').'
					cp.is_active = 1 AND 
					(\''.$current_date.'\' >= cp.date_started AND \''.$current_date.'\' <= cp.date_finished)';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output = $result[0];
		}
		return $output;
	}
	
	/**
	 * Get coupon info
	 * @param int $customer_id
	 * @param string $field
	 */
	public static function GetCouponByCustomerId($customer_id = 0, $field = '')
	{
		if(empty($customer_id)) return false;
		
		$output = array();
		
		$sql = 'SELECT * FROM '.TABLE_COUPONS.'
				WHERE
					customer_id = '.(int)$customer_id.' AND
					is_active = 1';	
		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			if(!empty($field)){
				$output = isset($result[0][$field]) ? $result[0][$field] : '';
			}else{
				$output = $result[0];	
			}			
		}
		
		if(empty($output)){
			return !empty($field) ? '' : array();	
		}else{
			return $output;	
		}
	}

	/**
	 * Check if start date is greater than finish date
	 */
	private function CheckStartFinishDate()
	{
		$date_started = MicroGrid::GetParameter('date_started', false);
		$date_finished = MicroGrid::GetParameter('date_finished', false);
		
		if($date_started > $date_finished){
			$this->error = _START_FINISH_DATE_ERROR;
			$this->errorField = 'date_finished';
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
	 * Updates coupons status
	 */
	public static function UpdateStatus()
	{
		$sql = 'UPDATE '.TABLE_COUPONS.'
				SET is_active = 0
				WHERE date_finished < \''.@date('Y-m-d').'\' AND is_active = 1';    
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
