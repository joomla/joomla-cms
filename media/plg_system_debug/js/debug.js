/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(document => {

  // Selectors used by this script
  const debugSectionTogglerSelector = '.dbg-header';
  const toggleTargetAttribute = 'data-debug-toggle';

  /**
   * Toggle an element by id
   * @param {string} id - The ID of the element to toggle
   */
  const toggle = id => {
    document.getElementById(id).classList.toggle('hidden');
  };

  /**
   * Register events for debug section togglers
   */
  const registerEvents = () => {
    document.querySelectorAll(debugSectionTogglerSelector).forEach(toggler => {
      toggler.addEventListener('click', event => {
        event.preventDefault();
        toggle(toggler.getAttribute(toggleTargetAttribute));
      });
    });
  };

  /**
   * Adds accessible attributes (name, id, for) to PHPDebugBar settings form elements.
   * This fixes accessibility issues where form fields lack proper identification attributes
   * which are required for:
   * - Proper form submission behavior
   * - Screen reader compatibility
   * - Browser autofill functionality
   *
   * @param {HTMLElement} settingsForm - The settings form element to fix
   */
  const addAccessibleAttributesToForm = settingsForm => {
    if (!settingsForm) {
      return;
    }

    // Get all form rows within the settings panel
    const formRows = settingsForm.querySelectorAll('.phpdebugbar-form-row');
    formRows.forEach((row, index) => {
      const labelDiv = row.querySelector('.phpdebugbar-form-label');
      const inputDiv = row.querySelector('.phpdebugbar-form-input');
      if (!labelDiv || !inputDiv) {
        return;
      }

      // Get the label text to create a unique identifier
      const labelText = labelDiv.textContent.trim();

      // Create a sanitized ID from the label text
      const sanitizedId = `debugbar-${labelText.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/-+$/, '')}`;

      // Find select elements and add attributes
      const selectElement = inputDiv.querySelector('select');
      if (selectElement && !selectElement.hasAttribute('name')) {
        selectElement.setAttribute('name', sanitizedId);
        selectElement.setAttribute('id', sanitizedId);

        // Convert the label div to be associated with the select
        labelDiv.setAttribute('id', `${sanitizedId}-label`);
        selectElement.setAttribute('aria-labelledby', `${sanitizedId}-label`);
      }

      // Find checkbox inputs and add attributes
      const checkboxElement = inputDiv.querySelector('input[type="checkbox"]');
      if (checkboxElement && !checkboxElement.hasAttribute('name')) {
        checkboxElement.setAttribute('name', sanitizedId);
        checkboxElement.setAttribute('id', sanitizedId);

        // If the checkbox is wrapped in a label, associate it properly
        const parentLabel = checkboxElement.closest('label');
        if (parentLabel) {
          parentLabel.setAttribute('for', sanitizedId);
        }
      }

      // Find any other input elements and add attributes
      const inputElement = inputDiv.querySelector('input:not([type="checkbox"])');
      if (inputElement && !inputElement.hasAttribute('name')) {
        inputElement.setAttribute('name', sanitizedId);
        inputElement.setAttribute('id', sanitizedId);
        labelDiv.setAttribute('id', `${sanitizedId}-label`);
        inputElement.setAttribute('aria-labelledby', `${sanitizedId}-label`);
      }
    });
  };

  /**
   * Fix missing name/id attributes in PHPDebugBar settings form elements.
   * Uses MutationObserver to detect when the debugbar adds its settings panel to the DOM,
   * then applies accessible attributes to all form elements within.
   *
   * This addresses browser warnings about form fields lacking proper name attributes
   * which can affect accessibility and form behavior.
   */
  const fixDebugBarFormAttributes = () => {
    /**
     * Process the debugbar element and fix any settings forms found
     * @param {HTMLElement} debugbar - The debugbar container element
     */
    const processDebugBar = debugbar => {
      // Find and fix the settings form
      const settingsForm = debugbar.querySelector('form.phpdebugbar-settings');
      if (settingsForm) {
        addAccessibleAttributesToForm(settingsForm);
      }
    };

    // Create a MutationObserver to watch for the debugbar being rendered
    const observer = new MutationObserver(mutations => {
      for (const mutation of mutations) {
        // Check added nodes for debugbar elements
        for (const node of mutation.addedNodes) {
          if (node.nodeType !== Node.ELEMENT_NODE) {
            continue;
          }

          // Check if this is the debugbar or contains it
          if (node.classList && node.classList.contains('phpdebugbar')) {
            processDebugBar(node);
          } else if (node.querySelector) {
            const debugbar = node.querySelector('.phpdebugbar');
            if (debugbar) {
              processDebugBar(debugbar);
            }
          }

          // Check if this is the settings form directly
          if (node.classList && node.classList.contains('phpdebugbar-settings')) {
            addAccessibleAttributesToForm(node);
          }

          // Check if any settings form was added as a descendant
          if (node.querySelector) {
            const settingsForm = node.querySelector('form.phpdebugbar-settings');
            if (settingsForm) {
              addAccessibleAttributesToForm(settingsForm);
            }
          }
        }
      }
    });

    // Start observing document body for debugbar additions
    if (document.body) {
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    }

    // Also check if debugbar already exists (in case we loaded late)
    const existingDebugbar = document.querySelector('.phpdebugbar');
    if (existingDebugbar) {
      processDebugBar(existingDebugbar);
    }

    // Additional fallback: wait for window load and check again
    window.addEventListener('load', () => {
      const debugbar = document.querySelector('.phpdebugbar');
      if (debugbar) {
        processDebugBar(debugbar);
      }
    });
  };

  // Initialize all functionality when DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    registerEvents();
    fixDebugBarFormAttributes();
  });
})(document);
