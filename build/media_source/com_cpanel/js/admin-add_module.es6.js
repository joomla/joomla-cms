/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Message listener
 * @param {MessageEvent} event
 */
const msgListener = function (event) {
  // Avoid cross origins
  if (event.origin !== window.location.origin) return;
  // Check message
  if (event.data.contentType === 'com_modules.module') {
    // Reload the page when module has been created
    if (event.data.id) {
      setTimeout(() => { window.location.reload(); }, 500);
    }
    // Close dialog
    this.close();
  }
};

// Listen when "add module" dialog opens, and add message listener
document.addEventListener('joomla-dialog:open', ({ target }) => {
  if (!target.classList.contains('cpanel-dialog-module-editing')) return;
  // Prevent admin-module-edit.js closing it
  // @TODO: This can be removed when all modals for module editing  will use Dialog
  Joomla.Modal.setCurrent(null);

  // Create a listener with current dialog context
  const listener = msgListener.bind(target);

  // Wait for a message
  window.addEventListener('message', listener);

  // Remove listener on close
  target.addEventListener('joomla-dialog:close', () => {
    window.removeEventListener('message', listener);
  });
});
