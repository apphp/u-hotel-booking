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

if($objLogin->IsLoggedInAsAdmin()){
	
	draw_title_bar(prepare_breadcrumbs(array(_ACCOUNTS=>'',_MY_ACCOUNT=>'')));	

	if($msg == ''){
		draw_message(_ALERT_REQUIRED_FILEDS);
	}else{
		echo $msg;
	}
	
	draw_content_start();

	$arr_account_types = array(
		'owner'=>_OWNER,
		'admin'=>_ADMIN,
		'mainadmin'=>_MAIN_ADMIN,
		'hotelowner'=>FLATS_INSTEAD_OF_HOTELS ? _HOTEL_OWNER : _FLAT_OWNER,
		'regionalmanager'=>_REGIONAL_MANAGER
	);
	
	if(Modules::IsModuleInstalled('car_rental')){
		$arr_account_types['agencyowner'] = _CAR_AGENCY_OWNER;
	}

	if(Modules::IsModuleInstalled('property_management')){
		$arr_account_types['hotelmanager'] = FLATS_INSTEAD_OF_HOTELS ? _FLAT_MANAGER : _HOTEL_MANAGER;
	}
?>

	<?php draw_sub_title_bar(_GENERAL_INFO); ?>
	<form action="index.php?admin=my_account" method="post">
	<?php draw_hidden_field('submit_type', '1'); ?>
	<?php draw_token_field(); ?>
	<table width="100%" class="mgrid_table">
	<tr>
		<td>&nbsp;<?= _ACCOUNT_TYPE;?>:</td>
		<td><?= $arr_account_types[$objAdmin->GetParameter('account_type')];?></td>
	</tr>
	<tr>
		<td width="160px">&nbsp;<?= _USERNAME;?>:</td>
		<td><?= $objAdmin->account_name;?></td>
	</tr>
	<tr>
		<td width="160px">&nbsp;<?= _PREFERRED_LANGUAGE;?> <span class="required">*</span>:</td>
		<td>
		<?php
			// display language
			$total_languages = Languages::GetAllActive(); 
			draw_languages_box('preferred_language', $total_languages[0], 'abbreviation', 'lang_name', $objAdmin->GetParameter('preferred_language'), 'mgrid_select'); 
		?>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>	
	<tr>
		<td style="padding-left:0px;" colspan="2"><input class="form_button" type="submit" name="submit" value="<?= _BUTTON_CHANGE; ?>"></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>	
	</table>
	</form>
	
	<?php
		if(
		   Modules::IsModuleInstalled('rest_api') && 
		   in_array($objAdmin->GetParameter('account_type'), array('owner', 'mainadmin', 'admin', 'hotelowner', 'hotelmanager'))
		){
	?>
		<?php draw_sub_title_bar(_API_DETAILS); ?>
		<form action="index.php?admin=my_account" id="form-api-details"  method="post">
		<?php draw_hidden_field('submit_type', '4'); ?>
		<?php draw_token_field(); ?>
		<table width="100%" class="mgrid_table">
		<tr>
			<td width="160px">&nbsp;<?= _API_DOMAIN;?>: <img class="help" src="images/question_mark.png" title="<?= _SITE_ROOT_DIRECTORY; ?>" alt="" /></td>
			<td><input class="mgrid_text" id="oa_consumer_domain" name="oa_consumer_domain" type="text" size="25" maxlength="150" value="<?= $objAdmin->GetParameter('oa_consumer_domain'); ?>"></td>
		</tr>
		<tr>
			<td width="160px">&nbsp;<?= _API_KEY;?>:</td>
			<td><?= $objAdmin->GetParameter('oa_consumer_key'); ?></td>
		</tr>
		<tr>
			<td width="160px">&nbsp;<?= _API_SECRET;?>:</td>
			<td><?= $objAdmin->GetParameter('oa_consumer_secret'); ?></td>
		</tr>
		<tr>
			<td width="160px">&nbsp;<?= _DATE_CREATED;?>:</td>
			<td><?php
					$oa_date_created = $objAdmin->GetParameter('oa_date_created');
					if(is_empty_date($oa_date_created)){
						echo '--';	
					}else{
						$date_format_view = get_datetime_format('view');
						echo date($date_format_view, strtotime($oa_date_created));
					}
				?>
			</td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>	
		<tr>
			<td style="padding-left:0px;" colspan="2">
				<input class="form_button" type="submit" name="submit" value="<?= _BUTTON_CHANGE; ?>">
				<?php if($objAdmin->GetParameter('oa_consumer_key') != ''){ ?>
				<button class="form_button write" type="submit" name="btnRecreate" onclick="javascript:if(!confirm('<?= htmlspecialchars(_MESSAGE_FOR_RECREATE); ?>')){return false;}document.getElementById('form-api-details').getElementsByTagName('input')[0].value=5;document.getElementById('form-api-details').submit.click();"><?= _RECREATE; ?></button>
				<?php } ?>
			</td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>	
		</table>
		</form>
	<?php } ?>


	<?php draw_sub_title_bar(_PERSONAL_INFORMATION); ?>
	<form id="frmAccount" action="index.php?admin=my_account" method="post" enctype="multipart/form-data">
	<?php draw_hidden_field('submit_type', '2'); ?>
	<?php draw_token_field(); ?>
	<table width="100%" class="mgrid_table">
	<tr>
		<td width="160px">&nbsp;<?= _FIRST_NAME;?> <span class="required">*</span>:</td>
		<td><input class="mgrid_text" id="first_name" name="first_name" type="text" size="25" maxlength="32" value="<?= $objAdmin->GetParameter('first_name'); ?>"></td>
	</tr>
	<tr>
		<td width="160px">&nbsp;<?= _LAST_NAME;?> <span class="required">*</span>:</td>
		<td><input class="mgrid_text" id="last_name" name="last_name" type="text" size="25" maxlength="32" value="<?= $objAdmin->GetParameter('last_name'); ?>"></td>
	</tr>
	<tr>
		<td width="160px">&nbsp;<?= _EMAIL_ADDRESS;?> <span class="required">*</span>:</td>
		<td><input class="mgrid_text" id="admin_email" name="admin_email" type="text" size="25" maxlength="70" value="<?= $objAdmin->GetParameter('email'); ?>"></td>
	</tr>
	<tr>
		<td width="160px">&nbsp;<?= _PHOTO;?>:</td>
		<td>
			<?php
				$profile_photo = $objAdmin->GetParameter('profile_photo');
				if(!empty($profile_photo)){
					if(!file_exists('images/admins/'.$profile_photo)){
						$profile_photo = 'no_image.png';
					}
					echo '<img src="images/admins/'.$profile_photo.'" alt="icon" width="100px" height="90px"><br><a href="javascript:removeIcon();">['._DELETE_WORD.']</a>';
				}
				else{
					echo '<input type="file" class="mgrid_text" name="profile_photo" id="profile_photo" />';
				}
			?>			
		</td>
	</tr>
    <?php if ($objAdmin->GetParameter('account_type') == 'hotelowner'): ?>
    <tr>
        <td>
            <input type="checkbox" class="tution_selector_main main_tuition_checkboxes" name="room_relocation" id="room_relocation" <?= (($objAdmin->GetParameter('room_relocation')) ? 'checked="checked"' : '');?> value="1" autocomplete="off">
            <label for="room_relocation"><?= _MS_ROOM_RELOCATION; ?></label>
        </td>
    </tr>
    <tr>
        <td>
            <input type="checkbox" class="tution_selector_main main_tuition_checkboxes" name="hotel_relocation" id="hotel_relocation" <?= (($objAdmin->GetParameter('hotel_relocation')) ? 'checked="checked"' : '');?> value="1" autocomplete="off">
            <label for="hotel_relocation"><?= _MS_HOTEL_RELOCATION; ?></label>
        </td>
    </tr>
    <?php endif; ?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td style="padding-left:0px;" colspan="2"><input class="form_button" type="submit" name="submit" value="<?= _BUTTON_CHANGE; ?>"></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>	
	</table>	
	</form>

	<?php draw_sub_title_bar(_CHANGE_YOUR_PASSWORD); ?>
	<form action="index.php?admin=my_account" method="post">
	<?php draw_hidden_field('submit_type', '3'); ?>
	<?php draw_token_field(); ?>
	<table width="100%" class="mgrid_table">
	<tr>
		<td width="160px">&nbsp;<?= _PASSWORD;?> <span class="required">*</span>:</td>
		<td width="405px"><input class="mgrid_text" id="password_one" name="password_one" type="password" size="25" maxlength="15"></td>
	</tr>
	<tr>
		<td>&nbsp;<?= _RETYPE_PASSWORD;?> <span class="required">*</span>:</td>
		<td colspan="2"><input class="mgrid_text" id="password_two" name="password_two" type="password" size="25" maxlength="15"></td>
	</tr>
	<tr><td colspan="3">&nbsp;</td></tr>
	<tr>
		<td colspan="2" style="padding-left:0px;" colspan="2"><input class="form_button" type="submit" name="submit" value="<?= _BUTTON_CHANGE_PASSWORD ?>"></td>
		<td></td>
	</tr>
	</table>
	</form>

<?php
	if($error_field != '') echo '<script type="text/javascript">appSetFocus(\''.$error_field.'\');</script>';

	draw_content_end();	
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
?>
<script>
	function removeIcon() {
		$('#frmAccount').find('[name="submit_type"]').val(9);
		$('#frmAccount').find('input[type="submit"]').click();
	}	
</script>
