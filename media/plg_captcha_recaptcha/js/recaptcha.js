/**
 * @package		Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
window.JoomlaSubmitReCaptchaInvisible = function(token) {
	'use strict';
	
	window.sentForm.submit();
	delete  window.sentForm;
}

window.JoomlaInitReCaptchaInvisible = function() {
	'use strict';

	var items = document.getElementsByClassName('g-recaptcha'), item, options, element;
	for (var i = 0, l = items.length; i < l; i++) {
		item = items[i];
		options = item.dataset ? item.dataset : {
			sitekey: item.getAttribute('data-sitekey'),
			callback : item.getAttribute('data-callback'),
			badge : item.getAttribute('data-badge'),
		};
		var widgetId = grecaptcha.render(item, options);
		if (widgetId !== '') {
			grecaptcha.reset(widgetId);
			element = item; 
			do {
				element = element.parentNode;
			}
			while (element.nodeName != 'FORM');
			element.addEventListener('submit', function(event) {
				event.preventDefault();
				grecaptcha.execute(widgetId);
				window.sentForm = this;
			});
		}
	}
}

window.JoomlaInitReCaptcha2 = function() {
	'use strict';

	var items = document.getElementsByClassName('g-recaptcha'), item, options;
	for (var i = 0, l = items.length; i < l; i++) {
		item = items[i];
		options = item.dataset ? item.dataset : {
			sitekey: item.getAttribute('data-sitekey'),
			theme:   item.getAttribute('data-theme'),
			size:    item.getAttribute('data-size')
		};
		grecaptcha.render(item, options);
	}
}
