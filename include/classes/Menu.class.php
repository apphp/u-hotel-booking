<?php

/**
 *	Class Menu (for Hotel Site ONLY)
 *  -------------- 
 *  Description : encapsulates menu properties
 *  Updated	    : 11.09.2013
 *	Written by  : ApPHP
 *	
 *	PUBLIC:					STATIC:						PRIVATE:
 * 	------------------	  	---------------     		---------------
 *  __construct             GetAll       				GetAllFooter
 *  __destruct              DrawMenuSelectBox
 *  GetName                 DrawContentTypeBox
 *  GetParameter            DrawMenuPlacementBox
 *  GetId                   DrawMenuAccessSelectBox 
 *  GetOrder                DrawMenu 
 *  MenuUpdate        		DrawTopMenu
 *  MenuCreate              DrawFooterMenu
 *  MenuDelete              GetTopMenus
 *  MenuMove                GetMenuPages
 *                          GetMenuLinks (private)
 *            				GetMenus
 *                          GetAllSystemPages 
 *                          DrawHeaderMenu
 *                          GetAllTop
 *                          DrawHotelsSearchForm (private)
 *                          DrawCarsSearchForm (private)
 *                          
 **/

class Menu {

	private $id;
	
	protected $menu;
	protected $languageId;
	protected $whereClause;
	
	public $langIdByUrl;
	public $error;    
	
	//==========================================================================
    // Class Constructor
	//		@param $id
	//==========================================================================
	function __construct($id = '')
	{
		$this->id = $id;
		$this->languageId  = (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? prepare_input($_REQUEST['language_id']) : Languages::GetDefaultLang();
		$this->whereClause  = '';
		$this->whereClause .= ($this->languageId != '') ? ' AND language_id = \''.$this->languageId.'\'' : '';		
		$this->langIdByUrl = ($this->languageId != '') ? '&amp;language_id='.$this->languageId : '';
		
		if($this->id != ''){
			$sql = 'SELECT
						'.TABLE_MENUS.'.*,
						'.TABLE_LANGUAGES.'.lang_name as language_name
					FROM '.TABLE_MENUS.'
						LEFT OUTER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_MENUS.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
					WHERE '.TABLE_MENUS.'.id = \''.(int)$this->id.'\'';
			$this->menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		}else{
			$this->menu['menu_name'] = '';
			$this->menu['menu_placement'] = '';
			$this->menu['menu_order'] = '';
			$this->menu['language_id'] = '';
			$this->menu['language_name'] = '';
			$this->menu['access_level'] = '';
		}
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	//==========================================================================
    // PUBLIC METHODS
	//==========================================================================
	/**
	 *	Return a name of menu 
	 */
	public function GetName()
	{		
		if(isset($this->menu['menu_name'])) return decode_text($this->menu['menu_name']);
		else return '';
	}

	/**
	 * Return a value of parameter
	 * @param $param
	 */
	public function GetParameter($param = '')
	{
		if(isset($this->menu[$param])){
			return $this->menu[$param];
		}else{
			return '';
		}
	}
	
	/**
	 *	Returns menu ID
	 */
	public function GetId()
	{
		return $this->id;
	}
	
	/**
	 *	Returns menu order
	 */
	public function GetOrder()
	{
		if(isset($this->menu['menu_order'])) return $this->menu['menu_order'];
		else return '';
	}
	
	/**
	 * Updates menu 
	 * @param $param - array of parameters
	 */
	public function MenuUpdate($params = array())
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		if(isset($this->id)){
			// Get input parameters
			if(isset($params['name']) && $params['name'] != ''){
				$this->menu['menu_name'] = $params['name'];
			}else{
				$this->error = _MENU_NAME_EMPTY;
				return false;
			}
			if(isset($params['order'])) 		 $this->menu['menu_order'] = $params['order'];
		    if(isset($params['language_id'])) 	 $this->menu['language_id'] = $params['language_id'];
			if(isset($params['menu_placement'])) $this->menu['menu_placement'] = $params['menu_placement'];
			if(isset($params['access_level'])) 	 $this->menu['access_level'] = $params['access_level'];
			
			$sql = 'SELECT MIN(menu_order) as min_order, MAX(menu_order) as max_order FROM '.TABLE_MENUS;
			if($menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
				$min_order = $menu['min_order'];
				$max_order = $menu['max_order'];
				
				// insert menu with new priority order in menus list
				$sql = 'SELECT menu_order FROM '.TABLE_MENUS.' WHERE id = '.(int)$this->id;
				if($menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
					$sql_down = 'UPDATE '.TABLE_MENUS.' SET menu_order = menu_order - 1 WHERE language_id = \''.$this->menu['language_id'].'\' AND id <> '.(int)$this->id.' AND menu_order <= '.$this->menu['menu_order'].' AND menu_order > '.$menu['menu_order'];
					$sql_up = 'UPDATE '.TABLE_MENUS.' SET menu_order = menu_order + 1 WHERE language_id = \''.$this->menu['language_id'].'\' AND id <> '.(int)$this->id.' AND menu_order >= '.$this->menu['menu_order'].' AND menu_order < '.$menu['menu_order'];
					
					if($menu['menu_order'] != $this->menu['menu_order']){							
						$sql = 'UPDATE '.TABLE_MENUS.'
						        SET
								    language_id = \''.$this->menu['language_id'].'\',
									menu_name = \''.encode_text($this->menu['menu_name']).'\',
									menu_placement = \''.$this->menu['menu_placement'].'\',
								    menu_order = '.$this->menu['menu_order'].',
									access_level = \''.$this->menu['access_level'].'\'
								WHERE id = '.(int)$this->id.' AND menu_order <> '.$this->menu['menu_order'];
						if($result = database_void_query($sql)){
							if($this->menu['menu_order'] == $min_order){
								$sql = $sql_up;
							}elseif($this->menu['menu_order'] == $max_order){
								$sql = $sql_down;
							}else{
								if($menu['menu_order'] < $this->menu['menu_order']) $sql = $sql_down;
								else $sql = $sql_up;
							}
							$result = database_void_query($sql);
						}
					}else{
						$sql = 'UPDATE '.TABLE_MENUS.'
						        SET
									language_id = \''.$this->menu['language_id'].'\',
								    menu_name = \''.encode_text($this->menu['menu_name']).'\',
									menu_placement = \''.$this->menu['menu_placement'].'\',
									access_level = \''.$this->menu['access_level'].'\'
								WHERE id = '.(int)$this->id;
						$result = database_void_query($sql);
					}
				}
			}

			if($result >= 0){
				return true;
			}else{
				$this->error = _TRY_LATER;
				return false;
			}				
		}else{
			$this->error = _MENU_MISSED;
			return false;
		}
	}
	
	/**
	 * Creates new menu 
	 * @param $param - array of parameters
	 */
	public function MenuCreate($params = array())
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		// Get input parameters
		if(isset($params['name'])) 			 $this->menu['menu_name'] = $params['name'];
		if(isset($params['menu_placement'])) $this->menu['menu_placement'] = $params['menu_placement'];
		if(isset($params['order'])) 		 $this->menu['menu_order'] = $params['order'];
		if(isset($params['language_id'])) 	 $this->menu['language_id'] = $params['language_id'];
		if(isset($params['access_level']))   $this->menu['access_level'] = $params['access_level'];

		// Prevent creating of empty records in our 'menus' table
		if($this->menu['menu_name'] != ''){
			$menu_code = strtoupper(get_random_string(10));

			$total_languages = Languages::GetAllActive();
			for($i = 0; $i < $total_languages[1]; $i++){				

				$m = self::GetAll(' menu_order ASC', TABLE_MENUS, '', $total_languages[0][$i]['abbreviation']);
				$max_order = (int)($m[1]+1);			

				$sql = 'INSERT INTO '.TABLE_MENUS.' (language_id, menu_code, menu_name, menu_placement, menu_order, access_level)
						VALUES(\''.$total_languages[0][$i]['abbreviation'].'\', \''.$menu_code.'\', \''.encode_text($this->menu['menu_name']).'\', \''.$this->menu['menu_placement'].'\', '.$max_order.', \''.$this->menu['access_level'].'\')';
				if(!database_void_query($sql)){
					$this->error = _TRY_LATER;
					return false;
				}
			}
			return true;			
		}else{
			$this->error = _MENU_NAME_EMPTY;
			return false;
		}
	}

	/**
	 * Delete menu 
	 * @param $menu_id - menu ID
	 * @param $menu_order
	 */
	public function MenuDelete($menu_id = '0', $menu_order = '0')
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'SELECT language_id FROM '.TABLE_MENUS.' WHERE id = '.(int)$menu_id;
		if($menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$sql = 'DELETE FROM '.TABLE_MENUS.' WHERE id = '.(int)$menu_id;
			if(database_void_query($sql)){
				$sql = 'UPDATE '.TABLE_MENUS.' SET menu_order = menu_order - 1 WHERE language_id = \''.$menu['language_id'].'\' AND menu_order > '.(int)$menu_order;
				if(database_void_query($sql)){
					return true;    
				}                				   
			}
		}		
		return false;
	}

	/**
	 * Moves menu (change priority order)
	 * @param $menu_id
	 * @param $dir - direction
	 * @param $menu_order  - menu order
	 */
	public function MenuMove($menu_id, $dir = '', $menu_order = '')
	{		
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){ 
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		if(($dir == '') || ($menu_order == '')){
			$this->error = _WRONG_PARAMETER_PASSED;
			return false;
		}

		$sql = 'SELECT * FROM '.TABLE_MENUS.'
				WHERE
					id <> \''.(int)$menu_id.'\' AND
					menu_order '.(($dir == 'up') ? '<' : '>').' '.(int)$menu_order.' AND
					language_id = \''.$this->languageId.'\'
				ORDER BY menu_order '.(($dir == 'up') ? 'DESC' : 'ASC');
        if($menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$sql = 'UPDATE '.TABLE_MENUS.' SET menu_order = \''.$menu_order.'\' WHERE id = '.(int)$menu['id'];
			if(database_void_query($sql)){
				$sql = 'UPDATE '.TABLE_MENUS.' SET menu_order = \''.$menu['menu_order'].'\' WHERE id = '.(int)$menu_id;				
				if(!database_void_query($sql)){
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

	//==========================================================================
    // STATIC METHODS
	//==========================================================================
	/**
	 * Return array of all menus 
	 * @param $order - order clause
	 * @param $join_table - join tables
	 * @param $menu_placement
	 * @param $lang_id
	 */
	public static function GetAll($order = ' menu_order ASC', $join_table = '', $menu_placement = '', $lang_id = '')
	{
		$where_clause = '';
		if($menu_placement != ''){
			$where_clause .= 'AND '.TABLE_MENUS.'.menu_placement = \''.$menu_placement.'\' ';
		}
		if($lang_id != '') $where_clause .= 'AND '.$join_table.'.language_id = \''.$lang_id.'\' ';
		
		// Build ORDER BY CLAUSE
		if($order == '') $order_clause = '';
		else $order_clause = 'ORDER BY '.$order;		

		// Build JOIN clause
		if($join_table == '') {
			$join_clause = '';
			$join_select_fields = '';
		}elseif($join_table != TABLE_MENUS){
			$join_clause = 'LEFT OUTER JOIN '.$join_table.' ON '.$join_table.'.menu_id='.TABLE_MENUS.'.id ';
			$join_select_fields = ', '.$join_table.'.* ';
		} else {
			$join_clause = '';
			$join_select_fields = '';
        }		
		
		$sql = 'SELECT
					'.TABLE_MENUS.'.*,
					'.TABLE_LANGUAGES.'.lang_name as language_name
					'.$join_select_fields.' 
				FROM '.TABLE_MENUS.' 
				    INNER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_MENUS.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
					'.$join_clause.'
				WHERE 1=1
				'.$where_clause.'
				'.$order_clause;			
		
		return database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	}	

	/**
	 * Draws all menus in dropdowm box
	 * @param $menu_id
	 * @param $language_id
	 */
	public static function DrawMenuSelectBox($menu_id = '', $language_id = '')
	{	
		echo '<select class="mgrid_select" name="menu_id" id="menu_id" style="width:145px">';
		echo '<option value="">-- '._SELECT.' --</option>';
		$all_menus = self::GetAll(' menu_order ASC', TABLE_MENUS, '', $language_id);		                 
		for($i = 0; $i < $all_menus[1]; $i++){
			echo '<option value="'.$all_menus[0][$i]['id'].'"';
			echo ($all_menus[0][$i]['id'] == $menu_id) ? ' selected="selected" ' : '';
			echo '>'.$all_menus[0][$i]['menu_name'].'</option>';
		}
		echo '</select>';		
	}

	/**
	 * Return array of all footer menus 
	 * @param $where_clause
	 * @param $lang_id
	 */
	private static function GetAllFooter($where_clause = '', $lang_id = '')
	{
		global $objLogin;

		if($lang_id != '') $where_clause .= 'AND '.TABLE_PAGES.'.language_id = \''.$lang_id.'\' ';
		
		// Get all top menus
		$sql = 'SELECT '.TABLE_PAGES.'.* 
				FROM '.TABLE_PAGES.'
					INNER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id = '.TABLE_MENUS.'.id
				WHERE '.TABLE_MENUS.'.menu_placement = \'bottom\' AND 
					is_published = 1 AND 
					('.TABLE_PAGES.'.finish_publishing IS NULL OR '.TABLE_PAGES.'.finish_publishing >= \''.@date('Y-m-d').'\')
					'.((!$objLogin->IsLoggedIn()) ? ' AND ('.TABLE_MENUS.'.access_level = \'public\' AND '.TABLE_PAGES.'.access_level = \'public\')' : '').'					
					'.$where_clause.'
				ORDER BY '.TABLE_MENUS.'.menu_order ASC, '.TABLE_PAGES.'.priority_order ASC';				
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 * Draw content type dropdowm box
	 * @param $content_type
	 */
	public static function DrawContentTypeBox($content_type = '')
	{
		echo '<select class="mgrid_select" name="content_type" onchange="ContentType_OnChange(this.value);" >';
		echo '<option value="article" '.(($content_type == 'article') ? ' selected="selected"' : '').'>'._ARTICLE.'</option>';
		echo '<option value="link" '.(($content_type == 'link') ? ' selected="selected"' : '').'>'._LINK.'</option>';
		echo '</select>';		
	}

	/**
	 * Draw menus placement in dropdowm box
	 * @param $menu_placement
	 * @param $draw
	 */
	public static function DrawMenuPlacementBox($menu_placement = '', $draw = true)
	{
		global $objSettings;

		$menus = array(
			'left'  =>array('placement'=>_LEFT, 'avalable'=>false),
			'top'   =>array('placement'=>_TOP, 'avalable'=>false),
			'right' =>array('placement'=>_RIGHT, 'avalable'=>false),
			'bottom'=>array('placement'=>_BOTTOM, 'avalable'=>false),
		);

		// load data from XML file
		$template = $objSettings->GetParameter('template');
		if(@file_exists('templates/'.$template.'/info.xml')) {
			$xml = simplexml_load_file('templates/'.$template.'/info.xml');		 
			if(isset($xml->menus->menu)){
				foreach($xml->menus->menu as $menu){
					if(isset($menus[strtolower($menu)])) $menus[strtolower($menu)]['avalable'] = true;
				}				
			}
		}		
		$output = '<select class="mgrid_select" name="menu_placement">';
		foreach($menus as $menu => $val){
			$output .= '<option value="'.$menu.'"'.(($menu_placement == $menu) ? ' selected="selected" ' : '').((!$val['avalable']) ? ' disabled="disabled"' : '').'>'.$val['placement'].((!$val['avalable']) ? ' ('._DISABLED.')' : '').'</option>';			
		}
		$output .= '<option value="hidden" '.(($menu_placement == 'hidden') ? ' selected="selected" ' : '').'>- '._HIDDEN.' -</option>';
		$output .= '</select>';

		if($draw) echo $output;
		else return $output;		
	}

	/**
	 * Draw menu accessible dropdown menu
	 * @param $access_level
	 */
	public static function DrawMenuAccessSelectBox($access_level = 'public')
	{
		echo '<select class="mgrid_select" name="access_level" id="access_level">';
			echo '<option value="public" '.(($access_level == 'public') ? ' selected="selected"' : '').'>'._PUBLIC.'</option>';
			echo '<option value="registered" '.(($access_level == 'registered') ? ' selected="selected"' : '').'>'._REGISTERED.'</option>';
		echo '</select>';		
	}	

	/**
	 * Draws menus 
	 * @param $menu_position
	 * @param $draw
	 * @param $params
	 */
	public static function DrawMenu($menu_position = 'left', $draw = true, $params = array())
	{
		global $objSettings, $objLogin;
		$output = '';
		$search_availability = isset($params['search_availability']) ? (bool)$params['search_availability'] : true;
		$reservation = isset($params['reservation']) ? $params['reservation'] : 'hotels';
		$page = Application::Get('page');
		
		// Get all menus which have items (links to pages)
		$menus = self::GetMenus($menu_position);
		$menus_count = $menus[1];
        $menu_counter = 0;

		if($menu_position == 'left'){
			if(Modules::IsModuleInstalled('conferences') && Application::Get('page') == 'conference_general'){
				$output .= Conferences::DrawConferenceMenu(false);
			}
			if($page == 'check_availability' && ($reservation == 'hotels' && Modules::IsModuleInstalled('booking'))){
				$output .= self::DrawHotelsSearchForm($search_availability, $menu_counter);
			}elseif($page == 'check_cars_availability' && ($reservation == 'cars' && Modules::IsModuleInstalled('car_rental'))){
				$output .= self::DrawCarsSearchForm($menu_counter);
			}
			$output .= $objLogin->DrawLoginLinks(false);
		}
		
		//variant 1. if($menus_count > 0) $output .= '<div id="column-'.$menu_position.'-wrapper">';
		//variant 2. if($menus_count > 0) $output = '<div id="column-'.$menu_position.'-wrapper" style="'.(($menus_count > 0) ? 'width:205px;' : '').'">';

		if($page != 'check_availability' && ($reservation == 'hotels' && Modules::IsModuleInstalled('booking'))){
			$output .= self::DrawHotelsSearchForm($search_availability, $menu_counter);
		}elseif($page != 'check_cars_availability' && ($reservation == 'cars' && Modules::IsModuleInstalled('car_rental'))){
			$output .= self::DrawCarsSearchForm($menu_counter);
		}
		
		$objNews = News::Instance();
		$show_news_block = ModulesSettings::Get('news', 'show_news_block');
		$show_subscribe_block = ModulesSettings::Get('news', 'show_newsletter_subscribe_block');
		//if(Modules::IsModuleInstalled('news') && ($show_news_block == 'right side' || $show_subscribe_block == 'right side')) $menus_count++;

		// Display all menu titles (names) according to their order
		for($menu_ind = 0; $menu_ind < $menus[1]; $menu_ind++){				
			// Start draw new menu
			$output .= draw_block_top($menus[0][$menu_ind]['menu_name'], $menu_counter++, 'maximazed', 'hidden-xs', false);

			$menu_links = self::GetMenuLinks($menus[0][$menu_ind]['id'], Application::Get('lang'), $menu_position);
			if($menu_links[1] > 0) $output .= '<ul class="'.Application::Get('lang_dir').'">';
			for($menu_link_ind = 0; $menu_link_ind < $menu_links[1]; $menu_link_ind++) {
				if($menu_links[0][$menu_link_ind]['content_type'] == 'link'){
					$output .= '<li>'.prepare_permanent_link($menu_links[0][$menu_link_ind]['link_url'], $menu_links[0][$menu_link_ind]['menu_link'], $menu_links[0][$menu_link_ind]['link_target'], 'main_menu_link').'</li>';
				}else{					
					// draw current menu link
					$class = (Application::Get('page_id') == $menu_links[0][$menu_link_ind]['id']) ? ' active' : '';
					$output .= '<li>'.prepare_link('pages', 'pid', $menu_links[0][$menu_link_ind]['id'], $menu_links[0][$menu_link_ind]['page_key'], $menu_links[0][$menu_link_ind]['menu_link'], 'main_menu_link'.$class).'</li>';
				}
			}
			if($menu_links[1] > 0) $output .= '</ul>';
			$output .= draw_block_bottom(false);
		}
		
		if($menu_position == 'right'){
			if(Modules::IsModuleInstalled('news')){
				if($show_news_block == 'right side') $output .= $objNews->DrawNewsBlock(false, $menu_counter++);
				if($show_subscribe_block == 'right side') $output .= $objNews->DrawSubscribeBlock(false, $menu_counter++);	
			}
		}
		
		if($menu_position == 'left'){
			if(!$objLogin->IsLoggedIn() || Application::Get('preview') == 'yes'){
				if(Modules::IsModuleInstalled('customers') && ModulesSettings::Get('customers', 'allow_login') == 'yes'){
					if(Application::Get('customer') != 'login'){
						$output .= Customers::DrawLoginFormBlock(false, $menu_counter++);		
					}
				}				
			}
			if(Modules::IsModuleInstalled('news')){
				if($show_news_block == 'left side') $output .= $objNews->DrawNewsBlock(false, $menu_counter++);
				if($show_subscribe_block == 'left side') $output .= $objNews->DrawSubscribeBlock(false, $menu_counter++);	
			}
			if(Modules::IsModuleInstalled('booking')){
				if(in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'front-end'))){					
					if(ModulesSettings::Get('booking', 'payment_type_paypal') != 'no' || ModulesSettings::Get('booking', 'payment_type_2co') != 'yes' || ModulesSettings::Get('booking', 'payment_type_authorize') != 'yes'){
						$output .= draw_block_top(_PAYMENT_METHODS, $menu_counter++, 'maximized', 'hidden-xs', false);
						$output .= '<div class="payment_instruments"><img src="images/ppc_icons/logo_paypal.gif" title="PayPal" alt="PayPal" />
							  <img src="images/ppc_icons/logo_ccVisa.gif" title="Visa" alt="Visa" />
							  <img src="images/ppc_icons/logo_ccMC.gif" title="MasterCard" alt="MasterCard" />
							  <img src="images/ppc_icons/logo_ccAmex.gif" title="Amex" alt="Amex" /></div>';
						$output .= draw_block_bottom(false);
					}
				}
			}
			
			// Draw local time
			if(Hotels::HotelsCount() == 1){
				$output .= draw_block_top(_LOCAL_TIME, $menu_counter++, 'maximazed', 'hidden-xs', false);
				$output .= Hotels::DrawLocalTime('', false);
				$output .= draw_block_bottom(false);
			}
			
			$output .= draw_block_footer(false);			
		}
		
		if($draw) echo $output;
		else return $output;		
		
		//if($menus_count > 0) $output .= '</div>';		
	}

	/**
	 *	Draws top menu
	 */
	public static function DrawTopMenu()
	{		
		$nl = "\n";
		echo '<li><a href="'.APPHP_BASE.'index.php">'._HOME.'</a></li>'.$nl;

		if(Modules::IsModuleInstalled('customers')){			
			echo '<li><a href="index.php?customer=my_account">'._MY_ACCOUNT.'</a></li>';
		}
		if(Modules::IsModuleInstalled('booking')){				
			if(in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'front-end'))){
				//echo '<li><a href="index.php?page=booking">'._BOOKING.'</a></li>';
				echo '<li><a href="index.php?page=checkout">'._CHECKOUT.'</a></li>';
			}
		}
		
		$menus = self::GetTopMenus($lang);
		for($i = 0; $i < $menus[1]; $i++) {
			$menu_pages = self::GetMenuPages($menus[0][$i]['id'], Application::Get('lang'));
			
			if($menu_pages[1] == 1){
				$css_class = (Application::Get('page_id') == $menu_pages[0][0]['id']) ? 'active' : '';
				if($menu_pages[0][0]['content_type'] == 'link'){
					$output .= '<li>'.prepare_permanent_link($menu_pages[0][0]['link_url'], $menu_pages[0][0]['menu_link'], $menu_pages[0][0]['link_target']).'</li>'.$nl;
				}else{					
					$output .= '<li>'.prepare_link('pages', 'pid', $menu_pages[0][0]['id'], $menu_pages[0][0]['page_key'], $menu_pages[0][0]['menu_link'], $css_class).'</li>'.$nl;
				}				
			}elseif($menu_pages[1] > 0){
				echo '<li><a href="javascript:void(0);">'.$menus[0][$i]['menu_name'].'</a>';
				echo '<ul class="dropdown_inner" style="width:200px">';
				// Draw current menu link
				for($j = 0; $j < $menu_pages[1]; $j++) {
                    $css_class = (Application::Get('page_id') == $menu_pages[0][$j]['id']) ? 'active' : '';
					if($menu_pages[0][$j]['content_type'] == 'link'){
					    echo '<li>'.prepare_permanent_link($menu_pages[0][$j]['link_url'], $menu_pages[0][$j]['menu_link'], $menu_pages[0][$j]['link_target']).'</li>';					
					}else{					
						echo '<li>'.prepare_link('pages', 'pid', $menu_pages[0][$j]['id'], $menu_pages[0][$j]['page_key'], $menu_pages[0][$j]['menu_link'], $css_class).'</li>';						
					}					
				}
				echo '</ul>';
				echo '</li>';
			}				
		}
	}

	/**
	 *	Draws all menus for footer
	 */
	public static function DrawFooterMenu($wrapper = '', $page_types = 'all')
	{
		$lang = Application::Get('lang');
        $output = '';

		$system_pages = self::GetAllSystemPages();
        
        if($page_types == 'all' || $page_types == 'general'){

			$output .= ($wrapper == 'ul') ? '<li>' : '';
			$output .= '<a href="'.APPHP_BASE.'index.php">'._HOME.'</a>';
			$output .= ($wrapper == 'ul') ? '</li>' : '';
			
            for($ind = 0; $ind < $system_pages[1]; $ind++) {
                if(($system_pages[0][$ind]['is_published']) &&
                   ($system_pages[0][$ind]['system_page'] == 'terms_and_conditions' ||
                    $system_pages[0][$ind]['system_page'] == 'about_us' ||
                    $system_pages[0][$ind]['system_page'] == 'contact_us')
                    ){
                    if($system_pages[0][$ind]['content_type'] == 'link'){
                        $output .= ($wrapper == 'ul') ? '<li>' : '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
                        $output .= prepare_permanent_link($system_pages[0][$ind]['link_url'], $system_pages[0][$ind]['menu_link'], $system_pages[0][$ind]['link_target']);
                        if($wrapper == 'ul') $output .= '</li>';                    
                    }else{					
                        $output .= ($wrapper == 'ul') ? '<li>' : '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
                        $output .= prepare_link('pages', 'system_page', $system_pages[0][$ind]['system_page'], 'index', $system_pages[0][$ind]['menu_link'], '');
                        if($wrapper == 'ul') $output .= '</li>';                    
                    }
                }
            }

			$menus = self::GetAllFooter('', $lang);
			for($menu_ind = 0; $menu_ind < $menus[1]; $menu_ind++) {
				$output .= ($wrapper == 'ul') ? '<li>' : '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
				$output .= prepare_link('pages', 'pid', $menus[0][$menu_ind]['id'], $menus[0][$menu_ind]['page_key'], $menus[0][$menu_ind]['menu_link'], '');
				if($wrapper == 'ul') $output .= '</li>';                    
			}
        }

        if($page_types == 'all' || $page_types == 'system'){
            for($ind = 0; $ind < $system_pages[1]; $ind++) {
                if(($system_pages[0][$ind]['is_published']) &&
                   ($system_pages[0][$ind]['system_page'] != 'terms_and_conditions' &&
                    $system_pages[0][$ind]['system_page'] != 'about_us' &&
                    $system_pages[0][$ind]['system_page'] != 'contact_us')
                   ){
                    if($ind != 0 && $system_pages[0][$ind]['menu_link']){
                        if($wrapper != 'ul') $output .= '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
                    }
                    if($wrapper == 'ul') $output .= '<li>';
                    if($system_pages[0][$ind]['content_type'] == 'link'){
                        $output .= prepare_permanent_link($system_pages[0][$ind]['link_url'], $system_pages[0][$ind]['menu_link'], $system_pages[0][$ind]['link_target']);
                    }else{					
                        $output .= prepare_link('pages', 'system_page', $system_pages[0][$ind]['system_page'], 'index', $system_pages[0][$ind]['menu_link'], '');
                    }
                    if($wrapper == 'ul') $output .= '</li>';                    
                }
            }
            if(Modules::IsModuleInstalled('booking')){
                if(in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'front-end'))){
                    $output .= ($wrapper == 'ul') ? '<li>' : '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
                    $output .= '<a href="index.php?page=booking">'._BOOKING.'</a>';
                    if($wrapper == 'ul') $output .= '</li>';
					$output .= ($wrapper == 'ul') ? '<li>' : '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
					$output .= '<a href="index.php?page=check_status">'._CHECK_STATUS.'</a>';
					if($wrapper == 'ul') $output .= '</li>';
                }
            }
            if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
                $output .= ($wrapper == 'ul') ? '<li>' : '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
                $output .= '<a href="index.php?page=book_now_car">'._CAR_RENTAL.'</a>';
                if($wrapper == 'ul') $output .= '</li>';                    
            }
        }
			
//		$menus = self::GetAllFooter('', $lang);
//		if($menus[1] > 0) $output .= '<br />';
//		for($menu_ind = 0; $menu_ind < $menus[1]; $menu_ind++) {
//			if($menu_ind > 0){
//                $output .= ($wrapper == 'ul') ? '<li>' : '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
//            }
//			$output .= prepare_link('pages', 'pid', $menus[0][$menu_ind]['id'], $menus[0][$menu_ind]['page_key'], $menus[0][$menu_ind]['menu_link'], '');
//            if($wrapper == 'ul') $output .= '</li>';                    
//		}
		
		echo $output;
	}

	/**
	 * Return array of all top menus 
	 * @param $lang_id
	 */
	public static function GetTopMenus($lang_id = '')
	{
		global $objLogin;
		
		$where_clause = ($lang_id != '') ? ' AND '.TABLE_MENUS.'.language_id = \''.$lang_id.'\' ' : '';
		
		// Get all top menus
		$sql = 'SELECT '.TABLE_MENUS.'.* 
				FROM '.TABLE_MENUS.'
				WHERE '.TABLE_MENUS.'.menu_placement = \'top\'
				    '.((!$objLogin->IsLoggedIn()) ? ' AND '.TABLE_MENUS.'.access_level = \'public\'' : '').'
					'.$where_clause.'
				ORDER BY '.TABLE_MENUS.'.menu_order ASC';
		return database_query($sql, DATA_AND_ROWS);
	}	

	/**
	 * Returns all top pages fot to pmenu
	 * @param $menu_id
	 * @param $lang_id
	 */
	public static function GetMenuPages($menu_id = '0', $lang_id = '')
	{
		global $objLogin;

		$where_clause = ($lang_id != '') ? ' AND '.TABLE_PAGES.'.language_id = \''.$lang_id.'\' ' : '';
		
		// Get all top menus
		$sql = 'SELECT '.TABLE_PAGES.'.* 
				FROM '.TABLE_PAGES.'
					INNER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id = '.TABLE_MENUS.'.id
				WHERE
					'.TABLE_MENUS.'.id = \''.$menu_id.'\' AND 
					'.TABLE_PAGES.'.is_published = 1 AND
					('.TABLE_PAGES.'.finish_publishing IS NULL OR '.TABLE_PAGES.'.finish_publishing >= \''.@date('Y-m-d').'\')
					'.((!$objLogin->IsLoggedIn()) ? ' AND ('.TABLE_MENUS.'.access_level = \'public\' AND '.TABLE_PAGES.'.access_level = \'public\')' : '').'					
					'.$where_clause.'
				ORDER BY '.TABLE_PAGES.'.priority_order ASC';				
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 * Returns all left menu links array
	 * @param $menu_id
	 * @param $lang_id
	 * @param $position
	 */
	private static function GetMenuLinks($menu_id, $lang_id = '', $position = 'left')
	{
		global $objLogin;
		
		// Get all left menus
		$sql = 'SELECT
					'.TABLE_PAGES.'.*
				FROM '.TABLE_PAGES.'
				    INNER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_PAGES.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
					INNER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id = '.TABLE_MENUS.'.id
				WHERE
					'.TABLE_PAGES.'.language_id = \''.$lang_id.'\' AND
					'.TABLE_MENUS.'.menu_placement = \''.$position.'\' AND
					'.TABLE_PAGES.'.menu_id = \''.$menu_id.'\' AND
					'.TABLE_PAGES.'.is_home = 0 AND
					'.TABLE_PAGES.'.is_published = 1 AND
					('.TABLE_PAGES.'.finish_publishing IS NULL OR '.TABLE_PAGES.'.finish_publishing >= \''.@date('Y-m-d').'\')
					'.((!$objLogin->IsLoggedIn()) ? ' AND '.TABLE_PAGES.'.access_level = \'public\'' : '').'
				ORDER BY '.TABLE_PAGES.'.priority_order ASC';
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 * Returns all left menus array
	 * @param $position
	 */
	public static function GetMenus($position = 'left')
	{
		global $objLogin;

		// Get all left menus
		$sql = 'SELECT
					'.TABLE_MENUS.'.*
				FROM '.TABLE_MENUS.'
				    INNER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_MENUS.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
				WHERE
					'.TABLE_MENUS.'.language_id = \''.Application::Get('lang').'\' AND
					'.TABLE_MENUS.'.menu_placement = \''.$position.'\'
					'.((!$objLogin->IsLoggedIn()) ? ' AND '.TABLE_MENUS.'.access_level = \'public\'' : '').'
				ORDER BY '.TABLE_MENUS.'.menu_order ASC';
		return database_query($sql, DATA_AND_ROWS);
	}
	
	/**
	 *	Returns array of all system pages 
	 */
	public static function GetAllSystemPages()
	{
		$sql = 'SELECT '.TABLE_PAGES.'.* 
				FROM '.TABLE_PAGES.'
				WHERE
					is_system_page = 1 AND					
					language_id = \''.Application::Get('lang').'\' 
				ORDER BY priority_order ASC';				
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 *	Draws all menus for header
	 *	@param bool $draw
	 *	@param array $params
	 */
	public static function DrawHeaderMenu($draw = true, $params = array())
	{
		$nl = "\n";
		$system_page = Application::Get('system_page');
		$page = isset($_GET['page']) ? prepare_input($_GET['page']) : '';			
		if($page == 'booking' || $page == 'booking_details' || $page == 'booking_checkout' || $page == 'booking_payment') $system_page = 'booking';
        $property_type_id = isset($_REQUEST['property_type_id']) ? (int)$_REQUEST['property_type_id'] : 0;
		
		$wrapper_id = isset($params['wrapper_id']) ? ' id="'.$params['wrapper_id'].'"' : '';
		$wrapper_class = isset($params['wrapper_class']) ? ' class="'.$params['wrapper_class'].'"' : ' class="nav nav_bg"';
		$current_item_class = isset($params['current_item_class']) ? $params['current_item_class'] : '';
		
		$output = '<ul'.$wrapper_id.$wrapper_class.'>';
		$output .= '<li '.(($system_page == '' && $page == '') ? 'class="active"' : '').'><a href="'.APPHP_BASE.'index.php" '.(($system_page == '') ? 'class="current"' : '').'>'._HOME.'</a></li>';
		
		// prepare properties array		
		$property_types = Application::Get('property_types');
		foreach($property_types as $key => $val){
			$output .= '<li '.(($page == 'check_'.$val['property_code'] || ($page == 'check_availability' && $property_type_id == $val['id'])) ? 'class="active"' : '').'><a href="'.APPHP_BASE.'index.php?page=check_'.$val['property_code'].'">'.$val['name'].'</a></li>';
		}
		
		if(Modules::IsModuleInstalled('car_rental')){
			if(ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
				$output .= '<li'.($page == 'check_cars_availability' ? ' class="active"' : '').'><a href="'.APPHP_BASE.'index.php?page=check_cars_availability">'._CARS.'</a></li>';
			}
		}

		$system_pages = self::GetAllSystemPages();
		for($ind = 0; $ind < $system_pages[1]; $ind++) {
			if(($system_pages[0][$ind]['is_published']) &&
			    $system_pages[0][$ind]['system_page'] != 'terms_and_conditions' &&
			    $system_pages[0][$ind]['system_page'] != 'about_us' &&
                $system_pages[0][$ind]['system_page'] != 'reviews' &&
				$system_pages[0][$ind]['system_page'] != 'contact_us' &&
				(!Modules::IsModuleInstalled('conferences') || ($system_pages[0][$ind]['system_page'] != 'restaurant' && $system_pages[0][$ind]['system_page'] != 'rooms'))
				){

				$li_class = '';
				if($system_page == $system_pages[0][$ind]['system_page']){
					$li_class = ' class="active '.$current_item_class.'"';
				}

				if($system_pages[0][$ind]['content_type'] == 'link'){
					$output .= '<li>'.prepare_permanent_link($system_pages[0][$ind]['link_url'], $system_pages[0][$ind]['menu_link'], $system_pages[0][$ind]['link_target'], (($system_page == $system_pages[0][$ind]['system_page']) ? 'current' : '')).'</li>';
				}else{					
					$output .= '<li'.$li_class.'>'.prepare_link('pages', 'system_page', $system_pages[0][$ind]['system_page'], 'index', $system_pages[0][$ind]['menu_link'], (($system_page == $system_pages[0][$ind]['system_page']) ? 'current' : '')).'</li>';
				}
			}
		}
		if(Modules::IsModuleInstalled('conferences')){
			$li_class = ($page == 'conferences') ? ' class="active '.$current_item_class.'"' : '';
			$a_class = ($page == 'conferences') ? ' class="current"' : '';
			$output .= '<li'.$li_class.'><a href="index.php?page=conferences"'.$a_class.'>'._CONFERENCES.'</a></li>';	
		}
		if(Modules::IsModuleInstalled('booking')){
			if(in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'front-end'))){
				//$li_class = ($system_page == 'booking') ? ' class="active '.$current_item_class.'"' : '';
				//$a_class = ($system_page == 'booking') ? ' class="current"' : '';
				//$output .= '<li'.$li_class.'><a href="index.php?page=booking"'.$a_class.'>'._BOOKING.'</a></li>';	
			}
		}
		
		// draw "top" placed menus
		$menus = self::GetTopMenus(Application::Get('lang'));
		for($i = 0; $i < $menus[1]; $i++) {
			$menu_pages = self::GetMenuPages($menus[0][$i]['id'], Application::Get('lang'));
			
			if($menu_pages[1] == 1){
				$css_class = (Application::Get('page_id') == $menu_pages[0][0]['id']) ? 'active' : '';
				if($menu_pages[0][0]['content_type'] == 'link'){
					$output .= '<li>'.prepare_permanent_link($menu_pages[0][0]['link_url'], $menu_pages[0][0]['menu_link'], $menu_pages[0][0]['link_target']).'</li>'.$nl;
				}else{					
					$output .= '<li>'.prepare_link('pages', 'pid', $menu_pages[0][0]['id'], $menu_pages[0][0]['page_key'], $menu_pages[0][0]['menu_link'], $css_class).'</li>'.$nl;
				}				
			}elseif($menu_pages[1] > 0){
				$output .= '<li><a href="javascript:void(0)">'.$menus[0][$i]['menu_name'].'</a>'.$nl;
				$output .= '<ul class="dropdown_inner">'.$nl;
				// Draw current menu link
				for($j = 0; $j < $menu_pages[1]; $j++){
                    $css_class = (Application::Get('page_id') == $menu_pages[0][$j]['id']) ? 'active' : '';
					if($menu_pages[0][$j]['content_type'] == 'link'){
					    $output .= '<li>'.prepare_permanent_link($menu_pages[0][$j]['link_url'], $menu_pages[0][$j]['menu_link'], $menu_pages[0][$j]['link_target']).'</li>'.$nl;
					}else{					
						$output .= '<li>'.prepare_link('pages', 'pid', $menu_pages[0][$j]['id'], $menu_pages[0][$j]['page_key'], $menu_pages[0][$j]['menu_link'], $css_class).'</li>'.$nl;
					}
				}
				$output .= '</ul>'.$nl;
				$output .= '</li>'.$nl;
			}				
		}

		$output .= '</ul>';		

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Returns array of all top pages 
	 * @param $where_clause
	 * @param $lang_id
	 */
	public static function GetAllTop($where_clause = '', $lang_id = '')
	{
		global $objLogin;

		if($lang_id != '') $where_clause .= 'AND '.TABLE_PAGES.'.language_id = \''.$lang_id.'\' ';
		
		// Get all top menus
		$sql = 'SELECT '.TABLE_PAGES.'.* 
				FROM '.TABLE_PAGES.'
					INNER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id = '.TABLE_MENUS.'.id
				WHERE '.TABLE_MENUS.'.menu_placement = \'top\' AND
					is_published = 1 					
					'.((!$objLogin->IsLoggedIn()) ? ' AND ('.TABLE_MENUS.'.access_level = \'public\' AND '.TABLE_PAGES.'.access_level = \'public\')' : '').'					
					'.$where_clause.' 
				ORDER BY '.TABLE_MENUS.'.menu_order ASC, '.TABLE_PAGES.'.priority_order ASC';				
		return database_query($sql, DATA_AND_ROWS);
	}
	
	/**
	 * Draws hotels search form
	 * @param $search_availability
	 * @param &$menu_counter
	 * @return HTML
	 */
	private static function DrawHotelsSearchForm($search_availability, &$menu_counter)
	{
		global $objLogin;
		$output = '';
		
		if(ModulesSettings::Get('booking', 'show_reservation_form') == 'yes'){
			if($search_availability && Application::Get('page') != 'rooms'){
				$output .= draw_block_top(_RESERVATION, $menu_counter++, 'maximazed', '', false);
				$output .= Rooms::DrawSearchAvailabilityBlock(true, '', '', 8, 3, 'main-vertical', '', '', false);
				$output .= draw_block_bottom(false);
			}
			
			if(!$objLogin->IsLoggedIn() && (ModulesSettings::Get('booking', 'show_booking_status_form') == 'yes')){			
				if(!$objLogin->IsLoggedIn() && (ModulesSettings::Get('booking', 'show_booking_status_form') == 'yes')){			
					if(Application::Get('page') != 'check_status'){
						$output .= draw_block_top(_BOOKING_STATUS, $menu_counter++, 'maximazed', 'hidden-xs', false);
						$output .= Bookings::DrawBookingStatusBlock(false);
						$output .= draw_block_bottom(false);
					}
				}
			}
		}
		
		return $output;
	}

		
	/**
	 * Draws cars search form
	 * @param &$menu_counter
	 * @return HTML
	 */
	private static function DrawCarsSearchForm(&$menu_counter)
	{
		global $objLogin;
		$output = '';

		if(ModulesSettings::Get('car_rental', 'is_active') == 'yes'){			
			$output .= draw_block_top(_RENT_A_CAR, $menu_counter++, 'maximazed', '', false);
			$output .= CarAgencies::DrawSearchAvailabilityBlock('menu');
			$output .= draw_block_bottom(false);
		}
		
		return $output;
	}
}
