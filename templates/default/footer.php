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

if(Application::Get('page') == 'home' && Application::Get('customer') == ''){
	$ftitle = 'ftitle';
	$footerbg = 'footerbg';
	$footerbg3 = 'footerbg3';
}else{
	$ftitle = 'ftitleblack';
	$footerbg = 'footerbgblack';
	$footerbg3 = 'footerbg3black';
}

?>
<!-- FOOTER -->
<div class="<?= $footerbg; ?>">
    <div class="container">		
        
        <div class="col-md-3">
            <span class="<?= $ftitle; ?>"><?= _LETS_SOCIALIZE; ?></span>
            <div class="scont">
                <a href="#" class="social1b"><img src="<?= 'templates/'.Application::Get('template').'/images/'; ?>icon-facebook.png" alt=""/></a>
                <a href="#" class="social2b"><img src="<?= 'templates/'.Application::Get('template').'/images/'; ?>icon-twitter.png" alt=""/></a>
                <a href="#" class="social3b"><img src="<?= 'templates/'.Application::Get('template').'/images/'; ?>icon-gplus.png" alt=""/></a>
                <a href="#" class="social4b"><img src="<?= 'templates/'.Application::Get('template').'/images/'; ?>icon-youtube.png" alt=""/></a>
                <br/><br/>
            </div>
            
            <?php
                if(Modules::IsModuleInstalled('booking') && (in_array(ModulesSettings::Get('booking', 'is_active'), array('global', 'front-end')))){
                    if(ModulesSettings::Get('booking', 'payment_type_paypal') != 'no' || ModulesSettings::Get('booking', 'payment_type_2co') != 'yes' || ModulesSettings::Get('booking', 'payment_type_authorize') != 'yes'){
                        echo '<div class="hidden-xs">';
						echo '<span class="'.$ftitle.'">'._PAYMENT_METHODS.'</span>';
                        echo '<div class="scont"><img src="images/ppc_icons/logo_paypal.gif" title="PayPal" alt="PayPal" />
                              <img src="images/ppc_icons/logo_ccVisa.gif" title="Visa" alt="Visa" />
                              <img src="images/ppc_icons/logo_ccMC.gif" title="MasterCard" alt="MasterCard" />
                              <img src="images/ppc_icons/logo_ccAmex.gif" title="Amex" alt="Amex" /></div>';
						echo '</div>';	  
                    }
                }
            ?>            
            <br/>
            
            <a href="index.php"><img class="footer-logosmal" src="<?= 'templates/'.Application::Get('template').'/images/'; ?>logosmal2.png" alt="small logo" /></a><br/>
            <span class="grey2"><?= _COPYRIGHT; ?> &copy; <?= date('Y'); ?><br> <?= _ALL_RIGHTS_RESERVED; ?></span><br/>
			<?php if(SHOW_COPYRIGHT){ ?>
			<span class="powered">Powered by <a href="http://apphp.com">ApPHP</a></span>
			<?php } ?>
        </div>
        <!-- End of column 1-->

        <div class="col-md-3">
			<?php if(is_mobile()){ ?>
				<h3 class="collapsebtn collapsed <?= $ftitle; ?>" data-target="#collapse-footer-general" data-toggle="collapse"><?= _GENERAL; ?> <span class="collapsearrow"></span></h3>
				<ul id="collapse-footer-general" class="footerlistblack collapse">			
			<?php }else{ ?>
				<span class="<?= $ftitle; ?>"><?= _GENERAL; ?></span>
				<br/><br/>
				<ul class="footerlistblack">
			<?php } ?>
            <?php  Menu::DrawFooterMenu('ul', 'general'); ?>
            </ul>
        </div>
        <!-- End of column 2-->		
        
        <div class="col-md-3">
			<?php if(is_mobile()){ ?>
				<h3 class="collapsebtn collapsed <?= $ftitle; ?>" data-target="#collapse-footer-info" data-toggle="collapse"><?= _INFORMATION; ?> <span class="collapsearrow"></span></h3>
				<ul id="collapse-footer-info" class="footerlistblack collapse">			
			<?php }else{ ?>
				<span class="<?= $ftitle; ?>"><?= _INFORMATION; ?></span>
				<br/><br/>
				<ul class="footerlistblack">
			<?php } ?>
            <?php Menu::DrawFooterMenu('ul', 'system');	?>
            </ul>				
        </div>
        <!-- End of column 3-->		
        
        <div class="col-md-3 grey">
            <?php
                if(Modules::IsModuleInstalled('news')){
                    $objNews = News::Instance();
                    echo $objNews->DrawSubscribeBlockFooter(false);	
                }
            ?>
            <?php
				$support_info = Hotels::GetSupportInfo();			
				if(!empty($support_info['phone']) || !empty($support_info['email'])){
					echo '<br/><br/>';
					echo '<span class="ftitle">'._CUSTOMER_SUPPORT.'</span><br/>';
					echo !empty($support_info['phone']) ? '<span class="pnr">'.$support_info['phone'].'</span><br/>' : '';
					//echo !empty($support_info['fax']) ? '<span class="pnr">'.$support_info['fax'].'</span><br/>' : '';
					echo !empty($support_info['email']) ? '<span class="grey2">'.$support_info['email'].'</span>' : '';
				}				
			?>

			<br><br>
			<a href="feeds/rss.xml" title="RSS Feed"><img src="templates/default/images/rss.png" alt="RSS Feed"></a>
        </div>			
        <!-- End of column 4-->			            
    </div>	
</div>

<div class="<?= $footerbg3; ?>">
    <div class="container center grey">
        
        <form name="frmLogout" id="frmLogout" action="<?= APPHP_BASE; ?>index.php" method="post">
        <?php if($objLogin->IsLoggedIn()){ ?>
            <?php draw_hidden_field('submit_logout', 'logout'); ?>	
			<?= prepare_permanent_link('index.php?customer=home', _DASHBOARD, '', 'main_link'); ?> &nbsp;|&nbsp;	
			<?= prepare_permanent_link('index.php?customer=my_account', _MY_ACCOUNT, '', 'main_link'); ?> &nbsp;|&nbsp;	
            <a class="main_link" href="javascript:appFormSubmit('frmLogout');"><?= _BUTTON_LOGOUT; ?></a>
        <?php }else{ ?>
            <?php
                if(Modules::IsModuleInstalled('customers')){
                    if(ModulesSettings::Get('customers', 'allow_login') == 'yes'){
						echo prepare_permanent_link('index.php?customer=login', _CUSTOMER_LOGIN, '', 'main_link');
						if(ModulesSettings::Get('customers', 'allow_agencies') == 'yes' && SHOW_TRAVEL_AGENCY_LOGIN){
							echo '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
							echo prepare_permanent_link('index.php?customer='.TRAVEL_AGENCY_LOGIN, _AGENCY_LOGIN, '', 'main_link');
						}
                    }
                }
				if(ModulesSettings::Get('accounts', 'hotel_owner_allow_registration') == 'yes'){
					echo '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
					echo prepare_permanent_link('index.php?admin='.ADMIN_LOGIN.'&type=hotel_owners', _HOTEL_OWNER_LOGIN, '', 'main_link');
				}
				if(SHOW_ADMIN_LOGIN){
					echo '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
					echo prepare_permanent_link('index.php?admin='.ADMIN_LOGIN, _ADMIN_LOGIN, '', 'main_link');
				}
            ?>
        <?php } ?>
        </form>
        <br>    
        <a href="#top" class="gotop scroll"><img src="<?= 'templates/'.Application::Get('template').'/images/'; ?>spacer.png" alt=""/></a>
    </div>
</div>
