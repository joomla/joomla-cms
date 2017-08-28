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
}var JoomlaFieldMedia = function (a) {
  function b() {
    return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).apply(this, arguments));
  }return _inherits(b, a), _createClass(b, [{ key: 'attributeChangedCallback', value: function attributeChangedCallback(a, b, c) {
      switch (a) {case 'basepath':case 'rootfolder':case 'url':case 'modalcont':case 'input':case 'buttonselect':case 'buttonclear':case 'buttonsaveselected':case 'previewContainer':
          break;case 'modalwidth':case 'modalheight':case 'previewwidth':case 'previewheight':
          break;case 'preview':
          -1 < ['true', 'false', 'tooltip', 'static'].indexOf(c) && b !== c && (this.preview = c);break;default:}
    } }, { key: 'connectedCallback', value: function connectedCallback() {
      var a = this,
          b = this.querySelector(this.buttonselect),
          c = this.querySelector(this.buttonclear);b.addEventListener('click', function () {
        a.show(a);
      }), c && c.addEventListener('click', function () {
        a.clearValue();
      }), this.updatePreview();
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
      var a = this.querySelector(this.buttonselect);a.removeEventListener('click', self);
    } }, { key: 'show', value: function show(a) {
      window.jQuery(this.querySelector('[role="dialog"]')).modal('show'), window.jQuery(this.querySelector(this.buttonsaveselected)).on('click', function (b) {
        return b.preventDefault(), b.stopPropagation(), a.selectedPath ? a.setValue(a.rootfolder + a.selectedPath) : a.setValue(''), a.modalClose(a), !1;
      }), window.document.addEventListener('onMediaFileSelected', function (b) {
        var c = b.item.path;a.selectedPath = c.match(/.jpg|.jpeg|.gif|.png/) ? b.item.path : '';
      });
    } }, { key: 'modalClose', value: function modalClose(a) {
      window.jQuery(a.querySelector('[role="dialog"]')).modal('hide');
    } }, { key: 'setValue', value: function setValue(a) {
      var b = window.jQuery(this.querySelector(this.input));b.val(a).trigger('change'), this.updatePreview();
    } }, { key: 'clearValue', value: function clearValue() {
      this.setValue('');
    } }, { key: 'updatePreview', value: function updatePreview() {
      if (-1 !== ['true', 'tooltip', 'static'].indexOf(this.preview) && 'false' !== this.preview && this.preview) {
        var a = window.jQuery(this.querySelector(this.previewcontainer)),
            b = window.jQuery(this.querySelector(this.input)),
            c = b.val();if (a.popover('dispose'), b.tooltip('dispose'), !c) a.popover({ content: Joomla.JText._('JLIB_FORM_MEDIA_PREVIEW_EMPTY'), html: !0 });else {
          var d = new Image(this.previewwidth, this.previewheight);d.src = this.basepath + c, a.popover({ content: d, html: !0 }), b.tooltip({ placement: 'top', title: c });
        }
      }
    } }, { key: 'basepath', get: function get() {
      return this.getAttribute('basepath');
    }, set: function set(a) {
      this.setAttribute('basepath', a);
    } }, { key: 'rootfolder', get: function get() {
      return this.getAttribute('rootfolder');
    }, set: function set(a) {
      this.setAttribute('rootfolder', a);
    } }, { key: 'url', get: function get() {
      return this.getAttribute('url');
    }, set: function set(a) {
      this.setAttribute('url', a);
    } }, { key: 'modalcont', get: function get() {
      return this.getAttribute('modalcont');
    }, set: function set(a) {
      this.setAttribute('modalcont', a);
    } }, { key: 'input', get: function get() {
      return this.getAttribute('input');
    }, set: function set(a) {
      this.setAttribute('input', a);
    } }, { key: 'buttonselect', get: function get() {
      return this.getAttribute('buttonselect');
    }, set: function set(a) {
      this.setAttribute('buttonselect', a);
    } }, { key: 'buttonclear', get: function get() {
      return this.getAttribute('buttonclear');
    }, set: function set(a) {
      this.setAttribute('buttonclear', a);
    } }, { key: 'buttonsaveselected', get: function get() {
      return this.getAttribute('buttonsaveselected');
    }, set: function set(a) {
      this.setAttribute('buttonsaveselected', a);
    } }, { key: 'modalwidth', get: function get() {
      return this.getAttribute(parseInt('modalwidth', 10));
    }, set: function set(a) {
      this.setAttribute('modalwidth', a);
    } }, { key: 'modalheight', get: function get() {
      return this.getAttribute(parseInt('modalheight', 10));
    }, set: function set(a) {
      this.setAttribute('modalheight', a);
    } }, { key: 'previewwidth', get: function get() {
      return this.getAttribute('previewwidth');
    }, set: function set(a) {
      this.setAttribute('previewwidth', a);
    } }, { key: 'previewheight', get: function get() {
      return this.getAttribute('previewheight');
    }, set: function set(a) {
      this.setAttribute('previewheight', a);
    } }, { key: 'preview', get: function get() {
      return this.getAttribute('preview');
    }, set: function set(a) {
      this.setAttribute('preview', a);
    } }, { key: 'previewcontainer', get: function get() {
      return this.getAttribute('previewcontainer');
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['basepath', 'rootfolder', 'url', 'modalcont', 'modalwidth', 'modalheight', 'input', 'buttonselect', 'buttonclear', 'buttonsaveselected', 'preview', 'previewwidth', 'previewheight'];
    } }]), b;
}(HTMLElement);customElements.define('joomla-field-media', JoomlaFieldMedia);

},{}]},{},[1]);
