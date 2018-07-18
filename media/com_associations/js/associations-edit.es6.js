/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((Joomla, document) => {
	'use strict';

	Joomla.hideAssociation = (formControl, languageCode) => {
		const controlGroup = [].slice.call(document.querySelectorAll('#associations .control-group'));

		controlGroup.forEach((element) => {
			// Current selected language. Hide it
			const el = element.querySelector('.control-label label');

			if (el) {
				const attribute = el.getAttribute('for');

				if (attribute.replace('_id', '') === formControl + '_associations_' + languageCode.replace('-', '_')) {
					element.style.display = 'none';
				}
			}
		});
	};

	Joomla.showAssociationMessage = () => {
		const controlGroup = [].slice.call(document.querySelectorAll('#associations .control-group'));

		controlGroup.forEach((element) => {
			element.style.display = 'none';

			const associations = document.getElementById('associations');

			if (associations) {
				const html = document.createElement('div');
				html.classList.add('alert');
				html.classList.add('alert-info');
				html.id = 'associations-notice';
				html.innerHTML = Joomla.JText._('JGLOBAL_ASSOC_NOT_POSSIBLE');

				associations.insertAdjacentElement('afterbegin', html);
			}
		});
	};

	document.addEventListener('DOMContentLoaded', () => {
		const associationsEditOptions = Joomla.getOptions('system.associations.edit');
		const formControl = associationsEditOptions.formControl || 'jform';
		const formControlLanguage = document.getElementById(formControl + '_language');

		// Hide the associations tab if needed
		if (parseInt(associationsEditOptions.hidden) === 1) {
			Joomla.showAssociationMessage();
		} else {
			// Hide only the associations for the current language
			if (formControlLanguage) {
				Joomla.hideAssociation(formControl, formControlLanguage.value);
			}
		}

		// When changing the language
		if (formControlLanguage) {
			formControlLanguage.addEventListener('change', (event) => {
				// Remove message if any
				Joomla.removeMessages();

				let existsAssociations = false;

				// For each language, remove the associations, ie, empty the associations fields and reset the buttons to Select/Create
				const controlGroup = [].slice.call(document.querySelectorAll('#associations .control-group'));

				controlGroup.forEach((element) => {
					const attribute = element.querySelector('.control-label label').getAttribute('for');
					const languageCode = attribute.replace('_id', '').replace('jform_associations_', '');

					// Show the association fields
					element.style.display = 'block';

					// Check if there was an association selected for this language
					if (!existsAssociations && document.getElementById(formControl + '_associations_' + languageCode + '_id').value !== '') {
						existsAssociations = true;
					}

					// Call the modal clear button
					const clear = document.getElementById(formControl + '_associations_' + languageCode + '_clear');

					if (clear.onclick) {
						clear.onclick();
					} else if (clear.click) {
						clear.click();
					}
				});

				// If associations existed, send a warning to the user
				if (existsAssociations) {
					Joomla.renderMessages({warning: [Joomla.JText._('JGLOBAL_ASSOCIATIONS_RESET_WARNING')]});
				}

				// If the selected language is All hide the fields and add a message
				const selectedLanguage = event.target.value;

				if (selectedLanguage === '*') {
					Joomla.showAssociationMessage();
				} else {
					// Else show the associations fields/buttons and hide the current selected language
					Joomla.hideAssociation(formControl, selectedLanguage);
				}
			});
		}
	});
})(Joomla, document);
