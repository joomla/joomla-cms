<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of search terms.
 *
 * @since  1.6
 */
class SearchModelSearches extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'search_term', 'a.search_term',
				'hits', 'a.hits',
			);
		}

		parent::__construct($config);
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
	protected function populateState($ordering = 'a.hits', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

		// Special state for toggle results button.
		$this->setState('show_results', $this->getUserStateFromRequest($this->context . '.show_results', 'show_results', 1, 'int'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_search');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('show_results');
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__core_log_searches', 'a'));

		// Filter by search in title
		if ($search = $this->getState('filter.search'))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where($db->quoteName('a.search_term') . ' LIKE ' . $search);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.hits')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Override the parent getItems to inject optional data.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Determine if number of results for search item should be calculated
		// by default it is `off` as it is highly query intensive
		if ($this->getState('show_results'))
		{
			JPluginHelper::importPlugin('search');
			$app = JFactory::getApplication();

			if (!class_exists('JSite'))
			{
				// This fools the routers in the search plugins into thinking it's in the frontend
				JLoader::register('JSite', JPATH_ADMINISTRATOR . '/components/com_search/helpers/site.php');
			}

			foreach ($items as &$item)
			{
				$results = $app->triggerEvent('onContentSearch', array($item->search_term));
				$item->returns = 0;

				foreach ($results as $result)
				{
					$item->returns += count($result);
				}
			}
		}

		return $items;
	}

	/**
	 * Method to reset the seach log table.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function reset()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__core_log_searches'));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}
}
