/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
      this.inputHtmlBody = this.form.querySelector('#jform_htmlbody'); // Set options

      this.templateData = options && options.templateData ? options.templateData : {}; // Add back reference

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
        input.value += ` ${tag}`;
      }

      return true;
    }

    bindListeners() {
      // To enable editing of specific input
      const subjectSwitcher = document.querySelectorAll('input[type=radio][name="jform[subject_switcher]"]');
      const bodySwitcher = document.querySelectorAll('input[type=radio][name="jform[body_switcher]"]');
      const htmlBodySwitcher = document.querySelectorAll('input[type=radio][name="jform[htmlbody_switcher]"]');

      const subjectSwitcherChangeHandler = ({
        target
      }) => {
        if (target.value === '0') {
          this.inputSubject.disabled = true;
          this.inputSubject.value = this.templateData.subject ? this.templateData.subject.master : '';
        } else if (target.value === '1') {
          this.inputSubject.disabled = false;
          this.inputSubject.value = this.templateData.subject ? this.templateData.subject.translated : '';
        } else {
          // eslint-disable-next-line no-console
          console.error('unrecognised value');
        }
      };

      Array.prototype.forEach.call(subjectSwitcher, radio => {
        radio.addEventListener('change', subjectSwitcherChangeHandler);
      });

      const bodySwitcherChangeHandler = ({
        target
      }) => {
        const tagsContainer = this.form.querySelector('.tags-container-body');

        if (target.value === '0') {
          this.setBodyValue(this.templateData.body ? this.templateData.body.master : '');
          this.inputBody.disabled = true;
          tagsContainer.classList.add('hidden');
        } else if (target.value === '1') {
          this.inputBody.disabled = false;
          this.inputBody.readOnly = false;
          this.setBodyValue(this.templateData.body ? this.templateData.body.translated : '');
          tagsContainer.classList.remove('hidden');
        } else {
          // eslint-disable-next-line no-console
          console.error('unrecognised value');
        }
      };

      Array.prototype.forEach.call(bodySwitcher, radio => {
        radio.addEventListener('change', bodySwitcherChangeHandler);
      });

      const htmlBodySwitcherChangeHandler = ({
        target
      }) => {
        const tagsContainer = this.form.querySelector('.tags-container-htmlbody');

        if (target.value === '0') {
          this.setHtmlBodyValue(this.templateData.htmlbody ? this.templateData.htmlbody.master : '');
          this.inputHtmlBody.disabled = true;
          Joomla.editors.instances[this.inputHtmlBody.id].disable(true);
          tagsContainer.classList.add('hidden');
        } else if (target.value === '1') {
          Joomla.editors.instances[this.inputHtmlBody.id].disable(false);
          this.inputHtmlBody.disabled = false;
          this.inputHtmlBody.readOnly = false;
          this.setHtmlBodyValue(this.templateData.htmlbody ? this.templateData.htmlbody.translated : '');
          tagsContainer.classList.remove('hidden');
        } else {
          // eslint-disable-next-line no-console
          console.error('unrecognised value');
        }
      };

      Array.prototype.forEach.call(htmlBodySwitcher, radio => {
        radio.addEventListener('change', htmlBodySwitcherChangeHandler);
      }); // Buttons for inserting a tag

      this.form.querySelectorAll('.edit-action-add-tag').forEach(button => {
        button.addEventListener('click', event => {
          event.preventDefault();
          const el = event.target;
          this.insertTag(el.dataset.tag, el.dataset.target);
        });
      });
    }

  }

  document.addEventListener('DOMContentLoaded', () => {
    const editor = new EmailTemplateEdit(document.getElementById('item-form'), Joomla.getOptions('com_mails'));
    editor.bindListeners();
  });
})(document, Joomla);