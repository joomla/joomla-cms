/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	"use strict";

	jQuery(document).ready(function ($) {
		var propagate = function () {
			var $this = $(this);
			var sub   = $this.closest('li').find('.treeselect-sub [type="checkbox"]');
			sub.prop('checked', this.checked);
			if ($this.val() == 1)
				sub.each(propagate);
			else
				sub.attr('disabled', this.checked ? 'disabled' : null);
		};
		$('.treeselect')
			.on('click', '[type="checkbox"]', propagate)
			.find('[type="checkbox"]:checked').each(propagate);
	});
})();
