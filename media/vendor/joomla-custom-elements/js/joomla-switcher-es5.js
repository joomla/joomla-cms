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
  var a = { ENTER: 13, SPACE: 32 },
      b = function (b) {
    function c() {
      _classCallCheck(this, c);var a = _possibleConstructorReturn(this, (c.__proto__ || Object.getPrototypeOf(c)).call(this));return a.inputs = [], a.spans = [], a.inputsContainer = '', a.spansContainer = '', a.newActive = '', a;
    }return _inherits(c, b), _createClass(c, [{ key: 'type', get: function get() {
        return this.getAttribute('type');
      }, set: function set(a) {
        return this.setAttribute('type', a);
      } }, { key: 'offText', get: function get() {
        return this.getAttribute('off-text') || 'Off';
      } }, { key: 'onText', get: function get() {
        return this.getAttribute('on-text') || 'On';
      } }], [{ key: 'observedAttributes', get: function get() {
        return ['type', 'off-text', 'on-text'];
      } }]), _createClass(c, [{ key: 'connectedCallback', value: function connectedCallback() {
        var a = this;if (this.inputs = [].slice.call(this.querySelectorAll('input')), 2 !== this.inputs.length || 'radio' !== this.inputs[0].type) throw new Error('`Joomla-switcher` requires two inputs type="checkbox"');this.createMarkup.bind(this)(), this.inputsContainer = this.firstElementChild, this.spansContainer = this.lastElementChild, this.inputsContainer.setAttribute('role', 'switch'), this.inputs[1].checked ? (this.inputs[1].parentNode.classList.add('active'), this.spans[1].classList.add('active'), this.inputsContainer.setAttribute('aria-label', this.spans[1].innerHTML)) : (this.spans[0].classList.add('active'), this.inputsContainer.setAttribute('aria-label', this.spans[0].innerHTML)), this.inputs.forEach(function (b) {
          b.addEventListener('click', a.toggle.bind(a));
        }), this.inputsContainer.addEventListener('keydown', this.keyEvents.bind(this));
      } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
        this.removeEventListener('joomla.switcher.toggle', this.toggle, !0), this.removeEventListener('click', this.switch, !0), this.removeEventListener('keydown', this.keydown, !0);
      } }, { key: 'dispatchCustomEvent', value: function dispatchCustomEvent(a) {
        var b = new CustomEvent(a, { bubbles: !0, cancelable: !0 });b.relatedTarget = this, this.dispatchEvent(b), this.removeEventListener(a, this);
      } }, { key: 'createMarkup', value: function createMarkup() {
        var a = 0,
            b = document.createElement('span');b.classList.add('switcher'), b.setAttribute('tabindex', 0), this.type || this.setAttribute('type', 'success');var c = document.createElement('span');c.classList.add('switch'), this.inputs.forEach(function (c, d) {
          c.setAttribute('tabindex', '-1'), c.checked && b.setAttribute('aria-checked', !0), b.appendChild(c), 1 === d && c.checked && (a = 1);
        }), b.appendChild(c);var d = document.createElement('span');d.classList.add('switcher-labels');var e = document.createElement('span');e.classList.add('switcher-label-0'), e.innerText = this.offText;var f = document.createElement('span');f.classList.add('switcher-label-1'), f.innerText = this.onText, 0 == a ? e.classList.add('active') : f.classList.add('active'), this.spans.push(e), this.spans.push(f), d.appendChild(e), d.appendChild(f), this.appendChild(b), this.appendChild(d);
      } }, { key: 'switch', value: function _switch() {
        this.spans.forEach(function (a) {
          a.classList.remove('active');
        }), this.inputsContainer.classList.contains('active') ? this.inputsContainer.classList.remove('active') : this.inputsContainer.classList.add('active'), this.inputs.forEach(function (a) {
          a.classList.remove('active');
        }), 1 === this.newActive ? (this.inputs[this.newActive].classList.add('active'), this.inputs[1].setAttribute('checked', ''), this.inputs[0].removeAttribute('checked'), this.inputsContainer.setAttribute('aria-checked', !0), this.inputsContainer.setAttribute('aria-label', this.spans[1].innerHTML), this.dispatchCustomEvent('joomla.switcher.on')) : (this.inputs[1].removeAttribute('checked'), this.inputs[0].setAttribute('checked', ''), this.inputs[0].classList.add('active'), this.inputsContainer.setAttribute('aria-checked', !1), this.inputsContainer.setAttribute('aria-label', this.spans[0].innerHTML), this.dispatchCustomEvent('joomla.switcher.off')), this.spans[this.newActive].classList.add('active');
      } }, { key: 'toggle', value: function toggle() {
        this.newActive = this.inputs[1].classList.contains('active') ? 0 : 1, this.switch.bind(this)();
      } }, { key: 'keyEvents', value: function keyEvents(b) {
        (b.keyCode === a.ENTER || b.keyCode === a.SPACE) && (b.preventDefault(), this.newActive = this.inputs[1].classList.contains('active') ? 0 : 1, this.switch.bind(this)());
      } }]), c;
  }(HTMLElement);customElements.define('joomla-switcher', b);
})();

},{}]},{},[1]);
