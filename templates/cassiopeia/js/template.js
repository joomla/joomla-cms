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
(function (document) {
  'use strict';

  var initTemplate = function initTemplate(event) {
    var target = event && event.target ? event.target : document; // Prevent clicks on buttons within a disabled fieldset

    var fieldsets = [].slice.call(target.querySelectorAll('fieldset.btn-group'));
    fieldsets.forEach(function (fieldset) {
      if (fieldset.getAttribute('disabled') === 'true') {
        fieldset.style.pointerEvents = 'none';
        var buttons = [].slice.call(fieldset.querySelectorAll('.btn'));

        if (buttons.length) {
          buttons.forEach(function (button) {
            button.classList.add('disabled');
          });
        }
      }
    });
  };

  document.addEventListener('DOMContentLoaded', function (event) {
    initTemplate(event); // Back to top

    var backToTop = document.getElementById('back-top');

    if (backToTop) {
      backToTop.addEventListener('click', function (evnt) {
        evnt.preventDefault();
        window.scrollTo(0, 0);
      });
    }
  }); // Initialize when a part of the page was updated

  document.addEventListener('joomla:updated', initTemplate);
})(document);