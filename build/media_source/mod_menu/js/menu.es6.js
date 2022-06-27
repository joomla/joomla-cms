/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  function topLevelMouseOver(el, settings) {
    const ulChild = el.querySelector('ul');
    if (ulChild) {
      ulChild.setAttribute('aria-hidden', 'false');
      ulChild.classList.add(settings.menuHoverClass);
    }
  }

  function topLevelMouseOut(el, settings) {
    const ulChild = el.querySelector('ul');
    if (ulChild) {
      ulChild.setAttribute('aria-hidden', 'true');
      ulChild.classList.remove(settings.menuHoverClass);
    }
  }

  function setupNavigation(nav) {
    const settings = {
      menuHoverClass: 'show-menu',
      dir: 'ltr',
    };
    const topLevelChilds = nav.querySelectorAll(':scope > li');

    // Set tabIndex to -1 so that top_level_childs can't receive focus until menu is open
    topLevelChilds.forEach((topLevelEl) => {
      const linkEl = topLevelEl.querySelector('a');
      if (linkEl) {
        linkEl.tabIndex = '0';
        linkEl.addEventListener('mouseover', topLevelMouseOver(topLevelEl, settings));
        linkEl.addEventListener('mouseout', topLevelMouseOut(topLevelEl, settings));
      }
      const spanEl = topLevelEl.querySelector('span');
      if (spanEl) {
        spanEl.tabIndex = '0';
        spanEl.addEventListener('mouseover', topLevelMouseOver(topLevelEl, settings));
        spanEl.addEventListener('mouseout', topLevelMouseOut(topLevelEl, settings));
      }

      topLevelEl.addEventListener('mouseover', ({ target }) => {
        const ulChild = target.querySelector('ul');
        if (ulChild) {
          ulChild.setAttribute('aria-hidden', 'false');
          ulChild.classList.add(settings.menuHoverClass);
        }
      });

      topLevelEl.addEventListener('mouseout', ({ target }) => {
        const ulChild = target.querySelector('ul');
        if (ulChild) {
          ulChild.setAttribute('aria-hidden', 'true');
          ulChild.classList.remove(settings.menuHoverClass);
        }
      });

      topLevelEl.addEventListener('focus', ({ target }) => {
        const ulChild = target.querySelector('ul');
        if (ulChild) {
          ulChild.setAttribute('aria-hidden', 'true');
          ulChild.classList.add(settings.menuHoverClass);
        }
      });

      topLevelEl.addEventListener('blur', ({ target }) => {
        const ulChild = target.querySelector('ul');
        if (ulChild) {
          ulChild.setAttribute('aria-hidden', 'false');
          ulChild.classList.remove(settings.menuHoverClass);
        }
      });

      topLevelEl.addEventListener('keydown', (event) => {
        const keyName = event.key;
        const curEl = event.target;
        const curLiEl = curEl.parentElement;
        const curUlEl = curLiEl.parentElement;
        let prevLiEl = curLiEl.previousElementSibling;
        let nextLiEl = curLiEl.nextElementSibling;
        if (!prevLiEl) {
          prevLiEl = curUlEl.children[curUlEl.children.length - 1];
        }
        if (!nextLiEl) {
          [nextLiEl] = curUlEl.children;
        }
        switch (keyName) {
          case 'ArrowLeft':
            event.preventDefault();
            if (settings.dir === 'rtl') {
              nextLiEl.children[0].focus();
            } else {
              prevLiEl.children[0].focus();
            }
            break;
          case 'ArrowRight':
            event.preventDefault();
            if (settings.dir === 'rtl') {
              prevLiEl.children[0].focus();
            } else {
              nextLiEl.children[0].focus();
            }
            break;
          case 'ArrowUp':
          {
            event.preventDefault();
            const parent = curLiEl.parentElement.parentElement;
            if (parent.nodeName === 'LI') {
              parent.children[0].focus();
            } else {
              prevLiEl.children[0].focus();
            }
            break;
          }
          case 'ArrowDown':
            event.preventDefault();
            if (curLiEl.classList.contains('parent')) {
              const child = curLiEl.querySelector('ul');
              if (child != null) {
                const childLi = child.querySelector('li');
                childLi.children[0].focus();
              } else {
                nextLiEl.children[0].focus();
              }
            } else {
              nextLiEl.children[0].focus();
            }
            break;
          default:
            break;
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const navs = document.querySelectorAll('.nav');
    [].forEach.call(navs, (nav) => {
      setupNavigation(nav);
    });
  });
})();
