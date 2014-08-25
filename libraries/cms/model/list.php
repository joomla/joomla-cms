<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

/**
 * Model class for handling lists of items.
 *
 * @package     Joomla.CMS
 * @subpackage  Model
 * @since       3.5
 */
class JCmsModelList extends JCmsModel
{

	/**
	 * Name of state field name, usually be tbl.state or tbl.published
	 * 
	 * @var string
	 */
	protected $stateField;

	/**
	 * List of fields which will be used for searching data from database table
	 *
	 * @var array
	 */
	protected $searchFields = array();

	/**
	 * The query object used to get data
	 *
	 * @var JDatabaseQuery
	 */
	protected $query;

	/**
	 * List total
	 *
	 * @var integer
	 */
	protected $total;

	/**
	 * Model list data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Pagination object
	 *
	 * @var JPagination
	 */
	protected $pagination;

	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *        	  	
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$app = JFactory::getApplication();
		$this->query = $this->db->getQuery(true);
		$context = $this->option . '.' . $this->name . '.';
		$table = $this->getTable();
		$fields = array_keys($table->getFields());
		if (in_array('ordering', $fields))
		{
			$defaultOrdering = 'tbl.ordering';
		}
		else
		{
			$defaultOrdering = 'tbl.id';
		}
		if (in_array('state', $fields))
		{
			$this->stateField = 'tbl.state';
		}
		else
		{
			$this->stateField = 'tbl.published';
		}
		if (isset($config['ignore_session']))
		{
			$this->state->insert('limit', 'int', $app->getCfg('list_limit'))
				->insert('limitstart', 'int', 0)
				->insert('filter_order', 'cmd', $defaultOrdering)
				->insert('filter_order_Dir', 'word', 'asc')
				->insert('filter_search', 'string')
				->insert('filter_state', 'string')
				->insert('filter_access', 'int', 0)
				->insert('filter_language', 'string')
				->insert('filter_tag', 'string', '');
		}
		else
		{
			$this->state->insert('limit', 'int', $app->getUserStateFromRequest($context . 'limit', 'limit', $app->getCfg('list_limit')))
				->insert('limitstart', 'int', $app->getUserStateFromRequest($context . 'limitstart', 'limitstart', 0))
				->insert('filter_order', 'cmd', $app->getUserStateFromRequest($context . 'filter_order', 'filter_order', $defaultOrdering))
				->insert('filter_order_Dir', 'word', $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', 'asc'))
				->insert('filter_search', 'string', $app->getUserStateFromRequest($context . 'filter_search', 'filter_search'))
				->insert('filter_state', 'string', $app->getUserStateFromRequest($context . 'filter_state', 'filter_state'))
				->insert('filter_access', 'int', $app->getUserStateFromRequest($context . 'filter_access', 'filter_access'))
				->insert('filter_language', 'string', $app->getUserStateFromRequest($context . 'filter_language', 'filter_language'))
				->insert('filter_tag', 'string', $app->getUserStateFromRequest($context . 'filter_tag', 'filter_tag'));
		}
		
		if (isset($config['search_fields']))
		{
			$this->searchFields = (array) $config['search_fields'];
		}
		else
		{
			// Build the search field array automatically, basically, we should search based on name, title, description if these fields are available			
			if (in_array('name', $fields))
			{
				$this->searchFields[] = 'tbl.name';
			}
			if (in_array('title', $fields))
			{
				$this->searchFields[] = 'tbl.title';
			}
			if (in_array('alias', $fields))
			{
				$this->searchFields[] = 'tbl.alias';
			}
			if (in_array('description', $fields))
			{
				$this->searchFields[] = 'tbl.description';
			}
		}
	}

	/**
	 * Get a list of items
	 *
	 * @return array
	 */
	public function getData()
	{
		if (empty($this->data))
		{
			$db = $this->getDbo();
			$this->_buildQueryColumns()
				->_buildQueryFrom()
				->_buildQueryJoins()
				->_buildQueryWhere()
				->_buildQueryGroup()
				->_buildQueryHaving()
				->_buildQueryOrder();
			$db->setQuery($this->query, $this->state->limitstart, $this->state->limit);
			$this->data = $db->loadObjectList();
		}
		
		return $this->data;
	}

	/**
	 * Get total record
	 *
	 * @return integer Number of records
	 *        
	 */
	public function getTotal()
	{
		if (empty($this->total))
		{
			$db = $this->getDbo();
			$query = clone ($this->query);
			$query->clear('select')
				->clear('order')
				->clear('limit')
				->select('COUNT(*)');
			$db->setQuery($query);
			$this->total = (int) $db->loadResult();
		}
		
		return $this->total;
	}

	/**
	 * Get pagination object
	 *
	 * @return JPagination
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($this->getTotal(), $this->state->limitstart, $this->state->limit);
		}
		
		return $this->pagination;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns()
	{
		$this->query->select(array('tbl.*'));
		
		return $this;
	}

	/**
	 * Builds FROM tables list for the query
	 */
	protected function _buildQueryFrom()
	{
		$this->query->from($this->table . ' AS tbl');
		
		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins()
	{
		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere()
	{
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$state = $this->state;
		$query = $this->query;
		if (is_numeric($state->filter_state))
		{
			$query->where($this->stateField . ' = ' . (int) $state->filter_state);
		}
		elseif ($state->filter_state === '')
		{
			$query->where('(' . $this->stateField . ' IN (0, 1))');
		}
		if ($state->filter_access)
		{
			$query->where('tbl.access = ' . (int) $state->filter_access);
		}
		
		if (!$user->authorise('core.admin'))
		{
			$query->where('tbl.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}
		
		if ($state->filter_search)
		{
			if (stripos($state->search, 'id:') === 0)
			{
				$query->where('tbl.id = ' . (int) substr($state->filter_search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($state->filter_search, true) . '%', false);
				if (is_array($this->searchFields))
				{
					$whereOr = array();
					foreach ($this->searchFields as $searchField)
					{
						$whereOr[] = " LOWER($searchField) LIKE " . $search;
					}
					$query->where('(' . implode(' OR ', $whereOr) . ') ');
				}
			}
		}
		
		if ($state->filter_language && $state->filter_language != '*')
		{
			$query->where('tbl.language IN (' . $db->Quote($state->filter_language) . ',' . $db->Quote('*') . ', "")');
		}
		
		if (is_numeric($state->filter_tag))
		{
			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $state->filter_tag)
			->join(
				'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
				. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
				. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote($this->option.'.'.JCmsInflector::singularize($this->name))
			);
		}
		
		return $this;
	}

	/**
	 * Builds a GROUP BY clause for the query
	 */
	protected function _buildQueryGroup()
	{
		return $this;
	}

	/**
	 * Builds a HAVING clause for the query
	 */
	protected function _buildQueryHaving()
	{
		return $this;
	}

	/**
	 * Builds a generic ORDER BY clasue based on the model's state
	 */
	protected function _buildQueryOrder()
	{
		$sort = $this->state->filter_order;
		$direction = strtoupper($this->state->filter_order_Dir);
		if ($sort)
		{
			$this->query->order($sort . ' ' . $direction);
		}
	}
}
