<?php
/**
 * Draws reservation bar
 * @param $current_tab
 */
function draw_widget_reservation_bar($current_tab = '', $is_draw = true, $links_allowed = true)
{	
	global $objLogin;
	
	$selected_rooms_link = false;
	$booking_details_link = false;
	$reservation_link = false;
	$payment_link = false;
	if($links_allowed){
		if($current_tab == 'booking_details'){
			$selected_rooms_link = true;
		}elseif($current_tab == 'reservation'){
			$booking_details_link = true;			
			$selected_rooms_link = true;
		}elseif($current_tab == 'payment'){
			$booking_details_link = false;			
			$selected_rooms_link = false;
			$reservation_link = true;
		}		
	}
	
	$output = '<table class="reservation_tabs" align="center" border="0">';
	$output .= '<tr>';
	$output .= ' <td class="'.(($current_tab == 'check_availability') ? 'reservation_tab_active' : 'reservation_tab').'">'.(($selected_rooms_link) ? '<a href="'.WIDGET_HOME_URL.'?page=check_availability&'.WIDGET_HASH.'">'._CHECK_AVAILABILITY.'</a>' : _CHECK_AVAILABILITY).'</td>';
	$output .= ' <td class="'.(($current_tab == 'booking_details') ? 'reservation_tab_active' : 'reservation_tab').'">'.(($booking_details_link) ? '<a href="'.WIDGET_HOME_URL.'?page=booking_details&m=edit&'.WIDGET_HASH.'">'._BOOKING_DETAILS.'</a>' : _BOOKING_DETAILS).'</td>';
	$output .= ' <td class="'.(($current_tab == 'reservation') ? 'reservation_tab_active' : 'reservation_tab').'">'._RESERVATION.'</td>';
	$output .= ' <td class="'.(($current_tab == 'payment') ? 'reservation_tab_active' : 'reservation_tab').'">'._PAYMENT.'</td>';
	$output .= '</tr>';
	$output .= '</table>';
	if(!$is_draw) return $output;
	else echo $output;		
}

/**
 * Checks if image
 * @param $number
 * @return bool
 */
function is_image($filename) {
	$is_image = @getimagesize($filename);
	
	if(!$is_image){
		return false;
	}elseif(!in_array($is_image[2], array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WBMP))){
		return false;
	}else{
		return true;
	}
}

/**
 * Check is exist url and type
 * @param string $url
 * @param string|array $type
 * @return bool
 */
function check_url_type($url, $type = 'text/html')
{
	$headers = @get_headers($url, 1);
	if(!empty($headers)){
		if(!empty($type)){
			$get_type = isset($headers['Content-Type']) ? $headers['Content-Type'] : '';
			if(
				!empty($get_type)
				&& (is_array($type) && in_array($get_type, $type))
				|| (is_string($type) && $type == $get_type)
			){
				return true;
			}
		}else{
			return true;
		}
	}
	return false;
}

/**
 * Validation Url
 * @param string $url
 * @param string|array $type
 * @return bool
 */
function validation_url($url)
{
	return filter_var($url, FILTER_VALIDATE_URL) === false ? false : true;
}

/**
 * Check is exist file
 * @param string $url
 * @param string $directory
 * @param string $type
 * @return bool
 */
function check_exist_file($file, $directory = '', $type = '')
{
	$path = strval(str_replace("\0", "", $directory.$file));
	if($type == 'image'){
		$result = is_image($path) ? true : false;
	}else{
		$result = is_file($path) ? true : false;
	}
	return $result;
}

/**
 * Improve Url
 * @param string $url
 * @return false|string
 */
function improve_url($url)
{
    $arr_url = parse_url($url);
    $ret = false;

    if(!isset($arr_url['scheme']) || !in_array($arr_url['scheme'], array("http", "https"))){
		$arr_url["scheme"] = "http";
	}

    if(!empty($arr_url['host'])){
        $ret = sprintf("%s://%s%s%s", $arr_url['scheme'], $arr_url['host'], (!empty($arr_url['port']) ? ':'.$arr_url['port'] : ''), $arr_url['path']);
	}elseif(preg_match("/^\w+\.[\w\.]+(\/.*)?$/", $arr_url["path"])){
        $ret = sprintf("%s://%s", $arr_url["scheme"], $arr_url["path"]);
	}

    if(!empty($ret) && !empty($arr_url['query'])){
		$ret .= sprintf("?%s", $arr_url['query']);
	}

    return $ret;
}

function check_and_return_valid_url($url, $type = '')
{
	$result = '';
	$arr_mime_type = array(
		'css' => 'text/css',
		'image' => array('image/jpg', 'image/jpeg', 'image/png', 'image/gif'),
		'html' => 'text/html'
	);

	$url = improve_url($url);
	if(!empty($url)){
		$mime_type = isset($arr_mime_type[$type]) ? $arr_mime_type[$type] : $arr_mime_type['html'];
		if(check_url_type($url, $mime_type)){
			$result = $url;
		}
	}

	return $result;
}

