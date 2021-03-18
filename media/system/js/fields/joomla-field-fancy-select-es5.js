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

/**
 * Fancy select field, which use Choices.js
 *
 * Example:
 * <joomla-field-fancy-select ...attributes>
 *   <select>...</select>
 * </joomla-field-fancy-select>
 *
 * Possible attributes:
 *
 * allow-custom          Whether allow User to dynamically add a new value.
 * new-item-prefix=""    Prefix for a dynamically added value.
 *
 * remote-search         Enable remote search.
 * url=""                Url for remote search.
 * term-key="term"       Variable key name for searched term, will be appended to Url.
 *
 * min-term-length="1"   The minimum length a search value should be before choices are searched.
 * placeholder=""        The value of the inputs placeholder.
 * search-placeholder="" The value of the search inputs placeholder.
 */
window.customElements.define('joomla-field-fancy-select', /*#__PURE__*/function (_HTMLElement) {
  _inherits(_class, _HTMLElement);

  var _super = _createSuper(_class);

  _createClass(_class, [{
    key: "allowCustom",
    // Attributes to monitor
    get: function get() {
      return this.hasAttribute('allow-custom');
    }
  }, {
    key: "remoteSearch",
    get: function get() {
      return this.hasAttribute('remote-search');
    }
  }, {
    key: "url",
    get: function get() {
      return this.getAttribute('url');
    }
  }, {
    key: "termKey",
    get: function get() {
      return this.getAttribute('term-key') || 'term';
    }
  }, {
    key: "minTermLength",
    get: function get() {
      return parseInt(this.getAttribute('min-term-length')) || 1;
    }
  }, {
    key: "newItemPrefix",
    get: function get() {
      return this.getAttribute('new-item-prefix') || '';
    }
  }, {
    key: "placeholder",
    get: function get() {
      return this.getAttribute('placeholder');
    }
  }, {
    key: "searchPlaceholder",
    get: function get() {
      return this.getAttribute('search-placeholder');
    }
  }, {
    key: "value",
    get: function get() {
      return this.choicesInstance.getValue(true);
    },
    set: function set($val) {
      this.choicesInstance.setChoiceByValue($val);
    }
    /**
     * Lifecycle
     */

  }]);

  function _class() {
    var _this;

    _classCallCheck(this, _class);

    _this = _super.call(this); // Keycodes

    _this.keyCode = {
      ENTER: 13
    };

    if (!Joomla) {
      throw new Error('Joomla API is not properly initiated');
    }

    if (!window.Choices) {
      throw new Error('JoomlaFieldFancySelect requires Choices.js to work');
    }

    _this.choicesCache = {};
    _this.activeXHR = null;
    _this.choicesInstance = null;
    _this.isDisconnected = false;
    return _this;
  }
  /**
   * Lifecycle
   */


  _createClass(_class, [{
    key: "connectedCallback",
    value: function connectedCallback() {
      var _this2 = this;

      // Make sure Choices are loaded
      if (window.Choices || document.readyState === 'complete') {
        this.doConnect();
      } else {
        var callback = function callback() {
          _this2.doConnect();

          window.removeEventListener('load', callback);
        };

        window.addEventListener('load', callback);
      }
    }
  }, {
    key: "doConnect",
    value: function doConnect() {
      var _this3 = this;

      // Get a <select> element
      this.select = this.querySelector('select');

      if (!this.select) {
        throw new Error('JoomlaFieldFancySelect requires <select> element to work');
      } // The element was already initialised previously and perhaps was detached from DOM


      if (this.choicesInstance) {
        if (this.isDisconnected) {
          // Re init previous instance
          this.choicesInstance.init();
          this.isDisconnected = false;
        }

        return;
      }

      this.isDisconnected = false; // Add placeholder option for multiple mode,
      // Because it not supported as parameter by Choices for <select> https://github.com/jshjohnson/Choices#placeholder

      if (this.select.multiple && this.placeholder) {
        var option = document.createElement('option');
        option.setAttribute('placeholder', '');
        option.textContent = this.placeholder;
        this.select.appendChild(option);
      } // Init Choices


      this.choicesInstance = new Choices(this.select, {
        placeholderValue: this.placeholder,
        searchPlaceholderValue: this.searchPlaceholder,
        removeItemButton: true,
        searchFloor: this.minTermLength,
        searchResultLimit: 10,
        shouldSort: false,
        fuseOptions: {
          threshold: 0.3 // Strict search

        },
        noResultsText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
        noChoicesText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
        itemSelectText: Joomla.Text._('JGLOBAL_SELECT_PRESS_TO_SELECT', 'Press to select'),
        // Redefine some classes
        classNames: {
          button: 'choices__button_joomla' // It is need because an original styling use unavailable Icon.svg file

        }
      }); // Handle typing of custom term

      if (this.allowCustom) {
        this.addEventListener('keydown', function (event) {
          if (event.keyCode !== _this3.keyCode.ENTER || event.target !== _this3.choicesInstance.input.element) {
            return;
          }

          event.preventDefault();

          if (_this3.choicesInstance.highlightPosition || !event.target.value || _this3.choicesCache[event.target.value]) {
            return;
          } // Make sure nothing is highlighted


          var highlighted = _this3.choicesInstance.dropdown.element.querySelector(".".concat(_this3.choicesInstance.config.classNames.highlightedState));

          if (highlighted) {
            return;
          }

          _this3.choicesInstance.setChoices([{
            value: _this3.newItemPrefix + event.target.value,
            label: event.target.value,
            selected: true,
            customProperties: {
              value: event.target.value // Store real value, just in case

            }
          }], 'value', 'label', false);

          _this3.choicesCache[event.target.value] = event.target.value;
          event.target.value = null;

          _this3.choicesInstance.hideDropdown();

          return false;
        });
      } // Handle remote search


      if (this.remoteSearch && this.url) {
        // Cache existing
        this.choicesInstance.config.choices.forEach(function (choiceItem) {
          _this3.choicesCache[choiceItem.value] = choiceItem.label;
        });
        var lookupDelay = 300;
        var lookupTimeout = null;
        this.select.addEventListener('search', function () {
          clearTimeout(lookupTimeout);
          lookupTimeout = setTimeout(_this3.requestLookup.bind(_this3), lookupDelay);
        });
      }
    }
    /**
     * Lifecycle
     */

  }, {
    key: "disconnectedCallback",
    value: function disconnectedCallback() {
      // Destroy Choices instance, to unbind event listeners
      if (this.choicesInstance) {
        this.choicesInstance.destroy();
        this.isDisconnected = true;
      }

      if (this.activeXHR) {
        this.activeXHR.abort();
        this.activeXHR = null;
      }
    }
  }, {
    key: "requestLookup",
    value: function requestLookup() {
      var _this4 = this;

      var url = this.url;
      url += url.indexOf('?') === -1 ? '?' : '&';
      url += "".concat(encodeURIComponent(this.termKey), "=").concat(encodeURIComponent(this.choicesInstance.input.value)); // Stop previous request if any

      if (this.activeXHR) {
        this.activeXHR.abort();
      }

      this.activeXHR = Joomla.request({
        url: url,
        onSuccess: function onSuccess(response) {
          _this4.activeXHR = null;
          var items = response ? JSON.parse(response) : [];

          if (!items.length) {
            return;
          } // Remove duplications


          var item; // eslint-disable-next-line no-plusplus

          for (var i = items.length - 1; i >= 0; i--) {
            // The loop must be form the end !!!
            item = items[i]; // eslint-disable-next-line prefer-template

            item.value = '' + item.value; // Make sure the value is a string, choices.js expect a string.

            if (_this4.choicesCache[item.value]) {
              items.splice(i, 1);
            } else {
              _this4.choicesCache[item.value] = item.text;
            }
          } // Add new options to field, assume that each item is object, eg {value: "foo", text: "bar"}


          if (items.length) {
            _this4.choicesInstance.setChoices(items, 'value', 'text', false);
          }
        },
        onError: function onError() {
          _this4.activeXHR = null;
        }
      });
    }
  }, {
    key: "disableAllOptions",
    value: function disableAllOptions() {
      // Choices.js does not offer a public API for accessing the choices
      // So we have to access the private store => don't eslint
      // eslint-disable-next-line no-underscore-dangle
      var choices = this.choicesInstance._store.choices;
      choices.forEach(function (elem, index) {
        choices[index].disabled = true;
        choices[index].selected = false;
      });
      this.choicesInstance.clearStore();
      this.choicesInstance.setChoices(choices, 'value', 'label', true);
    }
  }, {
    key: "enableAllOptions",
    value: function enableAllOptions() {
      // Choices.js does not offer a public API for accessing the choices
      // So we have to access the private store => don't eslint
      // eslint-disable-next-line no-underscore-dangle
      var choices = this.choicesInstance._store.choices;
      var values = this.choicesInstance.getValue(true);
      choices.forEach(function (elem, index) {
        choices[index].disabled = false;
      });
      this.choicesInstance.clearStore();
      this.choicesInstance.setChoices(choices, 'value', 'label', true);
      this.value = values;
    }
  }, {
    key: "disableByValue",
    value: function disableByValue($val) {
      // Choices.js does not offer a public API for accessing the choices
      // So we have to access the private store => don't eslint
      // eslint-disable-next-line no-underscore-dangle
      var choices = this.choicesInstance._store.choices;
      var values = this.choicesInstance.getValue(true);
      choices.forEach(function (elem, index) {
        if (elem.value === $val) {
          choices[index].disabled = true;
          choices[index].selected = false;
        }
      });
      var index = values.indexOf($val);

      if (index > -1) {
        values.slice(index, 1);
      }

      this.choicesInstance.clearStore();
      this.choicesInstance.setChoices(choices, 'value', 'label', true);
      this.value = values;
    }
  }, {
    key: "enableByValue",
    value: function enableByValue($val) {
      // Choices.js does not offer a public API for accessing the choices
      // So we have to access the private store => don't eslint
      // eslint-disable-next-line no-underscore-dangle
      var choices = this.choicesInstance._store.choices;
      var values = this.choicesInstance.getValue(true);
      choices.forEach(function (elem, index) {
        if (elem.value === $val) {
          choices[index].disabled = false;
        }
      });
      this.choicesInstance.clearStore();
      this.choicesInstance.setChoices(choices, 'value', 'label', true);
      this.value = values;
    }
  }]);

  return _class;
}( /*#__PURE__*/_wrapNativeSuper(HTMLElement)));