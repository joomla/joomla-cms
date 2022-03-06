/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Import the Alert Custom Element
import 'joomla-ui-custom-elements/src/js/alert/alert.js';

/**
 * Returns the container of the Messages
 *
 * @param {string|HTMLElement}  container  The container
 *
 * @returns {HTMLElement}
 */
const getMessageContainer = (container) => {
  let messageContainer;

  if (container instanceof HTMLElement) {
    return container;
  }
  if (typeof container === 'undefined' || (container && container === '#system-message-container')) {
    messageContainer = document.getElementById('system-message-container');
  } else {
    messageContainer = document.querySelector(container);
  }

  return messageContainer;
};

/**
 * Render messages send via JSON
 * Used by some javascripts such as validate.js
 *
 * @param   {object}  messages JavaScript object containing the messages to render.
 *          Example:
 *          const messages = {
 *              "message": ["This will be a green message", "So will this"],
 *              "error": ["This will be a red message", "So will this"],
 *              "info": ["This will be a blue message", "So will this"],
 *              "notice": ["This will be same as info message", "So will this"],
 *              "warning": ["This will be a orange message", "So will this"],
 *              "my_custom_type": ["This will be same as info message", "So will this"]
 *          };
 * @param  {string} selector The selector of the container where the message will be rendered
 * @param  {bool}   keepOld  If we shall discard old messages
 * @param  {int}    timeout  The milliseconds before the message self destruct
 * @return  void
 */
Joomla.renderMessages = (messages, selector, keepOld, timeout) => {
  const messageContainer = getMessageContainer(selector);
  if (typeof keepOld === 'undefined' || (keepOld && keepOld === false)) {
    Joomla.removeMessages(messageContainer);
  }

  [].slice.call(Object.keys(messages)).forEach((type) => {
    let alertClass = type;

    // Array of messages of this type
    const typeMessages = messages[type];
    const messagesBox = document.createElement('joomla-alert');

    if (['success', 'info', 'danger', 'warning'].indexOf(type) < 0) {
      alertClass = (type === 'notice') ? 'info' : type;
      alertClass = (type === 'message') ? 'success' : alertClass;
      alertClass = (type === 'error') ? 'danger' : alertClass;
      alertClass = (type === 'warning') ? 'warning' : alertClass;
    }

    messagesBox.setAttribute('type', alertClass);
    messagesBox.setAttribute('close-text', Joomla.Text._('JCLOSE'));
    messagesBox.setAttribute('dismiss', true);

    if (timeout && parseInt(timeout, 10) > 0) {
      messagesBox.setAttribute('auto-dismiss', timeout);
    }

    // Title
    const title = Joomla.Text._(type);

    // Skip titles with untranslated strings
    if (typeof title !== 'undefined') {
      const titleWrapper = document.createElement('div');
      titleWrapper.className = 'alert-heading';
      titleWrapper.innerHTML = Joomla.sanitizeHtml(`<span class="${type}"></span><span class="visually-hidden">${Joomla.Text._(type) ? Joomla.Text._(type) : type}</span>`);
      messagesBox.appendChild(titleWrapper);
    }

    // Add messages to the message box
    const messageWrapper = document.createElement('div');
    messageWrapper.className = 'alert-wrapper';
    typeMessages.forEach((typeMessage) => {
      messageWrapper.innerHTML += Joomla.sanitizeHtml(`<div class="alert-message">${typeMessage}</div>`);
    });
    messagesBox.appendChild(messageWrapper);
    messageContainer.appendChild(messagesBox);
  });
};

/**
 * Remove messages
 *
 * @param  {element} container The element of the container of the message
 * to be removed
 *
 * @return  {void}
 */
Joomla.removeMessages = (container) => {
  const messageContainer = getMessageContainer(container);
  const alerts = [].slice.call(messageContainer.querySelectorAll('joomla-alert'));
  if (alerts.length) {
    alerts.forEach((alert) => {
      alert.close();
    });
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const messages = Joomla.getOptions('joomla.messages');
  if (messages) {
    Object.keys(messages)
      .map((message) => Joomla.renderMessages(messages[message], undefined, true, undefined));
  }
});
