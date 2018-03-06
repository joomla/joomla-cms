(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

(function () {
	var JoomlaIframe = function (_HTMLElement) {
		_inherits(JoomlaIframe, _HTMLElement);

		function JoomlaIframe() {
			_classCallCheck(this, JoomlaIframe);

			return _possibleConstructorReturn(this, (JoomlaIframe.__proto__ || Object.getPrototypeOf(JoomlaIframe)).apply(this, arguments));
		}

		_createClass(JoomlaIframe, [{
			key: 'connectedCallback',
			value: function connectedCallback() {
				this.iframe = document.createElement('iframe');

				this.iframe.setAttribute('name', this.iframeName);
				this.iframe.setAttribute('src', this.iframeSrc);
				this.iframe.setAttribute('width', this.iframeWidth);
				this.iframe.setAttribute('height', this.iframeHeight);
				this.iframe.setAttribute('scrolling', this.iframeScrolling);
				this.iframe.setAttribute('frameborder', this.iframeBorder);
				this.iframe.setAttribute('class', this.iframeClass);
				this.iframe.setAttribute('title', this.iframeTitle);

				// Generate a random unique ID
				this.iframe.setAttribute('id', 'iframe-' + Date.now().toString(36) + Math.random().toString(36).substr(2, 5));

				if (this.iframeAutoHeight) {
					this.iframe.addEventListener('load', this.adjustHeight.bind(this), false);
				}

				this.appendChild(this.iframe);
			}
		}, {
			key: 'adjustHeight',
			value: function adjustHeight() {
				var doc = this.iframe.contentWindow.document;
				var height = doc.body.scrollHeight || 0;
				this.iframe.setAttribute('height', height + 60 + 'px');
			}
		}, {
			key: 'iframeAutoHeight',
			get: function get() {
				return this.getAttribute('iframe-auto-height') === '1';
			}
		}, {
			key: 'iframeName',
			get: function get() {
				return this.getAttribute('iframe-name');
			}
		}, {
			key: 'iframeSrc',
			get: function get() {
				return this.getAttribute('iframe-src');
			}
		}, {
			key: 'iframeWidth',
			get: function get() {
				return this.getAttribute('iframe-width');
			}
		}, {
			key: 'iframeHeight',
			get: function get() {
				return this.getAttribute('iframe-height');
			}
		}, {
			key: 'iframeScrolling',
			get: function get() {
				return this.getAttribute('iframe-scrolling') === '1';
			}
		}, {
			key: 'iframeBorder',
			get: function get() {
				return this.getAttribute('iframe-border') === '1';
			}
		}, {
			key: 'iframeClass',
			get: function get() {
				return this.getAttribute('iframe-class');
			}
		}, {
			key: 'iframeTitle',
			get: function get() {
				return this.getAttribute('iframe-title');
			}
		}], [{
			key: 'observedAttributes',
			get: function get() {
				return ['iframe-auto-height', 'iframe-name', 'iframe-src', 'iframe-width', 'iframe-height', 'iframe-scrolling', 'iframe-border', 'iframe-class', 'iframe-title'];
			}
		}]);

		return JoomlaIframe;
	}(HTMLElement);

	customElements.define('joomla-iframe', JoomlaIframe);
})();

},{}]},{},[1]);
