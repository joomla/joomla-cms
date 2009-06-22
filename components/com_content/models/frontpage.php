<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Frontpage Component Model
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class ContentModelFrontpage extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_content.articles';


	/**
	 * Overridden method to lazy load data from the request/session as necessary
	 *
	 * @access	public
	 * @param	string	$key		The key of the state item to return
	 * @param	mixed	$default	The default value to return if it does not exist
	 * @return	mixed	The requested value by key
	 * @since	1.0
	 */
	function _populateState()
	{
		$app = &JFactory::getApplication();

		// List state information
		$limit 		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest($this->_context.'.limitstart', 'limitstart', 0);
		$this->setState('list.limitstart', $limitstart);

		$orderCol	= $app->getUserStateFromRequest($this->_context.'.ordercol', 'filter_order', 'a.title');
		$this->setState('list.ordering', $orderCol);

		$orderDirn	= $app->getUserStateFromRequest($this->_context.'.orderdirn', 'filter_order_Dir', 'asc');
		$this->setState('list.direction', $orderDirn);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.frontpage', true);
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
	public function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.frontpage');

		return md5($id);
	}

	/**
	 * @param	boolean	True to join selected foreign information
	 *
	 * @return	string
	 */
	function _getListQuery($resolveFKs = true)
	{
/*
		$query = ' SELECT a.id, a.title, a.alias, a.title_alias, a.introtext, a.fulltext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,' .
			' a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.attribs, a.urls, a.metakey, a.metadesc, a.access,' .
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug,'.
			' CHAR_LENGTH(a.`fulltext`) AS readmore,' .
			' u.name AS author, u.usertype, u.email as author_email, cc.title AS category, s.title AS section, s.ordering AS s_ordering, cc.ordering AS cc_ordering, a.ordering AS a_ordering, f.ordering AS f_ordering'.
			$voting['select'] .
			' FROM #__content AS a' .
			' INNER JOIN #__content_frontpage AS f ON f.content_id = a.id' .
			' LEFT JOIN #__categories AS cc ON cc.id = a.catid'.
			' LEFT JOIN #__sections AS s ON s.id = a.sectionid'.
			' LEFT JOIN #__users AS u ON u.id = a.created_by' .
			$voting['join'].
			$where
			.$orderby
			;

		global $mainframe;
		// Get the page/component configuration
		$params = &$mainframe->getParams();
		if (!is_object($params)) {
			$params = &JComponentHelper::getParams('com_content');
		}

		$orderby_sec	= $params->def('orderby_sec', '');
		$orderby_pri	= $params->def('orderby_pri', '');
		$secondary		= ContentHelperQuery::orderbySecondary($orderby_sec);
		$primary		= ContentHelperQuery::orderbyPrimary($orderby_pri);

		$orderby = ' ORDER BY '.$primary.' '.$secondary;

		return $orderby;
*/
		// Create a new query object.
		$query = new JQuery;

		// Select the required fields from the table.
		$query->select($this->getState(
			'list.select',
			'a.id, a.title, a.alias, a.title_alias, a.introtext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' .
			' a.modified, a.modified_by,a.publish_up, a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,' .
			' LENGTH(a.fulltext) AS readmore'
		));
		$query->from('#__content AS a');

		// Join over the categories.
		$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$user	= &JFactory::getUser();
			$groups	= implode(',', $user->authorisedLevels());
			$query->where('a.access IN ('.$groups.')');
			$query->where('(c.access IS NULL OR c.access IN ('.$groups.'))');
		}

		// Filter by published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		}
		else if (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$query->where('a.state IN ('.$published.')');
		}

		// Filter by frontpage.
		if ($this->getState('filter.frontpage')) {
			$query->join('INNER', '#__content_frontpage AS fp ON fp.content_id = a.id');
		}

		// Filter by start and end dates.
		$nullDate	= $this->_db->Quote($this->_db->getNullDate());
		$nowDate	= $this->_db->Quote(JFactory::getDate()->toMySQL());

		$query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
		$query->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.title')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}

	/**
	 * Method to get a list of articles.
	 *
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function &getItems()
	{
		$items	= &parent::getItems();
		$user	= &JFactory::getUser();
		$groups	= $user->authorisedLevels();

		// Contvert the parameter fields into objects.
		foreach ($items as &$item)
		{
			$registry = new JRegistry;
			$registry->loadJSON($item->attribs);
			$item->params = clone $this->getState('params');
			$item->params->merge($registry);

			// TODO: Embed the access controls in here
			$item->params->set('access-edit', false);

			$access = $this->getState('filter.access');
			if ($access = $this->getState('filter.access'))
			{
				// If the access filter has been set, we already have only the articles this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ($item->catid == 0 || $item->category_access === null) {
					$item->params->set('access-view', in_array($item->access, $groups));
				}
				else {
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}
		}

		return $items;
	}
}
