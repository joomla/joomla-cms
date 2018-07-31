/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (Joomla, document) {
  'use strict';

  Joomla.hideAssociation = function (formControl, languageCode) {
    var controlGroup = [].slice.call(document.querySelectorAll('#associations .control-group'));

    controlGroup.forEach(function (element) {
      // Current selected language. Hide it
      var el = element.querySelector('.control-label label');

      if (el) {
        var attribute = el.getAttribute('for');

        if (attribute.replace('_id', '') === formControl + '_associations_' + languageCode.replace('-', '_')) {
          element.style.display = 'none';
        }
      }
    });
  };

  Joomla.showAssociationMessage = function () {
    var controlGroup = [].slice.call(document.querySelectorAll('#associations .control-group'));

    controlGroup.forEach(function (element) {
      element.style.display = 'none';

      var associations = document.getElementById('associations');

      if (associations) {
        var html = document.createElement('div');
        html.classList.add('alert');
        html.classList.add('alert-info');
        html.id = 'associations-notice';
        html.innerHTML = Joomla.JText._('JGLOBAL_ASSOC_NOT_POSSIBLE');

        associations.insertAdjacentElement('afterbegin', html);
      }
    });
  };

  document.addEventListener('DOMContentLoaded', function () {
    var associationsEditOptions = Joomla.getOptions('system.associations.edit');
    var formControl = associationsEditOptions.formControl || 'jform';
    var formControlLanguage = document.getElementById(formControl + '_language');

    // Hide the associations tab if needed
    if (parseInt(associationsEditOptions.hidden, 10) === 1) {
      Joomla.showAssociationMessage();
    } else if (formControlLanguage) {
      // Hide only the associations for the current language
      Joomla.hideAssociation(formControl, formControlLanguage.value);
    }

    // When changing the language
    if (formControlLanguage) {
      formControlLanguage.addEventListener('change', function (event) {
        // Remove message if any
        Joomla.removeMessages();

        var existsAssociations = false;

        /** For each language, remove the associations, ie,
         *  empty the associations fields and reset the buttons to Select/Create
         */
        var controlGroup = [].slice.call(document.querySelectorAll('#associations .control-group'));

        controlGroup.forEach(function (element) {
          var attribute = element.querySelector('.control-label label').getAttribute('for');
          var languageCode = attribute.replace('_id', '').replace('jform_associations_', '');

          // Show the association fields
          element.style.display = 'block';

          // Check if there was an association selected for this language
          if (!existsAssociations && document.getElementById(formControl + '_associations_' + languageCode + '_id').value !== '') {
            existsAssociations = true;
          }

          // Call the modal clear button
          var clear = document.getElementById(formControl + '_associations_' + languageCode + '_clear');

          if (clear.onclick) {
            clear.onclick();
          } else if (clear.click) {
            clear.click();
          }
        });

        // If associations existed, send a warning to the user
        if (existsAssociations) {
          Joomla.renderMessages({ warning: [Joomla.JText._('JGLOBAL_ASSOCIATIONS_RESET_WARNING')] });
        }

        // If the selected language is All hide the fields and add a message
        var selectedLanguage = event.target.value;

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
