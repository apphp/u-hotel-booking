<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('car_rental') &&
	($objLogin->IsLoggedInAs('owner','mainadmin') ||
	($objLogin->IsLoggedInAs('agencyowner') && ModulesSettings::Get('car_rental', 'is_active') != 'no'))
){	
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_CAR_RENTAL=>'',_SETTINGS=>'',_INTEGRATION=>'')));
	draw_message(_WIDGET_INTEGRATION_MESSAGE_CARS.'<br>'._INTEGRATION_MESSAGE.'<br>'._WIDGET_INTEGRATION_MESSAGE_HINT_CARS);
	
	// Prepare assigned car companies
	//$assigned_to_agencies = $objLogin->IsLoggedInAs('agencyowner') ? implode(',', $objLogin->AssignedToCarAgencies()) : '';

	draw_content_start();
?>

	<h4><?= _SIDE_PANEL; ?>:</h4>
	<div class="integration-wrapper">
		<div class="pull-left">
			<textarea id="integration-code-side" cols="60" style="height:160px;margin:5px 0;" onclick="this.select()" readonly="readonly"><?php
				echo '<script type="text/javascript">'."\n";
				echo 'var hsJsHost = "'.APPHP_BASE.'";'."\n";
				echo 'var hsJsKey = "'.INSTALLATION_KEY.'";'."\n";
				//echo 'var hsAgencyIDs = "'.$assigned_to_agencies.'";'."\n";
				echo 'document.write(unescape(\'%3Cscript src="\' + hsJsHost + \'widgets/cars/ipanel-side/main.js" type="text/javascript"%3E%3C/script%3E\'));'."\n";
				echo '</script>'."\n";
			?></textarea>
			<br>			
			<a href="javascript:void(0);" onclick="appPopupWindow('integration_preview.html','integration-code-side',false)">[ <?= _PREVIEW; ?> ]</a>
		</div>		
		<div class="pull-left">
			<img src="templates/admin/images/integration-side-car.png" alt="integration" />
		</div>
	</div>
	<div class="clearfix"></div>
	<hr>

<?php
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

