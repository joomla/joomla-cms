<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * About Page Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentModelKeywords extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_content.keywords';
	static $_map_table = '#__content_keyword_article_map';
	static $_authorTag = 'authid::';
	static $_aliasTag = 'alias::';
	static $_categoryTag = 'catid::';
	static $_rowCount = 0;

	/**
	 * Method to auto-populate the model state.
	 *
	 * @since	1.6
	 */
	protected function _populateState()
	{
		$app = &JFactory::getApplication();

		$search = $app->getUserStateFromRequest($this->_context.'.search', 'filter_search');
		$this->setState('filter.search', $search);

		$tags = $app->getUserStateFromRequest($this->_context.'.tags', 'filter_tags', '');
		$this->setState('filter.tags', $tags);

		$published = $app->getUserStateFromRequest($this->_context.'.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$categoryId = $app->getUserStateFromRequest($this->_context.'.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest($this->_context.'.limitstart', 'limitstart', 0);
		$this->setState('list.limitstart', $limitstart);

		$orderCol = $app->getUserStateFromRequest($this->_context.'.ordercol', 'filter_order', 'm.keyword');
		$this->setState('list.ordering', $orderCol);

		$orderDirn = $app->getUserStateFromRequest($this->_context.'.orderdirn', 'filter_order_Dir', 'asc');
		$this->setState('list.direction', $orderDirn);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');

		return md5($id);
	}

	/**
	 * @param	boolean	True to join selected foreign information
	 *
	 * @return	string
	 */
	function _getListQuery($resolveFKs = true)
	{
		// Create a new query object.
		$query = new JQuery;

		// Select the required fields from the table.
		$query->select(
		$this->getState(
				'list.select',
				'm.keyword, ' .  
				'SUM(CASE WHEN a.state BETWEEN -2 AND 1 THEN 1 ELSE 0 END) AS total_articles, ' . 
				'SUM(CASE WHEN a.state = 1 THEN 1 ELSE 0 END) AS published_articles,' . 
				'SUM(CASE WHEN a.state = 0 THEN 1 ELSE 0 END) AS unpublished_articles,' . 
				'SUM(CASE WHEN a.state = -1 THEN 1 ELSE 0 END) AS archived_articles,' .
				'SUM(CASE WHEN a.state = -2 THEN 1 ELSE 0 END) AS trashed_articles')
		);
		$query->from('#__content_keyword_article_map AS m');

		// Join with article table
		$query->join('LEFT', '#__content AS a ON m.article_id = a.id');

		// add grouping
		$query->group('m.keyword');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$query->where('a.catid = ' . (int) $categoryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
			$query->where('m.keyword LIKE '.$search);
		}

		// Filter by tags or keywords
		$tags = $this->getState('filter.tags');
		if (!empty($tags)) {
			switch ($tags) {
				case 'keywords':
					$search = $this->_db->Quote(self::$_aliasTag . '%');
					$query->where('m.keyword NOT LIKE ' . $search);
					$search = $this->_db->Quote(self::$_authorTag . '%');
					$query->where('m.keyword NOT LIKE ' . $search);
					$search = $this->_db->Quote(self::$_categoryTag . '%');
					$query->where('m.keyword NOT LIKE ' . $search);
					break;
				case 'tags':
					$search1 = $this->_db->Quote(self::$_aliasTag . '%');
					$search2 = $this->_db->Quote(self::$_authorTag . '%');
					$search3 = $this->_db->Quote(self::$_categoryTag . '%');
					$query->where('(m.keyword LIKE ' . $search1 . 'OR m.keyword LIKE ' . $search2 .
						' OR m.keyword LIKE ' . $search3 . ')');
					break;
				case 'all':
				default:
					break;
			}
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'm.keyword')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		return $query;
	}
	/**
	 * function rebuild - rebuilds the jos_content_keyword_article_map table
	 * this table is used in the related items module to find articles with matching keywords, author, or category
	 * @return 	array()		element 0 = true if method successful;
	 * 						element 1 = count of articles;
	 * 						element 2 = count of keywords
	 */
	function rebuild() {
		$limit = 1000;
		$offset = 0;
		//set_time_limit(600);
		global $mainframe;
		$result = true; // set return value
		$db	=& JFactory::getDBO();
		// clear the table
		$deleteQuery = 'TRUNCATE ' . self::$_map_table;
		$db->setQuery($deleteQuery);
		if (!$db->query()) {
			$result = false;
		}

		// now insert the rows for each article
		$query = 'SELECT id, metakey, catid, created_by, created_by_alias '.
				' FROM #__content ';
		$db->setQuery($query, $offset, $limit);
		$articleList = $db->loadObjectList();
		$count = count($articleList);
		// outer while loop -- process one chunk at a time
		while ($articleList)
		{
			foreach ($articleList as $article)
			{
				if (!self::_insertArticleRows($db, $article)) 
				{
					$result = false;
				}
			}
			// get the next chunk
			$offset += $limit;
			$db->setQuery($query, $offset, $limit);
			$articleList = $db->loadObjectList();
			$count += count($articleList);
		}
		return array($result, $count, self::$_rowCount);
	}

	/**
	 * Checks the jos_content_keyword_article_map table to see if it matches the
	 * metakey column in the jos_content table. If there is a difference between these
	 * two values, the map table is rebuilt for that one article.
	 * keywords, author, or category.
	 * @return 	array()		element 'success' = true if method successful;
	 * 						element 'repaired' = count of articles repaired.
	 * 						element 'good' = count of articles checked correctly.
	 * 						element 'unmatched' = count of unmatched rows in map table deleted
	 */
	function repair() {
		$limit = 1000;
		$offset = 0;
		//set_time_limit(600);
		global $mainframe;
		$result = true; // set return value
		$goodArticleCount = 0;
		$fixedArticleCount = 0;
		$mapRowsRemoved = 0;
		$db	=& JFactory::getDBO();

		// check for unmatched rows in map table
		$unmatchedQuery = 'SELECT COUNT(m.keyword) as count FROM ' . 
			self::$_map_table . ' AS m' .
			' LEFT JOIN #__content AS a ' .
			' ON m.article_id = a.id ' . 
			' WHERE a.id IS NULL';
		$db->setQuery($unmatchedQuery);
		$mapRowsRemoved = $db->loadResult();
		if ($mapRowsRemoved > 0)
		{
			$deleteQuery = 'DELETE m  FROM ' . self::$_map_table . ' AS m' .
			' LEFT JOIN #__content AS a ' .
			' ON m.article_id = a.id ' . 
			' WHERE a.id IS NULL';
			$db->setQuery($deleteQuery);
			if (!$db->query())
			{
				$result = false;
			}
		}

		// Loop through the articles and check that the metakey is equal to map rows
		$query = 'SELECT id, metakey, catid, created_by, created_by_alias '.
				' FROM #__content ';
		$db->setQuery($query, $offset, $limit);
		$articleList = $db->loadObjectList();
		$count = count($articleList);
		// outer while loop -- process one chunk at a time
		while ($articleList)
		{
			foreach ($articleList as $article)
			{
				$articleKeys = self::_getArticleKeywords($db, $article->id);
				if (!self::_compareArticleKeywords($article, $articleKeys))
				{
					$fixedArticleCount += 1;
					if (!self::_deleteArticleRows($db, $article->id)) {
						$result = false;
					}
					if (!self::_insertArticleRows($db, $article)) {
						$result = false;
					}
				}
				else 
				{
					$goodArticleCount += 1;
				}
			}
			// get the next chunk
			$offset += $limit;
			$db->setQuery($query, $offset, $limit);
			$articleList = $db->loadObjectList();
		}
		return array('success' => $result, 'repaired' => $fixedArticleCount, 
			'good' => $goodArticleCount, 'unmatched' => $mapRowsRemoved);		

	}

	/**
	 * Utility method to insert keys for one article
	 * @param	JDatabase object		the current database
	 * @param	JTableContent object	the article for which to insert map rows
	 * @return	bool					true if successful
	 */
	protected function _insertArticleRows($db, $article) {
		$result = true; // assume success unless failure encountered below
		if ($article->metakey) // process keywords if present
		{
			$keyArray = explode(',', $article->metakey);
			$keysInserted = array();
			foreach ($keyArray as $thisKey)
			{
				$thisKey = trim($thisKey);
				if (!in_array(strtoupper($thisKey), $keysInserted))
				{
					if (!self::_insertRow($db, $thisKey, $article->id))
					{
						$result = false;
					}
					$keysInserted[] = strtoupper($thisKey);
				}
			}
		}
		// process author, alias, and category
		$authorTag = self::$_authorTag . $article->created_by;
		if (!self::_insertRow($db, $authorTag, $article->id))
		{
			$result = false;
		}
		$categoryTag = self::$_categoryTag . $article->catid;
		if (!self::_insertRow($db, $categoryTag, $article->id))
		{
			$result = false;
		}
		if ($article->created_by_alias)
		{
			$aliasTag = self::$_aliasTag . $article->created_by_alias;
			if (!self::_insertRow($db, $aliasTag, $article->id))
			{
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Utility method to insert rows into table
	 * @param $db - JDatabase object
	 * @param $keyword - keyword
	 * @param $id - article id
	 * @return - true if successfult
	 */
	protected function _insertRow($db, $keyword, $id) {
		$insertQuery = 'INSERT INTO ' . self::$_map_table .
			' VALUES (' . $db->Quote($keyword).','.$db->Quote($id).')';
		$db->setQuery($insertQuery);
		self::$_rowCount += 1;
		return $db->query();
	}
	
	/**
	 * Utility method to get the keywords in an array format
	 * @param	JDatabase object	current database
	 * @param	int					article id
	 * @return	string array		array of keywords from the map table
	 */
	protected function _getArticleKeywords($db, $id) {
		$keywords = array();
		$keywordQuery = 'SELECT keyword FROM ' . self::$_map_table . 
			' WHERE article_id = ' . (int) $id . 
			' ORDER BY keyword';
		$db->setQuery($keywordQuery);
		$mapRows = $db->loadObjectList();
		if ($mapRows) 
		{
			foreach ($mapRows as $row)
			{
				$keywords[] = strtolower(trim($row->keyword));
			}	
		}
		return sort($keywords);
	}
	
	/**
	 * Utility method to compare article metakey to keywords from map table
	 * @param 	JTableContent object 	current article object
	 * @param	string array			keyword array from map table
	 * @return	boolean					true if keywords are the same
	 */
	protected function _compareArticleKeywords($article, $mapKeys) {
		// put article keywords in an array
		$articleKeywords = array();
		if ($article->metakey)
		{
			$articleKeywords = explode(',', strtolower($article->metakey));
			$count = count($articleKeywords);
			for ($i = 0; $i < $count; $i++) {
				$articleKeywords[$i] = trim($articleKeywords[$i]);
			}
		}
		$articleKeywords[] = self::$_categoryTag . $article->catid;
		$articleKeywords[] = self::$_authorTag . $article->created_by;
		if ($article->created_by_alias) 
		{
			$articleKeywords[] = self::$_aliasTag . strtolower($article->created_by_alias);
		}
		sort($articleKeywords);
		return $articleKeywords == $mapKeys;
	}
	
	/**
	 * Utility method to delete map table rows for one article
	 * @param	JDatabase	current database object
	 * @param 	int			id of article to delete
	 * @return	bool		true if successful
	 */
	protected function _deleteArticleRows($db, $id) {
		$deleteQuery = 'DELETE FROM ' . self::$_map_table . 
		' WHERE article_id = ' . (int) $id;
		$db->setQuery($deleteQuery);
		return $db->query();
	}
}