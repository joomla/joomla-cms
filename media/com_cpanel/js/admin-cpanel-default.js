/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (window, document, Joomla) {
  Joomla.unpublishModule = function (element) {
    // Get variables
    var baseUrl = 'index.php?option=com_modules&task=modules.unpublish&format=json';
    var id = element.getAttribute('data-module-id');
    Joomla.request({
      url: "".concat(baseUrl, "&cid=").concat(id),
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      onSuccess: function onSuccess() {
        var wrapper = element.closest('.module-wrapper');
        wrapper.parentNode.removeChild(wrapper);
        Joomla.renderMessages({
          message: [Joomla.JText._('COM_CPANEL_UNPUBLISH_MODULE_SUCCESS')]
        });
      },
      onError: function onError() {
        Joomla.renderMessages({
          error: [Joomla.JText._('COM_CPANEL_UNPUBLISH_MODULE_ERROR')]
        });
      }
    });
  };

  var onBoot = function onBoot() {
    var cpanelModules = document.getElementById('content');

    if (cpanelModules) {
      var links = [].slice.call(cpanelModules.querySelectorAll('.unpublish-module'));
      links.forEach(function (link) {
        link.addEventListener('click', function (_ref) {
          var target = _ref.target;
          return Joomla.unpublishModule(target);
        });
      });
    } // Cleanup


    document.removeEventListener('DOMContentLoaded', onBoot);
  }; // Initialise


  document.addEventListener('DOMContentLoaded', onBoot); // Masonry layout for cpanel cards

  var MasonryLayout = {
    $gridBox: null,
    // Calculate "grid-row-end" property
    resizeGridItem: function resizeGridItem($cell, rowHeight, rowGap) {
      var $content = $cell.querySelector('.card');

      if ($content) {
        var contentHeight = $content.getBoundingClientRect().height + rowGap;
        var rowSpan = Math.ceil(contentHeight / (rowHeight + rowGap));
        $cell.style.gridRowEnd = "span ".concat(rowSpan);
      }
    },
    // Check a size of every cell in the grid
    resizeAllGridItems: function resizeAllGridItems() {
      var $gridCells = [].slice.call(MasonryLayout.$gridBox.children);
      var gridStyle = window.getComputedStyle(MasonryLayout.$gridBox);
      var gridAutoRows = parseInt(gridStyle.getPropertyValue('grid-auto-rows'), 10) || 0;
      var gridRowGap = parseInt(gridStyle.getPropertyValue('grid-row-gap'), 10) || 10;
      $gridCells.forEach(function ($cell) {
        MasonryLayout.resizeGridItem($cell, gridAutoRows, gridRowGap);
      });
    },
    initialise: function initialise() {
      MasonryLayout.$gridBox = document.querySelector('#cpanel-modules .card-columns');
      MasonryLayout.resizeAllGridItems(); // Watch on window resize

      var resizeTimer;
      window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(MasonryLayout.resizeAllGridItems, 300);
      });
    }
  }; // Initialise Masonry layout on full load,
  // to be sure all images/fonts are loaded, and so cards have a "final" size

  window.addEventListener('load', MasonryLayout.initialise);
})(window, document, window.Joomla);