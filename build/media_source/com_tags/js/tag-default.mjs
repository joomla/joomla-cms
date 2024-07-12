/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((document) => {
  'use strict';

  const onClick = () => {
    const form = document.getElementById('adminForm');
    document.getElementById('filter-search').value = '';
    form.submit();
  };

  const onBoot = () => {
    const form = document.getElementById('adminForm');
    const element = form.querySelector('button[type="reset"]');

    if (element) {
      element.addEventListener('click', onClick);
    }

    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(document);
