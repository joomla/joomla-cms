/**
* @version $Id$
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Joomla! 1.5 Admin template main css file
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @since		1.5
 * @version  	1.0
 */
  
document.menu = null
document.addLoadEvent(function() {
  element = document.getElementById('menu')
  var menu = new JMenu(element)
  document.menu = menu
});

document.addLoadEvent(function() { 
	Fat.fade_all(); 
	if(NiftyCheck()) {
		Rounded("div#toolbar-box","all","#fff","#fbfbfb","border #ccc");
		Rounded("div#element-box","all","#fff","#fff","border #ccc");
		Rounded("div#submenu-box","all","#fff","#f6f6f6","border #ccc");
	}
});

