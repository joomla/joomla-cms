<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Weblinks Component Weblink Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.5
 */
class WeblinksModelWeblinks extends JModel
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

		$app = &JFactory::getApplication();

		// Get the pagination request variables
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_weblinks.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$filter = new stdClass();
		$filter->order		= $app->getUserStateFromRequest('com_weblinks.filter_order',		'filter_order',		'a.ordering',	'cmd');
		$filter->order_Dir	= $app->getUserStateFromRequest('com_weblinks.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$filter->state		= $app->getUserStateFromRequest('com_weblinks.filter_state',		'filter_state',		'',				'word');
		$filter->catid		= $app->getUserStateFromRequest('com_weblinks.filter_catid',		'filter_catid',		0,				'int');
		$filter->search		= $app->getUserStateFromRequest('com_weblinks.search',			'search',			'',				'string');
		$this->_filter		= $filter;
	}

	/**
	 * Method to get weblinks item data
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
	 * Method to get the total number of weblink items
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
	 * Method to get a pagination object for the weblinks
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
	 * Method to get filter object for the weblinks
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

		$query = ' SELECT a.*, cc.title AS category, u.name AS editor '
			. ' FROM #__weblinks AS a '
			. ' LEFT JOIN #__categories AS cc ON cc.id = a.catid '
			. ' LEFT JOIN #__users AS u ON u.id = a.checked_out '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		if ($this->_filter->order == 'a.ordering'){
			$orderby 	= ' ORDER BY category, a.ordering '.$this->_filter->order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$this->_filter->order.' '.$this->_filter->order_Dir.' , category, a.ordering ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$search				= JString::strtolower($this->_filter->search);

		$where = array();

		if ($this->_filter->catid > 0) {
			$where[] = 'a.catid = '.(int) $this->_filter->catid;
		}
		if ($search) {
			$where[] = 'LOWER(a.title) LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%', false);
		}
		if ($this->_filter->state) {
			if ($this->_filter->state == 'P') {
				$where[] = 'a.state = 1';
			} else if ($this->_filter->state == 'U') {
				$where[] = 'a.state = 0';
			} else if ($this->_filter->state == 'R') {
				$where[] = 'a.state = -1';
			}
		}

		$where 		= (count($where) ? ' WHERE '. implode(' AND ', $where) : '');

		return $where;
	}
}
