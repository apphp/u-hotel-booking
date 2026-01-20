<?php

/**
 * 	Class Conferences (for uHotelBooking ONLY)
 *  -------------- 
 *  Description : 
 *  Updated	    : 02.05.2017
 * 	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Usage       : uHotelBooking
 * 	
 * 	PUBLIC:						STATIC:						PRIVATE:
 *  -----------					-----------					-----------
 *  __construct					GetAllConferences			ValidateFields
 *  __destruct					GetConference
 *  BeforeInsertRecord       	DrawRegestrationProgress
 *  AfterInsertRecord 			GetSpecialities
 *  BeforeUpdateRecord          DrawConferenceMenu
 *  AfterUpdateRecord
 *  AfterDeleteRecord
 * */

class Conferences extends MicroGrid {

    protected $debug = false;
	
	private $arrTranslations = '';		

    //==========================================================================
    // Class Constructor
    //==========================================================================
	function __construct()
	{
        parent::__construct();

        //global $objSettings;
        //global $objLogin;

        $this->params = array();
		if(isset($_POST['conference_type_id'])) (int)$this->params['conference_type_id'] = (int)prepare_input($_POST['conference_type_id']);
		if(isset($_POST['start_date'])) $this->params['start_date'] = prepare_input($_POST['start_date']);
		if(isset($_POST['finish_date'])) $this->params['finish_date'] = prepare_input($_POST['finish_date']);
		if(isset($_POST['hotel_location_id'])) (int)$this->params['hotel_location_id'] = (int)prepare_input($_POST['hotel_location_id']);
		if(isset($_POST['hotel_id'])) (int)$this->params['hotel_id'] = (int)prepare_input($_POST['hotel_id']);
		$this->params['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : '0';

        $this->primaryKey = 'id';
        $this->tableName = DB_PREFIX.'conferences';
        $this->tableTranslationsName = DB_PREFIX.'conferences_translations';
        $this->tableConferenceTypesName = DB_PREFIX.'conference_types_translations';
        $this->dataSet = array();
        $this->error = '';
        $this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

        $this->actions = array('add' => true, 'edit' => true, 'details' => true, 'delete' => true);
        $this->actionIcons = true;
        $this->allowRefresh = true;
        $this->allowPrint = true;
        $this->allowTopButtons = true;

        $this->allowLanguages = false;
        $this->WHERE_CLAUSE = '';
        $this->ORDER_CLAUSE = 'ORDER BY start_date DESC';

        $this->isAlterColorsAllowed = true;

        $this->isPagingAllowed = true;
        $this->pageSize = 20;

        $this->isSortingAllowed = true;

		$this->arrHotelsLocationsWm = array();
		$this->arrConferenceTypes = array();

		$arr_active_vm = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		// prepare locations array		
		$total_hotels_locations = HotelsLocations::GetAllLocations();
		$arr_hotels_locations = array();
		foreach($total_hotels_locations[0] as $key => $val){
			$arr_hotels_locations[$val['country_name']][$val['id']] = $val['name'];
			$this->arrHotelsLocationsWm[$val['id']] = $val['country_name'].', '.$val['name'];
		}

		// prepare hotels
		$arr_hotels = array();
		$arr_hotels_filter = array();
        $total_hotels = Hotels::GetAllHotels();
        foreach($total_hotels[0] as $key => $val){
            $arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
            $arr_hotels_filter[$val['id']] = $val['name'].(!empty($val['location_name']) ? ' ('.$val['location_name'].') ' : '');
        }

		$this->arrConferenceTypes = array();
		$all_conference_types = ConferenceTypes::GetAllConferences();
		if(!empty($all_conference_types) && $all_conference_types[1] > 0){
			foreach($all_conference_types[0] as $conference_info){
				$this->arrConferenceTypes[$conference_info['id']] = $conference_info['name'];
			}
		}

        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
        $this->isFilteringAllowed = true;
        $this->arrFilteringFields = array(
            _CONFERENCE_TYPE => array('table'=>$this->tableName, 'field'=>'conference_type_id', 'type'=>'dropdownlist', 'sign'=>'=', 'source'=>$this->arrConferenceTypes, 'width'=>''),
			//_LOCATIONS  => array('table'=>$this->tableName, 'field'=>'hotel_location_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels_locations, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
			$hotel_name  => array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels_filter, 'sign'=>'=', 'width'=>'', 'visible'=>true),
        );

		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('location_description', 'hotel_description', 'target_audience', 'program_purpose', 'objectives', 'program_schedule', 'faculty', 'accreditation', 'fap', 'course_cancellation_fee')
		);

		// #003. prepare translations array for add/edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
			$this->tableTranslationsName,
			'conference_id',
			array('location_description', 'hotel_description', 'target_audience', 'program_purpose', 'objectives', 'program_schedule', 'faculty', 'accreditation', 'fap', 'course_cancellation_fee')
		);

        //---------------------------------------------------------------------- 
        // VIEW MODE
        //---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.*,
									'.$this->tableTranslationsName.'.location_description,
									'.$this->tableTranslationsName.'.hotel_description,
									'.$this->tableTranslationsName.'.target_audience,
									'.$this->tableTranslationsName.'.program_purpose,
									'.$this->tableTranslationsName.'.objectives,
									'.$this->tableTranslationsName.'.program_schedule,
									'.$this->tableTranslationsName.'.faculty,
									'.$this->tableTranslationsName.'.accreditation,
									'.$this->tableTranslationsName.'.fap,
									'.$this->tableTranslationsName.'.course_cancellation_fee,
									'.$this->tableConferenceTypesName.'.name as conference_name,
										CONCAT("<a href=\"index.php?admin=mod_conference_tuitions&cid=", '.$this->tableName.'.'.$this->primaryKey.', "\">[ '._TUITIONS.' ]</a>  (", (SELECT COUNT(*) as cnt FROM '.DB_PREFIX.'conference_tuitions cp WHERE cp.conference_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_tuitions,
									CONCAT("<a href=\"index.php?admin=mod_conference_participants&cid=", '.$this->tableName.'.'.$this->primaryKey.', "\">[ '._PARTICIPANTS.' ]</a>  (", (SELECT COUNT(*) as cnt FROM '.DB_PREFIX.'conference_participants cp WHERE cp.conference_id = '.$this->tableName.'.'.$this->primaryKey.') , ")") as link_participants
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.$this->tableConferenceTypesName.' ON '.$this->tableName.'.conference_type_id = '.$this->tableConferenceTypesName.'.conference_type_id AND '.$this->tableConferenceTypesName.'.language_id = \''.$this->languageId.'\'
									LEFT OUTER JOIN '.$this->tableTranslationsName.' ON '.$this->tableName.'.id = '.$this->tableTranslationsName.'.conference_id AND '.$this->tableTranslationsName.'.language_id = \''.$this->languageId.'\'
								';
        // define view mode fields
        $this->arrViewModeFields = array(
            'conference_name' 	=> array('title'=>_CONFERENCE_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'270px', 'maxlength'=>''),
            'start_date'	  	=> array('title'=>_START_DATE, 'type'=>'date', 'align'=>'left', 'width'=>'90px', 'sortable'=>true, 'format'=>get_date_format('view')),
			'finish_date'	  	=> array('title'=>_FINISH_DATE,  'type'=>'date', 'align'=>'left', 'width'=>'90px', 'sortable'=>true, 'format'=>get_date_format('view')),
			'hotel_location_id' => array('title'=>_LOCATION, 'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arrHotelsLocationsWm),
			'hotel_id'			=> array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
			'link_tuitions'		=> array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'140px', 'sortable'=>false, 'visible'=>true),
			'link_participants' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'140px', 'sortable'=>false, 'visible'=>true),
			'is_active'       	=> array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'55px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_active_vm),
            'id' 			  	=> array('title'=>'ID', 'type' => 'label', 'align' => 'center', 'width' => '80px'),
        );

        //---------------------------------------------------------------------- 
        // ADD MODE
        //---------------------------------------------------------------------- 
        // define add mode fields
        $this->arrAddModeFields = array(
			'conference_type_id' => array('title'=>_CONFERENCE_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrConferenceTypes, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			'start_date'         => array('title'=>_START_DATE, 'type'=>'date', 'align'=>'left', 'width'=>'80px', 'sortable'=>true, 'format'=>get_date_format('edit')),
			'finish_date'        => array('title'=>_FINISH_DATE,  'type'=>'date', 'align'=>'left', 'width'=>'80px', 'sortable'=>true, 'format'=>get_date_format('edit'), 'format_parameter'=>'', 'movable'=>true),
			'hotel_location_id'  => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels_locations, 'default_option'=>'', 'unique'=>false, 'view_type'=>'dropdownlist', 'multi_select'=>false, 'javascript_event'=>'onchange="appReloadHotels(this.value,\'\',\'hotel_id\',\''.Application::Get('token').'\', \'\', \'\', \'-- '._SELECT.' --\')"'),
			'hotel_id'           => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'width'=>'160px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			'is_active'          => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
        );

        //---------------------------------------------------------------------- 
        // EDIT MODE
        // * password field must be written directly in SQL!!!
        //---------------------------------------------------------------------- 
        $this->EDIT_MODE_SQL = 'SELECT
		                            '.$sql_translation_description.'
									'.$this->tableName.'.*																		
								FROM '.$this->tableName.'
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';

        // define edit mode fields
        $this->arrEditModeFields = array(
			'conference_type_id' => array('title'=>_CONFERENCE_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrConferenceTypes, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
            'start_date'		 => array('title'=>_START_DATE, 'type'=>'date', 'align'=>'left', 'width'=>'80px', 'sortable'=>true, 'format'=>get_date_format('edit')),
			'finish_date'		 => array('title'=>_FINISH_DATE,  'type'=>'date', 'align'=>'left', 'width'=>'80px', 'sortable'=>true, 'format'=>get_date_format('edit'), 'format_parameter'=>'', 'movable'=>true),
			'hotel_location_id'  => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels_locations, 'default_option'=>'', 'unique'=>false, 'view_type'=>'dropdownlist', 'multi_select'=>false, 'javascript_event'=>'onchange="appReloadHotels(this.value,\'\',\'hotel_id\',\''.Application::Get('token').'\', \'\', \'\', \'-- '._SELECT.' --\')"'),
			'hotel_id'			 => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'width'=>'160px', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			'is_active'			 => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
        );

        //---------------------------------------------------------------------- 
        // DETAILS MODE
        // format: strip_tags, nl2br, readonly_text
        //----------------------------------------------------------------------
        $this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
        $this->arrDetailsModeFields = array(
			'conference_type_id' => array('title'=>_CONFERENCE_TYPE, 'type'=>'enum', 'width'=>'', 'readonly'=>true, 'default'=>'', 'source'=>$this->arrConferenceTypes, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
            'start_date'	 => array('title'=>_START_DATE, 'type'=>'date', 'align'=>'left', 'width'=>'80px', 'sortable'=>true, 'format'=>get_date_format('view'), 'readonly'=>true),
			'finish_date'	 => array('title'=>_FINISH_DATE,  'type'=>'date', 'align'=>'left', 'width'=>'80px', 'sortable'=>true, 'format'=>get_date_format('view'), 'readonly'=>true),
			'hotel_location_id' => array('title'=>_LOCATION_NAME, 'type'=>'enum', 'source'=>$arr_hotels_locations),
			'hotel_id' => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'source'=>$arr_hotels),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_active_vm),
        );

		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array(
				'location_description' => array('title'=>_LOCATION_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'hotel_description' => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_DESCRIPTION : _HOTEL_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'target_audience' => array('title'=>_TARGET_AUDIENCE, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'program_purpose' => array('title'=>_PROGRAM_PURPOSE, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'objectives' => array('title'=>_OBJECTIVES, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'program_schedule' => array('title'=>_PROGRAM_SCHEDULE, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'8192', 'validation_maxlength'=>'8192', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'faculty' => array('title'=>_FACULTY, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'accreditation' => array('title'=>_ACCREDITATION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'fap' => array('title'=>_FAP, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
				'course_cancellation_fee' => array('title'=>_COURSE_CANCELLATION_FEE, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'editor_type'=>'wysiwyg'),
			)
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
     * 	@param $order - order clause
	 * 	@return array
     */
	public static function GetAllConferences($where = '', $order = '')
	{
        $tableName = DB_PREFIX.'conferences';
        $tableTranslationsName = DB_PREFIX.'conferences_translations';
        $languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

        // Build WHERE clause
        $where_clause = !empty($where) ? 'WHERE '.$where : '';
        $order_clause = !empty($order) ? 'ORDER BY '.$order : '';
		$sql = 'SELECT '.$tableName.'.*,
					'.$tableTranslationsName.'.location_description,
					'.$tableTranslationsName.'.hotel_description,
					'.$tableTranslationsName.'.target_audience,
					'.$tableTranslationsName.'.program_purpose,
					'.$tableTranslationsName.'.objectives,
					'.$tableTranslationsName.'.program_schedule,
					'.$tableTranslationsName.'.faculty,
					'.$tableTranslationsName.'.accreditation,
					'.$tableTranslationsName.'.fap,
					'.$tableTranslationsName.'.course_cancellation_fee,
					'.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.name as hotel_location_name
				FROM '.$tableName.'
					LEFT OUTER JOIN '.$tableTranslationsName.' ON '.$tableName.'.id = '.$tableTranslationsName.'.conference_id AND '.$tableTranslationsName.'.language_id = \''.$languageId.'\'
					LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' ON '.$tableName.'.hotel_location_id = '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.hotel_location_id AND '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.language_id = \''.$languageId.'\'
				'.$where_clause.' '.$order_clause;

        return database_query($sql, DATA_AND_ROWS);
    }

    /**
     * 	Get all countries array
     * 	@param $conference_id
	 * 	@return array
     */
	public static function GetConference($conference_id)
	{
        $tableName = DB_PREFIX.'conferences';
        $tableConferenceTypesName = DB_PREFIX.'conference_types_translations';
        $tableTranslationsName = DB_PREFIX.'conferences_translations';
        $languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

		$sql = 'SELECT '.$tableName.'.*,
					'.$tableTranslationsName.'.location_description,
					'.$tableTranslationsName.'.hotel_description,
					'.$tableTranslationsName.'.target_audience,
					'.$tableTranslationsName.'.program_purpose,
					'.$tableTranslationsName.'.objectives,
					'.$tableTranslationsName.'.program_schedule,
					'.$tableTranslationsName.'.faculty,
					'.$tableTranslationsName.'.accreditation,
					'.$tableTranslationsName.'.fap,
					'.$tableTranslationsName.'.course_cancellation_fee,
					'.$tableConferenceTypesName.'.name as conference_type_name,
					'.$tableConferenceTypesName.'.short_description as conference_type_short_description,
					'.$tableConferenceTypesName.'.full_description as conference_type_full_description,
					'.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.name as hotel_location_name,
					'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name
				FROM '.$tableName.'
					LEFT OUTER JOIN '.$tableTranslationsName.' ON '.$tableName.'.id = '.$tableTranslationsName.'.conference_id AND '.$tableTranslationsName.'.language_id = \''.$languageId.'\'
					LEFT OUTER JOIN '.$tableConferenceTypesName.' ON '.$tableName.'.conference_type_id = '.$tableConferenceTypesName.'.conference_type_id AND '.$tableConferenceTypesName.'.language_id = \''.$languageId.'\'
					LEFT OUTER JOIN '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' ON '.$tableName.'.hotel_location_id = '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.hotel_location_id AND '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.'.language_id = \''.$languageId.'\'
					LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.$tableName.'.hotel_id = '.TABLE_HOTELS_DESCRIPTION.'.hotel_id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$languageId.'\'
				WHERE '.$tableName.'.id = '.(int)$conference_id;

        return database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
    }

	/*
	 * Draw progress bar for the conference registration
	 * @param int $step
	 * @param int $activeStep
	 * @param bool $draw
	 * @return void|HTML
	 * */
	public static function DrawRegestrationProgress($step, $activeStep, $draw = true)
	{
		$step = (int)$step > 0 ? (int)$step : 1;
		$activeStep = (int)$activeStep > 0 ? (int)$activeStep : 1;

		$output = '<div class="ui-content" role="main">
			<div class="progress">
				<p class="'.($activeStep == 1 ? 'current' : 'visit').'">1</p>
				<div class="line'.($step > 1 || $activeStep == 2 ? ' visit' : '').'"></div>
				<p class="'.($activeStep == 2 ? 'current' : ($step >= 2 ? 'visit' : '')).'">2</p>
				<div class="line'.($step > 2 || $activeStep == 3 ? ' visit' : '').'"></div>
				<p class="'.($activeStep == 3 ? 'current' : ($step >= 3 ? 'visit' : '')).'">3</p>
				<div class="line'.($step > 3 || $activeStep == 4 ? ' visit' : '').'"></div>
				<p class="'.($activeStep == 4 ? 'current' : ($step >= 4 ? 'visit' : '')).'">4</p>
				<div class="line'.($step > 4 || $activeStep == 5 ? ' visit' : '').'"></div>
				<p class="'.($activeStep == 5 ? 'current' : ($step >= 5 ? 'visit' : '')).'">5</p>
				<div class="line'.($step > 5 || $activeStep == 6 ? ' visit' : '').'"></div>
				<p class="'.($activeStep == 6 ? 'current' : ($step >= 6 ? 'visit' : '')).'">6</p>
			  </div>
		  </div>';
		if($draw){
			echo $output;
		}else{
			return $output;
		}
	}

	/*
	 * Draw links for side bar the conference registration
	 * @param bool $draw
	 * @return void|HTML
	 * */
	public static function DrawConferenceMenu($draw = true)
	{
		$output = '';
		$url = 'index.php?page=conference_general&cid='.(int)$_GET['cid'];
		$conference = Conferences::GetConference((int)$_GET['cid']);

		if(!empty($conference)){
			$output .= '<div class="left_box_container">
				<h3 class="collapsebtn side_box_heading" data-target="#conference-location2" data-toggle="collapse">'._LOCATIONS.'<span class="collapsearrow"></span></h3>
				<div id="conference-location2" class="side_box_content collapse in">
					<ul class="ltr">
						'.(!empty($conference['hotel_description']) ? '<li><a class="main_menu_link" href="'.$url.'#hotel_description">'._ACCOMMODATIONS.'</a></li>' : '').'
						<li><a class="main_menu_link" href="index.php?page=check_availability">'._RESERVATION.'</a></li>
					</ul>
				</div>
				<div class="line1"></div>
			</div>';
			if(!empty($conference['target_audience']) || !empty($conference['program_purpose']) || !empty($conference['objectives']) || !empty($conference['program_schedule']) || !empty($conference['faculty']) || !empty($conference['accreditation']) || !empty($conference['fap'])){
				$output .= '<div class="left_box_container">
					<h3 class="collapsebtn side_box_heading" data-target="#conference-program2" data-toggle="collapse">'._PROGRAM.'<span class="collapsearrow"></span></h3>
					<div id="conference-program2" class="side_box_content collapse in">
						<ul class="ltr">
							'.(!empty($conference['target_audience']) ? '<li><a class="main_menu_link" href="'.$url.'#target_audience">'._TARGET_AUDIENCE.'</a></li>' : '').'
							'.(!empty($conference['program_purpose']) ? '<li><a class="main_menu_link" href="'.$url.'#program_purpose">'._PROGRAM_PURPOSE.'</a></li>' :'').'
							'.(!empty($conference['objectives']) ? '<li><a class="main_menu_link" href="'.$url.'#objectives">'._OBJECTIVES.'</a></li>' : '').'
							'.(!empty($conference['program_schedule']) ? '<li><a class="main_menu_link" href="'.$url.'#program_schedule">'._PROGRAM_SCHEDULE.'</a></li>' : '').'
							'.(!empty($conference['faculty']) ? '<li><a class="main_menu_link" href="'.$url.'#faculty">'._FACULTY.'</a></li>' : '').'
							'.(!empty($conference['accreditation']) ? '<li><a class="main_menu_link" href="'.$url.'#accreditation">'._ACCREDITATION.'</a></li>' : '').'
							'.(!empty($conference['fap']) ? '<li><a class="main_menu_link" href="'.$url.'#fap">'._FAP.'</a></li>' : '').'
						</ul>
					</div>
					<div class="line1"></div>
				</div>';
			}
		}
		if($draw){
			echo $output;
		}else{
			return $output;
		}
	}

	public static function GetSpecialities()
	{
		$arr_specialties = array();
		$all_specialities = Specialities::GetAllActive();
		if(!empty($all_specialities) && !empty($all_specialities[1])){
			foreach($all_specialities[0] as $speciality){
				$arr_specialties[$speciality['id']] = $speciality['code'].' - '.$speciality['name'];
			}
		}
		$arr_specialties[0] = _OTHER_NOT_LISTED;

		return $arr_specialties;
	}

	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	/**
	 * Validate fields
	 */
	private function ValidateFields()	
	{
		$conference_type_ids = array_keys($this->arrConferenceTypes);
        $hotel_location_ids = array_keys($this->arrHotelsLocationsWm);
        $hotel_ids = array();
        if(!empty($this->params['hotel_location_id'])){
            $total_hotels = Hotels::GetAllHotels(TABLE_HOTELS.'.hotel_location_id = '.(int)$this->params['hotel_location_id']);
            foreach($total_hotels[0] as $key => $val){
                $hotel_ids[] = $val['id'];
            }		
        }
		if(empty($this->params['conference_type_id'])){
			$this->error = str_replace('_FIELD_', '<b>'._CONFERENCE_TYPE.'</b>', _FIELD_CANNOT_BE_EMPTY);
			$this->errorField = 'conference_type_id';
			return false;				
        }elseif(!in_array($this->params['conference_type_id'], $conference_type_ids)){
			$this->error = str_replace('_FIELD_', '<b>'._CONFERENCE_TYPE.'</b>', _FIELD_INCORRECT_VALUE);
			$this->errorField = 'conference_type_id';
			return false;				
        }elseif(empty($this->params['hotel_location_id'])){
			$this->error = str_replace('_FIELD_', '<b>'._LOCATION_NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
			$this->errorField = 'hotel_location_id';
			return false;				
        }elseif(!in_array($this->params['hotel_location_id'], $hotel_location_ids)){
			$this->error = str_replace('_FIELD_', '<b>'._LOCATION_NAME.'</b>', _FIELD_INCORRECT_VALUE);
			$this->errorField = 'hotel_location_id';
			return false;				
        }elseif(empty($this->params['hotel_id'])){
			$this->error = str_replace('_FIELD_', '<b>'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).'</b>', _FIELD_CANNOT_BE_EMPTY);
			$this->errorField = 'hotel_id';
			return false;
        }elseif(!in_array($this->params['hotel_id'], $hotel_ids)){
			$this->error = str_replace('_FIELD_', '<b>'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).'</b>', _FIELD_INCORRECT_VALUE);
			$this->errorField = 'hotel_location_id';
			return false;				
		}
		foreach($this->arrTranslations as $key => $val){
			if(strlen($val['location_description']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'._LOCATION_DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'location_description'.$key;
	 			return false;
			}elseif(strlen($val['hotel_description']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT_DESCRIPTION : _HOTEL_DESCRIPTION).'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'hotel_description_'.$key;
                return false;
			}elseif(strlen($val['target_audience']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'._TARGET_AUDIENCE.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'target_audience_'.$key;
                return false;
			}elseif(strlen($val['program_purpose']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'._PROGRAM_PURPOSE.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'program_purpose_'.$key;
                return false;
			}elseif(strlen($val['objectives']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'._OBJECTIVES.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'objectives_'.$key;
                return false;
			}elseif(strlen($val['program_schedule']) > 8192){
	 			$this->error = str_replace('_FIELD_', '<b>'._PROGRAM_SCHEDULE.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 8192, $this->error);
	 			$this->errorField = 'program_schedule_'.$key;
                return false;
			}elseif(strlen($val['faculty']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'._FACULTY.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'faculty_'.$key;
                return false;
			}elseif(strlen($val['accreditation']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'._ACCREDITATION.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'accreditation_'.$key;
                return false;
			}elseif(strlen($val['fap']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'._FAP.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'fap_'.$key;
                return false;
			}elseif(strlen($val['course_cancellation_fee']) > 4096){
	 			$this->error = str_replace('_FIELD_', '<b>'._COURSE_CANCELLATION_FEE.'</b>', _FIELD_LENGTH_EXCEEDED);
	 			$this->error = str_replace('_LENGTH_', 4096, $this->error);
	 			$this->errorField = 'course_cancellation_fee_'.$key;
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
		return $this->ValidateFields();
	}

	/**
	 * After Add Record
	 */
	public function BeforeAddRecord()
	{ 
        if(!empty($this->params['hotel_location_id'])){
            $arr_hotels = array();
            $arr_hotels_filter = array();
            $total_hotels = Hotels::GetAllHotels(TABLE_HOTELS.'.hotel_location_id = '.(int)$this->params['hotel_location_id']);
            foreach($total_hotels[0] as $key => $val){
                $arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
            }
            $default_hotel_id = !empty($this->params['hotel_id']) ? $this->params['hotel_id'] : '';
            $this->arrAddModeFields['hotel_id'] = array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'width'=>'160px', 'required'=>true, 'readonly'=>false, 'default'=>$default_hotel_id, 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false);

			return true;
        }
	}
	
	/**
	 * After Edit Record
	 */
	public function BeforeEditRecord()
	{
        $hotel_location_id = !empty($this->params['hotel_location_id']) ? $this->params['hotel_location_id'] : '';
        $default_hotel_id = !empty($this->params['hotel_id']) ? $this->params['hotel_id'] : '';
        if(empty($hotel_location_id)){
            $sql = 'SELECT hotel_location_id, hotel_id FROM '.$this->tableName.' WHERE '.$this->primaryKey.' = '.(int)$this->curRecordId;
            $result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
            $hotel_location_id = isset($result['hotel_location_id']) ? $result['hotel_location_id'] : '';
            $hotel_id = isset($result['hotel_id']) ? $result['hotel_id'] : '';
        }
        if(!empty($hotel_location_id)){
            $arr_hotels = array();
            $arr_hotels_filter = array();
            $total_hotels = Hotels::GetAllHotels(TABLE_HOTELS.'.hotel_location_id = '.(int)$hotel_location_id);
            foreach($total_hotels[0] as $key => $val){
                $arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
            }
            $this->arrEditModeFields['hotel_id'] = array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'width'=>'160px', 'required'=>true, 'readonly'=>false, 'default'=>$default_hotel_id, 'source'=>$arr_hotels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false);			
        }
		
		return true;
	}

	/**
	 * After-Insertion - add banner descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		global $objLogin;
		
		$sql = 'INSERT INTO '.$this->tableTranslationsName.'(id, conference_id, language_id, location_description, hotel_description, target_audience, program_purpose, objectives, program_schedule, faculty, accreditation, fap, course_cancellation_fee) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0){
				$sql .= ',';
			}
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['location_description'], false, 'medium')).'\', \''.encode_text(prepare_input($val['hotel_description'], false, 'medium')).'\', \''.encode_text(prepare_input($val['target_audience'], false, 'medium')).'\', \''.encode_text(prepare_input($val['program_purpose'], false, 'medium')).'\', \''.encode_text(prepare_input($val['objectives'], false, 'medium')).'\', \''.encode_text(prepare_input($val['program_schedule'], false, 'medium')).'\', \''.encode_text(prepare_input($val['faculty'], false, 'medium')).'\', \''.encode_text(prepare_input($val['accreditation'], false, 'medium')).'\', \''.encode_text(prepare_input($val['fap'], false, 'medium')).'\', \''.encode_text(prepare_input($val['course_cancellation_fee'], false, 'medium')).'\')';
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
		return $this->ValidateFields();
	}

	/**
	 * After-Updating - update hotels item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.$this->tableTranslationsName.'
					SET location_description = \''.encode_text(prepare_input($val['location_description'], false, 'medium')).'\',
                        hotel_description =  \''.encode_text(prepare_input($val['hotel_description'], false, 'medium')).'\',
                        target_audience = \''.encode_text(prepare_input($val['target_audience'], false, 'medium')).'\',
                        program_purpose = \''.encode_text(prepare_input($val['program_purpose'], false, 'medium')).'\',
                        objectives = \''.encode_text(prepare_input($val['objectives'], false, 'medium')).'\',
                        program_schedule = \''.encode_text(prepare_input($val['program_schedule'], false, 'medium')).'\',
                        faculty = \''.encode_text(prepare_input($val['faculty'], false, 'medium')).'\',
                        accreditation = \''.encode_text(prepare_input($val['accreditation'], false, 'medium')).'\',
                        fap = \''.encode_text(prepare_input($val['fap'], false, 'medium')).'\',
                        course_cancellation_fee = \''.encode_text(prepare_input($val['course_cancellation_fee'], false, 'medium')).'\'
					WHERE conference_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			if($this->debug) $start_time = $this->GetFormattedMicrotime();
			$result = database_void_query($sql);
			if($this->debug) $finish_time = $this->GetFormattedMicrotime();        
			if($this->debug) $this->arrSQLs['update_sql'] = '<i>Update Record</i> | T: '.round((float)$finish_time - (float)$start_time, 4).' sec. <br>'.$sql;
			if(!$result){
				if($this->debug) $this->arrErrors['update_sql'] = $sql.'<br>'.database_error();						
			}			
		}
	}

    /**
	 * After-Deleting Record
	 */
	public function AfterDeleteRecord()
	{
        // Delete info from hotel description table and other hotel related info
		$sql = 'DELETE FROM '.$this->tableTranslationsName.' WHERE conference_id = '.$this->curRecordId;
		database_void_query($sql);
	}
}
