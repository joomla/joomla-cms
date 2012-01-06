<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

require_once JPATH_SITE.'/components/com_weblinks/helpers/route.php';

/**
 * Weblinks Search plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Search.weblinks
 * @since		1.6
 */
class plgSearchWeblinks extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Weblinks';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_weblinks';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'weblink';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Web Link';

	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas() {
		static $areas = array(
			'weblinks' => 'PLG_SEARCH_WEBLINKS_WEBLINKS'
			);
			return $areas;
	}

	/**
	 * Weblink Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	 */
	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		= JFactory::getDbo();
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		$searchText = $text;

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}

		$sContent		= $this->params->get('search_content',		1);
		$sArchived		= $this->params->get('search_archived',		1);
		$limit			= $this->params->def('search_limit',		50);
		$state = array();
		if ($sContent) {
			$state[]=1;
		}
		if ($sArchived) {
			$state[]=2;
		}

		$text = trim($text);
		if ($text == '') {
			return array();
		}
		$section	= JText::_('PLG_SEARCH_WEBLINKS');

		$wheres	= array();
		switch ($phrase)
		{
			case 'exact':
				$text		= $db->Quote('%'.$db->escape($text, true).'%', false);
				$wheres2	= array();
				$wheres2[]	= 'a.url LIKE '.$text;
				$wheres2[]	= 'a.description LIKE '.$text;
				$wheres2[]	= 'a.title LIKE '.$text;
				$where		= '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words	= explode(' ', $text);
				$wheres = array();
				foreach ($words as $word)
				{
					$word		= $db->Quote('%'.$db->escape($word, true).'%', false);
					$wheres2	= array();
					$wheres2[]	= 'a.url LIKE '.$word;
					$wheres2[]	= 'a.description LIKE '.$word;
					$wheres2[]	= 'a.title LIKE '.$word;
					$wheres[]	= implode(' OR ', $wheres2);
				}
				$where	= '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
		}

		$return = array();
		if (!empty($state)) {
			$query	= $db->getQuery(true);
	        //sqlsrv changes
	        $case_when = ' CASE WHEN ';
	        $case_when .= $query->charLength('a.alias');
	        $case_when .= ' THEN ';
	        $a_id = $query->castAsChar('a.id');
	        $case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
	        $case_when .= ' ELSE ';
	        $case_when .= $a_id.' END as slug';   
	      
	        $case_when1 = ' CASE WHEN ';
	        $case_when1 .= $query->charLength('c.alias');
	        $case_when1 .= ' THEN ';
	        $c_id = $query->castAsChar('c.id');
	        $case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
	        $case_when1 .= ' ELSE ';
	        $case_when1 .= $c_id.' END as catslug'; 
      
			$query->select('a.title AS title, a.description AS text, a.created AS created, a.url, '
						.$case_when.','.$case_when1.', '
						.$query->concatenate(array($db->Quote($section), "c.title"), " / ").' AS section, \'1\' AS browsernav');
			$query->from('#__weblinks AS a');
			$query->innerJoin('#__categories AS c ON c.id = a.catid');
			$query->where('('.$where.')' . ' AND a.state in ('.implode(',',$state).') AND  c.published=1 AND  c.access IN ('.$groups.')');
			$query->order($order);

			// Filter by language
			if ($app->isSite() && $app->getLanguageFilter()) {
				$tag = JFactory::getLanguage()->getTag();
				$query->where('a.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
				$query->where('c.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
			}

			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList();

			$return = array();
			if ($rows) {
				foreach($rows as $key => $row) {
					$rows[$key]->href = WeblinksHelperRoute::getWeblinkRoute($row->slug, $row->catslug);
				}

				foreach($rows as $key => $weblink) {
					if (searchHelper::checkNoHTML($weblink, $searchText, array('url', 'text', 'title'))) {
						$return[] = $weblink;
					}
				}
			}
		}
		return $return;
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
	public function onSearchCategoryChangeState($extension, $pks, $value)
	{
		// Make sure we're handling com_weblinks categories
		if ($extension == 'com_weblinks')
		{
			// The web link published state is tied to the category
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
					FinderIndexerQueue::add('com_weblinks.weblink', $item->id, JFactory::getDate()->toMySQL());
				}
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
	public function onSearchAfterDelete($context, $table)
	{
		if ($context == 'com_weblinks.weblink')
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
	public function onSearchAfterSave($context, $row, $isNew)
	{
		// We only want to handle web links here. We need to handle front end and back end editing.
		if ($context == 'com_weblinks.weblink' || $context == 'com_weblinks.form' )
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
			//FinderIndexerQueue::add($context, $row->id, JFactory::getDate()->toSQL());
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
					//FinderIndexerQueue::add('com_weblinks.weblink', $row->id, JFactory::getDate()->toSQL());
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
	public function onSearchBeforeSave($context, $row, $isNew)
	{
		// We only want to handle web links here
		if ($context == 'com_weblinks.weblink' || $context == 'com_weblinks.form')
		{
			// Query the database for the old access level if the item isn't new
			if (!$isNew)
			{
				$query = $this->db->getQuery(true);
				$query->select($this->db->quoteName('access'));
				$query->from($this->db->quoteName('#__weblinks'));
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
	public function onSearchChangeState($context, $pks, $value)
	{
		// We only want to handle web links here
		if ($context == 'com_weblinks.weblink' || $context == 'com_weblinks.form')
		{

			// The web link published state is tied to the category
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
				// FinderIndexerQueue::add($context, $pk, JFactory::getDate()->toSQL());
			}
		}
		// Handle when the plugin is disabled
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			// Since multiple plugins may be disabled at a time, we need to check first
			// that we're handling web links
			foreach ($pks as $pk)
			{
				if ($this->getPluginType($pk) == 'weblinks')
				{
					// Get all of the web links to unindex them
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
	protected function index(FinderIndexerResult $item, $format = 'html')
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

		$registry = new JRegistry;
		$registry->loadString($item->metadata);
		$item->metadata = $registry;

		// Build the necessary route and path information.
		$item->url = $this->getURL($item->id, $this->extension, $this->layout);
		$item->route = WeblinksHelperRoute::getWeblinkRoute($item->slug, $item->catslug);
		$item->path = FinderIndexerHelper::getContentPath($item->route);

		/*
		 * Add the meta-data processing instructions based on the newsfeeds
		 * configuration parameters.
		 */
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
		$item->addTaxonomy('Type', 'Web Link');

		// Add the category taxonomy data.
		$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

		// Add the language taxonomy data.
-		$item->addTaxonomy('Language', $item->language);

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
		require_once JPATH_SITE . '/components/com_weblinks/helpers/route.php';

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
		$sql->select('a.id, a.catid, a.title, a.alias, a.url AS link, a.description AS summary');
		$sql->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.ordering');
		$sql->select('a.created_by_alias, a.modified, a.modified_by');
		$sql->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
		$sql->select('a.state AS state, a.ordering, a.access, a.approved, a.created AS start_date, a.params');
		$sql->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');
		$sql->select('CASE WHEN CHAR_LENGTH(a.alias) THEN ' . $sql->concatenate(array('a.id', 'a.alias'), ':') . ' ELSE a.id END as slug');
		$sql->select('CASE WHEN CHAR_LENGTH(c.alias) THEN ' . $sql->concatenate(array('c.id', 'c.alias'), ':') . ' ELSE c.id END as catslug');
		$sql->from('#__weblinks AS a');
		$sql->join('LEFT', '#__categories AS c ON c.id = a.catid');
		$sql->where('a.approved = 1');

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
		// Build an SQL query based on the modified time.
		$sql = $this->db->getQuery(true);
		$sql->where('a.date >= ' . $this->db->quote($time));

		return $sql;
	}

	/**
	 * Method to get a SQL query to load the published and access states for
	 * an web link and category.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	private function _getStateQuery()
	{
		$sql = $this->db->getQuery(true);
		$sql->select('a.id');
		$sql->select('a.state AS state, c.published AS cat_state');
		$sql->select('a.access AS access, c.access AS cat_access');
		$sql->from('#__weblinks AS a');
		$sql->join('LEFT', '#__categories AS c ON c.id = a.catid');

		return $sql;
	}
}
