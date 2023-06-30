<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// GLOBAL FUNCTIONS 24.04.2018

// Auto-loading classes
//------------------------------------------------------------------------------
// Set autoload register function
spl_autoload_register('my_autoloader');

function my_autoloader($class_name){

    $core_classes = array(
        /* core classes ALL - no differences */
        'Backup',
        'BanList',
        'Banners',
        'Email',
        'GalleryAlbums',
        'GalleryAlbumItems',
		'MailLogs',
        'MicroGrid',
        'Modules',
        'ModulesSettings',
        'Roles',
        'RolePrivileges',
        'Session',
        'Settings',
        'SocialNetworks',
        'States',
        /* core classes ALL - have differences */
        'Cron',
        /* core classes excepting - no differences */
        'Accounts',
        'Admins',
        'ContactUs',
        'FaqCategories',
        'FaqCategoryItems',
        'News',
        'NewsSubscribed',
        'PagesGrid',
        'RSSFeed',
        'SiteDescription',
        'Vocabulary',
        /* core classes excepting - have differences */
        'AdminsAccounts',
        'Application',
        'Comments',
        'EmailTemplates',
        'Languages',
        'Pages',
        'Currencies',
    );

    $api_classes = array(
		'BookingsApi',
		'BookingsRoomsApi',
		'CustomersApi',
		'HotelsApi',
		'HotelsDescriptionApi',
		'RoomsApi',
		'RoomsDescriptionApi',
    );

    if($class_name == 'PHPMailer'){
		require_once((defined('BASE_DIR') ? BASE_DIR : '').'modules/phpmailer/class.phpmailer.php');
	}elseif($class_name == 'tFPDF'){
		require_once('modules/tfpdf/tfpdf.php');
    }elseif(in_array($class_name, $core_classes)){
        require_once('classes/core/'.$class_name.'.class.php');	
    }elseif(in_array($class_name, $api_classes)){
        require_once('classes/api/'.$class_name.'.class.php');	
	}else{
		if(is_file(__DIR__.'/classes/'.$class_name.'.class.php')){
			require_once('classes/'.$class_name.'.class.php');	
		}
	}	
}

/**
 * 	Set error handler
 * 	@param closere function
 */
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    // Error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
	
	global $PROFILER;

	switch($errno){
		case E_ERROR:
		case E_USER_ERROR:
			throw new ErrorException($errstr, $errno);
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			$PROFILER['warnings'][] = "Notice: ".$errstr.'<br>'.nl2br(prepare_backtrace());
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$PROFILER['warnings'][] = "Warning: ".$errstr.'<br>'.nl2br(prepare_backtrace());
			break;
		default:
			$PROFILER['errors'][] = "Unknown Error: ".$errstr.'<br>'.nl2br(prepare_backtrace());
			break;
	}
    
});


/**
 * 	Returns time difference
 * 	@param first_time
 * 	@param last_time
 */
function time_diff($first_time, $last_time)
{
	// convert to unix timestamps
	$time_diff=strtotime($last_time)-strtotime($first_time);
	return $time_diff;
}

/**
 * Get nights difference
 */
function nights_diff($datefrom, $dateto)
{
	$datefrom = strtotime($datefrom, 0);
	$dateto = strtotime($dateto, 0);
	$difference = $dateto - $datefrom; // Difference in seconds
     
    $datediff = round($difference / 86400);
	return $datediff;
}

/**
 * Dates overlap
 */
function dates_overlap($start_one, $end_one, $start_two, $end_two) {

	$datetime_start_1 = new DateTime($start_one);
	$datetime_end_1 = new DateTime($end_one);
	
	$datetime_start_2 = new DateTime($start_two);
	$datetime_end_2 = new DateTime($end_two);
	
	$start = max($datetime_start_2, $datetime_start_1);
	$end = min($datetime_end_1, $datetime_end_2);
	return $end >= $start ? $end->diff($start)->days : 0;
}

/**
 * Highlight rows
 * @param $ind
 * @param $offset
 */
function highlight($ind, $offset = 1)
{
	if(($ind + $offset) % 2  == 0) $highlight = ' class="highlight_light"';
	else $highlight = ' class="highlight_dark"';
	return $highlight;
}

/**
 * Get random string
 * @param $length
 */
function get_random_string($length = 20)
{
	$template = '1234567890abcdefghijklmnopqrstuvwxyz';
	settype($template, 'string');
	settype($length, 'integer');
	settype($rndstring, 'string');
	settype($a, 'integer');
	settype($b, 'integer');           
	for ($a = 0; $a < $length; $a++) {
		$b = rand(0, strlen($template) - 1);
		$rndstring .= $template[$b];
	}       
	return $rndstring;       
}


/**
 * Camel Case
 * @param $string
 */
function camel_case($string)
{
	if(function_exists('mb_convert_case')){
		return mb_convert_case($string, MB_CASE_TITLE, mb_detect_encoding($string));				
	}else{
		return $string;				
	}	
}

/**
 * Create SEO url from string
 * @param $string
 */
function create_seo_url($string = '')
{
	$forbidden_simbols = array("\\", '"', "'", '(', ')', '[', ']', '*', '.', ',', '&', ';', ':', '&amp;', '?', '!', '=');

	$string = str_replace($forbidden_simbols, '', $string);
	$splitted_string = explode(' ', $string);
	$seo_url = '';
	$words_counter = 0;
	foreach($splitted_string as $key){
		if(trim($key) != ''){
			if($words_counter++ < 6){
				$seo_url .= ($seo_url != '') ? '-'.$key : $key;   
			}else{
				break;   
			}               
		}           
	}
	return substr($seo_url, 0, 125);
}

/**
 * Humanize string
 * @param string $str
 * @param string $func
 */
function humanize($str = '', $func = '') {
	if(!empty($str)){
		$str = trim(strtolower($str));
		$str = preg_replace('/_/', ' 	', $str);
		$str = preg_replace('/[^a-z0-9_\s+]/', '', $str);
		$str = trim(preg_replace('/\s+/', ' ', $str));
		$str = explode(' ', $str); 
		$str = array_map('ucwords', $str);
		$str = implode(' ', $str);
		
		if(!empty($func) && function_exists($func)){
			$str = $func($str);
		}
		
		return $str;
	}
	
	return '';
}

/**
 * Get base Path
 */
function get_base_path()
{
	$script_name = basename($_SERVER['SCRIPT_FILENAME']);
	$script_url = '';
	
	if(isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $script_name){
		$script_url = $_SERVER['SCRIPT_NAME'];
	}elseif(basename($_SERVER['PHP_SELF']) === $script_name){
		$script_url = $_SERVER['PHP_SELF'];
	}elseif(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $script_name){
		$script_url = $_SERVER['ORIG_SCRIPT_NAME'];
	}elseif(isset($_SERVER['SCRIPT_NAME']) && ($pos=strpos($_SERVER['PHP_SELF'], '/'.$script_name)) !== false){
		$script_url = substr($_SERVER['SCRIPT_NAME'], 0, $pos).'/'.$script_name;
	}elseif(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0){
		$script_url = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
	}

	return rtrim(dirname($script_url),'\\/').'/';
}

/**
 * Get base URL 
 */
function get_base_url()
{
	$protocol = 'http://';
	$port = '';
	$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : '';
	if((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) || strtolower(substr($server_protocol, 0, 5)) == 'https'){
		$protocol = 'https://';
	}			
	if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80'){
        if(!strpos($http_host, ':')){
			$port = ':'.$_SERVER['SERVER_PORT'];
		}
	}	
	$folder = get_foolder();	
	return $protocol.$http_host.$port.$folder;
}

/**
 * Get page URL 
 */
function get_page_url($urlencode = true)
{
	$protocol = 'http://';
	$port = '';
	$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : '';
	if((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) || strtolower(substr($server_protocol, 0, 5)) == 'https'){
		$protocol = 'https://';
	}			
	if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80'){
        if(!strpos($http_host, ':')){
			$port = ':'.$_SERVER['SERVER_PORT'];
		}
	}		
	// fixed for work with both Apache and IIS
	if(!isset($_SERVER['REQUEST_URI'])){	
		$uri = substr(prepare_input($_SERVER['PHP_SELF'], false, 'extra'),0);
		if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') {
			$uri .= '?'.prepare_input($_SERVER['QUERY_STRING'], false, 'extra');
		}
	}else{
		$uri = prepare_input($_SERVER['REQUEST_URI'], false, 'extra');
		if(preg_match("/:|;|'|\(|\)/", $uri)) $uri = '';
	}	
	if(isset($_GET['p'])){
		$uri = remove_url_param($uri, 'p');
	}
	if($urlencode) $uri = str_replace('&', '&amp;', $uri);
	return $protocol.$http_host.$port.$uri;
}

/**
 * Removes parameter from url
 * @param $url
 * @param $param
 */
function remove_url_param($url, $param)
{
    $url_parse = parse_url($url);
    parse_str($url_parse['query'], $uri_parts);
    unset($uri_parts['p']);
    return $url_parse['path'].'?'.http_build_query($uri_parts);   
}

/**
 * Read subfolders of directory
 */
function read_directory_subfolders($dir = '.'){
	$folder=dir($dir); 
	$arrFolderEntries = array();
	while($folderEntry=$folder->read()){
		if($folderEntry != '.' && $folderEntry != '..' && is_dir($dir.$folderEntry) && strtolower($folderEntry) != 'admin') 
			$arrFolderEntries[] = $folderEntry; 
	}     
	$folder->close(); 
	return $arrFolderEntries;
}

/**
 * Cut string by last word
 */
function substr_by_word($text, $length = '0', $three_dots = false, $lang = 'en')
{
	$output = substr($text, 0, (int)$length);
	if(strlen($text) > $length){
		$blank_pos = strrpos($output, ' ');		
        if($lang == 'en'){
            if($blank_pos > 0) $output = substr($output, 0, $blank_pos);		
        }else{
			if($blank_pos > 0) $output = mb_substr($text, 0, $length, 'UTF-8');
        }
		if($three_dots) $output .= '...';
	}
	return $output;
}

/**
 * Get current IP
 */
function get_current_ip()
{
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		// IP from share internet
		$user_ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(isset($_SERVER['HTTP_X_FORWARD_FOR']) && $_SERVER['HTTP_X_FORWARD_FOR']){
		$user_ip = $_SERVER['HTTP_X_FORWARD_FOR'];
	}else{
		$user_ip = $_SERVER['REMOTE_ADDR'];
	}

	return $user_ip;
}

/**
 * Get currency format
 */
function get_currency_format()
{
	global $objSettings;	
	
	if($objSettings->GetParameter('price_format') == 'european'){
		$price_format = 'european';
	}else{
		$price_format = 'american';
	}
	return $price_format;
}

/**
 * Format datetime
 * @param $datetime
 * @param $format
 * @param $empty_text
 * @param $locale
 */
function format_datetime($datetime, $format = '', $empty_text = '', $locale = false)
{	
	$format = ($format == '') ? get_datetime_format() : $format;
	
	$datetime_check = preg_replace('/0|-| |:/', '', $datetime);	
	if($datetime_check != ''){
		$datetime_new = @mktime(substr($datetime, 11, 2), substr($datetime, 14, 2),
							   substr($datetime, 17, 2), substr($datetime, 5, 2),
						       substr($datetime, 8, 2), substr($datetime, 0, 4));
      
		// convert datetime according to local settings
		if($locale && Application::Get('lang') != 'en'){
			$format = str_replace('%b', get_month_local(@strftime('%m', $datetime_new)), get_datetime_format(true, true));
			return @strftime($format, $datetime_new);
		}

		return @date($format, $datetime_new);						
	}else{
		return $empty_text;
	}		
}

/**
 * Get datetime format
 * @param $show_hours
 * @param $locale
 */
function get_datetime_format($show_hours = true, $locale = false)
{
	global $objSettings;	
	
	if($objSettings->GetParameter('date_format') == 'dd/mm/yyyy'){
		if($locale) $datetime_format = ($show_hours) ? '%d %b, %Y %H:%M' : '%d %b, %Y';
		else $datetime_format = ($show_hours) ? 'd M, Y g:i A' : 'd M, Y';
	}else{
		if($locale) $datetime_format = ($show_hours) ? '%b %d, %Y %H:%M' : '%b %d %Y';
		else $datetime_format = ($show_hours) ? 'M d, Y g:i A' : 'M d, Y';
	}
	return $datetime_format;
}

/**
 * Get time format
 * @param $show_seconds
 * @param $settings_format
 */
function get_time_format($show_seconds = true, $settings_format = false)
{
	global $objSettings;	
	
	if($objSettings->GetParameter('time_format') == 'am/pm'){
		if($settings_format) $time_format = 'am/pm';
		else $time_format = ($show_seconds) ? 'g:i:s A' : 'g:i A'; 		
	}else{
		if($settings_format) $time_format = '24';
		else $time_format = ($show_seconds) ? 'H:i:s' : 'H:i'; 		
	}
	return $time_format;
}

/**
 * Format date
 * @param $date
 * @param $format
 * @param $empty_text
 * @param $locale 
 */
function format_date($date, $format = '', $empty_text = '', $locale = false)
{	
	$format = ($format == '') ? get_date_format() : $format;
	
	if(!is_empty_date($date)){
		$date_new = mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));

		// convert date according to local settings
		if($locale && Application::Get('lang') != 'en'){
			$format = str_replace('%b', get_month_local(@strftime('%m', $date_new)), get_date_format('', false, true));
			return @strftime($format, $date_new);
		}

		return @date($format, $date_new);						
	}else{
		return $empty_text;
	}		
}

/**
 * Get date format
 * @param $format
 * @param $settings_format
 * @param $locale 
 */
function get_date_format($format = 'view', $settings_format = false, $locale = false)
{
	global $objSettings;	
	
	if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
		if($locale) $date_format = '%b %d, %Y';
		else if($settings_format) $date_format = 'mm/dd/yyyy';
		else $date_format = ($format == 'edit') ? 'm-d-y' : 'M d, Y';		
	}else{
		if($locale) $date_format = '%d %b, %Y';
		else if($settings_format) $date_format = 'dd/mm/yyyy';
		else $date_format = ($format == 'edit') ? 'd-m-y' : 'd M, Y';
	}
	return $date_format;
}

/**
 * Get month local name
 * @param $mon
 */
function get_month_local($mon)
{
	$months = array(
		'1' => _JANUARY,
		'2' => _FEBRUARY,
		'3' => _MARCH,
		'4' => _APRIL,
		'5' => _MAY,
		'6' => _JUNE,
		'7' => _JULY,
		'8' => _AUGUST,
		'9' => _SEPTEMBER,
		'10' => _OCTOBER,
		'11' => _NOVEMBER,
		'12' => _DECEMBER
	);
	return isset($months[(int)$mon]) ? $months[(int)$mon] : '';
}

/**
 * Get week day local name
 * @param $wday
 */
function get_weekday_local($wday)
{
	$weekdays = array(
		'1' => _SUNDAY,
		'2' => _MONDAY,
		'3' => _TUESDAY,
		'4' => _WEDNESDAY,
		'5' => _THURSDAY,
		'6' => _FRIDAY,
		'7' => _SATURDAY
	);
	return isset($weekdays[(int)$wday]) ? $weekdays[(int)$wday] : '';
}

/**
 * Get fuel types
 * @param $type
 */
function get_fuel_types($type = '')
{
	$fuel_types = array(
		'1' => _BIODIESEL,
		'2' => _CNG,
		'3' => _DIESEL,
		'4' => _ELECTRIC,
		'5' => _ETHANOL_FFV,
		'6' => _GASOLINE,
		'7' => _HYBRID_ELECTRIC,
		'8' => _PETROL,
		'9' => _STEAM,
		'10' => _OTHER
	);
	
	if(!empty($type)){
		return isset($fuel_types[(int)$type]) ? $fuel_types[(int)$type] : '';	
	}else{
		return $fuel_types;
	}	
}

/**
 * Get transmissions
 * @param $type
 */
function get_transmissions($type = '')
{
	$transmissions = array(
		'automatic' => _AUTOMATIC,
		'manual' 	=> _MANUAL,
		'tiptronic' => _TIPTRONIC
	);
	
	if(!empty($type)){
		return isset($transmissions[$type]) ? $transmissions[$type] : '';	
	}else{
		return $transmissions;
	}	
}

/**
 * Draw breadcrumbs
 * @param $breadcrumbs
 */
function prepare_breadcrumbs($breadcrumbs)
{
	$output = '';
	if(is_array($breadcrumbs)){
        if(Application::Get('lang_dir') == 'rtl'){
            $raquo = '&laquo;';
        }else{
            $raquo = '&raquo;';
        }
		
		foreach($breadcrumbs as $key => $val){
			if(!empty($key)){
				if(!empty($output)) $output .= ' '.$raquo.' ';
				if(!empty($val)) $output .= '<a class="cbc" href="'.APPHP_BASE.$val.'">'.$key.'</a>';
				else $output .= '<span class="cbc">'.$key.'</span>';
			}
		}	
	}
	return $output;
}

/**
 * Remove bad chars from input
 * @param $str_words - input
 * @param $escape
 * @param $level
 */
function prepare_input($str_words, $escape = false, $level = 'high')
{
	$found = false;
	if($level == 'low'){
		$bad_string = array('%20union%20', '*/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<applet', '<meta', '<style', '<form', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', 'include_path', 'prefix', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=', 'onload=');
	}elseif($level == 'medium'){
		$bad_string = array('xp_', '%20union%20', '/*', '*/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', 'script>', '<iframe', '<applet', '<meta', '<style', '<form', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=', 'onload=');		
	}elseif($level == 'high'){
		$bad_string = array('select', 'drop', '--', 'insert', 'xp_', '%20union%20', '/*', '*/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', 'script>', '<iframe', '<applet', '<meta', '<style', '<form', '<img', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'http://', 'https://', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=', 'onload=');
	}elseif($level == 'extra'){
		$bad_string = array('select', 'drop', '--', 'insert', 'xp_', '%20union%20', '/*', '*/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', 'script>', '<iframe', '<applet', '<meta', '<style', '<form', '<img', '<body', '<link', '<input', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'http://', 'https://', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=', 'onload=', '<', '>', "'", '"', ';');
	}
	for($i = 0; $i < count($bad_string); $i++){
		$str_words = str_ireplace($bad_string[$i], '', $str_words);	
	}
	
	if($escape){
		$str_words = encode_text($str_words); 
	}
	
	return $str_words;            
}

/**
 * Prepares alpha numeric input
 * @param $field
 */
function prepare_input_alphanumeric($field = '')
{
	$field = (string)$field;
	
	if(preg_match('/[^a-zA-z0-9_\-\.]/', $field)){
		return '';
	}else{
		return $field;
	}
}

function check_input($input, $level = 'medium')
{	
	if($input == '') return true;
	
    $error = 0;
	$bad_string = array('%20union%20', '/*', '*/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', '<iframe', '<applet', '<meta', '<style', '<form', '<img', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'http://', 'https://', 'ftp://', 'smb://' );
	foreach($bad_string as $string_value){
		if(strstr($input, $string_value)) $error = 1;
	}
	
	if((preg_match('/<[^>]*script*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*object*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*iframe*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*applet*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*meta*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*style*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*form*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*img*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*onmouseover*\"?[^>]*>/i', $input)) ||
		(preg_match('/<[^>]*body*\"?[^>]*>/i', $input)) ||
		(preg_match('/\([^>]*\"?[^)]*\)/i', $input)) || 
		(preg_match('/ftp:\/\//i', $input)) || 
		(preg_match('/https:\/\//i', $input)) || 
		(preg_match('/http:\/\//i', $input)) )
	{		
		$error = 1;
	}
	
	$ss = $_SERVER['HTTP_USER_AGENT'];
	
	if((preg_match('/libwww/i',$ss)) ||
	    (preg_match('/^lwp/i',$ss))  ||
	    (preg_match('/^Jigsaw/i',$ss)) ||
	    (preg_match('/^Wget/i',$ss)) ||
	    (preg_match('/^Indy\ Library/i',$ss)) )
	{ 
	    $error = 1;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if(!empty($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_HOST'])){
			if(!preg_match('/'.$_SERVER['HTTP_HOST'].'/i', $_SERVER['HTTP_REFERER'])) $error = 1;
		}
	}
    if($error){
        return '';
    }
	return true;
}

/**
 * Start Caching of page
 * @param $cachefile - name of file to be cached
 */
function start_caching($cachefile)
{
	global $objSettings;	

	$cache_lifetime = (int)$objSettings->GetParameter('cache_lifetime');
	
	if($cachefile != '' && file_exists(CACHE_DIRECTORY.$cachefile)) {        
		$cachetime = $cache_lifetime * 60; /* cache lifetime in minutes */
		// Serve from the cache if it is younger than $cachetime
		if(file_exists(CACHE_DIRECTORY.$cachefile) && (filesize(CACHE_DIRECTORY.$cachefile) > 0) && ((time() - $cachetime) < filemtime(CACHE_DIRECTORY.$cachefile))){
			// the page has been cached from an earlier request output the contents of the cache file
			include_once(CACHE_DIRECTORY.$cachefile); 
			echo '<!-- Generated from cache at '.@date('H:i', filemtime(CACHE_DIRECTORY.$cachefile)).' -->'."\n";
			return true;
		}        
	}
	// start the output buffer
	ob_start();
}

/**
 * Finish Caching of page
 * 	    @param $cachefile - name of file to be cached
 */
function finish_caching($cachefile)
{
	if($cachefile != ''){
		$fp = @fopen(CACHE_DIRECTORY.$cachefile, 'w'); 
		@fwrite($fp, ob_get_contents());
		@fclose($fp); 
		// Send the output to the browser
		ob_end_flush();
		// check if we exeeded max number of cache files
		check_cache_files();
	}
}

/**
 * Delete all cache files
 */
function delete_cache()
{
	global $objSettings;	
	
	///if(!$objSettings->GetParameter('caching_allowed')) return false;
	
	if($hdl = @opendir(CACHE_DIRECTORY)){
		while(false !== ($obj = @readdir($hdl))){
			if($obj == '.' || $obj == '..' || $obj == '.htaccess') continue; 
			@unlink(CACHE_DIRECTORY.$obj);
		}
	}
}    

/**
 * Check chache files
 */
function check_cache_files()
{		
	$oldest_file_name = '';
	$oldest_file_time = @date('Y-m-d H:i:s');

	if(count(glob(CACHE_DIRECTORY.'*')) > 100){
		if($hdl = opendir(CACHE_DIRECTORY)){
			while(false !== ($obj = @readdir($hdl))){
				if($obj == '.' || $obj == '..' || $obj == '.htaccess') continue; 
				$file_time = @date('Y-m-d H:i:s', filectime(CACHE_DIRECTORY.$obj));
				if($file_time < $oldest_file_time){
					$oldest_file_time = $file_time;
					$oldest_file_name = CACHE_DIRECTORY.$obj;
				}				
			}
		}		
		@unlink($oldest_file_name);		
	}
}

/**
 * Convert to decimal number with leading zero
 * @param $number
 */	
function convert_to_decimal($number)
{
	return (($number < 0) ? '-' : '').((abs($number) < 10) ? '0' : '').abs($number);
}

/**
 * Get encoded text
 * @param $string
 */
function encode_text($string = '')
{
	$search	 = array("\\","\0","\n","\r","\x1a","'",'"',"\'",'\"');
	$replace = array("\\\\","\\0","\\n","\\r","\Z","\'",'\"',"\\'",'\\"');
	return str_replace($search, $replace, $string);
}

/**
 * Get decoded text
 * @param $string
 */
function decode_text($string = '', $code_quotes = true, $quotes_type = '')
{
	$single_quote = "'";
	$double_quote = '"';		
	if($code_quotes){
		if(!$quotes_type){
			$single_quote = '&#039;';
			$double_quote = '&#034;';
		}elseif($quotes_type == 'single'){
			$single_quote = '&#039;';
		}elseif($quotes_type == 'double'){
			$double_quote = '&#034;';
		}
	}
	
	$search  = array("\\\\","\\0","\\n","\\r","\Z","\\'",'\\"','"',"'");
	$replace = array("\\","\0","\n","\r","\x1a","\'",'\"',$double_quote,$single_quote);
	return str_replace($search, $replace, $string);
}

/**
 * Get quoted text
 * @param $string
 */
function quote_text($string = '')
{
	return '\''.$string.'\'';
}

/**
 * Prepare permanent link
 * @param $href
 * @param $link
 * @param $target
 * @param $css_class
 * @param $title
 * @param $js_event
 * 		ex.: prepare_permanent_link('index.php?admin=login', _ADMIN_LOGIN, '', 'main_link');
 */
function prepare_permanent_link($href, $link, $target = '', $css_class = '', $title = '', $js_event = '')
{
	$css_class = ($css_class != '') ? ' class="'.$css_class.'"' : '';
	$target = ($target != '') ? ' target="'.$target.'"' : '';
	$rel = ($target == '_blank') ? ' rel="noopener noreferrer"' : '';
	$title = ($title != '') ? ' title="'.decode_text($title).'"' : '';
	$js_event = ($js_event != '') ? ' '.$js_event : '';
	$base = !preg_match('/http:\/\/|https:\/\/|ftp:\/\/|javascript|www./i', $href) ? APPHP_BASE : '';
	
	return '<a'.$css_class.$target.$rel.$title.$js_event.' href="'.$base.$href.'">'.$link.'</a>';
}

/**
 * Prepare link
 * @param $page_type
 * @param $page_id_param
 * @param $page_id
 * @param $page_url_name
 * @param $page_name
 * @param $css_class
 * @param $title
 * @param $href_only
 * @param $target
 */
function prepare_link($page_type, $page_id_param, $page_id, $page_url_name, $page_name, $css_class = '', $title = '', $href_only = false, $target = '')
{
	global $objSettings;	
	
	$css_class = ($css_class != '') ? ' class="'.$css_class.'"' : '';
	$title = ($title != '') ? ' title="'.decode_text($title).'"' : '';
	$page_url_name = str_replace(array(" ", "\\'", '\\"', '#', "'", '"'), '-', (($page_url_name != '') ? $page_url_name : $page_name));
	$target = ($target != '') ? ' target="'.$target.'"' : '';
	$rel = ($target == '_blank') ? ' rel="noopener noreferrer"' : '';

	// Use SEO optimized link	
	if($objSettings->GetParameter('seo_urls') == '1'){
		$href = $page_type.(($page_id != '') ? '/'.$page_id : '').(($page_url_name != 'index') ? '/'.$page_url_name.'.html' : '.html');
		if($href_only) return $href;
		else return '<a'.$css_class.$title.$target.$rel.' href="'.APPHP_BASE.$href.'">'.$page_name.'</a>';
	}else{
		$href = 'index.php?page='.$page_type.(($page_id_param != '') ? '&amp;'.$page_id_param.'='.$page_id : '');
		if($href_only) return $href;
		else return '<a'.$css_class.$title.$target.$rel.' href="'.APPHP_BASE.$href.'">'.decode_text($page_name).'</a>';
	}	
}

/**
 * Returns timezone by offset (last change 12.09.2011)
 */
function get_timezone_by_offset($offset)
{
	$zonelist = array(
	   'Pacific/Kwajalein' => -12.00,
	   'Pacific/Samoa' => -11.00,
	   'Pacific/Honolulu' => -10.00,
	   'Pacific/Marquesas' => -9.50,
	   'America/Juneau' => -9.00,
	   'America/Los_Angeles' => -8.00,
	   'America/Denver' => -7.00,
	   'America/Mexico_City' => -6.00,
	   'America/New_York' => -5.00,
	   'America/Caracas' => -4.50,
	   'America/Halifax' => -4.00,
	   'America/St_Johns' => -3.50,
	   'America/Argentina/Buenos_Aires' => -3.00,
	   'Atlantic/South_Georgia' => -2.00,
	   'Atlantic/Azores' => -1.00,
	   //'Europe/London' => 0,
	   'UTC' => 0,
	   'Europe/Berlin' => 1.00,
	   'Europe/Helsinki' => 2.00,
	   'Asia/Kuwait' => 3.00,
	   'Asia/Tehran' => 3.50,      
	   'Asia/Muscat' => 4.00,
	   'Asia/Kabul' => 4.50,
	   'Asia/Yekaterinburg' => 5.00,
	   'Asia/Kolkata' => 5.50,
	   'Asia/Kathmandu' => 5.75,
	   'Asia/Dhaka' => 6.00,
	   'Asia/Rangoon' => 6.50,
	   'Asia/Bangkok' => 7.00,
	   'Asia/Brunei' => 8.00,
	   'Australia/Eucla' => 8.75,      
	   'Asia/Tokyo' => 9.00,
	   'Australia/Darwin' => 9.50,
	   'Australia/Canberra' => 10.00,
	   'Australia/Lord_Howe' => 10.50,
	   'Asia/Magadan' => 11.00,
	   'Pacific/Norfolk' => 11.50,
	   'Pacific/Fiji' => 12.00,
	   'Pacific/Chatham' => 12.75,
	   'Pacific/Tongatapu' => 13.00,
	   'Pacific/Kiritimati' => 14.00
	);
	$index = array_keys($zonelist, $offset);
	if(sizeof($index)!=1) return false;
	return $index[0];
} 

/**
 * Get OS name
 */
function get_os_name()
{
	// some possible outputs
	// Linux: Linux localhost 2.4.21-0.13mdk #1 Fri Mar 14 15:08:06 EST 2003 i686		
	// FreeBSD: FreeBSD localhost 3.2-RELEASE #15: Mon Dec 17 08:46:02 GMT 2001		
	// WINNT: Windows NT XN1 5.1 build 2600		
	// MAC: Darwin Ron-Cyriers-MacBook-Pro.local 10.6.0 Darwin Kernel Version 10.6.0: Wed Nov 10 18:13:17 PST 2010; root:xnu-1504.9.26~3/RELEASE_I386 i386
	$os_name = strtoupper(substr(PHP_OS, 0, 3));
	switch($os_name){
		case 'WIN':
			return 'windows'; break;
		case 'LIN':
			return 'linux'; break;
		case 'FRE':
			return 'freebsd'; break;
		case 'DAR':
			return 'mac'; break;
		default:
			return 'windows'; break;
	}
}

/**
 * Send email
 * @param $recipient
 * @param $sender
 * @param $email_template
 * @param $replace_holders
 * @param $cc_email
 * @param $cc_subject
 * @param $debug
 */
function send_email($recipient, $sender, $email_template, $replace_holders = array(), $lang = '', $cc_email = '', $cc_subject = '', $debug = false)
{
	global $objSettings, $objLogin;

	// Prevent sendign emails on DEMO mode
	if(SITE_MODE == 'demo'){
		return false;
	}

	$email_html = false;
	
	if($lang == ''){
		$lang = Application::Get('lang');
		$lang_dir = Application::Get('lang_dir');
	}else{
		$lang_dir = Languages::Get($lang, 'lang_dir');
	}
	
	$objEmailTemplates = new EmailTemplates();				
	$email_info = $objEmailTemplates->GetTemplate($email_template, $lang);
	$arr_constants = array();
	$arr_constants_all = array(
		'{FIRST NAME}', '{LAST NAME}', '{USER NAME}', '{USER PASSWORD}', '{USER EMAIL}', '{SUBJECT}',
		'{REGISTRATION CODE}', '{BASE URL}', '{WEB SITE}', '{YEAR}', '{EVENT}', '{MESSAGE FOOTER}'
	);
	$arr_values  = array();

    if(!isset($replace_holders['{MESSAGE FOOTER}'])){
        $replace_holders['{MESSAGE FOOTER}'] = EmailTemplates::PrepareMessageFooter();
    }
	
    if(!isset($replace_holders['{SUBJECT}'])){
        $replace_holders['{SUBJECT}'] = $email_info['template_subject'];
    }
	
	foreach($replace_holders as $key => $val){
		$arr_constants[] = $key;
		$arr_values[] = $val;
	}
	// add the rest of holders
	foreach($arr_constants_all as $key){
		if(!in_array($key, $arr_constants)){
			$arr_constants[] = $key;
			$arr_values[] = '';
		}
	}
	
	$subject = str_ireplace($arr_constants, $arr_values, $email_info['template_subject']);
	if($cc_email == '' && $cc_subject != '') $subject = $cc_subject;

	if(file_exists('templates/'.Application::Get('template').'/lib/email.template.php')){
		$email_html = true;
		$content = include('templates/'.Application::Get('template').'/lib/email.template.php');
		$template_content = '<div style=direction:'.$lang_dir.'>'.nl2br($email_info['template_content']).'</div>';
		$body = str_ireplace('{TEMPLATE_EMAIL_CONTENT}', $template_content, $content);
		$body = str_ireplace($arr_constants, $arr_values, $body);
	}else{
		$body  = '<div style=direction:'.$lang_dir.'>';
		$body .= str_ireplace($arr_constants, $arr_values, $email_info['template_content']);
		$body .= '</div>';
	}	
	
	if($objSettings->GetParameter('mailer') == 'smtp'){
		$mail = PHPMailer::Instance();
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->SMTPDebug  = 0;          // enables SMTP debug information (for testing)
										// 1 = errors and messages
										// 2 = messages only
		$mail->SMTPAuth   = true;       // enable SMTP authentication
		$mail->SMTPSecure = (in_array($objSettings->GetParameter('smtp_secure'), array('ssl', 'tls'))) ? $objSettings->GetParameter('smtp_secure') : '';
		$mail->Host       = $objSettings->GetParameter('smtp_host');  
		$mail->Port       = $objSettings->GetParameter('smtp_port');  
		$mail->Username   = $objSettings->GetParameter('smtp_username'); 
		$mail->Password   = $objSettings->GetParameter('smtp_password'); 
		
		$mail->setLanguage($lang);		// Set language

		$mail->ClearAddresses();        // clear previously added 'To' addresses
		$mail->ClearReplyTos();         // clear previously added 'ReplyTo' addresses
		$mail->SetFrom($sender);        // $mail->SetFrom($mail_from, 'First Last');
		$mail->AddReplyTo($sender);     // $mail->AddReplyTo($mail_to, 'First Last');
		
		$recipients = explode(',', $recipient);
		foreach($recipients as $key){
			$mail->AddAddress($key);    // $mail->AddAddress($mail_to, 'John Doe'); 	
		}

		$mail->Subject    = $subject;
		$mail->AltBody    = strip_tags($body);
		if($email_html){
			$mail->MsgHTML($body);
		}else{
			$mail->MsgHTML(nl2br($body));
		}

		$result = $mail->Send();		

		$status = ($result ? '1' : '0');
		$status_description = $mail->ErrorInfo;

		if($cc_email != ''){
			$mail->ClearAddresses();       // clear previously added 'To' addresses
			$mail->ClearReplyTos();        // clear previously added 'ReplyTo' addresses
			$mail->AddAddress($cc_email);  // $mail->AddAddress($mail_to, 'John Doe');
			$mail->Subject = (($cc_subject != '') ? $cc_subject : $subject);
			$result = $mail->Send();		
		}
	}else{
		$text_version = strip_tags($body);
		if($email_html){
			$html_version = $body;
		}else{
			$html_version = nl2br($body);
		}
	
		$objEmail = new Email($recipient, $sender, $subject); 				
		$objEmail->textOnly = false;
		$objEmail->content = $html_version;	
		$result = $objEmail->Send();
		
		$status = ($result ? '1' : '0');
		$status_description = '';
		if(version_compare(PHP_VERSION, '5.2.0', '>=')){
			$err = error_get_last();
			$status_description = (isset($err['message']) ? $err['message'] : '');
		}		

		if($cc_email != ''){
			if($cc_subject != '') $subject = $cc_subject;
			$objEmail = new Email($cc_email, $sender, $subject); 				
			$objEmail->textOnly = false;
			$objEmail->content = $html_version;	
			$result = $objEmail->Send();		
		}		
	}
	
	if($debug){
		echo 'To: '.$recipient.' <br>From: '.$sender.' <br>Subject: '.$subject.' <br>'.$body;
		if($cc_email != ''){
			echo '<br>--------<br>To: '.$cc_email.' <br>From: '.$sender.' <br>';
		}
		exit;
	}

	if(SAVE_MAIL_LOG == 'all'){
		MailLogs::MailLogAddRecord($objLogin->GetLoggedID(), $recipient, $email_template, $subject, $body, '1', '1', $status, $status_description);
	}

	return $result;
}

/**
 * Send email
 * @param $recipient
 * @param $sender
 * @param $title
 * @param $body
 * @param $lang
 * @param $debug
 */
function send_email_wo_template($recipient, $sender, $subject, $body, $lang = '', $debug = false)
{
	global $objSettings, $objLogin;

	// Prevent sendign emails on DEMO mode
	if(SITE_MODE == 'demo'){
		return false;
	}

	$email_html = true;
	
	if($lang == ''){
		$lang = Application::Get('lang');
		$lang_dir = Application::Get('lang_dir');
	}else{
		$lang_dir = Languages::Get($lang, 'lang_dir');
	}

	$text  = '<div style="direction:'.$lang_dir.'">';
	$text .= $body;
	$text .= '</div>';			

	if(file_exists('templates/'.Application::Get('template').'/lib/email.template.php')){
		$email_html = true;
		$content = include('templates/'.Application::Get('template').'/lib/email.template.php');
		$text = str_ireplace('{TEMPLATE_EMAIL_CONTENT}', nl2br($text), $content);
	}	

	if($objSettings->GetParameter('mailer') == 'smtp'){
		$mail = PHPMailer::Instance();
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->SMTPDebug  = 0;          // enables SMTP debug information (for testing)
										// 1 = errors and messages
										// 2 = messages only
		$mail->SMTPAuth   = true;       // enable SMTP authentication
		// sets the prefix to the server
		$mail->SMTPSecure = (in_array($objSettings->GetParameter('smtp_secure'), array('ssl', 'tls'))) ? $objSettings->GetParameter('smtp_secure') : '';
		$mail->Host       = $objSettings->GetParameter('smtp_host');  
		$mail->Port       = $objSettings->GetParameter('smtp_port');  
		$mail->Username   = $objSettings->GetParameter('smtp_username'); 
		$mail->Password   = $objSettings->GetParameter('smtp_password'); 
		
		$mail->ClearAddresses();       // clear previously added 'To' addresses
		$mail->ClearReplyTos();        // clear previously added 'ReplyTo' addresses
		$mail->SetFrom($sender);       // $mail->SetFrom($mail_from, 'First Last');
		$mail->AddReplyTo($sender);    // $mail->AddReplyTo($mail_to, 'First Last');
		$mail->AddAddress($recipient); // $mail->AddAddress($mail_to, 'John Doe'); 

		$mail->Subject    = $subject;
		$mail->AltBody    = strip_tags($body);

		if($email_html){
			$mail->MsgHTML($text);
		}else{
			$mail->MsgHTML(nl2br($text));
		}
		$result = $mail->Send();
		
		$status = ($result ? '1' : '0');
		$status_description = $mail->ErrorInfo;
	}else{
		$text_version = strip_tags($text);
		if($email_html){
			$html_version = $text;
		}else{
			$html_version = nl2br($text);
		}
	
		$objEmail = new Email($recipient, $sender, $subject); 				
		$objEmail->textOnly = false;
		$objEmail->content = $html_version;	
		$result = $objEmail->Send();
	
		$status = ($result ? '1' : '0');
		$status_description = '';
		if(version_compare(PHP_VERSION, '5.2.0', '>=')){
			$err = error_get_last();
			$status_description = (isset($err['message']) ? $err['message'] : '');
		}		
	}

	if($debug){
		echo $text;
		exit;
	}

	if(SAVE_MAIL_LOG == 'all'){
		MailLogs::MailLogAddRecord($objLogin->GetLoggedID(), $recipient, '', $subject, $body, '1', '1', $status, $status_description);
	}

	return $result;	
}

/**
 * Prepare pagination part
 **/
function pagination_get_links($total_pages, $url)
{
	$output = '';	
	$current_page = isset($_GET['p']) ? abs((int)$_GET['p']) : '1';
	if($total_pages > 1){
		$output .= '<div class="pagging">&nbsp;'._PAGES.': ';
		for($page_ind = 1; $page_ind <= $total_pages; $page_ind++){
			$output .= '<a class="pagging_link" href="'.$url.'&p='.$page_ind.'">'.(($page_ind == $current_page) ? '<b>'.$page_ind.'</b>' : $page_ind).'</a> ';
		}
		$output .= '</div>'; 
	}
	return $output;	
}

/**
 * Perform redirect to specific page
 * @param $page
 * @param $delay in milliseconds
 * @param $text
 */
function redirect_to($page = '', $delay = '', $text = '')
{
	if($page == '') return false;
	if($delay == ''){
		@header('location: '.(($page != '') ? $page : ''));
		echo '<script type="text/javascript">window.location.href="'.(($page != '') ? $page : '').'";</script>';
	}else{
		echo '<script type="text/javascript">setTimeout(\'window.location.href="'.(($page != '') ? $page : '').'"\', '.(int)$delay.');</script>';
	}
	echo $text;
	exit;		
}

/**
 * Draw debug of data on the screen
 * @param mixed $data
 * @param bool exit
 * @return void
 */
function dbug($data = array(), $exit = false)
{
	echo '<pre>';
	print_r($data);
	echo '</pre>';
	
	if($exit){
		exit;
	}
}

/**
 * Search in sub-array
 * @param $field
 * @param $value
 * @param $array
 * @return bool
 */
function in_sub_array($field = '', $value = '', $array = array()) {
	foreach($array as $key => $val){
		if(isset($val[$field]) && $val[$field] == $value){
			return true;
		}
	}
	
	return false;
}

/**
 * Checks if number if empty
 * @param $number
 * @return bool
 */
function is_empty_number($number = ''){
	return (empty($number) || $number == '0.0' || $number == '0.00' ) ? true : false;
}

/**
 * Revert text for RTL languages
 * @param string $str
 */
function utf8_strrev($str = ''){
	if(empty($str)) return '';
	
    preg_match_all('/./us', $str, $arr);
    return join('', array_reverse($arr[0]));
}

/**
 * Returns folder name
 */
function get_foolder(){
	$folder = '';
	$script_name = '';

	if(isset($_SERVER['SCRIPT_NAME'])){
		$script_name = $_SERVER['SCRIPT_NAME'];
	}elseif(isset($_SERVER['PHP_SELF'])){
		$script_name = $_SERVER['PHP_SELF'];
	}

	if(!empty($script_name)){
		$folder = substr($script_name, 0, strrpos($script_name, '/')+1);
	}

	return $folder;
}

/**
 * Returns language constant value
 * @param string $key
 */
function lang($key){
	return (defined($key) ? constant($key) : ucwords(strtolower(str_replace('_', ' ', $key))));
}

/**
 * Collect debug info 
 */
function profiler_start()
{
	if(SITE_MODE != 'development' || Application::Get('template') == 'mobile'){
		return false;
	}

	global $PROFILER;
	
	$PROFILER['start_time']	= get_formatted_microtime();
}

/**
 * Display debug info on the screen
 */
function profiler_finish()
{
	if(SITE_MODE != 'development' || Application::Get('template') == 'mobile'){
		return false;
	}
	
	global $PROFILER;
	
	$PROFILER['end_time'] = get_formatted_microtime();
	$end_memory_usage = memory_get_usage();
	
	$nl = "\n";
	
	// Debug bar status
	$debug_bar_state = isset($_COOKIE['debugBarState']) ? $_COOKIE['debugBarState'] : 'min';
	$onDblClick = 'appTabsMinimize()';

	$panelAlign = 'right';
	$panelTextAlign = 'left';
	
	$arr_general = array();
	$arr_params = (isset($_GET) ? $_GET : array()) + (isset($_POST) ? $_POST : array());
	$arr_warnings = !empty($PROFILER['warnings']) ? $PROFILER['warnings'] : array();	
	$arr_errors = !empty($PROFILER['errors']) ? $PROFILER['errors'] : array();	
	$arr_queries = !empty($PROFILER['queries']) ? $PROFILER['queries'] : array();
	
	$total_params = count($arr_params);
	$total_warnings = count($arr_warnings);
	$total_errors = count($arr_errors);
	$total_queries = count($arr_queries);

	echo $nl.'<style type="text/css">
		#debug-panel {opacity:0.9;position:fixed;bottom:0;left:0;z-index:2000;width:100%;max-height:90%;font:12px tahoma, verdana, sans-serif;color:#000;}
		#debug-panel fieldset {padding:0px 10px;background-color:#fff;border:1px solid #ccc;width:98%;margin:0px auto 0px auto;text-align:'.$panelTextAlign.';}
		#debug-panel fieldset legend {float:'.$panelAlign.';background-color:#f9f9f9;padding:5px 5px 4px 5px;border:1px solid #ccc;border-left:1px solid #ddd;border-bottom:1px solid #f4f4f4;margin:-15px 0 0 10px;font:12px tahoma, verdana, sans-serif;width:auto;}
		#debug-panel fieldset legend ul {color:#999;font-weight:normal;margin:0px;padding:0px;}
		#debug-panel fieldset legend ul li{float:left;width:auto;list-style-type:none;}
		#debug-panel fieldset legend ul li.title{width:50px;padding:0 2px;}
		#debug-panel fieldset legend ul li.narrow{width:auto;padding:0 2px;}
		#debug-panel fieldset legend ul li.item{width:auto;padding:0 12px;border-right:1px solid #999;}
		#debug-panel fieldset legend ul li.item:last-child{padding:0 0 0 12px;border-right:0px;}
		#debug-panel a {text-decoration:none;text-transform:none;color:#bbb;font-weight:normal;}
		#debug-panel a.debugArrow {color:#222;}
		#debug-panel pre {border:0px;}
		#debug-panel strong {font-weight:bold;}
		#debug-panel .tab-orange { color:#d15600 !important; }
		#debug-panel .tab-red { color:#cc0000 !important; }
		@media (max-width: 680px) {
			#debug-panel fieldset legend ul li.item a {display:block;visibility:hidden;}				
			#debug-panel fieldset legend ul li.item a:first-letter {visibility:visible !important;}
			#debug-panel fieldset legend ul li.item {width:30px; height:15px; margin-bottom:3px;)
		}
	</style>
	<script type="text/javascript">
		var arrDebugTabs = ["General","Params","Warnings","Errors","Queries"];
		var debugTabsHeight = "200px";
		var cssText = keyTab = "";
		function appSetCookie(state, tab){ document.cookie = "debugBarState="+state+"; path=/"; if(tab !== null) document.cookie = "debugBarTab="+tab+"; path=/"; }
		function appGetCookie(name){ if(document.cookie.length > 0){ start_c = document.cookie.indexOf(name + "="); if(start_c != -1){ start_c += (name.length + 1); end_c = document.cookie.indexOf(";", start_c); if(end_c == -1) end_c = document.cookie.length; return unescape(document.cookie.substring(start_c,end_c)); }} return ""; }
		function appTabsMiddle(){ appExpandTabs("middle", appGetCookie("debugBarTab")); }
		function appTabsMaximize(){ appExpandTabs("max", appGetCookie("debugBarTab")); }
		function appTabsMinimize(){ appExpandTabs("min", "General"); }			
		function appExpandTabs(act, key){ 
			if(act == "max"){ debugTabsHeight = "500px"; }
			else if(act == "middle"){ debugTabsHeight = "200px"; }
			else if(act == "min"){ debugTabsHeight = "0px";	}
			else if(act == "auto"){ 
				if(debugTabsHeight == "0px"){ debugTabsHeight = "200px"; act = "middle"; }
				else if(debugTabsHeight == "200px"){ act = "middle"; }
				else if(debugTabsHeight == "500px"){ act = "max"; }
			}
			keyTab = (key == null) ? "General" : key;
			document.getElementById("debugArrowExpand").style.display = ((act == "max") ? "none" : (act == "middle") ? "none" : "");
			document.getElementById("debugArrowCollapse").style.display = ((act == "max") ? "" : (act == "middle") ? "" : "none");
			document.getElementById("debugArrowMaximize").style.display = ((act == "max") ? "none" : (act == "middle") ? "" : "");
			document.getElementById("debugArrowMinimize").style.display = ((act == "max") ? "" : (act == "middle") ? "none" : "none");
			for(var i = 0; i < arrDebugTabs.length; i++){
				if(act == "min" || arrDebugTabs[i] != keyTab){
					document.getElementById("content"+arrDebugTabs[i]).style.display = "none";
					document.getElementById("tab"+arrDebugTabs[i]).style.cssText = "color:#bbb;";
				}
			}
			if(act != "min"){
				document.getElementById("content"+keyTab).style.display = "";
				document.getElementById("content"+keyTab).style.cssText = "width:100%;height:"+debugTabsHeight+";overflow-y:auto;";
				if(document.getElementById("tab"+keyTab).className == "tab-orange"){
					cssText = "color:#b13600 !important;";
				}
				else if(document.getElementById("tab"+keyTab).className == "tab-red"){
					cssText = "color:#aa0000 !important;";
				}
				else{
					cssText = "color:#222;";	
				}
				document.getElementById("tab"+keyTab).style.cssText = cssText;
			}
			document.getElementById("debug-panel").style.opacity = (act == "min") ? "0.9" : "1";
			appSetCookie(act, key);
		}
	</script>
	
	<div id="debug-panel">
	<fieldset>
	<legend id="debug-panel-legend">
		<ul>
			<li class="title"><b style="color:#222">Debug</b>:&nbsp;</li>
			<li class="narrow"><a id="debugArrowExpand" class="debugArrow" style="display:;" href="javascript:void(0)" title="Expand" onclick="javascript:appTabsMiddle()">&#9650;</a></li>
			<li class="narrow"><a id="debugArrowCollapse" class="debugArrow" style="display:none;" href="javascript:void(0)" title="Collapse" onclick="javascript:appTabsMinimize()">&#9660;</a></li>
			<li class="narrow"><a id="debugArrowMaximize" class="debugArrow" style="display:;" href="javascript:void(0)" title="Maximize" onclick="javascript:appTabsMaximize()">&#9744;</a></li>
			<li class="narrow"><a id="debugArrowMinimize" class="debugArrow" style="display:none;" href="javascript:void(0)" title="Minimize" onclick="javascript:appTabsMiddle()">&#9635;</a></li>
			<li class="item"><a id="tabGeneral" href="javascript:void(\'General\')" onclick="javascript:appExpandTabs(\'auto\', \'General\')" ondblclick="javascript:'.$onDblClick.'">General</a></li>
			<li class="item"><a id="tabParams" href="javascript:void(\'Params\')" onclick="javascript:appExpandTabs(\'auto\', \'Params\')" ondblclick="javascript:'.$onDblClick.'">Params ('.$total_params.')</a></li>
			<li class="item"><a id="tabWarnings" href="javascript:void(\'Warnings\')" '.($total_warnings ? 'class="tab-orange"' : '').' onclick="javascript:appExpandTabs(\'auto\', \'Warnings\')" ondblclick="javascript:'.$onDblClick.'">Warnings ('.$total_warnings.')</a></li>
			<li class="item"><a id="tabErrors" href="javascript:void(\'Errors\')" '.($total_errors ? 'class="tab-red"' : '').' onclick="javascript:appExpandTabs(\'auto\', \'Errors\')" ondblclick="javascript:'.$onDblClick.'">Errors ('.$total_errors.')</a></li>
			<li class="item"><a id="tabQueries" href="javascript:void(\'Queries\')" onclick="javascript:appExpandTabs(\'auto\', \'Queries\')" ondblclick="javascript:'.$onDblClick.'">SQL Queries ('.$total_queries.')</a></li>
		</ul>
	</legend>
	
	<div id="contentGeneral" style="display:none;padding:10px;width:100%;height:200px;overflow-y:auto;">
		Script name: '.PROJECT_NAME.'<br>
		Script version: '.CURRENT_VERSION.'<br>
		PHP version: '.phpversion().'<br><br>';
		
		$total_running_time = round((float)$PROFILER['end_time'] - (float)$PROFILER['start_time'], 5);
		$total_running_time_sql = !empty($PROFILER['sql_total_time']) ? $PROFILER['sql_total_time'] : 0;
		$total_running_time_script = round($total_running_time - $total_running_time_sql, 5);
		//$totalMemoryUsage = CConvert::fileSize((float)$end_memory_usage - (float)self::$_startMemoryUsage);
		//$htmlCompressionRate = !empty(self::$_arrData['html-compression-rate']) ? self::$_arrData['html-compression-rate'] : Unknown;
		
		echo 'Total running time: '.$total_running_time.' sec.<br>';
		echo 'Script running time: '.$total_running_time_script.' sec.<br>';
		echo 'SQL running time: '.$total_running_time_sql.' sec.<br>';
		//echo 'Total memory usage: '.$totalMemoryUsage.'<br>';
		echo '<br>';
		
		$included_files = get_included_files();
		
		foreach($included_files as $included_file){
			if(preg_match('/templates\\\\'.Application::Get('template').'/i', $included_file)){
				$arr_general['template_files'][] = $included_file;
			}elseif(preg_match('/include\\\\classes/i', $included_file)){
				$arr_general['classes'][] = $included_file;
			}elseif(preg_match('/include\\\\/i', $included_file)){
				$arr_general['included_files'][] = $included_file;
			}
		}
		
		echo '<strong>LOADED CLASSES</strong>:';
		echo '<pre>';
		sort($arr_general['classes']);
		print_r($arr_general['classes']);
		echo '</pre>';
		//echo '<br>';
		echo '<strong>INCLUDED FILES</strong>:';
		echo '<pre>';
		sort($arr_general['included_files']);
		print_r($arr_general['included_files']);
		echo '</pre>';
		//echo '<br>';
		echo '<strong>TEMPLATE FILES</strong>:';
		echo '<pre>';
		sort($arr_general['template_files']);
		print_r($arr_general['template_files']);
		echo '</pre>';
		echo '<br>';
	echo '</div>

	<div id="contentParams" style="display:none;padding:10px;width:100%;height:200px;overflow-y:auto;">';
		
		echo '<strong>$_GET</strong>:';
		echo '<pre style="white-space:pre-wrap;">';
		$arrGet = array();
		if(isset($_GET)){
			foreach($_GET as $key => $val){
				$arrGet[$key] = is_array($val) ? $val : strip_tags($val);
			}
		}
		print_r($arrGet);
		echo '</pre>';
		//echo '<br>';
		
		echo '<strong>$_POST</strong>:';
		echo '<pre style="white-space:pre-wrap;">';
		$arrPost = array();
		if(isset($_POST)){
			foreach($_POST as $key => $val){
				$arrPost[$key] = is_array($val) ? $val : strip_tags($val);
			}
		}
		print_r($arrPost);
		echo '</pre>';
		//echo '<br>';

		echo '<strong>$_FILES</strong>:';
		echo '<pre style="white-space:pre-wrap;">';
		$arrFiles = array();
		if(isset($_FILES)){
			foreach($_FILES as $key => $val){
				$arrFiles[$key] = is_array($val) ? $val : strip_tags($val);
			}
		}
		print_r($arrFiles);
		echo '</pre>';
		//echo '<br>';
		
		echo '<strong>$_COOKIE</strong>:';
		echo '<pre style="white-space:pre-wrap;">';
		$arrCookie = array();
		if(isset($_COOKIE)){
			foreach($_COOKIE as $key => $val){
				$arrCookie[$key] = is_array($val) ? $val : strip_tags($val);
			}
		}
		print_r($arrCookie);
		echo '</pre>';
		//echo '<br>';
		
		echo '<strong>$_SESSION</strong>:';
		echo '<pre style="white-space:pre-wrap;">';
		$arrSession = array();
		if(isset($_SESSION)){
			foreach($_SESSION as $key => $val){
				$arrSession[$key] = is_array($val) ? $val : strip_tags($val);
			}
		}
		print_r($arrSession);
		echo '</pre>';
		echo '<br>';
		
		//echo '<strong>CONSTANTS</strong>:';
		//echo '<pre style="white-space:pre-wrap;">';
		//$arrConstants = @get_defined_constants(true);
		//$arrUserConstants = isset($arrConstants['user']) ? $arrConstants['user'] : array();
		//print_r($arrUserConstants);
		//echo '</pre>';
		//echo '<br>';
	
	echo '</div>

	<div id="contentWarnings" style="display:none;padding:10px;width:100%;height:200px;overflow-y:auto;">';
		if($total_warnings > 0){
			$count = 0;
			foreach($arr_warnings as $msg){				
				echo '<pre style="white-space:normal;word-wrap:break-word;">';
				echo ++$count.'. ';
				print_r($msg);
				echo '</pre>';
				//echo '<br>';
			}               
		}
	echo '</div>

	<div id="contentErrors" style="display:none;padding:10px;width:100%;height:200px;overflow-y:auto;">';
		if($total_errors > 0){
			$count = 0;
			foreach($arr_errors as $msg){
				echo '<pre style="white-space:normal;word-wrap:break-word;">';
				echo ++$count.'. ';
				print_r($msg);
				echo '</pre>';
				//echo '<br>';
			}               
		}
	echo '</div>

	<div id="contentQueries" style="display:none;padding:10px;width:100%;height:200px;overflow-y:auto;">';
		if(!empty($arr_queries)){
			echo 'SQL running time: '.$total_running_time_sql.' sec.<br><br>';							
			foreach($arr_queries as $msgKey => $msgVal){
				echo ($msgKey+1).'. ';
				echo $msgVal.'<br><br>';
			}               
		}
	echo '</div>

	</fieldset>
	</div>';
	
	if($debug_bar_state == 'max'){
		echo '<script type="text/javascript">appTabsMaximize();</script>';
	}elseif($debug_bar_state == 'middle'){
		echo '<script type="text/javascript">appTabsMiddle();</script>';
	}else{
		echo '<script type="text/javascript">appTabsMinimize();</script>';
	}
}

/**
 * Get formatted microtime
 * @return float
 */
function get_formatted_microtime()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * Prepare backtrace message
 * @param array $trace_data
 * @return string
 */
function prepare_backtrace($trace_data = '')
{
	$stack = '';
	$i = 0;		
	
	// Prepare trace data
	if(empty($trace_data)){
		$trace = debug_backtrace();
		// Remove call to this function from stack trace
		unset($trace[0]);
	}else{
		$trace = $trace_data;	
	}

	foreach($trace as $node){
		$file = isset($node['file']) ? $node['file'] : '';
		$line = isset($node['line']) ? '('.$node['line'].') ' : '';
		$stack .= '#'.(++$i).' '.$file.$line.': '; 
		if(isset($node['class'])){
			$stack .= $node['class'].'->'; 
		}
		$stack .= $node['function'].'()'.PHP_EOL;
	}
	
	return $stack;
}

/**
 * Debug backtrace
 * @param string $message
 * @param array $trace_data
 * @param string $errno
 * @param bool $formatted
 * @return HTML
 */
function backtrace($message = '', $errno = 0, $trace_data = '', $formatted = true)
{
	switch($errno){
		case E_NOTICE:
		case E_USER_NOTICE:
			$error_type = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error_type = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error_type = "Fatal Error";
			break;
		default:
			$error_type = "Unknown Error";
			break;
	}

	if(SITE_MODE == 'development'){		
		$stack = prepare_backtrace($trace_data);
	}else{
		$message = 'A fatal exception has occurred. Program will exit.';
		$stack = 'Backtrace information is available in debug mode';
	}
	
	if($formatted){
		$return = '<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="utf-8">
			<title>Error / '.$error_type.'</title>
			<style type="text/css">
				::selection { background-color: #E13300; color: white; }
				::-moz-selection { background-color: #E13300; color: white; }
				body { background-color: #fff; margin: 40px; font: 13px/20px normal Helvetica, Arial, sans-serif; color: #4F5155;}
				a {	color: #003399;	background-color: transparent; font-weight: normal;}
				h1 { color: #444; background-color: transparent; border-bottom: 1px solid #D0D0D0; font-size: 19px; font-weight: normal; margin: 0 0 14px 0; padding: 14px 15px 10px 15px;}
				code { font-family: Consolas, Monaco, Courier New, Courier, monospace; font-size: 12px; background-color: #f9f9f9; border: 1px solid #D0D0D0; color: #002166; display: block; margin: 14px 0 14px 0; padding: 12px 10px 12px 10px;}
				#container { margin: 10px; border: 1px solid #D0D0D0; box-shadow: 0 0 8px #D0D0D0; }
				#container-content { padding:10px 20px; }
				p {	margin: 12px 15px 12px 15px; }
				pre { margin: 0px 15px; white-space: pre-wrap; word-wrap: break-word; }
			</style>
		</head>
		<body>
			<div id="container">
				<h1>An Error Was Encountered - '.$error_type.'</h1>
				<div id="container-content">
					<p>
						Exception caught:<br>
						'.$message.'
					</p>
					<p>
						Backtrace:<br>
						<pre>'.$stack.'</pre>
					</p>
				</div>
			</div>
		</body>
		</html>';
	}else{
		$return = $stack;
	}
	
	return $return;
}
