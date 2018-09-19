/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(customElements){
  "use strict";

  class JoomlaFieldModuelEditposition extends HTMLElement {

    connectedCallback() {
      if (!window.Choices) {
        throw new Error('JoomlaFieldModuelEditposition require Choices.js to work');
      }

      // Get a <select> element
      this.select = this.querySelector('select');

      if (!this.select) {
        throw new Error('JoomlaFieldModuelEditposition require <select> element to work');
      }

      // Init Choices
      this.choicesInstance = new Choices(this.select, {
        //removeItemButton: true,
        searchFloor: 1,
        searchResultLimit: 10,
        shouldSort: false,
        fuseOptions: {
          threshold: 0.3 // Strict search
        },
        noResultsText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
        itemSelectText: Joomla.Text._('JGLOBAL_SELECT_PRESS_TO_SELECT', 'Press to select'),
      });

      // Handle typing of custom position
      this.addEventListener('keydown', (event) => {
        if (this.choicesInstance.highlightPosition || event.keyCode !== 13
          || event.target !== this.choicesInstance.input || !event.target.value) return;

        // Make sure nothing is highlighted
        const highlighted = this.choicesInstance.dropdown.querySelector('.' + this.choicesInstance.config.classNames.highlightedState);
        if (highlighted) return;

        // Add new option, and make it active
        this.choicesInstance.setChoices([{value: event.target.value, label: event.target.value, selected: true}], 'value', 'label', false);
        event.target.value = null;
        this.choicesInstance.hideDropdown();
        return false;
      });
    }

    disconnectedCallback() {
      // Destroy Choices instance, to unbind an event listeners
      if (this.choicesInstance) {
        this.choicesInstance.destroy();
      }
    }
  }

  customElements.define('joomla-field-module-editposition', JoomlaFieldModuelEditposition);

})(customElements);
