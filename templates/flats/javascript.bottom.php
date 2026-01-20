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

?>

<!-- Javascript -->	
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/js-list.js"></script>	
<!-- Custom Select -->
<script type='text/javascript' src='<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/jquery.customSelect.min.js'></script>
<!-- Custom Select -->
<script type='text/javascript' src='<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/js/lightbox.js'></script>	
<!-- JS Ease -->	
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/jquery.easing.min.js"></script>
<!-- Custom functions -->
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/functions.js"></script>
<!-- jQuery KenBurn Slider  -->
<script type="text/javascript" src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/rs-plugin/js/jquery.themepunch.revolution.min.js"></script>
<!-- Nicescroll  -->	
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/jquery.nicescroll.min.js"></script>
<!-- Bootstrap -->	
<script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/dist/js/bootstrap.min.js"></script>
<!-- Picker -->	
<script src="<?= APPHP_BASE; ?>js/jquery/jquery-ui.min.js"></script>
<script type='text/javascript'>
jQuery.datepicker.regional.ar = {
    closeText: "<?= htmlspecialchars(_CLOSE); ?>",
    prevText: "&#x3C;<?= htmlspecialchars(_PREVIEW); ?>",
    nextText: "<?= htmlspecialchars(_NEXT); ?>&#x3E;",
    currentText: "<?= htmlspecialchars(_TODAY); ?>",
    monthNames: [ "<?= htmlspecialchars(_JANUARY); ?>", "<?= htmlspecialchars(_FEBRUARY); ?>", "<?= htmlspecialchars(_MARCH); ?>", "<?= htmlspecialchars(_APRIL); ?>", "<?= htmlspecialchars(_MAY); ?>", "<?= htmlspecialchars(_JUNE); ?>",
    "<?= htmlspecialchars(_JULY); ?>", "<?= htmlspecialchars(_AUGUST); ?>", "<?= htmlspecialchars(_SEPTEMBER); ?>", "<?= htmlspecialchars(_OCTOBER); ?>", "<?= htmlspecialchars(_NOVEMBER); ?>", "<?= htmlspecialchars(_DECEMBER); ?>" ],
	monthNamesShort: [ "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12" ],
    dayNames: [ "<?= htmlspecialchars(_SUNDAY); ?>", "<?= htmlspecialchars(_MONDAY); ?>", "<?= htmlspecialchars(_TUESDAY); ?>", "<?= htmlspecialchars(_WEDNESDAY); ?>", "<?= htmlspecialchars(_THURSDAY); ?>", "<?= htmlspecialchars(_FRIDAY); ?>", "<?= htmlspecialchars(_SATURDAY); ?>" ],
    dayNamesShort: [ "<?= htmlspecialchars(_SUN); ?>", "<?= htmlspecialchars(_MON); ?>", "<?= htmlspecialchars(_TUE); ?>", "<?= htmlspecialchars(_WED); ?>", "<?= htmlspecialchars(_THU); ?>", "<?= htmlspecialchars(_FRI); ?>", "<?= htmlspecialchars(_SAT); ?>" ],
	dayNamesMin: [ "<?= htmlspecialchars(_SU); ?>", "<?= htmlspecialchars(_MO); ?>", "<?= htmlspecialchars(_TU); ?>", "<?= htmlspecialchars(_WE); ?>", "<?= htmlspecialchars(_TH); ?>", "<?= htmlspecialchars(_FR); ?>", "<?= htmlspecialchars(_SA); ?>" ],
    isRTL: <?= Application::Get('lang_dir') == 'rtl' ? 'true' : 'false'; ?>};
jQuery.datepicker.setDefaults( jQuery.datepicker.regional.ar );
</script>
