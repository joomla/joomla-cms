<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;

/**
 * Helper class for Joomla! Search components
 *
 * @since  3.0
 */
class SearchHelper
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
		$enableLogSearches = ComponentHelper::getParams($component)->get('enabled');

		if (!$enableLogSearches)
		{
			return;
		}

		// Initialise our variables
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		// Sanitise the term for the database
		$searchTerm = $db->escape(trim(strtolower($term)));

		// Query the table to determine if the term has been searched previously
		$query->select($db->quoteName('hits'))
			->from($db->quoteName('#__core_log_searches'))
			->where($db->quoteName('search_term') . ' = ' . $db->quote($searchTerm));
		$db->setQuery($query);
		$hits = intval($db->loadResult());

		// Reset the $query object
		$query->clear();

		// Update the table based on the results
		if ($hits)
		{
			$query->update($db->quoteName('#__core_log_searches'))
				->set('hits = (hits + 1)')
				->where($db->quoteName('search_term') . ' = ' . $db->quote($searchTerm));
		}
		else
		{
			$query->insert($db->quoteName('#__core_log_searches'))
				->columns(array($db->quoteName('search_term'), $db->quoteName('hits')))
				->values($db->quote($searchTerm) . ', 1');
		}

		// Execute the update query
		$db->setQuery($query);
		$db->execute();
	}
}
