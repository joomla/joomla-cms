/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @package     Joomla.JavaScript
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

window.JoomlaInitReCaptcha2 = function () {
  'use strict';

  var itemNodes = document.getElementsByClassName('g-recaptcha');
  var items = [].slice.call(itemNodes);
  items.forEach(function (item) {
    var options = item.dataset ? item.dataset : {
      sitekey: item.getAttribute('data-sitekey'),
      theme: item.getAttribute('data-theme'),
      size: item.getAttribute('data-size')
    };

    /* global grecaptcha */
    grecaptcha.render(item, options);
  });
};
