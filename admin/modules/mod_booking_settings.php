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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('booking')){

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$settings_key   = MicroGrid::GetParameter('settings_key', false);
	$settings_value = MicroGrid::GetParameter('settings_value', false);
	$mode   		= 'view';
	$msg    		= '';
	
	$objBookingSettings = new ModulesSettings('booking');
	
	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objBookingSettings->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objBookingSettings->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		$error = false;
		if($settings_key == 'pre_payment_value'){
			if(ModulesSettings::Get('booking', 'pre_payment_type') == 'percentage' && $settings_value > 99){
				$msg = draw_important_message(_PERCENTAGE_MAX_ALOWED_VALUE, false);
				$mode = 'edit';
				$error = true;
			}				
		}elseif($settings_key == 'pre_payment_type' && $settings_value == 'percentage'){
			if(ModulesSettings::Get('booking', 'pre_payment_value') > 99){
				$msg = draw_important_message(_PERCENTAGE_MAX_ALOWED_VALUE, false);
				$mode = 'edit';
				$error = true;
			}
		}
		
		if(!$error){			
			if($objBookingSettings->UpdateRecord($rid)){
				if($settings_key == 'vat_value'){
					$objCountries = new Countries();
					$objCountries->UpdateVAT($settings_value);
				}
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objBookingSettings->error, false);
				$mode = 'edit';
			}		
		}
	}elseif($action=='delete'){
		if($objBookingSettings->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objBookingSettings->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
    }else{
        $action = '';
	}
	
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_BOOKINGS=>'',_BOOKING_SETTINGS=>'',ucfirst($action)=>'')));
	
    echo '<br />';
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objBookingSettings->DrawViewMode();

        $remember_text = '';
        if(ModulesSettings::Get('booking', 'allow_separate_gateways') == 'yes'){
            $remember_text = 'Remember to repeat all these instructions for each hotel separately!<br /><br />';
        }
		
		echo '<br /><br />
		<script type="text/javascript">
			var arrTabs = ["Poa","Online","PayPal","2CO","AuthorizeNet","BankTransfer"];
		</script>
		<fieldset class="instructions">
		<legend>
			<b>INSTRUCTIONS:
				&nbsp;<a id="tabPoa" style="font-weight:bold" href="javascript:void(\'Poa\')" onclick="javascript:appToggleTabs(\'Poa\', arrTabs)">[ Pay On Arrival ]</a>
				&nbsp;<a id="tabOnline" href="javascript:void(\'Online\')" onclick="javascript:appToggleTabs(\'Online\', arrTabs)">[ On-Line Order ]</a>
				&nbsp;<a id="tabPayPal" href="javascript:void(\'PayPal\')" onclick="javascript:appToggleTabs(\'PayPal\', arrTabs)">[ PayPal ]</a>
				&nbsp;<a id="tab2CO" href="javascript:void(\'2CO\')" onclick="javascript:appToggleTabs(\'2CO\', arrTabs)">[ 2CO ]</a>
				&nbsp;<a id="tabAuthorizeNet" href="javascript:void(\'AuthorizeNet\')" onclick="javascript:appToggleTabs(\'AuthorizeNet\', arrTabs)">[ Authorize.Net ]</a>
				&nbsp;<a id="tabBankTransfer" href="javascript:void(\'BankTransfer\')" onclick="javascript:appToggleTabs(\'BankTransfer\', arrTabs)">[ Bank Transfer ]</a>
			</b>
		</legend>

		<div id="contentPoa" style="display:;padding:10px;">
			\'Pay on Arrival\' (POA) is designed to allow the customer to make a reservation on the site without any advance payment.<br />
			The administrator receives a notification about placing this reservation and can complete it when the customer arrives to the hotel and pays his reservation in cash/check.
			<br /><br />
            '.$remember_text.'
			IMPORTANT:
			<ol>
				<li>Administrator can view bookings '.prepare_permanent_link('index.php?admin=mod_booking_bookings', 'here').'.</li>
			</ol>
		</div>

		<div id="contentOnline" style="display:none;padding:10px;">
			\'On-line Order\' is designed to allow the customer to make a reservation on the site without any advance payment.<br />
			The administrator receives a notification about placing the order and can complete the order by himself.
			<br /><br />
            '.$remember_text.'
			IMPORTANT:
			<ol>
				<li>Administrator can view bookings '.prepare_permanent_link('index.php?admin=mod_booking_bookings', 'here').'.</li>
				<li>Administrator may require collecting of credit card info via <b>Modules -> Booking Settings</b></li>
			</ol>
		</div>
		
		<div id="contentPayPal" style="display:none;padding:10px;">
		To make PayPal processing system works on your site you have to perform the following steps:<br/><br/>
        '.$remember_text.'
		<ol>
			<li>Create an account on PayPal: <a href="https://www.paypal.com" target="_blank" rel="noopener noreferrer">https://www.paypal.com</a></li>
			<li>After account is created, log into and select from the top menu: <b>My Account -> Profile</b></li>
			<li>On <b>Profile Summary</b> page select from the <b>Selling Preferences</b> column: <b>Instant Payment Notification (IPN) Preferences</b>.  </li>
			<li>Turn \'On\' IPN by selecting <b>Receive IPN messages (Enabled)</b> and write into <b>Notification URL</b>: {site}/index.php?page=booking_notify_paypal, where {site} is a full path to your site.<br /><br />
				<span class="code">
			    For example: <b>http://your_domain.com/index.php?page=booking_notify_paypal</b> or <br /><b>http://your_domain.com/new_site/index.php?page=booking_notify_paypal</b>
				</span>
			</li>
			<li>
				Then go to <b>My Account -> Profile -> Website Payment Preferences</b>, turn <b>Auto Return</b> \'On\' and write into <b>Return URL</b>: {site}/index.php?page=booking_return, where {site} is a full path to your site.<br /><br />
				<span class="code">
			    For example: <b>http://your_domain.com/index.php?page=booking_return</b>
				</span>
			</li>
		</ol>
		</div>

		<div id="content2CO" style="display:none;padding:10px;">
		To make 2CO processing system works on your site you have to perform the following steps:<br/><br/>
        '.$remember_text.'
		<ol>
			<li>Create an account on 2Checkout: <a href="http://www.2checkout.com" target="_blank" rel="noopener noreferrer">http://www.2checkout.com</a></li>
			<li>After account is created, <a href="https://www.2checkout.com/2co/login" target="_blank" rel="noopener noreferrer">log into</a> and select from the top menu: <b>Notifications -> Settings</b></li>
			<li>On <b>Instant Notification Settings</b> page enter into <b>Global URL</b> textbox: {site}/index.php?page=booking_notify_2co, where {site} is a full path to your site. <br /><br />
				<span class="code">
			    For example: <b>http://your_domain.com/index.php?page=booking_notify_2co</b> or <br /><b>http://your_domain.com/new_site/index.php?page=booking_notify_2co</b>
				</span>	<br /><br />
				Then click on <b>Enable All Notifications</b> and <b>Save Settings</b> buttons.
			</li>			
			<li>
				Go to <b>Account -> Site Management</b>, set <b>Demo Setting</b> on \'Off\' and enter into <b>Approved URL</b> textbox: {site}/index.php?page=booking_return, where {site} is a full path to your site.<br /><br />
				<span class="code">
			    For example: <b>http://your_domain.com/index.php?page=booking_return</b> or <br /><b>http://your_domain.com/new_site/index.php?page=booking_return</b>
				</span>	<br />
			</li>
		</ol>
		</div>

		<div id="contentAuthorizeNet" style="display:none;padding:10px;">
		To make Authorize.Net processing system works on your site you have to perform the following steps:<br/><br/>
        '.$remember_text.'
		<ol>
			<li>Create an account on Authorize.Net: <a href="http://www.authorize.net/solutions/merchantsolutions/" target="_blank" rel="noopener noreferrer">http://www.authorize.net</a><br /></li>
			<li>After account is created, <a href="https://account.authorize.net/" target="_blank" rel="noopener noreferrer">log into</a> and obtain <b>API Login ID</b> and <b>Transaction Key</b>. Find here how to do this: <a href="http://developer.authorize.net/faqs/" target="_blank" rel="noopener noreferrer">Authorize.Net FAQ</a><br /></li>
			<li>
				Then go back to <b>Administrator Panel -> Modules -> '.prepare_permanent_link('index.php?admin=mod_booking_settings', 'Booking Settings').'</b>, <br>where activate <b>Authorize.Net payment type</b>, enter <b>API Login ID</b> and <b>Transaction Key</b>.<br /><br />
			</li>
		</ol>
		</div>

		<div id="contentBankTransfer" style="display:none;padding:10px;">
		\'BankTransfer\' is designed to allow the customer to make a reservation on the site without any advance payment.<br />
		The administrator receives a notification about placing this reservation and can complete it after the customer will pay
		a required sum to the provided bank account.<br><br>
        '.$remember_text.'
		<ol>
		  <li>Administrator can view bookings '.prepare_permanent_link('index.php?admin=mod_booking_bookings', 'here').'.</li>
  		</ol>
		</div>
		</fieldset>';
        
        
		
	}elseif($mode == 'add'){		
		$objBookingSettings->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objBookingSettings->DrawEditMode($rid);		
	}elseif($mode == 'details'){ 
		$objBookingSettings->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

