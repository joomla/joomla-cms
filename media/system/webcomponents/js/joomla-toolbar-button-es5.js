(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function (customElements) {
  'use strict';

  var JoomlaToolbarButton = function (_HTMLElement) {
    _inherits(JoomlaToolbarButton, _HTMLElement);

    _createClass(JoomlaToolbarButton, [{
      key: 'task',

      // Attribute getters
      get: function get() {
        return this.getAttribute('task');
      }
    }, {
      key: 'listSelection',
      get: function get() {
        return this.hasAttribute('list-selection');
      }
    }, {
      key: 'form',
      get: function get() {
        return this.getAttribute('form');
      }
    }, {
      key: 'formValidation',
      get: function get() {
        return this.hasAttribute('form-validation');
      }
    }, {
      key: 'confirmMessage',
      get: function get() {
        return this.getAttribute('confirm-message');
      }
    }]);

    function JoomlaToolbarButton() {
      _classCallCheck(this, JoomlaToolbarButton);

      // We need a button to support button behavior,
      // because we cannot currently extend HTMLButtonElement
      var _this = _possibleConstructorReturn(this, (JoomlaToolbarButton.__proto__ || Object.getPrototypeOf(JoomlaToolbarButton)).call(this));

      _this.buttonElement = _this.querySelector('button');
      _this.disabled = false;

      // If list selection are required, set button to disabled by default
      if (_this.listSelection) {
        _this.setDisabled(true);
      }

      _this.addEventListener('click', function (e) {
        return _this.executeTask(e);
      });
      return _this;
    }

    _createClass(JoomlaToolbarButton, [{
      key: 'connectedCallback',
      value: function connectedCallback() {
        var _this2 = this;

        // Check whether we have a form
        var formSelector = this.form || '#adminForm';
        this.formElement = document.querySelector(formSelector);

        if (this.listSelection) {
          if (!this.formElement) {
            throw new Error('The form "' + formSelector + '" is required to perform the task, but the form not found on the page.');
          }

          // Watch on list selection
          this.formElement.boxchecked.addEventListener('change', function (event) {
            // Check whether we have selected something
            _this2.setDisabled(event.target.value < 1);
          });
        }
      }
    }, {
      key: 'setDisabled',
      value: function setDisabled(disabled) {
        // Make sure we have a boolean value
        this.disabled = !!disabled;

        if (this.buttonElement) {
          if (this.disabled) {
            this.buttonElement.setAttribute('disabled', true);
          } else {
            this.buttonElement.removeAttribute('disabled');
          }
        }
      }
    }, {
      key: 'executeTask',
      value: function executeTask() {
        if (this.disabled) {
          return false;
        }

        // eslint-disable-next-line no-restricted-globals
        if (this.confirmMessage && !confirm(this.confirmMessage)) {
          return false;
        }

        if (this.task) {
          Joomla.submitbutton(this.task, this.form, this.formValidation);
        } else {
          throw new Error('"task" attribute must be preset to perform an action.');
        }

        return true;
      }
    }]);

    return JoomlaToolbarButton;
  }(HTMLElement);

  customElements.define('joomla-toolbar-button', JoomlaToolbarButton);
})(customElements);

},{}]},{},[1]);
