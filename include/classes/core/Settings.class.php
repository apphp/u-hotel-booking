<?php

/***
 *	Class Settings
 *  ----------------- 
 *  Description : encapsulates session properties
 *	Written by  : ApPHP
 *  Version     : 1.0.2
 *  Updated	    : 08.10.2017
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	Used rank code functions: www.lampdeveloper.co.uk
 *	
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct			    SetTimeZone             CheckHash
 *	__destruct                                     	HashURL
 *	GetTemplate                                    	StrToNum  
 *	GetParameter
 *	UpdateFields
 *	SetTemplate
 *	GetDataTagged
 *	CheckAlexaRank
 *	CheckGoogleIndexedPages
 *	LoadData
 *	
 *  1.0.2
 *  	-
 *  	-
 *  	-
 *  	-
 *  	-
 *  1.0.1
 *      - added SetTimeZone()
 *      - updated SetTimeZone()
 *      - fixed issue with _OPERATION_BLOCKED
 *      - added encryption of smtp_password
 *      - added CheckGoogleIndexedPages      
 **/

class Settings {

	public $error;
	
	private $res;
	private $template;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		$this->error = '';
		$this->template = '';
		
		$this->LoadData();
	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 *	Loads parameters 
	 */
	public function LoadData()
	{
		$sql = 'SELECT *
				'.(PASSWORDS_ENCRYPTION ? ', AES_DECRYPT(smtp_password, \''.PASSWORDS_ENCRYPT_KEY.'\') as smtp_password' : '').'
				FROM '.TABLE_SETTINGS;
		$this->res = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		if($this->res){
			$this->template = $this->res['template'];
		}
	}

	/**
	 *	Returns template name
	 */
	public function GetTemplate()
	{
		return decode_text($this->template);
	}
	
	/**
	 * Returns parameter value by name
	 * @param $field_name	
	 */
	public function GetParameter($field_name = '')
	{
		if(isset($this->res[$field_name])){
			return decode_text($this->res[$field_name]);
		}else{
			return '';
		}
	}

	/**
	 * Updates fields
	 * @param $params - pairs: field - value 
	 */
	public function UpdateFields($params = array())
	{		
		// check if this is a DEMO
		if(strtolower(SITE_MODE) == 'demo'){ $this->error = defined('_OPERATION_BLOCKED') ? _OPERATION_BLOCKED : 'This operation is blocked in Demo Version!'; return false; }
		
		if(count($params) > 0){
			// prepare UPDATE statement
			$sql = 'UPDATE '.TABLE_SETTINGS.' SET ';
			$count = 0;
			foreach($params as $key => $val){
				if($count++ > 0) $sql .= ', ';
				if(PASSWORDS_ENCRYPTION && $key == 'smtp_password' && $val != ''){
					$sql .= $key.' = AES_ENCRYPT(\''.$val.'\', \''.PASSWORDS_ENCRYPT_KEY.'\')';
				}else{
					$sql .= $key.' = \''.encode_text($val).'\'';					
				}				
			}
			if(database_void_query($sql)){
				$this->LoadData();
				return true;
			}else{
				$this->error = _TRY_LATER;
				return false;
			}				
		}else{
			return '';						
		}
	}	

	/**
	 * Sets site template
	 * @param $template - template name
	 */
	public function SetTemplate($template = '')
	{
		// check if this is a DEMO
		if(strtolower(SITE_MODE) == 'demo'){ $this->error = defined('_OPERATION_BLOCKED') ? _OPERATION_BLOCKED : 'This operation is blocked in Demo Version!'; return false; }
		
		$sql = 'UPDATE '.TABLE_SETTINGS.' SET template = \''.encode_text($template).'\'';
		if (database_void_query($sql)){
			$this->template = $template;
			return true;
		} else {
			$this->error = _TRY_LATER;
			return false;
		}							
	}
	
	/**
	 * Returns tagged string 
	 * @param $str
	 */
	public function GetDataTagged($str)
	{
		$str = str_replace('&lt;', '<', $str); 
		$str = str_replace('&gt;', '>', $str); 
		return $str;		
	}	

	/**
	 * Returns Alexa Page Rank
	 * @param $url
	 */
	public function CheckAlexaRank($url)
	{
		$part = '';
		$remote_url = 'http://data.alexa.com/data?cli=10&dat=snbamz&url='.trim($url);
		$search_for = '<POPULARITY URL';
		if($handle = @fopen($remote_url, 'r')) {
			while(!feof($handle)){
				$part .= fread($handle, 100);
				$pos = strpos($part, $search_for);
				if($pos === false) continue;
				else break;
			}
			$part .= fread($handle, 100);
			fclose($handle);
		}
		$str = explode($search_for, $part);
		$str_1 = (isset($str[1])) ? $str[1] : '';
        $str_1_array = explode('"/>', $str_1);
		$str = array_shift($str_1_array);
		$str = explode('TEXT="', $str);
		$str_2 = (isset($str[1])) ? $str[1] : '';
		return $str_2;
	}

	/**
	 * Returns Google Indexed Pages
	 * @param $url
	 */
	public function CheckGoogleIndexedPages($url)
	{
		$indexedPages = 0;
		
		$content = file_get_contents('https://www.google.com/search?filter=0&q=site:'.$url);
		$pos = strpos($content, 'id="resultStats"');
		if($pos !== false){
			$text = substr($content, $pos+15, 15);
			$indexedPages = preg_replace('/[^0-9,]/', '', $text);					
		}
		
		return $indexedPages;    
	}
	
	/**
	 * Checks hash for url
	 * @param $hashnum
	 */
	private function CheckHash($hashnum)
	{
		$check_byte = 0;
		$flag = 0;
		$hash_str = sprintf('%u', $hashnum);
		$length = strlen($hash_str);
		for($i = $length - 1; $i >= 0; $i --){
			$re = $hash_str[$i];
			if (1 === ($flag % 2)){              
				$re += $re;     
				$re = (int)($re / 10) + ($re % 10);
			}
			$check_byte += $re;
			$flag ++;	
		}
		$check_byte %= 10;
		if(0 !== $check_byte){
			$check_byte = 10 - $check_byte;
			if (1 === ($flag % 2)){
				if(1 === ($check_byte % 2)) $check_byte += 9;
				$check_byte >>= 1;
			}
		}	
		return '7'.$check_byte.$hash_str;
	}

	/**
	 * Genearate hash for url
	 * @param $str
	 */
	private function HashURL($str)
	{
		$check1 = $this->StrToNum($str, 0x1505, 0x21);
		$check2 = $this->StrToNum($str, 0, 0x1003F);	
		$check1 >>= 2; 	
		$check1 = (($check1 >> 4) & 0x3FFFFC0 ) | ($check1 & 0x3F);
		$check1 = (($check1 >> 4) & 0x3FFC00 ) | ($check1 & 0x3FF);
		$check1 = (($check1 >> 4) & 0x3C000 ) | ($check1 & 0x3FFF);			
		$t1 = (((($check1 & 0x3C0) << 4) | ($check1 & 0x3C)) <<2 ) | ($check2 & 0xF0F);
		$t2 = (((($check1 & 0xFFFFC000) << 4) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000);		
		return ($t1 | $t2);
	}
	
	/**
	 * Converts string into 32-bit integer
	 * @param $str
	 * @param $check
	 * @param $magic
	 */
	private function StrToNum($str, $check, $magic)
	{
		$int_32_u = 4294967296;  // 2^32	
		$length = strlen($str);
		for ($i = 0; $i < $length; $i++) {
			$check *= $magic; 	
			//If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31), 
			//  the result of converting to integer is undefined
			//  refer to http://www.php.net/manual/en/language.types.integer.php
			if ($check >= $int_32_u) {
				$check = ($check - $int_32_u * (int) ($check / $int_32_u));
				//if the check less than -2^31
				$check = ($check < -2147483648) ? ($check + $int_32_u) : $check;
			}
			$check += ord($str[$i]);
		}
		return $check;
	}
	
	/**
	 * Set timezone for the site
	 */
	public static function SetTimeZone()
	{
		$sql = 'SELECT * FROM '.TABLE_SETTINGS;		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY, FETCH_ASSOC);
		if($result[1] > 0){
			$time_zone = (isset($result[0]['time_zone'])) ? @get_timezone_by_offset($result[0]['time_zone']) : '';
			if($time_zone) @date_default_timezone_set($time_zone);
			else if(!@date_default_timezone_set(TIME_ZONE)) date_default_timezone_set('UTC'); 
		}                                                   
	}
	
}
