<?php

/**
 *	Class ConferenceParticipants
 *  --------------
 *	Description : 
 *	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Updated	    : 02.05.2017
 *  Usage       : uHotelBooking
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct
 *	__destruct
 *	
 **/

class ConferenceParticipants extends MicroGrid {

    protected $debug = false;

    //==========================================================================
    // Class Constructor
    //==========================================================================
	function __construct($conference_id = '')
	{
        parent::__construct();

        //global $objSettings;
        //global $objLogin;

        $this->params = array();

        $this->primaryKey = 'id';
        $this->tableName = DB_PREFIX.'conference_participants';
        $this->tableConferenceName = DB_PREFIX.'conferences';
		$this->tableConferenceTypeName = DB_PREFIX.'conference_types_translations';
		$this->tableSpeciality = DB_PREFIX.'specialities_description';
        $this->error = '';

        $this->actions = array('add' => false, 'edit' => false, 'details' => true, 'delete' => true);
        $this->actionIcons = true;
        $this->allowRefresh = true;
        $this->allowPrint = true;
        $this->allowTopButtons = false;

        $this->allowLanguages = false;
        $this->WHERE_CLAUSE = (int)$conference_id != 0 ? 'WHERE conference_id = '.(int)$conference_id : '';
        $this->ORDER_CLAUSE = 'ORDER BY id DESC';

        $this->isAlterColorsAllowed = true;

        $this->isPagingAllowed = true;
        $this->pageSize = 20;
        $this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

        $this->isSortingAllowed = true;

		$this->arrHotelsLocationsWm = array();

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

        $this->isFilteringAllowed = false;
        $this->arrFilteringFields = array();

		$rid = self::GetParameter('rid');

		$arr_course_registrations = array();
		$course_registrations = ConferenceTuitions::GetConferenceTuitions($conference_id);
		if(!empty($course_registrations[1])){
			for($i = 0; $i < $course_registrations[1]; $i++){
				$arr_course_registrations[$course_registrations[0][$i]['id']] = $course_registrations[0][$i]['name'];
			}
		}

        //---------------------------------------------------------------------- 
        // VIEW MODE
        //---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.*,
									'.$this->tableConferenceTypeName.'.name as conference_name,
									'.$this->tableSpeciality.'.name as speciality_name,
									DATE_FORMAT('.$this->tableName.'.created_date, "%b %d, %Y <br/> %H:%i:%s") as created_date_formated,
									IF(
										'.$this->tableName.'.is_admin_registration = 0,
										CONCAT("<a href=\"javascript:void(\'customer|view\')\" onclick=\"open_popup(\'popup.ajax.php\',\'customer\',\'",'.$this->tableName.'.customer_id,"\',\'",'.TABLE_CUSTOMERS.'.customer_type,"\',\''.Application::Get('token').'\')\">", CONCAT('.TABLE_CUSTOMERS.'.last_name, " ", '.TABLE_CUSTOMERS.'.first_name, " ", IF('.TABLE_CUSTOMERS.'.customer_type = 1, " ('.htmlentities(_AGENCY).')", "")), "</a>"),
										CONCAT("<a href=\"javascript:void(\'admin|view\')\" onclick=\"open_popup(\'popup.ajax.php\',\'admin\',\'",'.$this->tableName.'.customer_id,"\',\'",0,"\',\''.Application::Get('token').'\')\">", CONCAT('.TABLE_ACCOUNTS.'.last_name, " ", '.TABLE_ACCOUNTS.'.first_name, " (", '.TABLE_ACCOUNTS.'.account_type), ")</a>")
									) as customer_name
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.$this->tableConferenceName.' ON '.$this->tableName.'.conference_id = '.$this->tableConferenceName.'.id
									LEFT OUTER JOIN '.$this->tableConferenceTypeName.' ON '.$this->tableConferenceName.'.conference_type_id = '.$this->tableConferenceTypeName.'.id
									LEFT OUTER JOIN '.$this->tableSpeciality.' ON '.$this->tableName.'.speciality_id = '.$this->tableSpeciality.'.speciality_id AND '.$this->tableSpeciality.'.language_id = \''.$this->languageId.'\'
									LEFT OUTER JOIN '.TABLE_BOOKINGS.' ON '.$this->tableName.'.booking_number = '.TABLE_BOOKINGS.'.booking_number
									LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
									LEFT OUTER JOIN '.TABLE_ACCOUNTS.' ON '.$this->tableName.'.customer_id = '.TABLE_ACCOUNTS.'.id AND '.$this->tableName.'.is_admin_registration = 1
								';
        // define view mode fields
        $this->arrViewModeFields = array(
            'conference_name'		=> array('title'=>_CONFERENCE_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'270px', 'maxlength'=>''),
			'customer_name'			=> array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
			'speciality_name'		=> array('title'=>_SPECIALTY,  'type'=>'label', 'align'=>'left', 'width'=>'90px'),
			'created_date_formated' => array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'nowrap'=>'nowrap', 'maxlength'=>''),
			'booking_number'		=> array('title'=>_BOOKING_NUMBER, 'type'=>'label', 'align'=>'center', 'width'=>'140px', 'sortable'=>false, 'visible'=>true),
        );

		// prepare trigger
		$sql = 'SELECT
					IF(TRIM(cc_number) = "" OR LENGTH(AES_DECRYPT(cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")) <= 4, "hide", "show") as cc_number_trigger
				FROM '.$this->tableName.' WHERE id = '.(int)$rid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$cc_number_trigger = $result[0]['cc_number_trigger'];
		}else{
			$cc_number_trigger = 'hide';
		}

        //---------------------------------------------------------------------- 
        // DETAILS MODE
        // format: strip_tags, nl2br, readonly_text
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = 'SELECT
									'.$this->tableName.'.*,
									'.$this->tableConferenceTypeName.'.name as conference_name,
									'.$this->tableSpeciality.'.name as speciality_name,
									IF(
										LENGTH(AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")) = 4,
										CONCAT("...", AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'"), " ('._CLEANED.')"),
										AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")
									) as m_cc_number,								
									AES_DECRYPT('.$this->tableName.'.cc_cvv_code, \''.PASSWORDS_ENCRYPT_KEY.'\') as cc_cvv_code, 
									DATE_FORMAT('.$this->tableName.'.created_date, "%b %d, %Y <br/> %H:%i:%s") as created_date_formated,
									DATE_FORMAT(CONCAT('.$this->tableName.'.cc_expires_year, "-", '.$this->tableName.'.cc_expires_month, "-01"), "%b, %Y") as cc_expires,
									CONCAT('.TABLE_CUSTOMERS.'.last_name, " ", '.TABLE_CUSTOMERS.'.first_name, " ", IF('.TABLE_CUSTOMERS.'.customer_type = 1, " ('.htmlentities(_AGENCY).')", "")) as customer_name
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.$this->tableConferenceName.' ON '.$this->tableName.'.conference_id = '.$this->tableConferenceName.'.id
									LEFT OUTER JOIN '.$this->tableConferenceTypeName.' ON '.$this->tableConferenceName.'.conference_type_id = '.$this->tableConferenceTypeName.'.id
									LEFT OUTER JOIN '.$this->tableSpeciality.' ON '.$this->tableName.'.speciality_id = '.$this->tableSpeciality.'.speciality_id
									LEFT OUTER JOIN '.TABLE_BOOKINGS.' ON '.$this->tableName.'.booking_number = '.TABLE_BOOKINGS.'.booking_number
									LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
									LEFT OUTER JOIN '.TABLE_ACCOUNTS.' ON '.$this->tableName.'.customer_id = '.TABLE_ACCOUNTS.'.id AND '.$this->tableName.'.is_admin_registration = 1
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
		$this->arrDetailsModeFields = array(
            'conference_name'       => array('title'=>_CONFERENCE_NAME, 'type'=>'label'),
            'speciality_name'       => array('title'=>_SPECIALTY, 'type'=>'label'),
            'customer_name'         => array('title'=>_CUSTOMER, 'type'=>'label'),
            'created_date_formated' => array('title'=>_CREATED_DATE, 'type'=>'label'),
            'booking_number'        => array('title'=>_BOOKING_NUMBER, 'type'=>'label'),
            'cc_type'               => array('title'=>_CREDIT_CARD_TYPE, 'type'=>'label'),
            'cc_holder_name'        => array('title'=>_CREDIT_CARD_HOLDER_NAME, 'type'=>'label'),
            'm_cc_number'           => array('title'=>_CREDIT_CARD_NUMBER, 'type'=>'label', 'post_html'=>(($cc_number_trigger == 'show') ? '&nbsp;[ <a href="javascript:void(0);" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) __mgDoPostBack(\''.$this->tableName.'\', \'clean_credit_card\', \''.$rid.'\')">'._REMOVE.'</a> ]' : '')),
            'cc_expires'            => array('title'=>_CREDIT_CARD_EXPIRES, 'type'=>'label'),
            'cc_cvv_code'			=> array('title'=>_CVV_CODE, 'type'=>'label'),
            'additional_info'       => array('title'=>_ADDITIONAL_INFO, 'type'=>'label'),
			'course_registrations'  => array('title'=>_COURSE_REGISTRATION, 'type'=>'enum', 'source'=>$arr_course_registrations, 'view_type'=>'checkboxes', 'multi_select'=>true),
		);
    }

    //==========================================================================
    // Class Destructor
    //==========================================================================
	function __destruct()
	{
        // echo 'this object has been destroyed';
    }
		
	/**
	 * Cleans credit card info
	 * @param $rid
	 */
	public function CleanUpCreditCardInfo($rid)
	{
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'UPDATE '.$this->tableName.'
				SET
					cc_number = AES_ENCRYPT(SUBSTRING(AES_DECRYPT(cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\'), -4), \''.PASSWORDS_ENCRYPT_KEY.'\'),
					cc_cvv_code = \'\',
					cc_expires_month = \'\',
					cc_expires_year = \'\'
				WHERE '.$this->primaryKey.' = '.(int)$rid;
		if(database_void_query($sql)){
			return true;
		}else{
			$this->error = _TRY_LATER;
		}		
	}
}
