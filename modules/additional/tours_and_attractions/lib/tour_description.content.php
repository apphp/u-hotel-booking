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

$tour_id = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
$r_page = isset($_GET['r_page']) ? (int)$_GET['r_page'] : 0;
$customer_id = $objLogin->GetLoggedID();

$tour_info = Tours::GetTourFullInfo($tour_id, Application::Get('lang'));
//dd($tour_info);

if(!empty($tour_info) && $tour_info['is_active']) {
    $tour_images = ToursImages::GetToursImages($tour_id);
    //dbug($tourImages,1);

    $main_images = '';
    $arr_images  = [];
    if (GALLERY_TYPE == 'carousel') {
        if ( ! empty($tour_images[0][0]['tour_image'])) {
            $arr_images[] = $tour_images[0][0]['tour_image'];
        }
        for ($i = 0; $i < $tour_images[1]; $i++) {
            if ( ! empty($tour_images[0][$i]['item_file'])) {
                $arr_images[] = $tour_images[0][$i]['item_file'];
            }
        }
        if (empty($arr_images)) {
            $arr_images[] = 'no_image.png';
        }
    } else {
        if ( ! empty($tour_images[0][0]['tour_image'])) {
            $main_images .= '<img src="images/tours/'.$tour_images[0][0]['tour_image'].'" alt="tour image" />'."\n";
        }
        for ($i = 0; $i < $tour_images[1]; $i++) {
            if ( ! empty($tour_images[0][$i]['item_file'])) {
                $main_images .= '<img src="images/tours/'.$tour_images[0][$i]['item_file'].'" alt="tour image" />'."\n";
            }
        }
        if (empty($main_images)) {
            $main_images .= '<img src="images/tours/no_image.png" alt="tour image" />'."\n";
        }
    }
    //dbug($main_images,1);
    //dbug($arr_images,1);

    // Get info about wishlist
    $in_wishlist = Wishlist::GetWishListItemInfo($tour_id, 'tour', $customer_id);


    /*
    |---------------------------------------------------------------------------
    | REVIEWS AND RAITING
    |---------------------------------------------------------------------------
    */
    // Calculate averages
    // Current page reviews
    $page_review = $r_page > 0 ? $r_page : 1;
    // Count show reviews
    $num_reviews   = 10;
    $count_reviews = ToursReviews::CountReviews($tour_id);
    // Count pages
    $count_page_reviews = ceil($count_reviews / $num_reviews);
    // Check current page reviews
    $page_review = $page_review > $count_page_reviews ? $count_page_reviews : $page_review;

    $progress_bar_recommended    = 0;
    $progress_bar_ratings        = 0;
    $percent_of_guests_recommend = 0;

    $reviews        = ToursReviews::GetReviews($tour_id, $page_review, $num_reviews);
    $average_rating = ToursReviews::GetAverageRaiting($tour_id, $page_review, $num_reviews);
    $total_reviews  = $average_rating['total_reviews'];

    if ($total_reviews > 0) {
        $percent_of_guests_recommend = round($average_rating['guests_recommend'] / $total_reviews * 100, 2);
        $progress_bar_recommended    = floor((int)($percent_of_guests_recommend / 5) * 5);
        $progress_bar_ratings        = floor((int)(($average_rating['general'] * 20) / 5) * 5);
    }


    $currency_rate    = Application::Get('currency_rate');
    $currency_format  = get_currency_format();
    $default_currency = Currencies::GetDefaultCurrencyInfo();
    $lang             = Application::Get('lang');

    $image = 'images/tours/'.(! empty($tour_info['image']) ? $tour_info['image'] : 'no_image.png');

    $make_model       = (! empty($tour_info['make']) ? $tour_info['make'] : '').(! empty($tour_info['model']) ? ' '.$tour_info['model'] : '');
    $tour_agency_id   = ! empty($tour_info['tour_agency_id']) ? $tour_info['tour_agency_id'] : 0;
    $type_name        = ! empty($tour_info['type_name']) ? $tour_info['type_name'] : '';
    $price_per_day    = ! empty($tour_info['price_per_person']) ? $tour_info['price_per_person'] : 0;
    $tour_description = ! empty($tour_info['description']) ? $tour_info['description'] : '';

    $agency_phone         = '';
    $agency_email         = '';
    $agency_name          = '';
    $terms_and_conditions = '';

    if (ModulesSettings::Get('tours_and_attractions', 'show_agency_in_search_result') !== 'no') {
        $tour_agency = ToursAgencies::GetAgencyFullInfo($tour_agency_id, $lang);
        if (is_array($tour_agency) && count($tour_agency) > 0) {
            $agency               = $tour_agency;
            $agency_phone         = ! empty($agency['phone']) ? $agency['phone'] : '';
            $agency_email         = ! empty($agency['email']) ? $agency['email'] : '';
            $agency_name          = ! empty($agency['name']) ? $agency['name'] : '';
            $terms_and_conditions = ! empty($agency['terms_and_conditions']) ? $agency['terms_and_conditions'] : '';
        }
    }

    if ($objLogin->IsLoggedInAs('owner', 'mainadmin')) {
        $view_condition = empty($tour_info);
    } else {
        $view_condition = empty($tour_info) || $tour_info['is_active'] == 0;
    }

    if ( ! $view_condition) {
        $not_iteration      = Session::Get('session_tour_'.$tour_id.'_not_iteration');
        $not_iteration_time = time() - (int)Session::Get('session_tour_'.$tour_id.'_not_iteration_time');
        if ( ! $not_iteration || $not_iteration_time > 60) {
            // Iteration number of views
            if (Tours::NumberViewsIteration($tour_id)) {
                $tour_info['number_of_views']++;
                Session::Set('session_tour_'.$tour_id.'_not_iteration', '1');
                Session::Set('session_tour_'.$tour_id.'_not_iteration_time', time());
            }
        }
        ?>

		<div class="container">
			<div class="container pagecontainer offset-0">

				<!-- SLIDER -->
                <?php if (GALLERY_TYPE != 'carousel') { ?>
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
								<button id="prev_btn2" class="prev2"><img src="<?= 'templates/'.Application::Get(
                                        'template'
                                    ).'/'; ?>images/spacer.png" alt=""/></button>
								<button id="next_btn2" class="next2"><img src="<?= 'templates/'.Application::Get(
                                        'template'
                                    ).'/'; ?>images/spacer.png" alt=""/></button>
							</div>
						</div> <!-- /c-carousel -->
					</div>
                <?php } elseif ( ! empty($arr_images)) { ?>
					<div class="col-md-8" id="bx-slider">
						<ul id="bxslider">
                            <?php for ($i = 0, $max_count = count($arr_images); $i < $max_count; $i++) { ?>
								<li <?= ($i > 0 ? ' style="display:none;float:none;list-style:none;"'
                                    : ' style="float:none;list-style:none;"'); ?>>
									<img src="images/tours/<?= htmlentities($arr_images[$i]); ?>"/></li>
                            <?php } ?>
						</ul>

						<div id="bx-pager">
                            <?php for ($i = 0, $max_count = count($arr_images); $i < $max_count; $i++) { ?>
								<a data-slide-index="<?= $i; ?>" href=""><img src="images/tours/<?= htmlentities(
                                        $arr_images[$i]
                                    ); ?>"/></a>
                            <?php } ?>
						</div>
						<script>
                            jQuery(document).ready(function () {
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

                    $ratings               = '';
                    $average_property_rate = round($average_rating['general'], 1);

                    if ($average_property_rate >= 4.7) {
                        $ratings = _WONDERFUL;
                    } elseif ($average_property_rate >= 4) {
                        $ratings = _VERY_GOOD;
                    } elseif ($average_property_rate >= 3) {
                        $ratings = _GOOD;
                    } elseif ($average_property_rate >= 2) {
                        $ratings = _NEUTRAL;
                    } elseif ($average_property_rate >= 1) {
                        $ratings = _NOT_GOOD;
                        //}else{
                        //	$ratings = _NOT_RECOMMENDED;
                    }
                    ?>

					<div class="padding20">
                        <?php

                        echo '<div class="pull-right">';
                        if (empty($ratings)) {
                            if ($in_wishlist) {
                                echo Wishlist::GetFavoriteButton(
                                    'tour',
                                    $tour_id,
                                    $customer_id,
                                    Application::Get('token'),
                                    'remove'
                                );
                            } else {
                                echo Wishlist::GetFavoriteButton(
                                    'tour',
                                    $tour_id,
                                    $customer_id,
                                    Application::Get('token'),
                                    'add'
                                );
                            }
                        }
                        echo '</div>';
                        ?>
						<h3 class="lh1"><?= $tour_info['name']; ?></h3>
						<span class="opensans size13 grey"><?= $type_name; ?></span><br>
						<span class="opensans size13 grey"><?= $agency_name; ?></span>
                        <?php

                        if (get_currency_format() == 'european') {
                            $number_of_views = number_format((float)$tour_info['number_of_views'], 0, ',', '.');
                        } else {
                            $number_of_views = number_format((float)$tour_info['number_of_views'], 0, '.', ',');
                        }
                        ?>
						&nbsp; <small class="grey2 right"><?= ($tour_info['number_of_views'] > 1 ? str_replace(
                                '{number}',
                                $number_of_views,
                                _VIEWED_TIMES
                            ) : _VIEWED_TIME); ?></small>
                        <?php

                        echo(! $tour_info['is_active'] ? ' &nbsp;<label class="badge-red">'.htmlspecialchars(_INACTIVE)
                            .'</label>' : '');
                        ?>
						<div class="clearfix"></div>
					</div>
					<div class="line3"></div>


					<!-- RAITING -->
                    <?php if ( ! empty($ratings)) { ?>
						<div class="hpadding10">
							<div class="col-md-7">
								<h3 class="opensans slim green2"><?= $ratings; ?></h3>
							</div>
							<div class="col-md-5 center bordertype-wishlist bordertypeh60">
                                <?php

                                if ($in_wishlist) {
                                    echo Wishlist::GetFavoriteButton(
                                        'tour',
                                        $tour_id,
                                        $customer_id,
                                        Application::Get('token'),
                                        'remove'
                                    );
                                } else {
                                    echo Wishlist::GetFavoriteButton(
                                        'tour',
                                        $tour_id,
                                        $customer_id,
                                        Application::Get('token'),
                                        'add'
                                    );
                                }
                                ?>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="line3 margtop10"></div>
                    <?php } ?>

                    <?php if ($total_reviews > 0) { ?>
						<div class="col-md-6 bordertype1 bordertypeh100 padding20 bordertype-guests-recommend">
                            <?= str_replace(
                                '_PERCENT_',
                                '<span class="opensans size30 bold grey2">'.$percent_of_guests_recommend.'%</span><br>',
                                _PERCENT_OF_GUESTS_RECOMMEND
                            ); ?>
						</div>
						<div class="col-md-6 bordertype2 bordertypeh100 padding20 bordertype-guests-rating">
							<span class="opensans size30 bold grey2"><?= round($average_rating['general'], 1); ?></span>/5<br/>
                            <?= _GUEST_RATINGS; ?>
						</div>
                    <?php } ?>

					<div class="col-md-6 bordertype3 bordertype-reviews">
						<img src="templates/<?= Application::Get('template'); ?>/images/user-rating-<?= round(
                            $average_rating['general']
                        ); ?>.png" alt="user rating"/><br/>
						<a href="javascript:void('reviews');" onclick="$('#tab-reviews').click();scrollToElement('tab-reviews');"><?= $total_reviews
                            .' '.strtolower($total_reviews == 1 ? _REVIEW : _REVIEWS); ?></a>
					</div>

					<div class="col-md-6 bordertype3 bordertype-all-reviews">
						<a <?php if ($objLogin->IsLoggedInAsCustomer()){
                            echo 'href="index.php?customer=my_tour_reviews&mg_action=add&tid='.(int)$tour_id.'"';
                        }else{ ?>href="javascript:void('reviews');" onclick="$('#tab-reviews').click();scrollToElement('tab-reviews');" data-toggle="tab" href="javascript:void(0)"<?php } ?> class="grey"><?= ($objLogin->IsLoggedInAsCustomer(
                            ) ? '+ '._ADD_REVIEW : _ALL_REVIEWS); ?></a>
					</div>

					<div class="clearfix"></div>
					<br/>

					<div class="hpadding20">
						<a href="<?= prepare_link(
                            'pages',
                            'system_page',
                            'contact_us',
                            'index',
                            '',
                            '',
                            '',
                            true
                        ); ?>" class="add2fav margtop5"><?= _CONTACT_US; ?></a>

						<form action="index.php?page=check_tours_availability" id="reservation-form-side" name="reservation-form" method="post">

							<input name="tour_id" value="<?= $tour_id ?>" type="hidden"/>
							<input name="p" id="page_number" value="1" type="hidden"/>
							<input name="token" value="<?= Application::Get('token'); ?>" type="hidden"/>

							<input type="submit" class="booknow margtop20 btnmarg" value="<?= _BOOK_NOW; ?>"/>
						</form>
					</div>
				</div>
				<!-- END OF RIGHT INFO -->

			</div>
			<!-- END OF container-->

			<div class="container mt25 offset-0">

				<!-- CONTENT -->
				<div class="col-md-8 pagecontainer2 offset-0">

					<ul class="nav nav-tabs" id="myTab">
						<li class="active">
							<a data-toggle="tab" href="#details"><span class="details"></span><span class="hidetext">&nbsp;<?= _TOUR_DETAILS; ?></span>&nbsp;</a>
						</li>
						<li class="">
							<a data-toggle="tab" href="#conditions"><span class="rates"></span><span class="hidetext">&nbsp;<?= _TERMS; ?></span>&nbsp;</a>
						</li>
						<li class="">
							<a data-toggle="tab" href="#maps"><span class="maps"></span><span class="hidetext">&nbsp;<?= _MAPS; ?></span>&nbsp;</a>
						</li>
						<li class="<?= ! empty($r_page) ? 'active' : ''; ?>">
							<a id="tab-reviews" data-toggle="tab" href="#reviews"><span class="reviews"></span><span class="hidetext">&nbsp;<?= _REVIEWS; ?></span>&nbsp;</a>
						</li>
					</ul>

					<div class="tab-content4">

						<!-- TAB 1 -->
						<div id="details" class="tab-pane active in">

							<!-- Collapse 1 -->
							<button type="button" class="collapsebtn2" data-toggle="collapse" data-target="#collapse1">
                                <?= _DESCRIPTION; ?>: <span class="collapsearrow"></span>
							</button>
							<br><br>

                            <?php if ( ! empty($tour_description)) { ?>

								<div id="collapse1" class="collapse in">
									<div class="hpadding20">
                                        <?= $tour_description; ?>
									</div>
									<br>
								</div>
								<div class="line4"></div>

                            <?php } ?>

							<!-- Collapse 1 -->
							<button type="button" class="collapsebtn2" data-toggle="collapse" data-target="#collapse2">
                                <?= _TOUR_DETAILS; ?>: <span class="collapsearrow"></span>
							</button>
							<br><br>

							<div id="collapse2" class="collapse in">

								<div class="hpadding20">
                                    <?= $agency_name; ?><br>
									<span><?= $type_name; ?></span> <br>
								</div>

								<div class="hpadding20">
                                    <?= _PRICE.'('.strtolower(_PERSON_PER_DAY).'): '.Currencies::PriceFormat(
                                        $price_per_day,
                                        '',
                                        '',
                                        $currency_format
                                    ); ?><br>
								</div>
								<br>
							</div>
						</div>

						<!-- TAB 2 -->
						<div id="conditions" class="tab-pane fade">
							<!-- Collapse 1 -->
							<div class="hpadding20">
                                <?= $terms_and_conditions; ?>
								<div class="clearfix"></div>
								<br>
							</div>
						</div>

						<!-- TAB 3 -->
						<div id="maps" class="tab-pane fade">
							<div class="hpadding20">
                                <?php Tours::DrawMap($tour_info, ['width' => 100]); ?>
								<div class="clearfix"></div>
							</div>
						</div>

						<!-- TAB 4 -->
						<div id="reviews" class="tab-pane fade<?= ! empty($r_page) ? ' active in' : ''; ?>">

                            <?php if ($total_reviews > 0) { ?>
								<script>//jQuery("html, body").animate({scrollTop: $("#reviews").offset().top}, 1100);</script>
								<div class="hpadding20">
									<div class="col-md-4 offset-0">
										<span class="opensans dark size60 slim lh3"><?= round(
                                                $average_rating['general'],
                                                1
                                            ); ?>/5</span><br/>
										<img src="templates/<?= Application::Get(
                                            'template'
                                        ); ?>/images/user-rating-<?= round(
                                            $average_rating['general']
                                        ); ?>.png" alt="user rating"/>
									</div>
									<div class="col-md-8 offset-0">
										<div class="progress progress-striped">
											<div class="progress-bar progress-bar-success wh<?= $progress_bar_ratings; ?>percent" role="progressbar" aria-valuenow="<?= $progress_bar_ratings; ?>" aria-valuemin="0" aria-valuemax="100">
												<span class="sr-only"><?= str_replace(
                                                        ['_X_', '_Y_'],
                                                        [
                                                            round(
                                                                $average_rating['general'],
                                                                1
                                                            ),
                                                            '5'
                                                        ],
                                                        _X_OUT_OF_Y
                                                    ); ?></span>
											</div>
										</div>
										<div class="progress progress-striped">
											<div class="progress-bar progress-bar-success wh<?= $progress_bar_recommended; ?>percent" role="progressbar" aria-valuenow="<?= $progress_bar_recommended; ?>" aria-valuemin="0" aria-valuemax="100">
												<span class="sr-only"><?= str_replace(
                                                        '_PERCENT_',
                                                        $percent_of_guests_recommend.'%',
                                                        _PERCENT_OF_GUESTS_RECOMMEND
                                                    ); ?></span>
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
									<div class="col-xs-12 col-md-4">
										<div class="scircle left"><?= number_format(
                                                $average_rating['location'],
                                                1
                                            ); ?></div>
										<div class="sctext left margtop15"><?= _LOCATION; ?></div>
										<div class="clearfix"></div>
									</div>
									<div class="col-xs-12 col-md-4">
										<div class="scircle left"><?= number_format(
                                                $average_rating['service'],
                                                1
                                            ); ?></div>
										<div class="sctext left margtop15"><?= _SERVICE_AND_STAFF; ?></div>
										<div class="clearfix"></div>
									</div>
									<div class="col-xs-12 col-md-4">
										<div class="scircle left"><?= number_format(
                                                $average_rating['price'],
                                                1
                                            ); ?></div>
										<div class="sctext left margtop15"><?= _VALUE_FOR_PRICE; ?></div>
										<div class="clearfix"></div>
									</div>

									<div class="vpadding10">
										<span class="mt10 opensans dark size16 bold"><?= _REVIEWS; ?></span>
									</div>

								</div>

								<div class="line2"></div>

                            <?php for ($i = 0;
                            $i < $total_reviews;
                            $i++){ ?>
								<div class="vpadding20 hpadding10">
									<div class="col-md-4 offset-0 center">
										<div class="padding20">
											<div class="bordertype5">
												<div class="circlewrap">
                                                    <?php

                                                    $profile_photo_src = ! empty($reviews[0][$i]['profile_photo_thumb'])
                                                    && file_exists(
                                                        'images/customers/'.$reviews[0][$i]['profile_photo_thumb']
                                                    ) ? $reviews[0][$i]['profile_photo_thumb'] : 'no_image.png';
                                                    ?>
													<img src="images/customers/<?= $profile_photo_src; ?>" class="circleimg" alt="profile photo"/>
													<span><?= round(
                                                            ($reviews[0][$i]['rating_location']
                                                                + $reviews[0][$i]['rating_service']
                                                                + $reviews[0][$i]['rating_price']) / 6,
                                                            1
                                                        ); ?></span>
												</div>
												<span class="dark"><?= _BY; ?> <?= $reviews[0][$i]['author_name']; ?></span><br/>
                                                <?= strtolower(_FROM); ?> <?= $reviews[0][$i]['author_city']
                                                .(! empty($reviews[0][$i]['author_city'])
                                                && $reviews[0][$i]['country_name'] ? ', ' : '')
                                                .$reviews[0][$i]['country_name']; ?><br/>
												<div class="vpadding10 hpadding20">
													<span class="green "><?= ToursReviews::GetEvaluation(
                                                            $reviews[0][$i]['evaluation']
                                                        ); ?></span>
                                                    <?php if ($reviews[0][$i]['evaluation'] > 4) { ?>
														&nbsp;
														<img src="templates/default/images/check.png" alt="check"/>
                                                    <?php } ?>
												</div>
											</div>

										</div>
									</div>
									<div class="col-md-8 offset-0">
										<div class="padding20">
										<span class="opensans size16 dark">
											<?= $reviews[0][$i]['title']; ?>
										</span>
											<br/>
											<span class="opensans size13 lgrey"><?= _POSTED_ON; ?> <?= format_datetime(
                                                    $reviews[0][$i]['date_created'],
                                                    '',
                                                    '',
                                                    true
                                                ); ?>
                                                <?php if ($reviews[0][$i]['customer_id'] == $customer_id) { ?>
													<span class="your-review green">(<?= _IT_IS_YOUR_REVIEW; ?>)</span>
                                                <?php } ?>
											</span><br/>
                                            <?= ! empty($reviews[0][$i]['positive_comments'])
                                                ? '<p><span class="badge badge-green">+</span> '
                                                .$reviews[0][$i]['positive_comments'] : '</p>'; ?>
                                            <?= ! empty($reviews[0][$i]['negative_comments'])
                                                ? '<p><span class="badge badge-gray">-</span> '
                                                .$reviews[0][$i]['negative_comments'] : '</p>'; ?>
											<ul class="circle-list customer-circle-list">
												<li title="<?= _LOCATION; ?>"><?= $reviews[0][$i]['rating_location']; ?></li>
												<li title="<?= _SERVICE_AND_STAFF; ?>"><?= $reviews[0][$i]['rating_service']; ?></li>
												<li title="<?= _VALUE_FOR_PRICE; ?>"><?= $reviews[0][$i]['rating_price']; ?></li>
											</ul>
                                            <?php if ( ! empty($reviews[0][$i]['admin_answer'])) {
                                                echo '<div class="admin-answer">';
                                                echo '<span class="opensans size13 dark">'._ADMIN_ANSWER.':</span>';
                                                echo '<p>'.$reviews[0][$i]['admin_answer'].'</p>';
                                                echo '</div>';
                                            }
                                            ?>
										</div>
                                        <?php if ( ! empty($reviews[0][$i]['image_file_1'])
                                            || ! empty($reviews[0][$i]['image_file_2'])
                                            || ! empty($reviews[0][$i]['image_file_3'])
                                        ) { ?>
											<div class="padding20">
                                                <?php

                                                $image_dir = 'images/tours_reviews/';
                                                $num_img   = 0;
                                                ?>
                                                <?php if ( ! empty($reviews[0][$i]['image_file_1'])
                                                    && is_file(
                                                        $image_dir.$reviews[0][$i]['image_file_1']
                                                    )
                                                ) { ?>
													<a href="<?= $image_dir
                                                    .$reviews[0][$i]['image_file_1']; ?>" rel="lyteshow[720 480](review<?= $reviews[0][$i]['id']; ?>)" title="Picture #<?= ++$num_img; ?>">
														<img src="<?= $image_dir
                                                        .$reviews[0][$i]['image_file_1_thumb']; ?>" height="45px"/>
													</a>
                                                <?php } ?>
                                                <?php if ( ! empty($reviews[0][$i]['image_file_2'])
                                                    && is_file(
                                                        $image_dir.$reviews[0][$i]['image_file_2']
                                                    )
                                                ) { ?>
													<a href="<?= $image_dir
                                                    .$reviews[0][$i]['image_file_2']; ?>" rel="lyteshow[720 480](review<?= $reviews[0][$i]['id']; ?>)" title="Picture #<?= ++$num_img; ?>">
														<img src="<?= $image_dir
                                                        .$reviews[0][$i]['image_file_2_thumb']; ?>" height="45px"/>
													</a>
                                                <?php } ?>
                                                <?php if ( ! empty($reviews[0][$i]['image_file_3'])
                                                    && is_file(
                                                        $image_dir.$reviews[0][$i]['image_file_3']
                                                    )
                                                ) { ?>
													<a href="<?= $image_dir
                                                    .$reviews[0][$i]['image_file_3']; ?>" rel="lyteshow[720 480](review<?= $reviews[0][$i]['id']; ?>)" title="Picture #<?= ++$num_img; ?>">
														<img src="<?= $image_dir
                                                        .$reviews[0][$i]['image_file_3_thumb']; ?>" height="45px"/>
													</a>
                                                <?php } ?>
											</div>
                                        <?php } ?>
									</div>
									<div class="clearfix"></div>
								</div>

								<div class="line2"></div>
                            <?php } ?>
                            <?php if ($count_page_reviews > 1){ ?>
								<div class="col-md-12 pull-right go-right">
									<ul class="pagination right paddingbtm20">
                                        <?php

                                        $link        = 'index.php?page=tours&tid='.$tour_id.'&r_page=';
                                        $r_page_end  = $count_page_reviews;
                                        $r_show_prev = true;
                                        $r_show_next = true;

                                        if ($page_review == 1) {
                                            // Show start pages "1 2 3 ... 7 >>"
                                            if ($page_review + 2 < $r_page_end) {
                                                $r_show_pages = [1, $page_review + 1, $page_review + 2, $r_page_end];
                                            } else {
                                                $r_show_pages = range(1, $r_page_end);
                                            }
                                            $r_show_prev = false;
                                            $r_show_next = $page_review < $r_page_end ? true : false;
                                        } elseif ($page_review == $r_page_end) {
                                            // Show end pages "<< 1 ... 5 6 7"
                                            if ($page_review - 2 > 1) {
                                                $r_show_pages = [1, $page_review - 2, $page_review - 1, $r_page_end];
                                            } else {
                                                $r_show_pages = range(1, $r_page_end);
                                            }
                                            $r_show_next = false;
                                        } else {
                                            // "<< 1 ... 3 4 5 ... 7 >>"
                                            $r_show_pages = [
                                                1,
                                                $page_review - 1,
                                                $page_review,
                                                $page_review + 1,
                                                $r_page_end
                                            ];
                                            $r_show_pages = array_unique($r_show_pages);
                                        }

                                        // Link Prev
                                        if ($r_show_prev) {
                                            echo '<li><a href="'.$link.($page_review - 1).'" data-page="'.($page_review
                                                    - 1).'">«</a></li>';
                                        }

                                        // The variable needed to display "..."
                                        $r_page_prev = $r_show_prev[0];
                                        foreach ($r_show_pages as $r_page) {
                                            if ($r_page_prev < ($r_page - 1)) {
                                                // Link to page between
                                                echo '<li><a href="'.$link.$r_page.'" data-page="'.(int)(($r_page_prev
                                                            + $r_page) / 2).'">...</a></li>';
                                            }
                                            if ($r_page != $page_review) {
                                                echo '<li><a href="'.$link.$r_page.'" data-page="'.$r_page.'">'.$r_page
                                                    .'</a></li>';
                                            } else {
                                                // Active page
                                                echo '<li class="active"><a href="javascript:void(0);" data-page="0">'
                                                    .$r_page.'</a></li>';
                                            }
                                            $r_page_prev = $r_page;
                                        }

                                        // Link Next
                                        if ($r_show_next) {
                                            echo '<li><a href="'.$link.($page_review + 1).'" data-page="'.($page_review
                                                    + 1).'">»</a></li>';
                                        }
                                        ?>
									</ul>
								</div>
                            <?php } ?>
							<br/>
							<br/>
                            <?php }else{ ?>
								<div class="hpadding20">
                                    <?php draw_message(
                                        str_replace('_TOUR_NAME_', $tour_info['name'], _TOUR_NOT_REVIEWED_YET)
                                    ); ?>
								</div>
							<br/>
                                <?php if ( ! $objLogin->IsLoggedInAsCustomer()) { ?>
								<div class="hpadding20">
                                    <?php draw_message(_MUST_BE_LOGGED); ?>
								</div>
							<br/><br/>
                            <?php }else { ?>
								<div class="hpadding20" id="title-add-review">
										<span class="opensans dark size16 bold">
											&nbsp; <a href="index.php?customer=my_tour_reviews&mg_action=add&tid=<?= (int)$tour_id ?>" class="grey">+ <?= _ADD_REVIEW ?></a>
										</span>
								</div>
                            <?php } ?>
                            <?php } ?>

							<div class="clearfix"></div>

						</div>

					</div>

				</div>
				<!-- END OF CONTENT -->

				<div class="col-md-4">

					<div class="pagecontainer2 pbottom15 needassistancebox">
						<div class="cpadding1">
							<span class="icon-help"></span>
							<h3 class="opensans"><?= _NEED_ASSISTANCE; ?></h3>
							<p class="size14 grey"><?= _NEED_ASSISTANCE_TEXT; ?></p>
							<p class="opensans size30 lblue xslim"><?= $agency_phone; ?></p>
							<span class="grey2"><a href="mailto:<?= $agency_email; ?>"><?= $agency_email; ?></a></span>
						</div>
					</div>
					<br>


				</div>
			</div>
		</div>

        <?php
    } else {
        ?>
		<div class="container">
			<div class="container pagecontainer offset-0">
				<div class="col-md-12">
					<h1><?= _PAGE; ?></h1>
                    <?= draw_important_message(_WRONG_PARAMETER_PASSED); ?>
					<br><br>
				</div>
			</div>
		</div>
        <?php
    }
}
?>