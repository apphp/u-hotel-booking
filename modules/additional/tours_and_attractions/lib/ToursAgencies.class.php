<?php

/**
 *	ToursAgencies Class
 *  --------------
 *  Description : encapsulates Tour Agencies class properties
 *  Updated	    : 27.02.2020
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllActive            ValidateTranslationFields
 *  __destruct              GetAgencyInfo
 *  BeforeInsertRecord      DrawAboutUs
 *  AfterInsertRecord       GetAgencyFullInfo
 *  BeforeUpdateRecord      AgenciesCount
 *  AfterUpdateRecord       DrawSearchAvailabilityBlock
 *  BeforeDeleteRecord      GetAllAgencies
 *  AfterDeleteRecord
 *  SearchFor
 *  DrawSearchResult
 *
 *
 **/


class ToursAgencies extends MicroGrid {

    protected $debug = false;

    private $arrTranslations = '';

    private static $sTableName = '';
    private static $sTableTranslationName = '';

    //==========================================================================
    // Class Constructor
    //==========================================================================
    function __construct()
    {
        parent::__construct();

        global $objLogin;

        $this->params = array();

        ## for standard fields

        if(isset($_POST['phone']))     $this->params['phone'] = prepare_input($_POST['phone']);
        if(isset($_POST['fax']))   	   $this->params['fax'] = prepare_input($_POST['fax']);
        if(isset($_POST['email']))     $this->params['email'] = prepare_input($_POST['email']);
        if(isset($_POST['map_code']))  $this->params['map_code'] = prepare_input($_POST['map_code'], false, 'low');
        if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
        if(isset($_POST['is_active']))   $this->params['is_active']  = (int)$_POST['is_active']; else $this->params['is_active'] = '0';

        ## for checkboxes
        //$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

        ## for images (not necessary)
        //if(isset($_POST['icon'])){
        //	$this->params['icon'] = prepare_input($_POST['icon']);
        //}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
        //	// nothing
        //}elseif(self::GetParameter('action') == 'create'){
        //	$this->params['icon'] = '';
        //}

        ## for files:
        // define nothing

        ///$this->params['language_id'] = MicroGrid::GetParameter('language_id');

        //$this->uPrefix 		= 'prefix_';

        $this->primaryKey 	= 'id';
        $this->tableName 	= DB_PREFIX.'tours_agencies';
        $this->tableTranslationName 	= DB_PREFIX.'tours_agencies_description';
        $this->tableTours 	= DB_PREFIX.'tours';
        $this->dataSet 		= array();
        $this->error 		= '';
        $this->formActionURL = 'index.php?admin=mod_tours_and_attractions_agencies_management';
        $this->actions      = array(
            'add'=>true,
            'edit'=>true,
            'details'=>true,
            'delete'=>true);
        $this->actionIcons  = true;
        $this->allowRefresh = true;
        $this->allowTopButtons = true;
        $this->alertOnDelete = ''; // leave empty to use default alerts

        $this->allowLanguages = false;
        $this->languageId = $objLogin->GetPreferredLang();

        $this->WHERE_CLAUSE = 'WHERE 1 = 1';

        $this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.priority_order ASC';

        $this->isAlterColorsAllowed = true;

        $this->isPagingAllowed = true;
        $this->pageSize = 20;

        $this->isSortingAllowed = true;

        $this->isExportingAllowed = true;
        $this->arrExportingTypes = array('csv'=>true);

        ///$this->isAggregateAllowed = false;
        ///// define aggregate fields for View Mode
        ///$this->arrAggregateFields = array(
        ///	'field1' => array('function'=>'SUM', 'align'=>'center'),
        ///	'field2' => array('function'=>'AVG', 'align'=>'center'),
        ///);

        ///$date_format = get_date_format('view');
        ///$date_format_settings = get_date_format('view', true); /* to get pure settings format */
        ///$date_format_edit = get_date_format('edit');
        ///$datetime_format = get_datetime_format();
        ///$time_format = get_time_format(); /* by default 1st param - shows seconds */
        ///$currency_format = get_currency_format();
        $default_currency = Currencies::GetDefaultCurrency();
        $this->currencyFormat = get_currency_format();

        $arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
        $arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
        $arr_default_types = array('0'=>_NO, '1'=>_YES);

        $this->isFilteringAllowed = true;
        // define filtering fields
        $this->arrFilteringFields = array(
            //_LOCATIONS  => array('table'=>$this->tableName, 'field'=>'agency_location_id', 'type'=>'dropdownlist', 'source'=>$arr_agencies_locations, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
        );

        ///////////////////////////////////////////////////////////////////////////////
        // #002. prepare translation fields array
        $this->arrTranslations = $this->PrepareTranslateFields(
            array('name', 'address', 'description', 'terms_and_conditions')
        );
        ///////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////////////////////////
        // #003. prepare translations array for add/edit/detail modes
        /// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
        /// $sql_translation_description = $this->PrepareTranslateSql(
        $sql_translation_description = $this->PrepareTranslateSql(
            $this->tableTranslationName,
            'agency_id',
            array('name', 'address', 'description', 'terms_and_conditions')
        );
        ///////////////////////////////////////////////////////////////////////////////

        //----------------------------------------------------------------------
        // VIEW MODE
        // format: strip_tags
        // format: nl2br
        // format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
        // format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
        //----------------------------------------------------------------------
        $this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.phone,
									'.$this->tableName.'.fax,
									'.$this->tableName.'.email,
									'.$this->tableName.'.map_code,
									'.$this->tableName.'.logo_image,
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.priority_order,
									'.$this->tableTranslationName.'.name,
									CONCAT("<a href=\"javascript:void();\" onclick=\"javascript:appGoToPage(\'index.php?admin=mod_tours_and_attractions_management\',\'&amp;mg_action=view&amp;mg_operation=filtering&amp;mg_search_status=active&amp;token='.Application::Get('token').'&amp;filter_by_'.DB_PREFIX.'tourstour_agency_id=", '.$this->tableName.'.'.$this->primaryKey.', "\',\'post\')\"",">[ '._TOURS.' ]</a>  (", (SELECT COUNT(*) as cnt FROM '.$this->tableTours.' t WHERE t.tour_agency_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_tours
								FROM ('.$this->tableName.'
									LEFT OUTER JOIN '.$this->tableTranslationName.' ON '.$this->tableName.'.id = '.$this->tableTranslationName.'.agency_id AND '.$this->tableTranslationName.'.language_id = \''.$this->languageId.'\')
								';

        // define view mode fields
        $this->arrViewModeFields = array(
            'logo_image' 			=> array('title'=>_LOGO, 'type'=>'image', 'align'=>'center', 'width'=>'80px', 'image_width'=>'60px', 'image_height'=>'30px', 'target'=>'images/tours_agencies/', 'no_image'=>'no_image.png'),
            'name'    				=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
            'email' 	  			=> array('title'=>_EMAIL, 'type'=>'link', 'maxlength'=>'70', 'href'=>'mailto:{email}', 'width'=>'210px', 'align'=>'left'),
            'phone'   				=> array('title'=>_PHONE, 'type'=>'label', 'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
            'fax'     				=> array('title'=>_FAX, 'type'=>'label', 'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
            'link_tours' 		    => array('title'=>_TOURS, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>false, 'visible'=>true),
            'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'50px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
            'priority_order' 		=> array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),
            'id'             		=> array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'30px'),
        );

        //----------------------------------------------------------------------
        // ADD MODE
        // - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
        // 	 Validation Sub-Type: positive (for numeric and float)
        //   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
        // - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
        // - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
        // - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
        //----------------------------------------------------------------------
        // define add mode fields
        $this->arrAddModeFields = array(

            'separator_1'   =>array(
                'separator_info' => array('legend'=>_AGENCY_INFO, 'columns'=>'0'),
                'phone'  		=> array('title'=>_PHONE, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
                'fax'  		   	=> array('title'=>_FAX, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
                'email' 		=> array('title'=>_EMAIL_ADDRESS,'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'70', 'validation_type'=>'email', 'unique'=>true),
                'logo_image'    => array('title'=>_LOGO, 'type'=>'image',    'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/tours_agencies/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'agency_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>false, 'thumbnail_field'=>'agency_image_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
                'map_code'      => array('title'=>_MAP_CODE, 'type'=>'textarea', 'required'=>false, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
                'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
                'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
            ),
        );

        //----------------------------------------------------------------------
        // EDIT MODE
        // - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
        //   Validation Sub-Type: positive (for numeric and float)
        //   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
        // - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
        // - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
        // - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
        // - for editable passwords they must be defined directly in SQL : '.$this->tableName.'.user_password,
        //----------------------------------------------------------------------
        $this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.phone,
								'.$this->tableName.'.fax,
								'.$this->tableName.'.email,
								'.$this->tableName.'.logo_image,
								'.$this->tableName.'.map_code,
								'.$sql_translation_description.'
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE
								'.$this->tableName.'.'.$this->primaryKey.' = _RID_';

        // define edit mode fields
        $this->arrEditModeFields = array(
            'separator_1'   => array(
                'separator_info'=> array('legend'=>_AGENCY_INFO, 'columns'=>'0'),
                'phone'  		=> array('title'=>_PHONE, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
                'fax'  		   	=> array('title'=>_FAX, 'type'=>'textbox',  'required'=>false, 'width'=>'170px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'text'),
                'email' 		=> array('title'=>_EMAIL_ADDRESS,'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'70', 'validation_type'=>'email', 'unique'=>true),
                'logo_image'   => array('title'=>_LOGO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/tours_agencies/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>true, 'image_name_pefix'=>'agency_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>false, 'thumbnail_field'=>'agency_image_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
                'map_code'      => array('title'=>_MAP_CODE, 'type'=>'textarea', 'required'=>false, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
                'priority_order'=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
                'is_active'     => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
            ),
        );

        //----------------------------------------------------------------------
        // DETAILS MODE
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
        $this->arrDetailsModeFields = array(
            'separator_1'   =>array(
                'separator_info' => array('legend'=>_AGENCY_INFO, 'columns'=>'0'),
                'phone'  		=> array('title'=>_PHONE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'fax'  		   	=> array('title'=>_FAX, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'email'     	=> array('title'=>_EMAIL_ADDRESS, 	 'type'=>'label'),
                'logo_image'   => array('title'=>_LOGO, 'type'=>'image', 'target'=>'images/tours_agencies/', 'no_image'=>'no_image.png', 'image_width'=>'120px', 'image_height'=>'90px', 'visible'=>true),
                'map_code'      => array('title'=>_MAP_CODE, 'type'=>'html', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
                'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
            ),
        );

        ///////////////////////////////////////////////////////////////////////////////
        // #004. add translation fields to all modes
        $this->AddTranslateToModes(
            $this->arrTranslations,
            array('name'        		 => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
                  'address' 			 => array('title'=>_ADDRESS, 'type'=>'textarea', 'width'=>'410px', 'height'=>'55px', 'required'=>false, 'maxlength'=>'225', 'validation_maxlength'=>'225', 'readonly'=>false),
                  'description' 		 => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'2048', 'validation_maxlength'=>'2048', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
                  'terms_and_conditions' => array('title'=>_TERMS, 'type'=>'textarea', 'width'=>'410px', 'height'=>'70px', 'required'=>false, 'maxlength'=>'2048', 'validation_maxlength'=>'5096', 'readonly'=>false, 'editor_type'=>'wysiwyg')
            )
        );
        ///////////////////////////////////////////////////////////////////////////////
    }


    //==========================================================================
    // Class Destructor
    //==========================================================================
    function __destruct()
    {
        // echo 'this object has been destroyed';
    }

    /**
     * Returns all array of all active agencies
     * @param $where_clause
     */
    public static function GetAllAgencies($where_clause = '')
    {
        self::setTableNames();

        $sql = 'SELECT
					'.self::$sTableName.'.*,
					'.self::$sTableTranslationName.'.name,
					'.self::$sTableTranslationName.'.description,
					'.self::$sTableTranslationName.'.terms_and_conditions
				FROM '.self::$sTableName.'
					INNER JOIN '.self::$sTableTranslationName.' ON '.self::$sTableName.'.id = '.self::$sTableTranslationName.'.agency_id AND '.self::$sTableTranslationName.'.language_id = \''.Application::Get('lang').'\'
				WHERE 1=1 
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.self::$sTableName.'.priority_order ASC ';

        return database_query($sql, DATA_AND_ROWS);
    }

    /**
     * Returns all array of all active agencies
     * @param $where_clause
     */
    public static function GetAllActive($where_clause = '')
    {
        self::setTableNames();

        $sql = 'SELECT
					'.self::$sTableName.'.*,
					'.self::$sTableTranslationName.'.name,
					'.self::$sTableTranslationName.'.description,
					'.self::$sTableTranslationName.'.terms_and_conditions
				FROM '.self::$sTableName.'
					INNER JOIN '.self::$sTableTranslationName.' ON '.self::$sTableName.'.id = '.self::$sTableTranslationName.'.agency_id AND '.self::$sTableTranslationName.'.language_id = \''.Application::Get('lang').'\'
				WHERE
					'.self::$sTableName.'.is_active = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '').'
				ORDER BY '.self::$sTableName.'.priority_order ASC ';

        return database_query($sql, DATA_AND_ROWS);
    }

    /**
     * Returns agency info
     * @param $agency_id
     */
    public static function GetAgencyInfo($agency_id = '')
    {
        self::setTableNames();

        $output = array();
        $sql = 'SELECT *
				FROM '.self::$sTableName.'
				WHERE '.(!empty($agency_id) ? ' id ='.(int)$agency_id : ' 1=1');

        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $output = $result[0];
        }
        return $output;
    }

    /**
     * Returns agency full info
     * @param $agency_id
     * @param $lang
     */
    public static function GetAgencyFullInfo($agency_id = '', $lang = '')
    {
        self::setTableNames();

        $output = array();
        $lang = !empty($lang) ? $lang : Application::Get('lang');
        $sql = "SELECT
					a.*,
					ad.name,
					ad.address,
					ad.description,
					ad.terms_and_conditions
				FROM ".self::$sTableName." as a
					INNER JOIN ".self::$sTableTranslationName." ad ON a.id = ad.agency_id AND ad.language_id = '".$lang."' 
				WHERE 1=1
				".(!empty($agency_id) ? ' AND a.id = '.(int)$agency_id : '')."
				LIMIT 0, 1";

        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $output = $result[0];
        }
        return $output;
    }

    /**
     * Returns agencys count
     */
    public static function AgenciesCount()
    {
        self::setTableNames();

        $sql = 'SELECT COUNT(*) as cnt FROM '.self::$sTableName.' WHERE is_active = 1';
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            return (int)$result[0]['cnt'];
        }
        return 0;
    }


    //==========================================================================
    // MicroGrid Methods
    //==========================================================================
    /**
     * Validate translation fields
     */
    private function ValidateTranslationFields()
    {
        foreach($this->arrTranslations as $key => $val){
            if(trim($val['name']) == ''){
                $this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
                $this->errorField = 'name_'.$key;
                return false;
            }
        }
        return true;
    }

    /**
     * Before-Insertion
     */
    public function BeforeInsertRecord()
    {
        return $this->ValidateTranslationFields();
    }

    /**
     * After-Insertion - add banner descriptions to description table
     */
    public function AfterInsertRecord()
    {
        $sql = 'INSERT INTO '.$this->tableTranslationName.'(id, agency_id, language_id, name, address, description, terms_and_conditions) VALUES ';
        $count = 0;
        foreach($this->arrTranslations as $key => $val){
            if($count > 0) $sql .= ',';
            $sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['address'])).'\', \''.encode_text(prepare_input($val['description'], false, 'medium')).'\', \''.encode_text(prepare_input($val['terms_and_conditions'], false, 'medium')).'\')';
            $count++;
        }
        if(database_void_query($sql)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Before-Updating operations
     */
    public function BeforeUpdateRecord()
    {
        return $this->ValidateTranslationFields();
    }

    /**
     * After-Updating - update agencies item descriptions to description table
     */
    public function AfterUpdateRecord()
    {
        foreach($this->arrTranslations as $key => $val){
            $sql = 'UPDATE '.$this->tableTranslationName.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						address = \''.encode_text(prepare_input($val['address'], false, 'medium')).'\',
						description = \''.encode_text(prepare_input($val['description'], false, 'medium')).'\',
						terms_and_conditions = \''.encode_text(prepare_input($val['terms_and_conditions'], false, 'medium')).'\'
					WHERE agency_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
            database_void_query($sql);
        }
    }


    /**
     * Before-Deleting Record
     */
    public function BeforeDeleteRecord()
    {
        return true;
    }

    /**
     * After-Deleting Record
     */
    public function AfterDeleteRecord()
    {
        $sql = 'SELECT id, is_active FROM '.$this->tableName;
        if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
            if((int)$result[1] == 1){
                // make last agency always default and active
                $sql = 'UPDATE '.$this->tableName.' SET is_active = \'1\' WHERE id= '.(int)$result[0][0]['id'];
                database_void_query($sql);
            }
        }
    }

    static private function setTableNames()
    {
        self::$sTableName = DB_PREFIX.'tours_agencies';
        self::$sTableTranslationName	= DB_PREFIX.'tours_agencies_description';
    }
}
