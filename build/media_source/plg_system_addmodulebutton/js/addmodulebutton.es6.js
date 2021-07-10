/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const addModuleBtnOptions = Joomla.getOptions('js-addModuleBtn');
    const elMain = document.querySelector('main');

    // Create the form node
    const elForm = document.createElement('form');
    elForm.setAttribute('method', 'get');

    // Hidden input field to pass the get param for place module view
    const placeModuleFlag = document.createElement('input');
    placeModuleFlag.setAttribute('type', 'hidden');
    placeModuleFlag.setAttribute('name', 'pm');
    placeModuleFlag.setAttribute('value', '1');
    elForm.appendChild(placeModuleFlag);

    const iconSpan = document.createElement('span');
    iconSpan.classList.add('icon-new');
    iconSpan.setAttribute('aria-hidden', 'true');

    const textSpan = document.createElement('span');
    textSpan.classList.add('visually-hidden');
    textSpan.innerText = addModuleBtnOptions.btnLabel;

    // The button to ad
    const addModuleBtn = document.createElement('button');
    addModuleBtn.setAttribute('type', 'submit');
    addModuleBtn.classList.add('btn', 'jmodedit');
    addModuleBtn.setAttribute('aria-described-by', 'tip-addmodule');
    addModuleBtn.appendChild(iconSpan);
    addModuleBtn.appendChild(textSpan);

    const addModuleBtnDiv = document.createElement('div');
    addModuleBtnDiv.classList.add('mod-custom');
    addModuleBtnDiv.appendChild(addModuleBtn);

    // Tooltip for the add module button
    const addModuleTooltip = document.createElement('div');
    addModuleTooltip.setAttribute('role', 'tooltip');
    addModuleTooltip.setAttribute('id', 'tip-addmodule');
    addModuleTooltip.appendChild(document.createTextNode(addModuleBtnOptions.btnLabel));
    addModuleTooltip.appendChild(document.createElement('br'));
    addModuleTooltip.appendChild(document.createTextNode(addModuleBtnOptions.btnDescription));
    addModuleBtnDiv.appendChild(addModuleTooltip);

    elForm.appendChild(addModuleBtnDiv);
    elMain.insertBefore(elForm, elMain.firstChild);
  });
})(document);
