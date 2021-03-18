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

  if (Joomla.getOptions('menus-default')) {
    // eslint-disable-next-line prefer-destructuring
    var items = Joomla.getOptions('menus-default').items;
    items.forEach(function (item) {
      window["jSelectPosition_".concat(item)] = function (name) {
        document.getElementById(item).value = name;
        Joomla.Modal.getCurrent().close();
      };
    });
  } // @Todo make vanilla, when modals are custom elements


  window.jQuery('.modal').on('hidden.bs.modal', function () {
    setTimeout(function () {
      window.parent.location.reload();
    }, 1000);
  });
})(Joomla);

(function (originalFn) {
  'use strict';

  Joomla.submitform = function (task, form) {
    originalFn(task, form);

    if (task === 'menu.exportXml') {
      document.adminForm.task.value = '';
    }
  };
})(Joomla.submitform);