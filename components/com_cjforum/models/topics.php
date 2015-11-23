<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelTopics extends JModelList
{
	protected $_item = null;
	
	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'alias', 'a.alias',
					'checked_out', 'a.checked_out',
					'checked_out_time', 'a.checked_out_time',
					'catid', 'a.catid',
					'category_title',
					'state', 'a.state',
					'access', 'a.access',
					'access_level',
					'created', 'a.created',
					'created_by', 'a.created_by',
					'ordering', 'a.ordering',
					'featured', 'a.featured',
					'language', 'a.language',
					'hits', 'a.hits',
					'likes', 'a.likes',
					'dislikes', 'a.dislikes',
					'replies', 'a.replies',
					'publish_up', 'a.publish_up',
					'publish_down', 'a.publish_down',
					'images', 'a.images',
					'urls', 'a.urls'
			);
		}
		
		parent::__construct($config);
	}

	protected function populateState ($ordering = 'ordering', $direction = 'ASC')
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();
		
		// List state information
		$value = $app->input->get('limit', $app->getCfg('list_limit', 20), 'uint');
		$this->setState('list.limit', $value);
		
		$repliesLimit = $params->get('replies_limit', 10);
		$this->setState('list.replies_limit', $repliesLimit);
		
		$limitStart = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $limitStart);
		
		$featured = $app->input->get('filter_featured', '', 'cmd');
		$this->setState('filter.featured', $featured);

		$unanswered = $app->input->get('filter_unanswered', 0, 'uint');
		$this->setState('filter.unanswered', $unanswered);

		$authorId = $app->input->get('filter_author_id', 0, 'uint');
		$this->setState('filter.author_id', $authorId);

		$favored = $app->input->get('filter_favored', 0, 'uint');
		$this->setState('filter.favored', $favored);
		
		$unread = $app->input->get('list_unread', 0, 'uint');
		$this->setState('list.unread', $unread);

		$categories = $app->input->getArray(array('catid'=>'array'));
		$this->setState('filter.category_id', $categories['catid']);

		$filter = $app->input->get('list_filter', '', 'string');
		if(strlen($filter) > 1)
		{
			$this->setState('list.filter', $filter);
				
			$filterAllKeywords = $app->input->get('list_filter_all_keywords', 0, 'int');
			$this->setState('list.filter_all_keywords', $filterAllKeywords);
				
			$filterField = $app->input->get('list_filter_field', 'title', 'word');
			$this->setState('list.filter_field', $filterField);
		}
		
		$orderCol = $app->input->get('filter_order', 'a.created');
		if (! in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.created';
		}
		
		$this->setState('list.ordering', $orderCol);
		
		$listOrder = $app->input->get('filter_order_Dir', 'DESC');
		if (! in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'DESC';
		}
		
		$this->setState('list.direction', $listOrder);
		
		$recent = $app->input->getBool('recent', false);
		$this->setState('list.recent', $recent);
		$this->setState('params', $params);
		$user = JFactory::getUser();
		
		if (! $user->authorise('core.edit.state', 'com_cjforum') && ! $user->authorise('core.edit', 'com_cjforum'))
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}
		
		$this->setState('filter.language', JLanguageMultilang::isEnabled());
		
		// Process show_noauth parameter
		if (! $params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}
		
		$this->setState('layout', $app->input->getString('layout'));
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.featured');
		$id .= ':' . $this->getState('filter.topic_id');
		$id .= ':' . $this->getState('filter.topic_id.include');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . $this->getState('filter.category_id.include');
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.author_id.include');
		$id .= ':' . serialize($this->getState('filter.author_alias'));
		$id .= ':' . $this->getState('filter.author_alias.include');
		$id .= ':' . $this->getState('filter.date_filtering');
		$id .= ':' . $this->getState('filter.date_field');
		$id .= ':' . $this->getState('filter.start_date_range');
		$id .= ':' . $this->getState('filter.end_date_range');
		$id .= ':' . $this->getState('filter.relative_date');
		
		return parent::getStoreId($id);
	}

	protected function getListQuery ()
	{
		// Get the current user for authorisation checks
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_cjforum');
		
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Select the required fields from the table.
		$query->select(
				$this->getState('list.select', 
						'a.id, a.title, a.alias, a.introtext, a.fulltext, a.checked_out, a.checked_out_time, a.locked, a.last_reply,' .
								 'a.catid, a.created, a.created_by, a.created_by_alias, a.replies, a.replied, a.replied_by, a.language,' . 
								// Use created if modified is 0
								'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
								'a.modified_by, uam.'.$params->get('display_name', 'name').' as modified_by_name,' . 
								// Use created if publish_up is 0
								'CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up,' .
								'a.publish_down, a.images, a.urls, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
								'a.hits, a.xreference, a.featured,' . ' ' . $query->length('a.fulltext') . ' AS readmore'));
		
		// Process an Archived Article layout
		if ($this->getState('filter.published') == 2)
		{
			// If badcats is not null, this means that the topic is inside an
			// archived category
			// In this case, the state is set to 2 to indicate Archived (even if
			// the topic state is Published)
			$query->select($this->getState('list.select', 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END AS state'));
		}
		else
		{
			/*
			 * Process non-archived layout If badcats is not null, this means
			 * that the topic is inside an unpublished category In this case,
			 * the state is set to 0 to indicate Unpublished (even if the topic
			 * state is Published)
			 */
			$query->select($this->getState('list.select', 'CASE WHEN badcats.id is not null THEN 0 ELSE a.state END AS state'));
		}
		
		// get last page number
		$repliesLimit = $this->getState('list.replies_limit', 10);
		if($repliesLimit > 0)
		{
			$query->select('(ceil(a.replies / '.$repliesLimit.') - 1) * '.$repliesLimit.' AS page_start');
		}
		else 
		{
			$query->select('floor(a.replies / 5) * 5 AS page_start');
		}
		
		$query->from('#__cjforum_topics AS a');
		
		// Join over the categories.
		$query
			->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');
		
		// Join over cjforum users.
		$query
			->select("CASE WHEN cju.handle is null OR trim(cju.handle) = '' THEN ua.username ELSE cju.handle END AS created_by_handle")
			->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.".$params->get('display_name', 'name')." END AS author")
			->join('LEFT', '#__cjforum_users AS cju ON cju.id = a.created_by');
		
		// Join over the users for the author and modified_by names.
		$query
			->select('ua.email AS author_email')
			->select('ur.'.$params->get('display_name', 'name').' AS reply_author, ur.email as reply_author_email')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
			->join('LEFT', '#__users AS uam ON uam.id = a.modified_by')
			->join('LEFT', '#__users AS ur ON ur.id = a.replied_by');
		
		
		// Join over the categories to get parent category titles
		$query
			->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
			->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');
		
		// Join to check for category published state in parent categories up the tree
		$query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
		$subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
		$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
		$subquery .= 'WHERE parent.extension = ' . $db->quote('com_cjforum');
		
		$unanswered = $this->getState('filter.unanswered');
		if($unanswered)
		{
			$query->where('a.replies = 0');
		}
		
		if ($this->getState('filter.published') == 2)
		{
			// Find any up-path categories that are archived
			// If any up-path categories are archived, include all children in
			// archived layout
			$subquery .= ' AND parent.published = 2 GROUP BY cat.id ';
			
			// Set effective state to archived if up-path category is archived
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END';
		}
		else
		{
			// Find any up-path categories that are not published
			// If all categories are published, badcats.id will be null, and we
			// just use the topic state
			$subquery .= ' AND parent.published != 1 GROUP BY cat.id ';
			
			// Select state to unpublished if up-path category is unpublished
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 0 END';
		}
		
		$query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');
		
		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')')->where('c.access IN (' . $groups . ')');
		}
		
		// Filter by published state
		$published = $this->getState('filter.published');
		
		if (is_numeric($published))
		{
			// Use topic state if badcats.id is null, otherwise, force 0 for unpublished
			$query->where($publishedWhere . ' = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);
			
			// Use topic state if badcats.id is null, otherwise, force 0 for
			// unpublished
			$query->where($publishedWhere . ' IN (' . $published . ')');
		}
		
		// Filter by featured state
		$featured = $this->getState('filter.featured');
		
		switch ($featured)
		{
			case 'hide':
				$query->where('a.featured = 0');
				break;
			
			case 'only':
				$query->where('a.featured = 1');
				break;
			
			case 'show':
			default:
				// Normally we do not discriminate
				// between featured/unfeatured items.
				break;
		}
		
		// Filter by a single or group of topics.
		$topicId = $this->getState('filter.topic_id');
		
		if (is_numeric($topicId))
		{
			$type = $this->getState('filter.topic_id.include', true) ? '= ' : '<> ';
			$query->where('a.id ' . $type . (int) $topicId);
		}
		elseif (is_array($topicId))
		{
			JArrayHelper::toInteger($topicId);
			$topicId = implode(',', $topicId);
			$type = $this->getState('filter.topic_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.id ' . $type . ' (' . $topicId . ')');
		}
		
		// Filter by a single or group of categories
		$categoryId = $this->getState('filter.category_id');
		
		if (is_numeric($categoryId))
		{
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';
			
			// Add subcategory check
			$includeSubcategories = $this->getState('filter.subcategories', false);
			$categoryEquals = 'a.catid ' . $type . (int) $categoryId;
			
			if ($includeSubcategories)
			{
				$levels = (int) $this->getState('filter.max_category_levels', '1');
				
				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true)
					->select('sub.id')
					->from('#__categories as sub')
					->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
					->where('this.id = ' . (int) $categoryId);
				
				if ($levels >= 0)
				{
					$subQuery->where('sub.level <= this.level + ' . $levels);
				}
				
				// Add the subquery to the main query
				$query->where('(' . $categoryEquals . ' OR a.catid IN (' . $subQuery->__toString() . '))');
			}
			else
			{
				$query->where($categoryEquals);
			}
		}
		elseif (is_array($categoryId) && (count($categoryId) > 0))
		{
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			
			if (! empty($categoryId))
			{
				$type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
				$query->where('a.catid ' . $type . ' (' . $categoryId . ')');
			}
		}
		
		// Filter by author
		$authorId = $this->getState('filter.author_id');
		$authorWhere = '';
		
		if (is_numeric($authorId) && $authorId)
		{
			$favored = $this->getState('filter.favored', 0);
			
			// do not set created_by restriction if it is request for author favorites
			if($favored)
			{
				$authorWhere = 'a.id in (select item_id from #__cjforum_favorites where user_id = '.$authorId.' and item_type = '.ITEM_TYPE_TOPIC.')';
			}
			else 
			{
				$type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
				$authorWhere = 'a.created_by ' . $type . (int) $authorId;
			}
		}
		elseif (is_array($authorId))
		{
			JArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);
			
			if ($authorId)
			{
				$type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
				$authorWhere = 'a.created_by ' . $type . ' (' . $authorId . ')';
			}
		}
		
		// Filter by author alias
		$authorAlias = $this->getState('filter.author_alias');
		$authorAliasWhere = '';
		
		if (is_string($authorAlias))
		{
			$type = $this->getState('filter.author_alias.include', true) ? '= ' : '<> ';
			$authorAliasWhere = 'a.created_by_alias ' . $type . $db->quote($authorAlias);
		}
		elseif (is_array($authorAlias))
		{
			$first = current($authorAlias);
			
			if (! empty($first))
			{
				JArrayHelper::toString($authorAlias);
				
				foreach ($authorAlias as $key => $alias)
				{
					$authorAlias[$key] = $db->quote($alias);
				}
				
				$authorAlias = implode(',', $authorAlias);
				
				if ($authorAlias)
				{
					$type = $this->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
					$authorAliasWhere = 'a.created_by_alias ' . $type . ' (' . $authorAlias . ')';
				}
			}
		}
		
		if (! empty($authorWhere) && ! empty($authorAliasWhere))
		{
			$query->where('(' . $authorWhere . ' OR ' . $authorAliasWhere . ')');
		}
		elseif (empty($authorWhere) && empty($authorAliasWhere))
		{
			// If both are empty we don't want to add to the query
		}
		else
		{
			// One of these is empty, the other is not so we just add both
			$query->where($authorWhere . $authorAliasWhere);
		}
		
		// Filter by start and end dates.
		if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
		{
			$nullDate = $db->quote($db->getNullDate());
			$nowDate = $db->quote(JFactory::getDate()->toSql());
			
			$query
				->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
				->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}
		
		// Filter by Date Range or Relative Date
		$dateFiltering = $this->getState('filter.date_filtering', 'off');
		$dateField = $this->getState('filter.date_field', 'a.created');
		
		switch ($dateFiltering)
		{
			case 'range':
				$startDateRange = $db->quote($this->getState('filter.start_date_range', $nullDate));
				$endDateRange = $db->quote($this->getState('filter.end_date_range', $nullDate));
				$query->where('(' . $dateField . ' >= ' . $startDateRange . ' AND ' . $dateField . ' <= ' . $endDateRange . ')');
				break;
			
			case 'relative':
				$relativeDate = (int) $this->getState('filter.relative_date', 0);
				$query->where($dateField . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' . $relativeDate . ' DAY)');
				break;
			
			case 'off':
			default:
				break;
		}
		
		// Process the filter for list views with user-entered filters
		$filter = $this->getState('list.filter');
		$filterField = $this->getState('list.filter_field');
		
		if (!empty($filter))
		{
			// Clean filter variable
			$filter = JString::strtolower($filter);
			$hitsFilter = (int) $filter;
			$createdByFilter = (int) $filter;
				
			switch ($filterField)
			{
				case 'createdby':
					$query->where('a.created_by = ' . $createdByFilter . ' ');
					break;
						
				case 'author':
					$query->where('LOWER( CASE WHEN a.created_by_alias > ' . $db->quote(' ') . 
						' THEN a.created_by_alias ELSE ua.'.$params->get('display_name', 'name').' END ) LIKE ' . $db->q('%' . $db->escape($filter, true) . '%', false) . ' ');
					break;
		
				case 'hits':
					$query->where('a.hits >= ' . $hitsFilter . ' ');
					break;
		
				case 'title':
				default: // Default to 'title' if parameter is not valid
					$stopwords = array(
					"a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always",
					"am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at",
					"back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between",
					"beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done",
					"down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything",
					"everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front",
					"full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself",
					"him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter",
					"latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must",
					"my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of",
					"off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps",
					"please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six",
					"sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their",
					"them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those",
					"though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up",
					"upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein",
					"whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet",
					"you", "your", "yours", "yourself", "yourselves", "the");
		
					$keywords = array_diff(explode('-', $filter), $stopwords);
					$filters = array();
		
					foreach ($keywords as $keyword)
					{
						if(strlen($keyword) > 2)
						{
							$filters[] = 'LOWER( a.title ) LIKE '.$db->q('%' . $db->escape($keyword, true) . '%', false);
						}
					}
					
					
					if(!empty($filters))
					{
						if($filterAllKeywords = $this->getState('list.filter_all_keywords'))
						{
							$query->where('('.implode(' AND ', $filters).')');
						}
						else
						{
							$query->where('('.implode(' OR ', $filters).')');
						}
					}
						
					break;
			}
		}
		
		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}
		
		$recent = $this->getState('list.recent', false);
		if($recent)
		{
			$query->order('a.replied DESC');
		}
		
		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.created') . ' ' . $this->getState('list.direction', 'DESC'));
		
// echo $query->dump();
		return $query;
	}

	public function getItems ()
	{
		$items = parent::getItems();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$userId = $user->get('id');
		$guest = $user->get('guest');
		$groups = $user->getAuthorisedViewLevels();
		$input = $app->input;
		
		// Get the global params
		$globalParams = JComponentHelper::getParams('com_cjforum', true);
		
		// Convert the parameter fields into objects.
		foreach ($items as &$item)
		{
			$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;
				
			// No link for ROOT category
			if ($item->parent_alias == 'root')
			{
				$item->parent_slug = null;
			}

			$item->catslug = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			
			$topicParams = new JRegistry();
			$topicParams->loadString($item->attribs);
			
			// Unpack readmore and layout params
			$item->alternative_readmore = $topicParams->get('alternative_readmore');
			$item->layout = $topicParams->get('layout');
			
			$item->params = clone $this->getState('params');
			
			/*
			 * For blogs, topic params override menu item params only if menu
			 * param = 'use_topic' Otherwise, menu item params control the
			 * layout If menu item is 'use_topic' and there is no topic param,
			 * use global
			 */
			if (($input->getString('layout') == 'blog') || ($input->getString('view') == 'featured') ||
					 ($this->getState('params')->get('layout_type') == 'blog'))
			{
				// Create an array of just the params set to 'use_topic'
				$menuParamsArray = $this->getState('params')->toArray();
				$topicArray = array();
				
				foreach ($menuParamsArray as $key => $value)
				{
					if ($value === 'use_topic')
					{
						// If the topic has a value, use it
						if ($topicParams->get($key) != '')
						{
							// Get the value from the topic
							$topicArray[$key] = $topicParams->get($key);
						}
						else
						{
							// Otherwise, use the global value
							$topicArray[$key] = $globalParams->get($key);
						}
					}
				}
				
				// Merge the selected topic params
				if (count($topicArray) > 0)
				{
					$topicParams = new JRegistry();
					$topicParams->loadArray($topicArray);
					$item->params->merge($topicParams);
				}
			}
			else
			{
				// For non-blog layouts, merge all of the topic params
				$item->params->merge($topicParams);
			}
			
			// Get display date
			switch ($item->params->get('list_show_date'))
			{
				case 'modified':
					$item->displayDate = $item->modified;
					break;
				
				case 'published':
					$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
					break;
				
				case 'created':
					$item->displayDate = $item->created;
					break;
			}
			
			// Compute the asset access permissions.
			// Technically guest could edit an topic, but lets not check that
			// to improve performance a little.
			if (! $guest)
			{
				$asset = 'com_cjforum.topic.' . $item->id;
				
				// Check general edit permission first.
				if ($user->authorise('core.edit', $asset))
				{
					$item->params->set('access-edit', true);
				}
				
				// Now check if edit.own is available.
				elseif (! empty($userId) && $user->authorise('core.edit.own', $asset))
				{
					// Check for a valid user and that they are the owner.
					if ($userId == $item->created_by)
					{
						$item->params->set('access-edit', true);
					}
				}
			}
			
			$access = $this->getState('filter.access');
			
			if ($access)
			{
				// If the access filter has been set, we already have only the
				// topics this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some
				// responsibility for display of limited information.
				if ($item->catid == 0 || $item->category_access === null)
				{
					$item->params->set('access-view', in_array($item->access, $groups));
				}
				else
				{
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}
			
			// Get the tags
// 			$item->tags = new JHelperTags();
// 			$item->tags->getItemTags('com_cjforum.topic', $item->id);

			$item->new_posts = 0;
		}
		
		reset($items);
		
		if(!$user->guest && !empty($items))
		{
			$newPostsData = $this->getRecentPosts();
			if(!empty($newPostsData))
			{
				foreach ($newPostsData as $topicId=>$newPost)
				{
					foreach ($items as &$item)
					{
						if($topicId == $item->id)
						{
							$item->new_posts = $newPost->new_posts;
						}
					}
					
					reset($items);
				}
			}
		}
			
		return $items;
	}
	
	public function getRecentPosts()
	{
		// get user profile.
		$user = JFactory::getUser();
		$profileApi = CjForumApi::getProfileApi();
		$profile = $profileApi->getUserProfile($user->id);

		if(!$profile['last_access_date'])
		{
			return false;
		}
		
		try
		{
			$app = JFactory::getApplication();
			$newPostsData = $app->getUserState('new_posts_data', null);
			
			if(empty($newPostsData))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.topic_id, count(*) as new_posts')
					->from('#__cjforum_replies AS a')
					->where('a.created > '.$db->q($profile['last_access_date']))
					->where('a.created_by != '.$user->id)
					->group('a.topic_id')
					->order('a.topic_id desc');
				
				$db->setQuery($query, 0, 100);
				$newPostsData = $db->loadObjectList('topic_id');
				$app->setUserState('new_posts_data', $newPostsData);
				$date = JFactory::getDate()->toSql();
				
				$query = $db->getQuery(true)
					->update('#__cjforum_users')
					->set('last_access_date = current_access_date')
					->set('current_access_date = '.$db->q($date))
					->where('id = '.$user->id);

				$db->setQuery($query);
				$db->execute();
			}
			
			return $newPostsData;
		}
		catch (Exception $e)
		{
			///
		}
		
		return false;
	}
	
	public function getPagination()
	{
		$page = parent::getPagination();
		$view = JFactory::getApplication()->input->getCmd('view');
		
		if($view == 'topics')
		{
			$page->setAdditionalUrlParam('view', 'topics');
			
			if($this->state->get('list.ordering') == 'hits')
			{
				$page->setAdditionalUrlParam('filter_order', $this->state->get('list.ordering'));
				$page->setAdditionalUrlParam('filter_order_Dir', $this->state->get('list.direction'));
			}
			
			$featured = $this->state->get('filter.featured');
			if(!empty($featured))
			{
				$page->setAdditionalUrlParam('filter_featured', $this->state->get('filter.featured'));
			}
			
			if($this->state->get('filter.unanswered', 0) == 1)
			{
				$page->setAdditionalUrlParam('filter_unanswered', 1);
			}
		}
		
		return $page;
	}

	public function getCategory ()
	{
		if (! is_object($this->_item))
		{
			if (isset($this->state->params))
			{
				$params = $this->state->params;
				$options = array();
				$options['countItems'] = $params->get('show_cat_num_topics', 1) || ! $params->get('show_empty_categories_cat', 0);
			}
			else
			{
				$options['countItems'] = 0;
			}
			
			$categories = JCategories::getInstance('CjForum', $options);
			$this->_item = $categories->get($this->getState('filter.category_id', 'root'));
			
			// Compute selected asset permissions.
			if (is_object($this->_item))
			{
				$user = JFactory::getUser();
				$asset = 'com_cjforum.category.' . $this->_item->id;
				
				// Check general create permission.
				if ($user->authorise('core.create', $asset))
				{
					$this->_item->getParams()->set('access-create', true);
				}
				
				// TODO: Why aren't we lazy loading the children and siblings?
				$this->_children = $this->_item->getChildren();
				$this->_parent = false;
				
				if ($this->_item->getParent())
				{
					$this->_parent = $this->_item->getParent();
				}
				
				$this->_rightsibling = $this->_item->getSibling();
				$this->_leftsibling = $this->_item->getSibling(false);
			}
			else
			{
				$this->_children = false;
				$this->_parent = false;
			}
		}
		
		return $this->_item;
	}
}