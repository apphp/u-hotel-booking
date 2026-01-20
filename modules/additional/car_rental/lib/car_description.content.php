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

$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : '';

$car = Vehicles::GetVehicleInfo($car_id);

if(!empty($car) && $car['is_active']){

	$currency_rate = Application::Get('currency_rate');
	$currency_format = get_currency_format();
	$default_currency = Currencies::GetDefaultCurrencyInfo();
	$lang = Application::Get('lang');

	$image 				= 'images/vehicles/'.(!empty($car['image']) ? $car['image'] : 'no_image.png');
	$type_image 		= 'images/vehicle_types/'.(!empty($car['type_image']) ? $car['type_image'] : 'no_image.png');
	$make_model 		= (!empty($car['make']) ? $car['make'] : '').(!empty($car['model']) ? ' '.$car['model'] : '');
	$type_name 			= !empty($car['type_name']) ? $car['type_name'] : '';
	$price_per_day 		= !empty($car['price_per_day']) ? $car['price_per_day'] : '';
	$price_per_hour 	= !empty($car['price_per_hour']) ? $car['price_per_hour'] : '';
	$default_distance	= !empty($car['default_distance']) ? $car['default_distance'] : '';
	$distance_extra_price = !empty($car['distance_extra_price']) ? $car['distance_extra_price'] : '';
	$fuel_type			= !empty($car['fuel_type_id']) ? get_fuel_types($car['fuel_type_id']) : '';
	$transmission		= !empty($car['transmission']) ? get_transmissions($car['transmission']) : '';
	$total_price 		= $price_per_day;
	$vehicle_description = !empty($car['vehicle_description']) ? $car['vehicle_description'] : '';
	$agency_phone 		= '';
	$agency_email 		= '';
	$agency_name 		= '';
	$agency_map_code 	= '';
	$agency_description = '';
	$terms_and_conditions = '';

	$car_agency = CarAgencies::GetAgencyFullInfo($car['agency_id'], $lang);
	if(is_array($car_agency) && count($car_agency) > 0){
		$agency = $car_agency;
		$agency_phone = !empty($agency['phone']) ? $agency['phone'] : '';
		$agency_email = !empty($agency['email']) ? $agency['email'] : '';
		if(ModulesSettings::Get('car_rental', 'show_agency_in_search_result') == 'yes'){
			$agency_name = !empty($agency['name']) ? $agency['name'] : '';	
		}
		$agency_map_code = !empty($agency['map_code']) ? $agency['map_code'] : '';
		$agency_description = !empty($agency['description']) ? $agency['description'] : '';
		$terms_and_conditions = !empty($agency['terms_and_conditions']) ? $agency['terms_and_conditions'] : '';
	}
	
	if($car_agency['is_active']){	
?>
	<div class="container">
		<div class="container pagecontainer offset-0">

			<!-- SLIDER -->
			<div class="col-md-8 details-slider">

				<div class="wh100percent center">
				<img src="<?= $image; ?>" class="fwimg padding20" alt="Vehicle">
				</div>

				<div class="cposalert">
					<!--a href="#" class="lblue" rel="popover" id="infottip2" data-content="When you pick up your car a deposit is usually held on your card for your excess which may be charged if the vehicle is damaged. Take our Damage Excess Refund and if you have any bumps or scrapes we'll refund any deductions from your excess. " data-original-title="Excess Protection -  Excellent Value, Best Cover, Peace of Mind">What is this?</a-->
					<!--div class="alert alert-danger alertcust"><span class="glyphicon glyphicon-remove-sign"></span> Warning! Your excess is currently unprotected <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button></div-->
				</div>

			</div>
			<!-- END OF SLIDER -->

			<!-- RIGHT INFO -->
			<div class="col-md-4 detailsright offset-0">
				<div class="padding20">
					<div class="wh70percent left">
						<span class="opensans size18 dark bold"><?= $make_model; ?></span><br>
						<span class="opensans size13 grey"><?= $type_name; ?></span><br>
						<span class="opensans size13 grey"><?= $agency_name; ?></span>
					</div>
					<div class="wh30percent right">
						<table class="right">
							<tbody><tr>
							<td><span class="size14 grey bold"><?= Currencies::PriceFormat($price_per_day * $currency_rate, '', '', $currency_format); ?></span></td>
							<td><span class="size12 lgrey"><i><?= _PER_DAY; ?></i></span></td>
							</tr>
						</tbody></table>
					</div>

					<div class="clearfix"></div>
				</div>

				<div class="line3"></div>

				<div class="line3"></div>

				<div class="padding20 car-reservation-block">
					<?= CarAgencies::DrawSearchAvailabilityBlock('details'); ?>
				<script>
				$(document).ready(function(){
					var time_pick_up = null;
					var time_pick_up_arr = [];
					var time_drop_off = null;
					var time_drop_off_arr = [];
					var price_day = <?= $price_per_day; ?>;
					var price_hour = <?= $price_per_hour; ?>;
					var sumbol = "<?= $default_currency['symbol']; ?>";

					$('input.mySelectCalendar').change(function(e){
						var date_time = this.value;
						var hour = '';
						var day = '';
						var mouth = '';
						var year = '';
						var diff = '';
						var diff_days = '';
						var diff_hours = '';
						var delta = ''
						var date_arr = date_time.split('/');

						if(date_arr.length != 3){
							return null;
						}

						mouth = date_arr[0];
						day = date_arr[1];
						year = date_arr[2];


						// If Pick Up
						if($(this).hasClass('car_pick_up_date')){
							hours_minute = $('select.car_pick_up_time').val();
							delta = hours_minute % 1;
							minutes = delta * 60;
							hours = hours_minute - delta;
							time_pick_up = new Date(year, mouth, day, hours, minutes, 0, 0);
							time_pick_up_arr = [year, mouth, day, hours, minutes];
							if(time_drop_off != null){
								setPrice(time_drop_off - time_pick_up);
							}
						}else{
						// If Drop Off
							hours_minute = $('select.car_drop_off_time').val();
							delta = hours_minute % 1;
							minutes = delta * 60;
							hours = hours_minute - delta;
							time_drop_off = new Date(year, mouth, day, hours, minutes, 0, 0);
							time_drop_off_arr = [year, mouth, day, hours, minutes];
							if(time_pick_up != null){
								setPrice(time_drop_off - time_pick_up);
							}
						}
					});
					$('select.hasCustomSelect').on('change', function(e) {
						var hours_minute = this.value;
						var hour = '';
						var day = '';
						var mouth = '';
						var year = '';
						var diff = '';
						var diff_days = '';
						var diff_hours = '';
						var delta = ''
						var date_arr = '';

						delta = hours_minute % 1;
						minutes = delta * 60;
						hours = hours_minute - delta;

						// If Pick Up
						if($(this).hasClass('car_pick_up_time')){
							date_time = $('input.car_pick_up_date').val();
						    date_arr = date_time.split('/');
							if(date_arr.length != 3){
								return null;
							}
							mouth = date_arr[0];
							day = date_arr[1];
							year = date_arr[2];
							time_pick_up = new Date(year, mouth, day, hours, minutes, 0, 0);
							time_pick_up_arr = [year, mouth, day, hours, minutes];
							if(time_drop_off != null){
								setPrice(time_drop_off - time_pick_up);
							}
						}else{
							// If Drop Off
							date_time = $('input.car_drop_off_date').val();
						    date_arr = date_time.split('/');
							if(date_arr.length != 3){
								return null;
							}
							mouth = date_arr[0];
							day = date_arr[1];
							year = date_arr[2];
							time_drop_off = new Date(year, mouth, day, hours, minutes, 0, 0);
							time_drop_off_arr = [year, mouth, day, hours, minutes];
							if(time_pick_up != null){
								setPrice(time_drop_off - time_pick_up);
							}
						}
					});

					function setPrice(time){
						diff = time / 1000;
						if(diff < 0){
							price = 0;
						}else{
							delta = diff % (24 * 60 * 60);
							diff_hours = delta / (60 * 60);
							diff_day = (diff - delta) / (24 * 60 * 60);
							price = diff_day * price_day + diff_hours * price_hour;
						}
						if($('#cars-price').length > 0){
							$('#cars-price').val(price);
						}else{
							$('#cars-reservation-form').prepend("<input type='hidden' id='cars-price' name='price' value='" + price + "'/>");
						}
						$('#cars-total-price').html(sumbol+price)
					}
				 });
				</script>
				</div>

				<!--div class="line3"></div-->

				<div class="padding20">

					<!--table class="wh100percent size12 bold dark">
						<tbody><tr>
							<td>Mazda 3 Economy</td>
							<td class="textright">$240.00</td>
						</tr>
						<tr>
							<td>GPS</td>
							<td class="textright">$18.00</td>
						</tr>
					</tbody></table-->
					<!--div class="fdash mt10"></div><br>
					<span class="size14 dark bold"><?php //echo _TOTAL_PRICE; ?>:</span>
					<span id="cars-total-price" class="size24 green bold right margtop-5"><?php //echo Currencies::PriceFormat($total_price, '', '', $currency_format); ?></span-->
				</div>

				<div class="line3"></div>

				<div class="clearfix"></div>

				<div class="hpadding20">
					<a href="#" id="button-book-now" class="booknow margtop20 btnmarg"><?= _BOOK_NOW; ?></a><br>
					<script>
						$('#cars-reservation-form').prepend("<input type='hidden' name='car_id' value='" + <?= $car_id ?> + "'/>");
						$('#button-book-now').click(function(){
							$('#cars-reservation-form').submit();
							return false;
						});
					</script>
				</div>
			</div>
			<!-- END OF RIGHT INFO -->

		</div>
		<!-- END OF container-->

		<div class="container mt25 offset-0">

			<!-- CONTENT -->
			<div class="col-md-8 pagecontainer2 offset-0">

				<ul class="nav nav-tabs" id="myTab">
					<li class="active"><a data-toggle="tab" href="#details"><span class="details"></span><span class="hidetext">&nbsp;<?= _VEHICLE_DETAILS; ?></span>&nbsp;</a></li>
					<?php if(ModulesSettings::Get('car_rental', 'show_agency_in_search_result') == 'yes'){ ?>
						<li class=""><a data-toggle="tab" href="#description"><span class="summary"></span><span class="hidetext">&nbsp;<?= _AGENCY_INFO; ?></span>&nbsp;</a></li>
					<?php } ?>
					<li class=""><a data-toggle="tab" href="#conditions"><span class="rates"></span><span class="hidetext">&nbsp;<?= _TERMS; ?></span>&nbsp;</a></li>
				</ul>
	
				<div class="tab-content4">
					
					<!-- TAB 1 -->
					<div id="details" class="tab-pane active in">

						<!-- Collapse 1 -->
						<button type="button" class="collapsebtn2" data-toggle="collapse" data-target="#collapse1">
						<?= _DESCRIPTION; ?>: <span class="collapsearrow"></span>
						</button>
						<br><br>

						<?php if(!empty($vehicle_description)){ ?>
						
							<div id="collapse1" class="collapse in">	
								<div class="hpadding20">
									<?= $vehicle_description; ?>
								</div>
								<br>
							</div>							
							<div class="line4"></div>
						
						<?php } ?>

						<!-- Collapse 1 -->
						<button type="button" class="collapsebtn2" data-toggle="collapse" data-target="#collapse2">
						<?= _VEHICLE_DETAILS; ?>: <span class="collapsearrow"></span>
						</button>
						<br><br>
						
						<div id="collapse2" class="collapse in">

							<div class="hpadding20">
								<i class="glyphicon glyphicon-user"></i> <?= _GUESTS; ?> <?= $car['passengers']; ?>&nbsp;&nbsp;&nbsp;
								<i class="glyphicon glyphicon-print"></i> <?= _DOORS; ?> <?= $car['doors']; ?>&nbsp;&nbsp;&nbsp;
								<i class="glyphicon glyphicon-th"></i> <?= _GEAR; ?> <?= $car['transmission']; ?>&nbsp;&nbsp;&nbsp;
								<i class="glyphicon glyphicon-briefcase"></i> <?= _BAGGAGE; ?> <?= $car['luggages']; ?>
							</div>
							<div class="line4"></div>

							<div class="hpadding20">
								<?= $agency_name; ?><br>
								<span><?= $type_name; ?></span> <img src="<?= $type_image; ?>" class="fwimg" alt="Vehicle Type" style="width:48px"><br>
							</div>
							<div class="line4"></div>

							<div class="hpadding20">
								<?= _FUEL_TYPE.': '.$fuel_type; ?><br>
								<?= _TRANSMISSION.': '.$transmission; ?><br>
								<?= _PRICE_PER_DAY.': '.Currencies::PriceFormat($price_per_day, '', '', $currency_format); ?><br>
								<?= _PRICE_PER_HOUR.': '.Currencies::PriceFormat($price_per_hour, '', '', $currency_format); ?><br>
								<?= _DEFAULT_DISTANCE.': '.$default_distance.' '._DISTANCE_UNITS; ?><br>
								<?= _DISTANCE_EXTRA_PRICE.': '.Currencies::PriceFormat($distance_extra_price, '', '', $currency_format); ?><br>
							</div>
							<br>								
						</div>
					</div>


					<!-- TAB 2 -->
					<?php if(ModulesSettings::Get('car_rental', 'show_agency_in_search_result') == 'yes'){ ?>
					<div id="description" class="tab-pane fade">
						<div class="hpadding20">
							<?= $agency_description; ?>
							<div class="clearfix"></div>
							<br>
						</div>
					</div>
					<?php } ?>					
		
					<!-- TAB 3 -->
					<div id="conditions" class="tab-pane fade">
						<!-- Collapse 1 -->
						<div class="hpadding20">
							<?= $terms_and_conditions; ?>
							<div class="clearfix"></div>
							<br>
						</div>
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
						<?php
							// In Agemcy info is hidden - show default contact info
							if(ModulesSettings::Get('car_rental', 'show_agency_in_search_result') == 'no'){
								$agency_email = ModulesSettings::Get('car_rental', 'default_contant_email');
								$agency_phone = ModulesSettings::Get('car_rental', 'default_contant_phone');
							}	
						?>						
						<p class="opensans size30 lblue xslim"><?= $agency_phone; ?></p>
						<span class="grey2"><a href="mailto:<?= $agency_email; ?>"><?= $agency_email; ?></a></span>
					</div>
				</div>
				<br>


			</div>
		</div>
	</div>

<?php
	}else{
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
}else{
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
?>
