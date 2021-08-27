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
  template.querySelector('.modal-title').innerText = Joomla.Text._('COM_TEMPLATES_CREATE_OVERRIDE');

  const overrideCreator = document.createElement('create-overrides');
  overrideCreator.setAttribute('task', button.dataset.task);
  overrideCreator.setAttribute('client', button.dataset.client);
  overrideCreator.setAttribute('token', button.dataset.token);
  overrideCreator.setAttribute('item', button.dataset.item);

  template.querySelector('.modal-body').appendChild(overrideCreator);
  template.querySelector('.btn.btn-primary').innerText = Joomla.Text._('COM_TEMPLATES_CREATE_OVERRIDE');

  document.body.appendChild(template);
  const modalElement = document.querySelector('.modal-template');

  const modal = new bootstrap.Modal(modalElement);
  modal.toggle();
  modalElement.addEventListener('hidden.bs.modal', () => {
    document.body.removeChild(modalElement);
    button.removeAttribute('disabled');
  });
};
const actionButtons = [].slice.call(document.querySelectorAll('button.js-action-exec'));
const actionButtonsNew = [].slice.call(document.querySelectorAll('button.js-action-exec-new'));
const actionButtonsModal = [].slice.call(document.querySelectorAll('button.js-action-exec-modal'));

actionButtons.map((button) => button.addEventListener('click', onClick));
actionButtonsNew.map((button) => button.addEventListener('click', onClickNew));
actionButtonsModal.map((button) => button.addEventListener('click', onClickModal));
