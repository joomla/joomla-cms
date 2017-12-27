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
            console.dir(this);
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
                throw new Error('The row template are required to subform element work')
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

            // @TODO Tell about the new row
            //this.$container.trigger('subform-row-add', $row);
            //Joomla.Event.dispatch($row.get(0), 'joomla:updated');
            return row;
        }

        /**
         * Remove the row
         * @param {HTMLElement} row
         */
        removeRow (row) {
            // Count how much we have
            const count = this.getRows().length;
            if(count <= this.minimum){
                return;
            }

            // @TODO: tell everyoune about the row will be removed
            //this.$container.trigger('subform-row-remove', $row);
            //Joomla.Event.dispatch($row.get(0), 'joomla:removed');

            row.parentNode.removeChild(row);
        }

        /**
         * Fix names ind id`s for field that in the row
         * @param {HTMLElement} row
         * @param {Number} count
         */
        fixUniqueAttributes(row, count) {
            console.log(row, count);
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
