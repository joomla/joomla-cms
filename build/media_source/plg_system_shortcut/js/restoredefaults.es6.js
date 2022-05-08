class JoomlaRestoreDefaults {
  constructor() {
    if (!Joomla) {
      throw new Error('Joomla API is not properly initialised');
    }
    let restoreDefaultsBtns =
      document.getElementsByClassName('restoreDefaultsBtn');
    for (let x = 0; x < restoreDefaultsBtns.length; x++) {
      restoreDefaultsBtns[x].addEventListener(
        'click',
        this.handleRestoreDefaultsClickEvent,
        false
      );
    }
  }
  handleRestoreDefaultsClickEvent(e) {
    e.preventDefault();
    const buttonDefaultOptions = {
      apply: { keyEvent: 's', hasShift: 0, hasAlt: 1, hasControl: 0 },
      new: { keyEvent: 'n', hasShift: 0, hasAlt: 1, hasControl: 0 },
      save: { keyEvent: 'w', hasShift: 0, hasAlt: 1, hasControl: 0 },
      saveNew: { keyEvent: 'n', hasShift: 1, hasAlt: 1, hasControl: 0 },
      help: { keyEvent: 'x', hasShift: 0, hasAlt: 1, hasControl: 0 },
      cancel: { keyEvent: 'q', hasShift: 0, hasAlt: 1, hasControl: 0 },
      copy: { keyEvent: 'c', hasShift: 1, hasAlt: 1, hasControl: 0 },
    };
    const buttonEditorOptions = {
      article: { keyEvent: 'a', hasShift: 0, hasAlt: 1, hasControl: 1 },
      contact: { keyEvent: 'c', hasShift: 0, hasAlt: 1, hasControl: 1 },
      fields: { keyEvent: 'f', hasShift: 0, hasAlt: 1, hasControl: 1 },
      image: { keyEvent: 'i', hasShift: 0, hasAlt: 1, hasControl: 1 },
      menu: { keyEvent: 'm', hasShift: 0, hasAlt: 1, hasControl: 1 },
      module: { keyEvent: 'm', hasShift: 1, hasAlt: 1, hasControl: 1 },
      pagebreak: { keyEvent: 'p', hasShift: 0, hasAlt: 1, hasControl: 1 },
      readmore: { keyEvent: 'r', hasShift: 0, hasAlt: 1, hasControl: 1 },
    };
    if (this.getAttribute('data-class') === 'buttons') {
      this.options = buttonDefaultOptions;
    } else if (this.getAttribute('data-class') === 'editor') {
      this.options = buttonEditorOptions;
    }
    for (let action in this.options) {
      let keyValue = this.options[action].keyEvent;
      keyValue = keyValue.toUpperCase();
      const restoreCombination = new Array();
      if (this.options[action].hasControl) {
        restoreCombination.push('CTRL');
      }
      if (this.options[action].hasShift) {
        restoreCombination.push('SHIFT');
      }
      if (this.options[action].hasAlt) {
        restoreCombination.push('ALT');
      }
      restoreCombination.push(keyValue);
      const restoreDefault = restoreCombination.join(" + ");
      document.getElementById(`jform_params_${action}_keyEvent`).value =
        this.options[action].keyEvent;
      document.getElementById(`jform_params_${action}_hasControl`).value =
        this.options[action].hasControl;
      document.getElementById(`jform_params_${action}_hasShift`).value =
        this.options[action].hasShift;
      document.getElementById(`jform_params_${action}_hasAlt`).value =
        this.options[action].hasAlt;
      document.getElementById(
        `jform_params_${action}_keySelect_btn`
      ).textContent = restoreDefault;
      document.getElementById(`jform_params_${action}_keySelect`).value =
        restoreDefault;
    }
  }
}
new JoomlaRestoreDefaults();
