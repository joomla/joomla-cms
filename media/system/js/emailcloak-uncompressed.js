/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * emailcloak javascript behavior
 *
 * Used for triggering cloaked emails
 *
 * @package  Joomla
 * @since    3.2
 */

function triggerEmail(id) {
	(function($)
	{
		var pre = '';
		var post = '';
		$('#'+id +' span').each(function(e, el) {
			pre += $(el).attr('data-content-pre');
			post = $(el).attr('data-content-post') + post;
		});
		var email = pre + post;
		window.location.href = 'mailto:' + email;
	})(jQuery);
}
