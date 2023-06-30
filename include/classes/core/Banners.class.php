<?php

/**
 * 	Class Banners
 *  -------------- 
 *  Description : encapsulates banners properties
 *	Written by  : ApPHP
 *	Version     : 1.1.9
 *  Updated	    : 19.01.2015
 *  Usage       : Core Class
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             GetBanners              ValidateTranslationFields
 *	__destruct              GetRandomBanner
 *	BeforeInsertRecord      GetBannersArray
 *	AfterInsertRecord       DrawBannersTop
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDeleteRecord
 *
 *
 *	ChangeLog:
 *  1.1.9
 *  	- added link_url on main view
 *  	-
 *  	-
 *  	-
 *  1.1.8
 *      - added "enum" types for YES/NO fields
 *      - replaced $objBanners = new Banners();
 *      - added automatical adding max priority order for new record
 *      - added maxlength for textarea
 *      - added column page for banners
 *  1.1.7
 *      - changes in DrawBannersTop
 *      - added focus on translate fields
 *      - added prepare_input() for translate fields
 *      - added possibility to use HTML in caption
 *      - added 'format'=>'strip_tags' for text in View Mode
 *  1.1.6
 *      - changes path to jquery.cross-slide.min.js
 *      - changed " with '
 *      - changes location.href with appGoToPage()
 *      - added APPHP_BASE to paths
 *      - added ModulesSettings::Get();
 *  1.1.5
 *  	- added default value for priority_order in Add Mode
 *  	- removed unexpected 'default'=>val['value'] from add mode fields
 *  	- fixed bug with empty values for translation fields on age reloading
 *  	- improved working with translation fields
 *  	- added DrawBannersTop()
 *  1.1.4
 *  	- added encapsulation of working with table description (for multi-languages)
 *  	- re-done SELECTs in View and Edit Modes
 *  	- added maxlength for edit mode and reduced width
 *  	- added tramesets for add/edit/detail mode
 *  	- added automatical addition of http:// for 'link_url' fields
 *	
 *	
 **/


class Banners extends MicroGrid {
	
	protected $debug = false;
	
	//------------------------
	private $arrTranslations = '';		
	
	private $pages = array();

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		parent::__construct();
		
		global $objLogin;
		
		$this->params = array();		
		if(isset($_POST['link_url'])){
			$link_url = prepare_input($_POST['link_url'], false, 'medium');
			if(preg_match('/www./i', $link_url) && !preg_match('/http:/i', $link_url)){
				$link_url = 'http://'.$link_url;
			}
			$this->params['link_url'] = $link_url;
		}
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))  $this->params['is_active'] = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		if(isset($_POST['page'])) 			$this->params['page'] = prepare_input($_POST['page']);
		///$this->params['language_id'] 	= MicroGrid::GetParameter('language_id');

		## for images
		if(isset($_POST['image_file'])){
			$this->params['image_file'] = prepare_input($_POST['image_file']);
		}elseif(isset($_FILES['image_file']['name']) && $_FILES['image_file']['name'] != ''){
			// nothing 			
		}elseif(self::GetParameter('action') == 'create'){
			$this->params['image_file'] = '';
		}
		
		$properties = HotelsPropertyTypes::GetHotelsPropertyTypes();
		$this->pages = array(''=>_ALL);
		foreach($properties as $key => $val){
			$this->pages['check_'.$val['property_code']] = $val['name'];
		}

		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_BANNERS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_banners_management';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = ''; //'WHERE ';		
		$this->ORDER_CLAUSE = 'ORDER BY page ASC, priority_order ASC'; // ORDER BY date_created DESC

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_PAGE  => array('table'=>'b', 'field'=>'page', 'type'=>'dropdownlist', 'source'=>$this->pages, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
			//'parameter1' => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
			//'parameter2'  => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
		);
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		
		$slideshow_caption_html = ModulesSettings::Get('banners', 'slideshow_caption_html');

		///////////////////////////////////////////////////////////////////////////////
		// 1. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('image_text')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// 2. prepare translations array for edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_BANNERS_DESCRIPTION,
			'banner_id',
			array('image_text')
		);
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//----------------------------------------------------------------------
		$this->VIEW_MODE_SQL = 'SELECT b.'.$this->primaryKey.',
									b.image_file,
									b.image_file_thumb,
									b.link_url,
									b.page,
									b.priority_order,
									b.is_active,
									bd.image_text as image_text
								FROM ('.$this->tableName.' b
									LEFT OUTER JOIN '.TABLE_BANNERS_DESCRIPTION.' bd ON b.id = bd.banner_id AND bd.language_id = \''.$this->languageId.'\')';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'image_file_thumb' => array('title'=>_BANNER_IMAGE, 'type'=>'image', 'sortable'=>false, 'align'=>'left', 'width'=>'150px', 'image_width'=>'120px', 'image_height'=>'30px', 'target'=>'images/banners/', 'no_image'=>'no_image.png'),
			'image_text'   	   => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'70', 'format'=>'strip_tags'),
			'link_url'  		=> array('title'=>_URL, 'type'=>'link',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'href'=>'{link_url}', 'target'=>'_blank'),
			'page'   	   	 	=> array('title'=>_PAGE, 'type'=>'enum',  'align'=>'center', 'width'=>'150px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->pages),
			'priority_order'   => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'60px', 'movable'=>true),
			'is_active'        => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'130px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$max_order = (self::GetParameter('action') == 'add') ? $this->GetMaxOrder('priority_order', 99) : 0;
		$this->arrAddModeFields = array(
			'separator_general' =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'image_file'     => array('title'=>_BANNER_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'images/banners/', 'no_image'=>'', 'random_name'=>'true', 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_thumb', 'thumbnail_width'=>'140px', 'thumbnail_height'=>'30px', 'file_maxsize'=>'1000k'),
				'link_url'       => array('title'=>_URL.' (http://)', 'type'=>'textbox',  'width'=>'270px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'maxlength'=>'255'),
				'page'  		 => array('title'=>_PAGE, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->pages, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
				'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'default'=>$max_order, 'readonly'=>false, 'validation_type'=>'numeric', 'maxlength'=>'2'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0'),
			)
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		// define edit mode fields
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->primaryKey.',
								image_file,
								image_file_thumb,
								page,
								'.$sql_translation_description.'
								link_url,
								priority_order,
								is_active,
								CONCAT(\'<img src="images/banners/\', image_file_thumb, \'" alt="icon" width="140px" height="30px" />\') as my_image_file
							FROM '.$this->tableName.'
							WHERE '.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_general' =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'image_file'     => array('title'=>_BANNER_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'images/banners/', 'no_image'=>'', 'image_width'=>'280px', 'image_height'=>'90px', 'random_name'=>'true', 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_thumb', 'thumbnail_width'=>'140px', 'thumbnail_height'=>'30px'),
				'link_url'       => array('title'=>_URL.' (http://)', 'type'=>'textbox',  'width'=>'270px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'maxlength'=>'255'),
				'page'  		 => array('title'=>_PAGE, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->pages, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
				'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric', 'maxlength'=>'2'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'true_value'=>'1', 'false_value'=>'0'),
			)
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_general' =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'image_file'     => array('title'=>_BANNER_IMAGE, 'type'=>'image', 'target'=>'images/banners/', 'image_width'=>'280px', 'image_height'=>'90px', 'no_image'=>'no_image.png', 'file_maxsize'=>'1000k'),
				'link_url'       => array('title'=>_URL, 'type'=>'label'),
				'page'  		 => array('title'=>_PAGE, 'type'=>'enum',  'source'=>$this->pages),
				'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
			)
		);

		///////////////////////////////////////////////////////////////////////////////
		// 3. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array('image_text' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'2048', 'validation_maxlength'=>'2048', 'readonly'=>false, 'post_html'=>(($slideshow_caption_html == 'yes') ? '<br>'._CAN_USE_TAGS_MSG.' &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;br&gt;' : '')))
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
	 * Returns random banner image
	 */
	public static function GetBanners()
	{
		$output = '';
		
		$sql = 'SELECT
					b.id, b.image_file, b.link_url, b.priority_order,
					bd.image_text
				FROM '.TABLE_BANNERS.' b
					LEFT OUTER JOIN '.TABLE_BANNERS_DESCRIPTION.' bd ON b.id = bd.banner_id
				WHERE b.is_active = 1 AND b.image_file != \'\' AND bd.language_id = \''.encode_text(Application::Get('lang')).'\' 
				ORDER BY RAND() ASC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$image = '<img src="'.APPHP_BASE.'images/banners/'.$result[0]['image_file'].'" width="723px" height="140px" alt="banner" />';	
			if($result[0]['link_url'] != '' && $result[0]['link_url'] != 'http://'){
				$output .= '<a href="'.$result[0]['link_url'].'" title="'.$result[0]['image_text'].'">'.$image.'</a>';
			}else{
				$output .= $image;
			}			
		}
	    return $output;
	}

	/**
	 * Returns random banner
	 */
	public static function GetRandomBanner()
	{
		$output = '';
		
		$sql = 'SELECT 
					b.id, b.image_file, b.page, b.link_url, b.priority_order,
					bd.image_text
				FROM '.TABLE_BANNERS.' b
					LEFT OUTER JOIN '.TABLE_BANNERS_DESCRIPTION.' bd ON b.id = bd.banner_id
				WHERE b.is_active = 1 AND b.image_file != \'\' AND bd.language_id = \''.encode_text(Application::Get('lang')).'\' 
				ORDER BY RAND() ASC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$image = '<img src="'.APPHP_BASE.'images/banners/'.$result[0]['image_file'].'" title="'.$result[0]['image_text'].'" width="100%" height="140px" alt="banner" />';
			if($result[0]['link_url'] != '' && $result[0]['link_url'] != 'http://'){
				$output .= '<a href="'.$result[0]['link_url'].'" title="'.$result[0]['image_text'].'">'.$image.'</a>';
			}else{
				$output .= $image;
			}			
		}
	    return $output;
	}

	/**
	 * Returns banners array
	 * @param string $page
	 * @return HTML
	 */
	/**
	 * Returns banners array
	 * @param mixed $page
	 * @return HTML
	 */
	public static function GetBannersArray($page = '')
	{
		$output = array();
		
		$sql = 'SELECT 
					b.id, b.image_file, b.page, b.link_url, b.priority_order,
					bd.image_text
				FROM '.TABLE_BANNERS.' b
					LEFT OUTER JOIN '.TABLE_BANNERS_DESCRIPTION.' bd ON b.id = bd.banner_id
				WHERE
					b.is_active = 1 AND b.image_file != \'\' AND bd.language_id = \''.encode_text(Application::Get('lang')).'\'';
					
		if(is_array($page)){
			$sql .= !empty($page) ? " AND b.page IN ('".implode("','", $page)."')" : '';
		}else{
			$sql .= !empty($page) ? " AND b.page = '".$page."'" : '';
		}
					
		$sql .= ' ORDER BY priority_order ASC';
		
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			$output = $result[0];
		}
	    return $output;
	}
	
	/**
	 * Draw top banners code
	 * @param $banner_image
	 * @param $show_always
	 * @param $draw
	 */
	public static function DrawBannersTop(&$banner_image, $show_always = true, $draw = true)
	{
		global $objLogin;
		
		$default_banner_image = '';
		$nl = "\n";
		
		if(Modules::IsModuleInstalled('banners')){
			$is_banners_active = ModulesSettings::Get('banners', 'is_active');
			$rotate_delay	   = ModulesSettings::Get('banners', 'rotate_delay'); 
			$rotation_type	   = ModulesSettings::Get('banners', 'rotation_type');
			$caption_html      = ModulesSettings::Get('banners', 'slideshow_caption_html');

			if($is_banners_active == 'yes'){
				$objBanners = new Banners();
				if($rotation_type == 'slide show'){
					$arrBanners = $objBanners->GetBannersArray();				
					if($show_always || (!$show_always && Application::Get('page') == 'home' && !$objLogin->IsLoggedIn())){						
						$output = '<script src="'.APPHP_BASE.'modules/cslide/jquery.cross-slide.min.js" type="text/javascript"></script>'.$nl;
						$output .= '<script type="text/javascript">'.$nl;
						$output .= 'jQuery(function() {
							jQuery(\'#slideshow\').crossSlide({
							  sleep: '.$rotate_delay.', fade: 2,variant: true
							}, [						
						';
						$ind = '0';
						foreach($arrBanners as $key => $val){
							if($ind == '0'){
								$default_banner_image = 'images/banners/'.$val['image_file'];
							}else{
								$output .= ',';
							}
							$output .= '{ src: \'images/banners/'.$val['image_file'].'\', alt: \''.encode_text($val['image_text']).(($val['link_url'] != '') ? '##'.$val['link_url'] : '').'\', to:\'up\' }';
							$ind++;
						}
						$output .= '], function(idx, img, idxOut, imgOut) {
							var img_alt_split = img.alt.split(\'##\');
							var caption_width = jQuery(\'div#slideshow\').width() - 20;						
							if(idxOut == undefined){							
							  /* starting single image phase, put up caption */
							  if(img.alt != \'\'){
								jQuery(\'div.slideshow-caption\').click(function(){ if(img_alt_split[1] != undefined && img_alt_split[1] != \'\') appGoToPage(img_alt_split[1]); });
								jQuery(\'div.slideshow-caption\').'.(($caption_html == 'yes') ? 'html' : 'text').'(img_alt_split[0]).animate({ opacity: .7 })
								jQuery(\'div.slideshow-caption\').fadeIn();
								if(caption_width != null) jQuery(\'div.slideshow-caption\').width(caption_width);
							  }
							}else{
							  // starting cross-fade phase, take out caption
							  jQuery(\'div.slideshow-caption\').click(function() { });
							  jQuery(\'div.slideshow-caption\').fadeOut();
							}}) });';
						$output .= '</script>'.$nl;
						if($ind == 1){
							$banner_image = '<div class="banners-box-random" id="slideshow">'.$objBanners->GetRandomBanner().'</div>';
						}else{
							if($draw) echo $output; else return $output;							
							$banner_image = '<div class="banners-box-slideshow" id="slideshow"></div><div class="slideshow-caption"></div>';						
						}
					}
				}else{
					if($show_always || (!$show_always && Application::Get('page') == 'home' && !$objLogin->IsLoggedIn())){
						$banner_image = '<div class="banners-box-random" id="slideshow">'.$objBanners->GetRandomBanner().'</div>';
					}
				}					
			}
		}
	}
	
	
	////////////////////////////////////////////////////////////////////
	// BEFORE/AFTER METHODS
	///////////////////////////////////////////////////////////////////
	/**
	 * Validate translation fields
	 */
	private function ValidateTranslationFields()	
	{
		// check for required fields		
		foreach($this->arrTranslations as $key => $val){			
			if(strlen($val['image_text']) > 2048){
				$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 2048, $this->error);
				$this->errorField = 'image_text_'.$key;
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
	 * After-Addition - add banner descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		$sql = 'INSERT INTO '.TABLE_BANNERS_DESCRIPTION.'(id, banner_id, language_id, image_text) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.(int)$this->lastInsertId.', \''.encode_text($key).'\', \''.encode_text(prepare_input($val['image_text'])).'\')';
			$count++;
		}
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}	

	/**
	 * Before-Update operations
	 */
	public function BeforeUpdateRecord()
	{
		return $this->ValidateTranslationFields();
	}

	/**
	 * After-Updating - update banner descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_BANNERS_DESCRIPTION.'
					SET image_text = \''.encode_text(prepare_input($val['image_text'])).'\'
					WHERE banner_id = '.$this->curRecordId.' AND language_id = \''.encode_text($key).'\'';
			database_void_query($sql);
		}
	}	

	/**
	 * After-Deleting - delete banner descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.TABLE_BANNERS_DESCRIPTION.' WHERE banner_id = '.(int)$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}
	
}
