<?php

/**
 * Draws search result
 * @param $obj_rooms
 * @param $params
 * @param $rooms_total
 * @param $draw
 */
function Template_DrawSearchResult($obj_rooms, $params, $rooms_total = 0, $draw = true)
{		
    global $objLogin, $objSettings;

    $arrAvailableRooms = $obj_rooms->GetAvailableRooms();
    
    $nl = "\n";
    $output = '';
    $currency_info     = Currencies::GetDefaultCurrencyInfo();
    $default_currency  = $currency_info['symbol'];
    $currency_decimals = $currency_info['decimals'];
    $currency_rate     = Application::Get('currency_rate');
    $currency_format   = get_currency_format();
    $lang              = Application::Get('lang');
    $template          = $objSettings->GetTemplate() != '' ? $objSettings->GetTemplate() : Application::Get('template');
    $customer_id       = $objLogin->GetLoggedID();
    $rooms_count       = 0;
    $hotels_count      = 0;
    $total_hotels      = Hotels::HotelsCount();
    $arr_facilities    = Template_get_prepare_facilities();

    $search_page_size = (int)ModulesSettings::Get('rooms', 'search_availability_page_size');
    $show_room_types_in_search = ModulesSettings::Get('rooms', 'show_room_types_in_search');
    if($search_page_size <= 0) $search_page_size = '1';
    $allow_children    = ModulesSettings::Get('rooms', 'allow_children');
    $allow_extra_beds  = ModulesSettings::Get('rooms', 'allow_extra_beds');
    $min_max_hotels    = isset($params['min_max_hotels']) ? $params['min_max_hotels'] : array();
    $sort_facilities   = isset($params['sort_facilities']) ? $params['sort_facilities'] : '';
    $sort_rating       = isset($params['sort_rating']) ? $params['sort_rating'] : '';
    $sort_price        = isset($params['sort_price']) ? $params['sort_price'] : '';
    $minimum_beds      = isset($params['minimum_beds']) ? $params['minimum_beds'] : '';
    $max_adults        = isset($params['max_adults']) && (int)$params['max_adults'] > 1 ? (int)$params['max_adults'] : 1;
    $best_select_rooms = array();
    $best_count_rooms  = array();
    $best_adults       = array();

    $allow_booking = false;
    if(Modules::IsModuleInstalled('booking')){
        if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
           ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
          (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
        ){
            $allow_booking = true;
        }
    }
    
    $sql = 'SELECT
                r.id,
                r.room_type,
                r.room_count,
                r.room_icon,
                r.refund_money_type,
                r.refund_money_value,
                IF(r.room_icon_thumb != \'\', r.room_icon_thumb, \'no_image.png\') as room_icon_thumb,
                r.room_picture_1,
                r.room_picture_2,
                r.room_picture_3,
                r.room_picture_4,
                r.room_picture_5,
                CASE
                    WHEN r.room_picture_1 != \'\' THEN r.room_picture_1
                    WHEN r.room_picture_2 != \'\' THEN r.room_picture_2
                    WHEN r.room_picture_3 != \'\' THEN r.room_picture_3
                    WHEN r.room_picture_4 != \'\' THEN r.room_picture_4
                    WHEN r.room_picture_5 != \'\' THEN r.room_picture_5
                    ELSE \'\'
                END as first_room_image,
                r.max_adults,
                r.max_children,
                r.max_extra_beds,
                r.discount_night_type,
                r.discount_night_3,
                r.discount_night_4,
                r.discount_night_5,
                r.discount_guests_type,
                r.discount_guests_3,
                r.discount_guests_4,
                r.discount_guests_5,
                r.facilities,
                r.default_price as price,
                rd.room_type as loc_room_type,
                rd.room_short_description as loc_room_short_description
            FROM '.TABLE_ROOMS.' r
                INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' rd ON r.id = rd.room_id
            WHERE
                r.id = _KEY_ AND
                rd.language_id = \''.$lang.'\'';
                
    if(count($arrAvailableRooms) == 1){

        // -------- pagination		
        $current_page = isset($_REQUEST['p']) ? (int)$_REQUEST['p'] : '1';
        $total_pages = (int)($rooms_total / $search_page_size);		
        if($current_page > ($total_pages+1)) $current_page = 1;
        if(($rooms_total % $search_page_size) != 0) $total_pages++;
        if(!is_numeric($current_page) || (int)$current_page <= 0) $current_page = 1;
        // --------
        
        if($rooms_total > 0){
            // get a first key of the array
            reset($arrAvailableRooms);
            $hotel_id              = key($arrAvailableRooms);
            $hotel_info            = Hotels::GetHotelFullInfo($hotel_id, $lang);
            $meal_plans            = MealPlans::GetAllMealPlans($hotel_id);
            $campaign_small_banner = Campaigns::DrawCampaignSmallBanner($hotel_id, false);
            $arrBestPrices         = $obj_rooms->GetArrBestPrices();
            $last_bookings         = Bookings::GetLastBookingForHotels(array($hotel_id));
            $canceled_days         = Hotels::GetCanceledDays($hotel_id, $params['from_date'], $params['to_date']);
            $reviews_info          = Template_get_review_info($hotel_id);
            $total_reviews         = $reviews_info['total_reviews'];
            $average_rating        = $reviews_info['average_rating'];
            
            $rating = '<img src="templates/'.$template.'/images/user-rating-'.round($average_rating).'.png" alt="user rating" /><br/>
                <span class="size11 grey">'.$total_reviews.' '.strtolower($total_reviews == 1 ? _REVIEW : _REVIEWS).'</span>';

            if(SHOW_BEST_PRICE_ROOMS){
                $arrBestRooms      = $obj_rooms->GetArrBestRooms();
                $arrBestRoomCount  = $obj_rooms->GetArrBestRoomCount();
                $arrBestRoomAdults = $obj_rooms->GetArrBestRoomAdults();
                if(empty($minimum_beds)){
                    $room_min_price = 0;
                    $room_id_best_price = 0;
                    foreach($arrAvailableRooms[$hotel_id] as $room){
                        if($room_min_price == 0 || $room['lowest_price_per_night'] < $room_min_price){
                            $room_min_price     = $room['lowest_price_per_night'];
                            $room_id_best_price = $room['id'];
                        }
                    }
                    if($room_id_best_price != 0){
                        $best_select_rooms = array($room_id_best_price);
                        $best_count_rooms  = array($room_id_best_price => 1);
                    }
                }else{
                    $best_select_rooms = !empty($arrBestRooms[$hotel_id])      ? $arrBestRooms[$hotel_id]      : array();
                    $best_count_rooms  = !empty($arrBestRoomCount[$hotel_id])  ? $arrBestRoomCount[$hotel_id]  : array();
                    $best_adults       = !empty($arrBestRoomAdults[$hotel_id]) ? $arrBestRoomAdults[$hotel_id] : array();
                }
            }
            
            // This hotel requires special min/max nights order
            if(isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['alert'])){
                $output .= '<tr>';
                $output .= '  <td align="left" colspan="8" style="padding:2px 10px">'.$min_max_hotels[$hotel_id]['alert'].'</td>';
                $output .= '<tr>';							
            }else{					
                $min_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['minimum_rooms']) ? $min_max_hotels[$hotel_id]['minimum_rooms'] : 0;
                $max_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['maximum_rooms']) ? $min_max_hotels[$hotel_id]['maximum_rooms'] : 0;
                $room_info = current($arrAvailableRooms[$hotel_id]);
                if($show_room_types_in_search == 'all' || $room_info['available_rooms'] >= 1 || $room_info['available_rooms'] < $min_rooms){
                    
                    $room = database_query(str_replace('_KEY_', $room_info['id'], $sql), DATA_AND_ROWS, FIRST_ROW_ONLY);
                    if($room[1] > 0){					
                        $room_adults = $max_adults;
                        $room_price_result   = Rooms::GetRoomPrice($room[0]['id'], $hotel_id, $params, 'array');
                        $room_total_price    = $room_price_result['total_price'];
                        $room_original_price = $room_price_result['original_price'];
                        $room_price = (Rooms::GetRoomPriceIncludeNightsDiscount($room_total_price / $params['nights'], $params['nights'], $room[0]['id'], $room) * $params['nights']);
                        if(empty($room_info['available_rooms'])) $rooms_descr = '<span class="gray">('._FULLY_BOOKED.')</span>';
                        else $rooms_descr = '<span class="green">('._AVAILABLE.')</span>';

                        if(!empty($minimum_beds)){
                            $room_adults = $room[0]['max_adults'] < $minimum_beds ? $room[0]['max_adults'] : $minimum_beds;
                        }
                        if(SHOW_BEST_PRICE_ROOMS && !empty($best_adults[$room[0]['id']])){
                            $room_adults = $best_adults[$room[0]['id']];
                        }

                        // START padding20
                        $output .= '<div class="padding20">';
                        // START itemlabel3
                        $output .= '<div class="itemlabel3">';
                        
                        if($hotel_info){
                            // Get info about wishlist
                            $in_wishlist  = Wishlist::GetWishListItemInfo($hotel_id, 'hotel_id', $customer_id);
                       
                            $hotel_img    = ($hotel_info['hotel_image'] != '' && file_exists('images/hotels/'.$hotel_info['hotel_image'])) ? 'images/hotels/'.$hotel_info['hotel_image'] : 'images/hotels/no_image.png';
                            $hotel_name   = prepare_link('flats', 'fid', $hotel_info['id'], $hotel_info['name'], $hotel_info['name'], '', _CLICK_TO_SEE_DESCR);
                            $hotel_stars  = (($hotel_info['stars'] > 0) ? (int)$hotel_info['stars'] : '0');
                            $hotel_viewed = (($hotel_info['number_of_views'] > 1) ? str_replace('{number}', $hotel_info['number_of_views'], _VIEWED_TIMES) : _VIEWED_TIME);

                            // Select the minimum price in arrAvailableRooms since GetHotelFullInfo
                            // returns the minimum price for selecting all available rooms, excluding filter
                            $hotel_lowest_price = 0;
                            for($i = 0, $max_count = count($arrAvailableRooms[$hotel_id]); $i < $max_count; $i++){
                                if(empty($hotel_lowest_price) || $hotel_lowest_price > $arrAvailableRooms[$hotel_id][$i]['lowest_price_per_night']){
                                    $hotel_lowest_price = $arrAvailableRooms[$hotel_id][$i]['lowest_price_per_night'];
                                }
                            }
                            $hotel_descr = substr_by_word(str_replace(array('<br>', '<br />'), ' ', $hotel_info['description']), 300, true);
                            $room_facilities = !empty($room_info['facilities']) ? @unserialize($room_info['facilities']) : array();

                            // Show Banner
                            if(!empty($campaign_small_banner)){
                                $output .= '<div class="campaing-msg">'.$campaign_small_banner.'</div>';
                            }
                            // START hotel-info-block
                            $output .= '<div class="hotel-info-block">';
                            // START form
                            $output .= '<form action="index.php?page=booking" method="post">'.$nl;
                            $output .= draw_hidden_field('act', 'add', false).$nl;
                            $output .= draw_hidden_field('hotel_id', $hotel_id, false).$nl;
                            $output .= draw_hidden_field('room_id', $room[0]['id'], false).$nl;
                            $output .= draw_hidden_field('available_rooms', '1-'.$room_total_price, false).$nl; 
                            if(isset($params['from_date']))           $output .= draw_hidden_field('from_date', $params['from_date'], false).$nl;
                            if(isset($params['to_date']))             $output .= draw_hidden_field('to_date', $params['to_date'], false).$nl;
                            if(isset($params['nights']))              $output .= draw_hidden_field('nights', $params['nights'], false).$nl;
                            if(isset($params['max_children']))        $output .= draw_hidden_field('children', $params['max_children'], false).$nl;
                            if(isset($params['hotel_sel_id']))        $output .= draw_hidden_field('hotel_sel_id', $params['hotel_sel_id'], false).$nl;
                            if(isset($params['hotel_sel_loc_id']))    $output .= draw_hidden_field('hotel_sel_loc_id', $params['hotel_sel_loc_id'], false).$nl;
                            if(isset($params['property_type_id']))    $output .= draw_hidden_field('property_type_id', $params['property_type_id'], false).$nl;
                            if(isset($params['checkin_year_month']))  $output .= draw_hidden_field('checkin_year_month', $params['from_year'].'-'.(int)$params['from_month'], false).$nl;
                            if(isset($params['checkin_monthday']))    $output .= draw_hidden_field('checkin_monthday', $params['from_day'], false).$nl;
                            if(isset($params['checkout_year_month'])) $output .= draw_hidden_field('checkout_year_month', $params['to_year'].'-'.(int)$params['to_month'], false).$nl;
                            if(isset($params['checkout_monthday']))   $output .= draw_hidden_field('checkout_monthday', $params['to_day'], false).$nl;
                            $output .= draw_token_field(false).$nl;

                            // START col-md-10
                            $output .= '<div class="col-md-10">';
                            // START col-md-5
                            $output .= '<div class="col-md-5">';

                            // Determine the best hotel
                            if(SHOW_BEST_PRICE_ROOMS){
                                $best_hotel = ($hotel_id == $obj_rooms->GetHotelIdBestPrice()) ? true : false;
                                if($best_hotel){
                                    $output .= '<div class="label-tab success best-price-tab">'._FLAT_WITH_BEST_PRICE.' <span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($obj_rooms->GetBestPriceValue()) * $currency_rate, '', '', $currency_format).'</b></span></div>';
                                }elseif(!empty($arrBestPrices[$hotel_id])){
                                    $output .= '<div class="label-tab success best-price-tab"><span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($arrBestPrices[$hotel_id]) * $currency_rate, '', '', $currency_format).'</b></span></div>';
                                }
                            }
                            if(empty($arrBestPrices) && $max_adults > 1){
                                $output .= '<div class="label-tab success best-price-tab">'._BEST_PRICE.' <span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($hotel_lowest_price) * $currency_rate, '', '', $currency_format).' ['.$max_adults.' '._ADULTS.']</b></span></div>';
                            }

                            // START listitem2
                            $output .= '<div class="listitem2">';
                            $output .= '<a href="'.$hotel_img.'" data-footer="" data-title="" data-gallery="multiimages" data-toggle="lightbox"><img src="'.$hotel_img.'" alt=""/></a>';
                            $output .= '<br /><div class="liover"></div>';
                                    
                            // Draw wishlist icon
                            if($in_wishlist){
                                $output .= Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'remove', 'fav-icon', '&nbsp;');
                            }else{
                                $output .= Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'add', 'fav-icon', '&nbsp;');
                            }
                            
                            $output .= '<a class="book-icon" href="index.php?page=flats&fid='.$hotel_id.'"></a>';
                            $output .= '</div>'; // END listitem2
                            $output .= '</div>'; // END col-md-5
                            // START col-md-7
                            $output .= '<div class="col-md-7 pl0r15">';
                            // START labelleft2
                            $output .= '<div class="labelleft2">';
                                        
                            // Draw wishlist icon
                            if($in_wishlist){
                                $output .= '<span class="pull-right">'.Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'remove').'</span>';
                            }
                            
                            $output .= '<b class="hn_new">'.$hotel_name.' '.$rooms_descr.'</b><br>';
                            $output .= $hotel_info['location_name'].' &nbsp;('.$hotel_viewed.')<br>';
                            $output .= '<p class="grey">'.substr_by_word($hotel_descr, (count($room_facilities) > 12 ? 200 : 270), true).'</p>';
                            $output .= '<ul class="hotelpreferences'.(count($room_facilities) > 12 ? ' hp_new_many' : ' hp_new').'">';
                            if(is_array($room_facilities)){
                                foreach($room_facilities as $key => $val){
                                    if(isset($arr_facilities[$val])) $output .= '<li'.(empty($arr_facilities[$val]['icon']) ? ' class="icohp-'.$arr_facilities[$val]['code'].'"' : '').' title="'.htmlentities($arr_facilities[$val]['name']).'">'.(!empty($arr_facilities[$val]['icon']) ? '<img src="images/facilities/'.$arr_facilities[$val]['icon'].'"/>' : '').'</li>';
                                }					
                            }
                            $output .= '</ul>';
                            $output .= '</div>'; // END labelleft2
                            $output .= '</div>'; // END col-md-7
                            $output .= '<div class="clearfix"></div>';
                            $output .= '<div class="line-hor"></div>';
                            // START col-md-12
                            $output .= '<div class="col-md-12">';
                            $output .= '<div class="col-md-3"><b>'._MAX_ADULTS.': '.$room[0]['max_adults'].(($allow_children == 'yes') ? '<br/> '._MAX_CHILDREN.': '.$room[0]['max_children'] : '').'</b></div>';
                            if($room_info['available_rooms']){ 
                                if(!empty($room_adults)){
                                    $output .= '<div class="col-md-3 parent_adults_ddl">';
                                    $output .= draw_hidden_field('adults', $room_adults, false).$nl;
                                    $output .= '<select name="adults" class="form-control adults_ddl">';
                                    for($i = 1; $i <= $room[0]['max_adults']; $i++){
                                        $output .= '<option value="'.$i.'">'.$i.'</option>';
                                    }
                                    $output .= '</select>';
                                    $output .= _ADULTS.': ';
                                    $output .= '</div>';									
                                }

                                if($meal_plans[1] > 0){
                                    $output .= '<div class="col-md-6 parent_available_meals_ddl">';
                                    $output .= MealPlans::DrawMealPlansDDL($meal_plans, $currency_rate, $currency_format, $allow_booking, false);
                                    $output .= _MEAL_PLANS.': <br/>';
                                    $output .= '<span class="red">*</span> <small>'._PERSON_PER_NIGHT.'</small></span>';
                                    $output .= '</div>';									
                                }
                                //if($allow_extra_beds == 'yes' && $room[0]['max_extra_beds'] > 0){
                                    //$output .= '<div class="col-md-offset-3 col-md-4 parent_available_extra_beds_ddl">';
                                    //$output .= Template_draw_extra_beds_ddl($room[0]['id'], $room[0]['max_extra_beds'], $params, $currency_rate, $currency_format, $allow_booking, false);
                                    //$output .= _EXTRA_BEDS.': <br/>';
                                    //$output .= '<span class="red">*</span> <small>'._PER_NIGHT.'</small></span>';
                                    //$output .= '</div>';
                                //}
                            }
                            $output .= '<div class="clearfix paddingbtm10"></div>';
                            // START table#1
                            $output .= '<table border="0" width="100%">'.$nl;
                            $output .= '<tr valign="top">';
                            $output .= '<td>';
                            // START table#2
                            $output .= '<table border="0" width="100%">';					
                            $output .= '<tr><td colspan="2"><a class="price_link" href="javascript:void(0);" onclick="javascript:appToggleElement(\'row_prices_'.$room[0]['id'].'\')" title="'._CLICK_TO_SEE_PRICES.'">'._PRICES.' (+)</a></td></tr>';
                            $output .= '</table>'; // END table#2
                            $output .= '</td>';
                            $output .= '</tr>';
                            $output .= '<tr><td colspan="2"><span id="row_prices_'.$room[0]['id'].'" style="margin:5px 5px 10px 5px;display:none;">'.Rooms::GetRoomPricesTable($room[0]['id']).'</span></td></tr>';
                            $output .= '<tr><td colspan="2"><br /><td></tr>';
                            $output .= '</table>'.$nl; // END table#1
                            $output .= '</div>'; // END col-md-12
                            $output .= '</div>'; // END col-md-10
                            // START col-md-2
                            $output .= '<div class="col-md-2 labelright center ml10r0">';
                            $output .= '<img src="templates/'.$template.'/images/filter-rating-'.$hotel_stars.'.png" alt="stars rating" width="60" />';
                            $output .= '<br/><br/>'.$rating.'<br><br>';
                            $output .= '<center>';
                            $output .= '<span class="green size16">';
                            if($room_price != $room_original_price){
                                $output .= '<span class="old-price">'.Currencies::PriceFormat(($room_original_price * $currency_rate) / $params['nights'], '', '', $currency_format).'</span> ';
                                $output .= '<span class="new-price">';
                                $output .= Currencies::PriceFormat(($room_price * $currency_rate) / $params['nights'], '', '', $currency_format);
                                $output .= '</span>';
                            }else{
                                $output .= Currencies::PriceFormat(($room_price * $currency_rate) / $params['nights'], '', '', $currency_format);
                            }
                            $output .= '</span>';
                            $output .= '<br>';
                            $output .= '<span class="size11 grey">'._FROM_PER_NIGHT.'</span>';
                            $output .= '<br>';
                            $output .= '<br>';
                            $output .= '<span class="green size22">';
                            $output .= Currencies::PriceFormat($room_price * $currency_rate, '', '', $currency_format);
                            $output .= '</span>';
                            $output .= '</center>';
                            $output .= '<br/><br/>';
                            $output .= '<button class="bookbtn mt1" type="button" onclick="jQuery(location).attr(\'href\',\'index.php?page=flats&fid='.$hotel_id.'\')">'._DETAILS.'</button>';
                            $output .= '<br/><br/>';
                            if($allow_booking && $room_info['available_rooms']){
                                if(Reservation::RoomIsReserved($room[0]['id'])){
                                    $output .= '<input type="submit" '.(HOTEL_BUTTON_RESERVE_AJAX ? 'data-room-id="'.$room[0]['id'].'" data-type="remove" value="'._REMOVE.'!" ' : 'value="'._BOOK_NOW.'" ').'class="form_button_middle red" style="margin:3px;" />';
                                }else{
                                    $output .= '<input type="submit" '.(HOTEL_BUTTON_RESERVE_AJAX ? 'data-room-id="'.$room[0]['id'].'" data-type="reserve" value="'._RESERVE.'!" ' : 'value="'._BOOK_NOW.'" ').'class="form_button_middle green" style="margin:3px;" />';
                                }
                            }
                            $output .= '</div>'; // END col-md-2
                            $output .= '</form>'.$nl; // END form
                            $output .= '</div>'; // END hotel-info-block
                        }
                        $output .= '</div>'; // itemlabel3
                        $output .= '</div>'; // padding20
                    }
                }
            }

            $output .= Template_draw_pagination_links($total_pages, $current_page, $params, false);	
        }
            
    }else{
        // multi hotels found
        // -------- pagination
        $hotels_total = count($arrAvailableRooms);
        $current_page = isset($_REQUEST['p']) ? (int)$_REQUEST['p'] : '1';
        $total_pages = (int)($hotels_total / $search_page_size);		
        if($current_page > ($total_pages+1)) $current_page = 1;
        if(($hotels_total % $search_page_size) != 0) $total_pages++;
        if(!is_numeric($current_page) || (int)$current_page <= 0) $current_page = 1;
        // --------

        if($rooms_total > 0){				
            $arr_hotel_ids = array();
            foreach($arrAvailableRooms as $key => $val){
                $hotels_count++;		
                if($hotels_count <= ($search_page_size * ($current_page - 1))){
                    continue;
                }elseif($hotels_count > ($search_page_size * ($current_page - 1)) + $search_page_size){
                    break;
                }
                $arr_hotel_ids[] = $key;
            }

            $hotels_count  = 0;
                
            foreach($arrAvailableRooms as $hotel_id => $val){
                $hotels_count++;		
                if($hotels_count <= ($search_page_size * ($current_page - 1))){
                    continue;
                }elseif($hotels_count > ($search_page_size * ($current_page - 1)) + $search_page_size){
                    break;
                }

                $hotel_info            = Hotels::GetHotelFullInfo($hotel_id, $lang);
                $meal_plans            = MealPlans::GetAllMealPlans($hotel_id);
                $campaign_small_banner = Campaigns::DrawCampaignSmallBanner($hotel_id, false);
                $arrBestPrices         = $obj_rooms->GetArrBestPrices();
                $last_bookings         = Bookings::GetLastBookingForHotels(array($hotel_id));
                $canceled_days         = Hotels::GetCanceledDays($hotel_id, $params['from_date'], $params['to_date']);
                $reviews_info          = Template_get_review_info($hotel_id);
                $total_reviews         = $reviews_info['total_reviews'];
                $average_rating        = $reviews_info['average_rating'];
                
                $rating = '<img src="templates/'.$template.'/images/user-rating-'.round($average_rating).'.png" alt="user rating" /><br/>
                    <span class="size11 grey">'.$total_reviews.' '.strtolower($total_reviews == 1 ? _REVIEW : _REVIEWS).'</span>';

                if(SHOW_BEST_PRICE_ROOMS){
                    $arrBestRooms      = $obj_rooms->GetArrBestRooms();
                    $arrBestRoomCount  = $obj_rooms->GetArrBestRoomCount();
                    $arrBestRoomAdults = $obj_rooms->GetArrBestRoomAdults();
                    if(empty($minimum_beds)){
                        $room_min_price = 0;
                        $room_id_best_price = 0;
                        foreach($arrAvailableRooms[$hotel_id] as $room){
                            if($room_min_price == 0 || $room['lowest_price_per_night'] < $room_min_price){
                                $room_min_price     = $room['lowest_price_per_night'];
                                $room_id_best_price = $room['id'];
                            }
                        }
                        if($room_id_best_price != 0){
                            $best_select_rooms = array($room_id_best_price);
                            $best_count_rooms  = array($room_id_best_price => 1);
                        }
                    }else{
                        $best_select_rooms = !empty($arrBestRooms[$hotel_id])      ? $arrBestRooms[$hotel_id]      : array();
                        $best_count_rooms  = !empty($arrBestRoomCount[$hotel_id])  ? $arrBestRoomCount[$hotel_id]  : array();
                        $best_adults       = !empty($arrBestRoomAdults[$hotel_id]) ? $arrBestRoomAdults[$hotel_id] : array();
                    }
                }
                
                // This hotel requires special min/max nights order
                if(isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['alert'])){
                    $output .= '<tr>';
                    $output .= '  <td align="left" colspan="8" style="padding:2px 10px">'.$min_max_hotels[$hotel_id]['alert'].'</td>';
                    $output .= '<tr>';							
                }else{					
                    $min_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['minimum_rooms']) ? $min_max_hotels[$hotel_id]['minimum_rooms'] : 0;
                    $max_rooms = isset($min_max_hotels[$hotel_id]) && !empty($min_max_hotels[$hotel_id]['maximum_rooms']) ? $min_max_hotels[$hotel_id]['maximum_rooms'] : 0;
                    $room_info = current($arrAvailableRooms[$hotel_id]);
                    if($show_room_types_in_search == 'all' || $room_info['available_rooms'] >= 1 || $room_info['available_rooms'] < $min_rooms){
                        
                        $room = database_query(str_replace('_KEY_', $room_info['id'], $sql), DATA_AND_ROWS, FIRST_ROW_ONLY);
                        if($room[1] > 0){					
                            $room_adults = $max_adults;
                            $room_price_result   = Rooms::GetRoomPrice($room[0]['id'], $hotel_id, $params, 'array');
                            $room_total_price    = $room_price_result['total_price'];
                            $room_original_price = $room_price_result['original_price'];
                            $room_price = (Rooms::GetRoomPriceIncludeNightsDiscount($room_total_price / $params['nights'], $params['nights'], $room[0]['id'], $room) * $params['nights']);
                            if(empty($room_info['available_rooms'])) $rooms_descr = '<span class="gray">('._FULLY_BOOKED.')</span>';
                            else $rooms_descr = '<span class="green">('._AVAILABLE.')</span>';

                            if(!empty($minimum_beds)){
                                $room_adults = $room[0]['max_adults'] < $minimum_beds ? $room[0]['max_adults'] : $minimum_beds;
                            }
                            if(SHOW_BEST_PRICE_ROOMS && !empty($best_adults[$room[0]['id']])){
                                $room_adults = $best_adults[$room[0]['id']];
                            }

                            // START padding20
                            $output .= '<div class="padding20">';
                            // START itemlabel3
                            $output .= '<div class="itemlabel3">';
                            
                            if($hotel_info){
                                // Get info about wishlist
                                $in_wishlist  = Wishlist::GetWishListItemInfo($hotel_id, 'hotel_id', $customer_id);
                           
                                $hotel_img    = ($hotel_info['hotel_image'] != '' && file_exists('images/hotels/'.$hotel_info['hotel_image'])) ? 'images/hotels/'.$hotel_info['hotel_image'] : 'images/hotels/no_image.png';
                                $hotel_name   = prepare_link('flats', 'fid', $hotel_info['id'], $hotel_info['name'], $hotel_info['name'], '', _CLICK_TO_SEE_DESCR);
                                $hotel_stars  = (($hotel_info['stars'] > 0) ? (int)$hotel_info['stars'] : '0');
                                $hotel_viewed = (($hotel_info['number_of_views'] > 1) ? str_replace('{number}', $hotel_info['number_of_views'], _VIEWED_TIMES) : _VIEWED_TIME);

                                // Select the minimum price in arrAvailableRooms since GetHotelFullInfo
                                // returns the minimum price for selecting all available rooms, excluding filter
                                $hotel_lowest_price = 0;
                                for($i = 0, $max_count = count($arrAvailableRooms[$hotel_id]); $i < $max_count; $i++){
                                    if(empty($hotel_lowest_price) || $hotel_lowest_price > $arrAvailableRooms[$hotel_id][$i]['lowest_price_per_night']){
                                        $hotel_lowest_price = $arrAvailableRooms[$hotel_id][$i]['lowest_price_per_night'];
                                    }
                                }
                                $hotel_descr = substr_by_word(str_replace(array('<br>', '<br />'), ' ', $hotel_info['description']), 300, true);
                                $room_facilities = !empty($room_info['facilities']) ? @unserialize($room_info['facilities']) : array();

                                // Show Banner
                                if(!empty($campaign_small_banner)){
                                    $output .= '<div class="campaing-msg">'.$campaign_small_banner.'</div>';
                                }
                                // START hotel-info-block
                                $output .= '<div class="hotel-info-block">';
                                // START form
                                $output .= '<form action="index.php?page=booking" method="post">'.$nl;
                                $output .= draw_hidden_field('act', 'add', false).$nl;
                                $output .= draw_hidden_field('hotel_id', $hotel_id, false).$nl;
                                $output .= draw_hidden_field('room_id', $room[0]['id'], false).$nl;
                                $output .= draw_hidden_field('available_rooms', '1-'.$room_total_price, false).$nl; 
                                if(isset($params['from_date']))           $output .= draw_hidden_field('from_date', $params['from_date'], false).$nl;
                                if(isset($params['to_date']))             $output .= draw_hidden_field('to_date', $params['to_date'], false).$nl;
                                if(isset($params['nights']))              $output .= draw_hidden_field('nights', $params['nights'], false).$nl;
                                if(isset($params['max_children']))        $output .= draw_hidden_field('children', $params['max_children'], false).$nl;
                                if(isset($params['hotel_sel_id']))        $output .= draw_hidden_field('hotel_sel_id', $params['hotel_sel_id'], false).$nl;
                                if(isset($params['hotel_sel_loc_id']))    $output .= draw_hidden_field('hotel_sel_loc_id', $params['hotel_sel_loc_id'], false).$nl;
                                if(isset($params['property_type_id']))    $output .= draw_hidden_field('property_type_id', $params['property_type_id'], false).$nl;
                                if(isset($params['checkin_year_month']))  $output .= draw_hidden_field('checkin_year_month', $params['from_year'].'-'.(int)$params['from_month'], false).$nl;
                                if(isset($params['checkin_monthday']))    $output .= draw_hidden_field('checkin_monthday', $params['from_day'], false).$nl;
                                if(isset($params['checkout_year_month'])) $output .= draw_hidden_field('checkout_year_month', $params['to_year'].'-'.(int)$params['to_month'], false).$nl;
                                if(isset($params['checkout_monthday']))   $output .= draw_hidden_field('checkout_monthday', $params['to_day'], false).$nl;
                                $output .= draw_token_field(false).$nl;

                                // START col-md-10
                                $output .= '<div class="col-md-10">';
                                // START col-md-5
                                $output .= '<div class="col-md-5">';

                                // Determine the best hotel
                                if(SHOW_BEST_PRICE_ROOMS){
                                    $best_hotel = ($hotel_id == $obj_rooms->GetHotelIdBestPrice()) ? true : false;
                                    if($best_hotel){
                                        $output .= '<div class="label-tab success best-price-tab">'._FLAT_WITH_BEST_PRICE.' <span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($obj_rooms->GetBestPriceValue()) * $currency_rate, '', '', $currency_format).'</b></span></div>';
                                    }elseif(!empty($arrBestPrices[$hotel_id])){
                                        $output .= '<div class="label-tab success best-price-tab"><span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($arrBestPrices[$hotel_id]) * $currency_rate, '', '', $currency_format).'</b></span></div>';
                                    }
                                }
                                if(empty($arrBestPrices) && $max_adults > 1){
                                    $output .= '<div class="label-tab success best-price-tab">'._BEST_PRICE.' <span style="font-size:15px;color:#ffffff;"><b>'.Currencies::PriceFormat(($hotel_lowest_price) * $currency_rate, '', '', $currency_format).' ['.$max_adults.' '._ADULTS.']</b></span></div>';
                                }

                                // START listitem2
                                $output .= '<div class="listitem2">';
                                $output .= '<a href="'.$hotel_img.'" data-footer="" data-title="" data-gallery="multiimages" data-toggle="lightbox"><img src="'.$hotel_img.'" alt=""/></a>';
                                $output .= '<br /><div class="liover"></div>';
                                        
                                // Draw wishlist icon
                                if($in_wishlist){
                                    $output .= Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'remove', 'fav-icon', '&nbsp;');
                                }else{
                                    $output .= Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'add', 'fav-icon', '&nbsp;');
                                }
                                
                                $output .= '<a class="book-icon" href="index.php?page=flats&fid='.$hotel_id.'"></a>';
                                $output .= '</div>'; // END listitem2
                                $output .= '</div>'; // END col-md-5
                                // START col-md-7
                                $output .= '<div class="col-md-7 pl0r15">';
                                // START labelleft2
                                $output .= '<div class="labelleft2">';
                                            
                                // Draw wishlist icon
                                if($in_wishlist){
                                    $output .= '<span class="pull-right">'.Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'remove').'</span>';
                                }
                                
                                $output .= '<b class="hn_new">'.$hotel_name.' '.$rooms_descr.'</b><br>';
                                $output .= $hotel_info['location_name'].' &nbsp;('.$hotel_viewed.')<br>';
                                $output .= '<p class="grey">'.substr_by_word($hotel_descr, (count($room_facilities) > 12 ? 200 : 270), true).'</p>';
                                $output .= '<ul class="hotelpreferences'.(count($room_facilities) > 12 ? ' hp_new_many' : ' hp_new').'">';
                                if(is_array($room_facilities)){
                                    foreach($room_facilities as $key => $val){
                                        if(isset($arr_facilities[$val])) $output .= '<li'.(empty($arr_facilities[$val]['icon']) ? ' class="icohp-'.$arr_facilities[$val]['code'].'"' : '').' title="'.htmlentities($arr_facilities[$val]['name']).'">'.(!empty($arr_facilities[$val]['icon']) ? '<img src="images/facilities/'.$arr_facilities[$val]['icon'].'"/>' : '').'</li>';
                                    }					
                                }
                                $output .= '</ul>';
                                $output .= '</div>'; // END labelleft2
                                $output .= '</div>'; // END col-md-7
                                $output .= '<div class="clearfix"></div>';
                                $output .= '<div class="line-hor"></div>';
                                // START col-md-12
                                $output .= '<div class="col-md-12">';
                                $output .= '<div class="col-md-3"><b>'._MAX_ADULTS.': '.$room[0]['max_adults'].(($allow_children == 'yes') ? '<br/> '._MAX_CHILDREN.': '.$room[0]['max_children'] : '').'</b></div>';
                                if($room_info['available_rooms']){ 
                                    if(!empty($room_adults)){
                                        $output .= '<div class="col-md-3 parent_adults_ddl">';
                                        $output .= draw_hidden_field('adults', $room_adults, false).$nl;
                                        $output .= '<select name="adults" class="form-control adults_ddl">';
                                        for($i = 1; $i <= $room[0]['max_adults']; $i++){
                                            $output .= '<option value="'.$i.'">'.$i.'</option>';
                                        }
                                        $output .= '</select>';
                                        $output .= _ADULTS.': ';
                                        $output .= '</div>';									
                                    }

                                    if($meal_plans[1] > 0){
                                        $output .= '<div class="col-md-6 parent_available_meals_ddl">';
                                        $output .= MealPlans::DrawMealPlansDDL($meal_plans, $currency_rate, $currency_format, $allow_booking, false);
                                        $output .= _MEAL_PLANS.': <br/>';
                                        $output .= '<span class="red">*</span> <small>'._PERSON_PER_NIGHT.'</small></span>';
                                        $output .= '</div>';									
                                    }
                                    //if($allow_extra_beds == 'yes' && $room[0]['max_extra_beds'] > 0){
                                        //$output .= '<div class="col-md-offset-3 col-md-4 parent_available_extra_beds_ddl">';
                                        //$output .= Template_draw_extra_beds_ddl($room[0]['id'], $room[0]['max_extra_beds'], $params, $currency_rate, $currency_format, $allow_booking, false);
                                        //$output .= _EXTRA_BEDS.': <br/>';
                                        //$output .= '<span class="red">*</span> <small>'._PER_NIGHT.'</small></span>';
                                        //$output .= '</div>';
                                    //}
                                }
                                $output .= '<div class="clearfix paddingbtm10"></div>';
                                // START table#1
                                $output .= '<table border="0" width="100%">'.$nl;
                                $output .= '<tr valign="top">';
                                $output .= '<td>';
                                // START table#2
                                $output .= '<table border="0" width="100%">';					
                                $output .= '<tr><td colspan="2"><a class="price_link" href="javascript:void(0);" onclick="javascript:appToggleElement(\'row_prices_'.$room[0]['id'].'\')" title="'._CLICK_TO_SEE_PRICES.'">'._PRICES.' (+)</a></td></tr>';
                                $output .= '</table>'; // END table#2
                                $output .= '</td>';
                                $output .= '</tr>';
                                $output .= '<tr><td colspan="2"><span id="row_prices_'.$room[0]['id'].'" style="margin:5px 5px 10px 5px;display:none;">'.Rooms::GetRoomPricesTable($room[0]['id']).'</span></td></tr>';
                                $output .= '<tr><td colspan="2"><br /><td></tr>';
                                $output .= '</table>'.$nl; // END table#1
                                $output .= '</div>'; // END col-md-12
                                $output .= '</div>'; // END col-md-10
                                // START col-md-2
                                $output .= '<div class="col-md-2 labelright center ml10r0">';
                                $output .= '<img src="templates/'.$template.'/images/filter-rating-'.$hotel_stars.'.png" alt="stars rating" width="60" />';
                                $output .= '<br/><br/>'.$rating.'<br><br>';
                                $output .= '<center>';
                                $output .= '<span class="green size16">';
                                if($room_price != $room_original_price){
                                    $output .= '<span class="old-price">'.Currencies::PriceFormat(($room_original_price * $currency_rate) / $params['nights'], '', '', $currency_format).'</span> ';
                                    $output .= '<span class="new-price">';
                                    $output .= Currencies::PriceFormat(($room_price * $currency_rate) / $params['nights'], '', '', $currency_format);
                                    $output .= '</span>';
                                }else{
                                    $output .= Currencies::PriceFormat(($room_price * $currency_rate) / $params['nights'], '', '', $currency_format);
                                }
                                $output .= '</span>';
                                $output .= '<br>';
                                $output .= '<span class="size11 grey">'._FROM_PER_NIGHT.'</span>';
                                $output .= '<br>';
                                $output .= '<br>';
                                $output .= '<span class="green size22">';
                                $output .= Currencies::PriceFormat($room_price * $currency_rate, '', '', $currency_format);
                                $output .= '</span>';
                                $output .= '</center>';
                                $output .= '<br/><br/>';
                                $output .= '<button class="bookbtn mt1" type="button" onclick="jQuery(location).attr(\'href\',\'index.php?page=flats&fid='.$hotel_id.'\')">'._DETAILS.'</button>';
                                $output .= '<br/><br/>';
                                if($allow_booking && $room_info['available_rooms']){
                                    if(Reservation::RoomIsReserved($room[0]['id'])){
                                        $output .= '<input type="submit" '.(HOTEL_BUTTON_RESERVE_AJAX ? 'data-room-id="'.$room[0]['id'].'" data-type="remove" value="'._REMOVE.'!" ' : 'value="'._BOOK_NOW.'" ').'class="form_button_middle red" style="margin:3px;" />';
                                    }else{
                                        $output .= '<input type="submit" '.(HOTEL_BUTTON_RESERVE_AJAX ? 'data-room-id="'.$room[0]['id'].'" data-type="reserve" value="'._RESERVE.'!" ' : 'value="'._BOOK_NOW.'" ').'class="form_button_middle green" style="margin:3px;" />';
                                    }
                                }
                                $output .= '</div>'; // END col-md-2
                                $output .= '</form>'.$nl; // END form
                                $output .= '</div>'; // END hotel-info-block
                            }
                            $output .= '</div>'; // itemlabel3
                            $output .= '</div>'; // padding20
                        }
                    }
                }
            }
        
            $output .= Template_draw_pagination_links($total_pages, $current_page, $params, false);			
        }
    }

    if(HOTEL_BUTTON_RESERVE_AJAX){
        $output .= '<script>
            var dialog = null, table, type, key;

            jQuery(document).ready(function(){
                var $ = jQuery;

                $(".form_button_middle").click(function(){
                    var objButton = this;
                    var token = "'.htmlentities(Application::Get('token')).'";
                    var room_id = $(this).data("room-id");
                    var type = $(this).data("type");
					
					if($(objButton).hasClass("ajax-working")){
						return false;
					}
					
                    if($(this).parents("form").length > 0){
                        var arr_inpud = $(this).parents("form").find("input").toArray();
                        arr_inpud = arr_inpud.concat($(this).parents("form").find("select").toArray());
                    }else{
                        var arr_inpud = $(this).parents("tr").find("input").toArray();
                        arr_inpud = arr_inpud.concat($(this).parents("tr").find("select").toArray());
                    }
                    var data = {};
                    for(i = 0, max_count = arr_inpud.length; i < max_count; i++){
                        val = $(arr_inpud[i]).val();
                        key = $(arr_inpud[i]).attr("name");
                        if(key != undefined){
                            data[key] = val;
                        }
                    }

                    data.act = "send";
                    data.type = type;

                    jQuery.ajax({
                        url: "ajax/reserve_room.ajax.php",
                        global: false,
                        type: "POST",
                        data: (data),
                        dataType: "json",
                        async: true,
						beforeSend: function(){ $(objButton).addClass("ajax-working"); },
						complete: function(){ $(objButton).removeClass("ajax-working"); },                            
                        error: function(html){ console.log("AJAX: cannot connect to the server or server response error! Please try again later."); },
                        success: function(data){
                            var error = data.error !== undefined ? data.error : false;
                            var message = data.message;
                            if(data.toastr_message != undefined){
                                if(typeof toastr != "undefined"){
                                    toastr.remove();
                                }
                                if(data.toastr_message != ""){
                                    jQuery(document).ready(function(){
                                        if(typeof toastr != "undefined"){
                                            // Init
                                            toastr.options = {
                                              "closeButton": true,
                                              "debug": false,
                                              "newestOnTop": false,
                                              "progressBar": false,
                                              "positionClass": "toast-bottom-left",
                                              "preventDuplicates": false,
                                              "onclick": null,
                                              "showDuration": "300",
                                              "hideDuration": "1000",
                                              "timeOut": "0",
                                              "extendedTimeOut": "0",
                                              "showEasing": "swing",
                                              "hideEasing": "linear",
                                              "showMethod": "fadeIn",
                                              "hideMethod": "fadeOut",
                                              "rtl": '.(Application::Get('lang_dir') == 'rtl' ? 'true' : 'false').'
                                            }
                            
                                            toastr.success(data.toastr_message);
                                        }
                                    });
                                }
                            }
                            if(message.length > 0){
                                // dialog
                                if(error == false){
                                    if(type == "reserve"){
                                        $(objButton).removeClass("green");
                                        $(objButton).addClass("red");
                                        $(objButton).data("type", "remove");
                                        $(objButton).val("'.htmlspecialchars(_REMOVE).'!");
                                    }else{
                                        $(objButton).removeClass("red");
                                        $(objButton).addClass("green");
                                        $(objButton).data("type", "reserve");
                                        $(objButton).val("'.htmlspecialchars(_RESERVE).'!");
                                    }
                                }
                                $("#dialog-message").html(message);
                                dialog = jQuery( "#dialog-alert" ).dialog({
                                  autoOpen: true,
                                  width: 500,
                                  buttons: {
                                    "'.str_replace('"', '\\"', htmlspecialchars(_CONTINUE_RESERVATION)).'": function(){
                                        window.location.href = "index.php?page=booking";
                                    },
                                    "Ok": function() {
                                      dialog.dialog("close");
                                      if(type == "reserve" && typeof toastr != "undefined"){
                                        // Init
                                        toastr.options = {
                                          "closeButton": true,
                                          "debug": false,
                                          "newestOnTop": false,
                                          "progressBar": false,
                                          "positionClass": "toast-bottom-right",
                                          "preventDuplicates": false,
                                          "onclick": null,
                                          "showDuration": "300",
                                          "hideDuration": "1000",
										  "timeOut": "0",
										  "extendedTimeOut": "0",
                                          "showEasing": "swing",
                                          "hideEasing": "linear",
                                          "showMethod": "fadeIn",
                                          "hideMethod": "fadeOut",
                                          "rtl": '.(Application::Get('lang_dir') == 'rtl' ? 'true' : 'false').'
                                        }                        
                                        toastr.success("'.str_replace('"', '\\"', _MESSAGE_INFO_LINK_BOOKING).'");
                                      }
                                    }
                                  },
                                  open: function() {
                                    $(".ui-dialog-buttonpane").find("button:contains(\"Ok\")").css("float", "right");
                                    $(".ui-dialog-buttonset").css({"float": "left", "margin-left": "5px", "width":"100%"});
                                  }
                                });
                            }
                        }
                    });
                    return false;
                });
            });
          </script>
          <div id="dialog-alert" title="'.htmlspecialchars(_RESERVATION).'">
              <p><span id="dialog-alert-image" class="ui-icon ui-icon-alert" style="display:none;float:left; margin:2px 5px 0px 0;"></span><span id="dialog-message" style="font-size:14px;"></span></p>
          </div>';
    }
    
    $output .= '<br>';

    if($draw) echo $output;
    else return $output;
}

/** 
 * Show Reservation Cart on the screen
 */	
function Template_ShowReservationInfo($objReservation)
{
	global $objLogin, $objSettings;
	
	$align_left       = Application::Get('defined_left');
	$align_right      = Application::Get('defined_right');
	$class_left       = 'rc_'.Application::Get('defined_left');
	$class_right      = 'rc_'.Application::Get('defined_right');
	$allow_children   = ModulesSettings::Get('rooms', 'allow_children');
	$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
	$meal_plans_count = MealPlans::MealPlansCount();
	$total_adults     = 0;
	$total_children   = 0;
	$rooms_guests     = array();
	// Count all guests
	$all_adults_count   = 0;
	$arrReservation     = $objReservation->GetArrReservation();
	$lang               = Application::Get('lang');

    // prepare currency info
    if(Application::Get('currency_code') == ''){
        $currencyRate = '1';
    }else{
        //$default_currency_info = Currencies::GetDefaultCurrencyInfo();
        $currencyRate = Application::Get('currency_rate');
    }		

    $currencyFormat   = get_currency_format();		  
	$discount_info      = $objReservation->GetDiscountInfo();
	$discountCampaignID = $discount_info['discountCampaignID'];
	$discountCoupon     = $discount_info['discountCoupon'];
	$discountType       = $discount_info['discountType'];
    $discountValue      = $discount_info['discountValue'];
    
	$bookingInitialFee  = ModulesSettings::Get('booking', 'booking_initial_fee');
	$guestTax           = ModulesSettings::Get('booking', 'special_tax') * $currencyRate;
	$vatIncludedInPrice = ModulesSettings::Get('booking', 'vat_included_in_price');
	$vatPercent         = $objReservation->GetVatPercent();

	if(count($arrReservation) > 0){

		echo '<div class="table-responsive div-room-prices offset-1">';
		echo '<form id="update-room-form" action="index.php?page=booking" method="post">
		'.draw_hidden_field('act', 'update', false).'
		'.draw_hidden_field('room_id', '', false).'
		'.draw_hidden_field('from_date', '', false).'
		'.draw_hidden_field('to_date', '', false).'
		'.draw_token_field(false).'
		</form>';
		
		echo '<table class="reservation_cart responsive_table" width="100%" align="center"><thead>';
		echo '<tr class="header">				
			<th colspan="2" class="'.$class_left.'" width="30px">&nbsp;</th>
			<th align="'.$align_left.'">'._FLAT.'</th>
			<th align="center" width="60px">'._FROM.'</th>
			<th align="center" width="60px">'._TO.'</th>
			<th width="40px" align="center">&nbsp;'._NIGHTS.'&nbsp;</th>
			<th width="70px" colspan="3" align="center">'._OCCUPANCY.'</th>
			'.(($meal_plans_count) ? '<th width="60px" align="center">'._MEAL_PLANS.'</th>' : '<th style="padding:0px;">&nbsp;</th>').'
			<th width="65px" class="'.$class_right.'">'._PRICE.'</th>
		</tr>';

		echo '<tr style="font-size:10px;background-color:transparent;">				
			<th colspan="6"></th>
			<th align="center">'._ADULTS.'</th>
			'.(($allow_children == 'yes') ? '<th align="center">'._CHILDREN.'</th>' : '<th></th>').'
			'.(($allow_extra_beds == 'yes') ? '<th align="center">'._EXTRA_BED.'</th>' : '<th></th>').' 
			<th colspan="2"></th>
		</tr>';
		
		echo '</thead>
		<tr><td colspan="11" nowrap height="5px"></td></tr>';

		$order_price = 0;
		$min_nights = ModulesSettings::Get('booking', 'minimum_nights');
		$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
		$objRoom = new Rooms();
		$discount_night_value = 0;
		$max_rooms = 0;
		foreach($arrReservation as $key => $val)
		{
			$sql = 'SELECT
						'.TABLE_ROOMS.'.id,
						'.TABLE_ROOMS.'.hotel_id,
						'.TABLE_ROOMS.'.room_type,
						'.TABLE_ROOMS.'.room_icon_thumb,
						'.TABLE_ROOMS.'.max_adults,
						'.TABLE_ROOMS.'.max_children, 
						'.TABLE_ROOMS.'.default_price as price,
						'.TABLE_ROOMS.'.discount_guests_type,
						'.TABLE_ROOMS.'.discount_guests_3,
						'.TABLE_ROOMS.'.discount_guests_4,
						'.TABLE_ROOMS.'.discount_guests_5,
						'.TABLE_ROOMS.'.discount_night_type,
						'.TABLE_ROOMS.'.discount_night_3,
						'.TABLE_ROOMS.'.discount_night_4,
						'.TABLE_ROOMS.'.discount_night_5,
						'.TABLE_ROOMS_DESCRIPTION.'.room_type as loc_room_type,
						'.TABLE_ROOMS_DESCRIPTION.'.room_short_description as loc_room_short_description,
						'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name
					FROM '.TABLE_ROOMS.'
						INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.TABLE_ROOMS.'.id = '.TABLE_ROOMS_DESCRIPTION.'.room_id
						INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_ROOMS.'.hotel_id	= '.TABLE_HOTELS_DESCRIPTION.'.hotel_id
					WHERE
						'.TABLE_ROOMS.'.id = '.$key.' AND
						'.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.$lang.'\' AND
						'.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$lang.'\' ';
						
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){			
				$room_icon_thumb = ($result[0]['room_icon_thumb'] != '') ? $result[0]['room_icon_thumb'] : 'no_image.png';
				$room_price_w_meal_extrabeds = ($val['price'] + $val['meal_plan_price'] + $val['extra_beds_charge']);
                $meal_plan_info = MealPlans::GetPlanInfo($val['meal_plan_id']);
                $meal_plan_name = isset($meal_plan_info['name']) ? $meal_plan_info['name'] : '';

				if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
					$rooms_guests[$key] = array(
						'price' => $val['price'] / ($val['rooms'] * $val['nights']),
						'rooms_count' => $val['rooms'],
						'adults_count' => $val['adults'] * $val['rooms'],
						'nights' => $val['nights'],
						'discount_type' => $result[0]['discount_guests_type'],
						'discount_guests_3' => $result[0]['discount_guests_3'],
						'discount_guests_4' => $result[0]['discount_guests_4'],
						'discount_guests_5' => $result[0]['discount_guests_5'],
						'rooms_price' => $val['price'],
					);
					$all_adults_count += $val['adults'] * $val['rooms'];
					$max_rooms = $val['rooms'] > $max_rooms ? $val['rooms'] : $max_rooms;
				}
				
				$date_form = format_date($val['from_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', false);
				$date_to = format_date($val['to_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', false);
				echo '<tr>
						<td colspan="2" align="center"><a href="index.php?page=booking&act=remove&rid='.$key.'"><img src="images/remove.gif" width="16" height="16" border="0" title="'._REMOVE_ROOM_FROM_CART.'" alt="" /></a></td>							
						<td data-th="'.htmlspecialchars(_FLAT).'">
							'.prepare_link('flats', 'fid', $result[0]['hotel_id'], $result[0]['hotel_name'], $result[0]['hotel_name'], '', _CLICK_TO_VIEW).'
						</td>
						<td colspan="2" data-th="'.htmlspecialchars(_FROM.' - '._TO).'">
							<table class="subtable">
								<tr>
									<td align="center"><input class="form-control from_date" id="from_date_'.$result[0]['id'].'" data-id="'.$result[0]['id'].'" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$date_form.'"></td>
									<td align="center"><input class="form-control to_date" id="to_date_'.$result[0]['id'].'" data-id="'.$result[0]['id'].'" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$date_to.'"></td>
								</tr>
								<tr><td colspan="2" style="text-align:center;font-size:12px;"><a href="javascript:void(0);" data-id="'.$result[0]['id'].'" class="apply-change-date">'._APPLY_CHANGES.'</a></td></tr>
							</table>
						</td>
						<td align="center" data-th="'.htmlspecialchars(_NIGHTS).'">'.$val['nights'].'</td>
                        <td align="center" data-th="'.htmlspecialchars(_ADULTS).'">'.$val['adults'].'</td>
						'.(($allow_children == 'yes') ? '<td align="center" data-th="'.htmlspecialchars(_CHILDREN).'">'.$val['children'].'</td>' : '<td></td>').'
						'.(($allow_extra_beds == 'yes') ? '<td align="center" data-th="'.htmlspecialchars(_EXTRA_BEDS).'">'.$val['extra_beds'].'</td>' : '<td></td>').'
						'.(($meal_plans_count) ? ('<td align="center" style="cursor:help;" title="'.(($val['nights'] > 1) ? _RATE_PER_NIGHT_AVG : _RATE_PER_NIGHT.'" data-th="'.htmlspecialchars(_MEAL_PLANS).'">'.$meal_plan_name.'</td>')) : '<td></td>').'
						<td align="'.$align_right.'" data-th="'.htmlspecialchars(_PRICE).'">'.Currencies::PriceFormat($room_price_w_meal_extrabeds * $currencyRate, '', '', $currencyFormat).'&nbsp;<a class="price_link" href="javascript:void(0);" onclick="javascript:appToggleElement(\'row_prices_'.$key.'\')" title="'._CLICK_TO_SEE_PRICES.'">(+)</a></td>
					</tr>
					<tr><td colspan="11" nowrap height="1px"></td></tr>
					<tr><td colspan="11" align="'.$align_left.'">
							<span id="row_prices_'.$key.'" style="margin:5px 10px;display:none;">								
							<table width="100%">
							<tr>
								<td width="90px"><img src="images/rooms/'.$room_icon_thumb.'" alt="" width="80px" height="65px" /></td>
								<td>
								'._ROOM_PRICE.': '.Currencies::PriceFormat(($val['price'] * $currencyRate), '', '', $currencyFormat).'<br>
								'._MEAL_PLANS.': '.Currencies::PriceFormat(($val['meal_plan_price'] * $currencyRate), '', '', $currencyFormat).'<br>
								'._EXTRA_BEDS.': '.Currencies::PriceFormat(($val['extra_beds_charge'] * $currencyRate), '', '', $currencyFormat).'<br>
								'._RATE_PER_NIGHT.': '.Currencies::PriceFormat(($room_price_w_meal_extrabeds * $currencyRate) / $val['nights'], '', '', $currencyFormat).'<br>
								</td>
							</tr>
							<tr><td colspan="2">'.Rooms::GetRoomPricesTable($key).'</td></tr>
							</table>
							</span>
						</td>
					</tr>';
				echo '<script>
					jQuery(function() {
						var minDate = new Date();
						var maxDate = new Date();
						// min date + 1 day
						minDate.setTime(Date.parse("'.$val['from_date'].'") + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
						'.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
							? 'minDate.setFullYear(minDate.getFullYear() - 1);'
							: '').'
						jQuery("#from_date_'.$result[0]['id'].'").datepicker({
							'.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
								? 'defaultDate: "-1y",minDate: "-1y",'
								: 'minDate: 0,').'
							
							maxDate: "+'.(int)$search_availability_period.'y",
							numberOfMonths: 1,
							dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
						});
						jQuery("#to_date_'.$result[0]['id'].'").datepicker({
							'.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
								? 'defaultDate: "-1y",'
								: '').'
							minDate: minDate,
							maxDate: "+'.(int)$search_availability_period.'y +'.((int)$min_nights > 0 ? (int)$min_nights : '1').'d",
							numberOfMonths: 1,
							dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
						});
					});
				</script>';
				$order_price += ($room_price_w_meal_extrabeds * $currencyRate);					
				$total_adults += $val['rooms'] * $val['adults'];
				$total_children += ($allow_children == 'yes') ? $val['rooms'] * $val['children'] : 0;

                    // If has been ordered 3 and more nights, check an additional discount on the night
                    if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
                        if($val['nights'] >= 3){
                            $discount_value = 0;
                            if($val['nights'] == 3 && !empty($result[0]['discount_night_3'])){
                                $discount_value = $result[0]['discount_night_3'];
                            }elseif($val['nights'] == 4 && !empty($result[0]['discount_night_4'])){
                                $discount_value = $result[0]['discount_night_4'];
                            }else{
                                $discount_value = $result[0]['discount_night_5'];
                            }
    
                            // 0 - Fixed price, 1 - Percentage
                            if($result[0]['discount_night_type'] == 1){
                                $discount_nights = ($val['price'] * ($discount_value / 100));
                                if(!empty($rooms_guests[$key])){
                                    $rooms_guests[$key]['price'] -= $rooms_guests[$key]['price'] * ($discount_value / 100);
                                    $rooms_guests[$key]['rooms_price'] -= $rooms_guests[$key]['rooms_price'] * ($discount_value / 100);
                                }
                                
                            }else{
                                if($val['price'] > $discount_value){
                                    $discount_nights = ($val['price'] / ($val['rooms'] * $val['nights'])  > $discount_value ? $discount_value * $val['rooms'] * $val['nights'] : $val['price']);
                                    if(!empty($rooms_guests[$key])){
                                        $rooms_guests[$key]['price'] -= $discount_value;
                                        $rooms_guests[$key]['rooms_price'] -= $discount_nights;
                                    }
                                }else{
                                    $discount_nights = $val['price'];
                                    if(!empty($rooms_guests[$key])){
                                        $rooms_guests[$key]['price'] = 0;
                                        $rooms_guests[$key]['rooms_price'] = 0;
                                    }
                                }
                            }
                            // The discount can't exceed the cost per room
                            $discount_nights = $discount_nights > $val['price'] ? $val['price'] : $discount_nights;
                            $discount_night_value += $discount_nights * $currencyRate;
                            $arrReservation[$key]['discount_nights'] = $discount_nights;
                        }
                    }
			}
		}

		// draw sub-total row			
		echo '<tr>
				<td class="responsive_hidden" colspan="7"></td>
				<td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._SUBTOTAL.': </b></td>
				<td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(_SUBTOTAL).'"><b>'.Currencies::PriceFormat($order_price, '', '', $currencyFormat).'</b></td>
			</tr>';				

		echo '<tr><td colspan="11" nowrap="nowrap" height="15px"></td></tr>';

		if($discount_night_value > 0){
			$order_price -= $discount_night_value;
			echo '<tr>
					<td class="responsive_hidden" colspan="7"></td>
					<td class="td responsive_hidden '.$class_left.'" colspan="3"><b><span style="color:#a60000">'._LONG_TERM_STAY_DISCOUNT.': </span></b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(_LONG_TERM_STAY_DISCOUNT).'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_night_value, '', '', $currencyFormat).'</span></b></td>
				</tr>';				
		}

            if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
                $discount_value = 0;
                if(TYPE_DISCOUNT_GUEST == 'guests'){
                    $count_adults_or_guests = $all_adults_count;
                }else{
                    $count_adults_or_guests = $max_rooms;
                }
                if($count_adults_or_guests >= 3){
                    foreach($rooms_guests as $key => $info_room){
                        $discount_value = 0;
                        if(TYPE_DISCOUNT_GUEST != 'guests'){
                            $count_adults_or_guests = $info_room['rooms_count'];
                            if($count_adults_or_guests < 3){
                                continue;
                            }
                        }
                        if($count_adults_or_guests == 3 && !empty($info_room['discount_guests_3'])){
                            $discount_value = $info_room['discount_guests_3'];
                        }elseif($count_adults_or_guests == 4 && !empty($info_room['discount_guests_4'])){
                            $discount_value = $info_room['discount_guests_4'];
                        }else{
                            $discount_value = $info_room['discount_guests_5'];
                        }
    
                        // 0 - Fixed price, 1 - Percentage
                        if($info_room['discount_type'] == 1){
                            $discount_room = ($info_room['rooms_price'] * ($discount_value / 100));
                        }else{
                            $discount_room = ($info_room['price'] > $discount_value ? $discount_value : $info_room['price']) * $info_room['rooms_count'] * $info_room['nights'];
                        }
    
                        // The discount can't exceed the cost per room
                        $discount_room = $discount_room > $info_room['rooms_price'] ? $info_room['rooms_price'] : $discount_room;
                        $discount_value += $discount_room * $currencyRate;
                        $arrReservation[$key]['discount_guests'] = $discount_room;
                    }
                }

			if($discount_value > 0){
				$order_price -= $discount_value;
				echo '<tr>
						<td class="responsive_hidden" colspan="7"></td>
						<td class="td responsive_hidden '.$class_left.'" colspan="3"><b><span style="color:#a60000">'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).': </span></b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $currencyFormat).'</span></b></td>
					</tr>';				
			}
		}
		
		// calculate discount			
        $discount_value = ($discountType == '1' ? ($order_price * ($discountValue / 100)) : $discountValue);
		$order_price -= $discount_value;
		
		// calculate percent
		$vat_cost = (($order_price + $bookingInitialFee + ($guestTax * ($total_adults + $total_children))) * ($vatPercent / 100));
		$cart_total = ($order_price + $bookingInitialFee + ($guestTax * ($total_adults + $total_children))) + $vat_cost;

		if($discountCampaignID != '' || $discountCoupon != ''){
			echo '<tr>
					<td class="responsive_hidden" colspan="7"></td>
					<td class="td responsive_hidden '.$class_left.'" colspan="3"><b><span style="color:#a60000">'._COUPON_DISCOUNT.': (';
                    
                    // 0 - Fixed price, 1 - Percentage
                    if($discountType == 1){
                        echo Currencies::PriceFormat($discountValue, '%', 'after', $currencyFormat);
                    }else{
                        echo Currencies::PriceFormat($discountValue, Application::Get('currency_symbol'), 'before', $currencyFormat);
                    }
                    
                    echo ')</span></b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(_COUPON_DISCOUNT).'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $currencyFormat).'</span></b></td>
				</tr>';				
		}
		if(!empty($bookingInitialFee)){
			echo '<tr>
					<td class="responsive_hidden" colspan="7"></td>
					<td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._INITIAL_FEE.': </b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(_INITIAL_FEE).'"><b>'.Currencies::PriceFormat($bookingInitialFee, '', '', $currencyFormat).'</b></td>
				</tr>';								
		}
		if(!empty($guestTax)){
			echo '<tr>
					<td class="responsive_hidden" colspan="7"></td>
					<td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._GUEST_TAX.': </b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(_GUEST_TAX).'"><b>'.Currencies::PriceFormat($guestTax * ($total_adults + $total_children), '', '', $currencyFormat).'</b></td>
				</tr>';								
		}
		if($vatIncludedInPrice == 'no'){
			echo '<tr> 
					<td class="responsive_hidden" colspan="7"></td>
					<td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._VAT.': ('.Currencies::PriceFormat($vatPercent, '%', 'after', $currencyFormat, (substr($vatPercent, -1) == '0') ? 2 : 3).')</b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(_VAT).'"><b>'.Currencies::PriceFormat($vat_cost, '', '', $currencyFormat).'</b></td>
				</tr>';
		}
		echo '<tr><td colspan="11" nowrap height="5px"></td></tr>
			<tr class="tr-footer">
				<td class="responsive_hidden" colspan="7"></td>
				<td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._TOTAL.':</b></td>
				<td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(_TOTAL).'"><b>'.Currencies::PriceFormat($cart_total, '', '', $currencyFormat).'</b></td>
			</tr>
			<tr><td colspan="11" nowrap height="15px"></td></tr>
			<tr>
				<td class="responsive_hidden" colspan="7"></td>
				<td colspan="4" align="'.$align_left.'">
					<input type="button" class="form_button" onclick="javascript:appGoTo(\'page=booking_details\')" value="'._BOOK.'" />
				</td>
			</tr>
			</table>
			</div>';
			echo '<script>
			jQuery("a.apply-change-date").click(function(){
				form = jQuery("#update-room-form");
				var rid = jQuery(this).data("id");
				var checkin_val = jQuery("input.from_date[data-id=" + rid + "]").val();
				var checkout_val = jQuery("input.to_date[data-id=" + rid + "]").val();

				form.find("input[name=room_id]").val(rid);
				form.find("input[name=from_date]").val(checkin_val);
				form.find("input[name=to_date]").val(checkout_val);
				form.submit();
			});
			jQuery("input.from_date").change(function(){
				form = jQuery("#update-room-form");
				var rid = jQuery(this).data("id");
				var checkin_val = jQuery(this).val();
				'.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
					// make american format
					? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
					: '').'
				var set_date = new Date();

				// checkin_val + 1d
				set_date.setTime(Date.parse(checkin_val) + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
				jQuery("input.to_date[data-id=" + rid + "]").datepicker("option", "minDate", set_date);
			});
			jQuery("input.to_date").change(function(){
				form = jQuery("#update-room-form");
				var rid = jQuery(this).data("id");
				var checkout_val = jQuery(this).val();
				'.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
					// make american format
					? 'checkout_val = checkout_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
					: '').'
				var set_date = new Date();

				// checkout_val - 1d
				set_date.setTime(Date.parse(checkout_val) - ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
				jQuery("input.from_date[data-id=" + rid + "]").datepicker("option", "maxDate", set_date);
			});
			</script>';
	}else{
		draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
	}
}

/** 
 * Show checkout info
 */	
function Template_ShowCheckoutInfo($objReservation)
{
    global $objLogin, $objSettings;
    
    $lang = Application::Get('lang');
    
    $class_left = 'rc_'.Application::Get('defined_left');
    $class_right = 'rc_'.Application::Get('defined_right');
    
    $first_night_possible   = false;
    $default_payment_system = ModulesSettings::Get('booking', 'default_payment_system');		
    $pre_payment_type       = ModulesSettings::Get('booking', 'pre_payment_type');
    $pre_payment_type_post  = isset($_POST['pre_payment_type']) ? prepare_input($_POST['pre_payment_type']) : $pre_payment_type;
    $pre_payment_value      = ModulesSettings::Get('booking', 'pre_payment_value');
    $payment_type_poa       = ModulesSettings::Get('booking', 'payment_type_poa');
    $payment_type_online    = ModulesSettings::Get('booking', 'payment_type_online');
    $payment_type_bank_transfer = ModulesSettings::Get('booking', 'payment_type_bank_transfer');
    $payment_type_paypal    = ModulesSettings::Get('booking', 'payment_type_paypal');
    $payment_type_2co       = ModulesSettings::Get('booking', 'payment_type_2co');
    $payment_type_authorize = ModulesSettings::Get('booking', 'payment_type_authorize');
    $payment_type           = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : $default_payment_system;
    $submition_type         = isset($_POST['submition_type']) ? prepare_input($_POST['submition_type']) : '';
    $is_agency 				= $objLogin->GetCustomerType() == 1 ? true : false;
    $allow_payment_with_balance = ModulesSettings::Get('booking', 'allow_payment_with_balance') == 'yes' ? true : false;
	$arrReservation         = $objReservation->GetArrReservation();
	$lang                   = Application::Get('lang');

    // prepare currency info
    if(Application::Get('currency_code') == ''){
        $currencyRate = '1';
    }else{
        //$default_currency_info = Currencies::GetDefaultCurrencyInfo();
        $currencyRate = Application::Get('currency_rate');
    }		
    // preapre datetime format
    if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
        $fieldDateFormat = 'M d, Y';
    }else{
        $fieldDateFormat = 'd M, Y';
    }

    $currencyFormat   = get_currency_format();		  
	$discount_info      = $objReservation->GetDiscountInfo();
	$discountCampaignID = $discount_info['discountCampaignID'];
	$discountCoupon     = $discount_info['discountCoupon'];
	$discountType       = $discount_info['discountType'];
    $discountValue      = $discount_info['discountValue'];
	$bookingInitialFee  = ModulesSettings::Get('booking', 'booking_initial_fee');
	$guestTax           = ModulesSettings::Get('booking', 'special_tax') * $currencyRate;
	$vatIncludedInPrice = ModulesSettings::Get('booking', 'vat_included_in_price');
	$vatPercent         = $objReservation->GetVatPercent();

    $payment_type_poa 		    = ($payment_type_poa == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_poa == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_poa == 'Frontend Only')) ? 'yes' : 'no';
    $payment_type_online 		= ($payment_type_online == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_online == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_online == 'Frontend Only')) ? 'yes' : 'no';
    $payment_type_bank_transfer = ($payment_type_bank_transfer == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_bank_transfer == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_bank_transfer == 'Frontend Only')) ? 'yes' : 'no';
    $payment_type_paypal 		= ($payment_type_paypal == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Frontend Only')) ? 'yes' : 'no';
    $payment_type_2co 			= ($payment_type_2co == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_2co == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_2co == 'Frontend Only')) ? 'yes' : 'no';
    $payment_type_authorize 	= ($payment_type_authorize == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_authorize == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_authorize == 'Frontend Only')) ? 'yes' : 'no';
    $payment_type_balance		= (($is_agency || $objLogin->IsLoggedInAsAdmin()) && $allow_payment_with_balance) ? 'yes' : 'no';

    if(ModulesSettings::Get('booking', 'allow_separate_gateways') == 'yes'){
        $hotel_id = $objReservation->GetReservationHotelId();
        $sql = "SELECT * FROM ".TABLE_HOTEL_PAYMENT_GATEWAYS." WHERE hotel_id = ".(int)$hotel_id.' AND is_active = 0';
        $info = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        if($info[1] > 0){
            $arr_payment_types = array('poa'=>'payment_type_poa', 'online'=>'payment_type_online', 'paypal'=>'payment_type_paypal', '2co'=>'payment_type_2co', 'authorize.net'=>'payment_type_authorize', 'bank.transfer'=>'payment_type_bank_transfer', 'account.balance'=>'payment_type_balance');
            for($i = 0; $i < $info[1]; $i++){
                $tmp_payment_type = $arr_payment_types[$info[0][$i]['payment_type']];
                $$tmp_payment_type = 'no';
            }
        }
    }

    $payment_type_cnt = (($payment_type_poa === 'yes')+
                        ($payment_type_online === 'yes')+
                        ($payment_type_bank_transfer === 'yes')+
                        ($payment_type_paypal === 'yes')+
                        ($payment_type_2co === 'yes')+
                        ($payment_type_authorize === 'yes')+
                        ($payment_type_balance === 'yes'));
    $payment_types_defined  = true;
    $allow_children = ModulesSettings::Get('rooms', 'allow_children');
    $allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
    $meal_plans_count = MealPlans::MealPlansCount();
    
    $find_user = isset($_GET['cl']) ? prepare_input($_GET['cl']) : '';
    $cid = isset($_GET['cid']) ? prepare_input($_GET['cid']) : '';
    if($cid != ''){
        if($cid != 'admin'){
            $currentCustomerID = $cid;
            Session::Set('sel_current_customer_id', $cid);
            $selectedUser = 'customer';			
        }else{
            $currentCustomerID = $objLogin->GetLoggedID();
            $selectedUser = 'admin';
            Session::Set('sel_current_customer_id', '');
        }
    }else{
		if(!$objLogin->IsLoggedIn()){
			$currentCustomerID = (int)Session::Get('current_customer_id');
			$selectedUser = 'customer';
		}elseif($objLogin->IsLoggedInAsCustomer()){
			$currentCustomerID = $objLogin->GetLoggedID();
			$selectedUser = 'customer';			
		}else{
			if(Session::IsExists('sel_current_customer_id') && (int)Session::Get('sel_current_customer_id') != 0){
				$currentCustomerID = (int)Session::Get('sel_current_customer_id');
				$selectedUser = 'customer';
			}else{
				$currentCustomerID = $objLogin->GetLoggedID();
				$selectedUser = 'admin';
			}
		}
    }
                        
    if($objLogin->IsLoggedInAsAdmin() && $selectedUser == 'admin'){
        $table_name = TABLE_ACCOUNTS;
        $sql='SELECT '.$table_name.'.*
              FROM '.$table_name.'
              WHERE '.$table_name.'.id = '.(int)$currentCustomerID;
    }else{
        $table_name = TABLE_CUSTOMERS;
        $sql = 'SELECT
                '.$table_name.'.*,
                cd.name as country_name,
                c.vat_value,
                IF(st.name IS NOT NULL, st.name, '.$table_name.'.b_state) as b_state
            FROM '.$table_name.'
                LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.$table_name.'.b_country = c.abbrv AND c.is_active = 1
                LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
                LEFT OUTER JOIN '.TABLE_STATES.' st ON '.$table_name.'.b_state = st.abbrv AND st.country_id = c.id AND st.is_active = 1
            WHERE '.$table_name.'.id = '.(int)$currentCustomerID;				  
    }
    $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
    if($result[1] <= 0){
        draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
        return false;
    }

    if(count($arrReservation) > 0){
        $hotel_id = 0;
        foreach($arrReservation as $key => $val){
            if($hotel_id == 0){
                $hotel_id = $val['hotel_id'];
            }elseif($hotel_id != $val['hotel_id']){
                $hotel_id = 0;
                break;
            }
        }
        if($hotel_id == 0){
            $extras = Extras::GetAllExtras();
        }else{
            $extras = Extras::GetAllExtras(array(0, $hotel_id));
        }
        echo "\n".'<script type="text/javascript">'."\n";			
        echo 'var arrExtras = new Array('.$extras[1].');'."\n";
        echo 'var arrExtrasSelected = new Array('.$extras[1].');'."\n";			
        if($extras[1]){
            for($i=0; $i<$extras[1]; $i++){
                echo 'arrExtras['.$i.'] = "'.($extras[0][$i]['price'] * $currencyRate).'";'."\n";
                echo 'arrExtrasSelected['.$i.'] = 0;'."\n";
            }
        }						
        echo '</script>'."\n";

        echo '<form id="update-room-form" action="index.php?page=booking_checkout&m=3" method="post">
        '.draw_hidden_field('submition_type', 'update', false).'
        '.draw_hidden_field('room_id', '', false).'
        '.draw_hidden_field('from_date', '', false).'
        '.draw_hidden_field('to_date', '', false).'
        '.draw_token_field(false).'
        </form>';
        
        echo '<form id="checkout-form" action="index.php?page=booking_payment" method="post">
        '.draw_hidden_field('task', 'do_booking', false).'
        '.draw_hidden_field('submition_type', '', false).'
        '.draw_hidden_field('selected_user', $selectedUser, false).'
        '.draw_token_field(false);
        
        echo '<table class="reservation_cart" width="99%" align="center">
        <tr>
            <td colspan="2"><h4>'._BILLING_DETAILS.' &nbsp;';
                if($objLogin->IsLoggedIn()){
                    if($objLogin->IsLoggedInAsCustomer()){
                        echo '<a style="font-size:13px;" href="javascript:void(0);" onclick="javascript:appGoTo(\'customer=my_account\')">['._EDIT_WORD.']</a>	';
                    }elseif($objLogin->IsLoggedInAsAdmin()){
                    
                        echo '<br>'._CHANGE_CUSTOMER.': 
                        <input type="text" id="find_user" name="find_user" value="" size="10" maxlength="40" />
                        <input type="button" class="form_button" value="'._SEARCH.'" onclick="javascript:appGoTo(\'page=booking_checkout&cl=\'+jQuery(\'#find_user\').val())" />
                        <select name="sel_customer" id="sel_customer">';
                            if($find_user == ''){
                                if($selectedUser == 'admin'){
                                    echo '<option value="admin">'.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.$result[0]['user_name'].')</option>';										
                                }else{
                                    echo '<option value="'.$result[0]['id'].'">ID:'.$result[0]['id'].' '.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.(($result[0]['user_name'] != '') ? $result[0]['user_name'] : _WITHOUT_ACCOUNT).')'.'</option>';										
                                }
                            }else{
                                $objCustomers = new Customers();
                                $result_customers = $objCustomers->GetAllCustomers(' AND (last_name like \''.$find_user.'%\' OR first_name like \''.$find_user.'%\' OR user_name like \''.$find_user.'%\') ');
                                if($result_customers[1] > 0){
                                    for($i = 0; $i < $result_customers[1]; $i++){
                                        echo '<option value="'.$result_customers[0][$i]['id'].'">ID:'.$result_customers[0][$i]['id'].' '.$result_customers[0][$i]['first_name'].' '.$result_customers[0][$i]['last_name'].' ('.(($result_customers[0][$i]['user_name'] != '') ? $result_customers[0][$i]['user_name'] : _WITHOUT_ACCOUNT).')'.'</option>';
                                    }								
                                }else{
                                    echo '<option value="admin">'.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.$result[0]['user_name'].')</option>';
                                }
                            }								
                        echo '</select> ';
                        if($find_user != '') echo '<input type="button" class="form_button" value="'._APPLY.'" onclick="javascript:appGoTo(\'page=booking_checkout&cid=\'+jQuery(\'#sel_customer\').val())"/> ';
                        echo '<input type="button" class="form_button" value="'._SET_ADMIN.'" onclick="javascript:appGoTo(\'page=booking_checkout&cid=admin\')"/>';
                        if($find_user != '' && $result_customers[1] == 0) echo ' '._NO_CUSTOMER_FOUND;
                    }
                }else{
                    echo '<a style="font-size:13px;" href="javascript:void(0);" onclick="javascript:appGoTo(\'page=booking_details\',\'&m=edit\')">['._EDIT_WORD.']</a>	';
                }
                echo '</h4>
            </td>
        </tr>
        <tr>
            <td style="padding-left:10px;">
                '._FIRST_NAME.': '.$result[0]['first_name'].'<br />
                '._LAST_NAME.': '.$result[0]['last_name'].'<br />';				
                if(!$objLogin->IsLoggedInAsAdmin()){					
                    echo _ADDRESS.': '.$result[0]['b_address'].'<br />';
                    echo _ADDRESS_2.': '.$result[0]['b_address_2'].'<br />';
                    echo _CITY.': '.$result[0]['b_city'].'<br />';
                    echo _ZIP_CODE.': '.$result[0]['b_zipcode'].'<br />';
                    echo _COUNTRY.': '.$result[0]['country_name'].'<br />';
                    echo _STATE.': '.$result[0]['b_state'].'<br />';
                }				
            echo '</td>
            <td></td>
        </tr>
        </table><br />';

        echo '<table class="reservation_cart responsive_table" width="99%" align="center">
        <tr><td colspan="10"><h4>'._RESERVATION_DETAILS.'</h4></td></tr>
        <tr class="header">
            <th colspan="2" class="'.$class_left.'" width="40px">&nbsp;</th>
            <th align="'.$class_left.'">'._FLAT.'</th>
            <th align="center">'._FROM.'</th>
            <th align="center">'._TO.'</th>
            <th width="60px" align="center">'._NIGHTS.'</th>								
            <th width="70px" colspan="3" align="center">'._OCCUPANCY.'</th>
            '.(($meal_plans_count) ? '<th width="60px" align="center">'._MEAL_PLANS.'</th>' : '<th style="padding:0px;">&nbsp;</th>').'
            <th class="'.$class_right.'" width="80px" align="'.$class_right.'">'._PRICE.'</th>
        </tr>
        <tr><td colspan="11" nowrap="nowrap" height="5px"></td></tr>';

        echo '<tr style="font-size:10px;background-color:transparent;">				
            <th colspan="6"></th>
            <th align="center">'._ADULTS.'</th>
            '.(($allow_children == 'yes') ? '<th align="center">'._CHILDREN.'</th>' : '<th></th>').'
            '.(($allow_extra_beds == 'yes') ? '<th align="center">'._EXTRA_BEDS.'</th>' : '<th></th>').' 
            <th colspan="2"></th>
        </tr>';
        
        $order_price = 0;
        $total_adults = 0;
        $total_children = 0;
        $rooms_guests = array();
        // Count all guests
        $all_adults_count = 0;
        $max_rooms = 0;
        $discount_night_value = 0;
        $min_nights = ModulesSettings::Get('booking', 'minimum_nights');
        $search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
        foreach($arrReservation as $key => $val){
            if($val['nights'] > 1) $first_night_possible = true;
            $sql = 'SELECT
                        '.TABLE_ROOMS.'.id,
                        '.TABLE_ROOMS.'.room_type,
                        '.TABLE_ROOMS.'.room_icon_thumb,
                        '.TABLE_ROOMS.'.hotel_id,
                        '.TABLE_ROOMS.'.max_adults,
                        '.TABLE_ROOMS.'.max_children, 
                        '.TABLE_ROOMS.'.default_price as price,
                        '.TABLE_ROOMS.'.discount_guests_type,
                        '.TABLE_ROOMS.'.discount_guests_3,
                        '.TABLE_ROOMS.'.discount_guests_4,
                        '.TABLE_ROOMS.'.discount_guests_5,
                        '.TABLE_ROOMS.'.discount_night_type,
                        '.TABLE_ROOMS.'.discount_night_3,
                        '.TABLE_ROOMS.'.discount_night_4,
                        '.TABLE_ROOMS.'.discount_night_5,
                        '.TABLE_ROOMS_DESCRIPTION.'.room_type as loc_room_type,
                        '.TABLE_ROOMS_DESCRIPTION.'.room_short_description as loc_room_short_description,
                        '.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name
                    FROM '.TABLE_ROOMS.'
                        INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.TABLE_ROOMS.'.id = '.TABLE_ROOMS_DESCRIPTION.'.room_id
                        INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_ROOMS.'.hotel_id	= '.TABLE_HOTELS_DESCRIPTION.'.hotel_id
                    WHERE
                        '.TABLE_ROOMS.'.id = '.(int)$key.' AND
                        '.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.$lang.'\' AND
                        '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$lang.'\' ';

            $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
            if($result[1] > 0){
                $room_icon_thumb = ($result[0]['room_icon_thumb'] != '') ? $result[0]['room_icon_thumb'] : 'no_image.png';
                $room_price_w_meal_extrabeds = ($val['price'] + $val['meal_plan_price'] + $val['extra_beds_charge']);
                $meal_plan_info = MealPlans::GetPlanInfo($val['meal_plan_id']);
                $meal_plan_name = isset($meal_plan_info['name']) ? $meal_plan_info['name'] : '';

                if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
                    $rooms_guests[$key] = array(
                        'price' => $val['price'] / ($val['rooms'] * $val['nights']),
                        'rooms_count' => $val['rooms'],
                        'adults_count' => $val['adults'] * $val['rooms'],
                        'nights' => $val['nights'],
                        'discount_type' => $result[0]['discount_guests_type'],
                        'discount_guests_3' => $result[0]['discount_guests_3'],
                        'discount_guests_4' => $result[0]['discount_guests_4'],
                        'discount_guests_5' => $result[0]['discount_guests_5'],
                        'rooms_price' => $val['price'],
                    );
                    $all_adults_count += $val['adults'] * $val['rooms'];
                    $max_rooms = $val['rooms'] > $max_rooms ? $val['rooms'] : $max_rooms;
                }
                $date_form = format_date($val['from_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', true);
                $date_to = format_date($val['to_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', true);
                echo '<tr>
                        <td colspan="2"><img src="images/rooms/'.$room_icon_thumb.'" alt="icon" width="32px" height="32px" /></td>							
                        <td data-th="'.htmlspecialchars(_FLAT).'">
                            '.prepare_link('flats', 'fid', $result[0]['hotel_id'], $result[0]['hotel_name'], $result[0]['hotel_name'], '', _CLICK_TO_VIEW).'
                        </td>							
                        <td colspan="2" data-th="'.htmlspecialchars(_FROM.' - '._TO).'">
                            <table class="subtable">
                                <tr>
                                    <td align="center"><input class="form-control from_date" id="from_date_'.$result[0]['id'].'" data-id="'.$result[0]['id'].'" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$date_form.'"></td>
                                    <td align="center"><input class="form-control to_date" id="to_date_'.$result[0]['id'].'" data-id="'.$result[0]['id'].'" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" value="'.$date_to.'"></td>
                                </tr>
                                <tr><td colspan="2" style="text-align:center;font-size:12px;"><a href="javascript:void(0);" data-id="'.$result[0]['id'].'" class="apply-change-date">'._APPLY_CHANGES.'</a></td></tr>
                            </table>
                        </td>
                        <td align="center" data-th="'.htmlspecialchars(_NIGHTS).'">'.$val['nights'].'</td>
                        <td align="center" data-th="'.htmlspecialchars(_ADULTS).'">'.$val['adults'].'</td>
                        '.(($allow_children == 'yes') ? '<td align="center" data-th="'.htmlspecialchars(_CHILDREN).'">'.$val['children'].'</td>' : '<td></td>').'
                        '.(($allow_extra_beds == 'yes') ? '<td align="center" data-th="'.htmlspecialchars(_EXTRA_BEDS).'">'.$val['extra_beds'].'</td>' : '<td></td>').'
                        '.(($meal_plans_count) ? '<td align="center" data-th="'.htmlspecialchars(_MEAL_PLANS).'">'.$meal_plan_name.'</td>' : '<td></td>').'
                        <td class="'.$class_right.'" data-th="'.htmlspecialchars(_PRICE).'">'.Currencies::PriceFormat($room_price_w_meal_extrabeds * $currencyRate, '', '', $currencyFormat).'&nbsp;</td>
                    </tr>
                    <tr><td colspan="11" nowrap height="3px"></td></tr>';
                echo '<script>
                    jQuery(function() {
                        var minDate = new Date();
                        var maxDate = new Date();
                        // min date + 1 day
                        minDate.setTime(Date.parse("'.$val['from_date'].'") + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
                        '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                            ? 'minDate.setFullYear(minDate.getFullYear() - 1);'
                            : '').'
                        jQuery("#from_date_'.$result[0]['id'].'").datepicker({
                            '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                                ? 'defaultDate: "-1y",minDate: "-1y",'
                                : 'minDate: 0,').'
                            maxDate: "+'.(int)$search_availability_period.'y",
                            numberOfMonths: 1,
                            dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
                        });
                        jQuery("#to_date_'.$result[0]['id'].'").datepicker({
                            minDate: minDate,
                            maxDate: "+'.(int)$search_availability_period.'y +'.((int)$min_nights > 0 ? (int)$min_nights : '1').'d",
                            numberOfMonths: 1,
                            dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
                        });
                    });
                </script>';
                $order_price += ($room_price_w_meal_extrabeds * $currencyRate);
                $total_adults += $val['rooms'] * $val['adults'];
                $total_children += ($allow_children == 'yes') ? $val['rooms'] * $val['children'] : 0;

                // If has been ordered 3 and more nights, check an additional discount on the night
                if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
                    if($val['nights'] >= 3){
                        $discount_value = 0;
                        if($val['nights'] == 3 && !empty($result[0]['discount_night_3'])){
                            $discount_value = $result[0]['discount_night_3'];
                        }elseif($val['nights'] == 4 && !empty($result[0]['discount_night_4'])){
                            $discount_value = $result[0]['discount_night_4'];
                        }else{
                            $discount_value = $result[0]['discount_night_5'];
                        }

                        // 0 - Fixed price, 1 - Percentage
                        if($result[0]['discount_night_type'] == 1){
                            $discount_nights = ($val['price'] * ($discount_value / 100));
                            $rooms_guests[$key]['price'] -= $rooms_guests[$key]['price'] * ($discount_value / 100);
                            $rooms_guests[$key]['rooms_price'] -= $rooms_guests[$key]['rooms_price'] * ($discount_value / 100);
                            
                        }else{
                            if($val['price'] > $discount_value){
                                $discount_nights = ($val['price'] / ($val['rooms'] * $val['nights'])  > $discount_value ? $discount_value * $val['rooms'] * $val['nights'] : $val['price']);
                                $rooms_guests[$key]['price'] -= $discount_value;
                                $rooms_guests[$key]['rooms_price'] -= $discount_nights;
                            }else{
                                $discount_nights = $val['price'];
                                $rooms_guests[$key]['price'] = 0;
                                $rooms_guests[$key]['rooms_price'] = 0;
                            }
                        }
                        // The discount can't exceed the cost per room
                        $discount_nights = $discount_nights > $val['price'] ? $val['price'] : $discount_nights;
                        $discount_night_value += $discount_nights * $currencyRate;
                        $arrReservation[$key]['discount_nights'] = $discount_nights;
                    }
                }
            }
        }
        
        // draw sub-total row			
        echo '<tr>
                <td class="responsive_hidden" colspan="7"></td>
                <td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._SUBTOTAL.':</b></td>
                <td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlspecialchars(_SUBTOTAL).'">
                    <b>'.Currencies::PriceFormat($order_price, '', '', $currencyFormat).'</b>
                </td>
             </tr>';

        //echo '<tr><td colspan="10" nowrap height="5px"></td></tr>';
        
        // EXTRAS
        // ------------------------------------------------------------
        if($extras[1]){
            echo '<tr><td colspan="11" nowrap height="10px"></td></tr>';
            echo '<tr><td colspan="11" ><h4>'._EXTRAS.'</h4></td></tr>';				
            echo '<tr><td colspan="11" class="no_title"><table class="subtable" width="340px">';				
            for($i=0; $i<$extras[1]; $i++){
                $extras_id = (($submition_type == 'apply_coupon') && isset($_POST['extras_'.$extras[0][$i]['id']])) ? $_POST['extras_'.$extras[0][$i]['id']] : 0;
                echo '<tr>';
                echo '<td wrap="wrap">'.$extras[0][$i]['name'].' <span class="help" title="'.$extras[0][$i]['description'].'">[?]</span></td>';
                echo '<td>&nbsp;</td>';
                echo '<td align="right">'.Currencies::PriceFormat($extras[0][$i]['price'] * $currencyRate, '', '', $currencyFormat).'</td>';
                echo '<td>&nbsp;</td>';
                echo '<td>'.draw_numbers_select_field('extras_'.$extras[0][$i]['id'], $extras_id, '0', $extras[0][$i]['maximum_count'], 1, 'extras_ddl form-control', 'onchange="appUpdateTotalSum('.$i.',this.value,'.(int)$extras[1].')"', false).'</td>';
                echo '</tr>';
            }
            echo '</table></td></tr>';								
        }
        
        echo '<script>
        jQuery("a.apply-change-date").click(function(){
            form = jQuery("#update-room-form");
            var rid = jQuery(this).data("id");
            var checkin_val = jQuery("input.from_date[data-id=" + rid + "]").val();
            var checkout_val = jQuery("input.to_date[data-id=" + rid + "]").val();

            form.find("input[name=room_id]").val(rid);
            form.find("input[name=from_date]").val(checkin_val);
            form.find("input[name=to_date]").val(checkout_val);
            form.submit();
        });
        jQuery("input.from_date").change(function(){
            form = jQuery("#update-room-form");
            var rid = jQuery(this).data("id");
            var checkin_val = jQuery(this).val();
            '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
                // make american format
                ? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
                : '').'
            var set_date = new Date();

            // checkin_val + 1d
            set_date.setTime(Date.parse(checkin_val) + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
            jQuery("input.to_date[data-id=" + rid + "]").datepicker("option", "minDate", set_date);
        });
        jQuery("input.to_date").change(function(){
            form = jQuery("#update-room-form");
            var rid = jQuery(this).data("id");
            var checkout_val = jQuery(this).val();
            '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
                // make american format
                ? 'checkout_val = checkout_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
                : '').'
            var set_date = new Date();

            // checkout_val - 1d
            set_date.setTime(Date.parse(checkout_val) - ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
            jQuery("input.from_date[data-id=" + rid + "]").datepicker("option", "maxDate", set_date);
        });
        </script>';

        if($discount_night_value > 0){
            $align_right = Application::Get('defined_right');
            $order_price -= $discount_night_value;
            echo '<tr>
                    <td class="responsive_hidden" colspan="7"></td>
                    <td class="td responsive_hidden '.$class_left.'" colspan="3"><b><span style="color:#a60000">'._LONG_TERM_STAY_DISCOUNT.': </span></b></td>
                    <td class="td '.$class_right.'" align="'.$align_right.'" data-th="'.htmlspecialchars(_LONG_TERM_STAY_DISCOUNT).'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_night_value, '', '', $currencyFormat).'</span></b></td>
                </tr>';				
        }

        // If has been ordered 3 and more rooms, check an additional discount on the number of rooms
        if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
            $discount_value = 0;
            if(TYPE_DISCOUNT_GUEST == 'guests'){
                $count_adults_or_guests = $all_adults_count;
            }else{
                $count_adults_or_guests = $max_rooms;
            }
            if($count_adults_or_guests >= 3){
                foreach($rooms_guests as $info_room){
                    if(TYPE_DISCOUNT_GUEST != 'guests'){
                        $count_adults_or_guests = $info_room['rooms_count'];
                    }
                    $discount_value = 0;
                    if($count_adults_or_guests == 3 && !empty($info_room['discount_guests_3'])){
                        $discount_value = $info_room['discount_guests_3'];
                    }elseif($count_adults_or_guests == 4 && !empty($info_room['discount_guests_4'])){
                        $discount_value = $info_room['discount_guests_4'];
                    }else{
                        $discount_value = $info_room['discount_guests_5'];
                    }

                    // 0 - Fixed price, 1 - Percentage
                    if($info_room['discount_type'] == 1){
                        $discount_room = ($info_room['rooms_price'] * ($discount_value / 100));
                    }else{
                        $discount_room = ($info_room['price'] > $discount_value ? $discount_value : $info_room['price']) * $info_room['rooms_count'] * $info_room['nights'];
                    }
                    // The discount can't exceed the cost per room
                    $discount_room = $discount_room > $info_room['rooms_price'] ? $info_room['rooms_price'] : $discount_room;
                    $discount_value += $discount_room * $currencyRate;
                    $arrReservation[$key]['discount_guests'] = $discount_room;
                }
            }

            if($discount_value > 0){
                $order_price -= $discount_value;
                echo '<tr>
                        <td class="responsive_hidden" colspan="7"></td>
                        <td class="td responsive_hidden '.$class_left.'" colspan="3"><b><span style="color:#a60000">'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).': </span></b></td>
                        <td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlspecialchars(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $currencyFormat).'</span></b></td>
                    </tr>';				
            }
        }
        
        // Calculate discount
        $discount_value = ($discountType == '1' ? ($order_price * ($discountValue / 100)) : $discountValue);
        $order_price -= $discount_value;
        
        // calculate percent
        $vat_cost = (($order_price + $bookingInitialFee + ($guestTax * ($total_adults + $total_children))) * ($vatPercent / 100));
        $cart_total = ($order_price + $bookingInitialFee + ($guestTax * ($total_adults + $total_children))) + $vat_cost;

        if($discountCampaignID != '' || $discountCoupon != ''){
            echo '<tr>
                    <td class="responsive_hidden" colspan="7"></td>
                    <td class="td responsive_hidden '.$class_left.'" colspan="3"><b><span style="color:#a60000">'._COUPON_DISCOUNT.': (';

                    // 0 - Fixed price, 1 - Percentage
                    if($discountType == 1){
                        echo Currencies::PriceFormat($discountValue, '%', 'after', $currencyFormat);
                    }else{
                        echo Currencies::PriceFormat($discountValue, Application::Get('currency_symbol'), 'before', $currencyFormat);
                    }
                    
                    echo ')</span></b></td>
                    <td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlspecialchars(_COUPON_DISCOUNT).'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $currencyFormat).'</span></b></td>
                </tr>';				
        }
        if(!empty($bookingInitialFee)){
            echo '<tr>
                    <td class="responsive_hidden" colspan="7"></td>
                    <td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._INITIAL_FEE.': </b></td>
                    <td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlspecialchars(_INITIAL_FEE).'"><b>'.Currencies::PriceFormat($bookingInitialFee, '', '', $currencyFormat).'</b></td>
                </tr>';								
        }
        if(!empty($guestTax)){
            echo '<tr>
                    <td class="responsive_hidden" colspan="7"></td>
                    <td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._GUEST_TAX.': </b></td>
                    <td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlspecialchars(_GUEST_TAX).'">
                        <b><label id="guest_tax" data-price="'.(float)($guestTax * ($total_adults + $total_children)).'">'.Currencies::PriceFormat(($guestTax * ($total_adults + $total_children)), '', '', $currencyFormat).'</label></b>
                    </td>
                </tr>';								
        }
        if($vatIncludedInPrice == 'no'){
            echo '<tr>
                    <td class="responsive_hidden" colspan="7"></td>
                    <td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._VAT.': ('.Currencies::PriceFormat($vatPercent, '%', 'after', $currencyFormat, (substr($vatPercent, -1) == '0') ? 2 : 3).')</b></td>
                    <td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlspecialchars(_VAT).'">
                        <b><label id="reservation_vat">'.Currencies::PriceFormat($vat_cost, '', '', $currencyFormat).'</label></b>
                    </td>
                 </tr>';
        }
        echo '<tr><td colspan="11" nowrap height="5px"></td></tr>
             <tr class="tr-footer">
                <td class="responsive_hidden" colspan="7"></td>
                <td class="td responsive_hidden '.$class_left.'" colspan="3"><b>'._TOTAL.':</b></td>
                <td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlspecialchars(_TOTAL).'">
                    <b><label id="reservation_total">'.Currencies::PriceFormat($cart_total, '', '', $currencyFormat).'</label></b>
                </td>
             </tr>';

        // PAYMENT DETAILS
        // ------------------------------------------------------------
        echo '<tr><td colspan="11" nowrap height="12px"></td></tr>';
        echo '<tr><td colspan="11"><h4>'._PAYMENT_DETAILS.'</h4></td></tr>';
        echo '<tr><td colspan="11">';
        echo '<table border="0" width="100%">';
            if($payment_type_cnt > 1){
                if(SHOW_PAYMENT_FOR_CHECKBOX){
                    echo '<tr><td width="130px" style="vertical-align:top;">'._PAYMENT_TYPE.': &nbsp;</td><td>';
                    if($payment_type_poa == 'yes')			 echo '<input type="radio" name="payment_type" id="payment_type_poa" value="poa" '.(($payment_type == 'poa') ? 'checked="checked"' : '').'/> <label for="payment_type_poa">'.(file_exists('images/payments/poa.png') ? '<img src="images/payments/poa.png" alt="'._PAY_ON_ARRIVAL.'" title="'._PAY_ON_ARRIVAL.'" style="margin-left:10px;height:35px;">' : _PAY_ON_ARRIVAL).'</label><br><br>';
                    if($payment_type_online == 'yes')		 echo '<input type="radio" name="payment_type" id="payment_type_online" value="online" '.(($payment_type == 'online') ? 'checked="checked"' : '').'/> <label for="payment_type_online">'.(file_exists('images/payments/creditcard.png') ? '<img src="images/payments/creditcard.png" alt="'._ONLINE_ORDER.'" title="'._ONLINE_ORDER.'" style="margin-left:10px;height:35px;">' : _ONLINE_ORDER).'</label><br><br>';	
                    if($payment_type_bank_transfer == 'yes') echo '<input type="radio" name="payment_type" id="payment_type_transfer" value="bank.transfer" '.(($payment_type == 'bank.transfer') ? 'checked="checked"' : '').'/> <label for="payment_type_transfer">'.(file_exists('images/payments/bank_transfer.png') ? '<img src="images/payments/bank_transfer.png" alt="'._BANK_TRANSFER.'" title="'._BANK_TRANSFER.'" style="margin-left:10px;height:35px;">' : _BANK_TRANSFER).'</label><br><br>';
                    if($payment_type_paypal == 'yes')		 echo '<input type="radio" name="payment_type" id="payment_type_paypal" value="paypal" '.(($payment_type == 'paypal') ? 'checked="checked"' : '').'/> <label for="payment_type_paypal">'.(file_exists('images/payments/paypal.png') ? '<img src="images/payments/paypal.png" alt="'._PAYPAL.'" title="'._PAYPAL.'" style="margin-left:10px;height:35px;">' : _PAYPAL).'</label><br><br>';
                    if($payment_type_2co == 'yes')			 echo '<input type="radio" name="payment_type" id="payment_type_2co" value="2co" '.(($payment_type == '2co') ? 'checked="checked"' : '').'/> <label for="payment_type_2co">'.(file_exists('images/payments/2co.png') ? '<img src="images/payments/2co.png" alt="2CO" title="2CO" style="margin-left:10px;height:35px;">' : '2CO').'</label><br><br>';	
                    if($payment_type_authorize == 'yes')	 echo '<input type="radio" name="payment_type" id="payment_type_authorize" value="authorize.net" '.(($payment_type == 'authorize.net') ? 'checked="checked"' : '').'/> <label for="payment_type_authorize">'.(file_exists('images/payments/authorize.png') ? '<img src="images/payments/authorize.png" alt="Authorize.net" title="Authorize.net" style="margin-left:10px;height:35px;">' : 'Authorize.net').'</label><br><br>';
                    if($payment_type_balance == 'yes')		 echo '<input type="radio" name="payment_type" id="payment_type_balance" value="account.balance" '.(($payment_type == 'account.balance') ? 'checked="checked"' : '').'/> <label for="payment_type_balance">'.(file_exists('images/payments/balance.png') ? '<img src="images/payments/balance.png" alt="'._PAY_WITH_BALANCE.'" title="'._PAY_WITH_BALANCE.'" style="margin-left:10px;height:35px;">' : _PAY_WITH_BALANCE).'</label><br><br>';	
                }else{
                    echo '<tr><td width="130px" nowrap>'._PAYMENT_TYPE.': &nbsp;</td><td>';
                    echo '<select name="payment_type" class="form-control payment_type" id="payment_type">';
                        if($payment_type_poa == 'yes') echo '<option value="poa" '.(($payment_type == 'poa') ? 'selected="selected"' : '').'>'._PAY_ON_ARRIVAL.'</option>';
                        if($payment_type_online == 'yes') echo '<option value="online" '.(($payment_type == 'online') ? 'selected="selected"' : '').'>'._ONLINE_ORDER.'</option>';	
                        if($payment_type_bank_transfer == 'yes') echo '<option value="bank.transfer" '.(($payment_type == 'bank.transfer') ? 'selected="selected"' : '').'>'._BANK_TRANSFER.'</option>';
                        if($payment_type_paypal == 'yes') echo '<option value="paypal" '.(($payment_type == 'paypal') ? 'selected="selected"' : '').'>'._PAYPAL.'</option>';
                        if($payment_type_2co == 'yes') echo '<option value="2co" '.(($payment_type == '2co') ? 'selected="selected"' : '').'>2CO</option>';	
                        if($payment_type_authorize == 'yes') echo '<option value="authorize.net" '.(($payment_type == 'authorize.net') ? 'selected="selected"' : '').'>Authorize.Net</option>';
                        if($payment_type_balance == 'yes') echo '<option value="account.balance" '.(($payment_type == 'account.balance') ? 'selected="selected"' : '').'>'._PAY_WITH_BALANCE.'</option>';	
                    echo '</select>';
                }
                echo '</td></tr>';
            }elseif($payment_type_cnt == 1){
                if($payment_type_poa == 'yes') $payment_type_hidden = 'poa';
                else if($payment_type_online == 'yes') $payment_type_hidden = 'online';
                else if($payment_type_bank_transfer == 'yes') $payment_type_hidden = 'bank.transfer';
                else if($payment_type_paypal == 'yes') $payment_type_hidden = 'paypal';
                else if($payment_type_2co == 'yes') $payment_type_hidden = '2co';
                else if($payment_type_authorize == 'yes') $payment_type_hidden = 'authorize.net';
                else if($payment_type_balance == 'yes') $payment_type_hidden = 'account.balance';
                else{
                    $payment_type_hidden = '';
                    $payment_types_defined = false;
                }
                echo '<tr><td width="130px" nowrap>'._PAYMENT_TYPE.': &nbsp;</td><td>'.draw_hidden_field('payment_type', $payment_type_hidden, false, 'payment_type');
                echo '<b>';
                    if($payment_type_poa == 'yes') echo _PAY_ON_ARRIVAL;
                    if($payment_type_online == 'yes') echo _ONLINE_ORDER;
                    if($payment_type_bank_transfer == 'yes') echo _BANK_TRANSFER;
                    if($payment_type_paypal == 'yes') echo _PAYPAL;
                    if($payment_type_2co == 'yes') echo '2CO';	
                    if($payment_type_authorize == 'yes') echo 'Authorize.Net';
                    if($payment_type_balance == 'yes') echo _PAY_WITH_BALANCE;
                echo '</b>';
                echo '</td></tr>';
            }else{
                $payment_types_defined = false;
                echo '<tr><td colspan="2">'.draw_important_message(_NO_PAYMENT_METHODS_ALERT, false).'</td></tr>';
            }						
            echo '<tr>';
                if($pre_payment_type == 'first night' && $objReservation->first_night_possible){
                    echo '<td width="130px">'._PAYMENT_METHOD.': </td>';
                    echo '<td>';
                    echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
                    echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="first night" /> <label for="pre_payment_partially">'._FIRST_NIGHT.'</label>';
                    echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
                    echo '</td>';
                }elseif($pre_payment_type == 'percentage' && $pre_payment_value > '0' && $pre_payment_value < '100'){
                    echo '<td width="130px">'._PAYMENT_METHOD.': </td>';
                    echo '<td>';
                    echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked" /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
                    echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="percentage" /> <label for="pre_payment_partially">'._PRE_PAYMENT.' ('.Currencies::PriceFormat($pre_payment_value, '%', 'after', $currencyFormat).')</label>';
                    echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
                    echo '</td>';
                }elseif($pre_payment_type == 'fixed sum' && $pre_payment_value > '0'){
                    echo '<td width="130px">'._PAYMENT_METHOD.': </td>';
                    echo '<td>';
                    echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked" /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
                    echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="fixed sum" /> <label for="pre_payment_partially">'._PRE_PAYMENT.' ('.Currencies::PriceFormat($pre_payment_value * $currencyRate, '', '', $currencyFormat).')</label>';
                    echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
                    echo '</td>';
                }else{
                    echo '<td colspan="2">';
                    // full price payment
                    if($payment_type_cnt <= 1 && $payment_types_defined) echo _FULL_PRICE;
                    echo draw_hidden_field('pre_payment_type', 'full price', false, 'pre_payment_fully');
                    echo draw_hidden_field('pre_payment_value', '100', false, 'pre_payment_full');
                    echo '</td>';
                }					
            echo '</tr>';			
        echo '</table></td></tr>';
        
        if($payment_types_defined){			
            // PROMO CODES OR DISCOUNT COUPONS
            // ------------------------------------------------------------
            echo '<tr><td colspan="11" nowrap height="12px"></td></tr>';
            echo '<tr><td class="no_title" colspan="11"><h4>'._PROMO_CODE_OR_COUPON.'</h4></td></tr>';
            echo '<tr><td class="no_title" colspan="11">'._PROMO_COUPON_NOTICE.'</td></tr>';
            echo '<tr>';
            echo '<td class="no_title" colspan="11">';
            if(!empty($discountCoupon)){				
                echo '<input type="text" class="discount_coupon" name="discount_coupon" id="discount_coupon" value="'.$discountCoupon.'" readonly="readonly" maxlength="32" autocomplete="off" />&nbsp;&nbsp;&nbsp;';
                echo '<input type="button" class="form_button" id="discount_button" value="'._REMOVE.'" onclick="appSubmitCoupon(\'remove_coupon\')" />';
            }else{				
                echo '<input type="text" class="discount_coupon" name="discount_coupon" id="discount_coupon" value="'.$discountCoupon.'" maxlength="32" autocomplete="off" />&nbsp;&nbsp;&nbsp;';
                echo '<input type="button" class="form_button" id="discount_button" value="'._APPLY.'" onclick="appSubmitCoupon(\'apply_coupon\')" />';
            }
            echo '</td>';
            echo '</tr>';					
    
            echo '<tr><td colspan="11" nowrap height="15px"></td></tr>
                  <tr valign="middle">
                    <td colspan="11" nowrap height="15px">
                        <h4 style="cursor:pointer;" onclick="appToggleElement(\'additional_info\')">'._ADDITIONAL_INFO.' +</h4>
                        <textarea name="additional_info" id="additional_info" style="display:none;width:100%;height:75px"></textarea>
                    </td>
                  </tr>
                  <tr><td colspan="11" nowrap height="5px"></td></tr>
                  <tr valign="middle">
                    <td colspan="8" align="'.$class_right.'"></td>
                    <td align="'.$class_right.'" colspan="3">
                        '.(($payment_types_defined) ? '<input class="form_button" type="submit" value="'._SUBMIT_BOOKING.'" />' : '').' 
                    </td>
                </tr>';
            echo '</table>';
            echo '<input type="hidden" id="hid_vat_percent" value="'.$vatPercent.'" />';
            echo '<input type="hidden" id="hid_booking_initial_fee" value="'.$bookingInitialFee.'" />';
            echo '<input type="hidden" id="hid_booking_guest_tax" value="'.($guestTax * ($total_adults + $total_children)).'" />';
            echo '<input type="hidden" id="hid_order_price" value="'.$order_price.'" />';
            echo '<input type="hidden" id="hid_currency_symbol" value="'.Application::Get('currency_symbol').'" />';
            echo '<input type="hidden" id="hid_currency_format" value="'.$currencyFormat.'" />';
            echo '</form><br>';
            
            if($submition_type == 'apply_coupon'){
                echo "\n".'<script type="text/javascript">'."\n";			
                for($i=0; $i<$extras[1]; $i++){
                    $extras_id = isset($_POST['extras_'.$extras[0][$i]['id']]) ? $_POST['extras_'.$extras[0][$i]['id']] : 0;
                    if($extras_id > 0){
                        echo 'appUpdateTotalSum('.$i.','.$extras_id.','.$extras[1].')'.";\n";		
                    }
                }
                echo '</script>'."\n";					
            }
        }else{
            echo '</table>';
            echo '</form>';				
            return '';
        }
    }else{
        draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
    }
}	

/**
 * Draws pagination links
 * @param $total_pages
 * @param $current_page
 * @param $params
 * @param $draw
 */
function Template_draw_pagination_links($total_pages, $current_page, $params, $draw = true)
{
    global $objLogin;
    
    $output = '';
    
    // draw pagination links
    if($total_pages > 1){	
        if($objLogin->IsLoggedInAsAdmin()){				
            $output .= '<form action="index.php?page=check_availability" id="reservation-form" name="reservation-form" method="post">
            '.draw_hidden_field('p', '1', false, 'page_number').'
            '.draw_token_field(false).'
            '.draw_hidden_field('checkin_monthday', $params['from_day'], false, 'checkin_monthday').'
            '.draw_hidden_field('checkin_year_month', $params['from_year'].'-'.(int)$params['from_month'], false, 'checkin_year_month').'
            '.draw_hidden_field('checkout_monthday', $params['to_day'], false, 'checkout_monthday').'
            '.draw_hidden_field('checkout_year_month', $params['to_year'].'-'.(int)$params['to_month'], false, 'checkout_year_month');
        }
        
        $output .= '<div class="paging">';
        for($page_ind = 1; $page_ind <= $total_pages; $page_ind++){
            $output .= '<a class="paging_link" href="javascript:void(\'page|'.$page_ind.'\');" onclick="javascript:appFormSubmit(\'reservation-form\',\'page_number='.$page_ind.'\')">'.(($page_ind == $current_page) ? '<b>['.$page_ind.']</b>' : $page_ind).'</a> ';
        }
        $output .= '</div>'; 
        if($objLogin->IsLoggedInAsAdmin()) $output .= '<form>';
    }

    if($draw) echo $output;
    else return $output;		
}

/**
 * Draw extra beds dropdownlist
 * @param $room_id
 * @param $max_extra_beds
 * @param $params
 * @param $currency_rate
 * @param $currency_format
 * @param $enabled
 * @param $draw
 */
function Template_draw_extra_beds_ddl($room_id, $max_extra_beds, $params, $currency_rate, $currency_format, $enabled = true, $draw = true)
{
    $extra_bed_price = Rooms::GetRoomExtraBedsPrice($room_id, $params);
    $output = '<select class="form-control available_extra_beds_ddl" name="available_extra_beds" '.($enabled ? '' : 'disabled="disabled"').'>';
    $output .= '<option value="0">0</option>';	
    for($i=0; $i<$max_extra_beds; $i++){
        $extra_beds_count = ($i+1);
        $extra_bed_charge_per_night = ($extra_beds_count * $extra_bed_price);
        $extra_bed_charge_per_night_format = Currencies::PriceFormat($extra_bed_charge_per_night * $currency_rate, '', '', $currency_format);
        $output .= '<option value="'.$extra_beds_count.'-'.$extra_bed_charge_per_night.'">'.$extra_beds_count.' ('.$extra_bed_charge_per_night_format.')</option>';	
    }
    $output .= '</select>';
    
    if($draw) echo $output;
    else return $output;
}

/**
 * Get prepare array facilities
 * @return array
 */
function Template_get_prepare_facilities()
{
    $arr_facilities = array();
    // prepare facilities array		
    $total_facilities = RoomFacilities::GetAllActive();
    if(!empty($total_facilities[1]) && $total_facilities[1] > 0){
        foreach($total_facilities[0] as $key => $val){
            $arr_facilities[$val['id']] = array('name'=>$val['name'], 'code'=>$val['code'], 'icon'=>$val['icon_image']);
        }
    }

    return $arr_facilities;
}

/**
 * Get total reviews and average rating
 * @param int $hotel_id
 * @return array
 */
function Template_get_review_info($hotel_id)
{
    // prepare reviews
    $reviews = Reviews::GetReviews($hotel_id);
    $total_reviews = $reviews[1];
    $average_rating = 0;
    for($i=0; $i<$total_reviews; $i++){
        $average_rating += $reviews[0][$i]['rating_cleanliness'] + $reviews[0][$i]['rating_room_comfort'] + $reviews[0][$i]['rating_location'] + $reviews[0][$i]['rating_service'] + $reviews[0][$i]['rating_sleep_quality'] + $reviews[0][$i]['rating_price'];
    }			
    if($total_reviews > 0){
        $average_rating /= $total_reviews * 6;	
    }

    $arrOutput = array('total_reviews'=>$total_reviews, 'average_rating'=>$average_rating);

    return $arrOutput;
}
