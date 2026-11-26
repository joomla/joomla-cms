/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

window.customElements.define('joomla-toolbar-button', class extends HTMLElement {
  // Attribute getters
  get task() { return this.getAttribute('task'); }

  get listSelection() { return this.hasAttribute('list-selection'); }

  get form() { return this.getAttribute('form'); }

  get formValidation() { return this.hasAttribute('form-validation'); }

  get confirmMessage() { return this.getAttribute('confirm-message'); }

  /**
   * Lifecycle
   */
  constructor() {
    super();

    if (!Joomla) {
      throw new Error('Joomla API is not properly initiated');
    }

    this.confirmationReceived = false;
    this.onChange = this.onChange.bind(this);
    this.executeTask = this.executeTask.bind(this);
  }

  /**
   * Lifecycle
   */
  connectedCallback() {
    // We need a button to support button behavior,
    // because we cannot currently extend HTMLButtonElement
    this.buttonElement = this.querySelector('button, a');

    this.buttonElement.addEventListener('click', this.executeTask);

    // Check whether we have a form
    const formSelector = this.form || 'adminForm';
    this.formElement = document.getElementById(formSelector);

    this.disabled = false;
    // If list selection is required, set button to disabled by default
    if (this.listSelection) {
      this.setDisabled(true);
    }

    if (this.listSelection) {
      if (!this.formElement) {
        throw new Error(`The form "${formSelector}" is required to perform the task, but the form was not found on the page.`);
      }

      // Watch on list selection
      this.formElement.boxchecked.addEventListener('change', this.onChange);
    }

    this.onHideDropdown = this.onHideDropdown.bind(this);
    if (this.buttonElement.classList.contains('dropdown-toggle')) {
      this.buttonElement.addEventListener('hide.bs.dropdown', this.onHideDropdown);
    }
  }

  /**
   * Lifecycle
   */
  disconnectedCallback() {
    if (this.formElement.boxchecked) {
      this.formElement.boxchecked.removeEventListener('change', this.onChange);
    }

    this.buttonElement.removeEventListener('click', this.executeTask);

    if (this.buttonElement.classList.contains('dropdown-toggle')) {
      this.buttonElement.removeEventListener('hide.bs.dropdown', this.onHideDropdown);
    }
  }

  onHideDropdown(e) {
    if (e.clickEvent && e.clickEvent.target instanceof Element) {
      const target = e.clickEvent.target;
      if (this.formElement && this.formElement.contains(target) && target.closest('input[type="checkbox"], label')) {
        if (this.formElement.boxchecked && this.formElement.boxchecked.value > 0) {
          e.preventDefault();
        }
      }
    }
  }

  onChange({ target }) {
    // Check whether we have selected something
    this.setDisabled(target.value < 1);
  }

  setDisabled(disabled) {
    // Make sure we have a boolean value
    this.disabled = !!disabled;

    // Switch attribute for native element
    // An anchor does not support "disabled" attribute, so use class
    if (this.buttonElement) {
      if (this.disabled) {
        // If it's a dropdown toggle and it's currently showing, hide it before disabling
        if (this.buttonElement.classList.contains('dropdown-toggle') && this.buttonElement.classList.contains('show')) {
          if (window.bootstrap && window.bootstrap.Dropdown) {
            const dropdown = window.bootstrap.Dropdown.getInstance(this.buttonElement);
            if (dropdown) {
              dropdown.hide();
            }
          }
        }

        if (this.buttonElement.nodeName === 'BUTTON') {
          this.buttonElement.setAttribute('aria-disabled', 'true');
        }
        this.buttonElement.classList.add('disabled');
      } else if (this.buttonElement.nodeName === 'BUTTON') {
        this.buttonElement.removeAttribute('aria-disabled');
        this.buttonElement.classList.remove('disabled');
      } else {
        this.buttonElement.classList.remove('disabled');
      }
    }
  }

  executeTask() {
    if (this.disabled) {
      return false;
    }

    // Ask for User confirmation when needed
    if (this.confirmMessage && !this.confirmationReceived) {
      import('joomla.dialog')
        .then((m) => m.default.confirm(this.confirmMessage, Joomla.Text._('WARNING', 'Warning')))
        .then((confirmed) => {
          if (confirmed) {
            // Set confirmation flag, and emulate the click again
            this.confirmationReceived = true;
            this.buttonElement.click();
          }
        });
      return false;
    }

    // Reset any previous confirmation
    this.confirmationReceived = false;

    if (this.task) {
      Joomla.submitbutton(this.task, this.form, this.formValidation);
    }

    return true;
  }
});
