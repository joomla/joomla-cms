<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Modules Component Module Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.5
 */
class ModulesModelModules extends JModel
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
	 * Client object
	 *
	 * @var object
	 */
	var $_client = null;

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

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$filter = new stdClass();
		$filter->order		= $mainframe->getUserStateFromRequest($option.'filter_order',		'filter_order',		'm.position',	'cmd');
		$filter->order_Dir	= $mainframe->getUserStateFromRequest($option.'filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$filter->state		= $mainframe->getUserStateFromRequest($option.'filter_state',		'filter_state',		'',				'word');
		$filter->position	= $mainframe->getUserStateFromRequest($option.'filter_position',	'filter_position',	'',				'cmd');
		$filter->type		= $mainframe->getUserStateFromRequest($option.'filter_type',		'filter_type',		'',				'cmd');
		$filter->assigned	= $mainframe->getUserStateFromRequest($option.'filter_assigned',	'filter_assigned',	'',				'cmd');
		$filter->search		= $mainframe->getUserStateFromRequest($option.'search',			'search',			'',				'string');
		$this->_filter = $filter;

		$this->_client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', 0, '', 'int'));
	}

	/**
	 * Method to get Modules item data
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
	 * Method to get the total number of Module items
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
	 * Method to get a pagination object for the Modules
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
	 * Method to get filter object for the Modules
	 *
	 * @access public
	 * @return object
	 */
	function getFilter()
	{
		return $this->_filter;
	}

	/**
	 * Method to get the client object
	 *
	 * @access public
	 * @return object
	 */
	function getClient()
	{
		return $this->_client;
	}

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$join = '';
		if ($this->_filter->assigned) {
			$join = ' LEFT JOIN #__templates_menu AS t ON t.menuid = mm.menuid';
		}

		$query = 'SELECT m.*, u.name AS editor, g.title AS groupname, MIN(mm.menuid) AS pages'
			. ' FROM #__modules AS m'
			. ' LEFT JOIN #__users AS u ON u.id = m.checked_out'
			. ' LEFT JOIN #__access_assetgroups AS g ON g.id = m.access'
			. ' LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id'
			. $join
			. $where
			. ' GROUP BY m.id'
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$orderby 	= ' ORDER BY '. $this->_filter->order .' '. $this->_filter->order_Dir .', m.ordering ASC';
		return $orderby;
	}

	function _buildContentWhere()
	{
		$search				= JString::strtolower($this->_filter->search);

		$where = array();
		$where[] = 'm.client_id = '.(int) $this->_client->id;

		if ($this->_filter->assigned) {
			$joins[] = 'LEFT JOIN #__templates_menu AS t ON t.menuid = mm.menuid';
			$where[] = 't.template = '.$this->_db->Quote($this->_filter->assigned);
		}
		if ($this->_filter->position) {
			$where[] = 'm.position = '.$this->_db->Quote($this->_filter->position);
		}
		if ($this->_filter->type) {
			$where[] = 'm.module = '.$this->_db->Quote($this->_filter->type);
		}
		if ($search) {
			$where[] = 'LOWER(m.title) LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%', false);
		}
		if ($this->_filter->state) {
			if ($this->_filter->state == 'P') {
				$where[] = 'm.published = 1';
			} else if ($this->_filter->state == 'U') {
				$where[] = 'm.published = 0';
			}
		}

		$where 		= (count($where) ? ' WHERE '. implode(' AND ', $where) : '');

		return $where;
	}
}
