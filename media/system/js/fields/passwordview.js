/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field switcher
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		var passwordInputs = document.getElementsByTagName('input');

		for (var i = 0, l = passwordInputs.length; i < l; i++) {

			if (passwordInputs[i].getAttribute('type') === 'password') {

				var inputGroup = passwordInputs[i].parentNode.querySelector('.input-group-addon');

				if (inputGroup) {
					inputGroup.addEventListener('click', function(e) {

						var target = this.querySelector('.fa'),
							input  = this.parentNode.querySelector('input'),
							srText = target.nextElementSibling;

						if (target.classList.contains('fa-eye')) {
							// Update the icon class
							target.classList.remove('fa-eye');
							target.classList.add('fa-eye-slash');

							// Update the input type
							input.type = 'text';

							// Updat the text for screenreaders
							srText.innerText = Joomla.JText._('JSHOW');
						}
						else {
							// Update the icon class
							target.classList.add('fa-eye');
							target.classList.remove('fa-eye-slash');

							// Update the input type
							input.type = 'password';

							// Updat the text for screenreaders
							srText.innerText = Joomla.JText._('JHIDE');
						}
					});
				}

			}

		}

	});

})();
