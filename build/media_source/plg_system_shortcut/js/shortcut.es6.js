class JoomlaShortcuts {
    constructor() {
      if (!Joomla) {
        throw new Error('Joomla API is not properly initialised');
      }
      
      const defaultOptions = {
        apply: {
          keyEvent: 's',
          hasShift: false,
          hasAlt: true,
          hasControl: true,
          selector: 'joomla-toolbar-button button.button-apply'
        },
        new :{
          keyEvent: 'n',
          hasShift: false,
          hasAlt: true,
          hasControl: true,
          selector: 'joomla-toolbar-button button.button-new'
        },
        save :{
          keyEvent: 'w',
          hasShift: false,
          hasAlt: true,
          hasControl: true,
          selector: 'joomla-toolbar-button button.button-save'
        },
        saveNew: {
          keyEvent: 'w',
          hasShift: true,
          hasAlt: true,
          hasControl: true,
          selector: 'joomla-toolbar-button button.button-save-new'
        },
        help :{
          keyEvent: 'h',
          hasShift: false,
          hasAlt: true,
          hasControl: true,
          selector: 'joomla-toolbar-button button.button-help'
        },
        cancel :{
          keyEvent: 'q',
          hasShift: false,
          hasAlt: true,
          hasControl: true,
          selector: 'joomla-toolbar-button button.button-cancel'
        },
        copy: {
          keyEvent: 'c',
          hasShift: true,
          hasAlt: true,
          hasControl: true,
          selector: 'joomla-toolbar-button button.button-button-copy'
        }
      };
      
      const phpOptions = Joomla.getOptions('joomla-shortcut-keys');
      
      this.options = {...defaultOptions, ...phpOptions};
  
      // Bindings
      this.execCommand = this.execCommand.bind(this);
      this.handleKeyPressEvent = this.handleKeyPressEvent.bind(this);
  
      document.addEventListener('keydown', this.handleKeyPressEvent, false);
  
      try {
        tinyMCE.activeEditor.on('keydown', function (e) {
          handleKeyPressEvent(e);
        });
      } catch (e) {}
    }
  
    execCommand(event, selector, prevent) {
      if(selector.includes("joomla-editor-option") && Joomla.getOptions('editor') == "tinymce"){
          // Editor Option
          const selectorArr = selector.split("~");
          if(selectorArr[1] !== undefined){
              if(selectorArr[1] == "read_more"){
                  const element = Joomla.getOptions('xtd-pagebreak').editor;
                  insertReadmore(element);
              }else{
                  const bootstrapModals = Joomla.getOptions('bootstrap.modal');
                  for (var eModal in bootstrapModals){
                      if(eModal.includes(selectorArr[1])){
                          const modalElement = document.getElementById(eModal.replace("#", ""));
                          if(modalElement){
                              window.bootstrap.Modal.getInstance(modalElement).show(modalElement);
                          }
                      }
                  }
              }
          }
          event.preventDefault();
      }else{
             let actionBtn = document.querySelector(selector);
          if (actionBtn) {
            if (prevent) {
              event.preventDefault();
            }
            actionBtn.click();
          }
      }
    }
  
    handleKeyPressEvent(e) {
      for (var action in this.options){
        // check for meta+shift+alt+ctrl key
        let keyEvent = this.options[action].keyEvent;
        let altKey = this.options[action].hasAlt;
        let shiftKey = this.options[action].hasShift;
        let ctrlKey = this.options[action].hasControl;
        
        if ((altKey == false || (altKey == true && (navigator.platform.match('Mac') ? e.metaKey : e.altKey))) && (shiftKey == false || (shiftKey == true && e.shiftKey)) && (ctrlKey == false || (ctrlKey == true && e.ctrlKey)) && e.key.toLowerCase() == keyEvent) {
          this.execCommand(e, this.options[action].selector);
        }
      }
    }
  }
  
  new JoomlaShortcuts();