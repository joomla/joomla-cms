(function() {
	"use strict";
	if (errorLocale) {
		var header = document.getElementById('headerText'),
			text1 = document.getElementById('descText1');

		// Get the minimum PHP version
		window.phpVersion = document.body.getAttribute('data-php-version');

		// Create links for all the languages
		Object.keys(errorLocale).forEach(function(key) {
			var ul = document.getElementById('translatedLanguages'), li, aLink;
			li = document.createElement('li');
			aLink = document.createElement('a');
			aLink.setAttribute('href', '#');
			aLink.innerHTML = errorLocale[key].language;
			aLink.setAttribute('data-code', key);

			// Override click functionality for the link
			aLink.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				var ref = e.target.getAttribute('data-code');
				if (ref) {
					header.innerHTML = errorLocale[ref].header;
					text1.innerHTML = errorLocale[ref].text1.replace('{{phpversion}}', phpVersion);
				}

				var helpLink = document.getElementById('linkHelp');
				if (helpLink) {
					helpLink.href = errorLocale[ref]["help-url"];
					helpLink.innerText = errorLocale[ref]["help-url-text"];
				}
			});
			li.appendChild(aLink);
			ul.appendChild(li);
		});
	}
})();
