<?php

/**
 *	Class Countries (for Hotel Site ONLY)
 *  -------------- 
 *  Description : encapsulates countries properties
 *  Updated	    : 03.11.2010
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:            		PROTECTED
 *  -----------				-----------				-----------    				---------------- 
 *  __construct				GetAllCountries         ValidateTranslationFields	OnItemCreated_ViewMode
 *  __destruct              DrawAllCountries                            		OnItemCreated_DetailsMode
 *  UpdateVAT               GetCountryInfo			
 *	BeforeDeleteRecord
 *	BeforeInsertRecord
 *	AfterInsertRecord
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDeleteRecord
 *	
 **/

class Countries extends MicroGrid {
	
	protected $debug = false;

	//-------------------------
	private $id;
	protected $countries;
	private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//		@param $id
	//==========================================================================
	function __construct($id = '') {
		
		parent::__construct();

		$this->params = array();
		
		## for standard fields
		if(isset($_POST['name']))           $this->params['name'] = prepare_input($_POST['name']);
		if(isset($_POST['abbrv']))          $this->params['abbrv'] = prepare_input($_POST['abbrv']);
		if(isset($_POST['vat_value']))      $this->params['vat_value'] = prepare_input($_POST['vat_value']);
		if(isset($_POST['is_active']))      $this->params['is_active'] = (int)$_POST['is_active'];
		if(isset($_POST['is_default']))     $this->params['is_default'] = (int)$_POST['is_default'];
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = (int)$_POST['priority_order'];
	
		$this->id = $id;
		if($this->id != ''){
			$sql = 'SELECT id, abbrv, name, is_active, is_default, vat_value, priority_order
					FROM '.TABLE_COUNTRIES.'
					WHERE id = '.(int)$this->id;
			$this->countries = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		}else{
			$this->countries['id'] = '';
			$this->countries['abbrv  '] = '';
			$this->countries['name'] = '';
			$this->countries['vat_value'] = '';
			$this->countries['is_active'] = '';
			$this->countries['is_default'] = '';
			$this->countries['priority_order'] = '';
		}

		## for checkboxes 
		//if(isset($_POST['parameter4']))   $this->params['parameter4'] = $_POST['parameter4']; else $this->params['parameter4'] = '0';

		## for images
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = $_POST['icon'];
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}elseif (self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		// $this->params['language_id'] 	  = MicroGrid::GetParameter('language_id');
		$lang               = Application::Get('lang');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_COUNTRIES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=countries_management';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		//$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... 
		$this->ORDER_CLAUSE = 'ORDER BY c.priority_order DESC, cd.name ASC'; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;

		$arr_activity_types_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_activity_types = array('0'=>_NO, '1'=>_YES);				
		$arr_default_types = array('0'=>_NO, '1'=>_YES);		
		
		// define filtering fields
		$this->arrFilteringFields = array(
			_NAME   => array('table'=>'cd', 'field'=>'name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
			_ACTIVE => array('table'=>'c', 'field'=>'is_active', 'type'=>'dropdownlist', 'source'=>$arr_activity_types, 'sign'=>'=', 'width'=>'90px', 'visible'=>true),
		);

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		// #002. prepare translation fields array
			$this->arrTranslations = $this->PrepareTranslateFields(
			array('name')
		);

		// #003. prepare translations array for add/edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_COUNTRIES_DESCRIPTION,
			'country_id',
			array('name')
		);

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT c.'.$this->primaryKey.',
									c.abbrv,
									cd.name,
									c.vat_value,
									c.is_default,
									c.is_active,
									c.priority_order,
									CONCAT("<a href=index.php?admin=states_management&cid=", c.id, ">'._STATES.'</a> (", (SELECT COUNT(*) as cnt FROM '.TABLE_STATES.' s WHERE s.country_id = c.id) , ")") as link_states
								FROM '.$this->tableName.' c
									INNER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'';
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'  		 => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
			'abbrv'  		 => array('title'=>_ABBREVIATION, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'height'=>'', 'maxlength'=>''),
			'is_default'     => array('title'=>_DEFAULT, 'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_default_types_vm),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_activity_types_vm),
			'vat_value'      => array('title'=>_VAT, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'height'=>'', 'maxlength'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'height'=>'', 'maxlength'=>''),
			'link_states'    => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'110px'),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			///'name'  		 => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>'text'),
			'abbrv'  		 => array('title'=>_ABBREVIATION, 'type'=>'textbox', 'width'=>'35px', 'required'=>true, 'readonly'=>false, 'unique'=>true, 'maxlength'=>'2', 'default'=>'', 'validation_type'=>'alpha'),
			'vat_value' 	 => array('title'=>_VAT, 'type'=>'textbox', 'width'=>'60px', 'required'=>false, 'readonly'=>false, 'unique'=>false, 'maxlength'=>'6', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'99', 'post_html'=>' %'),
			'is_default'     => array('title'=>_DEFAULT, 'type'=>'enum', 'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'0', 'source'=>$arr_default_types, 'unique'=>false, 'javascript_event'=>''),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_activity_types, 'unique'=>false, 'javascript_event'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric'),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$sql_translation_description.'
								'.$this->tableName.'.abbrv,
								'.$this->tableName.'.is_active,
								'.$this->tableName.'.is_default,
								'.$this->tableName.'.vat_value,
								'.$this->tableName.'.priority_order
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			///'name'  		 => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>'text'),
			'abbrv'  		 => array('title'=>_ABBREVIATION, 'type'=>'textbox',  'width'=>'35px', 'required'=>true, 'readonly'=>false, 'unique'=>true, 'maxlength'=>'2', 'default'=>'', 'validation_type'=>'alpha'),
			'vat_value'      => array('title'=>_VAT, 'type'=>'textbox',  'width'=>'60px', 'required'=>false, 'readonly'=>false, 'unique'=>false, 'maxlength'=>'6', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'99', 'post_html'=>' %'),
			'is_default'     => array('title'=>_DEFAULT,      'type'=>'enum',     'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_default_types, 'unique'=>false, 'javascript_event'=>''),
			'is_active'      => array('title'=>_ACTIVE,       'type'=>'enum',     'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_activity_types, 'unique'=>false, 'javascript_event'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric'),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			///'name'  	=> array('title'=>_NAME, 'type'=>'label'),
			'abbrv'  	=> array('title'=>_ABBREVIATION, 'type'=>'label'),
			'vat_value' => array('title'=>_VAT, 'type'=>'label'),
			'is_default' => array('title'=>_DEFAULT, 'type'=>'enum', 'source'=>$arr_default_types_vm),
			'is_active'  => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_activity_types_vm),
			'priority_order'  => array('title'=>_ORDER, 'type'=>'label'),
		);

		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array('name' => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'100', 'readonly'=>false))
		);
	}

	//==========================================================================
    // Static Methods
	//==========================================================================	
	/**
	 * Get all countries array
	 * @param $order - order clause
	 */
	public static function GetAllCountries($order = ' c.priority_order DESC, cd.name ASC')
	{
		$lang = Application::Get('lang');
		
		// Build ORDER BY clause
		$order_clause = (!empty($order)) ? 'ORDER BY '.$order : '';
	
		$sql = "SELECT c.id, c.abbrv, cd.name, c.is_active, c.is_default, c.priority_order
				FROM ".TABLE_COUNTRIES." c
					INNER JOIN ".TABLE_COUNTRIES_DESCRIPTION." cd ON c.id = cd.country_id AND cd.language_id = '".$lang."'
				WHERE c.is_active = 1 ".$order_clause;
		
		return database_query($sql, DATA_AND_ROWS);
	}
	
	/**
	 * Returns country info
	 * @param $country_id
	 */
	public static function GetCountryInfo($country_id = '')
	{
		$lang = Application::Get('lang');
		
		$output = array();
		$sql = "SELECT c.*, cd.name
				FROM ".TABLE_COUNTRIES." c
					INNER JOIN ".TABLE_COUNTRIES_DESCRIPTION." cd ON c.id = cd.country_id AND cd.language_id = '".$lang."'
				WHERE c.id = ".(int)$country_id;		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output = $result[0];
		}
		return $output;
	}

	/**
	 * Draw all languages array
	 * @param $tag_name
	 * @param $selected_value
	 * @param $select_default
	 * @param $on_js_event
	 * @param $draw
	 */
	public static function DrawAllCountries($tag_name = 'b_country', $selected_value = '', $select_default = true, $on_js_event = '', $draw = true)
	{	
		$output  = '<select class="form-control" name="'.$tag_name.'" id="'.$tag_name.'"'.($on_js_event != '' ? ' onchange="'.$on_js_event.'"' : '').'>';
		$output .= '<option value="">-- '._SELECT.' --</option>';		
		$countries = self::GetAllCountries();
		for($i=0; $i < $countries[1]; $i++){
			if($select_default && $countries[0][$i]['is_default'] && empty($selected_value)){
				$selected_state = 'selected="selected"';
			}elseif($selected_value == $countries[0][$i]['abbrv']){
				$selected_state = 'selected="selected"';
			}else{
				$selected_state = '';
			}			
			$output .= '<option '.$selected_state.' value="'.$countries[0][$i]['abbrv'].'">'.$countries[0][$i]['name'].'</option>';
		}
		$output .= '</select>';
		
		if($draw) echo $output;
		else return $output;		
	}

	/**
	 * Updates VAT value for all countries 
	 * @param $value
	 */
	public function UpdateVAT($value = '0')
	{
		$sql = 'UPDATE '.TABLE_COUNTRIES.' SET vat_value = '.number_format($value, 3, '.', '');
		if(database_void_query($sql)){
			return true;
		}else{
			$this->error = _TRY_LATER;
			return false;
		}				
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
			}elseif(strlen($val['name']) > 100){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 125, $this->error);
				$this->errorField = 'name_'.$key;
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
	 * After-Insertion Record
	 */
	public function AfterInsertRecord()
	{
		$sql = 'INSERT INTO '.TABLE_COUNTRIES_DESCRIPTION.'(id, country_id, language_id, name) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\')';
			$count++;
		}
		
		if(database_void_query($sql)){			
			$is_default = MicroGrid::GetParameter('is_default', false);
			if($is_default == '1'){
				$sql = 'UPDATE '.TABLE_COUNTRIES.' SET is_default = \'0\' WHERE id != '.(int)$this->lastInsertId;
				database_void_query($sql);
				return true;
			}		
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
	 * After-Updating Record
	 */
	public function AfterUpdateRecord()
	{
		// Update translations
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_COUNTRIES_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\'
					WHERE country_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
		
		$sql = 'SELECT id, is_active, is_default FROM '.TABLE_COUNTRIES;
		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			if((int)$result[1] == 1){
				// make last country always be default
				$sql = 'UPDATE '.TABLE_COUNTRIES.' SET is_default = \'1\', is_active = \'1\' WHERE id = '.(int)$result[0][0]['id'];
				database_void_query($sql);
				return true;	
			}else{
				// save all other countries to be not default
				$rid = MicroGrid::GetParameter('rid');
				$is_default = MicroGrid::GetParameter('is_default', false);
				if($is_default == '1'){
					$sql = 'UPDATE '.TABLE_COUNTRIES.' SET is_active = \'1\'  WHERE id = '.(int)$rid;
					database_void_query($sql);
					
					$sql = 'UPDATE '.TABLE_COUNTRIES.' SET is_default = \'0\' WHERE id != '.(int)$rid;
					database_void_query($sql);
					return true;
				}
			}
		}
		
	    return true;	
	}
	
	/**
	 * Before record deleting
	 */	
	public function BeforeDeleteRecord()
	{
		$record_info = $this->GetInfoByID($this->curRecordId);
		if(isset($record_info['is_active']) && $record_info['is_active'] == 1){
			$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_COUNTRIES.' WHERE is_active = 1';
			$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			if(isset($result['cnt']) && $result['cnt'] <= 1){
				$this->error = _REMOVE_LAST_COUNTRY_ALERT;				
				return false;
			}
		}	
		return true;
	}

    /**
	 * After-Deleting Record
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'SELECT id, is_active FROM '.TABLE_COUNTRIES;
		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			if((int)$result[1] == 1){
				// make last country always default and active
				$sql = 'UPDATE '.TABLE_COUNTRIES.' SET is_default = \'1\', is_active = \'1\' WHERE id = '.(int)$result[0][0]['id'];
				database_void_query($sql);
			}
		}
		
		// Delete from translation table
		$sql = 'DELETE FROM '.TABLE_COUNTRIES_DESCRIPTION.' WHERE country_id = '.$this->curRecordId;
		database_void_query($sql);

		if(defined('TABLE_STATES')){
			$sql = 'DELETE FROM '.TABLE_STATES.' WHERE country_id = '.(int)$this->curRecordId;
			database_void_query($sql);
		}

	    return true;	
	}
	
	/**
	 * Trigger method - allows to work with View Mode items
	 * @param $field_name
	 * @param &$field_value
	*/
	protected function OnItemCreated_ViewMode($field_name, &$field_value)
	{
		if($field_name == 'vat_value'){
			if(substr($field_value, -1) == '0') $field_value = number_format($field_value, 2);
			if($field_value == '0'){
				$field_value = '<span class=gray>'.$field_value.'%</span>';
			}else{
				$field_value = $field_value.'%';
			}
		}
	}	

	/**
	 * Trigger method - allows to work with View Mode items
	 * @param $field_name
	 * @param &$field_value
	*/
	protected function OnItemCreated_DetailsMode($field_name, &$field_value)
	{
		if($field_name == 'vat_value'){
			if(substr($field_value, -1) == '0') $field_value = number_format($field_value, 2);
			if($field_value == '0'){
				$field_value = '<span class=gray>'.$field_value.'%</span>';
			}else{
				$field_value = $field_value.'%';
			}
		}
	}	

}
