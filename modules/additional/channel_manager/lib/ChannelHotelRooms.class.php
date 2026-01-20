<?php

/**
 *	Class ChannelHotelRooms (for uHotelBooking ONLY)
 *  -------------- 
 *  Description : 
 *  Updated	    : 10.12.2016
 *	Written by  : ApPHP
 *	
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				AddRoom					GetHotelRoomsData
 *  __destruct				DeleteRoom
 *  DrawRoomsForm			DeleteRooms
 *  UpdateRooms				GetHotelRoomData
 *  
 *  
 **/

class ChannelHotelRooms extends MicroGrid {
	
	protected $debug = false;
	
	protected $hotel_id = array();
	protected $channel_id = array();
	protected $channel_rooms = array();
	protected $hotel_rooms = array();
	
	protected static $CHANNEL_ID = 1; 
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($hotel_id = 0, $channel_id = 0)
	{
		parent::__construct();
		
		$this->hotel_id = !empty($hotel_id) ? (int)$hotel_id : 0;
		$this->channel_id = !empty($channel_id) ? (int)$channel_id : self::$CHANNEL_ID;
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= DB_PREFIX.'channel_hotel_rooms';
		$this->tableChannelsName = DB_PREFIX.'channels';
		$this->error 		= '';
		$this->error_field	= '';

		// Get data of channel hotel rooms
		$this->GetHotelRoomsData();

		// Get data of hotel rooms
		$sql = 'SELECT * FROM '.TABLE_ROOMS.' WHERE hotel_id = '.(int)$this->hotel_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] == 0){
			$this->error = _THIS_HOTEL_NO_ROOMS;
		}else{
			$this->hotel_rooms = $result;
		}
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 *	Draws rooms form
	 */
	public function DrawRoomsForm()
	{
		if(!empty($this->hotel_rooms)){			
			$nl = "\n";
			$output = '';
			
			$output .= '<div class="table-responsive">';
			$output .= '<form action="index.php?admin=mod_channel_manager_rooms&hid='.$this->hotel_id.'&chid='.$this->channel_id.'" id="frmRooms" method="post">';
			$output .= draw_hidden_field('task', 'update', false, 'task');
			$output .= draw_token_field(false);
			
			$output .= '<table width="100%" border="0" class="mgrid_table">';
			$output .= '<thead>';
			$output .= '<tr>';
			$output .= '<th width="180px">'._ROOM_TYPE.'</th>';
			$output .= '<th>'.ucfirst(_ROOM).' (ID)</th>';
			$output .= '</tr>';
			$output .= '</thead>';
	
			$output .= '<tbody>';
			for($i=0; $i<$this->hotel_rooms[1]; $i++){
				$room_id = $this->hotel_rooms[0][$i]['id'];
		
				// Fimd channel room ID by hotel room ID
				$cm_room_id = '';
				foreach($this->channel_rooms[0] as $channel_room){
					if($room_id == $channel_room['room_id']){
						$cm_room_id = (isset($channel_room['cm_room_id']) ? htmlspecialchars($channel_room['cm_room_id']) : '');
						break;
					}
				}
				
				$output .= '<tr>';
				$output .= '<td>'.$this->hotel_rooms[0][$i]['room_type'].'</td>';
				$output .= '<td><input type="text" '.($this->error_field == $room_id ? 'autofocus' : '').' name="rooms['.$room_id.']" class="mgrid_text" style="width:210px" maxlength="20" value="'.$cm_room_id.'" /></td>';
				$output .= '</tr>';
			}
			$output .= '</tbody>';
			
			$output .= '<tr><td colspan="39">&nbsp;</td></tr>';
			$output .= '<tr><td align="'.Application::Get('defined_left').'"><input type="submit" class="form_button" style="width:130px" onclick="javascript:submitAvailabilityForm(\'update\')" value="'._BUTTON_SAVE_CHANGES.'"></td></tr>';
			$output .= '</table>';
			$output .= '</form>';
			$output .= '</div>';
		
			echo $output;		
		}
	}

	/**
	 *	Draws rooms form
	 */
	public function UpdateRooms()
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$rooms_data = isset($_POST['rooms']) ? (array)$_POST['rooms'] : array();
		
		// Validate room ID
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE hotel_id = '.(int)$this->hotel_id.' AND channel_id = '.(int)$this->channel_id;
		$channel_rooms = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		$channel_rooms_ids = array();
		for($i=0; $i<$channel_rooms[1]; $i++){
			$channel_rooms_ids[] = $channel_rooms[0][$i]['room_id'];
		}
		
		// Input validation
		$count = 0;
		$all_fields_empty = true;
		foreach($rooms_data as $key => $room){
			if(!empty($room)){
				$all_fields_empty = false;
				if(!preg_match('/[0-9]/', $room)){
					$this->error = str_replace('_FIELD_', '<b>'.$this->hotel_rooms[0][$count]['room_type'].'</b>', _FIELD_MUST_BE_NUMERIC);
					$this->error_field = $this->hotel_rooms[0][$count]['id'];
					return false;
				}
				if(!empty($channel_rooms_ids) && !in_array($key, $channel_rooms_ids)){
					$this->error = _WRONG_PARAMETER_PASSED;
					$this->error_field = $this->hotel_rooms[0][$count]['id'];
					return false;
				}
			}
			$count++;
		}
		
		// Insert/Update DB
		if(!$all_fields_empty){			
			foreach($rooms_data as $key => $room){
				$sql = "INSERT INTO ".$this->tableName."
							(id, hotel_id, room_id, channel_id, cm_room_id)
						VALUES
							(NULL, ".(int)$this->hotel_id.", ".(int)$key.", ".(int)$this->channel_id.", '".$room."')
						ON DUPLICATE KEY UPDATE cm_room_id = '".$room."'";
				if(database_void_query($sql)){
					//$this->error = _TRY_LATER;
					//return false;
				}
			}
			
			// Refresh data
			$this->GetHotelRoomsData();
			
			return true;
		}else{
			$this->error = _NO_RECORDS_PROCESSED;
			reset($rooms_data);
			$this->error_field = key($rooms_data);
			return false;
		}
	}
	
	/**
	 * Add room
	 * @param int $hotel_id
	 * @param int $room_id
	 */
	public static function AddRoom($hotel_id, $room_id)
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		if(empty($hotel_id) || empty($room_id)){
			return false;
		}
		
		$channels = ChannelManager::GetAllChannels();
		
		for($i=0; $i<$channels[1]; $i++){			
			$sql = "INSERT INTO ".DB_PREFIX."channel_hotel_rooms
						(id, hotel_id, room_id, channel_id, cm_room_id)
					VALUES 
						(NULL, ".(int)$hotel_id.", ".(int)$room_id.", ".(int)$channels[0][$i]['id'].", '')";
			database_void_query($sql);
		}
	}

	/**
	 * Delete room
	 * @param int $room_id
	 */
	public static function DeleteRoom($room_id)
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'DELETE FROM '.DB_PREFIX.'channel_hotel_rooms WHERE room_id = '.(int)$room_id;
		database_void_query($sql);
	}

	/**
	 * Delete rooms
	 * @param int $room_ids
	 */
	public static function DeleteRooms($room_ids = '')
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		if(!empty($room_ids)){
			$sql = 'DELETE FROM '.DB_PREFIX.'channel_hotel_rooms WHERE room_id IN('.$room_ids.')';
			database_void_query($sql);
		}
	}

	/**
	 * Get room data
	 * @param int $room_id
	 */
	public static function GetHotelRoomData($room_id = 0, $channel_id = 0)
	{
		// Validate channel ID
		$channel_id = !empty($channel_id) ? $channel_id : self::$CHANNEL_ID;
		
		// Get data of channel hotel room 
		$sql = 'SELECT
					ch.hotel_id, ch.channel_id, ch.cm_auth_key, ch.cm_api_key, ch.cm_hotel_id, ch.cm_enabled,
					chr.room_id, chr.cm_room_id
				FROM '.DB_PREFIX.'channel_hotels as ch
					INNER JOIN '.DB_PREFIX.'channel_hotel_rooms chr ON ch.hotel_id = chr.hotel_id
				WHERE
					chr.room_id = '.(int)$room_id.' AND ch.channel_id = '.(int)$channel_id;
		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		
		return $result;
	}
	
	/**
	 * Get all rooms data
	 */
	private function GetHotelRoomsData()
	{
		// Get data of channel hotel rooms
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE hotel_id = '.(int)$this->hotel_id.' AND channel_id = '.(int)$this->channel_id;
		$this->channel_rooms = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	}
	
}
