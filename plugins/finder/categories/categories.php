<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Categories
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

jimport('joomla.application.component.helper');

// Load the base adapter.
require_once JPATH_ADMINISTRATOR.'/components/com_finder/helpers/indexer/adapter.php';

/**
 * Finder adapter for Joomla Categories.
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.Categories
 * @since       2.5
 */
class PlgFinderCategories extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Categories';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_categories';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'category';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Category';

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
	public function onContentAfterDelete($context, $table)
	{
		if ($context == 'com_categories.category')
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
	 * @param   JTable   &$row     A JTable object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onContentAfterSave($context, &$row, $isNew)
	{
		// We only want to handle categories here
		if ($context != 'com_categories.category')
		{
			return true;
		}

		// Check if the access levels are different
		if (!$isNew && $this->old_access != $row->access)
		{
			$sql = clone($this->_getStateQuery());
			$sql->where('c.id = '.(int)$row->id);

			// Get the access level.
			$this->db->setQuery($sql);
			$item = $this->db->loadObject();

			// Set the access level.
			$temp = max($row->access, $item->cat_access);

			// Update the item.
			$this->change((int)$row->id, 'access', $temp);

			// Queue the item to be reindexed.
			FinderIndexerQueue::add($context, $row->id, JFactory::getDate()->toMySQL());
		}

		return true;
	}

	/**
	 * Method to reindex the link information for an item that has been saved.
	 * This event is fired before the data is actually saved so we are going
	 * to queue the item to be indexed later.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   &$row     A JTable object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onContentBeforeSave($context, &$row, $isNew)
	{
		// We only want to handle categories here
		if ($context != 'com_categories.category')
		{
			return true;
		}

		// Query the database for the old access level if the item isn't new
		if (!$isNew)
		{
			$query = $this->db->getQuery(true);
			$query->select($this->db->quoteName('access'));
			$query->from($this->db->quoteName('#__categories'));
			$query->where($this->db->quoteName('id').' = '.$row->id);
			$this->db->setQuery($query);

			// Store the access level to determine if it changes
			$this->old_access = $this->db->loadResult();
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
	public function onContentChangeState($context, $pks, $value)
	{
		// We only want to handle categories here
		if ($context != 'com_categories.category')
		{
			return;
		}

		// The article published state is tied to the category
		// published state so we need to look up all published states
		// before we change anything.
		foreach ($pks as $pk)
		{
			$sql = clone($this->_getStateQuery());
			$sql->where('c.id = '.(int)$pk);

			// Get the published states.
			$this->db->setQuery($sql);
			$item = $this->db->loadObject();

			// Translate the state.
			$temp = $this->translateState($value);

			// Update the item.
			$this->change($pk, 'state', $temp);

			// Queue the item to be reindexed.
			FinderIndexerQueue::add($context, $pk, JFactory::getDate()->toMySQL());
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
		$item->params	= $registry;

		// Trigger the onPrepareContent event.
		$item->summary	= FinderIndexerHelper::prepareContent($item->summary, $item->params);

		// Build the necessary route and path information.
		$item->url		= $this->getURL($item->id, $item->extension, $this->layout);
		$item->route	= ContentHelperRoute::getCategoryRoute($item->slug, $item->catid);
		$item->path		= FinderIndexerHelper::getContentPath($item->route);

		// Get the menu title if it exists.
		$title = $this->getItemMenuTitle($item->url);

		// Adjust the title if necessary.
		if (!empty($title) && $this->params->get('use_menu_title', true))
		{
			$item->title = $title;
		}

		// Translate the state. Categories should only be published if the section is published.
		$item->state = $this->translateState($item->state);

		// Set the language.
		$item->language	= $item->params->get('language', FinderIndexerHelper::getDefaultLanguage());

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Category');

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
		include_once JPATH_SITE.'/components/com_content/helpers/route.php';

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $sql  A JDatabaseQuery object or null.
	 *
	 * @return  object  A JDatabaseQuery object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($sql = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : $db->getQuery(true);
		$sql->select('a.id, a.title, a.alias, a.description AS summary, a.extension');
		$sql->select('a.created_time AS start_date, a.published AS state, a.access, a.params');
		$sql->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug');
		$sql->from('#__categories AS a');
		$sql->where($db->quoteName('a.id').' > 1');

		return $sql;
	}

	/**
	 * Method to get a SQL query to load the published and access states for
	 * a category and section.
	 *
	 * @return  object  A JDatabaseQuery object.
	 *
	 * @since   2.5
	 */
	private function _getStateQuery()
	{
		$sql = $this->db->getQuery(true);
		$sql->select($this->db->quoteName('c.id'));
		$sql->select($this->db->quoteName('c.published').' AS cat_state');
		$sql->select($this->db->quoteName('c.access').' AS cat_access');
		$sql->from($this->db->quoteName('#__categories').' AS c');

		return $sql;
	}
}
