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

            this.addEventListener('click', this.executeTask.bind(this));
        }

        executeTask() {
            let formId  = this.form || 'adminForm';
            let form    = document.getElementById(formId);
            let perform = true;

            if (this.listConfirmation) {
                if (!form) {
                    throw new Error('The form "' + formId + '" is required to perform the task, but the form not found on the page.');
                }

                if (form.boxchecked.value == 0) {
                    perform = false;
                    Joomla.renderMessages({'error': [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]});
                }
            }

            if (perform) {
                Joomla.submitbutton(this.task, form, this.formValidation);
            }
        }

    }

    customElements.define('joomla-toolbar-button', JoomlaToolbarButton);

})(customElements);
