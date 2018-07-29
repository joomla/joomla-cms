/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    /** Get the elements * */
    const modulesLinks = [].slice.call(document.querySelectorAll('.js-module-insert'));
    const positionsLinks = [].slice.call(document.querySelectorAll('.js-position-insert'));

    /** Assign listener for click event (for single module insertion) * */
    modulesLinks.forEach((module) => {
      module.addEventListener('click', (event) => {
        event.preventDefault();
        const type = event.target.getAttribute('data-module');
        const name = event.target.getAttribute('data-title');
        const editor = event.target.getAttribute('data-editor');

        window.parent.Joomla.editors.instances[editor].replaceSelection(`{loadmodule ${type},${name}}`);

        if (window.parent.Joomla.currentModal) {
          window.parent.Joomla.Modal.getCurrent().close();
        }
      });
    });

    /** Assign listener for click event (for position insertion) * */
    positionsLinks.forEach((module) => {
      module.addEventListener('click', (event) => {
        event.preventDefault();
        const position = event.target.getAttribute('data-position');
        const editor = event.target.getAttribute('data-editor');

        window.parent.Joomla.editors.instances[editor].replaceSelection(`{loadposition ${position}}`);

        if (window.parent.Joomla.currentModal) {
          window.parent.Joomla.Modal.getCurrent().close();
        }
      });
    });
  });
})();
