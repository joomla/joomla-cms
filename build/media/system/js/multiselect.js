/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
Joomla = window.Joomla || {};

(function (Joomla) {
  Joomla.JMultiSelect = function (formElement) {
    'use strict';

    let last; let boxes;


    const initialize = function (formElement) {
			    const tableEl = document.querySelector(formElement);

			    if (tableEl) {
				    boxes = tableEl.querySelectorAll('input[type=checkbox]');
				    let i = 0; const
          countB = boxes.length;
				    for (i; boxes < countB; i++) {
					    boxes[i].addEventListener('click', (e) => {
						    doselect(e);
					    });
				    }
			    }
		    };


    var doselect = function (e) {
			    const current = e.target; let isChecked; let lastIndex; let currentIndex; let
        swap;
			    if (e.shiftKey && last.length) {
				    isChecked = current.hasAttribute(':checked');
				    lastIndex = boxes.index(last);
				    currentIndex = boxes.index(current);
				    if (currentIndex < lastIndex) {
					    // handle selection from bottom up
					    swap = lastIndex;
					    lastIndex = currentIndex;
					    currentIndex = swap;
				    }
				    boxes.slice(lastIndex, currentIndex + 1).setAttribute('checked', isChecked);
			    }

			    last = current;
		    };
    initialize(formElement);
  };

  document.addEventListener('DOMContentLoaded', (event) => {
    'use strict';

    if (Joomla.getOptions && typeof Joomla.getOptions === 'function' && Joomla.getOptions('js-multiselect')) {
      if (Joomla.getOptions('js-multiselect').formName) {
        Joomla.JMultiSelect(Joomla.getOptions('js-multiselect').formName);
      } else {
        Joomla.JMultiSelect('adminForm');
      }
    }

    const rows = document.querySelectorAll('tr[class^="row"]');

    // Changes the background-color on every cell inside a <tr>
    function changeBg(item, checkall) {
      // Check if it should add or remove the background colour
      if (checkall.checked) {
        item.querySelectorAll('td').forEach((td) => {
          td.classList.add('row-selected');
        });
        item.querySelectorAll('th').forEach((th) => {
          th.classList.add('row-selected');
        });
      } else {
        item.querySelectorAll('td').forEach((td) => {
          td.classList.remove('row-selected');
        });
        item.querySelectorAll('th').forEach((th) => {
          th.classList.remove('row-selected');
        });
      }
    }

    const checkallToggle = document.getElementsByName('checkall-toggle')[0];

    if (checkallToggle) {
      checkallToggle.addEventListener('click', function (event) {
        const checkall = this;

        rows.forEach((row, index) => {
          changeBg(row, checkall);
        });
      });
    }

    if (rows.length) {
      rows.forEach((row, index) => {
        row.addEventListener('click', function (event) {
          const clicked = `cb${index}`;


          const cbClicked = document.getElementById(clicked);

          if (cbClicked) {
            if (!(event.target.id == clicked)) {
              cbClicked.checked = !cbClicked.checked;
              Joomla.isChecked(cbClicked.checked);
            }

            changeBg(this, cbClicked);
          }
        });
      });
    }
  });
}(Joomla));
