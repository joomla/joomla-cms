/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @package     Joomla.Site
 * @subpackage  Templates.Cassiopeia
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
(document => {
  'use strict';

  const initTemplate = event => {
    const target = event && event.target ? event.target : document; // Prevent clicks on buttons within a disabled fieldset

    const fieldsets = [].slice.call(target.querySelectorAll('fieldset.btn-group'));
    fieldsets.forEach(fieldset => {
      if (fieldset.getAttribute('disabled') === 'true') {
        fieldset.style.pointerEvents = 'none';
        const buttons = [].slice.call(fieldset.querySelectorAll('.btn'));

        if (buttons.length) {
          buttons.forEach(button => {
            button.classList.add('disabled');
          });
        }
      }
    });
  };

  document.addEventListener('DOMContentLoaded', event => {
    initTemplate(event); // Back to top

    const backToTop = document.getElementById('back-top');

    if (backToTop) {
      backToTop.addEventListener('click', evnt => {
        evnt.preventDefault();
        window.scrollTo(0, 0);
      });
    }
  }); // Initialize when a part of the page was updated

  document.addEventListener('joomla:updated', initTemplate);
})(document);