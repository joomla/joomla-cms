/**
 * @package     Joomla.JavaScript
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

window.JoomlaInitReCaptcha2 = () => {
  'use strict';

  const itemNodes = document.getElementsByClassName('g-recaptcha');
  const items = [].slice.call(itemNodes);
  items.forEach((item) => {
    const options = item.dataset ? item.dataset : {
      sitekey: item.getAttribute('data-sitekey'),
      theme: item.getAttribute('data-theme'),
      size: item.getAttribute('data-size'),
    };

    /* global grecaptcha */
    grecaptcha.render(item, options);
  });
};
