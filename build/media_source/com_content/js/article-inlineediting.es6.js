/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((tinyMCE, window, document, submitForm) => {
  'use strict';

  // Selectors used by this script
  const buttonDataSelector = 'data-submit-task';
  const formId = 'adminForm';

  /**
   * Submit the task
   * @param task
   */
  const submitTask = (task) => {
    const form = document.getElementById(formId);
    var title = document.createElement('input');
    title.type = 'hidden';
    title.name = 'jform[title]';
    title.value = document.getElementById('headline').innerHTML;
    var articletext = document.createElement('input');
    articletext.type = 'hidden';
    articletext.name = 'jform[articletext]';
    articletext.value = document.getElementById('articlebody').innerHTML;

    form.appendChild(title);
    form.appendChild(articletext);
    submitForm(task, form);
  };

  // Register events
  document.addEventListener('DOMContentLoaded', () => {
      window.tinyMCE.init({
          selector: 'div.editable',
          menubar: false,
          inline: true,
          plugins: [
            'link',
            'lists',
            'powerpaste',
            'autolink',
            'tinymcespellchecker'
          ],
          toolbar: [
            'undo redo | bold italic underline | fontselect fontsizeselect',
            'forecolor backcolor | alignleft aligncenter alignright alignfull | numlist bullist outdent indent'
          ]
        }
      );
      window.tinyMCE.init({
          selector: 'h2.editable',
          menubar: false,
          inline: true,
          plugins: [
            'link',
            'lists',
            'powerpaste',
            'autolink',
            'tinymcespellchecker',
          ],
          toolbar: [
            'undo redo',
          ],
        }
      );
      var el = [].slice.call(document.getElementsByClassName('editable'));
      el.forEach((field) => {
        field.addEventListener('mouseenter', () => {
          addBorder(field);
          setTimeout(deleteBorder, 500, field);
        });
        field.addEventListener('focusout', () => {
          submitTask('article.save');
        });
      });

      function addBorder(field) {
        field.style.border = '1px solid #000000';
        field.style.borderRadius = '2px';
      }

      function deleteBorder(field) {
        field.style.border = 'none';
      }

    }
  );
})(window.tinyMCE, window, document, Joomla.submitform);
