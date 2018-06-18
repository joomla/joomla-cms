;((customElements, Joomla, $) => {

  const ALLOW_NEW    = 1;
  const ALLOW_EDIT   = 2;
  const ALLOW_CLEAR  = 4;
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
    let el = document.createElement(type);

    if (attributes)
    {
      for (let prop in attributes) {
        if (attributes.hasOwnProperty(prop))
        {
          el.setAttribute(prop, attributes[prop]);
        }
      }
    }

    wrapped.forEach(n => {
      if (typeof n == 'string') {
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
    tmpl=tmpl.replace(/\\|`/g, '\\$&');

    // Tag function for a template literal
    function template(strings, ...keys) {
      // Template function, takes any number of values as replacements for template tokens
      return (...values) => {
          let result = keys.map((key, i) => strings[i] + values[parseInt(key)]);
        result.push(strings[strings.length - 1]);

          return result.join('');
      };
    }

    // I know, right?
    return eval('template`' + tmpl + '`');
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
      if (typeof Joomla.renderModalField == 'function')
      {
        Joomla.renderModalField.call(this);
      }
      else
      {
        this.render();
      }
    }

    get allow() {
      return (this.getAttribute('allow-new') == 'true' ? ALLOW_NEW : 0) |
        (this.getAttribute('allow-edit') == 'true' ? ALLOW_EDIT : 0) |
        (this.getAttribute('allow-clear') != 'false' ? ALLOW_CLEAR : 0) |
        (this.getAttribute('allow-select') != 'false' ? ALLOW_SELECT : 0);
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
      let hasValue = !!this.getAttribute('value');

      this.elements = {};

      this.elements.wrapper = getElement('span', {class: this.allow ? 'input-group' : ''})

      this.elements.fieldTitle = getElement('input', {
        class: 'form-control',
        id: this.getAttribute('id') + '_name',
        type: 'text',
        value: this.initialText,
        placeholder: this.getAttribute('text-placeholder'),
        disabled: 'disabled',
        size: '35',
      });

      this.elements.fieldId = getElement('input', {
        name: this.getAttribute('name'),
        id: this.getAttribute('id') + '_id',
        type: 'hidden',
        value: this.getAttribute('value'),
        required: this.getAttribute('required'),
      });

      let modalButtonAttr = {
        role: 'button',
        class: 'btn',
        'aria-hidden': 'true',
      };

      this.elements.modalButtonClose = getElement('a',
        modalButtonAttr, this.getAttribute('text-modal-button-close'));
      this.elements.modalButtonClose.classList.add('btn-secondary');
      this.elements.modalButtonClose.setAttribute('data-task', 'cancel');

      this.elements.modalButtonSave = getElement('a',
        modalButtonAttr, this.getAttribute('text-modal-button-save'));
      this.elements.modalButtonSave.classList.add('btn-primary');
      this.elements.modalButtonSave.setAttribute('data-task', 'save');

      this.elements.modalButtonApply = getElement('a',
        modalButtonAttr, this.getAttribute('text-modal-button-apply'));
      this.elements.modalButtonApply.classList.add('btn-success');
      this.elements.modalButtonApply.setAttribute('data-task', 'apply');

      if (!this.allow)
      {
        return;
      }

      this.elements.buttonGroup = getElement('span', {class: 'input-group-append'});

      let buttonAttr = {
        class: 'btn hasTooltip',
        type: 'button',
      };

      if (this.allow & ALLOW_SELECT)
      {
        this.elements.buttonSelect = getElement('button',
          buttonAttr,
          getElement('span', {class: 'icon-file', 'aria-hidden': 'true'}),
          ' ', this.getAttribute('text-button-select')
        );

        this.elements.buttonSelect.classList.add('btn-primary');
        this.elements.buttonSelect.classList[hasValue ? 'add' : 'remove']('sr-only');
        this.elements.buttonSelect.addEventListener('click', (evt) => this.modalSelect(evt), true);
      }

      if (this.allow & ALLOW_NEW)
      {
        this.elements.buttonNew = getElement('button',
          buttonAttr,
          getElement('span', {class: 'icon-new', 'aria-hidden': 'true'}),
          ' ', this.getAttribute('text-button-create')
        );

        this.elements.buttonNew.classList.add('btn-secondary');
        this.elements.buttonNew.classList[hasValue ? 'add' : 'remove']('sr-only');
        this.elements.buttonNew.addEventListener('click', (evt) => this.modalNew(evt), true);
      }

      if (this.allow & ALLOW_EDIT)
      {
        this.elements.buttonEdit = getElement('button',
          buttonAttr,
          getElement('span', {class: 'icon-edit', 'aria-hidden': 'true'}),
          ' ', this.getAttribute('text-button-edit')
        );

        this.elements.buttonEdit.classList.add('btn-secondary');
        this.elements.buttonEdit.classList[hasValue ? 'remove' : 'add']('sr-only');
        this.elements.buttonEdit.addEventListener('click', (evt) => this.modalEdit(evt), true);
      }

      if (this.allow & ALLOW_CLEAR)
      {
        this.elements.buttonClear = getElement('button',
          buttonAttr,
          getElement('span', {class: 'icon-remove', 'aria-hidden': 'true'}),
          ' ', this.getAttribute('text-button-clear')
        );

        this.elements.buttonClear.classList.add('btn-secondary');
        this.elements.buttonClear.classList[hasValue ? 'remove' : 'add']('sr-only');
        this.elements.buttonClear.addEventListener('click', (evt) => this.clear(evt), true);
      }
    }

    /**
     * Build the structure of the element
     *
     * @return  {void}
     */
    assembleElements()
    {
      this.elements.wrapper.appendChild(this.elements.fieldId);
      this.elements.wrapper.appendChild(this.elements.fieldTitle);

      if (this.elements.buttonGroup)
      {
        this.elements.wrapper.appendChild(this.elements.buttonGroup);

        if (this.elements.buttonSelect)
        {
          this.elements.buttonGroup.appendChild(this.elements.buttonSelect);
        }

        if (this.elements.buttonNew)
        {
          this.elements.buttonGroup.appendChild(this.elements.buttonNew);
        }

        if (this.elements.buttonEdit)
        {
          this.elements.buttonGroup.appendChild(this.elements.buttonEdit);
        }

        if (this.elements.buttonClear)
        {
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
    modalSelect(evt) {
      let title = this.getAttribute('text-title-select');
      let body = getElement('iframe', {
        class: 'iframe',
        src: this.getAttribute('url-select'),
        name: '',
      });
      let footer = getElement('a', {
          role: 'button',
          class: 'btn btn-secondary',
          'data-dismiss': 'modal',
          'aria-hidden': 'true',
        },
        'Close'
      );
      let modalOptions = {
        closeButton: true,
      };

      this.openModal(title, body, footer, modalOptions)
        .then(r => this.processResult(...r), r => {});
    }

    /**
     * Click handler for the 'new' button. Opens a modal for creating a new item.
     *
     * @param   {Event}  evt  The click event
     *
     * @return  {void}
     */
    modalNew(evt) {
      let title = this.getAttribute('text-title-new');
      let body = getElement('iframe', {
        class: 'iframe',
        src: this.getAttribute('url-new'),
        name: '',
      });
      let footer = [
        this.elements.modalButtonClose,
        this.elements.modalButtonSave,
        this.elements.modalButtonApply,
      ];
      let modalOptions = {
        backdrop: 'static',
        keyboard: false,
        closeButton: false,
      };

      this.openModal(title, body, footer, modalOptions)
        .then(r => this.processResult(...r), r => {});
    }

    /**
     * Click handler for the 'edit' button. Opens a modal editor.
     *
     * @param   {Event}  evt  The click event
     *
     * @return  {void}
     */
    modalEdit(evt) {
      let tmpl = templateFactory(this.getAttribute('url-edit'));

      let title = this.getAttribute('text-title-edit');
      let body = getElement('iframe', {
        class: 'iframe',
        src: tmpl(this.elements.fieldId.value),
        name: '',
      });
      let footer = [
        this.elements.modalButtonClose,
        this.elements.modalButtonSave,
        this.elements.modalButtonApply,
      ];
      let modalOptions = {
        backdrop: 'static',
        keyboard: false,
        closeButton: false,
      };

      this.openModal(title, body, footer, modalOptions)
        .then(r => this.processResult(...r), r => {});
    }

    /**
     * Click handler for the 'clear' button. Clears the current value.
     *
     * @param   {Event}  evt  The click event
     *
     * @return  {void}
     */
    clear(evt) {
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
      body = (body instanceof Array) ? body : [body];
      footer = (footer instanceof Array) ? footer : [footer];

      let modalWrapper = getElement('div',
        {
          role: 'dialog',
          tabindex: '-1',
          class: 'joomla-modal modal fade show',
        },
        getElement('div',
          {
            class: 'modal-dialog modal-lg jviewport-width80',
            role: 'document',
          },
          getElement('div',
            {class: 'modal-content'},
            getElement('div',
              {class: 'modal-header'},
              getElement('h3',
                {class: 'modal-title'},
                title || 'Title'
              ),
              modalOptions.closeButton ? getElement('button',
                {
                  type: 'button',
                  class: 'close novalidate',
                  'data-dismiss': 'modal',
                },
                '×'
              ) : ''
            ),
            getElement('div',
              {class: 'modal-body jviewport-height70'},
              ...body
            ),
            getElement('div',
              {class: 'modal-footer'},
              ...footer
            )
          )
        )
      );

      let iframe = [].slice.call(modalWrapper.getElementsByTagName('iframe')).shift();

      // Don't even show the modal until the iframe loads
      if (iframe)
      {
        function frameLoaded () {
          modalWrapper.classList.remove('sr-only');
          iframe.removeEventListener('load', frameLoaded);
        }
        modalWrapper.classList.add('sr-only');
        iframe.addEventListener('load', frameLoaded);
      }

      let selected = null;

      window.jModalSelect = (...args) => {
        selected = args;
        $(modalWrapper).modal('hide');
      };

      let resolveItem = (event) => {
        let task         = event.target.getAttribute('data-task') || 'cancel',
          itemType     = (this.getAttribute('item-type') || 'item').toLowerCase(),
          formId       = this.getAttribute('form-id') || itemType + '-form',
          idFieldId    = this.getAttribute('id-field-id') || 'jform_id',
          titleFieldId = this.getAttribute('title-field-id') || 'jform_title',
          iframe       = [].slice.call(modalWrapper.getElementsByTagName('iframe')).shift(),
          iframeWin    = iframe.contentWindow,
          iframeDoc    = iframe.contentDocument;

        if (task === 'cancel')
        {
          iframeWin.Joomla.submitbutton(itemType + '.' + task);
          $(modalWrapper).modal('hide');
        }

        // Don't need to do anything else with an invalid form.
        if (!iframeDoc.formvalidator.isValid(iframeDoc.getElementById(formId)))
        {
          return;
        }

        // When the frame reloads
        function frameLoaded (evt) {
          iframe.removeEventListener('load', frameLoaded);

          let iframeDoc = iframe.contentDocument,
            idField = iframeDoc.getElementById(idFieldId),
            titleField = iframeDoc.getElementById(titleFieldId);

          if (idField && idField.value != '0')
          {
            selected = [idField.value, titleField && titleField.value];

            // If Save & Close (save task), submit the edit close action (so we don't have checked out items).
            if (task === 'save')
            {
              iframeWin.Joomla.submitbutton(itemType + '.cancel');
              $(modalWrapper).modal('hide');
            }
          }

          iframe.classList.remove('sr-only');
        }

        iframe.addEventListener('load', frameLoaded);

        if (task === 'save')
        {
          iframe.classList.add('sr-only');
        }

        iframeWin.Joomla.submitbutton(itemType + '.apply');
      };

      // If we're using these buttons, clone them to remove old listeners add the new one
      [
        this.elements.modalButtonClose,
        this.elements.modalButtonSave,
        this.elements.modalButtonApply,
      ].forEach(el => {
        if (!el.parentNode)
        {
          return;
        }

        let tmp = el.cloneNode(true);
        el.parentNode.replaceChild(tmp, el);

        tmp.addEventListener('click', evt => resolveItem(evt));
      });


      $(modalWrapper)
        // Show the modal
        .modal(modalOptions)
        // When the modal is hidden, get rid of it. We will make a new one each time.
        .one('hidden.bs.modal', () => modalWrapper.parentNode.removeChild(modalWrapper));

      var promise = new Promise((resolve, reject) =>
        $(modalWrapper).one('hide.bs.modal', (evt) =>
          selected ? resolve(selected) : reject()
        )
      );

      promise.finally(r => window.jModalSelect = null);

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
      // Default values.
      id       = id || '';
      title    = title || '';
      catid    = catid || '';
      object   = object || '';
      url      = url || '';
      language = language || '';

      this.elements.fieldId.value    = id || '';
      this.elements.fieldTitle.value = id ? title : '';

      if (this.elements.buttonSelect)
      {
        this.elements.buttonSelect.classList[id ? 'add' : 'remove']('sr-only');
      }
      if (this.elements.buttonNew)
      {
        this.elements.buttonNew.classList[id ? 'add' : 'remove']('sr-only');
      }
      if (this.elements.buttonEdit)
      {
        this.elements.buttonEdit.classList[id ? 'remove' : 'add']('sr-only');
      }
      if (this.elements.buttonClear)
      {
        this.elements.buttonClear.classList[id ? 'remove' : 'add']('sr-only');
      }

      if (this.elements.fieldId.getAttribute('data-required') == '1')
      {
        document.formvalidator.validate(this.elements.fieldId);
        document.formvalidator.validate(this.elements.fieldTitle);
      }
    }
  });
})(customElements, Joomla, jQuery);
