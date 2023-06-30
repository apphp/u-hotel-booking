<?php

/**
 * HotelPaymentGateways Class
 * --------------
 * Description : encapsulates methods and properties
 * Written by  : ApPHP
 * Updated	    : 14.03.2016
 * Usage       : Core Class (ALL)
 *
 * PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 * __construct             GetPaymentInfo
 * __destruct
 * 
 * 
 * 1.0.0
 *	- added saving of payment_info
 *	- 
 *	-
 *	-
 *	-
 * 
 **/


class HotelPaymentGateways extends MicroGrid {
	
	protected $debug = false;

    private $hotelOwner = false;

	// #001 private $arrTranslations = '';		
	
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
		if(isset($_POST['api_login'])) 		$this->params['api_login'] = prepare_input($_POST['api_login']);
		if(isset($_POST['api_key']))   		$this->params['api_key'] = prepare_input($_POST['api_key']);
		if(isset($_POST['payment_info']))   $this->params['payment_info'] = prepare_input($_POST['payment_info']);

		$this->params['is_active'] = isset($_POST['is_active']) ? prepare_input($_POST['is_active']) : 0;
		
		## for checkboxes 
		//$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

		## for images (not necessary)
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = prepare_input($_POST['icon']);
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}elseif(self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		## for files:
		// define nothing

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
        $hotels_list = array();
        if($this->hotelOwner){
            $hotels_list = implode(',', $objLogin->AssignedToHotels());
            $this->formActionURL = 'index.php?admin=mod_booking_hotel_payment_gateways';
        }elseif($objLogin->IsLoggedInAs('owner', 'mainadmin')){
            $hotel_owner_id = Application::Get('hotel_owner_id');
            $hotels_list = implode(',', AdminsAccounts::getHotelListForHotelOwner($hotel_owner_id));
            $this->formActionURL = 'index.php?admin=hotel_owners_payment_gateways&hotel_owner_id='.$hotel_owner_id;
        }
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_HOTEL_PAYMENT_GATEWAYS;
		$this->dataSet 		= array();
		$this->error 		= '';


        $allow_editing = $this->hotelOwner && !$objLogin->HasPrivileges('edit_hotel_payments') ? false : true;
		$this->actions      = array('add'=>false, 'edit'=>$allow_editing, 'details'=>false, 'delete'=>false);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
        $this->allowPrint   = false;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= '';//($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
        if($this->hotelOwner || $objLogin->IsLoggedInAs('owner', 'mainadmin')){
    		$this->WHERE_CLAUSE = 'WHERE hpg.hotel_id IN('.$hotels_list.') '; // 'WHERE language_id = \''.$this->languageId.'\'';
        }else{
            $this->WHERE_CLAUSE = ' WHERE 1';
        }
		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
		$this->ORDER_CLAUSE = 'ORDER BY hpg.hotel_id ASC'; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 100;

		$this->isSortingAllowed = true;

		// exporting settings
		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);

		// prepare hotels array		
		$where_clause = '';
        if($this->hotelOwner || $objLogin->IsLoggedInAs('owner', 'mainadmin')){
            $where_clause = !empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : '1 = 0';
        }
        $total_hotels = Hotels::GetAllHotels($where_clause);
		$arr_hotels = array();
		$arr_hotels_filter = array();
		foreach($total_hotels[0] as $key => $val){
			$arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
			$arr_hotels_filter[$val['id']] = $val['name'].(!empty($val['location_name']) ? ' ('.$val['location_name'].') ' : '');
		}

		$this->isFilteringAllowed = true;

        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		// define filtering fields
		$this->arrFilteringFields = array(
			$hotel_name => array('table'=>'hpg', 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels_filter, 'sign'=>'=', 'width'=>'250px', 'visible'=>true),
		);

		///$this->isAggregateAllowed = false;
		///// define aggregate fields for View Mode
		///$this->arrAggregateFields = array(
		///	'field1' => array('function'=>'SUM', 'align'=>'center', 'aggregate_by'=>'', 'decimal_place'=>2),
		///	'field2' => array('function'=>'AVG', 'align'=>'center', 'aggregate_by'=>'', 'decimal_place'=>2),
		///);

		///$date_format = get_date_format('view');
		///$date_format_settings = get_date_format('view', true); /* to get pure settings format */
		///$date_format_edit = get_date_format('edit');
		///$datetime_format = get_datetime_format();
		///$time_format = get_time_format(); /* by default 1st param - shows seconds */
		///$currency_format = get_currency_format();

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
		/// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		/// $sql_translation_description = $this->PrepareTranslateSql(
		///	TABLE_XXX_DESCRIPTION,
		///	'gallery_album_id',
		///	array('field1', 'field2')
		/// );
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags, nl2br, readonly_text
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//----------------------------------------------------------------------
        $this->VIEW_MODE_SQL = 'SELECT
                                    hpg.'.$this->primaryKey.',
                                    hpg.hotel_id,
                                    hpg.payment_type,
                                    hpg.payment_type_name,
                                    hpg.api_login,
                                    hpg.api_key,
                                    hpg.is_active,
                                    hd.name as hotel_name
                                FROM '.$this->tableName.' hpg
                                    INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON hpg.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
        ';
		// define view mode fields
		$this->arrViewModeFields = array(
			'payment_type_name' => array('title'=>_PAYMENT_TYPE, 'type'=>'label', 'align'=>'left', 'width'=>'150px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
            'hotel_name' => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'is_active'  => array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>array('0'=>'<span class="red">'._NO.'</span>', '1'=>'<span class="green">'._YES.'</span>')),

			// 'field1'  => array('title'=>'', 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			// 'field2'  => array('title'=>'', 'type'=>'image', 'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'image_width'=>'50px', 'image_height'=>'30px', 'target'=>'uploaded/', 'no_image'=>''),
			// 'field3'  => array('title'=>'', 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>array()),
			// 'field4'  => array('title'=>'', 'type'=>'link',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'href'=>'http://{field4}|mailto:{field4}', 'target'=>''),

		);
		
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

			// 'field1'  => array('title'=>'', 'type'=>'label'),
			// 'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'username_generator'=>false),
			// 'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false),
			// 'field4'  => array('title'=>'', 'type'=>'hidden',                     'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s')),
			// 'field5'  => array('title'=>'', 'type'=>'enum',     'width'=>'',      'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_languages, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			// 'field6'  => array('title'=>'', 'type'=>'checkbox', 					 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			// 'field7'  => array('title'=>'', 'type'=>'image',    'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'uploaded/', 'no_image'=>'', 'random_name'=>true, 'image_name_pefix'=>'', 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'300k', 'watermark'=>false, 'watermark_text'=>''),
			// 'field8'  => array('title'=>'', 'type'=>'file',     'width'=>'210px', 'required'=>true, 'target'=>'uploaded/', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'file_maxsize'=>'300k'),
			// 'field9'  => array('title'=>'', 'type'=>'date',     				     'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'date', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field10' => array('title'=>'', 'type'=>'datetime', 					 'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field11' => array('title'=>'', 'type'=>'time', 					     'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'H:i:s', 'show_seconds'=>true, 'minutes_step'=>'1'),
			// 'field12' => array('title'=>'', 'type'=>'password', 'width'=>'310px', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'password_generator'=>false),
			// 'language_id'  => array('title'=>_LANGUAGE, 'type'=>'enum', 'source'=>$arr_languages, 'required'=>true, 'unique'=>false),
			
			//  'separator_X'   =>array(
			//		'separator_info' => array('legend'=>'Legend Text', 'columns'=>'0'),
			//		'field1'  => array('title'=>'', 'type'=>'label'),
			// 		'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			// 		'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'default'=>'', 'validation_type'=>'', 'unique'=>false),
			//  )
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
								'.$this->tableName.'.hotel_id,
								'.$this->tableName.'.payment_type,
                                '.$this->tableName.'.payment_type_name,
                                '.$this->tableName.'.api_login,
                                '.$this->tableName.'.api_key,
                                '.$this->tableName.'.is_active,
                                '.$this->tableName.'.payment_info,
                                hd.name as hotel_name
							FROM '.$this->tableName.'
                                INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON '.$this->tableName.'.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			
			'separator_general'   =>array(
                'separator_info' => array('legend'=>_GENERAL_INFO, 'columns'=>'0'),
                'hotel_id'       => array('title'=>'', 'type'=>'hidden', 'visible'=>true),
                'hotel_name'     => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'payment_type_name'  => array('title'=>_PAYMENT_TYPE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
            ),
        );
		
        if($this->hotelOwner){
            $this->arrEditModeFields['separator_payment'] = array(
                'separator_info' => array('legend'=>_PAYMENT_DETAILS, 'columns'=>'0'),
                'api_login'      => array('title'=>_API_LOGIN, 'type'=>'textbox',  'width'=>'210px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'40', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
                'api_key'        => array('title'=>_API_KEY, 'type'=>'textbox',  'width'=>'210px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'40', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
                'payment_info'   => array('title'=>_PAYMENT_INFO, 'type'=>'textarea', 'width'=>'430px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'170px', 'editor_type'=>'simple', 'validation_type'=>'', 'unique'=>false),
            );
        }

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		// format: strip_tags, nl2br, readonly_text
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(

		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		/// $this->AddTranslateToModes(
		/// $this->arrTranslations,
		/// array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'', 'validation_maxlength'=>'', 'readonly'=>false),
		/// 	  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'', 'maxlength'=>'512', 'validation_maxlength'=>'512', 'readonly'=>false)
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
     * Return infor for given hotel
     * @param $hotel_id
     * @param $payment_type
     */
    public static function GetPaymentInfo($hotel_id = 0, $payment_type = '')
	{
		$sql = "SELECT * FROM ".TABLE_HOTEL_PAYMENT_GATEWAYS." WHERE hotel_id = ".(int)$hotel_id." AND payment_type = '".$payment_type."'";
        return database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
    }

	/**
	 *	Before-editing record
	 *	@param bool
	 */
	public function BeforeEditRecord()
	{
		// $this->curRecordId - currently editing record
		// $this->result - current record info		
		if($this->result[1] > 0){
			if($this->hotelOwner && in_array($this->result[0][0]['payment_type'], array('poa','online'))){
				unset($this->arrEditModeFields['separator_payment']);
			}
		}
		return true;
	}
}
