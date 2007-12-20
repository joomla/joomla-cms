<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Cache
*/
class TOOLBAR_cache
{
	/**
	* Draws the menu for a New category
	*/
	function _DEFAULT() {

		JToolBarHelper::title( JText::_( 'Cache Manager' ), 'cache.png' );
		JToolBarHelper::custom( 'delete', 'delete.png', 'delete_f2.png', 'Delete', true );
		JToolBarHelper::help( 'screen.cache' );
	}
}