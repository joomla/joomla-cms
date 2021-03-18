/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (Joomla) {
  'use strict';

  Joomla.submitbutton = function (task) {
    if (task === 'field.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
      if (window.opener && (task === 'field.save' || task === 'field.cancel')) {
        window.opener.document.closeEditWindow = window.self;
        window.opener.setTimeout('window.document.closeEditWindow.close()', 1000);
      }

      Joomla.submitform(task, document.getElementById('item-form'));
    }
  };
})(Joomla);