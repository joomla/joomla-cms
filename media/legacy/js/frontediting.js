/**
 * @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to add front-end hover edit icons with tooltips for modules and menu items.
 */
(function($) {

	$(document).ready(function () {

		// Modules edit icons:
		$('.jmoddiv').on({
			mouseenter: function() {
				// Get module editing URL and tooltip for module edit:
				var self = $(this);
				var moduleEditUrl = self.data('jmodediturl');
				var moduleTip = self.data('jmodtip');
                var moduleTarget = self.data('target');

				// Stop timeout on previous tooltip and remove it:
				$('btn.jmodedit').tooltip('dispose').remove();

				// Add editing button with tooltip:
				self.addClass('jmodinside')
					.prepend('<a class="btn btn-link jmodedit" href="#" target="' + moduleTarget + '"><span class="fa fa-edit"></span></a>')
					.children(":first").attr('href', moduleEditUrl).attr('title', moduleTip)
					.tooltip({container: $('.jmodedit').parent(), html: true, placement: 'auto'});
			},
			mouseleave: function() {
				// Delay remove editing button if not hovering it:
				$('.btn.jmodedit').remove();
				$('.tooltip').tooltip('dispose');
			}
		});

		// Menu items edit icons:
		var activePopover = null;

		$('.jmoddiv[data-jmenuedittip] .nav li,.jmoddiv[data-jmenuedittip].nav li,.jmoddiv[data-jmenuedittip] .nav .nav-child li,.jmoddiv[data-jmenuedittip].nav .nav-child li').on({
			mouseenter: function() {

				// Get menu ItemId from the item-nnn class of the li element of the menu:
				var itemids = /\bitem-(\d+)\b/.exec($(this).attr('class'));
				if (typeof itemids[1] == 'string') {
					// Find module editing URL from enclosing module:
					var enclosingModuleDiv = $(this).closest('.jmoddiv');
					var moduleEditUrl = enclosingModuleDiv.data('jmodediturl');
					// Transform module editing URL into Menu Item editing url:
					var menuitemEditUrl = moduleEditUrl.replace(/\/index.php\?option=com_config&view=modules([^\d]+).+$/, '/administrator/index.php?option=com_menus&view=item&layout=edit$1' + itemids[1]);

				}

				// Get tooltip for menu items from enclosing module
				var menuEditTip = enclosingModuleDiv.data('jmenuedittip').replace('%s', itemids[1]);

				var content = $('<div><a class="btn jfedit-menu" href="#" target="_blank"><span class="fa fa-edit"></span></a></div>');
				content.children('a.jfedit-menu').prop('href', menuitemEditUrl).prop('title', menuEditTip);

				if (activePopover) {
					$(activePopover).popover('hide');
				}
				$(this).popover({html:true, content:content.html(), container:'body', trigger:'manual', animation:false, placement: 'bottom'}).popover('show');
				activePopover = this;

				$('body>div.popover').on({
					mouseenter: function() {
						if (activePopover) {
							$(activePopover).clearQueue();
						}
					},
					mouseleave: function() {
						if (activePopover) {
							$(activePopover).popover('hide');
						}
					}
				})
				.find('a.jfedit-menu').tooltip({"container": false, html: true, placement: 'bottom'});
			},
			mouseleave: function() {
				$(this).delay(1500).queue(function(next) { $(this).popover('hide'); next() });
			}
		});
	});
})(jQuery);
