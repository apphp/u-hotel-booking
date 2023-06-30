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
<script>
	var resizefunc = [];
</script>

<!-- jQuery  -->
<script src="<?= $template_path; ?>js/detect.js"></script>
<script src="<?= $template_path; ?>js/fastclick.js"></script>
<script src="<?= $template_path; ?>js/jquery.blockUI.js"></script>
<script src="<?= $template_path; ?>js/waves.js"></script>
<script src="<?= $template_path; ?>js/jquery.nicescroll.js"></script>
<script src="<?= $template_path; ?>js/jquery.slimscroll.js"></script>
<script src="<?= $template_path; ?>js/jquery.scrollTo.min.js"></script>
<script src="<?= APPHP_BASE; ?>js/chosen/chosen.jquery.min.js"></script>
<script src="<?= APPHP_BASE; ?>modules/datatables/js/jquery.dataTables.min.js"></script>
<script src="<?= APPHP_BASE; ?>modules/datatables/js/jquery.dataTables.min.js"></script>
<!-- App js -->
<script src="<?= $template_path; ?>js/jquery.core.js"></script>
<script src="<?= $template_path; ?>js/jquery.app.js"></script>
<script>
	<?php if(Application::Get('lang_dir') == 'rtl'){ ?>
    jQuery(".mgrid_table_filter .mgrid_select").addClass("chosen-rtl");
    jQuery("#hotel_id.mgrid_select").addClass("chosen-rtl");
    jQuery("#room_floor.mgrid_select").addClass("chosen-rtl");
    jQuery(".mgrid_select.chosen_select").addClass("chosen-rtl");
    <?php } ?>
    jQuery(".mgrid_table_filter .mgrid_select").chosen();
    jQuery("#hotel_id.mgrid_select").chosen();
    jQuery("#room_floor.mgrid_select").chosen();
    jQuery(".mgrid_select.chosen_select").chosen();
</script>
