<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Helper class for Finder.
 *
 * @since  2.5
 */
class FinderHelper
{
	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public static $extension = 'com_finder';

	/**
	 * Gets the finder system plugin extension id.
	 *
	 * @return  integer  The finder system plugin extension id.
	 *
	 * @since   3.6.0
	 */
	public static function getFinderPluginId()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
			->where($db->quoteName('element') . ' = ' . $db->quote('finder'));
		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $result;
	}
}
