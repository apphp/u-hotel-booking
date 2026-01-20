<!DOCTYPE html>
<html <?= Application::Get('lang') != '' ? ' lang="'.Application::Get('lang').'"' : ''; ?>>
<head>
  	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _RESERVATION; ?></title>
    <script type="text/javascript" src="<?= WIDGET_HOST; ?>js/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="<?= WIDGET_HOME_URL; ?>template/js/main.js"></script>
	<link href="<?= WIDGET_HOME_URL; ?>template/images/apphp.ico" rel="shortcut icon" />
	<?php if(WIDGET_FILE_CSS){ ?>
		<link href="<?= WIDGET_FILE_CSS; ?>" type="text/css" rel="stylesheet" />
	<?php } ?>
    <link href="<?= WIDGET_HOME_URL; ?>template/css/w3.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= WIDGET_HOST; ?>modules/toastr/toastr.min.css" type="text/css" media="screen" />
    <!-- Picker UI-->	
    <script src="<?= WIDGET_HOST; ?>js/jquery/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="<?= WIDGET_HOST; ?>js/jquery/jquery-ui.css" />
    <script type="text/javascript" src="<?= WIDGET_HOST; ?>modules/toastr/toastr.min.js"></script>
    <script>
       var host = "<?= str_replace('"', "'", WIDGET_HOST); ?>";
       var key = "<?= str_replace('"', "'", WIDGET_KEY); ?>";
       var hids = "<?= str_replace('"', "'", WIDGET_HOTEL_IDS); ?>";
       var homeurl = "<?= str_replace('"', "'", WIDGET_HOME_URL); ?>";
	   var hash = '<?= WIDGET_HASH; ?>';
    </script>
    <base href="<?= WIDGET_HOST; ?>">
</head>
<?php if(WIDGET_LAYOUT_FULL){ ?>
<body class="homepage<?= Application::Get('lang_dir') == 'rtl' ? ' rtl' : ''; ?> orientation-<?= WIDGET_TYPE_FORM; ?> <?= WIDGET_LAYOUT; ?>" <?= Application::Get('lang_dir') == 'rtl' ? ' dir="rtl"' : ''; ?>>
    <div class="container">
        <div class="row">
            <div id="search-box">
				<div id="search-content">
				<?php
					if(WIDGET_ONE_HOTEL){
						$hotel_info = Hotels::GetHotelFullInfo(WIDGET_HOTEL_IDS);
						if(!empty($hotel_info)){
//							echo '<div class="header-tagline">
//                                <a href="'.WIDGET_HOME_URL.'?'.WIDGET_HASH.'">'.$hotel_info['name'].'</a>
//                                &nbsp;
//                                <img src="'.$homeurl.'template/images/bigrating-'.(int)$hotel_info['stars'].'.png" class="rating" alt="stars rating" width="60" />'.$hotel_info['address'].'</div>';
							$one_hotel_link = '<div class="header-tagline">
                                <a href="javascript:void(0);" onclick="openModal(\'h_'.$hotel_info['id'].'\');currentDiv(1, \'h_'.$hotel_info['id'].'\');"  title="'.htmlentities($hotel_info['name']).'">'.$hotel_info['name'].'</a>
                                &nbsp;
                                <img src="'.$homeurl.'template/images/bigrating-'.(int)$hotel_info['stars'].'.png" class="rating" alt="stars rating" width="60" />'.$hotel_info['address'].'</div>';
							echo $one_hotel_link;
						}
					}else{
						echo '<h3 class="header-tagline"><img src="'.WIDGET_HOME_URL.'template/images/logo.png" class="icon" alt="icon" /> <a href="'.$homeurl.'?'.WIDGET_HASH.'">'.$objSiteDescription->DrawHeader(false).'</a></h3>';
					}
				?>
					<div class="language-block" style="text-align:<?= Application::Get('lang_dir') == 'rtl' ? 'left' : 'right'; ?>;">
					<?php				
						$objLang  = new Languages();				
						if($objLang->GetLanguagesCount('front-end') > 1){
							echo '<div class="language-flags">';
							$objLang->DrawLanguagesBar(true, $basedir);
							echo '</div>';
						}					
					?>		
					<?= Currencies::GetCurrenciesDDL(true, false, array('select_class'=>'mySelectBoxClass')); ?>
					</div>
				</div>
            </div>
<?php
		if(WIDGET_TYPE_FORM == 'horizontal'){
?>
			<div id="horizontal-search-block">
<?php
			Rooms::DrawSearchAvailabilityBlock(false, '', WIDGET_HOTEL_IDS, 8, 3, 'room-inline', WIDGET_HOME_URL, '', true, true, true, '', false, 2, false);
			Rooms::DrawSearchAvailabilityFooter('', WIDGET_HOST);
?>
			</div>
<?php
		}
?>
        </div>
	</div>
    <div class="container" id="main-container">
        <div class="row">
<?php
		if(WIDGET_TYPE_FORM == 'vertical'){
?>
			<div id="vertical-search-block">
<?php
			Rooms::DrawSearchAvailabilityBlock(false, '', WIDGET_HOTEL_IDS, 8, 3, 'main-vertical', WIDGET_HOME_URL, '', true, true, true, '', false, 2, false);
			Rooms::DrawSearchAvailabilityFooter('', WIDGET_HOST);
?>
			</div>
<?php
		}
 ?>
            <div class="inner plain-inner cms-data">
			<?php
				if(Application::Get('page') != '' && Application::Get('page') != 'home'){
                    if(file_exists('page/'.Application::Get('page').'.php')){	 
						include_once('page/'.Application::Get('page').'.php');
					}else{
						include_once('page/404.php');
					}
				}elseif(WIDGET_ONE_HOTEL){
					include_once('page/check_availability.php');
                }else{
                    include_once('page/home.php');
                }
			?>
            </div>
            <?php $objReservation->DrawWidgetReservationCart(WIDGET_HOME_URL, true); ?>
        </div>
	</div>
</body>
<?php }else{ ?>
<body class="homepage not-full-layout orientation-<?= WIDGET_TYPE_FORM; ?> not-full-layout">
	<div class="container"<?= (WIDGET_TYPE_FORM == 'vertical' ? ' style="padding:0;"' : 'style="width:100%;max-width:100%;"');?>>
        <div class="row">
            <div id="search-box" style="padding:0;">
			</div>
<?php 
				echo '<div id="'.WIDGET_TYPE_FORM.'-search-block" '.(WIDGET_TYPE_FORM == 'vertical' ? 'style="position:static;margin-left:0;"' : 'style="margin-top:0"').'>';

				Rooms::DrawSearchAvailabilityBlock(false, '', WIDGET_HOTEL_IDS, 8, 3, WIDGET_TYPE_FORM == 'horizontal' ? 'room-inline' : 'main-vertical', WIDGET_HOME_URL, '_parent', true, true, true, '', false, 2, false);
				Rooms::DrawSearchAvailabilityFooter('', WIDGET_HOST);

				echo '</div>';
?>
        </div>
	</div>
</body>
<?php } ?>


</html>
