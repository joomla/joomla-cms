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

const actionButtons = [].slice.call(document.querySelectorAll('button.js-action-exec'));

actionButtons.map((button) => button.addEventListener('click', onClick));
