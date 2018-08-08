/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    if (Joomla.getOptions('menus-default')) {
      // eslint-disable-next-line prefer-destructuring
      const items = Joomla.getOptions('menus-default').items;

      items.forEach((item) => {
        window[`jSelectPosition_${item}`] = (name) => {
          document.getElementById(item).value = name;
          Joomla.Modal.getCurrent().close();
        };
      });
    }

    Joomla.Modal.getCurrent().addEventListener('hidden.bs.modal', () => {
      setTimeout(() => { window.parent.location.reload(); }, 1000);
    });
  });
})(Joomla);

((originalFn) => {
  Joomla.submitform = (task, form) => {
    originalFn(task, form);
    if (task === 'menu.exportXml') {
      document.adminForm.task.value = '';
    }
  };
})(Joomla.submitform);
