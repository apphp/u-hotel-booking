<?php

/**
 *	Class ContactUs
 *  -------------- 
 *  Description : encapsulates contact us methods and properties
 *	Written by  : ApPHP
 *	Version     : 1.0.2
 *  Updated	    : 04.02.2014
 *	Usage       : Core
 *	Differences : no
 *
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             Instance
 *	__destruct
 *	DrawContactUsForm
 *
 *  1.0.2
 *      - <font> replaced with <span>
 *      - added maxlength attribute for textarea
 *      - removed style="" from textarea
 *      -
 *      -
 *  1.0.1
 *  	- fixed bug for PHP < 5.3 in Instance()
 *  	- changed return value in DrawContactUsForm
 *  	- changed email function
 *  	- added ModulesSettings::Get()
 *  	- added check for message max length
 *	
 **/

class ContactUs {
	
	private static $instance;

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		

	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Draws Contact Us form
	 * @param $draw
	 */
	public function DrawContactUsForm($draw = true)
	{		
		global $objSettings, $objSiteDescription, $objLogin;
	
	    $align_left = Application::Get('defined_left');
		$align_right = Application::Get('defined_right');
		if(!Modules::IsModuleInstalled('contact_us')) return '';
		
		$output = '';
		$from_email = $objSettings->GetParameter('admin_email');

		$admin_email  = ModulesSettings::Get('contact_us', 'email');
		$delay_length = ModulesSettings::Get('contact_us', 'delay_length');		
		$is_send_delay = ModulesSettings::Get('contact_us', 'is_send_delay');		
		$image_verification = ModulesSettings::Get('contact_us', 'image_verification_allow');
		
		$focus_element = '';
		
		// post fields
		$task             = isset($_POST['task']) ? prepare_input($_POST['task']) : '';		
		$first_name       = isset($_POST['first_name']) ? prepare_input($_POST['first_name']) : '';
		$last_name        = isset($_POST['last_name']) ? prepare_input($_POST['last_name']) : '';
		$email            = isset($_POST['email']) ? prepare_input($_POST['email']) : '';
		$phone            = isset($_POST['phone']) ? prepare_input($_POST['phone']) : '';
		$subject          = isset($_POST['subject']) ? prepare_input($_POST['subject']) : '';
		$message          = isset($_POST['message']) ? prepare_input($_POST['message']) : '';
		$captcha_code 	  = isset($_POST['captcha_code']) ? prepare_input($_POST['captcha_code']) : '';
		$msg              = '';
		$contact_mail_sent = (bool)Session::Get('contact_mail_sent');
		$contact_mail_sent_time = Session::Get('contact_mail_sent_time');

        if($image_verification == 'yes'){
			include_once('modules/captcha/securimage.php');
			$objImg = new Securimage();			
		}

		if($task == 'contact'){
			$time_elapsed = (time_diff($contact_mail_sent_time, date('Y-m-d H:i:s')));
			if($contact_mail_sent && $is_send_delay == 'yes' && $time_elapsed < $delay_length){
				$msg = draw_message(str_replace('_WAIT_', $delay_length - $time_elapsed, _CONTACT_US_ALREADY_SENT), false);
			}else{			
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
				}elseif($subject == ''){        
					$msg = draw_important_message(_SUBJECT_EMPTY_ALERT, false);
					$focus_element = 'subject';
				#}elseif($phone == ''){        
				#	$msg = draw_important_message(str_replace('_FIELD_', _PHONE, _FIELD_CANNOT_BE_EMPTY), false);
				#	$focus_element = 'phone';
				}elseif($message == ''){        
					$msg = draw_important_message(_MESSAGE_EMPTY_ALERT, false);
					$focus_element = 'message';
				}elseif(strlen($message) > 1024){        
					$msg = draw_important_message(str_replace(array('_FIELD_', '_LENGTH_'), array('<b>'._MESSAGE.'</b>', 1024), _FIELD_LENGTH_EXCEEDED), false);
					$focus_element = 'message';					
				}elseif(($image_verification == 'yes') && !$objImg->check($captcha_code)){
					$msg = draw_important_message(_WRONG_CODE_ALERT, false);
					$focus_element = 'captcha_code';
				}
				
				// deny all operations in demo version
				if(strtolower(SITE_MODE) == 'demo'){
					$msg = draw_important_message(_OPERATION_BLOCKED, false);
				}						

				if($msg == ''){            
					////////////////////////////////////////////////////////////
					send_email_wo_template(
						$admin_email,
						$from_email,
						'Question from visitor (via Contact Us - '.$objSiteDescription->GetParameter('header_text').')',
						_FIRST_NAME.': '.str_replace('\\', '', $first_name).'<br />'.
						_LAST_NAME.': '.str_replace('\\', '', $last_name).'<br />'.
						_EMAIL_ADDRESS.': '.str_replace('\\', '', $email).'<br />'.
						_PHONE.': '.str_replace('\\', '', $phone).'<br />'.
						_SUBJECT.': '.str_replace('\\', '', $subject).'<br />'.
						_MESSAGE.': '.str_replace('\\', '', $message)
					);
					////////////////////////////////////////////////////////////
			
					$msg = draw_success_message(_CONTACT_US_EMAIL_SENT, false);
					Session::Set('contact_mail_sent', true);
					Session::Set('contact_mail_sent_time', date('Y-m-d H:i:s'));
					
					$first_name = $last_name = $email = $phone = $subject = $message = '';
				}
			}
		}

		$output .= (($msg != '') ? $msg.'<br />' : '').'
        <form method="post" name="frmContactUs" id="frmContactUs">
			'.draw_hidden_field('task', 'contact', false).'
			'.draw_token_field(false).'
			
		    <table class="tblContactUs" border="0" width="99%">
		    <tbody>
		    <tr>
			    <td width="25%" align="'.$align_right.'">'._FIRST_NAME.':</td>
			    <td><span class="mandatory_star">*</span></td>
			    <td nowrap="nowrap" align="'.$align_left.'"><input type="text" id="first_name" name="first_name" size="34" maxlength="40" value="'.decode_text($first_name).'" autocomplete="off" /></td>
		    </tr>
		    <tr>
			    <td align="'.$align_right.'">'._LAST_NAME.':</td>
			    <td><span class="mandatory_star">*</span></td>
			    <td nowrap="nowrap" align="'.$align_left.'"><input type="text" id="last_name" name="last_name" size="34" maxlength="40" value="'.decode_text($last_name).'" autocomplete="off" /></td>
		    </tr>
		    <tr>
                <td align="'.$align_right.'">'._EMAIL_ADDRESS.':</td>
                <td><span class="mandatory_star">*</span></td>
                <td nowrap="nowrap" align="'.$align_left.'"><input type="text" id="email" name="email" size="34" maxlength="70" value="'.decode_text($email).'" autocomplete="off"  /></td>
		    </tr>
		    <tr>
                <td align="'.$align_right.'">'._PHONE.':</td>
                <td></td>
                <td nowrap="nowrap" align="'.$align_left.'"><input type="text" id="phone" name="phone" size="22" maxlength="40" value="'.decode_text($phone).'" autocomplete="off"  /></td>
		    </tr>
		    <tr>
                <td align="'.$align_right.'">'._SUBJECT.':</td>
                <td><span class="mandatory_star">*</span></td>
                <td nowrap="nowrap" align="'.$align_left.'"><input type="text" id="subject" name="subject" maxlength="128" value="'.decode_text($subject).'" autocomplete="off"  /></td>
		    </tr>
		    <tr valign="top">
                <td align="'.$align_right.'">'._MESSAGE.':</td>
                <td><span class="mandatory_star">*</span></td>
                <td nowrap="nowrap" align="'.$align_left.'">
                    <textarea id="message" name="message" maxlength="1024" rows="8">'.$message.'</textarea>                
                </td>
		    </tr>
			<tr>
				<td colspan="2"></td>
				<td>';				
					if($image_verification == 'yes'){
						$output .= '<table border="0">
						<tr>
							<td>
								<img id="captcha_image" src="'.APPHP_BASE.'modules/captcha/securimage_show.php?sid='.md5(uniqid(time())).'" />
							</td>	
							<td width="30px" align="center">
								<img style="cursor:pointer; padding:0px; margin:0px;" id="captcha_image_reload"
								src="modules/captcha/images/refresh.gif" style="cursor:pointer;"
								onclick="document.getElementById(\'captcha_image\').src = \'modules/captcha/securimage_show.php?sid=\' + Math.random(); appSetFocus(\'captcha_code\'); return false;" title="'._REFRESH.'" alt="'._REFRESH.'" /><br />
								<a href="modules/captcha/securimage_play.php"><img border="0" style="padding:0px; margin:0px;" id="captcha_image_play" src="modules/captcha/images/audio_icon.gif" title="'._PLAY.'" alt="'._PLAY.'" /></a>						
							</td>					
							<td align="left">
								'._TYPE_CHARS.'<br />								
								<input type="text" name="captcha_code" id="captcha_code" style="width:175px;margin-top:5px;" value="" maxlength="20" autocomplete="off" />
							</td>
						</tr>
						</table>';
					}				
				$output .= '</td>
			</tr>
		    <tr><td height="25" nowrap colspan="3"></td></tr>            
		    <tr>
				<td colspan="3" align="center">
					<input type="submit" '.($objLogin->IsLoggedInAsAdmin() ? 'disabled' : '').' class="form_button" name="btnSubmitPD" id="btnSubmitPD" value="'._SEND.'" />
				</td>
		    </tr>
		    <tr><td height="25" nowrap colspan="3"></td></tr>            
		    </table>
		</form>';
		if($focus_element != ''){
			$output .= '<script type="text/javascript">appSetFocus(\''.$focus_element.'\');</script>';
		}

		if($draw) echo $output;
        else return $output;      
    }
	
	/**
	 *	Return instance of the class
	 */
	public static function Instance()
	{
		if(self::$instance == null) self::$instance = new ContactUs();
		return self::$instance;
	}
	
}
