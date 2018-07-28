/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  Joomla.submitbutton = (task) => {
    if (task === 'module.cancel' || document.formvalidator.isValid(document.getElementById('module-form'))) {
      Joomla.submitform(task, document.getElementById('module-form'));

      const options = Joomla.getOptions('module-edit');

      if (self !== top) {
        if (parent.viewLevels) {
          const updPosition = jQuery('#jform_position').chosen().val();


          const updTitle = document.getElementById('jform_title').value;


          const updMenus = jQuery('#jform_assignment').chosen().val();


          const updStatus = jQuery('#jform_published').chosen().val();


          const updAccess = jQuery('#jform_access').chosen().val();


          const tmpMenu = parent.document.getElementById(`menus-${options.itemId}`);


          const tmpRow = parent.document.getElementById(`tr-${options.itemId}`);


          const tmpStatus = parent.document.getElementById(`status-${options.itemId}`);
          window.parent.inMenus = [];
          window.parent.numMenus = document.querySelectorAll('input[name="jform[assigned][]"]').length;

          jQuery('input[name="jform[assigned][]"]').each(function () {
            if (updMenus > 0) {
              if (jQuery(this).is(':checked')) {
                window.parent.inMenus.push(parseInt(jQuery(this).val()));
              }
            }
            if (updMenus < 0) {
              if (!jQuery(this).is(':checked')) {
                window.parent.inMenus.push(parseInt(jQuery(this).val()));
              }
            }
          });
          if (updMenus == 0) {
            tmpMenu.innerHTML = `<span class=\"badge badge-info\">${Joomla.JText._('JALL')}</span>`;
            if (tmpRow.classList.contains('no')) { tmpRow.classList.remove('no'); }
          }
          if (updMenus == '-') {
            tmpMenu.innerHTML = `<span class=\"badge badge-danger\">${Joomla.JText._('JNO')}</span>`;
            if (!tmpRow.classList.contains('no') || tmpRow.classList.length === 0) { tmpRow.classList.add('no'); }
          }
          if (updMenus > 0) {
            if (window.parent.inMenus.indexOf(parent.menuId) >= 0) {
              if (window.parent.numMenus == window.parent.inMenus.length) {
                tmpMenu.innerHTML = `<span class=\"badge badge-info\">${Joomla.JText._('JALL')}</span>`;
                if (tmpRow.classList.contains('no') || tmpRow.classList.length === 0) { tmpRow.classList.remove('no'); }
              } else {
                tmpMenu.innerHTML = `<span class=\"badge badge-success\">${Joomla.JText._('JYES')}</span>`;
                if (tmpRow.classList.contains('no')) { tmpRow.classList.remove('no'); }
              }
            }
            if (window.parent.inMenus.indexOf(parent.menuId) < 0) {
              tmpMenu.innerHTML = `<span class=\"badge badge-danger\">${Joomla.JText._('JNO')}</span>`;
              if (!tmpRow.classList.contains('no')) { tmpRow.classList.add('no'); }
            }
          }
          if (updMenus < 0) {
            if (window.parent.inMenus.indexOf(parent.menuId) >= 0) {
              if (window.parent.numMenus == window.parent.inMenus.length) {
                tmpMenu.innerHTML = `<span class=\"badge badge-info\">${Joomla.JText._('JALL')}</span>`;
                if (tmpRow.classList.contains('no')) { tmpRow.classList.remove('no'); }
              } else {
                tmpMenu.innerHTML = `<span class=\"badge badge-success\">${Joomla.JText._('JYES')}</span>`;
                if (tmpRow.classList.contains('no')) { tmpRow.classList.remove('no'); }
              }
            }
            if (window.parent.inMenus.indexOf(parent.menuId) < 0) {
              tmpMenu.innerHTML = `<span class=\"badge badge-danger\">${Joomla.JText._('JNO')}</span>`;
              if (!tmpRow.classList.contains('no') || tmpRow.classList.length === 0) { tmpRow.classList.add('no'); }
            }
          }
          if (updStatus == 1) {
            tmpStatus.innerHTML = `<span class=\"badge badge-success\">${Joomla.JText._('JYES')}</span>`;
            if (tmpRow.classList.contains('unpublished')) { tmpRow.classList.remove('unpublished'); }
          }
          if (updStatus == 0) {
            tmpStatus.innerHTML = `<span class=\"badge badge-danger\">${Joomla.JText._('JNO')}</span>`;
            if (!tmpRow.classList.contains('unpublished') || tmpRow.classList.length === 0) { tmpRow.classList.add('unpublished'); }
          }
          if (updStatus == -2) {
            tmpStatus.innerHTML = `<span class=\"badge badge-default\">${Joomla.JText._('JTRASHED')}</span>`;
            if (!tmpRow.classList.contains('unpublished') || tmpRow.classList.length === 0) { tmpRow.classList.add('unpublished'); }
          }
          if (document.formvalidator.isValid(document.getElementById('module-form'))) {
            jQuery(`#title-${options.itemId}`, parent.document).text(updTitle);
            jQuery(`#position-${options.itemId}`, parent.document).text(updPosition);
            jQuery(`#access-${options.itemId}`, parent.document).innerHTML = parent.viewLevels[updAccess];
          }
        }
      }

      if (task !== 'module.apply') {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    }
  };
})(Joomla);
