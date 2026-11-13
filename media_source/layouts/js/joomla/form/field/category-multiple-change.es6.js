/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    // Get the field ID passed from PHP
    const id = Joomla.getOptions('category-multiple-change');

    if (!id) return;

    const selectEl = document.getElementById(id);
    if (selectEl) {
      // Target the wrapper element because the layout puts the onchange attribute on it
      const fancySelect = selectEl.closest('joomla-field-fancy-select');

      if (fancySelect) {
        // Remove the core single-select handler from the wrapper
        fancySelect.removeAttribute('onchange');

        // Add our custom handler to the native select element
        selectEl.addEventListener('change', function () {
          setTimeout(function () {
            var selected = Array.from(selectEl.selectedOptions).map(function (o) { return o.value; }).sort().join(',');
            var stored = fancySelect.getAttribute('data-refresh-catid') || '';

            if (selected === stored) return;

            document.body.appendChild(document.createElement('joomla-core-loader'));
            var section = fancySelect.getAttribute('data-refresh-section');
            if (section) {
              var taskInput = document.querySelector('input[name=task]');
              if (taskInput) taskInput.value = section + '.reload';
            }
            Joomla.submitform(section + '.reload', selectEl.form);
          }, 50);
        });
      }
    }
  });
})(Joomla);
