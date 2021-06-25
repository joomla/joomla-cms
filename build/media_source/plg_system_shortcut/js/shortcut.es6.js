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
        let options="";
        document.addEventListener('DOMContentLoaded', ()=> {
          if (Joomla.getOptions('system-shortcut'))
          {
             options =Joomla.getOptions('system-shortcut');
          }
        })
        function handleKeyPressEvent(e) {
          for (var action in options){
            // check for alt key
            let keyEvent = options[action].keyEvent;
            let metaKey = false;
            let altKey = false;
            let shiftKey = false;
            let ctrlKey = false;
            if(keyEvent.toLowerCase().includes("alt")){			  altKey = true;		  }
            if(keyEvent.toLowerCase().includes("meta")){			  metaKey = true;		  }
            if(keyEvent.toLowerCase().includes("shift")){			  shiftKey = true;		  }
            if(keyEvent.toLowerCase().includes("ctrl")){			  ctrlKey = true;		  }
            const lastKey = keyEvent.charAt(keyEvent.length - 1);
            
            if ((metaKey == false || (metaKey == true && e.metaKey)) && (altKey == false || (altKey == true && e.altKey)) && (shiftKey == false || (shiftKey == true && e.shiftKey)) && (ctrlKey == false || (ctrlKey == true && e.ctrlKey)) && e.key.toLowerCase() == lastKey) {
              Joomla.addShortcut(e, options[action].selector);
            }
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