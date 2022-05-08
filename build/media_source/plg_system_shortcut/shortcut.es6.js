class Shortcut {
  constructor() {
    if (!Joomla) {
      throw new Error('Joomla API is not properly initialised');
    }

    ((window, document, Joomla) => {
      'use strict';

      Joomla.addShortcut = (event, selector, prevent) => {
        const actionBtn = document.querySelector(selector);
        if (actionBtn) {
          if (prevent) {
            event.preventDefault();
          }
          actionBtn.click();
        }
      };
      var options="";
      document.addEventListener('DOMContentLoaded', ()=> {
        if (Joomla.getOptions('system-shortcut'))
        {
           options =Joomla.getOptions('system-shortcut');
        }
      })
      function handleKeyPressEvent(e) {
        for (var action in options){
          // meta+shift+alt+c => [meta, shift, alt, c]
          const lastKey = options[action].keyEvent.charAt(options[action].keyEvent.length - 1);
          if (e.key.toLowerCase() == lastKey) {
            Joomla.addShortcut(e, options[action].selector);
          }
      }

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
  }
}
new Shortcut();
