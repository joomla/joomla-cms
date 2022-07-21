/**
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Unobtrusive transformation for combobox
 *
 *
 * @package		Joomla.Framework
 * @subpackage	Forms
 */

(function($,document,undefined)
{
	var combobox = function(options, elem)
	{
		var self = {},

		init = function(options, elem)
		{
			self.$elem = $(elem);
			self.options = $.extend({}, $.fn.ComboTransform.options, options);
			self.$input = $(elem).find('input[type="text"]');
			self.$dropBtnDiv = $(elem).find('div.btn-group');
			self.$dropBtn = self.$dropBtnDiv.find('[type="button"]');
			self.$dropDown = $(elem).find('ul.dropdown-menu'),
			self.$dropDownOptions = self.$dropDown.find('li a');
			self.$dropDown.isEmpty = false;
			self.$dropBtn.isClicked = false;
			render();

			addEventHandlers();
		},

		render = function()
		{
			// Align dropdown correctly
			var inputWidth = self.$elem.width(),
				btnWidth = self.$dropBtnDiv.width(),
				totalWidth = inputWidth - 3,
				dropDownLeft = -inputWidth + btnWidth,
				dropDownWidth = self.$dropDown.width();

			dropDownWidth < totalWidth ? self.$dropDown.width(totalWidth+'px') : null;
			self.$dropDown.css('left',dropDownLeft+'px');
			self.$dropDown.css('max-height','150px');
			self.$dropDown.css('overflow-y','scroll');
			self.$dropDown.css('left',dropDownLeft+'px');
		},

		addEventHandlers = function()
		{
			self.$input.bind('focus', drop);
			self.$input.bind('blur', pick);

			if(self.options.updateList)
			{
				self.$input.bind('keyup', updateList);
			}

			self.$dropDown.on('mouseenter',function() {
				highlight('clear');
				self.$input.unbind('blur', pick);
			});
			self.$dropDown.on('mouseleave',function(event) {
				self.$input.bind('blur', pick);
			});

			self.$dropBtn.on('click', focusCombo);

			self.$dropDown.find('li').on('click', updateCombo);
			self.$dropDown.find('li a').on('mouseenter', function(){
				$(this).addClass('hover');
				self.$currHovered = $(this);
			});
			self.$dropDown.find('li a').on('mouseleave', function(){
				$(this).removeClass('hover');
			});
		},

		drop = function()
		{
			if(!self.$dropDown.isEmpty)
			{
				var dropDownHeight = self.$dropDown.height(),
					inputClientHeight = self.$input[0].clientHeight,
					inputHeight = self.$input.height(),
					dropDownTop = -(inputHeight + dropDownHeight);

				// Drop it in viewable area
				self.$dropDown.css('top','100%');

				self.$elem.addClass('nav-hover');
				self.$dropBtnDiv.addClass('open');

				if(!inViewport(self.$dropDown))
				{
					self.$dropDown.css('top',dropDownTop+'px');
				}

				// Prevent form submit on enter press
				self.$input.bind('keypress keydown keyup', preventSubmit);
			}
		},

		pick = function()
		{
			self.$elem.removeClass('nav-hover');
			self.$dropBtnDiv.removeClass('open');

			if(self.$dropBtn.isClicked)
			{
				self.$dropBtn.isClicked = false;
				self.$dropDown.isEmpty = true;
			}

			highlight('clear');

			self.$input.unbind('keypress keydown keyup', preventSubmit);
		},

		focusCombo = function()
		{
			var $options = self.$dropDownOptions;
			$options.show();
			self.$dropBtn.isClicked = self.$dropDown.isEmpty;
			self.$dropDown.isEmpty = false;
			self.$input.focus();
		},

		updateCombo = function(event)
		{
			var selectedOption = $(event.target).text();
			self.$input.val(selectedOption);
			pick();
			return false;
		},

		updateList = function(event)
		{
			var keycode = event && (event.keycode || event.which);
			keycode = event.ctrlKey || event.altKey ? -1 : keycode;

			if ((keycode > 47 && keycode < 59) || (keycode > 62 && keycode < 127) || keycode == 32  || keycode == 8)
			{
				var text = self.$input.val().toLowerCase(),
					$options = self.$dropDownOptions,
					hiddenOptions = 0,
					moveHilighter = false;
				$options.each(function()
				{
					 if(this.innerHTML.toLowerCase().indexOf(text) == 0)
					 {
					 	$(this).show();
					 }
					 else
					 {
					 	$(this).hide();
					 	if($(this).hasClass('hover'))
					 	{
					 		moveHilighter = true;
					 	}

					 	hiddenOptions++;
					 }
				});

				if(hiddenOptions == $options.length)
				{
					self.$dropDown.isEmpty = true;
					pick();
				}
				else
				{
					self.$dropDown.isEmpty = false;
					if(moveHilighter)
					{
						highlight("clear");
					}
					drop();
				}
			}
			else if(!self.$dropDown.isEmpty)
			{
				// Change selected option in list
				if(keycode == 38)
				{
					highlight("prev");
				}
				else if(keycode == 40)
				{
					highlight("next");
				}
				else if(keycode == 13 && self.$currHovered != null)
				{
					self.$input.val(self.$currHovered.html());
					pick();
				}
			}
		},

		highlight = function(newHighlight)
		{
			if(newHighlight == "next" || newHighlight == "prev")
			{
				var $visibleOptions = self.$dropDownOptions.filter(':visible'),
					$currHovered = $visibleOptions.filter('.hover'),
					index = $visibleOptions.index($currHovered),
					$optionToHover;

				// Change selected option in list
				if(newHighlight == "prev")
				{
					index = index == -1 ? $visibleOptions.length -1 :  index - 1;
				}
				else
				{
					index = index == $visibleOptions.length - 1 ? 0 : index + 1;
				}

				if($currHovered.length != 0)
				{
					$currHovered.removeClass('hover');
				}

				$optionToHover = $visibleOptions.eq(index);
				self.$currHovered = $optionToHover;
				self.$currHovered.addClass('hover');

				scrollTo(self.$dropDown, self.$currHovered);
			}
			else if(newHighlight == "clear")
			{
				self.$currHovered != null ? self.$currHovered.removeClass('hover') : null;
				self.$currHovered = null;
			}
		},

		// Helper functions
		inViewport = function(el)
		{
		    var rect = el[0].getBoundingClientRect();
		    return (
		        rect.top >= 0 &&
		        rect.left >= 0 &&
		        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
		        rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
		        );
		},

		scrollTo = function(p, e)
		{
			z = p[0].getBoundingClientRect();
			r = e[0].getBoundingClientRect();

			if(!(r.top >= z.top && r.left >= z.left && (r.top+r.height) <= (z.top+z.height)))
			{
				var value = r.top - z.top + p.scrollTop();
				p.scrollTop(value);
			}
		},

		preventSubmit = function(event)
		{
			if(event.keyCode == 13)
			{
				event.preventDefault();
			}
		};

		init(options, elem);
	};
	$.fn.ComboTransform = function(options)
	{
		return this.each(function(){
			combobox(options, this);
		});
	};

	$.fn.ComboTransform.options = {
		updateList : true
	};

	$(function()
	{
		$('div.combobox').ComboTransform({updateList : true});
	});
})(jQuery,document);
