/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const Banner = new bootstrap.Modal(document.getElementById('cookieBanner'));
    Banner.show();
  });
})(document);
