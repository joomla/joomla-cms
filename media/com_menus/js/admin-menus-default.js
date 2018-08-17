/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (Joomla) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    if (Joomla.getOptions('menus-default')) {
      // eslint-disable-next-line prefer-destructuring
      var items = Joomla.getOptions('menus-default').items;

      items.forEach(function (item) {
        window['jSelectPosition_' + item] = function (name) {
          document.getElementById(item).value = name;
          Joomla.Modal.getCurrent().close();
        };
      });
    }

    Joomla.Modal.getCurrent().addEventListener('hidden.bs.modal', function () {
      setTimeout(function () {
        window.parent.location.reload();
      }, 1000);
    });
  });
})(Joomla);

(function (originalFn) {
  Joomla.submitform = function (task, form) {
    originalFn(task, form);
    if (task === 'menu.exportXml') {
      document.adminForm.task.value = '';
    }
  };
})(Joomla.submitform);
