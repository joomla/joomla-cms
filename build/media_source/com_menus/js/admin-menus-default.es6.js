/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla) => {
  'use strict';

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

  Array.from(document.querySelectorAll('.modal')).forEach((modalEl) => {
    modalEl.addEventListener('hidden.bs.modal', () => {
      setTimeout(() => { window.parent.location.reload(); }, 1000);
    });
  });
})(Joomla);

((originalFn) => {
  'use strict';

  Joomla.submitform = (task, form) => {
    originalFn(task, form);
    if (task === 'menu.exportXml') {
      document.adminForm.task.value = '';
    }
  };
})(Joomla.submitform);
