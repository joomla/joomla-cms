/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const wrapper = document.getElementById('wrapper');
const sidebar = document.getElementById('sidebar-wrapper');
const menuToggleIcon = document.getElementById('menu-collapse-icon');

// Strip server-side mm-show/mm-active, then re-apply after init (no visual flash).
// Workaround for MetisMenu to avoid isTransitioning lock when server-side active state is pre-rendered.
document.querySelectorAll('ul.main-nav').forEach(menu => {
  const prerenderedShown = [];
  menu.querySelectorAll('ul.mm-show').forEach(ul => {
    ul.classList.remove('mm-show');
    prerenderedShown.push(ul);
    const li = ul.parentElement;
    if (li) {
      li.classList.remove('mm-active');
    }
  });
  new MetisMenu(menu);

  // Re-apply the pre-rendered active state after MetisMenu has completed its clean initialisation.
  prerenderedShown.forEach(ul => {
    ul.classList.add('mm-show');
    const li = ul.parentElement;
    if (li) {
      li.classList.add('mm-active');

      // MetisMenu set aria-expanded="false" on all triggers during init
      const trigger = li.querySelector(':scope > a');
      if (trigger) {
        trigger.setAttribute('aria-expanded', 'true');
      }
    }
  });
});

// If the sidebar doesn't exist, for example, on edit views, then remove the "closed" class
if (!sidebar) {
  wrapper.classList.remove('closed');
}
if (sidebar && !sidebar.getAttribute('data-hidden')) {
  // Sidebar
  const menuToggle = document.getElementById('menu-collapse');

  // Apply 2nd level collapse
  sidebar.querySelectorAll('.collapse-level-1').forEach(first => {
    first.querySelectorAll('.collapse-level-1').forEach(second => {
      if (second) {
        second.classList.remove('collapse-level-1');
        second.classList.add('collapse-level-2');
      }
    });
  });

  // Toggle menu
  menuToggle.addEventListener('click', event => {
    event.preventDefault();
    wrapper.classList.toggle('closed');
    menuToggleIcon.classList.toggle('icon-toggle-on');
    menuToggleIcon.classList.toggle('icon-toggle-off');
    document.querySelectorAll('.main-nav > li').forEach(item => item.classList.remove('open'));
    const elem = document.querySelector('.child-open');
    if (elem) {
      elem.classList.remove('child-open');
    }
    window.dispatchEvent(new CustomEvent('joomla:menu-toggle', {
      detail: wrapper.classList.contains('closed') ? 'closed' : 'open',
      bubbles: true,
      cancelable: true
    }));
  });

  // Sidebar Nav
  const mainNav = document.querySelector('ul.main-nav');

  // Active path is normally pre-rendered server-side via CssMenu::setActivePath().
  // This client-side fallback only runs if server-side detection failed.
  if (!wrapper.querySelector('.mm-active')) {
    const currentUrl = window.location.href;

    // Set active class
    wrapper.querySelectorAll('a.no-dropdown, .menu-dashboard > a').forEach(link => {
      if (!link.href.match(/index\.php$/) && currentUrl.indexOf(link.href) === 0 || link.href.match(/index\.php$/) && currentUrl.match(/index\.php$/)) {
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
  }

  // Child open toggle
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
      cancelable: true
    }));
  };
  document.querySelectorAll('ul.main-nav li.parent > a').forEach(parent => {
    parent.addEventListener('click', openToggle);
    parent.addEventListener('keyup', openToggle);
  });

  // Menu close
  document.querySelectorAll('ul.main-nav li.parent .close').forEach(subMenu => {
    subMenu.addEventListener('click', () => {
      mainNav.querySelectorAll('.open').forEach(menuChild => menuChild.classList.remove('open'));
      mainNav.classList.remove('child-open');
    });
  });
}
