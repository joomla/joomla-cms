(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

(function (customElements) {
  var JoomlaFieldUser = function (_HTMLElement) {
    _inherits(JoomlaFieldUser, _HTMLElement);

    function JoomlaFieldUser() {
      _classCallCheck(this, JoomlaFieldUser);

      return _possibleConstructorReturn(this, (JoomlaFieldUser.__proto__ || Object.getPrototypeOf(JoomlaFieldUser)).apply(this, arguments));
    }

    _createClass(JoomlaFieldUser, [{
      key: 'connectedCallback',


      // attributeChangedCallback(attr, oldValue, newValue) {}

      value: function connectedCallback() {
        // Set up elements
        this.modal = this.querySelector(this.modalClass);
        this.modalBody = this.querySelector('.modal-body');
        this.input = this.querySelector(this.inputId);
        this.inputName = this.querySelector(this.inputNameClass);
        this.buttonSelect = this.querySelector(this.buttonSelectClass);

        // Bind events
        this.modalClose = this.modalClose.bind(this);
        this.setValue = this.setValue.bind(this);
        if (this.buttonSelect) {
          this.buttonSelect.addEventListener('click', this.modalOpen.bind(this));
          this.modal.addEventListener('hide', this.removeIframe.bind(this));

          // Check for onchange callback,
          var onchangeStr = this.input.getAttribute('data-onchange');
          var onUserSelect = void 0;
          if (onchangeStr) {
            /* eslint-disable */
            onUserSelect = new Function(onchangeStr);
            this.input.addEventListener('change', onUserSelect.bind(this.input));
            /* eslint-enable */
          }
        }
      }
    }, {
      key: 'disconnectedCallback',
      value: function disconnectedCallback() {
        this.buttonSelect.removeEventListener('click', this);
        this.modal.removeEventListener('hide', this);
      }

      // Opens the modal

    }, {
      key: 'modalOpen',
      value: function modalOpen() {
        var self = this;

        // Reconstruct the iframe
        this.removeIframe();
        var iframe = document.createElement('iframe');
        iframe.setAttribute('name', 'field-user-modal');
        iframe.src = this.url.replace('{field-user-id}', this.input.getAttribute('id'));
        iframe.setAttribute('width', this.modalWidth);
        iframe.setAttribute('height', this.modalHeight);

        this.modalBody.appendChild(iframe);

        this.modal.open();

        var iframeEl = this.modalBody.querySelector('iframe');

        // handle the selection on the iframe
        iframeEl.addEventListener('load', function () {
          var iframeDoc = iframeEl.contentWindow.document;
          var buttons = [].slice.call(iframeDoc.querySelectorAll('.button-select'));

          buttons.forEach(function (button) {
            button.addEventListener('click', function (event) {
              self.setValue(event.target.getAttribute('data-user-value'), event.target.getAttribute('data-user-name'));
              self.modalClose();
            });
          });
        });
      }

      // Closes the modal

    }, {
      key: 'modalClose',
      value: function modalClose() {
        Joomla.Modal.getCurrent().close();
        this.modalBody.innerHTML = '';
      }

      // Remove the iframe

    }, {
      key: 'removeIframe',
      value: function removeIframe() {
        this.modalBody.innerHTML = '';
      }

      // Sets the value

    }, {
      key: 'setValue',
      value: function setValue(value, name) {
        this.input.setAttribute('value', value);
        this.inputName.setAttribute('value', name || value);
      }
    }, {
      key: 'url',
      get: function get() {
        return this.getAttribute('url');
      },
      set: function set(value) {
        this.setAttribute('url', value);
      }
    }, {
      key: 'modalClass',
      get: function get() {
        return this.getAttribute('modal');
      },
      set: function set(value) {
        this.setAttribute('modal', value);
      }
    }, {
      key: 'modalWidth',
      get: function get() {
        return this.getAttribute('modal-width');
      },
      set: function set(value) {
        this.setAttribute('modal-width', value);
      }
    }, {
      key: 'modalHeight',
      get: function get() {
        return this.getAttribute('modal-height');
      },
      set: function set(value) {
        this.setAttribute('modal-height', value);
      }
    }, {
      key: 'inputId',
      get: function get() {
        return this.getAttribute('input');
      },
      set: function set(value) {
        this.setAttribute('input', value);
      }
    }, {
      key: 'inputNameClass',
      get: function get() {
        return this.getAttribute('input-name');
      },
      set: function set(value) {
        this.setAttribute('input-name', value);
      }
    }, {
      key: 'buttonSelectClass',
      get: function get() {
        return this.getAttribute('button-select');
      },
      set: function set(value) {
        this.setAttribute('button-select', value);
      }
    }], [{
      key: 'observedAttributes',
      get: function get() {
        return ['url', 'modal-class', 'modal-width', 'modal-height', 'input', 'input-name', 'button-select'];
      }
    }]);

    return JoomlaFieldUser;
  }(HTMLElement);

  customElements.define('joomla-field-user', JoomlaFieldUser);
})(customElements);

},{}]},{},[1]);
