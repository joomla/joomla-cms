/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((customElements) => {
  'use strict';

  class JoomlaToolbarButton extends HTMLElement {
    // Attribute getters
    get task() { return this.getAttribute('task'); }
    get listSelection() { return this.hasAttribute('list-selection'); }
    get form() { return this.getAttribute('form'); }
    get formValidation() { return this.hasAttribute('form-validation'); }
    get confirmMessage() { return this.getAttribute('confirm-message'); }

    constructor() {
      super();

      // We need a button to support button behavior,
      // because we cannot currently extend HTMLButtonElement
      this.buttonElement = this.querySelector('button');
      this.disabled = false;

      // If list selection are required, set button to disabled by default
      if (this.listSelection) {
        this.setDisabled(true);
      }

      this.addEventListener('click', e => this.executeTask(e));
    }

    connectedCallback() {
      // Check whether we have a form
      const formSelector = this.form || '#adminForm';
      this.formElement = document.querySelector(formSelector);

      if (this.listSelection) {
        if (!this.formElement) {
          throw new Error(`The form "${formSelector}" is required to perform the task, but the form not found on the page.`);
        }

        // Watch on list selection
        this.formElement.boxchecked.addEventListener('change', (event) => {
          // Check whether we have selected something
          this.setDisabled(event.target.value < 1);
        });
      }
    }

    setDisabled(disabled) {
      // Make sure we have a boolean value
      this.disabled = !!disabled;

      if (this.buttonElement) {
        if (this.disabled) {
          this.buttonElement.setAttribute('disabled', true);
        } else {
          this.buttonElement.removeAttribute('disabled');
        }
      }
    }

    executeTask() {
      if (this.disabled) {
        return false;
      }

      // eslint-disable-next-line no-restricted-globals
      if (this.confirmMessage && !confirm(this.confirmMessage)) {
        return false;
      }

      if (this.task) {
        Joomla.submitbutton(this.task, this.form, this.formValidation);
      } else {
        throw new Error('"task" attribute must be preset to perform an action.');
      }

      return true;
    }
  }

  customElements.define('joomla-toolbar-button', JoomlaToolbarButton);
})(customElements);
