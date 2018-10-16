/**
 * PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
 * OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
 **/

/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field switcher
 */
(function (document) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    [].slice.call(document.querySelectorAll('input[type="password"]')).forEach(function (input) {
      var inputGroup = input.parentNode;

      if (!inputGroup) {
        return;
      }

      inputGroup.querySelector('.input-group-prepend, .input-group-append').addEventListener('click', function (e) {
        var target = e.target;
        var srText = target.previousSibling;

        if (target.classList.contains('icon-eye')) {
          // Update the icon class
          target.classList.remove('icon-eye');
          target.classList.add('icon-eye-close');

          // Update the input type
          input.type = 'text';

          // Update the text for screenreaders
          srText.innerText = Joomla.JText._('JSHOW');
        } else {
          // Update the icon class
          target.classList.add('icon-eye');
          target.classList.remove('icon-eye-close');

          // Update the input type
          input.type = 'password';

          // Update the text for screenreaders
          srText.innerText = Joomla.JText._('JHIDE');
        }
      });
    });
  });
})(document);
