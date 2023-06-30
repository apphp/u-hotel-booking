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

if(Modules::IsModuleInstalled('banners') && ModulesSettings::Get('banners', 'is_active') == 'yes'){
?>
<!--
#################################
- THEMEPUNCH BANNER -
#################################
-->
<div id="dajy" class="fullscreen-container mtslide sliderbg fixed">
    <div class="fullscreenbanner">
        <ul style="visibility:hidden">
            <!-- papercut fade turnoff flyin slideright slideleft slideup slidedown-->			
			<?php
				$banners = Banners::GetBannersArray(array('home', ''));
				if(is_array($banners)){
					foreach($banners as $key => $banner){
						echo '<!-- FADE -->
						<li data-transition="fade" data-slotamount="1" data-masterspeed="300">';
							if(!empty($banner['link_url'])) echo '<a href="'.$banner['link_url'].'">';
							echo '<img src="'.APPHP_BASE.'images/banners/'.$banner['image_file'].'" alt=""/>';
							if(!empty($banner['link_url'])) echo '</a>';
							echo '<div class="tp-caption scrolleffect sft" data-x="center" data-y="120" data-speed="1000" data-start="800" data-easing="easeOutExpo">
								<div class="sboxpurple textcenter">'.$banner['image_text'].'</div>
							</div>	
						</li>';	
					}					
				}
			?>
        </ul>
        <div class="tp-bannertimer none"></div>
    </div>
</div>

<!--
##############################
 - ACTIVATE THE BANNER HERE -
##############################
-->
<script type="text/javascript">
var tpj=jQuery;
tpj.noConflict();

tpj(document).ready(function() {
if (tpj.fn.cssOriginal!=undefined)
    tpj.fn.css = tpj.fn.cssOriginal;
    tpj('.fullscreenbanner').revolution({
        delay:9000,
        startwidth:1170,
        startheight:600,

        onHoverStop:"on",						// Stop Banner Timet at Hover on Slide on/off

        thumbWidth:100,							// Thumb With and Height and Amount (only if navigation Tyope set to thumb !)
        thumbHeight:50,
        thumbAmount:3,

        hideThumbs:0,
        navigationType:"bullet",				// bullet, thumb, none
        navigationArrows:"solo",				// nexttobullets, solo (old name verticalcentered), none

        navigationStyle:false,				// round,square,navbar,round-old,square-old,navbar-old, or any from the list in the docu (choose between 50+ different item), custom

        navigationHAlign:"left",				// Vertical Align top,center,bottom
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

        ///fullWidth:"on",							// Same time only Enable FullScreen of FullWidth !!
        fullScreen:"off",						// Same time only Enable FullScreen of FullWidth !!

        shadow:0								//0 = no Shadow, 1,2,3 = 3 Different Art of Shadows -  (No Shadow in Fullwidth Version !)
    });
});
</script>
<?php } ?>
