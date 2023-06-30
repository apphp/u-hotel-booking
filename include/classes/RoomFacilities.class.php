<?php

/**
 *	Code Template for MicroGrid Class
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 16.09.2012
 *  Usage       : Core Class (ALL)
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             GetAllActive            ValidateTranslationFields
 *	__destruct
 *	BeforeInsertRecord
 *	AfterInsertRecord
 *	BeforeUpdateRecord
 *	AfterInsertRecord
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
 *	'header_tooltip'=>''            - displays tooltip for header in View/Add/Edit modes
 *	'autocomplete'=>'off'           - block autocomplete by browser
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
 *	1. to make image resize with out creating additional thumbnail - make 'thumbnail_field'=>'image field'
 *	2. function __mgDoModeAlert(){} allows to call customized alerts on update operation
 *	3. to don't show "-- select --" in DDl use 'default_option'=>false
 *	
 **/


class RoomFacilities extends MicroGrid {
	
	protected $debug = false;
	
	// ---------------------------------
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
        if(isset($_POST['code']))           $this->params['code'] = prepare_input($_POST['code']);
		if(isset($_POST['is_active']))  	$this->params['is_active']  = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		
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
		$this->tableName 	= TABLE_ROOM_FACILITIES; // 
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=facilities_management';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.priority_order ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 25;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);
		
		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
			// 'Caption_3'  => array('table'=>'', 'field'=>'', 'type'=>'calendar', 'date_format'=>'dd/mm/yyyy|mm/dd/yyyy|yyyy/mm/dd', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
		);

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

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		/// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_ROOM_FACILITIES_DESCRIPTION,
			'room_facility_id',
			array('name', 'description')
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
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.priority_order,
									'.$this->tableName.'.icon_image,
									rfd.name as facility_name
								FROM ('.$this->tableName.' 
									LEFT OUTER JOIN '.TABLE_ROOM_FACILITIES_DESCRIPTION.' rfd ON '.$this->tableName.'.id = rfd.room_facility_id AND rfd.language_id = \''.$this->languageId.'\')
								';		
								
		// define view mode fields
		$this->arrViewModeFields = array(
			'icon_image'	 => array('title'=>_ICON, 'type'=>'image', 'align'=>'center', 'width'=>'45px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'image_width'=>'28px', 'image_height'=>'28px', 'target'=>'images/facilities/', 'no_image'=>'no_image.png'),
			'facility_name'  => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'priority_order' => array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),			
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
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
			'priority_order' => array('title'=>_ORDER,  'type'=>'textbox',  'width'=>'45px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive'),
			'code'           => array('title'=>_CODE,   'type'=>'textbox',  'header_tooltip'=>_CSS_CLASS_NAME, 'width'=>'140px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'20', 'default'=>'', 'validation_type'=>'alpha'),
			'icon_image'     => array('title'=>_ICON,   'type'=>'image',    'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/facilities/', 'no_image'=>'no_image.png', 'random_name'=>true, 'image_name_pefix'=>'', 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'100k', 'watermark'=>false, 'watermark_text'=>''),
			'is_active'		 => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),			
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
                                '.$this->tableName.'.code,
								'.$this->tableName.'.icon_image,
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'priority_order' => array('title'=>_ORDER,  'type'=>'textbox',  'width'=>'45px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive'),
			'code'           => array('title'=>_CODE,   'type'=>'textbox',  'header_tooltip'=>_CSS_CLASS_NAME, 'width'=>'140px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'20', 'default'=>'', 'validation_type'=>'alpha'),
			'icon_image'     => array('title'=>_ICON,   'type'=>'image',    'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/facilities/', 'no_image'=>'no_image.png', 'random_name'=>true, 'image_name_pefix'=>'', 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'100k', 'watermark'=>false, 'watermark_text'=>''),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),			
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
            'code'           => array('title'=>_CODE, 'header_tooltip'=>_CSS_CLASS_NAME, 'type'=>'label'),
			'icon_image'	 => array('title'=>_ICON, 'type'=>'image', 'target'=>'images/facilities', 'no_image'=>'no_image.png', 'image_width'=>'28px', 'image_height'=>'28px', 'visible'=>true),
			'is_active'      => array('title'=>_DEFAULT, 'type'=>'enum', 'source'=>$arr_active_vm),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
		 	      'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'', 'maxlength'=>'512', 'validation_maxlength'=>'512', 'readonly'=>false)
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
	 *	Returns all array of all active facilities
	 */
	public static function GetAllActive()
	{		
		$sql = 'SELECT
					rf.*,
					rfd.name,
					rfd.description
				FROM '.TABLE_ROOM_FACILITIES.' rf
					INNER JOIN '.TABLE_ROOM_FACILITIES_DESCRIPTION.' rfd ON rf.id = rfd.room_facility_id AND rfd.language_id = \''.Application::Get('lang').'\'
				WHERE
					rf.is_active = 1
				ORDER BY rf.priority_order ASC ';			
		return database_query($sql, DATA_AND_ROWS);
	}

	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	/**
	 *  Validate translation fields
	 */
	private function ValidateTranslationFields()	
	{
		// check for required fields		
		foreach($this->arrTranslations as $key => $val){			
			if($val['name'] == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['name']) > 125){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 125, $this->error);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['description']) > 512){
				$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 512, $this->error);
				$this->errorField = 'description_'.$key;
				return false;
			}			
		}		
		return true;		
	}
	
	/**
	 * Before Insert
	 */
	public function BeforeInsertRecord()
	{
	    return $this->ValidateTranslationFields();
	}

	/**
	 * After Insert
	 */
	public function AfterInsertRecord()
	{
		// $this->lastInsertId - currently inserted record
		// $this->params - current record insert info

		$sql = 'INSERT INTO '.TABLE_ROOM_FACILITIES_DESCRIPTION.'(id, room_facility_id, language_id, name, description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['description'])).'\')';
			$count++;
		}
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Before Update
	 */
	public function BeforeUpdateRecord()
	{
		// $this->curRecordId - current record
	   	return $this->ValidateTranslationFields();
	}

	/**
	 * After Update
	 */
	public function AfterUpdateRecord()
	{
		// $this->curRecordId - currently updated record
	    // $this->params - current record update info
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_ROOM_FACILITIES_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
					    description = \''.encode_text(prepare_input($val['description'])).'\'
					WHERE room_facility_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}

	/**
	 * After Delete
	 */
	public function AfterDeleteRecord()
	{
		// $this->curRecordId - currently deleted record
		$sql = 'DELETE FROM '.TABLE_ROOM_FACILITIES_DESCRIPTION.' WHERE room_facility_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}

}
