if(hsJsHost != ''){

	var hsJsKey = (typeof hsJsKey === 'undefined') ? '' : hsJsKey;
	var hsJsHost = (typeof hsJsHost === 'undefined') ? '' : hsJsHost;
	
	if(hsJsKey != '' && hsJsHost != ''){
		
		var encoded_host = encode64(hsJsHost);
		var encoded_key = encode64(hsJsKey);
		
		var filePath = 'widgets/cars/ipanel-side/index.php?host='+encoded_host+'&key='+encoded_key;
		
		// setup the iframe target
		var iframe='<iframe id="frame" name="widget" src="#" width="230px" height="320px" marginheight="0" marginwidth="0" frameborder="no" scrolling="no"></iframe>';
		// write the iframe to the page
		document.write(iframe);
		 
		var myIframe = parent.document.getElementById("frame");
		// setup the width and height
		myIframe.width = 260;
		myIframe.height = 450;
		 
		myIframe.src = hsJsHost+filePath;
		// set the style of the iframe
		myIframe.style.border = "1px solid #aaa";
		myIframe.style.padding = "8px";			
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

