<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Register dependent classes.
JLoader::register('FinderIndexer', dirname(__FILE__) . '/indexer.php');
JLoader::register('FinderIndexerHelper', dirname(__FILE__) . '/helper.php');
JLoader::register('FinderIndexerResult', dirname(__FILE__) . '/result.php');
JLoader::register('FinderIndexerTaxonomy', dirname(__FILE__) . '/taxonomy.php');

/**
 * Prototype adapter class for the Finder indexer package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
abstract class FinderIndexerAdapter extends JPlugin
{
	/**
	 * The context is somewhat arbitrary but it must be unique or there will be
	 * conflicts when managing plugin/indexer state. A good best practice is to
	 * use the plugin name suffix as the context. For example, if the plugin is
	 * named 'plgFinderContent', the context could be 'Content'.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context;

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension;

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout;

	/**
	 * The mime type of the content the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $mime;

	/**
	 * The access level of an item before save.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	protected $old_access;

	/**
	 * The access level of a category before save.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	protected $old_cataccess;

	/**
	 * The type of content the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title;

	/**
	 * The type id of the content.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	protected $type_id;

	/**
	 * The database object.
	 *
	 * @var    object
	 * @since  2.5
	 */
	protected $db;

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $table;

	/**
	 * The field the published state is stored in.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $state_field = 'state';

	/**
	 * Method to instantiate the indexer adapter.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An array that holds the plugin configuration.
	 *
	 * @since   2.5
	 */
	public function __construct(&$subject, $config)
	{
		// Get the database object.
		$this->db = JFactory::getDBO();

		// Call the parent constructor.
		parent::__construct($subject, $config);

		// Get the type id.
		$this->type_id = $this->getTypeId();

		// Add the content type if it doesn't exist and is set.
		if (empty($this->type_id) && !empty($this->type_title))
		{
			$this->type_id = FinderIndexerHelper::addContentType($this->type_title, $this->mime);
		}

		// Check for a layout override.
		if ($this->params->get('layout'))
		{
			$this->layout = $this->params->get('layout');
		}
	}

	/**
	 * Method to get the adapter state and push it into the indexer.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws	Exception on error.
	 */
	public function onStartIndex()
	{
		JLog::add('FinderIndexerAdapter::onStartIndex', JLog::INFO);

		// Get the indexer state.
		$iState = FinderIndexer::getState();

		// Get the number of content items.
		$total = (int) $this->getContentCount();

		// Add the content count to the total number of items.
		$iState->totalItems += $total;

		// Populate the indexer state information for the adapter.
		$iState->pluginState[$this->context]['total'] = $total;
		$iState->pluginState[$this->context]['offset'] = 0;

		// Set the indexer state.
		FinderIndexer::setState($iState);
	}

	/**
	 * Method to prepare for the indexer to be run. This method will often
	 * be used to include dependencies and things of that nature.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on error.
	 */
	public function onBeforeIndex()
	{
		JLog::add('FinderIndexerAdapter::onBeforeIndex', JLog::INFO);

		// Get the indexer and adapter state.
		$iState = FinderIndexer::getState();
		$aState = $iState->pluginState[$this->context];

		// Check the progress of the indexer and the adapter.
		if ($iState->batchOffset == $iState->batchSize || $aState['offset'] == $aState['total'])
		{
			return true;
		}

		// Run the setup method.
		return $this->setup();
	}

	/**
	 * Method to index a batch of content items. This method can be called by
	 * the indexer many times throughout the indexing process depending on how
	 * much content is available for indexing. It is important to track the
	 * progress correctly so we can display it to the user.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on error.
	 */
	public function onBuildIndex()
	{
		JLog::add('FinderIndexerAdapter::onBuildIndex', JLog::INFO);

		// Get the indexer and adapter state.
		$iState = FinderIndexer::getState();
		$aState = $iState->pluginState[$this->context];

		// Check the progress of the indexer and the adapter.
		if ($iState->batchOffset == $iState->batchSize || $aState['offset'] == $aState['total'])
		{
			return true;
		}

		// Get the batch offset and size.
		$offset = (int) $aState['offset'];
		$limit = (int) ($iState->batchSize - $iState->batchOffset);

		// Get the content items to index.
		$items = $this->getItems($offset, $limit);

		// Iterate through the items and index them.
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			// Index the item.
			$this->index($items[$i]);

			// Adjust the offsets.
			$offset++;
			$iState->batchOffset++;
			$iState->totalItems--;
		}

		// Update the indexer state.
		$aState['offset'] = $offset;
		$iState->pluginState[$this->context] = $aState;
		FinderIndexer::setState($iState);

		return true;
	}

	/**
	 * Method to change the value of a content item's property in the links
	 * table. This is used to synchronize published and access states that
	 * are changed when not editing an item directly.
	 *
	 * @param   string   $id        The ID of the item to change.
	 * @param   string   $property  The property that is being changed.
	 * @param   integer  $value     The new value of that property.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws	Exception on database error.
	 */
	protected function change($id, $property, $value)
	{
		JLog::add('FinderIndexerAdapter::change', JLog::INFO);

		// Check for a property we know how to handle.
		if ($property !== 'state' && $property !== 'access')
		{
			return true;
		}

		// Get the url for the content id.
		$item = $this->db->quote($this->getUrl($id, $this->extension, $this->layout));

		// Update the content items.
		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__finder_links'));
		$query->set($this->db->quoteName($property) . ' = ' . (int) $value);
		$query->where($this->db->quoteName('url') . ' = ' . $item);
		$this->db->setQuery($query);
		$this->db->query();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		return true;
	}

	/**
	 * Method to index an item.
	 *
	 * @param   FinderIndexerResult  $item  The item to index as a FinderIndexerResult object.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	abstract protected function index(FinderIndexerResult $item);

	/**
	 * Method to reindex an item.
	 *
	 * @param   integer  $id  The ID of the item to reindex.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function reindex($id)
	{
		// Run the setup method.
		$this->setup();

		// Get the item.
		$item = $this->getItem($id);

		// Index the item.
		$this->index($item);
	}

	/**
	 * Method to remove an item from the index.
	 *
	 * @param   string  $id  The ID of the item to remove.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function remove($id)
	{
		JLog::add('FinderIndexerAdapter::remove', JLog::INFO);

		// Get the item's URL
		$url = $this->db->quote($this->getUrl($id, $this->extension, $this->layout));

		// Get the link ids for the content items.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('link_id'));
		$query->from($this->db->quoteName('#__finder_links'));
		$query->where($this->db->quoteName('url') . ' = ' . $url);
		$this->db->setQuery($query);
		$items = $this->db->loadColumn();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		// Check the items.
		if (empty($items))
		{
			return true;
		}

		// Remove the items.
		foreach ($items as $item)
		{
			FinderIndexer::remove($item);
		}

		return true;
	}

	/**
	 * Method to setup the adapter before indexing.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	abstract protected function setup();

	/**
	 * Method to update index data on category access level changes
	 *
	 * @param   JTable  $row  A JTable object
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function categoryAccessChange($row)
	{
		$sql = clone($this->getStateQuery());
		$sql->where('c.id = ' . (int) $row->id);

		// Get the access level.
		$this->db->setQuery($sql);
		$items = $this->db->loadObjectList();

		// Adjust the access level for each item within the category.
		foreach ($items as $item)
		{
			// Set the access level.
			$temp = max($item->access, $row->access);

			// Update the item.
			$this->change((int) $item->id, 'access', $temp);

			// Reindex the item
			$this->reindex($row->id);
		}
	}

	/**
	 * Method to update index data on category access level changes
	 *
	 * @param   array    $pks    A list of primary key ids of the content that has changed state.
	 * @param   integer  $value  The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function categoryStateChange($pks, $value)
	{
		// The item's published state is tied to the category
		// published state so we need to look up all published states
		// before we change anything.
		foreach ($pks as $pk)
		{
			$sql = clone($this->getStateQuery());
			$sql->where('c.id = ' . (int) $pk);

			// Get the published states.
			$this->db->setQuery($sql);
			$items = $this->db->loadObjectList();

			// Adjust the state for each item within the category.
			foreach ($items as $item)
			{
				// Translate the state.
				$temp = $this->translateState($item->state, $value);

				// Update the item.
				$this->change($item->id, 'state', $temp);

				// Reindex the item
				$this->reindex($item->id);
			}
		}
	}

	/**
	 * Method to check the existing access level for categories
	 *
	 * @param   JTable  $row  A JTable object
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function checkCategoryAccess($row)
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('access'));
		$query->from($this->db->quoteName('#__categories'));
		$query->where($this->db->quoteName('id') . ' = ' . (int)$row->id);
		$this->db->setQuery($query);

		// Store the access level to determine if it changes
		$this->old_cataccess = $this->db->loadResult();
	}

	/**
	 * Method to check the existing access level for items
	 *
	 * @param   JTable  $row  A JTable object
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function checkItemAccess($row)
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('access'));
		$query->from($this->db->quoteName($this->table));
		$query->where($this->db->quoteName('id') . ' = ' . (int)$row->id);
		$this->db->setQuery($query);

		// Store the access level to determine if it changes
		$this->old_access = $this->db->loadResult();
	}

	/**
	 * Method to get the number of content items available to index.
	 *
	 * @return  integer  The number of content items available to index.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getContentCount()
	{
		JLog::add('FinderIndexerAdapter::getContentCount', JLog::INFO);

		$return = 0;

		// Get the list query.
		$sql = $this->getListQuery();

		// Check if the query is valid.
		if (empty($sql))
		{
			return $return;
		}

		// Tweak the SQL query to make the total lookup faster.
		if ($sql instanceof JDatabaseQuery)
		{
			$sql = clone($sql);
			$sql->clear('select');
			$sql->select('COUNT(*)');
			$sql->clear('order');
		}

		// Get the total number of content items to index.
		$this->db->setQuery($sql);
		$return = (int) $this->db->loadResult();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		return $return;
	}

	/**
	 * Method to get a content item to index.
	 *
	 * @param   integer  $id  The id of the content item.
	 *
	 * @return  FinderIndexerResult  A FinderIndexerResult object.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getItem($id)
	{
		JLog::add('FinderIndexerAdapter::getItem', JLog::INFO);

		// Get the list query and add the extra WHERE clause.
		$sql = $this->getListQuery();
		$sql->where('a.' . $this->db->quoteName('id') . ' = ' . (int) $id);

		// Get the item to index.
		$this->db->setQuery($sql);
		$row = $this->db->loadAssoc();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		// Convert the item to a result object.
		$item = JArrayHelper::toObject($row, 'FinderIndexerResult');

		// Set the item type.
		$item->type_id = $this->type_id;

		// Set the item layout.
		$item->layout = $this->layout;

		return $item;
	}

	/**
	 * Method to get a list of content items to index.
	 *
	 * @param   integer         $offset  The list offset.
	 * @param   integer         $limit   The list limit.
	 * @param   JDatabaseQuery  $sql     A JDatabaseQuery object. [optional]
	 *
	 * @return  array  An array of FinderIndexerResult objects.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getItems($offset, $limit, $sql = null)
	{
		JLog::add('FinderIndexerAdapter::getItems', JLog::INFO);

		$items = array();

		// Get the content items to index.
		$this->db->setQuery($this->getListQuery($sql), $offset, $limit);
		$rows = $this->db->loadAssocList();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		// Convert the items to result objects.
		foreach ($rows as $row)
		{
			// Convert the item to a result object.
			$item = JArrayHelper::toObject($row, 'FinderIndexerResult');

			// Set the item type.
			$item->type_id = $this->type_id;

			// Set the mime type.
			$item->mime = $this->mime;

			// Set the item layout.
			$item->layout = $this->layout;

			// Set the extension if present
			if (isset($row->extension))
			{
				$item->extension = $row->extension;
			}

			// Add the item to the stack.
			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $sql  A JDatabaseQuery object. [optional]
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($sql = null)
	{
		JLog::add('FinderIndexerAdapter::getListQuery', JLog::INFO);

		// Check if we can use the supplied SQL query.
		$sql = $sql instanceof JDatabaseQuery ? $sql : $this->db->getQuery(true);

		return $sql;
	}

	/**
	 * Method to get the plugin type
	 *
	 * @param   integer  $id  The plugin ID
	 *
	 * @return  string  The plugin type
	 *
	 * @since   2.5
	 */
	protected function getPluginType($id)
	{
		// Prepare the query
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('element'));
		$query->from($this->db->quoteName('#__extensions'));
		$query->where($this->db->quoteName('extension_id') . ' = ' . (int) $id);
		$this->db->setQuery($query);
		$type = $this->db->loadResult();

		return $type;
	}

	/**
	 * Method to get a SQL query to load the published and access states for
	 * an article and category.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getStateQuery()
	{
		$sql = $this->db->getQuery(true);
		// Item ID
		$sql->select('a.id');
		// Item and category published state
		$sql->select('a.' . $this->state_field . ' AS state, c.published AS cat_state');
		// Item and category access levels
		$sql->select('a.access, c.access AS cat_access');
		$sql->from($this->table . ' AS a');
		$sql->join('LEFT', '#__categories AS c ON c.id = a.catid');

		return $sql;
	}

	/**
	 * Method to get the query clause for getting items to update by time.
	 *
	 * @param   string  $time  The modified timestamp.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getUpdateQueryByTime($time)
	{
		JLog::add('FinderIndexerAdapter::getUpdateQueryByTime', JLog::INFO);

		// Build an SQL query based on the modified time.
		$sql = $this->db->getQuery(true);
		$sql->where('a.' . $this->db->quoteName('modified') . ' >= ' . $this->db->quote($time));

		return $sql;
	}

	/**
	 * Method to get the query clause for getting items to update by id.
	 *
	 * @param   array  $ids  The ids to load.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getUpdateQueryByIds($ids)
	{
		JLog::add('FinderIndexerAdapter::getUpdateQueryByIds', JLog::INFO);

		// Build an SQL query based on the item ids.
		$sql = $this->db->getQuery(true);
		$sql->where('a.' . $this->db->quoteName('id') . ' IN(' . implode(',', $ids) . ')');

		return $sql;
	}

	/**
	 * Method to get the type id for the adapter content.
	 *
	 * @return  integer  The numeric type id for the content.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getTypeId()
	{
		JLog::add('FinderIndexerAdapter::getTypeId', JLog::INFO);

		// Get the type id from the database.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('id'));
		$query->from($this->db->quoteName('#__finder_types'));
		$query->where($this->db->quoteName('title') . ' = ' . $this->db->quote($this->type_title));
		$this->db->setQuery($query);
		$result = (int) $this->db->loadResult();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		return $result;
	}

	/**
	 * Method to get the URL for the item. The URL is how we look up the link
	 * in the Finder index.
	 *
	 * @param   integer  $id         The id of the item.
	 * @param   string   $extension  The extension the category is in.
	 * @param   string   $view       The view for the URL.
	 *
	 * @return  string  The URL of the item.
	 *
	 * @since   2.5
	 */
	protected function getURL($id, $extension, $view)
	{
		return 'index.php?option=' . $extension . '&view=' . $view . '&id=' . $id;
	}

	/**
	 * Method to get the page title of any menu item that is linked to the
	 * content item, if it exists and is set.
	 *
	 * @param   string  $url  The url of the item.
	 *
	 * @return  mixed  The title on success, null if not found.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getItemMenuTitle($url)
	{
		JLog::add('FinderIndexerAdapter::getItemMenuTitle', JLog::INFO);

		$return = null;

		// Set variables
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Build a query to get the menu params.
		$sql = $this->db->getQuery(true);
		$sql->select($this->db->quoteName('params'));
		$sql->from($this->db->quoteName('#__menu'));
		$sql->where($this->db->quoteName('link') . ' = ' . $this->db->quote($url));
		$sql->where($this->db->quoteName('published') . ' = 1');
		$sql->where($this->db->quoteName('access') . ' IN (' . $groups . ')');

		// Get the menu params from the database.
		$this->db->setQuery($sql);
		$params = $this->db->loadResult();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		// Check the results.
		if (empty($params))
		{
			return $return;
		}

		// Instantiate the params.
		$params = json_decode($params);

		// Get the page title if it is set.
		if ($params->page_title)
		{
			$return = $params->page_title;
		}

		return $return;
	}

	/**
	 * Method to update index data on access level changes
	 *
	 * @param   JTable  $row  A JTable object
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function itemAccessChange($row)
	{
		$sql = clone($this->getStateQuery());
		$sql->where('a.id = ' . (int) $row->id);

		// Get the access level.
		$this->db->setQuery($sql);
		$item = $this->db->loadObject();

		// Set the access level.
		$temp = max($row->access, $item->cat_access);

		// Update the item.
		$this->change((int) $row->id, 'access', $temp);
	}

	/**
	 * Method to update index data on published state changes
	 *
	 * @param   array    $pks    A list of primary key ids of the content that has changed state.
	 * @param   integer  $value  The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function itemStateChange($pks, $value)
	{
		// The item's published state is tied to the category
		// published state so we need to look up all published states
		// before we change anything.
		foreach ($pks as $pk)
		{
			$sql = clone($this->getStateQuery());
			$sql->where('a.id = ' . (int) $pk);

			// Get the published states.
			$this->db->setQuery($sql);
			$item = $this->db->loadObject();

			// Translate the state.
			$temp = $this->translateState($value, $item->cat_state);

			// Update the item.
			$this->change($pk, 'state', $temp);

			// Reindex the item
			$this->reindex($pk);
		}
	}

	/**
	 * Method to update index data when a plugin is disabled
	 *
	 * @param   array  $pks  A list of primary key ids of the content that has changed state.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function pluginDisable($pks)
	{
		// Since multiple plugins may be disabled at a time, we need to check first
		// that we're handling the appropriate one for the context
		foreach ($pks as $pk)
		{
			if ($this->getPluginType($pk) == strtolower($this->context))
			{
				// Get all of the items to unindex them
				$sql = clone($this->getStateQuery());
				$this->db->setQuery($sql);
				$items = $this->db->loadColumn();

				// Remove each item
				foreach ($items as $item)
				{
					$this->remove($item);
				}
			}
		}
	}

	/**
	 * Method to translate the native content states into states that the
	 * indexer can use.
	 *
	 * @param   integer  $item      The item state.
	 * @param   integer  $category  The category state. [optional]
	 *
	 * @return  integer  The translated indexer state.
	 *
	 * @since   2.5
	 */
	protected function translateState($item, $category = null)
	{
		// If category is present, factor in its states as well
		if ($category !== null)
		{
			if ($category == 0)
			{
				$item = 0;
			}
		}

		// Translate the state
		switch ($item)
		{
			// Published and archived items only should return a published state
			case 1;
			case 2:
				return 1;

			// All other states should return a unpublished state
			default:
			case 0:
				return 0;
		}
	}
}
