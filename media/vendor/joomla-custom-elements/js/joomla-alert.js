(() => {
  class JoomlaAlertElement extends HTMLElement {
    /* Attributes to monitor */
    static get observedAttributes() { return ['type', 'dismiss', 'acknowledge', 'href']; }
    get type() { return this.getAttribute('type'); }
    set type(value) { return this.setAttribute('type', value); }
    get dismiss() { return this.getAttribute('dismiss'); }
    get acknowledge() { return this.getAttribute('acknowledge'); }
    get href() { return this.getAttribute('href'); }

    /* Lifecycle, element created */
    constructor() {
      super();
    }

    /* Lifecycle, element appended to the DOM */
    connectedCallback() {
      this.setAttribute('role', 'alert');
      this.classList.add("joomla-alert--show");

      // Default to info
      if (!this.type || ['info', 'warning', 'danger', 'success'].indexOf(this.type) === -1) {
        this.setAttribute('type', 'info');
      }
      // Append button
      if (this.hasAttribute('dismiss') || this.hasAttribute('acknowledge') || (this.hasAttribute('href') && this.getAttribute('href') !== '')
        && !this.querySelector('button.joomla-alert--close') && !this.querySelector('button.joomla-alert-button--close')) {
        this.appendCloseButton();
      }

      this.dispatchCustomEvent('joomla.alert.show');

      let closeButton = this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close');

      if (closeButton) {
        closeButton.focus()
      }
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
        case 'dismiss':
        case 'acknowledge':
          if (!newValue || newValue === "true") {
            this.appendCloseButton();
          } else {
            this.removeCloseButton();
          }
          break;
        case 'href':
          if (!newValue || newValue === '') {
            this.removeCloseButton();
          } else {
            if (!this.querySelector('button.joomla-alert-button--close')) {
              this.appendCloseButton();
            }
          }
          break;
      }
    }

    /* Method to close the alert */
    close() {
      this.dispatchCustomEvent('joomla.alert.close');
      this.addEventListener("transitionend", function () {
        this.dispatchCustomEvent('joomla.alert.closed');
        this.parentNode.removeChild(this);
      }, false);
      this.classList.remove('joomla-alert--show');
    }

    /* Method to dispatch events */
    dispatchCustomEvent(eventName) {
      let OriginalCustomEvent = new CustomEvent(eventName);
      OriginalCustomEvent.relatedTarget = this;
      this.dispatchEvent(OriginalCustomEvent);
      this.removeEventListener(eventName, this);
    }

    /* Method to create the close button */
    appendCloseButton() {
      if (this.querySelector('button.joomla-alert--close') || this.querySelector('button.joomla-alert-button--close')) {
        return;
      }

      let self = this, closeButton = document.createElement('button');

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
          closeButton.addEventListener('click', function () {
            self.dispatchCustomEvent('joomla.alert.buttonClicked');
            if (self.getAttribute('data-callback')) {
              window[self.getAttribute('data-callback')]();
              self.close();
            } else {
              self.close();
            }
          });
        } else {
          closeButton.addEventListener('click', function () {
            self.dispatchCustomEvent('joomla.alert.buttonClicked');
            window.location.href = self.href;
            self.close();
          });
        }
      }

      if (this.hasAttribute('auto-dismiss')) {
        setTimeout(function () {
          self.dispatchCustomEvent('joomla.alert.buttonClicked');
          if (self.hasAttribute('data-callback')) {
            window[self.getAttribute('data-callback')]();
          } else {
            self.close();
          }
        }, parseInt(self.getAttribute('auto-dismiss')) ? self.getAttribute('auto-dismiss') : 3000);
      }
    }

    /* Method to remove the close button */
    removeCloseButton() {
      let button = this.querySelector('button');
      if (button) {
        button.removeEventListener('click', this);
        button.parentNode.removeChild(button);
      }
    }

    /* Method to get the translated text */
    getText(str, fallback) {
      return (window.Joomla && Joomla.JText && Joomla.JText._ && typeof Joomla.JText._ === 'function' && Joomla.JText._(str)) ? Joomla.JText._(str) : fallback;
    }
  }

  customElements.define('joomla-alert', JoomlaAlertElement);
})();
