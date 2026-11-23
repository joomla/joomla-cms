/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document, Joomla) => {
  'use strict';

  const copyToClipboardFallback = (input) => {
    input.focus();
    input.select();

    try {
      const copy = document.execCommand('copy');
      if (copy) {
        Joomla.renderMessages({ message: [Joomla.Text._('PLG_USER_TOKEN_COPY_SUCCESS')] });
      } else {
        Joomla.renderMessages({ error: [Joomla.Text._('PLG_USER_TOKEN_COPY_FAIL')] });
      }
    } catch (err) {
      Joomla.renderMessages({ error: [err] });
    }
  };

  const copyToClipboard = () => {
    const button = document.getElementById('token-copy');

    button.addEventListener('click', ({ currentTarget }) => {
      const input = currentTarget.previousElementSibling;

      if (!navigator.clipboard) {
        copyToClipboardFallback(input);
        return;
      }

      navigator.clipboard.writeText(input.value).then(() => {
        Joomla.renderMessages({ message: [Joomla.Text._('PLG_USER_TOKEN_COPY_SUCCESS')] });
      }, () => {
        Joomla.renderMessages({ error: [Joomla.Text._('PLG_USER_TOKEN_COPY_FAIL')] });
      });
    });
  };

  const onBoot = () => {
    copyToClipboard();

    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(document, Joomla);
