/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // The container where the draggable will be enabled
  let url;
  let direction;
  let isNested;
  let container = document.querySelector('.js-draggable');
  const orderRows = container.querySelectorAll('[name="order[]"]');

  if (container) {
    /** The script expects a form with a class js-form
     *  A table with the tbody with a class js-draggable
     *                         with a data-url with the ajax request end point and
     *                         with a data-direction for asc/desc
     */
    url = container.getAttribute('data-url');
    direction = container.getAttribute('data-direction');
    isNested = container.getAttribute('data-nested');
  } else if (Joomla.getOptions('draggable-list')) {
    const options = Joomla.getOptions('draggable-list');

    container = document.querySelector(options.id);
    /**
     * This is here to make the transition to new forms easier.
     */
    if (!container.classList.contains('js-draggable')) {
      container.classList.add('js-draggable');
    }

    ({ url } = options);
    ({ direction } = options);
    isNested = options.nested;
  }

  if (container) {
    // Add data order attribute for initial ordering
    for (let i = 0, l = orderRows.length; l > i; i += 1) {
      orderRows[i].setAttribute('data-order', i + 1);
    }

    // IOS 10 BUG
    document.addEventListener('touchstart', () => {}, false);

    const getOrderData = (wrapper, dir) => {
      let i;
      let l;
      const result = [];
      const rows = [].slice.call(wrapper.querySelectorAll('[name="order[]"]'));
      const inputRows = [].slice.call(wrapper.querySelectorAll('[name="cid[]"]'));

      if (dir === 'desc') {
        // Reverse the array
        rows.reverse();
        inputRows.reverse();
      }

      // Get the order array
      for (i = 0, l = rows.length; l > i; i += 1) {
        rows[i].value = i + 1;
        result.push(`order[]=${encodeURIComponent(i)}`);
        result.push(`cid[]=${encodeURIComponent(inputRows[i].value)}`);
      }

      return result;
    };

    // eslint-disable-next-line no-undef
    dragula([container], {
      // Y axis is considered when determining where an element would be dropped
      direction: 'vertical',
      // elements are moved by default, not copied
      copy: false,
      // elements in copy-source containers can be reordered
      // copySortSource: true,
      // spilling will put the element back where it was dragged from, if this is true
      revertOnSpill: true,
      // spilling will `.remove` the element, if this is true
      // removeOnSpill: false,

      accepts(el, target, source, sibling) {
        if (isNested) {
          if (sibling !== null) {
            return sibling.getAttribute('data-dragable-group') && sibling.getAttribute('data-dragable-group') === el.getAttribute('data-dragable-group');
          }

          return sibling === null || (sibling && sibling.tagName.toLowerCase() === 'tr');
        }

        return sibling === null || (sibling && sibling.tagName.toLowerCase() === 'tr');
      },
    })
      .on('drag', () => {

      })
      .on('cloned', () => {
        const el = document.querySelector('.gu-mirror');
        el.classList.add('table');
      })
      .on('drop', () => {
        if (url) {
        // Detach task field if exists
          const task = document.querySelector('[name="task"]');

          // Detach task field if exists
          if (task) {
            task.setAttribute('name', 'some__Temporary__Name__');
          }

          // Prepare the options
          const ajaxOptions = {
            url,
            method: 'POST',
            data: getOrderData(container, direction).join('&'),
            perform: true,
          };

          Joomla.request(ajaxOptions);

          // Re-Append original task field
          if (task) {
            task.setAttribute('name', 'task');
          }
        }
      })
      .on('dragend', () => {
        const elements = container.querySelectorAll('[name="order[]"]');
        // Reset data order attribute for initial ordering
        for (let i = 0, l = elements.length; l > i; i += 1) {
          elements[i].setAttribute('data-order', i + 1);
        }
      });
  }
});
