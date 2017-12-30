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
}();function _possibleConstructorReturn(a, b) {
  if (!a) throw new ReferenceError('this hasn\'t been initialised - super() hasn\'t been called');return b && ('object' == (typeof b === 'undefined' ? 'undefined' : _typeof(b)) || 'function' == typeof b) ? b : a;
}function _inherits(a, b) {
  if ('function' != typeof b && null !== b) throw new TypeError('Super expression must either be null or a function, not ' + (typeof b === 'undefined' ? 'undefined' : _typeof(b)));a.prototype = Object.create(b && b.prototype, { constructor: { value: a, enumerable: !1, writable: !0, configurable: !0 } }), b && (Object.setPrototypeOf ? Object.setPrototypeOf(a, b) : a.__proto__ = b);
}function _classCallCheck(a, b) {
  if (!(a instanceof b)) throw new TypeError('Cannot call a class as a function');
}(function (a) {
  'use strict';
  var b = function () {
    function a(b) {
      _classCallCheck(this, a), this.lowercase = b.lowercase || 0, this.uppercase = b.uppercase || 0, this.numbers = b.numbers || 0, this.special = b.special || 0, this.length = b.length || 4;
    }return _createClass(a, [{ key: 'getScore', value: function getScore(a) {
        for (var b = 0, c = 0, d = ['lowercase', 'uppercase', 'numbers', 'special', 'length'], e = 0, f = d.length; f > e; e++) {
          this.hasOwnProperty(d[e]) && 0 < this[d[e]] && ++c;
        }return b += this.calc(a, /[a-z]/g, this.lowercase, c), b += this.calc(a, /[A-Z]/g, this.uppercase, c), b += this.calc(a, /[0-9]/g, this.numbers, c), b += this.calc(a, /[\$\!\#\?\=\;\:\*\-\_\€\%\&\(\)\`\´]/g, this.special, c), b += 1 == c ? a.length > this.length ? 100 : 100 / this.length * a.length : a.length > this.length ? 100 / c : 100 / c / this.length * a.length, b;
      } }, { key: 'calc', value: function calc(a, b, c, d) {
        var e = a.match(b);return e && e.length > c && 0 != c ? 100 / d : e && 0 < c ? 100 / d / c * e.length : 0;
      } }]), a;
  }(),
      c = function (a) {
    function c() {
      _classCallCheck(this, c);var a = _possibleConstructorReturn(this, (c.__proto__ || Object.getPrototypeOf(c)).call(this));if (!window.Joomla) throw new Error('Joomla API is not iniatiated!');if (a.input = a.querySelector('input'), !a.input) throw new Error('Joomla Password field requires an input element!');return a.meterLabel = '', a.meter = '', a;
    }return _inherits(c, a), _createClass(c, [{ key: 'minLength', get: function get() {
        return parseInt(this.getAttribute('min-length') || 0);
      } }, { key: 'minIntegers', get: function get() {
        return parseInt(this.getAttribute('min-integers') || 0);
      } }, { key: 'minSymbols', get: function get() {
        return parseInt(this.getAttribute('min-symbols') || 0);
      } }, { key: 'minUppercase', get: function get() {
        return parseInt(this.getAttribute('min-uppercase') || 0);
      } }, { key: 'minLowercase', get: function get() {
        return parseInt(this.getAttribute('min-lowercase') || 0);
      } }, { key: 'reveal', get: function get() {
        return this.getAttribute('reveal') || !1;
      } }, { key: 'showText', get: function get() {
        return this.getAttribute('text-show') || 'Show';
      } }, { key: 'hideText', get: function get() {
        return this.getAttribute('text-hide') || 'Hide';
      } }, { key: 'completeText', get: function get() {
        return this.getAttribute('text-complete') || 'Password meets site\'s requirements';
      } }, { key: 'incompleteText', get: function get() {
        return this.getAttribute('text-incomplete') || 'Password does not meet site\'s requirements';
      } }], [{ key: 'observedAttributes', get: function get() {
        return ['min-length', 'min-integers', 'min-symbols', 'min-uppercase', 'min-lowercase', 'reveal', 'text-show', 'text-hide', 'text-complete', 'text-incomplete'];
      } }]), _createClass(c, [{ key: 'connectedCallback', value: function connectedCallback() {
        if (this.minLength && 0 < this.minLength || this.minIntegers && 0 < this.minIntegers || this.minSymbols && 0 < this.minSymbols || this.minUppercase && 0 < this.minUppercase || this.minLowercase && 0 < this.minLowercase) {
          var a = '',
              b = '';this.input.value.length || (a = ' bg-danger', b = 0);var c = Math.random().toString(36).substr(2, 9),
              d = document.createElement('div');d.setAttribute('class', 'progress'), this.meter = document.createElement('div'), this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated' + a), this.meter.style.width = 0 + b, this.meter.max = 100, this.meter.setAttribute('aria-describedby', 'password-' + c), d.appendChild(this.meter), this.meterLabel = document.createElement('div'), this.meterLabel.setAttribute('class', 'text-xs-center'), this.meterLabel.setAttribute('id', 'password-' + c), this.insertAdjacentElement('afterEnd', this.meterLabel), this.insertAdjacentElement('afterEnd', d), 0 < this.input.value.length && this.input.setAttribute('required', ''), this.input.addEventListener('keyup', this.getMeter.bind(this)), this.setAttribute('validation-handler', 'password-strength_' + Math.random().toString(36).substr(2, 9)), document.formvalidator && document.formvalidator.setHandler(this.getAttribute('validation-handler'), this.handler.bind(this));
        }if ('true' === this.reveal) {
          var e = document.createElement('span'),
              f = document.createElement('span'),
              g = document.createElement('span');e.classList.add('input-group-addon'), f.setAttribute('class', 'fa fa-eye'), f.setAttribute('aria-hidden', 'true'), g.setAttribute('class', 'sr-only'), g.innerText = this.showText, e.appendChild(f), e.appendChild(g);var h = this.querySelector('.input-group');h || (h = document.createElement('div'), h.classList.add('input-group'), h.appendChild(this.input), this.appendChild(h)), h.appendChild(e);var i = this;this.input = this.querySelector('input'), f.addEventListener('click', function () {
            f.classList.contains('fa-eye') ? (f.classList.remove('fa-eye'), f.classList.add('fa-eye-slash'), i.input.type = 'text', g.innerText = i.showText) : (f.classList.add('fa-eye'), f.classList.remove('fa-eye-slash'), i.input.type = 'password', g.innerText = i.hideText);
          });
        }
      } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {} }, { key: 'getMeter', value: function getMeter() {
        if (this.meter && this.meterLabel) {
          var a = new b({ lowercase: this.minLowercase ? this.minLowercase : 0, uppercase: this.minUppercase ? this.minUppercase : 0, numbers: this.minIntegers ? this.minIntegers : 0, special: this.minSymbols ? this.minSymbols : 0, length: this.minLength ? this.minLength : 4 }),
              c = a.getScore(this.input.value);79 < c && (this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning'), this.meterLabel.innerHTML = this.completeText), 64 < c && 80 > c && (this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning'), this.meterLabel.innerHTML = this.incompleteText), 50 < c && 65 > c && (this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning'), this.meterLabel.innerHTML = this.incompleteText), 40 < c && 51 > c && (this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning'), this.meterLabel.innerHTML = this.incompleteText), 41 > c && (this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-danger'), this.meterLabel.innerHTML = this.incompleteText), 100 === c && this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-success'), this.meter.style.width = c + '%', this.input.value.length || (this.meter.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated'), this.meterLabel.innerHTML = '', this.input.setAttribute('required', ''));
        }
      } }, { key: 'handler', value: function handler(a) {
        var c = new b({ lowercase: this.minLowercase ? this.minLowercase : 0, uppercase: this.minUppercase ? this.minUppercase : 0, numbers: this.minIntegers ? this.minIntegers : 0, special: this.minSymbols ? this.minSymbols : 0, length: this.minLength ? this.minLength : 4 });return 100 === c.getScore(a);
      } }]), c;
  }(HTMLElement);a.define('joomla-field-password', c);
})(customElements);

},{}]},{},[1]);
