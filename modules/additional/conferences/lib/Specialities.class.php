<?php

/**
 *	Class Specialities
 *  --------------
 *	Description : 
 *	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Updated	    : 13.04.2017
 *  Usage       : uHotelBooking
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             GetAllActive            ValidateFields
 *	__destruct              GetSpecialityName
 *	BeforeInsertRecord
 *	AfterInsertRecord 
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDeleteRecord
 *	
 **/


class Specialities extends MicroGrid {
	
	protected $debug = false;
	
	private $arrTranslations = '';		
	//private static $sTableName	= DB_PREFIX.'specialities';
	//private static $sTableDescriptionName = DB_PREFIX.'specialities_description';

	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
        
        global $objLogin;
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['code']))  $this->params['code'] = prepare_input($_POST['code']); else $this->params['code'] = '';
		if(isset($_POST['is_active']))  $this->params['is_active'] = prepare_input($_POST['is_active']); else $this->params['is_active'] = '0';
		
		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= DB_PREFIX.'specialities';
		$this->tableDescriptionName = DB_PREFIX.'specialities_description';
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_specialities_management';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY sd.name ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 50;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);
		
		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_CODE  => array('table'=>'s', 'field'=>'code', 'type'=>'text', 'sign'=>'like%', 'width'=>'110px', 'visible'=>true),
			_NAME  => array('table'=>'sd', 'field'=>'name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'110px', 'visible'=>true),
		);

		// prepare arrays
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		///////////////////////////////////////////////////////////////////////////////
		// prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
            array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// prepare translations array for add/edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
            $this->tableDescriptionName,
            'speciality_id',
            array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
                                    s.'.$this->primaryKey.',
                                    s.code,
                                    s.is_active,
									sd.name,
									sd.description
								FROM '.$this->tableName.' s
									LEFT OUTER JOIN '.$this->tableDescriptionName.' sd ON s.id = sd.speciality_id AND sd.language_id = \''.$this->languageId.'\'';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'code'        => array('title'=>_CODE, 'type'=>'label', 'align'=>'left', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'15', 'format'=>'', 'format_parameter'=>''),
			'name'        => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'290px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'45', 'format'=>'', 'format_parameter'=>''),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'80', 'format'=>'', 'format_parameter'=>''),
			'is_active'   => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'code'		=> array('title'=>_CODE,  'type'=>'textbox', 'width'=>'150px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'15', 'default'=>'', 'validation_type'=>'text'),
			'is_active' => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'true_value'=>'1', 'false_value'=>'0', 'default'=>'1'),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.code,
                                '.$sql_translation_description.'
                                '.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'code'		=> array('title'=>_CODE,  'type'=>'textbox', 'width'=>'150px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'15', 'default'=>'', 'validation_type'=>'text'),
			'is_active' => array('title'=>_ACTIVE,  'type'=>'checkbox', 'readonly'=>false, 'true_value'=>'1', 'false_value'=>'0'),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
            'code'		=> array('title'=>_CODE, 'type'=>'label'),
            'is_active' => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);

		///////////////////////////////////////////////////////////////////////////////
		// add translation fields to all modes
		$this->AddTranslateToModes(
            $this->arrTranslations,
            array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'70', 'validation_maxlength'=>'255', 'readonly'=>false),
                  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'', 'maxlength'=>'255', 'validation_maxlength'=>'255', 'readonly'=>false)
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
	 * Validate fields
	 */
	private function ValidateFields()	
	{
		if($this->params['code'] == ''){
			$this->error = str_replace('_FIELD_', '<b>'._CODE.'</b>', _FIELD_CANNOT_BE_EMPTY);
			$this->errorField = 'code';
			return false;
		}elseif(strlen($this->params['code']) > 15){
			$this->error = str_replace('_FIELD_', '<b>'._CODE.'</b>', _FIELD_LENGTH_EXCEEDED);
			$this->error = str_replace('_LENGTH_', 15, $this->error);
			$this->errorField = 'code';
			return false;
		}
		// check for required fields		
		foreach($this->arrTranslations as $key => $val){			
			if($val['name'] == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['name']) > 70){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 70, $this->error);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['description']) > 255){
				$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 255, $this->error);
				$this->errorField = 'description_'.$key;
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
		return $this->ValidateFields();
	}
    
	/**
	 * After-Insertion - add banner descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		$sql = 'INSERT INTO '.$this->tableDescriptionName.'(id, speciality_id, language_id, name, description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['description'])).'\')';
			$count++;
		}
		if(database_void_query($sql)){
			return true;
		}else{
			//echo mysql_error();			
			return false;
		}
	}	

	/**
	 * Before-Updating operations
	 */
	public function BeforeUpdateRecord()
	{
		return $this->ValidateFields();
	}

	/**
	 * After-Updating
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.$this->tableDescriptionName.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						description = \''.encode_text(prepare_input($val['description'])).'\'
					WHERE speciality_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
			//echo mysql_error();
		}
	}	

	/**
	 * After-Deleting 
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.$this->tableDescriptionName.' WHERE speciality_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}
    
	/**
	 * Returns all array of all active specialities
	 * @param $where_clause
	 */
	public static function GetAllActive($where_clause = '')
	{		
		$sql = 'SELECT
					s.id,
					s.code,
                    sd.name,
                    sd.description 
				FROM '.DB_PREFIX.'specialities s
                    LEFT OUTER JOIN '.DB_PREFIX.'specialities_description sd ON s.id = sd.speciality_id AND sd.language_id = \''.Application::Get('lang').'\'
				'.(($where_clause != '') ? 'WHERE '.$where_clause : '').'
				ORDER BY name ASC';			
		
		return database_query($sql, DATA_AND_ROWS);
	}
	
	/**
	 * Returns specialiy name by ID
	 * @param $speciality_id
	 */
	public static function GetSpecialityName($speciality_id = 0)
	{		
		$sql = 'SELECT
					s.id,
                    sd.name,
                    sd.description 
				FROM '.DB_PREFIX.'specialities s
                    LEFT OUTER JOIN '.DB_PREFIX.'specialities_description sd ON s.id = sd.speciality_id AND sd.language_id = \''.Application::Get('lang').'\'
				WHERE s.id = '.(int)$speciality_id;					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        return isset($result[0]['name']) ? $result[0]['name'] : '';            
	}	
}
?>
