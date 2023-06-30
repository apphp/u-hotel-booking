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

<?php if($objLogin->IsLoggedInAsAdmin()){ ?>
<!-- Navigation Bar-->
<header id="topnav">
	<div class="topbar-main">
		<div class="container">

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
				</a>
			</div>
			<!-- End Logo container-->

			<div class="menu-extras">

				<ul class="nav navbar-nav navbar-right pull-right">
					<!--li>
						<form role="search" class="navbar-left app-search pull-left hidden-xs">
							 <input type="text" placeholder="Search..." class="form-control">
							 <a href=""><i class="fa fa-search"></i></a>
						</form>
					</li-->
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
					<li class="dropdown user-box">
						<a href="" class="dropdown-toggle waves-effect waves-light profile " data-toggle="dropdown" aria-expanded="true">
						<?php
							$profile_photo = $objLogin->GetLoggedPhoto();
							if(!empty($profile_photo)){
								if(!file_exists('images/admins/'.$profile_photo)){
									$profile_photo = 'no_image.png';
								}
							}else{
								$profile_photo = 'no_image.png';
							}
						?>			
							<img src="images/admins/<?= $profile_photo; ?>" alt="user-img" class="img-circle user-img">
							<div class="user-status away"><i class="zmdi zmdi-dot-circle"></i></div>
						</a>

						<ul class="dropdown-menu">
							<li><a href="index.php?admin=home"><i class="ti-home m-r-5"></i> <?= _HOME; ?></a></li>
							<li><a href="index.php?admin=my_account"><i class="ti-user m-r-5"></i> <?= _MY_ACCOUNT; ?></a></li>
							<li>
								<a href="javascript:appFormSubmit('frmLogout');" title="<?= _BUTTON_LOGOUT; ?>">
									<form name="frmLogout" id="frmLogout" style="padding:0px;margin:0px;" action="<?= APPHP_BASE; ?>index.php" method="post">
										<?php draw_hidden_field('submit_logout', 'logout'); ?>
										<?php draw_token_field(); ?>
											<i class="ti-power-off m-r-5"></i> <?= _BUTTON_LOGOUT; ?>
									</form>
								</a>
							</li>
						</ul>
					</li>
				</ul>
				<div class="menu-item">
					<!-- Mobile menu toggle-->
					<a class="navbar-toggle">
						<div class="lines">
							<span></span>
							<span></span>
							<span></span>
						</div>
					</a>
					<!-- End mobile menu toggle-->
				</div>
			</div>

		</div>
	</div>

	<div class="navbar-custom">
		<div class="container">
			<div id="navigation" class="navigation">
				
				<!-- Navigation Menu-->
				<?php $objLogin->DrawLoginLinks(true, true); ?>
				<!-- End navigation menu  -->
			</div>
		</div>
	</div>
</header>
<!-- End Navigation Bar-->	
<?php
	}else{
		echo '<div class="admin-login-box">';
		if(!Application::Get('preview')){
			if(Application::Get('admin') == ADMIN_LOGIN){
				echo '<br>'._LOGIN_PAGE_MSG;
			}elseif(Application::Get('admin') == 'password_forgotten'){
				echo '<br>'._PASSWORD_FORGOTTEN_PAGE_MSG;
			}						
		}			
		echo '</div>';		
	}
?>
