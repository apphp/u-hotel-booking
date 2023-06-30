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

<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">
	<div class="sidebar-inner slimscrollleft">

		<?php if($objLogin->IsLoggedInAsAdmin()){ ?>

			<!-- User -->
			<div class="user-box">
				<div class="user-img">
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
					<img src="images/admins/<?= $profile_photo; ?>" alt="user-img" title="" class="img-circle img-thumbnail img-responsive">
					<div class="user-status online"><i class="zmdi zmdi-dot-circle"></i></div>
				</div>
				<h5><?php /*_YOU_ARE_LOGGED_AS.': '*/ echo $objLogin->GetLoggedName(); ?></h5>
				<ul class="list-inline">
					<li>
						<a href="index.php?admin=my_account" title="<?= _MY_ACCOUNT; ?>">
							<i class="zmdi zmdi-account zmdi-hc-fw"></i>
						</a>
					</li>
					<li>
						<form name="frmLogout" id="frmLogout" style="padding:0px;margin:0px;" action="<?= APPHP_BASE; ?>index.php" method="post">
							<?php draw_hidden_field('submit_logout', 'logout'); ?>
							<?php draw_token_field(); ?>
							<a href="javascript:appFormSubmit('frmLogout');" class="text-custom" title="<?= _BUTTON_LOGOUT; ?>">
								<i class="zmdi zmdi-power"></i>
							</a>
						</form>
					</li>
				</ul>
			</div>
			<!-- End User -->
	
			<!--- Sidemenu -->
			<div id="sidebar-menu">			
				<?php $objLogin->DrawLoginLinks(); ?>
				<div class="clearfix"></div>
			</div>
			<!-- Sidebar -->
			<div class="clearfix"></div>
			
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
		
	</div>
</div>
<!-- Left Sidebar End -->
