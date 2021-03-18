"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

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

customElements.define('joomla-editor-codemirror', /*#__PURE__*/function (_HTMLElement) {
  _inherits(_class, _HTMLElement);

  var _super = _createSuper(_class);

  function _class() {
    var _this;

    _classCallCheck(this, _class);

    _this = _super.call(this);
    _this.instance = '';
    _this.cm = '';
    _this.host = window.location.origin;
    _this.element = _this.querySelector('textarea');
    _this.refresh = _this.refresh.bind(_assertThisInitialized(_this));
    _this.toggleFullScreen = _this.toggleFullScreen.bind(_assertThisInitialized(_this));
    _this.closeFullScreen = _this.closeFullScreen.bind(_assertThisInitialized(_this)); // Append the editor script

    if (!document.head.querySelector('#cm-editor')) {
      var cmPath = _this.getAttribute('editor');

      var script1 = document.createElement('script');
      script1.src = "".concat(_this.host, "/").concat(cmPath);
      script1.id = 'cm-editor';
      script1.setAttribute('async', false);
      document.head.insertBefore(script1, _this.file);
    }

    return _this;
  }

  _createClass(_class, [{
    key: "attributeChangedCallback",
    value: function attributeChangedCallback(attr, oldValue, newValue) {
      switch (attr) {
        case 'options':
          if (oldValue && newValue !== oldValue) {
            this.refresh(this.element);
          }

          break;

        default: // Do nothing

      }
    }
  }, {
    key: "connectedCallback",
    value: function connectedCallback() {
      var _this2 = this;

      var that = this;
      this.checkElement('CodeMirror').then(function () {
        // Append the addons script
        if (!document.head.querySelector('#cm-addons')) {
          var addonsPath = _this2.getAttribute('addons');

          var script2 = document.createElement('script');
          script2.src = "".concat(_this2.host, "/").concat(addonsPath);
          script2.id = 'cm-addons';
          script2.setAttribute('async', false);
          document.head.insertBefore(script2, _this2.file);
        }

        _this2.checkElement('CodeMirror', 'findModeByName').then(function () {
          // For mode autoloading.
          window.CodeMirror.modeURL = _this2.getAttribute('mod-path'); // Fire this function any time an editor is created.

          window.CodeMirror.defineInitHook(function (editor) {
            var _map;

            // Try to set up the mode
            var mode = window.CodeMirror.findModeByName(editor.options.mode || '') || window.CodeMirror.findModeByName(editor.options.mode || '') || window.CodeMirror.findModeByExtension(editor.options.mode || '');
            window.CodeMirror.autoLoadMode(editor, mode ? mode.mode : editor.options.mode);

            if (mode && mode.mime) {
              editor.setOption('mode', mode.mime);
            }

            var map = (_map = {
              'Ctrl-Q': that.toggleFullScreen
            }, _defineProperty(_map, that.getAttribute('fs-combo'), that.toggleFullScreen), _defineProperty(_map, "Esc", that.closeFullScreen), _map);
            editor.addKeyMap(map); // Handle gutter clicks (place or remove a marker).

            editor.on('gutterClick', function (ed, n, gutter) {
              if (gutter !== 'CodeMirror-markergutter') {
                return;
              }

              var info = ed.lineInfo(n);
              var hasMarker = !!info.gutterMarkers && !!info.gutterMarkers['CodeMirror-markergutter'];
              ed.setGutterMarker(n, 'CodeMirror-markergutter', hasMarker ? null : _this2.constructor.makeMarker());
            });
            /* Some browsers do something weird with the fieldset which doesn't
              work well with CodeMirror. Fix it. */

            if (_this2.parentNode.tagName.toLowerCase() === 'fieldset') {
              _this2.parentNode.style.minWidth = 0;
            }
          }); // Register Editor

          _this2.instance = window.CodeMirror.fromTextArea(_this2.element, _this2.options);

          _this2.instance.disable = function (disabled) {
            return _this2.instance.setOption('readOnly', disabled ? 'nocursor' : false);
          };

          Joomla.editors.instances[_this2.element.id] = _this2.instance;
        });
      });
    }
  }, {
    key: "disconnectedCallback",
    value: function disconnectedCallback() {
      // Remove from the Joomla API
      delete Joomla.editors.instances[this.element.id];
    }
  }, {
    key: "refresh",
    value: function refresh(element) {
      this.instance = window.CodeMirror.fromTextArea(element, this.options);
    }
    /* eslint-disable */

  }, {
    key: "rafAsync",
    value: function rafAsync() {
      return new Promise(function (resolve) {
        return requestAnimationFrame(resolve);
      });
    }
  }, {
    key: "checkElement",
    value: function () {
      var _checkElement = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(string1, string2) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                if (!string2) {
                  _context.next = 8;
                  break;
                }

              case 1:
                if (!(typeof window[string1][string2] !== 'function')) {
                  _context.next = 6;
                  break;
                }

                _context.next = 4;
                return this.rafAsync();

              case 4:
                _context.next = 1;
                break;

              case 6:
                _context.next = 13;
                break;

              case 8:
                if (!(typeof window[string1] !== 'function')) {
                  _context.next = 13;
                  break;
                }

                _context.next = 11;
                return this.rafAsync();

              case 11:
                _context.next = 8;
                break;

              case 13:
                return _context.abrupt("return", true);

              case 14:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function checkElement(_x, _x2) {
        return _checkElement.apply(this, arguments);
      }

      return checkElement;
    }()
    /* eslint-enable */

  }, {
    key: "toggleFullScreen",
    value: function toggleFullScreen() {
      this.instance.setOption('fullScreen', !this.instance.getOption('fullScreen'));
      var header = document.getElementById('header');

      if (header) {
        header.classList.toggle('hidden');
      }
    }
  }, {
    key: "closeFullScreen",
    value: function closeFullScreen() {
      this.instance.getOption('fullScreen');
      this.instance.setOption('fullScreen', false);
      var header = document.getElementById('header');

      if (header) {
        header.classList.remove('hidden');
      }
    }
  }, {
    key: "options",
    get: function get() {
      return JSON.parse(this.getAttribute('options'));
    },
    set: function set(value) {
      this.setAttribute('options', value);
    }
  }], [{
    key: "makeMarker",
    value: function makeMarker() {
      var marker = document.createElement('div');
      marker.className = 'CodeMirror-markergutter-mark';
      return marker;
    }
  }, {
    key: "observedAttributes",
    get: function get() {
      return ['options'];
    }
  }]);

  return _class;
}( /*#__PURE__*/_wrapNativeSuper(HTMLElement)));