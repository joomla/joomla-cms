<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Languages Model Class
 *
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 * @since       1.6
 */
class LanguagesModelLanguages extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  An optional associative array of configuration settings.
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'lang_id', 'a.lang_id',
				'lang_code', 'a.lang_code',
				'title', 'a.title',
				'title_native', 'a.title_native',
				'sef', 'a.sef',
				'image', 'a.image',
				'published', 'a.published',
				'ordering', 'a.ordering',
				'access', 'a.access', 'access_level',
				'home', 'l.home',
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
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		$accessId = $this->getUserStateFromRequest($this->context . '.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);

		$published = $this->getUserStateFromRequest($this->context . '.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_languages');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.ordering', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id    A prefix for the store id.
	 *
	 * @return  string  A store id.
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string    An SQL query
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select all fields from the languages table.
		$query->select($this->getState('list.select', 'a.*', 'l.home'))
			->from($db->quoteName('#__languages') . ' AS a');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Select the language home pages
		$query->select('l.home AS home')
			->join('LEFT', $db->quoteName('#__menu') . ' AS l  ON  l.language = a.lang_code AND l.home=1  AND l.language <> ' . $db->quote('*'));

		// Filter on the published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(a.title LIKE ' . $search . ')');
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Set the published language(s)
	 *
	 * @param   array    $cid      An array of language IDs.
	 * @param   integer  $value    The value of the published state.
	 *
	 * @return  boolean  True on success, false otherwise.
	 * @since   1.6
	 */
	public function setPublished($cid, $value = 0)
	{
		return JTable::getInstance('Language')->publish($cid, $value);
	}

	/**
	 * Method to delete records.
	 *
	 * @param   array  An array of item primary keys.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 * @since   1.6
	 */
	public function delete($pks)
	{
		// Sanitize the array.
		$pks = (array) $pks;

		// Get a row instance.
		$table = JTable::getInstance('Language');

		// Iterate the items to delete each one.
		foreach ($pks as $itemId)
		{
			if (!$table->delete((int) $itemId))
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Custom clean cache method, 2 places for 2 clients
	 *
	 * @since   1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('_system');
		parent::cleanCache('com_languages');
	}
}
