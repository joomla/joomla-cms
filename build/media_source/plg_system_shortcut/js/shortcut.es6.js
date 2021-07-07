class JoomlaShortcuts {
	constructor() {
		if (!Joomla) {
			throw new Error('Joomla API is not properly initialised');
		}
		const defaultOptions = {
			apply: {keyEvent: 's',hasShift: 0,hasAlt: 1,hasControl: 0,selector: 'joomla-toolbar-button button.button-apply'},
			new :{keyEvent: 'n',hasShift: 0,hasAlt: 1,hasControl: 0,selector: 'joomla-toolbar-button button.button-new'},
			save :{keyEvent: 'w',hasShift: 0,hasAlt: 1,hasControl: 0,selector: 'joomla-toolbar-button button.button-save'},
			saveNew: {keyEvent: 'n',hasShift: 1,hasAlt: 1,hasControl: 0,selector: 'joomla-toolbar-button button.button-save-new'},
			help :{keyEvent: 'x',hasShift: 0,hasAlt: 1,hasControl: 0,selector: 'joomla-toolbar-button button.button-help'},
			cancel :{keyEvent: 'q',hasShift: 0,hasAlt: 1,hasControl: 0,selector: 'joomla-toolbar-button button.button-cancel'},
			copy: {keyEvent: 'c',hasShift: 1,hasAlt: 1,hasControl: 0,selector: 'joomla-toolbar-button button.button-button-copy'},
			article: {keyEvent: 'a',hasShift: 0,hasAlt: 1,hasControl: 1,selector: 'joomla-editor-option~article_modal'},
			contact: {keyEvent: 'c',hasShift: 0,hasAlt: 1,hasControl: 1,selector: 'joomla-editor-option~contact_modal'},
			fields: {keyEvent: 'f',hasShift: 0,hasAlt: 1,hasControl: 1,selector: 'joomla-editor-option~fields_modal'},
			image: {keyEvent: 'i',hasShift: 0,hasAlt: 1,hasControl: 1,selector: 'joomla-editor-option~image_modal'},
			menu: {keyEvent: 'm',hasShift: 0,hasAlt: 1,hasControl: 1,selector: 'joomla-editor-option~menu_modal'},
			module: {keyEvent: 'm',hasShift: 1,hasAlt: 1,hasControl: 1,selector: 'joomla-editor-option~module_modal'},
			pagebreak: {keyEvent: 'p',hasShift: 0,hasAlt: 1,hasControl: 1,selector: 'joomla-editor-option~pagebreak_modal'},
			readmore: {keyEvent: 'r',hasShift: 0,hasAlt: 1,hasControl: 1,selector: 'joomla-editor-option~read_more'}
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