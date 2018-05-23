((customElements) => {
	class JoomlaGroupButtonElement extends HTMLElement {
		constructor() {
			super();

			this.buttons = [];
			this.radios = [];

			// Do some binding
			this.initCheckboxes = this.initCheckboxes.bind(this);
			this.initRadios = this.initRadios.bind(this);
			this.onClickCheckboxes = this.onClickCheckboxes.bind(this);
			this.onClickRadio = this.onClickRadio.bind(this);
		}

		connectedCallback() {
			this.buttons = [].slice.call(this.querySelectorAll('[type="checkbox"]'));

			if (this.buttons.length) {
				// Checkboxes
				this.initCheckboxes();
			} else {
				// Radios
				this.radios = [].slice.call(this.querySelectorAll('[type="radio"]'));

				if (this.radios.length) {
					this.initRadios();
				}
			}
		}

		disconnectedCallback() {
			if (this.buttons.length) {
				// remove events
				this.buttons.forEach((button) => {
					button.removeEventListener('click', this.onClickCheckboxes);
				});
			} else {
				// Radios
				if (this.radios.length) {
					this.radios.forEach((radio) => {
						radio.removeEventListener('click', this.onClickRadio);
					});
				}
			}
		}

		initCheckboxes() {
			this.buttons.forEach((button) => {
				if (button.parentNode.tagName.toLowerCase() !== 'label') {
					return;
				}
				if (button.getAttribute('checked') || button.parentNode.classList.contains('active')) {
					button.setAttribute('checked', '');
					button.parentNode.setAttribute('aria-pressed', 'true');
				} else {
					button.removeAttribute('checked');
					button.parentNode.setAttribute('aria-pressed', 'false');
				}

				button.setAttribute('tabindex', 0);
				button.addEventListener('click', this.onClickCheckboxes);
			});
		}

		initRadios() {
			this.radios.forEach((radio) => {
				if (radio.parentNode.tagName.toLowerCase() !== 'label') {
					return;
				}
				if (radio.getAttribute('checked') || radio.parentNode.classList.contains('active')) {
					radio.setAttribute('checked', '');
					radio.parentNode.setAttribute('aria-pressed', 'true');
				} else {
					radio.removeAttribute('checked');
					radio.parentNode.setAttribute('aria-pressed', 'false');
				}

				radio.addEventListener('click', this.onClickRadio);
			});
		}

		onClickRadio(e) {
			this.clearAllRadios();
			if (e.currentTarget.checked) {
				e.currentTarget.setAttribute('checked', '');
				e.currentTarget.parentNode.classList.add('active');
				e.currentTarget.parentNode.setAttribute('aria-pressed', 'true');
			} else {
				e.currentTarget.removeAttribute('checked');
				e.currentTarget.parentNode.classList.remove('active');
				e.currentTarget.parentNode.setAttribute('aria-pressed', 'false');
			}
		}

		onClickCheckboxes(e) {
			if (e.currentTarget.checked) {
				e.currentTarget.setAttribute('checked', '');
				e.currentTarget.parentNode.classList.add('active');
				e.currentTarget.parentNode.setAttribute('aria-pressed', 'true');
			} else {
				e.currentTarget.removeAttribute('checked');
				e.currentTarget.parentNode.classList.remove('active');
				e.currentTarget.parentNode.setAttribute('aria-pressed', 'false');
			}
		}

		clearAllRadios() {
			this.radios.forEach((radio) => {
				radio.removeAttribute('checked');
				if (radio.parentNode.tagName.toLowerCase() === 'label') {
					radio.parentNode.classList.remove('active');
					radio.parentNode.setAttribute('aria-pressed', 'false');
				}
			});
		}
	}

	customElements.define('joomla-field-group-buttons', JoomlaGroupButtonElement);
})(customElements);
