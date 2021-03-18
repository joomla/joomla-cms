"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

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

(function () {
  var JoomlaFieldUser = /*#__PURE__*/function (_HTMLElement) {
    _inherits(JoomlaFieldUser, _HTMLElement);

    var _super = _createSuper(JoomlaFieldUser);

    function JoomlaFieldUser() {
      var _this;

      _classCallCheck(this, JoomlaFieldUser);

      _this = _super.call(this);
      _this.onUserSelect = '';
      _this.onchangeStr = '';
      _this.buttonClick = _this.buttonClick.bind(_assertThisInitialized(_this));
      _this.iframeLoad = _this.iframeLoad.bind(_assertThisInitialized(_this));
      return _this;
    }

    _createClass(JoomlaFieldUser, [{
      key: "connectedCallback",
      value: function connectedCallback() {
        // Set up elements
        this.modal = this.querySelector(this.modalClass);
        this.modalBody = this.querySelector('.modal-body');
        this.input = this.querySelector(this.inputId);
        this.inputName = this.querySelector(this.inputNameClass);
        this.buttonSelect = this.querySelector(this.buttonSelectClass); // Bind events

        this.modalClose = this.modalClose.bind(this);
        this.setValue = this.setValue.bind(this);

        if (this.buttonSelect) {
          this.buttonSelect.addEventListener('click', this.modalOpen.bind(this));
          this.modal.addEventListener('hide', this.removeIframe.bind(this)); // Check for onchange callback,

          this.onchangeStr = this.input.getAttribute('data-onchange');

          if (this.onchangeStr) {
            /* eslint-disable */
            this.onUserSelect = new Function(this.onchangeStr);
            this.input.addEventListener('change', this.onUserSelect);
            /* eslint-enable */
          }
        }
      }
    }, {
      key: "disconnectedCallback",
      value: function disconnectedCallback() {
        if (this.onchangeStr && this.input) {
          this.input.removeEventListener('change', this.onUserSelect);
        }

        if (this.buttonSelect) {
          this.buttonSelect.removeEventListener('click', this);
        }

        if (this.modal) {
          this.modal.removeEventListener('hide', this);
        }
      }
    }, {
      key: "buttonClick",
      value: function buttonClick(_ref) {
        var target = _ref.target;
        this.setValue(target.getAttribute('data-user-value'), target.getAttribute('data-user-name'));
        this.modalClose();
      }
    }, {
      key: "iframeLoad",
      value: function iframeLoad() {
        var _this2 = this;

        var iframeDoc = this.iframeEl.contentWindow.document;
        var buttons = [].slice.call(iframeDoc.querySelectorAll('.button-select'));
        buttons.forEach(function (button) {
          button.addEventListener('click', _this2.buttonClick);
        });
      } // Opens the modal

    }, {
      key: "modalOpen",
      value: function modalOpen() {
        // Reconstruct the iframe
        this.removeIframe();
        var iframe = document.createElement('iframe');
        iframe.setAttribute('name', 'field-user-modal');
        iframe.src = this.url.replace('{field-user-id}', this.input.getAttribute('id'));
        iframe.setAttribute('width', this.modalWidth);
        iframe.setAttribute('height', this.modalHeight);
        this.modalBody.appendChild(iframe);
        this.modal.open();
        this.iframeEl = this.modalBody.querySelector('iframe'); // handle the selection on the iframe

        this.iframeEl.addEventListener('load', this.iframeLoad);
      } // Closes the modal

    }, {
      key: "modalClose",
      value: function modalClose() {
        Joomla.Modal.getCurrent().close();
        this.modalBody.innerHTML = '';
      } // Remove the iframe

    }, {
      key: "removeIframe",
      value: function removeIframe() {
        this.modalBody.innerHTML = '';
      } // Sets the value

    }, {
      key: "setValue",
      value: function setValue(value, name) {
        this.input.setAttribute('value', value);
        this.inputName.setAttribute('value', name || value);
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
      key: "modalClass",
      get: function get() {
        return this.getAttribute('modal');
      },
      set: function set(value) {
        this.setAttribute('modal', value);
      }
    }, {
      key: "modalWidth",
      get: function get() {
        return this.getAttribute('modal-width');
      },
      set: function set(value) {
        this.setAttribute('modal-width', value);
      }
    }, {
      key: "modalHeight",
      get: function get() {
        return this.getAttribute('modal-height');
      },
      set: function set(value) {
        this.setAttribute('modal-height', value);
      }
    }, {
      key: "inputId",
      get: function get() {
        return this.getAttribute('input');
      },
      set: function set(value) {
        this.setAttribute('input', value);
      }
    }, {
      key: "inputNameClass",
      get: function get() {
        return this.getAttribute('input-name');
      },
      set: function set(value) {
        this.setAttribute('input-name', value);
      }
    }, {
      key: "buttonSelectClass",
      get: function get() {
        return this.getAttribute('button-select');
      },
      set: function set(value) {
        this.setAttribute('button-select', value);
      }
    }], [{
      key: "observedAttributes",
      get: function get() {
        return ['url', 'modal-class', 'modal-width', 'modal-height', 'input', 'input-name', 'button-select'];
      }
    }]);

    return JoomlaFieldUser;
  }( /*#__PURE__*/_wrapNativeSuper(HTMLElement));

  customElements.define('joomla-field-user', JoomlaFieldUser);
})();