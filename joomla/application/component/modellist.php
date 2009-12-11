<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.application.component.model');
jimport('joomla.database.query');

/**
 * Model class for handling lists of items.
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.6
 */
class JModelList extends JModel
{
	/**
	 * An array of totals for the lists.
	 *
	 * @var		array
	 */
	protected $_totals = array();

	/**
	 * Internal memory based cache array of data.
	 *
	 * @var		array
	 */
	protected $_cache = array();

	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the _getStoreId() method and caching data structures.
	 *
	 * @var		string
	 */
	protected $_context = null;

	/**
	 * Method to get an array of data items.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->_getStoreId();

		// Try to load the data from internal storage.
		if (!empty($this->_cache[$store])) {
			return $this->_cache[$store];
		}

		// Load the list items.
		$query	= $this->_getListQuery();
		$items	= $this->_getList((string) $query, $this->getState('list.start'), $this->getState('list.limit'));

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Add the items to the internal cache.
		$this->_cache[$store] = $items;

		return $this->_cache[$store];
	}

	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return	object	A JPagination object for the data set.
	 */
	public function getPagination()
	{
		// Get a storage key.
		$store = $this->_getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (!empty($this->_cache[$store])) {
			return $this->_cache[$store];
		}

		// Create the pagination object.
		jimport('joomla.html.pagination');
		$page = new JPagination($this->getTotal(), (int) $this->getState('list.start'), (int) $this->getState('list.limit'));

		// Add the object to the internal cache.
		$this->_cache[$store] = $page;

		return $this->_cache[$store];
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return	integer	The total number of items available in the data set.
	 */
	public function getTotal()
	{
		// Get a storage key.
		$store = $this->_getStoreId('getTotal');

		// Try to load the data from internal storage.
		if (!empty($this->_cache[$store])) {
			return $this->_cache[$store];
		}

		// Load the total.
		$query = $this->_getListQuery();
		$total = (int) $this->_getListCount((string) $query);

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Add the total to the internal cache.
		$this->_cache[$store] = $total;

		return $this->_cache[$store];
	}

	/**
	 * Method to get a JQuery object for retrieving the data set from a database.
	 *
	 * @return	object	A JQuery object to retrieve the data set.
	 */
	protected function _getListQuery()
	{
		$query = new JQuery;

		return $query;
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string	An identifier string to generate the store id.
	 * @return	string	A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');

		return md5($this->_context.':'.$id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @param	string	An optional ordering field.
	 * @param	string	An optional direction (asc|desc).
	 */
	protected function _populateState($ordering = null, $direction)
	{
		// If the context is set, assume that stateful lists are used.
		if ($this->_context)
		{
			$app = JFactory::getApplication();

			$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
			$this->setState('list.limit', $limit);

			$limitstart = $app->getUserStateFromRequest($this->_context.'.limitstart', 'limitstart', 0);
			$this->setState('list.start', $limitstart);

			$orderCol = $app->getUserStateFromRequest($this->_context.'.ordercol', 'filter_order', $ordering);
			$this->setState('list.ordering', $orderCol);

			$orderDirn = $app->getUserStateFromRequest($this->_context.'.orderdirn', 'filter_order_Dir', $direction);
			$this->setState('list.direction', $orderDirn);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->_state->set('list.limit', 0);
		}
	}
}
