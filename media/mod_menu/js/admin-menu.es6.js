/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla, document) => {
  'use strict';
  /**
   * Check if HTML5 localStorage enabled on the browser
   *
   * @since   4.0.0
   */

  Joomla.localStorageEnabled = () => {
    const test = 'joomla-cms';

    try {
      localStorage.setItem(test, test);
      localStorage.removeItem(test);
      return true;
    } catch (e) {
      return false;
    }
  };

  const allMenus = document.querySelectorAll('ul.main-nav');
  allMenus.forEach(menu => {
    // eslint-disable-next-line no-new, no-undef
    new MetisMenu(menu);
  });
  const wrapper = document.getElementById('wrapper');
  const sidebar = document.getElementById('sidebar-wrapper');
  const menuToggleIcon = document.getElementById('menu-collapse-icon'); // Set the initial state of the sidebar based on the localStorage value

  if (Joomla.localStorageEnabled()) {
    const sidebarState = localStorage.getItem('atum-sidebar');

    if (sidebarState === 'open' || sidebarState === null) {
      wrapper.classList.remove('closed');
      menuToggleIcon.classList.remove('fa-toggle-off');
      menuToggleIcon.classList.add('fa-toggle-on');
      localStorage.setItem('atum-sidebar', 'open');
      Joomla.Event.dispatch('joomla:menu-toggle', 'open');
    } else {
      wrapper.classList.add('closed');
      menuToggleIcon.classList.remove('fa-toggle-on');
      menuToggleIcon.classList.add('fa-toggle-off');
      localStorage.setItem('atum-sidebar', 'closed');
      Joomla.Event.dispatch('joomla:menu-toggle', 'closed');
    }
  } // If the sidebar doesn't exist, for example, on edit views, then remove the "closed" class


  if (!sidebar) {
    wrapper.classList.remove('closed');
  }

  if (sidebar && !sidebar.getAttribute('data-hidden')) {
    // Sidebar
    const menuToggle = document.getElementById('menu-collapse');
    const firsts = [].slice.call(sidebar.querySelectorAll('.collapse-level-1')); // Apply 2nd level collapse

    firsts.forEach(first => {
      const seconds = [].slice.call(first.querySelectorAll('.collapse-level-1'));
      seconds.forEach(second => {
        if (second) {
          second.classList.remove('collapse-level-1');
          second.classList.add('collapse-level-2');
        }
      });
    });

    const menuClose = () => {
      sidebar.querySelector('.mm-collapse').classList.remove('mm-collapsed');
    }; // Toggle menu


    menuToggle.addEventListener('click', () => {
      wrapper.classList.toggle('closed');
      menuToggleIcon.classList.toggle('fa-toggle-on');
      menuToggleIcon.classList.toggle('fa-toggle-off');
      const listItems = [].slice.call(document.querySelectorAll('.main-nav > li'));
      listItems.forEach(item => {
        item.classList.remove('open');
      });
      const elem = document.querySelector('.child-open');

      if (elem) {
        elem.classList.remove('child-open');
      } // Save the sidebar state and dispatch event


      const storageEnabled = Joomla.localStorageEnabled();

      if (wrapper.classList.contains('closed')) {
        if (storageEnabled) {
          localStorage.setItem('atum-sidebar', 'closed');
        }

        Joomla.Event.dispatch('joomla:menu-toggle', 'closed');
      } else {
        if (storageEnabled) {
          localStorage.setItem('atum-sidebar', 'open');
        }

        Joomla.Event.dispatch('joomla:menu-toggle', 'open');
      }
    });
    /**
     * Sidebar Nav
     */

    const allLinks = wrapper.querySelectorAll('a.no-dropdown, a.collapse-arrow, .menu-dashboard > a');
    const currentUrl = window.location.href.toLowerCase();
    const mainNav = document.querySelector('ul.main-nav');
    const menuParents = [].slice.call(mainNav.querySelectorAll('li.parent > a'));
    const subMenusClose = [].slice.call(mainNav.querySelectorAll('li.parent .close')); // Set active class

    allLinks.forEach(link => {
      if (currentUrl === link.href) {
        link.setAttribute('aria-current', 'page');
        link.classList.add('mm-active'); // Auto Expand Levels

        if (!link.parentNode.classList.contains('parent')) {
          const firstLevel = link.closest('.collapse-level-1');
          const secondLevel = link.closest('.collapse-level-2');
          if (firstLevel) firstLevel.parentNode.classList.add('mm-active');
          if (firstLevel) firstLevel.classList.add('mm-show');
          if (secondLevel) secondLevel.parentNode.classList.add('mm-active');
          if (secondLevel) secondLevel.classList.add('mm-show');
        }
      }
    }); // Child open toggle

    const openToggle = ({
      currentTarget
    }) => {
      let menuItem = currentTarget.parentNode;

      if (menuItem.tagName.toLowerCase() === 'span') {
        menuItem = currentTarget.parentNode.parentNode;
      }

      if (menuItem.classList.contains('open')) {
        mainNav.classList.remove('child-open');
        menuItem.classList.remove('open');
      } else {
        const siblings = [].slice.call(menuItem.parentNode.children);
        siblings.forEach(sibling => {
          sibling.classList.remove('open');
        });
        wrapper.classList.remove('closed');
        localStorage.setItem('atum-sidebar', 'open');

        if (menuToggleIcon.classList.contains('fa-toggle-off')) {
          menuToggleIcon.classList.toggle('fa-toggle-off');
          menuToggleIcon.classList.toggle('fa-toggle-on');
        }

        mainNav.classList.add('child-open');

        if (menuItem.parentNode.classList.contains('main-nav')) {
          menuItem.classList.add('open');
        }
      }

      Joomla.Event.dispatch('joomla:menu-toggle', 'open');
    };

    menuParents.forEach(parent => {
      parent.addEventListener('click', openToggle);
      parent.addEventListener('keyup', openToggle);
    }); // Menu close

    subMenusClose.forEach(subMenu => {
      subMenu.addEventListener('click', () => {
        const menuChildsOpen = [].slice.call(mainNav.querySelectorAll('.open'));
        menuChildsOpen.forEach(menuChild => {
          menuChild.classList.remove('open');
        });
        mainNav.classList.remove('child-open');
      });
    }); // Accessibility

    const allLiEls = [].slice.call(sidebar.querySelectorAll('ul[role="menubar"] li'));
    allLiEls.forEach(liEl => {
      // We care for enter and space
      liEl.addEventListener('keyup', e => {
        if (e.keyCode === 32 || e.keyCode === 13) {
          e.target.querySelector('a').click();
        }
      });
    });

    if (Joomla.localStorageEnabled()) {
      if (localStorage.getItem('adminMenuState') === 'true') {
        menuClose();
      }
    }
  }
})(window.Joomla, document);