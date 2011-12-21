<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Register dependent classes.
JLoader::register('FinderIndexer', dirname(__FILE__) . DS . 'indexer.php');
JLoader::register('FinderIndexerHelper', dirname(__FILE__) . DS . 'helper.php');
JLoader::register('FinderIndexerQueue', dirname(__FILE__) . DS . 'queue.php');
JLoader::register('FinderIndexerResult', dirname(__FILE__) . DS . 'result.php');
JLoader::register('FinderIndexerTaxonomy', dirname(__FILE__) . DS . 'taxonomy.php');

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
	 * Method to get the adapter state and push it into the updater.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws	Exception on error.
	 */
	public function onStartUpdate()
	{
		JLog::add('FinderIndexerAdapter::onStartUpdate', JLog::INFO);

		// Get the indexer state.
		$iState = FinderIndexer::getState();

		// Get the indexer queue.
		$queue = FinderIndexerQueue::get($this->context);

		// Get the number of content items to update.
		$total = count($queue);

		// Add the count to the total number of items.
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
	 * Method to index a batch of content items. This method can be called by
	 * the updater many times throughout the updating process depending on how
	 * much content is available for updating. It is important to track the
	 * progress correctly so we can display it to the user.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on error.
	 */
	public function onBuildUpdate()
	{
		JLog::add('FinderIndexerAdapter::onBuildUpdate', JLog::INFO);

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

		// Get the indexer queue.
		$queue = FinderIndexerQueue::get($this->context);

		/*
		 * We need to start building an SQL query that we will use to fetch
		 * the modified records. This will serve as the foundation and will be
		 * augmented to fetch the actual data by the getListQuery()
		 */
		if (array_key_exists(0, $queue) === true)
		{
			// Get the timestamp of the first item in the queue.
			$first = array_shift(array_values($queue));
			$time = $first['timestamp'];

			// Get the query to load the items by time.
			$sql = $this->getUpdateQueryByTime($time);
		}
		else
		{
			// Create an array of ids to fetch.
			$ids = array_keys($queue);
			JArrayHelper::toInteger($ids);

			// Get the query to load the items by id.
			$sql = $this->getUpdateQueryByIds($ids);
		}

		// Get the content items to index.
		$items = $this->getItems(0, count($queue), $sql);

		// Check if any items were returned.
		if (count($items))
		{
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
		}
		else
		{
			// Flush the queue for this context.
			FinderIndexerQueue::remove($this->context);

			// Update indexer state to prevent endless polling.
			$offset += count($queue);
			$iState->batchOffset += count($queue);
			$iState->totalItems -= count($queue);
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
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : $this->db->getQuery(true);

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
			// Unpublished
			case 0:
				return 0;

			// Published
			default:
			case 1:
				return 1;
		}

		// Shouldn't get this far, but return the original item just in case
		return $item;
	}
}
