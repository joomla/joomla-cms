/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  /**
   * Navigation menu
   *
   * Example usage:
   *   // Default behavior (uses menuHoverClass = 'show-menu', dir = 'ltr')
   *   new Nav(document.querySelector('.nav'));
   *
   *   // Override defaults (e.g. custom open-class and RTL support)
   *   new Nav(document.querySelector('.nav'), {
   *     menuHoverClass: 'my-open-class',
   *     dir:            'rtl'
   *   });
   *
   * @param {HTMLElement} nav                                   The root <ul class="nav"> element
   * @param {Object}      [settings]                            Optional overrides for defaultSettings
   * @param {string}      [settings.menuHoverClass='show-menu'] CSS class to toggle on open submenus
   * @param {string}      [settings.dir='ltr']                  Text direction for keyboard nav ('ltr'|'rtl')
   */

  class Nav {

    // Default settings for the Nav class
    static defaultSettings = {
      menuHoverClass: 'show-menu',
      dir: 'ltr'
    };

    constructor(nav, settings = {}) {
      this.nav = nav;

      // read the HTML dir attribute or computed style, or fall back to defaultSettings.dir
      const browserDir =
        document.documentElement.getAttribute('dir') ||                          // <html dir="…">
        getComputedStyle(document.documentElement).direction ||                   // CSS direction

        Nav.defaultSettings.dir;
        this.settings = {
          ...Nav.defaultSettings,
          ...settings
      };

      // merge defaults, browser‐detected dir, and any explicit overrides in `settings`
      this.settings = {
        ...Nav.defaultSettings,
        dir: settings.dir ?? browserDir,  // explicit settings.dir wins, otherwise browserDir
        ...settings                       // other overrides (e.g. menuHoverClass)
      };

      // Unique prefix for this nav instance - needed for the id of submenus and aria-controls
      this.idPrefix = this.nav?.id ?? `nav-${Math.floor(Math.random() * 100000)}`;

      this.topLevelNodes = this.nav.querySelectorAll(':scope > li');

      this.topLevelNodes.forEach((topLevelEl) => {
        // get submenu ul elements within topLevelEl
        const levelChildUls = topLevelEl.querySelectorAll('ul');
        const ariaControls = [];
        levelChildUls.forEach((childUl) => {
          childUl.setAttribute('aria-hidden', 'true');
          childUl.classList.remove(this.settings.menuHoverClass);
          childUl.id = `${this.idPrefix}-submenu${Nav.idCounter}`;
          Nav.idCounter += 1;
          ariaControls.push(childUl.id);
        });

        if (levelChildUls.length > 0) {
          const togglebtn = topLevelEl.querySelector('[aria-expanded]');
          togglebtn?.setAttribute('aria-controls', ariaControls.join(' '));
        }
      });

      nav.addEventListener('keydown', this.onMenuKeyDown.bind(this));
      nav.addEventListener('click', this.onClick.bind(this));
    }

    onMenuKeyDown(event) {
      const target = event.target.closest('li');
      if (!target) {
        return;
      }

      const subLists = target.querySelectorAll('ul');

      switch (event.key) {
        case 'ArrowUp':
          event.preventDefault();
          this.tabPrev();
          break;
        case 'ArrowLeft':
          event.preventDefault();
          if (this.settings.dir === 'rtl') {
            this.tabNext();
          } else {
            this.tabPrev();
          }
          break;
        case 'ArrowDown':
          event.preventDefault();
          this.tabNext();
          break;
        case 'ArrowRight':
          event.preventDefault();
          if (this.settings.dir === 'rtl') {
            this.tabPrev();
          } else {
            this.tabNext();
          }
          break;
        case 'Enter':
          if (event.target.nodeName === 'SPAN' && event.target.parentNode.nodeName !== 'A' && subLists.length > 0) {
            event.preventDefault();
            this.toggleSubMenu(target, subLists, subLists[0]?.getAttribute('aria-hidden') === 'true');
          }
          break;
        case ' ':
        case 'Spacebar':
          if (subLists.length > 0) {
            event.preventDefault();
            this.toggleSubMenu(target, subLists, subLists[0]?.getAttribute('aria-hidden') === 'true');
          }
          break;
        case 'Escape': {
          event.preventDefault();
          const currentTopLevelLi = this.getTopLevelParentLi(event.target);
          if (!currentTopLevelLi) {
            break;
          }
          const allChildListsFromTopLevelLi = currentTopLevelLi.querySelectorAll('ul');
          if (allChildListsFromTopLevelLi.length > 0) {
            this.toggleSubMenu(currentTopLevelLi, allChildListsFromTopLevelLi, false);
          }
          // set focus on the top level li child with tabindex
          currentTopLevelLi.querySelectorAll(':scope > [tabindex]:not([tabindex="-1"]), a, button').forEach((tabElement) => {
            if (tabElement.hasAttribute(['aria-expanded'])) {
              tabElement.focus();
            }
          });
          break;
        }
        case 'End': {
          event.preventDefault();
          const currentLiList = target.closest('ul')?.querySelectorAll(':scope > li');
          for (let index = currentLiList.length - 1; index >= 0; index -= 1) {
            const lastTabbable = currentLiList[index].querySelector(':scope > [tabindex]:not([tabindex="-1"]), a, button');
            if (lastTabbable) {
              lastTabbable.focus();
              return;
            }
          }
          break;
        }
        case 'Home': {
          event.preventDefault();
          const firstLi = target.closest('ul')?.querySelector(':scope > li:first-child');
          if (firstLi) {
            // set focus on first li child with tabindex within current list
            firstLi.querySelector(':scope > [tabindex]:not([tabindex="-1"]), a, button')?.focus();
          }
          break;
        }
        default:
          break;
      }
    }

    onClick(event) {
      if (!event.target?.hasAttribute('aria-expanded') && !event.target?.closest('[aria-expanded')) {
        return;
      }
      if (event.target?.nodeName === 'A') {
        return;
      }
      if (event.target?.nodeName === 'SPAN' && event.target.parentNode.nodeName === 'A') {
        return;
      }
      const target = event.target.closest('li');
      const subLists = target?.querySelectorAll('ul');
      if (subLists && subLists.length > 0) {
        event.preventDefault();
        this.toggleSubMenu(target, subLists, subLists[0]?.getAttribute('aria-hidden') === 'true');
      }
    }

    toggleSubMenu(target, subLists, open = false) {
      if (open) {
        // close all opened submenus before opening the new one
        const allSubMenus = this.nav.querySelectorAll('ul[aria-hidden="false"]');
        allSubMenus.forEach((ulChild) => {
            ulChild.setAttribute('aria-hidden', 'true');
            ulChild.classList.remove(this.settings.menuHoverClass);
            this.getTopLevelParentLi(ulChild)?.querySelector(':scope > [aria-expanded]')?.setAttribute('aria-expanded', 'false');
        });
      }
      subLists.forEach((ulChild) => {
        ulChild.setAttribute('aria-hidden', open ? 'false' : 'true');
        ulChild.classList.toggle(this.settings.menuHoverClass, open);
      });
      target.querySelector(':scope > [aria-expanded]').setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    focusTabbable(direction = 1) {
      const tabbables = Array.from(this.nav.querySelectorAll('[tabindex]:not([tabindex="-1"]), a, button'))
        .filter((el) => !el.disabled && el.tabIndex >= 0 && el.offsetParent !== null);
      const currentIndex = tabbables.indexOf(document.activeElement);
      if (tabbables.length === 0) return;
      const nextIndex = (currentIndex + direction + tabbables.length) % tabbables.length;
      tabbables[nextIndex].focus();
    }

    tabNext() {
      this.focusTabbable(1);
    }

    tabPrev() {
      this.focusTabbable(-1);
    }

    getTopLevelParentLi(element) {
      let currentLi = element.closest('li');
      // this.topLevelNodes is a NodeList of top-level li elements in this nav
      while (currentLi && !Array.from(this.topLevelNodes).includes(currentLi)) {
        const parentUl = currentLi.parentElement.closest('ul');
        currentLi = parentUl ? parentUl.closest('li') : null;
      }
      return currentLi; // top-level li or null if not found, or the
    }
  }

  // static idCounter for unique id generation of submenus
  Nav.idCounter = 0;

  // Initialize Nav instances for all nav elements on the page
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav').forEach((nav) => new Nav(nav));
  });
})();
