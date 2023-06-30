<?php
/* Base directory */
$base_dir = '../../../../';

////////////////////////////////////////////////////////////////////////////////
define('APPHP_EXEC', 'access allowed');
define('APPHP_CONNECT', 'direct');
require_once($base_dir.'include/base.inc.php');
require_once($base_dir.'include/connection.php');

$is_demo = (defined('SITE_MODE') && SITE_MODE == 'demo') ? true : false;
////////////////////////////////////////////////////////////////////////////////

/**
 * Check image directory and create it if needed
 */
$salt = 'authors_';
$image_dir = 'images/upload/'.md5($salt.$objLogin->GetLoggedId()).'/';
if(!is_dir($base_dir.$image_dir)){
    if(!mkdir($base_dir.$image_dir, 0755, true)){
        echo draw_important_message(_AUTHOR_FOLDER_CREATION_ERROR, false);
        ///echo draw_important_message(_ADMIN_FOLDER_CREATION_ERROR, false);        
        exit;
    }
}

$imagebasedir = $base_dir.$image_dir;

/**
 * Define the extentions you want to show within the directory listing.
 * The extensions also limit the files the user can upload to your image folders.
 */
$supportedextentions = array('gif', 'png', 'jpeg', 'jpg', 'bmp');
$filetypes = array ('png' => 'jpg.gif', 'jpeg' => 'jpg.gif', 'bmp' => 'jpg.gif', 'jpg' => 'jpg.gif', 'gif' => 'gif.gif', 'psd' => 'psd.gif');
$act = isset($_GET['act']) ? filter_var($_GET['act'], FILTER_SANITIZE_STRING) : '';
$sel_file = isset($_GET['file']) ? strip_tags($_GET['file']) : '';
$sel_file = isset($_GET['file']) ? $image_dir.str_ireplace(array('/', '\\', '..', 'function'), '', $sel_file) : '';
$msg = '';
$get_sort = 'date';
$get_order = 'desc';
$files = array();


if(!$objLogin->IsLoggedInAsAdmin()){
    echo '<html><head><link href="'.$base_dir.'templates/admin/css/style.css" type="text/css" rel="stylesheet"></head><body>';
    draw_important_message(_NOT_AUTHORIZED, true, false);
    echo '</body></html>';
    exit;
}

if(isset($_FILES['image'])){
    if(is_uploaded_file($_FILES['image']['tmp_name'])){
		if($is_demo){
			$msg = draw_important_message('This operation is blocked in demo version!', false, false);
		}else{			
			//@todo change base_dir!
			
			//@todo change image location and naming (if needed)
			$image = $image_dir.$_FILES['image']['name'];
			$ext = strtolower(substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.')+1));
			$size = isset($_FILES['image']['size']) ? $_FILES['image']['size'] : 0;
	
			// prepare and check maximum allowed file size
			$ini_file_size = trim(ini_get('upload_max_filesize'));        
			$upload_file_size = ((int)$ini_file_size < (int)'10M') ? $ini_file_size : '10M';
			$last = strtolower($upload_file_size{strlen($upload_file_size)-1});
			switch($last) {
				case 'g':
					$max_file_size = $upload_file_size * 1024*1024*1024; break;
				case 'm':
					$max_file_size = $upload_file_size * 1024*1024; break;
				default:
					$max_file_size = $upload_file_size; break;
			}
			
			if(in_array($ext, $supportedextentions)){
				$act = 'upload';
			}        
			if($size > $max_file_size){
				$act = 'wrong_size';
			}
			
			if($act == 'upload'){
				move_uploaded_file($_FILES['image']['tmp_name'], $base_dir.$image);
			}elseif($act == 'wrong_size'){
				$msg = draw_important_message('The uploaded file exceeds the maximum allowed size '.$upload_file_size.'!', false, false);            
			}else{
				$msg = draw_important_message('Extension is not allowed!<br>Please select another image for uploading.', false, false);
			}
		}
    }else{
        $msg = draw_important_message('No file selected for uploading!', false, false);
    }
}elseif($act == 'remove'){
    if($is_demo){
        $msg = draw_important_message('This operation is blocked in demo version!', false, false);
    }else{
        @unlink($base_dir.$sel_file);    
    }
}

clearstatcache();
$leadon = '';
if($handle = opendir($base_dir.$image_dir)){
    $n=0;
    while(false !== ($file = readdir($handle))){ 
        //first see if this file is required in the listing
        if($file == "." || $file == "..")  continue;
        
        if(@filetype($leadon.$file) != "dir") {
            $n++;
            if($get_sort == "date"){
                $key = @filemtime($leadon.$file).'.'.$n;
            }elseif($get_sort == "size"){
                $key = @filesize($leadon.$file).'.'.$n;
            }else {
                $key = $n;
            }
            $files[$key] = $file;
        }
    }
    closedir($handle); 
}

//sort our files
if($get_sort == "date"){
    @ksort($dirs, SORT_NUMERIC);
    @ksort($files, SORT_NUMERIC);
}elseif($get_sort == "size"){
    @natcasesort($dirs); 
    @ksort($files, SORT_NUMERIC);
}else{
    @natcasesort($dirs); 
    @natcasesort($files);
}

//order correctly
if($get_order == "desc" && $get_order != "size") {$dirs = @array_reverse($dirs);}
if($get_order == "desc") {$files = @array_reverse($files);}
$dirs = @array_values($dirs); $files = @array_values($files);

    
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">	
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Upload / Select Image</title>
    <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
    <link href="<?= $base_dir;?>templates/admin/css/style.css" type="text/css" rel="stylesheet">
    <script type="text/javascript">
    function selectImage(img_src){
        var ImageDialog = {
          init : function(ed) {
            ed.execCommand('mceInsertContent', false, 
              tinyMCEPopup.editor.dom.createHTML('img', {
                src : img_src
              })
            );            
            tinyMCEPopup.editor.execCommand('mceRepaint');
            tinyMCEPopup.editor.focus();
            tinyMCEPopup.close();
          }
        };
        tinyMCEPopup.onInit.add(ImageDialog.init, ImageDialog);        
    }
    function deleteImage(file_name){
        window.location.href = 'upload.php?act=remove&file=' + file_name;
    }
    </script>
</head>
<body style="padding:0px;margin:0px;">
<?php if($act == 'upload'){ ?>
    <script>
    selectImage('<?= $image; ?>');
    </script>
<?php }elseif($act == 'select'){ ?>
    <script>
    selectImage('<?= $sel_file; ?>');
    </script>
<?php }else{
    
    echo $msg;    
?>
    <form name="iform" action="" method="post" enctype="multipart/form-data">
        <input id="file" accept="image/*" type="file" name="image" style="width:220px;" />
        <button <?= (($is_demo) ? "disabled='disabled'" : "");?>>Upload</button>
    </form>
<?php

    $arsize = sizeof($files);
    echo '<div style="overflow-x:auto; height:200px;">';
    echo '<table width="99%" border="0">';
    echo '<tr><td colspan="3">&nbsp;&nbsp;<b>'._IMAGES.':</b></td></tr>';
    for($i=0; $i<$arsize; $i++) {
        $icon = 'unknown.png';
        $ext = strtolower(substr($files[$i], strrpos($files[$i], '.')+1));
        if(in_array($ext, $supportedextentions)) {
            
            $icon = ($filetypes[$ext]) ? $filetypes[$ext] : 'unknown.png';
                        
            $filename = $files[$i];
            if(strlen($filename) > 43) {
                $filename = substr($files[$i], 0, 40) . '...';
            }
            $fileurl = $leadon . $files[$i];
            $filedir = str_replace($imagebasedir, "", $leadon);

            echo '<tr>';
            echo '<td width="30px" align="center"><img src="img/'.$icon.'" alt="" /></td>';
            echo '<td><a href="javascript:void(0)" onclick="window.location.href=\'upload.php?act=select&file='.$filedir.$filename.'\'"><b>'.$filename.'</b></a></td>';
            if($is_demo){
                echo '<td>[x]</td>';
            }else{
                echo '<td><a href="javascript:void(0)" onclick="if(confirm(\'Are you sure?\')) deleteImage(\''.$filename.'\');" title="Delete"><b>[x]</b></a></td>';
            }
            echo '</tr>';
            
            //if($class=='b') $class='w';
            //else $class = 'b';	
        }
    }
    if(!$arsize) echo '<tr><td colspan="3" align="center">No images found</td></tr>';
    echo '</table>';
    echo '</div>';
}
?>
</body>
</html>
