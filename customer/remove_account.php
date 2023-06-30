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

if($objLogin->IsLoggedInAsCustomer()){

	$submit = isset($_POST['submit']) ? prepare_input($_POST['submit']) : '';
	$msg = '';
	$account_deleted = false;
	
	if($submit == 'remove'){                
		if(strtolower(SITE_MODE) == 'demo'){
			$msg = draw_important_message(_OPERATION_BLOCKED, false);
		}else{
			if($objLogin->RemoveAccount()){
				$msg = draw_success_message(_ACCOUNT_WAS_DELETED, false);
				$account_deleted = true;
		
				////////////////////////////////////////////////////////////////
				send_email(
					$objLogin->GetLoggedEmail(),
					$objSettings->GetParameter('admin_email'),
					'account_deleted_by_user',
					array(
						'{USER NAME}'  => $objLogin->GetLoggedName(),
					),
					$objLogin->GetPreferredLang()
				);
				////////////////////////////////////////////////////////////

				$objSession->EndSession();
			}else{
				$msg = draw_important_message(_DELETING_ACCOUNT_ERROR, false);
			}			
		}
	}
            
	draw_title_bar(prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_REMOVE_ACCOUNT=>'')));
	
?>
	<form action="index.php" method="post" id="frmLogout" style="display:inline; margin-top:0px; padding-top:0px;">
		<?php draw_hidden_field('submit_logout', 'logout'); ?>
		<?php draw_token_field(); ?>
	</form>
		
	<form action="index.php?customer=remove_account" method="post" name="frmProfile" onsubmit="return confirm('<?= _REMOVE_ACCOUNT_ALERT; ?>');">
		<?php draw_hidden_field('submit', 'remove'); ?>
		<?php draw_token_field(); ?>
		<br />
		<?php        
			echo $msg;
			if($account_deleted){
				echo '<script type="text/javascript">setTimeout(function(){appFormSubmit("frmLogout")}, 5000);</script>';
			}else{
				draw_message(_REMOVE_ACCOUNT_WARNING);
			}        
		?>
		<?php if(!$account_deleted){ ?>
		<table align="center" border="0" cellspacing="1" cellpadding="2" width="96%">
		<tr><td colspan="3">&nbsp;</td></tr>            
		<tr>
			<td colspan="2">
				<input type="submit" class="form_button" name="btnSubmitPD" id="btnSubmitPD" value="<?= _REMOVE; ?>" />
				&nbsp;
				<a href="javascript:appGoTo('customer=my_account');"><?= _BUTTON_CANCEL; ?></a>
			</td>
		</tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		</table>
		<?php } ?>
	</form>

<?php
}elseif($objLogin->IsLoggedIn()){
    draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'', _REMOVE_ACCOUNT=>'')));
    draw_important_message(_NOT_AUTHORIZED);
}else{
    draw_title_bar(prepare_breadcrumbs(array(_CUSTOMERS=>'', _REMOVE_ACCOUNT=>'')));
    draw_important_message(_MUST_BE_LOGGED);
}
