function insertReadmore(editor)
{
	var content = (new Function('return ' + Joomla.getOptions('xtd-readmore')))();

	if (content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i))
	{
		alert(Joomla.JText._('PLG_READMORE_ALREADY_EXISTS'));
	} else {
		jInsertEditorText('<hr id="system-readmore" />', editor);
	}
}
