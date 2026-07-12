/**
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';

  customElements.whenDefined('joomla-field-fancy-select').then(() => {
    const primaryEl = document.getElementById('jform_catid');
    const secondaryEl = document.getElementById('jform_secondary_categories');

    if (!primaryEl || !secondaryEl) {
      return;
    }

    const secondaryWrapper = secondaryEl.closest('joomla-field-fancy-select');

    if (!secondaryWrapper) {
      return;
    }

    const secondaryChoices = secondaryWrapper.choicesInstance;

    if (!secondaryChoices) {
      return;
    }

    // Cache all available secondary category options before rebuilding the field.
    const allOptions = Object.freeze(
      Array.from(secondaryEl.options)
        .filter((opt) => opt.value !== '')
        .map((opt) => ({
          value: String(opt.value),
          label: opt.text,
        })),
    );

    // Get initial selected secondaries.
    let selectedSecondaries = new Set(
      Array.from(secondaryEl.options)
        .filter((opt) => opt.selected && opt.value !== '')
        .map((opt) => String(opt.value)),
    );

    let isRebuilding = false;

    // Helper to get current primary value as string.
    const getPrimaryValue = () => String(primaryEl.value || '');

    const rebuildSecondary = () => {
      const primaryValue = getPrimaryValue();

      isRebuilding = true;

      try {
        // Clear all existing chips
        secondaryChoices.removeActiveItems();

        // Rebuild dropdown — all options except primary
        const choices = allOptions
          .filter((opt) => opt.value !== primaryValue)
          .map((opt) => ({
            value: opt.value,
            label: opt.label,
            selected: false,
            disabled: false,
          }));

        secondaryChoices.setChoices(choices, 'value', 'label', true);
        // Restore selected secondaries.
        selectedSecondaries.forEach((val) => {
          if (val !== primaryValue) {
            secondaryChoices.setChoiceByValue(val);
          }
        });
      } finally {
        isRebuilding = false;
      }
    };

    primaryEl.addEventListener('change', () => {
      const primaryVal = getPrimaryValue();

      // If primary is in selected secondaries, remove it.
      selectedSecondaries.delete(primaryVal);

      // Rebuild secondary options and chips.
      rebuildSecondary();
    });

    secondaryEl.addEventListener('change', () => {
      if (isRebuilding) {
        return;
      }

      selectedSecondaries = new Set(
        Array.from(secondaryEl.options)
          .filter((opt) => opt.selected && opt.value !== '')
          .map((opt) => String(opt.value)),
      );

      rebuildSecondary();
    });

    // Initialize state on page load
    selectedSecondaries.delete(getPrimaryValue());
    rebuildSecondary();
  });
})();
