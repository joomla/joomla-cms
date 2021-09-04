class CreateOverrides extends HTMLElement {
  constructor() {
    super();

    this.fetchOptions = {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    };

    this.url = new URL(`${Joomla.getOptions('system.paths').baseFull}index.php?option=com_templates`);
    this.html = `<div class="ms-4 me-4">
    <div class="control-group">
      <div class="control-label">
        <label for="extension-type-selector">${Joomla.Text._('COM_TEMPLATES_SELECT_TYPE_OF_OVERRIDE_LABEL')}</label>
      </div>
      <div class="controls">
        <select id="extension-type-selector" class="form-select"></select>
      </div>
    </div>
    <div class="control-group" hidden>
      <div class="control-label">
        <label for="extension-selector">${Joomla.Text._('COM_TEMPLATES_COMPONENT_LABEL')}</label>
      </div>
      <div class="controls">
        <select id="extension-selector" class="form-select"></select>
      </div>
    </div>
    <div class="control-group" hidden>
      <div class="control-label">
        <label for="layout-selector">${Joomla.Text._('COM_TEMPLATES_LAYOUT_SELECT_LABEL')}</label>
      </div>
      <div class="controls">
        <select id="layout-selector" class="form-select"></select>
      </div>
    </div>
    <div class="control-group" hidden>
      <div class="control-label">
        <label for="override_creator_name">${Joomla.Text._('COM_TEMPLATES_LAYOUT_CUSTOM_NAME')}</label>
      </div>
      <div class="controls">
        <fieldset id="override_creator_name">
          <legend class="visually-hidden">${Joomla.Text._('COM_TEMPLATES_LAYOUT_CUSTOM_NAME')}</legend>
          <div class="switcher">
            <input type="radio" id="override_creator_name0" name="override_creator_named" value="0" checked="" class="active ">
            <label for="override_creator_name0">${Joomla.Text._('JNO')}</label>
            <input type="radio" id="override_creator_name1" name="override_creator_named" value="1">
            <label for="override_creator_name1">${Joomla.Text._('JYES')}</label>
            <span class="toggle-outside">
              <span class="toggle-inside"></span>
            </span>
          </div>
        </fieldset>
      </div>
    </div>
    <div class="control-group" hidden>
      <div class="control-label">
        <label for="override_creator_name_input">${Joomla.Text._('COM_TEMPLATES_LAYOUT_CUSTOM_NAME_LABEL')}</label>
      </div>
      <div class="controls has-success">
        <input type="text" id="override_creator_name_input" value="" class="form-control" size="40" maxlength="255">
      </div>
    </div>
    <div class="control-group" hidden>
      <button type="button" class="btn btn-success w-100">{{buttonPrimary}}</button>
    </div>
  </div>`;

    this.data = {};
  }

  async connectedCallback() {
    this.task = this.getAttribute('task');
    this.client = this.getAttribute('client');
    this.token = this.getAttribute('token');
    this.item = this.getAttribute('item');

    if (Object.keys(this.data).length === 0) {
      const { url } = this;
      url.searchParams.append('task', this.task);
      url.searchParams.append('client', this.client);
      url.searchParams.append(this.getAttribute('token'), 1);
      url.searchParams.append('id', this.item);

      const response = await fetch(url, this.fetchOptions);
      const data = await response.json();
      this.data = data.data;
    }

    this.innerHTML = this.html;
    this.createBtn = this.querySelector('.btn.btn-success');
    this.createBtn.setAttribute('disabled', '');
    this.createBtn.innerText = Joomla.Text._('COM_TEMPLATES_CREATE_OVERRIDE');
    this.createOverrides();
  }

  // disconnectedCallback() {

  // }

  // attributeChangedCallback(attrName, oldVal, newVal) {

  // }

  async createOverrides() {
    const elements = [
      {
        value: '',
        name: Joomla.Text._('COM_TEMPLATES_SELECT_OPTION_NONE'),
      },
      {
        value: 'components',
        name: Joomla.Text._('COM_TEMPLATES_COMPONENT'),
      },
      {
        value: 'layouts',
        name: Joomla.Text._('COM_TEMPLATES_LAYOUT'),
      },
      {
        value: 'modules',
        name: Joomla.Text._('COM_TEMPLATES_MODULE'),
      },
      {
        value: 'plugins',
        name: Joomla.Text._('COM_TEMPLATES_PLUGIN'),
      },
    ];

    this.firstSelector = this.querySelector('#extension-type-selector');
    this.secondSelector = this.querySelector('#extension-selector');
    this.thirdSelector = this.querySelector('#layout-selector');
    this.switcher = this.querySelector('#override_creator_name');
    this.switcherRadios = [].slice.call(this.switcher.querySelectorAll('input[name="override_creator_named"]'));
    this.creatorNameInput = this.querySelector('#override_creator_name_input');
    this.button = this.querySelector('button');
    elements.forEach((element) => this.appendElement(element, this.firstSelector));

    this.firstSelector.addEventListener('change', (e) => {
      const { value } = e.target;
      if (value === '') {
        this.secondSelector.closest('.control-group').setAttribute('hidden', '');
        this.secondSelector.innerHTML = '';
        this.thirdSelector.closest('.control-group').setAttribute('hidden', '');
        this.thirdSelector.innerHTML = '';
        this.switcher.closest('.control-group').setAttribute('hidden', '');
        this.creatorNameInput.closest('.control-group').setAttribute('hidden', '');
        this.button.closest('.control-group').setAttribute('hidden', '');
      }
      if (['components', 'layouts', 'modules', 'plugins'].includes(value)) {
        this.secondSelector.innerHTML = '';
        this.thirdSelector.innerHTML = '';
        this.thirdSelector.closest('.control-group').setAttribute('hidden', '');
        this.switcher.closest('.control-group').setAttribute('hidden', '');
        this.creatorNameInput.closest('.control-group').setAttribute('hidden', '');
        this.button.closest('.control-group').setAttribute('hidden', '');
        const elementsFirst = [{
          name: Joomla.Text._('COM_TEMPLATES_SELECT_OPTION_NONE'),
          value: '',
        }];
        Object.keys(this.data[value]).forEach((key) => { elementsFirst.push({ name: key, value: key }); });
        elementsFirst.forEach((element) => this.appendElement(element, this.secondSelector));
        this.secondSelector.closest('.control-group').removeAttribute('hidden');
      }
    });

    this.secondSelector.addEventListener('change', (e) => {
      const { value } = e.target;
      if (value === '') {
        this.thirdSelector.closest('.control-group').setAttribute('hidden', '');
        this.thirdSelector.innerHTML = '';
      }
      if (value !== '') {
        this.thirdSelector.innerHTML = '';
        const elementsSecond = [{
          name: Joomla.Text._('COM_TEMPLATES_SELECT_OPTION_NONE'),
          value: '',
        }];

        Object.keys(this.data[`${this.firstSelector.value}`][this.secondSelector.value])
          .forEach((key) => { elementsSecond.push({ name: this.data[`${this.firstSelector.value}`][this.secondSelector.value][key].name, value: this.data[`${this.firstSelector.value}`][this.secondSelector.value][key].path }); });

        elementsSecond.forEach((element) => this.appendElement(element, this.thirdSelector));
        this.thirdSelector.closest('.control-group').removeAttribute('hidden');
      }
    });

    this.thirdSelector.addEventListener('change', (e) => {
      const { value } = e.target;
      if (value === '') {
        this.createBtn.setAttribute('disabled', '');
        this.button.closest('.control-group').setAttribute('hidden', '');
      } else {
        this.switcher.closest('.control-group').removeAttribute('hidden');
        this.createBtn.removeAttribute('disabled');
        this.button.closest('.control-group').removeAttribute('hidden');
      }
    });
    this.switcherRadios[0].addEventListener('click', () => {
      if (this.switcherRadios[0].checked) {
        this.creatorNameInput.closest('.control-group').setAttribute('hidden', '');
      } else {
        this.creatorNameInput.closest('.control-group').removeAttribute('hidden');
      }
    });
    this.switcherRadios[1].addEventListener('click', () => {
      if (!this.switcherRadios[1].checked) {
        this.creatorNameInput.closest('.control-group').setAttribute('hidden', '');
      } else {
        this.creatorNameInput.closest('.control-group').removeAttribute('hidden');
      }
    });

    this.button.addEventListener('click', async () => {
      // @todo Submit the data
      const response = await fetch(this.url, this.fetchOptions);
      const data = await response.json();
      if (data.success) {
        const modal = this.closest('.modal-template');
        bootstrap.Modal.getInstance(modal).toggle();
      } else {
        Joomla.renderMessages({ error: [data.message] }, this, true, 5000);
      }
    });
  }

  // eslint-disable-next-line class-methods-use-this
  appendElement(element, parent) {
    const el = document.createElement('option');
    el.value = element.value;
    el.innerText = element.name;
    parent.appendChild(el);
  }
}

customElements.define('create-overrides', CreateOverrides);
