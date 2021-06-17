/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We need to use JS to move the modal before the closing body tag to avoid stacking issues
const multilangueModal = document.getElementById('multiLangModal');

if (multilangueModal) {
  const bsModal = bootstrap.Modal.getInstance(multilangueModal);

  if (bsModal) {
    bsModal.dispose();
  }

  // Append the modal before closing body tag
  document.body.appendChild(multilangueModal);

  // Modal was moved so it needs to be re initialised
  Joomla.initialiseModal(multilangueModal);
}
