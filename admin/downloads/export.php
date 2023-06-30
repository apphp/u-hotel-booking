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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','agencyowner')){
	
    $file = isset($_GET['file']) ? prepare_input($_GET['file']) : '';
	if($file == 'export.csv' || $file == 'invoice.pdf' || $file == 'car_invoice.pdf'){
		$file_path = 'tmp/export/'.$file;
		
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Pragma: no-cache'); // HTTP/1.0
		header('Content-type: application/force-download'); 
		header('Content-Disposition: inline; filename="'.$file.'"'); 
		header('Content-Transfer-Encoding: binary'); 
		header('Content-length: '.filesize($file_path)); 
		header('Content-Type: application/octet-stream'); 
		header('Content-Disposition: attachment; filename="'.$file.'"'); 
		readfile($file_path);
		
		exit(0);		
	}
}

