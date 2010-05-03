/**
 * @version		$Id$
 * @package		Hathor Accessible Administrator Template
 * @since		1.6
 * @version  	1.04
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * from accessible suckerfish menu by Matt Carroll,
 * mootooled by Bill Tomczak
 */

window.addEvent('domready', function(){
  var menu = document.id('menu');
  if (menu && !menu.hasClass('disabled')) {
    menu.getElements('li').each(function(cel){
      cel.addEvent('mouseenter', function(){
        this.addClass('sfhover');
      });
      cel.addEvent('mouseleave', function() {
				this.removeClass('sfhover');
			});
    });

  	menu.getElements('a').each(function(ael) {
			ael.addEvent('focus', function() {
				this.addClass('sffocus');
				this.getParents('li').addClass('sfhover');
			});
			ael.addEvent('blur', function() {
				this.removeClass('sffocus');
				this.getParents('li').removeClass('sfhover');
			});
		});
	}
});
