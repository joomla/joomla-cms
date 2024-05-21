/**
 * @package     Joomla.JavaScript
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

((window, document) => {
  'use strict';

  window.JoomlainitReCaptchaInvisible = () => {
    const optionKeys = ['sitekey', 'badge', 'size', 'tabindex', 'callback', 'expired-callback', 'error-callback'];

    document.getElementsByClassName('g-recaptcha').forEach((element) => {
      let options = {};

      if (element.dataset) {
        options = element.dataset;
      } else {
        optionKeys.forEach((key) => {
          const optionKeyFq = `data-${optionKeys[key]}`;

          if (element.hasAttribute(optionKeyFq)) {
            options[optionKeys[key]] = element.getAttribute(optionKeyFq);
          }
        });
      }

      // Set the widget id of the recaptcha item
      element.setAttribute(
        'data-recaptcha-widget-id',
        window.grecaptcha.render(element, options),
      );

      // Execute the invisible reCAPTCHA
      window.grecaptcha.execute(element.getAttribute('data-recaptcha-widget-id'));
    });
  };
})(window, document);
