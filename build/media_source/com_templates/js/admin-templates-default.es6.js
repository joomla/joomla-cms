/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
const onClick = async (event) => {
  const { currentTarget: button } = event;
  button.setAttribute('disabled', '');
  const { form } = button;
  const hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'cid[]');
  hiddenInput.setAttribute('value', button.dataset.item);
  form.appendChild(hiddenInput);
  const task = form.querySelector('[name="task"]');
  task.value = button.dataset.task;
  form.submit();
};

const onClickNew = async (event) => {
  onclick(event);
};

const onClickModal = async (event) => {
  const { currentTarget: button } = event;
  const templateEl = document.querySelector('#modal-template');

  if (!templateEl) {
    throw new Error('The Modal template is missing, check your HTML markup');
  }

  button.setAttribute('disabled', '');

  const template = templateEl.content.cloneNode(true);

  // Create the Override Modal
  if (button.dataset.override) {
    template.querySelector('.modal-title').innerText = Joomla.Text._('COM_TEMPLATES_CREATE_OVERRIDE');
    const overrideCreator = document.createElement('create-overrides');
    overrideCreator.setAttribute('task', button.dataset.task);
    overrideCreator.setAttribute('client', button.dataset.client);
    overrideCreator.setAttribute('token', button.dataset.token);
    overrideCreator.setAttribute('item', button.dataset.item);
    overrideCreator.classList.add('p-4');
    template.querySelector('.modal-body').appendChild(overrideCreator);
  }

  // Create the Template child/fork Modal
  if (button.dataset.createNew) {
    // @todo title according to modern template, eg fork or child
    template.querySelector('.modal-title').innerText = Joomla.Text._('COM_TEMPLATES_CREATE_OVERRIDE');
    const createNewTemplate = document.createElement('create-fork-child');
    createNewTemplate.setAttribute('task', button.dataset.task);
    createNewTemplate.setAttribute('client', button.dataset.client);
    createNewTemplate.setAttribute('token', button.dataset.token);
    createNewTemplate.setAttribute('item', button.dataset.item);
    createNewTemplate.classList.add('p-4');
    template.querySelector('.modal-body').appendChild(createNewTemplate);
  }

  // Create the Template description Modal
  if (button.dataset.infoTask) {
    template.querySelector('.modal-title').innerText = button.dataset.name;
    const conainer = document.createElement('div');
    conainer.classList.add('p-4');

    const url = new URL(`${Joomla.getOptions('system.paths').baseFull}index.php?option=com_templates`);
    url.searchParams.append('task', button.dataset.infoTask);
    url.searchParams.append('client', button.dataset.client);
    url.searchParams.append(button.dataset.token, 1);
    url.searchParams.append('id', button.dataset.id);
    const options = {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    };
    const response = await fetch(url, options);
    // Unsafe to use innerHTML here, as it can be XSS-attacked
    conainer.innerHTML = (await response.json()).data;
    const modalBody = template.querySelector('.modal-body');
    modalBody.classList.add('jviewport-height80');
    modalBody.closest('.modal-dialog').classList.add('jviewport-width80');
    modalBody.appendChild(conainer);
  }

  document.body.appendChild(template);
  const modalElement = document.querySelector('.modal-template');

  const modal = new bootstrap.Modal(modalElement, { backdrop: 'static' });
  modal.toggle();
  modalElement.addEventListener('hidden.bs.modal', () => {
    document.body.removeChild(modalElement);
    button.removeAttribute('disabled');
  });
};

// Add the Interactivity
[].slice.call(document.querySelectorAll('button.js-action-exec')).map((button) => button.addEventListener('click', onClick));
[].slice.call(document.querySelectorAll('button.js-action-exec-new')).map((button) => button.addEventListener('click', onClickNew));
[].slice.call(document.querySelectorAll('button.js-action-exec-modal')).map((button) => button.addEventListener('click', onClickModal));
