var errorLocale = window.errorLocale || null;

(function(document, errorLocale) {
  'use strict';

  if (errorLocale) {
    var header = document.getElementById('headerText');

    // Create links for all the languages
    Object.keys(errorLocale).forEach(function(key) {
      var sel = document.getElementById('translatedLanguagesSelect'),
          opt = document.createElement('option');
      opt.text = errorLocale[key].language;
      opt.value = key;

      if (key === 'en-GB') {
        opt.setAttribute('selected', 'selected');
      }

      document.getElementById('translatedLanguagesSelect').addEventListener('change', function(e) {
        var ref = e.target.value, helpLink, meta;

        if (ref) {
          header.innerHTML = errorLocale[ref].header;
        }

        helpLink = document.getElementById('linkHelp');
        if (helpLink) {
          helpLink.innerText = errorLocale[ref]['help-url-text'];
        }

        meta = document.querySelector('[http-equiv="Content-Language"]');
        if (meta) {
          meta.setAttribute('content', ref);
        }
      });

      sel.appendChild(opt)
    });

    // Select language based on Browser's language
    Object.keys(errorLocale).forEach(function(key) {
      if (navigator.language === key) {
        // Remove the selected property
        document.querySelector('#translatedLanguagesSelect option[value="en-GB"]').removeAttribute('selected');
        document.querySelector('#translatedLanguagesSelect option[value="' + key + '"]').setAttribute('selected', 'selected');

        // Append the translated strings
        header.innerHTML = errorLocale[key].header;

        var helpLink = document.getElementById('linkHelp'), meta;

        if (helpLink) {
          helpLink.innerText = errorLocale[key]['help-url-text'];
        }

        meta = document.querySelector('[http-equiv="Content-Language"]');
        if (meta) {
          meta.setAttribute('content', key);
        }
      }
    });
  }
})(document, errorLocale);
