/**
 * Toggles the check state of a group of boxes
 *
 * Checkboxes must have an id attribute in the form cb0, cb1...
 *
 * @param {mixed}  checkbox The number of box to 'check', for a checkbox element
 * @param {string} stub     An alternative field name
 *
 * @return {boolean}
 */
Joomla.checkAll = (checkbox, stub) => {
  if (!checkbox.form) {
    return false;
  }

  const currentStab = stub || 'cb';
  const elements = [].slice.call(checkbox.form.elements);
  let state = 0;

  elements.forEach((element) => {
    if (element.type === checkbox.type && element.id.indexOf(currentStab) === 0) {
      element.checked = checkbox.checked;
      state += element.checked ? 1 : 0;
    }
  });

  if (checkbox.form.boxchecked) {
    checkbox.form.boxchecked.value = state;
    checkbox.form.boxchecked.dispatchEvent(new CustomEvent('change', {
      bubbles: true,
      cancelable: true,
    }));
  }

  return true;
};

/**
 * USED IN: administrator/components/com_cache/views/cache/tmpl/default.php
 * administrator/components/com_installer/views/discover/tmpl/default_item.php
 * administrator/components/com_installer/views/update/tmpl/default_item.php
 * administrator/components/com_languages/helpers/html/languages.php
 * libraries/joomla/html/html/grid.php
 *
 * @param  {boolean}  isitchecked  Flag for checked
 * @param  {node}     form         The form
 *
 * @return  {void}
 */
Joomla.isChecked = (isitchecked, form) => {
  let newForm = form;
  if (typeof newForm === 'undefined') {
    newForm = document.getElementById('adminForm');
  } else if (typeof form === 'string') {
    newForm = document.getElementById(form);
  }

  newForm.boxchecked.value = isitchecked
    ? parseInt(newForm.boxchecked.value, 10) + 1
    : parseInt(newForm.boxchecked.value, 10) - 1;

  newForm.boxchecked.dispatchEvent(new CustomEvent('change', {
    bubbles: true,
    cancelable: true,
  }));

  // If we don't have a checkall-toggle, done.
  if (!newForm.elements['checkall-toggle']) {
    return;
  }

  // Toggle main toggle checkbox depending on checkbox selection
  let c = true;
  let i;
  let e;
  let n;

  // eslint-disable-next-line no-plusplus
  for (i = 0, n = newForm.elements.length; i < n; i++) {
    e = newForm.elements[i];

    if (e.type === 'checkbox' && e.name !== 'checkall-toggle' && !e.checked) {
      c = false;
      break;
    }
  }

  newForm.elements['checkall-toggle'].checked = c;
};

/**
 * USED IN: libraries/joomla/html/html/grid.php
 * In other words, on any reorderable table
 *
 * @param  {string}  order  The order value
 * @param  {string}  dir    The direction
 * @param  {string}  task   The task
 * @param  {node}    form   The form
 *
 * return  {void}
 */
Joomla.tableOrdering = (order, dir, task, form) => {
  let newForm = form;
  if (typeof newForm === 'undefined') {
    newForm = document.getElementById('adminForm');
  } else if (typeof form === 'string') {
    newForm = document.getElementById(form);
  }

  newForm.filter_order.value = order;
  newForm.filter_order_Dir.value = dir;
  Joomla.submitform(task, newForm);
};

/**
 * USED IN: all over :)
 *
 * @param  {string}  id    The id
 * @param  {string}  task  The task
 * @param  {string}  form  The optional form
 *
 * @return {boolean}
 */
Joomla.listItemTask = (id, task, form = null) => {
  let newForm = form;
  if (form !== null) {
    newForm = document.getElementById(form);
  } else {
    newForm = document.adminForm;
  }

  const cb = newForm[id];
  let i = 0;
  let cbx;

  if (!cb) {
    return false;
  }

  // eslint-disable-next-line no-constant-condition
  while (true) {
    cbx = newForm[`cb${i}`];

    if (!cbx) {
      break;
    }

    cbx.checked = false;

    i += 1;
  }

  cb.checked = true;
  newForm.boxchecked.value = 1;
  Joomla.submitform(task, newForm);

  return false;
};

function gridItemAction(event) {
  let item = event.target;

  if (item.nodeName === 'SPAN' && ['A', 'BUTTON'].includes(item.parentNode.nodeName)) {
    item = item.parentNode;
  }

  if (item.nodeName === 'A') {
    event.preventDefault();
  }

  if (item.hasAttribute('disabled')) {
    return;
  }

  const { itemId } = item.dataset;
  const { itemTask } = item.dataset;
  const { itemFormId } = item.dataset;

  if (itemFormId) {
    Joomla.listItemTask(itemId, itemTask);
  } else {
    Joomla.listItemTask(itemId, itemTask);
  }

  Joomla.submitform(itemTask, item.form);
}

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
    Joomla.listItemTask(itemId, itemTask);
  } else {
    Joomla.listItemTask(itemId, itemTask);
  }

  Joomla.submitform(itemTask, item.form);
}

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

function applyIsChecked(event) {
  const item = event.target;
  const itemFormId = item.dataset.itemFormId || '';

  if (itemFormId) {
    Joomla.isChecked(item.checked, itemFormId);
  } else {
    Joomla.isChecked(item.checked);
  }
}

document.querySelectorAll('.js-checkbox-check-all').forEach((element) => element.addEventListener('click', (event) => Joomla.checkAll(event.target)));
document.querySelectorAll('.js-checkbox-is-checked').forEach((element) => element.addEventListener('click', applyIsChecked));
document.querySelectorAll('.js-grid-item-action').forEach((element) => element.addEventListener('click', gridItemAction));
document.querySelectorAll('.js-grid-transition-item-action').forEach((element) => element.addEventListener('change', gridTransitionItemAction));
document.querySelectorAll('.js-grid-transition-button-action').forEach((element) => element.addEventListener('click', gridTransitionButtonAction));
