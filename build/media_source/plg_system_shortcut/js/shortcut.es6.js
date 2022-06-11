((document, Joomla) => {
  'use strict';

  if (!Joomla) {
    throw new Error('Joomla API is not properly initialised');
  }

  Joomla.addShortcut = (hotkey, callback) => {
    hotkeys(hotkey, (event) => {
      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();

      callback.call();
    });
  };

  Joomla.addClickShortcut = (hotkey, selector) => {
    Joomla.addShortcut(hotkey, () => {
      const element = document.querySelector(selector);
      if (element) {
        element.click();
      }
    });
  };

  Joomla.addFocusShortcut = (hotkey, selector) => {
    Joomla.addShortcut(hotkey, () => {
      const element = document.querySelector(selector);
      if (element) {
        element.focus();
      }
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    const options = Joomla.getOptions('plg_system_shortcut.shortcuts');
    Object.values(options).forEach((value) => {
      if (value.selector.includes('input')) {
        Joomla.addFocusShortcut(value.default, value.selector);
      } else {
        Joomla.addClickShortcut(value.default, value.selector);
      }
    });
  });
})(document, Joomla);
