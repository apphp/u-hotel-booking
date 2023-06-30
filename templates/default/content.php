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

	// Prepare Main Content
	ob_start();
	if(Application::Get('page') != '' && Application::Get('page') != 'home'){							
		if(file_exists('page/'.Application::Get('page').'.php')){	 
			include_once('page/'.Application::Get('page').'.php');
		}else{
			include_once('page/404.php');
		}
	}else if(Application::Get('customer') != ''){					
		if(Modules::IsModuleInstalled('customers') && file_exists('customer/'.Application::Get('customer').'.php') && !(Application::Get('customer') == 'travel_login' && TRAVEL_AGENCY_LOGIN != 'travel_login')){	
			include_once('customer/'.Application::Get('customer').'.php');
		}else if(Application::Get('customer') == TRAVEL_AGENCY_LOGIN){
			include_once('customer/agency_login.php');
		}else{
			include_once('customer/404.php');
		}
	}else if((Application::Get('hotel_owner') != '') && file_exists('hotel_owner/'.Application::Get('hotel_owner').'.php')){
		include_once('hotel_owner/'.Application::Get('hotel_owner').'.php');
	}else if((Application::Get('admin') != '') && file_exists('admin/'.Application::Get('admin').'.php')){
		include_once('admin/'.Application::Get('admin').'.php');
	}else{
		if(Application::Get('template') == 'admin'){
			include_once('admin/home.php');
		}else{
			include_once('page/pages.php');										
		}
	}
	$main_content = ob_get_contents();
	ob_end_clean();

?>
        
<!-- CONTENT -->
<div class="container">
    <div class="container pagecontainer offset-0">	

	<?php
		$col_rightcontent = 12;
		if(!(Modules::IsModuleInstalled('conferences') && ModulesSettings::Get('conferences', 'is_active') != 'no' && Application::Get('page') == 'conference_registration' && $activeStep == 3)){
			$col_rightcontent = 9;
	?>
        <!-- FILTERS -->
        <div class="col-md-3 filters offset-0">

			<?php if(!empty($rooms_count['rooms'])){
				
				// Prepare property type
				$property_type_id = isset($_REQUEST['property_type_id']) ? (int)$_REQUEST['property_type_id'] : '';
				$property_types = Application::Get('property_types');
				$property_type = '';
				foreach($property_types as $key => $val){
					if(isset($val['id']) && $val['id'] == $property_type_id){
						$property_type = $val['property_code'];
						break;
					}
				}				

				if($property_type == 'hotels'){
					$found_property	= _FOUND_HOTELS;
				}else if($property_type == 'villas'){
					$found_property	= _FOUND_VILLAS;
				}else{
					$found_property	= _FOUND_PROPERTIES;
				}
				
				$currency_rate = Application::Get('currency_rate');
				$currency_format = get_currency_format();
			?>
				<div class="filtertip">
					<div class="padding20">
						<p class="size14"><?= $found_property; ?>: <b><?= $rooms_count['hotels'];?></b></p>
						<p class="size14"><?= _TOTAL_ROOMS; ?>: <b><?= $rooms_count['rooms'];?></b></p>
						<p class="size22 bold"><span class="size14 normal darkblue"><?= _STARTING;?></span> <span class="countprice"><?= Currencies::PriceFormat($rooms_count['min_price_per_hotel'] * $currency_rate, '', '', $currency_format);?></span> <span class="size14 normal darkblue">/<?= _DAY;?></span></p>
					</div>
					<div style="bottom: -9px;" class="tip-arrow"></div>
				</div>
			<?php } ?>
			
			<!-- Use any element to open the sidenav -->
			<div class="filter-results-wrapper">
				<span onclick="goBack()" class="filter-results pull-left">&laquo; <?= _BUTTON_BACK; ?></span>
				<span onclick="toggleNav()" class="filter-results pull-right cursor-pointer">					
					<?php if($objLogin->IsLoggedIn()){ ?>
						<?php if(in_array(Application::Get('page'), array('check_cars_availability', 'check_availability'))) { ?>
							<span class="filter-link"><?= _FILTER; ?> &nbsp; <i class="filter-icon pull-right"></i></span>
						<?php }else{ ?>
							<span class="filter-link"><?= _MY_ACCOUNT; ?> / <?= _RESERVATION; ?> &nbsp; <i class="customer-icon pull-right"></i></span>
						<?php } ?>
					<?php }else{ ?>
						<span class="filter-link"><?= _FILTER; ?> &nbsp; <i class="filter-icon pull-right"></i></span>
					<?php } ?>
				</span>
			</div>
			
			<div id="side-nav" class="sidenav">
				<a href="javascript:void(0)" class="closebtn filter-closebtn" onclick="closeNav()">&times;</a>	
                <!-- LEFT COLUMN -->
                <?php
                    // Draw menu tree
                    Menu::DrawMenu('left');						
                ?>                            
                <!-- END OF LEFT COLUMN -->
            </div>
			<div id="content-main" onclick="closeNav()"></div>
            <!-- END OF BOOK FILTERS -->	

            <div class="clearfix"></div>			
		</div>
		<!-- END OF FILTERS -->
		<?php } ?>

        <!-- LIST CONTENT-->
        <div class="rightcontent col-md-<?= $col_rightcontent; ?> offset-0">
        <?php if(Application::Get('page') == 'check_availability'){
			$arr_evaluation = array(5=>_WONDERFUL, 4=>_VERY_GOOD, 3=>_GOOD, 2=>_NEUTRAL, 1=>_NOT_GOOD, 0=>_NOT_RECOMMENDED);
			$sort_rating = isset($_POST['sort_rating']) && in_array($_POST['sort_rating'], array_keys($arr_evaluation)) && $_POST['sort_rating'] !== '' ? (int)$_POST['sort_rating'] : null;
			if(!empty($rooms_count['rooms'])){
				$additional_sort_by = isset($_POST['additional_sort_by']) && in_array($_POST['additional_sort_by'], array('stars-1-5', 'stars-5-1', 'name-a-z', 'name-z-a', 'price-l-h', 'price-h-l', 'distance-asc', 'distance-desc')) ? strtolower($_POST['additional_sort_by']) : '';
				$sort_price = isset($_POST['sort_price']) && in_array(strtolower($_POST['sort_price']), array('asc', 'desc')) ? strtolower($_POST['sort_price']) : '';
			}
			if(!empty($rooms_count['rooms']) || $sort_rating !== null){
		?>
            <div class="hpadding20">
				<!-- Top filters -->
				<div style="opacity:1;" id="check-availability-topsortby" class="topsortby">
					<div class="col-md-12 offset-0">
						<div class="left wh35percent">
							<div class="left div-label wh30percent mt7"><b><?= _FILTER_BY; ?>:</b> </div>
							<select class="wh55percent my-form-control sort-rating" name="sort_rating">
								<option<?= $sort_rating === null ? ' selected="selected"' : ''; ?> value=""><?= _VISITORS_RATING; ?></option>
								<?php
									foreach($arr_evaluation as $key => $evaluation){
										echo '<option'.($key === $sort_rating ? ' selected="selected"' : '').' value="'.$key.'">'.$evaluation.'</option>';
									}
								?>
							</select>
						</div>
						<?php if(!empty($rooms_count['rooms'])){ ?>					
							<div class="left wh45percent">
								<div class="left div-label wh20percent mt7"><b><?= _SORT_BY; ?>:</b> </div>
								<select class="wh55percent my-form-control sort-by" name="additional_sort_by">
									<optgroup label="<?= _PRICE; ?>">
										<option<?= $sort_by == 'price-l-h' ? ' selected="selected"' : ''; ?> value="price-l-h"><?= _LOWEST_HIGHEST; ?></option>
										<option<?= $sort_by == 'price-h-l' ? ' selected="selected"' : ''; ?> value="price-h-l"><?= _HIGHEST_LOWEST; ?></option>
									</optgroup>
									<optgroup label="<?= _STARS; ?>">
										<option<?= $sort_by == 'stars-1-5' ? ' selected="selected"' : ''; ?> value="stars-1-5"><?= _STARS_1_5; ?></option>
										<option<?= $sort_by == 'stars-5-1' ? ' selected="selected"' : ''; ?> value="stars-5-1"><?= _STARS_5_1; ?></option>
									</optgroup>
								<?php if(SHOW_FILTER_DISTANCE_TO_CENTER_POINT){ ?>
									<optgroup label="<?= _DISTANCE_TO_CENTER_POINT; ?>">
										<option<?= $sort_by == 'distance-asc' ? ' selected="selected"' : ''; ?> value="distance-asc"><?= _ASCENDING; ?></option>
										<option<?= $sort_by == 'distance-desc' ? ' selected="selected"' : ''; ?> value="distance-desc"><?= _DESCENDING; ?></option>
									</optgroup>
								<?php } ?>
									<optgroup label="<?= _RATINGS; ?>">
										<option<?= $sort_by == 'review-asc' ? ' selected="selected"' : ''; ?> value="review-asc"><?= _LOWEST_HIGHEST; ?></option>
										<option<?= $sort_by == 'review-desc' ? ' selected="selected"' : ''; ?> value="review-desc"><?= _HIGHEST_LOWEST; ?></option>
									</optgroup>
									<optgroup label="<?= _NAME; ?>">
										<option<?= $sort_by == 'name-a-z' ? ' selected="selected"' : ''; ?> value="name-a-z"><?= _A_Z; ?></option>
										<option<?= $sort_by == 'name-z-a' ? ' selected="selected"' : ''; ?> value="name-z-a"><?= _Z_A; ?></option>
									</optgroup>
								</select>
							</div>
						<?php } ?>
					</div>
				</div>
				<!-- End of topfilters-->
			</div>
			<script>
				$(document).ready(function(){
				<?php if(!empty($rooms_count['rooms'])){ ?>
					$("div.topsortby select.sort-price").change(function(){
						var sort_price = $("div.topsortby select.sort-price option:selected").val();
						$("#sort_price").val(sort_price);
						$("#reservation-form").submit();
					});
	
					$("div.topsortby select.sort-by").change(function(){
						var sort_by = $("div.topsortby select.sort-by option:selected").val();
						if($("#sort_by option[value="+sort_by+"]").length > 0){
							$("#sort_by").val(sort_by);
						}else{
							$("#sort_by").val("");
						}
						$("#additional_sort_by").val(sort_by);
						$("#reservation-form").submit();
					});
				<?php } ?>
					$("div.topsortby select.sort-rating").change(function(){
						var sort_rating = $("div.topsortby select.sort-rating option:selected").val();
						$("#sort_rating").val(sort_rating);
						$("#reservation-form").submit();
					});
				});
			</script>
		<?php 
				}
			}
		?>
			<div class="hpadding20">
			<!-- MAIN CONTENT -->
			<?php
				// Print Main Content
				echo $main_content;
			?>
			</div>
		</div>
		<!-- END OF LIST CONTENT-->    

    </div>
</div>
<!-- END OF CONTENT -->

