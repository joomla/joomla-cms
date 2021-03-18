/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document, Joomla) {
  'use strict';

  var EmailTemplateEdit = /*#__PURE__*/function () {
    function EmailTemplateEdit(form, options) {
      _classCallCheck(this, EmailTemplateEdit);

      // Set elements
      this.form = form;
      this.inputSubject = this.form.querySelector('#jform_subject');
      this.inputBody = this.form.querySelector('#jform_body');
      this.inputHtmlBody = this.form.querySelector('#jform_htmlbody'); // Set options

      this.templateData = options && options.templateData ? options.templateData : {}; // Add back reference

      this.form.EmailTemplateEdit = this;
    }

    _createClass(EmailTemplateEdit, [{
      key: "setBodyValue",
      value: function setBodyValue(value) {
        if (this.inputBody.disabled) {
          return;
        }

        if (Joomla.editors.instances[this.inputBody.id]) {
          Joomla.editors.instances[this.inputBody.id].setValue(value);
        } else {
          this.inputBody.value = value;
        }
      }
    }, {
      key: "setHtmlBodyValue",
      value: function setHtmlBodyValue(value) {
        if (this.inputHtmlBody.disabled) {
          return;
        }

        if (Joomla.editors.instances[this.inputHtmlBody.id]) {
          Joomla.editors.instances[this.inputHtmlBody.id].setValue(value);
        } else {
          this.inputHtmlBody.value = value;
        }
      }
    }, {
      key: "insertTag",
      value: function insertTag(tag, targetField) {
        if (!tag) return false;
        var input;

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
          input.value += " ".concat(tag);
        }

        return true;
      }
    }, {
      key: "bindListeners",
      value: function bindListeners() {
        var _this = this;

        // To enable editing of specific input
        var subjectSwitcher = document.querySelectorAll('input[type=radio][name="jform[subject_switcher]"]');
        var bodySwitcher = document.querySelectorAll('input[type=radio][name="jform[body_switcher]"]');
        var htmlBodySwitcher = document.querySelectorAll('input[type=radio][name="jform[htmlbody_switcher]"]');

        var subjectSwitcherChangeHandler = function subjectSwitcherChangeHandler(_ref) {
          var target = _ref.target;

          if (target.value === '0') {
            _this.inputSubject.disabled = true;
            _this.inputSubject.value = _this.templateData.subject ? _this.templateData.subject.master : '';
          } else if (target.value === '1') {
            _this.inputSubject.disabled = false;
            _this.inputSubject.value = _this.templateData.subject ? _this.templateData.subject.translated : '';
          } else {
            // eslint-disable-next-line no-console
            console.error('unrecognised value');
          }
        };

        Array.prototype.forEach.call(subjectSwitcher, function (radio) {
          radio.addEventListener('change', subjectSwitcherChangeHandler);
        });

        var bodySwitcherChangeHandler = function bodySwitcherChangeHandler(_ref2) {
          var target = _ref2.target;

          var tagsContainer = _this.form.querySelector('.tags-container-body');

          if (target.value === '0') {
            _this.setBodyValue(_this.templateData.body ? _this.templateData.body.master : '');

            _this.inputBody.disabled = true;
            tagsContainer.classList.add('hidden');
          } else if (target.value === '1') {
            _this.inputBody.disabled = false;
            _this.inputBody.readOnly = false;

            _this.setBodyValue(_this.templateData.body ? _this.templateData.body.translated : '');

            tagsContainer.classList.remove('hidden');
          } else {
            // eslint-disable-next-line no-console
            console.error('unrecognised value');
          }
        };

        Array.prototype.forEach.call(bodySwitcher, function (radio) {
          radio.addEventListener('change', bodySwitcherChangeHandler);
        });

        var htmlBodySwitcherChangeHandler = function htmlBodySwitcherChangeHandler(_ref3) {
          var target = _ref3.target;

          var tagsContainer = _this.form.querySelector('.tags-container-htmlbody');

          if (target.value === '0') {
            _this.setHtmlBodyValue(_this.templateData.htmlbody ? _this.templateData.htmlbody.master : '');

            _this.inputHtmlBody.disabled = true;

            Joomla.editors.instances[_this.inputHtmlBody.id].disable(true);

            tagsContainer.classList.add('hidden');
          } else if (target.value === '1') {
            Joomla.editors.instances[_this.inputHtmlBody.id].disable(false);

            _this.inputHtmlBody.disabled = false;
            _this.inputHtmlBody.readOnly = false;

            _this.setHtmlBodyValue(_this.templateData.htmlbody ? _this.templateData.htmlbody.translated : '');

            tagsContainer.classList.remove('hidden');
          } else {
            // eslint-disable-next-line no-console
            console.error('unrecognised value');
          }
        };

        Array.prototype.forEach.call(htmlBodySwitcher, function (radio) {
          radio.addEventListener('change', htmlBodySwitcherChangeHandler);
        }); // Buttons for inserting a tag

        this.form.querySelectorAll('.edit-action-add-tag').forEach(function (button) {
          button.addEventListener('click', function (event) {
            event.preventDefault();
            var el = event.target;

            _this.insertTag(el.dataset.tag, el.dataset.target);
          });
        });
      }
    }]);

    return EmailTemplateEdit;
  }();

  document.addEventListener('DOMContentLoaded', function () {
    var editor = new EmailTemplateEdit(document.getElementById('item-form'), Joomla.getOptions('com_mails'));
    editor.bindListeners();
  });
})(document, Joomla);