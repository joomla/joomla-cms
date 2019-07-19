<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Featured contact model class.
 *
 * @since  1.6.0
 */
class ContactModelFeatured extends JModelList
{
	/**
	 * Category items data
	 *
	 * @var         array
	 * @since       1.6.0-beta1
	 * @deprecated  4.0  Variable not used since 1.6.0-beta8
	 */
	protected $_item = null;

	/**
	 * Who knows what this was for? It has never been used
	 *
	 * @var          array
	 * @since        1.6.0-beta1
	 * @deprecated   4.0  Variable not used ever
	 */
	protected $_articles = null;

	/**
	 * Get the siblings of the category
	 *
	 * @var          array
	 * @since        1.6.0-beta1
	 * @deprecated   4.0  Variable not used since 1.6.0-beta8
	 */
	protected $_siblings = null;

	/**
	 * Get the children of the category
	 *
	 * @var          array
	 * @since        1.6.0-beta1
	 * @deprecated   4.0  Variable not used since 1.6.0-beta8
	 */
	protected $_children = null;

	/**
	 * Get the parent of the category
	 *
	 * @var          array
	 * @since        1.6.0-beta1
	 * @deprecated   4.0  Variable not used since 1.6.0-beta8
	 */
	protected $_parent = null;

	/**
	 * The category that applies.
	 *
	 * @access      protected
	 * @var         object
	 * @deprecated   4.0  Variable not used ever
	 */
	protected $_category = null;

	/**
	 * The list of other contact categories.
	 *
	 * @access    protected
	 * @var       array
	 * @deprecated   4.0  Variable not used ever
	 */
	protected $_categories = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'con_position', 'a.con_position',
				'suburb', 'a.suburb',
				'state', 'a.state',
				'country', 'a.country',
				'ordering', 'a.ordering',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a list of items.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 */
	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

		// Convert the params field into an object, saving original in _params
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item = &$items[$i];

			if (!isset($this->_params))
			{
				$item->params = new Registry($item->params);
			}
		}

		return $items;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string    An SQL query
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select required fields from the categories.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->quoteName('#__contact_details') . ' AS a')
			->where('a.access IN (' . $groups . ')')
			->where('a.featured=1')
			->join('INNER', '#__categories AS c ON c.id = a.catid')
			->where('c.access IN (' . $groups . ')');

		// Filter by category.
		if ($categoryId = $this->getState('category.id'))
		{
			$query->where('a.catid = ' . (int) $categoryId);
		}

		// Change for sqlsrv... aliased c.published to cat_published
		$query->select('c.published as cat_published, c.published AS parents_published')
			->where('c.published = 1');

		// Filter by state
		$state = $this->getState('filter.published');

		if (is_numeric($state))
		{
			$query->where('a.published = ' . (int) $state);

			// Filter by start and end dates.
			$nullDate = $db->quote($db->getNullDate());
			$date = JFactory::getDate();
			$nowDate = $db->quote($date->toSql());
			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
				->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}

		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_contact');

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$this->setState('list.limit', $limit);

		$limitstart = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $limitstart);

		$orderCol = $app->input->get('filter_order', 'ordering');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'ordering';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_contact')) && (!$user->authorise('core.edit', 'com_contact')))
		{
			// Limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);

			// Filter by start and end dates.
			$this->setState('filter.publish_date', true);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		// Load the parameters.
		$this->setState('params', $params);
	}
}
