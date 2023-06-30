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
	
    define ('TABS_DIR', 'modules/tabs/');
    require_once(TABS_DIR.'tabs.class.php');

	$tabid = isset($_REQUEST['tabid']) ? prepare_input($_REQUEST['tabid']) : '1_1';
	$tab_content_1 = '';
	$tab_content_2 = '';
	$tab_content_3 = '';
	$tab_content_4 = '';
	$tab_content_5 = ''; 
	$tab_content_6 = '';
	$tab_content_7 = '';
	$tab_content_8 = ''; 
	$tab_content_9 = ''; 
	
	if($tabid == '1_1') {
		
		$tab_content_1 = '<form action="index.php?admin=settings" method="post">
			'.draw_hidden_field('submition_type', 'general', false).'
			'.draw_hidden_field('tabid', $tabid, false).'
			'.draw_hidden_field('rss_feed', '1', false).'
			'.draw_token_field(false).'
			
			<table border="0" class="tabs_table">
			<tr valign="top">
				<td width="150px">'._SITE_OFFLINE.': <img class="help" src="images/question_mark.png" title="'._SITE_OFFLINE_ALERT.'" alt="" /></td>
				<td colspan="2">
					<select class="mgrid_select" name="is_offline">
						<option '.(($params['is_offline'] == '0') ? 'selected="selected"' : '').' value="0">'._NO.'</option>
						<option '.(($params['is_offline'] == '1') ? 'selected="selected"' : '').' value="1">'._YES.'</option>
					</select>		
				</td>
			</tr>
			<tr valign="top">
				<td>'._OFFLINE_MESSAGE.': <img class="help" src="images/question_mark.png" title="'._SITE_OFFLINE_MESSAGE_ALERT.'" alt="" /></td>
				<td colspan="2">
					<textarea class="mgrid_textarea" name="offline_message" cols="54" rows="3">'.decode_text($params['offline_message']).'</textarea>
					<br /><span>'._OFFLINE_LOGIN_ALERT.'</span>
				</td>
			</tr>
		    <tr valign="top">
			    <td width="150px">'._FORCE_SSL.': <img class="help" src="images/question_mark.png" title="'._FORCE_SSL_ALERT.'" alt="" /></td>
			    <td colspan="2">
				    <select class="mgrid_select" name="ssl_mode">
					    <option '.(($params["ssl_mode"] == "0") ? "selected" : "").' value="0">'._NO.'</option>
					    <option '.(($params["ssl_mode"] == "1") ? "selected" : "").' value="1">'._ENTIRE_SITE.'</option>
						<option '.(($params["ssl_mode"] == "2") ? "selected" : "").' value="2">'._ADMINISTRATOR_ONLY.'</option>
						<option '.(($params["ssl_mode"] == "3") ? "selected" : "").' value="3">'._CUSTOMER_PAYMENT_MODULES.'</option>
				    </select>
			    </td>
		    </tr>
			<tr valign="top">
				<td>'._SEO_URLS.': <img class="help" src="images/question_mark.png" title="'._SEO_LINKS_ALERT.'" alt="" /></td>
				<td colspan="2">
					<select class="mgrid_select" name="seo_urls">
						<option '.(($params['seo_urls'] == '1') ? 'selected="selected"' : '').' value="1">'._YES.'</option>
						<option '.(($params['seo_urls'] == '0') ? 'selected="selected"' : '').' value="0">'._NO.'</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td>'._TYPE_MENU.': </td>
				<td colspan="2">
					<select class="mgrid_select" name="type_menu">
						<option '.(($params['type_menu'] == 'horizontal') ? 'selected="selected"' : '').' value="horizontal">'._HORIZONTAL.'</option>
						<option '.(($params['type_menu'] == 'vertical') ? 'selected="selected"' : '').' value="vertical">'._VERTICAL.'</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td>'._WYSIWYG_EDITOR.':</td>
				<td colspan="2">
					<select class="mgrid_select" name="wysiwyg_type">
						<option '.(($params['wysiwyg_type'] == 'none') ? 'selected="selected"' : '').' value="none">'._NONE.'</option>
						<option '.(($params['wysiwyg_type'] == 'tinymce') ? 'selected="selected"' : '').' value="tinymce">TinyMCE</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td>'._RSS_FEED_TYPE.': </td>
				<td colspan="2">
					<select class="mgrid_select" name="rss_feed_type" id="rss_feed_type" '.((!$params['rss_feed']) ? 'disabled="disabled"' : '').'>
						<option '.(($params['rss_feed_type'] == 'rss1') ? 'selected="selected"' : '').' value="rss1">RSS 1.0</option>
						<option '.(($params['rss_feed_type'] == 'rss2') ? 'selected="selected"' : '').' value="rss2">RSS 2.0</option>
						<option '.(($params['rss_feed_type'] == 'atom') ? 'selected="selected"' : '').' value="atom">Atom</option>
					</select>					
				</td>
			</tr>
			<tr valign="top">
				<td>'._CACHING.': </td>
				<td colspan="2">
					<select class="mgrid_select" name="caching_allowed" onchange="appToggleElementView(this.value, \'0\', \'cache_lifetime_row\')">
						<option '.(($params['caching_allowed'] == '1') ? 'selected="selected"' : '').' value="1">'._YES.'</option>
						<option '.(($params['caching_allowed'] == '0') ? 'selected="selected"' : '').' value="0">'._NO.'</option>
					</select>
					&nbsp;
					<a href="javascript:void(\'clean|cache\')" onclick="cleanCacheSubmit()">[ '._CLEAN_CACHE.' ]</a>
				</td>
			</tr>
			<tr valign="top" id="cache_lifetime_row" style="'.(($params['caching_allowed'] == '1') ? '' : 'display:none').';">
				<td>'._CACHE_LIFETIME.': </td>
				<td colspan="2">'.draw_numbers_select_field('cache_lifetime', $params['cache_lifetime'], 1, 60, 1, '', '', false).' '._MINUTES.'.</td>
			</tr>
			<tr><td colspan="3" nowrap="nowrap" height="5px"></td></tr>
			<tr>
				<td colspan="3"><input class="form_button" type="submit" name="btnSubmit" value="'._BUTTON_CHANGE.'"></td>
			</tr>
			</table>
			</form>';		

	}elseif($tabid == '1_2') {
		
		$tab_content_2 = '<form name="frmSettings" id="frmSettings" action="index.php?admin=settings" method="post">
				'.draw_hidden_field('tabid', $tabid, false).'
				'.draw_token_field(false).'
				
				<table width="99%" border="0" class="tabs_table">
				<tr valign="top">
					<td width="150px">'._LANGUAGE.':</td>
					<td>';				
					$all_languages = Languages::GetAllActive();		
					$tab_content_2 .= draw_languages_box('sel_language_id', $all_languages[0], 'abbreviation', 'lang_name', $language_id, 'mgrid_select', 'onchange="javascript:appFormSubmit(\'frmSettings\');"', false);
					$tab_content_2 .= '</td>
				</tr>
				</table>
			</form>

			<fieldset style="margin:10px;">
			<legend>'._HEADERS_AND_FOOTERS.'</legend>
				<form action="index.php?admin=settings" method="post">
				'.draw_hidden_field('submition_type', 'visual_settings', false).'
				'.draw_hidden_field('tabid', $tabid, false).'
				'.draw_hidden_field('sel_language_id', $language_id, false).'
				'.draw_token_field(false).'
				
				<table width="99%" border="0" class="tabs_table">
				<tr valign="top">
					<td width="150px">'._HDR_HEADER_TEXT.' <span class="required">*</span>:</td>
					<td><textarea class="mgrid_textarea" name="header_text" id="header_text" cols="54" rows="3">'.decode_text($params_tab2a['header_text']).'</textarea></td>
				</tr>
				<tr valign="top">
					<td width="150px">'._HDR_SLOGAN_TEXT.':</td>
					<td><textarea class="mgrid_textarea" name="slogan_text" id="slogan_text" cols="54" rows="3">'.decode_text($params_tab2a['slogan_text']).'</textarea></td>
				</tr>
				<tr valign="top">
					<td width="150px">'._HDR_FOOTER_TEXT.':</td>
					<td><textarea class="mgrid_textarea" name="footer_text" id="footer_text" cols="54" rows="3">'.decode_text($params_tab2a['footer_text']).'</textarea></td>
				</tr>
				<tr><td colspan="2" nowrap height="5px"></td></tr>
				<tr>
					<td colspan="2"><input class="form_button" type="submit" name="btnSubmit" value="'._BUTTON_CHANGE.'"></td>
				</tr>
				</table>
			</form>
			</fieldset>
			
			<br />			
		
			<fieldset style="margin:10px;">
			<legend>'._META_TAGS.'</legend>
				<form action="index.php?admin=settings" method="post">
				'.draw_hidden_field('submition_type', 'meta_tags', false).'
				'.draw_hidden_field('tabid', $tabid, false).'
				'.draw_hidden_field('sel_language_id', $language_id, false).'				
				'.draw_token_field(false).'
				
				<table border="0" class="tabs_table">
				<tr valign="top">
					<td width="150px">'._TAG.' &lt;TITLE&gt; <span class="required">*</span>:</td>
					<td><textarea class="mgrid_textarea" name="tag_title" id="tag_title" cols="54" rows="2">'.$params_tab2b['tag_title'].'</textarea></td>
				</tr>
				<tr valign="top">
					<td width="150px">'._META_TAG.' &lt;KEYWORDS&gt;:</td>
					<td><textarea class="mgrid_textarea" name="tag_keywords" id="tag_keywords" cols="54" rows="3">'.$params_tab2b['tag_keywords'].'</textarea></td>
				</tr>
				<tr valign="top">
					<td width="150px">'._META_TAG.' &lt;DESCRIPTION&gt;:</td>
					<td><textarea class="mgrid_textarea" name="tag_description" id="tag_description" cols="54" rows="3">'.$params_tab2b['tag_description'].'</textarea></td>
				</tr>
				<tr>
					<td style="padding-left:5px;"></td>
					<td>
						<input type="checkbox" class="form_checkbox" name="apply_to_all_pages" id="apply_to_all_pages" value="1">
						<label for="apply_to_all_pages">'._APPLY_TO_ALL_PAGES.'</label>
					</td>
				</tr>
				<tr><td colspan="2" nowrap height="5px"></td></tr>
				<tr>
					<td colspan="2"><input class="form_button" type="submit" name="btnSubmit" value="'._BUTTON_CHANGE.'"></td>
				</tr>
				</table>
			</form>
			</fieldset>
			
			<br />			
		
			<fieldset style="margin:10px;">
                <legend>'._OTHER.'</legend>
                <form action="index.php?admin=settings" method="post">
                '.draw_hidden_field('submition_type', 'language_settings', false).'
                '.draw_hidden_field('tabid', $tabid, false).'
                '.draw_hidden_field('sel_language_id', $language_id, false).'
                '.draw_token_field(false).'
                
                    <table width="99%" border="0" class="tabs_table">
                        <tr valign="top">
                            <td width="150px">'._GEOGRAPHICAL_ADDRESS.': </td>
                            <td colspan="2">
                                <textarea class="mgrid_textarea" name="geographical_address" cols="54" rows="3" maxlength="255">'.decode_text($params_tab2c['geographical_address']).'</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><input class="form_button" type="submit" name="btnSubmit" value="'._BUTTON_CHANGE.'"></td>
                        </tr>
                    </table>
                </form>
			</fieldset>';

	}elseif($tabid == '1_3') {

		$tab_content_3 = '<form action="index.php?admin=settings" method="post">
			'.draw_hidden_field('submition_type', 'date_time', false).'
			'.draw_hidden_field('tabid', $tabid, false).'
			'.draw_token_field(false).'
		
			<table border="0" class="tabs_table">
			<tr valign="top">
				<td width="150px">'._DATE_FORMAT.': </td>
				<td colspan="2">
					<select class="mgrid_select" name="date_format">
						<option '.(($params_tab3['date_format'] == 'dd/mm/yyyy') ? 'selected="selected"' : '').' value="dd/mm/yyyy">dd/mm/yyyy</option>
						<option '.(($params_tab3['date_format'] == 'mm/dd/yyyy') ? 'selected="selected"' : '').' value="mm/dd/yyyy">mm/dd/yyyy (american)</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td width="150px">'._TIME_ZONE.': </td>
				<td colspan="2">
					<select class="mgrid_select" name="time_zone">';
					$arr_time_zones = get_timezones_array();
					foreach($arr_time_zones as $key => $val){
						$tab_content_3 .= '<option '.(($params_tab3['time_zone'] == $key) ? 'selected="selected"' : '').' value="'.$key.'">'.$val.'</option>';
					}
					$tab_content_3 .= '</select>
				</td>
			</tr>			
		    <tr valign="top">
				<td>'._WEEK_START_DAY.': </td>
				<td colspan="2">
					<select class="mgrid_select" name="week_start_day">';
					for($i=0; $i < 7; $i++){
						$tab_content_3 .= '<option '.(($params_tab3['week_start_day'] == ($i+1)) ? 'selected="selected"' : '').' value="'.($i+1).'">'.get_weekday_local($i+1).'</option>';
					}
					$tab_content_3 .= '</select>
				</td>			
		    </tr>
		    <tr valign="top">
				<td>'._PRICE_FORMAT.': <img class="help" src="images/question_mark.png" title="'._PRICE_FORMAT_ALERT.'" alt="" /></td>
				<td colspan="2">
					<select class="mgrid_select" name="price_format">
					<option '.(($params_tab3['price_format'] == 'european') ? 'selected="selected"' : '').' value="european">1.234,00 (european)</option>
					<option '.(($params_tab3['price_format'] == 'american') ? 'selected="selected"' : '').' value="american">1,234.00 (american)</option>
					</select>
				</td>
			</tr>			
			<tr><td colspan="3" nowrap height="5px"></td></tr>
			<tr>
				<td	colspan="3"><input class="form_button" type="submit" name="btnSubmit" value="'._BUTTON_CHANGE.'"></td>
			</tr>
			</table>
		</form>';		

	}elseif($tabid == '1_4') {
		
		$tab_content_4 = '<form id="frmEmailSettings" action="index.php?admin=settings" method="post">
			'.draw_hidden_field('submition_type', 'email', false).'
			'.draw_hidden_field('tabid', $tabid, false).'
			'.draw_token_field(false).'
		
			<table width="99%" border="0" class="tabs_table">
			<tr valign="top">
				<td width="90px">'._MAILER.': <img class="help" src="images/question_mark.png" title="'._ADMIN_MAILER_ALERT.'" alt="" /></td>
				<td width="160px">
					<input class="form_radio" type="radio" name="mailer" id="mailer_php" '.(($params_tab4['mailer'] == 'php') ? 'checked="checked"' : '').' value="php" onclick="appHideElement(\'mailer_smtp_row\');appShowElement(\'mailer_php_row\');" /> <label for="mailer_php">PHP Mail Function</label><br />
					<input class="form_radio" type="radio" name="mailer" id="mailer_smtp" '.(($params_tab4['mailer'] == 'smtp') ? 'checked="checked"' : '').' value="smtp" onclick="appHideElement(\'mailer_php_row\');appShowElement(\'mailer_smtp_row\');" /> <label for="mailer_smtp">SMTP</label><br />
				</td>
				<td style="border-left:1px solid #d1d2d3;padding:2px 18px 2px 18px" rowspan="2">
					<table border="0" class="tabs_table">
					<tr valign="top">
						<td width="150px">'._EMAIL_ADDRESS.'<span class="required">*</span>: <img class="help" src="images/question_mark.png" title="'._ADMIN_EMAIL_ALERT.'" alt="" /></td>
						<td align="left">
							<input class="mgrid_text" type="text" name="admin_email" id="admin_email" size="25" maxlength="70" value="'.$params_tab4['admin_email'].'" />
						</td>
					</tr>
					</table>
					
					<table id="mailer_php_row" style="'.(($params_tab4['mailer'] == 'php') ? '' : 'display:none;').'" border="0" class="tabs_table">
					<tr valign="top">
						<td width="150px">'._TYPE.':</td>
						<td align="left">
							<select class="mgrid_select" name="mailer_type">
								<option '.(($params_tab4['mailer_type'] == 'php_mail_standard') ? 'selected="selected"' : '').' value="php_mail_standard">'._STANDARD.'</option>
								<option '.(($params_tab4['mailer_type'] == 'php_mail_simple') ? 'selected="selected"' : '').' value="php_mail_simple">'._SIMPLE.'</option>	
							</select>										
						</td>
					</tr>
					<tr><td colspan="2" nowrap height="15px"></td></tr>
					</table>
					<table id="mailer_smtp_row" style="'.(($params_tab4['mailer'] == 'smtp') ? '' : 'display:none;').'" border="0" class="tabs_table">
					<tr valign="top">
						<td width="150px">'._SMTP_SECURE.' <span class="required">*</span>:</td>
						<td align="left">
							<select class="mgrid_select" name="smtp_secure">
								<option '.(($params_tab4['smtp_secure'] == 'ssl') ? 'selected="selected"' : '').' value="ssl">SSL</option>
								<option '.(($params_tab4['smtp_secure'] == 'tls') ? 'selected="selected"' : '').' value="tls">TLS</option>
								<option '.(($params_tab4['smtp_secure'] == 'no') ? 'selected="selected"' : '').' value="no">'._NO.'</option>	
							</select>																
						</td>
					</tr>
					<tr valign="top">
						<td width="150px">'._SMTP_HOST.' <span class="required">*</span>: <img class="help" src="images/question_mark.png" title="Ex.: smtp.gmail.com" alt="" /></td>
						<td align="left"><input class="mgrid_text" type="text" name="smtp_host" id="smtp_host" size="20" maxlength="70" value="'.$params_tab4['smtp_host'].'" /></td>
					</tr>
					<tr valign="top">
						<td width="150px">'._SMTP_PORT.' <span class="required">*</span>: <img class="help" src="images/question_mark.png" title="Ex.: 465 or 587" alt="" /></td>
						<td align="left"><input class="mgrid_text" type="text" name="smtp_port" id="smtp_port" size="6" maxlength="6" value="'.$params_tab4['smtp_port'].'" /></td>
					</tr>
					<tr valign="top">
						<td width="150px">'._USERNAME.' <span class="required">*</span>:</td>
						<td align="left"><input class="mgrid_text" type="text" name="smtp_username" id="smtp_username" size="20" maxlength="40" value="'.$params_tab4['smtp_username'].'" autocomplete="off" /></td>
					</tr>
					<tr valign="top">
						<td width="150px">'._PASSWORD.' <span class="required">*</span>:</td>
						<td align="left"><input class="mgrid_text" type="password" name="smtp_password" id="smtp_password" size="20" maxlength="50" value="" '.($objSettings->GetParameter('smtp_password') ? 'placeholder="&bull;&bull;&bull;&bull;&bull;"' : '').' autocomplete="off" /></td>
					</tr>
					</table>
					
					<table border="0" class="tabs_table">
					<tr>
						<td width="150px"></td>
						<td><input class="form_button" type="button" name="btnSubmit" value="'._TEST_EMAIL.'" onclick="javascript:sendTestEmail(this)" /></td>
					</tr>
					</table>					 
				</td>
			</tr>
			<tr valign="top">
				<td width="90px">'._EMAIL_TEMPLATES_EDITOR.':</td>
				<td width="160px">
					<select class="mgrid_select" name="mailer_wysiwyg_type">
						<option '.(($params_tab4['mailer_wysiwyg_type'] == 'none') ? 'selected="selected"' : '').' value="none">'._NONE.'</option>
						<option '.(($params_tab4['mailer_wysiwyg_type'] == 'tinymce') ? 'selected="selected"' : '').' value="tinymce">TinyMCE</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input class="form_button" type="submit" name="btnSubmit" value="'._BUTTON_CHANGE.'" /></td>
			</tr>
			</table>
		</form>';			

	}elseif($tabid == '1_5') {
		
		// Load XML file
		if(@file_exists('templates/'.$template.'/info.xml')) {
			$xml = simplexml_load_file('templates/'.$template.'/info.xml');		 
		}
		$template_name = isset($xml->name) ? $xml->name : _UNKNOWN;
		$template_icon = isset($xml->icon) ? 'templates/'.$template.'/'.$xml->icon : 'images/no_image.png';
		$template_direction = isset($xml->direction) ? $xml->direction : _UNKNOWN;
		$template_description = isset($xml->description) ? $xml->description : _UNKNOWN;
		$template_license = isset($xml->license) ? $xml->license : _UNKNOWN;
		$template_version = isset($xml->version) ? $xml->version : _UNKNOWN;
		$template_layout = isset($xml->layout) ? $xml->layout : _UNKNOWN;
        $template_author = isset($xml->author) ? $xml->author: '';
        $template_author_url = isset($xml->authorUrl) ? $xml->authorUrl : '';       
        
		$template_menus = '';
		if(isset($xml->menus->menu)){
			foreach($xml->menus->menu as $menu){
				$template_menus .= (($template_menus != '') ? ',' : '').$menu;
			}
		}			
		
		$tab_content_5 = '<form action="index.php?admin=settings" method="post">
			'.draw_hidden_field('submition_type', 'templates', false).'
			'.draw_hidden_field('tabid', $tabid, false).'
			'.draw_token_field(false).'
			
			<table width="99%" border="0" class="tabs_table">
			<tr valign="top">
				<td width="90px">'._HDR_TEMPLATE.' <span class="required">*</span>:</td>
				<td width="160px">
					<select class="mgrid_select" name="site_template" onchange="change_icon(this.value,\''.Application::Get('token').'\')">
					<option value="">-- '._SELECT.' --</option>';					
						// prepare templates
						$arr_templates = @read_directory_subfolders('templates/'); 
						foreach($arr_templates as $key){
							$tab_content_5 .= "<option ".(($template == $key) ? "selected" : "")." value='".$key."'>".ucfirst($key)."</option>";
						}
						$tab_content_5 .= '
					</select>
				</td>
				<td width="240px" style="border-left:1px solid #d1d2d3; padding:2px 28px 2px 28px">
					<img id="template_icon" src="'.$template_icon.'" style="border:1px solid #cccccc" alt="" width="240" height="180px" />				
				</td>
				<td>
					<img class="loading_img" src="images/ajax_loading.gif" alt="loading..." />
					<b>'._NAME.'</b>: <span id="template_name">'.$template_name.'</span><br />
					<b>'._DESCRIPTION.'</b>: <span id="template_description">'.$template_description.'</span><br />';
                    if($template_author != ''){
                        $tab_content_5 .= '<b>Author</b>: ';
                        $tab_content_5 .= '<span id="template_author">'.$template_author.'</span><br />';   
                    }
                    
					$tab_content_5 .= '<b>'._LICENSE.'</b>: <span id="template_license">'.$template_license.'</span><br />
					<b>'._VERSION.'</b>: <span id="template_version">'.$template_version.'</span><br />
					<b>'._LAYOUT.'</b>: <span id="template_layout">'.$template_layout.'</span><br />
					<b>'._HDR_TEXT_DIRECTION.'</b>: <span id="template_direction">'.$template_direction.'</span><br />
					<b>'._MENUS.'</b>: <span id="template_menus">'.$template_menus.'</span><br />
				</td>				
			</tr>
			<tr>
				<td colspan="4"><input class="form_button" type="submit" name="btnSubmit" value="'._BUTTON_CHANGE.'"></td>
			</tr>
			</table>
		</form>';
		
	}elseif($tabid == '1_6') {
		
		// Load XML file
		if(@file_exists('templates/'.$template.'/info.xml')) {
			$xml = simplexml_load_file('templates/'.$template.'/info.xml');		 
		}
		$template_name = isset($xml->name) ? $xml->name : _UNKNOWN;
		$template_icon = isset($xml->icon) ? 'templates/'.$template.'/'.$xml->icon : 'images/no_image.png';
		$template_direction = isset($xml->direction) ? $xml->direction : _UNKNOWN;
		$template_description = isset($xml->description) ? $xml->description : _UNKNOWN;
		$template_license = isset($xml->license) ? $xml->license : _UNKNOWN;
		$template_version = isset($xml->version) ? $xml->version : _UNKNOWN;
		$template_layout = isset($xml->layout) ? $xml->layout : _UNKNOWN;
        $template_author = isset($xml->author) ? $xml->author: '';
        $template_author_url = isset($xml->authorUrl) ? $xml->authorUrl : '';       
        
		$template_menus = '';
		if(isset($xml->menus->menu)){
			foreach($xml->menus->menu as $menu){
				$template_menus .= (($template_menus != '') ? ',' : '').$menu;
			}
		}			
		
		$tab_content_6 = '<form action="index.php?admin=settings" method="post">
			'.draw_hidden_field('submition_type', 'google_api', false).'
			'.draw_hidden_field('tabid', $tabid, false).'
			'.draw_token_field(false).'
			
			<table width="99%" border="0" class="tabs_table">
			<tr valign="top">
                <td>
					<table border="0" class="tabs_table">
					<tr valign="top">
						<td width="250px">
							'._MAPPING_API_KEY.' <span class="required">*</span>:
							<input class="mgrid_text" type="text" name="google_api" id="google_api" size="45" maxlength="70" value="'.(SITE_MODE == 'demo' ? '**********' : htmlspecialchars($params_tab6['google_api'])).'" />
						</td>
						<td align="left"></td>
					</tr>
					</table>
				</td>
				<td style="border-left:1px solid #d1d2d3; padding:2px 28px 2px 28px">
                    '._HOW_GOOGLE_MAPS_API_KEY.'
                    <ol>
                        <li>'._HOW_GOOGLE_MAPS_STEP_1.'</li>
                        <li>'._HOW_GOOGLE_MAPS_STEP_2.'</li>
                        <li>'._HOW_GOOGLE_MAPS_STEP_3.'</li>
                        <li>'._HOW_GOOGLE_MAPS_STEP_4.'</li>
                        <li>'._HOW_GOOGLE_MAPS_STEP_5.'</li>
                        <li>'._HOW_GOOGLE_MAPS_STEP_6.'</li>
                    </ol>
                </td>				
			</tr>
			<tr>
				<td colspan="2"><input class="form_button" type="submit" name="btnSubmit" value="'._BUTTON_CHANGE.'"></td>
			</tr>
			</table>
		</form>';
		
	}elseif($tabid == '1_7'){

        ob_start();
        if(function_exists('phpinfo')) @phpinfo(-1);
        $phpinfo = array('phpinfo' => array());
        if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
        foreach($matches as $match){
            $array_keys = array_keys($phpinfo);
            $end_array_keys = end($array_keys);
            if(strlen($match[1])){
                $phpinfo[$match[1]] = array();
            }elseif(isset($match[3])){
                $phpinfo[$end_array_keys][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
            }else{
                $phpinfo[$end_array_keys][] = $match[2];
            }
        }

	    $phpversion 	= function_exists('phpversion') ? phpversion() : _UNKNOWN;
		$mysqli_version 	= database_query('select version() as ve', DATA_ONLY, FIRST_ROW_ONLY);

		$asp_tags 		= isset($phpinfo['PHP Core']['asp_tags']) ? $phpinfo['PHP Core']['asp_tags'][0] : _UNKNOWN;
		$safe_mode 		= isset($phpinfo['PHP Core']['safe_mode']) ? $phpinfo['PHP Core']['safe_mode'][0] : _UNKNOWN;
        // mode_rewrite
        if(function_exists('apache_get_modules')){
            // works only if PHP is not running as CGI module
            $mode_rewrite = (in_array('rewrite', get_loaded_extensions())) ? 'On' : 'Off';
        }else{
            // old - $mod_rewrite = getenv('HTTP_MOD_REWRITE') == 'On' ? true : false ;
            if(file_exists('install/tests/test1.txt')){
                $mode_rewrite = (file_get_contents(get_base_url().'install/tests/test1.txt') == '2') ? 'On' : 'Off';    
            }else{
                $mode_rewrite = _UNKNOWN;
            }
        }
		$short_open_tag = isset($phpinfo['PHP Core']['short_open_tag']) ? $phpinfo['PHP Core']['short_open_tag'][0] : _UNKNOWN;
		$vd_support 	= isset($phpinfo['phpinfo']['Virtual Directory Support']) ? $phpinfo['phpinfo']['Virtual Directory Support'] : _UNKNOWN;
		$system 		= isset($phpinfo['phpinfo']['System']) ? $phpinfo['phpinfo']['System'] : _UNKNOWN;
		$build_date 	= isset($phpinfo['phpinfo']['Build Date']) ? $phpinfo['phpinfo']['Build Date'] : _UNKNOWN;
		$server_api 	= isset($phpinfo['phpinfo']['Server API']) ? $phpinfo['phpinfo']['Server API'] : _UNKNOWN;
		
		$smtp 	 		= (ini_get('SMTP') != '') ? ini_get('SMTP') : _UNKNOWN;
		$smtp_port	 	= (ini_get('smtp_port') != '') ? ini_get('smtp_port') : _UNKNOWN;
		$sendmail_from 	= (ini_get('sendmail_from') != '') ? ini_get('sendmail_from') : _UNKNOWN;
		$sendmail_path 	= (ini_get('sendmail_path') != '') ? ini_get('sendmail_path') : _UNKNOWN;

		$session_support = isset($phpinfo['session']['Session Support']) ? $phpinfo['session']['Session Support'] : 'unknown';
		$mbstring_support = (function_exists('mb_detect_encoding')) ? 'enabled' : 'disabled';
		$magic_quotes_gpc = ini_get('magic_quotes_gpc') ? _ON : _OFF;
		$magic_quotes_runtime = ini_get('magic_quotes_runtime') ? _ON : _OFF;
		$magic_quotes_sybase = ini_get('magic_quotes_sybase') ? _ON : _OFF;

		$tab_content_7 = '<table border="0" class="tabs_table"><tr><td>';		
		$tab_content_7 .= '<ul>
				<li><b>PHP Version:</b> <i>'.$phpversion.'</i></li>
				<li><b>'.((DB_TYPE == 'PDO') ? 'PDO MySQLi' : DB_TYPE).' Version:</b> <i>'.(isset($mysqli_version['ve']) ? $mysqli_version['ve'] : _UNKNOWN).'</i></li>
				<li><b>'._SYSTEM.':</b> <i>'.$system.'</i></li>
			</ul>';		
		$tab_content_7 .= '<ul>				
				<li><b>Build Date:</b> <i>'.$build_date.'</i></li>
				<li><b>Server API:</b> <i>'.$server_api.'</i></li>
				<li><b>Virtual Directory Support:</b> <i>'.$vd_support.'</i></li>
				<li><b>Safe Mode:</b> <i>'.$safe_mode.'</i></li>
				<li><b>Mode Rewrite:</b> <i>'.$mode_rewrite.'</i></li>
				<li><b>Asp Tags:</b> <i>'.$asp_tags.'</i></li>
				<li><b>Short Open Tag:</b> <i>'.$short_open_tag.'</i></li>				
				<li><b>Session Support:</b> <i>'.$session_support.'</i></li>
				<li><b>mbString Support:</b> <i>'.$mbstring_support.'</i></li>
			</ul>';		
		$tab_content_7 .= '<ul>
				<li><b>SMTP:</b> <i>'.$smtp.'</i></li>
				<li><b>SMTP Port:</b> <i>'.$smtp_port.'</i></li>
				<li><b>Sendmail From:</b> <i>'.$sendmail_from.'</i></li>
				<li><b>Sendmail Path:</b> <i>'.$sendmail_path.'</i></li>
			</ul>';		
		$tab_content_7 .= '<ul>
				<li><b>Magic Quotes GPC:</b> <i>'.$magic_quotes_gpc.'</i></li>
				<li><b>Magic Quotes RunTime:</b> <i>'.$magic_quotes_runtime.'</i></li>
				<li><b>Magic Quotes SyBase:</b> <i>'.$magic_quotes_sybase.'</i></li>
			</ul>';
		$tab_content_7 .= '</td></tr></table>';

	}elseif($tabid == '1_8'){		
		
		$tab_content_8 = '<form action="index.php?admin=settings" method="post">
				'.draw_hidden_field('submition_type', 'site_info', false).'
				'.draw_hidden_field('tabid', $tabid, false).'
				'.draw_token_field(false).'
				
				<table border="0" class="tabs_table">
				<tr valign="top">
					<td>
						'._SITE_RANKS.' ('.$http_host.'):
					</td>
				</tr>
				<tr valign="top">
					<td>
						<ul>
							<li>Google Indexed Pages: <b><i>'.(int)$objSettings->GetParameter('google_indexed_pages').'</i></b></li>
							<li>Alexa Rank: <b><i>'.$objSettings->GetParameter('alexa_rank').'</i></b></li>
						</ul>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<input class="form_button" type="submit" name="submit" value="'._BUTTON_UPDATE.'">
					</td>
				</tr>
				</table>
			</form>';		

	}elseif($tabid == '1_9'){
		
		$tab_content_9 = '<form action="index.php?admin=settings" method="post">
				'.draw_hidden_field('submition_type', 'cron_settings', false).'
				'.draw_hidden_field('tabid', $tabid, false).'
				'.draw_token_field(false).'
				
				<table width="99%" border="0" class="tabs_table">
				<tr valign="top">
					<td width="90px">'._RUN_CRON.':</td>
					<td width="160px">
                        <input class="form_radio" type="radio" name="cron_type" id="cron_type_batch" '.(($params_cron['cron_type'] == 'batch') ? 'checked="checked"' : '').' value="batch" onclick="appHideElement(\'cron_run_period_row\')" /> <label for="cron_type_batch">Batch</label><br />
						<input class="form_radio" type="radio" name="cron_type" id="cron_type_non_batch" '.(($params_cron['cron_type'] == 'non-batch') ? 'checked="checked"' : '').' value="non-batch" onclick="appShowElement(\'cron_run_period_row\')" /> <label for="cron_type_non_batch">Non-batch</label><br />
						<input class="form_radio" type="radio" name="cron_type" id="cron_type_stop" '.(($params_cron['cron_type'] == 'stop') ? 'checked="checked"' : '').' value="stop" onclick="appHideElement(\'cron_run_period_row\')" /> <label for="cron_type_stop">'._STOP.'</label><br />
					</td>
					<td rowspan="3" style="border-left:1px solid #d1d2d3;padding:2px 18px 2px 18px">
						'._CRONJOB_NOTICE.'<br /><br />'._CRONJOB_HTACCESS_BLOCK.'
<pre>
&lt;Files "cron.php"&gt;
   Order Deny,Allow
   Deny from all
   Allow from localhost
   Allow from 127.0.0.1
   Allow from xx.xx.xx.xx <-- add here your IP address (allowed)
&lt;/Files&gt;
</pre>
					</td>
				</tr>
				<tr>
					<td colspan="3">
					<table>
					<tr valign="top" id="cron_run_period_row" '.(($params_cron['cron_type'] != 'non-batch') ? 'style="display:none;"' : '').'>
						<td width="106px">'._RUN_EVERY.':</td>
						<td>
							'.draw_numbers_select_field('cron_run_period_value', $params_cron['cron_run_period_value'], 1, 100, 1, 'mgrid_select', '', false).'
							<select class="mgrid_select" name="cron_run_period">
								<option value="minute" '.(($params_cron['cron_run_period'] == 'minute') ? 'selected="selected"' : '').'>'._MINUTES.'</option>
								<option value="hour" '.(($params_cron['cron_run_period'] == 'hour') ? 'selected="selected"' : '').'>'._HOURS.'</option>
							</select>
						</td>
					</tr>
					</table>
					</td>
				</tr>	
				<tr valign="top">
					<td height="200px">
						'._LAST_RUN.': 
						<br /><br /><br />
						<input class="form_button" type="submit" name="submit" value="'._BUTTON_UPDATE.'" />
					</td>
					<td>
						'.format_datetime($objSettings->GetParameter('cron_run_last_time'), '', '- '._NEVER.' -').'
					</td>
				</tr>		
				<tr><td colspan="3" nowrap="nowrap" height="15px"></td></tr>
				</table>
			</form>';			
	}
   
	$tabs = new Tabs(1, 'xp', TABS_DIR, '?admin=settings');
	$tabs->SetToken(Application::Get('token'));
	///$tabs->SetHttpVars(array('admin'));
 
	$tab1=$tabs->AddTab(_GENERAL_SETTINGS, $tab_content_1);
	$tab2=$tabs->AddTab(_VISUAL_SETTINGS, $tab_content_2);
	$tab3=$tabs->AddTab(_DATETIME_PRICE_FORMAT, $tab_content_3); 
	$tab4=$tabs->AddTab(_EMAIL_SETTINGS, $tab_content_4);
	$tab5=$tabs->AddTab(_TEMPLATES_STYLES, $tab_content_5);
	$tab6=$tabs->AddTab(_GOOGLE_MAPS_API, $tab_content_6);
	$tab7=$tabs->AddTab(_SERVER_INFO, $tab_content_7);
	$tab8=$tabs->AddTab(_SITE_INFO, $tab_content_8);
	$tab9=$tabs->AddTab(_CRON_JOBS, $tab_content_9);

	## +---------------------------------------------------------------------------+
	## | 2. Customizing:                                                           |
	## +---------------------------------------------------------------------------+
	## *** set container's width in pixels (px), inches (in) or points (pt)
	$tabs->SetWidth('100%');
 
	## *** set container's height in pixels (px), inches (in) or points (pt)
	$tabs->SetHeight('auto'); // 'auto'
 
	## *** set alignment inside the container (left, center or right)
	$tabs->SetAlign('left');
 
	## *** set container's color in RGB format or using standard names
	/// $tabs->SetContainerColor('#64C864');
	## *** set border's width in pixels (px), inches (in) or points (pt)
	/// $tabs->SetBorderWidth('5px');
	## *** set border's color in RGB format or using standard names
	/// $tabs->SetBorderColor('#64C864');
	/// $tabs->SetBorderColor('blue');
	/// $tabs->SetBorderColor('#445566');
	## *** show debug info - false|true
	$tabs->Debug(false);
	## *** allow refresh selected tabs - false|true
	/// $tabs->AllowRefreshSelectedTabs(true);
	## *** set form submission type: 'get' or 'post'
	$tabs->SetSubmissionType('post');
 
	/// $tabs->Disable($tab2);
	/// $tabs->SetDefaultTab($tab3);
	/// $tab4->SetDefaultTab($subtab4);

	echo '<script type="text/javascript">
		function cleanCacheSubmit(){
			if(confirm("'._PERFORM_OPERATION_COMMON_ALERT.'")){
				appGoToPage("index.php?admin=settings", "submition_type=clean_cache&token='.Application::Get('token').'", "post");
				return true;
			}
			return false;
		}
		function sendTestEmail(el){
			el.disabled=true;
			el.value=\''._SENDING.'...\';
			document.getElementById(\'frmEmailSettings\').submition_type.value=\'test_smtp_connection\';
			document.getElementById(\'frmEmailSettings\').submit();
		}
	</script>';

	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(
			_GENERAL			=> 'index.php?admin=home',
			_SITE_SETTINGS		=> ''
		)
	));

	if($msg == '') draw_message(_ALERT_REQUIRED_FILEDS);
	else echo $msg;
	
	draw_content_start();	
	$tabs->Display();
	if($focus_on_field) echo '<script type="text/javascript">appSetFocus("'.$focus_on_field.'")</script>';
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
