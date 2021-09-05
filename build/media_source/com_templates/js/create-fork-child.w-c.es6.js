class CreateForkOrChild extends HTMLElement {
  constructor() {
    super();

    this.createForkTask = 'templates.getExistingLayouts';
    this.createChildTask = 'templates.createLayout';
    this.fetchOptions = {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    };

    this.html = `<div class="ms-4 me-4">
    <div class="control-group">
      <div class="control-label">
        <label for="fork_child_name_input">${this.isModern ? Joomla.Text._('COM_TEMPLATES_CREATE_CHILD_LABEL') : Joomla.Text._('COM_TEMPLATES_CREATE_FORK_LABEL')}</label>
      </div>
      <div class="controls has-success">
        <input type="text" id="fork_child_name_input" value="" class="form-control" size="40" maxlength="255">
      </div>
    </div>
    <div class="control-group">
      <button type="button" class="btn btn-success w-100" disabled></button>
    </div>
  </div>`;

    this.data = {};

    this.onInputEvent = this.onInputEvent.bind(this);
    this.onClickEvent = this.onClickEvent.bind(this);
  }

  connectedCallback() {
    this.client = this.getAttribute('client');
    this.token = this.getAttribute('token');
    this.item = this.getAttribute('item');
    this.isModern = this.getAttribute('isModern') === 'true';
    this.innerHTML = this.html;
    this.button = this.querySelector('button');
    this.input = this.querySelector('#fork_child_name_input');

    this.button.innerText = this.isModern ? Joomla.Text._('COM_TEMPLATES_CREATE_CHILD_LABEL') : Joomla.Text._('COM_TEMPLATES_CREATE_FORK_LABEL');

    this.button.addEventListener('click', this.onClickEvent);
    this.input.addEventListener('input', this.onInputEvent);
  }

  disconnectedCallback() {
    if (this.button) {
      this.button.removeEventListener('click', this.onClickEvent);
    }
    this.innerHTML = '';
  }

  async onClickEvent() {
    const url = new URL(`${Joomla.getOptions('system.paths').baseFull}index.php?option=com_templates`);

    url.searchParams.append('client', this.client);
    url.searchParams.append(this.getAttribute('token'), 1);
    url.searchParams.append('id', this.item);

    // @todo Pass the right params
    // url.searchParams.append('task', this.createLayoutTask);

    window.location = url.href;
  }

  onInputEvent(event) {
    const { value } = event.target;
    // @todo use better validation
    if (value.length) {
      this.button.removeAttribute('disabled');
    } else {
      this.button.setAttribute('disabled', '');
    }
  }
}

customElements.define('create-fork-child', CreateForkOrChild);
