<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Cache
 */
class TOOLBAR_cache
{
	/**
	* Draws the menu for a New category
	*/
	function _DEFAULT() {

		JToolBarHelper::title(JText::_('Cache Manager - Clean Cache Admin'), 'checkin.png');
		JToolBarHelper::custom('delete', 'delete.png', 'delete_f2.png', 'Delete', true);
		JToolBarHelper::help('screen.cache');
	}

	function _PURGEADMIN() {

		JToolBarHelper::title(JText::_('Cache Manager - Purge Cache Admin'), 'checkin.png');
		JToolBarHelper::custom('purge', 'delete.png', 'delete_f2.png', 'Purge expired', false);
		JToolBarHelper::help('screen.cache');
	}
}