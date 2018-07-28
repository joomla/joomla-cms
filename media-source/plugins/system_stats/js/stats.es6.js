/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      3.5.0
 */

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    // Toggle stats details
    document.getElementById('js-pstats-data-details-toggler').addEventListener('click', (event) => {
      event.preventDefault();
      const element = document.getElementById('js-pstats-data-details');
      if (element) {
        element.classList.toggle('d-none');
      }
    });

    // Reset the unique id
    document.getElementById('js-pstats-reset-uid').addEventListener('click', (event) => {
      event.preventDefault();
      document.getElementById('jform_params_unique_id').value = '';
      Joomla.submitbutton('plugin.apply');
    });
  });
})(document, Joomla);
