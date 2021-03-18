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

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
window.customElements.define('joomla-editor-none', /*#__PURE__*/function (_HTMLElement) {
  _inherits(_class, _HTMLElement);

  var _super = _createSuper(_class);

  function _class() {
    var _this;

    _classCallCheck(this, _class);

    _this = _super.call(this); // Properties

    _this.editor = ''; // Bindings

    _this.unregisterEditor = _this.unregisterEditor.bind(_assertThisInitialized(_this));
    _this.registerEditor = _this.registerEditor.bind(_assertThisInitialized(_this));
    _this.childrenChange = _this.childrenChange.bind(_assertThisInitialized(_this));
    _this.getSelection = _this.getSelection.bind(_assertThisInitialized(_this)); // Watch for children changes.
    // eslint-disable-next-line no-return-assign

    new MutationObserver(function () {
      return _this.childrenChange();
    }).observe(_assertThisInitialized(_this), {
      childList: true
    });
    return _this;
  }
  /**
   * Lifecycle
   */


  _createClass(_class, [{
    key: "connectedCallback",
    value: function connectedCallback() {
      // Note the mutation observer won't fire for initial contents,
      // so childrenChange is also called here.
      this.childrenChange();
    }
    /**
     * Lifecycle
     */

  }, {
    key: "disconnectedCallback",
    value: function disconnectedCallback() {
      this.unregisterEditor();
    }
    /**
     * Get the selected text
     */

  }, {
    key: "getSelection",
    value: function getSelection() {
      if (document.selection) {
        // IE support
        this.editor.focus();
        return document.selection.createRange();
      }

      if (this.editor.selectionStart || this.editor.selectionStart === 0) {
        // MOZILLA/NETSCAPE support
        return this.editor.value.substring(this.editor.selectionStart, this.editor.selectionEnd);
      }

      return this.editor.value;
    }
    /**
     * Register the editor
     */

  }, {
    key: "registerEditor",
    value: function registerEditor() {
      var _this2 = this;

      if (!window.Joomla || !window.Joomla.editors || _typeof(window.Joomla.editors) !== 'object') {
        throw new Error('The Joomla API is not correctly registered.');
      }

      window.Joomla.editors.instances[this.editor.id] = {
        id: function id() {
          return _this2.editor.id;
        },
        element: function element() {
          return _this2.editor;
        },
        // eslint-disable-next-line no-return-assign
        getValue: function getValue() {
          return _this2.editor.value;
        },
        // eslint-disable-next-line no-return-assign
        setValue: function setValue(text) {
          return _this2.editor.value = text;
        },
        // eslint-disable-next-line no-return-assign
        getSelection: function getSelection() {
          return _this2.getSelection();
        },
        // eslint-disable-next-line no-return-assign
        disable: function disable(disabled) {
          _this2.editor.disabled = disabled;
          _this2.editor.readOnly = disabled;
        },
        // eslint-disable-next-line no-return-assign
        replaceSelection: function replaceSelection(text) {
          if (_this2.editor.selectionStart || _this2.editor.selectionStart === 0) {
            _this2.editor.value = _this2.editor.value.substring(0, _this2.editor.selectionStart) + text + _this2.editor.value.substring(_this2.editor.selectionEnd, _this2.editor.value.length);
          } else {
            _this2.editor.value += text;
          }
        },
        onSave: function onSave() {}
      };
    }
    /**
     * Remove the editor from the Joomla API
     */

  }, {
    key: "unregisterEditor",
    value: function unregisterEditor() {
      if (this.editor) {
        delete window.Joomla.editors.instances[this.editor.id];
      }
    }
    /**
     * Called when element's child list changes
     */

  }, {
    key: "childrenChange",
    value: function childrenChange() {
      // Ensure the first child is an input with a textarea type.
      if (this.firstElementChild && this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() === 'textarea' && this.firstElementChild.getAttribute('id')) {
        this.editor = this.firstElementChild;
        this.unregisterEditor();
        this.registerEditor();
      }
    }
  }]);

  return _class;
}( /*#__PURE__*/_wrapNativeSuper(HTMLElement)));