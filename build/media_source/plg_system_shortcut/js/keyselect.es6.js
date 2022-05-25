((document, Joomla) => {
  if (!Joomla) {
    throw new Error('Joomla API is not properly initialised');
  }

  let button = null;

  const keyDown = (e) => {
    if (e.keyCode >= 65 && e.keyCode <= 90) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
    }
  };

  const keyUp = (e) => {
    if (e.keyCode >= 65 && e.keyCode <= 90) {
      const pressedKeys = [];
      if (e.ctrlKey) {
        pressedKeys.push('CTRL');
      }
      if (e.shiftKey) {
        pressedKeys.push('SHIFT');
      }
      if (navigator.platform.match('Mac') ? e.metaKey : e.altKey) {
        pressedKeys.push('ALT');
      }

      pressedKeys.push(e.key.toUpperCase());

      document.getElementById('newKeyCombination').value = pressedKeys.join(' + ');
    }
  };

  const openModal = (e) => {
    button = null;
    if (!e.relatedTarget) {
      return;
    }

    button = e.relatedTarget;
    document.getElementById('currentKeyCombination').value = e.relatedTarget.innerText;
  };

  const closeDModal = () => {
    document.getElementById('newKeyCombination').value = '';
  };

  const confirmKeys = () => {
    const modal = document.getElementById('keySelectModal');
    const modalInstance = bootstrap.Modal.getInstance(modal);
    const value = Joomla.sanitizeHtml(document.getElementById('newKeyCombination').value);
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
              <h5 class="modal-title" id="keySelectModalLabel">${Joomla.Text._('PLG_SYSTEM_SHORTCUT_SET_SHORTCUT')}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="p-3">
                <div class="mb-3">
                  <label>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_CURRENT_COMBINATION')}</label>
                  <input type="text" class="form-control" readonly id="currentKeyCombination">
                </div>
                <div class="mb-3">
                  <label>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_NEW_COMBINATION')}</label>
                  <input type="text" class="form-control" readonly id="newKeyCombination">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">${Joomla.Text._('PLG_SYSTEM_SHORTCUT_CANCEL')}</button>
              <button type="button" class="btn btn-success" id="saveKeyCombination">${Joomla.Text._('PLG_SYSTEM_SHORTCUT_SAVE_CHANGES')}</button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modal);
    const keySelectModal = document.getElementById('keySelectModal');

    keySelectModal.addEventListener('keydown', keyDown, false);
    keySelectModal.addEventListener('keyup', keyUp, false);
    keySelectModal.addEventListener('show.bs.modal', openModal, false);
    keySelectModal.addEventListener('hidden.bs.modal', closeDModal, false);
    const saveKeyCombination = document.getElementById('saveKeyCombination');
    saveKeyCombination.addEventListener('click', confirmKeys, false);
  };

  document.addEventListener('DOMContentLoaded', initialize);
})(document, Joomla);
