<?php

/**
 *	CarAgenciesVehicleTypes Class 
 *  --------------
 *  Description : encapsulates Transportation Agencies vehicle types class properties
 *  Updated	    : 27.02.2016
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllActive            ValidateTranslationFields
 *  __destruct              						CheckRecordAssigned
 *  BeforeInsertRecord      						CheckCategoryUnique
 *  AfterInsertRecord
 *  BeforeEditRecord
 *  BeforeUpdateRecord      
 *  AfterUpdateRecord
 *  BeforeDetailsRecord
 *  BeforeDeleteRecord      
 *  AfterDeleteRecord
 *                          
 *                          
 *	                        
 **/


class CarAgenciesVehicleTypes extends MicroGrid {
	
	protected $debug = false;
	protected $assigned_to_car_agencies = '';

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
		
		if(isset($_POST['agency_id']))  			$this->params['agency_id'] = prepare_input($_POST['agency_id']);
		if(isset($_POST['vehicle_category_id']))  	$this->params['vehicle_category_id'] = prepare_input($_POST['vehicle_category_id']);
		if(isset($_POST['passengers']))     		$this->params['passengers'] = prepare_input($_POST['passengers']);
		if(isset($_POST['luggages']))     			$this->params['luggages'] = prepare_input($_POST['luggages']);
		if(isset($_POST['doors']))     				$this->params['doors'] = prepare_input($_POST['doors']);
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
		$this->tableName 	= TABLE_CAR_AGENCY_VEHICLE_TYPES; // 
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_car_rental_vehicle_types';
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

		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		if($this->agencyOwner){
			$agencies = $objLogin->AssignedToCarAgencies();
			$agencies_list = implode(',', $agencies);
			if(!empty($agencies_list)) $this->assigned_to_car_agencies = ' AND '.$this->tableName.'.agency_id IN ('.$agencies_list.')';
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_car_agencies;

		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
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
		
		// prepare agencies array		
		$total_agencies = CarAgencies::GetAllActive(
			(!empty($agencies_list) ? TABLE_CAR_AGENCIES.'.id IN ('.$agencies_list.')' : '')
		);
		$arr_car_agencies = array();
		foreach($total_agencies[0] as $key => $val){
			$arr_car_agencies[$val['id']] = $val['name'];
		}
		
		// prepare agencies array		
		$total_vehicle_types = VehicleCategories::GetAllActive();
		$arr_vehicle_types = array();
		foreach($total_vehicle_types[0] as $key => $val){
			$arr_vehicle_types[$val['id']] = $val['name'];
		}
				
		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types = array('0'=>_NO, '1'=>_YES);

		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_AGENCY  => array('table'=>$this->tableName, 'field'=>'agency_id', 'type'=>'dropdownlist', 'source'=>$arr_car_agencies, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
		);

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.agency_id,
									'.$this->tableName.'.vehicle_category_id,
									'.$this->tableName.'.image_thumb,
									'.$this->tableName.'.passengers,
									'.$this->tableName.'.luggages,
									'.$this->tableName.'.doors,
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.priority_order
								FROM '.$this->tableName;
								
		// define view mode fields
		$this->arrViewModeFields = array(
			'image_thumb' 			=> array('title'=>_IMAGE, 'type'=>'image', 'align'=>'left', 'width'=>'80px', 'image_width'=>'60px', 'image_height'=>'30px', 'target'=>'images/vehicle_types/', 'no_image'=>'no_image.png'),
			'vehicle_category_id'   => array('title'=>_VEHICLE_CATEGORY, 'type'=>'enum',  'align'=>'left', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_vehicle_types),
			'agency_id'    			=> array('title'=>_CAR_AGENCY, 'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_car_agencies),
			'passengers'   			=> array('title'=>_PASSENGERS, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'luggages'   			=> array('title'=>_LUGGAGES, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'doors'   				=> array('title'=>_DOORS, 'type'=>'label', 'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
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
			'agency_id'				=> array('title'=>_CAR_AGENCY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_car_agencies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'vehicle_category_id'	=> array('title'=>_VEHICLE_CATEGORY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_vehicle_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'passengers' 			=> array('title'=>_PASSENGERS, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_maximum'=>'99', 'unique'=>false, 'visible'=>true),
			'luggages' 				=> array('title'=>_LUGGAGES, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_maximum'=>'99', 'unique'=>false, 'visible'=>true),
			'doors' 				=> array('title'=>_DOORS, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'1', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'image'      			=> array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/vehicle_types/', 'random_name'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_thumb', 'thumbnail_width'=>$icon_width, 'thumbnail_height'=>$icon_height, 'file_maxsize'=>'2000k'),
			'priority_order' 		=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
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
								'.$this->tableName.'.agency_id,
								'.$this->tableName.'.vehicle_category_id,
								'.$this->tableName.'.passengers,
								'.$this->tableName.'.luggages,
								'.$this->tableName.'.doors,
								'.$this->tableName.'.image,
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE
								'.$this->tableName.'.'.$this->primaryKey.' = _RID_';

		// define edit mode fields
		$this->arrEditModeFields = array(
			'agency_id'				=> array('title'=>_CAR_AGENCY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_car_agencies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'vehicle_category_id'	=> array('title'=>_VEHICLE_CATEGORY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_vehicle_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'passengers' 			=> array('title'=>_PASSENGERS, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_maximum'=>'99', 'unique'=>false, 'visible'=>true),
			'luggages' 				=> array('title'=>_LUGGAGES, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_maximum'=>'99', 'unique'=>false, 'visible'=>true),
			'doors' 				=> array('title'=>_DOORS, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'1', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'image'      			=> array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/vehicle_types/', 'random_name'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_thumb', 'thumbnail_width'=>$icon_width, 'thumbnail_height'=>$icon_height, 'file_maxsize'=>'2000k'),
			'priority_order' 		=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'agency_id'				=> array('title'=>_CAR_AGENCY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_car_agencies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'vehicle_category_id'	=> array('title'=>_VEHICLE_CATEGORY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_vehicle_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'passengers' 			=> array('title'=>_PASSENGERS, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'luggages' 				=> array('title'=>_LUGGAGES, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'doors' 				=> array('title'=>_DOORS, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'image'      			=> array('title'=>_IMAGE, 'type'=>'image', 'target'=>'images/vehicle_types/', 'no_image'=>'no_image.png'),
			'priority_order' 		=> array('title'=>_ORDER, 'type'=>'label'),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
		);

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
					'.TABLE_CAR_AGENCY_VEHICLE_TYPES.'.*,
					'.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.name,
					'.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.description
				FROM '.TABLE_CAR_AGENCY_VEHICLE_TYPES.'
					INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' ON '.TABLE_CAR_AGENCY_VEHICLE_TYPES.'.vehicle_category_id = '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.agency_vehicle_category_id AND '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.TABLE_CAR_AGENCY_VEHICLE_TYPES.'.is_active = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.TABLE_CAR_AGENCY_VEHICLE_TYPES.'.priority_order ASC ';			
		
		return database_query($sql, DATA_AND_ROWS);
	}
	
	/**
	 *	Before-Insertion record
	 */
	public function BeforeInsertRecord()
	{
		if(!$this->CheckCategoryUnique()) return false;
		return true;
	}	

	/**
	 *	Before-updating record
	 */
	public function BeforeUpdateRecord()
	{
		if(!$this->CheckCategoryUnique()) return false;
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
	 * Check if specific record is assigned to given owner
	 * @param int $curRecordId
	 */
	private function CheckRecordAssigned($curRecordId = 0)
	{
		global $objSession;
		
		$sql = 'SELECT * 
				FROM '.$this->tableName.' 
				WHERE '.$this->primaryKey.' = '.(int)$curRecordId . $this->assigned_to_car_agencies;
				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] <= 0){
			$objSession->SetMessage('notice', draw_important_message(_WRONG_PARAMETER_PASSED, false));
			return false;
		}
		
		return true;		
	}
	
	/**
	 * Check if category already exists for current agency
	 */
	private function CheckCategoryUnique()
	{
		$rid = MicroGrid::GetParameter('rid');
		$agency_id = MicroGrid::GetParameter('agency_id', false);
		$vehicle_category_id = MicroGrid::GetParameter('vehicle_category_id', false);

		$sql = 'SELECT * FROM '.TABLE_CAR_AGENCY_VEHICLE_TYPES.'
				WHERE
					id != '.(int)$rid.' AND
					agency_id = '.(int)$agency_id.' AND
					vehicle_category_id = '.(int)$vehicle_category_id;
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$this->error = str_replace('_FIELD_', '<b>'._VEHICLE_CATEGORY.'</b>', _FILED_UNIQUE_VALUE_ALERT);
			return false;
		}
		return true;
	}	

	

}
