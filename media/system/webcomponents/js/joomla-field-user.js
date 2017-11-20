class JoomlaFieldUser extends HTMLElement {
	static get observedAttributes() {
		return ['url', 'modal-class', 'modal-width', 'modal-height', 'input', 'input-name', 'button-select'];
	}

	get url() { return this.getAttribute('url'); }
	set url(value) { this.setAttribute('url', value); }
	get modalClass() { return this.getAttribute('modal'); }
	set modalClass(value) { this.setAttribute('modal', value); }
	get modalWidth() { return this.getAttribute('modal-width'); }
	set modalWidth(value) { this.setAttribute('modal-width', value); }
	get modalHeight() { return this.getAttribute('modal-height'); }
	set modalHeight(value) { this.setAttribute('modal-height', value); }
	get inputId() { return this.getAttribute('input'); }
	set inputId(value) { this.setAttribute('input', value); }
	get inputNameClass() { return this.getAttribute('input-name'); }
	set inputNameClass(value) { this.setAttribute('input-name', value); }
	get buttonSelectClass() { return this.getAttribute('button-select'); }
	set buttonSelectClass(value) { this.setAttribute('button-select', value); }

	// attributeChangedCallback(attr, oldValue, newValue) {}

	connectedCallback() {
		// Set up elements
		this.modal = this.querySelector(this.modalClass);
		this.modalBody = this.querySelector('.modal-body');
		this.input = this.querySelector(this.inputId);
		this.inputName = this.querySelector(this.inputNameClass);
		this.buttonSelect = this.querySelector(this.buttonSelectClass);

		// Bind events
		this.modalClose = this.modalClose.bind(this);
		this.setValue = this.setValue.bind(this);
		if (this.buttonSelect) {
			this.buttonSelect.addEventListener('click', this.modalOpen.bind(this));
			this.modal.addEventListener('hide', this.removeIframe.bind(this));

			// Check for onchange callback,
			const onchangeStr = this.input.getAttribute('data-onchange');
			let onUserSelect;
			if (onchangeStr) {
				/* eslint-disable */
				onUserSelect = new Function(onchangeStr);
				this.input.addEventListener('change', onUserSelect.bind(this.input));
				/* eslint-enable */
			}
		}
	}

	disconnectedCallback() {
		this.buttonSelect.removeEventListener('click', this);
		this.modal.removeEventListener('hide', this);
	}

	// Opens the modal
	modalOpen() {
		const self = this;

		// Reconstruct the iframe
		this.removeIframe();
		const iframe = document.createElement('iframe');
		iframe.setAttribute('name', 'field-user-modal');
		iframe.src = this.url.replace('{field-user-id}', this.input.getAttribute('id'));
		iframe.setAttribute('width', this.modalWidth);
		iframe.setAttribute('height', this.modalHeight);

		this.modalBody.appendChild(iframe);

		window.jQuery(this.modal).modal('show');

		const iframeEl = this.modalBody.querySelector('iframe');

		// handle the selection on the iframe
		iframeEl.addEventListener('load', () => {
			const iframeDoc = iframeEl.contentWindow.document;
			const buttons = [].slice.call(iframeDoc.querySelectorAll('.button-select'));

			buttons.forEach((button) => {
				button.addEventListener('click', (event) => {
					self.setValue(event.target.getAttribute('data-user-value'), event.target.getAttribute('data-user-name'));
					self.modalClose();
				});
			});
		});
	}

	// Closes the modal
	modalClose() {
		window.jQuery(this.modal).modal('hide');
		this.modalBody.innerHTML = '';
	}

	// Remove the iframe
	removeIframe() {
		this.modalBody.innerHTML = '';
	}

	// Sets the value
	setValue(value, name) {
		this.input.setAttribute('value', value);
		this.inputName.setAttribute('value', name || value);
	}
}

customElements.define('joomla-field-user', JoomlaFieldUser);
