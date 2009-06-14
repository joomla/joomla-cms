<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');
jimport('joomla.application.component.model');

/**
 * Content Component Article Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since		1.5
 */
class ContentModelElement extends JModel
{
	/**
	 * Content data in category array
	 *
	 * @var array
	 */
	var $_list = null;

	var $_page = null;

	/**
	 * Method to get content article data for the frontpage
	 *
	 * @since 1.5
	 */
	function getList()
	{
		global $mainframe;

		if (!empty($this->_list)) {
			return $this->_list;
		}

		// Initialize variables
		$db		= &$this->getDbo();
		$filter	= null;

		// Get some variables from the request
		$redirect			= $sectionid;
		$option				= JRequest::getCmd('option');
		$filter_order		= $mainframe->getUserStateFromRequest('articleelement.filter_order',		'filter_order',		'',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest('articleelement.filter_order_Dir',	'filter_order_Dir',	'',	'word');
		$catid				= $mainframe->getUserStateFromRequest('articleelement.catid',				'catid',			0,	'int');
		$filter_authorid	= $mainframe->getUserStateFromRequest('articleelement.filter_authorid',		'filter_authorid',	0,	'int');
		$limit				= $mainframe->getUserStateFromRequest('global.list.limit',					'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart			= $mainframe->getUserStateFromRequest('articleelement.limitstart',			'limitstart',		0,	'int');
		$search				= $mainframe->getUserStateFromRequest('articleelement.search',				'search',			'',	'string');
		$search				= JString::strtolower($search);

		//$where[] = "c.state >= 0";
		$where[] = "c.state != -2";

		$order = ' ORDER BY '. $filter_order .' '. $filter_order_Dir .', cc.name, c.ordering';
		$all = 1;

		/*
		 * Add the filter specific information to the where clause
		 */
		// Category filter
		if ($catid > 0) {
			$where[] = 'c.catid = '.(int) $catid;
		}
		// Author filter
		if ($filter_authorid > 0) {
			$where[] = 'c.created_by = '.(int) $filter_authorid;
		}

		// Only published articles
		$where[] = 'c.state = 1';

		// Keyword filter
		if ($search) {
			$where[] = 'LOWER(c.title) LIKE '.$db->Quote('%'.$db->getEscaped($search, true).'%', false);
		}

		// Build the where clause of the content record query
		$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

		// Get the total number of records
		$query = 'SELECT COUNT(*)' .
				' FROM #__content AS c' .
				' LEFT JOIN #__categories AS cc ON cc.id = c.catid' .
				$where;
		$db->setQuery($query);
		$total = $db->loadResult();

		// Create the pagination object
		jimport('joomla.html.pagination');
		$this->_page = new JPagination($total, $limitstart, $limit);

		// Get the articles
		$query = 'SELECT c.*, cc.title as cctitle, u.name AS editor, f.content_id AS frontpage, v.name AS author' .
				' FROM #__content AS c' .
				' LEFT JOIN #__categories AS cc ON cc.id = c.catid' .
				' LEFT JOIN #__users AS u ON u.id = c.checked_out' .
				' LEFT JOIN #__users AS v ON v.id = c.created_by' .
				' LEFT JOIN #__content_frontpage AS f ON f.content_id = c.id' .
				$where .
				$order;
		$db->setQuery($query, $this->_page->limitstart, $this->_page->limit);
		$this->_list = $db->loadObjectList();

		// If there is a db query error, throw a HTTP 500 and exit
		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
			return false;
		}

		return $this->_list;
	}

	function getPagination()
	{
		if (is_null($this->_list) || is_null($this->_page)) {
			$this->getList();
		}
		return $this->_page;
	}
}
?>
