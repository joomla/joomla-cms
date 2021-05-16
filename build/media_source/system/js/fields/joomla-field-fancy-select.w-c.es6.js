/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Fancy select field, which use Choices.js
 *
 * Example:
 * <joomla-field-fancy-select ...attributes>
 *   <select>...</select>
 * </joomla-field-fancy-select>
 *
 * Possible attributes:
 *
 * allow-custom          Whether allow User to dynamically add a new value.
 * new-item-prefix=""    Prefix for a dynamically added value.
 *
 * remote-search         Enable remote search.
 * url=""                Url for remote search.
 * term-key="term"       Variable key name for searched term, will be appended to Url.
 *
 * min-term-length="1"   The minimum length a search value should be before choices are searched.
 * placeholder=""        The value of the inputs placeholder.
 * search-placeholder="" The value of the search inputs placeholder.
 *
 * data-max-results="30" The maximum amount of search results to be displayed.
 * data-max-render="30"  The maximum amount of items to be rendered, critical for large lists.
 */
window.customElements.define('joomla-field-fancy-select', class extends HTMLElement {
  // Attributes to monitor
  get allowCustom() { return this.hasAttribute('allow-custom'); }

  get remoteSearch() { return this.hasAttribute('remote-search'); }

  get url() { return this.getAttribute('url'); }

  get termKey() { return this.getAttribute('term-key') || 'term'; }

  get minTermLength() { return parseInt(this.getAttribute('min-term-length'), 10) || 1; }

  get newItemPrefix() { return this.getAttribute('new-item-prefix') || ''; }

  get placeholder() { return this.getAttribute('placeholder'); }

  get searchPlaceholder() { return this.getAttribute('search-placeholder'); }

  get value() { return this.choicesInstance.getValue(true); }

  set value($val) { this.choicesInstance.setChoiceByValue($val); }

  /**
   * Lifecycle
   */
  constructor() {
    super();

    // Keycodes
    this.keyCode = {
      ENTER: 13,
    };

    if (!Joomla) {
      throw new Error('Joomla API is not properly initiated');
    }

    if (!window.Choices) {
      throw new Error('JoomlaFieldFancySelect requires Choices.js to work');
    }

    this.choicesCache = {};
    this.activeXHR = null;
    this.choicesInstance = null;
    this.isDisconnected = false;
  }

  /**
   * Lifecycle
   */
  connectedCallback() {
    // Make sure Choices are loaded
    if (window.Choices || document.readyState === 'complete') {
      this.doConnect();
    } else {
      const callback = () => {
        this.doConnect();
        window.removeEventListener('load', callback);
      };
      window.addEventListener('load', callback);
    }
  }

  doConnect() {
    // Get a <select> element
    this.select = this.querySelector('select');

    if (!this.select) {
      throw new Error('JoomlaFieldFancySelect requires <select> element to work');
    }

    // The element was already initialised previously and perhaps was detached from DOM
    if (this.choicesInstance) {
      if (this.isDisconnected) {
        // Re init previous instance
        this.choicesInstance.init();
        this.isDisconnected = false;
      }
      return;
    }

    this.isDisconnected = false;

    // Add placeholder option for multiple mode,
    // Because it not supported as parameter by Choices for <select> https://github.com/jshjohnson/Choices#placeholder
    if (this.select.multiple && this.placeholder) {
      const option = document.createElement('option');
      option.setAttribute('placeholder', '');
      option.textContent = this.placeholder;
      this.select.appendChild(option);
    }

    // Init Choices
    // eslint-disable-next-line no-undef
    this.choicesInstance = new Choices(this.select, {
      placeholderValue: this.placeholder,
      searchPlaceholderValue: this.searchPlaceholder,
      removeItemButton: true,
      searchFloor: this.minTermLength,
      searchResultLimit: parseInt(this.select.dataset.maxResults, 10) || 10,
      renderChoiceLimit: parseInt(this.select.dataset.maxRender, 10) || -1,
      shouldSort: false,
      fuseOptions: {
        threshold: 0.3, // Strict search
      },
      noResultsText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
      noChoicesText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
      itemSelectText: Joomla.Text._('JGLOBAL_SELECT_PRESS_TO_SELECT', 'Press to select'),

      // Redefine some classes
      classNames: {
        button: 'choices__button_joomla', // It is need because an original styling use unavailable Icon.svg file
      },
    });

    // Handle typing of custom Term
    if (this.allowCustom) {
      // START Work around for issue https://github.com/joomla/joomla-cms/issues/29459
      // The choices.js always auto-highlights the first element
      // in the dropdown that not allow to add a custom Term.
      //
      // This workaround can be removed when choices.js
      // will have an option that allow to disable it.

      // eslint-disable-next-line no-underscore-dangle, prefer-destructuring
      const _highlightChoice = this.choicesInstance._highlightChoice;
      // eslint-disable-next-line no-underscore-dangle
      this.choicesInstance._highlightChoice = (el) => {
        // Prevent auto-highlight of first element, if nothing actually highlighted
        if (!el) return;

        // Call original highlighter
        _highlightChoice.call(this.choicesInstance, el);
      };

      // Unhighlight any highlighted items, when mouse leave the dropdown
      this.addEventListener('mouseleave', () => {
        if (!this.choicesInstance.dropdown.isActive) {
          return;
        }

        const highlighted = Array.from(this.choicesInstance.dropdown.element
          .querySelectorAll(`.${this.choicesInstance.config.classNames.highlightedState}`));

        highlighted.forEach((choice) => {
          choice.classList.remove(this.choicesInstance.config.classNames.highlightedState);
          choice.setAttribute('aria-selected', 'false');
        });

        // eslint-disable-next-line no-underscore-dangle
        this.choicesInstance._highlightPosition = 0;
      });
      // END workaround for issue #29459

      // Add custom term on ENTER keydown
      this.addEventListener('keydown', (event) => {
        if (event.keyCode !== this.keyCode.ENTER
          || event.target !== this.choicesInstance.input.element) {
          return;
        }
        event.preventDefault();

        // eslint-disable-next-line no-underscore-dangle
        if (this.choicesInstance._highlightPosition || !event.target.value) {
          return;
        }

        // Make sure nothing is highlighted
        const highlighted = this.choicesInstance.dropdown.element
          .querySelector(`.${this.choicesInstance.config.classNames.highlightedState}`);

        if (highlighted) {
          return;
        }

        // Check if value already exist
        const lowerValue = event.target.value.toLowerCase();
        let valueInCache = false;

        // Check if value in existing choices
        this.choicesInstance.config.choices.some((choiceItem) => {
          if (choiceItem.value.toLowerCase() === lowerValue
            || choiceItem.label.toLowerCase() === lowerValue) {
            valueInCache = choiceItem.value;
            return true;
          }
          return false;
        });

        if (valueInCache === false) {
          // Check if value in cache
          Object.keys(this.choicesCache).some((key) => {
            if (key.toLowerCase() === lowerValue
              || this.choicesCache[key].toLowerCase() === lowerValue) {
              valueInCache = key;
              return true;
            }
            return false;
          });
        }

        // Make choice based on existing value
        if (valueInCache !== false) {
          this.choicesInstance.setChoiceByValue(valueInCache);
          event.target.value = null;
          this.choicesInstance.hideDropdown();
          return;
        }

        // Create and add new
        this.choicesInstance.setChoices([{
          value: this.newItemPrefix + event.target.value,
          label: event.target.value,
          selected: true,
          customProperties: {
            value: event.target.value, // Store real value, just in case
          },
        }], 'value', 'label', false);

        this.choicesCache[event.target.value] = event.target.value;

        event.target.value = null;
        this.choicesInstance.hideDropdown();
      });
    }

    // Handle remote search
    if (this.remoteSearch && this.url) {
      // Cache existing
      this.choicesInstance.config.choices.forEach((choiceItem) => {
        this.choicesCache[choiceItem.value] = choiceItem.label;
      });

      const lookupDelay = 300;
      let lookupTimeout = null;
      this.select.addEventListener('search', () => {
        clearTimeout(lookupTimeout);
        lookupTimeout = setTimeout(this.requestLookup.bind(this), lookupDelay);
      });
    }
  }

  /**
   * Lifecycle
   */
  disconnectedCallback() {
    // Destroy Choices instance, to unbind event listeners
    if (this.choicesInstance) {
      this.choicesInstance.destroy();
      this.isDisconnected = true;
    }

    if (this.activeXHR) {
      this.activeXHR.abort();
      this.activeXHR = null;
    }
  }

  requestLookup() {
    let { url } = this;
    url += (url.indexOf('?') === -1 ? '?' : '&');
    url += `${encodeURIComponent(this.termKey)}=${encodeURIComponent(this.choicesInstance.input.value)}`;

    // Stop previous request if any
    if (this.activeXHR) {
      this.activeXHR.abort();
    }

    this.activeXHR = Joomla.request({
      url,
      onSuccess: (response) => {
        this.activeXHR = null;
        const items = response ? JSON.parse(response) : [];
        if (!items.length) {
          return;
        }

        // Remove duplications
        let item;
        // eslint-disable-next-line no-plusplus
        for (let i = items.length - 1; i >= 0; i--) { // The loop must be form the end !!!
          item = items[i];
          // eslint-disable-next-line prefer-template
          item.value = '' + item.value; // Make sure the value is a string, choices.js expect a string.

          if (this.choicesCache[item.value]) {
            items.splice(i, 1);
          } else {
            this.choicesCache[item.value] = item.text;
          }
        }

        // Add new options to field, assume that each item is object, eg {value: "foo", text: "bar"}
        if (items.length) {
          this.choicesInstance.setChoices(items, 'value', 'text', false);
        }
      },
      onError: () => {
        this.activeXHR = null;
      },
    });
  }

  disableAllOptions() {
    // Choices.js does not offer a public API for accessing the choices
    // So we have to access the private store => don't eslint
    // eslint-disable-next-line no-underscore-dangle
    const { choices } = this.choicesInstance._store;

    choices.forEach((elem, index) => {
      choices[index].disabled = true;
      choices[index].selected = false;
    });

    this.choicesInstance.clearStore();

    this.choicesInstance.setChoices(choices, 'value', 'label', true);
  }

  enableAllOptions() {
    // Choices.js does not offer a public API for accessing the choices
    // So we have to access the private store => don't eslint
    // eslint-disable-next-line no-underscore-dangle
    const { choices } = this.choicesInstance._store;
    const values = this.choicesInstance.getValue(true);

    choices.forEach((elem, index) => {
      choices[index].disabled = false;
    });

    this.choicesInstance.clearStore();

    this.choicesInstance.setChoices(choices, 'value', 'label', true);

    this.value = values;
  }

  disableByValue($val) {
    // Choices.js does not offer a public API for accessing the choices
    // So we have to access the private store => don't eslint
    // eslint-disable-next-line no-underscore-dangle
    const { choices } = this.choicesInstance._store;
    const values = this.choicesInstance.getValue(true);

    choices.forEach((elem, index) => {
      if (elem.value === $val) {
        choices[index].disabled = true;
        choices[index].selected = false;
      }
    });

    const index = values.indexOf($val);

    if (index > -1) {
      values.slice(index, 1);
    }

    this.choicesInstance.clearStore();

    this.choicesInstance.setChoices(choices, 'value', 'label', true);

    this.value = values;
  }

  enableByValue($val) {
    // Choices.js does not offer a public API for accessing the choices
    // So we have to access the private store => don't eslint
    // eslint-disable-next-line no-underscore-dangle
    const { choices } = this.choicesInstance._store;
    const values = this.choicesInstance.getValue(true);

    choices.forEach((elem, index) => {
      if (elem.value === $val) {
        choices[index].disabled = false;
      }
    });

    this.choicesInstance.clearStore();

    this.choicesInstance.setChoices(choices, 'value', 'label', true);

    this.value = values;
  }
});
