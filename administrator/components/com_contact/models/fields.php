<?php
/**
 * @version		$Id: fields.php 11476 2009-01-25 06:58:51Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Contact Component Fields Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @since		1.6
 */
class ContactModelFields extends JModel
{
	/**
	 * Category data array
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
	 * Constructor
	 *
	 * @since 1.5
	 */
	protected function __construct()
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
	}

	/**
	 * Method to get fields item data
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
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
	 * Method to get the total number of fields items
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
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
	 * Method to get a pagination object for the fields
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	public function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT f.*, u.name AS editor, g.name AS groupname '
			. ' FROM #__contact_fields AS f '
			. ' LEFT JOIN #__users AS u ON u.id = f.checked_out '
			. ' LEFT JOIN #__core_acl_axo_groups AS g ON g.value = f.access '
			. $where
			. $orderby;

		return $query;
	}

	public function _buildContentOrderBy()
	{
		global $mainframe, $option;

		$filter_order = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', 'f.ordering', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir',	'',				'word');

		if ($filter_order == 'f.ordering'){
			$orderby 	= ' ORDER BY f.pos, f.ordering '.$filter_order_Dir.' , f.title ';
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , f.pos, f.ordering, f.title ';
		}

		return $orderby;
	}

	public function _buildContentWhere()
	{
		global $mainframe, $option;
		$db =& JFactory::getDBO();
		$filter_state = $mainframe->getUserStateFromRequest($option.'filter_state', 'filter_state', '', 'word');
		$filter_order = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', 'f.ordering', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');
		$search = $mainframe->getUserStateFromRequest($option.'search', 'search', '', 'string');
		$search	= JString::strtolower($search);

		$where = array();

		if ($search) {
			$where[] = 'LOWER(f.title) LIKE '.$db->Quote('%'.$db->getEscaped($search, true).'%', false);
		}
		if ($filter_state) {
			if ($filter_state == 'P') {
				$where[] = 'f.published = 1';
			} else if ($filter_state == 'U') {
				$where[] = 'f.published = 0';
			}
		}

		$where 		= (count($where) ? ' WHERE '. implode(' AND ', $where) : '');

		return $where;
	}
} 
