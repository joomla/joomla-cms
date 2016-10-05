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
		$('.radio.btn-group label').addClass('btn btn-outline-secondary');

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
						label.addClass('active btn-outline-primary');
					}
					else if (input.val() == 0)
					{
						label.addClass('active btn-outline-success');
					}
					else
					{
						label.addClass('active btn-outline-danger');
					}
				}
				else
				{
					if (input.val() == '')
					{
						label.addClass('active btn-outline-primary');
					}
					else if (input.val() == 0)
					{
						label.addClass('active btn-outline-danger');
					}
					else
					{
						label.addClass('active btn-outline-success');
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
					$('label[for=' + attrId + ']').addClass('active btn-outline-primary');
				}
				else if ($self.val() == 0)
				{
					$('label[for=' + attrId + ']').addClass('active btn-outline-success');
				}
				else
				{
					$('label[for=' + attrId + ']').addClass('active btn-outline-danger');
				}
			}
			else
			{
				if ($self.val() == '')
				{
					$('label[for=' + attrId + ']').addClass('active btn-outline-primary');
				}
				else if ($self.val() == 0)
				{
					$('label[for=' + attrId + ']').addClass('active btn-outline-danger');
				}
				else
				{
					$('label[for=' + attrId + ']').addClass('active btn-outline-success');
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
	});
})(jQuery);
