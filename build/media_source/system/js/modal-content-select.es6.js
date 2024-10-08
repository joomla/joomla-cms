/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A helper to Post a Message
 * @param {Object} data
 */
const send = (data) => {
  // Set the message type and send it
  data.messageType = data.messageType || 'joomla:content-select';
  window.parent.postMessage(data);
};

// Bind the buttons
document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-content-select]');
  if (!button) return;
  event.preventDefault();

  // Extract the data and send
  const data = { ...button.dataset };
  delete data.contentSelect;
  send(data);
});

// Check for "select on load"
window.addEventListener('load', () => {
  const data = Joomla.getOptions('content-select-on-load');
  if (data) {
    send(data);
  }
});
