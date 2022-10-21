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

      const loading = document.getElementById('loading');
      if (loading) {
        loading.classList.remove('hidden');
      }

      form.installtype.value = 'url';
      form.submit();
    };
  });
})(Joomla);
