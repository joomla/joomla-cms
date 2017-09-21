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
}var Joomla = window.Joomla || {},
    JoomlaFieldMedia = function (a) {
  function b() {
    return _classCallCheck(this, b), _possibleConstructorReturn(this, (b.__proto__ || Object.getPrototypeOf(b)).apply(this, arguments));
  }return _inherits(b, a), _createClass(b, [{ key: 'connectedCallback', value: function connectedCallback() {
      console.log(this.buttonClear);var a = this.querySelector(this.buttonSelect),
          b = this.querySelector(this.buttonClear);this.show = this.show.bind(this), this.modalClose = this.modalClose.bind(this), this.clearValue = this.clearValue.bind(this), this.setValue = this.setValue.bind(this), this.updatePreview = this.updatePreview.bind(this), a.addEventListener('click', this.show), b && b.addEventListener('click', this.clearValue), this.updatePreview();
    } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
      var a = this.querySelector(this.buttonClear);a.removeEventListener('click', self);
    } }, { key: 'show', value: function show() {
      var a = this,
          b = this,
          c = this.querySelector(this.input);window.jQuery(this.querySelector('[role="dialog"]')).modal('show'), window.jQuery(this.querySelector(this.buttonSaveSelected)).on('click', function (c) {
        return c.preventDefault(), c.stopPropagation(), a.selectedPath && b.setValue(a.selectedPath), b.modalClose(), !1;
      }), window.document.addEventListener('onMediaFileSelected', function (a) {
        console.log(a.detail), b.selectedPath = a.detail.path;
      });
    } }, { key: 'modalClose', value: function modalClose() {
      window.jQuery(this.querySelector('[role="dialog"]')).modal('hide');
    } }, { key: 'setValue', value: function setValue(a) {
      var b = window.jQuery(this.querySelector(this.input));b.val(a).trigger('change'), this.updatePreview();
    } }, { key: 'clearValue', value: function clearValue() {
      this.setValue('');
    } }, { key: 'updatePreview', value: function updatePreview() {
      if (-1 !== ['true', 'tooltip', 'static'].indexOf(this.preview) && 'false' !== this.preview && this.preview) {
        var a = window.jQuery(this.querySelector(this.previewContainer)),
            b = window.jQuery(this.querySelector(this.input)),
            c = b.val();if (a.popover('dispose'), b.tooltip('dispose'), !c) a.popover({ content: Joomla.JText._('JLIB_FORM_MEDIA_PREVIEW_EMPTY'), html: !0 });else {
          var d = document.createElement('div'),
              e = new Image();switch (this.type) {case 'image':
              e.src = this.basePath + c;break;default:}d.style.width = this.previewWidth, e.style.width = '100%', d.appendChild(e), a.popover({ html: !0, content: d }), b.tooltip({ placement: 'top', title: c });
        }
      }
    } }, { key: 'type', get: function get() {
      return this.getAttribute('type');
    }, set: function set(a) {
      this.setAttribute('type', a);
    } }, { key: 'basePath', get: function get() {
      return this.getAttribute('base-path');
    }, set: function set(a) {
      this.setAttribute('base-path', a);
    } }, { key: 'rootFolder', get: function get() {
      return this.getAttribute('root-folder');
    }, set: function set(a) {
      this.setAttribute('root-folder', a);
    } }, { key: 'url', get: function get() {
      return this.getAttribute('url');
    }, set: function set(a) {
      this.setAttribute('url', a);
    } }, { key: 'modalContainer', get: function get() {
      return this.getAttribute('modal-container');
    }, set: function set(a) {
      this.setAttribute('modal-container', a);
    } }, { key: 'input', get: function get() {
      return this.getAttribute('input');
    }, set: function set(a) {
      this.setAttribute('input', a);
    } }, { key: 'buttonSelect', get: function get() {
      return this.getAttribute('button-select');
    }, set: function set(a) {
      this.setAttribute('button-select', a);
    } }, { key: 'buttonClear', get: function get() {
      return this.getAttribute('button-clear');
    }, set: function set(a) {
      this.setAttribute('button-clear', a);
    } }, { key: 'buttonSaveSelected', get: function get() {
      return this.getAttribute('button-save-selected');
    }, set: function set(a) {
      this.setAttribute('button-save-selected', a);
    } }, { key: 'modalWidth', get: function get() {
      return this.getAttribute(parseInt('modal-width', 10));
    }, set: function set(a) {
      this.setAttribute('modal-width', a);
    } }, { key: 'modalHeight', get: function get() {
      return this.getAttribute(parseInt('modal-height', 10));
    }, set: function set(a) {
      this.setAttribute('modal-height', a);
    } }, { key: 'previewWidth', get: function get() {
      return this.getAttribute(parseInt('preview-width', 10));
    }, set: function set(a) {
      this.setAttribute('preview-width', a);
    } }, { key: 'previewHeight', get: function get() {
      return this.getAttribute(parseInt('preview-height', 10));
    }, set: function set(a) {
      this.setAttribute('preview-height', a);
    } }, { key: 'preview', get: function get() {
      return this.getAttribute('preview');
    }, set: function set(a) {
      this.setAttribute('preview', a);
    } }, { key: 'previewContainer', get: function get() {
      return this.getAttribute('preview-container');
    } }], [{ key: 'observedAttributes', get: function get() {
      return ['type', 'base-path', 'root-folder', 'url', 'modal-container', 'modal-width', 'modal-height', 'input', 'button-select', 'button-clear', 'button-save-selected', 'preview', 'preview-width', 'preview-height'];
    } }]), b;
}(HTMLElement);customElements.define('joomla-field-media', JoomlaFieldMedia);

},{}]},{},[1]);
