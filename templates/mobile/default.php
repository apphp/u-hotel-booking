<?php
// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

// Cache settings
header('Cache-Control: must-revalidate');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 259200) . ' GMT');

// Characters settings
header('content-type: text/html; charset=utf-8');
//--------------------------------------------------------------------------
?>
<?php if(Application::Get('render') !== 'partial'){ ?>
<!DOCTYPE html>
<html<?= Application::Get('lang') != '' ? ' lang="'.Application::Get('lang').'"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!--<meta name="theme-color" content="#FF4000">-->
    <meta http-equiv="Content-Security-Policy" content="default-src * 'self' 'unsafe-inline' 'unsafe-eval' data: gap:">

    <meta name="keywords" content="<?= Application::Get('tag_keywords'); ?>" />
    <meta name="description" content="<?= Application::Get('tag_description'); ?>" />
    <meta name="author" content="ApPHP Company - Advanced Power of PHP">
    <meta name="generator" content="uHotelBooking v<?= CURRENT_VERSION; ?>">
    <title><?= Application::Get('tag_title'); ?></title>

    <base href="<?= APPHP_BASE; ?>" />
    <link href="<?= APPHP_BASE; ?>templates/mobile/img/favicon.png" rel="apple-touch-icon" >
    <link href="<?= APPHP_BASE; ?>templates/mobile/img/favicon.ico" rel="SHORTCUT ICON" />
    <!-- <link rel="icon" href="img/f7-icon.png">-->

    <link href="<?= APPHP_BASE; ?>templates/mobile/framework/css/framework7.min.css" rel="stylesheet" media="screen">
    <link href="<?= APPHP_BASE; ?>templates/mobile/css/app.css" rel="stylesheet" media="screen">
</head>
<body class="color-theme-orange">
<div id="app">
    <div class="statusbar"></div>

	<?php include_once('left_panel.php'); ?>

	<div class="view view-main view-init ios-edges" data-url="/">
	<? } ?>

		<?php
			// PARTIAL CONTENT
			if((Application::Get('mobile') != '') && file_exists('mobile/'.Application::Get('mobile').'.php')){
				include_once('mobile/'.Application::Get('mobile').'.php');
			}else{
				include_once('mobile/home.php');
			}
		?>

	<?php if(Application::Get('render') !== 'partial'){ ?>
	</div>
</div>
<script src="<?= APPHP_BASE; ?>templates/mobile/framework/js/framework7.min.js"></script>
<script src="<?= APPHP_BASE; ?>templates/mobile/js/routes.js"></script>
<script src="<?= APPHP_BASE; ?>templates/mobile/js/app.js"></script>
</body>
</html>
<?php } ?>