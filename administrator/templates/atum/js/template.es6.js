/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document) => {
  'use strict';

  /**
   * Method that add a fade effect and transition on sidebar and content side after login and logout
   *
   * @since   4.0.0
   */
  function fade(fadeAction, transitAction) {
    const sidebar = document.querySelector('.sidebar-wrapper');
    const sidebarChildren = sidebar ? sidebar.children : [];
    const sideChildrenLength = sidebarChildren.length;
	const contentMain = document.querySelector('.container-main');
    const contentChildren = contentMain ? contentMain.children : [];
    const contChildrenLength = contentChildren.length;

    for (let i = 0; i < sideChildrenLength; i += 1) {
      sidebarChildren[i].classList.add(`load-fade${fadeAction}`);
    }
    for (let i = 0; i < contChildrenLength; i += 1) {
      contentChildren[i].classList.add(`load-fade${fadeAction}`);
    }
	if (sidebar) {
	  if (transitAction) {
	    sidebar.classList.add(`transit-${transitAction}`);
	  }

	  sidebar.classList.toggle('fade-done', fadeAction !== 'out');
	}
	if (contentMain) {
	  contentMain.classList.toggle('fade-done', fadeAction !== 'out');
	}
  }

  document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('form-login');
    const logoutBtn = document.querySelector('.header-items a[href*="task=logout"]');

    // Fade out login form when login was successful
    if (loginForm) {
      loginForm.addEventListener('joomla:login', () => {
        fade('out', 'narrow');
      });
    }

    // Fade in dashboard when coming from login or going back to login
    fade('in');

    // Fade out dashboard on logout
    if (logoutBtn) {
      logoutBtn.addEventListener('click', () => {
        fade('out', 'wider');
      });
    }
  });
})(window.Joomla, document);
