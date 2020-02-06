/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((window, document, Joomla) => {
  let matchesFn = 'matches';

  const closest = (element, selector) => {
    let parent;
    let el = element;

    // Traverse parents
    while (el) {
      parent = el.parentElement;
      if (parent && parent[matchesFn](selector)) {
        return parent;
      }
      el = parent;
    }

    return null;
  };

  Joomla.unpublishModule = (element) => {
    // Get variables
    const baseUrl = 'index.php?option=com_modules&task=modules.unpublish&format=json';
    const id = element.getAttribute('data-module-id');

    Joomla.request({
      url: `${baseUrl}&cid=${id}`,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      onSuccess: () => {
        const wrapper = closest(element, '.module-wrapper');
        wrapper.parentNode.removeChild(wrapper);

        Joomla.renderMessages({
          message: [Joomla.JText._('COM_CPANEL_UNPUBLISH_MODULE_SUCCESS')],
        });
      },
      onError: () => {
        Joomla.renderMessages({
          error: [Joomla.JText._('COM_CPANEL_UNPUBLISH_MODULE_ERROR')],
        });
      },
    });
  };

  const onBoot = () => {
    // Find matchesFn with vendor prefix
    ['matches', 'msMatchesSelector'].some((fn) => {
      if (typeof document.body[fn] === 'function') {
        matchesFn = fn;
        return true;
      }
      return false;
    });

    const cpanelModules = document.getElementById('cpanel-modules');
    if (cpanelModules) {
      const links = [].slice.call(cpanelModules.querySelectorAll('.unpublish-module'));
      links.forEach((link) => {
        link.addEventListener('click', event => Joomla.unpublishModule(event.target));
      });
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  // Initialise
  document.addEventListener('DOMContentLoaded', onBoot);

  // Masonry layout for cpanel cards
  const MasonryLayout = {
    $gridBox: null,

    // Calculate "grid-row-end" property
    resizeGridItem($cell, rowHeight, rowGap) {
      const $content = $cell.querySelector('.card');
      const contentHeight = $content.getBoundingClientRect().height + rowGap;
      const rowSpan = Math.ceil(contentHeight / (rowHeight + rowGap));

      $cell.style.gridRowEnd = `span ${rowSpan}`;
    },

    // Check a size of every cell in the grid
    resizeAllGridItems() {
      const $gridCells = [].slice.call(MasonryLayout.$gridBox.children);
      const gridStyle = window.getComputedStyle(MasonryLayout.$gridBox);
      const gridAutoRows = parseInt(gridStyle.getPropertyValue('grid-auto-rows'), 10) || 0;
      const gridRowGap = parseInt(gridStyle.getPropertyValue('grid-row-gap'), 10) || 10;

      $gridCells.forEach(($cell) => {
        MasonryLayout.resizeGridItem($cell, gridAutoRows, gridRowGap);
      });
    },

    initialise() {
      MasonryLayout.$gridBox = document.querySelector('#cpanel-modules .card-columns');
      MasonryLayout.resizeAllGridItems();

      // Watch on window resize
      let resizeTimer;
      window.addEventListener('resize', () => {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(MasonryLayout.resizeAllGridItems, 300);
      });
    },
  };

  // Initialise Masonry layout on full load,
  // to be sure all images/fonts are loaded, and so cards have a "final" size
  window.addEventListener('load', MasonryLayout.initialise);

})(window, document, window.Joomla);
