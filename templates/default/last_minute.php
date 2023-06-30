<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.phps
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------


$last_minute = News::DrawLastMinuteNews(false);

if(!empty($last_minute)){
	echo '<div class="container">'.$last_minute.'</div>';
}



