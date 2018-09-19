/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(customElements){
  "use strict";

  class JoomlaFieldTag extends HTMLElement {

    // Attribute getters
    get allowCustom()    { return this.hasAttribute('allow-custom'); }
    get remoteSearch()   { return this.hasAttribute('remote-search'); }
    get url()            { return this.getAttribute('url'); }
    get termKey()        { return this.getAttribute('term-key') || 'term'; }
    get minTermLength()  { return parseInt(this.getAttribute('min-term-length')) || 3; }
    get newItemPrefix()  { return this.getAttribute('new-item-prefix') || ''; }

    connectedCallback() {
      if (!window.Choices) {
        throw new Error('JoomlaFieldTag require Choices.js to work');
      }

      // Get a <select> element
      this.select = this.querySelector('select');

      if (!this.select) {
        throw new Error('JoomlaFieldTag require <select> element to work');
      }

      // Init Choices
      this.choicesInstance = new Choices(this.select, {
        removeItemButton: true,
        searchFloor: this.minTermLength,
        searchResultLimit: 10,
        shouldSort: false,
        fuseOptions: {
          threshold: 0.3 // Strict search
        },
        noResultsText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
        itemSelectText: Joomla.Text._('JGLOBAL_SELECT_PRESS_TO_SELECT', 'Press to select'),
      });

      // Handle typing of custom term
      if (this.allowCustom) {
        this.addEventListener('keydown', (event) => {
          if (this.choicesInstance.highlightPosition || event.keyCode !== 13
            || event.target !== this.choicesInstance.input || !event.target.value) return;

          event.preventDefault();

          // Make sure nothing is highlighted
          const highlighted = this.choicesInstance.dropdown.querySelector('.' + this.choicesInstance.config.classNames.highlightedState);
          if (highlighted) return;

          // Add new option, and make it active
          this.choicesInstance.setChoices([{
            value: this.newItemPrefix + event.target.value,
            label: event.target.value,
            selected: true
          }], 'value', 'label', false);

          event.target.value = null;
          this.choicesInstance.hideDropdown();

          return false;
        });
      }

      // Handle remote search
      if (this.remoteSearch && this.url) {
        // TODO: make it work
        this.select.addEventListener('search', (event) => {
          console.log(event.detail);
        });
      }
    }

    disconnectedCallback() {
      // Destroy Choices instance, to unbind an event listeners
      if (this.choicesInstance) {
        this.choicesInstance.destroy();
        this.choicesInstance = null;
      }
    }
  }

  customElements.define('joomla-field-tag', JoomlaFieldTag);

})(customElements);
