<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('FinderIndexer', __DIR__ . '/indexer.php');
JLoader::register('FinderIndexerHelper', __DIR__ . '/helper.php');
JLoader::register('FinderIndexerResult', __DIR__ . '/result.php');
JLoader::register('FinderIndexerTaxonomy', __DIR__ . '/taxonomy.php');

/**
 * Prototype adapter class for the Finder indexer package.
 *
 * @since  2.5
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
	 * The indexer object.
	 *
	 * @var    FinderIndexer
	 * @since  3.0
	 */
	protected $indexer;

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
		$this->db = JFactory::getDbo();

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

		// Get the indexer object
		$this->indexer = FinderIndexer::getInstance();
	}

	/**
	 * Method to get the adapter state and push it into the indexer.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on error.
	 */
	public function onStartIndex()
	{
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
	 * @throws  Exception on database error.
	 */
	protected function change($id, $property, $value)
	{
		// Check for a property we know how to handle.
		if ($property !== 'state' && $property !== 'access')
		{
			return true;
		}

		// Get the URL for the content id.
		$item = $this->db->quote($this->getUrl($id, $this->extension, $this->layout));

		// Update the content items.
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__finder_links'))
			->set($this->db->quoteName($property) . ' = ' . (int) $value)
			->where($this->db->quoteName('url') . ' = ' . $item);
		$this->db->setQuery($query);
		$this->db->execute();

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

		// Remove the old item.
		$this->remove($id);

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
		// Get the item's URL
		$url = $this->db->quote($this->getUrl($id, $this->extension, $this->layout));

		// Get the link ids for the content items.
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('link_id'))
			->from($this->db->quoteName('#__finder_links'))
			->where($this->db->quoteName('url') . ' = ' . $url);
		$this->db->setQuery($query);
		$items = $this->db->loadColumn();

		// Check the items.
		if (empty($items))
		{
			return true;
		}

		// Remove the items.
		foreach ($items as $item)
		{
			$this->indexer->remove($item);
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
		$query = clone $this->getStateQuery();
		$query->where('c.id = ' . (int) $row->id);

		// Get the access level.
		$this->db->setQuery($query);
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
		/*
		 * The item's published state is tied to the category
		 * published state so we need to look up all published states
		 * before we change anything.
		 */
		foreach ($pks as $pk)
		{
			$query = clone $this->getStateQuery();
			$query->where('c.id = ' . (int) $pk);

			// Get the published states.
			$this->db->setQuery($query);
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
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('access'))
			->from($this->db->quoteName('#__categories'))
			->where($this->db->quoteName('id') . ' = ' . (int) $row->id);
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
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('access'))
			->from($this->db->quoteName($this->table))
			->where($this->db->quoteName('id') . ' = ' . (int) $row->id);
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
		$return = 0;

		// Get the list query.
		$query = $this->getListQuery();

		// Check if the query is valid.
		if (empty($query))
		{
			return $return;
		}

		// Tweak the SQL query to make the total lookup faster.
		if ($query instanceof JDatabaseQuery)
		{
			$query = clone $query;
			$query->clear('select')
				->select('COUNT(*)')
				->clear('order');
		}

		// Get the total number of content items to index.
		$this->db->setQuery($query);

		return (int) $this->db->loadResult();
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
		// Get the list query and add the extra WHERE clause.
		$query = $this->getListQuery();
		$query->where('a.id = ' . (int) $id);

		// Get the item to index.
		$this->db->setQuery($query);
		$row = $this->db->loadAssoc();

		// Convert the item to a result object.
		$item = ArrayHelper::toObject((array) $row, 'FinderIndexerResult');

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
	 * @param   JDatabaseQuery  $query   A JDatabaseQuery object. [optional]
	 *
	 * @return  array  An array of FinderIndexerResult objects.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getItems($offset, $limit, $query = null)
	{
		$items = array();

		// Get the content items to index.
		$this->db->setQuery($this->getListQuery($query), $offset, $limit);
		$rows = $this->db->loadAssocList();

		// Convert the items to result objects.
		foreach ($rows as $row)
		{
			// Convert the item to a result object.
			$item = ArrayHelper::toObject((array) $row, 'FinderIndexerResult');

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
	 * @param   mixed  $query  A JDatabaseQuery object. [optional]
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($query = null)
	{
		// Check if we can use the supplied SQL query.
		return $query instanceof JDatabaseQuery ? $query : $this->db->getQuery(true);
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
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('element'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('extension_id') . ' = ' . (int) $id);
		$this->db->setQuery($query);

		return $this->db->loadResult();
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
		$query = $this->db->getQuery(true);

		// Item ID
		$query->select('a.id');

		// Item and category published state
		$query->select('a.' . $this->state_field . ' AS state, c.published AS cat_state');

		// Item and category access levels
		$query->select('a.access, c.access AS cat_access')
			->from($this->table . ' AS a')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		return $query;
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
		// Build an SQL query based on the modified time.
		$query = $this->db->getQuery(true)
			->where('a.modified >= ' . $this->db->quote($time));

		return $query;
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
		// Build an SQL query based on the item ids.
		$query = $this->db->getQuery(true)
			->where('a.id IN(' . implode(',', $ids) . ')');

		return $query;
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
		// Get the type id from the database.
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__finder_types'))
			->where($this->db->quoteName('title') . ' = ' . $this->db->quote($this->type_title));
		$this->db->setQuery($query);

		return (int) $this->db->loadResult();
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
	protected function getUrl($id, $extension, $view)
	{
		return 'index.php?option=' . $extension . '&view=' . $view . '&id=' . $id;
	}

	/**
	 * Method to get the page title of any menu item that is linked to the
	 * content item, if it exists and is set.
	 *
	 * @param   string  $url  The URL of the item.
	 *
	 * @return  mixed  The title on success, null if not found.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getItemMenuTitle($url)
	{
		$return = null;

		// Set variables
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Build a query to get the menu params.
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('params'))
			->from($this->db->quoteName('#__menu'))
			->where($this->db->quoteName('link') . ' = ' . $this->db->quote($url))
			->where($this->db->quoteName('published') . ' = 1')
			->where($this->db->quoteName('access') . ' IN (' . $groups . ')');

		// Get the menu params from the database.
		$this->db->setQuery($query);
		$params = $this->db->loadResult();

		// Check the results.
		if (empty($params))
		{
			return $return;
		}

		// Instantiate the params.
		$params = json_decode($params);

		// Get the page title if it is set.
		if (isset($params->page_title) && $params->page_title)
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
		$query = clone $this->getStateQuery();
		$query->where('a.id = ' . (int) $row->id);

		// Get the access level.
		$this->db->setQuery($query);
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
		/*
		 * The item's published state is tied to the category
		 * published state so we need to look up all published states
		 * before we change anything.
		 */
		foreach ($pks as $pk)
		{
			$query = clone $this->getStateQuery();
			$query->where('a.id = ' . (int) $pk);

			// Get the published states.
			$this->db->setQuery($query);
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
				$query = clone $this->getStateQuery();
				$this->db->setQuery($query);
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

			// All other states should return an unpublished state
			default:
			case 0:
				return 0;
		}
	}
}
