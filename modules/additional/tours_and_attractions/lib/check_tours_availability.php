<?php
/**
 * @project uHotelBooking
 * @copyright (c) 2019 ApPHP
 * @author ApPHP <info@apphp.com>
 * @site http://www.hotel-booking-script.com
 * @license http://hotel-booking-script.com/license.php
 */

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------


if(Modules::IsModuleInstalled('tours_and_attractions') && ModulesSettings::Get('tours_and_attractions', 'is_active') == 'yes'){

    $currency_code = Application::Get('currency_code');
    $currency_rate = Application::Get('currency_rate');
    $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;

    $page      = isset($_POST['p']) ? $_POST['p'] : 1;
    $view_type = isset($_POST['view_type']) ? prepare_input($_POST['view_type']) : 'list';

    $tour_id       = isset($_POST['tour_id']) ? $_POST['tour_id'] : 0;
    $tour_location = isset($_POST['tour_location']) ? (int)$_POST['tour_location'] : 0;
    $tour_types    = isset($_POST['tour_types']) ? prepare_input($_POST['tour_types']) : [];
    $current_currency = isset($_POST['current_currency']) ? prepare_input($_POST['current_currency'], true) : Application::Get('currency_code');

    $price = isset($_POST['tour_price']) ? prepare_input($_POST['tour_price']) : 0;
    if(!empty($price)){
        $arr_price = explode(';',$price);
    }
    $min_price  = isset($arr_price[0]) && is_numeric($arr_price[0]) ? $arr_price[0] : 0;
    $max_price  = isset($arr_price[1]) && is_numeric($arr_price[1]) ? $arr_price[1] : 0;

    $price_sort = !empty($_POST['sort_price']) && in_array(strtolower($_POST['sort_price']), array('asc', 'desc')) ? strtolower($_POST['sort_price']) : '';

    // Dates
    $tour_from_date      = !empty($_POST['tour_from_date']) ? prepare_input($_POST['tour_from_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time());
    $tour_from_arr       = explode('/', $tour_from_date);
    $tour_from_month     = !empty($tour_from_arr[$american_format ? 0 : 1]) ? $tour_from_arr[$american_format ? 0 : 1] : '';
    $tour_from_day       = !empty($tour_from_arr[$american_format ? 1 : 0]) ? $tour_from_arr[$american_format ? 1 : 0] : '';
    $tour_from_year      = !empty($tour_from_arr[2]) ? $tour_from_arr[2] : '';
    $tour_from_date      = $tour_from_year.'-'.$tour_from_month.'-'.$tour_from_day;

    $tour_to_date      = !empty($_POST['tour_to_date']) ? prepare_input($_POST['tour_to_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + 86400);
    $tour_to_arr       = explode('/', $tour_to_date);
    $tour_to_month     = !empty($tour_to_arr[$american_format ? 0 : 1]) ? $tour_to_arr[$american_format ? 0 : 1] : '';
    $tour_to_day       = !empty($tour_to_arr[$american_format ? 1 : 0]) ? $tour_to_arr[$american_format ? 1 : 0] : '';
    $tour_to_year      = !empty($tour_to_arr[2]) ? $tour_to_arr[2] : '';
    $tour_to_date      = $tour_to_year.'-'.$tour_to_month.'-'.$tour_to_day;

    $unix_time_from    = strtotime($tour_from_year.'-'.$tour_from_month.'-'.$tour_from_day);
    $unix_time_to      = strtotime($tour_to_year.'-'.$tour_to_month.'-'.$tour_to_day);

    $current_date      = strtotime(date('Y-m-d'));


    draw_title_bar(_AVAILABLE_TOURS);

    if(empty($tour_from_date)){
        draw_important_message(_WRONG_EMPTY_FROM_DATE);
    }elseif(empty($tour_to_date)){
        draw_important_message(_WRONG_EMPTY_TO_DATE);
    }elseif(!checkdate($tour_from_month, $tour_from_day, $tour_from_year)){
        draw_important_message(_WRONG_CHECKOUT_DATE_ALERT);
    }elseif(!checkdate($tour_to_month, $tour_to_day, $tour_to_year)){
        draw_important_message(_WRONG_CHECKOUT_DATE_ALERT);
    }elseif(($unix_time_from < $current_date) || ($unix_time_to < $current_date) || ($unix_time_to < $unix_time_from)){
        draw_important_message(_PAST_TIME_ALERT);
    }else{
        draw_content_start();

        $lang = Application::Get('lang');
        $result = array();
        $diff_country = false;

        // Prepare locations array
        $total_tour_locations = ToursLocations::GetAllLocations();
        $location_names = [];
        foreach($total_tour_locations[0] as $key => $val){
            $location_names[$val['id']] = $val['name'];
        }

        if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
            draw_sub_title_bar((!empty($location_names[$tour_location]) ? _LOCATION.': <b>'.$location_names[$tour_location].'</b> ' : '')._FROM.': '.$tour_from_date.' '._TO.': '.$tour_to_date, true, 'h4');
        }else{
            draw_sub_title_bar((!empty($location_names[$tour_location]) ? _LOCATION.': <b>'.$location_names[$tour_location].'</b> ' : '')._FROM.': '.$tour_from_date.' '._TO.': '.$tour_to_date, true, 'h4');
        }

        if($currency_code != $current_currency){
            $current_currency_info = Currencies::GetCurrencyInfo($current_currency);
            if($current_currency_info['code'] != $currency_code){
                $min_price = ceil($min_price * ($currency_rate / $current_currency_info['rate']));
                $max_price = ceil($max_price * ($currency_rate / $current_currency_info['rate']));
            }
        }

        $objTours = new Tours();
        $params = array(
            'tour_id'        => $tour_id,
            'tour_location'  => $tour_location,
            'tour_from_date' => $tour_from_date,
            'tour_to_date'   => $tour_to_date,
            'min_price'      => $min_price,
            'max_price'      => $max_price,
            'tour_types'     => $tour_types,
            'sort_price'     => $price_sort,
            'view_type'     => $view_type,
        );

        $tours_count = $objTours->SearchFor($params);

        echo '<div class="line-hor"></div>';

        if($tours_count['tours'] > 0){
            //dbug($params);
            echo $search_result = $objTours->DrawSearchResult($params, $tours_count['tours']);
        }else{
            draw_important_message(_NO_TOURS_FOUND);
        }

        draw_content_end();
    }

}

