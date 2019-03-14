/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, doc) => {
  'use strict';

  const storageEnabled = typeof Storage !== 'undefined';

  /**
   * Shrink or extend the logo, depending on sidebar
   *
   * @param {string} [change] is the sidebar 'open' or 'closed'
   *
   * @since   4.0.0
   */
  function changeLogo(change) {
    const logo = doc.querySelector('.logo');
    if (!logo) {
      return;
    }

    const state = change
        || (storageEnabled && localStorage.getItem('atum-sidebar'));

    if (state === 'closed') {
      logo.classList.add('small');
    } else {
      logo.classList.remove('small');
    }
  }

  /**
   * Method that add a fade effect and transition on sidebar and content side
   * after login and logout
   *
   * @since   4.0.0
   */
  function fade(fadeAction, transitAction) {
    const sidebar = doc.querySelector('.sidebar-wrapper');
    const sidebarChildren = sidebar ? sidebar.children : [];
    const sideChildrenLength = sidebarChildren.length;
    const contentMain = doc.querySelector('.container-main');
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
        // Transition class depends on the width of the sidebar
        if (storageEnabled
            && localStorage.getItem('atum-sidebar') === 'closed') {
          sidebar.classList.add(`transit-${transitAction}-closed`);
          changeLogo('small');
        } else {
          sidebar.classList.add(`transit-${transitAction}`);
        }
      }
      sidebar.classList.toggle('fade-done', fadeAction !== 'out');
    }
    if (contentMain) {
      contentMain.classList.toggle('fade-done', fadeAction !== 'out');
    }
  }

  doc.addEventListener('DOMContentLoaded', () => {
    const loginForm = doc.getElementById('form-login');
    const logoutBtn = doc.querySelector('.header-items a[href*="task=logout"]');
    const sidebar = doc.querySelector('.sidebar-wrapper');
    const mobile = window.matchMedia("(max-width: 992px)");

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

    // Make logo big or small like the sidebar
    if (!sidebar || mobile.matches) {
      changeLogo('closed');
    } else {
      changeLogo();
    }

    window.addEventListener('joomla:menu-toggle', (event) => {
      if(!mobile.matches) {
        changeLogo(event.detail);
      }
    });

    window.addEventListener('resize', () => {
      if (mobile.matches) {
        changeLogo('closed');
      }
      else{
        changeLogo();
      }
    });
  });

})(window.Joomla, document);
