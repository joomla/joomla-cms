/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla.submitbutton = function(task)
{
	if (task == "article.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
	{
		var form = document.getElementById("item-form");
		jQuery("#permissions-sliders select").attr("disabled", "disabled");
		new Function(form.getAttribute('data-callback'));
		Joomla.submitform(task, document.getElementById("item-form"));

		if (task !== "article.apply")
		{
			window.parent.jQuery("#articleEdit" +form.getAttribute('data-article-id') + "Modal").modal("hide");
		}
	}
};
