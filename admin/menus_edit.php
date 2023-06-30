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

if($objLogin->IsLoggedInAsAdmin() && $objLogin->HasPrivileges('edit_menus')){
	
    draw_title_bar(prepare_breadcrumbs(array(_MENUS_AND_PAGES=>'',_MENU_MANAGEMENT=>'',_MENU_EDIT=>'')));
	echo $msg;

	draw_content_start();				
?>
	<form name="frmEditMenu" method="post" action="index.php?admin=menus_edit">
		<?php draw_hidden_field('act', 'edit'); ?>
		<?php draw_hidden_field('mid', $mid); ?>
		<?php draw_hidden_field('language_id', $menu->GetParameter('language_id')); ?>
		<?php draw_token_field(); ?>

		<table width="100%" class="mgrid_table">
		<tr>
			<td><?= _MENU_NAME;?> <span class="required">*</span>:</td>
			<td><input class="mgrid_text" name="name" value="<?= $menu->GetName();?>" size="40" maxlength="30"></td>
		</tr>
		<tr>
			<td><?= _MENU_ORDER;?> <span class="required">*</span>:</td>
			<td>
				<?php
					// output select tag as a total number of menus available
					$total_menus = Menu::GetAll(' menu_order ASC', TABLE_MENUS, '', $menu->GetParameter('language_id'));
					draw_numbers_select_field('order', $menu->GetOrder(2), 1, $total_menus[1], 1, 'mgrid_select');
				?>
			</td>
		</tr>
		<tr>
			<td><?= _DISPLAY_ON;?>:</td>
			<td><?php Menu::DrawMenuPlacementBox($menu->GetParameter('menu_placement')); ?></td>
		</tr>
		<tr>
			<td nowrap="nowrap">
				<?= _ACCESS; ?>:&nbsp;
			</td>
			<td>
				<?= Menu::DrawMenuAccessSelectBox($menu->GetParameter('access_level')); ?>
			</td>
		</tr>
		<tr>
			<td><?= _LANGUAGE;?>:</td>
			<td>
				<?php
					// display language
					echo '<label>'.$menu->GetParameter('language_name').'</label>';
				?>
			</td>
		</tr>
		<tr><td height="10px" nowrap="nowrap"></td></tr>		
		<tr>
			<td colspan="2">
				<input class="mgrid_button" type="submit" name="subEditMenu" value="<?= _BUTTON_SAVE_CHANGES ?>">
				<input class="mgrid_button mgrid_button_cancel" type="button" onclick="javascript:appGoTo('admin=menus')" value="<?= _BUTTON_CANCEL; ?>">					
			</td>
		</tr>		
		</table>
		<br />
	</form>
<?php
	draw_content_end();	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
