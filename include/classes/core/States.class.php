<?php

/**
 * 	Class images 
 *  -------------- 
 *  Description : encapsulates states properties
 *	Written by  : ApPHP
 *	Version     : 1.0.1
 *  Updated	    : 04.11.2013
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             GetAllActive                        
 *	__destruct
 *
 *  1.0.1
 *      - 
 *      - 
 *      - 
 *      - 
 *      -      
 *
 *	
 **/


class States extends MicroGrid {
	
	protected $debug = false;

    //---------------------------	
	private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	// 	@param $hotel_id
	//==========================================================================
	function __construct($country_id = 0)
	{		
		parent::__construct();
		
		global $objLogin;

		$this->params = array();		
		if(isset($_POST['country_id'])) $this->params['country_id'] = prepare_input($_POST['country_id']);
		if(isset($_POST['abbrv'])) $this->params['abbrv'] = prepare_input($_POST['abbrv']);
        if(isset($_POST['name'])) $this->params['name'] = prepare_input($_POST['name']);
		if(isset($_POST['is_active']))      $this->params['is_active'] = (int)$_POST['is_active'];
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = (int)$_POST['priority_order'];

		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_STATES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=states_management&cid='.(int)$country_id;
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;

		$this->allowLanguages = false;
		$this->languageId  	=  '';//$objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = 'WHERE country_id = \''.$country_id.'\'';		
		$this->ORDER_CLAUSE = 'ORDER BY priority_order ASC'; // ORDER BY date_created DESC

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 100;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			//'parameter1' => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
			//'parameter2'  => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
		);

		$arr_activity_types_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_activity_types = array('0'=>_NO, '1'=>_YES);				

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.country_id,
									'.$this->tableName.'.abbrv,
									'.$this->tableName.'.name,
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.priority_order
								FROM '.$this->tableName;
								
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'  		 => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
			'abbrv'  		 => array('title'=>_ABBREVIATION, 'type'=>'label', 'align'=>'center', 'width'=>'150px', 'height'=>'', 'maxlength'=>''),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_activity_types_vm),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'height'=>'', 'movable'=>true, 'maxlength'=>''),
		);

		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'name'  		 => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>'text'),
			'abbrv'  		 => array('title'=>_ABBREVIATION, 'type'=>'textbox', 'width'=>'45px', 'required'=>true, 'readonly'=>false, 'unique'=>false, 'maxlength'=>'3', 'default'=>'', 'validation_type'=>'alpha'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_activity_types, 'unique'=>false, 'javascript_event'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric'),
			'country_id'     => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$country_id),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->primaryKey.',
								abbrv,
								name,
								is_active,
								priority_order
							FROM '.$this->tableName.'
							WHERE '.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'name'  		 => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>'text'),
			'abbrv'  		 => array('title'=>_ABBREVIATION, 'type'=>'textbox', 'width'=>'45px', 'required'=>true, 'readonly'=>false, 'unique'=>false, 'maxlength'=>'3', 'default'=>'', 'validation_type'=>'alpha'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_activity_types, 'unique'=>false, 'javascript_event'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'0', 'validation_type'=>'numeric'),
			//'country_id'     => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$country_id),
		);

		
		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'name'  	=> array('title'=>_NAME, 'type'=>'label'),
			'abbrv'  	=> array('title'=>_ABBREVIATION, 'type'=>'label'),
			'is_active'  => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_activity_types_vm),
			'priority_order'  => array('title'=>_ORDER, 'type'=>'label'),
		);
		
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }
	
	////////////////////////////////////////////////////////////////////
	// BEFORE/AFTER METHODS
	///////////////////////////////////////////////////////////////////
	
	/**
	 * Returns all array of all active states
	 * @param $where_clause
	 */
	public static function GetAllActive($where_clause = '')
	{		
		$sql = 'SELECT
					'.TABLE_STATES.'.*
				FROM '.TABLE_STATES.' 
					INNER JOIN '.TABLE_COUNTRIES.' ON '.TABLE_STATES.'.country_id = '.TABLE_COUNTRIES.'.id
				WHERE
					'.TABLE_STATES.'.is_active = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.TABLE_STATES.'.priority_order ASC ';	
		return database_query($sql, DATA_AND_ROWS);
	}
	
}
