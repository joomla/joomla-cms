/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    [].slice.call(document.querySelectorAll('input[type="password"]')).forEach((input) => {
      const toggleButton = input.parentNode.querySelector('.input-password-toggle');

      if (!toggleButton) {
        return;
      }

      toggleButton.addEventListener('click', () => {
        const icon = toggleButton.firstElementChild;
        const srText = toggleButton.lastElementChild;

        if (input.type === 'password') {
          // Update the icon class
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');

          // Update the input type
          input.type = 'text';

          // Focus the input field
          input.focus();

          // Update the text for screenreaders
          srText.innerText = Joomla.Text._('JHIDEPASSWORD');
        } else if (input.type === 'text') {
          // Update the icon class
          icon.classList.add('fa-eye');
          icon.classList.remove('fa-eye-slash');

          // Update the input type
          input.type = 'password';

          // Update the text for screenreaders
          srText.innerText = Joomla.Text._('JSHOWPASSWORD');
        }
      });
    });
  });
})(document);
