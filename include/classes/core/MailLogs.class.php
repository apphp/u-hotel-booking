<?php

/**
 *	MailLogs Class
 *  -------------- 
 *  Description : encapsulates ban list properties
 *	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Updated	    : 30.08.2018
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				MailLogAddRecord
 *	__destruct
 *	DeleteAllRecord
 *
 *  1.0.0
 *      - added maxlength validation to 'Reason' field
 *      - changed " with '
 *      - added 'maxlength' to textareas
 *      - added DeleteAllRecord method
 *      - added readonly_html
 *	
 **/


class MailLogs extends MicroGrid {
	
	protected $debug = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		$this->params = array();
		

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_MAIL_LOGS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mail_log';
		$this->actions      = array('add'=>false, 'edit'=>false, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.date_created DESC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;
		$datetime_format = get_datetime_format();

		$arr_email_types = array('test'=>_TEST_EMAIL, 'newsletter_subscribers'=>_NEWSLETTER_SUBSCRIBERS, 'all'=>_ALL, 'uncategorized'=>_UNCATEGORIZED, 'admins'=>_ADMINS);

		// define filtering fields
		$this->arrFilteringFields = array(
			_FIRST_NAME  => array('table'=>TABLE_CUSTOMERS, 'field'=>'first_name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'150px', 'visible'=>true),
			_LAST_NAME   => array('table'=>TABLE_CUSTOMERS, 'field'=>'last_name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'150px', 'visible'=>true),
			_SUBJECT     => array('table'=>$this->tableName, 'field'=>'email_template_subject', 'type'=>'text', 'sign'=>'%like%', 'width'=>'150px', 'visible'=>true),
		);
		
		$arr_statuses = array('0'=>'<span class=red>'._ERROR.'</span>', '1'=>'<span class=green>'._SENT.'</span>');

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.account_id,
									'.$this->tableName.'.email_to,
									'.$this->tableName.'.email_template_subject,
									'.$this->tableName.'.date_created,
									'.$this->tableName.'.status,
									CONCAT('.$this->tableName.'.emails_total, " / ", '.$this->tableName.'.emails_sent) as emails_counter,
									'.TABLE_EMAIL_TEMPLATES.'.template_name,
									CONCAT('.TABLE_CUSTOMERS.'.first_name, " ", '.TABLE_CUSTOMERS.'.last_name) as full_name
								FROM '.$this->tableName.'
									LEFT JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.account_id = '.TABLE_CUSTOMERS.'.id
									LEFT JOIN '.TABLE_EMAIL_TEMPLATES.' ON '.TABLE_EMAIL_TEMPLATES.'.template_code = '.$this->tableName.'.email_template_code AND '.TABLE_EMAIL_TEMPLATES.'.language_id = \''.$this->languageId.'\'';
		// define view mode fields
		$this->arrViewModeFields = array();
		$this->arrViewModeFields['full_name'] 			= array('title'=>_USER_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true);
		if(SAVE_MAIL_LOG == 'mass_only'){
			$this->arrViewModeFields['email_to']		= array('title'=>_EMAIL_TO, 'type'=>'enum', 'align'=>'left', 'width'=>'190px', 'sortable'=>'true', 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_email_types);		
		}else{
			$this->arrViewModeFields['email_to']		= array('title'=>_EMAIL_TO, 'type'=>'label', 'align'=>'left', 'width'=>'150px');			
		}
		$this->arrViewModeFields['email_template_subject'] = array('title'=>_SUBJECT, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>'');
		$this->arrViewModeFields['template_name'] 		= array('title'=>_EMAIL_TEMPLATE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>'');
		$this->arrViewModeFields['emails_counter']		= array('title'=>_TOTAL.' / '._SENT, 'type'=>'label', 'align'=>'center', 'width'=>'130px', 'format'=>'date', 'format_parameter'=>'', 'visible'=>SAVE_MAIL_LOG == 'all' ? false : true);
		$this->arrViewModeFields['status']            	= array('title'=>_STATUS, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_statuses);
		$this->arrViewModeFields['date_created']  		= array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'center', 'width'=>'150px', 'format'=>'date', 'format_parameter'=>$datetime_format);

		
		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.account_id,
								'.$this->tableName.'.email_to,
								'.$this->tableName.'.email_template_subject,
								'.$this->tableName.'.email_template_content,
								'.$this->tableName.'.date_created,
								'.$this->tableName.'.emails_total,
								'.$this->tableName.'.emails_sent,
								'.$this->tableName.'.status,
								'.$this->tableName.'.status_description,
								'.TABLE_EMAIL_TEMPLATES.'.template_name,
								CONCAT('.TABLE_CUSTOMERS.'.first_name, " ", '.TABLE_CUSTOMERS.'.last_name) as full_name
							FROM '.$this->tableName.'
								LEFT JOIN '.TABLE_EMAIL_TEMPLATES.' ON '.TABLE_EMAIL_TEMPLATES.'.template_code = '.$this->tableName.'.email_template_code AND '.TABLE_EMAIL_TEMPLATES.'.language_id = \''.$this->languageId.'\'
								LEFT JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.account_id = '.TABLE_CUSTOMERS.'.id
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		$this->arrDetailsModeFields = array();
		$this->arrDetailsModeFields['full_name'] = array('title'=>_USER_NAME, 'type'=>'label');
		if(SAVE_MAIL_LOG == 'mass_only'){
			$this->arrDetailsModeFields['email_to']	= array('title'=>_EMAIL_TO, 'type'=>'enum', 'source'=>$arr_email_types);
		}else{
			$this->arrDetailsModeFields['email_to']	= array('title'=>_EMAIL_TO, 'type'=>'label');
		}
		
		$this->arrDetailsModeFields['template_name'] 		= array('title'=>_EMAIL_TEMPLATE, 'type'=>'label');
		$this->arrDetailsModeFields['email_template_subject'] = array('title'=>_SUBJECT, 'type'=>'label');
		$this->arrDetailsModeFields['email_template_content'] = array('title'=>_MESSAGE, 'type'=>'label', 'format'=>'readonly_html');
		$this->arrDetailsModeFields['emails_total'] 		= array('title'=>_TOTAL, 'type'=>'label', 'visible'=>SAVE_MAIL_LOG == 'all' ? false : true);
		$this->arrDetailsModeFields['emails_sent'] 			= array('title'=>_SENT, 'type'=>'label', 'visible'=>SAVE_MAIL_LOG == 'all' ? false : true);
		$this->arrDetailsModeFields['status'] 				= array('title'=>_STATUS, 'type'=>'enum', 'source'=>$arr_statuses);
		$this->arrDetailsModeFields['status_description'] 	= array('title'=>_STATUS_DESCRIPTION, 'type'=>'label');
		$this->arrDetailsModeFields['date_created']  		= array('title'=>_DATE_CREATED, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format);
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }
	
	//==========================================================================
    // Static Methods
	//==========================================================================
	/**
	 * Add new records
	 * @param $account_id
	 * @param $email_to
	 * @param $email_template_code
	 * @param $email_template_subject
	 * @param $email_template_content
	 * @param $emails_sent
	 * @param $emails_total
	 * @param $status
	 * @param $status_description
	 * @return bool
	 */
	public static function MailLogAddRecord($account_id = 0, $email_to = 'admins', $email_template_code = 'test_template', $email_template_subject = '', $email_template_content = '', $emails_sent = 0, $emails_total = 0, $status = '', $status_description = '')
	{
		$sql = "INSERT
			INTO ".TABLE_MAIL_LOGS."
			(id, account_id, email_to, email_template_code, email_template_subject, email_template_content, emails_total, emails_sent, date_created, status, status_description) VALUES
			(NULL, '".(int)$account_id."', '".$email_to."', '".encode_text($email_template_code)."', '".$email_template_subject."', '".encode_text(str_replace(array("\r\n\r\n"), "\r\n", $email_template_content))."', '".(int)$emails_total."', '".(int)$emails_sent."', '".date('Y-m-d H:i:s')."', '".$status."', '".encode_text(strip_tags($status_description))."')";
		$result = database_void_query($sql);
		if($result > 0){
			return true;
		}else{
			///echo database_error();
			return false;
		}
	}

	/**
	 * Delete all records
	 * @return boolean
	 */
	public function DeleteAllRecord()
	{
		$sql = "DELETE FROM ".TABLE_MAIL_LOGS." WHERE 1";
        if($this->debug) $start_time = $this->GetFormattedMicrotime();
        $result = database_void_query($sql);
        if($this->debug) $finish_time = $this->GetFormattedMicrotime();        
		if($result > 0){
			if($this->debug) $this->arrSQLs['delete_sql'] = '<i>Delete All Records</i> | T: '.round((float)$finish_time - (float)$start_time, 4).' sec. <br>'.$sql;
			return true;
		}else{
			if($this->debug) $this->arrErrors['delete_sql'] = $sql.'<br>'.database_error();
			$this->error = _TRY_LATER;
			return false;
		}
	}
}
