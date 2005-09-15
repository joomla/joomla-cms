function TinyMCE_default_getEditorTemplate() {
	var template = new Array();

	template['html'] = '\
<table class="mceEditor" border="0" cellpadding="0" cellspacing="0" width="{$width}" height="{$height}">\
<tr><td align="center">\
<span id="{$editor_id}">IFRAME</span>\
</td></tr>\
<tr><td class="mceToolbar" align="center" height="1">\
<img id="{$editor_id}_bold" src="{$themeurl}/images/{$lang_bold_img}" title="{$lang_bold_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'Bold\')">\
<img id="{$editor_id}_italic" src="{$themeurl}/images/{$lang_italic_img}" title="{$lang_italic_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'Italic\')">\
<img id="{$editor_id}_underline" src="{$themeurl}/images/{$lang_underline_img}" title="{$lang_underline_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'Underline\')">\
<img id="{$editor_id}_strikethrough" src="{$themeurl}/images/strikethrough.gif" title="{$lang_striketrough_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'Strikethrough\')">\
<img src="{$themeurl}/images/spacer.gif" width="1" height="15" class="mceSeparatorLine">\
<img id="{$editor_id}_left" src="{$themeurl}/images/left.gif" title="{$lang_justifyleft_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'JustifyLeft\')">\
<img id="{$editor_id}_center" src="{$themeurl}/images/center.gif" title="{$lang_justifycenter_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'JustifyCenter\')">\
<img id="{$editor_id}_right" src="{$themeurl}/images/right.gif" title="{$lang_justifyright_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'JustifyRight\')">\
<img id="{$editor_id}_full" src="{$themeurl}/images/full.gif" title="{$lang_justifyfull_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'JustifyFull\')">\
<img src="{$themeurl}/images/spacer.gif" width="1" height="15" class="mceSeparatorLine">\
<img src="{$themeurl}/images/outdent.gif" title="{$lang_outdent_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'Outdent\')">\
<img src="{$themeurl}/images/indent.gif" title="{$lang_indent_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'Indent\')">\
<br>\
<img id="{$editor_id}_bullist" src="{$themeurl}/images/bullist.gif" title="{$lang_bullist_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'InsertUnorderedList\')">\
<img id="{$editor_id}_numlist" src="{$themeurl}/images/numlist.gif" title="{$lang_numlist_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'InsertOrderedList\')">\
<img src="{$themeurl}/images/spacer.gif" width="1" height="15" width="20" height="20" class="mceSeparatorLine">\
<img src="{$themeurl}/images/undo.gif" title="{$lang_undo_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'Undo\')">\
<img src="{$themeurl}/images/redo.gif" title="{$lang_redo_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'Redo\')">\
<img src="{$themeurl}/images/spacer.gif" width="1" height="15" class="mceSeparatorLine">\
<img id="{$editor_id}_link" src="{$themeurl}/images/link.gif" title="{$lang_link_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceLink\', true)">\
<img src="{$themeurl}/images/unlink.gif" title="{$lang_unlink_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'unlink\')">\
<img id="{$editor_id}_image" src="{$themeurl}/images/image.gif" title="{$lang_image_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceImage\', true)">\
<img src="{$themeurl}/images/cleanup.gif" title="{$lang_cleanup_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceCleanup\')">\
<img src="{$themeurl}/images/help.gif" title="{$lang_help_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceHelp\')">\
</td></tr>\
</table>';

	template['delta_width'] = 0;
	template['delta_height'] = -40;

	return template;
}

/**
 * Insert link template function.
 */
function TinyMCE_default_getInsertLinkTemplate() {
	var template = new Array();

	template['file'] = 'link.htm';
	template['width'] = 320;
	template['height'] = 200;

	// Language specific width and height addons
	template['width'] += tinyMCE.getLang('lang_insert_link_delta_width', 0);
	template['height'] += tinyMCE.getLang('lang_insert_link_delta_height', 0);

	return template;
}

/**
 * Insert image template function.
 */
function TinyMCE_default_getInsertImageTemplate() {
	var template = new Array();

	template['file'] = 'image.htm';
	template['width'] = 360;
	template['height'] = 200;

	// Language specific width and height addons
	template['width'] += tinyMCE.getLang('lang_insert_image_delta_width', 0);
	template['height'] += tinyMCE.getLang('lang_insert_image_delta_height', 0);

	return template;
}

function TinyMCE_default_handleNodeChange(editor_id, node) {
	// Reset old states
	tinyMCE.switchClassSticky(editor_id + '_left', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_right', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_center', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_full', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_bold', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_italic', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_underline', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_strikethrough', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_bullist', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_numlist', 'mceButtonNormal');

	// Handle align attributes
	alignNode = node;
	breakOut = false;
	do {
		if (!alignNode.getAttribute || !alignNode.getAttribute('align'))
			continue;

		switch (alignNode.getAttribute('align').toLowerCase()) {
			case "left":
				tinyMCE.switchClassSticky(editor_id + '_left', 'mceButtonSelected');
				breakOut = true;
			break;

			case "right":
				tinyMCE.switchClassSticky(editor_id + '_right', 'mceButtonSelected');
				breakOut = true;
			break;

			case "middle":
			case "center":
				tinyMCE.switchClassSticky(editor_id + '_center', 'mceButtonSelected');
				breakOut = true;
			break;

			case "justify":
				tinyMCE.switchClassSticky(editor_id + '_full', 'mceButtonSelected');
				breakOut = true;
			break;
		}
	} while (!breakOut && (alignNode = alignNode.parentNode));

	// Handle elements
	do {
		switch (node.nodeName.toLowerCase()) {
			case "b":
			case "strong":
				tinyMCE.switchClassSticky(editor_id + '_bold', 'mceButtonSelected');
			break;

			case "i":
			case "em":
				tinyMCE.switchClassSticky(editor_id + '_italic', 'mceButtonSelected');
			break;

			case "u":
				tinyMCE.switchClassSticky(editor_id + '_underline', 'mceButtonSelected');
			break;

			case "strike":
				tinyMCE.switchClassSticky(editor_id + '_strikethrough', 'mceButtonSelected');
			break;
			
			case "ul":
				tinyMCE.switchClassSticky(editor_id + '_bullist', 'mceButtonSelected');
			break;

			case "ol":
				tinyMCE.switchClassSticky(editor_id + '_numlist', 'mceButtonSelected');
			break;
		}
	} while ((node = node.parentNode));
}
