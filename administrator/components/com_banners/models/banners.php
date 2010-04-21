<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of banner records.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersModelBanners extends JModelList
{
	/**
	 * Categories data
	 * @var		array
	 */
	protected $categories;

	/**
	 * Method to auto-populate the model state.
	 */
	protected function populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$categoryId = $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$clientId = $app->getUserStateFromRequest($this->context.'.filter.client_id', 'filter_client_id', '');
		$this->setState('filter.client_id', $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_banners');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('name', 'asc');
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Banner', $prefix = 'BannersTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.category_id');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Get the application object
		$app = &JFactory::getApplication();

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id AS id, a.name AS name, a.alias AS alias,'.
				'a.checked_out AS checked_out,'.
				'a.checked_out_time AS checked_out_time, a.catid AS catid,' .
				'a.clicks AS clicks, a.metakey AS metakey, a.sticky AS sticky,'.
				'a.impmade AS impmade, a.imptotal AS imptotal,' .
				'a.state AS state, a.ordering AS ordering,'.
				'a.purchase_type as purchase_type'
			)
		);
		$query->from('`#__banners` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the clients.
		$query->select('cl.name AS client_name,cl.purchase_type as client_purchase_type');
		$query->join('LEFT', '#__banner_clients AS cl ON cl.id = a.cid');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$query->where('a.catid = '.(int) $categoryId);
		}

		// Filter by client.
		$clientId = $this->getState('filter.client_id');
		if (is_numeric($clientId)) {
			$query->where('a.cid = '.(int) $clientId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(a.name LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'ordering');
		$app->setUserState($this->context . '.'.$orderCol.'.orderdirn',$this->getState('list.direction', 'ASC'));
		if ($orderCol=='ordering') {
			$query->order($db->getEscaped('category_title').' '.$db->getEscaped($app->getUserState($this->context . '.category_title.orderdirn','ASC')));
		}
		$query->order($db->getEscaped($orderCol).' '.$db->getEscaped($this->getState('list.direction', 'ASC')));
		if ($orderCol=='category_title') {
			$query->order($db->getEscaped('ordering').' '.$db->getEscaped($app->getUserState($this->context . '.ordering.orderdirn','ASC')));
		}
		$query->order($db->getEscaped('state').' '.$db->getEscaped($app->getUserState($this->context . '.state.orderdirn','ASC')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
	/**
	 * method to give information about categories
	 */
	function &getCategories()
	{
		if (!isset($this->categories))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('MAX(ordering) as `max`');
			$query->select('catid');
			$query->from('#__banners');
			$query->where('state>=0');
			$query->group('catid');
			$db->setQuery((string)$query);
			$this->categories = $db->loadObjectList('catid');
		}
		return $this->categories;
	}
}
