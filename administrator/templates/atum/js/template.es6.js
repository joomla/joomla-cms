/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable max-len

// Only define the Joomla namespace if not defined.
window.Joomla = window.Joomla || {};

/**
 * Method that add a fade effect and transition on sidebar and content side after login and logout
 * working with session data, to add the animation just in that two cases
 *
 * @since   4.0.0
 */
Joomla.fadeEffect = () => {
  const logoutBtn = document.querySelector('.header-items a[href*="task=logout"]');

  if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
      letsFade('out', 'wider');
    });
  }

  letsFade('in');

  function letsFade(fadeAction, transitAction) {
    const sideBar = document.querySelector('.sidebar-wrapper');
    const sidebarChildren = sideBar.children;
    const sideChildrenLength = sidebarChildren.length;
	const contentMain = document.querySelector('.container-main');
    const contentChildren = contentMain.children;
    const contChildrenLength = contentChildren.length;

    for (let i = 0; i < sideChildrenLength; i++) {
      sidebarChildren[i].classList.add('load-fade' + fadeAction);
    }
    for (let i = 0; i < contChildrenLength; i++) {
      contentChildren[i].classList.add('load-fade' + fadeAction);
    }
    if (transitAction) {
      sideBar.classList.add('transit-' + transitAction);
    }

    sideBar.classList.toggle('fade-done', fadeAction !== 'out');
    contentMain.classList.toggle('fade-done', fadeAction !== 'out');
  }
};

/** Load Method for fade effect after login and logout */
document.addEventListener('DOMContentLoaded', Joomla.fadeEffect);
