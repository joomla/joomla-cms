/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised!');
}
const copyToClipboardFallback = input => {
  input.focus();
  input.select();
  try {
    const copy = document.execCommand('copy');
    if (copy) {
      Joomla.renderMessages({
        message: [Joomla.Text._('COM_SCHEDULER_CONFIG_WEBCRON_LINK_COPY_SUCCESS')]
      });
    } else {
      Joomla.renderMessages({
        error: [Joomla.Text._('COM_SCHEDULER_CONFIG_WEBCRON_LINK_COPY_FAIL')]
      });
    }
  } catch (err) {
    Joomla.renderMessages({
      error: [err]
    });
  }
};
const copyToClipboard = () => {
  const button = document.getElementById('link-copy');
  button.addEventListener('click', ({
    currentTarget
  }) => {
    const input = currentTarget.previousElementSibling;
    if (!navigator.clipboard) {
      copyToClipboardFallback(input);
      return;
    }
    navigator.clipboard.writeText(input.value).then(() => {
      Joomla.renderMessages({
        message: [Joomla.Text._('COM_SCHEDULER_CONFIG_WEBCRON_LINK_COPY_SUCCESS')]
      });
    }, () => {
      Joomla.renderMessages({
        error: [Joomla.Text._('COM_SCHEDULER_CONFIG_WEBCRON_LINK_COPY_FAIL')]
      });
    });
  });
};
const onBoot = () => {
  copyToClipboard();
  document.removeEventListener('DOMContentLoaded', onBoot);
};
document.addEventListener('DOMContentLoaded', onBoot);
