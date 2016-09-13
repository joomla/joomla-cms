/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       3.0
 */

(function($)
{
	$(document).ready(function()
	{
		var $w = $(window);

		$('*[rel=tooltip]').tooltip();

		// Turn radios into btn-group
		$('.radio.btn-group label').addClass('btn');

		$('.btn-group label:not(.active)').click(function()
		{
			var label = $(this);
			var input = $('#' + label.attr('for'));

			if (!input.prop('checked'))
			{
				label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');

				if (label.closest('.btn-group').hasClass('btn-group-reversed'))
				{
					if (input.val() == '')
					{
						label.addClass('active btn-primary');
					}
					else if (input.val() == 0)
					{
						label.addClass('active btn-success');
					}
					else
					{
						label.addClass('active btn-danger');
					}
				}
				else
				{
					if (input.val() == '')
					{
						label.addClass('active btn-primary');
					}
					else if (input.val() == 0)
					{
						label.addClass('active btn-danger');
					}
					else
					{
						label.addClass('active btn-success');
					}

				}
				input.prop('checked', true);
				input.trigger('change');
			}
		});
		$('.btn-group input[checked=checked]').each(function()
		{
			var $self  = $(this);
			var attrId = $self.attr('id');

			if ($self.parent().hasClass('btn-group-reversed'))
			{
				if ($self.val() == '')
				{
					$('label[for=' + attrId + ']').addClass('active btn-primary');
				}
				else if ($self.val() == 0)
				{
					$('label[for=' + attrId + ']').addClass('active btn-success');
				}
				else
				{
					$('label[for=' + attrId + ']').addClass('active btn-danger');
				}
			}
			else
			{
				if ($self.val() == '')
				{
					$('label[for=' + attrId + ']').addClass('active btn-primary');
				}
				else if ($self.val() == 0)
				{
					$('label[for=' + attrId + ']').addClass('active btn-danger');
				}
				else
				{
					$('label[for=' + attrId + ']').addClass('active btn-success');
				}
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
		 * Append submenu items to empty UL on hover allowing a scrollable dropdown
		 */
		if ($w.width() > 767)
		{
			var menuScroll = $('#menu > li > ul'),
				emptyMenu  = $('#nav-empty');

			$('#menu > li').on('click mouseenter', function() {

				// Set max-height (and width if scroll) for dropdown menu, depending of window height
				var $dropdownMenu    = $(this).children('ul'),
					windowHeight     = $w.height(),
					linkHeight       = $(this).outerHeight(true),
					statusHeight     = $('#status').outerHeight(true),
					menuHeight       = $dropdownMenu.height(),
					menuOuterHeight  = $dropdownMenu.outerHeight(true),
					scrollMenuWidth  = $dropdownMenu.width() + 15,
					maxHeight        = windowHeight - (linkHeight + statusHeight + (menuOuterHeight - menuHeight) + 20);

				if (maxHeight < menuHeight)
				{
					$dropdownMenu.css('width', scrollMenuWidth);
				}
				else if (maxHeight > menuHeight)
				{
					$dropdownMenu.css('width', 'auto');
				}

				$dropdownMenu.css('max-height', maxHeight);

				// Get the submenu position
				linkWidth        = $(this).outerWidth(true);
				menuWidth        = $dropdownMenu.width();
				linkPaddingLeft  = $(this).children('a').css('padding-left');
				offsetLeft       = Math.round($(this).offset().left) - parseInt(linkPaddingLeft);

				emptyMenu.empty().hide();

			});

			menuScroll.find('.dropdown-submenu > a').on('mouseover', function() {

				var $self           = $(this),
					dropdown        = $self.next('ul'),
					submenuWidth    = dropdown.outerWidth(),
					offsetTop       = $self.offset().top,
					linkPaddingTop  = parseInt(dropdown.css('padding-top')) + parseInt($(this).css('padding-top')),
					scroll          = $w.scrollTop() + linkPaddingTop;

				// Set the submenu position
				if ($('html').attr('dir') == 'rtl')
				{
					emptyMenu.css({
						top : offsetTop - scroll,
						left: offsetLeft - (menuWidth - linkWidth) - submenuWidth
					});
				}
				else
				{
					emptyMenu.css({
						top : offsetTop - scroll,
						left: offsetLeft + menuWidth
					});
				}

				// Append items to empty <ul> and show it
				dropdown.hide();
				emptyMenu.show().html(dropdown.html());

				// Check if the full element is visible. If not, adjust the position
				if (emptyMenu.Jvisible() !== true)
				{
					emptyMenu.css({
						top : ($w.height() - emptyMenu.outerHeight()) - $('#status').height()
					});
				}

			});

			menuScroll.find('a.no-dropdown').on('mouseenter', function() {

				emptyMenu.empty().hide();

			});

			// obtain a reference to the original handler
			var _clearMenus = $._data(document, 'events').click.filter(function (el) {
				return el.namespace === 'data-api.dropdown' && el.selector === undefined
			})[0].handler;

			// disable the old listener
			$(document)
				.off('click.data-api.dropdown', _clearMenus)
				.on('click.data-api.dropdown', function(e) {
					e.button === 2 || _clearMenus();

					if (!$('#menu').find('> li').hasClass('open'))
					{
						emptyMenu.empty().hide();
					}
				});

			$.fn.Jvisible = function(partial,hidden)
			{
				if (this.length < 1)
				{
					return;
				}

				var $t = this.length > 1 ? this.eq(0) : this,
					t  = $t.get(0)

				var viewTop         = $w.scrollTop(),
					viewBottom      = (viewTop + $w.height()) - $('#status').height(),
					offset          = $t.offset(),
					_top            = offset.top,
					_bottom         = _top + $t.height(),
					compareTop      = partial === true ? _bottom : _top,
					compareBottom   = partial === true ? _top : _bottom;

				return !!t.offsetWidth * t.offsetHeight && ((compareBottom <= viewBottom) && (compareTop >= viewTop));
			};

		}


		/**
		 * USED IN: All views with toolbar and sticky bar enabled
		 */
		var navTop;
		var isFixed = false;


		if (document.getElementById('isisJsData') && document.getElementById('isisJsData').getAttribute('data-tmpl-sticky') == "true") {
			processScrollInit();
			processScroll();

			$(window).on('resize', processScrollInit);
			$(window).on('scroll', processScroll);
		}

		function processScrollInit() {
			if ($('.subhead').length) {
				navTop = $('.subhead').length && $('.subhead').offset().top - parseInt(document.getElementById('isisJsData').getAttribute('data-tmpl-offset'));

				// Fix the container top
				$(".container-main").css("top", $('.subhead').height() + $('nav.navbar').height());

				// Only apply the scrollspy when the toolbar is not collapsed
				if (document.body.clientWidth > 480) {
					$('.subhead-collapse').height($('.subhead').height());
					$('.subhead').scrollspy({offset: {top: $('.subhead').offset().top - $('nav.navbar').height()}});
				}
			}
		}

		function processScroll() {
			if ($('.subhead').length) {
				var scrollTop = $(window).scrollTop();
				if (scrollTop >= navTop && !isFixed) {
					isFixed = true;
					$('.subhead').addClass('subhead-fixed');

					// Fix the container top
					$(".container-main").css("top", $('.subhead').height() + $('nav.navbar').height());
				} else if (scrollTop <= navTop && isFixed) {
					isFixed = false;
					$('.subhead').removeClass('subhead-fixed');
				}
			}
		}

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
				$sidebar.attr('aria-hidden', true);
				$sidebar.find('a').attr('tabindex', '-1');
				$sidebar.find(':input').attr('tabindex', '-1');

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
				$sidebar.removeAttr('aria-hidden');
				$sidebar.find('a').removeAttr('tabindex');
				$sidebar.find(':input').removeAttr('tabindex');

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
