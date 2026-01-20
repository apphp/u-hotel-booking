if(hsJsHost != ''){

	var hsJsKey = (typeof hsJsKey === 'undefined') ? '' : hsJsKey;
	var hsJsHost = (typeof hsJsHost === 'undefined') ? '' : hsJsHost;
	var hsHotelIDs = (typeof hsHotelIDs === 'undefined') ? '' : hsHotelIDs;
	var wgFullLayout = (typeof wgFullLayout === 'undefined') ? true : wgFullLayout;
	var wgLayoutType = (typeof wgLayoutType === 'undefined') ? 'layout-1' : wgLayoutType;
	var wgTypeSearchForm = (typeof wgTypeSearchForm === 'undefined' || wgTypeSearchForm != 'horizontal') ? 'vertical' : 'horizontal';
	var hsBkg = (typeof hsBkg === 'undefined') ? '' : hsBkg;
	var hsCss = (typeof hsCss === 'undefined') ? '' : hsCss;
	var hsLang = (typeof hsLang === 'undefined') ? '' : hsLang;
	
	if(hsJsKey != '' && hsJsHost != ''){
		
		var encoded_host = encode64(hsJsHost);
		var encoded_key = encode64(hsJsKey);
		var encoded_ids = hsHotelIDs != '' ? encode64(hsHotelIDs.replace(",","-").replace(" ","")) : '';
		var encoded_full = encode64(wgFullLayout);
		var encoded_layout = wgLayoutType != '' ? encode64(wgLayoutType) : '';
		var encoded_type = wgTypeSearchForm != '' ? encode64(wgTypeSearchForm) : '';
		var encoded_bkg = hsBkg != '' ? encode64(hsBkg) : '';
		var encoded_css = hsCss != '' ? encode64(hsCss) : '';
		var encoded_lang = hsLang != '' ? encode64(hsLang) : '';
		
		var filePath = 'widgets/hotels/ipanel-center/index.php?host='+encoded_host+'&key='+encoded_key+'&hids='+encoded_ids+'&full='+encoded_full+'&layout='+encoded_layout+'&type='+encoded_type+'&bkg='+encoded_bkg+'&css='+encoded_css+'&d_lang='+encoded_lang;
		
		// setup the iframe target
		var iframe='<iframe id="frame" name="widget-center-panel" style="background:#f6f6f6 url('+( hsBkg ? hsJsHost+'widgets/hotels/ipanel-center/template/images/background/'+hsBkg : '' )+') center center;background-attachment:fixed;background-size:cover;" src="#" width="100%" height="100%" marginheight="0" marginwidth="0" frameborder="no" scrolling="yes"></iframe>';
		// write the iframe to the page
		document.write(iframe);

        try{
			var myIframe = parent.document.getElementById("frame");
			// setup the width and height
			//myIframe.width = 800;
			//myIframe.height = 180;
			myIframe.src = hsJsHost+filePath;
			// set the style of the iframe
			//myIframe.style.border = "1px solid #aaa";
			myIframe.style.padding = "0px";
        }
        catch(exception){
            if (typeof(exception) == "string"){
                if (exception.charAt(0) == "!"){
                    console.log(exception.substr(1));
                    document.form1.txtNum1.focus();
                    document.form1.txtNum1.select();
                }
                else console.log(exception);
            }
            else console.log("The following error occurred: " + exception.message);
        }
	}
}

function encode64(input){
	var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";    

	input = escape(input);
    var output = "";
    var chr1, chr2, chr3 = "";
    var enc1, enc2, enc3, enc4 = "";
    var i = 0;

    do{
        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);

        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;

        if(isNaN(chr2)){
           enc3 = enc4 = 64;
        }else if(isNaN(chr3)){
           enc4 = 64;
        }

        output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4);
        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";
    } while (i < input.length);

    return output;
}
