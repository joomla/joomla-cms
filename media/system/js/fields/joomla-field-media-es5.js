"use strict";

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _wrapNativeSuper(Class) { var _cache = typeof Map === "function" ? new Map() : undefined; _wrapNativeSuper = function _wrapNativeSuper(Class) { if (Class === null || !_isNativeFunction(Class)) return Class; if (typeof Class !== "function") { throw new TypeError("Super expression must either be null or a function"); } if (typeof _cache !== "undefined") { if (_cache.has(Class)) return _cache.get(Class); _cache.set(Class, Wrapper); } function Wrapper() { return _construct(Class, arguments, _getPrototypeOf(this).constructor); } Wrapper.prototype = Object.create(Class.prototype, { constructor: { value: Wrapper, enumerable: false, writable: true, configurable: true } }); return _setPrototypeOf(Wrapper, Class); }; return _wrapNativeSuper(Class); }

function _construct(Parent, args, Class) { if (_isNativeReflectConstruct()) { _construct = Reflect.construct; } else { _construct = function _construct(Parent, args, Class) { var a = [null]; a.push.apply(a, args); var Constructor = Function.bind.apply(Parent, a); var instance = new Constructor(); if (Class) _setPrototypeOf(instance, Class.prototype); return instance; }; } return _construct.apply(null, arguments); }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _isNativeFunction(fn) { return Function.toString.call(fn).indexOf("[native code]") !== -1; }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function (customElements, Joomla) {
  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }

  var selectedFile = {};
  window.document.addEventListener('onMediaFileSelected', function (e) {
    selectedFile = e.detail;
  });

  var execTransform = function execTransform(resp, editor, fieldClass) {
    if (resp.success === true) {
      if (resp.data[0].url) {
        if (/local-/.test(resp.data[0].adapter)) {
          var _Joomla$getOptions = Joomla.getOptions('system.paths'),
              rootFull = _Joomla$getOptions.rootFull; // eslint-disable-next-line prefer-destructuring


          selectedFile.url = resp.data[0].url.split(rootFull)[1];

          if (resp.data[0].thumb_path) {
            selectedFile.thumb = resp.data[0].thumb_path;
          } else {
            selectedFile.thumb = false;
          }
        } else if (resp.data[0].thumb_path) {
          selectedFile.thumb = resp.data[0].thumb_path;
        }
      } else {
        selectedFile.url = false;
      }

      var isElement = function isElement(o) {
        return (typeof HTMLElement === "undefined" ? "undefined" : _typeof(HTMLElement)) === 'object' ? o instanceof HTMLElement : o && _typeof(o) === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string';
      };

      if (selectedFile.url) {
        if (!isElement(editor) && _typeof(editor) !== 'object') {
          Joomla.editors.instances[editor].replaceSelection("<img loading=\"lazy\" src=\"".concat(selectedFile.url, "\" alt=\"\"/>"));
        } else if (!isElement(editor) && _typeof(editor) === 'object' && editor.id) {
          window.parent.Joomla.editors.instances[editor.id].replaceSelection("<img loading=\"lazy\" src=\"".concat(selectedFile.url, "\" alt=\"\"/>"));
        } else {
          editor.value = selectedFile.url;
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


  var fetchImageDetails = function fetchImageDetails(data, editor, fieldClass) {
    return new Promise(function (resolve, reject) {
      if (!data || _typeof(data) === 'object' && (!data.path || data.path === '')) {
        selectedFile = {};
        reject(new Error('Nothing selected'));
        return;
      }

      var apiBaseUrl = "".concat(Joomla.getOptions('system.paths').rootFull, "administrator/index.php?option=com_media&format=json");
      Joomla.request({
        url: "".concat(apiBaseUrl, "&task=api.files&url=true&path=").concat(data.path, "&").concat(Joomla.getOptions('csrf.token'), "=1&format=json"),
        method: 'GET',
        perform: true,
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: function onSuccess(response) {
          var resp = JSON.parse(response);
          resolve(execTransform(resp, editor, fieldClass));
        },
        onError: function onError(err) {
          reject(err);
        }
      });
    });
  };

  var JoomlaFieldMedia = /*#__PURE__*/function (_HTMLElement) {
    _inherits(JoomlaFieldMedia, _HTMLElement);

    var _super = _createSuper(JoomlaFieldMedia);

    function JoomlaFieldMedia() {
      var _this;

      _classCallCheck(this, JoomlaFieldMedia);

      _this = _super.call(this);
      _this.onSelected = _this.onSelected.bind(_assertThisInitialized(_this));
      _this.show = _this.show.bind(_assertThisInitialized(_this));
      _this.clearValue = _this.clearValue.bind(_assertThisInitialized(_this));
      return _this;
    }

    _createClass(JoomlaFieldMedia, [{
      key: "connectedCallback",
      // attributeChangedCallback(attr, oldValue, newValue) {}
      value: function connectedCallback() {
        this.button = this.querySelector(this.buttonSelect);
        this.buttonClearEl = this.querySelector(this.buttonClear);
        this.show = this.show.bind(this);
        this.modalClose = this.modalClose.bind(this);
        this.clearValue = this.clearValue.bind(this);
        this.setValue = this.setValue.bind(this);
        this.updatePreview = this.updatePreview.bind(this);
        this.button.addEventListener('click', this.show);

        if (this.buttonClearEl) {
          this.buttonClearEl.addEventListener('click', this.clearValue);
        }

        this.updatePreview();
      }
    }, {
      key: "disconnectedCallback",
      value: function disconnectedCallback() {
        if (this.button) {
          this.button.removeEventListener('click', this.show);
        }

        if (this.buttonClearEl) {
          this.buttonClearEl.removeEventListener('click', this.clearValue);
        }
      }
    }, {
      key: "onSelected",
      value: function onSelected(event) {
        // event.target.removeEventListener('click', this.onSelected);
        event.preventDefault();
        event.stopPropagation();
        this.modalClose();
        return false;
      }
    }, {
      key: "show",
      value: function show() {
        this.querySelector('[role="dialog"]').open();
        this.querySelector(this.buttonSaveSelected).addEventListener('click', this.onSelected);
      }
    }, {
      key: "modalClose",
      value: function modalClose() {
        var input = this.querySelector(this.input);
        fetchImageDetails(selectedFile, input, this).then(function () {
          Joomla.Modal.getCurrent().close();
        }).catch(function () {
          Joomla.Modal.getCurrent().close();
          Joomla.renderMessages({
            error: [Joomla.Text._('JLIB_APPLICATION_ERROR_SERVER')]
          });
        });
      }
    }, {
      key: "setValue",
      value: function setValue(value) {
        this.querySelector(this.input).value = value;
        this.updatePreview();
      }
    }, {
      key: "clearValue",
      value: function clearValue() {
        this.setValue('');
      }
    }, {
      key: "updatePreview",
      value: function updatePreview() {
        if (['true', 'static'].indexOf(this.preview) === -1 || this.preview === 'false') {
          return;
        } // Reset preview


        if (this.preview) {
          var input = this.querySelector(this.input);
          var value = input.value;
          var div = this.querySelector('.field-media-preview');

          if (!value) {
            div.innerHTML = '<span class="field-media-preview-icon"></span>';
          } else {
            div.innerHTML = '';
            var imgPreview = new Image();

            switch (this.type) {
              case 'image':
                imgPreview.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
                imgPreview.setAttribute('alt', '');
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
      key: "type",
      get: function get() {
        return this.getAttribute('type');
      },
      set: function set(value) {
        this.setAttribute('type', value);
      }
    }, {
      key: "basePath",
      get: function get() {
        return this.getAttribute('base-path');
      },
      set: function set(value) {
        this.setAttribute('base-path', value);
      }
    }, {
      key: "rootFolder",
      get: function get() {
        return this.getAttribute('root-folder');
      },
      set: function set(value) {
        this.setAttribute('root-folder', value);
      }
    }, {
      key: "url",
      get: function get() {
        return this.getAttribute('url');
      },
      set: function set(value) {
        this.setAttribute('url', value);
      }
    }, {
      key: "modalContainer",
      get: function get() {
        return this.getAttribute('modal-container');
      },
      set: function set(value) {
        this.setAttribute('modal-container', value);
      }
    }, {
      key: "input",
      get: function get() {
        return this.getAttribute('input');
      },
      set: function set(value) {
        this.setAttribute('input', value);
      }
    }, {
      key: "buttonSelect",
      get: function get() {
        return this.getAttribute('button-select');
      },
      set: function set(value) {
        this.setAttribute('button-select', value);
      }
    }, {
      key: "buttonClear",
      get: function get() {
        return this.getAttribute('button-clear');
      },
      set: function set(value) {
        this.setAttribute('button-clear', value);
      }
    }, {
      key: "buttonSaveSelected",
      get: function get() {
        return this.getAttribute('button-save-selected');
      },
      set: function set(value) {
        this.setAttribute('button-save-selected', value);
      }
    }, {
      key: "modalWidth",
      get: function get() {
        return this.getAttribute(parseInt('modal-width', 10));
      },
      set: function set(value) {
        this.setAttribute('modal-width', value);
      }
    }, {
      key: "modalHeight",
      get: function get() {
        return this.getAttribute(parseInt('modal-height', 10));
      },
      set: function set(value) {
        this.setAttribute('modal-height', value);
      }
    }, {
      key: "previewWidth",
      get: function get() {
        return this.getAttribute(parseInt('preview-width', 10));
      },
      set: function set(value) {
        this.setAttribute('preview-width', value);
      }
    }, {
      key: "previewHeight",
      get: function get() {
        return this.getAttribute(parseInt('preview-height', 10));
      },
      set: function set(value) {
        this.setAttribute('preview-height', value);
      }
    }, {
      key: "preview",
      get: function get() {
        return this.getAttribute('preview');
      },
      set: function set(value) {
        this.setAttribute('preview', value);
      }
    }, {
      key: "previewContainer",
      get: function get() {
        return this.getAttribute('preview-container');
      }
    }], [{
      key: "observedAttributes",
      get: function get() {
        return ['type', 'base-path', 'root-folder', 'url', 'modal-container', 'modal-width', 'modal-height', 'input', 'button-select', 'button-clear', 'button-save-selected', 'preview', 'preview-width', 'preview-height'];
      }
    }]);

    return JoomlaFieldMedia;
  }( /*#__PURE__*/_wrapNativeSuper(HTMLElement));

  customElements.define('joomla-field-media', JoomlaFieldMedia);
})(customElements, Joomla);