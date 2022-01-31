/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      3.5.0
 */
((document) => {
  'use strict';

  // Selectors used by this script
  const statsDataTogglerId = 'js-pstats-data-details-toggler';
  const statsDataDetailsId = 'js-pstats-data-details';
  const resetId = 'js-pstats-reset-uid';
  const uniqueIdFieldId = 'jform_params_unique_id';

  const onToggle = (event) => {
    event.preventDefault();
    const element = document.getElementById(statsDataDetailsId);

    if (element) {
      element.classList.toggle('d-none');
    }
  };

  const onReset = (event) => {
    event.preventDefault();
    document.getElementById(uniqueIdFieldId).value = '';
    Joomla.submitbutton('plugin.apply');
  };

  const onBoot = () => {
    // Toggle stats details
    const toggler = document.getElementById(statsDataTogglerId);
    if (toggler) {
      toggler.addEventListener('click', onToggle);
    }

    // Reset the unique id
    const reset = document.getElementById(resetId);
    if (reset) {
      reset.addEventListener('click', onReset);
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(document, Joomla);
