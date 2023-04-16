/*
 * Apply the predefined action for the current element
 *
 * @param {Event} event
 */
function gridItemAction(event) {
  let item = event.target;

  if (item.nodeName === 'SPAN' && ['A', 'BUTTON'].includes(item.parentNode.nodeName)) {
    item = item.parentNode;
  }

  if (item.nodeName === 'A') {
    event.preventDefault();
  }

  if (item.hasAttribute('disabled') || !item.hasAttribute('data-item-task')) {
    return;
  }

  const { itemId } = item.dataset;
  const { itemTask } = item.dataset;
  const { itemFormId } = item.dataset;

  if (itemFormId) {
    Joomla.listItemTask(itemId, itemTask, itemFormId);
  } else {
    Joomla.listItemTask(itemId, itemTask);
  }
}

/*
 * Apply the transition state for the current element
 *
 * @param {Event} event
 */
function gridTransitionItemAction(event) {
  const item = event.target;

  if (item.nodeName !== 'SELECT' || item.hasAttribute('disabled')) {
    return;
  }

  const { itemId } = item.dataset;
  const { itemTask } = item.dataset;
  const { itemFormId } = item.dataset;

  item.form.transition_id.value = item.value;

  if (itemFormId) {
    Joomla.listItemTask(itemId, itemTask, itemFormId);
  } else {
    Joomla.listItemTask(itemId, itemTask);
  }
}

/*
 * Apply the transition state for the current element
 *
 * @param {Event} event
 */
function gridTransitionButtonAction(event) {
  let item = event.target;

  if (item.nodeName === 'SPAN' && item.parentNode.nodeName === 'BUTTON') {
    item = item.parentNode;
  }

  if (item.hasAttribute('disabled')) {
    return;
  }

  Joomla.toggleAllNextElements(item, 'd-none');
}

/*
 * Switch the check state for the current element
 *
 * @param {Event} event
 */
function applyIsChecked(event) {
  const item = event.target;
  const itemFormId = item.dataset.itemFormId || '';

  if (itemFormId) {
    Joomla.isChecked(item.checked, itemFormId);
  } else {
    Joomla.isChecked(item.checked);
  }
}

document.querySelectorAll('.js-grid-item-check-all').forEach((element) => element.addEventListener('click', (event) => Joomla.checkAll(event.target)));
document.querySelectorAll('.js-grid-item-is-checked').forEach((element) => element.addEventListener('click', applyIsChecked));
document.querySelectorAll('.js-grid-item-action').forEach((element) => element.addEventListener('click', gridItemAction));
document.querySelectorAll('.js-grid-item-transition-action').forEach((element) => element.addEventListener('change', gridTransitionItemAction));
document.querySelectorAll('.js-grid-button-transition-action').forEach((element) => element.addEventListener('click', gridTransitionButtonAction));
