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
				$toggleSidebarIcon = $('#j-toggle-sidebar-icon'),
				$toggleButtonWrapper = $('#j-toggle-button-wrapper'),
				$toggleButton = $('#j-toggle-sidebar-button'),
				$sidebarToggle = $('#j-toggle-sidebar');

			var openIcon = 'icon-arrow-left-2',
				closedIcon = 'icon-arrow-right-2';

			var $visible = $sidebarToggle.is(":visible");

			if (jQuery(document.querySelector("html")).attr('dir') == 'rtl')
			{
				openIcon = 'icon-arrow-right-2';
				closedIcon = 'icon-arrow-left-2';
			}

			var isComponent = $('body').hasClass('component');

			$sidebar.removeClass('span2').addClass('j-sidebar-container');
			$message.addClass('j-toggle-main');
			$main.addClass('j-toggle-main');
			if (!isComponent) {
				$debug.addClass('j-toggle-main');
			}

			var mainHeight = $main.outerHeight()+30,
				sidebarHeight = $sidebar.outerHeight(),
				bodyWidth = $('body').outerWidth(),
				sidebarWidth = $sidebar.outerWidth(),
				contentWidth = $('#content').outerWidth(),
				contentWidthRelative = contentWidth / bodyWidth * 100,
				mainWidthRelative = (contentWidth - sidebarWidth) / bodyWidth * 100;

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
				$toggleButtonWrapper.addClass('j-toggle-transition');
				$main.addClass('j-toggle-transition');
				if (!isComponent) {
					$debug.addClass('j-toggle-transition');
				}
			}

			if ($visible)
			{
				$sidebarToggle.hide();
				$sidebar.removeClass('j-sidebar-visible').addClass('j-sidebar-hidden');
				$toggleButtonWrapper.removeClass('j-toggle-visible').addClass('j-toggle-hidden');
				$toggleSidebarIcon.removeClass('j-toggle-visible').addClass('j-toggle-hidden');
				$message.removeClass('span10').addClass('span12');
				$main.removeClass('span10').addClass('span12 expanded');
				$toggleSidebarIcon.removeClass(openIcon).addClass(closedIcon);
				$toggleButton.attr( 'data-original-title', Joomla.JText._('JTOGGLE_SHOW_SIDEBAR') );
				if (!isComponent) {
					$debug.css( 'width', contentWidthRelative + '%' );
				}

				if (typeof(Storage) !== "undefined")
				{
					// Set the last selection in localStorage
					localStorage.setItem(context, true);
				}
			}
			else
			{
				$sidebarToggle.show();
				$sidebar.removeClass('j-sidebar-hidden').addClass('j-sidebar-visible');
				$toggleButtonWrapper.removeClass('j-toggle-hidden').addClass('j-toggle-visible');
				$toggleSidebarIcon.removeClass('j-toggle-hidden').addClass('j-toggle-visible');
				$message.removeClass('span12').addClass('span10');
				$main.removeClass('span12 expanded').addClass('span10');
				$toggleSidebarIcon.removeClass(closedIcon).addClass(openIcon);
				$toggleButton.attr( 'data-original-title', Joomla.JText._('JTOGGLE_HIDE_SIDEBAR') );

				if (!isComponent && bodyWidth > 768 && mainHeight < sidebarHeight)
				{
					$debug.css( 'width', mainWidthRelative + '%' );
				}
				else if (!isComponent)
				{
					$debug.css( 'width', contentWidthRelative + '%' );
				}

				if (typeof(Storage) !== "undefined")
				{
					// Set the last selection in localStorage
					localStorage.setItem( context, false );
				}
			}
		}
	});
})(jQuery);
