/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(tinyMCE, Joomla, window, document){
	"use strict";

	// @deprecated 4.0 Use directly Joomla.editors.instances[editor].replaceSelection(text);
	window.jInsertEditorText = function ( text, editor ) {
		Joomla.editors.instances[editor].replaceSelection(text);
	};

	var JoomlaTinyMCE = {

		/**
		 * Find all TinyMCE elements and initialize TinyMCE instance for each
		 *
		 * @param {HTMLElement}  target  Target Element where to search for the editor element
		 *
		 * @since 3.7.0
		 */
		setupEditors: function ( target ) {
			target = target || document;
			var pluginOptions = Joomla.getOptions ? Joomla.getOptions('plg_editor_tinymce', {})
					:  (Joomla.optionsStorage.plg_editor_tinymce || {}),
				editors = target.querySelectorAll('.js-editor-tinymce');

			for(var i = 0, l = editors.length; i < l; i++) {
				var editor = document.querySelector('textarea');
				this.setupEditor(editor, pluginOptions);

				/** Register Editor */
				Joomla.editors.instances[editor.id] = {
					'id': editor.id,
					'getValue': function () { return tinyMCE.editors[this.id].getContent(); },
					'setValue': function (text) { return tinyMCE.editors[this.id].setContent(text); },
					'replaceSelection': function (text) { return tinyMCE.editors[this.id].execCommand('mceInsertContent', false, text); },
					'onSave': function() { if (tinyMCE.editors[this.id].isHidden()) { tinyMCE.editors[this.id].show()}; return '';}
				};

				/** On save **/
				editor.form.addEventListener('submit', function() {
					var editors = document.querySelectorAll('.js-editor-tinymce');
					for(var i = 0, l = editors.length; i < l; i++) {
						var editor = document.querySelector('textarea');
						if (tinyMCE.get(editor.id).isHidden()) {
							tinyMCE.get(editor.id).show();
						}
					}
				})
			}
		},

		/**
		 * Initialize TinyMCE editor instance
		 *
		 * @param {HTMLElement}  element
		 * @param {Object}       pluginOptions
		 *
		 * @since 3.7.0
		 */
		setupEditor: function ( element, pluginOptions ) {
			var name = element ? element.getAttribute('name').replace(/\[\]|\]/g, '').split('[').pop() : 'default', // Get Editor name
				tinyMCEOptions = pluginOptions ? pluginOptions.tinyMCE || {} : {},
				defaultOptions = tinyMCEOptions['default'] || {},
				options = tinyMCEOptions[name] ? tinyMCEOptions[name] : defaultOptions; // Check specific options by the name

			// Avoid unexpected changes
			options = Joomla.extend({}, options);

			if (element) {
				// We already have the Target, so reset the selector and assign given element as target
				options.selector = null;
				options.target   = element;
			}

			if (options.setupCallbackString && !options.setup) {
				options.setup = new Function('editor', options.setupCallbackString);
			}

			tinyMCE.init(options);
		}

	};

	Joomla.JoomlaTinyMCE = JoomlaTinyMCE;

	// Init on DOMContentLoaded
	document.addEventListener('DOMContentLoaded', function(){
		Joomla.JoomlaTinyMCE.setupEditors();

		// Init in subform field
		if(window.jQuery) {
			jQuery(document).on('subform-row-add', function(event, row){
				Joomla.JoomlaTinyMCE.setupEditors(row);
			})
		}
	});

}(tinyMCE, Joomla, window, document));
