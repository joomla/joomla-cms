/**
 * @version		$Id$
 * @package		Hathor Accessible Administrator Template
 * @since		1.6
 * @version  	1.04
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * from accessible suckerfish menu by Matt Carroll,
 * redesigned & mootooled by Bill Tomczak
 */

window.addEvent('domready', function(){
  var menu = document.id('menu');
  var cancel = false;
  var clearDelay = 250; // milliseconds to clear all menus on exit

  if (menu && !menu.hasClass('disabled')) {
    // set up mouse events
    menu.getElements('li').each(function(cel){
      cel.addEvent('mouseenter', function(){
        // add mouse hover
        this.addClass('sfhover');
        this.getElement('a').focus();
        cancel = true;
      });

      // remove mouse hover
      cel.addEvent('mouseleave', function() {
        this.removeClass('sfhover');
        if (!cancel) {
          this.getElement('a').fireEvent('blur', null, clearDelay);
        }
        cancel = false;
      });
    });

    // Maintain place for keyboard hovering
    menu.getElements('a').each(function(ael) {
      // add hover for the link and propagate up the list
      ael.addEvent('focus', function() {
        this.getParents('li').addClass('sfhover');
      });

      // remove keyboard hover all the way up the list
      ael.addEvent('blur', function() {
        this.getParents('li').removeClass('sfhover');
      });
    });
  }
});
