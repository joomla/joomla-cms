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
}var Joomla = window.Joomla || {};if (!document.head.querySelector('#joomla-alert-style')) {
  var style = document.createElement('style');style.id = 'joomla-alert-style', style.innerHTML = 'joomla-alert{display:block;padding:.5rem 1.25rem;margin-bottom:1rem;border:1px solid transparent;opacity:0;border-radius:.25rem;transition:opacity .15s linear}joomla-alert.joomla-alert--show{opacity:1}joomla-alert .joomla-alert--close,joomla-alert .joomla-alert-button--close{position:relative;top:-.5rem;right:-1.25rem;padding:.2rem 1rem;color:inherit}joomla-alert .joomla-alert--close{font-size:1.5rem;font-weight:700;line-height:1;text-shadow:0 1px 0 #fff}joomla-alert .joomla-alert--close,joomla-alert .joomla-alert-button--close{float:right;color:#000;background:0 0;border:0;opacity:.5}joomla-alert .joomla-alert--close:focus,joomla-alert .joomla-alert--close:hover,joomla-alert .joomla-alert-button--close:focus,joomla-alert .joomla-alert-button--close:hover{color:#000;text-decoration:none;cursor:pointer;opacity:.75}joomla-alert button.joomla-alert-button--close{padding-top:.75rem;font-size:100%;line-height:1.15;cursor:pointer;background:0 0;border:0;-webkit-appearance:none}joomla-alert[position=top-left]{position:fixed;top:10px;left:10px}joomla-alert[position=top-center]{position:fixed;top:10px;left:50%;transform:translateX(-50%)}joomla-alert[position=top-right]{position:fixed;top:10px;right:10px}joomla-alert[type=primary]{color:#00364f;background-color:#cce1ea;border-color:#b8d5e2}joomla-alert[type=primary] hr{border-top-color:#a6cadb}joomla-alert[type=primary] .alert-link{color:#00131c}joomla-alert[type=secondary]{color:#464a4e;background-color:#e7e8ea;border-color:#dddfe2}joomla-alert[type=secondary] hr{border-top-color:#cfd2d6}joomla-alert[type=secondary] .alert-link{color:#2e3133}joomla-alert[type=success]{color:#234423;background-color:#d9e6d9;border-color:#cadcca}joomla-alert[type=success] hr{border-top-color:#bbd2bb}joomla-alert[type=success] .alert-link{color:#122212}joomla-alert[type=info]{color:#0c5460;background-color:#d1ecf1;border-color:#bee5eb}joomla-alert[type=info] hr{border-top-color:#abdde5}joomla-alert[type=info] .alert-link{color:#062c33}joomla-alert[type=warning]{color:#7d5a29;background-color:#fcefdc;border-color:#fbe8cd}joomla-alert[type=warning] hr{border-top-color:#f9ddb5}joomla-alert[type=warning] .alert-link{color:#573e1c}joomla-alert[type=danger]{color:#234423;background-color:#d9e6d9;border-color:#cadcca}joomla-alert[type=danger] hr{border-top-color:#bbd2bb}joomla-alert[type=danger] .alert-link{color:#122212}joomla-alert[type=light]{color:#818182;background-color:#fefefe;border-color:#fdfdfe}joomla-alert[type=light] hr{border-top-color:#ececf6}joomla-alert[type=light] .alert-link{color:#686868}joomla-alert[type=dark]{color:#1b1e21;background-color:#d6d8d9;border-color:#c6c8ca}joomla-alert[type=dark] hr{border-top-color:#b9bbbe}joomla-alert[type=dark] .alert-link{color:#040505}', document.head.appendChild(style);
}var JoomlaAlertElement = function (a) {
  function b() {
    return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).apply(this, arguments));
  }return _inherits(b, a), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      this.setAttribute('role', 'alert'), this.classList.add('joomla-alert--show'), this.type || this.setAttribute('type', 'info'), (this.hasAttribute('dismiss') || this.hasAttribute('acknowledge') || this.hasAttribute('href') && '' !== this.getAttribute('href')) && !this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close') && this.appendCloseButton(), this.dispatchCustomEvent('joomla.alert.show');var a = this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close');a && a.focus();
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
      this.removeEventListener('joomla.alert.show', this), this.removeEventListener('joomla.alert.close', this), this.removeEventListener('joomla.alert.closed', this), this.firstChild.tagName && 'button' === this.firstChild.tagName.toLowerCase() && this.firstChild.removeEventListener('click', this);
    } }, { key: 'attributeChangedCallback', value: function attributeChangedCallback(a, b, c) {
      switch (a) {case 'type':
          c || (this.type = 'info');break;case 'dismiss':case 'acknowledge':
          c && 'true' !== c ? this.removeCloseButton() : this.appendCloseButton();break;case 'href':
          c && '' !== c ? !this.querySelector('button.joomla-alert-button--close') && this.appendCloseButton() : this.removeCloseButton();break;case 'auto-dismiss':
          c && '' !== c || this.removeAttribute('auto-dismiss');break;default:}
    } }, { key: 'close', value: function close() {
      var a = this;this.dispatchCustomEvent('joomla.alert.close'), this.addEventListener('transitionend', function () {
        a.dispatchCustomEvent('joomla.alert.closed'), a.parentNode.removeChild(a);
      }), this.classList.remove('joomla-alert--show');
    } }, { key: 'dispatchCustomEvent', value: function dispatchCustomEvent(a) {
      var b = new CustomEvent(a, { bubbles: !0, cancelable: !0 });b.relatedTarget = this, this.dispatchEvent(b), this.removeEventListener(a, this);
    } }, { key: 'appendCloseButton', value: function appendCloseButton() {
      if (!(this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close'))) {
        var a = this,
            b = document.createElement('button');if (this.hasAttribute('dismiss') ? (b.classList.add('joomla-alert--close'), b.innerHTML = '<span aria-hidden="true">&times;</span>', b.setAttribute('aria-label', this.getText('JCLOSE', 'Close'))) : (b.classList.add('joomla-alert-button--close'), b.innerHTML = this.hasAttribute('acknowledge') ? this.getText('JOK', 'ok') : this.getText('JOPEN', 'Open')), this.firstChild ? this.insertBefore(b, this.firstChild) : this.appendChild(b), b && b.addEventListener('click', function () {
          a.dispatchCustomEvent('joomla.alert.buttonClicked'), a.href && (window.location.href = a.href), a.close();
        }), 0 < a['auto-dismiss']) {
          var c = a['auto-dismiss'];setTimeout(function () {
            a.dispatchCustomEvent('joomla.alert.buttonClicked'), a.href && (window.location.href = a.href), a.close();
          }, c);
        }
      }
    } }, { key: 'removeCloseButton', value: function removeCloseButton() {
      var a = this.querySelector('button');a && (a.removeEventListener('click', this), a.parentNode.removeChild(a));
    } }, { key: 'getText', value: function getText(a, b) {
      return window.Joomla && Joomla.JText && Joomla.JText._ && 'function' == typeof Joomla.JText._ && Joomla.JText._(a) ? Joomla.JText._(a) : b;
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
    } }, { key: 'auto-dismiss', get: function get() {
      return parseInt(this.getAttribute('auto-dismiss'), 10);
    }, set: function set(a) {
      return this.setAttribute('auto-dismiss', parseInt(a, 10));
    } }, { key: 'position', get: function get() {
      return this.getAttribute('position');
    }, set: function set(a) {
      return this.setAttribute('position', a);
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['type', 'dismiss', 'acknowledge', 'href', 'auto-dismiss', 'position'];
    } }]), b;
}(HTMLElement);customElements.define('joomla-alert', JoomlaAlertElement);

},{}]},{},[1]);
