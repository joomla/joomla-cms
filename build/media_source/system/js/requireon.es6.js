/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JField 'requireon' class
 */
class Requireon {
  /**
   * Constructor
   *
   * @param {HTMLElement} cont Container element
   */
  constructor(cont) {
    const self = this;
    this.container = cont || document;
    this.fields = {// origin-field-name: {
      //   origin:  ['collection of all the trigger nodes'],
      //   targets: ['collection of nodes to be controlled control']
      // }
    };
    this.requireonFields = [].slice.call(this.container.querySelectorAll('[data-requireon]')); // Populate the fields data

    if (this.requireonFields.length) {
      // @todo refactor this, dry
      this.requireonFields.forEach((field) => {
        // Set up only once
        if (field.hasAttribute('data-requireon-initialised')) {
          return;
        }

        field.setAttribute('data-requireon-initialised', '');
        const jsondata = field.getAttribute('data-requireon') || '';
        const requireonData = JSON.parse(jsondata);

        let localFields;

        if (requireonData.length) {
          localFields = [].slice.call(self.container.querySelectorAll(`[name="${requireonData[0].field}"], [name="${requireonData[0].field}[]"]`));

          if (!this.fields[requireonData[0].field]) {
            this.fields[requireonData[0].field] = {
              origin: [],
              targets: [],
            };
          } // Add trigger elements

          localFields.forEach((cField) => {
            if (this.fields[requireonData[0].field].origin.indexOf(cField) === -1) {
              this.fields[requireonData[0].field].origin.push(cField);
            }
          }); // Add target elements

          this.fields[requireonData[0].field].targets.push(field); // Data requireon can have multiple values

          if (requireonData.length > 1) {
            requireonData.forEach((value, index) => {
              if (index === 0) {
                return;
              }

              localFields = [].slice.call(self.container.querySelectorAll(`[name="${value.field}"], [name="${value.field}[]"]`));

              if (!this.fields[requireonData[0].field]) {
                this.fields[requireonData[0].field] = {
                  origin: [],
                  targets: [],
                };
              } // Add trigger elements

              localFields.forEach((cField) => {
                if (this.fields[requireonData[0].field].origin.indexOf(cField) === -1) {
                  this.fields[requireonData[0].field].origin.push(cField);
                }
              }); // Add target elements

              if (this.fields[requireonData[0].field].targets.indexOf(field) === -1) {
                this.fields[requireonData[0].field].targets.push(field);
              }
            });
          }
        }
      }); // Do some binding

      this.linkedOptions = this.linkedOptions.bind(this); // Attach events to referenced element, to check condition on change and keyup

      Object.keys(this.fields).forEach((key) => {
        if (this.fields[key].origin.length) {
          this.fields[key].origin.forEach((elem) => {
            // Initialize the requireon behaviour for the given HTMLElement
            self.linkedOptions(key); // Setup listeners

            elem.addEventListener('change', () => {
              self.linkedOptions(key);
            });
            elem.addEventListener('keyup', () => {
              self.linkedOptions(key);
            });
            elem.addEventListener('click', () => {
              self.linkedOptions(key);
            });
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
    // Loop through the elements that need to be either mandatory or optional
    this.fields[key].targets.forEach((field) => {
      const elementRequireonDatas = JSON.parse(field.getAttribute('data-requireon')) || [];
      let requirefield = true;
      let itemval; // Check if target conditions are satisfied

      elementRequireonDatas.forEach((elementRequireonData, index) => {
        const condition = elementRequireonData || {};
        condition.valid = 0; // Test in each of the elements in the field array if condition is valid

        this.fields[key].origin.forEach((originField) => {
          if (originField.name.replace('[]', '') !== elementRequireonData.field) {
            return;
          }

          const originId = originField.id; // If checkbox or radio box the value is read from properties

          if (originField.getAttribute('type') && ['checkbox', 'radio'].includes(originField.getAttribute('type').toLowerCase())) {
            if (!originField.checked) {
              // Unchecked fields will return a blank and so always match
              // a != condition so we skip them
              return;
            }

            itemval = document.getElementById(originId).value;
          } else if (originField.nodeName === 'SELECT' && originField.hasAttribute('multiple')) {
            itemval = Array.from(originField.querySelectorAll('option:checked')).map((el) => el.value);
          } else {
            // Select lists, text-area etc. Note that multiple-select list returns
            // an Array here s0 we can always treat 'itemval' as an array
            itemval = document.getElementById(originId).value; // A multi-select <select> $field  will return null when no elements are
            // selected so we need to define itemval accordingly

            if (itemval === null && originField.tagName.toLowerCase() === 'select') {
              itemval = [];
            }
          } // Convert to array to allow multiple values in the field (e.g. type=list multiple)
          // and normalize as string

          if (!(typeof itemval === 'object')) {
            itemval = JSON.parse(`["${itemval}"]`);
          } // Test if any of the values of the field exists in requireon conditions

          itemval.forEach((val) => {
            // ":" Equal to one or more of the values condition
            if (condition.sign === '=' && condition.values.indexOf(val) !== -1) {
              condition.valid = 1;
            } // "!:" Not equal to one or more of the values condition

            if (condition.sign === '!=' && condition.values.indexOf(val) === -1) {
              condition.valid = 1;
            }
          });
        }); // Verify conditions
        // First condition (no operator): current condition must be valid

        if (condition.op === '') {
          if (condition.valid === 0) {
            requirefield = false;
          }
        } else {
          // Other conditions (if exists)
          // AND operator: both the previous and current conditions must be valid
          if (condition.op === 'AND' && condition.valid + elementRequireonDatas[index - 1].valid < 2) {
            requirefield = false;
            condition.valid = 0;
          } // OR operator: one of the previous and current conditions must be valid

          if (condition.op === 'OR' && condition.valid + elementRequireonDatas[index - 1].valid > 0) {
            requirefield = true;
            condition.valid = 1;
          }
        }
      }); // If conditions are satisfied require the target field(s), else un-require

      if (field.tagName !== 'option') {
        // Get the indicator of requiredness...
        const star = field.getElementsByClassName('star');

        if (requirefield) {
          if (star.length) {
            star[0].classList.remove('hidden');
          }

          if (field.getElementsByTagName('select').length > 0) {
            field.getElementsByTagName('select')[0].setAttribute('required', '');
          } else if (field.getElementsByTagName('textarea').length > 0) {
            field.getElementsByTagName('textarea')[0].setAttribute('required', '');
          } else if (field.getElementsByTagName('input').length > 0) {
            field.getElementsByTagName('input')[0].setAttribute('required', '');
          }

          field.dispatchEvent(new CustomEvent('joomla:requireon-mandatory', {
            bubbles: true,
          }));
        } else {
          if (star.length) {
            star[0].classList.add('hidden');
          }

          if (field.getElementsByTagName('select').length > 0) {
            field.getElementsByTagName('select')[0].removeAttribute('required');
          } else if (field.getElementsByTagName('textarea').length > 0) {
            field.getElementsByTagName('textarea')[0].removeAttribute('required');
          } else if (field.getElementsByTagName('input').length > 0) {
            field.getElementsByTagName('input')[0].removeAttribute('required');
          }

          field.dispatchEvent(new CustomEvent('joomla:requireon-optional', {
            bubbles: true,
          }));
        }
      } else {
        // @todo: If chosen or choices.js is active we should update them
        field.disabled = !requirefield;
      }
    });
  }
}

if (!window.Joomla) {
  throw new Error('Joomla API is not properly initialized');
} // Provide a public API

if (!Joomla.Requireon) {
  Joomla.Requireon = {
    initialise: (container) => new Requireon(container),
  };
}

/**
 * Initialize 'requireon' feature at an initial page load
 */
Joomla.Requireon.initialise(document);
/**
 * Search for matching parents
 *
 * @param {HTMLElement} $child
 * @param {String} selector
 * @returns {HTMLElement[]}
 */

const getMatchedParents = ($child, selector) => {
  let $parent = $child;
  let $matchingParent;
  const parents = [];

  while ($parent) {
    $matchingParent = $parent.matches && $parent.matches(selector) ? $parent : null;

    if ($matchingParent) {
      parents.unshift($matchingParent);
    }

    $parent = $parent.parentNode;
  }

  return parents;
};

/**
 * Initialize 'requireon' feature when part of the page was updated
 */
document.addEventListener('joomla:updated', ({
  target,
}) => {
  // Check is it subform, then wee need to fix some "requireon" config
  if (target.classList.contains('subform-repeatable-group')) {
    const elements = [].slice.call(target.querySelectorAll('[data-requireon]'));

    if (elements.length) {
      const search = [];
      const replace = []; // Collect all parent groups of changed group

      getMatchedParents(target, '.subform-repeatable-group').forEach(($parent) => {
        search.push(new RegExp(`\\[${$parent.dataset.baseName}X\\]`, 'g'));
        replace.push(`[${$parent.dataset.group}]`);
      }); // Fix requireon field names in a current group

      elements.forEach((element) => {
        let {
          requireon,
        } = element.dataset;
        search.forEach((pattern, i) => {
          requireon = requireon.replace(pattern, replace[i]);
        });
        element.dataset.requireon = requireon;
      });
    }
  }

  Joomla.Requireon.initialise(target);
});
