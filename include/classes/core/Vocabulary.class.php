<?php

/**
 * Class Vocabulary
 * -------------- 
 * Description : encapsulates vocabulary properties and methods
 * Written by  : ApPHP
 * Version     : 1.0.8
 * Updated	    : 05.07.2016
 * Usage       : Core Class
 * Differences : no
 *
 * PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 * __construct			                           	GetVocabulary
 * __destruct                                     	GetVocabularySize
 * GetFilterURL                                   	GetFieldsEncoded
 * GetLanguageURL
 * DrawRewriteButton
 * DrawEditForm
 * IsKeyUpdated
 * UpdateKey
 * DrawVocabulary
 * RewriteVocabularyFile
 * DrawUploadForm
 * UploadAndUpdate
 * 
 * 1.0.8
 *	- fixed issue with wrong detecting type of uploading vocabulary file
 *	- improved detecting of file mine type of uploading vocabulary file
 *	- added mgrid_ classes to HTML elements
 *	-
 *	-
 * 1.0.7
 *		- bug fixed in UploadAndUpdate() for uploaded files
 *     	- added maxlength to textarea
 *     	- another bug fixed in UploadAndUpdate() for uploaded files
 *     	- Google translate API changed to version 2.0
 *     	- improved highlight() call
 * 1.0.6
 *     	- changed _APPLY_TO_ALL_LANGUAGES
 *     	- added for Google accounts..
 *     	- added $this->filterByUrl to cancel button
 *     	- added $this->filterByUrl to DrawUploadForm()
 *     	- fixed issue with last character - new line
 * 1.0.5
 *     	- fixed bug while uploading file if text includes commas (,)
 *     	- added possibility to re-write vocabulary for all languages
 *     	- window.location.href  replaced with appGoTo()
 *     	- index.php?... replaced with prepare_permanent_link()
 *     	- added check for uploaded file extension
 * 
 * 
 **/

class Vocabulary {

	public $error;
	public $updatedKeys;	
	
	protected $keys;
	protected $filterBy;
	protected $filterByUrl;
	protected $languageId;
	protected $langIdByUrl;
	protected $whereClause;
	protected $isKeyUpdated;
	protected $currentKey;

	private $vocabularySize;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		// get filter value
		$this->filterBy = isset($_REQUEST['filter_by']) ? prepare_input($_REQUEST['filter_by']) : '';
		$this->filterByUrl = ($this->filterBy != '') ? '&filter_by='.$this->filterBy : '';

		$this->languageId  = (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? prepare_input($_REQUEST['language_id']) : Languages::GetDefaultLang();
		$this->langIdByUrl = ($this->languageId != '') ? '&language_id='.$this->languageId : '';

		$this->whereClause  = '';
		$this->whereClause .= ($this->languageId != '') ? ' AND language_id = \''.$this->languageId.'\'' : '';		
		$this->whereClause .= ($this->filterBy != '') ? ' AND key_value LIKE \'_'.$this->filterBy.'%\'' : '';		
		
		$this->isKeyUpdated = false;
		$this->vocabularySize = 0;
		$this->currentKey = '';
		$this->updatedKeys = '0';
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Returns filter URL
	 */
	public function GetFilterURL()
	{
		return $this->filterByUrl;		
	}

	/**
	 * Returns language URL
	 */
	public function GetLanguageURL()
	{
		return $this->langIdByUrl;		
	}

	/**
	 * Returns vocabulary record set
	 * @param $where_clause
	 */
	private function GetVocabulary($where_clause = '')
	{
		$sql = 'SELECT * FROM '.TABLE_VOCABULARY.' WHERE 1=1 '.$where_clause.' ORDER BY key_value ASC';
		$this->keys = database_query($sql, DATA_ONLY, ALL_ROWS);
		$this->vocabularySize = count($this->keys);
	}
	
	/**
	 * Returns vocabulary size
	 * @param $where_clause
	 */
	private function GetVocabularySize($where_clause = '')
	{
		$sql = 'SELECT COUNT(*) FROM '.TABLE_VOCABULARY;
		$this->vocabularySize = database_query($sql, ROWS_ONLY);		
	}

	/** 
	 * Draws rewrite button
	 * @param $draw
	 */
	public function DrawRewriteButton($draw = true)
	{
		global $objSettings;
		$total_languages = Languages::GetAllActive();
		$output = '';
		
		$button_align_left  = (Application::Get('lang_dir') == 'ltr') ? 'text-align:left;' : 'text-align:right;';
		$button_align_right = (Application::Get('lang_dir') == 'ltr') ? 'text-align:right;' : 'text-align:left;';

		if($this->GetVocabularySize() <= 0){			
			$output .= '<form action="index.php?admin=vocabulary&filter_by=A" method="post">';
			$output .= draw_hidden_field('submition_type', '2', false);
			$output .= draw_token_field(false);
			$output .='<table align="center" width="100%" border="0" cellspacing="0" cellpadding="3" class="main_text">
				  <tr valign="top">					
					<td style="'.$button_align_left.'">
						'.draw_languages_box('language_id', $total_languages[0], 'abbreviation', 'lang_name', $this->languageId, 'mgrid_select', 'onchange="appGoTo(\'admin=vocabulary'.$this->filterByUrl.'&language_id=\'+this.value)"', false).'
					</td>
					<td style="padding:5px;'.$button_align_right.'">
						'.prepare_permanent_link('index.php?admin=vocabulary&act=upload_form'.$this->langIdByUrl.$this->filterByUrl, '[ '._UPLOAD_FROM_FILE.' ]').'&nbsp;&nbsp;&nbsp;
					</td>
					<td width="185px" style="'.$button_align_left.'">
						<input class="mgrid_button" type="submit" name="btnRewrite" value="'.decode_text(_BUTTON_REWRITE).'"><br />
						<input class="mgrid_button mgrid_button_cancel" type="checkbox" name="all_languages" id="chk_all_languages"><label for="chk_all_languages">'._APPLY_TO_ALL_LANGUAGES.'</label>
					</td>
				  </tr>
				  </table>
			</form>';			
		}
		
		if($draw) echo $output;
		else return $output;
	}	
	
	/**
	 * Draws Upload Form
	 * @param $draw
	 */
	public function DrawUploadForm($draw = true)
	{
		$disabled = (strtolower(SITE_MODE) == 'demo') ? ' disabled="disabled"' : '';
		$total_languages = Languages::GetAllLanguages();
		$output = '';

		$output .= '<script type="text/javascript">';
		$output .= 'function validate_file_type(){
						var id_value = document.getElementById("lang_update_file").value;						
						if(id_value == ""){
							alert("'.str_replace('_FIELD_', '['._SELECT_FILE_TO_UPLOAD.']', _FIELD_CANNOT_BE_EMPTY).'");
							return false;
						}
						else if(id_value != ""){
							if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')){
								var valid_extensions = /(.php)$/i;
								if(!valid_extensions.test(id_value)){
									alert("'._WRONG_FILE_TYPE.'");
									return false;
								}
							}
						}
						return true;
					}';
		$output .= '</script>';
		$output .= '<form action="index.php?admin=vocabulary'.$this->filterByUrl.'" method="post" enctype="multipart/form-data">';
		$output .= draw_hidden_field('act', 'upload_and_update', false);
		$output .= draw_token_field(false);
		$output .= '<table align="center" width="99%" border="0" cellspacing="0" cellpadding="3" class="main_text">
		<tr valign="top">
			<td width="200px"><b>'._SELECT_FILE_TO_UPLOAD.'</b></td>
			<td><input class="mgrid_text" name="lang_update_file" id="lang_update_file" type="file"></td>
			<td align="right">
				<input class="mgrid_button" '.$disabled.' type="submit" value="'._UPLOAD_AND_PROCCESS.'" onclick="return validate_file_type();">
				&nbsp;
				<a href="javascript:void(0);" onclick="javascript:appGoTo(\'admin=vocabulary'.$this->langIdByUrl.$this->filterByUrl.'\')">[ '._BUTTON_CANCEL.' ]</a>
			</td>
		</tr>
		<tr valign="top">
			<td><b>'._SELECT_LANG_TO_UPDATE.'</b></td>
			<td colspan="2">'.draw_languages_box('language_id', $total_languages[0], 'abbreviation', 'lang_name', $this->languageId, 'mgrid_select', '', false).'</td>
		</tr>
		<tr><td colspan="3"></td></tr>
		</table>
		</form>';
		
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Upload and Update Vocabulary
	 * @param $language_id
	 */
	public function UploadAndUpdate($language_id = '')
	{
		// Block all operations in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;				
		}

		$lang 	     = (!empty($language_id)) ? $language_id : Application::Get('lang');
		$update_file = isset($_FILES['lang_update_file']) ? true : false;
		$file_type   = isset($_FILES['lang_update_file']['tmp_name']) ? mime_content_type($_FILES['lang_update_file']['tmp_name']) : '';
        $file_name   = isset($_FILES['lang_update_file']['name']) ? $_FILES['lang_update_file']['name'] : '';
		$count       = 0;
		
		if($update_file && (preg_match('/php/i', $file_type) || preg_match('/php/i', $file_name))){			
			$arr = file($_FILES['lang_update_file']['tmp_name']);
			
			foreach($arr as $key => $val){
				if(preg_match('/define/i', $val)){
					$val = str_replace(array('","', '", "'), '##', $val);
					$val = str_replace(array('define(', 'define( ', 'define (', 'define ( ', ');', '"'), '', $val);
					$val = trim($val, "\r\n");
					$val_parts = explode('##', $val);
					
					$key_value = isset($val_parts[0]) ? trim($val_parts[0]) : '';
					$key_text = isset($val_parts[1]) ? $val_parts[1] : '';

					$sql = 'UPDATE '.TABLE_VOCABULARY.'
							SET key_text = \''.encode_text($key_text).'\'
							WHERE key_value = \''.$key_value.'\' AND language_id = \''.$lang.'\'';
					if(database_void_query($sql, false, false)){					
						$count++;
					}
				}
			}
		}else{
			$this->error = _WRONG_FILE_TYPE;
			return false;
		}
		if($count > 0){
			$this->updatedKeys = $count;
			return true;
		}else{
			$this->error = _NO_RECORDS_UPDATED;
			return false;
		}		
	}	

	/**
	 * Draws Edit Form
	 * @param $key
	 * @param $draw
	 */
	public function DrawEditForm($key = '0', $draw = true)
	{
		$total_languages = Languages::GetAllLanguages();
		$key_value = $key_text = '';
		$default_lang_name = 'English';
		$default_lang_abbr = 'en';
		$lang_to_dir  = Languages::GetLanguageDirection($this->languageId);
		$default_lang_text = '';		
		$align_left  = (Application::Get('lang_dir') == 'ltr') ? 'left' : 'right';
		$align_right = (Application::Get('lang_dir') == 'ltr') ? 'right' : 'left';
		$output = '';
	
		$sql = 'SELECT * FROM '.TABLE_VOCABULARY.' WHERE id = '.(int)$key;
        if($row = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$key_value = $row['key_value'];
			$key_text = $row['key_text'];
			$this->currentKey = $key_value;
		}
		
		$sql = 'SELECT * FROM '.TABLE_LANGUAGES.' WHERE is_default = 1';
        if($row = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$default_lang_name = $row['lang_name'];
			$default_lang_abbr = $row['abbreviation'];

			$sql = 'SELECT * FROM '.TABLE_VOCABULARY.' WHERE key_value = \''.$this->currentKey.'\' AND language_id = \''.$default_lang_abbr.'\'';
			if($row = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
				$default_lang_text = strip_tags($row['key_text'], '<b><i><u><br><br />');
			}
		}

		if($default_lang_abbr != $this->languageId){		
			$output .= '<script type="text/javascript" src="//www.google.com/jsapi"></script>';
			$output .= '<script type="text/javascript">
				google.load("language", "1");
				function TranslateText(response){
					if(response.data){
						jQuery("#txt_message").html("'._COMPLETED.'!");
						jQuery("#txt_key_value").val(response.data.translations[0].translatedText);  
						jQuery("#btnTranslate").attr("disabled", false); 
					}
					else if(response.error.errors){
						jQuery("#txt_message").html(response.error.message);
					}else{
						//response.error.message
						jQuery("#txt_message").html("This feature is only available for Google paid accounts! Please check you have a valid API Key.");
					}
				}
				
				function GoAndTranslate(){';				
				// Block all operations in demo mode
				if(strtolower(SITE_MODE) == 'demo'){
					$output .= 'alert(\''._OPERATION_BLOCKED.'\'); return false; ';
				}else{
					$output .= '// grabbing the text to translate										
					var YOUR_API_KEY = "";
					var text = jQuery("#txt_key_value").val();
					if(text.indexOf(" ") <= 0) text = text.toLowerCase();
					jQuery("#btnTranslate").attr("disabled", true);
					jQuery("#txt_message").html("");
					
					var newScript = document.createElement("script");
					newScript.type = "text/javascript";
					
					// WARNING: be aware that YOUR-API-KEY inside html is viewable by all your users.
					// Restrict your key to designated domains or use a proxy to hide your key
					// to avoid misuage by other party.
					var source = "https://www.googleapis.com/language/translate/v2?key="+YOUR_API_KEY+"&source='.$default_lang_abbr.'&target='.$this->languageId.'&callback=TranslateText&q=" + text;
					newScript.src = source;
					// When we add this script to the head, the request is sent off.
					document.getElementsByTagName("head")[0].appendChild(newScript);';					
				}
				$output .= '}
				</script>';
		}
	
		$output .= '<form action="index.php?admin=vocabulary" method="post">';
		$output .= draw_hidden_field('submition_type', '1', false);
		$output .= draw_hidden_field('key', $key, false);
		$output .= draw_hidden_field('filter_by', $this->filterBy, false);
		$output .= draw_hidden_field('language_id', $this->languageId, false);
		$output .= draw_token_field(false);
		$output .= '<table align="center" width="99%" border="0" cellspacing="0" cellpadding="3" class="main_text">
				<tr valign="top">
					<td><b>'._EDIT_WORD.'</b></td>
					<td><div id="txt_message" style="color:#00a600"></div></td>
					<td width="20px" nowrap="nowrap"></td>
					<td align="'.$align_right.'">'.draw_languages_box('language_id', $total_languages[0], 'abbreviation', 'lang_name', $this->languageId, 'mgrid_select', 'disabled="disabled"', false).'</td>
				</tr>
				<tr valign="top">
					<td align="'.$align_right.'" width="90px">'._KEY.':</td>					
					<td align="'.$align_left.'" colspan="2">
						'.$key_value.'
						'.draw_hidden_field('txt_key', $key_value, false).'
					</td>
					<td></td>
				</tr>';
		   $output .= '<tr valign="top">
					<td align="'.$align_right.'">'._VALUE.' <span style="color:#c13a3a">*</span>:</td>
					<td align="'.$align_left.'">
						<textarea dir="'.$lang_to_dir.'" class="mgrid_textarea" style="width:100%;height:60px;overflow:auto;padding:3px;" name="txt_key_value" id="txt_key_value" maxlength="4096">'.decode_text($key_text).'</textarea>						
					</td>
					<td></td>
					<td align="right" width="240px">';
					if($default_lang_abbr != $this->languageId){
						$output .= '<nobr>
							'.$default_lang_name.' &raquo; '.strtoupper($this->languageId).' &nbsp;
							<input class="mgrid_button" type="button" id="btnTranslate" name="submit" style="width:150px" onclick="GoAndTranslate()" value="'._TRANSLATE_VIA_GOOGLE.'" />
							<input class="mgrid_button mgrid_button_cancel" type="reset" name="btnReset" title="'._RESET.'" value="R" />
						</nobr><br /><br />';
					}
				$output .= '<input class="mgrid_button" type="submit" name="submit" value="'.decode_text(_BUTTON_UPDATE).'">&nbsp;&nbsp;
					  <input class="mgrid_button mgrid_button_cancel" type="button" onclick="appGoTo(\'admin=vocabulary'.$this->langIdByUrl.$this->filterByUrl.'\')" value="'.decode_text(_BUTTON_CANCEL).'">			
					</td>
				</tr>';
			if($default_lang_abbr != $this->languageId){
				$output .= '<tr valign="top">
						<td align="'.$align_right.'" width="110px">'.$default_lang_name.':</td>
						<td align="'.$align_left.'">'.$default_lang_text.'</td>
						<td colspan="2"></td>
					</tr>';
			}				
		   $output .= '<tr align="right"><td colspan="4"></td></tr>
				</table>
			 </form>';
			 
		if($draw) echo $output;
		else return $output;			 
	}	
	
	/**
	 * Checks if a key was updated
	 */
	public function IsKeyUpdated()
	{
		return $this->isKeyUpdated;
	}

	/**
	 * Updates vocabulary key
	 * @param $key_value
	 * @param $key_text
	 */
	public function UpdateKey($key_value = '', $key_text = '')
	{		
		// Block all operations in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;				
		}

		// Check input parameters
		if($key_text == ''){
			$this->error = _VOC_KEY_VALUE_EMPTY;
			return false;
		}elseif(strlen($key_text) > 2048){
			$msg_text = str_replace('_FIELD_', '<b>'._VALUE.'</b>', _FIELD_LENGTH_ALERT);
			$msg_text = str_replace('_LENGTH_', '2048', $msg_text);			
			$this->error = $msg_text;
			return false;
		}
		
		$sql = 'UPDATE '.TABLE_VOCABULARY.'
				SET key_text = \''.$this->GetFieldsEncoded(trim($key_text, "\r\n")).'\'
				WHERE
					key_value = \''.$key_value.'\' AND
					language_id = \''.$this->languageId.'\'';
		if(database_void_query($sql)){
			$this->isKeyUpdated = true;
			return true;
		}else{
			$this->error = _TRY_LATER;
			return false;
		}            
	}			
	
	/**
	 * Draws vocabulary
	 * @param $key
	 */
	public function DrawVocabulary($key)
	{	
		$align_left  = (Application::Get('lang_dir') == 'ltr') ? 'left' : 'right';
		$align_right = (Application::Get('lang_dir') == 'ltr') ? 'right' : 'left';		

		$this->GetVocabulary($this->whereClause);
			
		echo '<a name="top"></a>';
		echo '<div class="table-responsive">';
		echo '<table width="100%" style="min-width:600px" class="mgrid_table">
			  <tr>
				<td>'._FILTER_BY.': ';
				echo prepare_permanent_link('index.php?admin=vocabulary'.$this->langIdByUrl, _ALL).' - ';
				for($i = 65; $i < 91; $i++){
					if($this->filterBy == chr($i)) $chr_i = '<b><u>'.chr($i).'</u></b>';
					else $chr_i = chr($i);
					echo prepare_permanent_link('index.php?admin=vocabulary&filter_by='.chr($i).$this->langIdByUrl, $chr_i).' ';
				}
				echo ' - ';
				for($i = 1; $i <= 5; $i++){
					if($this->filterBy == $i) $chr_i = '<b><u>'.$i.'</u></b>';
					else $chr_i = $i;
					echo prepare_permanent_link('index.php?admin=vocabulary&filter_by='.$i.$this->langIdByUrl, $chr_i).' ';
				}
				echo '</td>
				<td width="7%" align="center" nowrap="nowrap">
				'._TOTAL.': '.count($this->keys).'
				</td>
			  </tr>';
		echo '<tr align="center"><td colspan="2">'.draw_line('line_no_margin', IMAGE_DIRECTORY, false).'</td></tr>';	
		echo '</table>';

		if(!empty($this->keys)){							           
            echo '<table width="100%" style="min-width:600px" class="mgrid_table">';
			echo '<tr>
					<th width="1%">#</th>
					<th width="25%" align="'.$align_left.'">'._KEY.'</th>
					<th align="'.$align_left.'">'._VALUE.'</th>
					<th width="60px"></th>';

			for($i=0; $i < $this->vocabularySize; $i++){
				// Prepare key_text for displaying
				$decoded_text = strip_tags(decode_text($this->keys[$i]['key_text']));
				if(strlen($decoded_text) > 90){
					$key_text = '<span style="cursor:help;" title="'.$decoded_text.'">'.substr_by_word($decoded_text, 95, true).'</span>';
				}else{
					$key_text = $decoded_text;
				}

				// Display vocabulary row
				if($this->keys[$i]['key_value'] == $this->currentKey){
					echo '<tr>';
					echo '<td align="'.$align_right.'" class="voc_row_edit_'.$align_left.'" nowrap="nowrap">'.($i+1).'.</td>';
					echo '<td align="'.$align_left.'" class="voc_row_edit_middle" nowrap="nowrap">'.$this->keys[$i]['key_value'].'</td>';
					echo '<td align="'.$align_left.'" class="voc_row_edit_middle">'.$key_text.'</td>
					      <td align="center" class="voc_row_edit_'.$align_right.'">'.prepare_permanent_link('index.php?admin=vocabulary&key='.$this->keys[$i]['id'].'&act=edit'.$this->filterByUrl.$this->langIdByUrl, '[ '._EDIT_WORD.' ]').'</td>
					</tr>';
				}elseif($this->keys[$i]['id'] == (int)$key){
					echo '<tr>';
					echo '<td align="'.$align_right.'" class="voc_row_update_'.$align_left.'" nowrap="nowrap">'.($i+1).'.</td>';
					echo '<td align="'.$align_left.'" class="voc_row_update_middle" nowrap="nowrap">'.$this->keys[$i]['key_value'].'</td>';
					echo '<td align="'.$align_left.'" class="voc_row_update_middle">'.$key_text.'</td>
					      <td align="center" class="voc_row_update_'.$align_right.'">'.prepare_permanent_link('index.php?admin=vocabulary&key='.$this->keys[$i]['id'].'&act=edit'.$this->filterByUrl.$this->langIdByUrl, '[ '._EDIT_WORD.' ]').'</td>
					</tr>';					
				}else{
					echo '<tr '.highlight($i, 0).' onmouseover="oldColor=this.style.backgroundColor;this.style.backgroundColor=\'#ededed\';" onmouseout="this.style.backgroundColor=oldColor">';
					echo '<td align="'.$align_right.'" nowrap="nowrap">'.($i+1).'.</td>';
					echo '<td align="'.$align_left.'" nowrap="nowrap">'.$this->keys[$i]['key_value'].'</td>';
					echo '<td align="'.$align_left.'">'.$key_text.'</td>
					      <td align="center">'.prepare_permanent_link('index.php?admin=vocabulary&key='.$this->keys[$i]['id'].'&act=edit'.$this->filterByUrl.$this->langIdByUrl, '[ '._EDIT_WORD.' ]').'</td>
					</tr>';				
				}
			}
			echo '<tr><td colspan="4" nowrap="nowrap" height="10px"></td></tr>';
			if($this->vocabularySize > 15) echo '<tr valign="bottom"><td colspan="3"></td><td align="center">'.prepare_permanent_link('index.php?admin=vocabulary'.$this->filterByUrl.$this->langIdByUrl.'#top', _TOP.' ^').'</td></tr>';
			echo '</table>';
		}else{
			draw_important_message(_VOC_NOT_FOUND);
		}			
		echo '</div>';
	}

	/***
	 * Rewrites vocabulary file
	 * @param $all_languages
	 */
	public function RewriteVocabularyFile($all_languages = false)
	{	
		// Block all operations in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;				
		}

		$languages = Languages::GetAllLanguages(' priority_order ASC', '', ((!$all_languages) ? ' abbreviation = \''.$this->languageId.'\'' : ''));
		$nl = "\n";
		
		for($i=0; $i < $languages[1]; $i++){
			
			$this->GetVocabulary(' AND language_id = \''.$languages[0][$i]['abbreviation'].'\'');
			
			$string_data = '<?php'.$nl;		
			for($j=0; $j < $this->vocabularySize; $j++){
				$replace_from = array('"', '\\', '$');
				$replace_to   = array('&#034;', '\\\\', '&#36;');
				$key_text = str_replace($replace_from, $replace_to, $this->keys[$j]['key_text']); // double slashes
				$string_data .= 'define("'.$this->keys[$j]['key_value'].'","'.$key_text.'");'.$nl;
			}		
			$string_data .= $nl.'?>';
			
			// Write data to the file
			$voc_file = 'include/messages.'.$languages[0][$i]['abbreviation'].'.inc.php';			
			@chmod($voc_file, 0755);
			$fh = @fopen($voc_file, 'w');
			if(!$fh){
				$this->error = 'Cannot open vocabulary file: '.$voc_file;
			}else{
				@fwrite($fh, $string_data);
				@fclose($fh);						
			}
			@chmod($voc_file, 0644);			
		}	
		
		return true;	
	}
	
	/**
	 * Returns encoded data 
	 * @param $str
	 */
	private function GetFieldsEncoded($str = '')
	{
		$str = encode_text($str);
		$str = str_replace('<TITLE>', '&lt;TITLE&gt;', $str); // <TITLE>
		$str = str_replace('<META>', '&lt;META&gt;', $str);   // <META>
		$str = str_replace('<DESCRIPTION>', '&lt;DESCRIPTION&gt;', $str); // <DESCRIPTION>
		return $str;
	}	
}
