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

if(ModulesSettings::Get('accounts', 'hotel_owner_allow_registration') == 'yes' && !$objLogin->IsLoggedIn()){
    
	echo $msg;
	
	echo '<div class="pages_contents">';
	if(!$confirmed && !$draw_form_reg){
		echo '<br />
		<form action="index.php?admin=hotel_owners_confirm_registration" method="post" class="form-horizontal m-t-20" name="frmHotelOwnersConfirmCode" id="frmHotelOwnersConfirmCode">
			'.draw_token_field(false).'
			'.draw_hidden_field('task', 'post_submission', false).'
						
			<div class="form-group ">
                <div class="col-xs-12">
                <input type="text" class="form-control" name="c" id="c" value="" size="27" maxlength="25"  placeholder="'._ENTER_CONFIRMATION_CODE.'" />
                </div>
            </div>
            <div id="send_email" class="form-group text-center m-t-30">
                <div class="col-xs-12">
                    <input class="form_button" type="submit" name="btnSubmit" id="btnSubmit" value="Submit">
                </div>
            </div>
						
		</form>
		<script type="text/javascript">appSetFocus("c")</script>';
	}elseif($draw_form_reg){
	    echo '
            <form action="index.php?admin=hotel_owners_confirm_registration" method="post" class="form-horizontal m-t-20" name="frmHotelOwnersConfirmCode" id="frmHotelOwnersConfirmCode">
                '.draw_token_field(false).'
                '.draw_hidden_field('task', 'update_account', false).'
                '.draw_hidden_field('c', $code, false, 'c').'
                <div class="form-group">
                    <div class="col-xs-12">
                        <fieldset style="padding:5px;margin-left:5px;margin-right:10px;">
                            <legend>'._ACCOUNT_DETAILS.'</legend>
                            <input type="text" class="form-control m-b-5" id="frmReg_user_name" name="user_name" size="32" maxlength="32" required=""  value="'.decode_text($user_name).'" autocomplete="off" placeholder="'._USERNAME.'" />
                            <input type="password" class="form-control m-b-5" id="frmReg_user_password1" name="user_password1" size="32" required=""  maxlength="20" value="'.decode_text($user_password1).'" autocomplete="off" placeholder="'._PASSWORD.'" />
                            <input type="password" class="form-control m-b-5" id="frmReg_user_password2" name="user_password2" size="32" required=""  maxlength="20" value="'.decode_text($user_password2).'" autocomplete="off" placeholder="'._CONFIRM_PASSWORD.'" />
                        </fieldset>
                        <fieldset style="padding:5px;margin-left:5px;margin-right:10px;">
                            <legend>'._PERSONAL_DETAILS.'</legend>
                            <input type="text" class="form-control m-b-5" id="frmReg_first_name" name="first_name" size="32" maxlength="20" value="'.decode_text($first_name).'" autocomplete="off" placeholder="'._FIRST_NAME.'" />
                            <input type="text" class="form-control m-b-5" name="last_name" size="32"  maxlength="20" value="'.decode_text($last_name).'" autocomplete="off" placeholder="'._LAST_NAME.'" />
                            <input type="text" class="form-control m-b-5" name="email" id="email" value="'.decode_text($email).'" placeholder="'._EMAIL_ADDRESS.'" autocomplete="off">
                        </fieldset>
                    </div>
                </div>
                <div id="send_email" class="form-group text-center m-t-30">
                    <div class="col-xs-12">
                        <input class="form_button" type="submit" name="btnSubmit" id="btnSubmit" value="'._SUBMIT.'">
                    </div>
                </div>
                            
            </form>
	    ';
    }
	echo '</div>';
	echo '<script type="text/javascript">appSetFocus("'.$focus_field.'");</script>';

}else{
    draw_important_message(_NOT_AUTHORIZED);
}

