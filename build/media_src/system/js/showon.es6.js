/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// @todo remove this totally irrelevant piece of code
// Only define the Joomla namespace if not defined.
Joomla = window.Joomla || {};

/**
 * Sets the HTML of the container-collapse element
 */
Joomla.setcollapse = (url, name, height) => {
  if (!document.getElementById(`collapse-${name}`)) {
    document.getElementById('container-collapse').innerHTML = `<div class="collapse fade" id="collapse-${name}"><iframe class="iframe" src="${url}" height="${height}" width="100%"></iframe></div>`;
  }
};
// end of @todo

((document) => {
  'use strict';

  /*
   * JField 'showon' class
   */
  class Showon {
    /*
     * Constructor
     *
     * @param {HTMLElement} cont Container element
     */
    constructor(cont) {
      const self = this;
      this.container = cont || document;
      this.fields = {
        // demo-name: {
        //   origin:  ['collectionof origin nodes'],
        //   targets: ['collection of nodes to be controlled control']
        // }
      };

      this.showonFields = [].slice.call(this.container.querySelectorAll('[data-showon]'));

      // Populate the fields data
      if (this.showonFields.length) {
        this.showonFields.forEach((field) => {
          const jsondata = field.getAttribute('data-showon') || '';

          JSON.parse(jsondata).forEach((value) => {
            const localFields = [].slice.call(self.container.querySelectorAll(`[name="${value.field}"], [name="${value.field}[]"]`));

            if (!this.fields[value.field]) {
              this.fields[value.field] = {
                origin: localFields,
                targets: [],
              };
            }

            this.fields[value.field].targets.push(field);
          });
        });

        // Do some binding
        this.linkedOptions = this.linkedOptions.bind(this);

        // Attach events to referenced element, to check condition on change
        Object.keys(this.fields).forEach((key) => {
          if (this.fields[key].origin.length) {
            this.fields[key].origin.forEach((elem) => {
              const name = elem.getAttribute('name');
              // Initialise
              self.linkedOptions(name);

              // Setup listeners
              elem.addEventListener('change', () => { self.linkedOptions(name); });
            });
          }
        });
      }
    }

    /**
     *
     * @param key
     */
    linkedOptions(key) {
      this.fields[key].targets.forEach((field) => {
        let showfield = true;
        const elementShowonDatas = JSON.parse(field.getAttribute('data-showon')) || [];
        let itemval;
        let condition;

        // Check if target conditions are satisfied
        elementShowonDatas.forEach((elementShowonData, index) => {
          condition = elementShowonData || {};
          condition.valid = 0;

          // Test in each of the elements in the field array if condition is valid
          this.fields[key].origin.forEach((originField) => {
            const originId = originField.id;

            // If checkbox or radio box the value is read from properties
            if (originField.getAttribute('type') && ['checkbox', 'radio'].indexOf(originField.getAttribute('type').toLowerCase()) !== -1) {
              if (!originField.checked) {
                // Unchecked fields will return a blank and so always match
                // a != condition so we skip them
                return;
              }

              itemval = document.getElementById(originId).value;
            } else {
              // Select lists, text-area etc. Note that multiple-select list returns
              // an Array here s0 we can always treat 'itemval' as an array
              itemval = document.getElementById(originId).value;
              // A multi-select <select> $field  will return null when no elements are
              // selected so we need to define itemval accordingly
              if (itemval === null && originField.tagName.toLowerCase() === 'select') {
                itemval = [];
              }
            }

            // Convert to array to allow multiple values in the field (e.g. type=list multiple)
            // and normalize as string
            if (!(typeof itemval === 'object')) {
              itemval = JSON.parse(`["${itemval}"]`);
            }

            // Test if any of the values of the field exists in showon conditions
            itemval.forEach((val) => {
              // ":" Equal to one or more of the values condition
              if (elementShowonData.sign === '=' && elementShowonData.values.indexOf(val) !== -1) {
                elementShowonData.valid = 1;
              }
              // "!:" Not equal to one or more of the values condition
              if (elementShowonData.sign === '!=' && elementShowonData.values.indexOf(val) === -1) {
                elementShowonData.valid = 1;
              }
            });
          });

          // Verify conditions
          // First condition (no operator): current condition must be valid
          if (condition.op === '') {
            if (condition.valid === 0) {
              showfield = false;
            }
          } else {
            // Other conditions (if exists)
            // AND operator: both the previous and current conditions must be valid
            if (condition.op === 'AND' && condition.valid + elementShowonDatas[index - 1].valid < 2) {
              showfield = false;
            }
            // OR operator: one of the previous and current conditions must be valid
            if (condition.op === 'OR' && condition.valid + elementShowonDatas[index - 1].valid > 0) {
              showfield = true;
            }
          }
        });

        // If conditions are satisfied show the target field(s), else hide
        field.style.display = (showfield) ? 'block' : 'none';
      });
    }
  }

  /**
   * Initialize 'showon' feature at an initial page load
   */
  document.addEventListener('DOMContentLoaded', () => {
    // eslint-disable-next-line no-new
    new Showon(document);
  });

  /**
   * Initialize 'showon' feature when part of the page was updated
   */
  document.addEventListener('joomla:updated', (event) => {
    const target = event.target;

    // Check is it subform, then wee need to fix some "showon" config
    if (target.classList.contains('subform-repeatable-group')) {
      const elements = [].slice.call(target.querySelectorAll('[data-showon]'));
      const baseName = target.getAttribute('data-baseName');
      const group = target.getAttribute('data-group');
      const search = new RegExp(`\\[${baseName}\\]\\[${baseName}X\\]`, 'g');
      const replace = `[${baseName}][${group}]`;

      // Fix showon field names in a current group
      elements.forEach((element) => {
        const showon = element.getAttribute('data-showon').replace(search, replace);

        element.setAttribute('data-showon', showon);
      });
    }

    // eslint-disable-next-line no-new
    new Showon(event.target);
  });
})(document);
