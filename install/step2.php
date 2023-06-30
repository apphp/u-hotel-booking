<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

    require_once('settings.inc.php');    
    require_once('functions.inc.php');
    
    if(file_exists(EI_CONFIG_FILE_PATH)){        
		header('location: '.EI_APPLICATION_START_FILE);
        exit;
	}
	
	if(EI_MODE == 'debug') error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    
	$completed = false;
	$error_mg  = array();
	$submit = isset($_POST['submit']) ? stripcslashes($_POST['submit']) : '';
	
	if($submit != 'step2'){
		header('location: step1.php');
        exit;        
    }else{        
		$username				= isset($_POST['username']) ? stripcslashes($_POST['username']) : '';
		$password				= isset($_POST['password']) ? stripcslashes($_POST['password']) : '';
		$email				    = isset($_POST['email']) ? stripcslashes($_POST['email']) : 'admin@example.com';
		$database_host			= isset($_POST['database_host']) ? $_POST['database_host'] : '';
		$database_port			= isset($_POST['database_port']) ? $_POST['database_port'] : '';
		$database_name			= isset($_POST['database_name']) ? $_POST['database_name'] : '';
        $database_username	    = isset($_POST['database_username']) ? prepare_input($_POST['database_username']) : "";
		$database_password		= isset($_POST['database_password']) ? $_POST['database_password'] : '';
		$database_prefix    	= isset($_POST['database_prefix']) ? strtolower(stripcslashes($_POST['database_prefix'])) : '';
		$install_type			= isset($_POST['install_type']) ? $_POST['install_type'] : 'new';
		$password_encryption 	= isset($_POST['password_encryption']) ? $_POST['password_encryption'] : EI_PASSWORD_ENCRYPTION_TYPE;
		$link 					= null;
		
		if(isset($EI_APPLICATION_UPDATE_VERSIONS[$install_type])){
			$sql_dump_file = 'sql_dump/installation_update_'.$install_type.'.sql';
		}elseif($install_type == 'new'){
			$sql_dump_file = EI_SQL_DUMP_FILE_NEW;
		}
		
		if (empty($database_host)) $error_mg[] = 'Database host cannot be empty! Please re-enter.';
		if (empty($database_port)) $error_mg[] = 'Database port cannot be empty! Please re-enter.';
		if (empty($database_name)) $error_mg[] = 'Database name cannot be empty! Please re-enter.';	
		if (empty($database_username)) $error_mg[] = 'Database username cannot be empty! Please re-enter.';	
		//if (empty($database_password)) $error_mg[] = 'Database password cannot be empty! Please re-enter.';
		if($install_type == 'new' && EI_USE_USERNAME_AND_PASWORD && empty($username)) $error_mg[] = 'Admin username cannot be empty! Please re-enter.';
		if($install_type == 'new' && EI_USE_USERNAME_AND_PASWORD && empty($password)) $error_mg[] = 'Admin password cannot be empty! Please re-enter.';
		if($install_type == 'new' && EI_USE_USERNAME_AND_PASWORD && !empty($email) && !isValidEmail($email)) $error_mg[] = "Admin email must be in a valid email format! Please re-enter.";
		
		if(empty($error_mg)){
	
			if(EI_MODE == 'demo'){
				if($database_host == 'localhost' && $database_name == 'db_name' &&
				   $database_username == 'test' && $database_password == 'test'){
					$completed = true; 
				}else{
					$error_mg[] = 'Testing parameters are wrong! Please enter valid parameters.';
				}
			}else{				
				$config_file = file_get_contents(EI_CONFIG_FILE_TEMPLATE);
				$config_file = str_replace('<DB_HOST>', $database_host, $config_file);
				$config_file = str_replace('<DB_PORT>', $database_port, $config_file);
				$config_file = str_replace('<DB_NAME>', $database_name, $config_file);
				$config_file = str_replace('<DB_USER>', $database_username, $config_file);
				$config_file = str_replace('<DB_PASSWORD>', $database_password, $config_file);
				$config_file = str_replace('<DB_PREFIX>', $database_prefix, $config_file);
				$config_file = str_replace('<ENCRYPTION>', (EI_USE_PASSWORD_ENCRYPTION) ? 'true' : 'false', $config_file);			
				$config_file = str_replace('<ENCRYPTION_TYPE>', $password_encryption, $config_file);			
				$config_file = str_replace('<ENCRYPT_KEY>', EI_PASSWORD_ENCRYPTION_KEY, $config_file);
				$config_file = str_replace('<INSTALLATION_KEY>', random_string(10), $config_file);
				
				@chmod(EI_CONFIG_FILE_PATH, 0755);
				$f = @fopen(EI_CONFIG_FILE_PATH, 'w+');
				if(@fwrite($f, $config_file) > 0){
					@chmod(EI_CONFIG_FILE_DIRECTORY, 0755);
					$link = mysqli_connect($database_host, $database_username, $database_password, $database_name, $database_port);
					if($link){					
						// read sql dump file
						$sql_dump = file_get_contents($sql_dump_file);
						if($sql_dump != ''){							
							if(false == ($db_error = apphp_db_install($sql_dump_file))){
								$error_mg[] = 'SQL execution error! Please check carefully a syntax of SQL dump file.';                            
							}else{
								// additional operations, like setting up system preferences etc.
								// ...
								// ...
								$completed = true;                            
							}							
						}else{
							$error_mg[] = 'Could not read file '.$sql_dump_file.'! Please check if a file exists.';                            
						}						
					}else{
						if(EI_MODE == 'debug'){
							$error_mg[] = 'Database connecting error! Please check your connection parameters. <br />Error: '.mysqli_connect_error().'</span><br />';
						}else{
							$error_mg[] = 'Database connecting error! Please check your connection parameters. <br />Error: '.mysqli_connect_error().'</span><br />';
						}						
					}
				}else{				
					$error_mg[] = 'Cannot open configuration file '.EI_CONFIG_FILE_PATH.'. Please make sure you have the \'write\' permissions on this path.';
				}
				@fclose($f);
				if(count($error_mg) > 0) @unlink(EI_CONFIG_FILE_PATH);				
			}			
		}
	}
        
?>	


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>uHotelBooking :: Installation Wizard</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="img/styles.css"></link>
</head>
<body text="#000000" vlink="#2971c1" alink="#2971c1" link="#2971c1" bgcolor="#ffffff">
    
<table align="center" width="70%" cellspacing="0" cellpadding="2" border="0">
<tbody>
<tr><td>&nbsp;</td></tr>
<tr>
    <td class="text" valign="top">
        <h2>New Installation of <?= EI_APPLICATION_NAME;?>!</h2>
        
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
                    <td align="middle">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
						<?php
						if(!$completed){							
							foreach($error_mg as $msg){
								echo '<tr><td class="text" align="left"><span style="color:#bb5500;">&#8226; '.$msg.'</span></td></tr>';
							}
						?>
							<tr><td>&nbsp;</td></tr>
							<tr>
								<td class="text" align="left">	
									<img class="form_button" src="img/button_back.gif" name="btn_back" title="" alt="back" onclick="javascript: history.go(-1);" />
									&nbsp;&nbsp;&nbsp;&nbsp;
									<img class="form_button" src="img/button_retry.gif" name="btn_back" title="" alt="retry" onclick="javascript: location.reload();" />
								</td>
							</tr>							
						<?php } else {?>
							<tr><td>&nbsp;</td></tr>
							<TR>
								<TD class="text" align="left">
									<b>Step 2. Installation Completed</b>
								</td>
							</tr>
							<tr><td>&nbsp;</td></tr>	
							<tr>
								<TD class="text" align="left">
									The <b><?= EI_CONFIG_FILE_DIRECTORY.EI_CONFIG_FILE_NAME;?></b> file was successfully created.<br /><br />
									<?= EI_POST_TEXT; ?><br />
									<span style='color:#bb5500;'>
										For security reasons, please remove <b>install/</b> directory and <b>install.php</b> file from your server!!!
									</span>
									<br /><br />
									Proceed to:
										<?php if(EI_APPLICATION_START_FILE != ''){ ?><a href="<?= EI_APPLICATION_START_FILE;?>">Front-End</a>  | <?php } ?>
									    <?php if(EI_APPLICATION_ADMIN_FILE != ''){ ?><a href="<?= EI_APPLICATION_ADMIN_FILE;?>">Administrator Panel</a><?php } ?>
								</td>
							</tr>						
						<?php } ?>
                        </tbody>
                        </table>
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
