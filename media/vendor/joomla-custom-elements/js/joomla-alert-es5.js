(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () {
  function a(a, b) {
    for (var c, d = 0; d < b.length; d++) {
      c = b[d], c.enumerable = c.enumerable || !1, c.configurable = !0, 'value' in c && (c.writable = !0), Object.defineProperty(a, c.key, c);
    }
  }return function (b, c, d) {
    return c && a(b.prototype, c), d && a(b, d), b;
  };
}();function _classCallCheck(a, b) {
  if (!(a instanceof b)) throw new TypeError('Cannot call a class as a function');
}function _possibleConstructorReturn(a, b) {
  if (!a) throw new ReferenceError('this hasn\'t been initialised - super() hasn\'t been called');return b && ('object' == (typeof b === 'undefined' ? 'undefined' : _typeof(b)) || 'function' == typeof b) ? b : a;
}function _inherits(a, b) {
  if ('function' != typeof b && null !== b) throw new TypeError('Super expression must either be null or a function, not ' + (typeof b === 'undefined' ? 'undefined' : _typeof(b)));a.prototype = Object.create(b && b.prototype, { constructor: { value: a, enumerable: !1, writable: !0, configurable: !0 } }), b && (Object.setPrototypeOf ? Object.setPrototypeOf(a, b) : a.__proto__ = b);
}var JoomlaAlertElement = function (a) {
  function b() {
    return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).apply(this, arguments));
  }return _inherits(b, a), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      this.dispatchCustomEvent('joomla.alert.show'), this.setAttribute('role', 'alert'), this.classList.add('joomla-alert--show'), this.type || this.setAttribute('type', 'info'), (this.hasAttribute('dismiss') || this.hasAttribute('acknowledge') || this.hasAttribute('href') && '' !== this.getAttribute('href')) && !this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close') && this.appendCloseButton.bind(this)(), this.dispatchCustomEvent('joomla.alert.show'), this.closeButton && this.closeButton.focus();
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
      this.firstChild.tagName && 'button' === this.firstChild.tagName.toLowerCase() && this.firstChild.removeEventListener('click', this.buttonCloseFn);
    } }, { key: 'attributeChangedCallback', value: function attributeChangedCallback(a, b, c) {
      switch (a) {case 'type':
          c && -1 !== ['info', 'warning', 'success', 'danger'].indexOf(c) || (this.type = 'info');break;case 'dismiss':case 'acknowledge':
          c && 'true' !== c ? this.firstElementChild.tagName && 'button' === this.firstElementChild.tagName.toLowerCase() && this.removeCloseButton.bind(this)() : this.firstElementChild.tagName && 'button' !== this.firstElementChild.tagName.toLowerCase() && this.appendCloseButton.bind(this)();break;case 'href':
          c && '' !== c ? this.firstElementChild.tagName && 'button' !== this.firstElementChild.tagName.toLowerCase() && this.firstElementChild.classList.contains('joomla-alert-button--close') && this.appendCloseButton.bind(this)() : this.firstElementChild.tagName && 'button' !== this.firstElementChild.tagName.toLowerCase() && this.removeCloseButton.bind(this)();break;case 'auto-dismiss':
          c && '' !== c || this.removeAttribute('auto-dismiss');break;default:}
    } }, { key: 'buttonCloseFn', value: function buttonCloseFn() {
      this.dispatchCustomEvent('joomla.alert.buttonClicked'), this.href && (window.location.href = this.href), this.close();
    } }, { key: 'close', value: function close() {
      var a = this;this.dispatchCustomEvent('joomla.alert.close'), this.addEventListener('transitionend', function () {
        a.dispatchCustomEvent('joomla.alert.closed'), a.parentNode.removeChild(a);
      }), this.classList.remove('joomla-alert--show');
    } }, { key: 'dispatchCustomEvent', value: function dispatchCustomEvent(a) {
      var b = new CustomEvent(a, { bubbles: !0, cancelable: !0 });b.relatedTarget = this, this.dispatchEvent(b), this.removeEventListener(a, this);
    } }, { key: 'appendCloseButton', value: function appendCloseButton() {
      if (!(this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close'))) {
        var a = document.createElement('button');if (this.hasAttribute('dismiss') ? (a.classList.add('joomla-alert--close'), a.innerHTML = '<span aria-hidden="true">&times;</span>', a.setAttribute('aria-label', this.textClose)) : (a.classList.add('joomla-alert-button--close'), a.innerHTML = this.hasAttribute('acknowledge') ? this.textAcknowledge : this.textDismiss), this.closeButton = a, this.firstChild ? this.insertBefore(a, this.firstChild) : this.appendChild(a), a && a.addEventListener('click', this.buttonCloseFn.bind(this)), 0 < this.autoDismiss) {
          var b = this,
              c = this.autoDismiss;setTimeout(function () {
            b.dispatchCustomEvent('joomla.alert.buttonClicked'), b.href && (window.location.href = b.href), b.close();
          }, c);
        }
      }
    } }, { key: 'removeCloseButton', value: function removeCloseButton() {
      this.closeButton && (this.closeButton.removeEventListener('click', this.buttonCloseFn), this.closeButton.parentNode.removeChild(this.closeButton));
    } }, { key: 'type', get: function get() {
      return this.getAttribute('type');
    }, set: function set(a) {
      return this.setAttribute('type', a);
    } }, { key: 'dismiss', get: function get() {
      return this.getAttribute('dismiss');
    }, set: function set(a) {
      return this.setAttribute('dismiss', a);
    } }, { key: 'acknowledge', get: function get() {
      return this.getAttribute('acknowledge');
    }, set: function set(a) {
      return this.setAttribute('acknowledge', a);
    } }, { key: 'href', get: function get() {
      return this.getAttribute('href');
    }, set: function set(a) {
      return this.setAttribute('href', a);
    } }, { key: 'autoDismiss', get: function get() {
      return parseInt(this.getAttribute('auto-dismiss'), 10);
    }, set: function set(a) {
      return this.setAttribute('auto-dismiss', parseInt(a, 10));
    } }, { key: 'position', get: function get() {
      return this.getAttribute('position');
    }, set: function set(a) {
      return this.setAttribute('position', a);
    } }, { key: 'textClose', get: function get() {
      return this.getAttribute('textClose') || 'Close';
    }, set: function set(a) {
      return this.setAttribute('textClose', a);
    } }, { key: 'textDismiss', get: function get() {
      return this.getAttribute('textDismiss') || 'Open';
    }, set: function set(a) {
      return this.setAttribute('textDismiss', a);
    } }, { key: 'textAcknowledge', get: function get() {
      return this.getAttribute('textAcknowledge') || 'Ok';
    }, set: function set(a) {
      return this.setAttribute('textAcknowledge', a);
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['type', 'dismiss', 'acknowledge', 'href', 'auto-dismiss', 'position', 'textClose', 'textDismiss', 'textAcknowledge'];
    } }]), b;
}(HTMLElement);customElements.define('joomla-alert', JoomlaAlertElement);

},{}]},{},[1]);
