
if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

((window, document, Joomla) => {
  'use strict';

  Joomla.addShortcut = (event, selector, prevent) => {
    let actionBtn = document.querySelector(selector);
    if (actionBtn) {
      console.log(selector);
      if(prevent)
      {
        event.preventDefault();
      }
      actionBtn.click();
    }
  };

  function handleKeyPressEvent(e) {
    
    // like this maintain a list of all the shortcuts
    // add a check if the array is already defined, if not then define it.
    
    window.Joomla.shortcutsEvent = [{ actionButton:'meta+alt+s', selector:'joomla-toolbar-button button.button-apply' }, 
                      {actionButton:'meta+alt+n', selector:'joomla-toolbar-button button.button-new'},
                      {actionButton:'meta+alt+w', selector:'joomla-toolbar-button button.button-save'},
                      {actionButton:'meta+shift+alt+w', selector:'joomla-toolbar-button button.button-save-new'},
                      {actionButton:'meta+alt+h', selector:'joomla-toolbar-button button.button-help'},
                      {actionButton:'meta+alt+q', selector:'joomla-toolbar-button button.button-cancel'},
                      {actionButton:'meta+shift+alt+c', selector:'joomla-toolbar-button button.button-copy'}];

    // Then map shortcuts
    window.Joomla.shortcutsEvent.map(( {actionButton, selector} ) => {
      let splitArr = actionButton.split('+');
      // meta+shift+alt+c => [meta, shift, alt, c]
      let lastKey = actionButton.charAt(actionButton.length -1);
      if(e.key.toLowerCase() == lastKey)
      {
        Joomla.addShortcut(e, selector);
      }
      
      // console.log(actionButton + " " + selector)
    });
    
    
    if (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) {
      // On Press ALT + S
      let ch = e.key.toLowerCase();
      let list = [ {"c": "joomla-toolbar-button button.button-save-copy"},
                   { "h": "joomla-toolbar-button button.button-help"},
                   {"q": "joomla-toolbar-button button.button-cancel"},
                   {"s": "joomla-toolbar-button button.button-apply"},
                   {"n": "joomla-toolbar-button button.button-new"},
                   {"w": "joomla-toolbar-button button.button-save"},
                   {"n": "joomla-toolbar-button button.button-save-new"},
        ];
      
      list.map(l => {
        if(ch == l.first)
          Joomla.addShortcut(e, l.second);
      })

  }
  }

  window.addEventListener("DOMContentLoaded", (event) => {
    document.addEventListener(
      "keydown",
      function (e) {
        handleKeyPressEvent(e);
      },
      false
    );

    try {
      tinyMCE.activeEditor.on("keydown", function (e) {
        handleKeyPressEvent(e);
      });
    } catch (e) {}
  });

})(window, document, Joomla);
