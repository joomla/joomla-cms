((document, Joomla) => {
  'use strict';

  if (!Joomla) {
    throw new Error('Joomla API is not properly initialised');
  }

  Joomla.addShortcut = (hotkey, callback) => {
    hotkeys(hotkey, (e) => {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      if (typeof callback === 'function') {
        callback.call();
      }
    });
  };

  Joomla.addClickButtonShortcut = (hotkey, selector) => {
    Joomla.addShortcut(hotkey, () => {
      const actionBtn = document.querySelector(selector);
      if (actionBtn) {
        actionBtn.click();
      }
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    const options = Joomla.getOptions('plg_system_shortcut.shortcuts');

    Object.entries(options).forEach((option) => {
      Joomla.addShortcut(option[0], () => eval(option[1]));
    });
  });
})(document, Joomla);
