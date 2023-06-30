<?php

/**
 * 	Class GalleryAlbumItems
 *  -------------- 
 *  Description : encapsulates gallery album items properties
 *	Written by  : ApPHP
 *	Version     : 1.0.5
 *  Updated	    : 11.02.2014
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct                                     ValidateTranslationFields
 *	__destruct
 *	BeforeInsertRecord
 *	AfterInsertRecord
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDeleteRecord
 *
 *	1.0.5
 *	    - added 'maxlength' for textarea
 *	    - changed help tooltips for some fields
 *	    -
 *	    -
 *	    -
 *	1.0.4
 *	    - redone $help_tooltip
 *	    - item_text changed into name + description 
 *	    - added focus on translate fields
 *	    - added prepare_input() for translate fields
 *	    - changed CASE SQLs wit 'enum' types
 *	1.0.3
 *		- added http:// for video links 
 *		- added automaticall addition of http:// for video links 
 *		- added thumbnail for video files
 *		- added tooltip for video file field
 *		- improved working with translation fields
 *	1.0.2
 *		- changed in Details Mode thumb with normal image
 *		- added 'movable'=>true for priority_order
 *		- added possibility to add/edit language descriptions on one page
 *		- added image/video switch
 *		- renamed table from gallery_images into gallery_album_items
 *
 *	
 **/


class GalleryAlbumItems extends MicroGrid {
	
	protected $debug = false;

    //---------------------------	
	private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		global $objLogin;

		$album  = MicroGrid::GetParameter('album', false);
		$objAlbums = new GalleryAlbums();
		$album_info = $objAlbums->GetAlbumInfo($album);	

		$this->params = array();		
		if(isset($_POST['album_code'])) $this->params['album_code'] = prepare_input($_POST['album_code']);
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))  $this->params['is_active'] = prepare_input($_POST['is_active']); else $this->params['is_active'] = '0';
		if($album_info[0]['album_type'] == 'video'){
			if(isset($_POST['item_file'])){
				$this->params['item_file'] = prepare_input($_POST['item_file']);
				if($this->params['item_file'] != '' && !preg_match('/^http:\/\/i/', $this->params['item_file'])) $this->params['item_file'] = 'http://'.$this->params['item_file'];
			}
			if(isset($_POST['item_file_thumb'])) $this->params['item_file_thumb'] = prepare_input($_POST['item_file_thumb'], false, 'medium');
		}
		///$this->params['language_id'] 	= MicroGrid::GetParameter('language_id');

		$icon_width  = (ModulesSettings::Get('gallery', 'album_icon_width') != '') ? ModulesSettings::Get('gallery', 'album_icon_width') : '120px';
		$icon_height = (ModulesSettings::Get('gallery', 'album_icon_height') != '') ? ModulesSettings::Get('gallery', 'album_icon_height') : '90px';
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_GALLERY_ALBUM_ITEMS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_gallery_upload_items&album='.$album;
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = 'WHERE album_code = \''.$album.'\'';		
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
			TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION,
			'gallery_album_item_id',
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$video_link_tooltip = '<br><span class="gray">e.g. http://www.youtube.com/watch?v=5VIV8nt2KkU - or - http://localhost/{your site}/my_video.wmv</span>';
		$thumbnail_tooltip = '<br><span class="gray">e.g. http://www.domain.com/icon.png - or - images/icon.png</span>';

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									gi.'.$this->primaryKey.',
									gi.album_code,
									gi.item_file,
									gi.item_file_thumb,
									gi.priority_order,
									gi.is_active,
									gid.name,
									gid.description
								FROM ('.$this->tableName.' gi	
									LEFT OUTER JOIN '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.' gid ON gi.id = gid.gallery_album_item_id AND gid.language_id = \''.$this->languageId.'\')';		
		// define view mode fields
		if($album_info[0]['album_type'] == 'video'){
			$this->arrViewModeFields['name']     = array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'20%', 'maxlength'=>'30');
			$this->arrViewModeFields['item_file'] = array('title'=>_VIDEO, 'type'=>'label', 'align'=>'left', 'width'=>'40%');
		}else{
			$this->arrViewModeFields['item_file_thumb'] = array('title'=>_IMAGE, 'type'=>'image', 'align'=>'left', 'width'=>'60px', 'sortable'=>false, 'nowrap'=>'', 'visible'=>'', 'image_width'=>'50px', 'image_height'=>'30px', 'target'=>'images/gallery/', 'no_image'=>'no_image.png');
			$this->arrViewModeFields['name']     = array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'20%', 'maxlength'=>'30');			
		}
		$this->arrViewModeFields['description']    = array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'60');
		$this->arrViewModeFields['priority_order'] = array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'10%', 'movable'=>true);
		$this->arrViewModeFields['is_active']      = array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'10%', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active);

		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'separator_general'  =>array(
				'separator_info' => array('legend'=>_GENERAL),				
				'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0'),
				'album_code'     => array('title'=>'', 'type'=>'hidden',   'required'=>true, 'readonly'=>false, 'default'=>$album),
			)
		);
		if($album_info[0]['album_type'] == 'video'){
			$this->arrAddModeFields['separator_general']['item_file']       = array('title'=>_VIDEO, 'type'=>'textbox',  'width'=>'370px', 'maxlength'=>'255', 'required'=>false, 'readonly'=>false, 'validation_type'=>'', 'post_html'=>$video_link_tooltip);
			$this->arrAddModeFields['separator_general']['item_file_thumb'] = array('title'=>_THUMBNAIL, 'type'=>'textbox',  'width'=>'370px', 'maxlength'=>'255', 'required'=>false, 'readonly'=>false, 'validation_type'=>'', 'post_html'=>$thumbnail_tooltip);
		}else{
			$this->arrAddModeFields['separator_general']['item_file'] = array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'images/gallery/', 'thumbnail_create'=>true, 'thumbnail_field'=>'item_file_thumb', 'thumbnail_width'=>$icon_width, 'thumbnail_height'=>$icon_height, 'file_maxsize'=>'900k');
		}

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->primaryKey.',
								album_code,
								item_file,
								item_file_thumb,
								priority_order,
								'.$sql_translation_description.'
								is_active
							FROM '.$this->tableName.'
							WHERE '.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_general'  =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'priority_order' => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'true_value'=>'1', 'false_value'=>'0'),
				'album_code'     => array('title'=>'', 'type'=>'hidden',   'required'=>true, 'readonly'=>false, 'default'=>$album),
			)
		);
		if($album_info[0]['album_type'] == 'video'){
			$this->arrEditModeFields['separator_general']['item_file'] = array('title'=>_VIDEO, 'type'=>'textbox',  'width'=>'370px', 'maxlength'=>'255', 'required'=>false, 'readonly'=>false, 'validation_type'=>'', 'post_html'=>$video_link_tooltip);
			$this->arrEditModeFields['separator_general']['item_file_thumb'] = array('title'=>_THUMBNAIL, 'type'=>'textbox',  'width'=>'370px', 'maxlength'=>'255', 'required'=>false, 'readonly'=>false, 'validation_type'=>'', 'post_html'=>$thumbnail_tooltip);
		}else{
			$this->arrEditModeFields['separator_general']['item_file'] = array('title'=>_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'images/gallery/', 'thumbnail_create'=>true, 'thumbnail_field'=>'item_file_thumb', 'thumbnail_width'=>$icon_width, 'thumbnail_height'=>$icon_height, 'file_maxsize'=>'900k');
		}

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_general'  =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
				'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
			)
		);
		if($album_info[0]['album_type'] == 'video'){
			$this->arrDetailsModeFields['separator_general']['item_file'] = array('title'=>_VIDEO, 'type'=>'object', 'width'=>'240px', 'height'=>'200px');
			$this->arrDetailsModeFields['separator_general']['item_file_thumb'] = array('title'=>_THUMBNAIL, 'type'=>'label');
		}else{
			$this->arrDetailsModeFields['separator_general']['item_file'] = array('title'=>_IMAGE, 'type'=>'image', 'target'=>'images/gallery/', 'no_image'=>'no_image.png');
		}

		///////////////////////////////////////////////////////////////////////////////
		// 3. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>false, 'maxlength'=>'125', 'readonly'=>false),
				  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'70px', 'required'=>false, 'maxlength'=>'255', 'validation_maxlength'=>'255', 'readonly'=>false)
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
			if(strlen($val['name']) > 125){
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
	 * After-Insertion - add banner descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		$sql = 'INSERT INTO '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.'(id, gallery_album_item_id, language_id, name, description) VALUES ';
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
	 * After-Updating - update album item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						description = \''.encode_text(prepare_input($val['description'])).'\'
					WHERE gallery_album_item_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	

	/**
	 * After-Deleting - delete album altem descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION.' WHERE gallery_album_item_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}
	
}
