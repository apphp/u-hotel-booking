<?php

/**
 * 	Class PropertyManagers (for uHotelBooking ONLY)
 *  -------------- 
 *  Description : 
 *  Updated	    : 27.01.2019
 * 	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Usage       : uHotelBooking
 * 	
 * 	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllPropertyManagers
 *  __destruct              GetSelectedHotelId
 *
 *
 *
 *
 *
 *  
 * */
class PropertyManagers extends MicroGrid {

    protected $debug = false;

    //-------------------------
    private $regionalManager = false;
    private $hotelOwner = false;
    private $allowChangingPassword;
    private $hotelsList;

	private $arrTranslations = '';		

    //==========================================================================
    // Class Constructor
    //==========================================================================
	function __construct()
	{
        parent::__construct();

        global $objLogin;
        $this->regionalManager = $objLogin->IsLoggedInAs('regionalmanager');
        $this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner');
        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;

        $this->params = array();
		$this->params['hotel_id'] = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
		$this->params['first_name'] = isset($_POST['first_name']) ? $_POST['first_name'] : '';
		$this->params['last_name'] = isset($_POST['last_name']) ? $_POST['last_name'] : '';
		$this->params['email'] = isset($_POST['email']) ? $_POST['email'] : '';
		$this->params['user_name'] = isset($_POST['user_name']) ? $_POST['user_name'] : '';
		$this->params['user_password'] = isset($_POST['user_password']) ? $_POST['user_password'] : '';
		$this->params['preferred_language'] = isset($_POST['preferred_language']) ? $_POST['preferred_language'] : '';
		$this->params['created_at'] = isset($_POST['created_at']) ? $_POST['created_at'] : '';
		$this->params['date_lastlogin'] = isset($_POST['date_lastlogin']) ? $_POST['date_lastlogin'] : '';
		$this->params['created_by_admin_id'] = isset($_POST['created_by_admin_id']) ? $_POST['created_by_admin_id'] : '0';
		$this->params['registered_from_ip'] = isset($_POST['registered_from_ip']) ? $_POST['registered_from_ip'] : '';
		$this->params['last_logged_ip'] = isset($_POST['last_logged_ip']) ? $_POST['last_logged_ip'] : '';
		$this->params['is_active'] = isset($_POST['is_active']) ? $_POST['is_active'] : '1';
		$this->params['is_removed'] = isset($_POST['is_removed']) ? $_POST['is_removed'] : '0';
		$this->params['comments'] = isset($_POST['comments']) ? $_POST['comments'] : '';

        $this->primaryKey = 'id';
        $this->tableName = DB_PREFIX.'property_managers';
        $this->dataSet = array();
        $this->error = '';
        $this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

        $user_ip = get_current_ip();
        $date_format_edit = get_date_format('edit');
        $hotel_id = $this->GetSelectedHotelId();
        $addPropertyManagers    = $objLogin->HasPrivileges('add_property_managers');
        $editPropertyManagers   = $objLogin->HasPrivileges('edit_property_managers');
        $deletePropertyManagers = $objLogin->HasPrivileges('delete_property_managers');

        $this->actions      = array(
            'add'       => $addPropertyManagers,
            'edit'      => $editPropertyManagers,
            'details'   => true,
            'delete'    => $deletePropertyManagers
        );
        $this->formActionURL = 'index.php?admin=mod_property_management_managers'.(!empty($hotel_id) ? '&filter_by_uhb_property_managershotel_id='.(int)$hotel_id : '');
        $this->actionIcons = true;
        $this->allowRefresh = true;
        $this->allowPrint = true;
        $this->allowTopButtons = false;

        $this->allowLanguages = false;
        $this->ORDER_CLAUSE = 'ORDER BY id DESC';

        $this->isAlterColorsAllowed = true;

        $this->isPagingAllowed = true;
        $this->pageSize = 20;
        $this->allowChangingPassword = ModulesSettings::Get('customers', 'password_changing_by_admin');

        $this->isSortingAllowed = true;

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

        $this->WHERE_CLAUSE = '';
        $this->hotelsList = '';
        if($objLogin->IsLoggedInAs('hotelowner')){
            $this->hotelsList = implode(',', $objLogin->AssignedToHotels());
            $this->WHERE_CLAUSE .= 'WHERE '.(!empty($this->hotelsList) ? $this->tableName.'.hotel_id IN ('.$this->hotelsList.')' : '1 = 0');
        }elseif($this->regionalManager){
            $this->hotelsList = implode(',', AccountLocations::GetHotels($objLogin->GetLoggedID()));
            $this->WHERE_CLAUSE .= 'WHERE '.(!empty($this->hotelsList) ? $this->tableName.'.hotel_id IN ('.$this->hotelsList.')' : '1 = 0');
        }

        // Prepare hotels array
        $where_clause = '';
        if($this->hotelOwner){
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

        // prepare languages array
        $total_languages = Languages::GetAllActive();
        $arr_languages = array();
        foreach($total_languages[0] as $key => $val){
            $arr_languages[$val['abbreviation']] = $val['lang_name'];
        }

        $this->isFilteringAllowed = true;
        $this->arrFilteringFields = array(
            $hotel_name => array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels_filter, 'sign'=>'=', 'width'=>'250px', 'visible'=> true),
            _USERNAME   => array('table'=>$this->tableName, 'field'=>'user_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
            _FIRST_NAME  => array('table'=>$this->tableName, 'field'=>'first_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
            _LAST_NAME  => array('table'=>$this->tableName, 'field'=>'last_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
            _EMAIL      => array('table'=>$this->tableName, 'field'=>'email', 'type'=>'text', 'sign'=>'like%', 'width'=>'100px'),
        );

        //---------------------------------------------------------------------- 
        // VIEW MODE
        //---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.*,
									CONCAT('.$this->tableName.'.first_name, " ", '.$this->tableName.'.last_name) as full_name
								FROM '.$this->tableName;
        // define view mode fields
        $this->arrViewModeFields = array(
            'full_name'    	 => array('title'=>_CUSTOMER_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'25'),
            'user_name'		 => array('title'=>_USERNAME, 'type'=>'label', 'align'=>'left', 'width'=>''),
            'email' 	   	 => array('title'=>_EMAIL_ADDRESS, 'type'=>'link', 'href'=>'mailto:{email}', 'align'=>'left', 'maxlength'=>'28', 'width'=>''),
            'hotel_id'       => array('title'=>$hotel_name, 'type'=>'enum',  'align'=>'left', 'width'=>'150px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>(empty($hotel_id) ? true : false), 'source'=>$arr_hotels),
            'is_active'    	 => array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'50px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
            'is_removed' 	 => array('title'=>_REMOVED, 'type'=>'enum', 'align'=>'center', 'width'=>'50px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
            'id'           	 => array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'50px'),
        );

        //---------------------------------------------------------------------- 
        // ADD MODE
        //---------------------------------------------------------------------- 
        // define add mode fields
        $this->arrAddModeFields['separator_1'] = array(
            'separator_info' => array('legend'=>_PERSONAL_DETAILS),
            'hotel_id'       => array('title'=>$hotel_name, 'type'=>'enum',  'width'=>'210px',   'required'=>true, 'readonly'=>false, 'default'=>((count($arr_hotels) == 1) ? key($arr_hotels) : $hotel_id), 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            'first_name'  	 => array('title'=>_FIRST_NAME,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
            'last_name'    	 => array('title'=>_LAST_NAME, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
        );
        $this->arrAddModeFields['separator_2'] = array(
            'separator_info' 		=> array('legend'=>_ACCOUNT_DETAILS),
            'email' 		 	=> array('title'=>_EMAIL_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'100', 'required'=>true, 'readonly'=>false, 'validation_type'=>'email', 'unique'=>false, 'autocomplete'=>'off'),
            'user_name' 	 		=> array('title'=>_USERNAME,  'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text', 'validation_minlength'=>'4', 'readonly'=>false, 'unique'=>true, 'username_generator'=>true),
            'user_password'  		=> array('title'=>_PASSWORD, 'type'=>'password', 'width'=>'210px', 'maxlength'=>'30', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'password_generator'=>true),
            'preferred_language' 	=> array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'120px', 'default'=>Application::Get('lang'), 'source'=>$arr_languages),
        );
        $this->arrAddModeFields['separator_3'] = array(
            'separator_info' 		=> array('legend'=>_OTHER),
            'created_at'        	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>date('Y-m-d H:i:s')),
            'registered_from_ip'  	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>$user_ip),
            'last_logged_ip'      	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>''),
            'is_removed'          	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>'0'),
            'is_active'           	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
            'comments'            	=> array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'420px', 'height'=>'70px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'validation_maxlength'=>'1024'),
        );

        //---------------------------------------------------------------------- 
        // EDIT MODE
        // * password field must be written directly in SQL!!!
        //---------------------------------------------------------------------- 
        $this->EDIT_MODE_SQL = 'SELECT
									'.$this->tableName.'.*																		
								FROM '.$this->tableName.'
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';

        // define edit mode fields

        $this->arrEditModeFields['separator_1'] = array(
            'separator_info' 	=> array('legend'=>_PERSONAL_DETAILS),
            'hotel_id'       => array('title'=>$hotel_name, 'type'=>'enum',  'width'=>'210px',   'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            'first_name'  	 	=> array('title'=>_FIRST_NAME,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
            'last_name'    	 	=> array('title'=>_LAST_NAME, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
        );
        $this->arrEditModeFields['separator_4'] = array(
            'separator_info' 	 => array('legend'=>_ACCOUNT_DETAILS),
            'email' 		 	 => array('title'=>_EMAIL_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'100', 'required'=>true, 'readonly'=>false, 'validation_type'=>'email', 'unique'=>true, 'autocomplete'=>'off'),
            'user_name'  		 => array('title'=>_USERNAME, 'type'=>'label'),
            'user_password'  	 => array('title'=>_PASSWORD, 'type'=>'password', 'width'=>'210px', 'maxlength'=>'20', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'visible'=>(($this->allowChangingPassword == 'yes') ? true : false)),
            'preferred_language' => array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'120px', 'source'=>$arr_languages),
        );
        $this->arrEditModeFields['separator_6'] = array(
            'separator_info' 		=> array('legend'=>_OTHER),
            'created_at'			=> array('title'=>_DATE_CREATED, 'type'=>'label'),
            'date_lastlogin'		=> array('title'=>_LAST_LOGIN, 'type'=>'label'),
            'registered_from_ip'	=> array('title'=>_REGISTERED_FROM_IP, 'type'=>'label'),
            'last_logged_ip'		=> array('title'=>_LAST_LOGGED_IP,	 'type'=>'label'),
            'is_active'				=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>true),
            'is_removed'			=> array('title'=>_REMOVED,	'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>true),
            'comments'				=> array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'420px', 'height'=>'70px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'validation_maxlength'=>'1024', 'visible'=>true),
        );

        //---------------------------------------------------------------------- 
        // DETAILS MODE
        // format: strip_tags, nl2br, readonly_text
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
        $this->arrDetailsModeFields['separator_1'] = array(
            'separator_info' 		=> array('legend'=>_PERSONAL_DETAILS),
            'first_name'  			=> array('title'=>_FIRST_NAME, 'type'=>'label'),
            'last_name'    			=> array('title'=>_LAST_NAME,  'type'=>'label'),
        );
        $this->arrDetailsModeFields['separator_2'] = array(
            'separator_info' 		=> array('legend'=>_ACCOUNT_DETAILS),
            'email' 				=> array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
            'user_name'  		    => array('title'=>_USERNAME,	 'type'=>'label'),
            'preferred_language' 	=> array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'source'=>$arr_languages),
        );
        $this->arrDetailsModeFields['separator_3'] = array(
            'separator_info' 		=> array('legend'=>_OTHER),
            'created_at'			=> array('title'=>_DATE_CREATED, 'type'=>'label'),
            'date_lastlogin'		=> array('title'=>_LAST_LOGIN,	 'type'=>'label'),
            'registered_from_ip' 	=> array('title'=>_REGISTERED_FROM_IP, 'type'=>'label'),
            'last_logged_ip'	 	=> array('title'=>_LAST_LOGGED_IP,	 'type'=>'label'),
            'is_active'	        	=> array('title'=>_ACTIVE,	 'type'=>'enum', 'source'=>$arr_active_vm),
            'is_removed'	    	=> array('title'=>_REMOVED,  'type'=>'enum', 'source'=>$arr_active_vm),
            'comments'				=> array('title'=>_COMMENTS,  'type'=>'label', 'format'=>'nl2br'),
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
     * 	@param $where - where clause
     */
	public static function GetAllPropertyManagers($where = '')
	{

    }

    /**
     * 	Get select hotel id
     * @return string
     */
	public static function GetSelectedHotelId()
	{
        $operation = MicroGrid::GetParameter('operation');
        $selected_hotel_id = (in_array($operation, array('filtering', 'sorting')) ? MicroGrid::GetParameter('filter_by_uhb_property_managershotel_id', false) : '0');

        return $selected_hotel_id;
    }

	//==========================================================================
    // MicroGrid Methods
	//==========================================================================




}
