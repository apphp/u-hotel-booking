<?php

/**
 *	Code Template for MicroGrid Class
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 30.08.2018
 *  Usage       : Core Class (ALL)
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct
 *	__destruct
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
 *	->SetRowTemplate($template) - set custom html template
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


class MyClass extends MicroGrid {
	
	protected $debug = false;
	
	// #001 private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['field1']))   $this->params['field1'] = prepare_input($_POST['field1']);
		if(isset($_POST['field2']))   $this->params['field2'] = prepare_input($_POST['field2']);
		if(isset($_POST['field3']))   $this->params['field3'] = prepare_input($_POST['field3']);
		if(isset($_POST['field4_link']))   $this->params['field4_link'] = prepare_input($_POST['field4_link'], false, 'middle');
		
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

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= DB_PREFIX.'table'; // TABLE_NAME
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=page_name';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
        $this->allowPrint   = false;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = true;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';
		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
		$this->ORDER_CLAUSE = ''; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		// $this->isResponsiveTable = true; // optional, determines whether the MicroGrid will draw a responsive table

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
		// format: strip_tags, nl2br, readonly_text, readonly_html
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									field1,
									field2,
									field3
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(

			// 'field1'  => array('title'=>'', 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'header_tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			// 'field2'  => array('title'=>'', 'type'=>'image', 'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'image_width'=>'50px', 'image_height'=>'30px', 'target'=>'uploaded/', 'no_image'=>''),
			// 'field3'  => array('title'=>'', 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>array(), 'default'=>'', 'data_view'=>'counter|normal', 'data_type'=>'serialized|normal', 'pre_html'=>'', 'post_html'=>'', 'format'=>'', 'format_parameter'=>'', , 'tooltip_divider'=>'\r\n'),
			// 'field4'  => array('title'=>'', 'type'=>'link',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'header_tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'href'=>'http://{field4}|mailto:{field4}', 'target'=>''),

		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date|url
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Min Value: 1, 10... Ex.: 'validation_minimum'=>'1'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    

			// 'field1'  => array('title'=>'', 'type'=>'label'),
			// 'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'placeholder'=>''),
			// 'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false),
			// 'field4'  => array('title'=>'', 'type'=>'hidden',                     'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s')),
			// 'field5'  => array('title'=>'', 'type'=>'enum',     'width'=>'',      'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_languages, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select_columns'=>4, 'multi_select'=>false),
			// 'field6'  => array('title'=>'', 'type'=>'checkbox', 					 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			// 'field7'  => array('title'=>'', 'type'=>'image',    'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'uploaded/', 'no_image'=>'', 'random_name'=>true, 'image_name_pefix'=>'', 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'300k', 'watermark'=>false, 'watermark_text'=>''),
			// 'field8'  => array('title'=>'', 'type'=>'file',     'width'=>'210px', 'required'=>true, 'target'=>'uploaded/', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'file_maxsize'=>'300k'),
			// 'field9'  => array('title'=>'', 'type'=>'date',     				     'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'date', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field10' => array('title'=>'', 'type'=>'datetime', 					 'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field11' => array('title'=>'', 'type'=>'time', 					     'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'H:i:s', 'show_seconds'=>true, 'minutes_step'=>'1'),
			// 'field12' => array('title'=>'', 'type'=>'password', 'width'=>'310px', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'password_generator'=>false),
			// 'field13' => array('title'=>'', 'type'=>'slider',   'width'=>'410px', 'readonly'=>false, 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1), 'visible'=>true, 'validation_type'=>'float|positive', 'validation_maximum'=>5, 'validation_minimum'=>0),
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
								'.$this->tableName.'.field1,
								'.$this->tableName.'.field2,
								'./* #003 $sql_translation_description */'
								'.$this->tableName.'.field3
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
		
			// 'field1'  => array('title'=>'', 'type'=>'label',    'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			// 'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			// 'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			// 'field4'  => array('title'=>'', 'type'=>'hidden',   					 'required'=>true, 'default'=>date('Y-m-d H:i:s'), 'visible'=>true),
			// 'field5'  => array('title'=>'', 'type'=>'enum',     'width'=>'',      'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_languages, 'default_option'=>'', 'unique'=>false, 'visible'=>true, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes|label', 'multi_select'=>false),
			// 'field6'  => array('title'=>'', 'type'=>'checkbox', 					 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
			// 'field7'  => array('title'=>'', 'type'=>'image',    'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'uploaded/', 'no_image'=>'', 'random_name'=>true, 'image_name_pefix'=>'', 'overwrite_image'=>false, 'unique'=>false, 'visible'=>true, 'image_width'=>'120px', 'image_height'=>'90px', 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'300k', 'watermark'=>false, 'watermark_text'=>''),
			// 'field8'  => array('title'=>'', 'type'=>'file',     'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'uploaded/', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'visible'=>true, 'file_maxsize'=>'300k'),
			// 'field9'  => array('title'=>'', 'type'=>'date',     					 'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'date', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field10' => array('title'=>'', 'type'=>'datetime',                   'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field11' => array('title'=>'', 'type'=>'time', 					     'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'H:i:s', 'show_seconds'=>true, 'minutes_step'=>'1'),
			// 'field12' => array('title'=>'', 'type'=>'password', 'width'=>'310px', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'visible'=>true),
		
			//  'separator_X'   =>array(
			//		'separator_info' => array('legend'=>'Legend Text', 'columns'=>'0'),
			//		'field1'  => array('title'=>'', 'type'=>'label'),
			// 		'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			// 		'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			//  )
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		// format: strip_tags, nl2br, readonly_text, readonly_html
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(

			// 'field1'  => array('title'=>'', 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			// 'field2'  => array('title'=>'', 'type'=>'image', 'target'=>'uploaded/', 'no_image'=>'', 'image_width'=>'120px', 'image_height'=>'90px', 'visible'=>true),
			// 'field3'  => array('title'=>'', 'type'=>'date', 'format'=>'date', 'format_parameter'=>'M d, Y', 'visible'=>true),
			// 'field3'  => array('title'=>'', 'type'=>'datetime', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'visible'=>true),
			// 'field4'  => array('title'=>'', 'type'=>'time', 'format'=>'date', 'format_parameter'=>'g:i A', 'visible'=>true),
			// 'field5'  => array('title'=>'', 'type'=>'enum', 'source'=>array(), 'visible'=>true, 'view_type'=>'label|checkboxes', 'multi_select'=>false),
			// 'field6'  => array('title'=>'', 'type'=>'html', 'visible'=>true),
			// 'field7'  => array('title'=>'', 'type'=>'object', 'width'=>'240px', 'height'=>'200px', 'visible'=>true),

			//  'separator_X'   =>array(
			//		'separator_info' => array('legend'=>'Legend Text', 'columns'=>'0', 'visible'=>true),
            //  )
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


	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	/***
	 *	Get all DataSet array
	 *
	 *  public function GetAll() { }
	 *	
	 **/
	
	/**
	 * #005 Validate translation fields
	 *
	 * private function ValidateTranslationFields()	
	 * {
	 *	* check for required fields		
	 *	foreach($this->arrTranslations as $key => $val){			
	 *		if($val['name'] == ''){
	 *			$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
	 *			$this->errorField = 'name_'.$key;
	 *			return false;
	 *		}elseif(strlen($val['name']) > 125){
	 *			$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
	 *			$this->error = str_replace('_LENGTH_', 125, $this->error);
	 *			$this->errorField = 'name_'.$key;
	 *			return false;
	 *		}elseif(strlen($val['description']) > 512){
	 *			$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
	 *			$this->error = str_replace('_LENGTH_', 512, $this->error);
	 *			$this->errorField = 'description_'.$key;
	 *			return false;
	 *		}			
	 *	}		
	 *	return true;		
	 * }
	 **/

	/***
	 *	Add record
	 *
	 *  public function BeforeAddRecord(){ 
	 *		// your code here...
     *      // $this->arrAddModeFields
	 * 	}
	 *
	 *  public function AfterAddRecord(){ 
	 *		// your code here...
	 * 	}
	 * 	 	
	 **/

	/***
	 *	Insert record
	 *
	 *	public function AddRecord($params = array()) { 	}
	 *
	 *  public function BeforeInsertRecord(){
	 *     // #006 return $this->ValidateTranslationFields();
	 *     return true;
	 * 	}
	 * 	
	 *	public function AfterInsertRecord(){
	 *      // $this->lastInsertId - currently inserted record
	 *      // $this->params - current record insert info
	 *
	 *      // #006 
	 *		// $sql = 'INSERT INTO '.TABLE_XXX_DESCRIPTION.'(id, gallery_album_id, language_id, name, description) VALUES ';
	 *		// $count = 0;
	 *		// foreach($this->arrTranslations as $key => $val){
	 *		// 	if($count > 0) $sql .= ',';
	 *		// 	$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['description'])).'\')';
	 *		// 	$count++;
	 *		// }
	 *		// if(database_void_query($sql)){
	 *		// 	return true;
	 *		// }else{
	 *		// 	//echo database_error();			
	 *		// 	return false;
	 *		// }
	 *	}
	 *	
	 **/

	/***
	 *	Edit record
	 *
	 *  public function BeforeEditRecord(){ 
	 *		// $this->curRecordId - currently editing record
	 *		// $this->result - current record info
	 *      return true;
	 * 	}
	 *
	 *  public function AfterEditRecord(){ 
	 *		// $this->curRecordId - currently editing record
	 *		// $this->result - current record info
	 * 	}
	 * 	 	
	 **/

	/***
	 *	Update record
	 *
	 *	public function UpdateRecord($params = array()) { 	}
	 *
	 *  public function BeforeUpdateRecord(){
	 *      // #006 return $this->ValidateTranslationFields();
	 *     	// $this->curRecordId - current record
	 *     	return true;
	 * 	}
	 * 	
	 *	public function AfterUpdateRecord(){
	 *		// $this->curRecordId - currently updated record
	 *      // $this->params - current record update info
	 *      // #006 
	 *		// foreach($this->arrTranslations as $key => $val){
	 *		//		 $sql = 'UPDATE '.TABLE_XXX_DESCRIPTION.'
	 *		//			SET name = \''.encode_text(prepare_input($val['name'])).'\',
	 *		//				description = \''.encode_text(prepare_input($val['description'])).'\'
	 *		//			WHERE gallery_album_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
	 *		//	database_void_query($sql);
	 *		//	//echo database_error();
	 *		// }
	 *	}
	 *	
	 **/
	
	/***
	 *	Delete record
	 *
	 *	public function DeleteRecord($rid = '') { 	}
	 *
	 *  public function BeforeDeleteRecord(){
	 *     	// $this->curRecordId - current record
	 *     	return true;
	 * 	}
	 * 	
	 *	public function AfterDeleteRecord(){
	 *		// $this->curRecordId - currently deleted record
	 *		// #006 $sql = 'DELETE FROM '.TABLE_XXX_DESCRIPTION.' WHERE gallery_album_id = '.$this->curRecordId;
	 *		// if(database_void_query($sql)){
	 *		// 	return true;
	 *		// }else{
	 *		// 	return false;
	 *		// }
	 *	}
	 *	
	 **/

	/***
	 *	View mode
	 *
	 *	public function BeforeViewRecords(){
	 *
	 *	}
	 *
	 *	public function AfterViewRecords(){
	 *
	 *	}
	 *	
	 **/

	/***
	 *	Details mode
	 *
	 *	public function BeforeDetailsRecord(){
	 *    	// $this->curRecordId - currently deleted record
	 *      // $this->result - current record info
	*      return true;
	 *	}
	 *	
	 *	public function AfterDetailsMode(){
	 *      // $this->curRecordId - currently deleted record
	 *      // $this->result - current record info
	 *	}
	 *	
	 **/	
	
	
	//==========================================================================
    // MicroGrid Triggers
	//==========================================================================	
	/**
	 * Trigger method - allows to work with View Mode items
	 * @param $field_name
	 * @param &$field_value
	 * protected function OnItemCreated_ViewMode($field_name, &$field_value)
	 * {
     *	   // your code here... &$field_value        	
	 * }
	 *
	 * Trigger method - allows to work with View Mode items
	 * @param $field_name
	 * @param &$field_value
	 * protected function OnItemCreated_DetailsMode($field_name, &$field_value)
	 * {
     *	   // your code here... &$field_value        	
	 * }
	 *  
	 * 
	 */
	
}
