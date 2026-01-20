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

<?php if(Application::Get('render') !== 'partial'){ ?>
<div class="page">
	<?= draw_page_navbar(_SETTINGS); ?>
	<?= draw_page_content_start('settings'); ?>
<? } ?>

	<div class="block-title">Application Information</div>
	<div class="block block-strong">
		<p>Program: <?= MOBILE_APP_NAME?></p>
		<p>Version: <?= MOBILE_APP_VERSION?></p>
	</div>

	<div class="block-title">Developer Information</div>
	<div class="block block-strong">
		<p>Developer: <?= MOBILE_APP_NAME?></p>
		<p>Website: <a href="https://hotel-booking-script.com" target="_blank">https://hotel-booking-script.com</a></p>
		<p>Contact: <a href="mailto:info@hotel-booking-script.com">info@hotel-booking-script.com</a></p>
	</div>

<?php if(Application::Get('render') !== 'partial'){ ?>
	<?= draw_page_content_end(); ?>
</div>
<? } ?>