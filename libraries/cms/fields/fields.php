<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for com_fields
 *
 * @since  __DEPLOY_VERSION__
 */
class JFields
{
	/**
	 * Method to determine if the com_fields component is enabled.
	 *
	 * @return  boolean  True if the component exists and is enabled; false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isEnabled()
	{
		// Flag to avoid doing multiple database queries.
		static $tested = false;

		// Status of the component.
		static $enabled = false;

		// If already tested, don't test again.
		if (!$tested)
		{
			// Determine status of the component.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('enabled')
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('component'))
				->where($db->quoteName('element') . ' = ' . $db->quote('com_fields'));
			$db->setQuery($query);

			$compEnabled = $db->loadResult();

			// Determine status of the fields system plugin.
			$query->clear()
			->select('enabled')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('fields'));
			$db->setQuery($query);

			$pluginEnabled = $db->loadResult();

			if ($compEnabled && $pluginEnabled)
			{
				$enabled = true;

				$tested = true;
			}
		}

		return (bool) $enabled;
	}
}
