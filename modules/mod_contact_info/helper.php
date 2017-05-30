<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_users_latest
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_contact_info
 *
 * @package     Joomla.Site
 * @subpackage  mod_contact_info
 * @since       3.1
 */
class ModContactsInfoHelper
{
	public static function getContact($id)
	{
		if (JComponentHelper::isEnabled('com_contact'))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__contact_details');
			$query->where($db->quoteName('id') . ' = ' . (int) $id);
			$db->setQuery($query);

			return $db->loadObject();
		}
	}
}
