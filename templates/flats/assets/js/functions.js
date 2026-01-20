// ########################
// BACK TO TOP FUNCTION
// ########################


jQuery(document).ready(function(){
"use strict";
	
	// hide #back-top first
	jQuery("#back-top").hide();
        jQuery("#mod_calendar").addClass("table-responsive");
        jQuery("#roomprices").addClass("table-responsive");
        jQuery("#frmRegistration").addClass("table-responsive"); 
        jQuery("#checkout-form").addClass("table-responsive");
        jQuery("#divDescriptionContent,#divInvoiceContent").addClass("table-responsive");
        jQuery("#frmContactUs").addClass("table-responsive").addClass("login-form"); 
        jQuery(".filter-options").click(function(){
          jQuery(".filters").toggle(1000);
        }); 
        jQuery(".footer-toggle").click(function(){
          jQuery(".footerbgblack").toggle();
        });      
    	var url = "index.php?customer=my_account";;
        var filter = document.getElementsByClassName("filters");
        if(url == window.location.href)
        {
         filter[0].style.display ="block";
        }
	// fade in #back-top
	jQuery(function () {
		jQuery(window).scroll(function () {
			if (jQuery(this).scrollTop() > 700) {
				jQuery('#back-top').fadeIn();
			} else {
				jQuery('#back-top').fadeOut();
			}
		});

		// scroll body to 0px on click
		jQuery('#back-top a').click(function () {
			jQuery('body,html').animate({
				scrollTop: 0
			}, 500);
			return false;
		});
		
				// scroll body to 0px on click
		jQuery('a#gotop2').click(function () {
			jQuery('body,html').animate({
				scrollTop: 0
			}, 500);
			return false;
		});
		
		var jQueryih = jQuery('body').innerHeight();
		
		jQuery(".scroll").click(function(event){		
			event.preventDefault();
			jQuery('html,body').animate({scrollTop:jQuery(this.hash).offset().top - jQueryih}, 1500);
		});
		
		
		
		
		
	});
});



