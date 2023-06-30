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

header('content-type: text/html; charset=utf-8');

$template_path = APPHP_BASE.'templates/'.Application::Get('template').'/';

$css_dir = $objSettings->GetParameter('type_menu') == 'horizontal' ? 'css-h' : 'css';

// Get Required Actions
$actions_msg = Application::GetSystemAlerts();

// Define classes for left menu according to saved status
$left_menu_width = isset($_COOKIE['leftMenuWidth']) ? $_COOKIE['leftMenuWidth'] : '';
$left_menu_status = isset($_COOKIE['leftMenuStatus']) ? $_COOKIE['leftMenuStatus'] : '';
$body_class = 'fixed-left';
$wrapper_class = ''; 

if(!empty($left_menu_width) && $objLogin->IsLoggedInAsAdmin()){
	if($left_menu_width == 'widescreen' && $left_menu_status == 'closed'){
		$body_class = 'widescreen fixed-left-void';
		$wrapper_class = 'enlarged forced';
	}
	
	if($left_menu_width == 'smallscreen'){
		if($left_menu_status == 'closed'){
			$body_class = 'smallscreen fixed-left-void';
			$wrapper_class =  'enlarged forced'; 
		}elseif($left_menu_status == 'minimized'){
			$body_class = 'smallscreen fixed-left-void';
			$wrapper_class =  'forced enlarged';			
		}else{
			$body_class = 'smallscreen fixed-left';
			$wrapper_class =  'forced';
		}
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="keywords" content="<?= $objSiteDescription->GetParameter('tag_keywords'); ?>" />
	<meta name="description" content="<?= $objSiteDescription->GetParameter('tag_description'); ?>" />
	<meta name="author" content="ApPHP Company">
	<meta name="generator" content="uHotelBooking v<?= CURRENT_VERSION; ?>">

	<base href="<?= APPHP_BASE; ?>" /> 
	<link href="<?= APPHP_BASE; ?>images/apphp.ico" rel="SHORTCUT ICON" />

	<title><?= $objSiteDescription->GetParameter('tag_title'); ?> :: <?= _ADMIN_PANEL; ?></title>

	<!-- App css -->
	<link href="<?= $template_path; ?><?= $css_dir; ?>/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= $template_path; ?><?= $css_dir; ?>/core.css" rel="stylesheet" type="text/css" />
	<link href="<?= $template_path; ?><?= $css_dir; ?>/components.css" rel="stylesheet" type="text/css" />
	<link href="<?= $template_path; ?><?= $css_dir; ?>/icons.css" rel="stylesheet" type="text/css" />

	<?php if(Application::Get('template') == 'admin' && !$objLogin->IsLoggedInAsAdmin()){ ?>
		<link href="<?= $template_path; ?><?= $css_dir; ?>/pages.css" rel="stylesheet" type="text/css" />
	<?php }else{ ?>
		<link href="<?= $template_path; ?><?= $css_dir; ?>/print.css" type="text/css" rel="stylesheet" media="print">
	<?php } ?>
	<link href="<?= APPHP_BASE; ?>js/jquery/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="<?= APPHP_BASE; ?>js/chosen/chosen.min.css" type="text/css" rel="stylesheet" />

	<link href="<?= $template_path; ?><?= $css_dir; ?>/menu.css" rel="stylesheet" type="text/css" />
	<link href="<?= $template_path; ?><?= $css_dir; ?>/responsive.css" rel="stylesheet" type="text/css" />

    <link href="<?= APPHP_BASE; ?>modules/datatables/css/jquery.dataTables.min.css" type="text/css" rel="stylesheet" />
	
	<?php if(Application::Get('lang_dir') == 'rtl'){ ?>
		<link href="<?= $template_path; ?><?= $css_dir; ?>/custom.rtl.css" type="text/css" rel="stylesheet" />
	<?php } else { ?>
		<link href="<?= $template_path; ?><?= $css_dir; ?>/custom.css" type="text/css" rel="stylesheet" />
	<?php } ?>
	
	
	<!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->

	<script src="<?= $template_path; ?>js/modernizr.min.js"></script>
	
	<script src="<?= $template_path; ?>js/jquery.min.js"></script>

	<?php if(Application::Get('template') == 'admin' && !$objLogin->IsLoggedInAsAdmin()){ ?>
		<!-- admin scripts -->
		<script type="text/javascript" src="<?= APPHP_BASE; ?>js/main.min.js"></script>
	<?php }else{ ?>		
		<script src="<?= $template_path; ?>js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?= APPHP_BASE; ?>js/main.min.js"></script>
		<script type="text/javascript" src="<?= APPHP_BASE; ?>js/cart.js"></script>
		<script type="text/javascript" src="<?= $template_path; ?>js/menu.js"></script>
	<?php } ?>
    <script type="text/javascript" src="<?= APPHP_BASE; ?>js/jquery/jquery-ui.min.js"></script>
    <?php
		// Swipe support
		if(in_array(Application::Get('page'), array('check_cars_availability', 'check_availability'))){
			echo '<!-- Swipe plugin -->'."\n";
			echo '<script type="text/javascript" src="'.APPHP_BASE.'templates/'.Application::Get('template').'/js/jquery.detect_swipe.js"></script>'."\n";
			echo '<script type="text/javascript" src="'.APPHP_BASE.'templates/'.Application::Get('template').'/js/main.js"></script>'."\n";
		}
	?>
	
	<?php
		// Set lytebox and video labraries
		if(Application::Get('page') == 'gallery' || (Application::Get('page') == 'pages' && Application::Get('type') == 'system')){
			echo Application::SetLibraries();		
		}	
	?>
</head>
<body class="<?= $body_class; ?>"  dir="<?= Application::Get('lang_dir');?>">

	<?php if(Application::Get('template') == 'admin' && !$objLogin->IsLoggedInAsAdmin()){ ?>
	
        <div class="account-pages"></div>
        <div class="clearfix"></div>
        <div class="wrapper-page">
            <div class="text-center">
                <a href="index.php" class="logo">
				<?php
					if(preg_match('/booking/i', $objSiteDescription->GetParameter('header_text'))){
						echo preg_replace('/booking/i', '<span>Booking</span>', '<span>'.$objSiteDescription->GetParameter('header_text').'</span>');	
					}else{
						echo $objSiteDescription->GetParameter('header_text');
					}
				?>
				</a>				
                <h5 class="text-muted m-t-0 font-600">
					<?= ($objLogin->IsLoggedInAsAdmin()) ? _ADMIN_PANEL : $objSiteDescription->GetParameter('slogan_text') ?>
				</h5>
            </div>
        	<div class="m-t-40 card-box">
                <div class="text-center">
                    <h4 class="text-uppercase font-bold m-b-0">
					<?php
						if(Application::Get('admin') == 'password_forgotten'){
							echo _PASSWORD_FORGOTTEN;
						}elseif(Application::Get('admin') == 'login' && Application::Get('type') == 'hotel_owners'){
                            echo _HOTEL_OWNER_LOGIN;
						}elseif(Application::Get('admin') == 'login' && Application::Get('type') == 'hotel_managers'){
                            echo _HOTEL_MANAGER_LOGIN;
						}elseif(Application::Get('admin') == 'hotel_owners_create_account'){
                            echo _CREATE_HOTEL_OWNER_ACCOUNT;
						}elseif(Application::Get('admin') == 'hotel_owners_confirm_registration'){
                            echo _REGISTRATION_CONFIRMATION;
						}else{
							echo _ADMIN_LOGIN;
						}
					?>
					</h4>
                </div>
                <div class="panel-body">					
					<!-- MAIN CONTENT -->
					<?php					
						if((Application::Get('admin') != '') && !preg_match('/mod_/', Application::Get('admin')) && file_exists('admin/'.Application::Get('admin').'.php')){
							include_once('admin/'.Application::Get('admin').'.php');	
						}elseif((Application::Get('admin') != '') && preg_match('/mod_/', Application::Get('admin')) && file_exists('admin/modules/'.Application::Get('admin').'.php')){
							include_once('admin/modules/'.Application::Get('admin').'.php');	
						}elseif(Application::Get('admin') == ADMIN_LOGIN){
							include_once('admin/login.php');	
						}					
					?>
                </div>
            </div>
            <!-- end card-box-->

			<?php if(SHOW_COPYRIGHT){ ?>
				<!--<h1 class="login"><a href="http://apphp.com" title="Powered by ApPHP">ApPHP</a></h1>-->
				<div class="row">
					<div class="col-sm-12 text-center">
						<?= date('Y').' &copy; '.$objSiteDescription->DrawFooter(false); ?>
					</div>
				</div>
			<?php } ?>
            
        </div>
        <!-- end wrapper page -->
		
		
	<?php }elseif($objSettings->GetParameter('type_menu') == 'horizontal') { ?>
	
		<?php include_once 'templates/'.Application::Get('template').'/top_menu.php'; ?>
		<div id="wrapper" class="wrapper <?= $wrapper_class; ?>">
			<!-- Start content -->
			<div class="content">
				<div class="container">						
					<div class="row">
						<div class="col-sm-12">
							<div class="card-box">
								
							<!-- MAIN CONTENT -->
							<?php					
								if((Application::Get('page') != '') && file_exists('page/'.Application::Get('page').'.php')){
									include_once('page/'.Application::Get('page').'.php');
								}elseif((Application::Get('customer') != '') && file_exists('customer/'.Application::Get('customer').'.php')){
									include_once('customer/'.Application::Get('customer').'.php');
								}elseif((Application::Get('admin') != '') && !preg_match('/mod_/', Application::Get('admin')) && file_exists('admin/'.Application::Get('admin').'.php')){
									include_once('admin/'.Application::Get('admin').'.php');	
								}elseif((Application::Get('admin') != '') && preg_match('/mod_/', Application::Get('admin')) && file_exists('admin/modules/'.Application::Get('admin').'.php')){
									include_once('admin/modules/'.Application::Get('admin').'.php');	
								}elseif(Application::Get('admin') == ADMIN_LOGIN){
									include_once('admin/login.php');
								}else{
									if(Application::Get('template') == 'admin'){
										include_once('admin/home.php');
									}else{										
										include_once('page/pages.php');								
									}
								}
							?>

							</div>
						</div> 
					</div> <!-- row -->
				</div> <!-- container -->

			</div>

				<!-- ============================================================== -->
				<!-- End Right content here -->
				<!-- ============================================================== -->

			<?php include_once 'templates/'.Application::Get('template').'/right_sidebar.php'; ?>
	
			<?php if(SHOW_COPYRIGHT){ ?>
			<footer class="footer text-right">
				<div class="footer-inner">
					<?= date('Y').' &copy; '.$objSiteDescription->DrawFooter(false); ?>
				</div>
			</footer>
			<?php } ?>
		</div>

	<?php }else{ ?>
	
		<!-- Begin page -->
		<div id="wrapper" class="<?= $wrapper_class; ?>">
	
			<?php include_once 'templates/'.Application::Get('template').'/top_bar.php'; ?>			
	
			<?php include_once 'templates/'.Application::Get('template').'/left_menu.php'; ?>
	
			<!-- ============================================================== -->
			<!-- Start right Content here -->
			<!-- ============================================================== -->
			<div class="content-page">
				<!-- Start content -->
				<div class="content">
					<div class="container">						
						<!--<div class="row">-->
						<!--<div class="col-12">-->
						<div class="card-box">
							
						<!-- MAIN CONTENT -->
						<?php
							if((Application::Get('page') != '') && file_exists('page/'.Application::Get('page').'.php')){
								include_once('page/'.Application::Get('page').'.php');
							}elseif((Application::Get('customer') != '') && file_exists('customer/'.Application::Get('customer').'.php')){
								include_once('customer/'.Application::Get('customer').'.php');
							}elseif((Application::Get('admin') != '') && !preg_match('/mod_/', Application::Get('admin')) && file_exists('admin/'.Application::Get('admin').'.php')){
								include_once('admin/'.Application::Get('admin').'.php');	
							}elseif((Application::Get('admin') != '') && preg_match('/mod_/', Application::Get('admin')) && file_exists('admin/modules/'.Application::Get('admin').'.php')){
								include_once('admin/modules/'.Application::Get('admin').'.php');	
							}elseif(Application::Get('admin') == ADMIN_LOGIN){
								include_once('admin/login.php');
							}else{
								if(Application::Get('template') == 'admin'){
									include_once('admin/home.php');
								}else{										
									include_once('page/pages.php');								
								}
							}
						?>
	
						<!--</div>-->
						<!--</div> -->
						</div> <!-- row -->
					</div> <!-- container -->
				</div> <!-- content -->
	
				<?php if(SHOW_COPYRIGHT){ ?>
				<footer class="footer text-right">
					<?= date('Y').' &copy; '.$objSiteDescription->DrawFooter(false); ?>
				</footer>
				<?php } ?>
			</div>
	
			<!-- ============================================================== -->
			<!-- End Right content here -->
			<!-- ============================================================== -->
	
			<?php include_once 'templates/'.Application::Get('template').'/right_sidebar.php'; ?>
	
		</div>
		<!-- END wrapper -->
		
	<?php } ?>

	<?php include_once 'templates/'.Application::Get('template').'/footer.php'; ?>			
    <?php Rooms::DrawSearchAvailabilityFooter(); ?>
	
</body>
</html>