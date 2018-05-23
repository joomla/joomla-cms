/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Edit Associations javascript behavior
 *
 * Used for editing associations in the backend.
 *
 * @package  Joomla
 * @since    3.7.0
 */

Joomla = window.Joomla || {};

(function(Joomla, document) {
	'use strict';

	Joomla.hideAssociation = function(formControl, languageCode)
	{
		var controlGroup = document.querySelectorAll('#associations .control-group');

		for (var i = 0, l = controlGroup.length; i < l; i++) {

			// Current selected language. Hide it
			var attribute = controlGroup[i].querySelector('.control-label label').getAttribute('for');

			if (attribute.replace('_id', '') == formControl + '_associations_' + languageCode.replace('-', '_')) {
				controlGroup[i].style.display = 'none';
			}
		}
	}

	Joomla.showAssociationMessage = function()
	{
		var controlGroup = document.querySelectorAll('#associations .control-group');

		for (var i = 0, l = controlGroup.length; i < l; i++) {
			controlGroup[i].style.display = 'none';

			var associations = document.getElementById('associations');

			if (associations) {
				var html = document.createElement('div');
				html.classList.add('alert')
				html.classList.add('alert-info')
				html.id = 'associations-notice';
				html.innerHTML = Joomla.JText._('JGLOBAL_ASSOC_NOT_POSSIBLE');

				associations.insertAdjacentElement('afterbegin', html);
			}
		}
	}


	document.addEventListener('DOMContentLoaded', function() {

		var associationsEditOptions = Joomla.getOptions('system.associations.edit'), formControl = associationsEditOptions.formControl || 'jform',
		    formControlLanguage     = document.getElementById(formControl + '_language');

		// Hide the associations tab if needed
		if (associationsEditOptions.hidden == 1)
		{
			Joomla.showAssociationMessage();
		}
		// Hide only the associations for the current language
		else {
			if (formControlLanguage) {
				Joomla.hideAssociation(formControl, formControlLanguage.value);
			}
		}

		// When changing the language
		if (formControlLanguage) {
			formControlLanguage.addEventListener('change', function (event) {

				// Remove message if any
				Joomla.removeMessages();

				var existsAssociations = false;

				// For each language, remove the associations, ie, empty the associations fields and reset the buttons to Select/Create
				var controlGroup = document.querySelectorAll('#associations .control-group');

				for (var i = 0, l = controlGroup.length; i < l; i++) {
					var attribute    = controlGroup[i].querySelector('.control-label label').getAttribute('for'),
					    languageCode = attribute.replace('_id', '').replace('jform_associations_', '');

					// Show the association fields
					controlGroup[i].style.display = 'block';

					// Check if there was an association selected for this language
					if (!existsAssociations && document.getElementById(formControl + '_associations_' + languageCode + '_id').value !== '')
					{
						existsAssociations = true;
					}

					// Call the modal clear button
					var clear = document.getElementById(formControl + '_associations_' + languageCode + '_clear');

					if (clear.onclick) {
						clear.onclick();
					} else if (clear.click) {
						clear.click();
					}
				}

				// If associations existed, send a warning to the user
				if (existsAssociations)
				{
					Joomla.renderMessages({warning: [Joomla.JText._('JGLOBAL_ASSOCIATIONS_RESET_WARNING')]});
				}

				// If the selected language is All hide the fields and add a message
				var selectedLanguage = event.target.value;
				if (selectedLanguage == '*')
				{
					Joomla.showAssociationMessage();
				}
				// Else show the associations fields/buttons and hide the current selected language
				else
				{
					Joomla.hideAssociation(formControl, selectedLanguage);
				}
			});
		}

	});

})(Joomla, document);
