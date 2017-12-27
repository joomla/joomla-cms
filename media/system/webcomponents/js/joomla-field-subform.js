/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(customElements){
    "use strict";

    class JoomlaFieldSubform extends HTMLElement {

        // Attribute getters
        get buttonAdd()         { return this.getAttribute('button-add'); }
        get buttonRemove()      { return this.getAttribute('button-remove'); }
        get buttonMove()        { return this.getAttribute('button-move'); }
        get rowsContainer()     { return this.getAttribute('rows-container'); }
        get repeatableElement() { return this.getAttribute('repeatable-element'); }
        get minimum()           { return this.getAttribute('minimum'); }
        get maximum()           { return this.getAttribute('maximum'); }

        connectedCallback () {
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
            this.addEventListener('click', function(event) {
                let btnAdd = closest(event.target, that.buttonAdd);
                let btnRem = closest(event.target, that.buttonRemove);

                // Check actine, with extra check for nested joomla-field-subform
                if (btnAdd && closest(btnAdd, 'joomla-field-subform') === that) {
                    let row = closest(btnAdd, that.repeatableElement);
                    console.log(row);
                    this.addRow(row);
                } else if (btnRem && closest(btnRem, 'joomla-field-subform') === that) {
                    let row = closest(btnRem, that.repeatableElement);
                    this.removeRow(row);
                }
            });
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
            let tmpEl = document.createElement('div');
            tmpEl.innerHTML = this.template;
            let row = tmpEl.children[0];

            // Add to container
            if (after) {
                after.parentNode.insertBefore(row, after.nextSibling);
            } else {
                this.containerWithRows.append(row);
            }

            //add marker that it is new
            row.setAttribute('data-new', '1');
            // fix names and id`s, and reset values
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
                $el.setAttribute('name', nameNew);
                $el.setAttribute('id', idNew);

                // Guess there a label for this input
                let lbl = row.querySelector('label[for="' + forOldAttr + '"]');
                if (lbl) {
                    lbl.setAttribute('for', idNew);
                    lbl.setAttribute('id', idNew + '-lbl');
                }
            }
        }
    }

    customElements.define('joomla-field-subform', JoomlaFieldSubform);

    function closest(element, selector) {
        let matchesFn;

        // find vendor prefix
        ['matches', 'msMatchesSelector'].some(function(fn) {
            if (typeof document.body[fn] === 'function') {
                matchesFn = fn;
                return true;
            }
            return false;
        });

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

})(customElements);
