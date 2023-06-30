<?php

/**
 *	RoomsDescription
 *  -------------- 
 *	Written by  : ApPHP
 *  Updated	    : 24.11.2010
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct										
 *  __destruct
 *	
 **/


class RoomsDescription extends MicroGrid {
	
	protected $debug = false;	
	private $regionalManager = false;

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		global $objLogin;
		$this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager');
        $this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner');

		$this->params = array();		
		if(isset($_POST['room_type'])) $this->params['room_type'] = prepare_input($_POST['room_type']);
		if(isset($_POST['room_short_description'])) $this->params['room_short_description'] = prepare_input($_POST['room_short_description'], false, 'medium');
		if(isset($_POST['room_long_description'])) $this->params['room_long_description'] = prepare_input($_POST['room_long_description'], false, 'medium');

		$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : '0';
		
		//$default_lang = Languages::GetDefaultLang();
		//$default_currency = Currencies::GetDefaultCurrency();	
		
		// for checkboxes
		/// if(isset($_POST['parameter4']))   $this->params['parameter4'] = $_POST['parameter4']; else $this->params['parameter4'] = '0';
		
		//$this->params['language_id'] 	  = MicroGrid::GetParameter('language_id');

        $editHotelRooms     = ($this->hotelOwner && !$objLogin->HasPrivileges('edit_hotel_rooms')) ? false : true;
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_ROOMS_DESCRIPTION;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_room_description&room_id='.$room_id;
		$this->actions      = array('add'=>false, 'edit'=>$editHotelRooms, 'details'=>true, 'delete'=>false);
		$this->actionIcons  = true;
		
		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		
		$this->WHERE_CLAUSE = 'WHERE '.$this->tableName.'.room_id  = \''.$room_id.'\'';	
		if($objLogin->IsLoggedInAs('hotelowner')){
			$hotels = $objLogin->AssignedToHotels();
			$hotels_list = implode(',', $hotels);
			if(!empty($hotels_list)) $this->WHERE_CLAUSE .= ' AND '.TABLE_ROOMS.'.hotel_id IN ('.$hotels_list.')';
		}else{
			$hotels = AccountLocations::GetHotels($objLogin->GetLoggedID());
			$hotels_list = implode(',', $hotels);
			if(!empty($hotels_list)) $this->WHERE_CLAUSE .= 'AND '.TABLE_ROOMS.'.hotel_id IN ('.$hotels_list.')';
		}
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.id ASC';
		
		$this->isAlterColorsAllowed = true;
        
		$this->isPagingAllowed = false;
		$this->pageSize = 100;
        
		$this->isSortingAllowed = true;
        
		$this->isFilteringAllowed = false;
		// define filtering fields
		// $this->arrFilteringFields = array();
		
		// prepare languages array
		//$total_languages = Languages::GetAllActive();
		//$arr_languages      = array();
		//foreach($total_languages[0] as $key => $val){
		//	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		//}
		

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.room_id,
									'.$this->tableName.'.language_id,
									'.$this->tableName.'.room_type,									
									'.$this->tableName.'.room_short_description,
									'.$this->tableName.'.room_long_description, 
									'.TABLE_LANGUAGES.'.lang_name  
								FROM '.$this->tableName.'
									INNER JOIN '.TABLE_ROOMS.' ON '.$this->tableName.'.room_id = '.TABLE_ROOMS.'.id
									INNER JOIN '.TABLE_LANGUAGES.' ON '.$this->tableName.'.language_id = '.TABLE_LANGUAGES.'.abbreviation AND '.TABLE_LANGUAGES.'.is_active = 1
								';

		// define view mode fields
		$this->arrViewModeFields = array(
			'room_type'  	   => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'120px', 'maxlength'=>''),
			'room_short_description' => array('title'=>_SHORT_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'100', 'format'=>'strip_tags'),
			'lang_name'    	   => array('title'=>_LANGUAGE, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'maxlength'=>''),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
		
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.room_id,
									'.$this->tableName.'.language_id,
									'.$this->tableName.'.room_type,
									'.$this->tableName.'.room_short_description,
									'.$this->tableName.'.room_long_description,
									'.TABLE_LANGUAGES.'.lang_name  
								FROM '.$this->tableName.'
									INNER JOIN '.TABLE_ROOMS.' ON '.$this->tableName.'.room_id = '.TABLE_ROOMS.'.id
									INNER JOIN '.TABLE_LANGUAGES.' ON '.$this->tableName.'.language_id = '.TABLE_LANGUAGES.'.abbreviation AND '.TABLE_LANGUAGES.'.is_active = 1
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		

		// define edit mode fields
		$this->arrEditModeFields = array(
			'lang_name'              => array('title'=>_LANGUAGE, 'type'=>'label'),
			'room_type' 	         => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'maxlength'=>'70'),
			'room_short_description' => array('title'=>_SHORT_DESCRIPTION, 'type'=>'textarea', 'editor_type'=>'wysiwyg', 'width'=>'470px', 'height'=>'80px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'validation_maxlength'=>'512'),
			'room_long_description'  => array('title'=>_LONG_DESCRIPTION, 'type'=>'textarea', 'editor_type'=>'wysiwyg', 'width'=>'470px', 'height'=>'240px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'validation_maxlength'=>'4096'),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'lang_name'        		 => array('title'=>_LANGUAGE, 'type'=>'label'),
			'room_type'  			 => array('title'=>_NAME, 'type'=>'label'),
			'room_short_description' => array('title'=>_SHORT_DESCRIPTION, 'type'=>'label'),
			'room_long_description'  => array('title'=>_LONG_DESCRIPTION, 'type'=>'label'),
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
