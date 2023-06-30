<?php

/**
 *	Makes Class
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 25.03.2016
 *  Usage       : Core Class (ALL)
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct										ValidateTranslationFields
 *	__destruct
 *	GetAllActive
 *	BeforeInsertRecord
 *	AfterInsertRecord
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDeleteRecord
 *	
 *  1.0.0
 *      - 
 *      - 
 *      -
 *      -
 *      -
 *      
 *	NOTES:
 *	------
 *	1.	for fiels and images 'target'=>'' must be relative path to the site's directory
 *
 *	NON-DOCUMENTED:
 *	---------------
 *	'pre_html'
 *	'post_html'
 *	'validation_maxlength'=>''		- for add/edit modes - check max length of string
 *	'validation_minlength'=>''		- for add/edit modes - check min length of string
 *	'validation_maximum'=>''		- for add/edit modes - check maximum value of argument
 *	'validation_minimum'=>''		- for add/edit modes - check minimum value of argument
 *	'movable'=>true 				- for priority order in view mode
 *	'sort_type'=>'string|numeric' 	- for fields sorting in view mode
 *	'sort_by'=>'field_name' 		- for fields sorting in view mode
 *	'header_tooltip'=>''            - displays tooltip for header in View/Add/Edit/Details modes
 *	'autocomplete'=>'off'           - block autocomplete by browser
 *	                                - for all label|textbox|textarea fields in all modes,
 *	                                  including text field in filtering mode
 *	'javascript_event'              - for specified filtering fields - allows to use pre-defined javascript events, like onchange tc.
 *	'custom_handler'=>true          - for specified filtering fields - allows to create custom handler (filter field will be automatically used in SQL)
 *	'cryptography'=>PASSWORDS_ENCRYPTION, 
 *	'cryptography_type'=>'AES',
 *	'aes_password'=>PASSWORDS_ENCRYPT_KEY
 *	->SetAlerts(array('delete'=>'')) - set custom delete alert
 *	
 *	TEMPLATES:
 *	-------
 *  1. Standard multi-language: 1 table :: 1 page + field lang
 *  2. Advanced multi-language: 2 tables :: 2 pages + link on main grid [description] to another page
 *  3. Professional multi-language: 2 table :: 1 pages + all lang fields on add/edit/detail modes
 *  	  #001. array for language translations
 *		  #002. prepare translation fields array
 *		  #003. prepare translations array for add/edit/detail modes
 *		  #004. add translation fields to all modes
 *        #005. add validation - ValidateTranslationFields()	
 *        #006. add Before/AfterInsert(), Before/AfterUpdate methods and AfterDeleteRecord + prepare_input()!!!
 *	
 *	TRICKS & TIPS:
 *	--------------
 *	1. to make image resize without creating additional thumbnail - make 'thumbnail_field'=>'image field'
 *	2. function __mgDoModeAlert(){} allows to call customized alerts on update operation
 *	3. to don't show "-- select --" in DDl use 'default_option'=>false
 *	
 **/


class Makes extends MicroGrid {
	
	protected $debug = false;
	
	// #001 -----------------------
	private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		global $objLogin;
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))  $this->params['is_active']  = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		
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

		///$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CAR_AGENCY_MAKES; 
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_car_rental_makes';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
        $this->allowPrint   = false;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId = $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';
		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.priority_order ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

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
		
		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		/// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_CAR_AGENCY_MAKES_DESCRIPTION,
			'make_id',
			array('name')
		);
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags, nl2br, readonly_text
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT 
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.priority_order,
									'.$this->tableName.'.is_active,
									'.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.'.name as make_name
								FROM '.$this->tableName.'
									INNER JOIN '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' ON '.$this->tableName.'.id = '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.'.make_id AND '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.'.language_id = \''.$this->languageId.'\'
								';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'make_name'  	 => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'priority_order' => array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'85px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),			
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),

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
		// - Validation Min Value: 1, 10... Ex.: 'validation_minimum'=>'1'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'40px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
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
								'.$sql_translation_description.'
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'40px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		// format: strip_tags, nl2br, readonly_text
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'make_name' 	 => array('title'=>_COUNTRY, 'type'=>'label'),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array('name' => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'', 'validation_maxlength'=>'', 'readonly'=>false))
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
	 * Returns all array of all active makes
	 * @param $where_clause
	 */
	public static function GetAllActive($where_clause = '')
	{		
		$sql = 'SELECT
					'.TABLE_CAR_AGENCY_MAKES.'.*,
					'.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.'.name
				FROM '.TABLE_CAR_AGENCY_MAKES.'
					INNER JOIN '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' ON '.TABLE_CAR_AGENCY_MAKES.'.id = '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.'.make_id AND '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.TABLE_CAR_AGENCY_MAKES.'.is_active = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.TABLE_CAR_AGENCY_MAKES.'.priority_order ASC ';			
		
		return database_query($sql, DATA_AND_ROWS);
	}

	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	/**
	 *	Validate translation fields
	 */	
	private function ValidateTranslationFields()	
	{
	   // check for required fields		
	   foreach($this->arrTranslations as $key => $val){			
			if($val['name'] == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['name']) > 50){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 50, $this->error);
				$this->errorField = 'name_'.$key;
				return false;
			}			
	   }		
	   return true;		
	}

	/**
	 * Before insert operation
	 */
	public function BeforeInsertRecord()
	{
	    return $this->ValidateTranslationFields();
	}	  	

	/**
	 * After adding operation
	 */
	public function AfterInsertRecord()
	{
		// $this->lastInsertId - currently inserted record
		// $this->params - current record insert info
		
		// #006 
		$sql = 'INSERT INTO '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.'(id, make_id, language_id, name) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\')';
			$count++;
		}
		if(database_void_query($sql)){
			return true;
		}else{
			//echo database_error();			
			return false;
		}
	}

	/**
	 * Before update operation
	 */
	public function BeforeUpdateRecord()
	{
     	// $this->curRecordId - current record		
		// #006		
    	return $this->ValidateTranslationFields();
 	}

	/**
	 * After update operation
	 */
	public function AfterUpdateRecord()
	{
		// $this->curRecordId - currently updated record
		// $this->params - current record update info
		// #006 
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\'
					WHERE make_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
			//echo database_error();
		}
	}

	/**
	 * After delete operation
	 */
	public function AfterDeleteRecord()
	{
		// $this->curRecordId - currently deleted record
		// #006
		$sql = 'DELETE FROM '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' WHERE make_id = '.$this->curRecordId;
		if(database_void_query($sql)){
		 	return true;
		}else{
		 	return false;
		}
	}

	
}
