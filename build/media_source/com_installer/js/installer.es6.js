/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbutton4 = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        Joomla.renderMessages({ warning: [Joomla.JText._('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL')] });
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'url';
        form.submit();
      }
    };

    Joomla.submitbutton5 = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_url.value !== '' || form.install_url.value !== 'http://' || form.install_url.value !== 'https://') {
        Joomla.submitbutton4();
      } else if (form.install_url.value === '') {
        Joomla.renderMessages({ warning: [Joomla.apps.options.btntxt] });
      } else {
        document.querySelector('#appsloading').classList.remove('hidden');
        form.installtype.value = 'web';
        form.submit();
      }
    };

    Joomla.displayLoader = () => {
      const loading = document.getElementById('loading');
      if (loading) {
        loading.classList.remove('hidden');
      }
    };

    const loading = document.getElementById('loading');
    const installer = document.getElementById('installer-install');

    if (loading && installer) {
      loading.style.top = parseInt(installer.offsetTop - window.pageYOffset, 10);
      loading.style.left = 0;
      loading.style.width = '100%';
      loading.style.height = '100%';
      loading.classList.add('hidden');
      loading.style.marginTop = '-10px';
    }
  });
})(Joomla);
