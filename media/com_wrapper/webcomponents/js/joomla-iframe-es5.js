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
  var a = function (a) {
    function b() {
      return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).apply(this, arguments));
    }return _inherits(b, a), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
        this.adjustHeight = this.adjustHeight().bind(this);var a = document.createElement('iframe');a.setAttribute('name', this.name), a.setAttribute('src', this.src), a.setAttribute('width', this.width), a.setAttribute('height', this.height), a.setAttribute('scrolling', this.scrolling), a.setAttribute('frameborder', this.frameborder), a.setAttribute('class', this.useClass), a.innerText = this.noFrameText, this.autoHeight && a.addEventListener('load', this.adjustHeight, !1);
      } }, { key: 'adjustHeight', value: function adjustHeight() {
        var a = 0,
            b = this.querySelector('iframe'),
            c = 'contentDocument' in b ? b.contentDocument : b.contentWindow.document;a = c.body.scrollHeight, b.style.height = parseInt(a) + 60 + 'px';
      } }, { key: 'autoHeight', get: function get() {
        return 'true' === this.getAttribute('auto-height');
      } }, { key: 'name', get: function get() {
        this.getAttribute('name');
      } }, { key: 'src', get: function get() {
        return this.getAttribute('src');
      } }, { key: 'width', get: function get() {
        this.getAttribute('width');
      } }, { key: 'height', get: function get() {
        this.getAttribute('height');
      } }, { key: 'scrolling', get: function get() {
        this.getAttribute('scrolling');
      } }, { key: 'frameborder', get: function get() {
        this.getAttribute('frameborder');
      } }, { key: 'useClass', get: function get() {
        this.getAttribute('use-class');
      } }, { key: 'noFrameText', get: function get() {
        this.getAttribute('no-frame-text');
      } }], [{ key: 'observedAttributes', get: function get() {
        return ['autoHeight', 'name', 'src', 'width', 'height', 'scrolling', 'frameborder', 'useClass', 'noFrameText'];
      } }]), b;
  }(HTMLElement);customElements.define('joomla-iframe', a);
})();

},{}]},{},[1]);
