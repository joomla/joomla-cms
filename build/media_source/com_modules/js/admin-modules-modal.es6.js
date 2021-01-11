/**
  * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
  * @license    GNU General Public License version 2 or later; see LICENSE.txt
  */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // Get the elements
  const modulesLinks = [].slice.call(document.querySelectorAll('.js-module-insert'));
  const positionsLinks = [].slice.call(document.querySelectorAll('.js-position-insert'));

  // Assign listener for click event (for single module id insertion)
  modulesLinks.forEach((element) => {
    element.addEventListener('click', (event) => {
      event.preventDefault();
      const modid = event.target.getAttribute('data-module');
      const editor = event.target.getAttribute('data-editor');

      // Use the API
      if (window.parent.Joomla && window.parent.Joomla.editors
        && window.parent.Joomla.editors.instances
        && Object.prototype.hasOwnProperty.call(window.parent.Joomla.editors.instances, editor)) {
        window.parent.Joomla.editors.instances[editor].replaceSelection(`{loadmoduleid ${modid}}`);
      }

      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });

  // Assign listener for click event (for position insertion)
  positionsLinks.forEach((element) => {
    element.addEventListener('click', (event) => {
      event.preventDefault();
      const position = event.target.getAttribute('data-position');
      const editor = event.target.getAttribute('data-editor');

      // Use the API
      if (window.Joomla && window.Joomla.editors && Joomla.editors.instances
        && Object.prototype.hasOwnProperty.call(window.parent.Joomla.editors.instances, editor)) {
        window.parent.Joomla.editors.instances[editor].replaceSelection(`{loadposition ${position}}`);
      }

      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });
});
