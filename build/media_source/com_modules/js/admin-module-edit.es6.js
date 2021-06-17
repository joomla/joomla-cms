/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  Joomla.submitbutton = (task) => {
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

          [].slice.call(document.querySelectorAll('input[name="jform[assigned][]"]')).forEach((element) => {
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
            tmpMenu.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-danger">${Joomla.Text._('JNO')}</span>`);
            if (!tmpRow.classList.contains('no') || tmpRow.classList.length === 0) { tmpRow.classList.add('no'); }
          }
          if (parseInt(updMenus, 10) === 0) {
            tmpMenu.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-info">${Joomla.Text._('JALL')}</span>`);
            if (tmpRow.classList.contains('no')) { tmpRow.classList.remove('no'); }
          }
          if (parseInt(updMenus, 10) > 0) {
            if (window.parent.inMenus.indexOf(window.parent.menuId) >= 0) {
              if (window.parent.numMenus === window.parent.inMenus.length) {
                tmpMenu.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-info">${Joomla.Text._('JALL')}</span>`);
                if (tmpRow.classList.contains('no') || tmpRow.classList.length === 0) { tmpRow.classList.remove('no'); }
              } else {
                tmpMenu.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-success">${Joomla.Text._('JYES')}</span>`);
                if (tmpRow.classList.contains('no')) { tmpRow.classList.remove('no'); }
              }
            }
            if (window.parent.inMenus.indexOf(window.parent.menuId) < 0) {
              tmpMenu.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-danger">${Joomla.Text._('JNO')}</span>`);
              if (!tmpRow.classList.contains('no')) { tmpRow.classList.add('no'); }
            }
          }
          if (parseInt(updMenus, 10) < 0) {
            if (window.parent.inMenus.indexOf(window.parent.menuId) >= 0) {
              if (window.parent.numMenus === window.parent.inMenus.length) {
                tmpMenu.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-info">${Joomla.Text._('JALL')}</span>`);
                if (tmpRow.classList.contains('no')) { tmpRow.classList.remove('no'); }
              } else {
                tmpMenu.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-success">${Joomla.Text._('JYES')}</span>`);
                if (tmpRow.classList.contains('no')) { tmpRow.classList.remove('no'); }
              }
            }
            if (window.parent.inMenus.indexOf(window.parent.menuId) < 0) {
              tmpMenu.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-danger">${Joomla.Text._('JNO')}</span>`);
              if (!tmpRow.classList.contains('no') || tmpRow.classList.length === 0) { tmpRow.classList.add('no'); }
            }
          }
          if (parseInt(updStatus, 10) === 1) {
            tmpStatus.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-success">${Joomla.Text._('JYES')}</span>`);
            if (tmpRow.classList.contains('unpublished')) { tmpRow.classList.remove('unpublished'); }
          }
          if (parseInt(updStatus, 10) === 0) {
            tmpStatus.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-danger">${Joomla.Text._('JNO')}</span>`);
            if (!tmpRow.classList.contains('unpublished') || tmpRow.classList.length === 0) { tmpRow.classList.add('unpublished'); }
          }
          if (parseInt(updStatus, 10) === -2) {
            tmpStatus.innerHTML = Joomla.sanitizeHtml(`<span class="badge bg-secondary">${Joomla.Text._('JTRASHED')}</span>`);
            if (!tmpRow.classList.contains('unpublished') || tmpRow.classList.length === 0) { tmpRow.classList.add('unpublished'); }
          }
          if (document.formvalidator.isValid(document.getElementById('module-form'))) {
            window.parent.document.querySelector(`#title-${options.itemId}`).innerText = updTitle;
            window.parent.document.querySelector(`#position-${options.itemId}`).innerText = updPosition;
            window.parent.document.querySelector(`#access-${options.itemId}`).innerHTML = Joomla.sanitizeHtml(window.parent.viewLevels[updAccess]);
          }
        }

        if (task !== 'module.apply') {
          window.parent.Joomla.Modal.getCurrent().close();
        }
      }
    }
  };
})(Joomla);
