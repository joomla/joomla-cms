(() => {
  class JoomlaAlertElement extends HTMLElement {
    /* Attributes to monitor */
    static get observedAttributes() { return ['type', 'role', 'dismiss', 'acknowledge', 'href']; }

    get type() { return this.getAttribute('type'); }

    set type(value) { return this.setAttribute('type', value); }

    get role() { return this.getAttribute('role'); }

    set role(value) { return this.setAttribute('role', value); }

    get dismiss() { return this.getAttribute('dismiss'); }

    get autodismiss() { return this.getAttribute('auto-dismiss'); }

    get acknowledge() { return this.getAttribute('acknowledge'); }

    get href() { return this.getAttribute('href'); }

    /* Lifecycle, element appended to the DOM */
    connectedCallback() {
      this.classList.add('joomla-alert--show');

      // Default to info
      if (!this.type || ['info', 'warning', 'danger', 'success'].indexOf(this.type) === -1) {
        this.setAttribute('type', 'info');
      }
      // Default to alert
      if (!this.role || ['alert', 'alertdialog'].indexOf(this.role) === -1) {
        this.setAttribute('role', 'alert');
      }
      // Append button
      if ((this.hasAttribute('dismiss') || this.hasAttribute('acknowledge')) || ((this.hasAttribute('href') && this.getAttribute('href') !== '')
        && !this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close'))) {
        this.appendCloseButton();
      }

      if (this.hasAttribute('auto-dismiss')) {
        this.autoDismiss();
      }

      this.dispatchCustomEvent('joomla.alert.show');
    }

    /* Lifecycle, element removed from the DOM */
    disconnectedCallback() {
      this.removeEventListener('joomla.alert.show', this);
      this.removeEventListener('joomla.alert.close', this);
      this.removeEventListener('joomla.alert.closed', this);

      if (this.firstChild.tagName && this.firstChild.tagName.toLowerCase() === 'button') {
        this.firstChild.removeEventListener('click', this);
      }
    }

    /* Respond to attribute changes */
    attributeChangedCallback(attr, oldValue, newValue) {
      switch (attr) {
        case 'type':
          if (!newValue || (newValue && ['info', 'warning', 'danger', 'success'].indexOf(newValue) === -1)) {
            this.type = 'info';
          }
          break;
        case 'role':
          if (!newValue || (newValue && ['alert', 'alertdialog'].indexOf(newValue) === -1)) {
            this.role = 'alert';
          }
          break;
        case 'dismiss':
        case 'acknowledge':
          if (!newValue || newValue === 'true') {
            this.appendCloseButton();
          } else {
            this.removeCloseButton();
          }
          break;
        case 'auto-dismiss':
          this.autoDismiss();
          break;
        case 'href':
          if (!newValue || newValue === '') {
            this.removeCloseButton();
          } else if (!this.querySelector('button.joomla-alert-button--close')) {
            this.appendCloseButton();
          }
          break;
        default:
          break;
      }
    }

    /* Method to close the alert */
    close(element = null) {
      this.dispatchCustomEvent('joomla.alert.close');
      this.addEventListener('transitionend', () => {
        this.dispatchCustomEvent('joomla.alert.closed');
        if (element) {
          element.parentNode.removeChild(element);
        } else {
          this.remove();
        }
      }, false);
      this.classList.remove('joomla-alert--show');
    }

    /* Method to dispatch events */
    dispatchCustomEvent(eventName) {
      const OriginalCustomEvent = new CustomEvent(eventName);
      OriginalCustomEvent.relatedTarget = this;
      this.dispatchEvent(OriginalCustomEvent);
      this.removeEventListener(eventName, this);
    }

    /* Method to create the close button */
    appendCloseButton() {
      if (this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close')) {
        return;
      }

      const self = this;
      const closeButton = document.createElement('button');

      if (this.hasAttribute('dismiss')) {
        closeButton.classList.add('joomla-alert--close');
        closeButton.innerHTML = '<span aria-hidden="true">&times;</span>';
        closeButton.setAttribute('aria-label', this.getText('JCLOSE', 'Close'));
      } else {
        closeButton.classList.add('joomla-alert-button--close');
        if (this.hasAttribute('acknowledge')) {
          closeButton.innerHTML = this.getText('JOK', 'ok');
        } else {
          closeButton.innerHTML = this.getText('JOPEN', 'Open');
        }
      }

      if (this.firstChild) {
        this.insertBefore(closeButton, this.firstChild);
      } else {
        this.appendChild(closeButton);
      }

      /* Add the required listener */
      if (closeButton) {
        if (!this.href) {
          closeButton.addEventListener('click', () => {
            self.dispatchCustomEvent('joomla.alert.buttonClicked');
            if (self.getAttribute('data-callback')) {
              window[self.getAttribute('data-callback')]();
              self.close();
            } else {
              self.close();
            }
          });
        } else {
          closeButton.addEventListener('click', () => {
            self.dispatchCustomEvent('joomla.alert.buttonClicked');
            window.location.href = self.href;
            self.close();
          });
        }
      }
    }

    /* Method to auto-dismiss */
    autoDismiss() {
      const self = this;
      setTimeout(() => {
        self.dispatchCustomEvent('joomla.alert.buttonClicked');
        if (self.hasAttribute('data-callback')) {
          window[self.getAttribute('data-callback')]();
        } else {
          self.close(self);
        }
      }, parseInt(self.getAttribute('auto-dismiss'), 10) ? self.getAttribute('auto-dismiss') : 3000);
    }

    /* Method to remove the close button */
    removeCloseButton() {
      const button = this.querySelector('button');
      if (button) {
        button.removeEventListener('click', this);
        button.parentNode.removeChild(button);
      }
    }

    /* Method to get the translated text */
    getText(str, fallback) {
      // TODO: Remove coupling to Joomla CMS Core JS here
      /* eslint-disable-next-line no-undef */
      return (window.Joomla && Joomla.JText && Joomla.JText._ && typeof Joomla.JText._ === 'function' && Joomla.JText._(str)) ? Joomla.JText._(str) : fallback;
    }
  }

  customElements.define('joomla-alert', JoomlaAlertElement);
})();
