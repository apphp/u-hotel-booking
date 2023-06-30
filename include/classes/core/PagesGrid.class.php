<?php

/**
 *	PagesGrid
 *  -------------- 
 *  Description : encapsulates vocabulary properties and methods
 *	Written by  : ApPHP
 *	Version     : 1.0.2
 *  Updated	    : 16.09.2012
 *  Usage       : Core Class
 *	Differences : no
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct			  
 *	__destruct
 *
 *  1.0.2
 *      -
 *      -
 *      -
 *      -
 *      -
 *  1.0.1
 *      - added ModulesSettings::Get()
 *      - added _UNDEFINED
 *      - added menu_link for system pages
 *      - updated with _HIDDEN
 *      - <font> replaced with <span>
 *	
 **/


class PagesGrid extends MicroGrid {
	
	protected $debug = false;
	
	//==========================================================================
    // Class Constructor
	//	@param $type
	//	@param $actions
	//==========================================================================
	function __construct($type = '', $actions = array())
	{		
		parent::__construct();
		
		$this->params = array();		
		///if(isset($_POST['parameter1']))   $this->params['parameter1'] = $_POST['parameter1'];
		///if(isset($_POST['parameter2']))   $this->params['parameter2'] = $_POST['parameter2'];
		///if(isset($_POST['parameter3']))   $this->params['parameter3'] = $_POST['parameter3'];
		// for checkboxes 
		///if(isset($_POST['parameter4']))   $this->params['parameter4'] = $_POST['parameter4']; else $this->params['parameter4'] = '0';
		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_PAGES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=pages'.(($type != '') ? '&type='.$type: '');
		$this->actions      = array('add'=>false, 'edit'=>false, 'details'=>false, 'delete'=>false);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = true;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = 'WHERE
									'.$this->tableName.'.is_system_page = '.(($type == 'system') ? '1' : '0').' AND 
									'.$this->tableName.'.is_home = 0 AND
									'.$this->tableName.'.is_removed = 0 AND
									'.$this->tableName.'.language_id = \''.$this->languageId.'\''; 
		$this->ORDER_CLAUSE = 'ORDER BY priority_order ASC';
		
		$this->isAlterColorsAllowed = true;
		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = ($type == 'system') ? false : true;
		// prepare menus array
		$total_menus = Menu::GetAll(' menu_order ASC', TABLE_MENUS, '', $this->languageId);
		$arr_menus = array();
		foreach($total_menus[0] as $key => $val){
			$arr_menus[$val['id']] = $val['menu_name'].(($val['menu_placement'] == 'hidden') ? ' ('._HIDDEN.')' : '');
		}		
		// define filtering fields
		$this->arrFilteringFields = array(
			_MENU_WORD => array('table'=>TABLE_MENUS, 'field'=>'id', 'type'=>'dropdownlist', 'source'=>$arr_menus, 'sign'=>'=', 'width'=>'150px'),
		);

		// prepare languages array		
		$total_languages = Languages::GetAllActive();
		$arr_languages      = array();
		foreach($total_languages[0] as $key => $val){
			$arr_languages[$val['abbreviation']] = $val['lang_name'];
		}
		
		$comments_allow	= (Modules::IsModuleInstalled('comments')) ? ModulesSettings::Get('comments', 'comments_allow') : 'no';

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.language_id,
									'.$this->tableName.'.content_type,
									'.$this->tableName.'.link_url,
									'.$this->tableName.'.link_target,
									'.$this->tableName.'.page_key,
									IF('.$this->tableName.'.page_title != "", '.$this->tableName.'.page_title, "- '._UNDEFINED.' -") as page_title,
									'.$this->tableName.'.page_text,
									'.$this->tableName.'.menu_id,
									IF('.$this->tableName.'.menu_link != "", '.$this->tableName.'.menu_link, "- '._UNDEFINED.' -") as menu_link,
									'.$this->tableName.'.comments_allowed,
									'.$this->tableName.'.is_home,
									'.$this->tableName.'.priority_order,
									IF('.$this->tableName.'.access_level = "public", "'._PUBLIC.'", "'._REGISTERED.'") my_access_level,
									CASE
										WHEN '.$this->tableName.'.is_published = 1 THEN
											IF(
												(finish_publishing IS NULL OR finish_publishing >= \''.date('Y-m-d').'\'),
												"<img src=\"images/published_g.gif\" alt=\"published\" />",
												"<img src=\"images/expired.gif\" alt=\"expired\" />"
											  ) 		
										ELSE "<img src=\"images/published_x.gif\" alt=\"published\" />"
									END as is_published,
									IF('.TABLE_MENUS.'.menu_name != "", '.TABLE_MENUS.'.menu_name, "'._NOT_AVAILABLE.'") as menu_name,
									CASE
										WHEN '.$this->tableName.'.comments_allowed = 1 THEN
											CONCAT("<a href=\"index.php?admin=mod_comments_management&pid=",
											'.$this->tableName.'.'.$this->primaryKey.', "\">", (SELECT COUNT(*) FROM '.TABLE_COMMENTS.' c WHERE c.article_id = '.$this->tableName.'.'.$this->primaryKey.'),											
											(SELECT IF(COUNT(*) > 0, CONCAT("(",COUNT(*),")"), "") FROM '.TABLE_COMMENTS.' c WHERE c.is_published = 0 AND c.article_id = '.$this->tableName.'.'.$this->primaryKey.'),
											"</a>")
										ELSE
											"<span class=gray>'._NOT_ALLOWED.'</span>"
									END as comments_count,
									CONCAT(
										" <a href=\"index.php?page=pages'.(($type == 'system') ? '&type='.$type: '').'&pid=", '.$this->tableName.'.'.$this->primaryKey.', "&mg_language_id='.$this->languageId.'\">'._VIEW_WORD.'</a>
										'.(($actions['edit']) ? ' | <a href=\"index.php?admin=pages_edit'.(($type != '') ? '&type='.$type: '').'&pid=", '.$this->tableName.'.'.$this->primaryKey.', "\">'._EDIT_WORD.'</a>' : '').'
										'.(($actions['delete'] && $type != 'system') ? ' | <a href=\"javascript:confirmRemoving(\'", '.$this->tableName.'.'.$this->primaryKey.', "\')\">'._REMOVE.'</a>' : '').'
										") as action_links
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_MENUS.' ON '.$this->tableName.'.menu_id='.TABLE_MENUS.'.id';
		// define view mode fields
		$this->arrViewModeFields = array();
		$this->arrViewModeFields['menu_link'] = array('title'=>_MENU_LINK, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'40');			
		if($type == 'system'){
			$this->arrViewModeFields['page_title'] = array('title'=>_PAGE_HEADER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'40');
		}
		$this->arrViewModeFields['menu_name'] 	   = array('title'=>_MENU_WORD, 'type'=>'label', 'align'=>'center', 'width'=>'', 'visible'=>(($type == 'system') ? false : true));
		$this->arrViewModeFields['is_published']   = array('title'=>_PUBLISHED, 'type'=>'label', 'align'=>'center', 'width'=>'85px');
		$this->arrViewModeFields['my_access_level'] = array('title'=>_ACCESS, 'type'=>'label', 'align'=>'center', 'width'=>'75px');
		$this->arrViewModeFields['priority_order'] = array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'65px', 'visible'=>'true', 'movable'=>true);
		$this->arrViewModeFields['comments_count'] = array('title'=>_COMMENTS, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'visible'=>(($comments_allow == 'yes') ? true : false));
		$this->arrViewModeFields['id']             = array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'50px');
		$this->arrViewModeFields['action_links']   = array('title'=>_ACTIONS, 'type'=>'label', 'align'=>'center', 'sortable'=>false, 'nowrap'=>'nowrap', 'width'=>(($type == 'system') ? '90px' : '130px'));

		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(

		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.parameter1,
								'.$this->tableName.'.parameter2,
								'.$this->tableName.'.parameter3
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
		
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(

		);
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }
	
}
