/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  // Shortcuts
  const $ = document.querySelector.bind(document);
  const $$ = document.querySelectorAll.bind(document);

  // Get a reference of all the elements needed
  const defaultButton = $('#style-default');
  const createChildButton = $('#create-child');
  const overrideChildButton = $('#override-child');

  const duplicateButton = $('#toolbar-copy > button');
  const deleteButton = $('#toolbar-delete > button');
  const stylesElements = [].slice.call($$('input[name="cid[]"]'));

  if (!defaultButton || !createChildButton || !overrideChildButton || !duplicateButton || !deleteButton || stylesElements.count) {
    throw new Error('Some elements are missing, bailing out');
  }

  const manageButtons = (event) => {
    const element = event.currentTarget;

    // Enable Default button
    if (element.dataset.isHome !== 'true') {
      defaultButton.removeAttribute('disabled');
    } else {
      defaultButton.setAttribute('disabled', '');
    }

    // Enable Duplicate as chid button
    if (element.dataset.isParent === 'true') {
      createChildButton.removeAttribute('disabled');
    } else {
      createChildButton.setAttribute('disabled', '');
    }

    // Enable Manage child overrides button
    if (element.dataset.isChild === 'true') {
      overrideChildButton.removeAttribute('disabled');
    } else {
      overrideChildButton.setAttribute('disabled', '');
    }
  };

  stylesElements.forEach((element) => {
    element.addEventListener('click', manageButtons);
  });
})();
