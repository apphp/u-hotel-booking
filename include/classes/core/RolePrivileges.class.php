<?php

/**
 *	Class RolePrivileges
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *	Version     : 1.0.1
 *  Updated	    : 20.06.2012
 *  Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct
 *	__destruct
 *	
 *  1.0.1
 *      - 
 *      - 
 *      -
 *      -
 *      -
 *	
 **/


class RolePrivileges extends MicroGrid {
	
	protected $debug = false;
	
	//==========================================================================
    // Class Constructor
	//		@param $role_id
	//==========================================================================
	function __construct($role_id = 0)
	{		
		parent::__construct();
		
		$this->params = array();
		
		## for standard fields
		//if(isset($_POST['name']))        $this->params['name'] = prepare_input($_POST['name']);
		//if(isset($_POST['description'])) $this->params['description'] = prepare_input($_POST['description']);
		
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

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_ROLE_PRIVILEGES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=role_privileges_management&role_id='.(int)$role_id;
		$this->actions      = array('add'=>false, 'edit'=>(($role_id > 1) ? true : false), 'details'=>true, 'delete'=>false);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = ''; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);
		
		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
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

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									rp.'.$this->primaryKey.',
									rp.is_active,
									p.name,
									p.description
								FROM '.$this->tableName.' rp
									INNER JOIN '.TABLE_ROLES.' r ON rp.role_id = r.id
									INNER JOIN '.TABLE_PRIVILEGES.' p ON rp.privilege_id = p.id
								WHERE
									rp.role_id = '.$role_id;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'        => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'20%', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'32', 'format'=>'', 'format_parameter'=>''),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'100', 'format'=>'', 'format_parameter'=>''),
			'is_active'   => array('title'=>_ALLOW, 'type'=>'enum',  'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
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
								rp.'.$this->primaryKey.',
								rp.is_active,
								p.name,
								p.description
							FROM '.$this->tableName.' rp
								INNER JOIN '.TABLE_ROLES.' r ON rp.role_id = r.id
								INNER JOIN '.TABLE_PRIVILEGES.' p ON rp.privilege_id = p.id
							WHERE rp.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'name'        => array('title'=>_NAME, 'type'=>'label'),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'label'),
			'is_active'   => array('title'=>_ALLOW, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'name'        => array('title'=>_NAME, 'type'=>'label'),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'label'),
			'is_active'   => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);

	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

}
