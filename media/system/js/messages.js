class AlertElement extends HTMLElement {
  constructor() {
    super();

    // Bindings
    this.close = this.close.bind(this);
    this.destroyCloseButton = this.destroyCloseButton.bind(this);
    this.createCloseButton = this.createCloseButton.bind(this);
    this.onMutation = this.onMutation.bind(this);
    this.observer = new MutationObserver(this.onMutation);
    this.observer.observe(this, {
      attributes: false,
      childList: true,
      subtree: true
    });

    // Handle the fade in animation
    this.addEventListener('animationend', event => {
      if (event.animationName === 'joomla-alert-fade-in' && event.target === this) {
        this.dispatchEvent(new CustomEvent('joomla.alert.shown'));
        this.style.removeProperty('animationName');
      }
    });

    // Handle the fade out animation
    this.addEventListener('animationend', event => {
      if (event.animationName === 'joomla-alert-fade-out' && event.target === this) {
        this.dispatchEvent(new CustomEvent('joomla.alert.closed'));
        this.remove();
      }
    });
  }

  /* Attributes to monitor */
  static get observedAttributes() {
    return ['type', 'role', 'dismiss', 'auto-dismiss', 'close-text'];
  }
  get type() {
    return this.getAttribute('type');
  }
  set type(value) {
    this.setAttribute('type', value);
  }
  get role() {
    return this.getAttribute('role');
  }
  set role(value) {
    this.setAttribute('role', value);
  }
  get closeText() {
    return this.getAttribute('close-text');
  }
  set closeText(value) {
    this.setAttribute('close-text', value);
  }
  get dismiss() {
    return this.getAttribute('dismiss');
  }
  set dismiss(value) {
    this.setAttribute('dismiss', value);
  }
  get autodismiss() {
    return this.getAttribute('auto-dismiss');
  }
  set autodismiss(value) {
    this.setAttribute('auto-dismiss', value);
  }

  /* Lifecycle, element appended to the DOM */
  connectedCallback() {
    this.dispatchEvent(new CustomEvent('joomla.alert.show'));
    this.style.animationName = 'joomla-alert-fade-in';

    // Default to info
    if (!this.type || !['info', 'warning', 'danger', 'success'].includes(this.type)) {
      this.setAttribute('type', 'info');
    }
    // Default to alert
    if (!this.role || !['alert', 'alertdialog'].includes(this.role)) {
      this.setAttribute('role', 'alert');
    }

    // Hydrate the button
    if (this.firstElementChild && this.firstElementChild.tagName === 'BUTTON') {
      this.button = this.firstElementChild;
      if (this.button.classList.contains('joomla-alert--close')) {
        this.button.classList.add('joomla-alert--close');
      }
      if (this.button.innerHTML === '') {
        this.button.innerHTML = '<span aria-hidden="true">&times;</span>';
      }
      if (!this.button.hasAttribute('aria-label')) {
        this.button.setAttribute('aria-label', this.closeText);
      }
    }

    // Append button
    if (this.hasAttribute('dismiss') && !this.button) {
      this.createCloseButton();
    }
    if (this.hasAttribute('auto-dismiss')) {
      this.autoDismiss();
    }
  }

  /* Lifecycle, element removed from the DOM */
  disconnectedCallback() {
    if (this.button) {
      this.button.removeEventListener('click', this.close);
    }
    this.observer.disconnect();
  }

  /* Respond to attribute changes */
  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      case 'type':
        if (!newValue || newValue && ['info', 'warning', 'danger', 'success'].indexOf(newValue) === -1) {
          this.type = 'info';
        }
        break;
      case 'role':
        if (!newValue || newValue && ['alert', 'alertdialog'].indexOf(newValue) === -1) {
          this.role = 'alert';
        }
        break;
      case 'dismiss':
        if ((!newValue || newValue === '') && (!oldValue || oldValue === '')) {
          if (this.button && !this.hasAttribute('dismiss')) {
            this.destroyCloseButton();
          } else if (!this.button && this.hasAttribute('dismiss')) {
            this.createCloseButton();
          }
        } else if (this.button && newValue === 'false') {
          this.destroyCloseButton();
        } else if (!this.button && newValue !== 'false') {
          this.createCloseButton();
        }
        break;
      case 'close-text':
        if (!newValue || newValue !== oldValue) {
          if (this.button) {
            this.button.setAttribute('aria-label', newValue);
          }
        }
        break;
      case 'auto-dismiss':
        this.autoDismiss();
        break;
    }
  }

  /* Observe added elements */
  onMutation(mutationsList) {
    // eslint-disable-next-line no-restricted-syntax
    for (const mutation of mutationsList) {
      if (mutation.type === 'childList') {
        if (mutation.addedNodes.length) {
          // Make sure that the button is always the first element
          if (this.button && this.firstElementChild !== this.button) {
            this.prepend(this.button);
          }
        }
      }
    }
  }

  /* Method to close the alert */
  close() {
    this.dispatchEvent(new CustomEvent('joomla.alert.close'));
    this.style.animationName = 'joomla-alert-fade-out';
  }

  /* Method to create the close button */
  createCloseButton() {
    this.button = document.createElement('button');
    this.button.setAttribute('type', 'button');
    this.button.classList.add('joomla-alert--close');
    this.button.innerHTML = '<span aria-hidden="true">&times;</span>';
    this.button.setAttribute('aria-label', this.closeText);
    this.insertAdjacentElement('afterbegin', this.button);

    /* Add the required listener */
    this.button.addEventListener('click', this.close);
  }

  /* Method to remove the close button */
  destroyCloseButton() {
    if (this.button) {
      this.button.removeEventListener('click', this.close);
      this.button.parentNode.removeChild(this.button);
      this.button = null;
    }
  }

  /* Method to auto-dismiss */
  autoDismiss() {
    const timer = parseInt(this.getAttribute('auto-dismiss'), 10);
    setTimeout(this.close, timer >= 10 ? timer : 3000);
  }
}
if (!customElements.get('joomla-alert')) {
  customElements.define('joomla-alert', AlertElement);
}

/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


/**
 * Returns the container of the Messages
 *
 * @param {string|HTMLElement}  container  The container
 *
 * @returns {HTMLElement}
 */
const getMessageContainer = container => {
  let messageContainer;
  if (container instanceof HTMLElement) {
    return container;
  }
  if (typeof container === 'undefined' || container && container === '#system-message-container') {
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
  if (typeof keepOld === 'undefined' || keepOld && keepOld === false) {
    Joomla.removeMessages(messageContainer);
  }
  Object.keys(messages).forEach(type => {
    let alertClass = type;

    // Array of messages of this type
    const typeMessages = messages[type];
    const messagesBox = document.createElement('joomla-alert');
    if (['success', 'info', 'danger', 'warning'].indexOf(type) < 0) {
      alertClass = type === 'notice' ? 'info' : type;
      alertClass = type === 'message' ? 'success' : alertClass;
      alertClass = type === 'error' ? 'danger' : alertClass;
      alertClass = type === 'warning' ? 'warning' : alertClass;
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
    typeMessages.forEach(typeMessage => {
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
Joomla.removeMessages = container => {
  const messageContainer = getMessageContainer(container);
  messageContainer.querySelectorAll('joomla-alert').forEach(alert => alert.close());
};
document.addEventListener('DOMContentLoaded', () => {
  const messages = Joomla.getOptions('joomla.messages');
  if (messages) {
    Object.keys(messages).map(message => Joomla.renderMessages(messages[message], undefined, true, undefined));
  }
});
