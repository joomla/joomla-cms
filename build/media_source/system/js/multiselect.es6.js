/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
class JMultiSelect {
  constructor(formElement) {
    this.tableEl = formElement;
    this.rowSelector = 'tr[class^="row"]';
    this.boxSelector = 'input[type="checkbox"][name="cid[]"]';
    this.rows = Array.from(this.tableEl.querySelectorAll(this.rowSelector));
    this.checkallToggle = this.tableEl.querySelector('[name="checkall-toggle"]');
    this.prevRow = null;

    this.onRowClick = this.onRowClick.bind(this);

    this.rows.forEach((row) => {
      row.addEventListener('click', this.onRowClick);
    });

    if (this.checkallToggle) {
      this.checkallToggle.addEventListener('click', ({ target }) => {
        const isChecked = target.checked;

        this.rows.forEach((row) => {
          this.changeBg(row, isChecked);
        });
      });
    }

    console.log(this)
  }

  // Changes the row class depends on selection
  changeBg(row, isChecked) {
    row.classList.toggle('row-selected', isChecked)
  }

  // Handle click on a row
  onRowClick({ target, shiftKey }) {
    // Do not interfere with links, buttons, inputs
    if (target.tagName && (target.tagName === 'A' || target.tagName === 'BUTTON'
      || target.tagName === 'SELECT' || target.tagName === 'TEXTAREA'
      || (target.tagName === 'INPUT' && !target.matches(this.boxSelector)) )) {
      return;
    }

    // Get clicked row and checkbox in it
    const currentRow = target.closest(this.rowSelector);
    const currentBox = currentRow ?
      (target.matches(this.boxSelector) ? target : currentRow.querySelector(this.boxSelector)) : false;

    if (!currentBox) {
      return;
    }

    const isChecked = !currentBox.checked;

    if (currentBox !== target) {
      currentBox.checked = isChecked;
      Joomla.isChecked(isChecked, this.tableEl);
    }
    this.changeBg(currentRow, isChecked);

    // Select rows in range
    if (shiftKey && this.prevRow) {
      // Prevent text selection
      document.getSelection().removeAllRanges();

      // Re-query all rows, because they may be modified during sort operations
      const rows = Array.from(this.tableEl.querySelectorAll(this.rowSelector));
      const idxStart = rows.indexOf(this.prevRow);
      const idxEnd = rows.indexOf(currentRow);

      // Check for more than 2 row selected
      if (Math.abs(idxStart - idxEnd) > 1) {
        const slice = idxStart < idxEnd ? rows.slice(idxStart, idxEnd + 1) : rows.slice(idxEnd, idxStart + 1);

        slice.forEach((row) => {
          if (row === currentRow) {
            return;
          }
          const rowBox = row.querySelector(this.boxSelector);
          if (rowBox) {
            rowBox.checked = isChecked;
            Joomla.isChecked(isChecked, this.tableEl);
            this.changeBg(row, isChecked);
          }
        });
      }
    }

    this.prevRow = currentRow;
  }
}

const onBoot = (container) => {
  let formId = '#adminForm';
  if (Joomla && Joomla.getOptions('js-multiselect', {}).formName) {
    formId = `#${Joomla.getOptions('js-multiselect', {}).formName}`;
  }
  const formElement = container.querySelector(formId);
  if (formElement && !('multiselect' in formElement.dataset)) {
    formElement.dataset.multiselect = '';
    // eslint-disable-next-line no-new
    new JMultiSelect(formElement);
  }
};

document.addEventListener('DOMContentLoaded', () => onBoot(document));
document.addEventListener('joomla:updated', ({ target }) => onBoot(target));
