<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Search
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Helper class for Joomla! Search components
 *
 * @package     Joomla.Libraries
 * @subpackage  Search
 * @since       3.0
 */
class JSearchHelper
{
	/**
	 * Method to log search terms to the database
	 *
	 * @param   string  $term       The term being searched
	 * @param   string  $component  The component being used for the search
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function logSearch($term, $component)
	{
		// Initialise our variables
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$enable_log_searches = JComponentHelper::getParams($component)->get('enabled');

		// Sanitise the term for the database
		$search_term = $db->escape(trim(strtolower($term)));

		if ($enable_log_searches)
		{
			// Query the table to determine if the term has been searched previously
			$query->select($db->qn('hits'))
				->from($db->qn('#__core_log_searches'))
				->where($db->qn('search_term') . ' = ' . $db->q($search_term));
			$db->setQuery($query);
			$hits = intval($db->loadResult());

			// Reset the $query object
			$query->clear();

			// Update the table based on the results
			if ($hits)
			{
				$query->update($db->qn('#__core_log_searches'));
				$query->set('hits = (hits + 1)');
				$query->where($db->qn('search_term') . ' = ' . $db->q($search_term));
			}
			else
			{
				$query->insert($db->qn('#__core_log_searches'))
					->columns(array($db->qn('search_term'), $db->qn('hits')))
					->values($db->q($search_term) . ', 1');
			}

			// Execute the update query
			$db->setQuery($query);
			$db->execute();
		}
	}
}
