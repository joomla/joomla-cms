/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  const initTemplate = (event) => {
    const target = event && event.target ? event.target : document;
    /**
     * Prevent clicks on buttons within a disabled fieldset
     */
    const fieldSets = [].slice.call(target.querySelectorAll('fieldset.btn-group'));
    fieldSets.forEach((fieldSet) => {
      if (fieldSet.getAttribute('disabled') === true) {
        fieldSet.style.pointerEvents = 'none';
        const buttons = [].slice.call(fieldSet.querySelectorAll('.btn'));
        buttons.forEach((button) => {
          button.classList.add('disabled');
        });
      }
    });
  };

  document.addEventListener('DOMContentLoaded', (event) => {
    initTemplate(event);

    /**
     * Back to top
     */
    const backToTop = document.getElementById('back-top');
    if (backToTop) {
      backToTop.addEventListener('click', (evt) => {
        evt.preventDefault();
        window.scrollTo(0, 0);
      });
    }
  });

  /**
   * Initialize when a part of the page was updated
   */
  document.addEventListener('joomla:updated', initTemplate);
})(Joomla, document);
