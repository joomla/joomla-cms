/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const targetAssociation = window.parent.document.getElementById('target-association');
    const links = [].slice.call(document.querySelectorAll('.select-link'));
    links.forEach(item => {
      item.addEventListener('click', ({
        target
      }) => {
        targetAssociation.src = `${targetAssociation.getAttribute('data-editurl')}&task=${targetAssociation.getAttribute('data-item')}.edit&id=${parseInt(target.getAttribute('data-id'), 10)}`;
        window.parent.Joomla.Modal.getCurrent().close();
      });
    });
  });
})(Joomla, document);