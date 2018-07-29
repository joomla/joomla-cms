/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((jQuery, document, Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    // Handle toggle all
    [].slice.call(document.querySelectorAll('.filter-toggle-all')).forEach((button) => {
      button.addEventListener('click', () => {
        [].slice.call(document.querySelectorAll('.filter-node')).forEach((node) => {
          node.click();
        });
      });
    });

    // Update the count
    [].slice.call(document.querySelectorAll('.filter-node')).forEach(() => {
      const count = document.getElementById('jform_map_count');
      if (count) {
        count.value = document.querySelectorAll('input[type="checkbox"]:checked').length;
      }
    });

    // Expand/collapse
    const expandAccordion = document.getElementById('expandAccordion');
    if (expandAccordion) {
      expandAccordion.addEventListener('click', (event) => {
        event.preventDefault();

        if (event.target.innerText === Joomla.JText._('COM_FINDER_FILTER_SHOW_ALL')) {
          event.target.innerText = Joomla.JText._('COM_FINDER_FILTER_HIDE_ALL');

          [].slice.call(document.querySelectorAll('.collapse:not(.in)')).forEach((element) => {
            // @todo use custom elements accordion
            jQuery(element).collapse('toggle');
          });
        } else {
          event.target.innerText = Joomla.JText._('COM_FINDER_FILTER_SHOW_ALL');

          [].slice.call(document.querySelectorAll('.collapse.in')).forEach((element) => {
            // @todo use custom elements accordion
            jQuery(element).collapse('toggle');
          });
        }
      });
    }
  });
})(window.jQuery, document, Joomla);
