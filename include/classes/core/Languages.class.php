<?php

/**
 *	Class Languages 
 *  -------------- 
 *  Description : encapsulates languages methods and properties
 *	Written by  : ApPHP
 *	Version     : 1.0.8
 *	Updated	    : 10.05.2017
 *  Usage       : Core Class
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				Init			        CopyDataToNewLang
 *	__construct_single      Get                     DeleteDataOfLang
 *	__construct_all         GetDefaultLang
 *	__destruct              LanguageExists
 *	BeforeAddRecord         LanguageActive
 *	AfterInsertRecord       GetLanguageDirection
 *	AfterUpdateRecord       GetUsedToBox
 *	BeforeDeleteRecord      GetLanguageName
 *	AfterDeleteRecord       GetLanguageInfo
 *	DrawLanguagesBar        GetAllActive
 *	GetLanguagesCount       GetAllActiveSelectBox
 *	GetParam                GetAllLanguages 
 *
 *	                          
 *
 *	ChangeLog:
 *	---------
 *  1.0.8
 *  	- added 'el_GR'=>'Greek - Greece',
 *  	-
 *  	-
 *  	-
 *  	-
 *  1.0.7
 *      - fixed isue with page=index for HotelSite
 *      - added SQL for HotelBooking
 *      - commented $url = str_replace(array('page=check_availability'), 'page=home', $url);										
 *      - TABLE_HOTELS_DESCRIPTION for HotelBooking
 *      - TABLE_COUNTRIES_DESCRIPTION for HotelBooking and HotelSite
 *  1.0.6
 *      - <font> replaced with <span>
 *      - added new tables for HotelSite
 *      - utf-8 eror for Norwegian encoding name
 *      - added TABLE_MEMBERSHIP_PLANS_DESCRIPTION
 *      - added more tables for MedicalAppointment
 *  1.0.5
 *      - added lc_time_name
 *      - CASE SQL changed with 'enum' types
 *      - added Meal Plans table for hotels
 *      - hotel renamed into hotels
 *      - fixed issue with default lang indertion
 *  1.0.4
 *      - re-done with extends MicroGrid
 *      - fixed bug imn cloning of GALLERY_ALBUM_ITEMS_DESCRIPTION
 *      - bug fixed in AfterUpdateRecord()
 *      - fixed issue with empty icon of language
 *      - added 'file_maxsize'=>'100k'
 *  1.0.3
 *      - added LanguageActive()
 *      - language name in English
 *      - added new param to GetLanguageName
 *      - added $PROJECT
 *      - added GetLanguageInfo
 *  1.0.2
 *  	- fixed problem with wrong swith of language
 *  	- changes in DrawLanguagesBar() for SEO links
 *  	- improved GetLanguagesCount()
 *  	- added news_code for cloning News table
 *  	- fixed bug in drawing language bar for HotelSite
 *	
 **/

class Languages extends MicroGrid {

	protected $debug = false;

	//-------------------------------
	private $id;
	protected $language;
	public $error;
	
	private $d_languages_count;
	private $d_priority_order;
	private $d_language_abbrev;
	private static $arrLanguageSettings = array();

	//==========================================================================
    // Class Constructor
	// 		@param $id
	//==========================================================================
	function __construct($id = '')
	{
		parent::__construct();

		if($id != ''){
			$this->__construct_single($id);
		}else{
			$this->__construct_all();
		}
	}

	//==========================================================================
    // Constructor for Single Record
	//==========================================================================
	private function __construct_single($id)
	{
		$this->id = $id;
		if($this->id != ''){
			$sql = 'SELECT
						id, lang_name, lang_name_en, abbreviation, lc_time_name, lang_dir, is_default, icon_image, used_on, priority_order, is_active,
						IF(is_default = 1, \'<span class=yes>'._YES.'</span>\', \''._NO.'\') as is_default_verb,
						IF(is_active = 1, \'<span class=yes>'._YES.'</span>\', \'<span class=no>'._NO.'</span>\') as is_active_verb
					FROM '.TABLE_LANGUAGES.' WHERE id = '.(int)$this->id;
			$this->language = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		}else{
			$this->language['lang_name'] = '';
			$this->language['lang_name_en'] = '';
			$this->language['priority_order'] = '';
			$this->language['lang_dir'] = '';
			$this->language['abbreviation'] = '';
			$this->language['lc_time_name'] = '';
			$this->language['used_on'] = '';
			$this->language['is_default'] = '';
			$this->language['is_active'] = '';
			$this->language['icon_image'] = '';
		}
	}
	
	//==========================================================================
    // Constructor for All Records
	//==========================================================================
	private function __construct_all()
	{		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['lang_name']))    $this->params['lang_name'] = prepare_input($_POST['lang_name']);
		if(isset($_POST['lang_name_en'])) $this->params['lang_name_en'] = prepare_input($_POST['lang_name_en']);
		if(isset($_POST['abbreviation'])) $this->params['abbreviation'] = prepare_input($_POST['abbreviation']);
		if(isset($_POST['lc_time_name'])) $this->params['lc_time_name'] = prepare_input($_POST['lc_time_name']);		
		if(isset($_POST['lang_dir']))     $this->params['lang_dir'] = prepare_input($_POST['lang_dir']);
		if(isset($_POST['priority_order']))   $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['used_on']))      $this->params['used_on'] = prepare_input($_POST['used_on']);

		## for checkboxes 
		$this->params['is_default'] = isset($_POST['is_default']) ? prepare_input($_POST['is_default']) : '0';
		$this->params['is_active'] = isset($_POST['is_active']) ? prepare_input($_POST['is_active']) : '0';

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

		$this->params['language_id'] = self::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_LANGUAGES; // TABLE_NAME
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=languages';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.priority_order ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
		);

		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_is_default = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		$arr_text_directions = array('ltr'=>_LEFT_TO_RIGHT, 'rtl'=>_RIGHT_TO_LEFT);
		$arr_used_on = array('global'=>_GLOBAL, 'front-end'=>'Front-End', 'back-end'=>'Back-End');
		$arr_lc_time_names = array(
			'sq_AL'=>'Albanian - Albania',						
			'ar_AE'=>'Arabic - United Arab Emirates',
			'ar_BH'=>'Arabic - Bahrain',
			'ar_DZ'=>'Arabic - Algeria',
			'ar_EG'=>'Arabic - Egypt',
			'ar_IN'=>'Arabic - India',
			'ar_IQ'=>'Arabic - Iraq',
			'ar_JO'=>'Arabic - Jordan',
			'ar_KW'=>'Arabic - Kuwait',
			'ar_LB'=>'Arabic - Lebanon',
			'ar_LY'=>'Arabic - Libya',
			'ar_MA'=>'Arabic - Morocco',
			'ar_OM'=>'Arabic - Oman',
			'ar_QA'=>'Arabic - Qatar',
			'ar_SA'=>'Arabic - Saudi Arabia',
			'ar_SD'=>'Arabic - Sudan',
			'ar_SY'=>'Arabic - Syria',
			'ar_TN'=>'Arabic - Tunisia',
			'ar_YE'=>'Arabic - Yemen',
			'eu_ES'=>'Basque - Basque',
			'be_BY'=>'Belarusian - Belarus',
			'bg_BG'=>'Bulgarian - Bulgaria',
			'ca_ES'=>'Catalan - Spain',
			'zh_CN'=>'Chinese - China',
			'zh_HK'=>'Chinese - Hong Kong',
			'zh_TW'=>'Chinese - Taiwan Province of China',
			'hr_HR'=>'Croatian - Croatia',
			'cs_CZ'=>'Czech - Czech Republic',
			'da_DK'=>'Danish - Denmark',
			'nl_BE'=>'Dutch - Belgium',
			'nl_NL'=>'Dutch - The Netherlands',
			'de_AT'=>'German - Austria',
			'de_BE'=>'German - Belgium',
			'de_CH'=>'German - Switzerland',
			'de_DE'=>'German - Germany',
			'de_LU'=>'German - Luxembourg',
			'en_AU'=>'English - Australia',
			'en_CA'=>'English - Canada',
			'en_GB'=>'English - United Kingdom',
			'en_IN'=>'English - India',
			'en_NZ'=>'English - New Zealand',
			'en_PH'=>'English - Philippines',
			'en_US'=>'English - United States',
			'en_ZA'=>'English - South Africa',
			'en_ZW'=>'English - Zimbabwe',
			'et_EE'=>'Estonian - Estonia',
			'fi_FI'=>'Finnish - Finland',
			'fo_FO'=>'Faroese - Faroe Islands',
			'fr_BE'=>'French - Belgium',
			'fr_CA'=>'French - Canada',
			'fr_CH'=>'French - Switzerland',
			'fr_FR'=>'French - France',
			'fr_LU'=>'French - Luxembourg',
			'gl_ES'=>'Galician - Spain',
			'el_GR'=>'Greek - Greece',
			'gu_IN'=>'Gujarati - India',
			'he_IL'=>'Hebrew - Israel',
			'hi_IN'=>'Hindi - India',
			'hu_HU'=>'Hungarian - Hungary',
			'id_ID'=>'Indonesian - Indonesia',
			'is_IS'=>'Icelandic - Iceland',
			'it_CH'=>'Italian - Switzerland',
			'it_IT'=>'Italian - Italy',
			'ja_JP'=>'Japanese - Japan',
			'ko_KR'=>'Korean - Republic of Korea',
			'lt_LT'=>'Lithuanian - Lithuania',
			'lv_LV'=>'Latvian - Latvia',
			'mk_MK'=>'Macedonian - FYROM',
			'mn_MN'=>'Mongolia - Mongolian',
			'ms_MY'=>'Malay - Malaysia',
			'nb_NO'=>'Norwegian(Bokm&aring;l) - Norway',
			'no_NO'=>'Norwegian - Norway',
			'pl_PL'=>'Polish - Poland',
			'pt_BR'=>'Portugese - Brazil',
			'pt_PT'=>'Portugese - Portugal',
			'ro_RO'=>'Romanian - Romania',
			'ru_RU'=>'Russian - Russia',
			'ru_UA'=>'Russian - Ukraine',
			'sk_SK'=>'Slovak - Slovakia',
			'sl_SI'=>'Slovenian - Slovenia',
			'sr_YU'=>'Serbian - Yugoslavia',
			'es_AR'=>'Spanish - Argentina',
			'es_BO'=>'Spanish - Bolivia',
			'es_CL'=>'Spanish - Chile',
			'es_CO'=>'Spanish - Columbia',
			'es_CR'=>'Spanish - Costa Rica',
			'es_DO'=>'Spanish - Dominican Republic',
			'es_EC'=>'Spanish - Ecuador',
			'es_ES'=>'Spanish - Spain',
			'es_GT'=>'Spanish - Guatemala',
			'es_HN'=>'Spanish - Honduras',
			'es_MX'=>'Spanish - Mexico',
			'es_NI'=>'Spanish - Nicaragua',
			'es_PA'=>'Spanish - Panama',
			'es_PE'=>'Spanish - Peru',
			'es_PR'=>'Spanish - Puerto Rico',
			'es_PY'=>'Spanish - Paraguay',
			'es_SV'=>'Spanish - El Salvador',
			'es_US'=>'Spanish - United States',
			'es_UY'=>'Spanish - Uruguay',
			'es_VE'=>'Spanish - Venezuela',
			'sv_FI'=>'Swedish - Finland',
			'sv_SE'=>'Swedish - Sweden',
			'ta_IN'=>'Tamil - India',
			'te_IN'=>'Telugu - India',
			'th_TH'=>'Thai - Thailand',
			'tr_TR'=>'Turkish - Turkey',
			'uk_UA'=>'Ukrainian - Ukraine',
			'ur_PK'=>'Urdu - Pakistan',
			'vi_VN'=>'Vietnamese - Viet Nam'
		);

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		/// $this->arrTranslations = $this->PrepareTranslateFields(
		///	array('field1', 'field2')
		/// );
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		/// $sql_translation_description = $this->PrepareTranslateSql(
		///	TABLE_XXX_DESCRIPTION,
		///	'gallery_album_id',
		///	array('field1', 'field2')
		/// );
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									id,
		                            lang_name,
									lang_name_en,
									abbreviation,
									lc_time_name,
									lang_dir,
									icon_image,
									priority_order,
									CONCAT(UCASE(MID(used_on,1,1)),MID(used_on,2)) as used_on,
									is_default,
									is_active,
								    CONCAT(lang_name, \' (\', UCASE(abbreviation), \')\') as lang_full_name,
									UCASE(lang_dir) as upper_lang_dir
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'lang_full_name' => array('title'=>_LANGUAGE_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'icon_image'     => array('title'=>_ICON_IMAGE, 'type'=>'image', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'image_width'=>'16px', 'image_height'=>'11px', 'target'=>'images/flags/', 'no_image'=>'no_image.gif'),
			'used_on'        => array('title'=>_USED_ON, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'upper_lang_dir' => array('title'=>_HDR_TEXT_DIRECTION, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),
			'is_default'     => array('title'=>_IS_DEFAULT, 'type'=>'enum',  'align'=>'center', 'width'=>'95px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_default),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'105px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
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
			'lang_name'    => array('title'=>_LANGUAGE_NAME, 'type'=>'textbox',  'required'=>true, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'', 'unique'=>true, 'visible'=>true),
			'lang_name_en' => array('title'=>_LANGUAGE_NAME.' (English)', 'type'=>'textbox',  'required'=>true, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'', 'unique'=>true, 'visible'=>true),
			'abbreviation' => array('title'=>_ABBREVIATION, 'type'=>'textbox',  'required'=>true, 'width'=>'40px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'', 'validation_type'=>'alpha', 'validation_minlength'=>'2', 'unique'=>true, 'visible'=>true),
			'lc_time_name' => array('title'=>_SERVER_LOCALE, 'type'=>'enum', 'required'=>true, 'width'=>'', 'readonly'=>false, 'default'=>'en_US', 'source'=>$arr_lc_time_names, 'unique'=>false, 'javascript_event'=>''),
			'lang_dir'     => array('title'=>_HDR_TEXT_DIRECTION, 'type'=>'enum', 'required'=>true, 'width'=>'', 'readonly'=>false, 'default'=>'ltr', 'source'=>$arr_text_directions, 'unique'=>false, 'javascript_event'=>''),
			'icon_image'   => array('title'=>_ICON_IMAGE, 'type'=>'image',    'required'=>false, 'width'=>'210px', 'readonly'=>false, 'target'=>'images/flags/', 'no_image'=>'no_image.gif', 'random_name'=>false, 'overwrite_image'=>false, 'unique'=>false, 'image_width'=>'16px', 'image_height'=>'11px', 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'16px', 'thumbnail_height'=>'11px', 'file_maxsize'=>'100k'),
			'used_on'      => array('title'=>_USED_ON, 'type'=>'enum', 'required'=>true, 'width'=>'', 'readonly'=>false, 'default'=>'global', 'source'=>$arr_used_on, 'unique'=>false, 'javascript_event'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'40px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_default'   => array('title'=>_IS_DEFAULT, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
			'is_active'    => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								id,
								lang_name,
								lang_name_en,
								abbreviation,
								lc_time_name,
								UCASE(abbreviation) as mod_abbreviation,
								lang_dir,
								icon_image,
								priority_order,
								used_on,
								CONCAT(UCASE(MID(used_on,1,1)),MID(used_on,2)) as mod_used_on,
								is_default,
								is_active,
								CONCAT(lang_name, " (", UCASE(abbreviation), ")") as lang_full_name,
								UCASE(lang_dir) as upper_lang_dir
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		

		// prepare trigger
		$sql = 'SELECT is_default FROM '.$this->tableName.' WHERE id = '.(int)self::GetParameter('rid');
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		$is_default = '0';
		if($result[1] > 0){
			$is_default = (isset($result[0]['is_default'])) ? $result[0]['is_default'] : '0';
		}

		// define edit mode fields
		$this->arrEditModeFields = array(
			'lang_name'    => array('title'=>_LANGUAGE_NAME, 'type'=>'textbox',  'required'=>true, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'', 'unique'=>true, 'visible'=>true),
			'lang_name_en' => array('title'=>_LANGUAGE_NAME.' (English)', 'type'=>'textbox',  'required'=>true, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'32', 'default'=>'', 'validation_type'=>'', 'unique'=>true, 'visible'=>true),
			'abbreviation' => array('title'=>_ABBREVIATION, 'type'=>'textbox',  'required'=>true, 'width'=>'40px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'', 'validation_type'=>'alpha', 'validation_minlength'=>'2', 'unique'=>true, 'visible'=>true),
			'lc_time_name' => array('title'=>_SERVER_LOCALE, 'type'=>'enum', 'required'=>true, 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_lc_time_names, 'unique'=>false, 'javascript_event'=>''),
			'lang_dir'     => array('title'=>_HDR_TEXT_DIRECTION, 'type'=>'enum', 'required'=>true, 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_text_directions, 'unique'=>false, 'javascript_event'=>''),
			'icon_image'   => array('title'=>_ICON_IMAGE, 'type'=>'image',    'required'=>false, 'width'=>'210px', 'readonly'=>false, 'target'=>'images/flags/', 'no_image'=>'no_image.gif', 'random_name'=>false, 'overwrite_image'=>false, 'unique'=>false, 'image_width'=>'24px', 'image_height'=>'17px', 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'16px', 'thumbnail_height'=>'11px', 'file_maxsize'=>'100k'),
			'used_on'      => array('title'=>_USED_ON, 'type'=>'enum', 'required'=>true, 'width'=>'', 'readonly'=>false, 'default'=>'', 'source'=>$arr_used_on, 'unique'=>false, 'javascript_event'=>''),
			'priority_order' => array('title'=>_ORDER, 'type'=>'textbox', 'required'=>true, 'width'=>'40px', 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'unique'=>false, 'visible'=>true),
			'is_default'   => array('title'=>_IS_DEFAULT, 'type'=>'checkbox', 'readonly'=>(($is_default) ? true : false), 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
			'is_active'    => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>(($is_default) ? true : false), 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'lang_name'      => array('title'=>_LANGUAGE_NAME, 'type'=>'label'),
			'lang_name_en' 	 => array('title'=>_LANGUAGE_NAME.' (English)', 'type'=>'label'),
			'mod_abbreviation' => array('title'=>_ABBREVIATION, 'type'=>'label'),
			'lc_time_name'   => array('title'=>_SERVER_LOCALE, 'type'=>'enum', 'source'=>$arr_lc_time_names),
			'icon_image'     => array('title'=>_ICON_IMAGE, 'type'=>'image', 'target'=>'images/flags/', 'no_image'=>'no_image.gif', 'image_width'=>'24px', 'image_height'=>'17px'),
			'mod_used_on'    => array('title'=>_USED_ON, 'type'=>'label'),
			'upper_lang_dir' => array('title'=>_HDR_TEXT_DIRECTION, 'type'=>'label'),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
			'is_default' 	 => array('title'=>_IS_DEFAULT, 'type'=>'enum', 'source'=>$arr_is_default),			
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		/// $this->AddTranslateToModes(
		/// $this->arrTranslations,
		/// array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'readonly'=>false),
		/// 	  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'readonly'=>false)
		/// )
		/// );
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
    // Public Methods
	//==========================================================================
	/**
	 *	Returns languages count
	 */
	public function GetLanguagesCount($used_on = '')
	{
		$where_clause = '';
		
		if($used_on == 'front-end') $where_clause = ' AND (used_on = \'global\' OR used_on = \'front-end\')';
		
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_LANGUAGES.' WHERE is_active = 1'.$where_clause;
		$language = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);        
		return isset($language['cnt']) ? $language['cnt'] : 0;
	}

	/**
	 * Draws languages bar
	 * @param bool $draw
	 * @param string $base_url
	 */
	public function DrawLanguagesBar($draw = true, $basedir = '')
	{
		global $objLogin, $objSettings;
		
		$allow_opacity = true;
		$opacity = '';
		$output = '';

		$all_languages = Languages::GetAllLanguages(' priority_order ASC', '', 'is_active = 1 AND (used_on = \'global\' OR used_on = \'front-end\')');
		if($all_languages > 0){
			for($i=0; $i < $all_languages[1]; $i++){
                $url = get_page_url();
                if(!empty($basedir)){
                    $basedir = rtrim($basedir, '/').'/';
				}
                $base_url = APPHP_BASE;
				if(!$objLogin->IsLoggedInAsAdmin()){
					// prevent wrong re-loading for some problematic cases
					$url = str_replace(array('page=search'), 'page=index', $url);				
					
					$url = str_replace(array('page=booking_payment'), 'page=booking_checkout', $url);
					//$url = str_replace(array('page=check_availability'), 'page=home', $url);

					if($objSettings->GetParameter('seo_urls') == '1' && !(defined('IS_WIDGET') && IS_WIDGET)){
						// remove currency parameters
						$url = str_replace('/'.Application::Get('currency_code').'/', '/', $url);

						if(preg_match('/\/'.Application::Get('lang').'\//i', $url)){
							$url = str_replace('/'.Application::Get('lang').'/', '/'.$all_languages[0][$i]['abbreviation'].'/', $url);						
						}else{
							$url = str_replace($base_url, $base_url.$all_languages[0][$i]['abbreviation'].'/', $url); 							
						}						
					}else{
						if(preg_match('/lang='.Application::Get('lang').'/i', $url)){
							$url = str_replace('lang='.Application::Get('lang'), 'lang='.$all_languages[0][$i]['abbreviation'], $url);						
						}else{
							$url .= (preg_match('/\?/', $url) ? '&amp;' : '?').'lang='.$all_languages[0][$i]['abbreviation'];						
						}
					}
				}
				if($allow_opacity = true){
					if(Application::Get('lang') == $all_languages[0][$i]['abbreviation']){
						$opacity = ' class="opacity_on"';
					}else{
						$opacity = ' class="opacity" onmouseover="opacity_onmouseover(this)" onmouseout="opacity_onmouseout(this)"';
					}
				}
				$output .= '<a href="'.$url.'" title="'.decode_text($all_languages[0][$i]['lang_name']).'">';
				$output .= ($all_languages[0][$i]['icon_image'] != '' && file_exists($basedir.'images/flags/'.$all_languages[0][$i]['icon_image'])
					? '<img src="'.APPHP_BASE.$basedir.'images/flags/'.$all_languages[0][$i]['icon_image'].'" height="11px" title="'.decode_text($all_languages[0][$i]['lang_name']).'" alt="'.decode_text($all_languages[0][$i]['lang_name']).'"'.$opacity.' />'
					: $all_languages[0][$i]['lang_name']);
				$output .= '</a> ';
			}
		}

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Returns language parameter
	 * @param $param
	 */
	public function GetParam($param = '')
	{
		if(isset($this->language[$param])){
			return $this->language[$param];
		}else{
			return '';
		}
	}

    /**
	 * Before Add Record
     */
	public function BeforeAddRecord()
	{ 
		$languages = Languages::GetAllLanguages();
		$max_order = (int)$languages[1];
		$this->arrAddModeFields['priority_order']['default'] = $max_order + 1;
	}

	/**
	 *	After Insert Record
	 */
	public function AfterInsertRecord()
	{
		$sql = 'SELECT * FROM '.TABLE_LANGUAGES.' WHERE id = '.(int)$this->lastInsertId;                    
		if($language = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){                        
			// define previous default language
			$sql = 'SELECT * FROM '.TABLE_LANGUAGES.' WHERE is_default = 1 AND id != '.(int)$this->lastInsertId;
			$previous_default = 'en';				
			if($result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
				$previous_default = $result['abbreviation'];					
			}
			// clone data from default language 
			$this->CopyDataToNewLang($previous_default, $language['abbreviation']);				
			
			// set default = 0 for other languages
			if(self::GetParameter('is_default', false) == '1'){
				$sql = 'UPDATE '.TABLE_LANGUAGES.'
						SET is_active = IF(id = '.(int)$this->lastInsertId.', 1, is_active),
						    is_default = IF(id = '.(int)$this->lastInsertId.', 1, 0)';
				database_void_query($sql);					
			}
		}
	}

	/**
	 *	After Insert Record
	 */
	public function AfterUpdateRecord()
	{
		$sql = 'SELECT * FROM '.TABLE_LANGUAGES.' WHERE id = '.(int)$this->curRecordId;                    
		if($language = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){                        
			// set default  = 0 for other languages
			if(self::GetParameter('is_default', false) == '1'){
				$sql = 'UPDATE '.TABLE_LANGUAGES.'
						SET is_active = IF(id = '.(int)$this->curRecordId.', 1, is_active),
						    is_default = IF(id = '.(int)$this->curRecordId.', 1, 0)';
				database_void_query($sql);					
			}			
		}			
	}
	
	/**
	 *	After Insert Record
	 */
	public function BeforeDeleteRecord()
	{
		$all_languages = Languages::GetAllLanguages('', '', 'is_active = 1');
		$this->d_languages_count = $all_languages[1];
		
		$sql = 'SELECT * FROM '.TABLE_LANGUAGES.' WHERE id = '.(int)$this->curRecordId;
		$result_current = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		// define language's abbreviation
		$this->d_language_abbrev = $result_current['abbreviation'];
		$this->d_priority_order = $result_current['priority_order'];

		if($this->d_languages_count > 1){
			if($result_current['is_default'] == '1'){
				$this->error = _DEFAULT_LANG_DELETE_ALERT;
				return false;
			}		
		}else{
			if($result_current['is_active'] == '1'){
				$this->error = _LANG_DELETE_LAST_ERROR;
				return false;	
			}			
		}
		return true;						
	}
	
	/**
	 *	After Delete Record
	 */
	public function AfterDeleteRecord()
	{
		$this->d_languages_count--;
		
		$sql = 'UPDATE '.TABLE_LANGUAGES.' SET priority_order = priority_order - 1 WHERE priority_order > '.(int)$this->d_priority_order;
		if(database_void_query($sql) >= 0){
			// there is the last
			if($this->d_languages_count == 1){ 
				$sql = 'UPDATE '.TABLE_LANGUAGES.' SET is_default = 1 WHERE is_active = 1';
				database_void_query($sql);					
			}
			$this->DeleteDataOfLang($this->d_language_abbrev);					
			return true;    
		}
	}
	

	//==========================================================================
    // Static Methods
	//==========================================================================	
	/**
	 *	Initialize class
	 */
	public static function Init()
	{
		$sql = 'SELECT
					id,
					lang_name,
					lang_name_en,
					abbreviation,
					lc_time_name,
					lang_dir,
					icon_image,
					priority_order,
					used_on,
					is_default,
					is_active 
				FROM '.TABLE_LANGUAGES;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		for($i=0; $i < $result[1]; $i++){
			$abbrev = $result[0][$i]['abbreviation'];
			self::$arrLanguageSettings[$abbrev]['lang_name'] = $result[0][$i]['lang_name'];
			self::$arrLanguageSettings[$abbrev]['lang_name_en'] = $result[0][$i]['lang_name_en'];
			self::$arrLanguageSettings[$abbrev]['abbreviation'] = $result[0][$i]['abbreviation'];
			self::$arrLanguageSettings[$abbrev]['lc_time_name'] = $result[0][$i]['lc_time_name'];
			self::$arrLanguageSettings[$abbrev]['lang_dir'] = $result[0][$i]['lang_dir'];
			self::$arrLanguageSettings[$abbrev]['icon_image'] = $result[0][$i]['icon_image'];
			self::$arrLanguageSettings[$abbrev]['icon_image'] = $result[0][$i]['priority_order'];
			self::$arrLanguageSettings[$abbrev]['used_on'] = $result[0][$i]['used_on'];
			self::$arrLanguageSettings[$abbrev]['is_default'] = $result[0][$i]['is_default'];
			self::$arrLanguageSettings[$abbrev]['is_active'] = $result[0][$i]['is_active'];
		}
	}	

	/**
	 * Get languages settings parameter
	 * @param $language_name
	 * @param $param_name
	 */
	public static function Get($language_name = '', $param_name = '')
	{
		return isset(self::$arrLanguageSettings[$language_name][$param_name]) ? self::$arrLanguageSettings[$language_name][$param_name] : '';
	}	

	/**
	 *	Returns default language
	 */
	public static function GetDefaultLang()
	{
		$def_language = 'en';
		$sql = 'SELECT abbreviation FROM '.TABLE_LANGUAGES.' WHERE is_default = 1';
		if($language = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$def_language = $language['abbreviation'];					
		}
		return $def_language;
	}

	/**
	 * Checks if language exists
	 * @param $lang_abbrev
	 */
	public static function LanguageExists($lang_abbrev)
	{
		global $objLogin;
		
		if($objLogin->IsLoggedInAs('owner','mainadmin','admin')){
			$used_on = ' AND (used_on = \'global\' || used_on = \'back-end\')';				
		}else{
			$used_on = ' AND (used_on = \'global\' || used_on = \'front-end\')';
		}
		
		$sql = 'SELECT abbreviation
				FROM '.TABLE_LANGUAGES.'
				WHERE abbreviation = \''.$lang_abbrev.'\' '.$used_on.' AND is_active = 1';
		if(database_query($sql, ROWS_ONLY) > 0){
			return true;
		}
		return false;
	}

	/**
	 * Checks if language is active
	 * @param $lang_abbrev
	 */
	public static function LanguageActive($lang_abbrev)
	{
		$sql = 'SELECT abbreviation
				FROM '.TABLE_LANGUAGES.'
				WHERE abbreviation = \''.$lang_abbrev.'\' AND is_active = 1';
		if(database_query($sql, ROWS_ONLY) > 0){
			return true;
		}
		return false;
	}

	/**
	 * Returns language direction
	 * @param $lang_abbrev
	 */
	public static function GetLanguageDirection($lang_abbrev = '')
	{
		$lang_dir = 'ltr';
		$sql = 'SELECT lang_dir FROM '.TABLE_LANGUAGES.' WHERE abbreviation = \''.$lang_abbrev.'\'';
		if($language = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$lang_dir = $language['lang_dir'];					
		}
		return $lang_dir;
	}
	
	/**
	 * Draws 'Used To' select box 
	 * @param $selected_item
	 */
	public static function GetUsedToBox($selected_item = '', $is_default = false)
	{
		$output  = '<select class="form_text" name="used_on" id="used_on">';
		$output .= '<option value="global" '.(($selected_item == 'global') ? 'selected="selected"' : '').'>'._GLOBAL.'</option>';
		$output .= '<option value="front-end" '.(($selected_item == 'front-end') ? 'selected="selected"' : '').'>Front-End</option>';
		if(!$is_default) $output .= '<option value="back-end" '.(($selected_item == 'back-end') ? 'selected="selected"' : '').'>Back-End</option>';
		$output .= '</select>';
		
		return $output;
	}
	
	/**
	 * Returns language name
	 * @param $lang_abbrev
	 */
	public static function GetLanguageName($lang_abbrev = '', $field = 'lang_name')
	{
		$lang_name = 'English';
		$sql = 'SELECT '.$field.' FROM '.TABLE_LANGUAGES.' WHERE abbreviation = \''.$lang_abbrev.'\'';
		if($language = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$lang_name = $language[$field];					
		}
		return $lang_name;
	}
	
	/**
	 * Returns language info
	 * @param $lang_abbrev
	 */
	public static function GetLanguageInfo($lang_abbrev = '')
	{
		$sql = 'SELECT * FROM '.TABLE_LANGUAGES.' WHERE abbreviation = \''.$lang_abbrev.'\'';
		return database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
	}

	/**
	 * Returns array of all active languages
	 * @return array
	 */
	public static function GetAllActive()
	{		
		global $objLogin;
		
		if($objLogin->IsLoggedInAs('owner','mainadmin','admin')){
			$used_on = ' AND (used_on = \'global\' || used_on = \'back-end\')';				
		}else{
			$used_on = ' AND (used_on = \'global\' || used_on = \'front-end\')';
		}
		
		$sql = 'SELECT
					id, lang_name, lang_name_en, abbreviation, lc_time_name, lang_dir, is_default, icon_image, used_on, priority_order, is_active,
					IF(is_default = 1, "<span style=color:#00a600>'._YES.'</span>", "'._NO.'") as is_default_verb,
					IF(is_active = 1, "<span style=color:#00a600>'._YES.'</span>", "<span style=color:#a60000>'._NO.'</span>") as is_active_verb
				FROM '.TABLE_LANGUAGES.'
				WHERE is_active = 1 '.$used_on.'
				ORDER BY priority_order ASC';			
		
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 *	Return select box with all active languages
	 */
	public static function GetAllActiveSelectBox($exclude_lang = '', $selected_lang = '', $params = array())
	{
		$select_class = isset($params['select_class']) ? ' class="'.$params['select_class'].'"' : '';
		$output = '<select name="selLanguages" id="selLanguages"'.$select_class.'>';
		$total_languages = Languages::GetAllActive();		
		foreach($total_languages[0] as $key => $val){
			$output .= '<option '.(($selected_lang == $val['abbreviation']) ? 'selected="selected"' : '').' ';
			if($exclude_lang != '' && $exclude_lang != $val['abbreviation']){
				$output .= 'value="'.$val['abbreviation'].'">'.$val['lang_name'].' &raquo; '.strtoupper($exclude_lang).'</option>';
			}else{
				$output .= 'value="'.$val['abbreviation'].'">'.$val['lang_name'].'</option>';
			}
		}
		$output .= '</select>';
		return $output;		
	}	

	/***
	 * Returns array of all languages 
	 * @param $order - order clause
	 * @param $join_table - join tables
	 * @param $where
	 */
	public static function GetAllLanguages($order = ' priority_order ASC', $join_table = '', $where = '')
	{		
		$where_clause = ($where != '') ? ' AND '.$where : '';
		
		// Build ORDER BY CLAUSE
		if($order == '') $order_clause = '';
		else $order_clause = 'ORDER BY '.$order;		

		// Build JOIN clause
		$join_clause = '';
		$join_select_fields = '';
		
		$sql = 'SELECT
					id, lang_name, lang_name_en, abbreviation, lc_time_name, lang_dir, is_default, icon_image, used_on, priority_order, is_active,
					IF(is_default = 1, "<span class=yes>'._YES.'</span>", "'._NO.'") as is_default_verb,
					IF(is_active = 1, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as is_active_verb
				FROM '.TABLE_LANGUAGES.'
					'.$join_clause.'
					WHERE 1=1 '.$where_clause.'
				'.$order_clause;			
		
		return database_query($sql, DATA_AND_ROWS);
	}


	//==========================================================================
    // Private Methods
	//==========================================================================	
	/**
	 * Copies default data for new language
	 * @param $from_abbrev
	 * @param $to_abbrev
	 */
	private function CopyDataToNewLang($from_abbrev, $to_abbrev)
	{
		// clone data for Menus table
		$sql = 'INSERT INTO '.TABLE_MENUS.' (language_id, menu_code, menu_name, menu_placement, menu_order, access_level)
						        (SELECT \''.$to_abbrev.'\', menu_code, menu_name, menu_placement, menu_order, access_level FROM '.TABLE_MENUS.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data for Vocabulary table
		$sql = 'INSERT INTO '.TABLE_VOCABULARY.' (language_id, key_value, key_text)
			                         (SELECT \''.$to_abbrev.'\', key_value, key_text FROM '.TABLE_VOCABULARY.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);					

		// clone data for Static Pages table
		$sql = 'INSERT INTO '.TABLE_PAGES.' (language_id, page_code, content_type, link_url, link_target, page_key, page_title, page_text, menu_id, menu_link, tag_title, tag_keywords, tag_description, comments_allowed, date_created, date_updated, is_home, is_removed, is_published, is_system_page, system_page, show_in_search, status_changed, access_level, priority_order)
			                    (SELECT \''.$to_abbrev.'\', page_code, content_type, link_url, link_target, page_key, page_title, page_text, menu_id, menu_link, tag_title, tag_keywords, tag_description, comments_allowed, date_created, date_updated, is_home, is_removed, is_published, is_system_page, system_page, show_in_search, status_changed, access_level, priority_order FROM '.TABLE_PAGES.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);					

		// clone data for News table
		$sql = 'INSERT INTO '.TABLE_NEWS.' (language_id, news_code, type, header_text, body_text, date_created)
							   (SELECT \''.$to_abbrev.'\', news_code, type, header_text, body_text, date_created FROM '.TABLE_NEWS.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data for Email Templates table
		$sql = 'INSERT INTO '.TABLE_EMAIL_TEMPLATES.' (language_id, template_code, template_name, template_subject, template_content, is_system_template)
					                      (SELECT \''.$to_abbrev.'\', template_code, template_name, template_subject, template_content, is_system_template FROM '.TABLE_EMAIL_TEMPLATES.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data for Gallery Albums Description table
		$sql = 'INSERT INTO '.TABLE_GALLERY_ALBUMS_DESCRIPTION.' (gallery_album_id, language_id, name, description)
						                             (SELECT gallery_album_id, \''.$to_abbrev.'\', name, description FROM '.TABLE_GALLERY_ALBUMS_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data for Gallery Album Items Description table
		$sql = 'INSERT INTO '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.' (gallery_album_item_id, language_id, name, description)
						                                  (SELECT gallery_album_item_id, \''.$to_abbrev.'\', name, description FROM '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data for Banners Description table
		$sql = 'INSERT INTO '.TABLE_BANNERS_DESCRIPTION.' (banner_id, language_id, image_text)
						                      (SELECT banner_id, \''.$to_abbrev.'\', image_text FROM '.TABLE_BANNERS_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data for Site Description table
		$sql = 'INSERT INTO '.TABLE_SITE_DESCRIPTION.' (language_id, header_text, slogan_text, footer_text, tag_title, tag_description, tag_keywords)
						                   (SELECT \''.$to_abbrev.'\', header_text, slogan_text, footer_text, tag_title, tag_description, tag_keywords FROM '.TABLE_SITE_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		$sql = 'INSERT INTO '.TABLE_HOTELS_DESCRIPTION.' (hotel_id, language_id, name, address, name_center_point, description, preferences)
											(SELECT hotel_id, \''.$to_abbrev.'\', name, address, \'\', description, preferences FROM '.TABLE_HOTELS_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data from Rooms Description table
		$sql = 'INSERT INTO '.TABLE_ROOMS_DESCRIPTION.' (room_id, language_id, room_type, room_short_description, room_long_description)
											(SELECT room_id, \''.$to_abbrev.'\', room_type, room_short_description, room_long_description FROM '.TABLE_ROOMS_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data from Meal Plans Description table
		$sql = 'INSERT INTO '.TABLE_MEAL_PLANS_DESCRIPTION.' (meal_plan_id, language_id, name, description)
											(SELECT meal_plan_id, \''.$to_abbrev.'\', name, description FROM '.TABLE_MEAL_PLANS_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data from Hotel Location Description table
		$sql = 'INSERT INTO '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' (hotel_location_id, language_id, name)
											(SELECT hotel_location_id, \''.$to_abbrev.'\', name FROM '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data from Room Facilities Description table
		$sql = 'INSERT INTO '.TABLE_ROOM_FACILITIES_DESCRIPTION.' (room_facility_id, language_id, name, description)
											(SELECT room_facility_id, \''.$to_abbrev.'\', name, description FROM '.TABLE_ROOM_FACILITIES_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data from Countries Description table
		$sql = 'INSERT INTO '.TABLE_COUNTRIES_DESCRIPTION.' (country_id, language_id, name)
											(SELECT country_id, \''.$to_abbrev.'\', name FROM '.TABLE_COUNTRIES_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		// clone data from Hotel Property Type Description table
		$sql = 'INSERT INTO '.TABLE_HOTELS_PROPERTY_TYPES_DESCRIPTION.' (hotel_property_id, language_id, name)
											(SELECT hotel_property_id, \''.$to_abbrev.'\', name FROM '.TABLE_HOTELS_PROPERTY_TYPES_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
		database_void_query($sql);

		if(Modules::IsModuleInstalled('car_rental')){
			// clone data from Hotel Description table
			$sql = 'INSERT INTO '.TABLE_CAR_AGENCIES_DESCRIPTION.' (agency_id, language_id, name, description)
											(SELECT agency_id, \''.$to_abbrev.'\', name, description FROM '.TABLE_CAR_AGENCIES_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
			database_void_query($sql);

			// clone data from Car Agency Location Description table
			$sql = 'INSERT INTO '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' (agency_location_id, language_id, name)
											(SELECT agency_location_id, \''.$to_abbrev.'\', name FROM '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
			database_void_query($sql);

			// clone data from Car Agency Makes Description table
			$sql = 'INSERT INTO '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' (make_id, language_id, name)
											(SELECT make_id, \''.$to_abbrev.'\', name FROM '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
			database_void_query($sql);

			// clone data from Car Agency Vehicles Description table
			$sql = 'INSERT INTO '.TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION.' (agency_vehicle_id, language_id, description)
											(SELECT agency_vehicle_id, \''.$to_abbrev.'\', description FROM '.TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
			database_void_query($sql);

			// clone data from Agency Vehicle Category Description table
			$sql = 'INSERT INTO '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' (agency_vehicle_category_id, language_id, name, description)
											(SELECT agency_vehicle_category_id, \''.$to_abbrev.'\', name, description FROM '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
			database_void_query($sql);
		}

		if(Modules::IsModuleInstalled('conferences')){
			// clone data from conference types translations table
			$sql = 'INSERT INTO `<DB_PREFIX>conference_types_translations` (conference_type_id, language_id, name, short_description, full_description)
											(SELECT conference_type_id, \''.$to_abbrev.'\', name, short_description, full_description FROM '.TABLE_CAR_AGENCIES_DESCRIPTION.' WHERE language_id = \''.$from_abbrev.'\')';
			database_void_query($sql);
		}

		// copy default messages.inc.php for new language
		$source_file = 'include/messages.'.$from_abbrev.'.inc.php';
		$desfination_file = 'include/messages.'.$to_abbrev.'.inc.php';
		@copy($source_file, $desfination_file);
	}
	
	/**
	 * Delete data of language
	 * @param $lang_abbrev
	 */
	private function DeleteDataOfLang($lang_abbrev)
	{	
		$sql = 'DELETE FROM '.TABLE_MENUS.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);					
		$sql = 'DELETE FROM '.TABLE_VOCABULARY.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);					
		$sql = 'DELETE FROM '.TABLE_PAGES.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);					
		$sql = 'DELETE FROM '.TABLE_NEWS.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);					
		$sql = 'DELETE FROM '.TABLE_EMAIL_TEMPLATES.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);					
		$sql = 'DELETE FROM '.TABLE_GALLERY_ALBUMS_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);					
		$sql = 'DELETE FROM '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);					
		$sql = 'DELETE FROM '.TABLE_BANNERS_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);					
		$sql = 'DELETE FROM '.TABLE_SITE_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_HOTELS_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);
		$sql = 'DELETE FROM '.TABLE_ROOMS_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);
		$sql = 'DELETE FROM '.TABLE_MEAL_PLANS_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);
		$sql = 'DELETE FROM '.TABLE_HOTELS_LOCATIONS_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);
		$sql = 'DELETE FROM '.TABLE_ROOM_FACILITIES_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);
		$sql = 'DELETE FROM '.TABLE_COUNTRIES_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);
		$sql = 'DELETE FROM '.TABLE_HOTELS_PROPERTY_TYPES_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
		database_void_query($sql);

		if(Modules::IsModuleInstalled('car_rental')){
			$sql = 'DELETE FROM '.TABLE_CAR_AGENCIES_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
			database_void_query($sql);
			$sql = 'DELETE FROM '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
			database_void_query($sql);
			$sql = 'DELETE FROM '.TABLE_CAR_AGENCY_MAKES_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
			database_void_query($sql);
			$sql = 'DELETE FROM '.TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
			database_void_query($sql);
			$sql = 'DELETE FROM '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' WHERE language_id = \''.$lang_abbrev.'\'';
			database_void_query($sql);
		}

		if(Modules::IsModuleInstalled('conferences')){
			$sql = 'DELETE FROM `<DB_PREFIX>conference_types_translations` WHERE language_id = \''.$lang_abbrev.'\'';
			database_void_query($sql);
		}

		// delete language file
		@unlink('include/messages.'.$lang_abbrev.'.inc.php');
	}
	
}
