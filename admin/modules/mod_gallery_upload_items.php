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

if($objLogin->IsLoggedInAsAdmin() && Modules::IsModuleInstalled('gallery')){	

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$album  = MicroGrid::GetParameter('album', false);
	$mode   = 'view';
	$msg    = '';
	
	$objAlbums = new GalleryAlbums();
	$album_info = $objAlbums->GetAlbumInfo($album);
	
	if(count($album_info) > 0){
		
		$objAlbumItems = new GalleryAlbumItems();
		
		if($action=='add'){		
			$mode = 'add';
		}elseif($action=='create'){
			if($objAlbumItems->AddRecord()){		
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objAlbumItems->error, false);
				$mode = 'add';
			}
		}elseif($action=='edit'){
			$mode = 'edit';
		}elseif($action=='update'){
			if($objAlbumItems->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objAlbumItems->error, false);
				$mode = 'edit';
			}		
		}elseif($action=='delete'){
			if($objAlbumItems->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objAlbumItems->error, false);
			}
			$mode = 'view';
		}elseif($action=='details'){		
			$mode = 'details';		
		}elseif($action=='cancel_add'){		
			$mode = 'view';		
		}elseif($action=='cancel_edit'){				
			$mode = 'view';
		}

		// Start main content
		draw_title_bar(
			prepare_breadcrumbs(array(_MODULES=>'',_GALLERY=>'',_GALLERY_MANAGEMENT=>'',_ALBUM=>'',$album_info[0]['name']=>'',ucfirst($action)=>'')),
			prepare_permanent_link('index.php?admin=mod_gallery_management', _BUTTON_BACK)
		);
	
		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		echo $msg;
	
		draw_content_start();
		if($mode == 'view'){		
			$objAlbumItems->DrawViewMode();	
		}elseif($mode == 'add'){		
			$objAlbumItems->DrawAddMode();		
		}elseif($mode == 'edit'){		
			$objAlbumItems->DrawEditMode($rid);		
		}elseif($mode == 'details'){		
			$objAlbumItems->DrawDetailsMode($rid);		
		}
		draw_content_end();		
	}else{
		draw_title_bar(
			prepare_breadcrumbs(array(_MODULES=>'',_GALLERY_MANAGEMENT=>'',_ALBUM=>'')),
			prepare_permanent_link('index.php?admin=mod_gallery_management', _BUTTON_BACK)
		);
		draw_important_message(_WRONG_PARAMETER_PASSED);
	}
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

