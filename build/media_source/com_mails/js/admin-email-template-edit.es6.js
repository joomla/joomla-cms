/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document, Joomla) => {
  'use strict';

  class EmailTemplateEdit {
    constructor(form, options) {
      // Set elements
      this.form = form;
      this.inputSubject = this.form.querySelector('#jform_subject');
      this.inputBody = this.form.querySelector('#jform_body');
      this.inputHtmlBody = this.form.querySelector('#jform_htmlbody');

      // Set options
      this.templateData = options && options.templateData ? options.templateData : {};

      // Add back reference
      this.form.EmailTemplateEdit = this;
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

    insertTag(tag, targetField) {
      if (!tag) return false;

      let input;
      switch (targetField) {
        case 'body':
          input = this.inputBody;
          break;
        case 'htmlbody':
          input = this.inputHtmlBody;
          break;
        default:
          return false;
      }

      if (input.disabled) return false;

      if (Joomla.editors.instances[input.id]) {
        Joomla.editors.instances[input.id].replaceSelection(tag);
      } else {
        input.value += ' ' + tag;
      }

      return true;
    }

    bindListeners() {
      // To enable editing of specific input
      this.form.addEventListener('joomla.switcher.on', (event) => {
        const type = event.target.id.slice(6, -9);
        const inputValue = this.templateData[type] ? this.templateData[type].translated : '';
        let tagsContainer;

        switch (type) {
          case 'subject':
            this.inputSubject.disabled = false;
            this.inputSubject.value = inputValue;
            break;
          case 'htmlbody':
            this.inputHtmlBody.disabled = false;
            this.setHtmlBodyValue(inputValue);

            tagsContainer = this.form.querySelector('.tags-container-htmlbody');
            break;
          case 'body':
          default:
            this.inputBody.disabled = false;
            this.setBodyValue(inputValue);

            tagsContainer = this.form.querySelector('.tags-container-body');
            break;
        }

        // Show Tags section
        if (tagsContainer) {
          tagsContainer.classList.remove('hidden');
        }
      });

      // To disable editing of specific input
      this.form.addEventListener('joomla.switcher.off', (event) => {
        const type = event.target.id.slice(6, -9);
        const inputValue = this.templateData[type] ? this.templateData[type].master : '';
        let tagsContainer;

        switch (type) {
          case 'subject':
            this.inputSubject.disabled = true;
            this.inputSubject.value = inputValue;
            break;
          case 'htmlbody':
            this.setHtmlBodyValue(inputValue);
            this.inputHtmlBody.disabled = true;

            tagsContainer = this.form.querySelector('.tags-container-htmlbody');
            break;
          case 'body':
          default:
            this.setBodyValue(inputValue);
            this.inputBody.disabled = true;

            tagsContainer = this.form.querySelector('.tags-container-body');
            break;
        }

        // Hide Tags section
        if (tagsContainer) {
          tagsContainer.classList.add('hidden');
        }
      });

      // Buttons for inserting a tag
      this.form.querySelectorAll('.edit-action-add-tag').forEach((button) => {
        button.addEventListener('click', (event) => {
          event.preventDefault();
          const el = event.target;
          this.insertTag(el.dataset.tag, el.dataset.target);
        });
      })
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const editor = new EmailTemplateEdit(document.getElementById('item-form'), Joomla.getOptions('com_mails'));
    editor.bindListeners();
  });
})(document, Joomla);
