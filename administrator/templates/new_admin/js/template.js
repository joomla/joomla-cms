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
  
/* -------------------------------------------- */
/* -- page loader ----------------------------- */
/* -------------------------------------------- */

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}

document.menu = null
addLoadEvent(function() {
  element = document.getElementById('menu')
  var menu = new JMenu(element)
  document.menu = menu
});

addLoadEvent(function() {  
	if(!NiftyCheck()) alert("hello");
	Rounded("div.component","all","#fff","#fff","border #ccc");
	Rounded("div.toolbar-box","all","#fff","#fbfbfb","border #ccc");
	Rounded("div.element-box","all","#fff","#fff","border #ccc");
	Rounded("div.submenu-box","all","#fff","#fbfbfb","border #ccc");
});

