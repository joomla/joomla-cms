/**
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
import JoomlaDialog from 'joomla.dialog';

(() => {
  'use strict';

  const form = document.getElementById('component-form');
  const navigation = form?.querySelector('.sidebar-nav');

  if (!form || !navigation) {
    return;
  }

  /**
   * Get a snapshot of the current form values.
   *
   * FormData preserves repeated values (for example, multi-select fields).
   *
   * @returns {string}
   */
  const getFormState = () => {
    const data = new FormData(form);

    return JSON.stringify(Array.from(data.entries()));
  };

  let initialState = getFormState();
  let pendingNavigation = null;

  /**
   * Whether the form has changes which have not been saved.
   *
   * @returns {boolean}
   */
  const hasUnsavedChanges = () => getFormState() !== initialState;

  /**
   * Update the saved state after a successful form submission.
   */
  form.addEventListener('submit', () => {
    // The form is leaving the page to save. A failed save will return with
    // the submitted values, which become the new initial state on page load.
    initialState = getFormState();
  });

  /**
   * Intercept navigation to another component when the form has changed.
   */
  navigation.addEventListener('click', async (event) => {
    const link = event.target.closest('a[href]');

    if (!link || !navigation.contains(link) || !hasUnsavedChanges()) {
      return;
    }

    // Allow modified clicks and links opening another browsing context.
    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target) {
      return;
    }

    event.preventDefault();

    if (pendingNavigation) {
      return;
    }

    pendingNavigation = link.href;

    const discard = await JoomlaDialog.confirm(
      Joomla.Text._(
        'COM_CONFIG_UNSAVED_CHANGES_CONFIRM',
        'You have made changes that have not been saved. If you continue, your changes will be lost.'
      ),
      Joomla.Text._('WARNING', 'Warning')
    );

    const destination = pendingNavigation;
    pendingNavigation = null;

    if (discard && destination) {
      window.location.assign(destination);
    }
  });
})();
