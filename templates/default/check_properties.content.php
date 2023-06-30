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

$property_name = '';
$property_code = '';
$property_id = 1;
$property_page = Application::Get('page');

$property_types = Application::Get('property_types');
foreach($property_types as $key => $val){
	if($property_page == 'check_'.$val['property_code']){
		$property_name = $val['name'];
		$property_code = $val['property_code'];
		$property_id = $val['id'];
	}
}

?>

<!-- background -->
<div class="mtslide2 sliderbg1"></div>
<!-- / background -->

<!-- WRAP -->
<div class="wrap ctup check-property-ctup" >
    
	<?php if(is_mobile()){ ?>
	<div id="check-property-form">
		<?php						
			echo !empty($property_name) ? '<h3>'.$property_name.'</h3>' : '';
			if(Modules::IsModuleInstalled('booking')){
				if(ModulesSettings::Get('booking', 'show_reservation_form') == 'yes'){
					echo Rooms::DrawSearchAvailabilityBlock(true, '', '', 8, 3, 'main-vertical', '', '', false, true, true, $property_id);
				}
			}
		?>
	</div>
	<?php }else{ ?>
		<div class="slideup hidden-xs">
			<div class="container z-index100">		
				<div class="slidercontainer <?= $property_code; ?>-slidercontainer offset-3" style="overflow:hidden;height:446px;">
					<div class="row">
						<div class="col-md-4 scolleft">
						<?php						
							echo !empty($property_name) ? '<h3>'.$property_name.'</h3>' : '';
							if(Modules::IsModuleInstalled('booking')){
								if(ModulesSettings::Get('booking', 'show_reservation_form') == 'yes'){
									echo Rooms::DrawSearchAvailabilityBlock(true, '', '', 8, 3, 'main-vertical', '', '', false, true, true, $property_id);
								}
							}
						?>
						</div>
					<?php
						if(Modules::IsModuleInstalled('banners') && ModulesSettings::Get('banners', 'is_active') == 'yes'){
					?>
						<div class="col-md-8 scolright">
							<!--
							#################################
								- THEMEPUNCH BANNER -
							#################################
							-->
							<div class="fullwidthbanner">
								<ul>
									<?php
										$banners = Banners::GetBannersArray(array('', $property_page));
										if(is_array($banners)){
											foreach($banners as $key => $banner){
												
												$image_text_parts = strip_tags(str_replace('<br>', ' ', $banner['image_text']));
												$image_text_parts = explode(' ', $image_text_parts, 2);
												$image_text_part_1 = isset($image_text_parts[0]) ? $image_text_parts[0] : '';
												$image_text_part_2 = isset($image_text_parts[1]) ? $image_text_parts[1] : '';
												
												//<div class="tp-caption  sfl" data-x="0" data-y="10" data-speed="1000" data-start="800" data-easing="easeOutExpo">
												//	<div class="slidecouple"></div>
												//</div>	
												echo '<li data-transition="fade" data-slotamount="1" data-masterspeed="300">';
												
												if(!empty($banner['link_url'])) echo '<a href="'.$banner['link_url'].'">';	
												echo '<img src="'.APPHP_BASE.'images/banners/'.$banner['image_file'].'" alt="" />';
												if(!empty($banner['link_url'])) echo '</a>';
												
												if(ModulesSettings::Get('banners', 'slideshow_caption_html') == 'yes'){
													echo '<div class="tp-caption scrolleffect sft" data-x="center" data-y="100" data-speed="1000" data-start="800" data-easing="easeOutExpo">
															<div class="sboxpurple textcenter">'.$banner['image_text'].'</div>
														</div>	
														<div class="tp-caption sfb" data-x="left" data-y="371" data-speed="1000" data-start="800" data-easing="easeOutExpo">
															<div class="blacklable">
																<h4 class="lato bold white">'.htmlentities($image_text_part_1).'</h4>
																<h5 class="lato grey mt-10">'.htmlentities($image_text_part_2).'</h5>
															</div>
														</div>';
												}
												echo '</li>';	
											}					
										}
									?>
								</ul>
								<div class="tp-bannertimer none"></div>
							</div>                    
						
						<!--
						##############################
						 - ACTIVATE THE BANNER HERE -
						##############################
						-->
						<?php
							$delay = ModulesSettings::Get('banners', 'rotate_delay');
							$delay = !empty($delay) ? $delay * 1000 : 9000;
						?>
						<script type="text/javascript">
							var tpj=jQuery;
							tpj.noConflict();
							tpj(document).ready(function() {
							if (tpj.fn.cssOriginal!=undefined)
								tpj.fn.css = tpj.fn.cssOriginal;
								var api = tpj('.fullwidthbanner').revolution({
									delay:<?= $delay; ?>,
										startwidth:960,
										startheight:446,
										onHoverStop:"on",						// Stop Banner Timet at Hover on Slide on/off
										thumbWidth:100,							// Thumb With and Height and Amount (only if navigation Tyope set to thumb !)
										thumbHeight:50,
										thumbAmount:3,
										hideThumbs:0,
										navigationType:"bullet",				// bullet, thumb, none
										navigationArrows:"solo",				// nexttobullets, solo (old name verticalcentered), none
										navigationStyle:"round",				// round,square,navbar,round-old,square-old,navbar-old, or any from the list in the docu (choose between 50+ different item), custom
										navigationHAlign:"right",				// Vertical Align top,center,bottom
										navigationVAlign:"bottom",					// Horizontal Align left,center,right
										navigationHOffset:30,
										navigationVOffset:30,
										soloArrowLeftHalign:"left",
										soloArrowLeftValign:"center",
										soloArrowLeftHOffset:20,
										soloArrowLeftVOffset:0,
										soloArrowRightHalign:"right",
										soloArrowRightValign:"center",
										soloArrowRightHOffset:20,
										soloArrowRightVOffset:0,
										touchenabled:"on",						// Enable Swipe Function : on/off
										stopAtSlide:-1,							// Stop Timer if Slide "x" has been Reached. If stopAfterLoops set to 0, then it stops already in the first Loop at slide X which defined. -1 means do not stop at any slide. stopAfterLoops has no sinn in this case.
										stopAfterLoops:-1,						// Stop Timer if All slides has been played "x" times. IT will stop at THe slide which is defined via stopAtSlide:x, if set to -1 slide never stop automatic
										hideCaptionAtLimit:0,					// It Defines if a caption should be shown under a Screen Resolution ( Basod on The Width of Browser)
										hideAllCaptionAtLilmit:0,				// Hide all The Captions if Width of Browser is less then this value
										hideSliderAtLimit:0,					// Hide the whole slider, and stop also functions if Width of Browser is less than this value
										fullWidth:"on",
										shadow:1								//0 = no Shadow, 1,2,3 = 3 Different Art of Shadows -  (No Shadow in Fullwidth Version !)
									});
	
									// TO HIDE THE ARROWS SEPERATLY FROM THE BULLETS, SOME TRICK HERE:
									// YOU CAN REMOVE IT FROM HERE TILL THE END OF THIS SECTION IF YOU DONT NEED THIS !
										api.bind("revolution.slide.onloaded",function (e) {
											jQuery('.tparrows').each(function() {
												var arrows=jQuery(this);
												var timer = setInterval(function() {
													if (arrows.css('opacity') == 1 && !jQuery('.tp-simpleresponsive').hasClass("mouseisover"))
													  arrows.fadeOut(300);
												},3000);
											})
	
											jQuery('.tp-simpleresponsive, .tparrows').hover(function() {
												jQuery('.tp-simpleresponsive').addClass("mouseisover");
												jQuery('body').find('.tparrows').each(function() {
													jQuery(this).fadeIn(300);
												});
											}, function() {
												if (!jQuery(this).hasClass("tparrows"))
													jQuery('.tp-simpleresponsive').removeClass("mouseisover");
											})
										});
									// END OF THE SECTION, HIDE MY ARROWS SEPERATLY FROM THE BULLETS
	
						});
						
						jQuery(document).ready(function($){
							// gets the width of black bar at the bottom of the slider
							var $gwsr = $('.scolright').outerWidth();
							$('.blacklable').css({'width' : $gwsr +'px'});
						});
						jQuery(window).resize(function() {
							jQuery(document).ready(function($){
	
								// gets the width of black bar at the bottom of the slider
								var $gwsr = $('.scolright').outerWidth();
								$('.blacklable').css({'width' : $gwsr +'px'});
	
							});
						});
						</script>                
						</div>		
					<?php
						}
					?>
					
					</div><!-- end of row -->
				</div>
			</div>
		</div>	
	<?php } ?>
	
	<?php
		// 2 'last-minute'
		$last_minute = Hotels::DrawHotelsByGroup(2, true, false);

		// 3 'early-booking'
		$early_booking = Hotels::DrawHotelsByGroup(3, true, false);

		// 4 'hot deals '
		$hot_deals = Hotels::DrawHotelsByGroup(4, true, false);
	?>
    
	<?php if(!empty($last_minute) || !empty($early_booking) || !empty($hot_deals)){ ?>
    <div class="deals4">
        <div class="container">
			<div class="row">
				<?php 
					echo $last_minute;
					echo $early_booking;
					echo $hot_deals;
				?>
            </div>
        </div>
	</div>
	<?php }else{ ?>
		<br>
	<?php } ?>
    
	<?php
		ob_start();

        include('templates/'.Application::Get('template').'/last_minute.php');
		$file_last_minute = ob_get_contents();
		ob_clean();
        include('templates/'.Application::Get('template').'/today_top_deals.php');
		$file_today_top_deals = ob_get_contents();
		ob_clean();
        include('templates/'.Application::Get('template').'/today_featured_offers.php');
		$file_today_featured_offers = ob_get_contents();
		ob_end_clean();
	?>
	
	<?php if(!empty($file_last_minute)){ ?>
	<div class="lastminute4">
		<?= $file_last_minute; ?>
	</div>	
	<?php } ?>

	<?php if(!empty($file_today_top_deals) || !empty($file_today_featured_offers)){ ?>
	<div class="container cstyle06">
		<?= $file_today_top_deals; ?>
        <hr class="featurette-divider2">
		<?= $file_today_featured_offers; ?>
    </div>
	<?php } ?>
	
    <?php include('templates/'.Application::Get('template').'/footer.php'); ?>
    
</div>
<!-- WRAP -->
