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

    function JoomlaAlertElement() {
      _classCallCheck(this, JoomlaAlertElement);

      return _possibleConstructorReturn(this, (JoomlaAlertElement.__proto__ || Object.getPrototypeOf(JoomlaAlertElement)).apply(this, arguments));
    }

    _createClass(JoomlaAlertElement, [{
      key: 'connectedCallback',

      /* Lifecycle, element appended to the DOM */
      value: function connectedCallback() {
        // Trigger show event
        this.dispatchCustomEvent('joomla.alert.show');
        this.setAttribute('role', 'alert');
        this.classList.add('joomla-alert--show');

        // If no type has been defined, the default is "info"
        if (!this.type) {
          this.setAttribute('type', 'info');
        }

        // Append button
        if (this.hasAttribute('dismiss') || this.hasAttribute('acknowledge') || this.hasAttribute('href') && this.getAttribute('href') !== '') {
          if (!this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close')) {
            this.appendCloseButton.bind(this)();
          }
        }

        // Trigger shown event
        this.dispatchCustomEvent('joomla.alert.show');

        if (this.closeButton) {
          this.closeButton.focus();
        }
      }

      /* Lifecycle, element removed from the DOM */

    }, {
      key: 'disconnectedCallback',
      value: function disconnectedCallback() {
        if (this.firstChild.tagName && this.firstChild.tagName.toLowerCase() === 'button') {
          this.firstChild.removeEventListener('click', this.buttonCloseFn);
        }
      }

      /* Respond to attribute changes */

    }, {
      key: 'attributeChangedCallback',
      value: function attributeChangedCallback(attr, oldValue, newValue) {
        switch (attr) {
          case 'type':
            if (!newValue || ['info', 'primary', 'warning', 'success', 'danger'].indexOf(newValue) === -1) {
              this.type = 'info';
            }
            break;
          case 'dismiss':
          case 'acknowledge':
            if (!newValue || newValue === 'true') {
              if (this.firstElementChild && this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() !== 'button') {
                this.appendCloseButton.bind(this)();
              }
            } else if (this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() === 'button') {
              this.removeCloseButton.bind(this)();
            }
            break;
          case 'href':
            if (!newValue || newValue === '') {
              if (this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() !== 'button') {
                this.removeCloseButton.bind(this)();
              }
            } else if (this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() !== 'button' && this.firstElementChild.classList.contains('joomla-alert-button--close')) {
              this.appendCloseButton.bind(this)();
            }
            break;
          case 'auto-dismiss':
            if (!newValue || newValue === '') {
              this.removeAttribute('auto-dismiss');
            }
            break;
          default:
            break;
        }
      }
    }, {
      key: 'buttonCloseFn',
      value: function buttonCloseFn() {
        this.dispatchCustomEvent('joomla.alert.buttonClicked');
        if (this.href) {
          window.location.href = this.href;
        }
        this.close();
      }

      /* Method to close the alert */

    }, {
      key: 'close',
      value: function close() {
        var _this2 = this;

        this.dispatchCustomEvent('joomla.alert.close');
        this.addEventListener('transitionend', function () {
          _this2.dispatchCustomEvent('joomla.alert.closed');
          _this2.parentNode.removeChild(_this2);
        });
        this.classList.remove('joomla-alert--show');
      }

      /* Method to dispatch events. Internal */

    }, {
      key: 'dispatchCustomEvent',
      value: function dispatchCustomEvent(eventName) {
        var OriginalCustomEvent = new CustomEvent(eventName, { bubbles: true, cancelable: true });
        OriginalCustomEvent.relatedTarget = this;
        this.dispatchEvent(OriginalCustomEvent);
        this.removeEventListener(eventName, this);
      }

      /* Method to create the close button. Internal */

    }, {
      key: 'appendCloseButton',
      value: function appendCloseButton() {
        if (this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close')) {
          return;
        }

        var closeButton = document.createElement('button');

        if (this.hasAttribute('dismiss')) {
          closeButton.classList.add('joomla-alert--close');
          closeButton.innerHTML = '<span aria-hidden="true">&times;</span>';
          closeButton.setAttribute('aria-label', this.textClose);
        } else {
          closeButton.classList.add('joomla-alert-button--close');
          if (this.hasAttribute('acknowledge')) {
            closeButton.innerHTML = this.textAcknowledge;
          } else {
            closeButton.innerHTML = this.textDismiss;
          }
        }

        this.closeButton = closeButton;

        if (this.firstChild) {
          this.insertBefore(closeButton, this.firstChild);
        } else {
          this.appendChild(closeButton);
        }

        /* Add the required listener */
        if (closeButton) {
          closeButton.addEventListener('click', this.buttonCloseFn.bind(this));
        }

        if (this.autoDismiss > 0) {
          var self = this;
          var timeout = this.autoDismiss;
          setTimeout(function () {
            self.dispatchCustomEvent('joomla.alert.buttonClicked');
            if (self.href) {
              window.location.href = self.href;
            }
            self.close();
          }, timeout);
        }
      }

      /* Method to remove the close button. Internal */

    }, {
      key: 'removeCloseButton',
      value: function removeCloseButton() {
        if (this.closeButton) {
          this.closeButton.removeEventListener('click', this.buttonCloseFn);
          this.closeButton.parentNode.removeChild(this.closeButton);
        }
      }
    }, {
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
      },
      set: function set(value) {
        return this.setAttribute('dismiss', value);
      }
    }, {
      key: 'acknowledge',
      get: function get() {
        return this.getAttribute('acknowledge');
      },
      set: function set(value) {
        return this.setAttribute('acknowledge', value);
      }
    }, {
      key: 'href',
      get: function get() {
        return this.getAttribute('href');
      },
      set: function set(value) {
        return this.setAttribute('href', value);
      }
    }, {
      key: 'autoDismiss',
      get: function get() {
        return parseInt(this.getAttribute('auto-dismiss'), 10);
      },
      set: function set(value) {
        return this.setAttribute('auto-dismiss', parseInt(value, 10));
      }
    }, {
      key: 'position',
      get: function get() {
        return this.getAttribute('position');
      },
      set: function set(value) {
        return this.setAttribute('position', value);
      }
    }, {
      key: 'textClose',
      get: function get() {
        return this.getAttribute('textClose') || 'Close';
      },
      set: function set(value) {
        return this.setAttribute('textClose', value);
      }
    }, {
      key: 'textDismiss',
      get: function get() {
        return this.getAttribute('textDismiss') || 'Open';
      },
      set: function set(value) {
        return this.setAttribute('textDismiss', value);
      }
    }, {
      key: 'textAcknowledge',
      get: function get() {
        return this.getAttribute('textAcknowledge') || 'Ok';
      },
      set: function set(value) {
        return this.setAttribute('textAcknowledge', value);
      }
    }], [{
      key: 'observedAttributes',

      /* Attributes to monitor */
      get: function get() {
        return ['type', 'dismiss', 'acknowledge', 'href', 'auto-dismiss', 'position', 'textClose', 'textDismiss', 'textAcknowledge'];
      }
    }]);

    return JoomlaAlertElement;
  }(HTMLElement);

  customElements.define('joomla-alert', JoomlaAlertElement);
})();

},{}]},{},[1]);
