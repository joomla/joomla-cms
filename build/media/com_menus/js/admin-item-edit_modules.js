/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function () {
  'use strict';

  const options = Joomla.getOptions('menus-edit-modules');

  if (options) {
    window.viewLevels = options.viewLevels;
    window.menuId = parseInt(options.itemId);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const baseLink = 'index.php?option=com_modules&amp;client_id=0&amp;task=module.edit&amp;tmpl=component&amp;view=module&amp;layout=modal&amp;id=';


    const iFrameAttr = 'class="iframe jviewport-height70"';

    document.getElementById('jform_toggle_modules_assigned1').addEventListener('click', (event) => {
      const list = document.querySelectorAll('tr.no');
      list.forEach((item) => {
        item.style.display = 'table-row';
      });
    });

    document.getElementById('jform_toggle_modules_assigned0').addEventListener('click', (event) => {
      const list = document.querySelectorAll('tr.no');
      list.forEach((item) => {
        item.style.display = 'none';
      });
    });

    document.getElementById('jform_toggle_modules_published1').addEventListener('click', (event) => {
      const list = document.querySelectorAll('.table tr.unpublished');
      list.forEach((item) => {
        item.style.display = 'table-row';
      });
    });

    document.getElementById('jform_toggle_modules_published0').addEventListener('click', (event) => {
      const list = document.querySelectorAll('.table tr.unpublished');
      list.forEach((item) => {
        item.style.display = 'none';
      });
    });

    // TODO: Dimitris Help me!!!!!
    const linkElements = document.getElementsByClassName('module-edit-link');

    for (let i = 0; i < linkElements.length; i++) {
      linkElements[i].addEventListener('click', function (event) {
        const link = baseLink + jQuery(this).data('moduleId');


        const iFrame = jQuery(`<iframe src="${link}" ${iFrameAttr}></iframe>`);

        jQuery('#moduleEditModal').modal()
          .find('.modal-body').empty()
          .prepend(iFrame);
      });
    }

    const targetDiv = document.getElementById('moduleEditModal').getElementsByClassName('bar')[0];
    jQuery(document)
		.on('click', '#moduleEditModal .modal-footer .btn', function () {
        const target = jQuery(this).data('target');

        if (target) {
          jQuery('#moduleEditModal iframe').contents().find(target).click();
        }
      });
  });
}());
