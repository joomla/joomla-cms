<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Module\Menu\Administrator\Menu;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Helper for mod_menu
 *
 * @since  1.5
 */
abstract class MenuHelper
{
	/**
	 * Get a list of the available menus.
	 *
	 * @return  array  An array of the available menus (from the menu types table).
	 *
	 * @since   1.6
	 */
	public static function getItems()
	{
		$db = Factory::getDbo();

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
		catch (\RuntimeException $e)
		{
			$result = array();
			Factory::getApplication()->enqueueMessage(Text::sprintf('JERROR_LOADING_MENUS', $e->getMessage()), 'error');
		}

		return $result;
	}
}
