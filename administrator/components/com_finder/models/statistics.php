<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Statistics model class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderModelStatistics extends JModelLegacy
{
	/**
	 * Method to get the component statistics
	 *
	 * @return  object  The component statistics
	 *
	 * @since   2.5
	 */
	public function getData()
	{
		// Initialise
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$data = new JObject;

		$query->select('COUNT(term_id)');
		$query->from($db->quoteName('#__finder_terms'));
		$db->setQuery($query);
		$data->term_count = $db->loadResult();

		$query->clear();
		$query->select('COUNT(link_id)');
		$query->from($db->quoteName('#__finder_links'));
		$db->setQuery($query);
		$data->link_count = $db->loadResult();

		$query->clear();
		$query->select('COUNT(id)');
		$query->from($db->quoteName('#__finder_taxonomy'));
		$query->where($db->quoteName('parent_id') . ' = 1');
		$db->setQuery($query);
		$data->taxonomy_branch_count = $db->loadResult();

		$query->clear();
		$query->select('COUNT(id)');
		$query->from($db->quoteName('#__finder_taxonomy'));
		$query->where($db->quoteName('parent_id') . ' > 1');
		$db->setQuery($query);
		$data->taxonomy_node_count = $db->loadResult();

		$query->clear();
		$query->select('t.title AS type_title, COUNT(a.link_id) AS link_count');
		$query->from($db->quoteName('#__finder_links') . ' AS a');
		$query->join('INNER', $db->quoteName('#__finder_types') . ' AS t ON t.id = a.type_id');
		$query->group('a.type_id, t.title');
		$query->order($db->quoteName('type_title'), 'ASC');
		$db->setQuery($query);
		$data->type_list = $db->loadObjectList();

		return $data;
	}
}
