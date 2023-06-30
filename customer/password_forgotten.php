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

// check if customer is not logged in
if(!$objLogin->IsLoggedIn() && (ModulesSettings::Get('customers', 'allow_reset_passwords') == 'yes')){	

	$act 	= isset($_POST['act']) ? prepare_input($_POST['act']) : '';
	$email 	= isset($_POST['email']) ? prepare_input($_POST['email']) : '';
	$msg 	= '';

	if($act == 'send'){
		if(!check_email_address($email)){
			$msg = draw_important_message(_EMAIL_IS_WRONG, false);					
		}else{
			if(!(bool)Session::Get('password_sent')){
				if(Customers::SendPassword($email)){
					$msg = draw_success_message(_PASSWORD_SUCCESSFULLY_SENT, false);
					Session::Set('password_sent', true);
				}else{
					$msg = draw_important_message(Customers::GetStaticError(), false);					
				}
			}else{
				$msg = draw_message(_PASSWORD_ALREADY_SENT, false);
			}
		}
	}

	// draw title bar
	draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'',_PASSWORD_FORGOTTEN=>'')));

	echo $msg;
	
	draw_content_start();
?>
	<form class="forgot-password-form" action="index.php?customer=password_forgotten" method="post">
		<?php draw_hidden_field('act', 'send'); ?>
		<?php draw_hidden_field('type', 'customer'); ?>
		<?php draw_token_field(); ?>
		
		<table width="96%" border="0">
		<tr>
			<td colspan="2">
				<p><?= _PASSWORD_RECOVERY_MSG; ?></p>
			</td>
		</tr>
		<tr>
			<td width="15%" nowrap="nowrap"><?= _EMAIL_ADDRESS;?>:</td>
			<td width="85%"><input class="form-control form_text" type="text" name="email" id="forgotten_email" size="25" maxlength="70" autocomplete="off" /></td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>			
		<tr>
			<td colspan="2">
				<input class="form_button" type="submit" name="btnSend" value="<?= _SEND;?>">
			</td>
		</tr>
		<tr><td colspan="2" nowrap="nowrap" height="5px"></td></tr>		
		<tr>
			<td colspan="2">
				<?php
					if(ModulesSettings::Get('customers', 'allow_login') == 'yes'){
						echo prepare_permanent_link('index.php?customer=login', _CUSTOMER_LOGIN).'<br />';	
					}
					if(ModulesSettings::Get('customers', 'allow_registration') == 'yes'){
						echo prepare_permanent_link('index.php?customer=create_account', _CREATE_ACCOUNT);
					}
				?>
			</td>
		</tr>
		<tr><td colspan="2" nowrap="nowrap" height="5px"></td></tr>		
		</table>
		<script type="text/javascript">appSetFocus("forgotten_email");</script>
	</form>
<?php
	draw_content_end();
}else{
	draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'',_PASSWORD_FORGOTTEN=>'')));
    draw_important_message(_NOT_AUTHORIZED);
}
