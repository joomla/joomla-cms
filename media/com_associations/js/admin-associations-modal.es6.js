/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    if (Joomla.getOptions('modal-associations')) {
      const fnName = Joomla.getOptions('modal-associations').func;
      const links = [].slice.call(document.querySelectorAll('.select-link'));

      links.forEach((item) => {
        item.addEventListener('click', (event) => {
          // eslint-disable-next-line no-restricted-globals
          if (self !== top) {
            // Run function on parent window.
            window.parent[fnName](event.target.getAttribute('data-id'));
          }
        });
      });
    }
  });
})(Joomla, document);
