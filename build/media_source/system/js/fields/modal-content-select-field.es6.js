/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'joomla.dialog';

/**
 * Helper method to set values on the fields, and trigger "change" event
 *
 * @param {object} data
 * @param {HTMLInputElement} inputValue
 * @param {HTMLInputElement} inputTitle
 */
const setValues = (data, inputValue, inputTitle) => {
  const value = `${data.id || data.value || ''}`;
  const isChanged = inputValue.value !== value;
  inputValue.value = value;
  if (inputTitle) {
    inputTitle.value = data.title || inputValue.value;
  }
  if (isChanged) {
    inputValue.dispatchEvent(new CustomEvent('change', { bubbles: true, cancelable: true }));
  }
};

/**
 * Show Select dialog
 *
 * @param {HTMLInputElement} inputValue
 * @param {HTMLInputElement} inputTitle
 * @param {Object} dialogConfig
 * @returns {Promise}
 */
const doSelect = (inputValue, inputTitle, dialogConfig) => {
  // Use a JoomlaExpectingPostMessage flag to be able to distinct legacy methods
  // @TODO: This should be removed after full transition to postMessage()
  window.JoomlaExpectingPostMessage = true;
  // Create and show the dialog
  const dialog = new JoomlaDialog(dialogConfig);
  dialog.classList.add('joomla-dialog-content-select-field');
  dialog.show();

  return new Promise((resolve) => {
    const msgListener = (event) => {
      // Avoid cross origins
      if (event.origin !== window.location.origin) return;
      // Check message type
      if (event.data.messageType === 'joomla:content-select') {
        setValues(event.data, inputValue, inputTitle);
        dialog.close();
      } else if (event.data.messageType === 'joomla:cancel') {
        dialog.close();
      }
    };

    // Clear all when dialog is closed
    dialog.addEventListener('joomla-dialog:close', () => {
      delete window.JoomlaExpectingPostMessage;
      window.removeEventListener('message', msgListener);
      dialog.destroy();
      resolve();
    });

    // Wait for message
    window.addEventListener('message', msgListener);
  });
};

/**
 * Update view, depending if value is selected or not
 *
 * @param {HTMLInputElement} inputValue
 * @param {HTMLElement} container
 */
const updateView = (inputValue, container) => {
  const hasValue = !!inputValue.value;
  container.querySelectorAll('[data-show-when-value]').forEach((el) => {
    if (el.dataset.showWhenValue) {
      // eslint-disable-next-line no-unused-expressions
      hasValue ? el.removeAttribute('hidden') : el.setAttribute('hidden', '');
    } else {
      // eslint-disable-next-line no-unused-expressions
      hasValue ? el.setAttribute('hidden', '') : el.removeAttribute('hidden');
    }
  });
};

/**
 * Initialise the field
 * @param {HTMLElement} container
 */
const setupField = (container) => {
  const inputValue = container ? container.querySelector('.js-input-value') : null;
  const inputTitle = container ? container.querySelector('.js-input-title') : null;

  if (!container || !inputValue) {
    throw new Error('Incomplete markup of Content dialog field');
  }

  container.addEventListener('change', () => {
    updateView(inputValue, container);
  });

  // Bind the buttons
  container.addEventListener('click', (event) => {
    const button = event.target.closest('[data-button-action]');
    if (!button) return;
    event.preventDefault();

    // Extract the data
    const action = button.dataset.buttonAction;
    const dialogConfig = button.dataset.modalConfig ? JSON.parse(button.dataset.modalConfig) : {};
    const keyName = container.dataset.keyName || 'id';
    const token = Joomla.getOptions('csrf.token', '');

    // Handle requested action
    let handle;
    switch (action) {
      case 'select':
      case 'create': {
        const url = dialogConfig.src.indexOf('http') === 0 ? new URL(dialogConfig.src) : new URL(dialogConfig.src, window.location.origin);
        url.searchParams.set(token, '1');
        dialogConfig.src = url.toString();
        handle = doSelect(inputValue, inputTitle, dialogConfig);
        break;
      }
      case 'edit': {
        // Update current value in the URL
        const url = dialogConfig.src.indexOf('http') === 0 ? new URL(dialogConfig.src) : new URL(dialogConfig.src, window.location.origin);
        url.searchParams.set(keyName, inputValue.value);
        url.searchParams.set(token, '1');
        dialogConfig.src = url.toString();

        handle = doSelect(inputValue, inputTitle, dialogConfig);
        break;
      }
      case 'clear':
        handle = (async () => setValues({ id: '', title: '' }, inputValue, inputTitle))();
        break;
      default:
        throw new Error(`Unknown action ${action} for Modal select field`);
    }

    handle.then(() => {
      // Perform checkin when needed
      if (button.dataset.checkinUrl) {
        const chckUrl = button.dataset.checkinUrl;
        const url = chckUrl.indexOf('http') === 0 ? new URL(chckUrl) : new URL(chckUrl, window.location.origin);
        // Add value to request
        url.searchParams.set(keyName, inputValue.value);
        url.searchParams.set('cid[]', inputValue.value);
        // Also add value to POST, because Controller may expect it from there
        const data = new FormData();
        data.append('id', inputValue.value);
        data.append('cid[]', inputValue.value);

        Joomla.request({
          url: url.toString(), method: 'POST', promise: true, data,
        });
      }
    });
  });
};

const setup = (container) => {
  container.querySelectorAll('.js-modal-content-select-field').forEach((el) => setupField(el));
};

document.addEventListener('DOMContentLoaded', () => setup(document));
document.addEventListener('joomla:updated', (event) => setup(event.target));
