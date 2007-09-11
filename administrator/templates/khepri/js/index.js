/**
* @version		$Id$
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Joomla! 1.5 Admininstrator index template behvaior
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @since		1.5
 * @version  	1.0
 */

//For IE6 - Background flicker fix
try {
  document.execCommand('BackgroundImageCache', false, true);
} catch(e) {}

document.menu = null
window.addEvent('domready', function(){
	element = $('menu')
	if(!element.hasClass('disabled')) {
		var menu = new JMenu(element)
		document.menu = menu
	}
});