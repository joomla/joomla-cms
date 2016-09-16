/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

(function($)
{
	$(document).ready(function()
	{
		var $w = $(window);

		/**
		 * Bootstrap tooltips
		 */
		$('*[rel=tooltip]').tooltip({
			html: true
		});


		/**
		 * Sidebar
		 */
		var sidebar = $('#sidebar-wrapper');
		var wrapper = $('#wrapper');
		var menu    = sidebar.find('#menu');

		// Apply 2nd level collapse
		menu.find('.nav > .parent > ul')
			.removeClass('collapse-level-1')
			.addClass('collapse-level-2');
		menu.find('.nav > .parent > a')
			.removeAttr('data-parent');

		function animateWrapper()
		{
			var logo       = $('#main-brand');
			var menuToggle = $('#header').find('.menu-toggle');
			var isClosed   = wrapper.hasClass('closed');

			if (isClosed)
			{
				logo.stop(true, false).fadeIn();
				wrapper.removeClass('closed');
				menuToggle.removeClass('active');
				isClosed = false;
			}
			else
			{
				sidebar.find('.collapse').removeClass('in');
				sidebar.find('.collapse-arrow').addClass('collapsed');
				menuToggle.addClass('active');
				logo.stop(true, false).fadeOut();
				wrapper.addClass('closed');
				isClosed = true;
			}
		}
		
		// Toggle menu
		$('#menu-collapse').on('click', function(e) {
			e.preventDefault();
			animateWrapper();
		});

		$(document).on('click', "#wrapper.closed .sidebar-wrapper [data-toggle='collapse']", function () {		
			if (wrapper.hasClass('closed') && $(window).width() > 767)
			{
				animateWrapper();
			}
		});

		// Set the height of the menu to prevent overlapping
		function setMenuHeight()
		{
			var height = $('#header').height() + $('#main-brand').outerHeight();
			$('#menu').height( $(window).height() - height );
		}
		setMenuHeight();

		// Remove 'closed' class on resize
		$(window).on('resize', function() {
			if (wrapper.hasClass('closed'))
			{
				animateWrapper();
			}
			setMenuHeight();
		});
		
		/**
		 * Localstorage to remember which menu item was clicked on
		 */
		menu.find('a').on('click', function(){

			var href = $(this).attr('href');

			if (typeof(Storage) !== 'undefined')
			{
				// Set the last selection in localStorage
				localStorage.setItem('href', href);
			}

		});
		
		// Auto expand
		if (typeof(Storage) !== 'undefined')
		{
			var wLocationpath   = window.location.pathname;
			var wLocationSearch = window.location.search;

			if ((wLocationpath !== '/administrator/' || wLocationpath !== '/administrator/index.php') && wLocationSearch == '')
			{
				localStorage.setItem('href', false);
			}

			var localItem       = menu.find('a[href="' + localStorage.getItem('href') + '"]');
			var localitemParent = localItem.parents('.parent').find('a')[0];

			if (typeof(localitemParent) !== 'undefined')
			{
				localitemParent.click();
			}
		}


		/**
		 * Turn radios into btn-group
		 */
		$('.radio.btn-group label').addClass('btn btn-secondary-outline');

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
						label.addClass('active btn-primary-outline');
					}
					else if (input.val() == 0)
					{
						label.addClass('active btn-success-outline');
					}
					else
					{
						label.addClass('active btn-danger-outline');
					}
				}
				else
				{
					if (input.val() == '')
					{
						label.addClass('active btn-primary-outline');
					}
					else if (input.val() == 0)
					{
						label.addClass('active btn-danger-outline');
					}
					else
					{
						label.addClass('active btn-success-outline');
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
					$('label[for=' + attrId + ']').addClass('active btn-primary-outline');
				}
				else if ($self.val() == 0)
				{
					$('label[for=' + attrId + ']').addClass('active btn-success-outline');
				}
				else
				{
					$('label[for=' + attrId + ']').addClass('active btn-danger-outline');
				}
			}
			else
			{
				if ($self.val() == '')
				{
					$('label[for=' + attrId + ']').addClass('active btn-primary-outline');
				}
				else if ($self.val() == 0)
				{
					$('label[for=' + attrId + ']').addClass('active btn-danger-outline');
				}
				else
				{
					$('label[for=' + attrId + ']').addClass('active btn-success-outline');
				}
			}
		});


		/**
		 * Add color classes to chosen field based on value
		 */
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
		 * Sticky Toolbar
		 */
		var navTop;
		var isFixed = false;

		processScrollInit();
		processScroll();

		$(window).on('resize', processScrollInit);
		$(window).on('scroll', processScroll);

		function processScrollInit()
		{
			var subhead = $('#subhead');

			if (subhead.length)
			{
				navTop = subhead.length && $('.subhead').offset().top - 50;

				// Only apply the scrollspy when the toolbar is not collapsed
				if (document.body.clientWidth > 480)
				{
					$('.subhead-collapse').height($('.subhead').outerHeight());
					subhead.css('width', 'auto');
					
					subhead.scrollspy({
						offset: subhead.height() + $('.navbar').height()
					});
				}
			}
		}

		function processScroll()
		{
			var subhead = $('#subhead');

			if (subhead.length) 
			{
				var scrollTop = $(window).scrollTop();

				if (scrollTop >= navTop && !isFixed)
				{
					isFixed = true;
					subhead.addClass('subhead-fixed')
					subhead.css('width', $('.container-main').width());
				}
				else if (scrollTop <= navTop && isFixed)
				{
					isFixed = false;
					subhead.removeClass('subhead-fixed');
				}
			}
		}


		/**
		 * All list views to hide/show the sidebar
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

			$sidebar.removeClass('col-md-2').addClass('j-sidebar-container');
			$message.addClass('pull-xs-right');
			
			if (!isComponent) 
			{
				$debug.addClass('pull-xs-right');
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
				
				if (!isComponent) 
				{
					$debug.addClass('j-toggle-transition');
				}
			}

			if ($visible)
			{
				$sidebarToggle.hide();
				$sidebar.removeClass('j-sidebar-visible').addClass('j-sidebar-hidden');
				$toggleButtonWrapper.removeClass('j-toggle-visible').addClass('j-toggle-hidden');
				$toggleSidebarIcon.removeClass('j-toggle-visible').addClass('j-toggle-hidden');
				$message.removeClass('col-md-10').addClass('col-md-12');
				$main.removeClass('col-md-10 pull-xs-right').addClass('col-md-12 expanded');
				$toggleSidebarIcon.removeClass(openIcon).addClass(closedIcon);
				$toggleButton.attr( 'data-original-title', Joomla.JText._('JTOGGLE_SHOW_SIDEBAR') );
				$sidebar.attr('aria-hidden', true);
				$sidebar.find('a').attr('tabindex', '-1');
				$sidebar.find(':input').attr('tabindex', '-1');

				if (!isComponent) 
				{
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
				$message.removeClass('col-md-12').addClass('col-md-10');
				$main.removeClass('col-md-12 expanded').addClass('pull-xs-right col-md-10');
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
