/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    [].slice.call(document.querySelectorAll('input[type="password"]')).forEach(function (input) {
      var toggleButton = input.parentNode.querySelector('.input-password-toggle');

      if (!toggleButton) {
        return;
      }

      toggleButton.addEventListener('click', function () {
        var icon = toggleButton.firstElementChild;
        var srText = toggleButton.lastElementChild;

        if (input.type === 'password') {
          // Update the icon class
          icon.classList.remove('icon-eye');
          icon.classList.add('icon-eye-close'); // Update the input type

          input.type = 'text'; // Focus the input field

          input.focus(); // Update the text for screenreaders

          srText.innerText = Joomla.Text._('JHIDEPASSWORD');
        } else if (input.type === 'text') {
          // Update the icon class
          icon.classList.add('icon-eye');
          icon.classList.remove('icon-eye-close'); // Update the input type

          input.type = 'password'; // Update the text for screenreaders

          srText.innerText = Joomla.Text._('JSHOWPASSWORD');
        }
      });
    });
  });
})(document);