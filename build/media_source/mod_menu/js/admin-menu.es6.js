/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.querySelectorAll('ul.main-nav').forEach((menu) => {
  // eslint-disable-next-line no-new, no-undef
  new MetisMenu(menu);
});

const wrapper = document.getElementById('wrapper');
const sidebar = document.getElementById('sidebar-wrapper');
const menuToggleIcon = document.getElementById('menu-collapse-icon');

// If the sidebar doesn't exist, for example, on edit views, then remove the "closed" class
if (!sidebar) {
  wrapper.classList.remove('closed');
}

if (sidebar && !sidebar.getAttribute('data-hidden')) {
  // Sidebar
  const menuToggle = document.getElementById('menu-collapse');

  // Apply 2nd level collapse
  sidebar.querySelectorAll('.collapse-level-1').forEach((first) => {
    first.querySelectorAll('.collapse-level-1').forEach((second) => {
      if (second) {
        second.classList.remove('collapse-level-1');
        second.classList.add('collapse-level-2');
      }
    });
  });

  // Toggle menu
  menuToggle.addEventListener('click', (event) => {
    event.preventDefault();
    wrapper.classList.toggle('closed');
    menuToggleIcon.classList.toggle('icon-toggle-on');
    menuToggleIcon.classList.toggle('icon-toggle-off');

    document.querySelectorAll('.main-nav > li').forEach((item) => item.classList.remove('open'));

    const elem = document.querySelector('.child-open');
    if (elem) {
      elem.classList.remove('child-open');
    }

    window.dispatchEvent(new CustomEvent('joomla:menu-toggle', {
      detail: wrapper.classList.contains('closed') ? 'closed' : 'open',
      bubbles: true,
      cancelable: true,
    }));
  });

  // Sidebar Nav
  const currentUrl = window.location.href;
  const mainNav = document.querySelector('ul.main-nav');

  // Set active class
  wrapper.querySelectorAll('a.no-dropdown, a.collapse-arrow, .menu-dashboard > a').forEach((link) => {
    if (
      (!link.href.match(/index\.php$/) && currentUrl.indexOf(link.href) === 0)
      || (link.href.match(/index\.php$/) && currentUrl.match(/index\.php$/))) {
      link.setAttribute('aria-current', 'page');
      link.classList.add('mm-active');

      // Auto Expand Levels
      if (!link.parentNode.classList.contains('parent')) {
        const firstLevel = link.closest('.collapse-level-1');
        const secondLevel = link.closest('.collapse-level-2');
        if (firstLevel) firstLevel.parentNode.classList.add('mm-active');
        if (firstLevel) firstLevel.classList.add('mm-show');
        if (secondLevel) secondLevel.parentNode.classList.add('mm-active');
        if (secondLevel) secondLevel.classList.add('mm-show');
      }
    }
  });

  // Child open toggle
  const openToggle = ({ currentTarget }) => {
    let menuItem = currentTarget.parentNode;

    if (menuItem.tagName.toLowerCase() === 'span') {
      menuItem = currentTarget.parentNode.parentNode;
    }

    if (menuItem.classList.contains('open')) {
      mainNav.classList.remove('child-open');
      menuItem.classList.remove('open');
    } else {
      const siblings = [].slice.call(menuItem.parentNode.children);
      siblings.forEach((sibling) => {
        sibling.classList.remove('open');
      });

      wrapper.classList.remove('closed');
      if (menuToggleIcon.classList.contains('icon-toggle-off')) {
        menuToggleIcon.classList.toggle('icon-toggle-off');
        menuToggleIcon.classList.toggle('icon-toggle-on');
      }
      mainNav.classList.add('child-open');

      if (menuItem.parentNode.classList.contains('main-nav')) {
        menuItem.classList.add('open');
      }
    }

    window.dispatchEvent(new CustomEvent('joomla:menu-toggle', {
      detail: 'open',
      bubbles: true,
      cancelable: true,
    }));
  };

  document.querySelectorAll('ul.main-nav li.parent > a').forEach((parent) => {
    parent.addEventListener('click', openToggle);
    parent.addEventListener('keyup', openToggle);
  });

  // Menu close
  document.querySelectorAll('ul.main-nav li.parent .close').forEach((subMenu) => {
    subMenu.addEventListener('click', () => {
      mainNav.querySelectorAll('.open').forEach((menuChild) => menuChild.classList.remove('open'));
      mainNav.classList.remove('child-open');
    });
  });
}
