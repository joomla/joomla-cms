(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

;(function (customElements) {
	"use strict";

	/**
  * PasswordStrength script by Thomas Kjaergaard
  * License: MIT
  * Repo: https://github.com/tkjaergaard/Password-Strength
  */

	var PasswordStrength = function () {
		function PasswordStrength(settings) {
			_classCallCheck(this, PasswordStrength);

			this.lowercase = settings.lowercase || 0;
			this.uppercase = settings.uppercase || 0;
			this.numbers = settings.numbers || 0;
			this.special = settings.special || 0;
			this.length = settings.length || 4;
		}

		_createClass(PasswordStrength, [{
			key: 'getScore',
			value: function getScore(value) {
				var score = 0,
				    mods = 0;
				var sets = ['lowercase', 'uppercase', 'numbers', 'special', 'length'];
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
					score += value.length > this.length ? 100 / mods : 100 / mods / this.length * value.length;
				}

				return score;
			}
		}, {
			key: 'calc',
			value: function calc(value, pattern, length, mods) {
				var count = value.match(pattern);
				if (count && count.length > length && length != 0) {
					return 100 / mods;
				}
				if (count && length > 0) {
					return 100 / mods / length * count.length;
				} else {
					return 0;
				}
			}
		}]);

		return PasswordStrength;
	}();

	var JoomlaFieldPassword = function (_HTMLElement) {
		_inherits(JoomlaFieldPassword, _HTMLElement);

		_createClass(JoomlaFieldPassword, [{
			key: 'minLength',
			get: function get() {
				return parseInt(this.getAttribute('min-length') || 0);
			}
		}, {
			key: 'minIntegers',
			get: function get() {
				return parseInt(this.getAttribute('min-integers') || 0);
			}
		}, {
			key: 'minSymbols',
			get: function get() {
				return parseInt(this.getAttribute('min-symbols') || 0);
			}
		}, {
			key: 'minUppercase',
			get: function get() {
				return parseInt(this.getAttribute('min-uppercase') || 0);
			}
		}, {
			key: 'minLowercase',
			get: function get() {
				return parseInt(this.getAttribute('min-lowercase') || 0);
			}
		}, {
			key: 'reveal',
			get: function get() {
				return this.getAttribute('reveal') || false;
			}
		}, {
			key: 'showText',
			get: function get() {
				return this.getAttribute('text-show') || 'Show';
			}
		}, {
			key: 'hideText',
			get: function get() {
				return this.getAttribute('text-hide') || 'Hide';
			}
		}, {
			key: 'completeText',
			get: function get() {
				return this.getAttribute('text-complete') || 'Password meets site\'s requirements';
			}
		}, {
			key: 'incompleteText',
			get: function get() {
				return this.getAttribute('text-incomplete') || 'Password does not meet site\'s requirements';
			}

			// attributeChangedCallback(attr, oldValue, newValue) {}

		}], [{
			key: 'observedAttributes',
			get: function get() {
				return ['min-length', 'min-integers', 'min-symbols', 'min-uppercase', 'min-lowercase', 'reveal', 'text-show', 'text-hide', 'text-complete', 'text-incomplete'];
			}
		}]);

		function JoomlaFieldPassword() {
			_classCallCheck(this, JoomlaFieldPassword);

			var _this = _possibleConstructorReturn(this, (JoomlaFieldPassword.__proto__ || Object.getPrototypeOf(JoomlaFieldPassword)).call(this));

			if (!window.Joomla) {
				throw new Error('Joomla API is not iniatiated!');
			}

			_this.input = _this.querySelector('input');

			if (!_this.input) {
				throw new Error('Joomla Password field requires an input element!');
			}

			_this.meterLabel = '';
			_this.meter = '';
			return _this;
		}

		_createClass(JoomlaFieldPassword, [{
			key: 'connectedCallback',
			value: function connectedCallback() {
				if (this.reveal === 'true') {
					var parent = document.createElement('div');
					var parentSpan = document.createElement('span');
					var firstSpan = document.createElement('span');
					var secondSpan = document.createElement('span');

					parent.classList.add('input-group-append');
					parentSpan.setAttribute('class', 'input-group-text');
					firstSpan.setAttribute('class', 'fa fa-eye');
					firstSpan.setAttribute('aria-hidden', 'true');
					secondSpan.setAttribute('class', 'sr-only');
					secondSpan.innerText = this.showText;

					parentSpan.appendChild(firstSpan);
					parentSpan.appendChild(secondSpan);
					parent.appendChild(parentSpan);

					var groupInput = this.querySelector('.input-group');

					if (!groupInput) {
						groupInput = document.createElement('div');
						groupInput.classList.add('input-group');

						groupInput.appendChild(this.input);
						this.appendChild(groupInput);
					}

					groupInput.appendChild(parent);

					var that = this;

					this.input = this.querySelector('input');

					// @TODO Remove Font awesome dependency
					// @TODO Remove Bootstrap dependency
					firstSpan.addEventListener('click', function () {

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
				if (this.minLength && this.minLength > 0 || this.minIntegers && this.minIntegers > 0 || this.minSymbols && this.minSymbols > 0 || this.minUppercase && this.minUppercase > 0 || this.minLowercase && this.minLowercase > 0) {
					var startClass = '';
					var initialVal = '';
					var el = void 0;

					if (!this.input.value.length) {
						startClass = ' bg-danger';
						initialVal = 0;
					}

					var i = Math.random().toString(36).substr(2, 9);

					/** Create a progress meter and the label **/
					var meter = document.createElement('div');
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
		}, {
			key: 'disconnectedCallback',
			value: function disconnectedCallback() {}

			/** Method to check the input and set the meter **/

		}, {
			key: 'getMeter',
			value: function getMeter() {

				if (!this.meter || !this.meterLabel) {
					return;
				}

				var strength = new PasswordStrength({
					lowercase: this.minLowercase ? this.minLowercase : 0,
					uppercase: this.minUppercase ? this.minUppercase : 0,
					numbers: this.minIntegers ? this.minIntegers : 0,
					special: this.minSymbols ? this.minSymbols : 0,
					length: this.minLength ? this.minLength : 4
				});

				var score = strength.getScore(this.input.value);

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
		}, {
			key: 'handler',
			value: function handler(value) {
				var strength = new PasswordStrength({
					lowercase: this.minLowercase ? this.minLowercase : 0,
					uppercase: this.minUppercase ? this.minUppercase : 0,
					numbers: this.minIntegers ? this.minIntegers : 0,
					special: this.minSymbols ? this.minSymbols : 0,
					length: this.minLength ? this.minLength : 4
				});

				if (strength.getScore(value) === 100) {
					return true;
				}

				return false;
			}
		}]);

		return JoomlaFieldPassword;
	}(HTMLElement);

	customElements.define('joomla-field-password', JoomlaFieldPassword);
})(customElements);

},{}]},{},[1]);
