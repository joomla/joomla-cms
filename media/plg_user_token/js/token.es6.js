/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, Joomla) => {
  'use strict';

  const copyToClipboardFallback = input => {
    input.focus();
    input.select();

    try {
      const copy = document.execCommand('copy');

      if (copy) {
        Joomla.renderMessages({
          message: [Joomla.JText._('PLG_USER_TOKEN_COPY_SUCCESS')]
        });
      } else {
        Joomla.renderMessages({
          error: [Joomla.JText._('PLG_USER_TOKEN_COPY_FAIL')]
        });
      }
    } catch (err) {
      Joomla.renderMessages({
        error: [err]
      });
    }
  };

  const copyToClipboard = () => {
    const button = document.getElementById('token-copy');
    button.addEventListener('click', ({
      currentTarget
    }) => {
      const input = currentTarget.parentNode.previousElementSibling;

      if (!navigator.clipboard) {
        copyToClipboardFallback(input);
        return;
      }

      navigator.clipboard.writeText(input.value).then(() => {
        Joomla.renderMessages({
          message: [Joomla.JText._('PLG_USER_TOKEN_COPY_SUCCESS')]
        });
      }, () => {
        Joomla.renderMessages({
          error: [Joomla.JText._('PLG_USER_TOKEN_COPY_FAIL')]
        });
      });
    });
  };

  const onBoot = () => {
    copyToClipboard();
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(document, Joomla);