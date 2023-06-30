<?php

/***
 *	CustomerGroups Class
 *  ------------------ 
 *  Description : encapsulates customer groups properties
 *	Written by  : ApPHP
 *	Version     : 1.0.1
 *  Updated	    : 26.09.2012
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             GetAllGroups
 *	__destruct              GetAllGroupsByCustomers
 *	
 *	ChangeLog:
 *	---------
 *  1.0.1
 *  	- added 'maxlength' to textarea
 *  	- added
 *  	-
 *  	-
 *  	-
 *	
 **/


class CustomerGroups extends MicroGrid {
	
	protected $debug = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		global $objLogin;

		$this->params = array();
		
		## for standard fields
		if(isset($_POST['name']))   	 $this->params['name'] = prepare_input($_POST['name']);
		if(isset($_POST['description'])) $this->params['description'] = prepare_input($_POST['description']);
		
		## for checkboxes 
		//$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

		## for images (not necessary)
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = prepare_input($_POST['icon']);
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}elseif (self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		## for files:
		// define nothing

		//$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CUSTOMER_GROUPS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_customers_groups';

		$allow_adding = true;
		$allow_editing = true;
		$allow_deleting = true;				
		if($objLogin->IsLoggedInAs('hotelowner')){
			$allow_adding = $allow_editing = $allow_deleting = false;
		}	
		$this->actions      = array('add'=>$allow_adding, 'edit'=>$allow_editing, 'details'=>true, 'delete'=>$allow_deleting);

		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = ''; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|like%|%like|%like%', 'width'=>'130px'),
		);

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
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A' + IF(date_created IS NULL, '', date_created) as date_created,
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									name,
									description
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name' 		  => array('title'=>_GROUP_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'140px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>true, 'maxlength'=>'70', 'format'=>'', 'format_parameter'=>''),
			'id'   		  => array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'name'        => array('title'=>_GROUP_NAME, 'type'=>'textbox',  'width'=>'210px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'required'=>true, 'validation_type'=>'', 'unique'=>true, 'visible'=>true),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'310px', 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'maxlength'=>'255', 'validation_maxlength'=>'255', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
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
			'name'        => array('title'=>_GROUP_NAME, 'type'=>'textbox',  'width'=>'210px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'required'=>true, 'validation_type'=>'', 'unique'=>true, 'visible'=>true),
			'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'310px', 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'maxlength'=>'255', 'validation_maxlength'=>'255', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'name'        => array('title'=>_GROUP_NAME, 'type'=>'label'),
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


	//==========================================================================
    // Static Methods
	//==========================================================================	
	/**
	 *	Get all groups
	 */
	public static function GetAllGroups()
	{
		$sql = 'SELECT id, name, description FROM '.TABLE_CUSTOMER_GROUPS.' ORDER BY name ASC';					
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 *	Get all groups by customers
	 */
	public static function GetAllGroupsByCustomers()
	{
		$sql = 'SELECT cg.id, cg.name, cg.description,
					(SELECT COUNT(*) FROM '.TABLE_CUSTOMERS.' c WHERE c.group_id = cg.id AND c.is_active = 1 AND c.email_notifications = 1 AND c.email != \'\') as customers_count
				FROM '.TABLE_CUSTOMER_GROUPS.' cg				
				ORDER BY cg.name ASC';				
		return database_query($sql, DATA_AND_ROWS);
	}

}
