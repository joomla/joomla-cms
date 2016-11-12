<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Search.finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

define('FINDER_PATH_INDEXER', JPATH_ADMINISTRATOR . '/components/com_search/helpers/indexer');
JLoader::register('SearchIndexerHelper', FINDER_PATH_INDEXER . '/helper.php');
JLoader::register('SearchIndexerQuery', FINDER_PATH_INDEXER . '/query.php');
JLoader::register('SearchIndexerResult', FINDER_PATH_INDEXER . '/result.php');
JLoader::register('SearchIndexerStemmer', FINDER_PATH_INDEXER . '/stemmer.php');

/**
 * Finder search plugin.
 *
 * @since  4.0
 */
class PlgSearchFinder extends JPlugin
{
	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   1.6
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'finder' => 'PLG_SEARCH_FINDER_FINDER'
		);

		return $areas;
	}

	/**
	 * Search finder
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search it to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		// Get view data.
		$this->_db = JFactory::getDbo();
		$this->state = new JObject;
		$state = $this->populateState($text);
		$results = $this->getResults();

		return $results;
	}

	/**
	 * Context string for the model type
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'com_finder.search';

	/**
	 * The query object is an instance of SearchIndexerQuery which contains and
	 * models the entire search query including the text input; static and
	 * dynamic taxonomy filters; date filters; etc.
	 *
	 * @var    SearchIndexerQuery
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
	 * @return  array  An array of SearchIndexerResult objects.
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

		// Get the row data.
		$items = $this->getResultsData();

		// Check the data.
		if (empty($items))
		{
			return null;
		}

		// Create the query to get the search results.
		$db = $this->_db;
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

		// Return the results.
		return $results;
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
		// Set variables
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Create a new query object.
		$db = JFactory::getDbo();
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
			if ($this->query->when1 == 'before')
			{
				$query->where($db->quoteName('l.start_date') . ' <= ' . $date1);
			}
			elseif ($this->query->when1 == 'after')
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
			if ($this->query->when2 == 'before')
			{
				$query->where($db->quoteName('l.start_date') . ' <= ' . $date2);
			}
			elseif ($this->query->when2 == 'after')
			{
				$query->where($db->quoteName('l.start_date') . ' >= ' . $date2);
			}
			else
			{
				$query->where($db->quoteName('l.start_date') . ' = ' . $date2);
			}
		}
		// Filter by language
		if ($this->state->get('filter.language'))
		{
			$query->where('l.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ', ' . $db->quote('*') . ')');
		}

		// Return a copy of the query object.
		return $query;
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
		// Get the result ordering and direction.
		$ordering = $this->state->get('list.ordering', 'l.start_date');
		$direction = $this->state->get('list.direction', 'DESC');

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
			$this->_db->setQuery($base, (int) $this->state->get('list.start'), (int) $this->state->get('list.limit'));
			return $this->_db->loadObjectList('link_id');
		}

		/*
		 * If there are optional or required search terms in the query, the
		 * process of getting the results is more complicated.
		 */
		$start = 0;
		$limit = (int) $this->state->get('match.limit');
		$items = array();
		$results = array();
		$sorted = array();
		$allIDs = array();
		$excluded = $this->getExcludedLinkIds();

		foreach ($this->includedTerms as $token => $ids)
		{
			$allIDs = array_merge($allIDs, $ids);
		}

		// Adjust the query to join on the appropriate mapping table.
		$query = clone($base);
		$query->join('INNER', $this->_db->quoteName('#__finder_links_terms') . ' AS m ON m.link_id = l.link_id')
			->where('m.term_id IN (' . implode(',', $allIDs) . ')');

		// Load the results from the database.
		$this->_db->setQuery($query, $start, $limit);
		$results = $this->_db->loadObjectList('link_id');

		// Store this set in cache.
		$this->store($setId, $results);

		// The data is keyed by link_id to ease caching, we don't need it till later.
		$results = array_values($results);

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

		$items = $sorted;

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
			return $sorted;
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
			// Check if the token was matched.
			if (empty($required))
			{
				return null;
			}
			// Load the data from the database.
			else
			{
				// Setup containers in case we have to make multiple passes.
				$reqStart = 0;
				$reqTemp = array();

				// Adjust the query to join on the appropriate mapping table.
				$query = clone($base);
				$query->join('INNER', $this->_db->quoteName('#__finder_links_terms') . ' AS m ON m.link_id = l.link_id')
					->where('m.term_id IN (' . implode(',', $required) . ')');

				// Load the results from the database.
				$this->_db->setQuery($query, $reqStart, $limit);
				$temp = $this->_db->loadObjectList('link_id');

				// Merge the matching set for this token.
				$reqTemp = $reqTemp + $temp;

				// Increment the term offset.
				$reqStart += $limit;
			}

			// Remove any items that do not match the required term.
			$sorted = array_intersect_key($sorted, $reqTemp);
		}

		// If we need more items and they're available, make another pass.
		if (count($sorted) < ($this->state->get('list.start') + $this->state->get('list.limit')))
		{
			// Increment the batch starting point.
			$start += $limit;

			// Merge the found items.
			$items = array_merge($items, $sorted);
		}

		// Return the requested set.
		return $items;
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
		$allIDs = array();

		/*
		 * Iterate through the excluded search terms and group them by mapping
		 * table suffix. This ensures that we never have to do more than 16
		 * queries to get a batch. This may seem like a lot but it is rarely
		 * anywhere near 16 because of the improved mapping algorithm.
		 */
		foreach ($this->excludedTerms as $token => $id)
		{
			$allIDs[] = (int) $id;
		}

		/*
		 * Iterate through the mapping groups and load the excluded links ids
		 * from each mapping table.
		 */
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the query to get the links ids.
		$query->clear()
			->select('link_id')
			->from($db->quoteName('#__finder_links_terms'))
			->where($db->quoteName('term_id') . ' IN (' . implode(',', $allIDs) . ')')
			->group($db->quoteName('link_id'));

		// Load the link ids from the database.
		$db->setQuery($query);
		$temp = $db->loadColumn();

		// Merge the link ids.
		$links = array_merge($links, $temp);

		// Sanitize the link ids.
		$links = array_unique($links);
		$links = ArrayHelper::toInteger($links);

		// Push the link ids into cache.
		$this->store($store, $links);

		return $links;
	}

	/**
	 * Method to auto-populate the model state.  Calling state->get in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field. [optional]
	 * @param   string  $direction  An optional direction. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function populateState($search)
	{
		// Get the configuration options.
		$app = JFactory::getApplication();
		$input = $app->input;
		$params = JComponentHelper::getParams('com_search');
		$user = JFactory::getUser();
		$filter = JFilterInput::getInstance();

		$this->state->set('filter.language', JLanguageMultilang::isEnabled());

		// Setup the stemmer.
		if ($params->get('stem', 1) && $params->get('stemmer', 'porter_en'))
		{
			SearchIndexerHelper::$stemmer = SearchIndexerStemmer::getInstance($params->get('stemmer', 'porter_en'));
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
		$options['input'] = $search;

		// Get the query language.
		$options['language'] = $request->getCmd('l', $params->get('l', ''));

		// Get the start date and start date modifier filters.
		$options['date1'] = $request->getString('d1', $params->get('d1', ''));
		$options['when1'] = $request->getString('w1', $params->get('w1', ''));

		// Get the end date and end date modifier filters.
		$options['date2'] = $request->getString('d2', $params->get('d2', ''));
		$options['when2'] = $request->getString('w2', $params->get('w2', ''));

		// Load the query object.
		$this->query = new SearchIndexerQuery($options);

		// Load the query token data.
		$this->excludedTerms = $this->query->getExcludedTermIds();
		$this->includedTerms = $this->query->getIncludedTermIds();
		$this->requiredTerms = $this->query->getRequiredTermIds();

		// Load the list state.
		$this->state->set('list.start', $input->get('limitstart', 0, 'uint'));
		$this->state->set('list.limit', $input->get('limit', $app->get('list_limit', 20), 'uint'));

		/* Load the sort ordering.
		 * Currently this is 'hard' coded via menu item parameter but may not satisfy a users need.
		 * More flexibility was way more user friendly. So we allow the user to pass a custom value
		 * from the pool of fields that are indexed like the 'title' field.
		 * Also, we allow this parameter to be passed in either case (lower/upper).
		 */
		$order = $input->getWord('filter_order', $params->get('sort_order', 'relevance'));
		$order = JString::strtolower($order);
		switch ($order)
		{
			case 'date':
				$this->state->set('list.ordering', 'l.start_date');
				break;

			case 'price':
				$this->state->set('list.ordering', 'l.list_price');
				break;

			case ($order == 'relevance' && !empty($this->includedTerms)):
				$this->state->set('list.ordering', 'm.weight');
				break;

			// Custom field that is indexed and might be required for ordering
			case 'title':
				$this->state->set('list.ordering', 'l.title');
				break;

			default:
				$this->state->set('list.ordering', 'l.link_id');
				break;
		}

		/* Load the sort direction.
		 * Currently this is 'hard' coded via menu item parameter but may not satisfy a users need.
		 * More flexibility was way more user friendly. So we allow to be inverted.
		 * Also, we allow this parameter to be passed in either case (lower/upper).
		 */
		$dirn = $input->getWord('filter_order_Dir', $params->get('sort_direction', 'desc'));
		$dirn = JString::strtolower($dirn);
		switch ($dirn)
		{
			case 'asc':
				$this->state->set('list.direction', 'ASC');
				break;

			default:
			case 'desc':
				$this->state->set('list.direction', 'DESC');
				break;
		}

		// Set the match limit.
		$this->state->set('match.limit', 1000);

		// Load the parameters.
		$this->state->set('params', $params);

		// Load the user state.
		$this->state->set('user.id', (int) $user->get('id'));
		$this->state->set('user.groups', $user->getAuthorisedViewLevels());
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
	/**protected function retrieve($id, $persistent = true)
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
	}**/

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
