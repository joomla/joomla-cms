/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! form related functions
 *
 * @since  4.0.0
 */
((window, Joomla) => {
  'use strict';

  /**
   * Generic submit form
   *
   * @param  {String}  task      The given task
   * @param  {node}    form      The form element
   * @param  {bool}    validate  The form element
   *
   * @returns  {void}
   */
  Joomla.submitform = (task, form, validate) => {
    let newForm = form;
    const newTask = task;

    if (!newForm) {
      newForm = document.getElementById('adminForm');
    }

    if (newTask) {
      newForm.task.value = newTask;
    }

    // Toggle HTML5 validation
    newForm.noValidate = !validate;

    if (!validate) {
      newForm.setAttribute('novalidate', '');
    } else if (newForm.hasAttribute('novalidate')) {
      newForm.removeAttribute('novalidate');
    }

    // Submit the form.
    // Create the input type="submit"
    const button = document.createElement('input');
    button.classList.add('hidden');
    button.type = 'submit';

    // Append it and click it
    newForm.appendChild(button).click();

    // If "submit" was prevented, make sure we don't get a build up of buttons
    newForm.removeChild(button);
  };

  /**
   * Default function. Can be overridden by the component to add custom logic
   *
   * @param  {String}  task            The given task
   * @param  {String}  formSelector    The form selector eg '#adminForm'
   * @param  {bool}    validate        The form element
   *
   * @returns {void}
   */
  Joomla.submitbutton = (task, formSelector, validate) => {
    let form = document.querySelector(formSelector || 'form.form-validate');
    let newValidate = validate;

    if (typeof formSelector === 'string' && form === null) {
      form = document.querySelector(`#${formSelector}`);
    }

    if (form) {
      if (newValidate === undefined || newValidate === null) {
        const pressbutton = task.split('.');
        let cancelTask = form.getAttribute('data-cancel-task');

        if (!cancelTask) {
          cancelTask = `${pressbutton[0]}.cancel`;
        }

        newValidate = task !== cancelTask;
      }

      if (!newValidate || document.formvalidator.isValid(form)) {
        Joomla.submitform(task, form);
      }
    } else {
      Joomla.submitform(task);
    }
  };


  /**
   * USED IN: all list forms.
   *
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
      Joomla.Event.dispatch(checkbox.form.boxchecked, 'change');
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

    Joomla.Event.dispatch(newForm.boxchecked, 'change');

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
})(window, Joomla);
