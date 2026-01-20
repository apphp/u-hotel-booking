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

//
//Array
//(
//    [p] => 1
//    [token] => 60fca378b577fbf52b01387a44e58e74
//)

if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){

    $page = isset($_POST['p']) ? $_POST['p'] : 1;
    $view_type       = isset($_POST['view_type']) ? prepare_input($_POST['view_type']) : 'list';
    $car_id          = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
    $picking_up_id   = !empty($_POST['car_picking_up']) && !empty($_POST['car_picking_up_id']) ?   (int)$_POST['car_picking_up_id'] : '';
    $dropping_off_id = !empty($_POST['car_dropping_off']) && !empty($_POST['car_dropping_off_id']) ? (int)$_POST['car_dropping_off_id'] : '';
    $current_currency = isset($_POST['current_currency']) ? prepare_input($_POST['current_currency'], true) : Application::Get('currency_code');
    $price           = isset($_POST['car_price']) ? prepare_input($_POST['car_price']) : 0;
    if(!empty($price)){
        $arr_price = explode(';',$price);
    }
    $min_price  = isset($arr_price[0]) && is_numeric($arr_price[0]) ? $arr_price[0] : 0;
    $max_price  = isset($arr_price[1]) && is_numeric($arr_price[1]) ? $arr_price[1] : 0;
    $make_sort  = !empty($_POST['sort_make']) && in_array(strtolower($_POST['sort_make']), array('asc', 'desc')) ? strtolower($_POST['sort_make']) : '';
    $price_sort = !empty($_POST['sort_price']) && in_array(strtolower($_POST['sort_price']), array('asc', 'desc')) ? strtolower($_POST['sort_price']) : '';
    $types      = isset($_POST['car_types']) && is_array($_POST['car_types']) ? prepare_input($_POST['car_types']) : array();
    if(!empty($types)){
        $all_types = array();
        foreach($types as $type){
            if((string)(int)$type == $type){
                $all_types[] = $type;
            }
        }
        $types = $all_types;
    }

    $american_format = $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? true : false;

    $pick_up_date      = !empty($_POST['car_pick_up_date']) ? prepare_input($_POST['car_pick_up_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + 3600);
    $pick_up_date_arr  = explode('/', $pick_up_date);
    $pick_up_month     = !empty($pick_up_date_arr[$american_format ? 0 : 1]) ? $pick_up_date_arr[$american_format ? 0 : 1] : '';
    $pick_up_day       = !empty($pick_up_date_arr[$american_format ? 1 : 0]) ? $pick_up_date_arr[$american_format ? 1 : 0] : '';
    $pick_up_year      = !empty($pick_up_date_arr[2]) ? $pick_up_date_arr[2] : '';
    $pick_up_time      = isset($_POST['car_pick_up_time']) && $_POST['car_pick_up_time'] !== '' ? prepare_input($_POST['car_pick_up_time']) : date('G', time() + 5400).(date('i') < '30' ? '.5' : '');

    $drop_off_date     = !empty($_POST['car_drop_off_date']) ? prepare_input($_POST['car_drop_off_date']) : date($american_format ? 'm/d/Y' : 'd/m/Y', time() + (24 * 60 * 60 + 3600));
    $drop_off_date_arr = explode('/', $drop_off_date);
    $drop_off_month    = !empty($drop_off_date_arr[$american_format ? 0 : 1]) ? $drop_off_date_arr[$american_format ? 0 : 1] : '';
    $drop_off_day      = !empty($drop_off_date_arr[$american_format ? 1 : 0]) ? $drop_off_date_arr[$american_format ? 1 : 0] : '';
    $drop_off_year     = !empty($drop_off_date_arr[2]) ? $drop_off_date_arr[2] : '';
    $drop_off_time     = isset($_POST['car_drop_off_time']) && $_POST['car_drop_off_time'] !== '' ? prepare_input($_POST['car_drop_off_time']) : date('G', time() + 5400).(date('i') < '30' ? '.5' : '');

    $unix_time_pick_up     = strtotime($pick_up_year.'-'.$pick_up_month.'-'.$pick_up_day.' ') + $pick_up_time * 60 * 60;
    $unix_time_drop_off    = strtotime($drop_off_year.'-'.$drop_off_month.'-'.$drop_off_day.' ') + $drop_off_time * 60 * 60;

    $diff_time = $unix_time_drop_off - $unix_time_pick_up;
    $hours_to_seconds = $diff_time % (24 * 60 * 60);
    $days = ($diff_time - $hours_to_seconds) / (24 * 60 * 60);
    $hours = $hours_to_seconds / 3600;

	draw_title_bar(_AVAILABLE_CARS);

	// Check if there is a page
	if(empty($picking_up_id) && empty($dropping_off_id)){
		if(SITE_MODE == 'demo'){
			draw_message('To perform a test search enter "Boston" both as Picking up and Dropping off locations, then click on Search button.');
		}else{
			draw_important_message(_WRONG_EMPTY_FIELDS_PICK_UP_AND_DROP_OFF);
		}
	}elseif(empty($picking_up_id)){
		draw_important_message(_WRONG_EMPTY_FIELD_PICK_UP);
	}elseif(empty($dropping_off_id)){
		draw_important_message(_WRONG_EMPTY_FIELD_DROP_OFF);
	}elseif($pick_up_year == '0' || $drop_off_year == '0'){
		draw_important_message(_WRONG_PARAMETER_PASSED);
	}elseif(!checkdate($drop_off_month, $drop_off_day, $drop_off_year)){
		draw_important_message(_WRONG_CHECKOUT_DATE_ALERT);
	}elseif($unix_time_pick_up < time() || $unix_time_drop_off < $unix_time_pick_up){
		draw_important_message(_PAST_TIME_ALERT);
	}elseif($unix_time_pick_up >= $unix_time_drop_off){
		draw_important_message(_CAR_HALF_HOUR_ALERT);
	}else{
        draw_content_start();

		$lang = Application::Get('lang');		
        $result = array();
		$diff_country = false;
		
		// Checking on what locations are in the same country
        if(!empty($picking_up_id) && !empty($dropping_off_id) && $dropping_off_id != $picking_up_id){
            $sql = 'SELECT
                    al.id,
                    al.country_id,
                    ald.name
                FROM '.TABLE_CAR_AGENCIES_LOCATIONS.' al
                    LEFT OUTER JOIN '.TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION.' ald ON al.id = ald.agency_location_id AND ald.language_id = \''.$lang.'\'
                WHERE 
                    al.id IN ('.$picking_up_id.', '.$dropping_off_id.')
                LIMIT 2';
            $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			$diff_country = true;
			if($result[1] == 2){
				if($result[0][0]['country_id'] == $result[0][1]['country_id']){
					$diff_country = false;
				}
			}
        }

        if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
            draw_sub_title_bar(_FROM.': '.(!empty($location_names[$picking_up_id]) ? '<b>'.$location_names[$picking_up_id].'</b> ' : '').get_month_local($pick_up_month).date(' d, Y (H:i)', $unix_time_pick_up).' '._TO.': '.(!empty($location_names[$dropping_off_id]) ? '<b>'.$location_names[$dropping_off_id].'</b> ' : '').get_month_local($drop_off_month).date(' d, Y (H:i)', $unix_time_drop_off).(!empty($days) || !empty($hours) ? ' - ' : '').(!empty($days) ? ' '.$days.' '.strtolower($days > 1 ? _DAYS : _DAY) : '').(!empty($hours) ? ' '.$hours.' '.strtolower($hours > 1 ? _HOURS : _HOUR).' ' : ''), true, 'h4');
        }else{
            draw_sub_title_bar(_FROM.': '.(!empty($location_names[$picking_up_id]) ? '<b>'.$location_names[$picking_up_id].'</b> ' : '').date('H:i d', $unix_time_pick_up).' '.get_month_local($pick_up_month).' '.$pick_up_year.' '._TO.': '.(!empty($location_names[$dropping_off_id]) ? '<b>'.$location_names[$dropping_off_id].'</b> ' : '').date('H:i d', $unix_time_drop_off).' '.get_month_local($drop_off_month).' '.$drop_off_year.(!empty($days) || !empty($hours) ? ' - ' : '').(!empty($days) ? $days.' '.strtolower($days > 1 ? _DAYS : _DAY).' ' : ' ').(!empty($hours) ? $hours.' '.strtolower($hours > 1 ? _HOURS : _HOUR).' ' : ''), true, 'h4');
        }

		if($diff_country){
            draw_important_message(_PLEASE_SELECT_LOCATION);
		}else{
            $currency_code = Application::Get('currency_code');
            $currency_rate = Application::Get('currency_rate');
            if($currency_code != $current_currency){
                $current_currency_info = Currencies::GetCurrencyInfo($current_currency);
                if($current_currency_info['code'] != $currency_code){
                    $min_price = ceil($min_price * ($currency_rate / $current_currency_info['rate']));
                    $max_price = ceil($max_price * ($currency_rate / $current_currency_info['rate']));
                }
            }

            $objCarAgencies = new CarAgencies();
            $params = array(
                'view_type' => $view_type,
                'car_id' => $car_id,
                'picking_up' => $picking_up_id,
                'dropping_off' => $dropping_off_id,
                'pick_up_date_time' => date('Y-m-d H:i:s', $unix_time_pick_up),
                'drop_off_date_time' => date('Y-m-d H:i:s', $unix_time_drop_off),
                'min_price' => $min_price,
                'max_price' => $max_price,
                'make_sort' => $make_sort,
                'price_sort' => $price_sort,
                'types' => $types,
            );

            $cars_count = $objCarAgencies->SearchFor($params);

			// Removed - showen on the ledt column
            //echo '<div style="margin:10px 0;"><b>'._FOUND_AGENCIES.': '.$cars_count['agencies'].', '._TOTAL_CARS.': '.$cars_count['cars'].'</b></div>';
            //echo '<div class="clearfix"></div>';
			echo '<div class="line-hor"></div>';

			if($cars_count['cars'] > 0){
				echo $search_result = $objCarAgencies->DrawSearchResult($params, $cars_count['cars']);
			}else{
				draw_important_message(_NO_CARS_FOUND);
			}
		}
        draw_content_end();
	}
	
}

