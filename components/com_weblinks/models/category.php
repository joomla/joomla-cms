<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Weblinks Component Weblink Model
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksModelCategory extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @access	protected
	 * @var		string
	 */
	 protected $_context = 'com_weblinks.category';

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
	public function &getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = &parent::getItems();

		// Convert the params field into an object, saving original in _params
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item = &$items[$i];
			if (!isset($this->_params))
			{
				$item->_params	= $item->params;
				$item->params	= new JParameter($item->_params);
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
	protected function _getListQuery()
	{
		$user	= &JFactory::getUser();
		$groups	= implode(',', $user->authorisedLevels());

		// Create a new query object.
		$query = new JQuery;

		// Select required fields from the categories.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('`#__weblinks` AS a');
		$query->where('a.access IN ('.$groups.')');

		// Filter by category.
		if ($categoryId = $this->getState('category.id'))
		{
			$query->where('a.catid = '.(int) $categoryId);
			$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
			$query->where('c.access IN ('.$groups.')');
		}

		// Filter by state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('a.state = '.(int) $state);
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.ordering')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function _populateState()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$params	= JComponentHelper::getParams('com_weblinks');

		// List state information
		$limit 		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$this->setState('list.limitstart', $limitstart);

		$orderCol	= JRequest::getCmd('filter_order', 'ordering');
		$this->setState('list.ordering', $orderCol);

		$orderDirn	=  JRequest::getCmd('filter_order_Dir', 'ASC');
		$this->setState('list.direction', $orderDirn);

		$id = JRequest::getVar('id', 0, '', 'int');
		$this->setState('category.id', $id);

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
	function &getCategory($id = 0)
	{
		if (empty($id)) {
			$id = $this->getState('category.id');
		}

		if (empty($this->_category))
		{
			$this->_db->setQuery(
				'SELECT a.*' .
				' FROM #__categories AS a' .
				' WHERE id = '.(int) $id .
				'  AND a.published = '.$this->getState('filter.published').
				'  AND a.extension = '.$this->_db->quote('com_weblinks')
			);
			$this->_category = $this->_db->loadObject();

			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
			}
		}

		return $this->_category;
	}

	/**
	 * Get the list of weblinks categories
	 *
	 * @since	1.6
	 */
	function &getCategories()
	{
		if (empty($this->_categories))
		{
			$model = &JModel::getInstance('Categories', 'Weblinksmodel', array('ignore_request' => true));
			$model->setState('published',	$this->getState('published'));
			$model->setState('approved',	$this->getState('approved'));

			if (!($this->_categories = $model->getItems())) {
				$this->setError($model->getError());
			}
		}
		return $this->_categories;
	}
}
