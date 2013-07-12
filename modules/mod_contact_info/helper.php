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
 * @subpackage  mod_random_image
 * @since       3.1
 */
class ModContactsInfoHelper
{
	public static function getData($id, $component, $table, $field = 'id', $selector = '*')
	{
		if (1 == JComponentHelper::isEnabled($component))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($selector);
			$query->from($table);
			$query->where($db->quoteName($field) . ' = ' . $db->quote($id));
			$db->setQuery($query);
			$db->query();

			return $db->loadObject();
		}
	}
}
