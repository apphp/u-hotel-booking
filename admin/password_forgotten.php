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

$act 		    = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
$password_sent 	= (bool)Session::Get('password_sent');
$email 			= isset($_POST['email']) ? prepare_input($_POST['email']) : '';
$msg 			= '';

if($act == 'send'){
	if(!check_email_address($email)){
		$msg = draw_important_message(_EMAIL_IS_WRONG, false);					
	}else{
		if(!$password_sent){
			$objAdmin = new Admins(Session::Get('session_account_id'));
			if($objAdmin->SendPassword($email)){
				$msg = draw_success_message(_PASSWORD_SUCCESSFULLY_SENT, false);
				Session::Set('password_sent', true);
			}else{
				$msg = draw_important_message($objAdmin->error, false);					
			}
		}else{
			$msg = draw_message(_PASSWORD_ALREADY_SENT, false);
		}
	}
}

// Draw title bar
//draw_title_bar(prepare_breadcrumbs(array(_ADMIN=>'',_PASSWORD_FORGOTTEN=>'')));

// Check if admin is logged in
if(!$objLogin->IsLoggedIn()){
	echo $msg;
	draw_content_start();	
?>

	<form class="form-horizontal m-t-20" action="index.php?admin=password_forgotten" method="post">
		<?php draw_hidden_field('act', 'send'); ?>
		<?php draw_token_field(); ?>

		<p><?= _PASSWORD_RECOVERY_MSG; ?></p>
		
		<div class="form-group">
			<div class="col-xs-12">
				<input name="email" id="txt_forgotten_email" class="form-control" required="" placeholder="<?= _EMAIL_ADDRESS;?>" type="email">
			</div>
		</div>

		<div class="form-group text-center m-t-20 m-b-0">
			<div class="col-xs-12">
				<button class="btn btn-custom btn-bordred btn-block waves-effect waves-light" type="submit"><?= _SEND;?></button>
			</div>
		</div>
		
		<div class="form-group m-t-30 m-b-0">
			<div class="col-sm-12">
				<?= prepare_permanent_link('index.php?admin='.ADMIN_LOGIN, '<i class="fa fa-user m-r-5"></i> '._ADMIN_LOGIN); ?>
			</div>
		</div>
	</form>

	<script type="text/javascript">appSetFocus('txt_forgotten_email');</script>	
<?php
	draw_content_end();	
}elseif($objLogin->IsLoggedInAsAdmin()){
	draw_important_message(_ALREADY_LOGGED);
}else{
	draw_important_message(_NOT_AUTHORIZED);
}
