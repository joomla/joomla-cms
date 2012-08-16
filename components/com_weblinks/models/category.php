<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblinks Component Weblink Model
 *
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 * @since       1.5
 */
class WeblinksModelCategory extends JModelList
{
	/**
	 * Category items data
	 *
	 * @var array
	 */
	protected $_item = null;

	protected $_articles = null;

	protected $_siblings = null;

	protected $_children = null;

	protected $_parent = null;

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'hits', 'a.hits',
				'ordering', 'a.ordering',
			);
		}

		parent::__construct($config);
	}

	/**
	 * The category that applies.
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $_category = null;

	/**
	 * The list of other weblink categories.
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $_categories = null;

	/**
	 * Method to get a list of items.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

		// Convert the params field into an object, saving original in _params
		for ($i = 0, $n = count($items); $i < $n; $i++) {
			if (!isset($this->_params)) {
				$params = new JRegistry;
				$params->loadString($items[$i]->params);
				$items[$i]->params = $params;
			}
		}

		return $items;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select required fields from the categories.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from($db->quoteName('#__weblinks').' AS a');
		$query->where('a.access IN ('.$groups.')');

		// Filter by category.
		if ($categoryId = $this->getState('category.id')) {
			$query->where('a.catid = '.(int) $categoryId);
			$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
			$query->where('c.access IN ('.$groups.')');

			//Filter by published category
			$cpublished = $this->getState('filter.c.published');
			if (is_numeric($cpublished)) {
				$query->where('c.published = '.(int) $cpublished);
			}
		}

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author");
		$query->select("ua.email AS author_email");

		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		$query->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		// Filter by state

		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('a.state = '.(int) $state);
		}
		// do not show trashed links on the front-end
		$query->where('a.state != -2');

		// Filter by start and end dates.
		$nullDate = $db->Quote($db->getNullDate());
		$date = JFactory::getDate();
		$nowDate = $db->Quote($date->toSql());

		if ($this->getState('filter.publish_date')){
			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
			$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}

		// Filter by language
		if ($this->getState('filter.language')) {
			$query->where('a.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.ordering')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_weblinks');

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
		$this->setState('list.limit', $limit);

		$limitstart = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $limitstart);

		$orderCol = $app->input->get('filter_order', 'ordering');
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$id = $app->input->get('id', 0, 'int');
		$this->setState('category.id', $id);

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_weblinks')) &&  (!$user->authorise('core.edit', 'com_weblinks'))){
			// limit to published for people who can't edit or edit.state.
			$this->setState('filter.state',	1);

			// Filter by start and end dates.
			$this->setState('filter.publish_date', true);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get category data for the current category
	 *
	 * @param	int		An optional ID
	 *
	 * @return	object
	 * @since	1.5
	 */
	public function getCategory()
	{
		if(!is_object($this->_item))
		{
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry;

			if($active)
			{
				$params->loadString($active->params);
			}

			$options = array();
			$options['countItems'] = $params->get('show_cat_num_links_cat', 1) || $params->get('show_empty_categories', 0);
			$categories = JCategories::getInstance('Weblinks', $options);
			$this->_item = $categories->get($this->getState('category.id', 'root'));
			if(is_object($this->_item))
			{
				$this->_children = $this->_item->getChildren();
				$this->_parent = false;
				if($this->_item->getParent())
				{
					$this->_parent = $this->_item->getParent();
				}
				$this->_rightsibling = $this->_item->getSibling();
				$this->_leftsibling = $this->_item->getSibling(false);
			} else {
				$this->_children = false;
				$this->_parent = false;
			}
		}

		return $this->_item;
	}

	/**
	 * Get the parent category
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 */
	public function getParent()
	{
		if(!is_object($this->_item))
		{
			$this->getCategory();
		}
		return $this->_parent;
	}

	/**
	 * Get the sibling (adjacent) categories.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 */
	function &getLeftSibling()
	{
		if(!is_object($this->_item))
		{
			$this->getCategory();
		}
		return $this->_leftsibling;
	}

	function &getRightSibling()
	{
		if(!is_object($this->_item))
		{
			$this->getCategory();
		}
		return $this->_rightsibling;
	}

	/**
	 * Get the child categories.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 */
	function &getChildren()
	{
		if(!is_object($this->_item))
		{
			$this->getCategory();
		}
		return $this->_children;
	}
}
