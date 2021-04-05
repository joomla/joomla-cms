/**
 * @package     Joomla.JavaScript
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

((window, document) => {
  'use strict';

  window.JoomlainitReCaptcha2 = () => {
    const elements = [].slice.call(document.getElementsByClassName('g-recaptcha'));
    const optionKeys = ['sitekey', 'theme', 'size', 'tabindex', 'callback', 'expired-callback', 'error-callback'];

    elements.forEach((element) => {
      let options = {};

      if (element.dataset) {
        options = element.dataset;
      } else {
        optionKeys.forEach((key) => {
          const optionKeyFq = `data-${key}`;
          if (element.hasAttribute(optionKeyFq)) {
            options[key] = element.getAttribute(optionKeyFq);
          }
        });
      }

      // Set the widget id of the recaptcha item
      element.setAttribute(
        'data-recaptcha-widget-id',
        window.grecaptcha.render(element, options),
      );
    });
  };
})(window, document);
