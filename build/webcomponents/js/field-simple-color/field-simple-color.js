/**
 * Based on:
 * Very simple jQuery Color Picker
 * Copyright (C) 2012 Tanguy Krotoff
 * Licensed under the MIT license
 *
 * ADAPTED BY: Dimitris Grammatikogiannis
 *
 */
((customElements) => {
	const KEYCODE = {
		TAB: 9,
		ESC: 27,
	};

	class JoomlaFieldSimpleColor extends HTMLElement {
		constructor() {
			super();

			// Define some variables
			this.select  = '';
			this.options = [];
			this.icon    = '';
			this.panel   = '';
			this.buttons = [];
			this.focusableElements = null;
			this.focusableSelectors = ['a[href]', 'area[href]', 'input:not([disabled])', 'select:not([disabled])', 'textarea:not([disabled])', 'button:not([disabled])', 'iframe', 'object', 'embed', '[contenteditable]', '[tabindex]:not([tabindex^="-"])'];
		}

		connectedCallback() {
			this.select = this.querySelector('select');

			if (!this.select) {
				throw new Error('Simple color field requires a select element')
			}

			this.options = [].slice.call(this.select.querySelectorAll('option'));

			this.select.classList.add('hidden');

			// Build the pop up
			this.options.forEach((option) => {
				let color = option.value;
				let clss = 'swatch';

				if (color === 'none') {
					clss += ' nocolor';
					color = 'transparent';
				}
				if (option.selected) {
					clss += ' active';
				}

				const el = document.createElement('button');
				el.setAttribute('class', clss);
				el.style.backgroundColor = color;
				el.setAttribute('type', 'button');
				el.innerHTML = `<span class="sr-only">${this.textColor} ${color.replace('#', '').split('').join(', ')}</span>`;

				this.buttons.push(el)
			});

			// Add a close button
			const close = document.createElement('button');
			close.setAttribute('class', 'btn-close');
			close.setAttribute('type', 'button');
			close.innerHTML = this.textClose;

			this.buttons.push(close);

			let color = this.select.value;
			let clss  = '';

			if (color === 'none') {
				clss += ' nocolor';
				color = 'transparent';
			}

			this.icon = document.createElement('button');

			if (clss) {
				this.icon.setAttribute('class', clss);
			}

			this.icon.setAttribute('type', 'button');
			this.icon.setAttribute('tabindex', '0');
			this.icon.style.backgroundColor = color
			this.icon.innerHTML             = '<span class="sr-only">' + this.textSelect + '</span>';
			this.select.insertAdjacentElement('beforebegin', this.icon);
			this.icon.addEventListener('click', this.show.bind(this));

			this.panel = document.createElement('div');
			this.panel.classList.add('simplecolors-panel');

			this.buttons.forEach((el) => {
				if (el.classList.contains('btn-close')) {
					el.addEventListener('click', this.hide.bind(this));
				} else {
					el.addEventListener('click', this.colorSelect.bind(this));
				}

				this.panel.insertAdjacentElement('beforeend', el);
			});

			this.appendChild(this.panel);

			this.focusableElements = [].slice.call(this.panel.querySelectorAll(this.focusableSelectors.join()));

			this.keys = this.keys .bind(this);
			this.hide = this.hide.bind(this);
			this.mousedown = this.mousedown.bind(this);
		}

		disconnectedCallback() {

		}

		static get observedAttributes() {
			return ['text-select', 'text-color'];
		}

		get textSelect() { return this.getAttribute('text-select'); }
		get textColor() { return this.getAttribute('text-color'); }
		get textClose() { return this.getAttribute('text-close'); }

		// Show the panel
		show() {
			document.addEventListener('mousedown', this.hide);
			this.addEventListener('keydown', this.keys);
			this.panel.addEventListener('mousedown', this.mousedown);
			this.panel.setAttribute('data-open', '');

			const focused = this.panel.querySelector('button');

			if (focused) {
				focused.focus();
			}
		}

		// Hide panel
		hide() {
			document.removeEventListener('mousedown', this.hide, false);
			this.removeEventListener('keydown', this.keys);

			if (this.panel.hasAttribute('data-open')) {
				this.panel.removeAttribute('data-open');
			}

			this.icon.focus();
		}

		colorSelect(e) {
			let color   = '';
			let bgcolor = '';
			let clss    = '';

			if (e.target.classList.contains('nocolor')) {
				color   = 'none';
				bgcolor = 'transparent';
				clss    = 'nocolor';
			} else {
				color   = this.rgb2hex(e.target.style.backgroundColor);
				bgcolor = color;
			}

			// Reset the active class
			this.buttons.forEach((el) => {
				if (el.classList.contains('active')) {
					el.classList.remove('active')
				}
			});

			// Add the active class to the selected button
			e.target.classList.add('active');

			this.icon.classList.remove('nocolor');
			this.icon.setAttribute('class', clss);
			this.icon.style.backgroundColor = bgcolor;

			// Hide the panel
			this.hide();

			// Change select value
			this.options.forEach((el) => {
				if (el.selected) {
					el.removeAttribute('selected');
				}

				if (el.value === bgcolor) {
					el.setAttribute('selected', '');
				}
			});
		}

		keys(e) {
			if (e.keyCode === KEYCODE.ESC) {
				this.hide();
			}

			if (e.keyCode === KEYCODE.TAB) {
				// Get the index of the current active element
				const focusedIndex = this.focusableElements.indexOf(document.activeElement);

				// If first element is focused and shiftkey is in use, focus last item within modal
				if (e.shiftKey && (focusedIndex === 0 || focusedIndex === -1)) {
					this.focusableElements[this.focusableElements.length - 1].focus();
					e.preventDefault();
				}
				// If last element is focused and shiftkey is not in use, focus first item within modal
				if (!e.shiftKey && focusedIndex === this.focusableElements.length - 1) {
					this.focusableElements[0].focus();
					e.preventDefault();
				}
			}
		}

		// Prevents the mousedown event from "eating" the click event.
		mousedown(e) {
			e.stopPropagation();
			e.preventDefault();
		}

		/**
		 * Converts a RGB color to its hexadecimal value.
		 * See http://stackoverflow.com/questions/1740700/get-hex-value-rather-than-rgb-value-using-$
		 */
		rgb2hex(rgb) {
			const hex = (x) => {
				return ("0" + parseInt(x, 10).toString(16)).slice(-2);
			};

			const matches = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);

			return '#' + hex(matches[1]) + hex(matches[2]) + hex(matches[3]);
		}
	}

	customElements.define('joomla-field-simple-color', JoomlaFieldSimpleColor);
})(customElements);
