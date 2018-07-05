/* global Joomla, jQuery */
((customElements, Joomla, $) => {
  const ALLOW_NEW = 1;
  const ALLOW_EDIT = 2;
  const ALLOW_CLEAR = 4;
  const ALLOW_SELECT = 8;

  /**
   * Create an element with optional attributes and wrapped elements
   *
   * @param   {string}              type        Element type to create
   * @param   {object}              attributes  Attributes to set on the new element
   * @param   {string|HTMLElement}  wrapped     Zero or more elements to insert into the new element
   *
   * @return  {HTMLElement}
   */
  function getElement(type, attributes, ...wrapped) {
    /* eslint-disable no-prototype-builtins */
    const el = document.createElement(type);

    if (attributes) {
      Object.keys(attributes).forEach(prop => el.setAttribute(prop, attributes[prop]));
    }

    wrapped.forEach((n) => {
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
    /* eslint-disable no-eval, no-unused-vars */
    // Escape all dangerous things
    const safe = tmpl.replace(/\\|`/g, '\\$&');

    // Tag function for a template literal
    function template(strings, ...keys) {
      // Template function, takes any number of values as replacements for template tokens
      return (...values) => {
        const result = keys.map((key, i) => strings[i] + values[parseInt(key, 10)]);
        result.push(strings[strings.length - 1]);

        return result.join('');
      };
    }

    // I know, right?
    return eval(`template\`${safe}\``);
  }

  customElements.define('joomla-field-modal', class JoomlaFieldModal extends HTMLElement {
    /**
     * Set up the element
     */
    constructor() {
      super();

      if (!Joomla) {
        throw new Error('Joomla API is not properly initiated');
      }

      // Save the initial text which we may need later.
      this.initialText = this.innerText;
      this.innerText = '';

      // Allow a custom rendering function. Should we?
      if (typeof Joomla.renderModalField === 'function') {
        Joomla.renderModalField.call(this);
      } else {
        this.render();
      }
    }

    get allow() {
      /* eslint no-bitwise: "off" */
      /* jshint bitwise: false */
      return (this.getAttribute('allow-new') === 'true' ? ALLOW_NEW : 0) |
        (this.getAttribute('allow-edit') === 'true' ? ALLOW_EDIT : 0) |
        (this.getAttribute('allow-clear') !== 'false' ? ALLOW_CLEAR : 0) |
        (this.getAttribute('allow-select') !== 'false' ? ALLOW_SELECT : 0);
    }

    /**
     * Render the element's dom
     *
     * @return  {void}
     */
    render() {
      this.createElements();
      this.assembleElements();
    }

    /**
     * Create all the elements we'll need so they can be accessed without querying
     *
     * @return  {void}
     */
    createElements() {
      const hasValue = !!this.getAttribute('value');

      this.elements = {};

      this.elements.wrapper = getElement('span', { class: this.allow ? 'input-group' : '' });

      this.elements.fieldTitle = getElement('input', {
        class: 'form-control',
        id: `${this.getAttribute('id')}_name`,
        type: 'text',
        value: this.initialText,
        placeholder: this.getAttribute('text-placeholder'),
        disabled: 'disabled',
        size: '35',
      });

      this.elements.fieldId = getElement('input', {
        name: this.getAttribute('name'),
        id: `${this.getAttribute('id')}_id`,
        type: 'hidden',
        value: this.getAttribute('value'),
        required: this.getAttribute('required'),
      });

      const modalButtonAttr = {
        role: 'button',
        class: 'btn',
        'aria-hidden': 'true',
      };

      this.elements.modalButtonClose = getElement(
        'a',
        modalButtonAttr,
        this.getAttribute('text-modal-button-close'),
      );
      this.elements.modalButtonClose.classList.add('btn-secondary');
      this.elements.modalButtonClose.setAttribute('data-task', 'cancel');

      this.elements.modalButtonSave = getElement(
        'a',
        modalButtonAttr,
        this.getAttribute('text-modal-button-save'),
      );
      this.elements.modalButtonSave.classList.add('btn-primary');
      this.elements.modalButtonSave.setAttribute('data-task', 'save');

      this.elements.modalButtonApply = getElement(
        'a',
        modalButtonAttr,
        this.getAttribute('text-modal-button-apply'),
      );
      this.elements.modalButtonApply.classList.add('btn-success');
      this.elements.modalButtonApply.setAttribute('data-task', 'apply');

      if (!this.allow) {
        return;
      }

      this.elements.buttonGroup = getElement('span', { class: 'input-group-append' });

      const buttonAttr = {
        class: 'btn hasTooltip',
        type: 'button',
      };

      /* eslint no-bitwise: ["error", { "allow": ["&"] }] */
      /* jshint bitwise: false */
      if (this.allow & ALLOW_SELECT) {
        this.elements.buttonSelect = getElement(
          'button',
          buttonAttr,
          getElement('span', { class: 'icon-file', 'aria-hidden': 'true' }),
          ' ',
          this.getAttribute('text-button-select'),
        );

        this.elements.buttonSelect.classList.add('btn-primary');
        this.elements.buttonSelect.classList[hasValue ? 'add' : 'remove']('sr-only');
        this.elements.buttonSelect.addEventListener('click', () => this.modalSelect(), true);
      }

      if (this.allow & ALLOW_NEW) {
        this.elements.buttonNew = getElement(
          'button',
          buttonAttr,
          getElement('span', { class: 'icon-new', 'aria-hidden': 'true' }),
          ' ',
          this.getAttribute('text-button-create'),
        );

        this.elements.buttonNew.classList.add('btn-secondary');
        this.elements.buttonNew.classList[hasValue ? 'add' : 'remove']('sr-only');
        this.elements.buttonNew.addEventListener('click', () => this.modalNew(), true);
      }

      if (this.allow & ALLOW_EDIT) {
        this.elements.buttonEdit = getElement(
          'button',
          buttonAttr,
          getElement('span', { class: 'icon-edit', 'aria-hidden': 'true' }),
          ' ',
          this.getAttribute('text-button-edit'),
        );

        this.elements.buttonEdit.classList.add('btn-secondary');
        this.elements.buttonEdit.classList[hasValue ? 'remove' : 'add']('sr-only');
        this.elements.buttonEdit.addEventListener('click', () => this.modalEdit(), true);
      }

      if (this.allow & ALLOW_CLEAR) {
        this.elements.buttonClear = getElement(
          'button',
          buttonAttr,
          getElement('span', { class: 'icon-remove', 'aria-hidden': 'true' }),
          ' ',
          this.getAttribute('text-button-clear'),
        );

        this.elements.buttonClear.classList.add('btn-secondary');
        this.elements.buttonClear.classList[hasValue ? 'remove' : 'add']('sr-only');
        this.elements.buttonClear.addEventListener('click', () => this.clear(), true);
      }
    }

    /**
     * Build the structure of the element
     *
     * @return  {void}
     */
    assembleElements() {
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
     * @return  {void}
     */
    modalSelect() {
      const title = this.getAttribute('text-title-select');
      const body = getElement('iframe', {
        class: 'iframe',
        src: this.getAttribute('url-select'),
        name: '',
      });
      const footer = getElement(
        'a',
        {
          role: 'button',
          class: 'btn btn-secondary',
          'data-dismiss': 'modal',
          'aria-hidden': 'true',
        },
        'Close',
      );
      const modalOptions = {
        closeButton: true,
      };

      this.openModal(title, body, footer, modalOptions)
        .then(r => this.processResult(...r), () => {});
    }

    /**
     * Click handler for the 'new' button. Opens a modal for creating a new item.
     *
     * @return  {void}
     */
    modalNew() {
      const title = this.getAttribute('text-title-new');
      const body = getElement('iframe', {
        class: 'iframe',
        src: this.getAttribute('url-new'),
        name: '',
      });
      const footer = [
        this.elements.modalButtonClose,
        this.elements.modalButtonSave,
        this.elements.modalButtonApply,
      ];
      const modalOptions = {
        backdrop: 'static',
        keyboard: false,
        closeButton: false,
      };

      this.openModal(title, body, footer, modalOptions)
        .then(r => this.processResult(...r), () => {});
    }

    /**
     * Click handler for the 'edit' button. Opens a modal editor.
     *
     * @return  {void}
     */
    modalEdit() {
      const tmpl = templateFactory(this.getAttribute('url-edit'));

      const title = this.getAttribute('text-title-edit');
      const body = getElement('iframe', {
        class: 'iframe',
        src: tmpl(this.elements.fieldId.value),
        name: '',
      });
      const footer = [
        this.elements.modalButtonClose,
        this.elements.modalButtonSave,
        this.elements.modalButtonApply,
      ];
      const modalOptions = {
        backdrop: 'static',
        keyboard: false,
        closeButton: false,
      };

      this.openModal(title, body, footer, modalOptions)
        .then(r => this.processResult(...r), () => {});
    }

    /**
     * Click handler for the 'clear' button. Clears the current value.
     *
     * @return  {void}
     */
    clear() {
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
    openModal(title, body, footer, modalOptions) {
      // Need arrays
      const aBody = (body instanceof Array) ? body : [body];
      const aFooter = (footer instanceof Array) ? footer : [footer];

      const modalWrapper = getElement(
        'div',
        {
          role: 'dialog',
          tabindex: '-1',
          class: 'joomla-modal modal fade show',
        },
        getElement(
          'div',
          {
            class: 'modal-dialog modal-lg jviewport-width80',
            role: 'document',
          },
          getElement(
            'div',
            { class: 'modal-content' },
            getElement(
              'div',
              { class: 'modal-header' },
              getElement(
                'h3',
                { class: 'modal-title' },
                title || 'Title',
              ),
              modalOptions.closeButton ? getElement(
                'button',
                {
                  type: 'button',
                  class: 'close novalidate',
                  'data-dismiss': 'modal',
                },
                '×',
              ) : '',
            ),
            getElement(
              'div',
              { class: 'modal-body jviewport-height70' },
              ...aBody,
            ),
            getElement(
              'div',
              { class: 'modal-footer' },
              ...aFooter,
            ),
          ),
        ),
      );

      const iframe = [].slice.call(modalWrapper.getElementsByTagName('iframe')).shift();

      // Don't even show the modal until the iframe loads
      function frameLoaded() {
        modalWrapper.classList.remove('sr-only');
        iframe.removeEventListener('load', frameLoaded);
      }
      if (iframe) {
        modalWrapper.classList.add('sr-only');
        iframe.addEventListener('load', frameLoaded);
      }

      let selected = null;

      window.jModalSelect = (...args) => {
        selected = args;
        $(modalWrapper).modal('hide');
      };

      const resolveItem = (event) => {
        const task = event.target.getAttribute('data-task') || 'cancel';
        const itemType = (this.getAttribute('item-type') || 'item').toLowerCase();
        const formId = this.getAttribute('form-id') || `${itemType}-form`;
        const idFieldId = this.getAttribute('id-field-id') || 'jform_id';
        const titleFieldId = this.getAttribute('title-field-id') || 'jform_title';
        const iframeWin = iframe.contentWindow;
        const iframeDoc = iframe.contentDocument;

        if (task === 'cancel') {
          iframeWin.Joomla.submitbutton(`${itemType}.${task}`);
          $(modalWrapper).modal('hide');
        }

        // Don't need to do anything else with an invalid form.
        if (!iframeDoc.formvalidator.isValid(iframeDoc.getElementById(formId))) {
          return;
        }

        // When the frame reloads
        function frameReload() {
          iframe.removeEventListener('load', frameReload);

          const frameDoc = iframe.contentDocument;
          const idField = frameDoc.getElementById(idFieldId);
          const titleField = frameDoc.getElementById(titleFieldId);

          if (idField && idField.value !== '0') {
            selected = [idField.value, titleField && titleField.value];

            // If Save & Close (save task), submit the edit close action
            // (so we don't have checked out items).
            if (task === 'save') {
              iframeWin.Joomla.submitbutton(`${itemType}.cancel`);
              $(modalWrapper).modal('hide');
            }
          }

          iframe.classList.remove('sr-only');
        }

        iframe.addEventListener('load', frameReload);

        if (task === 'save') {
          iframe.classList.add('sr-only');
        }

        iframeWin.Joomla.submitbutton(`${itemType}.apply`);
      };

      // If we're using these buttons, clone them to remove old listeners add the new one
      [
        this.elements.modalButtonClose,
        this.elements.modalButtonSave,
        this.elements.modalButtonApply,
      ].forEach((el) => {
        if (!el.parentNode) {
          return;
        }

        const tmp = el.cloneNode(true);
        el.parentNode.replaceChild(tmp, el);

        tmp.addEventListener('click', evt => resolveItem(evt));
      });


      $(modalWrapper)
        // Show the modal
        .modal(modalOptions)
        // When the modal is hidden, get rid of it. We will make a new one each time.
        .one('hidden.bs.modal', () => modalWrapper.parentNode.removeChild(modalWrapper));

      const promise = new Promise((resolve, reject) => {
        $(modalWrapper).one('hide.bs.modal', () => {
          if (selected) {
            resolve(selected);
          } else {
            reject();
          }
        });
      });

      promise.finally(() => { window.jModalSelect = null; });

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
    processResult(id = '', title = '', catid = '', object = '', url = '', language = '') {
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
  });
})(customElements, Joomla, jQuery);
