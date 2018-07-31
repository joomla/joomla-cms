/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(( Joomla, window) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    if (Joomla.getOptions('modal-associations')) {
      const itemId = Joomla.getOptions('modal-associations').itemId;

      // @TODO function should not be global, move it to Joomla
      window['jSelectAssociation_' + itemId] = (id) => {
        const target = document.getElementById('target-association');

        if (target) {
          target.setAttribute('src',
            `${target.getAttribute('data-editurl')}&task=${target.getAttribute('data-item')}.edit&id=${id}`);
        }

        Joomla.Modal.getCurrent().close();
      }
    }
  });
})(Joomla, window);
