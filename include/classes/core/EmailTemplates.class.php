<?php

/**
 *	EmailTemplates
 *  -------------- 
 *  Description : encapsulates email templates properties
 *	Written by  : ApPHP
 *	Version     : 1.0.9
 *  Updated	    : 27.07.2016
 *  Usage       : Core Class
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				PrepareMessageFooter	GetAllTemplates
 *	__destruct										
 *	GetTemplate
 *	SendMassMail
 *	DrawMassMailForm
 *	AfterInsertRecord
 *	BeforeDeleteRecord
 *
 *  1.0.9
 *  	- 
 *  	-
 *  	-
 *  	-
 *  	-
 *  1.0.8
 *      - added possibility to send mass mail to suppliers class
 *      - added _ADMIN_COPY
 *      - added MailLogs
 *      - added Filtering section
 *      - added sending emails by template (if selected) and preferred language for members
 *  1.0.7
 *      - added switch for WYSIWYg editor
 *      - replaced <font> with <span>
 *      - added maxlength for textareas
 *      - added enum instead of label for is_system_template in Edit Mode
 *      - replaced mysq l_real_escape_string() with encode_text()
 *  1.0.6
 *      - added maxlength in View Mode
 *      - added MedicalAppointment
 *      - added replacing {USER EMAIL} with valid value
 *      - for Hotelsite clients changes with customers
 *      - fixed error on missing 'cnt' is SQL
 *  1.0.5
 *      - added swith by $PROJECT
 *      - added draw param in DrawMassMailForm()
 *      - changed SendEmail function
 *      - optimized SELECT SQLs
 *      - added sending emails to Newsletter Subscribers
 *  1.0.4
 *      - notification_updates renamed into email_notifications
 *      - fixed bug in sending emails to clients groups
 *      - added automatically cloning to other languages on insertion
 *      - changed using of session with object Session
 *      - added {USER EMAIL} holder
 **/


class EmailTemplates extends MicroGrid {
	
	protected $debug = false;
	
	//----------------------------------
	private $MEMBERS_TABLE_NAME = '';
	private $MEMBERS_MODULE_NAME = '';
	private $MEMBERS_NAME = '';
	private $SUPPLIERS_TABLE_NAME = '';
	private $SUPPLIERS_MODULE_NAME = '';
	private $SUPPLIERS_NAME = '';
	private $ADMINS_AND_OTHERS_NAME = '';

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();

		$this->params = array();
		global $objSettings;
		
		## for standard fields
		if(isset($_POST['template_code'])) $this->params['template_code'] = prepare_input($_POST['template_code']);
		if(isset($_POST['template_name'])) $this->params['template_name'] = prepare_input($_POST['template_name']);
		if(isset($_POST['template_subject'])) $this->params['template_subject'] = prepare_input($_POST['template_subject']);
		if(isset($_POST['template_content'])) $this->params['template_content'] = prepare_input($_POST['template_content'], false, 'medium');
		
		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey = 'id';
		$this->tableName  = TABLE_EMAIL_TEMPLATES;
		$this->MEMBERS_TABLE_NAME = TABLE_CUSTOMERS;
		$this->MEMBERS_MODULE_NAME = 'customers';
		$this->MEMBERS_NAME = _CUSTOMERS;
		$this->ADMINS_AND_OTHERS_NAME = _ADMINS_AND_CUSTOMERS;

		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=email_templates';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = true;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.template_code ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 25;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;
		$this->arrFilteringFields = array(
			_SUBJECT  	=> array('table'=>'', 'field'=>'template_subject', 'type'=>'text', 'sign'=>'%like%', 'width'=>'130px', 'visible'=>true),
			_TEXT  		=> array('table'=>'', 'field'=>'template_content', 'type'=>'text', 'sign'=>'%like%', 'width'=>'130px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
			// 'Caption_3'  => array('table'=>'', 'field'=>'', 'type'=>'calendar', 'date_format'=>'dd/mm/yyyy|mm/dd/yyyy|yyyy/mm/dd', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
		);

		$arr_is_system = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		
		// prepare languages array		
		$total_languages = Languages::GetAllActive();
		$arr_languages   = array();
		foreach($total_languages[0] as $key => $val){
			$arr_languages[$val['abbreviation']] = $val['lang_name'];
		}
		
		$wysiwyg_type = ($objSettings->GetParameter('mailer_wysiwyg_type') == 'tinymce') ? 'wysiwyg' : 'simple';

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									language_id,
									template_code,
									template_name,
									template_subject,
									template_content,
									is_system_template
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'template_subject'   => array('title'=>_SUBJECT, 'type'=>'label', 'align'=>'left', 'width'=>'30%', 'sortable'=>true, 'nowrap'=>'wrap', 'visible'=>'', 'height'=>'', 'maxlength'=>'50', 'format'=>''),
			'template_name'      => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'wrap', 'visible'=>'', 'height'=>'', 'maxlength'=>'65', 'format'=>''),
			'is_system_template' => array('title'=>_SYSTEM, 'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_system),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255 ....
		//   Ex.: 'validation_maxlength'=>'255'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'language_id'      => array('title'=>_LANGUAGE, 'type'=>'enum',     'required'=>true, 'readonly'=>true, 'width'=>'210px', 'source'=>$arr_languages, 'unique'=>false),
			'template_code'    => array('title'=>_TEMPLATE_CODE, 'type'=>'textbox',  'width'=>'350px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'40', 'default'=>'', 'validation_type'=>'alpha_numeric', 'unique'=>true),
			'template_name'    => array('title'=>_DESCRIPTION, 'type'=>'textbox',  'width'=>'350px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'100', 'default'=>'', 'validation_type'=>'', 'unique'=>true),
			'template_subject' => array('title'=>_SUBJECT, 'type'=>'textbox',  'width'=>'510px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'125', 'default'=>'', 'validation_type'=>'', 'unique'=>false),
			'template_content' => array('title'=>_TEXT, 'type'=>'textarea', 'width'=>'530px', 'height'=>'290px', 'required'=>true, 'editor_type'=>$wysiwyg_type, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false),
			'is_system_template' => array('title'=>'', 'type'=>'hidden',   'required'=>true, 'readonly'=>false, 'default'=>'0'),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255 ....
		//   Ex.: 'validation_maxlength'=>'255'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.language_id,
								'.$this->tableName.'.template_name,
								'.$this->tableName.'.template_code,
								'.$this->tableName.'.template_subject,
								'.$this->tableName.'.template_content,
								'.$this->tableName.'.is_system_template
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'language_id'      => array('title'=>_LANGUAGE, 'type'=>'enum',  'required'=>true, 'readonly'=>true, 'width'=>'210px', 'source'=>$arr_languages, 'unique'=>false),
			'template_code'    => array('title'=>_TEMPLATE_CODE, 'type'=>'label'),
			'template_name'    => array('title'=>_DESCRIPTION, 'type'=>'textbox',  'width'=>'350px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'100', 'default'=>'', 'validation_type'=>'', 'unique'=>false),
			'template_subject' => array('title'=>_SUBJECT, 'type'=>'textbox',  'width'=>'510px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'125', 'default'=>'', 'validation_type'=>'', 'unique'=>false),
			'template_content' => array('title'=>_TEXT, 'type'=>'textarea', 'width'=>'530px', 'height'=>'300px', 'required'=>true, 'editor_type'=>$wysiwyg_type, 'maxlength'=>'4096', 'validation_maxlength'=>'4096', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false),
			'is_system_template' => array('title'=>_SYSTEM_TEMPLATE, 'type'=>'enum', 'readonly'=>true, 'source'=>$arr_is_system),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'template_name'    => array('title'=>_DESCRIPTION, 'type'=>'label'),
			'template_subject' => array('title'=>_SUBJECT, 'type'=>'label'),
			'template_content' => array('title'=>_TEXT, 'type'=>'label', 'format'=>'readonly_text'),
			'is_system_template' => array('title'=>_SYSTEM_TEMPLATE, 'type'=>'enum', 'source'=>$arr_is_system),
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
	 * Returns email template
	 * @param $template_code
	 * @param $language_id
	 */
	public function GetTemplate($template_code = '', $language_id = '')
	{
		$output = array('template_subject'=>'', 'template_content'=>'', 'template_name'=>'');
		$sql = 'SELECT
					language_id,
					template_code,
					template_name,
					template_subject,
					template_content
				FROM '.$this->tableName.'
				WHERE
					template_code = \''.encode_text($template_code).'\' AND
					language_id = \''.$language_id.'\'';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output['template_subject'] = $result[0]['template_subject'];
			$output['template_content'] = $result[0]['template_content'];
			$output['template_name'] = $result[0]['template_name'];
		}
		return $output;
	}

	/**
	 * Sends mass mail	 
	 */
	public function SendMassMail()
	{
		global $objSettings, $objLogin;

		$template_name 	= isset($_POST['template_name']) ? prepare_input($_POST['template_name']) : '';
		$email_from 	= isset($_POST['email_from']) ? prepare_input($_POST['email_from']) : '';
		$email_to_req	= isset($_POST['email_to']) ? prepare_input($_POST['email_to']) : '';
		$subject 		= isset($_POST['subject']) ? prepare_input($_POST['subject']) : '';
		$message 		= isset($_POST['message']) ? prepare_input($_POST['message']) : '';
		$package_size 	= isset($_POST['package_size']) ? prepare_input($_POST['package_size']) : '';		
		$duration 		= isset($_POST['duration']) ? (int)$_POST['duration'] : '5';
		$send_copy_to_admin = isset($_POST['send_copy_to_admin']) ? prepare_input($_POST['send_copy_to_admin']) : '';
		$admin_email 	= $objSettings->GetParameter('admin_email');
		$email_session_code = Session::Get('email_random_code');
		$email_post_code = isset($_POST['email_random_code']) ? prepare_input($_POST['email_random_code']) : '';
		$msg = '';
		$emails_total = '0';
		$emails_sent = '0';

		if(strtolower(SITE_MODE) == 'demo'){
			draw_important_message(_OPERATION_BLOCKED);
			return false;
		}

		if($email_post_code != '' && $email_session_code == $email_post_code){
			$this->error = true;
			draw_message(_OPERATION_WAS_ALREADY_COMPLETED);								
			return false;
		}			

		// handle emails sending
		if($subject != '' && $message != ''){
			$message = str_ireplace('{YEAR}', date('Y'), $message);
			$message = str_ireplace('{WEB SITE}', $_SERVER['SERVER_NAME'], $message);
			$message = str_ireplace('{BASE URL}', APPHP_BASE, $message);

			$message = str_ireplace('{MESSAGE FOOTER}', self::PrepareMessageFooter(), $message);

			$email_to_parts = explode('|', $email_to_req);
			$email_to = isset($email_to_parts[0]) ? $email_to_parts[0] : '';
			$email_to_subtype = isset($email_to_parts[1]) ? $email_to_parts[1] : '';
			if($email_to_subtype == 'all'){
				$member_where_clause = '';
			}elseif($email_to_subtype == 'uncategorized'){
				$member_where_clause = 'group_id=0 AND';
			}elseif($email_to_subtype != ''){
				$member_where_clause = 'group_id='.$email_to_subtype.' AND';
			}else{
				$member_where_clause = '';
			}
			
			if($email_to == 'test'){
				$emails_total = '1';
				if(send_email_wo_template($admin_email, $admin_email, $subject, $message)) $emails_sent = '1';
			}else{
				$result = database_query('SELECT COUNT(*) as cnt FROM '.$this->MEMBERS_TABLE_NAME.' WHERE is_active = 1 AND '.$member_where_clause.' email_notifications = 1 AND email != \'\'', DATA_ONLY, FIRST_ROW_ONLY);
				$members_emails_total = $result['cnt'];
				$suppliers_emails_total = 0;
				if($this->SUPPLIERS_TABLE_NAME != ''){
					$result = database_query('SELECT COUNT(*) as cnt FROM '.$this->SUPPLIERS_TABLE_NAME.' WHERE is_active = 1 AND email_notifications = 1 AND email != \'\'', DATA_ONLY, FIRST_ROW_ONLY);
					$suppliers_emails_total = $result['cnt'];
				}
				$result = database_query('SELECT COUNT(*) as cnt FROM '.TABLE_ACCOUNTS.' WHERE is_active = 1 AND email != \'\'', DATA_ONLY, FIRST_ROW_ONLY);
				$admins_emails_total = $result['cnt'];
				$result = database_query('SELECT COUNT(*) as cnt FROM '.TABLE_NEWS_SUBSCRIBED.' WHERE email != \'\'', DATA_ONLY, FIRST_ROW_ONLY);
				$newsletter_email_total = $result['cnt'];
				
				if($email_to == 'members'){
					$emails_total = $members_emails_total;
				}elseif($email_to == 'suppliers'){
					$emails_total = $suppliers_emails_total;
				}elseif($email_to == 'admins'){
					$emails_total = $admins_emails_total;
				}elseif($email_to == 'all'){
					$emails_total = $members_emails_total + $suppliers_emails_total + $admins_emails_total;
				}elseif($email_to == 'newsletter_subscribers'){
					$emails_total = $newsletter_email_total;
				}

				if($email_to == 'members' || $email_to == 'suppliers' || $email_to == 'all'){
                    
                    if($email_to == 'members' || $email_to == 'all'){
                        $sql = 'SELECT id, first_name, last_name, email, user_name, preferred_language
                                FROM '.$this->MEMBERS_TABLE_NAME.'
                                WHERE is_active = 1 AND '.$member_where_clause.' email_notifications = 1 AND email != \'\'
                                ORDER BY id ASC';
                        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
                        for($i=0; $i < $result[1]; $i++){
							$replace_holders = array(
								'{FIRST NAME}' => $result[0][$i]['first_name'],
								'{LAST NAME}' => $result[0][$i]['last_name'],
								'{USER NAM}' => $result[0][$i]['user_name'],
								'{USER EMAIL}' => $result[0][$i]['email']													 
							);
							$lang = !empty($result[0][$i]['preferred_language']) ? $result[0][$i]['preferred_language'] : '';
							
							if(!empty($template_name)){
								if(send_email($result[0][$i]['email'], $admin_email, $template_name, $replace_holders, $lang, '', '')) $emails_sent++;
							}else{
								$body_middle = $message;
								foreach($replace_holders as $key => $replace_holder){
									$body_middle = str_ireplace($key, $replace_holder, $body_middle);
								}
								if(send_email_wo_template($result[0][$i]['email'], $admin_email, $subject, $body_middle, $lang)) $emails_sent++;
							}
                        }
                    }
					
					if($this->SUPPLIERS_TABLE_NAME != ''){
                        if($email_to == 'suppliers' || $email_to == 'all'){
                            $sql = 'SELECT * 
                                    FROM '.$this->SUPPLIERS_TABLE_NAME.'
                                    WHERE is_active = 1 AND email_notifications = 1 AND email != \'\'
                                    ORDER BY id ASC';
                            $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
                            for($i=0; $i < $result[1]; $i++){
                                $body_middle = str_ireplace('{FIRST NAME}', $result[0][$i]['first_name'], $message);
                                if(isset($result[0][$i]['middle_name'])) $body_middle = str_ireplace('{MIDDLE NAME}', $result[0][$i]['middle_name'], $message);
                                $body_middle = str_ireplace('{LAST NAME}', $result[0][$i]['last_name'], $body_middle);
                                $body_middle = str_ireplace('{USER NAME}', $result[0][$i]['user_name'], $body_middle);
                                $body_middle = str_ireplace('{USER EMAIL}', $result[0][$i]['email'], $body_middle);
                                if(send_email_wo_template($result[0][$i]['email'], $admin_email, $subject, $body_middle)) $emails_sent++;
                            }
                        }
					}
				}
				
				if($email_to == 'admins' || $email_to == 'all'){					
					$sql = "SELECT id, first_name, last_name, email, user_name  
							FROM ".TABLE_ACCOUNTS."
							WHERE is_active = 1 AND email != ''
							ORDER BY id ASC";
					$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
					for($i=0; $i < $result[1]; $i++){
						$body_middle = str_ireplace('{FIRST NAME}', $result[0][$i]['first_name'], $message);
						$body_middle = str_ireplace('{LAST NAME}', $result[0][$i]['last_name'], $body_middle);
						$body_middle = str_ireplace('{USER NAME}', $result[0][$i]['user_name'], $body_middle);
						$body_middle = str_ireplace('{USER EMAIL}', $result[0][$i]['email'], $body_middle);
						if(send_email_wo_template($result[0][$i]['email'], $admin_email, $subject, $body_middle)) $emails_sent++;
					}
				}
				
				if($email_to == 'newsletter_subscribers'){
					$sql = "SELECT email FROM ".TABLE_NEWS_SUBSCRIBED." WHERE email != '' ORDER BY id ASC";
					$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
					for($i=0; $i < $result[1]; $i++){
						$body_middle = $message;
						if(send_email_wo_template($result[0][$i]['email'], $admin_email, $subject, $body_middle)) $emails_sent++;
					}					
				}
				
				if($send_copy_to_admin == '1'){
					send_email_wo_template($admin_email, $admin_email, $subject.' ('._ADMIN_COPY.')', $message);
				}									
			}
		
			if($emails_sent){
				Session::Set('email_random_code', $email_post_code);
				$msg = str_replace('_SENT_', $emails_sent, _EMAILS_SUCCESSFULLY_SENT);
				$msg = str_replace('_TOTAL_', $emails_total, $msg);
				$this->error = false;
				draw_success_message($msg);
				$emails_status = 1;
				$status_description = '';
			}else{
				$this->error = true;
				draw_important_message(_EMAILS_SENT_ERROR);
				$emails_status = 0;
				$status_description = '';
				if(version_compare(PHP_VERSION, '5.2.0', '>=')){
					$err = error_get_last();
					$status_description = (isset($err['message']) ? $err['message'] : '');
				}		
			}			

			if(SAVE_MAIL_LOG == 'mass_only'){
				MailLogs::MailLogAddRecord($objLogin->GetLoggedID(), $email_to, $template_name, $subject, $message, $emails_sent, $emails_total, $emails_status, $status_description);
			}			
		}else{
			draw_important_message(_EMAIL_FIELDS_EMPTY_ALERT);			
		}		
	}

	/**
	 *	Draws mass mail form
	 *		$param $draw
	 */
	public function DrawMassMailForm($draw = true)
	{
		global $objSettings; 

		$template_subject = '';
		$template_content = '';
		// for members
		$result = database_query('SELECT COUNT(*) as cnt FROM '.$this->MEMBERS_TABLE_NAME.' WHERE is_active = 1 AND email_notifications = 1 AND email != \'\'', DATA_ONLY, FIRST_ROW_ONLY);
		$members_emails_count = isset($result['cnt']) ? $result['cnt'] : 0;
		// for suppliers
		$suppliers_emails_count = 0;
		if($this->SUPPLIERS_TABLE_NAME != ''){
			$result = database_query('SELECT COUNT(*) as cnt FROM '.$this->SUPPLIERS_TABLE_NAME.' WHERE is_active = 1 AND email_notifications = 1 AND email != \'\'', DATA_ONLY, FIRST_ROW_ONLY);
			$suppliers_emails_count = isset($result['cnt']) ? $result['cnt'] : 0;
		}
		// for admins
		$result = database_query('SELECT COUNT(*) as cnt FROM '.TABLE_ACCOUNTS.' WHERE is_active = 1 AND email != \'\'', DATA_ONLY, FIRST_ROW_ONLY);
		$admins_emails_count = isset($result['cnt']) ? $result['cnt'] : 0;
		
		$emails_count = $members_emails_count + $suppliers_emails_count + $admins_emails_count;
		
		$result = database_query('SELECT COUNT(*) as cnt FROM '.TABLE_NEWS_SUBSCRIBED.' WHERE email != \'\'', DATA_ONLY, FIRST_ROW_ONLY);
		$newsletter_subscribers_count = isset($result['cnt']) ? $result['cnt'] : 0;
		$send_copy_to_admin = '1';

		$email_from = $objSettings->GetParameter('admin_email');
		
		$template_code = isset($_GET['template_code']) ? prepare_input($_GET['template_code']) : '';
		$duration = isset($_POST['duration']) ? (int)$_POST['duration'] : '5';
		
		$members_module_installed = Modules::IsModuleInstalled($this->MEMBERS_MODULE_NAME);
		$suppliers_module_installed = Modules::IsModuleInstalled($this->SUPPLIERS_MODULE_NAME);
		
		// load appropriate email template
		if($template_code != ''){
			$template = $this->GetTemplate($template_code, Application::Get('lang'));
			$template_subject = $template['template_subject'];
			$template_content = $template['template_content'];
		}
		
		if($this->error == true){
			$template_code    = isset($_POST['template_name']) ? prepare_input($_POST['template_name']) : '';	
			$template_subject = isset($_POST['subject']) ? prepare_input($_POST['subject']) : '';
			$template_content = isset($_POST['message']) ? prepare_input($_POST['message']) : '';			
		}
		
		$output ='<script type="text/javascript">
			function duration_OnChange(val){
				var el_package_size = (document.getElementById(\'package_size\')) ? document.getElementById(\'package_size\') : null;
				if(val == \'\' && el_package_size){
					el_package_size.selectedIndex = 0;
					el_package_size.disabled = \'disabled\';
				}else{
					el_package_size.disabled = \'\';
				}
			}
			
			function email_to_OnChange(val){
				var el_send_copy_to_admin = (document.getElementById(\'send_copy_to_admin\')) ? document.getElementById(\'send_copy_to_admin\') : null;
				if(val == \'admins\' && el_send_copy_to_admin){
					el_send_copy_to_admin.disabled = \'disabled\';
				}else{
					el_send_copy_to_admin.disabled = \'\';
				}
			}
					
			function OnSubmit_Check(){
				var email_to = (document.getElementById(\'email_to\')) ? document.getElementById(\'email_to\').value : \'\';
				var email_from = (document.getElementById(\'email_from\')) ? document.getElementById(\'email_from\').value : \'\';
				var subject = (document.getElementById(\'subject\')) ? document.getElementById(\'subject\').value : \'\';
				var message = (document.getElementById(\'message\')) ? document.getElementById(\'message\').value : \'\';
				if(email_to == \'\'){
					alert(\''.str_replace('_FIELD_', _EMAIL_TO, _FIELD_CANNOT_BE_EMPTY).'\');
					document.getElementById(\'email_to\').focus();
					return false;            
				}else if(email_from == \'\'){
					alert(\''.str_replace('_FIELD_', _EMAIL_FROM, _FIELD_CANNOT_BE_EMPTY).'\');
					document.getElementById(\'email_from\').focus();
					return false;
				}else if(email_from != \'\' && !appIsEmail(email_from)){
					alert(\''.str_replace('_FIELD_', _EMAIL_FROM, _FIELD_MUST_BE_EMAIL).'\');
					document.getElementById(\'email_from\').focus();
					return false;			
				}else if(subject == \'\'){
					alert(\''.str_replace('_FIELD_', _SUBJECT, _FIELD_CANNOT_BE_EMPTY).'\');
					document.getElementById(\'subject\').focus();
					return false;
				}else if(message == \'\'){
					alert(\''.str_replace('_FIELD_', _MESSAGE, _FIELD_CANNOT_BE_EMPTY).'\');
					document.getElementById(\'message\').focus();
					return false;
				}else if(email_to == \'all\'){
					if(!confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')){
						return false;
					}
				}
				return true;
			}
		</script>';
		
		$output .= '<div class="table-responsive">
		<form action="index.php?admin=mass_mail" method="post" style="margin:0px;">
			'.draw_hidden_field('task', 'send', false).'
			'.draw_hidden_field('email_random_code', get_random_string(10), false).'
			'.draw_token_field(false).'			
			
			<table border="0" cellspacing="10" class="mgrid_table">
			<tr>
				<td align="left" valign="top">
					<fieldset style="height:500px;">
					<legend><b>'._FORM.':</b></legend>
					<table width="97%" align="center" border="0" cellspacing="5">
					<tr>
						<td align="right" nowrap="nowrap">
							<label>'._EMAIL_TEMPLATES.':</label><br>
							'.prepare_permanent_link('index.php?admin=email_templates', '[ '._MANAGE_TEMPLATES.' ]', '', '').'
						</td>
						<td></td>
						<td>
							<table cellpadding="0" cellspacing="0">
							<tr valign="middle">
								<td>
									<select name="template_name" id="template_name" class="mgrid_select" style="margin-bottom:3px;" onchange="javascript:appGoTo(\'admin=mass_mail&template_code=\'+this.value)">
										<option value="">-- '._NO_TEMPLATE.' --</option>';
										$templates = $this->GetAllTemplates('is_system_template=0');
										for($i=0; $i < $templates[1]; $i++){
											$output .= '<option';
											$output .= (($templates[0][$i]['is_system_template'] == '1') ? ' style="background-color:#ffffcc;color:#000055"' : '');
											$output .= (($template_code == $templates[0][$i]['template_code']) ? ' selected="selected"' : '');
											$output .= ' value="'.encode_text($templates[0][$i]['template_code']).'">'.$templates[0][$i]['template_name'].'</option>';
										}
										$output .= '
									</select>						
								</td>
							</tr>
							</table>                    
						</td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap"><label>'._EMAIL_TO.':</label></td>
						<td><span class="mandatory_star">*</span></td>
						<td>
							<select name="email_to" id="email_to" class="mgrid_select" style="margin-bottom:3px;" onchange="email_to_OnChange(this.value)">
								<option value="">-- '._SELECT.' --</option>
								<option value="test" style="background-color:#ffffcc;color:#000055">'._TEST_EMAIL.' ('.$email_from.')</option>';
								if(Modules::IsModuleInstalled('news')){
									$output .= '<option value="newsletter_subscribers" style="background-color:#ffccff;color:#000055">'._NEWSLETTER_SUBSCRIBERS.' ('.$newsletter_subscribers_count.')</option>';
								}
								if($members_module_installed){
									$output .= '<optgroup label="'.$this->MEMBERS_NAME.'">';
									$output .= '<option value="members|all">'._ALL.' ('.$members_emails_count.')</option>';	
									$arrMembersGroups = CustomerGroups::GetAllGroupsByCustomers();

									$member_groups_emails_count = 0;
									if($arrMembersGroups[1] > 0){
										foreach($arrMembersGroups[0] as $key => $val){
											if($val[$this->MEMBERS_MODULE_NAME.'_count']){
												$output .= '<option value="members|'.$val['id'].'">'.$val['name'].' ('.$val[$this->MEMBERS_MODULE_NAME.'_count'].')</option>';
												$member_groups_emails_count += $val[$this->MEMBERS_MODULE_NAME.'_count'];												
											}
										}
									}
									$member_non_groups_emails = $members_emails_count - $member_groups_emails_count;
									$output .= '<option value="members|uncategorized">'._UNCATEGORIZED.' ('.$member_non_groups_emails.')</option>';										
									$output .= '</optgroup>';
								}
								if($suppliers_module_installed) $output .= '<option value="suppliers|all">'.$this->SUPPLIERS_NAME.' ('.$suppliers_emails_count.')</option>';
								$output .= '<option value="admins">'._ADMINS.' ('.$admins_emails_count.')</option>';
								if($members_module_installed || $suppliers_module_installed) $output .= '<option value="all">'.$this->ADMINS_AND_OTHERS_NAME.' ('.$emails_count.')</option>';
							$output .= '</select>
						</td>
					</tr>            
					<tr>
						<td align="right" nowrap="nowrap"><label for="email">'._EMAIL_FROM.':</label></td>
						<td><span class="mandatory_star">*</span></td>
						<td>
							<input type="text" class="mgrid_text" style="width:210px" name="email_from" id="email_from" value="'.decode_text($email_from).'" maxlength="70" />
						</td>
					</tr>
					<tr valign="top">
						<td align="right" nowrap="nowrap"><label>'._SUBJECT.':</label></td>
						<td><span class="mandatory_star">*</span></td>
						<td>
							<input type="text" class="mgrid_text" style="width:410px" name="subject" id="subject" value="'.decode_text($template_subject).'" maxlength="255" />
						</td>
					</tr>
					<tr valign="top">
						<td align="right" nowrap="nowrap"><label>'._MESSAGE.':</label></td>
						<td><span class="mandatory_star">*</span></td>
						<td>
							<textarea style="width:465px;margin-right:10px;" rows="10" name="message" id="message">'.$template_content.'</textarea>
						</td>
					</tr>';
					
					$output .= '<tr valign="middle">
						<td colspan="2"></td>
						<td><img src="images/question_mark.png" alt="help">'._MASS_MAIL_ALERT.'</td>
					</tr>';						
					
					$output .= '<tr><td colspan="3" nowrap style="height:6px;"></td></tr>
					<tr>
						<td align="right" nowrap="nowrap"><a href="javascript:void(0);" onclick="appPopupWindow(\'mail_preview.html\',\'message\')">[ '._PREVIEW.' ]</a></td>
						<td></td>
						<td>
							<div class="checkbox" style="float:left;width:400px;"><input type="checkbox" class="mgrid_checkbox" name="send_copy_to_admin" id="send_copy_to_admin" '.(($send_copy_to_admin == '1') ? 'checked="checked"' : '').' value="1"> <label for="send_copy_to_admin">'._SEND_COPY_TO_ADMIN.'</label></div>
							<div style="float:left;"><input type="submit" class="mgrid_button" name="btnSubmit" value="'._SEND.'" onclick="return OnSubmit_Check();">&nbsp;&nbsp;</div>
						</td>
					</tr>
					</table>
					</fieldset>
				</td>        
				<td align="left" valign="top">
					<fieldset style="padding-'.Application::Get('defined_right').':10px;">
					<legend>'._PREDEFINED_CONSTANTS.':</legend>
					<ul>
						<li>{FIRST NAME} <br><span style="color:a0a0a0">'._PC_FIRST_NAME_TEXT.'</span></li>
						<li>{LAST NAME} <br><span style="color:a0a0a0">'._PC_LAST_NAME_TEXT.'</span></li>
						<li>{USER NAME} <br><span style="color:a0a0a0">'._PC_USER_NAME_TEXT.'</span></li>
						<li>{USER EMAIL} <br><span style="color:a0a0a0">'._PC_USER_EMAIL_TEXT.'</span></li>
						<li>{BASE URL} <br><span style="color:a0a0a0">'._PC_WEB_SITE_BASED_URL_TEXT.'</span></li>
						<li>{WEB SITE} <br><span style="color:a0a0a0">'._PC_WEB_SITE_URL_TEXT.'</span></li>
						<li>{YEAR} <br><span style="color:a0a0a0">'._PC_YEAR_TEXT.'</span></li>
                        <li>{MESSAGE FOOTER} - <span class=gray>'._PC_MESSAGE_FOOTER.'</span></li>
					</ul>
					</fieldset>
				</td>
			</tr>
			</table>			
		</form>
		</div>';
	
		if($draw) echo $output;
		else return $output;
	}

    /*
     * Prepare {Message Footer}
     * */
    public static function PrepareMessageFooter($lang = '')
    {
		global $objSiteDescription;
		if(!empty($lang)){
			$objSiteDescription->LoadData($lang);
		}
        $header_text = $objSiteDescription->GetParameter('header_text');
        $phone = ModulesSettings::Get('rooms', 'default_contant_phone');
        $email = ModulesSettings::Get('rooms', 'default_contant_email');
        $geographical_address = $objSiteDescription->GetParameter('geographical_address');
        $logo = '<a href=\''.APPHP_BASE.'\'><img src=\''.APPHP_BASE.'images/logo.png\' /></a>';

		if(!empty($lang)){
			$objSiteDescription->LoadData(Application::Get('lang'));
		}
        
        return (!empty($header_text) ? '<h3>'.$header_text.'</h3>' : '').(!empty($phone) ? '<b>'.$phone.'</b><br>' : '').(!empty($email) ? '<b>'.$email.'</b><br>' : '').(!empty($geographical_address) ? '<br>'.$geographical_address.'<br>' : '').(!empty($logo) ? '<br>'.$logo.'<br>' : '');
    }

	/**
	 *	'After'-operation methods
	 */
	public function AfterInsertRecord()
	{
		// clone to other languages ---
		$total_languages    = Languages::GetAllActive();
		$language_id 	    = MicroGrid::GetParameter('language_id');
		$template_code 	    = MicroGrid::GetParameter('template_code', false);
		$template_name 		= MicroGrid::GetParameter('template_name', false);
		$template_subject	= MicroGrid::GetParameter('template_subject', false);
		$template_content	= MicroGrid::GetParameter('template_content', false);
		$is_system_template = MicroGrid::GetParameter('is_system_template', false);
		
		for($i = 0; $i < $total_languages[1]; $i++){
			if($language_id != '' && $total_languages[0][$i]['abbreviation'] != $language_id){				
				$sql = 'INSERT INTO '.TABLE_EMAIL_TEMPLATES.' (
							id,
							language_id,
							template_code,
							template_name,
							template_subject,
							template_content,
							is_system_template
						) VALUES (
							NULL,
							\''.encode_text($total_languages[0][$i]['abbreviation']).'\',
							\''.encode_text($template_code).'\',
							\''.encode_text($template_name).'\',
							\''.encode_text($template_subject).'\',
							\''.encode_text($template_content).'\',
							'.(int)$is_system_template.'
						)';
				database_void_query($sql);
				$this->SetSQLs('insert_lan_'.$total_languages[0][$i]['abbreviation'], $sql);
			}								
		}
	}

	/**
	 * Before-deleting record
	 */
	public function BeforeDeleteRecord()
	{
		$sql = 'SELECT is_system_template FROM '.$this->tableName.' WHERE id = '.(int)$this->curRecordId;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			if($result[0]['is_system_template'] == '1'){
				$this->error = _SYSTEM_EMAIL_DELETE_ALERT;
				return false;
			}
		}
		return true;
	}

	/**
	 * Returns all email templates
	 * @param @where_clause
	 */
	private function GetAllTemplates($where_clause = '')
	{
		$sql = 'SELECT
					language_id,
					template_code,
					template_name,
					template_subject,
					template_content,
					is_system_template
				FROM '.$this->tableName.'
				WHERE language_id = \''.Application::Get('lang').'\' '.(($where_clause != '') ? ' AND '.$where_clause : '').'
				ORDER BY is_system_template ASC';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result;
	}

}
