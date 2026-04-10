/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {

  document.addEventListener('DOMContentLoaded', () => {
    const loading = document.getElementById('loading');
    const installer = document.getElementById('installer-install');
    if (loading && installer) {
      loading.style.position = 'absolute';
      loading.style.top = 0;
      loading.style.left = 0;
      loading.style.width = '100%';
      loading.style.height = '100%';
      loading.classList.add('hidden');
    }
  });
})();
