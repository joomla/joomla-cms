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
        this.iframe = document.createElement('iframe'), this.iframe.setAttribute('name', this.iframeName), this.iframe.setAttribute('src', this.iframeSrc), this.iframe.setAttribute('width', this.iframeWidth), this.iframe.setAttribute('height', this.iframeHeight), this.iframe.setAttribute('scrolling', this.iframeScrolling), this.iframe.setAttribute('frameborder', this.iframeBorder), this.iframe.setAttribute('class', this.iframeClass), this.iframe.setAttribute('title', this.iframeTitle), this.iframe.setAttribute('id', 'iframe-' + Date.now().toString(36) + Math.random().toString(36).substr(2, 5)), this.iframeAutoHeight && this.iframe.addEventListener('load', this.adjustHeight.bind(this), !1), this.appendChild(this.iframe);
      } }, { key: 'adjustHeight', value: function adjustHeight() {
        var a = this.iframe.contentWindow.document,
            b = a.body.scrollHeight || 0;this.iframe.setAttribute('height', b + 60 + 'px');
      } }, { key: 'iframeAutoHeight', get: function get() {
        return '1' === this.getAttribute('iframe-auto-height');
      } }, { key: 'iframeName', get: function get() {
        return this.getAttribute('iframe-name');
      } }, { key: 'iframeSrc', get: function get() {
        return this.getAttribute('iframe-src');
      } }, { key: 'iframeWidth', get: function get() {
        return this.getAttribute('iframe-width');
      } }, { key: 'iframeHeight', get: function get() {
        return this.getAttribute('iframe-height');
      } }, { key: 'iframeScrolling', get: function get() {
        return '1' === this.getAttribute('iframe-scrolling');
      } }, { key: 'iframeBorder', get: function get() {
        return '1' === this.getAttribute('iframe-border');
      } }, { key: 'iframeClass', get: function get() {
        return this.getAttribute('iframe-class');
      } }, { key: 'iframeTitle', get: function get() {
        return this.getAttribute('iframe-title');
      } }], [{ key: 'observedAttributes', get: function get() {
        return ['iframe-auto-height', 'iframe-name', 'iframe-src', 'iframe-width', 'iframe-height', 'iframe-scrolling', 'iframe-border', 'iframe-class', 'iframe-title'];
      } }]), b;
  }(HTMLElement);customElements.define('joomla-iframe', a);
})();

},{}]},{},[1]);
