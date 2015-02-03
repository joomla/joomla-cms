function isBrowserIE()
{
	return navigator.appName=="Microsoft Internet Explorer";
}

function jInsertEditorText( text, editor )
{
	tinyMCE.execCommand('mceInsertContent', false, text);
}

var global_ie_bookmark = false;

function IeCursorFix()
{
	if (isBrowserIE())
	{
		tinyMCE.execCommand('mceInsertContent', false, '');
		global_ie_bookmark = tinyMCE.activeEditor.selection.getBookmark(false);
	}
	return true;
}