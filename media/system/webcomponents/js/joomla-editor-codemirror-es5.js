(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

customElements.define('joomla-editor-codemirror', function (_HTMLElement) {
  _inherits(_class, _HTMLElement);

  function _class() {
    _classCallCheck(this, _class);

    var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

    _this.instance = '';
    _this.cm = '';
    _this.host = window.location.origin;
    _this.element = _this.querySelector('textarea');
    _this.refresh = _this.refresh.bind(_this);
    _this.toggleFullScreen = _this.toggleFullScreen.bind(_this);
    _this.closeFullScreen = _this.closeFullScreen.bind(_this);

    // Append the editor script
    if (!document.head.querySelector('#cm-editor')) {
      var cmPath = _this.getAttribute('editor');
      var script1 = document.createElement('script');

      script1.src = _this.host + '/' + cmPath;
      script1.id = 'cm-editor';
      script1.setAttribute('async', false);
      document.head.insertBefore(script1, _this.file);
    }
    return _this;
  }

  _createClass(_class, [{
    key: 'attributeChangedCallback',
    value: function attributeChangedCallback(attr, oldValue, newValue) {
      switch (attr) {
        case 'options':
          if (oldValue && newValue !== oldValue) {
            this.refresh(this.element);
          }
          break;
        default:
        // Do nothing
      }
    }
  }, {
    key: 'connectedCallback',
    value: function connectedCallback() {
      var _this2 = this;

      var that = this;
      this.checkElement('CodeMirror').then(function () {
        // Append the addons script
        if (!document.head.querySelector('#cm-addons')) {
          var addonsPath = _this2.getAttribute('addons');
          var script2 = document.createElement('script');

          script2.src = _this2.host + '/' + addonsPath;
          script2.id = 'cm-addons';
          script2.setAttribute('async', false);
          document.head.insertBefore(script2, _this2.file);
        }

        _this2.checkElement('CodeMirror', 'findModeByName').then(function () {
          // For mode autoloading.
          window.CodeMirror.modeURL = _this2.getAttribute('mod-path');

          // Fire this function any time an editor is created.
          window.CodeMirror.defineInitHook(function (editor) {
            var _map;

            // Try to set up the mode
            var mode = window.CodeMirror.findModeByName(editor.options.mode || '') || window.CodeMirror.findModeByName(editor.options.mode || '') || window.CodeMirror.findModeByExtension(editor.options.mode || '');

            window.CodeMirror.autoLoadMode(editor, mode ? mode.mode : editor.options.mode);

            if (mode) {
              editor.setOption('mode', mode.mode);
            }

            var map = (_map = {
              'Ctrl-Q': that.toggleFullScreen
            }, _defineProperty(_map, that.getAttribute('fs-combo'), that.toggleFullScreen), _defineProperty(_map, 'Esc', that.closeFullScreen), _map);

            editor.addKeyMap(map);

            // Handle gutter clicks (place or remove a marker).
            editor.on('gutterClick', function (ed, n, gutter) {
              if (gutter !== 'CodeMirror-markergutter') {
                return;
              }

              var info = ed.lineInfo(n);
              var hasMarker = !!info.gutterMarkers && !!info.gutterMarkers['CodeMirror-markergutter'];
              ed.setGutterMarker(n, 'CodeMirror-markergutter', hasMarker ? null : _this2.makeMarker());
            });

            /* Some browsers do something weird with the fieldset which doesn't
              work well with CodeMirror. Fix it. */
            if (_this2.parentNode.tagName.toLowerCase() === 'fieldset') {
              _this2.parentNode.style.minWidth = 0;
            }
          });

          // Register Editor
          _this2.instance = window.CodeMirror.fromTextArea(_this2.element, _this2.options);
          Joomla.editors.instances[_this2.element.id] = _this2.instance;
        });
      });
    }
  }, {
    key: 'disconnectedCallback',
    value: function disconnectedCallback() {
      // Remove from the Joomla API
      delete Joomla.editors.instances[this.element.id];
    }
  }, {
    key: 'refresh',
    value: function refresh(element) {
      this.instance = window.CodeMirror.fromTextArea(element, this.options);
    }

    /* eslint-disable */

  }, {
    key: 'rafAsync',
    value: function rafAsync() {
      return new Promise(function (resolve) {
        return requestAnimationFrame(resolve);
      });
    }
  }, {
    key: 'checkElement',
    value: function () {
      var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(string1, string2) {
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
                return _context.abrupt('return', true);

              case 14:
              case 'end':
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function checkElement(_x, _x2) {
        return _ref.apply(this, arguments);
      }

      return checkElement;
    }()

    /* eslint-enable */

  }, {
    key: 'toggleFullScreen',
    value: function toggleFullScreen() {
      this.instance.setOption('fullScreen', !this.instance.getOption('fullScreen'));
    }
  }, {
    key: 'closeFullScreen',
    value: function closeFullScreen() {
      this.instance.getOption('fullScreen');
      this.instance.setOption('fullScreen', false);
    }
  }, {
    key: 'options',
    get: function get() {
      return JSON.parse(this.getAttribute('options'));
    },
    set: function set(value) {
      this.setAttribute('options', value);
    }
  }], [{
    key: 'makeMarker',
    value: function makeMarker() {
      var marker = document.createElement('div');
      marker.className = 'CodeMirror-markergutter-mark';
      return marker;
    }
  }, {
    key: 'observedAttributes',
    get: function get() {
      return ['options'];
    }
  }]);

  return _class;
}(HTMLElement));

},{}]},{},[1]);
