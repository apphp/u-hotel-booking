<?php

/***
 *	Comments class (has differences)
 *  --------------
 *  Description : encapsulates comments properties
 *	Written by  : ApPHP
 *	Version     : 1.0.3
 *  Updated	    : 16.04.2017
 *  Usage       : Core Class
 *
 *	PUBLIC:				  	STATIC:				 		PRIVATE:
 * 	------------------	  	---------------     		---------------
 *	__construct				AwaitingModerationCount 	DrawCommentsForm
 *	__destruct
 *	GetPageName
 *	DrawArticleComments
 *
 *  1.0.3
 *      - fixed vulnerability issue for username field in comments
 *      -
 *      -
 *      -       
 *  1.0.2
 *      - replaced " with '
 *      - added abs() to $_GET['page']
 *      - added maxlength attribute for comments textarea
 *      - added sqlFieldDatetimeFormat for localization
 *      - minor changes in HTML
 **/

class Comments extends MicroGrid {
	
	protected $debug = false;

	//------------------------------
    // customer
	private $USER_TYPE_NAME = '';     
	private $sqlFieldDatetimeFormat = '';
	
	//==========================================================================
    // Class Constructor
	//		@param $page_id
	//==========================================================================
	function __construct($page_id = '')
	{		
		parent::__construct();

		global $objSettings;
        
        $this->USER_TYPE_NAME = 'customer';

		$this->params = array();		
		## for standard fields
		if(isset($_POST['is_published']))   $this->params['is_published'] = prepare_input($_POST['is_published']);
		if(isset($_POST['date_published'])) $this->params['date_published'] = prepare_input($_POST['date_published']);
		if($page_id == 'home') $page_id = '';
		
		## for checkboxes 
		//$this->params['parameter4'] = isset($_POST['parameter4']) ? $_POST['parameter4'] : '0';

		## for images
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = $_POST['icon'];
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}elseif(self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_COMMENTS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_comments_management'.(($page_id != '') ? '&pid='.(int)$page_id : '');
		$this->actions      = array('add'=>false, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ($page_id != '') ? 'WHERE '.$this->tableName.'.article_id='.$page_id : '';
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.is_published ASC, '.$this->tableName.'.date_created DESC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_PUBLISHED  => array('table'=>$this->tableName, 'field'=>'is_published', 'type'=>'dropdownlist', 'source'=>array('0'=>_NO, '1'=>_YES), 'sign'=>'=', 'width'=>'90px'),
		);

		$datetime_format = get_datetime_format();
		$arr_is_published = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

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
		// format: strip_tags
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.article_id,
									'.$this->tableName.'.user_id,
									'.$this->tableName.'.user_name,
									'.$this->tableName.'.user_email,
									'.$this->tableName.'.comment_text,									
									DATE_FORMAT('.$this->tableName.'.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as mod_date_created,
									CONCAT("<img src=\"images/", IF('.$this->tableName.'.is_published, "published_g.gif", "published_x.gif"), "\" alt=\"published\" />") as is_published,
									'.TABLE_PAGES.'.page_title,
									'.TABLE_LANGUAGES.'.lang_name
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_PAGES.' ON '.$this->tableName.'.article_id = '.TABLE_PAGES.'.id
									LEFT OUTER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_PAGES.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
								';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'mod_date_created' => array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'left', 'width'=>'160px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'height'=>'', 'maxlength'=>''),
			'page_title'    => array('title'=>_ARTICLE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'height'=>'', 'maxlength'=>'', 'format'=>''),
			'is_published'  => array('title'=>_PUBLISHED, 'type'=>'label', 'align'=>'center', 'width'=>'80px'),
			'lang_name'     => array('title'=>_LANGUAGE, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'height'=>'', 'maxlength'=>'', 'format'=>''),
			'user_email'    => array('title'=>_EMAIL_ADDRESS, 'type'=>'label', 'align'=>'center', 'width'=>'130px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'height'=>'', 'maxlength'=>'32', 'format'=>''),
			'user_name'     => array('title'=>_USERNAME, 'type'=>'label', 'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'height'=>'', 'maxlength'=>'32', 'format'=>''),
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
								'.$this->tableName.'.article_id,
								'.$this->tableName.'.user_id,
								'.$this->tableName.'.user_name,
								'.$this->tableName.'.user_email,
								'.$this->tableName.'.comment_text,
								'.$this->tableName.'.is_published,
								DATE_FORMAT('.$this->tableName.'.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as date_created,
								'.$this->tableName.'.date_published,
								IF('.$this->tableName.'.date_published IS NULL, "", DATE_FORMAT('.$this->tableName.'.date_published, \''.$this->sqlFieldDatetimeFormat.'\')) as m_date_published,
								'.TABLE_PAGES.'.page_title,
								'.TABLE_LANGUAGES.'.lang_name
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_PAGES.' ON '.$this->tableName.'.article_id = '.TABLE_PAGES.'.id
								LEFT OUTER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_PAGES.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(

			'page_title'    => array('title'=>_ARTICLE, 'type'=>'label'),
			'user_name'     => array('title'=>_USERNAME, 'type'=>'label'),
			'user_email'    => array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
			'lang_name'     => array('title'=>_LANGUAGE, 'type'=>'label'),
			'comment_text'  => array('title'=>_TEXT, 'type'=>'label'),
			'date_created'  => array('title'=>_DATE_CREATED, 'type'=>'label', 'format'=>'', 'format_parameter'=>''),
			'date_published' => array('title'=>'', 'type'=>'hidden', 'required'=>false, 'default'=>date('Y-m-d H:i:s')),
			'm_date_published' => array('title'=>_DATE_PUBLISHED, 'type'=>'label', 'format'=>'', 'format_parameter'=>''),
			'is_published'  => array('title'=>_APPROVE, 'type'=>'enum', 'width'=>'80px', 'required'=>true, 'readonly'=>false, 'source'=>array('1'=>_YES), 'unique'=>false),
		
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(

			'page_title'    => array('title'=>_ARTICLE, 'type'=>'label'),
			'user_name'     => array('title'=>_USERNAME, 'type'=>'label'),
			'user_email'    => array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
			'lang_name'     => array('title'=>_LANGUAGE, 'type'=>'label'),
			'comment_text'  => array('title'=>_TEXT, 'type'=>'label', 'format'=>'nl2br'),
			'is_published'  => array('title'=>_APPROVED, 'type'=>'enum', 'source'=>$arr_is_published),
			'date_created'  => array('title'=>_DATE_CREATED, 'type'=>'label', 'format'=>'', 'format_parameter'=>''),
			'm_date_published' => array('title'=>_DATE_PUBLISHED, 'type'=>'label', 'format'=>'', 'format_parameter'=>''),

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
	 * Returns page name
	 * @param $page_id
	 */
	public function GetPageName($page_id = '')
	{
		if(!empty($page_id)){
			$sql = 'SELECT '.TABLE_PAGES.'.page_title
				FROM '.$this->tableName.'
					LEFT OUTER JOIN '.TABLE_PAGES.' ON '.$this->tableName.'.article_id = '.TABLE_PAGES.'.id
				WHERE '.$this->tableName.'.article_id = '.(int)$page_id;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				return $result[0]['page_title'];
			}
		}
		return '';
	}
	
	/**
	 * Draws article comments
	 * @param $article_id
	 * @param $draw
	 */
	public function DrawArticleComments($article_id = '', $draw = true)
	{
		if(!$article_id) return '';
		
		global $objLogin;
		
		$delete_pending_time  = ModulesSettings::Get('comments', 'delete_pending_time');
		$user_type  		  = ModulesSettings::Get('comments', 'user_type');
		$comment_length		  = ModulesSettings::Get('comments', 'comment_length'); 
		$image_verification   = ModulesSettings::Get('comments', 'image_verification_allow'); 
		$comments_on_page     = ModulesSettings::Get('comments', 'page_size');
		$is_published         = (ModulesSettings::Get('comments', 'pre_moderation_allow') == 'yes') ? '0' : '1';
		
        if($image_verification == 'yes'){
			include_once('modules/captcha/securimage.php');
			$objImg = new Securimage();			
		}

		$task 				= isset($_POST['task']) ? prepare_input($_POST['task']) : '';
		$comment_id 	    = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : '';
		$init_state 		= 'closed';
		$user_id			= isset($_POST['user_id']) ? (int)$_POST['user_id'] : '';		
		$user_name 			= isset($_POST['comment_user_name']) ? strip_tags(prepare_input($_POST['comment_user_name'])) : '';
		$user_email  		= isset($_POST['comment_user_email']) ? prepare_input($_POST['comment_user_email']) : '';
		$comment_text 		= isset($_POST['comment_text']) ? prepare_input($_POST['comment_text']) : '';
		$captcha_code 		= isset($_POST['captcha_code']) ? prepare_input($_POST['captcha_code']) : '';
		$msg                = '';
		$task_completed     = false;
		$focus_field 		= '';
		$current_page 		= isset($_GET['p']) ? abs((int)$_GET['p']) : '1';

		if($task == 'publish_comment'){
			$init_state = 'opened';
			
			if($user_name == ''){
				$msg = draw_important_message(_USERNAME_EMPTY_ALERT, false);
				$focus_field = 'comment_user_name';
			}elseif(!check_email_address($user_email) && !$objLogin->IsLoggedInAs($this->USER_TYPE_NAME)){
				$msg = draw_important_message(_EMAIL_IS_WRONG, false);
				$focus_field = 'comment_user_email';			
			}elseif($comment_text == ''){
				$msg = draw_important_message(_MESSAGE_EMPTY_ALERT, false);
				$focus_field = 'comment_text';
			}elseif($comment_text != '' && (strlen($comment_text) > $comment_length)){
				$msg = draw_important_message(str_replace('_LENGTH_', $comment_length, _COMMENT_LENGTH_ALERT), false);
				$focus_field = 'comment_text';				
			}elseif(($image_verification == 'yes') && !$objImg->check($captcha_code)){
				$msg = draw_important_message(_WRONG_CODE_ALERT, false);
				$focus_field = 'captcha_code';				
			}else{
				// Block operation in demo mode
				if(strtolower(SITE_MODE) == 'demo'){
					$msg = draw_important_message(_OPERATION_BLOCKED, false);
				}else{					
					if($objLogin->IpAddressBlocked(get_current_ip())){
						$msg = draw_important_message(_IP_ADDRESS_BLOCKED, false);
					}elseif($objLogin->EmailBlocked($user_email)){
						$msg = draw_important_message(_EMAIL_BLOCKED, false);
					}else{
						$sql = 'INSERT INTO '.TABLE_COMMENTS.'(
									id,
									article_id,
									user_id,
									user_name,
									user_email,
									comment_text,
									date_created,
									date_published,
									is_published
								)VALUES(
									NULL,
									'.(int)$article_id.',
									'.(int)$user_id.',
									\''.encode_text($user_name).'\',
									\''.encode_text($user_email).'\',
									\''.encode_text(strip_tags($comment_text, '<b><i><u><br>')).'\',
									\''.date('Y-m-d H:i:s').'\',
									'.($is_published == '1' ? "'".date('Y-m-d H:i:s')."'" : null).',
									\''.$is_published.'\'
								)';
						if(database_void_query($sql)){
							if($is_published == '1'){
								$msg = draw_success_message(_COMMENT_POSTED_SUCCESS, false);	
							}else{
								$msg = draw_success_message(_COMMENT_SUBMITTED_SUCCESS, false);	
							}						
							$task_completed = true;
						}else{
							$msg = draw_important_message(_TRY_LATER, false);
						}
					}
				}
			}			
		}elseif($task == 'delete_comment'){
			$init_state = 'opened';
		
			$sql = 'DELETE FROM '.$this->tableName.'
					WHERE TIMESTAMPDIFF(MINUTE, date_published, \''.date('Y-m-d H:i:s').'\') < '.$delete_pending_time.' AND
						  id = '.(int)$comment_id;
			if(database_void_query($sql)){
				$msg = draw_success_message(_COMMENT_DELETED_SUCCESS, false);				
			}else{
				$msg = draw_important_message(_TRY_LATER, false);
			}
		}

		// -------- pagination
		$total_comments 	= 0;
		$page_size  		= $comments_on_page;		
		
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_COMMENTS.' WHERE is_published = 1 AND article_id = '.(int)$article_id;
		$comments_result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		$total_comments = $comments_result['cnt'];
		
		$total_pages = (int)($total_comments / $page_size);
		
		if($current_page > ($total_pages+1)) $current_page = 1;
		if(($total_comments % $page_size) != 0) $total_pages++;
		if($task_completed) $current_page = $total_pages;
		if(!is_numeric($current_page) || (int)$current_page <= 0) $current_page = 1;
		
		$start_row = ($current_page - 1) * $page_size;
		if(isset($_GET['p'])) $init_state = 'opened';		
		// --------

		$sql = 'SELECT *
				FROM '.TABLE_COMMENTS.'
				WHERE article_id = '.(int)$article_id.' AND is_published = 1
				ORDER BY date_published ASC 
				LIMIT '.$start_row.', '.$page_size;
				
		$result = database_query($sql, DATA_AND_ROWS);
		
		$output = '<script type="text/javascript">function deleteComment(cid) {
			if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')){
				jQuery(\'#comment_task\').val(\'delete_comment\');
				jQuery(\'#comment_id\').val(cid);
				jQuery(\'#frmComments\').submit();				
				return true;
			}
			return false;
		} </script>';
		$output .= '<div id="commentsLink"><a href="javascript:void(0);" onclick="javascript:jQuery(\'#commentsWrapper\').slideToggle(\'fast\');">'.str_replace('_COUNT_', $total_comments, _COMMENTS_LINK).'</a><br /><br /></div>';
		$output .= '<div id="commentsWrapper" style="display:'.(($init_state == 'opened') ? '' : 'none').';">';
		$output .= '<div id="commentsPublished">';
		if($result[1] > 0){
			for($i=0; $i<$result[1]; $i++){
				$output .= '<div class="comment">';
				$output .= '<div class="comment_user_name"><b>'.htmlspecialchars($result[0][$i]['user_name']).'</b> '._SAID.'...</div>';
				$output .= '<div class="comment_test">'.htmlspecialchars($result[0][$i]['comment_text']).'</div>';
				$output .= '<div class="comment_date">';
				if($result[0][$i]['user_id'] == $objLogin->GetLoggedID() && floor(time_diff($result[0][$i]['date_published'], date('Y-m-d H:i:s'))/60) < $delete_pending_time) $output .= '<img src="images/published_x.gif" alt="published" style="cursor:pointer;margin-bottom:-3px;margin-right:3px;" onclick="deleteComment(\''.$result[0][$i]['id'].'\');">';
				$output .= '<i>'._PUBLISHED.': '.format_datetime($result[0][$i]['date_published']).'</i></div>';
				$output .= '</div>';
			}
			// draw pagination links
			if($total_pages > 1){
				$output .= '<div class="paging">';
				for($page_ind = 1; $page_ind <= $total_pages; $page_ind++){
					$output .= prepare_permanent_link('index.php?page='.Application::Get('page').'&pid='.Application::Get('page_id').'&p='.$page_ind, (($page_ind == $current_page) ? '<b>['.$page_ind.']</b>' : $page_ind), '', 'paging_link').' ';
				}
				$output .= '</div>'; 
			}
		}else{
			$output .= '<div class="comment">';
			$output .= '<b>'._NO_COMMENTS_YET.'</b><br /><br />';			
			$output .= '</div>';			
		}
		$output .= '</div>';
		
		$output .= (($msg != '') ? $msg : '');
		if($user_type == 'registered' && !$objLogin->IsLoggedInAs($this->USER_TYPE_NAME)){
			$output .= draw_message(_POST_COM_REGISTERED_ALERT, false);
		}else{
			$output .= $this->DrawCommentsForm($article_id, $image_verification, $focus_field, $task_completed, false);	
		}		
		$output .= '</div>';
		
		if($draw) echo $output;
		else return $output;
	}	
	
	/**
	 * Draws comment submission form
	 * @param $article_id
	 * @param $image_verification
	 * @param $focus_field
	 * @param $task_completed
	 * @param $draw
	 */
	private function DrawCommentsForm($article_id = '', $image_verification = 'no', $focus_field = '', $task_completed = false, $draw = true)
	{
		if(!$article_id) return '';
	
		global $objLogin;
		$user_id 		= '';
		$user_name 		= '';
		$user_name 		= (isset($_POST['comment_user_name']) && !$task_completed) ? decode_text(strip_tags(prepare_input($_POST['comment_user_name']))) : '';
		$user_email  	= (isset($_POST['comment_user_email']) && !$task_completed) ? decode_text(prepare_input($_POST['comment_user_email'])) : '';		
		$comment_text 	= (isset($_POST['comment_text']) && !$task_completed) ? prepare_input($_POST['comment_text']) : '';
		$comment_length = ModulesSettings::Get('comments', 'comment_length'); 

		if($objLogin->IsLoggedInAs($this->USER_TYPE_NAME)){
			$user_id = $objLogin->GetLoggedID();
			$user_name = $objLogin->GetLoggedName();
		}		
		
		$output = '
		<div class="comments_form_container">
		<form class="comments-form" method="post" name="frmComments" id="frmComments">
			'.draw_hidden_field('task', 'publish_comment', false, 'comment_task').'
			'.draw_hidden_field('comment_id', '', false, 'comment_id').'
			'.draw_hidden_field('article_id', $article_id, false).'
			'.draw_hidden_field('user_id', $user_id, false).'
			'.draw_token_field(false);
            if($user_id != ''){
                $output .= draw_hidden_field('comment_user_name', $user_name, false);
                $output .= draw_hidden_field('comment_user_email', $user_email, false);
            }
			
			$output .= '<table border="0" width="98%">
			<tr><td nowrap height="10px"></td></tr>
			<tr><td><h4>'._LEAVE_YOUR_COMMENT.':</h4></td></tr>';
            if($user_id == ''){
                $output .= '<tr><td>';
                $output .= _YOUR_NAME.': <input type="text" name="comment_user_name" id="comment_user_name" style="width:170px" value="'.htmlspecialchars($user_name).'" maxlength="50" autocomplete="off" />&nbsp;&nbsp;';
                $output .= _YOUR_EMAIL.': <input type="text" name="comment_user_email" id="comment_user_email" style="width:170px" value="'.htmlspecialchars($user_email).'" maxlength="70" autocomplete="off" />';
                $output .= '</td></tr>';    
            }
            
            $output .= '<tr><td>';
			$output .= _COMMENT_TEXT.':<br />';
            $output .= '<textarea id="comment_text" name="comment_text" maxlength="'.$comment_length.'" style="width:96%" rows="5">'.stripcslashes($comment_text).'</textarea><br />';
            $output .= _CAN_USE_TAGS_MSG.' &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;br&gt;';
            $output .= '</td></tr>';
            $output .= '<tr><td nowrap height="10px"></td></tr>';
            $output .= '</table>';
        
            if($image_verification == 'yes'){						
                $output .= '<table border="0" cellspacing="0" cellpadding="0">
                <tr><td colspan="3">'._IMAGE_VERIFICATION.':</td></tr>
                <tr>
                    <td><img style="padding:0px;margin:0px;" id="captcha_image" src="modules/captcha/securimage_show.php?sid='.md5(uniqid(time())).'" /></td>	
                    <td width="30px">
                        <img style="cursor:pointer;padding:0px;margin:0px;" id="captcha_image_reload" src="modules/captcha/images/refresh.gif" style="cursor:pointer;" onclick="document.getElementById(\'captcha_image\').src = \'modules/captcha/securimage_show.php?sid=\' + Math.random(); appSetFocus(\'frmComments_captcha_code\'); return false" title="'._REFRESH.'" alt="'._REFRESH.'" /><br />
                        <a href="modules/captcha/securimage_play.php"><img border="0" style="padding:0px;margin:0px;" id="captcha_image_play" src="modules/captcha/images/audio_icon.gif" title="'._PLAY.'" alt="'._PLAY.'" /></a>						
                    </td>					
                    <td>
                        '._TYPE_CHARS.'<br>
                        <input type="text" name="captcha_code" id="frmComments_captcha_code" style="width:175px" value="" maxlength="20" autocomplete="off" />
                    </td>
                </tr>
                <tr><td colspan="3" nowrap height="10px"></td></tr>
                </table>';						
            }

            $output .= '
			<input type="submit" '.($objLogin->IsLoggedInAsAdmin() ? 'disabled' : '').' class="form_button" name="btnSubmitPC" id="btnSubmitPC" value="'._PUBLISH_YOUR_COMMENT.'">
		</form>
		</div>';
		
		if($focus_field != '') $output .= '<script type="text/javascript">appSetFocus("'.$focus_field.'");</script>';
	
		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 *	Get number of comments awaiting moderation
	 */
	public static function AwaitingModerationCount()
	{
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_COMMENTS.' WHERE is_published = 0';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return $result[0]['cnt'];
		}
		return '0';
	}	
}
