var active_menu_count = 0;

//--------------------------------------------------------------------------
function set_active_menu_count(val) {
	active_menu_count = val;
}

//--------------------------------------------------------------------------
function toggle_meta(){
	if(document.getElementById('row_meta_2').style.display == ""){
		document.getElementById('meta_tags_status').value = "closed";
		document.getElementById('row_meta_2').style.display = "none";
		document.getElementById('row_meta_3').style.display = "none";
		document.getElementById('row_meta_4').style.display = "none";
		document.getElementById('meta_close').style.display = "none";
		document.getElementById('meta_show').style.display = "";
	}else{
		document.getElementById('meta_tags_status').value = "opened";
		document.getElementById('row_meta_2').style.display = "";
		document.getElementById('row_meta_3').style.display = "";
		document.getElementById('row_meta_4').style.display = "";
		document.getElementById('meta_close').style.display = "";
		document.getElementById('meta_show').style.display = "none";
	}
};

//--------------------------------------------------------------------------
function ContentType_OnChange(val){
	if(val == "link"){
		document.getElementById("link_row_1").style.display = "";			
		document.getElementById("link_row_2").style.display = "";
		document.getElementById("page_row_1").style.display = "none";
		document.getElementById("page_row_2").style.display = "none";
		//document.getElementById("page_row_3").style.display = "";
		document.getElementById("row_meta_1").style.display = "none";
		if(document.getElementById("comments_allowed_1")) document.getElementById("comments_allowed_1").style.display = "none";
		if(document.getElementById("comments_allowed_2")) document.getElementById("comments_allowed_2").style.display = "none";
		if(document.getElementById("comments_allowed_3")) document.getElementById("comments_allowed_3").style.display = "none";
	}else{
		document.getElementById("link_row_1").style.display = "none";				
		document.getElementById("link_row_2").style.display = "none";
		document.getElementById("page_row_1").style.display = "";
		document.getElementById("page_row_2").style.display = "";
		//document.getElementById("page_row_3").style.display = "none";
		document.getElementById("row_meta_1").style.display = "";
		document.getElementById('meta_close').style.display = "none";
		document.getElementById('meta_show').style.display = "";
		if(document.getElementById("comments_allowed_1")) document.getElementById("comments_allowed_1").style.display = "";
		if(document.getElementById("comments_allowed_2")) document.getElementById("comments_allowed_2").style.display = "";
		if(document.getElementById("comments_allowed_3")) document.getElementById("comments_allowed_3").style.display = "";
	}
	document.getElementById("row_meta_2").style.display = "none";
	document.getElementById("row_meta_3").style.display = "none";
	document.getElementById("row_meta_4").style.display = "none";
}

//--------------------------------------------------------------------------
function toggle_menu_block(ind){
	var old_status = appGetMenuStatus(ind);
	if(!jQuery("#side_box_content_"+ind)){
		if(document.getElementById("side_box_content_"+ind).style.display == ""){
			document.getElementById("side_box_content_"+ind).style.display = "none";
		}else{
			document.getElementById("side_box_content_"+ind).style.display = "";
		}
	}else{
		jQuery("#side_box_content_"+ind).slideToggle("fast");		
	}
	save_menu_status(ind, old_status);				
};

//--------------------------------------------------------------------------
// save menu blocks status 
function toggle_menus(status){
	for(var i = 0; i < active_menu_count; i++){
		appSetCookie("side_box_content_"+i,((status == 1) ? "maximized" : "minimized"),14);
		if(status == 1){
			if(!jQuery("#side_box_content_"+i)) document.getElementById("side_box_content_"+i).style.display = "";
			else if(!jQuery("#side_box_content_"+i).is(":visible")) jQuery("#side_box_content_"+i).show("fast");
		}else{
			if(!jQuery("#side_box_content_"+i)) document.getElementById("side_box_content_"+i).style.display = "none";
			else if(jQuery("#side_box_content_"+i).is(":visible")) jQuery("#side_box_content_"+i).hide("fast");			
		}
	}
}

//--------------------------------------------------------------------------
// save menu blocks status 
function save_menu_status(ind, old_status){   
	for(var i = 0; i < active_menu_count; i++){
		if(i == ind){
			if(old_status == "none"){
				appSetCookie("side_box_content_"+i,"maximized",14);
			}else{
				appSetCookie("side_box_content_"+i,"minimized",14);
			}					
		}else{
			var status = document.getElementById("side_box_content_"+i).style.display;
			if(status == "none"){
				appSetCookie("side_box_content_"+i,"minimized",14);
			}else{
				appSetCookie("side_box_content_"+i,"maximized",14);
			}		
		}
	}
}

//--------------------------------------------------------------------------
// change icon
function change_icon(val, token){
	var token_ = (token != null) ? token : "";
	var icon_img_src = "images/no_image.png";
	
	jQuery('.loading_img').show();
	
	jQuery.ajax({
		url: "ajax/handler.ajax.php",
		global: false,
		type: "POST",
		data: ({template : val, check_key : "apphphs", token : token_}),
		dataType: "html",
		async:false,
		error: function(html){
			jQuery('.loading_img').hide();
			alert("AJAX: cannot connect to the server or server response error! Please try again later.");
		},
		success: function(html){			
			var obj = jQuery.parseJSON(html);            			
			if(obj.status == "1"){
				jQuery("#template_name").html(obj.template_name);
				jQuery("#template_direction").html(obj.template_direction);
                jQuery("#template_author").html(obj.template_author);
				jQuery("#template_description").html(obj.template_description);
				jQuery("#template_license").html(obj.template_license);
				jQuery("#template_version").html(obj.template_version);
				jQuery("#template_layout").html(obj.template_layout);
				jQuery("#template_menus").html(obj.template_menus);
				if(val != "") icon_img_src = 'templates/' + val + '/' + obj.template_icon;
			}else{
				jQuery("#template_name").html("");                
				jQuery("#template_direction").html("");
                jQuery("#template_author").html("");
				jQuery("#template_description").html("");
				jQuery("#template_license").html("");
				jQuery("#template_version").html("");
				jQuery("#template_layout").html("");
				jQuery("#template_menus").html("");
			}
			jQuery('.loading_img').hide();
			jQuery("#template_icon").attr("src", icon_img_src);				
		}
	});	
}

//--------------------------------------------------------------------------
// collapse/expand navigation panel
function toggle_navigation_panel(status){
	appSetCookie("nav_panel_state",((status == 1) ? "expanded" : "collapsed"),14);
	if(status == 0){
		jQuery('#navColumnLeftWrapper').hide();
		jQuery('#imgCollapse').hide();
		jQuery('#imgExpand').show();	
	}else{
		jQuery('#navColumnLeftWrapper').show();
		jQuery('#imgCollapse').show();
		jQuery('#imgExpand').hide();		
	}	
}

//--------------------------------------------------------------------------
function AccountType_OnChange(val){
	if(val == "hotelowner" || val == "agencyowner" || val == "hotelmanager"){
		jQuery('#mg_row_companies').show();
		jQuery('#mg_row_autocomplete_companies').show();
	}else{
		jQuery('#mg_row_companies').hide();	
		jQuery('#mg_row_autocomplete_companies').hide();
	}
}

//--------------------------------------------------------------------------
// generate
function generateRandomAlphaNumeric(el, length){

	if(el === null){
		return false;
	}

	length = (length === null ? '8' : length);
    var result = "";
	var i = 0;
    var randomNumber = "";
	
    while(i < length){
        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
		if(randomNumber == 32 || randomNumber == 34 || randomNumber == 39 || randomNumber == 96 || randomNumber > 126) { continue; }
		if((randomNumber >=33) && (randomNumber <=47)) { continue; }
		if((randomNumber >=58) && (randomNumber <=64)) { continue; }
		if((randomNumber >=91) && (randomNumber <=96)) { continue; }
		if((randomNumber >=123) && (randomNumber <=126)) { continue; }
		i++;
        result += String.fromCharCode(randomNumber);
    }

	document.getElementById(el).value = result.toLowerCase();		
}

//-------------------------------------------------------------------------
// Calculate distance

/*
 * The distance between the two points
 * latA, longA - latitude, longitude 1st point
 * latB, longB - latitude, longitude, 2nd point
 * Written explanations http://gis-lab.info/qa/great-circles.html
 */
function calculateTheDistance (latA, longA, latB, longB) {
	// Radius the Earth in km
	var EARTH_RADIUS = 6372.795;

	// Translate coordinates in radians
	var lat1 = latA * Math.PI / 180;
	var lat2 = latB * Math.PI / 180;
	var long1 = longA * Math.PI / 180;
	var long2 = longB * Math.PI / 180;

	// косинусы и синусы широт и разницы долгот
	var cl1 = Math.cos(lat1);
	var cl2 = Math.cos(lat2);
	var sl1 = Math.sin(lat1);
	var sl2 = Math.sin(lat2);
	var delta = long2 - long1;
	var cdelta = Math.cos(delta);
	var sdelta = Math.sin(delta);

	var y = Math.sqrt(Math.pow(cl2 * sdelta, 2) + Math.pow(cl1 * sl2 - sl1 * cl2 * cdelta, 2));
	var x = sl1 * sl2 + cl1 * cl2 * cdelta;

	//
	var ad = Math.atan2(y, x);
	var dist = ad * EARTH_RADIUS;

	return dist;
}

function Hotel_GetDistantionCenterPoint(){
	var latitude_hotel = jQuery("#latitude").val();
	var latitude_center = jQuery("#latitude_center").val();
	var longitude_hotel = jQuery("#longitude").val();
	var longitude_center = jQuery("#longitude_center").val();
	
	if(latitude_hotel == ''){
		alert('The field "Latitude" can not be empty');
		jQuery("#latitude").focus();
	}else if(longitude_hotel == ''){
		alert('The field "Longitude" can not be empty');
		jQuery("#longitude").focus();
	}else if(latitude_center == ''){
		alert('The field "Latitude (Center Point)" can not be empty');
		jQuery("#latitude_center").focus();
	}else if(longitude_center == ''){
		alert('The field "Longitude" (Center Point) can not be empty');
		jQuery("#longitude_center").focus();
	}else{
		var distance = calculateTheDistance(latitude_hotel, longitude_hotel, latitude_center, longitude_center);
		distance = distance.toFixed(2);
		jQuery("#distance_center").val(distance);
	}
}

//--------------------------------------------------------------------------
// open poupup window
function open_popup(file, key_1, key_2, key_3, token){
	var token_ = (token != null) ? token : '';
	jQuery.ajax({
		url: "ajax/"+file,
		global: false,
		type: "POST",
		data: ({param : key_1, id : key_2, customer_type : key_3, check_key : "apphphs", token : token_}),
		dataType: "html",
		async:false,
		error: function(html){
			alert("AJAX: cannot connect to the server or server response error! Please try again later.");
		},
		success: function(html){
			var obj = jQuery.parseJSON(html);            			
			if(obj.status == "1"){
				var new_window = window.open('','PopupWindow','height=500,width=550,scrollbars=yes,screenX=710,screenY=100,toolbar=no,location=no,menubar=no',false);				
				if(window.focus) new_window.focus();
				
				var message = '<html>';
				message += '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
				message += '<body>'+utf8_decode(obj.content)+'</body>';
				message += '</html>';

				new_window.document.open();
				new_window.document.write(message);
				new_window.document.close();
			}else{
				alert("An error occurred while processing your request! Please try again later.");
			}
		}
	});	
}

// Converts a string 
// original by: Webtoolkit.info (http://www.webtoolkit.info/)
function utf8_decode(str_data){
	
	var tmp_arr = [], i = 0, ac = 0, c1 = 0, c2 = 0, c3 = 0;	
	str_data += '';
	
	while(i < str_data.length){
		c1 = str_data.charCodeAt(i);
		if(c1 < 128){
			tmp_arr[ac++] = String.fromCharCode(c1);
			i++;
		}else if (c1 > 191 && c1 < 224){
			c2 = str_data.charCodeAt(i + 1);
			tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
			i += 2;
		}else{
			c2 = str_data.charCodeAt(i + 1);
			c3 = str_data.charCodeAt(i + 2);
			tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			i += 3;
		}
	}

	return tmp_arr.join('');
}

