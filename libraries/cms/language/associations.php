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
		$query = $db->getQuery(true);
		$query->select($db->qn('c2.language'))
			->from($db->qn($tablename, 'c'))
			->innerJoin($db->qn('#__associations', 'a') . ' ON a.id = c.id AND a.context=' . $db->q($context))
			->innerJoin($db->qn('#__associations', 'a2') . ' ON a.key = a2.key')
			->innerJoin($db->qn($tablename, 'c2') . ' ON a2.id = c2.' . $db->qn($pk));

		// Use alias field ?
		if (!empty($aliasField))
		{
			$query->select(
				$query->concatenate(
					array(
						$db->qn('c2.' . $pk),
						$db->qn('c2.' . $aliasField)
					),
					':'
				) . ' AS ' . $db->qn($pk)
			);
		}
		else
		{
			$query->select($db->qn('c2.' . $pk));
		}

		// Use catid field ?
		if (!empty($catField))
		{
			$query->innerJoin($db->qn('#__categories', 'ca') . ' ON ' . $db->qn('c2.' . $catField) . ' = ca.id AND ca.extension = ' . $db->q($extension))
				->select(
				$query->concatenate(
					array(
						'ca.id',
						'ca.alias'
					),
					':'
				) . ' AS ' . $db->qn($catField)
			);
		}

		$query->where('c.id =' . (int) $id);

		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList('language');
		}
		catch (runtimeException $e)
		{
			throw new Exception($e->getMessage(), 500);

			return false;
		}

		if ($items)
		{
			foreach ($items as $tag => $item)
			{
				$associations[$tag] = $item;
			}
		}

		return $associations;
	}
}
