/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
(() => {
  class JMultiSelect {
    constructor(formElement) {
      this.tableEl = document.querySelector(formElement);

      if (this.tableEl) {
        this.boxes = [].slice.call(this.tableEl.querySelectorAll('input[type=checkbox]'));
        this.rows = [].slice.call(document.querySelectorAll('tr[class^="row"]'));
        this.checkallToggle = document.getElementsByName('checkall-toggle')[0];

        this.doSelect = this.doSelect.bind(this);
        this.onCheckallToggleClick = this.onCheckallToggleClick.bind(this);
        this.onRowClick = this.onRowClick.bind(this);

        this.boxes.forEach((box) => {
          box.addEventListener('click', this.doSelect);
        });


        if (this.checkallToggle) {
          this.checkallToggle.addEventListener('click', this.onCheckallToggleClick);
        }

        if (this.rows.length) {
          this.rows.forEach((row) => {
            row.addEventListener('click', this.onRowClick);
          });
        }
      }
    }

    doSelect(event) {
      const current = event.target;
      let isChecked;
      let lastIndex;
      let currentIndex;
      let swap;

      if (event.shiftKey && this.last.length) {
        isChecked = current.hasAttribute(':checked');
        lastIndex = this.boxes.index(this.last);
        currentIndex = this.boxes.index(current);

        if (currentIndex < lastIndex) {
          // handle selection from bottom up
          swap = lastIndex;
          lastIndex = currentIndex;
          currentIndex = swap;
        }

        this.boxes.slice(lastIndex, currentIndex + 1).setAttribute('checked', isChecked);

        this.changeBg(this.rows[currentIndex], isChecked);
      }

      this.last = current;
    }

    // Changes the background-color on every cell inside a <tr>
    // eslint-disable-next-line class-methods-use-this
    changeBg(row, isChecked) {
      // Check if it should add or remove the background colour
      if (isChecked) {
        [].slice.call(row.querySelectorAll('td, th')).forEach((elementToMark) => {
          elementToMark.classList.add('row-selected');
        });
      } else {
        [].slice.call(row.querySelectorAll('td, th')).forEach((elementToMark) => {
          elementToMark.classList.remove('row-selected');
        });
      }
    }

    onCheckallToggleClick(event) {
      const isChecked = event.target.checked;

      this.rows.forEach((row) => {
        this.changeBg(row, isChecked);
      });
    }

    onRowClick(event) {
      const currentRowNum = this.rows.indexOf(event.target.closest('tr'));
      const currentCheckBox = this.checkallToggle ? currentRowNum + 1 : currentRowNum;
      let isChecked = this.boxes[currentCheckBox].checked;

      if (currentCheckBox >= 0) {
        if (!(event.target.id === this.boxes[currentCheckBox].id)) {
          // We will prevent selecting text to prevent artifacts
          if (event.shiftKey) {
            this.boxes[currentCheckBox].style['-webkit-user-select'] = 'none';
            this.boxes[currentCheckBox].style['-moz-user-select'] = 'none';
            this.boxes[currentCheckBox].style['-ms-user-select'] = 'none';
            this.boxes[currentCheckBox].style['user-select'] = 'none';
          }

          this.boxes[currentCheckBox].checked = !this.boxes[currentCheckBox].checked;
          isChecked = this.boxes[currentCheckBox].checked;
          Joomla.isChecked(this.boxes[currentCheckBox].checked);
        }

        this.changeBg(this.rows[currentCheckBox - 1], isChecked);

        // Restore normality
        if (event.shiftKey) {
          delete this.boxes[currentCheckBox].style['-webkit-user-select'];
          delete this.boxes[currentCheckBox].style['-moz-user-select'];
          delete this.boxes[currentCheckBox].style['-ms-user-select'];
          delete this.boxes[currentCheckBox].style['user-select'];
        }
      }
    }
  }


  ((Joomla) => {
    'use strict';

    const onBoot = () => {
      if (!Joomla) {
        // eslint-disable-next-line no-new
        new JMultiSelect('#adminForm');
      } else if (Joomla.getOptions && typeof Joomla.getOptions === 'function' && Joomla.getOptions('js-multiselect')) {
        if (Joomla.getOptions('js-multiselect').formName) {
          // eslint-disable-next-line no-new
          new JMultiSelect(`#${Joomla.getOptions('js-multiselect').formName}`);
        } else {
          // eslint-disable-next-line no-new
          new JMultiSelect('#adminForm');
        }
      }
    };

    document.addEventListener('DOMContentLoaded', onBoot);
  })(Joomla);
})(window.Joomla);
