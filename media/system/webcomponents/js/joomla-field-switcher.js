;((customElements) => {
	// Keycodes
	const KEYCODE = {
		ENTER: 13,
		SPACE: 32,
	};

	customElements.define('joomla-field-switcher', 	class extends HTMLElement {
		/* Attributes to monitor */
		static get observedAttributes() { return ['type', 'off-text', 'on-text']; }

		get type() { return this.getAttribute('type'); }
		set type(value) { return this.setAttribute('type', value); }
		get offText() { return this.getAttribute('off-text') || 'Off'; }
		get onText() { return this.getAttribute('on-text') || 'On'; }

		// attributeChangedCallback(attr, oldValue, newValue) {}

		constructor() {
			super();

			this.inputs = [];
			this.spans = [];
			this.inputsContainer = '';
			this.newActive = '';

			this.css = `joomla-field-switcher{-webkit-box-sizing:border-box;box-sizing:border-box;display:block;height:28px}joomla-field-switcher .switcher{position:relative;-webkit-box-sizing:border-box;box-sizing:border-box;display:inline-block;width:62px;height:28px;vertical-align:middle;cursor:pointer;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background-color:#f2f2f2;background-clip:content-box;border:1px solid rgba(0,0,0,0.18);border-radius:0;-webkit-box-shadow:0 0 0 0 #dfdfdf inset;box-shadow:0 0 0 0 #dfdfdf inset;-webkit-transition:border .4s ease 0s,-webkit-box-shadow .4s ease 0s;transition:border .4s ease 0s,-webkit-box-shadow .4s ease 0s;transition:border .4s ease 0s,box-shadow .4s ease 0s;transition:border .4s ease 0s,box-shadow .4s ease 0s,-webkit-box-shadow .4s ease 0s}joomla-field-switcher .switcher.active{-webkit-transition:border .4s ease 0s,background-color 1.2s ease 0s,-webkit-box-shadow .4s ease 0s;transition:border .4s ease 0s,background-color 1.2s ease 0s,-webkit-box-shadow .4s ease 0s;transition:border .4s ease 0s,box-shadow .4s ease 0s,background-color 1.2s ease 0s;transition:border .4s ease 0s,box-shadow .4s ease 0s,background-color 1.2s ease 0s,-webkit-box-shadow .4s ease 0s}joomla-field-switcher .switcher.active .switch{left:calc((62px / 2) - (1px * 2))}joomla-field-switcher input{position:absolute;top:0;left:0;z-index:2;width:62px;height:28px;padding:0;margin:0;cursor:pointer;opacity:0}joomla-field-switcher .switch{position:absolute;top:0;left:0;width:calc(62px / 2);height:calc(28px - (1px * 2));background:#fff;border-radius:0;-webkit-box-shadow:0 1px 3px rgba(0,0,0,0.15);box-shadow:0 1px 3px rgba(0,0,0,0.15);-webkit-transition:left .2s ease 0s;transition:left .2s ease 0s}joomla-field-switcher .switcher:focus .switch{-webkit-animation:switcherPulsate 1.5s infinite;animation:switcherPulsate 1.5s infinite}joomla-field-switcher input:checked{z-index:0}joomla-field-switcher .switcher-labels{position:relative}joomla-field-switcher .switcher-labels span{position:absolute;top:0;left:10px;color:#868e96;visibility:hidden;opacity:0;-webkit-transition:all .2s ease-in-out;transition:all .2s ease-in-out}joomla-field-switcher .switcher-labels span.active{visibility:visible;opacity:1;-webkit-transition:all .2s ease-in-out;transition:all .2s ease-in-out}joomla-field-switcher[type="primary"] .switcher.active{background-color:#1e87f0;border-color:#1e87f0;-webkit-box-shadow:0 0 0 calc(28px / 2) #1e87f0 inset;box-shadow:0 0 0 calc(28px / 2) #1e87f0 inset}joomla-field-switcher[type="secondary"] .switcher.active{background-color:#868e96;border-color:#868e96;-webkit-box-shadow:0 0 0 calc(28px / 2) #868e96 inset;box-shadow:0 0 0 calc(28px / 2) #868e96 inset}joomla-field-switcher[type="success"] .switcher.active{background-color:#32d296;border-color:#32d296;-webkit-box-shadow:0 0 0 calc(28px / 2) #32d296 inset;box-shadow:0 0 0 calc(28px / 2) #32d296 inset}joomla-field-switcher[type="warning"] .switcher.active{background-color:#faa05a;border-color:#faa05a;-webkit-box-shadow:0 0 0 calc(28px / 2) #faa05a inset;box-shadow:0 0 0 calc(28px / 2) #faa05a inset}joomla-field-switcher[type="danger"] .switcher.active{background-color:#f0506e;border-color:#f0506e;-webkit-box-shadow:0 0 0 calc(28px / 2) #f0506e inset;box-shadow:0 0 0 calc(28px / 2) #f0506e inset}@-webkit-keyframes switcherPulsate{0%{-webkit-box-shadow:0 0 0 0 rgba(66,133,244,0.55);box-shadow:0 0 0 0 rgba(66,133,244,0.55)}70%{-webkit-box-shadow:0 0 0 10px rgba(66,133,244,0);box-shadow:0 0 0 10px rgba(66,133,244,0)}100%{-webkit-box-shadow:0 0 0 0 rgba(66,133,244,0);box-shadow:0 0 0 0 rgba(66,133,244,0)}}@keyframes switcherPulsate{0%{-webkit-box-shadow:0 0 0 0 rgba(66,133,244,0.55);box-shadow:0 0 0 0 rgba(66,133,244,0.55)}70%{-webkit-box-shadow:0 0 0 10px rgba(66,133,244,0);box-shadow:0 0 0 10px rgba(66,133,244,0)}100%{-webkit-box-shadow:0 0 0 0 rgba(66,133,244,0);box-shadow:0 0 0 0 rgba(66,133,244,0)}}`;
			this.styleEl = document.createElement('style');
			this.styleEl.id = 'joomla-field-switcher-css';
			this.styleEl.innerHTML = this.css;

			if (!document.head.querySelector('#joomla-field-switcher-css')) {
				document.head.appendChild(this.styleEl)
			}
		}

		/* Lifecycle, element appended to the DOM */
		connectedCallback() {
			this.inputs = [].slice.call(this.querySelectorAll('input'));

			if (this.inputs.length !== 2 || this.inputs[0].type !== 'radio') {
				throw new Error('`Joomla-switcher` requires two inputs type="checkbox"');
			}

			// Create the markup
			this.createMarkup.bind(this)();

			this.inputsContainer = this.firstElementChild;

			this.inputsContainer.setAttribute('role', 'switch');

			if (this.inputs[1].checked) {
				this.inputs[1].parentNode.classList.add('active');
				this.spans[1].classList.add('active');

				// Aria-label ONLY in the container span!
				this.inputsContainer.setAttribute('aria-label', this.spans[1].innerHTML);
			} else {
				this.spans[0].classList.add('active');

				// Aria-label ONLY in the container span!
				this.inputsContainer.setAttribute('aria-label', this.spans[0].innerHTML);
			}

			this.inputs.forEach((switchEl) => {
				// Add the active class on click
				switchEl.addEventListener('click', this.toggle.bind(this));
			});

			this.inputsContainer.addEventListener('keydown', this.keyEvents.bind(this));
		}

		/* Lifecycle, element removed from the DOM */
		disconnectedCallback() {
			this.removeEventListener('joomla.switcher.toggle', this.toggle, true);
			this.removeEventListener('click', this.switch, true);
			this.removeEventListener('keydown', this.keydown, true);
		}

		/* Method to dispatch events */
		dispatchCustomEvent(eventName) {
			const OriginalCustomEvent = new CustomEvent(eventName, { bubbles: true, cancelable: true });
			OriginalCustomEvent.relatedTarget = this;
			this.dispatchEvent(OriginalCustomEvent);
			this.removeEventListener(eventName, this);
		}

		/** Method to build the switch */
		createMarkup() {
			let checked = 0;

			// Create the first 'span' wrapper
			const spanFirst = document.createElement('span');
			spanFirst.classList.add('switcher');
			spanFirst.setAttribute('tabindex', 0);

			// If no type has been defined, the default as "success"
			if (!this.type) {
				this.setAttribute('type', 'success');
			}

			const switchEl = document.createElement('span');
			switchEl.classList.add('switch');

			this.inputs.forEach((input, index) => {
				// Remove the tab focus from the inputs
				input.setAttribute('tabindex', '-1');

				if (input.checked) {
					spanFirst.setAttribute('aria-checked', true);
				}

				spanFirst.appendChild(input);

				if (index === 1 && input.checked) {
					checked = 1;
				}
			});

			spanFirst.appendChild(switchEl);

			// Create the second 'span' wrapper
			const spanSecond = document.createElement('span');
			spanSecond.classList.add('switcher-labels');

			const labelFirst = document.createElement('span');
			labelFirst.classList.add('switcher-label-0');
			labelFirst.innerText = this.offText;

			const labelSecond = document.createElement('span');
			labelSecond.classList.add('switcher-label-1');
			labelSecond.innerText = this.onText;

			if (checked === 0) {
				labelFirst.classList.add('active');
			} else {
				labelSecond.classList.add('active');
			}

			this.spans.push(labelFirst);
			this.spans.push(labelSecond);
			spanSecond.appendChild(labelFirst);
			spanSecond.appendChild(labelSecond);

			// Append everything back to the main element
			this.appendChild(spanFirst);
			this.appendChild(spanSecond);
		}

		/** Method to toggle the switch */
		switch() {
			this.spans.forEach((span) => {
				span.classList.remove('active');
			});

			if (this.inputsContainer.classList.contains('active')) {
				this.inputsContainer.classList.remove('active');
			} else {
				this.inputsContainer.classList.add('active');
			}

			// Remove active class from all inputs
			this.inputs.forEach((input) => {
				input.classList.remove('active');
			});

			// Check if active
			if (this.newActive === 1) {
				this.inputs[this.newActive].classList.add('active');
				this.inputs[1].setAttribute('checked', '');
				this.inputs[0].removeAttribute('checked');
				this.inputsContainer.setAttribute('aria-checked', true);

				// Aria-label ONLY in the container span!
				this.inputsContainer.setAttribute('aria-label', this.spans[1].innerHTML);

				// Dispatch the "joomla.switcher.on" event
				this.dispatchCustomEvent('joomla.switcher.on');
			} else {
				this.inputs[1].removeAttribute('checked');
				this.inputs[0].setAttribute('checked', '');
				this.inputs[0].classList.add('active');
				this.inputsContainer.setAttribute('aria-checked', false);

				// Aria-label ONLY in the container span!
				this.inputsContainer.setAttribute('aria-label', this.spans[0].innerHTML);

				// Dispatch the "joomla.switcher.off" event
				this.dispatchCustomEvent('joomla.switcher.off');
			}

			this.spans[this.newActive].classList.add('active');
		}

		/** Method to toggle the switch */
		toggle() {
			this.newActive = this.inputs[1].classList.contains('active') ? 0 : 1;

			this.switch.bind(this)();
		}

		keyEvents(event) {
			if (event.keyCode === KEYCODE.ENTER || event.keyCode === KEYCODE.SPACE) {
				event.preventDefault();
				this.newActive = this.inputs[1].classList.contains('active') ? 0 : 1;

				this.switch.bind(this)();
			}
		}
	});
})(customElements);
