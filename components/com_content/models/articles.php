<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * This models supports retrieving lists of articles.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class ContentModelArticles extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// List state information
		//$value = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$value = JRequest::getInt('limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		//$value = $app->getUserStateFromRequest($this->context.'.limitstart', 'limitstart', 0);
		$value = JRequest::getInt('limitstart', 0);
		$this->setState('list.start', $value);

		//$value = $app->getUserStateFromRequest($this->context.'.ordercol', 'filter_order', 'a.lft');
		$value = JRequest::getCmd('filter_order', 'a.ordering');
		$this->setState('list.ordering', $value);

		//$value = $app->getUserStateFromRequest($this->context.'.orderdirn', 'filter_order_Dir', 'asc');
		$value = JRequest::getWord('filter_order_Dir', 'asc');
		$this->setState('list.direction', $value);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published', 1);

		$this->setState('filter.language',$app->getLanguageFilter());

		// process show_noauth parameter
		if (!$params->get('show_noauth')) {
			$this->setState('filter.access', true);
		} else {
			$this->setState('filter.access', false);
		}
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
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':'.$this->getState('filter.published');
		$id .= ':'.$this->getState('filter.access');
		$id .= ':'.$this->getState('filter.featured');
		$id .= ':'.$this->getState('filter.article_id');
		$id .= ':'.$this->getState('filter.article_id.include');
		$id .= ':'.$this->getState('filter.category_id');
		$id .= ':'.$this->getState('filter.category_id.include');
		$id .= ':'.$this->getState('filter.author_id');
		$id .= ':'.$this->getState('filter.author_id.include');
		$id .= ':'.$this->getState('filter.author_alias');
		$id .= ':'.$this->getState('filter.author_alias.include');
		$id .= ':'.$this->getState('filter.date_filtering');
		$id .= ':'.$this->getState('filter.date_field');
		$id .= ':'.$this->getState('filter.start_date_range');
		$id .= ':'.$this->getState('filter.end_date_range');
		$id .= ':'.$this->getState('filter.relative_date');

		return parent::getStoreId($id);
	}

	/**
	 * @param	boolean	True to join selected foreign information
	 *
	 * @return	string
	 * @since	1.6
	 */
	function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.title_alias, a.introtext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' .
				// use created if modified is 0
				'CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END as modified,' .
					'a.modified_by, uam.name as modified_by_name,' .
				// use created if publish_up is 0
				'CASE WHEN a.publish_up = 0 THEN a.created ELSE a.publish_up END as publish_up,' .
					'a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,'.
					'a.hits, a.featured,'.' LENGTH(a.fulltext) AS readmore'
			)
		);
		$query->from('#__content AS a');

		// Join over the categories.
		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author");

		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		$query->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		// Join over the categories to get parent category titles
		$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
		$query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$user	= JFactory::getUser();
			$groups	= implode(',', $user->authorisedLevels());
			$query->where('a.access IN ('.$groups.')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			$query->where('a.state = '.(int) $published);
		} else if (is_array($published)) {
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);
			$query->where('a.state IN ('.$published.')');
		}

		// Filter by featured state
		$featured = $this->getState('filter.featured');
		switch ($featured) {
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

		// Filter by a single or group of articles.
		$articleId = $this->getState('filter.article_id');

		if (is_numeric($articleId)) {
			$type = $this->getState('filter.article_id.include', true) ? '= ' : '<> ';
			$query->where('a.id '.$type.(int) $articleId);
		} else if (is_array($articleId)) {
			JArrayHelper::toInteger($articleId);
			$articleId = implode(',', $articleId);
			$type = $this->getState('filter.article_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.id '.$type.' ('.$articleId.')');
		}

		// Filter by a single or group of categories
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId)) {
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

			// Add subcategory check
			$includeSubcategories = $this->getState('filter.subcategories', false);
			$categoryEquals = 'a.catid '.$type.(int) $categoryId;

			if ($includeSubcategories) {
				$levels = (int) $this->getState('filter.max_category_levels', '1');
				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true);
				$subQuery->select('sub.id');
				$subQuery->from('#__categories as sub');
				$subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
				$subQuery->where('this.id = '.(int) $categoryId);
				$subQuery->where('sub.level <= this.level + '.$levels);

				// Add the subquery to the main query
				$query->where('('.$categoryEquals.' OR a.catid IN ('.$subQuery->__toString().'))');
			} else {
				$query->where($categoryEquals);
			}
		} else if (is_array($categoryId)) {
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.catid '.$type.' ('.$categoryId.')');
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');
		$authorWhere = '';

		if (is_numeric($authorId)) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
			$authorWhere = 'a.created_by '.$type.(int) $authorId;
		} else if (is_array($authorId)) {
			JArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);

			if ($authorId) {
				$type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
				$authorWhere = 'a.created_by '.$type.' ('.$authorId.')';
			}
		}

		// Filter by author alias
		$authorAlias = $this->getState('filter.author_alias');
		$authorAliasWhere = '';

		if (is_string($authorAlias)) {
			$type = $this->getState('filter.author_alias.include', true) ? '= ' : '<> ';
			$authorAliasWhere = 'a.created_by_alias '.$type.$db->Quote($authorAlias);
		} else if (is_array($authorAlias)) {
			$first = current($authorAlias);

			if (!empty($first)) {
				JArrayHelper::toString($authorAlias);

				foreach ($authorAlias as $key => $alias) {
					$authorAlias[$key] = $db->Quote($alias);
				}

				$authorAlias = implode(',', $authorAlias);

				if ($authorAlias) {
					$type = $this->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
					$authorAliasWhere = 'a.created_by_alias '.$type.' ('.$authorAlias .
						')';
				}
			}
		}

		if (!empty($authorWhere) && !empty($authorAliasWhere)) {
			$query->where('('.$authorWhere.' OR '.$authorAliasWhere.')');
		} else if (empty($authorWhere) && empty($authorAliasWhere)) {
			// If both are empty we don't want to add to the query
		} else {
			// One of these is empty, the other is not so we just add both
			$query->where($authorWhere.$authorAliasWhere);
		}

		// Filter by start and end dates.
		$nullDate = $db->Quote($db->getNullDate());
		$nowDate = $db->Quote(JFactory::getDate()->toMySQL());

		$query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
		$query->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');

		// Filter by Date Range or Relative Date
		$dateFiltering = $this->getState('filter.date_filtering', 'off');
		$dateField = $this->getState('filter.date_field', 'a.created');

		switch ($dateFiltering) {
			case 'range':
				$startDateRange = $db->Quote($this->getState('filter.start_date_range', $nullDate));
				$endDateRange = $db->Quote($this->getState('filter.end_date_range', $nullDate));
				$query->where('('.$dateField.' >= '.$startDateRange.' AND '.$dateField .
					' <= '.$endDateRange.')');
				break;

			case 'relative':
				$relativeDate = (int) $this->getState('filter.relative_date', 0);
				$query->where($dateField.' >= DATE_SUB('.$nowDate.', INTERVAL ' .
					$relativeDate.' DAY)');
				break;

			case 'off':
			default:
				break;
		}

		// process the filter for list views with user-entered filters
		$params = $this->getState('params');

		if ((is_object($params)) && ($params->get('filter_field') != 'hide') && ($filter = $this->getState('list.filter'))) {
			// clean filter variable
			$filter = JString::strtolower($filter);
			$hitsFilter = intval($filter);
			$filter = $db->Quote('%'.$db->getEscaped($filter, true).'%', false);

			switch ($params->get('filter_field'))
			{
			case 'author':
				$query->where(
					'LOWER( CASE WHEN a.created_by_alias > '.$db->quote(' ').
					' THEN a.created_by_alias ELSE ua.name END ) LIKE '.$filter.' '
				);
				break;

			case 'hits':
				$query->where('a.hits >= '.$hitsFilter.' ');
				break;

			case 'title':
			default: // default to 'title' if parameter is not valid
				$query->where('LOWER( a.title ) LIKE '.$filter);
				break;
			}
		}

		// Filter by language
		if ($this->getState('filter.language')) {
			$query->where('a.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
		}

		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.ordering').' '.$this->getState('list.direction', 'ASC'));

		return $query;
	}

	/**
	 * Method to get a list of articles.
	 *
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 * @since	1.6
	 */
	public function &getItems()
	{
		$items	= &parent::getItems();
		$user	= JFactory::getUser();
		$groups	= $user->authorisedLevels();

		// Get the global params
		$globalParams = JComponentHelper::getParams('com_content', true);

		// Convert the parameter fields into objects.
		foreach ($items as &$item) {
			$articleParams = new JRegistry;
			$articleParams->loadJSON($item->attribs);

			// Unpack readmore and layout params
			$item->alternative_readmore = $articleParams->get('alternative_readmore');
			$item->layout = $articleParams->get('layout');

			$item->params = clone $this->getState('params');

			// For blogs, article params override menu item params only if menu param = 'use_article'
			// Otherwise, menu item params control the layout
			// If menu item is 'use_article' and there is no article param, use global
			if (JRequest::getString('layout') == 'blog' || JRequest::getString('view') == 'featured') {
				// create an array of just the params set to 'use_article'
				$menuParamsArray = $this->getState('params')->toArray();
				$articleArray = array();

				foreach ($menuParamsArray as $key => $value) {
					if ($value === 'use_article') {
						// if the article has a value, use it
						if ($articleParams->get($key) != '') {
							// get the value from the article
							$articleArray[$key] = $articleParams->get($key);
						} else {
							// otherwise, use the global value
							$articleArray[$key] = $globalParams->get($key);
						}
					}
				}

				// merge the selected article params
				if (count($articleArray) > 0) {
					$articleParams = new JRegistry;
					$articleParams->loadArray($articleArray);
					$item->params->merge($articleParams);
				}
			} else {
				// For non-blog layouts, merge all of the article params
				$item->params->merge($articleParams);
			}

			// get display date
			switch ($item->params->get('show_date')) {
				case 'modified':
					$item->displayDate = $item->modified;
					break;

				case 'published':
					$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
					break;

				default:
				case 'created':
					$item->displayDate = $item->created;
					break;
			}

			// TODO: Embed the access controls in here
			$item->params->set('access-edit', false);

			$access = $this->getState('filter.access');

			if ($access) {
				// If the access filter has been set, we already have only the articles this user can view.
				$item->params->set('access-view', true);
			} else {
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ($item->catid == 0 || $item->category_access === null) {
					$item->params->set('access-view', in_array($item->access, $groups));
				} else {
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}
		}

		return $items;
	}
}
