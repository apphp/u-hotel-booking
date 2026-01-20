<?php

/**
 * 	Class ConferenceTypes (for uHotelBooking ONLY)
 *  -------------- 
 *  Description : 
 *  Updated	    : 13.04.2017
 * 	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Usage       : uHotelBooking
 * 	
 * 	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllConferences		ValidateFields
 *  __destruct              
 *  BeforeInsertRecord
 *  AfterInsertRecord
 *  BeforeUpdateRecord
 *  AfterUpdateRecord
 *  AfterDeleteRecord
 *  
 * */
class ConferenceTypes extends MicroGrid {

    protected $debug = false;
	
	private $arrTranslations = '';		

    //==========================================================================
    // Class Constructor
    //==========================================================================
	function __construct()
	{
        parent::__construct();

        $this->params = array();
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		$this->params['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : '0';

        $this->primaryKey = 'id';
        $this->tableName = DB_PREFIX.'conference_types';
        $this->tableTranslationsName = DB_PREFIX.'conference_types_translations';
        $this->tableConferenceName = DB_PREFIX.'conferences';
        $this->dataSet = array();
        $this->error = '';
        $this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

        $this->actions = array('add' => true, 'edit' => true, 'details' => true, 'delete' => true);
        $this->actionIcons = true;
        $this->allowRefresh = true;
        $this->allowPrint = true;
        $this->allowTopButtons = false;

        $this->allowLanguages = false;
        $this->WHERE_CLAUSE = '';
        $this->ORDER_CLAUSE = 'ORDER BY id DESC';

        $this->isAlterColorsAllowed = true;

        $this->isPagingAllowed = true;
        $this->pageSize = 20;

        $this->isSortingAllowed = true;

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

        $this->isFilteringAllowed = true;
        $this->arrFilteringFields = array(
            _CONFERENCE_NAME => array('table' => $this->tableTranslationsName, 'field' => 'name', 'type' => 'text', 'sign' => '%like%', 'width' => '90px'),
        );

		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'short_description', 'full_description')
		);

		// #003. prepare translations array for add/edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
			$this->tableTranslationsName,
			'conference_type_id',
			array('name', 'short_description', 'full_description')
		);

        //---------------------------------------------------------------------- 
        // VIEW MODE
        //---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.*,
									'.$this->tableTranslationsName.'.name,
									'.$this->tableTranslationsName.'.short_description,
									CONCAT("<a href=\"javascript:void();\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_conferences_management\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;mg_language_id='.$this->languageId.'&amp;filter_by_'.$this->tableConferenceName.'conference_type_id=", '.$this->tableName.'.'.$this->primaryKey.', "\',\'post\')\"",">[ '._CONFERENCES.' ]</a>  (", (SELECT COUNT(*) as cnt FROM '.DB_PREFIX.'conferences c WHERE c.conference_type_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_conferences
								FROM ('.$this->tableName.'
									LEFT OUTER JOIN '.$this->tableTranslationsName.' ON '.$this->tableName.'.id = '.$this->tableTranslationsName.'.conference_type_id AND '.$this->tableTranslationsName.'.language_id = \''.$this->languageId.'\')
								';
        // define view mode fields
        $this->arrViewModeFields = array(
            'name' 			 	=> array('title'=>_CONFERENCE_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'270px', 'maxlength'=>''),
            'short_description' => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'85', 'format'=>'strip_tags'),
			'link_conferences' 	=> array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'140px', 'sortable'=>false, 'visible'=>true),
			'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'55px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
			'priority_order' 	=> array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'55px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),
            'id' 			 	=> array('title'=>'ID', 'type' => 'label', 'align' => 'center', 'width' => '80px'),
        );

        //---------------------------------------------------------------------- 
        // ADD MODE
        //---------------------------------------------------------------------- 
        // define add mode fields
        $this->arrAddModeFields = array(
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric', 'unique'=>false, 'visible'=>true),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
        );

        //---------------------------------------------------------------------- 
        // EDIT MODE
        // * password field must be written directly in SQL!!!
        //---------------------------------------------------------------------- 
        $this->EDIT_MODE_SQL = 'SELECT
		                            '.$sql_translation_description.'
									'.$this->tableName.'.*																		
								FROM '.$this->tableName.'
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';

        // define edit mode fields
        $this->arrEditModeFields = array(
			'priority_order'=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric', 'unique'=>false, 'visible'=>true),
			'is_active'     => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
        );

        //---------------------------------------------------------------------- 
        // DETAILS MODE
        // format: strip_tags, nl2br, readonly_text
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
        $this->arrDetailsModeFields = array(
			'priority_order'=> array('title'=>_ORDER, 'type'=>'label'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
        );

		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
				array('name'              => array('title'=>_CONFERENCE_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'255', 'readonly'=>false),
			          'short_description' => array('title'=>_SHORT_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'2048', 'validation_maxlength'=>'2048', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
			          'full_description'  => array('title'=>_FULL_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'120px', 'required'=>false, 'maxlength'=>'', 'validation_maxlength'=>'', 'readonly'=>false, 'editor_type'=>'wysiwyg')
			)
		);
    }

    //==========================================================================
    // Class Destructor
    //==========================================================================
	function __destruct()
	{
        // echo 'this object has been destroyed';
    }

    //==========================================================================
    // Static Methods
    //==========================================================================	
    /**
     * 	Get all countries array
     * 	@param $where - where clause
     */
	public static function GetAllConferences($where = '')
	{
        $tableName = DB_PREFIX.'conference_types';
        $tableTranslationsName = DB_PREFIX.'conference_types_translations';
        $languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

        // Build WHERE clause
        $where_clause = !empty($where) ? 'WHERE '.$where : '';
		$sql = 'SELECT '.$tableName.'.*,
					'.$tableTranslationsName.'.name,
					'.$tableTranslationsName.'.short_description,
					'.$tableTranslationsName.'.full_description
				FROM ('.$tableName.'
					LEFT OUTER JOIN '.$tableTranslationsName.' ON '.$tableName.'.id = '.$tableTranslationsName.'.conference_type_id AND '.$tableTranslationsName.'.language_id = \''.$languageId.'\')
				'.$where_clause;

        return database_query($sql, DATA_AND_ROWS);
    }

	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	/**
	 * Validate fields
	 */
	private function ValidateFields()	
	{
		if(strlen($this->params['priority_order']) > 3){
			$this->error = str_replace('_FIELD_', '<b>'._ORDER.'</b>', _FIELD_LENGTH_EXCEEDED);
			$this->error = str_replace('_LENGTH_', 3, $this->error);
			$this->errorField = 'priority_order';
			return false;				
		}
		foreach($this->arrTranslations as $key => $val){
			if(trim($val['name']) == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;				
	 		}elseif(strlen($val['name']) > 255){
	 			$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 255, $this->error);
	 			$this->errorField = 'name_'.$key;
	 			return false;
	 		}elseif(strlen($val['short_description']) > 2048){
	 			$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 2048, $this->error);
	 			$this->errorField = 'short_description_'.$key;
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
		global $objLogin;
		
		$sql = 'INSERT INTO '.$this->tableTranslationsName.'(id, conference_type_id, language_id, name, short_description, full_description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0){
				$sql .= ',';
			}
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['short_description'], false, 'medium')).'\', \''.encode_text(prepare_input($val['full_description'], false, 'medium')).'\')';
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
		return $this->ValidateFields();
	}

	/**
	 * After-Updating - update hotels item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.$this->tableTranslationsName.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						short_description = \''.encode_text(prepare_input($val['short_description'], false, 'medium')).'\',
						full_description = \''.encode_text(prepare_input($val['full_description'], false, 'medium')).'\'
					WHERE conference_type_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			if($this->debug) $start_time = $this->GetFormattedMicrotime();
			$result = database_void_query($sql);
			if($this->debug) $finish_time = $this->GetFormattedMicrotime();        
			if($this->debug) $this->arrSQLs['update_sql'] = '<i>Update Record</i> | T: '.round((float)$finish_time - (float)$start_time, 4).' sec. <br>'.$sql;
			if(!$result){
				if($this->debug) $this->arrErrors['update_sql'] = $sql.'<br>'.database_error();						
			}			
		}
	}

    /**
	 * After-Deleting Record
	 */
	public function AfterDeleteRecord()
	{
        // Delete info from hotel description table and other hotel related info
		$sql = 'DELETE FROM '.$this->tableTranslationsName.' WHERE conference_type_id = '.$this->curRecordId;
		database_void_query($sql);
	}
}
