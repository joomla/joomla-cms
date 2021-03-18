/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(Joomla => {
  'use strict';

  if (Joomla.getOptions('menus-default')) {
    // eslint-disable-next-line prefer-destructuring
    const items = Joomla.getOptions('menus-default').items;
    items.forEach(item => {
      window[`jSelectPosition_${item}`] = name => {
        document.getElementById(item).value = name;
        Joomla.Modal.getCurrent().close();
      };
    });
  } // @Todo make vanilla, when modals are custom elements


  window.jQuery('.modal').on('hidden.bs.modal', () => {
    setTimeout(() => {
      window.parent.location.reload();
    }, 1000);
  });
})(Joomla);

(originalFn => {
  'use strict';

  Joomla.submitform = (task, form) => {
    originalFn(task, form);

    if (task === 'menu.exportXml') {
      document.adminForm.task.value = '';
    }
  };
})(Joomla.submitform);