/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


(function (window, Joomla) {
	"use strict";

	jQuery(document).ready(function() {
		var cm = CodeMirror, $ = jQuery;

		// @deprecated 4.0 Use directly Joomla.editors.instances[editor].replaceSelection(text);
		window.jInsertEditorText = function(text, editor) {
			Joomla.editors.instances[editor].replaceSelection(text);
		};

		window.toggleFullScreen = function(cm) {
			cm.setOption("fullScreen", !cm.getOption("fullScreen"));
		};
		window.closeFullScreen = function(cm) {
			cm.getOption("fullScreen") && cm.setOption("fullScreen", false);
		};
		window.makeMarker = function() {
			var marker = document.createElement("div");
			marker.className = "CodeMirror-markergutter-mark";
			return marker;
		};

		var options = Joomla.getOptions('js-editors-cm');
		if (options) {
			cm.keyMap.default["Ctrl-Q"] = toggleFullScreen;
			cm.keyMap.default[options.fsCombo] = toggleFullScreen;
			cm.keyMap.default["Esc"] = closeFullScreen;
			// For mode autoloading.
			cm.modeURL = options.modPath;
			// Fire this function any time an editor is created.
			cm.defineInitHook(function (editor)
			{
				// Load the editor mode (typically 'htmlmixed').
				cm.autoLoadMode(editor, editor.options.mode);
				// Handle gutter clicks (place or remove a marker).
				editor.on("gutterClick", function (ed, n, gutter) {
					if (gutter != "CodeMirror-markergutter") { return; }
					var info = ed.lineInfo(n),
						hasMarker = !!info.gutterMarkers && !!info.gutterMarkers["CodeMirror-markergutter"];
					ed.setGutterMarker(n, "CodeMirror-markergutter", hasMarker ? null : makeMarker());
				});
				// jQuery's ready function.
				$(function () {
					// Some browsers do something weird with the fieldset which doesn't work well with CodeMirror. Fix it.
					$(editor.getTextArea()).parent('fieldset').css('min-width', 0);
					// Listen for Bootstrap's 'shown' event. If this editor was in a hidden element when created, it may need to be refreshed.
					$(document.body).on("shown shown.bs.tab shown.bs.modal", function () { editor.refresh(); });
				});
			});
		}

		var editors = document.querySelectorAll('.js-editor-cm');

		for(var i = 0, l = editors.length; i < l; i++) {
			var editor = editors[i].getElementsByTagName('textarea')[0];
			var newOptions = Joomla.extend({'field': editor}, options.options);

			/** Register Editor */
			var instance = CodeMirror.fromTextArea(editor, newOptions);
			Joomla.editors.instances[editor.id] = instance;
			//Joomla.editors.instances[editor.id].setValue(editor.value);

			/** On save **/
			instance.options.field.form.addEventListener('submit', function() {
				instance.options.field.value = instance.getValue();
			});
		}
	});
}(window, Joomla));
