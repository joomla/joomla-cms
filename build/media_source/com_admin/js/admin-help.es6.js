/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((document, Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', function(event) {
    let helpIndex = document.getElementById('help-index');
    let links = helpIndex.querySelectorAll('a');
    links && links.forEach(element => {
      element.addEventListener('click', event => {
        window.scroll(0,0);
      });
    })
  });
})(document, Joomla);
