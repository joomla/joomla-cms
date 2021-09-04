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
  const { currentTarget: button } = event;
  button.setAttribute('disabled', '');
  const { form } = button;
  const hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'cid[]');
  hiddenInput.setAttribute('value', button.dataset.item);
  form.appendChild(hiddenInput);
  // if (button.dataset.task === 'templates.createChild')
  const task = form.querySelector('[name="task"]');
  task.value = button.dataset.task;
  form.submit();
};

const appendElement = (element, elementSelector) => {
  const title = (element.charAt(0).toUpperCase() + element.slice(1)).slice(0, -1);
  const value = element.slice(0, -1);
  const el = document.createElement('option');
  el.value = value;
  el.innerText = title;
  elementSelector.appendChild(el);
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
  const elements = ['components', 'layouts', 'modules', 'plugins'];
  const elementSelector = document.createElement('select');
  elements.map((element) => appendElement(element, elementSelector));

  template.querySelector('.modal-body').appendChild(elementSelector);

  document.body.appendChild(template);

  const modal = new bootstrap.Modal(document.querySelector('.modal-template'));
  modal.toggle();
  const response = await fetch(`index.php?option=com_templates&task=${button.dataset.task}&id=${button.dataset.item}&${button.dataset.token}=1`,
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
};
const actionButtons = [].slice.call(document.querySelectorAll('button.js-action-exec'));
const actionButtonsNew = [].slice.call(document.querySelectorAll('button.js-action-exec-new'));
const actionButtonsModal = [].slice.call(document.querySelectorAll('button.js-action-exec-modal'));

actionButtons.map((button) => button.addEventListener('click', onClick));
actionButtonsNew.map((button) => button.addEventListener('click', onClickNew));
actionButtonsModal.map((button) => button.addEventListener('click', onClickModal));
