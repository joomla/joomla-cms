(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;(function (customElements, Joomla) {
	var JoomlaFieldPermissions = function (_HTMLElement) {
		_inherits(JoomlaFieldPermissions, _HTMLElement);

		function JoomlaFieldPermissions() {
			_classCallCheck(this, JoomlaFieldPermissions);

			var _this = _possibleConstructorReturn(this, (JoomlaFieldPermissions.__proto__ || Object.getPrototypeOf(JoomlaFieldPermissions)).call(this));

			if (!Joomla) {
				throw new Error('Joomla API is not properly initiated');
			}

			if (!_this.getAttribute('data-uri')) {
				throw new Error('No valid url for validation');
			}
			return _this;
		}

		_createClass(JoomlaFieldPermissions, [{
			key: 'connectedCallback',
			value: function connectedCallback() {
				var _this2 = this;

				var buttonDataSelector = 'data-onchange-task';
				var buttons = [].slice.call(document.querySelectorAll('[' + buttonDataSelector + ']'));

				if (buttons) {
					buttons.forEach(function (button) {
						button.addEventListener('change', function (e) {
							e.preventDefault();
							var task = e.target.getAttribute(buttonDataSelector);
							if (task == 'permissions.apply') {
								_this2.sendPermissions(e);
							}
						});
					});
				}
			}
		}, {
			key: 'sendPermissions',
			value: function sendPermissions(event) {
				var target = event.target;
				//set the icon while storing the values
				var icon = document.getElementById('icon_' + target.id);
				icon.removeAttribute('class');
				icon.setAttribute('class', 'fa fa-spinner fa-spin');

				//get values add prepare GET-Parameter
				var asset = 'not';
				var component = this.getUrlParam('component');
				var extension = this.getUrlParam('extension');
				var option = this.getUrlParam('option');
				var view = this.getUrlParam('view');
				var title = component;
				var value = target.value;
				var context = '';

				if (document.getElementById('jform_context')) {
					context = document.getElementById('jform_context').value;
					context = context.split('.')[0];
				}

				if (option == 'com_config' && component == false && extension == false) {
					asset = 'root.1';
				} else if (extension == false && view == 'component') {
					asset = component;
				} else if (context) {
					if (view == 'group') {
						asset = context + '.fieldgroup.' + this.getUrlParam('id');
					} else {
						asset = context + '.field.' + this.getUrlParam('id');
					}
					title = document.getElementById('jform_title').value;
				} else if (extension != false && view != false) {
					asset = extension + '.' + view + '.' + this.getUrlParam('id');
					title = document.getElementById('jform_title').value;
				} else if (extension == false && view != false) {
					asset = option + '.' + view + '.' + this.getUrlParam('id');
					title = document.getElementById('jform_title').value;
				}

				var id = target.id.replace('jform_rules_', '');
				var lastUnderscoreIndex = id.lastIndexOf('_');

				var permissionData = {
					comp: asset,
					action: id.substring(0, lastUnderscoreIndex),
					rule: id.substring(lastUnderscoreIndex + 1),
					value: value,
					title: title
				};

				// Remove JS messages, if they exist.
				Joomla.removeMessages();

				// Ajax request
				Joomla.request({
					url: this.getAttribute('data-uri'),
					method: 'POST',
					data: JSON.stringify(permissionData),
					perform: true,
					headers: { 'Content-Type': 'application/json' },
					onSuccess: function onSuccess(response, xhr) {
						try {
							response = JSON.parse(response);
						} catch (e) {
							console.log(e);
						}

						icon.removeAttribute('class');

						// Check if everything is OK
						if (response.data && response.data.result === true) {
							icon.setAttribute('class', 'fa fa-check');

							var badgeSpan = target.parentNode.parentNode.nextElementSibling.querySelector('span');
							badgeSpan.removeAttribute('class');
							badgeSpan.setAttribute('class', response.data['class']);
							badgeSpan.innerHTML = response.data.text;
						}

						// Render messages, if any. There are only message in case of errors.
						if (_typeof(response.messages) === 'object' && response.messages !== null) {
							Joomla.renderMessages(response.messages);

							if (response.data && response.data.result === true) {
								icon.setAttribute('class', 'fa fa-check');
							} else {
								icon.setAttribute('class', 'fa fa-times');
							}
						}
					},
					onError: function onError(xhr) {
						// Remove the spinning icon.
						icon.removeAttribute('style');

						Joomla.renderMessages(Joomla.ajaxErrorsMessages(jqXHR, textStatus, error));
						icon.setAttribute('class', 'fa fa-times');
					}
				});
			}
		}, {
			key: 'getUrlParam',
			value: function getUrlParam(variable) {
				var query = window.location.search.substring(1);
				var vars = query.split('&');

				for (var i = 0; i < vars.length; i += 1) {
					var pair = vars[i].split('=');
					if (pair[0] == variable) {
						return pair[1];
					}
				}
				return false;
			}
		}]);

		return JoomlaFieldPermissions;
	}(HTMLElement);

	customElements.define('joomla-field-permissions', JoomlaFieldPermissions);
})(customElements, Joomla);

},{}]},{},[1]);
