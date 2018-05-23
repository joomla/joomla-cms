(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

(function (customElements) {
  var JoomlaGroupButtonElement = function (_HTMLElement) {
    _inherits(JoomlaGroupButtonElement, _HTMLElement);

    function JoomlaGroupButtonElement() {
      _classCallCheck(this, JoomlaGroupButtonElement);

      var _this = _possibleConstructorReturn(this, (JoomlaGroupButtonElement.__proto__ || Object.getPrototypeOf(JoomlaGroupButtonElement)).call(this));

      _this.buttons = [];
      _this.radios = [];

      // Do some binding
      _this.initCheckboxes = _this.initCheckboxes.bind(_this);
      _this.initRadios = _this.initRadios.bind(_this);
      _this.onClickRadio = _this.onClickRadio.bind(_this);
      return _this;
    }

    _createClass(JoomlaGroupButtonElement, [{
      key: 'connectedCallback',
      value: function connectedCallback() {
        this.buttons = [].slice.call(this.querySelectorAll('[type="checkbox"]'));

        if (this.buttons.length) {
          // Checkboxes
          this.initCheckboxes();
        } else {
          // Radios
          this.radios = [].slice.call(this.querySelectorAll('[type="radio"]'));

          if (this.radios.length) {
            this.initRadios();
          }
        }
      }
    }, {
      key: 'disconnectedCallback',
      value: function disconnectedCallback() {
        var _this2 = this;

        // remove events
        if (this.buttons.length) {
          this.buttons.forEach(function (button) {
            button.removeEventListener('click', _this2.onClickCheckboxes);
          });
        }
        if (this.radios.length) {
          this.radios.forEach(function (radio) {
            radio.removeEventListener('click', _this2.onClickRadio);
          });
        }
      }
    }, {
      key: 'initCheckboxes',
      value: function initCheckboxes() {
        var _this3 = this;

        this.buttons.forEach(function (button) {
          if (button.parentNode.tagName.toLowerCase() !== 'label') {
            return;
          }
          if (button.getAttribute('checked') || button.parentNode.classList.contains('active')) {
            button.setAttribute('checked', '');
            button.parentNode.setAttribute('aria-pressed', 'true');
          } else {
            button.removeAttribute('checked');
            button.parentNode.setAttribute('aria-pressed', 'false');
          }

          button.setAttribute('tabindex', 0);
          button.addEventListener('click', _this3.onClickCheckboxes);
        });
      }
    }, {
      key: 'initRadios',
      value: function initRadios() {
        var _this4 = this;

        this.radios.forEach(function (radio) {
          if (radio.parentNode.tagName.toLowerCase() !== 'label') {
            return;
          }
          if (radio.getAttribute('checked') || radio.parentNode.classList.contains('active')) {
            radio.setAttribute('checked', '');
            radio.parentNode.setAttribute('aria-pressed', 'true');
          } else {
            radio.removeAttribute('checked');
            radio.parentNode.setAttribute('aria-pressed', 'false');
          }

          radio.addEventListener('click', _this4.onClickRadio);
        });
      }
    }, {
      key: 'onClickRadio',
      value: function onClickRadio(e) {
        this.clearAllRadios();
        if (e.currentTarget.checked) {
          e.currentTarget.setAttribute('checked', '');
          e.currentTarget.parentNode.classList.add('active');
          e.currentTarget.parentNode.setAttribute('aria-pressed', 'true');
        } else {
          e.currentTarget.removeAttribute('checked');
          e.currentTarget.parentNode.classList.remove('active');
          e.currentTarget.parentNode.setAttribute('aria-pressed', 'false');
        }
      }
    }, {
      key: 'onClickCheckboxes',
      value: function onClickCheckboxes(e) {
        if (e.currentTarget.checked) {
          e.currentTarget.setAttribute('checked', '');
          e.currentTarget.parentNode.classList.add('active');
          e.currentTarget.parentNode.setAttribute('aria-pressed', 'true');
        } else {
          e.currentTarget.removeAttribute('checked');
          e.currentTarget.parentNode.classList.remove('active');
          e.currentTarget.parentNode.setAttribute('aria-pressed', 'false');
        }
      }
    }, {
      key: 'clearAllRadios',
      value: function clearAllRadios() {
        this.radios.forEach(function (radio) {
          radio.removeAttribute('checked');
          if (radio.parentNode.tagName.toLowerCase() === 'label') {
            radio.parentNode.classList.remove('active');
            radio.parentNode.setAttribute('aria-pressed', 'false');
          }
        });
      }
    }]);

    return JoomlaGroupButtonElement;
  }(HTMLElement);

  customElements.define('joomla-field-group-buttons', JoomlaGroupButtonElement);
})(customElements);

},{}]},{},[1]);
