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

$new_news_id = News::GetNewsId(Application::Get('news_id'), Application::Get('lang'));
if($new_news_id != '' && Application::Get('news_id') != $new_news_id){
    
    $url = get_page_url(false);

    if($objSettings->GetParameter('seo_urls') == '1'){
        $url = str_replace('/'.Application::Get('news_id').'/', '/'.$new_news_id.'/', $url);						
    }else{
        $url = str_replace('nid='.Application::Get('news_id'), 'nid='.$new_news_id, $url);
    }

    redirect_to($url);
}

