/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const elems = [].slice.call(document.querySelectorAll('#new-modules-list a.select-link'));

    elems.forEach((elem) => {
      elem.addEventListener('click', (event) => {
        let target = event.currentTarget;

        // There is some bug with events in iframe where currentTarget is "null" => prevent this here by bubble up
        if (!target)
        {
          target = event.target;

          if (target && !target.classList.contains('select-link'))
          {
            target = target.parentNode;
          }
        }

        const functionName = target.getAttribute('data-function');

        if (functionName && typeof window.parent[functionName] === 'function')
        {
          window.parent[functionName](target);
        }
      });
    })
  });
})();
