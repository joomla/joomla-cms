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
		var tinyMCEOptions = Joomla.optionsStorage.plg_editor_tinymce || {},
			$editors = $(target).find('.joomla-editor-tinymce');

		for(var i = 0, l = $editors.length; i < l; i++) {
			Joomla.initializeEditorTinyMCE($editors[i], tinyMCEOptions);
		}
	}

	/**
	 * Initialize TinyMCE instance
	 */
	Joomla.initializeEditorTinyMCE = Joomla.initializeEditorTinyMCE || function (element, tinyMCEOptions) {
		var options = tinyMCEOptions.tinyMCE || {};

		if (element) {
			options.selector = null;
			options.target   = element;
		}

		tinyMCE.init(options);
	}

	// Init on doomready
	$(document).ready(Joomla.setupEditorsTinyMCE);

	// Init in subform field
	$(document).on('subform-row-add', '.subform-repeatable', function(row){
		Joomla.setupEditorsTinyMCE(row);
	})

}(tinyMCE, Joomla, jQuery, window, document));
