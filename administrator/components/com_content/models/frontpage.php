<?php
/**
 * @version		$Id $
 * @package		Joomla.Administrator
 * @subpackage	Frontpage
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Frontpage Component Frontpage Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.6
 */
class ContentModelFrontpage extends JModel
{
	/**
	 * Category ata array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Category total
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
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart	= $mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$filter = new stdClass();
		$filter->order		= $mainframe->getUserStateFromRequest($option.'filter_order',		'filter_order',		'fpordering',	'cmd');
		$filter->order_Dir	= $mainframe->getUserStateFromRequest($option.'filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$filter->state		= $mainframe->getUserStateFromRequest($option.'filter_state',		'filter_state',		'',				'word');
		$filter->catid		= $mainframe->getUserStateFromRequest($option.'filter_catid',		'filter_catid',		0,				'int');
		$filter->search		= $mainframe->getUserStateFromRequest($option.'search',			'search',			'',				'string');
		$filter->authorid	= $mainframe->getUserStateFromRequest($option.'filter_authorid',	'filter_authorid',	0,				'int');
		$filter->sectionid	= $mainframe->getUserStateFromRequest($option.'filter_sectionid',	'filter_sectionid',	-1,				'int');
		$this->_filter = $filter;
	}

	/**
	 * Method to get Frontpages item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of Frontpage items
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

	/**
	 * Method to get a pagination object for the Frontpages
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method to get filter object for the Frontpages
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

		$query = 'SELECT c.*, g.name AS groupname, cc.title as name, s.title AS sect_name, u.name AS editor, f.ordering AS fpordering, v.name AS author'
			. ' FROM #__content AS c'
			. ' LEFT JOIN #__categories AS cc ON cc.id = c.catid'
			. ' LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope="content"'
			. ' INNER JOIN #__content_frontpage AS f ON f.content_id = c.id'
			. ' INNER JOIN #__core_acl_axo_groups AS g ON g.value = c.access'
			. ' LEFT JOIN #__users AS u ON u.id = c.checked_out'
			. ' LEFT JOIN #__users AS v ON v.id = c.created_by'
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$orderby 	= ' ORDER BY '. $this->_filter->order .' '. $this->_filter->order_Dir .', fpordering';
		return $orderby;
	}

	function _buildContentWhere()
	{
		$search				= JString::strtolower($this->_filter->search);

		$where = array();
		$where[] = "c.state >= 0";

		if ($this->_filter->catid > 0) {
			$where[] = 'c.catid = '.(int) $this->_filter->catid;
		}
		if ($this->_filter->sectionid >= 0) {
			$where[] = 'c.sectionid = '.(int) $this->_filter->sectionid;
		}
		if ($this->_filter->authorid > 0) {
			$where[] = 'c.created_by = '. (int) $this->_filter->authorid;
		}
		if ($this->_filter->state) {
			if ($this->_filter->state == 'P') {
				$where[] = 'c.state = 1';
			} else if ($this->_filter->state == 'U') {
				$where[] = 'c.state = 0';
			}
		}
		if ($search) {
			$where[] = 'LOWER(c.title) LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%', false);
		}

		$where 		= (count($where) ? ' WHERE '. implode(' AND ', $where) : '');

		return $where;
	}

	/**
	 * Method to move a frontpage article
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function move($direction)
	{
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		$row = & JTable::getInstance('frontpage', 'Table');
		if (!$row->load((int) $cid[0])) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move($direction, ' 1 ')) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to save article order
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function saveorder($cid = array(), $order)
	{
		$row = & JTable::getInstance('frontpage', 'Table');

		// update ordering values
		for($i=0; $i < count($cid); $i++)
		{
			$row->load((int) $cid[$i]);

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		// execute updateOrder
		$row->reorder(' 1 ');

		return true;
	}

	/**
	 * Method to remove articles
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);
			$query = 'DELETE FROM #__content_frontpage'
				. ' WHERE content_id IN ('.$cids.')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	* Function to toggle frontpage flag
	*
	*/
	function toggle($cid)
	{
		/*
		 * We need to update frontpage status for the articles.
		 *
		 * First we include the frontpage table and instantiate an instance of
		 * it.
		 */
		$fp = & JTable::getInstance('frontpage', 'Table');

		foreach ($cid as $id)
		{
			// toggles go to first place
			if ($fp->load($id)) {
				if (!$fp->delete($id)) {
					$msg .= $fp->stderr();
				}
				$fp->ordering = 0;
			} else {
				// new entry
				$query = 'INSERT INTO #__content_frontpage' .
						' VALUES ('. (int) $id .', 0)';
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {
					JError::raiseError(500, $this->_db->stderr());
					return false;
				}
				$fp->ordering = 0;
			}
			$fp->reorder();
		}
		return true;
	}
}