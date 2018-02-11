/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(customElements) {
    "use strict";

    class JoomlaToolbarButton extends HTMLElement {

        // Attribute getters
        get task()              { return this.getAttribute('task'); }
        get listConfirmation()  { return this.hasAttribute('list-confirmation'); }
        get form()              { return this.getAttribute('form'); }
        get formValidation()    { return this.hasAttribute('form-validation'); }

        constructor() {
            super();

            // We need to do a button to support button behavior, because we cannot currently extend HTMLButtonElement
            let button = document.createElement('button');
            button.innerHTML = this.innerHTML;
            button.className = this.className;
            button.id        = this.id + '-button';
            this.innerHTML   = '';
            this.className   = '';

            // If list selection are required, set button to disabled by default
            if (this.listConfirmation) {
                button.setAttribute('disabled', 'disabled');
            }

            // Keep the button for quick reference
            this.taskButtonElement = button;

            this.appendChild(button);
            this.addEventListener('click', e => this.executeTask());
        }

        connectedCallback() {
            // Check whether we have a form
            let formId       = this.form || 'adminForm';
            this.formElement = document.getElementById(formId);

            if (this.listConfirmation) {
                if (!this.formElement) {
                    throw new Error('The form "' + formId + '" is required to perform the task, but the form not found on the page.');
                }

                // Watch on list selection
                this.formElement.addEventListener('change', (event) => {
                    let target = event.target;
                    if (target.nodeName !== 'INPUT' || (target.name !== 'cid[]' && target.name !== 'checkall-toggle')) {
                        return;
                    }

                    // Check whether we have selected something
                    if (this.formElement.boxchecked.value == 0) {
                        this.taskButtonElement.setAttribute('disabled', 'disabled');
                    } else {
                        this.taskButtonElement.removeAttribute('disabled');
                    }
                });
            }
        }

        executeTask() {
            Joomla.submitbutton(this.task, this.formElement, this.formValidation);
        }

    }

    customElements.define('joomla-toolbar-button', JoomlaToolbarButton);

})(customElements);
