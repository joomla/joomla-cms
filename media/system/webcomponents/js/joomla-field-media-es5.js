(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

(function (customElements, Joomla) {
  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }

  Joomla.selectedFile = {};

  window.document.addEventListener('onMediaFileSelected', function (e) {
    Joomla.selectedFile = e.detail;
  });

  Joomla.doIt = function (resp, editor, fieldClass) {
    if (resp.success === true) {
      if (resp.data[0].url) {
        if (/local-/.test(resp.data[0].adapter)) {
          var server = Joomla.getOptions('system.paths').rootFull;

          Joomla.selectedFile.url = resp.data[0].url.split(server)[1];
          if (resp.data[0].thumb_path) {
            Joomla.selectedFile.thumb = resp.data[0].thumb_path;
          } else {
            Joomla.selectedFile.thumb = false;
          }
        } else {
          if (resp.data[0].thumb_path) {
            Joomla.selectedFile.thumb = resp.data[0].thumb_path;
          }
        }
      } else {
        Joomla.selectedFile.url = false;
      }

      var isElement = function isElement(o) {
        return (typeof HTMLElement === 'undefined' ? 'undefined' : _typeof(HTMLElement)) === 'object' ? o instanceof HTMLElement : o && (typeof o === 'undefined' ? 'undefined' : _typeof(o)) === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string';
      };

      if (Joomla.selectedFile.url) {
        if (!isElement(editor) && (typeof editor === 'undefined' ? 'undefined' : _typeof(editor)) !== 'object') {
          Joomla.editors.instances[editor].replaceSelection('<img src="' + Joomla.selectedFile.url + '" alt=""/>');
        } else if (!isElement(editor) && (typeof editor === 'undefined' ? 'undefined' : _typeof(editor)) === 'object' && editor.id) {
          window.parent.Joomla.editors.instances[editor.id].replaceSelection('<img src="' + Joomla.selectedFile.url + '" alt=""/>');
        } else {
          editor.value = Joomla.selectedFile.url;
          fieldClass.updatePreview();
        }
      }
    }
  };

  /**
   * Create and dispatch onMediaFileSelected Event
   *
   * @param {object}  data  The data for the detail
   *
   * @returns {void}
   */
  Joomla.getImage = function (data, editor, fieldClass) {
    return new Promise(function (resolve, reject) {
      var apiBaseUrl = Joomla.getOptions('system.paths').rootFull + 'administrator/index.php?option=com_media&format=json';

      Joomla.request({
        url: apiBaseUrl + '&task=api.files&url=true&path=' + data.path + '&' + Joomla.getOptions('csrf.token') + '=1&format=json',
        method: 'GET',
        perform: true,
        headers: { 'Content-Type': 'application/json' },
        onSuccess: function onSuccess(response) {
          var resp = JSON.parse(response);
          resolve(Joomla.doIt(resp, editor, fieldClass));
        },
        onError: function onError() {
          reject();
        }
      });
    });
  };

  var JoomlaFieldMedia = function (_HTMLElement) {
    _inherits(JoomlaFieldMedia, _HTMLElement);

    function JoomlaFieldMedia() {
      _classCallCheck(this, JoomlaFieldMedia);

      return _possibleConstructorReturn(this, (JoomlaFieldMedia.__proto__ || Object.getPrototypeOf(JoomlaFieldMedia)).apply(this, arguments));
    }

    _createClass(JoomlaFieldMedia, [{
      key: 'connectedCallback',


      // attributeChangedCallback(attr, oldValue, newValue) {}

      value: function connectedCallback() {
        var button = this.querySelector(this.buttonSelect);
        var buttonClear = this.querySelector(this.buttonClear);
        this.show = this.show.bind(this);
        this.modalClose = this.modalClose.bind(this);
        this.clearValue = this.clearValue.bind(this);
        this.setValue = this.setValue.bind(this);
        this.updatePreview = this.updatePreview.bind(this);

        button.addEventListener('click', this.show);

        if (buttonClear) {
          buttonClear.addEventListener('click', this.clearValue);
        }

        this.updatePreview();
      }
    }, {
      key: 'disconnectedCallback',
      value: function disconnectedCallback() {
        var button = this.querySelector(this.buttonClear);
        button.removeEventListener('click', this);
      }
    }, {
      key: 'show',
      value: function show() {
        var _this2 = this;

        var self = this;

        this.querySelector('[role="dialog"]').open();

        this.querySelector(this.buttonSaveSelected).addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();

          if (_this2.selectedPath) {
            self.setValue(_this2.selectedPath);
          }

          self.modalClose();
          return false;
        });
      }
    }, {
      key: 'modalClose',
      value: function modalClose() {
        var input = this.querySelector(this.input);
        Joomla.getImage(Joomla.selectedFile, input, this);

        Joomla.Modal.getCurrent().close();
      }
    }, {
      key: 'setValue',
      value: function setValue(value) {
        this.querySelector(this.input).value = value;
        this.updatePreview();
      }
    }, {
      key: 'clearValue',
      value: function clearValue() {
        this.setValue('');
      }
    }, {
      key: 'updatePreview',
      value: function updatePreview() {
        if (['true', 'static'].indexOf(this.preview) === -1 || this.preview === 'false') {
          return;
        }

        // Reset preview
        if (this.preview) {
          var input = this.querySelector(this.input);
          var value = input.value;
          var div = this.querySelector('.field-media-preview');

          if (!value) {
            div.innerHTML = '<span class="field-media-preview-icon fa fa-picture-o"></span>';
          } else {
            div.innerHTML = '';
            var imgPreview = new Image();

            switch (this.type) {
              case 'image':
                imgPreview.src = imgPreview.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
                break;
              default:
                // imgPreview.src = dummy image path;
                break;
            }

            div.style.width = this.previewWidth;
            div.appendChild(imgPreview);
          }
        }
      }
    }, {
      key: 'type',
      get: function get() {
        return this.getAttribute('type');
      },
      set: function set(value) {
        this.setAttribute('type', value);
      }
    }, {
      key: 'basePath',
      get: function get() {
        return this.getAttribute('base-path');
      },
      set: function set(value) {
        this.setAttribute('base-path', value);
      }
    }, {
      key: 'rootFolder',
      get: function get() {
        return this.getAttribute('root-folder');
      },
      set: function set(value) {
        this.setAttribute('root-folder', value);
      }
    }, {
      key: 'url',
      get: function get() {
        return this.getAttribute('url');
      },
      set: function set(value) {
        this.setAttribute('url', value);
      }
    }, {
      key: 'modalContainer',
      get: function get() {
        return this.getAttribute('modal-container');
      },
      set: function set(value) {
        this.setAttribute('modal-container', value);
      }
    }, {
      key: 'input',
      get: function get() {
        return this.getAttribute('input');
      },
      set: function set(value) {
        this.setAttribute('input', value);
      }
    }, {
      key: 'buttonSelect',
      get: function get() {
        return this.getAttribute('button-select');
      },
      set: function set(value) {
        this.setAttribute('button-select', value);
      }
    }, {
      key: 'buttonClear',
      get: function get() {
        return this.getAttribute('button-clear');
      },
      set: function set(value) {
        this.setAttribute('button-clear', value);
      }
    }, {
      key: 'buttonSaveSelected',
      get: function get() {
        return this.getAttribute('button-save-selected');
      },
      set: function set(value) {
        this.setAttribute('button-save-selected', value);
      }
    }, {
      key: 'modalWidth',
      get: function get() {
        return this.getAttribute(parseInt('modal-width', 10));
      },
      set: function set(value) {
        this.setAttribute('modal-width', value);
      }
    }, {
      key: 'modalHeight',
      get: function get() {
        return this.getAttribute(parseInt('modal-height', 10));
      },
      set: function set(value) {
        this.setAttribute('modal-height', value);
      }
    }, {
      key: 'previewWidth',
      get: function get() {
        return this.getAttribute(parseInt('preview-width', 10));
      },
      set: function set(value) {
        this.setAttribute('preview-width', value);
      }
    }, {
      key: 'previewHeight',
      get: function get() {
        return this.getAttribute(parseInt('preview-height', 10));
      },
      set: function set(value) {
        this.setAttribute('preview-height', value);
      }
    }, {
      key: 'preview',
      get: function get() {
        return this.getAttribute('preview');
      },
      set: function set(value) {
        this.setAttribute('preview', value);
      }
    }, {
      key: 'previewContainer',
      get: function get() {
        return this.getAttribute('preview-container');
      }
    }], [{
      key: 'observedAttributes',
      get: function get() {
        return ['type', 'base-path', 'root-folder', 'url', 'modal-container', 'modal-width', 'modal-height', 'input', 'button-select', 'button-clear', 'button-save-selected', 'preview', 'preview-width', 'preview-height'];
      }
    }]);

    return JoomlaFieldMedia;
  }(HTMLElement);

  customElements.define('joomla-field-media', JoomlaFieldMedia);
})(customElements, Joomla);

},{}]},{},[1]);
