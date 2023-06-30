<?php

/**
 * 	Class HotelImages
 *  -------------- 
 *  Description : encapsulates hotel images properties
 *	Written by  : ApPHP
 *	Version     : 1.0.5
 *  Updated	    : 24.09.2012
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct                                     
 *	__destruct
 *
 *	
 **/


class HotelImages extends MicroGrid {
	
	protected $debug = false;

    //---------------------------	
	private $arrTranslations = '';
	private $regionalManager = false;
	
	
	//==========================================================================
    // Class Constructor
	// 	@param $hotel_id
	//==========================================================================
	function __construct($hotel_id = 0)
	{		
		parent::__construct();
		
		global $objLogin;
		
		$this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager');

		$this->params = array();		
		if(isset($_POST['hotel_id'])) $this->params['hotel_id'] = prepare_input($_POST['hotel_id']);
		if(isset($_POST['image_title'])) $this->params['image_title'] = prepare_input($_POST['image_title']);
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))  $this->params['is_active'] = prepare_input($_POST['is_active']); else $this->params['is_active'] = '0';

		$icon_width  = '120px';
		$icon_height = '90px';
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_HOTEL_IMAGES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=hotel_upload_images&hid='.(int)$hotel_id;
		$this->actions      = array(
				'add'		=> $this->regionalManager ? false : true,
				'edit'		=> $this->regionalManager ? false : true,
				'details'	=> true,
				'delete'	=> $this->regionalManager ? false : true
		);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;

		$this->allowLanguages = false;
		$this->languageId  	=  '';//$objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = 'WHERE hotel_id = \''.$hotel_id.'\'';		
		$this->ORDER_CLAUSE = 'ORDER BY priority_order ASC'; // ORDER BY date_created DESC

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			//'parameter1' => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
			//'parameter2'  => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
		);

		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.hotel_id,
									'.$this->tableName.'.item_file,
									'.$this->tableName.'.item_file_thumb,
									'.$this->tableName.'.image_title,
									'.$this->tableName.'.priority_order,
									'.$this->tableName.'.is_active
								FROM '.$this->tableName;
								
		// define view mode fields
		$this->arrViewModeFields['item_file_thumb'] = array('title'=>_IMAGE, 'type'=>'image', 'align'=>'left', 'width'=>'90px', 'sortable'=>false, 'nowrap'=>'', 'visible'=>'', 'image_width'=>'50px', 'image_height'=>'30px', 'target'=>'images/hotels/', 'no_image'=>'no_image.png');
		$this->arrViewModeFields['image_title'] 	= array('title'=>_TEXT, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'70', 'movable'=>false);
		$this->arrViewModeFields['priority_order'] 	= array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'movable'=>true);
		$this->arrViewModeFields['is_active']      	= array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active);

		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'item_file'      => array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'images/hotels/', 'random_name'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'item_file_thumb', 'thumbnail_width'=>$icon_width, 'thumbnail_height'=>$icon_height, 'file_maxsize'=>'900k'),
			'image_title'    => array('title'=>_TEXT, 'type'=>'textarea', 'width'=>'410px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'255', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false),
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric', 'default'=>'0'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0'),
			'hotel_id'       => array('title'=>'', 'type'=>'hidden',   'required'=>true, 'readonly'=>false, 'default'=>$hotel_id),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->primaryKey.',
								hotel_id,
								item_file,
								item_file_thumb,
								image_title,
								priority_order,
								is_active
							FROM '.$this->tableName.'
							WHERE '.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'item_file'      => array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'images/hotels/', 'random_name'=>true, 'thumbnail_create'=>true, 'thumbnail_field'=>'item_file_thumb', 'thumbnail_width'=>$icon_width, 'thumbnail_height'=>$icon_height, 'file_maxsize'=>'900k'),
			'image_title'    => array('title'=>_TEXT, 'type'=>'textarea', 'width'=>'410px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'255', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false),
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'true_value'=>'1', 'false_value'=>'0'),
			'hotel_id'       => array('title'=>'', 'type'=>'hidden',   'required'=>true, 'readonly'=>false, 'default'=>$hotel_id),
		);

		
		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'item_file'      => array('title'=>_IMAGE, 'type'=>'image', 'target'=>'images/hotels/', 'no_image'=>'no_image.png'),
			'image_title'    => array('title'=>_TEXT, 'type'=>'label'),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
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
	
}
