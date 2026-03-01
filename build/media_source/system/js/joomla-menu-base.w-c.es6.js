/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Base class for Joomla menu web components.
 * Provides reusable keyboard navigation, focus cycling, RTL support,
 * ARIA management, and submenu toggle abstraction.
 *
 * Not registered as a custom element — intended to be extended.
 * Consumers set the menu-selector attribute to control which <ul> is used;
 * defaults to the first <ul> found inside the element.
 */
class JoomlaMenuBase extends HTMLElement {
  connectedCallback() {
    this.menuEl = this.querySelector(this.getAttribute('menu-selector') || 'ul');

    if (!this.menuEl) {
      return;
    }

    this.initAriaAttributes();
    this.initSubmenuToggles();
    this.initKeyboardNav();
    this.onConnected();
  }

  disconnectedCallback() {
    if (this.menuEl && this.keydownHandler) {
      this.menuEl.removeEventListener('keydown', this.keydownHandler);
    }
    this.onDisconnected();
  }

  /**
   * Hook for subclasses — called at the end of connectedCallback.
   */
  onConnected() {}

  /**
   * Hook for subclasses — called at the end of disconnectedCallback.
   */
  onDisconnected() {}

  /**
   * Generate unique IDs for submenus and set aria-controls on triggers.
   */
  initAriaAttributes() {
    let idCounter = 0;

    this.menuEl.querySelectorAll('li.parent > .has-arrow').forEach((trigger) => {
      const li = trigger.parentElement;
      const submenu = li.querySelector(':scope > ul');

      if (!submenu) {
        return;
      }

      if (!submenu.id) {
        submenu.id = `submenu-${this.menuEl.id || 'nav'}-${idCounter}`;
        idCounter += 1;
      }

      if (!trigger.hasAttribute('aria-controls')) {
        trigger.setAttribute('aria-controls', submenu.id);
      }

      if (!trigger.hasAttribute('aria-expanded')) {
        trigger.setAttribute('aria-expanded', submenu.classList.contains('submenu-show') ? 'true' : 'false');
      }
    });
  }

  /**
   * Attach click handlers on submenu triggers.
   */
  initSubmenuToggles() {
    this.menuEl.querySelectorAll('li.parent > .has-arrow').forEach((trigger) => {
      const li = trigger.parentElement;
      const submenu = li.querySelector(':scope > ul');

      if (!submenu) {
        return;
      }

      trigger.addEventListener('click', (event) => {
        event.preventDefault();
        this.toggleSubmenu(li, submenu, trigger);
      });
    });
  }

  /**
   * Initialise keyboard navigation on the menu.
   */
  initKeyboardNav() {
    this.keydownHandler = (event) => this.handleKeydown(event);
    this.menuEl.addEventListener('keydown', this.keydownHandler);
  }

  /**
   * Toggle a submenu open or closed.
   *
   * @param {HTMLElement} li       The parent <li>
   * @param {HTMLElement} submenu  The child <ul>
   * @param {HTMLElement} trigger  The <a> toggle
   */
  toggleSubmenu(li, submenu, trigger) {
    const isOpen = submenu.classList.contains('submenu-show');

    if (isOpen) {
      this.closeSubmenu(li, submenu, trigger);
    } else {
      this.openSubmenu(li, submenu, trigger);
    }
  }

  /**
   * Open a submenu — base implementation (instant, no transition).
   *
   * @param {HTMLElement} li       The parent <li>
   * @param {HTMLElement} submenu  The child <ul>
   * @param {HTMLElement} trigger  The <a> toggle
   */
  openSubmenu(li, submenu, trigger) {
    submenu.classList.add('submenu-collapse', 'submenu-show');
    trigger.setAttribute('aria-expanded', 'true');
    li.classList.add('menu-active');
  }

  /**
   * Close a submenu — base implementation (instant, no transition).
   *
   * @param {HTMLElement} li       The parent <li>
   * @param {HTMLElement} submenu  The child <ul>
   * @param {HTMLElement} trigger  The <a> toggle
   */
  closeSubmenu(li, submenu, trigger) {
    submenu.classList.remove('submenu-show');
    submenu.classList.add('submenu-collapse');
    trigger.setAttribute('aria-expanded', 'false');
    li.classList.remove('menu-active');
  }

  /**
   * Collect visible focusable elements and move focus by direction.
   *
   * @param {number} direction  +1 for next, -1 for previous
   */
  focusTabbable(direction) {
    const tabbables = Array.from(this.menuEl.querySelectorAll('a, button'))
      .filter((el) => !el.disabled && el.offsetParent !== null);

    if (tabbables.length === 0) {
      return;
    }

    const currentIndex = tabbables.indexOf(document.activeElement);
    const nextIndex = (currentIndex + direction + tabbables.length) % tabbables.length;
    tabbables[nextIndex].focus();
  }

  /**
   * Handle keydown events on the menu.
   *
   * @param {KeyboardEvent} event  The keyboard event
   */
  handleKeydown(event) {
    const { target } = event;
    const li = target.closest('li');

    if (!li) {
      return;
    }

    switch (event.key) {
      case 'ArrowDown':
        event.preventDefault();
        this.focusTabbable(1);
        break;

      case 'ArrowUp':
        event.preventDefault();
        this.focusTabbable(-1);
        break;

      case 'ArrowRight':
        event.preventDefault();
        this.focusTabbable(document.documentElement.getAttribute('dir') === 'rtl' ? -1 : 1);
        break;

      case 'ArrowLeft':
        event.preventDefault();
        this.focusTabbable(document.documentElement.getAttribute('dir') === 'rtl' ? 1 : -1);
        break;

      case 'Enter':
        if (target.classList.contains('has-arrow') && target.tagName.toLowerCase() === 'button') {
          // Button: prevent native Enter→click from double-firing toggleSubmenu
          event.preventDefault();
          const submenu = li.querySelector(':scope > ul');
          if (submenu) {
            this.toggleSubmenu(li, submenu, target);
          }
        }
        // <a>.has-arrow: browser fires click → click handler calls toggleSubmenu (event.preventDefault blocks nav)
        // Leaf <a> links (no has-arrow): let browser navigate
        break;

      case ' ':
        // Always toggle submenu if parent item; prevent scroll
        if (target.classList.contains('has-arrow')) {
          event.preventDefault();
          const submenu = li.querySelector(':scope > ul');
          if (submenu) {
            this.toggleSubmenu(li, submenu, target);
          }
        }
        break;

      case 'Escape': {
        event.preventDefault();

        // If current item has an open submenu, close it
        if (li.classList.contains('parent')) {
          const ownSubmenu = li.querySelector(':scope > ul');
          const ownTrigger = li.querySelector(':scope > .has-arrow');

          if (ownSubmenu && ownSubmenu.classList.contains('submenu-show') && ownTrigger) {
            this.closeSubmenu(li, ownSubmenu, ownTrigger);
            ownTrigger.focus();
            break;
          }
        }

        // Otherwise, close the parent submenu and move focus to its parent trigger
        const parentUl = li.closest('ul');
        const parentLi = parentUl ? parentUl.closest('li.parent') : null;

        if (parentLi) {
          const parentSubmenu = parentLi.querySelector(':scope > ul');
          const parentTrigger = parentLi.querySelector(':scope > .has-arrow');

          if (parentSubmenu && parentTrigger) {
            this.closeSubmenu(parentLi, parentSubmenu, parentTrigger);
            parentTrigger.focus();
          }
        }
        break;
      }

      case 'Home': {
        event.preventDefault();
        const currentList = li.closest('ul');
        const firstLink = currentList
          ? currentList.querySelector(':scope > li > a, :scope > li > button')
          : null;
        if (firstLink) {
          firstLink.focus();
        }
        break;
      }

      case 'End': {
        event.preventDefault();
        const currentList = li.closest('ul');
        if (currentList) {
          const items = currentList.querySelectorAll(':scope > li');
          for (let i = items.length - 1; i >= 0; i -= 1) {
            const lastLink = items[i].querySelector(':scope > a, :scope > button');
            if (lastLink) {
              lastLink.focus();
              break;
            }
          }
        }
        break;
      }

      default:
        break;
    }
  }
}

export { JoomlaMenuBase };
