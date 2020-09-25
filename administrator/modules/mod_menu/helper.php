<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
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
	 *
	 * @deprecated  4.0
	 */
	public static function getMenus()
	{
		$db     = JFactory::getDbo();

		// Search for home menu and language if exists
		$subQuery = $db->getQuery(true)
			->select('b.menutype, b.home, b.language, l.image, l.sef, l.title_native')
			->from('#__menu AS b')
			->leftJoin('#__languages AS l ON l.lang_code = b.language')
			->where('b.home != 0')
			->where('(b.client_id = 0 OR b.client_id IS NULL)');

		// Get all menu types with optional home menu and language
		$query = $db->getQuery(true)
			->select('a.id, a.asset_id, a.menutype, a.title, a.description, a.client_id')
			->select('c.home, c.language, c.image, c.sef, c.title_native')
			->from('#__menu_types AS a')
			->leftJoin('(' . (string) $subQuery . ') c ON c.menutype = a.menutype')
			->order('a.id');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$result = array();
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JERROR_LOADING_MENUS', $e->getMessage()), 'error');
		}

		return $result;
	}
}
