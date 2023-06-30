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

?>
<!-- Right Sidebar -->
<div class="side-bar right-bar">
	<a href="javascript:void(0);" class="right-bar-toggle">
		<i class="zmdi zmdi-close-circle-o"></i>		
	</a>
	<h4><i class="zmdi zmdi-notifications-active zmdi-hc-fw"></i> <?= _ACTION_REQUIRED; ?></h4>
	<div class="notification-list nicescroll">
		<?php		
			// draw important messages 
			// ---------------------------------------------------------------------
			echo '<ul class="list-group list-no-border user-list">';
			if(count($actions_msg) > 0){
				$count = 0;
				foreach($actions_msg as $single_msg){
					echo '<li class="list-group-item">
						<div class="user-desc">
							'.++$count.'.
							<span class="name">'.$single_msg.'</span>							
						</div>
					</li>';
				}				
			}else{
				echo '<li class="list-group-item">'._NO_RECORDS_FOUND.'</li>';
			}
			echo '</ul>';
		?>
	</div>
</div>
<!-- /Right-bar -->
