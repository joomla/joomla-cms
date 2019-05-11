/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  /** Get the elements * */
  const modulesLinks = [].slice.call(document.querySelectorAll('.js-module-insert'));
  const positionsLinks = [].slice.call(document.querySelectorAll('.js-position-insert'));

  /** Assign listener for click event (for single module id insertion) * */
  modulesLinks.forEach((modulesLink) => {
    modulesLink.addEventListener('click', (event) => {
      event.preventDefault();
      const modid = event.target.getAttribute('data-module');
      const editor = event.target.getAttribute('data-editor');

      // Insert the short tag in the editor
      window.parent.Joomla.editors.instances[editor].replaceSelection(`{loadmoduleid ${modid}}`);

      // Close the modal
      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });

  /** Assign listener for click event (for position insertion) * */
  positionsLinks.forEach((positionsLink) => {
    positionsLink.addEventListener('click', (event) => {
      event.preventDefault();
      const position = event.target.getAttribute('data-position');
      const editor = event.target.getAttribute('data-editor');

      // Insert the short tag in the editor
      window.parent.Joomla.editors.instances[editor].replaceSelection(`{loadposition ${position}}`);

      // Close the modal
      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });
});
