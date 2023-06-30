<?php
/**
* @project ApPHP Hotel Site
* @copyright (c) 2012 ApPHP
* @author ApPHP <info@apphp.com>
* @license http://www.gnu.org/licenses/
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------
	
if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('testimonials')){
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$email		= MicroGrid::GetParameter('email', false);
	$mode   	= 'view';
	$msg 		= '';
	
	$objTestimonials = new Testimonials();

	if($action=='add'){		
		$mode = 'add';
	}elseif($action=='create'){
		if($objTestimonials->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objTestimonials->error, false);
			$mode = 'add';
		}
	}elseif($action=='edit'){
		$mode = 'edit';
	}elseif($action=='update'){
		if($objTestimonials->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objTestimonials->error, false);
			$mode = 'edit';
		}		
	}elseif($action=='delete'){
		if($objTestimonials->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objTestimonials->error, false);
		}
		$mode = 'view';
	}elseif($action=='details'){		
		$mode = 'details';		
	}elseif($action=='cancel_add'){		
		$mode = 'view';		
	}elseif($action=='cancel_edit'){				
		$mode = 'view';
    }else{
        $action = '';
	}
	
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_TESTIMONIALS=>'',_TESTIMONIALS_MANAGEMENT=>'',ucfirst($action)=>'')));
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
	if($mode == 'view'){
		$objTestimonials->DrawViewMode();	
	}elseif($mode == 'add'){		
		$objTestimonials->DrawAddMode();		
	}elseif($mode == 'edit'){		
		$objTestimonials->DrawEditMode($rid);		
	}elseif($mode == 'details'){		
		$objTestimonials->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

