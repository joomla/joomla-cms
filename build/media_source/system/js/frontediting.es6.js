/**
 * @copyright (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to add front-end hover edit icons with tooltips for modules and menu items.
 */
document.addEventListener('DOMContentLoaded', () => {
  'strict';

  // Modules edit icons:
  const editableModules = Array.from(document.querySelectorAll('.jmoddiv'));
  const attachListeners = (element) => {
    // Get module editing URL and tooltip for module edit:
    const moduleEditUrl = element.dataset.jmodediturl;
    const moduleTip = element.dataset.jmodtip;
    const moduleTarget = element.dataset.bsTarget;

    // Stop timeout on previous tooltip and remove it:
    const button = element.querySelector('.jmodedit');
    if (button && Joomla.Bootstrap.Instances.Tooltip.get(button)) {
      button.dispose();
      button.parentNode.remove(button);
    }

    // Add editing button with tooltip:
    element.classList.add('jmodinside');
    if (!element.firstElementChild.classList.contains('jmodedit')) {
      element.insertAdjacentHTML('afterbegin', `<a class="btn btn-link jmodedit" href="#" target="${moduleTarget}"><span class="icon-edit"></span></a>`);
    }
    const firstEl = element.firstElementChild;
    firstEl.setAttribute('href', moduleEditUrl);
    firstEl.setAttribute('title', moduleTip);

    Joomla.Bootstrap.Initialise.Tooltip(firstEl, { container: 'body', html: true, placement: 'top' });
  };

  editableModules.map((el) => attachListeners(el));

  // Menu items edit icons:
  const editableMenus = Array.from(document.querySelectorAll('.jmoddiv[data-jmenuedittip] .nav li,.jmoddiv[data-jmenuedittip].nav li,.jmoddiv[data-jmenuedittip] .nav .nav-child li,.jmoddiv[data-jmenuedittip].nav .nav-child li'));
  const attachMenusListeners = (element) => {
    // Get menu ItemId from the item-nnn class of the li element of the menu:
    const itemids = /\bitem-(\d+)\b/.exec(ev.target.getAttribute('class'));
    let menuitemEditUrl;
    let enclosingModuleDiv;
    if (typeof itemids[1] === 'string') {
      // Find module editing URL from enclosing module:
      enclosingModuleDiv = ev.target.closest('.jmoddiv');
      // Transform module editing URL into Menu Item editing url:
      menuitemEditUrl = enclosingModuleDiv.data('jmodediturl').replace(
        /\/index.php\?option=com_config&view=modules([^\d]+).+$/,
        `/administrator/index.php?option=com_menus&view=item&layout=edit$1${itemids[1]}`,
      );
    }
    // Get tooltip for menu items from enclosing module
    const menuEditTip = enclosingModuleDiv.dataset.jmenuedittip.replace('%s', itemids[1]);

    if (Joomla.Bootstrap.Instances.Popover.get(element)) {
      element.hide();
    }
    Joomla.Bootstrap.Initialise.Popover(element, {
      html: true,
      content: `<div><a class="btn jfedit-menu" href="${menuitemEditUrl}" title="${menuEditTip}" target="_blank"><span class="icon-edit"></span></a></div>`,
      container: 'body',
      trigger: 'manual',
      animation: false,
      placement: 'bottom',
    });
    element.show();

    const popovers = Array.from(document.querySelectorAll('body > div.popover'));
    popovers.map((el) => {
      el.addEventListener('mouseenter', (ev1) => {
        if (Joomla.Bootstrap.Instances.Popover.get(ev1.target)) {
          ev1.target.clearQueue();
        }
      });
      el.addEventListener('mouseleave', (ev2) => {
        if (Joomla.Bootstrap.Instances.Popover.get(ev2.target)) {
          ev2.target.hide();
        }
      });
      return el;
    });

    const allPopovers = Array.from(document.querySelectorAll('.jfedit-menu'));
    allPopovers.map((el) => Joomla.Bootstrap.Initialise.Tooltip(el,
      { container: false, html: true, placement: 'bottom' }));
    // const allPopovers = Array.from(document.querySelectorAll('.jfedit-menu'));
    // const xxx = $('body>div.popover').find('.jfedit-menu').get()[0];
    // Joomla.Bootstrap.Initialise.Tooltip(xxx,
    // { container: false, html: true, placement: 'bottom' });
  };

  editableMenus.map((el) => attachMenusListeners(el));
});
