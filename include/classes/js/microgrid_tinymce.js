////////////////////////////////////////////
// LAST MODIFIED: 07.04.2013
// -----------------------------------------
// - [31.12.2012] : added mode 2 + imageupload plugin
// - [23.10.2012] : added entity_encoding : 'raw',
// - [19.01.2012] : allowed IFRAMES in Advanced Mode
// - [21.07.2011] : added more oprions to basic template
// - [31.05.2011] : added tinyMCE.settings['height'] = height;
//
////////////////////////////////////////////

// ------------------------------------------------------------------------------------
// tinyMCE
// #skin : 'o2k7',
// #skin_variant : 'silver',
// ------------------------------------------------------------------------------------
tinyMCE.init({
    // General options
    mode : '',
    theme : 'advanced',
    language : 'en',
    width: '600px',
    height: '200px',
    entity_encoding : 'raw',
    theme_advanced_layout_manager : 'SimpleLayout',
    theme_advanced_toolbar_location : 'top',
    theme_advanced_toolbar_align : 'left',
    theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,code,|',
    theme_advanced_buttons2 : '',
    theme_advanced_buttons3 : ''
});

var tinymceConfigs = [ {
    mode : 'textareas',
    theme : 'advanced',
    language : 'en',
    width: '600px',
    height: '200px',
    entity_encoding : 'raw',
    theme_advanced_layout_manager : 'SimpleLayout',
    theme_advanced_toolbar_location : 'top',
    theme_advanced_toolbar_align : 'left',
    theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,code,|',
    theme_advanced_buttons2 : '',
    theme_advanced_buttons3 : ''
},
{
    // General options
    mode : 'textareas',
    theme : 'advanced',
    editor_selector : 'mceAdvanced',
    width: '600px',
    height: '200px',
    entity_encoding : 'raw',
    language:'en',
    plugins : 'imageupload,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave',

    // Theme options
    theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect,|,fullscreen,help,|,print,',
    theme_advanced_buttons2 : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,imageupload,cleanup,|,code,|,insertdate,inserttime,preview,|,forecolor,backcolor,',
    theme_advanced_buttons3 : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,advhr,|,visualchars,nonbreaking,blockquote,pagebreak,|,ltr,rtl,',
    theme_advanced_toolbar_location : 'top',
    theme_advanced_toolbar_align : 'left',
    theme_advanced_statusbar_location : 'bottom',
    theme_advanced_resizing : true,

    // Example word content CSS (should be your site CSS) this one removes paragraph margins
    // content_css : 'css/word.css',

    // Drop lists for link/image/media/template dialogs
    template_external_list_url : 'lists/template_list.js',
    external_link_list_url : 'lists/link_list.js',
    external_image_list_url : 'lists/image_list.js',
    media_external_list_url : 'lists/media_list.js',

    // Extended elements
    extended_valid_elements : "iframe[src|width|height|name|align]"
}];

function toggleEditor(mode, id, height){
    var height_ = (height != null) ? height : "200px";
    if(mode == '0'){        
        tinyMCE.execCommand('mceRemoveControl', true, id);
        tinyMCE.settings = tinymceConfigs[0];
        tinyMCE.settings['height'] = height_;
        tinyMCE.execCommand('mceAddControl', true, id);
        if(document.getElementById('lnk_0_'+id)) document.getElementById('lnk_0_'+id).style.display = 'none';
        if(document.getElementById('lnk_1_'+id)) document.getElementById('lnk_1_'+id).style.display = 'inline';
    }else{
        tinyMCE.execCommand('mceRemoveControl', true, id);
        tinyMCE.settings = tinymceConfigs[1];
        if(mode == '2'){
            tinyMCE.settings['height'] = '400px';
            tinyMCE.settings['width'] = '99%';
        }else{
            tinyMCE.settings['height'] = height_;    
        }        
        tinyMCE.execCommand('mceAddControl', true, id);
        if(document.getElementById('lnk_0_'+id)) document.getElementById('lnk_0_'+id).style.display = 'inline';
        if(document.getElementById('lnk_1_'+id)) document.getElementById('lnk_1_'+id).style.display = 'none';         
    }
    __mgSetCookie("wysiwyg_"+id+"_mode", mode);
}

function disableEditor(mode, id){
    tinyMCE.execCommand('mceRemoveControl', true, id);
}
