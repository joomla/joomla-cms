<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Terms model class for Finder.
 *
 * @since  __DEPLOY_VERSION__
 */
class FinderModelTerms extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An associative array of configuration settings. [optional]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @see     JControllerLegacy
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'language',
				'link',
				'links',
				'shard',
				'state',
				'stem',
				'soundex',
				'term',
				'type',
				'weight',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getListQuery()
	{
		$termIds = array();
		$db = $this->getDbo();

		// Get model state variables.
		$search	   = $this->getState('filter.search');
		$shard	   = (int) $this->getState('filter.shard');
		$soundex   = $this->getState('filter.soundex');
		$listOrder = $this->getState('list.ordering', 'term');
		$listDir   = $this->getState('list.direction', 'ASC');

		// Construct filter criteria specification.
		$filterCriteria = array(
			'type'  => (int) $this->getState('filter.type'),
			'link'  => (int) $this->getState('filter.link'),
			'state' => $this->getState('filter.state'),
			);

		// If there is a search term, determine the term ids up-front.
		// This avoids doing a wildcard search within the union queries which would consume a lot of memory.
		if (!empty($search) || !empty($soundex))
		{
			$termQuery = $db->getQuery(true)
				->select('DISTINCT t.term_id')
				->from($db->quoteName('#__finder_terms', 't'));

			if (!empty($search))
			{
				$searchSql = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$termQuery->where(array($db->quoteName('t.term') . ' LIKE ' . $searchSql, $db->quoteName('t.stem') . ' LIKE ' . $searchSql), 'OR');
			}

			if (!empty($soundex))
			{
				$termQuery->where($db->quoteName('t.soundex') . ' = ' . $db->quote($soundex));
			}

			$filterCriteria['termIds'] = $db->setQuery($termQuery)->loadColumn();

			// If no terms were found bail out early with a dummy query.
			if (empty($filterCriteria['termIds']))
			{
				return $this->getShardQuery(0, $filterCriteria)->where('false');
			}
		}

		// Construct ordering clause.
		$ordering = $db->escape($listOrder) . ' ' . $db->escape($listDir);

		// If we are filtering by shard, we don't need to do a union.
		if (!empty($shard))
		{
			return $this->getShardQuery($shard, $filterCriteria)->order($ordering);
		}

		// For a search across all shards, start with shard 0.
		$query = $this->getShardQuery(0, $filterCriteria)->order($ordering);

		// Union the other shards together to form a single query.
		for ($i = 1; $i <= 15; $i++)
		{
			$query->union($this->getShardQuery($i, $filterCriteria));
		}

		return $query;
	}

	/**
	 * Return a query for a single shard.
	 *
	 * @param   integer  $shard           Shard id.
	 * @param   array    $filterCriteria  Array of filter criteria.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getShardQuery($shard, array $filterCriteria)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('lt.term_id, lt.weight, t.term, t.stem, t.links, t.common, t.soundex, t.language')
			->select($db->quote((string) dechex($shard)) . ' AS shard')
			->from($db->quoteName('#__finder_links_terms' . dechex($shard), 'lt'))
			->leftjoin($db->quoteName('#__finder_terms', 't') . ' ON t.term_id = lt.term_id');

		// Searching for a particular link.
		if ($filterCriteria['link'])
		{
			$query->where($db->quoteName('lt.link_id') . ' = ' . (int) $filterCriteria['link']);
		}

		// Searching for something involving the links table.
		if (!empty($filterCriteria['type']) || is_numeric($filterCriteria['state']))
		{
			$query->leftjoin($db->quoteName('#__finder_links', 'l') . ' ON l.link_id = lt.link_id');

			// Searching for a particular link state.
			if (is_numeric($filterCriteria['state']))
			{
				$query->where($db->quoteName('l.published') . ' = ' . (int) $filterCriteria['state']);
			}

			// Searching for a particular content type.
			if (!empty($filterCriteria['type']))
			{
				$query->where($db->quoteName('l.type_id') . ' = ' . $db->quote($filterCriteria['type']));
			}
		}

		// Searching for a term or terms, use the term ids.
		if (!empty($filterCriteria['termIds']))
		{
			$query->where($db->quoteName('t.term_id') . ' IN (' . implode(',', $filterCriteria['termIds']) . ')');
		}

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id. [optional]
	 *
	 * @return  string  A store id.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.link');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.shard');
		$id .= ':' . $this->getState('filter.soundex');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get data regarding the content item.
	 *
	 * @return  JObject  Data about the content item.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('l.*, type.title AS type')
			->from($db->quoteName('#__finder_links', 'l'))
			->leftjoin($db->quoteName('#__finder_types', 'type') . ' ON type.id = l.type_id')
			->where($db->quoteName('l.link_id') . ' = ' . (int) $this->getState('filter.link'));
		$item = $db->setQuery($query)->loadObject();

		return $item;
	}

	/**
	 * Method to auto-populate the model state.  Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field. [optional]
	 * @param   string  $direction  An optional direction. [optional]
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = 'term', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'cmd'));
		$this->setState('filter.shard', $this->getUserStateFromRequest($this->context . '.filter.shard', 'filter_shard', '', 'cmd'));
		$this->setState('filter.soundex', $this->getUserStateFromRequest($this->context . '.filter.soundex', 'filter_soundex', '', 'cmd'));
		$this->setState('filter.link', $this->getUserStateFromRequest($this->context . '.filter.link', 'filter_link', '', 'int'));

		// List state information.
		parent::populateState($ordering, $direction);
	}
}
