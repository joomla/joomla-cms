/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Debounce
 * https://gist.github.com/nmsdvid/8807205
 *
 * @param { function } callback  The callback function to be executed
 * @param { int }  time      The time to wait before firing the callback
 * @param { int }  interval  The interval
 */
// eslint-disable-next-line no-param-reassign, no-return-assign, default-param-last
const debounce = (callback, time = 250, interval) => (...args) => clearTimeout(interval, interval = setTimeout(callback, time, ...args));

((window, document, Joomla) => {
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
        const wrapper = element.closest('.module-wrapper');
        wrapper.parentNode.removeChild(wrapper);

        Joomla.renderMessages({
          message: [Joomla.Text._('COM_CPANEL_UNPUBLISH_MODULE_SUCCESS')],
        });
      },
      onError: () => {
        Joomla.renderMessages({
          error: [Joomla.Text._('COM_CPANEL_UNPUBLISH_MODULE_ERROR')],
        });
      },
    });
  };

  const onBoot = () => {
    const cpanelModules = document.getElementById('content');
    if (cpanelModules) {
      cpanelModules.querySelectorAll('.unpublish-module').forEach((link) => {
        link.addEventListener('click', ({ target }) => Joomla.unpublishModule(target));
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
    gridAutoRows: 0,
    gridRowGap: 10,

    // Calculate "grid-row-end" property
    resizeGridItem($cell, rowHeight, rowGap) {
      const $content = $cell.querySelector('.card');
      if ($content) {
        const contentHeight = $content.getBoundingClientRect().height + rowGap;
        const rowSpan = Math.ceil(contentHeight / (rowHeight + rowGap));

        $cell.style.gridRowEnd = `span ${rowSpan}`;
      }
    },

    // Check a size of every cell in the grid
    resizeAllGridItems() {
      const $gridCells = [].slice.call(this.$gridBox.children);

      $gridCells.forEach(($cell) => this.resizeGridItem($cell, this.gridAutoRows, this.gridRowGap));
    },

    initialise() {
      this.$gridBox = document.querySelector('#cpanel-modules .card-columns');

      const gridStyle = window.getComputedStyle(this.$gridBox);
      this.gridAutoRows = parseInt(gridStyle.getPropertyValue('grid-auto-rows'), 10) || this.gridAutoRows;
      this.gridRowGap = parseInt(gridStyle.getPropertyValue('grid-row-gap'), 10) || this.gridRowGap;

      this.resizeAllGridItems();

      // Recheck the layout after all content (fonts and images) is loaded.
      window.addEventListener('load', () => this.resizeAllGridItems());

      // Recheck the layout when the menu is toggled
      window.addEventListener('joomla:menu-toggle', () => {
        // 300ms is animation time, need to wait for the animation to end
        setTimeout(() => this.resizeAllGridItems(), 330);
      });

      // Watch on window resize
      window.addEventListener('resize', debounce(() => this.resizeAllGridItems(), 50));
    },
  };

  // Initialise Masonry layout at the very beginning, to avoid jumping.
  // We can do this because the script is deferred.
  MasonryLayout.initialise();
})(window, document, window.Joomla);
