/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(customElements){
    "use strict";

    const KEYCODE = {
        SPACE: 32,
        ESC: 27,
        ENTER: 13
    };

    // Find matchesFn with vendor prefix
    let matchesFn = 'matches';
    ['matches', 'msMatchesSelector'].some(function(fn) {
        if (typeof document.body[fn] === 'function') {
            matchesFn = fn;
            return true;
        }
        return false;
    });

    class JoomlaFieldSubform extends HTMLElement {

        // Attribute getters
        get buttonAdd()         { return this.getAttribute('button-add'); }
        get buttonRemove()      { return this.getAttribute('button-remove'); }
        get buttonMove()        { return this.getAttribute('button-move'); }
        get rowsContainer()     { return this.getAttribute('rows-container'); }
        get repeatableElement() { return this.getAttribute('repeatable-element'); }
        get minimum()           { return this.getAttribute('minimum'); }
        get maximum()           { return this.getAttribute('maximum'); }
        get name()              { return this.getAttribute('name'); }

        set name(value)         {
            // Update the template
            this.template = this.template.replace(new RegExp(' name="' + this.name.replace(/[\[\]]/g, '\\$&'), 'g'), ' name="' + value);

            return this.setAttribute('name', value);
        }

        constructor() {
            super();

            let that = this;

            // Get the rows container
            this.containerWithRows = this;

            if (this.rowsContainer) {
                let allContainers = this.querySelectorAll(this.rowsContainer);

                // Find closest, and exclude nested
                for (let i = 0, l = allContainers.length; i < l; i++) {
                    if (closest(allContainers[i], 'joomla-field-subform') === this) {
                        this.containerWithRows = allContainers[i];
                        break;
                    }
                }
            }

            // Last row number, help to avoid the name duplications
            this.lastRowNum = this.getRows().length;

            // Template for the repeating group
            this.template = '';

            // Prepare a row template, and find available field names
            this.prepareTemplate();

            // Bind buttons
            if (this.buttonAdd || this.buttonRemove) {
                this.addEventListener('click', function(event) {
                    let btnAdd = null, btnRem = null;

                    if (that.buttonAdd) {
                        btnAdd = event.target[matchesFn](that.buttonAdd) ? event.target : closest(event.target, that.buttonAdd);
                    }

                    if (that.buttonRemove) {
                        btnRem = event.target[matchesFn](that.buttonRemove) ? event.target : closest(event.target, that.buttonRemove);
                    }

                    // Check actine, with extra check for nested joomla-field-subform
                    if (btnAdd && closest(btnAdd, 'joomla-field-subform') === that) {
                        let row = closest(btnAdd, that.repeatableElement);
                        row = closest(row, 'joomla-field-subform') === that ? row : null;
                        that.addRow(row);
                        event.preventDefault();
                    } else if (btnRem && closest(btnRem, 'joomla-field-subform') === that) {
                        let row = closest(btnRem, that.repeatableElement);
                        that.removeRow(row);
                        event.preventDefault();
                    }
                });

                this.addEventListener('keydown', function(event) {
                    if (event.keyCode !== KEYCODE.SPACE) return;
                    let isAdd = that.buttonAdd && event.target[matchesFn](that.buttonAdd);
                    let isRem = that.buttonRemove && event.target[matchesFn](that.buttonRemove);

                    if ((isAdd || isRem) && closest(event.target, 'joomla-field-subform') === that) {
                        let row = closest(event.target, that.repeatableElement);
                        row = closest(row, 'joomla-field-subform') === that ? row : null;
                        if (isRem && row) {
                            that.removeRow(row);
                        } else if (isAdd) {
                            that.addRow(row)
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
        getRows () {
            let rows = this.containerWithRows.children,
                matchesFn = document.body['msMatchesSelector'] ? 'msMatchesSelector' : 'matches',
                result = [];

            // Filter out the rows
            for (let i = 0, l = rows.length; i < l; i++) {
                if (rows[i][matchesFn](this.repeatableElement)) {
                    result.push(rows[i]);
                }
            }

            return result;
        }

        /**
         * Prepare a row template
         */
        prepareTemplate () {
            let tmplElement = [].slice.call(this.children).filter(function(el){
                return el.classList.contains('subform-repeatable-template-section');
            });

            if (tmplElement[0]) {
                this.template = tmplElement[0].innerHTML;
            }

            if (!this.template) {
                throw new Error('The row template are required to subform element to work')
            }
        }

        /**
         * Add new row
         * @param {HTMLElement} after
         * @returns {HTMLElement}
         */
        addRow (after) {
            // Count how much we already have
            const count = this.getRows().length;
            if (count >= this.maximum){
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
            let row = tmpEl.children[0];

            // Add to container
            if (after) {
                after.parentNode.insertBefore(row, after.nextSibling);
            } else {
                this.containerWithRows.append(row);
            }

            // Add dragable attributes
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
                detail:     {row: row},
                bubbles:    true
            }));

            if (window.Joomla) {
                Joomla.Event.dispatch(row, 'joomla:updated');
            }

            return row;
        }

        /**
         * Remove the row
         * @param {HTMLElement} row
         */
        removeRow (row) {
            // Count how much we have
            const count = this.getRows().length;
            if (count <= this.minimum){
                return;
            }

            // Tell about the row will be removed
            this.dispatchEvent(new CustomEvent('subform-row-remove', {
                detail:     {row: row},
                bubbles:    true
            }));

            if (window.Joomla) {
                Joomla.Event.dispatch(row, 'joomla:removed');
            }

            row.parentNode.removeChild(row);
        }

        /**
         * Fix names ind id`s for field that in the row
         * @param {HTMLElement} row
         * @param {Number} count
         */
        fixUniqueAttributes(row, count) {
            this.lastRowNum++;
            count = count || 0;

            let group    = row.getAttribute('data-group'), // current group name
                basename = row.getAttribute('data-base-name'), // group base name, without count
                countnew = Math.max(this.lastRowNum, count + 1),
                groupnew = basename + countnew; // new group name

            this.lastRowNum = countnew;
            row.setAttribute('data-group', groupnew);

            // Fix inputs that have a "name" attribute
            let haveName = row.querySelectorAll('[name]'),
                ids = {}; // Collect id for fix checkboxes and radio

            // Filter out nested
            haveName = [].slice.call(haveName).filter(el => {
                return closest(el, 'joomla-field-subform') === this;
            });

            for (let i = 0, l = haveName.length; i < l; i++) {
                let $el     = haveName[i],
                    name    = $el.getAttribute('name'),
                    id      = name.replace(/(\[\]$)/g, '').replace(/(\]\[)/g, '__').replace(/\[/g, '_').replace(/\]/g, ''), // id from name
                    nameNew = name.replace('[' + group + '][', '['+ groupnew +']['), // New name
                    idNew   = id.replace(group, groupnew), // Count new id
                    countMulti = 0,  // count for multiple radio/checkboxes
                    forOldAttr = id; // Fix "for" in the labels

                if ($el.type === 'checkbox' && name.match(/\[\]$/)) { // <input type="checkbox" name="name[]"> fix
                    // Recount id
                    countMulti = ids[id] ? ids[id].length : 0;
                    if (!countMulti) {
                        // Set the id for fieldset and group label
                        let fieldset = closest($el, 'fieldset.checkboxes'),
                            elLbl = row.querySelector('label[for="' + id + '"]');

                        if (fieldset) {
                            fieldset.setAttribute('id', idNew);
                        }

                        if (elLbl) {
                            elLbl.setAttribute('for', idNew);
                            elLbl.setAttribute('id', idNew + '-lbl');
                        }
                    }
                    forOldAttr = forOldAttr + countMulti;
                    idNew = idNew + countMulti;
                }
                else if ($el.type === 'radio') { // <input type="radio"> fix
                    // Recount id
                    countMulti = ids[id] ? ids[id].length : 0;
                    if (!countMulti) {
                        // Set the id for fieldset and group label
                        let fieldset = closest($el, 'fieldset.radio'),
                            elLbl = row.querySelector('label[for="' + id + '"]');

                        if (fieldset) {
                            fieldset.setAttribute('id', idNew);
                        }

                        if (elLbl) {
                            elLbl.setAttribute('for', idNew);
                            elLbl.setAttribute('id', idNew + '-lbl');
                        }
                    }
                    forOldAttr = forOldAttr + countMulti;
                    idNew = idNew + countMulti;
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

                // Guess there a label for this input
                let lbl = row.querySelector('label[for="' + forOldAttr + '"]');
                if (lbl) {
                    lbl.setAttribute('for', idNew);
                    lbl.setAttribute('id', idNew + '-lbl');
                }
            }
        }

        /**
         * Use of HTML Drag and Drop API
         * https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API
         * https://www.sitepoint.com/accessible-drag-drop/
         */
        setUpDragSort() {
            let that = this; // Self reference
            let item = null; // Storing the selected item
            let touched = false; // We have a touch events

            // Find all existing rows and add dragable attributes
            let rows = this.getRows();
            for (let ir = 0, lr = rows.length; ir < lr; ir++) {
                let childRow = rows[ir];

                childRow.setAttribute('draggable', 'false');
                childRow.setAttribute('aria-grabbed', 'false');
                childRow.setAttribute('tabindex', '0');
            }

            // Helper method to test whether Handler was clicked
            function getMoveHandler(element) {
                return !element.form // This need to test whether the element is :input
                && element[matchesFn](that.buttonMove) ? element : closest(element, that.buttonMove);
            }

            // Helper method to mover row to selected position
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
                }
                else {
                    dest.parentNode.insertBefore(src, dest.nextSibling);
                }
            }

            // Touch interaction:
            // - a touch of "move button" mark a row dragable / "selected", or deselect previous selected
            // - a touch of "move button" in the destination row will move a selected row to a new position
            this.addEventListener('touchstart', function(event) {
                touched = true;

                // Check for .move button
                let handler = getMoveHandler(event.target),
                    row = handler ? closest(handler, that.repeatableElement) : null;

                if (!row || closest(row, 'joomla-field-subform') !== that) {
                    return;
                }

                // First selection
                if (!item) {
                    row.setAttribute('draggable', 'true');
                    row.setAttribute('aria-grabbed', 'true');
                    item = row;
                }
                // Second selection
                else {
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
            this.addEventListener('mousedown', function(event) {
                if (touched) return;

                // Check for .move button
                let handler = getMoveHandler(event.target),
                    row = handler ? closest(handler, that.repeatableElement) : null;

                if (!row || closest(row, 'joomla-field-subform') !== that) {
                    return;
                }

                row.setAttribute('draggable', 'true');
                row.setAttribute('aria-grabbed', 'true');
                item = row;
            });

            this.addEventListener('mouseup', function(event) {
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
            this.addEventListener('keydown', function(event) {
                if ((event.keyCode !== KEYCODE.ESC && event.keyCode !== KEYCODE.SPACE && event.keyCode !== KEYCODE.ENTER)
                    || event.target.form || !event.target[matchesFn](that.repeatableElement)) {
                    return;
                }

                let row = event.target;

                // Make sure we handle correct children
                if (!row || closest(row, 'joomla-field-subform') !== that) {
                    return;
                }

                // Space is the selection or unselection keystroke
                if (event.keyCode === KEYCODE.SPACE && hasModifier(event)) {
                    // Unselect previously selected
                    if (row.getAttribute('aria-grabbed') === 'true') {
                        row.setAttribute('draggable', 'false');
                        row.setAttribute('aria-grabbed', 'false');
                        item = null;
                    }
                    // Select new
                    else {
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
            this.addEventListener('dragstart', function(event) {
                if (item) {
                    // We going to move the row
                    event.dataTransfer.effectAllowed = 'move';

                    // This need to work in Firefox and IE10+
                    event.dataTransfer.setData('text', '');
                }
            });

            this.addEventListener('dragover', function(event) {
                if (item) {
                    event.preventDefault();
                }
            });

            // Handle drag action, move element to hovered position
            this.addEventListener('dragenter', function(event) {
                // Make sure the target in the correct container
                if (!item || that.rowsContainer && closest(event.target, that.rowsContainer) !== that.containerWithRows) {
                    return;
                }

                // Find a hovered row, and replace it
                let row = event.target[matchesFn](that.repeatableElement) ? event.target : closest(event.target, that.repeatableElement);
                if (!row) return;

                switchRowPositions(item, row);
            });

            // dragend event to clean-up after drop or abort
            // which fires whether or not the drop target was valid
            this.addEventListener('dragend', function() {
                if (item) {
                    item.setAttribute('draggable', 'false');
                    item.setAttribute('aria-grabbed', 'false');
                    item = null;
                }
            });
        }
    }

    customElements.define('joomla-field-subform', JoomlaFieldSubform);

    /**
     * Helper to find a closest parent element
     *
     * @param {HTMLElement} element
     * @param {String}      selector
     *
     * @returns {HTMLElement|null}
     */
    function closest(element, selector) {
        let parent;

        // Traverse parents
        while (element) {
            parent = element.parentElement;
            if (parent && parent[matchesFn](selector)) {
                return parent;
            }
            element = parent;
        }

        return null;
    }

    /**
     * Helper for testing whether a selection modifier is pressed
     * @param {Event} event
     *
     * @returns {boolean|*}
     */
    function hasModifier(event) {
        return (event.ctrlKey || event.metaKey || event.shiftKey);
    }

})(customElements);
