<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Site\Helper;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;

/**
 * Helper class for Joomla! Finder components
 *
 * @since  __DEPLOY_VERSION__
 */
class FinderHelper
{
	/**
	 * Method to log searches to the database
	 *
	 * @param   string   $searchquery  The search query
	 * @param   integer  $resultCount  The number of results for this search
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function logSearch($searchquery, $resultCount = 0)
	{
		$enable_log_searches = ComponentHelper::getParams('com_finder')->get('logging_enabled', 1);

		if (!$enable_log_searches)
		{
			return;
		}

		// Initialise our variables
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);

		// Sanitise the term for the database
		$temp = unserialize(serialize($searchquery));
		$temp->input = trim(strtolower($searchquery->input));
		$entry = new \stdClass;
		$entry->searchterm = $temp->input;
		$entry->query = serialize($temp);
		$entry->md5sum = md5($entry->query);
		$entry->hits = 1;
		$entry->results = $resultCount;

		// Query the table to determine if the term has been searched previously
		$query->select($db->quoteName('hits'))
			->from($db->quoteName('#__finder_logging'))
			->where($db->quoteName('md5sum') . ' = ' . $db->quote($entry->md5sum));
		$db->setQuery($query);
		$hits = (int) $db->loadResult();

		// Reset the $query object
		$query->clear();

		// Update the table based on the results
		if ($hits)
		{
			$query->update($db->quoteName('#__finder_logging'))
				->set('hits = (hits + 1)')
				->where($db->quoteName('md5sum') . ' = ' . $db->quote($entry->md5sum));
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$db->insertObject('#__finder_logging', $entry);
		}
	}
}
