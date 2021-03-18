/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Awesomplete, Joomla, window, document) => {
  'use strict';

  if (!Joomla) {
    throw new Error('core.js was not properly initialised');
  } // Handle the autocomplete


  const onKeyUp = ({
    target
  }) => {
    if (target.value.length > 1) {
      target.awesomplete.list = [];
      Joomla.request({
        url: `${Joomla.getOptions('finder-search').url}&q=${target.value}`,
        method: 'GET',
        data: {
          q: target.value
        },
        perform: true,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        onSuccess: resp => {
          const response = JSON.parse(resp);

          if (Object.prototype.toString.call(response.suggestions) === '[object Array]') {
            target.awesomplete.list = response.suggestions;
          }
        },
        onError: xhr => {
          if (xhr.status > 0) {
            Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
          }
        }
      });
    }
  }; // Handle the submit


  const onSubmit = event => {
    event.stopPropagation();
    const advanced = event.target.querySelector('.js-finder-advanced'); // Disable select boxes with no value selected.

    if (advanced.length) {
      const fields = [].slice.call(advanced.querySelectorAll('select'));
      fields.forEach(field => {
        if (!field.value) {
          field.setAttribute('disabled', 'disabled');
        }
      });
    }
  }; // The boot sequence


  const onBoot = () => {
    const searchWords = [].slice.call(document.querySelectorAll('.js-finder-search-query'));
    searchWords.forEach(searchword => {
      // Handle the auto suggestion
      if (Joomla.getOptions('finder-search')) {
        searchword.awesomplete = new Awesomplete(searchword); // If the current value is empty, set the previous value.

        searchword.addEventListener('keyup', onKeyUp);
      }
    });
    const forms = [].slice.call(document.querySelectorAll('.js-finder-searchform'));
    forms.forEach(form => {
      form.addEventListener('submit', onSubmit);
    }); // Cleanup

    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(window.Awesomplete, window.Joomla, window, document);