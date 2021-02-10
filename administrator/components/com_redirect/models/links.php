<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of redirect links.
 *
 * @since  1.6
 */
class RedirectModelLinks extends JModelList
{
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
				'state', 'a.state',
				'old_url', 'a.old_url',
				'new_url', 'a.new_url',
				'referer', 'a.referer',
				'hits', 'a.hits',
				'created_date', 'a.created_date',
				'published', 'a.published',
				'header', 'a.header', 'http_status',
			);
		}

		parent::__construct($config);
	}
	/**
	 * Removes all of the unpublished redirects from the table.
	 *
	 * @return  boolean result of operation
	 *
	 * @since   3.5
	 */
	public function purge()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);

		$query->delete('#__redirect_links')->where($db->qn('published') . '= 0');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
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
	protected function populateState($ordering = 'a.old_url', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.http_status', $this->getUserStateFromRequest($this->context . '.filter.http_status', 'filter_http_status', '', 'cmd'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_redirect');
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.http_status');

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
		$query->from($db->quoteName('#__redirect_links', 'a'));

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where($db->quoteName('a.published') . ' = ' . (int) $state);
		}
		elseif ($state === '')
		{
			$query->where($db->quoteName('a.published') . ' IN (0,1)');
		}

		// Filter the items over the HTTP status code header.
		if ($httpStatusCode = $this->getState('filter.http_status'))
		{
			$query->where($db->quoteName('a.header') . ' = ' . (int) $httpStatusCode);
		}

		// Filter the items over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where(
					'(' . $db->quoteName('old_url') . ' LIKE ' . $search .
					' OR ' . $db->quoteName('new_url') . ' LIKE ' . $search .
					' OR ' . $db->quoteName('comment') . ' LIKE ' . $search .
					' OR ' . $db->quoteName('referer') . ' LIKE ' . $search . ')'
				);
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.old_url')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Add the entered URLs into the database
	 *
	 * @param   array  $batchUrls  Array of URLs to enter into the database
	 *
	 * @return boolean
	 */
	public function batchProcess($batchUrls)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$params = JComponentHelper::getParams('com_redirect');
		$state  = (int) $params->get('defaultImportState', 0);

		$columns = array(
			$db->quoteName('old_url'),
			$db->quoteName('new_url'),
			$db->quoteName('referer'),
			$db->quoteName('comment'),
			$db->quoteName('hits'),
			$db->quoteName('published'),
			$db->quoteName('created_date')
		);

		$query->columns($columns);

		foreach ($batchUrls as $batch_url)
		{
			$old_url = $batch_url[0];

			// Destination URL can also be an external URL
			if (!empty($batch_url[1]))
			{
				$new_url = $batch_url[1];
			}
			else
			{
				$new_url = '';
			}

			$query->insert($db->quoteName('#__redirect_links'), false)
				->values(
					$db->quote($old_url) . ', ' . $db->quote($new_url) . ' ,' . $db->quote('') . ', ' . $db->quote('') . ', 0, ' . $state . ', ' .
					$db->quote(JFactory::getDate()->toSql())
				);
		}

		$db->setQuery($query);
		$db->execute();

		return true;
	}
}
