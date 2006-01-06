/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('fullscreen', 'en,sv,cs,fr_ca,zh_cn,da,he,nb,de,hu,ru,ru_KOI8-R,ru_UTF-8,nn,es,cy,is,pl,nl,fr,pt_br');

function TinyMCE_fullscreen_getInfo() {
	return {
		longname : 'Fullscreen',
		author : 'Moxiecode Systems',
		authorurl : 'http://tinymce.moxiecode.com',
		infourl : 'http://tinymce.moxiecode.com/tinymce/docs/plugin_fullscreen.html',
		version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
	};
};

function TinyMCE_fullscreen_getControlHTML(control_name) {
	switch (control_name) {
		case "fullscreen":
			var cmd = 'tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceFullScreen\');return false;';
			return '<a href="javascript:' + cmd + '" onclick="' + cmd + '" onmousedown="return false;"><img id="{$editor_id}_fullscreen" src="{$pluginurl}/images/fullscreen.gif" title="{$lang_fullscreen_desc}" width="20" height="20" class="mceButton' + (tinyMCE.getParam('fullscreen_is_enabled') ? 'Selected' : 'Normal') + '" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');" /></a>';
	}

	return "";
}

function TinyMCE_fullscreen_execCommand(editor_id, element, command, user_interface, value) {
	// Handle commands
	switch (command) {
		case "mceFullScreen":
			if (tinyMCE.getParam('fullscreen_is_enabled')) {
				// In fullscreen mode
				window.opener.tinyMCE.execInstanceCommand(tinyMCE.getParam('fullscreen_editor_id'), 'mceSetContent', false, tinyMCE.getContent(editor_id));
				top.close();
			} else {
				tinyMCE.setWindowArg('editor_id', editor_id);

				var win = window.open(tinyMCE.baseURL + "/plugins/fullscreen/fullscreen.htm", "mceFullScreenPopup", "fullscreen=yes,menubar=no,toolbar=no,scrollbars=no,resizable=yes,left=0,top=0,width=" + screen.availWidth + ",height="  + screen.availHeight);
			}
	
			return true;
	}

	// Pass to next handler in chain
	return false;
}


