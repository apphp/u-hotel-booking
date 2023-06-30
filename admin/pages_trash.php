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

if($objLogin->IsLoggedInAsAdmin() &&
	$objLogin->HasPrivileges('edit_pages') || $objLogin->HasPrivileges('delete_pages')
){

	$act = isset($_GET['act']) ? prepare_input($_GET['act']) : '';
	$language_id = (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? prepare_input($_REQUEST['language_id']) : Languages::GetDefaultLang();
	$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : '';
	$msg = '';

	$objPage = new Pages($pid);	
	// do delete action
	if($act=='delete'){
		if($objPage->PageDelete() && $objLogin->HasPrivileges('delete_pages')){			
			$msg = draw_success_message(_PAGE_DELETED, false);
		}else{
			$msg = draw_important_message($objPage->error, false);
		}
	// do restore action	
	}elseif($act=='restore' && $objLogin->HasPrivileges('edit_pages')){
		if($objPage->PageRestore()){
			$msg = draw_success_message(_PAGE_RESTORED, false);
		}else{
			$msg = draw_important_message($objPage->error, false);
		}		
	}
	
	// start main content
	$all_pages = array();
	$all_pages = Pages::GetAll($language_id, 'removed');
	$total_languages = Languages::GetAllActive();
	
	draw_title_bar(prepare_breadcrumbs(array(_MENUS_AND_PAGES=>'',_PAGE_MANAGEMENT=>'',_TRASH_PAGES=>'')));

	if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();			
?>
	<script type="text/javascript">
	<!--
		function confirmDelete(pid){
			if(!confirm('<?= _PAGE_DELETE_WARNING;?>')){
				return false;
			}else{
				appGoTo('admin=pages_trash&act=delete&language_id=<?= $language_id; ?>&pid='+pid);
			}			
		}

		function confirmRestore(pid){
			if(!confirm('<?= _PAGE_RESTORE_WARNING;?>')){
				return false;
			}else{
				appGoTo('admin=pages_trash&act=restore&language_id=<?= $language_id; ?>&pid='+pid);
			}			
		}
	//-->
	</script>

	<div class="table-responsive">
	<table width="99%" class="mgrid_table">
	<tr>
		<td align="<?= Application::Get('defined_right');?>">
		<?php draw_languages_box('language_id', $total_languages[0], 'abbreviation', 'lang_name', $language_id, 'mgrid_select', 'onchange="appGoTo(\'admin=pages_trash&language_id=\'+this.value)"'); ?>
		</td>
	</tr>
	</table>

	<?php
	if($all_pages[1] > 0){
	?>
		<table width="99%" class="mgrid_table" style="min-width:600px;">
		<tbody>
		<tr><th colspan="6" height="3px" nowrap="nowrap"></th></tr>
		<tr>
			<th class="align_left" align="<?= Application::Get('defined_left'); ?>"><?= _PAGE_HEADER;?></th>
			<th class="align_center" width="20%"><?= _MENU_WORD;?></th>
			<th class="align_center" width="18%" nowrap="nowrap" align="center"><?= _REMOVED;?></th>
			<th class="align_center" width="15%" nowrap="nowrap"><?= _CONTENT_TYPE;?></th>
			<?php
				if($objLogin->HasPrivileges('edit_pages') || $objLogin->HasPrivileges('delete_pages')){
					echo '<th class="align_center" width="13%">'._ACTIONS_WORD.'</th>';
				}else{
					echo '<th></th>';
				}
			?>			
		</tr>
		<tr><td colspan="6" height="3px" nowrap="nowrap"><?php draw_line(); ?></td></tr>
		<?php
			for($i=0;$i<$all_pages[1];$i++){

				// prepare page header for display
				$page_header = $all_pages[0][$i]['page_title'];
				if(strlen($page_header) > 60) $page_header = substr($page_header,0,60).'..';

				// prepare menu link for display
				$menu_name = $all_pages[0][$i]['menu_name'];
				if(strlen($menu_name) > 18) $menu_name = substr($menu_name,0,18).'..';
				
				// display page row
				echo '<tr '.highlight($i, 0).' onmouseover="oldColor=this.style.backgroundColor;this.style.backgroundColor=\'#e7e7e7\';" onmouseout="this.style.backgroundColor=oldColor">
						<td align="'.Application::Get('defined_left').'">'.$page_header.'</td>
						<td align="center">'.(($menu_name!='')?$menu_name:_NOT_AVAILABLE).'</td>
						<td align="center">'.format_datetime($all_pages[0][$i]['status_changed']).'</td>
						<td align="center">'.ucfirst($all_pages[0][$i]['content_type']).'</td>
						<td align="center" nowrap>
							'.prepare_permanent_link('index.php?page=pages&pid='.$all_pages[0][$i]['id'].'&mg_language_id='.$language_id, _VIEW_WORD).'&nbsp;&nbsp;'.draw_divider(false).'&nbsp;
							'.($objLogin->HasPrivileges('edit_pages') ? '<a href="javascript:void(0)" onclick="javascript:confirmRestore(\''.$all_pages[0][$i]['id'].'\');">'._RESTORE.'</a>' : '').'
							'.(($objLogin->HasPrivileges('edit_pages') && $objLogin->HasPrivileges('delete_pages')) ? '&nbsp;'.draw_divider(false).'' : '').'&nbsp;
							'.($objLogin->HasPrivileges('delete_pages') ? '<a href="javascript:void(0)" onclick="javascript:confirmDelete(\''.$all_pages[0][$i]['id'].'\');">'._DELETE_WORD.'</a>' : '').'
						</td>
					</tr>';
			}
		?>
		</tbody>
		</table>
	</div>
<?php
	}else{
		draw_important_message(_PAGE_NOT_FOUND, true, true, false, 'width:100%');
	} 
	draw_content_end();			
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
