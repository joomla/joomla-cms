<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.helper');
jimport( 'joomla.application.component.model');

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

	/**
	 * Total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Filter object
	 *
	 * @var object
	 */
	var $_filter = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		// Get the pagination request variables
		$limit				= $mainframe->getUserStateFromRequest('global.list.limit',					'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart			= $mainframe->getUserStateFromRequest('articleelement.limitstart',			'limitstart',		0,	'int');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$filter = new stdClass();
		$filter->order		= $mainframe->getUserStateFromRequest('articleelement.filter_order',		'filter_order',		'section_name',	'cmd');
		$filter->order_Dir	= $mainframe->getUserStateFromRequest('articleelement.filter_order_Dir',	'filter_order_Dir',	'',	'word');
		$filter->catid		= $mainframe->getUserStateFromRequest('articleelement.filter_catid',		'filter_catid',		0,	'int');
		$filter->authorid	= $mainframe->getUserStateFromRequest('articleelement.filter_authorid',		'filter_authorid',	0,	'int');
		$filter->sectionid	= $mainframe->getUserStateFromRequest('articleelement.filter_sectionid',	'filter_sectionid',	-1,	'int');
		$filter->search		= $mainframe->getUserStateFromRequest('articleelement.search',				'search',			'',	'string');
		$this->_filter = $filter;
	}

	/**
	 * Method to get content article data for the frontpage
	 *
	 * @since 1.5
	 */
	function getList()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_list))
		{
			$query = $this->_buildQuery();
			$this->_list = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_list;
	}

	/**
	 * Method to get the total number of articles
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to get filter object for the articles
	 *
	 * @access public
	 * @return object
	 */
	function getFilter()
	{
		return $this->_filter;
	}

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = 'SELECT c.*, g.name AS groupname, cc.title as cctitle, u.name AS editor, f.content_id AS frontpage, s.title AS section_name, v.name AS author'
			. ' FROM #__content AS c'
			. ' LEFT JOIN #__categories AS cc ON cc.id = c.catid'
			. ' LEFT JOIN #__sections AS s ON s.id = c.sectionid'
			. ' LEFT JOIN #__core_acl_axo_groups AS g ON g.value = c.access'
			. ' LEFT JOIN #__users AS u ON u.id = c.checked_out'
			. ' LEFT JOIN #__users AS v ON v.id = c.created_by'
			. ' LEFT JOIN #__content_frontpage AS f ON f.content_id = c.id'
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$orderby 	= ' ORDER BY '.$this->_filter->order.' '.$this->_filter->order_Dir;
		return $orderby;
	}

	function _buildContentWhere()
	{
		$search				= JString::strtolower( $this->_filter->search );

		// Only published articles
		$where[] = 'c.state = 1';

		/*
		 * Add the filter specific information to the where clause
		 */
		// Section filter
		if ($this->_filter->sectionid >= 0) {
			$where[] = 'c.sectionid = '.(int) $this->_filter->sectionid;
		}
		// Category filter
		if ($this->_filter->catid > 0) {
			$where[] = 'c.catid = '.(int) $this->_filter->catid;
		}
		// Author filter
		/*
		 * Not currently used
		if ($this->_filter->authorid > 0) {
			$where[] = 'c.created_by = '.(int) $this->_filter->authorid;
		}
		*/

		// Keyword filter
		if ($search) {
			$where[] = 'LOWER( c.title ) LIKE '.$this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );
		}

		// Build the where clause of the content record query
		$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

		return $where;
	}
}
?>
