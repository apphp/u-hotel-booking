<?php

/**
 *	Class MealPlans (for Hotel site ONLY)
 *  --------------
 *  Description : encapsulates MealPlans class properties
 *  Updated	    : 04.06.2012
 *	Written by  : ApPHP
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct             GetAllMealPlans         ValidateTranslationFields
 *	__destruct              MealPlansCount			CheckRecordAssigned
 *	BeforeEditRecord		DrawMealPlansDDL
 *	BeforeDetailsRecord
 *	BeforeInsertRecord      
 *	AfterInsertRecord       
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	BeforeDeleteRecord
 *	AfterDeleteRecord
 *	
 **/


class MealPlans extends MicroGrid {
	
	protected $debug = false;
	protected $assigned_to_hotels = '';
	
	private $arrTranslations = '';
	private $hotelsList;
    private $regionalManager = false;
    private $hotelOwner = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();

		global $objLogin;

        $this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager') ? true : false;
        $this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner') ? true : false;

		$this->params = array();
		if(isset($_POST['hotel_id']))       $this->params['hotel_id'] = prepare_input($_POST['hotel_id']);
		if(isset($_POST['price']))   		$this->params['price'] = prepare_input($_POST['price']);
		if(isset($_POST['charge_type']))    $this->params['charge_type'] = prepare_input($_POST['charge_type']);
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))  	$this->params['is_active']  = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		if(isset($_POST['is_default']))     $this->params['is_default'] = (int)$_POST['is_default'];
		
		## for checkboxes 
		//$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

		## for files:
		// define nothing

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_MEAL_PLANS; 
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_booking_meal_plans';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;

		$this->allowLanguages = false;
		$this->languageId  	= $objLogin->GetPreferredLang();
		
		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		$this->hotelsList = '';
		if($this->hotelOwner){
			$hotels = $objLogin->AssignedToHotels();
			$this->hotelsList = implode(',', $hotels);
			if(!empty($this->hotelsList)) $this->assigned_to_hotels = ' AND '.$this->tableName.'.hotel_id IN ('.$this->hotelsList.')';
		}elseif($this->regionalManager){
			$hotels = AccountLocations::GetHotels($objLogin->GetLoggedID());
			$this->hotelsList = implode(',', $hotels);
			$this->assigned_to_hotels = !empty($this->hotelsList) ? ' AND '.$this->tableName.'.hotel_id IN ('.$this->hotelsList.')' : ' AND 1 = 0 ';
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_hotels;

		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.hotel_id ASC, '.$this->tableName.'.priority_order ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

        // prepare hotels array
        $where_clause = '';
        if($this->regionalManager || $this->hotelOwner){
            $where_clause = !empty($this->hotelsList) ? TABLE_HOTELS.'.id IN ('.$this->hotelsList.')' : '1 = 0';
        }
        $total_hotels = Hotels::GetAllHotels($where_clause);
		$arr_hotels = array();
		foreach($total_hotels[0] as $key => $val){
			$arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
		}		

        $this->isFilteringAllowed = true;

        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		// define filtering fields
		$this->arrFilteringFields = array(
			$hotel_name  => array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
		);

		$default_currency = Currencies::GetDefaultCurrency();
		$currency_format = get_currency_format();
		$arr_charge_types = array(0=>_PERSON_PER_NIGHT);
		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types = array('0'=>_NO, '1'=>_YES);		

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_MEAL_PLANS_DESCRIPTION,
			'meal_plan_id',
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

	
		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A' + IF(date_created IS NULL, '', date_created) as date_created,
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.hotel_id,
									mpd.name,
									mpd.description,
									CONCAT("'.$default_currency.'", '.$this->tableName.'.price) as price,
									'.$this->tableName.'.charge_type,
									'.$this->tableName.'.is_default,
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.priority_order									
								FROM ('.$this->tableName.' 
									INNER JOIN '.TABLE_HOTELS.' ON '.$this->tableName.'.hotel_id = '.TABLE_HOTELS.'.id
									LEFT OUTER JOIN '.TABLE_MEAL_PLANS_DESCRIPTION.' mpd ON '.$this->tableName.'.id = mpd.meal_plan_id AND mpd.language_id = \''.$this->languageId.'\')';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'align'=>'left', 'width'=>'150px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
			'name'  	     => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'40', 'format'=>'', 'format_parameter'=>''),
			'description'    => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'50', 'format'=>'', 'format_parameter'=>''),
			'charge_type'    => array('title'=>_CHARGE_TYPE,  'type'=>'enum',  'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_charge_types),
			'price'          => array('title'=>_PRICE,  'type'=>'label', 'align'=>'right', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'currency', 'format_parameter'=>$currency_format.'|2'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
			'is_default'     => array('title'=>_DEFAULT, 'type'=>'enum',  'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_default_types_vm),
			'priority_order' => array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'85px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),			
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
			'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'width'=>'',   'required'=>true, 'readonly'=>false, 'default'=>((count($arr_hotels) == 1) ? key($arr_hotels) : ''), 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			'charge_type'    => array('title'=>_CHARGE_TYPE, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_charge_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),			
			'price'  	     => array('title'=>_PRICE,  'type'=>'textbox',  'required'=>true, 'width'=>'90px', 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'10000000', 'unique'=>false, 'visible'=>true, 'pre_html'=>$default_currency.' '),
			'priority_order' => array('title'=>_ORDER,   'type'=>'textbox',  'width'=>'45px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive'),
			'is_active'		 => array('title'=>_ACTIVE,  'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),			
			'is_default'     => array('title'=>_DEFAULT, 'type'=>'enum',     'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'0', 'source'=>$arr_default_types, 'unique'=>false, 'javascript_event'=>''),
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
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.hotel_id,
								'.$this->tableName.'.price,
								'.$this->tableName.'.charge_type,
								'.$sql_translation_description.'
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.is_active,
								'.$this->tableName.'.is_default
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'width'=>'',   'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			'charge_type'    => array('title'=>_CHARGE_TYPE, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_charge_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),			
			'price'  	     => array('title'=>_PRICE,  'type'=>'textbox',  'required'=>true, 'width'=>'90px', 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'10000000', 'unique'=>false, 'visible'=>true, 'pre_html'=>$default_currency.' '),
			'priority_order' => array('title'=>_ORDER,  'type'=>'textbox',  'width'=>'45px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive'),
			'is_default'     => array('title'=>_DEFAULT, 'type'=>'enum',  'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_default_types, 'unique'=>false, 'javascript_event'=>''),
			'is_active'		 => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'source'=>$arr_hotels),
			'charge_type'    => array('title'=>_CHARGE_TYPE, 'type'=>'enum', 'source'=>$arr_charge_types),			
			'price'  	     => array('title'=>_PRICE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$currency_format.'|2', 'pre_html'=>$default_currency),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
			'is_default'     => array('title'=>_DEFAULT, 'type'=>'enum', 'source'=>$arr_default_types_vm),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
				array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
					  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'validation_maxlength'=>'512', 'readonly'=>false)
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
			if(trim($val['name']) == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;				
			}elseif(strlen($val['name']) > 125){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 125, $this->error);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['description']) > 255){
				$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 512, $this->error);
				$this->errorField = 'description_'.$key;
				return false;
			}			
		}		
		return true;		
	}
	
	/**
	 *	Before-editing record
	 */
	public function BeforeEditRecord()
	{ 
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			redirect_to($this->formActionURL);
		}
		
		return true;
	}

	/**
	 *	Before-details record
	 */
	public function BeforeDetailsRecord()
	{
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			redirect_to($this->formActionURL);
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
		$hotel_id = MicroGrid::GetParameter('hotel_id', false);

		// update default plan
		$is_default = MicroGrid::GetParameter('is_default', false);
		if($is_default == '1'){
			$sql = 'UPDATE '.TABLE_MEAL_PLANS.' SET is_default = 0 WHERE hotel_id = '.(int)$hotel_id.' AND id != '.(int)$this->lastInsertId;
			database_void_query($sql);
		}		

		$sql = 'INSERT INTO '.TABLE_MEAL_PLANS_DESCRIPTION.'(id, meal_plan_id, language_id, name, description) VALUES ';
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
	 * Before-Updating operations
	 */
	public function BeforeUpdateRecord()
	{
		return $this->ValidateTranslationFields();
	}

	/**
	 * After-Updating - update meal plans item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		$hotel_id = MicroGrid::GetParameter('hotel_id', false);
		
		// set default plan
		$sql = 'SELECT id, is_active, is_default FROM '.TABLE_MEAL_PLANS;
		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			if((int)$result[1] == 1){
				// make the last plan always to be a default
				$sql = 'UPDATE '.TABLE_MEAL_PLANS.' SET is_default = 1 WHERE hotel_id = '.(int)$hotel_id.' AND id = '.(int)$result[0][0]['id'];
				database_void_query($sql);
			}else{
				// save all other plans to be not default
				$rid = MicroGrid::GetParameter('rid');
				$is_default = MicroGrid::GetParameter('is_default', false);
				if($is_default == '1'){
					// not sure we need it?! 
					//$sql = 'UPDATE '.TABLE_MEAL_PLANS.' SET is_active = \'1\'  WHERE id = '.(int)$rid;
					//database_void_query($sql);
					
					$sql = 'UPDATE '.TABLE_MEAL_PLANS.' SET is_default = 0 WHERE hotel_id = '.(int)$hotel_id.' AND id != '.(int)$rid;
					database_void_query($sql);
				}
			}
		}

		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_MEAL_PLANS_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						description = \''.encode_text(prepare_input($val['description'])).'\'
					WHERE meal_plan_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	

    /**
	 * Before-Deleting Record
	 */
	public function BeforeDeleteRecord()
	{
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			return false;
		}

		return true;	
	}

	/**
	 * After-Deleting - delete meal plans descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'SELECT id, is_active FROM '.TABLE_MEAL_PLANS;
		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			if((int)$result[1] == 1){
				// make last plan always default and active
				$sql = 'UPDATE '.TABLE_MEAL_PLANS.' SET is_default = \'1\', is_active = \'1\' WHERE id = '.(int)$result[0][0]['id'];
				database_void_query($sql);
			}
		}

		$sql = 'DELETE FROM '.TABLE_MEAL_PLANS_DESCRIPTION.' WHERE meal_plan_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Returns all meal plans
	 * @param $hotel_id
	 */
	public static function GetAllMealPlans($hotel_id = '')
	{
		$lang_id = Application::Get('lang');
		$sql = 'SELECT
					mp.id, mp.price, mp.charge_type, mp.priority_order, mp.is_active, mp.is_default,
					mpd.name, mpd.description
				FROM ('.TABLE_MEAL_PLANS.' mp
					LEFT OUTER JOIN '.TABLE_MEAL_PLANS_DESCRIPTION.' mpd ON mp.id = mpd.meal_plan_id AND mpd.language_id = \''.$lang_id.'\')
				WHERE mp.is_active = 1
					  '.(!empty($hotel_id) ? ' AND mp.hotel_id = '.(int)$hotel_id : '').'
				ORDER BY mp.priority_order ASC'; 
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result;
	}

	/**
	 * Returns meal plans count
	*/
	public static function MealPlansCount()
	{
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_MEAL_PLANS.' WHERE is_active = 1'; 
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return (int)$result[0]['cnt'];
		}
		return 0;
	}
	
	/**
	 * Returns meal plans count
	 * @param $meal_plans
	 * @param $currency_rate
	 * @param $currency_format
	 * @param $enabled
	 * @param $draw
	*/
	public static function DrawMealPlansDDL($meal_plans, $currency_rate, $currency_format, $enabled = true, $draw = false)
	{
		// $meal_plans_total = Currencies::PriceFormat(($meal_plans[0][$i]['price'] * $params['nights'] * $params['max_adults']) * $currency_rate, '', '', $currency_format);
		// $output .= '<option value="'.$meal_plans[0][$i]['id'].'">'.$meal_plans[0][$i]['name'].' '.$meal_plans_per_night.' x '.$params['nights'].'x '.$params['max_adults'].' = '.$meal_plans_total.'</option>';
		$output = '<select name="meal_plans" class="form-control available_meals_ddl" '.($enabled ? '' : 'disabled="disabled"').'>';
		for($i = 0; $i < $meal_plans[1]; $i++){
			$meal_plans_per_night = Currencies::PriceFormat($meal_plans[0][$i]['price'] * $currency_rate, '', '', $currency_format);
			$selected_option = (($meal_plans[0][$i]['is_default'] == 1) ? ' selected="selected"' : '');												
			$output .= '<option value="'.$meal_plans[0][$i]['id'].'"'.$selected_option.'>'.$meal_plans[0][$i]['name'].' '.$meal_plans_per_night.'</option>';
		}													
		$output .= '</select>';

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Get meal plan info
	 * @param $plan_id
	 * @param $param
	 */
	public static function GetPlanInfo($plan_id, $param = '')
	{
		$output = '';
		$lang_id = Application::Get('lang');
		$sql = 'SELECT
					mp.id, mp.price, mp.charge_type, mp.priority_order, mp.is_active, mp.is_default,
					mpd.name, mpd.description
				FROM ('.TABLE_MEAL_PLANS.' mp
					LEFT OUTER JOIN '.TABLE_MEAL_PLANS_DESCRIPTION.' mpd ON mp.id = mpd.meal_plan_id AND mpd.language_id = \''.$lang_id.'\')
				WHERE
					mp.is_active = 1 AND
					mp.id = '.(int)$plan_id.'
				ORDER BY mp.priority_order ASC'; 

		$plan_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($plan_info[1] > 0){
			if(!empty($param)) $output = isset($plan_info[0][$param]) ? $plan_info[0][$param] : '';
			else $output = $plan_info[0];
		}
		return $output;
	}

	/**
	 * Check if specific record is assigned to given owner
	 * @param int $curRecordId
	 */
	private function CheckRecordAssigned($curRecordId = 0)
	{
		global $objSession;
		
		$sql = 'SELECT * 
				FROM '.$this->tableName.' 
				WHERE '.$this->primaryKey.' = '.(int)$curRecordId . $this->assigned_to_hotels;
				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] <= 0){
			$objSession->SetMessage('notice', draw_important_message(_WRONG_PARAMETER_PASSED, false));
			return false;
		}
		
		return true;		
	}

}

