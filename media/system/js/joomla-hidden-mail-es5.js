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
window.customElements.define('joomla-hidden-mail', /*#__PURE__*/function (_HTMLElement) {
  _inherits(_class, _HTMLElement);

  var _super = _createSuper(_class);

  function _class() {
    var _this;

    _classCallCheck(this, _class);

    _this = _super.call(this);
    _this.newElement = '';
    _this.base = '';
    return _this;
  }
  /**
   * Lifecycle
   */


  _createClass(_class, [{
    key: "disconnectedCallback",
    value: function disconnectedCallback() {
      this.innerHTML = '';
    }
    /**
     * Lifecycle
     */

  }, {
    key: "connectedCallback",
    value: function connectedCallback() {
      var _this2 = this;

      this.base = "".concat(this.getAttribute('base'), "/");

      if (this.getAttribute('is-link') === '1') {
        this.newElement = document.createElement('a');
        this.newElement.setAttribute('href', "mailto:".concat(this.constructor.b64DecodeUnicode(this.getAttribute('first')), "@").concat(this.constructor.b64DecodeUnicode(this.getAttribute('last')))); // Get all of the original element attributes, and pass them to the link

        [].slice.call(this.attributes).forEach(function (attribute, index) {
          var _this2$attributes$ite = _this2.attributes.item(index),
              nodeName = _this2$attributes$ite.nodeName;

          if (nodeName) {
            // We do care for some attributes
            if (['is-link', 'is-email', 'first', 'last', 'text'].indexOf(nodeName) === -1) {
              var _this2$attributes$ite2 = _this2.attributes.item(index),
                  nodeValue = _this2$attributes$ite2.nodeValue;

              _this2.newElement.setAttribute(nodeName, nodeValue);
            }
          }
        });
      } else {
        this.newElement = document.createElement('span');
      }

      if (this.getAttribute('text')) {
        var innerStr = this.constructor.b64DecodeUnicode(this.getAttribute('text'));
        innerStr = innerStr.replace('src="images/', "src=\"".concat(this.base, "images/")).replace('src="media/', "src=\"".concat(this.base, "media/"));
        this.newElement.innerHTML = innerStr;
      } else {
        this.newElement.innerText = "".concat(window.atob(this.getAttribute('first')), "@").concat(window.atob(this.getAttribute('last')));
      } // Remove the noscript message


      this.innerText = ''; // Display the new element

      this.appendChild(this.newElement);
    }
  }], [{
    key: "b64DecodeUnicode",
    value: function b64DecodeUnicode(str) {
      return decodeURIComponent(Array.prototype.map.call(atob(str), function (c) {
        return "%".concat("00".concat(c.charCodeAt(0).toString(16)).slice(-2));
      }).join(''));
    }
  }]);

  return _class;
}( /*#__PURE__*/_wrapNativeSuper(HTMLElement)));