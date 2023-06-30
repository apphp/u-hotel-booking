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
   ($objLogin->HasPrivileges('add_menus') || $objLogin->HasPrivileges('edit_menus') || $objLogin->HasPrivileges('delete_menus'))
   ){ 
	
	$act = isset($_GET['act']) ? prepare_input($_GET['act']) : '';
	$mid = isset($_GET['mid']) ? (int)$_GET['mid'] : '';
	$mo  = isset($_GET['mo']) ? (int)$_GET['mo'] : '';
	$dir = isset($_GET['dir']) ? prepare_input($_GET['dir']) : '';
	$language_id = (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? prepare_input($_REQUEST['language_id']) : Languages::GetDefaultLang();
	$msg = '';
	
	$objMenu = new Menu($mid);
	
	if($act=='delete' && $objLogin->HasPrivileges('delete_menus')){
		// delete menu action
		if($objMenu->MenuDelete($mid, $mo)){
			$msg = draw_success_message(_MENU_DELETED, false);
		}else{
			$msg = draw_important_message($objMenu->error, false);
		}
	}elseif($act=='move' && $objLogin->HasPrivileges('edit_menus')){
		// move menu action
		if($objMenu->MenuMove($mid, $dir, $mo)){
			$msg = draw_success_message(_MENU_ORDER_CHANGED, false);
		}else{
			$msg = draw_important_message($objMenu->error, false);
		}		
	}
	
	// Start main content
	$all_menus = array();
	$all_menus = Menu::GetAll(' menu_order ASC', TABLE_MENUS, '', $language_id);
	
	$total_languages = Languages::GetAllActive();	
	
	draw_title_bar(prepare_breadcrumbs(array(_MENUS_AND_PAGES=>'',_MENU_MANAGEMENT=>'',_EDIT_MENUS=>'')));

	if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();	
?>    
	<script type="text/javascript">
	<!--
		function confirmDelete(mid, mo){
			if(!confirm('<?= _MENU_DELETE_WARNING;?>')){
				false;
			}else{
				appGoTo('admin=menus&act=delete&mid='+mid+'&mo='+mo+'&language_id=<?= $language_id; ?>');
			}			
		}
	//-->
	</script>
	
	<div class="table-responsive">
	<table width="100%" class="main_text" style="min-width:600px">
	<tr valign="middle">
		<td align="<?= Application::Get('defined_right'); ?>"><?= _LANGUAGE;?>: &nbsp;</td>
		<td align="<?= Application::Get('defined_right'); ?>" width="80px">
			<?php draw_languages_box('language_id', $total_languages[0], 'abbreviation', 'lang_name', $language_id, 'mgrid_select', 'onchange="appGoTo(\'admin=menus&language_id=\'+this.value)"'); ?>
		</td>
	</tr>
	<tr><td nowrap="nowrap" height="5px"></td></tr>
	</table>
	
	<?php
	if($all_menus[1] > 0){ ?>				
		<table width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text">
		<tr><td colspan="6" height="3px" nowrap="nowrap"></td></tr>
		<tr>
			<th align="<?= Application::Get('defined_left'); ?>"><?= _MENU_TITLE;?></th>
			<th width="11%"><?= _ACCESS;?></th>
			<th width="11%"><?= _DISPLAY_ON;?></th>
			<th width="9%"><?= _ORDER;?></th>
			<th width="15%"><?= _CHANGE_ORDER;?></th>
			<?php
				if($objLogin->HasPrivileges('edit_menus') || $objLogin->HasPrivileges('delete_menus')){
					echo '<th width="8%">'._ACTIONS.'</th>';
				}else{
					echo '<th></th>';
				}
			?>
		</tr>
		<tr><td colspan="6" height="3px" nowrap="nowrap"><?php draw_line(); ?></td></tr>
		<?php
			for($i=0; $i<$all_menus[1]; $i++){				
				echo '<tr '.highlight($i, 0).' onmouseover="oldColor=this.style.backgroundColor;this.style.backgroundColor=\'#e1e1e1\';" onmouseout="this.style.backgroundColor=oldColor">
					<td align="'.Application::Get('defined_left').'">'.$all_menus[0][$i]['menu_name'].'</td>
					<td align="center">'.ucfirst($all_menus[0][$i]['access_level']).'</td>
					<td align="center">'.(($all_menus[0][$i]['menu_placement'] == 'hidden') ? '- '.$all_menus[0][$i]['menu_placement'].' -' : $all_menus[0][$i]['menu_placement']).'</td>
					<td align="center">'.$all_menus[0][$i]['menu_order'].'</td>
					<td align="center">
					   '.prepare_permanent_link('index.php?admin=menus&act=move&mid='.$all_menus[0][$i]['id'].'&mo='.$all_menus[0][$i]['menu_order'].'&dir=up&language_id='.$language_id, _UP).'/'.prepare_permanent_link('index.php?admin=menus&act=move&mid='.$all_menus[0][$i]['id'].'&mo='.$all_menus[0][$i]['menu_order'].'&dir=down&language_id='.$language_id, _DOWN).'
					</td>
					<td align="center" nowrap>
						'.($objLogin->HasPrivileges('edit_menus') ? prepare_permanent_link('index.php?admin=menus_edit&mid='.$all_menus[0][$i]['id'].'&language_id='.$language_id, _EDIT_WORD) : '').'
						'.(($objLogin->HasPrivileges('edit_menus') && $objLogin->HasPrivileges('delete_menus')) ? '&nbsp;'.draw_divider(false).'&nbsp;' : '').'						
						'.($objLogin->HasPrivileges('delete_menus') ? '<a href="javascript:confirmDelete(\''.$all_menus[0][$i]['id'].'\', \''.$all_menus[0][$i]['menu_order'].'\');">'._DELETE_WORD.'</a>' : '').'
					</td>
				</tr>';
			}
		echo '</table>';
		echo '</div>';
	}else{
		draw_message(_MENU_NOT_FOUND);
	} 
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
