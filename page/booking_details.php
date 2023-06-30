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

if(Modules::IsModuleInstalled('booking')){
	if(ModulesSettings::Get('booking', 'is_active') == 'global' ||
	   ModulesSettings::Get('booking', 'is_active') == 'front-end' ||
	  (ModulesSettings::Get('booking', 'is_active') == 'back-end' && $objLogin->IsLoggedInAsAdmin())	
	){

		draw_reservation_bar('booking_details');
		
		if($current_customer_id != '' && $m == 'edit'){
			if($allow_booking_without_account == 'no') draw_sub_title_bar(_UPDATING_ACCOUNT, true, 'h4');
		}else{

			echo '<table width="100%">';
			echo '<tr>';
			echo '<td align="'.Application::Get('defined_left').'">'.draw_sub_title_bar(_CREATING_NEW_ACCOUNT, false, 'h4').'</td>';
			echo '<td align="'.Application::Get('defined_right').'">';
			if(Modules::IsModuleInstalled('customers')){
				if(ModulesSettings::Get('customers', 'allow_login') == 'yes'){
					echo _ALREADY_HAVE_ACCOUNT;
				}
			}
			echo '</td>';
			echo '</tr>';
			echo '</table>';
		}
?>

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
			}else if(!appIsEmail(frmReg.email.value)){ alert("<?= _EMAIL_VALID_ALERT; ?>"); frmReg.email.focus(); return false;        
			
			}
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
	
		<p style='padding-left:3px;'>
			<?= _ALERT_REQUIRED_FILEDS; ?>
		</p>		
				
		<?= $msg; ?>        
		
		<form action="<?= APPHP_BASE; ?>index.php?page=booking_details" method="post" name="frmRegistration" id="frmRegistration">
		<?php draw_token_field(); ?>
			<?php
				if($current_customer_id != '' && $m == 'edit'){
					draw_hidden_field('act', 'update'); 
					draw_hidden_field('m', 'edit'); 
				}else{
					draw_hidden_field('act', 'create');
				}
			?>
			
			<table width="100%">
			<tbody>			
			<tr><td colspan="3"><b><?= _PERSONAL_DETAILS;?></b><hr size="1" noshade="noshade" /></td></tr>	
			<tr>
				<td width="38%" align="right"><?= _FIRST_NAME;?></td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap"><input type="text" id="first_name" name="first_name" size="32" maxlength="32" value="<?= $first_name;?>" /></td>
			</tr>
			<tr>
				<td align="right"><?= _LAST_NAME;?></td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap"><input type="text" id="last_name" name="last_name" size="32" maxlength="32" value="<?= $last_name;?>" /></td>
			</tr>
			<tr>
				<td align="right"><?= _BIRTH_DATE;?></td>
				<td>&nbsp;</td>
				<td nowrap="nowrap">
					<?= draw_date_select_field('birth_date', $birth_date, '90', '0', false); ?>
				</td>
			</tr>
			<tr>
				<td align="right"><?= _COMPANY;?></td>
				<td>&nbsp;</td>
				<td nowrap="nowrap"><input type="text" id="company" name="company" size="32" maxlength="128" value="<?= $company;?>" /></td>
			</tr>
	
			<tr><td colspan="3"><b><b><?= _BILLING_ADDRESS;?></b><hr size="1" noshade="noshade" /></td></tr>		    
			<tr>
				<td align="right"><?= _ADDRESS;?></td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap"><input type="text" id="b_address" name="b_address" size="32" maxlength="64" value="<?= $b_address;?>" /></td>
			</tr>	
			<tr>
				<td align="right"><?= _ADDRESS_2;?></td>
				<td>&nbsp;</td>
				<td nowrap="nowrap"><input type="text" id="b_address_2" name="b_address_2" size="32" maxlength="64" value="<?= $b_address_2;?>" /></td>
			</tr>	
			<tr>
				<td align="right"><?= _CITY;?></td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap"><input type="text" id="b_city" name="b_city" size="32" maxlength="64" value="<?= $b_city;?>" /></td>
			</tr>	
			<tr>
				<td align="right"><?= _ZIP_CODE;?></td>
				<td><!--span class="mandatory_star">*</span--></td>
				<td nowrap="nowrap"><input type="text" id="b_zipcode" name="b_zipcode" size="32" maxlength="32" value="<?= $b_zipcode;?>" /></td>
			</tr>
			<tr>
				<td align="right"><?= _COUNTRY;?></td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap">				
					<?php Countries::DrawAllCountries('b_country', $b_country, true, "appChangeCountry(this.value,'b_state','','".Application::Get('token')."')"); ?>
				</td>
			</tr>	
			<tr>
				<td align="right"><?= _STATE_PROVINCE;?></td>
				<td></td>
				<td nowrap="nowrap"><input type="text" id="b_state" name="b_state" size="32" maxlength="64" value="<?= $b_state;?>" /></td>
			</tr>					
	
			<tr><td height="20" colspan="3"><b><?= _CONTACT_INFORMATION;?></b><hr size="1" noshade="noshade" /></td></tr>
			<tr>
				<td align="right"><?= _PHONE;?></td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap"><input type="text" id="phone" name="phone" size="32" maxlength="32" value="<?= $phone;?>" /></td>
			</tr>
			<tr>
				<td align="right"><?= _FAX;?></td>
				<td></td>
				<td nowrap="nowrap"><input type="text" id="fax" name="fax" size="32" maxlength="32" value="<?= $fax;?>" /></td>
			</tr>
			<tr>
				<td align="right"><?= _EMAIL_ADDRESS;?></td>
				<td><span class="mandatory_star">*</span></td>
				<td nowrap="nowrap">				 
					<?= _ENTER_EMAIL_ADDRESS;?>
					<br />
					<input type="text" id="email" name="email" size="32" maxlength="70" value="<?= $email;?>" />
				</td>
			</tr>
	
			<?php if($current_customer_id != '' && $m == 'edit'){ ?>
				<tr>
					<td colspan="3" align="center">
						<br /><br />
						<input type='submit' class="form_button" name="btnSubmitPD" id="btnSubmitPD" value="<?= _BUTTON_UPDATE; ?>" onclick="return btnSubmitPD_OnClick()">
						&nbsp;&nbsp;&nbsp;
						<input type="button" class="form_button" onclick="javascript:appGoTo('page=booking_details')" value="<?= _CHECKOUT; ?>" />
					</td>
				</tr>
			<?php }else{ ?>
				<?php if($allow_booking_without_account == 'no'){ ?>
				<tr><td colspan="3"><b><?= _ACCOUNT_DETAILS;?></b><hr size="1" noshade="noshade" /></td></tr>
				<tr>
					<td align="right"><?= _USERNAME;?></td>
					<td class="mandatory_star">*</td>
					<td nowrap="nowrap"><input type="text" id="user_name" name="user_name" size="32" maxlength="32" value="<?= $user_name;?>" /></td>
				</tr>		    
				<tr>
					<td align="right"><?= _PASSWORD;?></td>
					<td><span class="mandatory_star">*</span></td>
					<td nowrap="nowrap"><input type="password" id="user_password1" name="user_password1" size="32" maxlength="20" value="<?= $user_password1;?>" /></td>
				</tr>		    
				<tr>
					<td align="right"><?= _CONFIRM_PASSWORD;?></td>
					<td class="mandatory_star">*</td>
					<td nowrap="nowrap"><input type="password" id="user_password2" name="user_password2" size="32" maxlength="20" value="<?= $user_password1;?>" /></td>
				</tr>
				<?php } ?>
				
				<?php if($image_verification_allow == 'yes'){?>
				<tr><td height="20" colspan="3"><b><?= _IMAGE_VERIFICATION; ?></b><hr size="1" noshade="noshade" /></td></tr>
				<tr valign="top">
					<td align="left"><?= _TYPE_CHARS; ?></td>
					<td></td>
					<td>
						<table>
						<tr>
							<td width="50px"><img style="padding:0px; margin:0px;" id="captcha_image" src="modules/captcha/securimage_show.php?sid=<?= md5(uniqid(time())); ?>" /></td>
							<td>
								<a href="modules/captcha/securimage_play.php"><img style="padding:0px; margin:0px; border:0px;" id="captcha_image_play" src="modules/captcha/images/audio_icon.gif" title="<?= _PLAY; ?>" alt="<?= _PLAY; ?>" /></a><br />
								<img style="cursor:pointer; padding:0px; margin:0px; border:0px;" id="captcha_image_reload" src="modules/captcha/images/refresh.gif" style="cursor:pointer;" onclick="document.getElementById('captcha_image').src = 'modules/captcha/securimage_show.php?sid=' + Math.random(); appSetFocus('captcha_code'); return false;" title="<?= _REFRESH; ?>" alt="<?= _REFRESH; ?>" />
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="text" name="captcha_code" id="captcha_code" size="21" value="" />
							</td>
						</tr>
						</table>			    			    
					</td>
				</tr>
				<?php } ?>
				
				<tr><td colspan="3" nowrap height="7px"></td></tr>
				<tr>
					<td colspan="3" align="left">
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
							<td><a href="<?= APPHP_BASE; ?>index.php?page=booking_details#top" onclick="javascript:appShowTermsAndConditions()"><?= _AGREE_CONF_TEXT; ?></a></td>
						</tr>					
						</table>
					</td>
				</tr>		
				<tr>
					<td colspan="3" align="center">
						<br /><br />
						<input type="submit" class="form_button" name="btnSubmitPD" id="btnSubmitPD" value="<?= _SUBMIT; ?>" onclick="return btnSubmitPD_OnClick()">
					</td>
				</tr>			
			<?php } ?>
			
			<tr><td colspan="2">&nbsp;</td></tr>
			<?php if($allow_booking_without_account == 'no'){ ?>
			<tr>
			<td colspan="3" align="left">
				<p><?= _CREATE_ACCOUNT_NOTE; ?></p>
			</td>
			</tr>
			<?php } ?>
			
			</table>
		</form>

		<div id="fade" class="black_overlay" onclick="javascript:appCloseTermsAndConditions();"></div>
		<div id="light">
			<div class="white_header">
				<div class="title_left">
				<?php
					$objPageTemp = new Pages('terms_and_conditions', true);				
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
		appChangeCountry(jQuery("#b_country").val(),'b_state','<?= decode_text($b_state);?>','<?= Application::Get('token');?>');
		</script>		
	<?php		 
	}else{
		draw_title_bar(_BOOKINGS);
		draw_important_message(_NOT_AUTHORIZED);
	}	
}else{
	draw_title_bar(_BOOKINGS);
    draw_important_message(_NOT_AUTHORIZED);
}

