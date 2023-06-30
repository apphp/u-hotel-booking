<?php

/***
 *	Class Site Description
 *  ----------------- 
 *	Description : encapsulates site's settings and properties
 *	Written by  : ApPHP
 *	Version     : 1.0.1
 *  Updated	    : 13.02.2012
 *  Usage       : Core Class
 *	Differences : no
 *	
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct			                           	
 *	__destruct                                     	
 *	GetParameter
 *	UpdateFields
 *	GetDataTagged
 *	DrawFooter
 *	DrawHeader
 *	LoadData
 *	
 *  1.0.1
 *      - 
 *      - 
 *      -
 *      -
 *      -
 **/

class SiteDescription {

	public $error;
	
	private $res;
	private $language_id;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		$this->error = '';
		$this->language_id = Application::Get('lang');
		///$this->LoadData();
	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Loads parameters according to selected language
	 * @param $language_id
	 */
	public function LoadData($language_id = '')
	{
		$language_id = ($language_id != '') ? $language_id : $this->language_id;
		$sql = 'SELECT * FROM '.TABLE_SITE_DESCRIPTION.' WHERE language_id = \''.$language_id.'\'';
		$this->res = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);		
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
	 * @param $language_id
	 */
	public function UpdateFields($params = array(), $language_id = '')
	{		
		// check if this is a DEMO
		if(strtolower(SITE_MODE) == 'demo'){ $this->error = _OPERATION_BLOCKED; return false; }
		
		$language_id = ($language_id != '') ? $language_id : $this->language_id;
		
		if(count($params) > 0){
			// prepare UPDATE statement
			$sql = 'UPDATE '.TABLE_SITE_DESCRIPTION.' SET ';
			$count = 0;
			foreach($params as $key => $val){
				if($count++ > 0) $sql .= ', ';
				$sql .= $key.' = \''.encode_text($val).'\'';				
			}
			$sql .= ' WHERE language_id = \''.$language_id.'\'';
			if(database_void_query($sql)){				
				$this->LoadData($language_id);
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
	 * Returns tagged string 
	 * @param $string
	 */
	public function GetDataTagged($str)
	{
		$str = str_replace('&lt;', '<', $str); 
		$str = str_replace('&gt;', '>', $str);
		$str = str_replace('&#034;', '"', $str);
		$str = str_replace('&#039;', "'", $str);
		return $str;		
	}	
	
	/**
	 * Draws site's footer
	 * @param $draw
	 */
	public function DrawFooter($draw = true)	
	{
		$output = $this->GetDataTagged($this->GetParameter('footer_text'));

		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 * Draws site's header
	 * @param $draw
	 */
	public function DrawHeader($draw = true)
	{
		$output = $this->GetDataTagged($this->GetParameter('header_text'));

		if($draw) echo $output;
		else return $output;
	}
	
}
