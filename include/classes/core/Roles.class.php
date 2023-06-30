<?php

/**
 *	Class Roles
 *  --------------
 *	Description : encapsulates roles methods and properties
 *	Written by  : ApPHP
 *	Version     : 1.0.1
 *  Updated	    : 01.10.2012
 *  Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct             GetPrivileges
 *	__destruct
 *	
 *  1.0.1
 *      - added GetPrivileges()
 *      - added maxlength for textareas
 *      -
 *      -
 *      -
 *	
 **/


class Roles extends MicroGrid {
	
	protected $debug = false;	

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['name']))        $this->params['name'] = prepare_input($_POST['name']);
		if(isset($_POST['description'])) $this->params['description'] = prepare_input($_POST['description']);
		
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

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_ROLES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=roles_management';
		$this->actions      = array('add'=>false, 'edit'=>true, 'details'=>true, 'delete'=>false);
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
		$this->arrFilteringFields = array();

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
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									name,
									description,
									"[ '._PRIVILEGES.' ]" as link_privileges
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'            => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'20%', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'32', 'format'=>'', 'format_parameter'=>''),
			'description'     => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'100', 'format'=>'', 'format_parameter'=>''),
			'link_privileges' => array('title'=>'', 'type'=>'link',  'align'=>'left', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'href'=>'index.php?admin=role_privileges_management&role_id={id}', 'target'=>''),
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
			'name'        => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'text', 'unique'=>true, 'visible'=>true),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'maxlength'=>'255', 'validation_maxlength'=>'255', 'unique'=>false),
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
								'.$this->tableName.'.name,
								'.$this->tableName.'.description
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'name'        => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'text', 'unique'=>true, 'visible'=>true),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'maxlength'=>'255', 'validation_maxlength'=>'255', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'name'        => array('title'=>_NAME, 'type'=>'label'),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'label'),
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
	 * Get privileges according to user type
	 * @param $user_role
     */
	public static function GetPrivileges($user_role = '')
	{
	    $sql = 'SELECT
				p.code,
				p.name,
				p.description,
				rp.is_active
			FROM '.TABLE_ROLE_PRIVILEGES.' rp
				INNER JOIN '.TABLE_ROLES.' r ON rp.role_id = r.id
				INNER JOIN '.TABLE_PRIVILEGES.' p ON rp.privilege_id = p.id
			WHERE
				r.code = \''.$user_role.'\'';		
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result;			
	}
	
}
