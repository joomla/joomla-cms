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
}if (!document.head.querySelector('#joomla-dropdown-style')) {
  var style = document.createElement('style');style.id = 'joomla-dropdown-style', style.innerHTML = 'joomla-dropdown{display:none}joomla-dropdown[expanded]{position:absolute;top:100%;left:-240px;z-index:1000;display:block;width:20rem;min-width:10rem;padding:.5rem 0;margin:.125rem 0 0;font-size:1rem;color:#292b2c;text-align:left;list-style:none;background-color:#fff;background-clip:padding-box;border:1px solid rgba(0,0,0,.15);border-radius:.25rem}', document.head.appendChild(style);
}var JoomlaDropdownElement = function (a) {
  function b() {
    return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).apply(this, arguments));
  }return _inherits(b, a), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      this.setAttribute('aria-labelledby', this.for.substring(1));var a = document.querySelector(this.for),
          b = this,
          c = [].slice.call(this.childNodes);a.id && (a.setAttribute('aria-haspopup', 'true'), a.setAttribute('aria-expanded', 'false'), a.addEventListener('click', function (d) {
        var e = d.target;'span' === d.target.tagName.toLowerCase() && (e = d.target.parentNode), b.hasAttribute('expanded') ? (b.removeAttribute('expanded'), e.setAttribute('aria-expanded', 'false')) : (b.setAttribute('expanded', ''), e.setAttribute('aria-expanded', 'true')), c.forEach(function (a) {
          a.tagName && 'a' !== a.tagName.toLowerCase || a.addEventListener('click', b.close.bind(b));
        }), document.addEventListener('click', function (c) {
          c.target === a || a.childNodes.length && -1 < [].slice.call(a.childNodes).indexOf(c.target) || b.close();
        }), c.forEach(function (a) {
          a.addEventListener('click', function () {
            b.close();
          });
        });
      }));
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {} }, { key: 'adoptedCallback', value: function adoptedCallback() {} }, { key: 'attributeChangedCallback', value: function attributeChangedCallback(a) {
      switch (a) {}
    } }, { key: 'close', value: function close() {
      var a = document.querySelector('#' + this.getAttribute('aria-labelledby'));this.removeAttribute('expanded'), a.setAttribute('aria-expanded', 'false');
    } }, { key: 'for', get: function get() {
      return this.getAttribute('for');
    }, set: function set(a) {
      return this.setAttribute('for', a);
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['for'];
    } }]), b;
}(HTMLElement);customElements.define('joomla-dropdown', JoomlaDropdownElement);

},{}]},{},[1]);
