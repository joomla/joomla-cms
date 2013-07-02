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

(function($)
{
	$(document).ready(function()
	{
		$('a.email_address').each(function(e, el)
		{
			var pre = '';
			var post = '';
			$(el).find('.cloaked_email span').each(function(e, el)
			{
				pre += $(el).attr('data-content-pre');
				post = $(el).attr('data-content-post') + post;
			});
			$(el).attr('href', 'mailto:' + pre + post);
		});
	});
})(jQuery);
