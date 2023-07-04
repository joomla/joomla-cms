/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  // before 'joomla:showon-processed' is implemented in Showon we must use more frequent 'joomla:showon-show' and'joomla:showon-hide' events
  ['joomla:showon-show', 'joomla:showon-hide'].forEach((eventType) => {
    document.addEventListener(eventType, () => {
      document.querySelectorAll('#guidedtour-dates-form fieldset').forEach((fieldset) => {
        // Only hide fieldsets containing field control-group i.e. not radio selectors etc. that may use fieldsets
        if (fieldset.querySelectorAll(':scope .control-group').length === 0) {
          return;
        }
        const visibleChildren = fieldset.querySelectorAll(':scope .control-group:not(.hidden)');
        if (visibleChildren.length) {
          fieldset.classList.remove('hidden');
        } else {
          fieldset.classList.add('hidden');
        }
      });
      document.querySelectorAll('#guidedtour-dates-form joomla-tab-element').forEach((tabelement) => {
        const tabLabel = document.querySelector(`button[aria-controls="${tabelement.id}"]`);
        if (tabLabel) {
          const visibleChildren = tabelement.querySelectorAll(':scope .control-group:not(.hidden)');
          if (visibleChildren.length) {
            tabLabel.removeAttribute('hidden');
          } else {
            tabLabel.setAttribute('hidden', 'hidden');
          }
        }
      });
    });
  });
})();
