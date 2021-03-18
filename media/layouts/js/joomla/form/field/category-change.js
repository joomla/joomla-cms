/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (Joomla) {
  var id = Joomla.getOptions('category-change');
  var element = document.querySelector("#".concat(id));

  if (!element) {
    throw new Error('Category Id element not found');
  }

  if (element.getAttribute('data-refresh-catid') && element.value !== element.getAttribute('data-cat-id')) {
    element.value = element.getAttribute('data-refresh-catid');
  } else {
    // No custom fields
    element.setAttribute('data-refresh-catid', element.value);
  }

  window.Joomla.categoryHasChanged = function (el) {
    if (el.value === el.getAttribute('data-refresh-catid')) {
      return;
    }

    document.body.appendChild(document.createElement('joomla-core-loader')); // Custom Fields

    if (el.getAttribute('data-refresh-section')) {
      document.querySelector('input[name=task]').value = "".concat(el.getAttribute('data-refresh-section'), ".reload");
    }

    Joomla.submitform("".concat(el.getAttribute('data-refresh-section'), ".reload"), element.form);
  };
})(Joomla);