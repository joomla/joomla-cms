<?php
/**
 * @package     Joomla.Libraries
 * @subpackage Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JModelList extends JModelItem
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



	public function __construct($config = array())
	{
		parent::__construct($config);

		// Add the ordering filtering fields white list.
		if (isset($config['filter_fields']))
		{
			foreach ($config['filter_fields'] AS $filter_field)
			{
				$hasName = (array_key_exists('name', $filter_field));
				$hasDataKey = (array_key_exists('dataKeyName', $filter_field));

				if ($hasName && $hasDataKey)
				{
					$name = $filter_field['name'];
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
	 * @param string $name name of the filter I.E. "title"
	 * @param string $dataKeyName name of the database key I.E. "a.title"
	 * @param booleen $sortable true to add to the filterFields array
	 * @param booleen $searchable true to add to the searchFields array
	 * @return JCmsModelList $this to allow for chaining
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

				$stateHasFilter = property_exists($this->state, $filterName);
				if ($stateHasFilter)
				{
					$validState = (!empty($this->state->$filterName) || is_numeric($this->state->$filterName));
					$isPublishFilter = ($filterName == 'filter.state');
						
					if ($validState && !$isPublishFilter)
					{
						$activeFilters[$filterField['dataKeyName']] = $this->state->get($filterName);
					}
				}
			}
		}

		return $activeFilters;
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
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	protected function getListQuery(JDatabaseQuery $query = null)
	{
		$db = $this->getDbo();

		if (is_null($query))
		{
			$query = $db->getQuery(true);
				
			$table = $this->getTable();
			$tableName = $table->getTableName();
				
			$query->select('a.*');
			$query->from($tableName.' AS a');
		}

		if (array_key_exists('a.state', $this->filterFields))
		{
			$state =  $this->getState('filter.state');
				
			if (is_numeric($state))
			{
				$query->where('a.state = '.(int) $state);
			}
			else if ($published === '')
			{
				$query->where('(a.state IN (0, 1))');
			}
		}

		$activeFilters = $this->getActiveFilters();
		foreach ($activeFilters AS $dataKeyName => $value)
		{
			$query->where($dataKeyName.' = '.$db->quote($value));
		}

		$search = $this->buildSearch();

		if ($search != '' && JString::strlen($search) != 0)
		{
			$query->where($search);
		}

		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol.' '.$orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		$db = $this->getDbo();
		// Load the list items.
		$query = $this->getListQuery();

		$start = $this->getStart();
		$limit = $this->getState('list.limit');

		$db->setQuery($query, $start, $limit);

		$items = $db->loadObjectList();

		return $items;
	}

	protected function buildSearch()
	{
		$db		= JFactory::getDbo();
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if(isset($this->searchFields))
			{
				$where = '';
				$searchInList = (array)$this->searchFields;

				$isExact = (JString::strrpos($search, '"'));

				if ($isExact)
				{
					$search = JString::substr($search, 1,-1);
					$where = '( ';
					foreach ((array)$searchInList as $search_field)
					{
						$cleanSearch = $db->Quote($db->escape($search, true));
						$where .=' '.$search_field['dataKeyName'].' = '.$cleanSearch.' OR';
					}
					$where = substr($where, 0,-3);
					$where.=')';
				}
				else
				{
					$where = '';
					$search = $db->Quote('%'.$db->escape($search, true).'%');

					$where = '( ';
					foreach ((array)$searchInList as $search_field)
					{
						$where .=' '.$search_field['dataKeyName'].' LIKE '.$search.' OR ';
					}
					$where = substr($where, 0,-3);
					$where.=')';
				}

				return $where;
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see JCmsModelBase::populateState()
	 */
	protected function populateState($ordering = null, $direction = null)
	{

		$context = $this->getContext();

		if (!$this->stateIsSet)
		{
			$app = JFactory::getApplication();
				
			$filters = $this->getUserStateFromRequest($context.'.filter', 'filter', array(), 'array');
				
			foreach ($filters AS $name => $value)
			{
				$this->setState('filter.'.$name, $value);
			}
				
			$limit = $this->getUserStateFromRequest($context.'list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
			$this->setState('list.limit', $limit);
				
			// Check if the ordering field is in the white list, otherwise use the incoming value.
			$orderColName = $app->getUserStateFromRequest($context . '.ordercol', 'filter_order', $ordering);
				
			if (!array_key_exists($orderColName, $this->filterFields))
			{
				$orderColName = $ordering;
				$app->setUserState($context . '.ordercol', $orderColName);
			}
				
			$this->setState('list.ordering', $orderColName);
				
			// Check if the ordering direction is valid, otherwise use the incoming value.
			$orderDir = $app->getUserStateFromRequest($context . '.orderdirn', 'filter_order_Dir', $direction);
				
			if (!in_array(strtoupper($orderDir),array('ASC', 'DESC')))
			{
				$orderDir = $direction;
				$app->setUserState($context . '.orderdirn', $orderDir);
			}
				
			$this->setState('list.direction', strtoupper($orderDir));
				
			$limitStartValue =  $app->getUserStateFromRequest($context . '.limitstart', 'limitstart', 0);
				
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
		jimport('joomla.html.pagination');
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new JPagination($this->getTotal(), $this->getStart(), $limit);

		return $page;
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
		$total = $this->getState('list.total', null);

		if ($total == null)
		{
			// Load the total.
			$query = $this->getListQuery();

			$total = (int) $this->_getListCount($query);
			$this->setState('list.total', $total);
		}

		return $total;
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

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal();

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		return $start;
	}
	/**
	 * Returns a record count for the query.
	 *
	 * @param   JDatabaseQuery $query  The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   12.2
	 */
	protected function _getListCount(JDatabaseQuery $query)
	{
		$db = $this->getDbo();

		//if this is a select and there are no GROUP BY or HAVING clause
		//Use COUNT(*) method to improve performance.

		$isSelect = ($query->type == 'select');
		$hasGroupClause = ($query->group === null);
		$hasHaveClause = ($query->having === null);
			
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
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param   string   $key        The key of the user state variable.
	 * @param   string   $request    The name of the variable passed in a request.
	 * @param   string   $default    The default value for the variable if not found. Optional.
	 * @param   string   $type       Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 * @param   boolean  $resetPage  If true, the limitstart in request is set to zero
	 *
	 * @return  The request user state.
	 *
	 * @since   12.2
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app = JFactory::getApplication();
		$input     = $app->input;

		$old_state = $app->getUserState($key);
		if(!is_null($old_state))
		{
			$cur_state = $old_state;
		}
		else
		{
			$cur_state = $default;
		}

		$new_state = $input->get($request, null, $type);

		$hasChanged = ($cur_state != $new_state);

		if ($hasChanged && $resetPage)
		{
			$input->set('limitstart', 0);
			$input->set('list.total', null);
				
		}

		// Save the new value only if it is set in this request.
		if ($new_state !== null)
		{
			$app->setUserState($key, $new_state);
		}
		else
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}

}