(function() {
	"use strict";
	if (errorLocale) {
		var header = document.getElementById('headerText'),
			text1 = document.getElementById('descText1');

		// Get the minimum PHP version
		window.phpVersion = document.body.getAttribute('data-php-version') || '5.5.9';

		// Create links for all the languages
		Object.keys(errorLocale).forEach(function(key) {
			var sel = document.getElementById('translatedLanguagesSelect');
			var opt = document.createElement('option');
			opt.text = errorLocale[key].language;
			opt.value = key;

			if (key === 'en-GB') {
				opt.setAttribute('selected', 'selected');
			}

			document.getElementById('translatedLanguagesSelect').addEventListener('change', function(e) {
				var ref = e.target.value;
				if (ref) {
					header.innerHTML = errorLocale[ref].header;
					text1.innerHTML = errorLocale[ref].text1.replace('{{phpversion}}', phpVersion);
				}

				var helpLink = document.getElementById('linkHelp');
				if (helpLink) {
					helpLink.innerText = errorLocale[ref]["help-url-text"];
				}

				var meta = document.querySelector('[http-equiv="Content-Language"]');
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
				document.querySelector("#translatedLanguagesSelect option[value='" + key + "']").setAttribute('selected', 'selected');

				// Append the translated strings
				header.innerHTML = errorLocale[key].header;
				text1.innerHTML = errorLocale[key].text1.replace('{{phpversion}}', phpVersion);

				var helpLink = document.getElementById('linkHelp');
				if (helpLink) {
					helpLink.innerText = errorLocale[key]["help-url-text"];
				}

				var meta = document.querySelector('[http-equiv="Content-Language"]');
				if (meta) {
					meta.setAttribute('content', key);
				}
			}
		});
	}
})();
