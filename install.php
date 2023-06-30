<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

	require_once('install/settings.inc.php');    
	
	if(file_exists(EI_CONFIG_FILE_DIRECTORY.EI_CONFIG_FILE_NAME)){        
		header('location: '.EI_APPLICATION_START_FILE);
		exit;
	}
	
    ob_start();
    
	if(function_exists('phpinfo')) @phpinfo(-1);
	$phpinfo = array('phpinfo' => array());
	if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
	foreach($matches as $match){
		$array_keys = array_keys($phpinfo);
		$end_array_keys = strtolower(end($array_keys));
		if(strlen($match[1])){
			$phpinfo[strtolower($match[1])] = array();
		}elseif(isset($match[3])){
			$phpinfo[$end_array_keys][strtolower($match[2])] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
		}else{
			$phpinfo[$end_array_keys][] = $match[2];
		}
	}

    $is_error = false;
    $error_mg = array();
    if(EI_CHECK_PHP_MINIMAL_VERSION && (EI_PHP_MINIMAL_VERSION > phpversion())){
        $is_error = true;
        $error_mg[] = 'This program requires at least PHP version '.EI_PHP_MINIMAL_VERSION.' installed. You cannot proceed the installation.';	
    }
    if(EI_CHECK_CONFIG_DIR_WRITABILITY && !is_writable(EI_CONFIG_FILE_DIRECTORY)){
        $is_error = true;
        $error_mg[] = 'The directory <b>'.EI_CONFIG_FILE_DIRECTORY.'</b> is not writable! <br />You must grant access rights 0755 or 777 (depending on your system settings) to this directory before you start the installation!<br />';
    }	
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>uHotelBooking :: Installation Wizard</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="install/img/styles.css"></link>
</head>
<body text="#000000" vlink="#2971c1" alink="#2971c1" link="#2971c1" bgcolor="#ffffff">
    
<table align="center" width="70%" cellspacing="0" cellpadding="2" border="0">
<tbody>
<tr><td>&nbsp;</td></tr>
<tr>
    <td class="text" valign="top">
        <h2>New Installation of <?= EI_APPLICATION_NAME;?> v<?= EI_APPLICATION_VERSION; ?>!</h2>		
        
        Follow the wizard to setup your database.<br /><br />
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
        <tbody>
        <tr>
            <td class="gray_table">
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                <tr><td class="ltcorner"></td><td></td><td class="rtcorner"></td></tr>
                <tr>
                    <td></td>
                    <td align=middle>
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                        <tr>
                            <td class="text" align="left">
								<b>Getting System Info</b>
                            </td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr>
                            <td class="text" align="left">
								<?php
									$php_core_index = ((version_compare(phpversion(), '5.3.0', '<'))) ? 'PHP Core' : 'Core';
								
									$system = isset($phpinfo['phpinfo']['system']) ? $phpinfo['phpinfo']['system'] : 'unknown';
									$database_system = isset($phpinfo['mysqli']['mysqli support']) ? $phpinfo['mysqli']['mysqli support'] : 'unknown';
									$database_system_version = isset($phpinfo['mysqli']) && isset($phpinfo['mysqli']['client api version']) ? $phpinfo['mysqli']['client api version'] : (isset($phpinfo['mysqli']['client api library version']) ? $phpinfo['mysqli']['client api library version'] : 'unknown');
									$build_date = isset($phpinfo['phpinfo']['build date']) ? $phpinfo['phpinfo']['build date'] : 'unknown';
									$server_api = isset($phpinfo['phpinfo']['server api']) ? $phpinfo['phpinfo']['server api'] : 'unknown';
									$vd_support = isset($phpinfo['phpinfo']['virtual directory support']) ? $phpinfo['phpinfo']['virtual directory support'] : 'unknown';

									$asp_tags 	= isset($phpinfo[$php_core_index]['asp_tags']) ? $phpinfo[$php_core_index]['asp_tags'][0] : 'unknown';
									$safe_mode 	= isset($phpinfo[$php_core_index]['safe_mode']) ? $phpinfo[$php_core_index]['safe_mode'][0] : 'unknown';
									$mode_rewrite = in_array('rewrite', get_loaded_extensions()) ? 'On' : 'Off';
									$short_open_tag = isset($phpinfo[$php_core_index]) ? $phpinfo[$php_core_index]['short_open_tag'][0] : 'unknown';
									$session_support = isset($phpinfo['session']['session support']) ? $phpinfo['session']['session support'] : 'unknown';
									$mbstring_support = (function_exists('mb_detect_encoding')) ? 'enabled' : 'disabled';
									$magic_quotes_gpc = ini_get('magic_quotes_gpc') ? 'On' : 'Off';
									$magic_quotes_runtime = ini_get('magic_quotes_runtime') ? 'On' : 'Off';
									$magic_quotes_sybase = ini_get('magic_quotes_sybase') ? 'On' : 'Off';
								?>
                                <ul>
                                    <li>PHP Version: <b><i><?= phpversion(); ?></i></b></li>
									<li>Database System: <b><i>MySQL - <?= $database_system.' ('.$database_system_version.')'; ?></i></b></li>
									<li>System: <b><i><?= $system; ?></i></b></li>							
								</ul>	
                                <ul>
									<li>Build Date: <b><i><?= $build_date; ?></i></b></li>
                                    <li>Server API: <b><i><?= $server_api; ?></i></b></li>
									<li>Virtual Directory Support: <b><i><?= $vd_support; ?></i></b></li>
									<li>Safe Mode: <b><i><?= $safe_mode; ?></i></b></li>
									<li>Mode Rewrite: <b><i><?= $mode_rewrite; ?></i></b></li>
								</ul>	
								<ul>
                                    <li>Asp Tags: <b><i><?= $asp_tags; ?></i></b></li>
									<li>Short Open Tag: <b><i><?= $short_open_tag; ?></i></b></li>
									<li>Session Support: <b><i><?= $session_support; ?></i></b></li>
									<li>mbString Support: <b><i><?= $mbstring_support; ?></i></b></li>
									<li>Magic Quotes GPC: <b><i><?= $magic_quotes_gpc; ?></i></b></li>
									<li>Magic Quotes RunTime: <b><i><?= $magic_quotes_runtime; ?></i></b></li>
									<li>Magic Quotes SyBase: <b><i><?= $magic_quotes_sybase; ?></i></b></li>
								</ul>
							</td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
						<?php if(!$is_error){ ?>
							<tr>
								<td class="text" align="left">
									Click on Start button to continue.
								</td>
							</tr>
						<?php }else{ ?>
							<?php
								if($is_error){
									foreach($error_mg as $msg){
										echo '<tr><td class="text" align="left"><span style="color:#bb5500;">&#8226; '.$msg.'</span></td></tr>';
									}								
								}
							?>						
						<?php } ?>
						</tbody>
                        </table>
						<br />						
						<?php if(!$is_error){ ?>
							<table width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text">
							<tr>
								<td colspan="2" align="left">
									<a href="install/step1.php"><img class="form_button" src="install/img/button_start.gif" name="submit" title="Click to start installation" alt="" /></a>
								</td>
							</table>						
						<?php } ?>
					</td>
                    <td></td>
                </tr>
				<tr><td class="lbcorner"></td><td></td><td class="rbcorner"></td></tr>
                </tbody>
                </table>

            </td>
        </tr>
        </tbody>
        </table>

        <?php include_once('install/footer.php'); ?>        
    </td>
</tr>
</tbody>
</table>
                  
</body>
</html>
