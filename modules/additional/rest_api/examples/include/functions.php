<?php
/**
 * REST API module for uHotelBooking script
 *
 * @project uHotelBooking
 * @copyright (c) 2019 ApPHP
 * @author ApPHP <info@apphp.com>
 * @site http://www.hotel-booking-script.com
 * @license http://hotel-booking-script.com/license.php
 */	  
	  
function call($method, $url, $data = false , &$dbug = '') {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($ch, CURLOPT_URL, $url);
	if ($data) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Content-Length: ' . strlen($data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	$dbug = curl_error($ch);
	// Close cURL resource, and free up system resources
	curl_close($ch);
	$result_decode = (array)json_decode($result);
	if(isset($result_decode['content']) && isset($result_decode['debug'])){
		$dbug = $result_decode['debug'];
		$result = json_encode($result_decode['content']);
	}
	return $result;
}


function humanize($string = ''){
	return ucwords(str_replace('_', ' ', $string));
}

function print_data($arr_data = array()){
	$ouput = '';
	if(!empty($arr_data) && is_array($arr_data)){
		$max_len = 0;
		$arr_keys = array_keys($arr_data);
		for($i = 0, $length = count($arr_keys); $i < $length; $i++){
			if(strlen($arr_keys[$i]) > $max_len){
				$max_len = strlen($arr_keys[$i]);
			}
		}
		foreach($arr_data as $key => $value){
			$repeat = $max_len - strlen($key);
			$ouput .= '<div>['.$key.'] '.str_repeat(' ', $repeat).'=> '.$value.'</div>';
		}
	}

	return $ouput;
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
 * Alias to draw dbug() function
 * @param mixed $data
 * @param bool exit
 * @return void
 */
function dd($data = array(), $exit = true)
{
    dbug($data, $exit);
}
