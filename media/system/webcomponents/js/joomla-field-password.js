;(function(customElements) {
	"use strict";

	/**
	 * PasswordStrength script by Thomas Kjaergaard
	 * License: MIT
	 * Repo: https://github.com/tkjaergaard/Password-Strength
	 */
	class PasswordStrength {
		constructor(settings) {
			this.lowercase = settings.lowercase || 0;
			this.uppercase = settings.uppercase || 0;
			this.numbers   = settings.numbers || 0;
			this.special   = settings.special || 0;
			this.length    = settings.length || 4;
		}

		getScore(value) {
			var score = 0, mods = 0;
			var sets  = ['lowercase', 'uppercase', 'numbers', 'special', 'length'];
			for (var i = 0, l = sets.length; l > i; i++) {
				if (this.hasOwnProperty(sets[i]) && this[sets[i]] > 0) {
					mods = mods + 1;
				}
			}

			score += this.calc(value, /[a-z]/g, this.lowercase, mods);
			score += this.calc(value, /[A-Z]/g, this.uppercase, mods);
			score += this.calc(value, /[0-9]/g, this.numbers, mods);
			score += this.calc(value, /[\$\!\#\?\=\;\:\*\-\_\€\%\&\(\)\`\´]/g, this.special, mods);
			if (mods == 1) {
				score += value.length > this.length ? 100 : 100 / this.length * value.length;
			} else {
				score += value.length > this.length ? (100 / mods) : (100 / mods) / this.length * value.length;
			}

			return score;
		}

		calc(value, pattern, length, mods) {
			const count = value.match(pattern);
			if (count && count.length > length && length != 0) {
				return 100 / mods;
			}
			if (count && length > 0) {
				return (100 / mods) / length * count.length;
			} else {
				return 0;
			}
		}
	}

	class JoomlaFieldPassword extends HTMLElement {
		static get observedAttributes() {
			return ['min-length', 'min-integers', 'min-symbols', 'min-uppercase', 'min-lowercase', 'reveal', 'text-show', 'text-hide', 'text-complete', 'text-incomplete'];
		}

		get minLength() { return parseInt(this.getAttribute('min-length') || 0); }
		get minIntegers() { return parseInt(this.getAttribute('min-integers') || 0); }
		get minSymbols() { return parseInt(this.getAttribute('min-symbols') || 0); }
		get minUppercase() { return parseInt(this.getAttribute('min-uppercase') || 0); }
		get minLowercase() { return parseInt(this.getAttribute('min-lowercase') || 0); }
		get reveal() { return this.getAttribute('reveal') || false; }
		get showText() { return this.getAttribute('text-show') || 'Show'; }
		get hideText() { return this.getAttribute('text-hide') || 'Hide'; }
		get completeText() { return this.getAttribute('text-complete') || 'Password meets site\'s requirements'; }
		get incompleteText() { return this.getAttribute('text-incomplete') || 'Password does not meet site\'s requirements'; }

		// attributeChangedCallback(attr, oldValue, newValue) {}

		constructor() {
			super();

			if (!window.Joomla) {
				throw new Error('Joomla API is not iniatiated!')
			}

			this.input = this.querySelector('input');

			if (!this.input) {
				throw new Error('Joomla Password field requires an input element!')
			}

			this.meterLabel = ''
			this.meter = ''
		}

		connectedCallback() {
			if (this.reveal === 'true') {
				const parent = document.createElement('div');
				const parentSpan = document.createElement('span');
				const firstSpan = document.createElement('span');
				const secondSpan = document.createElement('span');

				parent.classList.add('input-group-append');
				parentSpan.setAttribute('class', 'input-group-text');
				firstSpan.setAttribute('class', 'fa fa-eye');
				firstSpan.setAttribute('aria-hidden', 'true');
				secondSpan.setAttribute('class', 'sr-only');
				secondSpan.innerText = this.showText;

				parentSpan.appendChild(firstSpan);
				parentSpan.appendChild(secondSpan);
				parent.appendChild(parentSpan);

				let groupInput = this.querySelector('.input-group');

				if (!groupInput) {
					groupInput = document.createElement('div');
					groupInput.classList.add('input-group');

					groupInput.appendChild(this.input);
					this.appendChild(groupInput);
				}

				groupInput.appendChild(parent);

				const that = this;

				this.input = this.querySelector('input');

				// @TODO Remove Font awesome dependency
				// @TODO Remove Bootstrap dependency
				firstSpan.addEventListener('click', () => {

					if (firstSpan.classList.contains('fa-eye')) {
						// Update the icon class
						firstSpan.classList.remove('fa-eye');
						firstSpan.classList.add('fa-eye-slash');

						// Update the input type
						that.input.type = 'text';

						// Update the text for screenreaders
						secondSpan.innerText = that.showText;
					} else {
						// Update the icon class
						firstSpan.classList.add('fa-eye');
						firstSpan.classList.remove('fa-eye-slash');

						// Update the input type
						that.input.type = 'password';

						// Update the text for screenreaders
						secondSpan.innerText = that.hideText;
					}
				});
			}

			// Meter is enabled
			// @TODO Remove Bootstrap dependency
			if (this.minLength && this.minLength > 0
				||this.minIntegers && this.minIntegers > 0
				||this.minSymbols && this.minSymbols > 0
				||this.minUppercase && this.minUppercase > 0
				||this.minLowercase && this.minLowercase > 0
			) {
				let startClass = '';
				let initialVal = '';
				let el;

				if (!this.input.value.length) {
					startClass = ' bg-danger';
					initialVal = 0;
				}

				const i = Math.random().toString(36).substr(2, 9);

				/** Create a progress meter and the label **/
				const meter = document.createElement('div');
				meter.setAttribute('class', 'progress');

				this.meter = document.createElement('div');
				this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated' + startClass);
				this.meter.style.width = 0 + initialVal;
				this.meter.max = 100;
				this.meter.setAttribute('aria-describedby', 'password-' + i);
				meter.appendChild(this.meter);

				this.meterLabel = document.createElement('div');
				this.meterLabel.setAttribute('class', 'text-xs-center');
				this.meterLabel.setAttribute('id', 'password-' + i);

				this.insertAdjacentElement('beforeend', this.meterLabel);
				this.insertAdjacentElement('beforeend', meter);

				/** Add a data attribute for the required **/
				if (this.input.value.length > 0) {
					this.input.setAttribute('required', '');
				}

				/** Add a listener for input data change **/
				this.input.addEventListener('keyup', this.getMeter.bind(this));

				// Set the validation handler
				// @TODO refactor the validation.js to reflect the changes here!
				this.setAttribute('validation-handler', 'password-strength' + '_' + Math.random().toString(36).substr(2, 9));

				if (document.formvalidator) {
					document.formvalidator.setHandler(this.getAttribute('validation-handler'), this.handler.bind(this));
				}
			}
		}

		disconnectedCallback() {

		}

		/** Method to check the input and set the meter **/
		getMeter() {

			if (!this.meter || !this.meterLabel) {
				return;
			}

			const strength = new PasswordStrength({
				lowercase: this.minLowercase ? this.minLowercase : 0,
				uppercase: this.minUppercase ? this.minUppercase : 0,
				numbers  : this.minIntegers ? this.minIntegers : 0,
				special  : this.minSymbols ? this.minSymbols : 0,
				length   : this.minLength ? this.minLength : 4
			});

			const score = strength.getScore(this.input.value);

			if (score > 79) {
				this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning');
				this.meterLabel.innerHTML = this.completeText;
			}
			if (score > 64 && score < 80) {
				this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning');
				this.meterLabel.innerHTML = this.incompleteText;
			}
			if (score > 50 && score < 65) {
				this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning');
				this.meterLabel.innerHTML = this.incompleteText;
			}
			if (score > 40 && score < 51) {
				this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning');
				this.meterLabel.innerHTML = this.incompleteText;
			}
			if (score < 41) {
				this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-danger');
				this.meterLabel.innerHTML = this.incompleteText;
			}
			if (score === 100) {
				this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-success');
			}
			this.meter.style.width = score + '%';

			if (!this.input.value.length) {
				this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated');
				this.meterLabel.innerHTML = '';
				this.input.setAttribute('required', '');
			}
		}

		handler(value) {
			const strength = new PasswordStrength({
				lowercase: this.minLowercase ? this.minLowercase : 0,
				uppercase: this.minUppercase ? this.minUppercase : 0,
				numbers  : this.minIntegers ? this.minIntegers : 0,
				special  : this.minSymbols ? this.minSymbols : 0,
				length   : this.minLength ? this.minLength : 4
			});

			if (strength.getScore(value) === 100) {
				return true;
			}

			return false;
		}
	}

	customElements.define('joomla-field-password', JoomlaFieldPassword);

})(customElements);
