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
}(function () {
  if (!document.getElementById('joomla-alert-stylesheet')) {
    var a = document.createElement('style');a.id = 'joomla-alert-stylesheet', a.innerHTML = 'joomla-alert{padding:.5rem 1.25rem;margin-bottom:1rem;border:1px solid transparent;border-radius:.25rem;display:block;opacity:0;transition:opacity .15s linear}joomla-alert .joomla-alert--close,joomla-alert .joomla-alert-button--close{position:relative;top:-.5rem;right:-1.25rem;padding:.5rem 1.25rem;color:inherit}joomla-alert .joomla-alert--close{font-size:1.5rem;font-weight:700;line-height:1;text-shadow:0 1px 0 #fff}joomla-alert .joomla-alert--close,joomla-alert .joomla-alert-button--close{background:0 0;border:0;float:right;color:#000;opacity:.5}joomla-alert .joomla-alert--close:focus,joomla-alert .joomla-alert--close:hover,joomla-alert .joomla-alert-button--close:focus,joomla-alert .joomla-alert-button--close:hover{color:#000;text-decoration:none;cursor:pointer;opacity:.75}joomla-alert button.joomla-alert-button--close{font-size:100%;line-height:1.15;cursor:pointer;padding-top:.75rem;background:0 0;border:0;-webkit-appearance:none}joomla-alert.joomla-alert--show{opacity:1}joomla-alert[level=success]{background-color:#dff0d8;border-color:#d0e9c6;color:#3c763d}joomla-alert[level=success] hr{border-top-color:#c1e2b3}joomla-alert[level=success] .alert-link{color:#2b542c}joomla-alert[level=info]{background-color:#d9edf7;border-color:#bcdff1;color:#31708f}joomla-alert[level=info] hr{border-top-color:#a6d5ec}joomla-alert[level=info] .alert-link{color:#245269}joomla-alert[level=warning]{background-color:#fcf8e3;border-color:#faf2cc;color:#8a6d3b}joomla-alert[level=warning] hr{border-top-color:#f7ecb5}joomla-alert[level=warning] .alert-link{color:#66512c}joomla-alert[level=danger]{background-color:#f2dede;border-color:#ebcccc;color:#a94442}joomla-alert[level=danger] hr{border-top-color:#e4b9b9}joomla-alert[level=danger] .alert-link{color:#843534}', document.head.appendChild(a);
  }
})();var AlertElement = function (a) {
  function b() {
    return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).call(this));
  }return _inherits(b, a), _createClass(b, [{ key: 'level', get: function get() {
      return this.getAttribute('level') || 'info';
    }, set: function set(a) {
      return this.setAttribute('level', a);
    } }, { key: 'dismiss', get: function get() {
      return this.getAttribute('dismiss');
    } }, { key: 'acknowledge', get: function get() {
      return this.getAttribute('acknowledge');
    } }, { key: 'href', get: function get() {
      return this.getAttribute('href');
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['level', 'dismiss', 'acknowledge', 'href'];
    } }]), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      this.setAttribute('role', 'alert'), this.classList.add('joomla-alert--show'), this.level && -1 !== ['info', 'warning', 'danger', 'success'].indexOf(this.level) || this.setAttribute('level', 'info'), (this.hasAttribute('dismiss') || this.hasAttribute('acknowledge') || this.hasAttribute('href') && '' !== this.getAttribute('href') && !this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close')) && this.appendCloseButton(), this.dispatchCustomEvent('joomla.alert.show');var a = this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close');a && a.focus();
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
      this.removeEventListener('joomla.alert.show', this), this.removeEventListener('joomla.alert.close', this), this.removeEventListener('joomla.alert.closed', this), this.firstChild.tagName && 'button' === this.firstChild.tagName.toLowerCase() && this.firstChild.removeEventListener('click', this);
    } }, { key: 'attributeChangedCallback', value: function attributeChangedCallback(a, b, c) {
      'level' === a ? (!c || c && -1 === ['info', 'warning', 'danger', 'success'].indexOf(c)) && (this.level = 'info') : 'dismiss' === a || 'acknowledge' === a ? c && 'true' !== c ? this.removeCloseButton() : this.appendCloseButton() : 'href' === a ? c && '' !== c ? !this.querySelector('button.joomla-alert-button--close') && this.appendCloseButton() : this.removeCloseButton() : void 0;
    } }, { key: 'close', value: function close() {
      this.dispatchCustomEvent('joomla.alert.close'), this.addEventListener('transitionend', function () {
        this.dispatchCustomEvent('joomla.alert.closed'), this.parentNode.removeChild(this);
      }, !1), this.classList.remove('joomla-alert--show');
    } }, { key: 'dispatchCustomEvent', value: function dispatchCustomEvent(a) {
      var b = new CustomEvent(a);b.relatedTarget = this, this.dispatchEvent(b), this.removeEventListener(a, this);
    } }, { key: 'appendCloseButton', value: function appendCloseButton() {
      if (!(this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close'))) {
        var a = this,
            b = document.createElement('button');this.hasAttribute('dismiss') ? (b.classList.add('joomla-alert--close'), b.innerHTML = '<span aria-hidden="true">&times;</span>', b.setAttribute('aria-label', this.getText('JCLOSE', 'Close'))) : (b.classList.add('joomla-alert-button--close'), b.innerHTML = this.hasAttribute('acknowledge') ? this.getText('JOK', 'ok') : this.getText('JOPEN', 'Open')), this.firstChild ? this.insertBefore(b, this.firstChild) : this.appendChild(b), b && (this.href ? b.addEventListener('click', function () {
          a.dispatchCustomEvent('joomla.alert.buttonClicked'), window.location.href = a.href, a.close();
        }) : b.addEventListener('click', function () {
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
