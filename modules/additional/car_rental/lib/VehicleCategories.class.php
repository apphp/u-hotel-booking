<?php

/**
 *	VehicleCategories Class 
 *  --------------
 *  Description : encapsulates Transportation Agencies vehicle categories class properties
 *  Updated	    : 27.02.2016
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllActive            ValidateTranslationFields
 *  __destruct              
 *  BeforeInsertRecord      
 *  AfterInsertRecord       
 *  BeforeUpdateRecord      
 *  AfterUpdateRecord       
 *  BeforeDeleteRecord      
 *  AfterDeleteRecord
 *                          
 *                          
 *	                        
 **/


class VehicleCategories extends MicroGrid {
	
	protected $debug = false;
	
	private $arrTranslations = '';
	private $agencyOwner = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		global $objLogin;
		$this->agencyOwner = $objLogin->IsLoggedInAs('agencyowner');
		
		$this->params = array();
		
		## for standard fields
		
		if(isset($_POST['priority_order'])) 		$this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))   			$this->params['is_active']  = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		
		$icon_width  = '120px';
		$icon_height = '90px';

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
		$this->tableName 	= TABLE_CAR_AGENCY_VEHICLE_CATEGORIES; // 
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_car_rental_vehicle_categories';
		$this->actions      = array(
								'add'=>($this->agencyOwner ? true : true),
								'edit'=>($this->agencyOwner ? $objLogin->HasPrivileges('edit_car_agency_vehicles') : true),
								'details'=>true,
								'delete'=>($this->agencyOwner ? true : true));
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId = $objLogin->GetPreferredLang();
        $watermark = (ModulesSettings::Get('rooms', 'watermark') == 'yes') ? true : false;
        $watermark_text = ModulesSettings::Get('rooms', 'watermark_text');

		$this->WHERE_CLAUSE = '';
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.priority_order ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
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
		$currency_format = get_currency_format();
		$default_currency = Currencies::GetDefaultCurrency();
        $this->currencyFormat = get_currency_format();		
	
		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types = array('0'=>_NO, '1'=>_YES);

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			//_AGENCY  => array('table'=>$this->tableName, 'field'=>'', 'type'=>'dropdownlist', 'source'=>$arr_car_agencies, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		/// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		/// $sql_translation_description = $this->PrepareTranslateSql(
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION,
			'agency_vehicle_category_id',
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
									'.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.name
								FROM ('.$this->tableName.'
									INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' ON '.$this->tableName.'.id = '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.agency_vehicle_category_id AND '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.language_id = \''.$this->languageId.'\')
								';
								
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'    				=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
			'priority_order' 		=> array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),			
			'id'             		=> array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'30px'),
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
			'priority_order' 	=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
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
							WHERE
								'.$this->tableName.'.'.$this->primaryKey.' = _RID_';

		// define edit mode fields
		$this->arrEditModeFields = array(
			'priority_order' 	=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'priority_order' 	=> array('title'=>_ORDER, 'type'=>'label'),
			'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
				array('name'       	=> array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
					  'description'	=> array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'2048', 'validation_maxlength'=>'2048', 'readonly'=>false, 'editor_type'=>'wysiwyg')
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
	 * Returns all array of all active vehicle types
	 * @param $where_clause
	 */
	public static function GetAllActive($where_clause = '')
	{		
		$sql = 'SELECT
					'.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES.'.*,
					'.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.name,
					'.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.description
				FROM '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES.'
					INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' ON '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES.'.id = '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.agency_vehicle_category_id AND '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES.'.is_active = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES.'.priority_order ASC ';			
		
		return database_query($sql, DATA_AND_ROWS);
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
		$sql = 'INSERT INTO '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'(id, agency_vehicle_category_id, language_id, name, description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['description'], false, 'medium')).'\')';
			$count++;
		}
		if(database_void_query($sql)){			
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
	 * After-Updating - update agencies item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						description = \''.encode_text(prepare_input($val['description'], false, 'medium')).'\'
					WHERE id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	


    /**
	 * Before-Deleting Record
	 */
	public function BeforeDeleteRecord()
	{
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_CAR_AGENCY_VEHICLES.' WHERE vehicle_type_id = '.(int)$this->curRecordId;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		
		if((int)$result['cnt'] > 1){
			$this->error = _VEHICLE_CATEGORY_DELETE_ALERT;
			return false; 
		}
		
		return true;		
	}

    /**
	 * After-Deleting Record
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'SELECT id, is_active FROM '.TABLE_CAR_AGENCIES;
		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			if((int)$result[1] == 1){
				// Make last agency always default and active
				$sql = 'UPDATE '.TABLE_CAR_AGENCIES.' SET is_active = \'1\' WHERE id= '.(int)$result[0][0]['id'];
				database_void_query($sql);
			}
		}

        // Delete info from agency description table
		$sql = 'DELETE FROM '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' WHERE agency_vehicle_category_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}

}
