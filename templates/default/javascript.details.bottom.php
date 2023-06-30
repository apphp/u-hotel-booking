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

?>

<!-- Javascript -->	
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/js-details.js"></script>
<!-- Custom Select -->
<script type='text/javascript' src='<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/jquery.customSelect.min.js'></script>
<!-- Review icon -->
<script type='text/javascript' src='<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/js/lightbox.js'></script>	
<!-- Custom functions -->
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/functions.js"></script>
<!-- Nicescroll  -->	
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/jquery.nicescroll.min.js"></script>
<!-- jQuery KenBurn Slider  -->
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/rs-plugin/js/jquery.themepunch.revolution.min.js" type="text/javascript"></script>
<?php if(GALLERY_TYPE == 'carousel'){ ?>
    <script src="<?= APPHP_BASE; ?>modules/bxslider/jquery.bxslider.min.js"></script>
<?php }else{ ?>
    <script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/helper-plugins/jquery.touchSwipe.min.js"></script>

    <script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/helper-plugins/jquery.mousewheel.min.js" type="text/javascript"></script>
<!--    <script src="--><?//= APPHP_BASE; ?><!--templates/--><?//= Application::Get('template');?><!--/assets/js/helper-plugins/jquery.transit.min.js" type="text/javascript"></script>-->
    <!-- Carousel-->
    <script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/initialize-carousel-detailspage.js"></script>		
<?php } ?>
<!-- Js Easing-->	
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/jquery.easing.min.js"></script>
<!-- Bootstrap-->	
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/dist/js/bootstrap.min.js"></script>
<!-- Picker -->	
<script src="<?= APPHP_BASE; ?>js/jquery/jquery-ui.min.js"></script>
