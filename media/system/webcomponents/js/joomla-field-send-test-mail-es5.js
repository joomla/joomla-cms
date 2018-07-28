(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;(function (customElements, Joomla) {
	var JoomlaFieldSendTestMail = function (_HTMLElement) {
		_inherits(JoomlaFieldSendTestMail, _HTMLElement);

		// attributeChangedCallback(attr, oldValue, newValue) {}
		function JoomlaFieldSendTestMail() {
			_classCallCheck(this, JoomlaFieldSendTestMail);

			var _this = _possibleConstructorReturn(this, (JoomlaFieldSendTestMail.__proto__ || Object.getPrototypeOf(JoomlaFieldSendTestMail)).call(this));

			if (!Joomla) {
				throw new Error('Joomla API is not properly initiated');
			}

			if (!_this.getAttribute('uri')) {
				throw new Error('No valid url for validation');
			}
			return _this;
		}

		_createClass(JoomlaFieldSendTestMail, [{
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

		return JoomlaFieldSendTestMail;
	}(HTMLElement);

	customElements.define('joomla-field-send-test-mail', JoomlaFieldSendTestMail);
})(customElements, Joomla);

},{}]},{},[1]);
