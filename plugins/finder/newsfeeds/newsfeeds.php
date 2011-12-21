<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

jimport('joomla.application.component.helper');

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

/**
 * Finder adapter for Joomla Newsfeeds.
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.Newsfeeds
 * @since       2.5
 */
class plgFinderNewsfeeds extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Newsfeeds';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_newsfeeds';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'newsfeed';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'News Feed';

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   2.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Method to update the item link information when the item category is
	 * changed. This is fired when the item category is published or unpublished
	 * from the list view.
	 *
	 * @param   string   $extension  The extension whose category has been updated.
	 * @param   array    $pks        A list of primary key ids of the content that has changed state.
	 * @param   integer  $value      The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderCategoryChangeState($extension, $pks, $value)
	{
		// Make sure we're handling com_newsfeeds categories
		if ($extension != 'com_newsfeeds')
		{
			return;
		}

		// The news feed published state is tied to the category
		// published state so we need to look up all published states
		// before we change anything.
		foreach ($pks as $pk)
		{
			$sql = clone($this->_getStateQuery());
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

				// Queue the item to be reindexed.
				FinderIndexerQueue::add('com_newsfeeds.newsfeed', $item->id, JFactory::getDate()->toMySQL());
			}
		}
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string  $context  The context of the action being performed.
	 * @param   JTable  $table    A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_newsfeeds.newsfeed')
		{
			$id = $table->id;
		}
		elseif ($context == 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return true;
		}
		// Remove the items.
		return $this->remove($id);
	}

	/**
	 * Method to determine if the access level of an item changed.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row      A JTable object
	 * @param   boolean  $isNew    If the content has just been created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle news feeds here
		if ($context == 'com_newsfeeds.newsfeed')
		{
			// Check if the access levels are different
			if (!$isNew && $this->old_access != $row->access)
			{
				$sql = clone($this->_getStateQuery());
				$sql->where('a.id = ' . (int) $row->id);

				// Get the access level.
				$this->db->setQuery($sql);
				$item = $this->db->loadObject();

				// Set the access level.
				$temp = max($row->access, $item->cat_access);

				// Update the item.
				$this->change((int) $row->id, 'access', $temp);
			}

			// Queue the item to be reindexed.
			FinderIndexerQueue::add($context, $row->id, JFactory::getDate()->toMySQL());
		}

		// Check for access changes in the category
		if ($context == 'com_categories.category')
		{
			// Check if the access levels are different
			if (!$isNew && $this->old_cataccess != $row->access)
			{
				$sql = clone($this->_getStateQuery());
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

					// Queue the item to be reindexed.
					FinderIndexerQueue::add('com_newsfeeds.newsfeed', $row->id, JFactory::getDate()->toMySQL());
				}
			}
		}

		return true;
	}

	/**
	 * Method to reindex the link information for an item that has been saved.
	 * This event is fired before the data is actually saved so we are going
	 * to queue the item to be indexed later.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row     A JTable object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// We only want to handle news feeds here
		if ($context == 'com_newsfeeds.newsfeed')
		{
			// Query the database for the old access level if the item isn't new
			if (!$isNew)
			{
				$query = $this->db->getQuery(true);
				$query->select($this->db->quoteName('access'));
				$query->from($this->db->quoteName('#__newsfeeds'));
				$query->where($this->db->quoteName('id') . ' = ' . $row->id);
				$this->db->setQuery($query);

				// Store the access level to determine if it changes
				$this->old_access = $this->db->loadResult();
			}
		}

		// Check for access levels from the category
		if ($context == 'com_categories.category')
		{
			// Query the database for the old access level if the item isn't new
			if (!$isNew)
			{
				$query = $this->db->getQuery(true);
				$query->select($this->db->quoteName('access'));
				$query->from($this->db->quoteName('#__categories'));
				$query->where($this->db->quoteName('id') . ' = ' . $row->id);
				$this->db->setQuery($query);

				// Store the access level to determine if it changes
				$this->old_cataccess = $this->db->loadResult();
			}
		}

		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      A list of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle news feeds here
		if ($context != 'com_newsfeeds.newsfeed')
		{
			// The news feed published state is tied to the category
			// published state so we need to look up all published states
			// before we change anything.
			foreach ($pks as $pk)
			{
				$sql = clone($this->_getStateQuery());
				$sql->where('a.id = ' . (int) $pk);

				// Get the published states.
				$this->db->setQuery($sql);
				$item = $this->db->loadObject();

				// Translate the state.
				$temp = $this->translateState($value, $item->cat_state);

				// Update the item.
				$this->change($pk, 'state', $temp);

				// Queue the item to be reindexed.
				FinderIndexerQueue::add($context, $pk, JFactory::getDate()->toMySQL());
			}
		}

		// Handle when the plugin is disabled
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			// Since multiple plugins may be disabled at a time, we need to check first
			// that we're handling news feeds
			foreach ($pks as $pk)
			{
				if ($this->getPluginType($pk) == 'newsfeeds')
				{
					// Get all of the news feeds to unindex them
					$sql = clone($this->_getStateQuery());
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
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item  The item to index as an FinderIndexerResult object.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function index(FinderIndexerResult $item)
	{
		// Check if the extension is enabled
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Initialize the item parameters.
		$registry = new JRegistry;
		$registry->loadString($item->params);
		$item->params = $registry;

		// Build the necessary route and path information.
		$item->url = $this->getURL($item->id, $this->extension, $this->layout);
		$item->route = NewsfeedsHelperRoute::getNewsfeedRoute($item->slug, $item->catslug);
		$item->path = FinderIndexerHelper::getContentPath($item->route);

		// Handle the link to the meta-data.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'link');

		// Set the language.
		$item->language = FinderIndexerHelper::getDefaultLanguage();

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'News Feed');

		// Add the category taxonomy data.
		if (!empty($item->category))
		{
			$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);
		}

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		// Index the item.
		FinderIndexer::index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	protected function setup()
	{
		// Load dependent classes.
		require_once JPATH_SITE . '/includes/application.php';
		require_once JPATH_SITE . '/components/com_newsfeeds/helpers/route.php';

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $sql  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($sql = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : $db->getQuery(true);
		$sql->select('a.id, a.catid, a.name AS title, a.alias, a.link AS link');
		$sql->select('a.published AS state, a.ordering, a.created AS start_date, a.params, a.access');
		$sql->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
		$sql->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');
		$sql->select('CASE WHEN CHAR_LENGTH(a.alias) THEN ' . $sql->concatenate(array('a.id', 'a.alias'), ':') . ' ELSE a.id END as slug');
		$sql->select('CASE WHEN CHAR_LENGTH(c.alias) THEN ' . $sql->concatenate(array('c.id', 'c.alias'), ':') . ' ELSE c.id END as catslug');
		$sql->from('#__newsfeeds AS a');
		$sql->join('LEFT', '#__categories AS c ON c.id = a.catid');

		return $sql;
	}

	/**
	 * Method to get a SQL query to load the published and access states for
	 * a news feed and category.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	private function _getStateQuery()
	{
		$sql = $this->db->getQuery(true);
		$sql->select('a.id');
		$sql->select('a.published AS state, c.published AS cat_state');
		$sql->select('a.access AS access, c.access AS cat_access');
		$sql->from('#__newsfeeds AS a');
		$sql->join('LEFT', '#__categories AS c ON c.id = a.catid');

		return $sql;
	}
}
