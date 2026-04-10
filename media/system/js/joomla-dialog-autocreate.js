import JoomlaDialog from 'joomla.dialog';

/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Auto create a popup dynamically on click, eg:
 *
 * <button type="button" data-joomla-dialog='{"popupType": "iframe", "src": "content/url.html"}'>Click</button>
 * <button type="button" data-joomla-dialog='{"popupType": "inline", "popupContent": "#id-of-content-element"}'>Click</button>
 * <a href="content/url.html" data-joomla-dialog>Click</a>
 */
const delegateSelector = '[data-joomla-dialog]';
const configDataAttr = 'joomlaDialog';
const configCacheFlag = 'joomlaDialogCache';
document.addEventListener('click', event => {
  const triggerEl = event.target.closest(delegateSelector);
  if (!triggerEl) return;
  event.preventDefault();

  // Check for cached instance
  const cacheable = !!triggerEl.dataset[configCacheFlag];
  if (cacheable && triggerEl.JoomlaDialogInstance) {
    Joomla.Modal.setCurrent(triggerEl.JoomlaDialogInstance);
    triggerEl.JoomlaDialogInstance.show();
    return;
  }
  // Parse config
  const config = triggerEl.dataset[configDataAttr] ? JSON.parse(triggerEl.dataset[configDataAttr]) : {};

  // Check if the click is on anchor
  if (triggerEl.nodeName === 'A') {
    if (!config.popupType) {
      config.popupType = triggerEl.hash ? 'inline' : 'iframe';
    }
    if (!config.src && config.popupType === 'iframe') {
      config.src = triggerEl.href;
    } else if (!config.src && config.popupType === 'inline') {
      config.src = triggerEl.hash;
    }
  }

  // Template not allowed here
  delete config.popupTemplate;
  const popup = new JoomlaDialog(config);
  if (cacheable) {
    triggerEl.JoomlaDialogInstance = popup;
  }

  // Perform close when received any message
  if ('closeOnMessage' in triggerEl.dataset) {
    window.addEventListener('message', message => {
      // Close when source Window match the iframe Window (for iframe) or current Window (for other popups)
      if (message.source === (popup.getBodyContent().contentWindow || window)) {
        popup.close();
      }
    });
  }

  // Perform post-close actions
  popup.addEventListener('joomla-dialog:close', () => {
    // Clean up after close
    Joomla.Modal.setCurrent(null);
    if (!cacheable) {
      popup.destroy();
    }

    // Perform checkin request and page reload after close when needed
    const {
      checkinUrl
    } = triggerEl.dataset;
    const reloadOnClose = 'reloadOnClose' in triggerEl.dataset;
    if (checkinUrl) {
      Joomla.request({
        url: checkinUrl,
        method: 'POST',
        promise: true
      }).then(() => {
        if (reloadOnClose) {
          window.location.reload();
        }
      });
    } else if (reloadOnClose) {
      window.location.reload();
    }
  });

  // Show the popup
  popup.JoomlaDialogTrigger = triggerEl;
  Joomla.Modal.setCurrent(popup);
  popup.show();
});
