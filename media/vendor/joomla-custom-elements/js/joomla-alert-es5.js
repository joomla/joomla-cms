(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

function _typeof2(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof2 = function _typeof2(obj) { return typeof obj; }; } else { _typeof2 = function _typeof2(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof2(obj); }

function _typeof(obj) {
  if (typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol") {
    _typeof = function _typeof(obj) {
      return _typeof2(obj);
    };
  } else {
    _typeof = function _typeof(obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : _typeof2(obj);
    };
  }

  return _typeof(obj);
}

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

function _possibleConstructorReturn(self, call) {
  if (call && (_typeof(call) === "object" || typeof call === "function")) {
    return call;
  }

  return _assertThisInitialized(self);
}

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) _setPrototypeOf(subClass, superClass);
}

function _wrapNativeSuper(Class) {
  var _cache = typeof Map === "function" ? new Map() : undefined;

  _wrapNativeSuper = function _wrapNativeSuper(Class) {
    if (Class === null || !_isNativeFunction(Class)) return Class;

    if (typeof Class !== "function") {
      throw new TypeError("Super expression must either be null or a function");
    }

    if (typeof _cache !== "undefined") {
      if (_cache.has(Class)) return _cache.get(Class);

      _cache.set(Class, Wrapper);
    }

    function Wrapper() {
      return _construct(Class, arguments, _getPrototypeOf(this).constructor);
    }

    Wrapper.prototype = Object.create(Class.prototype, {
      constructor: {
        value: Wrapper,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
    return _setPrototypeOf(Wrapper, Class);
  };

  return _wrapNativeSuper(Class);
}

function isNativeReflectConstruct() {
  if (typeof Reflect === "undefined" || !Reflect.construct) return false;
  if (Reflect.construct.sham) return false;
  if (typeof Proxy === "function") return true;

  try {
    Date.prototype.toString.call(Reflect.construct(Date, [], function () {}));
    return true;
  } catch (e) {
    return false;
  }
}

function _construct(Parent, args, Class) {
  if (isNativeReflectConstruct()) {
    _construct = Reflect.construct;
  } else {
    _construct = function _construct(Parent, args, Class) {
      var a = [null];
      a.push.apply(a, args);
      var Constructor = Function.bind.apply(Parent, a);
      var instance = new Constructor();
      if (Class) _setPrototypeOf(instance, Class.prototype);
      return instance;
    };
  }

  return _construct.apply(null, arguments);
}

function _isNativeFunction(fn) {
  return Function.toString.call(fn).indexOf("[native code]") !== -1;
}

function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

function _getPrototypeOf(o) {
  _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

(function () {
  var JoomlaAlertElement =
  /*#__PURE__*/
  function (_HTMLElement) {
    _inherits(JoomlaAlertElement, _HTMLElement);

    function JoomlaAlertElement() {
      _classCallCheck(this, JoomlaAlertElement);

      return _possibleConstructorReturn(this, _getPrototypeOf(JoomlaAlertElement).apply(this, arguments));
    }

    _createClass(JoomlaAlertElement, [{
      key: "connectedCallback",

      /* Lifecycle, element appended to the DOM */
      value: function connectedCallback() {
        this.classList.add('joomla-alert--show'); // Default to info

        if (!this.type || ['info', 'warning', 'danger', 'success'].indexOf(this.type) === -1) {
          this.setAttribute('type', 'info');
        } // Default to alert


        if (!this.role || ['alert', 'alertdialog'].indexOf(this.role) === -1) {
          this.setAttribute('role', 'alert');
        } // Append button


        if (this.hasAttribute('dismiss') || this.hasAttribute('acknowledge') || this.hasAttribute('href') && this.getAttribute('href') !== '' && !this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close')) {
          this.appendCloseButton();
        }

        if (this.hasAttribute('auto-dismiss')) {
          this.autoDismiss();
        }

        this.dispatchCustomEvent('joomla.alert.show');
      }
      /* Lifecycle, element removed from the DOM */

    }, {
      key: "disconnectedCallback",
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
      key: "attributeChangedCallback",
      value: function attributeChangedCallback(attr, oldValue, newValue) {
        switch (attr) {
          case 'type':
            if (!newValue || newValue && ['info', 'warning', 'danger', 'success'].indexOf(newValue) === -1) {
              this.type = 'info';
            }

            break;

          case 'role':
            if (!newValue || newValue && ['alert', 'alertdialog'].indexOf(newValue) === -1) {
              this.role = 'alert';
            }

            break;

          case 'dismiss':
          case 'acknowledge':
            if (!newValue || newValue === 'true') {
              this.appendCloseButton();
            } else {
              this.removeCloseButton();
            }

            break;

          case 'auto-dismiss':
            this.autoDismiss();
            break;

          case 'href':
            if (!newValue || newValue === '') {
              this.removeCloseButton();
            } else if (!this.querySelector('button.joomla-alert-button--close')) {
              this.appendCloseButton();
            }

            break;

          default:
            break;
        }
      }
      /* Method to close the alert */

    }, {
      key: "close",
      value: function close() {
        var _this = this;

        var element = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
        this.dispatchCustomEvent('joomla.alert.close');
        this.addEventListener('transitionend', function () {
          _this.dispatchCustomEvent('joomla.alert.closed');

          if (element) {
            element.parentNode.removeChild(element);
          } else {
            _this.remove();
          }
        }, false);
        this.classList.remove('joomla-alert--show');
      }
      /* Method to dispatch events */

    }, {
      key: "dispatchCustomEvent",
      value: function dispatchCustomEvent(eventName) {
        var OriginalCustomEvent = new CustomEvent(eventName);
        OriginalCustomEvent.relatedTarget = this;
        this.dispatchEvent(OriginalCustomEvent);
        this.removeEventListener(eventName, this);
      }
      /* Method to create the close button */

    }, {
      key: "appendCloseButton",
      value: function appendCloseButton() {
        if (this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close')) {
          return;
        }

        var self = this;
        var closeButton = document.createElement('button');

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
      }
      /* Method to auto-dismiss */

    }, {
      key: "autoDismiss",
      value: function autoDismiss() {
        var self = this;
        setTimeout(function () {
          self.dispatchCustomEvent('joomla.alert.buttonClicked');

          if (self.hasAttribute('data-callback')) {
            window[self.getAttribute('data-callback')]();
          } else {
            self.close(self);
          }
        }, parseInt(self.getAttribute('auto-dismiss'), 10) ? self.getAttribute('auto-dismiss') : 3000);
      }
      /* Method to remove the close button */

    }, {
      key: "removeCloseButton",
      value: function removeCloseButton() {
        var button = this.querySelector('button');

        if (button) {
          button.removeEventListener('click', this);
          button.parentNode.removeChild(button);
        }
      }
      /* Method to get the translated text */

    }, {
      key: "getText",
      value: function getText(str, fallback) {
        // TODO: Remove coupling to Joomla CMS Core JS here

        /* eslint-disable-next-line no-undef */
        return window.Joomla && Joomla.JText && Joomla.JText._ && typeof Joomla.JText._ === 'function' && Joomla.JText._(str) ? Joomla.JText._(str) : fallback;
      }
    }, {
      key: "type",
      get: function get() {
        return this.getAttribute('type');
      },
      set: function set(value) {
        return this.setAttribute('type', value);
      }
    }, {
      key: "role",
      get: function get() {
        return this.getAttribute('role');
      },
      set: function set(value) {
        return this.setAttribute('role', value);
      }
    }, {
      key: "dismiss",
      get: function get() {
        return this.getAttribute('dismiss');
      }
    }, {
      key: "autodismiss",
      get: function get() {
        return this.getAttribute('auto-dismiss');
      }
    }, {
      key: "acknowledge",
      get: function get() {
        return this.getAttribute('acknowledge');
      }
    }, {
      key: "href",
      get: function get() {
        return this.getAttribute('href');
      }
    }], [{
      key: "observedAttributes",

      /* Attributes to monitor */
      get: function get() {
        return ['type', 'role', 'dismiss', 'acknowledge', 'href'];
      }
    }]);

    return JoomlaAlertElement;
  }(_wrapNativeSuper(HTMLElement));

  customElements.define('joomla-alert', JoomlaAlertElement);
})();

},{}]},{},[1]);
