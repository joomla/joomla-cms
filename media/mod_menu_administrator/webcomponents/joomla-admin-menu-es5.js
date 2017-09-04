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
}var JoomlaAdminMenu = function (a) {
  function b() {
    _classCallCheck(this, b);var a = _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).call(this));return a.wrapper = document.getElementById('wrapper'), a.sidebar = document.getElementById('sidebar-wrapper'), a.allLinks = '', a.currentUrl = '', a.mainNav = '', a.menuParents = '', a.subMenuClose = '', a.menuToggle = '', a.first = '', a;
  }return _inherits(b, a), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      if (!this.wrapper || !this.sidebar) throw new Error('HTML markup error initialising menu');var a = this;if (Joomla.localStorageEnabled()) {
        var b = localStorage.getItem('atum-sidebar');'open' === b || null === b ? (this.wrapper.classList.remove('closed'), localStorage.setItem('atum-sidebar', 'open')) : (this.wrapper.classList.add('closed'), localStorage.setItem('atum-sidebar', 'closed'));
      }if (this.sidebar || this.wrapper.classList.remove('closed'), this.wrapper.classList.contains('wrapper0') && (document.querySelector('.subhead') && (document.querySelector('.subhead').style.left = 0), document.getElementById('status') && (document.getElementById('status').style.marginLeft = 0)), this.allLinks = [].slice.call(this.wrapper.querySelectorAll('a.no-dropdown, a.collapse-arrow')), this.currentUrl = window.location.href.toLowerCase(), this.mainNav = document.getElementById('menu'), this.menuParents = [].slice.call(this.mainNav.querySelectorAll('li.parent > a')), this.subMenuClose = [].slice.call(this.mainNav.querySelectorAll('li.parent .close')), this.menuToggle = document.getElementById('menu-collapse'), this.first = [].slice.call(this.sidebar.querySelectorAll('.collapse-level-1')), this.first.forEach(function (a) {
        var b = [].slice.call(a.querySelectorAll('.collapse-level-1'));b.forEach(function (a) {
          a && (a.classList.remove('collapse-level-1'), a.classList.add('collapse-level-2'));
        });
      }), this.menuToggle.addEventListener('click', function () {
        a.wrapper.classList.toggle('closed');var b = [].slice.call(document.querySelectorAll('.main-nav > li'));b.forEach(function (a) {
          a.classList.remove('open');
        });var c = document.querySelector('.child-open');c && c.classList.remove('child-open'), Joomla.localStorageEnabled() && (a.wrapper.classList.contains('closed') ? localStorage.setItem('atum-sidebar', 'closed') : localStorage.setItem('atum-sidebar', 'open'));
      }), this.allLinks.forEach(function (b) {
        if (a.currentUrl === b.href && (b.classList.add('active'), !b.parentNode.classList.contains('parent'))) {
          a.mainNav.classList.add('child-open');var c = a.closest(b, '.collapse-level-1');c && c.parentNode.classList.add('open');
        }
      }), document.body.classList.contains('com_cpanel') || document.body.classList.contains('com_media')) {
        var c = [].slice.call(this.mainNav.querySelectorAll('.open'));c.forEach(function (a) {
          a.classList.remove('open');
        }), this.mainNav.classList.remove('child-open');
      }this.menuParents.forEach(function (b) {
        b.addEventListener('click', a.openToggle.bind(a));
      }), this.subMenuClose.forEach(function (b) {
        b.addEventListener('click', function () {
          var b = a.mainNav.querySelectorAll('.open');b.forEach(function (a) {
            a.classList.remove('open');
          }), a.mainNav.classList.remove('child-open');
        });
      }), this.setMenuHeight(), window.addEventListener('resize', a.setMenuHeight.bind(a)), Joomla.localStorageEnabled() && 'true' == localStorage.getItem('adminMenuState') && a.menuClose.bind(a);
    } }, { key: 'closest', value: function closest(a, b) {
      var c;['matches', 'msMatchesSelector'].some(function (a) {
        return 'function' == typeof document.body[a] && (c = a, !0);
      });for (var d; a;) {
        if (d = a.parentElement, d && d[c](b)) return d;a = d;
      }return null;
    } }, { key: 'setMenuHeight', value: function setMenuHeight() {
      var a = document.getElementById('header').offsetHeight + document.getElementById('main-brand').offsetHeight;document.getElementById('menu').height = window.height - a;
    } }, { key: 'openToggle', value: function openToggle(a) {
      var b = this.findAncestor(a.target, 'li');if (b.classList.contains('open')) this.mainNav.classList.remove('child-open'), b.classList.remove('open');else {
        var c = [].slice.call(b.parentNode.children);c.forEach(function (a) {
          a.classList.remove('open');
        }), this.wrapper.classList.remove('closed'), this.mainNav.classList.add('child-open'), b.parentNode.classList.contains('main-nav') && b.classList.add('open');
      }
    } }, { key: 'menuClose', value: function menuClose() {
      this.sidebar.querySelector('.collapse').classList.remove('in'), this.sidebar.querySelector('.collapse-arrow').classList.add('collapsed');
    } }, { key: 'findAncestor', value: function findAncestor(a, b) {
      for (; (a = a.parentElement) && a.nodeName.toLowerCase() !== b;) {}return a;
    } }]), b;
}(HTMLElement);customElements.define('joomla-admin-menu', JoomlaAdminMenu);

},{}]},{},[1]);
