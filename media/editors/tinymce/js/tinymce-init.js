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

	/**
	 * Find all TinyMCE elements and initialize TinyMCE instance for each
	 */
	Joomla.setupEditorsTinyMCE = Joomla.setupEditorsTinyMCE || function(target){
		target = target || document;
		var pluginOptions = Joomla.optionsStorage.plg_editor_tinymce || {},
			$editors = $(target).find('.joomla-editor-tinymce');

		for(var i = 0, l = $editors.length; i < l; i++) {
			Joomla.initializeEditorTinyMCE($editors[i], pluginOptions);
		}
	}

	/**
	 * Initialize TinyMCE instance
	 */
	Joomla.initializeEditorTinyMCE = Joomla.initializeEditorTinyMCE || function (element, pluginOptions) {
		var name = element ? $(element).attr('name').replace(/\[\]|\]/g, '').split('[').pop() : 'default', // Get Editor name
			tinyMCEOptions = pluginOptions ? pluginOptions.tinyMCE || {} : {},
			defaultOptions = tinyMCEOptions['default'] || {},
			options = tinyMCEOptions[name] ? tinyMCEOptions[name] : defaultOptions; // Check specific options by the name

		// Avoid unexpected changes
		options = jQuery.extend({}, options);

		if (element) {
			options.selector = null;
			options.target   = element;
		}

		if (options.setupCallbacString && !options.setup) {
			options.setup = new Function('editor', options.setupCallbacString);
		}

		tinyMCE.init(options);
	}

	// Init on doomready
	$(document).ready(function(){
		Joomla.setupEditorsTinyMCE();

    	// Init in subform field
    	$(document).on('subform-row-add', function(event, row){
    		Joomla.setupEditorsTinyMCE(row);
    	})
	});

}(tinyMCE, Joomla, jQuery, window, document));
