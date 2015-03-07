/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       3.2
 */

(function($, Joomla)
{
	Joomla.Behavior.add('template.protostar', 'ready update', function(event)
	{
		var $target = $(event.target);

		$target.find('*[rel=tooltip]').tooltip()

		// Turn radios into btn-group
		$target.find('.radio.btn-group label').addClass('btn');
		$target.find('.btn-group label').not('.template-protostar-radio')
			.addClass('template-protostar-radio').on('click.templateProtostarRadio', function()
		{
			var label = $(this);
			var input = $('#' + label.attr('for'));

			if (!input.prop('checked')) {
				label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				if (input.val() == '') {
					label.addClass('active btn-primary');
				} else if (input.val() == 0) {
					label.addClass('active btn-danger');
				} else {
					label.addClass('active btn-success');
				}
				input.prop('checked', true);
				input.trigger('change');
			}
		});

		$target.find(".btn-group input:checked").each(function()
		{
			if ($(this).val() == '') {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
			} else if ($(this).val() == 0) {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-danger');
			} else {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-success');
			}
		});
	});

	Joomla.Behavior.add('template.protostar', 'remove', function(event)
	{
		var $target = $(event.target);
		$target.find('label.template-protostar-radio').removeClass('template-protostar-radio').off('click.templateProtostarRadio');
	});

})(jQuery, Joomla);
