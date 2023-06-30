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

	$hotel_id = isset($_GET['hid']) ? (int)$_GET['hid'] : '';
	$r_page = isset($_GET['r_page']) ? (int)$_GET['r_page'] : 0;
	$customer_id = $objLogin->GetLoggedID();
	
	$hotel_info = Hotels::GetHotelFullInfo($hotel_id, Application::Get('lang'));
    $hotel_images = Hotels::GetHotelsImages($hotel_id);

	$main_images = '';
    $arr_images = array();
    if(GALLERY_TYPE == 'carousel'){
        if(!empty($hotel_images[0][0]['hotel_image'])){
            $arr_images[] = $hotel_images[0][0]['hotel_image'];
        }
        for($i = 0; $i < $hotel_images[1]; $i++){
            if(!empty($hotel_images[0][$i]['item_file'])){
                $arr_images[] = $hotel_images[0][$i]['item_file'];
            }
        }
        if(empty($arr_images)){
            $arr_images[] = 'no_image.png';
        }
    }else{
        if(!empty($hotel_images[0][0]['hotel_image'])){
            $main_images .= '<img src="images/hotels/'.$hotel_images[0][0]['hotel_image'].'" alt="hotel image" />'."\n";
        }
        for($i=0; $i < $hotel_images[1]; $i++){
            if(!empty($hotel_images[0][$i]['item_file'])){
                $main_images .= '<img src="images/hotels/'.$hotel_images[0][$i]['item_file'].'" alt="hotel image" />'."\n";
            }
        }
        if(empty($main_images)){
            $main_images .= '<img src="images/hotels/no_image.png" alt="hotel image" />'."\n";
        }
    }

	// Get info about wishlist
	$in_wishlist = Wishlist::GetHotelInfo($hotel_id, 'hotel', $customer_id);
	
	$hotel_facilities  = isset($hotel_info['facilities']) ? unserialize($hotel_info['facilities']) : array();
	// prepare facilities array		
	$total_facilities = RoomFacilities::GetAllActive();
	$arr_facilities = array();
	foreach($total_facilities[0] as $key => $val){
		$arr_facilities[$val['id']] = array('name'=>$val['name'], 'code'=>$val['code'], 'icon'=>$val['icon_image']);
	}
	
	// Calculate averages 
	// Current page reviews
	$page_review = $r_page > 0 ? $r_page : 1;
	// Count show reviews
	$num_reviews = 10;
	$count_reviews = Reviews::CountReviews($hotel_id);
	// Count pages
	$count_page_reviews = ceil($count_reviews / $num_reviews);
	// Check current page reviews
	$page_review = $page_review > $count_page_reviews ? $count_page_reviews : $page_review;
	$reviews = Reviews::GetReviews($hotel_id, $page_review, $num_reviews);
	$guests_recommend = 0;
	$progress_bar_recommended = 0;
	$progress_bar_ratings = 0;
	$percent_of_guests_recommend = 0;
	$total_reviews = $reviews[1];
	$average_rating = array(
		'general'		=> 0,
		'cleanliness' 	=> 0,
		'room_comfort' 	=> 0,
		'location'		=> 0,
		'service'		=> 0,
		'sleep_quality'	=> 0,
		'price'			=> 0,
	);
	
	for($i=0; $i<$total_reviews; $i++){
		$average_rating['cleanliness'] += $reviews[0][$i]['rating_cleanliness'];
		$average_rating['room_comfort'] += $reviews[0][$i]['rating_room_comfort'];
		$average_rating['location'] += $reviews[0][$i]['rating_location'];
		$average_rating['service'] += $reviews[0][$i]['rating_service'];
		$average_rating['sleep_quality'] += $reviews[0][$i]['rating_sleep_quality'];
		$average_rating['price'] += $reviews[0][$i]['rating_price'];
		$average_rating['general'] = $average_rating['cleanliness'] + $average_rating['room_comfort'] + $average_rating['location'] + $average_rating['service'] + $average_rating['sleep_quality'] + $average_rating['price'];
		
		if($reviews[0][$i]['evaluation'] == 5){
			$guests_recommend++;
		}
	}
	
	if($total_reviews > 0){
		$average_rating['cleanliness'] /= $total_reviews;
		$average_rating['room_comfort'] /= $total_reviews;
		$average_rating['location'] /= $total_reviews;
		$average_rating['service'] /= $total_reviews;
		$average_rating['sleep_quality'] /= $total_reviews;
		$average_rating['price'] /= $total_reviews;
		$average_rating['general'] /= $total_reviews * 6;

		$percent_of_guests_recommend = round($guests_recommend / $total_reviews * 100, 2);
		$progress_bar_recommended = floor((int)($percent_of_guests_recommend / 5) * 5);
		$progress_bar_ratings = floor((int)(($average_rating['general'] * 20) / 5) * 5);
	}

?>

<!-- CONTENT -->
<div class="container">
<?php
	if(ADMIN_CAN_SEE_INACTIVE_PROPERTIES && $objLogin->IsLoggedInAs('owner','mainadmin')){
		$view_condition = empty($hotel_info);
	}else{
		$view_condition = empty($hotel_info) || $hotel_info['is_active'] == 0;
	}
	
	if($view_condition){
?>
		<div class="container pagecontainer offset-2">
			<?php
				draw_title_bar(_HOTEL_DESCRIPTION);
				draw_important_message(_WRONG_PARAMETER_PASSED);
			?>
            <br/>
            <br/>
		</div>
<?php			
	}else{			

	$not_iteration = Session::Get('session_hotel_'.$hotel_id.'_not_iteration');
	$not_iteration_time = time() - (int)Session::Get('session_hotel_'.$hotel_id.'_not_iteration_time');
	if(!$not_iteration || $not_iteration_time > 60){
		// iteration number of views
		if(Hotels::NumberViewsIteration($hotel_id)){
			$hotel_info['number_of_views']++;
			Session::Set('session_hotel_'.$hotel_id.'_not_iteration', '1');
			Session::Set('session_hotel_'.$hotel_id.'_not_iteration_time', time());
		}
	}

?>
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
				<li<?= ($i > 0 ? ' style="display:none;float:none;list-style:none;"' : ' style="float:none;list-style:none;"'); ?>><img src="images/hotels/<?= htmlentities($arr_images[$i]); ?>"/></li>
            <?php } ?>
            </ul>

            <div id="bx-pager">
            <?php for($i = 0, $max_count = count($arr_images); $i < $max_count; $i++){ ?>
                <a data-slide-index="<?= $i; ?>" href=""><img src="images/hotels/<?= htmlentities($arr_images[$i]); ?>" /></a>
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
			<?php
				$ratings = '';
				$average_property_rate = round($average_rating['general'], 1);
  
				if($average_property_rate >= 4.7){
					$ratings = _WONDERFUL;
				}elseif($average_property_rate >= 4){
					$ratings = _VERY_GOOD;
				}elseif($average_property_rate >= 3){
					$ratings = _GOOD;
				}elseif($average_property_rate >= 2){
					$ratings = _NEUTRAL;
				}elseif($average_property_rate >= 1){
					$ratings = _NOT_GOOD;
				//}else{
				//	$ratings = _NOT_RECOMMENDED;						
				}
			?>
			
            <div class="padding20">
				<?php
					echo '<div class="pull-right">';
					if(empty($ratings)){
						if($in_wishlist){
							echo Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'remove');
						}else{
							echo Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'add');
						}
					}
					echo '</div>';
				?>
                <h3 class="lh1"><?= $hotel_info['name']; ?></h3>
                <?php if(!empty($hotel_info['registration_number'])): ?>
                    <p class="size12 grey"><?= _HOTEL_REGISTRATION_NUMBER.': '.$hotel_info['registration_number']; ?></p>
                <?php endif; ?>
				<?= (($hotel_info['stars'] > 0) ? '<img src="templates/'.Application::Get('template').'/images/smallrating-'.$hotel_info['stars'].'.png" alt="hotel stars" />' : ''); ?>
				<?php
					if(get_currency_format() == 'european'){
						$number_of_views = number_format((float)$hotel_info['number_of_views'], 0, ',', '.');
					}else{
						$number_of_views = number_format((float)$hotel_info['number_of_views'], 0, '.', ',');
					}
				?>
				&nbsp;<small class="grey2"><?= ($hotel_info['number_of_views'] > 1 ? str_replace('{number}', $number_of_views, _VIEWED_TIMES) : _VIEWED_TIME); ?></small>
				<?php
					echo (!$hotel_info['is_active'] ? ' &nbsp;<label class="badge-red">'.htmlspecialchars(_INACTIVE).'</label>' : '');	
				?>

            </div>            
            <div class="line3"></div>
            
			<?php if(!empty($ratings)){ ?>
				<div class="hpadding10">
					<div class="col-md-7">
						<h3 class="opensans slim green2"><?= $ratings; ?></h3>
					</div>
					<div class="col-md-5 center bordertype-wishlist bordertypeh60">
						<?php						
							if($in_wishlist){
								echo Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'remove');
							}else{
								echo Wishlist::GetFavoriteButton('hotel', $hotel_id, $customer_id, Application::Get('token'), 'add');
							}
						?>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="line3 margtop10"></div>
			<?php } ?>            

			<?php if($total_reviews > 0){ ?>
				<div class="col-md-6 bordertype1 bordertypeh100 padding20 bordertype-guests-recommend">
					<?= str_replace('_PERCENT_', '<span class="opensans size30 bold grey2">'.$percent_of_guests_recommend.'%</span><br>', _PERCENT_OF_GUESTS_RECOMMEND); ?>
				</div>
				<div class="col-md-6 bordertype2 bordertypeh100 padding20 bordertype-guests-rating">
					<span class="opensans size30 bold grey2"><?= round($average_rating['general'], 1); ?></span>/5<br/>
					<?= _GUEST_RATINGS; ?>
				</div>
			<?php } ?>
            
			<div class="col-md-6 bordertype3 bordertype-reviews">
				<img src="templates/<?= Application::Get('template'); ?>/images/user-rating-<?= round($average_rating['general']); ?>.png" alt="user rating" /><br/>
				<a href="javascript:void('reviews');" onclick="$('#tab-reviews').click();scrollToElement('tab-reviews');"><?= $total_reviews.' '.strtolower($total_reviews == 1 ? _REVIEW : _REVIEWS); ?></a>
			</div>

			<div class="col-md-6 bordertype3 bordertype-all-reviews">
				<a <?php if($objLogin->IsLoggedInAsCustomer()){ echo 'href="index.php?customer=my_reviews&mg_action=add&hid='.(int)$hotel_id.'"'; }else{ ?>href="javascript:void('reviews');" onclick="$('#tab-reviews').click();scrollToElement('tab-reviews');" data-toggle="tab" href="javascript:void(0)"<?php } ?> class="grey"><?= ($objLogin->IsLoggedInAsCustomer() ? '+ '._ADD_REVIEW : _ALL_REVIEWS); ?></a>
			</div>

            <div class="clearfix"></div><br/>
            
            <div class="hpadding20">
                <a href="<?= prepare_link('pages', 'system_page', 'contact_us', 'index', '', '', '', true); ?>" class="add2fav margtop5"><?= _CONTACT_US; ?></a>

				<form action="index.php?page=check_availability" id="reservation-form-side" name="reservation-form" method="post">
					<input name="room_id" value="" type="hidden" />
					<input name="p" id="page_number" value="1" type="hidden" />
					<input name="property_type_id" id="property_type_id" value="<?= $hotel_info['property_type_id']; ?>" type="hidden" />
					<input name="token" value="<?= Application::Get('token');?>" type="hidden" />
					<input name="hotel_sel_loc_id" value="" type="hidden" />
					<input name="hotel_sel_id" value="<?= $hotel_id; ?>" type="hidden" />
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
						<input name="checkin_date" value="<?= date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', (Session::Get('availability_checkin_unix') != '' ? Session::Get('availability_checkin_unix') : time())); ?>" type="hidden">
						<input name="checkout_date" value="<?= date($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', (Session::Get('availability_checkout_unix') != '' ? Session::Get('availability_checkout_unix') : mktime(0, 0, 0, date('m'), date('d')+$min_nights, date('y')))); ?>" type="hidden">
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
				<li onclick="$('#maps').addClass('tab-pane');" class="<?= empty($r_page) ? 'active' : ''; ?>"><a data-toggle="tab" href="#summary"><span class="summary"></span><span class="hidetext">&nbsp;<?= _SUMMARY; ?></span>&nbsp;</a></li>
                <li onclick="$('#maps').addClass('tab-pane');" class=""><a data-toggle="tab" href="#roomprices"><span class="rates"></span><span class="hidetext">&nbsp;<?= _PRICES; ?></span>&nbsp;</a></li>
                <li onclick="$('#maps').addClass('tab-pane');" class=""><a data-toggle="tab" href="#preferences"><span class="preferences"></span><span class="hidetext">&nbsp;<?= _PREFERENCES; ?></span>&nbsp;</a></li>
                <li onclick="$('#maps').removeClass('tab-pane');" class=""><a data-toggle="tab" href="#maps"><span class="maps"></span><span class="hidetext">&nbsp;<?= _MAPS; ?></span>&nbsp;</a></li>
				<li onclick="$('#maps').addClass('tab-pane');" class="<?= !empty($r_page) ? 'active' : ''; ?>"><a id="tab-reviews" data-toggle="tab" href="#reviews"><span class="reviews"></span><span class="hidetext">&nbsp;<?= _REVIEWS; ?></span>&nbsp;</a></li>
            </ul>

            <div class="tab-content4">
                <!-- TAB 1 -->				
                <div id="summary" class="tab-pane fade<?= empty($r_page) ? ' active in' : ''; ?>">
                    <!-- Collapse 1 -->	
                    <button type="button" class="collapsebtn2 collapsed" data-toggle="collapse" data-target="#collapse1">
                        <?= _DESCRIPTION; ?> <span class="collapsearrow"></span>
                    </button>                    
                    <div id="collapse1" class="collapse in">
                        <div class="hpadding20">
                            <?= $hotel_info['description']; ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <?php if(!empty($hotel_info['video_link'])): ?>
                    <button type="button" class="collapsebtn2 collapsed" data-toggle="collapse" data-target="#collapse3">
                        <?= _VIDEO; ?> <span class="collapsearrow"></span>
                    </button>
                    <div id="collapse3" class="collapse in">
                        <div class="hpadding20">
                            <?= Hotels::video($hotel_info['video_link'], array('width'=>'560', 'height'=>'350', 'autoplay'=>true, 'allowfullscreen'=>true)); ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <?php endif; ?>

					<?php if($hotel_info['distance_center'] > 0 && $hotel_info['name_center_point'] != ''){ ?>
                    <!-- Collapse 2 -->	
                    <button type="button" class="collapsebtn2 collapsed" data-toggle="collapse" data-target="#collapse2">
                        <?= _LOCATION; ?> <span class="collapsearrow"></span>
                    </button>                    
                    <div id="collapse2" class="collapse in">
                        <div class="hpadding20">
							<?php 
								$distance_string = $hotel_info['distance_center'].' '._KILOMETERS_SHORTENED;
							?>
                            <?= str_replace(array('{name_center_point}', '{distance_center_point}'), array($hotel_info['name_center_point'],$distance_string), _DISTANCE_OF_HOTEL_FROM_CENTER_POINT); ?>
                        </div>
                        <div class="clearfix"></div>
					</div>
					<?php } ?>

                    <!-- Collapse 3 -->	
                    <button type="button" class="collapsebtn2 collapsed" data-toggle="collapse" data-target="#collapse3">
                        <?= _ROOMS; ?> <span class="collapsearrow"></span>
                    </button>                    
                    <div id="collapse3" class="collapse in">
                        <div class="hpadding10">
                            <?= Rooms::DrawRoomsInHotel($hotel_id, false, false); ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>                
                
                <!-- TAB 2 -->
                <div id="roomprices" class="tab-pane fade">
                    <div class="hpadding20">
                        <?= Rooms::DrawSearchAvailabilityBlock(true, '', $hotel_id, 8, 3, 'main-vertical', '', '', false, true, true, $hotel_info['property_type_id']); ?>
                        <div class="clearfix"></div>
                    </div>
                    <br/>                    
                </div>
                
                <!-- TAB 3 -->					
                <div id="preferences" class="tab-pane fade">
                    <p class="hpadding20">
                    <?= $hotel_info['preferences']; ?>
                    </p>                        
                    <div class="line4"></div>                    
                    
                    <?php
                        $count_facilities = count($hotel_facilities) / 2;                        
                    ?>                    
                    <?php if(count($hotel_facilities) > 1){ ?>
                    <!-- Collapse 7 -->	
                    <button type="button" class="collapsebtn2" data-toggle="collapse" data-target="#collapse7">
                    <?= _FACILITIES; ?> <span class="collapsearrow"></span>
                    </button>
                    <?php } ?>
                    
                    <div id="collapse7" class="collapse in">
                        <div class="hpadding20">                            
                            <div class="col-md-12 offset-0">
                                <ul class="hotelpreferences2 left">
                                    <?php
                                        $count = 0;
                                        if(is_array($hotel_facilities)){
                                            foreach($hotel_facilities as $key => $val){
                                                if($count++ >= $count_facilities) continue;
                                                //if(isset($arr_facilities[$val])) echo '<li class="icohp-'.$arr_facilities[$val]['code'].'" title="'.htmlentities($arr_facilities[$val]['name']).'"></li>';
												if(isset($arr_facilities[$val])) echo '<li'.(empty($arr_facilities[$val]['icon']) ? ' class="icohp-'.$arr_facilities[$val]['code'].'"' : '').' title="'.htmlentities($arr_facilities[$val]['name']).'">'.(!empty($arr_facilities[$val]['icon']) ? '<img src="images/facilities/'.$arr_facilities[$val]['icon'].'"/>' : '').'</li>';
                                            }					
                                        }
                                    ?>
                                </ul>
                                <ul class="hpref-text left col-md-4">
                                    <?php
                                        $count = 0;
                                        if(is_array($hotel_facilities)){
                                            foreach($hotel_facilities as $key => $val){
                                                if($count++ >= $count_facilities) continue;
                                                if(isset($arr_facilities[$val])) echo '<li>'.htmlentities($arr_facilities[$val]['name']).'</li>';
                                            }					
                                        }
                                    ?>
                                </ul>

                                <ul class="hotelpreferences2 left">
                                    <?php
                                        $count = 0;
                                        if(is_array($hotel_facilities)){
                                            foreach($hotel_facilities as $key => $val){
                                                if($count++ < $count_facilities) continue;
                                                //if(isset($arr_facilities[$val])) echo '<li class="icohp-'.$arr_facilities[$val]['code'].'" title="'.htmlentities($arr_facilities[$val]['name']).'"></li>';
												if(isset($arr_facilities[$val])) echo '<li'.(empty($arr_facilities[$val]['icon']) ? ' class="icohp-'.$arr_facilities[$val]['code'].'"' : '').' title="'.htmlentities($arr_facilities[$val]['name']).'">'.(!empty($arr_facilities[$val]['icon']) ? '<img src="images/facilities/'.$arr_facilities[$val]['icon'].'"/>' : '').'</li>';
                                            }					
                                        }
                                    ?>
                                </ul>
                                <ul class="hpref-text left col-md-4">
                                    <?php
                                        $count = 0;
                                        if(is_array($hotel_facilities)){
                                            foreach($hotel_facilities as $key => $val){
                                                if($count++ < $count_facilities) continue;
                                                if(isset($arr_facilities[$val])) echo '<li>'.htmlentities($arr_facilities[$val]['name']).'</li>';
                                            }					
                                        }
                                    ?>
                                </ul>
                            </div>
                            <div class="clearfix"></div>
                        </div>                        
                    </div>
                    
                    <!-- End of collapse 7 -->		
                </div>
                
                <!-- TAB 4 -->					
                <div id="maps" class="<?= !empty($r_page) ? 'tab-pane ' : ''; ?>fade">
                    <div class="hpadding20">
                        <?php Hotels::DrawMap($hotel_info, array('width'=>100)); ?>
                        <div class="clearfix"></div>
                    </div>
                </div>

                <!-- TAB 5 -->					
				<div id="reviews" class="tab-pane fade<?= !empty($r_page) ? ' active in' : ''; ?>">
				
					<?php if($total_reviews > 0 ){ ?>
						<script>//jQuery("html, body").animate({scrollTop: $("#reviews").offset().top}, 1100);</script>
						<div class="hpadding20">
							<div class="col-md-4 offset-0">
								<span class="opensans dark size60 slim lh3"><?= round($average_rating['general'], 1); ?>/5</span><br/>
								<img src="templates/<?= Application::Get('template'); ?>/images/user-rating-<?= round($average_rating['general']); ?>.png" alt="user rating"/>
							</div>
							<div class="col-md-8 offset-0">
								<div class="progress progress-striped">
								  <div class="progress-bar progress-bar-success wh<?= $progress_bar_ratings; ?>percent" role="progressbar" aria-valuenow="<?= $progress_bar_ratings; ?>" aria-valuemin="0" aria-valuemax="100">
									<span class="sr-only"><?= str_replace(array('_X_', '_Y_'), array(round($average_rating['general'], 1), '5'), _X_OUT_OF_Y); ?></span>
								  </div>
								</div>		
								<div class="progress progress-striped">
								  <div class="progress-bar progress-bar-success wh<?= $progress_bar_recommended; ?>percent" role="progressbar" aria-valuenow="<?= $progress_bar_recommended; ?>" aria-valuemin="0" aria-valuemax="100">
									<span class="sr-only"><?= str_replace('_PERCENT_', $percent_of_guests_recommend.'%', _PERCENT_OF_GUESTS_RECOMMEND); ?></span>
								  </div>
								</div>
								<div class="clearfix"></div>
								<?= str_replace('_REVIEWS_', $total_reviews, _RATINGS_BASED_ON_REVIEWS); ?>
							</div>			
							<div class="clearfix"></div>
							<br/>
							<span class="opensans dark size16 bold"><?= _AVERAGE_RATINGS; ?></span>
						</div>
						
						<div class="line4"></div>
						
						<div class="hpadding20">
							<div class="col-md-4 offset-0">
								<div class="scircle left"><?= number_format($average_rating['cleanliness'], 1); ?></div>
								<div class="sctext left margtop15"><?= _CLEANLINESS; ?></div>
								<div class="clearfix"></div>
								<div class="scircle left"><?= number_format($average_rating['service'], 1); ?></div>
								<div class="sctext left margtop15"><?= _SERVICE_AND_STAFF; ?></div>
								<div class="clearfix"></div>								
							</div>
							<div class="col-md-4 offset-0">
								<div class="scircle left"><?= number_format($average_rating['room_comfort'], 1); ?></div>
								<div class="sctext left margtop15"><?= _ROOM_COMFORT; ?></div>
								<div class="clearfix"></div>
								<div class="scircle left"><?= number_format($average_rating['sleep_quality'], 1); ?></div>
								<div class="sctext left margtop15"><?= _SLEEP_QUALITY; ?></div>			
								<div class="clearfix"></div>										
							</div>
							<div class="col-md-4 offset-0">
								<div class="scircle left"><?= number_format($average_rating['location'], 1); ?></div>
								<div class="sctext left margtop15"><?= _LOCATION; ?></div>
								<div class="clearfix"></div>
								<div class="scircle left"><?= number_format($average_rating['price'], 1); ?></div>
								<div class="sctext left margtop15"><?= _VALUE_FOR_PRICE; ?></div>
								<div class="clearfix"></div>										
							</div>		
							<div class="clearfix"></div>
							
							<br/>
							<span class="opensans dark size16 bold"><?= _REVIEWS; ?></span>
						</div>
						
						<div class="line2"></div>
						
						<?php for($i=0; $i<$total_reviews; $i++){ ?>	
							<div class="vpadding20 hpadding10">						
								<div class="col-md-4 offset-0 center">
									<div class="padding20">
										<div class="bordertype5">
											<div class="circlewrap">
												<?php
													$profile_photo_src = !empty($reviews[0][$i]['profile_photo_thumb']) && file_exists('images/customers/'.$reviews[0][$i]['profile_photo_thumb']) ? $reviews[0][$i]['profile_photo_thumb'] : 'no_image.png';
												?>
												<img src="images/customers/<?= $profile_photo_src; ?>" class="circleimg" alt="profile photo" />
												<span><?= round(($reviews[0][$i]['rating_cleanliness'] + $reviews[0][$i]['rating_room_comfort'] + $reviews[0][$i]['rating_location'] + $reviews[0][$i]['rating_service'] + $reviews[0][$i]['rating_sleep_quality'] + $reviews[0][$i]['rating_price']) / 6, 1); ?></span>
											</div>
											<span class="dark"><?= _BY; ?> <?= $reviews[0][$i]['author_name']; ?></span><br/>
											<?= strtolower(_FROM); ?> <?= $reviews[0][$i]['author_city'].( !empty($reviews[0][$i]['author_city']) && $reviews[0][$i]['country_name'] ? ', ' : '' ).$reviews[0][$i]['country_name']; ?><br/>
											<div class="vpadding10 hpadding20">
												<span class="green "><?= Reviews::GetEvaluation($reviews[0][$i]['evaluation']); ?></span>
											</div>
											<?php if($reviews[0][$i]['evaluation'] > 4){ ?>
												<img src="templates/default/images/check.png" alt=""/><br>
											<?php } ?>
										</div>
										
									</div>
								</div>
								<div class="col-md-8 offset-0">
									<div class="padding20">
										<span class="opensans size16 dark">
											<?= $reviews[0][$i]['title']; ?>
										</span>
										<br/>
										<span class="opensans size13 lgrey"><?= _POSTED_ON; ?> <?= format_datetime($reviews[0][$i]['date_created'], '', '', true); ?>
										
										<?php if($reviews[0][$i]['customer_id'] == $customer_id){ ?>
											<span class="your-review green">(<?= _IT_IS_YOUR_REVIEW; ?>)</span>
										<?php } ?>
										</span><br/>
										<?= !empty($reviews[0][$i]['positive_comments']) ? '<p><span class="badge badge-green">+</span> '.$reviews[0][$i]['positive_comments'] : '</p>'; ?>
										<?= !empty($reviews[0][$i]['negative_comments']) ? '<p><span class="badge badge-gray">-</span> '.$reviews[0][$i]['negative_comments'] : '</p>'; ?>
										<ul class="circle-list customer-circle-list">
											<li title="<?= _CLEANLINESS; ?>"><?= $reviews[0][$i]['rating_cleanliness']; ?></li>
											<li title="<?= _ROOM_COMFORT; ?>"><?= $reviews[0][$i]['rating_room_comfort']; ?></li>
											<li title="<?= _LOCATION; ?>"><?= $reviews[0][$i]['rating_location']; ?></li>
											<li title="<?= _SERVICE_AND_STAFF; ?>"><?= $reviews[0][$i]['rating_service']; ?></li>
											<li title="<?= _SLEEP_QUALITY; ?>"><?= $reviews[0][$i]['rating_sleep_quality']; ?></li>
											<li title="<?= _VALUE_FOR_PRICE; ?>"><?= $reviews[0][$i]['rating_price']; ?></li>
										</ul>
										<?php if(!empty($reviews[0][$i]['admin_answer'])){
											echo '<div class="admin-answer">';
											echo '<span class="opensans size13 dark">'._ADMIN_ANSWER.':</span>';
											echo '<p>'.$reviews[0][$i]['admin_answer'].'</p>';
											echo '</div>';
											}
										?>
									</div>
									<?php if(!empty($reviews[0][$i]['image_file_1']) || !empty($reviews[0][$i]['image_file_2']) || !empty($reviews[0][$i]['image_file_3'])){ ?>
									<div class="padding20">
										<?php
											$image_dir = 'images/reviews/';
											$num_img = 0;
										?>
										<?php if(!empty($reviews[0][$i]['image_file_1']) && is_file($image_dir.$reviews[0][$i]['image_file_1'])){ ?>
										<a href="<?= $image_dir.$reviews[0][$i]['image_file_1']; ?>" rel="lyteshow[720 480](review<?= $reviews[0][$i]['id']; ?>)" title="Picture #<?= ++$num_img; ?>">
											<img src="<?= $image_dir.$reviews[0][$i]['image_file_1_thumb']; ?>" height="45px" />
										</a>
										<?php } ?>
										<?php if(!empty($reviews[0][$i]['image_file_2']) && is_file($image_dir.$reviews[0][$i]['image_file_2'])){ ?>
										<a href="<?= $image_dir.$reviews[0][$i]['image_file_2']; ?>" rel="lyteshow[720 480](review<?= $reviews[0][$i]['id']; ?>)" title="Picture #<?= ++$num_img; ?>">
											<img src="<?= $image_dir.$reviews[0][$i]['image_file_2_thumb']; ?>" height="45px" />
										</a>
										<?php } ?>
										<?php if(!empty($reviews[0][$i]['image_file_3']) && is_file($image_dir.$reviews[0][$i]['image_file_3'])){ ?>
										<a href="<?= $image_dir.$reviews[0][$i]['image_file_3']; ?>" rel="lyteshow[720 480](review<?= $reviews[0][$i]['id']; ?>)" title="Picture #<?= ++$num_img; ?>">
											<img src="<?= $image_dir.$reviews[0][$i]['image_file_3_thumb']; ?>" height="45px" />
										</a>
										<?php } ?>
									</div>
									<?php } ?>
								</div>
								<div class="clearfix"></div>
							</div>
								
							<div class="line2"></div>						
						<?php }	?>
						<?php if($count_page_reviews > 1){ ?>
							<div class="col-md-12 pull-right go-right">
							    <ul class="pagination right paddingbtm20">
							<?php 
								$link = 'index.php?page=hotels&hid='.$hotel_id.'&r_page=';
								$r_page_end = $count_page_reviews;
								$r_show_prev = true;
								$r_show_next = true;

								if($page_review == 1){
									// Show start pages "1 2 3 ... 7 >>"
									if($page_review + 2 < $r_page_end){
										$r_show_pages = array(1, $page_review + 1, $page_review + 2, $r_page_end);
									}else{
										$r_show_pages = range(1, $r_page_end);
									}
									$r_show_prev = false;
									$r_show_next = $page_review < $r_page_end ? true : false;
								}elseif($page_review == $r_page_end){
									// Show end pages "<< 1 ... 5 6 7"
									if($page_review - 2 > 1){
										$r_show_pages = array(1, $page_review - 2, $page_review - 1, $r_page_end);
									}else{
										$r_show_pages = range(1, $r_page_end);
									}
									$r_show_next = false;
								}else{
									// "<< 1 ... 3 4 5 ... 7 >>"
									$r_show_pages = array(1, $page_review - 1, $page_review, $page_review + 1, $r_page_end);
									$r_show_pages = array_unique($r_show_pages);
								}

								// Link Prev
								if($r_show_prev){
									echo '<li><a href="'.$link.($page_review - 1).'" data-page="'.($page_review - 1).'">«</a></li>';
								}

								// The variable needed to display "..."
								$r_page_prev = $r_show_prev[0];
								foreach($r_show_pages as $r_page){
									if($r_page_prev < ($r_page - 1)){
										// Link to page between
										echo '<li><a href="'.$link.$r_page.'" data-page="'.(int)(($r_page_prev + $r_page) / 2).'">...</a></li>';
									}
									if($r_page != $page_review){
										echo '<li><a href="'.$link.$r_page.'" data-page="'.$r_page.'">'.$r_page.'</a></li>';
									}else{
										// Active page
										echo '<li class="active"><a href="javascript:void(0);" data-page="0">'.$r_page.'</a></li>';
									}
									$r_page_prev = $r_page;
								}
								
								// Link Next
								if($r_show_next){
									echo '<li><a href="'.$link.($page_review + 1).'" data-page="'.($page_review + 1).'">»</a></li>';
								}
							?>
							    </ul>
							</div>
						<?php } ?>
						<br/>
						<br/>
					<?php }else{ ?>
						<div class="hpadding20">
						<?php draw_message(str_replace('_PROPERTY_NAME_', $hotel_info['name'], _PROPERTY_NOT_REVIEWED_YET)); ?>
						</div>
						<br/><br/>
					<?php if(!$objLogin->IsLoggedInAsCustomer()){ ?>
						<div class="hpadding20">
						<?php draw_message(_MUST_BE_LOGGED); ?>
						</div>
						<br/><br/>
					<?php  } 
					   } 
					?>

					<?php
					// It's allowed to be added from My Reviews menu in Customer's Account
					if(FALSE && $objLogin->IsLoggedInAsCustomer()){
					?>
						<div class="hpadding20" id="title-add-review">
							<span class="opensans dark size16 bold"><?= _ADD_REVIEW; ?></span>
						</div>
						
						<div class="line2"></div>

						<div class="wh33percent left center">
							<ul class="jslidetext">
								<li><?= _CLEANLINESS; ?></li>
								<li><?= _ROOM_COMFORT; ?></li>
								<li><?= _LOCATION; ?></li>
								<li><?= _SERVICE_AND_STAFF; ?></li>
								<li><?= _SLEEP_QUALITY; ?></li>
								<li><?= _VALUE_FOR_PRICE; ?></li>
							</ul>
							
							<ul class="jslidetext2">
								<li><?= _EVALUATION; ?></li>
								<li><?= _TITLE; ?></li>
								<li><?= _POSITIVE_COMMENTS; ?></li>
								<li><?= _NEGATIVE_COMMENTS; ?></li>
							</ul>
						</div>
						<div class="wh66percent right offset-0">
                        <form action="<?= APPHP_BASE; ?>index.php?page=hotels&hid=<?= $hotel_id; ?>" method="post" name="FormAddReview" id="FormAddReview">
								<?php draw_token_field(); ?>
								<?php draw_hidden_field('hid', $hotel_id); ?>
								<?php draw_hidden_field('customer_id', $customer_id); ?>
								<div class="padding20 relative wh70percent">
									<div class="layout-slider wh100percent">
									<span class="cstyle01"><input type="slider" name="rating_cleanliness" id="FormAddReviewRatingCleanliness" value="0;2.5" /></span>
									</div>
									<script type="text/javascript">jQuery("#FormAddReviewRatingCleanliness").slider({ from: 0, to: 5, step: 0.1, smooth: false, round: 1, dimension: "", skin: "round", showStartPoint: false });</script>
									
									<div class="layout-slider margtop10 wh100percent">
									<span class="cstyle01"><input type="slider" name="rating_room_comfort" id="FormAddReviewRatingRoomConfort" value="0;2.5" /></span>
									</div>
									<script type="text/javascript">jQuery("#FormAddReviewRatingRoomConfort").slider({ from: 0, to: 5, step: 0.1, smooth: true, round: 1, dimension: "", skin: "round" , showStartPoint: false });</script>
									
									<div class="layout-slider margtop10 wh100percent">
									<span class="cstyle01"><input type="slider" name="rating_location" id="FormAddReviewRatingLocation" value="0;2.5" /></span>
									</div>
									<script type="text/javascript">jQuery("#FormAddReviewRatingLocation").slider({ from: 0, to: 5, step: 0.1, smooth: true, round: 1, dimension: "", skin: "round" , showStartPoint: false });</script>

									<div class="layout-slider margtop10 wh100percent">
									<span class="cstyle01"><input type="slider" name="rating_service" id="FormAddReviewRatingService" value="0;2.5" /></span>
									</div>
									<script type="text/javascript">jQuery("#FormAddReviewRatingService").slider({ from: 0, to: 5, step: 0.1, smooth: true, round: 1, dimension: "", skin: "round" , showStartPoint: false });</script>
									
									<div class="layout-slider margtop10 wh100percent">
									<span class="cstyle01"><input type="slider" name="rating_sleep_quality" id="FormAddReviewRatingSleepQuality" value="0;2.5" /></span>
									</div>
									<script type="text/javascript" >jQuery("#FormAddReviewRatingSleepQuality").slider({ from: 0, to: 5, step: 0.1, smooth: true, round: 1, dimension: "", skin: "round" , showStartPoint: false });</script>
									
									<div class="layout-slider margtop10 wh100percent">
									<span class="cstyle01"><input type="slider" name="rating_price" id="FormAddReviewRatingPrice" value="0;2.5" /></span>
									</div>
									<script type="text/javascript">jQuery("#FormAddReviewRatingPrice").slider({ from: 0, to: 5, step: 0.1, smooth: true, round: 1, dimension: "", skin: "round" , showStartPoint: false });</script>
									
									<select class="my-form-control margtop10" name="evaluation" id="FormAddReviewEvalution">
									<?php
										$evaluation_types = Reviews::GetEvaluations();
										if(is_array($evaluation_types)){
											foreach($evaluation_types as $key=> $evaluation_type){
												echo '<option value="'.$key.'" '.($key == 2 ? ' selected="selected"' : '').'>'.$evaluation_type.'</option>';
											}
										}
									?>
									</select>
									<input type="text" class="form-control margtop10 left" name="title" id="FormAddReviewTitle" placeholder="">
									
									<textarea class="form-control margtop10 left" name="positive_comments" id="FormAddReviewPositiveComments" rows="3"></textarea>
									
									<textarea class="form-control margtop10 left" name="negative_comments" id="FormAddReviewNegativeComments" rows="3"></textarea>
									
									<div class="clearfix"></div>
									<button type="button" id="FormAddReviewButton" class="btn-search4 margtop20"><?= _SUBMIT; ?></button>	

									<br/>
									<br/>
									<br/>
									<br/>
									
								</div>							
							</form>
						</div>
						<script>
							jQuery(document).ready(function(){
								jQuery("#FormAddReviewButton").click(function(){
									var $ = jQuery;
									var token = "<?= htmlentities(Application::Get('token')); ?>";
									var hotel_id = <?= (int)$hotel_id; ?>;
									var customer_id = <?= (int)$customer_id; ?>;
									var rating_cleanliness = $("#FormAddReviewRatingCleanliness").val().split(';')[1];
									var rating_room_comfort = $("#FormAddReviewRatingRoomConfort").val().split(';')[1];
									var rating_location = $("#FormAddReviewRatingLocation").val().split(';')[1];
									var rating_service = $("#FormAddReviewRatingService").val().split(';')[1];
									var rating_sleep_quality = $("#FormAddReviewRatingSleepQuality").val().split(';')[1];
									var rating_price = $("#FormAddReviewRatingPrice").val().split(';')[1];
									var evaluation = $("#FormAddReviewEvalution").val();
									var title = $("#FormAddReviewTitle").val();
									var positive_comments = $("#FormAddReviewPositiveComments").val();
									var negative_comments = $("#FormAddReviewNegativeComments").val();
									jQuery.ajax({
										url: "ajax/reviews.ajax.php",
										global: false,
										type: "POST",
										data: ({
											token: token,
											act: "send",
											hotel_id : hotel_id,
											customer_id : customer_id,
											rating_cleanliness : rating_cleanliness,
											rating_room_comfort : rating_room_comfort,
											rating_location : rating_location,
											rating_service : rating_service,
											rating_sleep_quality : rating_sleep_quality,
											rating_price : rating_price,
											evaluation : evaluation,
											title : title,
											positive_comments : positive_comments,
											negative_comments : negative_comments
										}),
										dataType: "json",
										async: true,
										error: function(html){
											console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
										},
										success: function(data){
											if(data.length == 0){
												console.log("<?= htmlentities(_NO_MATCHES_FOUND); ?>");
											}else{
												console.log(data)
											}
										}
									});
								});
							});
						</script>
                    <?php }elseif(false){ ?>
						<div class="hpadding20">
						<?php draw_message(_REVIEW_YOU_HAVE_REGISTERED.' '._REVIEW_LINK_LOGIN); ?>
						</div>
						<br/><br/>
                    <?php } ?>
					<div class="clearfix"></div>

				</div>	
            </div>
        </div>
        
        <div class="col-md-4 pagecontainer3">
            
			<div class="lastbookingbox">
				<?php
					$result = Bookings::GetLastBookingForHotels(array($hotel_id));
					if($result[1] > 0){
						$unix_time_created = strtotime($result[0][0]['created_date']);
						$count_hours = (int)((time() - $unix_time_created) / 3600);
						if($count_hours < 24){
							$type_booking = $count_hours < 1 ? _JUST_BOOKED : ($count_hours > 1 ? $count_hours.' '._HOURS : _HOUR).' '._AGO;
						}else{
							$count_days = (int)($count_hours / 24);
							$type_booking = strtolower(($count_days == 1 ? '1 '._DAY : $count_days.' '._DAYS).' '._AGO);
						}
						$message = str_replace('{type_booking}', $type_booking, _LAST_BOOKING_HOTEL_WAS);
						echo '<div class="msg notice">'.$message.'</div>';
					}
				?>
			</div>
			<?php
				$review = Reviews::GetRandomReview($hotel_id);
				if($review[1] > 0){
					///$average_rating = (($review[0]['rating_cleanliness'] + $review[0]['rating_room_comfort'] + $review[0]['rating_location'] + $review[0]['rating_service'] + $review[0]['rating_sleep_quality'] + $review[0]['rating_price']) / 6);
					echo '<div class="pagecontainer2 reviewbox">';
					echo '<div class="cpadding0 mt-10">';
						echo '<span class="icon-quote"></span>';        
						echo '<p class="opensans size16 grey2">'.substr_by_word((!empty($review[0]['positive_comments']) ? $review[0]['positive_comments'] : $review[0]['negative_comments']), 120, true).'<br/>';
						echo '<span class="lato orange bold size13"><i>'.(!empty($review[0]['author_name']) ? $review[0]['author_name'] : '').' '.(!empty($review[0]['author_country']) ? $review[0]['author_country'] : '').'</i></span></p>';
						echo '<a onclick="$(\'#tab-reviews\').click();scrollToElement(\'tab-reviews\');" data-toggle="tab" href="javascript:void(0)" class="grey">'._VIEW_ALL.'</a>';
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

        </div>            
    </div>    

<?php } ?>    
</div>
<!-- END OF CONTENT -->
