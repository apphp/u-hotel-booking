<?php

/**
 *	Class HotelPeriods
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 15.07.2013
 *  Usage       : Core Class (ALL)
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct                                     CheckStartFinishDate
 *	__destruct
 *	
 *	
 **/


class HotelPeriods extends MicroGrid {
	
	protected $debug = false;
	
	// #001 private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	// 	@param $hotel_id
	//==========================================================================
	function __construct($hotel_id = 0)
	{		
		parent::__construct();
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['hotel_id'])) $this->params['hotel_id'] = prepare_input($_POST['hotel_id']);
		if(isset($_POST['start_date'])) $this->params['start_date'] = prepare_input($_POST['start_date']);
		if(isset($_POST['finish_date'])) $this->params['finish_date'] = prepare_input($_POST['finish_date']);
		if(isset($_POST['period_description'])) $this->params['period_description'] = prepare_input($_POST['period_description']);
		
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

		$this->params['language_id'] = '';//MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_HOTEL_PERIODS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=hotel_default_periods&hid='.(int)$hotel_id;
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts
		
		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = 'WHERE hotel_id = \''.$hotel_id.'\'';		
		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
		$this->ORDER_CLAUSE = 'ORDER BY start_date ASC'; // ORDER BY date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		// exporting settings
		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);

		// define filtering fields		
		$this->isFilteringAllowed = false;
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
		
		//$date_format = get_date_format('view');
		$date_format_edit = get_date_format('edit');
		
		global $objSettings;
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$sqlFieldDateFormat = '%b %d, %Y';
		}else{
			$sqlFieldDateFormat = '%d %b, %Y';
		}
		
        // set locale time names
		$this->SetLocale(Application::Get('lc_time_name'));
		
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
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									hotel_id,
									DATE_FORMAT(start_date, \''.$sqlFieldDateFormat.'\') as mod_start_date,
									DATE_FORMAT(finish_date, \''.$sqlFieldDateFormat.'\') as mod_finish_date,
									period_description
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'mod_start_date'   => array('title'=>_START_DATE, 'type'=>'label', 'align'=>'left', 'width'=>'130px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'mod_finish_date'     => array('title'=>_FINISH_DATE, 'type'=>'label', 'align'=>'left', 'width'=>'130px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'period_description' => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'30', 'format'=>'', 'format_parameter'=>''),
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
			'start_date' 		 => array('title'=>_START_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10'),
			'finish_date'  		 => array('title'=>_FINISH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'1', 'max_year'=>'10'),
			'period_description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'255', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false),
			'hotel_id'           => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$hotel_id),
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
								'.$this->tableName.'.period_description,
								'.$this->tableName.'.start_date,
								'.$this->tableName.'.finish_date,
								DATE_FORMAT('.$this->tableName.'.start_date, \''.$sqlFieldDateFormat.'\') as mod_start_date,
								DATE_FORMAT('.$this->tableName.'.finish_date, \''.$sqlFieldDateFormat.'\') as mod_finish_date								
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'start_date'  	  => array('title'=>_START_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'50', 'max_year'=>'10'),
			'finish_date'  	  => array('title'=>_FINISH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'date', 'format_parameter'=>$date_format_edit, 'min_year'=>'50', 'max_year'=>'10'),
			'period_description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'255', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false),
			'hotel_id'           => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$hotel_id),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		// format: strip_tags, nl2br, readonly_text
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'mod_start_date'  => array('title'=>_START_DATE, 'type'=>'label'),
			'mod_finish_date' => array('title'=>_FINISH_DATE, 'type'=>'label'),
			'period_description' => array('title'=>_DESCRIPTION, 'type'=>'html', 'visible'=>true),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		/// $this->AddTranslateToModes(
		/// $this->arrTranslations,
		/// array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'', 'readonly'=>false),
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
	 * Check if start date is greater than finish date
	 */
	private function CheckStartFinishDate()
	{
		$start_date = MicroGrid::GetParameter('start_date', false);
		$finish_date = MicroGrid::GetParameter('finish_date', false);
		
		if($start_date > $finish_date){
			$this->error = _START_FINISH_DATE_ERROR;
			return false;
		}	
		return true;		
	}
	
	/**
	 * Check if there is a date overlapping
	 */
	private function CheckDateOverlapping()
	{
		$rid = MicroGrid::GetParameter('rid');
		$start_date = MicroGrid::GetParameter('start_date', false);
		$finish_date = MicroGrid::GetParameter('finish_date', false);
		$hotel_id = MicroGrid::GetParameter('hotel_id', false);

		$sql = 'SELECT * FROM '.TABLE_HOTEL_PERIODS.'
				WHERE
					id != '.(int)$rid.' AND
					hotel_id = '.(int)$hotel_id.' AND
					(((\''.$start_date.'\' >= start_date) AND (\''.$start_date.'\' <= finish_date)) OR
					((\''.$finish_date.'\' >= start_date) AND (\''.$finish_date.'\' <= finish_date))) ';	
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$this->error = _TIME_PERIOD_OVERLAPPING_ALERT;
			return false;
		}
		return true;
	}
	

	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	/**
	 * Before-Insertion record
	 */
	public function BeforeInsertRecord()
	{
		if(!$this->CheckStartFinishDate()) return false;
		if(!$this->CheckDateOverlapping()) return false;
		return true;
	}

	/**
	 * Before-updating record
	 */
	public function BeforeUpdateRecord()
	{
		if(!$this->CheckStartFinishDate()) return false;
		if(!$this->CheckDateOverlapping()) return false;
		return true;
	}

}
