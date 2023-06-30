<?php

/**
 *	Class News
 *  -------------- 
 *  Description : encapsulates mews operations & properties
 *	Written by  : ApPHP
 *	Version     : 1.0.9
 *  Updated	    : 11.03.2016
 *  Usage       : Core Class
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             GetNewsId               GetNewsCount
 *	__destruct              GetNewsInfo
 *	SetSQLs                 CacheAllowed
 *	DrawNewsBlock           GetAllNews
 *	DrawNews
 *	ProcessSubscription
 *	ProcessUnsubscription
 *	DrawSubscribeBlockMain
 *	DrawSubscribeBlock
 *	DrawSubscribeBlockFooter
 *	GetNews
 *	DrawRegistrationForm
 *	AfterInsertRecord
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDetailsMode
 *	
 *  1.0.9
 *      - added SEO links for View All news page
 *      - added _ADMIN_COPY
 *      - added DrawLastMinuteNews
 *      -
 *      -
 *  1.0.8
 *      - fixed bug in View All link
 *      - fixed bug with creating "events" for all languages
 *      - added is_active field
 *      - bug fixed when page size equal total number of records
 *      - fixed issue with auto ficus on first field in event registration form
 *  1.0.7
 *      - added sqlFieldDatetimeFormat for langs
 *      - removed align="left" from news event subscription
 *      - added View All link undernease
 *      - added GetNewsCount
 *      - cleaned 'format'=>''
 *  1.0.6
 *      - aded sending subscription email in a preferred language
 *      - added placeholder for email address field
 *      - fixed bug on wrong lang in email for subscription/unsubscription
 *      - <font> replaced with <span>
 *      - added maxlength for textareas
 *  1.0.5
 *      - GetAllNews changed to static
 *      - added sending email on subscription
 *      - added time delay on repeated subscription
 *      - added maxlength for email fields
 *      - fixed issue with news text in details mode
 *	
 **/

class News extends MicroGrid {
	
	protected $debug = false;

	//---------------------------------
	private $sqlFieldDatetimeFormat = '';
	private $news_type = '';
	private $news_code = '';

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		parent::__construct();

		global $objSettings;
		
		$this->params = array();
		if(isset($_POST['news_code']))     $this->params['news_code']    = prepare_input($_POST['news_code']);
		if(isset($_POST['header_text']))   $this->params['header_text']  = prepare_input($_POST['header_text']);
		if(isset($_POST['body_text'])) 	   $this->params['body_text']    = prepare_input($_POST['body_text'], false, 'medium');
		if(isset($_POST['type']))   	   $this->params['type']         = prepare_input($_POST['type']);
		if(isset($_POST['date_created']))  $this->params['date_created'] = prepare_input($_POST['date_created']);
		$this->params['language_id'] 	   = isset($_POST['language_id']) ? prepare_input($_POST['language_id']) : MicroGrid::GetParameter('language_id');
		$this->params['is_active'] 		   = isset($_POST['is_active']) ? (int)$_POST['is_active'] : '0';
	
		$this->isHtmlEncoding = true;
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_NEWS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->formActionURL = 'index.php?admin=mod_news_management';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;		

		$this->allowLanguages = true;
		$this->WHERE_CLAUSE = 'WHERE language_id = \''.$this->languageId.'\'';		
		$this->ORDER_CLAUSE = 'ORDER BY date_created DESC';

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		// prepare activity array		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		// prepare languages array		
		$total_languages = Languages::GetAllActive();
		$arr_languages      = array();
		foreach($total_languages[0] as $key => $val){
			$arr_languages[$val['abbreviation']] = $val['lang_name'];
		}
		
		$arr_types = array('news'=>_NEWS, 'events'=>_EVENTS, 'last_minute'=>_LAST_MINUTE);

		$datetime_format = get_datetime_format();

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
			$this->sqlFieldDateFormat = '%b %d, %Y';
		}else{
			$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
			$this->sqlFieldDateFormat = '%d %b, %Y';
		}
		$this->SetLocale(Application::Get('lc_time_name'));

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									type,
									header_text,
									body_text,
									DATE_FORMAT('.$this->tableName.'.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as mod_date_created,
									CASE
										WHEN type = "events" THEN
											CONCAT("<a href=javascript:void(0) onclick=javascript:__mgDoPostBack(\''.$this->tableName.'\',\'details\',\'", '.$this->primaryKey.', "\')>events",
											       " (", (SELECT COUNT(*) as cnt FROM '.TABLE_EVENTS_REGISTERED.' er WHERE er.event_id = '.$this->tableName.'.'.$this->primaryKey.'), ")</a>")
										ELSE
                                            REPLACE(type, "_", " ")
									END as type_link,
									is_active
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'mod_date_created' => array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'left', 'width'=>'190px', 'format'=>'', 'format_parameter'=>''),
			'header_text'  	   => array('title'=>_HEADER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'nowrap'=>'wrap', 'maxlength'=>'90'),
			'type_link'    	   => array('title'=>_TYPE, 'type'=>'label', 'align'=>'center', 'width'=>'90px'),
			'is_active'        => array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'header_text'  => array('title'=>_HEADER, 	    'type'=>'textbox', 'required'=>true, 'width'=>'410px', 'maxlength'=>'255'),
			'body_text'    => array('title'=>_TEXT,   	    'type'=>'textarea', 'width'=>'490px', 'height'=>'200px', 'editor_type'=>'wysiwyg', 'readonly'=>false, 'default'=>'', 'required'=>true, 'validation_type'=>'', 'unique'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096'),
			'type'  	   => array('title'=>_TYPE,         'type'=>'enum', 'source'=>$arr_types, 'required'=>true, 'default'=>'news'),
			'date_created' => array('title'=>_DATE_CREATED, 'type'=>'datetime', 'required'=>true, 'readonly'=>false, 'default'=>@date('Y-m-d H:i:s'), 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'format'=>'', 'format_parameter'=>'', 'min_year'=>'10', 'max_year'=>'5'),
			'language_id'  => array('title'=>_LANGUAGE,     'type'=>'enum', 'source'=>$arr_languages, 'required'=>true),
			'news_code'    => array('title'=>'',            'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>get_random_string(10)),
			'is_active'    => array('title'=>_ACTIVE,       'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.type,
								'.$this->tableName.'.header_text,
								'.$this->tableName.'.body_text,
								'.$this->tableName.'.language_id,
								'.$this->tableName.'.date_created,
								DATE_FORMAT('.$this->tableName.'.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as mod_date_created,
								'.TABLE_LANGUAGES.'.lang_name as language_name,
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
								INNER JOIN '.TABLE_LANGUAGES.' ON '.$this->tableName.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'header_text'  => array('title'=>_HEADER, 	   'type'=>'textbox', 'required'=>true, 'width'=>'410px', 'maxlength'=>'255'),
			'body_text'    => array('title'=>_TEXT,   	   'type'=>'textarea', 'width'=>'490px', 'height'=>'200px', 'editor_type'=>'wysiwyg', 'readonly'=>false, 'default'=>'', 'required'=>true, 'validation_type'=>'', 'unique'=>false, 'maxlength'=>'4096', 'validation_maxlength'=>'4096'),
			'type'  	   => array('title'=>_TYPE,        'type'=>'enum', 'source'=>$arr_types, 'required'=>true),
			'date_created' => array('title'=>_DATE_CREATED,'type'=>'datetime', 'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'format'=>'', 'format_parameter'=>'', 'min_year'=>'10', 'max_year'=>'5'),
			'language_id'  => array('title'=>_LANGUAGE,    'type'=>'enum', 'source'=>$arr_languages, 'required'=>true, 'readonly'=>true),
			'is_active'    => array('title'=>_ACTIVE,      'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'header_text'   	=> array('title'=>_HEADER,   'type'=>'label'),
			'body_text'     	=> array('title'=>_TEXT,     'type'=>'html'),
			'type'          	=> array('title'=>_TYPE,     'type'=>'label'),
			'mod_date_created'  => array('title'=>_DATE_CREATED, 'type'=>'label'),
			'language_name' 	=> array('title'=>_LANGUAGE, 'type'=>'label'),
			'is_active'     	=> array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);

	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * After-operation Details mode
	 */
	function AfterDetailsMode()
	{
		$sql = 'SELECT
					er.first_name,
					er.last_name,
					er.email,
					er.phone,
					er.date_registered
				FROM '.TABLE_EVENTS_REGISTERED.' er
					INNER JOIN '.$this->tableName.' e ON er.event_id = e.'.$this->primaryKey.'
				WHERE
					e.type = "events" AND 
					er.event_id = '.(int)$this->curRecordId;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		
		if($result[1] > 0){
			echo '<table class="mgrid_table" border="0" cellspacing="0" cellpadding="2">';
			echo '<tr>';
			echo '<th align="left"><label></label></th>';
			echo '<th align="left"><label>'._FIRST_NAME.'</label></th>';
			echo '<th align="left"><label>'._LAST_NAME.'</label></th>';
			echo '<th align="left"><label>'._EMAIL_ADDRESS.'</label></th>';
			echo '<th align="left"><label>'._PHONE.'</label></th>';
			echo '<th align="left"><label>'._REGISTERED.'</label></th>';
			echo '</tr>';
			echo '<tr><td colspan="6" height="3px" nowrap="nowrap"><div class="no_margin_line"><img src="images/line_spacer.gif" width="100%" height="1px" alt="spacer" /></div></td></tr>';
	
			for($i=0; $i<$result[1]; $i++){
				echo '<tr>';
				echo '<td>'.($i+1).'.</td>';
				echo '<td>'.$result[0][$i]['first_name'].'</td>';
				echo '<td>'.$result[0][$i]['last_name'].'</td>';
				echo '<td>'.$result[0][$i]['email'].'</td>';
				echo '<td>'.$result[0][$i]['phone'].'</td>';
				echo '<td>'.format_datetime($result[0][$i]['date_registered']).'</td>';	
				echo '</tr>';
			}
			echo '</tr>';
			echo '</table>';			
		}		
	}

	/**
	 * After-deleting record
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.TABLE_EVENTS_REGISTERED.' WHERE event_id = '.(int)$this->curRecordId;
		database_void_query($sql);		
	}
	
	/**
	 * Sets system SQLs
	 * @param $key
	 * @param $msg
	 */	
	public function SetSQLs($key, $msg)
	{
		if($this->debug) $this->arrSQLs[$key] = $msg;					
	}
	
	/**
	 * Draws news block
	 * @param $draw
	 */	
	public function DrawNewsBlock($draw = true, $menu_ind = '')
	{	
	    $text_align_left = (Application::Get('lang_dir') == 'ltr') ? 'text-align:left;' : 'text-align:right;padding-right:15px;';
		$text_align_right = (Application::Get('lang_dir') == 'ltr') ? 'text-align:right;padding-right:15px;' : 'text-align:left;';

		$news_header_length = ModulesSettings::Get('news', 'news_header_length');
		$news_count = ModulesSettings::Get('news', 'news_count');

		$this->WHERE_CLAUSE = 'WHERE date_created < \''.@date('Y-m-d H:i:s').'\' AND language_id = \''.Application::Get('lang').'\' AND is_active = 1';		
		$all_news = $this->GetAll($this->ORDER_CLAUSE);
		$output = draw_block_top(_NEWS_AND_EVENTS, $menu_ind, 'maximized', 'hidden-xs', false);
		$output .= '<ul class="news-block">';
		$total_news = ($news_count < $all_news[1]) ? $news_count : $all_news[1];
		for($news_ind = 0; $news_ind < $total_news; $news_ind++){
			if($news_ind+1 > $news_count) break; // Show first X news
			$news_str = $all_news[0][$news_ind]['header_text']; // Display Y first chars
			$news_str = (strlen($news_str) > $news_header_length) ? substr($all_news[0][$news_ind]['header_text'],0,$news_header_length).'...' : $news_str;
			$output .= '<li>'.$news_str.'<br />';
			$output .= prepare_link('news', 'nid', $all_news[0][$news_ind]['id'], $news_str, '<i>'._READ_MORE.' &raquo;</i>', 'category-news');
			$output .= '</li>';
			
			if($total_news > 1 && ($news_ind == $total_news - 1)){
				$output .= prepare_link('news', '', '', '', _VIEW_ALL, 'category-news');
			}
		}
		if($news_ind == 0){
			$output .= '<li>'._NO_NEWS.'</li>';
		}
		$output .= '</ul>';
		$output .= draw_block_bottom(false);
		
		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 * Draws news
	 * @param $news_id
	 * @param $draw
	 */	
	public function DrawNews($news_id = 0, $draw = true)
	{
		global $objSession;
		$news = $this->GetNews($news_id);
		
		if($news[1] == 1){		
			$news_type = isset($news[0][0]['type']) ? $news[0][0]['type'] : 'news';
			$header_text = isset($news[0][0]['header_text']) ? str_replace("\'", "'", $news[0][0]['header_text']) : '';
			$body_text = isset($news[0][0]['body_text']) ? str_replace("\'", "'", $news[0][0]['body_text']) : '';
			$date_created = isset($news[0][0]['mod_date_created']) ? $news[0][0]['mod_date_created'] : '';
		
			if($news_type == 'events'){
				draw_title_bar(prepare_breadcrumbs(array(_EVENTS=>'',$header_text=>'')));
			}else{
				draw_title_bar(prepare_breadcrumbs(array(_NEWS=>'',$header_text=>'')));
			}
			
			///echo '<div class="center_box_heading_news">'.$header_text.'</div>';
			echo '<div class="center_box_contents_news">'.$body_text.'</div>';
			echo '<div class="center_box_bottom_news"><i><b>'._POSTED_ON.':</b>&nbsp;'.$date_created.'</i></div>';
			if($news_type == 'events'){
				$this->DrawRegistrationForm($news[0][0]['id'], $header_text);
			}
		}elseif($news[1] > 1){
		
			draw_title_bar(prepare_breadcrumbs(array(_NEWS=>'', _ALL=>'')));
		
			// -------- pagination
			$current_page = isset($_GET['p']) ? abs((int)$_GET['p']) : '1';
			$total_news = 0;
			$page_size  = 10;
			$news = $this->GetNewsCount();		
			$total_news = isset($news['cnt']) ? (int)$news['cnt'] : 0;
			$total_pages = (int)($total_news / $page_size);
			
			if($current_page > ($total_pages+1)) $current_page = 1;
			if(($total_news % $page_size) != 0) $total_pages++;
			if(!is_numeric($current_page) || (int)$current_page <= 0) $current_page = 1;
			if($total_pages == 1 && $current_page > 1) $current_page = 1;
			
			$start_row = ($current_page - 1) * $page_size;
			// --------
			$news = $this->GetNews($news_id, 'LIMIT '.$start_row.', '.$page_size);
			
			for($i=0; $i<$news[1]; $i++){		
				$news_type = isset($news[0][$i]['type']) ? $news[0][$i]['type'] : 'news';
				$header_text = isset($news[0][$i]['header_text']) ? str_replace("\'", "'", $news[0][$i]['header_text']) : '';
				$body_text = isset($news[0][$i]['body_text']) ? str_replace("\'", "'", $news[0][$i]['body_text']) : '';
				$date_created = isset($news[0][$i]['mod_date_created']) ? $news[0][$i]['mod_date_created'] : '';
		
				echo '<div class="center_box_heading_news">';
        		echo prepare_link('news', 'nid', $news[0][$i]['id'], $header_text, '<b>'.$header_text.'</b>');                
                echo '</div>';
				echo '<div class="center_box_contents_news">';
				// change size of image elements								
				$body_text = strip_tags($body_text, '<img>');
				$body_text = str_replace('<img style="', '<img style="height:90px;width:120px;', $body_text);				
				if(strlen($body_text) > 1000){
					echo substr($body_text, 0, 1000).'...';
        			echo '<br />'.prepare_link('news', 'nid', $news[0][$i]['id'], _READ_MORE, '<i>'._READ_MORE.' &raquo;</i>');
				}else{
					echo $body_text;				
				}
				echo '</div>';
				echo '<div class="center_box_bottom_news"><i><b>'._POSTED_ON.':</b>&nbsp;'.$date_created.'</i></div>';
				echo '<div class="center_box_bottom_news">';
				draw_line();
				echo '</div>';	
			}
			
			echo '<div style="padding:10px;">';	
			echo pagination_get_links($total_pages, 'index.php?page=news');
			echo '</div>';	
			
		}else{
			draw_title_bar(_NEWS); 	
			draw_important_message(_WRONG_PARAMETER_PASSED);
		}
	}	

	/**
	 * Process subscription
	 * @param $email
	 */	
	public function ProcessSubscription($email)
	{
		global $objSettings, $objLogin;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}						

		$newsletter_subscription = (bool)Session::Get('newsletter_subscription');
		$newsletter_subscription_time = Session::Get('newsletter_subscription_time');
		$delay_length = 20;

		if($email == ''){
			$this->error = _EMAIL_EMPTY_ALERT;
			return false;
		}elseif($email != '' && !check_email_address($email)){
			$this->error = _EMAIL_VALID_ALERT;
			return false;
		}else{			
			$time_elapsed = (time_diff($newsletter_subscription_time, date('Y-m-d H:i:s')));
			if($newsletter_subscription && $time_elapsed < $delay_length){
				$this->error = str_replace('_WAIT_', $delay_length - $time_elapsed, _SUBSCRIPTION_ALREADY_SENT);
				return false;
			}else{				
				// check if email already exists                    
				$sql = 'SELECT * FROM '.TABLE_NEWS_SUBSCRIBED.' WHERE email = \''.encode_text($email).'\'';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					$this->error = _SUBSCRIBE_EMAIL_EXISTS_ALERT;
					return false;
				}else{
					$sql = 'INSERT INTO '.TABLE_NEWS_SUBSCRIBED.' (id, email, date_subscribed)
							VALUES (NULL, \''.encode_text($email).'\', \''.@date('Y-m-d H:i:s').'\')';
					if(database_void_query($sql)){
						////////////////////////////////////////////////////////////
						send_email(
							$email,
							$objSettings->GetParameter('admin_email'),
							'subscription_to_newsletter',
							array(
								'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
								'{BASE URL}'   => APPHP_BASE,
								'{USER EMAIL}' => $email,
								'{YEAR}' 	   => date('Y')								
							),
							(($objLogin->IsLoggedIn()) ? $objLogin->GetPreferredLang() : '')
						);
						////////////////////////////////////////////////////////////
						Session::Set('newsletter_subscription', true);
						Session::Set('newsletter_subscription_time', date('Y-m-d H:i:s'));
					}else{					
						$this->error = _TRY_LATER;
					}
				}	
			}			
		}
		return true;		
	}
	
	/**
	 * Process unsubscription
	 * @param $email
	 */	
	public function ProcessUnsubscription($email)
	{
		global $objSettings, $objLogin;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}						

		if($email == ''){
			$this->error = _EMAIL_EMPTY_ALERT;
			return false;
		}elseif($email != '' && !check_email_address($email)){
			$this->error = _EMAIL_VALID_ALERT;
			return false;
		}else{
			// check if email already exists                    
			$sql = 'SELECT * FROM '.TABLE_NEWS_SUBSCRIBED.' WHERE email = \''.encode_text($email).'\'';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] <= 0){
				$this->error = _EMAIL_NOT_EXISTS;
				return false;
			}else{
				$sql = 'DELETE FROM '.TABLE_NEWS_SUBSCRIBED.' WHERE email = \''.encode_text($email).'\'';
				if(database_void_query($sql)){
 					////////////////////////////////////////////////////////////
					send_email(
						$email,
						$objSettings->GetParameter('admin_email'),
						'unsubscription_from_newsletter',
						array(
							'{WEB SITE}' => $_SERVER['SERVER_NAME'],
							'{BASE URL}' => APPHP_BASE,
						    '{USER EMAIL}' => $email,
							'{YEAR}' 	 => date('Y')
						),
						(($objLogin->IsLoggedIn()) ? $objLogin->GetPreferredLang() : '')
					);
					////////////////////////////////////////////////////////////
				}else{					
					$this->error = _TRY_LATER;
				}
			}	
		}
		return true;		
	}

	/**
	 * Draws newsletter main subscribe block
	 * @param $focus_field
	 * @param $email
	 * @param $draw
	 */	
	public function DrawSubscribeBlockMain($focus_field, $email = '', $draw = true)
	{
		$output  = '<form name="frmNewsletterSubscribeMain" class="newsletter_subscribe" action="index.php?page=newsletter" method="post">';
		$output .= draw_hidden_field('task', 'subscribe', false);
		$output .= draw_token_field(false);
		$output .= '<fieldset>';
		$output .= '<legend><b>'._SUBSCRIBE.'</b></legend>';
		$output .= _NEWSLETTER_SUBSCRIBE_TEXT.'<br />';
		$output .= _EMAIL_ADDRESS.':&nbsp;<input type="text" name="email" id="subscribe_email" value="'.(($focus_field == 'subscribe_email') ? $email : '').'" maxlength="70" autocomplete="off" /><br />';
		$output .= '<input class="form_button" type="submit" name="submit" value="'._SUBSCRIBE.'" />';
		$output .= '</fieldset>';
		$output .= '</form>';
		$output .= '<br/>';

		$output .= '<form name="frmNewsletterUnsubscribeMain" class="newsletter_subscribe" action="index.php?page=newsletter" method="post">';
		$output .= draw_hidden_field('task', 'unsubscribe', false);
		$output .= draw_token_field(false);
		$output .= '<fieldset>';
		$output .= '<legend><b>'._UNSUBSCRIBE.'</b></legend>';
		$output .= _NEWSLETTER_UNSUBSCRIBE_TEXT.'<br />';
		$output .= _EMAIL_ADDRESS.':&nbsp;<input type="text" name="email" id="unsubscribe_email" value="'.(($focus_field == 'unsubscribe_email') ? $email : '').'" maxlength="70" autocomplete="off" /><br />';
		$output .= '<input class="form_button" type="submit" name="submit" value="'._UNSUBSCRIBE.'" />';
		$output .= '</fieldset>';
		$output .= '</form>';
		$output .= '<script type="text/javascript">appSetFocus(\''.$focus_field.'\');</script>';
			
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws newsletter side subscribe block 
	 * @param $draw
	 */	
	public function DrawSubscribeBlock($draw = true)
	{
		$output = draw_block_top(_SUBSCRIBE_TO_NEWSLETTER, '', 'maximized', 'hidden-xs', false);
		$output .= '<form name="frmNewsletterSubscribeBlock" class="newsletter_subscribe" action="index.php?page=newsletter" method="post">';
		$output .= draw_hidden_field('task', 'subscribe', false);
		$output .= draw_token_field(false);
		$output .= '<input type="text" name="email" value="" maxlength="70" autocomplete="off" placeholder="'._EMAIL_ADDRESS.'" />';
		$output .= '<input class="form_button" type="submit" name="submit" value="'._SUBSCRIBE.'" />';		
		$output .= '</form>';
		$output .= draw_block_bottom(false);
			
		if($draw) echo $output;
		else return $output;		
	}
	
	/**
	 * Draws newsletter side subscribe block 
	 * @param $draw
	 */	
	public function DrawSubscribeBlockFooter($draw = true, $menu_ind = '')
	{
        $output = '<span class="ftitle">'._SUBSCRIBE_TO_NEWSLETTER.'</span>';
        $output .= '<div class="relative">';
		$output .= '<form name="frmNewsletterSubscribeBlock" class="newsletter_subscribe" action="index.php?page=newsletter" method="post">';
		$output .= draw_hidden_field('task', 'subscribe', false);
		$output .= draw_token_field(false);
        $output .= '<input type="email" class="form-control fccustom2" name="email" placeholder="'._EMAIL_ADDRESS.'">';
        $output .= '<button type="submit" class="btn btn-default btncustom">Submit<img src="'.APPHP_BASE.'templates/'.Application::Get('template').'/images/arrow.png" alt=""/></button>';
		$output .= '</form>';
		$output .= '</div>';
			
		if($draw) echo $output;
		else return $output;		
	}

	/**
	 * Returns certain news
	 * @param $news_id
	 * @param $limit_clause
	 */
	public function GetNews($news_id = '0', $limit_clause = '')
	{
		$sql = $this->VIEW_MODE_SQL.' ';
		if(!empty($news_id)) $sql .= 'WHERE is_active = 1 AND '.$this->primaryKey.' = '.(int)$news_id.' ';
		else $sql .= 'WHERE is_active = 1 AND language_id = \''.Application::Get('lang').'\' ';
		$sql .= $this->ORDER_CLAUSE.' ';
		if(!empty($limit_clause)) $sql .= $limit_clause;
		return database_query($sql, DATA_AND_ROWS);			
	}

	/**
	 *	Returns news count
	 */
	private function GetNewsCount()
	{
		$sql = 'SELECT COUNT(*) as cnt
				FROM '.TABLE_NEWS.'
				WHERE is_active = 1 AND language_id = \''.Application::Get('lang').'\' ';
		return database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);		
	}
	
	/**
	 * Returns all news
	 * @param $type
	 * @param $lang
	 */
	public static function GetAllNews($type = '', $lang = 'en')
	{
		$type_where_clause = ($type == 'previous') ? ' AND date_created <= \''.@date('Y-m-d H:i:s').'\'' : '';
		$sql = 'SELECT * FROM '.TABLE_NEWS.'
				WHERE is_active = 1 AND language_id = \''.$lang.'\' '.$type_where_clause.'
				ORDER BY date_created DESC';
		return database_query($sql, DATA_AND_ROWS);	
	}

	/**
	 * Draws registration form
	 * @param $news_id
	 * @param $event_title
	 * @param $draw
	 */
	public function DrawRegistrationForm($news_id = '0', $event_title = '', $draw = true)
	{
		if(!$news_id) return '';
		
		global $objSettings, $objLogin;
		
		$lang = Application::Get('lang');		
		$focus_element = 'first_name';

		// post fields
		$task             = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
		$event_id		  = isset($_POST['event_id']) ? (int)$_POST['event_id'] : '0';
		$first_name       = isset($_POST['first_name']) ? prepare_input($_POST['first_name']) : '';
		$last_name        = isset($_POST['last_name']) ? prepare_input($_POST['last_name']) : '';
		$email            = isset($_POST['email']) ? prepare_input($_POST['email']) : '';
		$phone            = isset($_POST['phone']) ? prepare_input($_POST['phone']) : '';
		$message          = isset($_POST['message']) ? substr(prepare_input($_POST['message']), 0, 2048) : '';
		$captcha_code 	  = isset($_POST['captcha_code']) ? prepare_input($_POST['captcha_code']) : '';
		$admin_email	  = $objSettings->GetParameter('admin_email');
		$msg              = '';

		if($task == 'register_to_event'){
			include_once('modules/captcha/securimage.php');
			$objImg = new Securimage();			
		
			if($first_name == ''){
				$msg = draw_important_message(_FIRST_NAME_EMPTY_ALERT, false);
				$focus_element = 'first_name';
			}elseif($last_name == ''){
				$msg = draw_important_message(_LAST_NAME_EMPTY_ALERT, false);
				$focus_element = 'last_name';
			}elseif($email == ''){
				$msg = draw_important_message(_EMAIL_EMPTY_ALERT, false);
				$focus_element = 'email';
			}elseif(($email != '') && (!check_email_address($email))){
				$msg = draw_important_message(_EMAIL_VALID_ALERT, false);
				$focus_element = 'email';
			}elseif($phone == ''){        
				$msg = draw_important_message(str_replace('_FIELD_', _PHONE, _FIELD_CANNOT_BE_EMPTY), false);
				$focus_element = 'phone';
			}elseif(!$objImg->check($captcha_code)){
				$msg = draw_important_message(_WRONG_CODE_ALERT, false);
				$focus_element = 'captcha_code';
			}else{
				$sql = 'SELECT * FROM '.TABLE_EVENTS_REGISTERED.' WHERE event_id = \''.(int)$event_id.'\' AND email = \''.$email.'\'';
				if(database_query($sql, ROWS_ONLY, FIRST_ROW_ONLY) > 0){
					$msg = draw_important_message(_EVENT_USER_ALREADY_REGISTERED, false);
				}				
			}
			
			// deny all operations in demo version
			if(strtolower(SITE_MODE) == 'demo'){
				$msg = draw_important_message(_OPERATION_BLOCKED, false);
			}						

			if($msg == ''){
				if($objLogin->IpAddressBlocked(get_current_ip())){
					$msg = draw_important_message(_IP_ADDRESS_BLOCKED, false);
				}elseif($objLogin->EmailBlocked($email)){
					$msg = draw_important_message(_EMAIL_BLOCKED, false);
				}else{
					$sql = 'INSERT INTO '.TABLE_EVENTS_REGISTERED.' (id, event_id, first_name, last_name, email, phone, message, date_registered)
							VALUES (NULL, '.(int)$event_id.', \''.encode_text($first_name).'\', \''.encode_text($last_name).'\', \''.encode_text($email).'\', \''.encode_text($phone).'\', \''.encode_text($message).'\', \''.@date('Y-m-d H:i:s').'\')';
					if(database_void_query($sql)){
						$msg = draw_success_message(_EVENT_REGISTRATION_COMPLETED, false);
	
						////////////////////////////////////////////////////////////
						send_email(
							$email,
							$admin_email,
							'events_new_registration',
							array(
								'{FIRST NAME}' => $first_name,
								'{LAST NAME}'  => $last_name,
								'{EVENT}'      => '<b>'.$event_title.'</b>'
							),
							'',
							$admin_email,
							_EVENTS_NEW_USER_REGISTERED.' ('._ADMIN_COPY.')'
						);
						////////////////////////////////////////////////////////////		
	
						$first_name = $last_name = $email = $phone = $message = '';
					}else{
						$msg = draw_important_message(_TRY_LATER, false);
					}					
				}
			}
		}

		$output = '
		'.(($msg != '') ? $msg : '').'<br />
		<fieldset style="border:1px solid #cccccc;padding-left:10px;margin:0px 12px 12px 12px;">
		<legend><b>'._REGISTRATION_FORM.'</b></legend>
		<form method="post" name="frmEventRegistration" id="frmEventRegistration">
			'.draw_hidden_field('task', 'register_to_event', false).'
			'.draw_hidden_field('event_id', $news_id, false).'
			'.draw_token_field(false);
		
		$output .= '
			<table cellspacing="1" cellpadding="2" border="0" width="100%">
			<tbody>
			<tr>
				<td width="25%" align="'.Application::Get('defined_right').'">'._FIRST_NAME.':</td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap" align="'.Application::Get('defined_left').'"><input type="text" id="first_name" name="first_name" size="34" maxlength="32" value="'.decode_text($first_name).'" autocomplete="off" /></td>
			</tr>
			<tr>
				<td align="'.Application::Get('defined_right').'">'._LAST_NAME.':</td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap" align="'.Application::Get('defined_left').'"><input type="text" id="last_name" name="last_name" size="34" maxlength="32" value="'.decode_text($last_name).'" autocomplete="off" /></td>
			</tr>
			<tr>
				<td align="'.Application::Get('defined_right').'">'._EMAIL_ADDRESS.':</td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap" align="'.Application::Get('defined_left').'"><input type="text" id="email" name="email" size="34" maxlength="70" value="'.decode_text($email).'" autocomplete="off" /></td>
			</tr>
			<tr>
				<td align="'.Application::Get('defined_right').'">'._PHONE.':</td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap" align="'.Application::Get('defined_left').'"><input type="text" id="phone" name="phone" size="22" maxlength="32" value="'.decode_text($phone).'" autocomplete="off" /></td>
			</tr>
		    <tr valign="top">
                <td align="'.Application::Get('defined_right').'">'._MESSAGE.':</td>
                <td></td>
                <td nowrap="nowrap" align="'.Application::Get('defined_left').'">
                    <textarea id="message" name="message" style="width:390px;" rows="4" maxlength="2048">'.$message.'</textarea>                
                </td>
		    </tr>
			<tr>
				<td colspan="2"></td>
				<td colspan="2">';				
					
					$output .= '<table border="0" cellspacing="2" cellpadding="2">
					<tr>
						<td>
							<img id="captcha_image" src="modules/captcha/securimage_show.php?sid='.md5(uniqid(time())).'" />
						</td>	
						<td>
							<img style="cursor:pointer; padding:0px; margin:0px;" id="captcha_image_reload" src="modules/captcha/images/refresh.gif" style="cursor:pointer;" onclick="document.getElementById(\'captcha_image\').src = \'modules/captcha/securimage_show.php?sid=\' + Math.random(); appSetFocus(\'captcha_code\'); return false" title="'._REFRESH.'" alt="'._REFRESH.'" /><br />
							<a href="modules/captcha/securimage_play.php"><img border="0" style="padding:0px; margin:0px;" id="captcha_image_play" src="modules/captcha/images/audio_icon.gif" title="'._PLAY.'" alt="'._PLAY.'" /></a>						
						</td>					
						<td>
							'._TYPE_CHARS.'<br />								
							<input type="text" name="captcha_code" id="captcha_code" style="width:175px;margin-top:5px;" value="" maxlength="20" autocomplete="off" />
						</td>
					</tr>
					</table>';

				$output .= '</td>
			</tr>
			<tr><td height="20" colspan="3">&nbsp;</td></tr>            
			<tr>
				<td colspan="3" align="center">
				<input type="submit" class="form_button" name="btnSubmitPD" id="btnSubmitPD" value=" '._SEND.' ">
				</td>
			</tr>
			<tr><td colspan="3">&nbsp;</td></tr>		    		    
			</table>
			</form>
			
		</form>
		</fieldset>';
		
		if($task != '' && $focus_element != '') $output .= '<script type="text/javascript">appSetFocus(\''.$focus_element.'\');</script>';
	
		if($draw) echo $output;		
		else return $output;
	}

	/**
	 * After-insertion operation
	 */
	public function AfterInsertRecord()
	{	    
		// --- clone to other languages
		$total_languages = Languages::GetAllActive();
		$language_id  = self::GetParameter('language_id', false);
		$news_code 	  = self::GetParameter('news_code', false);
		$header_text  = self::GetParameter('header_text', false);
		$body_text 	  = self::GetParameter('body_text', false);
		$date_created = self::GetParameter('date_created', false);
		$type  	      = self::GetParameter('type', false);
		
		for($i = 0; $i < $total_languages[1]; $i++){
			if($language_id != '' && $total_languages[0][$i]['abbreviation'] != $language_id){
				$sql = 'INSERT INTO '.TABLE_NEWS.' (id, news_code, header_text, body_text, type, date_created, language_id)
						VALUES(NULL, \''.encode_text($news_code).'\', \''.encode_text($header_text).'\', \''.encode_text($body_text).'\', \''.$type.'\', \''.encode_text($date_created).'\', \''.encode_text($total_languages[0][$i]['abbreviation']).'\')';
				database_void_query($sql);
				$this->SetSQLs('insert_lan_'.$total_languages[0][$i]['abbreviation'], $sql);
			}								
		}	
	}

	/**
	 * Before-updating operation
	 */
	public function BeforeUpdateRecord()
	{
		// $this->curRecordId - currently updated record
		$sql = 'SELECT type, news_code FROM '.TABLE_NEWS.' WHERE id = '.(int)$this->curRecordId;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		if(!empty($result)){
			$this->news_type = $result['type'];
			$this->news_code = $result['news_code'];			
		}		
	   	return true;
	}

	/**
	 * After-updating operation
	 */
	public function AfterUpdateRecord()
	{
		// $this->curRecordId - currently updated record
	    // $this->params - current record update info
		if($this->news_type != '' && $this->news_type != $this->params['type']){
			$sql = 'UPDATE '.TABLE_NEWS.' SET type = \''.$this->params['type'].'\' WHERE news_code = \''.$this->news_code.'\'';
			database_void_query($sql);			
		}
	}
	

	/**
	 * Return news id for specific language
	 * @param $nid
	 * @param $lang
	 */
	public static function GetNewsId($nid = '', $lang = '')
	{
		if($nid != '' && $lang != ''){
			$sql = 'SELECT id
					FROM '.TABLE_NEWS.'
					WHERE language_id = \''.$lang.'\' AND 
						  news_code = (SELECT news_code FROM '.TABLE_NEWS.' WHERE id = '.(int)$nid.')';
			$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			return isset($result['id']) ? $result['id'] : '';			
		}else{
			return '';	
		}		
	}	
	
	/**
	 * Return news info for specific language
	 * @param $nid
	 * @param $lang
	 */
	public static function GetNewsInfo($nid = '', $lang = '')
	{
		if($nid != '' && $lang != ''){
			$sql = 'SELECT *
					FROM '.TABLE_NEWS.'
					WHERE language_id = \''.$lang.'\' AND 
						  news_code = (SELECT news_code FROM '.TABLE_NEWS.' WHERE id = '.(int)$nid.')';
			return database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		}else{
			return '';	
		}		
	}
	
	/**
	 * Checks if the page with news may be cached
	 * @param $news_id
	 */
	public static function CacheAllowed($news_id)
	{
		$sql = 'SELECT id
				FROM '.TABLE_NEWS.'
				WHERE type = \'news\' AND id = '.(int)$news_id;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){			
			return true;		
		}
		return false;	
	}
	
	/**
	 * Draws news block last minute
	 * @param $draw
	 */	
	public static function DrawLastMinuteNews($draw = true)
	{	
        $output = '';
    
		$sql = 'SELECT *
				FROM '.TABLE_NEWS.'
				WHERE type = \'last_minute\' AND language_id = \''.Application::Get('lang').'\' AND is_active = 1
                ORDER BY date_created DESC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
            $output .= '<b>'._NEWS.'</b>: ';
            $output .= $result[0]['header_text'].'<br>';
            $output .= '<a class="btn iosbtn" href="index.php?page=news&nid='.$result[0]['id'].'">'._READ_MORE.'</a>';
		}

		if($draw) echo $output;
		else return $output;
	}    
	
}
