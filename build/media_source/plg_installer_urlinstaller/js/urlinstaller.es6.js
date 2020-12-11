/**
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbuttonurl = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        Joomla.renderMessages({ warning: [Joomla.Text._('PLG_INSTALLER_URLINSTALLER_NO_URL')] });
      } else {
        const loading = document.getElementById('loading');
        if (loading) {
          loading.classList.remove('hidden');
        }

        form.installtype.value = 'url';
        form.submit();
      }
    };
  });
})(Joomla);
