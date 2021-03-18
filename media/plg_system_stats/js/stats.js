/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      3.5.0
 */
(function (document) {
  'use strict'; // Selectors used by this script

  var statsDataTogglerId = 'js-pstats-data-details-toggler';
  var statsDataDetailsId = 'js-pstats-data-details';
  var resetId = 'js-pstats-reset-uid';
  var uniqueIdFieldId = 'jform_params_unique_id';

  var onToggle = function onToggle(event) {
    event.preventDefault();
    var element = document.getElementById(statsDataDetailsId);

    if (element) {
      element.classList.toggle('d-none');
    }
  };

  var onReset = function onReset(event) {
    event.preventDefault();
    document.getElementById(uniqueIdFieldId).value = '';
    Joomla.submitbutton('plugin.apply');
  };

  var onBoot = function onBoot() {
    // Toggle stats details
    var toggler = document.getElementById(statsDataTogglerId);

    if (toggler) {
      toggler.addEventListener('click', onToggle);
    } // Reset the unique id


    var reset = document.getElementById(resetId);

    if (reset) {
      reset.addEventListener('click', onReset);
    } // Cleanup


    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(document, Joomla);