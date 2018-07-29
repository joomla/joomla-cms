/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  // Selectors used by this script
  const debugSectionTogglerSelector = '.dbg-header';
  const toggleTargetAttribute = 'data-debug-toggle';

  /**
   * Toggle an element by id
   * @param id
   */
  const toggle = (id) => {
    const element = document.getElementById(id);
    if (element) {
      element.style.display = (element.style.display === 'block') ? 'none' : 'block';
    }
  };

  /**
   * Register events
   */
  const registerEvents = () => {
    const sectionTogglers = [].slice.call(document.querySelectorAll(debugSectionTogglerSelector));
    sectionTogglers.forEach((toggler) => {
      toggler.addEventListener('click', (event) => {
        event.preventDefault();
        toggle(toggler.getAttribute(toggleTargetAttribute));
      });
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    registerEvents();
  });
})(document);
