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

if(Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
	   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
	  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
	){
		draw_widget_reservation_bar('booking_details');
?>
        <h4><?= _BOOKING_DETAILS; ?></h4>

		<table width="100%">
		<tr>
		<td><?= draw_sub_title_bar(_CREATING_NEW_ACCOUNT, false, 'h4') ?></td>
		<td class="right">
		<?php
			if(Modules::IsModuleInstalled('customers') && ModulesSettings::Get('customers', 'allow_login') == 'yes'){
				if(!$objLogin->IsLoggedIn()){
					echo str_ireplace('index.php?customer=login', $homeurl.'index.php?page=login&'.WIDGET_HASH, _ALREADY_HAVE_ACCOUNT);
				}else{
					echo '<a href="'.$homeurl.'index.php?page=logout&'.WIDGET_HASH.'">'._BUTTON_LOGOUT.'</a>';
				}
			}
		?>
		</td>
		</tr>
		</table>

        <hr/>
        <div class="center_box_content" id="block-result">

		<!-- NOT LOGGED -->
		<script type="text/javascript"> 
		function btnSubmitPD_OnClick(){

			frmReg = document.getElementById("frmRegistration");
			
			if(frmReg.first_name.value == ""){ alert("<?= _FIRST_NAME_EMPTY_ALERT; ?>"); frmReg.first_name.focus(); return false;
			}else if(frmReg.last_name.value == ""){ alert("<?= _LAST_NAME_EMPTY_ALERT; ?>"); frmReg.last_name.focus(); return false;        
			}else if(frmReg.b_address.value == ""){ alert("<?= _ADDRESS_EMPTY_ALERT; ?>"); frmReg.b_address.focus(); return false;        
			}else if(frmReg.b_city.value == ""){ alert("<?= _CITY_EMPTY_ALERT; ?>"); frmReg.b_city.focus(); return false;        
//			}else if(frmReg.b_zipcode.value == ""){ alert("<?= _ZIPCODE_EMPTY_ALERT; ?>"); frmReg.b_zipcode.focus(); return false;        
			}else if(frmReg.b_country.value == ""){ alert("<?= _COUNTRY_EMPTY_ALERT; ?>"); frmReg.b_country.focus(); return false;        
			}else if(frmReg.phone.value == ""){ alert("<?= _PHONE_EMPTY_ALERT; ?>"); frmReg.phone.focus(); return false;        
			}else if(frmReg.email.value == ""){ alert("<?= _EMAIL_EMPTY_ALERT; ?>"); frmReg.email.focus(); return false;        
			}else if(!appIsEmail(frmReg.email.value)){ alert("<?= _EMAIL_VALID_ALERT; ?>"); frmReg.email.focus(); return false; }
			
			<?php if($current_customer_id != '' && $m == 'edit'){ ?>
			
			<?php }else{ ?>
				<?php if($allow_booking_without_account == 'no'){ ?>
				else if(frmReg.user_name.value == ""){
					alert("<?= _USERNAME_EMPTY_ALERT; ?>");
					frmReg.user_name.focus();
					return false;        
				}else if(frmReg.user_password1.value == ""){
					alert("<?= _PASSWORD_IS_EMPTY; ?>");
					frmReg.user_password1.focus();
					return false;        
				}else if(frmReg.user_password2.value == ""){
					alert("<?= _CONF_PASSWORD_IS_EMPTY; ?>");
					frmReg.user_password2.focus();
					return false;        
				}else if(frmReg.user_password1.value != frmReg.user_password2.value){
					alert("<?= _CONF_PASSWORD_MATCH; ?>");
					frmReg.user_password2.focus();
					return false;        		
				}
				<?php } ?>
				<?php if($image_verification_allow == 'yes'){ ?>
				else if(frmReg.captcha_code && frmReg.captcha_code.value == ""){
					alert("<?= _IMAGE_VERIFY_EMPTY; ?>");
					frmReg.captcha_code.focus();
					return false;
				}
				<?php } ?>
				else if(frmReg.agree && !frmReg.agree.checked){
					alert("<?= _CONFIRM_TERMS_CONDITIONS; ?>");
					return false;
				}
			<?php } ?>
			return true;
		}
		</script>
				
		<?= $msg; ?>
		<form action="<?= $homeurl; ?>index.php?page=booking_details" method="post" name="frmRegistration" id="frmRegistration">
		<?php draw_token_field(); ?>
			<?php
				if($current_customer_id != '' && $m == 'edit'){
					draw_hidden_field('act', 'update'); 
					draw_hidden_field('m', 'edit'); 
				}else{
					draw_hidden_field('act', 'create');
				}
			?>
			<div class="personal_info">
				<div class="reservation_cart_header"><?= _PERSONAL_DETAILS; ?></div>
				<div class="personal-info-div info-div">
					<div class="each-info">
						<div class="each-info-left">
							<?= _FIRST_NAME; ?>: <span class="mandatory_star">*</span>
						</div>
						<div class="each-info-right">
							<input type="text" id="first_name" name="first_name" size="32" maxlength="32" value="<?= $first_name;?>" />
						</div>
					</div>
					<div class="each-info">
						<div class="each-info-left">
							<?= _LAST_NAME; ?>: <span class="mandatory_star">*</span>
						</div>
						<div class="each-info-right">
							<input type="text" id="last_name" name="last_name" size="32" maxlength="32" value="<?= $last_name;?>" />
						</div>
					</div>
					<div class="each-info">
						<div class="each-info-left">
							<?= _BIRTH_DATE; ?>:
						</div>
						<div class="each-info-right">
							<?= draw_date_select_field('birth_date', $birth_date, '90', '0', false); ?>
						</div>
					</div>
					<div class="each-info">
						<div class="each-info-left">
							<?= _COMPANY; ?>:
						</div>
						<div class="each-info-right">
							<input type="text" id="company" name="company" size="32" maxlength="128" value="<?= $company;?>" />
						</div>
					</div>
				</div>
			</div>
			<div class="billing_info">
				<div class="reservation_cart_header"><?= _BILLING_ADDRESS; ?></div>
				<div class="billing-info-div info-div">
					<div class="each-info">
						<div class="each-info-left">
							<?= _ADDRESS; ?>: <span class="mandatory_star">*</span>
						</div>
						<div class="each-info-right">
							<input type="text" id="b_address" name="b_address" size="32" maxlength="64" value="<?= $b_address;?>" />
						</div>
					</div>

					<div class="each-info">
						<div class="each-info-left">
							<?= _ADDRESS_2; ?>:
						</div>
						<div class="each-info-right">
							<input type="text" id="b_address_2" name="b_address_2" size="32" maxlength="64" value="<?= $b_address_2;?>" />
						</div>
					</div>
					<div class="each-info">
						<div class="each-info-left">
							<?= _CITY; ?>: <span class="mandatory_star">*</span>
						</div>
						<div class="each-info-right">
							<input type="text" id="b_city" name="b_city" size="32" maxlength="64" value="<?= $b_city;?>" />
						</div>
					</div>                        
					<div class="each-info">
						<div class="each-info-left">
							<?= _ZIP_CODE; ?>:
						</div>
						<div class="each-info-right">
							<input type="text" id="b_zipcode" name="b_zipcode" size="32" maxlength="32" value="<?= $b_zipcode;?>" />
						</div>
					</div>
					<div class="each-info">
						<div class="each-info-left">
							<?= _COUNTRY; ?>: <span class="mandatory_star">*</span>
						</div>
						<div class="each-info-right">
							<?php Countries::DrawAllCountries('b_country', $b_country, true, "appChangeCountry(this.value,'b_state','','".Application::Get('token')."','".$host."')"); ?>
						</div>
					</div>
					<div class="each-info">
						<div class="each-info-left">
							<?= _STATE_PROVINCE; ?>: <span class="mandatory_star">*</span>
						</div>
						<div class="each-info-right">
							<input type="text" id="b_state" name="b_state" size="32" maxlength="64" value="<?= $b_state;?>" />
						</div>
					</div>
				</div>
			</div>
			<div class="contact_info">
				<div class="reservation_cart_header"><?= _CONTACT_INFORMATION; ?></div>
				<div class="billing-info-div info-div">
					<div class="each-info">
						<div class="each-info-left">
							<?= _PHONE; ?>: <span class="mandatory_star">*</span>
						</div>
						<div class="each-info-right">
							<input type="text" id="phone" name="phone" size="32" maxlength="32" value="<?= $phone;?>" />
						</div>
					</div>
					<div class="each-info">
						<div class="each-info-left">
							<?= _FAX; ?>:
						</div>
						<div class="each-info-right">
							<input type="text" id="fax" name="fax" size="32" maxlength="32" value="<?= $fax;?>" />
						</div>
					</div>
					<div class="each-info">
						<div class="each-info-left">
							<?= _EMAIL_ADDRESS; ?>: <span class="mandatory_star">*</span>
						</div>
						<div class="each-info-right">
							<?= _ENTER_EMAIL_ADDRESS;?>
							<input type="text" id="email" name="email" size="32" maxlength="70" value="<?= $email;?>" autocomplete="off" />
						</div>
					</div>
				</div>
			</div>
		<?php if($current_customer_id != '' && $m == 'edit'){ ?>
			<div class="next-step-wrapper">
				<input class="reservation_next_step" name="btnSubmitPD" id="btnSubmitPD" type="submit" value="<?= _BUTTON_UPDATE; ?>" onclick="return btnSubmitPD_OnClick()">
				&nbsp;&nbsp;&nbsp;
				<input type="button" class="form_button" onclick="javascript:appGoTo('page=booking_details')" value="<?= _CHECKOUT; ?>" />
			</div>
		<?php }else{ ?>
			<?php if($allow_booking_without_account == 'no'){ ?>
			<div class="verification_info">
				<div class="reservation_cart_header"><?= _ACCOUNT_DETAILS; ?></div>
				<div class="billing-info-div info-div">
					<div class="each-info">
						<div>
							<div class="each-info-left">
								<?= _USERNAME; ?>: <span class="mandatory_star">*</span>
							</div>
                            <div class="each-info-right">
								<input type="text" id="user_name" name="user_name" size="32" maxlength="32" value="<?= decode_text($user_name);?>" autocomplete="off" />
							</div>
						</div>		    
                        <div class="each-info">
                            <div class="each-info-left">
                                <?= _PASSWORD; ?>: <span class="mandatory_star">*</span>
                            </div>
                            <div class="each-info-right">
                                <input type="password" id="user_password1" name="user_password1" size="32" maxlength="20" value="<?= $user_password1;?>" />
                            </div>
                        </div>
                        <div class="each-info">
                            <div class="each-info-left">
                                <?= _CONFIRM_PASSWORD; ?>: <span class="mandatory_star">*</span>
                            </div>
                            <div class="each-info-right">
                                <input type="password" id="user_password2" name="user_password2" size="32" maxlength="20" value="<?= $user_password1;?>" />
                            </div>
                        </div>
					</div>
				</div>
				<?php } ?>
				<?php if($image_verification_allow == 'yes'){ ?>
                <div class="verification_info">
                    <div class="reservation_cart_header"><?= _IMAGE_VERIFICATION; ?></div>
                    <div class="verification-info-div info-div">
                        <div class="each-info">
                            <div class="each-info-left">
                                <?= _TYPE_CHARS; ?>
                            </div>
                            <div class="each-info-right">
                                <table>
                                    <tr>
                                        <td width="50px"><img style="padding:0px; margin:0px;" id="captcha_image" src="<?= $host; ?>modules/captcha/securimage_show.php?sid=<?= md5(uniqid(time())); ?>" /></td>
                                        <td>
                                            <a href="<?= $host; ?>modules/captcha/securimage_play.php"><img style="padding:0px; margin:0px; border:0px;" id="captcha_image_play" src="<?= $host; ?>modules/captcha/images/audio_icon.gif" title="<?= _PLAY; ?>" alt="<?= _PLAY; ?>" /></a><br />
                                            <img style="cursor:pointer; padding:0px; margin:0px; border:0px;" id="captcha_image_reload" src="<?= $host; ?>modules/captcha/images/refresh.gif" style="cursor:pointer;" onclick="document.getElementById('captcha_image').src = '<?= $host; ?>modules/captcha/securimage_show.php?sid=' + Math.random(); appSetFocus('captcha_code'); return false;" title="<?= _REFRESH; ?>" alt="<?= _REFRESH; ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <input type="text" name="captcha_code" id="captcha_code" size="21" value="" />
                                        </td>
                                    </tr>
                                </table>			    			    
                            </div>
                        </div>
                    </div>
                </div>
				<?php } ?>
				
				<p style='padding-right:3px;float:<?= Application::Get('lang_dir') == 'rtl' ? 'left' : 'right'; ?>;'>
                    <?= _ALERT_REQUIRED_FILEDS; ?>
                </p>		
                    <table>					
                        <tr valign="top">
                            <td align="right"><input type="checkbox" name="send_updates" id="send_updates" <?= (($send_updates == '1') ? 'checked="checked"' : '');?> value="1"></td>
                            <td>&nbsp;</td>
                            <td><?= _NOTIFICATION_MSG; ?></td>
                        </tr>					
                        <tr><td colspan="3" nowrap="nowrap" height="5px"></td></tr>
                        <tr valign="middle">
                            <td align="right"><input type="checkbox" name="agree" id="agree" value="1" <?= ($agree == '1') ? 'checked="checked"' : ''; ?>></td>
                            <td>&nbsp;</td>
                            <td><a href="<?= $homeurl; ?>index.php?page=booking_details&<?= WIDGET_HASH; ?>#main-container" onclick="javascript:openModal('term_condition');"><?= _AGREE_CONF_TEXT; ?></a></td>
                        </tr>					
                    </table>
                <div class="next-step-wrapper">
                    <input class="reservation_next_step" name="btnSubmitPD" id="btnSubmitPD" type="submit" value="<?= _SUBMIT; ?>" onclick="return btnSubmitPD_OnClick()">
                </div>
			<?php } ?>
		</form>

		<?php
			$objPageTemp = new Pages('terms_and_conditions', true);				
			echo Rooms::DrawWidgetJS();
			echo Rooms::DrawWidgetOneColumnModal('term_condition', $objPageTemp->DrawText(false));
		?>

		<div id="fade" class="black_overlay" onclick="javascript:appCloseTermsAndConditions();"></div>
		<div id="light">
			<div class="white_header">
				<div class="title_left">
				<?php
					
					$objPageTemp->DrawTitle(); 
				?>
				</div>
				<div class="title_right">
					<a href = "javascript:void(0)" onclick="javascript:appCloseTermsAndConditions();"><?= _CLOSE; ?></a>
				</div>			
			</div>
			<div class="white_content">	
				<?php
					$objPageTemp->DrawText();	
				?>
			</div>
		</div>		

		<script type="text/javascript">
		appSetFocus('<?= $focus_field; ?>');
            appChangeCountry(jQuery("#b_country").val(),'b_state','<?= decode_text($b_state);?>','<?= Application::Get('token');?>', '<?= $host; ?>');
		</script>		
    </div>		
	<?php		 
	}else{
        echo '<h4>'._BOOKINGS.'</h4>';
        echo '<hr/>';
        echo '<div class="center_box_content" id="block-result">';
		draw_important_message(_NOT_AUTHORIZED);
        echo '</div>';
	}	
}else{
    echo '<h4>'._BOOKINGS.'</h4>';
    echo '<hr/>';
    echo '<div class="center_box_content" id="block-result">';
    draw_important_message(_NOT_AUTHORIZED);
    echo '</div>';
}

