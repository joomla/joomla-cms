<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'state', 'a.state',
				'title', 'a.title',
				'branch'
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
		$user = JFactory::getUser();

		return $user->authorise('core.delete', $this->option);
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
		$user = JFactory::getUser();

		return $user->authorise('core.edit.state', $this->option);
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
			->from($db->quoteName('#__finder_taxonomy') . ' AS a');

		// Self-join to get children.
		$query->select('COUNT(b.id) AS num_children')
			->join('LEFT', $db->quoteName('#__finder_taxonomy') . ' AS b ON b.parent_id=a.id');

		// Join to get the map links
		$query->select('COUNT(c.node_id) AS num_nodes')
			->join('LEFT', $db->quoteName('#__finder_taxonomy_map') . ' AS c ON c.node_id=a.id')
			->group('a.id, a.parent_id, a.title, a.state, a.access, a.ordering');

		// If the model is set to check item state, add to the query.
		if (is_numeric($this->getState('filter.state')))
		{
			$query->where('a.state = ' . (int) $this->getState('filter.state'));
		}

		// Filter the maps over the branch if set.
		$branch_id = $this->getState('filter.branch');

		if (!empty($branch_id))
		{
			$query->where('a.parent_id = ' . (int) $branch_id);
		}

		// Filter the maps over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$query->where('a.title LIKE ' . $db->quote('%' . $search . '%'));
		}

		// Handle the list ordering.
		$ordering = $this->getState('list.ordering');
		$direction = $this->getState('list.direction');

		if (!empty($ordering))
		{
			$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
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
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.branch');

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
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$branch = $this->getUserStateFromRequest($this->context . '.filter.branch', 'filter_branch', '1', 'string');
		$this->setState('filter.branch', $branch);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_finder');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.title', 'asc');
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
