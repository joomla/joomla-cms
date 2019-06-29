<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// Register dependent classes.
define('FINDER_PATH_INDEXER', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer');
JLoader::register('FinderIndexerHelper', FINDER_PATH_INDEXER . '/helper.php');
JLoader::register('FinderIndexerQuery', FINDER_PATH_INDEXER . '/query.php');
JLoader::register('FinderIndexerResult', FINDER_PATH_INDEXER . '/result.php');
JLoader::register('FinderIndexerStemmer', FINDER_PATH_INDEXER . '/stemmer.php');

/**
 * Search model class for the Finder package.
 *
 * @since  2.5
 */
class FinderModelSearch extends JModelList
{
	/**
	 * Context string for the model type
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'com_finder.search';

	/**
	 * The query object is an instance of FinderIndexerQuery which contains and
	 * models the entire search query including the text input; static and
	 * dynamic taxonomy filters; date filters; etc.
	 *
	 * @var    FinderIndexerQuery
	 * @since  2.5
	 */
	protected $query;

	/**
	 * An array of all excluded terms ids.
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected $excludedTerms = array();

	/**
	 * An array of all included terms ids.
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected $includedTerms = array();

	/**
	 * An array of all required terms ids.
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected $requiredTerms = array();

	/**
	 * Method to get the results of the query.
	 *
	 * @return  array  An array of FinderIndexerResult objects.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function getResults()
	{
		// Check if the search query is valid.
		if (empty($this->query->search))
		{
			return null;
		}

		// Check if we should return results.
		if (empty($this->includedTerms) && (empty($this->query->filters) || !$this->query->empty))
		{
			return null;
		}

		// Get the store id.
		$store = $this->getStoreId('getResults');

		// Use the cached data if possible.
		if ($this->retrieve($store))
		{
			return $this->retrieve($store);
		}

		// Get the row data.
		$items = $this->getResultsData();

		// Check the data.
		if (empty($items))
		{
			return null;
		}

		// Create the query to get the search results.
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('link_id') . ', ' . $db->quoteName('object'))
			->from($db->quoteName('#__finder_links'))
			->where($db->quoteName('link_id') . ' IN (' . implode(',', array_keys($items)) . ')');

		// Load the results from the database.
		$db->setQuery($query);
		$rows = $db->loadObjectList('link_id');

		// Set up our results container.
		$results = $items;

		// Convert the rows to result objects.
		foreach ($rows as $rk => $row)
		{
			// Build the result object.
			$result = unserialize($row->object);
			$result->weight = $results[$rk];
			$result->link_id = $rk;

			// Add the result back to the stack.
			$results[$rk] = $result;
		}

		// Switch to a non-associative array.
		$results = array_values($results);

		// Push the results into cache.
		$this->store($store, $results);

		// Return the results.
		return $this->retrieve($store);
	}

	/**
	 * Method to get the total number of results.
	 *
	 * @return  integer  The total number of results.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function getTotal()
	{
		// Check if the search query is valid.
		if (empty($this->query->search))
		{
			return null;
		}

		// Check if we should return results.
		if (empty($this->includedTerms) && (empty($this->query->filters) || !$this->query->empty))
		{
			return null;
		}

		// Get the store id.
		$store = $this->getStoreId('getTotal');

		// Use the cached data if possible.
		if ($this->retrieve($store))
		{
			return $this->retrieve($store);
		}

		// Get the results total.
		$total = $this->getResultsTotal();

		// Push the total into cache.
		$this->store($store, $total);

		// Return the total.
		return $this->retrieve($store);
	}

	/**
	 * Method to get the query object.
	 *
	 * @return  FinderIndexerQuery  A query object.
	 *
	 * @since   2.5
	 */
	public function getQuery()
	{
		// Return the query object.
		return $this->query;
	}

	/**
	 * Method to build a database query to load the list data.
	 *
	 * @return  JDatabaseQuery  A database query.
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		// Get the store id.
		$store = $this->getStoreId('getListQuery');

		// Use the cached data if possible.
		if ($this->retrieve($store, false))
		{
			return clone $this->retrieve($store, false);
		}

		// Set variables
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('l.link_id')
			->from($db->quoteName('#__finder_links') . ' AS l')
			->where('l.access IN (' . $groups . ')')
			->where('l.state = 1')
			->where('l.published = 1');

		// Get the null date and the current date, minus seconds.
		$nullDate = $db->quote($db->getNullDate());
		$nowDate = $db->quote(substr_replace(JFactory::getDate()->toSql(), '00', -2));

		// Add the publish up and publish down filters.
		$query->where('(l.publish_start_date = ' . $nullDate . ' OR l.publish_start_date <= ' . $nowDate . ')')
			->where('(l.publish_end_date = ' . $nullDate . ' OR l.publish_end_date >= ' . $nowDate . ')');

		/*
		 * Add the taxonomy filters to the query. We have to join the taxonomy
		 * map table for each group so that we can use AND clauses across
		 * groups. Within each group there can be an array of values that will
		 * use OR clauses.
		 */
		if (!empty($this->query->filters))
		{
			// Convert the associative array to a numerically indexed array.
			$groups = array_values($this->query->filters);

			// Iterate through each taxonomy group and add the join and where.
			for ($i = 0, $c = count($groups); $i < $c; $i++)
			{
				// We use the offset because each join needs a unique alias.
				$query->join('INNER', $db->quoteName('#__finder_taxonomy_map') . ' AS t' . $i . ' ON t' . $i . '.link_id = l.link_id')
					->where('t' . $i . '.node_id IN (' . implode(',', $groups[$i]) . ')');
			}
		}

		// Add the start date filter to the query.
		if (!empty($this->query->date1))
		{
			// Escape the date.
			$date1 = $db->quote($this->query->date1);

			// Add the appropriate WHERE condition.
			if ($this->query->when1 === 'before')
			{
				$query->where($db->quoteName('l.start_date') . ' <= ' . $date1);
			}
			elseif ($this->query->when1 === 'after')
			{
				$query->where($db->quoteName('l.start_date') . ' >= ' . $date1);
			}
			else
			{
				$query->where($db->quoteName('l.start_date') . ' = ' . $date1);
			}
		}

		// Add the end date filter to the query.
		if (!empty($this->query->date2))
		{
			// Escape the date.
			$date2 = $db->quote($this->query->date2);

			// Add the appropriate WHERE condition.
			if ($this->query->when2 === 'before')
			{
				$query->where($db->quoteName('l.start_date') . ' <= ' . $date2);
			}
			elseif ($this->query->when2 === 'after')
			{
				$query->where($db->quoteName('l.start_date') . ' >= ' . $date2);
			}
			else
			{
				$query->where($db->quoteName('l.start_date') . ' = ' . $date2);
			}
		}

		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('l.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ', ' . $db->quote('*') . ')');
		}

		// Push the data into cache.
		$this->store($store, $query, false);

		// Return a copy of the query object.
		return clone $this->retrieve($store, false);
	}

	/**
	 * Method to get the total number of results for the search query.
	 *
	 * @return  integer  The results total.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getResultsTotal()
	{
		// Get the store id.
		$store = $this->getStoreId('getResultsTotal', false);

		// Use the cached data if possible.
		if ($this->retrieve($store))
		{
			return $this->retrieve($store);
		}

		// Get the base query and add the ordering information.
		$base = $this->getListQuery();
		$base->select('0 AS ordering');

		// Get the maximum number of results.
		$limit = (int) $this->getState('match.limit');

		/*
		 * If there are no optional or required search terms in the query,
		 * we can get the result total in one relatively simple database query.
		 */
		if (empty($this->includedTerms))
		{
			// Adjust the query to join on the appropriate mapping table.
			$query = clone $base;
			$query->clear('select')
				->select('COUNT(DISTINCT l.link_id)');

			// Get the total from the database.
			$this->_db->setQuery($query);
			$total = $this->_db->loadResult();

			// Push the total into cache.
			$this->store($store, min($total, $limit));

			// Return the total.
			return $this->retrieve($store);
		}

		/*
		 * If there are optional or required search terms in the query, the
		 * process of getting the result total is more complicated.
		 */
		$start = 0;
		$items = array();
		$sorted = array();
		$maps = array();
		$excluded = $this->getExcludedLinkIds();

		/*
		 * Iterate through the included search terms and group them by mapping
		 * table suffix. This ensures that we never have to do more than 16
		 * queries to get a batch. This may seem like a lot but it is rarely
		 * anywhere near 16 because of the improved mapping algorithm.
		 */
		foreach ($this->includedTerms as $token => $ids)
		{
			// Get the mapping table suffix.
			$suffix = StringHelper::substr(md5(StringHelper::substr($token, 0, 1)), 0, 1);

			// Initialize the mapping group.
			if (!array_key_exists($suffix, $maps))
			{
				$maps[$suffix] = array();
			}

			// Add the terms to the mapping group.
			$maps[$suffix] = array_merge($maps[$suffix], $ids);
		}

		/*
		 * When the query contains search terms we need to find and process the
		 * result total iteratively using a do-while loop.
		 */
		do
		{
			// Create a container for the fetched results.
			$results = array();
			$more = false;

			/*
			 * Iterate through the mapping groups and load the total from each
			 * mapping table.
			 */
			foreach ($maps as $suffix => $ids)
			{
				// Create a storage key for this set.
				$setId = $this->getStoreId('getResultsTotal:' . serialize(array_values($ids)) . ':' . $start . ':' . $limit);

				// Use the cached data if possible.
				if ($this->retrieve($setId))
				{
					$temp = $this->retrieve($setId);
				}
				// Load the data from the database.
				else
				{
					// Adjust the query to join on the appropriate mapping table.
					$query = clone $base;
					$query->join('INNER', '#__finder_links_terms' . $suffix . ' AS m ON m.link_id = l.link_id')
						->where('m.term_id IN (' . implode(',', $ids) . ')');

					// Load the results from the database.
					$this->_db->setQuery($query, $start, $limit);
					$temp = $this->_db->loadObjectList();

					// Set the more flag to true if any of the sets equal the limit.
					$more = count($temp) === $limit;

					// We loaded the data unkeyed but we need it to be keyed for later.
					$junk = $temp;
					$temp = array();

					// Convert to an associative array.
					for ($i = 0, $c = count($junk); $i < $c; $i++)
					{
						$temp[$junk[$i]->link_id] = $junk[$i];
					}

					// Store this set in cache.
					$this->store($setId, $temp);
				}

				// Merge the results.
				$results = array_merge($results, $temp);
			}

			// Check if there are any excluded terms to deal with.
			if (count($excluded))
			{
				// Remove any results that match excluded terms.
				for ($i = 0, $c = count($results); $i < $c; $i++)
				{
					if (in_array($results[$i]->link_id, $excluded))
					{
						unset($results[$i]);
					}
				}

				// Reset the array keys.
				$results = array_values($results);
			}

			// Iterate through the set to extract the unique items.
			for ($i = 0, $c = count($results); $i < $c; $i++)
			{
				if (!isset($sorted[$results[$i]->link_id]))
				{
					$sorted[$results[$i]->link_id] = $results[$i]->ordering;
				}
			}

			/*
			 * If the query contains just optional search terms and we have
			 * enough items for the page, we can stop here.
			 */
			if (empty($this->requiredTerms))
			{
				// If we need more items and they're available, make another pass.
				if ($more && count($sorted) < $limit)
				{
					// Increment the batch starting point and continue.
					$start += $limit;
					continue;
				}

				// Push the total into cache.
				$this->store($store, min(count($sorted), $limit));

				// Return the total.
				return $this->retrieve($store);
			}

			/*
			 * The query contains required search terms so we have to iterate
			 * over the items and remove any items that do not match all of the
			 * required search terms. This is one of the most expensive steps
			 * because a required token could theoretically eliminate all of
			 * current terms which means we would have to loop through all of
			 * the possibilities.
			 */
			foreach ($this->requiredTerms as $token => $required)
			{
				// Create a storage key for this set.
				$setId = $this->getStoreId('getResultsTotal:required:' . serialize(array_values($required)) . ':' . $start . ':' . $limit);

				// Use the cached data if possible.
				if ($this->retrieve($setId))
				{
					$reqTemp = $this->retrieve($setId);
				}
					// Check if the token was matched.
				elseif (empty($required))
				{
					return null;
				}
					// Load the data from the database.
				else
				{
					// Setup containers in case we have to make multiple passes.
					$reqStart = 0;
					$reqTemp = array();

					do
					{
						// Get the map table suffix.
						$suffix = StringHelper::substr(md5(StringHelper::substr($token, 0, 1)), 0, 1);

						// Adjust the query to join on the appropriate mapping table.
						$query = clone $base;
						$query->join('INNER', '#__finder_links_terms' . $suffix . ' AS m ON m.link_id = l.link_id')
							->where('m.term_id IN (' . implode(',', $required) . ')');

						// Load the results from the database.
						$this->_db->setQuery($query, $reqStart, $limit);
						$temp = $this->_db->loadObjectList('link_id');

						// Set the required token more flag to true if the set equal the limit.
						$reqMore = count($temp) === $limit;

						// Merge the matching set for this token.
						$reqTemp += $temp;

						// Increment the term offset.
						$reqStart += $limit;
					}
					while ($reqMore === true);

					// Store this set in cache.
					$this->store($setId, $reqTemp);
				}

				// Remove any items that do not match the required term.
				$sorted = array_intersect_key($sorted, $reqTemp);
			}

			// If we need more items and they're available, make another pass.
			if ($more && count($sorted) < $limit)
			{
				// Increment the batch starting point.
				$start += $limit;

				// Merge the found items.
				$items += $sorted;

				continue;
			}

			// Otherwise, end the loop.
			{
				// Merge the found items.
				$items += $sorted;

				$more = false;
			}
			// End do-while loop.
		}
		while ($more === true);

		// Set the total.
		$total = count($items);
		$total = min($total, $limit);

		// Push the total into cache.
		$this->store($store, $total);

		// Return the total.
		return $this->retrieve($store);
	}

	/**
	 * Method to get the results for the search query.
	 *
	 * @return  array  An array of result data objects.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getResultsData()
	{
		// Get the store id.
		$store = $this->getStoreId('getResultsData', false);

		// Use the cached data if possible.
		if ($this->retrieve($store))
		{
			return $this->retrieve($store);
		}

		// Get the result ordering and direction.
		$ordering = $this->getState('list.ordering', 'l.start_date');
		$direction = $this->getState('list.direction', 'DESC');

		// Get the base query and add the ordering information.
		$base = $this->getListQuery();
		$base->select($this->_db->escape($ordering) . ' AS ordering');
		$base->order($this->_db->escape($ordering) . ' ' . $this->_db->escape($direction));

		/*
		 * If there are no optional or required search terms in the query, we
		 * can get the results in one relatively simple database query.
		 */
		if (empty($this->includedTerms))
		{
			// Get the results from the database.
			$this->_db->setQuery($base, (int) $this->getState('list.start'), (int) $this->getState('list.limit'));
			$return = $this->_db->loadObjectList('link_id');

			// Get a new store id because this data is page specific.
			$store = $this->getStoreId('getResultsData', true);

			// Push the results into cache.
			$this->store($store, $return);

			// Return the results.
			return $this->retrieve($store);
		}

		/*
		 * If there are optional or required search terms in the query, the
		 * process of getting the results is more complicated.
		 */
		$start = 0;
		$limit = (int) $this->getState('match.limit');
		$items = array();
		$sorted = array();
		$maps = array();
		$excluded = $this->getExcludedLinkIds();

		/*
		 * Iterate through the included search terms and group them by mapping
		 * table suffix. This ensures that we never have to do more than 16
		 * queries to get a batch. This may seem like a lot but it is rarely
		 * anywhere near 16 because of the improved mapping algorithm.
		 */
		foreach ($this->includedTerms as $token => $ids)
		{
			// Get the mapping table suffix.
			$suffix = StringHelper::substr(md5(StringHelper::substr($token, 0, 1)), 0, 1);

			// Initialize the mapping group.
			if (!array_key_exists($suffix, $maps))
			{
				$maps[$suffix] = array();
			}

			// Add the terms to the mapping group.
			$maps[$suffix] = array_merge($maps[$suffix], $ids);
		}

		/*
		 * When the query contains search terms we need to find and process the
		 * results iteratively using a do-while loop.
		 */
		do
		{
			// Create a container for the fetched results.
			$results = array();
			$more = false;

			/*
			 * Iterate through the mapping groups and load the results from each
			 * mapping table.
			 */
			foreach ($maps as $suffix => $ids)
			{
				// Create a storage key for this set.
				$setId = $this->getStoreId('getResultsData:' . serialize(array_values($ids)) . ':' . $start . ':' . $limit);

				// Use the cached data if possible.
				if ($this->retrieve($setId))
				{
					$temp = $this->retrieve($setId);
				}
				// Load the data from the database.
				else
				{
					// Adjust the query to join on the appropriate mapping table.
					$query = clone $base;
					$query->join('INNER', $this->_db->quoteName('#__finder_links_terms' . $suffix) . ' AS m ON m.link_id = l.link_id')
						->where('m.term_id IN (' . implode(',', $ids) . ')');

					// Load the results from the database.
					$this->_db->setQuery($query, $start, $limit);
					$temp = $this->_db->loadObjectList('link_id');

					// Store this set in cache.
					$this->store($setId, $temp);

					// The data is keyed by link_id to ease caching, we don't need it till later.
					$temp = array_values($temp);
				}

				// Set the more flag to true if any of the sets equal the limit.
				$more = count($temp) === $limit;

				// Merge the results.
				$results = array_merge($results, $temp);
			}

			// Check if there are any excluded terms to deal with.
			if (count($excluded))
			{
				// Remove any results that match excluded terms.
				for ($i = 0, $c = count($results); $i < $c; $i++)
				{
					if (in_array($results[$i]->link_id, $excluded))
					{
						unset($results[$i]);
					}
				}

				// Reset the array keys.
				$results = array_values($results);
			}

			/*
			 * If we are ordering by relevance we have to add up the relevance
			 * scores that are contained in the ordering field.
			 */
			if ($ordering === 'm.weight')
			{
				// Iterate through the set to extract the unique items.
				for ($i = 0, $c = count($results); $i < $c; $i++)
				{
					// Add the total weights for all included search terms.
					if (isset($sorted[$results[$i]->link_id]))
					{
						$sorted[$results[$i]->link_id] += (float) $results[$i]->ordering;
					}
					else
					{
						$sorted[$results[$i]->link_id] = (float) $results[$i]->ordering;
					}
				}
			}
			/*
			 * If we are ordering by start date we have to add convert the
			 * dates to unix timestamps.
			 */
			elseif ($ordering === 'l.start_date')
			{
				// Iterate through the set to extract the unique items.
				for ($i = 0, $c = count($results); $i < $c; $i++)
				{
					if (!isset($sorted[$results[$i]->link_id]))
					{
						$sorted[$results[$i]->link_id] = strtotime($results[$i]->ordering);
					}
				}
			}
			/*
			 * If we are not ordering by relevance or date, we just have to add
			 * the unique items to the set.
			 */
			else
			{
				// Iterate through the set to extract the unique items.
				for ($i = 0, $c = count($results); $i < $c; $i++)
				{
					if (!isset($sorted[$results[$i]->link_id]))
					{
						$sorted[$results[$i]->link_id] = $results[$i]->ordering;
					}
				}
			}

			// Sort the results.
			natcasesort($items);

			if ($direction === 'DESC')
			{
				$items = array_reverse($items, true);
			}

			/*
			 * If the query contains just optional search terms and we have
			 * enough items for the page, we can stop here.
			 */
			if (empty($this->requiredTerms))
			{
				// If we need more items and they're available, make another pass.
				if ($more && count($sorted) < ($this->getState('list.start') + $this->getState('list.limit')))
				{
					// Increment the batch starting point and continue.
					$start += $limit;
					continue;
				}

				// Push the results into cache.
				$this->store($store, $sorted);

				// Return the requested set.
				return array_slice($this->retrieve($store), (int) $this->getState('list.start'), (int) $this->getState('list.limit'), true);
			}

			/*
			 * The query contains required search terms so we have to iterate
			 * over the items and remove any items that do not match all of the
			 * required search terms. This is one of the most expensive steps
			 * because a required token could theoretically eliminate all of
			 * current terms which means we would have to loop through all of
			 * the possibilities.
			 */
			foreach ($this->requiredTerms as $token => $required)
			{
				// Create a storage key for this set.
				$setId = $this->getStoreId('getResultsData:required:' . serialize(array_values($required)) . ':' . $start . ':' . $limit);

				// Use the cached data if possible.
				if ($this->retrieve($setId))
				{
					$reqTemp = $this->retrieve($setId);
				}
				// Check if the token was matched.
				elseif (empty($required))
				{
					return null;
				}
				// Load the data from the database.
				else
				{
					// Setup containers in case we have to make multiple passes.
					$reqStart = 0;
					$reqTemp = array();

					do
					{
						// Get the map table suffix.
						$suffix = StringHelper::substr(md5(StringHelper::substr($token, 0, 1)), 0, 1);

						// Adjust the query to join on the appropriate mapping table.
						$query = clone $base;
						$query->join('INNER', $this->_db->quoteName('#__finder_links_terms' . $suffix) . ' AS m ON m.link_id = l.link_id')
							->where('m.term_id IN (' . implode(',', $required) . ')');

						// Load the results from the database.
						$this->_db->setQuery($query, $reqStart, $limit);
						$temp = $this->_db->loadObjectList('link_id');

						// Set the required token more flag to true if the set equal the limit.
						$reqMore = count($temp) === $limit;

						// Merge the matching set for this token.
						$reqTemp += $temp;

						// Increment the term offset.
						$reqStart += $limit;
					}
					while ($reqMore === true);

					// Store this set in cache.
					$this->store($setId, $reqTemp);
				}

				// Remove any items that do not match the required term.
				$sorted = array_intersect_key($sorted, $reqTemp);
			}

			// If we need more items and they're available, make another pass.
			if ($more && count($sorted) < ($this->getState('list.start') + $this->getState('list.limit')))
			{
				// Increment the batch starting point.
				$start += $limit;

				// Merge the found items.
				$items = array_merge($items, $sorted);

				continue;
			}
			// Otherwise, end the loop.
			else
			{
				// Set the found items.
				$items = $sorted;

				$more = false;
			}

			// End do-while loop.
		}
		while ($more === true);

		// Push the results into cache.
		$this->store($store, $items);

		// Return the requested set.
		return array_slice($this->retrieve($store), (int) $this->getState('list.start'), (int) $this->getState('list.limit'), true);
	}

	/**
	 * Method to get an array of link ids that match excluded terms.
	 *
	 * @return  array  An array of links ids.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getExcludedLinkIds()
	{
		// Check if the search query has excluded terms.
		if (empty($this->excludedTerms))
		{
			return array();
		}

		// Get the store id.
		$store = $this->getStoreId('getExcludedLinkIds', false);

		// Use the cached data if possible.
		if ($this->retrieve($store))
		{
			return $this->retrieve($store);
		}

		// Initialize containers.
		$links = array();
		$maps = array();

		/*
		 * Iterate through the excluded search terms and group them by mapping
		 * table suffix. This ensures that we never have to do more than 16
		 * queries to get a batch. This may seem like a lot but it is rarely
		 * anywhere near 16 because of the improved mapping algorithm.
		 */
		foreach ($this->excludedTerms as $token => $id)
		{
			// Get the mapping table suffix.
			$suffix = StringHelper::substr(md5(StringHelper::substr($token, 0, 1)), 0, 1);

			// Initialize the mapping group.
			if (!array_key_exists($suffix, $maps))
			{
				$maps[$suffix] = array();
			}

			// Add the terms to the mapping group.
			$maps[$suffix][] = (int) $id;
		}

		/*
		 * Iterate through the mapping groups and load the excluded links ids
		 * from each mapping table.
		 */

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		foreach ($maps as $suffix => $ids)
		{
			// Create the query to get the links ids.
			$query->clear()
				->select('link_id')
				->from($db->quoteName('#__finder_links_terms' . $suffix))
				->where($db->quoteName('term_id') . ' IN (' . implode(',', $ids) . ')')
				->group($db->quoteName('link_id'));

			// Load the link ids from the database.
			$db->setQuery($query);
			$temp = $db->loadColumn();

			// Merge the link ids.
			$links = array_merge($links, $temp);
		}

		// Sanitize the link ids.
		$links = array_unique($links);
		$links = ArrayHelper::toInteger($links);

		// Push the link ids into cache.
		$this->store($store, $links);

		return $links;
	}

	/**
	 * Method to get a store id based on model the configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string   $id    An identifier string to generate the store id. [optional]
	 * @param   boolean  $page  True to store the data paged, false to store all data. [optional]
	 *
	 * @return  string  A store id.
	 *
	 * @since   2.5
	 */
	protected function getStoreId($id = '', $page = true)
	{
		// Get the query object.
		$query = $this->getQuery();

		// Add the search query state.
		$id .= ':' . $query->input;
		$id .= ':' . $query->language;
		$id .= ':' . $query->filter;
		$id .= ':' . serialize($query->filters);
		$id .= ':' . $query->date1;
		$id .= ':' . $query->date2;
		$id .= ':' . $query->when1;
		$id .= ':' . $query->when2;

		if ($page)
		{
			// Add the list state for page specific data.
			$id .= ':' . $this->getState('list.start');
			$id .= ':' . $this->getState('list.limit');
			$id .= ':' . $this->getState('list.ordering');
			$id .= ':' . $this->getState('list.direction');
		}

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.  Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field. [optional]
	 * @param   string  $direction  An optional direction. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Get the configuration options.
		$app = JFactory::getApplication();
		$input = $app->input;
		$params = $app->getParams();
		$user = JFactory::getUser();

		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		// Setup the stemmer.
		if ($params->get('stem', 1) && $params->get('stemmer', 'porter_en'))
		{
			FinderIndexerHelper::$stemmer = FinderIndexerStemmer::getInstance($params->get('stemmer', 'porter_en'));
		}

		$request = $input->request;
		$options = array();

		// Get the empty query setting.
		$options['empty'] = $params->get('allow_empty_query', 0);

		// Get the static taxonomy filters.
		$options['filter'] = $request->getInt('f', $params->get('f', ''));

		// Get the dynamic taxonomy filters.
		$options['filters'] = $request->get('t', $params->get('t', array()), '', 'array');

		// Get the query string.
		$options['input'] = $request->getString('q', $params->get('q', ''));

		// Get the query language.
		$options['language'] = $request->getCmd('l', $params->get('l', ''));

		// Get the start date and start date modifier filters.
		$options['date1'] = $request->getString('d1', $params->get('d1', ''));
		$options['when1'] = $request->getString('w1', $params->get('w1', ''));

		// Get the end date and end date modifier filters.
		$options['date2'] = $request->getString('d2', $params->get('d2', ''));
		$options['when2'] = $request->getString('w2', $params->get('w2', ''));

		// Load the query object.
		$this->query = new FinderIndexerQuery($options);

		// Load the query token data.
		$this->excludedTerms = $this->query->getExcludedTermIds();
		$this->includedTerms = $this->query->getIncludedTermIds();
		$this->requiredTerms = $this->query->getRequiredTermIds();

		// Load the list state.
		$this->setState('list.start', $input->get('limitstart', 0, 'uint'));
		$this->setState('list.limit', $input->get('limit', $app->get('list_limit', 20), 'uint'));

		/**
		 * Load the sort ordering.
		 * Currently this is 'hard' coded via menu item parameter but may not satisfy a users need.
		 * More flexibility was way more user friendly. So we allow the user to pass a custom value
		 * from the pool of fields that are indexed like the 'title' field.
		 * Also, we allow this parameter to be passed in either case (lower/upper).
		 */
		$order = $input->getWord('filter_order', $params->get('sort_order', 'relevance'));
		$order = StringHelper::strtolower($order);

		switch ($order)
		{
			case 'date':
				$this->setState('list.ordering', 'l.start_date');
				break;

			case 'price':
				$this->setState('list.ordering', 'l.list_price');
				break;

			case ($order === 'relevance' && !empty($this->includedTerms)) :
				$this->setState('list.ordering', 'm.weight');
				break;

			// Custom field that is indexed and might be required for ordering
			case 'title':
				$this->setState('list.ordering', 'l.title');
				break;

			default:
				$this->setState('list.ordering', 'l.link_id');
				break;
		}

		/**
		 * Load the sort direction.
		 * Currently this is 'hard' coded via menu item parameter but may not satisfy a users need.
		 * More flexibility was way more user friendly. So we allow to be inverted.
		 * Also, we allow this parameter to be passed in either case (lower/upper).
		 */
		$dirn = $input->getWord('filter_order_Dir', $params->get('sort_direction', 'desc'));
		$dirn = StringHelper::strtolower($dirn);

		switch ($dirn)
		{
			case 'asc':
				$this->setState('list.direction', 'ASC');
				break;

			default:
			case 'desc':
				$this->setState('list.direction', 'DESC');
				break;
		}

		// Set the match limit.
		$this->setState('match.limit', 1000);

		// Load the parameters.
		$this->setState('params', $params);

		// Load the user state.
		$this->setState('user.id', (int) $user->get('id'));
		$this->setState('user.groups', $user->getAuthorisedViewLevels());
	}

	/**
	 * Method to retrieve data from cache.
	 *
	 * @param   string   $id          The cache store id.
	 * @param   boolean  $persistent  Flag to enable the use of external cache. [optional]
	 *
	 * @return  mixed  The cached data if found, null otherwise.
	 *
	 * @since   2.5
	 */
	protected function retrieve($id, $persistent = true)
	{
		$data = null;

		// Use the internal cache if possible.
		if (isset($this->cache[$id]))
		{
			return $this->cache[$id];
		}

		// Use the external cache if data is persistent.
		if ($persistent)
		{
			$data = JFactory::getCache($this->context, 'output')->get($id);
			$data = $data ? unserialize($data) : null;
		}

		// Store the data in internal cache.
		if ($data)
		{
			$this->cache[$id] = $data;
		}

		return $data;
	}

	/**
	 * Method to store data in cache.
	 *
	 * @param   string   $id          The cache store id.
	 * @param   mixed    $data        The data to cache.
	 * @param   boolean  $persistent  Flag to enable the use of external cache. [optional]
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 */
	protected function store($id, $data, $persistent = true)
	{
		// Store the data in internal cache.
		$this->cache[$id] = $data;

		// Store the data in external cache if data is persistent.
		if ($persistent)
		{
			return JFactory::getCache($this->context, 'output')->store(serialize($data), $id);
		}

		return true;
	}
}
