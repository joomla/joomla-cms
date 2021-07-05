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
		  hasControl: false,
		  selector: 'joomla-toolbar-button button.button-apply'
		},
		new :{
		  keyEvent: 'n',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: false,
		  selector: 'joomla-toolbar-button button.button-new'
		},
		save :{
		  keyEvent: 'w',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: false,
		  selector: 'joomla-toolbar-button button.button-save'
		},
		saveNew: {
		  keyEvent: 'n',
		  hasShift: true,
		  hasAlt: true,
		  hasControl: false,
		  selector: 'joomla-toolbar-button button.button-save-new'
		},
		help :{
		  keyEvent: 'x',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: false,
		  selector: 'joomla-toolbar-button button.button-help'
		},
		cancel :{
		  keyEvent: 'q',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: false,
		  selector: 'joomla-toolbar-button button.button-cancel'
		},
		copy: {
		  keyEvent: 'c',
		  hasShift: true,
		  hasAlt: true,
		  hasControl: false,
		  selector: 'joomla-toolbar-button button.button-button-copy'
		},
		article: {
		  keyEvent: 'a',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: true,
		  selector: 'joomla-editor-option~article_modal'
		},
		contact: {
		  keyEvent: 'c',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: true,
		  selector: 'joomla-editor-option~contact_modal'
		},
		fields: {
		  keyEvent: 'f',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: true,
		  selector: 'joomla-editor-option~fields_modal'
		},
		image: {
		  keyEvent: 'i',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: true,
		  selector: 'joomla-editor-option~image_modal'
		},
		menu: {
		  keyEvent: 'm',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: true,
		  selector: 'joomla-editor-option~menu_modal'
		},
		module: {
		  keyEvent: 'm',
		  hasShift: true,
		  hasAlt: true,
		  hasControl: true,
		  selector: 'joomla-editor-option~module_modal'
		},
		pagebreak: {
		  keyEvent: 'p',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: true,
		  selector: 'joomla-editor-option~pagebreak_modal'
		},
		readmore: {
		  keyEvent: 'r',
		  hasShift: false,
		  hasAlt: true,
		  hasControl: true,
		  selector: 'joomla-editor-option~read_more'
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
				  for (let eModal in bootstrapModals){
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
	  for (let action in this.options){
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