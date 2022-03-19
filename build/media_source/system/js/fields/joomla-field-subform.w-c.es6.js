/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((customElements) => {
  'use strict';

  const KEYCODE = {
    SPACE: 32,
    ESC: 27,
    ENTER: 13,
  };

  /**
   * Helper for testing whether a selection modifier is pressed
   * @param {Event} event
   *
   * @returns {boolean|*}
   */
  function hasModifier(event) {
    return (event.ctrlKey || event.metaKey || event.shiftKey);
  }

  class JoomlaFieldSubform extends HTMLElement {
    // Attribute getters
    get buttonAdd() { return this.getAttribute('button-add'); }

    get buttonRemove() { return this.getAttribute('button-remove'); }

    get buttonMove() { return this.getAttribute('button-move'); }

    get rowsContainer() { return this.getAttribute('rows-container'); }

    get repeatableElement() { return this.getAttribute('repeatable-element'); }

    get minimum() { return this.getAttribute('minimum'); }

    get maximum() { return this.getAttribute('maximum'); }

    get name() { return this.getAttribute('name'); }

    set name(value) {
      // Update the template
      this.template = this.template.replace(new RegExp(` name="${this.name.replace(/[[\]]/g, '\\$&')}`, 'g'), ` name="${value}`);

      this.setAttribute('name', value);
    }

    constructor() {
      super();

      const that = this;

      // Get the rows container
      this.containerWithRows = this;

      if (this.rowsContainer) {
        const allContainers = this.querySelectorAll(this.rowsContainer);

        // Find closest, and exclude nested
        Array.from(allContainers).forEach((container) => {
          if (container.closest('joomla-field-subform') === this) {
            this.containerWithRows = container;
          }
        });
      }

      // Keep track of row index, this is important to avoid a name duplication
      // Note: php side should reset the indexes each time, eg: $value = array_values($value);
      this.lastRowIndex = this.getRows().length - 1;

      // Template for the repeating group
      this.template = '';

      // Prepare a row template, and find available field names
      this.prepareTemplate();

      // Bind buttons
      if (this.buttonAdd || this.buttonRemove) {
        this.addEventListener('click', (event) => {
          let btnAdd = null;
          let btnRem = null;

          if (that.buttonAdd) {
            btnAdd = event.target.matches(that.buttonAdd)
              ? event.target
              : event.target.closest(that.buttonAdd);
          }

          if (that.buttonRemove) {
            btnRem = event.target.matches(that.buttonRemove)
              ? event.target
              : event.target.closest(that.buttonRemove);
          }

          // Check active, with extra check for nested joomla-field-subform
          if (btnAdd && btnAdd.closest('joomla-field-subform') === that) {
            let row = btnAdd.closest(that.repeatableElement);
            row = row && row.closest('joomla-field-subform') === that ? row : null;
            that.addRow(row);
            event.preventDefault();
          } else if (btnRem && btnRem.closest('joomla-field-subform') === that) {
            const row = btnRem.closest(that.repeatableElement);
            that.removeRow(row);
            event.preventDefault();
          }
        });

        this.addEventListener('keydown', (event) => {
          if (event.keyCode !== KEYCODE.SPACE) return;
          const isAdd = that.buttonAdd && event.target.matches(that.buttonAdd);
          const isRem = that.buttonRemove && event.target.matches(that.buttonRemove);

          if ((isAdd || isRem) && event.target.closest('joomla-field-subform') === that) {
            let row = event.target.closest(that.repeatableElement);
            row = row && row.closest('joomla-field-subform') === that ? row : null;

            if (isRem && row) {
              that.removeRow(row);
            } else if (isAdd) {
              that.addRow(row);
            }
            event.preventDefault();
          }
        });
      }

      // Sorting
      if (this.buttonMove) {
        this.setUpDragSort();
      }
    }

    /**
     * Search for existing rows
     * @returns {HTMLElement[]}
     */
    getRows() {
      const rows = Array.from(this.containerWithRows.children);
      const result = [];

      // Filter out the rows
      rows.forEach((row) => {
        if (row.matches(this.repeatableElement)) {
          result.push(row);
        }
      });

      return result;
    }

    /**
     * Prepare a row template
     */
    prepareTemplate() {
      const tmplElement = [].slice.call(this.children).filter((el) => el.classList.contains('subform-repeatable-template-section'));

      if (tmplElement[0]) {
        this.template = tmplElement[0].innerHTML;
      }

      if (!this.template) {
        throw new Error('The row template is required for the subform element to work');
      }
    }

    /**
     * Add new row
     * @param {HTMLElement} after
     * @returns {HTMLElement}
     */
    addRow(after) {
      // Count how many we already have
      const count = this.getRows().length;
      if (count >= this.maximum) {
        return null;
      }

      // Make a new row from the template
      let tmpEl;
      if (this.containerWithRows.nodeName === 'TBODY' || this.containerWithRows.nodeName === 'TABLE') {
        tmpEl = document.createElement('tbody');
      } else {
        tmpEl = document.createElement('div');
      }
      tmpEl.innerHTML = this.template;
      const row = tmpEl.children[0];

      // Add to container
      if (after) {
        after.parentNode.insertBefore(row, after.nextSibling);
      } else {
        this.containerWithRows.append(row);
      }

      // Add draggable attributes
      if (this.buttonMove) {
        row.setAttribute('draggable', 'false');
        row.setAttribute('aria-grabbed', 'false');
        row.setAttribute('tabindex', '0');
      }

      // Marker that it is new
      row.setAttribute('data-new', '1');
      // Fix names and ids, and reset values
      this.fixUniqueAttributes(row, count);

      // Tell about the new row
      this.dispatchEvent(new CustomEvent('subform-row-add', {
        detail: { row },
        bubbles: true,
      }));

      row.dispatchEvent(new CustomEvent('joomla:updated', {
        bubbles: true,
        cancelable: true,
      }));

      return row;
    }

    /**
     * Remove the row
     * @param {HTMLElement} row
     */
    removeRow(row) {
      // Count how much we have
      const count = this.getRows().length;
      if (count <= this.minimum) {
        return;
      }

      // Tell about the row will be removed
      this.dispatchEvent(new CustomEvent('subform-row-remove', {
        detail: { row },
        bubbles: true,
      }));

      row.dispatchEvent(new CustomEvent('joomla:removed', {
        bubbles: true,
        cancelable: true,
      }));

      row.parentNode.removeChild(row);
    }

    /**
     * Fix name and id for fields that are in the row
     * @param {HTMLElement} row
     * @param {Number} count
     */
    fixUniqueAttributes(row, count) {
      const countTmp = count || 0;
      const group = row.getAttribute('data-group'); // current group name
      const basename = row.getAttribute('data-base-name');
      const countnew = Math.max(this.lastRowIndex, countTmp);
      const groupnew = basename + countnew; // new group name

      this.lastRowIndex = countnew + 1;
      row.setAttribute('data-group', groupnew);

      // Fix inputs that have a "name" attribute
      let haveName = row.querySelectorAll('[name]');
      const ids = {}; // Collect id for fix checkboxes and radio

      // Filter out nested
      haveName = [].slice.call(haveName).filter((el) => {
        if (el.nodeName === 'JOOMLA-FIELD-SUBFORM') {
          // Skip self in .closest() call
          return el.parentElement.closest('joomla-field-subform') === this;
        }

        return el.closest('joomla-field-subform') === this;
      });

      haveName.forEach((elem) => {
        const $el = elem;
        const name = $el.getAttribute('name');
        const aria = $el.getAttribute('aria-describedby');
        const id = name
          .replace(/(\[\]$)/g, '')
          .replace(/(\]\[)/g, '__')
          .replace(/\[/g, '_')
          .replace(/\]/g, ''); // id from name
        const nameNew = name.replace(`[${group}][`, `[${groupnew}][`); // New name
        let idNew = id.replace(group, groupnew).replace(/\W/g, '_'); // Count new id
        let countMulti = 0; // count for multiple radio/checkboxes
        let forOldAttr = id; // Fix "for" in the labels

        if ($el.type === 'checkbox' && name.match(/\[\]$/)) { // <input type="checkbox" name="name[]"> fix
          // Recount id
          countMulti = ids[id] ? ids[id].length : 0;
          if (!countMulti) {
            // Set the id for fieldset and group label
            const fieldset = $el.closest('fieldset.checkboxes');

            const elLbl = row.querySelector(`label[for="${id}"]`);

            if (fieldset) {
              fieldset.setAttribute('id', idNew);
            }

            if (elLbl) {
              elLbl.setAttribute('for', idNew);
              elLbl.setAttribute('id', `${idNew}-lbl`);
            }
          }
          forOldAttr += countMulti;
          idNew += countMulti;
        } else if ($el.type === 'radio') { // <input type="radio"> fix
          // Recount id
          countMulti = ids[id] ? ids[id].length : 0;
          if (!countMulti) {
            // Set the id for fieldset and group label
            const fieldset = $el.closest('fieldset.radio');

            const elLbl = row.querySelector(`label[for="${id}"]`);

            if (fieldset) {
              fieldset.setAttribute('id', idNew);
            }

            if (elLbl) {
              elLbl.setAttribute('for', idNew);
              elLbl.setAttribute('id', `${idNew}-lbl`);
            }
          }
          forOldAttr += countMulti;
          idNew += countMulti;
        }

        // Cache already used id
        if (ids[id]) {
          ids[id].push(true);
        } else {
          ids[id] = [true];
        }

        // Replace the name to new one
        $el.name = nameNew;
        if ($el.id) {
          $el.id = idNew;
        }

        if (aria) {
          $el.setAttribute('aria-describedby', `${nameNew}-desc`);
        }

        // Check if there is a label for this input
        const lbl = row.querySelector(`label[for="${forOldAttr}"]`);
        if (lbl) {
          lbl.setAttribute('for', idNew);
          lbl.setAttribute('id', `${idNew}-lbl`);
        }
      });
    }

    /**
     * Use of HTML Drag and Drop API
     * https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API
     * https://www.sitepoint.com/accessible-drag-drop/
     */
    setUpDragSort() {
      const that = this; // Self reference
      let item = null; // Storing the selected item
      let touched = false; // We have a touch events

      // Find all existing rows and add draggable attributes
      const rows = Array.from(this.getRows());

      rows.forEach((row) => {
        row.setAttribute('draggable', 'false');
        row.setAttribute('aria-grabbed', 'false');
        row.setAttribute('tabindex', '0');
      });

      // Helper method to test whether Handler was clicked
      function getMoveHandler(element) {
        return !element.form // This need to test whether the element is :input
        && element.matches(that.buttonMove) ? element : element.closest(that.buttonMove);
      }

      // Helper method to move row to selected position
      function switchRowPositions(src, dest) {
        let isRowBefore = false;
        if (src.parentNode === dest.parentNode) {
          for (let cur = src; cur; cur = cur.previousSibling) {
            if (cur === dest) {
              isRowBefore = true;
              break;
            }
          }
        }

        if (isRowBefore) {
          dest.parentNode.insertBefore(src, dest);
        } else {
          dest.parentNode.insertBefore(src, dest.nextSibling);
        }
      }

      /**
       *  Touch interaction:
       *
       *  - a touch of "move button" marks a row draggable / "selected",
       *     or deselect previous selected
       *
       *  - a touch of "move button" in the destination row will move
       *     a selected row to a new position
       */
      this.addEventListener('touchstart', (event) => {
        touched = true;

        // Check for .move button
        const handler = getMoveHandler(event.target);

        const row = handler ? handler.closest(that.repeatableElement) : null;

        if (!row || row.closest('joomla-field-subform') !== that) {
          return;
        }

        // First selection
        if (!item) {
          row.setAttribute('draggable', 'true');
          row.setAttribute('aria-grabbed', 'true');
          item = row;
        } else { // Second selection
          // Move to selected position
          if (row !== item) {
            switchRowPositions(item, row);
          }

          item.setAttribute('draggable', 'false');
          item.setAttribute('aria-grabbed', 'false');
          item = null;
        }

        event.preventDefault();
      });

      // Mouse interaction
      // - mouse down, enable "draggable" and allow to drag the row,
      // - mouse up, disable "draggable"
      this.addEventListener('mousedown', ({ target }) => {
        if (touched) return;

        // Check for .move button
        const handler = getMoveHandler(target);

        const row = handler ? handler.closest(that.repeatableElement) : null;

        if (!row || row.closest('joomla-field-subform') !== that) {
          return;
        }

        row.setAttribute('draggable', 'true');
        row.setAttribute('aria-grabbed', 'true');
        item = row;
      });

      this.addEventListener('mouseup', () => {
        if (item && !touched) {
          item.setAttribute('draggable', 'false');
          item.setAttribute('aria-grabbed', 'false');
          item = null;
        }
      });

      // Keyboard interaction
      // - "tab" to navigate to needed row,
      // - modifier (ctr,alt,shift) + "space" select the row,
      // - "tab" to select destination,
      // - "enter" to place selected row in to destination
      // - "esc" to cancel selection
      this.addEventListener('keydown', (event) => {
        if ((event.keyCode !== KEYCODE.ESC
          && event.keyCode !== KEYCODE.SPACE
          && event.keyCode !== KEYCODE.ENTER) || event.target.form
          || !event.target.matches(that.repeatableElement)) {
          return;
        }

        const row = event.target;

        // Make sure we handle correct children
        if (!row || row.closest('joomla-field-subform') !== that) {
          return;
        }

        // Space is the selection or unselection keystroke
        if (event.keyCode === KEYCODE.SPACE && hasModifier(event)) {
          // Unselect previously selected
          if (row.getAttribute('aria-grabbed') === 'true') {
            row.setAttribute('draggable', 'false');
            row.setAttribute('aria-grabbed', 'false');
            item = null;
          } else { // Select new
            // If there was previously selected
            if (item) {
              item.setAttribute('draggable', 'false');
              item.setAttribute('aria-grabbed', 'false');
              item = null;
            }

            // Mark new selection
            row.setAttribute('draggable', 'true');
            row.setAttribute('aria-grabbed', 'true');
            item = row;
          }

          // Prevent default to suppress any native actions
          event.preventDefault();
        }

        // Escape is the abort keystroke (for any target element)
        if (event.keyCode === KEYCODE.ESC && item) {
          item.setAttribute('draggable', 'false');
          item.setAttribute('aria-grabbed', 'false');
          item = null;
        }

        // Enter, to place selected item in selected position
        if (event.keyCode === KEYCODE.ENTER && item) {
          item.setAttribute('draggable', 'false');
          item.setAttribute('aria-grabbed', 'false');

          // Do nothing here
          if (row === item) {
            item = null;
            return;
          }

          // Move the item to selected position
          switchRowPositions(item, row);

          event.preventDefault();
          item = null;
        }
      });

      // dragstart event to initiate mouse dragging
      this.addEventListener('dragstart', ({ dataTransfer }) => {
        if (item) {
          // We going to move the row
          dataTransfer.effectAllowed = 'move';

          // This need to work in Firefox and IE10+
          dataTransfer.setData('text', '');
        }
      });

      this.addEventListener('dragover', (event) => {
        if (item) {
          event.preventDefault();
        }
      });

      // Handle drag action, move element to hovered position
      this.addEventListener('dragenter', ({ target }) => {
        // Make sure the target in the correct container
        if (!item || target.parentElement.closest('joomla-field-subform') !== that) {
          return;
        }

        // Find a hovered row
        const row = target.closest(that.repeatableElement);

        // One more check for correct parent
        if (!row || row.closest('joomla-field-subform') !== that) return;

        switchRowPositions(item, row);
      });

      // dragend event to clean-up after drop or abort
      // which fires whether or not the drop target was valid
      this.addEventListener('dragend', () => {
        if (item) {
          item.setAttribute('draggable', 'false');
          item.setAttribute('aria-grabbed', 'false');
          item = null;
        }
      });
    }
  }

  customElements.define('joomla-field-subform', JoomlaFieldSubform);
})(customElements);
