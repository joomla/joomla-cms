/**
 * plugin.js
 *
 * Released under LGPL License.
 * Copyright (c) 1999-2015 Ephox Corp. All rights reserved
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/* Ported for Joomla by Dimitris Grammatikogiannis */

tinymce.PluginManager.add('pagebreak', function(editor) {

	var pageBreakPlaceHolderHtml = '<hr id="system-readmore"/>';

	// Register commands
	editor.addCommand('mcePageBreak', function() {

		if (!editor.getContent().match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)) {
			if (editor.settings.pagebreak_split_block) {
				editor.insertContent('<p>' + pageBreakPlaceHolderHtml + '</p>');
			} else {
				editor.insertContent(pageBreakPlaceHolderHtml);
			}
		} else {
			alert(Joomla.JText._('PLG_TINY_PAGEBREAK_ERROR'), false);
			return false;
		}
	});

	// Register buttons
	editor.addButton('pagebreak', {
		title: 'Page break',
		cmd: 'mcePageBreak'
	});

	editor.addMenuItem('pagebreak', {
		text: 'Page break',
		icon: 'pagebreak',
		cmd: 'mcePageBreak',
		context: 'insert'
	});

	editor.on('ResolveName', function(e) {
		if (e.target.nodeName == 'HR' && e.target.id === 'system-readmore') {
			e.name = 'pagebreak';
		}
	});
});
