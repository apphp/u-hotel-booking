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
<h4><?= _AVAILABLE_ROOMS; ?></h4>
<hr/>
<div class="center_box_content" id="block-result">
	<h5><?= _SELECT_DATES_REQUIRED; ?></h5>
	<div class="page-message clearfix">
		<?= _TO_MAKE_YOUR_RESERVATION; ?>
		<hr/>
		<b><?= _WHY_SHOULD_I_BOOK; ?></b>
		<ul>
			<li><?= _BEST_RATE_GUARANTEED; ?></li>
			<li><?= _BOOK_NOW_PAY_LATER; ?></li>
			<li><?= FLATS_INSTEAD_OF_HOTELS ? _FLATS_DIRECT_RESERVATION : _DIRECT_RESERVATION; ?></li>
			<li><?= _FREE_CANCELLATION; ?></li>
		</ul>
	</div>
</div>
    <?php //draw_message(_MSG_WIDGET_CENTER_PANEL_HOME); ?>
</div>
