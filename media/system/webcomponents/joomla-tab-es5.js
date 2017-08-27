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
}if (!document.head.querySelector('#joomla-tab-style')) {
  var style = document.createElement('style');style.id = 'joomla-tab-style', style.innerHTML = 'joomla-tab{display:flex;flex-direction:column}joomla-tab>ul{display:flex;background-color:#f5f5f5;border-color:#ccc #ccc currentcolor;border-image:none;border-radius:.25rem .25rem 0 0;border-style:solid solid none;border-width:1px 1px 0;box-shadow:0 1px #fff inset,0 2px 3px -3px rgba(0,0,0,.15),0 -4px 0 rgba(0,0,0,.05) inset,0 0 3px rgba(0,0,0,.04);margin:0;padding:0;list-style:outside none none;overflow-x:auto;overflow-y:hidden;white-space:nowrap}joomla-tab a[role=tab]{display:block;color:#0d1321;padding:.75em 1em;position:relative;box-shadow:1px 0 0 rgba(0,0,0,.05);text-decoration:none}joomla-tab a[role=tab][active]{background-color:rgba(0,0,0,.03);background-image:linear-gradient(to bottom,transparent,rgba(0,0,0,.05) 100%);border-left:0 none;border-right:0 none;border-top-left-radius:0;border-top-right-radius:0;box-shadow:2px 0 1px -1px rgba(0,0,0,.08) inset,-2px 0 1px -1px rgba(0,0,0,.08) inset,0 1px 0 rgba(0,0,0,.02) inset}joomla-tab a[role=tab][active]:after{background-color:#006898;bottom:-1px;content:"";height:5px;left:0;opacity:.8;position:absolute;right:0}joomla-tab>section{display:none;background-color:#fefefe;border:1px solid #ccc;border-radius:0 0 .25rem .25rem;box-shadow:0 0 3px rgba(0,0,0,.04);padding:15px}joomla-tab>section[active]{display:block}joomla-tab[orientation=vertical]{flex-direction:row;align-items:flex-start}joomla-tab[orientation=vertical]>ul{flex-direction:column;min-width:30%;height:auto;border:1px solid #ccc;border-radius:.25rem;box-shadow:none;overflow:hidden}joomla-tab[orientation=vertical] li:last-of-type a{border-bottom:0}joomla-tab[orientation=vertical] a{display:block;color:#0d1321;padding:.75em 1em;position:relative;border-bottom:1px solid #ddd;box-shadow:none;text-decoration:none}joomla-tab[orientation=vertical] a[active]{border-left:0 none;border-right:0 none;background-color:#fff;background-image:none;box-shadow:none}joomla-tab[orientation=vertical] a[active]:after{left:-1px;width:5px;height:auto;top:0;bottom:0}joomla-tab[orientation=vertical]>section{border:0 none;box-shadow:none;padding:15px}joomla-tab[view=accordion]>ul{flex-direction:column;border-radius:.25rem;white-space:normal;box-shadow:0 1px #fff inset,0 0 3px rgba(0,0,0,.04)}joomla-tab[view=accordion] section{display:none;padding:15px}joomla-tab[view=accordion] section[active]{display:block;border-bottom:1px solid #ddd}joomla-tab[view=accordion] [active]{background-color:#fff}joomla-tab[view=accordion] a[role=tab]{border-bottom:1px solid #ddd}joomla-tab[view=accordion] a[role=tab][active]:after{width:5px;height:100%;top:0;left:0}', document.head.appendChild(style);
}var JoomlaTabElement = function (a) {
  function b() {
    _classCallCheck(this, b);var a = _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).call(this));return a.hasActive = !1, a.currentActive = '', a.hasNested = !1, a.isNested = !1, a;
  }return _inherits(b, a), _createClass(b, [{ key: 'recall', get: function get() {
      return this.getAttribute('recall');
    } }, { key: 'view', get: function get() {
      return this.getAttribute('view');
    }, set: function set(a) {
      this.setAttribute('view', a);
    } }, { key: 'orientation', get: function get() {
      return this.getAttribute('orientation');
    }, set: function set(a) {
      this.setAttribute('oriendation', a);
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['recall', 'orientation', 'view'];
    } }]), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      var a = this;(!this.orientation || this.orientation && -1 === ['horizontal', 'vertical'].indexOf(this.orientation)) && (this.orientation = 'horizontal');var b = this,
          c = [].slice.call(this.querySelectorAll('section'));if (c) {
        var d = [];if (this.findAncestor(this, 'joomla-tab') && (this.isNested = !0), this.querySelector('joomla-tab')) {
          this.hasNested = !0;for (var e, f = 0, g = c.length; f < g; ++f) {
            e = c[f], e.parentNode === b && d.push(e);
          }
        } else d = c;if ('accordion' !== this.view && this.createNavigation(d), d.forEach(function (b) {
          b.setAttribute('role', 'tabpanel'), b.hasAttribute('active') && (a.hasActive = !0, a.currentActive = b.id, a.querySelector('#tab-' + b.id).setAttribute('aria-selected', 'true'), a.querySelector('#tab-' + b.id).setAttribute('active', ''), a.querySelector('#tab-' + b.id).setAttribute('tabindex', '0'));
        }), this.hasActive || (d[0].setAttribute('active', ''), this.hasActive = !0, this.currentActive = d[0].id, this.querySelector('#tab-' + d[0].id).setAttribute('aria-selected', 'true'), this.querySelector('#tab-' + d[0].id).setAttribute('tabindex', '0'), this.querySelector('#tab-' + d[0].id).setAttribute('active', '')), this.addKeyListeners(d), window.location.href.match(/#\S[^&]*/)) {
          var h = window.location.href.match(/#\S[^&]*/),
              i = this.querySelector(h[0]);if (i) {
            var j = this.findAncestor(i, 'joomla-tab'),
                k = this.findAncestor(j, 'joomla-tab');if (k) {
              var l = this.findAncestor(j, 'section');k.showTab(l), this.show(i);
            } else this.showTab(i);
          }
        }this.hasAttribute('recall') && this.restoreState(), window.addEventListener('resize', function () {
          b.checkView(b);
        });
      }
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
      var a = this,
          b = this.querySelector('ul'),
          c = [].slice.call(b.querySelectorAll('a'));c.forEach(function (b) {
        b.removeEventListener('click', a);
      }), b.removeEventListener('keydown', this);
    } }, { key: 'createNavigation', value: function createNavigation(a) {
      var b = this;if ('ul' !== this.firstElementChild.nodeName.toLowerCase()) {
        var c = document.createElement('ul');c.setAttribute('role', 'tablist');var d = function d(a) {
          a.preventDefault(), b.hasActive && b.hideCurrent();var c = b.currentActive;b.dispatchCustomEvent('joomla.tab.show', a.target, b.querySelector('#tab-' + c)), a.target.setAttribute('active', ''), a.target.setAttribute('aria-selected', 'true'), a.target.setAttribute('tabindex', '0'), b.querySelector(a.target.hash).setAttribute('active', ''), b.querySelector(a.target.hash).removeAttribute('aria-hidden'), b.currentActive = a.target.hash.substring(1), b.dispatchCustomEvent('joomla.tab.shown', a.target, b.querySelector('#tab-' + c));
        };a.forEach(function (a) {
          if (a.id) {
            var b = a.hasAttribute('active'),
                e = document.createElement('li'),
                f = document.createElement('a');e.setAttribute('role', 'presentation'), f.setAttribute('role', 'tab'), f.setAttribute('aria-controls', a.id), f.setAttribute('aria-selected', b ? 'true' : 'false'), f.setAttribute('tabindex', b ? '0' : '-1'), f.setAttribute('href', '#' + a.id), f.setAttribute('id', 'tab-' + a.id), f.innerHTML = a.getAttribute('name'), b && f.setAttribute('active', ''), f.addEventListener('click', d), e.append(f), c.append(e), a.setAttribute('aria-labelledby', 'tab-' + a.id), b || a.setAttribute('aria-hidden', 'true');
          }
        }), this.insertAdjacentElement('afterbegin', c), this.addKeyListeners();
      }
    } }, { key: 'hideCurrent', value: function hideCurrent() {
      if (this.currentActive) {
        var a = this.querySelector('a[aria-controls="' + this.currentActive + '"]');this.dispatchCustomEvent('joomla.tab.hide', a, this.querySelector('#tab-' + this.currentActive)), a.removeAttribute('active'), a.setAttribute('tabindex', '-1'), this.querySelector('#' + this.currentActive).removeAttribute('active'), this.querySelector('#' + this.currentActive).setAttribute('aria-hidden', 'true'), a.removeAttribute('aria-selected'), this.dispatchCustomEvent('joomla.tab.hidden', a, this.querySelector('#tab-' + this.currentActive));
      }
    } }, { key: 'showTab', value: function showTab(a) {
      var b = document.querySelector('#tab-' + a.id);b.click(), this.saveState('#' + a.id);
    } }, { key: 'show', value: function show(a) {
      a.click(), this.saveState(a.hash);
    } }, { key: 'addKeyListeners', value: function addKeyListeners() {
      var a = this;this.querySelector('ul').addEventListener('keyup', function keyBehaviour(b) {
        var c = a.querySelector('#tab-' + a.currentActive),
            d = [].slice.call(a.querySelector('ul').querySelectorAll('a')),
            e = c.parentNode.previousElementSibling || c.parentNode.parentNode.lastElementChild,
            f = c.parentNode.nextElementSibling || c.parentNode.parentNode.firstElementChild;if (!(b.metaKey || b.altKey)) {
          if (document.activeElement !== c) return void b.preventDefault();switch (b.keyCode) {case 37:case 38:
              e.querySelector('a').click(), e.querySelector('a').focus(), b.preventDefault();break;case 39:case 40:
              f.querySelector('a').click(), f.querySelector('a').focus(), b.preventDefault();break;default:}
        }
      });
    } }, { key: 'getStorageKey', value: function getStorageKey() {
      return window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').split('#')[0];
    } }, { key: 'restoreState', value: function restoreState() {
      var a = sessionStorage.getItem(this.getStorageKey());if (a) {
        var b = this.querySelector(a);if (b) {
          var c = this.findAncestor(b, 'joomla-tab'),
              d = this.findAncestor(c, 'joomla-tab');if (d) {
            var e = this.findAncestor(c, 'section');d.showTab(e), this.show(b);
          } else this.showTab(b);
        }
      }
    } }, { key: 'saveState', value: function saveState(a) {
      var b = this.getStorageKey();sessionStorage.setItem(b, a);
    } }, { key: 'checkView', value: function checkView(a) {
      var b = a.querySelector('ul'),
          c = [];if (920 < document.body.getBoundingClientRect().width) {
        if ('tabs' === this.view) return;a.view = 'tabs';for (var d = [].slice.call(b.querySelectorAll('section')), e = 0, f = d.length; e < f; ++e) {
          console.log(d[e]), d[e].parentNode.parentNode.parentNode === a && c.push(d[e]);
        }c.length && c.forEach(function (b) {
          a.appendChild(b);
        });
      } else {
        if ('accordion' === this.view) return;a.view = 'accordion';for (var g = [].slice.call(a.querySelectorAll('section')), h = 0, i = g.length; h < i; ++h) {
          g[h].parentNode === a && c.push(g[h]);
        }c.length && c.forEach(function (b) {
          var c = a.querySelector('a[aria-controls="' + b.id + '"]');c.parentNode.parentNode === a.firstElementChild && c.parentNode.appendChild(b);
        });
      }
    } }, { key: 'findAncestor', value: function findAncestor(a, b) {
      for (; (a = a.parentElement) && a.nodeName.toLowerCase() !== b;) {}return a;
    } }, { key: 'dispatchCustomEvent', value: function dispatchCustomEvent(a, b, c) {
      var d = new CustomEvent(a, { bubbles: !0, cancelable: !0 });d.relatedTarget = c, b.dispatchEvent(d), b.removeEventListener(a, b);
    } }]), b;
}(HTMLElement);customElements.define('joomla-tab', JoomlaTabElement);

},{}]},{},[1]);
