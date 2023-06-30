<?php

/***
 *	Pages class 
 *  -------------- 
 *  Description : encapsulates pages properties
 *	Written by  : ApPHP
 *	Version     : 1.0.4
 *  Updated	    : 13.09.2016
 *  Usage       : Core Class
 *
 *	PUBLIC:				  	STATIC:				 		PRIVATE:
 * 	------------------	  	---------------     		---------------
 *	__construct			  	GetAll				   		GetMenuIdByLang
 *	__destruct            	DrawPageAccessSelectBox 	GetDataEncoded
 *	DrawTitle               UpdateMetaTags				PrepareModulesContent
 *	DrawText                GetPageId
 *	GetParameter
 *	GetTitle
 *	GetMenuId
 *	GetMenuLink
 *	GetKey
 *	GetId
 *	GetText
 *	PageUpdate
 *	PageCreate
 *	PageDelete
 *	MoveToTrash
 *	PageRestore
 *	CheckAccessRights
 *	CacheAllowed
 *	GetMaxOrder
 *	
 *  1.0.4
 *      - added check for GLOBAL_CAMPAIGNS
 *      -
 *      -
 *      -
 *      -
 *  1.0.3
 *      - added testimonials for
 *      - fixed issues with $output .= 
 *      - mysq l_real_escape_string() replaced with encode_text()
 *      - added {module:hotels} for Hotels
 *      - added {module:doctors} for MedicalAppointment
 *  1.0.2
 *      - Hotel:: replaced with Hotels::
 *      - added Campaigns::DrawCampaignBanner(); for
 *      - added Campaigns::DrawCampaignBanner('standard'); for HotelSite
 *      - changes to
 *      - added return value for DrawText
 *  1.0.1
 *  	- converted to Core class
 *  	- added code about_us for MedicalAppointment
 *  	- added difference for
 *  	- fixed bug in creating Home Page
 *  	- added $this->page_id = $last_insert_id; in PageCreate
 *  	
 **/

class Pages {

	protected $page_key;
	protected $page_id;
	protected $page;
	protected $languageId;
	
	public $error;
	public $focusOnField;
	
	//==========================================================================
    // Class Constructor
	//		@param $page_id
	//		@param $is_active
	// 		@param $lang_id	
	//==========================================================================
	function __construct($page_id = '', $is_active = false, $lang_id = '')
	{		
		$this->focusOnField = 'page_title';
		$lang = ($lang_id != '') ? $lang_id : Application::Get('lang');

		if($page_id == 'home' || $page_id == 'public_home'){
			$this->languageId = ($lang != '') ? $lang : Languages::GetDefaultLang();
		}else{
			$this->languageId = (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? prepare_input($_REQUEST['language_id']) : Languages::GetDefaultLang();
		}
		
		if($page_id != ''){
			if($page_id == 'home' || $page_id == 'public_home'){
				$sql_home = 'SELECT
							'.TABLE_PAGES.'.*,
							'.TABLE_LANGUAGES.'.lang_name as language_name
						FROM '.TABLE_PAGES.'
							LEFT OUTER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_PAGES.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
						WHERE
							'.TABLE_PAGES.'.is_home = 1 AND
							'.TABLE_PAGES.'.language_id = \''.$this->languageId.'\'';
				$this->page = database_query($sql_home, DATA_ONLY, FIRST_ROW_ONLY);
				if(empty($this->page)){
					$page_code = get_random_string(10);					
					// create Home Page
					$sql = 'INSERT INTO '.TABLE_PAGES.'(
							id, page_code, language_id, content_type,
							link_url, link_target, page_key, page_title, page_text,
							menu_id, menu_link, tag_title, tag_keywords, tag_description, 
							comments_allowed, show_in_search, date_created, date_updated, finish_publishing,
							is_home, is_removed, is_published, is_system_page, system_page,
							status_changed, access_level, priority_order
						)VALUES(
							NULL, \''.$page_code.'\', \''.$this->languageId.'\', \'article\',
							\'\', \'\', \'\', \'Home\', \'\',
							0, \'\', \'\', \'\', \'\', 
							0, 1, NULL, NULL, NULL,
							1, 0, 0, 0, \'\',
							NULL, \'public\', 0
						)';
					if(database_void_query($sql)){
						$this->page = database_query($sql_home, DATA_ONLY, FIRST_ROW_ONLY);
					    // update home pages code
						$sql_update = 'UPDATE '.TABLE_PAGES.' SET page_code = \''.$page_code.'\' WHERE is_home = 1';
						database_void_query($sql_update);							
					}
				}
			}elseif($page_id != '' && is_alpha_dashed($page_id)){
				$sql_home = 'SELECT
							'.TABLE_PAGES.'.*,
							'.TABLE_LANGUAGES.'.lang_name as language_name
						FROM '.TABLE_PAGES.'
							LEFT OUTER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_PAGES.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
						WHERE
							'.TABLE_PAGES.'.is_system_page = 1 AND
							'.TABLE_LANGUAGES.'.abbreviation = \''.$lang.'\' AND
							'.TABLE_PAGES.'.system_page = \''.$page_id.'\' AND
							'.TABLE_PAGES.'.is_published = 1 ';
				$this->page = database_query($sql_home, DATA_ONLY, FIRST_ROW_ONLY);
			}else{
				$sql = 'SELECT
							'.TABLE_PAGES.'.*,
							'.TABLE_LANGUAGES.'.lang_name as language_name,
							'.TABLE_MENUS.'.access_level as menu_access_level
						FROM '.TABLE_PAGES.'
							LEFT OUTER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_PAGES.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
							LEFT OUTER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id = '.TABLE_MENUS.'.id
						WHERE
							'.TABLE_PAGES.'.id = \''.(int)$page_id.'\'';
						if($is_active){
							$sql .= ' AND '.TABLE_PAGES.'.is_removed = 0 ';
							$sql .= ' AND '.TABLE_PAGES.'.is_published = 1 ';
						}
				$this->page = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			}
			if(empty($this->page)){
				$this->page_id = '';
			}else{
				$this->page_id = $page_id;				
			}			
		}else{
			$this->page_id = $page_id;
			$this->page['id'] = '';
			$this->page['page_code'] = '';
			$this->page['language_id'] = '';
			$this->page['content_type'] = 'article';
			$this->page['link_url'] = '';
			$this->page['link_target'] = '';
			$this->page['page_key'] = '';
			$this->page['page_title'] = '';
			$this->page['page_text'] = '';
			$this->page['menu_id'] = '0';
			$this->page['menu_link'] = '';
			$this->page['tag_title'] = '';
			$this->page['tag_keywords'] = '';
			$this->page['tag_description'] = '';
			$this->page['comments_allowed'] = '0';
			$this->page['date_created'] = '';
			$this->page['date_updated'] = '';
			$this->page['finish_publishing'] = '';
			$this->page['show_in_search'] = '1';
			$this->page['is_home'] = '0';
			$this->page['is_removed'] = '0';
			$this->page['is_published'] = '0';
			$this->page['is_system_page'] = '0';
			$this->page['language_name'] = '';
			$this->page['language_id'] = '';			
			$this->page['access_level'] = '0';
			$this->page['priority_order'] = '0';
		}
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Draw page title
	 * @param $additional_text
	 */
	public function DrawTitle($additional_text = '', $draw = true)
	{
		$output = '';
		if(isset($this->page['page_title'])){
			$page_title = decode_text($this->page['page_title']);
			$output = draw_title_bar($page_title, $additional_text, false);
		}
		if($draw) echo $output; 
		else return $output;				
	}

	/**
	 * Draw page text
	 * @param $draw
	 */
	public function DrawText($draw = true)
	{
		global $objLogin;
		
		$module_page = false;
		$output = '';

		if(!count($this->page)) return false;
		
		// Don't show this page if it was expired
		if(!$objLogin->IsLoggedInAsAdmin() && !is_empty_date($this->page['finish_publishing']) && date('Y-m-d') > $this->page['finish_publishing']){
			draw_important_message(_PAGE_EXPIRED);
			return false;
		}
		
		if($this->page['content_type'] == 'article' && isset($this->page['page_text'])){
			$page_text = decode_text($this->page['page_text'], false);
			
			$output .= '<div class="pages_contents">';
			$this->PrepareModulesContent($page_text);

			if($this->page['is_home']){
				$output .= Campaigns::DrawCampaignBanner('standard', false);
				$output .= defined('GLOBAL_CAMPAIGNS') && GLOBAL_CAMPAIGNS == 'enabled' ? Campaigns::DrawCampaignBanner('global', false) : '';
			}

			$output .= $page_text;

			// Draw comments form
			if(!$this->page['is_home'] && !$module_page){
				if(Modules::IsModuleInstalled('comments')){
					if(ModulesSettings::Get('comments', 'comments_allow') == 'yes' && $this->page['comments_allowed']){
						$objComments = new Comments();
						$output .= $objComments->DrawArticleComments($this->page['id'], false);
					}
				}
			}
			$output .= '</div>';
			
		}elseif($this->page['content_type'] == 'link' && isset($this->page['link_url'])){
			$link_url = decode_text($this->page['link_url']);
			$output .= '<div class="pages_contents">';
			$output .= '<a href="'.$link_url.'">'.$link_url.'</a>';
			$output .= '</div>';			
		}
		
		if($draw) echo $output; 
		else return $output;		
	}
	
	/**
	 * Return parameter
	 * @param $param
	 */
	public function GetParameter($param = '')
	{
		$meta_array = array('tag_title', 'tag_keywords', 'tag_description');
		if(isset($this->page[$param])){
			if(in_array($param, $meta_array)){
				return htmlspecialchars($this->page[$param]);	
			}else{
				return $this->page[$param];	
			}			
		}else{
			return '';
		}
	}

	/**
	 * Return page title
	 */
	public function GetTitle()
	{
		if(isset($this->page['page_title'])){
			$page_title = decode_text($this->page['page_title']);
			return $page_title;
		}else return '';
	}	

	/**
	 * Returns menu ID
	 */
	public function GetMenuId()
	{
		if(isset($this->page['menu_id'])) return $this->page['menu_id'];
		return '';
	}

	/**
	 * Returns menu link
	 */
	public function GetMenuLink()
	{
		if(isset($this->page['menu_link'])){
			$menu_link = decode_text($this->page['menu_link']);
			return $menu_link;
		}else return '';
	}
	
	/**
	 * Returns page key
	 */
	public function GetKey()
	{
		return $this->page_key;
	}

	/**
	 * Returns page ID
	 */
	public function GetId()
	{
		return $this->page_id;
	}
	
	/**
	 * Returns page text
	 * @param bool $apply_modules
	 * @returm string
	 */
	public function GetText($apply_modules = false)
	{
		$page_text = $this->page['page_text'];
		if(!empty($page_text)){
			if($apply_modules){
				$this->PrepareModulesContent($page_text);
			}
			return decode_text($page_text, false);
		}else{
			return '';
		}
	}
	
	/**
	 * Return maximal order of pages
	 */
	public function GetMaxOrder($language_id = '')
	{
		$sql = 'SELECT MAX(priority_order) as max_order FROM '.TABLE_PAGES.' WHERE is_removed = 0 AND language_id = \''.$language_id.'\'';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		return (isset($result['max_order']) && $result['max_order'] < 9999) ? $result['max_order'] : '9998';
	}
		
	/**
	 * Return record set of current page
	 * @param $lang_id
	 * @param $is_removed
	 */
	public static function GetAll($lang_id = '', $is_removed = '')
	{		
		$where_clause = '';
		$order_clause = 'ORDER BY '.TABLE_MENUS.'.menu_order ASC';

		if($lang_id != '') $where_clause .= 'AND '.TABLE_PAGES.'.language_id = \''.$lang_id.'\' ';
		if($is_removed == 'removed'){
			$where_clause .= 'AND '.TABLE_PAGES.'.is_removed = 1 ';
			$order_clause = ' ORDER BY '.TABLE_PAGES.'.status_changed DESC, '.TABLE_MENUS.'.menu_order ASC';
		}
		else $where_clause .= 'AND '.TABLE_PAGES.'.is_removed != 1 ';

		$sql = 'SELECT
					'.TABLE_PAGES.'.*,
					'.TABLE_MENUS.'.menu_name,
					'.TABLE_LANGUAGES.'.lang_name as language_name
				FROM '.TABLE_PAGES.'  
					LEFT OUTER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id='.TABLE_MENUS.'.id
					LEFT OUTER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_PAGES.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
				WHERE
					'.TABLE_PAGES.'.is_home != 1					
					'.$where_clause.'
					'.$order_clause;				
		return database_query($sql, DATA_AND_ROWS);
	}	
	
	/**
	 * Updates current page
	 * @param $params - set of fields
	 */
	public function PageUpdate($params = array())
	{
		if(isset($this->page['id'])){
			if(strtolower(SITE_MODE) == 'demo'){
				$this->error = _OPERATION_BLOCKED;
				return false;
			}else{				
			
				// Get input parameters
				if(isset($params['id']))	         $this->page['id'] = $params['id'];
				if(isset($params['content_type']))	 $this->page['content_type'] = $params['content_type'];
				if(isset($params['link_url']))	 	 $this->page['link_url'] = trim($params['link_url']);
				if(isset($params['link_target']))	 $this->page['link_target'] = $params['link_target'];
				if(isset($params['system_page']))	 $this->page['system_page'] = trim($params['system_page']);
				if(isset($params['page_title']))	 $this->page['page_title'] = trim($params['page_title']);
				if(isset($params['page_key'])) 		 $this->page['page_key'] = $params['page_key'];
				if(isset($params['page_text']))		 $this->page['page_text'] = $params['page_text'];
				if(isset($params['menu_id']))		 $this->page['menu_id'] = $params['menu_id'];
				if(isset($params['menu_link']))		 $this->page['menu_link'] = $params['menu_link'];
				if(isset($params['is_published']))	 $this->page['is_published'] = $params['is_published'];
				if(isset($params['comments_allowed'])) $this->page['comments_allowed'] = $params['comments_allowed'];
				if(isset($params['show_in_search'])) $this->page['show_in_search'] = $params['show_in_search'];
				if(isset($params['date_updated']))   $this->page['date_updated'] = $params['date_updated'];
				if(isset($params['finish_publishing']))$this->page['finish_publishing'] = $params['finish_publishing'];
				if(isset($params['priority_order'])) $this->page['priority_order'] = $params['priority_order'];
				if(isset($params['access_level']))   $this->page['access_level'] = $params['access_level'];

				if(isset($params['tag_title']))   	 $this->page['tag_title'] = $params['tag_title'];
				if(isset($params['tag_keywords']))   $this->page['tag_keywords'] = $params['tag_keywords'];
				if(isset($params['tag_description']))$this->page['tag_description'] = $params['tag_description'];		
				
				// Menu link cannot be more then 40 characters
				if(($this->page_id != 'home' && $this->page_id != 'public_home') && (strlen($this->page['menu_link']) > 40)) {
					$this->error = _PAGE_LINK_TOO_LONG;
					return false;
				}elseif($this->page['page_title'] == ''){
					$this->error = _PAGE_HEADER_EMPTY;
					return false;			
				}elseif($this->page['content_type'] == 'link' && $this->page['link_url'] == ''){
					$this->error = str_replace('_FIELD_', '<b>'._LINK.'</b>', _FIELD_CANNOT_BE_EMPTY);
					$this->focusOnField = 'link_url';
					return false;
				}elseif(Application::Get('type') == 'system' && $this->page['system_page'] == ''){
					$this->error = str_replace('_FIELD_', '<b>'._LINK_PARAMETER.'</b>', _FIELD_CANNOT_BE_EMPTY);
					$this->focusOnField = 'system_page';
					return false;				
				}elseif(!check_integer($this->page['priority_order']) || $this->page['priority_order'] < 0){
					$this->error = str_replace('_FIELD_', '<b>'._ORDER.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
					$this->focusOnField = 'priority_order';
					return false;
				}elseif(strlen($this->page['tag_title']) > 255){
					$msg_text = str_replace('_FIELD_', '<b>TITLE</b>', _FIELD_LENGTH_ALERT);
					$msg_text = str_replace('_LENGTH_', '255', $msg_text);
					$this->error = $msg_text;
					$this->focusOnField = 'tag_title';
					return false;
				}elseif(strlen($this->page['tag_keywords']) > 512){
					$msg_text = str_replace('_FIELD_', '<b>KEYWORDS</b>', _FIELD_LENGTH_ALERT);
					$msg_text = str_replace('_LENGTH_', '512', $msg_text);
					$this->error = $msg_text;
					$this->focusOnField = 'tag_keywords';
					return false;
				}elseif(strlen($this->page['tag_description']) > 512){
					$msg_text = str_replace('_FIELD_', '<b>DESCRIPTION</b>', _FIELD_LENGTH_ALERT);
					$msg_text = str_replace('_LENGTH_', '512', $msg_text);
					$this->error = $msg_text;
					$this->focusOnField = 'tag_description';
					return false;
				}
				
				$sql = 'UPDATE '.TABLE_PAGES.'
						SET
							content_type = \''.$this->page['content_type'].'\',
							link_url     = \''.encode_text($this->page['link_url']).'\',
							link_target  = \''.encode_text($this->page['link_target']).'\',
							page_title 	 = \''.encode_text($this->page['page_title']).'\',
							page_key 	 = \''.$this->page['page_key'].'\',
							page_text 	 = \''.encode_text($this->page['page_text']).'\',
							menu_id 	 = '.(int)$this->page['menu_id'].',
							menu_link    = \''.encode_text($this->page['menu_link']).'\',
							tag_title    = \''.encode_text($this->page['tag_title']).'\',
							tag_keywords = \''.encode_text($this->page['tag_keywords']).'\',
							tag_description = \''.encode_text($this->page['tag_description']).'\',
							comments_allowed = '.(int)$this->page['comments_allowed'].',
							show_in_search = '.(int)$this->page['show_in_search'].',
							date_updated = \''.($this->page['date_updated'] = date('Y-m-d H:i:s')).'\',
							finish_publishing = '.(!empty($this->page['finish_publishing']) ? "'".$this->page['finish_publishing']."'" : 'NULL').',
							is_published = '.(int)$this->page['is_published'].',
							access_level = \''.$this->page['access_level'].'\',
							priority_order = '.(int)$this->page['priority_order'].'							
							'.((Application::Get('type') == 'system') ? ', system_page=\''.create_seo_url($this->page['system_page']).'\'' : '').'
						WHERE id = \''.(int)$this->page['id'].'\'';						
				if(database_void_query($sql)){
					return true;
				}else{
					$this->error = _TRY_LATER;
					return false;
				}				
			}
		}else{
			$this->error = _PAGE_UNKNOWN;
			return false;
		}
	}
	
	/**
	 * Creates new page
	 * @param $params - set of fields
	 * @param $copy_to_other_langs
	 */
	public function PageCreate($params = array(), $copy_to_other_langs = 'yes')
	{		
		// Get input parameters
		if(isset($params['content_type']))	$this->page['content_type'] = $params['content_type'];
		if(isset($params['link_url']))		$this->page['link_url'] = $params['link_url'];
		if(isset($params['link_target']))	$this->page['link_target'] = $params['link_target'];
		if(isset($params['page_title']))	$this->page['page_title'] = $params['page_title'];
		if(isset($params['page_key']))		$this->page['page_key'] = $params['page_key'];
		if(isset($params['page_text']))		$this->page['page_text'] = $params['page_text'];
		if(isset($params['menu_id']))		$this->page['menu_id'] = $params['menu_id'];
		if(isset($params['menu_link']))		$this->page['menu_link'] = $params['menu_link'];
		if(isset($params['is_published']))  $this->page['is_published'] = $params['is_published'];
		if(isset($params['language_id'])) 	$this->page['language_id'] = $params['language_id'];
		if(isset($params['comments_allowed'])) $this->page['comments_allowed'] = $params['comments_allowed'];
		if(isset($params['show_in_search']))   $this->page['show_in_search'] = $params['show_in_search'];
		if(isset($params['priority_order']))   $this->page['priority_order'] = $params['priority_order'];
		if(isset($params['access_level']))     $this->page['access_level'] = $params['access_level'];
		if(isset($params['finish_publishing']))$this->page['finish_publishing'] = $params['finish_publishing'];

		if(isset($params['tag_title']))   	 $this->page['tag_title'] = $params['tag_title'];
		if(isset($params['tag_keywords']))   $this->page['tag_keywords'] = $params['tag_keywords'];
		if(isset($params['tag_description']))$this->page['tag_description'] = $params['tag_description'];		

		// Menu link cannot be more then 40 characters
		if(strlen($this->page['menu_link']) > 40){
			$this->error = _PAGE_LINK_TOO_LONG;
			return false;
		}elseif($this->page['page_title'] == ''){
			$this->error = _PAGE_HEADER_EMPTY;
			return false;
		}elseif($this->page['content_type'] == 'link' && $this->page['link_url'] == ''){
			$this->error = str_replace('_FIELD_', '<b>'._LINK.'</b>', _FIELD_CANNOT_BE_EMPTY);
			$this->focusOnField = 'link_url';
			return false;
		}elseif(!check_integer($this->page['priority_order']) || $this->page['priority_order'] < 0){
			$this->error = str_replace('_FIELD_', '<b>'._ORDER.'</b>', _FIELD_MUST_BE_NUMERIC_POSITIVE);
			$this->focusOnField = 'priority_order';
			return false;			
		}elseif(strlen($this->page['tag_title']) > 255){
			$msg_text = str_replace('_FIELD_', '<b>TITLE</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '255', $msg_text);
			$this->error = $msg_text;
			$this->focusOnField = 'tag_title';
			return false;
		}elseif(strlen($this->page['tag_keywords']) > 512){
			$msg_text = str_replace('_FIELD_', '<b>KEYWORDS</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '512', $msg_text);
			$this->error = $msg_text;
			$this->focusOnField = 'tag_keywords';
			return false;
		}elseif(strlen($this->page['tag_description']) > 512){
			$msg_text = str_replace('_FIELD_', '<b>DESCRIPTION</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '512', $msg_text);
			$this->error = $msg_text;
			$this->focusOnField = 'tag_description';
			return false;
		}
		
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}else{
			if($copy_to_other_langs == 'yes'){
				$total_languages = Languages::GetAllActive();
			}else{
	            $total_languages = Languages::GetAllLanguages(' priority_order ASC', '', 'abbreviation=\''.$this->page['language_id'].'\'');
			}
			$page_code = get_random_string(10);
			for($i = 0; $i < $total_languages[1]; $i++){					
				// Create new record
				$sql = 'INSERT INTO '.TABLE_PAGES.'(
						id,
						page_code,
						language_id,
						content_type,
						link_url,
						link_target,
						page_key,
						page_title,
						page_text,
						menu_id,
						menu_link,
						tag_title,
						tag_keywords,
						tag_description,
						comments_allowed,
						show_in_search,
						date_created,
						date_updated,
						finish_publishing,
						is_published,
						is_system_page,
						system_page,
						status_changed,
						access_level,
						priority_order
					)VALUES(
						NULL,
						\''.$page_code.'\',
						\''.$total_languages[0][$i]['abbreviation'].'\',
						\''.$this->page['content_type'].'\',
						\''.encode_text($this->page['link_url']).'\',
						\''.$this->page['link_target'].'\',
						\'\',
						\''.encode_text($this->page['page_title']).'\',
						\''.encode_text($this->page['page_text']).'\',
						'.(int)$this->GetMenuIdByLang($this->page['menu_id'], $total_languages[0][$i]['abbreviation']).',
						\''.encode_text($this->page['menu_link']).'\',
						\''.encode_text($this->page['tag_title']).'\',
						\''.encode_text($this->page['tag_keywords']).'\',
						\''.encode_text($this->page['tag_description']).'\',
						'.(int)$this->page['comments_allowed'].',
						'.(int)$this->page['show_in_search'].',
						\''.date('Y-m-d H:i:s').'\',
						NULL,
						'.(!empty($this->page['finish_publishing']) ? "'".$this->page['finish_publishing']."'" : 'NULL').',
						'.(int)$this->page['is_published'].',
						0,
						\'\',
						NULL,
						\''.$this->page['access_level'].'\',
						'.(int)$this->page['priority_order'].'
					)';
				if(database_void_query($sql)){				
					// Update page_key
					$last_insert_id = database_insert_id();
					$sql = 'UPDATE '.TABLE_PAGES.' 
							SET page_key=\''.$this->page['page_key'].'\'
							WHERE id='.(int)$last_insert_id;
					if(database_void_query($sql)){
						// ok
						$this->page_id = $last_insert_id;
					}else{
						$this->error = _TRY_LATER;
						return false;
					}
				}else{
					$this->error = _TRY_LATER;
					return false;
				}					
			}
			return true;				
		}					
	}

	/**
	 * Deletes a page
	 */
	public function PageDelete()
	{
		// check that the page exists
		if($this->page_id != ''){
			// Delete page
			if(strtolower(SITE_MODE) == 'demo'){
				$this->error = _OPERATION_BLOCKED;
				return false;
			}else{
				$sql = 'DELETE FROM '.TABLE_PAGES.' WHERE id = \''.(int)$this->page_id.'\'';
				database_void_query($sql);

				$sql = 'DELETE FROM '.TABLE_COMMENTS.' WHERE article_id = \''.(int)$this->page_id.'\'';
				database_void_query($sql);
				return true;
			}
		}
		$this->error = _TRY_LATER;
		return false;
	}

	/**
	 * Moves a page to trash
	 */
	public function MoveToTrash()
	{
		// check that the page exists
		if($this->page_id != ''){
			// Delete page
			if(strtolower(SITE_MODE) == 'demo'){
				$this->error = _OPERATION_BLOCKED;
				return false;
			}else{
				$sql = 'UPDATE '.TABLE_PAGES.' SET is_removed = 1, is_published = 0, status_changed = \''.date('Y-m-d H:i:s').'\' WHERE id = '.(int)$this->page_id;
				if(database_void_query($sql)){
					return true;
				}
			}
		}
		$this->error = _TRY_LATER;
		return false;
	}

	/**
	 * Moves page from the trash (PageRestore)
	 */
	public function PageRestore()
	{
		// check that the page exists
		if($this->page_id != ''){
			// Delete page
			if(strtolower(SITE_MODE) == 'demo'){
				$this->error = _OPERATION_BLOCKED;
				return false;
			}else{
				$sql = 'UPDATE '.TABLE_PAGES.' SET is_removed = 0 WHERE id = \''.(int)$this->page_id.'\'';
				if(database_void_query($sql)) return true;				
			}
		}
		$this->error = _TRY_LATER;
		return false;
	}

	/**
	 * Returns menu id by language
	 * @param $menu_id
	 * @param $lang
	 */
	private function GetMenuIdByLang($menu_id = '0', $lang = 'en')
	{
		$sql = 'SELECT '.TABLE_MENUS.'.menu_code
				FROM '.TABLE_MENUS.'
				WHERE '.TABLE_MENUS.'.id = \''.$menu_id.'\'';
		$menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		if(!empty($menu)){
			$sql = 'SELECT '.TABLE_MENUS.'.id
					FROM '.TABLE_MENUS.'
					WHERE
						'.TABLE_MENUS.'.menu_code = \''.$menu['menu_code'].'\' AND
						'.TABLE_MENUS.'.language_id = \''.$lang.'\'';
			$menu_id = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			if(!empty($menu_id)){
				return $menu_id['id'];
			}
		}
		return '0';
	}
	
	/**
	 * Returns data encoded
	 * @param $string
	 */
	private function GetDataEncoded($string = '')
	{
		return $string;
	}

	/**
	 * Returns page content with modules replacements
	 * @param &$page_text
	 * @return bool
	 */
	private function PrepareModulesContent(&$page_text)
	{
		$objGallery = new GalleryAlbums();
		$objContactUs = ContactUs::Instance();
		$module_page = false;

		if(preg_match('/{module:gallery}/i', $page_text)){
			$module_page = true;
			$page_text = @preg_replace('/{module:gallery}/i', $objGallery->DrawGallery(false), $page_text, 1);
		}
		if(preg_match_all('/{module:album=(.*?)}/i', $page_text, $matches)){
			$module_page = true;
			if(is_array($matches[1])){
				foreach($matches[1] as $key => $val){
					if(strtolower($val) != 'code'){
						$val = @preg_replace('/[^A-Za-z0-9:]/i', '', $val);
						$page_text = @preg_replace('/{module:album='.$val.'}/i', $objGallery->DrawAlbum($val, false), $page_text, 1);
					}
				}
			}
		}
		if(preg_match('/{module:about_us}/i', $page_text)){
			$module_page = true;
			$page_text = @preg_replace('/{module:about_us}/i', Hotels::DrawAboutUs(false), $page_text, 1);
		}
		if(preg_match('/{module:rooms}/i', $page_text)){
			$module_page = true;
			$page_text = @preg_replace('/{module:rooms}/i', Rooms::DrawRoomsInfo(false), $page_text, 1);
		}
		if(preg_match('/{module:hotels}/i', $page_text)){
			$module_page = true;
			$page_text = @preg_replace('/{module:hotels}/i', Hotels::DrawHotelsInfo(false), $page_text, 1);
		}
		if(preg_match('/{module:testimonials}/i', $page_text)){
			$module_page = true;
			$page_text = @preg_replace('/{module:testimonials}/i', Testimonials::DrawTestimonials(false), $page_text, 1);
		}
		if(preg_match('/{module:reviews}/i', $page_text)){
			$module_page = true;
			$page_text = @preg_replace('/{module:reviews}/i', Reviews::DrawReviews(false), $page_text, 1);
		}
		if(preg_match('/{module:contact_us}/i', $page_text)){
			$module_page = true;
			$page_text = @preg_replace('/{module:contact_us}/i', $objContactUs->DrawContactUsForm(false), $page_text, 1);
		}
		if(preg_match('/{module:faq}/i', $page_text)){
			$module_page = true;
			$page_text = @preg_replace('/{module:faq}/i', FaqCategories::DrawFaqList(false), $page_text, 1);
		}

		return $module_page;
	}

	/**
	 * Draw pages accessible dropdown menu
	 * @param $access_level
	 */
	public static function DrawPageAccessSelectBox($access_level = 'all')
	{
		echo '<select class="mgrid_select" name="access_level" id="access_level">';
			echo '<option value="public" '.(($access_level == 'public') ? ' selected="selected"' : '').'>'._PUBLIC.'</option>';
			echo '<option value="registered" '.(($access_level == 'registered') ? ' selected="selected"' : '').'>'._REGISTERED.'</option>';
		echo '</select>';		
	}	
	
	/**
	 * Update META tags for pages
	 * @param $params
	 * @param $lang_id
	 */
	public static function UpdateMetaTags($params, $lang_id = '')
	{
		$tag_title 		 = isset($params['tag_title']) ? $params['tag_title'] : '';
		$tag_keywords 	 = isset($params['tag_keywords']) ? $params['tag_keywords'] : '';
		$tag_description = isset($params['tag_description']) ? $params['tag_description'] : '';
		
		$sql = 'UPDATE '.TABLE_PAGES.' 
				SET
					tag_title = \''.encode_text($tag_title).'\',
					tag_keywords = \''.encode_text($tag_keywords).'\',
					tag_description = \''.encode_text($tag_description).'\'					
				WHERE language_id = \''.$lang_id.'\'';
		if(database_void_query($sql)){
			return true;
		}else{
			///$this->error = _TRY_LATER;
			return false;
		}
	}	

	/**
	 * Return page id for specific language
	 * @param $pid
	 * @param $lang
	 */
	public static function GetPageId($pid = '', $lang = '', $field_name = 'id')
	{
		if($pid != '' && $lang != ''){
			$sql = 'SELECT '.$field_name.'
					FROM '.TABLE_PAGES.'
					WHERE language_id = \''.$lang.'\' AND 
						  page_code = (SELECT page_code FROM '.TABLE_PAGES.' WHERE '.$field_name.' = \''.$pid.'\' LIMIT 0, 1)';
			$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			return isset($result[$field_name]) ? $result[$field_name] : '';			
		}else{
			return '';	
		}		
	}
	
	/**
	 * Check if this page is accessible 
	 * @param $is_logged
	 */
	public function CheckAccessRights($is_logged)
	{		
		if(isset($this->page['menu_access_level']) && $this->page['menu_access_level'] == 'registered' && !$is_logged){
			return false;
		}elseif(isset($this->page['access_level']) && $this->page['access_level'] == 'registered' && !$is_logged){
			return false;
		}
		return true;
	}	

	/**
	 *	Check if this page may be cached
	 */
	public function CacheAllowed()
	{
        if(count($this->page) <= 0) return false;
		if(preg_match('/{module:contact_us}/i', $this->page['page_text']) || $this->page['comments_allowed']){
			return false;
		}
        if(preg_match('/{module:rooms}/i', $this->page['page_text'])){
            return false;
        }
		return true;
	}	
	
}
