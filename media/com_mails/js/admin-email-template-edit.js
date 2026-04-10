/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document, Joomla) => {

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
      if (Joomla.editors.instances[this.inputBody.id]) {
        Joomla.editors.instances[this.inputBody.id].setValue(value);
      } else {
        this.inputBody.value = value;
      }
    }
    setHtmlBodyValue(value) {
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
      if (Joomla.editors.instances[input.id]) {
        Joomla.editors.instances[input.id].replaceSelection(tag);
      } else {
        input.value += ` ${tag}`;
      }
      return true;
    }
    bindListeners() {
      document.querySelector('#btnResetSubject').addEventListener('click', event => {
        event.preventDefault();
        this.inputSubject.value = this.templateData.subject ? this.templateData.subject : '';
      });
      const btnResetBody = document.querySelector('#btnResetBody');
      if (btnResetBody) {
        btnResetBody.addEventListener('click', event => {
          event.preventDefault();
          this.setBodyValue(this.templateData.body ? this.templateData.body : '');
        });
      }
      const btnResetHtmlBody = document.querySelector('#btnResetHtmlBody');
      if (btnResetHtmlBody) {
        btnResetHtmlBody.addEventListener('click', event => {
          event.preventDefault();
          this.setHtmlBodyValue(this.templateData.htmlbody ? this.templateData.htmlbody : '');
        });
      }

      // Buttons for inserting a tag
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
