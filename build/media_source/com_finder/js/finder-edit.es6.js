/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(() => {
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

    Array.from(document.querySelectorAll('.js-filter'))
      .forEach((button) => {
        button.addEventListener('click', (event) => {
          const btn = event.currentTarget;
          Array.from(document.querySelectorAll(`.${btn.dataset.id}`))
            .map((el) => el.click());
        });
      });

    // Expand/collapse
    const expandAccordion = document.getElementById('expandAccordion');
    if (expandAccordion) {
      expandAccordion.addEventListener('click', (event) => {
        event.preventDefault();
        let elements;

        if (event.target.innerText === Joomla.Text._('COM_FINDER_FILTER_SHOW_ALL')) {
          event.target.innerText = Joomla.Text._('COM_FINDER_FILTER_HIDE_ALL');

          elements = [].slice.call(document.querySelectorAll('.accordion-button.collapsed'));
        } else {
          event.target.innerText = Joomla.Text._('COM_FINDER_FILTER_SHOW_ALL');

          elements = [].slice.call(document.querySelectorAll('.accordion-button:not(.collapsed)'));
        }

        if (elements) {
          elements.forEach((element) => {
            element.click();
          });
        }
      });
    }
  });
})();
