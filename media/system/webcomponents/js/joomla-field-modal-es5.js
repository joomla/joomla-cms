(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;(function (customElements, Joomla, $) {

  var ALLOW_NEW = 1;
  var ALLOW_EDIT = 2;
  var ALLOW_CLEAR = 4;
  var ALLOW_SELECT = 8;

  /**
   * Create an element with optional attributes and wrapped elements
   *
   * @param   {string}              type        Element type to create
   * @param   {object}              attributes  Attributes to set on the new element
   * @param   {string|HTMLElement}  wrapped     Zero or more elements to insert into the new element
   *
   * @return  {HTMLElement}
   */
  function getElement(type, attributes) {
    var el = document.createElement(type);

    if (attributes) {
      for (var prop in attributes) {
        if (attributes.hasOwnProperty(prop)) {
          el.setAttribute(prop, attributes[prop]);
        }
      }
    }

    for (var _len = arguments.length, wrapped = Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
      wrapped[_key - 2] = arguments[_key];
    }

    wrapped.forEach(function (n) {
      if (typeof n === 'string') {
        el.appendChild(document.createTextNode(n));
      } else if (n instanceof HTMLElement) {
        el.appendChild(n);
      }
    });

    return el;
  }

  /**
   * Get a function that can be used to generate a string based on a template
   *
   * @param   {string}  tmpl  A template string containing tokens such as ${0}, ${1}, ${2}, etc
   *
   * @return  {function}
   */
  function templateFactory(tmpl) {
    // Escape all dangerous things
    tmpl = tmpl.replace(/\\|`/g, '\\$&');

    // Tag function for a template literal
    function template(strings) {
      for (var _len2 = arguments.length, keys = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
        keys[_key2 - 1] = arguments[_key2];
      }

      // Template function, takes any number of values as replacements for template tokens
      return function () {
        for (var _len3 = arguments.length, values = Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
          values[_key3] = arguments[_key3];
        }

        var result = keys.map(function (key, i) {
          return strings[i] + values[parseInt(key)];
        });
        result.push(strings[strings.length - 1]);

        return result.join('');
      };
    }

    // I know, right?
    return eval('template`' + tmpl + '`');
  }

  customElements.define('joomla-field-modal', function (_HTMLElement) {
    _inherits(JoomlaFieldModal, _HTMLElement);

    /**
     * Set up the element
     */
    function JoomlaFieldModal() {
      _classCallCheck(this, JoomlaFieldModal);

      var _this = _possibleConstructorReturn(this, (JoomlaFieldModal.__proto__ || Object.getPrototypeOf(JoomlaFieldModal)).call(this));

      if (!Joomla) {
        throw new Error('Joomla API is not properly initiated');
      }

      // Save the initial text which we may need later.
      _this.initialText = _this.innerText;
      _this.innerText = '';

      // Allow a custom rendering function. Should we?
      if (typeof Joomla.renderModalField === 'function') {
        Joomla.renderModalField.call(_this);
      } else {
        _this.render();
      }
      return _this;
    }

    _createClass(JoomlaFieldModal, [{
      key: 'render',


      /**
       * Render the element's dom
       *
       * @return  {void}
       */
      value: function render() {
        this.createElements();
        this.assembleElements();
      }

      /**
       * Create all the elements we'll need so they can be accessed without querying
       *
       * @return  {void}
       */

    }, {
      key: 'createElements',
      value: function createElements() {
        var _this2 = this;

        var hasValue = !!this.getAttribute('value');

        this.elements = {};

        this.elements.wrapper = getElement('span', { class: this.allow ? 'input-group' : '' });

        this.elements.fieldTitle = getElement('input', {
          class: 'form-control',
          id: this.getAttribute('id') + '_name',
          type: 'text',
          value: this.initialText,
          placeholder: this.getAttribute('text-placeholder'),
          disabled: 'disabled',
          size: '35'
        });

        this.elements.fieldId = getElement('input', {
          name: this.getAttribute('name'),
          id: this.getAttribute('id') + '_id',
          type: 'hidden',
          value: this.getAttribute('value'),
          required: this.getAttribute('required')
        });

        var modalButtonAttr = {
          role: 'button',
          class: 'btn',
          'aria-hidden': 'true'
        };

        this.elements.modalButtonClose = getElement('a', modalButtonAttr, this.getAttribute('text-modal-button-close'));
        this.elements.modalButtonClose.classList.add('btn-secondary');
        this.elements.modalButtonClose.setAttribute('data-task', 'cancel');

        this.elements.modalButtonSave = getElement('a', modalButtonAttr, this.getAttribute('text-modal-button-save'));
        this.elements.modalButtonSave.classList.add('btn-primary');
        this.elements.modalButtonSave.setAttribute('data-task', 'save');

        this.elements.modalButtonApply = getElement('a', modalButtonAttr, this.getAttribute('text-modal-button-apply'));
        this.elements.modalButtonApply.classList.add('btn-success');
        this.elements.modalButtonApply.setAttribute('data-task', 'apply');

        if (!this.allow) {
          return;
        }

        this.elements.buttonGroup = getElement('span', { class: 'input-group-append' });

        var buttonAttr = {
          class: 'btn hasTooltip',
          type: 'button'
        };

        if (this.allow & ALLOW_SELECT) {
          this.elements.buttonSelect = getElement('button', buttonAttr, getElement('span', { class: 'icon-file', 'aria-hidden': 'true' }), ' ', this.getAttribute('text-button-select'));

          this.elements.buttonSelect.classList.add('btn-primary');
          this.elements.buttonSelect.classList[hasValue ? 'add' : 'remove']('sr-only');
          this.elements.buttonSelect.addEventListener('click', function (evt) {
            return _this2.modalSelect(evt);
          }, true);
        }

        if (this.allow & ALLOW_NEW) {
          this.elements.buttonNew = getElement('button', buttonAttr, getElement('span', { class: 'icon-new', 'aria-hidden': 'true' }), ' ', this.getAttribute('text-button-create'));

          this.elements.buttonNew.classList.add('btn-secondary');
          this.elements.buttonNew.classList[hasValue ? 'add' : 'remove']('sr-only');
          this.elements.buttonNew.addEventListener('click', function (evt) {
            return _this2.modalNew(evt);
          }, true);
        }

        if (this.allow & ALLOW_EDIT) {
          this.elements.buttonEdit = getElement('button', buttonAttr, getElement('span', { class: 'icon-edit', 'aria-hidden': 'true' }), ' ', this.getAttribute('text-button-edit'));

          this.elements.buttonEdit.classList.add('btn-secondary');
          this.elements.buttonEdit.classList[hasValue ? 'remove' : 'add']('sr-only');
          this.elements.buttonEdit.addEventListener('click', function (evt) {
            return _this2.modalEdit(evt);
          }, true);
        }

        if (this.allow & ALLOW_CLEAR) {
          this.elements.buttonClear = getElement('button', buttonAttr, getElement('span', { class: 'icon-remove', 'aria-hidden': 'true' }), ' ', this.getAttribute('text-button-clear'));

          this.elements.buttonClear.classList.add('btn-secondary');
          this.elements.buttonClear.classList[hasValue ? 'remove' : 'add']('sr-only');
          this.elements.buttonClear.addEventListener('click', function (evt) {
            return _this2.clear(evt);
          }, true);
        }
      }

      /**
       * Build the structure of the element
       *
       * @return  {void}
       */

    }, {
      key: 'assembleElements',
      value: function assembleElements() {
        this.elements.wrapper.appendChild(this.elements.fieldId);
        this.elements.wrapper.appendChild(this.elements.fieldTitle);

        if (this.elements.buttonGroup) {
          this.elements.wrapper.appendChild(this.elements.buttonGroup);

          if (this.elements.buttonSelect) {
            this.elements.buttonGroup.appendChild(this.elements.buttonSelect);
          }

          if (this.elements.buttonNew) {
            this.elements.buttonGroup.appendChild(this.elements.buttonNew);
          }

          if (this.elements.buttonEdit) {
            this.elements.buttonGroup.appendChild(this.elements.buttonEdit);
          }

          if (this.elements.buttonClear) {
            this.elements.buttonGroup.appendChild(this.elements.buttonClear);
          }
        }

        this.appendChild(this.elements.wrapper);
      }

      /**
       * Click handler for the 'select' button. Opens a modal selector.
       *
       * @param   {Event}  evt  The click event
       *
       * @return  {void}
       */

    }, {
      key: 'modalSelect',
      value: function modalSelect(evt) {
        var _this3 = this;

        var title = this.getAttribute('text-title-select');
        var body = getElement('iframe', {
          class: 'iframe',
          src: this.getAttribute('url-select'),
          name: ''
        });
        var footer = getElement('a', {
          role: 'button',
          class: 'btn btn-secondary',
          'data-dismiss': 'modal',
          'aria-hidden': 'true'
        }, 'Close');
        var modalOptions = {
          closeButton: true
        };

        this.openModal(title, body, footer, modalOptions).then(function (r) {
          return _this3.processResult.apply(_this3, _toConsumableArray(r));
        }, function (r) {});
      }

      /**
       * Click handler for the 'new' button. Opens a modal for creating a new item.
       *
       * @param   {Event}  evt  The click event
       *
       * @return  {void}
       */

    }, {
      key: 'modalNew',
      value: function modalNew(evt) {
        var _this4 = this;

        var title = this.getAttribute('text-title-new');
        var body = getElement('iframe', {
          class: 'iframe',
          src: this.getAttribute('url-new'),
          name: ''
        });
        var footer = [this.elements.modalButtonClose, this.elements.modalButtonSave, this.elements.modalButtonApply];
        var modalOptions = {
          backdrop: 'static',
          keyboard: false,
          closeButton: false
        };

        this.openModal(title, body, footer, modalOptions).then(function (r) {
          return _this4.processResult.apply(_this4, _toConsumableArray(r));
        }, function (r) {});
      }

      /**
       * Click handler for the 'edit' button. Opens a modal editor.
       *
       * @param   {Event}  evt  The click event
       *
       * @return  {void}
       */

    }, {
      key: 'modalEdit',
      value: function modalEdit(evt) {
        var _this5 = this;

        var tmpl = templateFactory(this.getAttribute('url-edit'));

        var title = this.getAttribute('text-title-edit');
        var body = getElement('iframe', {
          class: 'iframe',
          src: tmpl(this.elements.fieldId.value),
          name: ''
        });
        var footer = [this.elements.modalButtonClose, this.elements.modalButtonSave, this.elements.modalButtonApply];
        var modalOptions = {
          backdrop: 'static',
          keyboard: false,
          closeButton: false
        };

        this.openModal(title, body, footer, modalOptions).then(function (r) {
          return _this5.processResult.apply(_this5, _toConsumableArray(r));
        }, function (r) {});
      }

      /**
       * Click handler for the 'clear' button. Clears the current value.
       *
       * @param   {Event}  evt  The click event
       *
       * @return  {void}
       */

    }, {
      key: 'clear',
      value: function clear(evt) {
        this.processResult();
      }

      /**
       * Open a modal dialog
       *
       * @param   {string}                    title         Title for the modal dialog
       * @param   {array|string|HTMLElement}  body          Body content
       * @param   {array|string|HTMLElement}  footer        Footer content
       * @param   {object}                    modalOptions  Modal behavior options
       *
       * @return  {promise}
       */

    }, {
      key: 'openModal',
      value: function openModal(title, body, footer, modalOptions) {
        var _this6 = this;

        // Need arrays
        body = body instanceof Array ? body : [body];
        footer = footer instanceof Array ? footer : [footer];

        var modalWrapper = getElement('div', {
          role: 'dialog',
          tabindex: '-1',
          class: 'joomla-modal modal fade show'
        }, getElement('div', {
          class: 'modal-dialog modal-lg jviewport-width80',
          role: 'document'
        }, getElement('div', { class: 'modal-content' }, getElement('div', { class: 'modal-header' }, getElement('h3', { class: 'modal-title' }, title || 'Title'), modalOptions.closeButton ? getElement('button', {
          type: 'button',
          class: 'close novalidate',
          'data-dismiss': 'modal'
        }, '×') : ''), getElement.apply(undefined, ['div', { class: 'modal-body jviewport-height70' }].concat(_toConsumableArray(body))), getElement.apply(undefined, ['div', { class: 'modal-footer' }].concat(_toConsumableArray(footer))))));

        var iframe = [].slice.call(modalWrapper.getElementsByTagName('iframe')).shift();

        // Don't even show the modal until the iframe loads
        if (iframe) {
          var _frameLoaded = function _frameLoaded() {
            modalWrapper.classList.remove('sr-only');
            iframe.removeEventListener('load', _frameLoaded);
          };

          modalWrapper.classList.add('sr-only');
          iframe.addEventListener('load', _frameLoaded);
        }

        var selected = null;

        window.jModalSelect = function () {
          for (var _len4 = arguments.length, args = Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
            args[_key4] = arguments[_key4];
          }

          selected = args;
          $(modalWrapper).modal('hide');
        };

        var resolveItem = function resolveItem(event) {
          var task = event.target.getAttribute('data-task') || 'cancel',
              itemType = (_this6.getAttribute('item-type') || 'item').toLowerCase(),
              formId = _this6.getAttribute('form-id') || itemType + '-form',
              idFieldId = _this6.getAttribute('id-field-id') || 'jform_id',
              titleFieldId = _this6.getAttribute('title-field-id') || 'jform_title',
              iframe = [].slice.call(modalWrapper.getElementsByTagName('iframe')).shift(),
              iframeWin = iframe.contentWindow,
              iframeDoc = iframe.contentDocument;

          if (task === 'cancel') {
            iframeWin.Joomla.submitbutton(itemType + '.' + task);
            $(modalWrapper).modal('hide');
          }

          // Don't need to do anything else with an invalid form.
          if (!iframeDoc.formvalidator.isValid(iframeDoc.getElementById(formId))) {
            return;
          }

          // When the frame reloads
          function frameLoaded(evt) {
            iframe.removeEventListener('load', frameLoaded);

            var iframeDoc = iframe.contentDocument,
                idField = iframeDoc.getElementById(idFieldId),
                titleField = iframeDoc.getElementById(titleFieldId);

            if (idField && idField.value != '0') {
              selected = [idField.value, titleField && titleField.value];

              // If Save & Close (save task), submit the edit close action (so we don't have checked out items).
              if (task === 'save') {
                iframeWin.Joomla.submitbutton(itemType + '.cancel');
                $(modalWrapper).modal('hide');
              }
            }

            iframe.classList.remove('sr-only');
          }

          iframe.addEventListener('load', frameLoaded);

          if (task === 'save') {
            iframe.classList.add('sr-only');
          }

          iframeWin.Joomla.submitbutton(itemType + '.apply');
        };

        // If we're using these buttons, clone them to remove old listeners add the new one
        [this.elements.modalButtonClose, this.elements.modalButtonSave, this.elements.modalButtonApply].forEach(function (el) {
          if (!el.parentNode) {
            return;
          }

          var tmp = el.cloneNode(true);
          el.parentNode.replaceChild(tmp, el);

          tmp.addEventListener('click', function (evt) {
            return resolveItem(evt);
          });
        });

        $(modalWrapper)
        // Show the modal
        .modal(modalOptions)
        // When the modal is hidden, get rid of it. We will make a new one each time.
        .one('hidden.bs.modal', function () {
          return modalWrapper.parentNode.removeChild(modalWrapper);
        });

        var promise = new Promise(function (resolve, reject) {
          $(modalWrapper).one('hide.bs.modal', function (evt) {
            return selected ? resolve(selected) : reject();
          });
        });

        promise.finally(function () {
          window.jModalSelect = null;
        });

        return promise;
      }

      /**
       * Handle the results of a change to the selected item.
       *
       * @param   {String}  [id='']        Item id
       * @param   {String}  [title='']     Item title
       * @param   {String}  [catid='']     Category id
       * @param   {String}  [object='']    Object
       * @param   {String}  [url='']       Url
       * @param   {String}  [language='']  Language
       *
       * @return  {void}
       */

    }, {
      key: 'processResult',
      value: function processResult() {
        var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
        var title = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
        var catid = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
        var object = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
        var url = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '';
        var language = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : '';

        this.elements.fieldId.value = id || '';
        this.elements.fieldTitle.value = id ? title : '';

        if (this.elements.buttonSelect) {
          this.elements.buttonSelect.classList[id ? 'add' : 'remove']('sr-only');
        }
        if (this.elements.buttonNew) {
          this.elements.buttonNew.classList[id ? 'add' : 'remove']('sr-only');
        }
        if (this.elements.buttonEdit) {
          this.elements.buttonEdit.classList[id ? 'remove' : 'add']('sr-only');
        }
        if (this.elements.buttonClear) {
          this.elements.buttonClear.classList[id ? 'remove' : 'add']('sr-only');
        }

        if (this.elements.fieldId.getAttribute('data-required') === '1') {
          document.formvalidator.validate(this.elements.fieldId);
          document.formvalidator.validate(this.elements.fieldTitle);
        }
      }
    }, {
      key: 'allow',
      get: function get() {
        return (this.getAttribute('allow-new') === 'true' ? ALLOW_NEW : 0) | (this.getAttribute('allow-edit') === 'true' ? ALLOW_EDIT : 0) | (this.getAttribute('allow-clear') !== 'false' ? ALLOW_CLEAR : 0) | (this.getAttribute('allow-select') !== 'false' ? ALLOW_SELECT : 0);
      }
    }]);

    return JoomlaFieldModal;
  }(HTMLElement));
})(customElements, Joomla, jQuery);

},{}]},{},[1]);
