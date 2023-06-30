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

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$main_images = '';

$room_info = Rooms::GetRoomFullInfo($room_id);
$room_images = Rooms::GetRoomInfo($room_id);
$property_type_id = isset($room_info['property_type_id']) ? (int)$room_info['property_type_id'] : '1';
$hotel_id = isset($room_info['hotel_id']) ? (int)$room_info['hotel_id'] : 0;
$hotel_info = Hotels::GetHotelFullInfo($hotel_id);
$arrFloor = Rooms::GetFloors();
$customer_id = $objLogin->GetLoggedID();

// Get info about wishlist
$in_wishlist = Wishlist::GetHotelInfo($room_id, 'room', $customer_id);

if(empty($hotel_info) || empty($room_info) || $room_info['is_active'] == 0){

	echo '<div class="container">';
	echo '<div class="container pagecontainer offset-2">';	
	
	draw_title_bar(_ROOMS);
    draw_important_message(_NOT_AUTHORIZED);	
	
	echo '<br><br>';
	echo '</div>';
	echo '</div>';
}else{

	$not_iteration = Session::Get('session_room_'.$room_id.'_not_iteration');
	$not_iteration_time = time() - (int)Session::Get('session_room_'.$room_id.'_not_iteration_time');
	if(!$not_iteration || $not_iteration_time > 60){
		// iteration number of views
		if(Rooms::NumberViewsIteration($room_id)){
			$room_info['number_of_views']++;
			Session::Set('session_room_'.$room_id.'_not_iteration', '1');
			Session::Set('session_room_'.$room_id.'_not_iteration_time', time());
		}
	}
	
    $arr_images = array();
    if(GALLERY_TYPE == 'carousel'){
        if(!empty($room_info['room_icon'])){
            $arr_images[] = $room_info['room_icon'];
        }
        for($i=0; $i < 9; $i++){
            $ind = 'room_picture_'.($i + 1);
            if(isset($room_info[$ind]) && $room_info[$ind] != ''){
                $arr_images[] = $room_info[$ind];
            }
        }
        if(empty($arr_images)){
            $arr_images[] = 'no_image.png';
        }
    }else{
        if(!empty($room_info['room_icon'])){
            $main_images .= '<img src="images/rooms/'.$room_info['room_icon'].'" alt="room image" />'."\n";
        }
        for($i=0; $i < 9; $i++){
            $ind = 'room_picture_'.($i + 1);
            if(isset($room_info[$ind]) && $room_info[$ind] != ''){
                $main_images .= '<img src="images/rooms/'.$room_info[$ind].'" alt="room image" />'."\n";
            }
        }
        if(empty($main_images)){
            $main_images .= '<img src="images/rooms/no_image.png" alt="room image" />'."\n";
        }
    }

	// prepare facilities array		
	$total_facilities = RoomFacilities::GetAllActive();
	$arr_facilities = array();
	foreach($total_facilities[0] as $key => $val){
		$arr_facilities[$val['id']] = array('code'=>$val['code'], 'name'=>$val['name'], 'icon'=>$val['icon_image']);
	}
	$facilities = isset($room_info['facilities']) ? unserialize($room_info['facilities']) : array();

?>

	<!-- CONTENT -->
	<div class="container">
		<div class="container pagecontainer offset-0">	
	
			<!-- SLIDER -->
            <?php if(GALLERY_TYPE != 'carousel'){ ?>
			<div class="col-md-8 details-slider">
			<div id="c-carousel">
                <div id="wrapper">
                    <div id="inner">
                        <div id="caroufredsel_wrapper2">
                            <div id="carousel"><?= $main_images; ?></div>
                        </div>
                        <div id="pager-wrapper">
                            <div id="pager"><?= $main_images; ?></div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <button id="prev_btn2" class="prev2"><img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/spacer.png" alt=""/></button>
                    <button id="next_btn2" class="next2"><img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/spacer.png" alt=""/></button>		
                </div>
            </div> <!-- /c-carousel -->
            </div>
            <?php }elseif(!empty($arr_images)){ ?>
            <div class="col-md-8" id="bx-slider">
                <ul id="bxslider">
                <?php for($i = 0, $max_count = count($arr_images); $i < $max_count; $i++){ ?>
                    <li<?= ($i > 0 ? ' style="display:none;float:none;list-style:none;"' : ' style="float:none;list-style:none;"'); ?>><img src="images/rooms/<?= htmlentities($arr_images[$i]); ?>" /></li>
                <?php } ?>
                </ul>

                <div id="bx-pager">
                <?php for($i = 0, $max_count = count($arr_images); $i < $max_count; $i++){ ?>
                    <a data-slide-index="<?= $i; ?>" href=""><img src="images/rooms/<?= htmlentities($arr_images[$i]); ?>" /></a>
                <?php } ?>
                </div>
                <script>
                    jQuery(document).ready(function(){
                        jQuery('#bxslider').bxSlider({
                            pagerCustom: '#bx-pager',
                            //adaptiveHeight: true,
                            mode: 'fade'
                        });
                    });
                </script>
            </div>
            <?php } ?>
			<!-- END OF SLIDER -->
			
			<!-- RIGHT INFO -->
			<div class="col-md-4 detailsright offset-0">
				<div class="padding20">
					<h3 class="lh1"><?= $room_info['loc_room_type']; ?></h3>
<?php
				if(get_currency_format() == 'european'){
					$number_of_views = number_format((float)$room_info['number_of_views'], 0, ',', '.');
				}else{
					$number_of_views = number_format((float)$room_info['number_of_views'], 0, '.', ',');
				}
?>
					&nbsp;<small class="grey2"><?= ($room_info['number_of_views'] > 1 ? str_replace('{number}', $number_of_views, _VIEWED_TIMES) : _VIEWED_TIME); ?></small>
				</div>
				
				<div class="line3"></div>
				
				<?php
					$arr_stars_vm = array(
						'0'=>_NONE,
						'1'=>'<img src="images/stars1.png" alt="1" title="1-star hotel" />',
						'2'=>'<img src="images/stars2.png" alt="2" title="2-star hotel" />',
						'3'=>'<img src="images/stars3.png" alt="3" title="3-star hotel" />',
						'4'=>'<img src="images/stars4.png" alt="4" title="4-star hotel" />',
						'5'=>'<img src="images/stars5.png" alt="5" title="5-star hotel" />');
					
                    if($hotel_info['stars'] >= 5){
                        $ratings = _WONDERFUL;
                    }elseif($hotel_info['stars'] >= 4){
                        $ratings = _VERY_GOOD;
                    }elseif($hotel_info['stars'] >= 3){
                        $ratings = _GOOD;
                    }elseif($hotel_info['stars'] >= 2){
                        $ratings = _NEUTRAL;
                    }elseif($hotel_info['stars'] >= 1){
                        $ratings = _NOT_GOOD;
					}else{
						$ratings = _NOT_RECOMMENDED;						
                    }
				?>				

				<div class="hpadding20">
					<div class="col-md-10">
						<h3 class="opensans slim green2"><a href="index.php?page=hotels&hid=<?= $hotel_id; ?>"><?= $hotel_info['name']; ?></a></h3>
						<?= (!$hotel_info['is_active'] ? '<label class="badge-red">'.htmlspecialchars(_INACTIVE).'</label>' : ''); ?>
						<h4 class="opensans slim green2"><?= $ratings; ?></h4>
					</div>
					<div class="col-md-2 center bordertype-wishlist">
						<?php
							if($in_wishlist){
								echo Wishlist::GetFavoriteButton('room', $room_id, $customer_id, Application::Get('token'), 'remove');
							}else{
								echo Wishlist::GetFavoriteButton('room', $room_id, $customer_id, Application::Get('token'), 'add');
							}
						?>
					</div>
					<div class="clearfix"></div>
				</div>
				
				<div class="line3 margtop20"></div>
				
				<div class="col-md-6 bordertype1 padding20 room-stars">
					<?php
						echo '<b>'._STARS.':</b><br>';
						echo (($hotel_info['stars'] > 0) ? $arr_stars_vm[$hotel_info['stars']] : '');
					?>
				</div>
				<div class="col-md-6 bordertype2 padding20 room-rating">
					<?php
						$rating = '';
						if(Modules::IsModuleInstalled('ratings') == 'yes'){					
							$rating .= '<link href="modules/ratings/css/ratings.css" rel="stylesheet" type="text/css" />';
							if(Application::Get('lang_dir') == 'rtl') $rating .= '<link href="modules/ratings/css/ratings-rtl.css" rel="stylesheet" type="text/css" />';
							$ratings_lang = (file_exists('modules/ratings/langs/'.Application::Get('lang').'.js')) ? Application::Get('lang') : 'en';
							$rating .= '<script src="modules/ratings/langs/'.$ratings_lang.'.js" type="text/javascript"></script>';
							$rating .= '<script src="modules/ratings/js/ratings.js" type="text/javascript"></script>';
							$rating .= '<b>'._VISITORS_RATING.': </b>';
							$rating .= '<div class="ratings_stars" style="margin:0 auto;" id="rt_room_'.$room_id.'"></div>';
						}			
						echo $rating;
					?>
				</div>
				
				<div class="clearfix"></div><br/>
				
				<div class="hpadding20">
					<a href="<?= prepare_link('pages', 'system_page', 'contact_us', 'index', '', '', '', true); ?>" class="add2fav margtop5"><?= _CONTACT_US; ?></a>
	
					<form action="index.php?page=check_availability" id="reservation-form-side" name="reservation-form" method="post">
						<input name="room_id" value="<?= $room_id; ?>" type="hidden" />
						<input name="p" id="page_number" value="1" type="hidden" />
						<input name="property_type_id" id="property_type_id" value="<?= $hotel_info['property_type_id']; ?>" type="hidden" />
						<input name="token" value="<?= Application::Get('token');?>" type="hidden" />
						<input name="hotel_sel_loc_id" value="" type="hidden">
						<input name="hotel_sel_id" value="<?= $hotel_id; ?>" type="hidden">
						<?= Rooms::PrepareHotelNameField($hotel_info); ?>
                        <?php
                            $min_nights = ModulesSettings::Get('booking', 'minimum_nights');
                            $min_nights_packages = Packages::GetMinimumNights(date('Y-m-d'), date('Y-m-d', mktime(0,0,0,date('m'),date('d')+$min_nights,date('y'))), $hotel_id, true);
                            if(!empty($min_nights_packages) && is_array($min_nights_packages)){
                                $packages_min_nights = '';
                                foreach($min_nights_packages as $key => $package){
                                    if(!empty($package['hotel_id'])){				
                                        if($package['minimum_nights'] < $packages_min_nights || $packages_min_nights === ''){
                                            $packages_min_nights = (int)$package['minimum_nights'];
                                        }
                                    }
                                }
                                
                                if(!empty($packages_min_nights) && $packages_min_nights < $min_nights){
                                    $min_nights = $packages_min_nights;
                                }
                            }
                        ?>
					
						<?php if(CALENDAR_HOTEL == 'new'){ ?>
							<input name="checkin_date" value="<?= date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y'); ?>" type="hidden">
							<input name="checkout_date" value="<?= date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', mktime(0, 0, 0, date('m'), date('d')+$min_nights, date('y'))); ?>" type="hidden">
						<?php }else{ ?>
							<?php
								$checkin_year_month 	= date('Y').'-'.(int)date('m');
								$checkin_day 			= date('d');					
		
								$checkout_date 			= mktime(0, 0, 0, date('m'), date('d')+$min_nights, date('y'));
								$checkout_year_month 	= date('Y', $checkout_date).'-'.(int)date('m', $checkout_date);
								$checkout_day 			= date('d', $checkout_date);					
							?>
							<input name="checkin_monthday" value="<?= $checkin_day; ?>" type="hidden">
							<input name="checkin_year_month" value="<?= $checkin_year_month; ?>" type="hidden">						
							<input name="checkout_monthday" value="<?= $checkout_day; ?>" type="hidden">
							<input name="checkout_year_month" value="<?= $checkout_year_month; ?>" type="hidden">
						<?php } ?>
							
						<input name="max_adults" value="1" type="hidden">
						<input name="max_children" value="0" type="hidden">
						<input name="sort_by" value="stars-5-1" type="hidden">

						<input type="submit" href="index.php?page=check_availability" class="booknow margtop20 btnmarg" value="<?= _BOOK_NOW; ?>" />						
					</form>
				</div>
			</div>
			<!-- END OF RIGHT INFO -->
	
		</div>
		<!-- END OF container-->
		
	
		<div class="container mt25 offset-0">
			<div class="col-md-8 pagecontainer2 offset-0">
				<div class="cstyle10"></div>
	
				<ul class="nav nav-tabs" id="myTab">
					<li onclick="mySelectUpdate()" class="active"><a data-toggle="tab" href="#summary"><span class="summary"></span><span class="hidetext">&nbsp;<?= _SUMMARY; ?></span>&nbsp;</a></li>
					<li onclick="mySelectUpdate()" class=""><a data-toggle="tab" href="#roomprices"><span class="rates"></span><span class="hidetext">&nbsp;<?= _PRICES; ?></span>&nbsp;</a></li>
					<li onclick="mySelectUpdate()" class=""><a data-toggle="tab" href="#facilities"><span class="preferences"></span><span class="hidetext">&nbsp;<?= _FACILITIES; ?></span>&nbsp;</a></li>
					<li onclick="mySelectUpdate()" class=""><a data-toggle="tab" href="#availability"><span class="reviews"></span><span class="hidetext">&nbsp;<?= _AVAILABILITY; ?></span>&nbsp;</a></li>
				</ul>
	
				<div class="tab-content4" >
					<!-- TAB 1 -->				
					<div id="summary" class="tab-pane fade active in">
						
						<!-- Collapse 1 -->	
						<button type="button" class="collapsebtn2 collapsed" data-toggle="collapse" data-target="#collapse1">
							<?= _DESCRIPTION; ?> <span class="collapsearrow"></span>
						</button>                    
						<div id="collapse1" class="collapse in">
							<div class="hpadding20">
								<?php
									$allow_children = ModulesSettings::Get('rooms', 'allow_children');
									$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
									$is_active = (isset($room_info['is_active']) && $room_info['is_active'] == 1) ? _AVAILABLE : _NOT_AVAILABLE;
									echo $room_info['loc_room_long_description'].'<br>';
									echo _COUNT.': '.$room_info['room_count'].'<br>';
									echo _MAX_ADULTS.': '.$room_info['max_adults'].'<br>';
									if(!empty($room_info['room_floor']) && isset($arrFloor[$room_info['room_floor']])) echo _FLOOR.': '.$arrFloor[$room_info['room_floor']].'<br>';
									if($allow_children == 'yes') echo _MAX_CHILDREN.': '.$room_info['max_children'].'<br>';
									if($allow_extra_beds == 'yes' && !empty($room_info['max_extra_beds'])) echo _MAX_EXTRA_BEDS.': '.$room_info['max_extra_beds'].'<br>';
									echo _AVAILABILITY.': '.$is_active.'<br>';
                                    if(!empty($room_info['beds']) || !empty($room_info['bathrooms']) || $room_info['room_area'] > 0.1){
                                        echo '<br>';
                                        echo !empty($room_info['beds']) ? _TOTAL_NUMBER_OF_BEDS.': '.$room_info['beds'].'<br>' : '';//_BEDS
                                        echo !empty($room_info['single_beds']) ? _SINGLE_BEDS.': '.$room_info['single_beds'].'<br>' : '';
                                        echo !empty($room_info['double_beds']) ? _DOUBLE_BEDS.': '.$room_info['double_beds'].'<br>' : '';
                                        echo !empty($room_info['folding_sofa_single_beds']) ? _FOLDING_SOFA_SINGLE_BEDS.': '.$room_info['folding_sofa_single_beds'].'<br>' : '';
                                        echo !empty($room_info['folding_sofa_double_beds']) ? _FOLDING_SOFA_DOUBLE_BEDS.': '.$room_info['folding_sofa_double_beds'].'<br>' : '';
                                        echo !empty($room_info['bathrooms']) ? _BATHROOMS.': '.$room_info['bathrooms'].'<br>' : '';
                                        echo $room_info['room_area'] > 0.1 ? _ROOM_AREA.': '.$room_info['room_area'].' m<sup>2</sup><br>' : '';
                                    }
								?>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>

					<!-- TAB 2 -->
					<div id="roomprices" class="tab-pane fadeh padding20">
						<div class="swipe-table table-responsive">
							<?= draw_swipe_icon(); ?>
							<?php
								echo '<h3>'._PRICES.'</h3>';
								echo Rooms::GetRoomPricesTable($room_id, false);
								echo '<br>';

								if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
									if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
										echo '<div class="left wh50percent">';
										echo Rooms::DrawRoomPricesNightDiscounts($room_info);
										echo '</div>';
									}else{
										echo Rooms::DrawRoomPricesNightDiscounts($room_info);
									}
								}
								if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
									if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
										echo '<div class="left wh50percent">';
										echo Rooms::DrawRoomPricesGuestsDiscounts($room_info);
										echo '</div>';
									}else{
										echo Rooms::DrawRoomPricesGuestsDiscounts($room_info);
									}
								}
								if(ModulesSettings::Get('rooms', 'refund_money') == 'yes'){
									echo Rooms::GetRoomRefundMoney($room_info);
								}
								
								echo '<h3>'._RESERVATION.'</h3>';
								echo Rooms::DrawSearchAvailabilityBlock(false, $room_id, $room_info['hotel_id'], 8, 3, 'room-inline', '', '', false, true, true, $property_type_id);
							?>
							<div class="clearfix"></div>
						</div>
						<br/>                    
					</div>
					
					<!-- TAB 3 -->					
					<div id="facilities" class="tab-pane fade">
						<!-- Collapse 7 -->	
						<button type="button" class="collapsebtn2" data-toggle="collapse" data-target="#collapse7">
						  <?= _FACILITIES; ?> <span class="collapsearrow"></span>
						</button>
						
						<div id="collapse7" class="collapse in">
							<div class="hpadding20">
								
								<div class="col-md-12 offset-0">
									<ul class="hotelpreferences2 left">
									<?php
										$output = '';
										if(is_array($facilities)){
											foreach($facilities as $key => $val){
												//if(isset($arr_facilities[$val])) $output .= '<li style="float:left;margin-right:5px;" class="icohp-'.$arr_facilities[$val]['code'].'" title="'.$arr_facilities[$val]['name'].'"></li>';
												if(isset($arr_facilities[$val])) echo '<li style="float:left;margin-right:5px;"'.(empty($arr_facilities[$val]['icon']) ? ' class="icohp-'.$arr_facilities[$val]['code'].'"' : '').' title="'.htmlentities($arr_facilities[$val]['name']).'">'.(!empty($arr_facilities[$val]['icon']) ? '<img src="images/facilities/'.$arr_facilities[$val]['icon'].'"/>' : '').'</li>';
											}					
										}
										echo $output;
									?>                    
									</ul>
								</div>
	
						
								<div class="clearfix"></div>
							</div>
							
						</div>
						<!-- End of collapse 7 -->		
						<br/>
						<div class="line4"></div>							
						
						<!-- Collapse 8 -->	
						<button type="button" class="collapsebtn2" data-toggle="collapse" data-target="#collapse8">
						  <?= _ROOM_FACILITIES; ?> <span class="collapsearrow"></span>
						</button>
						
						<div id="collapse8" class="collapse in">
							<div class="hpadding20">
								<div class="col-md-6">
									<ul class="checklist">
										<?php
											$count_facilities = count($facilities) / 2;
											$output = '';
											$count = 0;
											if(is_array($facilities)){
												foreach($facilities as $key => $val){
													if($count++ >= $count_facilities) continue;
													if(isset($arr_facilities[$val])) $output .= '<li>'.$arr_facilities[$val]['name'].'</li>';
												}					
											}
											echo $output;
										?>                    
									</ul>
								</div>
								<div class="col-md-6">
									<ul class="checklist">
										<?php
											$output = '';
											$count = 0;
											if(is_array($facilities)){
												foreach($facilities as $key => $val){
													if($count++ < $count_facilities) continue;
													if(isset($arr_facilities[$val])) $output .= '<li>'.$arr_facilities[$val]['name'].'</li>';
												}					
											}
											echo $output;
										?>                    
									</ul>									
								</div>	
							</div>
							<div class="clearfix"></div>
						</div>
						<!-- End of collapse 8 -->				
						
					</div>
					
					<!-- TAB 4 -->
					<div id="availability" class="tab-pane fade hpadding20">
						<div class="swipe-table table-responsive">
							<?= draw_swipe_icon(); ?>
							<?php
								if(!empty($room_id)){
									echo Rooms::DrawOccupancyCalendar($room_id, false);    
								}                                
							?>                            
							<div class="clearfix"></div>
						</div>
						<br/>                    
					</div>
					
				</div>
			</div>
			
			<div class="col-md-4 pagecontainer3">
				<div class="lastbookingbox">
					<?php
						$result = Bookings::GetLastBooking(1, TABLE_BOOKINGS_ROOMS.'.hotel_id = '.(int)$hotel_id.' AND '.TABLE_BOOKINGS_ROOMS.'.room_id = '.(int)$room_id);
						if($result[1] > 0){
							$unix_time_created = strtotime($result[0][0]['created_date']);
							$count_hours = (int)((time() - $unix_time_created) / 3600);
							if($count_hours < 24){
								$type_booking = $count_hours < 1 ? _JUST_BOOKED : ($count_hours > 1 ? $count_hours.' '._HOURS : _HOUR).' '._AGO;
							}else{
								$count_days = (int)($count_hours / 24);
								$type_booking = strtolower(($count_days == 1 ? '1 '._DAY : $count_days.' '._DAYS).' '._AGO);
							}
							$message = str_replace('{type_booking}', $type_booking, _LAST_BOOKING_ROOM_WAS);
							echo '<div class="msg notice">'.$message.'</div>';
						}
					?>
				</div>
				
				<?php
					$review = Reviews::GetRandomReview($hotel_id);
					if($review[1] > 0){
						echo '<div class="pagecontainer2 reviewbox">';
						echo '<div class="cpadding0 mt-10">';
							echo '<span class="icon-quote"></span>';        
							echo '<p class="opensans size16 grey2">'.substr_by_word((!empty($review[0]['positive_comments']) ? $review[0]['positive_comments'] : $review[0]['negative_comments']), 120, true).'<br/>';
							echo '<span class="lato orange bold size13"><i>'.(!empty($review[0]['author_name']) ? $review[0]['author_name'] : '').' '.(!empty($review[0]['author_country']) ? $review[0]['author_country'] : '').'</i></span></p>';
							echo prepare_link('hotels', 'hid', $review[0]['hotel_id'], _VIEW_ALL, _VIEW_ALL);
						echo '</div>';
						echo '</div>';
					}
				?>                    
            
				<?php
					if(ModulesSettings::Get('rooms', 'show_hotel_in_search_result') == 'yes'){
						$phone = ModulesSettings::Get('rooms', 'default_contant_phone');
						$email = ModulesSettings::Get('rooms', 'default_contant_email');
					}else{
						$support_info = Hotels::GetSupportSideInfo();
						$phone = $support_info['phone'];
						$email = $support_info['email'];
					}

				?>
				<div class="pagecontainer2<?= ($review[1] > 0) ? ' mt20' : ''; ?> pbottom15 needassistancebox">
					<div class="cpadding1">
						<span class="icon-help"></span>
						<h3 class="opensans"><?= _NEED_ASSISTANCE; ?></h3>
						<p class="size14 grey"><?= _NEED_ASSISTANCE_TEXT; ?></p>
                        <?php
                        if($objLogin->IsLoggedInAsCustomer() && !empty($email)){
                            $drawNeedAssistanceForm = Hotels::DrawNeedAssistanceForm($email);
							echo $drawNeedAssistanceForm;
                        }elseif(!empty($phone) || !empty($email)){ ?>
                            <p class="opensans size30 green xslim"><?= $phone; ?></p>
                            <span class="grey2"><a href="mailto:<?= htmlentities($email); ?>"><?= $email; ?></a></span>
                        <?php } ?>
					</div>
				</div><br/>
								
				<div class="pagecontainer2<?= ($review[1] > 0) ? ' mt20' : ''; ?> alsolikebox">
					<div class="cpadding1">
						<span class="icon-location"></span>
						<h3 class="opensans"><?= _YOU_MAY_ALSO_LIKE; ?></h3>
						<div class="clearfix"></div>
					</div>
					<?= Rooms::DrawRoomsSideInHotel($hotel_id, false, false); ?>
					<br/>
				</div>
			</div>            
		</div>    
		
	</div>
	<!-- END OF CONTENT -->

<?php } ?>

