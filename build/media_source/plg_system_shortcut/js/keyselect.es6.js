class JoomlaShortcutModal {
  constructor() {
    if (!Joomla) {
      throw new Error('Joomla API is not properly initialised');
    }
    // Bindings
    this.setModalAttributes = this.setModalAttributes.bind(this);
    this.handleKeyCombinationkeyUpEvent = this.handleKeyCombinationkeyUpEvent.bind(this);
    this.initialiseKeySelectModal = this.initialiseKeySelectModal.bind(this);
    this.handleSaveCombinationkeyUpEvent = this.handleSaveCombinationkeyUpEvent.bind(this);
    document.addEventListener('DOMContentLoaded', this.initialiseKeySelectModal);
    const keySelectBtns = document.getElementsByClassName('keySelectBtn');
    for (let x = 0; x < keySelectBtns.length; x += 1) {
      keySelectBtns[x].addEventListener('click', this.handleKeySelectClickEvent, false);
    }
  }

  initialiseKeySelectModal() {
    const modalDiv = document.createElement('div');
    this.setModalAttributes(modalDiv, {
      class: 'modal fade', id: 'keySelectModal', tabindex: '-1', role: 'dialog', 'aria-labelledby': 'keySelectModalLabel', 'aria-hidden': 'true',
    });
    modalDiv.innerHTML = `<div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="keySelectModalLabel">${Joomla.getOptions('set_shorcut_text')}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="p-3"><p>${Joomla.getOptions('current_combination_text')}: <span id="currentKeyCombination"></span></p><div class="form-group"><input type="hidden" id="current_KeyEvent" value="" /><input type="hidden" id="current_keyValue" value="" /><input type="hidden" id="current_hasControl" value="0" /><input type="hidden" id="current_hasShift" value="0" /><input type="hidden" id="current_hasAlt" value="0" /></div><p>${Joomla.getOptions('new_combination_text')}: <span id="newKeyCombination"></span></p></div></div><div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close">${Joomla.getOptions('cancel_button_text')}</button><button type="button" class="btn btn-success" id="saveKeyCombination">${Joomla.getOptions('save_button_text')}</button></div></div></div>`;
    document.body.appendChild(modalDiv);
    const keySelectModal = document.getElementById('keySelectModal');
    Joomla.initialiseModal(keySelectModal);
    keySelectModal.addEventListener('keydown', this.handleKeyDownEvent, false);
    keySelectModal.addEventListener('keyup', this.handleKeyCombinationkeyUpEvent, false);
    const saveKeyCombination = document.getElementById('saveKeyCombination');
    saveKeyCombination.addEventListener('click', this.handleSaveCombinationkeyUpEvent, false);
  }

  setModalAttributes(element, attrs) {
    Object.entries(attrs).forEach(([key, value]) => element.setAttribute(key, value));
  }

  handleKeySelectClickEvent(e) {
    e.preventDefault();
    document.getElementById('currentKeyCombination').textContent = this.textContent;
    document.getElementById('newKeyCombination').textContent = '';
    document.getElementById('current_KeyEvent').value = this.getAttribute('data-class');
    const keySelectModal = document.getElementById('keySelectModal');
    window.bootstrap.Modal.getInstance(keySelectModal).show(keySelectModal);
  }

  handleKeyCombinationkeyUpEvent(e) {
    if (e.keyCode >= 65 && e.keyCode <= 90) {
      let keyValue = e.key;
      keyValue = keyValue.toUpperCase();
      document.getElementById('current_hasControl').value = 0;
      document.getElementById('current_hasShift').value = 0;
      document.getElementById('current_hasAlt').value = 0;
      const newKeySelectCombination = [];
      if (e.ctrlKey) {
        newKeySelectCombination.push('CTRL');
        document.getElementById('current_hasControl').value = 1;
      }
      if (e.shiftKey) {
        newKeySelectCombination.push('SHIFT');
        document.getElementById('current_hasShift').value = 1;
      }
      if (e.metaKey || e.altKey) {
        if (navigator.platform.match('Mac')) {
          newKeySelectCombination.push('META');
        } else {
          newKeySelectCombination.push('ALT');
          document.getElementById('current_hasAlt').value = 1;
        }
      }
      newKeySelectCombination.push(keyValue);
      const newKeySelect = newKeySelectCombination.join(' + ');
      document.getElementById('newKeyCombination').textContent = newKeySelect;
      document.getElementById('current_keyValue').value = keyValue.toLowerCase();
    }
  }

  handleSaveCombinationkeyUpEvent(e) {
    e.preventDefault();
    const keySelectModal = document.getElementById('keySelectModal');
    if (document.getElementById('newKeyCombination').textContent) {
      const currentKeyEventVal = document.getElementById('current_KeyEvent').value;
      document.getElementById(`jform_params_${currentKeyEventVal}_keyEvent`).value = document.getElementById('current_keyValue').value;
      document.getElementById(`jform_params_${currentKeyEventVal}_hasControl`).value = document.getElementById('current_hasControl').value;
      document.getElementById(`jform_params_${currentKeyEventVal}_hasShift`).value = document.getElementById('current_hasShift').value;
      document.getElementById(`jform_params_${currentKeyEventVal}_hasAlt`).value = document.getElementById('current_hasAlt').value;
      document.getElementById(`jform_params_${currentKeyEventVal}_keySelect_btn`).textContent = document.getElementById('newKeyCombination').textContent;
      document.getElementById(`jform_params_${currentKeyEventVal}_keySelect`).value = document.getElementById('newKeyCombination').textContent;
    }
    window.bootstrap.Modal.getInstance(keySelectModal).hide();
  }
}
// eslint-disable-next-line no-new
new JoomlaShortcutModal();
