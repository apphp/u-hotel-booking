<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// ADMIN FUNCTIONS
// Updated: 26.09.2012

/**
 * Get chart changer
 * @param $tabid
 * @param $chart_type
 * @param $year
 * @param $country_id
 * @param $hotel_id
 * @param $page
 */
function get_chart_changer($tabid, $chart_type, $year, $country_id = '-1', $hotel_id = '-1', $page = 'accounts_statistics')
{
	global $objLogin;
	
	$output = '<form action="'.APPHP_BASE.'index.php?admin='.$page.'" name="frmStatistics" method="post">
	    '.draw_hidden_field('tabid', $tabid, false).'
		'.draw_token_field(false).'		
		
		<div class="row" style="padding:4px 20px 4px 20px;background-color:#efefef;border:1px solid #ddd;min-width:490px;width:100%">
			<div class="pull-left">
				'._TYPE.': <select name="chart_type" class="mgrid_select">
					<option value="barchart" '.(($chart_type == 'barchart') ? ' selected="selected"' : '').'>Barchart</option>
					<option value="columnchart" '.(($chart_type == 'columnchart') ? ' selected="selected"' : '').'>ColumnChart</option>
					<option value="piechart" '.(($chart_type == 'piechart') ? ' selected="selected"' : '').'>PieChart</option>
					<option value="areachart" '.(($chart_type == 'areachart') ? ' selected="selected"' : '').'>AreaChart</option>
				</select>&nbsp;&nbsp;&nbsp;
			</div>
			
			<div class="pull-left">
			'._YEAR.': <select name="year" class="mgrid_select">';
			for($y = date('Y')-5; $y < date('Y')+5; $y++){
				$output .= '<option value="'.$y.'" '.(($year == $y) ? ' selected="selected"' : '').'>'.$y.'</option>';
			}
			$output .= '</select>&nbsp;&nbsp;&nbsp;';
			$output .= '</div>';			

			if($country_id != '-1'){
				$output .= '<div class="pull-left">';
				$output .= _COUNTRIES.': <select name="country_id" class="mgrid_select">';
				$output .= '<option value="0">'._ALL.'</option>';
				$total_countries = Countries::GetAllCountries();
				foreach($total_countries[0] as $key => $val){
					$output .= '<option value="'.$val['abbrv'].'" '.(($country_id == $val['abbrv']) ? ' selected="selected"' : '').'>'.$val['name'].'</option>';
				}
				$output .= '</select>&nbsp;&nbsp;&nbsp;';
				$output .= '</div>';
			}
			
			if($hotel_id != '-1'){
				$hotels_list = ($objLogin->IsLoggedInAs('hotelowner')) ? implode(',', $objLogin->AssignedToHotels()) : '';
				$total_hotels = Hotels::GetAllActive((!empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : ''));

				$output .= '<div class="pull-left">';
				$output .= (FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS).': <select name="hotel_id" class="mgrid_select chosen_select">';
				$output .= '<option value="0">'._ALL.'</option>';
				foreach($total_hotels[0] as $key => $val){
					$output .= '<option value="'.$val['id'].'" '.(($hotel_id == $val['id']) ? ' selected="selected"' : '').'>'.$val['name'].'</option>';
				}				
				$output .= '</select>&nbsp;&nbsp;&nbsp;';
				$output .= '</div>';
			}

			$output .= '
			
			<div class="pull-left">
				<input type="button" class="form_button" onclick="frmStatistics_Submit();" value="'._SUBMIT.'" />
			</div>
		</div>
		</form>';	
	return $output;
}

/**
 * Draws set values for statistics
 * @param $result
 * @param $chart_type
 * @param $chart_name
 */
function draw_set_values($result, $chart_type, $chart_name, $pre_addition = '')
{
	$nl = "\n";
	$res_month1 = (isset($result['month1']) && $result['month1'] != '') ? str_replace(',', '', $result['month1']) : '0';
	$res_month2 = (isset($result['month2']) && $result['month2'] != '') ? str_replace(',', '', $result['month2']) : '0';
	$res_month3 = (isset($result['month3']) && $result['month3'] != '') ? str_replace(',', '', $result['month3']) : '0';
	$res_month4 = (isset($result['month4']) && $result['month4'] != '') ? str_replace(',', '', $result['month4']) : '0';
	$res_month5 = (isset($result['month5']) && $result['month5'] != '') ? str_replace(',', '', $result['month5']) : '0';
	$res_month6 = (isset($result['month6']) && $result['month6'] != '') ? str_replace(',', '', $result['month6']) : '0';
	$res_month7 = (isset($result['month7']) && $result['month7'] != '') ? str_replace(',', '', $result['month7']) : '0';
	$res_month8 = (isset($result['month8']) && $result['month8'] != '') ? str_replace(',', '', $result['month8']) : '0';
	$res_month9 = (isset($result['month9']) && $result['month9'] != '') ? str_replace(',', '', $result['month9']) : '0';
	$res_month10 = (isset($result['month10']) && $result['month10'] != '') ? str_replace(',', '', $result['month10']) : '0';
	$res_month11 = (isset($result['month11']) && $result['month11'] != '') ? str_replace(',', '', $result['month11']) : '0';
	$res_month12 = (isset($result['month12']) && $result['month12'] != '') ? str_replace(',', '', $result['month12']) : '0';	

	$output  = $nl.' data.setValue(0, 0, "'._JANUARY.' ('.$pre_addition.$res_month1.')");';
	$output .= $nl.' data.setValue(0, 1, '.$res_month1.');';
	$output .= $nl.' data.setValue(1, 0, "'._FEBRUARY.' ('.$pre_addition.$res_month2.')");';
	$output .= $nl.' data.setValue(1, 1, '.$res_month2.');';
	$output .= $nl.' data.setValue(2, 0, "'._MARCH.' ('.$pre_addition.$res_month3.')");';
	$output .= $nl.' data.setValue(2, 1, '.$res_month3.');';
	$output .= $nl.' data.setValue(3, 0, "'._APRIL.' ('.$pre_addition.$res_month4.')");';
	$output .= $nl.' data.setValue(3, 1, '.$res_month4.');';
	$output .= $nl.' data.setValue(4, 0, "'._MAY.' ('.$pre_addition.$res_month5.')");';
	$output .= $nl.' data.setValue(4, 1, '.$res_month5.');';
	$output .= $nl.' data.setValue(5, 0, "'._JUNE.' ('.$pre_addition.$res_month6.')");';
	$output .= $nl.' data.setValue(5, 1, '.$res_month6.');';
	$output .= $nl.' data.setValue(6, 0, "'._JULY.' ('.$pre_addition.$res_month7.')");';
	$output .= $nl.' data.setValue(6, 1, '.$res_month7.');';
	$output .= $nl.' data.setValue(7, 0, "'._AUGUST.' ('.$pre_addition.$res_month8.')");';
	$output .= $nl.' data.setValue(7, 1, '.$res_month8.');';
	$output .= $nl.' data.setValue(8, 0, "'._SEPTEMBER.' ('.$pre_addition.$res_month9.')");';
	$output .= $nl.' data.setValue(8, 1, '.$res_month9.');';
	$output .= $nl.' data.setValue(9, 0, "'._OCTOBER.' ('.$pre_addition.$res_month10.')");';
	$output .= $nl.' data.setValue(9, 1, '.$res_month10.');';
	$output .= $nl.' data.setValue(10, 0, "'._NOVEMBER.' ('.$pre_addition.$res_month11.')");';
	$output .= $nl.' data.setValue(10, 1, '.$res_month11.');';
	$output .= $nl.' data.setValue(11, 0, "'._DECEMBER.' ('.$pre_addition.$res_month12.')");';
	$output .= $nl.' data.setValue(11, 1, '.$res_month12.');';
	
	// Create and draw the visualization
	if($chart_type == 'barchart'){
		$output .= 'new google.visualization.BarChart(document.getElementById("div_visualization")).draw(data, {is3D: true, min:0, title:"'.$chart_name.'"});'; 
	}elseif($chart_type == 'piechart'){
		$output .= 'new google.visualization.PieChart(document.getElementById("div_visualization")).draw(data, {is3D: true, min:0, title:"'.$chart_name.'"});';
	}elseif($chart_type == 'areachart'){
		$output .= 'new google.visualization.AreaChart(document.getElementById("div_visualization")).draw(data, {is3D: true, min:0, title:"'.$chart_name.'"});';
	}else{ // columnchart
		$output .= 'new google.visualization.ColumnChart(document.getElementById("div_visualization")).draw(data, {is3D: true, min:0, title:"'.$chart_name.'"});';
	}
	
	return $output;
}

/**
 * Draws set values for statistics (last changed 12.09.2011)
 */
function get_timezones_array()
{
	$arr_time_zones = array();
	$arr_time_zones['-12'] = '[UTC - 12] Baker Island Time';
	$arr_time_zones['-11'] = '[UTC - 11] Niue Time, Samoa Standard Time';
	$arr_time_zones['-10'] = '[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time';
	$arr_time_zones['-9.5'] = '[UTC - 9:30] Marquesas Islands Time';
	$arr_time_zones['-9'] = '[UTC - 9] Alaska Standard Time, Gambier Island Time';
	$arr_time_zones['-8'] = '[UTC - 8] Pacific Standard Time';
	$arr_time_zones['-7'] = '[UTC - 7] Mountain Standard Time';
	$arr_time_zones['-6'] = '[UTC - 6] Central Standard Time';
	$arr_time_zones['-5'] = '[UTC - 5] Eastern Standard Time';
	$arr_time_zones['-4.5'] = '[UTC - 4:30] Venezuelan Standard Time';
	$arr_time_zones['-4'] = '[UTC - 4] Atlantic Standard Time';
	$arr_time_zones['-3.5'] = '[UTC - 3:30] Newfoundland Standard Time';
	$arr_time_zones['-3'] = '[UTC - 3] Amazon Standard Time, Central Greenland Time';
	$arr_time_zones['-2'] = '[UTC - 2] Fernando de Noronha, S. Georgia &amp; the S. Sandwich Islands (Time)';
	$arr_time_zones['-1'] = '[UTC - 1] Azores Standard Time, Cape Verde Time, Eastern Greenland Time';
	$arr_time_zones['0'] = '[UTC] Western European Time, Greenwich Mean Time';
	$arr_time_zones['1'] = '[UTC + 1] Central European Time, West African Time';
	$arr_time_zones['2'] = '[UTC + 2] Eastern European Time, Central African Time';
	$arr_time_zones['3'] = '[UTC + 3] Moscow Standard Time, Eastern African Time';
	$arr_time_zones['3.5'] = '[UTC + 3:30] Iran Standard Time';
	$arr_time_zones['4'] = '[UTC + 4] Gulf Standard Time, Samara Standard Time';
	$arr_time_zones['4.5'] = '[UTC + 4:30] Afghanistan Time';
	$arr_time_zones['5'] = '[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time';		
	$arr_time_zones['5.5'] = '[UTC + 5:30] Indian Standard Time, Sri Lanka Time';
	$arr_time_zones['5.75'] = '[UTC + 5:45] Nepal Time';
	$arr_time_zones['6'] = '[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time';
	$arr_time_zones['6.5'] = '[UTC + 6:30] Cocos Islands Time, Myanmar Time';
	$arr_time_zones['7'] = '[UTC + 7] Indochina Time, Krasnoyarsk Standard Time';
	$arr_time_zones['8'] = '[UTC + 8] Chinese, Australian Western, Irkutsk (Standard Time)';
	$arr_time_zones['8.75'] = '[UTC + 8:45] Southeastern Western Australia Standard Time';
	$arr_time_zones['9'] = '[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time';
	$arr_time_zones['9.30'] = '[UTC + 9:30] Australian Central Standard Time';
	$arr_time_zones['10'] = '[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time';
	$arr_time_zones['10.5'] = '[UTC + 10:30] Lord Howe Standard Time';
	$arr_time_zones['11'] = '[UTC + 11] Solomon Island Time, Magadan Standard Time';
	$arr_time_zones['11.5'] = '[UTC + 11:30] Norfolk Island Time';
	$arr_time_zones['12'] = '[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time';
	$arr_time_zones['12.75'] = '[UTC + 12:45] Chatham Islands Time';
	$arr_time_zones['13'] = '[UTC + 13] Tonga Time, Phoenix Islands Time';
	$arr_time_zones['14'] = '[UTC + 14] Line Island Time';
	
	return $arr_time_zones;
}

/**
 * Resize uploaded image
 */
function resize_image($image_path, $image_name, $resize_width = '', $resize_height = '')
{
	$image_path_name = $image_path.$image_name;        
	if(empty($image_path_name)){ // No Image?    
		echo 'Uploaded file is not image'; 
	}elseif(!function_exists('imagecreatefromjpeg')){
		return $image_name;
	}else{ // An Image?
		if($image_path_name){
			$size   = getimagesize($image_path_name);
			$width  = $size[0];
			$height = $size[1];                
			$case = '';
			$curr_ext = strtolower(substr($image_path_name,strrpos($image_path_name,'.')+1));
			$imagetype = (function_exists('exif_imagetype')) ? exif_imagetype($image_path_name) : '';	
			if($imagetype == '1' && $curr_ext != 'gif') $ext = 'gif';
			else if($imagetype == '2' && $curr_ext != 'jpg' && $curr_ext != 'jpeg') $ext = 'jpg';
			else if($imagetype == '3' && $curr_ext != 'png') $ext = 'png';
			else $ext = $curr_ext;
			switch($ext){
				case 'png':
					$iTmp = @imagecreatefrompng($image_path_name);
					$case = 'png';
					break;
				case 'gif':
					$iTmp = @imagecreatefromgif($image_path_name);
					$case = 'gif';
					break;                
				case 'jpeg':            
				case 'jpg':
					$iTmp = @imagecreatefromjpeg($image_path_name);
					$case = 'jpg';
					break;                
			}
			$image_path_name_old = $image_path.$image_name;        
			$image_name = str_replace('.'.$curr_ext, '.jpg', strtolower($image_name));
			$image_path_name_new = $image_path.$image_name;        

			if($case != ''){
				if($resize_width != '' && $resize_height == ''){
					$new_width = $resize_width;
					$new_height = ($height/$width) * $new_width;                
				}elseif($resize_width == '' && $resize_height != ''){
					$new_height = $resize_height;
					$new_width=($width/$height)*$new_height;
				}elseif($resize_width != '' && $resize_height != ''){
					$new_width  = $resize_width;
					$new_height = $resize_height;                    
				}else{
					$new_width  = $width;  
					$new_height = $height;
				}
				$iOut = @imagecreatetruecolor(intval($new_width), intval($new_height));     
				@imagecopyresampled($iOut,$iTmp,0,0,0,0,intval($new_width), intval($new_height), $width, $height);
				@imagejpeg($iOut,$image_path_name_new,100);
				if($curr_ext != 'jpg' && $case != 'jpg') @unlink($image_path_name_old);
			}
		}            
	}
	return $image_name;
}

/**
 * Detects MIME Content-type for a file
 * Define our own function in case there is no such function by default
 */
if(!function_exists('mime_content_type'))
{
    function mime_content_type($filename){
        $mime_types = array(
			
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

		$ext = explode('.',$filename);
        $ext = strtolower(array_pop($ext)); 
		
        if (array_key_exists($ext, $mime_types)){
            return $mime_types[$ext];
        }elseif (function_exists('finfo_open')){
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }else{
            return 'application/octet-stream';
        }
    }
}

