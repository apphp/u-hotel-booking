<?php

/***
 *	Class Session
 *  -------------- 
 *  Description : encapsulates session properties
 *	Written by  : ApPHP
 *  Version     : 1.0.3
 *  Updated	    : 06.05.2012
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct			    Set						RegenerateID	
 *	__destruct	            Get						FingerInfo
 *  SetSessionVariable      IsExists
 *  GetSessionVariable
 *  SetMessage
 *  IsMessage
 *  GetMessage
 *  EndSession
 *  AnalyseFingerInfo
 *
 *  ChangeLog:
 *  ----------
 *	1.0.3
 *	    - added session namespace
 *	    -
 *	    -
 *	    -
 *	    -
 *	1.0.2
 *		- added cookie deleting in EndSession()
 *	    - added check for isset($_SESSION) in constructor
 *	    - added session protection, based on AnalyseFingerInfo, RegenerateID and FingerInfo methods
 *	    - fixed error of undefined offset in FingerInfo
 *	    - added static Set(), Get() and IsExists()
 **/

class Session {
	
	private $deleteOldSession;
	private $secureWord;
	private $userAgent;
	private $useIpBlocks;
	private $algorithm;
	private static $nameSpace = INSTALLATION_KEY;

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		if(!isset($_SESSION)){
			@session_start();
		}
		
		$this->deleteOldSession = false;
		$this->secureWord = (defined('INSTALLATION_KEY') && INSTALLATION_KEY != '') ? INSTALLATION_KEY : 'SECWRD_';
		$this->userAgent = true;
		$this->useIpBlocks = true;
		$this->algorithm = (function_exists('hash') && in_array('sha256', hash_algos())) ? 'sha256' : null;
	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Sets session variable 
	 * @param $name
	 * @param $value
	 */
	public function SetSessionVariable($name, $value)
	{
		if(!empty(self::$nameSpace)){
			$_SESSION[self::$nameSpace][$name] = $value;	
		}else{
			$_SESSION[$name] = $value;
		}		
	}
	
	/**
	 * Returns session variable 
	 * @param $name
	 */
	public function GetSessionVariable($name)
	{
		if(!empty(self::$nameSpace)){
			return isset($_SESSION[self::$nameSpace][$name]) ? $_SESSION[self::$nameSpace][$name] : '';	
		}else{
			return isset($_SESSION[$name]) ? $_SESSION[$name] : '';	
		}		
	}

	/**
	 * Sets session variable 
	 * @param $name
	 * @param $value
	 */
	public static function Set($name, $value)
	{
		if(!empty(self::$nameSpace)){
			$_SESSION[self::$nameSpace][$name] = $value;
		}else{
			$_SESSION[$name] = $value;
		}		
	}
	
	/**
	 * Returns session variable 
	 * @param $name
	 */
	public static function Get($name)
	{
		if(!empty(self::$nameSpace)){
			return isset($_SESSION[self::$nameSpace][$name]) ? $_SESSION[self::$nameSpace][$name] : '';
		}else{
			return isset($_SESSION[$name]) ? $_SESSION[$name] : '';
		}				
	}

	/**
	 * Check if session variable exists
	 * @param $name
	 */
	public static function IsExists($name)
	{
		if(!empty(self::$nameSpace)){
			return isset($_SESSION[self::$nameSpace][$name]) ? true : false;
		}else{
			return isset($_SESSION[$name]) ? true : false;
		}
	}

	/**
	 * Sets message
	 * @param $name
	 * @param $value
	 */
	public function SetMessage($name, $value)
	{
		if(!empty(self::$nameSpace)){
			$_SESSION[self::$nameSpace]['messages'][$name] = $value;
		}else{
			$_SESSION['messages'][$name] = $value;
		}		
	}
	
	/**
	 * Checks if there is a message
	 * @param $name
	 */
	public function IsMessage($name)
	{
		if(!empty(self::$nameSpace)){
			return (isset($_SESSION[self::$nameSpace]['messages'][$name]) && $_SESSION[self::$nameSpace]['messages'][$name] != '') ? true : false;
		}else{
			return (isset($_SESSION['messages'][$name]) && $_SESSION['messages'][$name] != '') ? true : false;
		}
	}

	/**
	 * Returns message and clear 
	 * @param $name
	 */
	public function GetMessage($name)
	{
		if(!empty(self::$nameSpace)){
			$msg = isset($_SESSION[self::$nameSpace]['messages'][$name]) ? $_SESSION[self::$nameSpace]['messages'][$name] : '';
			if($msg != '') $_SESSION[self::$nameSpace]['messages'][$name] = '';
		}else{
			$msg = isset($_SESSION['messages'][$name]) ? $_SESSION['messages'][$name] : '';
			if($msg != '') $_SESSION['messages'][$name] = '';
		}
        return $msg;
	}

	/**
	 * Destroys the session
	 */
	public function EndSession()
	{
		if(isset($_SESSION)){
			if(!empty(self::$nameSpace)){
				$_SESSION[self::$nameSpace] = array();
			}else{
				$_SESSION = array();
			}
			///if(isset($_COOKIE[session_name()])) {
				///setcookie(session_name(), '', time() - 42000, '/');
			//}
			@session_destroy();
		}
	}

	/**
	 * Analyses if the check failed or not
	 */
	public function AnalyseFingerInfo()
	{
		//$this->RegenerateID();
		if(!empty(self::$nameSpace)){
			if(isset($_SESSION[self::$nameSpace]['_FingerInfo'])) {
				return ($_SESSION[self::$nameSpace]['_FingerInfo'] === $this->FingerInfo());
			}
		}else{
			if(isset($_SESSION['_FingerInfo'])) {
				return ($_SESSION['_FingerInfo'] === $this->FingerInfo());
			}
		}
		return false;
	}

	/**
	 * Sets finger info
	 */
	public function SetFingerInfo()
	{
		$this->RegenerateID();
		if(!empty(self::$nameSpace)){
			$_SESSION[self::$nameSpace]['_FingerInfo'] = $this->FingerInfo();
		}else{
			$_SESSION['_FingerInfo'] = $this->FingerInfo();
		}		
	}
	
	/**
	 * Regenerates Session ID
	 */
	private function RegenerateID(){
		if(function_exists('session_regenerate_id')) {
			if(version_compare(phpversion(), '5.1.0', '>=') && $this->deleteOldSession == true) {
				session_regenerate_id(true);
			}else{
				session_regenerate_id();
			}
		}
	}

	/**
	 * Returns Finger Info
	 */
	private function FingerInfo()
	{
		$finger_info = $this->secureWord;

		if($this->userAgent === true) $finger_info .= $_SERVER['HTTP_USER_AGENT'];
		if($this->useIpBlocks){
			$arrBlocks = array();
			$arrIpParts = explode('.', $_SERVER['REMOTE_ADDR']);

			for($i = 0; $i < 3; $i++){
				$arrBlocks[] = isset($arrIpParts[$i]) ? $arrIpParts[$i] : '';
			}

			$finger_info .= implode('.', $arrBlocks);
			unset($arrBlocks, $arrIpParts);
		}

		if($this->algorithm !== null){
			return hash($this->algorithm, $finger_info);
		}else{
			return (function_exists('sha1')) ? sha1($finger_info) : md5($finger_info);
		}
	}
	
}
