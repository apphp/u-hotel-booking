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

// Draw title bar
draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'',_AGENCY_LOGIN=>'')));

// Check if agency is logged in
if(!$objLogin->IsLoggedIn() && ModulesSettings::Get('customers', 'allow_login') == 'yes' && ModulesSettings::Get('customers', 'allow_agencies') == 'yes'){

	if($objLogin->IsWrongLogin()) draw_important_message($objLogin->GetLoginError()).'<br />';
	else if($objLogin->IsIpAddressBlocked()) draw_important_message(_IP_ADDRESS_BLOCKED).'<br />';
	else if($objLogin->IsEmailBlocked()) draw_important_message(_EMAIL_BLOCKED).'<br />';
	else if($objSession->IsMessage('notice')) draw_message($objSession->GetMessage('notice'));

	$remember_me = isset($_POST['remember_me']) ? (int)$_POST['remember_me'] : '';

	draw_content_start();	
?>
	<form class="login-form" action="index.php?customer=<?= TRAVEL_AGENCY_LOGIN; ?>" method="post">
		<?php draw_hidden_field('submit_login', 'login'); ?>
		<?php draw_hidden_field('type', 'customer'); ?>
		<?php draw_token_field(); ?>

		<table width="96%" cellspacing="4" border="0">
		<tr>
			<td width="13%" nowrap="nowrap"><?= _USERNAME;?></td>
			<td width="87%"><input type="text" name="user_name" id="txt_user_name" style="width:145px" maxlength="50" autocomplete="off" /></td>
		</tr>
		<tr>
			<td><?= _PASSWORD;?></td>
			<td><input type="password" name="password" size="22" style="width:145px" maxlength="20" autocomplete="off" /></td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>		
		<tr>
			<td valign="middle">
				<input class="form_button" type="submit" name="submit" value="<?= _BUTTON_LOGIN;?>">&nbsp;
			</td>
			<?php
				if(ModulesSettings::Get('customers', 'remember_me_allow') == 'yes'){
					echo '<td><input type="checkbox" class="form_checkbox" name="remember_me" id="chk_remember_me" '.($remember_me == '1' ? 'checked="checked"' : '').' value="1" /> <label for="chk_remember_me">'._REMEMBER_ME.'</label></td>';				
				}else{
					echo '<td></td>';
				}
			?>			
		</tr>
		<tr><td colspan="2" nowrap="nowrap" height="5px"></td></tr>		
		<tr>
			<td valign="top" colspan="2">
			<?php
				if(ModulesSettings::Get('customers', 'allow_reset_passwords') == 'yes'){
					echo prepare_permanent_link('index.php?customer=password_forgotten', _FORGOT_PASSWORD).'<br />';
				}
			?>
			</td>
		</tr>
		<tr><td colspan="2" nowrap="nowrap" height="5px"></td></tr>		
		</table>
	</form>
	<script type="text/javascript">appSetFocus("txt_user_name");</script>	
<?php
	draw_content_end();
}elseif($objLogin->IsLoggedInAsCustomer()){
	draw_content_start();	
	draw_important_message(_ALREADY_LOGGED);
?>
	<div class="pages_contents">
	<form action="index.php?customer=<?= TRAVEL_AGENCY_LOGIN; ?>" method="post">
		<?php draw_hidden_field('submit_logout', 'logout'); ?>
		<?php draw_token_field(); ?>
		<input class="form_button" type="submit" name="submit" value="<?= _BUTTON_LOGOUT;?>">
	</form>
	</div>	
<?php
	draw_content_end();
}else{
	$objSession->SetMessage('notice','');
	draw_important_message(_NOT_AUTHORIZED);
}
