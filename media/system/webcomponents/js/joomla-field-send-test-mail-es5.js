(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;customElements.define('joomla-field-send-test-mail', function (_HTMLElement) {
	_inherits(_class, _HTMLElement);

	// attributeChangedCallback(attr, oldValue, newValue) {}
	function _class() {
		_classCallCheck(this, _class);

		var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

		if (!Joomla) {
			throw new Error('Joomla API is not properly initiated');
		}

		if (!_this.getAttribute('uri')) {
			throw new Error('No valid url for validation');
		}
		return _this;
	}

	_createClass(_class, [{
		key: 'connectedCallback',
		value: function connectedCallback() {
			var self = this;
			var button = document.getElementById('sendtestmail');

			if (button) {
				button.addEventListener('click', function () {
					self.sendTestMail(self);
				});
			}
		}
	}, {
		key: 'sendTestMail',
		value: function sendTestMail() {
			var email_data = {
				smtpauth: this.querySelector('[name="jform[smtpauth]"]').value,
				smtpuser: this.querySelector('[name="jform[smtpuser]"]').value,
				smtppass: this.querySelector('[name="jform[smtppass]"]').value,
				smtphost: this.querySelector('[name="jform[smtphost]"]').value,
				smtpsecure: this.querySelector('[name="jform[smtpsecure]"]').value,
				smtpport: this.querySelector('[name="jform[smtpport]"]').value,
				mailfrom: this.querySelector('[name="jform[mailfrom]"]').value,
				fromname: this.querySelector('[name="jform[fromname]"]').value,
				mailer: this.querySelector('[name="jform[mailer]"]').value,
				mailonline: this.querySelector('[name="jform[mailonline]"]').value
			};

			// Remove js messages, if they exist.
			Joomla.removeMessages();

			Joomla.request({
				url: this.getAttribute('uri'),
				method: 'POST',
				data: JSON.stringify(email_data),
				perform: true,
				headers: { 'Content-Type': 'application/json' },
				onSuccess: function onSuccess(response, xhr) {
					response = JSON.parse(response);
					if (_typeof(response.messages) === 'object' && response.messages !== null) {
						Joomla.renderMessages(response.messages);
					}
				},
				onError: function onError(xhr) {
					Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
				}
			});
		}
	}]);

	return _class;
}(HTMLElement));

},{}]},{},[1]);
