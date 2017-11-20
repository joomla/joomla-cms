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
}var JoomlaFieldUser = function (a) {
  function b() {
    return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).apply(this, arguments));
  }return _inherits(b, a), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      if (this.modal = this.querySelector(this.modalClass), this.modalBody = this.querySelector('.modal-body'), this.input = this.querySelector(this.inputId), this.inputName = this.querySelector(this.inputNameClass), this.buttonSelect = this.querySelector(this.buttonSelectClass), this.modalClose = this.modalClose.bind(this), this.setValue = this.setValue.bind(this), this.buttonSelect) {
        this.buttonSelect.addEventListener('click', this.modalOpen.bind(this)), this.modal.addEventListener('hide', this.removeIframe.bind(this));var a,
            b = this.input.getAttribute('data-onchange');b && (a = new Function(b), this.input.addEventListener('change', a.bind(this.input)));
      }
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
      this.buttonSelect.removeEventListener('click', this), this.modal.removeEventListener('hide', this);
    } }, { key: 'modalOpen', value: function modalOpen() {
      var a = this;this.removeIframe();var b = document.createElement('iframe');b.setAttribute('name', 'field-user-modal'), b.src = this.url.replace('{field-user-id}', this.input.getAttribute('id')), b.setAttribute('width', this.modalWidth), b.setAttribute('height', this.modalHeight), this.modalBody.appendChild(b), window.jQuery(this.modal).modal('show');var c = this.modalBody.querySelector('iframe');c.addEventListener('load', function () {
        var b = c.contentWindow.document,
            d = [].slice.call(b.querySelectorAll('.button-select'));d.forEach(function (b) {
          b.addEventListener('click', function (b) {
            a.setValue(b.target.getAttribute('data-user-value'), b.target.getAttribute('data-user-name')), a.modalClose();
          });
        });
      });
    } }, { key: 'modalClose', value: function modalClose() {
      window.jQuery(this.modal).modal('hide'), this.modalBody.innerHTML = '';
    } }, { key: 'removeIframe', value: function removeIframe() {
      this.modalBody.innerHTML = '';
    } }, { key: 'setValue', value: function setValue(a, b) {
      this.input.setAttribute('value', a), this.inputName.setAttribute('value', b || a);
    } }, { key: 'url', get: function get() {
      return this.getAttribute('url');
    }, set: function set(a) {
      this.setAttribute('url', a);
    } }, { key: 'modalClass', get: function get() {
      return this.getAttribute('modal');
    }, set: function set(a) {
      this.setAttribute('modal', a);
    } }, { key: 'modalWidth', get: function get() {
      return this.getAttribute('modal-width');
    }, set: function set(a) {
      this.setAttribute('modal-width', a);
    } }, { key: 'modalHeight', get: function get() {
      return this.getAttribute('modal-height');
    }, set: function set(a) {
      this.setAttribute('modal-height', a);
    } }, { key: 'inputId', get: function get() {
      return this.getAttribute('input');
    }, set: function set(a) {
      this.setAttribute('input', a);
    } }, { key: 'inputNameClass', get: function get() {
      return this.getAttribute('input-name');
    }, set: function set(a) {
      this.setAttribute('input-name', a);
    } }, { key: 'buttonSelectClass', get: function get() {
      return this.getAttribute('button-select');
    }, set: function set(a) {
      this.setAttribute('button-select', a);
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['url', 'modal-class', 'modal-width', 'modal-height', 'input', 'input-name', 'button-select'];
    } }]), b;
}(HTMLElement);customElements.define('joomla-field-user', JoomlaFieldUser);

},{}]},{},[1]);
