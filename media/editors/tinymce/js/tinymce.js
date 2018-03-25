/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(tinyMCE, Joomla, window, document){
	"use strict";

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
				var editor = editors[i].querySelector('textarea');
				this.setupEditor(editor, pluginOptions);
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
			// Check whether the editor already has ben set
			if (Joomla.editors.instances[element.id]) {
				return;
			}

			var name = element ? element.getAttribute('name').replace(/\[\]|\]/g, '').split('[').pop() : 'default', // Get Editor name
			    tinyMCEOptions = pluginOptions ? pluginOptions.tinyMCE || {} : {},
			    defaultOptions = tinyMCEOptions['default'] || {},
			    options = tinyMCEOptions[name] ? tinyMCEOptions[name] : defaultOptions; // Check specific options by the name

			// Avoid an unexpected changes, and copy the options object
			if (options.joomlaMergeDefaults) {
				options = Joomla.extend(Joomla.extend({}, defaultOptions), options);
			} else {
				options = Joomla.extend({}, options);
			}

			if (element) {
				// We already have the Target, so reset the selector and assign given element as target
				options.selector = null;
				options.target   = element;
			}

			var buttonValues = [];
			var arr = Object.keys(options.joomlaExtButtons.names).map(function (key) { return options.joomlaExtButtons.names[key]; });

			arr.forEach(function(name) {
				var tmp = {};
				tmp.text = name.name;
				tmp.icon = name.icon;

				if (name.href) {
					tmp.onclick = function() {
						var modal = document.getElementById(name.id + 'Modal');

						jQuery(modal).modal('show');
						Joomla.currentModal = modal;
					};
				} else {
					tmp.onclick = function () { new Function(name.click)(); };
				}

				buttonValues.push(tmp)
			});

			options.setup = function (editor) {
				editor.addButton('jxtdbuttons', {
					type   : 'menubutton',
					text   : Joomla.JText._('PLG_TINY_CORE_BUTTONS'),
					icon   : 'none icon-joomla',
					menu : buttonValues
				});
			};

			// Create a new instance
			var ed = new tinyMCE.Editor(element.id, options, tinymce.EditorManager);
			ed.render();

			/** Register the editor's instance to Joomla Object */
			Joomla.editors.instances[element.id] = {
				// Required by Joomla's API for the XTD-Buttons
				'getValue': function () { return this.instance.getContent(); },
				'setValue': function (text) { return this.instance.setContent(text); },
				'replaceSelection': function (text) { return this.instance.execCommand('mceInsertContent', false, text); },
				// Some extra instance dependent
				'id': element.id,
				'instance': ed,
				'onSave': function() { if (this.instance.isHidden()) { this.instance.show()}; return '';},
			};

			/** On save **/
			document.getElementById(ed.id).form.addEventListener('submit', function() {
				return Joomla.editors.instances[ed.targetElm.id].onSave();
			})
		}

	};

	Joomla.JoomlaTinyMCE = JoomlaTinyMCE;

	/**
	 * Initialize at an initial page load
	 */
	document.addEventListener('DOMContentLoaded', function () {
		Joomla.JoomlaTinyMCE.setupEditors(document);
	});

	/**
	 * Initialize when a part of the page was updated
	 */
	document.addEventListener("joomla:updated", function(event){
		Joomla.JoomlaTinyMCE.setupEditors(event.target);
	});

}(tinyMCE, Joomla, window, document));
