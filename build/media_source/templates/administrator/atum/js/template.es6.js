/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!Joomla) {
  throw new Error('Joomla API is not initialized');
}

const getCookie = () => document.cookie.length && document.cookie
  .split('; ')
  .find((row) => row.startsWith('atumSidebarState='))
  ?.split('=')[1];

const mobile = window.matchMedia('(max-width: 992px)');
const small = window.matchMedia('(max-width: 575.98px)');
const tablet = window.matchMedia('(min-width: 576px) and (max-width:991.98px)');
const menu = document.querySelector('.sidebar-menu');
const sidebarNav = [].slice.call(document.querySelectorAll('.sidebar-nav'));
const subhead = document.querySelector('#subhead-container');
const wrapper = document.querySelector('.wrapper');
const sidebarWrapper = document.querySelector('.sidebar-wrapper');
const logo = document.querySelector('.logo');
const isLogin = document.querySelector('body.com_login');
const menuToggleIcon = document.getElementById('menu-collapse-icon');
const navDropDownIcon = document.querySelectorAll('.nav-item.dropdown span[class*="icon-angle-"]');
const headerTitleArea = document.querySelector('#header .header-title');
const headerItemsArea = document.querySelector('#header .header-items');
const headerExpandedItems = [].slice.call(headerItemsArea.children).filter((element) => element.classList.contains('header-item'));
const headerCondensedItemContainer = document.getElementById('header-more-items');
const headerCondensedItems = [].slice.call(headerCondensedItemContainer.querySelectorAll('.header-dd-item'));
let headerTitleWidth = headerTitleArea.getBoundingClientRect().width;
const headerItemWidths = headerExpandedItems
  .map((element) => element.getBoundingClientRect().width);

// Get the ellipsis button width
headerCondensedItemContainer.classList.remove('d-none');
// eslint-disable-next-line no-unused-expressions
headerCondensedItemContainer.paddingTop;
const ellipsisWidth = headerCondensedItemContainer.getBoundingClientRect().width;
headerCondensedItemContainer.classList.add('d-none');

/**
 * Shrink or extend the logo, depending on sidebar
 *
 * @param {string} [change] is the sidebar 'open' or 'closed'
 *
 * @since   4.0.0
 */
function changeLogo(change) {
  if (!logo || isLogin) {
    return;
  }

  if (small.matches) {
    logo.classList.add('small');
    return;
  }

  const state = change || getCookie();

  if (state === 'closed') {
    logo.classList.add('small');
  } else {
    logo.classList.remove('small');
  }
  if (menuToggleIcon) {
    if (wrapper.classList.contains('closed')) {
      menuToggleIcon.classList.add('icon-toggle-on');
      menuToggleIcon.classList.remove('icon-toggle-off');
    } else {
      menuToggleIcon.classList.remove('icon-toggle-on');
      menuToggleIcon.classList.add('icon-toggle-off');
    }
  }
}

/**
 * toggle arrow icon between down and up depending on position of the nav header
 *
 * @param {string} [positionTop] set if the nav header positioned to the 'top' otherwise 'bottom'
 *
 * @since   4.0.0
 */
function toggleArrowIcon(positionTop) {
  const remIcon = (positionTop) ? 'icon-angle-up' : 'icon-angle-down';
  const addIcon = (positionTop) ? 'icon-angle-down' : 'icon-angle-up';

  if (!navDropDownIcon) {
    return;
  }

  navDropDownIcon.forEach((item) => {
    item.classList.remove(remIcon);
    item.classList.add(addIcon);
  });
}

/**
 *
 * @param {[]} arr
 * @returns {Number}
 */
const getSum = (arr) => arr.reduce((a, b) => Number(a) + Number(b), 0);

/**
 * put elements that are too much in the header in a dropdown
 *
 * @since   4.0.0
 */
function headerItemsInDropdown() {
  headerTitleWidth = headerTitleArea.getBoundingClientRect().width;
  const minViable = headerTitleWidth + ellipsisWidth;
  const totalHeaderItemWidths = 50 + getSum(headerItemWidths);

  if (headerTitleWidth + totalHeaderItemWidths < document.body.getBoundingClientRect().width) {
    headerExpandedItems.map((element) => element.classList.remove('d-none'));
    headerCondensedItemContainer.classList.add('d-none');
  } else {
    headerCondensedItemContainer.classList.remove('d-none');
    headerCondensedItems.map((el) => el.classList.add('d-none'));
    headerCondensedItemContainer.classList.remove('d-none');
    headerItemWidths.forEach((width, index) => {
      const tempArr = headerItemWidths.slice(index, headerItemWidths.length);
      if (minViable + getSum(tempArr) < document.body.getBoundingClientRect().width) {
        return;
      }
      if (headerExpandedItems[index].children && !headerExpandedItems[index].children[0].classList.contains('dropdown')) {
        headerExpandedItems[index].classList.add('d-none');
        headerCondensedItems[index].classList.remove('d-none');
      }
    });
  }
}

/**
 * Change appearance for mobile devices
 *
 * @since   4.0.0
 */
function setMobile() {
  if (small.matches) {
    toggleArrowIcon();

    if (menu) {
      wrapper.classList.remove('closed');
    }
  } else {
    toggleArrowIcon('top');
  }

  if (tablet.matches && menu) {
    wrapper.classList.add('closed');
  }

  if (small.matches) {
    sidebarNav.map((el) => el.classList.add('collapse'));
    if (subhead) subhead.classList.add('collapse');
    if (sidebarWrapper) sidebarWrapper.classList.add('collapse');
  } else {
    sidebarNav.map((el) => el.classList.remove('collapse'));
    if (subhead) subhead.classList.remove('collapse');
    if (sidebarWrapper) sidebarWrapper.classList.remove('collapse');
  }
  changeLogo('closed');
}

/**
 * Change appearance for mobile devices
 *
 * @since   4.0.0
 */
function setDesktop() {
  if (!sidebarWrapper) {
    changeLogo('closed');
  } else {
    changeLogo(getCookie() || 'open');
    sidebarWrapper.classList.remove('collapse');
  }

  sidebarNav.map((el) => el.classList.remove('collapse'));
  if (subhead) subhead.classList.remove('collapse');

  toggleArrowIcon('top');
}

/**
 * React on resizing window
 *
 * @since   4.0.0
 */
function reactToResize() {
  window.addEventListener('resize', () => {
    if (mobile.matches) {
      setMobile();
    } else {
      setDesktop();
    }

    headerItemsInDropdown();
  });
}

/**
 * Subhead gets white background when user scrolls down
 *
 * @since   4.0.0
 */
function subheadScrolling() {
  if (subhead) {
    document.addEventListener('scroll', () => {
      if (window.scrollY > 0) {
        subhead.classList.add('shadow-sm');
      } else {
        subhead.classList.remove('shadow-sm');
      }
    });
  }
}

/**
 * Watch for Dark mode changes
 *
 * @since   5.1.0
 */
function darkModeWatch() {
  const docEl = document.documentElement;
  // Update data-bs-theme when scheme has been changed
  document.addEventListener('joomla:color-scheme-change', () => {
    docEl.dataset.bsTheme = docEl.dataset.colorScheme;
  });

  // Look for User choose with data-color-scheme-switch button
  const buttons = document.querySelectorAll('button[data-color-scheme-switch]');
  buttons.forEach((button) => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      const { colorScheme } = docEl.dataset;
      const newScheme = colorScheme !== 'dark' ? 'dark' : 'light';
      docEl.dataset.colorScheme = newScheme;
      document.cookie = `userColorScheme=${newScheme};`;
      document.dispatchEvent(new CustomEvent('joomla:color-scheme-change', { bubbles: true }));
    });
  });

  // Look for data-color-scheme-os attribute
  const { colorSchemeOs } = docEl.dataset;
  if (colorSchemeOs === undefined) return;
  // Watch on media changes
  const mql = window.matchMedia('(prefers-color-scheme: dark)');
  const check = () => {
    const newScheme = mql.matches ? 'dark' : 'light';
    // Check if theme already was set
    if (docEl.dataset.colorScheme === newScheme) return;
    docEl.dataset.colorScheme = newScheme;
    // Store theme in cookies, so php will know the last choice
    document.cookie = `osColorScheme=${newScheme};`;
    document.dispatchEvent(new CustomEvent('joomla:color-scheme-change', { bubbles: true }));
  };
  mql.addEventListener('change', check);
  check();
}

// Initialize
darkModeWatch();
headerItemsInDropdown();
reactToResize();
subheadScrolling();
if (small.matches) {
  changeLogo('closed');
  if (subhead) {
    subhead.classList.remove('show');
    subhead.classList.add('collapse');
  }
}
if (!navigator.cookieEnabled) {
  Joomla.renderMessages({ error: [Joomla.Text._('JGLOBAL_WARNCOOKIES')] }, undefined, false, 6000);
}
window.addEventListener('joomla:menu-toggle', (event) => {
  headerItemsInDropdown();
  document.cookie = `atumSidebarState=${event.detail};`;

  if (mobile.matches) {
    changeLogo('closed');
  } else {
    changeLogo(event.detail);
  }
});

/**
 * Close any open data-bs-toggle="collapse" when opening a data-bs-toggle="dropdown"
 *
 * @since 4.4
 */
document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((button) => {
  button.addEventListener('click', () => {
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach((cb) => {
      const target = document.querySelector(cb.getAttribute('data-bs-target'));
      if (target.contains(button)) {
        return;
      }
      const collapseMenu = bootstrap.Collapse.getInstance(target) || new bootstrap.Collapse(target, {
        toggle: false,
      });
      collapseMenu.hide();
    });
  });
});
