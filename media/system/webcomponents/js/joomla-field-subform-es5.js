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
}(function (a) {
  'use strict';
  function b(a, b) {
    var c;['matches', 'msMatchesSelector'].some(function (a) {
      return !('function' != typeof document.body[a]) && (c = a, !0);
    });for (var d; a;) {
      if (d = a.parentElement, d && d[c](b)) return d;a = d;
    }return null;
  }var c = function (a) {
    function c() {
      return _classCallCheck(this, c), _possibleConstructorReturn(this, (c.__proto__ || Object.getPrototypeOf(c)).apply(this, arguments));
    }return _inherits(c, a), _createClass(c, [{ key: 'connectedCallback', value: function connectedCallback() {
        var a = this;if (this.containerWithRows = this, this.rowsContainer) for (var c = this.querySelectorAll(this.rowsContainer), d = 0, e = c.length; d < e; d++) {
          if (b(c[d], 'joomla-field-subform') === this) {
            this.containerWithRows = c[d];break;
          }
        }this.lastRowNum = this.getRows().length, this.template = '', this.prepareTemplate(), this.addEventListener('click', function (c) {
          var d = b(c.target, a.buttonAdd),
              e = b(c.target, a.buttonRemove);if (d && b(d, 'joomla-field-subform') === a) {
            var f = b(d, a.repeatableElement);this.addRow(f);
          } else if (e && b(e, 'joomla-field-subform') === a) {
            var g = b(e, a.repeatableElement);this.removeRow(g);
          }
        }), this.buttonMove && new Sortable.default(this.containerWithRows, { draggable: this.repeatableElement, handle: this.buttonMove });
      } }, { key: 'getRows', value: function getRows() {
        for (var a = this.containerWithRows.children, b = document.body.msMatchesSelector ? 'msMatchesSelector' : 'matches', c = [], d = 0, e = a.length; d < e; d++) {
          a[d][b](this.repeatableElement) && c.push(a[d]);
        }return result;
      } }, { key: 'prepareTemplate', value: function prepareTemplate() {
        var a = [].slice.call(this.children).filter(function (a) {
          return a.classList.contains('subform-repeatable-template-section');
        });if (a[0] && (this.template = a[0].innerHTML), !this.template) throw new Error('The row template are required to subform element to work');
      } }, { key: 'addRow', value: function addRow(a) {
        var b = this.getRows().length;if (b >= this.maximum) return null;var c;c = 'TBODY' === this.containerWithRows.nodeName || 'TABLE' === this.containerWithRows.nodeName ? document.createElement('tbody') : document.createElement('div'), c.innerHTML = this.template;var d = c.children[0];return a ? a.parentNode.insertBefore(d, a.nextSibling) : this.containerWithRows.append(d), d.setAttribute('data-new', '1'), this.fixUniqueAttributes(d, b), this.dispatchEvent(new CustomEvent('subform-row-add', { detail: { row: d }, bubbles: !0 })), window.Joomla && Joomla.Event.dispatch(d, 'joomla:updated'), d;
      } }, { key: 'removeRow', value: function removeRow(a) {
        var b = this.getRows().length;b <= this.minimum || (this.dispatchEvent(new CustomEvent('subform-row-remove', { detail: { row: a }, bubbles: !0 })), window.Joomla && Joomla.Event.dispatch(a, 'joomla:removed'), a.parentNode.removeChild(a));
      } }, { key: 'fixUniqueAttributes', value: function fixUniqueAttributes(a, c) {
        this.lastRowNum++, c = c || 0;var d = a.getAttribute('data-group'),
            e = a.getAttribute('data-base-name'),
            f = Math.max(this.lastRowNum, c + 1),
            g = e + f;this.lastRowNum = f, a.setAttribute('data-group', g);for (var h = a.querySelectorAll('[name]'), j = {}, k = 0, i = h.length; k < i; k++) {
          var l = h[k],
              m = l.getAttribute('name'),
              n = m.replace(/(\[\]$)/g, '').replace(/(\]\[)/g, '__').replace(/\[/g, '_').replace(/\]/g, ''),
              o = m.replace('[' + d + '][', '[' + g + ']['),
              p = n.replace(d, g),
              q = 0,
              r = n;if ('checkbox' === l.type && m.match(/\[\]$/)) {
            if (q = j[n] ? j[n].length : 0, !q) {
              var s = b(l, 'fieldset.checkboxes'),
                  t = a.querySelector('label[for="' + n + '"]');s && s.setAttribute('id', p), t && (t.setAttribute('for', p), t.setAttribute('id', p + '-lbl'));
            }r += q, p += q;
          } else if ('radio' === l.type) {
            if (q = j[n] ? j[n].length : 0, !q) {
              var u = b(l, 'fieldset.radio'),
                  v = a.querySelector('label[for="' + n + '"]');u && u.setAttribute('id', p), v && (v.setAttribute('for', p), v.setAttribute('id', p + '-lbl'));
            }r += q, p += q;
          }j[n] ? j[n].push(!0) : j[n] = [!0], l.setAttribute('name', o), l.setAttribute('id', p);var w = a.querySelector('label[for="' + r + '"]');w && (w.setAttribute('for', p), w.setAttribute('id', p + '-lbl'));
        }
      } }, { key: 'buttonAdd', get: function get() {
        return this.getAttribute('button-add');
      } }, { key: 'buttonRemove', get: function get() {
        return this.getAttribute('button-remove');
      } }, { key: 'buttonMove', get: function get() {
        return this.getAttribute('button-move');
      } }, { key: 'rowsContainer', get: function get() {
        return this.getAttribute('rows-container');
      } }, { key: 'repeatableElement', get: function get() {
        return this.getAttribute('repeatable-element');
      } }, { key: 'minimum', get: function get() {
        return this.getAttribute('minimum');
      } }, { key: 'maximum', get: function get() {
        return this.getAttribute('maximum');
      } }]), c;
  }(HTMLElement);a.define('joomla-field-subform', c);
})(customElements);

},{}]},{},[1]);
