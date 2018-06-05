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
    for (var c; a;) {
      if (c = a.parentElement, c && c[e](b)) return c;a = c;
    }return null;
  }function c(a) {
    return a.ctrlKey || a.metaKey || a.shiftKey;
  }var d = { SPACE: 32, ESC: 27, ENTER: 13 },
      e = 'matches';['matches', 'msMatchesSelector'].some(function (a) {
    return !('function' != typeof document.body[a]) && (e = a, !0);
  });var f = function (a) {
    function f() {
      _classCallCheck(this, f);var a = _possibleConstructorReturn(this, (f.__proto__ || Object.getPrototypeOf(f)).call(this)),
          c = a;if (a.containerWithRows = a, a.rowsContainer) for (var g = a.querySelectorAll(a.rowsContainer), h = 0, i = g.length; h < i; h++) {
        if (b(g[h], 'joomla-field-subform') === a) {
          a.containerWithRows = g[h];break;
        }
      }return a.lastRowNum = a.getRows().length, a.template = '', a.prepareTemplate(), (a.buttonAdd || a.buttonRemove) && (a.addEventListener('click', function (a) {
        var d = null,
            f = null;if (c.buttonAdd && (d = a.target[e](c.buttonAdd) ? a.target : b(a.target, c.buttonAdd)), c.buttonRemove && (f = a.target[e](c.buttonRemove) ? a.target : b(a.target, c.buttonRemove)), d && b(d, 'joomla-field-subform') === c) {
          var g = b(d, c.repeatableElement);g = b(g, 'joomla-field-subform') === c ? g : null, c.addRow(g), a.preventDefault();
        } else if (f && b(f, 'joomla-field-subform') === c) {
          var h = b(f, c.repeatableElement);c.removeRow(h), a.preventDefault();
        }
      }), a.addEventListener('keydown', function (a) {
        if (a.keyCode === d.SPACE) {
          var f = c.buttonAdd && a.target[e](c.buttonAdd),
              g = c.buttonRemove && a.target[e](c.buttonRemove);if ((f || g) && b(a.target, 'joomla-field-subform') === c) {
            var h = b(a.target, c.repeatableElement);h = b(h, 'joomla-field-subform') === c ? h : null, g && h ? c.removeRow(h) : f && c.addRow(h), a.preventDefault();
          }
        }
      })), a.buttonMove && a.setUpDragSort(), a;
    }return _inherits(f, a), _createClass(f, [{ key: 'buttonAdd', get: function get() {
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
      } }, { key: 'name', get: function get() {
        return this.getAttribute('name');
      }, set: function set(a) {
        return this.template = this.template.replace(new RegExp(' name="' + this.name.replace(/[\[\]]/g, '\\$&'), 'g'), ' name="' + a), this.setAttribute('name', a);
      } }]), _createClass(f, [{ key: 'getRows', value: function getRows() {
        for (var a = this.containerWithRows.children, b = document.body.msMatchesSelector ? 'msMatchesSelector' : 'matches', c = [], d = 0, e = a.length; d < e; d++) {
          a[d][b](this.repeatableElement) && c.push(a[d]);
        }return result;
      } }, { key: 'prepareTemplate', value: function prepareTemplate() {
        var a = [].slice.call(this.children).filter(function (a) {
          return a.classList.contains('subform-repeatable-template-section');
        });if (a[0] && (this.template = a[0].innerHTML), !this.template) throw new Error('The row template are required to subform element to work');
      } }, { key: 'addRow', value: function addRow(a) {
        var b = this.getRows().length;if (b >= this.maximum) return null;var c;c = 'TBODY' === this.containerWithRows.nodeName || 'TABLE' === this.containerWithRows.nodeName ? document.createElement('tbody') : document.createElement('div'), c.innerHTML = this.template;var d = c.children[0];return a ? a.parentNode.insertBefore(d, a.nextSibling) : this.containerWithRows.append(d), this.buttonMove && (d.setAttribute('draggable', 'false'), d.setAttribute('aria-grabbed', 'false'), d.setAttribute('tabindex', '0')), d.setAttribute('data-new', '1'), this.fixUniqueAttributes(d, b), this.dispatchEvent(new CustomEvent('subform-row-add', { detail: { row: d }, bubbles: !0 })), window.Joomla && Joomla.Event.dispatch(d, 'joomla:updated'), d;
      } }, { key: 'removeRow', value: function removeRow(a) {
        var b = this.getRows().length;b <= this.minimum || (this.dispatchEvent(new CustomEvent('subform-row-remove', { detail: { row: a }, bubbles: !0 })), window.Joomla && Joomla.Event.dispatch(a, 'joomla:removed'), a.parentNode.removeChild(a));
      } }, { key: 'fixUniqueAttributes', value: function fixUniqueAttributes(a, c) {
        var d = this;this.lastRowNum++, c = c || 0;var e = a.getAttribute('data-group'),
            f = a.getAttribute('data-base-name'),
            g = Math.max(this.lastRowNum, c + 1),
            h = f + g;this.lastRowNum = g, a.setAttribute('data-group', h);var j = a.querySelectorAll('[name]'),
            k = {};j = [].slice.call(j).filter(function (a) {
          return b(a, 'joomla-field-subform') === d;
        });for (var m = 0, i = j.length; m < i; m++) {
          var l = j[m],
              n = l.getAttribute('name'),
              o = n.replace(/(\[\]$)/g, '').replace(/(\]\[)/g, '__').replace(/\[/g, '_').replace(/\]/g, ''),
              p = n.replace('[' + e + '][', '[' + h + ']['),
              q = o.replace(e, h),
              r = 0,
              s = o;if ('checkbox' === l.type && n.match(/\[\]$/)) {
            if (r = k[o] ? k[o].length : 0, !r) {
              var t = b(l, 'fieldset.checkboxes'),
                  u = a.querySelector('label[for="' + o + '"]');t && t.setAttribute('id', q), u && (u.setAttribute('for', q), u.setAttribute('id', q + '-lbl'));
            }s += r, q += r;
          } else if ('radio' === l.type) {
            if (r = k[o] ? k[o].length : 0, !r) {
              var v = b(l, 'fieldset.radio'),
                  w = a.querySelector('label[for="' + o + '"]');v && v.setAttribute('id', q), w && (w.setAttribute('for', q), w.setAttribute('id', q + '-lbl'));
            }s += r, q += r;
          }k[o] ? k[o].push(!0) : k[o] = [!0], l.name = p, l.id && (l.id = q);var x = a.querySelector('label[for="' + s + '"]');x && (x.setAttribute('for', q), x.setAttribute('id', q + '-lbl'));
        }
      } }, { key: 'setUpDragSort', value: function setUpDragSort() {
        function a(a) {
          return !a.form && a[e](that.buttonMove) ? a : b(a, that.buttonMove);
        }function f(a, b) {
          var c = !1;if (a.parentNode === b.parentNode) for (var d = a; d; d = d.previousSibling) {
            if (d === b) {
              c = !0;break;
            }
          }c ? b.parentNode.insertBefore(a, b) : b.parentNode.insertBefore(a, b.nextSibling);
        }for (var g, h = this, i = this.getRows(), j = 0, k = i.length; j < k; j++) {
          g = i[j], g.setAttribute('draggable', 'false'), g.setAttribute('aria-grabbed', 'false'), g.setAttribute('tabindex', '0');
        }this.addEventListener('touchstart', function (c) {
          touched = !0;var d = a(c.target),
              e = d ? b(d, that.repeatableElement) : null;e && b(e, 'joomla-field-subform') === that && (item ? (e !== item && f(item, e), item.setAttribute('draggable', 'false'), item.setAttribute('aria-grabbed', 'false'), item = null) : (e.setAttribute('draggable', 'true'), e.setAttribute('aria-grabbed', 'true'), item = e), c.preventDefault());
        }), this.addEventListener('mousedown', function (c) {
          if (!touched) {
            var d = a(c.target),
                e = d ? b(d, that.repeatableElement) : null;e && b(e, 'joomla-field-subform') === that && (e.setAttribute('draggable', 'true'), e.setAttribute('aria-grabbed', 'true'), item = e);
          }
        }), this.addEventListener('mouseup', function () {
          item && !touched && (item.setAttribute('draggable', 'false'), item.setAttribute('aria-grabbed', 'false'), item = null);
        }), this.addEventListener('keydown', function (a) {
          if ((a.keyCode === d.ESC || a.keyCode === d.SPACE || a.keyCode === d.ENTER) && !a.target.form && a.target[e](that.repeatableElement)) {
            var g = a.target;if (g && b(g, 'joomla-field-subform') === that && (a.keyCode === d.SPACE && c(a) && ('true' === g.getAttribute('aria-grabbed') ? (g.setAttribute('draggable', 'false'), g.setAttribute('aria-grabbed', 'false'), item = null) : (item && (item.setAttribute('draggable', 'false'), item.setAttribute('aria-grabbed', 'false'), item = null), g.setAttribute('draggable', 'true'), g.setAttribute('aria-grabbed', 'true'), item = g), a.preventDefault()), a.keyCode === d.ESC && item && (item.setAttribute('draggable', 'false'), item.setAttribute('aria-grabbed', 'false'), item = null), a.keyCode === d.ENTER && item)) {
              if (item.setAttribute('draggable', 'false'), item.setAttribute('aria-grabbed', 'false'), g === item) return void (item = null);f(item, g), a.preventDefault(), item = null;
            }
          }
        }), this.addEventListener('dragstart', function (a) {
          item && (a.dataTransfer.effectAllowed = 'move', a.dataTransfer.setData('text', ''));
        }), this.addEventListener('dragover', function (a) {
          item && a.preventDefault();
        }), this.addEventListener('dragenter', function (a) {
          if (item && (!that.rowsContainer || b(a.target, that.rowsContainer) === that.containerWithRows)) {
            var c = a.target[e](that.repeatableElement) ? a.target : b(a.target, that.repeatableElement);c && f(item, c);
          }
        }), this.addEventListener('dragend', function () {
          item && (item.setAttribute('draggable', 'false'), item.setAttribute('aria-grabbed', 'false'), item = null);
        });
      } }]), f;
  }(HTMLElement);a.define('joomla-field-subform', f);
})(customElements);

},{}]},{},[1]);
