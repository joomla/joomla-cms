<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JModelCollection extends JModelData
{
	/**
	 * Valid filter fields.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $filterFields = array();

	/**
	 * Array of field dataKeyNames to search in
	 * @var array
	 */
	protected $searchFields = array();

	/**
	 * The name of the field to use for date filtering
	 * @var string
	 */
	protected $dateFilterField = null;

	/**
	 * Pagination class
	 * @var JPagination
	 */
	protected $pagination = null;


	public function __construct($config = array())
	{
		parent::__construct($config);

		// Add the ordering filtering fields white list.
		if (isset($config['filter_fields']))
		{
			$this->prepareFilterFields($config['filter_fields']);
		}
	}

	/**
	 * Method to loop through the filterFields and add all valid fields to the filters
	 *
	 * @param $filterFields
	 * @return void
	 */
	protected function prepareFilterFields($filterFields)
	{
		foreach ($filterFields AS $filterField)
		{
			$hasName    = (array_key_exists('name', $filterField));
			$hasDataKey = (array_key_exists('dataKeyName', $filterField));

			if (!$hasName || !$hasDataKey)
			{
				//invalid filters are ignored.
				continue;
			}

			$name        = $filterField['name'];
			$dataKeyName = $filterField['dataKeyName'];

			$sortable = false;
			if (array_key_exists('sortable', $filterField))
			{
				$sortable = $filterField['sortable'];
			}

			$searchable = false;
			if (array_key_exists('searchable', $filterField))
			{
				$searchable = $filterField['searchable'];
			}

			$this->addFilterField($name, $dataKeyName, $sortable, $searchable);
		}
	}

	/**
	 * Method to add field to filterField and/or to searchFields arrays
	 *
	 * @param string $name        name of the filter I.E. "title"
	 * @param string $dataKeyName name of the database key I.E. "a.title"
	 * @param bool   $sortable    true to add to the filterFields array
	 * @param bool   $searchable  true to add to the searchFields array
	 *
	 * @return $this to allow for chaining
	 */
	public function addFilterField($name, $dataKeyName, $sortable = true, $searchable = false)
	{
		$filterField = array('name' => $name, 'dataKeyName' => $dataKeyName);

		if ($sortable)
		{
			$this->filterFields[$dataKeyName] = $filterField;
		}

		if ($searchable)
		{
			$this->searchFields[$dataKeyName] = $filterField;
		}

		return $this;
	}

	/**
	 * Method to remove a filter from the filter fields
	 * This allows you to disable sensitive filter types when needed.
	 *
	 * @param $dataKeyName
	 */
	public function removeFilterField($dataKeyName)
	{
		unset($this->filterFields[$dataKeyName]);
		unset($this->searchFields[$dataKeyName]);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @param string $key   the name of a field on which to key the result array.
	 * @param string $class the class name of the return item default is JRegistryCms
	 *
	 * @param bool   $appendFilters $appendFilters if set to false, will only return the main query.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getList($key = null, $class = 'JRegistryCms', $appendFilters = true)
	{
		$query = $this->getListQuery();

		$this->observers->update('onBeforeGetList', array($this, $query, $key, $class));

		if($appendFilters)
		{
			$this->addQueryFilters($query);
		}

		$limit = (int)$this->getState('list.limit', 0);
		$total = $this->getTotal($query);
		$start = $this->getStart($limit, $total);



		$dbo = $this->getDbo();
		$dbo->setQuery($query, $start, $limit);
		$items = $dbo->loadObjectList($key ,$class);

		$this->observers->update('onAfterGetList', array($this,$query, $items));

		return $items;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * If you send a $query object to this function it will append active filters before returning.
	 *
	 * If you don't send a $query it will return a $query object with:
	 *
	 * $query->select('a.*');
	 * $query->from($tableName.' AS a');
	 *
	 * before appending the active filters.
	 *
	 * @param  JDatabaseQuery $query
	 *
	 * @param  bool           $appendFilters if set to false, will only return the main query.
	 *
	 * @return JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 */
	protected function getListQuery(JDatabaseQuery $query = null, $appendFilters = true)
	{
		if (is_null($query))
		{
			$dbo = $this->getDbo();
			$query = $dbo->getQuery(true);

			$query->select('a.*');
			$query->from($this->getTableName() . ' AS a');
		}

		return $query;
	}

	/**
	 * Method to add search and filter clauses to a query
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return void
	 */
	protected function addQueryFilters(JDatabaseQuery $query)
	{
		$dbo = $this->getDbo();

		$activeFilters = $this->getActiveFilters();

		foreach ($activeFilters AS $dataKeyName => $value)
		{
			$query->where($dataKeyName . ' = ' . $dbo->quote($value));
		}

		$search = $this->buildSearch();

		if ($search != '' && JString::strlen($search) != 0)
		{
			$query->where($search);
		}


		$fromFilter = $this->getState('filter.from', null);
		$toFilter = $this->getState('filter.to', null);

		$hasFromRange = (!empty($fromFilter) && $fromFilter != $dbo->getNullDate());

		if($hasFromRange && !is_null($this->dateFilterField))
		{
			$fromFilter = new JDate($fromFilter);

			if(empty($toFilter) || $toFilter == $dbo->getNullDate())
			{
				$toDate = new JDate();
			}
			else
			{
				$toDate = new JDate($toFilter);
			}

			$toFilter = new JDate($toDate->format('Y-m-d').' 23:59:59');

			$query->where($this->dateFilterField.' BETWEEN '.$dbo->quote($fromFilter->toSql()).' AND '.$dbo->quote($toFilter->toSql()));
		}

		$orderCol  = $this->state->getProperty('list.ordering');

		if(!array_key_exists($orderCol, $this->filterFields))
		{
			$orderCol = false;
			$this->setState('list.ordering', null);
		}


		$orderDirn = $this->state->getProperty('list.direction');
		if ($orderCol && $orderDirn)
		{
			$query->order($dbo->escape($orderCol . ' ' . $orderDirn));
		}

	}

	/**
	 * Method to add a left join to the user table for record editor.
	 *
	 * @param JDatabaseQuery $query
	 * @param string         $rootPrefix
	 *
	 * @return JDatabaseQuery
	 */
	protected function addEditorQuery(JDatabaseQuery $query, $rootPrefix = 'a')
	{
		$query->select('editor.name AS editor');
		$query->join('LEFT', '#__users AS editor ON editor.id=' . $rootPrefix . '.checked_out');

		return $query;
	}

	/**
	 * Method to add a left join to the viewlevels table for the assess title
	 *
	 * @param JDatabaseQuery $query
	 * @param string         $onField the prefixed field name to join on
	 *
	 * @return JDatabaseQuery
	 */
	protected function addAccessTitle(JDatabaseQuery $query, $onField = 'a.access')
	{
		$query->select('access.title AS access_title');
		$query->join('LEFT', '#__viewlevels AS access ON access.id =' . $onField);

		return $query;
	}

	/**
	 * Method to add a left joint to the users table for the record owners name
	 *
	 * @param JDatabaseQuery $query
	 * @param string         $onField
	 *
	 * @return JDatabaseQuery
	 */
	protected function addOwnerName(JDatabaseQuery $query, $onField = 'a.owner')
	{
		$query->select('owner.name AS owner_name');
		$query->join('LEFT', '#__users AS owner ON owner.id = ' . $onField);

		return $query;
	}

	/**
	 * Function to get the active filters
	 *
	 * @return  array  Associative array in the format: array('filter_published' => 0)
	 */
	public function getActiveFilters()
	{
		$activeFilters = array();


		if (count($this->filterFields) == 0) // nothing active send back empty array
		{
			return $activeFilters;
		}

		foreach ($this->filterFields as $filterField)
		{
			$filterName = 'filter.' . $filterField['name'];
			$stateHasFilter = property_exists($this->state, $filterName);

			if ($stateHasFilter)
			{
				$filterValue = $this->getState($filterName, null);
				if (!empty($filterValue))
				{
					$activeFilters[$filterField['dataKeyName']] = $filterValue;
				}

			}
		}

		return $activeFilters;
	}

	/**
	 * Method to build a SQL conditional from the search filter
	 *
	 * @return null|string
	 */
	protected function buildSearch()
	{
		$search = $this->getState('filter.search');

		if (empty($search) || count($this->searchFields) == 0)
		{
			return null; //no search found
		}

		$dbo    = $this->getDbo();
		$isExactMatch = (JString::strrpos($search, '"'));
		if ($isExactMatch)
		{
			$glue = ' = ';
			$search = JString::substr($search, 1, -1); // remove quotes
			$cleanSearch = $dbo->Quote($dbo->escape($search, true));
		}
		else
		{
			$glue = ' LIKE ';
			$cleanSearch = $dbo->Quote('%' . $dbo->escape($search, true) . '%'); // add wildcards
		}

		$searchInList = (array) $this->searchFields;
		$where  = '( ';
		foreach ($searchInList as $search_field)
		{
			$where .= ' ' . $search_field['dataKeyName'] . $glue . $cleanSearch . ' OR ';
		}

		$where = substr($where, 0, -3);
		$where .= ')';

		return $where;

	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @param int $limit the current list limit
	 * @param int $total number of items in the data set
	 *
	 * @return  integer  The starting number of items available in the data set.
	 */
	protected function getStart($limit, $total)
	{
		$start = $this->getState('list.start');

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
			$this->setState('list.start', $start);
		}

		return $start;
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @param JDatabaseQuery $query for the data set
	 *
	 * @return  integer  The total number of items available in the data set.
	 */
	protected function getTotal(JDatabaseQuery $query)
	{
		$total = $this->getState('list.total', null);

		if ($total == null)
		{
			$total = (int) $this->_getListCount($query);
			$this->setState('list.total', $total);
		}

		return $total;
	}

	/**
	 * Returns a record count for the query.
	 *
	 * @param   JDatabaseQuery $query The query.
	 *
	 * @return  integer  Number of rows for query.
	 */
	protected function _getListCount(JDatabaseQuery $query)
	{
		$db = $this->getDbo();

		//if this is a select and there are no GROUP BY or HAVING clause
		//Use COUNT(*) method to improve performance.

		$isSelect       = ($query->type == 'select');
		$hasGroupClause = ($query->group !== null);
		$hasHaveClause  = ($query->having !== null);


		if ($isSelect && !$hasGroupClause && !$hasHaveClause)
		{
			$query = clone $query;
			$query->clear('select')->clear('order')->select('COUNT(*)');

			$db->setQuery($query);

			return (int) $db->loadResult();
		}

		// Else use brute-force and count all returned results.
		$db->setQuery($query);
		$db->execute();

		return (int) $db->getNumRows();
	}

	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  JPagination  A JPagination object for the data set.
	 */
	public function getPagination()
	{
		if(is_null($this->pagination))
		{
			jimport('joomla.html.pagination');
			$total = (int) $this->getState('list.total');
			$start = (int) $this->getState('list.start');
			$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
			$this->pagination = new JPagination($total, $start, $limit);
		}

		return $this->pagination;
	}

	/**
	 * This method loads the filters, sort ordering, and list limit from the user session into the model state.
	 * @param string $ordering
	 * @param string $direction
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if ($this->stateIsSet) // no need to populate state
		{
			return;
		}

		$app = JFactory::getApplication();

		$context = $this->getContext();
		$filters = $app->getUserStateFromRequest($context . '.filter', 'filter', array(), 'array');

		foreach ($filters AS $name => $value)
		{
			$this->setState('filter.' . $name, trim($value));
		}

		$limit = $app->getUserStateFromRequest($context . 'list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
		$this->setState('list.limit', $limit);

		//Ordering
		$orderColName = $app->getUserStateFromRequest($context . '.ordercol', 'filter_order', $ordering);
		$app->setUserState($context . '.ordercol', $orderColName);
		$this->setState('list.ordering', $orderColName);

		// Check if the ordering direction is valid, otherwise use the incoming value.
		$orderDir = $app->getUserStateFromRequest($context . '.orderdirn', 'filter_order_Dir', $direction);

		if (!in_array(strtoupper($orderDir), array('ASC', 'DESC')))
		{
			$orderDir = $direction;
			$app->setUserState($context . '.orderdirn', $orderDir);
		}

		$this->setState('list.direction', strtoupper($orderDir));

		$limitStartValue = $app->getUserStateFromRequest($context . '.limitstart', 'limitstart', 0, 'int');

		if ($limit != 0)
		{
			$limitStart = (floor($limitStartValue / $limit) * $limit);
		}
		else
		{
			$limitStart = 0;
		}

		$this->setState('list.start', $limitStart);

		parent::populateState($ordering, $direction);
	}
}