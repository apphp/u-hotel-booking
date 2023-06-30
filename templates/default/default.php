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
	
// Cache settings
header('Cache-Control: must-revalidate');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 259200) . ' GMT');

// Characters settings
header('content-type: text/html; charset=utf-8');

$page_types = explode('_', Application::Get('page'));
$page_type = isset($page_types[1]) ? $page_types[1] : '';

$affiliate_id = Application::Get('affiliate_id');
if(!empty($affiliate_id) && Modules::IsModuleInstalled('affiliates')){
	$days = ModulesSettings::Get('affiliates', 'expiration_date');
	if($days > 0){
		setcookie('affiliate_id', $affiliate_id, time() + (3600 * 24 * $days));
	}
}

?>
<!DOCTYPE html>
<html<?= Application::Get('lang') != '' ? ' lang="'.Application::Get('lang').'"' : ''; ?>>
  <head>
  	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="keywords" content="<?= Application::Get('tag_keywords'); ?>" />
	<meta name="description" content="<?= Application::Get('tag_description'); ?>" />
    <meta name="author" content="ApPHP Company - Advanced Power of PHP">
    <meta name="generator" content="uHotelBooking v<?= CURRENT_VERSION; ?>">        
	<title><?= Application::Get('tag_title'); ?></title>
    
	<base href="<?= APPHP_BASE; ?>" />
	<link href="<?= APPHP_BASE; ?>images/icons/apphp.ico" rel="SHORTCUT ICON" />
    <?php
        include('templates/'.Application::Get('template').'/javascript.top.php');
		if(in_array(Application::Get('page'), array('hotels', 'home', 'rooms', 'check_cars_availability', 'check_availability'))){
			if(Application::Get('page') == 'hotels' || Application::Get('page') == 'rooms'){
		?>
                <!-- jQuery-->
                <script src="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/js/jquery.v2.0.3.js"></script>
		<?php
                if(GALLERY_TYPE == 'carousel'){
                    echo '<link rel="stylesheet" href="'.APPHP_BASE.'modules/bxslider/jquery.bxslider.css" type="text/css" media="screen" />'."\n";
                }
            }else{
                include('templates/'.Application::Get('template').'/javascript.details.top.php');
                if(Application::Get('page') == 'check_availability'){
                    echo '<link rel="stylesheet" href="'.APPHP_BASE.'modules/toastr/toastr.min.css" type="text/css" media="screen" />'."\n";
                    echo '<script type="text/javascript" src="'.APPHP_BASE.'modules/toastr/toastr.min.js"></script>'."\n";
					echo '<!-- LyteBox v3.22 Author: Markus F. Hay Website: http://www.dolem.com/lytebox -->'."\n";
					echo '<link rel="stylesheet" href="'.APPHP_BASE.'modules/lytebox/css/lytebox.css" type="text/css" media="screen" />'."\n";
					echo '<script type="text/javascript" src="'.APPHP_BASE.'modules/lytebox/js/lytebox.js"></script>'."\n";
                }
            }
		}elseif(!in_array(Application::Get('page'), array('home')) && !in_sub_array('property_code', $page_type, Application::Get('property_types'))){
            include('templates/'.Application::Get('template').'/nohome-javascript.top.php');            
            echo '<!-- LyteBox v3.22 Author: Markus F. Hay Website: http://www.dolem.com/lytebox -->'."\n";
            echo '<link rel="stylesheet" href="'.APPHP_BASE.'modules/lytebox/css/lytebox.css" type="text/css" media="screen" />'."\n";
            echo '<script type="text/javascript" src="'.APPHP_BASE.'modules/lytebox/js/lytebox.js"></script>'."\n";
		}
		
		// Swipe support
		if(in_array(Application::Get('page'), array('home', 'rooms', 'check_status', 'check_cars_availability', 'check_availability'))){
			echo '<!-- Swipe plugin -->'."\n";
			echo '<script type="text/javascript" src="'.APPHP_BASE.'templates/'.Application::Get('template').'/assets/js/jquery.detect_swipe.js"></script>'."\n";
		}
    ?>
    <?php if(Application::Get('lang_dir') == 'rtl'){ ?>
        <link href="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/css/custom.rtl.css" rel="stylesheet" media="screen">
    <?php }else{ ?>
        <link href="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/assets/css/custom.css" rel="stylesheet" media="screen">
    <?php } ?>
    <link href="<?= APPHP_BASE; ?>templates/<?= Application::Get('template');?>/css/print.css" rel="stylesheet" media="print">

	<!--script type="text/javascript" src="<?= APPHP_BASE; ?>js/main.min.js"></script-->
	<script type="text/javascript" src="<?= APPHP_BASE; ?>js/main.js"></script>
	<script type="text/javascript" src="<?= APPHP_BASE; ?>js/cart.js"></script>	
</head>
<body id="top" class="thebg">
    <?php include('templates/'.Application::Get('template').'/header.php');?>    
    <?php
        if(Application::Get('page') == 'home' && Application::Get('customer') == '' && Application::Get('hotel_owner') == ''){
            include('templates/'.Application::Get('template').'/home-banner.php');
            include('templates/'.Application::Get('template').'/home-content.php');
            include('templates/'.Application::Get('template').'/home-javascript.bottom.php');
            Rooms::DrawSearchAvailabilityFooter('.home');
        }elseif(preg_match('/check\_/i', Application::Get('page')) && in_sub_array('property_code', $page_type, Application::Get('property_types')) && Application::Get('customer') == ''){
			include('templates/'.Application::Get('template').'/check_properties.content.php');
            include('templates/'.Application::Get('template').'/home-javascript.bottom.php');
            Rooms::DrawSearchAvailabilityFooter();
		}elseif(
			(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') != 'no') &&
			((Application::Get('page') == 'check_cars_availability' && Application::Get('customer') == '')
			|| (Application::Get('page') == 'book_now_car')
			|| (Application::Get('page') == 'booking_car_details')
			|| (Application::Get('page') == 'booking_car_checkout')
			|| (Application::Get('page') == 'booking_car_payment')
			|| (Application::Get('page') == 'cars_payment')
			|| (Application::Get('page') == 'cars_return'))
		){
			include('templates/'.Application::Get('template').'/breadcrubs.php');
            include('templates/'.Application::Get('template').'/content_cars.php');
            include('templates/'.Application::Get('template').'/footer.php');
            include('templates/'.Application::Get('template').'/javascript.bottom.php');
            ///Rooms::DrawSearchAvailabilityFooter();
        }elseif((Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') != 'no') && Application::Get('page') == 'cars'){
            include('templates/'.Application::Get('template').'/breadcrubs.php');
            include('templates/'.Application::Get('template').'/car_description.content.php');
            include('templates/'.Application::Get('template').'/footer.php');
            include('templates/'.Application::Get('template').'/javascript.details.bottom.php');
            Rooms::DrawSearchAvailabilityFooter();		
        }elseif(Application::Get('page') == 'hotels'){
            include('templates/'.Application::Get('template').'/breadcrubs.php');
            include('templates/'.Application::Get('template').'/hotel_description.content.php');
            include('templates/'.Application::Get('template').'/footer.php');
            include('templates/'.Application::Get('template').'/javascript.details.bottom.php');
            Rooms::DrawSearchAvailabilityFooter('.details');
        }elseif(Application::Get('page') == 'rooms'){
            include('templates/'.Application::Get('template').'/breadcrubs.php');
            include('templates/'.Application::Get('template').'/room_description.content.php');
            include('templates/'.Application::Get('template').'/footer.php');
            include('templates/'.Application::Get('template').'/javascript.details.bottom.php');
            Rooms::DrawSearchAvailabilityFooter();
        }elseif(Application::Get('page') == 'conferences'
            && Modules::IsModuleInstalled('conferences') && ModulesSettings::Get('conferences', 'is_active') != 'no') {
            include('templates/'.Application::Get('template').'/breadcrubs.php');
            include('templates/'.Application::Get('template').'/conferences.content.php');
            include('templates/'.Application::Get('template').'/footer.php');
            include('templates/'.Application::Get('template').'/javascript.conferences.bottom.php');
            Rooms::DrawSearchAvailabilityFooter();
        //}elseif(Application::Get('page') == 'activities'){
        //  include('templates/'.Application::Get('template').'/breadcrubs.php');
        //  include('templates/'.Application::Get('template').'/activities.content.php');
        //  include('templates/'.Application::Get('template').'/footer.php');
        //  include('templates/'.Application::Get('template').'/javascript.conferences.bottom.php');
        //  Rooms::DrawSearchAvailabilityFooter();
        }else{
            include('templates/'.Application::Get('template').'/breadcrubs.php');
            include('templates/'.Application::Get('template').'/content.php');
            include('templates/'.Application::Get('template').'/footer.php');
            include('templates/'.Application::Get('template').'/javascript.bottom.php');
            Rooms::DrawSearchAvailabilityFooter();
        }
    ?>
</body>
</html>