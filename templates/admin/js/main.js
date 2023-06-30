jQuery(document).ready(function(){
	var $ = jQuery;

	//------------------------------
	// Swipe handler
	//------------------------------
	$(".swipe-table")
		.on('swipeleft', function(){
			$(this).removeClass('swipe-table');
			$(this).find('.swipe-icon-wrapper').remove();
		})	
		.on('swiperight', function(){
			$(this).removeClass('swipe-table');
			$(this).find('.swipe-icon-wrapper').remove();
		})
});
