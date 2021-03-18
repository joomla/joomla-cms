/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (Joomla, document) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var targetAssociation = window.parent.document.getElementById('target-association');
    var links = [].slice.call(document.querySelectorAll('.select-link'));
    links.forEach(function (item) {
      item.addEventListener('click', function (_ref) {
        var target = _ref.target;
        targetAssociation.src = "".concat(targetAssociation.getAttribute('data-editurl'), "&task=").concat(targetAssociation.getAttribute('data-item'), ".edit&id=").concat(parseInt(target.getAttribute('data-id'), 10));
        window.parent.Joomla.Modal.getCurrent().close();
      });
    });
  });
})(Joomla, document);