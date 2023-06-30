<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

    require_once('settings.inc.php');    
    
    if (file_exists(EI_CONFIG_FILE_PATH)) {        
		header('location: '.EI_APPLICATION_START_FILE);
        exit;
	}

	$database_host		= isset($_REQUEST['database_host']) ? $_REQUEST['database_host'] : '';
	$database_port		= isset($_REQUEST['database_port']) ? $_REQUEST['database_port'] : '3306';
	$database_name 		= isset($_REQUEST['database_name']) ? $_REQUEST['database_name'] : '';
	$database_username	= isset($_REQUEST['database_username']) ? $_REQUEST['database_username'] : '';
	$database_password	= isset($_REQUEST['database_password']) ? $_REQUEST['database_password'] : '';
	$database_prefix	= isset($_REQUEST['database_prefix']) ? $_REQUEST['database_prefix'] : 'uhb_';	
	$install_type		= isset($_REQUEST['install_type']) ? $_REQUEST['install_type'] : 'new';
	$password_encryption= isset($_REQUEST['password_encryption']) ? $_REQUEST['password_encryption'] : EI_PASSWORD_ENCRYPTION_TYPE;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>uHotelBooking :: Installation Wizard</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="img/styles.css"></link>
	<script type="text/javascript">
		function install_type_OnChange(val){
			if(val !== 'new'){
				document.getElementById('tblAdminAccess').style.display = 'none';
				document.getElementById('tblBackupMsg').style.display = '';
			}else{
				document.getElementById('tblAdminAccess').style.display = '';
				document.getElementById('tblBackupMsg').style.display = 'none';
			}			
		}
		function togglePassword(el) {
			var txt = document.getElementById(el),
				lbl = document.getElementById('lbl_'+el);
			txt.type = (txt.type === "password") ? "text" : "password";
			lbl.innerHTML = (txt.type === "password") ? "Show" : "Hide";
		}
	</script>
</head>
<body text="#000000" vlink="#2971c1" alink="#2971c1" link="#2971c1" bgcolor="#ffffff">
    
<table align="center" width="70%" cellspacing="0" cellpadding="2" border="0">
<tbody>
<tr><td>&nbsp;</td></tr>
<tr>
    <td class=text valign=top>
        <h2>New Installation of <?= EI_APPLICATION_NAME;?>!</h2>
        
        Follow the wizard to setup your database.<br />
		<span style="color:#a60000">*</span> Items marked with an asterisk are required.<br /><br />
		
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
        <tbody>
        <tr>
            <td class="gray_table">
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                <tr><td class="ltcorner"></td><td></td><td class="rtcorner"></td></tr>
                <tr>
                    <td></td>
                    <td align="middle">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                        <tr><td class="text" align="left"><b>Step 1. Database Import</b></td></tr>
                        </tbody>
                        </table>
                        <br />
                        
                        <form method="post" action="step2.php">
                        <input type="hidden" name="submit" value="step2" />  
						<table width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text text">
						<tr>
							<td width="280px">&nbsp;Database Host&nbsp;<span style="color:#a60000">*</span></td>
							<td>
								<input type="text" class="form_text" name="database_host" value="localhost" size="30" />
								<img src="img/help_icon.jpg" class="help_icon" title="Hostname or IP-address of the database server. The database server can be in the form of a hostname, such as db1.myserver.com, or as an IP-address, such as 192.168.0.1">
								<?php if(EI_MODE == 'demo'){ ?> (demo: localhost) <?php } ?>
							</td>
						</tr>
						<tr>
							<td width="280px">&nbsp;Database Port&nbsp;<span style="color:#a60000">*</span></td>
							<td>
								<input type="text" class="form_text" name="database_port" value="3306" size="6" />
								<img src="img/help_icon.jpg" class="help_icon" title="Database port, default - 3306">
								<?php if(EI_MODE == 'demo'){ ?> (demo: 3306) <?php } ?>
							</td>
						</tr>
						<tr>
							<td>&nbsp;Database Name&nbsp;<span style="color:#a60000">*</span></td>
							<td>
								<input type="text" class="form_text" name="database_name" size="30" value="<?= $database_name; ?>" autocomplete="off" />
								<img src="img/help_icon.jpg" class="help_icon" title="Database Name. The database used to hold the data. An example database name is 'testdb'.">
								<?php if(EI_MODE == 'demo'){ ?> (demo: db_name) <?php } ?>
							</td>
						</tr>
						<tr>
							<td>&nbsp;Database Username&nbsp;<span style="color:#a60000">*</span></td>
							<td>
								<input type="text" class="form_text" name="database_username" size="30" value="<?= $database_username; ?>" autocomplete="off" />
								<img src="img/help_icon.jpg" class="help_icon" title="Database username. The username used to connect to the database server. An example username is 'test_123'.">
								<?php if(EI_MODE == 'demo'){ ?> (demo: test) <?php } ?>
							</td>
						</tr>
						<tr>
							<td>&nbsp;Database Password&nbsp;</td>
							<td>
								<input type="password" class="form_text" id="database_password" name="database_password" size="30" value="<?= $database_password; ?>" autocomplete="off" />
								<img src="img/help_icon.jpg" class="help_icon" title="Database password. The password is used together with the username, which forms the database user account.">
								<label style="cursor:pointer;margin-left:-60px" id="lbl_database_password" for="chk_database_password">Show</label>
								<input style="display:none;" type="checkbox" id="chk_database_password" onclick="togglePassword('database_password')">
								<?php if(EI_MODE == 'demo'){ ?> (demo: test) <?php } ?>
							</td>
						</tr>
						<tr>
							<td>&nbsp;Database Prefix (optional)</td>
							<td>
								<input type="text" class="form_text" name="database_prefix" size="12" maxlength="12" value="<?= $database_prefix; ?>" autocomplete="off" />
								<img src="img/help_icon.jpg" class="help_icon" title="Database prefix. Used to set the unique prefix for database tables.">
							</td>
						</tr>
						<tr>
							<td>&nbsp;Installation type</td>
							<td>
								<select name="install_type" onchange="install_type_OnChange(this.value)">
									<option value="new" <?= ($install_type == 'new') ? 'checked="checked"' : ''; ?>>Fresh installation</option>
									<?php
										foreach($EI_APPLICATION_UPDATE_VERSIONS as $key => $version){
											echo '<option value="'.$key.'" '.($install_type == $key ? 'checked="checked"' : '').'>'.$version.'</option>';
										}
									?>
								</select>
							</td>
						</tr>
						</table>
						
						<?php if(EI_USE_USERNAME_AND_PASWORD){ ?>
						<table id="tblAdminAccess" width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text text">
						<tr><td colspan="2">&nbsp;</td></tr>
						<tr><td width="280px" class="text" align=left><b>Admin access data</b></td><td>(you need it to enter the protected admin area)</td></tr>
						<tr>
							<td>&nbsp;Admin Login&nbsp;<span style="color:#a60000">*</span></td>
							<td class="text"><input name="username" type="text" size="28" maxlength="32" value="" autocomplete="off" /> <?php if(EI_MODE == 'demo'){ ?> (demo: test) <?php } ?></td>
						</tr>
						<tr>
							<td>&nbsp;Admin Password&nbsp;<span style="color:#a60000">*</span></td>
							<td class="text">
								<input id="password" name="password" type="password" size="28" maxlength="32" value="" autocomplete='off' /> <?php if(EI_MODE == 'demo'){ ?> (demo: test) <?php } ?>
								<label style="cursor:pointer;margin-left:-40px;" id="lbl_password" for="chk_password">Show</label>
								<input style="display:none;" type="checkbox" id="chk_password" onclick="togglePassword('password')">
							</td>
						</tr>
						<tr>
							<td>&nbsp;Admin Email (optional)</td>
							<td class="text"><input name="email" type="text" size="28" maxlength="70" value="" autocomplete='off' /> <?php if(EI_MODE == 'demo'){ ?> (demo: test@@example.com) <?php } ?></td>
						</tr>
							<?php if(EI_USE_PASSWORD_ENCRYPTION){ ?>
							<tr>
								<td>&nbsp;Password Encryption&nbsp;</td>
								<td class="text">
									<select name="password_encryption">
									<option <?= (($password_encryption == 'AES') ? 'selected="selected"' : ''); ?> value="AES">AES</option>
									<option <?= (($password_encryption == 'MD5') ? 'selected="selected"' : ''); ?> value="MD5">MD5</option>
									</select>
								</td>
							</tr>							
							<?php } ?>
						</table>
                        <table id="tblBackupMsg" width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text text" style="display:none;color:#bb5500;">
						<tr><td nowrap height="10px"></td></tr>
						<tr><td>We recommend creating your own backups before updating to the latest version of the script!</td></tr>
						</table>
						<?php } ?>

                        <table width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text text">
						<tr><td>&nbsp;</td></tr>
						<tr>
							<td align='left'>
								<a href='../install.php'><img class="form_button" src="img/button_cancel.gif" name="btn_back" title="Cancel installation" alt="cancel" /></a>
								&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="image" src="img/button_continue.gif" class="form_button" name="btn_submit" title="Continue installation" />
							</td>
						</tr>                        
                        </table>
                        </form>                        
						<br />
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
                
        <?php include_once('footer.php'); ?>        
    </td>
</tr>
</tbody>
</table>
                  
</body>
</html>