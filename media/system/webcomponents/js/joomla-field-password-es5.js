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
      _classCallCheck(this, c);var a = _possibleConstructorReturn(this, (c.__proto__ || Object.getPrototypeOf(c)).call(this));if (!window.Joomla) throw new Error('Joomla API is not iniatiated!');if (!a.querySelector('input')) throw new Error('Joomla Password field requires an input element!');return a;
    }return _inherits(c, a), _createClass(c, [{ key: 'minLength', get: function get() {
        parseInt(this.input.getAttribute('min-length'));
      } }, { key: 'minIntegers', get: function get() {
        parseInt(this.input.getAttribute('min-integers'));
      } }, { key: 'minSymbols', get: function get() {
        parseInt(this.input.getAttribute('min-symbols'));
      } }, { key: 'minUppercase', get: function get() {
        parseInt(this.input.getAttribute('min-uppercase'));
      } }, { key: 'minLowercase', get: function get() {
        parseInt(this.input.getAttribute('min-lowercase'));
      } }, { key: 'reveal', get: function get() {
        this.input.getAttribute('reveal');
      } }], [{ key: 'observedAttributes', get: function get() {
        return ['min-length', 'min-integers', 'min-symbols', 'min-uppercase', 'min-lowercase', 'reveal'];
      } }]), _createClass(c, [{ key: 'connectedCallback', value: function connectedCallback() {
        if (0 < this.minLength + this.minIntegers + this.minSymbols + this.minUppercase + this.minLowercase) {
          var a = '',
              b = '';this.input.value.length || (a = ' bg-danger', b = 0);var c = document.createElement('div');c.setAttribute('class', 'progress');var d = document.createElement('div');d.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated' + a), d.style.width = 0 + b, d.max = 100, d.setAttribute('aria-describedby', 'password-' + i), c.appendChild(d);var e = document.createElement('div');e.setAttribute('class', 'text-xs-center'), e.setAttribute('id', 'password-' + i), this.insertAdjacentElement('afterEnd', e), this.insertAdjacentElement('afterEnd', c), 0 < this.input.value.length && this.input.setAttribute('required', ''), this.input.addEventListener('keyup', this.setValue.bind(this)), this.setAttribute('validation-handler', 'password-strength_' + Math.random().toString(36).substr(2, 9)), document.formvalidator && document.formvalidator.setHandler(this.getAttribute('validation-handler'), this.handler(value).bind(this));
        }if ('true' === this.reveal) {
          var f = this.querySelector('.input-group-addon');if (f) {
            var g = this;f.addEventListener('click', function () {
              var a = g.querySelector('.fa'),
                  b = a.nextElementSibling;a.classList.contains('fa-eye') ? (a.classList.remove('fa-eye'), a.classList.add('fa-eye-slash'), g.input.type = 'text', b.innerText = Joomla.JText._('JSHOW')) : (a.classList.add('fa-eye'), a.classList.remove('fa-eye-slash'), g.input.type = 'password', b.innerText = Joomla.JText._('JHIDE'));
            });
          }
        }
      } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
        this.buttonSelect.removeEventListener('click', this), this.modal.removeEventListener('hide', this);
      } }, { key: 'setValue', value: function setValue() {
        this.getMeter.bind(this);
      } }, { key: 'getMeter', value: function getMeter() {
        var a = document.querySelector('.progress-bar'),
            c = new b({ lowercase: this.minLowercase ? this.minLowercase : 0, uppercase: this.minUppercase ? this.minUppercase : 0, numbers: this.minIntegers ? this.minIntegers : 0, special: this.minSymbols ? this.minSymbols : 0, length: this.minLength ? this.minLength : 4 }),
            d = c.getScore(this.input.value),
            e = a.getAttribute('aria-describedby').replace(/^\D+/g, ''),
            f = this.input.parentNode.parentNode.querySelector('#password-' + e);79 < d && (a.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning'), f.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_COMPLETE')), 64 < d && 80 > d && (a.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning'), f.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_INCOMPLETE')), 50 < d && 65 > d && (a.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning'), f.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_INCOMPLETE')), 40 < d && 51 > d && (a.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-warning'), f.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_INCOMPLETE')), 41 > d && (a.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-danger'), f.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_INCOMPLETE')), 100 === d && a.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated bg-success'), a.style.width = d + '%', this.input.value.length || (a.setAttribute('class', 'progress-bar progress-bar-striped progress-bar-animated'), f.innerHTML = '', this.input.setAttribute('required', ''));
      } }, { key: 'handler', value: function handler(a) {
        var c = new b({ lowercase: this.minLowercase ? this.minLowercase : 0, uppercase: this.minUppercase ? this.minUppercase : 0, numbers: this.minIntegers ? this.minIntegers : 0, special: this.minSymbols ? this.minSymbols : 0, length: this.minLength ? this.minLength : 4 });return 100 === c.getScore(a);
      } }]), c;
  }(HTMLElement);a.define('joomla-field-password', c);
})(customElements);

},{}]},{},[1]);
