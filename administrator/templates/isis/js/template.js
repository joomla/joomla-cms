/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       3.0
 */

(function($)
{
	$(document).ready(function()
	{
		$('*[rel=tooltip]').tooltip();

		// Turn radios into btn-group
		$('.radio.btn-group label').addClass('btn');
		$('.btn-group label:not(.active)').click(function()
		{
			var label = $(this);
			var input = $('#' + label.attr('for'));

			if (!input.prop('checked')) {
				label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
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
		$('.btn-group input[checked=checked]').each(function()
		{
			if ($(this).val() == '') {
				$('label[for=' + $(this).attr('id') + ']').addClass('active btn-primary');
			} else if ($(this).val() == 0) {
				$('label[for=' + $(this).attr('id') + ']').addClass('active btn-danger');
			} else {
				$('label[for=' + $(this).attr('id') + ']').addClass('active btn-success');
			}
		});
		// add color classes to chosen field based on value
		$('select[class^="chzn-color"], select[class*=" chzn-color"]').on('liszt:ready', function(){
			var select = $(this);
			var cls = this.className.replace(/^.(chzn-color[a-z0-9-_]*)$.*/, '\1');
			var container = select.next('.chzn-container').find('.chzn-single');
			container.addClass(cls).attr('rel', 'value_' + select.val());
			select.on('change click', function()
			{
				container.attr('rel', 'value_' + select.val());
			});

		});

		/**
		 * USED IN: All list views to hide/show the sidebar
		 */
		window.toggleSidebar = function(force)
		{
			var context = 'jsidebar';

			var $visible = $('#j-toggle-sidebar').is(":visible");

			var $sidebar = $('#j-sidebar-container');

			var open_icon = 'icon-cancel';
			var closed_icon = 'icon-arrow-right-2';

			if (jQuery(document.querySelector("html")).attr('dir') == 'rtl')
			{
				open_icon = 'icon-cancel';
				closed_icon = 'icon-arrow-left-2';
			}

			var main_height = $('#j-main-container').outerHeight()+30;
			var sidebar_height = $('#j-sidebar-container').outerHeight();

			var body_width = $('body').outerWidth();
			var sidebar_width = $sidebar.outerWidth();
			var content_width = $('#content').outerWidth();
			var isComponent = $('body').hasClass('component');
			var this_content = content_width / body_width * 100;
			var this_main = (content_width - sidebar_width) / body_width * 100;

			$('#j-sidebar-container').removeClass('span2').addClass('j-sidebar-container');
			$('#system-message-container').addClass('j-toggle-main');
			$('#j-main-container').addClass('j-toggle-main');
			if (!isComponent) {
				$('#system-debug').addClass('j-toggle-main');
			}

			if (force)
			{
				// Load the value from localStorage
				if (typeof(Storage) !== "undefined")
				{
					var $visible = localStorage.getItem(context);
				}

				// Need to convert the value to a boolean
				$visible = ($visible == 'true') ? true : false;
			}
			else
			{
				$('#system-message-container').addClass('j-toggle-transition');
				$('#j-sidebar-container').addClass('j-toggle-transition');
				$('#j-toggle-button-wrapper').addClass('j-toggle-transition');
				$('#j-main-container').addClass('j-toggle-transition');
				if (!isComponent) {
					$('#system-debug').addClass('j-toggle-transition');
				}
			}

			if ($visible)
			{
				$('#j-toggle-sidebar').hide();
				$('#j-sidebar-container').removeClass('j-sidebar-visible').addClass('j-sidebar-hidden');
				$('#j-toggle-button-wrapper').removeClass('j-toggle-visible').addClass('j-toggle-hidden');
				$('#j-toggle-sidebar-icon').removeClass('j-toggle-visible').addClass('j-toggle-hidden');
				$('#system-message-container').removeClass('span10').addClass('span12');
				$('#j-main-container').removeClass('span10').addClass('span12 expanded');
				$('#j-toggle-sidebar-icon').removeClass(open_icon).addClass(closed_icon);
				$('#j-toggle-sidebar-button').attr('data-original-title', Joomla.JText._('JTOGGLE_SHOW_SIDEBAR'));
				if (!isComponent) {
					$('#system-debug').css('width', this_content + '%');
				}

				if (typeof(Storage) !== "undefined")
				{
					// Set the last selection in localStorage
					localStorage.setItem(context, true);
				}
			}
			else
			{
				$('#j-toggle-sidebar').show();
				$('#j-sidebar-container').removeClass('j-sidebar-hidden').addClass('j-sidebar-visible');
				$('#j-toggle-button-wrapper').removeClass('j-toggle-hidden').addClass('j-toggle-visible');
				$('#j-toggle-sidebar-icon').removeClass('j-toggle-hidden').addClass('j-toggle-visible');
				$('#system-message-container').removeClass('span12').addClass('span10');
				$('#j-main-container').removeClass('span12 expanded').addClass('span10');
				$('#j-toggle-sidebar-icon').removeClass(closed_icon).addClass(open_icon);
				$('#j-toggle-sidebar-button').attr('data-original-title', Joomla.JText._('JTOGGLE_HIDE_SIDEBAR'));

				if (!isComponent && body_width > 768 && main_height < sidebar_height)
				{
					$('#system-debug').css('width', this_main+'%');
				}
				else if (!isComponent)
				{
					$('#system-debug').css('width', this_content+'%');
				}

				if (typeof(Storage) !== "undefined")
				{
					// Set the last selection in localStorage
					localStorage.setItem(context, false);
				}
			}
		}
	});
})(jQuery);
