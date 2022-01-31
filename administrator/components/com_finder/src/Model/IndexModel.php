<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Index model class for Finder.
 *
 * @since  2.5
 */
class IndexModel extends ListModel
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
	 * The event to trigger after purging the data.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $event_after_purge = 'onFinderIndexAfterPurge';

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
				'state', 'published', 'l.published',
				'title', 'l.title',
				'type', 'type_id', 'l.type_id',
				't.title', 't_title',
				'url', 'l.url',
				'language', 'l.language',
				'indexdate', 'l.indexdate',
				'content_map',
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

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;

					// Trigger the onContentBeforeDelete event.
					$result = Factory::getApplication()->triggerEvent($this->event_before_delete, array($context, $table));

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
					Factory::getApplication()->triggerEvent($this->event_after_delete, array($context, $table));
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
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('l.*')
			->select($db->quoteName('t.title', 't_title'))
			->from($db->quoteName('#__finder_links', 'l'))
			->join('INNER', $db->quoteName('#__finder_types', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('l.type_id'));

		// Check the type filter.
		$type = $this->getState('filter.type');

		// Join over the language
		$query->select('la.title AS language_title, la.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages') . ' AS la ON la.lang_code = l.language');

		if (is_numeric($type))
		{
			$query->where($db->quoteName('l.type_id') . ' = ' . (int) $type);
		}

		// Check the map filter.
		$contentMapId = $this->getState('filter.content_map');

		if (is_numeric($contentMapId))
		{
			$query->join('INNER', $db->quoteName('#__finder_taxonomy_map', 'm') . ' ON ' . $db->quoteName('m.link_id') . ' = ' . $db->quoteName('l.link_id'))
				->where($db->quoteName('m.node_id') . ' = ' . (int) $contentMapId);
		}

		// Check for state filter.
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where($db->quoteName('l.published') . ' = ' . (int) $state);
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where($db->quoteName('l.language') . ' = ' . $db->quote($language));
		}

		// Check the search phrase.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search      = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$orSearchSql = $db->quoteName('l.title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('l.url') . ' LIKE ' . $search;

			// Filter by indexdate only if $search doesn't contains non-ascii characters
			if (!preg_match('/[^\x00-\x7F]/', $search))
			{
				$orSearchSql .= ' OR ' . $query->castAsChar($db->quoteName('l.indexdate')) . ' LIKE ' . $search;
			}

			$query->where('(' . $orSearchSql . ')');
		}

		// Handle the list ordering.
		$listOrder = $this->getState('list.ordering', 'l.title');
		$listDir   = $this->getState('list.direction', 'ASC');

		if ($listOrder === 't.title')
		{
			$ordering = $db->quoteName('t.title') . ' ' . $db->escape($listDir) . ', ' . $db->quoteName('l.title') . ' ' . $db->escape($listDir);
		}
		else
		{
			$ordering = $db->escape($listOrder) . ' ' . $db->escape($listDir);
		}

		$query->order($ordering);

		return $query;
	}

	/**
	 * Method to get the state of the Smart Search Plugins.
	 *
	 * @return  array  Array of relevant plugins and whether they are enabled or not.
	 *
	 * @since   2.5
	 */
	public function getPluginState()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('name, enabled')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' IN (' . $db->quote('system') . ',' . $db->quote('content') . ')')
			->where($db->quoteName('element') . ' = ' . $db->quote('finder'));
		$db->setQuery($query);

		return $db->loadObjectList('name');
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
		$id .= ':' . $this->getState('filter.content_map');

		return parent::getStoreId($id);
	}

	/**
	 * Gets the total of indexed items.
	 *
	 * @return  integer  The total of indexed items.
	 *
	 * @since   3.6.0
	 */
	public function getTotalIndexed()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(link_id)')
			->from($db->quoteName('#__finder_links'));
		$db->setQuery($query);

		return (int) $db->loadResult();
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
	public function getTable($type = 'Link', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to purge the index, deleting all links.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 * @throws  \Exception on database error
	 */
	public function purge()
	{
		$db = $this->getDbo();

		// Truncate the links table.
		$db->truncateTable('#__finder_links');

		// Truncate the links terms tables.
		$db->truncateTable('#__finder_links_terms');

		// Truncate the terms table.
		$db->truncateTable('#__finder_terms');

		// Truncate the taxonomy map table.
		$db->truncateTable('#__finder_taxonomy_map');

		// Truncate the taxonomy table and insert the root node.
		$db->truncateTable('#__finder_taxonomy');
		$root = (object) array(
			'id' => 1,
			'parent_id' => 0,
			'lft' => 0,
			'rgt' => 1,
			'level' => 0,
			'path' => '',
			'title' => 'ROOT',
			'alias' => 'root',
			'state' => 1,
			'access' => 1,
			'language' => '*'
		);
		$db->insertObject('#__finder_taxonomy', $root);

		// Truncate the tokens tables.
		$db->truncateTable('#__finder_tokens');

		// Truncate the tokens aggregate table.
		$db->truncateTable('#__finder_tokens_aggregate');

		// Include the finder plugins for the on purge events.
		PluginHelper::importPlugin('finder');
		Factory::getApplication()->triggerEvent($this->event_after_purge);

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
	protected function populateState($ordering = 'l.title', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'cmd'));
		$this->setState('filter.content_map', $this->getUserStateFromRequest($this->context . '.filter.content_map', 'filter_content_map', '', 'cmd'));
		$this->setState('filter.language', $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', ''));

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
}
