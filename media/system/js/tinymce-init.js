/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(tinyMCE, Joomla, window, document){
	"use strict";

	// This line is for Mootools b/c
	window.getSize = window.getSize || function(){return {x: window.innerWidth, y: window.innerHeight};};

	window.jInsertEditorText = function ( text, editor ) {
		tinyMCE.activeEditor.execCommand('mceInsertContent', false, text);
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
				editors = target.querySelectorAll('.joomla-editor-tinymce');

			for(var i = 0, l = editors.length; i < l; i++) {
				this.setupEditor(editors[i], pluginOptions);
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

			// Check if control-s is enabled and map it
			if (window.parent.Joomla.getOptions('keySave')) {
				options.plugins = 'save';
				options.toolbar = 'save';
				options.save_onsavecallback = function() { window.parent.Joomla.submitbutton(window.parent.Joomla.getOptions('keySave').task) };
			}

			// Check if drag and drop is enabled
			if (window.parent.Joomla.getOptions('dnd-enabled')) {
				// Loads a plugin from an external URL
				tinymce.PluginManager.load('jdragdrop', window.parent.Joomla.getOptions('dnd-path'));
			}


			tinyMCE.init(options);
		}

	};

	Joomla.JoomlaTinyMCE = JoomlaTinyMCE;

	// Init on doomready
	document.addEventListener('DOMContentLoaded', function () {
		Joomla.JoomlaTinyMCE.setupEditors();

		// Init in subform field
		if(window.jQuery) {
			jQuery(document).on('subform-row-add', function (event, row) {
				Joomla.JoomlaTinyMCE.setupEditors(row);
			});
		}
	});

}(tinyMCE, Joomla, window, document));
