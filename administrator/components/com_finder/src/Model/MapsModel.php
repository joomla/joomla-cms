<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Model;

\defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseQuery;

/**
 * Maps model for the Finder package.
 *
 * @since  2.5
 */
class MapsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   3.7
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'state', 'a.state',
				'title', 'a.title',
				'branch',
				'branch_title', 'd.branch_title',
				'level', 'd.level',
				'language', 'a.language',
			);
		}

		parent::__construct($config, $factory);
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
		return Factory::getUser()->authorise('core.delete', $this->option);
	}

	/**
	 * Method to test whether a record can have its state changed.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   2.5
	 */
	protected function canEditState($record)
	{
		return Factory::getUser()->authorise('core.edit.state', $this->option);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  $pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   2.5
	 */
	public function delete(&$pks)
	{
		$pks = (array) $pks;
		$table = $this->getTable();

		// Include the content plugins for the on delete events.
		PluginHelper::importPlugin('content');

		// Iterate the items to check if all of them exist.
		foreach ($pks as $i => $pk)
		{
			if (!$table->load($pk))
			{
				// Item is not in the table.
				$this->setError($table->getError());

				return false;
			}
		}

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;

					// Trigger the onContentBeforeDelete event.
					$result = Factory::getApplication()->triggerEvent('onContentBeforeDelete', array($context, $table));

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
					Factory::getApplication()->triggerEvent('onContentAfterDelete', array($context, $table));
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
						$this->setError(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
					}
				}
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		// Select all fields from the table.
		$query = $db->getQuery(true)
			->select('a.id, a.parent_id, a.lft, a.rgt, a.level, a.path, a.title, a.alias, a.state, a.access, a.language')
			->from($db->quoteName('#__finder_taxonomy', 'a'))
			->where('a.parent_id != 0');

		// Join to get the branch title
		$query->select([$db->quoteName('b.id', 'branch_id'), $db->quoteName('b.title', 'branch_title')])
			->leftJoin($db->quoteName('#__finder_taxonomy', 'b') . ' ON b.level = 1 AND b.lft <= a.lft AND a.rgt <= b.rgt');

		// Join to get the map links.
		$stateQuery = $db->getQuery(true)
			->select('m.node_id')
			->select('COUNT(NULLIF(l.published, 0)) AS count_published')
			->select('COUNT(NULLIF(l.published, 1)) AS count_unpublished')
			->from($db->quoteName('#__finder_taxonomy_map', 'm'))
			->leftJoin($db->quoteName('#__finder_links', 'l') . ' ON l.link_id = m.link_id')
			->group('m.node_id');

		$query->select('COALESCE(s.count_published, 0) AS count_published');
		$query->select('COALESCE(s.count_unpublished, 0) AS count_unpublished');
		$query->leftJoin('(' . $stateQuery . ') AS s ON s.node_id = a.id');

		// If the model is set to check item state, add to the query.
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}

		// Filter over level.
		$level = $this->getState('filter.level');

		if (is_numeric($level) && (int) $level === 1)
		{
			$query->where('a.parent_id = 1');
		}

		// Filter the maps over the branch if set.
		$branchId = $this->getState('filter.branch');

		if (is_numeric($branchId))
		{
			$query->where('a.parent_id = ' . (int) $branchId);
		}

		// Filter the maps over the search string if set.
		if ($search = $this->getState('filter.search'))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('a.title LIKE ' . $search);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'branch_title, a.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Returns a record count for the query.
	 *
	 * @param   \Joomla\Database\DatabaseQuery|string
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   3.0
	 */
	protected function _getListCount($query)
	{
		$query = clone $query;
		$query->clear('select')->clear('join')->clear('order')->clear('limit')->clear('offset')->select('COUNT(*)');

		return (int) $this->getDbo()->setQuery($query)->loadResult();
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
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate. [optional]
	 * @param   string  $prefix  A prefix for the table class name. [optional]
	 * @param   array   $config  Configuration array for model. [optional]
	 *
	 * @return  \Joomla\CMS\Table\Table  A database object
	 *
	 * @since   2.5
	 */
	public function getTable($type = 'Map', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
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
	protected function populateState($ordering = 'branch_title, a.lft', $direction = 'ASC')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'));
		$this->setState('filter.branch', $this->getUserStateFromRequest($this->context . '.filter.branch', 'filter_branch', '', 'cmd'));
		$this->setState('filter.level', $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', '', 'cmd'));

		// Load the parameters.
		$params = ComponentHelper::getParams('com_finder');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    $pks    A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state. [optional]
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function publish(&$pks, $value = 1)
	{
		$user = Factory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;

		// Include the content plugins for the change of state event.
		PluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk) && !$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));

				return false;
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
		$result = Factory::getApplication()->triggerEvent('onContentChangeState', array($context, $pks, $value));

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
			->delete($db->quoteName('#__finder_taxonomy_map'));
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Manipulate the query to be used to evaluate if this is an Empty State to provide specific conditions for this extension.
	 *
	 * @return DatabaseQuery
	 *
	 * @since 4.0.0
	 */
	protected function getEmptyStateQuery()
	{
		$query = parent::getEmptyStateQuery();

		$title = 'ROOT';

		$query->where($this->_db->quoteName('title') . ' <> :title')
			->bind(':title', $title);

		return $query;
	}
}
