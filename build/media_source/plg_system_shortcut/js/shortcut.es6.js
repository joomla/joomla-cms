class JoomlaShortcuts {
  constructor() {
    if (!Joomla) {
      throw new Error('Joomla API is not properly initialised');
    }
    const defaultOptions = {
      apply: {
        keyEvent: 's',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 0,
        selector: 'joomla-toolbar-button button.button-apply',
        link: '',
      },
      new: {
        keyEvent: 'n',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 0,
        selector: 'joomla-toolbar-button button.button-new',
        link: '',
      },
      save: {
        keyEvent: 'w',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 0,
        selector: 'joomla-toolbar-button button.button-save',
        link: '',
      },
      saveNew: {
        keyEvent: 'n',
        hasShift: 1,
        hasAlt: 1,
        hasControl: 0,
        selector: 'joomla-toolbar-button button.button-save-new',
        link: '',
      },
      help: {
        keyEvent: 'x',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 0,
        selector: 'joomla-toolbar-button button.button-help',
        link: '',
      },
      cancel: {
        keyEvent: 'q',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 0,
        selector: 'joomla-toolbar-button button.button-cancel',
        link: '',
      },
      copy: {
        keyEvent: 'c',
        hasShift: 1,
        hasAlt: 1,
        hasControl: 0,
        selector: 'joomla-toolbar-button button.button-button-copy',
        link: '',
      },
      article: {
        keyEvent: 'a',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 1,
        selector: 'joomla-editor-option~article_modal',
        link: '',
      },
      contact: {
        keyEvent: 'c',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 1,
        selector: 'joomla-editor-option~contact_modal',
        link: '',
      },
      fields: {
        keyEvent: 'f',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 1,
        selector: 'joomla-editor-option~fields_modal',
        link: '',
      },
      image: {
        keyEvent: 'i',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 1,
        selector: 'joomla-editor-option~image_modal',
        link: '',
      },
      menu: {
        keyEvent: 'm',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 1,
        selector: 'joomla-editor-option~menu_modal',
        link: '',
      },
      module: {
        keyEvent: 'm',
        hasShift: 1,
        hasAlt: 1,
        hasControl: 1,
        selector: 'joomla-editor-option~module_modal',
        link: '',
      },
      pagebreak: {
        keyEvent: 'p',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 1,
        selector: 'joomla-editor-option~pagebreak_modal',
        link: '',
      },
      readmore: {
        keyEvent: 'r',
        hasShift: 0,
        hasAlt: 1,
        hasControl: 1,
        selector: 'joomla-editor-option~read_more',
        link: '',
      },
      com_articles: {
        keyEvent: 'a',
        hasShift: 1,
        hasAlt: 0,
        hasControl: 1,
        selector: '',
        link: 'index.php?option=com_content&view=articles',
      },
      com_categories: {
        keyEvent: 'c',
        hasShift: 1,
        hasAlt: 0,
        hasControl: 1,
        selector: '',
        link: 'index.php?option=com_categories&view=categories',
      },
      com_fields: {
        keyEvent: 'f',
        hasShift: 1,
        hasAlt: 0,
        hasControl: 1,
        selector: '',
        link: 'index.php?option=com_fields&view=fields',
      },
      com_sitemodules: {
        keyEvent: 'm',
        hasShift: 1,
        hasAlt: 0,
        hasControl: 1,
        selector: '',
        link: 'index.php?option=com_modules&view=modules&client_id=0',
      },
      com_adminmodules: {
        keyEvent: 'a',
        hasShift: 1,
        hasAlt: 1,
        hasControl: 1,
        selector: '',
        link: 'index.php?option=com_modules&view=modules&client_id=1',
      },
      com_banners: {
        keyEvent: 'b',
        hasShift: 1,
        hasAlt: 0,
        hasControl: 1,
        selector: '',
        link: 'index.php?option=com_banners&view=banners',
      },
      com_contacts: {
        keyEvent: 'c',
        hasShift: 1,
        hasAlt: 0,
        hasControl: 1,
        selector: '',
        link: 'index.php?option=com_contacts&view=contacts',
      },
    };
    const phpOptions = Joomla.getOptions('joomla-shortcut-keys');
    this.bootstrapModals = Joomla.getOptions('bootstrap.modal');
    this.options = { ...defaultOptions, ...phpOptions };
    this.handleKeyPressEvent = this.handleKeyPressEvent.bind(this);
    document.addEventListener('keydown', this.handleKeyPressEvent, false);
    if (window && window.tinyMCE) {
      window.tinyMCE.activeEditor.on('keydown', (e) => {
        this.handleKeyPressEvent(e);
      });
    }
  }

  execCommand(event, selector, prevent) {
    if (
      selector.includes('joomla-editor-option')
      && Joomla.getOptions('editor') === 'tinymce'
    ) {
      // Editor Option
      const selectorArr = selector.split('~');
      if (selectorArr[1] !== undefined) {
        if (selectorArr[1] !== 'read_more') {
          Object.entries(this.bootstrapModals).forEach((eModal) => {
            if (eModal.includes(selectorArr[1])) {
              const modalElement = document.getElementById(
                eModal.replace('#', ''),
              );
              if (modalElement) {
                window.bootstrap.Modal.getInstance(modalElement).show(
                  modalElement,
                );
              }
            }
          });
        }
      }
      event.preventDefault();
    } else {
      const actionBtn = document.querySelector(selector);
      if (actionBtn) {
        if (prevent) {
          event.preventDefault();
        }
        actionBtn.click();
      }
    }
  }

  handleKeyPressEvent(e) {
    Object.keys(this.options).forEach((action) => {
      // check for meta+shift+alt+ctrl key
      const { keyEvent } = this.options[action];
      const altKey = this.options[action].hasAlt;
      const shiftKey = this.options[action].hasShift;
      const ctrlKey = this.options[action].hasControl;
      if (
        (Number(altKey) === false
          || (Number(altKey) === true
            && (navigator.platform.match('Mac') ? e.metaKey : e.altKey)))
        && (Number(shiftKey) === false || (Number(shiftKey) === true && e.shiftKey))
        && (Number(ctrlKey) === false || (Number(ctrlKey) === true && e.ctrlKey))
        && e.key.toLowerCase() === keyEvent
      ) {
        this.execCommand(e, this.options[action].selector);
      }
    });
  }
}
// eslint-disable-next-line no-new
new JoomlaShortcuts();
