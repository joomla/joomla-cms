/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {

  const onChange = value => {
    if (value === '-' || parseInt(value, 10) === 0) {
      document.getElementById('menuselect-group').classList.add('hidden');
    } else {
      document.getElementById('menuselect-group').classList.remove('hidden');
    }
  };
  const onBoot = () => {
    const element = document.getElementById('jform_assignment');
    if (element) {
      // Initialise the state
      onChange(element.value);

      // Check for changes in the state
      element.addEventListener('change', ({
        target
      }) => {
        onChange(target.value);
      });
    }
    document.removeEventListener('DOMContentLoaded', onBoot);
  };
  document.addEventListener('DOMContentLoaded', onBoot);
})();
