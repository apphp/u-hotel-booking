<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

define('APPHP_EXEC', 'access allowed');
define('APPHP_CONNECT', 'direct');
require_once('../include/base.inc.php');
require_once('../include/connection.php');

$act                  = isset($_POST['act']) ? $_POST['act'] : '';
$hotel_id             = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
$customer_id          = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : '';
$rating_cleanliness   = isset($_POST['rating_cleanliness']) && is_numeric($_POST['rating_cleanliness']) ? prepare_input($_POST['rating_cleanliness'], true) : 0;
$rating_room_comfort  = isset($_POST['rating_room_comfort']) && is_numeric($_POST['rating_room_comfort']) ? prepare_input($_POST['rating_room_comfort'], true) : 0;
$rating_location      = isset($_POST['rating_location']) && is_numeric($_POST['rating_location']) ? prepare_input($_POST['rating_location'], true) : 0;
$rating_service       = isset($_POST['rating_service']) && is_numeric($_POST['rating_service']) ? prepare_input($_POST['rating_service'], true) : 0;
$rating_sleep_quality = isset($_POST['rating_sleep_quality']) && is_numeric($_POST['rating_sleep_quality']) ? prepare_input($_POST['rating_sleep_quality'], true) : 0;
$rating_price         = isset($_POST['rating_price']) && is_numeric($_POST['rating_price']) ? prepare_input($_POST['rating_price'], true) : 0;
$evaluation           = isset($_POST['evaluation']) ? (int)$_POST['evaluation'] : 0;
$title		          = isset($_POST['title']) ? prepare_input($_POST['title'], true) : '';
$positive_comments    = isset($_POST['title']) ? prepare_input($_POST['positive_comments'], true) : '';
$negative_comments	  = isset($_POST['title']) ? prepare_input($_POST['negative_comments'], true) : '';
$token                = isset($_POST['token']) ? prepare_input($_POST['token'], true) : '';
$session_token        = isset($_SESSION[INSTALLATION_KEY]['token']) ? prepare_input($_SESSION[INSTALLATION_KEY]['token']) : '';
$arr                  = array();

echo '[';
echo implode(',', $arr);
echo ']';

return;

//if($act == 'send' && ($token == $session_token) && !empty($hotel_id) && !empty($customer_id)){
//
//	if(Reviews::CheckCustomerReview($hotel_id, $customer_id)){
//
//		$sql = 'INSERT INTO '.TABLE_REVIEWS.'(id, hotel_id, customer_id, title, positive_comments, negative_comments, rating_cleanliness, rating_room_comfort, rating_location, rating_service, rating_sleep_quality, rating_price, evaluation, image_file_1, image_file_1_thumb, image_file_2, image_file_2_thumb, image_file_3, image_file_3_thumb, date_created, is_active, priority_order) VALUES 
//			(NULL, '.$hotel_id.', '.$customer_id.', \''.$title.'\', \''.$positive_comments.'\', \''.$negative_comments.'\', '.$rating_cleanliness.', '.$rating_room_comfort.', '.$rating_location.', '.$rating_service.', '.$rating_sleep_quality.', '.$rating_price.', '.$evaluation.', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', NOW(), 1, 0)';
//		database_void_query($sql);
//		
//		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');   // Date in the past
//		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
//		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
//		header('Pragma: no-cache'); // HTTP/1.0
//		header('Content-Type: application/json');
//		
//		$sql = 'SELECT
//					r.*,
//					cnt.name as country_name,
//                    hd.name as hotel_name
//				FROM '.TABLE_REVIEWS.' r
//					LEFT OUTER JOIN '.TABLE_COUNTRIES.' cnt ON r.author_country = cnt.abbrv AND cnt.is_active = 1
//                    LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
//				WHERE r.is_active = 1
//                    '.($hotel_id != 0 ? ' AND r.hotel_id = '.(int)$hotel_id : '').'
//				ORDER BY r.priority_order ASC';
//	//    $arr[] = '{"sql":"'.htmlentities($sql).'"}';
//		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
//		if($result[1] > 0){
//		}
//
//		echo '[';
//		echo implode(',', $arr);
//		echo ']';
//	}else{
//		// Customer send review for this hotel
//	}
//}    
