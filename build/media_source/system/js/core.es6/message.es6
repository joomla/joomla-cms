/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Message related functions
 *
 * @since  4.0.0
 */
((window, Joomla) => {
  'use strict';

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
    let messageContainer;
    let typeMessages;
    let messagesBox;
    let title;
    let titleWrapper;
    let messageWrapper;
    let alertClass;

    if (typeof selector === 'undefined' || (selector && selector === '#system-message-container')) {
      messageContainer = document.getElementById('system-message-container');
    } else {
      messageContainer = document.querySelector(selector);
    }

    if (typeof keepOld === 'undefined' || (keepOld && keepOld === false)) {
      Joomla.removeMessages(messageContainer);
    }

    [].slice.call(Object.keys(messages)).forEach((type) => {
      // Array of messages of this type
      typeMessages = messages[type];
      messagesBox = document.createElement('joomla-alert');

      if (['notice', 'message', 'error', 'warning'].indexOf(type) > -1) {
        alertClass = (type === 'notice') ? 'info' : type;
        alertClass = (type === 'message') ? 'success' : alertClass;
        alertClass = (type === 'error') ? 'danger' : alertClass;
        alertClass = (type === 'warning') ? 'warning' : alertClass;
      } else {
        alertClass = 'info';
      }

      messagesBox.setAttribute('type', alertClass);
      messagesBox.setAttribute('dismiss', 'true');

      if (timeout && parseInt(timeout, 10) > 0) {
        messagesBox.setAttribute('autodismiss', timeout);
      }

      // Title
      title = Joomla.Text._(type);

      // Skip titles with untranslated strings
      if (typeof title !== 'undefined') {
        titleWrapper = document.createElement('span');
        titleWrapper.className = 'alert-heading';
        titleWrapper.innerHTML = Joomla.Text._(type) ? Joomla.Text._(type) : type;
        messagesBox.appendChild(titleWrapper);
      }

      // Add messages to the message box
      typeMessages.forEach((typeMessage) => {
        messageWrapper = document.createElement('div');
        messageWrapper.innerHTML = typeMessage;
        messagesBox.appendChild(messageWrapper);
      });

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
    let messageContainer;

    if (container) {
      messageContainer = container;
    } else {
      messageContainer = document.getElementById('system-message-container');
    }

    const alerts = [].slice.call(messageContainer.querySelectorAll('joomla-alert'));
    if (alerts.length) {
      alerts.forEach((alert) => {
        alert.close();
      });
    }
  };

  /**
   * Treat AJAX errors.
   * Used by some javascripts such as sendtestmail.js and permissions.js
   *
   * @param   {object}  xhr         XHR object.
   * @param   {string}  textStatus  Type of error that occurred.
   * @param   {string}  error       Textual portion of the HTTP status.
   *
   * @return  {object}  JavaScript object containing the system error message.
   *
   * @since  3.6.0
   */
  Joomla.ajaxErrorsMessages = (xhr, textStatus) => {
    const msg = {};


    if (textStatus === 'parsererror') {
      // For jQuery jqXHR
      const buf = [];

      // Html entity encode.
      let encodedJson = xhr.responseText.trim();


      // eslint-disable-next-line no-plusplus
      for (let i = encodedJson.length - 1; i >= 0; i--) {
        buf.unshift(['&#', encodedJson[i].charCodeAt(), ';'].join(''));
      }

      encodedJson = buf.join('');

      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_PARSE').replace('%s', encodedJson)];
    } else if (textStatus === 'nocontent') {
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_NO_CONTENT')];
    } else if (textStatus === 'timeout') {
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_TIMEOUT')];
    } else if (textStatus === 'abort') {
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT')];
    } else if (xhr.responseJSON && xhr.responseJSON.message) {
      // For vanilla XHR
      msg.error = [`${Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status)} <em>${xhr.responseJSON.message}</em>`];
    } else if (xhr.statusText) {
      msg.error = [`${Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status)} <em>${xhr.statusText}</em>`];
    } else {
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status)];
    }

    return msg;
  };
})(window, Joomla);
