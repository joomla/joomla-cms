/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('directionality', 'en,sv,fr_ca,zh_cn');

function TinyMCE_directionality_getControlHTML(control_name) {
	var safariPatch = '" onclick="';

	if (tinyMCE.isSafari)
		safariPatch = "";

    switch (control_name) {
        case "ltr":
            return '<img id="{$editor_id}_ltr" src="{$pluginurl}/images/ltr.gif" title="{$lang_directionality_ltr_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');' + safariPatch + 'tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceDirectionLTR\');" />';

        case "rtl":
            return '<img id="{$editor_id}_rtl" src="{$pluginurl}/images/rtl.gif" title="{$lang_directionality_rtl_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');' + safariPatch + 'tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceDirectionRTL\');" />';
    }

    return "";
}

function TinyMCE_directionality_execCommand(editor_id, element, command, user_interface, value) {
	// Handle commands
	switch (command) {
		case "mceDirectionLTR":
			var inst = tinyMCE.getInstanceById(editor_id);
			var elm = tinyMCE.getParentElement(inst.getFocusElement(), "p,div,td,h1,h2,h3,h4,h5,h6,pre,address");

			if (elm)
				elm.setAttribute("dir", "ltr");

			tinyMCE.triggerNodeChange(false);
			return true;

		case "mceDirectionRTL":
			var inst = tinyMCE.getInstanceById(editor_id);
			var elm = tinyMCE.getParentElement(inst.getFocusElement(), "p,div,td,h1,h2,h3,h4,h5,h6,pre,address");

			if (elm)
				elm.setAttribute("dir", "rtl");

			tinyMCE.triggerNodeChange(false);
			return true;
	}

	// Pass to next handler in chain
	return false;
}

function TinyMCE_directionality_handleNodeChange(editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {
	function getAttrib(elm, name) {
		return elm.getAttribute(name) ? elm.getAttribute(name) : "";
	}

	tinyMCE.switchClassSticky(editor_id + '_ltr', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_rtl', 'mceButtonNormal');

	if (node == null)
		return;

	var elm = tinyMCE.getParentElement(node, "p,div,td,h1,h2,h3,h4,h5,h6,pre,address");
	if (!elm)
		return;

	var dir = getAttrib(elm, "dir");
	if (dir == "ltr" || dir == "")
		tinyMCE.switchClassSticky(editor_id + '_ltr', 'mceButtonSelected');
	else
		tinyMCE.switchClassSticky(editor_id + '_rtl', 'mceButtonSelected');

	return true;
}
