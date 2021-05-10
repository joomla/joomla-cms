/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const helpIndex = document.getElementById('help-index');
    const links = helpIndex.querySelectorAll('a');
    links && links.forEach(element => {
      element.addEventListener('click', () => {
        window.scroll(0,0);
      });
    })
  });
})(document);
