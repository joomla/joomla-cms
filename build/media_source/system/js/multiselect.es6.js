/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
class JMultiSelect {
  constructor(container) {
    this.tableEl = container;
    this.formEl = container.closest('form');
    this.rowSelector = 'tr[class^="row"]';
    this.boxSelector = 'input[type="checkbox"][name="cid[]"]';
    this.checkallToggle = this.tableEl.querySelector('[name="checkall-toggle"]');
    this.prevRow = null;

    // Use delegation listener, to allow dynamic tables
    this.tableEl.addEventListener('click', (event) => {
      if (!event.target.closest(this.rowSelector)) {
        return;
      }
      this.onRowClick(event);
    });

    if (this.checkallToggle) {
      this.checkallToggle.addEventListener('click', ({ target }) => {
        const isChecked = target.checked;

        this.getRows().forEach((row) => {
          this.changeBg(row, isChecked);
        });
      });
    }
  }

  getRows() {
    return Array.from(this.tableEl.querySelectorAll(this.rowSelector));
  }

  // Changes the row class depends on selection
  // eslint-disable-next-line class-methods-use-this
  changeBg(row, isChecked) {
    row.classList.toggle('row-selected', isChecked);
  }

  // Handle click on a row
  onRowClick({ target, shiftKey }) {
    // Do not interfere with links, buttons, inputs and other interactive elements
    if (!target.matches(this.boxSelector) && target.closest('a, button, input, select, textarea, details, dialog, audio, video')) {
      return;
    }

    // Get clicked row and checkbox in it
    const currentRow = target.closest(this.rowSelector);
    const currentBox = target.matches(this.boxSelector) ? target : currentRow.querySelector(this.boxSelector);
    if (!currentBox) {
      return;
    }

    const isChecked = (currentBox !== target) ? !currentBox.checked : currentBox.checked;

    if (isChecked !== currentBox.checked) {
      currentBox.checked = isChecked;
      Joomla.isChecked(isChecked, this.formEl);
    }
    this.changeBg(currentRow, isChecked);

    // Select rows in range
    if (shiftKey && this.prevRow) {
      // Prevent text selection
      document.getSelection().removeAllRanges();

      // Re-query all rows, because they may be modified during sort operations
      const rows = this.getRows();
      const idxStart = rows.indexOf(this.prevRow);
      const idxEnd = rows.indexOf(currentRow);

      // Check for more than 2 row selected
      if (idxStart >= 0 && idxEnd >= 0 && Math.abs(idxStart - idxEnd) > 1) {
        const slice = idxStart < idxEnd ? rows.slice(idxStart, idxEnd + 1) : rows.slice(idxEnd, idxStart + 1);

        slice.forEach((row) => {
          if (row === currentRow) {
            return;
          }
          const rowBox = row.querySelector(this.boxSelector);
          if (rowBox && rowBox.checked !== isChecked) {
            rowBox.checked = isChecked;
            this.changeBg(row, isChecked);
            Joomla.isChecked(isChecked, this.formEl);
          }
        });
      }
    }

    this.prevRow = currentRow;
  }
}

const onBoot = (container) => {
  let selector = '#adminForm';
  const confSelector = window.Joomla ? Joomla.getOptions('js-multiselect', {}).formName : '';

  if (confSelector) {
    const pref = confSelector[0];
    selector = (pref !== '.' && pref !== '#') ? `#${confSelector}` : confSelector;
  }

  container.querySelectorAll(selector).forEach((formElement) => {
    if (formElement && !('multiselect' in formElement.dataset)) {
      formElement.dataset.multiselect = '';
      // eslint-disable-next-line no-new
      new JMultiSelect(formElement);
    }
  });
};

onBoot(document);
document.addEventListener('joomla:updated', ({ target }) => onBoot(target));
