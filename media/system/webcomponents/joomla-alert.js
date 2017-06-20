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
}var AlertElement = function (a) {
  function b() {
    return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).call(this));
  }return _inherits(b, a), _createClass(b, [{ key: 'level', get: function get() {
      return this.getAttribute('level');
    } }, { key: 'dismiss', get: function get() {
      return this.getAttribute('dismiss');
    } }, { key: 'acknowledge', get: function get() {
      return this.getAttribute('acknowledge');
    } }, { key: 'href', get: function get() {
      return this.getAttribute('href');
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['level', 'dismiss', 'acknowledge', 'href'];
    } }]), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      this.setAttribute('role', 'alert'), this.classList.add('show'), this.level && -1 !== ['info', 'warning', 'danger', 'success'].indexOf(this.level) || this.setAttribute('level', 'info'), (this.hasAttribute('dismiss') && '' !== this.getAttribute('dismiss') || this.hasAttribute('acknowledge') && '' !== this.getAttribute('acknowledge') || this.hasAttribute('href') && '' !== this.getAttribute('href') && !this.querySelector('button.close') && !this.querySelector('button.button--close')) && this.appendCloseButton(), this.dispatchCustomEvent('joomla.alert.show');
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
      this.removeEventListener('joomla.alert.show', this), this.removeEventListener('joomla.alert.close', this), this.removeEventListener('joomla.alert.closed', this), this.firstChild.tagName && 'button' === this.firstChild.tagName.toLowerCase() && this.firstChild.removeEventListener('click', this);
    } }, { key: 'attributeChangedCallback', value: function attributeChangedCallback(a, b, c) {
      'level' === a ? -1 === ['info', 'warning', 'danger', 'success'].indexOf(c) && this.setAttribute('level', b) : 'dismiss' === a || 'acknowledge' === a ? 'true' === c ? this.appendCloseButton() : this.removeCloseButton() : 'href' === a ? '' === c ? this.removeCloseButton() : !this.querySelector('button.button--close') && this.appendCloseButton() : void 0;
    } }, { key: 'close', value: function close() {
      this.dispatchCustomEvent('joomla.alert.close'), this.addEventListener('transitionend', function () {
        this.dispatchCustomEvent('joomla.alert.closed'), this.parentNode.removeChild(this);
      }, !1), this.classList.remove('show');
    } }, { key: 'dispatchCustomEvent', value: function dispatchCustomEvent(a) {
      var b = new CustomEvent(a);b.relatedTarget = this, this.dispatchEvent(b), this.removeEventListener(a, this);
    } }, { key: 'appendCloseButton', value: function appendCloseButton() {
      if (!(this.querySelector('button.close') || this.querySelector('button.button--close'))) {
        var a = this,
            b = document.createElement('button');this.hasAttribute('dismiss') ? (b.classList.add('close'), b.innerHTML = '<span aria-hidden="true">&times;</span>', b.setAttribute('aria-label', this.getText('JCLOSE', 'Close'))) : (b.classList.add('button--close'), b.innerHTML = this.hasAttribute('acknowledge') ? this.getText('JOK', 'ok') : this.getText('JOPEN', 'Open')), this.firstChild ? this.insertBefore(b, this.firstChild) : this.appendChild(b), this.firstChild && this.firstChild.tagName && 'button' === this.firstChild.tagName.toLowerCase() && (this.firstChild.focus(), this.href ? this.firstChild.addEventListener('click', function () {
          a.dispatchCustomEvent('joomla.alert.buttonClicked'), window.location.href = a.href, a.close();
        }) : this.firstChild.addEventListener('click', function () {
          a.dispatchCustomEvent('joomla.alert.buttonClicked'), a.getAttribute('data-callback') ? (window[a.getAttribute('data-callback')](), a.close()) : a.close();
        })), this.hasAttribute('auto-dismiss') && setTimeout(function () {
          a.dispatchCustomEvent('joomla.alert.buttonClicked'), a.hasAttribute('data-callback') ? window[a.getAttribute('data-callback')]() : a.close();
        }, parseInt(a.getAttribute('auto-dismiss')) ? a.getAttribute('auto-dismiss') : 3e3);
      }
    } }, { key: 'removeCloseButton', value: function removeCloseButton() {
      var a = this.querySelector('button');a && (a.removeEventListener('click', this), a.parentNode.removeChild(a));
    } }, { key: 'getText', value: function getText(a, b) {
      return window.Joomla && Joomla.JText && Joomla.JText._ && 'function' == typeof Joomla.JText._ && Joomla.JText._(a) ? Joomla.JText._(a) : b;
    } }]), b;
}(HTMLElement);customElements.define('joomla-alert', AlertElement);

},{}]},{},[1]);
