/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      3.5.0
 */

(function (document) {
  'use strict';

  // Selectors used by this script
  var statsDataTogglerId = 'js-pstats-data-details-toggler';
  var statsDataDetailsId = 'js-pstats-data-details';
  var resetId = 'js-pstats-reset-uid';
  var uniqueIdFieldId = 'jform_params_unique_id';

  document.addEventListener('DOMContentLoaded', function () {
    // Toggle stats details
    document.getElementById(statsDataTogglerId).addEventListener('click', function (event) {
      event.preventDefault();
      var element = document.getElementById(statsDataDetailsId);
      if (element) {
        element.classList.toggle('d-none');
      }
    });

    // Reset the unique id
    document.getElementById(resetId).addEventListener('click', function (event) {
      event.preventDefault();
      document.getElementById(uniqueIdFieldId).value = '';
      Joomla.submitbutton('plugin.apply');
    });
  });
})(document, Joomla);
