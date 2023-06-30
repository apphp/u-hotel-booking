<?php

/**
 *	FAQ Categories Class
 *  --------------
 *	Description : encapsulates methods and properties for FAQ Categories
 *	Written by  : ApPHP
 *	Version     : 1.0.2
 *  Updated	    : 18.07.2016
 *  Usage       : Core Class
 *  Differences : no
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct             DrawFaqList				ValidateTranslationFields
 *	__destruct
 *	BeforeInsertRecord
 *	AfterInsertRecord
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDeleteRecord
 *	
 *	
 *  1.0.2
 *      - added translation for FAQ categories
 *      -
 *      -
 *      -
 *      -
 *  1.0.1
 *      - added ModulesSettings::Get()
 *      - added AfterDeleteRecord()
 *      - fixed issue with wrong anchor for questions
 *      - changed SQL IF with 'enum' types
 *      - added faq_ prefix for anchors of links
 *	
 **/


class FaqCategories extends MicroGrid {
	
	protected $debug = false;
	
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
		
		## for checkboxes 
		$this->params['is_active'] = isset($_POST['is_active']) ? prepare_input($_POST['is_active']) : '0';

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
		$this->tableName 	= TABLE_FAQ_CATEGORIES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_faq_management';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.priority_order ASC';
		
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
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		///$date_format = get_date_format('view');
		///$date_format_edit = get_date_format('edit');				
		///$currency_format = get_currency_format();

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
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_FAQ_CATEGORIES_DESCRIPTION,
			'faq_category_id',
			array('name')
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
									fcd.name,
									CONCAT(\'<a href="index.php?admin=mod_faq_questions_management&fcid=\', '.$this->tableName.'.id, \'">'._QUESTIONS.'</a> (\', (SELECT COUNT(*) as cnt FROM '.TABLE_FAQ_CATEGORY_ITEMS.' fci WHERE fci.category_id = '.$this->tableName.'.id), \')\') as link_faq_category_items
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_FAQ_CATEGORIES_DESCRIPTION.' fcd ON '.$this->tableName.'.id = fcd.faq_category_id AND fcd.language_id = \''.$this->languageId.'\'';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'           => array('title'=>_CATEGORY, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'70', 'format'=>'', 'format_parameter'=>''),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),
			'link_faq_category_items' => array('title'=>_ITEMS, 'type'=>'label', 'align'=>'center', 'width'=>'130px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
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
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
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
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->primaryKey.',
								'.$sql_translation_description.'
								priority_order,
								is_active								
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array(
				'name' => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'255', 'readonly'=>false),
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
	 * Draws FAQ list
	 * @param $draw
	 */
	public static function DrawFaqList($draw = true)
	{
		$output = '';
		$lang_id = Application::Get('lang');
		$page_url = get_page_url();		

		if(Modules::IsModuleInstalled('faq')){
			if(ModulesSettings::Get('faq', 'is_active') == 'yes'){				
				$sql = 'SELECT
						fc.id as category_id,
						fcd.name as category_name,
						fci.id as item_id,
						fcid.faq_question,
						fcid.faq_answer,
						fci.priority_order
					FROM '.TABLE_FAQ_CATEGORY_ITEMS.' fci
						INNER JOIN '.TABLE_FAQ_CATEGORIES.' fc ON fci.category_id = fc.id
						INNER JOIN '.TABLE_FAQ_CATEGORIES_DESCRIPTION.' fcd ON fci.category_id = fcd.faq_category_id AND fcd.language_id = \''.$lang_id.'\'
						INNER JOIN '.TABLE_FAQ_CATEGORY_ITEMS_DESCRIPTION.' fcid ON fci.id = fcid.faq_category_item_id AND fcid.language_id = \''.$lang_id.'\'
					WHERE
						fc.is_active = 1 AND
						fci.is_active = 1
					ORDER BY
						fc.priority_order ASC,
						fci.priority_order ASC ';
				
				$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
				
				$count = 1;
				$current_category = '';
				$output .= '<a name="up"></a>';
				$output .= '<div class="faq_questions">';
				for($i=0; $i < $result[1]; $i++){
					if($current_category == ''){                    
						$current_category = $result[0][$i]['category_name'];
						$output .= (($i > 0) ? '<br>' : '').'<h3>'.$current_category.'</h3>';
					}elseif($current_category != $result[0][$i]['category_name']){
						$current_category = $result[0][$i]['category_name'];
						$output .= (($i > 0) ? '<br>' : '').'<h3>'.$current_category.'</h3>';
					}                
					$output .= '<span>&nbsp;&#8226;&nbsp;</span><a href="'.$page_url.'#faq_'.$result[0][$i]['category_id'].'_'.$result[0][$i]['item_id'].'">'.str_replace('\\', '', $result[0][$i]['faq_question']).'</a><br>';                        
				}
				$output .= '</div>';
		
				$current_category = '';
				$draw_hr = true;
				$count = 1;
				for($i=0; $i < $result[1]; $i++){
					if($current_category == ''){                    
						$current_category = $result[0][$i]['category_name'];
						$draw_hr = false;
						$output .= '<br />'.draw_sub_title_bar($current_category, false);
					}elseif($current_category != $result[0][$i]['category_name']){
						$current_category = $result[0][$i]['category_name'];
						$draw_hr = false;
						$output .= '<br />'.draw_sub_title_bar($current_category, false);
					}else{
						$draw_hr = true;
					}
					$output .= '<table width="100%" border="0" cellpadding="1" cellspacing="2">
					'.(($draw_hr) ? '<tr align="left" valign="top"><td colspan="2"><hr size="1" style="color:#cccccc" noshade></td></tr>' : '').'
					<tr>
						<td><a name="faq_'.$result[0][$i]['category_id'].'_'.$result[0][$i]['item_id'].'"></a><strong>'.str_replace('\\', '', $result[0][$i]['faq_question']).'</strong></td>
					</tr>
					<tr>
						<td>'.str_replace('\\', '', $result[0][$i]['faq_answer']).'</td>
					</tr>
					<tr><td colspan="2" align="'.Application::Get('defined_right').'"><a href="'.$page_url.'#up">top ^</a></td></tr>                
					</table>';
				}				
			}			
		}		
		
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Validate translation fields
	 */
	private function ValidateTranslationFields()	
	{
		foreach($this->arrTranslations as $key => $val){
			if(trim($val['name']) == ''){
				$this->error = str_replace('_FIELD_', '<b>'._SERVICE.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;				
			}elseif(strlen($val['name']) > 255){
				$this->error = str_replace('_FIELD_', '<b>'._SERVICE.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 255, $this->error);
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
		$sql = 'INSERT INTO '.TABLE_FAQ_CATEGORIES_DESCRIPTION.'(id, faq_category_id, language_id, name) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\')';
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
			$sql = 'UPDATE '.TABLE_FAQ_CATEGORIES_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\'
					WHERE faq_category_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	

	/**
	 * After-Deleting - delete extras descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.TABLE_FAQ_CATEGORIES_DESCRIPTION.' WHERE faq_category_id = '.$this->curRecordId;
		if(database_void_query($sql)){

			$sql = 'DELETE FROM '.TABLE_FAQ_CATEGORY_ITEMS.' WHERE category_id = '.(int)$this->curRecordId;
			database_void_query($sql);		

			return true;
		}else{
			return false;
		}
	}
	
}
