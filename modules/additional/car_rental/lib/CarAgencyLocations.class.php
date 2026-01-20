<?php

/**
 *	Class CarAgencyLocations
 *  --------------
 *	Description : encapsulates agency locations methods and properties
 *	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Updated	    : 03.04.2016
 *  Usage       : HotelBooking
 *
 *	PUBLIC				  	STATIC				 		PRIVATE
 * 	------------------	  	---------------     		---------------
 *	__construct             GetAgencyLocations
 *	__destruct               
 *	
 **/


class CarAgencyLocations extends MicroGrid {
	
	protected $debug = false;
	
	//-----------------------------------------
	private $agency_id = '';

	//==========================================================================
    // Class Constructor
	//		@param $agency_id
	//      @param $account_type
	//==========================================================================
	function __construct($agency_id = 0, $account_type = '')
	{		
		parent::__construct();
		
		global $objLogin; 
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['agency_location_id'])) $this->params['agency_location_id'] = prepare_input($_POST['agency_location_id']);
		if(isset($_POST['agency_id'])) $this->params['agency_id'] = prepare_input($_POST['agency_id']);
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		
		## for checkboxes 
		if(isset($_POST['is_active'])) $this->params['is_active'] = (int)$_POST['is_active']; else $this->params['is_active'] = '0';

		$this->agency_id = (int)$agency_id;
		
		//$this->params['language_id'] = MicroGrid::GetParameter('language_id');	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CAR_AGENCY_LOCATIONS;
		$this->dataSet 		= array();
		$this->error 		= '';
		if($account_type == 'me'){
			$this->formActionURL = 'index.php';	
		}else{
			$this->formActionURL = 'index.php?admin=mod_car_rental_agency_locations&agency_id='.(int)$agency_id;
		}
		
		// prepare locations array
		$total_locations = CarAgenciesLocations::GetAgenciesLocations('agl.id NOT IN (SELECT agency_location_id FROM '.$this->tableName.' WHERE agency_id = '.(int)$agency_id.')');
		$arr_locations = array();
		foreach($total_locations[0] as $key => $val){
		 	$arr_locations[$val['id']] = $val['name'];
		}

        $this->actions = array();
        if($objLogin->IsLoggedInAs('owner','mainadmin')){
            $this->actions = array(
                'add'		=> count($arr_locations) > 0 ? true : false,
                'edit'		=> (($agency_id > 0) ? true : false),
                'details'	=> true,
                'delete'	=> true
            );            
        }elseif($objLogin->IsLoggedInAs('agencyowner')){
            $this->actions = array(
                'add'		=> true,
                'edit'		=> true,
                'details'	=> true,
                'delete'	=> true
            );
        }
        
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = ''.$this->languageId.''';				
        $this->ORDER_CLAUSE = 'ORDER BY al.priority_order ASC';
		
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
		$pre_currency_symbol = ((Application::Get('currency_symbol_place') == 'before') ? Application::Get('currency_symbol') : '');
		$post_currency_symbol = ((Application::Get('currency_symbol_place') == 'after') ? Application::Get('currency_symbol') : '');

		$arr_is_active = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		
		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A''
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									al.'.$this->primaryKey.',
                                    al.agency_id,
									al.agency_location_id,
                                    al.priority_order,
									al.is_active,
									ld.name
								FROM '.$this->tableName.' al
									INNER JOIN '.TABLE_CAR_AGENCIES.' ca ON al.agency_id = ca.id
									INNER JOIN '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' ld ON al.agency_location_id = ld.agency_location_id AND ld.language_id = \''.$this->languageId.'\'
								WHERE
									al.agency_id = '.$agency_id;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'           => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'40', 'format'=>'', 'format_parameter'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
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
			'agency_location_id' => array('title'=>_LOCATION, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_locations, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
			'agency_id'     	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$agency_id),										
			'priority_order'	=> array('title'=>_ORDER, 'type'=>'textbox', 'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'    		=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0'),
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
								al.'.$this->primaryKey.',
								al.agency_id,
                                al.priority_order,
								al.is_active,
								al.agency_location_id,
								ld.name as location_name
							FROM '.$this->tableName.' al
								INNER JOIN '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' ld ON al.agency_location_id = ld.agency_location_id AND ld.language_id = \''.$this->languageId.'\'
							WHERE al.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'location_name'		=> array('title'=>_LOCATION, 'type'=>'label'),
			'agency_id'      	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$agency_id),										
			'priority_order' 	=> array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_active'     	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0'),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'location_name'     => array('title'=>_LOCATION, 'type'=>'label'),
			'priority_order'    => array('title'=>_ORDER, 'type'=>'label'),
			'is_active'     	=> array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
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
    // MicroGrid Methods
	//==========================================================================	
	/**
	 * Returns all specialities of a specific agency
	 * @param $rid
	 * @param $agency_id
	 */
	public static function GetAgencyLocations($rid = 0, $agency_id = 0)
	{
		$output = array();		
		$sql = 'SELECT
					al.id,
					al.agency_id,
					ld.name
				FROM '.TABLE_CAR_AGENCY_LOCATIONS.' al
					INNER JOIN '.TABLE_CAR_AGENCIES.' d ON al.agency_id = d.id
					INNER JOIN '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' ld ON al.agency_location_id = ld.agency_location_id AND ld.language_id = \''.Application::Get('lang').'\' 
				WHERE
					al.id = '.(int)$rid.'
					'.(!empty($agency_id) ? ' AND al.agency_id = '.(int)$agency_id : '').'
                ORDER BY
					al.priority_order ASC';		

		if($result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY)){
			$output = $result;
		}
		
		return $output;		
	}

	
}

