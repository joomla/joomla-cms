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

/**
 * Message listener
 * @param {MessageEvent} event
 */
const msgListener = function (event) {
  // Avoid cross origins
  if (event.origin !== window.location.origin) return;
  // Check message
  if (event.data.messageType === 'joomla:content-select' && event.data.contentType === 'com_modules.module') {
    // Close dialog
    this.close();
  }
};

// Listen when "edit module" dialog opens
document.addEventListener('joomla-dialog:open', ({ target }) => {
  if (!target.classList.contains('menus-dialog-module-editing')) return;
  // Create a listener with current dialog context
  const listener = msgListener.bind(target);

  // Wait for a message
  window.addEventListener('message', listener);

  // Reload page on close
  target.addEventListener('joomla-dialog:close', () => {
    window.removeEventListener('message', listener);

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
