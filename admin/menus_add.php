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

if($objLogin->IsLoggedInAsAdmin() && $objLogin->HasPrivileges('add_menus')){

    draw_title_bar(prepare_breadcrumbs(array(_MENUS_AND_PAGES=>'',_MENU_MANAGEMENT=>'',_MENU_ADD=>'')));
	echo $msg;

	draw_content_start();	
?>
	<div class="table-responsive">
	<form name="frmAddMenu" method="post">
		<?php draw_hidden_field('act', 'add'); ?>
		<?php draw_token_field(); ?>
		<table width="100%" class="mgrid_table">
		<tr>
			<td width="20%"><?= _MENU_NAME;?> <span class="required">*</span>:</td>
			<td><input class="mgrid_text" name="name" id="frmAddMenu_name" value="" size="40" maxlength="30"></td>
		</tr>
		<tr>
			<td><?= _DISPLAY_ON;?>:</td>
			<td><?php Menu::DrawMenuPlacementBox($menu_placement); ?></td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<?= _ACCESS; ?>:&nbsp;
			</td>
			<td>
				<?= Menu::DrawMenuAccessSelectBox(); ?>
			</td>
		</tr>
		<tr>
			<td><?= _LANGUAGE;?> <span class="required">*</span>:</td>
			<td>
				<?php
					// display language
					$total_languages = Languages::GetAllActive();
					draw_languages_box('language_id', $total_languages[0], 'abbreviation', 'lang_name', $language_id, 'mgrid_select'); 
				?>
			</td>
		</tr>
		<tr><td height="10px" nowrap="nowrap"></td></tr>		
		<tr>
			<td colspan="2">
				<input class="mgrid_button" type="submit" name="subAddMenu" value="<?= _BUTTON_CREATE;?>">
				<input class="mgrid_button mgrid_button_cancel" type="button" onclick="javascript:appGoTo('admin=menus')" value="<?= _BUTTON_CANCEL; ?>">
			</td>
		</tr>		
		</table>
		<br />
	</form>
	</div>
	<script type="text/javascript">appSetFocus("frmAddMenu_name");</script>
<?php
	draw_content_end();	
}else{
	draw_title_bar(_ADMIN);
    draw_important_message(_NOT_AUTHORIZED);
}
