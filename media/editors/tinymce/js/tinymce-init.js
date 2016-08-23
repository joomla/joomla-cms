/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(tinyMCE, Joomla, $, window, document){
	"use strict";

	// This line is for Mootools b/c
	window.getSize = window.getSize || function(){return {x: $(window).width(), y: $(window).height()};};

	window.jInsertEditorText = function ( text, editor ) {
		tinyMCE.activeEditor.execCommand('mceInsertContent', false, text);
	}

	var JoomlaTinyMCE = {

		/**
		 * Find all TinyMCE elements and initialize TinyMCE instance for each
		 *
		 * @param {HTMLElement}  target  Target Element where to search for the editor element
		 *
		 * @since __DEPLOY_VERSION__
         */
		setupEditors: function ( target ) {
			target = target || document;
			var pluginOptions = Joomla.getOptions ? Joomla.getOptions('plg_editor_tinymce', {})
					:  (Joomla.optionsStorage.plg_editor_tinymce || {}),
				$editors = $(target).find('.joomla-editor-tinymce');

			for(var i = 0, l = $editors.length; i < l; i++) {
				this.setupEditor($editors[i], pluginOptions);
			}
		},

		/**
		 * Initialize TinyMCE editor instance
		 *
		 * @param {HTMLElement}  element
		 * @param {Object}       pluginOptions
		 *
		 * @since __DEPLOY_VERSION__
         */
		setupEditor: function ( element, pluginOptions ) {
			var name = element ? $(element).attr('name').replace(/\[\]|\]/g, '').split('[').pop() : 'default', // Get Editor name
				tinyMCEOptions = pluginOptions ? pluginOptions.tinyMCE || {} : {},
				defaultOptions = tinyMCEOptions['default'] || {},
				options = tinyMCEOptions[name] ? tinyMCEOptions[name] : defaultOptions; // Check specific options by the name

			// Avoid unexpected changes
			options = jQuery.extend({}, options);

			if (element) {
				// We already have the Target, so reset the selector and assign given element as target
				options.selector = null;
				options.target   = element;
			}

			if (options.setupCallbacString && !options.setup) {
				options.setup = new Function('editor', options.setupCallbacString);
			}

			tinyMCE.init(options);
		}

	};

	Joomla.JoomlaTinyMCE = JoomlaTinyMCE;

	// Init on doomready
	$(document).ready(function(){
		Joomla.JoomlaTinyMCE.setupEditors();

    	// Init in subform field
    	$(document).on('subform-row-add', function(event, row){
			Joomla.JoomlaTinyMCE.setupEditors(row);
    	})
	});

}(tinyMCE, Joomla, jQuery, window, document));
