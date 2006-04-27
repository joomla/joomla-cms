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
 * Common javascript funtionality
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
 
/* -------------------------------------------- */
/* -- Browser information --------------------- */
/* -------------------------------------------- */

Browser = new Object();
Browser.agt     = navigator.userAgent.toLowerCase();
Browser.is_ie	= ((Browser.agt.indexOf("msie") != -1) && (Browser.agt.indexOf("opera") == -1));

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

/* gap is in millisecs */
function delay(gap) { 	
	var then,now; then=new Date().getTime();
	now=then;
	while((now-then)<gap)
	{now=new Date().getTime();}
}



