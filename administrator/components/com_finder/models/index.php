<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Index model class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderModelIndex extends JModelList
{
	/**
	 * The event to trigger after deleting the data.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $event_after_delete = 'onContentAfterDelete';

	/**
	 * The event to trigger before deleting the data.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $event_before_delete = 'onContentBeforeDelete';

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
				'published', 'l.published',
				'title', 'l.title',
				'type_id', 'l.type_id',
				'url', 'l.url',
				'indexdate', 'l.indexdate'
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
		// Initialise variables.
		$dispatcher = JDispatcher::getInstance();
		$user = JFactory::getUser();
		$pks = (array) $pks;
		$table = $this->getTable();

		// Include the content and finder plugins for the on delete events.
		JPluginHelper::importPlugin('content');
		JPluginHelper::importPlugin('finder');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;

					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
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
					$dispatcher->trigger($this->event_after_delete, array($context, $table));
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

		$query->select('l.*');
		$query->select('t.title AS t_title');
		$query->from($db->quoteName('#__finder_links') . ' AS l');
		$query->join('INNER', $db->quoteName('#__finder_types') . ' AS t ON t.id = l.type_id');

		// Check the type filter.
		if ($this->getState('filter.type'))
		{
			$query->where($db->quoteName('l.type_id') . ' = ' . (int) $this->getState('filter.type'));
		}

		// Check for state filter.
		if (is_numeric($this->getState('filter.state')))
		{
			$query->where($db->quoteName('l.published') . ' = ' . (int) $this->getState('filter.state'));
		}

		// Check the search phrase.
		if ($this->getState('filter.search') != '')
		{
			$search = $db->escape($this->getState('filter.search'));
			$query->where($db->quoteName('l.title') . ' LIKE "%' . $db->escape($search) . '%"' . ' OR ' . $db->quoteName('l.url') . ' LIKE "%' . $db->escape($search) . '%"' . ' OR ' . $db->quoteName('l.indexdate') . ' LIKE "%' . $db->escape($search) . '%"');
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
	 * Method to get the state of the Smart Search plug-ins.
	 *
	 * @return  array   Array of relevant plug-ins and whether they are enabled or not.
	 *
	 * @since   2.5
	 */
	public function getPluginState()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('name, enabled');
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('type') . ' = ' .  $db->quote('plugin'));
		$query->where($db->quoteName('folder') . ' IN(' .  $db->quote('system') . ',' . $db->quote('content') . ')');
		$query->where($db->quoteName('element') . ' = ' .  $db->quote('finder'));
		$db->setQuery($query);
		$db->query();
		$plugins = $db->loadObjectList('name');

		return $plugins;
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
		$id .= ':' . $this->getState('filter.type');

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
	public function getTable($type = 'Link', $prefix = 'FinderTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to purge the index, deleting all links.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 */
	public function purge()
	{
		$db = $this->getDbo();

		// Truncate the links table.
		$db->truncateTable('#__finder_links');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Truncate the links terms tables.
		for ($i = 0; $i <= 15; $i++)
		{
			// Get the mapping table suffix.
			$suffix = dechex($i);

			$db->truncateTable('#__finder_links_terms' . $suffix);

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		// Truncate the terms table.
		$db->truncateTable('#__finder_terms');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Truncate the taxonomy map table.
		$db->truncateTable('#__finder_taxonomy_map');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Delete all the taxonomy nodes except the root.
		$query = $db->getQuery(true);
		$query->delete();
		$query->from($db->quoteName('#__finder_taxonomy'));
		$query->where($db->quoteName('id') . ' > 1');
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Truncate the tokens tables.
		$db->truncateTable('#__finder_tokens');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Truncate the tokens aggregate table.
		$db->truncateTable('#__finder_tokens_aggregate');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		return true;
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

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string');
		$this->setState('filter.type', $type);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_finder');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('l.title', 'asc');
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
		// Initialise variables.
		$dispatcher = JDispatcher::getInstance();
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
}
