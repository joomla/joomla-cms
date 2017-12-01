/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	if (!Joomla) {
		throw new Error('Joomla API is missing!')
	}

	var options = Joomla.getOptions('categoryEdit');

	Joomla.jEditCategoryModal = function() {
		if (window.parent && document.formvalidator.isValid(document.getElementById("item-form"))) {
			return window.parent[options.name](document.getElementById("jform_title").value);
		}
	}
})();
