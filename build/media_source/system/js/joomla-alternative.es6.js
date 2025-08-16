/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Toolbar 'alternative' class
 */
class Alternative {
  /**
   * Constructor
   *
   * @param {HTMLElement} cont Container element
   */
  constructor(cont) {
    this.fields = {};
    this.mapping = {
      AltLeft: 'AltLeft',
      AltRight: 'AltRight',
      ControlLeft: 'ControlLeft',
      ControlRight: 'ControlRight',
      CapsLock: 'CapsLock',
      ShiftLeft: 'ShiftLeft',
      ShiftRight: 'ShiftRight',
      MetaLeft: 'MetaLeft',
      MetaRight: 'MetaRight',

      Alt: 'AltLeft,AltRight,AltGraph',
      AltGraph: 'AllRight,Alt',
      Control: 'ControlLeft,ControlRight',
      Shift: 'CapsLock,ShiftLeft,ShiftRight',
      Meta: 'MetaLeft,MetaRight',

      Default: 'Default',
    };

    this.update = this.update.bind(this);
    this.change = this.change.bind(this);

    this.update(cont);

    document.addEventListener('keydown', this.change);
    document.addEventListener('keyup', this.change);
  }

  /**
   * Update containers
   * @param container
   */
  update(container) {
    const groups = [].slice.call(container.querySelectorAll('[data-alternative-group]'));

    groups.forEach((element) => {
      // eslint-disable-next-line no-prototype-builtins
      if (!this.fields.hasOwnProperty(element.dataset.alternativeGroup)) {
        this.fields[element.dataset.alternativeGroup] = {
          modifiers: '',
          elements: [],
          alternatives: {},
        };
      }
      const group = this.fields[element.dataset.alternativeGroup];

      if (group.elements.indexOf(element) !== -1) {
        return;
      }

      group.elements.push(element);

      const modifiers = element.dataset.alternativeKeys.split(',');
      modifiers.forEach((modifier) => {
        // eslint-disable-next-line no-prototype-builtins
        if (!this.mapping.hasOwnProperty(modifier)) {
          // Unknown modifier
          return;
        }

        this.mapping[modifier].split(',').forEach((code) => {
          // eslint-disable-next-line no-prototype-builtins
          if (!group.alternatives.hasOwnProperty(code)) {
            group.alternatives[code] = [];
          }
          group.alternatives[code].push(element);

          if (modifier !== 'Default' && group.modifiers.indexOf(modifier) === -1) {
            group.modifiers += `,${this.mapping[modifier]}`;
          }
        });
      });

      if (modifiers.indexOf('Default') === -1) {
        element.style.display = 'none';
      }
    });
  }

  /**
   * @param event
   */
  change(event) {
    // return early if it's not a supported modifier key
    // eslint-disable-next-line no-prototype-builtins
    if (!this.mapping.hasOwnProperty(event.code)) {
      return;
    }

    const isDefault = !event.shiftKey && !event.metaKey && !event.ctrlKey && !event.altKey && !event.getModifierState('CapsLock');

    Object.values(this.fields).forEach((element) => {
      // Is the pressed key relevent for the alternative group
      if (element.modifiers.indexOf(event.code) === -1) {
        return;
      }

      let activeElement = null;

      element.alternatives[event.code].forEach((ele) => {
        if (ele.contains(document.activeElement)) {
          activeElement = ele;
        }
        ele.style.display = 'none';
      });
      element.alternatives.Default.forEach((ele) => {
        if (ele.contains(document.activeElement)) {
          activeElement = ele;
        }
        ele.style.display = 'none';
      });

      if (isDefault) {
        element.alternatives.Default.forEach((ele) => {
          ele.style.display = 'block';
          if (activeElement === ele) {
            activeElement = null;
          }
        });
      } else {
        element.alternatives[event.code].forEach((ele) => {
          ele.style.display = 'block';
          if (activeElement === ele) {
            activeElement = null;
          }
        });
      }

      if (activeElement !== null) {
        element.elements.forEach((ele) => {
          if (ele.style.display === 'block') {
            // Simple implementation of finding the first focus able element, could be extended if needed
            ele.querySelector('a,input,select,textarea,button').focus();
          }
        });
      }
    });
  }
}

if (!window.Joomla) {
  throw new Error('Joomla API is not properly initialized');
}
if (!Joomla.Toolbar) {
  Joomla.Toolbar = {};
}
if (!Joomla.Toolbar.Alternative) {
  Joomla.Toolbar.Alternative = {
    initialise: (container) => new Alternative(container),
    update: (container) => Joomla.Toolbar.Alternative.update(container),
  };
}

/**
 * Initialize 'Alternative' feature at an initial page load
 */
Joomla.Toolbar.Alternative.initialise(document);
