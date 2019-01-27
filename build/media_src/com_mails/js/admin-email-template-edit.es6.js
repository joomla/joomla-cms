/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document, Joomla) => {
  'use strict';

  class EmailTemplateEdit {

    constructor(form, options) {
      this.form = form;
      this.templateData = options && options.templateData ? options.templateData : {};
      this.inputSubject = this.form.querySelector('#jform_subject');
      this.inputBody    = this.form.querySelector('#jform_body');
      this.inputHtmlBody = this.form.querySelector('#jform_htmlbody');

      console.log(this);
    }

    setBodyValue(value) {
      if (this.inputBody.disabled) {
        return;
      }

      if (Joomla.editors.instances[this.inputBody.id]) {
        Joomla.editors.instances[this.inputBody.id].setValue(value);
      } else {
        this.inputBody.value = value;
      }
    }

    setHtmlBodyValue(value) {
      if (this.inputHtmlBody.disabled) {
        return;
      }

      if (Joomla.editors.instances[this.inputHtmlBody.id]) {
        Joomla.editors.instances[this.inputHtmlBody.id].setValue(value);
      } else {
        this.inputHtmlBody.value = value;
      }
    }

    bindListeners() {
      this.form.addEventListener('joomla.switcher.on', (event) => {
        const type = event.target.id.slice(6, -9);
        const inputValue = this.templateData[type] ? this.templateData[type].translated : '';

        switch (type) {
          case 'subject':
            this.inputSubject.disabled = false;
            this.inputSubject.value = inputValue;
            break;
          case 'body':
            this.inputBody.disabled = false;
            this.setBodyValue(inputValue);
            break;
          case 'htmlbody':
            this.inputHtmlBody.disabled = false;
            this.setHtmlBodyValue(inputValue);
            break;
        }
      });

      this.form.addEventListener('joomla.switcher.off', (event) => {
        const type = event.target.id.slice(6, -9);
        const inputValue = this.templateData[type] ? this.templateData[type].master : '';

        switch (type) {
          case 'subject':
            this.inputSubject.disabled = true;
            this.inputSubject.value = inputValue;
            break;
          case 'body':
            this.setBodyValue(inputValue);
            this.inputBody.disabled = true;
            break;
          case 'htmlbody':
            this.setHtmlBodyValue(inputValue);
            this.inputHtmlBody.disabled = true;
            break;
        }

      });
    }

  }

  document.addEventListener('DOMContentLoaded', () => {
    const editor = new EmailTemplateEdit(document.getElementById('item-form'), Joomla.getOptions('com_mails'));
    editor.bindListeners();
  });

})(document, Joomla);
