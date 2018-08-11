/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  /** Get the elements * */

  var modulesLinks = [].slice.call(document.querySelectorAll('.js-module-insert'));
  var positionsLinks = [].slice.call(document.querySelectorAll('.js-position-insert'));

  /** Assign listener for click event (for single module insertion) * */
  modulesLinks.forEach(function (modulesLink) {
    modulesLink.addEventListener('click', function (event) {
      event.preventDefault();
      var type = event.target.getAttribute('data-module');
      var name = event.target.getAttribute('data-title');
      var editor = event.target.getAttribute('data-editor');

      // Insert the short tag in the editor
      window.parent.Joomla.editors.instances[editor].replaceSelection('{loadmodule ' + type + ',' + name + '}');

      // Close the modal
      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });

  /** Assign listener for click event (for position insertion) * */
  positionsLinks.forEach(function (positionsLink) {
    positionsLink.addEventListener('click', function (event) {
      event.preventDefault();
      var position = event.target.getAttribute('data-position');
      var editor = event.target.getAttribute('data-editor');

      // Insert the short tag in the editor
      window.parent.Joomla.editors.instances[editor].replaceSelection('{loadposition ' + position + '}');

      // Close the modal
      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });
});
