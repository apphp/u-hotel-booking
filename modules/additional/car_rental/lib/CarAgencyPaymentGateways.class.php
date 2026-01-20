<?php

/**
 *	HotelPaymentGateways Class
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 14.03.2016
 *  Usage       : Core Class (ALL)
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             GetPaymentInfo
 *	__destruct
 *	
 *	
 *  1.0.0
 *      - added saving of payment_info
 *      - 
 *      -
 *      -
 *      -
 *	
 **/


class CarAgencyPaymentGateways extends MicroGrid {
	
	protected $debug = false;
	
	// #001 private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
        global $objLogin;
        
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['api_login'])) 		$this->params['api_login'] = prepare_input($_POST['api_login']);
		if(isset($_POST['api_key']))   		$this->params['api_key'] = prepare_input($_POST['api_key']);
		if(isset($_POST['payment_info']))   $this->params['payment_info'] = prepare_input($_POST['payment_info']);
		
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
        $agencies_list = implode(',', $objLogin->AssignedToCarAgencies());
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CAR_AGENCY_PAYMENT_GATEWAYS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_car_rental_payment_gateways';
		$this->actions      = array('add'=>false, 'edit'=>true, 'details'=>false, 'delete'=>false);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
        $this->allowPrint   = false;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= '';//($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = 'WHERE apg.agency_id IN('.$agencies_list.') '; // 'WHERE language_id = \''.$this->languageId.'\'';
		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
		$this->ORDER_CLAUSE = 'ORDER BY apg.agency_id ASC'; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 100;

		$this->isSortingAllowed = true;

		// exporting settings
		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);

		// define filtering fields		
		$this->isFilteringAllowed = false;
		///$this->maxFilteringColumns = 4; /* optional, defines split columns default = 0 */
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
			// 'Caption_3'  => array('table'=>'', 'field'=>'', 'type'=>'calendar', 'date_format'=>'dd/mm/yyyy|mm/dd/yyyy|yyyy/mm/dd', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
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
                                    apg.'.$this->primaryKey.',
                                    apg.agency_id,
									apg.payment_type,
									apg.payment_type_name,
									apg.api_login,
                                    apg.api_key,
                                    ad.name as agency_name
								FROM '.$this->tableName.' apg
                                    INNER JOIN '.TABLE_CAR_AGENCIES_DESCRIPTION.' ad ON apg.agency_id = ad.agency_id AND ad.language_id = \''.Application::Get('lang').'\'
                                ';	
		// define view mode fields
		$this->arrViewModeFields = array(
			'payment_type_name' => array('title'=>_PAYMENT_TYPE, 'type'=>'label', 'align'=>'left', 'width'=>'150px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
            'agency_name' => array('title'=>_CAR_AGENCY, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),

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
								'.$this->tableName.'.agency_id,
								'.$this->tableName.'.payment_type,
                                '.$this->tableName.'.payment_type_name,
                                '.$this->tableName.'.api_login,
                                '.$this->tableName.'.api_key,
                                '.$this->tableName.'.payment_info,
                                ad.name as agency_name
							FROM '.$this->tableName.'
                                INNER JOIN '.TABLE_CAR_AGENCIES_DESCRIPTION.' ad ON '.$this->tableName.'.agency_id = ad.agency_id AND ad.language_id = \''.Application::Get('lang').'\'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
		// define edit mode fields
		$this->arrEditModeFields = array(
			
			'separator_general'   =>array(
                'separator_info' => array('legend'=>_GENERAL_INFO, 'columns'=>'0'),
                'agency_name'  => array('title'=>_CAR_AGENCY, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'payment_type_name'  => array('title'=>_PAYMENT_TYPE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
            ),
		
			'separator_payment'   =>array(
                'separator_info' => array('legend'=>_PAYMENT_DETAILS, 'columns'=>'0'),
                'api_login'      => array('title'=>_API_LOGIN, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'40', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
                'api_key'        => array('title'=>_API_KEY, 'type'=>'textbox',  'width'=>'210px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'40', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
                'payment_info'   => array('title'=>_PAYMENT_INFO, 'type'=>'textarea', 'width'=>'430px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'170px', 'editor_type'=>'simple', 'validation_type'=>'', 'unique'=>false),
            ),
		);

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
    public static function GetPaymentInfo($agency_id = 0, $payment_type = '')
	{
		$sql = "SELECT * FROM ".TABLE_CAR_AGENCY_PAYMENT_GATEWAYS." WHERE agency_id = ".(int)$agency_id." AND payment_type = '".$payment_type."'";
        return database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
    }
	
}
