<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_menu
 *
 * @since  1.5
 */
abstract class ModMenuHelper
{
	/**
	 * Get a list of the available menus.
	 *
	 * @return  array  An array of the available menus (from the menu types table).
	 *
	 * @since   1.6
	 */
	public static function getMenus()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Load all available menu types
		$query->select('a.*')
			->from('#__menu_types AS a');

		// Find the "home" link in each menu type
		$query->select('SUM(b.home) AS home, b.language')
			->join('LEFT', '#__menu AS b ON b.menutype = a.menutype AND b.home != 0')
			->where('(b.client_id = 0 OR b.client_id IS NULL)');

		// Find the language parameters for language in each home link found
		$query->select('l.image, l.sef, l.title_native')
			->join('LEFT', '#__languages AS l ON l.lang_code = b.language');

		// Sqlsrv change
		$query->group('a.id, a.menutype, a.description, a.title, b.menutype,b.language,l.image,l.sef,l.title_native');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$result = array();
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $result;
	}
}
