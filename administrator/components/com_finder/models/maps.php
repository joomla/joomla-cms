<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();

/**
 * Maps model for the Finder package.
 *
 * @since  2.5
 */
class FinderModelMaps extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An associative array of configuration settings. [optional]
	 *
	 * @since   2.5
	 * @see     JControllerLegacy
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'state', 'a.state',
				'title', 'a.title',
				'branch',
				'branch_title', 'd.branch_title',
				'level', 'd.level',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   2.5
	 */
	protected function canDelete($record)
	{
		return JFactory::getUser()->authorise('core.delete', $this->option);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   2.5
	 */
	protected function canEditState($record)
	{
		return JFactory::getUser()->authorise('core.edit.state', $this->option);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   2.5
	 */
	public function delete(&$pks)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;

					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger('onContentBeforeDelete', array($context, $table));

					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());

						return false;
					}

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());

						return false;
					}

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger('onContentAfterDelete', array($context, $table));
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();

					if ($error)
					{
						$this->setError($error);
					}
					else
					{
						$this->setError(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
					}
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);

		// Select all fields from the table.
		$query->select('a.*')
			->select('s.count_published')
			->select('s.count_unpublished')
			->from($db->quoteName('#__finder_taxonomy', 'a'))
			->where($db->quoteName('a.parent_id') . ' <> 0');

		// Self-join to get children.
		$query->select('COUNT(b.id) AS num_children')
			->join('LEFT', $db->quoteName('#__finder_taxonomy', 'b') . ' ON ' . $db->qn('b.parent_id') . ' = ' . $db->qn('a.id'));

		// Join to get the map links.
		$stateSubQuery1 = $db->getQuery(true);
		$stateSubQuery1->select($db->quoteName('mp.node_id'))
			->select('COUNT(mp.node_id) AS count_published')
			->from($db->quoteName('#__finder_links', 'lp'))
			->join('LEFT', $db->quoteName('#__finder_taxonomy_map', 'mp') . ' ON ' . $db->qn('lp.link_id') . ' = ' . $db->qn('mp.link_id'))
			->where($db->quoteName('lp.published') . ' = 1')
			->group($db->quoteName('mp.node_id'));

		$stateSubQuery2 = $db->getQuery(true);
		$stateSubQuery2->select($db->quoteName('mu.node_id'))
			->select('COUNT(mu.node_id) AS count_unpublished')
			->from($db->quoteName('#__finder_links', 'lu'))
			->join('LEFT', $db->quoteName('#__finder_taxonomy_map', 'mu') . ' ON ' . $db->qn('lu.link_id') . ' = ' . $db->qn('mu.link_id'))
			->where($db->quoteName('lu.published') . ' = 0')
			->group($db->quoteName('mu.node_id'));

		$stateQuery = $db->getQuery(true);
		$stateQuery->select('s1.*')
			->select('s2.count_unpublished')
			->from('(' . $stateSubQuery1 . ') AS s1')
			->join('LEFT', '(' . $stateSubQuery2 . ') AS s2 ON ' . $db->qn('s1.node_id') . ' = ' . $db->qn('s2.node_id'));

		$query->join('LEFT', '(' . $stateQuery . ') AS s ON ' . $db->qn('s.node_id') . ' = ' . $db->qn('a.id'));

		// Calculate levels.
		$levelQuery = $db->getQuery(true);
		$levelQuery->select('title AS branch_title, 1 as level')
			->select($db->quoteName('id'))
			->from($db->quoteName('#__finder_taxonomy'))
			->where($db->quoteName('parent_id') . ' = 1');
		$levelQuery2 = $db->getQuery(true);
		$levelQuery2->select('b.title AS branch_title, 2 as level')
			->select($db->quoteName('a.id'))
			->from($db->quoteName('#__finder_taxonomy', 'a'))
			->join('LEFT', $db->quoteName('#__finder_taxonomy', 'b') . ' ON ' . $db->qn('a.parent_id') . ' = ' . $db->qn('b.id'))
			->where($db->quoteName('a.parent_id') . ' NOT IN (0, 1)');

		$levelQuery->union($levelQuery2);

		// Join to get the levels.
		$query->join('LEFT', '(' . $levelQuery . ') AS d ON ' . $db->qn('d.id') . ' = ' . $db->qn('a.id'));

		// Group
		$query->group('a.id, a.parent_id, a.title, a.state, a.access, a.ordering');

		// Self-join to get the parent title.
		$query->select('e.title AS parent_title')
			->join('LEFT', $db->quoteName('#__finder_taxonomy', 'e') . ' ON ' . $db->quoteName('e.id') . ' = ' . $db->quoteName('a.parent_id'));

		// If the model is set to check item state, add to the query.
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where($db->quoteName('a.state') . ' = ' . (int) $state);
		}

		// Filter over level.
		$level = $this->getState('filter.level');

		if (is_numeric($level) && (int) $level === 1)
		{
			$query->where($db->quoteName('d.level') . ' = 1');
		}

		// Filter the maps over the branch if set.
		$branchId = $this->getState('filter.branch');

		if (is_numeric($branchId))
		{
			$query->where($db->quoteName('a.parent_id') . ' = ' . (int) $branchId);
		}

		// Filter the maps over the search string if set.
		if ($search = $this->getState('filter.search'))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where($db->quoteName('a.title') . ' LIKE ' . $search);
		}

		// Handle the list ordering.
		$listOrdering = $db->escape($this->getState('list.ordering', 'd.branch_title'));
		$listDirn     = $db->escape($this->getState('list.direction', 'ASC'));

		if ($listOrdering == 'd.branch_title')
		{
			$query->order('d.branch_title ' . $listDirn . ', d.level ASC, a.title ' . $listDirn);
		}
		elseif ($listOrdering == 'a.state')
		{
			$query->order('a.state ' . $listDirn . ', d.branch_title ' . $listDirn . ', d.level ASC');
		}

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id. [optional]
	 *
	 * @return  string  A store id.
	 *
	 * @since   2.5
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.branch');
		$id .= ':' . $this->getState('filter.level');

		return parent::getStoreId($id);
	}

	/**
	 * Returns a JTable object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate. [optional]
	 * @param   string  $prefix  A prefix for the table class name. [optional]
	 * @param   array   $config  Configuration array for model. [optional]
	 *
	 * @return  JTable  A database object
	 *
	 * @since   2.5
	 */
	public function getTable($type = 'Map', $prefix = 'FinderTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.  Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field. [optional]
	 * @param   string  $direction  An optional direction. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function populateState($ordering = 'd.branch_title', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'));
		$this->setState('filter.branch', $this->getUserStateFromRequest($this->context . '.filter.branch', 'filter_branch', '', 'cmd'));
		$this->setState('filter.level', $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', '', 'cmd'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_finder');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state. [optional]
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function publish(&$pks, $value = 1)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));

					return false;
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		$context = $this->option . '.' . $this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger('onContentChangeState', array($context, $pks, $value));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to purge all maps from the taxonomy.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   2.5
	 */
	public function purge()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__finder_taxonomy'))
			->where($db->quoteName('parent_id') . ' > 1');
		$db->setQuery($query);
		$db->execute();

		$query->clear()
			->delete($db->quoteName('#__finder_taxonomy_map'))
			->where('1');
		$db->setQuery($query);
		$db->execute();

		return true;
	}
}
