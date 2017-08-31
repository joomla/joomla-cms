/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function() {
	'use strict';

	Joomla.updateBannerFields = function(value) {
		var imgWrapper = document.getElementById('image'),
			custom     = document.getElementById('custom');

		switch (value) {
			case '0':
				// Image
				imgWrapper.style.display = 'block';
				custom.style.display = 'none';
				break;
			case '1':
				// Custom
				imgWrapper.style.display = 'none';
				custom.style.display = 'block';
				break;
		}
	}

	document.addEventListener('DOMContentLoaded', function(){

		var jformType = document.getElementById('jform_type');

		if (jformType)
		{
			// Hide/show parameters initially
			Joomla.updateBannerFields(jformType.value);

			// Hide/show parameters when the type has been selected
			jformType.addEventListener('change', function(event) {
				var value = typeof(params) !== 'object' ? jformType.value : params.selected;

				Joomla.updateBannerFields(value);
			});
		}

	});

})();
