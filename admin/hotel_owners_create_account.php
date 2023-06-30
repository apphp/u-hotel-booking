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

// Check if admin is logged in
if(ModulesSettings::Get('accounts', 'hotel_owner_allow_registration') == 'yes' && !$objLogin->IsLoggedIn()){
	if($objLogin->IsWrongLogin()) draw_important_message(_WRONG_LOGIN);

	if($account_created || $account_exists){
        if(!empty($msg)){
            echo '<div class="pages_contents">';
            echo $msg;
            echo '</div>';
        }
	}elseif(ModulesSettings::Get('accounts', 'hotel_owner_registration_type') == 'advanced'){
        if(!empty($msg)){ echo '<div class="pages_contents">'.$msg.'</div>'; }
        ?>
        <form id="frm_hotel_owners_create_account" class="form-horizontal m-t-20" action="index.php?admin=hotel_owners_create_account" method="post">
            <?php draw_hidden_field('act', 'create'); ?>
            <?php draw_hidden_field('hotel_id', '', true, 'hotel_id'); ?>
            <?php draw_token_field(); ?>

            <div class="form-group ">
                <div class="col-xs-12">
                    <input type="text" class="form-control" name="autocomplete_companies" id="autocomplete_companies" placeholder="<?= _TYPE_HOTEL_OR_LOCATION; ?>" autocomplete="off">
                </div>
            </div>
            <div class="form-group ">
                <div class="col-xs-12">
                    <label id="email_hotel" style="display: none;"></label>
                </div>
            </div>

            <div id="send_email" class="form-group text-center m-t-30" style="display: none;">
                <div class="col-xs-12">
                    <button class="btn btn-custom btn-bordred btn-block waves-effect waves-light" type="submit"><?= _CREATE_ACCOUNT; ?></button>
                </div>
            </div>
			
			<div class="form-group m-t-30 m-b-0">
				<div class="col-sm-12">
					<?= prepare_permanent_link('index.php?admin='.ADMIN_LOGIN.'&type=hotel_owners', '<i class="fa fa-user m-r-5"></i> '._LOGIN); ?>
				</div>
			</div>
        </form>

		<script>
			jQuery(document).ready(function(){
				jQuery("#autocomplete_companies").autocomplete({
					source: function(request, response){
						var token = "<?= htmlentities(Application::Get('token')); ?>";
						jQuery.ajax({
							url: "ajax/email_for_hotel_owner.ajax.php",
							global: false,
							type: "POST",
							data: ({
								token: token,
								act: "send",
								lang: "<?= htmlentities(Application::Get('lang')); ?>",
								search : jQuery("#autocomplete_companies").val(),
							}),
							dataType: "json",
							async: true,
							error: function(html){
								console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
							},
							success: function(data){
								if(data.length == 0){
									response({ label: "<?= htmlentities(_NO_MATCHES_FOUND); ?>" });
								}else{
									response(jQuery.map(data, function(item){
										return{ hotel_name: item.hotel_name, hotel_id: item.hotel_id, hotel_email: item.hotel_email, label: item.label }
									}));
								}
							}
						});
					},
					minLength: 2,
					select: function(event, ui) {
						if (ui.item.hotel_id === "" || ui.item.hotel_id === undefined){
							jQuery("#email_hotel").hide();
							jQuery("#send_email").hide();
							jQuery("#autocomplete_companies").val("");
							return false;
						}else{
							jQuery("#hotel_id").val(ui.item.hotel_id);
							jQuery("#email_hotel").html("<?= '<b>'.htmlentities(_EMAIL).'</b>: '; ?>" + ui.item.hotel_email);
							jQuery("#email_hotel").show();
							jQuery("#send_email").show();
						}
		
		
		
						return true;
					}
				});
		
				$('#frm_hotel_owners_create_account').keyup(function(event){
					if(event.keyCode == 13) {
						event.preventDefault();
						return false;
					}
				});
			});
		</script>
		
        <?php
	        }elseif(ModulesSettings::Get('accounts', 'hotel_owner_registration_type') == 'standard'){
            if(!empty($msg)){ echo '<div class="pages_contents">'.$msg.'</div>'; }
	    ?>
        <form id="frm_hotel_owners_create_account" class="form-horizontal m-t-20" action="index.php?admin=hotel_owners_create_account" method="post">
            <?php draw_hidden_field('act', 'create'); ?>
            <?php draw_token_field(); ?>

            <div class="form-group">
                <div class="col-xs-12">
                    <fieldset style="padding:5px;margin-left:5px;margin-right:10px;">
                        <legend><?= _PERSONAL_DETAILS; ?></legend>
                        <input type="text" class="form-control m-b-5" name="first_name" id="first_name" value="<?= decode_text($first_name); ?>" placeholder="<?= _FIRST_NAME; ?>" autocomplete="off">
                        <input type="text" class="form-control m-b-5" name="last_name" id="last_name" value="<?= decode_text($last_name); ?>" placeholder="<?= _LAST_NAME; ?>" autocomplete="off">
                        <input type="text" class="form-control m-b-5" name="email" id="email" value="<?= decode_text($email); ?>" placeholder="<?= _EMAIL_ADDRESS; ?>" autocomplete="off">
                    </fieldset>
                    <fieldset style="padding:5px;margin-left:5px;margin-right:10px;">
                        <legend><?= _ACCOUNT_DETAILS; ?></legend>
                        <input type="text" class="form-control m-b-5" name="username" id="username" value="<?= decode_text($username); ?>" placeholder="<?= _USERNAME; ?>" autocomplete="off">
                        <input type="password" class="form-control m-b-5" name="password" id="password" value="<?= decode_text($password); ?>" placeholder="<?= _PASSWORD; ?>" autocomplete="off">
                        <input type="password" class="form-control m-b-5" name="confirm_password" id="confirm_password" value="<?= decode_text($confirm_password); ?>" placeholder="<?= _CONFIRM_PASSWORD; ?>" autocomplete="off">
                        <input type="checkbox" class="tution_selector_main main_tuition_checkboxes" name="room_relocation" id="room_relocation" <?= (($room_relocation) ? 'checked="checked"' : '');?> value="1" autocomplete="off">
                        <label for="room_relocation"><?= _MS_ROOM_RELOCATION; ?></label>
                        <br>
                        <input type="checkbox" class="tution_selector_main main_tuition_checkboxes" name="hotel_relocation" id="hotel_relocation" <?= (($hotel_relocation) ? 'checked="checked"' : '');?> value="1" autocomplete="off">
                        <label for="hotel_relocation"><?= _MS_HOTEL_RELOCATION; ?></label>
                    </fieldset>
                </div>
            </div>

            <div id="send_email" class="form-group text-center m-t-30">
                <div class="col-xs-6">
                    <button class="btn btn-custom btn-bordred btn-block waves-effect waves-light" type="submit"><?= _CREATE_ACCOUNT; ?></button>
                </div>
                <div class="col-xs-6">
                    <?= prepare_permanent_link('index.php?admin='.ADMIN_LOGIN.'&type=hotel_owners', _BUTTON_CANCEL, '', 'btn btn-default btn-block waves-effect waves-light'); ?>
                </div>
            </div>
        </form>
        <?php }
}elseif($objLogin->IsLoggedInAsAdmin()){
	draw_important_message(_ALREADY_LOGGED);
	draw_content_start();
?>
	<form action="index.php?page=logout" method="post">
		<?php draw_hidden_field('submit_logout', 'logout'); ?>
		<?php draw_token_field(); ?>
		<input class="form_button" type="submit" name="submit" value="<?= _BUTTON_LOGOUT;?>">
	</form>
<?php
	draw_content_end();	
}else{
	$objSession->SetMessage('notice','');
	draw_important_message(_NOT_AUTHORIZED);
}
echo '<script type="text/javascript">appSetFocus("'.$focus_field.'");</script>';
