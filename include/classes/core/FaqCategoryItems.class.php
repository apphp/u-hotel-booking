<?php

/**
 *	FAQ Category Items Class
 *  --------------
 *	Description : encapsulates methods and properties for FAQ Categories
 *	Written by  : ApPHP
 *	Version     : 1.0.1
 *  Updated	    : 18.07.2015
 *  Usage       : Core Class
 *  Differences : no
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct										ValidateTranslationFields
 *	__destruct
 *	BeforeInsertRecord
 *	AfterInsertRecord
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDeleteRecord
 *	
 *  1.0.1
 *      - changed SQL IF with 'enum' type
 *      - added maxlength for textareas
 *      - added translations
 *      -
 *      -
 *	
 **/


class FaqCategoryItems extends MicroGrid {
	
	protected $debug = false;
	
	private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	// 		@param $fcid
	//==========================================================================
	function __construct($fcid = 0)
	{		
		parent::__construct();
		
		global $objLogin;

		$this->params = array();
		
		## for standard fields
		//if(isset($_POST['faq_question']))   $this->params['faq_question'] = prepare_input($_POST['faq_question']);
		//if(isset($_POST['faq_answer']))     $this->params['faq_answer'] = prepare_input($_POST['faq_answer']);
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['category_id']))    $this->params['category_id'] = prepare_input($_POST['category_id']);
		if(isset($_POST['is_active']))      $this->params['is_active'] = prepare_input($_POST['is_active']);
		
		///$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_FAQ_CATEGORY_ITEMS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_faq_questions_management&fcid='.(int)$fcid;
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = 'WHERE category_id = '.(int)$fcid;				
		$this->ORDER_CLAUSE = 'ORDER BY priority_order ASC, faq_answer ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);
		
		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
		);

		///$date_format = get_date_format('view');
		///$date_format_edit = get_date_format('edit');				
		///$currency_format = get_currency_format();
		
		$arr_activity_types = array('0'=>_NO, '1'=>_YES);
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('faq_question', 'faq_answer')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_FAQ_CATEGORY_ITEMS_DESCRIPTION,
			'faq_category_item_id',
			array('faq_question', 'faq_answer')
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
									'.$this->tableName.'.priority_order,
									'.$this->tableName.'.is_active,
									fcid.faq_question,
									fcid.faq_answer
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_FAQ_CATEGORY_ITEMS_DESCRIPTION.' fcid ON '.$this->tableName.'.id = fcid.faq_category_item_id AND fcid.language_id = \''.$this->languageId.'\'';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'faq_question'   => array('title'=>_QUESTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'80', 'format'=>'', 'format_parameter'=>''),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),
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
			'priority_order' => array('title'=>_ORDER,  'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric'),			
            'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_activity_types, 'unique'=>false, 'javascript_event'=>''),		
			'category_id'    => array('title'=>'',      'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$fcid),
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
								'.$this->primaryKey.',
								'.$sql_translation_description.'
								category_id,
								priority_order,
								is_active								
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric'),			
            'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_activity_types, 'unique'=>false, 'javascript_event'=>''),		
			//'category_id'    => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$fcid),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			//'faq_question'   => array('title'=>_QUESTION, 'type'=>'label'),
			//'faq_answer'     => array('title'=>_ANSWER, 'type'=>'label'),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
            'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array(
				'faq_question'  => array('title'=>_QUESTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'readonly'=>false),
				'faq_answer' 	=> array('title'=>_ANSWER, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'readonly'=>false, 'editor_type'=>'wysiwyg')
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
	 * Validate translation fields
	 */
	private function ValidateTranslationFields()	
	{
		foreach($this->arrTranslations as $key => $val){
			if(trim($val['faq_question']) == ''){
				$this->error = str_replace('_FIELD_', '<b>'._SERVICE.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;				
			}elseif(strlen($val['faq_question']) > 512){
				$this->error = str_replace('_FIELD_', '<b>'._SERVICE.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 512, $this->error);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(trim($val['faq_answer']) == ''){
				$this->error = str_replace('_FIELD_', '<b>'._SERVICE.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;				
			}elseif(strlen($val['faq_answer']) > 1024){
				$this->error = str_replace('_FIELD_', '<b>'._SERVICE.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 1024, $this->error);
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
		$sql = 'INSERT INTO '.TABLE_FAQ_CATEGORY_ITEMS_DESCRIPTION.'(id, faq_category_item_id, language_id, faq_question, faq_answer) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['faq_question'])).'\', \''.encode_text(prepare_input($val['faq_answer'])).'\')';
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
	 * After-Updating - update extras item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_FAQ_CATEGORY_ITEMS_DESCRIPTION.'
					SET
						faq_question = \''.encode_text(prepare_input($val['faq_question'])).'\',
						faq_answer = \''.encode_text(prepare_input($val['faq_answer'])).'\'
					WHERE faq_category_item_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	

	/**
	 * After-Deleting - delete extras descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.TABLE_FAQ_CATEGORY_ITEMS_DESCRIPTION.' WHERE faq_category_item_id = '.(int)$this->curRecordId;
		database_void_query($sql);		
	}
}
