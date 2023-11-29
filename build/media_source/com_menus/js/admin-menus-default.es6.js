/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (Joomla.getOptions('menus-default')) {
  const items = Joomla.getOptions('menus-default', {}).items || [];

  items.forEach((item) => {
    window[`jSelectPosition_${item}`] = (name) => {
      document.getElementById(item).value = name;
      Joomla.Modal.getCurrent().close();
    };
  });
}

const originalFn = Joomla.submitform;
Joomla.submitform = (task, form) => {
  originalFn(task, form);
  if (task === 'menu.exportXml') {
    document.adminForm.task.value = '';
  }
};

// Listen when "edit module" dialog opens
document.addEventListener('joomla-dialog:open', ({ target }) => {
  if (!target.classList.contains('menus-dialog-module-editing')) return;

  // Reload page on close
  target.addEventListener('joomla-dialog:close', () => {
    // Perform checkin
    const { checkinUrl } = target.JoomlaDialogTrigger.dataset;
    if (checkinUrl) {
      Joomla.request({ url: checkinUrl, method: 'POST', promise: true }).then(() => {
        window.location.reload();
      });
    } else {
      window.location.reload();
    }
  });
});
