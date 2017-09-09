class JoomlaAlertElement extends HTMLElement {
  /* Attributes to monitor */
  static get observedAttributes() { return ['type', 'dismiss', 'acknowledge', 'href', 'auto-dismiss', 'position', 'textClose', 'textDismiss', 'textAcknowledge']; }
  get type() { return this.getAttribute('type'); }
  set type(value) { return this.setAttribute('type', value); }
  get dismiss() { return this.getAttribute('dismiss'); }
  set dismiss(value) { return this.setAttribute('dismiss', value); }
  get acknowledge() { return this.getAttribute('acknowledge'); }
  set acknowledge(value) { return this.setAttribute('acknowledge', value); }
  get href() { return this.getAttribute('href'); }
  set href(value) { return this.setAttribute('href', value); }
  get autoDismiss() { return parseInt(this.getAttribute('auto-dismiss'), 10); }
  set autoDismiss(value) { return this.setAttribute('auto-dismiss', parseInt(value, 10)); }
  get position() { return this.getAttribute('position'); }
  set position(value) { return this.setAttribute('position', value); }
  get textClose() { return this.getAttribute('textClose') || 'Close'; }
  set textClose(value) { return this.setAttribute('textClose', value); }
  get textDismiss() { return this.getAttribute('textDismiss') || 'Open'; }
  set textDismiss(value) { return this.setAttribute('textDismiss', value); }
  get textAcknowledge() { return this.getAttribute('textAcknowledge') || 'Ok'; }
  set textAcknowledge(value) { return this.setAttribute('textAcknowledge', value); }

  /* Lifecycle, element appended to the DOM */
  connectedCallback() {
    // Trigger show event
    this.dispatchCustomEvent('joomla.alert.show');
    this.setAttribute('role', 'alert');
    this.classList.add('joomla-alert--show');

    // If no type has been defined, the default as "info"
    if (!this.type) {
      this.setAttribute('type', 'info');
    }

    // Append button
    if (this.hasAttribute('dismiss') || this.hasAttribute('acknowledge') || (this.hasAttribute('href') && this.getAttribute('href') !== '')) {
      if (!this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close')) {
        this.appendCloseButton.bind(this)();
      }
    }

    // Trigger shown event
    this.dispatchCustomEvent('joomla.alert.show');

    if (this.closeButton) {
      this.closeButton.focus();
    }
  }

  /* Lifecycle, element removed from the DOM */
  disconnectedCallback() {
    if (this.firstChild.tagName && this.firstChild.tagName.toLowerCase() === 'button') {
      this.firstChild.removeEventListener('click', this.buttonCloseFn);
    }
  }

  /* Respond to attribute changes */
  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      case 'type':
        if (!newValue || ['info', 'warning', 'success', 'danger'].indexOf(newValue) === -1) {
          this.type = 'info';
        }
        break;
      case 'dismiss':
      case 'acknowledge':
        if (!newValue || newValue === 'true') {
          if (this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() !== 'button') {
            this.appendCloseButton.bind(this)();
          }
        } else if (this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() === 'button') {
          this.removeCloseButton.bind(this)();
        }
        break;
      case 'href':
        if (!newValue || newValue === '') {
          if (this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() !== 'button') {
            this.removeCloseButton.bind(this)();
          }
        } else if (this.firstElementChild.tagName && this.firstElementChild.tagName.toLowerCase() !== 'button' && this.firstElementChild.classList.contains('joomla-alert-button--close')) {
          this.appendCloseButton.bind(this)();
        }
        break;
      case 'auto-dismiss':
        if (!newValue || newValue === '') {
          this.removeAttribute('auto-dismiss');
        }
        break;
      default:
        break;
    }
  }

  buttonCloseFn() {
    this.dispatchCustomEvent('joomla.alert.buttonClicked');
    if (this.href) {
      window.location.href = this.href;
    }
    this.close();
  }

  /* Method to close the alert */
  close() {
    this.dispatchCustomEvent('joomla.alert.close');
    this.addEventListener('transitionend', () => {
      this.dispatchCustomEvent('joomla.alert.closed');
      this.parentNode.removeChild(this);
    });
    this.classList.remove('joomla-alert--show');
  }

  /* Method to dispatch events. Internal */
  dispatchCustomEvent(eventName) {
    const OriginalCustomEvent = new CustomEvent(eventName, { bubbles: true, cancelable: true });
    OriginalCustomEvent.relatedTarget = this;
    this.dispatchEvent(OriginalCustomEvent);
    this.removeEventListener(eventName, this);
  }

  /* Method to create the close button. Internal */
  appendCloseButton() {
    if (this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close')) {
      return;
    }

    const closeButton = document.createElement('button');

    if (this.hasAttribute('dismiss')) {
      closeButton.classList.add('joomla-alert--close');
      closeButton.innerHTML = '<span aria-hidden="true">&times;</span>';
      closeButton.setAttribute('aria-label', this.textClose);
    } else {
      closeButton.classList.add('joomla-alert-button--close');
      if (this.hasAttribute('acknowledge')) {
        closeButton.innerHTML = this.textAcknowledge;
      } else {
        closeButton.innerHTML = this.textDismiss;
      }
    }

    this.closeButton = closeButton;

    if (this.firstChild) {
      this.insertBefore(closeButton, this.firstChild);
    } else {
      this.appendChild(closeButton);
    }

    /* Add the required listener */
    if (closeButton) {
      closeButton.addEventListener('click', this.buttonCloseFn.bind(this));
    }

    if (this.autoDismiss > 0) {
      const self = this;
      const timeout = this.autoDismiss;
      setTimeout(() => {
        self.dispatchCustomEvent('joomla.alert.buttonClicked');
        if (self.href) {
          window.location.href = self.href;
        }
        self.close();
      }, timeout);
    }
  }

  /* Method to remove the close button. Internal */
  removeCloseButton() {
    if (this.closeButton) {
      this.closeButton.removeEventListener('click', this.buttonCloseFn);
      this.closeButton.parentNode.removeChild(this.closeButton);
    }
  }
}

customElements.define('joomla-alert', JoomlaAlertElement);
