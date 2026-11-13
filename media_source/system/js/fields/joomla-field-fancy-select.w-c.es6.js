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
 * remove-item-text=""     Accessible name of the remove buttons, %s is replaced by the item label.
 * selected-items-text=""  Description of the selected items for assistive technology, %s is replaced
 *                         by the comma separated list of selected labels (multiple select only).
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

  get removeItemText() {
    return this.getAttribute('remove-item-text') || Joomla.Text._('JGLOBAL_SELECT_REMOVE_ITEM', 'Remove item: %s');
  }

  get selectedItemsText() {
    return this.getAttribute('selected-items-text') || Joomla.Text._('JGLOBAL_SELECT_SELECTED_ITEMS', 'Selected: %s');
  }

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
    this.selectionObserver = null;

    this.onRemoveButtonKeydown = this.onRemoveButtonKeydown.bind(this);
    this.onRemoveButtonMousedown = this.onRemoveButtonMousedown.bind(this);
    this.onRemoveButtonClick = this.onRemoveButtonClick.bind(this);
    this.syncChoicesSelectedState = this.syncChoicesSelectedState.bind(this);
    this.onDropdownToggle = this.onDropdownToggle.bind(this);
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
        this.choicesInstance.setChoiceByValue(this.disconnectValues);
        this.isDisconnected = false;
        this.enhanceAccessibility();
      }
      return;
    }

    this.isDisconnected = false;

    // START Work around for issue https://github.com/joomla/joomla-cms/issues/47507
    // For single-select fields, if selectedIndex is 0 and no option has a
    // selected attribute, set it before choices.js init.
    //
    // This workaround can be removed when choices.js
    // handles this state on init.
    if (!this.select.multiple
      && this.select.options.length
      && this.select.selectedIndex === 0
      && !this.select.querySelector('option[selected]')) {
      this.select.options[0].selected = true;
      this.select.options[0].setAttribute('selected', '');
    }
    // END workaround for issue #47507

    // Init Choices
    this.choicesInstance = new Choices(this.select, {
      placeholderValue: this.placeholder,
      searchPlaceholderValue: this.searchPlaceholder,
      appendGroupInSearch: true,
      removeItemButton: true,
      searchFloor: this.minTermLength,
      searchResultLimit: parseInt(this.select.dataset.maxResults, 10) || 10,
      renderChoiceLimit: parseInt(this.select.dataset.maxRender, 10) || -1,
      renderSelectedChoices: 'always',
      shouldSort: false,
      fuseOptions: {
        threshold: 0.3, // Strict search
        distance: 850, // Set distance so Fuse can match across full label length (~255 chars)
      },
      noResultsText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
      noChoicesText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
      itemSelectText: Joomla.Text._('JGLOBAL_SELECT_PRESS_TO_SELECT', 'Press to select'),

      // The aria-label is set with setAttribute(), so the raw label is used. The icon text is HTML.
      removeItemLabelText: (value, valueRaw, item) => this.getRemoveItemLabel(item, valueRaw),
      removeItemIconText: (value, valueRaw, item) => this.constructor.escapeHtml(this.getRemoveItemLabel(item, valueRaw)),

      // Redefine some classes
      classNames: {
        button: 'choices__button_joomla', // It is need because an original styling use unavailable Icon.svg file
      },
      labelId: this.getLabelId(),

      // The selected items of a multiple select contain buttons, so they can not be options of a listbox
      callbackOnCreateTemplates() {
        const choices = this;
        const { templates } = window.Choices.defaults;

        return {
          itemList(...args) {
            const element = templates.itemList.apply(choices, args);

            if (choices._isSelectMultipleElement) {
              element.setAttribute('role', 'list');
            }

            return element;
          },
          item(...args) {
            const element = templates.item.apply(choices, args);

            // A placeholder is not a value, so there is nothing to remove
            if (element.hasAttribute('data-placeholder')) {
              element.querySelector('[data-button]')?.remove();
              element.removeAttribute('data-deletable');
            }

            if (choices._isSelectMultipleElement) {
              element.setAttribute('role', 'listitem');
              element.removeAttribute('aria-selected');
            }

            return element;
          },
        };
      },
    });

    this.enhanceAccessibility();

    // Handle typing of custom Term
    if (this.allowCustom) {
      // START Work around for issue https://github.com/joomla/joomla-cms/issues/29459
      // The choices.js always auto-highlights the first element
      // in the dropdown that not allow to add a custom Term.
      //
      // This workaround can be removed when choices.js
      // will have an option that allow to disable it.

      const _highlightChoice = this.choicesInstance._highlightChoice;
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
        });

        // Removing the highlight must not change what is announced as selected
        this.syncChoicesSelectedState();

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
          value: new DOMParser().parseFromString(this.newItemPrefix + event.target.value, 'text/html').body.textContent,
          label: new DOMParser().parseFromString(event.target.value, 'text/html').body.textContent,
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
      this.choicesInstance.passedElement.optionsAsChoices().forEach((choiceItem) => {
        if (choiceItem.value !== undefined) {
          this.choicesCache[choiceItem.value] = choiceItem.label;
        }
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
    if (this.selectionObserver) {
      this.selectionObserver.disconnect();
      this.selectionObserver = null;
    }

    if (this.choicesInstance) {
      // Keep selected values, because choices will reset them on re-init
      this.disconnectValues = this.choicesInstance.getValue(true);
      this.choicesInstance.destroy();
      this.isDisconnected = true;
    }

    if (this.activeXHR) {
      this.activeXHR.abort();
      this.activeXHR = null;
    }
  }

  /**
   * Escape a plain text string for use as HTML.
   *
   * @param {string} text
   * @returns {string}
   */
  static escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;

    return div.innerHTML;
  }

  /**
   * The id of the visible label of the field, it is created when the label does not have one.
   *
   * @returns {string}
   */
  getLabelId() {
    const label = this.select.labels?.[0];

    if (!label) {
      return '';
    }

    if (!label.id && this.select.id) {
      label.id = `${this.select.id}-lbl`;
    }

    return label.id;
  }

  /**
   * Accessible name for the remove button of an item, eg "Remove tag: Joomla".
   *
   * @param {object|undefined} item      The item, as passed by Choices.js
   * @param {string}           fallback  Value to use when there is no item
   * @returns {string}
   */
  getRemoveItemLabel(item, fallback) {
    let label = item ? item.label : fallback;

    if (label && typeof label === 'object') {
      label = label.trusted ?? label.raw ?? label.escaped ?? '';
    }

    return this.removeItemText.replace('%s', () => String(label ?? ''));
  }

  /**
   * Accessibility improvements on top of Choices.js. Called after every (re)initialisation.
   */
  enhanceAccessibility() {
    const choices = this.choicesInstance;

    if (!choices) {
      return;
    }

    // Keyboard, pointer and assistive technology all remove an item through one click handler.
    // Adding the same listener twice is a no-op, so this is safe on re-init.
    this.addEventListener('keydown', this.onRemoveButtonKeydown, true);
    this.addEventListener('mousedown', this.onRemoveButtonMousedown, true);
    this.addEventListener('click', this.onRemoveButtonClick, true);

    // Announce an option as selected only when it is selected, not when it is highlighted
    this.select.addEventListener('highlightChoice', this.syncChoicesSelectedState);
    this.select.addEventListener('showDropdown', this.onDropdownToggle);
    this.select.addEventListener('hideDropdown', this.onDropdownToggle);

    const input = choices.input?.element;
    const inner = choices.containerInner.element;
    const itemList = choices.itemList.element;

    if (!choices._isSelectMultipleElement || !input || !inner.contains(input) || !inner.contains(itemList)) {
      return;
    }

    const outer = choices.containerOuter.element;
    const choiceList = choices.choiceList.element;
    const labelId = this.getLabelId();

    if (!this.baseId) {
      this.baseId = this.select.id || `joomla-fancy-select-${Math.random().toString(36).slice(2, 10)}`;
    }

    // The search input comes first, so it can be reached without tabbing through every selected item
    inner.insertBefore(input, itemList);

    // The wrapper groups the input with the selected items, the input itself is the combobox
    outer.setAttribute('role', 'group');
    ['aria-autocomplete', 'aria-haspopup', 'aria-expanded', 'aria-activedescendant'].forEach((name) => outer.removeAttribute(name));

    if (!choiceList.id) {
      choiceList.id = `${this.baseId}-listbox`;
    }

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-controls', choiceList.id);
    this.syncExpandedState(choices.dropdown.isActive);

    // Choices.js keeps the combobox state on the wrapper, keep it on the input instead.
    // The container is created again on re-init, so it is patched once per container.
    const container = choices.containerOuter;

    if (!container.joomlaComboboxOnInput) {
      const { open, close } = container;

      container.joomlaComboboxOnInput = true;
      container.open = (...args) => {
        open.apply(container, args);
        this.syncExpandedState(true);
      };
      container.close = (...args) => {
        close.apply(container, args);
        this.syncExpandedState(false);
      };
      container.setActiveDescendant = () => {};
    }

    if (labelId) {
      outer.setAttribute('aria-labelledby', labelId);
      input.setAttribute('aria-labelledby', labelId);
      input.removeAttribute('aria-label');
    }

    // Keep a description that was set on the original select, eg the field description
    const selectDescribedBy = this.select.getAttribute('aria-describedby');

    if (selectDescribedBy) {
      input.setAttribute('aria-describedby', selectDescribedBy);
    }

    // List of the selected items, referenced from the input. It is hidden, so it is not read twice
    // in browse mode, aria-describedby still uses its text.
    const summaryId = `${this.baseId}-selected-items`;
    let summary = Array.from(this.children).find((element) => element.id === summaryId);

    if (!summary) {
      summary = document.createElement('span');
      summary.id = summaryId;
      summary.hidden = true;
      this.appendChild(summary);
    }

    this.selectedSummary = summary;
    this.updateSelectedSummary();

    if (!this.selectionObserver) {
      this.selectionObserver = new MutationObserver(() => this.updateSelectedSummary());
    }

    this.selectionObserver.disconnect();
    this.selectionObserver.observe(itemList, { childList: true });
  }

  /**
   * Update the visually hidden description of the selected items.
   */
  updateSelectedSummary() {
    const choices = this.choicesInstance;

    if (!choices || !this.selectedSummary) {
      return;
    }

    const input = choices.input.element;
    const summaryId = this.selectedSummary.id;
    const labels = [].concat(choices.getValue() || [])
      .filter((item) => item && !item.placeholder)
      .map((item) => (typeof item.label === 'object' ? (item.label.trusted ?? item.label.raw ?? '') : String(item.label ?? '')))
      .filter((label) => label !== '');
    const describedBy = (input.getAttribute('aria-describedby') || '')
      .split(/\s+/)
      .filter((id) => id && id !== summaryId);

    if (labels.length) {
      this.selectedSummary.textContent = this.selectedItemsText.replace('%s', () => labels.join(', '));
      describedBy.push(summaryId);
    } else {
      this.selectedSummary.textContent = '';
    }

    if (describedBy.length) {
      input.setAttribute('aria-describedby', describedBy.join(' '));
    } else {
      input.removeAttribute('aria-describedby');
    }
  }

  /**
   * Choices.js marks the highlighted option as selected, and un-marks it when the highlight moves on,
   * even if the option is selected. Reflect the real selection instead.
   */
  syncChoicesSelectedState() {
    const choices = this.choicesInstance;

    if (!choices) {
      return;
    }

    const selectedClasses = [].concat(choices.config.classNames.selectedState).join(' ').split(/\s+/).filter(Boolean);

    choices.dropdown.element.querySelectorAll('[data-choice-selectable]').forEach((option) => {
      const isSelected = selectedClasses.length > 0 && selectedClasses.every((name) => option.classList.contains(name));
      option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
    });
  }

  /**
   * Re-sync the selected state when the dropdown is shown or hidden.
   */
  onDropdownToggle() {
    this.syncChoicesSelectedState();
  }

  /**
   * For a multiple select the input is the combobox: keep aria-expanded on the input, not on the group.
   *
   * @param {boolean} isExpanded
   */
  syncExpandedState(isExpanded) {
    const choices = this.choicesInstance;

    if (!choices) {
      return;
    }

    const outer = choices.containerOuter.element;
    const input = choices.input.element;

    outer.removeAttribute('aria-expanded');
    outer.removeAttribute('aria-activedescendant');
    input.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');

    // A collapsed combobox must not point to an option in the hidden list
    if (!isExpanded) {
      input.removeAttribute('aria-activedescendant');
    }
  }

  /**
   * The remove button of an item the event belongs to, if any.
   *
   * @param {Event} event
   * @returns {HTMLElement|null}
   */
  getRemoveButton(event) {
    const choices = this.choicesInstance;

    if (!choices || !(event.target instanceof Element)) {
      return null;
    }

    const button = event.target.closest('[data-button]');

    return button && choices.containerOuter.element.contains(button) ? button : null;
  }

  /**
   * Choices.js opens the dropdown on Enter and Space, instead let the browser activate the button.
   *
   * @param {KeyboardEvent} event
   */
  onRemoveButtonKeydown(event) {
    if ((event.key === 'Enter' || event.key === ' ') && this.getRemoveButton(event)) {
      event.stopPropagation();
    }
  }

  /**
   * Choices.js removes the item already on mousedown, the item is removed on click instead.
   *
   * @param {MouseEvent} event
   */
  onRemoveButtonMousedown(event) {
    if (this.getRemoveButton(event)) {
      // Keep the focus where it is, like Choices.js does
      event.preventDefault();
      event.stopPropagation();
    }
  }

  /**
   * Remove the item of a remove button. Mouse, Enter, Space and screen reader activation all end up here.
   *
   * @param {MouseEvent} event
   */
  onRemoveButtonClick(event) {
    const button = this.getRemoveButton(event);

    if (!button) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    this.removeItemByButton(button);
  }

  /**
   * Remove the item of the given button, and if the button had the focus, move the focus to a logical
   * place nearby: the next item, the previous item or the input.
   *
   * @param {HTMLElement} button
   */
  removeItemByButton(button) {
    const choices = this.choicesInstance;

    if (choices.containerOuter.isDisabled) {
      return;
    }

    const items = Array.from(choices.itemList.element.querySelectorAll('[data-item]'));
    const index = items.indexOf(button.closest('[data-item]'));
    const hadFocus = button.contains(document.activeElement);

    // Removes the item and renders the items again, synchronously
    choices._handleButtonAction(button);

    if (!hadFocus) {
      return;
    }

    if (choices._isSelectMultipleElement) {
      const buttons = Array.from(choices.itemList.element.querySelectorAll('[data-item] [data-button]'));
      const target = buttons[index] || buttons[index - 1] || choices.input.element;

      target.focus();
    } else {
      choices.containerOuter.element.focus();
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
        for (let i = items.length - 1; i >= 0; i--) { // The loop must be form the end !!!
          item = items[i];
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
