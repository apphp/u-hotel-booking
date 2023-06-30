<?php

/**
 *	BanList Class
 *  -------------- 
 *  Description : encapsulates ban list properties
 *	Written by  : ApPHP
 *	Version     : 1.1.9
 *  Updated	    : 09.09.2012
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct
 *	__destruct
 *
 *  1.1.9
 *      - added maxlength validation to 'Reason' field
 *      - changed " with '
 *      - added 'maxlength' to textareas
 *      -
 *      -      
 *	
 **/


class BanList extends MicroGrid {
	
	protected $debug = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['ban_item']))      $this->params['ban_item'] = prepare_input($_POST['ban_item']);
		if(isset($_POST['ban_reason']))    $this->params['ban_reason'] = prepare_input($_POST['ban_reason']);
		
		$item_validation_type = '';
		if(isset($_POST['ban_item_type'])){
			$this->params['ban_item_type'] = prepare_input($_POST['ban_item_type']);
			if($this->params['ban_item_type'] == 'IP'){
				$item_validation_type = 'ip_address';
			}elseif($this->params['ban_item_type'] == 'Email'){
				$item_validation_type = 'email';
			}
		}
		
		## for checkboxes 
		//if(isset($_POST['parameter4']))   $this->params['parameter4'] = $_POST['parameter4']; else $this->params['parameter4'] = '0';

		## for images
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = $_POST['icon'];
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}elseif(self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_BANLIST;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=ban_list';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = ''; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;
		$arr_ban_types = array('IP'=>_IP_ADDRESS, 'Email'=>_EMAIL_ADDRESS);
		// define filtering fields
		$this->arrFilteringFields = array(
			_TYPE  => array('table'=>$this->tableName, 'field'=>'ban_item_type', 'type'=>'dropdownlist', 'source'=>$arr_ban_types, 'sign'=>'=', 'width'=>'130px'),
		);

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									ban_item,
									CASE
										WHEN ban_item_type = \'IP\' THEN \''._IP_ADDRESS.'\'
										WHEN ban_item_type = \'Email\' THEN \''._EMAIL_ADDRESS.'\'
										ELSE \''._UNKNOWN.'\'
									END ban_item_type,
									ban_reason
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'ban_item'      => array('title'=>_BAN_ITEM, 'type'=>'label',  'align'=>'left', 'width'=>'170px', 'height'=>'', 'maxlength'=>''),
			'ban_item_type' => array('title'=>_TYPE, 'type'=>'label', 'align'=>'left', 'width'=>'150px', 'height'=>'', 'maxlength'=>''),
			'ban_reason'    => array('title'=>_REASON, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'ban_item'      => array('title'=>_BAN_ITEM, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'unique'=>true, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>$item_validation_type),
			'ban_item_type' => array('title'=>_TYPE, 'type'=>'enum',     'required'=>true, 'readonly'=>false, 'width'=>'130px', 'source'=>$arr_ban_types),
			'ban_reason'    => array('title'=>_REASON, 'type'=>'textarea', 'width'=>'310px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'255', 'validation_maxlength'=>'255', 'readonly'=>false, 'default'=>'Spam from this IP/Email', 'validation_type'=>''),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.ban_item,
								'.$this->tableName.'.ban_item_type,
								'.$this->tableName.'.ban_reason
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'ban_item'      => array('title'=>_BAN_ITEM, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'unique'=>true, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>$item_validation_type),
			'ban_item_type' => array('title'=>_TYPE, 'type'=>'enum',     'required'=>true, 'readonly'=>false, 'width'=>'130px', 'source'=>$arr_ban_types),
			'ban_reason'    => array('title'=>_REASON, 'type'=>'textarea', 'width'=>'310px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'255', 'validation_maxlength'=>'255', 'readonly'=>false, 'default'=>'Spam from this IP/Email', 'validation_type'=>''),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'ban_item'  	=> array('title'=>_BAN_ITEM, 'type'=>'label'),
			'ban_item_type' => array('title'=>_TYPE, 'type'=>'label'),
			'ban_reason'    => array('title'=>_REASON, 'type'=>'label'),
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
