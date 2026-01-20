<?php

/**
 * 	Class PropertyInventory (for uHotelBooking ONLY)
 *  -------------- 
 *  Description : 
 *  Updated	    : 27.01.2019
 * 	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Usage       : uHotelBooking
 * 	
 * 	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllPropertyInventory
 *  __destruct              GetSelectedHotelId
 *                          GetSelectedRoomId
 *
 *
 *
 *
 *  
 * */
class PropertyInventory extends MicroGrid {

    protected $debug = false;

    //-------------------------
    private $hotelOwner = false;
    private $hotelManager = false;
    private $hotelsList;
	
    //==========================================================================
    // Class Constructor
    //==========================================================================
	function __construct()
	{
        parent::__construct();

        global $objLogin;
        $this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner');
        $this->hotelManager = $objLogin->IsLoggedInAs('hotelmanager');
        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;

        $this->params = array();
		$this->params['hotel_id'] = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
		$this->params['room_id'] = isset($_POST['room_id']) ? (int)$_POST['room_id'] : '';
		$this->params['property_name'] = isset($_POST['property_name']) ? (string)$_POST['property_name'] : '';
		$this->params['description'] = isset($_POST['description']) ? (string)$_POST['description'] : '';
        $this->params['price'] = isset($_POST['price']) ? $_POST['price'] : '';
        $this->params['qty'] = isset($_POST['qty']) ? $_POST['qty'] : '0';
        $this->params['created_at'] = isset($_POST['created_at']) ? $_POST['created_at'] : '';
		$this->params['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : '';

        $this->primaryKey = 'id';
        $this->tableName = DB_PREFIX.'property_inventory';
        $this->dataSet = array();
        $this->error = '';
        $this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

        $hotel_id = $this->GetSelectedHotelId();
        $room_id = $this->GetSelectedRoomId();
        $default_currency = Currencies::GetDefaultCurrency();
        $currency_format = get_currency_format();
        $addPropertyInventory    = $objLogin->HasPrivileges('add_property_inventory');
        $editPropertyInventory   = $objLogin->HasPrivileges('edit_property_inventory');
        $deletePropertyInventory = $objLogin->HasPrivileges('delete_property_inventory');

        $this->actions      = array(
            'add'       => $addPropertyInventory,
            'edit'      => $editPropertyInventory,
            'details'   => true,
            'delete'    => $deletePropertyInventory
        );
        $this->formActionURL = 'index.php?admin=mod_property_management_inventory'.(!empty($hotel_id) ? '&filter_by_uhb_property_inventoryhotel_id='.(int)$hotel_id : '').(!empty($room_id) ? '&filter_by_uhb_property_inventoryroom_id='.(int)$room_id : '');
        $this->actionIcons = true;
        $this->allowRefresh = true;
        $this->allowPrint = true;
        $this->allowTopButtons = false;

        $this->allowLanguages = false;
        $this->WHERE_CLAUSE = '';
        $this->ORDER_CLAUSE = 'ORDER BY id DESC';

        $this->isAlterColorsAllowed = true;

        $this->isPagingAllowed = true;
        $this->pageSize = 20;

        $this->isSortingAllowed = true;

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

        $this->WHERE_CLAUSE = '';
		$this->hotelsList = '';
        if($this->hotelOwner || $this->hotelManager){
            $this->hotelsList = implode(',', $objLogin->AssignedToHotels());
            $this->WHERE_CLAUSE .= 'WHERE '.(!empty($this->hotelsList) ? $this->tableName.'.hotel_id IN ('.$this->hotelsList.')' : '1 = 0');
        }

        // Prepare hotels array
        $where_clause = '';
        if($this->hotelOwner || $this->hotelManager){
            $where_clause = !empty($this->hotelsList) ? TABLE_HOTELS.'.id IN ('.$this->hotelsList.')' : '1 = 0';
        }
        $total_hotels = Hotels::GetAllHotels($where_clause);
        $arr_hotels = array();
        $arr_hotels_filter = array();
        if(!empty($total_hotels) && is_array($total_hotels)){
            foreach($total_hotels[0] as $key => $val){
                $arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
                $arr_hotels_filter[$val['id']] = $val['name'].(!empty($val['location_name']) ? ' ('.$val['location_name'].') ' : '');

            }
        }

        $where_clause = '';
        if($this->hotelOwner || $this->hotelManager){
            $where_clause = '1 = 0';
            if(!empty($this->hotelsList)){
                if(empty($hotel_id)){
                    $where_clause = 'r.hotel_id IN ('.$this->hotelsList.')';
                }elseif(!empty($hotel_id) && in_array($hotel_id, $objLogin->AssignedToHotels())){
                    $where_clause = 'r.hotel_id = '.$hotel_id;
                }
            }
        }elseif(!empty($hotel_id)){
            $where_clause = 'r.hotel_id = '.$hotel_id;
        }

        $total_rooms = Rooms::GetAllRooms($where_clause);
        $arr_rooms = array();
        $arr_rooms_filter = array();
        if(!empty($total_rooms) && is_array($total_rooms)){
            foreach($total_rooms[0] as $key => $val){
                $arr_rooms[$val['id']] = $val['room_type'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
                $arr_rooms_filter[$val['id']] = $val['room_type'];
            }
        }


        $this->isFilteringAllowed = true;
        $this->arrFilteringFields = array(
            $hotel_name => array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels_filter, 'sign'=>'=', 'width'=>'250px', 'visible'=> true),
            _ROOM_TYPE => array('table'=>$this->tableName, 'field'=>'room_id', 'type'=>'dropdownlist', 'source'=>$arr_rooms_filter, 'sign'=>'=', 'width'=>'150px', 'visible'=> (!empty($arr_rooms_filter) && !empty($hotel_id)) ? true : false),
            _PROPERTY_INVENTORY_NAME => array('table' => $this->tableName, 'field' => 'property_name', 'type' => 'text', 'sign' => '%like%', 'width' => '100px'),
            _DESCRIPTION => array('table' => $this->tableName, 'field' => 'description', 'type' => 'text', 'sign' => '%like%', 'width' => '150px'),
        );

        //---------------------------------------------------------------------- 
        // VIEW MODE
        //---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.*,
									CONCAT("'.$default_currency.'", '.$this->tableName.'.price) as con_price
								FROM '.$this->tableName;
        // define view mode fields
        $this->arrViewModeFields = array(
            'property_name' 	=> array('title'=>_PROPERTY_INVENTORY_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'200px', 'maxlength'=>'30'),
            'hotel_id'      	=> array('title'=>$hotel_name, 'type'=>'enum',  'align'=>'left', 'width'=>'150px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>(empty($hotel_id) ? true : false), 'source'=>$arr_hotels),
            'room_id'      	    => array('title'=>_ROOM_TYPE, 'type'=>'enum',  'align'=>'left', 'width'=>'150px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>(empty($room_id) ? true : false), 'source'=>$arr_rooms),
            'description'       => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'55', 'format'=>'strip_tags'),
            'con_price'         => array('title'=>_PRICE, 'type'=>'label', 'align'=>'left', 'width'=>'100px', 'maxlength'=>'100', 'format'=>'strip_tags'),
            'qty'               => array('title'=>_QTY, 'type'=>'label', 'align'=>'left', 'width'=>'55px', 'maxlength'=>'50', 'format'=>'strip_tags'),
			'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'55px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
            'id' 			 	=> array('title'=>'ID', 'type' => 'label', 'align' => 'center', 'width' => '55px'),
        );

        //---------------------------------------------------------------------- 
        // ADD MODE
        //---------------------------------------------------------------------- 
        // define add mode fields
        $this->arrAddModeFields = array(
            'hotel_id'      => array('title'=>$hotel_name, 'type'=>'enum',  'width'=>'350px',   'required'=>true, 'readonly'=>false, 'default'=>(!empty($hotel_id) ? $hotel_id : ''), 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            'room_id'       => array('title'=>_ROOM_TYPE, 'type'=>'enum',  'width'=>'350px',   'required'=>false, 'readonly'=>false, 'default'=>((!empty($hotel_id) && !empty($room_id)) ? $room_id : ''), 'source'=>(!empty($hotel_id) ? $arr_rooms : array()), 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            'property_name' => array('title'=>_PROPERTY_INVENTORY_NAME,   'type'=>'textbox',  'width'=>'350px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'125', 'default'=>'', 'validation_type'=>'text'),
            'description'   => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'350px', 'required'=>false, 'height'=>'150px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'maxlength'=>'2048', 'validation_maxlength'=>'2048', 'unique'=>false),
            'price'         => array('title'=>_PRICE,  'type'=>'textbox',  'required'=>true, 'width'=>'90px', 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'10000000', 'unique'=>false, 'visible'=>true, 'pre_html'=>$default_currency.' '),
            'qty'           => array('title'=>_QTY,   'type'=>'textbox',  'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'numeric|positive'),
            'is_active'     => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
            'created_at'  	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s')),
        );

        //---------------------------------------------------------------------- 
        // EDIT MODE
        // * password field must be written directly in SQL!!!
        //---------------------------------------------------------------------- 
        $this->EDIT_MODE_SQL = 'SELECT
									'.$this->tableName.'.*,																		
									'.$this->tableName.'.price																	
								FROM '.$this->tableName.'
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';

        // define edit mode fields
        $this->arrEditModeFields = array(
            'hotel_id'      => array('title'=>$hotel_name, 'type'=>'enum',  'width'=>'350px',   'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            'room_id'       => array('title'=>_ROOM_TYPE, 'type'=>'enum',  'width'=>'350px',   'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_rooms, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            'property_name' => array('title'=>_PROPERTY_INVENTORY_NAME,   'type'=>'textbox',  'width'=>'350px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'125', 'default'=>'', 'validation_type'=>'text'),
            'description'   => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'350px', 'required'=>false, 'height'=>'150px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'text', 'maxlength'=>'2048', 'validation_maxlength'=>'2048', 'unique'=>false),
            'price'         => array('title'=>_PRICE,  'type'=>'textbox',  'required'=>true, 'width'=>'90px', 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'10000000', 'unique'=>false, 'visible'=>true, 'pre_html'=>$default_currency.' '),
            'qty'           => array('title'=>_QTY,   'type'=>'textbox',  'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'numeric|positive'),
            'is_active'     => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
        );

        //---------------------------------------------------------------------- 
        // DETAILS MODE
        // format: strip_tags, nl2br, readonly_text
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
        $this->arrDetailsModeFields = array(
            'hotel_id'      => array('title'=>$hotel_name, 'type'=>'enum', 'source'=>$arr_hotels),
            'room_id'       => array('title'=>_ROOM_TYPE, 'type'=>'enum', 'source'=>$arr_rooms),
            'property_name' => array('title'=>_PROPERTY_INVENTORY_NAME, 'type'=>'label'),
            'description'   => array('title'=>_DESCRIPTION, 'type'=>'label'),
            'price'         => array('title'=>_PRICE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$currency_format.'|2', 'pre_html'=>$default_currency),
            'qty'           => array('title'=>_QTY, 'type'=>'label'),
            'is_active'     => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
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
     * 	Get all countries array
     * 	@param string $where - where clause
     *  @return array
     */
	public static function GetAllPropertyInventory($where = '')
	{
        $sql = 'SELECT
					'.DB_PREFIX.'property_inventory.*
				FROM '.DB_PREFIX.'property_inventory 
				WHERE 1 = 1
					'.(!empty($where) ? ' AND '.$where : '').'	
				ORDER BY '.DB_PREFIX.'property_inventory.id ASC';

        return database_query($sql, DATA_AND_ROWS);

    }

    /**
     * 	Get select hotel id
     * @return string
     */
	public static function GetSelectedHotelId()
	{
        $operation = MicroGrid::GetParameter('operation');
        $selected_hotel_id = (in_array($operation, array('filtering', 'sorting')) ? MicroGrid::GetParameter('filter_by_uhb_property_inventoryhotel_id', false) : '0');

        return $selected_hotel_id;
    }

    /**
     * 	Get select room id
     * @return string
     */
    public static function GetSelectedRoomId()
    {
        $operation = MicroGrid::GetParameter('operation');
        $selected_room_id = (in_array($operation, array('filtering', 'sorting')) ? MicroGrid::GetParameter('filter_by_uhb_property_inventoryroom_id', false) : '0');

        return $selected_room_id;
    }

    /**
     * Check if there is a room in the hotel
     * @param int $hotelId
     * @param int $roomId
     * @return bool
     */
    public static function CheckRoomId($hotelId = 0, $roomId = 0)
    {
        $roomInfo = Rooms::GetRoomInfo($roomId);

        if(!empty($hotelId) && !empty($roomId) && $hotelId == $roomInfo['hotel_id']){
            return true;
        }

        return false;
    }


    //==========================================================================
    // MicroGrid Methods
	//==========================================================================


    /**
     *	'Before'-operation methods
     */
    public function BeforeInsertRecord()
    {
        $hotelId = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
        $roomId = isset($_POST['room_id']) ? (int)$_POST['room_id'] : '';

        if(!empty($roomId)){
            $result = $this->CheckRoomId($hotelId, $roomId);
            if($result){
                return true;
            }else{
                $this->error = _WRONG_PARAMETER_PASSED;
                return false;
            }

        }
        return true;
    }

    /**
     *	'Before'-operation methods
     */
    public function BeforeUpdateRecord()
    {
        $hotelId = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
        $roomId = isset($_POST['room_id']) ? (int)$_POST['room_id'] : '';

        if(!empty($roomId)){
            $result = $this->CheckRoomId($hotelId, $roomId);
            if($result){
                return true;
            }else{
                $this->error = _WRONG_PARAMETER_PASSED;
                return false;
            }

        }
        return true;
    }

    /**
    * Before drawing Add Mode
    */
    public function BeforeAddRecord()
    {
        if(!empty($this->params['hotel_id'])){
            $where_clause = 'r.hotel_id = '.$this->params['hotel_id'];

            $total_rooms = Rooms::GetAllRooms($where_clause);
            $arr_rooms = array();
            $arr_rooms_filter = array();
            if(!empty($total_rooms) && is_array($total_rooms)){
                foreach($total_rooms[0] as $key => $val){
                    $arr_rooms[$val['id']] = $val['room_type'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
                    $arr_rooms_filter[$val['id']] = $val['room_type'];
                }
            }
            $this->arrAddModeFields['room_id'] = array('title'=>_ROOM_TYPE, 'type'=>'enum',  'width'=>'350px',   'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_rooms, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false);
        }

        return true;
    }

    /**
    * Before drawing Edit Mode
    */
    public function BeforeEditRecord()
    {
        $sql = 'SELECT
					'.$this->tableName.'.hotel_id
				FROM '.$this->tableName.'
				WHERE '.$this->tableName.'.id = '.(int)$this->curRecordId;
        $inventory = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
        if(!empty($inventory['hotel_id'])){
            $where_clause = 'r.hotel_id = '.$inventory['hotel_id'];

            $total_rooms = Rooms::GetAllRooms($where_clause);
            $arr_rooms = array();
            $arr_rooms_filter = array();
            if(!empty($total_rooms) && is_array($total_rooms)){
                foreach($total_rooms[0] as $key => $val){
                    $arr_rooms[$val['id']] = $val['room_type'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
                    $arr_rooms_filter[$val['id']] = $val['room_type'];
                }
            }
            $this->arrEditModeFields['room_id'] = array('title'=>_ROOM_TYPE, 'type'=>'enum',  'width'=>'350px',   'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_rooms, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false);
        }

        return true;
    }

}
