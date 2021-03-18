/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(Joomla => {
  'use strict';

  Joomla.submitbutton = task => {
    if (task === 'module.cancel' || document.formvalidator.isValid(document.getElementById('module-form'))) {
      Joomla.submitform(task, document.getElementById('module-form'));
      const options = Joomla.getOptions('module-edit');

      if (window.self !== window.top) {
        if (window.parent.viewLevels) {
          const updPosition = document.getElementById('jform_position').value;
          const updTitle = document.getElementById('jform_title').value;
          const updMenus = document.querySelector('#jform_assignment').value;
          const updStatus = document.querySelector('#jform_published').value;
          const updAccess = document.querySelector('#jform_access').value;
          const tmpMenu = window.parent.document.getElementById(`menus-${options.itemId}`);
          const tmpRow = window.parent.document.getElementById(`tr-${options.itemId}`);
          const tmpStatus = window.parent.document.getElementById(`status-${options.itemId}`);
          window.parent.inMenus = [];
          window.parent.numMenus = [].slice.call(document.querySelectorAll('input[name="jform[assigned][]"]')).length;
          [].slice.call(document.querySelectorAll('input[name="jform[assigned][]"]')).forEach(element => {
            if (updMenus > 0) {
              if (element.checked) {
                window.parent.inMenus.push(parseInt(element.value, 10));
              }
            }

            if (updMenus < 0) {
              if (!element.checked) {
                window.parent.inMenus.push(parseInt(element.value, 10));
              }
            }
          });

          if (updMenus === '-') {
            tmpMenu.innerHTML = `<span class="badge badge-danger">${Joomla.JText._('JNO')}</span>`;

            if (!tmpRow.classList.contains('no') || tmpRow.classList.length === 0) {
              tmpRow.classList.add('no');
            }
          }

          if (parseInt(updMenus, 10) === 0) {
            tmpMenu.innerHTML = `<span class="badge badge-info">${Joomla.JText._('JALL')}</span>`;

            if (tmpRow.classList.contains('no')) {
              tmpRow.classList.remove('no');
            }
          }

          if (parseInt(updMenus, 10) > 0) {
            if (window.parent.inMenus.indexOf(window.parent.menuId) >= 0) {
              if (window.parent.numMenus === window.parent.inMenus.length) {
                tmpMenu.innerHTML = `<span class="badge badge-info">${Joomla.JText._('JALL')}</span>`;

                if (tmpRow.classList.contains('no') || tmpRow.classList.length === 0) {
                  tmpRow.classList.remove('no');
                }
              } else {
                tmpMenu.innerHTML = `<span class="badge badge-success">${Joomla.JText._('JYES')}</span>`;

                if (tmpRow.classList.contains('no')) {
                  tmpRow.classList.remove('no');
                }
              }
            }

            if (window.parent.inMenus.indexOf(window.parent.menuId) < 0) {
              tmpMenu.innerHTML = `<span class="badge badge-danger">${Joomla.JText._('JNO')}</span>`;

              if (!tmpRow.classList.contains('no')) {
                tmpRow.classList.add('no');
              }
            }
          }

          if (parseInt(updMenus, 10) < 0) {
            if (window.parent.inMenus.indexOf(window.parent.menuId) >= 0) {
              if (window.parent.numMenus === window.parent.inMenus.length) {
                tmpMenu.innerHTML = `<span class="badge badge-info">${Joomla.JText._('JALL')}</span>`;

                if (tmpRow.classList.contains('no')) {
                  tmpRow.classList.remove('no');
                }
              } else {
                tmpMenu.innerHTML = `<span class="badge badge-success">${Joomla.JText._('JYES')}</span>`;

                if (tmpRow.classList.contains('no')) {
                  tmpRow.classList.remove('no');
                }
              }
            }

            if (window.parent.inMenus.indexOf(window.parent.menuId) < 0) {
              tmpMenu.innerHTML = `<span class="badge badge-danger">${Joomla.JText._('JNO')}</span>`;

              if (!tmpRow.classList.contains('no') || tmpRow.classList.length === 0) {
                tmpRow.classList.add('no');
              }
            }
          }

          if (parseInt(updStatus, 10) === 1) {
            tmpStatus.innerHTML = `<span class="badge badge-success">${Joomla.JText._('JYES')}</span>`;

            if (tmpRow.classList.contains('unpublished')) {
              tmpRow.classList.remove('unpublished');
            }
          }

          if (parseInt(updStatus, 10) === 0) {
            tmpStatus.innerHTML = `<span class="badge badge-danger">${Joomla.JText._('JNO')}</span>`;

            if (!tmpRow.classList.contains('unpublished') || tmpRow.classList.length === 0) {
              tmpRow.classList.add('unpublished');
            }
          }

          if (parseInt(updStatus, 10) === -2) {
            tmpStatus.innerHTML = `<span class="badge badge-default">${Joomla.JText._('JTRASHED')}</span>`;

            if (!tmpRow.classList.contains('unpublished') || tmpRow.classList.length === 0) {
              tmpRow.classList.add('unpublished');
            }
          }

          if (document.formvalidator.isValid(document.getElementById('module-form'))) {
            window.parent.document.querySelector(`#title-${options.itemId}`).innerText = updTitle;
            window.parent.document.querySelector(`#position-${options.itemId}`).innerText = updPosition;
            window.parent.document.querySelector(`#access-${options.itemId}`).innerHTML = window.parent.viewLevels[updAccess];
          }
        }

        if (task !== 'module.apply') {
          window.parent.Joomla.Modal.getCurrent().close();
        }
      }
    }
  };
})(Joomla);