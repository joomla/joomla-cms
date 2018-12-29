;((customElements) => {
  // Keycodes
  const KEYCODE = {
    ENTER: 13,
    SPACE: 32,
  };

  class JoomlaSwitcherElement extends HTMLElement {
    /* Attributes to monitor */
    static get observedAttributes() { return ['type', 'off-text', 'on-text']; }

    get type() { return this.getAttribute('type'); }
    set type(value) { return this.setAttribute('type', value); }
    get offText() { return this.getAttribute('off-text') || 'Off'; }
    get onText() { return this.getAttribute('on-text') || 'On'; }

    // attributeChangedCallback(attr, oldValue, newValue) {}

    constructor() {
      super();

      this.inputs = [];
      this.spans = [];
      this.initialized = false;
      this.inputsContainer = '';
      this.newActive = '';
      this.inputLabel = '';
      this.inputLabelText = '';

      // Let's bind some functions so we always have the same context
      this.createMarkup = this.createMarkup.bind(this);
      this.addListeners = this.addListeners.bind(this);
      this.removeListeners = this.removeListeners.bind(this);
      this.switch = this.switch.bind(this);
      this.toggle = this.toggle.bind(this);
      this.keyEvents = this.keyEvents.bind(this);
      this.onFocus = this.onFocus.bind(this);
    }

    /* Lifecycle, element appended to the DOM */
    connectedCallback() {
      // Element was moved so we need to re add the event listeners
      if (this.initialized && this.inputs.length > 0) {
        this.addListeners();
        return;
      }

      this.inputs = [].slice.call(this.querySelectorAll('input'));

      if (this.inputs.length !== 2 || this.inputs[0].type !== 'radio') {
        throw new Error('`Joomla-switcher` requires two inputs type="radio"');
      }

      // this.inputLabel = document.querySelector(`[for="${this.id}"]`);
      //
      // if (this.inputLabel) {
      //   this.inputLabelText = this.inputLabel.innerText;
      // }

      // Create the markup
      this.createMarkup();

      this.inputsContainer = this.inputs[0].parentNode;

      this.inputsContainer.setAttribute('role', 'switch');

      if (this.inputs[1].checked) {
        this.inputs[1].parentNode.classList.add('active');
        this.spans[1].classList.add('active');

        // Aria-label ONLY in the container span!
        this.inputsContainer.setAttribute('aria-labeledby', `${this.id}-lbl`); //this.spans[1].innerHTML);
      } else {
        this.spans[0].classList.add('active');

        // Aria-label ONLY in the container span!
        this.inputsContainer.setAttribute('aria-label', this.spans[0].innerHTML);
      }

      this.addListeners();
    }

    /* Lifecycle, element removed from the DOM */
    disconnectedCallback() {
      this.removeListeners();
    }

    /* Method to dispatch events */
    dispatchCustomEvent(eventName) {
      const OriginalCustomEvent = new CustomEvent(eventName, { bubbles: true, cancelable: true });
      OriginalCustomEvent.relatedTarget = this;
      this.dispatchEvent(OriginalCustomEvent);
      this.removeEventListener(eventName, this);
    }

    /** Method to build the switch */
    createMarkup() {
      let checked = 0;

      // If no type has been defined, the default as "success"
      if (!this.type) {
        this.setAttribute('type', 'success');
      }

      // Create the first 'span' wrapper
      const spanFirst = document.createElement('fieldset');
      spanFirst.classList.add('switcher');
      spanFirst.classList.add(this.type);
      spanFirst.setAttribute('tabindex', '0');

      // Set the id to the fieldset
      spanFirst.id = this.id;
      // Remove the id from the custom Element
      // this.removeAttribute('id');

      const switchEl = document.createElement('span');
      switchEl.classList.add('switch');
      switchEl.classList.add(this.type);

      this.inputs.forEach((input, index) => {
        // Remove the tab focus from the inputs
        input.setAttribute('tabindex', '-1');

        if (input.checked) {
          spanFirst.setAttribute('aria-checked', true);
        }

        spanFirst.appendChild(input);

        if (index === 1 && input.checked) {
          checked = 1;
        }
      });

      spanFirst.appendChild(switchEl);

      // Create the second 'span' wrapper
      const spanSecond = document.createElement('span');
      spanSecond.classList.add('switcher-labels');

      const labelFirst = document.createElement('span');
      labelFirst.classList.add('switcher-label-0');
      labelFirst.innerHTML = `${this.offText}`;

      const labelSecond = document.createElement('span');
      labelSecond.classList.add('switcher-label-1');
      labelSecond.innerHTML = `${this.onText}`;

      if (checked === 0) {
        labelFirst.classList.add('active');
      } else {
        labelSecond.classList.add('active');
      }

      this.spans.push(labelFirst);
      this.spans.push(labelSecond);
      spanSecond.appendChild(labelFirst);
      spanSecond.appendChild(labelSecond);

      // Append everything back to the main element
      this.appendChild(spanFirst);
      this.appendChild(spanSecond);

      this.initialized = true;
    }

    /** Method to toggle the switch */
    switch() {
      this.spans.forEach((span) => {
        span.classList.remove('active');
      });

      if (this.inputsContainer.classList.contains('active')) {
        this.inputsContainer.classList.remove('active');
      } else {
        this.inputsContainer.classList.add('active');
      }

      // Remove active class from all inputs
      this.inputs.forEach((input) => {
        input.classList.remove('active');
      });

      // Check if active
      if (this.newActive === 1) {
        this.inputs[this.newActive].classList.add('active');
        this.inputs[1].setAttribute('checked', '');
        this.inputs[0].removeAttribute('checked');
        this.inputsContainer.setAttribute('aria-checked', true);

        // Aria-label ONLY in the container span!
        this.inputsContainer.setAttribute('aria-label', `${this.inputLabelText} ${this.spans[1].innerHTML}`);

        // Dispatch the "joomla.switcher.on" event
        this.dispatchCustomEvent('joomla.switcher.on');
      } else {
        this.inputs[1].removeAttribute('checked');
        this.inputs[0].setAttribute('checked', '');
        this.inputs[0].classList.add('active');
        this.inputsContainer.setAttribute('aria-checked', false);

        // Aria-label ONLY in the container span!
        this.inputsContainer.setAttribute('aria-label', `${this.inputLabelText} ${this.spans[0].innerHTML}`);

        // Dispatch the "joomla.switcher.off" event
        this.dispatchCustomEvent('joomla.switcher.off');
      }

      this.spans[this.newActive].classList.add('active');
    }

    /** Method to toggle the switch */
    toggle() {
      this.newActive = this.inputs[1].classList.contains('active') ? 0 : 1;
      this.switch();
    }

    keyEvents(event) {
      if (event.keyCode === KEYCODE.ENTER || event.keyCode === KEYCODE.SPACE) {
        event.preventDefault();
        this.newActive = this.inputs[1].classList.contains('active') ? 0 : 1;
        this.switch();
      }
    }

    onFocus() {
      this.inputsContainer.focus();
    }

    addListeners() {
      if (this.inputLabel) {
        this.inputLabel.addEventListener('click', this.onFocus);
      }

      this.inputs.forEach((switchEl) => {
        // Add the active class on click
        switchEl.addEventListener('click', this.toggle);
      });

      this.inputsContainer.addEventListener('keydown', this.keyEvents);
    }

    removeListeners() {
      if (this.inputLabel) {
        this.inputLabel.removeEventListener('click', this.onFocus);
      }

      this.inputs.forEach((switchEl) => {
        // Add the active class on click
        switchEl.removeEventListener('click', this.toggle);
      });

      this.inputsContainer.removeEventListener('keydown', this.keyEvents);
    }
  }

  customElements.define('joomla-field-switcher', JoomlaSwitcherElement);
})(customElements);
