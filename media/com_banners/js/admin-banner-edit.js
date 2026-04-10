/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(document => {

  const updateBannerFields = value => {
    const imgWrapper = document.getElementById('image');
    const custom = document.getElementById('custom');
    switch (value) {
      case '0':
        // Image
        imgWrapper.classList.remove('hidden');
        custom.classList.add('hidden');
        break;
      case '1':
        // Custom
        imgWrapper.classList.add('hidden');
        custom.classList.remove('hidden');
        break;
      // Do nothing
    }
  };
  document.addEventListener('DOMContentLoaded', () => {
    const jformType = document.getElementById('jform_type');
    if (jformType) {
      // Hide/show parameters initially
      updateBannerFields(jformType.value);

      // Hide/show parameters when the type has been selected
      jformType.addEventListener('change', ({
        target
      }) => {
        updateBannerFields(target.value);
      });
    }
  });
})(document);
