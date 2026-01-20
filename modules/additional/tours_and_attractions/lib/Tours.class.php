<?php

/**
 *	ToursTypes Class
 *  --------------
 *  Description : encapsulates Tour Types class properties
 *  Updated	    : 26.10.2020
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct				GetAllActive
 *  __destruct              GetTourFullInfo
 *  BeforeInsertRecord
 *  AfterInsertRecord
 *  BeforeEditRecord
 *  BeforeUpdateRecord
 *  AfterUpdateRecord
 *  BeforeDetailsRecord
 *  BeforeDeleteRecord
 *  AfterDeleteRecord
 *
 *
 *
 **/


class Tours extends MicroGrid {

    protected $debug = false;

    private $arrTranslations = '';
    private $currencyFormat = '';

    //==========================================================================
    // Class Constructor
    //==========================================================================
    function __construct()
    {
        parent::__construct();

        $this->params = array();

        ## for standard fields
        if(isset($_POST['tour_agency_id']))     $this->params['tour_agency_id'] = (int)$_POST['tour_agency_id'];
        if(isset($_POST['tour_type_id']))       $this->params['tour_type_id'] = (int)$_POST['tour_type_id'];
        if(isset($_POST['tour_location_id']))   $this->params['tour_location_id'] = prepare_input($_POST['tour_location_id']);
        if(isset($_POST['price_per_person']))   $this->params['price_per_person'] = prepare_input($_POST['price_per_person']);
        if(isset($_POST['map_code']))           $this->params['map_code'] = prepare_input($_POST['map_code'], false, 'low');
        if(isset($_POST['latitude']))           $this->params['latitude'] = prepare_input($_POST['latitude']);
        if(isset($_POST['longitude']))          $this->params['longitude'] = prepare_input($_POST['longitude']);

        if(isset($_POST['priority_order'])) 	$this->params['priority_order'] = prepare_input($_POST['priority_order']);
        if(isset($_POST['is_active']))   		$this->params['is_active'] = (int)$_POST['is_active']; else $this->params['is_active'] = '0';

        ## for checkboxes
        //$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

        ## for images (not necessary)
        //if(isset($_POST['icon'])){
        //	$this->params['icon'] = prepare_input($_POST['icon']);
        //}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
        //	// nothing
        //}elseif (self::GetParameter('action') == 'create'){
        //	$this->params['icon'] = '';
        //}

        ## for files:
        // define nothing

        $this->params['language_id'] = MicroGrid::GetParameter('language_id');

        //$this->uPrefix 		= 'prefix_';

        $this->primaryKey 	= 'id';
        $this->tableName 	= DB_PREFIX.'tours';
        $this->tableDescription = DB_PREFIX.'tours_description';
        $this->tableImages 	= DB_PREFIX.'tours_images';
        $this->tableLocationDescription = DB_PREFIX.'tours_locations_description';
        $this->tableToursTypesDescription = DB_PREFIX.'tours_types_description';
        $this->tableAgencyDescription = DB_PREFIX.'tours_agencies_description';

        $this->dataSet 		= array();
        $this->error 		= '';
        $this->formActionURL = 'index.php?admin=mod_tours_and_attractions_management';
        $this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
        $this->actionIcons  = true;
        $this->allowRefresh = true;
        $this->allowPrint   = false;
        $this->allowTopButtons = false;
        $this->alertOnDelete = ''; // leave empty to use default alerts

        $this->allowLanguages = false;
        $this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
        $this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';
        $this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
        $this->ORDER_CLAUSE = ''; // ORDER BY '.$this->tableName.'.date_created DESC

        $this->isAlterColorsAllowed = true;

        // $this->isResponsiveTable = true; // optional, determines whether the MicroGrid will draw a responsive table

        $this->isPagingAllowed = true;
        $this->pageSize = 20;

        $this->isSortingAllowed = true;

        // exporting settings
        $this->isExportingAllowed = false;
        $this->arrExportingTypes = array('csv'=>false);

        // prepare types array
        $property_types = ToursTypes::GetAllTypes();
        $arr_tour_types = array();
        $arr_tour_types_wm = array();
        foreach($property_types[0] as $key => $val){
            $arr_tour_types[$val['id']] = $val['name'];
        }

        $tour_agencies = ToursAgencies::GetAllAgencies();
        $arr_tour_agencies = array();
        foreach($tour_agencies[0] as $key => $val){
            $arr_tour_agencies[$val['id']] = $val['name'];
        }

        // prepare locations array
        $total_tour_locations = ToursLocations::GetAllLocations();
        $arr_tour_locations = [];
        $arr_tour_locations_vm = [];
        foreach($total_tour_locations[0] as $key => $val){
            $arr_tour_locations[$val['country_name']][$val['id']] = $val['name'];
            $arr_tour_locations_vm[$val['id']] = $val['name'].'<br><small>'.$val['country_name'].'</small>';
        }

        // define filtering fields
        $this->isFilteringAllowed = true;
        ///$this->maxFilteringColumns = 4; /* optional, defines split columns default = 0 */
        $this->arrFilteringFields = array(
            _NAME       => array('table'=>$this->tableDescription, 'field'=>'name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'140px', 'visible'=>true),
            _LOCATIONS  => array('table'=>$this->tableName, 'field'=>'tour_location_id', 'type'=>'dropdownlist', 'source'=>$arr_tour_locations, 'sign'=>'=', 'width'=>'140px', 'visible'=>true),
            _TOUR_TYPES => array('table'=>$this->tableName, 'field'=>'tour_type_id', 'type'=>'dropdownlist', 'source'=>$arr_tour_types, 'sign'=>'=', 'width'=>'170px', 'visible'=>true),
            _TOUR_AGENCIES => array('table'=>$this->tableName, 'field'=>'tour_agency_id', 'type'=>'dropdownlist', 'source'=>$arr_tour_agencies, 'sign'=>'=', 'width'=>'170px', 'visible'=>true),
        );

        ///$this->isAggregateAllowed = false;
        ///// define aggregate fields for View Mode
        ///$this->arrAggregateFields = array(
        ///	'field1' => array('function'=>'SUM', 'align'=>'center', 'aggregate_by'=>'', 'decimal_place'=>2),
        ///	'field2' => array('function'=>'AVG', 'align'=>'center', 'aggregate_by'=>'', 'decimal_place'=>2),
        ///);

        $arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
        $arr_default_types_vm = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
        $arr_default_types = array('0'=>_NO, '1'=>_YES);

        ///$date_format = get_date_format('view');
        ///$date_format_settings = get_date_format('view', true); /* to get pure settings format */
        ///$date_format_edit = get_date_format('edit');
        ///$datetime_format = get_datetime_format();
        ///$time_format = get_time_format(); /* by default 1st param - shows seconds */
        ///$currency_format = get_currency_format();
        $default_currency = Currencies::GetDefaultCurrency();
        $this->currencyFormat = get_currency_format();


        // prepare languages array
        //$total_languages = Languages::GetAllActive();
        //$arr_languages      = array();
        //foreach($total_languages[0] as $key => $val){
        //    $arr_languages[$val['abbreviation']] = $val['lang_name'];
        //}

        ///////////////////////////////////////////////////////////////////////////////
        // #002. prepare translation fields array
        $this->arrTranslations = $this->PrepareTranslateFields(
            array('name', 'address', 'description')
        );
        ///////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////////////////////////
        // #003. prepare translations array for add/edit/detail modes
        /// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
        $sql_translation_description = $this->PrepareTranslateSql(
            DB_PREFIX.'tours_description',
            'tour_id',
            array('name', 'address', 'description')
        );
        ///////////////////////////////////////////////////////////////////////////////

        //----------------------------------------------------------------------
        // VIEW MODE
        // format: strip_tags, nl2br, readonly_text, readonly_html
        // format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
        // format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
        //----------------------------------------------------------------------
        $this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.tour_agency_id,
									'.$this->tableName.'.tour_type_id,
									'.$this->tableName.'.tour_location_id,
									'.$this->tableName.'.price_per_person,
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.priority_order,
									'.$this->tableDescription.'.name,
									CONCAT("<a href=index.php?admin=mod_tours_and_attractions_upload_images&tid=", '.$this->tableName.'.'.$this->primaryKey.', ">[ '._UPLOAD.' ]</a> (", (SELECT COUNT(*) as cnt FROM '.$this->tableImages.' ti WHERE ti.tour_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_upload_images
								FROM ('.$this->tableName.'
									INNER JOIN '.$this->tableDescription.' ON '.$this->tableName.'.id = '.$this->tableDescription.'.tour_id AND '.$this->tableDescription.'.language_id = \''.$this->languageId.'\')
								';

        // define view mode fields
        $this->arrViewModeFields = array(
            'name'    				=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
            'tour_agency_id' 		=> array('title'=>_AGENCY, 'type'=>'enum',  'align'=>'left', 'width'=>'180px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_tour_agencies),
            'tour_type_id' 		    => array('title'=>_TYPE, 'type'=>'enum',  'align'=>'left', 'width'=>'190px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_tour_types),
            'tour_location_id' 	    => array('title'=>_LOCATION, 'type'=>'enum',  'align'=>'left', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_tour_locations_vm),
            'price_per_person'      => array('title'=>_PRICE,  'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>$default_currency),
            'link_upload_images'  	=> array('title'=>_IMAGES, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'sortable'=>false),
            'is_active'      		=> array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
            'priority_order' 		=> array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),
            'id'             		=> array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'30px'),
        );

        //----------------------------------------------------------------------
        // ADD MODE
        // - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date|url
        // 	 Validation Sub-Type: positive (for numeric and float)
        //   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
        // - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
        // - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
        // - Validation Min Value: 1, 10... Ex.: 'validation_minimum'=>'1'
        // - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
        //----------------------------------------------------------------------
        // define add mode fields
        $this->arrAddModeFields = array(
            'separator_1'   => array(
                'separator_info'    => array('legend'=>_TOUR_INFO, 'columns'=>'0'),
                'tour_agency_id'    => array('title'=>_TOUR_AGENCY, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'0',  'source'=>$arr_tour_agencies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'tour_type_id'      => array('title'=>_TOUR_TYPE, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'0',  'source'=>$arr_tour_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'priority_order' 	=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
                'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
            ),
            'separator_2'   =>array(
                'separator_info'    => array('legend'=>_LOCATION),
                'tour_location_id'  => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_tour_locations, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'latitude'          => array('title'=>_LATITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
                'longitude'         => array('title'=>_LONGITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
                'map_code'          => array('title'=>_MAP_CODE, 'type'=>'textarea', 'header_tooltip'=>_MAP_CODE_TOOLTIP, 'required'=>false, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
            ),
            'separator_3'   =>array(
                'separator_info' => array('legend'=>_PRICES),
                'price_per_person'  => array('title'=>_PRICE, 'type'=>'textbox',  'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
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
								'.$sql_translation_description.'
                                '.$this->tableName.'.tour_agency_id,
                                '.$this->tableName.'.tour_type_id,
                                '.$this->tableName.'.tour_location_id,
                                '.$this->tableName.'.price_per_person,
								'.$this->tableName.'.map_code,
								'.$this->tableName.'.latitude,
								'.$this->tableName.'.longitude,
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE
								'.$this->tableName.'.'.$this->primaryKey.' = _RID_';

        // define edit mode fields
        $this->arrEditModeFields = array(
            'separator_1'   => array(
                'separator_info'    => array('legend'=>_TOUR_INFO, 'columns'=>'0'),
                'tour_agency_id'    => array('title'=>_TOUR_AGENCY, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'0',  'source'=>$arr_tour_agencies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'tour_type_id'      => array('title'=>_TOUR_TYPE, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'0',  'source'=>$arr_tour_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'priority_order' 	=> array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'50px', 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
                'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
            ),
            'separator_2'   => array(
                'separator_info'    => array('legend'=>_LOCATION),
                'tour_location_id'  => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_tour_locations, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
                'latitude'          => array('title'=>_LATITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
                'longitude'         => array('title'=>_LONGITUDE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'20', 'validation_type'=>'float', 'unique'=>false),
                'map_code'          => array('title'=>_MAP_CODE, 'type'=>'textarea', 'required'=>false, 'header_tooltip'=>_MAP_CODE_TOOLTIP, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
            ),
            'separator_3'   =>array(
                'separator_info' => array('legend'=>_PRICES),
                'price_per_person'  => array('title'=>_PRICE, 'type'=>'textbox',  'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$default_currency.' '),
            ),
        );

        //----------------------------------------------------------------------
        // DETAILS MODE
        // format: strip_tags, nl2br, readonly_text, readonly_html
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
        $this->arrDetailsModeFields = array(
            'separator_1'   => array(
                'separator_info'    => array('legend'=>_TOUR_INFO, 'columns'=>'0'),
                'tour_agency_id'    => array('title'=>_TOUR_AGENCY, 'type'=>'enum', 'source'=>$arr_tour_agencies),
                'tour_type_id'      => array('title'=>_TOUR_TYPE, 'type'=>'enum', 'source'=>$arr_tour_types),
                'priority_order' 	=> array('title'=>_ORDER, 'type'=>'label'),
                'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
            ),
            'separator_2'   =>array(
                'separator_info'    => array('legend'=>_LOCATION),
                'tour_location_id'  => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'source'=>$arr_tour_locations),
                'map_code'          => array('title'=>_MAP_CODE, 'type'=>'html', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'latitude'          => array('title'=>_LATITUDE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
                'longitude'         => array('title'=>_LONGITUDE, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
            ),
            'separator_3'   =>array(
                'separator_info' => array('legend'=>_PRICES),
                'price_per_person'  => array('title'=>_DEFAULT_PRICE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$this->currencyFormat.'|2', 'pre_html'=>$default_currency),
            ),
        );

        ///////////////////////////////////////////////////////////////////////////////
        // #004. add translation fields to all modes
        $this->AddTranslateToModes(
            $this->arrTranslations,
            array('name'       	=> array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
                  'address'           => array('title'=>_ADDRESS, 'type'=>'textarea', 'width'=>'410px', 'height'=>'55px', 'required'=>false, 'maxlength'=>'225', 'validation_maxlength'=>'225', 'readonly'=>false),
                  'description'	=> array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'10000', 'validation_maxlength'=>'10000', 'readonly'=>false, 'editor_type'=>'wysiwyg')
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


    //==========================================================================
    // MicroGrid Methods
    //==========================================================================
    /***
     *	Get all DataSet array
     *
     *  public function GetAll() { }
     *
     **/

    /**
     * #005 Validate translation fields
     **/
    private function ValidateTranslationFields()
    {
        // check for required fields
        foreach($this->arrTranslations as $key => $val){
            if($val['name'] == ''){
                $this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
                $this->errorField = 'name_'.$key;
                return false;
            }elseif(strlen($val['name']) > 125){
                $this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
                $this->error = str_replace('_LENGTH_', 125, $this->error);
                $this->errorField = 'name_'.$key;
                return false;
            }elseif(strlen($val['description']) > 10000){
                $this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
                $this->error = str_replace('_LENGTH_', 10000, $this->error);
                $this->errorField = 'description_'.$key;
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
        $sql = 'INSERT INTO '.$this->tableDescription.'(id, tour_id, language_id, name, address, description) VALUES ';
        $count = 0;
        foreach($this->arrTranslations as $key => $val){
            if($count > 0) $sql .= ',';
            $sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['address'])).'\', \''.encode_text(prepare_input($val['description'], false, 'medium')).'\')';
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
            $sql = 'UPDATE '.$this->tableDescription.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
					    address = \''.encode_text(prepare_input($val['address'])).'\',  
						description = \''.encode_text(prepare_input($val['description'], false, 'medium')).'\'
					WHERE tour_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
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
        // Delete info from agency description table
        $sql = 'DELETE FROM '.$this->tableDescription.' WHERE tour_id = '.$this->curRecordId;
        if(database_void_query($sql)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Returns all active tours
     * @param string $where
     */
    public static function GetAllActive($where = '')
    {
        $lang = Application::Get('lang');

        $tableName 	= DB_PREFIX.'tours';
        $tableDescription = DB_PREFIX.'tours_description';
        $tableLocationDescription = DB_PREFIX.'tours_locations_description';

        $sql = 'SELECT
					t.*,
					td.name,
					td.address,
					td.description,
					tld.name as location_name
				FROM '.$tableName.' t
					INNER JOIN '.$tableDescription.' td ON t.id = td.tour_id AND td.language_id = \''.$lang.'\'            
					LEFT OUTER JOIN '.$tableLocationDescription.' tld ON t.tour_location_id = tld.tour_location_id AND tld.language_id = \''.$lang.'\'
				WHERE is_active = 1';

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

        return $result;
    }

    /**
     * Returns price for Tour
     * @param int $tour_id
     * @param array $params
     * @return float
     */
    public static function GetTourPrice($tour_id, $params)
    {
        $price = 0.00;
        $tableName 	= DB_PREFIX.'tours';
        $days = !empty($params['days']) ? $params['days'] : 1;

        $sql = 'SELECT * FROM '.$tableName.' WHERE id = '.(int)$tour_id.' AND is_active = 1';
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $price = isset($result[0]['price_per_person']) ? $result[0]['price_per_person'] * $days : array();
        }

        return $price;
    }

    /**
     * Returns Tour full info
     * @param $tour_id
     * @param $lang
     */
    public static function GetTourFullInfo($tour_id = '', $lang = '')
    {
        $output = array();
        if(empty($lang)){
            $lang = Application::Get('lang');
        }

        $tableName 	= DB_PREFIX.'tours';
        $tableDescription = DB_PREFIX.'tours_description';
        $tableTypesDescription = DB_PREFIX.'tours_types_description';
        $tableImages 	= DB_PREFIX.'tours_images';
        $tableLocationDescription = DB_PREFIX.'tours_locations_description';
        $tableAgencyDescription = DB_PREFIX.'tours_agencies_description';

        $sql = 'SELECT
					t.*,
					td.name,
					td.address,
					td.description,
					tt.name as type_name,
					tld.name as location_name,
                    tad.name as tour_agency_name
				FROM '.$tableName.' t
					INNER JOIN '.$tableDescription.' td ON t.id = td.tour_id AND td.language_id = \''.$lang.'\'            
					LEFT OUTER JOIN '.$tableTypesDescription.' tt ON t.tour_type_id = tt.tour_type_id AND tt.language_id = \''.$lang.'\'
					LEFT OUTER JOIN '.$tableLocationDescription.' tld ON t.tour_location_id = tld.tour_location_id AND tld.language_id = \''.$lang.'\'
					LEFT OUTER JOIN '.$tableAgencyDescription.' tad ON t.tour_agency_id = tad.agency_id AND tad.language_id = \''.$lang.'\'
				WHERE 1=1
				    '.(!empty($tour_id) ? ' AND t.id = \''.(int)$tour_id.'\' ' : ' ').'
				LIMIT 0, 1';


        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $output = $result[0];
        }
        return $output;
    }

    /**
     * Returns max price for tours
     */
    public static function GetMaxPrice()
    {
        $tableName 	= DB_PREFIX.'tours';
        $sql = 'SELECT MAX(price_per_person) as max_price FROM '.$tableName.' WHERE is_active = 1';
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            return (int)$result[0]['max_price'];
        }
        return 0;
    }

    /**
     * Iteration counter for tour
     * @param int $tid
     * @return void
     */
    public static function NumberViewsIteration($tid = 0)
    {
        $tableName 	= DB_PREFIX.'tours';
        if(!empty($tid) && is_numeric($tid)){
            $sql = 'UPDATE '.$tableName.' SET number_of_views = number_of_views + 1 WHERE id = '.(int)$tid;
            return database_void_query($sql);
        }
        return false;
    }


    /**
     * DrawLocationsDDL
     * @param $arr_tour_locations
     *
     * @return string
     */
    private static function DrawLocationsDDL($arr_tour_locations = [], $tour_location = ''){
        $output = '<label>'._LOCATION.':</label><br>
        <select name="tour_location" class="_form-control mgrid_select" style="width:100%;">';
        $output .= '<option value="">-- '._SELECT.' --</option>';
        foreach($arr_tour_locations as $key => $val){
            $output .= '<optgroup label="'.ucfirst($key).'">';
            foreach($val as $kkey => $kval){
                $output .= '<option value="'.$kkey.'"'.($kkey == $tour_location ? ' selected' : '').'>'.$kval.'</option>';
            }
            $output .= '</optgroup>';
        }
        $output .= '</select>';

        return $output;
    }

    /**
     * Draws search availability form
     * @param $type
     * @param $action_url
     * @param $target
     */
    public static function DrawSearchAvailabilityBlock($type = 'home',  $action_url = '', $target = '')
    {
        global $objSettings;

        $output = '';
        $action_url = ($action_url != '') ? $action_url : APPHP_BASE;
        $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;
        $lang = Application::Get('lang');
        $nl ="\n";

        $tour_location     = isset($_POST['tour_location']) ? prepare_input($_POST['tour_location']) : 0;
        $tour_types_select = isset($_POST['tour_types']) ? prepare_input($_POST['tour_types']) : [];
        $tour_from_date    = isset($_POST['tour_from_date']) ? prepare_input($_POST['tour_from_date']) : '';
        $tour_to_date      = isset($_POST['tour_to_date']) ? prepare_input($_POST['tour_to_date']) : '';
        if (empty($tour_from_date) && empty($tour_to_date)) {
            $tour_from_date    = date($american_format ? 'm/d/Y' : 'd/m/Y');
            $tour_to_date      = date($american_format ? 'm/d/Y' : 'd/m/Y', strtotime('+1 day'));
        }
        $current_currency  = isset($_POST['current_currency']) ? prepare_input($_POST['current_currency'], true) : Application::Get('currency_code');

        $price_select           = isset($_POST['tour_price']) ? prepare_input($_POST['tour_price']) : 0;
        if(!empty($price_select)){
            $arr_price = explode(';',$price_select);
        }
        $min_price_select = isset($arr_price[0]) && is_numeric($arr_price[0]) ? $arr_price[0] : 0;
        $max_price_select = isset($arr_price[1]) && is_numeric($arr_price[1]) ? $arr_price[1] : 0;

//        $make_sort_select  = !empty($_POST['sort_make']) && in_array(strtolower($_POST['sort_make']), array('asc', 'desc')) ? strtolower($_POST['sort_make']) : '';
        $price_sort_select = !empty($_POST['sort_price']) && in_array(strtolower($_POST['sort_price']), array('asc', 'desc')) ? strtolower($_POST['sort_price']) : '';
//        $view_type_select = !empty($_POST['view_type']) ? prepare_input($_POST['view_type']) : 'list';

        // prepare locations array
        $total_tour_locations = ToursLocations::GetAllLocations();
        $arr_tour_locations = [];
        foreach($total_tour_locations[0] as $key => $val){
            $arr_tour_locations[$val['country_name']][$val['id']] = $val['name'];
        }


        if($type == 'home'){
            // Home page tab
            $output = '
			<div tabindex="5002" style="overflow-y: hidden;" id="tour" class="tab-pane fade">
				<form'.(!empty($target) ? ' target="'.$target.'"' : '').' action="'.$action_url.'index.php?page=check_tours_availability" id="tours-reservation-form" name="tours-reservation-form" method="post">
				'.draw_hidden_field('p', '1', false, 'page_number').'
				'.draw_token_field(false).'

                <div class="w100percent" style="margin-bottom:10px;">
                '._FIND_TOUR_OR_ATTRACTION_NOTICE.' 
                </div>

				<div class="clearfix"></div>
				<hr>
				<div class="clearfix"></div>				

				<div class="w100percent" style="margin-bottom:10px;">
				    '.self::DrawLocationsDDL($arr_tour_locations, $tour_location).'
				</div>
			
                <div class="w100percent">
                    <div class="textleft">
                        <label>'._FROM.'</label>
                        <input tyle="padding: 6px 4px 6px 4px;min-width:120px !important;" class="form-control mySelectCalendar" name="tour_from_date" id="tour_from_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.htmlentities($tour_from_date).'" autocomplete="off">
                    </div>
                </div>

                <div class="w100percent">
                    <div class="textleft">
                        <label>'._TO.'</label>
                        <input tyle="padding: 6px 4px 6px 4px;min-width:120px !important;" class="form-control mySelectCalendar" name="tour_to_date" id="tour_to_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.htmlentities($tour_to_date).'" autocomplete="off">
                    </div>
                </div>
				
				<div class="clearfix pbottom15"></div>
				<br>
				
				<input class="button-availability" type="submit" value="'.htmlentities(_SEARCH).'" type="button" />
				</form>
			</div>';

        }else{
            $output .= '
			<div id="collapse0" class="side_box_content collapse in">
				<form action="'.$action_url.'index.php?page=check_tours_availability" id="tour-reservation-form" name="tours-reservation-form" method="post">
				'.draw_hidden_field('p', '1', false, 'page_number').'
				'.draw_hidden_field('view_type', '', false, 'view_type').'
				'.draw_hidden_field('sort_price', $price_sort_select, false, 'sort_price').'
				'.draw_hidden_field('current_currency', Application::Get('currency_code'), false, 'current_currency').'
				'.draw_token_field(false).'
				<div>';

            // LOCATIONS
            $output .= '
                <div style="margin-bottom:10px;">
                    <div class="wh100percent textleft">
                        '.self::DrawLocationsDDL($arr_tour_locations, $tour_location).'
                    </div>
                </div>';

            // FROM / TO
            $output .= '
                <!--<div class="clearfix pbottom15"></div>-->
                <div class="w100percent">
                    <div class="textleft">
                        <label>'._FROM.'</label>
                        <input tyle="padding: 6px 4px 6px 4px;min-width:120px !important;" class="form-control mySelectCalendar" name="tour_from_date" id="tour_from_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.htmlentities($tour_from_date).'" autocomplete="off">
                    </div>
                </div>

                <div class="w100percent">
                    <div class="textleft">
                        <label>'._TO.'</label>
                        <input tyle="padding: 6px 4px 6px 4px;min-width:120px !important;" class="form-control mySelectCalendar" name="tour_to_date" id="tour_to_date" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.htmlentities($tour_to_date).'" autocomplete="off">
                    </div>
                </div>

                <div class="clearfix pbottom15"></div>
                <div class="clearfix pbottom15"></div>

                <div class="w100percentlast">
                    <div class="w100percentlast">
                        <input class="button-availability" type="submit" value="'.htmlentities(_SEARCH).'" type="button" />
                    </div>
                </div>
            </div>
            <div class="line2"></div>
            <!-- END OF TOURS TAB -->';

            if($type == 'menu' && Application::Get('page') == 'check_tours_availability'){
                $allTypes = [];
                $min_price = $max_price = 0;

                // Prepare types
                $result = ToursTypes::GetAllTypes();
                if($result[1] > 0){
                    foreach($result[0] as $type){
                        $allTypes[$type['id']] = $type['name'];
                    }
                }

                $max_price = self::GetMaxPrice();

                $currency_symbol = Application::Get('currency_symbol');
                $currency_code = Application::Get('currency_code');
                $currency_rate = Application::Get('currency_rate');
                if($currency_code != $current_currency){
                    $current_currency_info = Currencies::GetCurrencyInfo($current_currency);
                    if($current_currency_info['code'] != $currency_code){
                        $min_price_select = floor($min_price_select * ($currency_rate / $current_currency_info['rate']));
                        $max_price_select = ceil($max_price_select * ($currency_rate / $current_currency_info['rate']));
                    }
                }

                $to_price = ceil($max_price * $currency_rate / 10) * 10;

                $min_price_select = !empty($min_price_select) && $min_price < $min_price_select ? $min_price_select : $min_price;
                $max_price_select = !empty($max_price_select) && $to_price > $max_price_select ? $max_price_select : $to_price;

                $output .= '<h3 class="side_box_heading collapsebtn" data-target="#collapse10_1" data-toggle="collapse">'._FILTER_BY.' <span class="collapsearrow"></span></h3>';
                $output .= '<div  id="collapse10_1" class="collapse in">';

                $output .= '<!-- Price range -->
                    <label>'._PRICE_RANGE.'</label>
                    <div class="padding20">
                        <div class="layout-slider wh100percent">
                            <span class="cstyle09"><input id="tour-price-slider" type="slider" name="tour_price" value="'.(int)$min_price_select.';'.(int)$max_price_select.'" /></span>
                        </div>
                        <script type="text/javascript" >
                          jQuery("#tour-price-slider").slider({ from: 0, to: '.$to_price.', step: 1, smooth: true, round: 0, dimension: "&nbsp;'.$currency_symbol.'", skin: "round" });
                        </script>
                    </div>';
                //<!-- End of Price range -->';

                if(!empty($allTypes)){
                    $output .= '<label>'._TOUR_TYPES.'</label>';
                    $output .= '<div class="pl5r0">';
                    foreach($allTypes as $key => $type_name){
                        $output .= '<div class="checkbox">
                                    <label><input type="checkbox"'.(in_array($key, $tour_types_select) ? ' checked="checked"' : '').' name="tour_types[]" value="'.htmlentities($key).'">'.htmlentities($type_name).'</label>
                                </div>';
                    }
                    $output .= '</div>';
                }

                $output .= '<div class="clearfix pbottom15"></div>';
                if (!empty($_POST)) {
                    $output .= '<input class="button-availability" type="submit" value="'._APPLY_FILTERS.'" />';
                }

                $output .= '</div>';
            }

            $output .= '</form>';
            $output .= '</div>';
        }

        $output .= '<script>
		jQuery(document).ready(function(){
			jQuery(function() {
				jQuery("#tour_from_date").datepicker({minDate: 0, maxDate: "+1y"});
				jQuery("#tour_to_date").datepicker({minDate: 1, maxDate: "+1y +1d"});
				
                jQuery("#tour_from_date").change(function(){
                    var checkin_val = jQuery("#tour_from_date").val();
                    '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' ? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4"); jQuery("#tour_from_date").val(checkin_val);' : '').'
                });				
			
                jQuery("#tour_to_date").change(function(){
                    var checkin_val = jQuery("#tour_to_date").val();
                    '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' ? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4"); jQuery("#tour_to_date").val(checkin_val);' : '').'
                });				
			});
		});
		</script>';

        return $output;
    }


    /**
     * Draw hotel map
     * @param $data
     * @param $params
     * @param $draw
     */
    public static function DrawMap($data = array(), $params = array(), $draw = true)
    {
        global $objSettings;

        $width = isset($params['width']) ? (int)$params['width'] : 99;

        // Show map
        if(empty($data['latitude']) && empty($data['longitude'])){
            $output = $data['map_code'];
        }else{
            // Use Gmap3 if latitude and longitude are known
            $output = '<script src="//maps.googleapis.com/maps/api/js?key='.$objSettings->GetParameter('google_api').'" type="text/javascript"></script>
				<script type="text/javascript" src="'.APPHP_BASE.'templates/'.Application::Get('template').'/js/gmap3.js"></script>					
				<div id="map" class="gmap3"></div>
				'.htmlentities($data['name'].', '.str_replace("\r\n", ' ', $data['address'])).'
				<script type="text/javascript">
				$(document).ready(function() {
					$("#map").width("'.$width.'%").height("390px").gmap3({
						map:{options:{center:['.$data['latitude'].','.$data['longitude'].'],zoom: 14}},
						marker:{
							values:[{latLng:['.$data['latitude'].','.$data['longitude'].'], data:"'.htmlentities($data['name']).', <br />'.htmlentities(str_replace("\r\n", ' ', $data['address'])).'"},],
							options:{draggable: false},
							events:{
								mouseover: function(marker, event, context){
								  var map = $(this).gmap3("get"),
									infowindow = $(this).gmap3({get:{name:"infowindow"}});
									if(infowindow){
										infowindow.open(map, marker);
										infowindow.setContent(context.data);
									}else{
										$(this).gmap3({infowindow:{anchor:marker, options:{content: context.data}}});
									}
								},
								mouseout: function(){
									var infowindow = $(this).gmap3({get:{name:"infowindow"}});
									if (infowindow){infowindow.close();}
								}
							}
						}
					});								
				});
				</script>';
        }

        if($draw) echo $output;
        else return $output;
    }

    /**
     * Search available tours
     * @param $params
     */
    public function SearchFor($params = array())
    {
        global $objLogin;

        $lang = Application::Get('lang');
        $currency_rate = Application::Get('currency_rate');

        $tour_id  = ! empty($params['tour_id']) ? (int)$params['tour_id'] : 0;
        $tour_location  = ! empty($params['tour_location']) ? (int)$params['tour_location'] : 0;
        $tour_from_date = ! empty($params['tour_from_date']) ? $params['tour_from_date'] : '';
        $tour_to_date   = ! empty($params['tour_to_date']) ? $params['tour_to_date'] : '';
        $min_price = ! empty($params['min_price']) ? $params['min_price'] : 0;
        $max_price = ! empty($params['max_price']) ? $params['max_price'] : 0;
        $tour_types = ! empty($params['tour_types']) ? $params['tour_types'] : [];
        $price_sort = !empty($params['sort_price']) && in_array(strtolower($params['sort_price']), array('asc', 'desc')) ? strtolower($params['sort_price']) : '';
        $view_type = !empty($params['view_type']) && in_array(strtolower($params['view_type']), array('grid', 'list')) ? strtolower($params['view_type']) : 'list';
        ///dd($params);

        // Where
        // ------------------------------------------
        if (!empty($tour_id)) {
            $tour_where_clause = ' AND id = '.$tour_id;
        } else {
            // Location
            $tour_where_clause = ! empty($tour_location) ? ' AND tour_location_id = '.$tour_location : '';

            // Price
            $tour_where_clause .= (!empty($min_price) && !empty($currency_rate)) ? ' AND price_per_person >= '.round($min_price / $currency_rate) : '';
            $tour_where_clause .= (!empty($max_price) && !empty($currency_rate)) ? ' AND price_per_person <= '.round($max_price / $currency_rate) : '';

            // Tour types
            if (!empty($tour_types) && is_array($tour_types)) {
                $tour_where_clause = ' AND tour_type_id IN ('.implode(',', $tour_types).')';
            }
        }

        if (!$objLogin->IsLoggedInAs('owner','mainadmin')) {
            $tour_where_clause .= ' AND is_active = 1';
        }

        // Order By
        // ------------------------------------------
        if(!empty($price_sort)){
            $arr_tour_order[] = 'price_per_person '.strtoupper($price_sort);
        }
        $tour_order_by_clause = !empty($arr_tour_order) ? ' ORDER BY '.implode(', ',$arr_tour_order).' ' : '';


        // Step 1. Find all tours related to selected locations
        // ----------------------------------------------
        //$tours = array();
        $sql = 'SELECT * 
                FROM '.$this->tableName.'
                WHERE 1=1
                    '.$tour_where_clause.'
                    '.$tour_order_by_clause;

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

        if(is_array($result) && $result[1] > 0){
            $arr_agencies = array();
            $min_price_per_day = 0;
            foreach($result[0] as $tour){
                if(!isset($arr_agencies[$tour['tour_agency_id']])){
                    $arr_agencies[$tour['tour_agency_id']] = 1;
                }
                if(!$min_price_per_day || $tour['price_per_person'] < $min_price_per_day){
                    $min_price_per_day = $tour['price_per_person'];
                }
            }
            $tours_count = array('tours' => $result[1], 'agencies'=>count($arr_agencies), 'min_price_per_day'=>$min_price_per_day);
        }else{
            $tours_count = array('tours' => 0, 'agencies'=>0, 'min_price_per_day'=>0);
        }
        //dd($tours_count);

        return $tours_count;
    }

    /**
     * Draws search result
     * @param $params
     * @param $tours_total
     */
    public function DrawSearchResult($params, $tours_total = 0)
    {
        global $objLogin;

        $currency_rate = Application::Get('currency_rate');
        $currency_format = $this->currencyFormat;
        $customer_id = $objLogin->GetLoggedID();

        $nl = "\n";
        $output = '';

        $lang = Application::Get('lang');

        $tour_id  = ! empty($params['tour_id']) ? (int)$params['tour_id'] : 0;
        $tour_location = ! empty($params['tour_location']) ? (int)$params['tour_location'] : 0;
        $tour_from_date = ! empty($params['tour_from_date']) ? $params['tour_from_date'] : '';
        $tour_to_date = ! empty($params['tour_to_date']) ? $params['tour_to_date'] : '';
        $min_price = ! empty($params['min_price']) ? $params['min_price'] : 0;
        $max_price = ! empty($params['max_price']) ? $params['max_price'] : 0;
        $tour_types = ! empty($params['tour_types']) ? $params['tour_types'] : [];
        $price_sort = !empty($params['sort_price']) && in_array(strtolower($params['sort_price']), array('asc', 'desc')) ? strtolower($params['sort_price']) : '';
        $view_type = !empty($params['view_type']) && in_array(strtolower($params['view_type']), array('grid', 'list')) ? strtolower($params['view_type']) : 'list';


        // -------- pagination
        $search_page_size = 10;
        $current_page = isset($_REQUEST['p']) ? (int)$_REQUEST['p'] : '1';
        $total_pages = (int)($tours_total / $search_page_size);
        if($current_page > ($total_pages+1)) $current_page = 1;
        if(($tours_total % $search_page_size) != 0) $total_pages++;
        if(!is_numeric($current_page) || (int)$current_page <= 0) $current_page = 1;
        // --------

        ///dd($params);

        $days = (int)(time_diff($tour_from_date, $tour_to_date) / (24 * 60 * 60));
        if ($days < 1 || $days > 180){
            $days = 1;
        }

        // Where
        // ------------------------------------------
        if (!empty($tour_id)) {
            $tour_where_clause = ' AND t.id = '.$tour_id;
        } else {
            // Location
            $tour_where_clause = ! empty($tour_location) ? ' AND t.tour_location_id = '.$tour_location : '';

            // Price
            $tour_where_clause .= (!empty($min_price) && !empty($currency_rate)) ? ' AND t.price_per_person >= '.round($min_price / $currency_rate) : '';
            $tour_where_clause .= (!empty($max_price) && !empty($currency_rate)) ? ' AND t.price_per_person <= '.round($max_price / $currency_rate) : '';

            // Tour types
            if (!empty($tour_types) && is_array($tour_types)) {
                $tour_where_clause = ' AND t.tour_type_id IN ('.implode(',', $tour_types).')';
            }
        }

        if (!$objLogin->IsLoggedInAs('owner','mainadmin')) {
            $tour_where_clause .= ' AND t.is_active = 1';
        }

        // Order By
        // ------------------------------------------
        if(!empty($price_sort)){
            $arr_tour_order[] = 't.price_per_person '.strtoupper($price_sort);
        }
        $tour_order_by_clause = !empty($arr_tour_order) ? ' ORDER BY '.implode(', ',$arr_tour_order).' ' : '';

        $sql = 'SELECT
					t.*,
					td.name,
					td.address,
					td.description,
					tld.name as location_name,
                    ttd.name as tour_type_name,
                    tad.name as tour_agency_name
				FROM '.$this->tableName.' t
					INNER JOIN '.$this->tableDescription.' td ON t.id = td.tour_id AND td.language_id = \''.$lang.'\'            
					INNER JOIN '.$this->tableToursTypesDescription.' ttd ON t.tour_type_id = ttd.tour_type_id AND ttd.language_id = \''.$lang.'\'            
					LEFT OUTER JOIN '.$this->tableLocationDescription.' tld ON t.tour_location_id = tld.tour_location_id AND tld.language_id = \''.$lang.'\'
					LEFT OUTER JOIN '.$this->tableAgencyDescription.' tad ON t.tour_agency_id = tad.agency_id AND tad.language_id = \''.$lang.'\'
				WHERE 1=1
                    '.$tour_where_clause.'
                    '.$tour_order_by_clause.'				    
				LIMIT '.(($current_page - 1) * $search_page_size).', '.$search_page_size;
        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        //dd($result,1);


        $sql = 'SELECT * 
                FROM '.$this->tableImages.'
                WHERE 1=1
                ORDER BY priority_order ASC';

        $images = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        $arrImages = [];
        foreach ($images[0] as $key => $val) {
            if (!isset($arrImages[$val['tour_id']])) {
                $arrImages[$val['tour_id']] = [];
            }
            $arrImages[$val['tour_id']][] = [
                'image' => $val['item_file'],
                'thumb' => $val['item_file_thumb']
            ];
        }
        //dbug($arrImages,1);

        $output .= '<div class="itemscontainer offset-1">'.$nl;
        foreach($result[0] as $tour){
            $price = $days * $tour['price_per_person'];
            $tour_id = $tour['id'];
            $image = 'images/tours/'.(!empty($arrImages[$tour_id][0]['image']) ? $arrImages[$tour_id][0]['image'] : 'no_image.png');
            $tour_link = prepare_link('tours', 'tour_id', $tour_id, '', $tour['name'], '', '', true);

            $rating_info = ToursReviews::GetAverageRaiting($tour_id, 1, 10);
            // Get info about wishlist
            $in_wishlist = Wishlist::GetWishListItemInfo($tour_id, 'tour', $customer_id);

            if($view_type == 'grid'){
                $output .= '<div class="voffcet-2 grid-view col-md-4 offset-0">
					<div class="border">
						<!-- CONTAINER-->
						<div class="carscontainer">
							<div class="center">
								<a href="'.$tour_link.'">
									<img style="width:100%;height:150px;" src="'.$image.'" alt="'.htmlentities($tour['name']).'">
								</a>
							</div>
							<div class="padding10">
								<div class="size14 bold dark">'.htmlentities($tour['name']).'</div>								
								<span class="size13 bold grey mt5">'.htmlentities((!empty($tour_location) ? '<b>'.$tour['location_name'].'</b> / ' : '').$tour['tour_type_name']).'</span>
                                <div class=" mt10">
                                    <!--<img src="templates/'.Application::Get('template').'/images/filter-rating-'.ceil($rating_info['general']).'.png" width="60" alt=""/><br/>-->
                                    <img src="templates/'.Application::Get('template').'/images/user-rating-'.ceil($rating_info['general']).'.png" width="60" alt="" />
                                    <span class="size11 grey">'.$rating_info['total_reviews'].' '.($rating_info['total_reviews'] == 1 ? _REVIEW : _REVIEWS).'</span>
                                </div>
							</div>
							<div class="purchasecontainer-tours">
						    <div class="row">
								<div class="col size18 bold green mt10">'.Currencies::PriceFormat($price * $currency_rate, '', '', $currency_format).'</div>
								<div class="col">
                                    <form action="index.php?page=book_now_tour" name="tours-reservation-form" method="post">
                                        '.draw_token_field(false).'
                                        '.draw_hidden_field('price', $price, false, 'price').'
                                        '.draw_hidden_field('act', 'add', false, 'act').'
                                        '.draw_hidden_field('tour_id', $tour_id, false, '').'
                                        '.draw_hidden_field('tour_location', $tour_location, false, '').'
                                        '.draw_hidden_field('tour_from_date', $tour_from_date, false, '').'
                                        '.draw_hidden_field('tour_to_date', $tour_to_date, false, '').'
                                        <input type="submit" class="form_button_middle bookbtn right margtop-20" style="margin-bottom:15px;" value="'._BOOK_NOW.'">
                                    </form>
								</div>
							</div>
							</div>
						</div>
						<!-- END OF CONTAINER-->
					</div>
				</div>';
            }else{
                $output .= '
                    <div class="voffset-2">
                        <div class="col-md-4 offset-0">
                            <div class="listitem2">
                                <img src="'.$image.'" alt="tour" />
                                <div class="liover"></div>';
                // Draw wishlist icon
                if($in_wishlist){
                    $output .= Wishlist::GetFavoriteButton('tour', $tour_id, $customer_id, Application::Get('token'), 'remove', 'fav-icon', '&nbsp;');
                }else{
                    $output .= Wishlist::GetFavoriteButton('tour', $tour_id, $customer_id, Application::Get('token'), 'add', 'fav-icon', '&nbsp;');
                }
                $output .= '
                                <a class="book-icon" href="'.$tour_link.'"></a>
                            </div>
                        </div>
                        <div class="col-md-8 offset-2">
                            <div class="itemlabel4 row">
                                <div class="col-sm-8 labelleft2-tour-descr">
                                    <div class="size18 mt5 mb10"><b><a href="'.$tour_link.'">'.$tour['name'].'</a></b>';
                // Draw wishlist icon
                if($in_wishlist){
                    $output .= '<pan class="pull-right mt-5">'.Wishlist::GetFavoriteButton('tour', $tour_id, $customer_id, Application::Get('token'), 'remove').'</pan>';
                }
                $output .= '</div>
                                    <div>
                                        <span class="line5 wh80percent" style="margin-bottom: 4px"></span>
                                        <span class="opensans size13 grey">
                                            '.(!empty($tour_location) ? '<b>'.$tour['location_name'].'</b> / ' : '').$tour['tour_type_name'].'                                            
                                        </span><br>
                                        <span class="opensans size13 grey">'.$tour['tour_agency_name'].'</span>
                                    </div>                                        
                                    <div class="line5 wh80percent"></div>
                                    <p class="grey size14 lh6 wordwrap">
                                        '.$tour['description'].'
                                    </p>
                                    <br/>
                                </div>
                                <div class="col-sm-4">
                                    <div class="mb10">
                                        <span class="green size18"><b>'.Currencies::PriceFormat($price * $currency_rate, '', '', $currency_format).'</b></span><span class="size11 grey">/person</span><br/>
                                    </div>
                                    <!--<img src="templates/'.Application::Get('template').'/images/filter-rating-'.ceil($rating_info['general']).'.png" width="60" alt=""/><br/>-->
                                    <img src="templates/'.Application::Get('template').'/images/user-rating-'.ceil($rating_info['general']).'.png" width="60" alt=""/><br/>
                                    <span class="size11 grey">'.$rating_info['total_reviews'].' '.($rating_info['total_reviews'] == 1 ? _REVIEW : _REVIEWS).'</span><br/><br/>

                                    <form action="index.php?page=book_now_tour" name="tours-reservation-form" method="post">
                                        '.draw_token_field(false).'
                                        '.draw_hidden_field('price', $price, false, '').'
                                        '.draw_hidden_field('act', 'add', false, '').'
                                        '.draw_hidden_field('tour_id', $tour_id, false, '').'
                                        '.draw_hidden_field('tour_location', $tour_location, false, '').'
                                        '.draw_hidden_field('tour_from_date', $tour_from_date, false, '').'
                                        '.draw_hidden_field('tour_to_date', $tour_to_date, false, '').'

                                        <input type="submit" class="form_button_middle" style="margin-top:3px;" value="'._BOOK_NOW.'">
                                    </form>
                                </div>                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="featurette-divider3">
                '.$nl;
            }
        }
        $output .= '<div class="clearfix"></div>'.$nl;
        $output .= '<!-- End of offset1-->'.$nl;

        $output .= '</div>';

        $output .= $this->DrawPaginationLinks($total_pages, $current_page, $params, false);

        return $output;
    }

    /**
     * Draws pagination links
     *
     * @param $total_pages
     * @param $current_page
     * @param $params
     * @param $draw
     *
     * @return string
     */
    private function DrawPaginationLinks($total_pages, $current_page, $params, $draw = true)
    {
        $output = '';

        $price_sort = !empty($params['sort_price']) && in_array(strtolower($params['sort_price']), array('asc', 'desc')) ? strtolower($params['sort_price']) : '';
        $view_type = !empty($params['view_type']) && in_array(strtolower($params['view_type']), array('grid', 'list')) ? strtolower($params['view_type']) : 'list';

        // Draw pagination links
        if($total_pages > 1){
            $output .= '<div class="paging">';
            for($page_ind = 1; $page_ind <= $total_pages; $page_ind++){
                $output .= '<a class="paging_link" href="javascript:void(\'page|'.$page_ind.'\');" onclick="javascript:appFormSubmit(\'tour-reservation-form\',\'view_type='.$view_type.'&sort_price='.$price_sort.'&page_number='.$page_ind.'\')">'.(($page_ind == $current_page) ? '<b>['.$page_ind.']</b>' : $page_ind).'</a> ';
            }
            $output .= '</div>';
        }

        if($draw) echo $output;
        else return $output;
    }

}
