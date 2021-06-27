/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(document => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
  	const addModuleBtnLabel = Joomla.getOptions('js-addModuleBtn');
    const elMain = document.querySelector('main');

    // Create the form node
		let elForm = document.createElement('form');
		elForm.setAttribute('method', 'get');

		// Hidden input field to pass the get param for place module view
		let placeModuleFlag = document.createElement('input');
		placeModuleFlag.setAttribute('type', 'hidden');
		placeModuleFlag.setAttribute('name', 'pm');
		placeModuleFlag.setAttribute('value', '1');
		elForm.appendChild(placeModuleFlag);

		let addModuleBtn = document.createElement('button');
		addModuleBtn.setAttribute('type', 'submit');
		addModuleBtn.classList.add('btn', 'jmodadd');
		addModuleBtn.innerText = addModuleBtnLabel;
		elForm.appendChild(addModuleBtn);

		// Append the form
		elMain.appendChild(elForm);
  });
})(document);
