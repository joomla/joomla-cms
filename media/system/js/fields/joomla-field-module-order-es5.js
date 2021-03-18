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
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */
customElements.define('joomla-field-module-order', /*#__PURE__*/function (_HTMLElement) {
  _inherits(_class, _HTMLElement);

  var _super = _createSuper(_class);

  function _class() {
    var _this;

    _classCallCheck(this, _class);

    _this = _super.call(this);
    _this.linkedFieldSelector = '';
    _this.linkedFieldElement = '';
    _this.originalPosition = '';

    _this.writeDynaList.bind(_assertThisInitialized(_this));

    _this.getNewOrder.bind(_assertThisInitialized(_this));

    return _this;
  }

  _createClass(_class, [{
    key: "connectedCallback",
    value: function connectedCallback() {
      this.linkedFieldSelector = this.getAttribute('data-linked-field') || 'jform_position';

      if (!this.linkedFieldSelector) {
        throw new Error('No linked field defined!');
      }

      this.linkedFieldElement = document.getElementById(this.linkedFieldSelector);

      if (!this.linkedFieldElement) {
        throw new Error('No linked field defined!');
      }

      var that = this;
      this.originalPosition = this.linkedFieldElement.value;
      /** Initialize the field * */

      this.getNewOrder(this.originalPosition);
      /** Watch for changes on the linked field * */

      this.linkedFieldElement.addEventListener('change', function () {
        that.originalPosition = that.linkedFieldElement.value;
        that.getNewOrder(that.linkedFieldElement.value);
      });
    }
  }, {
    key: "writeDynaList",
    value: function writeDynaList(selectProperties, source, originalPositionName, originalPositionValue) {
      var i = 0;
      var selectNode = document.createElement('select');

      if (this.hasOwnProperty('disabled')) {
        selectNode.setAttribute('disabled', '');
      }

      if (this.getAttribute('onchange')) {
        selectNode.setAttribute('onchange', this.getAttribute('onchange'));
      }

      if (this.getAttribute('size')) {
        selectNode.setAttribute('size', this.getAttribute('size'));
      }

      selectNode.classList.add(selectProperties.itemClass);
      selectNode.setAttribute('name', selectProperties.name);
      selectNode.id = selectProperties.id;

      for (var x in source) {
        if (!source.hasOwnProperty(x)) {
          continue;
        }

        var node = document.createElement('option');
        var item = source[x];
        node.value = item[1];
        node.innerHTML = item[2];

        if (originalPositionName && originalPositionValue === item[1] || !originalPositionName && i === 0) {
          node.setAttribute('selected', 'selected');
        }

        selectNode.appendChild(node);
        i++;
      }

      this.innerHTML = '';
      this.appendChild(selectNode);
    }
  }, {
    key: "getNewOrder",
    value: function getNewOrder(originalPosition) {
      var url = this.getAttribute('data-url');
      var clientId = this.getAttribute('data-client-id');
      var originalOrder = this.getAttribute('data-ordering');
      var name = this.getAttribute('data-name');
      var attr = this.getAttribute('data-client-attr') ? this.getAttribute('data-client-attr') : 'custom-select';
      var id = "".concat(this.getAttribute('data-id'));
      var orders = [];
      var that = this;
      Joomla.request({
        url: "".concat(url, "&client_id=").concat(clientId, "&position=").concat(originalPosition),
        method: 'GET',
        perform: true,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        onSuccess: function onSuccess(response, xhr) {
          if (response) {
            response = JSON.parse(response);
            /** Check if everything is OK * */

            if (response.data.length > 0) {
              for (var i = 0; i < response.data.length; ++i) {
                orders[i] = response.data[i].split(',');
              }

              that.writeDynaList({
                name: name,
                id: id,
                itemClass: attr
              }, orders, that.originalPosition, originalOrder);
            }
          }
          /** Render messages, if any. There are only message in case of errors. * */


          if (_typeof(response.messages) === 'object' && response.messages !== null) {
            Joomla.renderMessages(response.messages);
          }
        }
      });
    }
  }]);

  return _class;
}( /*#__PURE__*/_wrapNativeSuper(HTMLElement)));