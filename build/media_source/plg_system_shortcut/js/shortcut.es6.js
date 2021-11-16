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
		},
		new: {
		  keyEvent: 'n',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 0,
		  selector: 'joomla-toolbar-button button.button-new',
		},
		save: {
		  keyEvent: 'w',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 0,
		  selector: 'joomla-toolbar-button button.button-save',
		},
		saveNew: {
		  keyEvent: 'n',
		  hasShift: 1,
		  hasAlt: 1,
		  hasControl: 0,
		  selector: 'joomla-toolbar-button button.button-save-new',
		},
		help: {
		  keyEvent: 'x',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 0,
		  selector: 'joomla-toolbar-button button.button-help',
		},
		cancel: {
		  keyEvent: 'q',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 0,
		  selector: 'joomla-toolbar-button button.button-cancel',
		},
		copy: {
		  keyEvent: 'c',
		  hasShift: 1,
		  hasAlt: 1,
		  hasControl: 0,
		  selector: 'joomla-toolbar-button button.button-button-copy',
		},
		article: {
		  keyEvent: 'a',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 1,
		  selector: 'joomla-editor-option~article_modal',
		},
		contact: {
		  keyEvent: 'c',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 1,
		  selector: 'joomla-editor-option~contact_modal',
		},
		fields: {
		  keyEvent: 'f',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 1,
		  selector: 'joomla-editor-option~fields_modal',
		},
		image: {
		  keyEvent: 'i',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 1,
		  selector: 'joomla-editor-option~image_modal',
		},
		menu: {
		  keyEvent: 'm',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 1,
		  selector: 'joomla-editor-option~menu_modal',
		},
		module: {
		  keyEvent: 'm',
		  hasShift: 1,
		  hasAlt: 1,
		  hasControl: 1,
		  selector: 'joomla-editor-option~module_modal',
		},
		pagebreak: {
		  keyEvent: 'p',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 1,
		  selector: 'joomla-editor-option~pagebreak_modal',
		},
		readmore: {
		  keyEvent: 'r',
		  hasShift: 0,
		  hasAlt: 1,
		  hasControl: 1,
		  selector: 'joomla-editor-option~read_more',
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

		  (Number(altKey) === 0
			|| (Number(altKey) === 1
			  && (navigator.platform.match('Mac') ? e.metaKey : e.altKey)))
		  && (Number(shiftKey) === 0 || (Number(shiftKey) === 1 && e.shiftKey))
		  && (Number(ctrlKey) === 0 || (Number(ctrlKey) === 1 && e.ctrlKey))
		  && e.key.toLowerCase() === keyEvent
		) {
		  this.execCommand(e, this.options[action].selector);
		}
	  });
	}
  }
  // eslint-disable-next-line no-new
  new JoomlaShortcuts();
