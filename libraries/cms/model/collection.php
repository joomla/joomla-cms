<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Cms Model Class for a collection of records (e.g. list views)
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
abstract class JModelCollection extends JModelRecord
{
	/**
	 * Valid filter fields.
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $filterFields = array();

	/**
	 * Array of field dataKeyNames to search in
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $searchFields = array();

	/**
	 * Public constructor
	 *
	 * @param  JRegistry         $state       The state for the model
	 * @param  JDatabaseDriver   $db          The database object
	 * @param  JEventDispatcher  $dispatcher  The dispatcher object
	 * @param  array             $config      Array of config variables
	 *
	 * @since  3.4
	 */
	public function __construct(JRegistry $state = null, JDatabaseDriver $db = null, JEventDispatcher $dispatcher = null, $config = array())
	{
		parent::__construct($state, $db, $dispatcher, $config);

		// Add the ordering filtering fields white list.
		if (isset($config['filter_fields']))
		{
			foreach ($config['filter_fields'] AS $filter_field)
			{
				$hasName    = (array_key_exists('name', $filter_field));
				$hasDataKey = (array_key_exists('dataKeyName', $filter_field));

				if ($hasName && $hasDataKey)
				{
					$name        = $filter_field['name'];
					$dataKeyName = $filter_field['dataKeyName'];

					if (array_key_exists('sortable', $filter_field))
					{
						$sortable = $filter_field['sortable'];
					}
					else
					{
						$sortable = false;
					}

					if (array_key_exists('searchable', $filter_field))
					{
						$searchable = $filter_field['searchable'];
					}
					else
					{
						$searchable = false;
					}

					$this->addFilterField($name, $dataKeyName, $sortable, $searchable);
				}
			}
		}
	}

	/**
	 * Method to add field to filterField and/or to searchFields arrays
	 *
	 * @param   string  $name         Name of the filter I.E. "title"
	 * @param   string  $dataKeyName  Name of the database key I.E. "a.title"
	 * @param   bool    $sortable     True to add to the filterFields array
	 * @param   bool    $searchable   True to add to the searchFields array
	 *
	 * @return  JModelCollection  $this to allow for chaining
	 * @since   3.4
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
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.4
	 */
	public function getItems()
	{
		$db = $this->getDb();
		// Load the list items.
		$query = $this->getListQuery();

		$start = $this->getStart();
		$limit = $this->getStateVar('list.limit', 0);

		$db->setQuery($query, $start, (int) $limit);

		$items = $db->loadObjectList();

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
	 * @param JDatabaseQuery $query
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	protected function getListQuery(JDatabaseQuery $query = null)
	{
		$db = $this->getDb();

		if (is_null($query))
		{
			$query = $db->getQuery(true);

			$table     = $this->getTable();
			$tableName = $table->getTableName();

			$query->select('a.*');
			$query->from($tableName . ' AS a');
		}

		if (array_key_exists('a.state', $this->filterFields))
		{
			$state = $this->getStateVar('filter.state');

			if (is_numeric($state))
			{
				$query->where('a.state = ' . (int) $state);
			}
			else if ($state === '')
			{
				$query->where('(a.state IN (0, 1))');
			}
		}

		$activeFilters = $this->getActiveFilters();

		foreach ($activeFilters AS $dataKeyName => $value)
		{
			$query->where($dataKeyName . ' = ' . $db->quote($value));
		}

		$query = $this->buildSearch($query);

		$orderCol  = $this->getStateVar('list.ordering');
		$orderDirn = $this->getStateVar('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to add a left join to the user table for record editor.
	 * @param   JDatabaseQuery  $query       The query object
	 * @param   string          $rootPrefix  The root prefix
	 *
	 * @return  JDatabaseQuery
	 */
	protected function addEditorQuery(JDatabaseQuery $query, $rootPrefix = 'a')
	{
		$query->select('editor.name AS editor');
		$query->join('LEFT', '#__users AS editor ON editor.id='.$rootPrefix.'.checked_out');

		return $query;
	}

	/**
	 * Method to add a left join to the view levels table for the assess title
	 *
	 * @param   JDatabaseQuery  $query
	 * @param   string          $onField the prefixed field name to join on
	 *
	 *
	 * @return JDatabaseQuery
	 */
	protected function addAccessTitle(JDatabaseQuery $query, $onField = 'a.access')
	{
		$query->select('access.title AS access_title');
		$query->join('LEFT', '#__viewlevels AS access ON access.id ='.$onField);

		return $query;
	}

	/**
	 * Function to get the active filters
	 *
	 * @return  array  Associative array in the format: array('filter_published' => 0)
	 *
	 * @since   3.2
	 */
	public function getActiveFilters()
	{
		$activeFilters = array();

		if (count($this->filterFields) != 0)
		{
			foreach ($this->filterFields as $filterField)
			{
				$filterName = 'filter.' . $filterField['name'];
				$state = $this->getState();
				$stateHasFilter = $state->exists($filterName);

				if ($stateHasFilter)
				{
					$validState      = (!empty($state->get($filterName)) || is_numeric($state->get($filterName)));
					$isPublishFilter = ($filterName == 'filter.state');

					if ($validState && !$isPublishFilter)
					{
						$activeFilters[$filterField['dataKeyName']] = $state->get($filterName);
					}
				}
			}
		}

		return $activeFilters;
	}

	/**
	 * Function to get build the search query
	 *
	 * @param  JDatabaseQuery  $query  The query object
	 *
	 * @return  string  Associative array in the format: array('filter_published' => 0)
	 *
	 * @since   3.2
	 */
	protected function buildSearch($query)
	{
		$db     = $this->getDb();
		$search = $this->getStateVar('filter.search');

		if (!empty($search) && isset($this->searchFields))
		{
			$searchInList = (array) $this->searchFields;

			$isExact = (JString::strrpos($search, '"'));

			if ($isExact)
			{
				$search = JString::substr($search, 1, -1);
				$where  = '( ';

				foreach ((array) $searchInList as $search_field)
				{
					$cleanSearch = $db->Quote($db->escape($search, true));
					$where .= ' ' . $search_field['dataKeyName'] . ' = ' . $cleanSearch . ' OR';
				}
				$where = substr($where, 0, -3);
				$where .= ')';

				$query->where($where);
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$where = '( ';

				foreach ((array) $searchInList as $search_field)
				{
					$where .= ' ' . $search_field['dataKeyName'] . ' LIKE ' . $search . ' OR ';
				}

				$where = substr($where, 0, -3);
				$where .= ')';

				$query->where($where);
			}
		}

		return $query; //no search found
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   12.2
	 */
	public function getStart()
	{
		$start = $this->getStateVar('list.start');
		$limit = $this->getStateVar('list.limit');
		$total = $this->getTotal();

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		return $start;
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 *
	 * @since   12.2
	 */
	public function getTotal()
	{
		// Get a storage key.
		$total = $this->getStateVar('list.total', null);

		if ($total == null)
		{
			// Load the total.
			$query = $this->getListQuery();

			$total = (int) $this->_getListCount($query);
			$this->state->set('list.total', $total);
		}

		return $total;
	}

	/**
	 * Returns a record count for the query.
	 *
	 * @param   JDatabaseQuery $query The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   12.2
	 */
	protected function _getListCount(JDatabaseQuery $query)
	{
		$db = $this->getDb();

		// If this is a select and there are no GROUP BY or HAVING clause
		// Use COUNT(*) method to improve performance.

		$isSelect       = ($query->type == 'select');
		$hasGroupClause = ($query->group === null);
		$hasHaveClause  = ($query->having === null);

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
	 *
	 * @since   12.2
	 */
	public function getPagination()
	{
		// Create the pagination object.
		$limit = (int) $this->getStateVar('list.limit') - (int) $this->getStateVar('list.links');
		$page  = new JPagination($this->getTotal(), $this->getStart(), $limit);

		return $page;
	}

	/**
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param   string  $key       The key of the user state variable.
	 * @param   string  $request   The name of the variable passed in a request.
	 * @param   string  $default   The default value for the variable if not found. Optional.
	 * @param   string  $type      Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 * @param   boolean $resetPage If true, the limitstart in request is set to zero
	 *
	 * @return  mixed  The request user state.
	 *
	 * @since   12.2
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$oldState = $app->getUserState($key);

		if (!is_null($oldState))
		{
			$cur_state = $oldState;
		}
		else
		{
			$cur_state = $default;
		}

		$newState = $input->get($request, null, $type);

		$hasChanged = ($cur_state != $newState);

		if ($hasChanged && $resetPage)
		{
			$input->set('limitstart', 0);
			$input->set('list.total', null);
		}

		// Save the new value only if it is set in this request.
		if ($newState !== null)
		{
			$app->setUserState($key, $newState);
		}
		else
		{
			$newState = $cur_state;
		}

		return $newState;
	}

	/**
	 * @see JModelCms::populateState()
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$context = $this->getContext();

		if (!$this->stateIsSet)
		{
			$app = JFactory::getApplication();

			$filters = $app->getUserStateFromRequest($context . '.filter', 'filter', array(), 'array');

			foreach ($filters AS $name => $value)
			{
				$this->state->set('filter.' . $name, $value);
			}

			$limit = $app->getUserStateFromRequest($context . 'list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
			$this->state->set('list.limit', $limit);

			// Check if the ordering field is in the white list, otherwise use the incoming value.
			$orderColName = $app->getUserStateFromRequest($context . '.ordercol', 'filter_order', $ordering);

			if (!array_key_exists($orderColName, $this->filterFields))
			{
				$orderColName = $ordering;
				$app->setUserState($context . '.ordercol', $orderColName);
			}

			$this->state->set('list.ordering', $orderColName);

			// Check if the ordering direction is valid, otherwise use the incoming value.
			$orderDir = $app->getUserStateFromRequest($context . '.orderdirn', 'filter_order_Dir', $direction);

			if (!in_array(strtoupper($orderDir), array('ASC', 'DESC')))
			{
				$orderDir = $direction;
				$app->setUserState($context . '.orderdirn', $orderDir);
			}

			$this->state->set('list.direction', strtoupper($orderDir));

			$limitStartValue = $app->getUserStateFromRequest($context . '.limitstart', 'limitstart', 0, 'int');

			if ($limit != 0)
			{
				$limitStart = (floor($limitStartValue / $limit) * $limit);
			}
			else
			{
				$limitStart = 0;
			}

			$this->state->set('list.start', $limitStart);

			parent::populateState($ordering, $direction);
		}
	}
}
