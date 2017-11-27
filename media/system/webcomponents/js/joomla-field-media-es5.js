(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _typeof2 = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () {
  function a(a, b) {
    for (var c, d = 0; d < b.length; d++) {
      c = b[d], c.enumerable = c.enumerable || !1, c.configurable = !0, 'value' in c && (c.writable = !0), Object.defineProperty(a, c.key, c);
    }
  }return function (b, c, d) {
    return c && a(b.prototype, c), d && a(b, d), b;
  };
}(),
    _typeof = 'function' == typeof Symbol && 'symbol' == _typeof2(Symbol.iterator) ? function (a) {
  return typeof a === 'undefined' ? 'undefined' : _typeof2(a);
} : function (a) {
  return a && 'function' == typeof Symbol && a.constructor === Symbol && a !== Symbol.prototype ? 'symbol' : typeof a === 'undefined' ? 'undefined' : _typeof2(a);
};function _classCallCheck(a, b) {
  if (!(a instanceof b)) throw new TypeError('Cannot call a class as a function');
}function _possibleConstructorReturn(a, b) {
  if (!a) throw new ReferenceError('this hasn\'t been initialised - super() hasn\'t been called');return b && ('object' == (typeof b === 'undefined' ? 'undefined' : _typeof2(b)) || 'function' == typeof b) ? b : a;
}function _inherits(a, b) {
  if ('function' != typeof b && null !== b) throw new TypeError('Super expression must either be null or a function, not ' + (typeof b === 'undefined' ? 'undefined' : _typeof2(b)));a.prototype = Object.create(b && b.prototype, { constructor: { value: a, enumerable: !1, writable: !0, configurable: !0 } }), b && (Object.setPrototypeOf ? Object.setPrototypeOf(a, b) : a.__proto__ = b);
}(function () {
  var a = window.Joomla || {};a.selectedFile = {}, window.document.addEventListener('onMediaFileSelected', function (b) {
    a.selectedFile = b.detail;
  }), a.doIt = function (b, c, d) {
    'use strict';
    if (!0 === b.success) {
      if (!b.data[0].url) a.selectedFile.url = !1;else if (/local-/.test(b.data[0].adapter)) {
        var e = a.getOptions('system.paths').rootFull;a.selectedFile.url = b.data[0].url.split(e)[1], a.selectedFile.thumb = !!b.data[0].thumb_path && b.data[0].thumb_path;
      } else b.data[0].thumb_path && (a.selectedFile.thumb = b.data[0].thumb_path);var f = function f(a) {
        return 'object' === ('undefined' == typeof HTMLElement ? 'undefined' : _typeof(HTMLElement)) ? a instanceof HTMLElement : a && 'object' === ('undefined' == typeof a ? 'undefined' : _typeof(a)) && null !== a && 1 === a.nodeType && 'string' == typeof a.nodeName;
      };a.selectedFile.url && (f(c) || 'object' === ('undefined' == typeof c ? 'undefined' : _typeof(c)) ? !f(c) && 'object' === ('undefined' == typeof c ? 'undefined' : _typeof(c)) && c.id ? parent.window.Joomla.editors.instances[c.id].replaceSelection('<img src="' + a.selectedFile.url + '" alt=""/>') : (c.value = a.selectedFile.url, d.updatePreview()) : a.editors.instances[c].replaceSelection('<img src="' + a.selectedFile.url + '" alt=""/>'));
    }
  }, a.getImage = function (b, c, d) {
    return new Promise(function (e, f) {
      var g = a.getOptions('csrf.token'),
          h = a.getOptions('system.paths').rootFull + 'administrator/index.php?option=com_media&format=json';a.request({ url: h + '&task=api.files&url=true&path=' + b.path + '&' + g + '=1&format=json', method: 'GET', perform: !0, headers: { "Content-Type": 'application/json' }, onSuccess: function onSuccess(b) {
          var f = JSON.parse(b);e(a.doIt(f, c, d));
        }, onError: function onError() {
          f();
        } });
    });
  };var b = function (b) {
    function c() {
      return _classCallCheck(this, c), _possibleConstructorReturn(this, (c.__proto__ || Object.getPrototypeOf(c)).apply(this, arguments));
    }return _inherits(c, b), _createClass(c, [{ key: 'connectedCallback', value: function connectedCallback() {
        var a = this.querySelector(this.buttonSelect),
            b = this.querySelector(this.buttonClear);this.show = this.show.bind(this), this.modalClose = this.modalClose.bind(this), this.clearValue = this.clearValue.bind(this), this.setValue = this.setValue.bind(this), this.updatePreview = this.updatePreview.bind(this), a.addEventListener('click', this.show), b && b.addEventListener('click', this.clearValue), this.updatePreview();
      } }, { key: 'disconnectedCallback', value: function disconnectedCallback() {
        var a = this.querySelector(this.buttonClear);a.removeEventListener('click', self);
      } }, { key: 'show', value: function show() {
        var a = this,
            b = this,
            c = this.querySelector(this.input);window.jQuery(this.querySelector('[role="dialog"]')).modal('show'), window.jQuery(this.querySelector(this.buttonSaveSelected)).on('click', function (c) {
          return c.preventDefault(), c.stopPropagation(), a.selectedPath && b.setValue(a.selectedPath), b.modalClose(), !1;
        });
      } }, { key: 'modalClose', value: function modalClose() {
        var b = this.querySelector(this.input);a.getImage(a.selectedFile, b, this), window.jQuery(this.querySelector('[role="dialog"]')).modal('hide');
      } }, { key: 'setValue', value: function setValue(a) {
        var b = window.jQuery(this.querySelector(this.input));b.val(a).trigger('change'), this.updatePreview();
      } }, { key: 'clearValue', value: function clearValue() {
        this.setValue('');
      } }, { key: 'updatePreview', value: function updatePreview() {
        if (-1 !== ['true', 'static'].indexOf(this.preview) && 'false' !== this.preview && this.preview) {
          var b = this.querySelector(this.input),
              c = b.value,
              d = this.querySelector('.field-media-preview');if (!c) d.innerHTML = '<span class="field-media-preview-icon fa fa-picture-o"></span>';else {
            d.innerHTML = '';var e = new Image();switch (this.type) {case 'image':
                e.src = e.src = /http/.test(c) ? c : a.getOptions('system.paths').rootFull + c;break;default:}d.style.width = this.previewWidth, d.appendChild(e);
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
      } }]), c;
  }(HTMLElement);customElements.define('joomla-field-media', b);
})();

},{}]},{},[1]);
