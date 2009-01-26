/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

/**
 * Joomla! 1.5 Admininstrator index template behvaior
 *
 * @package		Joomla
 * @since		1.5
 * @version  	1.0
 */

//For IE6 - Background flicker fix
try {
  document.execCommand('BackgroundImageCache', false, true);
} catch(e) {}

document.menu = null
window.addEvent('load', function(){
	element = $('menu')
	if(!element.hasClass('disabled')) {
		var menu = new JMenu(element)
		document.menu = menu
	}
});