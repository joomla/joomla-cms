<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Language;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Utitlity class for associations in multilang
 *
 * @since  3.1
 */
class Associations
{
	/**
	 * Get the associations.
	 *
	 * @param   string   $extension   The name of the component.
	 * @param   string   $tablename   The name of the table.
	 * @param   string   $context     The context
	 * @param   integer  $id          The primary key value.
	 * @param   string   $pk          The name of the primary key in the given $table.
	 * @param   string   $aliasField  If the table has an alias field set it here. Null to not use it
	 * @param   string   $catField    If the table has a catid field set it here. Null to not use it
	 *
	 * @return  array                The associated items
	 *
	 * @since   3.1
	 *
	 * @throws  Exception
	 */
	public static function getAssociations($extension, $tablename, $context, $id, $pk = 'id', $aliasField = 'alias', $catField = 'catid')
	{
		// To avoid doing duplicate database queries.
		static $multilanguageAssociations = array();

		// Multilanguage association array key. If the key is already in the array we don't need to run the query again, just return it.
		$queryKey = implode('|', func_get_args());
		if (!isset($multilanguageAssociations[$queryKey]))
		{
			$multilanguageAssociations[$queryKey] = array();

			$db = \JFactory::getDbo();
			$categoriesExtraSql = (($tablename === '#__categories') ? ' AND c2.extension = ' . $db->quote($extension) : '');
			$query = $db->getQuery(true)
				->select($db->quoteName('c2.language'))
				->from($db->quoteName($tablename, 'c'))
				->join('INNER', $db->quoteName('#__associations', 'a') . ' ON a.id = c.' . $db->quoteName($pk) . ' AND a.context=' . $db->quote($context))
				->join('INNER', $db->quoteName('#__associations', 'a2') . ' ON a.key = a2.key')
				->join('INNER', $db->quoteName($tablename, 'c2') . ' ON a2.id = c2.' . $db->quoteName($pk) . $categoriesExtraSql);

			// Use alias field ?
			if (!empty($aliasField))
			{
				$query->select(
					$query->concatenate(
						array(
							$db->quoteName('c2.' . $pk),
							$db->quoteName('c2.' . $aliasField),
						),
						':'
					) . ' AS ' . $db->quoteName($pk)
				);
			}
			else
			{
				$query->select($db->quoteName('c2.' . $pk));
			}

			// Use catid field ?
			if (!empty($catField))
			{
				$query->join(
						'INNER',
						$db->quoteName('#__categories', 'ca') . ' ON ' . $db->quoteName('c2.' . $catField) . ' = ca.id AND ca.extension = ' . $db->quote($extension)
					)
					->select(
						$query->concatenate(
							array('ca.id', 'ca.alias'),
							':'
						) . ' AS ' . $db->quoteName($catField)
					);
			}

			$query->where('c.' . $pk . ' = ' . (int) $id);
			if ($tablename === '#__categories')
			{
				$query->where('c.extension = ' . $db->quote($extension));
			}

			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('language');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as $tag => $item)
				{
					// Do not return itself as result
					if ((int) $item->{$pk} != $id)
					{
						$multilanguageAssociations[$queryKey][$tag] = $item;
					}
				}
			}
		}

		return $multilanguageAssociations[$queryKey];
	}

	/**
	 * Method to determine if the language filter Items Associations parameter is enabled.
	 * This works for both site and administrator.
	 *
	 * @return  boolean  True if the parameter is implemented; false otherwise.
	 *
	 * @since   3.2
	 */
	public static function isEnabled()
	{
		// Flag to avoid doing multiple database queries.
		static $tested = false;

		// Status of language filter parameter.
		static $enabled = false;

		if (Multilanguage::isEnabled())
		{
			// If already tested, don't test again.
			if (!$tested)
			{
				$plugin = \JPluginHelper::getPlugin('system', 'languagefilter');

				if (!empty($plugin))
				{
					$params = new Registry($plugin->params);
					$enabled  = (boolean) $params->get('item_associations', true);
				}

				$tested = true;
			}
		}

		return $enabled;
	}
}
