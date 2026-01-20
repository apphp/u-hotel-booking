<?php

/**
 *	Class ChannelHotels (for uHotelBooking ONLY)
 *  -------------- 
 *  Description : 
 *  Updated	    : 10.12.2016
 *	Written by  : ApPHP
 *	
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetHotelData	
 *  __destruct
 *  BeforeAddRecord
 *  BeforeInsertRecord
 *  BeforeUpdateRecord
 *  
 **/

class ChannelHotels extends MicroGrid {
	
	protected $debug = false;
	
	protected $arr_channels = array();
	
	protected static $CHANNEL_ID = 1; 

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($hotel_id = 0)
	{
		parent::__construct();
		
		$hotel_id = !empty($hotel_id) ? (int)$hotel_id : 0;		
		
		$this->params = array();
		if(isset($_POST['channel_id']))  	$this->params['channel_id']     = prepare_input($_POST['channel_id']);
		if(isset($_POST['hotel_id']))  	 	$this->params['hotel_id']     	= $hotel_id;
		if(isset($_POST['cm_auth_key'])) 	$this->params['cm_auth_key']  	= prepare_input($_POST['cm_auth_key']);
		if(isset($_POST['cm_api_key'])) 	$this->params['cm_api_key']     = prepare_input($_POST['cm_api_key']);
		if(isset($_POST['cm_hotel_id'])) 	$this->params['cm_hotel_id']    = prepare_input($_POST['cm_hotel_id']);
		## for checkboxes 
		$this->params['cm_enabled']         = isset($_POST['cm_enabled']) ? prepare_input($_POST['cm_enabled']) : '0';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= DB_PREFIX.'channel_hotels';
		$this->tableChannelsName = DB_PREFIX.'channels';
		$this->dataSet 		= array();
		$this->error 		= '';
		///$this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();
		
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowPrint   = true;
		$this->allowTopButtons = false;
		
		$this->allowLanguages = false;
		$this->WHERE_CLAUSE = 'WHERE hotel_id = '.$hotel_id;		
		$this->ORDER_CLAUSE = 'ORDER BY id DESC';
		
		$this->isAlterColorsAllowed = true;
		
		$this->isPagingAllowed = true;
		$this->pageSize = 20;
		
		$this->isSortingAllowed = true;
		
		$total_channels = ChannelManager::GetAllChannels('id NOT IN (SELECT channel_id FROM '.$this->tableName.' WHERE hotel_id = '.(int)$hotel_id.')');
		foreach($total_channels[0] as $key => $val){
			$this->arr_channels[$val['id']] = $val['name'];
		}
		
		$this->isFilteringAllowed = true;
		$this->arrFilteringFields = array(
			_CHANNEL  	=> array('table'=>$this->tableChannelsName, 'field'=>'name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'90px'),
		);
		
		//$datetime_format = get_datetime_format();		
		//$date_format_view = get_date_format('view');
		//$date_format_edit = get_date_format('edit');
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		
		//if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
		//	$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
		//	$this->sqlFieldDateFormat = '%b %d, %Y';
		//}else{
		//	$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
		//	$this->sqlFieldDateFormat = '%d %b, %Y';
		//}
		//$this->SetLocale(Application::Get('lc_time_name'));
		
		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
		                            '.$this->tableName.'.*,
									'.$this->tableChannelsName.'.name,
									CONCAT("<a href=index.php?admin=mod_channel_manager_rooms&hid=", '.$this->tableName.'.hotel_id, "&chid=", '.$this->tableName.'.channel_id, ">[ '._ROOMS.' ]</a>") as link_channel_manager
								FROM '.$this->tableName.'
								INNER JOIN '.$this->tableChannelsName.' ON '.$this->tableName.'.channel_id = '.$this->tableChannelsName.'.id';
								
		// define view mode fields
		$this->arrViewModeFields = array(			
			'name'    		=> array('title'=>_CHANNEL_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'210px', 'maxlength'=>'25'),
			'cm_auth_key'   => array('title'=>_AUTH_KEY, 'type'=>'label', 'align'=>'left', 'width'=>'210px', 'maxlength'=>''),
			'cm_api_key'   	=> array('title'=>_API_KEY, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>''),
			'cm_hotel_id'   => array('title'=>(defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).' ID', 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'maxlength'=>''),
			'cm_enabled'    => array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			'link_channel_manager' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>false, 'visible'=>true)
		);			
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'channel_id'  	=> array('title'=>_CHANNEL_NAME, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$this->arr_channels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'hotel_id'  	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$hotel_id),
			'cm_auth_key'   => array('title'=>_AUTH_KEY, 'type'=>'textbox', 'width'=>'310px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'255', 'default'=>'', 'validation_type'=>'alpha_numeric', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'placeholder'=>''),
			'cm_api_key'    => array('title'=>_API_KEY, 'type'=>'textbox', 'width'=>'310px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'255', 'default'=>'', 'validation_type'=>'alpha_numeric', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'placeholder'=>''),
			'cm_hotel_id'   => array('title'=>(defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).' ID', 'type'=>'textbox', 'width'=>'110px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'20', 'default'=>'', 'validation_type'=>'numeric', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'placeholder'=>''),
			'cm_enabled'    => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// * password field must be written directly in SQL!!!
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
		                            '.$this->tableName.'.*,
									'.$this->tableChannelsName.'.name as channel_name
								FROM '.$this->tableName.'
									INNER JOIN '.$this->tableChannelsName.' ON '.$this->tableName.'.channel_id = '.$this->tableChannelsName.'.id
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'channel_name'  => array('title'=>_CHANNEL_NAME, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'hotel_id'  	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$hotel_id),
			'channel_id'  	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>''),
			'cm_auth_key'   => array('title'=>_AUTH_KEY, 'type'=>'textbox', 'width'=>'310px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'255', 'default'=>'', 'validation_type'=>'alpha_numeric', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'placeholder'=>''),
			'cm_api_key'    => array('title'=>_API_KEY, 'type'=>'textbox', 'width'=>'310px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'255', 'default'=>'', 'validation_type'=>'alpha_numeric', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'placeholder'=>''),
			'cm_hotel_id'   => array('title'=>(defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).' ID', 'type'=>'textbox', 'width'=>'110px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'20', 'default'=>'', 'validation_type'=>'numeric', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'placeholder'=>''),
			'cm_enabled'    => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		// format: strip_tags, nl2br, readonly_text
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'channel_name'  => array('title'=>_CHANNEL_NAME, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'cm_auth_key'   => array('title'=>_AUTH_KEY, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'cm_api_key'    => array('title'=>_API_KEY, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'cm_hotel_id'   => array('title'=>(defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).' ID', 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'cm_enabled'    => array('title'=>_ACTIVE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
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
	 * Before-Add operation
	 */	
	public function BeforeAddRecord(){ 
		if(empty($this->arr_channels)){
			echo draw_important_message(_ALL_CHANNELS_ALREADY_ADDED, false);
			exit;
		}

	    return true;
	}

	/**
	 * Before-Insert operation
	 */
	public function BeforeInsertRecord()
	{
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE hotel_id = '.(int)$this->params['hotel_id'].' AND channel_id = \''.$this->params['channel_id'].'\'';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		
		if(!empty($result)){
			$this->error = str_replace('_FIELD_', '<b>'._CHANNEL.' ID</b>', _FILED_UNIQUE_VALUE_ALERT);
			return false;
		}
		
		return true;
	}
	
	/**
	 * Before-Update operation
	 */
	public function BeforeUpdateRecord()
	{
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE '.$this->primaryKey.' != '.(int)$this->curRecordId.' AND hotel_id = '.(int)$this->params['hotel_id'].' AND channel_id = \''.$this->params['channel_id'].'\'';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		
		if(!empty($result)){
			$this->error = str_replace('_FIELD_', '<b>'._CHANNEL.' ID</b>', _FILED_UNIQUE_VALUE_ALERT);
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get hotel data
	 * @param int $hotel_id
	 */
	public static function GetHotelData($hotel_id = 0, $channel_id = 0)
	{
		// Validate channel ID
		$channel_id = !empty($channel_id) ? $channel_id : self::$CHANNEL_ID;

		// Get data of channel hotel room 
		$sql = 'SELECT hotel_id, channel_id, cm_auth_key, cm_api_key, cm_hotel_id, cm_enabled
				FROM '.DB_PREFIX.'channel_hotels
				WHERE cm_hotel_id = '.(int)$hotel_id.' AND channel_id = '.(int)$channel_id;
		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		return $result;
	}
	
}
