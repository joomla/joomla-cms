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

			var $sidebar = $('#j-sidebar-container'),
				$main = $('#j-main-container'),
				$message = $('#system-message-container'),
				$debug = $('#system-debug'),
				$toggle_sidebar_icon = $('#j-toggle-sidebar-icon'),
				$toggle_button_wrapper = $('#j-toggle-button-wrapper'),
				$toggle_button = $('#j-toggle-sidebar-button'),
				$sidebar_toggle = $('#j-toggle-sidebar');

			var open_icon = 'icon-arrow-left-2',
				closed_icon = 'icon-arrow-right-2';

			var $visible = $sidebar_toggle.is(":visible");

			if (jQuery(document.querySelector("html")).attr('dir') == 'rtl')
			{
				open_icon = 'icon-arrow-right-2';
				closed_icon = 'icon-arrow-left-2';
			}

			var isComponent = $('body').hasClass('component');

			$sidebar.removeClass('span2').addClass('j-sidebar-container');
			$message.addClass('j-toggle-main');
			$main.addClass('j-toggle-main');
			if (!isComponent) {
				$debug.addClass('j-toggle-main');
			}

			var main_height = $main.outerHeight()+30,
				sidebar_height = $sidebar.outerHeight(),
				body_width = $('body').outerWidth(),
				sidebar_width = $sidebar.outerWidth(),
				content_width = $('#content').outerWidth(),
				this_content = content_width / body_width * 100,
				this_main = (content_width - sidebar_width) / body_width * 100;

			if (force)
			{
				// Load the value from localStorage
				if (typeof(Storage) !== "undefined")
				{
					$visible = localStorage.getItem(context);
				}

				// Need to convert the value to a boolean
				$visible = ($visible == 'true');
			}
			else
			{
				$message.addClass('j-toggle-transition');
				$sidebar.addClass('j-toggle-transition');
				$toggle_button_wrapper.addClass('j-toggle-transition');
				$main.addClass('j-toggle-transition');
				if (!isComponent) {
					$debug.addClass('j-toggle-transition');
				}
			}

			if ($visible)
			{
				$sidebar_toggle.hide();
				$sidebar.removeClass('j-sidebar-visible').addClass('j-sidebar-hidden');
				$toggle_button_wrapper.removeClass('j-toggle-visible').addClass('j-toggle-hidden');
				$toggle_sidebar_icon.removeClass('j-toggle-visible').addClass('j-toggle-hidden');
				$message.removeClass('span10').addClass('span12');
				$main.removeClass('span10').addClass('span12 expanded');
				$toggle_sidebar_icon.removeClass(open_icon).addClass(closed_icon);
				$toggle_button.attr('data-original-title', Joomla.JText._('JTOGGLE_SHOW_SIDEBAR'));
				if (!isComponent) {
					$debug.css('width', this_content + '%');
				}

				if (typeof(Storage) !== "undefined")
				{
					// Set the last selection in localStorage
					localStorage.setItem(context, true);
				}
			}
			else
			{
				$sidebar_toggle.show();
				$sidebar.removeClass('j-sidebar-hidden').addClass('j-sidebar-visible');
				$toggle_button_wrapper.removeClass('j-toggle-hidden').addClass('j-toggle-visible');
				$toggle_sidebar_icon.removeClass('j-toggle-hidden').addClass('j-toggle-visible');
				$message.removeClass('span12').addClass('span10');
				$main.removeClass('span12 expanded').addClass('span10');
				$toggle_sidebar_icon.removeClass(closed_icon).addClass(open_icon);
				$toggle_button.attr('data-original-title', Joomla.JText._('JTOGGLE_HIDE_SIDEBAR'));

				if (!isComponent && body_width > 768 && main_height < sidebar_height)
				{
					$debug.css('width', this_main+'%');
				}
				else if (!isComponent)
				{
					$debug.css('width', this_content+'%');
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
