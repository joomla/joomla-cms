/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document,submitForm) => {
	'use strict';

	// Selectors used by this script
	const buttonDataSelector = 'data-submit-task';
	const formId = 'adminForm';

	tinymce.init({
		selector: '.editable',
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
  /**
   * Submit the task
   * @param task
   */
  const submitTask = (task) => {
		const form = document.getElementById(formId);
		if (task === 'article.cancel' || document.formvalidator.isValid(form)) {
      var title = document.createElement("input");
      title.type = "hidden";
      title.name = "jform[title]";
      title.value = document.getElementById("headline").innerHTML;
      var articletext = document.createElement("input");
      articletext.type = "hidden";
      articletext.name = "jform[articletext]";
      articletext.value = document.getElementById("articlebody").innerHTML;

			form.appendChild(title)
      form.appendChild(articletext);
		  submitForm(task, form);
		}

	};

	// Register events
	document.addEventListener('DOMContentLoaded', () => {
		console.log(Joomla.getOptions('original_article_text'));
		var articleBody = document.getElementById("articlebody");
		articleBody.addEventListener('onmousedown', (e) => {
			articleBody.innerHTML = Joomla.getOptions('original_article_text');
			}
		);

		console.log(document.getElementById("articlebody").innerHTML);
		const buttons = [].slice.call(document.querySelectorAll(`[${buttonDataSelector}]`));
		buttons.forEach((button) => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				const task = e.target.getAttribute(buttonDataSelector);
				submitTask(task);
				}
			);
			}
		);
		}
	);
})(document, Joomla.submitform);
