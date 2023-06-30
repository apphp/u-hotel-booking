<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if($objLogin->IsLoggedInAs('owner','mainadmin')){
	
	$submition_type = isset($_POST['submition_type']) ? prepare_input($_POST['submition_type']) : '';
	$site_template  = isset($_POST['site_template']) ? prepare_input($_POST['site_template']) : '';
	$cron_type      = isset($_POST['cron_type']) ? prepare_input($_POST['cron_type']) : $objSettings->GetParameter('cron_type');
	$http_host 	    = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : _UNKNOWN;
	$language_id    = isset($_POST['sel_language_id']) ? prepare_input($_POST['sel_language_id']) : Languages::GetDefaultLang();
	$msg            = '';
	$focus_on_field = '';
	
	$objSiteDescription->LoadData($language_id);

	$params = array();
	$params['ssl_mode']             = isset($_POST['ssl_mode']) ? prepare_input($_POST['ssl_mode']) : $objSettings->GetParameter('ssl_mode');
	$params['seo_urls']             = isset($_POST['seo_urls']) ? prepare_input($_POST['seo_urls']) : $objSettings->GetParameter('seo_urls');
	$params['type_menu']            = isset($_POST['type_menu']) ? prepare_input($_POST['type_menu']) : $objSettings->GetParameter('type_menu');
	$params['rss_feed']             = isset($_POST['rss_feed']) ? prepare_input($_POST['rss_feed']) : $objSettings->GetParameter('rss_feed');
	$params['rss_feed_type']        = isset($_POST['rss_feed_type']) ? prepare_input($_POST['rss_feed_type']) : $objSettings->GetParameter('rss_feed_type');
	$params['is_offline']           = isset($_POST['is_offline']) ? prepare_input($_POST['is_offline']) : $objSettings->GetParameter('is_offline');
	$params['offline_message']      = isset($_POST['offline_message']) ? prepare_input($_POST['offline_message']) : $objSettings->GetParameter('offline_message');
	$params['caching_allowed']      = isset($_POST['caching_allowed']) ? prepare_input($_POST['caching_allowed']) : $objSettings->GetParameter('caching_allowed');
	$params['cache_lifetime']       = isset($_POST['cache_lifetime']) ? prepare_input($_POST['cache_lifetime']) : $objSettings->GetParameter('cache_lifetime');
	$params['wysiwyg_type']         = isset($_POST['wysiwyg_type']) ? prepare_input($_POST['wysiwyg_type']) : $objSettings->GetParameter('wysiwyg_type');

	$params_tab2a = array();
	$params_tab2a['header_text'] = isset($_POST['header_text']) ? strip_tags(prepare_input($_POST['header_text'], false, 'medium'), '<b><u><i>') : $objSiteDescription->GetParameter('header_text');
	$params_tab2a['slogan_text'] = isset($_POST['slogan_text']) ? prepare_input($_POST['slogan_text']) : $objSiteDescription->GetParameter('slogan_text');
	$params_tab2a['footer_text'] = isset($_POST['footer_text']) ? prepare_input($_POST['footer_text'], false, 'medium') : $objSiteDescription->GetParameter('footer_text');

	$params_tab2b = array();
	$params_tab2b['tag_title'] 	     = isset($_POST['tag_title']) ? prepare_input($_POST['tag_title']) : $objSiteDescription->GetParameter('tag_title');
	$params_tab2b['tag_description'] = isset($_POST['tag_description']) ? prepare_input($_POST['tag_description']) : $objSiteDescription->GetParameter('tag_description');
	$params_tab2b['tag_keywords']    = isset($_POST['tag_description']) ? prepare_input($_POST['tag_keywords']) : $objSiteDescription->GetParameter('tag_keywords');
	$apply_to_all_pages              = isset($_POST['apply_to_all_pages']) ? prepare_input($_POST['apply_to_all_pages']) : '';

    $params_tab2c = array();
	$params_tab2c['geographical_address'] = isset($_POST['geographical_address']) ? prepare_input($_POST['geographical_address']) : $objSiteDescription->GetParameter('geographical_address');

	$params_tab3 = array();
	$params_tab3['date_format']  = isset($_POST['date_format']) ? prepare_input($_POST['date_format']) : $objSettings->GetParameter('date_format');
	$params_tab3['time_zone']    = isset($_POST['time_zone']) ? prepare_input($_POST['time_zone']) : $objSettings->GetParameter('time_zone');
    $params_tab3['price_format'] = isset($_POST['price_format']) ? prepare_input($_POST['price_format']) : $objSettings->GetParameter("price_format");
    $params_tab3['week_start_day']  = isset($_POST['week_start_day']) ? prepare_input($_POST['week_start_day']) : $objSettings->GetParameter('week_start_day');

	$params_tab4 = array();
	$params_tab4['mailer'] 	      = isset($_POST['mailer']) ? prepare_input($_POST['mailer']) : $objSettings->GetParameter('mailer');
	$params_tab4['admin_email']   = isset($_POST['admin_email']) ? prepare_input($_POST['admin_email']) : $objSettings->GetParameter('admin_email');
	$params_tab4['mailer_type']   = isset($_POST['mailer_type']) ? prepare_input($_POST['mailer_type']) : $objSettings->GetParameter('mailer_type');
	$params_tab4['mailer_wysiwyg_type'] = isset($_POST['mailer_wysiwyg_type']) ? prepare_input($_POST['mailer_wysiwyg_type']) : $objSettings->GetParameter('mailer_wysiwyg_type');
	$params_tab4['smtp_secure']   = isset($_POST['smtp_secure']) ? prepare_input($_POST['smtp_secure']) : $objSettings->GetParameter('smtp_secure');
	$params_tab4['smtp_host']     = isset($_POST['smtp_host']) ? prepare_input($_POST['smtp_host']) : $objSettings->GetParameter('smtp_host');
	$params_tab4['smtp_port']     = isset($_POST['smtp_port']) ? prepare_input($_POST['smtp_port']) : $objSettings->GetParameter('smtp_port');
	$params_tab4['smtp_username'] = isset($_POST['smtp_username']) ? prepare_input($_POST['smtp_username']) : $objSettings->GetParameter('smtp_username');
	$params_tab4['smtp_password'] = isset($_POST['smtp_password']) ? prepare_input($_POST['smtp_password']) : '';

	$params_tab6 = array();
	$params_tab6['google_api'] = isset($_POST['google_api']) ? htmlspecialchars($_POST['google_api']) : $objSettings->GetParameter('google_api');

	$params_cron = array();
	$params_cron['cron_type']             = isset($_POST['cron_type']) ? prepare_input($_POST['cron_type']) : $objSettings->GetParameter('cron_type');
	$params_cron['cron_run_period']       = isset($_POST['cron_run_period']) ? prepare_input($_POST['cron_run_period']) : $objSettings->GetParameter('cron_run_period');
	$params_cron['cron_run_period_value'] = isset($_POST['cron_run_period_value']) ? prepare_input($_POST['cron_run_period_value']) : $objSettings->GetParameter('cron_run_period_value');

	// SAVE CHANGES			
	if($submition_type == 'general'){
		if(strlen($params['offline_message']) > 255){
			$msg_text = str_replace('_FIELD_', '<b>'._OFFLINE_MESSAGE.'</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '255', $msg_text);
			$msg = draw_important_message($msg_text, false);
			$focus_on_field = 'offline_message';
		}else{			
			if($objSettings->UpdateFields($params) == true){
				$msg = draw_success_message(_SETTINGS_SAVED, false);
			}else $msg = draw_important_message($objSettings->error, false);
		}
	}elseif($submition_type == 'clean_cache'){
		if(strtolower(SITE_MODE) == 'demo'){
			$msg = draw_important_message(_OPERATION_BLOCKED, false);
		}else{
			delete_cache();
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}
	}elseif($submition_type == 'visual_settings'){
		if($params_tab2a['header_text'] == ''){
			$msg = draw_important_message(_HEADER_IS_EMPTY, false);
			$focus_on_field = 'header_text';
		}elseif(strlen($params_tab2a['header_text']) > 255){
			$msg_text = str_replace('_FIELD_', '<b>'._HDR_HEADER_TEXT.'</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '255', $msg_text);
			$msg = draw_important_message($msg_text, false);
			$focus_on_field = 'header_text';
		}elseif(strlen($params_tab2a['slogan_text']) > 512){
			$msg_text = str_replace('_FIELD_', '<b>'._HDR_SLOGAN_TEXT.'</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '512', $msg_text);
			$msg = draw_important_message($msg_text, false);
			$focus_on_field = 'slogan_text';
		//}elseif($params_tab2a['footer_text'] == ''){
		//	$msg = draw_important_message(_FOOTER_IS_EMPTY, false);
		//	$focus_on_field = 'footer_text';
		}elseif(strlen($params_tab2a['footer_text']) > 512){
			$msg_text = str_replace('_FIELD_', '<b>'._HDR_FOOTER_TEXT.'</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '512', $msg_text);
			$msg = draw_important_message($msg_text, false);
			$focus_on_field = 'footer_text';
		}else{			
			if($objSiteDescription->UpdateFields($params_tab2a, $language_id) == true){
				$msg = draw_success_message(_SETTINGS_SAVED, false);
			}else $msg = draw_important_message($objSiteDescription->error, false);
		}		
	}elseif($submition_type == 'meta_tags'){
		if($params_tab2b['tag_title'] == ''){
			$msg = draw_important_message(_TAG_TITLE_IS_EMPTY, false);
			$params_tab2b['tag_title'] = $objSiteDescription->GetParameter('tag_title');
			$focus_on_field = 'tag_title';
		}elseif(strlen($params_tab2b['tag_title']) > 255){
			$msg_text = str_replace('_FIELD_', '<b>TITLE</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '255', $msg_text);
			$msg = draw_important_message($msg_text, false);
			$focus_on_field = 'tag_title';
		}elseif(strlen($params_tab2b['tag_keywords']) > 512){
			$msg_text = str_replace('_FIELD_', '<b>KEYWORDS</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '512', $msg_text);
			$msg = draw_important_message($msg_text, false);
			$focus_on_field = 'tag_keywords';
		}elseif(strlen($params_tab2b['tag_description']) > 512){
			$msg_text = str_replace('_FIELD_', '<b>DESCRIPTION</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '512', $msg_text);
			$msg = draw_important_message($msg_text, false);
			$focus_on_field = 'tag_description';
		}else{
			if($objSiteDescription->UpdateFields($params_tab2b, $language_id) == true){
				if($apply_to_all_pages == '1') Pages::UpdateMetaTags($params_tab2b, $language_id);
				$msg = draw_success_message(_SETTINGS_SAVED, false);	
			}else $msg = draw_important_message($objSiteDescription->error, false);			
		}
    }elseif($submition_type == 'language_settings'){
        if(strlen($params_tab2c['geographical_address']) > 255){
			$msg_text = str_replace('_FIELD_', '<b>'._GEOGRAPHICAL_ADDRESS.'</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '255', $msg_text);
			$msg = draw_important_message($msg_text, false);
			$focus_on_field = 'geographical_address';
		}else{
			if($objSiteDescription->UpdateFields($params_tab2c, $language_id) == true){
				$msg = draw_success_message(_SETTINGS_SAVED, false);
			}else $msg = draw_important_message($objSiteDescription->error, false);
		}
	}elseif($submition_type == 'date_time'){
		if($objSettings->UpdateFields($params_tab3) == true){
			$msg = draw_success_message(_SETTINGS_SAVED, false);
		}else $msg = draw_important_message($objSettings->error, false);
	}elseif($submition_type == 'test_smtp_connection'){
		if($params_tab4['admin_email'] == ''){
			$msg = draw_important_message(_ADMIN_EMAIL_IS_EMPTY, false);
			$focus_on_field = 'admin_email';
		}elseif(!check_email_address($params_tab4['admin_email'])){
			$msg = draw_important_message(_ADMIN_EMAIL_WRONG, false);
			$focus_on_field = 'admin_email';
		}else{			
			if(strtolower(SITE_MODE) == 'demo'){
				$msg = draw_important_message(_OPERATION_BLOCKED, false);
			}else{
				if($params_tab4['mailer'] == 'smtp'){
					$mail = new PHPMailer();
					$mail->IsSMTP(); // telling the class to use SMTP
					$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
															   // 1 = errors and messages
															   // 2 = messages only
					$mail->SMTPAuth   = true;                  // enable SMTP authentication
					$mail->SMTPSecure = (in_array($params_tab4['smtp_secure'], array('ssl', 'tls'))) ? $params_tab4['smtp_secure'] : ''; // sets the prefix to the server
					$mail->Host       = $params_tab4['smtp_host'];  
					$mail->Port       = $params_tab4['smtp_port'];  
					$mail->Username   = $params_tab4['smtp_username']; 
					$mail->Password   = ($params_tab4['smtp_password'] != '') ? $params_tab4['smtp_password'] : $objSettings->GetParameter('smtp_password');
					
					$mail->setLanguage(Application::Get('lang'));		// Set language
			
					$mail->SetFrom($params_tab4['admin_email']);    
					$mail->AddAddress($params_tab4['admin_email']); 
			
					$mail->Subject    = 'This is Test Email!';
					$mail->AltBody    = strip_tags('Hello Admin! This is a test email that you just have sent to check your connection parameters.');
					$mail->MsgHTML('Hello! This is test email.');
					if(!$mail->Send()){
						$msg = draw_important_message($mail->ErrorInfo, false);				
					}else{
						$msg = draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
					}					
				}else{
					$mail = new Email($params_tab4['admin_email'], $params_tab4['admin_email'], 'This is Test Email!'); 				
					$mail->textOnly = false;
					$mail->content = 'Hello! This is test email.';						
					$result = $mail->Send();
					if(!$result){
						if(version_compare(PHP_VERSION, '5.2.0', '>=')){	
							$err = error_get_last();
							if(!empty($err)){
								$msg = draw_important_message((isset($err['message']) ? $err['message'] : ''), false);	
							}
						}else{
							$msg = draw_important_message('PHPMail Error: an error occurred while sending email.', false);
						}
					}else{
						$msg = draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
					}					
				}
			}
		}
	}elseif($submition_type == 'email'){		
		if($params_tab4['admin_email'] == ''){
			$msg = draw_important_message(_ADMIN_EMAIL_IS_EMPTY, false);
			$focus_on_field = 'admin_email';
		}elseif(!check_email_address($params_tab4['admin_email'])){
			$msg = draw_important_message(_ADMIN_EMAIL_WRONG, false);
			$focus_on_field = 'admin_email';
        }elseif(strlen($params_tab4['admin_email']) > 70){
            $msg_text = str_replace('_FIELD_', '<b>E-mail address</b>', _FIELD_LENGTH_ALERT);
            $msg_text = str_replace('_LENGTH_', '70', $msg_text);
            $msg = draw_important_message($msg_text, false);
            $focus_on_field = 'tag_keywords';
		}
		
		if($msg == '' && $params_tab4['mailer'] == 'smtp'){
            if($params_tab4['smtp_host'] == ''){
                $msg = draw_important_message(str_replace('_FIELD_', '<b>SMTP Host</b>', _FIELD_CANNOT_BE_EMPTY), false);
                $focus_on_field = 'smtp_host';
            }elseif(strlen($params_tab4['smtp_host']) > 70){
                $msg_text = str_replace('_FIELD_', '<b>SMTP Host</b>', _FIELD_LENGTH_ALERT);
                $msg_text = str_replace('_LENGTH_', '70', $msg_text);
                $msg = draw_important_message($msg_text, false);
                $focus_on_field = 'tag_keywords';
            }elseif($params_tab4['smtp_port'] == ''){
                $msg = draw_important_message(str_replace('_FIELD_', '<b>SMTP Port</b>', _FIELD_CANNOT_BE_EMPTY), false);
                $focus_on_field = 'smtp_port';
            }elseif(strlen($params_tab4['smtp_port']) > 5){
                $msg_text = str_replace('_FIELD_', '<b>SMTP Port</b>', _FIELD_LENGTH_ALERT);
                $msg_text = str_replace('_LENGTH_', '5', $msg_text);
                $msg = draw_important_message($msg_text, false);
                $focus_on_field = 'tag_keywords';
            }elseif($params_tab4['smtp_username'] == ''){
                $msg = draw_important_message(str_replace('_FIELD_', '<b>SMTP Username</b>', _FIELD_CANNOT_BE_EMPTY), false);
                $focus_on_field = 'smtp_username';
            }elseif(strlen($params_tab4['smtp_username']) > 40){
                $msg_text = str_replace('_FIELD_', '<b>SMTP Username</b>', _FIELD_LENGTH_ALERT);
                $msg_text = str_replace('_LENGTH_', '40', $msg_text);
                $msg = draw_important_message($msg_text, false);
                $focus_on_field = 'tag_keywords';
            }elseif($params_tab4['smtp_password'] == ''){
                $msg = draw_important_message(str_replace('_FIELD_', '<b>SMTP Password</b>', _FIELD_CANNOT_BE_EMPTY), false);
                $focus_on_field = 'smtp_password';
            }elseif(strlen($params_tab4['smtp_password']) > 50){
                $msg_text = str_replace('_FIELD_', '<b>SMTP Password</b>', _FIELD_LENGTH_ALERT);
                $msg_text = str_replace('_LENGTH_', '50', $msg_text);
                $msg = draw_important_message($msg_text, false);
                $focus_on_field = 'tag_keywords';
            }				
		}
		
		if($msg == ''){
			if($objSettings->UpdateFields($params_tab4) == true){
				$msg = draw_success_message(_SETTINGS_SAVED, false);
			}else $msg = draw_important_message($objSettings->error, false);								
		}		
	}elseif($submition_type == 'templates'){
		if(!empty($site_template)){
			if($objSettings->SetTemplate($site_template) == true){
				delete_cache();
				$msg = draw_success_message(_SETTINGS_SAVED, false);
			}else $msg = draw_important_message($objSettings->error, false);									
		}else $msg = draw_important_message(_TEMPLATE_IS_EMPTY, false);
	}elseif($submition_type == 'google_api'){
		if(strlen($params_tab6['google_api']) > 70){
            $params_tab6['google_api'] = substr($params_tab6['google_api'], 0, 70);
			$msg_text = str_replace('_FIELD_', '<b>'._MAPPING_API_KEY.'</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '70', $msg_text);
            $msg = draw_important_message($msg_text, false);
            $focus_on_field = 'tag_keywords';
        }elseif($params_tab6['google_api'] == ''){
            $msg = draw_important_message(str_replace('_FIELD_', '<b>'._MAPPING_API_KEY.'</b>', _FIELD_CANNOT_BE_EMPTY), false);
            $focus_on_field = 'google_api';
        }elseif(!is_alpha_numeric($params_tab6['google_api'])){
			$msg_text = str_replace('_FIELD_', '<b>'._MAPPING_API_KEY.'</b>', _FIELD_MUST_BE_ALPHA_NUMERIC);
            $msg = draw_important_message($msg_text, false);
            $focus_on_field = 'google_api';
        }				
		if($msg == ''){
			if($objSettings->UpdateFields($params_tab6) == true){
				$msg = draw_success_message(_SETTINGS_SAVED, false);
			}else $msg = draw_important_message($objSettings->error, false);								
		}		
	}elseif($submition_type == 'site_info'){		
		$params_ranks = array();
		$params_ranks['alexa_rank'] = number_format((float)$objSettings->CheckAlexaRank($http_host));
		$params_ranks['google_indexed_pages'] = $objSettings->CheckGoogleIndexedPages($http_host);
		if($objSettings->UpdateFields($params_ranks) == true) $msg = draw_success_message(_CHANGES_WERE_SAVED, false);	
		else $msg = draw_important_message($objSettings->error, false);					
	}elseif($submition_type == 'cron_settings'){
		if($objSettings->UpdateFields($params_cron) == true) $msg = draw_success_message(_CHANGES_WERE_SAVED, false);	
		else $msg = draw_important_message($objSettings->error, false);
	}
	
	$template = $objSettings->GetTemplate();	
	if(strtolower(SITE_MODE) != 'demo' && $submition_type == 'general' || $submition_type == 'visual_settings' || $submition_type == 'meta_tags'){
		$objSiteDescription->LoadData();
		RSSFeed::UpdateFeeds();	
	}

}
