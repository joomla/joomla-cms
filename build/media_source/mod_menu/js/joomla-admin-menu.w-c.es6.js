/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

import { JoomlaMenuBase } from 'joomla.menu-base';

/**
 * Safety timeout (ms) as fallback if transitionend doesn't fire
 * (e.g. prefers-reduced-motion or display:none).
 * Slightly longer than the 350ms CSS transition to allow for timing variations.
 */
const TRANSITION_SAFETY_MS = 400;

/**
 * Admin sidebar menu component.
 * Extends JoomlaMenuBase with CSS height transitions, sidebar toggle,
 * hover popout, and client-side active detection.
 */
class JoomlaAdminMenu extends JoomlaMenuBase {
  /** @inheritdoc */
  connectedCallback() {
    // @deprecated Since __DEPLOY_VERSION__. Will be removed in 7.0.
    // Shim to normalise old template overrides that use mm-collapse/mm-show so the web component's
    // toggleSubmenu() logic (which tracks submenu-show) works correctly.
    // Required when an old default_submenu.php override is used with the new default.php.
    const menuEl = this.querySelector(this.getAttribute('menu-selector') || 'ul');
    if (menuEl) {
      menuEl.querySelectorAll('.mm-collapse').forEach((ul) => {
        ul.classList.add('submenu-collapse');
        ul.classList.remove('mm-collapse');
        if (ul.classList.contains('mm-show')) {
          ul.classList.add('submenu-show');
          ul.classList.remove('mm-show');
        }
      });
    }
    super.connectedCallback();
  }

  /** @inheritdoc */
  onConnected() {
    this.wrapper = document.getElementById('wrapper');
    this.sidebar = document.getElementById('sidebar-wrapper');
    this.menuToggle = document.getElementById('menu-collapse');
    this.menuToggleIcon = document.getElementById('menu-collapse-icon');

    // If the sidebar doesn't exist, e.g. on edit views, remove the "closed" class
    if (!this.sidebar) {
      if (this.wrapper) {
        this.wrapper.classList.remove('closed');
      }
      return;
    }

    if (this.sidebar.getAttribute('data-hidden')) {
      return;
    }

    // Sidebar toggle
    if (this.menuToggle) {
      this.toggleClickHandler = (event) => {
        event.preventDefault();
        this.wrapper.classList.toggle('closed');
        this.menuToggleIcon.classList.toggle('icon-toggle-on');
        this.menuToggleIcon.classList.toggle('icon-toggle-off');

        this.menuEl.querySelectorAll(':scope > li').forEach((item) => item.classList.remove('open'));

        const elem = document.querySelector('.child-open');
        if (elem) {
          elem.classList.remove('child-open');
        }

        window.dispatchEvent(new CustomEvent('joomla:menu-toggle', {
          detail: this.wrapper.classList.contains('closed') ? 'closed' : 'open',
          bubbles: true,
          cancelable: true,
        }));
      };
      this.menuToggle.addEventListener('click', this.toggleClickHandler);
    }

    // Client-side active fallback — only if server-side didn't mark anything active
    if (!this.wrapper.querySelector('.menu-active')) {
      const currentUrl = window.location.href;

      this.wrapper.querySelectorAll('a.no-dropdown, .menu-dashboard > a').forEach((link) => {
        if (
          (!link.href.match(/index\.php$/) && currentUrl.indexOf(link.href) === 0)
          || (link.href.match(/index\.php$/) && currentUrl.match(/index\.php$/))) {
          link.setAttribute('aria-current', 'page');
          link.classList.add('menu-active');

          // Auto expand parent levels
          if (!link.parentNode.classList.contains('parent')) {
            let tempParent = link.parentNode;

            while (tempParent && !tempParent.classList.contains('main-nav')) {
              tempParent.parentNode.classList.add('menu-active');
              tempParent.classList.add('submenu-show');

              tempParent = tempParent.parentNode.closest('ul');
            }
          }
        }
      });
    }

    // Child open toggle (closed-sidebar hover-popout)
    this.openToggle = (event) => {
      if (event.type === 'keyup' && event.key !== 'Enter' && event.key !== ' ') return;
      const { currentTarget } = event;
      let menuItem = currentTarget.parentNode;

      if (menuItem.tagName.toLowerCase() === 'span') {
        menuItem = currentTarget.parentNode.parentNode;
      }

      if (menuItem.classList.contains('open')) {
        this.menuEl.classList.remove('child-open');
        menuItem.classList.remove('open');
      } else {
        const siblings = [].slice.call(menuItem.parentNode.children);
        siblings.forEach((sibling) => {
          sibling.classList.remove('open');
        });

        this.wrapper.classList.remove('closed');
        if (this.menuToggleIcon.classList.contains('icon-toggle-off')) {
          this.menuToggleIcon.classList.toggle('icon-toggle-off');
          this.menuToggleIcon.classList.toggle('icon-toggle-on');
        }
        this.menuEl.classList.add('child-open');

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

    this.parentLinks = Array.from(this.menuEl.querySelectorAll('li.parent > .has-arrow'));
    this.parentLinks.forEach((parent) => {
      parent.addEventListener('click', this.openToggle);
      parent.addEventListener('keyup', this.openToggle);
    });

    // Menu close buttons
    this.closeHandler = () => {
      this.menuEl.querySelectorAll('.open').forEach((menuChild) => menuChild.classList.remove('open'));
      this.menuEl.classList.remove('child-open');
    };

    this.closeBtns = Array.from(this.menuEl.querySelectorAll('li.parent .close'));
    this.closeBtns.forEach((closeBtn) => {
      closeBtn.addEventListener('click', this.closeHandler);
    });
  }

  /** @inheritdoc */
  onDisconnected() {
    if (this.menuToggle && this.toggleClickHandler) {
      this.menuToggle.removeEventListener('click', this.toggleClickHandler);
    }

    if (this.parentLinks && this.openToggle) {
      this.parentLinks.forEach((parent) => {
        parent.removeEventListener('click', this.openToggle);
        parent.removeEventListener('keyup', this.openToggle);
      });
    }

    if (this.closeBtns && this.closeHandler) {
      this.closeBtns.forEach((closeBtn) => {
        closeBtn.removeEventListener('click', this.closeHandler);
      });
    }
  }

  /**
   * Open a submenu with a CSS height transition.
   *
   * @param {HTMLElement} li       The parent <li>
   * @param {HTMLElement} submenu  The child <ul>
   * @param {HTMLElement} trigger  The <a> toggle
   */
  openSubmenu(li, submenu, trigger) {
    // Guard against re-entrant toggling during transition
    if (li.dataset.toggling) {
      return;
    }
    li.dataset.toggling = 'true';

    // Start: set explicit height 0 and add collapsing class
    submenu.classList.remove('submenu-collapse', 'submenu-show');
    submenu.classList.add('submenu-collapsing');
    submenu.style.blockSize = '0px';

    // Force reflow so the browser sees the starting state
    submenu.offsetHeight;

    // Transition to scrollHeight
    submenu.style.blockSize = `${submenu.scrollHeight}px`;

    const onEnd = () => {
      submenu.removeEventListener('transitionend', onEnd);
      clearTimeout(safetyTimer);
      submenu.classList.remove('submenu-collapsing');
      submenu.classList.add('submenu-collapse', 'submenu-show');
      submenu.style.blockSize = '';
      delete li.dataset.toggling;
    };

    submenu.addEventListener('transitionend', onEnd);
    const safetyTimer = setTimeout(onEnd, TRANSITION_SAFETY_MS);

    trigger.setAttribute('aria-expanded', 'true');
    li.classList.add('menu-active');
  }

  /**
   * Collapse a submenu with a CSS height transition.
   *
   * @param {HTMLElement} li       The parent <li>
   * @param {HTMLElement} submenu  The child <ul>
   * @param {HTMLElement} trigger  The <a> toggle
   */
  closeSubmenu(li, submenu, trigger) {
    // Guard against re-entrant toggling during transition
    if (li.dataset.toggling) {
      return;
    }
    li.dataset.toggling = 'true';

    // Set explicit current height so we can transition from it
    submenu.style.blockSize = `${submenu.scrollHeight}px`;

    // Force reflow
    submenu.offsetHeight;

    submenu.classList.remove('submenu-collapse', 'submenu-show');
    submenu.classList.add('submenu-collapsing');
    submenu.style.blockSize = '0px';

    const onEnd = () => {
      submenu.removeEventListener('transitionend', onEnd);
      clearTimeout(safetyTimer);
      submenu.classList.remove('submenu-collapsing');
      submenu.classList.add('submenu-collapse');
      submenu.style.blockSize = '';
      delete li.dataset.toggling;
    };

    submenu.addEventListener('transitionend', onEnd);
    const safetyTimer = setTimeout(onEnd, TRANSITION_SAFETY_MS);

    trigger.setAttribute('aria-expanded', 'false');
    li.classList.remove('menu-active');
  }

}

customElements.define('joomla-admin-menu', JoomlaAdminMenu);
