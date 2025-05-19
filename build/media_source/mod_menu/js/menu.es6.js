/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  class Nav {
      static idCounter = 0;

      constructor(nav) {
        this.nav = nav;
        this.settings = {
          menuHoverClass: 'show-menu',
          dir: 'ltr',
        };

        // Unique prefix for this nav instance - needed for the id of submenus and aria-controls
        this.idPrefix = this.nav?.id ?? `nav-${Math.floor(Math.random() * 100000)}`;

        this.topLevelNodes = this.nav.querySelectorAll(':scope > li');

        this.topLevelNodes.forEach((topLevelEl) => {
          // get the first child within topLevelEl - either a link or span
          const firstChild = topLevelEl.firstElementChild;
          // get submenu ul elements within topLevelEl
          const levelChildUls = topLevelEl.querySelectorAll('ul');
          let ariaControls = [];
          levelChildUls.forEach((childUl) => {
            childUl.setAttribute('aria-hidden', 'true');
            childUl.classList.remove(this.settings.menuHoverClass); // ???
            childUl.id = `${this.idPrefix}-submenu${Nav.idCounter++}`;
            ariaControls.push(childUl.id);
          });

          if (levelChildUls.length > 0) {

            if (!topLevelEl.querySelector('[aria-expanded]')) {
              // fallback - add element to toggle submenus if not already present
              // @todo better idea
              const togglebtn = document.createElement('span');
              togglebtn.setAttribute('aria-expanded', 'false');
              togglebtn.setAttribute('aria-controls', ariaControls);
              togglebtn.setAttribute('role', 'button');
              togglebtn.tabIndex = '0';
              togglebtn.innerHTML = '<span class="icon-chevron-down" aria-hidden="true"></span>';

              if (firstChild.nodeName === 'SPAN' && !firstChild.querySelector('a')) {
                togglebtn.innerHTML = firstChild.getHTML() + '\u00A0\u00A0' + togglebtn.innerHTML;
                topLevelEl.replaceChild(togglebtn, firstChild);
              } else {
                topLevelEl.querySelector('ul').before(togglebtn);
                const space = document.createTextNode('\u00A0\u00A0');
                topLevelEl.insertBefore(space, togglebtn);
              }
              // @todo aria-label
            } else {
              const togglebtn = topLevelEl.querySelector('[aria-expanded]');
              togglebtn.setAttribute('aria-controls', ariaControls);
            }
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
          case 'Escape':
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
            currentTopLevelLi.querySelectorAll(':scope > [tabindex]:not([tabindex="-1"]), a, button').forEach(tabElement => {
              if (tabElement.hasAttribute(['aria-expanded'])) {
                tabElement.focus();
                return;
              }
            });
            break;
          case 'End':
            event.preventDefault();
            console.log('End', event.target);
            const lastLi = target.closest('ul')?.querySelector(':scope > li:last-child');
            console.log('lastLi', lastLi);
            if (lastLi) {
              // set focus on last li child with tabindex within current list
              lastLi.querySelector(':scope > [tabindex]:not([tabindex="-1"]), a, button')?.focus();
            }
            break;
          case 'Home':
            event.preventDefault();
            const firstLi = target.closest('ul')?.querySelector(':scope > li:first-child');
            if (firstLi) {
              // set focus on first li child with tabindex within current list
              firstLi.querySelector(':scope > [tabindex]:not([tabindex="-1"]), a, button')?.focus();
            }
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
        subLists.forEach((ulChild) => {
          ulChild.setAttribute('aria-hidden', open ? 'false' : 'true');
          ulChild.classList.toggle(this.settings.menuHoverClass, open); // ???
        });
        target.querySelector(':scope > [aria-expanded]').setAttribute('aria-expanded', open ? 'true' : 'false');
      }

      focusTabbable(direction = 1) {
        const tabbables = Array.from(this.nav.querySelectorAll('[tabindex]:not([tabindex="-1"]), a, button'))
          .filter(el => !el.disabled && el.tabIndex >= 0 && el.offsetParent !== null);
        const currentIndex = tabbables.indexOf(document.activeElement);
        if (tabbables.length === 0) return;
        let nextIndex = (currentIndex + direction + tabbables.length) % tabbables.length;
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

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav').forEach((nav) => new Nav(nav));
  });
})();
