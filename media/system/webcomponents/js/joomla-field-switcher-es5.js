(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;(function (customElements) {
	// Keycodes
	var KEYCODE = {
		ENTER: 13,
		SPACE: 32
	};

	customElements.define('joomla-field-switcher', function (_HTMLElement) {
		_inherits(_class, _HTMLElement);

		_createClass(_class, [{
			key: 'type',
			get: function get() {
				return this.getAttribute('type');
			},
			set: function set(value) {
				return this.setAttribute('type', value);
			}
		}, {
			key: 'offText',
			get: function get() {
				return this.getAttribute('off-text') || 'Off';
			}
		}, {
			key: 'onText',
			get: function get() {
				return this.getAttribute('on-text') || 'On';
			}

			// attributeChangedCallback(attr, oldValue, newValue) {}

		}], [{
			key: 'observedAttributes',

			/* Attributes to monitor */
			get: function get() {
				return ['type', 'off-text', 'on-text'];
			}
		}]);

		function _class() {
			_classCallCheck(this, _class);

			var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

			_this.inputs = [];
			_this.spans = [];
			_this.inputsContainer = '';
			_this.newActive = '';

			_this.css = '{{CSS_CONTENTS_AUTOMATICALLY_INSERTED_HERE}}';
			_this.styleEl = document.createElement('style');
			_this.styleEl.id = 'joomla-field-switcher-css';
			_this.styleEl.innerHTML = _this.css;

			if (!document.head.querySelector('#joomla-field-switcher-css')) {
				document.head.appendChild(_this.styleEl);
			}
			return _this;
		}

		/* Lifecycle, element appended to the DOM */


		_createClass(_class, [{
			key: 'connectedCallback',
			value: function connectedCallback() {
				var _this2 = this;

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

				this.inputs.forEach(function (switchEl) {
					// Add the active class on click
					switchEl.addEventListener('click', _this2.toggle.bind(_this2));
				});

				this.inputsContainer.addEventListener('keydown', this.keyEvents.bind(this));
			}

			/* Lifecycle, element removed from the DOM */

		}, {
			key: 'disconnectedCallback',
			value: function disconnectedCallback() {
				this.removeEventListener('joomla.switcher.toggle', this.toggle, true);
				this.removeEventListener('click', this.switch, true);
				this.removeEventListener('keydown', this.keydown, true);
			}

			/* Method to dispatch events */

		}, {
			key: 'dispatchCustomEvent',
			value: function dispatchCustomEvent(eventName) {
				var OriginalCustomEvent = new CustomEvent(eventName, { bubbles: true, cancelable: true });
				OriginalCustomEvent.relatedTarget = this;
				this.dispatchEvent(OriginalCustomEvent);
				this.removeEventListener(eventName, this);
			}

			/** Method to build the switch */

		}, {
			key: 'createMarkup',
			value: function createMarkup() {
				var checked = 0;

				// Create the first 'span' wrapper
				var spanFirst = document.createElement('span');
				spanFirst.classList.add('switcher');
				spanFirst.setAttribute('tabindex', 0);

				// If no type has been defined, the default as "success"
				if (!this.type) {
					this.setAttribute('type', 'success');
				}

				var switchEl = document.createElement('span');
				switchEl.classList.add('switch');

				this.inputs.forEach(function (input, index) {
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
				var spanSecond = document.createElement('span');
				spanSecond.classList.add('switcher-labels');

				var labelFirst = document.createElement('span');
				labelFirst.classList.add('switcher-label-0');
				labelFirst.innerText = this.offText;

				var labelSecond = document.createElement('span');
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

		}, {
			key: 'switch',
			value: function _switch() {
				this.spans.forEach(function (span) {
					span.classList.remove('active');
				});

				if (this.inputsContainer.classList.contains('active')) {
					this.inputsContainer.classList.remove('active');
				} else {
					this.inputsContainer.classList.add('active');
				}

				// Remove active class from all inputs
				this.inputs.forEach(function (input) {
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

		}, {
			key: 'toggle',
			value: function toggle() {
				this.newActive = this.inputs[1].classList.contains('active') ? 0 : 1;

				this.switch.bind(this)();
			}
		}, {
			key: 'keyEvents',
			value: function keyEvents(event) {
				if (event.keyCode === KEYCODE.ENTER || event.keyCode === KEYCODE.SPACE) {
					event.preventDefault();
					this.newActive = this.inputs[1].classList.contains('active') ? 0 : 1;

					this.switch.bind(this)();
				}
			}
		}]);

		return _class;
	}(HTMLElement));
})(customElements);

},{}]},{},[1]);
