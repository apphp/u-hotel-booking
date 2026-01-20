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
	<?= draw_homepage_navbar(); ?>
	<?= draw_page_content_start('home'); ?>
<? } ?>

	<div class="block-title __searchbar-hide-on-search">Home Page</div>
	<div class="block">
		voc const: <?= _HOME; ?> :: home page content here.... <?= date('H:i:s'); ?>
		<br>
		currency: <?= Application::Get('currency').' '. Application::Get('currency_code')?>
	</div>


	<!-- And somewhere in DOM
	<div class="actions-modal my-langs">
        <div class="actions-group">
            <div class="actions-button actions-button-bold">
                  <div class="actions-button-text">Select Language</div>
                </div>
				<div class="actions-button">
                  <div class="actions-button-text"><a href="/home/" class="actions-close popover-close"></a>English</div>
                 </div>
					<div class="actions-button color-red">
                  <div class="actions-button-text">Cancel</div>
                </div>
          </div>
      </div>
      -->

<?php if(Application::Get('render') !== 'partial'){ ?>
	<?= draw_page_content_end(); ?>
</div>
<? } ?>