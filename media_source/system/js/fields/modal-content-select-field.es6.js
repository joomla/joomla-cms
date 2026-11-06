/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

import JoomlaDialog from 'joomla.dialog';

/**
 * Helper method to set values on the fields, and trigger "change" event
 *
 * @param {object} data
 * @param {HTMLInputElement} inputValue
 * @param {HTMLInputElement} inputTitle
 * @param {HTMLElement} container
 */
const setValues = (data, inputValue, inputTitle, container) => {
  const value = `${data.id || data.value || ''}`;
  const isChanged = inputValue.value !== value;
  inputValue.value = value;
  if (inputTitle) {
    inputTitle.value = data.title || inputValue.value;
  }
  // The selected item reports whether it is checked out by someone else. A list which does not
  // report it at all counts as not checked out, same as before.
  if (container) {
    container.dataset.checkedOut = data.checkedOut && data.checkedOut !== '0' ? '1' : '0';
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
 * @param {HTMLElement} container
 * @returns {Promise}
 */
const doSelect = (inputValue, inputTitle, dialogConfig, container) => {
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
        setValues(event.data, inputValue, inputTitle, container);
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
      hasValue ? el.removeAttribute('hidden') : el.setAttribute('hidden', '');
    } else {
      hasValue ? el.setAttribute('hidden', '') : el.removeAttribute('hidden');
    }
  });

  // An item which is checked out by another user must not be editable
  const isCheckedOut = hasValue && container.dataset.checkedOut === '1';
  const buttonEdit = container.querySelector('[data-button-action="edit"]');
  const note = container.querySelector('.js-checked-out-note');

  if (buttonEdit && isCheckedOut) {
    buttonEdit.setAttribute('hidden', '');
  }

  if (note) {
    isCheckedOut ? note.removeAttribute('hidden') : note.setAttribute('hidden', '');
  }
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
    // When the item is checked out by someone else then the dialog was not able to check it out for
    // us, and closing it must not release the lock of that other user.
    const wasCheckedOut = container.dataset.checkedOut === '1';

    // Handle requested action
    let handle;
    switch (action) {
      case 'select':
      case 'create': {
        const url = dialogConfig.src.indexOf('http') === 0 ? new URL(dialogConfig.src) : new URL(dialogConfig.src, window.location.origin);
        url.searchParams.set(token, '1');
        dialogConfig.src = url.toString();
        handle = doSelect(inputValue, inputTitle, dialogConfig, container);
        break;
      }
      case 'edit': {
        // Update current value in the URL
        const url = dialogConfig.src.indexOf('http') === 0 ? new URL(dialogConfig.src) : new URL(dialogConfig.src, window.location.origin);
        url.searchParams.set(keyName, inputValue.value);
        url.searchParams.set(token, '1');
        dialogConfig.src = url.toString();

        handle = doSelect(inputValue, inputTitle, dialogConfig, container);
        break;
      }
      case 'clear':
        handle = (async () => setValues({ id: '', title: '' }, inputValue, inputTitle, container))();
        break;
      default:
        throw new Error(`Unknown action ${action} for Modal select field`);
    }

    handle.then(() => {
      // Perform checkin when needed. The item may have been checked out again by "Save" in the
      // dialog, that applies to a newly created item as well.
      if (button.dataset.checkinUrl && inputValue.value && !wasCheckedOut) {
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

      // Keep the view in sync, also when the same item was selected again
      updateView(inputValue, container);

      // The dialog returns the focus to the button which opened it. When that button is hidden by
      // now, move the focus to the next available one instead of losing it to the document.
      if (button.hasAttribute('hidden')) {
        const nextButton = container.querySelector('[data-button-action]:not([hidden])');
        (nextButton || inputTitle || inputValue).focus();
      }
    });
  });
};

const setup = (container) => {
  container.querySelectorAll('.js-modal-content-select-field').forEach((el) => setupField(el));
};

document.addEventListener('DOMContentLoaded', () => setup(document));
document.addEventListener('joomla:updated', (event) => setup(event.target));
