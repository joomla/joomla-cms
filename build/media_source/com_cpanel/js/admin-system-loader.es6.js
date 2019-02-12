/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
  // Get the elements
    const elements = document.querySelectorAll('.system-counter');

    for (let i = 0, l = elements.length; l > i; i += 1) {
      const type = elements[i].getAttribute('data-type');

    Joomla.request({
      url: 'index.php?option=com_cpanel&task=system.loadSystemInfo&format=json',
      method: 'POST',
      data: 'type=' + type,
      onSuccess: (response) => {
        if (response.count > 0)
        {
          const elem = document.createElement('span');

          elem.addAttribute('class', 'pull-right badge badge-pill badge-warning');
          elem.innerHTML = parseInt(response.count);

          elements[i].parentNode.replaceChild(elem, elements[i]);
        }
        else
        {
          elements[i].classList.remove('fa-spin', 'fa-spinner');
          elements[i].classList.add('text-success', 'fa-check');
        }
      },
      onError: () => {
        elements[i].classList.remove('fa-spin', 'fa-spinner');
        elements[i].classList.add('text-danger', 'fa-remove');
      }
    });
    }
  });
})();
