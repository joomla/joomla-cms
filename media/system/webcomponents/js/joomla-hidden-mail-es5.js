(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;customElements.define('joomla-hidden-mail', function (_HTMLElement) {
	_inherits(_class, _HTMLElement);

	function _class() {
		_classCallCheck(this, _class);

		return _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).apply(this, arguments));
	}

	_createClass(_class, [{
		key: 'connectedCallback',
		value: function connectedCallback() {
			var newEl = void 0;

			if (this.getAttribute('is-link') === '1') {
				newEl = document.createElement('a');
				newEl.setAttribute('href', 'mailto:' + window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last')));

				// Get all of the original element attributes, and pass them to the link
				for (var i = 0, l = this.attributes.length; i < l; ++i) {
					var nodeName = this.attributes.item(i).nodeName;

					if (nodeName) {
						// We do care for some attributes
						if (['is-link', 'is-email', 'first', 'last', 'text'].indexOf(nodeName) > -1) {
							continue;
						}

						var nodeValue = this.attributes.item(i).nodeValue;

						newEl.setAttribute(nodeName, nodeValue);
					}
				}
			} else {
				newEl = document.createElement('span');
			}

			if (this.getAttribute('text')) {
				newEl.innerHTML = window.atob(this.getAttribute('text'));
			} else {
				newEl.innerText = window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last'));
			}

			// Remove the noscript message
			this.innerText = '';

			// Display the new element
			this.appendChild(newEl);
		}
	}]);

	return _class;
}(HTMLElement));

},{}]},{},[1]);
