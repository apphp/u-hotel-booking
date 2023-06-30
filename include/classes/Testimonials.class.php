<?php

/**
 *	Testimonials Class
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *	Version     : 1.0.2
 *  Updated	    : 30.12.2015
 *	Differences : no
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct             DrawTestimonials
 *	__destruct
 *	
 *  1.0.2
 *  	 
 *  1.0.1
 *  	- author_email made not required
 *  	- changes is_active to enum type
 *  	- added maxlength to textareas
 *  	- syntax eror in function name
 *  	- added span for testimonials text
 *
 *	
 **/

class Testimonials extends MicroGrid {
	
	protected $debug = false;
	
	// #001 private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['author_name']))    $this->params['author_name'] = prepare_input($_POST['author_name']);
		if(isset($_POST['author_country'])) $this->params['author_country'] = prepare_input($_POST['author_country']);
		if(isset($_POST['author_city']))    $this->params['author_city'] = prepare_input($_POST['author_city']);
		if(isset($_POST['author_email']))   $this->params['author_email'] = prepare_input($_POST['author_email']);
		if(isset($_POST['testimonial_text'])) $this->params['testimonial_text'] = prepare_input($_POST['testimonial_text']);		
		if(isset($_POST['is_active']))      $this->params['is_active'] = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']); 
		
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
		$this->tableName 	= TABLE_TESTIMONIALS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_testimonials_management';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY priority_order ASC'; // ORDER BY date_created DESC
		
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

		$total_countries = Countries::GetAllCountries();
		$arr_countries   = array();
		foreach($total_countries[0] as $key => $val){
			$arr_countries[$val['abbrv']] = $val['name'];
		}
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

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
		$this->VIEW_MODE_SQL = 'SELECT t.'.$this->primaryKey.',
									t.author_name,
									t.author_country,
									t.author_city,
									t.author_email,
									t.testimonial_text,
									t.priority_order,
									t.is_active,
									cd.name as country_name
								FROM '.$this->tableName.' t
									LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON t.author_country = c.abbrv AND c.is_active = 1
									LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.Application::Get('lang').'\'
								';
		// define view mode fields
		$this->arrViewModeFields = array(

			'author_name'    => array('title'=>_CUSTOMER,      'type'=>'label', 'align'=>'left', 'width'=>'190px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'20', 'format'=>'', 'format_parameter'=>''),
			'author_email'   => array('title'=>_EMAIL_ADDRESS, 'type'=>'link',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'30', 'format'=>'', 'format_parameter'=>'', 'href'=>'mailto://{email}', 'target'=>''),
			'country_name'   => array('title'=>_COUNTRY,       'type'=>'label', 'align'=>'left', 'width'=>'170px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'author_city'    => array('title'=>_CITY,          'type'=>'label', 'align'=>'left', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'is_active'      => array('title'=>_ACTIVE,        'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			'priority_order' => array('title'=>_ORDER,         'type'=>'label', 'align'=>'center', 'width'=>'80px', 'movable'=>true),
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

			'author_name'      => array('title'=>_CUSTOMER, 'type'=>'textbox', 'required'=>true, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			'author_country'   => array('title'=>_COUNTRY, 'type'=>'enum',   'required'=>true, 'width'=>'210px', 'readonly'=>false, 'default'=>'', 'source'=>$arr_countries, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
			'author_city'      => array('title'=>_CITY, 'type'=>'textbox', 'required'=>false, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			'author_email'     => array('title'=>_EMAIL_ADDRESS, 'type'=>'textbox', 'required'=>false, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>'email', 'unique'=>false, 'visible'=>true),
			'testimonial_text' => array('title'=>_TEXT, 'type'=>'textarea', 'required'=>true, 'width'=>'410px', 'height'=>'140px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>1024, 'validation_maxlength'=>1024),
			'priority_order'   => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'default'=>'0', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric|positive'),
			'is_active'        => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),

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
								'.$this->primaryKey.',
								author_name,
								author_country,
								author_city,
								author_email,
								testimonial_text,
								is_active,
								priority_order
							FROM '.$this->tableName.'
							WHERE '.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
		
			'author_name'      => array('title'=>_CUSTOMER, 'type'=>'textbox', 'required'=>true, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			'author_country'   => array('title'=>_COUNTRY, 'type'=>'enum',   'required'=>true, 'width'=>'210px', 'readonly'=>false, 'default'=>'', 'source'=>$arr_countries, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
			'author_city'      => array('title'=>_CITY, 'type'=>'textbox', 'required'=>false, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			'author_email'     => array('title'=>_EMAIL_ADDRESS, 'type'=>'textbox', 'required'=>false, 'width'=>'210px', 'readonly'=>false, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>'email', 'unique'=>false, 'visible'=>true),
			'testimonial_text' => array('title'=>_TEXT, 'type'=>'textarea', 'required'=>true, 'width'=>'410px', 'height'=>'140px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>1024, 'validation_maxlength'=>1024),
			'priority_order'   => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric|positive'),
			'is_active'        => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),

		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(

			'author_name'      => array('title'=>_CUSTOMER, 'type'=>'label'),
			'author_country'   => array('title'=>_COUNTRY, 'type'=>'enum', 'source'=>$arr_countries),
			'author_city'      => array('title'=>_CITY, 'type'=>'label'),
			'author_email'     => array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
			'testimonial_text' => array('title'=>_TEXT, 'type'=>'label'),
			'priority_order'   => array('title'=>_ORDER, 'type'=>'label'),
			'is_active'        => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
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


	/**
	 * Draw Testimonials
	 */
	public static function DrawTestimonials($draw = true)
	{
		$output = '';
		$lang = Application::Get('lang');
		
		$sql = 'SELECT t.id,
					t.author_name,
					t.author_country,
					t.author_city,
					t.author_email,
					t.testimonial_text,
					t.priority_order,
					t.is_active,
					cd.name as country_name
				FROM '.TABLE_TESTIMONIALS.' t
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON t.author_country = c.abbrv AND c.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
				WHERE t.is_active = 1
				ORDER BY t.priority_order ASC';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		
		for($i=0; $i<$result[1]; $i++){
			$address = ($result[0][$i]['author_city'] != '') ? $result[0][$i]['author_city'].' ('.$result[0][$i]['country_name'].')' : $result[0][$i]['country_name'];
			$output .= '<strong><u>'.$result[0][$i]['author_name'].'</u></strong>, '.$address.'<br>';
			$output .= '<span class="quore">'.$result[0][$i]['testimonial_text'].'</span><br><br>';
		}
	
		if($draw) echo $output;		
		else return $output;
	}

}
