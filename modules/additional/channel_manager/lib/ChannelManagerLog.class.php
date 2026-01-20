<?php

/**
 *	ChannelManagerLog Class
 *  --------------
 *  Description : encapsulates ban list properties
 *	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Updated		: 04.06.2016
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *	------------------		---------------			---------------
 *	__construct				LogAddRecord
 *	__destruct
 *
 *  1.0.0
 *      - added DeleteAllRecord method
 *
 **/


class ChannelManagerLog extends MicroGrid {

	protected $debug = false;

	protected static $CHANNEL_ID = 1;

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		parent::__construct();

		$this->params = array();


		$this->params['language_id'] = MicroGrid::GetParameter('language_id');

		$this->primaryKey	= 'id';
		$this->tableName	= DB_PREFIX.'channels_log';
		$this->tableChannelsName = DB_PREFIX.'channels';
		$this->dataSet		= array();
		$this->error		= '';
		$this->actions      = array('add'=>false, 'edit'=>false, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.date_created DESC';

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;
		$datetime_format = get_datetime_format();

		// define filtering fields
		//$this->arrFilteringFields = array(
			//_CHANNEL_MANAGER  => array('table'=>$this->tableName, 'field'=>'channel_id', 'type'=>'dropdownlist', 'source'=>$arr_users, 'sign'=>'=', 'width'=>'130px'),
		//);

		// prepare languages array
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		///		$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		//----------------------------------------------------------------------
		// VIEW MODE
		//----------------------------------------------------------------------
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.hotel_id,
									'.$this->tableName.'.channel_id,
									'.$this->tableName.'.log_subject,
									'.$this->tableName.'.log_status,
									'.$this->tableName.'.log_status_description,
									'.$this->tableName.'.log_content,
									'.$this->tableName.'.date_created,
									IF('.TABLE_HOTELS_DESCRIPTION.'.name IS NOT NULL, '.TABLE_HOTELS_DESCRIPTION.'.name, "- undefined - ") as hotel_name,
									IF('.TABLE_ROOMS_DESCRIPTION.'.room_type IS NOT NULL, '.TABLE_ROOMS_DESCRIPTION.'.room_type, "- undefined - ") as room_name,
									'.$this->tableChannelsName.'.name as channel_name
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.$this->tableName.'.hotel_id = '.TABLE_HOTELS_DESCRIPTION.'.hotel_id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$this->languageId.'\'
									LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.$this->tableName.'.room_id = '.TABLE_ROOMS_DESCRIPTION.'.room_id AND '.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.$this->languageId.'\'
									INNER JOIN '.$this->tableChannelsName.' ON '.$this->tableName.'.channel_id = '.$this->tableChannelsName.'.id
								';

		// define view mode fields
		$this->arrViewModeFields = array(
			'date_created'		=> array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'left', 'width'=>'180px', 'format'=>'date', 'format_parameter'=>$datetime_format),
			'hotel_name'		=> array('title'=> defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'label', 'align'=>'left', 'width'=>'150px', 'height'=>'', 'maxlength'=>''),
			'room_name'			=> array('title'=>_ROOM_TYPE, 'type'=>'label', 'align'=>'left', 'width'=>'120px', 'height'=>'', 'maxlength'=>''),
			'channel_name'		=> array('title'=>_CHANNEL, 'type'=>'label', 'align'=>'left', 'width'=>'110px', 'height'=>'', 'maxlength'=>''),
			'log_subject'		=> array('title'=>_SUBJECT, 'type'=>'label', 'align'=>'left', 'width'=>'180px', 'height'=>'', 'maxlength'=>''),
			'log_status'		=> array('title'=>_STATUS, 'type'=>'label', 'align'=>'left', 'width'=>'90px', 'height'=>'', 'maxlength'=>''),
			'log_status_description'=> array('title'=>_STATUS_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'85', 'format'=>'date', 'format_parameter'=>''),
		);

		//----------------------------------------------------------------------
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.hotel_id,
								'.$this->tableName.'.channel_id,
								'.$this->tableName.'.log_subject,
								'.$this->tableName.'.log_status,
								'.$this->tableName.'.log_status_description,
								'.$this->tableName.'.log_content,
								'.$this->tableName.'.date_created,
								IF('.TABLE_HOTELS_DESCRIPTION.'.name IS NOT NULL, '.TABLE_HOTELS_DESCRIPTION.'.name, "- undefined - ") as hotel_name,
								IF('.TABLE_ROOMS_DESCRIPTION.'.room_type IS NOT NULL, '.TABLE_ROOMS_DESCRIPTION.'.room_type, "- undefined - ") as room_name,
								'.$this->tableChannelsName.'.name as channel_name
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.$this->tableName.'.hotel_id = '.TABLE_HOTELS_DESCRIPTION.'.hotel_id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$this->languageId.'\'
								LEFT OUTER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.$this->tableName.'.room_id = '.TABLE_ROOMS_DESCRIPTION.'.room_id AND '.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.$this->languageId.'\'
								INNER JOIN '.$this->tableChannelsName.' ON '.$this->tableName.'.channel_id = '.$this->tableChannelsName.'.id
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
		$this->arrDetailsModeFields = array(
			'date_created'	        => array('title'=>_DATE_CREATED, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
			'hotel_name'            => array('title'=> defined('FLATS_INSTEAD_OF_HOTELS') && FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'label'),
			'room_name'		        => array('title'=>_ROOM_TYPE, 'type'=>'label'),
			'channel_name'	        => array('title'=>_CHANNEL, 'type'=>'label'),
			'log_subject'	        => array('title'=>_SUBJECT, 'type'=>'label'),
			'log_status'	        => array('title'=>_STATUS, 'type'=>'label'),
			'log_status_description'	=> array('title'=>_STATUS_DESCRIPTION, 'type'=>'label'),
			'log_content'	        => array('title'=>_LOG_CONTENT, 'type'=>'label'),
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
	 * Add new records
	 * @param $hotel_id
	 * @param $room_id
	 * @param $log_subject
	 * @param $log_status
	 * @param $log_status_description
	 * @param $log_content
	 * @param $channel_id
	 * @return bool
	 */
	public static function LogAddRecord($hotel_id = 0, $room_id = 0, $log_subject = '',  $log_status = '', $log_status_description = '', $log_content = '', $channel_id = 0)
	{
		if(Modules::IsModuleInstalled('channel_manager') &&
		   (ModulesSettings::Get('channel_manager', 'is_active') != 'no') &&
		   (ModulesSettings::Get('channel_manager', 'is_log_active') != 'no')
		){
			// Validate channel ID
			$channel_id = !empty($channel_id) ? $channel_id : self::$CHANNEL_ID;

			if(!empty($hotel_id) && !empty($channel_id)){
				$sql = "INSERT INTO ".DB_PREFIX."channels_log
					(id, channel_id, hotel_id, room_id, log_subject, log_status, log_status_description, log_content, date_created) VALUES
					(NULL, '".$channel_id."', '".$hotel_id."', '".$room_id."', '".encode_text($log_subject)."', '".encode_text($log_status)."', '".encode_text($log_status_description)."', '".encode_text($log_content)."', '".date('Y-m-d H:i:s')."')";
				database_void_query($sql);
				//echo database_error();
				//exit;
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete all records
	 * @return boolean
	 */
	public function DeleteAllRecord()
	{
		$sql = "DELETE FROM ".DB_PREFIX."channels_log WHERE 1";
        if($this->debug) $start_time = $this->GetFormattedMicrotime();
        $result = database_void_query($sql);
        if($this->debug) $finish_time = $this->GetFormattedMicrotime();
		if($result > 0){
			if($this->debug) $this->arrSQLs['delete_sql'] = '<i>Delete All Records</i> | T: '.round((float)$finish_time - (float)$start_time, 4).' sec. <br>'.$sql;
			return true;
		}else{
			if($this->debug) $this->arrErrors['delete_sql'] = $sql.'<br>'.database_error();
			$this->error = _TRY_LATER;
			return false;
		}
	}
}
