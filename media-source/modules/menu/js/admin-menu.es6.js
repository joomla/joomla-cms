/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};
((Joomla, document) => {
  'use strict';

  const closest = (element, selector) => {
    let matchesFn;

    // find vendor prefix
    ['matches', 'msMatchesSelector'].some((fn) => {
      if (typeof document.body[fn] === 'function') {
        matchesFn = fn;
        return true;
      }
      return false;
    });

    let parent;

    // Traverse parents
    while (element) {
      parent = element.parentElement;
      if (parent && parent[matchesFn](selector)) {
        return parent;
      }
      // eslint-disable-next-line no-param-reassign
      element = parent;
    }

    return null;
  };

  const wrapper = document.getElementById('wrapper');
  const sidebar = document.getElementById('sidebar-wrapper');
  const menuToggleIcon = document.getElementById('menu-collapse-icon');
  const body = document.body;

  // Set the initial state of the sidebar based on the localStorage value
  if (Joomla.localStorageEnabled()) {
    const sidebarState = localStorage.getItem('atum-sidebar');
    if (sidebarState === 'open' || sidebarState === null) {
      wrapper.classList.remove('closed');
      menuToggleIcon.classList.remove('fa-toggle-off');
      menuToggleIcon.classList.add('fa-toggle-on');
      localStorage.setItem('atum-sidebar', 'open');
    } else {
      wrapper.classList.add('closed');
      menuToggleIcon.classList.remove('fa-toggle-on');
      menuToggleIcon.classList.add('fa-toggle-off');
      localStorage.setItem('atum-sidebar', 'closed');
    }
  }

  // If the sidebar doesn't exist, for example, on edit views, then remove the "closed" class
  if (!sidebar) {
    wrapper.classList.remove('closed');
  }

  if (sidebar && !sidebar.getAttribute('data-hidden')) {
    // Sidebar
    const menuToggle = document.getElementById('menu-collapse');
    const first = [].slice.call(sidebar.querySelectorAll('.collapse-level-1'));

    // Apply 2nd level collapse
    first.forEach((element) => {
      const second = [].slice.call(element.querySelectorAll('.collapse-level-1'));
      second.forEach((el) => {
        if (el) {
          el.classList.remove('collapse-level-1');
          el.classList.add('collapse-level-2');
        }
      });
    });

    const menuClose = () => {
      sidebar.querySelector('.collapse').classList.remove('in');
      sidebar.querySelector('.collapse-arrow').classList.add('collapsed');
    };

    // Toggle menu
    menuToggle.addEventListener('click', () => {
      wrapper.classList.toggle('closed');
      menuToggleIcon.classList.toggle('fa-toggle-on');
      menuToggleIcon.classList.toggle('fa-toggle-off');

      const listItems = [].slice.call(document.querySelectorAll('.main-nav > li'));
      listItems.forEach((item) => {
        item.classList.remove('open');
      });

      const elem = document.querySelector('.child-open');
      if (elem) {
        elem.classList.remove('child-open');
      }

      // Save the sidebar state
      if (Joomla.localStorageEnabled()) {
        if (wrapper.classList.contains('closed')) {
          localStorage.setItem('atum-sidebar', 'closed');
        } else {
          localStorage.setItem('atum-sidebar', 'open');
        }
      }
    });


    /**
     * Sidebar Nav
     */
    const allLinks = [].slice.call(wrapper.querySelectorAll('a.no-dropdown, a.collapse-arrow'));
    const currentUrl = window.location.href.toLowerCase();
    const mainNav = document.getElementById('menu');
    const menuParents = [].slice.call(mainNav.querySelectorAll('li.parent > a'));
    const subMenuClose = [].slice.call(mainNav.querySelectorAll('li.parent .close'));

    // Set active class
    allLinks.forEach((link) => {
      if (currentUrl === link.href) {
        link.classList.add('active');
        // Auto Expand First Level
        if (!link.parentNode.classList.contains('parent')) {
          mainNav.classList.add('child-open');
          const firstLevel = closest(link, '.collapse-level-1');
          if (firstLevel) {
            firstLevel.parentNode.classList.add('open');
          }
        }
      }
    });

    // If com_cpanel or com_media - close menu
    if (body.classList.contains('com_cpanel') || body.classList.contains('com_media')) {
      const menuChildOpen = [].slice.call(mainNav.querySelectorAll('.open'));

      menuChildOpen.forEach((child) => {
        child.classList.remove('open');
      });
      mainNav.classList.remove('child-open');
    }

    // Child open toggle
    const openToggle = () => {
      const menuItem = this.parentNode;

      if (menuItem.classList.contains('open')) {
        mainNav.classList.remove('child-open');
        menuItem.classList.remove('open');
      } else {
        const siblings = [].slice.call(menuItem.parentNode.children);
        siblings.forEach((element) => {
          element.classList.remove('open');
        });

        wrapper.classList.remove('closed');
        mainNav.classList.add('child-open');
        if (menuItem.parentNode.classList.contains('main-nav')) {
          menuItem.classList.add('open');
        }
      }
    };

    menuParents.forEach((element) => {
      element.addEventListener('click', openToggle);
      element.addEventListener('keyup', openToggle);
    });

    // Menu close
    subMenuClose.forEach((element) => {
      element.addEventListener('click', () => {
        const menuChildOpen = [].slice.call(mainNav.querySelectorAll('.open'));

        menuChildOpen.forEach((elem) => {
          elem.classList.remove('open');
        });

        mainNav.classList.remove('child-open');
      });
    });

    // Accessibility
    const allLiEl = [].slice.call(sidebar.querySelectorAll('ul[role="menubar"] li'));
    allLiEl.forEach((element) => {
      // We care for enter and space
      element.addEventListener('keyup', (e) => {
        if (e.keyCode === 32 || e.keyCode === 13) {
          e.target.querySelector('a').click();
        }
      });
    });

    // Set the height of the menu to prevent overlapping
    const setMenuHeight = () => {
      const height = document.getElementById('header').offsetHeight + document.getElementById('main-brand').offsetHeight;
      mainNav.height = window.height - height;
    };

    setMenuHeight();

    // Remove 'closed' class on resize
    window.addEventListener('resize', () => {
      setMenuHeight();
    });

    if (Joomla.localStorageEnabled()) {
      if (localStorage.getItem('adminMenuState') === 'true') {
        menuClose();
      }
    }
  } else {
    if (sidebar) {
      sidebar.style.display = 'none';
      sidebar.style.width = 0;
    }

    const wrapperClass = document.getElementsByClassName('wrapper');
    if (wrapperClass.length) {
      wrapperClass[0].style.paddingLeft = 0;
    }
  }
})(Joomla, document);
