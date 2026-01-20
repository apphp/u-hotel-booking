<?php

/**
 *	CarAgencies Class 
 *  --------------
 *  Description : encapsulates Transportation Agencies class properties
 *  Updated	    : 27.02.2016
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllActive            ValidateTranslationFields
 *  __destruct              GetAgencyInfo
 *  BeforeInsertRecord      DrawAboutUs
 *  AfterInsertRecord       GetAgencyFullInfo
 *  BeforeUpdateRecord      AgenciesCount
 *  AfterUpdateRecord       DrawSearchAvailabilityBlock
 *  BeforeDeleteRecord      GetAllAgencies	
 *  AfterDeleteRecord       
 *  SearchFor
 *  DrawSearchResult
 *                          
 *	                        
 **/


class CarAgencies extends MicroGrid {
	
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
		
		if(isset($_POST['phone']))     $this->params['phone'] = prepare_input($_POST['phone']);
		if(isset($_POST['fax']))   	   $this->params['fax'] = prepare_input($_POST['fax']);
		if(isset($_POST['email']))     $this->params['email'] = prepare_input($_POST['email']);		
		if(isset($_POST['map_code']))  $this->params['map_code'] = prepare_input($_POST['map_code'], false, 'low');
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))   $this->params['is_active']  = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		
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
		$this->tableName 	= TABLE_CAR_AGENCIES; // 
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_car_rental_agencies';
		$this->actions      = array(
								'add'=>($this->agencyOwner ? false : true),
								'edit'=>($this->agencyOwner ? $objLogin->HasPrivileges('edit_car_agency_info') : true), 
								'details'=>true,
								'delete'=>($this->agencyOwner ? false : true));
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId = $objLogin->GetPreferredLang();

		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		$assigned_to_car_agencies = '';
		if($this->agencyOwner){
			$agencies = $objLogin->AssignedToCarAgencies();
			$agencies_list = implode(',', $agencies);
			if(!empty($agencies_list)) $assigned_to_car_agencies = ' AND '.$this->tableName.'.'.$this->primaryKey.' IN ('.$agencies_list.')';
		}
		$this->WHERE_CLAUSE .= $assigned_to_car_agencies;

		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.priority_order ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = true;
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
		///$currency_format = get_currency_format();
		$default_currency = Currencies::GetDefaultCurrency();
        $this->currencyFormat = get_currency_format();		

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_default_types = array('0'=>_NO, '1'=>_YES);

		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			//_LOCATIONS  => array('table'=>$this->tableName, 'field'=>'agency_location_id', 'type'=>'dropdownlist', 'source'=>$arr_agencies_locations, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'address', 'description', 'terms_and_conditions')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		/// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		/// $sql_translation_description = $this->PrepareTranslateSql(
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_CAR_AGENCIES_DESCRIPTION,
			'agency_id',
			array('name', 'address', 'description', 'terms_and_conditions')
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
									'.$this->tableName.'.phone,
									'.$this->tableName.'.fax,
									'.$this->tableName.'.email,
									'.$this->tableName.'.map_code,
									'.$this->tableName.'.logo_image,
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.priority_order,
									'.TABLE_CAR_AGENCIES_DESCRIPTION.'.name,
									CONCAT("<a href=\"javascript:void();\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_car_rental_vehicle_types\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_'.DB_PREFIX.'car_agency_vehicle_typesagency_id=", '.$this->tableName.'.'.$this->primaryKey.', "\',\'post\')\"",">[ '._VEHICLE_TYPES.' ]</a>  (", (SELECT COUNT(*) as cnt FROM '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt WHERE vt.agency_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_vehicle_types,
									CONCAT("<a href=\"javascript:void();\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_car_rental_vehicles\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_'.DB_PREFIX.'car_agency_vehiclesagency_id=", '.$this->tableName.'.'.$this->primaryKey.', "\',\'post\')\"",">[ '._VEHICLES.' ]</a>  (", (SELECT COUNT(*) as cnt FROM '.TABLE_CAR_AGENCY_VEHICLES.' av WHERE av.agency_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_vehicles,
									CONCAT("<a href=\"javascript:void();\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_car_rental_agency_locations\',\'&amp;agency_id=", '.$this->tableName.'.'.$this->primaryKey.', "\',\'get\')\"",">[ '._LOCATIONS.' ]</a>  (", (SELECT COUNT(*) as cnt FROM '.TABLE_CAR_AGENCY_LOCATIONS.' av WHERE av.agency_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_locations
								FROM ('.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_CAR_AGENCIES_DESCRIPTION.' ON '.$this->tableName.'.id = '.TABLE_CAR_AGENCIES_DESCRIPTION.'.agency_id AND '.TABLE_CAR_AGENCIES_DESCRIPTION.'.language_id = \''.$this->languageId.'\')
								';
								
		// define view mode fields
		$this->arrViewModeFields = array(
			'logo_image' 			=> array('title'=>_LOGO, 'type'=>'image', 'align'=>'center', 'width'=>'80px', 'image_width'=>'60px', 'image_height'=>'30px', 'target'=>'images/car_agencies/', 'no_image'=>'no_image.png'),
			'name'    				=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'email' 	  			=> array('title'=>_EMAIL, 'type'=>'link', 'maxlength'=>'70', 'href'=>'mailto:{email}', 'width'=>'170px', 'align'=>'left'),
			'phone'   				=> array('title'=>_PHONE, 'type'=>'label', 'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'fax'     				=> array('title'=>_FAX, 'type'=>'label', 'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'link_vehicle_types' 	=> array('title'=>_VEHICLE_TYPES, 'type'=>'label', 'align'=>'center', 'width'=>'130px', 'sortable'=>false, 'visible'=>true),
			'link_vehicles' 		=> array('title'=>_VEHICLES, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>false, 'visible'=>true),
			'link_locations' 		=> array('title'=>_LOCATIONS, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'sortable'=>false, 'visible'=>true),
			'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'50px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
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

			'separator_1'   =>array(
				'separator_info' => array('legend'=>_AGENCY_INFO, 'columns'=>'0'),
				'phone'  		=> array('title'=>_PHONE, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
				'fax'  		   	=> array('title'=>_FAX, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
				'email' 		=> array('title'=>_EMAIL_ADDRESS,'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'70', 'validation_type'=>'email', 'unique'=>true),
				'logo_image'    => array('title'=>_LOGO, 'type'=>'image',    'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/car_agencies/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'agency_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>false, 'thumbnail_field'=>'agency_image_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
				'map_code'      => array('title'=>_MAP_CODE, 'type'=>'textarea', 'required'=>false, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
				'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
			),
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
								'.$this->tableName.'.phone,
								'.$this->tableName.'.fax,
								'.$this->tableName.'.email,
								'.$this->tableName.'.logo_image,
								'.$this->tableName.'.map_code,
								'.$sql_translation_description.'
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE
								'.$this->tableName.'.'.$this->primaryKey.' = _RID_' . $assigned_to_car_agencies;

		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_1'   => array(
				'separator_info'=> array('legend'=>_AGENCY_INFO, 'columns'=>'0'),
				'phone'  		=> array('title'=>_PHONE, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
				'fax'  		   	=> array('title'=>_FAX, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
				'email' 		=> array('title'=>_EMAIL_ADDRESS,'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'70', 'validation_type'=>'email', 'unique'=>true),
				'logo_image'   => array('title'=>_LOGO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/car_agencies/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>true, 'image_name_pefix'=>'agency_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>false, 'thumbnail_field'=>'agency_image_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
				'map_code'      => array('title'=>_MAP_CODE, 'type'=>'textarea', 'required'=>false, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
				'priority_order'=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
				'is_active'     => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
			),
		);

		//----------------------------------------------------------------------
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_1'   =>array(
				'separator_info' => array('legend'=>_AGENCY_INFO, 'columns'=>'0'),
				'phone'  		=> array('title'=>_PHONE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
				'fax'  		   	=> array('title'=>_FAX, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
				'email'     	=> array('title'=>_EMAIL_ADDRESS, 	 'type'=>'label'),
				'logo_image'   => array('title'=>_LOGO, 'type'=>'image', 'target'=>'images/car_agencies/', 'no_image'=>'no_image.png', 'image_width'=>'120px', 'image_height'=>'90px', 'visible'=>true),
				'map_code'      => array('title'=>_MAP_CODE, 'type'=>'html', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
				'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
			),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
				array('name'        		 => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
					  'address' 			 => array('title'=>_ADDRESS, 'type'=>'textarea', 'width'=>'410px', 'height'=>'55px', 'required'=>false, 'maxlength'=>'225', 'validation_maxlength'=>'225', 'readonly'=>false),
					  'description' 		 => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'2048', 'validation_maxlength'=>'2048', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
					  'terms_and_conditions' => array('title'=>_TERMS, 'type'=>'textarea', 'width'=>'410px', 'height'=>'70px', 'required'=>false, 'maxlength'=>'2048', 'validation_maxlength'=>'5096', 'readonly'=>false, 'editor_type'=>'wysiwyg')
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
	 * Returns all array of all active agencies
	 * @param $where_clause
	 */
	public static function GetAllAgencies($where_clause = '')
	{		
		$sql = 'SELECT
					'.TABLE_CAR_AGENCIES.'.*,
					'.TABLE_CAR_AGENCIES_DESCRIPTION.'.name,
					'.TABLE_CAR_AGENCIES_DESCRIPTION.'.description,
					'.TABLE_CAR_AGENCIES_DESCRIPTION.'.terms_and_conditions
				FROM '.TABLE_CAR_AGENCIES.'
					INNER JOIN '.TABLE_CAR_AGENCIES_DESCRIPTION.' ON '.TABLE_CAR_AGENCIES.'.id = '.TABLE_CAR_AGENCIES_DESCRIPTION.'.agency_id AND '.TABLE_CAR_AGENCIES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\'
				WHERE 1=1 
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.TABLE_CAR_AGENCIES.'.priority_order ASC ';
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 * Returns all array of all active agencies
	 * @param $where_clause
	 */
	public static function GetAllActive($where_clause = '')
	{		
		$sql = 'SELECT
					'.TABLE_CAR_AGENCIES.'.*,
					'.TABLE_CAR_AGENCIES_DESCRIPTION.'.name,
					'.TABLE_CAR_AGENCIES_DESCRIPTION.'.description,
					'.TABLE_CAR_AGENCIES_DESCRIPTION.'.terms_and_conditions
				FROM '.TABLE_CAR_AGENCIES.'
					INNER JOIN '.TABLE_CAR_AGENCIES_DESCRIPTION.' ON '.TABLE_CAR_AGENCIES.'.id = '.TABLE_CAR_AGENCIES_DESCRIPTION.'.agency_id AND '.TABLE_CAR_AGENCIES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.TABLE_CAR_AGENCIES.'.is_active = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.TABLE_CAR_AGENCIES.'.priority_order ASC ';
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 * Draws About Us block
	 * @param $draw
	 */
	public static function DrawAboutUs($draw = true)
	{		
		$lang = Application::Get('lang');		
		$output = '';
		
		$sql = 'SELECT
					a.phone,
					a.fax,
					a.map_code,	
					ad.name,
					ad.address,
					ad.description,
					ad.terms_and_conditions
				FROM '.TABLE_CAR_AGENCIES.' a
					INNER JOIN '.TABLE_CAR_AGENCIES_DESCRIPTION.' ad ON a.id = ad.agency_id
				WHERE AND ad.language_id = \''.$lang.'\'';
				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output .= '<h3>'.$result[0]['name'].'</h3>';
			$output .= '<p>'.$result[0]['description'].'</p>';
			$output .= '<p>'.$result[0]['address'].'</p>';
			$output .= '<p>'._PHONE.': '.$result[0]['phone'].'<br />'._FAX.': '.$result[0]['fax'].'</p>';
			if($result[0]['map_code']) $output .= '<p>'._OUR_LOCATION.':<br /> '.$result[0]['map_code'].'</p>';
		}
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Returns agency info
	 * @param $agency_id 
	 */
	public static function GetAgencyInfo($agency_id = '')
	{
		$output = array();
		$sql = 'SELECT *
				FROM '.TABLE_CAR_AGENCIES.'
				WHERE '.(!empty($agency_id) ? ' id ='.(int)$agency_id : ' 1=1');		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output = $result[0];
		}
		return $output;
	}

	/**
	 * Returns agency full info
	 * @param $agency_id
	 * @param $lang
	 */
	public static function GetAgencyFullInfo($agency_id = '', $lang = '')
	{
		$output = array();
		$lang = !empty($lang) ? $lang : Application::Get('lang');
		$sql = "SELECT
					a.*,
					ad.name,
					ad.address,
					ad.description,
					ad.terms_and_conditions
				FROM ".TABLE_CAR_AGENCIES." as a
					INNER JOIN ".TABLE_CAR_AGENCIES_DESCRIPTION." ad ON a.id = ad.agency_id AND ad.language_id = '".$lang."' 
				WHERE 1=1
				".(!empty($agency_id) ? ' AND a.id = '.(int)$agency_id : '')."
				LIMIT 0, 1";
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output = $result[0];
		}
		return $output;
	}	

	/**
	 * Returns agencys count
	*/
	public static function AgenciesCount()
	{
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_CAR_AGENCIES.' WHERE is_active = 1'; 
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return (int)$result[0]['cnt'];
		}
		return 0;
	}	

	/**
	 * Returns agency description
	 * @param $agency_id
	 * @param $draw
	*/
	public static function DrawAgencyDescription($agency_id, $draw = true)
	{
		$output = '';
		
		$sql = 'SELECT
					a.id,
					a.agency_location_id,
					a.phone,
					a.fax,
					a.map_code,
					a.logo_image,
					a.is_active,
					a.priority_order,
					ad.name as agency_name,
					ad.description as agency_description,
					ad.terms_and_conditions as agency_terms_and_conditions
				FROM '.TABLE_CAR_AGENCIES.' a
					LEFT OUTER JOIN '.TABLE_CAR_AGENCIES_DESCRIPTION.' ad ON a.id = ad.agency_id AND ad.language_id = \''.Application::Get('lang').'\'
				WHERE a.is_active = 1 AND a.id = '.(int)$agency_id;
				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			
			$output .= '<table class="tblAgencyDescription" width="100%">';
			$output .= '<tr>';
			$output .= ' <td colspan="2"></td>';
			$output .= ' <td rowspan="2" width="140px"><img src="images/car_agencies/'.$result[0]['logo_image'].'" style="float:'.Application::Get('defined_right').';margin:0 5px;" alt="" /></td>';
			$output .= '</tr>';
			$output .= '<tr>';
			$output .= ' <td valign="top" width="170px">';
			if($result[0]['phone'] || $result[0]['fax']){
				if($result[0]['phone']) $output .= '<b>'._PHONE.'</b>: '.$result[0]['phone'].'<br />';
				if($result[0]['fax']) $output .= '<b>'._FAX.'</b>: '.$result[0]['fax'];
			}
			$output .= '</td>';			
			$output .= '<td valign="top" height="80px">';

			$output .= '</tr>';
			
			$output .= '<tr><td colspan="4">'.$result[0]['agency_description'].'</td></tr>';
			$output .= '</table>';
			
			if($result[0]['map_code']) $output .= '<p><b>'._LOCATION.'</b>:<br /> '.$result[0]['map_code'].'</p>';			
						
		}else{
			$output = draw_important_message(_WRONG_PARAMETER_PASSED, false);					
		}
		
		if($draw) echo $output;
		else return $output;
	}
	

	/**
	 * Draw information about rooms and services
	 * @param $draw
	 */	
	public static function DrawAgencysInfo($draw = true)
	{
		$lang = Application::Get('lang');
		$output = '';

		$sql = 'SELECT
				a.*,
				ad.name,
				ad.address,
				ad.description,
				ad.terms_and_conditions
			FROM '.TABLE_CAR_AGENCIES.' a
				LEFT OUTER JOIN '.TABLE_CAR_AGENCIES_DESCRIPTION.' ad ON a.id = ad.agency_id
			WHERE
				a.is_active = 1 AND
				ad.language_id = \''.$lang.'\'';
			
		$result = database_query($sql, DATA_AND_ROWS);
		for($i=0; $i<$result[1]; $i++){
			if($i > 0) $output .= '<div class="line-hor"></div>';					
			$output .= '<h3>'.prepare_link('agencies', 'hid', $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], '', _CLICK_TO_VIEW).'</h3>';
			$output .= strip_tags($result[0][$i]['address'], '<b><u><i><br>').'<br>';
			$output .= strip_tags($result[0][$i]['description'], '<b><u><i><br>').'<br>';
			$output .= (($result[0][$i]['phone'] != '') ? _PHONE.': '.$result[0][$i]['phone'].'<br>' : '');
			$output .= (($result[0][$i]['fax'] != '') ? _FAX.': '.$result[0][$i]['fax'].'<br>' : '');
		}
		if($draw) echo $output;
		else return $output;
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
		$sql = 'INSERT INTO '.TABLE_CAR_AGENCIES_DESCRIPTION.'(id, agency_id, language_id, name, address, description, terms_and_conditions) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['address'])).'\', \''.encode_text(prepare_input($val['description'], false, 'medium')).'\', \''.encode_text(prepare_input($val['terms_and_conditions'], false, 'medium')).'\')';
			$count++;
		}
		if(database_void_query($sql)){			
			$sql = "INSERT INTO ".TABLE_CAR_AGENCY_PAYMENT_GATEWAYS." (id, agency_id, payment_type, payment_type_name, api_login, api_key, payment_info) VALUES
				(NULL, ".(int)$this->lastInsertId.", 'paypal', 'PayPal', '', '', ''),
				(NULL, ".(int)$this->lastInsertId.", '2co', '2CO (2 checkout)', '', '', ''),
				(NULL, ".(int)$this->lastInsertId.", 'authorize.net', 'Authorize.Net', '', '', ''),
				(NULL, ".(int)$this->lastInsertId.", 'bank.transfer', 'Bank Transfer', '', '', 'Bank name: {BANK NAME HERE}\r\nSwift code: {CODE HERE}\r\nRouting in Transit# or ABA#: {ROUTING HERE}\r\nAccount number *: {ACCOUNT NUMBER HERE}\r\n\r\n*The account number must be in the IBAN format which may be obtained from the branch handling the customer''s account or may be seen at the top the customer''s bank statement\r\n')";
			database_void_query($sql);

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
			$sql = 'UPDATE '.TABLE_CAR_AGENCIES_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						address = \''.encode_text(prepare_input($val['address'], false, 'medium')).'\',
						description = \''.encode_text(prepare_input($val['description'], false, 'medium')).'\',
						terms_and_conditions = \''.encode_text(prepare_input($val['terms_and_conditions'], false, 'medium')).'\'
					WHERE agency_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	


    /**
	 * Before-Deleting Record
	 */
	public function BeforeDeleteRecord()
	{
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
				// make last agency always default and active
				$sql = 'UPDATE '.TABLE_CAR_AGENCIES.' SET is_active = \'1\' WHERE id= '.(int)$result[0][0]['id'];
				database_void_query($sql);
			}
		}

        // delete info from agency description table
		$sql = 'DELETE FROM '.TABLE_CAR_AGENCIES_DESCRIPTION.' WHERE agency_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			$sql = 'DELETE FROM '.TABLE_CAR_AGENCY_PAYMENT_GATEWAYS.' WHERE agency_id = '.$this->curRecordId;
			database_void_query($sql);
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Draws search availability form
	 * @param $type
	 * @param $action_url
	 * @param $target
	*/
	public static function DrawSearchAvailabilityBlock($type = 'home',  $action_url = '', $target = '')
	{
        global $objSettings;

		$action_url = ($action_url != '') ? $action_url : APPHP_BASE;
		$target = (!empty($target)) ? $target : '';
        $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;
		$arr_agencies_locations = array();
		$lang = Application::Get('lang');
        $nl ="\n";
		
		$destination_validation_attr = (DESTINATION_VALIDATION) ? ' data-required="required" data-required-message="'.htmlentities(_FIELD_CANNOT_BE_EMPTY_DESTINATION).'"' : '';

		$total_agencies_locations = CarAgenciesLocations::GetAgenciesLocations();
		foreach($total_agencies_locations[0] as $key => $val){
			$arr_agencies_locations[$val['country_name']][$val['id']] = $val['name'];
		}

        $picking_up_select      = isset($_POST['car_picking_up']) ? prepare_input($_POST['car_picking_up']) : '';
        $dropping_off_select    = isset($_POST['car_dropping_off']) ? prepare_input($_POST['car_dropping_off']) : '';
        $picking_up_id_select   = !empty($picking_up_select) && isset($_POST['car_picking_up_id']) ? (int)$_POST['car_picking_up_id'] : '';
        $dropping_off_id_select = !empty($dropping_off_select) && isset($_POST['car_dropping_off_id']) ? (int)$_POST['car_dropping_off_id'] : '';
        $selected_pick_up_date  = isset($_POST['car_pick_up_date']) ? prepare_input($_POST['car_pick_up_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + 5400); // + 1 hours 30 min
        $selected_drop_off_date = isset($_POST['car_drop_off_date']) ? prepare_input($_POST['car_drop_off_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + (24 * 60 * 60) + 5400); // + 1 day and 1 hours 30 min
        $pick_up_time_select    = isset($_POST['car_pick_up_time']) && $_POST['car_pick_up_time'] !== '' ? prepare_input($_POST['car_pick_up_time']) : date('G', time() + 5400).(date('i') < '30' ? '.5' : ''); // show count hours[.5]
        $drop_off_time_select   = isset($_POST['car_drop_off_time']) && $_POST['car_drop_off_time'] !== '' ? prepare_input($_POST['car_drop_off_time']) : date('G', time() + (24 * 60 * 60) + 5400).(date('i') < '30' ? '.5' : '');
        $current_currency       = isset($_POST['current_currency']) ? prepare_input($_POST['current_currency'], true) : Application::Get('currency_code');

		$price_select           = isset($_POST['car_price']) ? prepare_input($_POST['car_price']) : 0;
		if(!empty($price_select)){
			$arr_price = explode(';',$price_select);
		}
		$min_price_select = isset($arr_price[0]) && is_numeric($arr_price[0]) ? $arr_price[0] : 0;
		$max_price_select = isset($arr_price[1]) && is_numeric($arr_price[1]) ? $arr_price[1] : 0;

        $car_types_select  = isset($_POST['car_types']) ? prepare_input($_POST['car_types']) : array();
		$make_sort_select  = !empty($_POST['sort_make']) && in_array(strtolower($_POST['sort_make']), array('asc', 'desc')) ? strtolower($_POST['sort_make']) : '';
		$price_sort_select = !empty($_POST['sort_price']) && in_array(strtolower($_POST['sort_price']), array('asc', 'desc')) ? strtolower($_POST['sort_price']) : '';
		$view_type_select = !empty($_POST['view_type']) ? prepare_input($_POST['view_type']) : 'list';

        // Date
        // Time
        $pick_up_time = '<select class="my-form-control hasCustomSelect car_pick_up_time" name="car_pick_up_time">';
        $drop_off_time = '<select class="my-form-control hasCustomSelect car_drop_off_time" name="car_drop_off_time">';
        $pick_up_time .= '<option value="" '.($pick_up_time_select === '' ? ' selected="selected"' : '').'>-- '._SELECT.' --</option>'.$nl;
        $drop_off_time .= '<option value="" '.($drop_off_time_select === '' ? ' selected="selected"' : '').'>-- '._SELECT.' --</option>'.$nl;
        for($i = 0; $i < 24; $i++){
            if($i < 12){
                $end = 'AM';
                $hours = $i;
            }else{
                $end = 'PM';
                $hours = $i - 12;
            }
            $hours = $hours == 0 ? 12 : $hours;

            $hours_exactly = $hours.':00 '.$end;
            $hours_half = $hours.':30 '.$end;
            $time_exactly = (string)$i;
            $time_half = $i.'.5';
            $pick_up_time .= '<option'.($pick_up_time_select == $time_exactly ? ' selected="selected"' : '').' value="'.htmlentities($time_exactly).'">'.htmlentities($hours_exactly).'</option>';
            $pick_up_time .= '<option'.($pick_up_time_select == $time_half ? ' selected="selected"' : '').' value="'.htmlentities($time_half).'">'.htmlentities($hours_half).'</option>';
            $drop_off_time .= '<option'.($drop_off_time_select == $time_exactly ? ' selected="selected"' : '').' value="'.htmlentities($time_exactly).'">'.htmlentities($hours_exactly).'</option>';
            $drop_off_time .= '<option'.($drop_off_time_select == $time_half ? ' selected="selected"' : '').' value="'.htmlentities($time_half).'">'.htmlentities($hours_half).'</option>';
        }
        $pick_up_time .= '</select>';
        $drop_off_time .= '</select>';

		if($type == 'home'){
			$output = '
			<div tabindex="5002" style="overflow-y: hidden;" id="car" class="tab-pane fade">
				<form'.(!empty($target) ? ' target="'.$target.'"' : '').' action="'.$action_url.'index.php?page=check_cars_availability" id="cars-reservation-form" name="cars-reservation-form" method="post">
				'.draw_hidden_field('p', '1', false, 'page_number').'
				'.draw_token_field(false).'

				<div class="w100percent" style="margin-bottom:10px;">
					<div class="wh100percent textleft">
						<label>'._PICKING_UP.'</label>
						'.draw_hidden_field('car_picking_up_id', $picking_up_id_select, false, 'car_picking_up_id').'
						<input class="form-control car_picking_up" '.$destination_validation_attr.' name="car_picking_up" id="car_picking_up" placeholder="'.htmlentities(_TYPE_YOUR_PICKUP_LOCATION).'" type="text" value="'.htmlentities($picking_up_select).'">
					</div>
				</div>

				<div class="w100percentlast">
					<div class="wh100percent textleft right">
						<label>'._DROPPING_OFF.'</label>
						'.draw_hidden_field('car_dropping_off_id', $dropping_off_id_select, false, 'car_dropping_off_id').'
						<input class="form-control car_dropping_off" '.$destination_validation_attr.' name="car_dropping_off" id="car_dropping_off" placeholder="'.htmlentities(_TYPE_YOUR_DROPPING_OFF_LOCATION).'" type="text" value="'.htmlentities($dropping_off_select).'">
					</div>
				</div>
				
				<div class="clearfix"></div><br>
				
				<div class="w50percent">
					<div class="wh90percent textleft">
						<label>'._PICK_UP_DATE.'</label>
						<input class="form-control mySelectCalendar car_pick_up_date" name="car_pick_up_date" id="datepicker5" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.htmlentities($selected_pick_up_date).'"  autocomplete="off">
					</div>
				</div>
	
				<div class="w50percentlast">
					<div class="wh90percent textleft right">
						<label> &nbsp;'._HOUR.'</label>
						'.$pick_up_time.'
					</div>
				</div>
				
				<div class="clearfix"></div>
				
				<div class="room1 margtop15">
					<div class="w50percent">
						<div class="wh90percent textleft">
							<label>'._DROP_OFF_DATE.'</label>
							<input class="form-control mySelectCalendar car_drop_off_date" name="car_drop_off_date" id="datepicker6" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.htmlentities($selected_drop_off_date).'" autocomplete="off">
						</div>
					</div>
	
					<div class="w50percentlast">	
						<div class="wh90percent textleft right">
						<label> &nbsp;'._HOUR.'</label>
                        '.$drop_off_time.'
						</div>
					</div>
				</div>
				
				<div class="clearfix pbottom15"></div>
				<br>
				
				<input class="button-availability" type="submit" value="'.htmlentities(_SEARCH).'" type="button" />
				</form>
			</div>';
			
		}else{
			$output = '
			<div id="collapse0" class="side_box_content collapse in">					
				<form'.(!empty($target) ? ' target="'.$target.'"' : '').' action="'.$action_url.'index.php?page=check_cars_availability" id="cars-reservation-form" name="cars-reservation-form" method="post">
				'.draw_hidden_field('p', '1', false, 'page_number').'
				'.draw_hidden_field('view_type', $view_type_select, false, 'view_type').'
				'.draw_hidden_field('sort_make', $make_sort_select, false, 'make_id').'
				'.draw_hidden_field('sort_price', $price_sort_select, false, 'sort_price').'
				'.draw_hidden_field('current_currency', Application::Get('currency_code'), false, 'current_currency').'
				'.draw_token_field(false).'
				
					<!-- CARS TAB -->
					<div>					
						<div class="w100percent" style="margin-bottom:10px;">
							<div class="wh100percent textleft">
								<label>'._PICKING_UP.'</label>
								'.draw_hidden_field('car_picking_up_id', $picking_up_id_select, false, 'car_picking_up_id').'
								<input class="form-control car_picking_up" '.$destination_validation_attr.' name="car_picking_up" id="car_picking_up" placeholder="'.htmlentities(_TYPE_YOUR_PICKUP_LOCATION).'" type="text" value="'.htmlentities($picking_up_select).'">
							</div>
						</div>

						<div class="w100percentlast">
							<div class="wh100percent textleft right">
								<label>'._DROPPING_OFF.'</label>
								'.draw_hidden_field('car_dropping_off_id', $dropping_off_id_select, false, 'car_dropping_off_id').'
								<input class="form-control car_dropping_off" '.$destination_validation_attr.' name="car_dropping_off" id="car_dropping_off" placeholder="'.htmlentities(_TYPE_YOUR_DROPPING_OFF_LOCATION).'" type="text" value="'.htmlentities($dropping_off_select).'">
							</div>
						</div>
						
						<div class="clearfix pbottom15"></div>
						
						<div class="w50percent">
							<div class="wh90percent textleft">
								<label>'._PICK_UP_DATE.'</label>
								<input class="form-control mySelectCalendar car_pick_up_date" name="car_pick_up_date" id="datepicker5" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.htmlentities($selected_pick_up_date).'" autocomplete="off">
							</div>
						</div>

						<div class="w50percentlast">
							<div class="wh90percent textleft right">
								<label>'._HOUR.'</label>
                                '.$pick_up_time.'
							</div>
						</div>
						
						<div class="clearfix pbottom15"></div>
						
						<div class="room1">
							<div class="w50percent">
								<div class="wh90percent textleft">
									<label>'._DROP_OFF_DATE.'</label>
									<input class="form-control mySelectCalendar car_drop_off_date" name="car_drop_off_date" id="datepicker6" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.htmlentities($selected_drop_off_date).'" autocomplete="off">
								</div>
							</div>

							<div class="w50percentlast">	
								<div class="wh90percent textleft right">
									<label>'._HOUR.'</label>
                                    '.$drop_off_time.'
								</div>
							</div>
						</div>
						
						<div class="clearfix pbottom15"></div>
							
						<div class="w100percentlast">
							<div class="w100percentlast">';
						if($type != 'details'){
							$output .= '<input class="button-availability" type="submit" value="'.htmlentities(_SEARCH).'" type="button" />';
						}
						$output .= '</div>
						</div>
					</div>
					<!-- END OF CARS TAB -->';
		
				if($type == 'menu' && Application::Get('page') == 'check_cars_availability'){								
					$allTypes = array();
					$min_price = $max_price = 0;
					// Prepare types
					$result = VehicleCategories::GetAllActive();
					if($result[1] > 0){
						foreach($result[0] as $type){
							$allTypes[$type['id']] = $type['name'];
						}
					}
					
					$min_price = 0;
					$max_price = Vehicles::GetMaxPrice();

					$currency_symbol = Application::Get('currency_symbol');
                    $currency_code = Application::Get('currency_code');
                    $currency_rate = Application::Get('currency_rate');
                    if($currency_code != $current_currency){
                        $current_currency_info = Currencies::GetCurrencyInfo($current_currency);
                        if($current_currency_info['code'] != $currency_code){
                            $min_price_select = floor($min_price_select * ($currency_rate / $current_currency_info['rate']));
                            $max_price_select = ceil($max_price_select * ($currency_rate / $current_currency_info['rate']));
                        }
                    }

					$to_price = ceil($max_price * $currency_rate / 10) * 10;

					$min_price_select = !empty($min_price_select) && $min_price < $min_price_select ? $min_price_select : $min_price;
					$max_price_select = !empty($max_price_select) && $to_price > $max_price_select ? $max_price_select : $to_price;
					
					$output .= '
					<br>
					<h4>'._FILTER_BY.'</h4>
					<br>
					
					<div class="collapse in">
					
						<!-- Price range -->					
						<label>'._PRICE_RANGE.'</label>
					
						<div class="padding20">
							<div class="layout-slider wh100percent">
								<span class="cstyle09"><input id="car-price-slider" type="slider" name="car_price" value="'.(int)$min_price_select.';'.(int)$max_price_select.'" /></span>
							</div>
							<script type="text/javascript" >
							  jQuery("#car-price-slider").slider({ from: 0, to: '.$to_price.', step: 1, smooth: true, round: 0, dimension: "&nbsp;'.$currency_symbol.'", skin: "round" });
							</script>
						</div>';

					if(!empty($allTypes)){
						$output .= '<label>'._CAR_TYPE.'</label>
							<div class="hpadding20">';
						foreach($allTypes as $key => $type_name){
							$output .= '<div class="checkbox">
								<label><input type="checkbox"'.(in_array($key, $car_types_select) ? ' checked="checked"' : '').' name="car_types[]" value="'.htmlentities($key).'">'.htmlentities($type_name).'</label>
							</div>';
						}
						$output .= '</div>';
					}
					$output .= '</div>
					<!-- End of Price range -->';
				}
				
                $output .= '</form>
					</div>';
		}
		$lang = Application::Get('lang');
		$output .= '<script>
		jQuery(document).ready(function(){
			jQuery(function() {
				jQuery("#datepicker5").datepicker({
					minDate: 0,
					maxDate: "+1y"
				});
				jQuery("#datepicker6").datepicker({
					minDate: 1,
					maxDate: "+1y +1d"
				});
			});
			jQuery("#car_picking_up").autocomplete({
				source: function(request, response){
					var token = "'.htmlentities(Application::Get('token')).'";
					jQuery.ajax({
						url: "'.$action_url.'ajax/car_location.ajax.php",
						global: false,
						type: "POST",
						data: ({
							token: token,
							act: "send",
							lang: "'.$lang.'",
							search : jQuery("#car_picking_up").val(),
						}),
						dataType: "json",
						async: true,
						error: function(html){
							if(html.responseText != undefined){if(confirm(html.responseText)){location.reload();}}
							console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
						},
						success: function(data){
							if(data.length == 0){
								response({ label: "'.htmlentities(_NO_MATCHES_FOUND).'" });
							}else{
								jQuery("#car_picking_up_id").val("");
								response(jQuery.map(data, function(item){
									return{ id: item.id, label: item.label }
								}));
							}
						}
					});
				},
				minLength: 2,
				select: function(event, ui) {
					jQuery("#car_picking_up_id").val(ui.item.id);
					if(typeof(ui.item.id) == "undefined"){
						jQuery("#car_picking_up").val("");
						return false;
					}
				}
			});
			jQuery("#car_dropping_off").autocomplete({
				source: function(request, response){
					var token = "'.htmlentities(Application::Get('token')).'";
					jQuery.ajax({
						url: "'.$action_url.'ajax/car_location.ajax.php",
						global: false,
						type: "POST",
						data: ({
							token: token,
							act: "send",
							lang: "'.$lang.'",
							search : jQuery("#car_dropping_off").val(),
						}),
						dataType: "json",
						async: true,
						error: function(html){
							if(html.responseText != undefined){if(confirm(html.responseText)){location.reload();}}
							console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
						},
						success: function(data){
							if(data.length == 0){								
								response({ label: "'.htmlentities(_NO_MATCHES_FOUND).'" });
							}else{
								jQuery("#car_dropping_off_id").val("");
								response(jQuery.map(data, function(item){
									return{ id: item.id, label: item.label }
								}));
							}
						}
					});
				},
				minLength: 2,
				select: function(event, ui) {
					jQuery("#car_dropping_off_id").val(ui.item.id);
					if(typeof(ui.item.id) == "undefined"){
						jQuery("#car_dropping_off").val("");
						return false;
					}
				}
			});
		});
		</script>';	
		return $output;		
	}
	
    /**
	 * Search available rooms
	 * @param $params
	 */
	public function SearchFor($params = array())
	{
		$lang               = Application::Get('lang');
		$car_id             = !empty($params['car_id'])		        ? $params['car_id']             : '';
		$picking_up         = !empty($params['picking_up'])         ? $params['picking_up']         : 0;
		$dropping_off       = !empty($params['dropping_off'])       ? $params['dropping_off']       : 0;
		$pick_up_date_time  = !empty($params['pick_up_date_time'])  ? $params['pick_up_date_time']  : '';
		$drop_off_date_time = !empty($params['drop_off_date_time']) ? $params['drop_off_date_time'] : '';
		$min_price			= !empty($params['min_price'])		    ? $params['min_price']		   	: '';
		$max_price			= !empty($params['max_price'])		    ? $params['max_price']		   	: '';
		$types  			= !empty($params['types'])			    ? $params['types']			   	: '';
		$order_by_clause 	= '';
		$currency_rate 		= Application::Get('currency_rate');

        $car_where_clause = (!empty($car_id)) ? 'av.id = '.(int)$car_id.' AND ' : '';
        $car_where_clause .= (!empty($min_price) && !empty($currency_rate)) ? 'av.price_per_day >= '.round($min_price / $currency_rate).' AND ' : '';
        $car_where_clause .= (!empty($max_price) && !empty($currency_rate)) ? 'av.price_per_day <= '.round($max_price / $currency_rate).' AND ' : '';
        $car_where_clause .= (!empty($types)) ? 'vt.vehicle_category_id IN ('.implode(',', $types).') AND ' : '';
		
		// Step 1. Find all agencies related to selected locations: from/to
		// ----------------------------------------------
        $agencies = array();
		if(!empty($picking_up) && !empty($dropping_off)){
			if($picking_up == $dropping_off){
				$where = 'agency_location_id = '.(int)$picking_up;
				$select = '';
			}else{
				$where = 'agency_location_id IN ('.$picking_up.','.$dropping_off.')';
				$select = ', COUNT(*) as count_location';
			}
			$sql = 'SELECT
					agency_id,
                    agency_location_id
					'.$select.'
                FROM '.TABLE_CAR_AGENCY_LOCATIONS.'
                WHERE 
					'.$where.'
				GROUP BY agency_id';
            $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] > 0){
				if($picking_up == $dropping_off){
	                foreach($result[0] as $agency){
		                $agencies[] = $agency['agency_id'];
			        }
				}else{
	                foreach($result[0] as $agency){
						if($agency['count_location'] == 2){
			                $agencies[] = $agency['agency_id'];
						}
			        }
				}
            }
		}
		
		// Step 2. Create where string  for found agencies
		// ----------------------------------------------

		$agencies_sql = implode(',',$agencies);
		$car_where_clause .= (!empty($agencies) ? 'a.id IN ('.$agencies_sql.') AND ' : '');

		// Step 3. Select Cars from these agencies (array)
		// ----------------------------------------------
		$pick_up_date_unix = strtotime($pick_up_date_time);
		$drop_off_date_unix = strtotime($drop_off_date_time);
		$date_from = date('Y-m-d', $pick_up_date_unix);
		$time_from = date('H:i', $pick_up_date_unix);
		$date_to = date('Y-m-d', $drop_off_date_unix);
		$time_to = date('H:i', $drop_off_date_unix);

		$sql = 'SELECT
					car_vehicle_id, 
					car_agency_id
				FROM '.TABLE_CAR_AGENCY_RESERVATIONS.'
				WHERE
					'.(!empty($agencies_sql) ? 'car_agency_id IN ('.$agencies_sql.') AND ' : '').'
					(
						(date_from > \''.$date_from.'\' OR (date_from = \''.$date_from.'\' AND time_from >= \''.$time_from.'\')) AND
						(date_from < \''.$date_to.'\' OR (date_from = \''.$date_to.'\' AND time_from <= \''.$time_to.'\'))
					) OR (
						(date_to > \''.$date_from.'\' OR (date_to = \''.$date_from.'\' AND time_to >= \''.$time_from.'\')) AND
						(date_to < \''.$date_to.'\' OR (date_to = \''.$date_to.'\' AND time_to <= \''.$time_to.'\'))
					)
				   	AND status >= 1 AND status <= 3';

		$arr_car_reservations = array();
		$car_reservations = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if(!empty($car_reservations[1])){
			foreach($car_reservations[0] as $car){
				$arr_car_reservations[] = $car['car_vehicle_id'];
			}
		}

        $car_where_clause .= (!empty($arr_car_reservations)) ? 'av.id NOT IN ('.implode(',', $arr_car_reservations).') AND ' : '';
		
		// Step 4. Find cars reserved in theses dates!!!! And substruct reserved cars from found
		// ----------------------------------------------
		
        $sql = 'SELECT
            av.id,
            av.agency_id,
			av.make_id,
            av.price_per_day,
			av.price_per_hour,
			av.transmission,
            vt.image,
            vt.image_thumb,
            vt.passengers,
            vt.luggages,
			vt.doors		
        FROM '.TABLE_CAR_AGENCY_VEHICLES.' av
			INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON av.vehicle_type_id = vt.id
			INNER JOIN '.TABLE_CAR_AGENCIES.' a ON av.agency_id = a.id
        WHERE
            '.$car_where_clause.'
            av.is_active = 1 AND
			vt.is_active = 1 AND
			a.is_active = 1
        LIMIT 50';
		
        $cars = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        if(is_array($cars) && $cars[1] > 0){
            $arr_agencies = array();
			$min_price_per_day = 0;
            foreach($cars[0] as $car){
                if(!isset($arr_agencies[$car['agency_id']])){
                    $arr_agencies[$car['agency_id']] = 1;
                }
				if(!$min_price_per_day || $car['price_per_day'] < $min_price_per_day){
					$min_price_per_day = $car['price_per_day'];
				}
            }
            $cars_count = array('cars' => $cars[1], 'agencies' => count($arr_agencies), 'min_price_per_day' => $min_price_per_day);
        }else{
            $cars_count = array('cars' => 0, 'agencies' => 0, 'min_price_per_day' => 0);
        }
		
		return $cars_count;
	}

    /**
	 * Draws search result
	 * @param $params
	 * @param $rooms_total
	 */
	public function DrawSearchResult($params, $rooms_total = 0)
	{		
		global $objLogin;

		$nl = "\n";
		$output = '';

		$lang               = Application::Get('lang');
		$view_type          = !empty($params['view_type'])          ? $params['view_type']          : 'list';
		$car_id             = !empty($params['car_id'])             ? $params['car_id']             : '';
		$picking_up         = !empty($params['picking_up'])         ? $params['picking_up']         : '';
		$dropping_off       = !empty($params['dropping_off'])       ? $params['dropping_off']       : '';
		$pick_up_date_time  = !empty($params['pick_up_date_time'])  ? $params['pick_up_date_time']  : '';
		$drop_off_date_time = !empty($params['drop_off_date_time']) ? $params['drop_off_date_time'] : '';
		$min_price			= !empty($params['min_price'])		   ? $params['min_price']		   : '';
		$max_price			= !empty($params['max_price'])		   ? $params['max_price']		   : '';
		$make_sort			= !empty($params['make_sort'])		   ? $params['make_sort']		   : '';
		$price_sort			= !empty($params['price_sort'])		   ? $params['price_sort']		   : '';
		$types  			= !empty($params['types'])			   ? $params['types']			   : '';
		$pick_up_time		= !empty($pick_up_date_time)		   ? strtotime($pick_up_date_time) : '';
		$drop_off_time		= !empty($drop_off_date_time)		   ? strtotime($drop_off_date_time): '';
        $currency_rate      = Application::Get('currency_rate');
        $currency_format    = get_currency_format();


        $show_agency_in_search_result = ModulesSettings::Get('car_rental', 'show_agency_in_search_result') == 'yes' ? true : false;

		$diff_time = $drop_off_time - $pick_up_time;
		// 1800 = 30 min (30 * 60)
		if($diff_time < 1800){
			return '';
		}

		$hours_to_seconds = $diff_time % (24 * 60 * 60);
		$days = ($diff_time - $hours_to_seconds) / (24 * 60 * 60);
		$hours = $hours_to_seconds / 3600;

		$order_by_clause = '';

        $car_where_clause = (!empty($car_id)) ? 'av.id = '.(int)$car_id.' AND ' : '';
        $car_where_clause .= (!empty($min_price)) ? 'av.price_per_day >= '.round($min_price / $currency_rate).' AND ' : '';
        $car_where_clause .= (!empty($max_price)) ? 'av.price_per_day <= '.round($max_price / $currency_rate).' AND ' : '';
        $car_where_clause .= (!empty($types)) ? 'vt.vehicle_category_id IN ('.implode(',', $types).') AND ' : '';

		
		// Step 1. Find all agencies related to selected locations: from/to
		// ----------------------------------------------
        $agencies = array();
		if(!empty($picking_up) && !empty($dropping_off)){
			if($picking_up == $dropping_off){
				$where = 'agency_location_id = '.(int)$picking_up;
				$select = '';
			}else{
				$where = 'agency_location_id IN ('.$picking_up.','.$dropping_off.')';
				$select = ', COUNT(*) as count_location';
			}
			$sql = 'SELECT
					agency_id,
                    agency_location_id
					'.$select.'
                FROM '.TABLE_CAR_AGENCY_LOCATIONS.'
                WHERE 
					'.$where.'
				GROUP BY agency_id';
            $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] > 0){
				if($picking_up == $dropping_off){
	                foreach($result[0] as $agency){
		                $agencies[] = $agency['agency_id'];
			        }
				}else{
	                foreach($result[0] as $agency){
						if($agency['count_location'] == 2){
			                $agencies[] = $agency['agency_id'];
						}
			        }
				}
            }
		}

		
		// Step 2. Create where string  for found agencies
		// ----------------------------------------------

		$agencies_sql = implode(',',$agencies);
		$car_where_clause .= (!empty($agencies) ? 'a.id IN ('.$agencies_sql.') AND ' : '');

		// Step 3. Select Cars from these agancies (array)
		// ----------------------------------------------
		$pick_up_date_unix = strtotime($pick_up_date_time);
		$drop_off_date_unix = strtotime($drop_off_date_time);
		$date_from = date('Y-m-d', $pick_up_date_unix);
		$time_from = date('H:i', $pick_up_date_unix);
		$date_to = date('Y-m-d', $drop_off_date_unix);
		$time_to = date('H:i', $drop_off_date_unix);

		$sql = 'SELECT
					car_vehicle_id, 
					car_agency_id
				FROM '.TABLE_CAR_AGENCY_RESERVATIONS.'
				WHERE
					'.(!empty($agencies_sql) ? 'car_agency_id IN ('.$agencies_sql.') AND ' : '').'
					(
						(date_from > \''.$date_from.'\' OR (date_from = \''.$date_from.'\' AND time_from >= \''.$time_from.'\')) AND
						(date_from < \''.$date_to.'\' OR (date_from = \''.$date_to.'\' AND time_from <= \''.$time_to.'\'))
					) OR (
						(date_to > \''.$date_from.'\' OR (date_to = \''.$date_from.'\' AND time_to >= \''.$time_from.'\')) AND
						(date_to < \''.$date_to.'\' OR (date_to = \''.$date_to.'\' AND time_to <= \''.$time_to.'\'))
					)
				   	AND status >= 1 AND status <= 3';

		$arr_car_reservations = array();
		$car_reservations = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if(!empty($car_reservations[1])){
			foreach($car_reservations[0] as $car){
				$arr_car_reservations[] = $car['car_vehicle_id'];
			}
		}

        $car_where_clause .= (!empty($arr_car_reservations)) ? 'av.id NOT IN ('.implode(',', $arr_car_reservations).') AND ' : '';
		
		// Step 4. Find cars reserved in theses dates!!!! And substruct reserved cars from found
		// ----------------------------------------------
		
		$arr_car_order = array();
		if(!empty($make_sort) && in_array(strtolower($make_sort), array('asc', 'desc'))){
			$arr_car_order[] = 'md.name '.strtoupper($make_sort);
		}
		if(!empty($price_sort) && in_array(strtolower($price_sort), array('asc', 'desc'))){
			$arr_car_order[] = 'av.price_per_day '.strtoupper($price_sort);
		}
		$car_order = !empty($arr_car_order) ? ' ORDER BY '.implode(', ',$arr_car_order).' ' : '';

    	$sql = 'SELECT
				av.id, 
				av.agency_id, 
				av.make_id, 
				av.model, 
				av.image, 
				av.image_thumb, 
				av.transmission,
				av.price_per_day, 
				av.price_per_hour, 
				av.default_distance, 
				av.distance_extra_price, 
				vd.description as vehicle_description,
				vt.image as type_image,
				vt.image_thumb as type_image_thumb,
				vt.passengers, 
				vt.luggages, 
				vt.doors,
				md.name as make,
				vcd.name as vehicle_type_name, 
				vcd.description as vehicle_type_description
			FROM '.TABLE_CAR_AGENCY_VEHICLES.' av
				LEFT OUTER JOIN '.TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION.' vd ON av.id = vd.agency_vehicle_id AND vd.language_id = \''.$lang.'\'
				INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON av.vehicle_type_id = vt.id
				LEFT OUTER JOIN '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' vcd ON vt.vehicle_category_id = vcd.agency_vehicle_category_id AND vcd.language_id = \''.$lang.'\'
				LEFT OUTER JOIN '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' md ON av.make_id = md.make_id AND md.language_id = \''.$lang.'\'
				INNER JOIN '.TABLE_CAR_AGENCIES.' a ON av.agency_id = a.id
			WHERE
				'.$car_where_clause.'
				av.is_active = 1 AND
				vt.is_active = 1 AND
				a.is_active = 1
			'.$car_order.'
			LIMIT 50';
        $cars = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        if(is_array($cars) && $cars[1] > 0){
            $cars_count = $cars[1];
        }else{
            $cars_count = 0;
        }

		// prepare agencies array
		if($show_agency_in_search_result){
			$total_agencies = CarAgencies::GetAllActive();
			$arr_car_agencies = array();
			foreach($total_agencies[0] as $key => $val){
				$arr_car_agencies[$val['id']] = $val['name'];
			}
		}

		$output .= '<div class="itemscontainer offset-1">'.$nl;
		foreach($cars[0] as $car){
			$price = $days * $car['price_per_day'] + $hours * $car['price_per_hour'];
            $type_image = 'images/vehicle_types/'.(!empty($car['type_image']) ? $car['type_image'] : 'no_image.png');
			$image = 'images/vehicles/'.(!empty($car['image']) ? $car['image'] : 'no_image.png');

			if($view_type == 'grid'){
				$output .= '<div class="offcet-2">
					<div class="col-md-3 border" style="width:30%; margin-right:20px;margin-bottom:20px">
						<!-- CONTAINER-->
						<div class="carscontainer">
							<div class="center">
								<a href="'.prepare_link('cars', 'car_id', $car['id'], '', $car['make'].' '.$car['model'], '', '', true).'">
									<img style="width:180px;height:150px;" src="'.$image.'" alt="'.htmlentities($car['make'].' '.$car['model']).'">
								</a>
							</div>
							<div class="hpadding20">
								<!--span class="glyphicon glyphicon-info-sign right lblue cpointer" rel="popover" id="username" data-content="This field is mandatory" data-original-title="Here you can add additional information about the car"></span-->
					
								<span class="size14 bold dark">'.htmlentities($car['make'].' '.$car['model']).'</span><br>
								<span class="size13 bold grey mt-5">'.htmlentities($car['vehicle_type_name']).'</span><br>
								<!--span class="size13 grey">
									<span class="icn-air"></span>On airport<br>
									<span class="icn-gas"></span>Full to full<br>
									<span class="icn-gear"></span>Manual<br>
								</span-->
								<table class="table-bordered table-striped" style="font-size:10px;margin-left:-5px;">
									<thead>
										<tr>
											<td class="text-center">&nbsp;<i class="glyphicon glyphicon-user"></i> '._GUESTS.'&nbsp;</td>
											<td class="text-center">&nbsp;<i class="glyphicon glyphicon-print"></i> '._DOORS.'&nbsp;</td>
											<td class="text-center">&nbsp;<i class="glyphicon glyphicon-th"></i> '._GEAR.'&nbsp;</td>
											<td class="text-center">&nbsp;<i class="glyphicon glyphicon-briefcase"></i> '._BAGGAGE.'&nbsp;</td>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="text-center">'.$car['passengers'].'</td>
											<td class="text-center">'.$car['doors'].'</td>
											<td class="text-center">&nbsp;'.ucfirst($car['transmission']).'&nbsp;</td>
											<td class="text-center">x'.$car['luggages'].'</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="purchasecontainer">
								<span class="size18 bold green mt10">'.Currencies::PriceFormat($price * $currency_rate, '', '', $currency_format).'</span><br />
								<form action="index.php?page=book_now_car" id="cars-reservation-form" name="cars-reservation-form" method="post">
									'.draw_token_field(false).'
									'.draw_hidden_field('price', $price, false, 'price').'
									'.draw_hidden_field('act', 'add', false, 'act').'
									'.draw_hidden_field('car_id', $car['id'], false, 'car_id').'
									'.draw_hidden_field('picking_up_id', $picking_up, false, 'picking_up_id').'
									'.draw_hidden_field('dropping_off_id', $dropping_off, false, 'dropping_off_id').'
									'.draw_hidden_field('pick_up_date_time', $pick_up_date_time, false, 'pick_up_date_time').'
									'.draw_hidden_field('drop_off_date_time', $drop_off_date_time, false, 'drop_off_date_time').'
									<input type="submit" class="bookbtn right margtop-20" style="margin-top:3px;margin-bottom:15px;" value="'._BOOK_NOW.'"/>'.'
								</form>
							</div>
						</div>
						<!-- END OF CONTAINER-->
					</div>
				</div>';		
			}else{				
				$output .= '<div class="offset-2">
					<div class="col-lg-4 col-md-4 col-sm-4 offset-0 go-right">
						<!--<span><div data-original-title="Add to wishlist" title="" data-toggle="tooltip" data-placement="left" id="8" data-module="cars" class="wishlist wishlistcheck carswishtext8"><a class="tooltip_flip tooltip-effect-1" href="javascript:void(0);"><span class="carswishsign8">+</span></a></div></span>-->
						<div class="img_list">
							<a href="'.prepare_link('cars', 'car_id', $car['id'], '', $car['make'].' '.$car['model'], '', '', true).'">
								<img style="width:250px;height:200px;" src="'.$image.'" alt="'.htmlentities($car['make'].' '.$car['model']).'" />
								<!--div class="short_info"></div-->
							</a>
						</div>
					</div>
					<div class="col-md-8 offset-0">
						<div class="itemlabel3">
							<div class="labelright go-left" style="min-width:105px;margin-left:5px">
								<br />
								<span class="green size18" style="margin-left:25px;">
									<b>'.Currencies::PriceFormat($price * $currency_rate, '', '', $currency_format).'</b>
								</span>
							<div class="clearfix"></div>
							<hr />
							<a href="'.prepare_link('cars', 'car_id', $car['id'], '', $car['make'].' '.$car['model'], '', '', true).'" style="width:100%;display:block;text-align:center;"><button type="submit" class="bookbtn mt1">'._DETAILS.'</button></a><br />
								<form action="index.php?page=book_now_car" id="cars-reservation-form" name="cars-reservation-form" method="post">
									'.draw_token_field(false).'
									'.draw_hidden_field('price', $price, false, 'price').'
									'.draw_hidden_field('act', 'add', false, 'act').'
									'.draw_hidden_field('car_id', $car['id'], false, 'car_id').'
									'.draw_hidden_field('picking_up_id', $picking_up, false, 'picking_up_id').'
									'.draw_hidden_field('dropping_off_id', $dropping_off, false, 'dropping_off_id').'
									'.draw_hidden_field('pick_up_date_time', $pick_up_date_time, false, 'pick_up_date_time').'
									'.draw_hidden_field('drop_off_date_time', $drop_off_date_time, false, 'drop_off_date_time').'
									<input type="submit" class="form_button_middle" style="margin-top:3px;" value="'._BOOK_NOW.'"/>'.'
								</form>
							</div>
							<div class="labelleft2 rtl_title_home">
								<h4 class="mtb0 RTL go-text-right">
									<a href="'.prepare_link('cars', 'car_id', $car['id'], '', $car['make'].' '.$car['model'], '', '', true).'">
										<b>'.htmlentities($car['make'].' '.$car['model']).'</b>
									</a>
								</h4>
								'.htmlentities($car['vehicle_type_name']).'<br />
								'.($show_agency_in_search_result && isset($arr_car_agencies[$car['agency_id']]) ? $arr_car_agencies[$car['agency_id']] : '').'<br />
								<p class="grey RTL">'.substr_by_word(str_replace(array('<br>', '<br />'), ' ', $car['vehicle_description']), 150, true).'</p>
								<div class="visible-lg visible-md">
									<table class="table-bordered table-striped">
										<thead>
											<tr>
												<td class="text-center">&nbsp;<i class="glyphicon glyphicon-user"></i> '._GUESTS.'&nbsp;</td>
												<td class="text-center">&nbsp;<i class="glyphicon glyphicon-print"></i> '._DOORS.'&nbsp;</td>
												<td class="text-center">&nbsp;<i class="glyphicon glyphicon-th"></i> '._GEAR.'&nbsp;</td>
												<td class="text-center">&nbsp;<i class="glyphicon glyphicon-briefcase"></i> '._BAGGAGE.'&nbsp;</td>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="text-center">'.$car['passengers'].'</td>
												<td class="text-center">'.$car['doors'].'</td>
												<td class="text-center">&nbsp;'.ucfirst($car['transmission']).'&nbsp;</td>
												<td class="text-center">x'.$car['luggages'].'</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<hr style="margin-top: 10px; margin-bottom: 10px;" />
				</div>'.$nl;
			}			
        }
        $output .= '<div class="clearfix"></div>'.$nl;
        //$output .= '<!--div class="col-md-12 pull-right go-right">
        //        <ul class="pagination right paddingbtm20">
        //            <li><a href="#/1">«</a></li>
        //            <li><a href="#?">1</a></li>
        //            <li class="active"><a href="">2</a></li>
        //            <li><a href="#/3">3</a></li>
        //            <li><a href="#/3">»</a></li>
        //        </ul>
        //    </div-->'.$nl;
        $output .= '<div class="pull-right"></div>'.$nl;
        $output .= '<!-- End of offset1-->'.$nl;
        $output .= '</div>';
		
		return $output;
	}
	
    /**
     * Return infor for given agency
     * @param $agency_id
     * @param $payment_type
     */
    public static function GetPaymentInfo($agency_id = 0, $payment_type = '')
	{
		$sql = "SELECT * FROM ".TABLE_CAR_AGENCY_PAYMENT_GATEWAYS." WHERE agency_id = ".(int)$agency_id." AND payment_type = '".$payment_type."'";
        return database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
    }
	
}
