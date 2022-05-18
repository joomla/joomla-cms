class Shortcut {
  constructor() {
    if (!Joomla) {
      throw new Error('Joomla API is not properly initialised');
    }

    ((window, document, Joomla) => {
      'use strict';

      Joomla.addClickButtonShortcut = (hotkey, selector) => {
        Joomla.addShortcut(hotkey, () => {
          const actionBtn = document.querySelector(selector);
          if (actionBtn) {
            actionBtn.click();
          }
        });
      };

      Joomla.addShortcut = (hotkey, callback) => {
        if (prevent) {
          event.preventDefault();
        }
        hotkeys(hotkey, (event, handler) => {
          event.preventDefault();

          callback.call();
        });
      };

      document.addEventListener('DOMContentLoaded', ()=> {
        const options = Joomla.getOptions('plg_system_shortcut.shortcuts');

        options.forEach((callback, hotkey) => {
          Joomla.addShortcut(hotkey, callback);
        });
      });

      if (window.navigator.platform.match('Mac') ? e.metaKey : e.altKey) {
      // On Press ALT + S
        const ch = e.key.toLowerCase();
        const list = [{ c: 'joomla-toolbar-button button.button-save-copy' },
          { h: 'joomla-toolbar-button button.button-help' },
          { q: 'joomla-toolbar-button button.button-cancel' },
          { s: 'joomla-toolbar-button button.button-apply' },
          { n: 'joomla-toolbar-button button.button-new' },
          { w: 'joomla-toolbar-button button.button-save' },
          { n: 'joomla-toolbar-button button.button-save-new' },
        ];

        list.map((l) => {
          if (ch == l.first) { Joomla.addShortcut(e, l.second); }
        });
      }

      window.addEventListener('DOMContentLoaded', () => {
        document.addEventListener(
          'keydown',
          (e) => {
            handleKeyPressEvent(e);
          },
          false,
        );

        try {
          tinyMCE.activeEditor.on('keydown', (e) => {
            handleKeyPressEvent(e);
          });
        } catch (e) {}
      });
    })(window, document, Joomla);
  };
}
new Shortcut();
