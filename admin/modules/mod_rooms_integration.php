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

if($objLogin->IsLoggedInAs('owner','mainadmin','hotelowner','regionalmanager') && Modules::IsModuleInstalled('rooms')){	

	// Start main content
    if(FLATS_INSTEAD_OF_HOTELS){
        draw_title_bar(prepare_breadcrumbs(array(_FLATS_MANAGEMENT=>'',_SETTINGS=>'',_INTEGRATION=>'')));
    }else{
        draw_title_bar(prepare_breadcrumbs(array(_HOTELS_MANAGEMENT=>'',_SETTINGS=>'',_INTEGRATION=>'')));
    }
	draw_message(_WIDGET_INTEGRATION_MESSAGE.'<br>'._INTEGRATION_MESSAGE.'<br>'._WIDGET_INTEGRATION_MESSAGE_HINT);
	
	// Prepare assigned hotels
	$assigned_to_hotels = $objLogin->IsLoggedInAs('hotelowner') ? implode(',', $objLogin->AssignedToHotels()) : '';

	draw_content_start();
?>

	<h4><?= _SIDE_PANEL; ?>:</h4>
	<div class="integration-wrapper">
		<div class="pull-left">
			<textarea id="integration-code-side" cols="60" style="height:170px;margin:5px 0;" onclick="this.select()" readonly="readonly"><?php
				echo '<script type="text/javascript">'."\n";
				echo 'var hsJsHost = "'.APPHP_BASE.'";'."\n";
				echo 'var hsJsKey = "'.INSTALLATION_KEY.'";'."\n";
				echo 'var hsHotelIDs = "'.$assigned_to_hotels.'";'."\n";
				echo 'document.write(unescape(\'%3Cscript src="\' + hsJsHost + \'widgets/hotels/ipanel-side/main.js" type="text/javascript"%3E%3C/script%3E\'));'."\n";
				echo '</script>';
			?></textarea>
			<br>			
			<a href="javascript:void(0);" onclick="appPopupWindow('integration_preview.html','integration-code-side',false)">[ <?= _PREVIEW; ?> ]</a>
		</div>		
		<div class="pull-left">
			<img src="templates/admin/images/integration-side.png" alt="integration" />
		</div>
	</div>
	<div class="clearfix"></div>
	<hr>
	
	<h4><?= _TOP_PANEL; ?>:</h4>
	<div class="integration-wrapper">
		<div class="pull-left">
			<textarea id="integration-code-top" cols="60" style="height:170px;margin:5px 0;" onclick="this.select()" readonly="readonly"><?php
				echo '<script type="text/javascript">'."\n";
				echo 'var hsJsHost = "'.APPHP_BASE.'";'."\n";
				echo 'var hsJsKey = "'.INSTALLATION_KEY.'";'."\n";
				echo 'var hsHotelIDs = "'.$assigned_to_hotels.'";'."\n";
				echo 'document.write(unescape(\'%3Cscript src="\' + hsJsHost + \'widgets/hotels/ipanel-top/main.js" type="text/javascript"%3E%3C/script%3E\'));'."\n";
				echo '</script>';
			?></textarea>
			<br>			
			<a href="javascript:void(0);" onclick="appPopupWindow('integration_preview.html','integration-code-top',false)">[ <?= _PREVIEW; ?> ]</a>
		</div>		
		<div class="pull-left">
			<img src="templates/admin/images/integration-top.png" alt="integration" />
		</div>
	</div>
	<div class="clearfix"></div>

<?php if(is_dir('widgets/hotels/ipanel-center')){ ?>
	<hr>
	<h4><?= _CENTER_PANEL; ?>:</h4>
	<div class="integration-wrapper">
		<div class="pull-left">
			<textarea id="integration-code-center" cols="60" style="height:170px;margin:5px 0;" onclick="this.select()" readonly="readonly"><?php
				echo '<script type="text/javascript">'."\n";
				echo 'var hsJsHost = "'.APPHP_BASE.'";'."\n";
				echo 'var hsJsKey = "'.INSTALLATION_KEY.'";'."\n";
				echo 'var hsHotelIDs = "'.$assigned_to_hotels.'";'."\n";
				echo 'var wgFullLayout = true; // true|false'."\n";
				echo 'var wgTypeSearchForm = "vertical"; // "vertical|horizontal"'."\n";
				echo '// The display type for the widget'."\n";
				echo 'var wgLayoutType = "layout-2"; // "layout-1|layout-2"'."\n";
				echo '// image in directly widgets/hotels/ipanel-center/template/images/background/'."\n";
				echo '// or use full path http://example.com/path/bg.jpg'."\n";
				echo 'var hsBkg = "bg.jpg";'."\n";
				echo '// css in directly widgets/hotels/ipanel-center/template/css/'."\n";
				echo '// or use full path http://example.com/path/mystyle.css'."\n";
				echo 'var hsCss = "style.css";'."\n";
				echo 'var hsLang = "en";'."\n";
				echo 'document.write(unescape(\'%3Cscript src="\' + hsJsHost + \'widgets/hotels/ipanel-center/main.js" type="text/javascript"%3E%3C/script%3E\'));'."\n";
				echo '</script>';
			?></textarea>
			<br>			
			<a href="javascript:void(0);" onclick="appPopupWindow('integration_preview.html','integration-code-center',false)">[ <?= _PREVIEW; ?> ]</a>
		</div>		
		<div class="pull-left">
			<img src="templates/admin/images/integration-center.png" alt="integration" />
		</div>
	</div>
    <div class="clearfix"></div>
<?php } ?>
	<br><br>

<?php
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

