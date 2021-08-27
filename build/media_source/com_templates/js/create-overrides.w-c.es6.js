class CreateOverrides extends HTMLElement {
  constructor() {
    super();

    this.elementSelector = document.createElement('select');
    this.elementSelector.classList.add('form-select');

    // class="form-select" aria-label="Default select example"
  }

  connectedCallback() {
    this.task = this.getAttribute('task');
    this.client = this.getAttribute('client');
    this.token = this.getAttribute('token');
    this.createOverrides();
  }

  disconnectedCallback() {

  }

  attributeChangedCallback(attrName, oldVal, newVal) {

  }

  async createOverrides() {
    const elements = [
      {
        value: '',
        name: Joomla.Text._('COM_TEMPLATES_SELECT_TYPE_OF_OVERRIDE'),
      },
      {
        value: 'components',
        name: Joomla.Text._('COM_TEMPLATES_COMPONENT'),
      },
      {
        value: 'layouts',
        name: Joomla.Text._('COM_TEMPLATES_LAYOUTS'),
      },
      {
        value: 'modules',
        name: Joomla.Text._('COM_TEMPLATES_MODULES'),
      },
      {
        value: 'plugins',
        name: Joomla.Text._('COM_TEMPLATES_PLUGINS'),
      },
    ];

    elements.map((element) => this.appendElement(element));

    this.appendChild(this.elementSelector);

    console.log({
      task: this.task,
      client: this.client,
      token: this.token,
    });
    const url = new URL(`${Joomla.getOptions('system.paths').baseFull}index.php?option=com_templates`);
    url.searchParams.append('task', this.task);
    url.searchParams.append('client', this.client);
    url.searchParams.append('token', this.token);
    url.searchParams.append('id', this.item);

    //&task=${this.task}&client=${this.client}&token=${this.token}&format=json
    console.log(url);
    const response = await fetch(url,
      {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          // 'Content-Type': 'application/x-www-form-urlencoded',
        },
        // body: JSON.stringify({
        //   option: 'com_templates',
        //   task: button.dataset.task,
        //   id: button.dataset.item,
        //
        // })
    });

    const data = await response.json();
    console.log(await data);
  }

  appendElement(element) {
    const el = document.createElement('option');
    el.value = element.value;
    el.innerText = element.name;
    this.elementSelector.appendChild(el);
  }
}

customElements.define('create-overrides', CreateOverrides);
