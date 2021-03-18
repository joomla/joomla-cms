"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

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
window.customElements.define('joomla-field-permissions', /*#__PURE__*/function (_HTMLElement) {
  _inherits(_class, _HTMLElement);

  var _super = _createSuper(_class);

  function _class() {
    var _this;

    _classCallCheck(this, _class);

    _this = _super.call(this);

    if (!Joomla) {
      throw new Error('Joomla API is not properly initiated');
    }

    if (!_this.getAttribute('data-uri')) {
      throw new Error('No valid url for validation');
    }

    _this.query = window.location.search.substring(1);
    _this.buttons = '';
    _this.buttonDataSelector = 'data-onchange-task';
    _this.onDropdownChange = _this.onDropdownChange.bind(_assertThisInitialized(_this));
    _this.getUrlParam = _this.getUrlParam.bind(_assertThisInitialized(_this));
    _this.component = _this.getUrlParam('component');
    _this.extension = _this.getUrlParam('extension');
    _this.option = _this.getUrlParam('option');
    _this.view = _this.getUrlParam('view');
    _this.asset = 'not';
    _this.context = '';
    return _this;
  }
  /**
   * Lifecycle
   */


  _createClass(_class, [{
    key: "connectedCallback",
    value: function connectedCallback() {
      var _this2 = this;

      this.buttons = [].slice.call(document.querySelectorAll("[".concat(this.buttonDataSelector, "]")));

      if (this.buttons) {
        this.buttons.forEach(function (button) {
          button.addEventListener('change', _this2.onDropdownChange);
        });
      }
    }
    /**
     * Lifecycle
     */

  }, {
    key: "disconnectedCallback",
    value: function disconnectedCallback() {
      var _this3 = this;

      if (this.buttons) {
        this.buttons.forEach(function (button) {
          button.removeEventListener('change', _this3.onDropdownChange);
        });
      }
    }
    /**
     * Lifecycle
     */

  }, {
    key: "onDropdownChange",
    value: function onDropdownChange(event) {
      event.preventDefault();
      var task = event.target.getAttribute(this.buttonDataSelector);

      if (task === 'permissions.apply') {
        this.sendPermissions(event);
      }
    }
  }, {
    key: "sendPermissions",
    value: function sendPermissions(event) {
      var target = event.target; // Set the icon while storing the values

      var icon = document.getElementById("icon_".concat(target.id));
      icon.removeAttribute('class');
      icon.setAttribute('class', 'joomla-icon joomla-field-permissions__spinner'); // Get values add prepare GET-Parameter

      var value = target.value;

      if (document.getElementById('jform_context')) {
        this.context = document.getElementById('jform_context').value;

        var _this$context$split = this.context.split('.');

        var _this$context$split2 = _slicedToArray(_this$context$split, 1);

        this.context = _this$context$split2[0];
      }

      if (this.option === 'com_config' && !this.component && !this.extension) {
        this.asset = 'root.1';
      } else if (!this.extension && this.view === 'component') {
        this.asset = this.component;
      } else if (this.context) {
        if (this.view === 'group') {
          this.asset = "".concat(this.context, ".fieldgroup.").concat(this.getUrlParam('id'));
        } else {
          this.asset = "".concat(this.context, ".field.{this.getUrlParam('id')}");
        }

        this.title = document.getElementById('jform_title').value;
      } else if (this.extension && this.view) {
        this.asset = "".concat(this.extension, ".").concat(this.view, ".").concat(this.getUrlParam('id'));
        this.title = document.getElementById('jform_title').value;
      } else if (!this.extension && this.view) {
        this.asset = "".concat(this.option, ".").concat(this.view, ".").concat(this.getUrlParam('id'));
        this.title = document.getElementById('jform_title').value;
      }

      var id = target.id.replace('jform_rules_', '');
      var lastUnderscoreIndex = id.lastIndexOf('_');
      var permissionData = {
        comp: this.asset,
        action: id.substring(0, lastUnderscoreIndex),
        rule: id.substring(lastUnderscoreIndex + 1),
        value: value,
        title: this.title
      }; // Remove JS messages, if they exist.

      Joomla.removeMessages(); // Ajax request

      Joomla.request({
        url: this.getAttribute('data-uri'),
        method: 'POST',
        data: JSON.stringify(permissionData),
        perform: true,
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: function onSuccess(data) {
          var response;

          try {
            response = JSON.parse(data);
          } catch (e) {
            console.log(e);
          }

          icon.removeAttribute('class'); // Check if everything is OK

          if (response.data && response.data.result) {
            icon.setAttribute('class', 'joomla-icon joomla-field-permissions__allowed');
            var badgeSpan = target.parentNode.parentNode.nextElementSibling.querySelector('span');
            badgeSpan.removeAttribute('class');
            badgeSpan.setAttribute('class', response.data.class);
            badgeSpan.innerHTML = response.data.text;
          } // Render messages, if any. There are only message in case of errors.


          if (_typeof(response.messages) === 'object' && response.messages !== null) {
            Joomla.renderMessages(response.messages);

            if (response.data && response.data.result) {
              icon.setAttribute('class', 'joomla-icon joomla-field-permissions__allowed');
            } else {
              icon.setAttribute('class', 'joomla-icon joomla-field-permissions__denied');
            }
          }
        },
        onError: function onError(xhr) {
          // Remove the spinning icon.
          icon.removeAttribute('style');
          Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr, xhr.statusText));
          icon.setAttribute('class', 'joomla-icon joomla-field-permissions__denied');
        }
      });
    }
  }, {
    key: "getUrlParam",
    value: function getUrlParam(variable) {
      var vars = this.query.split('&');
      var i = 0;

      for (i; i < vars.length; i += 1) {
        var pair = vars[i].split('=');

        if (pair[0] === variable) {
          return pair[1];
        }
      }

      return false;
    }
  }]);

  return _class;
}( /*#__PURE__*/_wrapNativeSuper(HTMLElement)));