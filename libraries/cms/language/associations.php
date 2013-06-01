<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  helper
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Utitlity class for associations in multilang
 *
 * @package     Joomla.Libraries
 * @subpackage  Language
 * @since       3.1
 */
class JLanguageAssociations
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
	 */
	public static function getAssociations($extension, $tablename, $context, $id, $pk = 'id', $aliasField = 'alias', $catField = 'catid')
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('c2.language'))
			->from($db->quoteName($tablename, 'c'))
			->join('INNER', $db->quoteName('#__associations', 'a') . ' ON a.id = c.id AND a.context=' . $db->quote($context))
			->join('INNER', $db->quoteName('#__associations', 'a2') . ' ON a.key = a2.key')
			->join('INNER', $db->quoteName($tablename, 'c2') . ' ON a2.id = c2.' . $db->quoteName($pk));

		// Use alias field ?
		if (!empty($aliasField))
		{
			$query->select(
				$query->concatenate(
					array(
						$db->quoteName('c2.' . $pk),
						$db->quoteName('c2.' . $aliasField)
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
			$query->join('INNER', $db->quoteName('#__categories', 'ca') . ' ON ' . $db->quoteName('c2.' . $catField) . ' = ca.id AND ca.extension = ' . $db->quote($extension))
				->select(
					$query->concatenate(
						array('ca.id', 'ca.alias'),
						':'
					) . ' AS ' . $db->quoteName($catField)
				);
		}

		$query->where('c.' . $pk . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList('language');
		}
		catch (RuntimeException $e)
		{
			throw new Exception($e->getMessage(), 500);
		}

		if ($items)
		{
			foreach ($items as $tag => $item)
			{
				// Do not return itself as result
				if ((int) $item->{$pk} != $id)
				{
					$associations[$tag] = $item;
				}
			}
		}

		return $associations;
	}
}
