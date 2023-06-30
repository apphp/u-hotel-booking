<?php

/**
 * 	Class GalleryAlbums
 *  -------------- 
 *  Description : encapsulates gallery albums properties
 *	Written by  : ApPHP
 *	Version     : 1.1.1
 *  Updated	    : 13.01.2016
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             SetLibraries            ValidateTranslationFields
 *	__destruct
 *	SetSQLs
 *	GetAlbumInfo
 *	DrawAlbum
 *	DrawGallery
 *	BeforeInsertRecord
 *	AfterInsertRecord
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	BeforeDeleteRecord
 *	AfterDeleteRecord
 *	
 *  1.1.1
 *      - DrawGallery redone for using with templates
 *      -
 *      -
 *      -
 *      -
 *  1.1.0
 *	    - added 'maxlength' for textarea
 *      - added 'maxlength'=>'50' for description in view mode
 *      - added new settings 'show_items_numeration_in_album'
 *      - added class to gallery table
 *      - added rtl to lytebox
 *  1.0.9
 *	    - added focus on translate fields
 *      - added prepare_input() for translate fields
 *      - changed CASE SQLs with 'enum' types
 *      - removed anneded characters from href="" for video album links
 *      - added automatical adding max priority order for new record
 *  1.0.8
 *      - item_text changed into name in GalleryAlbumItems
 *      - fixed bug for R-o-k-B-o-x with rel='' attribute in tags
 *      - added $draw param for draw functions
 *      - all href='index... replaced with prepare_permanent_link
 *      - added APPHP_BASE for all images and links
 *  1.0.7
 *      - random_string() changed with get_random_string()
 *      - added new property to gallery settings : show_items_count_in_album
 *      - improved DrawAlbum() - added possibility to draw {module:album=CODE:open}
 *      - removed white-space:nowrap
 *      - fixed wrong priority order for items in album
 *  1.0.6
 *  	- changed DrawAlbum
 *  	- added default value for priority_order in Add Mode
 *  	- fixed bug with empty values for translation fields on age reloading
 *  	- fixed error in SetLibraries() - missed $output = '';
 *  	- improved working with translation fields
 *  1.0.5
 *      - wrong text for video items on drawing album
 *      - fixed error on showing empty video album
 *      - fixed error - text for DIV wrapper was not showing for images
 *      - added params for youtube link - &amp;hd=1&amp;autoplay=1
 *      - added check for video album icon      
 **/


class GalleryAlbums extends MicroGrid {
	
	protected $debug = false;
	
	//----------------------------
	private $arrTranslations = '';		
	private $curAlbumCode = '';
	private $curAlbumType = '';
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();

		global $objLogin;

		$this->params = array();		
		if(isset($_POST['album_code']))     $this->params['album_code'] = prepare_input($_POST['album_code']);
		if(isset($_POST['album_type']))     $this->params['album_type'] = prepare_input($_POST['album_type']);
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))      $this->params['is_active'] = prepare_input($_POST['is_active']); else $this->params['is_active'] = '0';
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_GALLERY_ALBUMS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_gallery_management';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = ''; // WHERE...
		$this->ORDER_CLAUSE = 'ORDER BY priority_order ASC'; // ORDER BY date_created DESC

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			//'parameter1' => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
			//'parameter2'  => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
		);

		///////////////////////////////////////////////////////////////////////////////
		// 1. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// 2. prepare translations array for edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_GALLERY_ALBUMS_DESCRIPTION,
			'gallery_album_id',
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		// prepare album types array		
		$arr_album_types = array('images'=>_IMAGES, 'video'=>_VIDEO);
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									ga.'.$this->primaryKey.',
		                            ga.album_code,
									ga.album_type,
									UCASE(ga.album_code) as mod_album_code,
									CONCAT(UCASE(SUBSTRING(ga.album_type, 1, 1)),LCASE(SUBSTRING(ga.album_type, 2))) as mod_album_type,
									gad.name,
									gad.description,
									ga.priority_order,
									ga.is_active,
									CONCAT("<a href=index.php?admin=mod_gallery_upload_items&album=", album_code, ">'._UPLOAD.'</a> (", (SELECT COUNT(*) as cnt FROM '.TABLE_GALLERY_ALBUM_ITEMS.' gi WHERE gi.album_code = ga.album_code) , ")") as link_upload_items
								FROM ('.$this->tableName.' ga	
									LEFT OUTER JOIN '.TABLE_GALLERY_ALBUMS_DESCRIPTION.' gad ON ga.id = gad.gallery_album_id AND gad.language_id = \''.$this->languageId.'\')';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'  			 => array('title'=>_ALBUM_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'15%', 'maxlength'=>'30'),
			'description'    	 => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'50'),
			'mod_album_code'  	 => array('title'=>_ALBUM_CODE, 'type'=>'label', 'align'=>'center', 'width'=>'12%'),
			'mod_album_type'     => array('title'=>_TYPE, 'type'=>'label', 'align'=>'center', 'width'=>'8%'),
			'is_active'          => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'8%', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			'priority_order' 	 => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'8%', 'movable'=>true),
			'link_upload_items'  => array('title'=>_ITEMS, 'type'=>'label', 'align'=>'center', 'width'=>'12%'),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$max_order = (self::GetParameter('action') == 'add') ? $this->GetMaxOrder('priority_order', 999) : 0;
		$this->arrAddModeFields = array(
			'separator_general'   =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'album_code'     => array('title'=>'',      'type'=>'hidden',   'required'=>true, 'readonly'=>false, 'default'=>get_random_string(8)),
				'album_type'     => array('title'=>_TYPE,   'type'=>'enum',     'required'=>true, 'readonly'=>false, 'source'=>$arr_album_types),
				'priority_order' => array('title'=>_ORDER,  'type'=>'textbox',  'width'=>'50px', 'maxlength'=>'3', 'default'=>$max_order, 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'true_value'=>'1', 'false_value'=>'0', 'default'=>'1'),
			)
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		// define edit mode fields
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								UCASE('.$this->tableName.'.album_code) as album_code,
								'.$this->tableName.'.album_type,
								CONCAT(UCASE(SUBSTRING('.$this->tableName.'.album_type, 1, 1)),LCASE(SUBSTRING('.$this->tableName.'.album_type, 2))) as mod_album_type,
								'.$sql_translation_description.'
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_general'  =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'album_code'     => array('title'=>_CODE,  'type'=>'label'),
				'album_type'     => array('title'=>_TYPE,    'type'=>'enum',     'required'=>true, 'readonly'=>false, 'source'=>$arr_album_types),
				'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric'),
				'is_active'      => array('title'=>_ACTIVE,  'type'=>'checkbox', 'readonly'=>false, 'true_value'=>'1', 'false_value'=>'0'),
			)
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_general'  =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'album_code'     => array('title'=>_CODE,    'type'=>'label'),
				'mod_album_type' => array('title'=>_TYPE,    'type'=>'label'),
				'priority_order' => array('title'=>_ORDER,   'type'=>'label'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
			)
		);

		///////////////////////////////////////////////////////////////////////////////
		// 3. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
				  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'255', 'validation_maxlength'=>'255', 'readonly'=>false)
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
	 * Set system SQLs
	 * @param $key
	 * @param $msg
	 */	
	public function SetSQLs($key, $msg)
	{
		if($this->debug) $this->arrSQLs[$key] = $msg;					
	}

	/**
	 * Returns album info
	 * @param $album_code
	 */
	public function GetAlbumInfo($album_code)
	{
		$sql = 'SELECT
					ga.id,
					ga.album_code,
					ga.album_type,
					ga.priority_order,
					ga.is_active, 
					gad.name,
					gad.description
				FROM '.$this->tableName.' ga	
					LEFT OUTER JOIN '.TABLE_GALLERY_ALBUMS_DESCRIPTION.' gad ON ga.id = gad.gallery_album_id
				WHERE
					ga.album_code = \''.$album_code.'\' AND 
					gad.language_id = \''.$this->languageId.'\'';		
		return database_query($sql, DATA_ONLY);
	}
	
	/**
	 * Draws album
	 * @param $album_code
	 * @param $draw
	 */
	public function DrawAlbum($album_code = '', $draw = true)
	{		
		$lang = Application::Get('lang');
		
		$output = '';

		$output .= '<script type="text/javascript" src="modules/unitegallery/js/unitegallery.min.js"></script>';
		$output .= '<link rel="stylesheet" href="modules/unitegallery/css/unite-gallery.css" type="text/css" />';
		$output .= '<script type="text/javascript" src="modules/unitegallery/themes/default/ug-theme-default.js"></script>';
		$output .= '<link rel="stylesheet" href="modules/unitegallery/themes/default/ug-theme-default.css" type="text/css" />';


		$icon_width  	    = ModulesSettings::Get('gallery', 'album_icon_width');
		$icon_height 	    = ModulesSettings::Get('gallery', 'album_icon_height');
		$albums_per_line    = ModulesSettings::Get('gallery', 'albums_per_line'); 
		$wrapper 		    = ModulesSettings::Get('gallery', 'wrapper');
		$image_gallery_type = ModulesSettings::Get('gallery', 'image_gallery_type');
		$image_gallery_rel  = ($image_gallery_type == 'unitegallery') ? 'unitegallery' : 'unitegallery';
		$video_gallery_type = ModulesSettings::Get('gallery', 'video_gallery_type');
		$video_gallery_rel  = ($video_gallery_type == 'unitegallery') ? 'unitegallery' : 'unitegallery';
		$show_items_count_in_album = ModulesSettings::Get('gallery', 'show_items_count_in_album');
		$show_items_numeration = ModulesSettings::Get('gallery', 'show_items_numeration_in_album');
		
		$album_code_parts = explode(':', $album_code);
		$album_code = isset($album_code_parts[0]) ? $album_code_parts[0] : '';
		$album_state = isset($album_code_parts[1]) ? $album_code_parts[1] : 'open';

		$sql = 'SELECT
					gad.name as album_name,
					gad.description,
					IF(gai.item_file_thumb != \'\', gai.item_file_thumb, gai.item_file) as mod_item_file_thumb,
					ga.album_code,
					ga.album_type,
					gai.item_file,
					gai.item_file_thumb,
					gaid.name as item_name,
					gaid.description as item_description,
					IF(gaid.description != \'\', gaid.description, gaid.name) as item_title
				FROM '.TABLE_GALLERY_ALBUMS.' ga
					INNER JOIN '.TABLE_GALLERY_ALBUM_ITEMS.' gai ON ga.album_code = gai.album_code
					LEFT OUTER JOIN '.TABLE_GALLERY_ALBUMS_DESCRIPTION.' gad ON ga.id = gad.gallery_album_id AND gad.language_id = \''.$lang.'\'
					LEFT OUTER JOIN '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.' gaid ON gai.id = gaid.gallery_album_item_id AND gaid.language_id = \''.$lang.'\'
				WHERE
					ga.album_code = \''.$album_code.'\' AND
					ga.is_active = 1 AND
					gai.item_file != \'\' AND
					gai.is_active = 1
				ORDER BY gai.priority_order ASC';

		$result_items = database_query($sql, DATA_AND_ROWS);

		if($result_items[1] > 0){
			if($album_state == 'open') $output .= draw_title_bar(_ALBUM.': '.$result_items[0][0]['album_name'], '', false);

//			$output .= '<table class="gallery-wrapper" border="0" cellspacing="5"><tr>'.(($wrapper == 'div') ? '<td>' : '');
			$output .= '<div id="gallery" style="display:none;">';
			for($i=0; $i<$result_items[1]; $i++){

				$output .= '<img src="'.APPHP_BASE.'images/gallery/'.$result_items[0][$i]['item_file_thumb'].'" data-image="'.APPHP_BASE.'images/gallery/'.$result_items[0][$i]['item_file'].'" alt="" title="" />';

//				$items_numeration = ($show_items_numeration == 'yes') ? ($i+1).'. ' : '';
//
//				$additional_params = (preg_match('/youtube/i', $result_items[0][$i]['item_file'])) ? '&amp;hd=1&amp;autoplay=1' : '';
//				$title_image = (($album_state == 'open') ? $result_items[0][$i]['item_title'] : _CLICK_TO_VIEW);
//				if($wrapper == 'table'){
//					if(($i != 0) && ($i % $albums_per_line == 0)) $output .= '</tr><tr>';
//					$output .= '<td valign="top" '.(($album_state == 'closed') ? 'align="center"' : '').'>';
//					if($result_items[0][$i]['album_type'] == 'video'){
//						if($album_state == 'open' || ($album_state == 'closed' && $i == 0)){
//							$output .= (($album_state == 'open') ? $result_items[0][$i]['item_name'] : $result_items[0][0]['album_name']).'<br>';
//							$output .= '<a href="'.$result_items[0][$i]['item_file'].$additional_params.'" rel="'.$video_gallery_rel.'[720 480](album'.$result_items[0][$i]['album_code'].')" title="'.$result_items[0][$i]['item_title'].'">';
//							$output .= '<img src="'.APPHP_BASE.(($result_items[0][$i]['item_file_thumb'] != '') ? $result_items[0][$i]['item_file_thumb'] : 'images/modules_icons/gallery/video.png').'" alt="video" title="'._CLICK_TO_VIEW.'" border="0" />';
//							$output .= '</a>';
//						}else{
//							$output .= '<a href="'.APPHP_BASE.$result_items[0][$i]['item_file'].$additional_params.'" rel="'.$video_gallery_rel.'[720 480](album'.$result_items[0][$i]['album_code'].')" title="'.$result_items[0][$i]['item_title'].'"></a>';
//						}
//					}else{
//						if($album_state == 'open' || ($album_state == 'closed' && $i == 0)){
//							$output .= '<a href="'.APPHP_BASE.'images/gallery/'.$result_items[0][$i]['item_file'].'" rel="'.$image_gallery_rel.'[720 480](album'.$result_items[0][$i]['album_code'].')" title="'.$title_image.'"><img src="'.APPHP_BASE.'images/gallery/'.$result_items[0][$i]['mod_item_file_thumb'].'" width="'.$icon_width.'" '.(($icon_height != 'px') ? 'height="'.$icon_height.'"' : '').' style="margin:5px 2px;" alt="icon" title="'.$title_image.'" border="" /></a>';
//							if($album_state == 'open') $output .= '<div style="padding-left:3px;width:'.$icon_width.';">'.$items_numeration.substr_by_word($result_items[0][$i]['item_name'], 40, true).'</div>';
//							else $output .= '<br>'.prepare_permanent_link('index.php?page=gallery&acode='.$result_items[0][$i]['album_code'], '<b>'.$result_items[0][$i]['album_name'].' '.(($show_items_count_in_album == 'yes') ? '('.$result_items[1].')' : '').'</b>', '', '', $result_items[0][$i]['description']);
//						}else{
//							$output .= '<a href="'.APPHP_BASE.'images/gallery/'.$result_items[0][$i]['item_file'].'" rel="'.$image_gallery_rel.'[720 480](album'.$result_items[0][$i]['album_code'].')" title="'.$title_image.'"></a>';
//						}
//					}
//					$output .= '</td>';
//				}else{
//					if($album_state == 'open' && ($i != 0) && ($i % $albums_per_line == 0)) $output .= '<br>';
//					$output .= '<div style="float:'.Application::Get('defined_alignment').';margin-right:5px;width:'.$icon_width.';">';
//					if($result_items[0][$i]['album_type'] == 'video'){
//						if($album_state == 'open' || ($album_state == 'closed' && $i == 0)){
//							$output .= (($album_state == 'open') ? $result_items[0][$i]['item_name'] : prepare_permanent_link('index.php?page=gallery&acode='.$result_items[0][$i]['album_code'], $result_items[0][0]['album_name'], '', '', $result_items[0][$i]['description'])).'<br>';
//							$output .= '<a href="'.$result_items[0][$i]['item_file'].$additional_params.'" rel="'.$video_gallery_rel.'[720 480](album'.$result_items[0][$i]['album_code'].')" title="'._CLICK_TO_VIEW.'"><img src="'.APPHP_BASE.(($result_items[0][$i]['item_file_thumb'] != '') ? $result_items[0][$i]['item_file_thumb'] : 'images/modules_icons/gallery/video.png').'" alt="icon" title="" border="0" /></a>';
//						}else{
//							$output .= '<a href="'.APPHP_BASE.$result_items[0][$i]['item_file'].$additional_params.'" rel="'.$video_gallery_rel.'[720 480](album'.$result_items[0][$i]['album_code'].')" title="'.$result_items[0][$i]['item_title'].'"></a>';
//						}
//					}else{
//						if($album_state == 'open' || ($album_state == 'closed' && $i == 0)){
//							$output .= '<a href="'.APPHP_BASE.'images/gallery/'.$result_items[0][$i]['item_file'].'" rel="'.$image_gallery_rel.'[720 480](album'.$result_items[0][$i]['album_code'].')" title="'.$title_image.'"><img src="'.APPHP_BASE.'images/gallery/'.$result_items[0][$i]['mod_item_file_thumb'].'" width="'.$icon_width.'" '.(($icon_height != 'px') ? 'height="'.$icon_height.'"' : '').' style="margin-bottom:5px;" alt="icon" title="'.$title_image.'" border="0" /></a><br />';
//							if($album_state == 'open') $output .= $items_numeration.substr_by_word($result_items[0][$i]['item_name'], 40, true);
//							else $output .= prepare_permanent_link('index.php?page=gallery&acode='.$result_items[0][$i]['album_code'], '<b>'.$result_items[0][$i]['album_name'].'</b> '.(($show_items_count_in_album == 'yes') ? '('.$result_items[1].')' : '')).'</a>';
//						}else{
//							$output .= '<a href="'.APPHP_BASE.'images/gallery/'.$result_items[0][$i]['item_file'].'" rel="'.$image_gallery_rel.'[720 480](album'.$result_items[0][$i]['album_code'].')" title="'.$title_image.'"></a>';
//						}
//					}
//					$output .= '</div>';
//				}
			}
//			$output .= (($wrapper == 'div') ? '</td>' : '').'</tr></table>';
			$output .= '</div>';
		}else{
			$output .= draw_title_bar(_ALBUM.': '.$album_code.' ('._EMPTY.')', '', false).'<br />';
		}

		$output .='<script type="text/javascript"> 
			
			jQuery(document).ready(function(){ 
				jQuery("#gallery").unitegallery(); 
			}); 
		
		</script>';
	
	    if($draw) echo $output;
		else return $output;
	}
	
	/**
	 * Draws gallery
	 * @param $draw
	 */
	public function DrawGallery($draw = true)
	{
		// For custom template			
		if(function_exists('Template_DrawGallery')){				
			$output	= Template_DrawGallery($draw);
		}else{
			$lang = Application::Get('lang');	
			$output = '';
			
			if(!Modules::IsModuleInstalled('gallery')) return $output;

			$wrapper = ModulesSettings::Get('gallery', 'wrapper');
			$icon_width = ModulesSettings::Get('gallery', 'album_icon_width');
			$icon_height = ModulesSettings::Get('gallery', 'album_icon_height');
			$albums_per_line = ModulesSettings::Get('gallery', 'albums_per_line');
			$image_gallery_type = ModulesSettings::Get('gallery', 'image_gallery_type');
			$show_items_count_in_album = ModulesSettings::Get('gallery', 'show_items_count_in_album');
			$image_gallery_rel = ($image_gallery_type == 'lytebox') ? 'lyteshow' : 'unitegallery';
	
			$sql = 'SELECT
						ga.id,
						ga.album_code,
						ga.album_type,
						gad.name,
						gad.description
					FROM '.TABLE_GALLERY_ALBUMS.' ga	
						LEFT OUTER JOIN '.TABLE_GALLERY_ALBUMS_DESCRIPTION.' gad ON ga.id = gad.gallery_album_id AND gad.language_id = \''.$lang.'\'
					WHERE
						ga.is_active = 1
					ORDER BY ga.priority_order ASC';
			$result = database_query($sql, DATA_AND_ROWS);
			if($result[1] > 0){
				
				$output .= '<table class="gallery_table" border="0" cellspacing="5"><tr>'.(($wrapper == 'div') ? '<td>' : '');
				for($i=0; $i<$result[1]; $i++){
					
					if($wrapper == 'table'){
						if(($i != 0) && ($i % $albums_per_line == 0)){
							$output .= '</tr><tr>';	
						}
						$output .= '<td valign="top" align="center">';	
					}else{
						$output .= '<div style="float:'.Application::Get('defined_alignment').';margin-right:5px;width:'.$icon_width.';">';
					}

					$sql = 'SELECT
								gai.item_file,
								gai.item_file_thumb,
								IF(gai.item_file_thumb != \'\', gai.item_file_thumb, gai.item_file) as mod_item_file_thumb, 
								gaid.name as item_name,
								gaid.description as item_description,
								IF(gaid.description != \'\', gaid.description, gaid.name) as item_title
							FROM '.TABLE_GALLERY_ALBUM_ITEMS.' gai
								INNER JOIN '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.' gaid ON gai.id = gaid.gallery_album_item_id AND gaid.language_id = \''.$lang.'\'
							WHERE
								gai.item_file != \'\' AND
								gai.album_code = \''.$result[0][$i]['album_code'].'\' AND
								gai.is_active = 1 
							ORDER BY gai.priority_order ASC';
					$result_items = database_query($sql, DATA_AND_ROWS);
					$gallery_icon = '';
					$gallery_pure_icon = '';
					$gallery_links = '';
					$video_gallery_thumb = 'images/modules_icons/gallery/video_album.png';
					for($j=0; $j<$result_items[1]; $j++){
						if($result[0][$i]['album_type'] == 'images'){
							if($gallery_icon == '' && $result_items[0][$j]['mod_item_file_thumb'] != ''){
								$gallery_icon .= '<a href="'.APPHP_BASE.'images/gallery/'.$result_items[0][$j]['item_file'].'" rel="'.$image_gallery_rel.'[720 480](galbum'.$result[0][$i]['album_code'].')" title="'.$result_items[0][$j]['item_title'].'"><img src="'.APPHP_BASE.'images/gallery/'.$result_items[0][$j]['mod_item_file_thumb'].'" width="'.$icon_width.'" '.(($icon_height != 'px') ? 'height="'.$icon_height.'"' : '').' alt="icon" title="'.$result_items[0][$j]['item_title'].'" border="0" /></a>';
								$gallery_pure_icon = '<img src="'.APPHP_BASE.'images/gallery/'.$result_items[0][$j]['mod_item_file_thumb'].'" width="'.$icon_width.'" '.(($icon_height != 'px') ? 'height="'.$icon_height.'"' : '').' alt="icon" title="'.$result_items[0][$j]['item_title'].'" border="0" />';
							}else{
								$gallery_links .= '<a href="'.APPHP_BASE.'images/gallery/'.$result_items[0][$j]['item_file'].'" rel="'.$image_gallery_rel.'[720 480](galbum'.$result[0][$i]['album_code'].')" title="'.$result_items[0][$j]['item_title'].'"></a>';
							}
						}elseif($result[0][$i]['album_type'] == 'video'){
							if($result_items[0][$j]['item_file_thumb'] != '' && @file_exists($video_gallery_thumb)) $video_gallery_thumb = $result_items[0][$j]['item_file_thumb'];
						}
					}

					// Draw album icons
					if($image_gallery_type == 'lytebox'){
						$output .= $gallery_icon;
						$output .= $gallery_links;

						if($result[0][$i]['album_type'] == 'video'){
							$output .= '<img src="'.APPHP_BASE.$video_gallery_thumb.'" width="'.$icon_width.'" '.(($icon_height != 'px') ? 'height="'.$icon_height.'"' : '').' alt="icon" title="" border="0" />';
							if($j == 0){
								$output .= '<br /><span title="'.$result[0][$i]['description'].'"><b>'.$result[0][$i]['name'].'</b>'.(($show_items_count_in_album == 'yes') ? ' ('.$result_items[1].')' : '').'</span>';
							}else{
								$output .= '<br />'.prepare_permanent_link('index.php?page=gallery&acode='.$result[0][$i]['album_code'], '<span title="'.$result[0][$i]['description'].'"><b>'.$result[0][$i]['name'].'</b>'.(($show_items_count_in_album == 'yes') ? ' ('.$result_items[1].')' : '').'</span>');
							}
						}else{
							if($j == 0){
								$output .= '<img src="'.APPHP_BASE.'images/gallery/no_image.png" width="'.$icon_width.'" '.(($icon_height != 'px') ? 'height="'.$icon_height.'"' : '').' alt="icon" title="" border="0" />';
								$output .= '<br /><span title="'.$result[0][$i]['description'].'"><b>'.$result[0][$i]['name'].'</b>'.(($show_items_count_in_album == 'yes') ? '('.$result_items[1].')' : '').'</span>';
							}else{
								$output .= '<br />'.prepare_permanent_link('index.php?page=gallery&acode='.$result[0][$i]['album_code'], '<span title="'.$result[0][$i]['description'].'"><b>'.$result[0][$i]['name'].'</b>'.(($show_items_count_in_album == 'yes') ? ' ('.$result_items[1].')' : '').'</span>');
							}
						}
					}else{
						$output .= '<a href="index.php?page=gallery&acode='.$result[0][$i]['album_code'].'">';
						if($result[0][$i]['album_type'] == 'video'){
							$output .= '<img src="'.APPHP_BASE.$video_gallery_thumb.'" width="'.$icon_width.'" '.(($icon_height != 'px') ? 'height="'.$icon_height.'"' : '').' alt="icon" title="" border="0" />';
						}else{
							$output .= $gallery_pure_icon;
						}
						$output .= '<br><span title="'.$result[0][$i]['description'].'"><b>'.$result[0][$i]['name'].'</b>'.(($show_items_count_in_album == 'yes') ? ' ('.$result_items[1].')' : '').'</span>';
						$output .= '</a>';
					}

					if($wrapper == 'table'){
						$output .= '</td>';
					}else{
						$output .= '</div>';
					}
					
				}				
				$output .= (($wrapper == 'div') ? '</td>' : '').'</tr></table>';
			}
		}

	    if($draw) echo $output;
		else return $output;
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
			if($val['name'] == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['name']) > 125){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 125, $this->error);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['description']) > 255){
				$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 255, $this->error);
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
	 * After-Insertion - add album descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		$sql = 'INSERT INTO '.TABLE_GALLERY_ALBUMS_DESCRIPTION.'(id, gallery_album_id, language_id, name, description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['description'])).'\')';
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
	 * After-Updating - update album descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_GALLERY_ALBUMS_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						description = \''.encode_text(prepare_input($val['description'])).'\'
					WHERE gallery_album_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	

	/**
	 * Before-Deleting - delete album descriptions from description table
	 */
	public function BeforeDeleteRecord()
	{
		$sql = 'SELECT id, album_code, album_type, priority_order, is_active  
				FROM '.TABLE_GALLERY_ALBUMS.'
				WHERE id = '.$this->curRecordId;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);		
		if($result[1] > 0){
			$this->curAlbumCode = $result[0]['album_code'];
			$this->curAlbumType = $result[0]['album_type'];
		}
		return true;
	}

	/**
	 * After-Deleting - delete album descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.TABLE_GALLERY_ALBUMS_DESCRIPTION.' WHERE gallery_album_id = '.(int)$this->curRecordId;
		database_void_query($sql);
		
		if($this->curAlbumCode != ''){
			$sql = 'SELECT id, album_code, item_file, item_file_thumb, priority_order, is_active 
					FROM '.TABLE_GALLERY_ALBUM_ITEMS.'
					WHERE album_code = \''.$this->curAlbumCode.'\'';
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] > 0){
				for($i=0; $i < $result[1]; $i++){
					if($this->curAlbumType == 'images'){					
						unlink('images/gallery/'.$result[0][$i]['item_file']);
						unlink('images/gallery/'.$result[0][$i]['item_file_thumb']);
					}
					$sql = 'DELETE FROM '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.' WHERE gallery_album_item_id = '.(int)$result[0][$i]['id'];
					database_void_query($sql);						
				}
				
				$sql = 'DELETE FROM '.TABLE_GALLERY_ALBUM_ITEMS.' WHERE album_code = \''.$this->curAlbumCode.'\'';
				database_void_query($sql);
				
				return true;	
			}			
		}
		return false;			
	}
	
	/**
	 * Include style and javascript files
	 */
	public static function SetLibraries()
	{
		if(!Modules::IsModuleInstalled('gallery')) return false;
		$output = '';
		$nl = "\n";
		
		$image_gallery_type = ModulesSettings::Get('gallery', 'image_gallery_type'); 
		$video_gallery_type = ModulesSettings::Get('gallery', 'video_gallery_type'); 
		$output = '';

		if($image_gallery_type == 'lytebox'){
			$output .= '<!-- LyteBox v3.22 Author: Markus F. Hay Website: http://www.dolem.com/lytebox -->'.$nl;
			$output .= '<link rel="stylesheet" href="'.APPHP_BASE.'modules/lytebox/css/lytebox.css" type="text/css" media="screen" />'.$nl;
            if(Application::Get('lang_dir') == 'rtl') $output .= '<link rel="stylesheet" href="'.APPHP_BASE.'modules/lytebox/css/lytebox.rtl.css" type="text/css" media="screen" />'.$nl;
			$output .= '<script type="text/javascript" src="'.APPHP_BASE.'modules/lytebox/js/lytebox.js"></script>'.$nl;
			Application::Set('js_included', 'lytebox');
		}
		
		if($image_gallery_type == 'unitegallery' || $video_gallery_type == 'unitegallery'){
			$output .= '<script type="text/javascript" src="'.APPHP_BASE.'modules/unitegallery/js/unitegallery.min.js"></script>'.$nl;
			$output .= '<link rel="stylesheet" href="'.APPHP_BASE.'modules/unitegallery/css/unite-gallery.css" type="text/css" />'.$nl;
			$output .= '<script type="text/javascript" src="'.APPHP_BASE.'modules/unitegallery/themes/tiles/ug-theme-tiles.js"></script>'.$nl;
			Application::Set('js_included', 'unitegallery');
		}

		return $output;
	}

}
