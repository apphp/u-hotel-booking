<?php

/**
 *	Vehicles Class 
 *  --------------
 *  Description : encapsulates Transportation Agencies vehicle types class properties
 *  Updated	    : 27.02.2016
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetVehicleInfo          ValidateTranslationFields  			
 *  __destruct              GetAllActive			CheckRecordAssigned
 *  BeforeInsertRecord		GetCarPrice
 *  BeforeInsertRecord		GetMaxPrice
 *  AfterInsertRecord
 *  BeforeEditRecord
 *  AfterEditRecord
 *  AfterAddRecord			
 *  BeforeUpdateRecord
 *  AfterUpdateRecord
 *  BeforeDeleteRecord
 *  AfterDeleteRecord
 *                          
 *	                        
 **/


class Vehicles extends MicroGrid {
	
	protected $debug = false;
	protected $assigned_to_car_agencies = '';
	
	private $arrTranslations = '';		
	
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
		if(isset($_POST['agency_id']))   			$this->params['agency_id'] = prepare_input($_POST['agency_id']);
		if(isset($_POST['vehicle_type_id']))   		$this->params['vehicle_type_id'] = (int)$_POST['vehicle_type_id'];
		if(isset($_POST['make_id']))   				$this->params['make_id'] = prepare_input($_POST['make_id']);
		if(isset($_POST['model']))   				$this->params['model'] = prepare_input($_POST['model']);
		if(isset($_POST['fuel_type_id']))   		$this->params['fuel_type_id'] = (int)$_POST['fuel_type_id'];
		if(isset($_POST['registration_number'])) 	$this->params['registration_number'] = prepare_input($_POST['registration_number']);
		if(isset($_POST['mileage']))   				$this->params['mileage'] = prepare_input($_POST['mileage']);
		if(isset($_POST['transmission']))     		$this->params['transmission'] = prepare_input($_POST['transmission']);		

		if(isset($_POST['price_per_day']))     		$this->params['price_per_day'] = (float)$_POST['price_per_day'];
		if(isset($_POST['price_per_hour']))   		$this->params['price_per_hour'] = prepare_input($_POST['price_per_hour']);
		if(isset($_POST['default_distance']))     	$this->params['default_distance'] = (int)$_POST['default_distance'];
		if(isset($_POST['distance_extra_price']))  	$this->params['distance_extra_price'] = (float)$_POST['distance_extra_price'];		

		if(isset($_POST['priority_order'])) 		$this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))   			$this->params['is_active']  = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		//if(isset($_POST['field4_link']))   $this->params['field4_link'] = prepare_input($_POST['field4_link'], false, 'middle');
		
		$icon_width  = '240px';
		$icon_height = '120px';

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

		//$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CAR_AGENCY_VEHICLES; // TABLE_NAME
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_car_rental_vehicles';
		$this->actions      = array(
								'add'=>($this->agencyOwner ? true : true),
								'edit'=>($this->agencyOwner ? $objLogin->HasPrivileges('edit_car_agency_vehicles') : true),
								'details'=>true,
								'delete'=>($this->agencyOwner ? true : true));
		$this->actionIcons  = true;
		$this->allowRefresh = true;
        $this->allowPrint   = false;
		$this->allowTopButtons = true;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		
		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		if($this->agencyOwner){
			$agencies = $objLogin->AssignedToCarAgencies();
			$agencies_list = implode(',', $agencies);
			if(!empty($agencies_list)) $this->assigned_to_car_agencies = ' AND '.$this->tableName.'.agency_id IN ('.$agencies_list.')';
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_car_agencies;
		
		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
		$this->ORDER_CLAUSE = ''; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		// exporting settings
		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);

		// prepare transmissions array		
		$arr_transmissions = get_transmissions();

		// prepare agencies array
		$total_agencies = CarAgencies::GetAllAgencies(
			(!empty($agencies_list) ? TABLE_CAR_AGENCIES.'.id IN ('.$agencies_list.')' : '')
		);
		$arr_car_agencies = array();
		foreach($total_agencies[0] as $key => $val){
			$arr_car_agencies[$val['id']] = $val['name'];
		}

		// prepare vehicle types array
		$total_vehicle_types = CarAgenciesVehicleTypes::GetAllActive();
		$arr_vehicle_types = array();
		foreach($total_vehicle_types[0] as $key => $val){
			$arr_vehicle_types[$val['id']] = $val['name'];
		}
		
		// prepare makes array		
		$total_makes = Makes::GetAllActive();
		$arr_makes = array();
		foreach($total_makes[0] as $key => $val){
			$arr_makes[$val['id']] = $val['name'];
		}

		// prepare fule types
		$arr_fuel_types = get_fuel_types();

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types = array('0'=>_NO, '1'=>_YES);

		// define filtering fields		
		$this->isFilteringAllowed = true;
		///$this->maxFilteringColumns = 4; /* optional, defines split columns default = 0 */
		$this->arrFilteringFields = array(
			_AGENCY  			=> array('table'=>$this->tableName, 'field'=>'agency_id', 'type'=>'dropdownlist', 'source'=>$arr_car_agencies, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
			_MAKE  				=> array('table'=>'md', 'field'=>'make_id', 'type'=>'dropdownlist', 'source'=>$arr_makes, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
			_VEHICLE_CATEGORY  	=> array('table'=>'vcd', 'field'=>'name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'80px', 'visible'=>true),
		);
		
		$this->currencyFormat = get_currency_format();

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
		$currency_format = get_currency_format();
		$default_currency = Currencies::GetDefaultCurrency();
        $this->currencyFormat = get_currency_format();

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// prepare translations array for add/edit/detail modes
		// #003. prepare translations array for add/edit/detail modes
		/// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION,
			'agency_vehicle_id',
			array('description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags, nl2br, readonly_text
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.agency_id,
									'.$this->tableName.'.vehicle_type_id,
									'.$this->tableName.'.make_id,
									'.$this->tableName.'.model,
									CONCAT("<a href=\"index.php?page=cars&car_id=", '.$this->tableName.'.id, "&preview=yes\" target=\"_new\">", CONCAT(md.name, " ", '.$this->tableName.'.model), "</a>") as make_model,
									'.$this->tableName.'.registration_number,
									'.$this->tableName.'.mileage,
									'.$this->tableName.'.fuel_type_id,
									'.$this->tableName.'.transmission,
									'.$this->tableName.'.image_thumb,
									'.$this->tableName.'.price_per_day,
									'.$this->tableName.'.price_per_hour,
									'.$this->tableName.'.default_distance,
									'.$this->tableName.'.distance_extra_price,
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.priority_order,
									vcd.name as vehicle_type_name
								FROM '.$this->tableName.' 
									INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON '.$this->tableName.'.vehicle_type_id = vt.id
									INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' vcd ON vt.vehicle_category_id = vcd.agency_vehicle_category_id AND vcd.language_id = \''.Application::Get('lang').'\'
									INNER JOIN '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' md ON '.$this->tableName.'.make_id = md.make_id AND md.language_id = \''.Application::Get('lang').'\'
								';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'image_thumb' 			=> array('title'=>_IMAGE, 'type'=>'image', 'align'=>'left', 'width'=>'70px', 'image_width'=>'60px', 'image_height'=>'30px', 'target'=>'images/vehicles/', 'no_image'=>'no_image.png'),
			'make_model'    		=> array('title'=>_VEHICLE, 'type'=>'label', 'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'fuel_type_id'    		=> array('title'=>_FUEL_TYPE, 'type'=>'enum', 'align'=>'left', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_fuel_types),
			'transmission'   		=> array('title'=>_TRANSMISSION, 'type'=>'enum', 'align'=>'left', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_transmissions),
			'registration_number'	=> array('title'=>_REGISTRATION_NUMBER, 'type'=>'right', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'price_per_day'   		=> array('title'=>_PRICE_PER_DAY, 'type'=>'label', 'align'=>'right', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'pre_html'=>$default_currency, 'format'=>'currency', 'format_parameter'=>$currency_format.'|2'),
			'price_per_hour'   		=> array('title'=>_PRICE_PER_HOUR, 'type'=>'label', 'align'=>'right', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'pre_html'=>$default_currency, 'format'=>'currency', 'format_parameter'=>$currency_format.'|2'),			
			'mileage'				=> array('title'=>_MILEAGE, 'type'=>'label', 'align'=>'right', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'format_parameter'=>'', 'post_html'=>' '._DISTANCE_UNITS),
			'vehicle_type_name'    	=> array('title'=>_VEHICLE_CATEGORY, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'agency_id'    			=> array('title'=>_CAR_AGENCY, 'type'=>'enum', 'align'=>'center', 'width'=>'160px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_car_agencies),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
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
		// - Validation Min Value: 1, 10... Ex.: 'validation_minimum'=>'1'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'agency_id' 			=> array('title'=>_CAR_AGENCY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_car_agencies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false, 'javascript_event'=>'onchange="appChangeCarAgency(this.value,\'vehicle_type_id\',\'\',\''.Application::Get('token').'\')"'),
			'vehicle_type_id' 		=> array('title'=>_VEHICLE_CATEGORY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>array(), 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'make_id'  				=> array('title'=>_MAKE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_makes, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'model'  				=> array('title'=>_MODEL, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'40', 'validation_maxlength'=>'40', 'default'=>'', 'validation_type'=>'text', 'unique'=>false, 'visible'=>true, 'username_generator'=>false),
			'registration_number'	=> array('title'=>_REGISTRATION_NUMBER, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'20', 'validation_maxlength'=>'20', 'default'=>'', 'validation_type'=>'text', 'unique'=>false, 'visible'=>true, 'username_generator'=>false),
			'fuel_type_id'			=> array('title'=>_FUEL_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_fuel_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'transmission' 			=> array('title'=>_TRANSMISSION, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_transmissions, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'mileage'				=> array('title'=>_MILEAGE, 'type'=>'textbox', 'width'=>'110px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'8', 'validation_maxlength'=>'8', 'default'=>'', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'post_html'=>' '._DISTANCE_UNITS),
			'price_per_day'  		=> array('title'=>_PRICE_PER_DAY, 'type'=>'textbox', 'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
			'price_per_hour'  		=> array('title'=>_PRICE_PER_HOUR, 'type'=>'textbox', 'width'=>'60px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
			'default_distance'		=> array('title'=>_DEFAULT_DISTANCE, 'type'=>'textbox', 'width'=>'110px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'10', 'validation_maxlength'=>'10', 'default'=>'', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'post_html'=>' '._DISTANCE_UNITS),
			'distance_extra_price' 	=> array('title'=>_DISTANCE_EXTRA_PRICE, 'type'=>'textbox', 'width'=>'60px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
			'image'      			=> array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/vehicles/', 'random_name'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_thumb', 'thumbnail_width'=>$icon_width, 'thumbnail_height'=>$icon_height, 'file_maxsize'=>'2000k'),
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
								'.$this->tableName.'.vehicle_type_id,
								'.$this->tableName.'.make_id,
								'.$this->tableName.'.model,
								'.$this->tableName.'.fuel_type_id,
								'.$this->tableName.'.transmission,
								'.$this->tableName.'.registration_number,
								'.$this->tableName.'.mileage,
								'.$this->tableName.'.image,
								'.$this->tableName.'.price_per_day,
								'.$this->tableName.'.price_per_hour,
								'.$this->tableName.'.default_distance,
								'.$this->tableName.'.distance_extra_price,
								'.$this->tableName.'.is_active,
								'.$sql_translation_description.'
								'.$this->tableName.'.priority_order
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
							
		// define edit mode fields
		$this->arrEditModeFields = array(
			'agency_id' 			=> array('title'=>_CAR_AGENCY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_car_agencies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false, 'javascript_event'=>'onchange="appChangeCarAgency(this.value,\'vehicle_type_id\',\'\',\''.Application::Get('token').'\')"'),
			'vehicle_type_id' 		=> array('title'=>_VEHICLE_CATEGORY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>array(), 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'make_id'  				=> array('title'=>_MAKE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_makes, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'model'  				=> array('title'=>_MODEL, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'40', 'validation_maxlength'=>'40', 'default'=>'', 'validation_type'=>'text', 'unique'=>false, 'visible'=>true, 'username_generator'=>false),
			'registration_number'	=> array('title'=>_REGISTRATION_NUMBER, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'20', 'validation_maxlength'=>'20', 'default'=>'', 'validation_type'=>'text', 'unique'=>false, 'visible'=>true, 'username_generator'=>false),
			'fuel_type_id'			=> array('title'=>_FUEL_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_fuel_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'transmission' 			=> array('title'=>_TRANSMISSION, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_transmissions, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'mileage'				=> array('title'=>_MILEAGE, 'type'=>'textbox', 'width'=>'110px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'8', 'validation_maxlength'=>'8', 'default'=>'', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'post_html'=>' '._DISTANCE_UNITS),
			'image'      			=> array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/vehicles/', 'random_name'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_thumb', 'thumbnail_width'=>$icon_width, 'thumbnail_height'=>$icon_height, 'file_maxsize'=>'2000k'),
			'price_per_day'  		=> array('title'=>_PRICE_PER_DAY, 'type'=>'textbox', 'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
			'price_per_hour'  		=> array('title'=>_PRICE_PER_HOUR, 'type'=>'textbox', 'width'=>'60px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
			'default_distance'		=> array('title'=>_DEFAULT_DISTANCE, 'type'=>'textbox', 'width'=>'110px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'10', 'validation_maxlength'=>'10', 'default'=>'', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'post_html'=>' '._DISTANCE_UNITS),
			'distance_extra_price' 	=> array('title'=>_DISTANCE_EXTRA_PRICE, 'type'=>'textbox', 'width'=>'60px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
			'priority_order' 		=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		// format: strip_tags, nl2br, readonly_text
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'agency_id' 			=> array('title'=>_CAR_AGENCY, 'type'=>'enum', 'source'=>$arr_car_agencies, 'visible'=>true, 'view_type'=>'label|checkboxes', 'multi_select'=>false),
			'vehicle_type_id' 		=> array('title'=>_VEHICLE_CATEGORY, 'type'=>'enum', 'source'=>$arr_vehicle_types, 'visible'=>true, 'view_type'=>'label|checkboxes', 'multi_select'=>false),
			'make_id'  				=> array('title'=>_MAKE, 'type'=>'enum', 'source'=>$arr_makes, 'visible'=>true, 'view_type'=>'label|checkboxes', 'multi_select'=>false),
			'model'  				=> array('title'=>_MODEL, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'registration_number'	=> array('title'=>_REGISTRATION_NUMBER, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'fuel_type_id'			=> array('title'=>_FUEL_TYPE, 'type'=>'enum', 'source'=>$arr_fuel_types, 'visible'=>true, 'view_type'=>'label|checkboxes', 'multi_select'=>false),
			'transmission' 			=> array('title'=>_TRANSMISSION, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_transmissions, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'mileage'				=> array('title'=>_MILEAGE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'visible'=>true, 'post_html'=>' '._DISTANCE_UNITS),
			'image'      			=> array('title'=>_IMAGE, 'type'=>'image', 'target'=>'images/vehicles/', 'no_image'=>'no_image.png'),
			'price_per_day'  		=> array('title'=>_PRICE_PER_DAY, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>$default_currency),
			'price_per_hour'  		=> array('title'=>_PRICE_PER_HOUR, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>$default_currency),
			'default_distance'		=> array('title'=>_DEFAULT_DISTANCE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'format_parameter'=>'', 'visible'=>true, 'post_html'=>' '._DISTANCE_UNITS),
			'distance_extra_price' 	=> array('title'=>_DISTANCE_EXTRA_PRICE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>$default_currency),
			'priority_order' 		=> array('title'=>_ORDER, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array('description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'', 'maxlength'=>'512', 'validation_maxlength'=>'512', 'readonly'=>false))
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
	 * Returns all array of all active vehicles
	 * @param $where_clause
	 */
	public static function GetAllActive($where_clause = '')
	{		
		$lang = Application::Get('lang');

		$sql = 'SELECT
				av.id,
				av.agency_id,
				av.vehicle_type_id,
				av.make_id,
				av.model,
				av.registration_number,
				av.mileage,
				av.priority_order,
				av.is_active,
				md.name as make,
				vcd.name as vehicle_type_name
			FROM '.TABLE_CAR_AGENCY_VEHICLES.' av  
				INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON av.vehicle_type_id = vt.id
				INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' vcd ON vt.vehicle_category_id = vcd.agency_vehicle_category_id AND vcd.language_id = \''.$lang.'\'
				INNER JOIN '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' md ON av.make_id = md.make_id AND md.language_id = \''.$lang.'\'
			WHERE
				av.is_active = 1
				'.(!empty($where_clause) ? ' AND '.$where_clause : '').'				
			ORDER BY av.priority_order ASC ';					
				
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result;
    }

	/**
	 * Returns info for Vehicle
	 * @param int $vehicle_id
	*/
    public static function GetVehicleInfo($vehicle_id)
	{
		$output = array();
		$lang = Application::Get('lang');

        $sql = 'SELECT
            av.*,
            vt.image as type_image,
            vt.image_thumb as type_image_thumb,
            vt.passengers,
            vt.luggages,
            vt.doors,
			vd.description as vehicle_description,
			md.name as make,
			vcd.name as type_name
        FROM '.TABLE_CAR_AGENCY_VEHICLES.' av
			INNER JOIN '.TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION.' vd ON av.id = vd.agency_vehicle_id AND vd.language_id = \''.$lang.'\'
			INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON av.vehicle_type_id = vt.id
			INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' vcd ON vt.vehicle_category_id = vcd.agency_vehicle_category_id AND vcd.language_id = \''.$lang.'\'
			INNER JOIN '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' md ON av.make_id = md.make_id AND md.language_id = \''.$lang.'\'
        WHERE
            av.id = '.(int)$vehicle_id.' AND
            av.is_active = 1 AND
            vt.is_active = 1';

		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		//echo database_error();
		if($result[1] > 0){
            $output = isset($result[0]) ? $result[0] : array();	
		}
		return $output;
    }

	/**
	 * Returns price for Vehicle
	 * @param int $vehicle_id
	 * @param array $params
	 * @return float
	*/
	public static function GetCarPrice ($vehicle_id, $params)
	{
		$price = 0.00;

        $sql = 'SELECT
            av.*
        FROM '.TABLE_CAR_AGENCY_VEHICLES.' av
			INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON av.vehicle_type_id = vt.id
        WHERE
            av.id = '.(int)$vehicle_id.' AND
            av.is_active = 1 AND
            vt.is_active = 1';

		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$vehicle = isset($result[0]) ? $result[0] : array();

			$price = $vehicle['price_per_day'] * $params['days'] + $vehicle['price_per_hour'] * $params['hours'];
		}

		return $price;
	}
	
	/**
	 * Returns max price for cars
	*/
	public static function GetMaxPrice()
	{
		$sql = 'SELECT MAX(price_per_day) as max_price FROM '.TABLE_CAR_AGENCY_VEHICLES.' WHERE is_active = 1'; 
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return (int)$result[0]['max_price'];
		}
		return 0;
	}	
	
	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	/**
	 * Validate translation fields
	 */
	private function ValidateTranslationFields()	
	{
		//foreach($this->arrTranslations as $key => $val){
		//	if(trim($val['name']) == ''){
		//		$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
		//		$this->errorField = 'name_'.$key;
		//		return false;				
		//	}			
		//}		
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
		$sql = 'INSERT INTO '.TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION.'(id, agency_vehicle_id, language_id, description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['description'], false, 'medium')).'\')';
			$count++;
		}
		if(database_void_query($sql)){			
			return true;
		}else{
			return false;
		}
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
			$sql = 'UPDATE '.TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION.'
					SET description = \''.encode_text(prepare_input($val['description'], false, 'medium')).'\'
					WHERE id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	

    /**
	 * After-Deleting Record
	 */
	public function AfterDeleteRecord()
	{
        // Delete info from agency description table
		$sql = 'DELETE FROM '.TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION.' WHERE agency_vehicle_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}

	
	//==========================================================================
    // PUBLIC METHODS
	//==========================================================================
	/**
	 * After Add Record
	 */
	public function AfterAddRecord()
	{ 
		echo '<script type="text/javascript">appChangeCarAgency(jQuery("#agency_id").val(), "vehicle_type_id", "'.self::GetParameter('vehicle_type_id', false).'", "'.Application::Get('token').'");</script>';
	}
	
	/**
	 * After Edit Record
	 */
	public function AfterEditRecord()
	{
		$vehicle_type_value = (self::GetParameter('vehicle_type_id', false) != '') ? self::GetParameter('vehicle_type_id', false) : $this->result[0][0]['vehicle_type_id'];
		echo '<script type="text/javascript">appChangeCarAgency(jQuery("#agency_id").val(), "vehicle_type_id", "'.$vehicle_type_value.'", "'.Application::Get('token').'");</script>';
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
	 * Trigger method - allows to work with View Mode items
	 */
	protected function OnItemCreated_ViewMode($field_name, &$field_value)
	{
		if($field_name == 'make_model' && $field_value == '{administrator}'){
			$field_value = _ADMIN;			
		}
    }
}
