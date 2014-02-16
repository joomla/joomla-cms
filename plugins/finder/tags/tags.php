<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Tags
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

/**
 * Finder adapter for Joomla Tag.
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.Tags
 * @since       3.1
 */
class PlgFinderTags extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $context = 'Tags';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $extension = 'com_tags';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $layout = 'tag';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $type_title = 'Tag';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $table = '#__tags';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * The field the published state is stored in.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $state_field = 'published';

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string  $context  The context of the action being performed.
	 * @param   JTable  $table    A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_tags.tag')
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
	 * @since   3.1
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle tags here.
		if ($context == 'com_tags.tag')
		{
			// Check if the access levels are different
			if (!$isNew && $this->old_access != $row->access)
			{
				// Process the change.
				$this->itemAccessChange($row);
			}

			// Reindex the item
			$this->reindex($row->id);
		}

		return true;
	}

	/**
	 * Method to reindex the link information for an item that has been saved.
	 * This event is fired before the data is actually saved so we are going
	 * to queue the item to be indexed later.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row      A JTable object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// We only want to handle news feeds here
		if ($context == 'com_tags.tag')
		{
			// Query the database for the old access level if the item isn't new
			if (!$isNew)
			{
				$this->checkItemAccess($row);
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
	 * @since   3.1
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle tags here
		if ($context == 'com_tags.tag')
		{
			$this->itemStateChange($pks, $value);
		}
		// Handle when the plugin is disabled
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item    The item to index as an FinderIndexerResult object.
	 * @param   string               $format  The item format
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @throws  Exception on database error.
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		// Check if the extension is enabled
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		$item->setLanguage();

		// Initialize the item parameters.
		$registry = new JRegistry;
		$registry->loadString($item->params);
		$item->params = JComponentHelper::getParams('com_tags', true);
		$item->params->merge($registry);

		$registry = new JRegistry;
		$registry->loadString($item->metadata);
		$item->metadata = $registry;

		// Build the necessary route and path information.
		$item->url = $this->getURL($item->id, $this->extension, $this->layout);
		$item->route = TagsHelperRoute::getTagRoute($item->slug);
		$item->path = FinderIndexerHelper::getContentPath($item->route);

		// Get the menu title if it exists.
		$title = $this->getItemMenuTitle($item->url);

		// Adjust the title if necessary.
		if (!empty($title) && $this->params->get('use_menu_title', true))
		{
			$item->title = $title;
		}

		// Add the meta-author.
		$item->metaauthor = $item->metadata->get('author');

		// Handle the link to the meta-data.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'link');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'created_by_alias');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Tag');

		// Add the author taxonomy data.
		if (!empty($item->author) || !empty($item->created_by_alias))
		{
			$item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author);
		}

		// Add the language taxonomy data.
		$item->addTaxonomy('Language', $item->language);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	protected function setup()
	{
		// Load dependent classes.
		require_once JPATH_SITE . '/components/com_tags/helpers/route.php';

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $query  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   3.1
	 */
	protected function getListQuery($query = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.description AS summary')
			->select('a.created_time AS start_date, a.created_user_id AS created_by')
			->select('a.metakey, a.metadesc, a.metadata, a.language, a.access')
			->select('a.modified_time AS modified, a.modified_user_id AS modified_by')
			->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
			->select('a.published AS state, a.access, a.created_time AS start_date, a.params');

		// Handle the alias CASE WHEN portion of the query
		$case_when_item_alias = ' CASE WHEN ';
		$case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
		$case_when_item_alias .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when_item_alias .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when_item_alias .= ' ELSE ';
		$case_when_item_alias .= $a_id.' END as slug';
		$query->select($case_when_item_alias)
			->from('#__tags AS a');

		// Join the #__users table
		$query->select('u.name AS author')
			->join('LEFT', '#__users AS u ON u.id = b.created_user_id')
			->from('#__tags AS b');

		// Exclude the ROOT item
		$query->where($db->quoteName('a.id') . ' > 1');

		return $query;
	}

	/**
	 * Method to get a SQL query to load the published and access states for the given tag.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   3.1
	 */
	protected function getStateQuery()
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('a.id'))
			->select($this->db->quoteName('a.' . $this->state_field, 'state') . ', ' . $this->db->quoteName('a.access'))
			->select('NULL AS cat_state, NULL AS cat_access')
			->from($this->db->quoteName($this->table, 'a'));

		return $query;
	}

	/**
	 * Method to get the query clause for getting items to update by time.
	 *
	 * @param   string  $time  The modified timestamp.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   3.1
	 */
	protected function getUpdateQueryByTime($time)
	{
		// Build an SQL query based on the modified time.
		$query = $this->db->getQuery(true)
			->where('a.date >= ' . $this->db->quote($time));

		return $query;
	}
}
