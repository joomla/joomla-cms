/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function addListeners() {
  document.addEventListener('DOMContentLoaded', () => {
    // Handle toggle all
    [].slice.call(document.querySelectorAll('.toggle-all')).forEach((button) => {
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

        if (expandAccordion.innerText === Joomla.JText._('COM_FINDER_FILTER_SHOW_ALL')) {
          expandAccordion.innerText = Joomla.JText._('COM_FINDER_FILTER_HIDE_ALL');

          jQuery('.collapse:not(.in)').each(function collapse() {
            jQuery(this).collapse('toggle');
          });
        } else {
          expandAccordion.innerText = Joomla.JText._('COM_FINDER_FILTER_SHOW_ALL');

          jQuery('.collapse.in').each(function collapse() {
            jQuery(this).collapse('toggle');
          });
        }
      });
    }
  });
}());
