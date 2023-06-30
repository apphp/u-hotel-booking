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

?>
<!-- Top Bar Start -->
<div class="topbar">

	<!-- LOGO -->
	<div class="topbar-left">
		<a href="index.php" class="logo">
			<?php
				if(preg_match('/booking/i', $objSiteDescription->GetParameter('header_text'))){
					echo preg_replace('/booking/i', '<span>Booking</span>', '<span>'.$objSiteDescription->GetParameter('header_text').'</span>');	
				}else{
					echo $objSiteDescription->GetParameter('header_text');
				}
			?>
			<i class="zmdi zmdi-layers"></i>
		</a>
	</div>

	<!-- Button mobile view to collapse sidebar menu -->
	<div class="navbar navbar-default" role="navigation">
		<div class="container">

			<?php if($objLogin->IsLoggedInAsAdmin()){ ?>
	
				<!-- Page title -->
				<ul class="nav navbar-nav navbar-left">
					<li>
						<button class="button-menu open-left"><i class="zmdi zmdi-menu"></i></button>
					</li>
					<li>
						<h4 class="page-title">
							<?= ($objLogin->IsLoggedInAsAdmin()) ? _ADMIN_PANEL : $objSiteDescription->GetParameter('slogan_text') ?>
						</h4>
					</li>
				</ul>
	
				<!-- Right(Notification and Searchbox -->
				<ul class="nav navbar-nav navbar-right">
					<?php if(count($actions_msg) > 0){ ?>
					<li>
						<!-- Notification -->
						<div class="notification-box">
							<ul class="list-inline m-b-0">
								<li>
									<a href="javascript:void(0);" class="right-bar-toggle">
										<i class="zmdi zmdi-notifications-none"></i>
									</a>
									<div class="noti-dot">
										<span class="dot"></span>
										<span class="pulse"></span>
									</div>
								</li>
							</ul>
						</div>
						<!-- End Notification bar -->
					</li>
					<?php } ?>
					<li class="hidden-xs">
						<?php if($objLogin->IsLoggedIn()){ ?>
							<form name="frmLogout" id="frmTopLogout" action="<?= APPHP_BASE; ?>index.php" method="post">
								<?php draw_hidden_field('submit_logout', 'logout'); ?>
								<?php draw_token_field(); ?>
								<?php
									echo '&nbsp;&nbsp;&nbsp;';
									echo prepare_permanent_link('index.php?admin=home', _HOME, '', 'main_link');
									echo '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;&nbsp;';
									echo prepare_permanent_link('index.php?admin=my_account', _MY_ACCOUNT, '', 'main_link');
									echo '&nbsp;&nbsp;'.draw_divider(false).'&nbsp;';
								?>				
								<a class="main_link" href="javascript:appFormSubmit('frmLogout');"><?= _BUTTON_LOGOUT; ?></a>				
							</form>
						<?php } ?>
					</li>
				</ul>
	
			<?php }else{ ?>
			
				<!-- Page title -->
				<ul class="nav navbar-nav navbar-left">
					<li>
						<h4 class="page-title">&nbsp;<?= $objSiteDescription->GetParameter('slogan_text'); ?></h4>
					</li>
				</ul>
				
			<?php } ?>
			
			
		</div><!-- end container -->
	</div><!-- end navbar -->
	
</div>
<!-- Top Bar End -->

