(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];descriptor.enumerable = descriptor.enumerable || false;descriptor.configurable = true;if ("value" in descriptor) descriptor.writable = true;Object.defineProperty(target, descriptor.key, descriptor);
    }
  }return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);if (staticProps) defineProperties(Constructor, staticProps);return Constructor;
  };
}();

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _possibleConstructorReturn(self, call) {
  if (!self) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }return call && ((typeof call === "undefined" ? "undefined" : _typeof(call)) === "object" || typeof call === "function") ? call : self;
}

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function, not " + (typeof superClass === "undefined" ? "undefined" : _typeof(superClass)));
  }subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } });if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
}

(function () {
  var JoomlaAlertElement = function (_HTMLElement) {
    _inherits(JoomlaAlertElement, _HTMLElement);

    _createClass(JoomlaAlertElement, [{
      key: 'type',
      get: function get() {
        return this.getAttribute('type');
      },
      set: function set(value) {
        return this.setAttribute('type', value);
      }
    }, {
      key: 'dismiss',
      get: function get() {
        return this.getAttribute('dismiss');
      }
    }, {
      key: 'acknowledge',
      get: function get() {
        return this.getAttribute('acknowledge');
      }
    }, {
      key: 'href',
      get: function get() {
        return this.getAttribute('href');
      }

      /* Lifecycle, element created */

    }], [{
      key: 'observedAttributes',

      /* Attributes to monitor */
      get: function get() {
        return ['type', 'dismiss', 'acknowledge', 'href'];
      }
    }]);

    function JoomlaAlertElement() {
      _classCallCheck(this, JoomlaAlertElement);

      return _possibleConstructorReturn(this, (JoomlaAlertElement.__proto__ || Object.getPrototypeOf(JoomlaAlertElement)).call(this));
    }

    /* Lifecycle, element appended to the DOM */

    _createClass(JoomlaAlertElement, [{
      key: 'connectedCallback',
      value: function connectedCallback() {
        this.setAttribute('role', 'alert');
        this.classList.add("joomla-alert--show");

        // Default to info
        if (!this.type || ['info', 'warning', 'danger', 'success'].indexOf(this.type) === -1) {
          this.setAttribute('type', 'info');
        }
        // Append button
        if (this.hasAttribute('dismiss') || this.hasAttribute('acknowledge') || this.hasAttribute('href') && this.getAttribute('href') !== '' && !this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close')) {
          this.appendCloseButton();
        }

        this.dispatchCustomEvent('joomla.alert.show');

        var closeButton = this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close');

        if (closeButton) {
          closeButton.focus();
        }
      }

      /* Lifecycle, element removed from the DOM */

    }, {
      key: 'disconnectedCallback',
      value: function disconnectedCallback() {
        this.removeEventListener('joomla.alert.show', this);
        this.removeEventListener('joomla.alert.close', this);
        this.removeEventListener('joomla.alert.closed', this);

        if (this.firstChild.tagName && this.firstChild.tagName.toLowerCase() === 'button') {
          this.firstChild.removeEventListener('click', this);
        }
      }

      /* Respond to attribute changes */

    }, {
      key: 'attributeChangedCallback',
      value: function attributeChangedCallback(attr, oldValue, newValue) {
        switch (attr) {
          case 'type':
            if (!newValue || newValue && ['info', 'warning', 'danger', 'success'].indexOf(newValue) === -1) {
              this.type = 'info';
            }
            break;
          case 'dismiss':
          case 'acknowledge':
            if (!newValue || newValue === "true") {
              this.appendCloseButton();
            } else {
              this.removeCloseButton();
            }
            break;
          case 'href':
            if (!newValue || newValue === '') {
              this.removeCloseButton();
            } else {
              if (!this.querySelector('button.joomla-alert-button--close')) {
                this.appendCloseButton();
              }
            }
            break;
        }
      }

      /* Method to close the alert */

    }, {
      key: 'close',
      value: function close() {
        this.dispatchCustomEvent('joomla.alert.close');
        this.addEventListener("transitionend", function () {
          this.dispatchCustomEvent('joomla.alert.closed');
          this.parentNode.removeChild(this);
        }, false);
        this.classList.remove('joomla-alert--show');
      }

      /* Method to dispatch events */

    }, {
      key: 'dispatchCustomEvent',
      value: function dispatchCustomEvent(eventName) {
        var OriginalCustomEvent = new CustomEvent(eventName);
        OriginalCustomEvent.relatedTarget = this;
        this.dispatchEvent(OriginalCustomEvent);
        this.removeEventListener(eventName, this);
      }

      /* Method to create the close button */

    }, {
      key: 'appendCloseButton',
      value: function appendCloseButton() {
        if (this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close')) {
          return;
        }

        var self = this,
            closeButton = document.createElement('button');

        if (this.hasAttribute('dismiss')) {
          closeButton.classList.add('joomla-alert--close');
          closeButton.innerHTML = '<span aria-hidden="true">&times;</span>';
          closeButton.setAttribute('aria-label', this.getText('JCLOSE', 'Close'));
        } else {
          closeButton.classList.add('joomla-alert-button--close');
          if (this.hasAttribute('acknowledge')) {
            closeButton.innerHTML = this.getText('JOK', 'ok');
          } else {
            closeButton.innerHTML = this.getText('JOPEN', 'Open');
          }
        }

        if (this.firstChild) {
          this.insertBefore(closeButton, this.firstChild);
        } else {
          this.appendChild(closeButton);
        }

        /* Add the required listener */
        if (closeButton) {
          if (!this.href) {
            closeButton.addEventListener('click', function () {
              self.dispatchCustomEvent('joomla.alert.buttonClicked');
              if (self.getAttribute('data-callback')) {
                window[self.getAttribute('data-callback')]();
                self.close();
              } else {
                self.close();
              }
            });
          } else {
            closeButton.addEventListener('click', function () {
              self.dispatchCustomEvent('joomla.alert.buttonClicked');
              window.location.href = self.href;
              self.close();
            });
          }
        }

        if (this.hasAttribute('auto-dismiss')) {
          setTimeout(function () {
            self.dispatchCustomEvent('joomla.alert.buttonClicked');
            if (self.hasAttribute('data-callback')) {
              window[self.getAttribute('data-callback')]();
            } else {
              self.close();
            }
          }, parseInt(self.getAttribute('auto-dismiss')) ? self.getAttribute('auto-dismiss') : 3000);
        }
      }

      /* Method to remove the close button */

    }, {
      key: 'removeCloseButton',
      value: function removeCloseButton() {
        var button = this.querySelector('button');
        if (button) {
          button.removeEventListener('click', this);
          button.parentNode.removeChild(button);
        }
      }

      /* Method to get the translated text */

    }, {
      key: 'getText',
      value: function getText(str, fallback) {
        return window.Joomla && Joomla.JText && Joomla.JText._ && typeof Joomla.JText._ === 'function' && Joomla.JText._(str) ? Joomla.JText._(str) : fallback;
      }
    }]);

    return JoomlaAlertElement;
  }(HTMLElement);

  customElements.define('joomla-alert', JoomlaAlertElement);
})();

},{}]},{},[1]);
