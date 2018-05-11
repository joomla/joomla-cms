/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(Joomla) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		Joomla.twoFactorMethodChange = function(e) {
			var method = document.getElementById('jform_twofactor_method');

			if (method) {
				var selectedPane   = 'com_users_twofactor_' + method.value,
				    twoFactorForms = document.querySelectorAll('#com_users_twofactor_forms_container > div');

				for (var i = 0; i < twoFactorForms.length; i++) {
					var id = twoFactorForms[i].id;

					if (id != selectedPane)
					{
						document.getElementById(id).style.display = 'none';
					}
					else
					{
						document.getElementById(id).style.display = 'block';
					}
				}
			}
		};

	});

})(Joomla);
