/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		var forms, awesompletes = [], searchword = document.querySelectorAll('.js-finder-search-query');

		for (var i = 0; i < searchword.length; i++) {
			// Handle the auto suggestion
			if (Joomla.getOptions('finder-search')) {

				searchword[i].awesomplete = new Awesomplete(searchword[i]);

				// If the current value is empty, set the previous value.
				searchword[i].addEventListener('keyup', function (event) {
					if (event.target.value.length > 1) {

						event.target.awesomplete.list = [];

						Joomla.request(
							{
								url:    Joomla.getOptions('finder-search').url+ '&q=' + event.target.value,
								method: 'GET',
								data:    { q: event.target.value },
								perform: true,
								headers: {'Content-Type': 'application/x-www-form-urlencoded'},
								onSuccess: function(response, xhr)
								{console.log(xhr);
									response = JSON.parse(response);
									if (Object.prototype.toString.call(response.suggestions) === '[object Array]') {
										event.target.awesomplete.list = response.suggestions;
									}
								},
								onError: function(xhr)
								{
									if (xhr.status > 0)
									{
										Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
									}
								}
							}
						);
					}
				});
			}
		}

		forms = document.querySelectorAll('.js-finder-searchform');

		for (var i = 0; i < forms.length; i++) {
			forms[i].addEventListener('submit', function(event) {
				event.stopPropagation();
				var advanced = event.target.querySelector('.js-finder-advanced');

				// Disable select boxes with no value selected.
				if (advanced.length) {
					var fields = advanced.querySelector('select');

					for (var j = 0; j < fields.length; j++) {
						if (!fields[j].value) {
							fields[j].setAttribute('disabled', 'disabled');
						}
					}
				}
			})
		}

	});

})();
