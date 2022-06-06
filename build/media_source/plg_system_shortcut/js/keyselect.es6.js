((document, Joomla) => {
  'use strict';

  if (!Joomla) {
    throw new Error('Joomla API is not properly initialised');
  }

  let button = null;
  let pressedKeys = [];

  const pushKey = (key) => {
    if (pressedKeys.indexOf(key) === -1) {
      pressedKeys.push(key);
    }
  }

  const addKey = (event) => {
    if (event.shiftKey) {
      pushKey('SHIFT');
    }
    if (event.ctrlKey) {
      pushKey('CTRL');
    }
    if (event.metaKey) {
      pushKey('⌘',);
    }
    if (event.altKey) {
      pushKey('ALT');
    }
    const key = event.key.toUpperCase();
    // Some fresh keydown events have 'control' and 'ctrl' which is superfluous
    if (key !== 'CONTROL') {
      pushKey(key);
    }
  }

  const isKeyAllowed = (event) => {
    // Allowed are alt, ctrl, shift, numbers (48 = 0) and chars (z = 90)
    const allowed = [16, 17, 18];
    return allowed.includes(event.keyCode) || event.keyCode >= 48 && event.keyCode <= 90;
  }

  const updateMacKeys = (shortcut, current) => {
    const elementId = current ? 'currentKeyCombinationMac' : 'newKeyCombinationMac';

    if (shortcut.indexOf('ALT') === -1) {
      document.getElementById(elementId).innerText = '';
    } else {
      document.getElementById(elementId).innerText = shortcut.replace('ALT', '⌥');
    }
  }

  const onKeyDown = (e) => {
    if (isKeyAllowed(e)) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      addKey(e);
    }
  };

  const onKeyUp = (e) => {
    if (isKeyAllowed(e) && pressedKeys.length) {
      const shortcut = pressedKeys.join(' + ');
      document.getElementById('newKeyCombination').innerText = shortcut;
      updateMacKeys(shortcut);
      pressedKeys = [];
    }
  };

  const openModal = (event) => {
    if (event.relatedTarget) {
      button = event.relatedTarget;
      document.getElementById('currentKeyCombination').innerText = event.relatedTarget.innerText;
      updateMacKeys(event.relatedTarget.innerText, true);
      document.getElementById('newKeyCombination').innerText = '';
      document.getElementById('newKeyCombinationMac').innerText = '';
    } else {
      button = null;
    }
  };

  const confirmKeys = () => {
    const modal = document.getElementById('keySelectModal');
    const modalInstance = bootstrap.Modal.getInstance(modal);
    const value = Joomla.sanitizeHtml(document.getElementById('newKeyCombination').innerText);
    if (button && value) {
      button.innerText = value;
      button.previousElementSibling.value = value;
    }

    modalInstance.hide();
  };

  const initialize = () => {
    const modal = `
      <div class="modal fade" id="keySelectModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="keySelectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h3 class="modal-title" id="keySelectModalLabel">${Joomla.Text._('PLG_SYSTEM_SHORTCUT_SET_SHORTCUT')}</h3>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
              <div class="mb-3">
                <p>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_CURRENT_COMBINATION')}</p>
                <p id="currentKeyCombination"></p>
                <p id="currentKeyCombinationMac"></p>
              </div>
              <div class="mb-3">
                <p>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_NEW_COMBINATION')}</p>
                <p id="newKeyCombination"></p>
                <p id="newKeyCombinationMac"></p>
              </div>
              <p>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_DESCRIPTION')}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${Joomla.Text._('PLG_SYSTEM_SHORTCUT_CANCEL')}</button>
              <button type="button" class="btn btn-success" id="saveKeyCombination">${Joomla.Text._('PLG_SYSTEM_SHORTCUT_SAVE_CHANGES')}</button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modal);

    const keySelectModal = document.getElementById('keySelectModal');
    keySelectModal.addEventListener('keydown', onKeyDown, false);
    keySelectModal.addEventListener('keyup', onKeyUp, false);
    keySelectModal.addEventListener('show.bs.modal', openModal, false);

    const saveKeyButton = document.getElementById('saveKeyCombination');
    saveKeyButton.addEventListener('click', confirmKeys, false);
  };

  document.addEventListener('DOMContentLoaded', initialize);
})(document, Joomla);
